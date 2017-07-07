<?php
	require_once('SpiderMapper.php');

	


	class CCYF_Mapper extends Listing_Mapper {
	
		function mapListing($filename) {
			$profile = file_get_contents($filename);
			if (preg_match_all('/img id="[^"]*"\s*src="([^"]*)".*id="[^"]*' .
							   'learning[^"]*">([^<]*)<.*id="[^"]*physical' .
							   '[^"]*">([^<]*)<.*id="[^"]*emotional[^"]*">' .
							   '([^<]*)<.*id="[^"]*behavioral[^"]*">([^<]*)<.*' .
							   'id="[^"]*developmental[^"]*">([^<]*)<.*' .
							   'kidName=(.*)&kidNum=(\d+(?:\/\d+)*)[&"].*id="[^"]*detailsdesc[^"]*">' .
							   '(.*)<\/div>/isU', $profile, $matches))
			{
				//print_r($matches);
				$listing = new Listing();
				
				$listing->photos = $matches[1][0];
				$needs = array('Learning_Needs_Overall__c'=>$matches[2][0],
									  'Physical_Needs_Overall__c'=>$matches[3][0],
									  'Emotional_Needs_Overall__c'=>$matches[4][0],
									  'Behavioral_Needs_Overall__c'=>$matches[5][0],
									  'Developmental_Needs_Overall__c'=>$matches[6][0]);
				
				$kids = array();
				$ids = explode('/', $matches[8][0]);
				//Sibling Group
				if (preg_match_all('/(?:(\w+)<p><p>)(.*)(?:<p><p><p><p>|\/span)/isU', $matches[9][0], $bios)) {
					
					foreach ($bios[1] as $i=>$name) {
						$child = new Child();
						$child->name = $name;
						$child->stateId = $ids[$i];//$matches[8][0];
						$child->bio = $bios[2][$i];
						$child->needs = $needs;
						$kids[] = $child;
					}
				}
				else if (count($ids) > 1) {
					preg_match_all('/(.*)<p><p>/isU', $matches[9][0], $bios);
					print_r($bios);
					foreach ($ids as $id) {
						$child = new Child();
						$child->stateId = $id;	
						
						
						exit();
					}
				}
				else {
					echo "SINGLE!\r\n";
					$child = new Child();
					$child->name = $matches[7][0];
					$child->stateId = $matches[8][0];
					$child->bio = $matches[9][0];
					$child->needs = $needs;
					$kids[] = $child;
				}
				$listing->kids = $kids;
				//print_r($listing);
				return $listing;
			}
			else
				throw new Exception ("Failed to parse profile in $filename");
		}
		
	}
	
	
	$map = new CCYF_Mapper('CCYF');
	
	$listing = $map->mapListing(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Listings' .
					DIRECTORY_SEPARATOR . 'CCYF' . DIRECTORY_SEPARATOR . /*'7090'*/ '015928');
					
	print_r($listing);

?>