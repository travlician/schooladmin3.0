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
  // Link input library with database
  inputclassbase::dbconnect($userlink);
  echo ('<LINK rel="stylesheet" type="text/css" href="style_TelKlasKaart.css" title="style1">');
  
  // Get the school name
  $schoolname = $announcement;
  $schoolname = str_replace("!","",$schoolname);
  //$schoolname = str_replace($schoolname,""," S.K.O.A.");
  
  // Get the year
  $schoolyear = SA_loadquery("SELECT year FROM period");
  $schoolyear = $schoolyear['year'][1];
  
 
  // Get a list of groups
  $groups = SA_loadquery("SELECT * FROM sgroup LEFT JOIN ". $teachercode. " ON(tid_mentor=tid) WHERE active=1 AND tid_mentor <> 1 ORDER BY groupname");
  
  if(isset($groups))
 {
 // Voorkant KlasKaart:
    echo("<html><head><title>TelKlasKaart</title></head><body link=blue vlink=blue>");
	foreach($groups['gid'] AS $sgid)
	  $KlasLijst[$sgid] = new group($sgid);
	//$KlasLijst = group::group_list();
    foreach ($KlasLijst AS $group)
	{
// 	Titel KlasKaart met gewenste gegevens van de leerling uit de betreffende klas:
    	echo("<P class = koptxt>". $group->get_groupname(). " ". $schoolyear."<span class = tal1spaties></P>");
		
// Haal op de lijst van leerlingen uit de betreffende klas:
		$MentorKlas = $group->get_mentor()->get_username() ;
		echo("<DIV class = koptxt>Klasseleerkracht: ". $MentorKlas. " (". $group->get_mentor()->get_teacher_detail($teachercode). ")</DIV>");

// 	Tabel met de gewenste gegevens van de leerling uit de betreffende klas:
			echo("<table class = TblPropKaart>
				<tr><td class = koptxt>Nr.</td><td class = koptxt>Achternaam</td><td class = koptxt>Voornaam</td>
					<td class = IDkoptxt>ID nummer</td><td class = koptxt>m/v</td><td class = koptxt>Adres</td>
					<td class = LLkoptxt>Tel. LL thuis</td>
					<td class = LLkoptxt>Cell Leerling</td>
					<td class = Ckoptxt>Tel. Moeder</td>
					<td class = Ckoptxt>Tel. Vader</td>");
	
		$Nr = 0;
	    $LLlijst = student::student_list($group);
		if(isset($LLlijst))
		foreach ($LLlijst AS $student)
		{
		  if($student <> null)
		  {
				 echo("<tr><td class = opmaakNr>". ++$Nr. "</td><td>". $student->get_lastname(). "</td><td>". $student->get_firstname(). "</td>
					<td class = opmaakCenter>". $student->get_student_detail("*sid"). "</td>
					<td class = opmaakCenter>". $student->get_student_detail("s_ASGender"). "</td>
					<td>". $student->get_student_detail("s_ASAddress"). "</td>
					<td class = opmaakCenter>". $student->get_student_detail("s_ASPhoneHomeStudent"). "</td>
					<td class = opmaakCenter>". $student->get_student_detail("s_ASMobilePhoneStudent"). "</td>
					<td class = opmaakCenter>". $student->get_student_detail("s_ASPhoneHomeParent2"). "</td>
					<td class = opmaakCenter>". $student->get_student_detail("s_ASPhoneHomeParent1"). "</td></tr>") ;
		  }
		  else
		  {
		    echo("<TR><TD COLSPAN=10>&nbsp;</td></tr>");
		  }
		  

		} // einde foreach student / leerling uit de klas
		echo("</table>");
		echo("<SPAN class=pagebreak>&nbsp;</SPAN>");
			
	} // einde foreach group / klas



  } // Endif 1
    
  // close the page
  echo("</html>");
?>
