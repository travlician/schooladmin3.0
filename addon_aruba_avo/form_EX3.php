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
  
  // Functions
  function get_initials($name)
  {
    $explstring = explode(" ",$name);
    $retstr = "";
    foreach($explstring AS $addstr)
      $retstr .= " ". substr($addstr,0,1);
    return $retstr;
  }
  
  function print_head($subjecttext, $scount)
  {
    global $schoolyear,$schoolname,$subjects,$subjects,$subtotal,$groups,$corr;
    echo("<div class=do>DIRECTIE ONDERWIJS ARUBA</div>");
    echo("<div class=ur>EX. 3 - M</div>");
    echo("<p>PROCES-VERBAAL VAN TOEZICHT (art. 26.2 van Landsbesluit eindexamens dagscholen VWO, HAVO, MAVO, AB 1991 No. GT 35).</p>");
	echo("<SPAN class=fillspan1>&nbsp;</span>Klas(sen)/Cluster(s) ");
	foreach($groups['gid'] AS $gix => $gid)
	  if(isset($_POST["gcb". $gid]))
	    echo($groups['groupname'][$gix]. " ");
	echo("<BR>");
	if($_POST['tdate'] != "")
	{
	  echo("<SPAN class=fillspan1>&nbsp;</span>". $_POST['tdate']. "<BR>");
	}
    echo("EXAMEN MAVO, schooljaar <b>". $schoolyear. "</b><BR>");
    echo("<SPAN class=fillspan1>School: <b>". $schoolname. "</b></SPAN><SPAN class=fillspan2>&nbsp;</SPAN><b>". strtoupper($subjecttext). "</b><SPAN class=studcount>". $scount. "</span><BR>");
    echo("<SPAN class=fillspan1>&nbsp;</span><SPAN class=fillspan2>Docent: </span> ". $corr);
    echo("<table class=studlist><TR><TH class=exnrhead>Ex. Nr.</TH><TH class=studhead2>Achternaam</TH><TH class=studhead2>Alle voornamen voluit</TH>");
	echo("<TH class=tabhead>Handtekening examenkandidaat</TH>");
    echo("</TR>");
  }
  
  function print_foot($corr)
  {	
	echo("</TABLE>");
   // Footing
	echo("<p>Ex nr. en naam dienen in overeenstemming te zijn met formulier EX. 1.</p>");
    echo("<div class=zoz>Z.O.Z.&nbsp;</div>");
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
  $subjects = SA_loadquery("SELECT shortname,subjectpackage.mid,fullname FROM subjectpackage LEFT JOIN subject USING(mid) UNION SELECT shortname,extrasubject,fullname FROM s_package LEFT JOIN subject ON(mid=extrasubject) WHERE shortname IS NOT NULL  UNION SELECT shortname,extrasubject2,fullname FROM s_package LEFT JOIN subject ON(mid=extrasubject2) WHERE shortname IS NOT NULL  UNION SELECT shortname,extrasubject3,fullname FROM s_package LEFT JOIN subject ON(mid=extrasubject3) WHERE shortname IS NOT NULL GROUP BY mid ORDER BY mid");
  
  // Get the data of the exam subject collections
  $packages = SA_loadquery("SELECT * FROM subjectpackage");
  $groups = SA_loadquery("SELECT groupname,gid FROM sgroup WHERE active=1 ORDER BY groupname");
if(isset($_POST['Print']))
{ 
  // Get a list of students with the subject package and extra subject and in the selected group(s)
  $squery = "SELECT sid,lastname,firstname,gid,s_exnr.data AS exnr,packagename,extrasubject,extrasubject2,extrasubject3 FROM student LEFT JOIN sgrouplink USING(sid)";
  $squery .= " LEFT JOIN s_exnr USING(sid) LEFT JOIN s_package USING(sid) WHERE s_exnr.data IS NOT NULL AND s_exnr.data > '0' ";
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
  // Add filter for the exam numbers
  $squery .= "AND s_exnr.data >= ". $_POST['exafrom']. " AND s_exnr.data <= ". $_POST['exato']. " ";
  $squery .= "GROUP BY sid ORDER BY s_exnr.data";
  $studs = SA_loadquery($squery);
  echo(mysql_error($userlink));
  
  // Get a list of students with the subject package and extra subject
  $squery = "SELECT sid,lastname,firstname,gid,s_exnr.data AS exnr,packagename,extrasubject,extrasubject2,extrasubject3 FROM student LEFT JOIN sgrouplink USING(sid)";
  $squery .= " LEFT JOIN s_exnr USING(sid) LEFT JOIN s_package USING(sid) WHERE s_exnr.data IS NOT NULL AND s_exnr.data > '0' ";
  // Add filter for the exam numbers
  // removed 14 oct 2012 on request Alvin Tromp
  // $squery .= "AND s_exnr.data >= ". $_POST['exafrom']. " AND s_exnr.data <= ". $_POST['exato']. " ";
  $squery .= "GROUP BY sid ORDER BY s_exnr.data";
  $studssubj = SA_loadquery($squery);
  echo(mysql_error($userlink));
  
  // Get the list of presence and exemptions 
  $pedata = SA_loadquery("SELECT sid,mid,xstatus FROM ex45data WHERE year='". $schoolyear. "'");
  if(isset($pedata['sid']))
    foreach($pedata['sid'] AS $peix => $sid)
	  $pexdata[$sid][$pedata['mid'][$peix]] = $pedata['xstatus'][$peix];
  // Get the corrector name
  if($_POST['corr'] != 0)
    $corname = SA_loadquery("SELECT firstname,lastname,data FROM teacher LEFT JOIN ". $teachercode. " USING(tid) WHERE teacher.tid=". $_POST['corr']);
  if(isset($corname['firstname'][1]))
    $corr = $corname['firstname'][1]. " ". $corname['lastname'][1]. " (". $corname['data'][1]. ")";
  else
    $corr = "";
  

  // First part of the page
  echo("<html><head><title>EX-3</title></head><body link=blue vlink=blue bgcolor=#FFFFE0>");
  echo '<LINK rel="stylesheet" type="text/css" href="style_sop.css" title="style1">';

  // Remove subjects that were not selected
  foreach($subjects['mid'] AS $sbix => $sbid)
    if(!isset($_POST["scb". $sbid]))
      unset($subjects['shortname'][$sbix]);

foreach($subjects['shortname'] AS $subjix => $sn)
{
  $mid = $subjects['mid'][$subjix];
  // First find out how many students we got here total for the subject
  $studcount = 0;
  foreach($studssubj['gid'] AS $six => $gid)
  {
    $hassubject = 0;
	// check for subjects here!
	foreach($packages['packagename'] AS $subix => $pname)
	{
	  if($pname == $studssubj['packagename'][$six] && $mid == $packages['mid'][$subix])
	  $hassubject = 1;
	}
	if($mid == $studssubj['extrasubject'][$six] || $mid == $studssubj['extrasubject2'][$six] || $mid == $studssubj['extrasubject3'][$six])
	  $hassubject = 1;
	// Now filter out those that are not to be shown
	if((isset($_POST['pcb1']) || isset($_POST['pcb2']) || isset($_POST['pcb3']) || isset($_POST['pcb4']) )&& 
       (!isset($pexdata[$studssubj['sid'][$six]][$mid]) || $pexdata[$studssubj['sid'][$six]][$mid] == 0))
	  $hassubject = 0;
	if(isset($pexdata[$studssubj['sid'][$six]][$mid]) && $pexdata[$studssubj['sid'][$six]][$mid] != 0 && !isset($_POST["pcb". $pexdata[$studssubj['sid'][$six]][$mid]]))
	  $hassubject = 0;
    if($hassubject != 0)
	  $studcount++;
  } // End loop for each student for counting
  
  print_head($subjects['fullname'][$subjix], $studcount);

  $linecount = 0;
  
  // Student listing
  $liststudcnt = 0;
  foreach($studs['gid'] AS $six => $gid)
  {
    if($linecount > 25)
	{
      print_foot($corr, $studcount);
	  $linecount = 0;
	  print_head($subjects['fullname'][$subjix], $studcount);
	}
    $hassubject = 0;
	// check for subjects here!
	foreach($packages['packagename'] AS $subix => $pname)
	{
	  if($pname == $studs['packagename'][$six] && $mid == $packages['mid'][$subix])
	  $hassubject = 1;
	}
	if($mid == $studs['extrasubject'][$six] || $mid == $studs['extrasubject2'][$six] || $mid == $studs['extrasubject3'][$six])
	  $hassubject = 1;
	// Now filter out those that are not to be shown
	if((isset($_POST['pcb1']) ||  isset($_POST['pcb2']) ||  isset($_POST['pcb3']) ||  isset($_POST['pcb4'])) &&
       (!isset($pexdata[$studs['sid'][$six]][$mid]) || $pexdata[$studs['sid'][$six]][$mid] == 0))
	  $hassubject = 0;
	if(isset($pexdata[$studs['sid'][$six]][$mid]) && $pexdata[$studs['sid'][$six]][$mid] != 0 && !isset($_POST["pcb". $pexdata[$studs['sid'][$six]][$mid]]))
	  $hassubject = 0;
    if($hassubject != 0)
	{
	  $liststudcnt++;
      echo("<TR><TD class=exnr>". $studs['exnr'][$six]. "</TD>");
      echo("<TD class=studname>". $studs['lastname'][$six]. "</TD><TD class=studname>". $studs['firstname'][$six]. "</TD>");
	  // Empty column for signature
	  echo("<TD class=signcol>&nbsp;</TD>");
	  
      echo("</TR>");
	  $linecount++;	
	} // End if student has package
  } // End loop for each student
  
  // Show student counts
  echo("<TR><TD>&nbsp;</TD></TR><TR><TD colspan=3  class=listcount>". $liststudcnt. " student(en) op deze lijst</TD></TR>");
  echo("<TR><TD colspan=3 class=subjectcount>". $studcount. " TOTAAL voor ". strtoupper($subjects['fullname'][$subjix]). "</TD></TR>");
  print_foot($corr);
} // End loop for each subject
  // close the page
  echo("</html>");
} // End if print posted
else
{ // No print posted, must show filter and sort fields
  $presenceoptions = array(1 => "Her","Afw. Mo","Afw. SE","Afw. Ex","Vrijst. 7","Vrijst. 8","Vrijst. 9","Vrijst. 10");
  $teacher = SA_loadquery("SELECT tid,firstname,lastname FROM teacher WHERE is_gone <> 'Y'");
  SA_closeDB();

  // First part of the page
  echo("<html><head><title>Formulier EX. 3-M</title></head><body link=blue vlink=blue bgcolor=#FFFFE0>");
  echo '<LINK rel="stylesheet" type="text/css" href="style_sop.css" title="style1">';
  
  // Testing posted values
  foreach($_POST AS $pix => $pvl)
  {
    echo("POST[". $pix. "]=". $pvl. "<BR>");
  }
  echo("<FORM METHOD=POST ACTION='form_EX3.php'>");
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
  echo("<BR>Afwezigheid en vrijstelling:<BR>");
  foreach($presenceoptions AS $pix => $ptxt)
  {
    echo("<SPAN style='width: 200px; display: inline-block'><INPUT TYPE=checkbox NAME=pcb". $pix);
	echo("> ". $ptxt. "</SPAN>");
  }
  echo("<BR>");
  echo("Examennummers van <INPUT TYPE=TEXT SIZE=10 NAME=exafrom VALUE=1> t/m <INPUT TYPE=TEXT SIZE=10 NAME=exato VALUE=99999><BR>");
  echo("Corrector: <SELECT NAME=corr ID=corr><OPTION VALUE=0> </OPTION>");
  foreach($teacher['tid'] AS $tix => $tid)
   echo("<OPTION VALUE=". $tid. ">". $teacher['firstname'][$tix]. " ". $teacher['lastname'][$tix]. "</OPTION>");
  echo("</SELECT><BR>");
  echo("Datum: <INPUT TYPE=TEXT NAME=tdate ID=tdate SIZE=10><BR>");

  echo("<INPUT TYPE=SUBMIT NAME='Print' VALUE='Afdrukken'>");
  echo("</FORM>");
  echo("</html>");
  
}
?>
