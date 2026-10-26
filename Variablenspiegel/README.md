# Variablenspiegel
Hält Variablenpaare synchron, ohne dass Skripte oder Hilfsvariablen nötig sind. Ändert sich eine Variable eines Paares, setzt die Instanz die andere Variable per `RequestAction` auf denselben Wert. Die Synchronisation läuft wahlweise in beide Richtungen oder nur in eine. Mit aktivem Watchdog legt die Instanz Statusvariablen an.

## Inhaltsverzeichnis
1. [Konfiguration](#1-konfiguration)
2. [Variablen](#2-variablen)
3. [Funktionen](#3-funktionen)
4. [Spenden](#4-spenden)
5. [Lizenz](#5-lizenz)

## 1. Konfiguration

Feld | Beschreibung
------------ | -------------
Name | Dient nur zur Übersicht! hat keine weitere Funktion!
Variable 1| Variable 1 welche Synchron zu Variable 2 gehalten wird.
Variable 2| Variable 2 welche Synchron zu Variable 1 gehalten wird.
Richtung | Legt fest, in welche Richtung synchronisiert wird: Variable 1 ↔ Variable 2 (beide Richtungen, Standard), Variable 1 → Variable 2 oder Variable 1 ← Variable 2. Bei einer Richtung wird nur die Quellvariable überwacht.
Watchdog-Intervall | Abstand in Sekunden, in dem alle Paare abgeglichen werden. 0 schaltet den Watchdog aus (Standard).

Hinweise:
* Beide Variablen sollten denselben Typ haben und die Aktion der Zielvariable muss einen passenden Wert annehmen.
* Eine Variable sollte nur in einem Paar vorkommen, sonst gilt das erste passende Paar.
* Nicht mehr vorhandene Variablen werden ignoriert.

### Watchdog
Das Modul vermittelt nur. Ist eine Seite zeitweise offline, geht eine Änderung verloren. Der Watchdog vergleicht deshalb im eingestellten Intervall beide Variablen jedes Paares und setzt bei einer Abweichung die Zielvariable per `RequestAction` nach. Ist das Gerät noch nicht erreichbar, scheitert der Versuch ohne Meldung im Log, wird in der Variable `Letzter Fehler` festgehalten und beim nächsten Lauf wiederholt.

Welche Seite gewinnt:
* Variable 1 → Variable 2: Variable 1 gewinnt immer.
* Variable 1 ← Variable 2: Variable 2 gewinnt immer.
* Variable 1 ↔ Variable 2: Die Variable gewinnt, die zuletzt aktualisiert wurde.

## 2. Variablen

Die Variablen existieren nur, solange das Watchdog-Intervall größer als 0 ist. Bei 0 werden sie entfernt.

Ident | Name | Typ | Beschreibung
------------ | ------------- | ------------- | -------------
InSync | Synchron | Boolean | Der Watchdog hat beim letzten Lauf keine Abweichung gefunden (`Synchron`) oder mindestens eine (`Abweichung`). Darauf kann mit einem Ereignis reagiert werden.
Deviations | Abweichende Paare | Integer | Anzahl der Paare, die beim letzten Lauf abwichen. Bleibt der Wert dauerhaft über 0, kommt die Zielseite nicht nach, zum Beispiel weil sie offline ist.
LastRun | Letzter Abgleich | Integer (Datum/Uhrzeit) | Zeitpunkt des letzten Watchdog-Laufs.
LastError | Letzter Fehler | String | Paare, bei denen das Nachsetzen nicht möglich war. Leer, wenn beim letzten Lauf kein Fehler auftrat.

## 3. Funktionen

`VSP_Watchdog(int $InstanzID)`: Gleicht alle Paare sofort ab. Läuft sonst über das Intervall.

## 4. Spenden

Dieses Modul ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:

[![PayPal](https://img.shields.io/badge/PayPal-Spenden-00457C?logo=paypal&logoColor=white&style=for-the-badge)](https://www.paypal.com/donate?hosted_button_id=EK4JRP87XLSHW) [![Amazon Wunschzettel](https://img.shields.io/badge/Amazon-Wunschzettel-FF9900?logo=amazon&logoColor=white&style=for-the-badge)](https://www.amazon.de/hz/wishlist/ls/3JVWED9SZMDPK?ref_=wl_share)

## 5. Lizenz

[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)
