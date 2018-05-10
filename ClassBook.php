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
require_once("displayelements/extendableelement.php");
require_once("CalendarLayer.php");

class ClassBook extends extendableelement
{
  protected $abselem;
  protected function add_contents()
  {
    parent::add_contents();
		if(isset($_GET['ClassBookDate']))
    {
			$newdate = $_GET['ClassBookDate'];
      $_SESSION['CurrentClassBookDate'] = mktime(0,0,0,substr($newdate,3,2),substr($newdate,0,2),substr($newdate,6,4));

		}
		if(isset($_POST['SelectCid']))
			$this->add_element(new CidAgenda);
		else
		{
			$dayag = new DayAgenda(NULL,"display: inline-block;");
				$this->add_element($dayag);
			//if(isset($_POST['SelectTimeslot']) || !isset($_SESSION['CurrentClassBookDate']) || date('Ymd',$_SESSION['CurrentClassBookDate']) == date('Ymd'))
			{
				$abselem = new Absenceman(NULL,"display: inline-block; margin-left: 20px;");
				if(isset($_SESSION['CurrentClassBookDate']))
					$abselem->set_cid_date($_SESSION['CurrentClassBookDate']);
				$dayag->set_abselem($abselem);
				$this->add_element($abselem);
			}
		}
  }
}

require_once("teacher.php");
require_once("Absenceman.php");

class DayAgenda extends displayelement
{
  protected $startDate;
  protected $calendarlayers;
  protected $abselem;
  
