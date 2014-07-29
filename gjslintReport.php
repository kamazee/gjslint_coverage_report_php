<?php

$usage = <<<EOF
Usage: php ./gjslintReport.php inputFile outputFile
Use '-' as the input file if you would like to use stdin instead of it.
EOF;


$stderr = fopen('php://stderr', 'w');

if (!isset($_SERVER['argv'][1]) || !isset($_SERVER['argv'][2])) {
    fwrite($stderr, $usage);
    exit(1);
}

$inputFile = $_SERVER['argv'][1] !== '-' ?: 'php://stdin';
$outputFile = $_SERVER['argv'][2];

$inputHandle = fopen($inputFile, "r");
if (!$inputHandle) {
    fwrite($stderr, 'Error while opening input file');
    exit(1);
}
$errors = array();
while (false !== ($line = fgets($inputHandle))) {
    if (substr($line, 0, 15) == '----- FILE  :  ') {
        $file = trim(str_replace(array('----- FILE  :  ', '-----'), '', $line));
        $errors[$file] = array();
        $key = 0;
    } elseif (substr($line, 0, 5) == 'Line ') {
        $error = explode(', ', $line, 2);
        $errors[$file][$key] = array();
        $errors[$file][$key]['line'] = trim(str_replace('Line ', '', $error[0]));
        $errors[$file][$key]['reason'] = trim($error[1]);
        $errors[$file][$key]['severity'] = 'error';
        $key++;
    }
}

fclose($inputHandle);


$outputHandle = fopen($outputFile, 'w');
fwrite($outputHandle, '<jslint>');
foreach ($errors as $fileName => $issues) {
    fwrite($outputHandle, '<file name="' . $fileName . '">');
    foreach ($issues as $issue) {
        fwrite(
            $outputHandle,
            sprintf(
                '<issue line="%d" severity="%s" reason="%s" />',
                $issue['line'],
                $issue['severity'],
                $issue['reason']
            ));
    }
    fwrite($outputHandle, '</file>');
}

fwrite($outputHandle, '</jslint>');

