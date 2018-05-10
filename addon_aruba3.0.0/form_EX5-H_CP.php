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
  include ("schooladminfunctions.php");
  
  // Subject translation tables
  $offsubjects = array(1 => "Ne","En","Sp","Pa","Wa","Wb","Na","Sk","Bi","Mo","Ec","Gs","Ak","CKV");
  $noexam = array("Ak");
  $altsubjects = array("Ne"=>1,"En"=>2,"Sp"=>3,"Wi-A"=>5,"Wi-B"=>6,"Na"=>7,"Sk"=>8,"Bio"=>9,"Ec"=>11,"M&O"=>10,"Ak"=>13,"Gs"=>12,"Pfw"=>15,"CKV"=>14,
                       "ne"=>1,"en"=>2,"sp"=>3,"pap"=>4,"wiA"=>5,"wiB"=>6,"naBB"=>7,"skBB"=>8,"bio"=>9,"ec"=>11,"m&o"=>10,"ak"=>13,"gs"=>12,"pfw"=>15,"ckv"=>14);
  $countries = array("AUA" => "Aruba", "NED" => "Nederland", "BON" => "Bonaire", "CUR" => "Curaçao", "SXM" => "Sint Maarten", "SUR" => "Suriname",
                     "COL" => "Colombia", "CHI" => "Chili", "CHN" => "China", "DOM" => "Dominicaanse Republiek", "HTI" => "Haïti", "JAM" => "Jamaica",
					 "PER" => "Peru", "PHL" => "Philipijnen", "USA" => "Verenigde Staten van Amerika", "CUB" => "Cuba", "VEN" => "Venezuela",
					 "PAN" => "Panama");
  $choicesubjs = array("MM 01"=>"Sp","MM 02"=>"Sp","MM 03"=>"Sp","MM 04"=>"Mo","MM 05"=>"Mo","MM 06"=>"Mo","MM 07"=>"Gs",
                       "MM 08"=>"Bi","MM 09"=>"Bi","MM 10"=>"Bi","HU 11"=>"Mo","HU 12"=>"Ec","NW 13"=>"Sp","NW 14"=>"Ec",
					   "NW 15"=>"Sp","NW 16"=>"Bi","NW 17"=>"Ec","NW 18"=>"Sp","NW 19"=>"Ec","NW 20"=>"Sp","NW 21"=>"Bi",
					   "NW 22"=>"Ec",
                       "MM01"=>"Sp","MM02"=>"Sp","MM03"=>"Sp","MM04"=>"Mo","MM05"=>"Mo","MM06"=>"Mo","MM07"=>"Gs",
                       "MM08"=>"Bi","MM09"=>"Bi","MM10"=>"Bi","HU11"=>"Mo","HU12"=>"Ec","NW13"=>"Sp","NW14"=>"Ec",
					   "NW15"=>"Sp","NW16"=>"Bi","NW17"=>"Ec","NW18"=>"Sp","NW19"=>"Ec","NW20"=>"Sp","NW21"=>"Bi",
					   "NW22"=>"Ec");
	$coresubs = array("Ne","En","Wa","Wb");
  $sub2full = array("Ne"=>"Nederlandse taal en literatuur", "En"=>"Engelse taal en literatuur", "Wa"=>"Wiskunde A",
                    "Ak"=>"Aardrijkskunde", "Gs"=>"Geschiedenis en staatsinrichting", "Sp"=>"Spaanse taal en literatuur",
					"Ec"=>"Economie", "Mo"=>"Management en organisatie", "Sk"=>"Scheikunde", "Na"=>"Natuurkunde",
					"Wb"=>"Wiskunde B", "Bi"=>"Biologie","CKV"=>"Culturele en kunstzinnige vorming");
	$valuetext = array(0=>"-","Zeer<BR>slecht","Slecht","Zeer<BR>onvoldoende","Onvoldoende","Voldoende","Voldoende","Ruim<BR>voldoende","Goed","Zeer<BR>goed","Uitmuntend");
	$subcor=1;
  
  // Functions
  function get_initials($name)
  {
    $explstring = explode(" ",$name);
    $retstr = "";
    foreach($explstring AS $addstr)
      $retstr .= " ". substr($addstr,0,1);
    return $retstr;
  }
  
  function print_head()
  {
    global $schoolyear,$schoolname,$subjects,$pageno;
		echo("<span class=toprow1>VERZAMELLIJST:</SPAN>");
		echo("<SPAN class=toprow2>". $schoolname. "</SPAN>");
		echo("<SPAN class=toprow3>Afdeling: HAVO - Hoger Algemeen Voortgezet Onderwijs</SPAN>");
		echo("<SPAN class=toprow4>Schooljaar: ". $schoolyear. "</SPAN>");
		
		echo("<table class=ex5table border=0><tr><td class=nobot>&nbsp;</td><td class=lastname>Achternaam</td><td class=firstnamehead>Voornamen</td><td class=fatbotgreen rowspan=2>v<BR>m</td><td class=fatbotgreen rowspan=2><center>pro-<BR>fiel</center></td><td colspan=9 class=gemdeel><b>Gemeenschappelijk deel</td>");
		$skipsubj=0;
		foreach($subjects['shortname'] AS $sn)
		{
			if($skipsubj < 2)
				$skipsubj++;
			else
				echo("<td rowspan=2 class=subjheader>$sn</td>");
		}
		echo("<td class=fatbotgreen rowspan=2><center>TOT<BR>TV1</td><td class=fatbotgreen rowspan=2><center>Gem<BR>e</td><td colspan=2 class=nobotgreen><center>Uitslag 1ste tijdvak</center></td><td colspan=6 class=herhead><b><center>Herex</center></b></td>");
		echo("<td class=nobotgreen colspan=3><center><b>Einduitslag</b></center></td></tr>");
		
		echo("<tr><td class=fatbot>Ex-no</td><td class=fatbotgreenleft>Geb. datum/Land</td><td class=fatbotgreen>&nbsp;</td><td class=subjheadergem>ckv</td><td class=subjheadergem>lo</td><td class=subjheadergem colspan=3>Combi</td><td class=subjheadergem>&nbsp;</td>");
		$skipsubj = 0;
		foreach($subjects['shortname'] AS $sn)
			if($skipsubj < 2)
			{
				echo("<td class=subjheadergem>$sn</td>");
			$skipsubj++;
			}
		echo("<td class=subjheadergem>Re</td>");
		
		echo("<td class=fatbotgreen><center>gesl</center></td><td class=fatbotgreen><center>afgew</center><td class=herbot>vak</td><td class=herbot>cijf</td><td class=herbot>vak</td><td class=herbot>cijf</td><td class=herbot>vak</td><td class=herbot>cijf</td>");
		echo("<td class=fatbotgreen>TOT</td><td class=fatbotgreen>G e</td>");
		echo("<td class=fatbotgreen>&nbsp</td></tr>");
	
  }
  
  function print_foot()
  {
    global $pageno;
		echo("</TABLE>");
		echo("<p class=footer><span class=formdata>EX 5-H (". date('n/j/Y; g:i A'). ") Bron: myschoolresults.com</span><span class=pagenr>Volgnummer ". $pageno++. "</span>");
		echo("<span class=dirsign>Handtekening directeur: _________________________________</span></p>");
  }
  
  
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
  
  // Get a list of subjects applicable to the exam subjects
  $subjectsqr = SA_loadquery("SELECT shortname,subjectpackage.mid FROM subjectpackage LEFT JOIN subject USING(mid) GROUP BY mid ORDER BY mid");

  // Get subject based on defined standard using translation tables as defined at start of this file.
  foreach($offsubjects AS $osix => $sjname)
  {
    foreach($subjectsqr['shortname'] AS $sbix => $subsn)
		{
			if(isset($altsubjects[$subsn]) && $altsubjects[$subsn] == $osix)
			{
				$subjects['shortname'][$osix] = $sjname;
				$subjects['mid'][$osix] = $subjectsqr['mid'][$sbix];
				$mids[$sjname] = $subjectsqr['mid'][$sbix];
				$mid2sjname[$subjectsqr['mid'][$sbix]] = $sjname;
			}
		}
  }
	
	// Get the mid of lo, it is not to be counted when counting subjects
	$lomidqr = SA_loadquery("SELECT mid FROM subject WHERE shortname='lo'");
	$lomid = $lomidqr['mid'][1];
  
  // Get the data of the exam subject collections
  $packages = SA_loadquery("SELECT * FROM subjectpackage WHERE packagename LIKE 'MM%' OR packagename LIKE 'HU%' OR packagename LIKE 'NW%'");
  // Reformat in array to get easier compares in pass/fail for 8 subjects
  foreach($packages['packagename'] AS $pnix => $pkname)
  {
    $packmids[$pkname][$pnix] = $packages['mid'][$pnix];
  }
  
  // Get a list of students with the subject package and extra subject
  $squery = "SELECT sid,lastname,firstname,s_exnr.data AS exnr,packagename,extrasubject,extrasubject2,extrasubject3,s_ASGender.data AS gender,";
  $squery .= " s_ASBirthDate.data AS bdate, arubacom.c_country.tekst AS bplace,s_pfwvak.data AS pfwvak FROM student";
  $squery .= " LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid)";
  $squery .= " LEFT JOIN s_ASBirthDate USING (sid) LEFT JOIN s_ASBirthCountry USING(sid) LEFT JOIN arubacom.c_country ON(s_ASBirthCountry.data=arubacom.c_country.id)";
  $squery .= " LEFT JOIN s_exnr USING(sid) LEFT JOIN s_package USING(sid) LEFT JOIN s_ASGender USING(sid) LEFT JOIN s_pfwvak USING(sid)";
  $squery .= " WHERE active=1 AND s_exnr.data IS NOT NULL AND s_exnr.data > '0' AND groupname='ExamHavo' ORDER BY s_exnr.data";
  $studs = SA_loadquery($squery);
  echo(mysql_error($userlink));
	
	// Preset soresults and exam results based on data from excertdata...
  $excertdata = SA_loadquery("SELECT excertdata.*,CONCAT(lastname,', ',firstname) AS studname,shortname FROM excertdata LEFT JOIN student USING(sid) 
                               LEFT JOIN subject USING(mid) LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid)
							   WHERE active=1 AND groupname LIKE 'Exam%' GROUP BY sid,mid ORDER BY lastname,firstname,year");
	if(isset($excertdata['sid']))
	{
		foreach($excertdata['sid'] AS $xcix => $sid)
		{
			$soarray[$sid][$excertdata['mid'][$xcix]] = $excertdata['seresult'][$xcix];
			$hexarray[$sid][$excertdata['mid'][$xcix]] = $excertdata['exresult'][$xcix];
			$exarray[$sid][$excertdata['mid'][$xcix]] = $excertdata['exresult'][$xcix];
		}
	}
	
  
  // Get SO results
  $cquery = "SELECT sid,mid,result FROM gradestore WHERE period=2 AND year=\"". $schoolyear. "\"";
  $cres = SA_loadquery($cquery);
  echo(mysql_error($userlink));
  if(isset($cres))
  {
    foreach($cres['sid'] AS $cix => $csid)
		{
			$soarray[$csid][$cres['mid'][$cix]] = $cres['result'][$cix];
		}
  }
  // Get Exam results
  $cquery = "SELECT sid,mid,result FROM gradestore WHERE period=3 AND year=\"". $schoolyear. "\"";
  $cres = SA_loadquery($cquery);
  echo(mysql_error($userlink));
  if(isset($cres))
  {
    foreach($cres['sid'] AS $cix => $csid)
		{
			$exarray[$csid][$cres['mid'][$cix]] = $cres['result'][$cix];
		}
  }
  // Get End results
  $cquery = "SELECT sid,mid,result FROM gradestore WHERE period=0 AND year=\"". $schoolyear. "\"";
  $cres = SA_loadquery($cquery);
  echo(mysql_error($userlink));
  if(isset($cres))
  {
    foreach($cres['sid'] AS $cix => $csid)
	{
	  $endarray[$csid][$cres['mid'][$cix]] = $cres['result'][$cix];
	}
  }
  
  // Get exam results based on pre-retry exam results
  $cquery = "SELECT sid,mid,result AS avgresult FROM testresult LEFT JOIN testdef USING(tdid) LEFT JOIN class USING(cid) WHERE year=\"". $schoolyear. "\" AND period=3 AND type=\"Exam\"";
  $cquery .= " GROUP BY sid, mid, testdef.date ORDER BY testdef.date DESC";
  $cres = SA_loadquery($cquery);
  echo(mysql_error($userlink));
  if(isset($cres))
    foreach($cres['sid'] AS $cix => $csid)
	{
	  $hexarray[$csid][$cres['mid'][$cix]] = round($cres['avgresult'][$cix],1);
	}

  // Get retry exam results 
  $cquery = "SELECT sid,mid,result AS avgresult FROM testresult LEFT JOIN testdef USING(tdid) LEFT JOIN class USING(cid) WHERE year=\"". $schoolyear. "\" AND period=3 AND type=\"Exam\" AND short_desc='Hex' AND result IS NOT NULL";
  $cquery .= " GROUP BY sid, mid, testdef.date ORDER BY testdef.date";
  $cres = SA_loadquery($cquery);
  echo(mysql_error($userlink));
  if(isset($cres))
    foreach($cres['sid'] AS $cix => $csid)
	{
	  $herxarray[$csid][$cres['mid'][$cix]] = round($cres['avgresult'][$cix],1);
	}

  // Get exam status data
  $cres = SA_loadquery("SELECT sid,mid,xstatus FROM ex45data WHERE xstatus>0 AND year='". $schoolyear. "'");
  if(isset($cres))
    foreach($cres['sid'] AS $cix => $csid)
	{
	  $exstatus[$csid][$cres['mid'][$cix]] = $cres['xstatus'][$cix];
	}
  $cres = SA_loadquery("SELECT sid,mid,xstatus FROM ahxdata WHERE xstatus>0 AND year='". $schoolyear. "'");
  if(isset($cres))
    foreach($cres['sid'] AS $cix => $csid)
	{
	  $ahxdata[$csid][$cres['mid'][$cix]] = $cres['xstatus'][$cix];
	}
	
  // Get exams year result texts
  $cres = SA_loadquery("SELECT sid,xresult FROM examresult WHERE xresult IS NOT NULL AND year='". $schoolyear. "'");
  if(isset($cres))
    foreach($cres['sid'] AS $cix => $csid)
	  $exr[$csid] = $cres['xresult'][$cix]; 

  // Get the I&S info gotten this year
  $ismidqr = SA_loadquery("SELECT mid FROM subject WHERE shortname LIKE 'I&S'");
  if(isset($ismidqr['mid']))
    $ismid = $ismidqr['mid'][1];
  else
    $ismid = 0;
	$isresq = SA_loadquery("SELECT sid,xstatus FROM ahxdata WHERE year='". $schoolyear. "' AND mid=". $ismid. " AND xstatus > 0");
	if(isset($isresq['xstatus']))
		foreach($isresq['sid'] AS $six => $asid)
