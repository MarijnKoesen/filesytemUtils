<?php
/**
 * When exporting a database in DEVONthink i got a lot of 'DEVONtech_storage' files
 * as well as a load of '._someName' files. This script removes those files.
 *
 * Usage: cleanUpDevonThinkExport <directory>
 *
 * @author Marijn Koesen
 */

if ($argc != 2 || !is_dir($argv[1]) || !is_dir($argv[1]))   
    die("Usage: {$argv[0]} <directory>\n");

// Remove unwanted files from a devonthink export
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($argv[1], FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $fileInfo) {
    if ( ! $fileInfo->isFile())
        continue;

    $baseName = $fileInfo->getBaseName(); 
    
    // Remove DEONthink meta data
    if ($fileInfo->getBaseName() == 'DEVONtech_storage') 
        unlink($fileInfo->getRealPath());

    // Remove other timemachine meta data
    if (preg_match("/^\._/", $baseName))
        unlink($fileInfo->getRealPath());
}
