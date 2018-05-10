<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2015 Aim4me N.V.   (http://www.aim4me.info)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();
  //error_reporting(E_STRICT);
  require_once("inputlib/inputclasses.php");

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
 
  // Subject translation tables
  $offsubjects = array(1 => "Ne","En","Sp","Pa","Wi","Nask1","Nask2","Bio","EcMo","Ak","Gs","CKV","LO","REK");
  $altsubjects = array("NAT4"=>6, "GES4"=>11, "AK4"=>10, "EC4"=>9, "BIO4"=>8, "SCH4"=>7, "WIS4"=>5, "PAP4"=>4, "SPA4"=>3, "ENG4"=>2, "NED4"=>1,
                       "Ne"=>1, "En"=>2, "Sp"=>3, "Wi"=>5, "Na"=>6, "Sk"=>7, "Bio"=>8, "Gs"=>11, "Ak"=>10, "Ec"=>9, "Pa"=>4, "NaSk 1"=>6, "NaSk 2"=>7, "EcMo"=>9,
					   "PA"=>4, "NE"=>1, "EN"=>2, "SP"=>3, "WI"=>5, "AK"=>10, "BI"=>8, "GS"=>11, "Na"=>6, "SK"=>7, "EC/MO"=>9,
					   "ne"=>1, "en"=>2, "sp"=>3, "pa"=>4, "wi"=>5, "na"=>6, "sk"=>7, "bi"=>8, "ec"=>9, "ak"=>10, "gs"=>11,
					   "NA"=>6, "EC"=>9, "EM & O"=>9, "CKV"=>12, "Ckv"=>12, "LO"=>13,"REK"=>14);


  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];
  if(isset($HTTP_POST_VARS['NewGroup']) && SA_verifyGroupAccess($uid,$HTTP_POST_VARS['NewGroup']))
    $CurrentGroup = $HTTP_POST_VARS['NewGroup'];
  if(isset($HTTP_GET_VARS['NewGroup']) && SA_verifyGroupAccess($uid,$HTTP_GET_VARS['NewGroup']))
    $CurrentGroup = $HTTP_GET_VARS['NewGroup'];
  // Store the new group or future pages
  $_SESSION['CurrentGroup']=$CurrentGroup;
  
  $uid = intval($uid);
  
  // This function is based on tables that is created as needed. So now we create it if it does not exist.
  $sqlquery = "CREATE TABLE IF NOT EXISTS `ex45data` (
    `ex45id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `sid` INTEGER(11) DEFAULT NULL,
    `mid` INTEGER(11) DEFAULT NULL,
    `xstatus` INTEGER(11) DEFAULT NULL,
	`year` TEXT,
    `lastmodifiedat` TIMESTAMP(9) NOT NULL ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`ex45id`),
    UNIQUE KEY `sidmid` (`sid`, `mid`)
    ) ENGINE=InnoDB;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());
  
  $sqlquery = "CREATE TABLE IF NOT EXISTS `examresult` (
    `xid` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `sid` INTEGER(11) DEFAULT NULL,
    `xresult` TEXT DEFAULT NULL,
	`ckvres` INTEGER(1) DEFAULT 0,
	`year` TEXT,
    `lastmodifiedat` TIMESTAMP(9) NOT NULL ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`xid`)
    ) ENGINE=InnoDB;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());
  
  $hasckv = SA_loadquery("SHOW COLUMNS FROM examresult LIKE 'ckvres'");
  if(!isset($hasckv['Field']))
  { // Need to add the ckvres column to the exam results
    mysql_query("ALTER TABLE examresult ADD ckvres INTEGER(1) DEFAULT 0", $userlink);
    echo(mysql_error());
  }
  
  // We need to get the year for entry!
  $curyearqr = SA_loadquery("SELECT year FROM period WHERE id=3");
  $curyear = $curyearqr['year'][1];
    
  // The states are predefined, here is the query to get that list (actually no DB access but the library works that way...)
  $statquery = "SELECT 0 AS id, '' AS tekst UNION SELECT 1 AS id,'Her' AS tekst UNION SELECT 2,'Afw. Mo' UNION SELECT 3,'Afw. SO' UNION SELECT 4,'Afw. Ex' 
                UNION SELECT 5,'Vrijst. 7' UNION SELECT 6,'Vrijst. 8' UNION SELECT 7,'Vrijst. 9' UNION SELECT 8,'Vrijst. 10' ";
  
  // Get a list of all applicable students
  $students = SA_loadquery("SELECT CONCAT(lastname,', ',firstname) AS name,sid,packagename,extrasubject,extrasubject2,extrasubject3,s_exnr.data AS exnr FROM student LEFT JOIN s_package USING(sid) LEFT JOIN s_exnr USING(sid) WHERE s_exnr.data > 0 GROUP BY sid ORDER BY name");

  // Get a list of applicable subjects
  $subjectsqr = SA_loadquery("SELECT shortname,subjectpackage.mid FROM subjectpackage LEFT JOIN subject USING(mid) GROUP BY mid ORDER BY mid");
  foreach($offsubjects AS $osix => $sjname)
  {
    foreach($subjectsqr['shortname'] AS $sbix => $subsn)
	{
	  if(isset($altsubjects[$subsn]) && $altsubjects[$subsn] == $osix)
	  {
	    $subjects['shortname'][$osix] = $sjname;
		$subjects['mid'][$osix] = $subjectsqr['mid'][$sbix];
	  }
	}
  }
 
  // Get the data of the exam subject collections
  $packages = SA_loadquery("SELECT * FROM subjectpackage");

  // See if we need to extract results from previous year
  if(isset($_GET['extractfromprevyear']))
  { // Need to extract results from previous year!
    $years = SA_loadquery("SELECT DISTINCT year FROM gradestore ORDER BY year DESC");
	if(isset($years['year'][2]))
	{ // The previous year exists (very important...)
	  $prevyear = $years['year'][2];
	  $pyrq = "SELECT sid,mid,result FROM gradestore LEFT JOIN sgrouplink USING (sid) LEFT JOIN sgroup USING(gid) ";
	  $pyrq .= "WHERE active=1 AND year='". $prevyear. "' AND (period=0 OR period=3) AND groupname LIKE '4%' GROUP BY sid,mid";
	  $pygrades = SA_loadquery($pyrq);
	  foreach($pygrades['sid'] AS $pyrix => $pysid)
	  {
	    if($pygrades['period'][$pyrix] == 0)
		{ // Year result
	      $pyres[$pysid][$pygrades['mid'][$pyrix]] = $pygrades['result'][$pyrix];
		  if(isset($pyrescount[$pysid]))
		    $pyrescount[$pysid]++;
		  else
		    $pyrescount[$pysid] = 1;
		}
		else // CE result
		  $pyxres[$pysid][$pygrades['mid'][$pyrix]] = $pygrades['result'][$pyrix];
	  }
	}
  }
  
  // Process results from previous year
  // Find out which mid belongs to CKV
  $ckvmidqr = SA_loadquery("SELECT mid FROM subject WHERE shortname LIKE 'ckv%' OR shortname LIKE 'CKV%' OR shortname LIKE 'Ckv%'");
  if(isset($ckvmidqr['mid'][1]))
    $ckvmid = $ckvmidqr['mid'][1];
  else
    $ckvmid = 0;
  foreach($students['sid'] AS $six => $sid)
  {
	foreach($subjects['mid'] AS $mid)
	{
	  $hassubject = 0;
	  // check for subjects here!
	  foreach($packages['packagename'] AS $subix => $pname)
	  {
	    if($pname == $students['packagename'][$six] && $mid == $packages['mid'][$subix])
		  $hassubject = 1;
	  }
	  if($mid == $students['extrasubject'][$six] || $mid == $students['extrasubject2'][$six] || $mid == $students['extrasubject3'][$six])
	    $hassubject = 1;
	  if($hassubject == 1)
	  { // First, see if we need to put a result here from the previous year as vrijstelling
		if(isset($_GET['extractfromprevyear']) && isset($pyres[$sid][$mid]))
		{
		  if($pyres[$sid][$mid] > 6.5 && $pyrescount[$sid] < 10 && isset($pyxres[$sid][$mid]) && $pyxres[$sid][$mid] >= 6.0)
		  {
		    $xstat = ceil(round($pyres[$sid][$mid]) - 2);
		    mysql_query("REPLACE INTO ex45data (sid,mid,xstatus,year) VALUES(". $sid. ",". $mid. ",". $xstat. ",'". $curyear. "')", $userlink);
		  }
		}
	  }
	}
	if(isset($pyres[$sid][$ckvmid]) && $pyres[$sid][$ckvmid] > 5.5)
	{
      mysql_query("REPLACE INTO examresult (sid,year,ckvres) VALUES(". $sid. ",'". $curyear. "',1)", $userlink);	  
	}
  }  
  // Get all exisiting records in an array
  $ex45data = SA_loadquery("SELECT * FROM ex45data WHERE year='". $curyear. "'");
  // Convert this to a more convenient array type
  if(isset($ex45data))
    foreach($ex45data['ex45id'] AS $xix => $xid)
	  $ex45s[$ex45data['sid'][$xix]][$ex45data['mid'][$xix]] = $xid;

  $exres = SA_loadquery("SELECT * FROM examresult WHERE year='". $curyear. "'");
  // Convert this to a more convenient array type
  if(isset($exres))
    foreach($exres['xid'] AS $xix => $xid)
	  $exr[$exres['sid'][$xix]] = $xid; 
  
  // First part of the page
  echo("<html><head><title>Examen status invoer</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>Status examens</font><p>");
  echo("<a href=# onClick='window.close();'>" . $dtext['back_teach_page'] . "</a><br>");

  // Create the heading row for the table
  echo("<table border=1 cellpadding=0>");
  $hdr="<tr><td>Exnr.</td><td><center>Studentnaam</td>";
  foreach($subjects['shortname'] AS $sname)
  {
    $hdr .= "<td><center>". $sname. "</td>";
  }
  $hdr .= "<td><center>CKV</center></td><td><center>Uitslag</center></td></tr>";
  echo($hdr);

  // Create a row in the table for each student
  $negix = 0;
  foreach($students['sid'] AS $six => $sid)
  {
    // Show header every 10 students
	if($students['exnr'][$six] % 10 == 0)
	  echo($hdr);
    echo("<tr><td>". $students['exnr'][$six]. "</td><td>". $students['name'][$six]. "</td>");
	
	foreach($subjects['mid'] AS $mid)
	{
	  echo("<TD>");
	  $hassubject = 0;
	  // check for subjects here!
	  foreach($packages['packagename'] AS $subix => $pname)
	  {
	    if($pname == $students['packagename'][$six] && $mid == $packages['mid'][$subix])
		  $hassubject = 1;
	  }
	  if($mid == $students['extrasubject'][$six] || $mid == $students['extrasubject2'][$six] || $mid == $students['extrasubject3'][$six])
	    $hassubject = 1;
	  if($hassubject == 0)
	    echo("&nbsp");
	  else
	  { // Create an entry field for the status and show it
	    if(isset($ex45s[$sid][$mid]))
		{
          $statfield = new inputclass_listfield("statfield". $ex45s[$sid][$mid],$statquery,$userlink,"xstatus","ex45data",$ex45s[$sid][$mid],"ex45id","","ex45procpage.php");
		}
		else
		{
          $statfield = new inputclass_listfield("statfield". (--$negix),$statquery,$userlink,"xstatus","ex45data",$negix,"ex45id","","ex45procpage.php");
		  $statfield->set_extrafield("mid", $mid);
		  $statfield->set_extrafield("sid", $sid);
		  $statfield->set_extrafield("year", $curyear);
		}
		$statfield->echo_html();	    
	  }
      echo("</TD>");
	}
	// add a column for the CKV result entry
	echo("<td>");
	if(isset($exr[$sid]))
	  $ckvfield = new inputclass_checkbox("ckvfield". $sid,NULL,$userlink,"ckvres","examresult",$exr[$sid],"xid","","ex45procpage.php");
	else
	{
	  $ckvfield = new inputclass_checkbox("ckvfield". (--$negix),NULL,$userlink,"ckvres","examresult",$negix,"xid","","ex45procpage.php");
	  $ckvfield->set_extrafield("sid", $sid);
	  $ckvfield->set_extrafield("year", $curyear);
	}
	$ckvfield->echo_html();
	echo("</td>");
	// add a column for the manual exam result entry
	echo("<td>");
	if(isset($exr[$sid]))
	  $resfield = new inputclass_textarea("resfield". $sid,"15,*",$userlink,"xresult","examresult",$exr[$sid],"xid","","ex45procpage.php");
	else
	{
	  $resfield = new inputclass_textarea("resfield". ($negix),"15,*",$userlink,"xresult","examresult",$negix,"xid","","ex45procpage.php");
	  $resfield->set_extrafield("sid", $sid);
	  $resfield->set_extrafield("year", $curyear);
	}
	$resfield->echo_html();
	echo("</td></tr>");
  }
 echo("</table>");
  echo '<a href=# onClick="window.close();">';
  echo $dtext['back_teach_page'];
  echo "</a><BR><BR><a href='" .$_SERVER['PHP_SELF']. "?extractfromprevyear=1'>Haal vrijstellingen en CKV resultaat uit resultaten vorig jaar</a>";
 
  // close the page
  echo("</html>");
?>
