<?php

    /*Mapper extracts the important information from a profile by placing
     *it into a Listing class
     * 
     *  Each Site provides at the very least:
     *    >Names
     *    >Number of Children
     *    >Pictures
     *    >Link to the Child's page?
     * 
     *  The Listing class 
     * 
     * In addition to this, we need to find all other relevant information
     * and add it to the Listing
     * 
     * The next step will be to combine the information from multiple sites
     * PROBLEM: We want to schedule multiple scrapers to combine their results
     * SOLUTION: Start the day by calling the Driver with a reset call
     *            Each site invokes the Driver
     *            Once the driver has enough filled it can go to work inserting
     * 
     * NOTE: All information needs to be downloaded and saved by the Scraper
     * 
     */ 

    /*CASE 1: Single child page => fill one child
     *CASE 2: Sibling Group page => >first divide into children
     *                              what if they are jumbled together?
     *                              what if each has their own page?
     *                              
     *                              >second find Listing info 
     * 
     *                              >return Listing
     * 
     * 
     * Case1 : All group members in a single blob
     *          Attempt to separate out each child
     *          Some have structure others not so much
     * 
     * Case2 : Each child gets separate section
     *          Use oldest childs info for group
     * 
     * Case3 : Group section and individual sections
     *          Use group section to fill all child info
     * 
     * Case4 : Each child gets own dedicated page no group page
     *          Need to find a way to link children together
     * 
     * Case5 : Each child gets own page with group page
     *          Find group info first then get each kid
     * 
     */
	 
    require_once('class.SpidListing.php');
	require_once(dirname(dirname(__DIR__)) . '/public_html/php/simple_html_dom.php');


	//NOTE: This class now expects the siblings to already be combined into a single file.
	abstract class Listing_Mapper
	{
		abstract function mapListing($profile_str);     //Generates a Listing Object for each child profile on that page
        
		protected $given;		  //Fields automatically set for this website (i.e. State, Supervisor, etc.)
		protected $name;		  //Name of the directory to search for Listings
		protected $save;		  //Saves the Listings to serialized files to combine across multiple sites
        
		
        function __construct($name, $given=array(), $save_output=false) {
            $this->given = $given;
			$this->name	 = $name;
			$this->save	 = $save_output;
        }
        
        
		function profilesToListing($filenames) {
            $listings = array();
            foreach ($filenames as $filename) {
				//Open the file
				$filename = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Listings' .
							DIRECTORY_SEPARATOR . $this->name . DIRECTORY_SEPARATOR . $filename;
                if (!file_exists($filename)) {
                    throw new Exception ("Could not locate file: $filename");
				}
				$temp_listing = '';
                try {
                    $temp_listing = $this->mapListing($filename);
                } catch (Exception $e) {
                    throw new Exception ('Failed to Parse Listing: ' . $e->getMessage());	
                }
                if ($temp_listing === '' || !is_a($temp_listing, 'Listing'))
                    throw new Exception ("Failed to create Listing Object from $filename");	
                foreach ($this->given as $key=>$val) {
                    echo "Setting $key to $val\r\n";
                    $temp_listing->$key = $val;
                }
                $listings[] = $temp_listing;
            }
            if (count($listings) == 0)
                throw new Exception ('Failed to create any listings');
            if (count($listings) == 1)
                return $listings[0];
			return $listings;
		}
	}
    
    
    class REGEX_Mapper extends Listing_Mapper
    {
        private $map_listing;	//Should capture only general listing (non-child specific) information.
        private $map_child;		//Should be a repeatable capture for each child.
        
        function __construct($name, $given, $map_listing, $map_child, $save_output=false) {
            parent::__construct($name, $given, $save_output);
            if (!is_array($map_listing) || count($map_listing) == 0 || !is_a($map_listing[0] , 'REGEX_fields'))
                throw new Exception ('map_listing must be an array of REGEX_fields objects');
            if (!is_array($map_child) || count($map_child) == 0 || !is_a($map_listing[0], 'REGEX_fields'))
                throw new Exception ('map_child must be an array of REGEX_fields objects');
            $this->map_listing = $map_listing;
            $this->map_child   = $map_child;
        }
        
        function mapListing($profile_str) {
            $listing = new Listing();            
            foreach ($this->map_listing as $regex_obj) {
                $matches = array();
                if (!preg_match_all('/' . $regex_obj->regex_str . '/isU', $profile_str, $matches) || !array_key_exists('1', $matches))
                    throw new Exception ("Regex {$regex_obj->regex_str} failed in map_listing");
                foreach($matches as $key=>$match) {
                    if ($key !== 0) {
                        $field = $regex_obj->fields[$key-1];
                        $listing->$field = $match;
                    }
                }
            }
            
            $kids = array();
            foreach($this->map_child as $regex_obj) {
                $matches = array();
                if (!preg_match_all('/' . $regex_obj->regex_str . '/isU', $profile_str, $matches) || !array_key_exists('1', $matches))
                    throw new Exception ('Regex: ' . $regex_obj->regex_str . ' failed in map_child');
                $id;
                foreach ($matches[1] as $key=>$match) {
                    if ($key % count($regex_obj->fields) == 0) {
                        $id = $match;
                        $kids[$id] = new Child();
                    }
                    $field = $fields[$key % count($fields)];
                    $kids[$id]->$field = $match;
                }
            }
            
            $listing->kids = $kids;
            return $listing;
        }
        
		
		/*
        function combineSiblings ($listings) {
            $combined = new Listing();
            $all_kids = array();
            foreach ($listings as $listing) {
                $kids = array();
                foreach ($listing as $key => $value) {
                    if ($key == 'kids') {
                        $kids[] = $value;
                    }
                    else if (!isset($combined->$key))
                        $combined->$key = $value;
                    else if (is_a($value, 'Child'))
                        $kids[$key] = $value;
                    else if (is_string($value))
                        if (stripos($value, $combined->$key) !== 0)
                            throw new Exception('Combined string values for ' . $key  .
                                ' did not match, ' . $combined->key .  ' vs ' . $value);
                    else if ($value !== $combined->$key)
                        throw new Exception('Combined string values for ' . $key  .
                            ' did not match, ' . $combined->key .  ' vs ' . $value);
                }
                $all_kids[] = $kids;
            }
            $combined->kids = combineChildren($all_kids);
            return $combined;
        }
		*/
        
		/*
        function combineChildren($kid_arrays) {
            $return_kids = array();
            foreach ($kid_arrays as $kid_array) {
                foreach ($kid_array as $key=>$kid) {
                    if (!array_key_exists($key, $return_kids))
                        $return_kids[] = $kid;
                    else {
                        foreach ($kid as $prop=>$val) {
                            if (!property_exists($kid, $prop))
                                $return_kids[$key]->$prop = $val;
                            else if ($kid->$prop !== $val)
                                echo "FOUND A DISCREPENCY\r\n";
                        }
                    }
                }
            }
            return $return_kids;
        }
		*/
    }


	class REGEX_fields {
		public $regex_str;
		public $fields;
		
		function __construct($regex_str, $fields) {
			if (!is_string($regex_str) || $regex_str == '')
				throw new Exception('$regex_str must be a non-empty string!');
			if (!is_array($fields) || count($fields) == 0)
				throw new Exception('$fields must be an array of string fieldnames');
			$this->regex_str = $regex_str;
			$this->fields = $fields;
		}
	}
	
	
	class Serialized_Mapper extends Listing_Mapper {
		private $child_atr;
		private $listing_atr;
		private $siblings;
		private $listings;

		function __construct($name, $given, $child_atr, $listing_atr) {
			parent::__construct($name, $given);
			$this->child_atr = $child_atr;
			$this->listing_atr = $listing_atr;
			$this->siblings = array();
		}

		//Currently only used for MARE, so we unserialize twice
		function mapListing($filename) {
			$children = array();
			$handle = fopen($filename, 'r');
			while (($line = fgets($handle)) !== false) {
				if (!isset($line) || trim($line) === '' || trim($line) === '\r\n')
					continue;
				$profile = @unserialize($line);
				if ($profile === NULL)
					continue;
				//This print statement helps look at what is in the serialized object
				//Use mapChild to create a new Child Object
				$child;
				try {
					$child = $this->mapChild($profile);
				} catch (Exception $e) {
					throw $e;
				}
				$children[] = $child;
			}
			
			$listing = new Listing();
			//Sort siblings by age, and use the eldest for Listing information
			$max_age = 0;
			$kids = array();
			foreach ($children as $i => $child) {
				$kids[$child->age] = $child;
				if ($child->age > $max_age) {
					$max_age = $child->age;
					//If this is the oldest child 
					foreach ($child as $atr=>$val) {
						if (array_key_exists($atr, $this->listing_atr)) {
							$listing_atr = $this->listing_atr[$atr];
							$listing->$listing_atr = $val;
						}
					}	
				}
			}
			$listing->kids = array_values($kids);
			print_r($listing);
			exit();
			return $listing;
		}
		
        function mapChild($profile) {
			$child = new Child();
			/*
			foreach ($profile as $atr=>$val) {
				if (array_key_exists($atr, $this->child_atr)) {
					$ch_atr = $this->child_atr[$atr];
					$child->$ch_atr = $val;
				}
			}
			*/
			foreach ($this->child_atr as $key=>$atr) {
				if (array_key_exists($key, $profile))
					$child->$atr = $profile[$key];
				else
					throw new Exception("Failed to get key $key from profile");
			}
			return $child;
		}
	}
?>