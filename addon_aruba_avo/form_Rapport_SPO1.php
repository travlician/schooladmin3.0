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
  $vakcats = array("Dominio di Idioma","Materia exacto","Formacion general","Arte y educacion fisico","Materia practico","Pasantia");
 
  $vakhead["Dominio di Idioma"] = array("Pa","En");
  // $vakhead["Dominio di Idioma"] = array("Pa","Ne","En"); tweede en derde
  $vakhead["Materia exacto"] = array("Rek","If");
  $vakhead["Formacion general"] = array("Prf","Prt","Pvl");
  $vakhead["Arte y educacion fisico"] = array("Mu","Lo","Exp");
  $vakhead["Materia practico"] = array("Pca","Ppd");
  $vakhead["Pasantia"] = array("Psi","Pse");
		
	$absrqr = SA_loadquery("SELECT aid FROM absencereasons WHERE acid=1");
	foreach($absrqr['aid'] AS $aid)
		$afwezigreden[$aid]=$aid;
	$laterqr = SA_loadquery("SELECT aid FROM absencereasons WHERE acid=2");
	foreach($laterqr['aid'] AS $aid)
		$telaatreden[$aid]=$aid;
  $groepfilter = "S_2_' OR groupname LIKE 'S_1_";
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
  $groups = SA_loadquery("SELECT * FROM sgroup LEFT JOIN ". $teachercode. " ON(tid_mentor=tid) LEFT JOIN teacher USING(tid) WHERE active=1 AND groupname LIKE '". $groepfilter. "' ORDER BY groupname");
  
  // Get a list of last test dates for periods
  //$perends = SA_loadquery("SELECT period,CEIL(date) AS edate FROM testdef GROUP BY period ORDER BY period");
  
  if(isset($groups))
  {
    // First part of the page
    echo("<html><head><title>Rapport</title>");
		echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
		echo("</head><body link=blue vlink=blue>");
    echo '<LINK rel="stylesheet" type="text/css" href="style_Rapport_SPO.css" title="style1">';

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
				// Show frontpage
				echo("<DIV class=frontpage><P class=logosubscript>Scol Practico pa Ofishi<BR>Locatie ". (substr($groups['groupname'][$gix],0,2) == "SC" ? "Santa Cruz" : "Savaneta"). "</p><P class=rapdblock><SPAN class=raplabel>Alumno:</SPAN>");
				echo("<SPAN class=rapdata>". $students['firstname'][1+$llnoffset]. " ". $students['lastname'][1+$llnoffset]. "</SPAN>");
				echo("<BR><SPAN class=raplabel>Klas:</SPAN>");
				echo("<SPAN class=rapdata>". $groups['groupname'][$gix]. "</SPAN>");
				echo("<BR><SPAN class=raplabel>Mentor:</SPAN>");
				echo("<SPAN class=rapdata>". get_initials($groups['firstname'][$gix]). " ". $groups['lastname'][$gix]. "</SPAN></P>");
				echo("<P class=yearinfo>Aña Escolar<BR>". $schoolyear. "</p>");
				echo("</DIV><P class=pagebreak>&nbsp;</P>");
				// On to the page with data
				echo("<DIV class=leftpage>");
				for($sx = 1; $sx <= $scnt; $sx++)
				{
					echo("<TABLE class=cijferlijst>");
					echo("<TR><TD class=greybgcenter colspan=2><SPAN class=schoolname>Scol Practico pa Ofishi</SPAN><BR><SPAN class=yeartext>- di ". (substr($groups['groupname'][$gix],2,1) == 1 ? "Prome" : "Dos"). " aña -</span></td>
					<td rowspan=3 class=greybgcenter>1<sup>e</sup><BR><BR>r<BR>a<BR>p<BR>o<BR>r<BR>t</td><td class=sepcolw></td>
					<td rowspan=3 class=greybgcenter>2<sup>o</sup><BR><BR>r<BR>a<BR>p<BR>o<BR>r<BR>t</td><td class=sepcolw></td>
					<td rowspan=3 class=greybgcenter>3<sup>er</sup><BR><BR>r<BR>a<BR>p<BR>o<BR>r<BR>t</td><td class=sepcolw></td>
					<td rowspan=3 class=greybgcenter>ultimo</sup><BR><BR>r<BR>a<BR>p<BR>o<BR>r<BR>t</td></tr>
					<tr><TD colspan=2>Nomber: ". $students['firstname'][1+$llnoffset]. " ". $students['lastname'][1+$llnoffset]. "</td></tr>
					<tr><TD colspan=2>Klas: ". $groups['groupname'][$gix]. "</td></tr>");
					echo("<TR><TD class=thickLRUcenter rowspan=2>Vakken</TD><TD class=thickLRUcenter colspan=4><B>Rapport</B></TD></TR>");
					echo("<TR><TD class=resultcol>1</TD><TD class=resultcol>2</TD><TD class=resultcol>3</TD><TD class=resultcolE>Eind</TD></TR>");
				}
				
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
					$sres = SA_loadquery("SELECT SUM(IF(date >= '". $pdata[1]['sdate']. "' AND date <= '". $pdata[1]['edate']. "' AND acid=1,1,0)) AS afw1, SUM(IF(date >= '". $pdata[2]['sdate']. "' AND date <= '". $pdata[2]['edate']. "' AND acid=1,1,0)) AS afw2, SUM(IF(date >= '". $pdata[3]['sdate']. "' AND date <= '". $pdata[3]['edate']. "' AND acid=1,1,0)) AS afw3, SUM(IF(date >= '". $pdata[1]['sdate']. "' AND date <= '". $pdata[1]['edate']. "' AND acid=2,1,0)) AS late1, SUM(IF(date >= '". $pdata[2]['sdate']. "' AND date <= '". $pdata[2]['edate']. "' AND acid=2,1,0)) AS late2, SUM(IF(date >= '". $pdata[3]['sdate']. "' AND date <= '". $pdata[3]['edate']. "' AND acid=2,1,0)) AS late3 FROM absence LEFT JOIN absencereasons USING(aid) WHERE sid=". $students['sid'][$llnoffset+$sx]);
					if(isset($sres))
					{
						if($sres['afw1'][1] > 0) $stabs[$llnoffset+$sx][1] = $sres['afw1'][1];
						if($sres['afw2'][1] > 0) $stabs[$llnoffset+$sx][2] = $sres['afw2'][1];
						if($sres['afw3'][1] > 0) $stabs[$llnoffset+$sx][3] = $sres['afw3'][1];
						if($sres['late1'][1] > 0) $stlate[$llnoffset+$sx][1] = $sres['late1'][1];
						if($sres['late2'][1] > 0) $stlate[$llnoffset+$sx][2] = $sres['late2'][1];
						if($sres['late3'][1] > 0) $stlate[$llnoffset+$sx][3] = $sres['late3'][1];
					}
					unset($sres);
				}
				

				
				foreach($vakcats AS $vk)
				{
					$lastsubj = end($vakhead[$vk]);
				foreach($vakhead[$vk] AS $vkn)
				{
				 if((substr($groups['groupname'][$gix],0,1) == 1 && $vkn != "Asw-cb2") || 
						(substr($groups['groupname'][$gix],0,1) == 2 && $vkn != "asw"))
				 {
					$lastmark = ($lastsubj == $vkn);
					echo("<TD class=thickLR". ($lastmark ? "B" : ""). ">". $vakdesc[$vk][$vkn]. "</TD>");
						for($sx = 1; $sx <= $scnt; $sx++)
						{
						echo("<TD class=resultcol". ($lastmark ? "B" : ""). ">");
					if(isset($stres[$llnoffset+$sx][$vkn][1]))
						if($stres[$llnoffset+$sx][$vkn][1] > 0)
							echo(colored(number_format($stres[$llnoffset+$sx][$vkn][1],1,',','.')));
						else
							echo($stres[$llnoffset+$sx][$vkn][1]);
					else
						echo("&nbsp;");
					echo("</TD>");
						echo("<TD class=resultcol". ($lastmark ? "B" : ""). ">");
					if(isset($stres[$llnoffset+$sx][$vkn][2]))
						if($stres[$llnoffset+$sx][$vkn][2] > 0)
							echo(colored(number_format($stres[$llnoffset+$sx][$vkn][2],1,',','.')));
						else
							echo($stres[$llnoffset+$sx][$vkn][2]);
					else
						echo("&nbsp;");
					echo("</TD>");
						echo("<TD class=resultcol". ($lastmark ? "B" : ""). ">");
					if(isset($stres[$llnoffset+$sx][$vkn][3]))
						if($stres[$llnoffset+$sx][$vkn][3] > 0)
							echo(colored(number_format($stres[$llnoffset+$sx][$vkn][3],1,',','.')));
						else
							echo($stres[$llnoffset+$sx][$vkn][3]);
					else
						echo("&nbsp;");
					echo("</TD>");
						echo("<TD class=resultcolE". ($lastmark ? "B" : ""). ">");
					if(isset($stres[$llnoffset+$sx][$vkn][0]) && isset($stres[$llnoffset+$sx][$vkn][3]))
						echo(colored($stres[$llnoffset+$sx][$vkn][0]));
					else
						echo("&nbsp;");
					echo("</TD>");
						}
					echo("</TR>");
				 } 
				} // End for each subject
				} // End subject categories
				//echo("<TR><TD class=thickLR><CENTER><B>Gedrag</B></CENTER></TD><TD class=resultcol>&nbsp;</TD><TD class=resultcol>&nbsp;</TD><TD class=resultcolE>&nbsp;</TD></TR>");		  
				//echo("<TR><TD class=thickLR><CENTER><B>Vlijt</B></CENTER></TD><TD class=resultcol>&nbsp;</TD><TD class=resultcol>&nbsp;</TD><TD class=resultcolE>&nbsp;</TD></TR>");	
				echo("<TR><TD class=thickLR><CENTER><B>Verzuim</B></CENTER></TD><TD class=resultcol>". (isset($stabs[$llnoffset+1][1]) ? $stabs[$llnoffset+1][1] : "&nbsp;"). "</TD><TD class=resultcol>". (isset($stabs[$llnoffset+1][2]) ?  $stabs[$llnoffset+1][2] : "&nbsp;"). "</TD><TD class=resultcolE>". (isset($stabs[$llnoffset+1][3]) ? $stabs[$llnoffset+1][3] : "&nbsp;"). "</TD></TR>");		  
				echo("<TR><TD class=thickLRB><CENTER><B>Te laat</B></CENTER></TD><TD class=resultcolB>". (isset($stlate[$llnoffset+1][1]) ? $stlate[$llnoffset+1][1] : "&nbsp;"). "</TD><TD class=resultcolB>". (isset($stlate[$llnoffset+1][2]) ? $stlate[$llnoffset+1][2] : "&nbsp;"). "</TD><TD class=resultcolEB>". (isset($stlate[$llnoffset+1][3]) ? $stlate[$llnoffset+1][3] : "&nbsp;"). "</TD></TR>");
				echo("<TR><TD>&nbsp</TD></TR>");
				echo("<TR><TD class=thickfullcenter COLSPAN=4><B>Persoonlijke kwaliteiten</B><BR><SPAN style='background-color: green'>&nbsp;&nbsp;</span> = Voldoende<SPAN class=legenda><SPAN style='background-color: orange'>&nbsp;&nbsp;</span> = Matig</SPAN><SPAN class=legenda><SPAN style='background-color: red'>&nbsp;&nbsp;</span> = Onvoldoende</SPAN></TD></TR>");
				$pkres = getPKs($students['sid'][$llnoffset+1]);
				echo("<TR><TD class=thickLR><B>Inzet / motivatie</B> <i>(Empuhe motivacion)</i></td><td class=resultcol>". showPK($pkres,"Inz/Mot",1). "
				</TD><td class=resultcol>". showPK($pkres,"Inz/Mot",2). "</TD><td class=resultcolE>". showPK($pkres,"Inz/Mot",3). "</TD></TR>");
				echo("<TR><TD class=thickLR><B>Concentratie</B> <i>(Concentracion)</i></td><td class=resultcol>". showPK($pkres,"Conc",1). "</TD><td class=resultcol>". showPK($pkres,"Conc",2). "</TD><td class=resultcolE>". showPK($pkres,"Conc",3). "</TD></TR>");
				echo("<TR><TD class=thickLR><B>Werkverzorging</B> <i>(Cuido di trabao)</i></td><td class=resultcol>". showPK($pkres,"Werkverz",1). "</TD><td class=resultcol>". showPK($pkres,"Werkverz",2). "</TD><td class=resultcolE>". showPK($pkres,"Werkverz",3). "</TD></TR>");
				echo("<TR><TD class=thickLR><B>Huiswerkattitude</B> <i>(Actitud pa cu huiswerk)</i></td><td class=resultcol>". showPK($pkres,"HWerk",1). "</TD><td class=resultcol>". showPK($pkres,"HWerk",2). "</TD><td class=resultcolE>". showPK($pkres,"HWerk",3). "</TD></TR>");
				echo("<TR><TD class=thickLR><B>Omgaan met afspraken, regels en procedures</B> <i>(Anda cu reglanan I palabracionan)</i></td><td class=resultcol>". showPK($pkres,"Omgaan ARP",1). "</TD><td class=resultcol>". showPK($pkres,"Omgaan ARP",2). "</TD><td class=resultcolE>". showPK($pkres,"Omgaan ARP",3). "</TD></TR>");
				echo("<TR><TD class=thickLR><B>Tempo</B></td><td class=resultcol>". showPK($pkres,"Tempo",1). "</TD><td class=resultcol>". showPK($pkres,"Tempo",2). "</TD><td class=resultcolE>". showPK($pkres,"Tempo",3). "</TD></TR>");
				echo("<TR><TD class=thickLRB><B>Sociaal gedrag</B> <i>(Comportacion Social)</i></td><td class=resultcolB>". showPK($pkres,"SocGedr",1). "</TD><td class=resultcolB>". showPK($pkres,"SocGedr",2). "</TD><td class=resultcolEB>". showPK($pkres,"SocGedr",3). "</TD></TR>");
				echo("</TABLE>");
				echo("<P class=dirsign>Handtekening Directrice:</P>");          
				echo("</DIV><DIV class=rightpage>");
				echo("<TABLE class=opmblock><TR><TD>Rapport 1</TD><TD>Datum:</TD></TR>");
				echo("<TR><TD>Handtekening Mentor:<BR><BR>&nbsp;</TD><TD>Handtekening Ouder/Voogd:<BR><BR>&nbsp;</TD></TR>");
				echo("<TR><TD colspan=2>Advies:</TD></TR>");
				echo("<TR><TD colspan=2>Opmerking Mentor:<BR><BR><BR><BR>&nbsp;</TD></TR>");
				echo("</TABLE>");
				echo("<BR><TABLE class=opmblock><TR><TD>Rapport 2</TD><TD>Datum:</TD></TR>");
				echo("<TR><TD>Handtekening Mentor:<BR><BR>&nbsp;</TD><TD>Handtekening Ouder/Voogd:<BR><BR>&nbsp;</TD></TR>");
				echo("<TR><TD colspan=2>Advies:</TD></TR>");
				echo("<TR><TD colspan=2>Opmerking Mentor:<BR><BR><BR><BR>&nbsp;</TD></TR>");
				echo("</TABLE>");
				echo("<BR><TABLE class=opmblock><TR><TD>Rapport 3</TD><TD>Datum:</TD></TR>");
				echo("<TR><TD>Handtekening Mentor:<BR><BR>&nbsp;</TD><TD>Handtekening Ouder/Voogd:<BR><BR>&nbsp;</TD></TR>");
				echo("<TR><TD colspan=2>Advies:</TD></TR>");
				echo("<TR><TD colspan=2>Opmerking Mentor:<BR><BR><BR><BR>&nbsp;</TD></TR>");
				echo("</TABLE>");
				echo("<BR><TABLE class=opmblock><TR><TD>&nbsp; &#x2610; Naar Ciclo Basico ....</TD><TD>&nbsp; &#x2610; Naar Ciclo Avansa ....</TD></TR></TABLE>");
				echo("<BR><TABLE class=opmblock><TR><TD>&nbsp; &#x2610; Verwezen naar: ................................................</TD></TR></TABLE>");
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
	 if($res2 < 5.5)
	   return("<SPAN class=redcolor>". $res. "</SPAN>");
	 else
	   return($res);
  }
	
	function ThreeBar($Vper,$Mper,$Oper)
	{
		return("<SPAN style='width: 1px; height: 13px;  background-color: white; display: inline-block;'>&nbsp;</span><SPAN style='width: 8px; height: ". round($Vper * 13 / 100,0). "px;  background-color: green; vertical-align: bottom; display: inline-block;'>&nbsp;</span><SPAN style='width: 8px; height: ". round($Mper * 13 / 100,0). "px;  background-color: orange; vertical-align: bottom; display: inline-block;'>&nbsp;</span><SPAN style='width: 8px; height: ". round($Oper * 13 / 100,0). "px;  background-color: red; vertical-align: bottom; display: inline-block;'>&nbsp;</span><SPAN style='width: 1px; height: 13px;  background-color: white; display: inline-block;'>&nbsp;</span>");
	}
	
	function GetPKs($sid)
	{
		global $schoolyear;
		$pkqry = "SELECT aspect";
		$pkqry .= ", SUM(IF(period=1,1,0)) AS t1, SUM(IF(period=1 AND xstatus='V',1,0)) AS v1, SUM(IF(period=1 AND xstatus='M',1,0)) AS m1, SUM(IF(period=1 AND xstatus='O',1,0)) AS o1";
		$pkqry .= ", SUM(IF(period=2,1,0)) AS t2, SUM(IF(period=2 AND xstatus='V',1,0)) AS v2, SUM(IF(period=2 AND xstatus='M',1,0)) AS m2, SUM(IF(period=2 AND xstatus='O',1,0)) AS o2";
		$pkqry .= ", SUM(IF(period=3,1,0)) AS t3, SUM(IF(period=3 AND xstatus='V',1,0)) AS v3, SUM(IF(period=3 AND xstatus='M',1,0)) AS m3, SUM(IF(period=3 AND xstatus='O',1,0)) AS o3";
		$pkqry .= " FROM avo_pk_data WHERE year='". $schoolyear. "' AND sid=". $sid. " GROUP BY aspect";
		$pkqr = SA_loadquery($pkqry);
		if(isset($pkqr))
		{
			foreach($pkqr['aspect'] AS $pkrow => $pkasp)
		  {
				$res[$pkasp]['t1'] = $pkqr['t1'][$pkrow];
				$res[$pkasp]['v1'] = $pkqr['v1'][$pkrow];
				$res[$pkasp]['m1'] = $pkqr['m1'][$pkrow];
				$res[$pkasp]['o1'] = $pkqr['o1'][$pkrow];
				$res[$pkasp]['t2'] = $pkqr['t2'][$pkrow];
				$res[$pkasp]['v2'] = $pkqr['v2'][$pkrow];
				$res[$pkasp]['m2'] = $pkqr['m2'][$pkrow];
				$res[$pkasp]['o2'] = $pkqr['o2'][$pkrow];
				$res[$pkasp]['t3'] = $pkqr['t3'][$pkrow];
				$res[$pkasp]['v3'] = $pkqr['v3'][$pkrow];
				$res[$pkasp]['m3'] = $pkqr['m3'][$pkrow];
				$res[$pkasp]['o3'] = $pkqr['o3'][$pkrow];
			}
			return($res);
		}
		else
		{
			return NULL;
		}
	}
	
	function showPK($res,$aspect,$period)
	{
		if(isset($res[$aspect]) && $res[$aspect]["t". $period] > 0)
		  return(ThreeBar(($res[$aspect]["v". $period] * 100)/ $res[$aspect]["t". $period],($res[$aspect]["m". $period] * 100) / $res[$aspect]["t". $period],($res[$aspect]["o". $period] * 100) / $res[$aspect]["t". $period]));
		else
			return("&nbsp;");
	}
?>
