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
  // Avoid sorting on students messed up
  unset($_SESSION['ssortertable']);
  // Link input library with database
  inputclassbase::dbconnect($userlink);
  echo ('<LINK rel="stylesheet" type="text/css" href="style_TelKlasKaart.css" title="style1">');
  
  // Get the school name
  $schoolname = $announcement;
  $schoolname = str_replace("!","",$schoolname);
  $schoolname = str_replace("Welkom bij ","",$schoolname);
  $schoolname = str_replace("het ","",$schoolname);
  $schoolname = str_replace("de ","",$schoolname);
  $schoolname = $schoolname. " - S.K.O.A.";
  
  // Get the year
  $schoolyear = SA_loadquery("SELECT year FROM period");
  $schoolyear = $schoolyear['year'][1];
  
 
  // Get a list of groups
  $groepfilter = "3%";
  $groups = SA_loadquery("SELECT * FROM sgroup LEFT JOIN ". $teachercode. " ON(tid_mentor=tid) WHERE active=1 AND groupname LIKE '". $groepfilter. "' ORDER BY groupname");
  
  if(isset($groups))
 {
 // Voorkant KlasKaart:
    echo("<html><head><title>INSP KlasKaart LS</title></head><body link=blue vlink=blue>");
	$KlasLijst = group::group_list();
    foreach ($KlasLijst AS $group)
	{
// 	Titel KlasKaart met gewenste gegevens van de leerling uit de betreffende klas:
		$Klas = substr($group->get_groupname(),0,1) ;
		switch ($Klas)
		{
			case 1:
				echo("<P class = koptxt>Ciclo Basico (". $Klas. ") ". $schoolyear."<span class = tal1spaties> </span>". $schoolname. "</P>");
			break;
			case 2:
				echo("<P class = koptxt>Ciclo Basico (". $Klas. ") ". $schoolyear."<span class = tal1spaties> </span>" .$schoolname. "</P>");
			break;
			case 3:
				echo("<P class = koptxt>Ciclo Avansa (". $Klas. ") ". $schoolyear."<span class = tal1spaties> </span>" .$schoolname. "</P>");
			break;
			case 4:
				echo("<P class = koptxt>Ciclo Avansa (". $Klas. ") ". $schoolyear."<span class = tal1spaties> </span>" .$schoolname. "</P>");
			break;
		}
		if($Klas >= 1)
		{
		

// 	Tabel met de gewenste gegevens van de leerling uit de betreffende klas:
			echo("<table class = TblPropKaart>
				<tr><td class = koptxt>Klas</td><td class = koptxt>Achternaam</td><td class = koptxt>Voornaam</td>
					<td class = koptxt>m/v</td><td class = koptxt>Geb. Datum</td><td class = koptxt>Adres</td>
					<td class = LLkoptxt>Geb. Plaats</td>
					<td class = Ckoptxt>Tel. Moeder</td>
					<td class = Ckoptxt>Tel. Vader</td><td class = Ckoptxt>Nationaliteit</td>");
	
		$Nr = 0;
	    $LLlijst = student::student_list($group);
		if(isset($LLlijst))
		foreach ($LLlijst AS $student) {
				 echo("<tr><td class = opmaakNr>". $group->get_groupname(). "</td><td>". $student->get_lastname(). "</td><td>". $student->get_firstname(). "</td>
					<td class = opmaakCenter>". $student->get_student_detail("s_ASGender"). "</td>
					<td>". uniform_date($student->get_student_detail("s_ASBirthDate")). "</td>
					<td>". $student->get_student_detail("s_ASAddress"). "</td>
					<td>". $student->get_student_detail("s_ASBirthCountry"). "</td>
					<td class = opmaakCenter>". $student->get_student_detail("s_ASPhoneHomeParent2"). "</td>
					<td class = opmaakCenter>". $student->get_student_detail("s_ASPhoneHomeParent1"). "</td>
					<td class = opmaakCenter>". $student->get_student_detail("s_ASNationality"). "</td></tr>") ;

		} // einde foreach student / leerling uit de klas
		echo("</table>");
		echo("<SPAN class=pagebreak>&nbsp;</SPAN>");
		}	
	} // einde foreach group / klas



  } // Endif 1
    
  // close the page
  echo("</html>");
  function uniform_date($datestr)
  {
    $months=array(1=>"januari","februari","maart","april","mei","juni","juli","augustus","september","oktober","november","december");
	$spd = explode("-",$datestr);
	if(count($spd) >= 3)
	{
	  $day = 0 + $spd[0];
	  $month = 0 + $spd[1];
	  $year = $spd[2];
	  if(isset($months[$month]))
	    return($day. "-". $months[$month]. "-". $year);
	  else
	    return($datestr);
	}
	else
	{
	  $spd = explode("/",$datestr);
	  if(count($spd) >= 3)
	  {
	    $day = 0 + $spd[0];
	    $month = 0 + $spd[1];
	    $year = $spd[2];
	    if(isset($months[$month]))
	      return($day. "-". $months[$month]. "-". $year);
	  }
	  else
	  {
	    $spd = explode(" ",$datestr);
	    if(count($spd) >= 3)
	    {
	      $day = 0 + $spd[0];
	      $month = $spd[1];
	      $year = $spd[2];
	      if(isset($months[$month]))
	        return($day. "-". $months[$month]. "-". $year);
		  else
		    return($day. "-". $month. "-". $year);
	    }
	  }
	  return($datestr);
	}
  }
?>
