<?php
	class Listing
	{
		public $kidID;     /* Key to KidInfo. When set non- -1, the bulletin number was found on the website */
		public function check_kidID($x)
		{
			if (!is_int($x))
				throw new Exception("Listing website kidID is not an integer!");
			if ($x < -1 || $x == 0 || $x > 1000000)
				throw new Exception("Invalid value for website kidID in Listing!");
			return $x;
		}

		public $sfid;                                   /* Filled out only when a SF record exists */
		public function check_sfid($x)
		{
			if (is_null($x))
				return $x;
			if (strlen($x) != 18)
				throw new Exception("Listing Salesforce ID is not 18 characters!");
			if (!ctype_alnum($x))
				throw new Exception("Listing Salesforce ID has illegal characters!");
			return $x;
		}

		const UPDATE = 1;
		const ADD = 2;
		public $update_or_add;  /* changed to UPDATE after first child is written */
		public function check_update_or_add($x)
		{
			if (!is_int($x))
				throw new Exception("Listing flag about being an Update or an Addition is not correctly set");
			if ($x != self::UPDATE && $x != self::ADD)
				throw new Exception("Listing flag about being an Update or an Addition is not ADD or UPDATE");
			return $x;
		}


		/* The comments emailed separately to the AFFEC child-listing crew */
		public $commentstoaffec;

        /* UNNESSESARY FOR SPIDER??
		const OTHER_PROGRAM_FAMILY_FINDING  = 'Family finding';
		const OTHER_PROGRAM_MENTOR          = 'Mentor';
		const OTHER_PROGRAM_FOSTER_TO_ADOPT = 'Foster to adopt';
		const OTHER_PROGRAM_HEART_GALLERY   = 'Heart gallery';
		const OTHER_PROGRAM_PRINCESS_EVENT  = 'Princess Event';
		const OTHER_PROGRAM_HERO_FOR_A_DAY  = 'Hero For A Day';
		const OTHER_PROGRAM_BMWCMF          = 'BMWCMF';
		const OTHER_PROGRAM_TARGETED_EMAIL  = 'Targeted Email';
		const OTHER_PROGRAM_PHOTO_REQUEST   = 'Photo Request - No Listing';
		static $OTHER_PROGRAMS = array('ff' => self::OTHER_PROGRAM_FAMILY_FINDING,
                                       'ment' => self::OTHER_PROGRAM_MENTOR,
		                               'fa' => self::OTHER_PROGRAM_FOSTER_TO_ADOPT,
                                       'hg' => self::OTHER_PROGRAM_HEART_GALLERY,
		                               'ev' => self::OTHER_PROGRAM_PRINCESS_EVENT,
                                       'hd' => self::OTHER_PROGRAM_HERO_FOR_A_DAY,
		                               'bmwcmf' => self::OTHER_PROGRAM_BMWCMF,
                                       'te' => self::OTHER_PROGRAM_TARGETED_EMAIL,
		                               'pr' => self::OTHER_PROGRAM_PHOTO_REQUEST);
		public $other_program;
		public function check_other_program($x)
		{
			if ($x == '')
				return $x;
			// Program Associated With Multipick list
			$a = explode(';', $x);
			foreach ($a as $item)
			{
				switch ($item)
				{
					case self::OTHER_PROGRAM_BMWCMF:
					case self::OTHER_PROGRAM_FAMILY_FINDING:
					case self::OTHER_PROGRAM_FOSTER_TO_ADOPT:
					case self::OTHER_PROGRAM_HEART_GALLERY:
					case self::OTHER_PROGRAM_HERO_FOR_A_DAY:
					case self::OTHER_PROGRAM_MENTOR:
					case self::OTHER_PROGRAM_PRINCESS_EVENT:
					case self::OTHER_PROGRAM_TARGETED_EMAIL:
					case self::OTHER_PROGRAM_PHOTO_REQUEST;
						continue;
					default:
						throw new Exception("Listing Program Associated With contains $item, which is not an approved value");
				}
			}
			return $x;
		}
        */

		public $state;/* Childrens state */
		public function check_state($x)
		{
			try { return check_mandatory_state($x);}
            catch (Exception $x) {
                throw new Exception('Listing: ' . $x->getMessage());}
		}

		public $caseno;/* As determined by the state */
		public function check_caseno($x)
		{
			if (!is_string($x))
				throw new Exception("The State Case Number must be a string!");
			$x = trim($x);
			$len = strlen($x);
			for ($l = 0; $l < $len; $l++)
			{
				$c = $x[$l];
				if (!ctype_alnum($c) && $c != '-' && $c != '.' && $c != ',' && $c != ' ')
					throw new Exception("State case numbers can only contain alphanumerics, dashes, commas, and periods");
			}
			return $x;
		}

		public $link_to_childs_page;
		public function check_link_to_childs_page($x)
		{
			if (!is_string($x))
				throw new Exception('The link to childs page must be a string');
			$x = trim($x);
			$len = strlen($x);
			if ($len == 0)
				return '';
			if ($len >= 256)
				throw new Exception('The link to the childs page must be fewer than 256 characters long');
			if (!preg_match('/^http(?:s)?\:.*$/', $x))
				throw new Exception('The link to the childs page must look like a URL!');
			return $x;

		}
		
		const RECRUITMENT_STATUS_ACTIVE = 'Active';
		const RECRUITMENT_STATUS_ONHOLD = 'On Hold';
		const RECRUITMENT_STATUS_INACTIVE = 'Inactive';
		const RECRUITMENT_STATUS_PLACED = 'Placed';
		const RECRUITMENT_STATUS_PRERECRUITMENT = 'Pre-Recruitment';
		const RECRUITMENT_STATUS_COMMITTEE_PENDING = 'Committee Pending';
		const RECRUITMENT_STATUS_OTHER_PROGRAM = 'Other Program';
		const RECRUITMENT_STATUS_PHOTO_REQUEST = 'Photo Request';
		public $recruitment_status;                     /* As recorded in salesforce and website */
		public function check_recruitment_status($x)
		{
			if (!is_string($x))
				throw new Exception("Recruitment Status must be one of the selections to be valid");
			$x = trim($x);
			switch($x)
			{
				case self::RECRUITMENT_STATUS_ACTIVE:
				case self::RECRUITMENT_STATUS_ONHOLD:
				case self::RECRUITMENT_STATUS_INACTIVE:
				case self::RECRUITMENT_STATUS_PLACED:
				case self::RECRUITMENT_STATUS_PRERECRUITMENT:
				case self::RECRUITMENT_STATUS_COMMITTEE_PENDING:
				case self::RECRUITMENT_STATUS_OTHER_PROGRAM:
				case self::RECRUITMENT_STATUS_PHOTO_REQUEST:
					return $x;
			}
			throw new Exception("Invalid Recruitment Status Supplied");
		}

		const PUBLIC_LISTING_PUBLIC = 'public';
		const PUBLIC_LISTING_PRIVATE = 'private';
		const PUBLIC_LISTING_NEITHER = 'neither';
		const PUBLIC_LISTING_PRERECRUITMENT = 'prerecruitment';
		public $public_listing_ok;
		public function check_public_listing_ok($x)
		{
			if (!is_string($x))
				throw new Exception("Please choose \"where to list\" this child!");
			switch ($x)
			{
				case self::PUBLIC_LISTING_PUBLIC:
				case self::PUBLIC_LISTING_PRIVATE:
				case self::PUBLIC_LISTING_NEITHER:
				case self::PUBLIC_LISTING_PRERECRUITMENT:
					return $x;
			}
			throw new Exception("The value for your authorization to list publicly must be one of the 3 values supplied!");
		}
		
		const RECRUITMENT_REGION_NATIONAL = 'National';
		const RECRUITMENT_REGION_REGIONAL = 'Regional';
		const RECRUITMENT_REGION_INSTATE = 'State';
		const RECRUITMENT_REGION_CUSTOM = 'Custom';
		public $recruitment_region;                     /* Regional/State/(NULL) */
		public function check_recruitment_region($x)
		{
			if (!is_string($x) || $x == '')
			{
				/* If they specified other program, it is ok not to have a recruitment region */
				if ($this->public_listing_ok == self::PUBLIC_LISTING_NEITHER)
					return NULL;
				throw new Exception("Please choose national, regional, custom, or state-wide-only recruitment!");
			}
				
			switch ($x)
			{
				case self::RECRUITMENT_REGION_NATIONAL:
				case self::RECRUITMENT_REGION_REGIONAL:
				case self::RECRUITMENT_REGION_INSTATE:
				case self::RECRUITMENT_REGION_CUSTOM:
					return $x;
			}
			throw new Exception("Please select one of the four valid choices for recruitment region!");
		}

		public $recruitment_states;
		public function check_recruitment_states($x)
		{
			$x = trim($x);
			if($x == '')
				return $x;

			$recruitment_states = array();
			foreach(explode(';', $x) as $statename)
				if (in_array($statename, $GLOBALS['check_us_states_list']))
					$recruitment_states[] = $statename;
			return implode(';', $recruitment_states);
		}

		public $icwa_compliant;                         /* True/False(NULL) */
		public function check_icwa_compliant($x)
		{
			if (is_null($x) || is_bool($x))
				return $x;
			throw new Exception("Invalid value for whether the whether the Indian Child Welfare Act applies was supplied!");
		}
		
		
		public $bio;                                    /* Text as input */
		public function check_bio($x)
		{
			$x = trim($x);
			if ($x == '')
				return NULL;
			setlocale(LC_CTYPE, 'en_US');
			$encoding = mb_detect_encoding($x, "UTF-8,ISO-8859-1,WINDOWS-1252", true);
			if ($encoding != 'UTF-8')
				$x = iconv($encoding, 'UTF-8//TRANSLIT', $x);
			return strip_to_utf8($x);
		}


		public $embed;									/* Embedded video  */
		public function check_embed($x)
		{
			$x = trim($x);
			if ($x == '')
				return NULL;
			setlocale(LC_CTYPE, 'en_US');
			$encoding = mb_detect_encoding($x, "UTF-8,ISO-8859-1,WINDOWS-1252", true);
			if ($encoding != 'UTF-8')
				$x = iconv($encoding, 'UTF-8//TRANSLIT', $x);
			return strip_to_utf8($x);
		}

		public $media_links;
		public function check_media_links($x)
		{
			$error_message = '';
			if (!is_array($x))
				throw new Exception("Media links must be an array!");
			foreach ($x as $link)
				try { $link->check($link); } catch (Exception $y) { $error_message .= $y->getMessage(); }
			if ($error_message != '')
				throw new Exception($error_message);
			return $x;
		}


		public $caseworker;
		public function check_caseworker($x)
		{
			$error_message = '';
			if (is_null($x))
				return $x;
			if (!is_a($x, 'Caseworker'))
				throw new Exception('Childs Caseworker must be a Caseworker Object');
			return $x;
		}


		public $photographer;
		public function check_photographer($x)
		{
			$error_message = '';
			if (is_null($x))
				return $x;
			try { $x->check(); } catch(Exception $y) { $error_message .= $y->getMessage(); }
			if ($error_message != '')
				throw new Exception($error_message);
			return $x;
		}
		public $videographer;
		public function check_videographer($x)
		{
			$error_message = '';
			if (is_null($x))
				return $x;
			try { $x->check(); } catch(Exception $y) { $error_message .= $y->getMessage(); }
			if ($error_message != '')
				throw new Exception($error_message);
			return $x;
		}
		public $nchildren;                            /* 1-8, 2-8 are Sibling Groups */
		public function check_nchildren($x)
		{
			if (((int) $x) < 1 || ((int) $x) > 8)
				throw new Exception("Too many or too few children have been placed in the sibling group!");
			return (int) $x;
		}

		public $children;
		public function check_children($x)
		{
			$error_message = '';
			for ($c = 0; $c < $this->nchildren; $c++)
				try { $this->children[$c]->check(); } catch (Exception $y) { $error_message .= $y->getMessage(); }
			if ($error_message != '')
				throw new Exception($error_message);
			return $x;
		}

		public $bulletinno;
		public $autobulletin; /* Whether to create a bulletin number automatically (or not) */
		public $bulletinerror; /* so you can still change it after it is non-blank */
		public function check_bulletinno($x)
		{
			if (is_null($x) || $x == '' || $this->autobulletin === FALSE)
			{
				$this->bulletinerror = FALSE;
				return $x;
			}
			/* Now we let them specify it, so the editing needs to be done */
			if (strlen($x) < 3)
			{
				$this->bulletinerror = TRUE;
				throw new Exception('The 2-letter state part of the bulletin number (followed by a number) is too short!');
			}
			$state = strtoupper(substr($x, 0, 2));
			$usstates = $GLOBALS['check_us_states_list'];
			if (!isset($usstates[$state]) || ($state == 'AA' && $listing->update_or_add == Listing::UPDATE))
			{
				$this->bulletinerror = TRUE;
				throw new Exception('Bulletin number consists of a 2-letter state abbreviation followed by a non-zero integer');
			}
			$number = substr($x, 2);
			if (!ctype_digit($number))
			{
				$this->bulletinerror = TRUE;
				throw new Exception('Bulletin number is state followed by a number. It must be a number!');
			}
			$x = $state . (int) $number;
			$this->bulletinerror = FALSE;
			return $x;
		}

		/* If listing is started, $nphotos is 1 and only the first photo shows. */
		/* If listing is a pre-existing kid, nphotos is number of showing photos + 1 */
		/* If a new photo is added, nphotos is bumped and a new iframe is added */
		/* Iframes are now populated into a div */
		public $thumbnail_shows_as_n;      /* when the 0th photo shows a thumbnail, set by readall, set to 1-n of the pos of thumb */
		public function check_thumbnail_shows_as_n($x)
		{
			if ($x === FALSE)
				throw new Exception("Please specify which photo to reduce as a thumbnail");
			if (!is_numeric($x))
				throw new Exception("Did you remember to mark the checkbox about which photo should be a thumbnail?");
			if ($x < 0)
				throw new Exception("The thumbnail number cannot be a negative value!");
			if ($x >= 100)
				throw new Exception("You have got to be kidding! The thumbnail# is greater than a hundred?");
			if (!isset($this->photos[$x]))
				throw new Exception("Please specify a currently valid photo!");
			return $x;
		}
		public $thumbnail_changed;           /* whether to rewrite salesforce and /Kid_Thumbs */

		// Used to display listings, and that is all.
		public $sfthumbnail_path;

		public $nphotos;                     /* Number of photos, including duplicacious thumbnail at beginning */
		public function check_nphotos($x)
		{
			if (!is_numeric($x))
				throw new Exception("The number of photos must be an integer!");
			if ($x < 0)
				throw new Exception("The number of photos cannot be a negative value!");
			if ($x >= 100)
				throw new Exception("You have got to be kidding! More than a hundred photos?");
			return $x;
		}
		
		public $photos;
		public function check_photos($x)
		{
			$error_message = '';
			if (!is_array($x))
				throw new Exception("Photos must be an array!");
			foreach ($x as $photo)
				try { $photo->check($photo); } catch (Exception $y) { $error_message .= $y->getMessage(); }
			if ($error_message != '')
				throw new Exception($error_message);
			return $x;
		}

		public $havevideo;
		public function check_havevideo($x)
		{
			$error_message = '';
			if ($x === TRUE || $x === FALSE)
				return $x;
			throw new Exception('Invalid value for havevideo!');
		}


		/* video1 or NULL, at most one as the main feature. */
		public $video1;
		public function check_video1($x)
		{
			if ($x == '')
				return NULL;
			setlocale(LC_CTYPE, 'en_US');
			$encoding = mb_detect_encoding($x, "UTF-8,ISO-8859-1,WINDOWS-1252", true);
			if ($encoding != 'UTF-8')
				$x = iconv($encoding, 'UTF-8//TRANSLIT', $x);
			return strip_to_utf8($x);
		}

		public $names;                              /* First names of all the kids, calculated by program, separated by commas */
		public function check_names($x)
		{
			$birthdatenames = array();
			for ($n = 0; $n < $this->nchildren; $n++)
			{
				$child = $this->children[$n];
				$name = $child->bare_firstname();
				if (array_key_exists($name, $birthdatenames))
					throw new Exception('Two siblings cannot have the same name!');
				$birthdatenames[$name] = $child->birthdate;
			}
			asort($birthdatenames);
			$names = '';
			foreach ($birthdatenames as $name => $birthdate)
				$names .= ', ' . $name;
			if ($names != '')
				$names = substr($names, 2);
			$pos = strrpos($names, ',');
			if ($pos !== FALSE)
				$names = substr($names, 0, $pos) . ' and ' . substr($names, $pos + 1);
			return $names;
		}


		//public $date_created;                       /* Record of date set by Salesforce, no real application here */
		public $date_last_modified;
		public $Web_Adoption_Recruitment_Date__c;	/* Avoid filling out the date on each save */
		public $Northwest_HG_Private_Listing_Date__c; /* Avoid filling out the date on each save */
		//public $Child_Bulletin_Date__c;             /* Avoid filling out the date on each save */


		
	}
?>
