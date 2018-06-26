<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014 Aim4me N.V.   (http://www.aim4me.info)	      |
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
    echo("<P>Afdruk instellingen (Firefox):<BR>Marges op 0,2 inch (5,1 mm), <b>100% scaling</b>, geen header/footer (Blank), Portrait, A4.<BR>KIES EERST DE JUISTE GROEP!</P>"); 
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
  $schoolname = str_replace("het ","",$schoolname);
  $schoolname = str_replace("de ","",$schoolname);
  
  // Get the year
  $schoolyear = SA_loadquery("SELECT year FROM period");
  $schoolyear = $schoolyear['year'][1];
  
  // Get the group
  $mygroup = new group();
  $mygroup->load_current();
    
  // First part of the page
  echo("<html><head><title>Cijferlijst AHA</title></head><body link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style_Cijferlijst_AHA.css" title="style1">';
  
  // Translation of subject package to subjects (short names)
require_once("AHA_pksubs.php");  
  
  $sub2full = array("Ne"=>"Nederlandse taal en literatuur", "En"=>"Engelse taal en literatuur", "Wi-A"=>"Wiskunde A",
                    "Ak"=>"Aardrijkskunde", "Gs"=>"Geschiedenis en staatsinrichting", "Sp"=>"Spaanse taal en literatuur",
					"Ec"=>"Economie", "M&O"=>"Management en organisatie", "Sk"=>"Scheikunde", "Na"=>"Natuurkunde",
					"Wi-B"=>"Wiskunde B", "Bio"=>"Biologie","CKV"=>"Culturele en kunstzinnige vorming","Inf"=>"Informatica","Pa"=>"Papiamentse taal en cultuur","Fa"=>"Franse taal en literatuur");
  $digittext = array(1=>"een","twee","drie","vier","vijf","zes","zeven","acht","negen","tien");
  $noexam = array("Ak","Pfw","Inf");
	$coresubs = array("Ne","En","Wi-A","Wi-B");


  // Get a list of students
  $students = student::student_list($mygroup);

  if(isset($students))
  {
	echo("<P class=footnote>Doorhalingen en/of wijzigingen maken deze cijferlijst ongeldig.</p>");
    foreach($students AS $student)
     stud_grades($student, $schoolyear,$mygroup);
  } // End if student for the group
	
  echo("</html>");
    
  function stud_grades($student,$schoolyear,$group)
  {
    global $noexam;
		global $coresubs;
    $sid = $student->get_id();
    global $schoolname,$schoolyear,$pksubs,$sub2full,$digittext;
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
	
	$profid = substr($package,3,2);
	
    // Get the list of grades for normal periods
	unset($results_array);
	unset($results_prv);
	unset($prvyear);
    $sql_query = "SELECT gradestore.*,shortname FROM gradestore LEFT JOIN subject USING(mid) WHERE sid=". $student->get_id(). " ORDER BY period DESC, year DESC";
    $grades = inputclassbase::load_query($sql_query);
    if(isset($grades))
      foreach($grades['result'] AS $grix => $gres)
			{
				if($grades['year'][$grix] == $schoolyear)
				{
						if($grades['period'][$grix] > 0 && $gres > 0.0)
								$results_array[$grades['period'][$grix]][$grades['shortname'][$grix]] = number_format($gres,1,',','.');
					else
						if($gres > 0 && ((isset($results_array[2][$grades['shortname'][$grix]]) && isset($results_array[3][$grades['shortname'][$grix]])) || in_array($grades['shortname'][$grix],$noexam)))
									$results_array[$grades['period'][$grix]][$grades['shortname'][$grix]] = $gres;
				}
				else
				{
					if(isset($prvyear) && $prvyear != $grades['year'][$grix])
						unset($results_prv); // a newer year with results is entered now! So forget ealier year
						if($grades['period'][$grix] > 0 && $gres > 0.0)
								$results_prv[$grades['period'][$grix]][$grades['shortname'][$grix]] = number_format($gres,1,',','.');
					else
						if($gres > 0 && isset($results_prv[2][$grades['shortname'][$grix]]) && isset($results_prv[3][$grades['shortname'][$grix]]))
									$results_prv[$grades['period'][$grix]][$grades['shortname'][$grix]] = $gres;
				}
			}
	$certgrades = inputclassbase::load_query("SELECT * FROM excertdata LEFT JOIN subject USING(mid) WHERE sid=". $student->get_id());
	if(isset($certgrades['shortname']))
	{
	  foreach($certgrades['shortname'] AS $cgix => $csn)
	  {
	    $results_prv[2][$csn] = ($certgrades['seresult'][$cgix] > 0.0 ? number_format($certgrades['seresult'][$cgix],1,",",".") : "-");
	    $results_prv[3][$csn] = ($certgrades['exresult'][$cgix] > 0.0 ? number_format($certgrades['exresult'][$cgix],1,",",".") : "-");
	    $results_prv[0][$csn] = number_format($certgrades['endresult'][$cgix],0,",",".");
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
			//if(in_array($vmid,$pksubs[$profid])) // Don't understand why this condition was here!
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
				$certqr = inputclassbase::load_query("SELECT seresult,exresult FROM excertdata LEFT JOIN subject USING(mid) WHERE sid=". $student->get_id(). " AND shortname='". $ssn. "'");
				if(isset($certqr['seresult']))
				{
					$results_array[2][$ssn] = $certqr['seresult'][0];
					$results_array[3][$ssn] = $certqr['exresult'][0];
				}
				else
				{
					$results_array[2][$ssn] = "<span class=vcmark>Vrijst</span>";
					$results_array[3][$ssn] = "<span class=vcmark>Vrijst</span>";	
				}
			}
	  }
	if(isset($certs))
	  foreach($certs AS $ssn => $res)
	  {
			//echo("Processing cert for ". $ssn. "<BR>");
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
	$ahxqr = inputclassbase::load_query("SELECT shortname,xstatus FROM ahxdata LEFT JOIN subject USING(mid) WHERE sid=". $student->get_id(). " AND year='". $schoolyear. "'");
	if(isset($ahxqr))
	  foreach($ahxqr['shortname'] AS $vix => $vmid)
	    $ahx[$vmid] = $ahxqr['xstatus'][$vix];

  
	echo("<DIV class=schoollogo><img src=schoollogo.png height=100px width=100px></DIV><BR>");
  echo("<P class=maintitle>CIJFERLIJST</p>");
	echo("<P class=secondtitle>EINDEXAMEN ". substr($schoolyear,-4). "<BR>HOGER ALGEMEEN VOORTGEZET ONDERWIJS</p>");
	echo("<P class=maintext>De ondergetekenden verklaren dat <b>". $student->get_lastname() . ", " . $student->get_firstname().
	      "</b><BR>geboren <b>". dateprint($student->get_student_detail("s_ASBirthDate")).  "</b> in <b>". 
		  $student->get_student_detail("s_ASBirthCountry"). "</b><BR>Heeft deelgenomen aan het eindexamen hoger algemeen voortgezet onderwijs<BR>".
		  "conform het profiel <b>". $profile. "</b><BR>aan de <b>Avondhavo</b> te <b>Aruba</b></p>");
		  
	// Check if there is an extra subject
	unset($ev);
	unset($ev2);
	unset($ev3);
	if(substr($package,-1) != ")")
	{  //student has extra subject
	  $evcomp = explode(" : ",$package);
	  if(isset($evcomp[1]))
		{
			$evs = explode(" ",$evcomp[1]);
	    $ev=$evs[0];
		}
	  if(isset($evcomp[2]))
		{
			$evs = explode(" ",$evcomp[2]);
	    $ev2=$evs[0];
		}
	  if(isset($evcomp[3]))
		{
			$evs = explode(" ",$evcomp[3]);
	    $ev3=$evs[0];
		}
	}
	  
	// Now comes the table with results...
	echo("<table><tr><td class=boldfatbot rowspan=2>Examenvakken</td><td class=centerbolddashbot colspan=2>Cijfers toegekend:</td>
	       <td class=centerbolddashbot colspan=2>Eindcijfers:</td></tr>
		  <tr><td class=centerfatbot>S.E.</td><td class=centerfatbot>C.E.</td><td class=centerfatbotdashr>in cijfers</td>
		   <td class=fatbot>in letters</td></tr>");
	// Fixed subjects
	echo("<tr><td class=bold colspan=5>GEMEENSCHAPPELIJK DEEL</td></tr>");
	echo("<tr><td>". $sub2full[$pksubs[$profid][0]]. "</td><td class=center>". 
	       (isset($results_array[2][$pksubs[$profid][0]]) ? $results_array[2][$pksubs[$profid][0]] : "-"). "</td><td class=center>".
	       (isset($results_array[3][$pksubs[$profid][0]]) ? $results_array[3][$pksubs[$profid][0]] : "-"). "</td><td class=centerdashr>".
	       (isset($results_array[0][$pksubs[$profid][0]]) ? $results_array[0][$pksubs[$profid][0]] : "-"). "</td><td>".
	       (isset($results_array[0][$pksubs[$profid][0]]) ? $digittext[$results_array[0][$pksubs[$profid][0]]] : "-"). "</td></tr>");
	echo("<tr><td class=fatbot>". $sub2full[$pksubs[$profid][1]]. "</td><td class=centerfatbot>". 
	       (isset($results_array[2][$pksubs[$profid][1]]) ? $results_array[2][$pksubs[$profid][1]] : "-"). "</td><td class=centerfatbot>".
	       (isset($results_array[3][$pksubs[$profid][1]]) ? $results_array[3][$pksubs[$profid][1]] : "-"). "</td><td class=centerfatbotdashr>".
	       (isset($results_array[0][$pksubs[$profid][1]]) ? $results_array[0][$pksubs[$profid][1]] : "-"). "</td><td class=fatbot>".
	       (isset($results_array[0][$pksubs[$profid][1]]) ? $digittext[$results_array[0][$pksubs[$profid][1]]] : "-"). "</td></tr>");
	// Combi is built from I&S and PFW which can be given this year or aquired earlier. Here we determine...
	unset($isres);
	unset($pfwres);
	unset($combires);
	if(isset($results_array[0]['I&S']) && $results_array[0]['I&S'] > 0.0)
	  $isres = round($results_array[0]['I&S']);
	else if(isset($ahx['I&S']) && $ahx['I&S'] > 0.0)
	  $isres = round($ahx['I&S']);
	if(isset($results_array[0]['Pfw']) && $results_array[0]['Pfw'] > 0.0)
	  $pfwres = round($results_array[0]['Pfw']);
	else if(isset($ahx['Pfw']) && $ahx['Pfw'] > 0.0)
	  $pfwres = round($ahx['Pfw']);
	if(isset($isres) && isset($pfwres))
	  $combires = round(($isres + $pfwres) / 2,0);
	// Combi result line
	echo("<tr><td class=fatbot>Combinatiecijfer*</td><td class=fatbot>&nbsp;</td><td class=fatbot>&nbsp;</td><td class=centerfatbotdashr>".
	       (isset($combires) ? $combires : "-"). "</td><td class=fatbot>".
	       (isset($combires) ? $digittext[$combires] : "-"). "</td></tr>");
	// Profile subjects
	echo("<tr><td class=bold colspan=5>PROFIELDEEL</td></tr>");
	echo("<tr><td>". $sub2full[$pksubs[$profid][2]]. "</td><td class=center>". 
	       (isset($results_array[2][$pksubs[$profid][2]]) ? $results_array[2][$pksubs[$profid][2]] : "-"). "</td><td class=center>".
	       (isset($results_array[3][$pksubs[$profid][2]]) ? $results_array[3][$pksubs[$profid][2]] : "-"). "</td><td class=centerdashr>".
	       (isset($results_array[0][$pksubs[$profid][2]]) ? $results_array[0][$pksubs[$profid][2]] : "-"). "</td><td>".
	       (isset($results_array[0][$pksubs[$profid][2]]) ? $digittext[$results_array[0][$pksubs[$profid][2]]] : "-"). "</td></tr>");
	echo("<tr><td>". $sub2full[$pksubs[$profid][3]]. "</td><td class=center>". 
	       (isset($results_array[2][$pksubs[$profid][3]]) ? $results_array[2][$pksubs[$profid][3]] : "-"). "</td><td class=center>".
	       (isset($results_array[3][$pksubs[$profid][3]]) ? $results_array[3][$pksubs[$profid][3]] : "-"). "</td><td class=centerdashr>".
	       (isset($results_array[0][$pksubs[$profid][3]]) ? $results_array[0][$pksubs[$profid][3]] : "-"). "</td><td>".
	       (isset($results_array[0][$pksubs[$profid][3]]) ? $digittext[$results_array[0][$pksubs[$profid][3]]] : "-"). "</td></tr>");
	echo("<tr><td class=fatbot>". $sub2full[$pksubs[$profid][4]]. "</td><td class=centerfatbot>". 
	       (isset($results_array[2][$pksubs[$profid][4]]) ? $results_array[2][$pksubs[$profid][4]] : "-"). "</td><td class=centerfatbot>".
	       (isset($results_array[3][$pksubs[$profid][4]]) ? $results_array[3][$pksubs[$profid][4]] : "-"). "</td><td class=centerfatbotdashr>".
	       (isset($results_array[0][$pksubs[$profid][4]]) ? $results_array[0][$pksubs[$profid][4]] : "-"). "</td><td class=fatbot>".
	       (isset($results_array[0][$pksubs[$profid][4]]) ? $digittext[$results_array[0][$pksubs[$profid][4]]] : "-"). "</td></tr>");
	// Choice subject(s)
	echo("<tr><td class=bold colspan=5>KEUZEDEEL</td></tr>");
	if(isset($ev) || isset($ev2) || isset($ev3))
		$clspost = "";
	else
		$clspost = "fatbot";

	echo("<tr><td>". $sub2full[$pksubs[$profid][5]]. "</td><td class=center". $clspost. ">". 
			 (isset($results_array[2][$pksubs[$profid][5]]) ? $results_array[2][$pksubs[$profid][5]] : "-"). "</td><td class=center". $clspost. ">".
			 (isset($results_array[3][$pksubs[$profid][5]]) ? $results_array[3][$pksubs[$profid][5]] : "-"). "</td><td class=center". $clspost. "dashr>".
			 (isset($results_array[0][$pksubs[$profid][5]]) ? $results_array[0][$pksubs[$profid][5]] : "-"). "</td><td>".
			 (isset($results_array[0][$pksubs[$profid][5]]) ? $digittext[$results_array[0][$pksubs[$profid][5]]] : "-"). "</td></tr>");
	if(isset($ev2) || isset($ev3))
		$clspost = "";
	else
		$clspost = "fatbot";
	if(isset($ev))
		echo("<tr><td class=". $clspost. ">". $sub2full[$ev]. "</td><td class=center". $clspost. ">". 
			 (isset($results_array[2][$ev]) ? $results_array[2][$ev] : "-"). "</td><td class=center". $clspost. ">".
			 (isset($results_array[3][$ev]) ? $results_array[3][$ev] : "-"). "</td><td class=center". $clspost. "dashr>".
			 (isset($results_array[0][$ev]) ? $results_array[0][$ev] : "-"). "</td><td class=". $clspost. ">".
			 (isset($results_array[0][$ev]) ? $digittext[$results_array[0][$ev]] : "-"). "</td></tr>");
	if(isset($ev3))
		$clspost = "";
	else
		$clspost = "fatbot";
	if(isset($ev2))
		echo("<tr><td class=". $clspost. ">". $sub2full[$ev2]. "</td><td class=center". $clspost. ">". 
			 (isset($results_array[2][$ev2]) ? $results_array[2][$ev2] : "-"). "</td><td class=center". $clspost. ">".
			 (isset($results_array[3][$ev2]) ? $results_array[3][$ev2] : "-"). "</td><td class=center". $clspost. "dashr>".
			 (isset($results_array[0][$ev2]) ? $results_array[0][$ev2] : "-"). "</td><td class=". $clspost. ">".
			 (isset($results_array[0][$ev2]) ? $digittext[$results_array[0][$ev2]] : "-"). "</td></tr>");
	$clspost = "fatbot";
	if(isset($ev3))
		echo("<tr><td class=". $clspost. ">". $sub2full[$ev3]. "</td><td class=center". $clspost. ">". 
			 (isset($results_array[2][$ev3]) ? $results_array[2][$ev3] : "-"). "</td><td class=center". $clspost. ">".
			 (isset($results_array[3][$ev3]) ? $results_array[3][$ev3] : "-"). "</td><td class=center". $clspost. "dashr>".
			 (isset($results_array[0][$ev3]) ? $results_array[0][$ev3] : "-"). "</td><td class=". $clspost. ">".
			 (isset($results_array[0][$ev3]) ? $digittext[$results_array[0][$ev3]] : "-"). "</td></tr>");

  // Combi result buildup
	echo("<tr><td class=bold colspan=5>*Onderdelen van het combinatiecijfer</td></tr>");
	echo("<tr><td>Individu en samenleving/maatschappijleer</td><td class=center>". (isset($isres) ? number_format($isres,1,',','.') : "").
	      "</td><td>&nbsp;</td><td class=dashr>&nbsp;</td><td>&nbsp;</td></tr>");
	echo("<tr><td>Profielwerkstuk</td><td class=center>". (isset($pfwres) ? number_format($pfwres,1,',','.') : "").
	      "</td><td>&nbsp;</td><td class=dashr>&nbsp;</td><td>&nbsp;</td></tr>");
    echo("<tr><td colspan=5>Vak waarop het profielwerkstuk betrekking heeft: ". $student->get_student_detail("s_pfwvak").
	       "<BR>Titel / onderwerp: ". $student->get_student_detail("s_pfwtitel")."</td></tr>");
	echo("</table>");

	// Decide if student passed or failed exam
	$subjcount = 0;
	$negpoints = 0;
	$coreshort = 0;
	$totpoints = 0;
	$fullfail = 0;
	$fails = 0;
	$choicesubfail = 0;
	$extotval = 0.0;
	$excnt = 0.0;
	if(isset($results_array[0]))
	  foreach($results_array[0] AS $ssn => $res)
	  {
		 if(in_array($ssn,$pksubs[$profid]) && ((isset($results_array[3][$ssn]) && $results_array[3][$ssn] != "-") || in_array($ssn,$noexam) || isset($certs[$ssn]) || isset($vrijst[$ssn])))
		 {
	    $subjcount++;
			if($res < 6)
			{
				$negpoints += 6 - $res;
				if(in_array($ssn,$coresubs))
					$coreshort += 6 - $res;
			}
			$totpoints += $res;
			if($res < 4)
				$fullfail++;
			if($ssn == $pksubs[$profid][5] && $res < 6)
			$choicesubfail++;
			if(isset($results_array[3][$ssn]) && $results_array[3][$ssn] != "-")
			{
				$extotval += str_replace(",",".",$results_array[3][$ssn]);
				$excnt += 1.0;
				//echo("Added ". str_replace(",",".",$results_array[3][$ssn]). " to CSE avg for ". $ssn. "<BR>");
			}
		 }
	  }
	if(isset($combires))
	{
	  $subjcount++;
	  if($combires < 6)
	    $negpoints += 6 - $combires;
	  $totpoints += $combires;
	  if($combires < 4)
	    $fullfail++;
	}
	if(isset($ev) && isset($results_array[0][$ev]))
	{
	  $res = $results_array[0][$ev];
	  $subjcount++;
	  if($res < 6)
	  {
	    $negpoints += 6 - $res;
			$choicesubfail++;
			if(in_array($ev,$coresubs))
				$coreshort += 6 - $res;
	  }
	  $totpoints += $res;
	  if($res < 4)
	    $fullfail++;
		if(isset($results_array[3][$ev]) && $results_array[3][$ev] != "-")
		{
			$extotval += str_replace(",",".",$results_array[3][$ev]);
			$excnt += 1.0;
			//echo("Added ". str_replace(",",".",$results_array[3][$ev]). " to CSE avg for ". $ev. "<BR>");
		}
	}
	if(isset($ev2) && isset($results_array[0][$ev2]))
	{
	  $res = $results_array[0][$ev2];
	  $subjcount++;
	  if($res < 6)
	  {
	    $negpoints += 6 - $res;
			$choicesubfail++;
			if(in_array($ev2,$coresubs))
				$coreshort += 6 - $res;
	  }
	  $totpoints += $res;
	  if($res < 4)
	    $fullfail++;
		if(isset($results_array[3][$ev2]) && $results_array[3][$ev2] != "-")
		{
			$extotval += str_replace(",",".",$results_array[3][$ev2]);
			$excnt += 1.0;
			//echo("Added ". str_replace(",",".",$results_array[3][$ev]). " to CSE avg for ". $ev. "<BR>");
		}
	}
	if(isset($ev3) && isset($results_array[0][$ev3]))
	{
	  $res = $results_array[0][$ev3];
	  $subjcount++;
	  if($res < 6)
	  {
	    $negpoints += 6 - $res;
			$choicesubfail++;
			if(in_array($ev3,$coresubs))
				$coreshort += 6 - $res;
	  }
	  $totpoints += $res;
	  if($res < 4)
	    $fullfail++;
		if(isset($results_array[3][$ev3]) && $results_array[3][$ev3] != "-")
		{
			$extotval += str_replace(",",".",$results_array[3][$ev3]);
			$excnt += 1.0;
			//echo("Added ". str_replace(",",".",$results_array[3][$ev]). " to CSE avg for ". $ev. "<BR>");
		}
	}
	if($excnt > 0.0)
		$exavg = $extotval / $excnt;
	else
		$exavg = 0;
	//echo("AVG=". $exavg. "<BR>");
	// Changed on request Giovann Geerman: Certificates are equal to non certificate candidates, revoked 13th june 2018
	$certconditions = false;
	//$certconditions = isset($certs);
		if((($certconditions && $subjcount >= 8 && $negpoints == 0) ||
			 (!$certconditions && $subjcount >= 8 && $totpoints >= ($subjcount * 6 - 1) && $negpoints == 1) || 
			 (!$certconditions && $subjcount >= 8 && $totpoints >= ($subjcount * 6) && $negpoints <= 3 && $coreshort < 2) && $fullfail == 0 && $fails < 3) && $exavg >= 5.5)
	  $passed = true;
	else
	{
	  $passed = false;
		//echo("Not passed for (". $certconditions. ",". $subjcount. ",". $totpoints. ",". $negpoints. ",". $coreshort. ",". $fullfail. ",". $choicesubfail. ",". $exavg. "<BR>");
	}

	echo("<P>Uitslag van het eindexamen: <B>". ($passed ? "geslaagd" : "afgewezen"). "</b></p>");
	// Date depends on the day the 1st of july is actual, if saturday or sunday, the date 1 or 2 days earlier applies.
	$frstjulday = date("N",mktime(0,0,0,7,1,substr($schoolyear,-4)));
	if($frstjulday > 5)
	  $dayoffset = $frstjulday - 5;
    else
      $dayoffset = 0;	
	echo("<P>Aruba, ". $_POST['rdate']. " ". date("Y"). "</p>");
	
	// Signoff area
	echo("<P class=leftsign>Drs. N.L.M. Boekhouder-Wever<BR>(voorzitter examencommissie)</p>");
	echo("<P class=rightsign>E.K. Meyers, M. Ed.<BR>(secretaris examencommissie)</p>");
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
