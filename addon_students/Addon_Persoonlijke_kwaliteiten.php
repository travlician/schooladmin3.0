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

  $login_qualify = 'S';
  include ("schooladminfunctions.php");
	
	// Aspect translations
  $aspects = array('Inzet' => 'Inzet','Gedr' => 'Gedrag', 'Regels' => 'Regels', 'HWerk' => 'Huiswerk', 
                   'Conc' => 'Concentratie', 'Cap' => 'Capaciteit', 'Wrkvz' => 'Werkverzorging', 'Tempo' => 'Tempo');

	// Result translations 
	$restrans = array("G"=>"Goed","V"=>"Voldoende","M"=>"Matig","S"=>"Slecht");
	
  // Get the year
  $schoolyear = SA_loadquery("SELECT year FROM period");
  $schoolyear = $schoolyear['year'][1];
	
	// Get student name
	$stdata = SA_loadquery("SELECT * from student WHERE sid=". $_SESSION['uid']);
	$stname = $stdata['firstname'][1]. " ". $stdata['lastname'][1];
	
	// Get subject data for translation of mid to subjectname
	$subjdata = SA_loadquery("SELECT mid,fullname,AVG(show_sequence) FROM sgrouplink LEFT JOIN class USING(gid) LEFT JOIN subject USING(mid) WHERE sid=". $_SESSION['uid']. " GROUP BY mid ORDER BY AVG(show_sequence)");
	foreach($subjdata['mid'] AS $sbix => $mid)
	  $mid2desc[$mid] = $subjdata['fullname'][$sbix];
		
	// Get the PK data
	$pkqr = SA_loadquery("SELECT * FROM avo_pk_data WHERE year='". $schoolyear. "' AND sid=". $_SESSION['uid']);
	if(isset($pkqr))
	{
		foreach($pkqr['mid'] AS $pkix => $mid)
		  $pkdata[$mid][$pkqr['aspect'][$pkix]][$pkqr['period'][$pkix]] = $pkqr['xstatus'][$pkix];
	}
	
  // First part of the page
  echo("<html><head><title>Persoonlijke kwaliteiten</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>Persoonlijke Kwaliteiten van ". $stname. "</font><p>");
  include("studentmenu.php");
	
	if(!isset($pkdata))
		echo("Geen persoonlijke kwaliteiten ingevuld");
	else
	{
		echo("<table style='border: 1px solid black'>");
		// Show the aspects (3 columns per aspect)
		echo("<tr><th style='border: 1px solid black'>Aspect</th>");
		foreach($aspects AS $aspname)
		  echo("<th colspan=3 style='border: 1px solid black'>". $aspname. "</th>");
		echo("</tr>");
		
		// Show the trimester row
		echo("<tr><th style='border: 1px solid black'>Trimester</th>");
		foreach($aspects AS $aspname)
		  echo("<th style='border: 1px solid black'>1</th><th style='border: 1px solid black'>2</th><th style='border: 1px solid black'>3</th>");
		echo("</tr>");
		
    // Show each subject with results (if any)
		foreach($mid2desc AS $mid => $subjname)
		{
			if(isset($pkdata[$mid]))
			{
				echo("<tr><th style='border: 1px solid black'>". $subjname. "</th>");
				foreach($aspects AS $aspkey => $dummy)
				{
					for($p=1; $p <= 3; $p++)
					{
						echo("<th style='border: 1px solid black'; text-align: center;>");
						if(isset($pkdata[$mid][$aspkey][$p]))
							echo($pkdata[$mid][$aspkey][$p]);
						echo("</th>");
					}
				}
				echo("</tr>");
			}
		}
		echo("</table>");
		foreach($restrans AS $rkey => $rvalue)
		  echo($rkey. " = ". $rvalue. "<BR>");
	}


  // close the page
  echo("</html>");

?>
