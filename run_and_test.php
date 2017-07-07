<?php
	$sites = array(/*'mare',*/ /*'oapl',*/ /*'ccyf',*/ 'pfsf', 'lvc', 'aask',
				   'ampf', 'hck', 'fco', 'afl', 'ksnap', 'imtnga', 'auk');
	foreach ($sites as $s) {
		$output = shell_exec("php SpiderDriver.php $s");
		$directory = __DIR__ . DIRECTORY_SEPARATOR . 'Listings' .
				DIRECTORY_SEPARATOR . $s;
		if (!file_exists($directory))
			echo "$s: failed to create a directory\r\n";
		else {
			$s = strtoupper($s);
			$f = new FilesystemIterator($directory, FilesystemIterator::SKIP_DOTS);
			echo $s . ' : ' . iterator_count($f) . "\r\n";
		}
	}
?>