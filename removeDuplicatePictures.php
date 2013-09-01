<?
/**
 * Somehow I ended up with a photo directory with duplicates:
 * - IMG-1344.jpg
 * - IMG-1344-2.jpg
 * - IMG-3424.cr2
 * - IMG-3424-2.cr2
 * etc
 * 
 * This script removes to '-2', '-3' files if they have the same
 * md5hash as the file without the postfix.
 *
 * NOTE: by default the directories are recursively traversed
 *
 * Usage: removeDuplicatePictures.php <directory>
 *
 * @author Marijn Koesen
 */

if ($argc != 2 || !is_dir($argv[1]) || !is_dir($argv[1]))   
    die("Usage: {$argv[0]} <directory>\n");

/**
 * Check all pictures in the directory and remove any duplicates
 *
 * @param string $dir The directory to check
 * @param bool $recursive 
 */
function checkDir($dir, $recursive=true) {
	echo "Checkdir $dir \n";
	$files = scandir($dir);

	foreach($files as $file) {
		$fullPath = $dir . '/' . $file;
		if ($file == '.' || $file == '..') continue;

		if (is_file($fullPath)) { 
			if (preg_match('/-[0-9]+\.(JPG|CR2)$/i', $file)) {
				$baseImageName = preg_replace("/(.*)-[0-9]+\.(jpg|cr2)/i", "\\1.\\2", $file);

				echo "Checking: $file - " . $baseImageName . " = ";
				if (md5_file($fullPath) == md5_file($dir . '/' . $baseImageName)) {
					echo " same! Removing...\n";
					unlink($fullPath);					
				} else {
					//echo " unique\n";
				}
			}
		} else if (is_dir($dir . '/' . $file) && $recursive) {	
			checkDir($dir . '/' . $file);
		}
	}
}


checkDir($argv[1]);
