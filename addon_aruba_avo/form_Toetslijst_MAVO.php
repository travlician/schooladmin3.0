<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014 Aim4me N.V.   (http://www.aim4me.com)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
      
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

  $subjqr = "SELECT shortname,mid FROM class LEFT JOIN subject USING(mid) LEFT JOIN sgroup USING(gid) WHERE active=1 AND
             (groupname LIKE '1_' OR groupname LIKE '2_' OR groupname LIKE '3_') AND
			 type <> 'meta' AND show_sequence IS NOT NULL
			 GROUP BY mid ORDER BY AVG(show_sequence)";
  $subjlist = SA_loadquery($subjqr);
  
  // Decide which period we are in... aug-dec => 1, jan-apr => 2, others => 3
  $month = date('n');
  if($month >= 8)
   $pid = 1;
  else if($month < 5)
   $pid = 2;
  else
   $pid = 3;

  if(isset($subjlist))
  {
    // First part of the page
    echo("<html><head><title>Toetslijst</title></head><body link=blue vlink=blue>");
    echo '<LINK rel="stylesheet" type="text/css" href="style_Toetslijst.css" title="style1">';
	echo("<H1>Toetslijst voor trimester ". $pid. "</H1>");
	echo("<H2>dc: Docent code, #T: Aantal toetsen in het PLT, -R: aantal ontbrekende resultaten = aantal leerlingen x aantal toetsen - aantal ingevoerde resultaten</H2>");
	echo("<TABLE class=abstable><TR><TH>&nbsp;</TH>");
	foreach($subjlist['shortname'] AS $sjname)
	  echo("<TH colspan=3 class=leftborderthick>". $sjname. "</TH>");
	echo("</TR>");
	// second heading row
	echo("<TR><TH>Klas</TH>");
	foreach($subjlist['shortname'] AS $sjname)
	  echo("<TH class=leftborderthick>dc</TH><TH>#T</TH><TH>-R</TH>");
	echo("</TR>");
	
	// Now show a row for group
	$grps = SA_loadquery("SELECT groupname,gid FROM sgroup WHERE active=1 AND (groupname LIKE '1_' OR groupname LIKE '2_' OR groupname LIKE '3_') ORDER BY groupname");
	foreach($grps['groupname'] AS $gix => $gname)
	{
	  // See how many students are in this group
	  $scount = SA_loadquery("SELECT COUNT(sid) AS scount FROM sgrouplink WHERE gid=". $grps['gid'][$gix]);
	  $scount = $scount['scount'][1];
	  echo("<TR><TD>". $gname. "</TD>");
	  foreach($subjlist['mid'] AS $mid)
	  {  // Show the 3 items for each subject/group combination
	    // Get the corresponding class and teachercode
		$ciddc = SA_loadquery("SELECT cid,data,minpasspointbalance FROM class LEFT JOIN `". $teachercode. "` USING(tid) LEFT JOIN coursepasscriteria USING(masterlink) WHERE mid=". $mid. " AND gid=". $grps['gid'][$gix]);
		if(isset($ciddc['cid']))
		{ // We got teachercode, show it and also number of tests and missing results
		  echo("<TD class=leftborderthick>". $ciddc['data'][1]. "</TD>");
		  $vtest = 0;
		  $rcount = 0;
          $tlist = SA_loadquery("SELECT tdid FROM testdef LEFT JOIN reportcalc ON(reportcalc.testtype = testdef.type) WHERE cid=". $ciddc['cid'][1]. " AND period=". $pid. " AND year='". $schoolyear. "' AND weight > 0");
		  if(isset($tlist['tdid']))
		    foreach($tlist['tdid'] AS $tdid)
			{
			  $rescnt = SA_loadquery("SELECT COUNT(sid) AS rescnt FROM testresult WHERE tdid=". $tdid. " AND result IS NOT NULL");
			  $rcount += $rescnt['rescnt'][1];
			  $vtest++;
			}
			  
		  echo("<TD". ($vtest < $ciddc['minpasspointbalance'][1] ? " class=redbg" : ""). ">". $vtest. "</TD><TD>". (($vtest * $scount) - $rcount). "</TD>");
		}
		else
		{  // Subject not applicable to this group, show empty
		  echo("<TD colspan=3 class=leftborderthick>&nbsp;</TD>");
		}
	  }
	  
	  echo("</TR>");
	}

	echo("</TABLE>");
  }

  // close the page
  echo("</html>");
?>
