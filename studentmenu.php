<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2008-2014 Aim4me N.V.   (http://www.aim4me.info)	      |
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
  // Show the student menu
  if($_SESSION['Schoolkey'] != $databasename)
    exit(1);
	if(isset($supressStudentItems))
	{
		$supitems = explode(",",$supressStudentItems);
		foreach($supitems AS $tosup)
			$doSupress[$tosup] = true;
	}
  if(isset($carecodetable))
  { // See if we can get the care code tekst and style, first extract which table holds the choice!
    $caretxtqr = SA_loadquery("SELECT params FROM student_details WHERE table_name='". $carecodetable. "'");
	if(isset($caretxtqr['params']) && substr($caretxtqr['params'][1],0,1) == "*")
	{ // Tables exist, now see if we can get the real carecode text and style
	  $caretbl = substr($caretxtqr['params'][1],1);
	  $caredata = SA_loadquery("SELECT `". $caretbl. "`.tekst AS cctxt, `". $carecodecolors. "`.tekst AS pstyle FROM `". $carecodetable. "` LEFT JOIN `". $caretbl. "` ON(`". $caretbl. "`.id=data) LEFT JOIN `". $carecodecolors. "` ON(`". $carecodecolors. "`.id=data) WHERE sid=". $_SESSION['uid']);
	  if(isset($caredata['cctxt']) && !isset($doSupress['carecode']))
	  { // Code exists, now show the data with style
	    echo("<P style='". $caredata['pstyle'][1]. "'>". $_SESSION['dtext']['Carecode']. " : ". $caredata['cctxt'][1]. "</p>");
	  }
	}
  }
  echo("<P>". $announcement. "</p>");
  $syear = SA_loadquery("SELECT year FROM period LIMIT 1");
  echo("<P>". $_SESSION['dtext']['Year']. " ". $syear['year'][1]. "</p>");
  $maingrp = SA_loadquery("SELECT GROUP_CONCAT(groupname) AS mgrp FROM sgrouplink LEFT JOIN sgroup USING(gid) 
                           WHERE active=1 AND sid=". $_SESSION['uid']. " AND CHAR_LENGTH(groupname) = ". (isset($main_groupname_length) ? $main_groupname_length : 2));
  if(isset($maingrp['mgrp'][1]))
    echo("<P>". $_SESSION['dtext']['Group_Cap']. " : ". $maingrp['mgrp'][1]. "</p>");
  echo("<table border=0 width=100%><tr>");
	echo("<td><a href=showreportcard.php>" . $dtext['My_grades'] . "</a><br></td>");
  if(isset($dtext['tpage_classbook']) && !isset($doSupress['classbook']))
    echo("<td><a href=showstudentclassbook.php>" . $dtext['tpage_classbook'] . "</a><br></td>");
  if(isset($dtext['tpage_calendar']) && !isset($doSupress['calendar']))
    echo("<td><a href=showstudentcalendar.php>" . $dtext['tpage_calendar'] . "</a><br></td>");
	if(!isset($doSupress['reports']))
		echo("<td><a href=showreports.php>" . $dtext['My_reports'] . "</a><br></td>");
	if(!isset($doSupress['absence']))
		echo("<td><a href=showabsence.php>" . $dtext['My_absence'] . "</a><br></td>");
	if(!isset($doSupress['details']))
		echo("<td><a href=showstudentdetails.php>" . $dtext['My_dets'] . "</a><br></td>");
	if(!isset($doSupress['teachers']))
		echo("<td><a href=showstudentteachers.php>" . $dtext['My_teach'] . "</a><br></td>");
	if(!isset($doSupress['plt']))
		echo("<td><a href=viewtestschedule.php>" . $dtext['tschd_title'] . "</a><br></td>");
  if(isset($dtext['Library']) && !isset($doSupress['library']))
    echo("<td><a href=showstudentlibrary.php>" . $dtext['Library'] . "</a><br></td>");
  // See if this student can choose it's subjects
  $subsel = SA_loadquery("SELECT * FROM subjectselectgroups LEFT JOIN sgrouplink ON (gid=`group`) WHERE `sid`=". $_SESSION['uid']);
  if(isset($subsel))
  {
    echo("<td><a href=subsel.php>" . $dtext['Subpack'] . "</a><br></td>");
  }
  // Include add-ons
  if ($handle = opendir('.'))
  {
    while (false !== ($file = readdir($handle)))
	{
	  if(substr($file,0,6) == "Addon_")
	  {
	    $addonname = substr(substr($file,6),0,-4);
  		echo("<td><a href=Addon_". $addonname. ".php>" . $addonname . "</a><br></td>");
	  }
    }
    closedir($handle);
  }

  //
  echo("<td><a href=studentcpw.php>" . $dtext['Chng_pw'] . "</a><br></td>");
  echo("<td><a href=logout.php>" . $dtext['Logoff'] . "</a><br></td>");
  echo '</tr></table>';
?>

