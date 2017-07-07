<?php
	require_once('SpiderMapper.php');
	
	class AUK_Mapper extends Listing_Mapper{
		
		function mapListing($filename) {
			$profile = file_get_contents($filename);
			$listing = new Listing();
			preg_match('/href="\/_app\/common\/VideoPlayer.aspx\?vid=([^"]*)"/isU', $profile, $video);
			preg_match('/src=[\'"](\/_photos[^\'"]*)[\'"]/isU', $profile, $pics);
			array_splice($video, 1);
			array_splice($pics, 1);
			foreach ($video as $i=>$v)
				$video[$i] = urldecode($v);
			foreach ($pics as $i=>$p)
				$pics[$i] = urldecode($p);
			$listing->photos = $pics;
			$listing->video = $video;
			
			
			$kids = array();
			if (preg_match('/onclick="return ExpandCaseNum\(\'\d+\'\);"/is', $profile, $matched)) {
				$tree = str_get_html($profile);
				
				foreach ($tree->find('ul[class=stHide] li') as $l) {
					preg_match_all('/[^\w]*(\w+)[^,]*,[^\d]*(\d+)[^,]*,[^\w](\w+)/is', $l->innertext, $matches);
					$child = new Child();
					$child->name = $matches[1][0];
					$child->age = $matches[2][0];
					$child->sex = $matches[3][0];
					$kids[] = $child;
				}
				$listing->kids = $kids;
				if (preg_match_all('/State:<\/td><td>([^<]*)<.*"caseNum">([^<]*)</isU', $profile, $matches))
				{
					$listing->State = $matches[1][0];
					$listing->kidID = $matches[2][0];
				}
				else
					throw new Exception ("Failed to find State for sibling group $filename");
				$bio = '';
				foreach($tree->find('div p') as $p)
					$bio .= $p->innertext . "\r\n";
				$listing->bio = $bio;
			}
			else {
				if (preg_match_all('/name:<\/td><td>([^<]*)<.*Ages:<\/td><td>(\d+)<.*Race:<\/td><td>([^<]*)' . 
				   '<.*Gender:<\/td><td>([^<]*)<.*State:<\/td><td>([^<]*)<.*Case[^<]*<\/td>' .
				   '<td[^>]*>([^<]*)<.*Photo Updated:<\/td><td>([^<]*)<.*<div id=".*nar[^"]*"'. 
				   '[^>]*><p>(.*)<\/p><[^p]/isU', $profile, $matches))
				{
					$child = new Child();
					$child->name = $matches[1][0];
					$child->age = $matches[2][0];
					$child->race = $matches[3][0];
					$child->sex = $matches[4][0];
					$listing->State = $matches[5][0];
					$listing->kidId = $matches[6][0];
					$listing->bio = $matches[7][0];
					$listing->kids = array($child);
				}
				else
					throw new Exception("Failed to find information for single child: $filename");
			}
			return $listing;
		}
	}

?>