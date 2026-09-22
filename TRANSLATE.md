# Translate REDCap strings into German

Run this workflow when asked, for example: **“Please execute TRANSLATE.md for 17.5.0.”** The language is German. Work from the repository root and use the requested REDCap version throughout. Do not create a tag until the translation and resulting diff have been reviewed.

1. Fetch the English strings missing from the repository's German file:

   ```bash
   php Tools/fetch_language_json.php 17.5.0
   ```

   The tool requests German `untranslated,unused` and English `translated` from the public LanguageUpdater endpoint. It rejects responses for another language or version. It writes [Todo/English_NEW.ini](Todo/English_NEW.ini) and a temporary, ignored `Todo/English_Keys.json` snapshot. The snapshot is needed to identify keys unused in this REDCap version. Stop if the endpoint is unavailable or its version differs; do not silently use another version.

2. Translate every entry in `Todo/English_NEW.ini` into [Todo/German_NEW.ini](Todo/German_NEW.ini). Follow [Rules/German.md](Rules/German.md), preserve keys, placeholders, action tags, and intentional markup, and consult [Translation/German.ini](Translation/German.ini) for terminology. If `German_NEW.ini` already has content, add new keys and replace existing keys as needed. Keep its keys in natural order (`prefix_9` before `prefix_10`). If no English keys are missing, skip the translation and merge steps.

3. Check that every key in the fetched English file has a German entry with the same placeholders and action tags. Review quoting, markup, and the translation diff. Then merge the translations:

   ```bash
   php Tools/check_translation_batch.php Todo/English_NEW.ini Todo/German_NEW.ini
   php Tools/merge_language_file.php Todo/German_NEW.ini Translation/German.ini
   ```

4. Set the version, local date, and unused-key comment in the full German file. The tool computes unused keys from the endpoint's English key set and the **local** German file. It retains all unused translations for older REDCap branches:

   ```bash
   php Tools/update_language_header.php 17.5.0
   php Tools/check_language_file.php Translation/German.ini
   ```

5. Review `git diff -- Translation/German.ini Todo/English_NEW.ini Todo/German_NEW.ini`. Commit the finished translation. When it is ready to publish, create and push a tag such as `v17.5.0` on that commit. A [GitHub Actions workflow](.github/workflows/release.yml) checks the header version and creates the GitHub release with only `README.md` and `German.ini` as uploaded assets. Existing bare version tags are historical and need no changes.
