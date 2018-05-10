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

  // Get a list of all test definitions for the current group
  if(isset($HTTP_GET_VARS['subject']))
  { // Produce a list for a single subject, all weeks
    if($_SESSION['LoginType'] == "S")
	{
      $query = "SELECT testdef.*,class.mid,subject.fullname FROM testdef LEFT JOIN class USING(cid) ";
      $query .= "LEFT JOIN subject USING(mid) LEFT JOIN period ON (testdef.period=period.id) LEFT JOIN sgrouplink USING(gid)";
      $query .= "WHERE sid='". $uid. "' AND period.year=testdef.year AND subject.shortname='". $HTTP_GET_VARS['subject']. "' ORDER BY date";
	}
	else
	{
      $query = "SELECT testdef.*,class.mid,subject.fullname FROM testdef LEFT JOIN class USING(cid) ";
      $query .= "LEFT JOIN subject USING(mid) LEFT JOIN period ON (testdef.period=period.id) LEFT JOIN sgroup ON(sgroup.gid=class.gid) ";
      $query .= "WHERE active=1 AND groupname='". $CurrentGroup. "' AND period.year=testdef.year AND subject.shortname='". $HTTP_GET_VARS['subject']. "' ORDER BY date";
	}
  }
  else
  { // Produce a list of all test definitions for the specified week
    if($_SESSION['LoginType'] == "S")
	{
      $query = "SELECT testdef.*,class.mid,subject.fullname FROM testdef LEFT JOIN class USING(cid) ";
      $query .= "LEFT JOIN subject USING(mid) LEFT JOIN period ON (testdef.period=period.id) LEFT JOIN sgrouplink USING(gid) ";
      $query .= "WHERE sid='". $uid. "' AND period.year=testdef.year AND testdef.week='". $HTTP_GET_VARS['week']. "' ORDER BY date";
	}
	else
	{
      $query = "SELECT testdef.*,class.mid,subject.fullname FROM testdef LEFT JOIN class USING(cid) ";
      $query .= "LEFT JOIN subject USING(mid) LEFT JOIN period ON (testdef.period=period.id) LEFT JOIN sgroup ON(sgroup.gid=class.gid) ";
      $query .= "WHERE active=1 AND groupname='". $CurrentGroup. "' AND period.year=testdef.year AND testdef.week='". $HTTP_GET_VARS['week']. "' ORDER BY date";
	}
  }
  $tdefs = SA_loadquery($query);
  echo mysql_error($userlink);
  // Get all weight data in an array
  $iweights = SA_loadquery("SELECT mid,testtype,weight FROM reportcalc");
  echo mysql_error($userlink);
  if(isset($iweights))
  {
    foreach($iweights['mid'] AS $wix => $dummy)
	  $weight[$iweights['mid'][$wix]][$iweights['testtype'][$wix]] = $iweights['weight'][$wix];
  }
  // Get a translation table for the testtypes
  $itt = SA_loadquery("SELECT * FROM testtype");
  if(isset($itt))
    foreach($itt['type'] AS $tix => $ttyp)
	  $testtrans[$ttyp] = $itt['translation'][$tix];

  SA_closeDB();

  // First part of the page
  echo("<html><head><title>" . $dtext['lpt_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['lpt_title'] . "</font><p>");
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
  if(isset($HTTP_GET_VARS['subject']))
    echo(" ". $dtext['Subject']. " : " .$HTTP_GET_VARS['subject']. "<br><br>");
  else
    echo(" ". $dtext['Week']. " : " .$HTTP_GET_VARS['week']. "<br><br>");

  if(isset($tdefs))
  {
    // Now create a table with all info on tests per week for the requested subject
    // Create the first heading row for the table
    echo("<table border=1 cellpadding=0>");
    echo("<tr><th>". $dtext['Week']. "</th>");
    echo("<th>". $dtext['Date']. "</th>");
    echo("<th>". $dtext['Description']. "</th>");
    echo("<th>". $dtext['Subject']. "</th>");
    echo("<th>". $dtext['Tools']. "</th>");
    echo("<th>". $dtext['Type']. "</th>");
    echo("<th>". $dtext['Short']. "</th>");
    echo("<th>". $dtext['Duration']. "</th>");
    echo("<th>". $dtext['Assignments']. "</th>");
    echo("<th>". $dtext['Weight']. "</th></tr>");
    // Now add each date (week) it's info
    foreach($tdefs['date'] AS $tid => $tdate)
    {
	  if(isset($weight[$tdefs['mid'][$tid]][$tdefs['type'][$tid]]))
	    $wgt = $weight[$tdefs['mid'][$tid]][$tdefs['type'][$tid]];
	  else if(isset($weight['0'][$tdefs['type'][$tid]]))
	    $wgt = $weight['0'][$tdefs['type'][$tid]];
	  else
	    $wgt = "-";
      $wkno = date("W",mktime(0,0,0,substr($tdate,5,2),substr($tdate,8,2),substr($tdate,0,4)));
	  if($wgt != "-")
	    echo("<tr class=highlight>");
	  else if($tdefs['short_desc'][$tid] == $dtext['no_lesson'])
	    echo("<tr class=no_lesson>");
	  else
	    echo("<tr>");
	  echo("<td>". $wkno. "</td><td>". SA_mysqldate2nl($tdefs['date'][$tid]). "</td>");
	  echo("<td>". $tdefs['description'][$tid]. "</td>");
	  echo("<td>". $tdefs['fullname'][$tid]. "</td>");
	  echo("<td>". $tdefs['tools'][$tid]. "</td>");
	  echo("<td>". ($tdefs['type'][$tid] != '' ? 
	  ("<a href=# class=hidelink title=\"". $testtrans[$tdefs['type'][$tid]]. "\">". 
	  $tdefs['type'][$tid]. "</a>") : "&nbsp"). "</td>");
	  echo("<td>". $tdefs['short_desc'][$tid]. "</td>");
	  echo("<td>". $tdefs['duration'][$tid]. "</td>");
	  echo("<td>". $tdefs['assignments'][$tid]. "</td>");
	  
	  echo("<td><center>". $wgt. "</td></tr>");
    }
    echo("</table>");
  }
  if($_SESSION['LoginType'] != "S")
  {
    echo '<a href="teacherpage.php">';
    echo $dtext['back_teach_page'];
    echo '</a>';
  }
   // close the page
  echo("</html>");
?>
