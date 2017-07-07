<?php
	/*Handles:
	 *  Page Requests:
	 *
	 *
	 */
	class PageAgent {
		public $first_page;
		protected $cookie;

		function __construct ($name, $first_page) {
			$this->cookie = tempnam('/tmp', 'CURLCOOKIE_' . $name);
			$this->first_page = $first_page;
		}
		
		
		function cleanResult($result) {
			//UTF-8 is easier to deal with
			$result = html_entity_decode($result, NULL,"UTF-8");
			//Remove nbsp;
			$result = str_replace(chr(0xC2) . chr(0xA0), '', $result);
			return $result;
		}
		
		
		//FIXME: Might check for HTTP_CODE == 302
		//FIXME: Probably want to use a forloop like in spider1a.php
		function pageRequest($url, $postfields = array()) {
			echo "pageRequest accessing $url\r\n";
			
			if (count($postfields)>0) {
				$temp = $postfields;
				unset($temp['__VIEWSTATE']);
				print_r($temp);
				unset($temp);
			}
			
			$result = '';
			try {
				$agent = "Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 " .
                    "(KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36";
				$channel = curl_init();
				curl_setopt($channel, CURLOPT_URL, $url);
				curl_setopt($channel, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($channel, CURLOPT_USERAGENT, $agent);
				curl_setopt($channel, CURLOPT_RETURNTRANSFER, true);
				//curl_setopt($channel, CURLOPT_MAXREDIRS, 5);
				//curl_setopt($channel, CURLOPT_CONNECTTIMEOUT, 30); 
				//curl_setopt($channel, CURLOPT_TIMEOUT, 30);
				//curl_setopt($channel, CURLOPT_VERBOSE, 1);
				curl_setopt($channel, CURLOPT_COOKIEJAR, $this->cookie);				
				curl_setopt($channel, CURLOPT_COOKIEFILE, $this->cookie);
				curl_setopt($channel, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($channel, CURLOPT_REFERER, $url);
				if (count($postfields) > 0) {
					curl_setopt($channel, CURLOPT_POST, true);
					curl_setopt($channel, CURLOPT_POSTFIELDS, $postfields);
				}
				
				$result = curl_exec($channel);
				curl_close($channel);
			} catch (Exception $e) {
				throw new Exception ("cURL error for page : " . $url .
                                        " : " . $e->getMessage());
			}
			if ($result == '')
				throw new Exception('Failed to get webpage: ' . $url);
			return $this->cleanResult($result);
		}
	}



	/*  Visits apsx search page first to get a cookie
	 *  Stores Postfields required for paging through site
	 *
	 *  IMTNGA: Requires a postback for each page
	 *  AUK:	Does not require a post for paging
	 *
	 */
		
	class ASPX_PageAgent extends PageAgent {
		public    $postfields;
		protected $find_postfields;
		protected $search_page; 	// Search Page initializes the aspx
                                    //  cookie and gives us fields
									// Stored in case Session Expires
                                    //  and getFirstPage needs to be recalled
		
		function __construct ($name, $first_page, $search_page, $postfields,
                              $find_postfields, $post_for_nextpage = true)
        {
			parent::__construct($name, $first_page);
			$this->search_page = $search_page;
			$this->postfields = $postfields;
			$this->find_postfields = $find_postfields;
			$this->getFirstPage();
			if (!$post_for_nextpage)
				$this->postfields = array();
		}
		
		//Visits the search page to create a new cookie for the site
		//Calls updatePostfields
		//
		public function getFirstPage () {
			$url = $this->search_page;
			$result = '';
			try {
				//Get Search Page
				$agent = "Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36";
				$channel = curl_init();
				curl_setopt($channel, CURLOPT_URL, $url);
				curl_setopt($channel, CURLOPT_USERAGENT, $agent);
				curl_setopt($channel, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($channel, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($channel, CURLOPT_MAXREDIRS, 5);
				curl_setopt($channel, CURLOPT_CONNECTTIMEOUT, 30); 
				curl_setopt($channel, CURLOPT_TIMEOUT, 30);
				curl_setopt($channel, CURLOPT_COOKIESESSION, true);
				curl_setopt($channel, CURLOPT_COOKIEJAR, $this->cookie);
				$result1 = curl_exec($channel);
				curl_close($channel);

				$this->updatePostfields($result1);
				
                //Visit First page to generate our paging cookie
				$channel = curl_init();				
				curl_setopt($channel, CURLOPT_URL, $url);
				curl_setopt($channel, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($channel, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($channel, CURLOPT_COOKIEJAR, $this->cookie);
				curl_setopt($channel, CURLOPT_COOKIEFILE, $this->cookie);
				curl_setopt($channel, CURLOPT_HEADER, FALSE);
				curl_setopt($channel, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($channel, CURLOPT_REFERER, $url);
				curl_setopt($channel, CURLOPT_USERAGENT, $agent);
				curl_setopt($channel, CURLOPT_POST, true);
				curl_setopt($channel, CURLOPT_POSTFIELDS, $this->postfields);
				$result = curl_exec($channel);
					
			} catch (Exception $e) {
				throw new Exception ("cURL error for page : " . $url . " : " . $e->getMessage());
			}
			if ($result == '')
				throw new Exception('Failed to get webpage: ' . $url);
		}
		
		
		public function updatePostfields($html) {
			foreach ($this->find_postfields as $find) {
				if (preg_match('/<input type="hidden" name="' . $find . '" id="' . $find . '" value="([^"]*)" \/>/',
				$html, $match))
					$this->postfields[$find] = $match[1];
			}
		}
	}
	
	/*
	class API_PageAgent extends PageAgent {
		
		
	}
	*/

?>
