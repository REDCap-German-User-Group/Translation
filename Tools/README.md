# Tools

## fetch_language_json.php und update_language_header.php

Autor: **Günther Rezniczek & ChatGPT/Codex**

`fetch_language_json.php` ruft für eine REDCap-Version die öffentlichen LanguageUpdater-JSON-Endpunkte für Deutsch und Englisch ab. Das Skript prüft Sprache und Version, schreibt die im lokalen `German.ini` fehlenden englischen Strings nach `Todo/English_NEW.ini` und speichert die englischen Schlüssel temporär in `Todo/English_Keys.json`.

Nach Übersetzung und Merge aktualisiert `update_language_header.php` die Version, das Datum und die Liste der in dieser REDCap-Version ungenutzten Schlüssel. Ungenutzte Übersetzungen bleiben für LTS-Versionen erhalten.

```bash
php Tools/fetch_language_json.php 17.5.0
php Tools/update_language_header.php 17.5.0
```

Für einen anderen Host kann beim Abruf `--endpoint URL` verwendet werden. Das vollständige Verfahren steht in [TRANSLATE.md](../TRANSLATE.md).

`check_translation_batch.php` prüft vor dem Merge, ob alle abgerufenen englischen Schlüssel in `German_NEW.ini` vorhanden sind und Platzhalter sowie Action Tags erhalten blieben.

## prepare_upload.php

Autor: **Günther Rezniczek & ChatGPT/Codex**

`prepare_upload.php` liest die gemeinsamen Einreichungsdaten aus [UPLOAD.md](../UPLOAD.md) und E-Mail/Institutions-ID aus der ignorierten lokalen Datei `UPLOAD.local.ini`. Beim ersten Einsatz muss `UPLOAD.local.ini.example` nach `UPLOAD.local.ini` kopiert und ausgefüllt werden. Das Skript ergänzt die angegebene REDCap-Version und erstellt die ebenfalls ignorierte lokale Datei `UPLOAD.html`. Diese enthält einen korrekt URL-kodierten Link zum vorausgefüllten REDCap-Einreichungsformular. CAPTCHA, Datei-Upload, Prüfung und Absenden erfolgen weiterhin manuell.

```bash
php Tools/prepare_upload.php 17.5.0
```

## create_debug_file.php

Autor: **Christof Meigen**

PHP Skript, das aus einem englischen und einem anderen (deutschen) Language File ein
neues Language-File zu Debug-Zwecken erzeugt, das die (deutschen) Texte anzeigt, aber ein rotes Fragezeichen voranstellt, das als Hover-Effekt den Schlüsselwert und den englischen Originaltext anzeigt.

## check_language_file.php

Autor: **Günther Rezniczek**

PHP Skript zur Prüfung eines Language Files. Bei einer erfolgreich parsbaren Datei
wird die Anzahl der Einträge ausgegeben. Wenn die Datei nicht geparst werden kann,
wird der letzte erfolgreich gelesene Schlüssel ausgegeben, um die fehlerhafte Stelle
leichter einzugrenzen.

Aufruf, wenn das aktuelle Verzeichnis das Repository-Root ist:

```bash
php Tools/check_language_file.php Translation/German.ini
```

## merge_language_file.php

Autor: **Günther Rezniczek**

PHP Skript zum Einfügen neuer Übersetzungen in ein bestehendes Language File.
Bereits vorhandene Schlüssel werden ersetzt, fehlende Schlüssel werden in der Nähe
passender vorhandener Schlüssel eingefügt. Die Sortierung berücksichtigt
Unterstrich-getrennte Schlüsselbestandteile und numerische Suffixe numerisch
(`prefix_9` vor `prefix_10`). Nach dem Merge wird geprüft, ob die erzeugte Datei
durch PHP parsbar ist.

Aufruf, wenn das aktuelle Verzeichnis das Repository-Root ist:

```bash
php Tools/merge_language_file.php Todo/German_NEW.ini Translation/German.ini
```

Mit `--dry-run` kann geprüft werden, wie viele Schlüssel eingefügt bzw. ersetzt
würden, ohne die Zieldatei zu schreiben:

```bash
php Tools/merge_language_file.php Todo/German_NEW.ini Translation/German.ini --dry-run
```

## validate_language_file.html

Autor: **Günther Rezniczek**

Eigenständige HTML-Datei zur Prüfung eines Language Files direkt im Browser. Die
Datei benötigt keinen Webserver und keine JavaScript-Bibliotheken. Sie kann lokal
geöffnet werden und validiert per eingebettetem JavaScript eine bewusst enge REDCap-
Language-File-Syntax: flache `key = "value"` Einträge, mehrzeilige Werte,
escaped Quotes und den in REDCap-Dateien vorkommenden doppelten Quote-Stil.

Die Prüfung ist konservativ: Dateien, die erfolgreich validiert werden, sollen durch
PHPs `parse_ini_file()` parsbar sein. Nicht jede allgemein gültige INI-Datei wird
akzeptiert.

Aufruf: `Tools/validate_language_file.html` lokal im Browser öffnen und eine
`.ini` Datei auswählen oder Text einfügen.

## REDCap Translation Assistant EM

Autor: **Günther Rezniczek**

Externes Modul, das zum einen 
- die Verwaltung von Sprach-Metadaten und Übersetzungen lokal und vorbereitend für die Integration mit diesem GitHub-Repository 

und zum anderen

- die einfache Übersetzung von REDCap Strings direkt am Ort der Darstellung sowie
- die parallele Erfassung von Metadaten zu diesen Strings

unterstützen soll.

Link zum GitHub-Repository: https://github.com/grezniczek/redcap_translator

Diese Modul ist derzeit auf dem Stand einer **BETA** Version und noch nicht im REDCap Repository verfügbar.
