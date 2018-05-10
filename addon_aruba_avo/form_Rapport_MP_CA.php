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
   
  $afwezigreden = array(1,2,3,4,5,26);
  $telaatreden = array(6,7,8,9,10,11,12,13,14,15,16,17,18,19);
  $groepfilter = "3%";
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
  
  if(isset($groups))
  {
    // First part of the page
    echo("<html><head><title>Rapport</title>");
	echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
	echo("</head><body link=blue vlink=blue>");
    echo '<LINK rel="stylesheet" type="text/css" href="style_Rapport_MP.css" title="style1">';

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
	  $pquery = "SELECT * FROM  period ORDER BY id";
	  $perdata = SA_loadquery($pquery);
	  foreach($perdata['id'] AS $pix => $pid)
	  {
	    $pdata[$pid]["sdate"] = $perdata["startdate"][$pix];
	    $pdata[$pid]["edate"] = $perdata["enddate"][$pix];
		$pdata[$pid]["state"] = $perdata["status"][$pix];
	  }

	  if(isset($students))
	  {
		for($sx = 1; $sx <= count($students['sid']); $sx++)
		{
 	      echo("<DIV class=leftpage>");
		  echo("<TABLE class=carapporttabel>");
		
		  
		  echo("<TR><TH class=cavakkenhdr rowspan=2>Vakken</th><TH class=trimester colspan=3>Trimesters</th><TH class=eindcijferhdr rowspan=2>Eindcijfer</th></tr>
		        <TR><TH class=caresultaathdr>1</th><TH class=caresultaathdr>2</th><TH class=caresultaathdr>3</th></tr>");
		  
		  // Get the student results 
		  $sres = SA_loadquery("SELECT period, result, shortname FROM gradestore LEFT JOIN subject USING(mid) LEFT JOIN period ON(period=id) WHERE sid=". $students['sid'][$sx]. " AND gradestore.year=\"". $schoolyear. "\" AND (status <> 'open' OR status IS NULL)");
			unset($stres);
		  if(isset($sres))
		    foreach($sres['period'] AS $rix => $perid)
					$stres[$sres['shortname'][$rix]][$perid] = $sres['result'][$rix];
		  unset($sres);
		  echo("<TR><TD class=cavak>Nederlands</TD>");
		  show_subjectresult($stres,"NED",$pdata);
		  echo("</tr>");
		  echo("<TR><TD class=cavak>Engels</TD>");
		  show_subjectresult($stres,"ENG",$pdata);
		  echo("</tr>");
		  echo("<TR><TD class=cavak>Spaans</TD>");
		  show_subjectresult($stres,"SPA",$pdata);
		  echo("</tr>");
		  echo("<TR><TD class=cavak>Papiamento</TD>");
		  show_subjectresult($stres,"PAP",$pdata);
		  echo("</tr>");
		  echo("<TR><TD class=cavak>Gechiedenis</TD>");
		  show_subjectresult($stres,"GES",$pdata);
		  echo("</tr>");
		  echo("<TR><TD class=cavak>Aardrijkskunde</TD>");
		  show_subjectresult($stres,"AK",$pdata);
		  echo("</tr>");
		  echo("<TR><TD class=cavak>Wiskunde</TD>");
		  show_subjectresult($stres,"WIS",$pdata);
		  echo("</tr>");
		  echo("<TR><TD class=cavak>Rekenen</TD>");
		  show_subjectresult_rek($stres,"REK",$pdata);
		  echo("</tr>");
		  echo("<TR><TD class=cavak>Natuurkunde (NaSk-1)</TD>");
		  show_subjectresult($stres,"NASK1",$pdata);
		  echo("</tr>");
		  echo("<TR><TD class=cavak>Scheikunde (NaSk-2)</TD>");
		  show_subjectresult($stres,"NASK2",$pdata);
		  echo("</tr>");
		  echo("<TR><TD class=cavak>Biologie</TD>");
		  show_subjectresult($stres,"BIO",$pdata);
		  echo("</tr>");
		  echo("<TR><TD class=cavak>Economie / M&O</TD>");
		  show_subjectresult($stres,"EC",$pdata);
		  echo("</tr>");
		  echo("<TR><TD class=cavak>Informatiekunde</TD>");
		  show_subjectresult($stres,"IK",$pdata);
		  echo("</tr>");
		  echo("<TR><TD class=cavak>Culturele en Kunstzinnige Vorming</TD>");
		  show_subjectresult($stres,"CKV",$pdata);
		  echo("</tr>");
		  echo("<TR><TD class=cavak>Lichamelijke Opvoeding</TD>");
		  show_subjectresult($stres,"LO",$pdata);
		  echo("</tr>");
		  echo("<TR><TD class=cavak>Godsdienst</TD>");
		  show_subjectresult($stres,"GD",$pdata);
		  echo("</tr>");

		  echo("</table><BR><BR><TABLE class=carapporttabel>");
		  echo("<TR><TD class=caabslabel>Lesuren te laat:</TD>");
		  // Get "Te laat" info
		  $latedata = SA_loadquery("SELECT SUM(IF(date >= '". $pdata[1]['sdate']. "' AND date <= '". $pdata[1]['edate']. "',1,0)) AS late1,
									SUM(IF(date >= '". $pdata[2]['sdate']. "' AND date <= '". $pdata[2]['edate']. "',1,0)) AS late2,
									SUM(IF(date >= '". $pdata[3]['sdate']. "' AND date <= '". $pdata[3]['edate']. "',1,0)) AS late3
									FROM absence LEFT JOIN absencereasons USING(aid)
									WHERE sid=". $students['sid'][$sx]. " AND acid=2");
		  echo("<TD class=caresultaat>". (isset($latedata['late1'][1]) ? $latedata['late1'][1] : "-"). "</TD>");
		  echo("<TD class=caresultaat>". (isset($latedata['late2'][1]) && $pdata[2]['state'] != "open" ? $latedata['late2'][1] : "&nbsp;"). "</TD>");
		  echo("<TD class=caresultaat>". (isset($latedata['late3'][1]) && $pdata[3]['state'] != "open" ? $latedata['late3'][1] : "&nbsp;"). "</TD>");
		  if($pdata[3]['state'] != "open" && isset($latedata['late1'][1]))
		    echo("<TD class=eindcijfer>". ($latedata['late1'][1] + $latedata['late2'][1] + $latedata['late3'][1]). "</td>");
		  else
		    echo("<TD class=eindcijfer>&nbsp</td>");
		  echo("</TR>");

		  echo("<TR><TD class=caabslabel>Lesuren afwezig:</TD>");
		  // Get "Afwezig" info
		  $absdata = SA_loadquery("SELECT SUM(IF(date >= '". $pdata[1]['sdate']. "' AND date <= '". $pdata[1]['edate']. "',1,0)) AS abs1,
									SUM(IF(date >= '". $pdata[2]['sdate']. "' AND date <= '". $pdata[2]['edate']. "',1,0)) AS abs2,
									SUM(IF(date >= '". $pdata[3]['sdate']. "' AND date <= '". $pdata[3]['edate']. "',1,0)) AS abs3
									FROM absence LEFT JOIN absencereasons USING(aid)
									WHERE sid=". $students['sid'][$sx]. " AND (acid=1 OR acid=4 OR acid=6)");
		  echo("<TD class=caresultaat>". (isset($absdata['abs1'][1]) ? $absdata['abs1'][1] : "-"). "</TD>");
		  echo("<TD class=caresultaat>". (isset($absdata['abs2'][1]) && $pdata[2]['state'] != "open" ? $absdata['abs2'][1] : "&nbsp;"). "</TD>");
		  echo("<TD class=caresultaat>". (isset($absdata['abs3'][1]) && $pdata[3]['state'] != "open" ? $absdata['abs3'][1] : "&nbsp;"). "</TD>");
		  if($pdata[3]['state'] != "open" && isset($absdata['abs1'][1]))
		    echo("<TD class=eindcijfer>". ($absdata['abs1'][1] + $absdata['abs2'][1] + $absdata['abs3'][1]). "</td>");
		  else
		    echo("<TD class=eindcijfer>&nbsp</td>");
		  echo("</TR>");
		  
		  
		  echo("</TABLE>");
		  echo("<P class=cadirsign>Handtekening Directeur:</p>");
		  
		  // Get the remarks and year results
		  unset($remp);
		  $rems = SA_loadquery("SELECT * FROM bo_opmrap_data WHERE sid=". $students['sid'][$sx]. " AND year='". $schoolyear. "'");
		  if(isset($rems))
		  {
		    foreach($rems['period'] AS $pix => $pid)
			{
			  $remp[$pid] = $rems['opmtext'][$pix];
			}
		  }
		  unset($yrres);
		  $yrres = SA_loadquery("SELECT * FROM bo_jaarresult_data WHERE sid=". $students['sid'][$sx]. " AND year='". $schoolyear. "'");
          
		  echo("</DIV><DIV class=rightpage>");
		  echo("<P class=yeardata>Ciclo Avansa - 3e leerjaar");
		  echo(" <span class=yearheading>". $schoolyear. "</SPAN></P>");
		  echo("<P class=studdata>Rapport van ". $students['firstname'][$sx]. " ". $students['lastname'][$sx]);
		  echo("<span class=yearheading>Klas ". $groups['groupname'][$gix]. "</SPAN></P>");
		  echo("<TABLE class=carighttable>");
		  echo("<TR><TD class=topblock colspan=2>");
		  echo("Rapport 1</TD><TD class=topblock>DATUM:</TD></TR>");
		  echo("<TD class=msign>Handtekening mentor:</TD><TD class=emptycenter>&nbsp;</TD><TD class=osign>Handtekening ouder/voogd:</TD></TR>");
		  echo("<TD class=mentorrem>Opmerking mentor:</TD><TD class=caopmtext colspan=2>");
		  if(isset($remp[1]))
		    echo("<SPAN class=opmtext>". $remp[1]. "</SPAN><BR>&nbsp;");
		  else
		    echo("<SPAN class=canoopmtext>&nbsp;<BR>&nbsp;<BR>&nbsp;<BR>&nbsp;</span>");
		  echo("</TD></TR>");
		  echo("</table><BR><TABLE class=carighttable>");
		  echo("<TR><TD class=topblock colspan=2>");
		  echo("Rapport 2</TD><TD class=topblock>DATUM:</TD></TR>");
		  echo("<TD class=msign>Handtekening mentor:</TD><TD class=emptycenter>&nbsp;</TD><TD class=osign>Handtekening ouder/voogd:</TD></TR>");
		  echo("<TD class=mentorrem>Opmerking mentor:</TD><TD class=caopmtext colspan=2>");
		  if(isset($remp[2]))
		    echo("<SPAN class=opmtext>". $remp[2]. "</SPAN><BR>&nbsp;");
		  else
		    echo("<SPAN class=canoopmtext>&nbsp;<BR>&nbsp;<BR>&nbsp;<BR>&nbsp;</span>");
		  echo("</TD></TR>");
		  echo("</table><BR><TABLE class=carighttable>");
		  echo("<TR><TD class=topblock colspan=2>");
		  echo("Rapport 3</TD><TD class=topblock>DATUM:</TD></TR>");
		  echo("<TD class=msign>Handtekening mentor:</TD><TD class=emptycenter>&nbsp;</TD><TD class=osign>Handtekening ouder/voogd:</TD></TR>");
		  echo("<TD class=mentorrem>Opmerking mentor:</TD><TD class=caopmtext colspan=2>");
		  if(isset($remp[3]))
		    echo("<SPAN class=opmtext>". $remp[3]. "</SPAN><BR>&nbsp;");
		  else
		    echo("<SPAN class=canoopmtext>&nbsp;<BR>&nbsp;<BR>&nbsp;<BR>&nbsp;</span>");
		  echo("</TD></TR>");
		  echo("</table><BR><TABLE class=carighttable><TR><TD>");
		  echo((isset($yrres['result'][1]) && $yrres['result'][1] == "NIET OVER" ? "&#x2611;" : "&#x2610"). 
		         " Niet bevorderd");
		  echo("<BR><BR>". (isset($yrres['result'][1]) && $yrres['result'][1] == "OVER" ? "&#x2611;" : "&#x2610"). 
		         " Bevorderd naar  leerjaar 4:");
		  echo("<SPAN class=packagetype>". (isset($yrres['advice'][1]) && $yrres['advice'][1] == "MM" ? "&#x2611;" : "&#x2610"). 
		         " MM</SPAN>");
		  echo("<SPAN class=packagetype>". (isset($yrres['advice'][1]) && $yrres['advice'][1] == "NW" ? "&#x2611;" : "&#x2610"). 
		         " NW</SPAN>");
		  echo("<SPAN class=packagetype>". (isset($yrres['advice'][1]) && $yrres['advice'][1] == "HUM" ? "&#x2611;" : "&#x2610"). 
		         " HUM</SPAN><BR>&nbsp;");
		  echo("</TD></TR></TABLE>");
		  echo("</DIV>");
		  echo("<P class=pagebreak>&nbsp;</P>");
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
  
  function show_subjectresult($stres,$vkn,$pdata)
  {
    echo("<TD class=resultaat>");
	if(isset($stres[$vkn][1]) && $stres[$vkn][1] > 0.0)
	  echo(colored(number_format($stres[$vkn][1],1,',','.')));
	else
	  echo("&nbsp;");
	echo("</td>");
    echo("<TD class=resultaat>");
	if(isset($stres[$vkn][2]) && $pdata[2]['state'] != "open" && $stres[$vkn][2] > 0.0)
	  echo(colored(number_format($stres[$vkn][2],1,',','.')));
	else
	  echo("&nbsp;");
	echo("</td>");
    echo("<TD class=resultaat>");
	if(isset($stres[$vkn][3]) && $pdata[3]['state'] != "open" && $stres[$vkn][3] > 0.0)
	  echo(colored(number_format($stres[$vkn][3],1,',','.')));
	else
	  echo("&nbsp;");
	echo("</td>");
    echo("<TD class=resultaat>");
	if(isset($stres[$vkn][0]) && $pdata[3]['state'] != "open" && $stres[$vkn][0] > 0.0)
	  echo(colored(number_format($stres[$vkn][0],0,',','.')));
	else
	  echo("&nbsp;");
	echo("</td><TD class=spatieresultaat>&nbsp;</td>");
  }

  function show_subjectresult_rek($stres,$vkn,$pdata)
  {
    echo("<TD class=resultaat>");
	if(isset($stres[$vkn][1]) && $stres[$vkn][1] > 0.0)
	  echo(colored_rek(number_format($stres[$vkn][1],1,',','.')));
	else
	  echo("&nbsp;");
	echo("</td>");
    echo("<TD class=resultaat>");
	if(isset($stres[$vkn][2]) && $pdata[2]['state'] != "open" && $stres[$vkn][2] > 0.0)
	  echo(colored_rek(number_format($stres[$vkn][2],1,',','.')));
	else
	  echo("&nbsp;");
	echo("</td>");
    echo("<TD class=resultaat>");
	if(isset($stres[$vkn][3]) && $pdata[3]['state'] != "open" && $stres[$vkn][3] > 0.0)
	  echo(colored_rek(number_format($stres[$vkn][3],1,',','.')));
	else
	  echo("&nbsp;");
	echo("</td>");
    echo("<TD class=resultaat>");
	if(isset($stres[$vkn][0]) && $pdata[3]['state'] != "open" && $stres[$vkn][0] > 0.0)
	  echo(colored_rek(number_format($stres[$vkn][0],0,',','.')));
	else
	  echo("&nbsp;");
	echo("</td><TD class=spatieresultaat>&nbsp;</td>");
  }
  function colored_rek($res)
  {
     $res2 = str_replace(',','.',$res);
	 if($res2 < 5.5)
	   return("<SPAN class=redcolor>Onv</SPAN>");
	 else if($res2 < 8.0)
	   return("Vold");
	 else
	   return("Goed");
  }
?>
