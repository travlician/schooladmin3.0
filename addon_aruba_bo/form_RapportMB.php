<?php
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2015 Aim4me N.V.   (http://www.aim4me.com)	      |
// +----------------------------------------------------------------------+
// | Authors: Carlos kelkboom / Wilfred van Weert - aim4me.com            |
// +----------------------------------------------------------------------+
//
  session_start();
  require_once("schooladminfunctions.php");
  require_once("student.php");
  // Link with database
  inputclassbase::dbconnect($userlink);
	if(!isset($pngsource))
		$pngsource="PNG";
	// Check if rapportdatum is valid format
	if(isset($_POST['rapportdatum']))
	{
		if(strlen($_POST['rapportdatum']) < 10)
			$invaliddate = true;
		else
		{
			$invaliddate = false;
			$d=substr($_POST['rapportdatum'],0,2);
			$m=substr($_POST['rapportdatum'],3,2);
			$y=substr($_POST['rapportdatum'],6,4);
			if($d < 1 || $d > 31 || $m < 1 || $m > 12 || $y < 2015 || $y > 3000)
			{
				$invaliddate=true;
			}
		}
		if($invaliddate)
		{
			echo("Ongeldige datum ingevoerd (". $_POST['rapportdatum']. ")<BR>");
		}
	}
	if(!isset($_POST['rapportdatum']) || $invaliddate)
	{
		echo("<FORM METHOD=POST>Rapportdatum (formaat dd-mm-yyyy): <INPUT TYPE=TEXT NAME=rapportdatum><INPUT TYPE=SUBMIT VALUE='Afdrukken'></FORM>");
		exit;
	}

	// Get the list of social emotional aspects
	$socemoaspectsqr = inputclassbase::load_query("SELECT * FROM bo_houding_defs WHERE categorie='Sociaal emotionele ontwikkeling' ORDER BY aspectid");
  if(isset($socemoaspectsqr))
		foreach($socemoaspectsqr['aspect'] AS $apix => $aspect)
	    $socemoaspect[$aspect] = $socemoaspectsqr['omschrijving'][$apix];
	// Get the list of attitude aspects
	$attaspectsqr = inputclassbase::load_query("SELECT * FROM bo_houding_defs WHERE categorie='Werkhouding' ORDER BY aspectid");
  if(isset($attaspectsqr))
		foreach($attaspectsqr['aspect'] AS $apix => $aspect)
	    $attaspect[$aspect] = $attaspectsqr['omschrijving'][$apix];
  // Get the year
  $schoolyear = SA_loadquery("SELECT year FROM period");
  $schoolyear = $schoolyear['year'][1];
	// Get the name of the teacher for the current group
	$mygrp = new group();
	$mygrp->load_current();
	$teachername = $mygrp->get_mentor()->get_username();
	// Now see if we got a backgroundimage in the library
	$bgimgqr = inputclassbase::load_query("SELECT libid FROM libraryfiles WHERE folder='/Rapport graphics' AND filename='achtergrond.png' OR filename='achtergrond.jpg' ORDER BY filename DESC");
	if(isset($bgimgqr['libid']))
		$imgurl = "Library.php?DownloadFile=". $bgimgqr['libid'][0];
?>
<html>
 <head>
   <META NAME="Author"	CONTENT="Owner">
   <META NAME="GENERATOR" CONTENT="">
   <META NAME="KEYWORDS" CONTENT="">
   <META NAME="DESCRIPTION" CONTENT="">
   <META NAME="HEADER" CONTENT="">

   <TITLE>Rapport MPbasis</TITLE>
   <LINK rel="stylesheet" type="text/css" href="style_BolletjesOvzPKs.css">
   </head>
<body summary="Rapport Mon Plaisir Basisschool">
<?
  // Translate the current group to a group id (gid)
  $sql_result = mysql_query("SELECT gid FROM sgroup WHERE active=1 AND groupname='". $_SESSION['CurrentGroup']. "'",$userlink);
  $gid = mysql_result($sql_result,0,'gid');
  // Get the list of periods with their details
  $periods = SA_loadquery("SELECT * FROM period WHERE status='open' ORDER BY id");
  if(!isset($periods['year']))
    $periods = SA_loadquery("SELECT * FROM period ORDER BY id DESC");
  $curperiod = $periods['id'][1];
  $curyear = $periods['year'][1];
  // Get the year result
  $yres = SA_loadquery("SELECT sid,result,advice FROM bo_jaarresult_data LEFT JOIN student USING(sid) LEFT JOIN sgrouplink USING(sid) WHERE gid=". $gid. " AND year='". $curyear. "'");
  if(isset($yres)) // convert it to easier array
    foreach($yres['sid'] AS $rix => $rsid)
	{
	  $yrresult[$rsid] = $yres['result'][$rix];
	  $yradvice[$rsid] = $yres['advice'][$rix];
	}
// Get a list of students
$lllist = student::student_list();
if(isset($lllist))
foreach($lllist AS $stud)
{
	if($stud != NULL)
	{
		// get the result for this student & year
		$stresqr = inputclassbase::load_query("SELECT * FROM gradestore LEFT JOIN subject USING(mid) WHERE sid=". $stud->get_id(). " AND year='". $schoolyear. "' AND period < ". (date("n") > 8 ? 2 : (date("n") > 3 ? 4 : 3)));
		unset($stres);
		if(isset($stresqr))
			foreach($stresqr['result'] AS $strix => $sres)
				$stres[$stresqr['shortname'][$strix]][$stresqr['period'][$strix]] = $sres;
		// Get the aspect results for this student
		unset($llar);
		// Get the behaviour results for this year
		$llarqr = inputclassbase::load_query("SELECT * FROM bo_houding_data WHERE year='". $schoolyear. "' AND sid=". $stud->get_id());
		if(isset($llarqr))
			foreach($llarqr['aspect'] AS $llix => $aspect)
				$llar[$aspect][$llarqr['period'][$llix]] = $llarqr['xstatus'][$llix];
		// Get the absence for this year
		unset($absqr);
		$absq = "SELECT 
							SUM(IF(acid=1 AND date > startp1 AND date <= endp1 AND (authorization='Yes' OR authorization='Parent'),1,0)) AS absokp1,
							SUM(IF(acid=1 AND date > startp1 AND date <= endp1 AND (authorization='No' OR authorization='Pending'),1,0)) AS absnop1,
							SUM(IF(acid=1 AND date > endp1 AND date < startp3 AND (authorization='Yes' OR authorization='Parent'),1,0)) AS absokp2,
							SUM(IF(acid=1 AND date > endp1 AND date < startp3 AND (authorization='No' OR authorization='Pending'),1,0)) AS absnop2,
							SUM(IF(acid=1 AND date >= startp3 AND (authorization='Yes' OR authorization='Parent'),1,0)) AS absokp3,
							SUM(IF(acid=1 AND date >= startp3 AND (authorization='No' OR authorization='Pending'),1,0)) AS absnop3,
							SUM(IF(acid=2 AND date > startp1 AND date <= endp1 AND (authorization='Yes' OR authorization='Parent'),1,0)) AS lateokp1,
							SUM(IF(acid=2 AND date > startp1 AND date <= endp1 AND (authorization='No' OR authorization='Pending'),1,0)) AS latenop1,
							SUM(IF(acid=2 AND date > endp1 AND date < startp3 AND (authorization='Yes' OR authorization='Parent'),1,0)) AS lateokp2,
							SUM(IF(acid=2 AND date > endp1 AND date < startp3 AND (authorization='No' OR authorization='Pending'),1,0)) AS latenop2,
							SUM(IF(acid=2 AND date >= startp3 AND (authorization='Yes' OR authorization='Parent'),1,0)) AS lateokp3,
							SUM(IF(acid=2 AND date >= startp3 AND (authorization='No' OR authorization='Pending'),1,0)) AS latenop3
						FROM absence LEFT JOIN absencereasons USING (aid)
						LEFT JOIN (SELECT enddate AS endp1 FROM period WHERE id=1 LIMIT 1) AS t1 ON(1=1)
						LEFT JOIN (SELECT startdate AS startp3 FROM period WHERE id=3 LIMIT 1) AS t2 ON(1=1)
						LEFT JOIN (SELECT startdate AS startp1 FROM period WHERE id=1 LIMIT 1) AS t3 ON(1=1)
						WHERE sid=". $stud->get_id(). " GROUP BY sid";
		$absqr = inputclassbase::load_query($absq);
		if($stud->get_student_detail("s_geenabsentieoprapport") == "Afwezigheid" || $stud->get_student_detail("s_geenabsentieoprapport") == "Afwezigheid en Te laat")
		{
			$absqr['absokp1'] = " ";
			$absqr['absokp2'] = " ";
			$absqr['absokp3'] = " ";
			$absqr['absnop1'] = " ";
			$absqr['absnop2'] = " ";
			$absqr['absnop3'] = " ";			
		}
		if($stud->get_student_detail("s_geenabsentieoprapport") == "Te laat" || $stud->get_student_detail("s_geenabsentieoprapport") == "Afwezigheid en Te laat")
		{
			$absqr['lateokp1'] = " ";
			$absqr['lateokp2'] = " ";
			$absqr['lateokp3'] = " ";
			$absqr['latenop1'] = " ";
			$absqr['latenop2'] = " ";
			$absqr['latenop3'] = " ";			
		}
		// Get remarks for each period
		unset ($opmrap);
		unset ($opmdate);
		// Set the rapportdatum for the current period
		if(date("n") >= 9)
			$rper = 1;
		else if (date("n") > 4)
			$rper = 3;
		else
			$rper = 2;
		mysql_query("UPDATE bo_opmrap_data SET lastmodifiedat='". inputclassbase::nldate2mysql($_POST['rapportdatum']). " 11:00:00' WHERE year='". $schoolyear. "' AND period=". $rper. " AND sid=". $stud->get_id(), $userlink);
		$opmrapqr = inputclassbase::load_query("SELECT * FROM bo_opmrap_data WHERE year='". $schoolyear. "' AND sid=". $stud->get_id());
		if(isset($opmrapqr))
			foreach($opmrapqr['opmtext'] AS $orix => $opm)
			{
				$opmrap[$opmrapqr['period'][$orix]] = $opm;
				$opmdate[$opmrapqr['period'][$orix]] = inputclassbase::mysqldate2nl(substr($opmrapqr['lastmodifiedat'][$orix],0,10));
			}

		// Page 4 of 4 : remarks and signing and absence
		echo('<table align="left" width="53%" style="padding-right: 5%; border-spacing: 0px; float: left;"><tr><td class="Titeltekst"><b>Afwezig en te laat</b></td><td class=trimesterid>1</td><td class=trimesterid>2</td><td class=trimesterid>3</td></tr>');
		echo("<TR><TD class=mainsubjtxt>Afwezig (geoorloofd/ongeoorloofd)</td>
							<td class=resultcol>". (isset($absqr['absokp1']) ? $absqr['absokp1'][0]. " / ". $absqr['absnop1'][0] : "0 / 0"). "</td>");
		if(date("n") < 9)
			echo("<td class=resultcol>". (isset($absqr['absokp2']) ? $absqr['absokp2'][0]. " / ". $absqr['absnop2'][0] : "0 / 0"). "</td>");
		else
			echo("<td class=resultcol>&nbsp;</td>");
		if(date("n") > 4 && date("n") < 9)
			echo("<td class=resultcol>". (isset($absqr['absokp3']) ? $absqr['absokp3'][0]. " / ". $absqr['absnop3'][0] : "0 / 0"). "</td></tr>");
		else
			echo("<td class=resultcol>&nbsp;</td></tr>");
		echo("<TR><TD class=mainsubjtxt>Te laat (geoorloofd/ongeoorloofd)</td>
							<td class=resultcol>". (isset($absqr['lateokp1']) ? $absqr['lateokp1'][0]. " / ". $absqr['latenop1'][0] : "0 / 0"). "</td>");
		if(date("n") < 9)
			echo("<td class=resultcol>". (isset($absqr['lateokp2']) ? $absqr['lateokp2'][0]. " / ". $absqr['latenop2'][0] : "0 / 0"). "</td>");
		else
			echo("<td class=resultcol>&nbsp;</td>");
		if(date("n") > 4 && date("n") < 9)
			echo("<td class=resultcol>". (isset($absqr['lateokp3']) ? $absqr['lateokp3'][0]. " / ". $absqr['latenop3'][0] : "0 / 0"). "</td></tr>");
		else
			echo("<td class=resultcol>&nbsp;</td></tr>");
		echo('<tr><td class="Titeltekst"><BR><b>Opmerkingen en aandachtspunten</b></td></tr>');
		for($p=1; $p<=3; $p++)
		{
			echo("<TR><TD class=rapportline>". $p. "e rapport dd ". (isset($opmdate[$p]) ? $opmdate[$p] : "&nbsp;"). "</td></tr>");
			echo("<TR style='height:100px;'><td class=remarks COLSPAN=4>". (isset($opmrap[$p]) ? $opmrap[$p] : "&nbsp;"). "</td></tr>");
		}
		// Jaar resultaat
		if(isset($yrresult[$stud->get_id()]) || isset($yradvice[$stud->get_id()]))
		{
			echo("<TR><TD>");
			if($yrresult[$stud->get_id()] == "OVER")
				echo("BEVORDERD");
			else if($yrresult[$stud->get_id()] == "NIET OVER")
				echo("NIET BEVORDERD");
			else if($yrresult[$stud->get_id()] == "O.W.L.")
				echo("NIET BEVORDERD, Gaat wegens leeftijd naar ". (substr($CurrentGroup,0,1) < 6 ? "klas ". (substr($_SESSION['CurrentGroup'],0,1) + 1) : "EPB"));
			else if($yrresult[$stud->get_id()] == "S.V.")
				echo("NIET BEVORDERD; verwezen naar ". (isset($ref[$stud->get_id()]) ? $ref[$stud->get_id()] : " een andere school"). ".");

			if(isset($yradvice[$stud->get_id()]) && $yradvice[$stud->get_id()] != "")
			{
				echo(" Advies: ". $yradvice[$stud->get_id()]);
			}
			echo("</td></tr>");
		}

		// Space for signing
		echo("<TR><TD COLSPAN=4><BR><BR>");
		echo("<table style='width: 100%; border-spacing: 0px;'<TR><td class=remarks><b>Handtekening leerkracht</b></td><td class=remarks><b>Handtekening schoolhoofd</b></td></tr>");
		echo("<TR><TD style='border: 1px solid black; height: 70px;'>&nbsp;</td><TD style='border: 1px solid black; height: 70px;'>&nbsp;</td></tr></table>");
		echo('</table>');
		
		// Page 1 of 4, Front page
		// The current yearlayer is extracted from the current groupname
		$curyear = 0 + substr($_SESSION['CurrentGroup'],0,1);
		// The year for reference is extracted from the schoolyear
		$refyear = substr($schoolyear,2,2);
		/* foreach($stres AS $sbi => $rrw)
		{
			echo("<BR>". $sbi);
			foreach($rrw AS $pi => $pr)
			  echo(" ". $pi. ":". $pr);
		} */
		//V1 echo('<table width="50%" style="border-collapse: collapse;"><tr><td colspan=2>&nbsp;</td><td class=trimesterid>1</td><td class=trimesterid>2</td><td class=trimesterid>3</td></tr>');
		//echo('<table width="46%" style="border-collapse: collapse; float: right;"><tr><td colspan=2 class=headingtext><img class=logospot src=schoollogo.png><LABEL>Leerling:</LABEL><B>'. $stud->get_name(). '</b> <BR><LABEL>Klas:</LABEL><B>'. $_SESSION['CurrentGroup']. '</b><BR><LABEL>Schooljaar:</LABEL><B>'. $schoolyear. '</td><td class=trimesterid>1</td><td class=trimesterid>2</td><td class=trimesterid>3</td></tr>');
		// 27 11 2017: add 4th trimeter as end result only for certain subjects and classes 3 and higher
		echo('<table width="46%" style="border-collapse: collapse; float: right;"><tr><td colspan=2 class=headingtext><img class=logospot src=schoollogo.png><LABEL>Leerling:</LABEL><B>'. $stud->get_name(). '</b> <BR><LABEL>Klas:</LABEL><B>'. $_SESSION['CurrentGroup']. '</b><BR><LABEL>Schooljaar:</LABEL><B>'. $schoolyear. '</td><td class=trimesterid>1</td><td class=trimesterid>2</td><td class=trimesterid>3</td>');
		if(substr($_SESSION['CurrentGroup'],0,1) > 2)
			echo("<td class=trimesterid>Eind</td>");
		echo('</tr>');

		show_result("Godsdienst","go",true,true);
		echo("<tr><td class=tablespace colspan=4></td></tr>");
		show_result("<span style='float: right; padding-right: 100px;'>Gemiddelde</span>Taal","ne",true,false,$curyear==1,false,false,false,true);
		// show_result("Mondeling taalgebruik","mo",false,false,$curyear==1); Change request 2 mar 2016:
		show_result("Mondeling taalgebruik","mo",false,true);
		if($curyear > 1)
		{
			show_result("Stellen","st",false,false);
			show_result("Taalbeschouwing","tb",false,false);
		}
		show_result("Leesbegrip","lb",false,false,$curyear==1);
		show_result("Woordenschat","ws",false,false,$curyear==1);
		show_result("Spelling","spe",false,false,$curyear==1,false,true);
		echo("<tr><td class=tablespace colspan=4></td></tr>");
		if($curyear == 1)
		{
			show_result("<span style='float: right; padding-right: 100px;'>Gemiddelde</span>Lezen","le",true,false,true,false,true,true);
			show_result("Leesvoorwaarden","lv",false,false,true,false,false);
			show_result("Woorden lezen","wl",false,false,true,false,false);
			show_result("Zinnen lezen","zl",false,false,true,false,true);
			
		}
		else
		{
			show_avi("Technisch lezen","AVI",$curyear);
			show_result("AVIniveau","AVI",false,false,false,true,true,false);
		}
		echo("<tr><td class=tablespace colspan=4></td></tr>");
		
		// Since sub-subjects are being phased out, reference point: only show in years 3-6 in 2015-2016, next year only 4-6 etc. we need to find out what to show
		if($refyear - $curyear > 11)
		{ // Need to show only main
			show_result("Rekenen","re",true,false,$curyear==1,false,false,false,true);			
		}
		else
		{ // Need to show the subs as well
			// V1echo("<tr><td class=mainsubjtxt colspan=2>Rekenen</td><td class=resultcol>&nbsp;</td><td class=resultcol>&nbsp;</td><td class=resultcol>&nbsp;</td></tr>");
			show_result("<span style='float: right; padding-right: 100px;'>Gemiddelde</span>Rekenen","re",true,false,false,false,false,false,true); 
			show_result("Getalbegrip","gb",false,false);
			show_result("Basisvaardigheden","bv",false,false);
			show_result("Meten en meetkunde","mm",false,false);
			show_result("Verhoudingen","vpb",false,false,false,true);
			// V1show_result("<b>Gemiddelde</b>","re",false,false);			
		}
		echo("<tr><td class=tablespace colspan=4></td></tr>");
		show_result("Wereldoriëntatie","wo". ($curyear < 3 ? "O" : ""),true,$curyear < 3,false,false,false,false,true);
		if($curyear == 3)
		{
			echo("<tr><td class=tablespace colspan=4></td></tr>");
			show_result("Zwemniveau","zw". ($curyear < 3 ? "O" : ""),true,false,false,true);			
		}
		if($curyear == 5)
		{
			echo("<tr><td class=tablespace colspan=4></td></tr>");
			show_result("Verkeersexamen","vk",true,false);			
		}
		if($curyear >= 5) // Show foreign languages
		{
			echo("<tr><td class=tablespace colspan=4></td></tr>");			
			show_result("Papiaments","pa",true,false);
			show_result("Engels","en",true,false);
			show_result("Spaans","spa",true,false);
		}
		echo("<tr><td class=tablespace colspan=4></td></tr>");
		show_result("Schrijven","sc",true,false,$curyear==1);
		echo("<tr><td class=tablespace colspan=4></td></tr>");
		show_result("Gym","lo",true,false,$curyear==1);
		show_result("Handvaardigheid","hv",true,true);
		show_result("Tekenen","te",true,true);
		show_result("Muziek","mu",true,true);
		echo('</table>');

		echo('<DIV class=pagebreak>&nbsp;</DIV>');
		
		// Page 2 of 4: social emotional aspects
		echo('</DIV>
		<br>
		<!-- Titel tabel: -->
		<table class="PlekTableLinks">
			<tr>
				<td class="Titeltekst"><b>Werkhouding</b> van '. $stud->get_firstname(). " ". $stud->get_lastname().
		'</td></tr>');
		// Now present each attidude aspects
		foreach($attaspect AS $aspect => $aspectdescription)
		{
			$splittext = explode("-",$aspectdescription,2);
			echo('<tr>	  <td class="TekstRandLinks"> <b>');
			echo($splittext[0]);
			echo('</b></td><td class="PeriodeRand">');
			for($per=1; $per <= 3; $per++)
			{
				echo($per);
				echo("&nbsp;&nbsp;<img src='". $pngsource. "/BolS");
				if(isset($llar[$aspect][$per]))
					echo($llar[$aspect][$per]);
				else
					echo("0");
				echo(".PNG' border='0'>");
				if($per < 3)
					echo("<BR>");			
			}
			echo('</td><td class="TekstRandRechts"><b>');
			echo($splittext[1]);
			echo('</td></tr>');
		}
		if(isset($imgurl))
		{ // A url is defined for the background image, let's show it
			echo("<TR><TD COLSPAN=4><IMG height=180 width=100% SRC='". $imgurl. "'></td></tr>");			
		}
		echo('</table>');
		echo('</b></DIV>');

		// Page 3 of 4: social emotional aspects
		echo('
		 <!-- Titel tabel rechts: Sociaal Emotionele Ontwikkeling -->
	   <table class=LeftTable>
			<tr>
				<td class="Titeltekst" colspan=4><b>Sociaal Emotionele Ontwikkeling</b></td>
		  </tr>');
		
		// Now present each social emotional aspects
		foreach($socemoaspect AS $aspect => $aspectdescription)
		{
			$splittext = explode("-",$aspectdescription,2);
			echo('<tr>	  <td class="TekstRandLinks"> <b>');
			echo($splittext[0]);
			echo('</b></td><td class="PeriodeRand">');
			for($per=1; $per <= 3; $per++)
			{
				echo($per);
				echo("&nbsp;&nbsp;<img src='". $pngsource. "/BolS");
				if(isset($llar[$aspect][$per]))
					echo($llar[$aspect][$per]);
				else
					echo("0");
				echo(".PNG' border='0'>");
				if($per < 3)
					echo("<BR>");			
			}
			echo('</td><td class="TekstRandRechts"><b>');
			echo($splittext[1]);
			echo('</td></tr>');
		}
		echo('</table>');
		

		echo('<DIV class=pagebreak>&nbsp;</DIV>');

	} // End if student is not NULL
}

function show_result($subjfull,$subjid,$normalsubj,$showletters,$showletters12=false,$noproc=false,$subfinal=false,$maini=false,$per4=false)
{
	global $stres;
	if($normalsubj)
	{
		if($maini)
			echo("<TR><td class=transcel>&nbsp;</td><TD class=mainsubjtxt>". $subjfull. "</td>");
		else
			echo("<TR><TD COLSPAN=2 class=mainsubjtxt>". $subjfull. "</td>");			
	}
	else if($subfinal)
		echo("<TR><td class=transcel>&nbsp;</td><TD class=subsubjtxtf>". $subjfull. "</td>");
	else
		echo("<TR><td class=transcel>&nbsp;</td><TD class=subsubjtxt>". $subjfull. "</td>");
	for($p=1; $p<=3; $p++)
	{
		if($normalsubj)
			echo("<td class=resultcol>");
		else if($subfinal)
			echo("<td class=resultcolsf>");
		else
			echo("<td class=resultcols>");
		if(isset($stres[$subjid][$p]))
		{
			if($noproc)
			{
				echo(strtoupper($stres[$subjid][$p]));				
			}
			else if($showletters || ($p < 3 && $showletters12))
			{
				if($stres[$subjid][$p] > 0.9)
				{
					if($stres[$subjid][$p] < 5.5)
						echo("O");
					else if($stres[$subjid][$p] < 6.0)
						echo("M");
					else if($stres[$subjid][$p] < 7.0)
						echo("V");
					else if($stres[$subjid][$p] < 8.0)
						echo("RV");
					else
						echo("G");
				}
				else
					echo(strtoupper($stres[$subjid][$p]));
			}
			else
			{
				if($stres[$subjid][$p] > 0.9)
					if($stres[$subjid][$p] < 4.0)
						echo("4-");
					else
						echo(number_format($stres[$subjid][$p],1,',','.'));
				else
					echo(strtoupper($stres[$subjid][$p]));
			}			
		}
		else
			echo("&nbsp;");
		
		echo("</td>");
	}
	if(substr($_SESSION['CurrentGroup'],0,1) > 2 && $per4)
	{
		if($subjid == "AVI")
			$p=3;
		else
			$p=0;
		echo("<td class=resultcol>");
		if(isset($stres[$subjid][$p]))
		{
			if($noproc)
			{
				echo(strtoupper($stres[$subjid][$p]));				
			}
			else if($showletters || ($p < 3 && $showletters12))
			{
				if($stres[$subjid][$p] > 0.9)
				{
					if($stres[$subjid][$p] < 5.5)
						echo("O");
					else if($stres[$subjid][$p] < 6.0)
						echo("M");
					else if($stres[$subjid][$p] < 7.0)
						echo("V");
					else if($stres[$subjid][$p] < 8.0)
						echo("RV");
					else
						echo("G");
				}
				else
					echo(strtoupper($stres[$subjid][$p]));
			}
			else
			{
				if($stres[$subjid][$p] > 0.9)
					if($stres[$subjid][$p] < 4.0)
						echo("4-");
					else
						echo(number_format($stres[$subjid][$p],1,',','.'));
				else
					echo(strtoupper($stres[$subjid][$p]));
			}			
		}
		else
			echo("&nbsp;");
		
		echo("</td>");
	}
	echo("</tr>");
}
function show_avi($subjfull,$subjid,$curyear)
{
	global $stres;
	$avi2norm[1][3]= array(0=> 'O','M','V','G','G','G','G','G','G','G','G');
	$avi2norm[2][1]= array(0=> 'O','O','M','V','G','G','G','G','G','G','G');
	$avi2norm[2][2]= array(0=> 'O','O','O','M','V','G','G','G','G','G','G');
	$avi2norm[2][3]= array(0=> 'O','O','O','O','M','V','G','G','G','G','G');
	$avi2norm[3][1]= array(0=> 'O','O','O','O','M','V','G','G','G','G','G');
	$avi2norm[3][2]= array(0=> 'O','O','O','O','O','M','V','G','G','G','G');
	$avi2norm[3][3]= array(0=> 'O','O','O','O','O','M','M','V','G','G','G');
	$avi2norm[4][1]= array(0=> 'O','O','O','O','O','M','M','V','G','G','G');
	$avi2norm[4][2]= array(0=> 'O','O','O','O','O','O','M','M','V','G','G');
	$avi2norm[4][3]= array(0=> 'O','O','O','O','O','O','O','M','V','G','G');
	$avi2norm[5][1]= array(0=> 'O','O','O','O','O','O','O','M','V','G','G');
	$avi2norm[5][2]= array(0=> 'O','O','O','O','O','O','O','M','M','V','G');
	$avi2norm[5][3]= array(0=> 'O','O','O','O','O','O','O','O','M','V','G');
	$avi2norm[6][1]= array(0=> 'O','O','O','O','O','O','O','O','M','V','G');
	$avi2norm[6][2]= array(0=> 'O','O','O','O','O','O','O','O','M','V','G');
	$avi2norm[6][3]= array(0=> 'O','O','O','O','O','O','O','O','M','V','G');
	
	echo("<TR><TD COLSPAN=2 class=mainsubjtxt>". $subjfull. "</td>");			
	for($p=1; $p<=3; $p++)
	{
		echo("<td class=resultcol>");
		if(isset($stres[$subjid][$p]))
		{
			if(strpos($stres[$subjid][$p],'9+') > 0)
				$cb = 10;
			else
				$cb= $stres[$subjid][$p];
			if(isset($avi2norm[$curyear][$p][$cb]))
				echo($avi2norm[$curyear][$p][$cb]);
		}
		else
			echo("&nbsp;");
		
		echo("</td>");
	}
	echo("</tr>");
}
?>
 </body>
</html>