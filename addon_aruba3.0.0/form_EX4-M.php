<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2015 Aim4me N.V.   (http://www.aim4me.info)	      |
// +----------------------------------------------------------------------+
// | This program is free software.  You can redistribute in and/or       |
// | modify it under the terms of the GNU General Public License Version  |
// | 2 as published by the Free Software Foundation.                      |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY, without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program;  If not, write to the Free Software         |
// | Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.            |
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
    global $schoolyear,$schoolname,$subjects;
    echo("<div class=do>DIRECTIE ONDERWIJS ARUBA</div>");
    echo("<div class=ur>EX. 4 - M</div>");
    echo("<p>Lijst van kandidaten voor de herkansing.</p>");
	echo("<p>Tevens lijst van kandidaten, die om een geldige reden verhinderd waren het examen te voltooien.<BR>Inzenden binnen 1 week na elke uitslag (1 exemplaar).<p>");
    echo("<p>EINDEXAMEN <b>MAVO</b>, in het schooljaar ". $schoolyear. "</p>");
    echo("<p>School: ". $schoolname. "</p>");
	print_head2(" &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Kandidaten herkansing (met <b>x</b> aangegeven in welk vak kandidaat ge&euml;xamineerd wil worden)");
  }
  
  function print_head2($tablehead)
  {
    global $subjects;
    echo("<table class=studlist><TR><TH colspan=". (2+count($subjects['shortname'])). " class=studhead>". $tablehead. "</TH></TR>");
	echo("<TR><TH class=exnrhead>Ex.<BR>nr.</TH><TH class=studhead>Naam en voorletters van de kandidaat<BR>(in alfabetische volgorde)</TH>");
    foreach($subjects['shortname'] AS $sn)
    {
      echo("<TH class=subjhead>". substr($sn,0,strlen($sn) / 2). "<BR>". substr($sn,strlen($sn) / 2). "</TH>");
    }
    echo("</TR>");
  }
    
  function print_foot()
  {
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
  
  // Get a list of students 
  $squery = "SELECT lastname,firstname,sid,s_exnr.data AS exnr FROM student";
  $squery .= " LEFT JOIN s_exnr USING(sid) WHERE s_exnr.data IS NOT NULL AND s_exnr.data > '0' ORDER BY s_exnr.data";
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
  //echo("<html><head><title>Formulier EX. 4-M</title></head><body link=blue vlink=blue bgcolor=#FFE0E0 onload=\"window.print();setTimeout('window.close();',10000);\">");
  echo("<html><head><title>Formulier EX. 4-M</title></head><body link=blue vlink=blue bgcolor=#FFE0E0>");
  echo '<LINK rel="stylesheet" type="text/css" href="style_EX1-M.css" title="style1">';
  
  print_head();

  // Student listing for herexamen
  foreach($studs['sid'] AS $six => $sid)
  {
    if(isset($ex45h[$sid]))
	{
      echo("<TR><TD class=exnr>". $studs['exnr'][$six]. "</TD>");
	  echo("<TD class=studname>". $studs['lastname'][$six]. " ". get_initials($studs['firstname'][$six]). "</TD>");
	  foreach($subjects['mid'] AS $mid)
	  {
	    echo("<TD class=subjind>");
	    if(isset($ex45h[$sid][$mid]))
	      echo("<b>x</b>");
	    else
	      echo("&nbsp");
        echo("</TD>");
	  }
	  echo("</TR>");
	}
  }
  echo("</table><BR><BR><BR>");


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
  echo("</table>");
  
  print_foot();
  // close the page
  echo("</html>");
?>
