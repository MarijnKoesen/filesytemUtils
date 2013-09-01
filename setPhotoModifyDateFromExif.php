<?
$files = explode("\n", `ls -1 *.jpg`);
///opt/local/bin/exif IMG_1170.jpg | grep "Datum en tijdstip (o"

foreach($files as $file) {
    if (preg_match('/\.jpg$/', $file)) {
        $exifTime = `/opt/local/bin/exif {$file} | grep "Datum en tijdstip (o"`;
        $exifTime = str_replace("Datum en tijdstip (o|", '', $exifTime);
        $time = strtotime($exifTime);

        if ($time > 0)
            touch($file, $time);
    }
}


/*
$files = explode("\n", `ls -1`);
///opt/local/bin/exif IMG_1170.jpg | grep "Datum en tijdstip (o"

foreach($files as $file) {
    if (preg_match('/\.jpg$/', $file)) {
        $takenTime = filemtime($file);

        $dateString = date("Y-m-d H:i:s", $takenTime);
        echo shell_exec("/opt/local/bin/exiv2 -M'add Exif.Photo.DateTimeOriginal Ascii " . $dateString . "' '" . $file . "'");
    }
}
*/
