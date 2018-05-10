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
require_once("inputlib/inputclasses.php");

abstract class CalendarLayer
{
  protected $layername;
  protected $enabled;
  protected $startdate,$enddate;
  public function __construct($layername,$enabled,$startdate,$enddate)
  {
    $this->layername = $layername;
	$this->enabled = $enabled;
	$this->startdate=$startdate;
	$this->enddate = $enddate;
  }
  abstract public function get_dateinfo($date);
  abstract public function edit_dateinfo($date);
  public function set_enabled($enable)
  {
    $this->enabled = $enable;
  }
  public function get_enabled()
  {
    return $this->enabled;
  }
  public function set_startdate($startdate)
  {
    $this->startdate = $startdate;
  }
  public function get_startdate()
  {
    return $this->startdate;
  }
  public function set_enddate($enddate)
  {
    $this->enddate = $enddate;
  }
  public function get_enddate()
  {
    return $this->enddate;
  }
  public function get_layername()
  {
    return $this->layername;
  }
  public function request_control()
  {
    return false;
  }
  public function show_control()
  {
  }
}

class DisabledDays extends CalendarLayer
{
  protected $datedata;
  public function __construct($layername,$enabled,$startdate,$enddate)
  {
    parent::__construct($layername,$enabled,$startdate,$enddate);
	// Preload the date info to avoid 
	$this->load_datedata();
  }

  public function get_dateinfo($date)
  {
    if(isset($this->datedata[date("Y-m-d",$date)]))
	  return(" ");
	else
	  return null;
  }
  public function edit_dateinfo($date)
  {
    global $currentuser;
	if(isset($currentuser) && !$currentuser->has_role("admin"))
	{
	  echo $this->get_dateinfo($date);
	  return;
	}
    // We show checkboxes for sunday till saturday, selecting or deselecting makes the handler deal with it.
	// Find the first sunday
	$curweekday = date("w", $date);
	$curday = mktime(0,0,0,date("n",$date),date("j",$date)-$curweekday+7,date("Y",$date));
	for($wd=0; $wd<7;$wd++)
	{
      $checkfield = new inputclass_checkbox("calweekendday_". $wd,$this->get_dateinfo($curday) != NULL,NULL,"caldata","calendaritem","'". date("Y-m-d",$curday). "'","caldate",NULL,"datahandler.php");
	  $curday = mktime(0,0,0,date("n",$curday),date("j",$curday)+1,date("Y",$curday));
	  echo("&nbsp;&nbsp;". $_SESSION['dtext']["dayabbrev_". $wd]. "");
      $checkfield->echo_html(); 
    }	  
  }
  
  protected function load_datedata()
  {
    $dateqr = inputclassbase::load_query("SELECT caldate,caldata FROM calendaritem WHERE calclass='". get_class($this). "' AND caldate >= '". date("Y-m-d",$this->startdate). "' AND caldate <= '". date("Y-m-d",$this->enddate). "'");
	if(isset($dateqr['caldate']))
	  foreach($dateqr['caldate'] AS $cix => $cdate)
	  {
	    $this->datedata[$cdate] = $dateqr['caldata'][$cix];
	  }
  }
}

class OffDays extends DisabledDays
{  
  public function get_dateinfo($date)
  {
    if(isset($this->datedata[date("Y-m-d",$date)]))
	  return($this->datedata[date("Y-m-d",$date)]);
	else
	  return null;
  }
  public function edit_dateinfo($date)
  {
    global $currentuser;
	if(isset($currentuser) && !$currentuser->has_role("admin"))
	{
	  echo $this->get_dateinfo($date);
	  return;
	}
    $calidqr = inputclassbase::load_query("SELECT calid FROM calendaritem WHERE caldate='". date("Y-m-d",$date). "' AND calclass='". get_class($this). "'");
	if(isset($calidqr['calid']))
	  $calid = $calidqr['calid'][0];
	else
	  $calid = -10;
    $offfld = new inputclass_ckeditor("caloffday_". date("Y-m-d",$date),40,NULL,"caldata","calendaritem",$calid,"calid",NULL,"datahandler.php");
	$offfld->set_extrafield("calclass",get_class($this));
	$offfld->set_extrafield("caldate",date("Y-m-d",$date));
	$offfld->echo_html();
  }
}

class CalendarAnnouncements extends OffDays
{  
  public function __construct($layername,$enabled,$startdate,$enddate)
  {
    global $userlink;
    parent::__construct($layername,$enabled,$startdate,$enddate);
	// Perform delete if needed
	if(isset($_GET['delcr']) &&isset($_GET['seqno']))
	{
	  mysql_query("DELETE FROM calendarremarks WHERE calid=". $_GET['delcr']. " AND seqno=". $_GET['seqno'], $userlink);
	}
  }
  public function get_dateinfo($date)
  {
    global $teachercode,$currentuser;
    $calidqr = inputclassbase::load_query("SELECT calid FROM calendaritem WHERE caldate='". date("Y-m-d",$date). "' AND calclass='". get_class($this). "'");
	if(isset($calidqr['calid']))
	{
	  $calid = $calidqr['calid'][0];
	  $calrmqry = "SELECT * FROM calendarremarks WHERE calid=". $calid;
	  if(isset($currentuser))
	  { // User is a teacher
	    if(!$currentuser->has_role("admin"))
		{
		  if($currentuser->has_role("counsel"))
	        $calrmqry .= " AND (raccess <> 'P' OR tid=". $currentuser->get_id(). ")";
		  else
	        $calrmqry .= " AND (raccess = 'A' OR raccess='T' OR tid=". $currentuser->get_id(). ")";
		}
	  }
	  else // User is a student or parent
	    $calrmqry .= " AND raccess='A'";
	  $calrmqry .= " ORDER BY seqno";
	  $calrmqr = inputclassbase::load_query($calrmqry);
	  if(isset($calrmqr['remarks']))
	  {
	    $retval = "";
	    foreach($calrmqr['remarks'] AS $rix => $remtxt)
		{
		  $retval .= $remtxt;
		  // Now if there is a teachercode and this is not our own, we show the teachercode
		  if(isset($teachercode) && (!isset($currentuser) || $calrmqr['tid'][$rix] != $currentuser->get_id()))
		  {
		    $owner = new teacher($calrmqr['tid'][$rix]);
			if($owner->get_teacher_detail($teachercode) != "")
			  $retval .= " (". $owner->get_teacher_detail($teachercode). ")";
		  }
		  $retval .= "<BR>";
		}
		return substr($retval,0,-4);
	  }
	  else
	    return null;
	}
	else
	  return null;
  }
  
