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
  $groepfilter = "2%' OR groupname LIKE '1%";
  //$groepfilter = "1%";
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
		  echo("<TABLE class=rapporttabel>");
		
		  
		  echo("<TR><TH class=nolines>&nbsp</th><TH class=trimester colspan=5>Trimester</th><TH class=nolines>&nbsp;</th></tr>
		        <TR><TH class=vak>Vak:</th><TH class=resultaat>1</th><TH class=resultaat>2</th><TH class=resultaat>3</th>
				    <TH class=spatieresultaat>&nbsp;</th><TH class=resultaat>4</th><TH class=spatieresultaat>&nbsp;</th>
					<TH class=leergebiedheader>Leergebied</TH></TR>");
		  
		  // Get the student results 
		  $sres = SA_loadquery("SELECT period, result, shortname FROM gradestore LEFT JOIN subject USING(mid) LEFT JOIN period ON(period=id) WHERE sid=". $students['sid'][$sx]. " AND gradestore.year=\"". $schoolyear. "\" AND (status <> 'open' OR status IS NULL)");
		  if(isset($sres))
		    foreach($sres['period'] AS $rix => $perid)
			  $stres[$sres['shortname'][$rix]][$perid] = $sres['result'][$rix];
		  unset($sres);
		  echo("<TR><TD class=vak>Kennis van het Geestelijk leven</TD>");
		  show_subjectresult($stres,"KGL",$pdata);
		  echo("<TD class=nolines>&nbsp</td></tr>");
		  echo("<TR><TD class=vak>Persoonlijke Vorming</TD>");
		  show_subjectresult($stres,"PV",$pdata);
		  echo("<TD class=leergebied>Individu</td></tr>");
		  echo("<TR><TD class=vak>Lichamelijke Opvoeding</TD>");
		  show_subjectresult($stres,"LO",$pdata);
		  echo("<TD class=nolines>&nbsp</td></tr>");
		  echo("<TR><TD class=nolines colspan=7>&nbsp;</td></tr>");

		  echo("<TR><TD class=vak>Algemene Sociale Wetenschappen</TD>");
		  show_subjectresult($stres,"ASW",$pdata);
		  echo("<TD class=leergebied>Maatschappij</td></tr>");
		  echo("<TR><TD class=nolines colspan=7>&nbsp;</td></tr>");

		  echo("<TR><TD class=vak>Papiamento</TD>");
		  show_subjectresult($stres,"PAP",$pdata);
		  echo("<TD class=leergebied rowspan=2>Taal & Communicatie:<BR>moedertaal en instructietaal</td></tr>");
		  echo("<TR><TD class=vak>Nederlands</TD>");
		  show_subjectresult($stres,"NED",$pdata);
		  echo("</tr>");
		  echo("<TR><TD class=nolines colspan=7>&nbsp;</td></tr>");

		  echo("<TR><TD class=vak>Engels</TD>");
		  show_subjectresult($stres,"ENG",$pdata);
		  echo("<TD class=leergebied rowspan=2>Taal & Communicatie:<BR>moderne en vreemde talen</td></tr>");
		  echo("<TR><TD class=vak>Spaans</TD>");
		  show_subjectresult($stres,"SPA",$pdata);
		  echo("</tr>");
		  echo("<TR><TD class=nolines colspan=7>&nbsp;</td></tr>");

		  echo("<TR><TD class=vak>Culturele en Kunstzinnige Vorming</TD>");
		  show_subjectresult($stres,"CKV",$pdata);
		  echo("<TD class=leergebied>Kunst en Cultuur</td></tr>");
		  echo("<TR><TD class=nolines colspan=7>&nbsp;</td></tr>");

		  echo("<TR><TD class=vak>Wiskunde</TD>");
		  show_subjectresult($stres,"WIS",$pdata);
		  echo("<TD class=leergebied ROWSPAN=2>Wiskunde</td></tr>");

		  echo("<TR><TD class=vak>Rekenen</TD>");
		  show_subjectresult($stres,"REK",$pdata);
		  echo("</tr>");
		  echo("<TR><TD class=nolines colspan=7>&nbsp;</td></tr>");

		  echo("<TR><TD class=vak>Natuur & Techniek</TD>");
		  show_subjectresult($stres,"N&T",$pdata);
		  echo("<TD class=leergebied>Natuur</td></tr>");
		  echo("<TR><TD class=nolines colspan=7>&nbsp;</td></tr>");

		  echo("<TR><TD class=vak>Informatiekunde</TD>");
		  show_subjectresult($stres,"IK",$pdata);
		  echo("<TD class=leergebied>Onderwijsondersteuning</td></tr>");
		  echo("<TR><TD class=mentorvak>* Mentor/ Studieles<TD class=nolines colspan=6>&nbsp;</td></tr>");

		  echo("<TR><TD class=nolines colspan=7>&nbsp;</td></tr>");
		  echo("<TR><TD class=nolines colspan=7>&nbsp;</td></tr>");
		  echo("<TR><TD class=abslabel>Lesuren te laat:</TD>");
		  // Get "Te laat" info
		  $latedata = SA_loadquery("SELECT SUM(IF(date >= '". $pdata[1]['sdate']. "' AND date <= '". $pdata[1]['edate']. "',1,0)) AS late1,
									SUM(IF(date >= '". $pdata[2]['sdate']. "' AND date <= '". $pdata[2]['edate']. "',1,0)) AS late2,
									SUM(IF(date >= '". $pdata[3]['sdate']. "' AND date <= '". $pdata[3]['edate']. "',1,0)) AS late3
									FROM absence LEFT JOIN absencereasons USING(aid)
									WHERE sid=". $students['sid'][$sx]. " AND acid=2");
		  echo("<TD class=resultaat>". (isset($latedata['late1'][1]) ? $latedata['late1'][1] : "-"). "</TD>");
		  echo("<TD class=resultaat>". (isset($latedata['late2'][1]) && $pdata[2]['state'] != "open" ? $latedata['late2'][1] : "&nbsp;"). "</TD>");
		  echo("<TD class=resultaat>". (isset($latedata['late3'][1]) && $pdata[3]['state'] != "open" ? $latedata['late3'][1] : "&nbsp;"). "</TD>");
		  echo("<TD class=spatieresultaat>&nbsp;</TD>");
		  if($pdata[3]['state'] != "open" && isset($latedata['late1'][1]))
		    echo("<TD class=resultaat>". ($latedata['late1'][1] + $latedata['late2'][1] + $latedata['late3'][1]). "</td>");
		  else
		    echo("<TD class=resultaat>&nbsp</td>");
		  echo("</TR>");
		  
		  echo("<TR><TD class=abslabel>Lesuren afwezig:</TD>");
		  // Get "Afwezig" info
		  $absdata = SA_loadquery("SELECT SUM(IF(date >= '". $pdata[1]['sdate']. "' AND date <= '". $pdata[1]['edate']. "',1,0)) AS abs1,
									SUM(IF(date >= '". $pdata[2]['sdate']. "' AND date <= '". $pdata[2]['edate']. "',1,0)) AS abs2,
									SUM(IF(date >= '". $pdata[3]['sdate']. "' AND date <= '". $pdata[3]['edate']. "',1,0)) AS abs3
									FROM absence LEFT JOIN absencereasons USING(aid)
									WHERE sid=". $students['sid'][$sx]. " AND (acid=1 OR acid=4 OR acid=6)");
		  echo("<TD class=resultaat>". (isset($absdata['abs1'][1]) ? $absdata['abs1'][1] : "-"). "</TD>");
		  echo("<TD class=resultaat>". (isset($absdata['abs2'][1]) && $pdata[2]['state'] != "open" ? $absdata['abs2'][1] : "&nbsp;"). "</TD>");
		  echo("<TD class=resultaat>". (isset($absdata['abs3'][1]) && $pdata[3]['state'] != "open" ? $absdata['abs3'][1] : "&nbsp;"). "</TD>");
		  echo("<TD class=spatieresultaat>&nbsp;</TD>");
		  if($pdata[3]['state'] != "open" && isset($absdata['abs1'][1]))
		    echo("<TD class=resultaat>". ($absdata['abs1'][1] + $absdata['abs2'][1] + $absdata['abs3'][1]). "</td>");
		  else
		    echo("<TD class=resultaat>&nbsp</td>");
		  echo("</TR>");
		  
		  
		  echo("</TABLE>");
		  echo("<P class=signdir>Handtekening directeur:</p>");
		  
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
		  echo("<P class=yeardata>Ciclo basico ");
		  if(substr($groups['groupname'][$gix],0,1) == "1")
			echo("1ste");
		  else
			echo("2de");
		  echo(" leerjaar <span class=yearheading>". $schoolyear. "</SPAN></P>");
		  echo("<P class=studdata>Rapport van ". $students['firstname'][$sx]. " ". $students['lastname'][$sx]);
		  echo("<span class=yearheading>Klas ". $groups['groupname'][$gix]. "</SPAN></P>");
		  echo("<TABLE class=righttable>");
		  echo("<TR><TD class=resultblock>");
		  echo("* Opmerkingen bij het 1e rapport:<SPAN class=rapdate>Datum: .............</SPAN><BR>");
		  if(isset($remp[1]))
		    echo("<SPAN class=opmtext>". $remp[1]. "</SPAN><BR>");
		  else
		    echo("<SPAN class=noopmtext>&nbsp;<BR>&nbsp;<BR>&nbsp;</span>");
		  echo("<BR>Handtekening:
			   <BR>Mentor: .....................................
			   <BR><BR><BR><BR><BR>Ouders/ Verzorgers: .....................................</TD></TR>");
		  echo("<TR><TD class=resultblock>");
		  echo("* Opmerkingen bij het 2e rapport:<SPAN class=rapdate>Datum: .............</SPAN><BR>");
		  if(isset($remp[2]))
		    echo("<SPAN class=opmtext>". $remp[2]. "</SPAN><BR>");
		  else
		    echo("<SPAN class=noopmtext>&nbsp;<BR>&nbsp;<BR>&nbsp;</span>");
		  echo("<BR>Handtekening:
			   <BR>Mentor: .....................................
			   <BR><BR><BR><BR><BR>Ouders/ Verzorgers: .....................................</TD></TR>");
		  echo("<TR><TD class=resultblock>");
		  echo("* Opmerkingen bij het 3e rapport:<SPAN class=rapdate>Datum: .............</SPAN><BR>");
		  if(isset($remp[3]))
		    echo("<SPAN class=opmtext>". $remp[3]. "</SPAN><BR>");
		  else
		    echo("<SPAN class=noopmtext>&nbsp;<BR>&nbsp;<BR>&nbsp;</span>");
		  if(substr($groups['groupname'][$gix],0,1) == "1")
		  {
		    echo("<BR>". (isset($yrres['result'][1]) && $yrres['result'][1] == "OVER" ? "&#x2611;" : "&#x2610"). 
		         " Bevorderd naar Ciclo Basico leerjaar 2");
		    echo("<BR>". (isset($yrres['result'][1]) && $yrres['result'][1] == "NIET OVER" ? "&#x2611;" : "&#x2610"). 
		         " Niet bevorderd naar  Ciclo Basico leerjaar 2");
		    echo("<BR>". (isset($yrres['advice'][1]) && $yrres['advice'][1] != "" ? "&#x2611;" : "&#x2610"). 
		         " Advies ". (isset($yrres['advice'][1]) && $yrres['advice'][1] != "" ? $yrres['advice'][1] : "____________________________"));
		  }
		  else
		  {
		    echo("<BR>". (isset($yrres['result'][1]) && $yrres['result'][1] == "OVER" ? "&#x2611;" : "&#x2610"). 
		         " Bevorderd naar Ciclo Avansa 1 - leerjaar 3");
		    echo("<BR>". (isset($yrres['result'][1]) && $yrres['result'][1] == "NIET OVER" ? "&#x2611;" : "&#x2610"). 
		         " Niet bevorderd naar Ciclo Avansa 1 - leerjaar 3");
		    echo("<BR>". (isset($yrres['advice'][1]) && $yrres['advice'][1] != "" ? "&#x2611;" : "&#x2610"). 
		         " Verwezen ". (isset($yrres['advice'][1]) && $yrres['advice'][1] != "" ? " naar ". $yrres['advice'][1] : ""));
		  }
		  echo("<BR><BR>Handtekening:
			    <BR>Mentor: .....................................</TD></TR>");
/*		  if(substr($groups['groupname'][$gix],0,1) == 1)
		    echo("Ciclo Basico 2");
		  else
		    echo("Ciclo Avansa 1");
		  echo("<BR><SPAN class=resselect>0</SPAN>Niet over<BR><SPAN class=resselect>0</SPAN>Advies<BR><BR>");
		  echo("<BR>Handtekening Mentor:<BR><BR>Handtekening Directeur v.d. School:</TD></TR>"); */
		  echo("</TABLE>");
		  echo("</DIV>");
		  echo("<P class=pagebreak>&nbsp;</P>");
		} // End while for subgroups of students
	  } // End if student for the group
	
	  unset($stres);
	} // End for each group
  } // End if groups defined
      
  echo("</html>");
  
  function show_subjectresult($stres,$vkn,$pdata)
  {
    echo("<TD class=resultaat>");
	if(isset($stres[$vkn][1]))
	  echo(colored(number_format($stres[$vkn][1],1,',','.')));
	else
	  echo("&nbsp;");
	echo("</td>");
    echo("<TD class=resultaat>");
	if(isset($stres[$vkn][2]) && $pdata[2]['state'] != "open")
	  echo(colored(number_format($stres[$vkn][2],1,',','.')));
	else
	  echo("&nbsp;");
	echo("</td>");
    echo("<TD class=resultaat>");
	if(isset($stres[$vkn][3]) && $pdata[3]['state'] != "open")
	  echo(colored(number_format($stres[$vkn][3],1,',','.')));
	else
	  echo("&nbsp;");
	echo("</td><TD class=spatieresultaat>&nbsp;</td>");
    echo("<TD class=resultaat>");
	if(isset($stres[$vkn][0]) && $pdata[3]['state'] != "open")
	  echo(colored(number_format($stres[$vkn][0],0,',','.')));
	else
	  echo("&nbsp;");
	echo("</td><TD class=spatieresultaat>&nbsp;</td>");
  }
  function colored($res)
  {
     $res2 = str_replace(',','.',$res);
	 if($res2 < 5.5)
	   return("<SPAN class=redcolor>". $res. "</SPAN>");
	 else
	   return($res);
  }
?>
