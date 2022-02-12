<?php

require __DIR__ . "/build-base.php";

$cache = [];

if (count($argv) !== 2) {
    printError("Invalid argument given");
    exit(1);
}

foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($argv[1], FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_PATHNAME)) as $file) {
    printWarning("find: $file");
    if (substr($file, -3) !== ".md") {
        printStatement("skipped due to invalid extension");
        continue;
    }
    $contents = file_get_contents($file);
    if ($contents === false) {
        printError("error in the recovery of the file content");
        exit(1);
    }
    printStatement("file content get");
    $filename = getFilename($file);
    $category = substr($filename, strlen($filename) - 9) === "_category";
    $name = getId($contents);
    if ($category) {
        printStatement("category format detected");
        $categName = substr($filename, strlen($filename) - 9);
        if (isset($cache[$categName][$name . "_category"])) {
            printError("category index for: $categName already exist");
            exit(1);
        }
    } else {
        printStatement("article format detected");
        $categ = getCategory($contents);
        if (isset($cache[$categ][$name])) {
            printError("$name for category: $categ already exist");
            exit(1);
        }
        $cache[$categ][$name] = $file;
    }
    printSuccess(getFilename($file) . " valid");
}

foreach ($cache as $categ => $art) {
    $asIndex = false;
    foreach ($art as $name => $file) {
        $path = $file;
        $path = str_replace($name . ".md", "", $path);
        if (file_exists($path . $categ . "_category.md")) {
            $asIndex = true;
            continue;
        }
    }
    if (!$asIndex) {
        printError("category: $categ has no index");
        exit(1);
    }
}
