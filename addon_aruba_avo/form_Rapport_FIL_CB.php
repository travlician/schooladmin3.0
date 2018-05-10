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
  
	// Check if rapportdatum is valid format
	if(isset($_POST['rapportdatum']))
	{
		if(strlen($_POST['rapportdatum']) < 10)
			$invaliddate = true;
		else
		{
			$invaliddate = false;
			$d=substr($_POST['rapportdatum'],0,2);
			$m=substr($_POST['rapportdatum'],3,2);
			$y=substr($_POST['rapportdatum'],6,4);
			if($d < 1 || $d > 31 || $m < 1 || $m > 12 || $y < 2015 || $y > 3000)
			{
				$invaliddate=true;
			}
		}
		if($invaliddate)
		{
			echo("Ongeldige datum ingevoerd (". $_POST['rapportdatum']. ")<BR>");
		}
	}
	if(!isset($_POST['rapportdatum']) || $invaliddate)
	{
		echo("<FORM METHOD=POST>Rapportdatum (formaat dd-mm-yyyy): <INPUT TYPE=TEXT NAME=rapportdatum><INPUT TYPE=SUBMIT VALUE='Afdrukken'></FORM>");
		exit;
	}

  // Operational definitions
  $vakcats = array("Individu","Maatschappij","Taal & Communicatie","Kunst & Cultuur","Natuur","Wiskunde","Onderwijs Ondersteunend");
 
  $vakhead["Individu"] = array("KGL","PV","LO");
  $vakhead["Maatschappij"] = array("ASW");
  $vakhead["Taal & Communicatie"] = array("PA","NE","EN","SP");
  $vakhead["Kunst & Cultuur"] = array("CKV");
  $vakhead["Natuur"] = array("N&T");
  $vakhead["Wiskunde"] = array("WI","Rek");
  $vakhead["Onderwijs Ondersteunend"] = array("IK");
  $newan = array("NE","EN","WI","ASW","N&T");
  $ptvakken = array("KGL","PV","LO","ASW","PA","NE","EN","SP","CKV","N&T","WI","Rek","IK");
   
  $afwezigreden = array(1,2,3,4,5,13,15,18);
  $telaatreden = array(6,7,8,9,16,17);
  $groepfilter = "2%' OR groupname LIKE '1%";
  $llnperpage = 1;
  
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

  // Get the remarks for the report
  $rems = SA_loadquery("SELECT sid,opmtext,period FROM bo_opmrap_data WHERE year='". $schoolyear. "'");
  if(isset($rems)) // convert it to easier array
    foreach($rems['sid'] AS $rix => $rsid)
	  $rapopms[$rsid][$rems['period'][$rix]] = $rems['opmtext'][$rix];
	//echo("<WVW>". count($rapopms). " opm records read</WVW>");
  
  if(isset($groups))
  {
    // First part of the page
    echo("<html><head><title>Rapport</title>");
	echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
	echo("</head><body link=blue vlink=blue>");
    echo '<LINK rel="stylesheet" type="text/css" href="style_Rapport_FIL.css" title="style1">';

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
      $students = SA_loadquery("SELECT student.* FROM student LEFT JOIN sgrouplink USING(sid) WHERE gid=". $gid. " ORDER BY lastname,firstname");

	  // Get a list of period dates
	  $pquery = "SELECT id AS period, startdate AS sdate, enddate AS edate, status FROM period ORDER BY period";
	  $perdata = SA_loadquery($pquery);
	  foreach($perdata['period'] AS $pix => $pid)
	  {
	    $pdata[$pid]["sdate"] = $perdata["sdate"][$pix];
	    $pdata[$pid]["edate"] = $perdata["edate"][$pix];
		$pdata[$pid]["status"] = $perdata["status"][$pix];
	  }
	  if(isset($students))
	  {
	    $llnoffset = 0;
		while ($llnoffset < sizeof($students['sid']))
		{
		  $scnt = $llnperpage;
		  if(sizeof($students['sid']) - $llnoffset < $scnt)
		    $scnt = sizeof($students['sid']) - $llnoffset;
		  echo("<DIV class=leftpage>");
		  echo("<img class=schoolimage src=schoollogo.png width=133>");
		  for($sx = 1; $sx <= $scnt; $sx++)
		  {
		    echo("<P class=studdata>NAAM: ". $students['firstname'][$sx + $llnoffset]. " ". $students['lastname'][$sx+$llnoffset]);
		    echo("<BR>SCHOOLJAAR: ". $schoolyear);
		    echo("<BR>KLAS: ". substr($groups['groupname'][$gix],0,2). "</P>");
		    echo("<TABLE BORDER=1>");
		  }
		  
		  echo("<TR><TH>LEERGEBIEDEN</TH><TH>VAKKEN</TH>");
		  for($sx = 1; $sx <= $scnt; $sx++)
		  {
		    echo("<TH class=rth>R1</TH><TH class=rth>R2</TH><TH class=rth>R3</TH><TH class=rOth>RO</TH>");
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
		    echo("<TR><TD class=cathead ROWSPAN=". count($vakhead[$vk]). ">". $vk. "</TD>");
			foreach($vakhead[$vk] AS $vkn)
			{
			  echo("<TD>". $subjdata[$vkn]["fullname"]. "</TD>");
		      for($sx = 1; $sx <= $scnt; $sx++)
		      {
			    echo("<TD class=rapres>");
				if(isset($stres[$llnoffset+$sx][$vkn][1]))
				  echo(colored(number_format($stres[$llnoffset+$sx][$vkn][1],1,',','.')));
				else
				  echo("&nbsp;");
				echo("</TD>");
			    echo("<TD class=rapres>");
				if(isset($stres[$llnoffset+$sx][$vkn][2]))
				  echo(colored(number_format($stres[$llnoffset+$sx][$vkn][2],1,',','.')));
				else
				  echo("&nbsp;");
				echo("</TD>");
			    echo("<TD class=rapres>");
				if(isset($stres[$llnoffset+$sx][$vkn][3]))
				  echo(colored(number_format($stres[$llnoffset+$sx][$vkn][3],1,',','.')));
				else
				  echo("&nbsp;");
				echo("</TD>");
			    echo("<TD class=rapOres>");
				if(isset($stres[$llnoffset+$sx][$vkn][0]) && isset($stres[$llnoffset+$sx][$vkn][3]))
				  echo(colored($stres[$llnoffset+$sx][$vkn][0]));
				else
				  echo("&nbsp;");
				echo("</TD>");
		      }
			  echo("</TR>");
			  
			} // End for each subject
		  } // End subject categories
		  		  
/*		  // Calculate points for advice
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
			      $stcalp[$sx][$p] += round($stres[$sx+$llnoffset][$vak][$p],0);
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
			      $stcaln[$sx][$p] += round($stres[$sx+$llnoffset][$vak][$p],0);
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
		  		  
		  echo("<TR><TH class=headleft COLSPAN=2>Advies</TH>");
		  for($sx = 1; $sx <= $scnt; $sx++)
		  {
		    for($p=0;$p<4;$p++)
			{
			  $pi = ($p + 1) % 4;
			  echo("<TD>");
			  if($stcalp[$sx][$pi] >= 88 && $stcalo[$sx][$pi] == 0 && $stcaln[$sx][$pi] >= 40)
			    echo("HV");
			  else if(($stcalp[$sx][$pi] >= 88 && $stcalo[$sx][$pi] == 0 && $stcaln[$sx][$pi] >= 39) ||
			          ($stcalp[$sx][$pi] == 87 && $stcalo[$sx][$pi] == 0 && $stcaln[$sx][$pi] >= 40) ||
			          ($stcalp[$sx][$pi] >= 88 && $stcalo[$sx][$pi] == 1 && $stcalc[$sx][$pi] == 1 && $stcaln[$sx][$pi] >= 40))
			    echo("BHV");
			  else if($stcalp[$sx][$pi] >= 72 && $stcalo[$sx][$pi] <= 3 && $stcalc[$sx][$pi] <= 4)
			    echo("MV");
			  else if($stcalp[$sx][$pi] >= 66 && $stcalo[$sx][$pi] <= 4 && $stcalc[$sx][$pi] <= 6)
			    echo("BME");
			  else if($stcalp[$sx][$pi] < 66 || $stcalo[$sx][$pi] >= 4)
			    echo("EPB");
			  else
			    echo("?");
			  echo("</TD>");
			}
		  }
		  echo("</TR>");
*/	
		  // Get absence info and display it.
		  $sx=1;
		  $absdq = "SELECT SUM(IF(date >= '". $pdata[1]['sdate']. "' AND date <= '". $pdata[1]['edate']. "',1,0)) AS p1abs,
						   SUM(IF(date >= '". $pdata[2]['sdate']. "' AND date <= '". $pdata[2]['edate']. "',1,0)) AS p2abs,
						   SUM(IF(date >= '". $pdata[3]['sdate']. "' AND date <= '". $pdata[3]['edate']. "',1,0)) AS p3abs
						   FROM absence WHERE sid=". $students['sid'][$llnoffset+$sx]. " AND aid IN (". implode(",",$telaatreden).") 
						   GROUP BY sid";
		  $absqr = SA_loadquery($absdq);
		  echo("<TR><TH class=cathead COLSPAN=2>Te laat</TH>");
		  if(isset($absqr['p1abs']))
		  {
		    $abssum = 0;
		    for($p=1; $p <= 4; $p++)
			{
			  if($p < 4)
			  {
			    if($pdata[$p]["status"] == "open")
				  echo("<TD>&nbsp;</TD>");
				else
			      echo("<TD class=rapres>". $absqr["p". $p. "abs"][1]. "</TD>");
				$abssum += $absqr["p". $p. "abs"][1];
			  }
			  else
			  {
			    if($pdata[3]["status"] == "open")
				  echo("<TD class=rapOres>&nbsp;</TD>");
				else
			      echo("<TD class=rapOres>". $abssum. "</TD>");
			  }
			}
		  }
		  else
		    echo("<TD COLSPAN=4><CENTER>-</TD>");
		  echo("</TR>");

		  $absdq = "SELECT SUM(IF(date >= '". $pdata[1]['sdate']. "' AND date <= '". $pdata[1]['edate']. "',1,0)) AS p1abs,
						   SUM(IF(date >= '". $pdata[2]['sdate']. "' AND date <= '". $pdata[2]['edate']. "',1,0)) AS p2abs,
						   SUM(IF(date >= '". $pdata[3]['sdate']. "' AND date <= '". $pdata[3]['edate']. "',1,0)) AS p3abs
						   FROM absence WHERE sid=". $students['sid'][$llnoffset+$sx]. " AND aid IN (". implode(",",$afwezigreden).") 
						   GROUP BY sid";
		  $absqr = SA_loadquery($absdq);
		  echo("<TR><TH class=cathead COLSPAN=2>Absent</TH>");
		  if(isset($absqr['p1abs']))
		  {
		    $abssum = 0;
		    for($p=1; $p <= 4; $p++)
			{
			  if($p < 4)
			  {
			    if($pdata[$p]["status"] == "open")
				  echo("<TD>&nbsp;</TD>");
				else
			      echo("<TD class=rapres>". $absqr["p". $p. "abs"][1]. "</TD>");
				$abssum += $absqr["p". $p. "abs"][1];
			  }
			  else
			  {
			    if($pdata[3]["status"] == "open")
				  echo("<TD class=rapOres>&nbsp;</TD>");
				else
			      echo("<TD class=rapOres>". $abssum. "</TD>");
			  }
			}
		  }
		  else
		    echo("<TD COLSPAN=4><CENTER>-</TD>");
		  echo("</TR>");

		  
		  echo("</TABLE>");
		  echo("<P class=resultrap>");
		  echo("Resultaat Rapport 1: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;_________________<BR>");
		  echo("Resultaat Rapport 2: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;_________________</P>");
		  echo("<DIV class=meanings>");
		  echo("<SPAN class=meaninghead>Betekenis van de cijfers</SPAN><BR>");
		  echo("<SPAN class=mdigit>10</SPAN><SPAN class=meaning>Uitmuntend</SPAN><SPAN class=mdigit>5</SPAN><SPAN class=meaning>Bijna voldoende</SPAN><BR>");
		  echo("<SPAN class=mdigit>9</SPAN><SPAN class=meaning>Zeer goed</SPAN><SPAN class=mdigit>4</SPAN><SPAN class=meaning>Onvoldoende</SPAN><BR>");
		  echo("<SPAN class=mdigit>8</SPAN><SPAN class=meaning>Goed</SPAN><SPAN class=mdigit>3</SPAN><SPAN class=meaning>Zeer onvoldoende</SPAN><BR>");
		  echo("<SPAN class=mdigit>7</SPAN><SPAN class=meaning>Ruim voldoende</SPAN><SPAN class=mdigit>2</SPAN><SPAN class=meaning>Slecht</SPAN><BR>");
		  echo("<SPAN class=mdigit>6</SPAN><SPAN class=meaning>Voldoende</SPAN><SPAN class=mdigit>1</SPAN><SPAN class=meaning>Zeer slecht</SPAN><BR>");
          
		  echo("</DIV>");		  
		  echo("<P class=resultrap>Datum: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;". $_POST['rapportdatum']. "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u></P>");
		  echo("<P class=signdir>Handtek. Directeur</P>");
		  echo("</DIV><DIV class=rightpage><BR><BR><BR>");
		  echo("<P class=rapheader>Rapport 1</P>");
		  echo("<DIV class=rapblock>Opmerking:<BR>");
			if(isset($rapopms[$students['sid'][$llnoffset+1]][1]))
				echo("<DIV class=remarks>". $rapopms[$students['sid'][$llnoffset+1]][1]."</DIV>");
			else
				echo("<DIV class=remarks>&nbsp;<HR>&nbsp;<HR></DIV>");
		  echo("<SPAN class=signlabel>Handtek. Mentor:</SPAN>______________________________<BR>");
		  echo("<SPAN class=signlabel>Handtek. Ouder/ voogd:</SPAN>______________________________");
		  echo("<BR>&nbsp;</DIV>");
		  echo("<P class=rapheader>Rapport 2</P>");
		  echo("<DIV class=rapblock>Opmerking:<BR>");
			if(isset($rapopms[$students['sid'][$llnoffset+1]][2]))
				echo("<DIV class=remarks>". $rapopms[$students['sid'][$llnoffset+1]][2]."</DIV>");
			else
				echo("<DIV class=remarks>&nbsp;<HR>&nbsp;<HR></DIV>");
		  echo("<SPAN class=signlabel>Handtek. Mentor:</SPAN>______________________________<BR>");
		  echo("<SPAN class=signlabel>Handtek. Ouder/ voogd:</SPAN>______________________________");
		  echo("<BR>&nbsp;</DIV>");
		  echo("<P class=rapheader>Overgangsrapport</P>");
		  echo("<DIV class=rapblock>Opmerking:<BR>");
			if(isset($rapopms[$students['sid'][$llnoffset+1]][3]))
				echo("<DIV class=remarks>". $rapopms[$students['sid'][$llnoffset+1]][3]."</DIV>");
			else
				echo("<DIV class=remarks>&nbsp;<HR>&nbsp;<HR></DIV>");
		  echo("<DIV class=leftresult>");
		  echo("o Over of verwijzing naar:<BR>");
		  echo("<SPAN class=referitem>o &nbsp;EPB</SPAN><BR>");
		  if(substr($groups['groupname'][$gix],0,1) == 1)
		    echo("<SPAN class=referitem>o &nbsp;Ciclo Basico 2</SPAN><BR>");
		  else
		    echo("<SPAN class=referitem>o &nbsp;Ciclo Avansa</SPAN><BR>");
		  echo("<SPAN class=referitem>o &nbsp;MAVO</SPAN><BR>");
		  echo("<SPAN class=referitem>o &nbsp;HAVO</SPAN>");
		  echo("</DIV><DIV class=rightresult>");
		  echo("o Niet Over:<BR>");
		  echo("<SPAN class=referitem>o &nbsp;Mag doubleren</SPAN><BR>");
		  echo("<SPAN class=referitem>o &nbsp;Moet school verlaten</SPAN></DIV>");
		  echo("<DIV class=clearfix><BR></DIV><SPAN class=signlabel>Handtek. Mentor:</SPAN>______________________________");
		  echo("<BR>&nbsp;</DIV>");
		  echo("</DIV><P class=pagebreak>&nbsp;</P>");
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
