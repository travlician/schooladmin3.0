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
  require_once("schooladminfunctions.php");
  require_once("student.php");
  require_once("group.php");
  require_once("teacher.php");
  
  // First see if date is already typed, if not ask for it!
  if(!isset($_POST['rdate']))
  {
    echo("<P>Afdruk instellingen (Firefox):<BR>Marges op 0,2 inch (5,1 mm), <b>90% scaling</b>, geen header/footer (Blank), Portrait, A4.<BR>KIES EERST DE JUISTE GROEP!</P>"); 
    echo("<FORM name=rdatefrm id=rdatefrm METHOD=POST ACTION=". $_SERVER['PHP_SELF']. ">Datum (zonder jaartal): <INPUT TYPE=TEXT SIZE=40 NAME=rdate><INPUT TYPE=SUBMIT NAME='OK' VALUE='OK'></FORM>");
    exit();
  }
  
  // Avoid sorting on students messed up
  unset($_SESSION['ssortertable']);
  // Connect library to database
  inputclassbase::dbconnect($userlink);
  
  $uid = $_SESSION['uid'];
  $uid = intval($uid);

  // Get the school name
  $schoolname = $announcement;
  $schoolname = str_replace("!","",$schoolname);
  $schoolname = str_replace("Welkom bij ","",$schoolname);
  //$schoolname = str_replace("het ","",$schoolname);
  //$schoolname = str_replace("de ","",$schoolname);
  
  // Get the year
  $schoolyear = SA_loadquery("SELECT year FROM period");
  $schoolyear = $schoolyear['year'][1];
  
  // Get the group
  $mygroup = new group();
  $mygroup->load_current();
    
  // Subject translation tables
  $offsubjects = array(1 => "Ne","En","Sp","Pa","Wi","Nask1","Nask2","Bio","EcMo","Ak","Gs","CKV");
  $altsubjects = array("NAT4"=>6, "GES4"=>11, "AK4"=>10, "EC4"=>9, "BIO4"=>8, "SCH4"=>7, "WIS4"=>5, "PAP4"=>4, "SPA4"=>3, "ENG4"=>2, "NED4"=>1,
                       "Ne"=>1, "En"=>2, "Sp"=>3, "Wi"=>5, "Na"=>6, "Sk"=>7, "Bio"=>8, "Gs"=>11, "Ak"=>10, "Ec"=>9, "Pa"=>4, "NaSk 1"=>6, "NaSk 2"=>7, "EcMo"=>9,
					   "PA"=>4, "NE"=>1, "EN"=>2, "SP"=>3, "WI"=>5, "AK"=>10, "BI"=>8, "GS"=>11, "Na"=>6, "SK"=>7, "EC/MO"=>9,
					   "ne"=>1, "en"=>2, "sp"=>3, "pa"=>4, "wi"=>5, "na"=>6, "sk"=>7, "bi"=>8, "ec"=>9, "ak"=>10, "gs"=>11,
					   "NA"=>6, "EC"=>9, "EM & O"=>9, "CKV"=>12, "Ckv"=>12);
   // Get a list of subjects applicable to the exam subjects
  $subjectsqr = SA_loadquery("SELECT shortname,mid FROM subject ORDER BY mid");
  foreach($offsubjects AS $osix => $sjname)
  {
    foreach($subjectsqr['shortname'] AS $sbix => $subsn)
		{
			if(isset($altsubjects[$subsn]) && $altsubjects[$subsn] == $osix)
			{
				if($sjname == "CKV")
				$ckvmid = $subjectsqr['mid'][$sbix];
			}
		}
  }
 // Get the CKV results from the last year where the first period was also present (which is not the case in the 4th year)
  $ckvrq = "SELECT sid,period,result FROM gradestore LEFT JOIN (SELECT sid,MAX(year) AS gsyear FROM gradestore WHERE mid=". $ckvmid. " AND period=1 GROUP BY sid) AS gsy USING(sid) WHERE year=gsyear AND mid=". $ckvmid;
  $ckvrqr = SA_loadquery($ckvrq);
  if(isset($ckvrqr['result']))
  {
    foreach($ckvrqr['sid'] AS $cix => $asid)
		{
			$ckvr[$asid][$ckvrqr['period'][$cix]] = $ckvrqr['result'][$cix]; 
		}
  }
  // First part of the page
  echo("<html><head><title>Cijferlijst MAVO Diploma</title></head><body link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style_Cijferlijst_MAVO_LS.css" title="style1">';
  
// Tabel met vertaling afwijkende vaknaam naar standaard vaknaam
  $altsn = array("NED"=>"ne","PAP"=>"pa","ENG"=>"en","WIS"=>"wi","SPA"=>"sp","SK"=>"nask 2","SCH"=>"nask 2","GES"=>"gs","NA"=>"nask 1","SK"=>"nask 2","NASK1"=>"nask 1","EC"=>"ecmo","EM & O"=>"ecmo","BI"=>"bio","EC/MO"=>"ecmo","em & o"=>"ecmo");
  $profilenames=array("HU"=>"Humaniora","MM"=>"Mens en Maatschappijwetenschappen","NW"=>"Natuurwetenschappen");
// Subjectsequences have changed by EBA for 2013-2014:
// And again change requested 7 dec 2017
  $pksubs = array("HU01"=>array("ne","en","sp","gs","ckv","ecmo"),
                   "HU02"=>array("ne","en","sp","gs","ckv","wi"),
                   "HU03"=>array("ne","en","sp","gs","ckv","pa"),
                   "HU04"=>array("ne","en","sp","ak","ckv","ecmo"),
                   "HU05"=>array("ne","en","sp","ak","ckv","wi"),
                   "HU06"=>array("ne","en","sp","ak","ckv","pa"),
                   "HU07"=>array("ne","en","sp","ak","gs","ecmo"),
                   "HU08"=>array("ne","en","sp","ak","gs","wi"),
                   "HU09"=>array("ne","en","sp","ak","gs","ckv"),
                   "HU10"=>array("ne","en","sp","ak","gs","pa"),
// Vakkenpakketten voor Mens en Maatschappijwetenschappen			   
                   "MM01"=>array("ne","en","wi","ecmo","gs","sp"),
                   "MM02"=>array("ne","en","wi","ecmo","gs","pa"),
                   "MM03"=>array("ne","en","wi","ecmo","gs","bio"),
                   "MM04"=>array("ne","en","wi","ecmo","ak","sp"),
                   "MM05"=>array("ne","en","wi","ecmo","ak","pa"),
                   "MM06"=>array("ne","en","wi","ecmo","ak","bio"),
                   "MM07"=>array("ne","en","wi","ecmo","ak","gs"),
                   "MM08"=>array("ne","en","wi","ak","gs","sp"),
                   "MM09"=>array("ne","en","wi","ak","gs","pa"),
                   "MM10"=>array("ne","en","wi","ak","gs","bio"),
                   "MM11"=>array("ne","en","wi","ak","gs","ecmo"),
// Vakkenpakketten voor Natuurwetenschappen				   
                   "NW01"=>array("ne","en","wi","nask 1","nask 2","sp"),
                   "NW02"=>array("ne","en","wi","nask 1","nask 2","pa"),
                   "NW03"=>array("ne","en","wi","nask 1","nask 2","bio"),
                   "NW04"=>array("ne","en","wi","nask 1","nask 2","ecmo"),
                   "NW05"=>array("ne","en","wi","nask 2","bio","sp"),
                   "NW06"=>array("ne","en","wi","nask 2","bio","pa"),
                   "NW07"=>array("ne","en","wi","nask 2","bio","ecmo"));
  /*$pksubs = array("HU01"=>array("ne","en","sp","gs","ckv","ecmo"),
                   "HU02"=>array("ne","en","sp","gs","ckv","wi"),
                   "HU03"=>array("ne","en","sp","gs","ckv","pa"),
                   "HU04"=>array("ne","en","sp","ak","ckv","ecmo"),
                   "HU05"=>array("ne","en","sp","ak","ckv","wi"),
                   "HU06"=>array("ne","en","sp","ak","ckv","pa"),
                   "HU07"=>array("ne","en","sp","ak","gs","ecmo"),
                   "HU08"=>array("ne","en","sp","ak","gs","wi"),
                   "HU09"=>array("ne","en","sp","ak","gs","ckv"),
                   "HU10"=>array("ne","en","sp","ak","gs","pa"),
// Vakkenpakketten voor Mens en Maatschappijwetenschappen			   
                   "MM01"=>array("ne","en","wi","ecmo","gs","sp"),
                   "MM02"=>array("ne","en","wi","ecmo","gs","pa"),
                   "MM03"=>array("ne","en","wi","ecmo","gs","bio"),
                   "MM04"=>array("ne","en","wi","ecmo","ak","sp"),
                   "MM05"=>array("ne","en","wi","ecmo","ak","pa"),
                   "MM06"=>array("ne","en","wi","ecmo","ak","bio"),
                   "MM07"=>array("ne","en","wi","ecmo","ak","gs"),
                   "MM08"=>array("ne","en","wi","ak","gs","sp"),
                   "MM09"=>array("ne","en","wi","ak","gs","pa"),
                   "MM10"=>array("ne","en","wi","ak","gs","bio"),
// Vakkenpakketten voor Natuurwetenschappen				   
                   "NW01"=>array("ne","en","wi","nask 1","nask 2","sp"),
                   "NW02"=>array("ne","en","wi","nask 1","nask 2","pa"),
                   "NW03"=>array("ne","en","wi","nask 1","nask 2","bio"),
                   "NW04"=>array("ne","en","wi","nask 2","bio","sp"),
                   "NW05"=>array("ne","en","wi","nask 2","bio","pa")); */
  
  $sub2full = array("ne"=>"Nederlandse taal en literatuur", "en"=>"Engelse taal en literatuur", "wi"=>"Wiskunde",
                    "ak"=>"Aardrijkskunde", "gs"=>"Geschiedenis en staatsinrichting", "sp"=>"Spaanse taal en literatuur",
					"ecmo"=>"Economie/management en organisatie", "nask 2"=>"Natuur- en scheikunde 2", "nask 1"=>"Natuur- en scheikunde 1",
					"bio"=>"Biologie","ckv"=>"Culturele en kunstzinnige vorming","pa"=>"Papiamentse taal en cultuur","lo"=>"Lichamelijke opvoeding");
  $digittext = array(1=>"EEN","TWEE","DRIE","VIER","VIJF","ZES","ZEVEN","ACHT","NEGEN","TIEN");
  $sdigittext = array(1=>"ONVOLDOENDE","ONVOLDOENDE","ONVOLDOENDE","ONVOLDOENDE","ONVOLDOENDE","VOLDOENDE","VOLDOENDE","GOED","GOED","GOED");

  // Get a list of students
  $students = student::student_list($mygroup);

  if(isset($students))
  {
	  echo("<P class=pagebreak>&nbsp;</p>");
		//echo("<IMG class=schoollogo SRC=schoollogo.png>");
	  echo("<P class=footnote>Doorhalingen en/of wijzigingen maken deze cijferlijst ongeldig.<BR>Niet gebruikte regels en vakken in de tabel zijn ongeldig gemaakt.</p>");
    foreach($students AS $student)
     stud_grades($student, $schoolyear,$mygroup);
  } // End if student for the group
	else
		echo("Geen studenten gevonden in de huidige groep!");
	
  echo("</html>");
    
  function stud_grades($student,$schoolyear,$group)
  {
    global $noexam;
		global $ckvr;
		global $altsn;
    $sid = $student->get_id();
    global $schoolname,$schoolyear,$pksubs,$sub2full,$digittext,$sdigittext;
	//echo($student->get_lastname(). "<BR>");

    // Get the list of applicable subjects with their details
		$package = $student->get_student_detail("*package");
		$profile = substr($package,0,2);
		if($profile == "MM")
			$profile = "Mens en Maatschappijwetenschappen";
		else if($profile=="HU")
			$profile = "Humaniora";
		else if($profile=="NW")
			$profile = "Natuurwetenschappen";
		else
		{
			//echo("NO profile for ". $student->get_lastname(). " (". $profile. ")<BR>");
			return; // Don;t show this if no predefined profile is applicable
		}
	
		$profid = substr($package,0,4);
		
			// Get the list of grades for normal periods
		unset($results_array);
		unset($results_prv);
		unset($prvyear);
		$sql_query = "SELECT gradestore.*,shortname FROM gradestore LEFT JOIN subject USING(mid) WHERE sid=". $student->get_id(). " ORDER BY period DESC, year DESC";
		$grades = inputclassbase::load_query($sql_query);
		if(isset($grades))
			foreach($grades['result'] AS $grix => $gres)
			{
				if(isset($altsn[$grades['shortname'][$grix]]))
					$grades['shortname'][$grix] = $altsn[$grades['shortname'][$grix]];
				else
					$grades['shortname'][$grix] = strtolower($grades['shortname'][$grix]);
				if($grades['year'][$grix] == $schoolyear)
				{
					if($grades['period'][$grix] > 0 && $gres > 0.0)
								$results_array[$grades['period'][$grix]][$grades['shortname'][$grix]] = number_format($gres,1,',','.');
					else if(($gres > 0 && ((isset($results_array[2][$grades['shortname'][$grix]]) && isset($results_array[3][$grades['shortname'][$grix]])))) || $grades['shortname'][$grix] == "lo")
              $results_array[$grades['period'][$grix]][$grades['shortname'][$grix]] = $gres;
				}
				else
				{
					if(isset($prvyear) && $prvyear < $grades['year'][$grix])
					{
						unset($results_prv); // a newer year with results is entered now! So forget ealier year
						$prvyear = $grades['year'][$grix];
					}
					else if(!isset($prvyear))
					{
						$prvyear = $grades['year'][$grix];
					}
					if($grades['period'][$grix] > 0 && $gres > 0.0 && $grades['year'][$grix] == $prvyear)
					{
						$results_prv[$grades['period'][$grix]][$grades['shortname'][$grix]] = number_format($gres,1,',','.');
					}
					else if($grades['year'][$grix] == $prvyear)
						if($gres > 0 && isset($results_prv[2][$grades['shortname'][$grix]]) && isset($results_prv[3][$grades['shortname'][$grix]]))
						{
							$results_prv[$grades['period'][$grix]][$grades['shortname'][$grix]] = $gres;
						}
				}
			}
	
		// Get "Vrijstellingen" and certificates
		unset($vrijst);
		unset($certs);
		$vrcertqr = inputclassbase::load_query("SELECT shortname,xstatus FROM ex45data LEFT JOIN subject USING(mid) WHERE sid=". $student->get_id(). " AND year='". $schoolyear. "' AND xstatus > 4 AND mid>0");
		if(isset($vrcertqr))
			foreach($vrcertqr['shortname'] AS $vix => $vmid)
			{
				if($vrcertqr['xstatus'][$vix] < 9)
					$vrijst[$vmid] = $vrcertqr['xstatus'][$vix]+2;
				else
					$certs[$vmid] = $vrcertqr['xstatus'][$vix]-3;
			}
	
		// If a "vrijstelling" or certificate is applicable, see if we get results from previous year.
		if(isset($vrijst))
			foreach($vrijst AS $ssn => $res)
			{
				$results_array[0][$ssn] = $res; // set end result
				if(isset($results_prv[0][$ssn]) && $results_prv[0][$ssn] == $res)
				{ // result from previous year is present and matches set result, so can use these results for this list
					$results_array[2][$ssn] = $results_prv[2][$ssn];
					$results_array[3][$ssn] = $results_prv[3][$ssn];
				}
				else
				{ // No valid result from previous year present, set end and mark SE and CE result in light grey as vrijstelling source
					$results_array[2][$ssn] = "<span class=vcmark>Vrijst</span>";
					$results_array[3][$ssn] = "<span class=vcmark>Vrijst</span>";		 
				}
			}
		if(isset($certs))
			foreach($certs AS $ssn => $res)
			{
				$results_array[0][$ssn] = $res; // set end result
				if(isset($results_prv[0][$ssn]) && $results_prv[0][$ssn] == $res)
				{ // result from previous year is present and matches set result, so can use these results for this list
					$results_array[2][$ssn] = $results_prv[2][$ssn];
					$results_array[3][$ssn] = $results_prv[3][$ssn];
				}
				else
				{ // No valid result from previous year present, set end and mark SE and CE result in light grey as vrijstelling source
					$results_array[2][$ssn] = "<span class=vcmark>Cert</span>";
					$results_array[3][$ssn] = "<span class=vcmark>Cert</span>";		  
				}
			}

		// Get prefilled I&S and PFW results
		unset($ahx);
  
		//echo("<DIV class=schoollogo><img src=schoollogo.png height=100px width=100px></DIV><BR>");
    echo("<P class=maintitle>CIJFERLIJST</p>");
		//echo("<P class=secondtitle>EINDEXAMEN ". substr($schoolyear,-4). "<BR>MIDDELBAAR ALGEMEEN VOORTGEZET ONDERWIJS</p>");
		echo("<P class=secondtitle>MIDDELBAAR ALGEMEEN VOORTGEZET ONDERWIJS</p>");
		echo("<P class=maintext>De ondergetekenden verklaren dat <SPAN class=studname>". $student->get_lastname() . ", " . $student->get_firstname().
	      "</span></p><P>geboren ". dateprint($student->get_student_detail("s_ASBirthDate")).  " te ". 
				$student->get_student_detail("s_ASBirthCountry"). "</p><P>Heeft deelgenomen aan het eindexamen middelbaar algemeen voortgezet onderwijs conform</p><P>".
				"<SPAN class=toplabels>het profiel </SPAN><SPAN class=profname>". strtoupper($profile). "</span></p><P><SPAN class=toplabels>aan</SPAN><SPAN class=profname>". strtoupper($schoolname). "</span></p><P><SPAN class=toplabels>te</SPAN>". ($schoolname == 'het John Wesley College' || $schoolname == 'het Filomena College MAVO' ? "San Nicolas" : "Oranjestad"). "</p><P>Dit examen werd afgenomen in de zin van artikel 32 van de Landsverordening Voortgezet Onderwijs (A.B. 1989, no. GT 103).</p>");
		  
		// Check if there is an extra subject
		unset($ev);
		unset($ev2);
		unset($ev3);
		if(substr($package,-1) != ")")
		{  //student has extra subject(s)
			$evcomp = explode(" : ",$package);
			if(isset($evcomp[1]))
			{
				$evlist = explode(",",$evcomp[1]);
				
				if(isset($altsn[$evlist[0]]))
					$ev = $altsn[$evlist[0]];
				else
				  $ev=strtolower($evlist[0]);
				if(isset($evlist[1]))
					if(isset($altsn[$evlist[1]]))
						$ev2 = $altsn[$evlist[1]];
					else
						$ev2=strtolower($evlist[1]);
				if(isset($evlist[2]))
					if(isset($altsn[$evlist[2]]))
						$ev3 = $altsn[$evlist[2]];
					else
						$ev3=strtolower($evlist[2]);
			}
		}
	  
		// Now comes the table with results...
		echo("<table><tr><td class=centerfatbot rowspan=3>EXAMENVAKKEN</td><td class=centerfatbot colspan=4>CIJFERS BIJ HET EXAMEN VERKREGEN</td><tr><td class=centerfatbot rowspan=2>School-<BR>examen</td>
					 <td class=centerfatbot rowspan=2>Centraal<BR>examen</td><td class=centerfatbot colspan=2>Eindcijfer/beoordeling</td></tr>
				<tr><td class=centerfatbotdashr>in cijfers</td>
				 <td class=centerfatbot>in letters</td></tr>");
		// Fixed subjects
		echo("<tr class=graybg><td colspan=5>Gemeenschappelijk deel</td></tr>");
		echo("<tr><td>". $sub2full[$pksubs[$profid][0]]. "</td><td class=center>". 
					 (isset($results_array[2][$pksubs[$profid][0]]) ? $results_array[2][$pksubs[$profid][0]] : "-"). "</td><td class=center>".
					 (isset($results_array[3][$pksubs[$profid][0]]) ? $results_array[3][$pksubs[$profid][0]] : "-"). "</td><td class=centerdashr>".
					 (isset($results_array[0][$pksubs[$profid][0]]) ? $results_array[0][$pksubs[$profid][0]] : "-"). "</td><td class=center>".
					 (isset($results_array[0][$pksubs[$profid][0]]) ? $digittext[$results_array[0][$pksubs[$profid][0]]] : "-"). "</td></tr>");
		echo("<tr><td class=fatbot>". $sub2full[$pksubs[$profid][1]]. "</td><td class=centerfatbot>". 
					 (isset($results_array[2][$pksubs[$profid][1]]) ? $results_array[2][$pksubs[$profid][1]] : "-"). "</td><td class=centerfatbot>".
					 (isset($results_array[3][$pksubs[$profid][1]]) ? $results_array[3][$pksubs[$profid][1]] : "-"). "</td><td class=centerfatbotdashr>".
					 (isset($results_array[0][$pksubs[$profid][1]]) ? $results_array[0][$pksubs[$profid][1]] : "-"). "</td><td class=centerfatbot>".
					 (isset($results_array[0][$pksubs[$profid][1]]) ? $digittext[$results_array[0][$pksubs[$profid][1]]] : "-"). "</td></tr>");
		// Dit is de CKV paragraaf:
		/*$ckvres = inputclassbase::load_query("SELECT ckvres,xresult FROM examresult WHERE year='". $schoolyear. "' AND sid=". $student->get_id(). " ORDER BY lastmodifiedat DESC");
		if(isset($ckvres['ckvres']) && $ckvres['ckvres'][0] == 1)
			$ckvtxt = "voldoende";
		else
			$ckvtxt = "onvoldoende"; */
		unset($xresult);
		if(isset($ckvres['xresult']) && $ckvres['xresult'][0] != "")
			$xresult = $ckvres['xresult'][0];
		echo("<tr><td class=fatbot>". $sub2full["ckv"]. "</td><td class=centerfatbot>--</td><td class=centerfatbot>--</td><td class=centerfatbotdashr>");
		/* if(isset($ckvr[$student->get_id()][0]))
		{
			echo($ckvr[$student->get_id()][0]);
			$ckvtxt = $digittext[$ckvr[$student->get_id()][0]];
		}
		else
		{
			echo("--");
			$ckvtxt = "--";
		}
		
		echo("</td><td class=centerfatbot>". $ckvtxt. "</td></tr>"); */
		if(isset($ckvr[$student->get_id()][0]))
		{
			echo("--");
			$ckvtxt = $sdigittext[$ckvr[$student->get_id()][0]];
		}
		else
		{
			echo("--");
			$ckvtxt = "--";
		}
		
		echo("</td><td class=centerfatbot>". $ckvtxt. "</td></tr>");
		// LO Result
		/*if(isset($results_array[0]['lo']) && $results_array[0]['lo'] >= 5.5)
			$lotxt = "voldoende";
		else
			$lotxt = "onvoldoende";
		*/
		echo("<tr><td class=fatbot>". $sub2full["lo"]. "</td><td class=centerfatbot>--</td><td class=centerfatbot>--</td><td class=centerfatbotdashr>");
		if(isset($vrijst['LO']))
			$results_array[0]['lo'] = $vrijst['LO'];
		if(isset($results_array[0]['lo']))
		{
			//echo($results_array[0]['lo']);
			echo("--");
			$lotxt = $sdigittext[$results_array[0]['lo']];
		}
		else
		{
			echo("--");
			$lotxt = "--";
		}
		echo("</td><td class=centerfatbot>". $lotxt. "</td></tr>");
		// Profile subjects
		echo("<tr class=graybg><td colspan=5>Profieldeel</td></tr>");
		echo("<tr><td>". $sub2full[$pksubs[$profid][2]]. "</td><td class=center>". 
					 (isset($results_array[2][$pksubs[$profid][2]]) ? $results_array[2][$pksubs[$profid][2]] : "-"). "</td><td class=center>".
					 (isset($results_array[3][$pksubs[$profid][2]]) ? $results_array[3][$pksubs[$profid][2]] : "-"). "</td><td class=centerdashr>".
					 (isset($results_array[0][$pksubs[$profid][2]]) ? $results_array[0][$pksubs[$profid][2]] : "-"). "</td><td class=center>".
					 (isset($results_array[0][$pksubs[$profid][2]]) ? $digittext[$results_array[0][$pksubs[$profid][2]]] : "-"). "</td></tr>");
		echo("<tr><td>". $sub2full[$pksubs[$profid][3]]. "</td><td class=center>". 
					 (isset($results_array[2][$pksubs[$profid][3]]) ? $results_array[2][$pksubs[$profid][3]] : "-"). "</td><td class=center>".
					 (isset($results_array[3][$pksubs[$profid][3]]) ? $results_array[3][$pksubs[$profid][3]] : "-"). "</td><td class=centerdashr>".
					 (isset($results_array[0][$pksubs[$profid][3]]) ? $results_array[0][$pksubs[$profid][3]] : "-"). "</td><td class=center>".
					 (isset($results_array[0][$pksubs[$profid][3]]) ? $digittext[$results_array[0][$pksubs[$profid][3]]] : "-"). "</td></tr>");
		echo("<tr><td class=fatbot>". $sub2full[$pksubs[$profid][4]]. "</td><td class=centerfatbot>". 
					 (isset($results_array[2][$pksubs[$profid][4]]) ? $results_array[2][$pksubs[$profid][4]] : "-"). "</td><td class=centerfatbot>".
					 (isset($results_array[3][$pksubs[$profid][4]]) ? $results_array[3][$pksubs[$profid][4]] : "-"). "</td><td class=centerfatbotdashr>".
					 (isset($results_array[0][$pksubs[$profid][4]]) ? $results_array[0][$pksubs[$profid][4]] : "-"). "</td><td class=centerfatbot>".
					 (isset($results_array[0][$pksubs[$profid][4]]) ? $digittext[$results_array[0][$pksubs[$profid][4]]] : "-"). "</td></tr>");
		// Choice subject(s)
		echo("<tr class=graybg><td colspan=5>Keuzedeel</td></tr>");
		echo("<tr><td>". $sub2full[$pksubs[$profid][5]]. "</td><td class=center>". 
					 (isset($results_array[2][$pksubs[$profid][5]]) ? $results_array[2][$pksubs[$profid][5]] : "-"). "</td><td class=center>".
					 (isset($results_array[3][$pksubs[$profid][5]]) ? $results_array[3][$pksubs[$profid][5]] : "-"). "</td><td class=centerdashr>".
					 (isset($results_array[0][$pksubs[$profid][5]]) ? $results_array[0][$pksubs[$profid][5]] : "-"). "</td><td class=center>".
					 (isset($results_array[0][$pksubs[$profid][5]]) ? $digittext[$results_array[0][$pksubs[$profid][5]]] : "-"). "</td></tr>");
		if(isset($ev))
		{
			echo("<tr class=graybg><td colspan=5>Zevende vak</td></tr>");
			if(isset($ev2))
			{
				echo("<tr><td>". $sub2full[$ev]. "</td><td class=center>". 
						 (isset($results_array[2][$ev]) ? $results_array[2][$ev] : "-"). "</td><td class=center>".
						 (isset($results_array[3][$ev]) ? $results_array[3][$ev] : "-"). "</td><td class=centerdashr>".
						 (isset($results_array[0][$ev]) ? $results_array[0][$ev] : "-"). "</td><td class=center>".
						 (isset($results_array[0][$ev]) ? $digittext[$results_array[0][$ev]] : "-"). "</td></tr>");
				if(isset($ev3))
				{
					echo("<tr><td>". $sub2full[$ev2]. "</td><td class=center>". 
							 (isset($results_array[2][$ev2]) ? $results_array[2][$ev2] : "-"). "</td><td class=center>".
							 (isset($results_array[3][$ev2]) ? $results_array[3][$ev2] : "-"). "</td><td class=centerdashr>".
							 (isset($results_array[0][$ev2]) ? $results_array[0][$ev2] : "-"). "</td><td class=center>".
							 (isset($results_array[0][$ev2]) ? $digittext[$results_array[0][$ev2]] : "-"). "</td></tr>");
					echo("<tr><td class=fatbot>". $sub2full[$ev3]. "</td><td class=centerfatbot>". 
							 (isset($results_array[2][$ev3]) ? $results_array[2][$ev3] : "-"). "</td><td class=centerfatbot>".
							 (isset($results_array[3][$ev3]) ? $results_array[3][$ev3] : "-"). "</td><td class=centerfatbotdashr>".
							 (isset($results_array[0][$ev3]) ? $results_array[0][$ev3] : "-"). "</td><td class=centerfatbot>".
							 (isset($results_array[0][$ev3]) ? $digittext[$results_array[0][$ev3]] : "-"). "</td></tr>");
				}
				else
				{
					echo("<tr><td class=fatbot>". $sub2full[$ev2]. "</td><td class=centerfatbot>". 
							 (isset($results_array[2][$ev2]) ? $results_array[2][$ev2] : "-"). "</td><td class=centerfatbot>".
							 (isset($results_array[3][$ev2]) ? $results_array[3][$ev2] : "-"). "</td><td class=centerfatbotdashr>".
							 (isset($results_array[0][$ev2]) ? $results_array[0][$ev2] : "-"). "</td><td class=centerfatbot>".
							 (isset($results_array[0][$ev2]) ? $digittext[$results_array[0][$ev2]] : "-"). "</td></tr>");
				}				
			}
			else
			{
				echo("<tr><td class=fatbot>". $sub2full[$ev]. "</td><td class=centerfatbot>". 
						 (isset($results_array[2][$ev]) ? $results_array[2][$ev] : "-"). "</td><td class=centerfatbot>".
						 (isset($results_array[3][$ev]) ? $results_array[3][$ev] : "-"). "</td><td class=centerfatbotdashr>".
						 (isset($results_array[0][$ev]) ? $results_array[0][$ev] : "-"). "</td><td class=centerfatbot>".
						 (isset($results_array[0][$ev]) ? $digittext[$results_array[0][$ev]] : "-"). "</td></tr>");
			}
		}
		else
		{
			//echo("<tr><td class=fatbot>". $sub2full[$pksubs[$profid][5]]. "</td><td class=centerfatbot>". 
			//		 (isset($results_array[2][$pksubs[$profid][5]]) ? $results_array[2][$pksubs[$profid][5]] : "-"). "</td><td class=centerfatbot>".
			//		 (isset($results_array[3][$pksubs[$profid][5]]) ? $results_array[3][$pksubs[$profid][5]] : "-"). "</td><td class=centerfatbotdashr>".
			//		 (isset($results_array[0][$pksubs[$profid][5]]) ? $results_array[0][$pksubs[$profid][5]] : "-"). "</td><td class=centerfatbot>".
			//		 (isset($results_array[0][$pksubs[$profid][5]]) ? $digittext[$results_array[0][$pksubs[$profid][5]]] : "-"). "</td></tr>");
		}
		//echo("</table>");

		// Decide if student passed or failed exam
		$subjcount = 0;
		$negpoints = 0;
		$totpoints = 0;
		if(isset($results_array[0]))
			foreach($results_array[0] AS $ssn => $res)
			{
				if($ssn != "lo" && (in_array($ssn,$pksubs[$profid]) || $ssn == $ev))
				{
					$subjcount++;
					if($res < 6)
						$negpoints += 6 - $res;
					$totpoints += $res;
					//echo("<BR>Use subj ". $ssn. "<BR>");
				}
				//else
					//echo("<BR>". $ssn. " not in ". $package);
			}
			if($subjcount >= 6 && ($negpoints == 0 || ($negpoints == 1 && $totpoints >= ($subjcount * 6 - 1)) || ($negpoints == 2 && $totpoints >= ($subjcount * 6))))
				$passed = true;
			else
			{
				$passed = false;
				//echo("<BR>Afgwewezen: subjcount=". $subjcount. ", negpoints=". $negpoints. ", totpoints=". $totpoints. "<BR>");
			}

		echo("<TR><TD class=uitslag colspan=5>Uitslag van het examen: <SPAN class=studname>". (isset($xresult) ? $xresult : ($passed ? "Geslaagd" : "Afgewezen")). "</td></tr></table>");
		
		// Debugging:
		/*foreach($results_array[0] AS $skey => $dummy)
		  echo($skey. ",");
		echo("<BR>");
		foreach($pksubs[$profid] AS $ixxx)
		  echo($ixxx. ",");
		echo("<BR>");*/
		
		// Date depends on the day the 1st of july is actual, if saturday or sunday, the date 1 or 2 days earlier applies.
		$frstjulday = date("N",mktime(0,0,0,7,1,substr($schoolyear,-4)));
		if($frstjulday > 5)
			$dayoffset = $frstjulday - 5;
			else
				$dayoffset = 0;	
		echo("<P style='text-align: right; margin-right: 20%; margin-top: 20px;margin-bottom: 60px;'>Aruba, ". $_POST['rdate']. " ". date("Y"). "</p>");
		
		// Signoff area
		echo("<P class=leftsign>De directeur,  __________________</p>");
		echo("<P class=rightsign>Secretaris van het eindexamen,  ___________________________</p>");
		echo("<P class=pagebreak>&nbsp;</p>");
  }

  function dateprint($printdate = NULL)
  {
    if(isset($printdate))
		{
			if(substr($printdate,3,2) < 1)
				return $printdate;
			$pdate = mktime(0,0,0,(0+substr($printdate,3,2)),0+substr($printdate,0,2),0+substr($printdate,-4));
		}
		else
			$pdate = mktime(0,0,0,date("n"),date("j")+1,date("Y"));
		$months=array(1=>"januari","februari","maart","april","mei","juni","juli","augustus","september","oktober","november","december");
		return(date("j",$pdate). " ". $months[date("n",$pdate)]. " ". date("Y",$pdate));
  }
?>
