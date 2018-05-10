<?php
/* vim: set expandtab tabstop=2 shiftwidth=2: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2016 Aim4me N.V.   (http://www.aim4me.com)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  require_once("inputlib/inputclasses.php");
	session_start();
  $login_qualify = 'ACT';
	require_once("schooladminfunctions.php");
  inputclassbase::dbconnect($userlink);
	
	if(isset($_POST['fieldid']))
	{
		echo("OK");

		exit;
	}
		
  require_once("student.php");
	// Which fields to show
	$fieldlist = array("groupname","lastname","firstname","s_ASGender");
	$collabels = array("groupname" => $_SESSION['dtext']['Group_Cap'],
											"lastname" => $_SESSION['dtext']['Lastname'],
											"firstname" => $_SESSION['dtext']['Firstname'],
											"s_ASGender" => "Geslacht");
  // And then we start giving out content
  echo ('<LINK rel="stylesheet" type="text/css" href="style_AVI_Overzicht.css" title="style1">');
  echo('<link rel="stylesheet" type="text/css" media="all" href="inputlib/datechooser.css">');

  // Get a list of groups
  if(isset($PrimaryGroupFilter))
    $groepfilter = $PrimaryGroupFilter;
  else
    $groepfilter = "__";
  // Get a list of the AVI levels
	// First get the mid for avi level
	$avimidqr = inputclassbase::load_query("SELECT mid FROM subject WHERE fullname LIKE '%avi%' AND fullname LIKE '%nivo%'");
	if(isset($avimidqr['mid']))
		$avimid = $avimidqr['mid'][0];
	else
	{
		echo("AVI Nivo niet als vak gedefinieerd.");
		exit;
	}
	// Get the current shoolyear
	$yearqr = inputclassbase::load_query("SELECT year FROM period");
	$schoolyear = $yearqr['year'][0];
	// Get the avi results with sid
	$avisidtableqr = inputclassbase::load_query("SELECT sid,CONCAT(' ',result) AS result,lastname,firstname,groupname,data FROM gradestore LEFT JOIN student USING(sid) LEFT JOIN s_ASGender USING(sid) LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) WHERE year='". $schoolyear. "' AND period > 0 AND mid=". $avimid. " AND groupname LIKE '". $groepfilter. "' ORDER BY groupname,lastname,firstname,period");
	foreach($avisidtableqr['sid'] AS $atix => $sid)
	{
		$sidavi[$sid] = $avisidtableqr['result'][$atix];
	}
	foreach($sidavi AS $avires)
	{
		if(!isset($avicnt[$avires]))
			$avicnt[$avires] = 1;
		else
			$avicnt[$avires]++;
	}

	ksort($avicnt);
	echo("<html><head><title>AVI overzicht</title></head><body link=blue vlink=blue>");
	foreach($avicnt AS $avin => $avic)
	{
		$llncount = 1;
		$llnseq = 1;
		unset($llntable);
		foreach($sidavi AS $sid => $avinl)
		{
			if($avinl == $avin)
				$llntable[$sid] = new student($sid);
		}
		print_header($llntable,$avin);
		foreach($llntable AS $stud)
		{
			if($llncount > 40)
			{
				$llncount=1;
				echo("</table>");
				print_header($llntable,$avin, $avic);
			}
			echo("<TR><TD>". $llnseq++. "</td><TD>". $stud->get_student_detail("*sgroup.groupname"). "</td><TD>". $stud->get_student_detail("*student.lastname"). "</td><TD>". $stud->get_student_detail("*student.firstname"). "</td><TD>". $stud->get_student_detail("s_ASGender"). "</td></tr>");			
			$llncount++;
		}
		echo("</table>");
	}
  echo("</html>");
	function print_header($llntable,$avin)
	{
		// Count the genders
		foreach($llntable AS $astud)
		{
			$studgen = $astud->get_student_detail("s_ASGender");
			if(isset($gentab[$studgen]))
				$gentab[$studgen]++;
			else
				$gentab[$studgen] = 1;
		}
		echo("<H1>AVI nivo: ". $avin. "</H1>");
		echo("<H3>Aantal lln: ". sizeof($llntable). ", per geslacht: ");
		foreach($gentab AS $agen => $gencnt)
		  echo($agen. ":". $gencnt. " ");
		echo("</H3>");
		echo("<table class=avitable><TR><TH>#</th><TH>Klas</th><TH>Achternaam</th><TH>Voornaam</th><TH>Geslacht</th></tr>");
	}
?>
