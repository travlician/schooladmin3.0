<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014 Aim4me N.V.   (http://www.aim4me.info)       |
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
require_once("displayelements/extendableelement.php");
require_once("CalendarLayer.php");
//require_once("teacher.php");
require_once("Absenceman.php");
require_once("testdef.php");
require_once("student.php");

class StudentClassBook extends displayelement
{
  protected $startDate,$endDate;
  protected $calendarlayers;
  protected $abselem;
	protected $sid, $oneday;
	
	public function __construct($divid = NULL, $style = NULL, $sid = NULL, $oneday = false)
	{
		if(isset($sid))
			$this->sid = $sid;
		$this->oneday = $oneday;
		parent::__construct($divid,$style);
	}
  
/*  public function set_abselem($abselem)
  {
    $this->abselem = $abselem;
  } */
  protected function add_contents()
  {
    // Clear flilters if another group is selected
    if(isset($_SESSION['CurrentClassBookDate']))
	  $this->startDate = $_SESSION['CurrentClassBookDate'];
	else
	{
	  $now = mktime() + $_SESSION['ClientTimeOffset'];
	  $this->startDate = mktime(0,0,0,Date("n",$now),Date("j",$now),Date("Y",$now));
	}
	if(isset($_GET['week']) && $_GET['week'] == 'prev')
	{
	  $this->startDate = mktime(0,0,0,Date("n",$this->startDate),Date("j",$this->startDate)-7,Date("Y",$this->startDate));
	}
	else if(isset($_GET['week']) && $_GET['week'] == 'next')
	{
	  $this->startDate = mktime(0,0,0,Date("n",$this->startDate),Date("j",$this->startDate)+7,Date("Y",$this->startDate));
	}
	$_SESSION['CurrentClassBookDate'] = $this->startDate;
	$this->endDate = mktime(0,0,0,Date("n",$this->startDate),Date("j",$this->startDate)+6,Date("Y",$this->startDate));
	
	// Create the layers
	$dtext = $_SESSION['dtext'];
	$this->calendarlayers = array('dd'=>new DisabledDays($dtext['DisabledDays'],true,$this->startDate,$this->endDate),
								  'od'=>new OffDays($dtext['OffDays'],true,$this->startDate,$this->endDate),
								  'tt'=>new TimetableTimes($dtext['TimetableTimes'],true,$this->startDate,$this->endDate),
								  'ta'=>new TimetableActivities($dtext['TimetableActivities'],true,$this->startDate,$this->endDate),
								  'ca'=>new CalendarAnnouncements($dtext['CalendarAnnouncements'],true,$this->startDate,$this->endDate)
								  );
  }

  public function show_contents()
  {
	$dtext = $_SESSION['dtext'];
    echo('<LINK href="ClassBook.css" rel="stylesheet" type="text/css">');
    //echo("<p><center>" . $dtext['tpage_classbook'] . "</center></p>");
	// Show week navigation
	if(!$this->oneday)
	{
		echo("<a href='". $_SERVER['PHP_SELF']. "?week=prev'><img src='PNG/arrow_back.png' BORDER=0></a>". $dtext['Week']);
		echo("<a href='". $_SERVER['PHP_SELF']. "?week=next'><img src='PNG/arrow_next.png' BORDER=0></a><BR>");
	}
	else
		echo("<BR>");
	// Show the agenda for one week (7 days) or just one day
	for($do=0; $do <= ($this->oneday ? 0 : 7); $do++)
	{
	  echo("<DIV style='display: inline-block; margin: 20px;'>");
	  $this->show_agenda(mktime(0,0,0,date("n",$this->startDate),date("j",$this->startDate) + $do,date("Y",$this->startDate)));
	  echo("</DIV>");
	}
  }
  
  private function show_agenda($agendaDate)
  { 
	// Now return a string with the name of the timetable activities and the resulting timetable (times and activities)
	$acttable = $this->calendarlayers['ta']->get_dateinfo($agendaDate);
	// Get the lesson times table name
	$timestablename = $this->calendarlayers['tt']->get_dateinfo($agendaDate);
	if($this->calendarlayers['od']->get_dateinfo($agendaDate) != "")
	{ // It's a day off, show date and description!
	  echo("<B>". $_SESSION['dtext']["dayabbrev_". date("N",$agendaDate)]. " ". date("d-m-Y",$agendaDate). "</b>");
	  echo("<P class=offdayannounce>". $this->calendarlayers['od']->get_dateinfo($agendaDate). "</p>");
	}
	if(isset($acttable) && isset($timestablename) && $this->calendarlayers['od']->get_dateinfo($agendaDate) == "")
	{
	  // Get the data to be viewed
	  // We are going to pass an array of groups to the list_activities function so we must create it first
		if(!isset($this->sid))
			$me = new student($_SESSION['uid']);
		else
			$me = new student($this->sid);
	  $mygrps = $me->get_groups();
	  foreach($mygrps AS $agid => $agrp)
	  {
	    $mygids[$agid] = $agrp->get_id();
	  }
	  $acts = TimetableActivity::list_activities($acttable,NULL,$mygids,NULL,NULL);
	  // We are going to show absence, so get the absence records for this student for this day
	  $absrecs = absence::list_student($me,$agendaDate);
	  // Get the max number of timeslots
	  $mts = inputclassbase::load_query("SELECT MAX(timeslot) AS mts FROM timetabletimes WHERE tablename='". $timestablename. "'");
	  $tttdata = inputclassbase::load_query("SELECT *,ADDTIME(starttime,duration) AS endtime FROM timetabletimes WHERE tablename='". $timestablename. "' ORDER BY timeslot");
	  foreach($tttdata['timeslot'] AS $tttix => $ts)
	  {
			$tttdat['starttime'][$ts] = $tttdata['starttime'][$tttix];
			$tttdat['endtime'][$ts] = $tttdata['endtime'][$tttix];
			$tttdat['timeslot'][$ts] = $tttdata['timeslot'][$tttix];
	  }
	  if(isset($mts['mts']) && isset($acts))
	  {
		$mts = $mts['mts'][0];
		// Show date
		echo("<B>". $_SESSION['dtext']["dayabbrev_". date("N",$agendaDate)]. " ". date("d-m-Y",$agendaDate). "</b>");
	    if($this->calendarlayers['ca']->get_dateinfo($agendaDate) != "")
	      echo("<P class=calendarannounce>". $this->calendarlayers['ca']->get_dateinfo($agendaDate). "</p>");
		echo("<table class=timetableactivitiesview><TR><TH>". $_SESSION['dtext']['TimetableTimesTimeslot']. "</th>
											<TH>". $_SESSION['dtext']['Time']. "</th>
											<TH>". $_SESSION['dtext']['Subject']. "</th>
											<TH>". $_SESSION['dtext']['Location']. "</th>
											<TH>". $_SESSION['dtext']['Assignments']. "</th>
											<TH>". $_SESSION['dtext']['Description']. "</th>
											<TH>". $_SESSION['dtext']['Testtype']. "</th>
											<TH>". $_SESSION['dtext']['My_absence']. "</th>
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
		  echo("<TR><TD>". $ts. "</a></td><TD>". substr($starttime,0,-3). "-". substr($tttdat['endtime'][$ts],0,-3));
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
			// show location
			if(isset($actarr['cid'][$ts]))
			{
			  $cidexpl = explode("&#45;",$actarr['cid'][$ts]);
			  echo("</td><TD". ($doubletime ? " rowspan=2" : ""). ">". $cidexpl[1]. "</td>
							   <TD". ($doubletime ? " rowspan=2" : ""). ">". $actarr['loc'][$ts]);
			  // Get the corresponding test definition and show its info 
			  $testdef = testdef::get_test($actarr['cidcid'][$ts],$agendaDate);
			  if(isset($testdef))
			  {
				  echo("<TD". ($doubletime ? " rowspan=2" : ""). ($testdef->get_type() != "" ? " class=highlight" : ""). ">");
				  echo($testdef->get_assign());
				  echo("</td><TD". ($doubletime ? " rowspan=2" : ""). ($testdef->get_type() != "" ? " class=highlight" : ""). ">");
				  echo($testdef->get_desc());
				  echo("</td><TD". ($doubletime ? " rowspan=2" : ""). ($testdef->get_type() != "" ? " class=highlight" : ""). ">");
				  echo($testdef->get_type());
				  echo("</td>");
			  }
			  else
			  {
				  echo("<TD". ($doubletime ? " rowspan=2" : ""). ">-");
				  echo("</td><TD". ($doubletime ? " rowspan=2" : ""). ">-");
				  echo("</td><TD". ($doubletime ? " rowspan=2" : ""). ">-");
				  echo("</td". ($doubletime ? " rowspan=2" : ""). ">");
			  }
			  // Show absence
			  echo("<td". ($doubletime ? " rowspan=2" : ""). ">");
			  if(isset($absrecs))
			  {
			    $frec = true;
			    foreach($absrecs AS $absrec)
				{
				  if(($absrec->get_timeslot() == "" && ($absrec->get_subject(true) == '' || $absrec->get_subject(true) == $cidexpl[1])) ||
						 ($absrec->get_timeslot() == $tttdat['timeslot'][$ts]))
				  {
				    if(!$frec)
							echo(",");
						echo($absrec->get_reason());
				    $frec = false;
				  }				  
				}
			  }
			  else
			    echo("&nbsp;");
			  echo("</td>");
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
	if(isset($reloadscript))
	  echo($reloadscript);
  }
  
}


?>
