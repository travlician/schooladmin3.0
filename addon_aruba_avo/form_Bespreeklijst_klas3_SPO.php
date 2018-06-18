<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2013 Aim4me N.V.   (http://www.aim4me.info)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();

  $login_qualify = 'ACT';
  require_once("schooladminfunctions.php");
  require_once("group.php");
  require_once("student.php");
  // Avoid sorting on students messed up
  unset($_SESSION['ssortertable']);
  // Connect library to database
  inputclassbase::dbconnect($userlink);
  // Operational definitions
  // Operational definitions
	global $vakhead;
	
	$llnperpage = 15;

  // Get the year
  $schoolyear = SA_loadquery("SELECT year FROM period");
  $schoolyear = $schoolyear['year'][1];
  
  // Get the group
  $mygroup = new group();
  $mygroup->load_current();
	$mentor = $mygroup->get_mentor(); // this is a teacher object!
	
	// Get the subject details (with teacher data)
	// Create a list of subject details
	$sdquery = "SELECT fullname, shortname, mid, firstname, lastname FROM class LEFT JOIN subject USING(mid) LEFT JOIN teacher USING(tid) WHERE gid=". $mygroup->get_id(). " AND shortname <> 'Com' ORDER BY show_sequence";
	$subjectdata = SA_loadquery($sdquery);
	foreach($subjectdata['shortname'] AS $cix => $subjab)
	{
		$subjdata[$subjab]["teacher"] = $subjectdata["firstname"][$cix]. " ". $subjectdata["lastname"][$cix];
		$subjdata[$subjab]["fullname"] = $subjectdata["fullname"][$cix];
		$subjdata[$subjab]["mid"] = $subjectdata["mid"][$cix];
	}

	// Get the number of subjects
	$subjcount = 0;
	foreach($subjdata AS $subshort)
	{
		$vakhead[$subjcount++] = $subshort;
	}

  // Decide for which period report is produced
  $repper = 3;
  
	// First part of the page
  echo("<html><head><title>Bespreeklijst</title></head><body link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style_Bespreeklijst_SPO.css" title="style1">';

  // We need to get the year for entry!
  $curyearqr = inputclassbase::load_query("SELECT year FROM period WHERE id=3");
  $curyear = $curyearqr['year'][0];

  // Get a list of students
  $students = student::student_list($mygroup);
  if(isset($students))
  {
		echo("<SCRIPT> var printdate = prompt('Datum:','". date("d-m-Y"). "'); </script>");
		echo("<SPAN style='padding-left: 50px; padding-right: 50px; display: inline-block; font-size: 160%;'>Beslissingsvergadering</span>
					<SPAN style='padding-left: 50px; padding-right: 50px; display: inline-block; font-size: 160%;'>Cijferlijst</span>
					<SPAN style='padding-left: 50px; padding-right: 50px; display: inline-block; font-size: 160%;'>Tijdvak ". (date("m") == 6 ? 1 : 2). "</span>
					<SPAN style='padding-left: 50px; padding-right: 50px; display: inline-block; font-size: 160%;'>". $schoolyear. "</span><BR><BR>
						");
		echo("<TABLE style='page-break-after: avoid;'><TR><TH class=logoname colspan=2><br><img src=schoollogo.png width=70%><br><BR><SPAN style='font-size: 140%;'><BR>Scol Practico pa Ofishi<BR>Locatie ". (substr($mygroup->get_groupname(),1,1) == "C" ? "Santa Cruz" : "Savaneta"). "<BR>Schooljaar ". $schoolyear. "<BR><BR>Klas ". $mygroup->get_groupname());
		echo("<BR>Mentor: ". $mygroup->get_mentor()->get_teacher_detail("*teacher.firstname"). " ". $mygroup->get_mentor()->get_teacher_detail("*teacher.lastname"). "<BR>Datum: ");
		echo("<SCRIPT> document.write(printdate); </script>");
		echo("<BR><BR><BR><B>Leerling:</b></span></TH><TH>Fecha di nacemento</th>");
		foreach($subjdata AS $asubj)
		{ // Show the heading info
			echo("<TH class=cathdr><SPAN class=turned2 style='width: 50px; float:left; margin-left:5px;'>". $asubj["fullname"]. "</SPAN></TH>");
		}
		echo("<TH>Uitslag:</th></tr>");
		echo("<TR>");
		$llnoffset = 0;
		$seqno = 1;
		foreach($students AS $student)
		{
			// Now data for each student
			echo("<TR ". ($llnoffset % 3 == 2 ? "class=signalrow" : ""). "><td>". $seqno. "</td><td class=studname>". $student->get_lastname(). ", ". $student->get_firstname(). "</td>");
			echo("<TD>". $student->get_student_detail("s_ASBirthDate"). "</td>");
			// get the report results
			$represqr = inputclassbase::load_query("SELECT result AS repres, shortname FROM gradestore LEFT JOIN subject USING(mid) WHERE year='". $schoolyear. "' AND period=0 AND sid=". $student->get_id());
			unset($repres);
			if(isset($represqr['repres']))
				foreach($represqr['shortname'] AS $tix => $tvk)
					$repres[$tvk] = $represqr['repres'][$tix];
			// Now show the results
			$validresult=true;
			$calco = 0;
			foreach($subjdata AS $avk => $subshort)
			{
				echo("<TD class=repres style='text-align: center;'>");
				if(isset($repres[$avk]))
				{
					if($repres[$avk] > 0.1 && $repres[$avk] < 5.5)
					{
						echo("<font color=red>". number_format($repres[$avk],0,',','.'). "</font>");
						$calco += 6 - round($repres[$avk],0);
						if($repres[$avk] < 3.5)
							$calco++; // This makes a 3 as result sure to have the student fail.
					}
					else if ($repres[$avk] > 0.1)
					{
						echo(number_format($repres[$avk],0,',','.'));
					}
					else echo($repres[$avk]);
				}
				else
				{
					echo("&nbsp;");
					$validresult = false;
				}
				echo("</td>");
			}
			$failed = $calco > 3;
			// Show result
			if($validresult)
				echo("<TD style='text-align: center; padding: 8px;'>". ($failed ? "Afgewezen" : "Geslaagd"). "</td>");
			else
				echo("<TD>&nbsp;</td>");
			echo("</tr>");
			$seqno++;
		} // End foreach loop students
		echo("</table>");
		echo("<BR><BR><SPAN style='font-size: 140%; display: inline-block;'>Inspecteur: __________________&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Adj. Directeur: __________________&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>");
  } // End if students defined
  else echo("<html>Geen leerlingen gevonden");
      
  echo("</html>");
  ?>
