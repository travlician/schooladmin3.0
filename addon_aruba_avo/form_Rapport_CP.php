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
  require_once("schooladminfunctions.php");
  require_once("teacher.php");
  // Link inputlib to database
  inputclassbase::dbconnect($userlink);
  if(substr($_SESSION['CurrentGroup'],0,2) == "H3" || substr($_SESSION['CurrentGroup'],0,2) == "V3")
  {
	  // CA 3
	  $vakcats = array("Kernvakken","Talen","Natuur en Wetenschap","Maatschappij","Cultuur en Kunst","Onderwijs Ondersteunend");
	  $vakhead["Kernvakken"] = array("ne","en","wi");
	  $vakhead["Talen"] = array("sp","pap");
	  $vakhead["Natuur en Wetenschap"] = array("na","sk","bio");
	  $vakhead["Maatschappij"] = array("e&m","ak","gs");
	  $vakhead["Cultuur en Kunst"] = array("bv","de","da","mu","ckv");
	  $vakhead["Onderwijs Ondersteunend"] = array("ik","lo","rek");
	  $newan = array("ne","en","wi","na");
	  $ptvakken = array("lo","e&m","ak","gs","ne","en","sp","pap","ckv","na","sk","bio","wi","ik");
  }
  else if(substr($_SESSION['CurrentGroup'],0,2) == "CB")
  {	  
	  // CB
	  $vakcats = array("Kernvakken","Talen","Natuur en Wetenschap","Maatschappij","Cultuur en Kunst","Onderwijs Ondersteunend");
	  $vakhead["Kernvakken"] = array("ne","en","wi");
	  $vakhead["Talen"] = array("sp","pap");
	  $vakhead["Natuur en Wetenschap"] = array("n&t");
	  $vakhead["Maatschappij"] = array("asw");
	  $vakhead["Cultuur en Kunst"] = array("bv","de","da","mu","ckv");
	  $vakhead["Onderwijs Ondersteunend"] = array("pv","ik","lo","rek","kgl");
	  $newan = array("ne","en","wi","asw","n&t");
	  $ptvakken = array("kgl","pv","lo","asw","pap","ne","en","sp","ckv","n&t","wi","ik");
  }
  else if(substr($_SESSION['CurrentGroup'],0,2) == "HL")
  {
	  // HL
	  $vakcats = array("Kernvakken","Talen","Natuur en Wetenschap","Maatschappij","Cultuur en Kunst","Onderwijs Ondersteunend");
	  $vakhead["Kernvakken"] = array("ne","en","wiA","WiB");
	  $vakhead["Talen"] = array("sp","pap");
	  $vakhead["Natuur en Wetenschap"] = array("na","sk","bio","inf");
	  $vakhead["Maatschappij"] = array("ec","m&o","ak","gs");
	  $vakhead["Cultuur en Kunst"] = array("bv","de","da","mu","ckv");
	  $vakhead["Onderwijs Ondersteunend"] = array("i&s","lo","rek");
	  $newan = array("ne","en","wi","asw","n&t");
	  $ptvakken = array("i&s","lo","ec","m&o","ak","gs","ne","en","sp","ckv","na","sk","bio","wiA","wiB");
  }
  else if(substr($_SESSION['CurrentGroup'],0,2) == "H4")
  {	 
	  // H4
	  $vakcats = array("Kernvakken","Talen","Natuur en Wetenschap","Maatschappij","Cultuur en Kunst","Onderwijs Ondersteunend");
	  $vakhead["Kernvakken"] = array("ne","en","wiA","wiB");
	  $vakhead["Talen"] = array("sp","pap");
	  $vakhead["Natuur en Wetenschap"] = array("na","sk","bio","inf");
	  $vakhead["Maatschappij"] = array("ec","m&o","ak","gs");
	  $vakhead["Cultuur en Kunst"] = array("bv","de","da","mu","ckv");
	  $vakhead["Onderwijs Ondersteunend"] = array("i&s","lo","rek");
	  $newan = array("ne","en","wi","asw","n&t");
	  $ptvakken = array("i&s","lo","ec","m&o","ak","gs","ne","en","sp","pap","ckv","na","sk","bio","wiA","wiB");
  }
  
  /* Test systeem 
  $vakhead["Individu"] = array("kgl","pv","lo");
  $vakhead["Maatschappij"] = array("asw");
  $vakhead["Taal & Communicatie"] = array("pa","ne","en","sp");
  $vakhead["Kunst & Cultuur"] = array("mu","te","ckv");
  $vakhead["Natuur"] = array("n&tt","n&tp","n&t");
  $vakhead["Wiskunde"] = array("wi");
  $vakhead["Onderw. Onderst."] = array("ik");
  $newan = array("ne","en","wi","asw","n&t");
  $ptvakken = array("kgl","pv","lo","asw","pa","ne","en","sp","ckv","n&t","wi","ik");
  */
  
  $afwezigreden = array(3,4,5,11,16,23,24,28,25,17,26,27,28,29,32);
  $telaatreden = array(6,7,8,9,10,18,19,20,21,22);
	$dagafwreden = array(2,33);
  $groepfilter = $_SESSION['CurrentGroup'];
  $llnperpage = 1;
  $rectorid=122;
  
  // First see if date is already typed, if not ask for it!
  if(!isset($_POST['rdate']))
  {
    echo("<P>Afdruk instellingen (Firefox):<BR>Marges op 0,2 inch, <b>95% scaling</b>, geen header/footer (Blank), Landscape, A4.<BR>KIES EERST DE JUISTE KLAS!</P>"); 
    echo("<FORM name=rdatefrm id=rdatefrm METHOD=POST ACTION=". $_SERVER['PHP_SELF']. ">Rapport datum: <INPUT TYPE=TEXT SIZE=40 NAME=rdate><INPUT TYPE=SUBMIT NAME='OK' VALUE='OK'></FORM>");
    exit();
  }
  
  // Define in which period we are. aug-dec -> 1, jan-apr -> 2, may-jul->3
  if(date('n') > 8)
    $repper = 1;
  else if(date('n') < 5)
    $repper = 2;
  else
    $repper = 3;
  
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
  $reportyear = substr($schoolyear,0,4). " - ". substr($schoolyear,-4);
  
  // Get a list of groups
  $groups = SA_loadquery("SELECT * FROM sgroup LEFT JOIN ". $teachercode. " ON(tid_mentor=tid) WHERE active=1 AND groupname LIKE '". $groepfilter. "' ORDER BY groupname");
  
  // Get a list of last test dates for periods
  //$perends = SA_loadquery("SELECT period,CEIL(date) AS edate FROM testdef GROUP BY period ORDER BY period");
  
  if(isset($groups))
  {
    // First part of the page
    echo("<html><head><title>Rapport</title></head><body link=blue vlink=blue>");
    echo '<LINK rel="stylesheet" type="text/css" href="style_Rapport_MH.css" title="style1">';

    foreach($groups['gid'] AS $gix => $gid)
	{

      // Get a list of students
      $students = SA_loadquery("SELECT * FROM student LEFT JOIN sgrouplink USING(sid) WHERE gid=". $gid. " ORDER BY lastname,firstname");

	  // Get a list of period dates
//	  $pquery = "SELECT period, FLOOR(date) AS sdate, CEIL(date) AS edate FROM testdef LEFT JOIN class USING(cid)";
//	  $pquery .= " WHERE year=\"". $schoolyear. "\" AND gid=". $gid. " AND period < 4 GROUP BY period";
      $pquery = "SELECT id AS period,startdate AS sdate, enddate AS edate FROM period";
	  $perdata = SA_loadquery($pquery);
	  foreach($perdata['period'] AS $pix => $pid)
	  {
	    $pdata[$pid]["sdate"] = $perdata["sdate"][$pix];
	    $pdata[$pid]["edate"] = $perdata["edate"][$pix];
	  }
	  // Fill in invalid period data
	  if(!isset($pdata[1]["sdate"]))
	    $pdata[1]["sdate"]= "2000-01-01";
	  if(!isset($pdata[1]["edate"]))
	    $pdata[1]["edate"] = $pdata[1]["sdate"];
	  if(!isset($pdata[2]["sdate"]))
	    $pdata[2]["sdate"]= $pdata[1]['edate'];
	  if(!isset($pdata[2]["edate"]))
	    $pdata[2]["edate"]= $pdata[2]["sdate"];
	  if(!isset($pdata[3]["sdate"]))
	    $pdata[3]["sdate"]= $pdata[2]['edate'];
	  if(!isset($pdata[3]["edate"]))
	    $pdata[3]["edate"]= $pdata[3]["sdate"];

	  if(isset($students))
	  {
	    $llnoffset = 0;
		while ($llnoffset < sizeof($students['sid']))
		{
		  $scnt = $llnperpage;
		  if(sizeof($students['sid']) - $llnoffset < $scnt)
		    $scnt = sizeof($students['sid']) - $llnoffset;
		  echo("<DIV class=leftblock>");
		  echo("<TABLE BORDER=1 class=lefttable>");
		  echo("<TR><TD class=headleft>Colegio San Nicolas</td><td class=schoolyear colspan=". ($repper < 3 ? (1+$repper) : 4). ">". $reportyear. "</td></tr>");
		  echo("<TR><TD class=groupname>Klas: <SPAN class=groupnameblue>". $groups['groupname'][$gix]. "</TD></TR>");
		  echo("<TR><TD class=mentorcode>Mentor: ". $groups['data'][$gix]. "</TD></TR>");
		  
		  echo("<TR><TH>Vakken</TH><TH>Doc.</TH>");
		  for($sx = 1; $sx <= $scnt; $sx++)
		  {
		    for($ph = 1; $ph <= $repper; $ph++)
			{
		      echo("<TH class=centerth>". $ph. "</TH>");
			  if($ph == 3)
			    echo("<TH class=centerth>E</TH>");
			}
		  }
		  echo("</TR>");
		  
		  // Get the student results for students in set
		  for($sx = 1; $sx <= $scnt; $sx++)
		  {
 		    // Create a list of subject details
		    unset($subjdata);
		    unset($ckvinvalid);
		    $sdquery = "SELECT type, fullname, shortname, data FROM class LEFT JOIN subject USING(mid) LEFT JOIN ". $teachercode. " USING(tid) LEFT JOIN sgrouplink USING(gid)
		              WHERE sid=". $students['sid'][$llnoffset+$sx];
//		    $sdquery .= " UNION SELECT type, fullname, shortname, '' FROM subject WHERE type='meta'";
// Change request 3 dec 2013: show all subjects, irrepectiv if the student follows it! We do that by a union with all subjects
			$sdquery .= " UNION SELECT type, fullname, shortname, '' FROM subject";
		    $subjectdata = SA_loadquery($sdquery);
		    foreach($subjectdata['shortname'] AS $cix => $subjab)
		    {
			  if($subjectdata["data"][$cix] != "") // Don't overwrite teacher id when blank, result by changd union 3 dec 2013
			    $subjdata[$subjab]["teacher"] = $subjectdata["data"][$cix];
			  $subjdata[$subjab]["fullname"] = str_replace(" bovenbouw","",$subjectdata["fullname"][$cix]);
			  $subjdata[$subjab]["type"] = $subjectdata["type"][$cix];
		    }
		    $sres = SA_loadquery("SELECT period, result, shortname FROM gradestore LEFT JOIN subject USING(mid) WHERE sid=". $students['sid'][$llnoffset+$sx]. " AND year=\"". $schoolyear. "\" ");
			if(isset($sres))
			  foreach($sres['period'] AS $rix => $perid)
			    $stres[$llnoffset+$sx][$sres['shortname'][$rix]][$perid] = $sres['result'][$rix];
			unset($sres);
		  }

			if(substr($_SESSION['CurrentGroup'],0,3) == "XHL1")
			{
				// Correction for HL1 only: if average of period 1 and 2 > period 0 then period 0 is averge of period 1 and 2 but only for ne
				foreach($stres AS $stix => $strs)
				{ // aka for each student
					foreach($strs AS $ssn => $strsn)
					{ // Aka for each subject
						if($ssn == "ne" && isset($strsn[1]) && isset($strsn[2]) && round(($strsn[1] + $strsn[2]) / 2.0,$ssn == "i&s" ? 1 : 0) > $strsn[0])
							$stres[$stix][$ssn][0] = round(($strsn[1] + $strsn[2]) / 2.0,$ssn == "i&s" ? 1 : 0);
					}
				}
			}

		  
		  foreach($vakcats AS $vk)
		  {
		    /*echo("<TR><TD class=cathead COLSPAN=2>". $vk. "</TD>");
		    for($sx = 1; $sx <= $scnt; $sx++)
		    {
		      echo("<TD COLSPAN=". ($repper == 3 ? 4 : $repper). ">&nbsp;</TD>");
		    }
			echo("</TR>"); */
			echo("<TR><TD COLSPAN=". ($repper == 3 ? 5 : ($repper+1)). " style='font-size: 6px;'>&nbsp;</TD></TR>");
			foreach($vakhead[$vk] AS $vkn)
			{
			  if(isset($subjdata[$vkn]))
			  {
					if(isset($subjdata[$vkn]["teacher"]) && $subjdata[$vkn]["teacher"] != "")
					{
						if($subjdata[$vkn]['type'] == "sub")
							echo("<TR><TD class=subsubjname>". $subjdata[$vkn]["fullname"]. "</TD><TD>". $subjdata[$vkn]["teacher"]. "</TD>");
						else
							echo("<TR><TD class=subjname>". $subjdata[$vkn]["fullname"]. "</TD><TD>". $subjdata[$vkn]["teacher"]. "</TD>");
					}
				  else
					{
						if($subjdata[$vkn]['type'] == "sub")
							echo("<TR><TD class=subsubjname colspan=2>". $subjdata[$vkn]["fullname"]. "</TD>");
						else
							echo("<TR><TD class=subjname colspan=2>". $subjdata[$vkn]["fullname"]. "</TD>");
					}
				  for($sx = 1; $sx <= $scnt; $sx++)
				  {
				    if($subjdata[$vkn]['type'] == "sub" || $vkn == "rekx")
					  echo("<TD class=centertdsmall>");
					else
					  echo("<TD class=centertd>");
					if($vkn == "rekx" && isset($stres[$llnoffset+$sx][$vkn][1]))
					{
						if($stres[$llnoffset+$sx][$vkn][1] < 5.5)
							echo("Onvoldoende");
						else
							echo("Voldoende");
					}
					else if(isset($stres[$llnoffset+$sx][$vkn][1]) && ($vkn != "ckv" || !isset($ckvinvalid[$llnoffset+$sx])))
					{
					  echo(colored(number_format($stres[$llnoffset+$sx][$vkn][1],1,',','.')));
					}
					else
					{
					  //if($vk == "Kunst & Cultuur")
					  //  $ckvinvalid[$llnoffset+$sx] = 1;
					  echo("");
					}
					echo("</TD>");
					if($repper > 1)
					{
				    if($subjdata[$vkn]['type'] == "sub" || $vkn == "rekx")
					    echo("<TD class=centertdsmall>");
					  else
						  echo("<TD class=centertd>");
						if($vkn == "rekx" && isset($stres[$llnoffset+$sx][$vkn][2]))
						{
							if($stres[$llnoffset+$sx][$vkn][2] < 5.5)
								echo("Onvoldoende");
							else
								echo("Voldoende");
						}
						else if(isset($stres[$llnoffset+$sx][$vkn][2]))
						  echo(colored(number_format($stres[$llnoffset+$sx][$vkn][2],1,',','.')));
						else
						  if($repper > 1)
							echo("");
						  else
							echo("&nbsp;");
						echo("</TD>");
					}
					if($repper > 2)
					{
				    if($subjdata[$vkn]['type'] == "sub" || $vkn == "rekx")
					    echo("<TD class=centertdsmall>");
					  else
						  echo("<TD class=centertd>");
						if($vkn == "rekx" && isset($stres[$llnoffset+$sx][$vkn][3]))
						{
							if($stres[$llnoffset+$sx][$vkn][3] < 5.5)
								echo("Onvoldoende");
							else
								echo("Voldoende");
						}
						else if(isset($stres[$llnoffset+$sx][$vkn][3]))
						  echo(colored(number_format($stres[$llnoffset+$sx][$vkn][3],1,',','.')));
						else
						  if($repper > 2)
								echo("");
							else
								echo("&nbsp;");
						echo("</TD>");
				    if($subjdata[$vkn]['type'] == "sub" || $vkn == "rek")
					    echo("<TD class=centertdsmall>");
					  else
						  echo("<TD class=centertd>");
						if($vkn == "rek" && isset($stres[$llnoffset+$sx][$vkn][0]))
						{
							if($stres[$llnoffset+$sx][$vkn][0] < 5.5)
								echo("Onvoldoende");
							else
								echo("Voldoende");
						}
						else if(isset($stres[$llnoffset+$sx][$vkn][0]) && ($vkn != "ckv" || !isset($ckvinvalid[$llnoffset+$sx])) && $subjdata[$vkn]['type'] != "sub")
						  echo(colored($stres[$llnoffset+$sx][$vkn][0]));
						else
						  echo("&nbsp;");
						echo("</TD>");
					}
				  }
				  echo("</TR>");
			  }
			} // End for each subject
		  } // End subject categories
		  
		  // Calculate points for advice
		  for($sx = 1; $sx <= $scnt; $sx++)
		  {
		    for($p=0;$p<4;$p++)
			{
			  $stcalp[$sx][$p] = 0;
			  $stcalo[$sx][$p] = 0;
			  $stcalc[$sx][$p] = 0;
			  $stcaln[$sx][$p] = 0;
			}
			foreach($ptvakken AS $vak)
			{
			  for($p=0;$p<4;$p++)
			  {
			    if(isset($stres[$sx+$llnoffset][$vak][$p]))
				{
				  if($p == 0)
			        $stcalp[$sx][$p] += round($stres[$sx+$llnoffset][$vak][$p],0);
			      else
			        $stcalp[$sx][$p] += round($stres[$sx+$llnoffset][$vak][$p],1);
				  if($stres[$sx+$llnoffset][$vak][$p] < 5.5)
				  {
				    $stcalo[$sx][$p]++;
					$stcalc[$sx][$p] += 6 - round($stres[$sx+$llnoffset][$vak][$p],0);
				  }			    
				}
			  }
			}
			foreach($newan AS $vak)
			{
			  for($p=0;$p<4;$p++)
			  {
			    if(isset($stres[$sx+$llnoffset][$vak][$p]))
				  if($p == 0)
			        $stcaln[$sx][$p] += round($stres[$sx+$llnoffset][$vak][$p],0);
				  else
			        $stcaln[$sx][$p] += round($stres[$sx+$llnoffset][$vak][$p],1);
			  }
			}
          }

		  // Empty row for separation
		  echo("<TR><TD colspan=". (2+($repper == 3 ? 4 : $repper)*$llnperpage). ">&nbsp;</td></tr>");
		  
		  // Show calculated results
		  echo("<TR><TD COLSPAN=2>Totaal punten</TD>");
		  for($sx = 1; $sx <= $scnt; $sx++)
		  {
		    for($p=0;$p<($repper == 3 ? 4 : $repper) ;$p++)
			{
			  $pi = ($p + 1) % 4;
			  echo("<TD class=centertd>");
			  if(isset($stcalp[$sx][$pi]) && $stcalp[$sx][$pi] > 0)
			    echo($stcalp[$sx][$pi]);
			  else
			    echo("&nbsp");
			  echo("</TD>");
			}
		  }
		  echo("</TR>");
		  
		  echo("<TR><TD COLSPAN=2>Aantal onvoldoende</TD>");
		  for($sx = 1; $sx <= $scnt; $sx++)
		  {
		    for($p=0;$p<($repper == 3 ? 4 : $repper);$p++)
			{
			  $pi = ($p + 1) % 4;
			  echo("<TD class=centertd>");
			  if(isset($stcalo[$sx][$pi]) && $stcalp[$sx][$pi] > 0)
			    echo($stcalo[$sx][$pi]);
			  else
			    echo("&nbsp");
			  echo("</TD>");
			}
		  }
		  echo("</TR>");
		  
		  echo("<TR><TD COLSPAN=2>Tekorten</TD>");
		  for($sx = 1; $sx <= $scnt; $sx++)
		  {
		    for($p=0;$p<($repper == 3 ? 4 : $repper);$p++)
			{
			  $pi = ($p + 1) % 4;
			  echo("<TD class=centertd>");
			  if(isset($stcalc[$sx][$pi]) && $stcalp[$sx][$pi] > 0)
			    echo($stcalc[$sx][$pi]);
			  else
			    echo("&nbsp");
			  echo("</TD>");
			}
		  }
		  echo("</TR>");

		  // Absence data
		 
		  echo("<TR><TH COLSPAN=2>Lesuren afwezig</TH>");
		  for($sx = 1; $sx <= $scnt; $sx++)
		  {
					// Get the absence data hours
				$absq = "SELECT SUM(IF(date >= '". $pdata[1]["sdate"]. "' AND date < '". $pdata[2]["sdate"]. "',1,0)) AS ap1";
				$absq .= ",SUM(IF(date >= '". $pdata[2]["sdate"]. "' AND date < '". $pdata[3]["sdate"]. "',1,0)) AS ap2";
				$absq .= ",SUM(IF(date >= '". $pdata[3]["sdate"]. "',1,0)) AS ap3";
				$absq .= " FROM absence WHERE sid=". $students['sid'][$llnoffset + $sx]. " AND (";
				$afwfilt = "";
				foreach($afwezigreden AS $afwr)
				{
					$afwfilt .= " OR aid=". $afwr;
				}
				$absq .= substr($afwfilt,4). ") GROUP BY sid";
				$absdat = SA_loadquery($absq);
				if(isset($absdat))
				{
					echo("<TD class=centertd>". $absdat["ap1"][1]. "</TD>");
					if($repper > 1)
						echo("<TD class=centertd>". $absdat["ap2"][1]. "</TD>");
					if($repper > 2)
						echo("<TD class=centertd>". $absdat["ap3"][1]. "</TD><TD class=centertd>". ($absdat["ap1"][1] + $absdat["ap2"][1] + $absdat["ap3"][1]). "</TD>");
				}
				else
				{
					$cels = $repper == 3 ? 4 : $repper;
					for($bc=1; $bc <= $cels; $bc++)
						echo("<TD class=centertd>0</TD>");
				}
				unset($absdat);
		  }
		  echo("</TR>");

		  echo("<TR><TH COLSPAN=2>Dagen afwezig</TH>");
		  for($sx = 1; $sx <= $scnt; $sx++)
		  {
					// Get the absence data hours
				$absq = "SELECT SUM(IF(date >= '". $pdata[1]["sdate"]. "' AND date < '". $pdata[2]["sdate"]. "',1,0)) AS ap1";
				$absq .= ",SUM(IF(date >= '". $pdata[2]["sdate"]. "' AND date < '". $pdata[3]["sdate"]. "',1,0)) AS ap2";
				$absq .= ",SUM(IF(date >= '". $pdata[3]["sdate"]. "',1,0)) AS ap3";
				$absq .= " FROM absence WHERE sid=". $students['sid'][$llnoffset + $sx]. " AND (";
				$afwfilt = "";
				foreach($dagafwreden AS $afwr)
				{
					$afwfilt .= " OR aid=". $afwr;
				}
				$absq .= substr($afwfilt,4). ") GROUP BY sid";
				$absdat = SA_loadquery($absq);
				if(isset($absdat))
				{
					echo("<TD class=centertd>". $absdat["ap1"][1]. "</TD>");
					if($repper > 1)
						echo("<TD class=centertd>". $absdat["ap2"][1]. "</TD>");
					if($repper > 2)
						echo("<TD class=centertd>". $absdat["ap3"][1]. "</TD><TD class=centertd>". ($absdat["ap1"][1] + $absdat["ap2"][1] + $absdat["ap3"][1]). "</TD>");
				}
				else
				{
					$cels = $repper == 3 ? 4 : $repper;
					for($bc=1; $bc <= $cels; $bc++)
						echo("<TD class=centertd>0</TD>");
				}
				unset($absdat);
		  }
		  echo("</TR>");

		  echo("<TR><TH COLSPAN=2>Te laat</TH>");
		  for($sx = 1; $sx <= $scnt; $sx++)
		  {
		    // Get the absence data
			$absq = "SELECT SUM(IF(date >= '". $pdata[1]["sdate"]. "' AND date < '". $pdata[2]["sdate"]. "',1,0)) AS ap1";
			$absq .= ",SUM(IF(date >= '". $pdata[2]["sdate"]. "' AND date < '". $pdata[3]["sdate"]. "',1,0)) AS ap2";
			$absq .= ",SUM(IF(date >= '". $pdata[3]["sdate"]. "',1,0)) AS ap3";
			$absq .= " FROM absence WHERE sid=". $students['sid'][$llnoffset + $sx]. " AND (";
			$afwfilt = "";
			foreach($telaatreden AS $afwr)
			{
			  $afwfilt .= " OR aid=". $afwr;
			}
			$absq .= substr($afwfilt,4). ") GROUP BY sid";
			$absdat = SA_loadquery($absq);
			if(isset($absdat))
			{
			  echo("<TD class=centertd>". $absdat["ap1"][1]. "</TD>");
			  if($repper > 1)
			    echo("<TD class=centertd>". $absdat["ap2"][1]. "</TD>");
			  if($repper > 2)
			    echo("<TD class=centertd>". $absdat["ap3"][1]. "</TD><TD class=centertd>". ($absdat["ap1"][1] + $absdat["ap2"][1] + $absdat["ap3"][1]). "</TD>");
			}
			else
			{
			  $cels = $repper == 3 ? 4 : $repper;
			  for($bc=1; $bc <= $cels; $bc++)
		      echo("<TD class=centertd>0</TD>");
			}
			unset($absdat);
		  }
		  echo("</TR>");

		  
		  echo("</TABLE></DIV>");
		  $sx=1;
		  echo("<DIV class=rightblock>");
		  echo("<P class=studentname>Naam: <SPAN class=stfirstname>". $students['firstname'][$sx+$llnoffset]. "</SPAN><SPAN class=stlastname> ". $students['lastname'][$sx+$llnoffset]. "</SPAN></P>");
		  echo("<P class=dateline>Datum: ". $_POST['rdate']. "</P>");
		  echo("<P class=opmbox>Opmerkingen bij het ". ($repper == 1 ? "eerste" : ($repper == 2 ? "tweede" : "derde")). " rapport:</p>");
		  
?>
		<p class=explained>BETEKENIS VAN DE CIJFERS:<BR>
		  <SPAN class=exdigit>10</SPAN><SPAN class=extext>Uitmuntend</SPAN><SPAN class=exdigit>5</SPAN>Bijna voldoende</SPAN><BR>
		  <SPAN class=exdigit>9</SPAN><SPAN class=extext>Zeer goed</SPAN><SPAN class=exdigit>4</SPAN>Onvoldoende</SPAN><BR>
		  <SPAN class=exdigit>8</SPAN><SPAN class=extext>Goed</SPAN><SPAN class=exdigit>3</SPAN>Zeer onvoldoende</SPAN><BR>
		  <SPAN class=exdigit>7</SPAN><SPAN class=extext>Ruim voldoende</SPAN><SPAN class=exdigit>2</SPAN>Slecht</SPAN><BR>
		  <SPAN class=exdigit>6</SPAN><SPAN class=extext>Voldoende</SPAN><SPAN class=exdigit>1</SPAN>Zeer slecht</SPAN></P>
<?
          $mygroup = new group();
		  $mygroup->load_current();
		  $me=$mygroup->get_mentor();
		  $rector = new teacher($rectorid);
		  echo("<P class=signbox>Mentor: ". $me->get_teacher_detail("t_formele_naam"). "</P>");
			echo("<img class=signimg src=dirfirm.png>");
		  echo("<P class=signboxdir>Rector: ". $rector->get_teacher_detail("t_formele_naam"). "</P>");
		  echo("</DIV><p class=footer>&nbsp;</p>");
		  $llnoffset += $llnperpage;
		} // End while for subgroups of students
	  } // End if student for the group
	
	  unset($stres);
	} // End for each group
  } // End if groups defined
  else
    echo("Geen groepen gevonden!");
      
  echo("</html>");
  
  function colored($res)
  {
     $res2 = str_replace(',','.',$res);
	 if($res2 < 5.5)
	   return("<SPAN class=redcolor>". $res. "</SPAN>");
	 else
	   return($res);
  }
?>
