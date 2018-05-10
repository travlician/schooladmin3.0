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
  // Link input library with database
  inputclassbase::dbconnect($userlink);
  $mntrans = array("01"=>"januari","02"=>"februari","03"=>"maart","04"=>"april","05"=>"mei","06"=>"juni",
                   "07"=>"juli","08"=>"augustus","09"=>"september","10"=>"oktober","11"=>"november","12"=>"december");
  $llnpp = 999999;
  
  // Get the year
  $schoolyear = SA_loadquery("SELECT year FROM period");
  $schoolyear = $schoolyear['year'][1];
  
  $KlasLijst = group::group_list("Exam%");
  if(isset($KlasLijst))
  {
	// Generate basic dump extension
	$mime_type = (SA_USR_BROWSER_AGENT == 'IE' || SA_USR_BROWSER_AGENT == 'OPERA')
					   ? 'application/octetstream'
					   : 'application/octet-stream';
		
	/**
	 * Send headers depending on whether the user chose to download a dump file
	 * or not
	 */
	// Download
	header('Content-Type: "' . $mime_type. '; charset=UTF-8"');
	header('Expires: ' . gmdate('D, d M Y H:i:s', time()-36000) . ' GMT');
	// lem9 & loic1: IE need specific headers
	if (SA_USR_BROWSER_AGENT == 'IE') 
	{
	   header('Content-Disposition: inline; filename="EX_registratie.csv"');
	   header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	   header('Pragma: public');
	} 
	else 
	{
	  header('Content-Disposition: attachment; filename="EX_registratie.csv"');
	  header('Pragma: no-cache');
	}
	echo pack("CCC",0xef,0xbb,0xbf); // Indicate UTF-8 charset
	echo("sep=,\r\n"); // Tell Excel to use , as separator
    foreach ($KlasLijst AS $group)
	{
      // 	Tabel met de gewenste gegevens van de leerling uit de betreffende klas:
	
		$Nr = 0;
	    $LLlijst = student::student_list($group);
		foreach ($LLlijst AS $student)
		{			
			echo("\"". $student->get_lastname(). "\",\"". $student->get_firstname(). "\",\"". $student->get_student_detail("s_ASGender"). "\"");
			$bdate = $student->get_student_detail("s_ASBirthDate");
			if(isset($mntrans[substr($bdate,3,2)]))
			  $bdate = substr($bdate,0,2). " ". $mntrans[substr($bdate,3,2)]. " ". substr($bdate,6);
			echo(",\" ". $bdate. "\",\"". $student->get_student_detail("s_ASBirthCountry"). "\"\r\n");

		} // einde foreach student / leerling uit de klas
	} // einde foreach group / klas
  }
?>
