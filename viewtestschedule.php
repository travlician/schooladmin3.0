<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014 Aim4me N.V.   (http://www.aim4me.info)	      |
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
  session_start();

  $login_qualify = 'SACT';
  include ("schooladminfunctions.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  if(isset($_SESSION['CurrentGroup']))
    $CurrentGroup = $_SESSION['CurrentGroup'];
  if(isset($HTTP_POST_VARS['NewGroup']) && SA_verifyGroupAccess($uid,$HTTP_POST_VARS['NewGroup']))
    $CurrentGroup = $HTTP_POST_VARS['NewGroup'];
  if(isset($HTTP_GET_VARS['NewGroup']) && SA_verifyGroupAccess($uid,$HTTP_GET_VARS['NewGroup']))
    $CurrentGroup = $HTTP_GET_VARS['NewGroup'];
  // Store the new group or future pages
  if(isset($CurrentGroup))
    $_SESSION['CurrentGroup']=$CurrentGroup;
  
  $uid = intval($uid);
  
  // If student, get the groupname for next query
  if($_SESSION['LoginType'] == "S")
  {
    $grp = SA_loadquery("SELECT groupname FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) WHERE active=1 AND sid=". $uid);
	$CurrentGroup = $grp['groupname'][1];
  }

  // Get a list of all test definitions for the current group or student
  
  if($_SESSION['LoginType'] == "S")
  {
    $query = "SELECT testdef.date,testdef.type,subject.shortname FROM testdef LEFT JOIN class USING(cid) ";
    $query .= "LEFT JOIN subject USING(mid) LEFT JOIN period ON (testdef.period=period.id) LEFT JOIN sgrouplink ON(sgrouplink.gid=class.gid) ";
    $query .= "WHERE sid='". $uid. "' AND period.year=testdef.year AND testdef.type <> '0' AND date > SUBDATE(NOW(), INTERVAL 1 YEAR) ORDER BY date ";
  }
  else
  {
    $query = "SELECT testdef.date,testdef.type,subject.shortname FROM testdef LEFT JOIN class USING(cid) ";
    $query .= "LEFT JOIN subject USING(mid) LEFT JOIN period ON (testdef.period=period.id) LEFT JOIN sgroup ON(sgroup.gid=class.gid) ";
    $query .= "WHERE active=1 AND groupname='". $CurrentGroup. "' AND period.year=testdef.year AND testdef.type <> '0' AND date > SUBDATE(NOW(), INTERVAL 1 YEAR) ORDER BY date ";
  }
  $tdefs = SA_loadquery($query);
  echo mysql_error($userlink);
  if(isset($tdefs))
  {
    foreach($tdefs['date'] AS $tid => $tdate)
    {
	 if(!isset($maxdate) || $tdate < $maxdate)
	  {
      $twk = 1 * date("W",mktime(0,0,0,substr($tdate,5,2),substr($tdate,8,2),substr($tdate,0,4)));
	  $tdy = date("j",mktime(0,0,0,substr($tdate,5,2),substr($tdate,8,2),substr($tdate,0,4)));
	  
      if(isset($ts[$tdefs['shortname'][$tid]][$twk]))
	      $ts[$tdefs['shortname'][$tid]][$twk] .= "+". $tdefs['type'][$tid];
	  else
	    $ts[$tdefs['shortname'][$tid]][$twk] = $tdefs['type'][$tid];
      if(isset($tl[$tdefs['shortname'][$tid]][$twk]))
	      $tl[$tdefs['shortname'][$tid]][$twk] .= "+". $tdy;
	  else
	    $tl[$tdefs['shortname'][$tid]][$twk] = $tdy;
      if(isset($wl[$twk]))
	      $wl[$twk] .= "+". $tdy;
	  else
	    $wl[$twk] = $tdy;
	  if(!isset($firstweek))
	    $firstweek = $twk;
      if(!isset($maxdate))
	    $maxdate = date("Y-m-d",mktime(0,0,0,substr($tdate,5,2),substr($tdate,8,2),substr($tdate,0,4)+1));
	  $lastweek = $twk;
	  if($twk == 53)
	    $week53 = 1;
	 }
    }
  }
  // Create a list of used weeks
  if($firstweek > $lastweek)
  {
    if(isset($week53))
	{
	  for($wkn = $firstweek; $wkn <= 53; $wkn++)
	    $usedweeks[$wkn] = 1;
	  for($wkn = 1; $wkn <= $lastweek; $wkn++)
	    $usedweeks[$wkn] = 1;
	}
	else
	{
	  for($wkn = $firstweek; $wkn <= 52; $wkn++)
	    $usedweeks[$wkn] = 1;
	  for($wkn = 1; $wkn <= $lastweek; $wkn++)
	    $usedweeks[$wkn] = 1;
	}
  }
  else
  {
    for($wkn = $firstweek;$wkn <= $lastweek; $wkn++)
	  $usedweeks[$wkn] = 1;
  } 
    
  SA_closeDB();

  // First part of the page
  echo("<html><head><title>" . $dtext['tschd_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['tschd_title'] . "</font><p>");
  if($_SESSION['LoginType'] != "S")
    echo("<a href=teacherpage.php>" . $dtext['back_teach_page'] . "</a><br>");
  else
    include("studentmenu.php");


  // Show for which group current editing and allow changing the group if teacher
  echo($dtext['Group_Cap'] . " <b>$CurrentGroup</b>");
  if($_SESSION['LoginType'] != "S")
  {
    echo(" (<a href=selectgroup.php?ReturnTo=viewtestschedule.php>" . $dtext['Change'] . "</a>)<br>");
  }
  echo("<br><br>");

  if(isset($ts))
  {
    // Now create a table with all info on tests per week
    // Create the first heading row for the table
    echo("<table border=1 cellpadding=0>");
    echo("<tr><th>". $dtext['Week']. ":</th>");
    foreach($usedweeks AS $wkno => $dum1)
      echo("<th><a href=viewltp.php?week=". $wkno. " title=\"". (isset($wl[$wkno]) ? $wl[$wkno] : "")."\">". $wkno. "</th>");
    echo("</tr>");
    // Now add each subjects info
    foreach($ts AS $subj => $dum2)
    {
      echo("<tr><th>");
	  if(isset($lessonplan) && $lessonplan == 1)
	  {
	    echo("<a href=\"viewltp.php?subject=". $subj. "\">". $subj. "</th>");
	  }
	  else
	    echo($subj. "</th>");
	  foreach($usedweeks AS $wkno => $dum3)
	  {
	    if(isset($ts[$subj][$wkno]))
	      echo("<td><a href=# class=hidelink title=\"". $tl[$subj][$wkno]. "\">". $ts[$subj][$wkno]. "</a></td>");
	    else
	      echo("<td>&nbsp;</td>");
	  }
	  echo("</tr>");
    }
    echo("</table>");
	
  }
  if($_SESSION['LoginType'] != 'S')
  {
    echo '<a href="teacherpage.php">';
    echo $dtext['back_teach_page'];
    echo '</a>';
  }
   // close the page
  echo("</html>");
?>
