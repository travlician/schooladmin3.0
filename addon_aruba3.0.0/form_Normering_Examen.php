<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014 Aim4me N.V.   (http://www.aim4me.info)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
  require_once("schooladmingradecalc.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  
  // We need to get the year for entry!
  $curyears = SA_loadquery("SELECT year,id FROM period ORDER BY id DESC");
  $curyear = $curyears['year'][1];
  
  if(isset($_POST['subjectmid']))
  {
    $_SESSION['subjectmid'] = $_POST['subjectmid'];
	unset($_SESSION['testname']);
	unset($_SESSION['testname2']);
  }
  else if(isset($_POST['testname']))
  {
    $_SESSION['testname'] = $_POST['testname'];
	unset($_SESSION['testname2']);
  }
  else if(isset($_POST['testname2']))
  {
    $_SESSION['testname2'] = $_POST['testname2'];
  }
  

  // First part of the page
  echo("<html><head><title>Normering Examen</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>Examen normering</font><p>");
  echo("<a href=# onClick='window.close();'>" . $dtext['back_teach_page'] . "</a><br>");
  
  if(isset($_POST['CALC']))
  { // Calculation requested, see if data entered is ok
    if($_POST['L'] != '' && $_POST['N'] != '' && $_POST['M'] == '' && $_POST['C'] == '')
	{ // Lenth and Nterm style entry
	  $_POST['N'] = str_replace(",",".",$_POST['N']); // Replace , by . as it's a float value
	  if($_POST['N'] == ((string) (0.0 + $_POST['N'])) && $_POST['L'] == ( (string) (0 + $_POST['L'])))
	  { // Entry values are valid
	    echo("<p>N=". $_POST['N']. ", L=". $_POST['L']. "</p>");
		echo(NtermConvert());
	  }
	  else
	   echo("<p style='color: red'>Normeringscriteria (L en N) zijn niet goed ingevuld!</p>");
	}
    else if($_POST['M'] != '' && $_POST['C'] != '' && $_POST['L'] == '' && $_POST['N'] == '')
	{ // Scale and cesuur style entry
	  $cesvals = explode("/",$_POST['C']);
	  if(count($cesvals) != 2 || $cesvals[0] != ((string) (0.0 + $cesvals[0])) || $cesvals[1] != ( (string) (0 + $cesvals[1])))
	  { // Entry of cesuur is invalid
	    echo("<p style='color: red'>Cesuur is niet goed ingevuld, moet bestaan uit twee cijfers gescheiden door een / !</p>");
	  }
	  else
	  {
	    if($_POST['M'] == ((string) (0.0 + $_POST['M'])))
		{ // Entry is OK
		  echo("<p>M=". $_POST['M']. ", C=". $_POST['C']. "</p>");
		  echo(CesuurConvert());
	    }
		else
	      echo("<p style='color: red'>Max. Score is niet goed ingevuld!</p>");
	  }	  
	}
	else
	  echo("<p style='color: red'>Normeringscriteria zijn niet goed ingevuld!</p>");
  }

  // Show for which subject we are going to process and allow changing the subject
  echo("<form method=post action=form_Normering_Examen.php name=subjfrm id=subjfrm>Vak: <select name=subjectmid onChange='document.subjfrm.submit();'>");
  // Allow desection of the subject
  echo("<OPTION VALUE=0> </OPTION>");
  $subjects = SA_loadquery("SELECT mid,fullname FROM subject ORDER BY fullname");
  if(isset($subjects['mid']))
    foreach($subjects['mid'] AS $sbix => $mid)
	{
	  echo("<OPTION VALUE=". $mid. ((isset($_SESSION['subjectmid']) && $_SESSION['subjectmid'] == $mid) ? " selected" : ""). ">");
	  echo($subjects['fullname'][$sbix]. "</OPTION>");
	}
  echo("</select></form>");
  // Now if a subject is chosen, get the test definitions that refer to test that have no weight for this subject
  if(isset($_SESSION['subjectmid']) && $_SESSION['subjectmid'] != 0)
  {
    $testnames = SA_loadquery("SELECT short_desc, description, GROUP_CONCAT(groupname) AS grps FROM testdef LEFT JOIN `class` USING(cid) 
	                           LEFT JOIN sgroup USING(gid) LEFT JOIN reportcalc ON(reportcalc.testtype = testdef.type) 
							   WHERE active=1 AND `class`.mid=". $_SESSION['subjectmid']. " AND year='". $curyear. "' AND period=3
							   AND weight=0.0 GROUP BY short_desc");
	if(isset($testnames['short_desc']))
	{
      echo("<form method=post action=form_Normering_Examen.php name=testfrm id=testfrm>Toetsdefinitie in het PLT voor de behaalde examenpunten: <select name=testname onChange='document.testfrm.submit();'>");
      // Allow deselection of the test
      echo("<OPTION VALUE=''> </OPTION>");
      foreach($testnames['short_desc'] AS $tix => $tsh)
	  {
	    echo("<OPTION VALUE='". $tsh. "'". ((isset($_SESSION['testname']) && $_SESSION['testname'] == $tsh) ? " selected" : ""). ">");
	    echo($testnames['description'][$tix]. " (". $testnames['grps'][$tix]. ")</OPTION>");
	  }
      echo("</select></form>");
	  if(isset($_SESSION['testname']) && $_SESSION['testname'] != '')
	  {
	    // Get the groups that match the test points entry
		$grpq = "SELECT gid FROM testdef LEFT JOIN `class` USING(cid) 
	                           LEFT JOIN reportcalc ON(reportcalc.testtype = testdef.type) 
							   WHERE `class`.mid=". $_SESSION['subjectmid']. " AND year='". $curyear. "' AND period=3
							   AND weight=0.0 AND short_desc = '". $_SESSION['testname']. "'";
		$grpqr = SA_loadquery($grpq);
		$grpfilt = " AND (gid=". implode(" OR gid=", $grpqr['gid']). ")";
		// Get the candidate test definitions that match the selectted points definition
	    $tn2q = "SELECT short_desc, description, GROUP_CONCAT(groupname) AS grps FROM testdef LEFT JOIN `class` USING(cid) 
								   LEFT JOIN sgroup USING(gid) LEFT JOIN reportcalc ON(reportcalc.testtype = testdef.type) 
								   WHERE active=1 AND `class`.mid=". $_SESSION['subjectmid']. " AND year='". $curyear. "' AND period=3
								   AND weight>0.0 ". $grpfilt. " GROUP BY short_desc";
		$testnames2 = SA_loadquery($tn2q);
		if(isset($testnames2['short_desc']))
		{
		  echo("<form method=post action=form_Normering_Examen.php name=testfrm2 id=testfrm2>Toetsdefinitie in het PLT voor het te berekenen examencijfer: <select name=testname2 onChange='document.testfrm2.submit();'>");
		  // Allow deselection of the test
		  echo("<OPTION VALUE=''> </OPTION>");
		  foreach($testnames2['short_desc'] AS $tix => $tsh)
		  {
			echo("<OPTION VALUE='". $tsh. "'". ((isset($_SESSION['testname2']) && $_SESSION['testname2'] == $tsh) ? " selected" : ""). ">");
			echo($testnames2['description'][$tix]. " (". $testnames2['grps'][$tix]. ")</OPTION>");
		  }
		  echo("</select></form>");
		  // Allow entry of factors for conversion
		  if(isset($_SESSION['testname2']) && $_SESSION['testname2'] != '')
		  {
		    echo("<form method=post action=form_Normering_Examen.php>");
			echo("<P>Vul <B>OF</b> de \"Lengte scoreschaal (L)\" en de \"Normeringsterm (N)\" in <B>OF</b> de \"Max. score\" en \"Cesuur\".</p>");
			echo("Lengte scoreschaal (L): <INPUT TYPE=TEXT NAME=L SIZE=3> Normeringsterm (N): <INPUT TYPE=TEXT NAME=N SIZE=3><BR>");
			echo("Max. score: <INPUT TYPE=TEXT NAME=M SIZE=3 READONLY> Cesuur: <INPUT TYPE=TEXT NAME=C SIZE=7 READONLY> (Cesuur is nog niet beschikbaar)<BR>");
			echo("<BR><INPUT TYPE=SUBMIT NAME='CALC' VALUE='CONVERTEER EXAMENPUNTEN NAAR EXAMENCIJFERS'><br><br>");
			
		  }
	    }
	    else
	      echo("Geen toetsdefinities gevonden voor het geselecteerde vak met dezelfde groep en weginigsfactor > 0!<BR>");
	  }
	}
	else
	  echo("Geen toetsdefinities gevonden met wegingsfactor 0 voor het geselecteerde vak!<BR>");
  }
  echo '<a href=# onClick="window.close();">';
  echo $dtext['back_teach_page'];
  echo '</a>';
 
  // close the page
  echo("</html>");
  
  function NtermConvert()
  {
    global $userlink;
    $cdat = ListCoreData();
	if(isset($cdat['sid']))
	{
	  $rstr = "<P style='color: green;'>". count($cdat['sid']). " resultaten geconverteerd.";
	  foreach($cdat['sid'] AS $cix => $sid)
	  {
	    //$rstr .= "<BR>sid=". $sid. ", pts=". $cdat['pts'][$cix]. ", dest.tdid=". $cdat['tdid'][$cix];
		$res = Nterm($cdat['pts'][$cix], $_POST['L'], $_POST['N']);
		mysql_query("REPLACE INTO testresult (sid,result,tdid) VALUES(". $sid. ",". $res. ",". $cdat['tdid'][$cix]. ")", $userlink);
		SA_calcGrades($cdat['sid'][$cix],$cdat['cid'][$cix],3);
	  }
	  $rstr .= "</p>";
	  unset($_SESSION['testname']);
	  unset($_SESSION['testname2']);
	  return($rstr);
	}
	else
      return("<p style='color: red;'>Geen resultaten gevonden voor conversie!</p>");
  }
  function CesuurConvert()
  {
    global $userlink;
	// Cesuur comes in two integers separated by /. The first makes no sense at all, we only use the second one.
	$ces = explode("/",$_POST['C']);
	$ces = $ces[1];
    $cdat = ListCoreData();
	if(isset($cdat['sid']))
	{
	  $rstr = "<P style='color: green;'>". count($cdat['sid']). " resultaten geconverteerd.";
	  foreach($cdat['sid'] AS $cix => $sid)
	  {
		$res = Cesuur($cdat['pts'][$cix], $_POST['M'], $ces);
		mysql_query("REPLACE INTO testresult (sid,result,tdid) VALUES(". $sid. ",". $res. ",". $cdat['tdid'][$cix]. ")", $userlink);
		SA_calcGrades($cdat['sid'][$cix],$cdat['cid'][$cix],3);
	  }
	  $rstr .= "</p>";
	  unset($_SESSION['testname']);
	  unset($_SESSION['testname2']);
	  return($rstr);
	}
	else
      return("<p style='color: red;'>Geen resultaten gevonden voor conversie!</p>");
  }
  function ListCoreData()
  {
    global $curyear;
    $ix = 0;
	// Get the source tdid with the gid
    $sourcetdids = SA_loadquery("SELECT tdid,gid,cid FROM testdef LEFT JOIN `class` USING(cid) 
	                           LEFT JOIN reportcalc ON(reportcalc.testtype = testdef.type) 
							   WHERE `class`.mid=". $_SESSION['subjectmid']. " AND year='". $curyear. "' AND period=3
							   AND weight=0.0 AND short_desc='". $_SESSION['testname']. "'");
	foreach($sourcetdids['tdid'] AS $tix => $tdid)
	{
	  // Get the destination tdid
      $dtdid = SA_loadquery("SELECT tdid FROM testdef LEFT JOIN `class` USING(cid) 
	                           LEFT JOIN reportcalc ON(reportcalc.testtype = testdef.type) 
							   WHERE `class`.mid=". $_SESSION['subjectmid']. " AND year='". $curyear. "' AND period=3
							   AND weight>0.0 AND short_desc='". $_SESSION['testname2']. "' AND gid=". $sourcetdids['gid'][$tix]);
	  if(isset($dtdid['tdid'][1]))
	  { // Destination found, now get the student with points
	    $pts = SA_loadquery("SELECT sid,result FROM testresult WHERE result IS NOT NULL AND tdid=". $tdid);
		if(isset($pts['sid']))
		  foreach($pts['sid'] AS $pix => $sid)
		  {
		    $rval['sid'][$ix] = $sid;
			$rval['pts'][$ix] = $pts['result'][$pix];
			$rval['tdid'][$ix] = $dtdid['tdid'][1];
			$rval['cid'][$ix] = $sourcetdids['cid'][$tix];
			$ix++;
		  }
	  }
	}
	if(isset($rval))
	  return($rval);
	else
	  return NULL;    
  }
  
  function Nterm($i, $s, $n)
  {
    $res = round($n + (9.0 * ($i/$s)),1);
    if($n > 1.0)
    {
      $res1 = round(1.0 + $i * (9.0 / $s) * 2.0,1);
	  //$res10 = round(10.0 – (($s – $i) * (9.0 / $s)) * 0.5,1);
	  $res10 = round(10.0 - ($s-$i) * (9.0 / $s) * 0.5,1);
	  if($res > $res1)
	    $res = $res1;
	  if($res > $res10)
	    $res = $res10;
    }
    else
    {
      $res1 = round(1.0 + $i * (9.0 / $s) * 0.5,1);
	  $res10 = round(10.0 - ($s-$i) * (9.0 / $s) * 2.0,1);
	  if($res < $res1)
	    $res = $res1;
	  if($res < $res10)
	    $res = $res10;
    }
	return($res);
  }
  
  function Cesuur($pts, $max, $ces)
  {
    if($pts >= $ces)
	{
	  return(round(5.5 + (4.5 * (($pts - $ces) / ($max - $ces))),1));
	}
	else
	{
	  return(round(1.0 + (4.5 * ($pts/$ces)),1));
	}
  }
?>
