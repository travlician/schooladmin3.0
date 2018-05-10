<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011 Aim4me N.V.   (http://www.aim4me.info)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
  
  // Subject translation tables
  $offsubjects = array(1 => "Ne","En","Sp","Pa","Wi","Na sk1","Na sk2","Bio","Ec Mo","Ak","Gs","CKV");
  $altsubjects = array("NAT4"=>6, "GES4"=>11, "AK4"=>10, "EC4"=>9, "BIO4"=>8, "SCH4"=>7, "WIS4"=>5, "PAP4"=>4, "SPA4"=>3, "ENG4"=>2, "NED4"=>1,
                       "Ne"=>1, "En"=>2, "Sp"=>3, "Wi"=>5, "Na"=>6, "Sk"=>7, "Bio"=>8, "Gs"=>11, "Ak"=>10, "Ec"=>9, "Pa"=>4, "NaSk 1"=>6, "NaSk 2"=>7, "EcMo"=>9,
					   "PA"=>4, "NE"=>1, "EN"=>2, "SP"=>3, "WI"=>5, "AK"=>10, "BI"=>8, "GS"=>11, "Na"=>6, "SK"=>7, "EC/MO"=>9,
					   "ne"=>1, "en"=>2, "sp"=>3, "pa"=>4, "wi"=>5, "na"=>6, "sk"=>7, "bi"=>8, "ec"=>9, "ak"=>10, "gs"=>11,
					   "NA"=>6, "EC"=>9, "EM & O"=>9, "CKV"=>12, "Ckv"=>12, "NED"=>1, "ENG"=>2, "SPA"=>3, "PAP"=>4, "WIS"=>5, "NASK1" => 6,"NS2"=>7, "NASK2" => 7, "BIO"=>8, "GES"=>11, "CKV"=>12, "bio"=>8, "CKV alg"=>12, "ecmo"=>9, "Nask 1"=>6, "Nask 2"=>7, "CKV ex"=>12);
  $countries = array("AUA" => "Aruba", "NED" => "Nederland", "BON" => "Bonaire", "CUR" => "Curaçao", "SXM" => "Sint Maarten", "SUR" => "Suriname",
                     "COL" => "Colombia", "CHI" => "Chili", "CHN" => "China", "DOM" => "Dominicaanse Republiek", "HTI" => "Haïti", "JAM" => "Jamaica",
					 "PER" => "Peru", "PHL" => "Philipijnen", "USA" => "Verenigde Staten van Amerika", "CUB" => "Cuba", "VEN" => "Venezuela",
					 "PAN" => "Panama");
  
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
	echo("<SPAN class=toprow3>Afdeling: MAVO - Middelbaar Algemeen Voortgezet Onderwijs</SPAN>");
	echo("<SPAN class=toprow4>Schooljaar: ". $schoolyear. "</SPAN>");
	
	echo("<table class=ex5table border=0><tr><td class=nobot>&nbsp;</td><td class=lastname>Achternaam</td><td class=firstnamehead>Voornamen</td><td class=fatbotgreen rowspan=2>v<BR>m</td><td class=fatbotgreen rowspan=2><center>pro-<BR>fiel</center></td><td colspan=5 class=gemdeel><b>Gemeenschappelijk deel</td>");
	$skipsubj=0;
	foreach($subjects['shortname'] AS $sn)
	{
	  if($skipsubj < 2)
	    $skipsubj++;
	  else
	    echo("<td rowspan=2 class=subjheader>$sn</td>");
	}
	echo("<td class=fatbotgreen rowspan=2><center>TOT<BR>TV1</td><td colspan=2 class=nobotgreen><center>Uitslag 1ste tijdvak</center></td><td colspan=6 class=herhead><b><center>Herexamen</center></b></td>");
	echo("<td class=nobotgreen colspan=2><center><b>Einduitslag</b></center></td></tr>");
	
	echo("<tr><td class=fatbot>Ex-no</td><td class=fatbotgreenleft>Geb. datum/Land</td><td class=fatbotgreen>&nbsp;</td><td class=subjheadergem>ckv</td><td class=subjheadergem>lo</td><td class=fatbot>&nbsp;</td>");
	$skipsubj = 0;
	foreach($subjects['shortname'] AS $sn)
	  if($skipsubj < 2)
	  {
	    echo("<td class=subjheadergem>$sn</td>");
		$skipsubj++;
	  }
	
	echo("<td class=fatbotgreen><center>gesl</center></td><td class=fatbotgreen><center>afgew</center><td class=herbot>vak1</td><td class=herbot>cijf</td>");
	echo("<td class=herbot>vak2</td><td class=herbot>cijf</td><td class=herbot>vak3</td><td class=herbot>cijf</td><td class=fatbotgreen>TOT</td>");
	echo("<td class=fatbotgreen>&nbsp</td></tr>");
	
  }
  
  function print_foot()
  {
    global $pageno;
	echo("</TABLE>");
	echo("<p class=footer><span class=formdata>EX 5-M (". date('n/j/Y; g:i A'). ") Bron: myschoolresults.com</span><span class=pagenr>Volgnummer ". $pageno++. "</span>");
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
  $subjectsqr = SA_loadquery("SELECT shortname,subjectpackage.mid,fullname FROM subjectpackage LEFT JOIN subject USING(mid) UNION SELECT shortname,extrasubject,fullname FROM s_package LEFT JOIN subject ON(mid=extrasubject) WHERE shortname IS NOT NULL UNION SELECT shortname,extrasubject2,fullname FROM s_package LEFT JOIN subject ON(mid=extrasubject2) WHERE shortname IS NOT NULL UNION SELECT shortname,extrasubject3,fullname FROM s_package LEFT JOIN subject ON(mid=extrasubject3) WHERE shortname IS NOT NULL GROUP BY mid ORDER BY mid");

  // Get subject based on defined standard using translation tables as defined at start of this file.
  foreach($offsubjects AS $osix => $sjname)
  {
    foreach($subjectsqr['shortname'] AS $sbix => $subsn)
	{
	  if(isset($altsubjects[$subsn]) && $altsubjects[$subsn] == $osix)
	  {
	    $subjects['shortname'][$osix] = $sjname;
			$subjects['mid'][$osix] = $subjectsqr['mid'][$sbix];
	  }
	}
  }
/*	foreach($subjects['mid'] AS $sjn => $sjmid)
	  echo($sjn. " => ". $sjmid. "<BR>");*/
  
  // Get the data of the exam subject collections
  $packages = SA_loadquery("SELECT * FROM subjectpackage");
  // Reformat in array to get easier compares in pass/fail for 7 subjects
  foreach($packages['packagename'] AS $pnix => $pkname)
  {
    $packmids[$pkname][$pnix] = $packages['mid'][$pnix];
  }
  
  // Get a list of students with the subject package and extra subject
  $squery = "SELECT sid,lastname,firstname,s_exnr.data AS exnr,packagename,extrasubject,extrasubject2,extrasubject3,s_ASGender.data AS gender,";
  $squery .= " s_ASBirthDate.data AS bdate, arubacom.c_country.tekst AS bplace FROM student";
  $squery .= " LEFT JOIN s_ASBirthDate USING (sid) LEFT JOIN s_ASBirthCountry USING(sid) LEFT JOIN arubacom.c_country ON(s_ASBirthCountry.data=arubacom.c_country.id)";
  $squery .= " LEFT JOIN s_exnr USING(sid) LEFT JOIN s_package USING(sid) LEFT JOIN s_ASGender USING(sid)";
  $squery .= " WHERE s_exnr.data IS NOT NULL AND s_exnr.data > '0' ORDER BY s_exnr.data";
  $studs = SA_loadquery($squery);
  echo(mysql_error($userlink));
  
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
	// Get the previous year (to get previous so and exam results later)
	$prevyearqr = SA_loadquery("SELECT MAX(year) as pyr FROM gradestore WHERE year <> '". $schoolyear. "'");
	$prevyear = $prevyearqr['pyr'][1];
  // Get preious year SO results
  $cquery = "SELECT sid,mid,result FROM gradestore WHERE period=2 AND year=\"". $prevyear. "\"";
  $cres = SA_loadquery($cquery);
  echo(mysql_error($userlink));
  if(isset($cres))
  {
    foreach($cres['sid'] AS $cix => $csid)
		{
			$vsoarray[$csid][$cres['mid'][$cix]] = $cres['result'][$cix];
		}
  }
  // Get previous year Exam results
  $cquery = "SELECT sid,mid,result FROM gradestore WHERE period=3 AND year=\"". $prevyear. "\"";
  $cres = SA_loadquery($cquery);
  echo(mysql_error($userlink));
  if(isset($cres))
  {
    foreach($cres['sid'] AS $cix => $csid)
		{
			$vexarray[$csid][$cres['mid'][$cix]] = $cres['result'][$cix];
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
  $cquery = "SELECT sid,mid,result AS avgresult FROM testresult LEFT JOIN testdef USING(tdid) LEFT JOIN class USING(cid) WHERE year=\"". $schoolyear. "\" AND period=3 AND type=\"Exam\" AND result IS NOT NULL";
  $cquery .= " GROUP BY sid, mid, testdef.date ORDER BY testdef.date DESC";
  $cres = SA_loadquery($cquery);
  echo(mysql_error($userlink));
  if(isset($cres))
    foreach($cres['sid'] AS $cix => $csid)
	{
	  $hexarray[$csid][$cres['mid'][$cix]] = round($cres['avgresult'][$cix],1);
	}

	$cquery = "SELECT sid,mid,result AS avgresult FROM testresult LEFT JOIN testdef USING(tdid) LEFT JOIN class USING(cid) WHERE year=\"". $schoolyear. "\" AND period=3 AND type=\"Exam\" AND short_desc='Hex'";
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
	
  // Get exams year result texts
  $cres = SA_loadquery("SELECT sid,xresult FROM examresult WHERE xresult IS NOT NULL AND year='". $schoolyear. "'");
  if(isset($cres))
    foreach($cres['sid'] AS $cix => $csid)
			$exr[$csid] = $cres['xresult'][$cix]; 

  // Get the CKV info
  $ckvqr = SA_loadquery("SELECT sid,ckvres FROM examresult WHERE year='". $schoolyear. "' AND ckvres = 1");
  if(isset($ckvqr['sid']))
    foreach($ckvqr['sid'] AS $crix => $csid)
	  $ckvres[$csid] = 1;
  // Get the LO info
  $lomidqr = SA_loadquery("SELECT mid FROM subject WHERE shortname LIKE 'LO%' OR shortname LIKE 'lo%' OR shortname LIKE 'Lo%'");
  if(isset($lomidqr['mid']))
    $lomid = $lomidqr['mid'][1];
  else
    $lomid = 0;
  $rekmidqr = SA_loadquery("SELECT mid FROM subject WHERE shortname LIKE 'rek'");
  if(isset($rekmidqr['mid']))
    $rekmid = $rekmidqr['mid'][1];
  else
    $rekmid = 0;
	//echo("<BR>rekmid=". $rekmid. "<BR>");
  $loresqr = SA_loadquery("SELECT sid FROM gradestore WHERE mid=". $lomid. " AND year='". $schoolyear. "' AND period=0 AND result > 5");
  if(isset($loresqr['sid']))
    foreach($loresqr['sid'] AS $lsid)
      $lores[$lsid] = 1;
	// Get lo res from excempts
	//echo("Check of LO exempts<BR>\r\n");
	if(isset($exstatus))
		foreach($exstatus AS $vsid => $xstatmid)
			if(isset($xstatmid[$lomid]) && $xstatmid[$lomid] > 4)
			{
				$lores[$vsid] = 1;	
				//echo("Set loex for sid ". $vsid. "<BR>");
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
  echo("<html><head><title>Formulier EX. 5-M</title></head><body link=blue vlink=blue onfload=\"window.print();setTimeout('window.close();',10000);\">");
  echo '<LINK rel="stylesheet" type="text/css" href="style_EX5-M.css" title="style1">';
  
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
	echo("<TR><TD class=exnrnobot>". $studs['exnr'][$six]. "</TD>");
	echo("<TD class=nobot>". $studs['lastname'][$six]. "</TD><TD rowspan=3 class=firstname> ". $studs['firstname'][$six]. "</TD>");
	echo("<TD rowspan=3 class=dbot>". $studs['gender'][$six]. "</td><TD rowspan=3 class=dbot>". substr($studs['packagename'][$six],0,2). "</td>");
	echo("<TD rowspan=3 class=". (isset($ckvres[$sid]) ? "ckvv>voldaan" : "ckvnv>niet<br>voldaan") . "</TD>");
	echo("<TD rowspan=3 class=". (isset($lores[$sid]) ? "ckvv>voldaan" : "ckvnv>niet<br>voldaan") . "</TD>");
	echo("<td>se</td>");
	if(strtoupper($studs['gender'][$six]) == "M")
	  $totm++;
	else
	  $totv++;
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
			if(isset($exstatus[$studs['sid'][$six]][$mid]) && $exstatus[$studs['sid'][$six]][$mid] >= 5)
	      echo("<TD class=free>");
			else
	      echo("<TD class=result>");
			// Vrijstellng?
			if(isset($exstatus[$studs['sid'][$six]][$mid]) && $exstatus[$studs['sid'][$six]][$mid] >= 5)
			{
				if(isset($vsoarray[$studs['sid'][$six]][$mid]))
					echo(number_format($vsoarray[$studs['sid'][$six]][$mid],1,",","."));
				else
					echo("&nbsp;");
			}
			else if(isset($soarray[$studs['sid'][$six]][$mid]))
				if(strtolower(substr($soarray[$studs['sid'][$six]][$mid],0,1)) == "v")
					echo("&nbsp;");
				else
					echo(number_format($soarray[$studs['sid'][$six]][$mid],1,",","."));
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
	  // - Voldoende CKV,LO
	  // - Alles voldoende
	  // - vijf zessen en een 5
	  // - max 2 vijven en gemiddeld 6 of meer
	  // - max 1 vier zonder andere onvoldoendes en gemiddeld 6 of meer
	  // - 1 drie of lager => jammer dan
	  // Speciaal voor 7de vakken: 
	  // 6 vakken binnen profiel volgens bovenstaande regels
	  // 
	  // Vrijstelling: alles wat 7 of meer is, en zowel SO als SE 6+ 
	  // 
	}
	echo("<td class=nobot>&nbsp;</td><td class=nobot>&nbsp;</td><td class=nobot>&nbsp;</td>");
	// Now we might have up to 3 repeated subject
	$repcnt = 0;
	foreach($subjects['mid'] AS $mid)
	{
	  if(isset($exstatus[$sid][$mid]) && ($exstatus[$sid][$mid] == 1 || $exstatus[$sid][$mid] == 2 || $exstatus[$sid][$mid] == 4) && $repcnt < 3)
	  {  // Do this repeated subject
	     echo("<td class=nobot>&nbsp</td>");
	     echo("<TD class=result>");
		 if(isset($soarray[$sid][$mid]))
		   if($soarray[$studs['sid'][$six]][$mid] > 0.9)
	         echo(number_format($soarray[$studs['sid'][$six]][$mid],1,",","."));
		   else
		     echo($soarray[$studs['sid'][$six]][$mid]);
	    else
  	      echo("X");
		echo("</td>");
		$repcnt++;
	  }
	}
	// Fill non used subject spaces
	for($i=$repcnt; $i<3; $i++)
	  echo("<td class=nobot>&nbsp;</td><td class=nobot>&nbsp;</td>");
    // First row ends with 2 blank coloumns or blank, endresult
    echo("<td class=nobot>&nbsp;</td>");
	if(isset($exr[$sid]))
	  echo("<td class=dbot rowspan=3><b>". nl2br($exr[$sid]). "</b></td>");
	else
	  echo("<td class=nobot>&nbsp;</td>");
	echo("</TR>");

	// Second row for each student	
	echo("<TR><TD class=exnrnobot>&nbsp;</TD>");
	echo("<TD class=nobot>". $studs['bdate'][$six]. "</TD><td>cs</td>");
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
				else if($exstatus[$sid][$mid] >= 5)
					echo("class=free");
				echo(">");
				if($exstatus[$sid][$mid] > 1 && $exstatus[$sid][$mid] < 5)
					$miss = true;
			}
			else
				echo("<TD class=result>");
			// Vrijstellng?
			if(isset($exstatus[$studs['sid'][$six]][$mid]) && $exstatus[$studs['sid'][$six]][$mid] >= 5)
				if(isset($vexarray[$studs['sid'][$six]][$mid]))
					echo(number_format($vexarray[$studs['sid'][$six]][$mid],1,",","."));
				else
					echo("&nbsp;");
			else if(isset($hexarray[$studs['sid'][$six]][$mid]))
				if(strtolower(substr($hexarray[$studs['sid'][$six]][$mid],0,1)) == "v")
					echo("&nbsp;");
				else
					echo(number_format($hexarray[$studs['sid'][$six]][$mid],1,",","."));
			else
				echo("X");
			echo("</TD>");
			}
	  else
	  { // Does not have the subject
	    echo("<TD class=notchoosen>&nbsp</td>");
	  }
	}
	echo("<td class=nobot>&nbsp;</td><td class=nobot>&nbsp;</td><td class=nobot>&nbsp</td>");
	// Now we might have up to 3 repeated subject
	$repcnt = 0;
	foreach($subjects['mid'] AS $mid)
	{
	  if(isset($exstatus[$sid][$mid]) && ($exstatus[$sid][$mid] == 1 || $exstatus[$sid][$mid] == 2 || $exstatus[$sid][$mid] == 4) && $repcnt < 3)
	  {  // Do this repeated subject
	    echo("<td class=nobot>&nbsp</td>");
	    echo("<TD class=result>");
			//if(isset($exarray[$sid][$mid]))
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
    // First row ends with 2 blank coloumns or 1 if exr is set for this student
    echo("<td class=nobot>&nbsp;</td>");
	if(!isset($exr[$sid]))
	  echo("<td class=nobot>&nbsp;</td>");
    echo("</TR>");

    // Third row for each student	
    echo("<TR><TD class=exnrdbot>&nbsp;</TD>");
    echo("<TD class=dbot>". (isset($countries[$studs['bplace'][$six]]) ? $countries[$studs['bplace'][$six]] : $studs['bplace'][$six]). "</TD>");
	echo("<td class=dbot>e</td>");
	// Maintain some counters for this
	$subjcount = 0;
	$totpoints = 0;
	$negpoints = 0;
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
			$subjcount++;
				// Show exam result
			if(isset($exstatus[$sid][$mid]) && $exstatus[$sid][$mid] >= 5)
				echo("<TD class=freedbot>");
			else
				echo("<TD class=endresult>");
			// Vrijstelling?
			if(isset($exstatus[$studs['sid'][$six]][$mid]) && $exstatus[$studs['sid'][$six]][$mid] >= 5)
			{
				echo($exstatus[$studs['sid'][$six]][$mid] + 2);
				$totpoints += $exstatus[$studs['sid'][$six]][$mid] + 2;
			}
			else if(isset($hendarray[$studs['sid'][$six]][$mid]))
			{
				echo(number_format($hendarray[$studs['sid'][$six]][$mid],0,",","."));
				$totpoints += $hendarray[$studs['sid'][$six]][$mid];
				if($hendarray[$studs['sid'][$six]][$mid] < 6)
					$negpoints += 6 - $hendarray[$studs['sid'][$six]][$mid];
			}
			else
			{
				echo("X");
				$negpoints += 6;
			}
			echo("</TD>");
	  }
	  else
	  { // Does not have the subject
	    echo("<TD class=notchoosendbot>&nbsp</td>");
	  }
	}
	// Decide if passed exam for TV1
	if((($totpoints >= ($subjcount * 6 - 1) && $negpoints == 1) || ($totpoints >= ($subjcount * 6) && $negpoints <= 2)) &&
	   isset($lores[$sid]) && isset($ckvres[$sid]))
	  $passedtv1 = true;
	else
	  $passedtv1 = false;
	// Now maybe this student has extra subjects, failed according to extra subjects and can drop 1 or more to pass anyway...
	unset($midlist);
	if($subjcount > 6 && !$passedtv1)
	{
		// Create a list of mids for which this student has done exams
		$midindex = 1;
		if(isset($hendarray[$sid]))
			foreach($hendarray[$sid] AS $cmid => $dummy)
				$midlist[$midindex++] = $cmid;
		// Check each package for compliance, first count matching subjects
		unset($pkcandidates);
		if(isset($midlist))
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
				if($pscount < 6)
				unset($pkcandidates[$pkname]);
		// remove the package candidates that if the student were to choose it would not result in a pass
		if(isset($pkcandidates))
		foreach($pkcandidates AS $pkname => $dummy)
		{
			$newtotpoints = 0;
			$newnegpoints = 0;
			foreach($packmids[$pkname] AS $nwcmid)
			{
				if(isset($hendarray[$sid]) && isset($hendarray[$sid][$nwcmid]))
				{
					if(isset($exarray[$sid][$nwcmid]))
					{
						$newtotpoints += $hendarray[$sid][$nwcmid];
						$newnegpoints += ($hendarray[$sid][$nwcmid] < 6 ?  (6 - $hendarray[$sid][$nwcmid]) : 0);
					}
					else
						$newnegpoints += 6;
				}
			}
			if(!((($newtotpoints >= 35 && $newnegpoints == 1) || ($newtotpoints >= 36 && $newnegpoints <= 2)) &&
					 isset($lores[$sid]) && isset($ckvres[$sid])))
			{
				unset($pkcandidates[$pkname]);
				//$pkcandidates[$pkname] = $newtotpoints. "_". $newnegpoints;
			}
		}
	}

	if(isset($midlist) && isset($pkcandidates) && count($pkcandidates) >= 1)
	  $passedtv1 = true;

	if($passedtv1)
	{
	  if(strtoupper($studs['gender'][$six]) == "M")
	    $passTV1m++;
	  else
	    $passTV1v++;
	}
	else
    {
	  if($miss)
	  {
	    if(strtoupper($studs['gender'][$six]) == "M")
		  $missTV1m++;
		else
		  $missTV1v++;
	  }
	}
	if(!isset($midlist))
	  echo("<td class=dbotgreen><center>". $totpoints. "</center></td><td class=dbot><center>". ($passedtv1 ? "gesl" : "&nbsp;"). "</center></td>");
	else
	{
	  echo("<td class=dbotgreen><center>". $totpoints. "</center></td><td class=dbot><center>");
	  if(isset($pkcandidates) && count($pkcandidates >= 1))
	  {
	    $pkct = "";
	    foreach($pkcandidates AS $pkname => $pkscnt)
		  $pkct .= $pkname. ",";
		echo(substr($pkct,0,-1));
      }
	  echo("</center></td>");
	}
	echo("<td class=dbot><center>". ($passedtv1 ? "&nbsp;" : "afgew"). "</center></td>");
	// Now we might have up to 3 repeated subject
	$repcnt = 0;
	foreach($subjects['mid'] AS $sbix => $mid)
	{
	  if(isset($exstatus[$sid][$mid]) && ($exstatus[$sid][$mid] == 1 || $exstatus[$sid][$mid] == 2 || $exstatus[$sid][$mid] == 4) && $repcnt < 3)
	  {  // Do this repeated subject
	    echo("<td class=dbot>". $subjects['shortname'][$sbix]. "</td>");
			if(isset($exstatus[$sid][$mid]))
			{
				echo("<TD ");
				if($exstatus[$sid][$mid] == 1)
					echo("class=repeatdbot");
				else if($exstatus[$sid][$mid] == 2 || $exstatus[$sid][$mid] == 3 || $exstatus[$sid][$mid] == 4)
					echo("class=absentdbot");
				else if($exstatus[$sid][$mid] >= 5)
					echo("class=freedbot");
				echo(">");
			}
			else
	      echo("<TD class=dbotresult>");
			if(isset($endarray[$sid][$mid]) && isset($soarray[$sid][$mid]) && isset($herxarray[$sid][$mid]))
			{
				$newres = ($soarray[$studs['sid'][$six]][$mid] + $herxarray[$studs['sid'][$six]][$mid]) / 2.0;
				echo(number_format($newres,0,",","."));
				$totpoints += round($newres);
				if(isset($hendarray[$studs['sid'][$six]][$mid]))
					$totpoints -= $hendarray[$studs['sid'][$six]][$mid];
				else
					$negpoints -= 6;
				if(isset($hendarray[$studs['sid'][$six]][$mid]) && $hendarray[$studs['sid'][$six]][$mid] < 6)
					$negpoints -= (6 - $hendarray[$studs['sid'][$six]][$mid]);
				if(round($newres) < 6)
					$negpoints += 6 - round($newres);
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
	//echo("totpts = ". $totpoints. ", subjcnt=". $subjcount. ", negpts=". $negpoints. "<BR>");
	if((($totpoints >= ($subjcount * 6 - 1) && $negpoints == 1) || ($totpoints >= ($subjcount * 6) && $negpoints <= 2)) &&
	   isset($lores[$sid]) && isset($ckvres[$sid]))
	  $passedtv2 = true;
	else
	  $passedtv2 = false;

	// Now maybe this student has extra subjects, failed according to extra subjects and can drop 1 or more to pass anyway...
	unset($midlist);
  if($subjcount > 6 && !$passedtv2)
  {
	  // Create a list of mids for which this student has done exams
	  $midindex = 1;
	  if(isset($endarray[$sid]))
	    foreach($endarray[$sid] AS $cmid => $dummy)
				if($cmid != $lomid && $cmid != $rekmid)
	        $midlist[$midindex++] = $cmid;
	  // Check each package for compliance, first count matching subjects
	  unset($pkcandidates);
	  if(isset($midlist))
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
	      if($pscount < 6)
					unset($pkcandidates[$pkname]);
	  // remove the package candidates that if the student were to choose it would not result in a pass
	  if(isset($pkcandidates))
			foreach($pkcandidates AS $pkname => $dummy)
			{
				$newtotpoints = 0;
				$newnegpoints = 0;
				foreach($packmids[$pkname] AS $nwcmid)
				{
					if(isset($endarray[$sid]) && isset($endarray[$sid][$nwcmid]) && $nwcmid != $lomid && $nwcmid != $rekmid)
					{
						if(isset($exarray[$sid][$nwcmid]))
						{
							$newtotpoints += $endarray[$sid][$nwcmid];
							$newnegpoints += ($endarray[$sid][$nwcmid] < 6 ?  (6 - $endarray[$sid][$nwcmid]) : 0);
							//echo("<BR>Afset mid ". $nwcmid. ", tp=". $newtotpoints. ", np=". $newnegpoints. "<BR>");
						}
						else
						{
							$newnegpoints += 6;
							//echo("<BR>Added 6 negpoints because no exarray data is set for this subject (mid=". $nwcmid. ")");
						} 
					}
				}
				if(!((($newtotpoints >= 35 && $newnegpoints == 1) || ($newtotpoints >= 36 && $newnegpoints <= 2)) &&
	         isset($lores[$sid]) && isset($ckvres[$sid])))
				{
					unset($pkcandidates[$pkname]);
					//echo("<BR>Unset ". $pkname. "(tp=". $newtotpoints. ",np=". $newnegpoints. ",nc=". count($pkcandidates). ")");
					//$pkcandidates[$pkname] = $newtotpoints. "_". $newnegpoints;
				}
			}
  }

	if(isset($midlist) && isset($pkcandidates) && count($pkcandidates) >= 1)
	  $passedtv2 = true;
	//lse
	//	echo("<BR>failed 7->6 ". (!isset($midlist) ? "nml" : (!isset($pkcandidates) ? "npk" : count($pkcandidates))). "<BR>");

	// Added june 30th 2017: if a student passes TV1, always passes TV2 too, although may have had bad results for TV2!
	if($passedtv1)
		$passedtv2 = true;

	if($passedtv2)
	{
	  if(strtoupper($studs['gender'][$six]) == "M")
	    $passTV2m++;
	  else
	    $passTV2v++;
	}
	else
    {
	  if($miss)
	  {
	    if(strtoupper($studs['gender'][$six]) == "M")
				$missTV2m++;
			else
				$missTV2v++;
	  }
	}
	if(!isset($exr[$sid]))
	{
    echo("<td class=dbot><b>");
	  if($passedtv2)
	  {
	    if(!isset($midlist))
	      echo("Geslaagd (D". $subjcount. ")");
			else
			{
				if(isset($pkcandidates) && count($pkcandidates >= 1))
				{
					$pkct = "";
					foreach($pkcandidates AS $pkname => $pkscnt)
						$pkct .= $pkname. ",";
					echo("Geslaagd (D6)<BR>". substr($pkct,0,-1));
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
//				else
//					echo("nvs: ". $subjects['shortname'][$six]. "(end=". $endarray[$sid][$mid]. ",ex=". $exarray[$sid][$mid]. ",so=". $soarray[$sid][$mid]. ")");
	    }
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
  echo("<td class=sumheader colspan=3>Dat zich tijdens het schoolonderzoek of examen terugtrok of om geldige reden verhinderd was het schriftelijk examen te voltooien.</td>");
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
