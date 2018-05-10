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

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");

	$valuetext = array(0=>"-","Zeer&nbsp;slecht","Slecht","Zeer&nbsp;onvoldoende","Onvoldoende","Voldoende","Voldoende","Ruim&nbsp;voldoende","Goed","Zeer&nbsp;goed","Uitmuntend");
  
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
    global $schoolyear,$schoolname;
    echo("<div class=do>DIRECTIE ONDERWIJS ARUBA</div>");
    echo("<div class=ur>Resultaten gemeenschappelijk CKV en Lichamelijke oefenening</div>");
    //echo("<p>Genummerde alfabetische naamlijst van de kandidaten (art. 23 Landsbesluit eindexamens dagscholen, AB 1991 No. GT 35).");
    //echo("<BR>Inzenden voor 1 oktober (1 exemplaar).</p>");
    echo("<p>EINDEXAMEN <b>HAVO</b>, in het schooljaar ". $schoolyear. "</p>");
    echo("<p>School: ". $schoolname. "</p>");
    echo("<table class=studlist><TR><TH ROWSPAN=2 class=exnrhead>Ex.<BR>nr.</TH><TH class=studhead ROWSPAN=2>Achternaam en voornamen van de kandidaat<BR>(in alfabetische volgorde)</TH>");
    echo("<th class=IS>CKV<BR>Gemeemschappelijk</TH><TH class=PFW>Lichamelijke<BR>opvoeding</TH></TR>");
		echo("<TR><TH class=IS>Resultaat</th><TH class=PFW>Resultaat</th></TR>");
  }

  function print_foot()
  {
    // Show the total of students per subject
    echo("</TABLE>");
    // Footing
		echo("<BR><BR>");
    echo("<div class=sign>..........................., .......................");
    echo("<BR>Plaats&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp Datum");
    echo("<BR><BR><BR><BR>. . . . . . . . . . . . . . . . . . . . .");
    echo("<BR>(Handtekening directeur)</div>");
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
	// Create a view with teacher names
	mysql_query("CREATE OR REPLACE VIEW teachersel AS SELECT 0 AS id, '' AS tekst UNION SELECT tid,CONCAT(firstname,' ',lastname) FROM teacher",$userlink);
  
  // Get a list of students
  $squery = "SELECT lastname,firstname,sid,s_exnr.data AS exnr FROM student";
  $squery .= " LEFT JOIN s_exnr USING(sid) LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) WHERE active=1 AND s_exnr.data IS NOT NULL AND s_exnr.data > '0'";
  $squery .= " AND groupname='ExamHavo' ORDER BY s_exnr.data";
  $studs = SA_loadquery($squery);
  echo(mysql_error($userlink));
	
  // Get the results for this year
  $cres = SA_loadquery("SELECT sid,mid,xstatus FROM ahxdata WHERE xstatus<>'' AND year='". $schoolyear. "'");
  if(isset($cres))
    foreach($cres['sid'] AS $cix => $csid)
		{
			$ahxdata[$csid][$cres['mid'][$cix]] = $cres['xstatus'][$cix];
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
	$ppyear = $prevyrqr['year'][3];

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
	$loavgqr = SA_loadquery("SELECT sid,AVG(result) AS loavg,data FROM gradestore LEFT JOIN s_exnr USING(sid) LEFT JOIN subject USING(mid) WHERE (year='". $schoolyear. "' OR year='". $prevyear. "' OR (year='". $ppyear. "' AND data LIKE 'HB%')) AND period<>0 AND shortname='lo' GROUP BY sid");
	if(isset($loavgqr['loavg']))
		foreach($loavgqr['sid'] AS $six => $asid)
			if(isset($loval[$asid]) || substr($loavgqr['data'][$six],0,2) == "HB")
				$lores[$asid] = round($loavgqr['loavg'][$six]);
	

  // First part of the page
  echo("<html><head><title>Formulier LO en CKV gemeenschappelijk resultaten</title></head><body link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style_EX_IS_PWS.css" title="style1">';
  
  print_head();

  $linecount = 0;
  
  // Student listing
  if(isset($studs['sid']))
    foreach($studs['sid'] AS $six => $sid)
    {
      if($linecount > 39)
			{
					print_foot();
					$linecount = 0;
					print_head();
			}
      echo("<TR><TD class=exnr>". $studs['exnr'][$six]. "</TD>");
			echo("<TD class=studname><b>". $studs['lastname'][$six]. "</b> ". $studs['firstname'][$six]. "</TD>");
			if(isset($ckvres[$sid]))
			{
				echo("<TD class=subjind>". $valuetext[$ckvres[$sid]]. "</td>");
			}
			else
				echo("<TD class=subjind>-</TD>");
			if(isset($lores[$sid]))
			{
				echo("<TD class=subjind>". $valuetext[$lores[$sid]]. "</td>");
			}
			else
				echo("<TD class=subjind>-</TD>");
			echo("</TR>");
			$linecount++;	
	  }
 
  print_foot();
  // close the page
  echo("</html>");
?>
