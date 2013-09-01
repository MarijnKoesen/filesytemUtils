<?
/**
 * This program will update the exif 'DateTaken' of the image files.
 *
 * The files are sorted by filename, and the first image is given the specified timestring.
 * All subsequent images are given timestring+i so that the images can be properly ordered on the exif DateTaken field.
 *
 * Usage: php setPhotoExifDateFromFilename.php <timestring> <files> [..]
 *
 * @author Marijn Koesen
 */

error_reporting(E_ALL);

function sortFileNames($name1, $name2) {
    // Get data to compare
    $path1 = basename($name1);
    $path2 = basename($name2);

    // Extract image numbers (image1.jpg -> 1, image30_2 -> 30)
    $imageNumber1 = 0;
    $imageNumber2 = 0;

    if (preg_match('|([0-9]+)|', $path1, $match1)) {
        $imageNumber1 = (int)$match1[1];
    }

    if (preg_match('|([0-9]+)|', $path2, $match2)) {
        $imageNumber2 = (int)$match2[1];
    }

    echo $path1 . " ($imageNumber1) <> " . $path2  . " ($imageNumber2)\n";

    // Compare the numbers
    if ($imageNumber1 < $imageNumber2) {
        return -1;
    } else if ($imageNumber1 == $imageNumber2) {
        return 0;
    } else  {
        return 1;
    }
}


function usage($message = '') {
    global $argv;
        
    if ($message != '') echo $message;

    echo "This program will update the exif 'DateTaken' of the image files.\n\n";
    echo "The files are sorted by filename, and the first image is given the specified timestring.\n";
    echo "All subsequent images are given timestring+i so that the images can be properly ordered on the exif DateTaken field.\n\n";
    
    echo "Usage: php {$argv[0]} <timestring> <files> [..]\n";
    exit;
}

if (count($argv) < 3 || strtotime($argv[1]) < 1) {
    if (isset($argv[1]) && strtotime($argv[1]) < 1) {
        usage("Error: timestamp / timestring is invalid. Use valid php's strtotime() format.\n\n");
    } else {
        usage();
    }
} else {
    // Extract all jpg images  from the arguments
    $files = array();

    // If we got a directory in the params, load the files in that dir
    foreach($argv as $param) {
        if (is_dir($param)) {
            $dirFiles = scandir($param);
            foreach($dirFiles as $file) {
                $argv[] = $param . '/' . $file;
            }
        }
    }

    // Check the collected files (from argv and parsed directoryes) and extract all jpgs
    foreach($argv as $image) {
        if (preg_match("|\.jpg$|i", trim($image))) {
            $files[] = trim($image);
        }
    }

    // Sort the images
    usort($files, "sortFileNames");

    echo "Updating " . count($files) . " files..\n";

    // Now update their exif data and last modified time
    $time = strtotime($argv[1]);
    foreach($files as $file) {
        $file = addslashes($file);
        $dateString = date("Y-m-d H:i:s", $time);

        echo " - " . $file . " = " . $dateString . "\n";

        echo shell_exec("exiv2 -M'add Exif.Photo.DateTimeOriginal Ascii " . $dateString . "' '" . $file . "'");
        touch($file, $time);
        $time += 60; // +1 minute
    }

    echo "\nDone.\n";
}
