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
  require_once ("inputlib/inputclassbase.php");
  inputclassbase::dbconnect($userlink);
  
  // Operational definitions
  $vakcats = array("A. Taal & Communicatie","C. Exacte vakken","B. Maatschappij","D. Onderw. Onderst.","E. Kunst & Cultuur","F. Individu");
  /* MC */
  $vakhead["A. Taal & Communicatie"] = array("NE","EN","SP","PA");
  $vakhead["B. Maatschappij"] = array("GS","AK");
  $vakhead["C. Exacte vakken"] = array("WI","REK","NA","SK","BI");
  $vakhead["D. Onderw. Onderst."] = array("EM & O","IK");
  $vakhead["E. Kunst & Cultuur"] = array("CKV");
  $vakhead["F. Individu"] = array("LO","GDS");
  $ptvakken = array("LO","GS","AK","EM & O","PA","NE","EN","SP","CKV","NA","SK","WI","BI","IK");
  $newvakken = array("NE","EN","WI");

  // Create a list of applicable aspects
  $aspects = array('Inzet' => 'Inzet','Gedr' => 'Gedrag', 'Regels' => 'Regels', 'HWerk' => 'Huiswerk', 
                   'Conc' => 'Concentratie', 'Cap' => 'Capaciteit', 'Wrkvz' => 'Werkverzorging', 'Tempo' => 'Tempo');
