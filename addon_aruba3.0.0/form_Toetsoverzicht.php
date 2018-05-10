<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2013 Aim4me N.V.   (http://www.aim4me.com)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
  require_once("student.php");
  require_once("group.php");
  // Avoid sorting on students messed up
  unset($_SESSION['ssortertable']);
  inputclassbase::DBconnect($userlink);
  
  // Functions
  function get_initials($name)
  {
    $explstring = explode(" ",$name);
    $retstr = "";
    foreach($explstring AS $addstr)
      $retstr .= " ". substr($addstr,0,1);
    return $retstr;
  }
    
  $uid = $_SESSION['uid'];
  $uid = intval($uid);

  // Get the school name
  $schoolname = $announcement;
  $schoolname = str_replace("!","",$schoolname);
  $schoolname = str_replace("Welkom bij ","",$schoolname);
  $schoolname = str_replace("het ","",$schoolname);
  $schoolname = str_replace("de ","",$schoolname);
  
  // Get the year
  $schoolyear = SA_loadquery("SELECT year FROM period");
  $schoolyear = $schoolyear['year'][1];
  
  // Get a list of student objects
  $studs = student::student_list();
  
  // Get the applicabe testdefinitions
  $mygrp = new group();
  $mygrp->load_current();
  $tdq = "SELECT `date`,sid,tdid,shortname,description FROM testdef LEFT JOIN class USING(cid) LEFT JOIN subject USING(mid)";
  $tdq .= " LEFT JOIN sgrouplink USING(gid) LEFT JOIN (SELECT sid FROM sgrouplink WHERE gid=". $mygrp->get_id(). " GROUP BY sid) AS t1 USING(sid)";
  $tdq .= " WHERE t1.sid IS NOT NULL AND year='". $schoolyear. "' AND `date` > CURDATE() AND testdef.type <> '' ORDER BY `date`,tdid";
  $tdqr = SA_loadquery($tdq,$userlink);
  if(isset($tdqr['date']))
    foreach($tdqr['date'] AS $tix => $tdate)
	{ // Convert data to a structure to be used during creating of display info
	  $testid[$tdate][$tdqr['sid'][$tix]][$tdqr['tdid'][$tix]] = $tdqr['shortname'][$tix];
	  $testdesc[$tdqr['tdid'][$tix]] = $tdqr['description'][$tix];
	}

  if(isset($studs))
  {
    // First part of the page
    echo("<html><head><title>Toetsoverzicht</title></head><body link=blue vlink=blue>");
    echo '<LINK rel="stylesheet" type="text/css" href="layout.css" title="style1">';
	echo("<div class=Contents><H1>Toetsoverzicht</H1><TABLE><TR><TH>Naam</TH>");
	if(isset($testid))
	foreach($testid AS $tdate => $dummy)
	{ // Show heading dates
	  echo("<TH>". substr($tdate,8,2). substr($tdate,4,3). "</TH>");
	}
	echo("</TR>");
	// Now a row for each student
	foreach($studs AS $sid => $sobj)
	{
	  echo("<TR><TD>". $sobj->get_name(). "</TD>");
	  if(isset($testid))
	  foreach($testid AS $tdate => $dummy)
	  { // Show for each date which tests are defined
	    echo("<TD class=calendar>");
	    if(isset($testid[$tdate][$sid]))
		{
		  $sstr = "";
		  $pwcnt = 0;
		  foreach($testid[$tdate][$sid] AS $tdid => $subj)
		  {
		    $sstr .= "+<a href=# title='". $testdesc[$tdid]. "'";
			if($pwcnt++ > 1)
			  $sstr .= " style='color: red'";
			$sstr .= ">". $subj. "</a>";
		  }
		  $sstr = substr($sstr,1);
		  echo($sstr);
		}
		else
		  echo("&nbsp;");
		echo("</TD>");
	  }
	  echo("</TR>");
	}
    echo("</TABLE></DIV>");
  } // Endif students defined
  
  // close the page
  echo("</html>");
?>
