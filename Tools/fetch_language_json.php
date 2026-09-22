<?php

if (PHP_SAPI !== 'cli') exit("CLI only.\n");
require_once __DIR__ . '/language_updater_common.php';

$root = dirname(__DIR__);
$usage = "Usage: php Tools/fetch_language_json.php X.Y.Z [--endpoint URL] [--german-json FILE --english-json FILE] [--target FILE] [--output FILE] [--snapshot FILE]\n";
try {
	if ($argc < 2) throw new RuntimeException($usage);
	$version = $argv[1];
	languageUpdaterVersion($version);
	$options = languageUpdaterOptions(array_slice($argv, 2), array('--endpoint', '--german-json', '--english-json', '--target', '--output', '--snapshot'));
	if (isset($options['--german-json']) !== isset($options['--english-json'])) {
		throw new RuntimeException('Both JSON fixture options must be supplied together.');
	}
	$endpoint = $options['--endpoint'] ?? "https://dev-redcap/redcap_v$version/LanguageUpdater/index.php";
	$target = $options['--target'] ?? "$root/Translation/German.ini";
	$output = $options['--output'] ?? "$root/Todo/English_NEW.ini";
	$snapshot = $options['--snapshot'] ?? "$root/Todo/English_Keys.json";
	$german = languageUpdaterResponse('German', $version, 'untranslated,unused', $options['--german-json'] ?? null, $endpoint);
	$english = languageUpdaterResponse('English', $version, 'translated', $options['--english-json'] ?? null, $endpoint);
	$local = @parse_ini_file($target);
	if (!is_array($local)) throw new RuntimeException("Unable to parse $target.");
	$allEnglish = $english['translated'];
	foreach ($german['untranslated'] as $key => $value) {
		if (!array_key_exists($key, $allEnglish) || $allEnglish[$key] !== $value) {
			throw new RuntimeException("English source differs between endpoint responses for $key.");
		}
	}
	$missing = array_diff_key($allEnglish, $local);
	uksort($missing, 'strnatcmp');
	$lines = array();
	foreach ($missing as $key => $value) {
		$encoded = str_replace(array('\\', '"'), array('\\\\', '\\"'), $value);
		$lines[] = "$key = \"$encoded\"";
	}
	$ini = $lines ? implode("\n", $lines) . "\n" : '';
	if (@parse_ini_string($ini) !== $missing) {
		throw new RuntimeException('Generated English INI does not round-trip through PHP.');
	}
	$keys = array_keys($allEnglish);
	$source = json_encode(array('version' => $version, 'english_keys' => $keys), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n";
	languageUpdaterWrite($output, $ini);
	languageUpdaterWrite($snapshot, $source);
	echo count($missing) . " keys missing from local German.ini; wrote $output\n";
	echo count($german['untranslated']) . " untranslated and " . count($german['unused']) . " unused keys reported by the installed German file.\n";
} catch (Throwable $e) {
	fwrite(STDERR, $e->getMessage() . "\n");
	exit(1);
}
