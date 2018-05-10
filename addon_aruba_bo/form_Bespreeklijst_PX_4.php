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
	include("teacher.php");
 	
	inputclassbase::dbconnect($userlink);
 
  // Operational definitions
  $vakcats = array("Hoofdvakken","Zaakvakken","Andere vakken");
  /* MC */
  $vakhead["Hoofdvakken"] = array("ne","ws","tv","spe","ss","sl","re","bt","pt","mt");
	$vakhead["Zaakvakken"] = array("ak","gs","kdn");
  $vakhead["Andere vakken"] = array("le","lb","vv","ac","an","ct","tt","sc","vk","en","sp","lo","hv","te","mu","go","gze");
  $ptvakken = array("ne","re");
  $aspects = array('Gedr' => 'Gedrag', 'Conc' => 'Concentratie', 'Wrkvz' => 'Werkverzorging', 'Zelfs' => 'Zelfstandigheid', 'Motv' => 'Motivatie');
    
  $afwezigreden = array(1,2,3,5,25,26);
  $groepfilter = "4%";
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
	$me = new teacher();
	$me->load_current();
	if($me->has_role("admin") || $me->has_role("counsel"))
		$grpqry = "SELECT * FROM sgroup LEFT JOIN ". $teachercode. " ON(tid_mentor=tid) WHERE active=1 AND groupname LIKE '". $groepfilter. "' ORDER BY groupname";
	else
		$grpqry = "SELECT * FROM sgroup LEFT JOIN ". $teachercode. " ON(tid_mentor=tid) WHERE active=1 AND tid_mentor=". $me->get_id(). " AND groupname LIKE '". $groepfilter. "' ORDER BY groupname";
  $groups = SA_loadquery($grpqry);
  
  // Get a list of last test dates for periods
  //$perends = SA_loadquery("SELECT period,CEIL(date) AS edate FROM testdef GROUP BY period ORDER BY period");
	
	// Reporting period, needed to supress absence and late
	if(date("m") > 7)
		$repper = 1;
	else if(date("m") < 6)
		$repper = 2;
	else
		$repper = 3;
  
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
			$students = SA_loadquery("SELECT student.*,s_Schoolloopbaan.data AS lb FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN s_ASGender USING(sid) LEFT JOIN s_Schoolloopbaan USING(sid) WHERE gid=". $gid. " ORDER BY s_ASGender.data,lastname,firstname");

			// Get a list of period dates
			$pquery = "SELECT id as period,startdate AS sdate, enddate AS edate FROM period";
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
			
			// Get the behavioural data
			$behaviour = SA_loadquery("SELECT sid,period,aspect,xstatus FROM bo_houding_data LEFT JOIN student USING(sid) LEFT JOIN sgrouplink USING(sid) WHERE gid=". $gid. " AND year='". $schoolyear. "'");
			if(isset($behaviour))
				foreach($behaviour['sid'] AS $bix => $bsid)
				$behave[$bsid][$behaviour['aspect'][$bix]][$behaviour['period'][$bix]] = $behaviour['xstatus'][$bix];

			if(isset($students))
			{
				$llnoffset = 0;
				while ($llnoffset < sizeof($students['sid']))
				{
					$scnt = $llnperpage;
					if(sizeof($students['sid']) - $llnoffset < $scnt)
						$scnt = sizeof($students['sid']) - $llnoffset;
					echo("<TABLE BORDER=1><TR><TH class=headleft>". $schoolname. "<BR>". $schoolyear. "<BR>Klas: ". $groups['groupname'][$gix]. "<BR>Mentor: ". $groups['data'][$gix]. " </TH>");
					for($sx = 1; $sx <= $scnt; $sx++)
					{
						echo("<TH COLSPAN=4 class=studnamecel>". ($sx+$llnoffset). ". ". $students['lastname'][$sx+$llnoffset]. "<BR><SPAN class=stfirstname>". $students['firstname'][$sx+$llnoffset]. "<BR>". $students['lb'][$sx+$llnoffset]. "</SPAN></TH>");
					}
					echo("</TR>");
					
					echo("<TR><TH>Vakken</TH>");
					for($sx = 1; $sx <= $scnt; $sx++)
					{
						echo("<TH class=leftB>1</TH><TH>2</TH><TH>3</TH><TH>E</TH>");
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
						echo("<TR><TD class=cathead>". $vk. "</TD>");
						for($sx = 1; $sx <= $scnt; $sx++)
						{
							echo("<TD COLSPAN=4 class=leftB>&nbsp;</TD>");
						}
						echo("</TR>");
						foreach($vakhead[$vk] AS $vkn)
						{
							if(isset($subjdata[$vkn]))
							{
								echo("<TR><TD". ($subjdata[$vkn]['type'] == "meta" ?  ' class=metahead' : ($subjdata[$vkn]['type'] == "sub" ? ' class=subhead' : '')). ">". $subjdata[$vkn]["fullname"]. "</TD>");
									for($sx = 1; $sx <= $scnt; $sx++)
									{
									echo("<TD class=rescolB>");
								if(isset($stres[$llnoffset+$sx][$vkn][1]))
									echo(colored($stres[$llnoffset+$sx][$vkn][1],$vkn!='an',false,$subjdata[$vkn]["type"] == "meta",$vkn=='ac'));
								else
									echo("&nbsp;");
								echo("</TD>");
								echo("<TD class=rescol>");
								if(isset($stres[$llnoffset+$sx][$vkn][2]))
									echo(colored($stres[$llnoffset+$sx][$vkn][2],$vkn!='an',false,$subjdata[$vkn]["type"] == "meta",$vkn=='ac'));
								else
									echo("&nbsp;");
								echo("</TD>");
								echo("<TD class=rescol>");
								if(isset($stres[$llnoffset+$sx][$vkn][3]))
									echo(colored($stres[$llnoffset+$sx][$vkn][3],$vkn!='an',false,$subjdata[$vkn]["type"] == "meta",$vkn=='ac'));
								else
									echo("&nbsp;");
								echo("</TD>");
								echo("<TD class=rescolE>");
								if(isset($stres[$llnoffset+$sx][$vkn][0]))
									echo(colored($stres[$llnoffset+$sx][$vkn][0],$vkn!='an',false,$subjdata[$vkn]["type"] == "meta",$vkn=='ac'));
								else
									echo("&nbsp;");
								echo("</TD>");
									}
								echo("</TR>");
							}
						} // End for each subject

						if($vk == "Hoofdvakken")
						{
							// Calculate points for advice
							for($sx = 1; $sx <= $scnt; $sx++)
							{
								for($p=0;$p<4;$p++)
								{
									$stcalp[$sx][$p] = 0;
									$stcalo[$sx][$p] = 0;
									$stcalc[$sx][$p] = 0;
									$stcall[$sx][$p] = false;
								}
								foreach($ptvakken AS $vak)
								{
									for($p=0;$p<4;$p++)
									{
										if(isset($stres[$sx+$llnoffset][$vak][$p]))
										{
											$stcalp[$sx][$p] += round($stres[$sx+$llnoffset][$vak][$p],1);
											if($stres[$sx+$llnoffset][$vak][$p] < 5.5)
											{
												$stcalo[$sx][$p]++;
												$stcalc[$sx][$p] += 6 - round($stres[$sx+$llnoffset][$vak][$p],0);
											}			    
										}
									}
								}
								for($p=0;$p<4;$p++)
									if(isset($stres[$sx+$llnoffset]["le"][$p]) && $stres[$sx+$llnoffset]["le"][$p] >= 5.5)
										$stcall[$sx][$p] = true;
							}
							
							// Show calculated results
							echo("<TR><TD><B>TOTAAL PUNTEN</b> (>= 11)</TD>");
							for($sx = 1; $sx <= $scnt; $sx++)
							{
								for($p=0;$p<4;$p++)
								{
									$pi = ($p + 1) % 4;
									if($pi==1)
										echo("<TD class=leftB>");
									else if($pi==0)
										echo("<TD class=leftE>");
									else
										echo("<TD>");
									if(isset($stcalp[$sx][$pi]) && $stcalp[$sx][$pi] > 0)
										echo($stcalp[$sx][$pi]);
									else
										echo("&nbsp");
									echo("</TD>");
								}
							}
							echo("</TR>");
							
							/* echo("<TR><TD><B>TEKORTEN</b> (<=1)</TD>");
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
							echo("</TR>");	*/	
						}						
						if($vk == "Zaakvakken")
						{
							// Calculate points for advice
							for($sx = 1; $sx <= $scnt; $sx++)
							{
								for($p=0;$p<4;$p++)
								{
									$stcalzp[$sx][$p] = 0; // Points zaakvakken
									$stcalzo[$sx][$p] = 0; // Failed zaakvakken
									$stcalzc[$sx][$p] = 0; // shorts zaakvakken
								}
								foreach($vakhead["Zaakvakken"] AS $vak)
								{
									for($p=0;$p<4;$p++)
									{
										if(isset($stres[$sx+$llnoffset][$vak][$p]))
										{
											$stcalzp[$sx][$p] += round($stres[$sx+$llnoffset][$vak][$p],1);
											if($stres[$sx+$llnoffset][$vak][$p] < 5.5)
											{
												$stcalzo[$sx][$p]++;
												$stcalzc[$sx][$p] += 6 - round($stres[$sx+$llnoffset][$vak][$p],0);
											}			    
										}
									}
								}
							}
							
							// Show calculated results
							echo("<TR><TD class=leftB><B>TOTAAL PUNTEN</b> (>= 16)</TD>");
							for($sx = 1; $sx <= $scnt; $sx++)
							{
								for($p=0;$p<4;$p++)
								{
									$pi = ($p + 1) % 4;
									if($pi==1)
										echo("<TD class=leftB>");
									else if($pi==0)
										echo("<TD class=leftE>");
									else
										echo("<TD>");
									if(isset($stcalzp[$sx][$pi]) && $stcalzp[$sx][$pi] > 0)
										echo($stcalzp[$sx][$pi]);
									else
										echo("&nbsp");
									echo("</TD>");
								}
							}
							echo("</TR>");
							
							/* echo("<TR><TD><B>ONVOLDOENDES</b> (<=1)</TD>");
							for($sx = 1; $sx <= $scnt; $sx++)
							{
								for($p=0;$p<4;$p++)
								{
									$pi = ($p + 1) % 4;
									echo("<TD>");
									if(isset($stcalzo[$sx][$pi]) && $stcalzp[$sx][$pi] > 0)
										echo($stcalzo[$sx][$pi]);
									else
										echo("&nbsp");
									echo("</TD>");
								}
							}
							echo("<TR><TD><B>TEKORTEN</b> (<=2)</TD>");
							for($sx = 1; $sx <= $scnt; $sx++)
							{
								for($p=0;$p<4;$p++)
								{
									$pi = ($p + 1) % 4;
									echo("<TD>");
									if(isset($stcalzc[$sx][$pi]) && $stcalzp[$sx][$pi] > 0)
										echo($stcalzc[$sx][$pi]);
									else
										echo("&nbsp");
									echo("</TD>");
								}
							}
							echo("</TR>");		*/
						}						
					} // End subject categories
					
					// Behaviour data
					foreach($aspects AS $aspkey => $aspname)
					{
						echo("<TR><TD class=baspect>". $aspname. "</TD>");
						for($sx = 1; $sx <= $scnt; $sx++)
						{
							$aspsid = $students['sid'][$sx+$llnoffset];
							if(isset($behave[$aspsid][$aspkey][1]))
								echo("<TD class=leftB><center>". $behave[$aspsid][$aspkey][1]. "</td>");
							else
								echo("<TD class=leftB>&nbsp;</td>");
							if(isset($behave[$aspsid][$aspkey][2]))
								echo("<TD><center>". $behave[$aspsid][$aspkey][2]. "</td>");
							else
								echo("<TD>&nbsp;</td>");
							if(isset($behave[$aspsid][$aspkey][3]))
								echo("<TD><center>". $behave[$aspsid][$aspkey][3]. "</td>");
							else
								echo("<TD>&nbsp;</td>");
							echo("<TD>&nbsp;</td>");								
						}
						echo("</TR>");
					}
					
					// Absence data
					echo("<TR><TH class=headleft>Afwezig</TH>");
					for($sx = 1; $sx <= $scnt; $sx++)
					{
						// Get the absence data
						$absq = "SELECT ";
						$absq .= "SUM(IF(date >= '". $pdata[1]["sdate"]. "' AND date < '". $pdata[2]["sdate"]. "' AND authorization='Yes',1,0)) AS ap1";
						$absq .= ",SUM(IF(date >= '". $pdata[2]["sdate"]. "' AND date < '". $pdata[3]["sdate"]. "' AND authorization='Yes',1,0)) AS ap2";
						$absq .= ",SUM(IF(date >= '". $pdata[3]["sdate"]. "' AND authorization='Yes',1,0)) AS ap3";
						$absq .= ",SUM(IF(date >= '". $pdata[1]["sdate"]. "' AND date < '". $pdata[2]["sdate"]. "' AND authorization<>'Yes',1,0)) AS apu1";
						$absq .= ",SUM(IF(date >= '". $pdata[2]["sdate"]. "' AND date < '". $pdata[3]["sdate"]. "' AND authorization<>'Yes',1,0)) AS apu2";
						$absq .= ",SUM(IF(date >= '". $pdata[3]["sdate"]. "' AND authorization<>'Yes',1,0)) AS apu3";
						$absq .= " FROM absence LEFT JOIN absencereasons USING(aid) WHERE sid=". $students['sid'][$llnoffset + $sx]. " AND acid=1 GROUP BY sid";
						$absdat = SA_loadquery($absq);
						echo("<TD class=leftB>". (isset($absdat["ap1"][1]) ? $absdat["ap1"][1]. "/". $absdat["apu1"][1] : "0/0"). "</TD>
									<TD>". ($repper > 1 ? (isset($absdat["ap2"][1]) ? $absdat["ap2"][1]. "/". $absdat["apu2"][1] : "0/0") : "&nbsp;"). "</TD>
									<TD>". ($repper > 2 ? (isset($absdat["ap3"][1]) ? $absdat["ap3"][1]. "/". $absdat["apu3"][1] : "0/0") : "&nbsp;"). "</TD>
									<TD class=leftE>". ($repper > 2 && isset($absdat['ap1']) ? ($absdat['ap1'][1]+$absdat['ap2'][1]+$absdat['ap3'][1]). "/". ($absdat['apu1'][1]+$absdat['apu2'][1]+$absdat['apu3'][1]) : "&nbsp;"). "</td>");
						unset($absdat);
					}
					echo("</TR>");

					echo("<TR><TH class=headleft>Te laat</TH>");
					for($sx = 1; $sx <= $scnt; $sx++)
					{
						// Get the absence data
						$absq = "SELECT ";
						$absq .= "SUM(IF(date >= '". $pdata[1]["sdate"]. "' AND date < '". $pdata[2]["sdate"]. "' AND authorization='Yes',1,0)) AS ap1";
						$absq .= ",SUM(IF(date >= '". $pdata[2]["sdate"]. "' AND date < '". $pdata[3]["sdate"]. "' AND authorization='Yes',1,0)) AS ap2";
						$absq .= ",SUM(IF(date >= '". $pdata[3]["sdate"]. "' AND authorization='Yes',1,0)) AS ap3";
						$absq .= ",SUM(IF(date >= '". $pdata[1]["sdate"]. "' AND date < '". $pdata[2]["sdate"]. "' AND authorization<>'Yes',1,0)) AS apu1";
						$absq .= ",SUM(IF(date >= '". $pdata[2]["sdate"]. "' AND date < '". $pdata[3]["sdate"]. "' AND authorization<>'Yes',1,0)) AS apu2";
						$absq .= ",SUM(IF(date >= '". $pdata[3]["sdate"]. "' AND authorization<>'Yes',1,0)) AS apu3";
						$absq .= " FROM absence LEFT JOIN absencereasons USING(aid) WHERE sid=". $students['sid'][$llnoffset + $sx]. " AND acid=2 GROUP BY sid";
						$absdat = SA_loadquery($absq);
						echo("<TD class=leftB>". (isset($absdat["ap1"][1]) ? $absdat["ap1"][1]. "/". $absdat["apu1"][1] : "0/0"). "</TD>
									<TD>". ($repper > 1 ? (isset($absdat["ap2"][1]) ? $absdat["ap2"][1]. "/". $absdat["apu2"][1] : "0/0") : "&nbsp;"). "</TD>
									<TD>". ($repper > 2 ? (isset($absdat["ap3"][1]) ? $absdat["ap3"][1]. "/". $absdat["apu3"][1] : "0/0") : "&nbsp;"). "</TD>
									<TD class=leftE>". ($repper > 2 && isset($absdat['ap1']) ? ($absdat['ap1'][1]+$absdat['ap2'][1]+$absdat['ap3'][1]). "/". ($absdat['apu1'][1]+$absdat['apu2'][1]+$absdat['apu3'][1]) : "&nbsp;"). "</td>");
						unset($absdat);
					}
					echo("</TR>");

					echo("<TR><TH class=headleft>Resultaat</TH>");
					for($sx = 1; $sx <= $scnt; $sx++)
					{
						for($p=0;$p<4;$p++)
						{
							$pi = ($p + 1) % 4;
							if($pi==1)
								echo("<TD class=leftB>");
							else if ($pi==0)
								echo("<TD class=leftE>");
							else
								echo("<TD>");
							if(!isset($stcalp[$sx][$pi]))
								echo("&nbsp;");
							else if($pi == 2 && date("n") > 8)
								echo("&nbsp");
							else if($pi == 3 && (date("n") < 5 || date("n") > 8))
								echo("&nbsp");
							else if($stcalp[$sx][$pi] >= 11 && $stcalc[$sx][$pi] < 2 && $stcalzp[$sx][$pi] >= 16 && $stcalzo[$sx][$pi] < 2 && $stcalzc[$sx][$pi] < 3)
								echo("Bev");
							else
								echo("NB");
							echo("</TD>");
						}
					}
					echo("</TR>");
					
					echo("</TABLE>");
					echo("<P class=footer>Advies codes: Bev=Bevorderd, NB=Niet bevorderd</P>");
					$llnoffset += $llnperpage;
				} // End while for subgroups of students
			} // End if student for the group
		
			unset($stres);
		} // End for each group
  } // End if groups defined
      
  echo("</html>");
  
  function colored($res,$format1=false,$letter=false,$meta=false,$roundit=false)
  {
		 if($roundit)
		   $res=round($res);
		 if($format1 && $res > 0.0 && !$roundit)
				$res = number_format($res,1,',','.');
     $res2 = str_replace(',','.',$res);
		 if($letter)
		 {
				if($res2>=9.0)
					$res = "A";
				else if($res2 >= 7.7)
					$res = "B";
				else if($res2 >= 5.1)
					$res = "C";
				else if($res2 >= 4.1)
					$res = "D";
				else if($res >0)
					$res = "E";			 
		 }
		 if($res2 < 5.5 && $res2 > 0 && $format1)
			 if($meta)
				return("<SPAN class=redcolor><b>". $res. "</b></SPAN>");
			else
				return("<SPAN class=redcolor>". $res. "</SPAN>");
		 else
		 {
			 if($meta)
				 $res = "<B>". $res. "</b>";
			 return($res);
		 }
  }
?>
