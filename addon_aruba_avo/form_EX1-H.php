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
  $offsubjects = array(1 => "Ne","En","I&S","Sp","Pa","Fa","Wi-A","Wi-B","Na","Sk","Bio","Ec","M&O","Ak","Gs","Inf","Pfw","CKV");
  // AH & CP alternates
  $altsubjects = array("Ne"=>1,"En"=>2,"I&S"=>3,"Sp"=>4,"Pa"=>5,"Fa"=>6,"Wi-A"=>7,"Wi-B"=>8,"Na"=>9,"Sk"=>10,"Bio"=>11,"Ec"=>12,"M&O"=>13,"Ak"=>14,"Gs"=>15,"Inf"=>16,"Pfw"=>17,"CKV"=>18,
                       "ne"=>1,"en"=>2,"i&s"=>3,"sp"=>4,"pap"=>5,"wiA"=>7,"wiB"=>8,"na"=>9,"sk"=>10,"bio"=>11,"ec"=>12,"m&o"=>13,"ak"=>14,"gs"=>15,"inf"=>16,"pfw"=>17,"ckv"=>18);
  
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
    echo("<div class=ur>EX. 1 - H</div>");
    echo("<p>Genummerde alfabetische naamlijst van de kandidaten (art. 23 Landsbesluit eindexamens dagscholen, AB 1991 No. GT 35).");
    echo("<BR>Inzenden voor 1 oktober (1 exemplaar).</p>");
    echo("<p>EINDEXAMEN <b>HAVO</b>, in het schooljaar ". $schoolyear. "</p>");
    echo("<p>School: ". $schoolname. "</p>");
    echo("<table class=studlist><TR><TH class=exnrhead>Ex.<BR>nr.</TH><TH class=studhead>Achternaam en voornamen van de kandidaat<BR>(in alfabetische volgorde)</TH>");
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
  $squery = "SELECT lastname,firstname,sid,s_exnr.data AS exnr,packagename,extrasubject,extrasubject2,extrasubject3 FROM student";
  $squery .= " LEFT JOIN s_exnr USING(sid) LEFT JOIN s_package USING(sid) LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) WHERE active=1 AND s_exnr.data IS NOT NULL AND s_exnr.data > '0'";
  $squery .= " AND groupname='ExamHavo' ORDER BY s_exnr.data";
  $studs = SA_loadquery($squery);
  echo(mysql_error($userlink));
  
  // Get the "vrijstelling" info
  $freest = SA_loadquery("SELECT * FROM ex45data WHERE xstatus>=5 AND year='". $schoolyear. "'");
  if(isset($freest))
    foreach($freest['sid'] AS $fix => $sid)
	  $frees[$sid][$freest['mid'][$fix]] = $freest['xstatus'][$fix];
  // Also added to "vrijstelling" info is aquired results for I&S and PFW
  $freest = SA_loadquery("SELECT * FROM ahxdata WHERE mid<>0 AND year='". $schoolyear. "'");
  if(isset($freest))
    foreach($freest['sid'] AS $fix => $sid)
	  $frees[$sid][$freest['mid'][$fix]] = $freest['xstatus'][$fix] + 13.0;

  SA_closeDB();

  // First part of the page
  // echo("<html><head><title>Formulier EX. 1-M</title></head><body link=blue vlink=blue bgcolor=#E0FFE0 onload=\"window.print();setTimeout('window.close();',10000);\">");
  echo("<html><head><title>Formulier EX. 1-H</title></head><body link=blue vlink=blue bgcolor=#E0FFE0>");
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
//	  echo("<TD class=studname>". $studs['lastname'][$six]. " ". get_initials($studs['firstname'][$six]). "</TD>");
	  echo("<TD class=studname><b>". $studs['lastname'][$six]. "</b> ". $studs['firstname'][$six]. "</TD>");
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
	      if(isset($frees[$sid][$mid]))
				{
					if($frees[$sid][$mid] < 9)
						echo("class=subjindv><b>v</b>");
					else if($frees[$sid][$mid] < 14)
							echo("class=subjindv><b>c</b>");
					else
						echo("class=subjindv><b>b</b>");
				}
				else
				{
						if($mid == $studs['extrasubject2'][$six] || $mid == $studs['extrasubject3'][$six])
							echo("class=subjind7><b>x</b>");
				else
							echo("class=subjind><b>x</b>");
					$subtotal[$mid]++;
				}
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
