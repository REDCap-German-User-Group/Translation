# Translate REDCap Language Strings from English to German

Following the translation rules given in [German.md](../German.md), translate the content of the file [English_NEW.ini](../English_NEW.ini) into German. The target should be a new file called `German_NEW.ini` and save it in the same directory as `English_NEW.ini`.

If you need to consult on previous translations in order to remain consistent, you can inspect the full [German.ini](../Translation/German.ini) or full [English.ini](English.ini) files.

Afterwards, fold the new translations into the main [German.ini](../Translation/German.ini) file such that the newly added language keys are inserted in the proper place, i.e., first by prefix (may contain multiple parts separated by underscores), then according to regular numberical ordering, or alphabetical ordering if the last part of a key is not numerica.

Finally, ensure that the new full [German.ini](../Translation/German.ini) file can be parsed by PHP. This can be done by using the [check_language_file.php](../check_language_file.php) script. Call it like this (assuming the current directory is the repository root):

```bash
php Tools/check_language_file.php Translation/German.ini
```

The tool will either output the number of language keys found in the file, or an error message if the file is not valid.