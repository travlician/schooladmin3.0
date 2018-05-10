<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011 Aim4me N.V.   (http://www.aim4me.info)	      |
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
					   "NA"=>6, "EC"=>9, "EM & O"=>9, "CKV"=>12, "Ckv"=>12);
  
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
    echo("<div class=ur>EX. 1 - CKV cijfers</div>");
    echo("<p>Genummerde alfabetische naamlijst van de kandidaten (art. 23 Landsbesluit eindexamens dagscholen MAVO, AB 1991 No. GT 35).<BR>Lijst met resultaten voor het vak CKV in de derde klas");
    echo("<BR>Inzenden voor 1 oktober (1 exemplaar).</p>");
    echo("<p>EINDEXAMEN <b>MAVO</b>, in het schooljaar ". $schoolyear. "</p>");
    echo("<p>School: ". $schoolname. "</p>");
    echo("<table class=studlist><TR><TH class=exnrhead>Ex.<BR>nr.</TH><TH class=studhead>Achternaam en voornamen van de kandidaat<BR>(in alfabetische volgorde)</TH><TH class=subjhead>1</TH><TH class=subjhead>2</TH><TH class=subjhead>3</TH><TH class=subjhead>Eind</TH></TR>");
  }
  
  function print_foot()
  {
    echo("</TABLE>");
    // Footing
    echo("<p class=footing>Ex. nr.: onder dit nummer doet de kandidaat examen.</p>");
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
  $subjectsqr = SA_loadquery("SELECT shortname,mid FROM subject ORDER BY mid");
  foreach($offsubjects AS $osix => $sjname)
  {
    foreach($subjectsqr['shortname'] AS $sbix => $subsn)
	{
	  if(isset($altsubjects[$subsn]) && $altsubjects[$subsn] == $osix)
	  {
	    if($sjname == "CKV")
		  $ckvmid = $subjectsqr['mid'][$sbix];
	  }
	}
  }
  
  // Get a list of students 
  $squery = "SELECT lastname,firstname,sid,s_exnr.data AS exnr FROM student";
  $squery .= " LEFT JOIN s_exnr USING(sid) WHERE s_exnr.data IS NOT NULL AND s_exnr.data > '0' ORDER BY s_exnr.data";
  $studs = SA_loadquery($squery);
  echo(mysql_error($userlink));
  
  // Get the CKV results from the last year
  $ckvrq = "SELECT sid,period,result FROM gradestore LEFT JOIN (SELECT sid,MAX(year) AS gsyear FROM gradestore WHERE mid=". $ckvmid. " GROUP BY sid) AS gsy USING(sid) WHERE year=gsyear AND mid=". $ckvmid;
  $ckvrqr = SA_loadquery($ckvrq);
  if(isset($ckvrqr['result']))
  {
    foreach($ckvrqr['sid'] AS $cix => $asid)
	{
	  $ckvr[$asid][$ckvrqr['period'][$cix]] = $ckvrqr['result'][$cix]; 
	}
  }
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
	  echo("<TD class=subjind>". (isset($ckvr[$sid][1]) ? number_format($ckvr[$sid][1],1,",",".") : "X"). "</TD>");
	  echo("<TD class=subjind>". (isset($ckvr[$sid][2]) ? number_format($ckvr[$sid][2],1,",",".") : "X"). "</TD>");
	  echo("<TD class=subjind>". (isset($ckvr[$sid][3]) ? number_format($ckvr[$sid][3],1,",",".") : "X"). "</TD>");
	  echo("<TD class=subjind>". (isset($ckvr[$sid][0]) ? $ckvr[$sid][0] : "X"). "</TD>");
	  echo("</TR>");
	  $linecount++;	
    }
  
  print_foot();
  // close the page
  echo("</html>");
?>
