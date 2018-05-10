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
  $offsubjects = array(1 => "Ne","En","Sp","Pa","Wi","Nask1","Nask2","Bio","EcMo","Ak","Gs","CKV");
  $altsubjects = array("NAT4"=>6, "GES4"=>11, "AK4"=>10, "EC4"=>9, "BIO4"=>8, "SCH4"=>7, "WIS4"=>5, "PAP4"=>4, "SPA4"=>3, "ENG4"=>2, "NED4"=>1,
                       "Ne"=>1, "En"=>2, "Sp"=>3, "Wi"=>5, "Na"=>6, "Sk"=>7, "Bio"=>8, "Gs"=>11, "Ak"=>10, "Ec"=>9, "Pa"=>4, "NaSk 1"=>6, "NaSk 2"=>7, "EcMo"=>9,
					   "PA"=>4, "NE"=>1, "EN"=>2, "SP"=>3, "WI"=>5, "AK"=>10, "BI"=>8, "GS"=>11, "Na"=>6, "SK"=>7, "EC/MO"=>9,
					   "ne"=>1, "en"=>2, "sp"=>3, "pa"=>4, "wi"=>5, "na"=>6, "sk"=>7, "bi"=>8, "ec"=>9, "ak"=>10, "gs"=>11,
					   "NA"=>6, "EC"=>9, "EM & O"=>9, "CKV"=>12, "Ckv"=>12, "NED"=>1, "ENG"=>2, "SPA"=>3, "PAP"=>4, "WIS"=>5, "NASK1" => 6,"NS2"=>7, "NASK2" => 7, "BIO"=>8, "GES"=>11, "CKV"=>12, "bio"=>8, "CKV alg"=>12, "ecmo"=>9,"Nask 1"=>6,"Nask 2"=>7);
  
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
    global $schoolyear,$schoolname,$subjects,$subtotal;
    echo("<div class=do>DIRECTIE ONDERWIJS ARUBA</div>");
    echo("<div class=ur>EX. 1 - M</div>");
    echo("<p>Genummerde alfabetische naamlijst van de kandidaten (art. 23 Landsbesluit eindexamens dagscholen MAVO, AB 1991 No. GT 35).");
    echo("<BR>Inzenden voor 1 oktober (1 exemplaar).</p>");
    echo("<p>EINDEXAMEN <b>MAVO</b>, in het schooljaar ". $schoolyear. "</p>");
    echo("<p>School: ". $schoolname. "</p>");
    echo("<table class=studlist><TR><TH class=exnrhead>Ex.<BR>nr.</TH><TH class=studhead>Achternaam en voornamen van de kandidaat<BR>(in alfabetische volgorde)</TH>");
    foreach($subjects['shortname'] AS $sn)
    {
      echo("<TH class=subjhead>". substr($sn,0,strlen($sn) / 2). "<BR>". substr($sn,strlen($sn) / 2). "</TH>");
    }
		echo("<TH class=subjhead># vakken</TH>");
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
    echo("<BR>Ex. nr.: onder dit nummer doet de kandidaat examen.");
    echo("<BR>Vakken waarin geëxamineerd moet worden, aangeven met <b>x</b>.</p>");
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
  
  // Get a list of subjects applicable to the exam subjects
  $subjectsqr = SA_loadquery("SELECT shortname,subjectpackage.mid,fullname FROM subjectpackage LEFT JOIN subject USING(mid) UNION SELECT shortname,extrasubject,fullname FROM s_package LEFT JOIN subject ON(mid=extrasubject) WHERE shortname IS NOT NULL  UNION SELECT shortname,extrasubject2,fullname FROM s_package LEFT JOIN subject ON(mid=extrasubject2) WHERE shortname IS NOT NULL  UNION SELECT shortname,extrasubject3,fullname FROM s_package LEFT JOIN subject ON(mid=extrasubject3) WHERE shortname IS NOT NULL GROUP BY mid ORDER BY mid");
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
  $squery = "SELECT lastname,firstname,sid,s_exnr.data AS exnr,packagename,extrasubject,extrasubject2,extrasubject3 FROM student";
  $squery .= " LEFT JOIN s_exnr USING(sid) LEFT JOIN s_package USING(sid) WHERE s_exnr.data IS NOT NULL AND s_exnr.data > '0' ORDER BY s_exnr.data";
  $studs = SA_loadquery($squery);
  echo(mysql_error($userlink));
  
  // Get the "vrijstelling" info
  $freest = SA_loadquery("SELECT * FROM ex45data WHERE xstatus>=5 AND year='". $schoolyear. "'");
  if(isset($freest))
    foreach($freest['sid'] AS $fix => $sid)
	  $frees[$sid][$freest['mid'][$fix]] = 1;

  SA_closeDB();

  // First part of the page
  // echo("<html><head><title>Formulier EX. 1-M</title></head><body link=blue vlink=blue bgcolor=#E0FFE0 onload=\"window.print();setTimeout('window.close();',10000);\">");
  echo("<html><head><title>Formulier EX. 1-M</title></head><body link=blue vlink=blue bgcolor=#E0FFE0>");
  echo '<LINK rel="stylesheet" type="text/css" href="style_EX1-M.css" title="style1">';
  
  print_head();

  $linecount = 0;
  
  // Student listing
  if(isset($studs['sid']))
    foreach($studs['sid'] AS $six => $sid)
    {
      if($linecount > 29)
	  {
        print_foot();
	    $linecount = 0;
	    print_head();
	  }
      echo("<TR><TD class=exnr>". $studs['exnr'][$six]. "</TD>");
	  echo("<TD class=studname>". $studs['lastname'][$six]. ", ". $studs['firstname'][$six]. "</TD>");
		$stscnt = 0;
	  foreach($subjects['mid'] AS $mid)
	  {
	    echo("<TD ");
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
	      echo("class=subjind>&nbsp");
	    else
	    {
				$stscnt++;
	      if(isset($frees[$sid][$mid]))
		    echo("class=subjindv><b>v</b>");
				else if($mid == $studs['extrasubject'][$six] || $mid == $studs['extrasubject2'][$six] || $mid == $studs['extrasubject3'][$six])
				{
					echo("class=subjind7><b>x</b>");
					$subtotal[$mid]++;
				}
				else
				{
						echo("class=subjind><b>x</b>");
					$subtotal[$mid]++;
				}
	    }
        echo("</TD>");
	  }
		echo("<TD class=subjind>". $stscnt. "</td>");
	  echo("</TR>");
	  $linecount++;	
    }
  
  print_foot();
  // close the page
  echo("</html>");
?>
