<?php
  // This file is to get all ORCIDs associated with UT Austin and TTU, request by Shelley Barba for a project.
  // Was used to query API once and doesn't need to be ran again. Left it here just in case.

  header('Access-Control-Allow-Methods: POST');
  /*********** BEGIN GETTING ACCESS TOKEN FROM ORCID USER  ***********/
  $data = array(
	  'client_id' => '',
	  'client_secret' => '',
	  'grant_type' => 'authorization_code',
	  'code' => $_REQUEST["q"],
	  'redirect_uri' => '' 
  );
 
  $postString = http_build_query($data, '', '&');

  $curl = curl_init();
  
  curl_setopt_array($curl, array(
    CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_SSL_VERIFYPEER => false,
	  CURLOPT_URL => 'https://sandbox.orcid.org/oauth/token',
    CURLOPT_POST => true,
	  CURLOPT_POSTFIELDS => $postString,
	  CURLOPT_HTTPHEADER => 'accept: application/json'
  ));
  
  $response = curl_exec($curl);
  
  // Error handling (check PHP error log). Check php.net/manual/en/function.curl-errno.php for error code
  if(!$response) 
	  die('Error: "'.curl_error($curl).'" - Code: '.curl_errno($curl));
  else {
	$json = json_decode($response, true); // true is to return a json string, not an object (which is the default).
	$token = $json["access_token"];
	
  }
  // ORCID sandbox test account: USERNAME: orcidtest@mailinator.com, PW: orcidtest1
  curl_close($curl);
  /*********** END GETTING ACCESS TOKEN FROM ORCID USER  ***********/

  /*********** BEGIN QUERYING OF ORCIDs ASSOCIATED WITH UT ************/
  
  $headers = array (
	  'Method: GET',
	  'Content-type: application/vnd.orcid+json',
	  'Authorization type: Bearer',
	  'Access token: '. $token
  );
  
  $queryCurl = curl_init();
  
  curl_setopt_array($queryCurl, array (
    CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_HTTPHEADER => $headers,
	  CURLOPT_SSL_VERIFYPEER => false
  ));
  
  
  $arrayOfOrcidIDs = array();
  // increment through all records since ORCID API only shows up to 200 records for each query.
  for($start = 2600; $start < 2700; $start += 200){
	// Search by keywords. Returns 1172 results as of 11/15/2017;
	$query = "/search/?q=affiliation-org-name:(%22University%20of%20Texas%20at%20Austin%22)&start=" . $start . "&rows=200";
	
	curl_setopt($queryCurl, CURLOPT_URL, "https://pub.orcid.org/v2.0" . $query); // won't work with member API (api.orcid.org)- was told library had a paid membership with ORCID.
	
	$queryResponse = curl_exec($queryCurl);
	
	$jsonQuery = json_decode($queryResponse, true);
	$orcidNum = count($jsonQuery['result']);
	$i = 0;
	// put all ORCID ids in an array
    while($i < $orcidNum) {
      $orcidID = $jsonQuery['result'][$i]['orcid-identifier']['path'];
      array_push($arrayOfOrcidIDs, $orcidID);
      $i += 1;
    }
  }
  echo("Number of ORCID records returned: ".count($arrayOfOrcidIDs)."\n");
  
  $arrayOfURLs = array();
  $i = 0;
  
  while ($i < count($arrayOfOrcidIDs) ) {
    $query = "https://pub.orcid.org/v2.0/".$arrayOfOrcidIDs[$i]."/record";
	  array_push($arrayOfURLs, $query);
	  $i += 1;
  }
