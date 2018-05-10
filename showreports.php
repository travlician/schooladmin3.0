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

  $login_qualify = 'S';
  include ("schooladminfunctions.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
    
  $uid = intval($uid);
  $sid = $uid;

  // First we get the data from student in an array.
  $sql_query = "SELECT * FROM student LEFT JOIN sgrouplink USING(sid) WHERE student.sid='$sid'";
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
       $student_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $row_n = $nrows;
  // set the group id for smarter queries following
  $gid = $student_array['gid'][1];
  
  // Get the reports on this student
  $sql_query = "SELECT DISTINCT reports.rid,teacher.tid,teacher.lastname,teacher.firstname,reports.date,reports.LastUpdate,reports.summary";
  $sql_query .= " FROM reports,teacher,sgroup";
  $sql_query .= " WHERE reports.sid='".$sid."' AND teacher.tid=reports.tid AND (reports.type='F' OR reports.type='T')";
  $sql_query .= " AND reports.protect='A' ";
  $sql_query .= " ORDER BY reports.date DESC";
  $stud_reports = SA_loadquery($sql_query);
  
  // Get the reports on the group
  $sql_query = "SELECT DISTINCT reports.rid,teacher.tid,teacher.lastname,teacher.firstname,reports.date,reports.LastUpdate,reports.summary";
  $sql_query .= " FROM reports,teacher";
  $sql_query .= " WHERE reports.tid=teacher.tid AND reports.sid='". $gid. "' AND (reports.type='C' OR reports.type='X')";
  $sql_query .= " AND reports.protect='A'";
  $sql_query .= " ORDER BY reports.date DESC";
  $grp_reports = SA_loadquery($sql_query);

  
  SA_closeDB();

  // First part of the page
  echo("<html><head><title>" . $dtext['My_reports'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['My_reports'] . " " . $student_array['firstname'][1] . " " . $student_array['lastname'][1] . "</font><p>");
  include("studentmenu.php");

  echo("<br>");

  // Now show the student reports
  echo($dtext['stud_report']. ":<br>"); 
  if(isset($stud_reports))
  {
    // Create the heading row for the table
    echo("<table border=1 cellpadding=0>");
    echo("<tr><td><center>" . $dtext['Author'] . "</td>");
    echo("<td><center>" . $dtext['Date'] . "</td>");
    echo("<td><center>" . $dtext['L_update'] . "</td>");
    echo("<td><center>" . $dtext['Summary'] . "</td>");
    echo("<td></td></font></tr>");
  }
  else
    echo($dtext['no_rep_4stu'] . "<br>");

  // Create a row in the table for every existing report
  if(isset($stud_reports))
  foreach($stud_reports['rid'] AS $r => $rid)
  {
    echo("<tr><form method=post action=viewsreport.php name=vr". $r. ">");
    // Put in the hidden field for report id and put the name of the teacher that created the report
    echo("<td><center><input type=hidden name=rid value=" . $stud_reports['rid'][$r] .">");
    echo($stud_reports['firstname'][$r]. " " . $stud_reports['lastname'][$r]."</td>");
    // Add date, last update and summary fields
    echo("<td><center>" . $stud_reports['date'][$r] . "</td>");
    echo("<td><center>" . $stud_reports['LastUpdate'][$r] . "</td>");
    echo("<td>" . $stud_reports['summary'][$r] . "</td>");
    // Add the View button
    echo("<td><center><img src='PNG/search.png' title='". $dtext['View']. "' onclick='document.vr". $r. ".submit();'></td></form>");
    echo("</tr>");
  }
  echo("</table>");
  
  // Show the group reports
  echo($dtext['grp_report']. ":<br>"); 
  if(isset($grp_reports))
  {
    // Create the heading row for the table
    echo("<table border=1 cellpadding=0>");
    echo("<tr><td><center>" . $dtext['Author'] . "</td>");
    echo("<td><center>" . $dtext['Date'] . "</td>");
    echo("<td><center>" . $dtext['L_update'] . "</td>");
    echo("<td><center>" . $dtext['Summary'] . "</td>");
    echo("<td></td></font></tr>");
  }
  else
    echo($dtext['No_rep_4grp'] . "<br><br>");

  // Create a row in the table for every existing report
  if(isset($grp_reports))
  foreach($grp_reports['rid'] AS $r => $rid)
  {
    echo("<tr><form method=post action=viewsreport.php name=gr". $r. ">");
    // Put in the hidden field for report id and put the name of the teacher that created the report
    echo("<td><center><input type=hidden name=rid value=" . $grp_reports['rid'][$r] .">");
    echo($grp_reports['firstname'][$r]. " " . $grp_reports['lastname'][$r]."</td>");
    // Add date, last update and summary fields
    echo("<td><center>" . $grp_reports['date'][$r] . "</td>");
    echo("<td><center>" . $grp_reports['LastUpdate'][$r] . "</td>");
    echo("<td>" . $grp_reports['summary'][$r] . "</td>");
    // Add the View button
    echo("<td><center><img src='PNG/search.png' title='". $dtext['View']. "' onclick='document.gr". $r. ".submit();'></td></form>");
    echo("</tr>");
  }
  echo("</table>");

?>

</html>
