<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 3.0                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2018 Aim4me N.V.   (http://www.aim4me.info)	      |
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
	
	// Create an array of possible send options
	$sendoptions = array('singlestudent','studentsgroup','singleparent','parentsgroup','singleteacher','administrators','office','mentors','absencemanagers','counselers','groupteachers','allteachers');
	
	$roleoptions = array("teacher","admin","counsel","arman","office","mentor");
	$roletxt['teacher'] = $dtext['Teacher'];
	$roletxt['admin'] = $dtext['Administrator'];
	$roletxt['counsel'] = $dtext['Counsellor'];
	$roletxt['arman'] = $dtext['Abs_admin'];
	$roletxt['office'] = $dtext['Office_admin'];
	$roletxt['mentor'] = $dtext['Mentor'];

  // First we get all the data from existing management of messages records in an array.
  $sql_query = "SELECT * FROM messagerights ORDER BY role,destination";
	$mrights = SA_loadquery($sql_query);

  SA_closeDB();

  // First part of the page
  echo("<html><head><title>" . $dtext['MessageRightsTitle'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['MessageRightsTitle'] . "</font><p>");
  echo '<a href="admin.php">';
  echo($dtext['back_admin'] . "</a><br>");
  echo("<table border=1 cellpadding=0>");

  // Create the heading row for the table
  echo("<tr><td><center>" . $dtext['MessageDestination'] . "</td>");
  echo("<td><center>" . $dtext['Role'] . "</td>");
  echo("<td></td>");
  echo("<td></td></font></tr>");

  // Create a row in the table for every existing reportcalc record
  foreach($mrights['destination'] AS $r => $mrdest)
  {
    echo("<tr><form method=post action=updmsgrights.php name=ug". $r. ">");
    // Insert the original destination and role as hidden to be able to delete b4 reinsert.
    echo("<td><input type=hidden name=orgdest value='" . $mrdest."'>");
    echo("<input type=hidden name=orgrole value='" . $mrights['role'][$r]."'>");
    // Insert the options for each destination
    echo("<center><select name=dest>");
    foreach($sendoptions AS $sendopt)
    {
      if($mrdest == $sendopt)
        $IsSelected = "selected";
      else
        $IsSelected = "";
      echo("<option value='" . $sendopt."' " . $IsSelected.">". $dtext["mesgdest_". $sendopt]. "</option>");
    }
    echo("</select></td>");

    // Insert the options for each role
    echo("<td><center><select name=role>");
    foreach($roleoptions AS $arole)
    {
      if($mrights['role'][$r] == $arole)
        $IsSelected = " selected";
      else
        $IsSelected = "";
      echo("<option value='" . $arole."'" . $IsSelected.">" . $roletxt[$arole]."</option>");
    }
    echo("</select></td>");
    
    // Add the change button
    //echo("<td><center><input type=submit value=" . $dtext['Change'] . "></td></form>");
    echo("<td><center><img src='PNG/action_check.png' title='". $dtext['Change']. "' onclick='document.ug". $r. ".submit();'></td></form>");
    // Add the delete button
    echo("<form method=post action=updmsgrights.php name=dg". $r. "><input type=hidden name=orgdest value=");
    echo($mrights['destination'][$r]);
    echo("><input type=hidden name=orgrole value=");
    echo($mrights['role'][$r]);
    //echo("><td><center><input type=submit value=" . $dtext['Delete'] . "></td></form></tr>");
    echo("><input type=hidden name=role value=''><input type=hidden name=dest value=''><td><center><img src='PNG/action_delete.png' title='". $dtext['Delete']. "' onclick='if(confirm(\"". $dtext['confirm_delete']. "\")) { document.dg". $r. ".submit(); }'></td></form></tr>");
  }
  // Insert the row for a new messagerights record
  echo("<tr><form method=post action=updmsgrights.php name=newg>");
  // Insert the original mid and testype as hidden to be able to delete b4 reinsert.
  echo("<td><input type=hidden name=orgdest value=\"\">");
  echo("<input type=hidden name=orgrole value=\"\">");
  // Insert the options for each destination
  echo("<center><select name=dest>");
  foreach($sendoptions AS $sendopt)
  {
    echo("<option value=" . $sendopt.">" . $dtext["mesgdest_". $sendopt]. "</option>");
  }
  echo("</select></td>");

  // Insert the options for each role
  echo("<td><center><select name=role>");
  foreach($roleoptions AS $arole)
  {
    echo("<option value='" . $arole."'>" . $roletxt[$arole]."</option>");
  }
  echo("</select></td>");
    
  // Add the ADD button
  //echo("<td><center><input type=submit value=" . $dtext['ADD_CAP'] . "></td></form>");
  echo("<td><center><img src='PNG/action_add.png' title='". $dtext['ADD_CAP']. "' onclick='document.newg.submit();'></td></form>");
  // Here we don't have a delete button!
  echo("<td></td></tr>");
  
  // close the table
  echo("</table>");

  
  echo("</div></html>");

?>
