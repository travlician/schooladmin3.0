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
  require_once("student.php");
  
  $llnperpage = 1;
  $minpoints=35;
  $maxshort=2;
  $oneshortsubs=array("ne","en","wa");
  
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
  $groups = SA_loadquery("SELECT * FROM sgroup LEFT JOIN ". $teachercode. " ON(tid_mentor=tid) WHERE active=1 AND groupname LIKE '". $_SESSION['CurrentGroup']. "' ORDER BY groupname");
    
  if(isset($groups))
  {
    // First part of the page
    echo("<html><head><title>Rapport MH</title></head><body link=blue vlink=blue>");
    echo '<LINK rel="stylesheet" type="text/css" href="style_Rapport_MH.css" title="style1">';

	// Check if periods are blocked, if so, only admin can see the list!
	$pblock = SA_loadquery("SELECT id FROM period WHERE status='closed'");
	$admincheck = SA_loadquery("SELECT tid FROM teacherroles WHERE role=1 AND tid=". $_SESSION['uid']);
	if(isset($pblock) && !isset($admincheck))
	{
	  echo("Omdat er een periode afgesloten is, zijn de rapporten alleen beschikbaar voor beheerders. </html>");
	  exit;
	}

    foreach($groups['gid'] AS $gix => $gid)
	{
      // Get a list of students
      $students = SA_loadquery("SELECT student.* FROM student LEFT JOIN sgrouplink USING(sid) WHERE gid=". $gid. " ORDER BY lastname,firstname");

	  if(isset($students))
	  {
	    $llnoffset = 0;
		while ($llnoffset < sizeof($students['sid']))
		{
		  $scnt = $llnperpage;
		  if(sizeof($students['sid']) - $llnoffset < $scnt)
		    $scnt = sizeof($students['sid']) - $llnoffset;
		  
		  // Connect input librabry to database
		  inputclassbase::dbconnect($userlink);
			
		  stud_grades($students['sid'][$llnoffset+1], $schoolyear);
			
			
			
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
  
  function stud_grades($sid,$schoolyear)
  {
    global $teachercode,$oneshortsubs,$schoolname;
	$dtext = $_SESSION['dtext'];
	$student = new student($sid);
	$group = new group();
	$group->load_current();

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
		else
		  $report_period=$periods['id'][$pix];
	  }
    }

    // Get the list of applicable subjects with their details
    if(isset($teachercode))
      $sql_query = "SELECT class.mid,cid,shortname,fullname, GROUP_CONCAT(distinct ". $teachercode. ".data) AS `tcode` FROM class LEFT JOIN subject using (mid) LEFT JOIN ". $teachercode. " USING(tid) ";
    else
      $sql_query = "SELECT class.mid,cid,shortname,fullname FROM class LEFT JOIN subject using (mid) ";
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

    // Get the list of pass criteria per subject
    $sql_query = "SELECT * FROM class left join coursepasscriteria using (masterlink) GROUP BY mid";
    $passcrits = inputclassbase::load_query($sql_query);
	if(isset($passcrits))
	  foreach($passcrits['minimumpass'] AS $crix => $mpass)
	    $passpoint[$passcrits['mid'][$crix]] = $mpass;
  
    $digits = inputclassbase::load_query("SELECT MAX(digitsafterdot) AS digits FROM reportcalc");
  
    echo("<div class=leftblock>");
	echo("<DIV class=heading><img src=schoollogo.png height=100px width=100px></DIV><BR>");
    echo("<font size=+2><center>" . $dtext['gcard_4'] . "<BR>" . $student->get_firstname() . " " . $student->get_lastname() . "</font><p>");
    echo("<br><font size=+2><center>Schooljaar: " . $schoolyear . "</font><br>"); 
   echo("<br><font size=+1><center>Leerjaar: " . substr($_SESSION['CurrentGroup'],0,1) . "</font><br>"); 

    echo("<br>");
	if(!isset($subjects['mid']))
	{
	  echo($dtext['No_grades']);
	  return;
	}

    // Now create a table with all subjects for this student to enable to go to the grade details
    // Create the first heading row for the table
    echo("<table border=1 cellpadding=0>");
    echo("<tr><td><center>" . $dtext['Subject'] . "</td>");
    // Now add the periods heading
	foreach($periods['id'] AS $pix => $p)
	{
	  if($p > 0)
        echo("<td class=resultcol><center>". $dtext['Period_marker']. $p . "</td>");
    }
    echo("<td class=resultcol><center>" . $dtext['fin_per_ind'] . "</td></tr>"); 
  

    // Create a row in the table for every subject
	$altrow = false;
	foreach($subjects['mid'] AS $sbix => $mid)
    { // each subject
      echo("<tr". ($altrow ? ' class=altbg' : ''). "><td>" . $subjects['fullname'][$sbix] . "</td>");
	  foreach($periods['id'] AS $pix => $pp)
      { // add the grades for regular periods
	    if($pp > 0)
		{
          echo("<td><center>");
          if(isset($results_array[$pp][$mid]) && $pp <= $report_period)
          { 
            $result = $results_array[$pp][$mid];
            // Colour depends on pass criteria
			if($result < '@')
			{ // Numeric value
              if($passpoint[$mid] > $result) echo("<font color=red>");
              else echo("<font color=blue>");
              if($periods['status'][$pix] == 'final') echo("<b>"); else echo("<i>");
              echo(number_format($result,$digits['digits'][0],$dtext['dec_sep'],$dtext['mil_sep']));
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
      echo("<td><center>");
	  if(!isset($results_array[1][$mid]) || !isset($results_array[2][$mid]) || !isset($results_array[3][$mid]) || $report_period < 3)
	    echo("&nbsp;");	  
      else if(isset($final_results_array[$mid]))
      {
        $result = $final_results_array[$mid];
		if($result < '@')
		{ // Numeric value
          // Colour depends on pass criteria
          if($passpoint[$mid] > $result) echo("<font color=red>");
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
	  $altrow = !$altrow;
    }
    echo("</table>");
	// Show the result for this period, calculate it first
	$pointtotal=0;
	$short=0;
	$shortnew = 0;
	$pointcount=0;
	if($report_period != 3)
	{
	  if(isset($results_array[$report_period]))
	  {
	    $pointcount = count($results_array[$report_period]);
	    foreach($results_array[$report_period] AS $mid => $subres)
	    {
	      $pointtotal += round($subres);
		  if(round($subres) < 6)
		    $short += 6 - round($subres);
		  $isnew = false;
		  foreach($subjects['shortname'] AS $six => $ssn)
		  {
		    foreach($oneshortsubs AS $ossn)
			  if($ssn == $ossn)
			    if($subjects['mid'][$six] == $mid)
				  $isnew = true;
		  }
		  if($isnew && round($subres) < 6)
		    $shortnew = 6 - round($subres);
	    }
	  }
	}
	else
	{
	  if(isset($final_results_array))
	  {
	    $pointcount = count($final_results_array);
	    foreach($final_results_array AS $mid => $subres)
	    {
	      $pointtotal += round($subres);
		  if(round($subres) < 6)
		    $short += 6 - round($subres);
		  $isnew = false;
		  foreach($subjects['shortname'] AS $six => $ssn)
		  {
		    foreach($oneshortsubs AS $ossn)
			  if($ssn == $ossn)
			    if($subjects['mid'][$six] == $mid)
				  $isnew = true;
		  }
		  if($isnew && round($subres) < 6)
		    $shortnew = 6 - round($subres);
	    }
	  }
	}
	// Now that basic calcultaion is done, draw the conclusion
	if($pointtotal >= 35 && $short <= 2 && $shortnew <=1)
	  $endresult="Voldoende";
	else if($pointcount >= 6)
	  $endresult="Onvoldoende";
    else
      $endresult="Onvolledig";
	//echo("<H2>Resultaat : ". $endresult. "</H2>");
	echo("<H2>Uitslag: </H2>");
	
	
	echo("</DIV><DIV class=rightblock>");
	echo("<p class=dateprint>Datum: ". dateprint(). "</p>");
	echo("<p class=leftsign>Co&ouml;rdinator<BR>N. Matthew</p>");
	echo("<p class=rightsign>". $student->get_firstname() . " " . $student->get_lastname() ."<BR>(Handtekening)</p>");  
	echo("<p class=bn>Beoordeling volgens de norm:<BR><ol><li>Voor alle vakken het eindcijfer 6 of meer behaald.</li><li>Maximaal 2 tekorten; (1x4 of 2x5) met &eacute;&eacute;n overpunt.</li><li>Minimaal 35 punten voor de zes examenvakken.</li><li>Maximaal &eacute;&eacute;n tekort voor de vakken Nederlands, Engels en Wiskunde.</li></ol></p></DIV>");
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
