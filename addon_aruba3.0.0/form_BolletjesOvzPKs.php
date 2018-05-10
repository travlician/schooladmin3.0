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
?>
<html>
 <head>
   <META NAME="Author"	CONTENT="Owner">
   <META NAME="GENERATOR" CONTENT="">
   <META NAME="KEYWORDS" CONTENT="">
   <META NAME="DESCRIPTION" CONTENT="">
   <META NAME="HEADER" CONTENT="">

   <TITLE>FORM: Bolletjes overzicht PK's MPbasis</TITLE>
   <LINK rel="stylesheet" type="text/css" href="style_BolletjesOvzPKs.css">
   </head>
<body summary="Overzicht per klas van PK's in de vorm van een bolletjespresentatie">
<?
// Get a list of students
$lllist = student::student_list();
if(isset($lllist))
foreach($lllist AS $stud)
{
	if($stud != NULL)
	echo('
   <!-- Titel tabel rechts: Sociaal Emotionele Ontwikkeling -->
 <table class="PlekTable">
    <tr>
      <td class="Titeltekst" colspan=4><b>Sociaal Emotionele Ontwikkeling</b></td>
	</tr>');
	// Get the aspect results for this student
	unset($llar);
	$llarqr = inputclassbase::load_query("SELECT * FROM bo_houding_data WHERE year='". $schoolyear. "' AND sid=". $stud->get_id());
	if(isset($llarqr))
		foreach($llarqr['aspect'] AS $llix => $aspect)
			$llar[$aspect][$llarqr['period'][$llix]] = $llarqr['xstatus'][$llix];
	
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
	echo('
  </table>

	<!-- Koptekst voor dit overzicht -->
  <img border="0" src="schoollogo.png" width="100px" alt="Logo" style="float: left">
  <p class="Koptekst"><b>Bolletjes overzicht over</b></p>
  <DIV class="SubKoptekst">Leerling: <B>');
	echo($stud->get_name());
	echo('</b></DIV>
  <br>
  <DIV class="SubKoptekst">Schooljaar: <B>');
	echo($_SESSION['CurrentGroup']);
	echo('</b></DIV>
  <DIV class="SubKoptekst">Leerkracht: <B>');
	echo($teachername);
	echo('</b></DIV>
  <br>
  <!-- Titel tabel: -->
  <table align="left" width="45%">
    <tr>
      <td class="Titeltekst"><b>Werkhouding</b></td>
	</tr>');
	// Now present each social emotional aspects
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
	echo('</table>
  <DIV class=pagebreak>&nbsp;</DIV>');
}
?>
 </body>
</html>