  public function set_abselem($abselem)
  {
    $this->abselem = $abselem;
  }
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
	$_SESSION['CurrentClassBookDate'] = $this->startDate;
	
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
    echo("<p><center>" . $dtext['tpage_classbook'] . "</center></p>");
	// Show day navigation
	$datefld = new inputclass_datefield("ClassBookDate",Date("d-m-Y",$this->startDate),NULL,NULL,NULL,NULL,NULL,NULL,"datahandler.php");
	// See which days are weekend days
	$wkdays = inputclassbase::load_query("SELECT DISTINCT DAYOFWEEK(caldate)-1 AS wd FROM calendaritem WHERE calclass='TimetableActivities'");
	if(isset($wkdays['wd']))
	{
      $enadays = implode(",",$wkdays['wd']);
	  $datefld->set_parameter("dc-alloweddays",$enadays);
	}
	$datefld->set_parameter("dc-startdate",date("mdY",$this->startDate));
	if($this->calendarlayers['od']->get_dateinfo($this->startDate) != "")
	  echo("<P class=offdayannounce>". $this->calendarlayers['od']->get_dateinfo($this->startDate). "</p>");
	echo("<P>");
	if($this->calendarlayers['ca']->get_dateinfo($this->startDate) != "")
	  echo("<P class=calendarannounce>". $this->calendarlayers['ca']->get_dateinfo($this->startDate). "</p>");
	echo("<P>");
	$datefld->echo_html();
	echo("</p><p>");
	$this->show_agenda();
	echo("</p>");
  }
  
  private function show_agenda()
  { 
    global $teachercode;
	if(!isset($teachercode))
	  $filtteacher = $_SESSION['uid'];
	else
	{ // Now this user might be lacking a teachercode! in that case the current group will be used to filter
	  $tdata = inputclassbase::load_query("SELECT data FROM `". $teachercode. "` WHERE tid=". $_SESSION['uid']);
	  if(!isset($tdata['data']))
	  { // So current teacher does not have a teachercode, so we get the current group as filter
		$curgidqr = inputclassbase::load_query("SELECT gid FROM sgroup WHERE active=1 AND groupname='". $_SESSION['CurrentGroup']. "'");
		if(isset($curgidqr['gid']))
		  $filtgrp = $curgidqr['gid'][0];
	  }
	  else
	    $filtteacher = $_SESSION['uid'];
	}
	// echo((isset($filtteacher) ? "Filter on teacher ". $filtteacher : "Filter on group ". $filtgrp));
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
											<TH>". $_SESSION['dtext']['Assignments']. "</th>
											<TH>". $_SESSION['dtext']['Description']. "</th>
											<TH>". $_SESSION['dtext']['Realised']. "</th>
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
				// Since current timeslot has been created automatically, set refresh for the next if available
				if(isset($tttdat['starttime'][$ts+1]))
				{
					$reloadtime = $tttdat['starttime'][$ts+1];
					$reloaddelay = (mktime(substr($reloadtime,0,2),substr($reloadtime,3,2),substr($reloadtime,6,2),
																 date('n',$clientnow),date('j',$clientnow),date("Y",$clientnow)) - $clientnow) * 1000;
					$reloadscript = ("<SCRIPT> setTimeout('location.reload(true)',". $reloaddelay. "); </SCRIPT>");
				}
		  }
		  if($curts)
		  { // Inform absence management of date and cid
		    if(isset($this->abselem) && isset($actarr['cidcid'][$ts]))
			  $this->abselem->set_cid_date($this->startDate,$actarr['cidcid'][$ts]);
		  }
		  echo("<TR". ($curts ? " class=selectedrow" : ""). "><TD><a href=# onClick='SelectTS(". $ts. ");'>". $ts. "</a></td><TD>". substr($starttime,0,-3). "-". substr($tttdat['endtime'][$ts],0,-3));
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
			                    <a href=# onClick='SelectCid(". $actarr['cidcid'][$ts]. ");'>". $cidexpl[0]. "</a></td>
			                   <TD". ($doubletime ? " rowspan=2" : ""). ">". $cidexpl[1]. "</td>
							   <TD". ($doubletime ? " rowspan=2" : ""). ">". $actarr['loc'][$ts]);
			  // Get the corresponding test definition and show its info (or allow edit realised)
			  $testdef = testdef::get_test($actarr['cidcid'][$ts],$this->startDate);
			  if(isset($testdef))
			  {
				  echo("<TD". ($doubletime ? " rowspan=2" : ""). ">");
				  echo($testdef->get_assign());
				  echo("</td><TD". ($doubletime ? " rowspan=2" : ""). ">");
				  echo($testdef->get_desc());
				  echo("</td><TD". ($doubletime ? " rowspan=2" : ""). ">");
				  $testdef->edit_realised();
				  echo("</td>");
			  }
			  else
			  {
//				  $testdef = new testdef(0-date("nd",$this->startDate)); // Replaced 14 jan 14, multiple occurences on page so must be unique and negative
				  $testdef = new testdef($negix--);

				  echo("<TD". ($doubletime ? " rowspan=2" : ""). ">-");
				  //$testdef->edit_assign($this->cid,$adate);
				  echo("</td><TD". ($doubletime ? " rowspan=2" : ""). ">-");
				  //$testdef->edit_desc($this->cid,$adate);
				  echo("</td><TD". ($doubletime ? " rowspan=2" : ""). ">");
				  $testdef->edit_realised($actarr['cidcid'][$ts],$this->startDate);
				  echo("</td>");
			  }
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
	echo("<FORM NAME=timeslotselectform ID=timeslotselectform METHOD=POST ACTION='". $_SERVER['REQUEST_URI']. "'>");
	echo("<INPUT TYPE=HIDDEN NAME=SelectTimeslot ID=SelectTimeslot VALUE=0></FORM>");
	echo("<SCRIPT> function SelectTS(timeslot) { document.getElementById('SelectTimeslot').value=timeslot; document.getElementById('timeslotselectform').submit(); } </SCRIPT>");
	echo("<FORM NAME=cidselectform ID=cidselectform METHOD=POST ACTION='". $_SERVER['REQUEST_URI']. "'>");
	echo("<INPUT TYPE=HIDDEN NAME=SelectCid ID=SelectCidFld VALUE=0></FORM>");
	echo("<SCRIPT> function SelectCid(cid) { document.getElementById('SelectCidFld').value=cid; document.getElementById('cidselectform').submit(); } </SCRIPT>");
  }
  
}

