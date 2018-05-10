<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014 Aim4me N.V.   (http://www.aim4me.info)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
  
  // Subject translation tables
  $offsubjects = array(1 => "Ne","En","I&S","Sp","Pa","Wi-A","Wi-B","Na","Sk","Bio","Ec","M&O","Ak","Gs","Inf","Pfw","CKV");
  // AH & CP alternates
  $altsubjects = array("Ne"=>1,"En"=>2,"I&S"=>3,"Sp"=>4,"Pa"=>5,"Wi-A"=>6,"Wi-B"=>7,"Na"=>8,"Sk"=>9,"Bio"=>10,"Ec"=>11,"M&O"=>12,"Ak"=>13,"Gs"=>14,"Inf"=>15,"Pfw"=>16,"CKV"=>17,
                       "ne"=>1,"en"=>2,"i&s"=>3,"sp"=>4,"pap"=>5,"wiA"=>6,"wiB"=>7,"na"=>8,"sk"=>9,"bio"=>10,"ec"=>11,"m&o"=>12,"ak"=>13,"gs"=>14,"inf"=>15,"pfw"=>16,"ckv"=>17);
 
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
    global $schoolyear,$schoolname,$subjects;
    echo("<div class=do>DIRECTIE ONDERWIJS ARUBA</div>");
    echo("<div class=ur>EX. 4 - H</div>");
    echo("<p>Lijst van kandidaten voor de herkansing.</p>");
	echo("<p>Tevens lijst van kandidaten, die om een geldige reden verhinderd waren het examen te voltooien.<BR>Inzenden binnen 1 week na elke uitslag (1 exemplaar).<p>");
    echo("<p>EINDEXAMEN <b>HAVO</b>, in het schooljaar ". $schoolyear. "</p>");
    echo("<p>School: ". $schoolname. "</p>");
	print_head2(" &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Kandidaten herkansing (met <b>x</b> aangegeven in welk vak kandidaat ge&euml;xamineerd wil worden)");
  }
  
  function print_head2($tablehead)
  {
    global $subjects,$subtotal;
    echo("<table class=studlist><TR><TH colspan=". (2+count($subjects['shortname'])). " class=studhead>". $tablehead. "</TH></TR>");
	echo("<TR><TH class=exnrhead>Ex.<BR>nr.</TH><TH class=studhead>Naam en voorletters van de kandidaat<BR>(in alfabetische volgorde)</TH>");
    foreach($subjects['shortname'] AS $sn)
    {
      echo("<TH class=subjhead>". substr($sn,0,strlen($sn) / 2). "<BR>". substr($sn,strlen($sn) / 2). "</TH>");
    }
    echo("</TR>");
    // Reset subject total counts
    foreach($subjects['mid'] AS $mid)
      $subtotal[$mid] = 0;
  }
    
  function print_foot()
  {
    global $subjects,$subtotal;
    // Show the total of students per subject
    echo("<TR><TD class=nolines>&nbsp</TD><TD class=total>TOTAAL</TD>");
    foreach($subjects['mid'] AS $mid)
      echo("<TD class=subjind>". $subtotal[$mid]. "</TD>");
    echo("</TR>");
    // Show the subjects again
    echo("<TR><TD class=nolines>&nbsp</TD><TD class=nolines>&nbsp</TD>");
    foreach($subjects['shortname'] AS $sn)
    {
      echo("<TH class=subjhead>". substr($sn,0,strlen($sn) / 2). "<BR>". substr($sn,strlen($sn) / 2). "</TH>");
    }
    echo("</TR></TABLE>");
    // Footing
    echo("<p class=footing>Dit formulier dient tevens voor bestelling schriftelijk werk.");
    echo("<BR>Ex. nr. en naam dienen in overeenstemming te zijn met formulier EX. 1.</p>");
    echo("<div class=sign>..........................., .......................");
    echo("<BR>Plaats&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp Datum");
    echo("<BR><BR><BR><BR>. . . . . . . . . . . . . . . . . . . . . &nbsp&nbsp&nbsp&nbsp . . . . . . . . . . . . . . . . . . . . . .");
    echo("<BR>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp voorzitter &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp secretaris</div>");
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
  
  // Get a list of students 
  $squery = "SELECT lastname,firstname,sid,s_exnr.data AS exnr FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid)";
  $squery .= " LEFT JOIN s_exnr USING(sid) WHERE active=1 AND s_exnr.data IS NOT NULL AND s_exnr.data > '0' AND groupname='ExamHavo' ORDER BY s_exnr.data";
  $studs = SA_loadquery($squery);
  echo(mysql_error($userlink));
  
  // Get a list of data concerning non attendance
  $ex45hdata = SA_loadquery("SELECT * FROM ex45data WHERE year='". $schoolyear. "' AND xstatus=1");
  // Convert the ex45data to a usefull array
  if(isset($ex45hdata))
    foreach($ex45hdata['xstatus'] AS $xix => $xst)
	  $ex45h[$ex45hdata['sid'][$xix]][$ex45hdata['mid'][$xix]] = $xst;

  $ex45adata = SA_loadquery("SELECT * FROM ex45data WHERE year='". $schoolyear. "' AND xstatus>1 AND xstatus<5");
  // Convert the ex45data to a usefull array
  if(isset($ex45adata))
    foreach($ex45adata['xstatus'] AS $xix => $xst)
	  $ex45a[$ex45adata['sid'][$xix]][$ex45adata['mid'][$xix]] = $xst;

  SA_closeDB();

  // First part of the page
  echo("<html><head><title>Formulier EX. 4-H</title></head><body link=blue vlink=blue bgcolor=#FFE0E0>");
  echo '<LINK rel="stylesheet" type="text/css" href="style_EX1-M.css" title="style1">';
  
  print_head();
  $linecount = 0;

  // Student listing for herexamen
  foreach($studs['sid'] AS $six => $sid)
  {
      if($linecount > 29)
	  {
        print_foot();
	    $linecount = 0;
	    print_head();
	  }
    if(isset($ex45h[$sid]))
	{
      echo("<TR><TD class=exnr>". $studs['exnr'][$six]. "</TD>");
	  echo("<TD class=studname>". $studs['lastname'][$six]. " ". get_initials($studs['firstname'][$six]). "</TD>");
	  foreach($subjects['mid'] AS $mid)
	  {
	    echo("<TD class=subjind>");
	    if(isset($ex45h[$sid][$mid]))
		{
	      echo("<b>x</b>");
		  $subtotal[$mid]++;
		}
	    else
	      echo("&nbsp");
        echo("</TD>");
	  }
	  echo("</TR>");
	  $linecount++;
	}
  }
  print_foot();


  // Student listing for absence during exam parts
  print_head2("<center>Kandidaten die om een geldige reden verhinderd waren het examen te voltooien.<BR>(voortzetting schoolonderszoek aangeven met <b>o</b>, schriftelijk examen met <b>s</b> en modeling examen met <b>m</b>).</center>");
  foreach($studs['sid'] AS $six => $sid)
  {
    if(isset($ex45a[$sid]))
	{
      echo("<TR><TD class=exnr>". $studs['exnr'][$six]. "</TD>");
	  echo("<TD class=studname>". $studs['lastname'][$six]. " ". get_initials($studs['firstname'][$six]). "</TD>");
	  foreach($subjects['mid'] AS $mid)
	  {
	    echo("<TD class=subjind>");
	    if(isset($ex45a[$sid][$mid]) && $ex45a[$sid][$mid] == 2)
	      echo("<b>m</b>");
	    else if(isset($ex45a[$sid][$mid]) && $ex45a[$sid][$mid] == 3)
	      echo("<b>o</b>");
	    if(isset($ex45a[$sid][$mid]) && $ex45a[$sid][$mid] == 4)
	      echo("<b>s</b>");
	    else
	      echo("&nbsp");
        echo("</TD>");
	  }
	  echo("</TR>");
	}
  }
  print_foot();
  // close the page
  echo("</html>");
?>
