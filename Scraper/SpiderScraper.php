<?php
	/* General Spider Scraper
	 *   1. Scans a website for updates to a child's listing and saves
     *      to the SpiderListings Directory
	 *   2. Checks the sha1 sum of each child's profile page html versus
     *      the last scrape
	 *	 3. Saves the filenames of all removed children in the
     *      SpiderListings Directory
	 *   4. Returns the filename of each child who has been added or
     *      updated
	 */
	 define('SPIDER_LISTING_DIRECTORY', dirname(__DIR__) .
            DIRECTORY_SEPARATOR . 'Listings' . DIRECTORY_SEPARATOR);
	 
	 require_once('SearchAgents.php');
	 require_once('URLGenerators.php');
	 
	 /* DEBUGGING:
	  *   DEBUG (0-3)
	  *	    0: None
	  *		1: Display Results of calls within ScrapeSite()
	  *		2: Display Results of pattern matching
	  *
	  *	  DEBUG_CHILD_LIMIT
	  *     Limits how many children will be retrieved
	  *
	  *   CLEAN_DIRECTORY
	  *     0: Do Nothing
	  *     1: Delete Old Directory before beginning 
	 */
	 define('DEBUG_CHILD_LIMIT', 25);
	 define('DEBUG', 3);
	 define('CLEAN_DIRECTORY', 1);
	 

	/* Scraper class 
     *      > Saves files from the last run and creates fresh folder
     *          in Listings/$name subdirectory
     *      > Sends calls to SearchAgent, URLGenerator, and PagingAgent.
     *      > Compares files from current and last run to detect differences
	 *
	 * @properties
	 *		String       $name				Determines the name of the
     *                                        folder for this spiders files.
     *      SearchAgent  $search_agent      Handles the search methods.
     *                                        for finding links and child
     *                                        profiles.
     *      URLGenerator $next_generator    Generates the url for getting
     *                                        the next page of listings.
     *      Boolean      $single_page       If true, the scrape will stop
     *                                        on the first page of listings.
     *      Integer      $page_count        Holds the current page number
     *                                        when a URLGenerator is not
     *                                        necessary	
     */
	class Scraper {
		protected $name;
		protected $single_page;
		protected $search_agent;
		protected $next_generator;
		protected $page_count;
		
		function __construct($website_abbreviation, $search_agent,
                    $nextpage_generator='', $single_page = false)
		{							
			if ($website_abbreviation == '')
				throw new Exception('Scraper requires the name of the' .
                                    ' website to initialize');
			if (!is_a($search_agent, 'SearchAgent'))
				throw new Exception('Search Agent must be a child class' .
                                    ' of SearchAgent');
			if (is_a($nextpage_generator, 'URLGenerator'))
				$this->next_generator = $nextpage_generator;
			
			$this->name					= $website_abbreviation;
			$this->search_agent			= $search_agent;
			$this->single_page			= $single_page;
			$this->page_count			= 2;
		}


		/* Scrapes information from listing pages and saves the child's information
		 *   to the filesystem
		 *
		 * Returns an array of Child State ID's corresponding to the filenames in this state's
		 *   SpiderListings Directory for children who have been updated or added since last run.
		 */
		public function ScrapeSite() {
			$this->my_log('Starting Scrape', 1);
			
			//Append _last_run to end of directory from last run
			$directory = SPIDER_LISTING_DIRECTORY . $this->name;
			try {
                if (is_dir($directory)) {
                    if (CLEAN_DIRECTORY == 1)
                        delete_old_directory($directory);
                    else
                        rename($directory, $directory . '_old');
                        //move_files_to_old($directory);
                }
            } catch (Exception $e) {
                $this->my_log('Failed to move filse from last run: ' . 
                                $e->getMessage(), 1);
                exit();
            }
			//Create new directory for this run
			mkdir($directory);
			
            $starting_pages = $this->search_agent->paging_agent->first_page;
            
            if (!is_array($starting_pages)) {
                $starting_pages = array($starting_pages);
            }
            $listing_count = 0;
            $postfields = array();
            $html = '';
            foreach ($starting_pages as $start_page) {
                $current_page = $start_page;
                if (is_a($this->search_agent->paging_agent, 'ASPX_PageAgent'))
                    $postfields = $this->search_agent->paging_agent->postfields;
                //Find the child listings on this page
                do {
                    $this->my_log("Getting Page $current_page", 1);
                    $found_new = false;
                    $html = $this->search_agent->paging_agent->pageRequest($current_page, $postfields);
                    $num_found = $this->search_agent->downloadListings($html, $directory);
                    
                    //If the ASPX Agent fails it might because we need a new cookie
                    if (is_a($this->search_agent->paging_agent, 'ASPX_PageAgent') && $num_found === false) {
                        $this->search_agent->paging_agent->getFirstPage();
                        $html = $this->search_agent->paging_agent->pageRequest($current_page, $postfields);
                        $num_found = $this->search_agent->downloadListings($html, $directory);
                    }
                    
                    if ($num_found !== false && $num_found > 0) {
                        $found_new = true;	
                        $listing_count += $num_found;
                    }
                    else {
                        $this->my_log("Found no listings on page $current_page", 1);
                        break;
                    }
                    //All listings are on current page
                    if ($this->single_page) {
                        $this->my_log("Stopping On First Page", 1);
                        break;
                    }
                    
                    if (!isset($this->next_generator)) {
                        $current_page = $this->search_agent->findNextPage($html);
                    }
                    //Generate a new link to the next page
                    else {
                        $genPage = $this->next_generator->getNextPage();
                        if (is_array($genPage) && property_exists($this->search_agent->paging_agent, 'postfields')) {
                            if (is_a($this->search_agent->paging_agent, 'ASPX_PageAgent')) {
                                $this->search_agent->paging_agent->updatePostfields($html);
                            }
                            $postfields = $this->search_agent->paging_agent->postfields;
                            foreach ($genPage as $key=>$val) {
                                if ($key == 'url')
                                    $current_page = $val;
                                else
                                    $postfields[$key] = $val;
                            }
                        }
                        else {
                            $current_page = $genPage;
                        }
                    }
                    
                    $this->my_log("Found next page : $current_page", 1);
                    
                } while ($found_new && $listing_count < DEBUG_CHILD_LIMIT);
            }
            $this->my_log("Scrape Completed, found " . $listing_count, 1);
			return $this->CompareListings();
		}
		
		
		/* Compare the data files for children and check for adds/removes/updates
		 *
		 * Runs after the main scraper function has saved the children's listings to the filesystem.
		 *   Compares the files from the Spider's Listing Directory to the Directory from last run,
		 *   by using a sha1 hash on the contents to determine if a child has been updated or added
		 *   since last run.
		 * 
		 * Returns an array of Child State ID's corresponding to the filenames in this state's
		 *   SpiderListings Directory for children who have been updated or added since last run.
		 */
		public function CompareListings() {
			$updates = array();
			
			$new_directory  = SPIDER_LISTING_DIRECTORY . $this->name;
			$old_directory  = $new_directory . '_last_run';
			$new_hashes_loc = SPIDER_LISTING_DIRECTORY . $this->name .
                              DIRECTORY_SEPARATOR . $this->name . '_hashes';
			$old_hashes_loc = $new_hashes_loc . '_old';
			$new_hashes = array();
			$old_hashes = array();
			
			if (!file_exists($new_directory))
				throw new Exception("CompareListings failed to find the directory $new_directory");
			
			foreach(scandir($new_directory) as $f) {
				//Ignore the dots, sibling combination files, and old hash files
				if ($f != '.' && $f != '..' && stripos($f, 'sibgrp_') === false && stripos($f, $this->name . '_') === false) {
					$new_hashes[$f] = sha1(file_get_contents($new_directory . DIRECTORY_SEPARATOR . $f));	
				}
			}
			
			//Get file hashes from last run
			if (file_exists($old_directory)) {
				//Generate old hashes
				if (!file_exists($new_hashes_loc)) {
					foreach (scandir($old_directory) as $f) {
						if ($f != '.' && $f != '..')
							$old_hashes[$f] = sha1(file_get_contents($f));
					}
				}
				else
					$old_hashes = unserialize(file_get_contents($new_hashes_loc));
					
				if (!file_put_contents($old_hashes_loc, serialize($old_hashes)))
					throw new Exception ("CompareListings failed to record the new hashes");
			}
			
			if (!file_put_contents($new_hashes_loc, serialize($new_hashes)))
				throw new Exception ("CompareListings failed to record the new hashes");
			
			//Find Deleted files
			if (!file_put_contents($new_directory . DIRECTORY_SEPARATOR .
                                   $this->name . '_deletes',
                                   serialize(array_diff_key($old_hashes, $new_hashes))))
				throw new Exception ("CompareListings failed to record deleted pages");
			
			//Find Added or changed files
			foreach ($new_hashes as $new_file=>$new_hash) {
				//Check for updates
				if (array_key_exists($new_file, $old_hashes)) {
					if ($old_hashes[$new_file] != $new_hash)
						$updates[] = $new_file;
				}
				//New child
				else
					$updates[] = $new_file;
			}		
			return $updates;
		}
		
		/* Error handling function */
		public function my_log($error_string, $debug_level) {
			if (DEBUG >= $debug_level) {
				//error_log($error_string . "\r\n", 3, 'error_log_' . $this->name);	
				echo $error_string . "\r\n";
			}
		}
		
		
	}
	
	
	
	
	function rename_win($oldfile, $newfile) {
		if (!rename($oldfile,$newfile)) {
			if (copy ($oldfile,$newfile)) {
				unlink($oldfile);
				return TRUE;
			}
			return FALSE;
		}
		return TRUE;
	}
    
    function move_files_to_old($dir) {
        $old = $dir . '_old';
        try {
            if (file_exists($old)) {
                delete_old_directory($old);
            }
            mkdir($old);
            copy($dir, $old);
        } catch (Exception $e) {
            throw $e;
        }
        delete_old_directory($dir);
    }
    
    function delete_old_directory($dir) {
        try {
            $iter = new RecursiveDirectoryIterator($dir,
                            RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($iter,
                            RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file) {
                if ($file->isDir()) {
                    delete_old_directory($file->getRealPath());
                }
                else {
                    unlink($file->getRealPath());
                }
            }
            rmdir($dir);
        } catch (Exception $e) {
            throw $e;
        }
    }
?>