/*********** BEGIN 2nd QUERYING OF ORCID API W/ ORCID URLs TO GET SPECIFIC DETAILS ABOUT EACH ORCID ************/
  echo("Number of unique API URLs: ".count($arrayOfURLs)."\n");
  $curlArr = array();
  $numOfURLs = count($arrayOfURLs);
  
  // CAN'T USE count($numOfURLs) in loop statement.
  for($i = 0; $i < 250; $i++){
    $url = $arrayOfURLs[$i];
    $curlArr[$i] = curl_init();
    curl_setopt($curlArr[$i], CURLOPT_URL, $url);
    
    curl_setopt_array($curlArr[$i], array(
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER => $headers,
      CURLOPT_SSL_VERIFYPEER => false
    ));
  }
  
  $multiCurl = curl_multi_init();
  
  $i = 0;
  $results = array();
  
  curl_multi_add_handle($multiCurl, $curlArr[$i]);
  $active = NULL;

  do {
	  curl_multi_exec($multiCurl, $active);
  }
  while ($active > 0);

  $results[] = curl_multi_getcontent($curlArr[$i]);
  curl_multi_remove_handle($multiCurl, $curlArr[$i]);
  
  array_pop($results);
  
  //process the unique urls to get back api records
  foreach($curlArr as $curl) {
    $i++;
    curl_multi_add_handle($multiCurl, $curlArr[$i]);
    $active = NULL;
    
    do {
      curl_multi_exec($multiCurl, $active);
    }
    while ($active > 0);

    $results[] = curl_multi_getcontent($curl);
    curl_multi_remove_handle($multiCurl, $curl);
  }
  /*********** END 2ND QUERYING ***********/
  /*********** END QUERYING OF ORCIDs ASSOCIATED WITH TTU ************/
  
  echo("Number of records in results array: ".count($results)."\n");
  
  // DB connection info
  $serverName = "";
  $connectInfo = array("Database"=>"", "UID"=>"", "PWD"=>"");
  $conn = sqlsrv_connect($serverName, $connectInfo);
  if(!$conn) 
	error_log("Couldn't connect to ORCID database in curlUT.PHP file.\r\n");
  
  for($i = 0; $i < count($results); $i++) {
	  $jsonRecord = json_decode($results[$i], true);
	  // reset variables for each ORCID record
	  $empName = "";
	  $empStartYear = "";
	  $empEndYear = "";
	  $titleName = "";
	  $empDepartName = "";
    
	  //EMPLOYMENT INFO
	  // only want TTU info and some records have info related to other schools and jobs
	  for($j = 0; $j < 6; $j++){
	    $empName = $jsonRecord['activities-summary']['employments']['employment-summary'][$j]['organization']['name'];
      // if API returns Texas Tech as employer name at some index, get associated info at that index
      if(strpos($empName, 'University of Texas at Austin') !== false){
          $empStartYear = $jsonRecord['activities-summary']['employments']['employment-summary'][$j]['start-date']['year']['value'];
          $empEndYear = $jsonRecord['activities-summary']['employments']['employment-summary'][$j]['end-date']['year']['value']; // COULD BE EMPTY STRING
          $titleName = $jsonRecord['activities-summary']['employments']['employment-summary'][$j]['role-title'];
          $empDepartName = $jsonRecord['activities-summary']['employments']['employment-summary'][$j]['department-name'];
      }
	  }
    
	  if(empty($empDepartName) and empty($titleName) and empty($empStartYear) and empty($empEndYear)){
      $fullEmployInfo = "No info entered or non-UT employment";
      $empStatus = "Never employed by UT";
    }
	  elseif(empty($empStartYear) and empty($empEndYear)){
      $fullEmployInfo = (empty($empDepartName) ? 'No department name entered' : $empDepartName).'---'.(empty($titleName) ? 'No role title entered' : $titleName);
      $empStatus = "Dates not entered";
	  }
	  else {
      $fullEmployInfo = (empty($empDepartName) ? 'No department name entered' : $empDepartName).'---'.(empty($titleName) ? 'No role title entered' : $titleName).'---'.((empty($empStartYear) ? 'No start year entered' : $empStartYear)).'-'.(empty($empEndYear) ? 'Present' : $empEndYear);
      // some ORCID records have a single quote in them and messes up the SQL query below
      $fullEmployInfo = str_replace("'", "", $fullEmployInfo);
      
      if((strpos($fullEmployInfo, 'Present') !== false))
        $empStatus = 'Current Employee';
      else 
        $empStatus = 'Past Employee';
	  }
	  
	  // EDUCATION INFO
	  // same idea here as employment
	  $schoolName = "";
	  $startYear = "";
	  $endYear = "";
	  $degree = "";
	  $departName = "";
	  
	  for($j = 0; $j < 7; $j++){
	    $schoolName = $jsonRecord['activities-summary']['educations']['education-summary'][$j]['organization']['name'];
		
      if(strpos($schoolName, 'University of Texas at Austin') !== false){
        $startYear = $jsonRecord['activities-summary']['educations']['education-summary'][$j]['start-date']['year']['value'];
        $endYear = $jsonRecord['activities-summary']['educations']['education-summary'][$j]['end-date']['year']['value'];
        $degree = str_replace("'", "", $jsonRecord['activities-summary']['educations']['education-summary'][$j]['role-title']);
        $departName = $jsonRecord['activities-summary']['educations']['education-summary'][$j]['department-name'];
      }
	  }
	  
	  if(empty($departName) and empty($degree) and empty($startYear) and empty($endYear)){
      $fullEduInfo = "No info entered or non-UT education";
      $eduStatus = "Never attended UT";
	  }
	  elseif(empty($startYear) and empty($endYear)){
      $fullEduInfo = (empty($departName) ? 'No department name entered' : $departName).'---'.(empty($degree) ? 'No degree info entered' : $degree);
      $eduStatus = "Dates not entered";
	  }
	  else {
      $fullEduInfo = (empty($departName) ? 'No department name entered' : $departName).'---'.(empty($degree) ? 'No degree info entered' : $degree).'---'.(empty($startYear) ? 'No start year entered' : $startYear).'-'.(empty($endYear) ? 'Present' : $endYear);
      if((strpos($fullEduInfo, 'Present') !== false))
        $eduStatus = 'Current Student';
      else $eduStatus = 'Past Student';
	  }
	  
	  $firstName = $jsonRecord['person']['name']['given-names']['value'];
	  $lastName = $jsonRecord['person']['name']['family-name']['value'];
	  $fullName = $firstName." ".$lastName;
	  $fullName = str_replace("'", "", $fullName);
	  $fullEduInfo = str_replace("'", "", $fullEduInfo);
	  $fullEmployInfo = str_replace("'", "", $fullEmployInfo);
	  $id = $jsonRecord['orcid-identifier']['path'];
	  
	  $query = "INSERT INTO dbo.UT ([name], orcid_id, education, employment, education_status, employment_status)
				VALUES ('$fullName', '$id', '$fullEduInfo', '$fullEmployInfo', '$eduStatus', '$empStatus');";
				
	  $execute = sqlsrv_query($conn, $query);
	  if(!$execute){
      error_log("Couldn't run SQL query in pass ".$i."\n");
      error_log($query);
	  }
  }
  sqlsrv_close($conn);
  curl_multi_close($multiCurl);
  curl_close($queryCurl);
?>
