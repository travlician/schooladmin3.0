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
  
  // Subject translation tables
  $offsubjects = array(1 => "Ne","En","I&S","Sp","Wi-A","Wi-B","Na","Sk","Bio","Ec","M&O","Ak","Gs","Pfw","CKV");
  $altsubjects = array("Ne"=>1,"En"=>2,"I&S"=>3,"Sp"=>4,"Wi-A"=>5,"Wi-B"=>6,"Na"=>7,"Sk"=>8,"Bio"=>9,"Ec"=>10,"M&O"=>11,"Ak"=>12,"Gs"=>13,"Pfw"=>14,"CKV"=>15);
  
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
    global $schoolyear,$schoolname,$subjects,$subjects,$subtotal;
    echo("<div class=do>DIRECTIE ONDERWIJS ARUBA</div>");
    echo("<div class=ur>EX. 2 - V</div>");
    echo("<p>Verzamellijst van de cijfers van schoolonderzoek (art. 21.2 Landsbesluit eindexamens dagscholen, AB 1991 No. GT 35).");
    echo("<BR>Inzenden tenminste 3 dagen voor aanvang schriftelijke examens (1 exemplaar).</p>");
    echo("<p>EINDEXAMEN <b>VWO</b>, in het schooljaar ". $schoolyear. "</p>");
    echo("<p>School: ". $schoolname. "</p>");
    echo("<table class=studlist><TR><TH class=exnrhead ROWSPAN=2>Ex.<BR>nr.</TH><TH class=studhead COLSPAN=2>Naam van de kandidaat (in alfabetische volgorde)</TH><TH ROWSPAN=2 class=subjhead>#<BR>se</TH>");
    foreach($subjects['shortname'] AS $sn)
    {
      echo("<TH class=subjhead ROWSPAN=2>". substr($sn,0,strlen($sn) / 2). "<BR>". substr($sn,strlen($sn) / 2). "</TH>");
    }
    echo("</TR>");
	echo("<TR><TH class=studheadln>Achternaam</TH><TH class=studheadfn>Alle voornamen voluit:</TH></TR>");
  
    // Reset subject total counts
    foreach($subjects['mid'] AS $mid)
      $subtotal[$mid] = 0;
  }
  
  function print_foot()
  {
    global $subjects,$subtotal;
    // Show the total of students per subject
    echo("<TR><TD class=nolines>&nbsp</TD><TD class=total COLSPAN=3>TOTAAL</TD>");
    foreach($subjects['mid'] AS $mid)
      echo("<TD class=subjind>". $subtotal[$mid]. "</TD>");
    echo("</TR>");
    // Show the subjects again
    echo("<TR><TD class=nolines COLSPAN=3>&nbsp</TD><TD class=nolines>&nbsp</TD>");
    foreach($subjects['shortname'] AS $sn)
    {
      echo("<TH class=subjhead>". substr($sn,0,strlen($sn) / 2). "<BR>". substr($sn,strlen($sn) / 2). "</TH>");
    }
    echo("</TR></TABLE>");
    // Footing
    echo("<p class=footing>Ex.nr. en naam dienen in overeenstemming te zijn");
    echo("<BR>met formulier EX. 1-V.");
    echo("</p>");
    echo("<div class=sign>..........................., .......................");
    echo("<BR>Plaats&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp Datum");
    echo("<BR><BR><BR><BR> . . . . . . . . . . . . . . . . . . . . .&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp . . . . . . . . . . . . . . . . . . . . .");
    echo("<BR> (Handtekening directeur)&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp (Handtekening secretaris)</div>");
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
  foreach($offsubjects AS $osix => $sjname)
  {
    foreach($subjectsqr['shortname'] AS $sbix => $subsn)
	{
	  if(isset($altsubjects[$subsn]) && $altsubjects[$subsn] == $osix)
	  {
	    $subjects['shortname'][$osix] = $sjname;
		$subjects['mid'][$osix] = $subjectsqr['mid'][$sbix];
	  }
	}
  }
  
  
  // Get the data of the exam subject collections
  $packages = SA_loadquery("SELECT * FROM subjectpackage");
  
  // Get a list of students with the subject package and extra subject
  $squery = "SELECT sid,lastname,firstname,gid,s_exnr.data AS exnr,packagename,extrasubject,extrasubject2,extrasubject3 FROM student LEFT JOIN sgrouplink USING(sid)";
  $squery .= " LEFT JOIN s_exnr USING(sid) LEFT JOIN s_package USING(sid) LEFT JOIN sgroup USING(gid) WHERE active=1 AND s_exnr.data IS NOT NULL AND s_exnr.data > '0' AND groupname='ExamVwo' GROUP BY sid ORDER BY s_exnr.data";
  $studs = SA_loadquery($squery);
  echo(mysql_error($userlink));
  $cquery = "SELECT sid,mid,result FROM gradestore WHERE period=2 AND year=\"". $schoolyear. "\"";
  $cres = SA_loadquery($cquery);
  echo(mysql_error($userlink));
  if(isset($cres))
  {
    foreach($cres['sid'] AS $cix => $csid)
	{
	  $resarray[$csid][$cres['mid'][$cix]] = $cres['result'][$cix];
	}
  }
  
  // Get the "vrijstelling" data
  $ex45qr = SA_loadquery("SELECT sid,mid,xstatus FROM ex45data WHERE year='". $schoolyear. "'");
  if(isset($ex45qr['sid']))
    foreach($ex45qr['sid'] AS $xdix => $xsid)
	{
	  if($ex45qr['xstatus'][$xdix] > 4 && $ex45qr['xstatus'][$xdix] < 9)
	    $vr[$xsid][$ex45qr['mid'][$xdix]] = $ex45qr['xstatus'][$xdix] + 2;
	  else if($ex45qr['xstatus'][$xdix] > 8 && $ex45qr['xstatus'][$xdix] < 14)
	    $cert[$xsid][$ex45qr['mid'][$xdix]] = $ex45qr['xstatus'][$xdix] - 3;
	  else if($ex45qr['xstatus'][$xdix] > 13)
	    $beh[$xsid][$ex45qr['mid'][$xdix]] = $ex45qr['xstatus'][$xdix] - 13;
	  else if($ex45qr['xstatus'][$xdix] == 2 || $ex45qr['xstatus'][$xdix] == 3)
	    $afw[$xsid][$ex45qr['mid'][$xdix]] = $ex45qr['xstatus'][$xdix] + 2;	  
	}
	
  SA_closeDB();

  // First part of the page
  echo("<html><head><title>Formulier EX. 2-VSE</title></head><body link=blue vlink=blue bgcolor=#E0E0FF>");
  echo '<LINK rel="stylesheet" type="text/css" href="style_EX1-M.css" title="style1">';
  
  print_head();

  $linecount = 0;
  
  // Student listing
  foreach($studs['gid'] AS $six => $gid)
  {
    if($linecount > 29)
	{
      print_foot();
	  $linecount = 0;
	  print_head();
	}
    echo("<TR><TD class=exnr>". $studs['exnr'][$six]. "</TD>");
	echo("<TD class=studname>". $studs['lastname'][$six]. "</TD><TD class=studname>". $studs['firstname'][$six]. "</TD>");
	echo("<TD class=subjind>");
	if(isset($resarray[$studs['sid'][$six]]))
	{
	  $sos = count($resarray[$studs['sid'][$six]]);
       if(isset($afw[$studs['sid'][$six]]))
	      $sos -= count($afw[$studs['sid'][$six]]);
	}
	else
	  $sos=0;
	if($sos < 1)
	  echo("&nbsp;");
	else
	  echo($sos);
	echo("</TD>");
	foreach($subjects['mid'] AS $mid)
	{
	  echo("<TD class=subjind>");
	  $hassubject = 0;
	  // check for subjects here!
	  foreach($packages['packagename'] AS $subix => $pname)
	  {
	    if($pname == $studs['packagename'][$six] && $mid == $packages['mid'][$subix])
		  $hassubject = 1;
	  }
	  if($mid == $studs['extrasubject'][$six] || $mid == $studs['extrasubject2'][$six] || $mid == $studs['extrasubject3'][$six])
	    $hassubject = 1;
	  if($hassubject == 0)
	    echo("&nbsp");
	  else
	  {
	    if(isset($resarray[$studs['sid'][$six]][$mid]) && $resarray[$studs['sid'][$six]][$mid] > 0.9 && !isset($afw[$studs['sid'][$six]][$mid]) && !isset($cert[$studs['sid'][$six]][$mid]))
		  echo(number_format($resarray[$studs['sid'][$six]][$mid],1,",","."));
	    else if(isset($vr[$studs['sid'][$six]][$mid]))
		  echo("V". $vr[$studs['sid'][$six]][$mid]);
	    else if(isset($cert[$studs['sid'][$six]][$mid]))
		  echo("C". $cert[$studs['sid'][$six]][$mid]);
	    else if(isset($beh[$studs['sid'][$six]][$mid]))
		  echo("B". $beh[$studs['sid'][$six]][$mid]);
		else
  	      echo("X");
		$subtotal[$mid]++;
	  }
      echo("</TD>");
	}
	echo("</TR>");
	$linecount++;	
  }
  
  print_foot();
  // close the page
  echo("</html>");
?>
