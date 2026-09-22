<?php

if (PHP_SAPI !== 'cli') exit("CLI only.\n");
require_once __DIR__ . '/language_updater_common.php';

$root = dirname(__DIR__);
$usage = "Usage: php Tools/prepare_upload.php X.Y.Z [--metadata FILE] [--local-config FILE] [--output FILE]\n";

try {
	if ($argc < 2) throw new RuntimeException($usage);
	$version = $argv[1];
	languageUpdaterVersion($version);
	$options = languageUpdaterOptions(array_slice($argv, 2), array('--metadata', '--local-config', '--output'));
	$metadataFile = $options['--metadata'] ?? "$root/LANGUAGE_LIBRARY.md";
	$localConfigFile = $options['--local-config'] ?? "$root/UPLOAD.local.ini";
	$output = $options['--output'] ?? "$root/UPLOAD.local.md";
	$contents = @file_get_contents($metadataFile);
	if ($contents === false) throw new RuntimeException("Unable to read $metadataFile.");

	if (!preg_match('/^Upload Survey:\s*(https:\/\/\S+)\s*$/m', $contents, $surveyMatch)) {
		throw new RuntimeException('LANGUAGE_LIBRARY.md has no valid HTTPS upload survey URL.');
	}
	$labels = array(
		'language' => 'Language',
		'translation_percentage' => 'Translation percentage',
		'translated_by' => 'Translated by',
		'comments' => 'Comments',
	);
	$params = array();
	foreach ($labels as $parameter => $label) {
		$pattern = '/^' . preg_quote($label, '/') . ':\s*`([^`]*)`(?:\s+\([^\r\n]*\))?\s*$/m';
		if (!preg_match($pattern, $contents, $match) || $match[1] === '') {
			throw new RuntimeException("LANGUAGE_LIBRARY.md has no value for $label.");
		}
		$params[$parameter] = $match[1];
	}
	if (!ctype_digit($params['language']) || !ctype_digit($params['translation_percentage'])) {
		throw new RuntimeException('Language and translation percentage must be numeric.');
	}
	$local = @parse_ini_file($localConfigFile, false, INI_SCANNER_RAW);
	if (!is_array($local)) {
		throw new RuntimeException("Unable to parse $localConfigFile. Copy UPLOAD.local.ini.example to UPLOAD.local.ini and fill in its values.");
	}
	$email = $local['translator_email'] ?? '';
	$leadSite = $local['lead_site'] ?? '';
	if (!is_string($email) || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
		throw new RuntimeException('UPLOAD.local.ini has no valid translator_email.');
	}
	if (!is_string($leadSite) || !ctype_digit($leadSite)) {
		throw new RuntimeException('UPLOAD.local.ini has no valid numeric lead_site.');
	}
	$params['translatoremail'] = $email;
	$params['lead_site'] = $leadSite;

	$parts = parse_url($surveyMatch[1]);
	if ($parts === false || ($parts['scheme'] ?? null) !== 'https' || !isset($parts['host'], $parts['query'])) {
		throw new RuntimeException('Invalid upload survey URL.');
	}
	parse_str($parts['query'], $baseQuery);
	if (!isset($baseQuery['s']) || !is_string($baseQuery['s']) || $baseQuery['s'] === '') {
		throw new RuntimeException('Upload survey URL has no survey hash.');
	}
	$query = array_merge(array('s' => $baseQuery['s'], 'version' => $version), $params);
	$url = $parts['scheme'] . '://' . $parts['host'] . ($parts['path'] ?? '/') . '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
	$archive = "German_$version.zip";
	$markdown = <<<MARKDOWN
# Upload German $version

[Open the prefilled REDCap submission survey]($url)

1. Complete the CAPTCHA and open the survey.
2. Upload `$archive`.
3. Review the prefilled values and submit the survey manually.

## Prefilled URL

<$url>
MARKDOWN;
	languageUpdaterWrite($output, $markdown . "\n");
	echo "Prepared $output for REDCap $version.\n";
} catch (Throwable $e) {
	fwrite(STDERR, $e->getMessage() . "\n");
	exit(1);
}
