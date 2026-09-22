<?php

function languageUpdaterVersion(string $version): void
{
	if (!preg_match('/^[0-9]+\.[0-9]+\.[0-9]+$/D', $version)) {
		throw new RuntimeException("Invalid REDCap version: $version");
	}
}

function languageUpdaterOptions(array $args, array $allowed): array
{
	$options = array();
	for ($i = 0; $i < count($args); $i++) {
		$name = $args[$i];
		if (!in_array($name, $allowed, true) || isset($options[$name]) || !isset($args[$i + 1])) {
			throw new RuntimeException("Invalid or incomplete option: $name");
		}
		$options[$name] = $args[++$i];
	}
	return $options;
}

function languageUpdaterResponse(string $language, string $version, string $include, ?string $fixture, string $endpoint): array
{
	if ($fixture !== null) {
		$json = @file_get_contents($fixture);
	} else {
		$url = $endpoint . '?' . http_build_query(array('get_json' => $language, 'include' => $include));
		$context = stream_context_create(array('http' => array('timeout' => 30, 'ignore_errors' => true)));
		$json = @file_get_contents($url, false, $context);
		if ($json === false) {
			throw new RuntimeException("Unable to fetch language JSON from $url.");
		}
		$status = $http_response_header[0] ?? '';
		if (!preg_match('#^HTTP/\S+ 200(?:\s|$)#', $status)) {
			throw new RuntimeException("Language endpoint returned $status for $language.");
		}
	}
	if ($json === false) {
		throw new RuntimeException("Unable to read language JSON for $language.");
	}
	try {
		$response = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
	} catch (JsonException $e) {
		throw new RuntimeException("Invalid JSON for $language: " . $e->getMessage());
	}
	if (!is_array($response) || ($response['language'] ?? null) !== $language || ($response['version'] ?? null) !== $version) {
		throw new RuntimeException("Language or REDCap version mismatch in $language response.");
	}
	foreach (explode(',', $include) as $branch) {
		if (!isset($response[$branch]) || !is_array($response[$branch]) || (array_is_list($response[$branch]) && $response[$branch] !== array())) {
			throw new RuntimeException("Missing or invalid $branch branch in $language response.");
		}
		foreach ($response[$branch] as $key => $value) {
			if (!is_string($key) || !preg_match('/^[A-Za-z0-9_.:-]+$/D', $key) || !is_string($value)) {
				throw new RuntimeException("Invalid $branch entry in $language response.");
			}
		}
	}
	return $response;
}

function languageUpdaterWrite(string $path, string $contents): void
{
	$directory = dirname($path);
	if (!is_dir($directory)) {
		throw new RuntimeException("Output directory does not exist: $directory");
	}
	$temp = tempnam($directory, '.language-updater-');
	if ($temp === false) {
		throw new RuntimeException("Unable to create temporary file in $directory.");
	}
	try {
		if (file_put_contents($temp, $contents) === false) {
			throw new RuntimeException("Unable to write $path.");
		}
		if (file_exists($path)) {
			chmod($temp, fileperms($path) & 0777);
		} else {
			chmod($temp, 0644);
		}
		if (!rename($temp, $path)) {
			throw new RuntimeException("Unable to replace $path.");
		}
	} finally {
		if (file_exists($temp)) unlink($temp);
	}
}
