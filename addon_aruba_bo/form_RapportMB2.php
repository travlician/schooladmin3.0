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
// Get a list of students
$lllist = student::student_list();
if(isset($lllist))
foreach($lllist AS $stud)
{
	if($stud != NULL)
	{
		// get the result for this student & year
		$stresqr = inputclassbase::load_query("SELECT * FROM gradestore LEFT JOIN subject USING(mid) WHERE sid=". $stud->get_id(). " AND year='". $schoolyear. "' AND period < ". (date("n") > 8 ? 2 : (date("n") > 3 ? 4 : 3)));
		//$stresqr = inputclassbase::load_query("SELECT * FROM gradestore LEFT JOIN subject USING(mid) WHERE sid=". $stud->get_id(). " AND year='". $schoolyear. "' AND period < 4");
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
		// Get the absence for this year'
		unset($absqr);
		if($stud->get_student_detail("s_geenabsentieoprapport") == "")
		{
			$absq = "SELECT 
									SUM(IF(acid=1 AND date <= endp1 AND (authorization='Yes' OR authorization='Parent'),1,0)) AS absokp1,
									SUM(IF(acid=1 AND date <= endp1 AND (authorization='No' OR authorization='Pending'),1,0)) AS absnop1,
									SUM(IF(acid=1 AND date > endp1 AND date < startp3 AND (authorization='Yes' OR authorization='Parent'),1,0)) AS absokp2,
									SUM(IF(acid=1 AND date > endp1 AND date < startp3 AND (authorization='No' OR authorization='Pending'),1,0)) AS absnop2,
									SUM(IF(acid=1 AND date >= startp3 AND (authorization='Yes' OR authorization='Parent'),1,0)) AS absokp3,
									SUM(IF(acid=1 AND date >= startp3 AND (authorization='No' OR authorization='Pending'),1,0)) AS absnop3,
									SUM(IF(acid=2 AND date <= endp1 AND (authorization='Yes' OR authorization='Parent'),1,0)) AS lateokp1,
									SUM(IF(acid=2 AND date <= endp1 AND (authorization='No' OR authorization='Pending'),1,0)) AS latenop1,
									SUM(IF(acid=2 AND date > endp1 AND date < startp3 AND (authorization='Yes' OR authorization='Parent'),1,0)) AS lateokp2,
									SUM(IF(acid=2 AND date > endp1 AND date < startp3 AND (authorization='No' OR authorization='Pending'),1,0)) AS latenop2,
									SUM(IF(acid=2 AND date >= startp3 AND (authorization='Yes' OR authorization='Parent'),1,0)) AS lateokp3,
									SUM(IF(acid=2 AND date >= startp3 AND (authorization='No' OR authorization='Pending'),1,0)) AS latenop3
								FROM absence LEFT JOIN absencereasons USING (aid)
								LEFT JOIN (SELECT enddate AS endp1 FROM period WHERE id=1 LIMIT 1) AS t1 ON(1=1)
								LEFT JOIN (SELECT startdate AS startp3 FROM period WHERE id=3 LIMIT 1) AS t2 ON(1=1)
								WHERE sid=". $stud->get_id(). " GROUP BY sid";
			$absqr = inputclassbase::load_query($absq);
		}
		// Get remarks for each period
		unset ($opmrap);
		unset ($opmdate);
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
		if(date("n") > 2 && date("n") < 9)
			echo("<td class=resultcol>". (isset($absqr['absokp3']) ? $absqr['absokp3'][0]. " / ". $absqr['absnop3'][0] : "0 / 0"). "</td></tr>");
		else
			echo("<td class=resultcol>&nbsp;</td></tr>");
		echo("<TR><TD class=mainsubjtxt>Te laat (geoorloofd/ongeoorloofd)</td>
							<td class=resultcol>". (isset($absqr['lateokp1']) ? $absqr['lateokp1'][0]. " / ". $absqr['latenop1'][0] : "0 / 0"). "</td>");
		if(date("n") < 9)
			echo("<td class=resultcol>". (isset($absqr['lateokp2']) ? $absqr['lateokp2'][0]. " / ". $absqr['latenop2'][0] : "0 / 0"). "</td>");
		else
			echo("<td class=resultcol>&nbsp;</td>");
		if(date("n") > 2 && date("n") < 9)
			echo("<td class=resultcol>". (isset($absqr['lateokp3']) ? $absqr['lateokp3'][0]. " / ". $absqr['latenop3'][0] : "0 / 0"). "</td></tr>");
		else
			echo("<td class=resultcol>&nbsp;</td></tr>");
		echo('<tr><td class="Titeltekst"><BR><b>Opmerkingen en aandachtspunten</b></td></tr>');
		for($p=1; $p<=3; $p++)
		{
			echo("<TR><TD class=rapportline>". $p. "e rapport dd ". (isset($opmdate[$p]) ? $opmdate[$p] : "&nbsp;"). "</td></tr>");
			echo("<TR style='height:100px;'><td class=subsubjtxt COLSPAN=4>". (isset($opmrap[$p]) ? $opmrap[$p] : "&nbsp;"). "</td></tr>");
		}
		
		// Space for signing
		echo("<TR><TD COLSPAN=4><BR><BR>");
		echo("<table style='width: 100%; border-spacing: 0px;'<TR><td class=subsubjtxt><b>Handtekening leerkracht</b></td><td class=subsubjtxt><b>Handtekening schoolhoofd</b></td></tr>");
		echo("<TR><TD style='border: 1px solid black; height: 70px;'>&nbsp;</td><TD style='border: 1px solid black; height: 70px;'>&nbsp;</td></tr></table>");
		echo('</table>');
		
		// Page 1 of 4, Front page
	/*	echo('<DIV class="SubKoptekst"><LABEL>Leerling:</LABEL><B>');
		echo($stud->get_name());
		echo('</b></DIV><DIV class="SubKoptekst"><LABEL>Klas:</LABEL><B>');
		echo($_SESSION['CurrentGroup']);
		echo('</b>');
		echo('</div><DIV class="SubKoptekst"><LABEL>Schooljaar:</LABEL><B>');
		echo($schoolyear); */
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
		echo('<table width="46%" style="border-collapse: collapse; float: right;"><tr><td colspan=2 class=headingtext><LABEL>Leerling:</LABEL><B>'. $stud->get_name(). '</b> <BR><LABEL>Klas:</LABEL><B>'. $_SESSION['CurrentGroup']. '</b><BR><LABEL>Schooljaar:</LABEL><B>'. $schoolyear. '</td><td class=trimesterid>1</td><td class=trimesterid>2</td><td class=trimesterid>3</td></tr>'); // V2
		show_result("Godsdienst","go",true,false);
		echo("<tr><td class=tablespace colspan=4></td></tr>");
		// V1echo("<tr><td class=mainsubjtxt colspan=2>Taal</td><td class=resultcol>&nbsp;</td><td class=resultcol>&nbsp;</td><td class=resultcol>&nbsp;</td></tr>");
		show_result("Taal<span style='text-align: right; padding-left: 65%;'>Gemiddelde</span>","ne",true,false);  //V2
		show_result("Mondeling taalgebruik","mo",false,false);
		show_result("Stellen","st",false,false);
		show_result("Taalbeschouwing","tb",false,false);
		show_result("Leesbegrip","lb",false,false);
		show_result("Woordenschat","ws",false,false);
		show_result("Spelling","spe",false,false);
		// V1show_result("<b>Gemiddelde</b>","ne",false,false);
		echo("<tr><td class=tablespace colspan=4></td></tr>");
		echo("<tr><td class=mainsubjtxt colspan=2>Technisch lezen</td><td class=resultcol>&nbsp;</td><td class=resultcol>&nbsp;</td><td class=resultcol>&nbsp;</td></tr>");
		show_result("Avi-niveau","AVI-N",false,false,true);
		show_result("Dit niveau is voor dit leerjaar","avi-B",false,false);
		echo("<tr><td class=tablespace colspan=4></td></tr>");
		
		//V2 from here
		if($curyear >= 5) // Show foreign languages
		{
			show_result("Papiaments","pa",true,false);
			show_result("Engels","en",true,false);
			show_result("Spaans","spa",true,false);
			echo("<tr><td class=tablespace colspan=4></td></tr>");			
		}
		// End V2
		// Since sub-subjects are being phased out, reference point: only show in years 3-6 in 2015-2016, next year only 4-6 etc. we need to find out what to show
		if($refyear - $curyear > 12)
		{ // Need to show only main
			show_result("Rekenen","reW",true,false);			
		}
		else
		{ // Need to show the subs as well
			// V1echo("<tr><td class=mainsubjtxt colspan=2>Rekenen</td><td class=resultcol>&nbsp;</td><td class=resultcol>&nbsp;</td><td class=resultcol>&nbsp;</td></tr>");
			show_result("Rekenen<span style='text-align: right; padding-left: 58%;'>Gemiddelde</span>","re",true,false); //V2
			show_result("Getalbegrip","gb",false,false);
			show_result("Basisvaardigheden","bv",false,false);
			show_result("Meten en meetkunde","mm",false,false);
			show_result("Verhoudingen","vpb",false,false);
			// V1show_result("<b>Gemiddelde</b>","re",false,false);			
		}
		echo("<tr><td class=tablespace colspan=4></td></tr>");
		show_result("Schrijven","sc",true,false);
		echo("<tr><td class=tablespace colspan=4></td></tr>");
		show_result("Wereldoriëntatie","wo". ($curyear < 3 ? "O" : ""),true,$curyear < 3);
		if($curyear == 3)
		{
			echo("<tr><td class=tablespace colspan=4></td></tr>");
			show_result("Zwemniveau","zw". ($curyear < 3 ? "O" : ""),true,false);			
		}
		if($curyear == 5)
		{
			echo("<tr><td class=tablespace colspan=4></td></tr>");
			show_result("Verkeersexamen","vk",true,false);			
		}
		echo("<tr><td class=tablespace colspan=4></td></tr>");
		show_result("Gym","lo",true,false);
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
				<td class="Titeltekst"><b>Werkhouding</b></td>
		</tr>');
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
				echo('&nbsp;&nbsp;<img src="PNG/BolS');
				if(isset($llar[$aspect][$per]))
					echo($llar[$aspect][$per]);
				else
					echo("0");
				echo('.PNG" border="0">');
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
				echo('&nbsp;&nbsp;<img src="PNG/BolS');
				if(isset($llar[$aspect][$per]))
					echo($llar[$aspect][$per]);
				else
					echo("0");
				echo('.PNG" border="0">');
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

function show_result($subjfull,$subjid,$normalsubj,$showletters,$noproc=false)
{
	global $stres;
	if($normalsubj)
		echo("<TR><TD COLSPAN=2 class=mainsubjtxt>". $subjfull. "</td>");
	else
		echo("<TR><td class=transcel>&nbsp;</td><TD class=subsubjtxt>". $subjfull. "</td>");
	for($p=1; $p<=3; $p++)
	{
		if($normalsubj)
			echo("<td class=resultcol>");
		else
			echo("<td class=resultcols>");
		if(isset($stres[$subjid][$p]))
		{
			if($noproc)
			{
				echo($stres[$subjid][$p]);				
			}
			else if($showletters)
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
					echo($stres[$subjid][$p]);
			}
			else
			{
				if($stres[$subjid][$p] > 0.9)
					if($stres[$subjid][$p] < 4.0)
						echo("4-");
					else
						echo(number_format($stres[$subjid][$p],1,',','.'));
				else
					echo($stres[$subjid][$p]);
			}			
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