require_once("testdef.php");
class CidAgenda extends displayelement
{
  protected $startDate;
  protected $calendarlayers;
  protected $futuredays;
  protected $cid,$gid,$tid,$mid;
  protected function add_contents()
  {
    global $maxCBfuturedays;
		if(!isset($maxCBfuturedays))
	  $maxCBfuturedays = 4;
	echo("Max days = ". $maxCBfuturedays. "<BR>");
    if(isset($_SESSION['CurrentClassBookDate']))
			$this->startDate = $_SESSION['CurrentClassBookDate'];
		else
		{
			$now = mktime() + $_SESSION['ClientTimeOffset'];
			$this->startDate = mktime(0,0,0,Date("n",$now),Date("j",$now),Date("Y",$now));
		}
		$_SESSION['CurrentClassBookDate'] = $this->startDate;
		$lastDate = mktime(0,0,0,Date("n",$this->startDate)+2,Date("j",$this->startDate),Date("Y",$this->startDate));
		$this->cid = $_POST['SelectCid'];
		// Create the layers
		$dtext = $_SESSION['dtext'];
		$this->calendarlayers = array('tt'=>new TimetableTimes($dtext['TimetableTimes'],true,$this->startDate,$lastDate),
										'ta'=>new TimetableActivities($dtext['TimetableActivities'],true,$this->startDate,$lastDate),
										'ca'=>new CalendarAnnouncements($dtext['CalendarAnnouncements'],true,$this->startDate,$lastDate)
										);
		// Extract data from the selected cid
		$ciddata = inputclassbase::load_query("SELECT * FROM `class` WHERE cid=". $_POST['SelectCid']);
		$this->gid = $ciddata['gid'][0];
		$this->tid = $ciddata['tid'][0];
		$this->mid = $ciddata['mid'][0];
		// Decide which days to show, we start 1 day after the start day and take every day that has an activit with the cid posted
		$curdate = $this->startDate;
		$futdays = 0;
		while($curdate <= $lastDate && $futdays < $maxCBfuturedays)
		{
			$curdate = mktime(0,0,0,date("n",$curdate),date("j",$curdate)+1,date("Y",$curdate));
			//echo("Checking date ". date("d-m-Y",$curdate). ",");
			$tttname = $this->calendarlayers['tt']->get_dateinfo($curdate);
			$ttaname = $this->calendarlayers['ta']->get_dateinfo($curdate);
			if(isset($tttname) && isset($ttaname))
			{
				$iscidday = false;
				// Get the number of timeslots for the current date
				$mts = inputclassbase::load_query("SELECT MAX(timeslot) AS mts FROM timetabletimes WHERE tablename='". $tttname. "'");
				if(isset($mts['mts'][0]))
				{
					$mts = $mts['mts'][0];
						$acts = TimetableActivity::list_activities($ttaname,NULL,$this->gid,$this->mid,$this->tid);
					if(isset($acts))
					foreach($acts AS $actobj)
					{
						if($actobj->get_timeslot() <= $mts)
						{
							$iscidday = true;
						}
					}
				}
				if($iscidday)
					$this->futuredays[$futdays++] = $curdate;
			}
		}
  }

  public function show_contents()
  {
	$dtext = $_SESSION['dtext'];
    echo('<LINK href="ClassBook.css" rel="stylesheet" type="text/css">');
    echo("<p><center>" . $dtext['tpage_classbook'] . "</center></p>");
	if(isset($this->futuredays))
	{
	  echo("<DIV class=CBCalendarContainer>");
	  foreach($this->futuredays AS $fdate)
	  {
		// First fid out which group
		$grpqr = inputclassbase::load_query("SELECT groupname FROM sgroup WHERE active=1 AND gid=". $this->gid);
		echo("<SPAN class=CBCalendar>");
		// Show announcement
	    echo("<P class=calendarannounce>". $this->calendarlayers['ca']->get_dateinfo($fdate). "</p>");
		echo("<P class=datelabel>". $_SESSION['dtext']["dayabbrev_". date("w",$fdate)]. " ". date('j',$fdate). " ". 
		       $_SESSION['dtext']["month_". date('n',$fdate)]. " ". date('Y',$fdate). " (". $grpqr['groupname'][0]. ")</p>");
		echo("<p>");
		$this->show_agenda($fdate);
		echo("</p></span>");
	  }
	  echo("</div>");
	}
  }
  
