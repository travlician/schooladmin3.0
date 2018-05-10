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
require_once("teacher.php");
require_once("student.php");
require_once("absencecategory.php");
require_once("subjectselector.php");
require_once("CalendarLayer.php");

class StudentGUI extends extendableelement
{
	protected $acatlist;
  protected function add_contents()
  {
    // This function is based on tables that is created as needed. So now we create it if it does not exist.
		global $userlink;
    global $teachercode;
    $sqlquery = "CREATE TABLE IF NOT EXISTS `stud_guiloc` (
      `sid` INTEGER(11) NOT NULL,
      `tid` INTEGER(11) UNSIGNED NOT NULL,
      `xoff` INTEGER(11) DEFAULT NULL,
      `yoff` INTEGER(11) DEFAULT NULL,
			`year` VARCHAR(10) DEFAULT NULL,
			`orientation` ENUM('l','r','t','b') DEFAULT 'b',
	  PRIMARY KEY (`sid`,`tid`)
      ) ENGINE=InnoDB;";
    mysql_query($sqlquery,$userlink);
    echo(mysql_error());
		$curyqr = inputclassbase::load_query("SELECT year FROM period");
		$this->curyear = $curyqr['year'][0];
		$this->acatlist = absencecategory::list_categories(true);
    if(isset($_POST['delte']))
		{
			if($_POST['delte'] == 0)
			{ // Delete the latest added absence record
				$lastabsrec = inputclassbase::load_query("SELECT MAX(asid) AS `todelete` FROM absence");
				$_POST['delte']=$lastabsrec['todelete'][0];
			}
			mysql_query("DELETE FROM absence WHERE asid=". $_POST['delte']);
		}
		
