<?php
/*
	include_once('PagingAgents.php');
	
	$post = array();
	$post['__EVENTTARGET'] = 'ctl00$cph1$myChildSearch$btnSearch';
	$post['ctl00$cph1$myChildSearch$ddlGender'] = '';
	$post['ctl00$cph1$myChildSearch$ddlUpdateSince'] = '';
	$post['ctl00$cph1$myChildSearch$maxAge'] = 21;
	$post['ctl00$cph1$myChildSearch$maxSib'] = 12;
	$post['ctl00$cph1$myChildSearch$minAge'] = 0;
	$post['ctl00$cph1$myChildSearch$minSib'] = 1;
	$post['ctl00$cph1$myChildSearch$rdlstGeographical'] = 1;
	$post['ctl00$cph1$myChildSearch$rdlstRaceComposition'] = 1;
	
	$agent = new ASPX_PageAgent('test', 'http://adoptuskids.org/_app/child/searchp.aspx', 'http://adoptuskids.org/_app/child/searchpResults.aspx?pg=%d', $post);
	$html = $agent->pageRequest('http://adoptuskids.org/_app/child/searchpResults.aspx?pg=2');
	file_put_contents('foo.html', $html);
	$html1 = $agent->pageRequest('http://adoptuskids.org/_app/child/searchpResults.aspx?pg=3');
	file_put_contents('foo1.html', $html1);
	exit();
*/

	$f = fopen('log.txt', 'w');
	$agent = "Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36";
	$channel = curl_init();
	$url = 'http://itsmyturnnow.dhs.ga.gov/WebForms/MeetChildren.aspx';
	//$url = 'https://prdweb.chfs.ky.gov/SNAP/process_search.aspx';
	//$url = 'https://prdweb.chfs.ky.gov/SNAP/index.aspx';
	//$cookie_jar = '';
	$ckfile = tempnam("/tmp", "CURLCOOKIE");
	
	curl_setopt($channel, CURLOPT_URL, $url);
	curl_setopt($channel, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($channel, CURLOPT_USERAGENT, $agent);
	curl_setopt($channel, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($channel, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($channel, CURLOPT_COOKIEJAR, $ckfile);
	//curl_setopt($channel, CURLOPT_VERBOSE, 1);
	//curl_setopt($channel, CURLOPT_STDERR, $f);
	//curl_setopt($channel, CURLOPT_MAXREDIRS, 5);
	//curl_setopt($channel, CURLOPT_CONNECTTIMEOUT, 30); 
	//curl_setopt($channel, CURLOPT_TIMEOUT, 30);
	$result1 = curl_exec($channel);
	curl_close($channel);
	file_put_contents('foo.html', $result1);
	
	$viewstate = '';
	if (preg_match('/<input type="hidden" name="__VIEWSTATE" id="__VIEWSTATE" value="([^"]*)" \/>/', $result1, $viewstate))
		$viewstate = $viewstate[1];
		
	$viewStateGenerator = '';
	if (preg_match('/<input type="hidden" name="__VIEWSTATEGENERATOR" id="__VIEWSTATEGENERATOR" value="(.*?)" \/>/', $result1, $viewStateGenerator))
		$viewStateGenerator = $viewStateGenerator[1];
		
	$eventvalidation = '';
	if (preg_match('/<input type="hidden" name="__EVENTVALIDATION" id="__EVENTVALIDATION" value="(.*?)" \/>/', $result1, $eventvalidation))
		$eventvalidation = $eventvalidation[1];	
		
	
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_REFERER, $url);
	curl_setopt($ch, CURLOPT_USERAGENT, $agent);
	curl_setopt($ch, CURLOPT_VERBOSE, 1);
	curl_setopt($ch, CURLOPT_STDERR, $f);
	
	
	$postfields = array();
	//$postfields['RadAJAXControlID'] = 'ctl00_ContentPlaceHolder1_AjaxPanel1';
	//$postfields['ctl00_ToolkitScriptManager1'] = 'ctl00$ContentPlaceHolder1$ctl00$ContentPlaceHolder1$RadListView1Panel|ctl00$ContentPlaceHolder1$RadListView1$RadDataPagerTop$ctl00$ctl03';
	//$postfields['ctl00_ToolkitScriptManager1_HiddenField'] = 'ctl00_ToolkitScriptManager1_HiddenField';
	$postfields['__EVENTTARGET'] = 'ctl00$ContentPlaceHolder1$RadListView1$RadDataPagerTop$ctl00$ctl04';
	$postfields['__EVENTARGUMENT'] = "";
	$postfields['__VIEWSTATE'] = $viewstate;
	$postfields['__VIEWSTATEGENERATOR'] = $viewStateGenerator;
	$postfields['__EVENTVALIDATION'] = $eventvalidation;
	$postfields['__ASYNCPOST'] = true;
	//$postfields['__PREVIOUSPAGE'] = $previouspage;
	$postfields['ctl00$ContentPlaceHolder1$ddlChildren'] = 0;
	$postfields['ctl00$ContentPlaceHolder1$ddlGender'] = 0;
	$postfields['ctl00$ContentPlaceHolder1$ddlRace'] = 0;
	$postfields['ctl00$ContentPlaceHolder1$ddlVideoClip'] = 141;

/*	
__LASTFOCUS=
__PREVIOUSPAGE=9R94AXI2Iep55QAmRSNgWDYzrWgumWIPTSRPOny9XLgtNH8Cq01xRShJQyWmq4wnx3Gv7_E_EC5U1RvdLVS--YOTDSU7I7b0EILgRdONqFPrxkc-0
ctl00$ContentPlaceHolder1$txtMaxAge
ctl00$ContentPlaceHolder1$txtMaxChild
ctl00$ContentPlaceHolder1$txtMinAge
ctl00$ContentPlaceHolder1$txtMinChild=
ctl00$ContentPlaceHolder1$txtSystem=
ctl00$ToolkitScriptManager1=ctl00$ContentPlaceHolder1$ctl00$ContentPlaceHolder1$RadListView1Panel|ctl00$ContentPlaceHolder1$RadListView1$RadDataPagerTop$ctl00$ctl03
ctl00$hiddenCMLoginAsTargetControlID=
ctl00$hiddenText=
ctl00$ucCMLogin$Login1$Password=
ctl00$ucCMLogin$Login1$UserName=
ctl00_ContentPlaceHolder1_RadListView1_ClientState=
ctl00_ContentPlaceHolder1_RadListView1_RadDataBottom_ClientState=
ctl00_ContentPlaceHolder1_RadListView1_RadDataPagerTop_ClientState=
ctl00_ContentPlaceHolder1_RadListView1_ctrl0_RadGrid1_ClientState=
ctl00_ContentPlaceHolder1_RadListView1_ctrl1_RadGrid1_ClientState=
ctl00_ContentPlaceHolder1_RadListView1_ctrl2_RadGrid1_ClientState=
ctl00_ContentPlaceHolder1_RadListView1_ctrl3_RadGrid1_ClientState=
ctl00_ContentPlaceHolder1_RadListView1_ctrl4_RadGrid1_ClientState=
ctl00_ContentPlaceHolder1_RadWindowManager1_ClientState=
ctl00_ContentPlaceHolder1_rwChildInfo_ClientState=
ctl00_ContentPlaceHolder1_rwPlayVideo_ClientState=
ctl00_RadWindowManager1_ClientState=
ctl00_ToolkitScriptManager1_HiddenField=;;AjaxControlToolkit, Version=3.5.50401.0, Culture=neutral, PublicKeyToken=28f01b0e84b6d53e:en-US:beac0bd6-6280-4a04-80bd-83d08f77c177:5546a2b:475a4ef5:effe2a26:b5ab7149:497ef277:a43b07eb:1d3ed089:751cdd15:dfad98a5:3cf12cf1
ctl00_rwChildInfo_ClientState=
*/
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
	//echo http_build_query($postfields);
	$result2 = curl_exec($ch);
	file_put_contents('foo1.html', $result2);
	exit();
//	echo $result;
	
	
	//file_put_contents('foo.html', $result2);
	$url2 = 'adoptuskids.org/_app/child/searchpResults.aspx?pg=2';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url2);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_REFERER, $url);
	curl_setopt($ch, CURLOPT_USERAGENT, $agent);
	curl_setopt($ch, CURLOPT_VERBOSE, 1);
	curl_setopt($ch, CURLOPT_STDERR, $f);
	$result3 = curl_exec($ch);
	curl_close($ch);
	file_put_contents('foo.html', $result3);
	fclose($f);
	exit();

	//file_put_contents('foo.html', file_get_contents('https://prdweb.chfs.ky.gov/SNAP/index.aspx'));
	//exit();

	/*
	require_once(dirname(__DIR__) . '/public_html/php/simple_html_dom.php');
	
	$html = file_get_contents('foo.html');
	$tree = str_get_html($html);
	$link = $tree->find('form');
	foreach ($link as $l) {
		echo $l->innertext;
	}
*/
/*
	require_once(dirname(__DIR__) . '/sqllogin.inc');
	require_once(dirname(__DIR__) . '/public_html/php/get_sfconnection.php');
	$old_addresses = array('mlevi@uoregon.edu', 'woeihgo', 'wekhwo');
	$new_addresses = array();
	print_r($old_addresses);
	try {
		$conn = get_connection();
		$addr_list = implode("', '", $old_addresses);
		//echo "SELECT email, templateid FROM Optouts JOIN Optout_GroupTemplates ON Optouts.groupId=Optout_GroupTemplates.groupId WHERE Optout_GroupTemplates.templateId= ? AND email IN ('" . $addr_list . "')";
		if (!($stmt = $conn->prepare("SELECT email, templateid FROM Optouts JOIN Optout_GroupTemplates ON Optouts.groupId=Optout_GroupTemplates.groupId WHERE Optout_GroupTemplates.templateId= ? AND email IN ('" . $addr_list . "')"))) //optouts.groupId='104' OR 
			throw new Exception('Failed to prepare statement for Optout Checks');
		$sfid = '00X50000001SLvcEAG';
		//echo $addr_list . ' ' . $sfid;
		if (!($stmt->bind_param('s', $sfid)))
			throw new Exception('Failed to prepare statement for Optout Checks');
		$stmt->execute();
		$stmt->bind_result($emails, $tmp);
		while ($stmt->fetch())
		{
			$new_addresses[] = $emails;
		}
		$stmt->close();
		$conn->close();
	} catch (Exception $e)
	{
		$conn->close();
		throw $e;
	}
	print_r($new_addresses);
	print_r(array_diff($old_addresses, $new_addresses));
*/
	//$x = file_get_contents('http://kidsdb.ccyfstaging.org/Details.aspx?kidNum=7486');
	//file_put_contents('foo.txt', $x);
	
	/*
	$x = file_get_contents('foo.txt');
	$matches = array();
	preg_match_all('/{[^"]*"ID[^"]*":([^,]*),.*ListingCode[^"]*"[^"]*"([^\\\\]*)\\\\/sUm', $x, $matches);
	print_r($matches);
	*/
	
	//'{[^"]*"ID[^"]*":([^,]*),.*"ListingCode[^"]*"[^"]*"([^\\]*)\\[^}]*}'
	/*
	$x = file_get_contents('foo.txt');
	//print($x . "\r\n");
	if (mb_check_encoding($x, "UTF-8"))
		echo "correct\r\n";
	
	//$x = substr($x, 1, -1);
	$x = str_replace('\"', '"', str_replace('\\\\', '\\', $x));
	$x = str_replace('\"', '"', str_replace('\\\\', '\\', $x));
	
	printf(sprintf($x));
	echo "\r\n\r\n";
	var_dump(json_decode(sprintf(sprintf($x)), true));
	
	file_put_contents('foo2.txt', sprintf(sprintf($x)));
	
	//var_dump(json_decode(sprintf($x)));
	*/
	/*
	$x = preg_replace('/[\\\]+"/m', '"', $x);
	$x = preg_replace('/[\\n]+"/m', '', $x);
	$x = substr($x, 1, -1);
	print($x . "\r\n");
	var_dump(json_decode($x));
	*/
	//print_r($json);
	

