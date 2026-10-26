<?php

declare(strict_types=1);

class Variablenspiegel extends IPSModuleStrict
{
    // Richtung der Synchronisation eines Paares
    private const DIRECTION_BOTH = 0;
    private const DIRECTION_1_TO_2 = 1;
    private const DIRECTION_2_TO_1 = 2;

    public function Create(): void
    {
        //Never delete this line!
        parent::Create();
        $this->RegisterPropertyString('Variables', '[]');
        $this->RegisterPropertyInteger('WatchdogInterval', 0);
        $this->RegisterTimer('Watchdog', 0, 'VSP_Watchdog($_IPS[\'TARGET\']);');
    }

    public function ApplyChanges(): void
    {
        //Never delete this line!
        parent::ApplyChanges();

        // Alte Registrierungen entfernen, sonst bleiben entfernte oder geänderte Paare aktiv
        foreach ($this->GetMessageList() as $SenderID => $Messages) {
            foreach ($Messages as $Message) {
                $this->UnregisterMessage($SenderID, $Message);
            }
        }

        // Nur Quellvariablen überwachen: bei einer Richtung wird das Ziel nicht beobachtet
        foreach ($this->getVariablePairs() as $Pair) {
            $Sources = [];
            if ($Pair['Direction'] != self::DIRECTION_2_TO_1) {
                $Sources[] = $Pair['VariableID1'];
            }
            if ($Pair['Direction'] != self::DIRECTION_1_TO_2) {
                $Sources[] = $Pair['VariableID2'];
            }
            foreach ($Sources as $VariableID) {
                if ($VariableID > 0 && IPS_VariableExists($VariableID)) {
                    $this->RegisterMessage($VariableID, VM_UPDATE);
                } else {
                    $this->SendDebug(__FUNCTION__, 'Variable ' . $VariableID . ' existiert nicht und wird ignoriert', 0);
                }
            }
        }

        $Interval = max(0, $this->ReadPropertyInteger('WatchdogInterval'));
        $this->maintainWatchdogVariables($Interval > 0);
        $this->SetTimerInterval('Watchdog', $Interval * 1000);
    }

    /**
     * Gleicht alle Paare ab und setzt abweichende Zielvariablen. Läuft über den Timer.
     * Bei einer Richtung gewinnt immer die Quelle, bei beiden Richtungen die zuletzt aktualisierte Variable.
     */
    public function Watchdog(): void
    {
        $Deviations = 0;
        $Errors = [];
        foreach ($this->getVariablePairs() as $Pair) {
            $ID1 = $Pair['VariableID1'];
            $ID2 = $Pair['VariableID2'];
            if (!$this->variableUsable($ID1) || !$this->variableUsable($ID2)) {
                continue;
            }
            if (GetValue($ID1) == GetValue($ID2)) {
                continue;
            }
            $Deviations++;
            switch ($Pair['Direction']) {
                case self::DIRECTION_1_TO_2:
                    $Source = $ID1;
                    break;
                case self::DIRECTION_2_TO_1:
                    $Source = $ID2;
                    break;
                default:
                    $Source = IPS_GetVariable($ID2)['VariableUpdated'] > IPS_GetVariable($ID1)['VariableUpdated'] ? $ID2 : $ID1;
                    break;
            }
            $Target = $Source == $ID1 ? $ID2 : $ID1;
            $this->SendDebug(__FUNCTION__, 'Abweichung: ' . $Source . ' -> ' . $Target, 0);
            $Label = $Pair['Name'] != '' ? $Pair['Name'] : $ID1 . ' / ' . $ID2;
            try {
                if (!@RequestAction($Target, GetValue($Source))) {
                    $this->SendDebug(__FUNCTION__, 'RequestAction für ' . $Target . ' nicht erfolgreich, nächster Versuch beim nächsten Lauf', 0);
                    $Errors[] = $Label . ': ' . sprintf($this->Translate('Target %d not reachable'), $Target);
                }
            } catch (Throwable $e) {
                $this->SendDebug(__FUNCTION__, 'Ziel ' . $Target . ' nicht erreichbar: ' . $e->getMessage(), 0);
                $Errors[] = $Label . ': ' . $e->getMessage();
            }
        }

        // Ohne Intervall existieren die Variablen nicht, der Aufruf per Skript bleibt trotzdem möglich
        if ($this->ReadPropertyInteger('WatchdogInterval') > 0) {
            $this->SetValue('InSync', $Deviations == 0);
            $this->SetValue('Deviations', $Deviations);
            $this->SetValue('LastRun', time());
            $this->SetValue('LastError', implode('; ', $Errors));
        }
    }

