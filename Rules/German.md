# Übersetzungsregeln / Guidelines

Diese Regeln sind nicht notwendiger Weise auf die Deutsche Community limitiert.
Es sollte wohl eine breitere Community Diskussion angestoßen werden, ob manche Begriffe Sprachen-übergreifend nicht oder auf eine bestimmte Art übersetzt werden, um möglichst große Harmonisierung zu erreichen.


## Allgemein

- Allgemein immer versuchen, eine Übersetzung (ein Wort) zu finden, das das englische Original erahnen lässt.
- Auf Imperative (in Menüpunkten, auf Buttons) verzichten (z.B. nicht "Verwalte Sperrung" sondern "Sperren verwalten").
- Da das REDCap UI an vielen Stellen sehr längensensitiv ist, sollten insbesondere für Strings mit einzelne Worte oder sehr kurzen Sequenzen möglichst gleich lange (oder kürzere) Übersetzungen gefunden werden, um einen Overflow oder ungünstige Umbrüche über die Grenzen eines UI-Widgets zu vermeiden. 
- Deutsche Umlaute und ß immer direkt verwenden, also ä/ö/ü/Ä/Ö/Ü/ß statt ae/oe/ue/Ae/Oe/Ue/ss. In vollständig großgeschriebenen Wörtern Umlaute ebenfalls verwenden (Ä/Ö/Ü), aber ß als SS schreiben.
- Strings nutzen Platzhalter: {0}, {1:With Context Indicator}, {2}  
  Diese müssen erhalten bleiben, können aber an die richtige Stelle gesetzt werden (und dann kann es sein, dass duch Wortumstellung die Reihenfolge der Platzhalter z.B. "{1} .. {0} ... {2}" ist).
- Platzhalter müssen auch dann erhalten bleiben, wenn sie im Deutschen nicht nötig wirken (z.B. englischer Plural-Suffix `{1}`). Gegebenenfalls eine etwas generische Formulierung wählen, aber die Platzhalter vollständig beibehalten.
- Action Tags wie `@PLACEHOLDER`, `@HIDDEN`, `@READONLY` usw. niemals übersetzen oder verändern. Alles nach `@` in dieser Form ist als REDCap-Action-Tag zu behandeln, nicht als frei übersetzbarer Platzhalter.
- Bei INI-Dateien können Anführungszeichen je nach Quelle als `\"` oder als doppelte Quotes `""` vorkommen. Den Stil der jeweiligen Datei bzw. des jeweiligen Blocks beibehalten.
- HTML, Links, `<code>`, `<b>`, `<i>`, `<u>`, `<br>` und ähnliche Markup-Fragmente beibehalten und nur den sichtbaren Text übersetzen.
- Wenn REDCap eine Meldung aus mehreren Sprachstrings und/oder dynamischen Werten zusammensetzt, über den betroffenen Fragmenten Kommentare im Format `; key_a is concatenated with strings key_b, key_c, ...` ergänzen. Dies nur bei echten Satz-/Fragment-Konstruktionen tun, nicht bei vollständig eigenständigen semantischen Einheiten.
- Bei fragmentierten Sätzen lieber den Codekontext prüfen und die deutschen Fragmente so formulieren, dass die tatsächlich zusammengesetzte Ausgabe grammatisch funktioniert. Wenn nötig, die Wortstellung etwas unnatürlicher halten, damit dynamische Werte an der richtigen Stelle bleiben.
- Bei den neuen Block-Dateien (`en_block_*.ini` -> `de_block_*.ini`) nur die jeweilige Zieldatei schreiben. Danach prüfen: gleiche Anzahl Keys, gleiche Key-Reihenfolge, Platzhalter/Action-Tags unverändert, keine fehlerhaften Quotes.

## Fixe Begriffe

- Action Tags = Action Tags
- Alert = Alarm
- Button = Button
- Codebook = Codebook
- Control Center = Control Center
- Clinical Data Mart = Clinical Data Mart
- Data Access Group = Zugriffsgruppe
- Data Entry Form = Eingabeformular
- Double Data Entry = doppelte Dateneingabe
- EHR = EHR
- E-signature = E-Signatur
- Event = Event
- External Modules = External Modules
- Field Embedding = Feldeinbettung
- File Repository = Datei-Repository
- Form = Formular
- Instrument = Instrument
- Lock(ing) = Sperre(n)
- Logging = Logging (und Zusammensetzugen als "...log", z.B. E-Mail-Log)
- Notification = Benachrichtigung
- P.I. = P.I.
- Piping = Piping
- Playground = Spielwiese
- Project = Projekt
- Project Dashboard = Projekt-Dashboard
- Project Home and Design = Projektverwaltung
- Home = Start (Menü Projektverwaltung)
- Setup = Setup (Menü Projektverwaltung)
- Record = Datensatz
- Report = Report (bisher: Berichte)
- Signature = Unterschrift
- Smart Variables = Smart Variables
- Survey = Kontextabhängig: Fragebogen (konkretes einzelnes Instrument) / Umfrage (Akt des Umfrage-Durchführens)
- Tool = Tool (immer mit Bindestrich, z.B. Datenimport-Tool) oder weglassen (Datenimport, Datenvergleich)
- Tutorials = Tutorials
- User = Benutzer
- User Rights = Benutzerrechte

## Häufige REDCap-Begriffe / Stil

- Access Control Group / ACG: als "Access Control Group" bzw. "ACG" stehen lassen. Bei Zusammensetzungen sind deutsche Formen wie "ACG-Zuweisung", "ACG-Konformität", "ACG-Name" sinnvoll und kurz.
- Alert: "Alarm"; "alert logs" entsprechend "Alarm-Logs".
- Completed (Projektstatus): "Abgeschlossen" großschreiben, wenn es als Statuswert gemeint ist.
- Data Quality: "Datenqualität".
- De-Identified: "De-identifiziert".
- Full Data Set: "Vollständiger Datensatz".
- No Access: "Kein Zugriff"; Read Only: "Schreibgeschützt"; View & Edit: "Anzeigen & Bearbeiten"; Full Access: "Vollzugriff".
- Mobile App bleibt meist "Mobile App"; in Zusammensetzungen auch "Mobile-App-...".
- Record List Cache = Datensatzlisten-Cache; Rapid Retrieval bleibt "Rapid Retrieval".
- Repeating Instruments and Events = "wiederholte Instrumente und Events".
- "My Projects" = "Meine Projekte"; "Project Setup" = "Projekt-Setup"; "Project Home" = "Projektstart".
- Bei API-, Datenbank-, PHP-, MySQL-/MariaDB- und ähnlichen technischen Fehlermeldungen Fachbegriffe, Dateinamen, SQL-Optionen und Konfigurationswerte unverändert lassen.


## Anrede

_"Here, you can ..."_ soll immer förmlich ("Sie") übersetzt werden. Gegebenenfalls passiv formuliert ("man").

## Gendern

Vermeiden. Generisches Maskulin. Kürzer und einfacher zu lesen/verstehen.

## Dinge, die NICHT übersetzt werden sollten

_Nicht notwendiger Weise limitiert auf Deutsch - Community Diskussion, ob manche Begriffe übergreifend nicht oder auf bestimmte Art übersetzt werden, um möglichst große Harmonisierung zu erreichen._


- REDCap-spezifische Fachbegriffe (wie z.B. Action Tags, Smart Variables, Piping)
- (Technische) Fehlermeldungen, insbesondere API, die nicht für Endbenutzer intendiert sind. Beibehaltung des Englischen erleichtert die internationale Kommunikation (Hilfestellung auf der Community Site; Bug Reports).
