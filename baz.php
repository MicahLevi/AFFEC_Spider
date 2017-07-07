<?php
	require_once(/*dirname(*/dirname(__DIR__)/*)*/ . '/public_html/php/simple_html_dom.php');
	require_once(__DIR__ . DIRECTORY_SEPARATOR . 'Mapper' . DIRECTORY_SEPARATOR . 'class.SpidListing.php');
	
	$profile = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR .'Listings' . DIRECTORY_SEPARATOR . 'OAPL' . DIRECTORY_SEPARATOR . /*'SCNET0110514'*/ /*'test.txt'*/ 'G280');
	echo $profile . "\r\n";
	if (preg_match_all('/src="(child_pictures[^"]*)".*Contact:<\/span>([^<]*)<.*Phone:<\/span>([^<]*)' .
				'<.*Email:<\/span>\s*<a href="mailto:([^"?]*)[?"].*<span class="h1">([^<]*)<' .
				'.*Child ID:<\/span>([^<]*)<.*Status:<\/span>([^<]*)<.*Age:<\/span>([^<]*)<' .
				'.*Gender:<\/span>([^<]*)<.*Ethnicity:<\/span>([^<]*)<.*Child Profile<\/span>.*<p>(.*)(?:<a)/isU', 
				$profile, $matches))
	{
		print_r($matches);
		$listing = new Listing();
		$child = new Child();
		$caseworker  = new Contact();
		$listing->photos = $matches[1][0];
		$caseworker->name = $matches[2][0];
		$caseworker->phone = $matches[3][0];
		$caseworker->email = $matches[4][0];
		$child->name = $matches[5][0];
		$child->stateId = $matches[6][0];
		$child->status = $matches[7][0];
		$child->age = $matches[8][0];
		$child->sex = $matches[9][0];
		$child->race = $matches[10][0];
		$child->bio = $matches[11][0];
		$listing->state = 'Ohio';
		
		$child->primaryContact = $caseworker;
		$listing->kids = array($child);
		print_r($listing);
	}
	else
		echo "Nope.\r\n";
?>

//Age:<\/span>([^<]*)<*Gender:<\/span>([^<]*)<.*Ethnicity:<\/span>([^<]*)<.*

//src="(child_pictures[^"]*)".*Contact:<\/span>([^<]*)<.*Phone:<\/span>([^<]*)<.*Email:<\/span>\s*<a href="mailto:([^"?]*)[?"].*<span class="h1">([^<]*)<.*(?:Group|Child) ID:<\/span>([^<]*)<.*Status:<\/span>([^<]*).*(?:Group|Child)\sProfile<\/span>.*(?:<p>)(.*)(?:<[^\/p])