<?php

	class Sibling_Combiner {
		public $name;
		public $find_siblings;
		
		function __construct($name, $find_siblings) {
			$this->name = $name;	
			$this->find_siblings = $find_siblings;
		}
		
		function combine_siblings($filenames) {
			$directory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Listings' . DIRECTORY_SEPARATOR . $this->name . DIRECTORY_SEPARATOR;
			$kids = array();
			foreach ($filenames as $fname) {
				$f = $directory . $fname;
				if (!file_exists ($f))
					throw new Exception ("Failed to locate file $f");
				$profile = file_get_contents($f);
				if (preg_match_all('/' . $this->find_siblings . '/isU', $profile, $matches) && count($matches)>0 && count($matches[0]) > 1) {
					$kids[$fname] = $matches[1];
				}	
			}
			
			$combines = array();
			$completed = array();
			//Iterate over the Listings
			foreach ($kids as $id=>$sibs) {
				if (in_array($id, $completed))
					continue;
				$completed[] = $id;
				sort($sibs);
				$group = array();
				$group[] = $id;
				foreach ($kids as $altId=>$altSibs) {
					if (!in_array($altId, $completed)) {
						sort($altSibs);
						if ($sibs == $altSibs) {
							$group[] = $altId;
							$completed[] = $altId;
						}
					}
				}
				if (count($group) == 1) {
					print_r($group);
					throw new Exception ("Expected to combine multiple siblings but found only one for $id");
				}
				$combines[] = $group;
			}
			$added = array();
			foreach ($combines as $c) {
				$n = 'sibgrp_' . implode('_', $c);
				$added[] = $n;
				$fname = $directory . $n;
				$f = fopen($fname, 'wb');
				foreach ($c as $id) {
					fputs($f, file_get_contents($directory . $id) . "\r\n\r\n");
				}
				fclose($f);
			}
			$filenames = array_diff($filenames, $completed);
			$filenames = array_merge($filenames, $added);
			return $filenames;
		}
		
	}


?>