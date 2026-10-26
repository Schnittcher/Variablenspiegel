[![Version](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
![Version](https://img.shields.io/badge/Symcon%20Version-9.0%20%3E-blue.svg)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![Check Style](https://github.com/Schnittcher/Variablenspiegel/actions/workflows/style.yml/badge.svg)](https://github.com/Schnittcher/Variablenspiegel/actions/workflows/style.yml)
[![Tests](https://github.com/Schnittcher/Variablenspiegel/actions/workflows/tests.yml/badge.svg)](https://github.com/Schnittcher/Variablenspiegel/actions/workflows/tests.yml)

# Variablenspiegel
Mit diesem Modul ist es möglich Variablen synchron zu halten, ohne mehrere Scripte oder Variablen anlegen zu müssen.

## Inhaltsverzeichnis
- [Variablenspiegel](#variablenspiegel)
  - [Inhaltsverzeichnis](#inhaltsverzeichnis)
  - [1. Voraussetzungen](#1-voraussetzungen)
  - [2. Funktionsumfang](#2-funktionsumfang)
  - [3. Enthaltene Module](#3-enthaltene-module)
  - [4. Installation](#4-installation)
  - [5. Konfiguration in IP-Symcon](#5-konfiguration-in-ip-symcon)
  - [6. Spenden](#6-spenden)
  - [7. Lizenz](#7-lizenz)

## 1. Voraussetzungen

* mindestens IPS Version 9.0

## 2. Funktionsumfang

* Beliebig viele Variablenpaare synchron halten, wahlweise in beide Richtungen oder nur in eine
* Watchdog gleicht die Paare regelmäßig ab, falls eine Seite zeitweise offline war
* Statusvariablen zeigen, ob alle Paare synchron sind und ob ein Nachsetzen fehlschlug
* Der neue Wert wird über die Aktion der Zielvariable gesetzt, das Gerät wird also tatsächlich geschaltet

Die einzelnen Funktionen stehen bei den Instanzen unter "Enthaltene Module" und in der README des jeweiligen Moduls.

## 3. Enthaltene Module

* [Variablenspiegel](Variablenspiegel/README.md)
  * Hält Variablenpaare synchron: Ändert sich eine Variable eines Paares, wird der Wert über die Aktion der anderen Variable übernommen.

## 4. Installation

Die Installation erfolgt über den IP-Symcon Module Store.

## 5. Konfiguration in IP-Symcon
Eine Instanz Variablenspiegel anlegen und die Variablenpaare in der Liste eintragen. Details stehen in der [README des Moduls](Variablenspiegel/README.md).

## 6. Spenden

Dieses Modul ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:

[![PayPal](https://img.shields.io/badge/PayPal-Spenden-00457C?logo=paypal&logoColor=white&style=for-the-badge)](https://www.paypal.com/donate?hosted_button_id=EK4JRP87XLSHW) [![Amazon Wunschzettel](https://img.shields.io/badge/Amazon-Wunschzettel-FF9900?logo=amazon&logoColor=white&style=for-the-badge)](https://www.amazon.de/hz/wishlist/ls/3JVWED9SZMDPK?ref_=wl_share)

## 7. Lizenz

[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)
