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
  require_once("student.php");
  require_once("group.php");
  
  // Avoid sorting on students messed up
  unset($_SESSION['ssortertable']);
  // Connect library to database
  inputclassbase::dbconnect($userlink);
  
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
  
  // Get the group
  $mygroup = new group();
  $mygroup->load_current();
    
  // First part of the page
  echo("<html><head><title>Rapport AHA</title></head><body link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style_Rapport_AHA.css" title="style1">';

  // Check if periods are blocked, if so, only admin can see the list!
  $pblock = SA_loadquery("SELECT id FROM period WHERE status='closed'");
  $admincheck = SA_loadquery("SELECT tid FROM teacherroles WHERE role=1 AND tid=". $_SESSION['uid']);
  if(isset($pblock) && !isset($admincheck))
  {
	echo("Omdat er een periode afgesloten is, zijn de rapporten alleen beschikbaar voor beheerders. </html>");
	exit;
  }

  // Get a list of students
  $students = student::student_list($mygroup);

  if(isset($students))
  {
    foreach($students AS $student)
     stud_grades($student, $schoolyear,$mygroup);
  } // End if student for the group
	
  echo("</html>");
    
  function stud_grades($student,$schoolyear,$group)
  {
    $sid = $student->get_id();
    global $schoolname,$schoolyear;

    // Get the list of periods with their details
	$periods = inputclassbase::load_query("SELECT id,status,year FROM period ORDER BY id");
    // Depending on the states of the periods we set the state of the final period.
    $all_final = 'Y';
    $any_open = 'N';
	$report_period=0;
    foreach($periods['status'] AS $pix => $pstat)
    {
      if($periods['id'][$pix] != 0)
			{
					if($pstat == 'open')
						$any_open = 'Y';
					if($pstat != 'final')
						$all_final = 'N';
			}
    }
		if(date("n") < 4)
			$report_period=2;
		else if(date("n") > 7)
			$report_period=1;
		else
			$report_period=3;
    // Get the list of applicable subjects with their details
    $sql_query = "SELECT class.mid,cid,shortname,fullname,show_sequence FROM class LEFT JOIN subject using (mid) ";
    $sql_query .= "LEFT JOIN (SELECT gid FROM (SELECT sid FROM sgrouplink WHERE gid=". $group->get_id(). " GROUP BY sid) AS t1 LEFT JOIN sgrouplink USING(sid)
                   GROUP BY gid) AS t2 USING(gid) WHERE t2.gid IS NOT NULL AND show_sequence IS NOT NULL ";
	$sql_query .= "GROUP BY mid ORDER BY show_sequence";
	$subjects = inputclassbase::load_query($sql_query);

    // Get the list of grades for normal periods
    $sql_query = "SELECT gradestore.* FROM gradestore LEFT JOIN period ON (period.id=gradestore.period)
				  LEFT JOIN (SELECT mid,cid FROM sgrouplink LEFT JOIN class USING(gid) WHERE sid=". $student->get_id(). ") AS t1 USING(mid)
                  WHERE gradestore.year=period.year AND cid IS NOT NULL AND sid=". $student->get_id();
    $grades = inputclassbase::load_query($sql_query);
    if(isset($grades))
      foreach($grades['result'] AS $grix => $gres)  
        $results_array[$grades['period'][$grix]][$grades['mid'][$grix]] = $gres;
  
    // Get the list of final grades
//    $sql_query = "SELECT * FROM student inner join gradestore using (sid) where period='0' AND gradestore.year='" . $periods['year'][0] . "' AND student.sid=". $student->get_id();
    $sql_query = "SELECT gradestore.* FROM gradestore 
				  LEFT JOIN (SELECT mid,cid FROM sgrouplink LEFT JOIN class USING(gid) WHERE sid=". $student->get_id(). ") AS t1 USING(mid)
                  WHERE year='" . $periods['year'][0] . "' AND period=0 AND cid IS NOT NULL AND sid=". $student->get_id();
    $fingrades = inputclassbase::load_query($sql_query);
    if(isset($fingrades))
      foreach($fingrades['result'] AS $grix => $fgr)
	    $final_results_array[$fingrades['mid'][$grix]] = $fgr;
	
	// Get "Vrijstellingen
	unset($vrijst);
	$vrijstqr = inputclassbase::load_query("SELECT mid,xstatus FROM ex45data WHERE sid=". $student->get_id(). " AND year='". $schoolyear. "' AND xstatus > 4");
	if(isset($vrijstqr))
	  foreach($vrijstqr['mid'] AS $vix => $vmid)
	    $vrijst[$vmid] = $vrijstqr['xstatus'][$vix];

    // Get the list of pass criteria per subject
    $sql_query = "SELECT * FROM class left join coursepasscriteria using (masterlink) GROUP BY mid";
    $passcrits = inputclassbase::load_query($sql_query);
	if(isset($passcrits))
	  foreach($passcrits['minimumpass'] AS $crix => $mpass)
	    $passpoint[$passcrits['mid'][$crix]] = $mpass;
  
    $digits = inputclassbase::load_query("SELECT MAX(digitsafterdot) AS digits FROM reportcalc");
		// Frontpage
		echo("<DIV class=rightblock><img src=schoollogo.png height=400px width=400px><H1>Schooljaar: ". $schoolyear. "</h1></DIV>");
		echo("<p class=pagebreak>&nbsp;</p>");
  
    echo("<div class=leftblock>");
//	echo("<DIV class=heading><img src=schoollogo.png height=100px width=100px></DIV><BR>");
    echo("<P class=studentname>Rapport van:<BR><BR>" . $student->get_lastname() . ", " . $student->get_firstname() . "</p>");
    echo("<P class=schoolyear>Schooljaar: " . $schoolyear . "</p>"); 

    echo("<br>");
	if(!isset($subjects['mid']))
	{
	  echo("Geen cijfers beschikbaar");
	  return;
	}

    // Now create a table with all subjects for this student to enable to go to the grade details
    // Create the first heading row for the table
    echo("<table border=1 cellpadding=0>");
    echo("<tr><th>Vakken</th>");
    // Now add the periods heading
	foreach($periods['id'] AS $pix => $p)
	{
	  if($p > 0)
        echo("<th class=resultcol>R". $p . "</th>");
    }
    echo("<th class=resultcol>E.C.</th></tr>"); 
  

    // Create a row in the table for every subject
	$curseqno = 0;
	foreach($subjects['mid'] AS $sbix => $mid)
    { // each subject
	  // See if double lines must be used to separate subjects
	  $newseqno = $subjects['show_sequence'][$sbix];
	  $subsepon = (ceil($newseqno / 10.0) != ceil($curseqno / 10.0));
	  $curseqno = $newseqno;
      echo("<tr><td". ($subsepon ? " class=subjectseparatortd" : ""). ">" . $subjects['fullname'][$sbix] . "</td>");
	  foreach($periods['id'] AS $pix => $pp)
      { // add the grades for regular periods
	    if($pp > 0)
		{
          echo("<td". ($subsepon ? " class=subjectseparatortd" : ""). "><center>");
		  if(isset($vrijst[$mid]))
		  {
		    if($vrijst[$mid] < 9)
		      echo("v". ($vrijst[$mid]+2));
			else
			  echo("c". ($vrijst[$mid]-3));
		  }
          else if(isset($results_array[$pp][$mid]) && $pp <= $report_period)
          { 
            $result = $results_array[$pp][$mid];
            // Colour depends on pass criteria
			if($result < '@')
			{ // Numeric value
              if($result < 5.5) echo("<font color=red>");
              else echo("<font color=blue>");
              if($periods['status'][$pix] == 'final') echo("<b>"); else echo("<i>");
              echo(number_format($result,$digits['digits'][0],",","."));
              if($periods['status'][$pix] == 'final') echo("</b>"); else echo("</i>");
              echo("</font>");
			}
			else // Alpha value
			  echo($result);
          }
          else
            echo("&nbsp;");
          echo("</td>");
		}
      }
      // Add the final grade
      echo("<td". ($subsepon ? " class=subjectseparatortd" : ""). "><center>");
	  if(isset($vrijst[$mid]))
	  {
		if($vrijst[$mid] < 9)
		  echo("v". ($vrijst[$mid]+2));
		else
		  echo("c". ($vrijst[$mid]-3));
	  }
	  else if(!isset($results_array[1][$mid]) || !isset($results_array[2][$mid]) || !isset($results_array[3][$mid]) || $report_period < 3)
	    echo("&nbsp;");	  
      else if(isset($final_results_array[$mid]))
      {
        $result = $final_results_array[$mid];
		if($result < '@')
		{ // Numeric value
          // Colour depends on pass criteria
          if($result < 5.5) echo("<font color=red>");
          else echo("<font color=blue>");
          if($any_open == 'N') echo("<b>"); else echo("<i>");
          echo($result);
          if($any_open == 'N') echo("</b>"); else echo("</i>");
          echo("</font>");
		}
		else // Alpha value
		  echo($result);
      }
      else
        echo("&nbsp;");
      echo("</td>");
      echo("</tr>");
    }
    echo("</table>");
	echo("<P class=pointsremark>Het cijfer 5,4 is een onvoldoende</p>");
	
	echo("</DIV><DIV class=rightblock>");
	echo("<P class=schoolname>". $schoolname. "</p>");
	echo("<P class=levelyear>KLAS ". filter_var($group->get_groupname(), FILTER_SANITIZE_NUMBER_INT). "</P>");
	echo("<P class=imagepart>Opmerking:<BR><BR><img src=schoollogo.png height=100px width=100px></P>");
	
	$yearresult = inputclassbase::load_query("SELECT xresult FROM examresult WHERE sid=". $student->get_id(). " AND year='". $schoolyear. "'");
	if(isset($yearresult))
	  $restxt = $yearresult['xresult'][0];
	else
	  $restxt = "&nbsp";
	echo("<P class=yearresult>". $restxt. "</P>");

	echo("<p class=signpart>Handtekening directeur:<SPAN class=signline>&nbsp;</SPAN></p>");  
	echo("</DIV>");
	echo("<DIV class=pagebreak>&nbsp;</DIV>");
  }
  function dateprint()
  {
    $months=array(1=>"januari","februari","maart","april","mei","juni","juli","augustus","september","oktober","november","december");
	$pdate = mktime(0,0,0,date("n"),date("j")+1,date("Y"));
	return(date("j",$pdate). " ". $months[date("n",$pdate)]. " ". date("Y",$pdate));
  }
 

?>
