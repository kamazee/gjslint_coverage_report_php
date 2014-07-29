<?php

if (!isset($_SERVER['argv'][1]) || !isset($_SERVER['argv'][2])) {
    echo 'Usage:- php gjslintReport.php [inputFile] [outputFile]';
    die();
}

$handle = fopen($_SERVER['argv'][1], "r");
$errors = array();
while (!feof($handle)) {
    $line = fgets($handle);
    if (substr($line, 0, 15) == '----- FILE  :  ') {
        $file = trim(str_replace(array('----- FILE  :  ', '-----'), '', $line));
        $errors[$file] = array();
        $key = 0;
    } else if (substr($line, 0, 5) == 'Line ') {
        $error = explode(', ', $line);
        $errors[$file][$key] = array();
        $errors[$file][$key]['line'] = trim(str_replace('Line ', '',
                        $error[0]));
        $errors[$file][$key]['reason'] = trim($error[1]);
        $errors[$file][$key]['severity'] = 'error';
        $key++;
    }
}

fclose($handle);
$xml = '<jslint>';
foreach ($errors as $fileName => $issues) {

    $xml .= '<file name="' . $fileName . '">';
    foreach ($issues as $issue) {
        $xml .= '<issue line="' . $issue['line'] . '" severity="' .
                $issue['severity'] . '" reason="' . str_replace('"', "'",
                        $issue['reason']) . '"/>';
    }
    $xml .= '</file>';
}

$xml .='</jslint>';

$handle = fopen($_SERVER['argv'][2], "w");
fwrite($handle, $xml);

fclose($handle)
?>
