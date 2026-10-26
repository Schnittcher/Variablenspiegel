# Changelog

## 12.10.2026 - Version 2.0

### Neu
- Das Modul heißt jetzt Variablenspiegel (vorher UniversalGateway). Bestehende Instanzen bleiben unverändert erhalten.
- Läuft jetzt ab IP-Symcon 9.0.
- Watchdog: Prüft im einstellbaren Intervall, ob beide Seiten eines Paares synchron sind, und gleicht Abweichungen nach (zum Beispiel nach einem Ausfall einer Seite). Bei einer Richtung gewinnt die Quelle, bei beiden Richtungen die zuletzt aktualisierte Variable.
- Statusvariablen bei aktivem Watchdog (Synchron, Abweichende Paare, Letzter Abgleich, Letzter Fehler), auf die per Ereignis reagiert werden kann.
- Alle Texte sind jetzt englisch und werden über die `locale.json` ins Deutsche übersetzt. Neu angelegte Statusvariablen heißen unter Deutsch wie bisher.
- Die Richtung der Synchronisation ist je Paar einstellbar: in beide Richtungen oder nur von Variable 1 nach Variable 2 beziehungsweise umgekehrt. Bestehende Paare bleiben bidirektional.

### Fixes
- Entfernte oder geänderte Variablenpaare werden nicht mehr weiter synchronisiert.
- Gelöschte Variablen in der Liste führen nicht mehr zu Fehlermeldungen.
- Achtung: Die Mindestversion von IP-Symcon ist jetzt 9.0.
