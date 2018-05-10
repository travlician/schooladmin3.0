<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 1.0                                           |
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

  $login_qualify = 'A';
  include ("schooladminfunctions.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  if(isset($_SESSION['CurrentGroup']))
    $CurrentGroup = $_SESSION['CurrentGroup'];
  
  $uid = intval($uid);

  // First we get all the data for students in an array.
  if(isset($CurrentGroup))
    $sql_query = "SELECT student.* FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) WHERE active=1 AND sgroup.groupname='$CurrentGroup' GROUP BY sid ORDER BY lastname,firstname";
  else
    $sql_query = "SELECT student.* FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) WHERE sgroup.groupname IS NULL GROUP BY sid ORDER BY lastname,firstname";
  //echo $sql_query;
  $grade_array = SA_loadquery($sql_query);
  
  // Get the groups for each student in an array
  if(isset($CurrentGroup))
  {
    $grpsq = "SELECT sgrouplink.sid,sgrouplink.gid,groupname FROM sgrouplink LEFT JOIN (SELECT sid,gid,groupname FROM sgrouplink LEFT JOIN sgroup USING(gid) WHERE active=1 AND sgroup.groupname='$CurrentGroup') AS t1  USING(sid)";
    $grpsqr = SA_loadquery($grpsq);
    if(isset($grpsqr))
      foreach($grpsqr['sid'] AS $gix => $sidg)
        $grps[$sidg][$grpsqr['gid'][$gix]] = $grpsqr['groupname'][$gix];
  }
  // Create a separate array with the groups
  $group_array = SA_loadquery("SELECT gid,groupname FROM sgroup WHERE active=1 ORDER BY groupname");
  SA_closeDB();

  // First part of the page
  echo("<html><head><title>" . $dtext['stuman_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['stuman_title'] . "</font><p>");
  echo '<a href="admin.php">';
  echo($dtext['back_admin'] . "</a><br>");
  echo("<br><div align=left>" . $dtext['stuman_expl_1']);
  echo("<br>" . $dtext['stuman_expl_2']);
  if(!isset($CurrentGroup))
  {
    echo("<br>" . $dtext['stuman_expl_3']);
    echo(" " . $dtext['stuman_expl_4']);
  }
  echo("</div><br>");

  // Show for which group current editing and allow changing the group
  echo("<form method=post action=chngrp4studman.php>" . $dtext['stuman_expl_5'] . " <select name=NewGroup>");
  echo("<option value=''>-</option>");
  foreach($group_array['groupname'] AS $gname)
  { // Add an option for each group, select the one currently active
    if(isset($CurrentGroup) && $CurrentGroup == $gname)
      $IsSelected = " selected";
    else
      $IsSelected = "";
    echo("<option value='" . $gname. "' $IsSelected>" . $gname."</option>");
  }
  echo("</SELECT> <input type=submit value=" . $dtext['Change'] . "></form>");
  
  // Create the heading row for the table
  echo("<table border=1 cellpadding=0>");
  echo("<tr><td><center>#</td>");
  echo("<td><center>ID</td>");
  echo("<td><center>" . $dtext['Lastname'] . "</td>");
  echo("<td><center>" . $dtext['Firstname'] . "</td>");
  echo("<td". (isset($hidegrouponstudentcreate) ? " style='display: none'" : ""). "><center>" . $dtext['Group_Cap'] . "</td>");
  echo("<td><center>" . $dtext['Password'] . " ". $dtext['Student']. "</td>");
  echo("<td><center>" . $dtext['Password'] . " ". $dtext['Parent']. "</td>");
  if(isset($CurrentGroup))
    echo("<td></td>");
  echo("<td></td></font></tr>");

  // Create a row in the table for every existing student
  $r=1;
  if(isset($grade_array))
  foreach($grade_array['sid'] AS $r => $asid)
  {
    echo("<tr><form method=post action=updstudent.php name=us". $r. ">");

    echo("<td>". $r. "</td><td><center><input type=hidden name=sid value=" . $grade_array['sid'][$r] .">");
    if($altsids == 1)
      echo("<input type=text size=16 name=altsid value='" . $grade_array['altsid'][$r]."'></td>");
    else
      echo($grade_array['sid'][$r]."</td>");
    // Add the names
    echo("<td><input type=text size=20 name=lastname value=\"" . $grade_array['lastname'][$r]."\"></td>");
    echo("<td><input type=text size=20 name=firstname value=\"" . $grade_array['firstname'][$r]."\"></td>");
    // Add the group
    echo("<td". (isset($hidegrouponstudentcreate) ? " style='display: none'" : ""). "><select name=gid[] multiple='multiple'>");
    foreach($group_array['gid'] AS $gix => $agid)
    { // add each group!
      if(isset($grps[$grade_array['sid'][$r]][$agid])) $SelectInd = "selected";
      else $SelectInd = "";
      echo("<option value=" . $agid. " " . $SelectInd.">");
      echo($group_array['groupname'][$gix]);
      echo("</option>");
    }
    echo("</select></td>");
    // Add the pasword (visible for the administrator if not encrypted!)
    echo("<td><input type=text size=20 name=password value='");
    if($encryptedpasswords != 1)
      echo($grade_array['password'][$r]);
    echo("'></td>");
    echo("<td><input type=text size=20 name=ppassword value='");
    if($encryptedpasswords != 1)
      echo($grade_array['ppassword'][$r]);
    echo("'></td>");

    // Add the change button
    //echo("<td><center><input type=submit value=" . $dtext['Change'] . "></td></form>");
    echo("<td><center><img src='PNG/action_check.png' title='". $dtext['Change']. "' onclick='document.us". $r. ".submit();'></td></form>");
    // Add the delete button
	if(!isset($CurrentGroup))
	{
      echo("<form method=post action=delstudent.php name=ds". $r. "><input type=hidden name=sid value=");
      echo($grade_array['sid'][$r]);
      //echo("><td><center><input type=submit value=" . $dtext['Delete'] . "></td></form></tr>");
      echo("><td><center><img src='PNG/action_delete.png' title='". $dtext['Delete']. "' onclick='document.ds". $r. ".submit();'></td>");
	}
	echo("</form></tr>");
  }
  // Insert the row for a new student
  echo("<tr><form method=post action=updstudent.php name=newstud>");

  echo("<td>". $r. "</td><td><center><input type=hidden name=sid value=\"\">");
  if($altsids == 1)
    echo("<input type=text size=16 name=altsid></td>");
  else
    echo("-</td>");
  // Add the names
  echo("<td><input type=text size=20 name=lastname></td>");
  echo("<td><input type=text size=20 name=firstname></td>");

  // group id (gid) must also be added hidden but we need to get it first!
  echo("<td". (isset($hidegrouponstudentcreate) ? " style='display: none'" : ""). ">");
  foreach($group_array['groupname'] AS $gc => $gn)
  {
     if(isset($CurrentGroup) && $gn == $CurrentGroup)
       echo("<input type=hidden name=gid value=" . $group_array['gid'][$gc].">");
  }
  // Do show the current groupname as normal text!
  if(isset($CurrentGroup))
    echo($CurrentGroup);
  else
    echo("-");
  echo("</td>");
  // Add the paswords (visible for the administrator!)
  echo("<td><input type=text size=20 name=password></td>");
  echo("<td><input type=text size=20 name=ppassword></td>");
  // Add the ADD button
  //echo("<td><center><input type=submit value=" . $dtext['ADD_CAP'] . "></td></form>");
  echo("<td><center><img src='PNG/action_add.png' title='". $dtext['ADD_CAP']. "' onclick='document.newstud.submit();'></td></form>");
  // Here we don't have a delete button! But we do need to show a cell
  if(!isset($CurrentGroup))
  echo("<td></td>");
  echo("</tr>");
  // close the table
  echo("</table></html>");

?>
