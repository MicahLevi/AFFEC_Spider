<?php
	require_once('SpiderMapper.php');
	
	class OAPL_Mapper extends Listing_Mapper{
		
		function mapListing($filename) {
			$profile = file_get_contents($filename);
			$listing = new Listing();
			$child = new Child();
			$caseworker  = new Contact();
			if (preg_match('/Gender:/isU', $profile, $matched)) {
				if (preg_match_all('/src="(child_pictures[^"]*)".*Contact:<\/span>' .
					'([^<]*)<.*Phone:<\/span>([^<]*)<.*Email:<\/span>\s*' .
					'<a href="mailto:([^"?]*)[?"].*<span class="h1">([^<]*)<.*' .
					'(?:Group|Child) ID:<\/span>([^<]*)<.*Status:<\/span>([^<]*).*' .
					'Age:<\/span>([^<]*)<.*Gender:<\/span>([^<]*)<.*Ethnicity:<\/span>' .
					'([^<]*)<.*(?:Group|Child)\sProfile<\/span>.*(?:<p>)(.*)(?:<[^\/p])/isU',
					$profile, $matches))
				{
					$child->age = $matches[8][0];
					$child->sex = $matches[9][0];
					$child->race = $matches[10][0];
					$child->bio = $matches[11][0];
					$child->name = $matches[5][0];
				}
			}
			else if (preg_match_all('/src="(child_pictures[^"]*)".*Contact:<\/span>([^<]*)<.*' .
									'Phone:<\/span>([^<]*)<.*Email:<\/span>\s*' .
									'<a href="mailto:([^"?]*)[?"].*<span class="h1">([^<]*)<.*' .
									'(?:Group|Child) ID:<\/span>([^<]*)<.*Status:<\/span>([^<]*).*' .
									'(?:Group|Child)\sProfile<\/span>.*(?:<p>)(.*)(?:<[^\/p])/isU', 
									$profile, $matches))
			{
				$listing->bio = $matches[11][0];
				$listing->names = $matches[5][0];
			}
			else
				throw new Exception('Failed to parse listing $filename');
			
			$listing->photos = $matches[1][0];
			$caseworker->name = $matches[2][0];
			$caseworker->phone = $matches[3][0];
			$caseworker->email = $matches[4][0];
			
			$child->stateId = $matches[6][0];
			$child->status = $matches[7][0];
			//$listing->state = 'Ohio';
			
			$child->primaryContact = $caseworker;
			$listing->kids = array($child);
			return $listing;
		}
	}
?>