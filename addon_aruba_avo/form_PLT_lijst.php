<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2013 Aim4me N.V.   (http://www.aim4me.info)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();
  require_once("inputlib/inputclasses.php");
  require_once("group.php");
	require_once("subjectselector.php");

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
  // Avoid sorting on students messed up
  unset($_SESSION['ssortertable']);
  inputclassbase::dbconnect($userlink);
  // Setup table for monitoring actions
	$mygroup = new group();
	$mygroup->load_current();

  $curyearqr = SA_loadquery("SELECT year FROM period WHERE id=3");
  $curyear = $curyearqr['year'][1];
	
  
  echo("<html><head><title>PLT lijst</title>");
  echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
  echo("</head><body link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
   
  echo '<p class=txtmidden></p>';
  
	if(isset($_POST['sselectfld']) && $_POST['sselectfld'] != '')
	{
		$subjdataqr = inputclassbase::load_query("SELECT * FROM subject WHERE mid=". $_POST['sselectfld']);
		$subjecttxt = $subjdataqr['fullname'][0];
		$groupyear = substr($_SESSION['CurrentGroup'], 0, 1);
		if($groupyear < 1)
			$groupyear = 4;
		
		// Get the PLT data for this year
		$pltdata = inputclassbase::load_query("SELECT * FROM testdef LEFT JOIN class USING(cid) LEFT JOIN reportcalc ON(testdef.type=testtype) LEFT JOIN testtype USING(type) WHERE class.mid=". $_POST['sselectfld']. " AND gid=". $mygroup->get_id(). " AND year='". $curyear. "' ORDER BY date");
		
		if(isset($pltdata['description']))
		{		
			$line=0;
			$page=1;
			foreach($pltdata['description'] AS $pltix => $desc)
			{
				if($line == 0)
					table_hdr();
				echo("<tr><td style='text-align: center;'>". $pltdata['week'][$pltix]. "<BR>". inputclassbase::mysqldate2nl($pltdata['date'][$pltix]). "</td>");
				echo("<td>". $desc. "</td>");
				echo("<td>". $pltdata['domain'][$pltix]. "</td>");
				echo("<td>". $pltdata['term'][$pltix]. "</td>");
				echo("<td>". $pltdata['duration'][$pltix]. "</td>");
				echo("<td>". $pltdata['tools'][$pltix]. "</td>");
				echo("<td>". $pltdata['translation'][$pltix]. " (". $pltdata['weight'][$pltix]. ")</td>");
				echo("</tr>");
				$line++;
				if($line == 6)
				{
					echo("</table>");
					$line=0;
				}
			}
		}
		echo("</table>");
	}
	else
	{
    // Show for which subject the list should be shown
		$subselbox = new subjectselector();
		$subselbox->show();	
	}
  // Scripts for functions
  echo("</html>");
	
	function table_hdr()
	{
		global $subjecttxt,$groupyear,$curyear,$page;
		echo("<p style='text-align: right;'>Pagina ". $page++. "</p>");
		echo("<H2>Programmering Leerstof & Toetsen mavo – havo – vwo Schooljaar ". $curyear. "</h2>");
		echo("<table border=1 style='page-break-after: always;'><tr><TH rowspan=2>Week /<BR>Datum</th><td colspan=6>Vak: ". $subjecttxt. "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Leerjaar: ". $groupyear. "</td></tr>");
		echo("<TR><TH>Omschrijving</th><TH>Domein</th><TH>Nummer doel<BR>vakleerplan</th><TH>Tijdsduur</th><TH>Hulpmiddelen</th><TH>Toetsvorm<BR>(Weging)</th></TR>");
	}
	echo("<SCRIPT> document.sselect.sselectfld.value='';</SCRIPT>");
?>
