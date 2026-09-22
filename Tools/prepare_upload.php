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
	$metadataFile = $options['--metadata'] ?? "$root/UPLOAD.md";
	$localConfigFile = $options['--local-config'] ?? "$root/UPLOAD.local.ini";
	$output = $options['--output'] ?? "$root/UPLOAD.html";
	$contents = @file_get_contents($metadataFile);
	if ($contents === false) throw new RuntimeException("Unable to read $metadataFile.");

	if (!preg_match('/^Upload Survey:\s*(https:\/\/\S+)\s*$/m', $contents, $surveyMatch)) {
		throw new RuntimeException('UPLOAD.md has no valid HTTPS upload survey URL.');
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
			throw new RuntimeException("UPLOAD.md has no value for $label.");
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
	$escapedUrl = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	$escapedVersion = htmlspecialchars($version, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	$archive = "German_$version.zip";
	$escapedArchive = htmlspecialchars($archive, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	$html = <<<HTML
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Upload German $escapedVersion</title>
</head>
<body>
<h1>Upload German $escapedVersion</h1>
<p><a href="$escapedUrl" target="_blank" rel="noopener noreferrer">Open the prefilled REDCap submission survey</a></p>
<ol>
<li>Complete the CAPTCHA and open the survey.</li>
<li>Upload <code>$escapedArchive</code>.</li>
<li>Review the prefilled values and submit the survey manually.</li>
</ol>
<details><summary>Prefilled URL</summary><p><code>$escapedUrl</code></p></details>
</body>
</html>
HTML;
	languageUpdaterWrite($output, $html . "\n");
	echo "Prepared $output for REDCap $version.\n";
} catch (Throwable $e) {
	fwrite(STDERR, $e->getMessage() . "\n");
	exit(1);
}
