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

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
  
  // Subject translation tables
  $offsubjects = array(1 => "Ne","En","Sp","Pa","Wi","Nask1","Nask2","Bio","EcMo","Ak","Gs","CKV");
  $altsubjects = array("NAT4"=>6, "GES4"=>11, "AK4"=>10, "EC4"=>9, "BIO4"=>8, "SCH4"=>7, "WIS4"=>5, "PAP4"=>4, "SPA4"=>3, "ENG4"=>2, "NED4"=>1,
                       "Ne"=>1, "En"=>2, "Sp"=>3, "Wi"=>5, "Na"=>6, "Sk"=>7, "Bio"=>8, "Gs"=>11, "Ak"=>10, "Ec"=>9, "Pa"=>4, "NaSk 1"=>6, "NaSk 2"=>7, "EcMo"=>9,
					   "PA"=>4, "NE"=>1, "EN"=>2, "SP"=>3, "WI"=>5, "AK"=>10, "BI"=>8, "GS"=>11, "Na"=>6, "SK"=>7, "EC/MO"=>9,
					   "ne"=>1, "en"=>2, "sp"=>3, "pa"=>4, "wi"=>5, "na"=>6, "sk"=>7, "bi"=>8, "ec"=>9, "ak"=>10, "gs"=>11,
					   "NA"=>6, "EC"=>9, "EM & O"=>9, "Nask 1"=>6, "Nask 2"=>7,"CKV"=>12);

					   // Functions
  function get_initials($name)
  {
    $explstring = explode(" ",$name);
    $retstr = "";
    foreach($explstring AS $addstr)
      $retstr .= " ". substr($addstr,0,1);
    return $retstr;
  }
  
  function print_head()
  {
    global $schoolyear,$schoolname,$subjects,$subjects,$subtotal;
    echo("<p>Overzicht vakkenpakket keuze voor groep/klas ". $_SESSION['CurrentGroup']. "</p><p>School: ". $schoolname. "</p>");
    echo("<table class=studlist><TR><TH class=studhead>Naam van de leerling<BR>(in alfabetische volgorde)</TH>");
    foreach($subjects['shortname'] AS $sn)
    {
      echo("<TH class=subjhead>". substr($sn,0,strlen($sn) / 2). "<BR>". substr($sn,strlen($sn) / 2). "</TH>");
    }
    echo("</TR>");
  
    // Reset subject total counts
    foreach($subjects['mid'] AS $mid)
      $subtotal[$mid] = 0;
  }
  
  function print_foot()
  {
    global $subjects,$subtotal;
    // Show the total of students per subject
    echo("<TR><TD class=total>TOTAAL</TD>");
    foreach($subjects['mid'] AS $mid)
      echo("<TD class=subjind>". $subtotal[$mid]. "</TD>");
    echo("</TR>");
    // Show the subjects again
    echo("<TR><TD class=nolines>&nbsp</TD>");
    foreach($subjects['shortname'] AS $sn)
    {
      echo("<TH class=subjhead>". substr($sn,0,strlen($sn) / 2). "<BR>". substr($sn,strlen($sn) / 2). "</TH>");
    }
    echo("</TR></TABLE>");
    // Footing
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
  
  // Get the gid of the current group
  $mygidqr = SA_loadquery("SELECT gid FROM sgroup WHERE active=1 AND groupname='". $_SESSION['CurrentGroup']. "'");
  $mygid = $mygidqr['gid'][1];
  
  // Get a list of subjects applicable to the exam subjects
  $subjectsqr = SA_loadquery("SELECT shortname,subjectpackage.mid FROM subjectpackage LEFT JOIN subject USING(mid) GROUP BY mid ORDER BY mid");

  // Get subject based on defined standard using translation tables as defined at start of this file.
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
  
  //foreach($subjects['shortname'] AS $skey => $sjname)
    //echo("Subject ". $sjname. " has mid ". $subjects['mid'][$skey]. "<BR>");
  
  // Get the mid values for Na and Sk as these may need translation in the results
  $namidqr = SA_loadquery("SELECT mid FROM subject WHERE shortname='NA' OR shortname='Na' OR shortname='na'");
  if(isset($namidqr['mid'][1]))
    $namid = $namidqr['mid'][1];
  $skmidqr = SA_loadquery("SELECT mid FROM subject WHERE shortname='Sk' OR shortname='SK' OR shortname='sk'");
  if(isset($skmidqr['mid'][1]))
    $skmid = $skmidqr['mid'][1];
  
  $resultsqr = SA_loadquery("SELECT sid,mid,result FROM gradestore LEFT JOIN sgrouplink USING(sid) 
							 WHERE year='". $schoolyear. "' AND period=0 AND gid=". $mygid. " GROUP BY mid,sid");
  if(isset($resultsqr['result']))
    foreach($resultsqr['sid'] AS $rix => $sid)
	{
	  $mid = $resultsqr['mid'][$rix];
	  if(isset($namid) && $mid == $namid)
	    $mid = $subjects['mid'][6];
	  if(isset($skmid) && $mid == $skmid)
	    $mid = $subjects['mid'][7];
	  $results[$sid][$mid] = $resultsqr['result'][$rix];
	}
  
  
  // Get the data of the exam subject collections
  $packages = SA_loadquery("SELECT * FROM subjectpackage");
  
  // Get a list of students with the subject package and extra subject
  $squery = "SELECT lastname,firstname,sgrouplink.gid,packagename,extrasubject,extrasubject2,extrasubject3,student.sid FROM student LEFT JOIN sgrouplink USING(sid)";
  $squery .= " LEFT JOIN s_package USING(sid) ";
  $squery .= "WHERE gid=". $mygid. " ORDER BY lastname,firstname";
  $studs = SA_loadquery($squery);
  echo(mysql_error($userlink));
  SA_closeDB();

  // First part of the page
  echo("<html><head><title>Vakkenpakket keuzes</title></head><body link=blue vlink=blue onfload=\"window.print();setTimeout('window.close();',10000);\">");
  echo '<LINK rel="stylesheet" type="text/css" href="style_vakken_nieuw.css" title="style1">';
  
  print_head();

  $linecount = 0;
  
  // Student listing
  foreach($studs['gid'] AS $six => $gid)
  {
    if($linecount > 2900)
	{
      print_foot();
	  $linecount = 0;
	  print_head();
	}
    echo("<TR>");
	echo("<TD class=studname>". $studs['lastname'][$six]. ", ". $studs['firstname'][$six]. "</TD>");
	foreach($subjects['mid'] AS $mid)
	{
	  echo("<TD class=");
	  $hassubject = 0;
	  // check for subjects here!
	  foreach($packages['packagename'] AS $subix => $pname)
	  {
	    if($pname == $studs['packagename'][$six] && $mid == $packages['mid'][$subix])
		  $hassubject = 1;
	  }
	  if($mid == $studs['extrasubject'][$six] || $mid == $studs['extrasubject2'][$six] || $mid == $studs['extrasubject3'][$six])
	    $hassubject = 1;
	  if($hassubject == 0)
	    echo("subjindnc>". (isset($results[$studs['sid'][$six]][$mid]) ? $results[$studs['sid'][$six]][$mid] : "&nbsp"));
	  else
	  {
	    if(isset($results[$studs['sid'][$six]][$mid]))
		{
		  if($results[$studs['sid'][$six]][$mid] > 6)
		    echo("subjindgood>");
		  else if($results[$studs['sid'][$six]][$mid] == 6)
		    echo("subjind>");
		  else
		    echo("subjindbad>");
		  echo($results[$studs['sid'][$six]][$mid]);
		}
		else
		  echo("subjind>gc");
		$subtotal[$mid]++;
	  }
      echo("</TD>");
	}
	echo("</TR>");
	$linecount++;	
  }
  
  print_foot();
  // close the page
  echo("</html>");
?>