/*  $vakcats = array("A. Individu","B. Maatschappij","C. Taal & Communicatie","D. Kunst & Cultuur","E. Natuur","F. Wiskunde","G. Onderwijsondersteuning");
  $vakhead["A. Individu"] = array("KGL","PV","LO");
  $vakhead["B. Maatschappij"] = array("ASW");
  $vakhead["C. Taal & Communicatie"] = array("PA","NE","EN","SP");
  $vakhead["D. Kunst & Cultuur"] = array("CKV");
  $vakhead["E. Natuur"] = array("N & T");
  $vakhead["F. Wiskunde"] = array("WI");
  $vakhead["G. Onderwijsondersteuning"] = array("IK");
  $newan = array("NE","EN","WI","ASW","N & T");
  $ptvakken = array("NE","EN","SP","PA","WI","ASW","IK","PV","KGL","CKV","N & T","LO"); */
  
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
  $groepfilter = "3%' AND groupname NOT LIKE '3MAVO";
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
  
	// Get a list of subject packages for all students
	$packagesqr = SA_loadquery("SELECT sid,GROUP_CONCAT(shortname) AS sbpack FROM s_package LEFT JOIN subjectpackage USING(packagename) LEFT JOIN subject USING(mid) GROUP BY sid");
	if(isset($packagesqr['sid']))
		foreach($packagesqr['sid'] AS $sbix => $asid)
			$package[$asid] = $packagesqr['sbpack'][$sbix];
  
  if(isset($groups))
  {
    // First part of the page
    echo("<html><head><title>Rapport</title></head><body link=blue vlink=blue>");
	echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
    echo '<LINK rel="stylesheet" type="text/css" href="style_Rapport_LS.css" title="style1">';

    foreach($groups['gid'] AS $gix => $gid)
	{
	  // Create a list of subject details
	  $sdquery = "SELECT type, fullname, shortname, mid, data FROM class LEFT JOIN subject USING(mid) LEFT JOIN ". $teachercode. " USING(tid) WHERE gid=". $gid;
	  $sdquery .= " UNION SELECT type, fullname, shortname, mid, '' FROM subject WHERE type='meta'";
	  $subjectdata = SA_loadquery($sdquery);
	  foreach($subjectdata['shortname'] AS $cix => $subjab)
	  {
	    $subjdata[$subjab]["teacher"] = $subjectdata["data"][$cix];
			$subjdata[$subjab]["fullname"] = $subjectdata["fullname"][$cix];
			$subjdata[$subjab]["type"] = $subjectdata["type"][$cix];
			$subjdata[$subjab]["mid"] = $subjectdata["mid"][$cix];
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
		
	  // Decide for which period report is produced
	  $curm = date("n");
	  if($curm > 8 || $curm < 3)
	    $repper = 1;
	  else if($curm < 5)
	    $repper = 2;
	  else
	    $repper = 3;

      // Get all exisiting records in an array
      $houdingdata = SA_loadquery("SELECT aspect,sid,xstatus,COUNT(houdingid) AS score FROM avo_pk_data WHERE year='". $schoolyear. "' AND period=". $repper. " GROUP BY sid,aspect,xstatus");
      // Convert this to a more convenient array type
      if(isset($houdingdata))
        foreach($houdingdata['score'] AS $xix => $score)
	      $hdata[$houdingdata['sid'][$xix]][$houdingdata['aspect'][$xix]][$houdingdata['xstatus'][$xix]] = $score;
	  if(isset($students))
	  {
	    $llnoffset = 0;
		while ($llnoffset < sizeof($students['sid']))
		{
          $sid = $students['sid'][1+$llnoffset];
		  $scnt = $llnperpage;
		  if(sizeof($students['sid']) - $llnoffset < $scnt)
		    $scnt = sizeof($students['sid']) - $llnoffset;
		  echo("<DIV class=toppage>");
		  echo("<SPAN class=leftlabelCA>Ciclo Avansa:</SPAN><SPAN class=midfill1>&nbsp;". $groups['groupname'][$gix]. "</SPAN><B>Schooljaar: ". $schoolyear. "</B><BR>");
		  echo("<SPAN class=leftlabelCA>Leerling:</SPAN><SPAN class=studentname>&nbsp;". $students['firstname'][1+$llnoffset]. " ". $students['lastname'][1+$llnoffset]. "</SPAN><BR>");
		  echo("<SPAN class=leftlabelCA>Mentor:</SPAN><SPAN class=studentname>&nbsp;<B>". $groups['data'][$gix]. "</B></SPAN><BR>");
		  echo("<SPAN class=leftlabelCA>Rapportage:</SPAN><SPAN class=midfill1>". $repper. "</SPAN><B>". $schoolname. "</B><BR>");
		  echo("<SPAN class=subresultsheaderCA>Rapportcijfers ". $repper. "</SPAN>");
		  echo("</DIV><DIV class=leftpage>");
		  echo("<TABLE class=subresultstable><tr><th>Vak:</th><th>OVERHORINGEN</th><th>PROEFWERKEN</th></tr>");
		  // Get the detailed results for this student
		  unset($detrespw);
		  unset($detresoh);
		  $detres = SA_loadquery("SELECT testresult.tdid, IF(subject.type='sub',meta_subject,mid) AS mid, result, testdef.type FROM testresult LEFT JOIN testdef USING(tdid) LEFT JOIN `class` USING(cid) LEFT JOIN subject USING(mid) WHERE year='". $schoolyear. "' AND period=". ($repper < 4 ? $repper : 3). " AND sid=". $students['sid'][1+$llnoffset]. " AND result IS NOT NULL ORDER BY date");
		  if(isset($detres))
		    foreach($detres['tdid'] AS $drix => $tdid)
			{
			  if($detres['type'][$drix] == "OH" || $detres['type'][$drix] == "PO1x")
			    $detresoh[$detres['mid'][$drix]][$tdid] = $detres['result'][$drix];
			  else if(substr($detres['type'][$drix],0,2) == "PW" || $detres['type'][$drix] == "PO2X")
			    $detrespw[$detres['mid'][$drix]][$tdid] = $detres['result'][$drix];			  
			}
		  foreach($ptvakken AS $subsj)
		  {
		    echo("<tr><td class=subsubjname>". $subjdata[$subsj]['fullname']. "</td><td class=ohresults>");
			if(isset($detresoh[$subjdata[$subsj]['mid']]))
			{
			  foreach($detresoh[$subjdata[$subsj]['mid']] AS $ohres)
				{
					if($ohres > 0)
						echo("<SPAN class=subresult>". ($ohres < 5.5 ? "<font color=red>" : ""). number_format($ohres,1,',','.'). ($ohres < 5.5 ? "</font>" : ""). "</SPAN>");
					else
						echo("<SPAN class=subresult>". $ohres. "</SPAN>");
				}
			}
			else
			  echo("&nbsp;");
			echo("</td><td class=pwresults>");
			if(isset($detrespw[$subjdata[$subsj]['mid']]))
			{
			  foreach($detrespw[$subjdata[$subsj]['mid']] AS $pwres)
			    echo("<SPAN class=subresult>". ($pwres < 5.5 ? "<font color=red>" : ""). number_format($pwres,1,',','.'). ($pwres < 5.5 ? "</font>" : ""). "</SPAN>");
			}
			else
			  echo("&nbsp;");
			echo("</td></tr>");
		  }
		  echo("</table>");
		  echo("<BR><SPAN class=subresultsheader>Persoonlijke Kwaliteiten</SPAN>");
		  
		  echo("<TABLE class=pktable><tr class=pktoprow><th class=pktableft>Kwaliteiten</th><th class=pkval>Goed</th><th class=pkval>Voldoende</th><th class=pkval>Matig</th><th class=pkval>Slecht</th></tr>");
		  foreach($aspects AS $aspix => $aspect)
		  {
		    echo("<tr><td class=pklabel>". $aspect. "</td>");
	        $totacnt = (isset($hdata[$sid][$aspix]["G"]) ? $hdata[$sid][$aspix]["G"] : 0) + 
	                   (isset($hdata[$sid][$aspix]["V"]) ? $hdata[$sid][$aspix]["V"] : 0) +
	                   (isset($hdata[$sid][$aspix]["M"]) ? $hdata[$sid][$aspix]["M"] : 0) +
	                   (isset($hdata[$sid][$aspix]["S"]) ? $hdata[$sid][$aspix]["S"] : 0);
	        echo("<TD class=pkval>". (isset($hdata[$sid][$aspix]["G"]) ? ROUND($hdata[$sid][$aspix]["G"] * 100.0 / $totacnt,0). "%" : " "). "</td>");
	        echo("<TD class=pkval>". (isset($hdata[$sid][$aspix]["V"]) ? ROUND($hdata[$sid][$aspix]["V"] * 100.0 / $totacnt,0). "%" : " "). "</td>");
	        echo("<TD class=pkval>". (isset($hdata[$sid][$aspix]["M"]) ? ROUND($hdata[$sid][$aspix]["M"] * 100.0 / $totacnt,0). "%" : " "). "</td>");
	        echo("<TD class=pkval>". (isset($hdata[$sid][$aspix]["S"]) ? ROUND($hdata[$sid][$aspix]["S"] * 100.0 / $totacnt,0). "%" : " "). "</td>");
			echo("</tr>");
		  }
		  echo("</table>");
		  // Get the remarks and date of remarks
		  $opmdata = inputclassbase::load_query("SELECT opmtext,lastmodifiedat FROM bo_opmrap_data WHERE sid=". $sid. " AND year='". $schoolyear. "' AND period=". $repper);
		  if(isset($opmdata['lastmodifiedat'][0]))
		    $opmdate = inputclassbase::mysqldate2nl(substr($opmdata['lastmodifiedat'][0],0,10));
		  else
		    $opmdate = date("d-m-Y");
		  echo("<div class=opmboxCA><span class=opmleftCA>Opmerking:</span><span class=opmdate>DATUM: ". $opmdate. "</SPAN><BR>");
		  if(isset($opmdata['opmtext'][0]))
		    echo($opmdata['opmtext'][0]);		  
		  echo("</DIV></DIV>"); // end of remarkbox and leftpart of page div.
		  echo("<TABLE class=raptable><TR><TH class=rapmainhdr COLSPAN=2>Gemiddelde Rapport ". $repper. "</TH>");
		  echo("<TH class=rapperhdr rowspan=2>1ste R</TH>");
		  if($repper > 1)
		    echo("<TH class=rapperhdr rowspan=2>2de R</TH>");
		  if($repper > 2)
		    echo("<TH class=rapperhdr rowspan=2>3de R</TH>");
			echo("<TH class=rapperhdr rowspan=2>EIND</TH>");
		  echo("</tr>");
		  echo("<tr><th class=rapsubj colspan=2>Vakken</th></tr>");
		    
		  
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
		    echo("<TR>");
			// echo("<TD class=cathead ROWSPAN=". sizeof($vakhead[$vk]). ">". $vk. "</TD>");
			//echo("</TR>");
			foreach($vakhead[$vk] AS $vix => $vkn)
			{
			  if($vix == 0)
			    $tdclass="raptdtop3";
			  else if($vix == count($vakhead[$vk]) - 1)
			  {
			    $tdclass="raptdbot3";
				echo("<TR>");
			  }
			  else
			  {
				echo("<TR>");
			    $tdclass="raptd3";
			  }
			  echo("<TD class=". $tdclass. ">". $subjdata[$vkn]["fullname"]. "</TD><TD class=". $tdclass. ">". $vkn. "</TD>");
		      for($sx = 1; $sx <= $scnt; $sx++)
		      {
			    echo("<TD class=". $tdclass. ">");
				if(isset($stres[$llnoffset+$sx][$vkn][1]))
				  echo(colored(number_format($stres[$llnoffset+$sx][$vkn][1],1,',','.')));
				else
				  echo("&nbsp;");
				echo("</TD>");
				if($repper > 1)
				{
			      echo("<TD class=". $tdclass. ">");
				  if(isset($stres[$llnoffset+$sx][$vkn][2]))
				    echo(colored(number_format($stres[$llnoffset+$sx][$vkn][2],1,',','.')));
				  else
				    echo("&nbsp;");
				  echo("</TD>");
				}
				if($repper > 2)
				{
			      echo("<TD class=". $tdclass. ">");
				  if(isset($stres[$llnoffset+$sx][$vkn][3]))
				    echo(colored(number_format($stres[$llnoffset+$sx][$vkn][3],1,',','.')));
				  else
				    echo("&nbsp;");
				  echo("</TD>");
				}
				echo("<TD class=". $tdclass. ">");
				if(isset($stres[$llnoffset+$sx][$vkn][0]))
					echo(colored($stres[$llnoffset+$sx][$vkn][0]));
				else
					echo("&nbsp;");
				echo("</TD>");
		      }
			  echo("</TR>");
			  
			} // End for each subject
		  } // End subject categories
		  
				// Calculate points for advice
				for($sx = 1; $sx <= $scnt; $sx++)
				{
					for($p=0;$p<4;$p++)
					{
						$stcalp[$sx][$p] = 0.0; // Total points of all subjects
						$stcalo[$sx][$p] = 0; // <6 count of all subjects
						$stcalc[$sx][$p] = 0; // shortage of all subjects
						$stcalpo[$sx][$p] = 0; // < 6 in selected subjects
						$stcalpt[$sx][$p] = 0; // shortage in selected subjects
						$stcalpno[$sx][$p] = 0; // < 6 in NEW in selected subjects
						$stcalpnc[$sx][$p] = 0; // Shortage in NEW selected subjects
						$stcalczo[$sx][$p] = 0; // subjects 3 or lower
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
									if($stres[$sx+$llnoffset][$vak][$p] < 3.5)
										$stcalczo[$sx][$p]++;
								}									
							}
						}
					}
					if(isset($package[$students['sid'][$sx+$llnoffset]]))
						$pvaks = explode(",",$package[$students['sid'][$sx+$llnoffset]]);
					else
						$pvaks = $newvakken;
					foreach($newvakken AS $vak)
					{
						for($p=0;$p<4;$p++)
						{
							if(isset($stres[$sx+$llnoffset][$vak][$p]) && in_array($vak,$pvaks))
							{
								if($stres[$sx+$llnoffset][$vak][$p] < 5.5)
								{
									$stcalpno[$sx][$p]++;
									$stcalpnc[$sx][$p] += 6 - round($stres[$sx+$llnoffset][$vak][$p],0);
								}
							}
						}
					}
					foreach($pvaks AS $vak)
					{
						for($p=0;$p<4;$p++)
						{
							if(isset($stres[$sx+$llnoffset][$vak][$p]))
							{
								if($stres[$sx+$llnoffset][$vak][$p] < 5.5)
								{
									$stcalpo[$sx][$p]++;
									$stcalpt[$sx][$p] += 6 - round($stres[$sx+$llnoffset][$vak][$p],0);
								}			    
							}
						}
					}
				}
		  
		  // Show points row
		  $sx=1;
		  echo("<TR><TD class=pointtd colspan=2>Aantal punten </TD>");
		  $percnt = ($repper > 2 ? 3 : $repper);
		  for($p=0;$p<$percnt;$p++)
		  {
		    $pi = ($p + 1) % 4;
			echo("<TD class=raptdtop>");
			if(isset($stcalp[$sx][$pi]) && $stcalp[$sx][$pi] > 0)
			  echo(number_format($stcalp[$sx][$pi],1,",","."));
			else
			  echo("&nbsp;");
		    echo("</TD>");
		  }
		  if($repper > 2)
		  {
		    $pi = 0;
			echo("<TD class=raptdtop>");
			if(isset($stcalp[$sx][$pi]) && $stcalp[$sx][$pi] > 0)
			  echo(number_format($stcalp[$sx][$pi],0,",","."));
			else
			  echo("&nbsp;");
		    echo("</TD>");
		  }
		  echo("</TR>");		  
		  // NEWAN points
		  
		  echo("</TABLE>");
		  
		  // Show advise
		  echo("<DIV class=advicebox>RESULTAAT: <SPAN class=resultspan>");

		  $sx=1;
		  $pi = ($repper > 2 ? 0 : $repper);
		
					// if($stcalp[$sx][$pi] >= 83.0 && $stcalo[$sx][$pi] <= 4 && $stcalc[$sx][$pi] <= 5 && $stcalpno[$sx][$pi] <= 2 && 
					//	 $stcalpo[$sx][$pi] <= 2 && $stcalpno[$sx][$pi] <= 2 &&  $stcalpno[$sx][$pi] == $stcalpnc[$sx][$pi] && $stcalczo[$sx][$pi] == 0)
					if($stcalp[$sx][$pi] >= 83.0 && $stcalo[$sx][$pi] <= 4 && $stcalc[$sx][$pi] <= 5 && $stcalpno[$sx][$pi] <= 2 && 
						 $stcalpo[$sx][$pi] <= 2 && $stcalpno[$sx][$pi] <= 2 &&  $stcalpno[$sx][$pi] == $stcalpnc[$sx][$pi])
						echo("VOLDOENDE"); // BEV
					else if($stcalp[$sx][$pi] >= 78.0 && $stcalo[$sx][$pi] <= 4 && $stcalc[$sx][$pi] <= 6 && $stcalpno[$sx][$pi] <= 2 && 
					$stcalpo[$sx][$pi] <= 2 && $stcalpno[$sx][$pi] <= 2 &&  $stcalpno[$sx][$pi] == $stcalpnc[$sx][$pi])
						echo("&nbsp;"); // BES
					else
						echo("ONVOLDOENDE");
		  echo("</SPAN> RAPPORT</DIV>");
		  
		  echo("<table class=raptable><TR><TD class=absencetd>Te laat:</TD><TD>&nbsp;</TD></TR><TR><TD class=absencetd>Absentie:</TD><TD>&nbsp;</TD></TR></table>");
		  
		  echo("<DIV class=signbox><SPAN class=signtxtspan>Handtekening:</SPAN><BR>");
		  echo("<SPAN class=signline>Mentor</SPAN><SPAN class=signspace>&nbsp;</SPAN><SPAN class=signline>Ouder/Voogd</SPAN><SPAN class=signspace>&nbsp;</SPAN><SPAN class=signline>Directeur</SPAN></DIV>");
		  //echo("<p class=pagebreak>&nbsp;</p>");
		  
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
