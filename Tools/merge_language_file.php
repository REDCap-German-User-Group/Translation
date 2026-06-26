<?php

if (PHP_SAPI !== 'cli') {
	exit("This script is intended for CLI use only.\n");
}

$usage = "Usage: php Tools/merge_language_file.php <new-language-file> <target-language-file> [--dry-run]\n";

if ($argc < 3 || $argc > 4 || ($argc === 4 && $argv[3] !== '--dry-run')) {
	fwrite(STDERR, $usage);
	exit(1);
}

$newLanguageFile = $argv[1];
$targetLanguageFile = $argv[2];
$dryRun = $argc === 4 && $argv[3] === '--dry-run';

if (!is_readable($newLanguageFile)) {
	fwrite(STDERR, "Unable to read new language file: $newLanguageFile\n");
	exit(1);
}

if (!is_readable($targetLanguageFile)) {
	fwrite(STDERR, "Unable to read target language file: $targetLanguageFile\n");
	exit(1);
}

$newContents = file_get_contents($newLanguageFile);
$targetContents = file_get_contents($targetLanguageFile);

if ($newContents === false || $targetContents === false) {
	fwrite(STDERR, "Unable to read one or more input files.\n");
	exit(1);
}

$newEntries = parseLanguageEntries($newContents, true);
$targetEntries = parseLanguageEntries($targetContents, false);

if (empty($newEntries['entries'])) {
	fwrite(STDERR, "No language keys found in new language file: $newLanguageFile\n");
	exit(1);
}

$duplicateNewKeys = findDuplicateKeys($newEntries['entries']);

if (!empty($duplicateNewKeys)) {
	fwrite(STDERR, "Duplicate keys in new language file: " . implode(', ', $duplicateNewKeys) . "\n");
	exit(1);
}

$targetKeyIndexes = array();

foreach ($targetEntries['entries'] as $index => $entry) {
	if (isset($targetKeyIndexes[$entry['key']])) {
		fwrite(STDERR, "Duplicate key in target language file: {$entry['key']}\n");
		exit(1);
	}

	$targetKeyIndexes[$entry['key']] = $index;
}

$mergedEntries = $targetEntries['entries'];
$inserted = 0;
$replaced = 0;

foreach ($newEntries['entries'] as $newEntry) {
	if (isset($targetKeyIndexes[$newEntry['key']])) {
		$mergedEntries[$targetKeyIndexes[$newEntry['key']]]['text'] = $newEntry['text'];
		$replaced++;
		continue;
	}

	$insertAt = findInsertionIndex($mergedEntries, $newEntry['key']);
	array_splice($mergedEntries, $insertAt, 0, array($newEntry));
	reindexKeys($mergedEntries, $targetKeyIndexes);
	$inserted++;
}

$mergedContents = renderLanguageFile($targetEntries['preamble'], $mergedEntries, $targetEntries['trailing']);
$parsed = @parse_ini_string($mergedContents);

if ($parsed === false) {
	fwrite(STDERR, "Merged language file is not parseable by PHP.\n");
	exit(1);
}

if (!$dryRun && file_put_contents($targetLanguageFile, $mergedContents) === false) {
	fwrite(STDERR, "Unable to write target language file: $targetLanguageFile\n");
	exit(1);
}

$action = $dryRun ? 'Would merge' : 'Merged';
echo "$action $inserted inserted and $replaced replaced keys into $targetLanguageFile\n";
echo count($parsed) . " target keys after merge\n";

function parseLanguageEntries(string $contents, bool $attachLeadingComments): array
{
	$lines = preg_split('/\R/', $contents);
	$preamble = array();
	$entries = array();
	$pending = array();
	$current = null;
	$seenEntry = false;
	$trailing = array();

	foreach ($lines as $line) {
		if (preg_match('/^([A-Za-z0-9_.:-]+)\s*=/', $line, $matches)) {
			if ($current !== null) {
				$entries[] = $current;
			} elseif (!$seenEntry && !$attachLeadingComments) {
				$preamble = array_merge($preamble, $pending);
				$pending = array();
			}

			$current = array(
				'key' => $matches[1],
				'leading' => $seenEntry || $attachLeadingComments ? $pending : array(),
				'text' => array($line),
			);
			$pending = array();
			$seenEntry = true;
			continue;
		}

		if ($current !== null) {
			if (isCommentOrBlank($line)) {
				$entries[] = $current;
				$current = null;
				$pending[] = $line;
			} else {
				$current['text'][] = $line;
			}
			continue;
		}

		$pending[] = $line;
	}

	if ($current !== null) {
		$entries[] = $current;
	} elseif ($seenEntry) {
		$trailing = $pending;
	} else {
		$preamble = $pending;
	}

	return array(
		'preamble' => $preamble,
		'entries' => $entries,
		'trailing' => $trailing,
	);
}

