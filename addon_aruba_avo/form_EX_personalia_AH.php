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
  echo ('<LINK rel="stylesheet" type="text/css" href="style_EX_personalia.css" title="style1">');
  
  // Get the school name
  $schoolname = $announcement;
  $schoolname = str_replace("!","",$schoolname);
  $schoolname = str_replace("Welkom bij ","",$schoolname);
//  $schoolname = str_replace($schoolname,""," S.K.O.A.");
  
  // Get the year
  $schoolyear = SA_loadquery("SELECT year FROM period");
  $schoolyear = $schoolyear['year'][1];
  
  // Get the remarks pur in the exam result entry form
  $remarksqr = SA_loadquery("SELECT * FROM examresult WHERE year='". $schoolyear. "'", $userlink);
  if(isset($remarksqr))
    foreach($remarksqr['sid'] AS $six => $sid)
	{
	  $remarks[$sid] = $remarksqr['xresult'][$six];
	}
  
 // Voorkant KlasKaart:
    echo("<html><head><title>EX Personalia</title></head><body link=blue vlink=blue>");

// Clear existing exam numbers if in month august or september
/*	if(date('m') == 8 || date('m') == 9)
	{ // Autogen exam numbers
	  // First delete any exiting exam number
	  mysql_query("DELETE FROM s_exnr", $userlink);
	} */

		$KlasLijst = group::group_list("Exam%");
	if(isset($KlasLijst))
    foreach ($KlasLijst AS $group)
	{
// 	Titel KlasKaart met gewenste gegevens van de leerling uit de betreffende klas:
//    echo("<P class = koptxt3>". substr($group->get_groupname(),6). " ". $schoolyear."<span class = tal1spaties></P>");
	$SoortOnderwijs = "";
	switch (substr($group->get_groupname(),4))
	{
		case "Mavo":
			$SoortOnderwijs = "MAVO - Middelbaar Algemeen Voortgezet Onderwijs";
		break;
		case "Havo":
			$SoortOnderwijs = "HAVO - Hoger Algemeen Voortgezet Onderwijs";
		break;
		case "Vwo":
			$SoortOnderwijs = "VWO - Voortgezet Wtenschappelijk Onderwijs";
		break;
	}

// 	Tabel met de gewenste gegevens van de leerling uit de betreffende klas:
	
		$Nr = 0;
	    $LLlijst = student::student_list($group);
		$sidlist = inputclassbase::load_query("SELECT sid FROM s_exnr ORDER BY data");
		foreach ($sidlist['sid'] AS $stsid)
		{
		  if(isset($LLlijst[$stsid]))
		  {
		    $student = $LLlijst[$stsid];
		    if($Nr % 30 == 0)
			{
			  if($Nr != 0)
			    echo("</table><p class=pagebreak>Datum: ". date("d-m-Y"). "<span style='float: right'>Pagina ". ($Nr / 30). "</span></p>");
			  echo("<table><tr>
			         <td colspan=7  class = koptxt1a>School: ". $schoolname. "</td>
					 <td colspan=4 class = koptxt1b>Schooljaar: " . $schoolyear ."</td></tr>
			        <tr>
					  <td colspan=7  class = koptxt2>Opleiding:<BR>AVO - Algemeen Voortgezet Onderwijs</td>
					  <td colspan=4 class = koptxt2>Afdeling:<BR>". $SoortOnderwijs . "</td></tr>
			        <tr><th class=koptxt3 colspan=2>Registratienr</th><th class = koptxt3>Ex.Nr.</th><th class=koptxt3>Soort reg.</th><th class=koptxt3>Profiel</th><th class = koptxt3>Achternaam</th><th class = koptxt3>Voornamen</th><th class = koptxt3>m/v</th>
			        <th class = koptxt3>Geboortedatum</th><th class = koptxt3>Geboorteland</th><th class = koptxt3>Opmerkingen</th></tr>");
			}

			echo("<tr><td>&nbsp;</td><td>&nbsp;</td><td class = opmaakCenter>");
			// Get or create exam number depending on date!
			$nextexnr = str_pad(++$Nr,3,"0",STR_PAD_LEFT);
			if(date('m') == 18 || date('m') == 19) // Changed 19 sep 2013: don't use this form to set exnrs!
			{ // Autogen exam numbers
			  // Insert next number for this student
			  mysql_query("INSERT INTO s_exnr (sid,data) VALUES(". $student->get_id(). ",'". $nextexnr. "')", $userlink);
			  echo($nextexnr);
			  
			} 
			else // Just show exam numbers
			  echo($student->get_student_detail("s_exnr"));
			
			// Reg soort and profiel depend on subject package.
			$pck = $student->get_student_detail("*package");
			if(substr($pck,0,2) == "MM" || substr($pck,0,2) == "NW" || substr($pck,0,2) == "HU")
			{
			  if(substr($pck,0,2) == "HU")
			    $prof = "H";
			  else
			    $prof = substr($pck,0,2);
			  if(strpos($pck," : ") === false)
			    $rt = "D6";
			  else
			    $rt = "D". (6 + count(explode(",",substr($pck,strpos($pck," : ")+3))));
			}
			else
			{
			  $prof="&nbsp;";
			  $rt = "C";
			}
			
			echo("</td><td class=opmaakCenter>". $rt. "</td><td class=opmaakCenter>". $prof);	
			echo("</td><td class = opmaakLinks>". $student->get_lastname(). "</td><td class = opmaakLinks>". $student->get_firstname(). "</td>
					<td class = opmaakCenter>". $student->get_student_detail("s_ASGender"). "</td>
					<td class = opmaakCenter>". $student->get_student_detail("s_ASBirthDate"). "</td>
					<td class = opmaakCenter>". $student->get_student_detail("s_ASBirthCountry"). "</td>
					<td class = opmaaklinks>". (isset($remarks[$student->get_id()]) ? $remarks[$student->get_id()] : "&nbsp;"). "</td></tr>") ;
		  } // End if student is present in group
		} // einde foreach student / leerling uit de klas
	    echo("</table><p class=pagebreak>Datum: ". date("d-m-Y"). "<span style='float: right'>Pagina ". ceil($Nr / 30). "</span></p>");
		echo("<SPAN class=pagebreak>&nbsp;</SPAN>");			
	} // einde foreach group / klas



    
  // close the page
  echo("</html>");
?>
