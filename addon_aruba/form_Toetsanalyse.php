<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2016 Aim4me N.V.   (http://www.aim4me.info)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
  
  // Functions
  
  
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
  $subjfilt = "";
  foreach($_POST AS $pkey => $pval)
    if(substr($pkey,0,3) == "scb")
	  $subjfilt .=  "subject.mid=". substr($pkey,3). " OR ";
  if($subjfilt == "")
    $subjfilt = "1=1 OR ";
  $subjects = SA_loadquery("SELECT shortname,mid,fullname FROM subject WHERE type <> 'meta' AND (". substr($subjfilt,0,-4). ") ORDER BY shortname");
    
  // Get a list of students with the subject package and extra subject
  $squery = "SELECT sid,lastname,firstname,gid FROM student LEFT JOIN sgrouplink USING(sid)";
  $squery .= " WHERE sid IS NOT NULL ";
  // Add the filter for the groups
  $fstr = "";
  foreach($_POST AS $pix => $pvl)
  {
    if(substr($pix,0,3) == "gcb")
	  $fstr .= "gid=". substr($pix,3). " OR ";
  }
  if($fstr == "")
    $fstr = "1=1 OR ";
  $squery .= "AND (". substr($fstr,0,-4). ") ";
  $squery .= " GROUP BY sid ORDER BY lastname,firstname";
  $studs = SA_loadquery($squery);
  echo(mysql_error($userlink));
  
	// Get the period details
	$perqr = SA_loadquery("SELECT id,if(startdate<=CURDATE() AND enddate>=CURDATE(),1,0) AS curper FROM period");
	foreach($perqr['id'] AS $pix => $pid)
	  $per[$pid] = $perqr['curper'][$pix];
	
	// Get the groups
  $groups = SA_loadquery("SELECT groupname,gid FROM sgroup WHERE active=1 ORDER BY groupname");

  // First part of the page
if(isset($_POST['Print']))
{ 
  echo("<html><head><title>Toetsanalyse</title></head><body link=blue vlink=blue bgcolor=#E0E0FF>");
  echo '<LINK rel="stylesheet" type="text/css" href="style_Toetsanalyse.css" title="style1">';
	// Get the test definitions, but first make a period filter
	$pfiltstr = "";
  foreach($_POST AS $pix => $pvl)
  {
    if(substr($pix,0,3) == "pcb")
	  $pfiltstr .= "period=". substr($pix,3). " OR ";
  }
  if($pfiltstr == "")
    $pfiltstr = "1=1 OR ";
	
	$testdefq = "SELECT tdid,short_desc,date,shortname FROM testdef LEFT JOIN class USING(cid) LEFT JOIN subject USING(mid) WHERE year='". $schoolyear. "' AND (". substr($subjfilt,0,-4). ") AND (". substr($pfiltstr,0,-4). ") AND (". substr($fstr,0,-4). ") ORDER BY date";
	$testdefqr = SA_loadquery($testdefq);
	if(!isset($testdefqr['tdid']))
	{
		echo("Er zijn geen toetsresulaten voor de opgegeven selectie<BR>");
		exit;
	}
	// Now first create the filter with the test definitions
	$tdfilt = "";
	foreach($testdefqr['tdid'] AS $tdid)
		$tdfilt .= "tdid=". $tdid. " OR ";
	// get the test result
	$testrqr = SA_loadquery("SELECT sid,tdid,result FROM testresult WHERE (". substr($tdfilt,0,-4). ")");
	if(isset($testrqr['tdid']))
	{
		foreach($testrqr['tdid'] AS $tdix => $tdid)
			$res[$tdid][$testrqr['sid'][$tdix]] = $testrqr['result'][$tdix];
	}
	echo("<TABLE><TR><TH>Toets</th>");
	foreach($testdefqr['tdid'] AS $tdix => $tdid)
	{
		echo("<TH>". $testdefqr['shortname'][$tdix]. "<BR>". substr($testdefqr['date'][$tdix],8,2). substr($testdefqr['date'][$tdix],4,3). "<BR>". $testdefqr['short_desc'][$tdix]. "</th>");
	}
	echo("</tr>");
	foreach($studs['sid'] AS $stix => $sid)
	{
		echo("<TR><TD>");
		echo($studs['lastname'][$stix]. ", ". $studs['firstname'][$stix]. "</td>");
		foreach($testdefqr['tdid'] AS $tdix => $tdid)
		{
			if(isset($res[$tdid][$sid]))
			{
				$rs = $res[$tdid][$sid];
				if($rs > 0 && $rs < 5.5)
					echo("<td class=resfl>");
				else if($rs == 5.5)
					echo("<td class=resrg>");
				else
					echo("<td class=resok>");
				if($rs > 0)
					$rs=number_format($rs,1,",",".");
				echo($rs . "</td>");
			}
			else
				echo("<td>&nbsp;</td>");
		}
	}
	// Show percentage uitval
	echo("<TR><TD>% Uitval</td>");
	$studuitval=",";
	foreach($testdefqr['tdid'] AS $tdix => $tdid)
	{
		$uitvcnt = 0;
		$totcnt = 0;
		foreach($studs['sid'] AS $stix => $sid)
		{
			if(isset($res[$tdid][$sid]) && $res[$tdid][$sid] > 0)
			{
				$totcnt++;
				if($res[$tdid][$sid] < 5.5)
				{
					$uitvcnt++;
					$studuitval .= $studs['firstname'][$stix]. " ". $studs['lastname'][$stix]. ",";
				}
			}
		}
		if($totcnt > 0)
			echo("<td class=resok>". number_format(100.0*$uitvcnt / $totcnt,1,",","."). "%</td>");
		else
			echo("<td>&nbsp;</td>");
	}
	//echo("<td>". substr($studuitval,1,-1). "</td>");
	echo("</tr>");
	
	// Show percentage randgeval
	echo("<TR><TD>% Randgeval</td>");
	$studrand=",";
	foreach($testdefqr['tdid'] AS $tdix => $tdid)
	{
		$uitvcnt = 0;
		$totcnt = 0;
		foreach($studs['sid'] AS $stix => $sid)
		{
			if(isset($res[$tdid][$sid]) && $res[$tdid][$sid] > 0)
			{
				$totcnt++;
				if($res[$tdid][$sid] == 5.5)
				{
					$uitvcnt++;
					$studrand .= $studs['firstname'][$stix]. " ". $studs['lastname'][$stix]. ",";
				}
			}
		}
		if($totcnt > 0)
			echo("<td class=resok>". number_format(100.0*$uitvcnt / $totcnt,1,",","."). "%</td>");
		else
			echo("<td>&nbsp;</td>");
	}
	//echo("<td>". substr($studrand,1,-1). "</td>");
	echo("</tr>");
	
	// Show percentage voldoende
	echo("<TR><TD>% Voldoende</td>");
	foreach($testdefqr['tdid'] AS $tdix => $tdid)
	{
		$uitvcnt = 0;
		$totcnt = 0;
		foreach($studs['sid'] AS $stix => $sid)
		{
			if(isset($res[$tdid][$sid]) && $res[$tdid][$sid] > 0)
			{
				$totcnt++;
				if($res[$tdid][$sid] > 5.5)
					$uitvcnt++;
			}
		}
		if($totcnt > 0)
			echo("<td class=resok>". number_format(100.0*$uitvcnt / $totcnt,1,",","."). "%</td>");
		else
			echo("<td>&nbsp;</td>");
	}
	
	// Show average
	echo("<TR><TD>Gemiddelde</td>");
	foreach($testdefqr['tdid'] AS $tdix => $tdid)
	{
		if(isset($res[$tdid]) && count($res[$tdid]) > 0)
			echo("<td class=resok>". number_format(array_sum($res[$tdid]) / count($res[$tdid]),2,",","."). "</td>");
		else
			echo("<td>&nbsp;</td>");
	}
	
	// Show standard deviation
	if (!function_exists('stats_standard_deviation')) {
			/**
			 * This user-land implementation follows the implementation quite strictly;
			 * it does not attempt to improve the code or algorithm in any way. It will
			 * raise a warning if you have fewer than 2 values in your array, just like
			 * the extension does (although as an E_USER_WARNING, not E_WARNING).
			 *
			 * @param array $a
			 * @param bool $sample [optional] Defaults to false
			 * @return float|bool The standard deviation or false on error.
			 */
			function stats_standard_deviation(array $a, $sample = false) {
					$n = count($a);
					if ($n === 0) {
							trigger_error("The array has zero elements", E_USER_WARNING);
							return false;
					}
					if ($sample && $n === 1) {
							trigger_error("The array has only 1 element", E_USER_WARNING);
							return false;
					}
					$mean = array_sum($a) / $n;
					$carry = 0.0;
					foreach ($a as $val) {
							$d = ((double) $val) - $mean;
							$carry += $d * $d;
					};
					if ($sample) {
						 --$n;
					}
					return sqrt($carry / $n);
			}
	}
	echo("<TR><TD>Standaard deviatie</td>");
	foreach($testdefqr['tdid'] AS $tdix => $tdid)
	{
		if(isset($res[$tdid]) && count($res[$tdid]) > 1)
			echo("<td class=resok>". number_format(stats_standard_deviation($res[$tdid]),2,",","."). "</td>");
		else
			echo("<td>&nbsp;?</td>");
	}
	// Show spread
	echo("<TR><TD>Spreiding</td>");
	foreach($testdefqr['tdid'] AS $tdix => $tdid)
	{
		$min = 10.0;
		$max = 0.0;
		foreach($studs['sid'] AS $stix => $sid)
		{
			if(isset($res[$tdid][$sid]) && $res[$tdid][$sid] > 0)
			{
				if($res[$tdid][$sid] > $max)
					$max=$res[$tdid][$sid];
				if($res[$tdid][$sid] < $min)
					$min=$res[$tdid][$sid];
			}
		}
		if($max > $min)
			echo("<td class=resok>". number_format($max-$min,1,",","."). "</td>");
		else
			echo("<td>&nbsp;</td>");
	}
	
	
	
	echo("</table>");
}
else
{ // Show selection options
  echo("<html><head><title>Toetsanlyse</title></head><body link=blue vlink=blue bgcolor=#E0E0FF>");
  echo '<LINK rel="stylesheet" type="text/css" href="style_Toetsanylse.css" title="style1">';

  $teacher = SA_loadquery("SELECT tid,firstname,lastname FROM teacher WHERE is_gone <> 'Y'");
  SA_closeDB();
  
  // Testing posted values
  foreach($_POST AS $pix => $pvl)
  {
    echo("POST[". $pix. "]=". $pvl. "<BR>");
  }
	echo("<H1>Toetsanalyse</h1>");
  echo("<FORM METHOD=POST ACTION='form_Toetsanalyse.php'>");
  // Put the subject checkboxes
  echo("Vakken:<BR>");
  foreach($subjects['mid'] AS $sbix => $sbid)
    echo("<SPAN style='width: 200px; display: inline-block'><INPUT TYPE=checkbox NAME=scb". $sbid. "> ". $subjects['shortname'][$sbix]. "</SPAN>");
  // Put the group checkboxes
  echo("<BR>Groepen:<BR>");
  foreach($groups['gid'] AS $gix => $gid)
  {
    echo("<SPAN style='width: 200px; display: inline-block'><INPUT TYPE=checkbox NAME=gcb". $gid);
	//if(substr($groups['groupname'][$gix],0,1) == "4")
	//  echo(" CHECKED");
	echo("> ". $groups['groupname'][$gix]. "</SPAN>");
  }
  echo("<BR>Trimesters:<BR>");
  foreach($per AS $pid => $actp)
  {
    echo("<SPAN style='width: 200px; display: inline-block'><INPUT TYPE=checkbox NAME=pcb". $pid);
		if($actp == 1)
			echo(" CHECKED");
		echo("> ". $pid. "</SPAN>");
  }
  echo("<BR>");
  echo("<INPUT TYPE=SUBMIT NAME='Print' VALUE='Afdrukken'>");
  echo("</FORM>");
}
  // close the page
  echo("</html>");
?>
