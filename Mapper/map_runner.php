<?php
    include_once('SpiderMapper.php');
    
    //$profile_map = array('name'=>
    
    
    //$html = file_get_contents('../SpiderListings/IMTNGA/4428D-4429D');
    //echo $html;
    
    $given = 
    
    $reg = array('<img\s*id="[^"]*"\s*src="([^"]*)"[^>]*>'
                    =>array('photos'),
                '<span id="txtDescription"[^>]*>(.*)<\/span>'
                    =>array('bio'));
                    
    $reg1 = array('(?:<td style="font-family[^"]*">([^<]*)<\/td>)+'
                    =>array('stateId', 'name', 'status', 'sex', 'age'));
                    
    $given = array('State'=>'Georgia');
    
    $mapper = new REGEX_Mapper($given, $reg, $reg1);
    
    
    
    $base = '../SpiderListings/IMTNGA/';
    
    $files = array($base . '4425D',
                   $base . '4671R',
                   $base . '4834A',
                   $base . '4959A-4960A',
                   $base . '5042A-5043A',
                   $base . '4428D-4429D',
                   $base . '4889A-4892A',
                   $base . '4516R-4518R',
                   $base . '5047A-5048A',
                   $base . '4946A-4947A',
                   $base . '4930A-4931A',
                   $base . '5037A',
                   $base . '5051A',
                   $base . '4738R',
                   $base . '5009A',
                   $base . '5038A',
                   $base . '5046A',
                   $base . '4799R',
                   $base . '5034A',
                   $base . '5039A',
                   $base . '4564R',
                   $base . '4802A',
                   $base . '5036A',
                   $base . '5041A',
                   $base . '5049A');
    
    foreach($files as $f) {
        $listing = $mapper->profilesToListing(array($f));
        print_r($listing);
    }
    
?>