/*
			$agent = "Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36";
			$channel = curl_init();
			curl_setopt($channel, CURLOPT_URL, 'http://www.mare.org/For-Families/View-Waiting-Children');
			curl_setopt($channel, CURLOPT_USERAGENT, $agent);
			curl_setopt($channel, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($channel, CURLOPT_MAXREDIRS, 5);
			curl_setopt($channel, CURLOPT_CONNECTTIMEOUT, 30); 
			curl_setopt($channel, CURLOPT_TIMEOUT, 30);
			curl_setopt($channel, CURLOPT_COOKIESESSION, true);
			curl_setopt($channel, CURLOPT_COOKIEJAR, 'cookie');
			$html = curl_exec($channel);
			curl_close($channel);
			file_put_contents('foo.html', $html);

*/
/*
	$html = file_get_contents('first_page_error.html');
	$matches = array();
	if (preg_match_all('/<a.*href="(.*)" title="View Child">.*<\/a>/im', $html, $matches))
		print_r($matches);
	else
		echo "None";
*/
/*
include_once('SpiderScraper.php');

$scraper = new Scraper();
$scraper->SetIdHeuristic('/Main_ID=(\d{4}[a-z])/im');
$scraper->name = 'IMTNGA';

$links = array('http://itsmyturnnow.dhs.ga.gov/WebForms/ChildInfo.aspx?MasterChild_ID=2528&Main_ID=5036A',
'http://itsmyturnnow.dhs.ga.gov/WebForms/ChildInfo.aspx?MasterChild_ID=2529&Main_ID=5037A',
'http://itsmyturnnow.dhs.ga.gov/WebForms/ChildInfo.aspx?MasterChild_ID=2530&Main_ID=5038A',
'http://itsmyturnnow.dhs.ga.gov/WebForms/ChildInfo.aspx?MasterChild_ID=2531&Main_ID=5039A',
'http://itsmyturnnow.dhs.ga.gov/WebForms/ChildInfo.aspx?MasterChild_ID=2528&Main_ID=5036A',
'http://itsmyturnnow.dhs.ga.gov/WebForms/ChildInfo.aspx?MasterChild_ID=2529&Main_ID=5037A',
'http://itsmyturnnow.dhs.ga.gov/WebForms/ChildInfo.aspx?MasterChild_ID=2530&Main_ID=5038A',
'http://itsmyturnnow.dhs.ga.gov/WebForms/ChildInfo.aspx?MasterChild_ID=2531&Main_ID=5039A',
'http://itsmyturnnow.dhs.ga.gov/WebForms/ChildInfo.aspx?MasterChild_ID=2528&Main_ID=5036A',
'http://itsmyturnnow.dhs.ga.gov/WebForms/ChildInfo.aspx?MasterChild_ID=2529&Main_ID=5037A',
'http://itsmyturnnow.dhs.ga.gov/WebForms/ChildInfo.aspx?MasterChild_ID=2530&Main_ID=5038A',
'http://itsmyturnnow.dhs.ga.gov/WebForms/ChildInfo.aspx?MasterChild_ID=2531&Main_ID=5039A',
'http://itsmyturnnow.dhs.ga.gov/WebForms/ChildInfo.aspx?MasterChild_ID=2528&Main_ID=5036A',
'http://itsmyturnnow.dhs.ga.gov/WebForms/ChildInfo.aspx?MasterChild_ID=2529&Main_ID=5037A',
'http://itsmyturnnow.dhs.ga.gov/WebForms/ChildInfo.aspx?MasterChild_ID=2530&Main_ID=5038A',
'http://itsmyturnnow.dhs.ga.gov/WebForms/ChildInfo.aspx?MasterChild_ID=2531&Main_ID=5039A',
'http://itsmyturnnow.dhs.ga.gov/WebForms/ChildInfo.aspx?MasterChild_ID=2528&Main_ID=5036A',
'http://itsmyturnnow.dhs.ga.gov/WebForms/ChildInfo.aspx?MasterChild_ID=2529&Main_ID=5037A',
'http://itsmyturnnow.dhs.ga.gov/WebForms/ChildInfo.aspx?MasterChild_ID=2530&Main_ID=5038A',
'http://itsmyturnnow.dhs.ga.gov/WebForms/ChildInfo.aspx?MasterChild_ID=2531&Main_ID=5039A',
'http://itsmyturnnow.dhs.ga.gov/WebForms/ChildInfo.aspx?MasterChild_ID=2528&Main_ID=5036A');

$result = $scraper->CompareListings($links);
print_r($result);
*/
?>