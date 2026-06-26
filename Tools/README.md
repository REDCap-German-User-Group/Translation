# Tools

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
