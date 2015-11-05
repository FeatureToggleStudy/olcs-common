#!/usr/bin/php
<?php
// @codingStandardsIgnoreFile

$translationLocation = __DIR__ . '/../Common/config/language/';

$translations = include($translationLocation . 'en_GB.php');

$translated = include($translationLocation . 'google-translated.php');

//$directories = array(
//    realpath(__DIR__ . '/../Common/config/list-data/'),
//    realpath(__DIR__ . '/../Common/src/'),
//    realpath(__DIR__ . '/../test/'),
//    realpath(__DIR__ . '/../Common/view/'),
//    realpath(__DIR__ . '/../../olcs-internal/module/'),
//    realpath(__DIR__ . '/../../olcs-internal/test/'),
//    realpath(__DIR__ . '/../../olcs-selfserve/module/'),
//    realpath(__DIR__ . '/../../olcs-selfserve/test/')
//);

$unusedArray = $foundArray = array();

foreach ($translations as $key => $value) {

    $found = true;
    /** Ignore the grep for now just to speed things up
    $found = false;

    foreach ($directories as $directory) {
        $response = shell_exec('grep -r "' . $key . '" ' . $directory);

        if (!empty($response)) {
            $found = true;
            break;
        }
    }
     */

    $value = preg_replace('/(\s+)/', ' ', $value);

    // fix quotes in values
    $value = str_replace("'", "\'", $value);

    // fix quotes in keys
    $key = str_replace("'", "\'", $key);

    if ($found) {
        $foundArray[$key] = $value;
    } else {
        $unusedArray[$key] = $value;
    }
}

ksort($foundArray);
ksort($unusedArray);

$cyGbContent = $enGbContent = $toBeContent = '<?php
// @codingStandardsIgnoreFile
return array(';

function wrapLine($string) {

    return $string;
//    $newString = '';
//
//    $remainingString = $string;
//
//    if (strlen($remainingString) <= 120) {
//        $newString = $remainingString;
//    }
//
//    while (strlen($remainingString) > 120) {
//
//        $offset = 120 - strlen($remainingString);
//
//        $splitSpaceOffset = strrpos($remainingString, ' ', $offset);
//
//        $lines = substr_replace($remainingString, "\n", $splitSpaceOffset, 1);
//
//        list($trimedLine, $remainingString) = explode("\n", $lines);
//
//        $newString .= $trimedLine . "\n";
//
//        $remainingString = "        " . $remainingString;
//
//        if (strlen($remainingString) < 120) {
//            $newString .= $remainingString;
//        }
//    }
//
//    return $newString;
}

$toBeTranslated = [];

foreach ($foundArray as $key => $value) {

    $gbLine = wrapLine("    '" . $key . "' => '" . $value . "',");

//    if (isset($translated[$key])) {
//        $welsh = str_replace("\n", '', $translated[$key]);
//        $welsh = addslashes($welsh);
//
//    } else {
//        $toBeTranslated[$key] = $value;
//        $welsh = $value;
//        $welsh = preg_replace('/[AU]/', 'Y', $welsh);
//        $welsh = preg_replace('/[IO]/', 'E', $welsh);
//        $welsh = preg_replace('/[au]/', 'y', $welsh);
//        $welsh = preg_replace('/[io]/', 'e', $welsh);
//
//        $toBeContent .= "\n" . wrapLine("    '" . $key . "' => '" . $value . "',");
//    }

    $welsh = '{WELSH} ' . $value;

    $cyLine = wrapLine("    '" . $key . "' => '" . $welsh . "',");

    $enGbContent .= "\n" . $gbLine;
    $cyGbContent .= "\n" . $cyLine;
}

$enGbContent .= "\n    // Potentially unused (Not found with grep)";
$cyGbContent .= "\n    // Potentially unused (Not found with grep)";

foreach ($unusedArray as $key => $value) {

    $gbLine = wrapLine("    '" . $key . "' => '" . $value . "',");
    $cyLine = wrapLine("    '" . $key . "' => 'W " . $value . "',");

    $enGbContent .= "\n" . $gbLine;
    $cyGbContent .= "\n" . $cyLine;
}

$enGbContent .= "\n);\n";
$cyGbContent .= "\n);\n";
$toBeContent .= "\n);\n";

file_put_contents($translationLocation . 'en_GB.php', $enGbContent);
file_put_contents($translationLocation . 'cy_GB.php', $cyGbContent);
file_put_contents($translationLocation . 'to-be-translated.php', $toBeContent);


// markup partial translations
function translatePartials($partials) {
    // replicates file structure and nested partial includes
    foreach ($partials as $file) {
        if (!$file->isDot()) {
            if ($file->isDir()) {
                $subDir = new DirectoryIterator($file->getPathname());
                translatePartials($subDir);
            } else {
                $source = $file->getPathname();
                $dest = str_replace('en_GB', 'cy_GB', $source);

                $content = '<p><b>Translated to Welsh</b></p>'. file_get_contents($source);
                file_put_contents($dest, str_replace('en_GB', 'cy_GB', $content));
            }
        }
    }
}

$partials = new DirectoryIterator($translationLocation.'partials/en_GB');
translatePartials($partials);