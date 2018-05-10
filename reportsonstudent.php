<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011 Aim4me N.V.   (http://www.aim4me.info)       |
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

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];
  if(isset($HTTP_POST_VARS['sid']))
    $StudentID = $HTTP_POST_VARS['sid'];
  else
    if(isset($_SESSION['sid']))
    {
      $StudentID = $_SESSION['sid'];
      $_SESSION['sid'] = "";
    }
  
  $uid = intval($uid);

  // First we get the data from concerned student in an array.
  $sql_query = "SELECT * FROM student WHERE sid='$StudentID'";
  $mysql_query = $sql_query;
  //echo $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);
  //echo mysql_error($userlink);
  $nrows = 0;
  if (mysql_num_rows($sql_result)!=0)
  {
    $nfields = mysql_num_fields($sql_result);
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
     $nrows++;
     for ($i=0;$i<$nfields;$i++){
       $fieldname = mysql_field_name($sql_result,$i);
       $fieldvalu = mysql_result($sql_result,$r,mysql_field_name($sql_result,$i));
       $grade_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $row_n = $nrows;

  // Create a separate array with the reports on the student the teacher is allowed to see
  $sql_query = "SELECT DISTINCT reports.rid,teacher.tid,teacher.lastname,teacher.firstname,reports.date,reports.LastUpdate,reports.summary";
  $sql_query .= " FROM reports,teacher,sgroup";
  $sql_query .= " WHERE reports.tid=teacher.tid AND reports.sid='".$StudentID."' AND (reports.type='F' OR reports.type='T')";
  $sql_query .= " AND sgroup.groupname='". $CurrentGroup."'";
  // extra limits that apply to non counseller teachers only
  if($LoginType != "C")
  {
    $sql_query .= " AND (reports.protect='A' OR reports.protect='T' OR (reports.protect='M' AND sgroup.tid_mentor=$uid) OR reports.tid=$uid)";
  }
	// If protection set to N (None), only authos has access
	$sql_query .= " AND (reports.protect <> 'N' OR reports.tid=$uid)";
  $sql_query .= " ORDER BY reports.date DESC";
  $sql_result = mysql_query($sql_query,$userlink);
  //echo mysql_error($userlink);
  $nrows = 0;
  if (mysql_num_rows($sql_result)!=0)
  {
    $nfields = mysql_num_fields($sql_result);
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
     $nrows++;
     for ($i=0;$i<$nfields;$i++){
       $fieldname = mysql_field_name($sql_result,$i);
       $fieldvalu = mysql_result($sql_result,$r,mysql_field_name($sql_result,$i));
       $report_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $report_n = $nrows;
  SA_closeDB();

  // First part of the page
  echo("<html><head><title>" . $dtext['repstu_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['repstu_title'] . "</font><p>");
  echo '<a href="teacherpage.php">';
  echo($dtext['back_teach_page'] . "</a><br>");
  echo '<a href="reportsongroup.php">';
  echo($dtext['back_repgrp'] . "</a><br>");
  echo("<br><div align=left>" . $dtext['repstu_expl_1'] . "</dev><br>");

  // Show for which group current editing and allow changing the group
  echo($dtext['repstu_expl_2'] . " <b>");
  echo($grade_array['firstname'][1] . " " . $grade_array['lastname'][1]);
  echo("</b><br><br>");

  if($report_n > 0)
  {
    // Create the heading row for the table
    echo("<table border=1 cellpadding=0>");
    echo("<tr><td><center>" . $dtext['Author'] . "</td>");
    echo("<td><center>" . $dtext['Date'] . "</td>");
    echo("<td><center>" . $dtext['L_update'] . "</td>");
    echo("<td><center>" . $dtext['Summary'] . "</td>");
    echo("<td></td>");
    echo("<td></td>");
    echo("<td></td></font></tr>");
  }
  else
    echo($dtext['no_rep_4stu'] . "<br><br>");

  // Create a row in the table for every existing report
  for($r=1;$r<=$report_n;$r++)
  {
    echo("<tr><form method=post action=viewreport.php name=vr". $r. ">");
    // Put in the hidden field for report id and put the name of the teacher that created the report
    echo("<td><center><input type=hidden name=rid value=" . $report_array['rid'][$r] .">");
    echo($report_array['firstname'][$r]. " " . $report_array['lastname'][$r]."</td>");
    // Add date, last update and summary fields
    echo("<td><center>" . $report_array['date'][$r] . "</td>");
    echo("<td><center>" . $report_array['LastUpdate'][$r] . "</td>");
    echo("<td>" . $report_array['summary'][$r] . "</td>");
    // Add the View button
    //echo("<td><center><input type=submit value=" . $dtext['View'] . "></td></form>");
    echo("<td><center><img src='PNG/search.png' title='". $dtext['View']. "' onclick='document.vr". $r. ".submit();'></td></form>");
    // Add the Edit & delete buttons (only if this theacher is the creator or counseller
    if($LoginType == "C" || $report_array['tid'][$r] == $uid)
    {
      echo("<form method=post action=editreport.php name=er". $r. "><input type=hidden name=rid value=");
      echo($report_array['rid'][$r]);
      //echo("><td><center><input type=hidden name=rtype value='Student'><input type=submit value=" . $dtext['Edit'] . "></td></form>");
      echo("><td><center><input type=hidden name=rtype value='Student'><img src='PNG/reply.png' title='". $dtext['Edit']. "' onclick='document.er". $r. ".submit();'></td></form>");
      echo("<form method=post action=delreport.php name=dr". $r. "><input type=hidden name=rid value=");
      echo($report_array['rid'][$r]);
      //echo("><td><center><input type=hidden name=sid value=$StudentID><input type=submit value=" . $dtext['Delete'] . "></td></form></tr>");
      echo("><td><center><input type=hidden name=sid value=$StudentID><img src='PNG/action_delete.png' title='". $dtext['Delete']. "' onclick='document.dr". $r. ".submit();'></td></form></tr>");
    }
    else
    {	// create two empty cells in the table because user has no access
      echo("</tr>");
    }   
  }
  echo("</table>");

  // Create a button for a new report
  // Insert the row for a new report
  echo("<form method=post action=editreport.php><input type=hidden name=rid value=''>");
  echo("<input type=hidden name=rtype value='Student'>");
  echo("<input type=hidden name=sid value=$StudentID>");
  echo("<input type=submit value='" . $dtext['Crea_rep_4stu'] . "'></form>");

  echo '<a href="teacherpage.php">';
  echo $dtext['back_teach_page'];
  echo '</a>';
 
  // close the page
  echo("</html>");

?>
