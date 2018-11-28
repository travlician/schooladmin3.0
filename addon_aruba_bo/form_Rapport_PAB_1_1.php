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
  $repper=3;
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
    echo("<html><head><title>1e Rapport</title>");
		echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
		echo("</head><body link=blue vlink=blue>");
    echo '<LINK rel="stylesheet" type="text/css" href="style_Rapport_PAB.css" title="style1">';

    foreach($groups['gid'] AS $gix => $gid)
	{
		$mentorname = $groups['firstname'][$gix]. " ". $groups['lastname'][$gix];
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
				echo("<TR class=opmsep><TD colspan=2>Opmerkingen van het 1<sup>e</sup> rapport</TD></TR>");
				echo("<TR><TD colspan=2 class=opmcontent11>");
				if(isset($remarks[$students['sid'][$llnoffset+1]][1]))
					echo($remarks[$students['sid'][$llnoffset+1]][1]);
				else
					echo("&nbsp;");
				echo("</TD></TR>");
				echo("<TR><TD class=spacerrow colspan=2>&nbsp;</td></tr>");
				echo("<TR><TD class=spacerrow colspan=2><CENTER><B><U><FONT SIZE=+2>Betekenis van letters:</FONT></U></B></CENTER></td></tr>");
				echo("<TR><TD class=spacerrow colspan=2>&nbsp;</td></tr>");
				echo("<TR><TD class=spacerrow><font size=+1>A= Goed</font></td><td><font size=+1>B= Ruim voldoende</td></tr>");
				echo("<TR><TD class=spacerrow colspan=2>&nbsp;</td></tr>");
				echo("<TR><TD class=spacerrow><font size=+1>C= Voldoende</td><td><font size=+1>D= Matig</td></tr>");
				echo("<TR><TD class=spacerrow colspan=2>&nbsp;</td></tr>");
				echo("<TR><TD class=spacerrow><font size=+1>E= Onvoldoende</td><td><font size=+1>F= Slecht</font></td></tr>");
				echo("</TABLE>");
				echo("</DIV>");
				// Show frontpage
				echo("<DIV class=frontpage><P class=raptitle11>1<sup>e</sup> Rapport</p><P class=yeartitle11>Schooljaar ". $schoolyear. "</p><img src=schoollogo.png class=frontlogo width=30%><P class=schoolnamefront>Openbare Basisschool<BR>Oranjestad, Jupiterstraat 23<BR>Tel: (297) 588-5890 - Fax (297) 582-2012<P class=rapdblock11>");
				echo("<SPAN class=rapname11><b>Van: </b>". $students['lastname'][1+$llnoffset]. ", ". $students['firstname'][1+$llnoffset]);
				echo("<BR><BR><b>Klas</b> : ". $groups['groupname'][$gix]. "</SPAN></P>");
				echo("</DIV><P class=pagebreak>&nbsp;</P>");

				// On to the page with data
				echo("<DIV class=leftpage>");
				echo("<SPAN class=raplabel2><b>1<sup>e</sup> rapport van : </b></span><SPAN class=rapname2>". $students['lastname'][1+$llnoffset]. " ". $students['firstname'][1+$llnoffset]. "</SPAN>");
				echo("<TABLE class=cijferlijst11>");
				
				// Get the student period results for students in set
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
				echo("<TR><TD class=spacerrow colpan=2><SPAN class=raplabel211>Gedrag</span></td></tr>");
				echo("<TR><TD class=subjectcol1>Contact met leerkracht</TD>");
				show_behave($students['sid'][$llnoffset+1],'ContactLK');
				echo("</TR><TR><TD class=subjectcol>Contact met leerling</TD>");
				show_behave($students['sid'][$llnoffset+1],'ContactLL');
				echo("</TR><TR><TD class=subjectcol>Zelfvertrouwen</TD>");
				show_behave($students['sid'][$llnoffset+1],'Zelfvertrouwen');
				echo("</TR>");		  

				echo("<TR><TD class=spacerrow colpan=2><SPAN class=raplabel211>Leer&#8209; en werkhouding</span></td></tr>");
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
				echo("<TR><TD class=spacerrow colspan=2>&nbsp;</td></tr>");
				echo("<td class=subjectcol>Te laat</td>");
				echo("<td class=resultcol>". (isset($stlate[$llnoffset+1][1]) ? $stlate[$llnoffset+1][1] : "&nbsp;"). "</td></tr>");
				echo("<TR><td class=subjectcol>Verzuim</td>");
				echo("<td class=resultcol>". (isset($stabs[$llnoffset+1][1]) ? $stabs[$llnoffset+1][1] : "&nbsp;"). "</td></tr>");

				echo("<TR><TD class=spacerrow colpan=2><SPAN class=raplabel211>Lezen</span></td></tr>");
				echo("<TR><TD class=subjectcol>Letterkennis</TD>");
				show_det_result($students['sid'][$llnoffset+1],'Tech. Lezen','L');
				echo("</tr><TR><TD class=subjectcol>Analyse: boek -> b-oe-k</TD>");
				show_det_result($students['sid'][$llnoffset+1],'Tech. Lezen','A');
				echo("</tr><TR><TD class=subjectcol>Synthese: b-oe-k -> boek</TD>");
				show_det_result($students['sid'][$llnoffset+1],'Tech. Lezen','S');
				echo("</tr><TR><TD class=subjectcol>Hardop lezen</TD>");
				show_det_result($students['sid'][$llnoffset+1],'Tech. Lezen','H');
				echo("</tr>");

				echo("<TR><TD class=spacerrow colpan=2><SPAN class=raplabel211>Schrijven</span></td></tr>");
				echo("<TR><TD class=subjectcol>Linkshandig/rechtshandig</TD>");
				show_det_result($students['sid'][$llnoffset+1],'Schrijven','LR');
				echo("</tr><TR><TD class=subjectcol>Potloodhouding</TD>");
				show_det_result($students['sid'][$llnoffset+1],'Schrijven','P');
				echo("</tr><TR><TD class=subjectcol>Zithouding</TD>");
				show_det_result($students['sid'][$llnoffset+1],'Schrijven','Z');
				echo("</tr><TR><TD class=subjectcol>Omzetten van leesletters in schrijfletters</TD>");
				show_det_result($students['sid'][$llnoffset+1],'Schrijven','O');
				echo("</tr><TR><TD class=subjectcol>Lettervormen</TD>");
				show_det_result($students['sid'][$llnoffset+1],'Schrijven','L');
				echo("</tr><TR><TD class=subjectcol>Cijfervormen</TD>");
				show_det_result($students['sid'][$llnoffset+1],'Schrijven','C');
				echo("</tr>");


				echo("</TABLE>");
				echo("</DIV><DIV class=rightpage>");
				echo("<SPAN class=raplabel2><b>Klas : </span><SPAN class=rapdata>". $_SESSION['CurrentGroup']. "</SPAN><SPAN class=raplabel2>Schooljaar : </span><SPAN class=rapdata>".  $schoolyear. "</b></SPAN>");
				echo("<TABLE class=cijferlijst11>");

				echo("<TR><TD class=spacerrow colpan=2><SPAN class=raplabel211>Rekenen</span></td></tr>");
				echo("<TR><TD class=subjectcol>Inzicht</TD>");
				show_det_result($students['sid'][$llnoffset+1],'Basis Vaard','I');
				echo("</tr><TR><TD class=subjectcol>Tellen en terugtellen 1-20</TD>");
				show_det_result($students['sid'][$llnoffset+1],'Basis Vaard','T');
				echo("</tr><TR><TD class=subjectcol>Herkennen cijfers 0-20</TD>");
				show_det_result($students['sid'][$llnoffset+1],'Basis Vaard','H');
				echo("</tr><TR><TD class=subjectcol>Kennis getallenlijn 1-20</TD>");
				show_det_result($students['sid'][$llnoffset+1],'Basis Vaard','K');
				echo("</tr><TR><TD class=subjectcol>Splitsingen t/m 10</TD>");
				show_det_result($students['sid'][$llnoffset+1],'Basis Vaard','S');
				echo("</tr>");

				echo("<TR><TD class=spacerrow colpan=2><SPAN class=raplabel211>Nederlanse taal</span></td></tr>");
				echo("<TR><TD class=subjectcol>Letterdictee</TD>");
				show_det_result($students['sid'][$llnoffset+1],'Dictee','L');
				echo("</tr><TR><TD class=subjectcol>Woorddictee</TD>");
				show_det_result($students['sid'][$llnoffset+1],'Dictee','W');
				echo("</tr><TR><TD class=subjectcol>Woordenschat</TD>");
				show_det_result($students['sid'][$llnoffset+1],'Taal oefeningen','W');
				echo("</tr><TR><TD class=subjectcol>Versjes opzeggen</TD>");
				show_det_result($students['sid'][$llnoffset+1],'Taal oefeningen','V');
				echo("</tr>");

				echo("<TR><TD class=spacerrow colspan=2>&nbsp;</td></tr>");
				echo("<TR><TD class=spacerrow colpan=2><SPAN class=raplabel211>Expressie</span></td></tr>");
				echo("<td class=subjectcol>Muzikale vorming</td>");
				show_result($llnoffset+1,"Muziek",false,false,true);
				echo("</tr><TR><td class=subjectcol>Tekenen</td>");
				show_result($llnoffset+1,"Tekenen",false,false,true);
				echo("</tr><TR><td class=subjectcol>Handvaardigheid</td>");
				show_result($llnoffset+1,"Handv.",false,false,true);
				echo("</tr><TR><td class=subjectcol>Lichamelijke opvoeding</td>");
				show_result($llnoffset+1,"Lich. Opvoeding",false,false,true);				
				echo("</tr><TR>");


				echo("<TR><TD class=spacerrow colspan=6>&nbsp;</td></tr>");
				
				echo("</table>");
				echo("<TABLE class=cijferlijst11>");
				echo("<TR><TD class=spacerrow colspan=6>&nbsp;</td></tr>");
				echo("<TR><TD class=spacerrow colspan=5><SPAN class=raplabel211>Handtekeningen :</span></td></tr>");
				echo("<TR><TD class=signname><BR>Hoofd der school<BR>&nbsp;</td><td class=resultcol style='width: 70%;'>&nbsp;</td></tr>");
				echo("<TR><TD class=signname><BR>Leerkracht<BR>&nbsp;</td><td class=resultcol>&nbsp;</td></tr>");
				echo("<TR><TD class=signname><BR>Ouder / Verzorger<BR>&nbsp;</td><td class=resultcol>&nbsp;</td></tr>");
									
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
		for($per = 1; $per < 2; $per++)
		echo("<TD class=resultcol>". ((isset($behave[$sid][$aspect][$per]) && $per <= $repper) ? $behave[$sid][$aspect][$per] : '&nbsp;'). "</TD>");
	}
	
	function show_result($sid,$subj,$per4 = false,$letter = false,$convertletter = false)
	{
		global $stres, $repper;
		for($per=1; $per < 2; $per++)
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
	
	function show_det_result($sid,$subj,$sd)
	{
		global $schoolyear;
		// Need to get results first
		echo("<TD class=resultcol>");
		$resqr = SA_loadquery("SELECT result, AVG(result) AS avgres, COUNT(tdid) AS rcnt FROM testresult LEFT JOIN testdef USING(tdid) LEFT JOIN `class` USING(cid) LEFT JOIN subject USING(mid) WHERE year='". $schoolyear. "' AND sid=". $sid. " AND shortname='". $subj. "' AND short_desc LIKE '". $sd. "'");
		if(!isset($resqr['rcnt'])) // No result found...
			echo("-");
		else if($resqr['rcnt'][1] > 1) // result of multiple tests
			show_res_per($resqr['avgres'][1],false,true);
		else // single result, may be letter
			show_res_per($resqr['result'][1],!($resqr['result'][1] > 0.9),$resqr['result'][1] < 0.9);
		echo("</td>");	
	}

?>