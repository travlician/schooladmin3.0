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
    echo '<LINK rel="stylesheet" type="text/css" href="style_Rapport_GZ.css" title="style1">';

		echo("</DIV><P class=pagebreak>&nbsp;</P>");
		echo("</DIV><P class=pagebreak>&nbsp;</P>");
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
				echo("<P class=opmtitle>Opmerkingen</p>");
				echo("<TABLE class=opmblock>");
				for($per=1; $per < 4; $per++)
				{
					echo("<TR class=opmsep><TD colspan=2>". $per. "e rapport :</TD></TR>");
					echo("<TR><TD colspan=2 class=opmcontent>");
					if(isset($remarks[$students['sid'][$llnoffset+1]][$per]))
						echo($remarks[$students['sid'][$llnoffset+1]][$per]);
					else
						echo("&nbsp;");
					echo("</TD></TR>");
					echo("<TR><TD class=spacerrow colspan=2>&nbsp;</td></tr>");
				}
				echo("<TR class=meaningtop><TD colspan=2><B><U>Betekenis der letters en cijfers :</td></tr>");
				echo("<TR class=meaningbot><TD>g = goed<BR>v = voldoende<BR>m = matig<BR>o = onvoldoende</td>");
				echo("<TD>10&nbsp;&nbsp;= uitmuntend<BR>&nbsp;9&nbsp;&nbsp;= zeer goed<BR>&nbsp;8&nbsp;&nbsp;= goed<BR>&nbsp;7&nbsp;&nbsp;= ruim voldoende<BR>&nbsp;6&nbsp;&nbsp;= voldoende<BR>&nbsp;5&nbsp;&nbsp;= bijna voldoende<BR>&nbsp;4&nbsp;&nbsp;= onvoldoende<BR>&nbsp;4-&nbsp;= slecht</td></tr>");
				echo("</TABLE>");
				echo("</DIV>");
				// Show frontpage
				echo("<DIV class=frontpage><P class=raptitle>RAPPORT VAN</p><P class=rapname>". firstonly($students['firstname'][1+$llnoffset]). " ". $students['lastname'][1+$llnoffset]. "</p><img src=schoollogo.png class=frontlogo width=40%><P class=schoolnamefront><B>Graf von Zinzendorfschool</b><BR>
					Bernhardstraat 259<BR>
					P.O. BOX 2131<BR>
					San Nicolaas - ARUBA<BR><BR>
					telefoon: 584-5551<BR>
					e-mail: gvzbasisschool@gmail.com<BR><BR>
					<B>STICHTING VOOR PROTESTANTS CHRISTELIJK<BR>
					ONDERWIJS OP ARUBA</b></p>");
				echo("</DIV><P class=pagebreak>&nbsp;</P>");

				// On to the page with data
				echo("<DIV class=leftpage>");
				echo("<TABLE class=cijferlijst>");
				echo("<TR><TD class=raphead>&nbsp;</td>");
				echo("<td class=raphead>R1</td>");
				echo("<td class=raphead>R2</td>");
				echo("<td class=raphead>R3</td></tr>");
				echo("<TR><TD class=spacerrow colspan=4>&nbsp;</td></tr>");
				
				// Get the student results for students in set
				for($sx = 1; $sx <= $scnt; $sx++)
				{
					$sres = SA_loadquery("SELECT period, result, shortname FROM gradestore LEFT JOIN subject USING(mid) WHERE sid=". $students['sid'][$llnoffset+$sx]. " AND year=\"". $schoolyear. "\" ". ($groups['groupname'][$gix] == "BO-1" ? "AND period <> 1" : ""));
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
					$sres = SA_loadquery("SELECT SUM(IF(date >= '". $pdata[1]['sdate']. "' AND date <= '". $pdata[1]['edate']. "' AND (aid<>32),1,0)) AS afw1, SUM(IF(date >= '". $pdata[2]['sdate']. "' AND date <= '". $pdata[2]['edate']. "' AND (aid<>32),1,0)) AS afw2, SUM(IF(date >= '". $pdata[3]['sdate']. "' AND date <= '". $pdata[3]['edate']. "' AND (aid<>32),1,0)) AS afw3, SUM(IF(date >= '". $pdata[1]['sdate']. "' AND date <= '". $pdata[1]['edate']. "' AND aid=32,1,0)) AS late1, SUM(IF(date >= '". $pdata[2]['sdate']. "' AND date <= '". $pdata[2]['edate']. "' AND aid=32,1,0)) AS late2, SUM(IF(date >= '". $pdata[3]['sdate']. "' AND date <= '". $pdata[3]['edate']. "' AND aid=32,1,0)) AS late3 FROM absence LEFT JOIN absencereasons USING(aid) WHERE sid=". $students['sid'][$llnoffset+$sx]);
					if(isset($sres))
					{
						if($sres['afw1'][1] > 0) { $stabs[$llnoffset+$sx][1] = $sres['afw1'][1]; $stabs[$llnoffset+$sx][0] += $sres['afw1'][1];}
						else $stabs[$llnoffset+$sx][1]=0;
						if($sres['afw2'][1] > 0 && $repper > 1) { $stabs[$llnoffset+$sx][2] = $sres['afw2'][1]; $stabs[$llnoffset+$sx][0] += $sres['afw2'][1];}
						else if($repper > 1) $stabs[$llnoffset+$sx][2]=0;
						if($sres['afw3'][1] > 0 && $repper > 2) { $stabs[$llnoffset+$sx][3] = $sres['afw3'][1]; $stabs[$llnoffset+$sx][0] += $sres['afw3'][1];}
						else if($repper > 2) $stabs[$llnoffset+$sx][3]=0;
						if($sres['late1'][1] > 0) { $stlate[$llnoffset+$sx][1] = $sres['late1'][1]; $stlate[$llnoffset+$sx][0] += $sres['late1'][1];}
						else $stlate[$llnoffset+$sx][1]=0;
						if($sres['late2'][1] > 0 && $repper > 1) { $stlate[$llnoffset+$sx][2] = $sres['late2'][1]; $stlate[$llnoffset+$sx][0] += $sres['late2'][1];}
						else if($repper > 1) $stlate[$llnoffset+$sx][2]=0;
						if($sres['late3'][1] > 0 && $repper > 2) { $stlate[$llnoffset+$sx][3] = $sres['late3'][1]; $stlate[$llnoffset+$sx][0] += $sres['late3'][1];}
						else if($repper > 2) $stlate[$llnoffset+$sx][3]=0;
					}
					unset($sres);
				}

				// Subject results
				echo("<TR><td class=subjectcol>Luistervaardigheid</td>");
				show_result($llnoffset+1,"lv",false,true);
				echo("</tr><TR><td class=subjectcol>Spreekvaardigheid</td>");
				show_result($llnoffset+1,"sv",false,true);
				echo("</tr><TR><td class=subjectcol>Belangstelling</td>");
				show_result($llnoffset+1,"bel",false,true);
				echo("</tr><TR><td class=subjectcol>Werkconcentratie</td>");
				show_result($llnoffset+1,"wc",false,true);
				echo("</tr><TR><td class=subjectcol>Expressievermogen</td>");
				show_result($llnoffset+1,"ev",false,true);
				echo("</tr><TR><td class=subjectcol>Tempo</td>");
				show_result($llnoffset+1,"tpo",false,true);
				echo("</tr><TR><td class=subjectcol>Nauwkeurigheid</td>");
				show_result($llnoffset+1,"nk",false,true);
				echo("</tr><TR><td class=subjectcol>Gedrag - sociaal</td>");
				show_result($llnoffset+1,"gds",false,true);
				echo("</tr><TR><td class=subjectcol>Gedrag - beleefdheid</td>");
				show_result($llnoffset+1,"gdb",false,true);
				echo("</tr>");
				echo("<tr><td colspan=4 class=spacerrow>&nbsp;</td></tr>");
				echo("<TR><td class=subjectcol>Verzuim</td>");
				echo("<td class=resultcol>". (isset($stabs[$llnoffset+1][1]) ? $stabs[$llnoffset+1][1]. " x" : "&nbsp;"). "</td>");
				echo("<td class=resultcol>". (isset($stabs[$llnoffset+1][2]) ? $stabs[$llnoffset+1][2]. " x" : "&nbsp;"). "</td>");
				echo("<td class=resultcol>". (isset($stabs[$llnoffset+1][3]) ? $stabs[$llnoffset+1][3]. " x" : "&nbsp;"). "</td></tr>");
				echo("<TR><td class=subjectcol>Te laat</td>");
				echo("<td class=resultcol>". (isset($stlate[$llnoffset+1][1]) ? $stlate[$llnoffset+1][1]. " x" : "&nbsp;"). "</td>");
				echo("<td class=resultcol>". (isset($stlate[$llnoffset+1][2]) ? $stlate[$llnoffset+1][2]. " x" : "&nbsp;"). "</td>");
				echo("<td class=resultcol>". (isset($stlate[$llnoffset+1][3]) ? $stlate[$llnoffset+1][3]. " x" : "&nbsp;"). "</td></tr>");
				echo("</TABLE>");
				echo("<P><B>Eindrapport</b></p>");
				echo("<P class=bevline><SPAN class=bevbullit>O</span><SPAN class=bevtxt>Bevorderd naar klas:</span><span class=bevfill>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></p>");
				echo("<P class=bevline><SPAN class=bevbullit>O</span><SPAN class=bevtxt>Stroomt door naar klas:</span><span class=bevfill>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></p>");
				echo("<P class=bevline><SPAN class=bevbullit>O</span><SPAN class=bevtxt>Over wegens leeftijd naar klas:</span><span class=bevfill>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></p>");
				echo("<P class=bevline><SPAN class=bevbullit>O</span><SPAN class=bevtxt>Niet bevorderd</span></p>");
				echo("<table class=signtable><tr><td>Leerkracht</td><td>Schoolhoofd</td><td>Ouder / Voogd</td></tr><tr><td> <BR><BR></td><td> <BR><BR></td><td> <BR><BR></td><tr><td> <BR><BR></td><td> <BR><BR></td><td> <BR><BR></td><tr><td> <BR><BR></td><td> <BR><BR></td><td> <BR><BR></td></table>");
				echo("</DIV><DIV class=rightpage>");

				echo("<TABLE class=cijferlijst>");
				echo("<TR><TD class=raphead>&nbsp;</td>");
				echo("<td class=raphead>R1</td>");
				echo("<td class=raphead>R2</td>");
				echo("<td class=raphead>R3</td>");
				if(substr($_SESSION['CurrentGroup'],3,1) > 2)
					echo("<td class=raphead>R4</td>");
				echo("</tr>");

				// Subject results
				echo("");
				echo("<TR class=boldseps><td class=subjectcol><B>Godsdienst</b></td>");
				show_result($llnoffset+1,"gods",false,true);
				echo("</tr><TR><td class=subjectcol><B>Lezen</b></td></tr>");
				echo("</tr><TR><td class=subjectcol><SPAN class=ident>Technisch lezen</span></td>");
				show_result($llnoffset+1,"lztl");
				echo("</tr><TR><td class=subjectcol><SPAN class=ident>AVI - niveau</span></td>");
				show_result($llnoffset+1,"lzavi",false,true);
				echo("</tr><TR class=boldseps><td class=subjectcol><B>Schrijven</b></td>");
				show_result($llnoffset+1,"schr",true);
				echo("</tr><TR><td class=subjectcol><B>Rekenen</b></td></tr>");
				echo("</tr><TR><td class=subjectcol><SPAN class=ident>Inzicht</span></td>");
				show_result($llnoffset+1,"rwinz",false,true);
				echo("</tr><TR class=boldsep><td class=subjectcol><SPAN class=ident>Rekenen</span></td>");
				show_result($llnoffset+1,"rw",true);
				echo("</tr><TR><td class=subjectcol><B>Nederlandse taal</b></td></tr>");
				echo("</tr><TR><td class=subjectcol><SPAN class=ident>Taaloefeningen</span></td>");
				show_result($llnoffset+1,"nloef");
				echo("</tr><TR><td class=subjectcol><SPAN class=ident>Spelling</span></td>");
				show_result($llnoffset+1,"nlsp");
				echo("</tr><TR><td class=subjectcol><SPAN class=ident>Begrijpend lezen</span></td>");
				show_result($llnoffset+1,"nlbg");
				echo("</tr><TR class=boldsep><td class=subjectcol><SPAN class=ident>Gemiddelde</span></td>");
				show_result($llnoffset+1,"nl",true);			
				echo("</tr><TR><td class=subjectcol><B>Wereldoriëntatie</b></td></tr>");
				echo("</tr><TR><td class=subjectcol><SPAN class=ident>Geschiedenis</span></td>");
				show_result($llnoffset+1,"wogs",true);
				echo("</tr><TR><td class=subjectcol><SPAN class=ident>Aardrijkskunde</span></td>");
				show_result($llnoffset+1,"woak",true);
				echo("</tr><TR class=boldsep><td class=subjectcol><SPAN class=ident>K.D.N.</span></td>");
				show_result($llnoffset+1,"wokdn",true);
				echo("</tr><TR><td class=subjectcol><B>Expressie vakken</b></td></tr>");
				echo("</tr><TR><td class=subjectcol><SPAN class=ident>Tekenen</span></td>");
				show_result($llnoffset+1,"exptek",true);
				echo("</tr><TR><td class=subjectcol><SPAN class=ident>Muziek</span></td>");
				show_result($llnoffset+1,"expmu",true);
				echo("</tr><TR><td class=subjectcol><SPAN class=ident>Handvaardigheid</span></td>");
				show_result($llnoffset+1,"exphv",true);
				echo("</tr><TR><td class=subjectcol><SPAN class=ident>Bewegingsonderwijs</span></td>");
				show_result($llnoffset+1,"expbo",true);
				echo("</tr><TR class=boldseps><td class=subjectcol><B>Verkeer</b></td>");
				show_result($llnoffset+1,"wovk",true);
				echo("</tr><TR class=boldseps><td class=subjectcol><B>Maatschappijleer</b></td>");
				show_result($llnoffset+1,"ml",false,true);
				echo("</tr><TR class=boldseps><td class=subjectcol><B>Engels</b></td>");
				show_result($llnoffset+1,"Eng",true);
				echo("</tr><TR class=boldseps><td class=subjectcol><B>Spaans</b></td>");
				show_result($llnoffset+1,"Spa",true);				
				echo("</tr><TR class=boldseps><td class=subjectcol><B>Papiamento</b></td>");
				show_result($llnoffset+1,"Pap",true);				
				echo("</tr><TR class=boldseps><td class=subjectcol><B>Presenteren</b></td>");
				show_result($llnoffset+1,"pre",true);				
				echo("</tr>");
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
		echo("<TD class=resultcol>". ((isset($behave[$sid][$aspect][$per]) && $per >= $repper) ? $behave[$sid][$aspect][$per] : '&nbsp;'). "</TD>");
	}
	
	function show_result($sid,$subj,$per4 = false,$letter = false,$convertletter = false)
	{
		global $stres, $repper;
		for($per=1; $per < 4; $per++)
		{
			echo("<TD class=resultcol>");
			if(isset($stres[$sid][$subj][$per]) && $per <= $repper)
			{
				show_res_per($stres[$sid][$subj][$per],$letter,$convertletter);
			}
			else
				echo("&nbsp;");
			echo("</td>");
		}
		if(substr($_SESSION['CurrentGroup'],3,1) > 2)
		{	
			echo("<TD class=resultcol><B>");
				if(isset($stres[$sid][$subj][0]) && $repper == 3 && $per4)
					show_res_per($stres[$sid][$subj][0],$letter,$convertletter);
				else
					echo("&nbsp;");
			echo("</B></td>");
		}
		
	}
	
	function show_res_per($res, $letter, $convertletter)
	{
		if($letter || !($res > 0.9))
		{
			$res = strtolower($res);
			if(strlen($res) == 2)
				$res = substr($res,0,1). "/". substr($res,1);
			echo($res);
		}
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
			if ($res < 4.0)
				echo("4-");
			else
				echo(number_format($res,1,",","."));
		}
	}
	
	function firstonly($name)
	{
		$names = explode(" ",$name);
		return $names[0];
		
	}
?>