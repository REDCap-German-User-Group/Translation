<?php

if (PHP_SAPI !== 'cli') {
	exit("This script is intended for CLI use only.\n");
}

if ($argc !== 2) {
	fwrite(STDERR, "Usage: php check_language_file.php <language-file>\n");
	exit(1);
}

$languageFile = $argv[1];

if (!is_readable($languageFile)) {
	fwrite(STDERR, "Unable to read language file: $languageFile\n");
	exit(1);
}

$entries = @parse_ini_file($languageFile);

if ($entries !== false) {
	echo count($entries) . PHP_EOL;
	exit(0);
}

$contents = file_get_contents($languageFile);

if ($contents === false) {
	fwrite(STDERR, "Unable to read language file: $languageFile\n");
	exit(1);
}

$lastSuccessfulKey = findLastSuccessfulKey($contents);

echo 'ERROR after key ' . ($lastSuccessfulKey ?? 'NONE') . PHP_EOL;
exit(1);

function findLastSuccessfulKey(string $contents): ?string
{
	list($preamble, $entries) = splitLanguageEntries($contents);

	if (@parse_ini_string($preamble) === false) {
		return null;
	}

	$low = 0;
	$high = count($entries);

	while ($low < $high) {
		$mid = intdiv($low + $high + 1, 2);

		if (canParseEntries($preamble, $entries, $mid)) {
			$low = $mid;
		} else {
			$high = $mid - 1;
		}
	}

	if (isset($entries[$low]) && canParseEntryPrefix($preamble, $entries, $low)) {
		return $entries[$low]['key'];
	}

	return $low === 0 ? null : $entries[$low - 1]['key'];
}

function splitLanguageEntries(string $contents): array
{
	$lines = preg_split('/\R/', $contents);
	$preamble = '';
	$entries = array();
	$currentEntry = null;

	foreach ($lines as $line) {
		$line .= "\n";

		if (preg_match('/^([A-Za-z0-9_.:-]+)\s*=/', $line, $matches)) {
			if ($currentEntry !== null) {
				$entries[] = $currentEntry;
			}

			$currentEntry = array(
				'key' => $matches[1],
				'text' => $line,
			);
			continue;
		}

		if ($currentEntry === null) {
			$preamble .= $line;
		} else {
			$currentEntry['text'] .= $line;
		}
	}

	if ($currentEntry !== null) {
		$entries[] = $currentEntry;
	}

	return array($preamble, $entries);
}

function canParseEntries(string $preamble, array $entries, int $entryCount): bool
{
	return @parse_ini_string(getEntryPrefix($preamble, $entries, $entryCount)) !== false;
}

function canParseEntryPrefix(string $preamble, array $entries, int $entryIndex): bool
{
	$contents = getEntryPrefix($preamble, $entries, $entryIndex);
	$lines = preg_split('/\R/', $entries[$entryIndex]['text']);

	foreach ($lines as $line) {
		$contents .= $line . "\n";

		if (@parse_ini_string($contents) !== false) {
			return true;
		}
	}

	return false;
}

function getEntryPrefix(string $preamble, array $entries, int $entryCount): string
{
	$contents = $preamble;

	for ($i = 0; $i < $entryCount; $i++) {
		$contents .= $entries[$i]['text'];
	}

	return $contents;
}
