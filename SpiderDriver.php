<?php

	/* Runs the spider for a single State's website.
	 * USAGE: php SpiderDriver.php {Website Abbreviation}
	 *
	 *
	 */
     
     
     /*TODO:
      *     Create a file to track which scrapes have successfully completed 
      *     If argv[x] == clean then delete the old tracker file
      *     Mapper should run for each site and save the Listings in the $name directory
	  *		When the right comibination of mappers have run, run the combiner
	  *		  for those sites, go through the sites and combine the listings for each
	  *		Create the Salesforce Adder Class
	  *		Create the Reporter 
      * 
      */ 
		
	require_once('Scraper/SpiderScraper.php');
	require_once('Mapper/SpiderMapper.php');
	require_once('Mapper/Preprocess_Sibling_Groups.php');

	if (!isset($argv[1]) || $argv[1] == '') {
		throw new Exception("USAGE: php SpiderDriver.php 'StateSiteName'");	
	}
	$scraper;
	switch (strtolower($argv[1])) {
		// The Ohio Adoption Listing Website
		// Generic DOM based Scraper	
		case "oapl":
			require_once('Mapper/OAPL_Mapper.php');
			//Step 1: Create Page Agent to handle cURL Requests of other agents
			$name = 'OAPL';
			$firstpage = 'http://adoptionphotolistingohio.org/browse.php?page=1';
			$pager = new PageAgent($name, $firstpage);
			
			//Step 2: Create Search Agent to find a link to the child's page and their profile information
			$find_listing = 'div.list_div';
			$find_listing_link = 'div.results_link a';
			$find_stateId = 'p';
			$extract_stateId = '(?:Child|Group) ID:\s*<\/span>\s*([CG]\d+)';
			$find_profile = 'div[id=profile_div]';
			$find_next = 'div.results_nav a';
			$listing_format = 'http://adoptionphotolistingohio.org/%s';
			$nextpage_format = 'http://adoptionphotolistingohio.org/%s';
			$searcher = new DOM_SearchAgent($name, $pager, $find_listing, $find_listing_link, $find_stateId,
											$extract_stateId, $find_profile, $find_next, $listing_format, $nextpage_format);
			
			//Step 3: Create Scraper to run the agents and download the scraped profiles
			$scraper = new Scraper($name, $searcher, '', false);
			
			$updated_listings = $scraper->ScrapeSite();
			
			$mapper = new OAPL_Mapper($name, array('state'=>'Ohio'));
			
			$maps = $mapper->profilesToListing($updated_listings);
			
			print_r($maps);
			
			exit();
			
			
			break;
		
		//Coalition for Children, Youth, and Families
		//Single Listing Page DOM Scraper
		case "ccyf":
			require_once('Mapper/CCYF_Mapper.php');
			$name = "CCYF";
			$firstpage = 'http://kidsdb.ccyfstaging.org/';
			$pager = new PageAgent($name, $firstpage);
			
			$find_listing = 'div[id=KidListing]';
			$find_listing_link = 'div[id=kidSpecs] h3 a';
			$extract_stateId = 'kidNum=(\d+)';
			$find_profile = 'form';
			$listing_format = 'http://kidsdb.ccyfstaging.org/%s';
			$searcher = new DOM_SearchAgent($name, $pager, $find_listing, $find_listing_link, '',
											$extract_stateId, $find_profile, '', $listing_format, '');
			
			$scraper = new Scraper($name, $searcher, '', true);
			
			$updated_listings = $scraper->ScrapeSite();
			
			$mapper = new CCYF_Mapper($name);
			
			$maps = $mapper->profilesToListing($updated_listings);
			
			print_r($maps);
			
			exit();
			
			
			
			break;
			
		//Partnership for Strong Families
		//Single Listing Page Regex Scraper
		case "pfsf":
			$name = 'PFSF';
			$firstpage = 'http://www.pfsf.org/partner-families/become-an-adoptive-family/heart-gallery/';
			$pager = new PageAgent($name, $firstpage);
			
			//NOTE: This site is loosely structured so this regular expression is likely to break 
			$find_profile = '(<img.*class.*src="[^"]*".*(?:My|Our)\s*Adoption\s*ID\s*is\s*(\d+?))';
			
			$searcher = new REGEX_SearchAgent($name, $pager, '', '', '', '', $find_profile, false);
			
			$scraper = new Scraper($name, $searcher, '', true);
			break;
		
		//Lakeview Center Baptist Health Care: Heart Gallery Project (Northwest Florida's) 
		//NOTE: This spider is able to find children who are not actually featured in the carousel
		case "lvc":
			$name = 'LVC';
			$firstpage = 'http://elakeviewcenter.org/FamiliesFirstNetwork/AdoptionHeartGallery.aspx';
			$pager = new PageAgent($name, $firstpage);
			
			$find_listing = '\/FamiliesFirstNetwork\/AdoptionHeartBio\.aspx\?ContentID=((\d+?))';
			$find_profile = '<div style="margin:0px 0px 20px 0px;[^"]*".*>(.*)<\/div';
			$listing_format = 'http://elakeviewcenter.org/FamiliesFirstNetwork/AdoptionHeartBio.aspx?ContentID=%s';
			$searcher = new REGEX_SearchAgent($name, $pager, $find_listing, $listing_format, '', '', $find_profile, true);
			
			$scraper = new Scraper($name, $searcher, '', true);
											
			break;
		
		//Aid to Adoption of Special Kids : Arizona
        // http://www.aask-az.org/profileModal/5892214ccee7c11047b58540
		case "aask":
			$name = 'AASK';
			$firstpage = 'http://www.aask-az.org/meetthekids';
			$pager = new PageAgent($name, $firstpage);
			
			$find_listing = 'div[class=thumbnail]';
			$listing_format = 'http://www.aask-az.org/%s';
			$find_listing_link = 'a';
			$extract_stateId = 'profileModal\/(.*)';
			$find_profile = 'div[class=modal-body]';
			$searcher = new DOM_SearchAgent($name, $pager, $find_listing, $find_listing_link, '',
											$extract_stateId, $find_profile, '', $listing_format, '');
			
			$scraper = new Scraper($name, $searcher, '', true);
			
			break;

			
		case "ampf":
			$name = 'AMPF';
			$firstpage = 'https://ampersandfamilies.org/adopting-teens-minnesota/meet-our-youth/';
			$pager = new PageAgent($name, $firstpage);
			
			$listing_format = '%s';
			$find_listing = 'div[class=author-list]';
			$find_listing_link = 'a';
			$extract_stateId = 'author\/(.*)\/';
			$find_profile = 'div[class=page_wrapper]';
			//$find_stateId = 'author\/(.*)\/';
			$find_stateId = '';
			//FIXME: Need some sort of generate ID function here
			//IDEA: probably gonna have to use the end of the link ... ugh
			$searcher = new DOM_SearchAgent($name, $pager, $find_listing,
                                $find_listing_link, $find_stateId, $extract_stateId,
                                $find_profile, '', $listing_format, '', true);
			
			$scraper = new Scraper($name, $searcher, '', true);
			break;
			
		case "hck":
            $name = 'HCK';
			$firstpage = array('https://www.hckids.org/children/girls/',
                            'https://www.hckids.org/children/boys/',
                            'https://www.hckids.org/children/siblings/');
			$pager = new PageAgent($name, $firstpage);
            
            $listing_format = '%s';
            $find_listing = 'div[class=children-selection] a';
            $find_listing_link = 'none';
            $extract_stateId = 'hckids\.org\/kid\/(.*)\/';
            $find_profile = 'div[class=right-content three-fourth]';
            $searcher = new DOM_SearchAgent($name, $pager, $find_listing,
                                $find_listing_link, '', $extract_stateId,
                                $find_profile, '', $listing_format, '', false);
            
            $scraper = new Scraper($name, $searcher, '', true);
            break;
			
		case "fco":
            echo "Franklin County Ohio's site is not currently being scraped\r\n";
			$firstpage = 'https://childrenservices.franklincountyohio.gov/adoptable-kids/index.cfm?minimumAge=&maximumAge=&race=&gender=&submit=Search';
			break;
			
		case "hgla":
			echo "Heart Gallery LA cannot be accessed until they whitelist us in their incapsula configuration\r\n";
			break;
		
        //This one has an annoying format for paging
        //Need to give sibgroups their own firstpage
        //Need to figure out paging with URLGenerator
		case "afl":
			
		
            $name = 'AFL';
            $firstpage = 'http://www.adoptflorida.org/searchchild.asp?group=MALE&youngest=0&oldest=17&hc1=0&hc2=0&hc3=0&hc4=0&hc5=0&hc6=0';
            $search_page = 'http://www.adoptflorida.org/search.shtml';
			$search_postfields = array();
            $postfields = array();
            $pager = new ASPX_PageAgent($name, $firstpage, $search_page, $postfields, $search_postfields);

			$find_stateId = 'ID#:*[^\d]*(\d+?)';
			$find_profile = 'div[class=child row]';
			$searcher = new DOM_SearchAgent($name, $pager, '', '', $find_stateId,
											'', $find_profile);
			$next_format = 'http://www.adoptflorida.org/searchchild.asp?group=MALE&youngest=0&oldest=17&hc1=0&hc2=0&hc3=0&hc4=0&hc5=0&hc6=0&PageIndex=%s';
			$next_gen = new URLGenerator($next_format);
			
			$scraper = new Scraper($name, $searcher, $next_gen, false);
			
            
            break;
        
        case "ksnap":
			$name = 'KSNAP';
			$first_page = 'https://prdweb.chfs.ky.gov/SNAP/index.aspx';
            //$first_page = 'https://prdweb.chfs.ky.gov/SNAP/process_search.aspx';
            $search_page = 'https://prdweb.chfs.ky.gov/SNAP/index.aspx';
			$search_postfields = array('__VIEWSTATE', '__EVENTVALIDATION', '__EVENTARGUMENT');
            
            $postfields = array();
            $postfields['age'] = '3';
            $postfields['gender'] = '8';
            $postfields['sibling'] = '11';
            $postfields['BtSearch'] = 'Search';
            
            $pager = new ASPX_PageAgent($name, $first_page, $search_page, $postfields, $search_postfields);
            
            $find_listing = '<img id=[^>]+>\s*<br[^>]*>\s*<b>([^>]*)<\/b>.*<a href="\/SNAP\/case_detail\.aspx\?id=([^"]*)">Learn More<\/a>';
            $listing_format = 'https://prdweb.chfs.ky.gov/SNAP/case_detail.aspx?id=%s';
            $find_profile = '<table id="Child_Info"[^>]*>(.*)<\/table>';
            $searcher = new REGEX_SearchAgent($name, $pager, $find_listing, $listing_format, '', '', $find_profile, false);
            
            $next_format = 'https://prdweb.chfs.ky.gov/SNAP/process_search.aspx';
            $post_format = 'dg_Photos$ctl14$ctl%s';
            $next_gen = new URLGenerator($next_format, 2, 1, 99999, 2, $post_format, false);
            $scraper = new Scraper($name, $searcher, $next_gen, false);
            
			break;
        
        
        //http://itsmyturnnow.dhs.ga.gov/WebForms/ChildInfo.aspx?MasterChild_ID=2531&Main_ID=5039A&rwndrnd=0.518929768725042
		case "imtnga":
			$name = 'IMTNGA';
			$search_page = 'http://itsmyturnnow.dhs.ga.gov/WebForms/MeetChildren.aspx';
			$first_page = 'http://itsmyturnnow.dhs.ga.gov/WebForms/MeetChildren.aspx';
			$search_postfields = array('__VIEWSTATE', '__VIEWSTATEGENERATOR', '__EVENTARGUMENT', '__EVENTVALIDATION');
			$postfields = array();
			$postfields['ctl00$ContentPlaceHolder1$ddlChildren'] = 0;
			$postfields['ctl00$ContentPlaceHolder1$ddlGender'] = 0;
			$postfields['ctl00$ContentPlaceHolder1$ddlRace'] = 0;
			$postfields['ctl00$ContentPlaceHolder1$ddlVideoClip'] = 141;
			$pager = new ASPX_PageAgent($name, $first_page, $search_page, $postfields, $search_postfields);
			
			
			$find_listing = '<img id="[^"]*" onclick="OpenRadWin\(\'(\d+)\',\'([^\']*)\'\)';
			$listing_format = 'http://itsmyturnnow.dhs.ga.gov/WebForms/ChildInfo.aspx?MasterChild_ID=%s';
			$find_profile = '<div style ="background: #D6D7A0;">(.*id="txtMainId">([^<]*)<.*)<p style="font-size:x-small;';
			$searcher = new REGEX_SearchAgent($name, $pager, $find_listing, $listing_format, '', '', $find_profile, true);
			
			$nextpage_format = 'ctl00$ContentPlaceHolder1$RadListView1$RadDataPagerTop$ctl00$ctl%s';
			//$next_generator = new URLGenerator($nextpage_format, array(1,2), 1, array(11, 12), true, 2);
			
			
			$next_gen = new URLGenerator($nextpage_format, array(1,2), 1, array(10, 11), true, 2, true);
			$scraper = new Scraper($name, $searcher, $next_gen, false);
            
            
            //$profile_map = array('name' =>
            //$mapper = new REGEX_Mapper(

			break;
			
		case "auk":
			$name = "AUK";
			$search_page = 'https://adoptuskids.org/meet-the-children/search-for-children/search';
			$first_page = 'http://adoptuskids.org/_app/child/searchpResults.aspx?pg=1';
			$search_postfields = array('VIEWSTATE', 'VIEWSTATEGENERATOR', 'EVENTARGUMENT');
			$postfields = array();
			$postfields['__EVENTTARGET'] = 'ctl00$cph1$myChildSearch$btnSearch';
			$postfields['ctl00$cph1$myChildSearch$ddlGender'] = '';
			$postfields['ctl00$cph1$myChildSearch$ddlUpdateSince'] = '';
			$postfields['ctl00$cph1$myChildSearch$maxAge'] = 21;
			$postfields['ctl00$cph1$myChildSearch$maxSib'] = 12;
			$postfields['ctl00$cph1$myChildSearch$minAge'] = 0;
			$postfields['ctl00$cph1$myChildSearch$minSib'] = 1;
			$postfields['ctl00$cph1$myChildSearch$rdlstGeographical'] = 1;
			$postfields['ctl00$cph1$myChildSearch$rdlstRaceComposition'] = 1;
			$pager = new ASPX_PageAgent($name, $first_page, $search_page, $postfields, $search_postfields, false);
			
			$find_profile = 'div[class=summary clearfix]';
			//$find_stateId = '<div class="summary clearfix" id="([^"]*)">';
			$find_stateId = '<td class="caseNum">([^<]*)<';
			
			//$find_next = '<a href=[\'"]\/_app\/child\/searchpResults\.aspx\?pg=(\d+)[\'"]>[\'"]{0,1}NEXT[\'"]{0,1}<';
			$find_next = 'li.next a';
			//$nextpage_format = 'http://adoptuskids.org/_app/child/searchpResults.aspx?pg=%s';
			$nextpage_format = 'http://adoptuskids.org/%s';
			
			$searcher = new DOM_SearchAgent($name, $pager, '', '', $find_stateId, '', $find_profile, $find_next, '', $nextpage_format);
						
			$scraper = new Scraper($name, $searcher, '', false);
			
			$updated = $scraper->ScrapeSite();
			
			require_once('Mapper' . DIRECTORY_SEPARATOR . 'AUK_Mapper.php');
			
			$mapper = new AUK_Mapper($name);
			
			print_r($mapper->profilesToListing($updated));
			

			break;
			
		/* The following pages use a public API which returns child information in JSON format */
		
		
		// Michigan Adoption Resource Exchange
		// Accesses child info is through their public API
		// I Attempted to scrape the child profiles, but they use data-bind which I was 
		//   unable to find a way around so we just scrape the API call instead (less data).
		case "mare":
            $name = "MARE";
			$firstpage = 'http://www.mare.org/DesktopModules/MARE.Services/API/Public' .
							'/GetPhotoListedChildren?Skip=0&Take=10';
            $pager = new PageAgent($name, $firstpage);
			
			$searcher = new JSON_SearchAgent($name, $pager, 'ID');
            
            $next_format = 'http://www.mare.org/DesktopModules/MARE.Services/API/Public/' .
								'GetPhotoListedChildren?Skip=%d&Take=10';
            $next_gen = new URLGenerator($next_format, 10, 10, 9999, 3);
            
            
			$scraper = new Scraper($name, $searcher, $next_gen, false);
			
			$local_updates = $scraper->ScrapeSite();
			
			$find_siblings  = '"ID";i:([^;]+);';
			
			$comb_sibs = new Sibling_Combiner($name, $find_siblings);
			
			$local_updates = $comb_sibs->combine_siblings($local_updates);
			
			$given = array($State = 'Michagan');
			
			$child_atr = array('FirstName'=>'name', 'Narrative'=>'bio', 'ListingCode'=>'stateId',
								'Gender'=>'sex', 'Age'=>'age', 'Race'=>'race',
								'PhotoLocation'=>'photo', 'PhotoUpdated'=>'photo_updated');
			
			$listing_atr = array('PhotoLocation'=>'photos', 'VideoLocation'=>'video');
			
			$mapper = new Serialized_Mapper($name, $given, $child_atr, $listing_atr);
			
			$mapper->profilesToListing($local_updates);
			
			exit();
			
			
			/*
			$attr = array('ListingCode'=>'kidId',
						  ''=>'',
						  ''=>'',
						  ''=>'',
						  ''=>'');
			*/
			
			//$mapper = new Serialized_Mapper($name, array(), array());
			break;
	}
	
	
	if (!isset($scraper) || !is_a($scraper, 'Scraper')) {
		echo "No scraper object\r\n";
		exit();
	}
	
	try {
		$local_updates = $scraper->ScrapeSite();
	} catch (Exception $e) {
		echo $e->getMessage() . "\r\n";
		exit();
	}
	echo "Found updates in files:\r\n";
	print_r($local_updates);
    
    
    //TODO
    //Here we check what has been scraped so far, and if enough have
    //accumulated, we can go onto the mapping phase
    
    //Need to combine the profiles from one or more sites into a single
    //listing
    
    
    if (!isset($mapper) || !is_a($mapper, 'Mapper')) {
        echo "No mapper object\r\n";
        exit();
    }
    $listing;
    foreach($local_updates as $update) {
        try {
            $listing = $mapper->profilesToListing(array($update));
        } catch (Exception $e) {
            echo $e->getMessage();
			exit();
        }
        print_r($listing);
        //TODO Call update database on listing and generate report
    }
    
    
    
    //For now, we assume that children only match when their ids are
    //the same. In the future we may have to do a quick regex search
    //of the files to determine which kids match from each site.
    /*
    class statusTracker
    {
        $mare;
		$oapl;
		$ccyf;
		$pfsf;
		$lvc;
		$aask;
		$ampf;
		$hck;
		$fco;
		$afl;
		$hgla;
        $ksnap;
		$imtnga;
		$auk;
    }
    */
?>