// Requested june 12 2017: maintain with 1 digit! Revoked june 30th 2017!
			$isres[$asid] = round($isresq['xstatus'][$six]);
//			$isres[$asid] = round($isresq['xstatus'][$six],1);
	// if I&S is not entered in EX1-5 entry, it may be retrievable from previous year(s)
	$isresq = SA_loadquery("SELECT sid,result FROM gradestore WHERE period=0 AND mid=". $ismid. " ORDER BY year DESC");
	if(isset($isresq['result']))
		foreach($isresq['sid'] AS $six => $asid)
			if(!isset($isres[$asid]))
// Requested june 12 2017: maintain with 1 digit! Revoked june 30th 2017!
				$isres[$asid] = round($isresq['result'][$six]);	   
//				$isres[$asid] = round($isresq['result'][$six],1);	   

  // Get the PFW info gotten this year
  $pfwmidqr = SA_loadquery("SELECT mid FROM subject WHERE shortname LIKE 'Pfw' OR shortname LIKE 'pws'");
  if(isset($pfwmidqr['mid']))
    $pfwmid = $pfwmidqr['mid'][1];
  else
    $pfwmid = 0;
	$pfwresq = SA_loadquery("SELECT sid,xstatus FROM ahxdata WHERE year='". $schoolyear. "' AND mid=". $pfwmid. " AND xstatus > 0");
	if(isset($pfwresq['xstatus']))
		foreach($pfwresq['sid'] AS $six => $asid)
			$pfwres[$asid] = round($pfwresq['xstatus'][$six]);
  $pfwresqr = SA_loadquery("SELECT sid,result FROM gradestore WHERE mid=". $pfwmid. " AND year='". $schoolyear. "' AND period=0 AND result > 0");
  if(isset($pfwresqr['sid']))
    foreach($pfwresqr['sid'] AS $lix => $lsid)
			if(!isset($pfwres[$lsid]))
				$pfwres[$lsid] = round($pfwresqr['result'][$lix]);

  // Convert I&S and PFW results and calculate result combivak
  foreach($studs['sid'] AS $ipsid)
  {
    if(!isset($isres[$ipsid]) && isset($ahxdata[$ipsid][$ismid]) && $ahxdata[$ipsid][$ismid] > 0)
// Requested june 12 2017: maintain with 1 digit! Revoked june 30th 2017!
			$isres[$ipsid] = round($ahxdata[$ipsid][$ismid]);
//			$isres[$ipsid] = round($ahxdata[$ipsid][$ismid],1);
    if(!isset($pfwres[$ipsid]) && isset($ahxdata[$ipsid][$pfwmid]) && $ahxdata[$ipsid][$pfwmid] > 0)
			$pfwres[$ipsid] = round($ahxdata[$ipsid][$pfwmid],1);
		if(isset($isres[$ipsid]) && isset($pfwres[$ipsid]))
			$combires[$ipsid] = round(($isres[$ipsid] + $pfwres[$ipsid]) / 2,0);
  }
  
  // Get the CKV info gotten this year
  $ckvmidqr = SA_loadquery("SELECT mid FROM subject WHERE shortname LIKE 'ckv'");
  if(isset($ckvmidqr['mid']))
    $ckvmid = $ckvmidqr['mid'][1];
  else
    $ckvmid = 0;
	$ckvresq = SA_loadquery("SELECT sid,xstatus FROM ahxdata WHERE year='". $schoolyear. "' AND mid=". $ckvmid. " AND xstatus > 0");
	if(isset($ckvresq['xstatus']))
		foreach($ckvresq['sid'] AS $six => $asid)
			$ckvres[$asid] = round($ckvresq['xstatus'][$six]);
	// if CKV is not entered in EX1-5 entry, it may be retrievable from previous year(s)
	$ckvresq = SA_loadquery("SELECT sid,result FROM gradestore WHERE period=0 AND mid=". $ckvmid. " ORDER BY year DESC");
	if(isset($ckvresq['result']))
		foreach($ckvresq['sid'] AS $six => $asid)
			if(!isset($ckvres[$asid]))
				$ckvres[$asid] = round($ckvresq['result'][$six]);	   

	// Get the previous year (since LO needs to use previous year results)
	$prevyrqr = SA_loadquery("SELECT DISTINCT year FROM testdef ORDER BY year DESC");
	$prevyear = $prevyrqr['year'][2];

  $loresq = "SELECT sid,result,year FROM gradestore LEFT JOIN subject USING(mid) WHERE period=0 AND shortname='lo' AND year='". $schoolyear. "'";
  $loresqr = SA_loadquery($loresq);
  if(isset($loresqr['sid']))
  {
    foreach($loresqr['sid'] AS $loix => $losid)
		{
			$loval[$losid] = $loresqr['result'][$loix];
		}
  }
	// Get the LO averge which is average of this year and previous year trimester values (not the year results!)
	$loavgqr = SA_loadquery("SELECT sid,AVG(result) AS loavg FROM gradestore LEFT JOIN subject USING(mid) WHERE (year='". $schoolyear. "' OR year='". $prevyear. "') AND period<>0 AND shortname='lo' GROUP BY sid");
	if(isset($loavgqr['loavg']))
		foreach($loavgqr['sid'] AS $six => $asid)
			if(isset($loval[$asid]))
				$lores[$asid] = round($loavgqr['loavg'][$six]);
	$loresq = SA_loadquery("SELECT sid,xstatus FROM ahxdata LEFT JOIN subject USING(mid) WHERE year='". $schoolyear. "' AND shortname LIKE 'lo' AND xstatus > 0");
	if(isset($loresq['xstatus']))
		foreach($loresq['sid'] AS $six => $asid)
			$lores[$asid] = round($loresq['xstatus'][$six]);

	// Get the Rekenen results
	$rekmidqr = SA_loadquery("SELECT mid FROM subject WHERE fullname='Rekenen'");
	if(isset($rekmidqr['mid']))
		$rekmid = $rekmidqr['mid'][1];
	else
		$rekmid = 9999;
	$rekresqr = SA_loadquery("SELECT sid,result FROM gradestore WHERE mid=". $rekmid. " AND period=0 ORDER BY year");
	if(isset($rekresqr['result']))
	{
		foreach($rekresqr['sid'] AS $rrix => $asid)
		{
			$rekres[$asid] = $rekresqr['result'][$rrix];
		}
	}

  SA_closeDB();
  
  // Get final results based on pre-retry exam results
  if(isset($soarray))
  foreach($soarray AS $xsid => $mres)
    foreach($mres AS $xmid => $sores)
	{
	  if(isset($hexarray[$xsid][$xmid]))
	    $hendarray[$xsid][$xmid] = round(($sores + $hexarray[$xsid][$xmid]) / 2.0,0);
	}

  // First part of the page
  echo("<html><head><title>Formulier EX. 5-H</title></head><body link=blue vlink=blue onfload=\"window.print();setTimeout('window.close();',10000);\">");
  echo '<LINK rel="stylesheet" type="text/css" href="style_EX5-H.css" title="style1">';
  
  $pageno = 1;
  print_head();
  $linecount = 0;
  
  $passTV1m = 0;
  $passTV1v = 0;
  $passTV2m = 0;
  $passTV2v = 0;
  $totm = 0;
  $totv = 0;
  $missTV1m = 0;
  $missTV1v = 0;
  $missTV2m = 0;
  $missTV2v = 0;
  
  foreach($studs['sid'] AS $six => $sid)
  // Student listing
  {
    if($linecount > 9)
	{
    print_foot();
	  $linecount = 0;
	  print_head();
	}
	$miss = false;
	
	// show exam nr and names
	// Must translate packagename first
	$pn = substr($studs['packagename'][$six],0,2);
	if($pn != "MM" && $pn != "HU" && $pn != "NW")
	  $pn = "DC";
	else
	{ // If the student has a certificate, he/she is going for certificates only, so must mark DC before profile
	  if(isset($exstatus[$studs['sid'][$six]]))
	  {
	    $hascert = false;
	    foreach($exstatus[$studs['sid'][$six]] AS $xmid => $xstat)
		  if($xstat >= 9 && $xstat <= 13)
		    $hascert = true;
		if($hascert)
		  $pn = "DC<BR>". $pn;
	  }
	}
    echo("<TR><TD class=exnrnobot>". $studs['exnr'][$six]. "</TD>");
    echo("<TD class=nobot>". $studs['lastname'][$six]. "</TD><TD rowspan=3 class=firstname> ". $studs['firstname'][$six]. "</TD>");
	echo("<TD rowspan=3 class=dbot>". $studs['gender'][$six]. "</td><TD rowspan=3 class=dbot>". $pn. "</td>");
	echo("<TD rowspan=3 class=". (isset($ckvres[$sid]) && $ckvres[$sid] > 4 ? "ckvv>" : "ckvnv>") . (isset($ckvres[$sid]) ? $valuetext[$ckvres[$sid]] : "-"). "</TD>");
	echo("<TD rowspan=3 class=". (isset($lores[$sid]) && $lores[$sid] > 4 ? "ckvv>" : "ckvnv>") . (isset($lores[$sid]) ? $valuetext[$lores[$sid]] : "-"). "</TD>");
	echo("<TD>I&S</TD>");
	// row below should reflect I&S result with color code depending on ...
	if(isset($ahxdata[$studs['sid'][$six]][$ismid]))
	{
	    echo("<TD class=free>");
	}
	else
	  echo("<TD class=result>");
	if(isset($isres[$studs['sid'][$six]]))
	  echo($isres[$studs['sid'][$six]]);
	else
	  echo("&nbsp;");
	echo("</td>");
	echo("<TD>&nbsp;</TD>");
	
	echo("<td>se</td>");
	if($studs['gender'][$six] == "m")
	  $totm++;
	else
	  $totv++;
	$subixx=0;
	foreach($subjects['mid'] AS $mid)
	{
      $hassubject = 0;
	  // check for subjects here!
	  foreach($packages['packagename'] AS $subix => $pname)
	  {
	    if($pname == $studs['packagename'][$six] && $mid == $packages['mid'][$subix])
	    $hassubject = 1;
	  }
	  if($mid == $studs['extrasubject'][$six])
	    $hassubject = 1;
	  if($mid == $studs['extrasubject2'][$six])
	    $hassubject = 1;
	  if($mid == $studs['extrasubject3'][$six])
	    $hassubject = 1;
      if($hassubject != 0)
	  {
	    // Show SO result
	    echo("<TD class=result>");
			// Vrijstelling?
			if(isset($soarray[$studs['sid'][$six]][$mid]))
				if(strtolower(substr($soarray[$studs['sid'][$six]][$mid],0,1)) == "v")
					echo("&nbsp;");
				else
						echo(number_format($soarray[$studs['sid'][$six]][$mid],1,",","."));
			else
				if(isset($exstatus[$studs['sid'][$six]][$mid]) && $exstatus[$studs['sid'][$six]][$mid] >= 5)
					echo("&nbsp;");
				else
					echo("X");
			echo("</TD>");
	  }
	  else
	  { // Does not have the subject
	    echo("<TD class=notchoosen>&nbsp</td>");
	  }
	  // Rules for passing exam:
	  // - vrijstelling telt als cijfer wat vorige keer is gegeven
	  // - Certificaat telt als cijfer maar als er minstens 1 certificaat is moet alles voldoende zijn
	  // - 1 x 5 en de rest voldoende
	  // - max 2 vijven en gemiddeld 6 of meer
	  // - max 1 vier zonder andere onvoldoendes en gemiddeld 6 of meer
	  // Bij 8 vakken mag er ook 3 x 5 of 1 x 4 en 1 x 5 voorkomen, bij 3 x 5 moeten dan 2 onvoldoendes in de keuzevakken zijn
	  // - 1 drie of lager => jammer dan
	  // Speciaal voor 8de vakken: 
	  // 7 vakken binnen profiel volgens bovenstaande regels
	  // 
		$subixx++;
		if($subixx == 2)
			echo("<TD class=result>-</td>");
	}
	echo("<td class=nobot>&nbsp;</td><td class=nobot>&nbsp;</td><td class=nobot>&nbsp;</td><td class=nobot>&nbsp;</td>");
	// Now we might have a repeated subject
	$repcnt = 0;
	foreach($subjects['mid'] AS $mid)
	{
	  if(isset($exstatus[$sid][$mid]) && ($exstatus[$sid][$mid] == 1 || $exstatus[$sid][$mid] == 2 || $exstatus[$sid][$mid] == 4) && $repcnt < 3)
	  {  // Do this repeated subject
	     echo("<td class=nobot>&nbsp</td>");
	     echo("<TD class=result>");
				if(isset($soarray[$studs['sid'][$six]][$mid]))
					if(strtolower(substr($soarray[$studs['sid'][$six]][$mid],0,1)) == "v")
						echo("&nbsp;");
					else
							echo(number_format($soarray[$studs['sid'][$six]][$mid],1,",","."));
				else
					if(isset($exstatus[$studs['sid'][$six]][$mid]) && $exstatus[$studs['sid'][$six]][$mid] >= 5)
						echo("&nbsp;");
					else
						echo("X");

			 echo("</td>");
		$repcnt++;
	  }
	}
	// Fill non used subject spaces
	for($i=$repcnt; $i<3; $i++)
	  echo("<td class=nobot>&nbsp;</td><td class=nobot>&nbsp;</td>");
  // First row ends with 3 blank coloumns or 2 blank, endresult
  echo("<td class=nobot>&nbsp;</td><td class=nobot>&nbsp;</td>");
	if(isset($exr[$sid]))
	  echo("<td class=dbot rowspan=3><b>". nl2br($exr[$sid]). "</b></td>");
	else
	  echo("<td class=nobot>&nbsp;</td>");
    echo("</TR>");

    // Second row for each student	
    echo("<TR><TD class=exnrnobot>&nbsp;</TD>");
    echo("<TD class=nobot>". $studs['bdate'][$six]. "</TD><td>Pws</td>");
	//  row below should reflect Pfw result and color coded depending on...
	if(isset($ahxdata[$studs['sid'][$six]][$pfwmid]))
	{
	  echo("<TD class=free>");
	}
	else
	  echo("<TD class=result>");
	if(isset($pfwres[$studs['sid'][$six]]))
	  echo($pfwres[$studs['sid'][$six]]);
	else
	  echo("&nbsp;");
	echo("</td>");
	
	// Show PWS subject
	$pfwvak = "&nbsp;";
	foreach($sub2full AS $vks => $vkf)
		if($vkf == $studs['pfwvak'][$six])
			$pfwvak = $vks;
	echo("<td>". $pfwvak."</td><td>cs</td>");

	$subixx=0;
	foreach($subjects['mid'] AS $mix => $mid)
	{
      $hassubject = 0;
	  // check for subjects here!
	  foreach($packages['packagename'] AS $subix => $pname)
	  {
	    if($pname == $studs['packagename'][$six] && $mid == $packages['mid'][$subix])
	    $hassubject = 1;
	  }
	  if($mid == $studs['extrasubject'][$six])
	    $hassubject = 1;
	  if($mid == $studs['extrasubject2'][$six])
	    $hassubject = 1;
	  if($mid == $studs['extrasubject3'][$six])
	    $hassubject = 1;
      if($hassubject != 0)
	  {
	    // Show exam result
		if(isset($exstatus[$sid][$mid]))
		{
		  echo("<TD ");
		  if($exstatus[$sid][$mid] == 1)
		    echo("class=repeat");
		  else if($exstatus[$sid][$mid] == 2)
		    echo("class=absentm");
		  else if($exstatus[$sid][$mid] == 3)
		    echo("class=absents");
		  else if($exstatus[$sid][$mid] == 4)
		    echo("class=absents");
		  echo(">");
		  if($exstatus[$sid][$mid] > 1 && $exstatus[$sid][$mid] < 5)
		    $miss = true;
		}
		else
	      echo("<TD class=result>");
		// Vrijstelling?
    if(isset($hexarray[$studs['sid'][$six]][$mid]))
		  if(strtolower(substr($hexarray[$studs['sid'][$six]][$mid],0,1)) == "v")
		    echo("&nbsp;");
		  else
	      echo(number_format($hexarray[$studs['sid'][$six]][$mid],1,",","."));
	    else if(in_array($subjects['shortname'][$mix],$noexam))
			{ // we set an exam result to fake exam being done...
				if(isset($soarray[$studs['sid'][$six]][$mid]))
					$hendarray[$studs['sid'][$six]][$mid] = $soarray[$studs['sid'][$six]][$mid];
				echo("nvt");
			}
	    else
				if(isset($exstatus[$studs['sid'][$six]][$mid]) && $exstatus[$studs['sid'][$six]][$mid] >= 5)
					echo("&nbsp;");
				else
					echo("X");
      echo("</TD>");
	  }
	  else
	  { // Does not have the subject
	    echo("<TD class=notchoosen>&nbsp</td>");
	  }
		$subixx++;
		if($subixx == 2)
			echo("<TD class=result>-</td>");
	}
	echo("<td class=nobot>&nbsp;</td><td class=nobot>&nbsp;</td><td class=nobot>&nbsp</td><td class=nobot>&nbsp</td>");
	// Now we might have a repeated subject
	$repcnt = 0;
	foreach($subjects['mid'] AS $mid)
	{
	  if(isset($exstatus[$sid][$mid]) && ($exstatus[$sid][$mid] == 1 || $exstatus[$sid][$mid] == 2 || $exstatus[$sid][$mid] == 4) && $repcnt < 3)
	  {  // Do this repeated subject
	    echo("<td class=nobot>&nbsp</td>");
	    echo("<TD class=result>");
			if(isset($herxarray[$sid][$mid]))
					echo(number_format($herxarray[$studs['sid'][$six]][$mid],1,",","."));
				else
						echo("X");
		echo("</td>");
		$repcnt++;
	  }
	}
	// Fill non used subject spaces
	for($i=$repcnt; $i<3; $i++)
	  echo("<td class=nobot>&nbsp;</td><td class=nobot>&nbsp;</td>");
    // Second row ends with 3 blank coloumns or 2 if exr is set for this student
    echo("<td class=nobot>&nbsp;</td><td class=nobot>&nbsp;</td>");
	if(!isset($exr[$sid]))
	  echo("<td class=nobot>&nbsp;</td>");
    echo("</TR>");

    // Third row for each student	
    echo("<TR><TD class=exnrdbot>&nbsp;</TD>");
    echo("<TD class=dbot>". (isset($countries[$studs['bplace'][$six]]) ? $countries[$studs['bplace'][$six]] : $studs['bplace'][$six]). "</TD>");
    echo("<TD class=dbot>ec</td>");
	//  row below should reflect average rounded of I&S and Pfw and color coded on...
	echo("<TD class=endresult>");
	if(isset($combires[$studs['sid'][$six]]))
	  echo($combires[$studs['sid'][$six]]);
	else
	  echo("X");
	echo("</td>");
	echo("<TD class=dbot>&nbsp;</TD>");
	
	echo("<td class=dbot>e</td>");
	// Maintain some counters for this calculation of pass for TV1 (and prep for TV2)
	$choicesubfail = 0;
	$extotval = 0.0;
	$extotcnt = 0;
	$coreshort = 0;
	if(isset($combires[$studs['sid'][$six]]))
	{
	  $subjcount = 1;
	  $totpoints = $combires[$studs['sid'][$six]];
	  if($combires[$studs['sid'][$six]] < 6)
	  {
	    $negpoints = 6 - round($combires[$studs['sid'][$six]]);
		$fails = 1;
		$fullfail = ($combires[$studs['sid'][$six]] < 4 ? 1 : 0);
	  }
	  else
	    $negpoints = 0;
		$fails = 0;
		$fullfail = 0;
	}
	else
	{
	  $subjcount = 1; // Combivak
	  $totpoints = 0;
	  $negpoints = 6;
	  $fails = 1;
	  $fullfail = 1;
	}
	$certconditions = false;
	$subixx=0;
	foreach($subjects['mid'] AS $mid)
	{
      $hassubject = 0;
	  // check for subjects here!
	  foreach($packages['packagename'] AS $subix => $pname)
	  {
	    if($pname == $studs['packagename'][$six] && $mid == $packages['mid'][$subix])
	    $hassubject = 1;
	  }
	  if($mid == $studs['extrasubject'][$six])
	    $hassubject = 1;
		  if($mid == $studs['extrasubject2'][$six])
	    $hassubject = 1;
		  if($mid == $studs['extrasubject3'][$six])
	    $hassubject = 1;
	  // Now I&S and PFW don't count as subjects (since it's part of combires)
	  if($mid == $ismid || $mid == $pfwmid)
	    $hassubject = 0;
      if($hassubject != 0)
	  {
		$subjcount++;
		// See what result was retrieved for the exam and ajust totals for it.
    if(isset($hexarray[$sid][$mid]))
		{
		  $extotval += $hexarray[$sid][$mid];
		  $extotcnt++;
		}
	  // Show exam result
		if(isset($exstatus[$sid][$mid]) && $exstatus[$sid][$mid] >= 9)
		  echo("<TD class=certdbot>");
		else if(isset($exstatus[$sid][$mid]) && $exstatus[$sid][$mid] >= 5)
		  echo("<TD class=freedbot>");
		else
	      echo("<TD class=endresult>");
		// Vrijstelling?
		if(isset($exstatus[$studs['sid'][$six]][$mid]) && $exstatus[$studs['sid'][$six]][$mid] >= 9)
		{
		  echo($exstatus[$studs['sid'][$six]][$mid] - 3);
		  $totpoints += $exstatus[$studs['sid'][$six]][$mid] - 3;
		  $certconditions = true;
		}
		else if(isset($exstatus[$studs['sid'][$six]][$mid]) && $exstatus[$studs['sid'][$six]][$mid] >= 5)
		{
		  echo($exstatus[$studs['sid'][$six]][$mid] + 2);
		  $totpoints += $exstatus[$studs['sid'][$six]][$mid] + 2;
		}
        else if(isset($hendarray[$studs['sid'][$six]][$mid]))
		{
	      echo(number_format($hendarray[$studs['sid'][$six]][$mid],0,",","."));
		  $totpoints += round($hendarray[$studs['sid'][$six]][$mid]);
		  if($hendarray[$studs['sid'][$six]][$mid] < 6)
		  {
		    $negpoints += 6 - round($hendarray[$studs['sid'][$six]][$mid]);
			$fails++;
						if(in_array($mid2sjname[$mid],$coresubs))
							$coreshort += 6 - round($hendarray[$studs['sid'][$six]][$mid]);
			if($hendarray[$studs['sid'][$six]][$mid] < 4)
			  $fullfail++;
			// See if this is a choice subject
			if((isset($choicesubjs[$studs['packagename'][$six]]) && $mids[$choicesubjs[$studs['packagename'][$six]]] == $mid) ||
			   (isset($studs['extrasubject'][$six]) && $studs['extrasubject'][$six] == $mid) || (isset($studs['extrasubject2'][$six]) && $studs['extrasubject2'][$six] == $mid) || (isset($studs['extrasubject3'][$six]) && $studs['extrasubject3'][$six] == $mid))
			{ // This is a choice subject and failed
			  $choicesubfail++;
			}
		  }
		}
	    else
		{
  	      echo("X");
		  $negpoints += 6;
		  $fails++;
		  $fullfail++;
		}
        echo("</TD>");
	  }
	  else
	  { // Does not have the subject
	    echo("<TD class=notchoosendbot>&nbsp</td>");
	  }
		$subixx++;
		if($subixx == 2)
		{
			if(isset($rekres[$studs['sid'][$six]]))
				echo("<TD class=endresult>". $rekres[$studs['sid'][$six]]. "</td>");
			else
				echo("<TD class=endresult>X</td>");
		}
	}
	// First calculte the average of exam results
	if($extotcnt > 0)
	  $exavg = $extotval / $extotcnt;
	else
	  $exavg = 0;
	// Decide if passed exam for TV1 
		if((($certconditions && $subjcount >= 7 && $negpoints == 0) ||
			 (!$certconditions && $subjcount >= 7 && $totpoints >= ($subjcount * 6 - 1) && $negpoints == 1) || 
			 (!$certconditions && $subjcount >= 7 && $totpoints >= ($subjcount * 6) && $negpoints <= 2 && $coreshort < 2) ||
			 (!$certconditions && $subjcount >= 8 && $totpoints >= ($subjcount * 6) && $negpoints == 3 && $coreshort < 2 && $fullfail == 0 && ($fails - $choicesubfail) <= 1)) && $exavg >= 5.5)
	  $passedtv1 = true;
	else
	  $passedtv1 = false;

	// Now maybe this student has 8 subjects, failed according to 8 subjects and can drop 1 to pass anyway...
	unset($midlist);
  if($subjcount > 8 && !$passedtv1)
  {
	  // Create a list of mids for which this student has done exams
	  $midindex = 1;
	  if(isset($hendarray[$sid]))
	    foreach($hendarray[$sid] AS $cmid => $dummy)
				if($cmid != $ismid && $cmid != $pfwmid && $cmid != $lomid)
	        $midlist[$midindex++] = $cmid;
	  // Check each package for compliance, first count matching subjects
	  unset($pkcandidates);
	  if(isset($midlist) && isset($combires[$sid]))
	    foreach($packmids AS $pkname => $pkmidar)
	    {
	      foreach($midlist AS $chkmid)
				{
					if(in_array($chkmid, $pkmidar))
					{
						if(isset($pkcandidates[$pkname]))
							$pkcandidates[$pkname]++;
						else
							$pkcandidates[$pkname] = 1;
					}
				}
	    }
	  // remove package candidates that don't have enough subjects included
	  if(isset($pkcandidates))
	    foreach($pkcandidates AS $pkname => $pscount)
	      if($pscount < 6) // Combisubject was not counted in this!
				{
					unset($pkcandidates[$pkname]);
					//echo("-". $pkname. "(". $pscount. ")-");
				}
	  // remove the package candidates that if the student were to choose it would not result in a pass
	  if(isset($pkcandidates))
	  foreach($pkcandidates AS $pkname => $dummy)
	  {
	    $newtotpoints = $totpoints;
			$newnegpoints = $negpoints;
			$newextotval = $extotval;
				$newcoreshort = $coreshort;
			$newextotcnt = $extotcnt;
			$pkmidar = $packmids[$pkname];
			$newsubjcount = 1; // Combivak must be present if we get here and is one of the subjects
			foreach($midlist AS $cmid)
			{
				if(!in_array($cmid,$pkmidar))
				{ // This subject is not in the package so correct points
					$newtotpoints -= $hendarray[$sid][$cmid];
					if($hendarray[$sid][$cmid] < 6)
					{
						$newnegpoints -= 6 - $hendarray[$sid][$cmid];
							if(in_array($mid2sjname[$cmid],$coresubs))
								$newcoreshort -= 6 - $hendarray[$sid][$cmid];
					}
					if(isset($exarray[$sid][$cmid]))
					{
						$newextotval -= $hexarray[$sid][$cmid];
						$newextotcnt--;
					}
					else if(isset($hexarray[$sid][$cmid]))
					{
						$newextotval -= $hexarray[$sid][$cmid];
						$newextotcnt--;
					}
				}
				else
					$newsubjcount++;
			}
		// Now reevaluate if failed, since if failed the package does not apply a a candidate anymore
		if($newextotcnt > 0)
		  $newexavg = $newextotval / $newextotcnt;
		else
		  $newexavg = 0.0;
				if((!(($certconditions && $newsubjcount >= 7 && $newnegpoints == 0) ||
						 (!$certconditions && $newsubjcount >= 7 && $newtotpoints >= ($newsubjcount * 6 - 1) && $newnegpoints == 1) || 
						 (!$certconditions && $newsubjcount >= 7 && $newtotpoints >= ($newsubjcount * 6) && $newnegpoints <= 2 && $newcoreshort <= 1))) || $newexavg < 5.5)
			{
	      unset($pkcandidates[$pkname]);
			}
	  }
  }

	if(isset($midlist) && isset($pkcandidates) && count($pkcandidates) >= 1)
	  $passedtv1 = true;

	if($passedtv1)
	{
	  if($studs['gender'][$six] == "m")
	    $passTV1m++;
	  else
	    $passTV1v++;
	}
	else
    {
	  if($miss)
	  {
	    if($studs['gender'][$six] == "m")
				$missTV1m++;
			else
				$missTV1v++;
	  }
	}
	echo("<td class=dbotgreen><center>". $totpoints. "</center></td><td class=dbot><center>". ($exavg > 0.0 ? round($exavg - 0.005,2) : "-"). "</center></td><td class=dbot><center>". ($passedtv1 && !isset($exr[$sid]) ? "gesl" : "&nbsp;"));
	if(isset($midlist))
	{
	  if(isset($pkcandidates) && count($pkcandidates >= 1))
	  {
	    $pkct = "";
	    foreach($pkcandidates AS $pkname => $pkscnt)
				$pkct .= $pkname. ",";
			if(!isset($exr[$sid])) 
				echo(substr($pkct,0,-1));
    }
	}
	echo("</center></td>");
	echo("<td class=dbot><center>". ($passedtv1 ? "&nbsp;" : "afgew"). "</center></td>");
	// Now we might have a repeated subject
	$repcnt = 0;
	foreach($subjects['mid'] AS $sbix => $mid)
	{
	  if(isset($exstatus[$sid][$mid]) && ($exstatus[$sid][$mid] == 1 || $exstatus[$sid][$mid] == 2 || 
	     $exstatus[$sid][$mid] == 4) && $repcnt < 3)
	  {  // Do this repeated subject
	    if(isset($herxarray[$sid][$mid]))
			{
				$extotval -= $hexarray[$sid][$mid];
				$extotval += $herxarray[$sid][$mid];
				//echo("Replacing ". $hexarray[$sid][$mid]. " for ". $herxarray[$sid][$mid]);
			}
			else
				echo("No herresult, mid=". $mid. ", sid=". $sid);
	    echo("<td class=dbot>". $subjects['shortname'][$sbix]. "</td>");
			if(isset($exstatus[$sid][$mid]))
			{
				echo("<TD ");
				if($exstatus[$sid][$mid] == 1)
					echo("class=repeatdbot");
				else if($exstatus[$sid][$mid] == 2 || $exstatus[$sid][$mid] == 3 || $exstatus[$sid][$mid] == 4)
					echo("class=absentdbot");
				else if($exstatus[$sid][$mid] >= 9)
					echo("class=certdbot");
				else if($exstatus[$sid][$mid] >= 5)
					echo("class=freedbot");
				echo(">");
			}
			else
				echo("<TD class=dbotresult>");
			if(isset($endarray[$sid][$mid]) && isset($soarray[$sid][$mid]) && isset($herxarray[$sid][$mid]))
			{
				echo(number_format($endarray[$studs['sid'][$six]][$mid],0,",","."));
				$totpoints += $endarray[$studs['sid'][$six]][$mid];
				if(isset($hendarray[$studs['sid'][$six]][$mid]))
					$totpoints -= $hendarray[$studs['sid'][$six]][$mid];
				else
				{
					$negpoints -= 6;
					$fullfails--;
					$fails--;
				}
				if(isset($hendarray[$studs['sid'][$six]][$mid]) && $hendarray[$studs['sid'][$six]][$mid] < 6)
				{
					$negpoints -= 6 - round($hendarray[$studs['sid'][$six]][$mid]);
					$fails--;
					if(in_array($mid2sjname[$mid],$coresubs))
						$coreshort -= 6 - round($hendarray[$studs['sid'][$six]][$mid]);
					if($hendarray[$studs['sid'][$six]][$mid] < 4)
						$fullfail--;
				}
				if($endarray[$studs['sid'][$six]][$mid] < 6)
				{
					$negpoints += 6 - round($endarray[$studs['sid'][$six]][$mid]);
						if(in_array($mid2sjname[$mid],$coresubs))
							$coreshort += 6 - round($endarray[$studs['sid'][$six]][$mid]);
				}
			}
			else
				echo("X");
			echo("</td>");
			$repcnt++;
	  }
	}
	// Fill non used subject spaces
	for($i=$repcnt; $i<3; $i++)
	  echo("<td class=dbot>&nbsp;</td><td class=dbot>&nbsp;</td>");
    // t-ec
    echo("<td class=dbotgreen>". $totpoints. "</td>");
	// year result
	// Decide if passed exam for TV2
	if($extotcnt > 0)
	{
	  $exavg = $extotval / $extotcnt;
	  //$exavg = $extotval;
	}
	else
	  $exavg = 0;
		if((($certconditions && $subjcount >= 7 && $negpoints == 0) ||
			 (!$certconditions && $subjcount >= 7 && $totpoints >= ($subjcount * 6 - 1) && $negpoints == 1) || 
			 (!$certconditions && $subjcount >= 7 && $totpoints >= ($subjcount * 6) && $negpoints <= 2 && $coreshort <= 1) ||
			 (!$certconditions && $subjcount >= 8 && $totpoints >= ($subjcount * 6) && $negpoints == 3 && $coreshort <= 1 && $fullfail == 0 && ($fails - $choicesubfail) <= 1)) && $exavg >= 5.5)
	  $passedtv2 = true;
	else
	{
	  $passedtv2 = false;
	}

	// Now maybe this student has 8 subjects, failed according to 8 subjects and can drop 1 to pass anyway...
	unset($midlist);
  if($subjcount > 8 && !$passedtv2)
  {
	  // Create a list of mids for which this student has done exams
	  $midindex = 1;
	  if(isset($endarray[$sid]))
	    foreach($endarray[$sid] AS $cmid => $dummy)
		  if($cmid != $ismid && $cmid != $pfwmid && $cmid != $lomid)
	        $midlist[$midindex++] = $cmid;
	  // Check each package for compliance, first count matching subjects
	  unset($pkcandidates);
	  if(isset($midlist) && isset($combires[$sid]))
	    foreach($packmids AS $pkname => $pkmidar)
	    {
	      foreach($midlist AS $chkmid)
		  {
		    if(in_array($chkmid, $pkmidar))
		    {
		      if(isset($pkcandidates[$pkname]))
			    $pkcandidates[$pkname]++;
			  else
			    $pkcandidates[$pkname] = 1;
		    }
		  }
	    }
	  // remove package candidates that don't have enough subjects included
	  if(isset($pkcandidates))
	    foreach($pkcandidates AS $pkname => $pscount)
	      if($pscount < 6) // Combisubject was not counted in this!
				{
					unset($pkcandidates[$pkname]);
				}
	  // remove the package candidates that if the student were to choose it would not result in a pass
	  if(isset($pkcandidates))
			foreach($pkcandidates AS $pkname => $dummy)
			{
				$newtotpoints = $totpoints;
				$newnegpoints = $negpoints;
				$newcoreshort = $coreshort;
				$newextotval = $extotval;
				$newextotcnt = $extotcnt;
				$pkmidar = $packmids[$pkname];
				$newsubjcount = 1; // Combivak must be present if we get here and is one of the subjects
				foreach($midlist AS $cmid)
				{
					if(!in_array($cmid,$pkmidar))
					{ // This subject is not in the package so correct points
						$newtotpoints -= $endarray[$sid][$cmid];
						if($endarray[$sid][$cmid] < 6)
						{
							$newnegpoints -= 6 - $endarray[$sid][$cmid];
							if(isset($mid2sjname[$cmid]) && in_array($mid2sjname[$cmid],$coresubs))
								$newcoreshort -= 6 - $endarray[$sid][$cmid];								
						}
						if(isset($exarray[$sid][$cmid]))
						{
							$newextotval -= $exarray[$sid][$cmid];
							$newextotcnt--;
						}
						else if(isset($hexarray[$sid][$cmid]))
						{
							$newextotval -= $hexarray[$sid][$cmid];
							$newextotcnt--;
						}
					}
					else
					{
						$newsubjcount++;
					}
				}
				if($newextotcnt > 0)
				{
					$newexavg = $newextotval / $newextotcnt;
				}
				else
					$newexavg = 0;
					if((!(($certconditions && $newsubjcount >= 7 && $newnegpoints == 0) ||
						 (!$certconditions && $newsubjcount >= 7 && $newtotpoints >= ($newsubjcount * 6 - 1) && $newnegpoints == 1) || 
						 (!$certconditions && $newsubjcount >= 7 && $newtotpoints >= ($newsubjcount * 6) && $newnegpoints <= 2 && $newcoreshort <= 1))) || $newexavg < 5.5)
				{
					unset($pkcandidates[$pkname]);
				}
			}
  }

	if(isset($midlist) && isset($pkcandidates) && count($pkcandidates) >= 1)
	  $passedtv2 = true;

	  if($passedtv2)
	{
	  if($studs['gender'][$six] == "m")
	    $passTV2m++;
	  else
	    $passTV2v++;
	}
	else
    {
	  if($miss)
	  {
	    if($studs['gender'][$six] == "m")
		  $missTV2m++;
		else
		  $missTV2v++;
	  }
	}
	// Show exam average
	echo("<td class=dbot>". ($exavg > 0.0 ? round($exavg - 0.005,2) : "-"). "</td>");

	if(!isset($exr[$sid]))
	{
      echo("<td class=dbot><b>");
	  if($passedtv2)
	  {
	    if(!isset($midlist))
	      echo("Geslaagd (D". ($subjcount-$subcor). ")");
		else
		{
		  if(isset($pkcandidates) && count($pkcandidates >= 1))
		  {
			$pkct = "";
			foreach($pkcandidates AS $pkname => $pkscnt)
			  $pkct .= $pkname. ",";
			echo("Geslaagd (D". ($subjcount-1-$subcor). ")<BR>". substr($pkct,0,-1));
		  }
		}
	  }
	  else
	  {
      echo("Afgewezen");
			$hascert = false;
	    foreach($subjects['mid'] AS $six => $mid)
	    {
	      if(isset($endarray[$sid][$mid]) && $endarray[$sid][$mid] >= 7.0 && isset($exarray[$sid][$mid]) && $exarray[$sid][$mid] >= 6.0 && isset($soarray[$sid][$mid]) && $soarray[$sid][$mid] >= 6.0)
				{
					if(!$hascert)
						echo(". VS: ");
					else
						echo(", ");
					echo(" ". $subjects['shortname'][$six]);
				$hascert = true;
				}
	    }
			//echo("[". $subjcount. ",". $totpoints. ",". $negpoints. ",". $coreshort. "]");
	  }
	  echo("</b></td>");
	}
    echo("</TR>");

	
    $linecount++;	
  } // End loop for each student
  
  print_foot();
  
  // Show the summary result
  echo("<img class=schoollogo src=schoollogo.png><p class=schooldata><b>". $schoolname. "</b><BR>");
  if(isset($schooladdress))
    echo($schooladdress. "<BR>"); 
  if(isset($schooltelfax))
    echo($schooltelfax. "<BR></p>");
  echo("<H1>EXAMEN SCHOOLJAAR ". $schoolyear. "</H1>");
  echo("<table class=summarytable><tr><td colspan=12 class=topsumrow>STATISTISCHE OPGAVE BETREFFENDE HET EINDEXAMEN AANTAL KANDIDATEN:</td></tr>");
  echo("<tr><td class=sumheader colspan=3>Dat aan het examen of aan een gedeelte ervan heeft deelgenomen. Dit aantal moet overeenkomen met de opgave op het form. Ex.1.</td>");
  echo("<td class=sumheader colspan=3>Dat zich tijdens het schoolonderzoek op examen terugtrok of om geldige reden verhinderd was het schriftelijk examen te voltooien.</td>");
  echo("<td class=sumheader colspan=3>Dat is afgewezen.</td>");
  echo("<td class=sumheader colspan=3>Dat is geslaagd.</td></tr>");
  echo("<tr><td class=sumdata>M</td><td class=sumdata>V</td><td class=sumdata>T</td><td class=sumdata>M</td><td class=sumdata>V</td><td class=sumdata>T</td>");
  echo("<td class=sumdata>M</td><td class=sumdata>V</td><td class=sumdata>T</td><td class=sumdata>M</td><td class=sumdata>V</td><td class=sumdata>T</td></tr>");
  echo("<tr><td class=sumdata>". $totm. "</td><td class=sumdata>". $totv. "</td><td class=sumdata>". ($totm + $totv). "</td>");
  echo("<td class=sumdata>". $missTV1m. "</td><td class=sumdata>". $missTV1v. "</td><td class=sumdata>". ($missTV1m + $missTV1v). "</td>");
  echo("<td class=sumdata>". ($totm - $passTV1m - $missTV1m). "</td><td class=sumdata>". ($totv - $passTV1v - $missTV1v). "</td><td class=sumdata>". ($totm + $totv - $missTV1m - $missTV1v - $passTV1m - $passTV1v). "</td>");
  echo("<td class=sumdata>". $passTV1m. "</td><td class=sumdata>". $passTV1v. "</td><td class=sumdata>". ($passTV1m + $passTV1v). "</td></tr>");

  echo("<tr><td class=sumdata>". $totm. "</td><td class=sumdata>". $totv. "</td><td class=sumdata>". ($totm + $totv). "</td>");
  echo("<td class=sumdata>". $missTV2m. "</td><td class=sumdata>". $missTV2v. "</td><td class=sumdata>". ($missTV2m + $missTV2v). "</td>");
  echo("<td class=sumdata>". ($totm - $passTV2m - $missTV2m). "</td><td class=sumdata>". ($totv - $passTV2v - $missTV2v). "</td><td class=sumdata>". ($totm + $totv - $missTV2m - $missTV2v - $passTV2m - $passTV2v). "</td>");
  echo("<td class=sumdata>". $passTV2m. "</td><td class=sumdata>". $passTV2v. "</td><td class=sumdata>". ($passTV2m + $passTV2v). "</td></tr>");
  echo("</table>");
  echo("<p class=summarysign>Eerste tijdvak : percentage ". round(($passTV1m + $passTV1v) / ($totm + $totv) * 100,0). " % kandidaten, ". round(($passTV1m + $passTV1v) / ($totm + $totv - $missTV1m - $missTV1v) * 100,0). "% deelnemers");
  echo("<BR><BR>Aruba<BR><BR><BR><BR>De Voorzitter: _________________________________________");
  echo("<BR><BR><BR><BR>De Secretaris: _________________________________________</p>");
  echo("<p class=summarysign>Tweede tijdvak : percentage ". round(($passTV2m + $passTV2v) / ($totm + $totv) * 100,0). " % kandidaten, ". round(($passTV2m + $passTV2v) / ($totm + $totv - $missTV2m - $missTV2v) * 100,0). "% deelnemers");
  echo("<BR><BR>Aruba<BR><BR><BR><BR>De Voorzitter: _________________________________________");
  echo("<BR><BR><BR><BR>De Secretaris: _________________________________________</p>");

  // close the page
  echo("</html>");
?>
