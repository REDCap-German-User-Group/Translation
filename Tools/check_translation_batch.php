<?php

if (PHP_SAPI !== 'cli') exit("CLI only.\n");
if ($argc !== 3) {
	fwrite(STDERR, "Usage: php Tools/check_translation_batch.php <English_NEW.ini> <German_NEW.ini>\n");
	exit(1);
}
$english = @parse_ini_file($argv[1]);
$german = @parse_ini_file($argv[2]);
if (!is_array($english) || !is_array($german)) {
	fwrite(STDERR, "Unable to parse one or both translation batch files.\n");
	exit(1);
}
$errors = array();
foreach ($english as $key => $source) {
	if (!array_key_exists($key, $german)) {
		$errors[] = "$key: missing German translation";
		continue;
	}
	foreach (array(
		'placeholder' => '/\{[0-9]+(?::[^{}]*)?\}/u',
		'action tag' => '/@[A-Z][A-Z0-9_]*/',
	) as $kind => $pattern) {
		preg_match_all($pattern, $source, $sourceMatches);
		preg_match_all($pattern, $german[$key], $targetMatches);
		$expected = $sourceMatches[0];
		$actual = $targetMatches[0];
		sort($expected);
		sort($actual);
		if ($expected !== $actual) $errors[] = "$key: $kind mismatch";
	}
}
if ($errors) {
	fwrite(STDERR, implode("\n", $errors) . "\n");
	exit(1);
}
echo count($english) . " source keys have German entries with matching placeholders and action tags.\n";
