<?php

	require_once('PagingAgents.php');
	require_once(dirname(dirname(__DIR__)) . '/public_html/php/simple_html_dom.php');

	abstract class SearchAgent {
		
		protected $name;
		public $paging_agent;
		protected $no_dedicated_listing;
		//protected $no_sibling_pages;
			
		abstract protected function findNextPage($html);
		abstract protected function findListings($html);
		abstract protected function findProfile($html, $from_link);
		
		
		function __construct($name, $paging_agent, $no_dedicated_listing = false)
		{
			if (!is_a($paging_agent, 'PageAgent'))
				throw new Exception ("Page Agent must be a PageAgent decendent class");
			$this->name 				= $name;
			$this->paging_agent 		= $paging_agent;
			$this->no_listing_pages		= $no_dedicated_listing; 
			//$this->no_sibling_pages		= $no_sibling_pages;
		}
		
		
		public function downloadListings($html, $directory) {
			$count = 0;
			if ($this->no_dedicated_listing) {
				$profile_htmls = array();
				echo "Looking for listings on mainpage\r\n";
				try {
					$profile_htmls = $this->findProfile($html, false);
				} catch (Exception $e) {
					if (is_a($this->paging_agent, 'ASPX_PageAgent')) {
						echo "Returning false\r\n";
						return false;
					}
				}
				foreach ($profile_htmls as $id=>$p) {
					if (!file_exists($directory . DIRECTORY_SEPARATOR . $id)) {
						file_put_contents($directory . DIRECTORY_SEPARATOR . $id, $p);
						$count++;
					}
				}
			}
			else {
				echo "Looking for listings on dedicated page\r\n";
				$html_listings = array();
				try {
					$html_listings = $this->findListings($html);
				} catch (Exception $e) {
					if (is_a($this->paging_agent, 'ASPX_PageAgent')) {
						return false;
					}
					echo $e->getMessage() . "\r\n";
				}
				echo "Found " . count($html_listings) . " listings\r\n";
				foreach ($html_listings as $listing) {
					try {
						$profiles = $this->findProfile($listing, true);
					} catch (Exception $e) {
						echo "$listing: {$e->getMessage()}\r\n";
						continue;
					}
					foreach ($profiles as $id=>$prof) {
						$file_name = $directory . DIRECTORY_SEPARATOR . $id;
						if (file_exists($file_name)) {
							echo "Found Duplicate $file_name\r\n";
						}
						else if ($handle = fopen($file_name, 'x')) {
							if (fwrite($handle, $prof))
								$count++;
							fclose($handle);
						}
						else
							throw new Exception ("Failed to open file $file_name");
						
					}
				}
			}
			return $count;
		}
	}


	class DOM_SearchAgent extends SearchAgent {
		
		private $find_listing;
		private $find_stateId;
		private $find_listing_link;
		private $listing_format;
		private $extract_id;
		private $id_from_link;
		
		private $find_profile;
		
		private $find_next;
		public $next_page_format;
        private $iterate_children;

	
		function __construct($name, $paging_agent, $find_listing, $find_listing_link = '',
							 $find_stateId = '',  $extractId = '', $find_profile='',
							 $find_next='', $listing_format = '', $next_page_format = '',
                             $iterate_children = false)
		{
			parent::__construct($name, $paging_agent);
			
			$this->find_listing		 = $find_listing;
			$this->find_stateId		 = $find_stateId;
			$this->find_listing_link = $find_listing_link;
			$this->listing_format 	 = $listing_format;
			$this->extract_id		 = $extractId;
			$this->find_profile 	 = $find_profile;
			$this->find_next 		 = $find_next;
			$this->next_page_format  = $next_page_format;
            $this->iterate_children  = $iterate_children;
			
			$this->id_from_link 	 = false;
			if ($find_stateId == '')
				$this->id_from_link  = true;
			
			if ($find_listing_link == '') {
				$this->no_dedicated_listing = true;
			}
		}
		
		/* Locates and returns an array of $id => $profile_string for each child profile
		 * 
		 * @Returns
		 *		Array (String $id => String $profile_html)	An array of child pages
		 *													  with their assoc. Id's
		 *
		 * @Params
		 *		String	$listing	Either the link to a childs profile or the
		 *							  html from their profile (for sites without
		 *							  dedicated pages for each child.
		 *		Boolean	$from_link	Is the passed profile a link or the page html 
		 *
		 */
		public function findProfile($listing, $from_link = false) {
			$id='';
			//If the profile is a link, fetch their page
			if ($from_link) {
				$profile_html = $this->paging_agent->PageRequest($listing);
				if ($this->id_from_link) {
					//$id = $link[0]->href;
					$id = $listing;
				}
			}
			else {
				$profile_html = $listing;
			}
			if ($profile_html == '')
				throw new Exception ("Profile HTML was empty");
			
			$tree = str_get_html($profile_html);
			
			if ($id == '') {	
				$id = $tree->find($this->find_stateId);
				if (!isset($id) || count($id) == 0)
					throw new Exception ('Failed to find child Id with find_stateId');
				$id = $id[0]->innertext;
			}
			
			if ($this->extract_id != '') {
				$matches = array();
				if (preg_match('/' . $this->extract_id . '/is', $id, $matches)) {
					$id = $matches[1];
				}
				else
					throw new Exception ("Failed to extract child State Id from $id");
			}
			
			
			$profiles = $tree->find($this->find_profile);
			if (count($profiles) == 0){
				throw new Exception("Failed to extract profile from page");
			}
			if (count($profiles) !== 1)
				throw new Exception("Found multiple profiles in listing");
			/*
			$temp = array();
			foreach ($profiles as $p) {
				$id = '';
				$heuristic = $this->find_stateId;
				$subject = $p;
				if ($this->id_from_link) {
					$heuristic = $this->extract_id;
					$subject = $listing;	
				}
				if (preg_match('/' . $heuristic . '/isU', $subject, $id) && array_key_exists(1, $id)) {
					$id = $id[1];
				}
				else {
					echo "SUBJECT: $subject\r\nHEURISTIC: $heuristic\r\n";
					exit();
					throw new Exception ("Failed to find state ID");
				}
				$temp[$id] = $p;
			}
			$profiles = $temp;
			*/
			$profiles = array($id=>$profiles[0]->innertext);
			//print_r($profiles);
			//exit();
			return $profiles;
		}

		
		public function findNextPage($html) {
			$tree = str_get_html($html);
			if ($link = $tree->find($this->find_next)) {
				if (is_array($link)) {
					$link = $link[0];
					echo "Getting first of array\r\n";
				}
				
				if (!is_array($link) && isset($link->href)) {
					if ($this->next_page_format == '') {
						if (isset($link->href))
							return $link->href;
					}
					else {
						return sprintf($this->next_page_format, $link->href);
					}
				}
				else {
					echo is_array($link) ? "Array\r\n" : "not an Array\r\n";
					print_r($link);
					exit();	
				}
			}
			throw new Exception("Failed to find nextpage");
		}
			
		
		public function findListings($html) {
			if ($html == '') {
				throw new Exception ("findListing recieved a blank page");	
			}
			if ($this->find_listing_link == '')
				return $this->find_profile($html);
			$tree = str_get_html($html);
			$links = array();
            $listings = $tree->find($this->find_listing);
            if ($this->iterate_children)
                $listings = $listings[0]->children();
            foreach ($listings as $listing) {
                
                //Find the link to the child's profile
                $link = array($listing);
				if (strcasecmp($this->find_listing_link, 'none') !== 0)
                    $link = $listing->find($this->find_listing_link);	
				$id = '';
				if (isset($link) && count($link) > 0) {
					/*
					if ($this->id_from_link) {
						$id = $link[0]->href;
                    }
					else {
						$id = $listing->find($this->find_stateId);
						if (!isset($id) || count($id) == 0)
							throw new Exception ('Failed to find child Id with find_stateId');
						$id = $id[0]->innertext;
					}
					if ($id == '') {
                        echo $listing . "\r\n";
						continue;
                    }
					*/
					$link = $link[0]->href;
					/*
					if ($this->extract_id != '') {
						$matches = array();
						if (preg_match('/' . $this->extract_id . '/is', $id, $matches)) {
							$id = $matches[1];
						}
						else
							throw new Exception ("Failed to extract child State Id from $id");
					}
					*/
					if ($link == '')
						throw new Exception ("Failed to find link to child's page");
					if ($this->listing_format == '')
						$links[/*$id*/] = $link;
					else
						$links[/*$id*/] = sprintf($this->listing_format, $link);
				}
				else {
                    print_r($links);
                    echo $listing . "\r\n";
					throw new Exception ('Failed to find link in child\'s listing');
				}
			}
			return $links;
		}
	}
	
	
	class REGEX_SearchAgent extends SearchAgent {
		private $find_listing;
		private $id_first;
		private $listing_format;
		
		private $find_next;
		private $next_page_format;
		
		private $find_profile;
		
		
		function __construct($name, $paging_agent, $find_listing, $listing_format, $find_next,
							 $next_page_format, $find_profile, $id_first = false)
		{
			parent::__construct($name, $paging_agent);
									
			$this->find_listing 	= $find_listing;
			$this->id_first			= $id_first;
			$this->listing_format	= $listing_format;
			
			$this->find_next		= $find_next;
			$this->next_page_format = $next_page_format;
			
			$this->find_profile		= $find_profile;
			
			if ($find_listing == '')
				$this->no_dedicated_listing = true;
		}
		
		
		public function findProfile($listing, $from_link=true) {
			$profile_html = $listing;
			if ($from_link) {
				$profile_html = $this->paging_agent->PageRequest($listing);
			}
			$profiles = array();
			$matches = array();
			if (preg_match_all('/' . $this->find_profile . '/iUs', $profile_html, $matches)) {
				if (array_key_exists('2', $matches)) {
					foreach ($matches[1] as $key=>$m) {
						$profiles[$matches[2][$key]] = $m;
					}
				}
				elseif (array_key_exists('1', $matches)) {
					foreach ($matches[1] as $m)
						if (stripos($listing, 'id=') !== false) {
							$profiles[substr($listing, stripos($listing, 'id=') + 3)] = $m;
						}
						else
							throw new Exception ("Failed to get an id for this listing");
				}
			}
			else {
				throw new Exception ("Failed to find a profile on $listing");
			}
			return $profiles;
		}
		
		
		public function findNextPage($html) {
			$matches = array();
			if (preg_match('/' . $this->find_next . '/isU', $html, $matches)) {
				if (array_key_exists('1', $matches)) {
					if ($this->next_page_format == '')
						return $matches[1][0];
					else {
						return sprintf($this->next_page_format, $matches[1][0]); 	
					}
				}
				return '';
			}
			throw new Exception ("REGEX Scraper failed to find a link for the next page");
		}
		
		
		public function findListings($html) {
			if ($html == '')
				throw new Exception ('findListing recieved a blank page');
			$links = array();
			$matches = array();
			if (preg_match_all('/' . $this->find_listing . '/isU', $html, $matches)
				&& array_key_exists('2', $matches))
			{
				if ($this->listing_format == '') {
					foreach ($matches[1] as $key=>$link){
						if ($this->id_first)
							$links[$matches[2][$key]] = $link;
						else
							$links[$link] = $matches[2][$key];
					}
				}
				else {
					foreach ($matches[1] as $key=>$local_id) {
						if ($this->id_first)
							$links[$matches[2][$key]] = sprintf($this->listing_format, $local_id);
						else
							$links[$local_id] = sprintf($this->listing_format,$matches[2][$key]);
					}
				}
			}
			else {
				echo "Found no listings\r\n";
				file_put_contents('error_html', $html);
			}
			return $links;
		}
	}
	
	class JSON_SearchAgent extends SearchAgent {
		
		private $id_field;
		
		function __construct($name, $paging_agent, $id_field)
		{
			parent::__construct($name, $paging_agent, true);
			$this->id_field = $id_field;
			$this->no_dedicated_listing = true;
		}
		
		function findListings($html) {
			return false;	
		}
		
		function findNextPage($html) {
			return false;	
		}
		
		function findProfile($html, $from_link = false) {
			$children = array();
			try {
				$children = $this->read_JSON($html);
			} catch (Exception $e) {
				throw new Exception('Failed to parse JSON string: ' . $e->getMessage());
			}
			if (count($children) == 0)
				throw new Exception('Failed to parse JSON string: $children was empty');
			foreach ($children as $id=>$c) {
				$children[$id] = serialize($c);	
			}
			return $children;
			/*
			$profiles = array();
			foreach ($children as $c) {
				$id_str = $this->id_field;
				if (!array_key_exists($id_str, $c))
					throw new Exception ('Failed to find Id attribute in child json');
				$id = $c->$id_str;
				$profiles[$id] = serialize($c);
			}
			return $profiles;
			*/
		}
		
		function read_JSON($json_str) {
			//if (substr($json_str, 0, 1) == '"' && substr($json_str, -1, 1) == '"')
			$json_str = substr($json_str, 1, -1);
			if (mb_check_encoding($json_str, "UTF-8")) {
				$json_str = str_replace('\"', '"', str_replace('\\\\', '\\', $json_str));
				$json_str = str_replace('\"', '"', str_replace('\\\\', '\\', $json_str));
				$arr = json_decode($json_str, true);
				$children = array();
				foreach ($arr as $a) {
					if (is_array($a)) {
						foreach ($a as $child) {
							$children[$child['ListingCode']] = $child;
						}
					}
				}
				//print_r($children);
				//exit();
				return $children;
			}
			else 
				throw new Exception("JSON string format must be UTF-8");
		}	
	}
	
?>
