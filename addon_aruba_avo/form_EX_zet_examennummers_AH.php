<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014 Aim4me N.V.   (http://www.aim4me.com)	      |
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
  // Link input library with database
  inputclassbase::dbconnect($userlink);
  

// Clear existing exam numbers
  mysql_query("DELETE FROM s_exnr", $userlink);
  $KlasLijst = group::group_list("Exam%");
// Get the group ids for OS and SN.
// Change on 19 sep 2013: locations are Havo and VWO, and added check on overwrite exanr
//  $LocationGrps = inputclassbase::load_query("SELECT gid FROM sgroup WHERE groupname='O3' OR groupname='SN3' OR groupname='ExamVwo' ORDER BY groupname");
  $LocationGrps = inputclassbase::load_query("SELECT gid FROM sgroup WHERE active=1 AND groupname='ExamHavo' OR groupname='ExamVwo' ORDER BY groupname");
  if(isset($KlasLijst))
    foreach ($KlasLijst AS $group)
	{
  	  $Nr = 0;
	  foreach($LocationGrps['gid'] AS $Lgid)
	  {
	    //echo("Checking for location: ". $Lgid. "<BR>");
	    $LLlijst = student::student_list($group);
		foreach ($LLlijst AS $student)
		{
		  // See if this student fits this location
		  unset($sloc);
		  $sloc = $student->get_groups();
		  // Changed 19 sep 2013: only set exnr if none present
		  if(isset($sloc[$Lgid]) && $student->get_student_detail("s_exnr") == "")
		  {
			// Create exam number
			$nextexnr = str_pad(++$Nr,3,"0",STR_PAD_LEFT);
			  // Insert next number for this student
			mysql_query("INSERT INTO s_exnr (sid,data) VALUES(". $student->get_id(). ",'". $nextexnr. "')", $userlink);
		  }
		} // einde foreach student / leerling uit de klas
	  } //eind foreach location
	} // einde foreach group / klas
  // Switch view to the EX_peronalia_AH form
  header("Location: form_EX_personalia_AH.php");
	
?>
