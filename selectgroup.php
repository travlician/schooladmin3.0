<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014 Aim4me N.V.  (http://www.aim4me.info)        |
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
  require_once("schooladminfunctions.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;

  global $ReturnTo;
  if(isset($HTTP_GET_VARS['ReturnTo']))
    $ReturnTo = $HTTP_GET_VARS['ReturnTo'];
  if(isset($HTTP_POST_VARS['ReturnTo']))
    $ReturnTo = $HTTP_POST_VARS['ReturnTo'];
  $uid = intval($uid);

  // Setup an array /w mentorgroups if the teacher is a mentor
  $sql_query = "SELECT groupname FROM sgroup WHERE active=1 AND tid_mentor = '" . $uid ."' ORDER BY groupname";
  $sql_result = mysql_query($sql_query,$userlink);
  $mentor_n = 0;
  if (mysql_num_rows($sql_result)>0)
  { // mentor group(s) found, setup the array
    for($mc=0;$mc<mysql_num_rows($sql_result);$mc++)
    {
      $mentor_group[$mc] = mysql_result($sql_result,$mc,'groupname');
      $mentor_n++;
    }
  }
  mysql_free_result($sql_result);

  // Setup an array /w normal groups which have a class with the teacher

  $sql_query = "SELECT DISTINCT subject.fullname,sgroup.groupname FROM class,subject,sgroup";
  $sql_query .= " WHERE active=1 AND class.mid=subject.mid AND class.gid=sgroup.gid AND class.tid='" . $uid . "'";
  $sql_query .= " ORDER BY subject.fullname,sgroup.groupname";
  $sql_result = mysql_query($sql_query,$userlink);
  $class_n = 0;
  if (mysql_num_rows($sql_result)>0)
  { // mentor group(s) found, setup the array
    for($cc=0;$cc<mysql_num_rows($sql_result);$cc++)
    {
      $class_group['groupname'][$cc] = mysql_result($sql_result,$cc,'groupname');
      $class_group['subject'][$cc] = mysql_result($sql_result,$cc,'fullname');
      $class_n++;
    }
  }
  mysql_free_result($sql_result);

  // If the teacher is an administrator or counseller, setup an array /w all groups
  $group_n = 0;
  if($LoginType == "A" || $LoginType == "C")
  {
    $sql_query = "SELECT groupname FROM sgroup WHERE active=1 ORDER BY groupname";
    $sql_result = mysql_query($sql_query,$userlink);
    if (mysql_num_rows($sql_result)>0)
    { // group(s) found, setup the array
      for($gc=0;$gc<mysql_num_rows($sql_result);$gc++)
      {
        $group_group[$gc] = mysql_result($sql_result,$gc,'groupname');
        $group_n++;
      }
    }
    mysql_free_result($sql_result);
  }


  SA_closeDB();

  echo("<html><title>" . $dtext['selgrp_title'] . "</title>");
  echo("<body background=schooladminbg.jpg>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2>");
  echo("<center><b>" . $dtext['selgrp_title'] . "</b><p>");
  echo("<br><div>" . $dtext['selgrp_expl_1'] . "</div></font><br>");

  $and = " " . $dtext['and'] . " ";
  $comma = ", ";
  $dot = ".<br>";

  echo("<div align=left><font size=+1>");
  // Show mentor groups
  if($mentor_n > 0)
  { // Show a message with each group mentored
    echo($dtext['selgrp_expl_2'] . " ");
    for($mc=0;$mc<$mentor_n;$mc++)
    {
      show_link($mentor_group[$mc]);
      if($mc < ($mentor_n - 2))
        echo($comma);
      if($mc == ($mentor_n - 2))
        echo($and);
      if($mc == ($mentor_n - 1))
        echo($dot);
    }
  }

  // Show normal groups for classes
  if($class_n > 0)
  { // Show each subject with the groups involved 
    $cc=0;
    while($cc<$class_n)
    { 
      $current_subject = $class_group['subject'][$cc];
      echo($dtext['selgrp_expl_3'] . " " . $current_subject . " " . $dtext['2group'] . " ");
      while(($cc < $class_n) && $class_group['subject'][$cc] == $current_subject)
      {
        show_link($class_group['groupname'][$cc]);
        if(($cc + 2) < $class_n && $class_group['subject'][$cc + 2] == $current_subject)
          echo($comma);
        else if(($cc + 1) < $class_n && $class_group['subject'][$cc + 1] == $current_subject)
          echo($and);
        else
          echo($dot);
        $cc++;
      }
    }
  }

  // Show all groups (mentor or counseller)
  if($group_n > 0)
  { // Show a message with each group mentored
    echo($dtext['selgrp_expl_4'] . " ");
    if($LoginType == "C")
      echo($dtext['Counsellor']);
    else
      echo($dtext['Administrator']);
    echo(" " . $dtext['selgrp_expl_5'] . " ");
    for($gc=0;$gc<$group_n;$gc++)
    {
      show_link($group_group[$gc]);
      if($gc < ($group_n - 2))
        echo($comma);
      if($gc == ($group_n - 2))
        echo($and);
      if($gc == ($group_n - 1))
        echo($dot);
    }
  }
  echo("</font></div>");

  echo("</body></html>");
  exit;

function show_link($groupname)
{
  global $ReturnTo;
  echo("<a href='" . $ReturnTo ."?NewGroup=" . $groupname . "'>" . $groupname . "</a>");
}

?>