function isCommentOrBlank(string $line): bool
{
	return trim($line) === '' || preg_match('/^\s*[;#]/', $line) === 1;
}

function findDuplicateKeys(array $entries): array
{
	$seen = array();
	$duplicates = array();

	foreach ($entries as $entry) {
		if (isset($seen[$entry['key']])) {
			$duplicates[$entry['key']] = true;
		}

		$seen[$entry['key']] = true;
	}

	return array_keys($duplicates);
}

function reindexKeys(array $entries, array &$indexes): void
{
	$indexes = array();

	foreach ($entries as $index => $entry) {
		$indexes[$entry['key']] = $index;
	}
}

function findInsertionIndex(array $entries, string $newKey): int
{
	$newPrefix = keyPrefix($newKey);
	$bestBefore = null;
	$bestAfter = null;

	foreach ($entries as $index => $entry) {
		$entryKey = $entry['key'];

		if (!sharesPrefix($entryKey, $newPrefix)) {
			continue;
		}

		$comparison = compareLanguageKeys($entryKey, $newKey);

		if ($comparison < 0) {
			$bestBefore = $index;
		} elseif ($comparison > 0 && $bestAfter === null) {
			$bestAfter = $index;
		}
	}

	if ($bestBefore !== null) {
		return $bestBefore + 1;
	}

	if ($bestAfter !== null) {
		return $bestAfter;
	}

	$bestGlobalBefore = null;

	foreach ($entries as $index => $entry) {
		if (compareLanguageKeys($entry['key'], $newKey) < 0) {
			$bestGlobalBefore = $index;
		}
	}

	return $bestGlobalBefore === null ? count($entries) : $bestGlobalBefore + 1;
}

function keyPrefix(string $key): array
{
	$parts = explode('_', $key);

	if (count($parts) > 1 && preg_match('/^\d+$/', end($parts))) {
		array_pop($parts);
	}

	return $parts;
}

function sharesPrefix(string $key, array $prefix): bool
{
	$parts = explode('_', $key);

	if (count($parts) < count($prefix)) {
		return false;
	}

	for ($i = 0; $i < count($prefix); $i++) {
		if ($parts[$i] !== $prefix[$i]) {
			return false;
		}
	}

	return true;
}

function compareLanguageKeys(string $left, string $right): int
{
	$leftParts = explode('_', $left);
	$rightParts = explode('_', $right);
	$length = max(count($leftParts), count($rightParts));

	for ($i = 0; $i < $length; $i++) {
		if (!array_key_exists($i, $leftParts)) {
			return -1;
		}

		if (!array_key_exists($i, $rightParts)) {
			return 1;
		}

		$leftIsNumber = preg_match('/^\d+$/', $leftParts[$i]) === 1;
		$rightIsNumber = preg_match('/^\d+$/', $rightParts[$i]) === 1;

		if ($leftIsNumber && $rightIsNumber) {
			$difference = intval($leftParts[$i]) <=> intval($rightParts[$i]);
		} else {
			$difference = strcmp($leftParts[$i], $rightParts[$i]);
		}

		if ($difference !== 0) {
			return $difference;
		}
	}

	return 0;
}

function renderLanguageFile(array $preamble, array $entries, array $trailing): string
{
	$lines = $preamble;

	foreach ($entries as $entry) {
		$lines = array_merge($lines, $entry['leading']);
		$lines = array_merge($lines, $entry['text']);
	}

	$lines = array_merge($lines, $trailing);

	return rtrim(implode("\n", $lines), "\n") . "\n";
}
