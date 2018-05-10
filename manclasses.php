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


  session_start();

  $login_qualify = 'A';
  include ("schooladminfunctions.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];
  
  $uid = intval($uid);

  // First we get all the data from existing classes in an array.
  $sql_query = "SELECT * FROM class,sgroup WHERE active=1 AND sgroup.groupname='$CurrentGroup' AND class.gid=sgroup.gid ORDER BY groupname,show_sequence";
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

  // Create a separate array with the teachers
  $sql_query = "SELECT tid,lastname,firstname FROM teacher WHERE is_gone <> 'Y' ORDER BY lastname";
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
       $teach_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $teach_n = $nrows;

  // Create a separate array with the subjects
  $sql_query = "SELECT mid,shortname FROM subject ORDER BY shortname";
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
       $subject_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $subject_n = $nrows;

  // Create a separate array with the groups
  $sql_query = "SELECT gid,groupname FROM sgroup WHERE active=1 ORDER BY groupname";
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
       $group_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $group_n = $nrows;
  SA_closeDB();

  // First part of the page
  echo("<html><head><title>" . $dtext['class_man_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['class_man_title'] . "</font><p>");
  echo '<a href="admin.php">';
  echo($dtext['back_admin'] . "</a><br>");
  echo("<br><div align=left>" . $dtext['man_class_expl_1']);
  echo("<br>" . $dtext['man_class_expl_2']);
  echo("<br>" . $dtext['man_class_expl_3']);
  echo("<br>" . $dtext['man_class_expl_4']);
  echo("<br>" . $dtext['man_class_expl_5'] . "</dev><br>");

  // Show for which group current editing and allow changing the group
  echo("<form method=post action=chngrp4class.php>" . $dtext['man_class_expl_6'] . " <select name=NewGroup>");
  for($gc=1;$gc<=$group_n;$gc++)
  { // Add an option for each group, select the one currently active
    if($CurrentGroup == $group_array['groupname'][$gc])
      $IsSelected = " selected";
    else
      $IsSelected = "";
    echo("<option value=" . $group_array['groupname'][$gc]."$IsSelected>" . $group_array['groupname'][$gc]."</option>");
  }
  echo("</select><input type=submit value=" . $dtext['Change'] . "></form>");
  
  // Create the heading row for the table
  echo("<table border=1 cellpadding=0>");
  echo("<tr><td><center>" . $dtext['Subject'] . "</td>");
  echo("<td><center>" . $dtext['Teacher'] . "</td>");
  echo("<td><center>" . $dtext['Special'] . "</td>");
  echo("<td><center>" . $dtext['numb_token'] . "</td>");
  echo("<td></td>");
  echo("<td></td></font></tr>");

  // Create a row in the table for every existing class
  for($r=1;$r<=$row_n;$r++)
  {
    echo("<tr><form method=post action=updclass.php name=uc". $r. ">");

    echo("<td><center><input type=hidden name=cid value=" . $grade_array['cid'][$r] .">");
    echo("<input type=hidden name=gid value=" . $grade_array['gid'][$r] .">");
    // Add the subject
    echo("<select name=mid>");
    for($sc=1;$sc<=$subject_n;$sc++)
    { // add each subject!
      if($grade_array['mid'][$r] == $subject_array['mid'][$sc]) $SelectInd = "selected";
      else $SelectInd = "";
      echo("<option value=" . $subject_array['mid'][$sc]. " " . $SelectInd.">");
      echo($subject_array['shortname'][$sc]);
      echo("</option>");
    }
    echo("</select></td>");
    // Add the teacher
    echo("<td><center><select name=tid>");
    for($tc=1;$tc<=$teach_n;$tc++)
    { // add each teacher!
      if($grade_array['tid'][$r] == $teach_array['tid'][$tc]) $SelectInd = "selected";
      else $SelectInd = "";
      echo("<option value=" . $teach_array['tid'][$tc]. " " . $SelectInd.">");
      echo($teach_array['firstname'][$tc]." " . $teach_array['lastname'][$tc]);
      echo("</option>");
    }
    echo("</select></td>");
    // Add the special entry
    echo("<td><input type=text size=5 name=masterlink value=" . $grade_array['masterlink'][$r]."></td>");
    // Add the sequence number for display
    echo("<td><input type=text size=2 name=show_sequence value=" .$grade_array['show_sequence'][$r]. "></td>");
    // Add the change button
    //echo("<td><center><input type=submit value=" . $dtext['Change'] . "></td></form>");
    echo("<td><center><img src='PNG/action_check.png' title='". $dtext['Change']. "' onclick='document.uc". $r. ".submit();'></td></form>");
    // Add the delete button
    echo("<form method=post action=delclass.php name=dc". $r. "><input type=hidden name=cid value=");
    echo($grade_array['cid'][$r]);
    //echo("><td><center><input type=submit value=" . $dtext['Delete'] . "></td></form></tr>");
    echo("><td><center><img src='PNG/action_delete.png' title='". $dtext['Delete']. "' onclick='if(confirm(\"". $dtext['man_class_expl_5']. "\")) { document.dc". $r. ".submit(); }'></td></form></tr>");
  }
  // Insert the row for a new class
  echo("<tr><form method=post action=updclass.php name=newc>");

  echo("<td><center><input type=hidden name=cid value=\"\">");
  // group id (gid) must also be added hidden but we need to get it first!
  for($gc=1;$gc<=$group_n;$gc++)
  {
     if($group_array['groupname'][$gc] == $CurrentGroup)
       echo("<input type=hidden name=gid value=" . $group_array['gid'][$gc].">");
  }
  // Add the subject
  echo("<select name=mid>");
  for($sc=1;$sc<=$subject_n;$sc++)
  { // add each subject!
    echo("<option value=" . $subject_array['mid'][$sc]. ">");
    echo($subject_array['shortname'][$sc]);
    echo("</option>");
  }
  echo("</select></td>");
  // Add the teacher name
  echo("<td><center><select name=tid>");
  for($mc=1;$mc<=$teach_n;$mc++)
  { // add each teacher!
    echo("<option value=" . $teach_array['tid'][$mc]. ">");
    echo($teach_array['firstname'][$mc]. " " . $teach_array['lastname'][$mc]);
    echo("</option>");
  }
  echo("</select></td>");
  // Add the special entry
  echo("<td><input type=text size=5 name=masterlink value=0></td>");
  // Add the sequence number for display
  echo("<td><input type=text size=2 name=show_sequence value=" .($row_n+1). "></td>");
  // Add the ADD button
  //echo("<td><center><input type=submit value=" . $dtext['ADD_CAP'] . "></td></form>");
  echo("<td><center><img src='PNG/action_add.png' title='". $dtext['ADD_CAP']. "' onclick='document.newc.submit();'></td></form>");
  // Here we don't have a delete button!
  echo("</tr>");
  
  // close the table
  echo("</table></html>");

?>
