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
  $vakcats = array("Dominio di Idioma","Materia exacto","Formacion general","Arte y educacion fisico","Materia practico");
 
  $vakheadi["Dominio di Idioma"] = array(7=>"Pa","Ne","En");
	$vakheadi["Materia exacto"] = array("Rek","If");
	$vakheadi["Formacion general"] = array("Prf","Prt","Pvl");
	$vakheadi["Arte y educacion fisico"] = array("Ckv","Lo","Exp");
	$vakheadi["Materia practico"] = array("Pca","Ppd");
	//$vakhead["Pasantia"] = array("St");
  $afwezigreden = array(1,2,3,4,5,11,12,17,18,21);
  $telaatreden = array(6,7,8,9,10,19);
	
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
	$sdquery = "SELECT fullname, shortname, mid, firstname, lastname FROM class LEFT JOIN subject USING(mid) LEFT JOIN teacher USING(tid) WHERE gid=". $mygroup->get_id();
	$subjectdata = SA_loadquery($sdquery);
	foreach($subjectdata['shortname'] AS $cix => $subjab)
	{
		$subjdata[$subjab]["teacher"] = $subjectdata["firstname"][$cix]. " ". $subjectdata["lastname"][$cix];
		$subjdata[$subjab]["fullname"] = $subjectdata["fullname"][$cix];
		$subjdata[$subjab]["mid"] = $subjectdata["mid"][$cix];
	}

	// Get the number of subjects
	$subjcount = 0;
	$catsums = 0;
	foreach($vakcats AS $vcat)
	{
		foreach($vakheadi[$vcat] AS $vix => $avk)
		{
			if(isset($subjdata[$avk]["teacher"]))
				$vakhead[$vcat][$subjcount++] = $vakheadi[$vcat][$vix];
		}
		if(count($vakhead[$vcat]) > 1)
			$catsums++;
	}

  // Decide for which period report is produced
  $curm = date("n");
  if($curm > 7)
    $repper = 1;
  else if($curm < 5)
    $repper = 2;
  else
    $repper = 3;
  
	// Get the behaviuor ascpects
	$behaveqr = SA_loadquery("SELECT sid,aspect,xstatus,period FROM bo_houding_data WHERE year='". $schoolyear. "' AND period=". $repper);
	if(isset($behaveqr['sid']))
		foreach($behaveqr['sid'] AS $bix => $bsid)
			if($behaveqr['xstatus'][$bix] == 1)
				$behave[$bsid][$behaveqr['aspect'][$bix]] = "O";
			else if($behaveqr['xstatus'][$bix] == 2)
				$behave[$bsid][$behaveqr['aspect'][$bix]] = "V";

	// Get a list of period dates
	$perdata = SA_loadquery("SELECT * FROM period");
	foreach($perdata['id'] AS $pix => $pid)
	{
		$pdata[$pid]["sdate"] = $perdata["startdate"][$pix];
		$pdata[$pid]["edate"] = $perdata["enddate"][$pix];
	}

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
		$llnoffset = 0;
		$seqno = 1;
		foreach($students AS $student)
		{
			if($llnoffset == 0)
			{
				echo("<TABLE><TR><TH class=logoname COLSPAN=2 rowspan=3><img src=schoollogo.png width=70%><BR>Scol Practico pa Ofishi<BR>Locatie ". (substr($mygroup->get_groupname(),1,1) == "C" ? "Santa Cruz" : "Savaneta"). "</TH>");
				echo("<TH class=mainheaderth COLSPAN=". (2 * $subjcount + $catsums + 6) ."><SPAN class=repnr>DI ". ($repper == 1 ? "PROME" : ($repper == 2 ? "DOS" : "TRES")). " RAPPORT</SPAN> <SPAN class=schoolyearhead>Aña Escolar ". $schoolyear. "</span><BR>KLAS: ". $mygroup->get_groupname(). "<BR>MENTOR: ". $mentor->get_teacher_detail("*teacher.firstname"). " ". $mentor->get_teacher_detail("*teacher.lastname"). "</TH></tr>");
				echo("<TR>"); // Now comes second heading row
				foreach($vakcats AS $vcat)
				{ // Show the heading info
					echo("<TH class=cathdr colspan=". (2 * count($vakhead[$vcat])). ">". $vcat. "</TH>");
					if(count($vakhead[$vcat]) > 1)
						echo("<TH class=cathdrt rowspan=3><SPAN class=turned>TOTAL</span></TH>");
				}
				echo("<TH class=cathdrr rowspan=3><SPAN class=turned2>PASANTIA</span></TH>");
				echo("<TH class=cathdrr rowspan=3><SPAN class=turned2 style='font-size: 7px;'>COMPORTACION</span></TH>");
				echo("<TH class=cathdrr rowspan=3><SPAN class=turned2>ACTITUD</span></TH>");
				echo("<TH class=cathdrr rowspan=3><SPAN class=turned2>AUSENCIA</span></TH>");
				echo("<TH class=cathdrr rowspan=3><SPAN class=turned2>YEGA&nbsp;LAT</span></TH>");
				echo("<TH class=cathdrt rowspan=3><SPAN class=turned2>TOTAL&nbsp;RAPPORT</span></TH>");
				echo("</tr><TR>");
				foreach($vakcats AS $vcat)
				{ // Show the subjects
					foreach($vakhead[$vcat] AS $avk)
					{
						echo("<TH class=subjhdr COLSPAN=2>". $subjdata[$avk]["fullname"]. "<BR><SPAN class=subdoc>". $subjdata[$avk]["teacher"]. "</span></th>");
					}
				}
				echo("</tr><TR>");
				// Now heading students
				echo("<TH class=shdr>NR</TH><TH class=shdr>NOMBER DI ALUMNO</TH>");
				foreach($vakcats AS $vcat)
				{ // Show the subjects
					foreach($vakhead[$vcat] AS $avk)
					{
						echo("<TH class=shdr>Cifranan</th><TH class=shdrg>R". $repper. "</th>");
					}
				}
				echo("</tr>");
			}
			// Now data for each student
			echo("<TR ". ($llnoffset % 3 == 2 ? "class=signalrow" : ""). "><TD class=seq>". $seqno++. "</td><td class=studname>". $student->get_firstname(). " ". $student->get_lastname(). "</td>");
			// Get the testresults and report data
			$testresqr = inputclassbase::load_query("SELECT GROUP_CONCAT(result SEPARATOR ' ') AS ress, shortname FROM testresult LEFT JOIN testdef USING(tdid) LEFT JOIN class USING(cid) LEFT JOIN subject USING(mid) WHERE year='". $schoolyear. "' AND period=". $repper. " AND sid=". $student->get_id(). " GROUP BY mid");
			unset($results);
			if(isset($testresqr['ress']))
				foreach($testresqr['shortname'] AS $tix => $tvk)
					$results[$tvk] = $testresqr['ress'][$tix];
			// get the report results
			$represqr = inputclassbase::load_query("SELECT result AS repres, shortname FROM gradestore LEFT JOIN subject USING(mid) WHERE year='". $schoolyear. "' AND period=". $repper. " AND sid=". $student->get_id());
			unset($repres);
			if(isset($represqr['repres']))
				foreach($represqr['shortname'] AS $tix => $tvk)
					$repres[$tvk] = $represqr['repres'][$tix];
			// Now show the results
			$rtot = 0.0;
			foreach($vakcats AS $vcat)
			{
				$ctot = 0.0;
				foreach($vakhead[$vcat] AS $avk)
				{
					echo("<td class=cifras><SPAN class=cifrasspan>");
					if(isset($results[$avk]))
						echo($results[$avk]);
					else
						echo("&nbsp;");
					echo("</span></td><TD class=repres>");
					if(isset($repres[$avk]))
					{
						if($repres[$avk] > 0.1 && $repres[$avk] < 5.5)
							echo("<font color=red>". number_format($repres[$avk],1,',','.'). "</font>");
						else if ($repres[$avk] > 0.1)
							echo(number_format($repres[$avk],1,',','.'));
						else echo($repres[$avk]);
						$rtot += $repres[$avk];
						$ctot += $repres[$avk];
					}
					else
						echo("&nbsp;");
					echo("</td>");
				}
				if(count($vakhead[$vcat]) > 1)
					echo("<td class=repres>". number_format($ctot,1,',','.'). "</td>");
			}
			
			// Show result for Pasantia (Stage)
			echo("<TD class=repres>");
			if(isset($repres['St']))
			{
				if($repres['St'] > 0.1 && $repres['St'] < 5.5)
					echo("<font color=red>". number_format($repres['St'],1,',','.'). "</font>");
				else if ($repres['St'] > 0.1)
					echo(number_format($repres['St'],1,',','.'));
				else echo($repres['St']);
				$rtot += $repres['St'];
				$ctot += $repres['St'];
			}
			else
				echo("&nbsp;");
			echo("</td>");
			
			// Behaviour data
			echo("<TD class=repres>");
			if(isset($behave[$student->get_id()]['Gedrag']))
				echo($behave[$student->get_id()]['Gedrag']);
			else
				echo("&nbsp;");
			echo("</td>");
			echo("<TD class=repres>");
			if(isset($behave[$student->get_id()]['Houding']))
				echo($behave[$student->get_id()]['Houding']);
			else
				echo("&nbsp;");
			echo("</td>");
					
		  // Get the student absence in set
			unset($stlate);
			unset($stabs);
			$sres = SA_loadquery("SELECT SUM(IF(date >= '". $pdata[$repper]['sdate']. "' AND date <= '". $pdata[$repper]['edate']. "' AND (acid=1 OR acid=4 OR acid=5),1,0)) AS afw, SUM(IF(date >= '". $pdata[$repper]['sdate']. "' AND date <= '". $pdata[$repper]['edate']. "' AND acid=2,1,0)) AS late FROM absence LEFT JOIN absencereasons USING(aid) WHERE sid=". $student->get_id());
			if(isset($sres))
			{
				if($sres['afw'][1] > 0) $stabs = $sres['afw'][1];
				if($sres['late'][1] > 0) $stlate = $sres['late'][1];
			}
			unset($sres);
			
			// Show student absence
			echo("<TD class=repres>");
			if(isset($stabs))
				echo($stabs);
			else
				echo(0);
			echo("</td><TD class=repres>");
			if(isset($stlate))
				echo($stlate);
			else
				echo(0);

			// how total pounts report
			echo("</td><TD class=repres>". number_format($rtot,1,',','.'). "</td></tr>");
			$llnoffset++;
			if($llnoffset == $llnperpage)
			{
				print_footer();
				$llnoffset = 0;
			}
		} // End foreach loop students
		print_footer();
  } // End if students defined
  else echo("<html>Geen leerlingen gevonden");
      
  echo("</html>");
  
  function print_footer()
  {
		global $vakcats, $vakhead, $mygroup;
		echo("<TR class=noborder><TD class=noborder>&nbsp;</td></tr>");
		echo("<TR><TD class=noborder>&nbsp;</td><TH>PROMEDIO PA MATERIA</TD>");
		// Get the averages
		$avgqr = SA_loadquery("SELECT AVG(result) AS ravg, shortname FROM gradestore LEFT JOIN sgrouplink USING(sid) LEFT JOIN subject USING(mid) WHERE gid=". $mygroup->get_id(). " GROUP BY shortname");
		if(isset($avgqr['ravg']))
			foreach($avgqr['shortname'] AS $aix => $avk)
			{
				$ravg[$avk] = $avgqr['ravg'][$aix];
			}
		
		foreach($vakcats AS $vcat)
		{
			foreach($vakhead[$vcat] AS $avk)
			{
				echo("<TH class=botcel>". $avk. "</th><TH class=botcel>". (isset($ravg[$avk]) && $ravg[$avk] > 0.0 ? number_format($ravg[$avk],1,',','.') : "&nbsp;"). "</th>");
			}
			if(count($vakhead[$vcat]) > 1)
				echo("<TD class=noborder>&nbsp;</td>");
		}
    echo("</TABLE>");
  }
?>
