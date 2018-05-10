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
  
  // Operational definitions
  $vakcats = array("Individu","Maatschappij","Taal & Communicatie","Kunst & Cultuur","Natuur","Wiskunde","Onderw. Onderst.");
  /* LS */
  $vakhead["Individu"] = array("KGL","PV","LO");
  $vakhead["Maatschappij"] = array("ASW");
  $vakhead["Taal & Communicatie"] = array("PA","NE","EN","SP");
  $vakhead["Kunst & Cultuur"] = array("CKV");
  $vakhead["Natuur"] = array("N & T");
  $vakhead["Wiskunde"] = array("WI","REK");
  $vakhead["Onderw. Onderst."] = array("IK");
  $newan = array("NE","EN","WI","ASW","N & T");
  $newanp = array("NE","EN","WI","ASW","N & T","PV");
  $ptvakken = array("KGL","PV","LO","ASW","PA","NE","EN","SP","CKV","N & T","WI","IK");
  $min4vkn = array("NE","EN","WI");
 
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
  
  $afwezigreden = array(1,2,3,4,5,15);
  $telaatreden = array(6,7,8,9);
  $groepfilter = "2%' OR groupname LIKE '1%";
  $llnperpage = 8;
  
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
  
  // Get a list of groups
  $groups = SA_loadquery("SELECT * FROM sgroup LEFT JOIN ". $teachercode. " ON(tid_mentor=tid) WHERE active=1 AND groupname LIKE '". $groepfilter. "' ORDER BY groupname");
  
  // Get a list of last test dates for periods
  //$perends = SA_loadquery("SELECT period,CEIL(date) AS edate FROM testdef GROUP BY period ORDER BY period");
  
  if(isset($groups))
  {
    // First part of the page
    echo("<html><head><title>Bespreeklijst</title></head><body link=blue vlink=blue>");
    echo '<LINK rel="stylesheet" type="text/css" href="style_Bespreeklijst.css" title="style1">';

    foreach($groups['gid'] AS $gix => $gid)
	{
	  // Create a list of subject details
	  $sdquery = "SELECT type, fullname, shortname, data FROM class LEFT JOIN subject USING(mid) LEFT JOIN ". $teachercode. " USING(tid) WHERE gid=". $gid;
	  $sdquery .= " UNION SELECT type, fullname, shortname, '' FROM subject WHERE type='meta'";
	  $subjectdata = SA_loadquery($sdquery);
	  foreach($subjectdata['shortname'] AS $cix => $subjab)
	  {
	    $subjdata[$subjab]["teacher"] = $subjectdata["data"][$cix];
		$subjdata[$subjab]["fullname"] = $subjectdata["fullname"][$cix];
		$subjdata[$subjab]["type"] = $subjectdata["type"][$cix];
	  }

      // Get a list of students
      $students = SA_loadquery("SELECT * FROM student LEFT JOIN sgrouplink USING(sid) WHERE gid=". $gid. " ORDER BY lastname,firstname");

	  // Get a list of period dates
	  $pquery = "SELECT period, FLOOR(date) AS sdate, CEIL(date) AS edate FROM testdef LEFT JOIN class USING(cid)";
	  $pquery .= " WHERE year=\"". $schoolyear. "\" AND gid=". $gid. " AND period < 4 GROUP BY period";
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
		  echo("<TABLE BORDER=1><TR><TH class=headleft COLSPAN=2>". $schoolname. "<BR>Schooljaar ". $schoolyear. "<BR>Klas: ". $groups['groupname'][$gix]. "<BR>Mentor: ". $groups['data'][$gix]. " </TH>");
		  for($sx = 1; $sx <= $scnt; $sx++)
		  {
		    echo("<TH COLSPAN=4>". ($sx+$llnoffset). ". ". $students['lastname'][$sx+$llnoffset]. "<BR><SPAN class=stfirstname>". $students['firstname'][$sx+$llnoffset]. "</SPAN></TH>");
		  }
		  echo("</TR>");
		  
		  echo("<TR><TH>Vakken</TH><TH>Doc.</TH>");
		  for($sx = 1; $sx <= $scnt; $sx++)
		  {
		    echo("<TH>1</TH><TH>2</TH><TH>3</TH><TH>E</TH>");
		  }
		  echo("</TR>");
		  
		  // Get the student results for students in set
		  for($sx = 1; $sx <= $scnt; $sx++)
		  {
		    $sres = SA_loadquery("SELECT period, result, shortname FROM gradestore LEFT JOIN subject USING(mid) WHERE sid=". $students['sid'][$llnoffset+$sx]. " AND year=\"". $schoolyear. "\" ");
			if(isset($sres))
			  foreach($sres['period'] AS $rix => $perid)
			    $stres[$llnoffset+$sx][$sres['shortname'][$rix]][$perid] = $sres['result'][$rix];
			unset($sres);
		  }

		  
		  foreach($vakcats AS $vk)
		  {
		    echo("<TR><TD class=cathead COLSPAN=2>". $vk. "</TD>");
		    for($sx = 1; $sx <= $scnt; $sx++)
		    {
		      echo("<TD COLSPAN=4>&nbsp;</TD>");
		    }
			echo("</TR>");
			foreach($vakhead[$vk] AS $vkn)
			{
			  echo("<TR><TD>". $subjdata[$vkn]["fullname"]. "</TD><TD>". $subjdata[$vkn]["teacher"]. "</TD>");
		      for($sx = 1; $sx <= $scnt; $sx++)
		      {
			    echo("<TD>");
				if(isset($stres[$llnoffset+$sx][$vkn][1]))
				  echo(colored(number_format($stres[$llnoffset+$sx][$vkn][1],1,',','.')));
				else
				  echo("&nbsp;");
				echo("</TD>");
			    echo("<TD>");
				if(isset($stres[$llnoffset+$sx][$vkn][2]))
				  echo(colored(number_format($stres[$llnoffset+$sx][$vkn][2],1,',','.')));
				else
				  echo("&nbsp;");
				echo("</TD>");
			    echo("<TD>");
				if(isset($stres[$llnoffset+$sx][$vkn][3]))
				  echo(colored(number_format($stres[$llnoffset+$sx][$vkn][3],1,',','.')));
				else
				  echo("&nbsp;");
				echo("</TD>");
			    echo("<TD>");
				if(isset($stres[$llnoffset+$sx][$vkn][0]))
				  echo(colored($stres[$llnoffset+$sx][$vkn][0]));
				else
				  echo("&nbsp;");
				echo("</TD>");
		      }
			  echo("</TR>");
			  
			} // End for each subject
		  } // End subject categories
		  
		  // Absence data
		  echo("<TR><TH class=headleft COLSPAN=2>Afwezig</TH>");
		  for($sx = 1; $sx <= $scnt; $sx++)
		  {
		    // Get the absence data
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
			  echo("<TD>". $absdat["ap1"][1]. "</TD><TD>". $absdat["ap2"][1]. "</TD><TD>". $absdat["ap3"][1]. "</TD><TD>");
			  echo(($absdat["ap1"][1] + $absdat["ap2"][1] + $absdat["ap3"][1]). "</TD>");
			}
			else
		      echo("<TD COLSPAN=4>&nbsp;</TD>");
			unset($absdat);
		  }
		  echo("</TR>");

		  echo("<TR><TH class=headleft COLSPAN=2>Te laat</TH>");
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
			  echo("<TD>". $absdat["ap1"][1]. "</TD><TD>". $absdat["ap2"][1]. "</TD><TD>". $absdat["ap3"][1]. "</TD><TD>");
			  echo(($absdat["ap1"][1] + $absdat["ap2"][1] + $absdat["ap3"][1]). "</TD>");
			}
			else
		      echo("<TD COLSPAN=4>&nbsp;</TD>");
			unset($absdat);
		  }
		  echo("</TR>");
		  
		  // Calculate points for advice
		  for($sx = 1; $sx <= $scnt; $sx++)
		  {
		    for($p=0;$p<4;$p++)
				{
					$stcalp[$sx][$p] = 0.0;  // Total points
					$stcalo[$sx][$p] = 0;  // Subjects < 5.5
					$stcalc[$sx][$p] = 0;  // Points less than 6
					$stcaln[$sx][$p] = 0;  // NEWAN points
					$stcalnp[$sx][$p] = 0.0; // NEWANP subjects < 5.5
					$stcaln4[$sx][$p] = 0; // NE /EN / WI < 3.5
					$stcaln5[$sx][$p] = 0; // NE / EN / WI < 4.5
				}
				foreach($ptvakken AS $vak)
				{
					for($p=0;$p<4;$p++)
					{
						if(isset($stres[$sx+$llnoffset][$vak][$p]))
						{
							$stcalp[$sx][$p] += $stres[$sx+$llnoffset][$vak][$p];
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
						{
							$stcaln[$sx][$p] += $stres[$sx+$llnoffset][$vak][$p];
						}
					}
				}
				foreach($min4vkn AS $vak)
				{
					for($p=0;$p<4;$p++)
					{
						if(isset($stres[$sx+$llnoffset][$vak][$p]))
						{
							if($stres[$sx+$llnoffset][$vak][$p] < 3.5)
								$stcaln4[$sx][$p]++;
							if($stres[$sx+$llnoffset][$vak][$p] < 4.5)
								$stcaln5[$sx][$p]++;
						}
					}
				}
				foreach($newanp AS $vak)
				{
					for($p=0;$p<4;$p++)
					{
						if(isset($stres[$sx+$llnoffset][$vak][$p]))
						{
							if($stres[$sx+$llnoffset][$vak][$p] < 5.5)
								$stcalnp[$sx][$p]++;
						}
					}
				}
      }
		  
		  // Show calculated results
		  echo("<TR><TD COLSPAN=2>Totaal punten</TD>");
		  for($sx = 1; $sx <= $scnt; $sx++)
		  {
		    for($p=0;$p<4;$p++)
			{
			  $pi = ($p + 1) % 4;
			  echo("<TD>");
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
		    for($p=0;$p<4;$p++)
			{
			  $pi = ($p + 1) % 4;
			  echo("<TD>");
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
		    for($p=0;$p<4;$p++)
			{
			  $pi = ($p + 1) % 4;
			  echo("<TD>");
			  if(isset($stcalc[$sx][$pi]) && $stcalp[$sx][$pi] > 0)
			    echo($stcalc[$sx][$pi]);
			  else
			    echo("&nbsp");
			  echo("</TD>");
			}
		  }
		  echo("</TR>");
		  
		  echo("<TR><TD COLSPAN=2>NEWAN punten</TD>");
		  for($sx = 1; $sx <= $scnt; $sx++)
		  {
		    for($p=0;$p<4;$p++)
			{
			  $pi = ($p + 1) % 4;
			  echo("<TD>");
			  if(isset($stcaln[$sx][$pi]) && $stcaln[$sx][$pi] > 0)
			    echo($stcaln[$sx][$pi]);
			  else
			    echo("&nbsp");
			  echo("</TD>");
			}
		  }
		  echo("</TR>");
		  		  
		  echo("<TR><TH class=headleft COLSPAN=2>Bevorderingsnorm</TH>");
		  for($sx = 1; $sx <= $scnt; $sx++)
		  {
		    for($p=0;$p<4;$p++)
			{
			  $pi = ($p + 1) % 4;
			  echo("<TD>");
			  if($stcalp[$sx][$pi] >= 90.0 && $stcalo[$sx][$pi] == 0 && $stcaln[$sx][$pi] >= 40.0)
			    echo("HV");
			  else if(($stcalp[$sx][$pi] >= 72.0 || ($stcalp[$sx][$pi] >= 71.0 && $stcalc[$sx][$pi] == 1)) && $stcalo[$sx][$pi] <= 3 && $stcalc[$sx][$pi] <= 4 && $stcaln4[$sx][$pi] == 0)
			    echo("BV");
			  else if($stcalp[$sx][$pi] >= 66.0 && $stcalo[$sx][$pi] <= 4 && $stcalc[$sx][$pi] <= 4 && $stcaln5[$sx][$pi] == 0)
			    echo("BE");
			  else if($stcalp[$sx][$pi] < 65.0 || $stcalnp[$sx][$pi]  == 6)
			    echo("EPB");
			  else
			    echo("DB");
			  echo("</TD>");
			}
		  }
		  echo("</TR>");
		  
		  echo("</TABLE>");
		  echo("<P class=footer>Norm: HV=HAVO, BV=Bevorderen, BE=Bespreekgeval, DB=Doubleren, EPB=Verwijzing EPB</P>");

		  $llnoffset += $llnperpage;
		} // End while for subgroups of students
	  } // End if student for the group
	
	  unset($stres);
	} // End for each group
  } // End if groups defined
      
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
