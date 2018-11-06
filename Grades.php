<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2015 Aim4me N.V.   (http://www.aim4me.info)       |
// +----------------------------------------------------------------------+
// | This program is free software.  You can redistribute in and/or       |
// | modify it under the terms of the GNU General Public License Version  |
// | 2 as published by the Free Software Foundation.                      |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY, without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program;  If not, write to the Free Software         |
// | Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.            |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
require_once("displayelements/displayelement.php");
require_once("student.php");
require_once("teacher.php");
require_once("group.php");
require_once("schooladmingradecalc.php"); //@grades
require_once("studentsorter.php");

if(!isset($_SESSION['dtext']['no_result']))
  $_SESSION['dtext']['no_result'] = "?";

class Grades extends displayelement
{
  protected function add_contents()
  {
  }
  
  public function show_contents()
  {
    if(isset($_GET['sid']))
	{ // Student related views
	  if(isset($_GET['period']))
	  {
	    if(isset($_GET['mid']))
	      $this->stud_per_sub_grades();
		else
		  $this->stud_per_grades();
	  }
	  else
	    $this->stud_grades();
	}
	else if(isset($_GET['cid']))
	{ // Subject releated views
	  if(isset($_GET['period']))
	    $this->sub_per_grades();
	  else
	    $this->sub_grades();
	}
	else // Main view
	  $this->main_grades();
  }
  
  private function main_grades()
  {
    global $teachercode,$noendfiltergroups;
		global $altwftable;
		global $carecodetable,$carecodecolors;
		$dtext = $_SESSION['dtext'];
		// Javascript to unhide hidden results
		echo("<SCRIPT> function unhide(mid) 
		{ objs2=document.getElementsByTagName('span'); for(i in objs2) 
			{ if(objs2[i].className == 'mgrade'+mid) 
				{objs2[i].style.fontSize='12px';} 
			} 
			objs2=document.getElementsByTagName('td'); for(i in objs2) 
			{ if(objs2[i].className == 'mgrade'+mid) objs2[i].style.fontSize='12px'; }
		 }   </SCRIPT>");
			// First we get the data from existing students in an array.
		$group = new group();
		$group->load_current();
		$stud_list = student::student_list($group);
		$me = new teacher();
		$me->load_current();

			// Get the list of periods with their details
		$periods = inputclassbase::load_query("SELECT * FROM period ORDER BY id");
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
		// See which period needs to be unsupressed (0 meaning final grade if weight factors are defined per period, otherwise the period matching current date)
		$periodweights = inputclassbase::load_query("SELECT * FROM finalcalc WHERE weigth > 0.0");
		if(isset($periodweights['period']) && (!isset($noendfiltergroups) || !preg_match($noendfiltergroups,$_SESSION['CurrentGroup'])))
		{
			$showperiod = 0;
		}
		else
		{
			$showperiod = 1;
			foreach($periods['id'] AS $pix => $pid)
			{
			if($periods['startdate'][$pix] <= date('Y-m-d') && $periods['enddate'][$pix] >= date('Y-m-d'))
				$showperiod = $pid;
			}
		}
		// Get the weight factors per period for the student where alternative weight factors are applicable
		if(isset($altwftable))
		{
			$wfresults = inputclassbase::load_query("SELECT sid,period,weigth FROM `". $altwftable. "` LEFT JOIN finalcalc ON (mid=0-CAST(data AS SIGNED))");
			if(isset($wfresults))
			{
				foreach($wfresults['sid'] AS $wfix => $wfsid)
				$wfps[$wfsid][$wfresults['period'][$wfix]] = $wfresults['weigth'][$wfix];
			}
		}

			// Get the list of applicable subjects with their details
			if(isset($teachercode))
				$sql_query = "SELECT class.mid,cid,shortname,fullname, GROUP_CONCAT(distinct ". $teachercode. ".data) AS `tcode` FROM class LEFT JOIN subject using (mid) LEFT JOIN ". $teachercode. " USING(tid) ";
			else
				$sql_query = "SELECT class.mid,cid,shortname,fullname FROM class LEFT JOIN subject using (mid) ";
			$sql_query .= "LEFT JOIN (SELECT gid FROM (SELECT sid FROM sgrouplink WHERE gid=". $group->get_id(). " GROUP BY sid) AS t1 LEFT JOIN sgrouplink USING(sid)
										 GROUP BY gid) AS t2 USING(gid) WHERE t2.gid IS NOT NULL AND show_sequence IS NOT NULL ";
		if(!isset($_GET['showallsubs']) && !$me->has_role("admin"))
			$sql_query .= "AND class.tid = ". $me->get_id(). " ";
		$sql_query .= "GROUP BY mid ORDER BY show_sequence";
		$subjects = inputclassbase::load_query($sql_query);

			// Get the list of grades for periods
			//~23mrt12 $sql_query = "SELECT gradestore.* FROM gradestore LEFT JOIN sgrouplink USING(sid) LEFT JOIN period ON(period.id=gradestore.period) WHERE gid=". $group->get_id(). " AND gradestore.year=period.year AND period > 0";
			$sql_query = "SELECT gradestore.* FROM gradestore LEFT JOIN sgrouplink USING(sid) LEFT JOIN period ON(period.id=gradestore.period) 
									LEFT JOIN (SELECT mid,sid FROM sgrouplink LEFT JOIN class USING (gid)) AS t1 ON(t1.mid=gradestore.mid AND t1.sid=sgrouplink.sid) 
						WHERE sgrouplink.gid=". $group->get_id(). " AND
						t1.sid IS NOT NULL AND gradestore.year='". $periods['year'][0]. "' GROUP BY period,mid,sid";
			$grades = inputclassbase::load_query($sql_query);
			if(isset($grades))
				foreach($grades['result'] AS $grix => $gres)
				{
					if($grades['period'][$grix] != 0)	  
						$results_array[$grades['period'][$grix]][$grades['mid'][$grix]][$grades['sid'][$grix]] = $gres;
			else
					$final_results_array[$grades['mid'][$grix]][$grades['sid'][$grix]] = $gres;
				}
			// Get the list of average grades for normal periods
			$sql_query = "SELECT AVG(result) as `average`,period,mid FROM gradestore LEFT JOIN sgrouplink USING(sid) LEFT JOIN period ON(period.id=gradestore.period) WHERE gradestore.year=period.year AND sgrouplink.gid=". $group->get_id(). " GROUP BY period,mid";
			$avgrades = inputclassbase::load_query($sql_query);
			if(isset($avgrades))
				foreach($avgrades['average'] AS $grix => $grav)
				$average_array[$avgrades['period'][$grix]][$avgrades['mid'][$grix]] = $grav;

			// Get the list of average final grades
			$sql_query = "SELECT mid,AVG(result) as `average` FROM gradestore LEFT JOIN sgrouplink USING(sid) where period='0' AND gradestore.year='" . $periods['year'][0] . "' AND sgrouplink.gid=". $group->get_id(). " GROUP BY mid";
		$favgrades = inputclassbase::load_query($sql_query);
		if(isset($favgrades))
			foreach($favgrades['average'] AS $grix => $fgrav)
				$final_average_array[$favgrades['mid'][$grix]] = $fgrav;

			// Get the list of pass criteria per subject
			$sql_query = "SELECT * FROM class LEFT JOIN coursepasscriteria USING(masterlink)";
			$passcrits = inputclassbase::load_query($sql_query);
		if(isset($passcrits))
			foreach($passcrits['minimumpass'] AS $crix => $mpass)
				$passpoint[$passcrits['mid'][$crix]] = $mpass;
		
			$digits = inputclassbase::load_query("SELECT MAX(digitsafterdot) AS digits FROM reportcalc");
			
			// get the digits for period and final per subject
			$digmidq = "SELECT digitsafterdotperiod,digitsafterdotfinal,mid from class left join coursepasscriteria using(masterlink) WHERE gid=". $group->get_id(). " UNION SELECT MAX(digitsafterdotperiod),MAX(digitsafterdotfinal),0 FROM coursepasscriteria";
			$digmidqr = inputclassbase::load_query($digmidq);
			foreach($digmidqr['mid'] AS $dmix => $mid)
			{
				$digmidp[$mid] = $digmidqr['digitsafterdotperiod'][$dmix];
				$digmidf[$mid] = $digmidqr['digitsafterdotfinal'][$dmix];
			}
		
		// Create an array of subjects per student so we can mark absence of result different from having and not having the subject
		$shassq = "SELECT sid,mid FROM sgrouplink LEFT JOIN student USING(sid) LEFT JOIN (SELECT sid,mid FROM class LEFT JOIN sgrouplink USING(gid) GROUP BY sid,mid) AS t1 USING(sid) WHERE gid=". $group->get_id();
		$shassqr = inputclassbase::load_query($shassq);
		if(isset($shassqr['mid']))
		{
			foreach($shassqr['sid'] AS $six => $ssid)
			{
				$shass[$ssid][$shassqr['mid'][$six]] = true;
			}
		}
		 
			echo("<font size=+2><center>" . $dtext['grdo_title'] . " ". $dtext['group']. " ". $_SESSION['CurrentGroup']. "</font><p>");
			echo("<br><div align=left>" . $dtext['grdo_expl_1'] );
		if(!isset($_GET['showallsubs']) && !$me->has_role("admin"))
			echo(" <a href='". $_SERVER['REQUEST_URI']. "&showallsubs=1'>". $dtext['Showallsubs']. "</a>");
		echo("</div><br>"); 
		// Show a box for sorting selection
		$ssortbox = new studentsorter();
		$ssortbox->show();

			// Now create a table with all students in the group to enable to go to their grade details
			// Create the first heading row for the table
		$seqno = 1;
			echo("<table border=1 cellpadding=0 ID=gradeTable>");
			echo("<tr><th ROWSPAN=2>". $dtext['numb_token']. "</th><th ROWSPAN=2><center>" . $dtext['Lastname'] . "</th>");
			echo("<th ROWSPAN=2><center>" . $dtext['Firstname'] . "</th>");
			// Now add the heading for the subjects
		if(isset($subjects))
		 foreach($subjects['cid'] AS $sbix => $cid)
			{
			// Add inline style hiding results
			$mid = $subjects['mid'][$sbix];
			echo("<STYLE> .mgrade". $mid. " { font-size: 0; } </STYLE>");
			
				echo("<th COLSPAN=" . ($showperiod == 0 ? COUNT($periods['id'])+1 : COUNT($periods['id'])) . "><center>");
				echo("<a href=". $_SERVER['REQUEST_URI']. "&cid=" . $cid . " onMouseover='unhide(". $mid. ");' title='". $subjects['fullname'][$sbix]. "'>");
				echo($subjects['shortname'][$sbix]);
				echo("</a>");
			if(isset($subjects['tcode'][$sbix]) && $subjects['tcode'][$sbix] != "")
			{
				echo("<span class=mgrade". $mid. ">(". $subjects['tcode'][$sbix]. ")</span>");
			}
			echo("</th>");
			}
			echo("</tr>");
			// Create the second heading row for the table
			echo("<tr>");
			// Now add the periods below each subject
		if(isset($subjects))
		foreach($subjects['cid'] AS $sbix => $cid)
			{
			$mid = $subjects['mid'][$sbix];
				foreach($periods['id'] AS $pix => $pid)
				{
				if($pid != $showperiod)
						echo("<td class=mgrade". $mid. "><center><a href=". $_SERVER['REQUEST_URI']. "&period=$pid&cid=" . $cid . ">". $dtext['Period_marker']. $pid . "</a></td>");
				else
						echo("<td><center><a href=". $_SERVER['REQUEST_URI']. "&period=$pid&cid=" . $cid . ">". $dtext['Period_marker']. $pid . "</a></td>");
				}
			if($showperiod == 0)
					echo("<td><center>" . $dtext['fin_per_ind'] . "</td>");
			}
			echo("</tr>");

			// Create a row in the table for every existing student in the group
		$altrow = false;
		$lastsortval = "";
		if(isset($stud_list))
		foreach($stud_list AS $cstud)
			{
		 if($cstud <> null)
		 { 
				$sid = $cstud->get_id();
			// See if carecodes are applicable and define the prefix for it if so
			if(isset($carecodetable))
			{
			$careprefix = inputclassbase::load_query("SELECT `". $carecodecolors. "`.tekst AS pref FROM `". $carecodetable. "` LEFT JOIN `". $carecodecolors. "` ON(data=id) WHERE sid=". $sid);
				// See what label the carecode field has
				$cclabelqr = inputclassbase::load_query("SELECT label FROM student_details WHERE table_name='". $carecodetable. "'");
			if(isset($cclabelqr['label']))
					$cctitle = $cclabelqr['label'][0]. ": ". $cstud->get_student_detail($carecodetable);
			}
			if(isset($_SESSION['ssortertable']) && $_SESSION['ssortertable'] != '' && $_SESSION['ssortertable'] != '-')
					$lastsortval = $cstud->get_student_detail($_SESSION['ssortertable']);
				echo("<tr". ($altrow ? ' class=altbg' : ''). (isset($careprefix['pref'][0]) ? " style='". $careprefix['pref'][0]. "'" : ''). (isset($cctitle) ? " title='". $cctitle. "'" : ''). "><td style='text-align: right'>". $seqno++. "</td>");
				echo("<td onClick='window.location=\"teacherpage.php?Page=Grades&sid=" . $sid ."\";'>");
				echo($cstud->get_lastname(). "</td>");
				echo("<td onClick='window.location=\"teacherpage.php?Page=Grades&sid=" . $sid ."\";'>");
				echo($cstud->get_firstname(). "</td>");
				// Add the Grades
			if(isset($subjects))
			foreach($subjects['mid'] AS $sbix => $mid)
				{ // each subject
					foreach($periods['id'] AS $pix => $pp)
					{ // add the grades for regular periods
				if($pp > 0)
				{
					if($pp != $showperiod)
								echo("<td class=mgrade". $mid. ">");
					else
								echo("<td onMouseover='unhide(". $mid. ");'>");
				echo("<center><a href=". $_SERVER['REQUEST_URI']. "&period=$pp&sid=$sid&mid=$mid>");
							if(isset($results_array[$pp][$mid][$sid]))
							{ 
								$result = $results_array[$pp][$mid][$sid];
								// Colour depends on pass criteria
					if($result < '@')
					{ //Numeric result
						if(isset($wfps[$sid][$pp]) && $wfps[$sid][$pp] == 0.0) echo("<font color=gray>");
									else if($passpoint[$mid] > $result) echo("<font color=red>");
									else echo("<font color=blue>");
									if($periods['status'][$pix] == 'final') echo("<b>"); else echo("<i>");
									echo(number_format($results_array[$pp][$mid][$sid],(isset($digmidp[$mid]) ? $digmidp[$mid] : $digmidp[0]),$dtext['dec_sep'],$dtext['mil_sep']));
									if($periods['status'][$pix] == 'final') echo("</b>"); else echo("</i>");
									echo("</font>");
					// Maintain the partial average
						if(isset($part_average[$pp][$mid]))
						{
							$part_average[$pp][$mid] += $result;
							$part_avg_count[$pp][$mid] += 1.0;
						}
						else
						{
							$part_average[$pp][$mid] = $result;
							$part_avg_count[$pp][$mid] = 1.0;
						}
					}
					else // Alpha result
						echo($result);
							}
							else
								echo("-");
							echo("</a></td>");
						}
			}
					// Add the final grade
			if($showperiod == 0)
			{
				echo("<td onMouseover='unhide(". $mid. ");'><center>");
				if(isset($final_results_array[$mid][$sid]))
				{
					$result = $final_results_array[$mid][$sid];
					// Colour depends on pass criteria
					if($result < '@')
					{ // Numeric result
					if($passpoint[$mid] > $result) echo("<font color=red>");
					else echo("<font color=blue>");
					if($any_open == 'N') echo("<b>"); else echo("<i>");
					echo($final_results_array[$mid][$sid]);
					if($any_open == 'N') echo("</b>"); else echo("</i>");
					echo("</font>");
					// Maintain the partial average
					if(isset($part_average[0][$mid]))
					{
						$part_average[0][$mid] += $result;
						$part_avg_count[0][$mid]+= 1.0;
					}
					else
					{
						$part_average[0][$mid] = $result;
						$part_avg_count[0][$mid] = 1.0;
					}
					}
					else //Alpha result
					echo($result);
				}
				else
				{
					if(isset($shass[$sid][$mid]))
					echo("<font color=red>". $dtext['no_result']);
					else
					echo("-");
				}
				echo("</td>");
			}
				}
				echo("</tr>");
			$altrow = !$altrow;
		 }
			 else
			{
				// Convert the partial averages to a single array
			 if(isset($part_average))
				foreach($part_average AS $ppid => $padata)
				{
				foreach($padata AS $pmid => $pres)
					$part_average[$ppid][$pmid] = $part_average[$ppid][$pmid] / $part_avg_count[$ppid][$pmid];
				}
					$this->main_grade_avg_row($dtext['Partial_average'],$subjects,$periods,(isset($part_average) ? $part_average : NULL),($showperiod == 0 && isset($part_average[0]) ? $part_average[0] : NULL),$digits,$passpoint,$any_open,$lastsortval,$showperiod);
				unset($part_average);
				unset($part_avg_count);
				$segregationlineadded=true;
			}	 
			}
		if(isset($segregationlineadded))
		{
				// Convert the partial averages to a single array
				if(isset($part_average))
				 foreach($part_average AS $ppid => $padata)
				 {
					foreach($padata AS $pmid => $pres)
					$part_average[$ppid][$pmid] = $part_average[$ppid][$pmid] / $part_avg_count[$ppid][$pmid];
				 }
					$this->main_grade_avg_row($dtext['Partial_average'],$subjects,$periods,isset($part_average) ? $part_average : NULL,$showperiod == 0 && isset($part_average[0]) ? $part_average[0] : NULL,$digits,$passpoint,$any_open,$lastsortval,$showperiod);
		}
		
			// Add the averages
		$this->main_grade_avg_row($dtext['Group_average'],$subjects,$periods,isset($average_array) ? $average_array : NULL,isset($final_average_array) ? $final_average_array : NULL,$digits,$passpoint,$any_open,"",$showperiod);
    echo("</table>");

    // Now we show links for each period to print the results for each student
    foreach($periods['id'] AS $p)
      if($p != 0)
        echo("<BR><a href=\"printperiodcard.php?period=". $p. "\" target=\"print\">". $dtext['print_res']. " ". $dtext['4_per']. " ". $p. "</a>");

  }
  
  private function main_grade_avg_row($rlabel,$subjects,$periods,$average_array,$final_average_array,$digits,$passpoint,$any_open,$lastsortval,$showperiod)
  {
    $dtext = $_SESSION['dtext'];
    echo("<tr class=average><td>&nbsp;</td>");
	if($lastsortval == "")
	  echo("<td colspan=2>");
	else
	  echo("<td>");
    echo($rlabel. "</td>");
	if($lastsortval != "")
      echo("<td>". $lastsortval. "</td>");
    // Add the Grades
	if(isset($subjects))
	foreach($subjects['mid'] AS $mid)
    { // each subject
      foreach($periods['id'] AS $pix => $pp)
      { // add the average grades for regular periods
	    if($pp > 0)
		{
		  if($pp == $showperiod)
            echo("<td>");
	      else
            echo("<td class=mgrade". $mid. ">");
          if(isset($average_array[$pp][$mid]))
          { 
            $result = $average_array[$pp][$mid];
			if($result != "" && $result != 0.0)
			{ // Numeric result
              // Colour depends on pass criteria
              if($passpoint[$mid] > $result) echo("<font color=red>");
              else echo("<font color=blue>");
              if($periods['status'][$pix] == 'final') echo("<b>"); else echo("<i>");
              echo(number_format($average_array[$pp][$mid],$digits['digits'][0],$dtext['dec_sep'],$dtext['mil_sep']));
              if($periods['status'][$pix] == 'final') echo("</b>"); else echo("</i>");
              echo("</font>");
			}
			else // Alpha result
			  echo("&nbsp;");
          }
          else
            echo("-");
          echo("</td>");
		}
      }
      // Add the final average grade
	  if($showperiod == 0)
	  {
		  echo("<td><center>");
		  if(isset($final_average_array[$mid]))
		  {
			$result = $final_average_array[$mid];
			if($result != "" && $result != 0.0)
			{ // Numeric
			  // Colour depends on pass criteria
			  if($passpoint[$mid] > $result) echo("<font color=red>");
			  else echo("<font color=blue>");
			  if($any_open == 'N') echo("<b>"); else echo("<i>");
			  echo(number_format($final_average_array[$mid],$digits['digits'][0],$dtext['dec_sep'],$dtext['mil_sep']));
			  if($any_open == 'N') echo("</b>"); else echo("</i>");
			  echo("</font>");
			}
			else // Alpha value
			  echo("&nbsp;");
		  }
		  else
			echo("-");
		  echo("</td>");
	  }
    }
    echo("</tr>");
  }
  
  private function stud_grades()
  {
    global $altwftable;
    global $teachercode;
	$dtext = $_SESSION['dtext'];
	$student = new student($_GET['sid']);
	$group = new group();
	$group->load_current();

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
    if(isset($teachercode))
      $sql_query = "SELECT class.mid,cid,shortname,fullname,`". $teachercode. "`.data AS `tcode` FROM (SELECT gid FROM sgrouplink WHERE sid=". $student->get_id(). ") AS t1 LEFT JOIN `class` USING(gid) LEFT JOIN subject using (mid) left join `". $teachercode. "` USING(tid) where show_sequence IS NOT NULL GROUP BY mid ORDER BY show_sequence";
    else
      $sql_query = "SELECT class.mid,cid,shortname,fullname FROM (SELECT gid FROM sgrouplink WHERE sid=". $student->get_id(). ") AS t1 LEFT JOIN `class` USING(gid) LEFT JOIN subject using (mid) where show_sequence IS NOT NULL GROUP BY mid ORDER BY show_sequence";
    $subjects = inputclassbase::load_query($sql_query);
	
	// Get the weigth factors per period for the student where alternative weight factors are applicable
	if(isset($altwftable))
	{
	  $wfresults = inputclassbase::load_query("SELECT period,weigth FROM `". $altwftable. "` LEFT JOIN finalcalc ON (mid=0-CAST(data AS SIGNED)) WHERE sid=". $student->get_id());
	  if(isset($wfresults))
	  {
	    foreach($wfresults['period'] AS $wfix => $wfper)
		  $wfps[$wfper] = $wfresults['weigth'][$wfix];
	  }
	}

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

    $sql_query = "SELECT * FROM class inner join coursepasscriteria using (masterlink)";
    $passcrits = inputclassbase::load_query($sql_query);
	if(isset($passcrits))
	  foreach($passcrits['minimumpass'] AS $crix => $mpass)
	    $passpoint[$passcrits['mid'][$crix]] = $mpass;
  
    $digits = inputclassbase::load_query("SELECT MAX(digitsafterdot) AS digits FROM reportcalc");
  

    echo("<font size=+2><center>" . $dtext['gcard_4'] . " " . $student->get_firstname() . " " . $student->get_lastname() . "</font><p>");
    echo("<br><div align=left>" . $dtext['gcrd_expl_1'] . "</dev><br>"); 

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
        echo("<td><center><a href=". $_SERVER['REQUEST_URI']. "&period=". $p. ">". $dtext['Period_marker']. $p . "</a></td>");
    }
	if(isset($final_results_array))
      echo("<td><center>" . $dtext['fin_per_ind'] . "</td></tr>"); 
  

    // Create a row in the table for every subject
	$altrow = false;
	foreach($subjects['mid'] AS $sbix => $mid)
    { // each subject
      echo("<tr". ($altrow ? ' class=altbg' : ''). "><td>" . $subjects['fullname'][$sbix] . "</td>");
	  foreach($periods['id'] AS $pix => $pp)
      { // add the grades for regular periods
	    if($pp > 0)
		{
          echo("<td><center><a href=". $_SERVER['REQUEST_URI']. "&period=$pp&mid=$mid>");
          if(isset($results_array[$pp][$mid]))
          { 
            $result = $results_array[$pp][$mid];
            // Colour depends on pass criteria
			if($result < '@')
			{ // Numeric value
			  if(isset($wfps[$pp]) && $wfps[$pp] == 0.0) echo("<font color=gray>");
              else if($passpoint[$mid] > $result) echo("<font color=red>");
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
          echo("</a></td>");
		}
      }
      // Add the final grade
	  if(isset($final_results_array))
	  {
		  echo("<td><center>");
		  if(isset($final_results_array[$mid]))
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
		  echo("</td>");
	  }
      echo("</tr>");
	  $altrow = !$altrow;
    }
    echo("</table>");
  }
  
  private function stud_per_grades()
  {
    global $teachercode;
	$dtext = $_SESSION['dtext'];
	$student = new student($_GET['sid']);
	$group = new group();
	$group->load_current();

    // Get the list of periods with their details
	$period = inputclassbase::load_query("SELECT * FROM period WHERE id=". $_GET['period']);
    // Get the list of applicable subjects with their details
    if(isset($teachercode))
      $sql_query = "SELECT class.mid,cid,fullname,shortname,`". $teachercode. "`.data AS `tcode` FROM (SELECT gid FROM sgrouplink WHERE sid=". $student->get_id(). ") AS t1 LEFT JOIN `class` USING(gid) LEFT JOIN subject using (mid) LEFT JOIN `". $teachercode. "` USING(tid) where show_sequence IS NOT NULL GROUP BY mid ORDER BY show_sequence";
    else
      $sql_query = "SELECT class.mid,cid,fullname,shortname FROM (SELECT gid FROM sgrouplink WHERE sid=". $student->get_id(). ") AS t1 LEFT JOIN `class` USING(gid) LEFT JOIN subject using (mid) where show_sequence IS NOT NULL GROUP BY mid ORDER BY show_sequence";
    $subjects = inputclassbase::load_query($sql_query);

    // Get a list of testresults for the current period
    $sql_query = "SELECT result,type,mid,testdef.tdid FROM testresult LEFT JOIN testdef using (tdid) LEFT JOIN class USING (cid) LEFT JOIN period ON(period.id=testdef.period) where sid=". $student->get_id(). " AND period=". $_GET['period']. " AND period.year=testdef.year ORDER BY testresult.last_update";
    $grades = inputclassbase::load_query($sql_query);
    if(isset($grades))
      foreach($grades['result'] AS $grix => $gres)  
        $test_array[$grades['mid'][$grix]][$grades['type'][$grix]][$grades['tdid'][$grix]] = $gres;


    // Get the list of pass criteria per subject & testtype
    $sql_query = "SELECT * FROM reportcalc ORDER BY testtype,mid";
    $passcrits = inputclassbase::load_query($sql_query);
	if(isset($passcrits))
	  foreach($passcrits['passthreshold'] AS $crix => $mpass)
	    $passpoints[$passcrits['testtype'][$crix]][$passcrits['mid'][$crix]] = $mpass;

    echo("<font size=+2><center>" . $dtext['gcard_4'] . " " . $student->get_firstname() . " " . $student->get_lastname() . " " . $dtext['4_per'] . " " .$period['id'][0]. "</font><p>");
    echo("<br>");

    // Now we must find out how many entries max. for each type of test (max # of collumns)
    if(isset($test_array))
    {
      foreach($test_array AS $subji => $subtest)
      {
        foreach($subtest AS $tti => $testtype)
          $testcount[$tti][$subji] = count($testtype);
      }
    }

    if(isset($test_array))
    {
      foreach($passpoints AS $type => $value)
      {
        $typecount[$type] = 0;
        if(isset($testcount[$type]))
        {
          foreach($testcount[$type] as $count)
          {
            if($typecount[$type] < $count)
              $typecount[$type] = $count;
          }
        }
      }
    }

    if(isset($test_array) && $period['status'][0] != 'closed')
    {   
      // Now create a table with all subjects for this student to enable to go to the grade details
      // Create the first heading row for the table
      echo("<table border=1 cellpadding=0>");
      echo("<tr><td><center>" . $dtext['Subject'] . "</td>");
      // Now add types heading
      foreach($typecount as $type => $count)
      {
        if($count > 0)
          echo("<td COLSPAN='$count'><center>" . $type . "</td>");
      }
      echo("</tr>"); 
  

      // Create a row in the table for every subject
      $currentTest = 1;
	  $altrow = false;
	  foreach($subjects['mid'] AS $s => $mid)
      { // each subject
        $cid = $subjects['cid'][$s];
        echo("<tr". ($altrow ? ' class=altbg' : ''). "><td>" . $subjects['fullname'][$s] . "</td>");
        foreach($typecount as $type => $count)
        {
          if(isset($passpoints[$type][$mid]))
            $passpoint=$passpoints[$type][$mid];
          else
            $passpoint=$passpoints[$type][0];
          if(isset($testcount[$type][$mid]))
          {
            foreach($test_array[$mid][$type] AS $tdid => $result)
            {
               echo("<td>");
			   if($result < '@')
			   { // Numeric result
                 // Colour depends on pass criteria
                 if($passpoint > $result) echo("<font color=red>");
                 else echo("<font color=blue>");
                 echo($result);
                 echo("</font>");
			   }
			   else
			     echo($result);
			   echo("</td>");
             }

             // Now pad with empty cells
             for($r=$testcount[$type][$mid]; $r<$count; $r++)
              echo("<td> </td>");
          }
          else
          { // No tests found for this type & subject!
            for($r=0;$r<$count;$r++)
              echo("<td> </td>");
          }
        }
        echo("</tr>");
		$altrow = !$altrow;
      }
      echo("</tr>");
      echo("</table>");
    }
    else
    { // No test results found or period is closed
      if($period['status'][0] == 'closed')
        echo($dtext['perres_expl_1']);
      else
        echo($dtext['perres_expl_2']);
    }
  }
  
  private function stud_per_sub_grades()
  {
    global $userlink,$teachercode;
	$userlink = inputclassbase::$dbconnection;
	$dtext = $_SESSION['dtext'];
	$student = new student($_GET['sid']);
	$mid = $_GET['mid'];
	$group = new group();
	$group->load_current();
    // Get the list of periods with their details
	$period = inputclassbase::load_query("SELECT * FROM period WHERE id=". $_GET['period']);

    $subject_array = inputclassbase::load_query("SELECT * FROM (SELECT gid FROM sgrouplink WHERE sid=". $student->get_id(). ") AS t1 LEFT JOIN `class` using (gid) LEFT JOIN subject USING(mid) where subject.mid='$mid'");
    $cid = $subject_array['cid'][0];

    // Calculate the grade again!
    SA_calcGrades($student->get_id(), $cid, $period['id'][0]); //@grades
    
    $sql_query = "SELECT testdef.*,AVG(testresult.result) AS `average`,STD(testresult.result) AS `std` FROM testdef 
	              LEFT JOIN period ON(period.id=testdef.period) LEFT JOIN testresult ON(testresult.tdid=testdef.tdid) 
				  WHERE cid='$cid' AND testdef.period=". $period['id'][0]. " AND period.year=testdef.year AND testdef.type <> '' 
				  GROUP BY tdid";
	$sql_query .= " UNION SELECT testdef.*,'-' `average`,'-' AS `std` FROM testresult
	              LEFT JOIN testdef USING(tdid) LEFT JOIN period ON (period.id=testdef.period) LEFT JOIN `class` USING(cid)
				  WHERE mid='$mid' AND testdef.period=". $period['id'][0]. " AND period.year=testdef.year AND testdef.type <> '' AND cid <> ". $cid. " AND sid=". $student->get_id(). "
				  GROUP BY tdid ORDER BY date";
    $testdef_array = inputclassbase::load_query($sql_query);

    // Get the list of grades for the given subject & period & student
	if(isset($teachercode))
      $sql_query = "SELECT tdid,result,CONCAT(IF(data IS NOT NULL,data,'". $dtext['no_result']. "'),':',last_update) AS mark FROM testresult LEFT JOIN `". $teachercode. "` USING(tid) where sid=". $student->get_id();
	else
      $sql_query = "SELECT tdid,result,last_update AS mark FROM testresult where sid=". $student->get_id();
	$res = inputclassbase::load_query($sql_query);
	if(isset($res))
	  foreach($res['result'] AS $rid => $tres)
	  {
	    $results_array[$res['tdid'][$rid]] = $tres;
		$results_mark[$res['tdid'][$rid]] = $res['mark'][$rid];
	  }

    $rcalc = inputclassbase::load_query("SELECT * FROM reportcalc");
	if(isset($rcalc))
	  foreach($rcalc['passthreshold'] AS $rcid => $ppt)
	  {
	    if($rcalc['mid'][$rcid] == $mid || ($rcalc['mid'][$rcid] == 0 && !isset($passpoint[$rcalc['testtype'][$rcid]])))
	      $passpoint[$rcalc['testtype'][$rcid]] = $ppt;
	  }

    // Get the final result from gradestore.
    $sql_query = "SELECT * FROM gradestore inner join period using (year) WHERE gradestore.period=period.id AND sid=". $student->get_id(). " AND mid='$mid' AND period=". $period['id'][0];
	$fres = inputclassbase::load_query($sql_query);
	if(isset($fres))
	  $finalresult = $fres['result'][0];
  
    $digits = inputclassbase::load_query("SELECT MAX(digitsafterdot) AS digits FROM reportcalc");
  
    echo("<font size=+2><center>" . $dtext['Tres_4'] . " " . $student->get_firstname() . " " . $student->get_lastname() . " " . $dtext['on'] . " " . $subject_array['fullname'][0] . " " . $dtext['4_per'] . " " .$period['id'][0] . "</font><p>");

    echo("<br>");

    // Now create a table with test definitions and their results for this student
    // Create the first heading row for the table
    echo("<table border=1 cellpadding=0>");
    echo("<tr><td><center>" . $dtext['Tst_des'] . "</td><td><center>" . $dtext['Type'] . "</td><td><center>" . 
	       $dtext['Date'] . "</td><td>" . $dtext['Grade'] . "</td><td>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</td><td>". 
		   $dtext['Group_average']. "</td><td>". $dtext['Group_std']. "</td><td>". $dtext['Time']. "</td></tr>");
    // Now add the test definitions
    // Create a row in the table for every existing test definition
	$altrow = false;
	if(isset($testdef_array))
	foreach($testdef_array['tdid'] AS $t => $tdid)
    {
      echo("<tr". ($altrow ? ' class=altbg' : ''). ">");
      echo("<td>" . $testdef_array['description'][$t] . "</td>");
      echo("<td>" . $testdef_array['type'][$t] . "</td>");
      echo("<td>" . inputclassbase::mysqldate2nl($testdef_array['date'][$t]) . "</td>");
      // Add the Grades
      echo("<td><center>");
      if(isset($results_array[$tdid]))
      { 
        $result = $results_array[$tdid];
        // Colour depends on pass criteria
		if($result < '@')
		{ // Numeric value
          if($passpoint[$testdef_array['type'][$t]] > $result) echo("<font color=red>");
          else echo("<font color=blue>");
					if($result > 0.0)
						echo(number_format($result,$digits['digits'][0],$dtext['dec_sep'],$dtext['mil_sep']));
					else
						echo($result);
          echo("</font>");
		}
		else
		  echo($result);
      }
      else
        echo("-");
      echo("</td><td>&nbsp</td>");
	  echo("<td><center>". ($testdef_array['average'][$t] == '-' ? '-' : number_format($testdef_array['average'][$t],2,$dtext['dec_sep'],$dtext['mil_sep'])). 
	       "</td><td><center>". ($testdef_array['std'][$t] == '-' ? '-' : number_format($testdef_array['std'][$t],2,$dtext['dec_sep'],$dtext['mil_sep'])). "</td>");
      echo("<td>" . (isset($results_mark[$tdid]) ? $results_mark[$tdid] : "") . "</td>");
      echo("</tr>");
	  $altrow = !$altrow;
    }
    // Now add one row with the calculated result
    if(isset($finalresult))
    {
      echo("<tr><td><center>");
      if(isset($testdef_array))
        echo($dtext['Calc_res']);
      else
        echo($dtext['His_res']);
	  if($finalresult < '@') // Numeric result
        echo("</td><td><center>-</td><td><center>-</td><td><center><B>" . number_format($finalresult,$digits['digits'][0],$dtext['dec_sep'],$dtext['mil_sep']) . "</B></td><td>&nbsp</td><td>&nbsp</td><td>&nbsp</td>");
	  else
        echo("</td><td><center>-</td><td><center>-</td><td><center><B>" . $finalresult . "</B></td><td>&nbsp</td><td>&nbsp</td><td>&nbsp</td>");
    }
    echo("</table>");
  }
  
  private function sub_grades()
  {
    global $altwftable;
	$dtext = $_SESSION['dtext'];
	$group = new group();
	$group->load_current();
	$stud_list = student::student_list($group);

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
    $sql_query = "SELECT * FROM subject LEFT join class using (mid) WHERE cid=". $_GET['cid'];
    $subjects = inputclassbase::load_query($sql_query);
    // Get the list of grades for normal periods
    $sql_query = "SELECT * FROM gradestore LEFT JOIN student USING(sid) LEFT JOIN sgrouplink USING(sid) LEFT JOIN period ON(period.id=gradestore.period) WHERE gradestore.year=period.year AND sgrouplink.gid=". $group->get_id(). " GROUP BY sid,mid,period";
	$results = inputclassbase::load_query($sql_query);
	if(isset($results))
	  foreach($results['result'] AS $rix => $res)
	  {
	    $results_array[$results['period'][$rix]][$results['mid'][$rix]][$results['sid'][$rix]] = $res;
	  }

    // Get the list of final grades
    $sql_query = "SELECT * FROM gradestore LEFT JOIN student using (sid) LEFT JOIN sgrouplink USING(sid) where period='0' AND gradestore.year='" . $periods['year'][0] . "' AND sgrouplink.gid=". $group->get_id();
	$fresults = inputclassbase::load_query($sql_query);
	if(isset($fresults))
	  foreach($fresults['result'] AS $rix => $res)
	  {
	    $final_results_array[$fresults['mid'][$rix]][$fresults['sid'][$rix]] = $res;
	  }
	
	// Get the weigth factors per period for the student where alternative weight factors are applicable
	if(isset($altwftable))
	{
	  $wfresults = inputclassbase::load_query("SELECT sid,period,weigth FROM `". $altwftable. "` LEFT JOIN finalcalc ON (mid=0-CAST(data AS SIGNED))");
	  if(isset($wfresults))
	  {
	    foreach($wfresults['sid'] AS $wfix => $wfsid)
		  $wfps[$wfsid][$wfresults['period'][$wfix]] = $wfresults['weigth'][$wfix];
	  }
	}

	// Remove calculated grades for subjects that are no longer part of the students set of subjects
	$remgradesq = "SELECT gradestore.sid,gradestore.mid,period FROM gradestore LEFT JOIN(SELECT sid,mid,cid 
	               FROM sgrouplink LEFT JOIN class USING(gid)) AS t1 ON(t1.sid=gradestore.sid AND t1.mid=gradestore.mid) 
				   LEFT JOIN period ON(period.id=gradestore.period) WHERE period.year=gradestore.year AND cid IS NULL";
	$remgrades = inputclassbase::load_query($remgradesq);
	if(isset($remgrades['sid']))
	  foreach($remgrades['sid'] AS $rgix => $rsid)
	  {
	    unset($results_array[$remgrades['period'][$rgix]][$remgrades['mid'][$rgix]][$rsid]);
		if(isset($final_results_array))
		  unset($final_results_array[$remgrades['mid'][$rgix]][$rsid]);
	  }

	  // Get the list of pass criteria per subject
    $sql_query = "SELECT * FROM class inner join coursepasscriteria using (masterlink) WHERE gid=". $group->get_id();
    $passcrits = inputclassbase::load_query($sql_query);
	if(!isset($passcrits))
	{
      $sql_query = "SELECT * FROM class inner join coursepasscriteria using (masterlink)";
      $passcrits = inputclassbase::load_query($sql_query);
	}
	if(isset($passcrits))
	  foreach($passcrits['minimumpass'] AS $crix => $mpass)
	  {
	    $passpoint[$passcrits['mid'][$crix]] = $mpass;
	  }

    $digits = inputclassbase::load_query("SELECT MAX(digitsafterdot) AS digits FROM reportcalc");

    // Now display results
    echo("<font size=+2><center>" . $dtext['Grades4'] . " " . $subjects['fullname'][0] . " " . $dtext['in_grp'] . " " . $group->get_groupname() . "</font><p>");
    echo("<br><div align=left>" . $dtext['grsub_expl_1'] . "</dev><br>"); 
    echo("<br>");
	// Show a box for sorting selection
	$ssortbox = new studentsorter();
	$ssortbox->show();
	
    // Now create a table with all students in the group to enable to go to their grade details
    // Create the first heading row for the table
    echo("<table border=1 cellpadding=0>");
    echo("<tr>");
    $fields = student::get_list_headers();
    foreach($fields AS $fieldname)
      echo("<th><center>". $fieldname. "</th>");
    // Now add the periods
    foreach($periods['id'] AS $pid)
	  if($pid > 0)
        echo("<th><center><a href=\"". $_SERVER['REQUEST_URI']. "&period=" . $pid . "\">". $dtext['Period_marker']. $pid . "</a></th>");
	if(isset($final_results_array))
      echo("<th><center>" . $dtext['fin_per_ind'] . "</th></tr>");    

    // Create a row in the table for every existing student in the group
	$altrow = false;
    foreach($stud_list AS $stud)
    {
	 if($stud <> null)
	 {
      $sid = $stud->get_id();
      echo("<tr". ($altrow ? ' class=altbg' : ''). ">");
	  $sdata = $stud->get_list_data();
      foreach($sdata AS $stdata)
		echo("<TD><a href=". $_SERVER['REQUEST_URI']. "&sid=" . $sid .">". $stdata. "</a></TD>");
      // Add the Grades
	  if(isset($subjects))
      foreach($subjects['mid'] AS $mid)
      { // each subject
        foreach($periods['id'] AS $pp => $p)
        { // add the grades for regular periods
		  if($p > 0)
		  {
            echo("<td><center><a href=". $_SERVER['REQUEST_URI']. "&period=$p&mid=$mid&sid=$sid>");
            if(isset($results_array[$p][$mid][$sid]))
            { 
              $result = $results_array[$p][$mid][$sid];
			  if($result < '@')
			  {
                // Colour depends on pass criteria
				if(isset($wfps[$sid][$p]) && $wfps[$sid][$p] == 0.0) echo("<font color=gray>");
                else if($passpoint[$mid] > $result) echo("<font color=red>");
                else echo("<font color=blue>");
                if($periods['status'][$pp] == 'final') echo("<b>"); else echo("<i>");
                echo(number_format($results_array[$p][$mid][$sid],$digits['digits'][0],$dtext['dec_sep'],$dtext['mil_sep']));
                if($periods['status'][$pp] == 'final') echo("</b>"); else echo("</i>");
                echo("</font>");
              }
			  else
			    echo($result);
			}
            else
              echo("-");
            echo("</a></td>");
          }
		}
        // Add the final grade
		if(isset($final_results_array))
		{
			echo("<td><center>");
			if(isset($final_results_array[$mid][$sid]))
			{
			  $result = $final_results_array[$mid][$sid];
			  if($result < '@')
			  { // Numeric
				// Colour depends on pass criteria
				if($passpoint[$mid] > $result) echo("<font color=red>");
				else echo("<font color=blue>");
				if($any_open == 'N') echo("<b>"); else echo("<i>");
				echo($final_results_array[$mid][$sid]);
				if($any_open == 'N') echo("</b>"); else echo("</i>");
				echo("</font>");
			  }
			  else // Alpha
				echo($result);
			}
			else
			  echo("-");
			echo("</td>");
		}
      }
      echo("</tr>");
	  $altrow = !$altrow;
	 }
     else
	 {
	   echo("<TR><TD COLSPAN=". (count($sdata) + (count($periods['id'])+1)).">&nbsp;</td></tr>");
	 }	 

    }
    echo("</table>");
  }
  
  private function sub_per_grades()
  {
    $dtext = $_SESSION['dtext'];
	$cid = $_GET['cid'];
	$period = $_GET['period'];

	$group = new group();
	$group->load_current();
	$stud_list = student::student_list($group);
	
	// Get the mid based on the class
	$midqr = inputclassbase::load_query("SELECT mid,fullname FROM class LEFT JOIN subject USING(mid) WHERE cid=". $cid);
	$mid=$midqr['mid'][0];
	$fullname = $midqr['fullname'][0];

    // Get the list of test definitions with their details
    $sql_query = "SELECT tdid,short_desc,type FROM testresult LEFT JOIN testdef USING(tdid) LEFT JOIN period ON(period.id=testdef.period) LEFT JOIN class USING(cid) LEFT JOIN sgrouplink USING(sid) WHERE mid='$mid' AND period='$period' AND period.year=testdef.year AND testdef.type <> '' AND sgrouplink.gid=". $group->get_id(). " GROUP BY tdid ORDER BY date,class.gid";
//    $sql_query = "SELECT tdid,short_desc,type FROM testdef LEFT JOIN period ON(period.id=testdef.period) WHERE cid='$cid' AND period='$period' AND period.year=testdef.year AND testdef.type <> '' ORDER BY date";
    $testdef_array = inputclassbase::load_query($sql_query);

    // Get the list of grades for the given subject & period
    $sql_query = "SELECT tdid,sid,result FROM testresult LEFT JOIN testdef USING(tdid) LEFT JOIN period ON(period.id=testdef.period) LEFT JOIN class USING(cid) LEFT JOIN sgrouplink USING(sid) WHERE mid='$mid' AND period='$period' AND period.year=testdef.year AND testdef.type <> '' AND sgrouplink.gid=". $group->get_id(). " ORDER BY date";
//    $sql_query = "SELECT testdef.tdid,sid,result FROM testdef inner join testresult using (tdid) LEFT JOIN period ON (period.id=testdef.period) where cid='$cid' AND period='$period' AND period.year=testdef.year";
    $result_pre = inputclassbase::load_query($sql_query);
    if(isset($result_pre['tdid']))
    foreach($result_pre['tdid'] AS $rpix => $tdid)
	  $results_array[$result_pre['sid'][$rpix]][$tdid] = $result_pre['result'][$rpix];

    // Get the list of average grades for the given subject & period
    //$sql_query = "SELECT testdef.tdid,AVG(result) AS `average` FROM testdef inner join testresult using (tdid) LEFT JOIN period ON (period.id=testdef.period) WHERE cid='$cid' AND period='$period' AND period.year=testdef.year GROUP BY tdid";
    $sql_query = "SELECT testdef.tdid,AVG(result) AS `average` FROM testdef inner join testresult using (tdid) LEFT JOIN period ON (period.id=testdef.period) LEFT JOIN `class` USING(cid) WHERE mid='$mid' AND period='$period' AND period.year=testdef.year GROUP BY tdid";
	$avg_pre = inputclassbase::load_query($sql_query);
	if(isset($avg_pre['tdid']))
	  foreach($avg_pre['tdid'] AS $avix => $tdid)
	    $average_array[$tdid] = $avg_pre['average'][$avix];

    // Get the list of stored grades for the given subject & period
    $sql_query = "SELECT * FROM period,student inner join gradestore using (sid) where period=". $period. " AND gradestore.year=period.year AND mid='$mid'";
	$period_pre = inputclassbase::load_query($sql_query);
	if(isset($period_pre['sid']))
	  foreach($period_pre['sid'] AS $prix => $sid)
	    $period_result[$sid] = $period_pre['result'][$prix];

    // Get the average of stored grades for the given subject & period
    $sql_query = "SELECT AVG(result) AS average FROM gradestore LEFT JOIN period ON (gradestore.year=period.year AND period.id=gradestore.period) LEFT JOIN sgrouplink USING(sid) where mid='$mid' AND gid=". $group->get_id(). " AND period.id='$period' GROUP BY period";
	$pa_pre = inputclassbase::load_query($sql_query);
	if(isset($pa_pre['average']))
	  $period_average = $pa_pre['average'][0];

    // Get the list of pass criteria per testtype
    $sql_query = "SELECT * FROM reportcalc";
	$pp_pre = inputclassbase::load_query($sql_query);
	if(isset($pp_pre['mid']))
	  foreach($pp_pre['mid'] AS $ppix => $pmid)
	  {
	    if($pmid == $mid)
		  $passpoint[$pp_pre['testtype'][$ppix]] = $pp_pre['passthreshold'][$ppix];
		else if($pmid == 0 && !isset($passpoint[$pp_pre['testtype'][$ppix]]))
		  $passpoint[$pp_pre['testtype'][$ppix]] = $pp_pre['passthreshold'][$ppix];		   
	  }

    // Get the list of pass criteria per subject
    $sql_query = "SELECT * FROM class inner join coursepasscriteria using (masterlink) WHERE cid='$cid'";
	$pp2_pre = inputclassbase::load_query($sql_query);
	if(isset($pp2_pre['mid']))
	  foreach($pp2_pre['mid'] AS $pp2ix => $pmid)
	  {
	    $passpoint2[$pmid] = $pp2_pre['minimumpass'][$pp2ix];
	  }

    $digits = inputclassbase::load_query("SELECT MAX(digitsafterdot) AS digits FROM reportcalc");

    echo("<font size=+2><center>" . $dtext['Tres_4'] . " " . $fullname . "<BR>" . $dtext['in_grp'] . " " . $_SESSION['CurrentGroup'] . " " . $dtext['4_per'] . " " .$period . "</font><p>");
    echo("<br><div align=left>" . $dtext['tressub_expl_1'] . "</dev><br>"); 

    echo("<br>");
	// Show a box for sorting selection
	$ssortbox = new studentsorter();
	$ssortbox->show();

    // Now create a table with all students in the group to enable to go to their grade details
    // Create the first heading row for the table
    echo("<table border=1 cellpadding=0>");
    echo("<tr>");
    $fields = student::get_list_headers();
    foreach($fields AS $fieldname)
      echo("<th><center>". $fieldname. "</th>");
    // Now add the test definitions
	if(isset($testdef_array['short_desc']))
	  foreach($testdef_array['short_desc'] AS $ssd)
        echo("<th><center>" . $ssd . "</th>");
    echo("<th align=center class=endgrade>". $dtext['Period_marker']. $period. "</th>");
    echo("</tr>");    
  
    // Create a row in the table for every existing student in the group
	$colcount=2;
	$altrow = false;
	$lastsortval = "";
	if(isset($stud_list))
	  foreach($stud_list AS $stud)
      {
	   if($stud <> null)
	   {
	    if(isset($_SESSION['ssortertable']) && $_SESSION['ssortertable'] != '' && $_SESSION['ssortertable'] != '-')
          $lastsortval = $stud->get_student_detail($_SESSION['ssortertable']);	    
        $sid = $stud->get_id();
        echo("<tr". ($altrow ? ' class=altbg' : ''). ">");
		$sdata = $stud->get_list_data();
		$colcount = count($sdata);
		foreach($sdata AS $stdata)
		  echo("<TD><a href=". $_SERVER['REQUEST_URI']. "&sid=" . $sid . ">". $stdata. "</a></TD>");
        // Add the Grades
		if(isset($testdef_array['tdid']))
		  foreach($testdef_array['tdid'] AS $tdix => $tdid)
		  {
            echo("<td><center>");
            if(isset($results_array[$sid][$tdid]))
            { 
              $result = $results_array[$sid][$tdid];
		      if($result < '@')
		      {
                // Colour depends on pass criteria
                if($passpoint[$testdef_array['type'][$tdix]] > $result) echo("<font color=red>");
                else echo("<font color=blue>");
                echo(number_format($result,$digits['digits'][0],$dtext['dec_sep'],$dtext['mil_sep']));
                echo("</font>");
				if(isset($part_average[$tdid]))
				{
				  $part_average[$tdid] += $result;
				  $part_avg_count[$tdid] += 1.0;
				}
				else
				{
				  $part_average[$tdid] = $result;
				  $part_avg_count[$tdid] = 1.0;
				}
		      }
		      else
		        echo($result);
            }
            else
              echo("-");
            echo("</td>");
          }
        // Show the calculated period prognose/result
        echo("<td class=endgrade>");
        if(isset($period_result[$sid]))
        {
          if($period_result[$sid] < '@')
	      {
            if(isset($passpoint2[$mid]) && $passpoint2[$mid] > $period_result[$sid])
              echo("<font color=red>");
            else
              echo("<font color=blue>");
            echo(number_format($period_result[$sid],$digits['digits'][0],$dtext['dec_sep'],$dtext['mil_sep']). "</font></td>");
			if(isset($part_average[0]))
			{
			  $part_average[0] += $period_result[$sid];
			  $part_avg_count[0] += 1.0;
			}
			else
			{
			  $part_average[0] = $period_result[$sid];
			  $part_avg_count[0] = 1.0;
			}
	      }
	      else
	        echo($period_result[$sid]. "</td>");
        }
        else
          echo("-</td>");
        echo("</tr>");
		$altrow = !$altrow;
	   }
	   else
	   {
		   // Convert partial average arrays to one array
		   foreach($part_average AS $itdid => $pavg)
		     $part_average[$itdid] = $pavg / $part_avg_count[$itdid];
		   // Show the average row
		   $this->show_sub_per_average_row($dtext['Partial_average'],$colcount,$testdef_array,$part_average,$part_average[0],$passpoint2,$mid,$lastsortval);
		   //echo("<TR><TD COLSPAN=". (count($fields) + count($testdef_array['tdid']) + 1).">&nbsp;</td></tr>");
		   // Unset partial averaged
		   unset($part_average);
		   unset($part_avg_count);
		   // Flag extra row with partial average need to be shown
		   $segregationrowpresent = true;
	   }	 

      }
	if(isset($segregationrowpresent))
    {
		   // Convert partial average arrays to one array
		   foreach($part_average AS $itdid => $pavg)
		     $part_average[$itdid] = $pavg / $part_avg_count[$itdid];
		   // Show the average row
		   $this->show_sub_per_average_row($dtext['Partial_average'],$colcount,$testdef_array,$part_average,$part_average[0],$passpoint2,$mid,$lastsortval);
	}
    // Show group averages
	$this->show_sub_per_average_row($dtext['Group_average'],$colcount,$testdef_array,$average_array,$period_average,$passpoint2,$mid,"");
    echo("</table>");
  }
  
  private function show_sub_per_average_row($label,$colcount,$testdef_array,$average_array,$period_average,$passpoint2,$mid,$lastsortval)
  {
    $dtext = $_SESSION['dtext'];
	if($lastsortval == "")
      echo("<tr class=average><td colspan=". $colcount. ">". $label. "</td>");
	else
      echo("<tr class=average><td colspan=". ($colcount - 1). ">". $label. "</td><td>". $lastsortval. "</td>");
    //echo("<td>&nbsp</td>");
    // Add the Grades
	if(isset($testdef_array['tdid']))
	  foreach($testdef_array['tdid'] AS $tdid)
	  {
        echo("<td><center>");
        if(isset($average_array[$tdid]))
        { 
          $result = round($average_array[$tdid],2);
            // Colour depends on pass criteria
            if(isset($passpoint2[$mid]) && $passpoint2[$mid] > $result) echo("<font color=red>");
            else echo("<font color=blue>");
            echo(number_format($result,2,$dtext['dec_sep'],$dtext['mil_sep']));
            echo("</font>");
        }
        else
          echo("-");
        echo("</td>");
      }
    // Show the calculated period prognose/result
    if(isset($period_average))
    {
      echo("<td class=endgrade>");
      if(isset($passpoint2[$mid]) && $passpoint2[$mid] > $period_average)
        echo("<font color=red>");
      else
        echo("<font color=blue>");
      echo(number_format($period_average,2,$dtext['dec_sep'],$dtext['mil_sep']). "</font></td>");
    }
    else 
      echo("<td>&nbsp</td>");
    echo("</tr>");
  }
}
?>
