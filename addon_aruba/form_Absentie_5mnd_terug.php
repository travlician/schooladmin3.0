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
  
  // Functions
  function get_initials($name)
  {
    $explstring = explode(" ",$name);
    $retstr = "";
    foreach($explstring AS $addstr)
      $retstr .= " ". substr($addstr,0,1);
    return $retstr;
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
  
  // Get an array of days
  // Get the date of the last day of the month
  $lday = date("j",mktime(0,0,0,date("n")-4,0,date("Y")));
  $daynames = array( 1 => "M","D","W","D","V");
  for($dt=1; $dt<= $lday; $dt++)
  {
    $wday = date("N",mktime(0,0,0,date("n")-5,$dt,date("Y")));
    if($wday < 6)
	  $dtab[$dt] = $daynames[$wday];
  }
  $dispmonth = date("n",mktime(0,0,0,date("n")-4,0,date("Y")));
  $dispyear = date("Y",mktime(0,0,0,date("n")-4,0,date("Y")));

  // Get a list of students
  $studs = SA_loadquery("SELECT sid,firstname,lastname FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) WHERE active=1 AND groupname='". $_SESSION['CurrentGroup']. "' ORDER BY lastname,firstname");
  
  // Get a list of absence categories
  $abscats = SA_loadquery("SELECT IF(LENGTH(name) < 5,name,CONCAT(SUBSTR(name,1,3),'.')) AS abscat,acid FROM absencecats ORDER BY acid");
  // Convert to something more usefull
  foreach($abscats['acid'] AS $aix => $acid)
    $acat[$acid] = $abscats['abscat'][$aix];
  
  // Get the absence
  $aq = "SELECT asid,sid,SUBSTR(name,1,1) AS indicator,DAY(date) AS aday, explanation, firstname,lastname,acid";
  $aq .= " FROM absence LEFT JOIN absencereasons USING(aid) ";
  $aq .= " LEFT JOIN absencecats USING(acid) LEFT JOIN student USING(sid) LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid)";
  $aq .= " WHERE active=1 AND YEAR(date)=". $dispyear. " AND MONTH(date)=". $dispmonth. " AND groupname='". $_SESSION['CurrentGroup']. "' ORDER BY date";
  $absr = SA_loadquery($aq);

  // Convert the abs records to something more usefull
  $remcount = 0;
  if(isset($absr['asid']))
  foreach($absr['asid'] AS $aix => $asid)
  {
    $absence[$absr['sid'][$aix]][$absr['aday'][$aix]] = $absr['indicator'][$aix];
    $abscati[$absr['sid'][$aix]][$absr['aday'][$aix]] = $absr['acid'][$aix];
	if($absr['explanation'][$aix] != "")
	{
	  $remark[$aix] = $absr['aday'][$aix]. " ". $absr['firstname'][$aix]. " ". $absr['lastname'][$aix]. " : ". $absr['explanation'][$aix];
	  $lastremark = $aix;
	  $remcount++;
	}
  }
    
  $monthnames = array(1 => "Januari", "Februari", "Maart", "April", "Mei", "Juni", "Juli", "Augustus", "September", "Oktober", "November", "December");

  if(isset($studs))
  {
    // First part of the page
    echo("<html><head><title>Absentielijst</title></head><body link=blue vlink=blue>");
    echo '<LINK rel="stylesheet" type="text/css" href="style_Absentielijst.css" title="style1">';
	
	echo("<TABLE class=abstable><TR><TH class=schoolname colspan=4>". $schoolname. "</TH><TH colspan=12 class=absth> Schooljaar: ". $schoolyear. "</TH>");
	echo("<TH colspan=". (count($dtab) - 10 + count($acat)). " class=absth> Klas: ". $_SESSION['CurrentGroup']. "</TH></TR>");
	echo("<TR><TH class=typeth colspan=4>Absentielijst</TH><TH colspan=". (count($dtab) + count($acat) - 2). " class=monthth>Maand: ". $monthnames[$dispmonth]. " ". $dispyear. "</TH></TR>");
	echo("<TR><TH colspan=2 rowspan=2 class=namesth>Namen</TH>");
	foreach($dtab AS $dn)
	{
	  if($dn == "M")
  	    echo("<TH class=dayl>". $dn. "</TH>");
	  else
	    echo("<TH class=absth>". $dn. "</TH>");
	}
	echo("<TH colspan=". count($acat). " class=tothead>Totaal</TH></TR><TR>");
	foreach($dtab AS $dn => $dn2)
	  if($dn2 == "M")
  	    echo("<TH class=daylb>". $dn. "</TH>");
	  else
	    echo("<TH class=absthb>". $dn. "</TH>");
	$cc = 1;
	foreach($acat AS $acid => $ctxt)
	{
	  echo("<TH class=");
	  if($cc == 1)
	    echo("abs1th");
	  else if($cc == count($acat))
	    echo("abslth");
	  else
	    echo("absnth");
	  echo(">". $ctxt. "</TH>");
	  $cc++;
	}
	echo("</TR>");
	
	// Now show a row for each student
	$sno = 1;
	foreach($acat AS $acid => $dummy)
      $tot[$acid] = 0;
	foreach($dtab AS $dd => $dummy)
	  $totD[$dd] = 0;
	foreach($studs['sid'] AS $six => $sid)
	{
	  foreach($acat AS $acid => $dummy)
	    $cur[$acid] = 0;
	  if($sno%5 == 0)
	  {
	    $sncls = "stnotdb";
		$snmcls = "stnamtdb";
	  }
	  else
	  {
	    $sncls = "stnotd";
		$snmcls = "stnamtd";
	  }
	  echo("<TR><TD class=". $sncls. ">". $sno. "</TD><TD class=". $snmcls. ">". $studs['firstname'][$six]. " ". $studs['lastname'][$six]. "</TD>");
	  foreach($dtab AS $dn => $adt)
	  {
	    if($adt == "M" && $sno%5==0)
	      echo("<TD class=tdlb>");
		else if($adt == "M")
		  echo("<TD class=tdl>");
		else if($sno%5==0)
		  echo("<TD class=tdb>");
		else
		  echo("<TD>");
		if(isset($absence[$sid][$dn]))
		{
		  echo($absence[$sid][$dn]);
		  $cur[$abscati[$sid][$dn]]++;
		  $totD[$dn]++;		  
	    }
		else
		  echo("&nbsp");
		echo("</TD>");
	  }
	  $cc = 1;
	  foreach($acat AS $acid => $dummy)
	  {
	    if($cc == 1 && $sno%5 == 0)
		  echo("<TD class=tdlb>");
		else if($cc == 1)
		  echo("<TD class=tdl>");
		else if($cc == count($acat) && $sno%5 == 0)
		  echo("<TD class=tdrb>");
		else if($cc == count($acat))
		  echo("<TD class=tdr>");
		else if($sno%5 == 0)
		  echo("<TD class=tdb>");
		else
		  echo("<TD>");
		echo(($cur[$acid] > 0 ? $cur[$acid] : "&nbsp;"). "</TD>");
		$cc++;
	  }
	  echo("</TR>");
	  $sno++;
	  foreach($acat AS $acid => $dummy)
	    $tot[$acid] += $cur[$acid];
	}
	
	  echo("<TR><TD colspan=2 class=totalbelow> Totaal:</TD>");
	  foreach($dtab AS $dn => $adt)
	  {
	    if($adt == "M" )
	      echo("<TD class=tdltb>");
        else
		  echo("<TD class=tdtb>");
		echo($totD[$dn]. "</TD>");
	  }
	  foreach($acat AS $acid => $dummy)
        echo("<TD class=totalbelow>". $tot[$acid]. "</TD>");
	  echo("</TR>");
	  $sno++;
	  foreach($acat AS $acid => $dummy)
	    $tot[$acid] += $cur[$acid];

  } // Endif students defined
  // Now the remarks
  $firstrem = true;
  if(isset($remark))
    foreach($remark AS $aix => $remtxt)
	{
      echo("<TR><TD class=remark COLSPAN=". (2+count($dtab)). ">". $remtxt. "</TD>");
	  if($firstrem)
	    echo("<TD rowspan=". $remcount. " colspan=". count($acat). " class=paraaf>Paraaf:</TD>");
	  $firstrem = false;
	  echo("</TR>");
	}
  echo("</TABLE>");
  
  // close the page
  echo("</html>");
?>