  public function edit_dateinfo($date)
  {
    global $userlink, $currentuser;
	if(!isset($currentuser))
	  return; // Return since non-teachers can not edit remarks at all.
    $calidqr = inputclassbase::load_query("SELECT calid FROM calendaritem WHERE caldate='". date("Y-m-d",$date). "' AND calclass='". get_class($this). "'");
	if(isset($calidqr['calid']))
	  $calid = $calidqr['calid'][0];
	else
	{ // No record included yet, make one now and get the calid.
	  mysql_query("INSERT INTO calendaritem (caldate,calclass) VALUES('". date("Y-m-d",$date). "','". get_class($this). "')", $userlink);
	  $calid = mysql_insert_id($userlink);
	}
	// Get a list of existing entries 
	$calrmqry = "SELECT * FROM calendarremarks WHERE calid=". $calid. " AND tid=". $currentuser->get_id();
	$calrmqry .= " ORDER BY seqno";
	$calrmqr = inputclassbase::load_query($calrmqry);
	$accselqry = "SELECT 'T' AS id,'". $_SESSION['dtext']['allow_teach_short']. "' AS tekst UNION SELECT 'C','". $_SESSION['dtext']['allow_couns_short']. "' UNION SELECT 'A','". $_SESSION['dtext']['allow_all_short']. "' UNION SELECT 'P','". $_SESSION['dtext']['allow_none']. "'";
	$maxrms = 0;
	if(isset($calrmqr['seqno']))
	{ // Existing records present, allow editing these
	  foreach($calrmqr['seqno'] AS $rms)
	  {
		$accfld = new inputclass_listfield("calanracc_". $calid. "_". $rms,$accselqry,NULL,"raccess","calendarremarks",$calid,"calid",NULL,"datahandler.php");
		$accfld->set_extrakey("seqno", $rms);
		echo($_SESSION['dtext']['R_acc']. ": ");
		$accfld->echo_html();
		// Add a delete icon
		echo(" <a href='". $_SERVER['PHP_SELF']. "?Page=". $_GET['Page']. "&delcr=". $calid. "&seqno=". $rms. "'><img src='PNG/action_delete.png'></a>");
        $annfld = new inputclass_ckeditor("calannounce_". $calid. "_". $rms,40,NULL,"remarks","calendarremarks",$calid,"calid",NULL,"datahandler.php");
	    $annfld->set_extrafield("tid",$currentuser->get_id());
		$annfld->set_extrakey("seqno", $rms);
		$annfld->echo_html();
		echo("<BR>");
	  }
	}
	// Put a new entry
	// We need to know what seqno is available
	$maxrmsqr = inputclassbase::load_query("SELECT MAX(seqno) AS maxrms FROM calendarremarks WHERE calid=". $calid);
	if(isset($maxrmsqr['maxrms']))
	  $maxrms = $maxrmsqr['maxrms'][0];
	$maxrms++;
	$accfld = new inputclass_listfield("calanracc_". $calid. "_". $maxrms,$accselqry,NULL,"raccess","calendarremarks",$calid,"calid",NULL,"datahandler.php");
	$accfld->set_extrakey("seqno", $maxrms);
	echo($_SESSION['dtext']['R_acc']. ": ");
	$accfld->echo_html();
    $annfld = new inputclass_ckeditor("calannounce_". $calid. "_". $maxrms,40,NULL,"remarks","calendarremarks",$calid,"calid",NULL,"datahandler.php");
	$annfld->set_extrafield("tid",$currentuser->get_id());
    $annfld->set_extrakey("seqno", $maxrms);
	$annfld->echo_html();
  }
}

