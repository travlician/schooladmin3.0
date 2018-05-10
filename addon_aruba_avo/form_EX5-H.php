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
  $offsubjects = array(1 => "Ne","En","Sp","Wa","Wb","Na","Sk","Bi","Mo","Ec","Gs","Ak","CKV","Fa","Inf");
  $noexam = array("Ak","CKV");
	$coresubs = array("Ne","En","Wa","Wb");
  $altsubjects = array("Ne"=>1,"En"=>2,"Sp"=>3,"Wi-A"=>4,"Wi-B"=>5,"Na"=>6,"Sk"=>7,"Bio"=>8,"Ec"=>10,"M&O"=>9,"Ak"=>12,"Gs"=>11,"Pfw"=>14,"CKV"=>13,
                       "ne"=>1,"en"=>2,"sp"=>3,"wiA"=>4,"wiB"=>5,"na"=>6,"sk"=>7,"bio"=>8,"ec"=>10,"m&o"=>9,"ak"=>12,"gs"=>11,"pws"=>14,"ckv"=>13,"Fa"=>14,"Inf"=>15);
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
					   "NW22"=>"Ec","HU99"=>"Wa");
  $sub2full = array("Ne"=>"Nederlandse taal en literatuur", "En"=>"Engelse taal en literatuur", "Wa"=>"Wiskunde A",
                    "Ak"=>"Aardrijkskunde", "Gs"=>"Geschiedenis en staatsinrichting", "Sp"=>"Spaanse taal en literatuur",
					"Ec"=>"Economie", "Mo"=>"Management en organisatie", "Sk"=>"Scheikunde", "Na"=>"Natuurkunde",
					"Wb"=>"Wiskunde B", "Bi"=>"Biologie","CKV"=>"Culturele en kunstzinnige vorming");
  
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
		
		echo("<table class=ex5table border=0><tr><td class=nobot>&nbsp;</td><td class=lastname>Achternaam</td><td class=firstnamehead>Voornamen</td><td class=fatbotgreen rowspan=2>v<BR>m</td><td class=fatbotgreen rowspan=2><center>pro-<BR>fiel</center></td><td colspan=7 class=gemdeel><b>Gemeenschappelijk deel</td>");
		$skipsubj=0;
		foreach($subjects['shortname'] AS $sn)
		{
			if($skipsubj < 2)
				$skipsubj++;
			else
				echo("<td rowspan=2 class=subjheader>$sn</td>");
		}
		echo("<td class=fatbotgreen rowspan=2><center>TOT<BR>TV1</td><td class=fatbotgreen rowspan=2><center><BR>gCe</td><td colspan=2 class=nobotgreen><center>Uitslag 1ste tijdvak</center></td><td colspan=6 class=herhead><b><center>Herex</center></b></td>");
		echo("<td class=nobotgreen colspan=3><center><b>Einduitslag</b></center></td></tr>");
		
		echo("<tr><td class=fatbot>Ex-no</td><td class=fatbotgreenleft>Geb. datum/Land</td><td class=fatbotgreen>&nbsp;</td><td class=fatbot>&nbsp</td><td class=subjheadergem colspan=2>Combi</td><td class=fatbot>&nbsp;</td>");
		$skipsubj = 0;
		foreach($subjects['shortname'] AS $sn)
			if($skipsubj < 2)
			{
				echo("<td class=subjheadergem>$sn</td>");
				$skipsubj++;
			}
		echo("<td class=subjheadergem>Re</td>");
		
		echo("<td class=fatbotgreen><center>gesl</center></td><td class=fatbotgreen><center>afgew</center><td class=herbot>vak</td><td class=herbot>cijf</td><td class=herbot>vak</td><td class=herbot>cijf</td><td class=herbot>vak</td><td class=herbot>cijf</td>");
		echo("<td class=fatbotgreen>TOT</td><td class=fatbotgreen>gCe</td>");
		echo("<td class=fatbotgreen>&nbsp</td></tr>");
	
  }
  
  function print_foot()
  {
    global $pageno;
		echo("</TABLE>");
		echo("<p class=footer><span class=formdata>EX 5-H (". date('n/j/Y; g:i A'). ") Bron: myschoolresult.com</span><span class=pagenr>Volgnummer ". $pageno++. "</span>");
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
  
  // Get the data of the exam subject collections
  $packages = SA_loadquery("SELECT * FROM subjectpackage");
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
  $cquery = "SELECT sid,mid,result FROM gradestore WHERE period=3 AND year=\"". $schoolyear. "\" AND result <> '' AND result IS NOT NULL";
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
  $cquery .= " AND result <> '' AND result IS NOT NULL GROUP BY sid, mid, testdef.date ORDER BY testdef.date DESC";
  $cres = SA_loadquery($cquery);
  echo(mysql_error($userlink));
  if(isset($cres))
    foreach($cres['sid'] AS $cix => $csid)
		{
			$hexarray[$csid][$cres['mid'][$cix]] = round($cres['avgresult'][$cix],1);
		}

  // Get retry exam results 
  $cquery = "SELECT sid,mid,result AS avgresult FROM testresult LEFT JOIN testdef USING(tdid) LEFT JOIN class USING(cid) WHERE year=\"". $schoolyear. "\" AND period=3 AND type=\"Exam\"";
  $cquery .= " AND result <> '' AND result IS NOT NULL GROUP BY sid, mid, testdef.date ORDER BY testdef.date";
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
  $ismidqr = SA_loadquery("SELECT mid FROM subject WHERE shortname ='I&S'");
  if(isset($ismidqr['mid']))
    $ismid = $ismidqr['mid'][1];
  else
    $ismid = 0;
  $isresqr = SA_loadquery("SELECT sid,result FROM gradestore WHERE mid=". $ismid. " AND year='". $schoolyear. "' AND period=0 AND result > 0");
  if(isset($isresqr['sid']))
    foreach($isresqr['sid'] AS $lix => $lsid)
      $isres[$lsid] = round($isresqr['result'][$lix]);

  // Get the PFW info gotten this year
  $pfwmidqr = SA_loadquery("SELECT mid FROM subject WHERE shortname ='Pfw' OR shortname LIKE 'pws'");
  if(isset($pfwmidqr['mid']))
    $pfwmid = $pfwmidqr['mid'][1];
  else
    $pfwmid = 0;
  $pfwresqr = SA_loadquery("SELECT sid,result FROM gradestore WHERE mid=". $pfwmid. " AND year='". $schoolyear. "' AND period=0 AND result > 0");
  if(isset($pfwresqr['sid']))
    foreach($pfwresqr['sid'] AS $lix => $lsid)
      $pfwres[$lsid] = round($pfwresqr['result'][$lix]);

  // Convert I&S and PFW results and calculate result combivak
  foreach($studs['sid'] AS $ipsid)
  {
    if(!isset($isres[$ipsid]) && isset($ahxdata[$ipsid][$ismid]) && $ahxdata[$ipsid][$ismid] > 0)
			$isres[$ipsid] = round($ahxdata[$ipsid][$ismid]);
    if(!isset($pfwres[$ipsid]) && isset($ahxdata[$ipsid][$pfwmid]) && $ahxdata[$ipsid][$pfwmid] > 0)
			$pfwres[$ipsid] = round($ahxdata[$ipsid][$pfwmid]);
		if(isset($isres[$ipsid]) && isset($pfwres[$ipsid]))
			$combires[$ipsid] = round(($isres[$ipsid] + $pfwres[$ipsid]) / 2,0);
  }
  
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

  // Get certificate & vrijstelling results to give as so and ex results
  $excertdata = SA_loadquery("SELECT * FROM excertdata");
  if(isset($excertdata['sid']))
  {
    foreach($excertdata['sid'] AS $ecix => $ecsid)
		{
			$ecmid = $excertdata['mid'][$ecix];
			if($ecmid > 0 && $excertdata['seresult'][$ecix] > 0.0)
				$soarray[$ecsid][$ecmid] = $excertdata['seresult'][$ecix];
			if($ecmid > 0 && $excertdata['exresult'][$ecix] > 0.0)
				$hexarray[$ecsid][$ecmid] = $excertdata['exresult'][$ecix];
			if($ecmid == $rekmid)
				$rekres[$ecsid] = $excertdata['endresult'][$ecix];
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
				if($xstat >= 9 && $xstat <= 13 && $xmid != $ismid && $xmid != $pfwmid && $xmid != $rekmid)
					$hascert = true;
				if($hascert)
					$pn = "DC<BR>". $pn;
			}
		}
		echo("<TR><TD class=exnrnobot>". $studs['exnr'][$six]. "</TD>");
		echo("<TD class=nobot>". $studs['lastname'][$six]. "</TD><TD rowspan=3 class=firstname> ". $studs['firstname'][$six]. "</TD>");
		echo("<TD rowspan=3 class=dbot>". $studs['gender'][$six]. "</td><TD rowspan=3 class=dbot>". $pn. "</td>");
		echo("<TD>I&S</TD>");
		// row below should reflect I&S result with color code depending on ...
		if(isset($ahxdata[$studs['sid'][$six]][$ismid]))
		{
			echo("<TD class=free>");
		}
		else
			echo("<TD class=result>");
		if(isset($isres[$studs['sid'][$six]]))
			echo(number_format($isres[$studs['sid'][$six]],1,",","."));
		else
			echo("&nbsp;");
		echo("</td>");
		
		echo("<td>&nbsp;</td><td>se</td>");
		if($studs['gender'][$six] == "M" || $studs['gender'][$six] == "m")
			$totm++;
		else
			$totv++;
		$subixx = 0;
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
				// Vrijstelling or certificate?
				//if(isset($exstatus[$studs['sid'][$six]][$mid]) && $exstatus[$studs['sid'][$six]][$mid] >= 5)
				//  echo("&nbsp;");
				//else if(isset($soarray[$studs['sid'][$six]][$mid]))
				if(isset($soarray[$studs['sid'][$six]][$mid]))
				if(strtolower(substr($soarray[$studs['sid'][$six]][$mid],0,1)) == "v")
					echo("&nbsp;");
				else
						echo($soarray[$studs['sid'][$six]][$mid] > 0.5 ? number_format($soarray[$studs['sid'][$six]][$mid],1,",",".") : $soarray[$studs['sid'][$six]][$mid]);
				else
						echo("X");
					echo("</TD>");
			}
			else
			{ // Does not have the subject
				echo("<TD class=notchoosen>&nbsp;</td>");
			}
			// Rules for passing exam:
			// - vrijstelling telt als cijfer wat vorige keer is gegeven
			// - Certificaat telt als cijfer maar als er minstens 1 certificaat is moet alles voldoende zijn
			// - 1 x 5 en de rest voldoende
			// - max 2 vijven en gemiddeld 6 of meer
			// - max 1 vier zonder andere onvoldoendes en gemiddeld 6 of meer
			// Bij 8 vakken mag er ook 3 x 5 of 1 x 4 en 1 x 5 voorkomen, bij 3 x 5 moeten dan 2 onvoldoendes in de keuzevakken zijn
			// - 1 drie of lager => jammer dan
			// Gemiddelde examens < 5.5 => jammer dan
			// Meer dan 1 tekort voor kernvaken => jammer dan
			// Speciaal voor 8de vakken: 
			// 7 vakken binnen profiel volgens bovenstaande regels
			// 
			$subixx++;
			if($subixx == 2) // Show empty space for rekenen
				echo("<TD class=result>-</td>");
		}
		//echo("<TD>". $subix. "</td>");
		echo("<td class=nobot>&nbsp;</td><td class=nobot>&nbsp;</td><td class=nobot>&nbsp;</td><td class=nobot>&nbsp;</td>");
		// Now we might have a repeated subject
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
		// First row ends with 3 blank coloumns or 2 blank, endresult
		echo("<td class=nobot>&nbsp;</td>");
		echo("<td class=nobot>&nbsp;</td>");
		if(isset($exr[$sid]))
			echo("<td class=dbot rowspan=3><b>". nl2br($exr[$sid]). "</b></td>");
		else
			echo("<td class=nobot>&nbsp;</td>");
		echo("</TR>");

		// Second row for each student	
		echo("<TR><TD class=exnrnobot>&nbsp;</TD>");
		echo("<TD class=nobot>". $studs['bdate'][$six]. "</TD><td>Pfw</td>");
		//  row below should reflect Pfw result and color coded depending on...
		if(isset($ahxdata[$studs['sid'][$six]][$pfwmid]))
		{
			echo("<TD class=free>");
		}
		else
			echo("<TD class=result>");
		if(isset($pfwres[$studs['sid'][$six]]))
			echo(number_format($pfwres[$studs['sid'][$six]],1,",","."));
		else
			echo("&nbsp;");
		echo("</td>");
		$pfwvak = "&nbsp;";
		foreach($sub2full AS $vks => $vkf)
			if($vkf == $studs['pfwvak'][$six])
				$pfwvak = $vks;
		echo("<td>". $pfwvak."</td><td>cs</td>"); //~~~
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
					else
						echo("class=result");
					echo(">");
					if($exstatus[$sid][$mid] > 1 && $exstatus[$sid][$mid] < 5)
						$miss = true;
				}
				else
					echo("<TD class=result>");
				// Vrijstelling or certificate?
				//if(isset($exstatus[$studs['sid'][$six]][$mid]) && $exstatus[$studs['sid'][$six]][$mid] >= 5)
				//  echo("&nbsp;");
				//else if(isset($hexarray[$studs['sid'][$six]][$mid]))
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
		// First row ends with 3 blank coloumns or 2 if exr is set for this student
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
		
		echo("<td class=dbot>&nbsp;</td><td class=dbot>e</td>");
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
				$negpoints = 6 - $combires[$studs['sid'][$six]];
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
							$coreshort += 6 - $hendarray[$studs['sid'][$six]][$mid];
						if($hendarray[$studs['sid'][$six]][$mid] < 4)
							$fullfail++;
						// See if this is a choice subject
						if((isset($choicesubjs[$studs['packagename'][$six]]) && $mids[$choicesubjs[$studs['packagename'][$six]]] == $mid) ||
							 (isset($studs['extrasubject'][$six]) && $studs['extrasubject'][$six] == $mid) ||
							 (isset($studs['extrasubject2'][$six]) && $studs['extrasubject2'][$six] == $mid) ||
							 (isset($studs['extrasubject3'][$six]) && $studs['extrasubject3'][$six] == $mid))
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
					echo("<TD class=result>". $rekres[$studs['sid'][$six]]. "</td>");
				else
					echo("<TD class=result>X</td>");
			}
		}
		// Decide if passed exam for TV1 
		// First calculte the average of exam results
		if($extotcnt > 0)
			$exavg = $extotval / $extotcnt;
		else
			$exavg = 0;
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
					if($cmid != $ismid && $cmid != $pfwmid)
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
						unset($pkcandidates[$pkname]);
			// remove the package candidates that if the student were to choose it would not result in a pass
			if(isset($pkcandidates))
			foreach($pkcandidates AS $pkname => $dummy)
			{
				$newtotpoints = $totpoints;
				$newnegpoints = $negpoints;
				$newcoreshort = $coreshort;
				$newextotval = $extotval;
				$newextotcnt = $extotcnt;
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
			if($studs['gender'][$six] == "M" || $studs['gender'][$six] == "m")
				$passTV1m++;
			else
				$passTV1v++;
		}
		else
		{
			if($miss)
			{
				if($studs['gender'][$six] == "M" || $studs['gender'][$six] == "m")
					$missTV1m++;
				else
					$missTV1v++;
			}
		}
		echo("<td class=dbotgreen><center>". $totpoints. "</center></td><td class=dbot><center>". ($exavg > 0.0 ? round($exavg - 0.005,2) : "-"). "</center></td><td class=dbot><center>". ($passedtv1 ? "gesl" : "&nbsp;"). "</center></td>");
		if(isset($midlist))
		{
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
		// Now we might have a repeated subject
		$repcnt = 0;
		foreach($subjects['mid'] AS $sbix => $mid)
		{
			if(isset($exstatus[$sid][$mid]) && ($exstatus[$sid][$mid] == 1 || $exstatus[$sid][$mid] == 2 || 
				 $exstatus[$sid][$mid] == 4) && $repcnt < 3)
			{  // Do this repeated subject
				if(isset($exarray[$sid][$mid]))
				{
					$extotval -= $hexarray[$sid][$mid];
					$extotval += $exarray[$sid][$mid];
				}
				//else
					//echo("<WVW>NO exarray</WVW>");
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
				if(isset($endarray[$sid][$mid]) && isset($soarray[$sid][$mid]) && (isset($exarray[$sid][$mid]) || in_array($mid2sjname[$mid],$noexam)))
				{
					echo(number_format($endarray[$studs['sid'][$six]][$mid],0,",","."));
					$totpoints += round($endarray[$studs['sid'][$six]][$mid]);
					if(isset($hendarray[$studs['sid'][$six]][$mid]))
						$totpoints -= round($hendarray[$studs['sid'][$six]][$mid]);
					else
					{
						$negpoints -= 6;
						$fullfail--;
						$fails--;
					}
					if(isset($hendarray[$studs['sid'][$six]][$mid]) && $hendarray[$studs['sid'][$six]][$mid] < 6)
					{
						$negpoints -= 6 - round($hendarray[$studs['sid'][$six]][$mid]);
						if(in_array($mid2sjname[$mid],$coresubs))
							$coreshort -= 6 - $hendarray[$studs['sid'][$six]][$mid];							
						$fails--;
						if($hendarray[$studs['sid'][$six]][$mid] < 4)
							$fullfail--;
					}
					if($endarray[$studs['sid'][$six]][$mid] < 6)
					{
						$negpoints += 6 - round($endarray[$studs['sid'][$six]][$mid]);
						if(in_array($mid2sjname[$mid],$coresubs))
							$coreshort += 6 - $endarray[$studs['sid'][$six]][$mid];
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
			//echo("<td>". "[". $certconditions. ",". $totpoints. ",". $negpoints. ",". $subjcount. ",". $exavg. ",". $coreshort. "]</td>");
		}

		// Now maybe this student has 8 subjects, failed according to 8 subjects and can drop 1 to pass anyway...
		unset($midlist);
		if($subjcount > 8 && !$passedtv2)
		{
			// Create a list of mids for which this student has done exams
			$midindex = 1;
			if(isset($endarray[$sid]))
				foreach($endarray[$sid] AS $cmid => $dummy)
					if($cmid != $ismid && $cmid != $pfwmid)
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
						unset($pkcandidates[$pkname]);
			// remove the package candidates that if the student were to choose it would not result in a pass
			if(isset($pkcandidates))
			foreach($pkcandidates AS $pkname => $dummy)
			{
				$newtotpoints = $totpoints;
				$newnegpoints = $negpoints;
				$newcoreshort = $coreshort;
				$newextotval = $extotval;
				$newextotcnt = $extotcnt;
				$newsubjcount = 1; // Combivak must be present if we get here and is one of the subjects
				foreach($midlist AS $cmid)
				{
					if(!in_array($cmid,$pkmidar))
					{ // This subject is not in the package so correct points
						$newtotpoints -= $endarray[$sid][$cmid];
						if($endarray[$sid][$cmid] < 6)
						{
							$newnegpoints -= 6 - $endarray[$sid][$cmid];
							if(in_array($mid2sjname[$cmid],$coresubs))
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
						$newsubjcount++;
				}
				// Evaluate if failed for this package
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
			if($studs['gender'][$six] == "M" || $studs['gender'][$six] == "m")
				$passTV2m++;
			else
				$passTV2v++;
		}
		else
		{
			if($miss)
			{
				if($studs['gender'][$six] == "M" || $studs['gender'][$six] == "m")
					$missTV2m++;
				else
					$missTV2v++;
			}
		}
		// Show exam average
		echo("<td class=dbot>". ($exavg > 0.0 ? round($exavg - 0.005,2) : "-"). "</td>");

		if(!isset($exr[$sid]))
		{
			// Remove any reward assigned to erroneous periods
			mysql_query("DELETE FROM s_beloning_2_". substr($schoolyear,0,4). " WHERE sid=". $sid, $userlink);
			mysql_query("DELETE FROM s_beloning_3_". substr($schoolyear,0,4). " WHERE sid=". $sid, $userlink);
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
						echo("Geslaagd (D". ($subjcount - 1). ")<BR>". substr($pkct,0,-1));
					}
				}
				// Register reward for passing exam
				$yrpf = substr($schoolyear,0,4);
				$insgqr = SA_loadquery("SELECT data FROM s_inschrijfgeld WHERE sid=". $sid);
				if(isset($insgqr['data'][1]))
					mysql_query("REPLACE INTO s_beloning_1_". $yrpf. " (sid,data) VALUES (". $sid. ",". ($insgqr['data'][1] - 100.0). ")", $userlink);
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
				// Remove registered reward
				mysql_query("DELETE FROM s_beloning_1_". substr($schoolyear,0,4). " WHERE sid=". $sid, $userlink);
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
