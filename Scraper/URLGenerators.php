<?php
	//Handles more complex page counting and formating
	//This is currently equipped to handle IMTNGA but can be easily adapted
	//to work in other ways
	/*
	 * @properties
     *      NOTE: Format Strings can be set to '%s' to take count directly     
	 *		String	$url_format		Sprintf format for adding count to url
     *      String  $post_format    Sprintf format for adding count to a postfield
     *                                  If left as '' postfields will not be returned
     *          
	 *		Int[]	$start			Starting point for count
	 *		Int[]	$reset_count	Resets count to start when reached
	 *		Int		$page_count		The current count
	 *		Int		$increment		How much to increment for with each getNextPage()
	 *		Int		$num_digits		Adds leading zeroes before count
	 *		Boolean $is_postfield	Returns as an EVENTTARGET instead of url
	 *		Boolean $async_post		Adds an additional post for IMTNGA
	 *
	 *		UNIMPLEMENTED
	 *		Boolean $loop_count		Cycles through arrays instead of popping
	 */
	
	class URLGenerator {
		protected $url_format;
        protected $post_format;
		protected $start;
		protected $reset_count;
		protected $page_count;
		protected $increment;
		protected $num_digits;
		protected $is_postfield;
		protected $async_post;
		
		function __construct($url_format = '%s', $start_count = 2, $increment = 1,
					  $reset_count = 9999, $num_digits = 1, $post_format = '',
					  $async_post = false)
		{
			if (is_array($start_count)) {
				$this->page_count = $start_count[0];
				$this->start = array_slice($start_count, 1);
			}
			else {
				$this->page_count = $start_count;	
			}
            //if the post format is empty do not return a postfield
            $this->is_postfield = !($post_format == '');
			$this->url_format   = $url_format;
            $this->post_format  = $post_format;
			$this->increment    = $increment;
			$this->reset_count  = $reset_count;
			$this->num_digits   = $num_digits;

		}
		
		function getNextPage() {
            //Hold count as string for adding leading zeroes
			$count = "{$this->page_count}";
            //Add leading zeroes until the number of digits is reached
			while (strlen($count) < $this->num_digits)
				$count = '0' . $count;
			$url_output = sprintf($this->url_format, $count);
			$this->page_count += $this->increment;
            
            //If page_count has reached the reset limit,
            //  Reset page_count using the next value of start
			if ($this->reset_count <= $this->page_count) {
				if (is_array($this->start)) {
					//The lazy man's pop() function
                    $this->page_count = $this->start[0];
					$this->start = array_slice($this->start, 1);
				}
				else
					$this->page_count = $this->start;
			}
			if ($this->post_format != '') {
                $post_output = sprintf($this->post_format, $count);
				$arr = array('url'=>$url_output, '__EVENTTARGET'=>$post_output);
				if ($this->async_post)
					$arr['__ASYNCPOST'] = true;
				return $arr;
				
			}
			return $url_output;
		}
	}
?>
