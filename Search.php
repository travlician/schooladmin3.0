<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2016 Aim4me N.V.   (http://www.aim4me.info)       |
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
require_once("report.php");
require_once("group.php");
require_once("studentsorter.php");
require_once("StudentClassBook.php");

class Search extends displayelement
{
  protected function add_contents()
  {
  }
  
  public function show_contents()
  {
    global $userlink;
		if(isset($_POST['firstname']))
			$this->search_student($_POST['firstname'],$_POST['lastname']);
		else if(isset($_POST['tfirstname']))
			$this->search_teacher($_POST['tfirstname'],$_POST['tlastname']);
		else if(isset($_POST['student']))
			$this->show_student($_POST['student']);
		else if(isset($_POST['teacher']))
			$this->show_teacher($_POST['teacher']);
		else
			$this->show_searchdialog();
  }
  
  private function show_searchdialog()
  {
    $dtext = $_SESSION['dtext'];
		$I = new teacher();
		$I->load_current();
		$group = new group();
		$group->load_current();
	
    echo("<font size=+2>" . $dtext['Search_title'] ."</font><p>");

		echo("<FORM METHOD=POST ACTION='". $_SERVER['REQUEST_URI']. "'>");
		echo("<H2>". $dtext['studsearch_title']. "</H2><label>". $dtext['Firstname']. ": </label><input type=text size=30 name=firstname id=firstname>
					<label>". $dtext['Lastname']. ": </label><input type=text size=30 name=lastname id=lastname>
			<input type=submit value='". $dtext['Find_it']. "'>");
		echo("</FORM><BR><BR>");

		echo("<FORM METHOD=POST ACTION='". $_SERVER['REQUEST_URI']. "'>");
		echo("<H2>". $dtext['teachsearch_title']. "</H2><label>". $dtext['Firstname']. ": </label><input type=text size=30 name=tfirstname id=tfirstname>
					<label>". $dtext['Lastname']. ": </label><input type=text size=30 name=tlastname id=tlastname>
			<input type=submit value='". $dtext['Find_it']. "'>");
		echo("</FORM><BR><BR>");

  }
  
  private function search_student($fname, $lname)
  {
    $dtext = $_SESSION['dtext'];
    $slist = inputclassbase::load_query("SELECT sid,firstname,lastname,GROUP_CONCAT(groupname) AS grps FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) WHERE active=1 AND firstname LIKE '%". $fname. "%' AND lastname LIKE '%". $lname. "%' GROUP BY sid ORDER BY lastname,firstname");
		if(!isset($slist['sid']))
		{ // No matching student found
			echo("<H2><font color=red>". $dtext['no_rep_4stu']. "</font></H2>");
			$this->show_searchdialog();
		}
		else if(count($slist['sid']) == 1)
		{ // only 1 student found, go to this one.
			$this->show_student($slist['sid'][0]);
		}
		else
		{ // Create a list of the students
			echo("<table border=1 cellpadding=0>");
			echo("<tr><th>". $dtext['Firstname']. "</th><th>". $dtext['Lastname']. "</th><th>". $dtext['in_grp']. "</th>");	
			echo("<th><center> </th>");  
			echo("</tr>");
			$altrow = false;
			foreach($slist['sid'] AS $six => $sid)
			{  
				echo("<tr". ($altrow ? " class=altbg" : ""). "><form method=post action=". $_SERVER['REQUEST_URI']. " name=studcalview". $sid. " id=studcalview". $sid. ">");
				echo("<TD>". $slist['firstname'][$six]. "</TD>");
				echo("<TD>". $slist['lastname'][$six]. "</TD>");
				echo("<TD>". $slist['grps'][$six]. "</TD>");
				// Add the Goto button
				echo("<td><center><input type=hidden name=student value=" . $sid ."><img src=PNG/search.png onClick='document.studcalview". $sid. ".submit();'></td></form></tr>");
				$altrow = !$altrow;
      }
      echo("</table>");
    }
  }

  private function search_teacher($fname, $lname)
  {
    $dtext = $_SESSION['dtext'];
    $slist = inputclassbase::load_query("SELECT tid,firstname,lastname,GROUP_CONCAT(groupname) AS grps FROM teacher LEFT JOIN class USING(tid) LEFT JOIN sgroup USING(gid) WHERE is_gone='N' AND firstname LIKE '%". $fname. "%' AND lastname LIKE '%". $lname. "%' GROUP BY tid ORDER BY lastname,firstname");
		if(!isset($slist['tid']))
		{ // No matching teacher found
			echo("<H2><font color=red>". $dtext['teacher_not_found']. "</font></H2>");
			$this->show_searchdialog();
		}
		else if(count($slist['tid']) == 1)
		{ // only 1 teacher found, go to this one.
			$this->show_teacher($slist['tid'][0]);
		}
		else
		{ // Create a list of the teachers
			echo("<table border=1 cellpadding=0>");
			echo("<tr><th>". $dtext['Firstname']. "</th><th>". $dtext['Lastname']. "</th><th>". $dtext['in_grp']. "</th>");	
			echo("<th><center> </th>");  
			echo("</tr>");
			$altrow = false;
			foreach($slist['tid'] AS $tix => $tid)
			{  
				echo("<tr". ($altrow ? " class=altbg" : ""). "><form method=post action=". $_SERVER['REQUEST_URI']. " name=teachcalview". $tid. " id=tcalview". $tid. ">");
				echo("<TD>". $slist['firstname'][$tix]. "</TD>");
				echo("<TD>". $slist['lastname'][$tix]. "</TD>");
				echo("<TD>". $slist['grps'][$tix]. "</TD>");
				// Add the Goto button
				echo("<td><center><input type=hidden name=teacher value=" . $tid ."><img src=PNG/search.png onClick='document.teachcalview". $tid. ".submit();'></td></form></tr>");
				$altrow = !$altrow;
      }
      echo("</table>");
    }
  }
	
	private function show_student($sid)
	{
		$stud = new student($sid);
		echo($_SESSION['dtext']['found_student']. " :<BR>". $stud->get_firstname(). " ". $stud->get_lastname(). "<BR>". $_SESSION['dtext']['group']. " : ". $stud->get_student_detail("*sgroup.groupname"));
		$cal = new StudentClassBook(NULL,NULL,$sid, true);
		$cal->add_contents();
		$cal->show_contents();
	}

	private function show_teacher($tid)
	{
		$teach = new teacher($tid);
		echo($_SESSION['dtext']['found_teacher']. " :<BR>". $teach->get_username(). "<BR>". $_SESSION['dtext']['group']. " : ". $teach->get_teacher_detail("*subject.fullname"));
 		$cal = new TeacherAgenda(NULL,NULL,$tid);
		$cal->add_contents();
		$cal->show_contents();		
	}
	

}

class TeacherAgenda extends displayelement
{
  protected $startDate;
  protected $calendarlayers;
  protected $abselem;
	protected $tid;
  
	public function __construct($divid = NULL, $style=NULL, $tid)
	{
		$this->tid = $tid;
		parent::__construct($divid,$style);
	}

  public function set_abselem($abselem)
  {
    $this->abselem = $abselem;
  }
  protected function add_contents()
  {
 	  $now = mktime() + $_SESSION['ClientTimeOffset'];
	  $this->startDate = mktime(0,0,0,Date("n",$now),Date("j",$now),Date("Y",$now));
		// Create the layers
		$dtext = $_SESSION['dtext'];
		$this->calendarlayers = array('dd'=>new DisabledDays($dtext['DisabledDays'],true,$this->startDate,$this->startDate),
										'od'=>new OffDays($dtext['OffDays'],true,$this->startDate,$this->startDate),
										'tt'=>new TimetableTimes($dtext['TimetableTimes'],true,$this->startDate,$this->startDate),
										'ta'=>new TimetableActivities($dtext['TimetableActivities'],true,$this->startDate,$this->startDate),
										'ca'=>new CalendarAnnouncements($dtext['CalendarAnnouncements'],true,$this->startDate,$this->startDate)
										);
  }

  public function show_contents()
  {
		$dtext = $_SESSION['dtext'];
			echo('<LINK href="ClassBook.css" rel="stylesheet" type="text/css">');
		echo("<p>");
		$this->show_agenda();
		echo("</p>");
  }
  
  private function show_agenda()
  { 
		$filtteacher = $this->tid;
		//echo((isset($filtteacher) ? "Filter on teacher ". $filtteacher : "Filter on group ". $filtgrp));
		// Now return a string with the name of the timetable activities and the resulting timetable (times and activities)
		$acttable = $this->calendarlayers['ta']->get_dateinfo($this->startDate);
		// Get the lesson times table name
		$timestablename = $this->calendarlayers['tt']->get_dateinfo($this->startDate);
		if(isset($acttable) && isset($timestablename))
		{
			// Get the data to be viewed
			//echo("filtgrp=". $filtgrp. ",filtteacher=". $filtteacher);
			//echo($acttable);
			$acts = TimetableActivity::list_activities($acttable,NULL,isset($filtgrp) ? $filtgrp : NULL,NULL,isset($filtteacher) ? $filtteacher : NULL);
			if(!isset($acts))
			{ // No activities set. Now if this is an administrator we can show the current group instead. (added jan 7 2014)
			$curgidqr = inputclassbase::load_query("SELECT gid FROM sgroup WHERE active=1 AND groupname='". $_SESSION['CurrentGroup']. "'");
			if(isset($curgidqr['gid']))
				$filtgrp = $curgidqr['gid'][0];
				$acts = TimetableActivity::list_activities($acttable,NULL,isset($filtgrp) ? $filtgrp : NULL,NULL,NULL);	    
			}
			//$acts = TimetableActivity::list_activities($acttable);
			// Get the max number of timeslots
			$mts = inputclassbase::load_query("SELECT MAX(timeslot) AS mts FROM timetabletimes WHERE tablename='". $timestablename. "'");
			$tttdata = inputclassbase::load_query("SELECT *,ADDTIME(starttime,duration) AS endtime FROM timetabletimes WHERE tablename='". $timestablename. "' ORDER BY timeslot");
			foreach($tttdata['timeslot'] AS $tttix => $ts)
			{
			$tttdat['starttime'][$ts] = $tttdata['starttime'][$tttix];
			$tttdat['endtime'][$ts] = $tttdata['endtime'][$tttix];
			}
			if(isset($mts['mts']) && isset($acts))
			{
				$mts = $mts['mts'][0];
				echo("<table class=timetableactivitiesview><TR><TH>". $_SESSION['dtext']['TimetableTimesTimeslot']. "</th>
													<TH>". $_SESSION['dtext']['Time']. "</th>
													<TH>". $_SESSION['dtext']['group']. "</th>
													<TH>". $_SESSION['dtext']['Subject']. "</th>
													<TH>". $_SESSION['dtext']['Location']. "</th>
													</tr>");
				$clientnow = mktime() + $_SESSION['ClientTimeOffset'];
				foreach($acts AS $actobj)
				{
					$actarr['cid'][$actobj->get_timeslot()] = $actobj->get_cid();
					$actarr['loc'][$actobj->get_timeslot()] = $actobj->get_location();
					$actarr['cidcid'][$actobj->get_timeslot()] = $actobj->get_cid_cid();
				}
				$doubletime = false;
				$negix = -1002;
				foreach($tttdat['starttime'] AS $ts => $starttime)
				{  // Now add the data for each timeslot
					if(isset($endtime) && $tttdat['starttime'][$ts] > $endtime) // Add a break
					echo("<TR><TD colspan=8>". $_SESSION['dtext']['Break']. "</td></tr>");
					// See if this is the current timeslot
					$curts = false;
					if(isset($_POST['SelectTimeslot']) && $ts == $_POST['SelectTimeslot'])
						$curts = true;
					else if(!isset($_POST['SelectTimeslot']) && date("Ymd", $this->startDate) == date("Ymd",$clientnow) &&
									date("G:i:s",$clientnow) >= $starttime && date("G:i:s",$clientnow) < $tttdat['endtime'][$ts])
					{
						$curts = true;
					}
					echo("<TR". ($curts ? " class=selectedrow" : ""). "><TD>". $ts. "</td><TD>". substr($starttime,0,-3). "-". substr($tttdat['endtime'][$ts],0,-3));
					if(!$doubletime)
					{
					// Decide if we need to display doubletime or not.
					if(isset($actarr['cid'][$ts]) && isset($actarr['cid'][$ts+1]) && 
						 isset($actarr['loc'][$ts]) && isset($actarr['loc'][$ts+1]) && 
						 $actarr['cid'][$ts] == $actarr['cid'][$ts+1] &&
						 $actarr['loc'][$ts] == $actarr['loc'][$ts+1] &&
						 isset($tttdat['endtime'][$ts]) && isset($tttdat['starttime'][$ts+1]) &&
						 $tttdat['endtime'][$ts] == $tttdat['starttime'][$ts+1]
						 )
						 $doubletime = true;
					// show class and location
					if(isset($actarr['cid'][$ts]))
					{
						$cidexpl = explode("&#45;",$actarr['cid'][$ts]);
						echo("</td><TD". ($doubletime ? " rowspan=2" : ""). ">
															". $cidexpl[0]. "</td>
														 <TD". ($doubletime ? " rowspan=2" : ""). ">". $cidexpl[1]. "</td>
										 <TD". ($doubletime ? " rowspan=2" : ""). ">". $actarr['loc'][$ts]);
					}
					else
						echo("</td><TD colspan=6>-</td>");
					}
					else
					$doubletime = false;
					echo("</tr>");
					$endtime = $tttdat['endtime'][$ts];
				}	  
				echo("</table>");
			}
		}
  }
  
}
?>