		// Preparng the blackboard: see which is the current timeslot and cid
		// See if we have a classbook with timeslots and if so, what is the current timeslot
		$clientnow = mktime() + $_SESSION['ClientTimeOffset'];
	  $now = mktime() + $_SESSION['ClientTimeOffset'];
	  $this->startDate = mktime(0,0,0,Date("n",$now),Date("j",$now),Date("Y",$now));
		
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
		$ttd = new TimetableTimes($_SESSION['dtext']['TimetableTimes'],true,$this->startDate,$this->startDate);
		$timestablename = $ttd->get_dateinfo($this->startDate);
	  $tttdata = inputclassbase::load_query("SELECT *,ADDTIME(starttime,duration) AS endtime FROM timetabletimes WHERE tablename='". $timestablename. "' ORDER BY timeslot");
	  foreach($tttdata['timeslot'] AS $tttix => $ts)
	  {
			$tttdat['starttime'][$ts] = $tttdata['starttime'][$tttix];
			$tttdat['endtime'][$ts] = $tttdata['endtime'][$tttix];
	  }
		$act = new TimetableActivities($_SESSION['dtext']['TimetableActivities'],true,$this->startDate,$this->startDate);
		$acttable = $act->get_dateinfo($this->startDate);
	  $acts = TimetableActivity::list_activities($acttable,NULL,isset($filtgrp) ? $filtgrp : NULL,NULL,isset($filtteacher) ? $filtteacher : NULL);
	  if(!isset($acts))
	  { // No activities set. Now if this is an administrator we can show the current group instead. (added jan 7 2014)
			$curgidqr = inputclassbase::load_query("SELECT gid FROM sgroup WHERE active=1 AND groupname='". $_SESSION['CurrentGroup']. "'");
			if(isset($curgidqr['gid']))
				$filtgrp = $curgidqr['gid'][0];
			$acts = TimetableActivity::list_activities($acttable,NULL,isset($filtgrp) ? $filtgrp : NULL,NULL,NULL);	
		}
    if(isset($acts))
			foreach($acts AS $actobj)
			{
				$actarr['cid'][$actobj->get_timeslot()] = $actobj->get_cid();
				$actarr['loc'][$actobj->get_timeslot()] = $actobj->get_location();
				$actarr['cidcid'][$actobj->get_timeslot()] = $actobj->get_cid_cid();
			}
		if(isset($tttdat))
		foreach($tttdat['starttime'] AS $ts => $starttime)
		{  // Now check each timeslot
		  $curts = false;
		  if(isset($_POST['SelectTimeslot']) && $ts == $_POST['SelectTimeslot'])
		    $this->CurrentTimeslot = $ts;
		  else if(!isset($_POST['SelectTimeslot']) && date("G:i:s",$clientnow) >= $starttime && date("G:i:s",$clientnow) < $tttdat['endtime'][$ts])
		  {
				$this->CurrentTimeslot = $ts;
				$this->CurrentStarttime = $starttime;
				$this->CurrentEndtime = $tttdat['endtime'][$ts];
				// Since current timeslot has been created automatically, set refresh for the next if available
				if(isset($tttdat['starttime'][$ts+1]))
				{
					$reloadtime = $tttdat['starttime'][$ts+1];
					$reloaddelay = (mktime(substr($reloadtime,0,2),substr($reloadtime,3,2),substr($reloadtime,6,2),
																 date('n',$clientnow),date('j',$clientnow),date("Y",$clientnow)) - $clientnow) * 1000;
					$reloadscript = ("<SCRIPT> setTimeout('location.reload(true)',". $reloaddelay. "); </SCRIPT>");
				}
		  }
			if(isset($this->CurrentTimeslot) && $this->CurrentTimeslot == $ts)
				$this->cidcid = $actarr['cidcid'][$ts];
		}
		if(isset($this->cidcid))
		{ // Extract the current mid from the cid
			$midqr = inputclassbase::load_query("SELECT mid,groupname FROM class LEFT JOIN sgroup USING(gid) WHERE cid=". $this->cidcid);
			if(isset($midqr['mid'][0]))
			{
				$this->curmid = $midqr['mid'][0];
				$_SESSION['CurrentSubject'] = $this->curmid;
				// Now if the current group does not match the group correponding the cid, change it!
				if($_SESSION['CurrentGroup'] != $midqr['groupname'][0])
				{
					$_SESSION['CurrentGroup'] = $midqr['groupname'][0];
					//echo("<SCRIPT> document.location.reload(true); </script>");
				}
			}
		}
		$this->subselbox = new subjectselector(NULL,"text-align : right; max-width: 20%; float: right;",NULL,isset($this->mid) ? $this->mid : NULL,true);
	}
  
  public function show_contents()
  {
    global $livepictures,$currentuser;
	  // Get the table name for the images
	  $pictn = inputclassbase::load_query("SELECT table_name FROM student_details WHERE type='picture' ORDER BY seq_no LIMIT 1"); 
	  // Get a list of all applicable students
	  $students = inputclassbase::load_query("SELECT CONCAT(firstname,', ',lastname) AS name,firstname, lastname, sid, data AS imgname 
								FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) 
								LEFT JOIN ". $pictn['table_name'][0]. " USING(sid)
								WHERE active=1 AND sgroup.groupname = '". $_SESSION['CurrentGroup']. "' ORDER BY name");
	  // Get the image locations applicable for this group, first we load the mentor's locations, then current teacher locations
	  $imglocs = SA_loadquery("SELECT sid,xoff,yoff,orientation FROM stud_guiloc LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) WHERE active=1 AND tid=tid_mentor AND groupname= '". $_SESSION['CurrentGroup']. "' AND year='". $this->curyear. "'");
	  if(isset($imglocs['sid']))
	  {
			foreach($imglocs['sid'] AS $six => $sid)
			{
				$imgloc[$sid]['x'] = $imglocs['xoff'][$six];
				$imgloc[$sid]['y'] = $imglocs['yoff'][$six];
				$imgloc[$sid]['orientation'] = $imglocs['orientation'][$six];
			}
	  }
	  $imglocs = SA_loadquery("SELECT sid,xoff,yoff,orientation FROM stud_guiloc LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) WHERE active=1 AND tid=". $_SESSION['uid']. " AND groupname= '". $_SESSION['CurrentGroup']. "' AND year='". $this->curyear. "'");
	  if(isset($imglocs['sid']))
	  {
		foreach($imglocs['sid'] AS $six => $sid)
			{
				$imgloc[$sid]['x'] = $imglocs['xoff'][$six];
				$imgloc[$sid]['y'] = $imglocs['yoff'][$six];
				$imgloc[$sid]['orientation'] = $imglocs['orientation'][$six];
			}
	  }
?>
		<style>
		.dragme1
		{
			position:relative;
			display: inline-block;
			width: 120px;
			height: 120px;
			padding: 0px;
			margin: 0px;
			max-width: 120px;
			max-height: 120px;
			border: none;
			vertical-align: top;
		}

		.dragme2
		{
			position:relative;
			display: inline-block;
			width: 120px;
			height: 120px;
			padding: 0px;
			margin: 0px;
			max-width: 120px;
			max-height: 120px;
			border: none;
			vertical-align: top;
		}

		.dragme3
		{
			position:relative;
			display: inline-block;
			width: 160px;
			height: 80px;
			padding: 0px;
			margin: 0px;
			max-width: 160px;
			max-height: 80px;
			border: none;
			vertical-align: top;
			white-space: nowrap;
			overflow: hidden;
		}

		.dragme4
		{
			position:relative;
			display: inline-block;
			width: 160px;
			height: 80px;
			padding: 0px;
			margin: 0px;
			max-width: 160px;
			max-height: 80px;
			border: none;
			vertical-align: top;
			white-space: nowrap;
			overflow: hidden;
		}

		#enlargeb
		{
			position:fixed;
			display: none;
			width: 360px;
			height: 360px;
			padding: 0px;
			margin: 0px;
			max-width: 360px;
			max-height: 360px;
			border: 2px solid black;
			vertical-align: top;
			top: 100px;
			left: 100px;
			z-index:3000;
		}

		#enlarget
		{
			position:fixed;
			display: none;
			width: 120px;
			height: 120px;
			padding: 0px;
			margin: 0px;
			max-width: 120px;
			max-height: 120px;
			border: 2px solid black;
			vertical-align: top;
			top: 100px;
			left: 100px;
			z-index:3000;
		}

		#enlargel
		{
			position:fixed;
			display: none;
			width: 482px;
			height: 242px;
			padding: 0px;
			margin: 0px;
			max-width: 482px;
			max-height: 242px;
			vertical-align: top;
			border: 2px solid black;
			white-space: nowrap;
			overflow: hidden;
			top: 100px;
			left: 100px;
			z-index:3000;
		}

		#enlarger
		{
			position:fixed;
			display: none;
			width: 482px;
			height: 242px;
			padding: 0px;
			margin: 0px;
			max-width: 482px;
			max-height: 242px;
			vertical-align: top;
			border: 2px solid black;
			white-space: nowrap;
			overflow: hidden;
			top: 100px;
			left: 100px;
			z-index:3000;
		}

		.tableatable
		{
			width: 40px;
			height: 80px;
			border: 0px solid black !important;
			background-color: peru;
			margin: 0px;
			padding: 0px;
			border-collapse: collapse;
		}
		.tableatable TR
		{
			margin: 0px;
			padding: 0px;
			max-height: 16px;
			border: none;
		}
		.tableatable TD
		{
			width: 10px;
			max-width: 10px;
			text-align: center;
			font-size: 9px;
			margin: 0px;
			padding: 0px;
			max-height: 16px;
			white-space: nowrap;
			overflow: hidden;
		}
		
		.largetableatable
		{
			width: 120px;
			height: 240px;
			border: 0px solid black !important;
			background-color: peru;
			margin: 0px;
			padding: 0px;
			border-collapse: collapse;
		}
		.largetableatable TR
		{
			margin: 0px;
			padding: 0px;
			max-height: 48px;
			border: none;
		}
		.largetableatable TD
		{
			width: 30px;
			max-width: 30px;
			text-align: center;
			font-size: 27px;
			margin: 0px;
			padding: 0px;
			max-height: 48px;
			white-space: nowrap;
			overflow: hidden;
		}
		
		.tablebtable
		{
			width: 120px;
			height: 40px;
			border: 0px solid black !important;
			background-color: peru;
			margin: 0px;
			padding: 0px;
			border-collapse: collapse;
		}
		.tablebtable TR
		{
			margin: 0px;
			padding: 0px;
			max-height: 10px;
			border: none;
		}
		.tablebtable TD
		{
			text-align: center;
			font-size: 9px;
			margin: 0px;
			padding: 0px;
			max-height: 10px;
			white-space: nowrap;
			overflow: hidden;
		}

		.largetablebtable
		{
			width: 360px;
			height: 120px;
			border: 0px solid black !important;
			background-color: peru;
			margin: 0px;
			padding: 0px;
			border-collapse: collapse;
		}
		.largetablebtable TR
		{
			margin: 0px;
			padding: 0px;
			max-height: 30px;
			border: none;
		}
		.largetablebtable TD
		{
			text-align: center;
			font-size: 27px;
			margin: 0px;
			padding: 0px;
			max-height: 30px;
			white-space: nowrap;
			overflow: hidden;
		}

		.tablestudtab
		{
			width: 120px;
			height: 80px;
			border: 1px solid grey !important;
			margin: 0px;
			padding: 0px;
			border-collapse: collapse;	
		}
		.largetablestudtab
		{
			width: 360px;
			height: 240px;
			border: 3px solid grey !important;
			margin: 0px;
			padding: 0px;
			border-collapse: collapse;	
			background-color: white;
			background-repeat: no-repeat;
			background-size: 210px 240px;
			background-position: center center;	
		}
		.mentcode
		{
			margin: 0px;
			padding: 0px;
			font-size: 8px;
			width: 24px;
			height: 9px;
			text-align: center;
		}
		.largementcode
		{
			margin: 0px;
			padding: 0px;
			font-size: 24px;
			width: 72px;
			height: 27px;
			text-align: center;
		}
		.studname
		{
			margin: 0px;
			padding: 0px;
			font-size: 10px;
			font-weight: bold;
			width: 70px;
			max-width: 70px;
			max-height: 10px;
			text-align: center;
			white-space: nowrap;
			overflow: hidden;
			background : rgba(170,170,170,0.5);
		}
		.largestudname
		{
			margin: 0px;
			padding: 0px;
			font-size: 20px;
			font-weight: bold;
			width: 210px;
			max-width: 210px;
			max-height: 30px;
			text-align: center;
			white-space: nowrap;
			overflow: hidden;
			background : rgba(170,170,170,0.5);
		}
		.bell
		{
			margin: 0px;
			padding: 0px;
			width: 24px;
			background-image: url(PNG/bell.jpg);
			background-size: 24px 9px;
			background-repeat: no-repeat;
			height: 9px;
		}
		.largebell
		{
			margin: 0px;
			padding: 0px;
			width: 72px;
			background-image: url(PNG/bell.jpg);
			background-size: 72px 27px;
			background-repeat: no-repeat;
			height: 27px;
		}
		.nobell
		{
			margin: 0px;
			padding: 0px;
			overflow: hidden;
			white-space: nowrap;
			width: 24px;		
			height: 9px;
			max-height: 9px;
			font-size: 8px;
			text-align: center;
		}
		.normalcell
		{
			margin: 0px;
			padding: 0px;
			overflow: hidden;
			white-space: nowrap;
			width: 24px;		
			height: 9px;
			max-height: 9px;
			font-size: 8px;
			text-align: center;
		}
		.largenobell
		{
			margin: 0px;
			padding: 0px;
			overflow: hidden;
			white-space: nowrap;
			width: 72px;		
			height: 27px;
			max-height: 27px;
			font-size: 24px;
			text-align: center;
		}
		.largecell
		{
			margin: 0px;
			padding: 0px;
			overflow: hidden;
			white-space: nowrap;
			width: 72px;		
			height: 27px;
			max-height: 27px;
			font-size: 24px;
			text-align: center;
		}
		.studimg
		{
			margin: 0px;
			padding: 0px;
			height: 58px;
			width: 70px;
		}
		.largestudimg
		{
			margin: 0px;
			padding: 0px;
			height: 174px;
			width: 210px;
		}

		#infoblock
		{
			position:fixed;
			display: none;
			border: 2px solid black;
			vertical-align: top;
			top: 50px;
			left: 50px;
			background-color: #EEE;
			z-index:3001;
		}
		#assignimages
		{
			position:fixed;
			border: 2px solid black;
			vertical-align: top;
			bottom: 0px;
			right: 0px;
			background-color: #EEE;
			z-index:2000;			
		}
		
		.assImg
		{
			padding: 0px;
		}
		
		#blackboard
		{
			position:fixed;
			border: 2px solid black;
			background-color: black;
			vertical-align: top;
			bottom: 0px;
			left: 10%;
			color: white;
			width: 40%;
			z-index:2000;		
		}
		.bblinew
		{
			color: white;
			margin-top: 0px;
			margin-bottom: 0px;
			padding-top: 0px;
			padding-bottom: 0px;
		}
		.bbliney
		{
			color: yellow;
			margin-top: 0px;
			margin-bottom: 0px;
			padding-top: 0px;
			padding-bottom: 0px;
		}
		.bbliner
		{
			color: red;
			margin-top: 0px;
			margin-bottom: 0px;
			padding-top: 0px;
			padding-bottom: 0px;
		}
		.bblineg
		{
			color: green;
			margin-top: 0px;
			margin-bottom: 0px;
			padding-top: 0px;
			padding-bottom: 0px;
			float: right;
		}
		


		</style>
		<script>
		function touchstart(ev)
		{
			xtarget = ev2xtarget(ev);
			xtarget.orgX = ev.targetTouches[0].pageX;
			xtarget.orgY = ev.targetTouches[0].pageY;
			xtarget.orgL = parseInt(xtarget.style.left+0);
			xtarget.orgT = parseInt(xtarget.style.top+0);
			xtarget.moved=false;
		}

		function dragstart(ev)
		{
			xtarget = ev2xtarget(ev);
			ev.preventDefault();
			xtarget.style.zIndex=1000;
			xtarget.orgX = ev.pageX;
			xtarget.orgY = ev.pageY;
			xtarget.orgL = parseInt(xtarget.style.left+0);
			xtarget.orgT = parseInt(xtarget.style.top+0);
			xtarget.dragging = true;
			xtarget.moved=false;
		}

		function touchmove(ev)
		{
			ev.preventDefault();
			xtarget = ev2xtarget(ev);
			//alert("Touchmove on "+ev.target.id+" ("+ev.touches[0].pageX+","+ev.touches[0].pageY+")");
			xtarget.style.left = (xtarget.orgL + ev.targetTouches[0].pageX - xtarget.orgX)+"px";
			xtarget.style.top = (xtarget.orgT + ev.targetTouches[0].pageY - xtarget.orgY)+"px";
			xtarget.moved=true;
		}

		function dragmove(ev)
		{
			xtarget = ev2xtarget(ev);
			if(xtarget.dragging)
			{
			//ev.preventDefault();
				xtarget.style.left = (xtarget.orgL + ev.pageX - xtarget.orgX)+"px";
				xtarget.style.top = (xtarget.orgT + ev.pageY -xtarget.orgY)+"px";
				xtarget.moved=true;
			}
		}

		function touchend(ev)
		{
			xtarget = ev2xtarget(ev);
			if(xtarget.moved)
				send_imgpos(xtarget);
			else
				clickproc(ev);
		}

		function dragend(ev)
		{
			xtarget = ev2xtarget(ev);		
			if(xtarget.dragging)
			{
				xtarget.style.zIndex=1;
				xtarget.dragging=false;
				if(xtarget.moved)
					send_imgpos(xtarget);
				else
					clickproc(ev);
			}
		}
		
		function ev2xtarget(ev)
		{
			xtarget = ev.currentTarget;
			while(xtarget.tagName != "DIV" && xtarget.parentElement)
				xtarget = xtarget.parentElement;
			return xtarget;
		}
		
		function clickproc(ev)
		{
			//alert("Clickproc");
			ttarget = ev.target;
			while(ttarget.tagName != "TABLE" && ttarget.parentElement)
				ttarget = ttarget.parentElement;
			if(ttarget.tagName == "TABLE")
			{
				if(ttarget.className == "tablestudtab")
					enlarge(ev);
				else
				{
					//alert(currentAssignImage);
					if(currentAssignImage == 0)
					{
						imgConn = new XHConn(ev2xtarget(ev));
						if (!imgConn) alert("XMLHTTP not available. Try a newer/better browser.");			
						imgConn.connect("datahandler.php", "POST", "studentguiorientation="+ev2xtarget(ev).id, imgconnDone);
					}
					else
						placeImage(ev2xtarget(ev));
				}
			}
		}
		
		function placeImage(target)
		{
			sid=target.id;
			imgSpots = target.getElementsByClassName("assImg");
			placed=false;
			j=1;
			for(i=0; i<imgSpots.length; i++)
			{
					if(placed==false)
					{
						if(imgSpots[i].innerHTML=="&nbsp;" && (typeof mentorImgs[sid] == 'undefined' || isNaN(mentorImgs[sid][currentAssignImage])) && (typeof ownImgs[sid] == 'undefined' || isNaN(ownImgs[sid][currentAssignImage])))
						{ // Icon is not present yet for this student
							imgSpots[i].innerHTML="<IMG src='Library.php?DownloadFile="+currentAssignImage+"' width='11' height='11'>";
							placed=true;
							if(typeof ownImgs[sid] == 'undefined')
								ownImgs[sid] = new Array();
							ownImgs[sid][currentAssignImage]=j;
							// Here we should send to server...
							imgConn = new XHConn(imgSpots[i]);
							if (!imgConn) alert("XMLHTTP not available. Try a newer/better browser.");			
							imgConn.connect("datahandler.php", "POST", "addguibadge="+sid+","+tid+","+currentAssignImage, imgconnDone);
						}
						else
						{ // Maybe we need to delete the icon
							if(typeof ownImgs[sid] != 'undefined' && !isNaN(ownImgs[sid][currentAssignImage]) && ownImgs[sid][currentAssignImage]==j)
							{
								//alert("Clearing image "+j);
								imgSpots[i].innerHTML="&nbsp;";
								placed=true;
								delete ownImgs[sid][currentAssignImage];
								imgConn = new XHConn(imgSpots[i]);
								if (!imgConn) alert("XMLHTTP not available. Try a newer/better browser.");			
								imgConn.connect("datahandler.php", "POST", "delguibadge="+sid+","+tid+","+currentAssignImage, imgconnDone);
							}
						}
					}
					j++;
			}
		}
		
		function enlarge(ev)
		{
			xtarget = ev2xtarget(ev);
			if(xtarget.className == "dragme1")
				etarget = document.getElementById("enlargeb");
			if(xtarget.className == "dragme2")
				etarget = document.getElementById("enlarget");
			if(xtarget.className == "dragme3")
				etarget = document.getElementById("enlargel");
			if(xtarget.className == "dragme4")
				etarget = document.getElementById("enlarger");
			// Copy contents from xtarget to etarget
			sourceelems = xtarget.getElementsByTagName("TD");
			targetelems = etarget.getElementsByTagName("TD");
			for(i=0; i<sourceelems.length; i++)
			{
				if(sourceelems[i].id.substr(0,7) != "resicon")
					targetelems[i].innerHTML = sourceelems[i].innerHTML;
				if(sourceelems[i].className == "nobell")
					targetelems[i].className="largenobell";
				if(sourceelems[i].className == "bell")
					targetelems[i].className="largebell";				
			}
			sstab = xtarget.getElementsByClassName("tablestudtab");
			tstab = etarget.getElementsByClassName("largetablestudtab");
			tstab[0].style.backgroundImage = sstab[0].style.backgroundImage;
			etarget.style.display='block';
			timgs = etarget.getElementsByTagName("IMG");
			for(i=0; i<timgs.length; i++)
			{
				timgs[i].width = timgs[i].width * 3;
				timgs[i].height = timgs[i].height * 3;
			}
			// Find out sid
			for(i=0; i<sourceelems.length; i++)
			{
				if(sourceelems[i].id.substr(0,7) == "resicon")
					sid=sourceelems[i].id.substr(7);
			}
			if(xtarget.className == "dragme1")
				paintLargeCanvas('ECANVASb',sid);
			if(xtarget.className == "dragme2")
				paintLargeCanvas('ECANVASt',sid);
			if(xtarget.className == "dragme3")
				paintLargeCanvas('ECANVASl',sid);
			if(xtarget.className == "dragme4")
				paintLargeCanvas('ECANVASr',sid);			
		}
		
		function show_emails(sid)
		{
			ib = document.getElementById('infoblock');
			ib.innerHTML = emailHTML[sid];
			ib.style.display = 'block';
		}

		function show_tels(sid)
		{
			ib = document.getElementById('infoblock');
			ib.innerHTML = telHTML[sid];
			ib.style.display = 'block';
		}
		</script>


		<?


	  // Create an image for each student
	  $poscnt = 0;
	  echo("<DIV id='div1'>");
	  echo("<p style='width: 900px;'>");

		// Create hidden enlarged versions for the student info
		// Large version with table at bottom
		
		echo("<DIV ID=enlargeb onMouseleave=\"this.style.display='none';\">");
		echo("<TABLE class=largetablestudtab><TR><TD class=largementcode></td><TD class=largestudname></td><TD class=largebell></td></tr>");
		echo("<TR><TD rowspan=4 class=largementcode></td><TD rowspan=6 class=largestudimg>");
		echo("</td><td class=largecell></td></tr>");
		echo("<tr><td class=largecell></td></tr>");
		echo("<tr><td class=largecell></td></tr>");
		echo("<tr><td class=largecell></td></tr>");
		echo("<tr><td class=largementcode></td><td class=largecell></td></tr>");
		echo("<tr><td class=largementcode></td><td class=largecell></td></tr>");	 
		echo("</table>");

		echo("<TABLE class=largetablebtable><TR><TD></td><TD></td><TD></td><TD></td></tr>");
		echo("<TR><TD></td><TD COLSPAN=2 ROWSPAN=2><CANVAS ID=ECANVASb WIDTH=54 HEIGHT=78></td><TD></td></tr>");
		echo("<TR><TD></td><TD></td></tr>");
		echo("<TR><TD colspan=2></td><TD COLSPAN=2></td>");
		echo("</table>");
	 
		echo("</div>");

		// Enlarged version with table at top
		
		echo("<DIV ID=enlarget onMouseleave=\"this.style.display='none';\">");		 
		echo("<TABLE class=largetablebtable><TR><TD></td><TD></td><TD></td><TD></td></tr>");
		echo("<TR><TD></td><TD COLSPAN=2 ROWSPAN=2><CANVAS ID=ECANVASt WIDTH=54 HEIGHT=78></td><TD></td></tr>");
		echo("<TR><TD></td><TD></td></tr>");
		echo("<TR><TD colspan=2></td><TD COLSPAN=2></td>");
		echo("</table>");

		echo("<TABLE class=largetablestudtab><TR><TD class=largementcode></td><TD class=largestudname></td><TD class=largebell></td></tr>");
		echo("<TR><TD rowspan=4 class=largementcode></td><TD rowspan=6 class=largestudimg>");
		echo("</td><td class=largecell></td></tr>");
		echo("<tr><td class=largecell></td></tr>");
		echo("<tr><td class=largecell></td></tr>");
		echo("<tr><td class=largecell></td></tr>");
		echo("<tr><td class=largementcode></td><td class=largecell></td></tr>");
		echo("<tr><td class=largementcode></td><td class=largecell></td></tr>");		 
		echo("</table>");
	 
		echo("</div>");
		
		// Enlarged version with table left
		
		echo("<DIV ID=enlargel onMouseleave=\"this.style.display='none';\">");
	 
		echo("<TABLE class=largetableatable style='float: left;'><TR><TD></td><TD></td><TD></td><TD></td></tr>");
		echo("<TR><TD></td><TD ROWSPAN=2 COLSPAN=2><CANVAS ID=ECANVASl WIDTH=54 HEIGHT=78></td><TD></td></tr>");
		echo("<TR><TD></td><TD></td></tr>");
		echo("<TR><TD colspan=4></td></tr><TR><TD COLSPAN=4></td>");
		echo("</table>");

		echo("<TABLE class=largetablestudtab><TR><TD class=largementcode></td><TD class=largestudname></td><TD class=largebell></td></tr>");
		echo("<TR><TD rowspan=4 class=largementcode></td><TD rowspan=6 class=largestudimg>");
		echo("</td><td class=largecell></td></tr>");
		echo("<tr><td class=largecell></td></tr>");
		echo("<tr><td class=largecell></td></tr>");
		echo("<tr><td class=largecell></td></tr>");
		echo("<tr><td class=largementcode></td><td class=largecell></td></tr>");
		echo("<tr><td class=largementcode></td><td class=largecell></td></tr>");			 
		echo("</table>");
	 
		echo("</div>");

		// Enlarged version with table right
		
		echo("<DIV ID=enlarger onMouseleave=\"this.style.display='none';\">");
	 
		echo("<TABLE class=largetableatable style='float: right;'><TR><TD></td><TD></td><TD></td><TD></td></tr>");
		echo("<TR><TD></td><TD ROWSPAN=2 COLSPAN=2><CANVAS ID=ECANVASr WIDTH=54 HEIGHT=78></td><TD></td></tr>");
		echo("<TR><TD></td><TD></td></tr>");
		echo("<TR><TD colspan=4></td></tr><TR><TD COLSPAN=4></td>");
		echo("</table>");

		echo("<TABLE class=largetablestudtab><TR><TD class=largementcode></td><TD class=largestudname></td><TD class=largebell></td></tr>");
		echo("<TR><TD rowspan=4 class=largementcode></td><TD rowspan=6 class=studimg>");
		echo("</td><td class=largecell></td></tr>");
		echo("<tr><td class=largecell></td></tr>");
		echo("<tr><td class=largecell></td></tr>");
		echo("<tr><td class=largecell></td></tr>");
		echo("<tr><td class=largementcode></td><td class=largecell></td></tr>");
		echo("<tr><td class=largementcode></td><td class=largecell></td></tr>");		 
		echo("</table>");
	 
		echo("</div>");
		
		// Info block for email and telephone
		echo("<DIV class=infoblock ID=infoblock onMouseleave=\"this.style.display='none'\"></div>");
		
		
		echo("\r\n<SCRIPT> var resultpass = new Array(); var colorgen = new Array(); var colorsub = new Array(); var checkImg = new Image(); checkImg.src='PNG/action_check.png'; var emailHTML = new Array(); telHTML = new Array(); var currentAssignImage=0; var ownImgs = new Array(); var mentorImgs = new Array(); var tid=". $_SESSION['uid']. "; </SCRIPT>\r\n");
		
		// For the queries we need to know mid, cid and gid
		if(isset($_SESSION['CurrentSubject']))
			$classqr = inputclassbase::load_query("SELECT mid,gid,cid FROM class LEFT JOIN sgroup USING(gid) WHERE mid=". $_SESSION['CurrentSubject']. " AND groupname='". $_SESSION['CurrentGroup']. "'");
		if(isset($classqr['mid']))
		{
			$qmid = $classqr['mid'][0];
			$qgid = $classqr['gid'][0];
			$qcid = $classqr['cid'][0];
		}
		else
		{
			$qmid=0; $qgid=0; $qcid=0;
		}
		
		// Show the students
	  foreach($students['sid'] AS $six => $sid)
	  {
			$stud = new student($sid);
			// First get the values to fill in the various spots.
			$sdata['deal'] = "D<BR>E<BR>A<BR>L";
			$i=1;
			$sdata['abs1'] = "";
			$sdata['abs2'] = "";
			$sdata['abs3'] = "";
			$sdata['abs4'] = "";
			$sdata['abs5'] = "";
			$sdata['abs6'] = "";
			$absent=false;
			if(isset($this->acatlist))
				foreach($this->acatlist AS $aacat)
				{
					if($stud->get_absstate($aacat))
						$absent=true;
					
					$absr = absence::get_abs_record($stud,$aacat,isset($this->adate) ? date("Y-m-d",$this->adate) : NULL);
					if(isset($absr))
						$sdata["abs". $i] = $absr->add_hidden_edit_dialog_link();
					else
						$sdata["abs". $i] = absence::add_hidden_add_dialog_link($stud,$aacat,isset($this->adate) ? $this->adate : NULL);
					
					$i++;
				}
			$sdata['mentcode'] = $stud->get_mentor_code();
			$sdata['tab1'] = "&nbsp;";
			$sdata['tab2'] = "&nbsp;";
			$sdata['tab3'] = "&nbsp;";
			$sdata['tab4'] = "&nbsp;";
			$sdata['tab5'] = "&nbsp;";
			$sdata['tab6'] = "&nbsp;";
			$sdata['tab7'] = "&nbsp;";
			$sdata['tab8'] = "&nbsp;";
			$sdata['results'] = "<CANVAS ID=SCANVAS". $sid. " WIDTH=18 HEIGHT=26>";
			$sdata['aplan'] = $this->simple_gui_query(7,$sid,$qmid,$qgid,$qcid);
			$sdata['catchup'] = $this->simple_gui_query(6,$sid,$qmid,$qgid,$qcid);
			// Set javascript var for result canvas drawing
			$passgen = $this->simple_gui_query(2,$sid);
			$passsub = $this->simple_gui_query(3,$sid,(isset($_SESSION['CurrentSubject']) ? $_SESSION['CurrentSubject'] : 0));
			$colorgen = $this->simple_gui_query(4,$sid);
			$colorsub = $this->simple_gui_query(5,$sid,(isset($_SESSION['CurrentSubject']) ? $_SESSION['CurrentSubject'] : 0));
			echo("<SCRIPT>\r\n resultpass[". $sid. "]=". ($passgen > 0 && $passsub > 0 ? 1 : 0). "; ");
			echo("\r\n colorgen[". $sid. "]='". $colorgen. "'; ");
			echo("\r\n colorsub[". $sid. "]='". $colorsub. "'; </SCRIPT>\r\n");
			$emailimg = "<img src='PNG/letter.png' HEIGHT=8 onClick='show_emails(". $sid. ");'>";
			$telimg = "<img src='PNG/phone-icon.png' HEIGHT=8 onClick='show_tels(". $sid. ");'>";
			$showbell=$this->simple_gui_query(1,$sid);
			if(!isset($showbell))
				$showbell=0;
			$emaildata = $this->full_gui_query(8,$sid);
			if(isset($emaildata['label']))
			{
				$emailHTML = "<table>";
				foreach($emaildata['label'] AS $dix => $xlab)
					$emailHTML .= "<TR><TD>". $xlab. ":</td><TD> ". $emaildata['data'][$dix]. "</td></tr>";
				$emailHTML .= "</table>";
			}
			else
				$emailHTML = $_SESSION['dtext']['No_data'];
			echo("<SCRIPT> emailHTML[". $sid. "] = '". $emailHTML. "'; </SCRIPT>");
			$teldata = $this->full_gui_query(9,$sid);
			if(isset($teldata['label']))
			{
				$telHTML = "<table>";
				foreach($teldata['label'] AS $dix => $xlab)
					$telHTML .= "<TR><TD>". $xlab. ":</td><TD> ". $teldata['data'][$dix]. "</td></tr>";
				$telHTML .= "</table>";
			}
			else
				$telHTML = $_SESSION['dtext']['No_data'];
			echo("<SCRIPT> telHTML[". $sid. "] = '". $telHTML. "'; </SCRIPT>");
			// Get the badges
			$prgrp = $stud->get_primary_group();
			if(!isset($prgrp))
				$mentorid=0;
			else
			{
				$mentor = $stud->get_primary_group()->get_mentor();
				if(!isset($mentor))
					$mentorid=0;
				else
					$mentorid = $stud->get_primary_group()->get_mentor()->get_id();
			}
			$j=1;
			if($_SESSION['uid'] != $mentorid)
			{ // Get mentor badges
				$badgesqr = inputclassbase::load_query("SELECT * FROM guibadges WHERE sid=". $sid. " AND tid=". $mentorid);
				if(isset($badgesqr['libid']))
				{ // Badges are present, set javascript var and php var to display it
					foreach($badgesqr['libid'] AS $alibid)
					{
						$sdata["tab". $j] = "<IMG  src='Library.php?DownloadFile=". $alibid. "' width='11' height='11'>";
						echo("<SCRIPT> if(typeof mentorImgs[". $sid. "] == 'undefined') mentorImgs[". $sid. "] = new Array(); mentorImgs[". $sid. "][". $alibid. "]=". $j. "; </script>");
						$j++;
					}
				}
			}
			// Add own badges
			$badgesqr = inputclassbase::load_query("SELECT * FROM guibadges WHERE sid=". $sid. " AND tid=". $_SESSION['uid']);
			if(isset($badgesqr['libid']))
			{ // Badges are present, set javascript var and php var to display it
				foreach($badgesqr['libid'] AS $alibid)
				{
					$sdata["tab". $j] = "<IMG  src='Library.php?DownloadFile=". $alibid. "' width='11' height='11'>";
					echo("\r\n<SCRIPT> if(typeof ownImgs[". $sid. "] == 'undefined') ownImgs[". $sid. "] = new Array(); ownImgs[". $sid. "][". $alibid. "]=". $j. "; </script>\r\n");
					$j++;
				}
			}
			
			// how student and table
			if(!isset($imgloc[$sid]) || $imgloc[$sid]['orientation'] == "b")
			{ // Table at bottom
			
				echo("<DIV class=dragme1 ID=". $sid. " onmousedown='dragstart(event)' 
				onmouseup='dragend(event)' onmouseout='dragend(event)' onmousemove='dragmove(event)' ontouchstart='touchstart(event)' ontouchmove='touchmove(event)' ontouchend='touchend(event)'>");
			 
				echo("<TABLE class=tablestudtab style='background-image : url(\"". ($students['imgname'][$six] != "" ? $livepictures. $students['imgname'][$six] : "PNG/user.png"). "\"); background-repeat: no-repeat; background-size: 70px 80px; background-position: center center; ". ($absent ? " opacity: 0.4;" : ""). "'><TR><TD class=mentcode>". $sdata['mentcode']. "</td><TD class=studname>". $students['name'][$six]. "</td><TD class=". ($showbell>0 ? "bell" : "nobell"). "> </td></tr>");
				echo("<TR><TD rowspan=4 class=mentcode>". $sdata['deal']. "</td><TD rowspan=6 class=studimg>");
				echo("&nbsp;&nbsp;");
				echo("</td><td class=normalcell>". $sdata['abs1']. "</td></tr>");
				echo("<tr><td class=normalcell>". $sdata['abs2']. "</td></tr>");
				echo("<tr><td class=normalcell>". $sdata['abs3']. "</td></tr>");
				echo("<tr><td class=normalcell>". $sdata['abs4']. "</td></tr>");
				echo("<tr><td class=mentcode>". $emailimg. "</td><td class=normalcell>". $sdata['abs5']. "</td></tr>");
				echo("<tr><td class=mentcode>". $telimg. "</td><td class=normalcell>". $sdata['abs6']. "</td></tr>");
			 
				echo("</table>");

				echo("<TABLE class=tablebtable><TR><TD class=assImg>". $sdata['tab1']. "</td><TD class=assImg>". $sdata['tab2']. "</td><TD class=assImg>". $sdata['tab3']. "</td><TD class=assImg>". $sdata['tab4']. "</td></tr>");
				echo("<TR><TD class=assImg>". $sdata['tab5']. "</td><td ID='resicon". $sid. "' COLSPAN=2 ROWSPAN=2>". $sdata['results']. "</td><TD class=assImg>". $sdata['tab6']. "</td></tr>");
				echo("<TR><TD class=assImg>". $sdata['tab7']. "</td><TD class=assImg>". $sdata['tab8']. "</td></tr>");
				echo("<TR><TD colspan=2>". $sdata['aplan']. "</td><TD COLSPAN=2>". $sdata['catchup']. "</td>");
				echo("</table>");
			 
				echo("</div>");
			}
			else if($imgloc[$sid]['orientation'] == "t")
			{ // Table at top
			
				echo("<DIV class=dragme2 ID=". $sid. " onmousedown='dragstart(event)' 
				onmouseup='dragend(event)' onmouseout='dragend(event)' onmousemove='dragmove(event)' ontouchstart='touchstart(event)' ontouchmove='touchmove(event)' ontouchend='touchend(event)'>");
			 
				echo("<TABLE class=tablebtable><TR><TD class=assImg>". $sdata['tab1']. "</td><TD class=assImg>". $sdata['tab2']. "</td><TD class=assImg>". $sdata['tab3']. "</td><TD class=assImg>". $sdata['tab4']. "</td></tr>");
				echo("<TR><TD class=assImg>". $sdata['tab5']. "</td><td ID='resicon". $sid. "' COLSPAN=2 ROWSPAN=2>". $sdata['results']. "</td><TD class=assImg>". $sdata['tab6']. "</td></tr>");
				echo("<TR><TD class=assImg>". $sdata['tab7']. "</td><TD class=assImg>". $sdata['tab8']. "</td></tr>");
				echo("<TR><TD colspan=2>". $sdata['aplan']. "</td><TD COLSPAN=2>". $sdata['catchup']. "</td>");
				echo("</table>");

				echo("<TABLE class=tablestudtab style='background-image : url(\"". ($students['imgname'][$six] != "" ? $livepictures. $students['imgname'][$six] : "PNG/user.png"). "\"); background-repeat: no-repeat; background-size: 70px 80px; background-position: center center; ". ($absent ? " opacity: 0.4;" : ""). "'><TR><TD class=mentcode>". $sdata['mentcode']. "</td><TD class=studname>". $students['name'][$six]. "</td><TD class=". ($showbell>0 ? "bell" : "nobell"). "> </td></tr>");
				echo("<TR><TD rowspan=4 class=mentcode>". $sdata['deal']. "</td><TD rowspan=6 class=studimg>");
				echo("&nbsp;&nbsp;");
				echo("</td><td class=normalcell>". $sdata['abs1']. "</td></tr>");
				echo("<tr><td class=normalcell>". $sdata['abs2']. "</td></tr>");
				echo("<tr><td class=normalcell>". $sdata['abs3']. "</td></tr>");
				echo("<tr><td class=normalcell>". $sdata['abs4']. "</td></tr>");
				echo("<tr><td class=mentcode>". $emailimg. "</td><td class=normalcell>". $sdata['abs5']. "</td></tr>");
				echo("<tr><td class=mentcode>". $telimg. "</td><td class=normalcell>". $sdata['abs6']. "</td></tr>");
			 
				echo("</table>");

			 
				echo("</div>");
			}
			else if($imgloc[$sid]['orientation'] == "l")
			{ // Table left
			
				echo("<DIV class=dragme3 ID=". $sid. " onmousedown='dragstart(event)' 
				onmouseup='dragend(event)' onmouseout='dragend(event)' onmousemove='dragmove(event)' ontouchstart='touchstart(event)' ontouchmove='touchmove(event)' ontouchend='touchend(event)'>");
			 
				echo("<TABLE class=tableatable style='float: left;'><TR><TD class=assImg>". $sdata['tab1']. "</td><TD class=assImg>". $sdata['tab2']. "</td><TD class=assImg>". $sdata['tab3']. "</td><TD class=assImg>". $sdata['tab4']. "</td></tr>");
				echo("<TR><TD class=assImg>". $sdata['tab5']. "</td><td ID='resicon". $sid. "' COLSPAN=2 ROWSPAN=2>". $sdata['results']. "</td><TD class=assImg>". $sdata['tab6']. "</td></tr>");
				echo("<TR><TD class=assImg>". $sdata['tab7']. "</td><TD class=assImg>". $sdata['tab8']. "</td></tr>");
				echo("<TR><TD colspan=4>". $sdata['aplan']. "</td></tr><TR><TD COLSPAN=4>". $sdata['catchup']. "</td>");
				echo("</table>");

				echo("<TABLE class=tablestudtab style='background-image : url(\"". ($students['imgname'][$six] != "" ? $livepictures. $students['imgname'][$six] : "PNG/user.png"). "\"); background-repeat: no-repeat; background-size: 70px 80px; background-position: center center; ". ($absent ? " opacity: 0.4;" : ""). "'><TR><TD class=mentcode>". $sdata['mentcode']. "</td><TD class=studname>". $students['name'][$six]. "</td><TD class=". ($showbell>0 ? "bell" : "nobell"). "> </td></tr>");
				echo("<TR><TD rowspan=4 class=mentcode>". $sdata['deal']. "</td><TD rowspan=6 class=studimg>");
				echo("&nbsp;&nbsp;");
				echo("</td><td class=normalcell>". $sdata['abs1']. "</td></tr>");
				echo("<tr><td class=normalcell>". $sdata['abs2']. "</td></tr>");
				echo("<tr><td class=normalcell>". $sdata['abs3']. "</td></tr>");
				echo("<tr><td class=normalcell>". $sdata['abs4']. "</td></tr>");
				echo("<tr><td class=mentcode>". $emailimg. "</td><td class=normalcell>". $sdata['abs5']. "</td></tr>");
				echo("<tr><td class=mentcode>". $telimg. "</td><td class=normalcell>". $sdata['abs6']. "</td></tr>");
			 
				echo("</table>");

			 
				echo("</div>");
			}
			else if($imgloc[$sid]['orientation'] == "r")
			{ // Table right
			
				echo("<DIV class=dragme4 ID=". $sid. " onmousedown='dragstart(event)' 
				onmouseup='dragend(event)' onmouseout='dragend(event)' onmousemove='dragmove(event)' ontouchstart='touchstart(event)' ontouchmove='touchmove(event)' ontouchend='touchend(event)'>");
			 
				echo("<TABLE class=tableatable style='float: right;'><TR><TD class=assImg>". $sdata['tab1']. "</td><TD class=assImg>". $sdata['tab2']. "</td><TD class=assImg>". $sdata['tab3']. "</td><TD class=assImg>". $sdata['tab4']. "</td></tr>");
				echo("<TR><TD class=assImg>". $sdata['tab5']. "</td><td ID='resicon". $sid. "' COLSPAN=2 ROWSPAN=2>". $sdata['results']. "</td><TD class=assImg>". $sdata['tab6']. "</td></tr>");
				echo("<TR><TD class=assImg>". $sdata['tab7']. "</td><TD class=assImg>". $sdata['tab8']. "</td></tr>");
				echo("<TR><TD colspan=4>". $sdata['aplan']. "</td></tr><TR><TD COLSPAN=4>". $sdata['catchup']. "</td>");
				echo("</table>");

				echo("<TABLE class=tablestudtab style='background-image : url(\"". ($students['imgname'][$six] != "" ? $livepictures. $students['imgname'][$six] : "PNG/user.png"). "\"); background-repeat: no-repeat; background-size: 70px 80px; background-position: center center; ". ($absent ? " opacity: 0.4;" : ""). "'><TR><TD class=mentcode>". $sdata['mentcode']. "</td><TD class=studname>". $students['name'][$six]. "</td><TD class=". ($showbell>0 ? "bell" : "nobell"). "> </td></tr>");
				echo("<TR><TD rowspan=4 class=mentcode>". $sdata['deal']. "</td><TD rowspan=6 class=studimg>");
				echo("&nbsp;&nbsp;");
				echo("</td><td class=normalcell>". $sdata['abs1']. "</td></tr>");
				echo("<tr><td class=normalcell>". $sdata['abs2']. "</td></tr>");
				echo("<tr><td class=normalcell>". $sdata['abs3']. "</td></tr>");
				echo("<tr><td class=normalcell>". $sdata['abs4']. "</td></tr>");
				echo("<tr><td class=mentcode>". $emailimg. "</td><td class=normalcell>". $sdata['abs5']. "</td></tr>");
				echo("<tr><td class=mentcode>". $telimg. "</td><td class=normalcell>". $sdata['abs6']. "</td></tr>");
			 
				echo("</table>");

			 
				echo("</div>");
			}
		
			 
			$poscnt++;
			if($poscnt % 8 == 0)
				echo("</p><p style='width: 900px;'>");
	  }
	  echo("</div>");

		// Ajax type scripting to send changed positions
		?>
		<SCRIPT>
		<?
			require_once("inputlib/xhconn.js");
		?>
		var AjaxPending=0;
		function send_imgpos(imgobj)
		{
			imgConn = new XHConn(imgobj);
			if (!imgConn) alert("XMLHTTP not available. Try a newer/better browser.");
			imgConn.connect("datahandler.php", "POST", "studentguilocation="+imgobj.id+"&xoff="+parseInt(imgobj.style.left+0)+"&yoff="+parseInt(imgobj.style.top+0), imgconnDone);
		}
		function imgconnDone(oXML,imgobj)
		{
			if(oXML.responseText.substring(0,2) != "OK" && typeof oXML.responseText != "undefined")
				alert(oXML.responseText);  
			if(oXML.responseText.substr(oXML.responseText.length - 7) == "REFRESH")
				document.location.reload(true);
		}
		document.getElementById("header").style.display='none';
		document.getElementById("menudivh").style.top='0px';
		function move_imgs()
		{
		<?
			if(isset($imgloc))
			{
				foreach($imgloc AS $imgsid => $imgxy)
				{
					echo("document.getElementById('". $imgsid. "').style.left='". $imgxy['x']. "px'; ");
					echo("document.getElementById('". $imgsid. "').style.top='". $imgxy['y']. "px'; \r\n");
					
				}
			}
			//echo("var stImgs = document.getElementsByClassName('dragme'); ");
			//echo("var xoff=parseInt(window.pageXOffset); var yoff=parseInt(window.pageYOffset); ");
			//echo("for(i=0; i<stImgs.length; i++) { ");
			//echo("var stName = document.createElement(\"DIV\"); stName.className='caption'; var title = stImgs[i].getAttribute('title'); stName.innerHTML=title.replace(', ','<BR>');  stImgs[i].parentNode.insertBefore(stName,stImgs[i]); ");
			//echo("stName.style.left=(xoff + stImgs[i].getBoundingClientRect().left) + 'px';  stName.style.top=(yoff + stImgs[i].getBoundingClientRect().bottom) + 'px'; }\r\n");
		?>
		}
		setTimeout('window.addEventListener("load",move_imgs());',500);
		
		// Now fill the result canvasses
		function paintResultCanvas(element,index,array)
		{
			var canvas = document.getElementById('SCANVAS' + index);
			if (canvas.getContext)
			{
				var ctx = canvas.getContext('2d');
				ctx.beginPath();
				ctx.fillStyle=colorsub[index];
				ctx.arc(9,12,9,Math.PI/2,Math.PI+Math.PI/2,true);
				ctx.fill();
				ctx.beginPath();
				ctx.fillStyle=colorgen[index];
				ctx.arc(9,12,9,Math.PI/2,Math.PI+Math.PI/2,false);
				ctx.fill();
				if(resultpass[index] > 0)
					ctx.drawImage(checkImg,1,3);
			}	  			
		}

		resultpass.forEach(paintResultCanvas);

		function paintLargeCanvas(canvasid,sid)
		{
			var canvas = document.getElementById(canvasid);
			if (canvas.getContext)
			{
				var ctx = canvas.getContext('2d');
				ctx.beginPath();
				ctx.fillStyle=colorsub[sid];
				ctx.arc(27,36,27,Math.PI/2,Math.PI+Math.PI/2,true);
				ctx.fill();
				ctx.beginPath();
				ctx.fillStyle=colorgen[sid];
				ctx.arc(27,36,27,Math.PI/2,Math.PI+Math.PI/2,false);
				ctx.fill();
				if(resultpass[sid] > 0)
					ctx.drawImage(checkImg,3,9,48,48);
			}	 			
		}
		
		function activateImage(imgid)
		{
			imgdiv = document.getElementById("assignimages");
			aimgs = imgdiv.getElementsByTagName("IMG");
			for(i=0; i<aimgs.length; i++)
			{
				aimgs[i].style.border='none';
			}
			activeimg = document.getElementById("aimg"+imgid);
			activeimg.style.border='2px solid red';
			currentAssignImage=imgid;
		}
		</SCRIPT>
		<?
		echo("<DIV ID=assignimages>");
		// Put the image for rotation
		echo("<IMG SRC='PNG/rotation.png' width=32 height=32 ID=aimg0 style='border: 2px solid red;' onClick='activateImage(0);'>");
		// Put the assignable images
		$asigimgpath = inputclassbase::load_query("SELECT query FROM guiqueries WHERE qname=9999");
		if(isset($asigimgpath['query']))
		{
			$asigimgids = inputclassbase::load_query("SELECT libid FROM libraryfiles WHERE folder='". $asigimgpath['query'][0]. "' AND type LIKE 'image%'");
			if(isset($asigimgids['libid']))
			{
				foreach($asigimgids['libid'] AS $imglibid)
				{
					echo("<IMG SRC='Library.php?DownloadFile=". $imglibid. "' width='32' height='32' ID=aimg". $imglibid. " onClick='activateImage(". $imglibid. ");'>");
				}
			}
		}
		echo("</div>");
		
		// Blackboard

		echo("<DIV ID=blackboard>");

		if(!isset($this->curmid))
		{ // If no current subject from classbook/agenda, show a subject selector
			$this->subselbox->show();
		}
		else
		{ // Show current timeslot with times
			echo("<SPAN style='float: right;'>". $_SESSION['dtext']["Timeslot"]. " ". $this->CurrentTimeslot. " : ". $this->CurrentStarttime. " - ". $this->CurrentEndtime. "</span>");
			
		}

		// Now for the texts
		$tqr = $this->full_gui_query(10,0,$qmid,$qgid,$qcid);
		if(isset($tqr['label']))
			foreach($tqr['label'] AS $qix => $rlabel)
				echo("<P class=bblinew>". $rlabel. ": ". $tqr['data'][$qix]. "</p>");
		$tqr = $this->full_gui_query(11,0,$qmid,$qgid,$qcid);
		if(isset($tqr['label']))
			foreach($tqr['label'] AS $qix => $rlabel)
				echo("<P class=bbliney>". $rlabel. ": ". $tqr['data'][$qix]. "</p>");
		$tqr = $this->full_gui_query(12,0,$qmid,$qgid,$qcid);
		if(isset($tqr['label']))
			foreach($tqr['label'] AS $qix => $rlabel)
				echo("<P class=bbliner>". $rlabel. ": ". $tqr['data'][$qix]. "</p>");
		$tqr = $this->full_gui_query(13,0,$qmid,$qgid,$qcid);
		if(isset($tqr['label']))
			foreach($tqr['label'] AS $qix => $rlabel)
				echo("<P class=bblineg>". $rlabel. ": ". $tqr['data'][$qix]. "</p>");
		//echo("Dit wordt het blackboard");
		echo("</div>");
		require_once("absscripts.php");
  } 

	protected function full_gui_query($qnum,$sid,$mid=0,$gid=0,$cid=0)
	{
		$qdefqr = inputclassbase::load_query("SELECT query FROM guiqueries WHERE qname=". $qnum);
		if(isset($qdefqr['query'][0]))
		{
			$qdef = $qdefqr['query'][0];
			$qdef = str_replace("{{sid}}",$sid,$qdef);
			if($mid > 0)
				$qdef = str_replace("{{mid}}",$mid,$qdef);
			else
				$qdef = str_replace("{{mid}}","'%'",$qdef);
			$qdef = str_replace("{{gid}}",$gid,$qdef);
			$qdef = str_replace("{{cid}}",$cid,$qdef);
			$curyrqr = inputclassbase::load_query("SELECT year FROM period");
			$curyr = $curyrqr['year'][0];
			$qdef = str_replace("{{year}}",$curyr,$qdef);
			return(inputclassbase::load_query($qdef));
		}	
		else
			return NULL;
	}
	protected function simple_gui_query($qnum,$sid,$mid=0,$gid=0,$cid=0)
	{
		$qres = $this->full_gui_query($qnum,$sid,$mid,$gid,$cid);
		if(isset($qres['result'][0]))
			return $qres['result'][0];
		else
			return NULL;
	}
}
?>
