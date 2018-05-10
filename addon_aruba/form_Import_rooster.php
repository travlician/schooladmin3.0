<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014  Aim4Me N.V.  (http://www.aim4me.info)       |
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
// | Authors: Wilfred van Weert  -  travlician@bigfoot.com                |
// +----------------------------------------------------------------------+
//
session_start();

//$login_qualify = 'A';
require_once("schooladminfunctions.php");

if(isset($_POST['denyconfirm']))
{
	unset($_SESSION['roosterxmlcontent']);
	unset($_SESSION['teachermatches']);
	unset($_SESSION['subjectmatches']);
	unset($_SESSION['groupmatches']);
	//echo("Cleared session xml content.<BR>");
}

if(isset($_POST['roostername']))
	$_SESSION['roostername'] = $_POST['roostername'];
if(!isset($_SESSION['roostername']))
	$_SESSION['roostername'] = "Rooster";

if(isset($_FILES['roosterfile']))
{
  $orgfile = explode(".",$_FILES['roosterfile']['name']);
	//$roosterxml = simplexml_load_file($_FILES['roosterfile']['tmp_name']);
  $xmlcontent = file_get_contents($_FILES['roosterfile']['tmp_name']);
	$_SESSION['roosterxmlcontent'] = $xmlcontent;
  //echo("Should process file here<BR>");
}
else
{
	if(isset($_SESSION['roosterxmlcontent']))
		$xmlcontent = $_SESSION['roosterxmlcontent'];
}
if(isset($xmlcontent))
{
	$roosterxml = new SimpleXMLElement($xmlcontent);
	if(isset($roosterxml['ascttversion']))
		$rooster = new ASCrooster($roosterxml);
	else if(isset($roosterxml->Teacher[0]))
		$rooster = new FETrooster($roosterxml);
	else
		echo("Het bestand bevat geen geldig rooster formaat<BR>");
}


if(isset($rooster))
{
	//$_SESSION['importroosterobj'] = $rooster;
	if(isset($_POST))
	{
		foreach($_POST AS $pkey => $pval)
		{
			if(substr($pkey,0,2) == "tm")
			{
				$rooster->match_teacher(substr(str_replace("_"," ",$pkey),2),$pval);
			}
			if(substr($pkey,0,2) == "sm")
			{
				$rooster->match_subject(substr(str_replace("_"," ",$pkey),2),$pval);
			}
			if(substr($pkey,0,2) == "gm")
			{
				$rooster->match_group(substr(str_replace("_"," ",$pkey),2),$pval);
			}
		}
	}
	$rooster->show_version();
	$teachers = $rooster->list_teachers();
	$subjects = $rooster->list_subjects();
	$groups = $rooster->list_groups();
	$activities = $rooster->get_activities();
	
/*	echo("<b>Teachers:</b><BR>");
	foreach($teachers AS $tkey => $tname)
		echo($tkey. " = ". $tname. "<BR>");
	echo("<b>Subjects:</b><BR>");
	foreach($subjects AS $tkey => $tname)
		echo($tkey. " = ". $tname. "<BR>");
	echo("<b>Groups:</b><BR>");
	foreach($groups AS $tkey => $tname)
		echo($tkey. " = ". $tname. "<BR>");
	echo("<b>Activities:</b><BR>");
	foreach($activities AS $akey => $arec)
		echo($akey. " : ". $arec['teacher']. ",". $arec['subject']. ",". $arec['group']. ",". $arec['room']. ",". $arec['day']. ",". $arec['hour']. "<BR>"); */
	if($rooster->check_matching())
	{
		$rooster->store_DB();
	}
	else
		$rooster->do_matching();
}
else
{
	unset($_SESSION['roosterxmlcontent']);
  echo("<FORM METHOD=POST NAME=roosterform ID=roosterform ACTION=". $_SERVER['REQUEST_URI']. " ENCTYPE='multipart/form-data'>");
  echo("XML bestand met rooster (afkomstig van FET teachers of ASC): <INPUT TYPE=FILE NAME=roosterfile onChange=\"document.forms['roosterform'].submit();\"></FORM>");
}

abstract class rooster
{
	protected $teachermatches;
	protected $subjectmatches;
	protected $groupmatches;
	abstract function list_teachers();
	abstract function list_subjects();
	abstract function list_groups();
	abstract function get_activities();
	abstract function show_version();
	
	public function check_matching()
	{
		if(isset($_SESSION['teachermatches']))
			$this->teachermatches = $_SESSION['teachermatches'];
		if(isset($_SESSION['subjectmatches']))
			$this->subjectmatches = $_SESSION['subjectmatches'];
		if(isset($_SESSION['groupmatches']))
			$this->groupmatches = $_SESSION['groupmatches'];
		if(!isset($this->teachermatches))
			$this->init_teachermatches();
		if(!isset($this->subjectmatches))
			$this->init_subjectmatches();
		if(!isset($this->groupmatches))
			$this->init_groupmatches();
		$teachers = $this->list_teachers();
		foreach($teachers AS $tkey => $aTeacher)
			if($this->teachermatches[$tkey] == '')
				return false;
		$subjects = $this->list_subjects();
		foreach($subjects AS $tsub => $aSubject)
			if($this->subjectmatches[$tsub] == '')
				return false;
		$groups = $this->list_groups();
		foreach($groups AS $tgrp => $aGroup)
			if($this->groupmatches[$tgrp] == '')
				return false;
		// If we get here, all matches are set
		return true;
	}
	
	public function do_matching()
	{
		global $teachercode;
		if(!isset($this->teachermatches))
			$this->init_teachermatches();
		if(!isset($this->subjectmatches))
			$this->init_subjectmatches();
		if(!isset($this->groupmatches))
			$this->init_groupmatches();
		if(isset($teachercode))
			$lvstids = SA_loadquery("SELECT tid,data FROM ". $teachercode. " ORDER BY data");
		else
			$lvstids = SA_loadquery("SELECT tid,CONCAT(firstname, ' ', lastname) as data FROM teacher ORDER BY lastname,firstname");
		$teachers = $this->list_teachers();
		echo("<FORM METHOD=POST>Roosternaam : <INPUT NAME='roostername' VALUE='". $_SESSION['roostername']. "' TYPE=TEXT><BR>");
		echo("Koppeling leerkrachten (links is rooster, rechts is LVS):<BR>");
		foreach($teachers AS $tkey => $aTeacher)
		{
			echo($aTeacher. " : <SELECT NAME='tm". $tkey. "'><OPTION VALUE=''></OPTION><OPTION VALUE=-1". ($this->teachermatches[$tkey] == -1 ? " selected" : ""). ">Toevoegen</OPTION><OPTION VALUE=-2". ($this->teachermatches[$tkey] == -2 ? " selected" : ""). ">Negeren</OPTION>");
			foreach($lvstids['tid'] AS $ltix => $ltid)
				echo("<OPTION VALUE='". $ltid. "'". ($this->teachermatches[$tkey] == $ltid ? " selected" : ""). ">". $lvstids['data'][$ltix]. "</OPTION>"); 
			echo("</SELECT><BR>");
		}
		
		$lvsmids = SA_loadquery("SELECT mid,shortname FROM subject ORDER BY shortname");
		$subjects = $this->list_subjects();
		echo("<BR>Koppeling vakken (links is rooster, rechts is LVS):<BR><FORM METHOD=POST>");
		foreach($subjects AS $skey => $aSubject)
		{
			echo($aSubject. " : <SELECT NAME='sm". $skey. "'><OPTION VALUE=''></OPTION><OPTION VALUE=-1". ($this->subjectmatches[$skey] == -1 ? " selected" : ""). ">Toevoegen</OPTION><OPTION VALUE=-2". ($this->subjectmatches[$skey] == -2 ? " selected" : ""). ">Negeren</OPTION>");
			foreach($lvsmids['mid'] AS $lmix => $lmid)
				echo("<OPTION VALUE='". $lmid. "'". ($this->subjectmatches[$skey] == $lmid ? " selected" : ""). ">". $lvsmids['shortname'][$lmix]. "</OPTION>"); 
			echo("</SELECT><BR>");
		}

		$lvsgids = SA_loadquery("SELECT gid,groupname FROM sgroup WHERE active=1 ORDER BY groupname");
		$groups = $this->list_groups();
		echo("<BR>Klas/cluster koppeling (links is rooster, rechts is LVS):<BR><FORM METHOD=POST>");
		foreach($groups AS $gkey => $aGroup)
		{
			echo($aGroup. " : <SELECT NAME='gm". $gkey. "'><OPTION VALUE=''></OPTION><OPTION VALUE=-1". ($this->groupmatches[$gkey] == -1 ? " selected" : ""). ">Toevoegen</OPTION><OPTION VALUE=-2". ($this->groupmatches[$gkey] == -2 ? " selected" : ""). ">Negeren</OPTION>");
			foreach($lvsgids['gid'] AS $lgix => $lgid)
				echo("<OPTION VALUE='". $lgid. "'". ($this->groupmatches[$gkey] == $lgid ? " selected" : ""). ">". $lvsgids['groupname'][$lgix]. "</OPTION>"); 
			echo("</SELECT><BR>");
		}

		echo("<INPUT TYPE=SUBMIT NAME=domatch VALUE='Verder met import rooster'></FORM>");
			
	}
	
	public function match_teacher($ikey,$mkey)
	{
		global $teachercode;
		if($mkey == -1)
		{ // Teacher needs to be added
			// First find out which teacher from myteachers, as we need to use the value, not the key.
			$myteachers = $this->list_teachers();
			foreach($myteachers AS $tkey => $tval)
				if($tkey == $ikey)
					$tname = $tval;
			if(isset($tname))
			{
				mysql_query("INSERT INTO teacher (lastname) VALUES('". $tname. "')");
				echo(mysql_error());
				//echo("Added teacher by query INSERT INTO teacher (lastname) VALUES('". $tname. "')<BR>");
				$mkey = mysql_insert_id();
				if(isset($teachercode))
				{
					mysql_query("INSERT INTO `". $teachercode. "` (tid,data) VALUES(". $mkey. ",'". $tname. "')");
				}
			}
			
		}
		$this->teachermatches[$ikey] = $mkey;
		//echo("Set teachermatch $ikey $mkey <BR>");
		$_SESSION['teachermatches'] = $this->teachermatches;
	}
	
	public function match_subject($ikey,$mkey)
	{
		if($mkey == -1)
		{ // Subject needs to be added
			// First find out which subject from mysubjects, as we need to use the value, not the key.
			$mysubjects = $this->list_subjects();
			foreach($mysubjects AS $skey => $sval)
				if($skey == $ikey)
					$sname = $sval;
			if(isset($sname))
			{
				mysql_query("INSERT INTO subject (shortname,fullname) VALUES('". $sname. "','". $sname. "')");
				echo(mysql_error());
				//echo("Added teacher by query INSERT INTO teacher (lastname) VALUES('". $tname. "')<BR>");
				$mkey = mysql_insert_id();
			}
			
		}
		$this->subjectmatches[$ikey] = $mkey;
		// echo("Set subjectmatch $ikey $mkey <BR>");
		$_SESSION['subjectmatches'] = $this->subjectmatches;
	}
	
	public function match_group($ikey,$mkey)
	{
		if($mkey == -1)
		{ // Group needs to be added
			// First find out which group from mygroups, as we need to use the value, not the key.
			$mygroups = $this->list_groups();
			foreach($mygroups AS $gkey => $gval)
				if($gkey == $ikey)
					$gname = $gval;
			if(isset($gname))
			{
				mysql_query("INSERT INTO sgroup (groupname) VALUES('". $gname. "')");
				echo(mysql_error());
				//echo("Added teacher by query INSERT INTO teacher (lastname) VALUES('". $tname. "')<BR>");
				$mkey = mysql_insert_id();
			}
			
		}
		$this->groupmatches[$ikey] = $mkey;
		//echo("Set groupmatch $ikey $mkey <BR>");
		$_SESSION['groupmatches'] = $this->groupmatches;
	}
	
	protected function init_teachermatches()
	{
		global $teachercode;
		$teachers = $this->list_teachers();
		if(isset($teachercode))
			$tcodes = SA_loadquery("SELECT tid,data FROM ". $teachercode);
		else
			$tcodes = SA_loadquery("SELECT tid, CONCAT(firstname,' ',lastname) AS data FROM teacher");
		foreach($teachers AS $tkey => $tname)
		{
			$this->teachermatches[$tkey] = '';
			foreach($tcodes['data'] AS $tckey => $tcname)
			{
				if(strcasecmp($tname,$tcname) == 0)
					$this->teachermatches[$tkey] = $tcodes['tid'][$tckey];
			}
		}
	}
	
	protected function init_subjectmatches()
	{
		$subjects = $this->list_subjects();
		$lvssubjects = SA_loadquery("SELECT mid, shortname FROM subject");
		foreach($subjects AS $tsub => $sname)
		{
			$this->subjectmatches[$tsub] = '';
			foreach($lvssubjects['shortname'] AS $lskey => $lsname)
			{
				if(strcasecmp($sname,$lsname) == 0)
					$this->subjectmatches[$tsub] = $lvssubjects['mid'][$lskey];
			}
		}
	}
	
	protected function init_groupmatches()
	{
		$groups = $this->list_groups();
		$lvsgroups = SA_loadquery("SELECT gid, groupname FROM sgroup WHERE active=1");
		foreach($groups AS $tgrp => $gname)
		{
			$this->groupmatches[$tgrp] = '';
			if(substr($gname,-4,4) == "vrij" || substr($gname,-5,4) == "vrij")
				$this->groupmatches[$tgrp] = -2;	
			//else 
				//echo("Groupmatch ". $gname. " does not contain vrij<BR>");
			foreach($lvsgroups['groupname'] AS $lgkey => $lgname)
			{
				if(strcasecmp($gname,$lgname) == 0)
					$this->groupmatches[$tgrp] = $lvsgroups['gid'][$lgkey];
				else
				{ // Groupname match based on groupname construction t{t}d-dt{t{t}}d which in LVS is t{t}dt{t{t}}d. Special for CSN!
					$spgpn = explode("-",$gname,2);
					if(isset($spgpn[1]) && strlen($spgpn[1]) > 1)
					{
						$altgname = $spgpn[0]. substr($spgpn[1],1);
						if(strcasecmp($altgname,$lgname) == 0)
							$this->groupmatches[$tgrp] = $lvsgroups['gid'][$lgkey];
					}				
				}
			}
		}
	}
	
	public function store_DB()
	{
		global $teachercode;
		$day2txt = array(1 => 'Ma','Di','Wo','Do','Vr');
		$roostername = $_SESSION['roostername'];
		// Storing data in database, first translate the activities and extract class items
		$myactivities = $this->get_activities();
		foreach($myactivities AS $akey => $activity)
		{
			//echo($akey. " : ". $arec['teacher']. ",". $arec['subject']. ",". $arec['group']. ",". $arec['room']. ",". $arec['day']. ",". $arec['hour']. "<BR>"); */
			if($activity['group'] != "" && isset($this->groupmatches["". $activity['group']]) && $this->groupmatches["". $activity['group']] > 0)
			{
				$transactivity[$akey]['teacher'] = $this->teachermatches["". $activity['teacher']];
				$transactivity[$akey]['subject'] = $this->subjectmatches["". $activity['subject']];
				$transactivity[$akey]['group'] = $this->groupmatches["". $activity['group']];
				if($transactivity[$akey]['teacher'] != '' && $transactivity[$akey]['teacher'] != '' && $transactivity[$akey]['teacher'] != '' && $transactivity[$akey]['teacher'] != -2 && $transactivity[$akey]['teacher'] != -2 && $transactivity[$akey]['teacher'] != -2)
				{ // Extracting a class (lesson)
					$classid = $transactivity[$akey]['teacher']. ",". $transactivity[$akey]['subject']. ",". $transactivity[$akey]['group'];
					$myclasses[$classid] = $activity['teacher']. ",". $activity['subject']. ",". $activity['group'];
				}
			}
		}
		// Now add, change classes (lessons) as needed but first check if addition is needed and ask confirmation if not already given.
		if(!isset($_POST['addconfirm']))
		{
			$teacherchangecount = 0;
			$lessonaddcount = 0;
			foreach($myclasses AS $classid => $classexpl)
			{
				$ci = explode(",",$classid);
				$cqr = SA_loadquery("SELECT * FROM class WHERE gid=". $ci[2]. " AND mid=". $ci[1]);
				if(isset($cqr['tid']))
				{ // Change of leave unchanged
					if($cqr['tid'][1] == $ci[0])
					{
						//echo("No change for class ". $classid. "<BR>");
					}
					else
						$teacherchangecount++;					
				}
				else
				{ // Add a new class
					if($lessonaddcount == 0)
					{
						echo("De onderstaande lessen worden toegevoegd, indien U bevestigt dat deze toegevoegd mogen worden.<FORM METHOD=POST ID='confirmform'><INPUT TYPE=SUBMIT NAME=addconfirm VALUE='JA, ik wil deze lessen toevoegen'><INPUT TYPE=SUBMIT NAME=denyconfirm VALUE='NEE, ik wil deze lessen niet toevoegen'></FORM>");
					}
					// Class id needs to be translates to teacher, subject and group.
					if(isset($teachercode))
						$tnqr = SA_loadquery("SELECT data FROM `". $teachercode. "` WHERE tid=". $ci[0]);
					else
						$tnqr = SA_loadquery("SELECT CONCAT(firstname,' ',lastname) AS data FROM teacher WHERE tid=". $ci[0]);
					$snqr = SA_loadquery("SELECT shortname FROM subject WHERE mid=". $ci[1]);
					$gnqr = SA_loadquery("SELECT groupname FROM sgroup WHERE gid=". $ci[2]);
					echo("Toe te voegen les (docent,vak,klas/cluster) : ". $tnqr['data'][1]. ",". $snqr['shortname'][1]. ",". $gnqr['groupname'][1]. "<BR>");
					$lessonaddcount++;
				}
			}
			if($teacherchangecount > 0)
				echo("<p style='color: purple;'>Voor ". $teacherchangecount. " lessen wordt een andere leerkracht ingesteld.</p>");
		}
		// Now add lessons and activities if allowed
		if(isset($_POST['addconfirm']) || $lessonaddcount == 0)
		{
			foreach($myclasses AS $classid => $classexpl)
			{
				$ci = explode(",",$classid);
				$cqr = SA_loadquery("SELECT * FROM class WHERE gid=". $ci[2]. " AND mid=". $ci[1]);
				if(isset($cqr['tid']))
				{ // Change of leave unchanged
					if($cqr['tid'][1] == $ci[0])
					{
						$cid[$classid] = $cqr['cid'][1];
					}
					else
					{
						mysql_query("UPDATE class SET tid=". $ci[0]. " WHERE cid=". $cqr['cid'][1]);	
						$cid[$classid] = $cqr['cid'][1];
					}
				}
				else
				{ // Add a new class
					mysql_query("INSERT INTO class (tid,mid,gid,masterlink) VALUES(". $ci[0]. ",". $ci[1]. ",". $ci[2]. ",0)");
					$cid[$classid] = mysql_insert_id();
				}
			}
			// Now remove the activities and add the new ones
			mysql_query("DELETE FROM timetableactivity WHERE timetablename LIKE '". $roostername. " __'");
			foreach($transactivity AS $akey => $atact)
			{
				$classid = $atact['teacher']. ",". $atact['subject']. ",". $atact['group'];
				//echo("INSERT INTO timetableactivity (timetablename,timeslot,location,cid) VALUES('". $roostername. " ". $day2txt[$myactivities[$akey]['day']]. "','". $myactivities[$akey]['hour']. "','". $myactivities[$akey]['room']. "',". $cid[$classid]. ")<BR>");
				mysql_query("INSERT INTO timetableactivity (timetablename,timeslot,location,cid) VALUES('". $roostername. " ". $day2txt[$myactivities[$akey]['day']]. "','". $myactivities[$akey]['hour']. "','". $myactivities[$akey]['room']. "',". $cid[$classid]. ")");
			}
			// Clear the session vars
			unset($_SESSION['roosterxmlcontent']);
			unset($_SESSION['teachermatches']);
			unset($_SESSION['subjectmatches']);
			unset($_SESSION['groupmatches']);
			echo("Rooster is succesvol ingevoerd in het LVS");
		}
	}
}

class FETrooster extends rooster
{
	protected $myteachers, $mysubjects, $mygroups, $myactivities;
	public function __construct($xmldata)
	{
		//$this->xmldata = $xmldata;
		$this->myteachers = $this->list_teachers($xmldata);
		$this->mysubjects = $this->list_subjects($xmldata);
		$this->mygroups = $this->list_groups($xmldata);
		$this->myactivities = $this->get_activities($xmldata);
	}	
	public function list_teachers($xmldata = NULL)
	{
		if(!isset($xmldata))
			return $this->myteachers;
		$teachcount=0;
		foreach($xmldata->Teacher AS $aTeacher)
		{
			$teachers[substr($aTeacher['name'],0)] = $aTeacher['name'];
		}
		if(isset($teachers))
			return($teachers);
	}
	public function list_subjects($xmldata = NULL)
	{
		if(!isset($xmldata))
			return $this->mysubjects;
		foreach($xmldata->Teacher AS $aTeacher)
		{
			foreach($aTeacher->Day AS $aDay)
				foreach($aDay->Hour AS $anHour)
				{
					$subshort = $anHour->Subject[0]['name'];
					if(isset($subshort))
						$subjects[substr($subshort,0)] = $subshort;
				}
		}
		if(isset($subjects))
			return($subjects);		
	}
	public function list_groups($xmldata = NULL)
	{
		if(!isset($xmldata))
			return $this->mygroups;
		foreach($xmldata->Teacher AS $aTeacher)
		{
			foreach($aTeacher->Day AS $aDay)
				foreach($aDay->Hour AS $anHour)
				{
					$grpshort = $anHour->Students[0]['name'];
					if(isset($grpshort))
						$groups[substr($grpshort,0)] = $grpshort;
				}
		}
		if(isset($groups))
			return($groups);
	}
	
	public function get_activities($xmldata = NULL)
	{
		if(!isset($xmldata))
			return $this->myactivities;
		$activitiesnr = 1;
		foreach($xmldata->Teacher AS $aTeacher)
		{
			$daynr = 1;
			foreach($aTeacher->Day AS $aDay)
			{
				$hrnr = 1;
				foreach($aDay->Hour AS $anHour)
				{
					if(isset($anHour->Students[0]['name']) && isset($anHour->Subject[0]['name']))
					{
						$activities[$activitiesnr]['group'] = $anHour->Students[0]['name'];
						$activities[$activitiesnr]['subject'] = $anHour->Subject[0]['name'];
						$activities[$activitiesnr]['teacher'] = $aTeacher['name'];
						$activities[$activitiesnr]['room'] = $anHour->Room[0]['name'];
						$activities[$activitiesnr]['day'] = $daynr;
						$activities[$activitiesnr]['hour'] = $hrnr;
						$activitiesnr++;
					}
					$hrnr++;
				}
				$daynr++;
			}
		}
		if(isset($activities))
			return($activities);		
		
	}
	
	public function show_version()
	{
		echo("FET format, zonder versie gegevens in het bestand<BR>");
	}
}

class ASCrooster extends rooster
{
	protected $myteachers, $mysubjects, $mygroups, $myactivities, $ascversion;
	protected $mylessons;
	protected $skipchars;
	public function __construct($xmldata)
	{
		$this->myteachers = $this->list_teachers($xmldata);
		$this->mysubjects = $this->list_subjects($xmldata);
		$this->mygroups = $this->list_groups($xmldata);
		$this->myactivities = $this->get_activities($xmldata);
		$this->ascversion = $xmldata['ascttversion'];
		if(substr($xmldata->teachers->teacher[0]['id'],0,1) == "*")
			$this->skipchars=1;
		else
			$this->skipchars=0;
	}	
	public function list_teachers($xmldata = NULL)
	{
		if(!isset($xmldata))
			return($this->myteachers);
		foreach($xmldata->teachers->teacher AS $aTeacher)
			$teachers[substr($aTeacher['id'],$this->skipchars)] = $aTeacher['short'];
		if(isset($teachers))
			return($teachers);		
	}
	public function list_subjects($xmldata = NULL)
	{
		if(!isset($xmldata))
			return($this->mysubjects);
		foreach($xmldata->subjects->subject AS $aSubject)
			$subjects[substr($aSubject['id'],$this->skipchars)] = $aSubject['short'];
		if(isset($subjects))
			return($subjects);		
	}
	public function list_groups($xmldata = NULL)
	{
		if(!isset($xmldata))
			return($this->mygroups);
		if(isset($xmldata->groups))
		{ // Groups are used in stead of classes, so we need to make a conversion
			foreach($xmldata->classes->class AS $aGroup)
				$classes[substr($aGroup['id'],$this->skipchars)] = $aGroup['name'];
			foreach($xmldata->groups->group AS $aGroup)
			{
				$classid = substr($aGroup['classid'],$this->skipchars);
				if($aGroup['entireclass'] == 1)
					$groups[substr($aGroup['id'],$this->skipchars)] = $classes[$classid];
				else
				{
					$groups[substr($aGroup['id'],$this->skipchars)] = $classes[$classid]. $aGroup['name'];
				}
			}
			//echo("I have groups!<BR>");
		}
		else
		{
			foreach($xmldata->classes->class AS $aGroup)
				$groups[substr($aGroup['id'],$this->skipchars)] = $aGroup['name'];
		}
		if(isset($groups))
			return($groups);				
	}
	public function get_activities($xmldata = NULL)
	{
		if(isset($xmldata->groups))
		{ // Groups are used so need lessons first
			$this->mylessons = $this->list_lessons($xmldata);
		}
		$daystrans = array("00001" => 5, "00010" => 4, "00100" => 3, "01000" => 2, "10000" => 1);
		if(!isset($xmldata))
			return($this->myactivities);
		$rooms = $this->list_rooms($xmldata);
		$activityid=1;
		foreach($xmldata->cards->card AS $anActivity)
		{
			if(isset($xmldata->groups))
			{ // teacher, subject and group come from lessons
				$lessonid = substr($anActivity['lessonid'],$this->skipchars);
				$activities[$activityid]['group'] = $this->mylessons[$lessonid]['group'];
				$activities[$activityid]['subject'] = $this->mylessons[$lessonid]['subject'];
				$activities[$activityid]['teacher'] = $this->mylessons[$lessonid]['teacher'];				
			}
			else
			{ // teacher, subject and group are included directly
				$activities[$activityid]['group'] = substr($anActivity['classids'],$this->skipchars);
				$activities[$activityid]['subject'] = substr($anActivity['subjectid'],$this->skipchars);
				$activities[$activityid]['teacher'] = substr($anActivity['teacherids'],$this->skipchars);
			}
			$activities[$activityid]['room'] = $rooms[substr($anActivity['classroomids'],$this->skipchars)];
			if(strlen($anActivity['day']) == 1)
				$activities[$activityid]['day'] = $anActivity['day'] + 1;
			else
			{ // Day specified as days!
				$days = "". $anActivity['days'];
				if(isset($daystrans[$days]))
					$activities[$activityid]['day'] = $daystrans[$days];			
			}
			$activities[$activityid++]['hour'] = $anActivity['period'];
		}
		if(isset($activities))
			return($activities);		
		
	}
	private function list_rooms($xmldata)
	{
		foreach($xmldata->classrooms->classroom AS $aRoom)
			$rooms[substr($aRoom['id'],$this->skipchars)] = $aRoom['short'];
		if(isset($rooms))
			return($rooms);				
	}
	
	private function list_lessons($xmldata)
	{
		if(isset($this->mylessons))
			return $this->mylessons;
		foreach($xmldata->lessons->lesson AS $aLesson)
		{
			$lessons[substr($aLesson['id'],$this->skipchars)]['teacher'] = $aLesson['teacherids'];
			$lessons[substr($aLesson['id'],$this->skipchars)]['subject'] = $aLesson['subjectid'];
			$grpid = explode(",",$aLesson['groupids']);
			$lessons[substr($aLesson['id'],$this->skipchars)]['group'] = $grpid[0];
		}
		if(isset($lessons))
			return($lessons);				
	}
	
	
	public function show_version()
	{
		echo("ASC formaat, versie ". $this->ascversion . "<BR>");
	} 
}
?>


