<?php
	$str = fopen(__DIR__ . '\\Listings\\MARE\\sibgrp_C07676_C07677_C07678', 'r');
	while (($line = fgets($str)) !== false) {
		if (!isset($line) || trim($line) === '' || trim($line) === '\r\n')
			continue;
		$obj = @unserialize($line);
		if ($obj === NULL)
			echo $line . "\r\n";
		else
			print_r($obj);
	}


/*
	$url = 'http://www.mare.org/For-Families/View-Waiting-Children';
	$cookie = 'tempCookie';
	$agent = "Mozilla/5.0 (Windows NT 10.0; WOW64; rv:53.0) Gecko/20100101 Firefox/53.0";
	$channel = curl_init();
	curl_setopt($channel, CURLOPT_URL, $url);
	curl_setopt($channel, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($channel, CURLOPT_USERAGENT, $agent);
	curl_setopt($channel, CURLOPT_RETURNTRANSFER, true);
	//curl_setopt($channel, CURLOPT_MAXREDIRS, 5);
	//curl_setopt($channel, CURLOPT_CONNECTTIMEOUT, 30); 
	//curl_setopt($channel, CURLOPT_TIMEOUT, 30);
	curl_setopt($channel, CURLOPT_VERBOSE, 1);
	curl_setopt($channel, CURLOPT_COOKIEJAR, $cookie);				
	curl_setopt($channel, CURLOPT_COOKIEFILE, $cookie);
	curl_setopt($channel, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($channel, CURLOPT_REFERER, 'http://www.mare.org/For-Families/View-Waiting-Children');

	$result = curl_exec($channel);
	curl_close($channel);
	
	$url = 'http://www.mare.org/For-Families/View-Waiting-Children/view/Detail?id=2777';
	$channel = curl_init();
	curl_setopt($channel, CURLOPT_URL, $url);
	curl_setopt($channel, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($channel, CURLOPT_USERAGENT, $agent);
	curl_setopt($channel, CURLOPT_RETURNTRANSFER, true);
	//curl_setopt($channel, CURLOPT_MAXREDIRS, 5);
	//curl_setopt($channel, CURLOPT_CONNECTTIMEOUT, 30); 
	//curl_setopt($channel, CURLOPT_TIMEOUT, 30);
	curl_setopt($channel, CURLOPT_POST, true);
	curl_setopt($channel, CURLOPT_POSTFIELDS, $postfields);
	curl_setopt($channel, CURLOPT_VERBOSE, 1);
	curl_setopt($channel, CURLOPT_COOKIEJAR, $cookie);				
	curl_setopt($channel, CURLOPT_COOKIEFILE, $cookie);
	curl_setopt($channel, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($channel, CURLOPT_REFERER, 'http://www.mare.org/For-Families/View-Waiting-Children');

	$result = curl_exec($channel);
	
	preg_match('/<div id="childview"(.*)<script/isU', $result, $matches);
	print_r($matches);
	curl_close($channel);
	*/

?>