  private function show_agenda($adate)
  { 
		// First set a flag if administrator
		$me = new teacher();
		$me->load_current();
		$isadmin = $me->has_role('admin');
		$acttable = $this->calendarlayers['ta']->get_dateinfo($adate);
		// Get the lesson times table name
		$timestablename = $this->calendarlayers['tt']->get_dateinfo($adate);
		if(isset($acttable) && isset($timestablename))
		{
			// Get the data to be viewed
			$acts = TimetableActivity::list_activities($acttable,NULL,$this->gid);
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
													<TH>". $_SESSION['dtext']['Subject']. "</th>
													<TH>". $_SESSION['dtext']['Assignments']. "</th>
													<TH>". $_SESSION['dtext']['Description']. "</th>
													<TH>". $_SESSION['dtext']['Type']. "</th>
													</tr>");
				foreach($acts AS $actobj)
				{
					$actarr['cid'][$actobj->get_timeslot()] = $actobj->get_cid();
					$actarr['loc'][$actobj->get_timeslot()] = $actobj->get_location();
					$actarr['cidcid'][$actobj->get_timeslot()] = $actobj->get_cid_cid();
				}
				$doubletime = false;
				foreach($tttdat['starttime'] AS $ts => $starttime)
				{  // Now add the data for each timeslot
					if(isset($endtime) && $tttdat['starttime'][$ts] > $endtime) // Add a break
					echo("<TR><TD colspan=5>". $_SESSION['dtext']['Break']. "</td></tr>");
					echo("<TR><TD>". $ts);
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
							echo("</td><TD". ($doubletime ? " rowspan=2" : ""). ">". $cidexpl[1]. "</td>");
							$testdef = testdef::get_test($actarr['cidcid'][$ts],$adate);
							if(isset($testdef))
							{
								if($actarr['cidcid'][$ts] == $this->cid)
								{ // Owned testdef, so allow editing
									echo("<TD". ($doubletime ? " rowspan=2" : ""). ">");
									$testdef->edit_assign();
									echo("</td><TD". ($doubletime ? " rowspan=2" : ""). ">");
									$testdef->edit_desc(NULL,NULL,$isadmin);
									echo("</td><TD". ($doubletime ? " rowspan=2" : ""). ">");
									if(!$testdef->get_admindefined() || $isadmin)
										$testdef->edit_type();
									else
										echo($testdef->get_type());
									echo("</td>");
								}
								else
								{ // Non-owned testdef so just display
									echo("<TD". ($doubletime ? " rowspan=2" : ""). ">". $testdef->get_assign().
											 "</td><TD". ($doubletime ? " rowspan=2" : ""). ">". $testdef->get_desc().
											 "</td><TD". ($doubletime ? " rowspan=2" : ""). ">". $testdef->get_type(). "</td>");
								}
							}
							else
							{
								if($actarr['cidcid'][$ts] == $this->cid)
								{ // Owned undefined testdef, so allow creation
									$testdef = new testdef(0-date("nd",$adate));
									echo("<TD". ($doubletime ? " rowspan=2" : ""). ">");
									$testdef->edit_assign($this->cid,$adate);
									echo("</td><TD". ($doubletime ? " rowspan=2" : ""). ">");
									$testdef->edit_desc($this->cid,$adate,$isadmin);
									echo("</td><TD". ($doubletime ? " rowspan=2" : ""). ">");
									$testdef->edit_type($this->cid,$adate);
									echo("</td>");
								}
								else
								{ // No testdef and no way to defined one
									echo("<TD". ($doubletime ? " rowspan=2" : ""). " colspan=3>&nbsp;</td>");
								}
							}
						}
						else
							echo("</td><TD colspan=4>-</td>");
					}
					else
					$doubletime = false;
					echo("</tr>");
					$endtime = $tttdat['endtime'][$ts];
				}
			}
			echo("</table>");
		}
  }
}

?>