    public function MessageSink(int $TimeStamp, int $SenderID, int $Message, array $Data): void
    {
        $this->SendDebug(__FUNCTION__, 'SenderID ' . $SenderID . ' Message: ' . $Message, 0);
        switch ($Message) {
            case VM_UPDATE:
                $AssociatedVariable = $this->getTargetVariable($SenderID);
                $this->SendDebug(__FUNCTION__, 'SenderID ' . $SenderID . ' AssociatedVariable: ' . $AssociatedVariable, 0);
                if ($AssociatedVariable > 0 && IPS_VariableExists($AssociatedVariable)) {
                    $AssociatedValue = GetValue($AssociatedVariable);
                    $SenderValue = GetValue($SenderID);
                    $this->SendDebug(__FUNCTION__, 'AssociatedValue ' . json_encode($AssociatedValue) . ' SenderValue: ' . json_encode($SenderValue), 0);
                    if ($AssociatedValue != $SenderValue) {
                        $this->SendDebug(__FUNCTION__, 'RequestAction', 0);
                        RequestAction($AssociatedVariable, $SenderValue);
                    }
                }
                break;
            default:
                $this->SendDebug(__FUNCTION__, $SenderID . ' Message: ' . $Message, 0);
                break;
        }
    }

    /**
     * Liefert die konfigurierten Variablenpaare mit ganzzahligen Variablen-IDs.
     */
    private function getVariablePairs(): array
    {
        $Variables = json_decode($this->ReadPropertyString('Variables'), true);
        if (!is_array($Variables)) {
            return [];
        }
        $Pairs = [];
        foreach ($Variables as $Variable) {
            $Pairs[] = [
                'Name'        => (string) ($Variable['Name'] ?? ''),
                'VariableID1' => (int) ($Variable['VariableID1'] ?? 0),
                'VariableID2' => (int) ($Variable['VariableID2'] ?? 0),
                'Direction'   => (int) ($Variable['Direction'] ?? self::DIRECTION_BOTH)
            ];
        }
        return $Pairs;
    }

    /**
     * Legt die Statusvariablen des Watchdogs an, solange er aktiv ist, und entfernt sie sonst.
     */
    private function maintainWatchdogVariables(bool $Active): void
    {
        $Value = ['PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION];
        $InSync = $Value + [
            'OPTIONS' => json_encode([
                ['Caption' => $this->Translate('Deviation'), 'ColorActive' => true, 'ColorValue' => 16711680, 'ContentColorActive' => false, 'ContentColorValue' => -1, 'IconActive' => false, 'IconValue' => '', 'Value' => false],
                ['Caption' => $this->Translate('In sync'), 'ColorActive' => true, 'ColorValue' => 65280, 'ContentColorActive' => false, 'ContentColorValue' => -1, 'IconActive' => false, 'IconValue' => '', 'Value' => true]
            ])
        ];
        $DateTime = ['PRESENTATION' => VARIABLE_PRESENTATION_DATE_TIME, 'DATE' => 1, 'MONTH_TEXT' => false, 'DAY_OF_THE_WEEK' => false, 'TIME' => 2];
        $this->MaintainVariable('InSync', 'In sync', VARIABLETYPE_BOOLEAN, $InSync, 1, $Active);
        $this->MaintainVariable('Deviations', 'Deviating pairs', VARIABLETYPE_INTEGER, $Value, 2, $Active);
        $this->MaintainVariable('LastRun', 'Last run', VARIABLETYPE_INTEGER, $DateTime, 3, $Active);
        $this->MaintainVariable('LastError', 'Last error', VARIABLETYPE_STRING, $Value, 4, $Active);
    }

    private function variableUsable(int $VariableID): bool
    {
        return $VariableID > 0 && IPS_VariableExists($VariableID);
    }

    /**
     * Liefert das Ziel zu einer geänderten Variable. Berücksichtigt die Richtung des Paares.
     */
    private function getTargetVariable(int $VariableID): int
    {
        foreach ($this->getVariablePairs() as $Pair) {
            if ($Pair['VariableID1'] == $VariableID && $Pair['Direction'] != self::DIRECTION_2_TO_1) {
                return $Pair['VariableID2'];
            } elseif ($Pair['VariableID2'] == $VariableID && $Pair['Direction'] != self::DIRECTION_1_TO_2) {
                return $Pair['VariableID1'];
            }
        }
        return 0;
    }
}
