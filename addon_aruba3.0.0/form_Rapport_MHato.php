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
  include ("schooladminfunctions.php");
  require_once("student.php");
  require_once("group.php");

  // Avoid sorting on students messed up
  unset($_SESSION['ssortertable']);
  // Connect library to database
  inputclassbase::dbconnect($userlink);

  if(!isset($_POST['rdate']))
  {
    echo("<P>Afdruk instellingen (Firefox):<BR>Marges op 0,2 inch, 100% scaling, geen header/footer (Blank), Landscape, A4.<BR>KIES EERST DE JUISTE KLAS!</P>"); 
    echo("<FORM name=rdatefrm id=rdatefrm METHOD=POST ACTION=". $_SERVER['PHP_SELF']. ">Rapport datum: <INPUT TYPE=TEXT SIZE=40 NAME=rdate><INPUT TYPE=SUBMIT NAME='OK' VALUE='OK'></FORM>");
    exit();
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
  
  // Define in which period we are. aug-dec -> 1, jan-apr -> 2, may-jul->3
  if(date('n') > 7)
    $repper = 1;
  else if(date('n') < 5)
    $repper = 2;
  else
    $repper = 3;
	// Get the group
  $mygroup = new group();
  $mygroup->load_current();
    
    
  // First part of the page
  echo("<html><head><title>Rapport</title></head><body link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style_Rapport_MHato.css" title="style1">';

  // Check if periods are blocked, if so, only admin can see the list!
  $pblock = SA_loadquery("SELECT id FROM period WHERE status='closed'");
  $admincheck = SA_loadquery("SELECT tid FROM teacherroles WHERE role=1 AND tid=". $_SESSION['uid']);
  if(isset($pblock) && !isset($admincheck))
  {
    echo("Omdat er een periode afgesloten is, is de bespreeklijst alleen beschikbaar voor beheerders. </html>");
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
  
  function colored($res)
  {
     $res2 = str_replace(',','.',$res);
	 if($res2 < 5.5)
	   return("<SPAN class=redcolor>". $res. "</SPAN>");
	 else
	   return($res);
  }
  
  function stud_grades($student,$schoolyear,$group)
  {
    global $repper;
	$dtext = $_SESSION['dtext'];

    // Get the list of periods with their details
	$periods = inputclassbase::load_query("SELECT id,status,year FROM period ORDER BY id");
    // Depending on the states of the periods we set the state of the final period.
    $all_final = 'Y';
    $any_open = 'N';
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

    // Get the list of applicable subjects with their details
    $sql_query = "SELECT class.mid,cid,shortname,fullname FROM subject inner join class using (mid) where gid='". $group->get_id(). "' AND show_sequence IS NOT NULL GROUP BY mid ORDER BY show_sequence";
    $subjects = inputclassbase::load_query($sql_query);

    // Get the list of grades for normal periods
    $sql_query = "SELECT * FROM period,student inner join gradestore using (sid) where period=id AND gradestore.year=period.year AND student.sid=". $student->get_id();
    $grades = inputclassbase::load_query($sql_query);
    if(isset($grades))
      foreach($grades['result'] AS $grix => $gres)  
        $results_array[$grades['period'][$grix]][$grades['mid'][$grix]] = $gres;
  
    // Get the list of final grades
    $sql_query = "SELECT * FROM student inner join gradestore using (sid) where period='0' AND gradestore.year='" . $periods['year'][0] . "' AND student.sid=". $student->get_id();
    $fingrades = inputclassbase::load_query($sql_query);
    if(isset($fingrades))
      foreach($fingrades['result'] AS $grix => $fgr)
	    $final_results_array[$fingrades['mid'][$grix]] = $fgr;

    // Get the list of pass criteria per subject
    $sql_query = "SELECT * FROM class inner join coursepasscriteria using (masterlink) WHERE gid=". $group->get_id();
    $passcrits = inputclassbase::load_query($sql_query);
	if(isset($passcrits))
	  foreach($passcrits['minimumpass'] AS $crix => $mpass)
	    $passpoint[$passcrits['mid'][$crix]] = $mpass;
  
    $digits = inputclassbase::load_query("SELECT MAX(digitsafterdot) AS digits FROM reportcalc");
  
    echo("<DIV class=leftblock>");
    echo("<P class=studentname>Naam: " . $student->get_lastname() . ", " . $student->get_firstname(). "</p>");
	echo("<P class=groupname>Klas: ". $group->get_groupname(). "</p>");

    echo("<br>");
	if(!isset($subjects['mid']))
	{
	  echo($dtext['No_grades']);
	  return;
	}

    // Now create a table with all subjects for this student to enable to go to the grade details
    // Create the first heading row for the table
    echo("<table border=1 cellpadding=0>");
    echo("<tr><td><center>" . $dtext['Subject'] . "</center></td>");
    // Now add the periods heading
	foreach($periods['id'] AS $pix => $p)
	{
	  if($p > 0)
        echo("<td><center>". $dtext['Period_marker']. $p . "</center></td>");
    }
    echo("<td><center>" . $dtext['fin_per_ind'] . "</center></td></tr>"); 
  

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
          if(isset($results_array[$pp][$mid]) && $pp <= $repper)
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
            echo("-");
          echo("</center></td>");
		}
      }
      // Add the final grade
      echo("<td><center>");
	  if(!isset($results_array[1][$mid]) || !isset($results_array[2][$mid]) || !isset($results_array[3][$mid]) || $repper < 3)
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
        echo("-");
      echo("</center></td>");
      echo("</tr>");
	  $altrow = !$altrow;
    }
    echo("</table>");
	echo("</DIV><DIV class=rightblock>");
	echo("<P class=ritem>Datum ". ($repper == 1 ? "eerste" : ($repper == 2 ? "tweede" : "derde")). " rapport: ". $_POST['rdate']. "</P>");
	// Get absence data
	$absdata = inputclassbase::load_query("SELECT SUM(IF(acid=1,1,0)) AS Absent, SUM(IF(acid=2,1,0)) AS Laat
                                           FROM absence LEFT JOIN absencereasons USING(aid) 
                                           LEFT JOIN period ON (id=". $repper. ") WHERE sid=". $student->get_id(). " AND date >= startdate AND date <=enddate");
	if(!isset($absdata['Laat'][0]))
	{
	  $absdata['Absent'][0] = 0;
	  $absdata['Laat'][0] = 0;
	}
	echo("<P class=ritem><SPAN class=halfwidth>Verzuim: ". $absdata['Absent'][0]. "</SPAN><SPAN class=halfwidth>Te laat: ". $absdata['Laat'][0]. "</SPAN></p>");
	echo("<P class=remarks>Opmerkingen:</p>");
	echo("<p class=signpart><SPAN class=signlabel>Handtekening Mentor:</SPAN><SPAN class=signline>&nbsp;</SPAN></p>");  
	echo("<p class=signpart><SPAN class=signlabel>Handtekening Directrice:</SPAN><SPAN class=signline>&nbsp;</SPAN></p>");  
	echo("</DIV><P class=footer>&nbsp;</P>");
  }
  

?>
