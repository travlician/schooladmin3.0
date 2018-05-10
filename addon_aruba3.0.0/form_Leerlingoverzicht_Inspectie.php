<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2015 Aim4me N.V.   (http://www.aim4me.com)	      |
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
  echo ('<LINK rel="stylesheet" type="text/css" href="style_Lloverzicht_inspectie.css" title="style1">');
  
  // Get the school name
  $schoolname = $announcement;
  $schoolname = str_replace("!","",$schoolname);
  //$schoolname = str_replace($schoolname,""," S.K.O.A.");
  
  // Get the year
  $schoolyear = SA_loadquery("SELECT year FROM period");
  $schoolyear = $schoolyear['year'][1];
	
	// Array with dutch month names
	$months=array(1 => "januari", "februari", "maart", "april", "mei", "juni", "juli", "augustus", "september", "oktober", "november", "december");
	
	// If the table s_ASIdNr exists, we use that table for the ID, else we use the alsternative sid (by specifying *sid!)
	$idtqr = SA_loadquery("SHOW TABLES LIKE 's_ASIdNr'");
	if(isset($idtqr))
	  $idcol = "s_ASIdNr";
	else
	  $idcol = "*sid";
  
 
  // Get a list of groups
	if(!isset($PrimaryGroupFilter))
		$PrimaryGroupFilter='__';
  $groups = SA_loadquery("SELECT * FROM sgroup LEFT JOIN ". $teachercode. " ON(tid_mentor=tid) WHERE active=1 AND tid_mentor <> 1 AND (groupname LIKE '". $PrimaryGroupFilter. "') ORDER BY groupname");
  
  if(isset($groups))
 {
    echo("<html><head><title>Leerlingenoverzicht Inspectie</title></head><body link=blue vlink=blue>");
	  foreach($groups['gid'] AS $sgid)
	    $KlasLijst[$sgid] = new group($sgid);
    foreach ($KlasLijst AS $group)
		{
			// Table setup and general info first
			echo("<table><TR><TD colspan=12 class=toprow1><SPAN class=tbheadrow>LEERLINGENOVERZICHT per 1 september van het schooljaar:<SPAN class=tbheadsy>". $schoolyear. "</span><BR>");
			echo("<SPAN class=pardir>Par. dir.:</span>Aantal blz: ". count($KlasLijst). "<BR></td></tr>");
			echo("<TR class=toprow><th>klas</th><th>nr.</th><th>naam</th><th>voornaam</th><th>v/m</th><th>Id.</th><th>huisadres*</th><th>voertaal</th><th>geb.</th><th>instr.</th><th>vader/voogd</th><th>moeder/voogd</th></tr>");

		$Nr = 0;
	    $LLlijst = student::student_list($group);
		if(isset($LLlijst))
		foreach ($LLlijst AS $student)
		{
		  if($student <> null)
		  {
				 echo("<tr><th>". $group->get_groupname(). "</th><th class=centered>". ++$Nr. "</th><td>". $student->get_lastname(). "</td><td>". $student->get_firstname(). "</td>
					<td class = centered>". $student->get_student_detail("s_ASGender"). "</td>
					<td>". format_idnr($student->get_student_detail($idcol)). "</td>
					<td>". $student->get_student_detail("s_ASAddress"). "</td>
					<td class = centered>". $student->get_student_detail("s_ASHomeLanguage"). "</td>
					<td class = centered>". $student->get_student_detail("s_ASBirthCountry"). "</td>
					<td class = centered>". $student->get_student_detail("s_ASEntryYear"). "</td>
					<td>". $student->get_student_detail("s_ASFirstNameParent1"). " ". $student->get_student_detail("s_ASLastNameParent1"). "</td>
					<td>". $student->get_student_detail("s_ASFirstNameParent2"). " ". $student->get_student_detail("s_ASLastNameParent2"). "</td>
					</tr>") ;
		  }
		  

		} // einde foreach student / leerling uit de klas
		// Finalising table
		echo("<TR CLASS=toprow><TD colspan=12><span class=lastrow1>* zie handleiding voor afkorting en extra informatie</span><span class=placedate>Aruba, ". date("j "). $months[date('n')]. date(" Y"). "</td></tr>");
		echo("</table>");
		echo("<SPAN class=pardir2>Par. dir.:</SPAN><SPAN class=llncount> Aantal:". $Nr. "</span>");
		echo("<P class=pagebreak>&nbsp;</p>");
			
	} // einde foreach group / klas



  } // Endif 1
    
  // close the page
  echo("</html>");
	function format_idnr($idnr)
	{
		$idnr = str_replace(".","",$idnr);
		$idtxt = str_pad($idnr,8,'0',STR_PAD_LEFT);
		return(substr($idtxt,0,2). ".". substr($idtxt,2,2). ".". substr($idtxt,4,2). ".". substr($idtxt,6,2));
	}
?>
