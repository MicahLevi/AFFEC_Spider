<?php
 
	class Listing {
		               
		public $kidID;     /* Key to KidInfo. When set non- -1, the bulletin number was found on the website */
		public function check_kidID($x)
		{
			if (!is_int($x))
				throw new Exception("Listing website kidID is not an integer!");
			if ($x < -1 || $x == 0 || $x > 1000000)
				throw new Exception("Invalid value for website kidID in Listing!");
			return $x;
		}
        
        public $sfid;
		public $names;                          //string representation of all kid names
		public $Bulletin_Number__c;             //bulletin number for group
		public $kids;                           //array of child objects sorted by desc age

        public $bio;
        
        //Geographical
		public $state;
        public $Child_s_County__c;
        
        //Legal                  
		public $Legal_Status2__c;
        
        //Links                
		public $Media_Weblink__c;
        public $thumbnail;
        public $photos;                          //group photo
		public $video;
		public $links;
		public $embeds;
        
        //Contacts
		public $Photographer;
		public $Videographer;
        
        /*Don't know if we need these yet*/
        //public $Northwest_HG__c;
        //public $Custom_Recruitment_States__c;
        //public $update_or_add;
        
        public $recruitment_status;
        const RECRUITMENT_STATUS_ACTIVE = 'Active';
		const RECRUITMENT_STATUS_ONHOLD = 'On Hold';
		const RECRUITMENT_STATUS_INACTIVE = 'Inactive';
		const RECRUITMENT_STATUS_PLACED = 'Placed';
		const RECRUITMENT_STATUS_PRERECRUITMENT = 'Pre-Recruitment';
		const RECRUITMENT_STATUS_COMMITTEE_PENDING = 'Committee Pending';
		const RECRUITMENT_STATUS_OTHER_PROGRAM = 'Other Program';
		const RECRUITMENT_STATUS_PHOTO_REQUEST = 'Photo Request';
        
        public $Recruitment_Region__c; 
        const RECRUITMENT_REGION_NATIONAL = 'National';
		const RECRUITMENT_REGION_REGIONAL = 'Regional';
		const RECRUITMENT_REGION_INSTATE = 'State';
		const RECRUITMENT_REGION_CUSTOM = 'Custom';
        
        public $public_listing_ok;
        const PUBLIC_LISTING_PUBLIC = 'public';
		const PUBLIC_LISTING_PRIVATE = 'private';
		const PUBLIC_LISTING_NEITHER = 'neither';
		const PUBLIC_LISTING_PRERECRUITMENT = 'prerecruitment';
        

		
		function __construct()
		{
			$this->kids = array();
			//$this->primaryContact = new Contact();
			//$this->secondaryContact = new Contact();
			//$this->additionalContact = new Contact();
			//$this->links = array();
			//$this->embeds = array();
            
            
		}
		public function __get($name)
		{
			throw new Exception("Attempt to access a non-existent property $name in " . get_class($this));
		}


		public function __set($name, $value)
		{
			throw new Exception("Attempt to access a non-existent property $name in " . get_class($this));
		}
        
        public function update_databases() {
            
        }
        
        public function search_databases() {
            $sfid = find_salesforce_id();
            $kidId = find_kidInfo_id();
            //TODO: Lookup the kidInfo SFID and compare
            /*
            $local_sfid;
            if ($sfid !== $local_sfid)
                throw new Exception("Found conflicting sfids from Salesforce: $sfid and KidInfo $kidId: $local_sfid");
            $this->sfid = $sfid;
            $this->kidId = $kidId;
            */
        }
        
        
        public function find_salesforce_id() {
            
        }
        
        public function find_kidInfo_id() {
            
        }
	}
	
	class Child
	{
        public $stateId;
		public $name;
		public $age;
		public $birthdate;
		public $needs;
        public $primary_language;
        public $status;
        public $bio;
		
		public $photo;
		public $photo_updated;
		
        //Contacts
        public $primaryContact;                 //Caseworker
		public $secondaryContact;               //Supervisor
		public $additionalContact;
        
        public $sex;
        const SEX_MALE = 'Male';
        const SEX_FEMALE = 'Female';
        
        public $race;
        const ETH_WHITE = 'White';
        const ETH_AFRICAN_AMERICAN = 'African American/Black';
        const ETH_ASIAN = 'Asian';
        const ETH_NATIVE_AMERICAN = 'American Indian-Alaskan Native';
        const ETH_ALASKAN_NATIVE = 'Alaskan Native';
        const ETH_HISPANIC = 'Hispanic/Latino';
        const ETH_PACIFIC_ISLANDER = 'Pacific Islander-Hawaiian';
        const ETH_MIXED = 'Mixed Race (2 or more - not AA & White)';
        const ETH_BIRACIAL = 'Bi-Racial(AA & White)';
        const ETH_UNKNOWN = 'Unknown';
		

        
		function __construct()
		{
			$this->needs = array();
		}
		
		public function __get($name)
		{
			throw new Exception("Attempt to access a non-existent property $name in " . get_class($this));
		}


		public function __set($name, $value)
		{
			throw new Exception("Attempt to access a non-existent property $name in " . get_class($this));
		}
	}
	
	
	class Contact
	{
		public $name;
		public $email;
		public $phone;
        public $region;
		
		
		public function __get($name)
		{
			throw new Exception("Attempt to access a non-existent property $name in " . get_class($this));
		}


		public function __set($name, $value)
		{
			throw new Exception("Attempt to access a non-existent property $name in " . get_class($this));
		}
	}
	
	class Link
	{
		public $url;
		public $orig_name;
		
		function __construct($a, $b)
		{
			$this->url = $a;
			$this->orig_name = $b;
		}
		
		public function __get($name)
		{
			throw new Exception("Attempt to access a non-existent property $name in " . get_class($this));
		}


		public function __set($name, $value)
		{
			throw new Exception("Attempt to access a non-existent property $name in " . get_class($this));
		}
	}
?>
