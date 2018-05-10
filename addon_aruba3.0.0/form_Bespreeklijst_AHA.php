<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2013 Aim4me N.V.   (http://www.aim4me.info)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();

  $login_qualify = 'ACT';
  require_once("schooladminfunctions.php");
  require_once("group.php");
  require_once("student.php");
  // Avoid sorting on students messed up
  unset($_SESSION['ssortertable']);
  // Connect library to database
  inputclassbase::dbconnect($userlink);
  // Operational definitions
  $offsubjects = array(1 => "Ne","En","Sp","Pa","Wi-A","Wi-B","Na","Sk","Bio","Inf","M&O","Ec","Ak","Gs","I&S","Pfw");
  $ptvakken = array(1 => "Ne","En","Sp","Pa","Wi-A","Wi-B","Na","Sk","Bio","Inf","M&O","Ec","Ak","Gs");
	$newvakken = array(1 => "Ne","En","Wi-A","Wi-B");
  
  $groepfilter = $_SESSION['CurrentGroup'];
  $llnperpage = 15;

  // Get the year
  $schoolyear = SA_loadquery("SELECT year FROM period");
  $schoolyear = $schoolyear['year'][1];
  
  // Get the group
  $mygroup = new group();
  $mygroup->load_current();
    
  // First part of the page
  echo("<html><head><title>Bespreeklijst</title></head><body link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style_Bespreeklijst_AHA.css" title="style1">';

  // Decide for which period report is produced
  $curm = date("n");
  if($curm > 7)
    $repper = 1;
  else if($curm < 5)
    $repper = 2;
  else
    $repper = 3;
  
  // We need to get the year for entry!
  $curyearqr = inputclassbase::load_query("SELECT year FROM period WHERE id=3");
  $curyear = $curyearqr['year'][0];

  // Get a list of students
  $students = student::student_list($mygroup);
  if(isset($students))
  {
	$llnoffset = 0;
	$seqno = 1;
	foreach($students AS $student)
	{
	    if($llnoffset == 0)
		{
		  echo("<TABLE><TR><TH class=emptyhead COLSPAN=3>&nbsp;</TH><TH class=mainheaderth COLSPAN=". (sizeof($offsubjects) - 2) .">Rapportcijfers ". $schoolyear. ", Klas: ". $mygroup->get_groupname(). "</TH><TH class=emptyhead COLSPAN=3>&nbsp;</TH></TR>");
	      echo("<TR><TH>nr.</TH><TH>Naam:</TH><TH>Advies</TH>");
		  foreach($offsubjects AS $subjnm)
		    echo("<TH class=subjecthead>". $subjnm. "</TH>");
		  echo("<TH class=remarkhead>Opmerkingen</TH>");
		  echo("<TH class=emptyhead>Afl.</TH></TR>");
		}
		// Get vrijstellingen
		unset($vrijst);
		unset($cert);
		unset($behaald);
		$vrijstqr = inputclassbase::load_query("SELECT shortname,xstatus FROM ex45data LEFT JOIN subject USING(mid) WHERE sid=". $student->get_id(). " AND year='". $schoolyear. "' AND xstatus >= 5");
		if(isset($vrijstqr))
		{
		  foreach($vrijstqr['shortname'] AS $vix => $vsubj)
		  {
		    if($vrijstqr['xstatus'][$vix] < 9)
		      $vrijst[$vsubj] = $vrijstqr['xstatus'][$vix] + 2;
			else if($vrijstqr['xstatus'][$vix] < 14)
		      $cert[$vsubj] = $vrijstqr['xstatus'][$vix] - 3;
			else
			  $behaald[$vsubj] = $vrijstqr['xstatus'][$vix] - 13;
		  }
		}
		unset($ahx);
		$ahxqr = inputclassbase::load_query("SELECT shortname,xstatus FROM ahxdata LEFT JOIN subject USING(mid) WHERE sid=". $student->get_id(). " AND year='". $schoolyear. "'");
		if(isset($ahxqr))
		  foreach($ahxqr['shortname'] AS $vix => $vsubj)
		  {
		    if($vsubj != '')
		      $ahx[$vsubj] = str_replace(".",",",$ahxqr['xstatus'][$vix]);
			else
		      $ahx["opm"] = $ahxqr['xstatus'][$vix];			  
		  }
		// Get subjects applicable
		unset($subjappl);
		$subjapplqr = inputclassbase::load_query("SELECT shortname FROM class LEFT JOIN sgrouplink USING(gid) LEFT JOIN subject USING(mid) WHERE sid=". $student->get_id());
		if(isset($subjapplqr['shortname']))
		 foreach($subjapplqr['shortname'] AS $asubj)
		  $subjappl[$asubj] = 1;
		for($period=1; $period <= ($repper > 2 ? 4 : 3); $period++)
		{
		  if($period == 1)
		    echo("<TR><TD class=seqnr>". str_pad($seqno++,4,"0",STR_PAD_LEFT). "</TD><TD class=studentname>". $student->get_lastname(). ", ". $student->get_firstname(). "</TD>");
		  else
		    echo("<TR><TD class=emptyhead COLSPAN=2></TD>");
		  // Get grades
		  unset($grades);
		  if($period == 4)
		    $gradesqr = inputclassbase::load_query("SELECT shortname,result FROM gradestore LEFT JOIN subject USING(mid) LEFT JOIN (SELECT mid FROM gradestore WHERE sid=". $student->get_id(). " AND year='". $schoolyear. "' AND period=3) AS t1 USING(mid) WHERE sid=". $student->get_id(). " AND year='". $schoolyear. "' AND period=0 AND t1.mid IS NOT NULL");
		  else
		    $gradesqr = inputclassbase::load_query("SELECT shortname,result FROM gradestore LEFT JOIN subject USING(mid) WHERE sid=". $student->get_id(). " AND year='". $schoolyear. "' AND period=". ($period % 4));
		  if(isset($gradesqr))
		    foreach($gradesqr['shortname'] AS $gix => $gsubj)
		      $grades[$gsubj] = $gradesqr['result'][$gix];		  
		  // Calcutate advice
		  $tekorten = 0;
			$tekortenn = 0;
		  $compensatie = 0;
		  $subjcount = 0;
		  foreach($ptvakken AS $subj)
		  {
		    if(isset($vrijst[$subj]))
					$compensatie += $vrijst[$subj] - 6;
				else if(isset($cert[$subj]))
					$compensatie = $compensatie; // certificates do not compensate
				else if(!isset($subjappl[$subj]))
					;
				else if(!isset($grades[$subj]))
				{
					$tekorten += 6;
				}
				else
				{
					$subjcount++;
					$rs = round($grades[$subj]);
					if($rs < 6)
					{
						$tekorten += 6 - $rs;
						if(in_array($subj,$newvakken))
							$tekortenn += 6 - $rs;							
					}
					else
						$compensatie += $rs - 6;
				}				
			}
			if($tekorten < 2 && $subjcount > 0)
				$advice = "v";
			else if($tekorten == 2 && $tekortenn < 2 && $compensatie > 1 && $subjcount > 0)
				$advice = "v";
			else if($tekorten == 2 && $tekortenn < 2 && $compensatie == 1 && $subjcount > 0)
				$advice = "b";
			else
				$advice = "o";
		  
		  // Show results
		  if($period <= $repper || $period ==4)
		    echo("<td class=". ($period == 1 ? "lineadvice" : "advice"). ">". $advice. "</td>");
		  else
		    echo("<td class=advice>&nbsp;</td>");
		  foreach($offsubjects AS $subj)
		  {
		    if($repper < $period && $period != 4)
			  echo("<TD class=". ($period == 1 ? "result1" : "result"). ">&nbsp;</TD>");			  
		    else if(isset($vrijst[$subj]))
			  echo("<TD class=". ($period == 1 ? "result1" : "result"). ">v". $vrijst[$subj]. "</TD>");
		    else if(isset($cert[$subj]))
			  echo("<TD class=". ($period == 1 ? "result1" : "result"). ">c". $cert[$subj]. "</TD>");
		    else if(isset($behaald[$subj]))
			  echo("<TD class=". ($period == 1 ? "result1" : "result"). ">b". $behaald[$subj]. "</TD>");
			else if(!isset($subjappl[$subj]) && !isset($ahx[$subj]))
			  echo("<TD class=". ($period == 1 ? "result1" : "result"). ">&nbsp;</TD>");
			else if(!isset($grades[$subj]))
			{
			  if(isset($ahx[$subj]))
			    echo("<TD class=". ($period == 1 ? "result1" : "result"). ">b". $ahx[$subj]. "</TD>");
			  else
			    echo("<TD class=". ($period == 1 ? "result1" : "result"). ">?</TD>");
			}
			else
			{
			  echo("<TD class=". ($period == 1 ? "result1" : "result"). ">");
			  if($grades[$subj] > 0.0 && $grades[$subj] < 5.5)
			    echo("<font color=red>");
			  else
			    echo("<font color=blue>");
			  echo($grades[$subj]. "</font></TD>");
            }			  
		  }
		  // Show remarks
		  echo("<TD class=". ($period == 1 ? "remarkcol1" : "remarkcol"). ">");
		  if(isset($ahx['opm']) && $repper == $period)
		    echo($ahx['opm']);
		  else
		    echo("&nbsp");
		  echo("</td>");
		  // Show credit
		  if($advice == "v" && $period != 3 && $student->get_student_detail("s_inschrijfgeld") > 50.0)
		  {
		    echo("<TD class=". ($period == 1 ? "emptyhead1" : "emptyhead"). ">". number_format($subjcount * ($period == 4 ? 25.00 : 12.50),2). "</td>");
				// Put amount in database, but only for the current period!
				if($repper == $period || ($repper==3 && $period==4))
				{
					$bonus=$subjcount * ($period == 4 ? 25.00 : 12.50);
					if($period != 4)
					{
						$bonq = "REPLACE INTO s_beloning_". $period. "_". substr($curyear,0,4). " (sid,data) VALUES(". $student->get_id(). ",$bonus)";
					}
					else
					{ // 4th period is 3rd bonus!
						$bonq = "REPLACE INTO s_beloning_3_". substr($curyear,0,4). " (sid,data) VALUES(". $student->get_id(). ",$bonus)";
					}
					mysql_query($bonq,$userlink);
				}
		  }
		  else
		  {
				//echo("repper=". $repper. ", period=". $period. "<BR>");
		    // If a cheque number is already defined, don't clear the reward amount!
				$cnrqr = inputclassbase::load_query("SELECT data FROM s_beloning_". ($period < 3 ? $period : "3"). "_cheque_". substr($curyear,0,4). " WHERE sid=". $student->get_id());
		    echo("<td class=". ($period == 1 ? "emptyhead1" : "emptyhead"). ">&nbsp;</td>");
		    if($period != 3 && $student->get_student_detail("s_inschrijfgeld") > 50.0 && !isset($cnrqr['data'][0]))
				{ // Remove any reward that may be assign by previous runs of this list while not fully complete
						if($student->get_student_detail("s_beloning_". ($period != 3 ? $period : "3"). "_cheque_". substr($curyear,0,4)) == "")
							mysql_query("DELETE FROM s_beloning_". $period. "_". substr($curyear,0,4). " WHERE sid=". $student->get_id(), $userlink);
				}
		  }
		  
		  echo("</TR>");
		}
	    $llnoffset++;
	    if($llnoffset == $llnperpage)
	    {
	      print_footer();
		  $llnoffset = 0;
	    }
	} // End foreach loop students
	print_footer();
  } // End if students defined
  else echo("<html>Geen leerlingen gevonden");
      
  echo("</html>");
  
  function print_footer()
  {
    echo("</TABLE>");
  }
?>
