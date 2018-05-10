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
  $offsubjects = array(1 => "Ne","En","Sp","Pa","Wi","Nask1","Nask2","Bio","EcMo","Ak","Gs","CKV");
  $altsubjects = array("NAT4"=>6, "GES4"=>11, "AK4"=>10, "EC4"=>9, "BIO4"=>8, "SCH4"=>7, "WIS4"=>5, "PAP4"=>4, "SPA4"=>3, "ENG4"=>2, "NED4"=>1,
                       "Ne"=>1, "En"=>2, "Sp"=>3, "Wi"=>5, "Na"=>6, "Sk"=>7, "Bio"=>8, "Gs"=>11, "Ak"=>10, "Ec"=>9, "Pa"=>4, "NaSk 1"=>6, "NaSk 2"=>7, "EcMo"=>9,
					   "PA"=>4, "NE"=>1, "EN"=>2, "SP"=>3, "WI"=>5, "AK"=>10, "BI"=>8, "GS"=>11, "Na"=>6, "SK"=>7, "EC/MO"=>9,
					   "ne"=>1, "en"=>2, "sp"=>3, "pa"=>4, "wi"=>5, "na"=>6, "sk"=>7, "bi"=>8, "ec"=>9, "ak"=>10, "gs"=>11,
					   "NA"=>6, "EC"=>9, "EM & O"=>9, "CKV"=>12, "Ckv"=>12, "NED"=>1, "ENG"=>2, "SPA"=>3, "PAP"=>4, "WIS"=>5, "NASK1" => 6,"NS2"=>7, "BIO"=>8, "GES"=>11, "CKV"=>12, "bio"=>8,
						 "Nask 1"=>6,"Nask 2"=>7,"ecmo"=>9);
  /* MC */
  
  /* TEST systeem 
  $vakhead["Taal & Communicatie"] = array("ne","en","sp","pa");
  $vakhead["Maatschappij"] = array("gs","ak");
  $vakhead["Exacte vakken"] = array("wi","na","sk","bio");
  $vakhead["Onderw. Onderst."] = array("ec","ik");
  $vakhead["Kunst & Cultuur"] = array("ckv");
  $vakhead["Individu"] = array("kgl","lo");
  $ptvakken = array("lo","gs","ak","ec","pa","ne","en","sp","ckv","na","sk","wi","bio","ik");
  */
  $groepfilter = "Exam%";
  $llnperpage = 6;
  
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
  // GEt a translation of the subjects shortname to fullname and mid
  $subqr = SA_loadquery("SELECT * FROM subject");
  if(isset($subqr['shortname']))
    foreach($subqr['shortname'] AS $sbix => $ssn)
	{
	  $subjdata[$ssn]['fullname'] = $subqr['fullname'][$sbix];
	  $subjdata[$ssn]['mid'] = $subqr['mid'][$sbix];
	  if(isset($altsubjects[$ssn]))
	    $subjdata[$offsubjects[$altsubjects[$ssn]]] = $subjdata[$ssn];
	}
  
  // Get a list of last test dates for periods
  //$perends = SA_loadquery("SELECT period,CEIL(date) AS edate FROM testdef GROUP BY period ORDER BY period");
  
  if(isset($groups))
  {
    // First part of the page
    echo("<html><head><title>Bespreeklijst</title></head><body link=blue vlink=blue>");
    echo '<LINK rel="stylesheet" type="text/css" href="style_Bespreeklijst.css" title="style1">';

    foreach($groups['gid'] AS $gix => $gid)
	{
      // Get a list of students
      $students = SA_loadquery("SELECT student.* FROM student LEFT JOIN sgrouplink USING(sid) WHERE sgrouplink.gid=". $gid. " ORDER BY lastname,firstname");
	  // Get the results
	  unset($resdata);
	  $resq = "SELECT sid,MAX(result) res, type,data AS tcode, mid FROM testresult LEFT JOIN testdef USING(tdid) 
	           LEFT JOIN class USING(cid) LEFT JOIN t_dcode ON(class.tid=t_dcode.tid) LEFT JOIN sgrouplink USING(sid)
			   WHERE sgrouplink.gid=". $gid. " AND year='". $schoolyear. "' GROUP BY sid,mid,type";
	  $resqr = SA_loadquery($resq);
	  if(isset($resqr['sid']))
	    foreach($resqr['sid'] AS $rix => $sid)
		{
		  $resdata[$sid][$resqr['mid'][$rix]][$resqr['type'][$rix]] = $resqr['res'][$rix];
		  $resdata[$sid][$resqr['mid'][$rix]]['teacher'] = $resqr['tcode'][$rix];
		}
	  $resq = "SELECT sid,result, mid FROM gradestore LEFT JOIN sgrouplink USING(sid)
			   WHERE sgrouplink.gid=". $gid. " AND year='". $schoolyear. "' AND period=2 GROUP BY sid,mid";
	  $resqr = SA_loadquery($resq);
	  if(isset($resqr['sid']))
	    foreach($resqr['sid'] AS $rix => $sid)
		  $resdata[$sid][$resqr['mid'][$rix]]['avg'] = $resqr['result'][$rix];
	  $resq = "SELECT sid,xstatus,mid FROM ex45data LEFT JOIN sgrouplink USING(sid)
			   WHERE sgrouplink.gid=". $gid. " AND year='". $schoolyear. "' AND xstatus>=5 GROUP BY sid,mid";
	  $resqr = SA_loadquery($resq);
	  if(isset($resqr['sid']))
	    foreach($resqr['sid'] AS $rix => $sid)
		  $resdata[$sid][$resqr['mid'][$rix]]['v'] = $resqr['xstatus'][$rix];
	  
	  $resq = "SELECT sid,ckvres FROM examresult LEFT JOIN sgrouplink USING(sid)
			   WHERE sgrouplink.gid=". $gid. " AND year='". $schoolyear. "' AND ckvres <> 0 GROUP BY sid";
	  $resqr = SA_loadquery($resq);
	  if(isset($resqr['sid']))
	    foreach($resqr['sid'] AS $rix => $sid)
		  $resdata[$sid]['CKV'] = 1;
	  $resq = "SELECT sid,result, mid FROM gradestore LEFT JOIN sgrouplink USING(sid) LEFT JOIN subject USING(mid)
			   WHERE sgrouplink.gid=". $gid. " AND year='". $schoolyear. "' AND period=0 AND shortname LIKE 'lo' GROUP BY sid,mid";
	  $resqr = SA_loadquery($resq);
	  if(isset($resqr['sid']))
	    foreach($resqr['sid'] AS $rix => $sid)
		  $resdata[$sid]['LO'] = $resqr['result'][$rix];
	  
	  if(isset($students))
	  {
	    $llnoffset = 0;
		while ($llnoffset < sizeof($students['sid']))
		{
		  $scnt = $llnperpage;
		  if(sizeof($students['sid']) - $llnoffset < $scnt)
		    $scnt = sizeof($students['sid']) - $llnoffset;
		  echo("<TABLE BORDER=1><TR><TH class=headleft>". $schoolname. "<BR>Schooljaar ". $schoolyear. "<BR>Klas: ". $groups['groupname'][$gix]. "<BR>Mentor: ". $groups['data'][$gix]. " </TH>");
		  for($sx = 1; $sx <= $scnt; $sx++)
		  {
		    echo("<TH COLSPAN=6 class=centercelRB>". ($sx+$llnoffset). ". ". $students['lastname'][$sx+$llnoffset]. "<BR><SPAN class=stfirstname>". $students['firstname'][$sx+$llnoffset]. "</SPAN></TH>");
		  }
		  echo("</TR>");
		  
		  echo("<TR><TH>Vakken</TH>");
		  for($sx = 1; $sx <= $scnt; $sx++)
		  {
		    echo("<TH>Doc</TH><TH>SE1</TH><TH>SE2</TH><TH>SE3</TH><TH>PO</TH><TH class=centercelRB>Gem</TH>");
		  }
		  echo("</TR>");
		  	  
			foreach($offsubjects AS $vkn)
			{
			  echo("<TR><TD>". $subjdata[$vkn]["fullname"]. "</TD>");
			  $mid = $subjdata[$vkn]['mid'];
		      for($sx = 1; $sx <= $scnt; $sx++)
		      {
			    $sid = $students['sid'][$llnoffset+$sx];
			    echo("<TD class=centercel>");
				if(isset($resdata[$sid][$mid]['teacher']))
				  echo($resdata[$sid][$mid]['teacher']);
				else
				  echo("&nbsp;");
				echo("</TD><TD class=centercel>");
				if(isset($resdata[$sid][$mid]['v']))
				  echo("v". ($resdata[$sid][$mid]['v'] + 2));
				else if(isset($resdata[$sid][$mid]['SE1']))
				  echo(colored(number_format($resdata[$sid][$mid]['SE1'],1,',','.')));
				else
				  echo("&nbsp;");
				echo("</TD>");
			    echo("<TD class=centercel>");
				if(isset($resdata[$sid][$mid]['v']))
				  echo("v". ($resdata[$sid][$mid]['v'] + 2));
				else if(isset($resdata[$sid][$mid]['SE2']))
				  echo(colored(number_format($resdata[$sid][$mid]['SE2'],1,',','.')));
				else
				  echo("&nbsp;");
				echo("</TD>");
			    echo("<TD class=centercel>");
				if(isset($resdata[$sid][$mid]['v']))
				  echo("v". ($resdata[$sid][$mid]['v'] + 2));
				else if(isset($resdata[$sid][$mid]['SE3']))
				  echo(colored(number_format($resdata[$sid][$mid]['SE3'],1,',','.')));
				else
				  echo("&nbsp;");
				echo("</TD>");
			    echo("<TD class=centercel>");
				if(isset($resdata[$sid][$mid]['v']))
				  echo("v". ($resdata[$sid][$mid]['v'] + 2));
				else if(isset($resdata[$sid][$mid]['PO']))
				  echo(colored(number_format($resdata[$sid][$mid]['PO'],1,',','.')));
				else
				  echo("&nbsp;");
				echo("</TD>");
			    echo("<TD class=centercelRB>");
				if(isset($resdata[$sid][$mid]['v']))
				  echo("v". ($resdata[$sid][$mid]['v'] + 2));
				else if(isset($resdata[$sid][$mid]['avg']))
				  echo(colored($resdata[$sid][$mid]['avg']));
				else
				  echo("&nbsp;");
				echo("</TD>");
		      }
			  echo("</TR>");
			  
			} // End for each subject
// CKV indicator
			echo("<TR><TD>CKV CA1</TD>");
		    for($sx = 1; $sx <= $scnt; $sx++)
		    {
			  $sid = $students['sid'][$llnoffset+$sx];
			  echo("<TD COLSPAN=6 class=centercelRB>");
			  if(isset($resdata[$sid]['CKV']))
				echo("voldoende");
			  else
				echo("onvoldoende");
		      echo("</TD>");
		    }
			echo("</TR>");
		  
// LO indicator
			echo("<TR><TD>LO indicatie</TD>");
		    for($sx = 1; $sx <= $scnt; $sx++)
		    {
			  $sid = $students['sid'][$llnoffset+$sx];
			  echo("<TD COLSPAN=6 class=centercelRB>");
			  if(isset($resdata[$sid]['LO']) && $resdata[$sid]['LO'] > 5)
				echo("voldoende");
			  else if(isset($resdata[$sid]['LO']) && $resdata[$sid]['LO'] < 6)
				echo("onvoldoende");
			  else
				echo("geen cijfer");
		      echo("</TD>");
		    }
			echo("</TR>");
		  
// Total points and amount of subjects
			for($sx = 1; $sx <= $scnt; $sx++)
			{
			  $sid = $students['sid'][$llnoffset+$sx];
			  $resdata[$sid]['TP']['SE1'] = 0;
			  $resdata[$sid]['TP']['SE2'] = 0;
			  $resdata[$sid]['TP']['SE3'] = 0;
			  $resdata[$sid]['TP']['avg'] = 0;
			  $resdata[$sid]['AV'] = 0;
			  foreach($offsubjects AS $vkn)
			  {
			    $mid = $subjdata[$vkn]['mid'];
			    if(isset($resdata[$sid][$mid]['v']))
				{
				  $resdata[$sid]['TP']['SE1'] += $resdata[$sid][$mid]['v'] + 2;
				  $resdata[$sid]['TP']['SE2'] += $resdata[$sid][$mid]['v'] + 2;
				  $resdata[$sid]['TP']['SE3'] += $resdata[$sid][$mid]['v'] + 2;
				  $resdata[$sid]['TP']['avg'] += $resdata[$sid][$mid]['v'] + 2;
				  $resdata[$sid]['AV'] += 1;
				}
				else
				{
				  if(isset($resdata[$sid][$mid]['SE1']))
				  {
				    $resdata[$sid]['TP']['SE1'] += $resdata[$sid][$mid]['SE1'];
				    $resdata[$sid]['AV'] += 1;
				  }
				  if(isset($resdata[$sid][$mid]['SE2']))
				    $resdata[$sid]['TP']['SE2'] += $resdata[$sid][$mid]['SE2'];
				  if(isset($resdata[$sid][$mid]['SE3']))
				    $resdata[$sid]['TP']['SE3'] += $resdata[$sid][$mid]['SE3'];
				  if(isset($resdata[$sid][$mid]['avg']))
				    $resdata[$sid]['TP']['avg'] += $resdata[$sid][$mid]['avg'];
				}
			  }
			}
			// Empty row precedes stat info
			echo("<TR><TD COLSPAN=". (1 + 6*$scnt). " class=centercelRB>&nbsp;</TD>");
			echo("</TR>");
			
			echo("<TR><TD>Aantal punten</TD>");
		    for($sx = 1; $sx <= $scnt; $sx++)
		    {
			  $sid = $students['sid'][$llnoffset+$sx];
			  echo("<TD>&nbsp;</TD><TD class=centercel>". $resdata[$sid]['TP']['SE1']. "</TD>");
			  echo("<TD class=centercel>". $resdata[$sid]['TP']['SE2']. "</TD>");
			  echo("<TD class=centercel>". $resdata[$sid]['TP']['SE3']. "</TD>");
			  echo("<TD class=centercel>&nbsp;</TD>");
			  echo("<TD class=centercelRB>". $resdata[$sid]['TP']['avg']. "</TD>");
		    }
			echo("</TR>");
			echo("<TR><TD>Aantal vakken</TD>");
		    for($sx = 1; $sx <= $scnt; $sx++)
		    {
			  $sid = $students['sid'][$llnoffset+$sx];
			  echo("<TD COLSPAN=6 class=centercelRB>". $resdata[$sid]['AV']. "</TD>");
		    }
			echo("</TR>");
// Empty advise cel
			echo("<TR><TD>Advies</TD>");
		    for($sx = 1; $sx <= $scnt; $sx++)
		    {
			  echo("<TD COLSPAN=6 class=centercelRB>&nbsp;</TD>");
		    }
			echo("</TR>");
		  
		  echo("</TABLE>");
		  echo("<P class=footer>&nbsp;</P>");
		  $llnoffset += $llnperpage;
		} // End while for subgroups of students
	  } // End if student for the group
	
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
