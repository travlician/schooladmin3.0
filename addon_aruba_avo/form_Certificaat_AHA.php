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
//  Print with margins at 0.5 inch, no heading and footing!
//
  session_start();

  $login_qualify = 'ACT';
  require_once("schooladminfunctions.php");
  require_once("student.php");
  require_once("group.php");
  require_once("teacher.php");

	$coresubs = array("Ne","En","Wi-A","Wi-B");
	$coresubs = array("Ne","En","Wi-A","Wi-B");
  
  // First see if date is already typed, if not ask for it!
  if(!isset($_POST['rdate']))
  {
    echo("<P>Afdruk instellingen (Firefox):<BR>Marges op 0,2 inch, <b>100% scaling</b>, geen header/footer (Blank), Portrait, A4.<BR>KIES EERST DE JUISTE GROEP!</P>"); 
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
  echo("<html><head><title>Certificaat AHA</title></head><body link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style_Certificaat_AHA.css" title="style1">';
  
  // Translation of subject package to subjects (short names)
	require_once("AHA_pksubs.php");  
  $pksubs[0] = array('Ne','En','Wi-A','Ak','Gs','Sp','Ec','M&O','Bio','Wi-B','Na','Sk','Pa','Inf');
  
  $sub2full = array("Ne"=>"Nederlandse taal en literatuur", "En"=>"Engelse taal en literatuur", "Wi-A"=>"Wiskunde A",
                    "Ak"=>"Aardrijkskunde", "Gs"=>"Geschiedenis en staatsinrichting", "Sp"=>"Spaanse taal en literatuur",
					"Ec"=>"Economie", "M&O"=>"Management en organisatie", "Sk"=>"Scheikunde", "Na"=>"Natuurkunde",
					"Wi-B"=>"Wiskunde B", "Bio"=>"Biologie","CKV"=>"Culturele en kunstzinnige vorming","Inf"=>"Informatica","Pa"=>"Papiamentse taal en cultuur","Fa"=>"Franse taal en literatuur");
  $digittext = array(1=>"een","twee","drie","vier","vijf","zes","zeven","acht","negen","tien");
  $noexam=array("Ak","Inf");
  

  // Get a list of students
  $students = student::student_list($mygroup);

  if(isset($students))
  {
		echo("<P class=footnote>Doorhalingen en/of wijzigingen maken dit certificaat ongeldig.</p>");
    foreach($students AS $student)
     stud_grades($student, $schoolyear,$mygroup);
  } // End if student for the group
	
  echo("</html>");
    
  function stud_grades($student,$schoolyear,$group)
  {
    global $noexam, $coresubs;
    $sid = $student->get_id();
    global $schoolname,$schoolyear,$pksubs,$sub2full,$digittext;

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
	  unset($profile);
	if(isset($profile))
	  $profid = substr($package,3,2);
	else
	  $profid=0;
	
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
				if(isset($prvyear) && $prvyear != $grades['year'][$grix])
					unset($results_prv); // a newer year with results is entered now! So forget ealier year
					if($grades['period'][$grix] > 0 && $gres > 0.0)
							$results_prv[$grades['period'][$grix]][$grades['shortname'][$grix]] = number_format($gres,1,',','.');
				else
					if($gres > 0 && isset($results_prv[2][$grades['shortname'][$grix]]) && isset($results_prv[3][$grades['shortname'][$grix]]))
								$results_prv[$grades['period'][$grix]][$grades['shortname'][$grix]] = $gres;
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
	
	// If a "vrijstelling" or certificate is applicable, see if we get results from excertdata.
	$excertdataqr = inputclassbase::load_query("SELECT * FROM excertdata LEFT JOIN subject USING(mid) WHERE sid=". $student->get_id(). " ORDER BY year DESC");
	if(isset($excertdataqr['sid']))
		foreach($excertdataqr['sid'] AS $cdix => $sid)
		{
			$results_prv[2][$excertdataqr['shortname'][$cdix]] = $excertdataqr['seresult'][$cdix];
			$results_prv[3][$excertdataqr['shortname'][$cdix]] = $excertdataqr['exresult'][$cdix];
			$results_prv[2][$excertdataqr['shortname'][$cdix]] = $excertdataqr['endresult'][$cdix];
		}

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

	// Combi is built from I&S and PFW which can be given this year or aquired earlier. Here we determine...
	unset($isres);
	unset($pfwres);
	unset($combires);
	if(isset($results_array[0]['I&S']) && $results_array[0]['I&S'] > 0.0)
	  $isres = $results_array[0]['I&S'];
	else if(isset($ahx['I&S']) && $ahx['I&S'] > 0.0)
	  $isres = $ahx['I&S'];
	if(isset($results_array[0]['Pfw']) && $results_array[0]['Pfw'] > 0.0)
	  $pfwres = $results_array[0]['Pfw'];
	else if(isset($ahx['Pfw']) && $ahx['Pfw'] > 0.0)
	  $pfwres = $ahx['Pfw'];
	if(isset($isres) && isset($pfwres))
	  $combires = round(($isres + $pfwres) / 2,0);
	// Decide if student passed or failed exam
	$subjcount = 0;
	$negpoints = 0;
	$totpoints = 0;
	$fullfail = 0;
	$fails = 0;
	$choicesubfail = 0;
	$coreshort = 0;
	$extotval = 0.0;
	$excnt = 0.0;

	// Need to know which subjects are in the package
	unset($mysubs);
	$mysubsqr = inputclassbase::load_query("SELECT shortname FROM s_package LEFT JOIN subjectpackage USING(packagename) LEFT JOIN subject USING(mid) WHERE sid=". $student->get_id(). " AND shortname != 'I&S' AND shortname != 'Pfw'");
	if(isset($mysubsqr['shortname']))
		foreach($mysubsqr['shortname'] AS $ssn)
			$mysubs[$ssn] = $ssn;

	if(isset($results_array[0]))
	  foreach($results_array[0] AS $ssn => $res)
	  {
			if(in_array($ssn,$mysubs))
			{
				$subjcount++;
				if(isset($results_array[3][$ssn]) && $results_array[3][$ssn] > 0)
					$exres = str_replace(",",".",$results_array[3][$ssn]);
				else if(isset($results_prv[3][$ssn]))
					$exres = $results_prv[3][$ssn];
				else
					unset($exres);
				if($res < 6)
				{
						$negpoints += 6 - $res;
						//echo("Negpoints set to ". $negpoints. " after processing ". $ssn. "<BR>");
						if(in_array($ssn,$coresubs))
							$coreshort += 6 - $res;
						//else
							//echo($ssn. " not in coresubs<BR>");
				}
				$totpoints += $res;
				if(isset($exres))
				{
					$extotval += $exres;
					$excnt++;
					//echo("extotval set to ". $extotval. " (". $exres. ") after evaluating ". $ssn. " and excnt=". $excnt. "<BR>");
				}
				if($res < 4)
					$fullfail++;
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
	  }
	  $totpoints += $res;
	  if($res < 4)
	    $fullfail++;
		if(isset($results_array[3][$ev]) && $results_array[3][$ev] > 0)
			$exres = str_replace(",",".",$results_array[3][$ev]);
		else if(isset($results_prv[3][$ev]))
			$exres = $results_prv[3][$ev];
		else
			unset($exres);
		if(isset($exres))
		{
			$extotval += $exres;
			$excnt++;
			//echo("extotval set to ". $extotval. " (". $exres. ") after evaluating ". $ev. " and excnt=". $excnt. "<BR>");
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
	  }
	  $totpoints += $res;
	  if($res < 4)
	    $fullfail++;
		if(isset($results_array[3][$ev3]) && $results_array[3][$ev3] > 0)
			$exres = str_replace(",",".",$results_array[3][$ev3]);
		else if(isset($results_prv[3][$ev3]))
			$exres = $results_prv[3][$ev3];
		else
			unset($exres);
		if(isset($exres))
		{
			$extotval += $exres;
			$excnt++;
			//echo("extotval set to ". $extotval. " (". $exres. ") after evaluating ". $ev3. " and excnt=". $excnt. "<BR>");
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
	  }
	  $totpoints += $res;
	  if($res < 4)
	    $fullfail++;
		if(isset($results_array[3][$ev2]) && $results_array[3][$ev2] > 0)
			$exres = str_replace(",",".",$results_array[3][$ev2]);
		else if(isset($results_prv[3][$ev2]))
			$exres = $results_prv[3][$ev2];
		else
			unset($exres);
		if(isset($exres))
		{
			$extotval += $exres;
			$excnt++;
			//echo("extotval set to ". $extotval. " (". $exres. ") after evaluating ". $ev. " and excnt=". $excnt. "<BR>");
		}
	}
	if($excnt > 0.0)
		$exavg = $extotval / $excnt;
	else
		$exavg = 0;
	//$certconditions = isset($certs);
	$certconditions = false;
		if((($certconditions && $subjcount >= 8 && $negpoints == 0) ||
			 (!$certconditions && $subjcount >= 8 && $totpoints >= ($subjcount * 6 - 1) && $negpoints == 1) || 
			 (!$certconditions && $subjcount >= 8 && $totpoints >= ($subjcount * 6) && $negpoints <= 3 && $coreshort < 2) && $fullfail == 0 && $fails < 3) && $exavg >= 5.5)
	  $passed = true;
	else
	  $passed = false;
	  
	if($passed)
	{
		//echo("No certificate for ". $student->get_lastname(). ", ". $student->get_firstname(). " bacause passed exam (coreshort=". $coreshort. ", exavg=". $exavg. ")<BR>");
	  return; // If passed, a diploma will be issued 
	}
	//echo("Not passed, subjcount=". $subjcount. ", negpoints=". $negpoints. ", totpoints=". $totpoints. ", coreshort=". $coreshort. ", fullfail=". $fullfail. ", exavg=". $exavg. "<BR>");
	// Now find out if any certificates apply!
	unset($certprint);
	// Debugging
	//echo("Evaluating ". $student->get_lastname() . ", " . $student->get_firstname(). "<BR>");
	// 
	if(isset($pksubs[$profid]))
	foreach($pksubs[$profid] AS $sbix => $ssn)
	{
	  if(!isset($certs[$ssn]) && 
	     !isset($vrijst[$ssn]) && 
		 isset($results_array[0][$ssn]) && 
		 $results_array[0][$ssn] > 5.5 &&
		 (!isset($ev) || $ssn!=$ev))
		 {
	     $certprint[$sbix] = $ssn;
			 // Debugging
			 //echo("Has cert for ". $ssn. "<BR>");
		 }
		// Debugging
		//else
			//echo("No cert for ". $ssn. "<BR>");
	}
	if(isset($ev) && !isset($certs[$ev]) && !isset($vrijst[$ev]) && isset($results_array[0][$ev]) && $results_array[0][$ev] > 5.5)
	  $certprint[1000] = $ev;
	if(isset($ev2) && !isset($certs[$ev2]) && !isset($vrijst[$ev2]) && isset($results_array[0][$ev2]) && $results_array[0][$ev2] > 5.5)
	  $certprint[1001] = $ev2;
	if(isset($ev3) && !isset($certs[$ev3]) && !isset($vrijst[$ev3]) && isset($results_array[0][$ev3]) && $results_array[0][$ev3] > 5.5)
	  $certprint[1002] = $ev3;
	
	if(!isset($certprint))
	  return; // no subjects qualified for certificate so nothing printed.
	  
	echo("<p class=certheader><img class=topimg src=emptyshield.gif></p>");
	echo("<p class=certheader>CERTIFICAAT</p>");
	echo("<p>Het bestuur van de Stichting Avond Onderwijs Aruba verklaart dat:</p>");
    echo("<P><b>". $student->get_lastname() . ", " . $student->get_firstname(). "</b></p>");
    echo("<P>geboren <b>". dateprint($student->get_student_detail("s_ASBirthDate")). "</b> te <b>". $student->get_student_detail("s_ASBirthCountry"). "</b></p>");
	echo("<P>in het jaar ". substr($schoolyear,-4). " aan de Avondhavo Aruba (deel) examen voor</p>");
	// Type of exam, depends on groupname. Containing Vwo of VWO for higher level.
	$grpn = strtolower($_SESSION['CurrentGroup']);
	if(strpos($grpn,"vwo") !== false)
	  echo("<P class=examtype>VOORBEREIDEND WETENSCHAPPELIJK ONDERWIJS</p>");
	else
	  echo("<P class=examtype>HOGER ALGEMEEN VOORTGEZET ONDERWIJS</p>");
	echo("<P>heeft afgelegd.</p>");
	// Now text depends on single or multiple subjects
	if(count($certprint) > 1)
	  echo("<P>In de hieronder vermelde vakken heeft hij/zij een voldoende eindcijfer behaald.");
	else
	  echo("<P>In het hieronder vermelde vak heeft hij/zij een voldoende eindcijfer behaald.");
	echo("<BR>Op grond daarvan wordt dit certificaat uitgereikt. Tevens zijn de deelcijfers vermeld.</p>");
	
	// Now show the table with results.
	echo("<table><tr><td rowspan=2 class=bold>Examenvak</td><td class=boldcenter colspan=2>Cijfers toegekend:</td>
	        <td class=bold colspan=2>Eindcijfer:</td></tr><tr><td class=boldcenter>S.E.</td><td class=boldcenter>C.E.</td>
			<td class=bold>Cijfer</td><td class=bold>In&nbsp;letters</td></tr>");
	foreach($certprint AS $ssn)
	{
	  //echo("<tr><td>". strtoupper($sub2full[$ssn]). "</td><td class=center>". $results_array[2][$ssn]. 
	  echo("<tr><td>". $sub2full[$ssn]. "</td><td class=center>". $results_array[2][$ssn]. 
	         "</td><td class=center>". (isset($results_array[3][$ssn]) ? $results_array[3][$ssn] : "-"). "</td><td class=center>". $results_array[0][$ssn].
			 "</td><td>". $digittext[$results_array[0][$ssn]]. "</td></tr>");
	}
	echo("</table>");
	echo("<P>Aruba, ". $_POST['rdate']. " ". date("Y"). "</p>");
	echo("<DIV class=signtop>De geëxamineerde</DIV>");
	echo("<DIV class=signtop2>De voorzitter van het Bestuur,<BR>voor deze,</DIV><BR>");
	echo("<DIV class=signbotclear>De geëxamineerde</DIV>");
	echo("<DIV class=signbot>Drs. N.L.M. Boekhouder-Wever<BR>directeur</DIV>");
	echo("<DIV class=signbot3>E.K. Meyers, M. Ed.<BR>secretaris</DIV>");
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
