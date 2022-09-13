<?php
# Erstellt aus einem englischen und einem anderen (deutschen) Language file eine
# neues Language-File zu debug-zwecken, das die deutschen texte anzeigt, aber
# ein rotes Fragezeichen voranstellt, das bei mouseover den key und den
# englischen originaltext anzeigt

if (!$argv[3]) {
    echo "Aufruf mit \n   php create_debug_file.php English.ini German.ini newfile.ini";
    exit;
}

$english = parse_ini_file($argv[1]);
$newlang = parse_ini_file($argv[2]);
$outfile = $argv[3];
$outarr = array();

$json_flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
foreach($newlang as $key => $value) {
    $outarr[] = "$key = " .
              json_encode("<span title=\"$key: " .
                          trim(json_encode($english[$key], $json_flags), '"') . "\">❓</span>" .
                          $value, $json_flags);
}
file_put_contents($outfile, implode("\r\n", $outarr))
?>
