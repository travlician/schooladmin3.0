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
  if(isset($HTTP_POST_VARS['NewGroup']) && SA_verifyGroupAccess($uid,$HTTP_POST_VARS['NewGroup']))
    $CurrentGroup = $HTTP_POST_VARS['NewGroup'];
  if(isset($HTTP_GET_VARS['NewGroup']) && SA_verifyGroupAccess($uid,$HTTP_GET_VARS['NewGroup']))
    $CurrentGroup = $HTTP_GET_VARS['NewGroup'];
  // Store the new group or future pages
  $_SESSION['CurrentGroup']=$CurrentGroup;
  
  $uid = intval($uid);
  
  // Get the fields to display in the summary
  $fields = SA_loadquery("SELECT * FROM student_details WHERE overview=1 ORDER BY seq_no");

  // First we get the data from existing students in an array.
  $sql_query = "SELECT * FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) WHERE sgroup.groupname='$CurrentGroup' ORDER BY lastname,firstname";
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
  
  // Add additional fields as defined in list
  if(isset($fields))
  {
    foreach($fields['table_name'] AS $ti => $tname)
	{
	  if($tname == "*package")
	  {
	    $packages = SA_loadquery("SELECT CONCAT(s_package.packagename,IF(s_package.extrasubject IS NOT NULL,CONCAT(\"+\",subject.shortname),\"\")) AS packinfo FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) LEFT JOIN s_package ON(s_package.sid=student.sid) LEFT JOIN subject ON (subject.mid=s_package.extrasubject) WHERE sgroup.groupname='$CurrentGroup' ORDER BY lastname,firstname");
		if(isset($packages))
   		  $grade_array['*package'] = $packages['packinfo'];
	  }
	  else if(substr($tname,0,1) != "*")
	  {
	    $extradata = SA_loadquery("SELECT `data` FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) LEFT JOIN `". $tname. "` ON(`". $tname. "`.sid=student.sid) WHERE sgroup.groupname='$CurrentGroup' ORDER BY lastname,firstname");
		if(isset($extradata))
		  $grade_array[$tname] = $extradata['data'];
	  }
	}
  }

  // Create a separate array with the reports on the group the teacher is allowed to see
  $sql_query = "SELECT DISTINCT reports.rid,teacher.tid,teacher.lastname,teacher.firstname,reports.date,reports.LastUpdate,reports.summary";
  $sql_query .= " FROM reports,teacher,sgroup";
  $sql_query .= " WHERE reports.tid=teacher.tid AND reports.sid=sgroup.gid AND (reports.type='C' OR reports.type='X')";
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
  echo("<html><head><title>" . $dtext['repgrp_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['repgrp_title'] . "</font><p>");
  echo '<a href="teacherpage.php">';
  echo($dtext['back_teach_page'] . "</a><br>");
  echo("<br><div align=left>" . $dtext['repgrp_expl_1'] . "</dev><br>");

  // Show for which group current editing and allow changing the group
  echo($dtext['repgrp_expl_2'] . " <b>$CurrentGroup</b> (<a href=selectgroup.php?ReturnTo=reportsongroup.php>" . $dtext['Change'] . "</a>)<br>");
  echo("<br>");

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
    echo($dtext['No_rep_4grp'] . "<br><br>");

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
      //echo("><td><center><input type=hidden name=rtype value='Group'><input type=submit value=" . $dtext['Edit'] . "></td></form>");
      echo("><td><center><input type=hidden name=rtype value='Group'><img src='PNG/reply.png' title='". $dtext['Edit']. "' onclick='document.er". $r. ".submit();'></td></form>");
      echo("<form method=post action=delreport.php name=dr". $r. "><input type=hidden name=rid value=");
      echo($report_array['rid'][$r]);
      //echo("><td><center><input type=submit value=" . $dtext['Delete'] . "></td></form></tr>");
      echo("><td><center><img src='PNG/action_delete.png' title='". $dtext['Delete']. "' onclick='document.dr". $r. ".submit();'></td></form></tr>");
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
  echo("<input type=hidden name=rtype value='Group'>");
  echo("<input type=submit value='" . $dtext['Crea_rep_4grp'] . "'></form>");

  // Now create a table with all students in the group to enable to go to their reports
  // Create the heading row for the table
  echo("<table border=1 cellpadding=0>");
  echo("<tr>");

  //echo("<td><center>" . $dtext['Lastname'] . "</td>");
  //echo("<td><center>" . $dtext['Firstname'] . "</td>");
  foreach($fields['label'] AS $fieldname)
  {
    echo("<td><center>". $fieldname. "</td>");
  }
  
  echo("<td><center>" . $dtext['Go_reps'] . "</td>");  
  echo("</font></tr>");

  // Create a row in the table for every existing student in the group
  for($r=1;$r<=$row_n;$r++)
  {
    echo("<tr><form method=post action=reportsonstudent.php>");
    // Put in the hidden field for student id 
    //echo("<td>". $grade_array['lastname'][$r]. "</td>");
    //echo("<td>" . $grade_array['firstname'][$r]."</td>");
	foreach($fields['table_name'] AS $fix => $tname)
	{
	  echo("<td>");
	  if($tname == "*student.lastname")
	    echo($grade_array['lastname'][$r]);
	  else if ($tname == "*student.firstname")
	    echo($grade_array['firstname'][$r]);
	  else if($tname == "*sid")
	    if(isset($altsids) && $altsids==1)
    	  echo($grade_array['altsid'][$r]);
	    else
		  echo($grade_array['sid'][$r]);
	  else if($tname == "*sgroup.groupname")
	    echo($grade_array['groupname'][$r]);
	  else if($fields['type'][$fix] == "picture" && isset($grade_array[$tname][$r]))
	    echo("<IMG SRC=". $livepictures. $grade_array[$tname][$r]. " HEIGHT=30>");
	  else
	  {
	    if(isset($grade_array[$tname][$r]))
  	      echo($grade_array[$tname][$r]);
		else
		  echo("&nbsp");
      }
	  echo("</td>");
	}
    // Add the Goto button
    echo("<td><center><input type=hidden name=sid value=" . $grade_array['sid'][$r] ."><input type=submit value='" . $dtext['Go2'] . "'></td></form></tr>");
  }
  echo("</table>");
  echo '<a href="teacherpage.php">';
  echo $dtext['back_teach_page'];
  echo '</a>';
 
  // close the page
  echo("</html>");

?>