class TimetableTimes extends OffDays
{  
  public function edit_dateinfo($date)
  {
    global $currentuser;
	if(isset($currentuser) && !$currentuser->has_role("admin"))
	{
	  echo $this->get_dateinfo($date);
	  return;
	}
    $calidqr = inputclassbase::load_query("SELECT calid FROM calendaritem WHERE caldate='". date("Y-m-d",$date). "' AND calclass='". get_class($this). "'");
	if(isset($calidqr['calid']))
	  $calid = $calidqr['calid'][0];
	else
	  $calid = -20;
    $offfld = new inputclass_listfield("caltimetabletimes_". date("Y-m-d",$date),"SELECT '' AS id,'' AS tekst UNION SELECT DISTINCT tablename, tablename FROM timetabletimes" ,NULL,"caldata","calendaritem",$calid,"calid",NULL,"datahandler.php");
	$offfld->set_extrafield("calclass",get_class($this));
	$offfld->set_extrafield("caldate",date("Y-m-d",$date));
	$offfld->echo_html();
	echo("<FORM ACTION='". $_SERVER['REQUEST_URI']. "' METHOD=POST><INPUT TYPE=hidden NAME=edittttdate VALUE='". date("Y-m-d",$date). "'><INPUT TYPE=SUBMIT VALUE='". $_SESSION['dtext']['EditTimetableTimes']. "'></FORM>");
	echo("<FORM ACTION='". $_SERVER['REQUEST_URI']. "' METHOD=POST><INPUT TYPE=hidden NAME=newtttdate VALUE='". date("Y-m-d",$date). "'><INPUT TYPE=SUBMIT VALUE='". $_SESSION['dtext']['NewTimetableTimes']. "'></FORM>");
	// Include dialogue items for assignment of timetable times to multiple dates.
	$perioddates = inputclassbase::load_query("SELECT MIN(startdate) AS sdat, MAX(enddate) AS edat FROM period");
	if(isset($perioddates['sdat']))
	{
	  $sdat = $perioddates['sdat'][0];
	  $edat = $perioddates['edat'][0];
	}
	else
	{
	  $sdat = date("Y-m-d");
	  $edat = date("Y-m-d");
	}
    echo("<FORM ACTION='". $_SERVER['REQUEST_URI']. "' METHOD=POST>
	      <INPUT TYPE=hidden NAME=copytimetables VALUE='". date("Y-m-d",$date). "'>");
	echo("<INPUT TYPE=SUBMIT VALUE='". $_SESSION['dtext']['TimetableTimesPlacement']. "'> ". $_SESSION['dtext']['DateFrom']. " ");
	$sdfield = new inputclass_datefield("sdat",inputclassbase::mysqldate2nl($sdat));
	$sdfield->set_parameter("dc-startdate",date("mdY",mktime(0,0,0,substr($sdat,5,2),substr($sdat,8,2),substr($sdat,0,4))));
	$sdfield->echo_html();
	echo("<SCRIPT> function dsfuncsdat() { } </SCRIPT>"); // This avoids data being sent by Ajax functions
	echo(" ". $_SESSION['dtext']['DateUntil']. " ");
	$edfield = new inputclass_datefield("edat",inputclassbase::mysqldate2nl($edat));
	$edfield->set_parameter("dc-startdate",date("mdY",mktime(0,0,0,substr($edat,5,2),substr($edat,8,2),substr($edat,0,4))));
	$edfield->echo_html();
	echo("<SCRIPT> function dsfuncedat() { } </SCRIPT>"); // This avoids data being sent by Ajax functions
	echo(" </FORM>");
 }
  
  public function request_control()
  {
    if(isset($_POST['edittttdate']) || isset($_POST['newtttdate']))
	  return true;
  }
  
  protected function load_datedata()
  {
    global $userlink;
    if(isset($_POST['copytimetables']))
	{ // Copy timetable times requested
	  $tnamerq = inputclassbase::load_query("SELECT caldata FROM calendaritem WHERE calclass='". get_class($this). "' AND caldate='". $_POST['copytimetables']. "'");
	  if(isset($tnamerq['caldata']))
	  {
	    $tname = $tnamerq['caldata'][0];
		$curdat = mktime(0,0,0,substr($_POST['sdat'],3,2),substr($_POST['sdat'],0,2),substr($_POST['sdat'],6,4));
		$edat = mktime(0,0,0,substr($_POST['edat'],3,2),substr($_POST['edat'],0,2),substr($_POST['edat'],6,4));
		mysql_query("DELETE FROM calendaritem WHERE calclass='". get_class($this). "' AND caldate >= '". $_POST['sdat']. "' AND caldate <= '". $_POST['edat']. "'",$userlink);
		while($curdat <= $edat)
		{
		  if(inputclassbase::load_query("SELECT * FROM calendaritem WHERE caldate='". date("Y-m-d",$curdat). "' AND (calclass='DisabledDays' OR calclass='OffDays')") == NULL)
		  {
			mysql_query("INSERT INTO calendaritem (caldate,calclass,caldata) VALUES('". date("Y-m-d",$curdat). "','". get_class($this). "','". $tname. "')", $userlink);
		  }
		  $curdat = mktime(0,0,0,date("n",$curdat),date("j",$curdat)+1,date("Y",$curdat));
		}
	  }
	}
	parent::load_datedata();
  }
  
  public function show_control()
  {
    if(isset($_POST['edittttdate']))
	{
	  $editdate = mktime(0,0,0,substr($_POST['edittttdate'],5,2),substr($_POST['edittttdate'],8,2),substr($_POST['edittttdate'],0,4));
	  if($this->get_dateinfo($editdate) != NULL)
	    $tablename = $this->get_dateinfo($editdate);
	  else
	    $tablename = $_SESSION['currenttimetabletimesname'];
	  echo("<h1>". $_SESSION['dtext']['EditTimetableTimes']. " `". $tablename. "`</H1>");
	  echo("<table class=timetabletimesedit><tr><th>". $_SESSION['dtext']['TimetableTimesTimeslot']. "</th>
	                                            <th>". $_SESSION['dtext']['Time']. "</th>
												<th>". $_SESSION['dtext']['Duration']. "</th></tr>");
	  // See if and if so, how many timeslots are already defined
	  $slots = inputclassbase::load_query("SELECT MAX(timeslot) AS ts FROM timetabletimes WHERE tablename='". $tablename. "'");
	  if(isset($slots['ts']) && $slots['ts'][0] > 1)
	    $showslots = $slots['ts'][0] + 1;
	  else
	    $showslots = 10;
	  for($ts=1; $ts <= $showslots; $ts++)
	  {
	    echo("<TR><TD>". $ts. "</td><td>");
		$tsfld = new inputclass_textfield("caledittimetabletimesstart". $ts,8 ,NULL,"starttime","timetabletimes",$ts,"timeslot",NULL,"datahandler.php");
		$tsfld->set_extrakey("tablename", $tablename);
		$tsfld->echo_html();
		echo("</td><td>");
		$tsfld = new inputclass_textfield("caledittimetabletimesduration". $ts,8 ,NULL,"duration","timetabletimes",$ts,"timeslot",NULL,"datahandler.php");
		$tsfld->set_extrakey("tablename", $tablename);
		$tsfld->echo_html();
		echo("</td></tr>");
	  }
	    
	}
	else if(isset($_POST['newtttdate']))
	{
	  echo("<h1>". $_SESSION['dtext']['NewTimetableTimes']. "</H1>");
	  $namefld = new inputclass_textfield("calnewtimetabletimesname",40 ,NULL,"tablename","timetabletimes",0,"timeslot",NULL,"datahandler.php");
	  $namefld->set_extrakey("caldate",$_POST['newtttdate']);
	  $namefld->echo_html();
	  echo("<SCRIPT> function delayedit() { setTimeout('document.getElementById(\"delayededit\").submit()',2000); } </SCRIPT>");
	  echo("<FORM ACTION='". $_SERVER['REQUEST_URI']. "' METHOD=POST ID=delayededit NAME=delayededit><INPUT TYPE=hidden NAME=edittttdate VALUE='". $_POST['newtttdate']. "'><INPUT TYPE=BUTTON VALUE='". $_SESSION['dtext']['ADD_CAP']. "' onClick='delayedit();'></FORM>");	
	}
  }
}

class TimetableActivities extends OffDays
{  
  public function get_dateinfo($date)
  {
    global $teachercode;
    if(isset($this->datedata[date("Y-m-d",$date)]))
	{
	  if(isset($_POST['tday']))
	  { // View single day mode
		if(!isset($_SESSION['flt_timetablegroup']) && !isset($_SESSION['flt_timetablesubject']) && 
		   !isset($_SESSION['flt_timetableteacher']) && !isset($_SESSION['flt_timetablelocation']))
		{ // No filter set, set the filter to match this teacher
		  if(!isset($teachercode))
			$_SESSION['flt_timetableteacher'] = $_SESSION['uid'];
		  else
		  { // Now this user might be lacking a teachercode! in that case the current group will be used to filter
		    $tdata = inputclassbase::load_query("SELECT data FROM `". $teachercode. "` WHERE tid=". $_SESSION['uid']);
		    if(!isset($tdata['data']))
		    { // So current teacher does not have a teachercode, so we get the current group as filter
		      $curgidqr = inputclassbase::load_query("SELECT gid FROM sgroup WHERE active=1 AND groupname='". $_SESSION['CurrentGroup']. "'");
			  if(isset($curgidqr['gid']))
			    $_SESSION['flt_timetablegroup'] = $curgidqr['gid'][0];
		    }
			else
			  $_SESSION['flt_timetableteacher'] = $_SESSION['uid'];
		  }
		}
		// Now return a string with the name of the timetable activities and the resulting timetable (times and activities)
		$retstr = $this->datedata[date("Y-m-d",$date)];
		// Get the lesson times
		$timestable = inputclassbase::load_query("SELECT caldata FROM calendaritem WHERE calclass='TimetableTimes' AND caldate='". date("Y-m-d",$date). "'");
		if(isset($timestable['caldata']))
		{
		  $timestablename = $timestable['caldata'][0];

		  // Get the data to be edited
		  $acts = TimetableActivity::list_activities($this->datedata[date("Y-m-d",$date)],NULL,
											isset($_SESSION['flt_timetablegroup']) ? $_SESSION['flt_timetablegroup'] : NULL,
	                                        isset($_SESSION['flt_timetablesubject']) ? $_SESSION['flt_timetablesubject'] : NULL,
											isset($_SESSION['flt_timetableteacher']) ? $_SESSION['flt_timetableteacher'] : NULL,
											isset($_SESSION['flt_timetablelocation']) ? $_SESSION['flt_timetablelocation'] : NULL);
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
			$retstr .= ("<table class=timetableactivitiesview><TH>". $_SESSION['dtext']['TimetableTimesTimeslot']. "</th>
	                                            <TH>". $_SESSION['dtext']['Time']. "</th>
												<TH>". $_SESSION['dtext']['Class']. "</th>
												<TH>". $_SESSION['dtext']['Location']. "</th></tr>");
  			foreach($acts AS $actobj)
			{
			  $actarr['cid'][$actobj->get_timeslot()] = $actobj->get_cid();
			  $actarr['loc'][$actobj->get_timeslot()] = $actobj->get_location();
			}
			$doubletime = false;
			foreach($tttdat['starttime'] AS $ts => $starttime)
			{  // Now add the data for each timeslot
			  if(isset($endtime) && $tttdat['starttime'][$ts] > $endtime) // Add a break
			    $retstr .= "<TR><TD colspan=4>". $_SESSION['dtext']['Break']. "</td></tr>";
			  $retstr .= ("<TR><TD>". $ts. "</td><TD>". substr($starttime,0,-3). "-". substr($tttdat['endtime'][$ts],0,-3));
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
				  $retstr .= "</td><TD". ($doubletime ? " rowspan=2" : ""). ">". $actarr['cid'][$ts]. "</td>
				                   <TD". ($doubletime ? " rowspan=2" : ""). ">". $actarr['loc'][$ts];
			    else
			      $retstr .= ("</td><TD colspan=2>-</td>");
			  }
			  else
			    $doubletime = false;
			  $retstr .= ("</td></tr>");
			  $endtime = $tttdat['endtime'][$ts];
			}
		  }
		  $retstr .= "</table>";
		}
		return($retstr);
	  }
	  else
	    return($this->datedata[date("Y-m-d",$date)]);
	}
	else
	  return null;
  }

  public function edit_dateinfo($date)
  {
    global $teachercode;
    global $currentuser;
	if(isset($currentuser) && !$currentuser->has_role("admin"))
	{
	  echo $this->get_dateinfo($date);
	  return;
	}
    // See fif any filter is already set, used for next step but needs te be prepared now...
	if(isset($_POST['eday']))
	{ // Edit single day mode
	  if(!isset($_SESSION['flt_timetablegroup']) && !isset($_SESSION['flt_timetablesubject']) && 
	     !isset($_SESSION['flt_timetableteacher']) && !isset($_SESSION['flt_timetablelocation']))
	  { // No filter set, set the filter to match this teacher
	    if(!isset($teachercode))
		  $_SESSION['flt_timetableteacher'] = $_SESSION['uid'];
		else
		{ // Now this user might be lacking a teachercode! in that case the current group will be used to filter
		  $tdata = inputclassbase::load_query("SELECT data FROM `". $teachercode. "` WHERE tid=". $_SESSION['uid']);
		  if(!isset($tdata['data']))
		  { // So current teacher does not have a teachercode, so we get the current group as filter
		    $curgidqr = inputclassbase::load_query("SELECT gid FROM sgroup WHERE active=1 AND groupname='". $_SESSION['CurrentGroup']. "'");
			if(isset($curgidqr['gid']))
			  $_SESSION['flt_timetablegroup'] = $curgidqr['gid'][0];
		  }
		}
		//echo("Filter set for teacher");
	  }
    }
    $calidqr = inputclassbase::load_query("SELECT calid FROM calendaritem WHERE caldate='". date("Y-m-d",$date). "' AND calclass='". get_class($this). "'");
	if(isset($calidqr['calid']))
	  $calid = $calidqr['calid'][0];
	else
	  $calid = -30;
    $offfld = new inputclass_listfield("caltimetableacts_". date("Y-m-d",$date),"SELECT '' AS id,'' AS tekst UNION SELECT DISTINCT timetablename, timetablename FROM timetableactivity UNION SELECT DISTINCT caldata,caldata FROM calendaritem WHERE calclass='". get_class($this). "'",NULL,"caldata","calendaritem",$calid,"calid",NULL,"datahandler.php");
	$offfld->set_extrafield("calclass",get_class($this));
	$offfld->set_extrafield("caldate",date("Y-m-d",$date));
	$offfld->echo_html();
	echo("<FORM ACTION='". $_SERVER['REQUEST_URI']. "' METHOD=POST><INPUT TYPE=hidden NAME=editttadate VALUE='". date("Y-m-d",$date). "'><INPUT TYPE=SUBMIT VALUE='". $_SESSION['dtext']['EditTimetableActivities']. "'></FORM>");
	echo("<FORM ACTION='". $_SERVER['REQUEST_URI']. "' METHOD=POST><INPUT TYPE=hidden NAME=newttadate VALUE='". date("Y-m-d",$date). "'><INPUT TYPE=SUBMIT VALUE='". $_SESSION['dtext']['NewTimetableActivities']. "'></FORM>");
	// Include dialogue items for assignment of timetable activities to multiple dates.
	$perioddates = inputclassbase::load_query("SELECT MIN(startdate) AS sdat, MAX(enddate) AS edat FROM period");
	if(isset($perioddates['sdat']))
	{
	  $sdat = $perioddates['sdat'][0];
	  $edat = $perioddates['edat'][0];
	}
	else
	{
	  $sdat = date("Y-m-d");
	  $edat = date("Y-m-d");
	}
    echo("<FORM ACTION='". $_SERVER['REQUEST_URI']. "' METHOD=POST><INPUT TYPE=hidden NAME=copyacttables VALUE='". date("Y-m-d",$date). "'>");
	echo("<INPUT TYPE=SUBMIT VALUE='". $_SESSION['dtext']['TimetableActivitiesPlacement']. "'> ". $_SESSION['dtext']['DateFrom']. " ");
	     " <INPUT TYPE=TEXT NAME=sdat VALUE='". $sdat. "'> ". $_SESSION['dtext']['DateUntil']. 
		 " <INPUT TYPE=TEXT NAME=edat VALUE='". $edat. "'> ". 

	$sdfield = new inputclass_datefield("ttasdat",inputclassbase::mysqldate2nl($sdat));
	$sdfield->set_parameter("dc-startdate",date("mdY",mktime(0,0,0,substr($sdat,5,2),substr($sdat,8,2),substr($sdat,0,4))));
	$sdfield->echo_html();
	echo("<SCRIPT> function dsfuncttasdat() { } </SCRIPT>"); // This avoids data being sent by Ajax functions
	echo(" ". $_SESSION['dtext']['DateUntil']. " ");
	$edfield = new inputclass_datefield("ttaedat",inputclassbase::mysqldate2nl($edat));
	$edfield->set_parameter("dc-startdate",date("mdY",mktime(0,0,0,substr($edat,5,2),substr($edat,8,2),substr($edat,0,4))));
	$edfield->echo_html();
	echo("<SCRIPT> function dsfuncttaedat() { } </SCRIPT>"); // This avoids data being sent by Ajax functions
	// Show the day boxes with the selected day or origen checked
	echo(" ". $_SESSION['dtext']['DateDays']);
	$defday = date("w",$date);
	for($cd = 0;$cd < 7; $cd++)
	{
	  echo("&nbsp;&nbsp;". $_SESSION['dtext']["dayabbrev_". $cd]. "<INPUT TYPE=CHECKBOX NAME='ttacopyday_". $cd. "'");
	  if($cd == $defday)
	    echo(" checked");
	  echo(">");
	}
	echo(" </FORM>");
 }
  
  public function request_control()
  {
    if(isset($_POST['editttadate']) || isset($_POST['newttadate']))
	  return true;
  }

  protected function load_datedata()
  {
    global $userlink;
    if(isset($_POST['copyacttables']))
	{ // Copy timetable activities requested
	  $tnamerq = inputclassbase::load_query("SELECT caldata FROM calendaritem WHERE calclass='". get_class($this). "' AND caldate='". $_POST['copyacttables']. "'");
	  if(isset($tnamerq['caldata']))
	  {
	    $tname = $tnamerq['caldata'][0];
		$curdat = mktime(0,0,0,substr($_POST['ttasdat'],3,2),substr($_POST['ttasdat'],0,2),substr($_POST['ttasdat'],6,4));
		$edat = mktime(0,0,0,substr($_POST['ttaedat'],3,2),substr($_POST['ttaedat'],0,2),substr($_POST['ttaedat'],6,4));
		while($curdat <= $edat)
		{
		  if(inputclassbase::load_query("SELECT * FROM calendaritem WHERE caldate='". date("Y-m-d",$curdat). "' AND calclass='TimetableTimes'") != NULL)
		  {
		    // Only copy if according to day checkboxes need to copy to that day
			if(isset($_POST["ttacopyday_". date("w",$curdat)]))
			{
		      mysql_query("DELETE FROM calendaritem WHERE calclass='". get_class($this). "' AND caldate = '". date("Y-m-d",$curdat). "'",$userlink);
			  mysql_query("INSERT INTO calendaritem (caldate,calclass,caldata) VALUES('". date("Y-m-d",$curdat). "','". get_class($this). "','". $tname. "')", $userlink);
			}
		  }
		  $curdat = mktime(0,0,0,date("n",$curdat),date("j",$curdat)+1,date("Y",$curdat));
		}
	  }
	}
	parent::load_datedata();
  }
  
  public function show_control()
  {
    global $userlink;
    if(isset($_POST['editttadate']))
	{
	  if(isset($_POST['remttaid']))
	  {
	    mysql_query("DELETE FROM timetableactivity WHERE actid=". $_POST['remttaid'], $userlink);
		echo(mysql_error($userlink));
	  } 
	  $editdate = mktime(0,0,0,substr($_POST['editttadate'],5,2),substr($_POST['editttadate'],8,2),substr($_POST['editttadate'],0,4));
	  if($this->get_dateinfo($editdate) != NULL)
	    $tablename = $this->get_dateinfo($editdate);
	  else
	    $tablename = $_SESSION['currenttimetableactivitiesname'];
	  $this->edit_timetable_activities($tablename);	    
	}
	else if(isset($_POST['newttadate']))
	{
	  echo("<h1>". $_SESSION['dtext']['NewTimetableActivities']. "</H1>");
	  $namefld = new inputclass_textfield("calnewtimetableactivitiesname",40 ,NULL,"timetablename","timetableactivity",0,"timeslot",NULL,"datahandler.php");
	  $namefld->set_extrakey("caldate",$_POST['newttadate']);
	  $namefld->echo_html();
	  echo("<SCRIPT> function delayedit() { setTimeout('document.getElementById(\"delayededit\").submit()',2000); } </SCRIPT>");
	  echo("<FORM ACTION='". $_SERVER['REQUEST_URI']. "' METHOD=POST ID=delayededit NAME=delayededit><INPUT TYPE=hidden NAME=editttadate VALUE='". $_POST['newttadate']. "'><INPUT TYPE=BUTTON VALUE='". $_SESSION['dtext']['ADD_CAP']. "' onClick='delayedit();'></FORM>");	
	}
  }
  
  private function edit_timetable_activities($tablename)
  {
    global $teachercode;
	echo("<h1>". $_SESSION['dtext']['EditTimetableActivities']. " `". $tablename. "`</H1>");
	// The field below will not be processed normally by the datahandler, it just stores in $_SESSION and sends a refresh.
	echo($_SESSION['dtext']['group']. " : ");
	$fltfldgrp = new inputclass_listfield("flt_timetablegroup","SELECT '' AS id,'*' AS tekst UNION SELECT gid,groupname FROM sgroup WHERE active=1 ORDER BY tekst",NULL,"gid","sgroup",isset($_SESSION['flt_timetablegroup']) ? $_SESSION['flt_timetablegroup'] : -31,"gid",NULL,"datahandler.php");
	$fltfldgrp->echo_html();
	echo(" ". $_SESSION['dtext']['Subject']. " : ");
	$fltfldgrp = new inputclass_listfield("flt_timetablesubject","SELECT '' AS id,'*' AS tekst UNION SELECT mid,shortname FROM subject ORDER BY tekst",NULL,"mid","subject",isset($_SESSION['flt_timetablesubject']) ? $_SESSION['flt_timetablesubject'] : -32,"mid",NULL,"datahandler.php");
	$fltfldgrp->echo_html();
	if(isset($teachercode))
	  $tdatatq = "SELECT tid,data FROM teacher LEFT JOIN `". $teachercode. "` USING (tid) WHERE is_gone='N' AND data IS NOT NULL ORDER BY tekst";
	else
	  $tdatatq = "SELECT tid,CONCAT(firstname,' ',lastname) AS tdat FROM teacher WHERE is_gone='N' ORDER BY tekst";
	echo(" ". $_SESSION['dtext']['Teacher']. " : ");
	$fltfldgrp = new inputclass_listfield("flt_timetableteacher","SELECT '' AS id,'*' AS tekst UNION ". $tdatatq,NULL,"tid","teacher",isset($_SESSION['flt_timetableteacher']) ? $_SESSION['flt_timetableteacher'] : -33,"tid",NULL,"datahandler.php");
	$fltfldgrp->echo_html();
	echo(" ". $_SESSION['dtext']['Location']. " : ");
	$fltfldgrp = new inputclass_listfield("flt_timetablelocation","SELECT '' AS id,'*' AS tekst UNION SELECT DISTINCT location,location FROM timetableactivity ORDER BY tekst",NULL,"location","timetableactivity",isset($_SESSION['flt_timetablelocation']) ? $_SESSION['flt_timetablelocation'] : -34,"location",NULL,"datahandler.php");
	$fltfldgrp->echo_html();
	echo("<BR><BR><table class=timetableactivities><TR><TH>". $_SESSION['dtext']['TimetableTimesTimeslot']. "</th><TH>".
	      $_SESSION['dtext']['Class']. "</th><TH>". $_SESSION['dtext']['Location']. "</th><TH><img src='PNG/action_delete.png'></th></TR>");
	// Get the data to be edited
	$acts = TimetableActivity::list_activities($tablename,NULL,isset($_SESSION['flt_timetablegroup']) ? $_SESSION['flt_timetablegroup'] : NULL,
	                                         isset($_SESSION['flt_timetablesubject']) ? $_SESSION['flt_timetablesubject'] : NULL,
											 isset($_SESSION['flt_timetableteacher']) ? $_SESSION['flt_timetableteacher'] : NULL,
											 isset($_SESSION['flt_timetablelocation']) ? $_SESSION['flt_timetablelocation'] : NULL);
	// Get the max numbr of timeslots
	$mts = inputclassbase::load_query("SELECT MAX(timeslot) AS mts FROM timetabletimes");
	if(isset($mts['mts']))
	  $mts = $mts['mts'][0];
	else
	  $mts = 10;
	for($ts=1; $ts <= $mts; $ts++)
	{
	  // First create an entry for a new timeslot
	  $actitem = new TimetableActivity(NULL,$ts,$tablename);
	  echo("<TR><TD>". $ts. "</td><TD>");
	  $actitem->edit_cid();
	  echo("</td><TD>");
	  $actitem->edit_location();
	  echo("</td><TD>&nbsp;</td></tr>");
	  // Now add the exiting activities for this timeslot
	  if(isset($acts))
	  {
	    foreach($acts AS $actobj)
		{
		  if($actobj->get_timeslot() == $ts)
		  {
		    echo("<TR><TD>");
			$actobj->edit_timeslot();
			echo("</td><TD>");
			$actobj->edit_cid();
			echo("</td><TD>");
			$actobj->edit_location();
			echo("</td><TD><img src=PNG/action_delete.png onClick='if(confirm(\"". $_SESSION['dtext']['confirm_delete']. "\")) { removettactivity(". $actobj->get_id(). "); }'></td></tr>");
		  }
		}
	  }
	}
	echo("</table>");
	echo("<FORM ID=removettactivityform METHOD=POST ACTION='". $_SERVER['REQUEST_URI']. "'>
	       <INPUT TYPE=HIDDEN NAME='editttadate' VALUE='". $_POST['editttadate']. "'>
		   <INPUT TYPE=HIDDEN NAME='remttaid' VALUE=0 ID='remttaid'></FORM>");
	echo("<SCRIPT> function removettactivity(actid) { 
	                document.getElementById('remttaid').value=actid; 
					document.getElementById('removettactivityform').submit(); } </SCRIPT>");	
  }
}

class TimetableActivity
{
  protected $tablename,$actid,$timeslot;
  protected $cid_fld,$location_fld,$timeslot_field;
  public function __construct($actid = NULL,$timeslot = NULL,$tablename = NULL)
  {
    if(isset($actid))
	  $this->actid = $actid;
	else
	  $this->actid = 0 - rand(3100,7000);
	if(isset($timeslot))
	  $this->timeslot = $timeslot;
	if(isset($tablename))
	  $this->tablename = $tablename;	
  }
  public function get_id()
  {
    return $this->actid;
  }
  
  public function get_cid_cid()
  {
    if($this->actid > 0)
	{
	  $cidqr = inputclassbase::load_query("SELECT cid FROM timetableactivity WHERE actid=". $this->actid);
	  if(isset($cidqr['cid'][0]))
	    return $cidqr['cid'][0];
	}
	return null;
  }
  public function get_cid()
  {
    if($this->actid < 0)
	  return NULL;
	else
	  return $this->get_cid_fld()->__toString();
  }
  
  protected function get_cid_fld()
  {
    global $teachercode;
	if(isset($this->cid_fld))
	  return($this->cid_fld);
    $selqry = "SELECT '' AS id,'' AS tekst UNION SELECT id,tekst FROM (SELECT cid AS id, CONCAT(groupname,'&#45;',shortname,'&#45;',";
	if(isset($teachercode))
	  $selqry .= "data";
	else
	  $selqry .= "firstname,' ',lastname";
	$selqry .= ") AS tekst FROM `class` LEFT JOIN sgroup USING(gid) LEFT JOIN subject USING(mid) LEFT JOIN ";
	if(isset($teachercode))
	  $selqry .= "`". $teachercode. "`";
	else
	  $selqry .= "teacher";
	$selqry .= " USING(tid) WHERE active=1 AND cid IS NOT NULL";
	if(isset($_SESSION['flt_timetableteacher']))
	  $selqry .= " AND tid=". $_SESSION['flt_timetableteacher'];
	if(isset($_SESSION['flt_timetablegroup']))
	  $selqry .= " AND gid=". $_SESSION['flt_timetablegroup'];
	if(isset($_SESSION['flt_timetablesubject']))
	  $selqry .= " AND mid=". $_SESSION['flt_timetablesubject'];
	$selqry .= " AND groupname <> '' AND groupname IS NOT NULL ORDER BY tekst) AS t2 WHERE tekst <> ''";
	//echo($selqry);
	$this->cid_fld = new inputclass_listfield("ttacid". $this->actid,$selqry,NULL,"cid","timetableactivity",$this->actid,"actid",NULL,"datahandler.php");
    if(isset($this->tablename))
	  $this->cid_fld->set_extrafield("timetablename",$this->tablename);
	if(isset($this->timeslot))
	  $this->cid_fld->set_extrafield("timeslot",$this->timeslot);
	return $this->cid_fld;  
  }
  public function edit_cid()
  {
	$this->get_cid_fld()->echo_html();
  }
  
  public function get_location()
  {
    if($this->actid < 0)
	  return NULL;
	return $this->get_location_fld()->__toString();
  }
  
  protected function get_location_fld()
  {
    if(!isset($this->location_fld))
	{
	  $this->location_fld = new inputclass_textfield("ttaloc". $this->actid,5,NULL,"location","timetableactivity",$this->actid,"actid",NULL,"datahandler.php");
    if(isset($this->tablename))
	  $this->location_fld->set_extrafield("timetablename",$this->tablename);
	if(isset($this->timeslot))
	  $this->location_fld->set_extrafield("timeslot",$this->timeslot);
	}
	return $this->location_fld;  
  }
  public function edit_location()
  {
	$this->get_location_fld()->echo_html();
  }
  
  public function get_timeslot()
  {
    if($this->actid < 0)
	{
	  if(isset($this->timeslot))
	    return($this->timeslot);
	  else
	    return NULL;
	}
	return $this->get_timeslot_fld()->__toString();
  }
  
  protected function get_timeslot_fld()
  {
    if(!isset($this->timeslot_fld))
	{
	  // Create the query for the selectable timeslots
	  $mts = inputclassbase::load_query("SELECT MAX(timeslot) AS mts FROM timetabletimes");
	  if(isset($mts['mts']))
	    $mts = $mts['mts'][0];
	  else
	    $mts = 10;
	  $tsqry = "SELECT '' AS id,'' AS tekst";
	  for($ts=1; $ts <= $mts; $ts++)
	  {
	    $tsqry .= " UNION SELECT ". $ts. ",". $ts;
	  }
	  
	  $this->timeslot_fld = new inputclass_listfield("ttats". $this->actid,$tsqry,NULL,"timeslot","timetableactivity",$this->actid,"actid",NULL,"datahandler.php");
      if(isset($this->tablename))
	    $this->timeslot_fld->set_extrafield("timetablename",$this->tablename);
	}
	return $this->timeslot_fld;  
  }
  public function edit_timeslot()
  {
	$this->get_timeslot_fld()->echo_html();
  }
  
  static public function list_activities($tablename,$timeslot = NULL,$group = NULL, $subject=NULL,$teacher=NULL,$location=NULL)
  {
    $qry = "SELECT actid FROM timetableactivity LEFT JOIN `class` USING (cid) LEFT JOIN sgroup USING (gid) WHERE active=1 AND timetablename='". $tablename. "'";
	if(isset($timeslot))
	  $qry .= " AND timeslot=". $timeslot;
	if(isset($group))
	if(is_array($group))
	{
	  $firstgrp = true;
	  $qry .= " AND gid IN(";
	  foreach($group AS $agid)
	  {
	    if($firstgrp)
		{
	      $qry .= $agid;
		  $firstgrp = false;
		}
		else
		{
		  $qry .= ",". $agid;
		}
	  }
	  $qry .= ")";
	}
	else
	  $qry .= " AND gid=". $group;
	if(isset($subject))
	  $qry .= " AND mid=". $subject;
	if(isset($teacher))
	  $qry .= " AND tid=". $teacher;
	if(isset($location))
	  $qry .= " AND location='". $location. "'";
	$qry .= " ORDER BY timeslot,groupname";
	$acts = inputclassbase::load_query($qry);
	if(isset($acts['actid']))
	{
	  foreach($acts['actid'] AS $actid)
	    $retar[$actid] = new TimetableActivity($actid);
	  return($retar);
	}
	return NULL;
  }  
}

?>
