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
  
  // Functions
  function get_initials($name)
  {
    $explstring = explode(" ",$name);
    $retstr = "";
    foreach($explstring AS $addstr)
      $retstr .= " ". substr($addstr,0,1);
    return $retstr;
  }
  
  function print_head($gname)
  {
    global $schoolyear,$schoolname,$subjects,$periods,$perends;
    echo("<table class=studlist><tr><th rowspan=2 class=headtbl>&nbsp</th><th class=headtbl>". $schoolname. "</th>");
	foreach($subjects['fullname'] AS $subname)
	  echo("<th colspan=4 rowspan=2 class=headtblrotate>". $subname. "</th>");
	// Absence and late heading
	echo("<th colspan=4 rowspan=2 class=headtblrotate>Verzuim</th>");
	echo("<th colspan=4 rowspan=2 class=headtblrotate>Te laat</th>");
	
	echo("</tr><th class=headtbl>". $gname. "</th></tr>");
	echo("<tr><th class=headtbl>Student#</th><th class=headtbl>Schooljaar ". $schoolyear. "</th>");
	foreach($subjects['shortname'] AS $subabr)
	{
	  foreach($periods['id'] AS $period)
    	echo("<th class=headtblrotate>R". $period. " ". $subabr. "</th>");
	  echo("<th class=headtblrotate>EIND ". $subabr. "</th>");
	}
    // Semester headings absence and late
	  foreach($perends['period'] AS $period)
    	echo("<th class=headtblrotate>R". $period. " VER</th>");
	  echo("<th class=headtblrotate>EIND VER</th>");
	  foreach($perends['period'] AS $period)
    	echo("<th class=headtblrotate>R". $period. " LAAT</th>");
	  echo("<th class=headtblrotate>EIND LAAT</th>");
		
	echo("</tr>"); 
  }
  
  function print_foot()
  {
    global $studentcount,$failcount,$subjects,$periods;
	// Show failed count and percentage
	echo("<tr><td>&nbsp</td><td>Onvoldoende</td>");
	foreach($subjects['mid'] AS $mid)
	{
	  foreach($periods['id'] AS $period)
	    echo("<td>". $failcount[$mid][$period]. "</td>");
	  echo("<td>". $failcount[$mid][0]. "</td>");
	}
	echo("</tr>");
	echo("<tr><td>&nbsp</td><td>Onvoldoende %</td>");
	foreach($subjects['mid'] AS $mid)
	{
	  foreach($periods['id'] AS $period)
	    echo("<td>". round((100.0 * $failcount[$mid][$period]) / $studentcount,0). "</td>");
	  echo("<td>". round((100.0 * $failcount[$mid][0]) / $studentcount,0). "</td>");
	}
	echo("</tr>");
    echo("</TABLE>");
	echo("<p class=pagebreak>&nbsp</p>");
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
  $groups = SA_loadquery("SELECT * FROM sgroup WHERE active=1 ORDER BY groupname");
  $periods = SA_loadquery("SELECT id FROM period ORDER BY id");
  
  // Get a list of last test dates for periods
  $perends = SA_loadquery("SELECT period,CEIL(date) AS edate FROM testdef GROUP BY period ORDER BY period");
  
  if(isset($groups))
  {
    // First part of the page
    echo("<html><head><title>Verzamelstaat</title></head><body link=blue vlink=blue>");
    echo '<LINK rel="stylesheet" type="text/css" href="style_Verzamelstaat.css" title="style1">';

    foreach($groups['gid'] AS $gix => $gid)
	{
	  // Get a list of subjects, applicable to the group and ordered by the sequence number
	  $subjects = SA_loadquery("SELECT DISTINCT subject.* FROM class LEFT JOIN subject ON(class.mid=subject.mid) ORDER BY show_sequence");
	  if(isset($subjects))
	  {
	    // Get a list of students
		$students = SA_loadquery("SELECT student.* FROM student LEFT JOIN sgrouplink USING(sid) WHERE gid=". $gid. " ORDER BY lastname,firstname");
		if(isset($students))
		{
		  // Initialise group failcount per subject and studentcount
		  $studentcount = 0;
		  foreach($subjects['mid'] AS $mid)
		  {
		    foreach($periods['id'] AS $period)
			  $failcount[$mid][$period] = 0;
			$failcount[$mid][0] = 0;
		  }

		  print_head($groups['groupname'][$gix]);
		  foreach($students['sid'] AS $six => $stid)
		  {
			$studentcount++;
		    echo("<tr><td>". $students['altsid'][$six]. "</td><td>". $students['lastname'][$six]. ", ". $students['firstname'][$six]. "</td>");
		    unset($gradestore);
			$gradestore = SA_loadquery("SELECT * FROM gradestore WHERE sid=". $stid. " AND year='". $schoolyear. "'");
			foreach($subjects['mid'] AS $mid)
			{
			  foreach($periods['id'] AS $period)
			  {
			    unset($xres);
				if(isset($gradestore))
				  foreach($gradestore['period'] AS $rix => $rp)
				  {
				    if($rp == $period && $gradestore['mid'][$rix] == $mid)
					  $xres = $gradestore['result'][$rix];
				  }
				if(isset($xres))
				{
				  echo("<td>". $xres. "</td>");
				  if($xres <= 5.4)
				    $failcount[$mid][$period]++;
				}
				else
				  echo("<td>&nbsp</td>");
			  }
			  // End result
			  unset($xres);
		      if(isset($gradestore))
				foreach($gradestore['period'] AS $rix => $rp)
				{
     			  if($rp == 0 && $gradestore['mid'][$rix] == $mid)
			        $xres = $gradestore['result'][$rix];
				}
			  if(isset($xres))
			  {
			    echo("<td>". $xres. "</td>");
				if($xres <= 5.4)
				  $failcount[$mid][0]++;
			  }
			  else
			    echo("<td>&nbsp</td>");
			  
			} // Next subject
			// Now show absences
			if(isset($perends))
			{
			  $abstotal = 0;
			  $preveper = "20000101";
			  foreach($perends['edate'] AS $perix => $eperdat)
			  {
			    unset($abscnt);
			    if($perends['period'][$perix] != 3)
				  $abscnt = SA_loadquery("SELECT COUNT(aid) AS abscount FROM absence WHERE sid=". $students['sid'][$six]. " AND date > '". $preveper. "' AND date <= '". $eperdat. "' AND aid > 12 GROUP BY sid");
				else
				  $abscnt = SA_loadquery("SELECT COUNT(aid) AS abscount FROM absence WHERE sid=". $students['sid'][$six]. " AND date > '". $preveper. "' AND aid > 12 GROUP BY sid");
				$abstotal += $abscnt['abscount'][1];
				$preveper = $eperdat;
				echo("<td>". $abscnt['abscount'][1]. "</td>");
			  }
			  echo("<td>". $abstotal. "</td>");
			}
			// Now show late arrival
			if(isset($perends))
			{
			  $abstotal = 0;
			  $preveper = "20000101";
			  foreach($perends['edate'] AS $perix => $eperdat)
			  {
			    unset($abscnt);
			    if($perends['period'][$perix] != 3)
				  $abscnt = SA_loadquery("SELECT COUNT(aid) AS abscount FROM absence WHERE sid=". $students['sid'][$six]. " AND date > '". $preveper. "' AND date <= '". $eperdat. "' AND aid <= 12 GROUP BY sid");
				else
				  $abscnt = SA_loadquery("SELECT COUNT(aid) AS abscount FROM absence WHERE sid=". $students['sid'][$six]. " AND date > '". $preveper. "' AND aid <= 12 GROUP BY sid");
				$abstotal += $abscnt['abscount'][1];
				$preveper = $eperdat;
				echo("<td>". $abscnt['abscount'][1]. "</td>");
			  }
			  echo("<td>". $abstotal. "</td>");
			}
		    echo("</tr>");
		  } // Next student
		  print_foot();
		} // Endif students present in group
	  } // Endif subjects defined for class
	} // Next group
  } // Endif groups defined
  // close the page
  echo("</html>");
?>
