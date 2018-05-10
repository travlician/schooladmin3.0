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
//  Print with margins at 0.5 inch, no heading and footing!
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
    echo("<P>De diploma's worden afgedrukt op een HP Laserjet Pro 200 Printer.</p>");
    echo("<P>Afdruk instellingen (Firefox):<BR>Marges 6mm boven en 5,1mm links, rechts en onder, <b>100% scaling</b>, geen header/footer (Blank), Portrait, A4.<BR>KIES EERST DE JUISTE GROEP!</P>");
		echo("<P>Printer instellingen: Paper type=Heavy Rough, Print on both side=Yes, flip over, Color options=Black & White. Plaats voor de achterkant (die komt eerst) de diplomas met het logo naar de voorkant van de printer en de achterkant boven. Voor de voorkant met het logo naar de achterkant van de printer en de voorkant naar boven.</p>");
    echo("<FORM name=rdatefrm id=rdatefrm METHOD=POST ACTION=". $_SERVER['PHP_SELF']. ">Datum (zonder jaartal!): <INPUT TYPE=TEXT SIZE=40 NAME=rdate><INPUT TYPE=SUBMIT NAME='OK' VALUE='OK'></FORM>");
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
  echo("<html><head><title>Diploma AHA</title></head><body link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style_Diploma_AHA.css" title="style1">';
  
  // Translation of subject package to subjects (short names)
  $pksubs['01'] = array('Ne','En','Wi-A','Ak','Gs','Sp');
  $pksubs['02'] = array('Ne','En','Wi-A','Ec','Gs','Sp');
  $pksubs['03'] = array('Ne','En','Wi-A','Ec','Ak','Sp');
  $pksubs['04'] = array('Ne','En','Wi-A','Ak','Gs','M&O');
  $pksubs['05'] = array('Ne','En','Wi-A','Ec','Gs','M&O');
  $pksubs['06'] = array('Ne','En','Wi-A','Ec','Ak','M&O');
  $pksubs['07'] = array('Ne','En','Wi-A','Ec','Ak','Gs');
  $pksubs['08'] = array('Ne','En','Wi-A','Ak','Gs','Bio');
  $pksubs['09'] = array('Ne','En','Wi-A','Ec','Gs','Bio');
  $pksubs['10'] = array('Ne','En','Wi-A','Ec','Ak','Bio');
  $pksubs['11'] = array('Ne','En','Sp','Ak','Gs','M&O');
  $pksubs['12'] = array('Ne','En','Sp','Ak','Gs','Ec');
  $pksubs['13'] = array('Ne','En','Wi-A','Sk','Bio','Sp');
  $pksubs['14'] = array('Ne','En','Wi-A','Sk','Bio','Ec');
  $pksubs['15'] = array('Ne','En','Wi-A','Na','Sk','Sp');
  $pksubs['16'] = array('Ne','En','Wi-A','Na','Sk','Bio');
  $pksubs['17'] = array('Ne','En','Wi-A','Na','Sk','Ec');
  $pksubs['18'] = array('Ne','En','Wi-B','Sk','Bio','Sp');
  $pksubs['19'] = array('Ne','En','Wi-B','Sk','Bio','Ec');
  $pksubs['20'] = array('Ne','En','Wi-B','Na','Sk','Sp');
  $pksubs['21'] = array('Ne','En','Wi-B','Na','Sk','Bio');
  $pksubs['22'] = array('Ne','En','Wi-B','Na','Sk','Ec');
  // HU 90+ with CKV!
  $pksubs['96'] = array('Ne','En','Sp','Ak','CKV','Ec');
  $pksubs['97'] = array('Ne','En','Wi-A','Ak','Gs','Ec');
  $pksubs['98'] = array('Ne','En','Sp','Gs','CKV','Wi-A');
  $pksubs['99'] = array('Ne','En','Sp','Ak','CKV','Wi-A');
  
  $sub2full = array("Ne"=>"Nederlandse taal en literatuur", "En"=>"Engelse taal en literatuur", "Wi-A"=>"Wiskunde A",
                    "Ak"=>"Aardrijkskunde", "Gs"=>"Geschiedenis en staatsinrichting", "Sp"=>"Spaanse taal en literatuur",
					"Ec"=>"Economie", "M&O"=>"Management en organisatie", "Sk"=>"Scheikunde", "Na"=>"Natuurkunde",
					"Wi-B"=>"Wiskunde B", "Bio"=>"Biologie","CKV"=>"Culturele en kunstzinnige vorming");
  $noexam = array("Ak","CKV","Pfw");
	$coresubs = array("Ne","En","Wi-A","Wi-B");
  $digittext = array(1=>"een","twee","drie","vier","vijf","zes","zeven","acht","negen","tien");
  

  // Get a list of students
  $students = student::student_list($mygroup);

  if(isset($students))
  {
    foreach($students AS $student)
     stud_grades($student, $schoolyear,$mygroup);
  } // End if student for the group
	
  echo("</html>");
    
  function stud_grades($student,$schoolyear,$group)
  {
		global $coresubs;
    $sid = $student->get_id();
    global $schoolname,$schoolyear,$pksubs,$sub2full,$digittext,$noexam;

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
	  return; // Don;t show this if no predefined profile is applicable
	
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
					if($gres > 0 && isset($results_array[2][$grades['shortname'][$grix]]) && 
						(isset($results_array[3][$grades['shortname'][$grix]]) || in_array($grades['shortname'][$grix],$noexam)))
								$results_array[$grades['period'][$grix]][$grades['shortname'][$grix]] = $gres;
			}
			else
			{
				if(isset($prvyear) && $prvyear < $grades['year'][$grix])
				{
					$prvyear = $grades['year'][$grix];
					unset($results_prv); // a newer year with results is entered now! So forget ealier year
				}
				else if(!isset($prvyear))
					$prvyear = $grades['year'][$grix];
					
				if($grades['period'][$grix] > 0 && $gres > 0.0 && $grades['year'][$grix] == $prvyear)
					$results_prv[$grades['period'][$grix]][$grades['shortname'][$grix]] = number_format($gres,1,',','.');
				else
					if($gres > 0 && isset($results_prv[2][$grades['shortname'][$grix]]) && isset($results_prv[3][$grades['shortname'][$grix]]) && $grades['year'][$grix] == $prvyear)
						$results_prv[$grades['period'][$grix]][$grades['shortname'][$grix]] = $gres;
			}
		}
	
	// Get "Vrijstellingen" and certificates
	unset($vrijst);
	unset($certs);
	$vrcertqr = inputclassbase::load_query("SELECT shortname,xstatus,mid FROM ex45data LEFT JOIN subject USING(mid) WHERE sid=". $student->get_id(). " AND year='". $schoolyear. "' AND xstatus > 4 AND mid>0");
	if(isset($vrcertqr))
	  foreach($vrcertqr['shortname'] AS $vix => $vmid)
	  {
	    if($vrcertqr['xstatus'][$vix] < 9)
	      $vrijst[$vmid] = $vrcertqr['xstatus'][$vix]+2;
			else
			{
				if(in_array($vmid,$pksubs[$profid]))
					$certs[$vmid] = $vrcertqr['xstatus'][$vix]-3;
			}
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
			$results_array[0][$ssn] = $res; // set end result
			//echo("SET result 0 for ". $ssn. "<BR>");
	    if(isset($results_prv[0][$ssn]) && $results_prv[0][$ssn] == $res)
			{ // result from previous year is present and matches set result, so can use these results for this list
				$results_array[2][$ssn] = $results_prv[2][$ssn];
				$results_array[3][$ssn] = $results_prv[3][$ssn];
			}
			else
			{ // No valid result from previous year present, extract from excertdata table
				$certqr = inputclassbase::load_query("SELECT seresult,exresult FROM excertdata LEFT JOIN subject USING(mid) WHERE sid=". $student->get_id(). " AND shortname='". $ssn. "'");
				if(isset($certqr['seresult']))
				{
					$results_array[2][$ssn] = $certqr['seresult'][0];
					$results_array[3][$ssn] = $certqr['exresult'][0];
				}
				else
				{
					$results_array[2][$ssn] = "<span class=vcmark>Cert</span>";
					$results_array[3][$ssn] = "<span class=vcmark>Cert</span>";		  
				}
			}
	  }

	// Get prefilled I&S and PFW results
	unset($ahx);
	$ahxqr = inputclassbase::load_query("SELECT shortname,xstatus FROM ahxdata LEFT JOIN subject USING(mid) WHERE sid=". $student->get_id(). " AND year='". $schoolyear. "'");
	if(isset($ahxqr))
	  foreach($ahxqr['shortname'] AS $vix => $vmid)
	    $ahx[$vmid] = $ahxqr['xstatus'][$vix];

	// Check if there is an extra subject
	unset($ev);
	unset($ev2);
	unset($ev3);
	if(substr($package,-1) != ")")
	{  //student has extra subject
	  $evcomp = explode(" : ",$package);
	  if(isset($evcomp[1]))
		{
			$evs = explode(",",$evcomp[1]);
	    $ev=$evs[0];
			if(isset($evs[1]))
				$ev2 = $evs[1];
			if(isset($evs[2]))
				$ev3 = $evs[2];
		}
	}

	// Combi is built from I&S and PFW which can be given this year or aquired earlier. Here we determine...
	unset($isres);
	unset($pfwres);
	unset($combires);
	if(isset($results_array[0]['I&S']) && $results_array[0]['I&S'] > 0.0)
	  round($isres = $results_array[0]['I&S']);
	else if(isset($ahx['I&S']) && $ahx['I&S'] > 0.0)
	  $isres = round($ahx['I&S']);
	if(isset($results_array[0]['Pfw']) && $results_array[0]['Pfw'] > 0.0)
	  $pfwres = round($results_array[0]['Pfw']);
	else if(isset($ahx['Pfw']) && $ahx['Pfw'] > 0.0)
	  $pfwres = round($ahx['Pfw']);
	if(isset($isres) && isset($pfwres))
	  $combires = round(($isres + $pfwres) / 2,0);
	// Decide if student passed or failed exam
	$subjcount = 0;
	$negpoints = 0;
	$coreshort = 0;
	$totpoints = 0;
	$fullfail = 0;
	$fails = 0;
	$choicesubfail = 0;
	$extotval = 0.0;
	$excnt = 0;
	if(isset($results_array[0]))
	  foreach($results_array[0] AS $ssn => $res)
	  {
			if($ssn != "I&S" && $ssn != "Pfw" && in_array($ssn,$pksubs[$profid]) && (isset($results_array[3][$ssn]) || in_array($ssn,$noexam) || isset($certs[$ssn]) || isset($vrijst[$ssn])))
			{
				$subjcount++;
				//echo("Added subject ". $ssn. "<BR>");
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
				if(isset($results_array[3][$ssn]))
				{
					$extotval += str_replace(",",".",$results_array[3][$ssn]);
					$excnt++;
					//echo("Added ". $results_array[3][$ssn]. " for ". $ssn. "<BR>");
				}
			}
			//else
				//echo("Subject ". $ssn. " NOT added<BR>");
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
		if(isset($results_array[3][$ev]))
		{
			//echo("Added ". $results_array[3][$ev]. " for ". $ev. "<BR>");
			$extotval += str_replace(",",".",$results_array[3][$ev]);
			$excnt++;
		}
	}
	if($excnt > 0)
		$exavg = $extotval / (1.0 * $excnt);
	else
		$exavg = 0;
	$certconditions = isset($certs);
	if(($certconditions && $subjcount >= 7 && $negpoints == 0) ||
	   (!$certconditions && $subjcount >= 7 && $totpoints >= ($subjcount * 6 - 1) && $negpoints == 1) || 
	   (!$certconditions && $subjcount >= 7 && $totpoints >= ($subjcount * 6) && $negpoints <= 2 && $coreshort <= 1) ||
	   (!$certconditions && $subjcount >= 8 && $totpoints >= ($subjcount * 6) && $negpoints == 3 && $coreshort <= 1 && $fullfail == 0 && ($fails - $choicesubfail) <= 1))
	{
		if($exavg >= 5.5)
	    $passed = true;
		else
		{
			$passed = false;
		}
	}
	else
	{
	  $passed = false;
	}
	  
	if(!$passed)
	{
	  //echo($student->get_lastname(). ",". $student->get_firstname(). " Not passed (". $certconditions. ",". $subjcount. ",". $negpoints. ",". $totpoints. ",". $coreshort. ",". $fullfail. ",". $fails. ",". $choicesubfail. ",". round($exavg,2). ",". $extotval. ",". $excnt. ")<BR>");
	  return;
	}
 
	echo("<P class=schoolname>de Avondhavo Aruba</p>");
	echo("<P class=studentname>". $student->get_firstname() . " " . $student->get_lastname(). "</p>");
	echo("<P class=birthdate>". dateprint($student->get_student_detail("s_ASBirthDate")). "</p>");
	echo("<P class=birthplace>". $student->get_student_detail("s_ASBirthCountry"). "&nbsp;</p>");
	echo("<P class=profile>". $profile. "</p>");
	echo("<P class=schoolyear>". substr($schoolyear,-4). "</p>");
	//echo("<P class=schoolplace>Aruba</p>");
	echo("<P class=diplomadate>". $_POST['rdate']. " ". substr($schoolyear,-4). "</p>");
	// echo("<P class=diplomayear>". substr($schoolyear,-2). "</p>");
	echo("<P class=pagebreak>&nbsp;</p>");
	// Common part
	for($vk=1; $vk <=2; $vk++)
	{
	  echo("<P class=subjectline". $vk. ">". $sub2full[$pksubs[$profid][$vk-1]]. "</p>");
	}
	echo("<P class=subjectline3>Individu en samenleving/maatschappijleer</p>");
	for($vk=4; $vk <=5; $vk++) // Empty lines to invalidate
	{
	  echo("<P class=subjectline". $vk. ">- - - - -</p>");
	}
	// Profile part
	for($vk=6; $vk <=8; $vk++)
	{
	  echo("<P class=subjectline". $vk. ">". $sub2full[$pksubs[$profid][$vk-4]]. "</p>");
	}
	// Choice part
	echo("<P class=subjectline9>". $sub2full[$pksubs[$profid][5]]. "</p>");
	if(isset($ev))
	  echo("<P class=subjectline10>". $sub2full[$ev]. "</p>");
	else
	  echo("<P class=subjectline10>- - - - -</p>");
	
	echo("<P class=subjectline11>". $student->get_student_detail("s_pfwvak")."&nbsp;</p>");
	echo("<P class=subjectline12>- - - - -</p>");
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
