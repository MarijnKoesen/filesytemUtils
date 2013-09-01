<?php
/**
 * This script copies the file dates from 1 directory and applies
 * them on the same files in the target directory.
 *
 * If you have copied some files and it has messed up the modification
 * and create dates, you can use this script to fix them
 *
 * Usage: ./copyFileDates <original-directory> <directory-to-fix>

 * @author Marijn Koesen
 */

if ($argc != 3 || !is_dir($argv[1]) || !is_dir($argv[2]))   
    die("Usage: {$argv[0]} <original-directory> <directory-to-fix>\n");


/**
 * Get a recursive list of file modified dates where the md5sum of the file 
 * is the key e.g.:
 * array(
 *   [14cff01fff26f4f5e43ba091ffaa993a] => Array
 *      (
 *          [atime] => 1378034229
 *          [ctime] => 1375535590
 *          [mtime] => 1370802255
 *      )

 *   [3faa34af6d32423abfe19b04056f1b9c] => Array
 *      (
 *          [atime] => 1378034230
 *          [ctime] => 1375535590
 *          [mtime] => 1286038767
 *      )
 * );
 * 
 * @return array with the file dates
 */
function getFileDateList($directory) {
    $list = array();

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $fileInfo) {
        if ( ! $fileInfo->isFile()) 
            continue;

        echo "Indexing: " . $fileInfo->getRealPath() . "\n";

        $md5 = md5_file($fileInfo->getRealPath());
        $list[$md5] = array(
            'atime' => $fileInfo->getATime(),
            'ctime' => $fileInfo->getCTime(),
            'mtime' => $fileInfo->getMTime(),
        );
    }

    return $list;
}

/**
 * Apply a fileDateData array (acquired from getFileDateList) to
 * the target directory $directory
 */
function applyFileDatesToDirectory($directory, $fileDateList)
{
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $fileInfo) {
        if ( ! $fileInfo->isFile()) 
            continue;

        applyFileDatesToFile($fileInfo->getRealPath(), $fileDateList);
    }
}

/**
 * Apply a fileDateData array to a single file, changing the modified
 * and access time
 */
function applyFileDatesToFile($path, $dateList=array())
{
    $md5 = md5_file($path);

    if (isset($dateList[$md5])) {
        echo "Applying " . date("Y-m-d H:i", $dateList[$md5]['mtime']) . " to " . $path . "\n";
        touch($path, $dateList[$md5]['mtime'], $dateList[$md5]['atime']);
    } else {
        echo "No data found for " . $path . "\n";
    }
}

applyFileDatesToDirectory($argv[2], getFileDateList($argv[1]));
