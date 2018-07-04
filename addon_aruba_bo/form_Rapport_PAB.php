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
  
  // Operational definitions
  $afwezigreden = array(1,2,3,4,5,);
  $telaatreden = array(6,7,8,9,10,11,12,13,14,15,16,17,18,19);
  $groepfilter = $_SESSION['CurrentGroup'];
  $llnperpage = 1;
	
	if(date("n") < 4)
		$repper = 2;
	else if (date("n") > 7)
		$repper = 1;
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
  
  // Get a list of groups
  $groups = SA_loadquery("SELECT * FROM sgroup LEFT JOIN teacher ON(tid_mentor=tid) WHERE active=1 AND groupname LIKE '". $groepfilter. "' ORDER BY groupname");
	
	// Get the remarks
	$remarksqr = SA_loadquery("SELECT sid,opmtext,period FROM bo_opmrap_data WHERE year='". $schoolyear. "'");
	if(isset($remarksqr))
		foreach($remarksqr['sid'] AS $rmix => $rmsid)
			$remarks[$rmsid][$remarksqr['period'][$rmix]] = $remarksqr['opmtext'][$rmix];
			
  // Get a list of last test dates for periods
  //$perends = SA_loadquery("SELECT period,CEIL(date) AS edate FROM testdef GROUP BY period ORDER BY period");

	// Get the behaviuor ascpects
	$behaveqr = SA_loadquery("SELECT sid,aspect,xstatus,period FROM bo_houding_data WHERE year='". $schoolyear. "'");
	if(isset($behaveqr['sid']))
		foreach($behaveqr['sid'] AS $bix => $bsid)
			if($behaveqr['xstatus'][$bix] == 1)
				$behave[$bsid][$behaveqr['aspect'][$bix]][$behaveqr['period'][$bix]] = "F";
			else if($behaveqr['xstatus'][$bix] == 2)
				$behave[$bsid][$behaveqr['aspect'][$bix]][$behaveqr['period'][$bix]] = "E";
			else if($behaveqr['xstatus'][$bix] == 3)
				$behave[$bsid][$behaveqr['aspect'][$bix]][$behaveqr['period'][$bix]] = "D";
			else if($behaveqr['xstatus'][$bix] == 4)
				$behave[$bsid][$behaveqr['aspect'][$bix]][$behaveqr['period'][$bix]] = "C";
			else if($behaveqr['xstatus'][$bix] == 5)
				$behave[$bsid][$behaveqr['aspect'][$bix]][$behaveqr['period'][$bix]] = "B";
			else if($behaveqr['xstatus'][$bix] == 6)
				$behave[$bsid][$behaveqr['aspect'][$bix]][$behaveqr['period'][$bix]] = "A";
  
  if(isset($groups))
  {
    // First part of the page
    echo("<html><head><title>Rapport</title>");
		echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
		echo("</head><body link=blue vlink=blue>");
    echo '<LINK rel="stylesheet" type="text/css" href="style_Rapport_PAB.css" title="style1">';

    foreach($groups['gid'] AS $gix => $gid)
	{
		$mentorname = $groups['firstname'][$gix]. " ". $groups['lastname'][$gix];
	  // Create a list of subject details
	  $sdquery = "SELECT type,fullname,shortname,'' AS data FROM subject UNION SELECT type, fullname, shortname, data FROM class LEFT JOIN subject USING(mid) LEFT JOIN ". $teachercode. " USING(tid) WHERE gid=". $gid;
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
	  $perdata = SA_loadquery("SELECT * FROM period");
	  foreach($perdata['id'] AS $pix => $pid)
	  {
	    $pdata[$pid]["sdate"] = $perdata["startdate"][$pix];
	    $pdata[$pid]["edate"] = $perdata["enddate"][$pix];
	  }

	  if(isset($students))
	  {
	    $llnoffset = 0;
			while ($llnoffset < sizeof($students['sid']))
			{
				$scnt = $llnperpage;
				if(sizeof($students['sid']) - $llnoffset < $scnt)
					$scnt = sizeof($students['sid']) - $llnoffset;
				// Show back page
				echo("<DIV class=leftpage>");
				echo("<TABLE class=opmblock>");
				for($per=1; $per < 4; $per++)
				{
					echo("<TR class=opmsep><TD colspan=3>Opmerkingen rapport ". $per. "</TD></TR>");
					echo("<TR><TD colspan=3 class=opmcontent>");
					if(isset($remarks[$students['sid'][$llnoffset+1]][$per]))
						echo($remarks[$students['sid'][$llnoffset+1]][$per]);
					else
						echo("&nbsp;");
					echo("</TD></TR>");
					echo("<TR><TD class=spacerrow colspan=3>&nbsp;</td></tr>");
				}
				echo("<TR class=meaningtop><TD colspan=3><B><U>Betekenis cijfers/letters :</td></tr>");
				echo("<TR class=meaningbot><TD>10 - Uitmuntend<BR>&nbsp;9 - Zeer goed<BR>&nbsp;8 - Goed<BR>&nbsp;7 - Ruim voldoende<BR>&nbsp;6 - Voldoende</td>");
				echo("<TD>5 - Bijna voldoende<BR>4 - Onvoldoende<BR>3 - Zeer onvoldoende<BR>2 - Slecht<BR>1 - Zeer slecht</td>");
				echo("<TD>A - Zeer goed<BR>B - Goed<BR>C - Ruim voldoende<BR>D - Voldoende<BR>E - Onvoldoende<BR>F - Slecht</td></tr>");
				echo("</TABLE>");
				echo("</DIV>");
				// Show frontpage
				echo("<DIV class=frontpage><P class=raptitle>Rapport</p><img src=schoollogo.png class=frontlogo width=30%><P class=schoolnamefront>Openbare Basisschool<BR>Oranjestad, Jupiterstraat 23<BR>Tel: (297) 588-5890 - Fax (297) 582-2012<P class=rapdblock><SPAN class=raplabel>Rapport&nbsp;van:</SPAN>");
				echo("<SPAN class=rapname>". $students['lastname'][1+$llnoffset]. ", ". $students['firstname'][1+$llnoffset]. "</SPAN>");
				echo("<BR><SPAN class=raplabel>Klas :</SPAN>");
				echo("<SPAN class=rapdata>". $groups['groupname'][$gix]. "</SPAN>");
				echo("<SPAN class=raplabel>Schooljaar :</SPAN>");
				echo("<SPAN class=rapdata>". $schoolyear. "</SPAN>");
				echo("</DIV><P class=pagebreak>&nbsp;</P>");

				// On to the page with data
				echo("<DIV class=leftpage>");
				echo("<SPAN class=raplabel2>Rapport van : </span><SPAN class=rapname2>". $students['lastname'][1+$llnoffset]. " ". $students['firstname'][1+$llnoffset]. "</SPAN>");
				echo("<TABLE class=cijferlijst>");
				echo("<TR><td class=spacerrow>&nbsp;</td><TD class=raphead>&nbsp;</td>");
				echo("<td class=raphead>1</td>");
				echo("<td class=raphead>2</td>");
				echo("<td class=raphead>3</td>");
				echo("<td class=raphead>4</td></tr>");
				echo("<TR><TD class=spacerrow colspan=6>&nbsp;</td></tr>");
				
				// Get the student results for students in set
				for($sx = 1; $sx <= $scnt; $sx++)
				{
					$sres = SA_loadquery("SELECT period, result, shortname FROM gradestore LEFT JOIN subject USING(mid) WHERE sid=". $students['sid'][$llnoffset+$sx]. " AND year=\"". $schoolyear. "\" ");
					if(isset($sres))
						foreach($sres['period'] AS $rix => $perid)
							$stres[$llnoffset+$sx][$sres['shortname'][$rix]][$perid] = $sres['result'][$rix];
					unset($sres);
				}
				
				// Get the student absence in set
				unset($stlate);
				unset($stabs);
				for($sx = 1; $sx <= $scnt; $sx++)
				{
					$stabs[$llnoffset+$sx][0] = 0;
					$stlate[$llnoffset+$sx][0] = 0;
					$sres = SA_loadquery("SELECT SUM(IF(date >= '". $pdata[1]['sdate']. "' AND date <= '". $pdata[1]['edate']. "' AND (acid=1 OR acid=4 OR acid=5),1,0)) AS afw1, SUM(IF(date >= '". $pdata[2]['sdate']. "' AND date <= '". $pdata[2]['edate']. "' AND (acid=1 OR acid=4 OR acid=5),1,0)) AS afw2, SUM(IF(date >= '". $pdata[3]['sdate']. "' AND date <= '". $pdata[3]['edate']. "' AND (acid=1 OR acid=4 OR acid=5),1,0)) AS afw3, SUM(IF(date >= '". $pdata[1]['sdate']. "' AND date <= '". $pdata[1]['edate']. "' AND acid=2,1,0)) AS late1, SUM(IF(date >= '". $pdata[2]['sdate']. "' AND date <= '". $pdata[2]['edate']. "' AND acid=2,1,0)) AS late2, SUM(IF(date >= '". $pdata[3]['sdate']. "' AND date <= '". $pdata[3]['edate']. "' AND acid=2,1,0)) AS late3 FROM absence LEFT JOIN absencereasons USING(aid) WHERE sid=". $students['sid'][$llnoffset+$sx]);
					if(isset($sres))
					{
						if($sres['afw1'][1] > 0) { $stabs[$llnoffset+$sx][1] = $sres['afw1'][1]; $stabs[$llnoffset+$sx][0] += $sres['afw1'][1];}
						if($sres['afw2'][1] > 0) { $stabs[$llnoffset+$sx][2] = $sres['afw2'][1]; $stabs[$llnoffset+$sx][0] += $sres['afw2'][1];}
						if($sres['afw3'][1] > 0) { $stabs[$llnoffset+$sx][3] = $sres['afw3'][1]; $stabs[$llnoffset+$sx][0] += $sres['afw3'][1];}
						if($sres['late1'][1] > 0) { $stlate[$llnoffset+$sx][1] = $sres['late1'][1]; $stlate[$llnoffset+$sx][0] += $sres['late1'][1];}
						if($sres['late2'][1] > 0) { $stlate[$llnoffset+$sx][2] = $sres['late2'][1]; $stlate[$llnoffset+$sx][0] += $sres['late2'][1];}
						if($sres['late3'][1] > 0) { $stlate[$llnoffset+$sx][3] = $sres['late3'][1]; $stlate[$llnoffset+$sx][0] += $sres['late3'][1];}
					}
					unset($sres);
				}
				
				// Behavioural aspects
				echo("<TR><TD class=vakcatcel rowspan=4><SPAN class=vakcat>Gedrag</span></td>");
				echo("<TD class=subjectcol>Contact met leerkracht</TD>");
				show_behave($students['sid'][$llnoffset+1],'ContactLK');
				echo("</TR><TR><TD class=subjectcol>Contact met leerling</TD>");
				show_behave($students['sid'][$llnoffset+1],'ContactLL');
				echo("</TR><TR><TD class=subjectcol>Zelfvertrouwen</TD>");
				show_behave($students['sid'][$llnoffset+1],'Zelfvertrouwen');
				echo("</TR><TR><TD class=subjectcol>Motivatie / Ijver</TD>");
				show_behave($students['sid'][$llnoffset+1],'Motijver');
				echo("</TR>");		  
				echo("<TR><TD class=spacerrow colspan=6>&nbsp;</td></tr>");

				echo("<TR><TD class=vakcatcel rowspan=7><SPAN class=vakcatl>Leer&#8209;werkhouding</span></td>");
				echo("<TD class=subjectcol>Nauwkeurigheid</TD>");
				show_behave($students['sid'][$llnoffset+1],'Nauwkeurig');
				echo("</TR><TR><TD class=subjectcol>Doorzettingsvermogen</TD>");
				show_behave($students['sid'][$llnoffset+1],'Doorzet');
				echo("</TR><TR><TD class=subjectcol>Zelfstandigheid</TD>");
				show_behave($students['sid'][$llnoffset+1],'Zelfst');
				echo("</TR><TR><TD class=subjectcol>Werktempo</TD>");
				show_behave($students['sid'][$llnoffset+1],'Werktempo');
				echo("</TR><TR><TD class=subjectcol>Werkverzorging</TD>");
				show_behave($students['sid'][$llnoffset+1],'Werkverzorging');
				echo("</TR><TR><TD class=subjectcol>Concentratie</TD>");
				show_behave($students['sid'][$llnoffset+1],'Concentratie');
				echo("</TR><TR><TD class=subjectcol>Huiswerk</TD>");
				show_behave($students['sid'][$llnoffset+1],'Huiswerk');
				echo("</TR>");		  
				echo("<TR><TD class=spacerrow colspan=6>&nbsp;</td></tr>");

				// Subject results
				echo("<TR><TD class=vakcatcel ROWSPAN=4><SPAN class=vakcat>Lezen</span></td>");
				echo("<td class=subjectcol>Technisch</td>");
				show_result($llnoffset+1,"Tech. Lezen");
				echo("</tr><TR><td class=subjectcol>Begrijpend</td>");
				show_result($llnoffset+1,"Begr. Lezen");
				echo("</tr><TR><td class=subjectcol><B>Gemiddeld</b></td>");
				show_result($llnoffset+1,"Lezen",true);
				echo("</tr><TR><td class=subjectcol>Avi-niveau</td>");
				show_result($llnoffset+1,"Avi-lezen",false,true);
				echo("</tr><TR>");

				echo("<TR><TD class=spacerrow colspan=6>&nbsp;</td></tr>");
				echo("<TR><TD class=vakcatcel ROWSPAN=1><SPAN class=vakcat>&nbsp;</span></td>");
				echo("<td class=subjectcol>Schrijven</td>");
				show_result($llnoffset+1,"Schrijven");
				echo("</tr><TR>");

				echo("<TR><TD class=spacerrow colspan=6>&nbsp;</td></tr>");
				echo("<TR><TD class=vakcatcel ROWSPAN=6><SPAN class=vakcat>Rekenen</span></td>");
				echo("<td class=subjectcol>Getalbegrip</td>");
				show_result($llnoffset+1,"GB");
				echo("</tr><TR><td class=subjectcol>Basisvaardigheden</td>");
				show_result($llnoffset+1,"Basis Vaard");
				echo("</tr><TR><td class=subjectcol>Meten / Meetkunde / Tijd</td>");
				show_result($llnoffset+1,"MMT");
				echo("</tr><TR><td class=subjectcol>Tabellen en grafieken</td>");
				show_result($llnoffset+1,"TG");
				echo("</tr><TR><td class=subjectcol><B>Gemiddeld</b></td>");
				show_result($llnoffset+1,"Rekenen",true);
				echo("</tr><TR><td class=subjectcol>Inzicht</td>");
				show_result($llnoffset+1,"Inzicht rek",false,true);
				echo("</tr><TR>");

				echo("<TR><TD class=spacerrow colspan=6>&nbsp;</td></tr>");
				echo("<TR><TD class=vakcatcel ROWSPAN=6><SPAN class=vakcatl>Nederlandse&nbsp;taal</span></td>");
				echo("<td class=subjectcol>Dictee</td>");
				show_result($llnoffset+1,"Dictee");
				echo("</tr><TR><td class=subjectcol>Taaloefeningen</td>");
				show_result($llnoffset+1,"Taal oefeningen");
				echo("</tr><TR><td class=subjectcol>Stellen</td>");
				show_result($llnoffset+1,"Stellen");
				echo("</tr><TR><td class=subjectcol><B>Gemiddeld</b></td>");
				show_result($llnoffset+1,"Nederlands",true);
				echo("</tr><TR><td class=subjectcol>Spreken</td>");
				show_result($llnoffset+1,"Spreken",false,true);
				echo("</tr><TR><td class=subjectcol>Luisteren</td>");
				show_result($llnoffset+1,"Luisteren",false,true);
				echo("</tr><TR>");

				echo("</TABLE>");
				echo("</DIV><DIV class=rightpage>");
				echo("<SPAN class=raplabel2>Klas : </span><SPAN class=rapdata>". $_SESSION['CurrentGroup']. "</SPAN><SPAN class=raplabel2>Schooljaar : </span><SPAN class=rapdata>".  $schoolyear. "</SPAN>");
				echo("<TABLE class=cijferlijst>");
				echo("<TR><td class=spacerrow>&nbsp;</td><TD class=raphead>&nbsp;</td>");
				echo("<td class=raphead>1</td>");
				echo("<td class=raphead>2</td>");
				echo("<td class=raphead>3</td>");
				echo("<td class=raphead>4</td></tr>");
				echo("<TR><TD class=spacerrow colspan=6>&nbsp;</td></tr>");

				// Subject results
				echo("<TR><TD class=vakcatcel ROWSPAN=3><SPAN class=vakcat>Zaakvakken</span></td>");
				echo("<td class=subjectcol>Aardrijkskunde</td>");
				show_result($llnoffset+1,"Aardrijkskunde",true);
				echo("</tr><TR><td class=subjectcol>Geschiedenis</td>");
				show_result($llnoffset+1,"Geschiedenis",true);
				echo("</tr><TR><td class=subjectcol>Kennis der Natuur</td>");
				show_result($llnoffset+1,"KdN",true);
				echo("</tr><TR>");

				echo("<TR><TD class=spacerrow colspan=6>&nbsp;</td></tr>");
				echo("<TR><TD class=vakcatcel ROWSPAN=4><SPAN class=vakcat>Expressie</span></td>");
				echo("<td class=subjectcol>Muzikale vorming</td>");
				show_result($llnoffset+1,"Muziek",false,false,true);
				echo("</tr><TR><td class=subjectcol>Handvaardigheid</td>");
				show_result($llnoffset+1,"Handv.",false,false,true);
				echo("</tr><TR><td class=subjectcol>Tekenen</td>");
				show_result($llnoffset+1,"Tekenen",false,false,true);
				echo("</tr><TR><td class=subjectcol>Lichamelijke opvoeding</td>");
				show_result($llnoffset+1,"Lich. Opvoeding",false,false,true);				
				echo("</tr><TR>");

				if(substr($_SESSION['CurrentGroup'],0,1) == '3')
				{
					echo("<TR><TD class=spacerrow colspan=6>&nbsp;</td></tr>");
					echo("<TR><TD class=vakcatcel ROWSPAN=1><SPAN class=vakcat>&nbsp;</span></td>");
					echo("<td class=subjectcol>Zwemniveau</td>");
					show_result($llnoffset+1,"Zwemniveau",false,true);
					echo("</tr><TR>");
				}

				echo("<TR><TD class=spacerrow colspan=6>&nbsp;</td></tr>");
				echo("<TR><TD class=vakcatcel ROWSPAN=1><SPAN class=vakcat>&nbsp;</span></td>");
				echo("<td class=subjectcol>Spreekbeurt / Boekbespreking / Project</td>");
				show_result($llnoffset+1,"SpBoPr");
				echo("</tr><TR>");

				echo("<TR><TD class=spacerrow colspan=6>&nbsp;</td></tr>");
				echo("<TR><TD class=vakcatcel ROWSPAN=3><SPAN class=vakcat>&nbsp;</span></td>");
				echo("<td class=subjectcol>Engelse taal</td>");
				show_result($llnoffset+1,"Engels");
				echo("</tr><TR><td class=subjectcol>Spaanse taal</td>");
				show_result($llnoffset+1,"Spaans");
				echo("</tr><TR><td class=subjectcol>Papiamentse taal</td>");
				show_result($llnoffset+1,"Papiamento");
				echo("</tr><TR>");

				echo("<TR><TD class=spacerrow colspan=6>&nbsp;</td></tr>");
				echo("<TR><TD class=vakcatcel ROWSPAN=3><SPAN class=vakcat>&nbsp;</span></td>");
				echo("<td class=subjectcol>Verkeer</td>");
				show_result($llnoffset+1,"Verkeer");
				echo("</tr><TR><td class=subjectcol>Maatschappijleer</td>");
				show_result($llnoffset+1,"Maats.L");
				echo("</tr><TR>");

				echo("<TR><TD class=spacerrow colspan=6>&nbsp;</td></tr>");
				echo("<TR><TD class=spacerrow colspan=6>&nbsp;</td></tr>");
				echo("<TR><TD class=vakcatcel ROWSPAN=2><SPAN class=vakcat>&nbsp;</span></td>");
				echo("<td class=subjectcol>Te laat</td>");
				echo("<td class=resultcol>". (isset($stlate[$llnoffset+1][1]) ? $stlate[$llnoffset+1][1] : "&nbsp;"). "</td>");
				echo("<td class=resultcol>". (isset($stlate[$llnoffset+1][2]) ? $stlate[$llnoffset+1][2] : "&nbsp;"). "</td>");
				echo("<td class=resultcol>". (isset($stlate[$llnoffset+1][3]) ? $stlate[$llnoffset+1][3] : "&nbsp;"). "</td></tr>");
				echo("<TR><td class=subjectcol>Verzuim</td>");
				echo("<td class=resultcol>". (isset($stabs[$llnoffset+1][1]) ? $stabs[$llnoffset+1][1] : "&nbsp;"). "</td>");
				echo("<td class=resultcol>". (isset($stabs[$llnoffset+1][2]) ? $stabs[$llnoffset+1][2] : "&nbsp;"). "</td>");
				echo("<td class=resultcol>". (isset($stabs[$llnoffset+1][3]) ? $stabs[$llnoffset+1][3] : "&nbsp;"). "</td></tr>");
				
				echo("<TR><TD class=spacerrow colspan=6>&nbsp;</td></tr>");
				echo("<TR><TD class=spacerrow ROWSPAN=4>&nbsp;</td><TD class=spacerrow colspan=5>Handtekening :</td></tr>");
				echo("<TR><TD class=signname>Hoofd der school</td><td colspan=4 class=resultcol>&nbsp;</td></tr>");
				echo("<TR><TD class=signname>Leerkracht</td><td colspan=4 class=resultcol>&nbsp;</td></tr>");
				echo("<TR><TD class=signname>Ouder / Verzorger</td><td colspan=4 class=resultcol>&nbsp;</td></tr>");
					
				echo("<TR><TD class=spacerrow colspan=6>&nbsp;</td></tr>");
				echo("<TR><TD class=spacerrow ROWSPAN=3>&nbsp;</td><TD class=spacerrow>O Bevorderd naar klas</td><td class=spacerrow>&nbsp</td><td class=undlin>&nbsp;</td></tr>");
				echo("<TR><TD class=spacerrow>O Over wegens leeftijd naar klas</td><td class=spacerrow>&nbsp</td><td class=undlin>&nbsp;</td></tr>");
				echo("<TR><TD class=spacerrow>O Niet bevorderd</td></tr>");
				
				echo("</table>");
				echo("</DIV><P class=pagebreak>&nbsp;</P>");
				$llnoffset += $llnperpage;
				unset($stabs);
				unset($stlate);
			} // End while for subgroups of students
	  } // End if student for the group
	
	  unset($stres);
	} // End for each group
  } // End if groups defined
      
  echo("</html>");
  
  function colored($res)
  {
    $res2 = str_replace(',','.',$res);
		if($res2 < 3.0)
			$res="3,0";
		if($res2 < 5.5)
			return("<SPAN class=redcolor>". $res. "</SPAN>");
		else
			return($res);
  }
	
	
	function show_behave($sid, $aspect)
	{
		global $behave,$repper;
		for($per = 1; $per < 4; $per++)
		echo("<TD class=resultcol>". ((isset($behave[$sid][$aspect][$per]) && $per <= $repper) ? $behave[$sid][$aspect][$per] : '&nbsp;'). "</TD>");
	}
	
	function show_result($sid,$subj,$per4 = false,$letter = false,$convertletter = false)
	{
		global $stres, $repper;
		for($per=1; $per < 4; $per++)
		{
			echo("<TD class=resultcol>");
			if($per4)
				echo("<B>");
			if(isset($stres[$sid][$subj][$per]) && $per <= $repper)
			{
				show_res_per($stres[$sid][$subj][$per],$letter,$convertletter);
			}
			else
				echo("&nbsp;");
			if($per4)
				echo("</B>");
			echo("</td>");
		}
		if($per4)
		{	
			echo("<TD class=resultcol><B>");
				if(isset($stres[$sid][$subj][0]) && $repper == 3)
					show_res_per($stres[$sid][$subj][0],$letter,$convertletter);
				else
					echo("&nbsp;");
			echo("</B></td>");
		}
		
	}
	
	function show_res_per($res, $letter, $convertletter)
	{
		if($letter || !($res > 0.9) || $res == "-")
			if($res == "0")
				echo("-");
			else
				echo($res);
		else if ($convertletter)
		{
			if($res < 4.5)
				echo("F");
			else if($res < 5.5)
				echo("E");
			else if($res < 6.5)
				echo("D");
			else if($res < 7.5)
				echo("C");
			else if($res < 9.0)
				echo("B");
			else
				echo("A");
		}
		else
		{
			echo(number_format($res,1,",","."));
		}
	}
?>