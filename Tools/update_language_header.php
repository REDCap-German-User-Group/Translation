<?php

if (PHP_SAPI !== 'cli') exit("CLI only.\n");
require_once __DIR__ . '/language_updater_common.php';

$root = dirname(__DIR__);
$usage = "Usage: php Tools/update_language_header.php X.Y.Z [--target FILE] [--snapshot FILE] [--date YYYY-MM-DD]\n";
try {
	if ($argc < 2) throw new RuntimeException($usage);
	$version = $argv[1];
	languageUpdaterVersion($version);
	$options = languageUpdaterOptions(array_slice($argv, 2), array('--target', '--snapshot', '--date'));
	$target = $options['--target'] ?? "$root/Translation/German.ini";
	$snapshot = $options['--snapshot'] ?? "$root/Todo/English_Keys.json";
	$date = $options['--date'] ?? (new DateTimeImmutable('now', new DateTimeZone('Europe/Berlin')))->format('Y-m-d');
	if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/D', $date, $parts) || !checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1])) {
		throw new RuntimeException("Invalid date: $date");
	}
	$contents = @file_get_contents($target);
	$source = @file_get_contents($snapshot);
	if ($contents === false || $source === false) throw new RuntimeException('Unable to read German.ini or the English key snapshot.');
	$before = @parse_ini_string($contents);
	if (!is_array($before)) throw new RuntimeException("Unable to parse $target.");
	try {
		$metadata = json_decode($source, true, 512, JSON_THROW_ON_ERROR);
	} catch (JsonException $e) {
		throw new RuntimeException('Invalid English key snapshot: ' . $e->getMessage());
	}
	if (!is_array($metadata) || ($metadata['version'] ?? null) !== $version || !isset($metadata['english_keys']) || !is_array($metadata['english_keys']) || !array_is_list($metadata['english_keys'])) {
		throw new RuntimeException('English key snapshot has the wrong version or structure.');
	}
	$english = array();
	foreach ($metadata['english_keys'] as $key) {
		if (!is_string($key) || !preg_match('/^[A-Za-z0-9_.:-]+$/D', $key) || isset($english[$key])) {
			throw new RuntimeException('Invalid or duplicate key in English key snapshot.');
		}
		$english[$key] = true;
	}
	if ($english === array()) throw new RuntimeException('English key snapshot is empty.');
	$unused = array_keys(array_diff_key($before, $english));
	usort($unused, 'strnatcmp');
	if (!preg_match('/^([A-Za-z0-9_.:-]+)\s*=/m', $contents, $match, PREG_OFFSET_CAPTURE)) {
		throw new RuntimeException('No language entries found in German.ini.');
	}
	$offset = $match[0][1];
	$prefix = substr($contents, 0, $offset);
	$body = substr($contents, $offset);
	$kept = array();
	$inUnusedBlock = false;
	foreach (preg_split('/\R/', $prefix) as $line) {
		if (preg_match('/^; These keys are not used in REDCap /', $line)) {
			$inUnusedBlock = true;
			continue;
		}
		if ($inUnusedBlock) {
			if ($line === '; End unused keys') $inUnusedBlock = false;
			continue;
		}
		if (preg_match('/^; (Last updated:|REDCap \d+\.\d+\.\d+$)/', $line)) continue;
		if (trim($line) !== '') $kept[] = $line;
	}
	if ($inUnusedBlock) throw new RuntimeException('Unclosed unused-key block in German.ini header.');
	$kept[] = "; Last updated: $date";
	$kept[] = "; REDCap $version";
	if ($unused) {
		$kept[] = "; These keys are not used in REDCap $version (retained for LTS):";
		$line = ';   ';
		foreach ($unused as $key) {
			$next = $line === ';   ' ? $key : ', ' . $key;
			if (strlen($line . $next) > 110 && $line !== ';   ') {
				$kept[] = $line;
				$line = ';   ' . $key;
			} else {
				$line .= $next;
			}
		}
		$kept[] = $line;
		$kept[] = '; End unused keys';
	}
	$updated = implode("\n", $kept) . "\n\n" . $body;
	if (@parse_ini_string($updated) !== $before) throw new RuntimeException('Header update changed language entries.');
	languageUpdaterWrite($target, $updated);
	echo "Updated $target for REDCap $version on $date; " . count($unused) . " unused keys retained.\n";
} catch (Throwable $e) {
	fwrite(STDERR, $e->getMessage() . "\n");
	exit(1);
}
