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

  $login_qualify = 'A';
  include ("schooladminfunctions.php");
  require_once("inputlib/inputclasses.php");
  require_once("teacher.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];

  $uid = intval($uid);

  // First we get all the data from existing teachers in an array.
  inputclassbase::dbconnect($userlink);
  $teachers = teacher::teacher_list();
  
  // First part of the page
  echo("<html><head><title>" . $dtext['manteach_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['manteach_title'] . "</font><p>");
  echo '<a href="admin.php">';
  echo($dtext['back_admin'] . "</a><br>");
  echo("<br><div align=left>" . $dtext['manteach_expl_1']);
  echo("<br>" . $dtext['manteach_expl_2'] . "</dev><br>");
  echo("<table border=1 cellpadding=0>");
  
  // Create the heading row for the table
  echo("<tr><td><center><font size=-1>ID</td>");
  echo("<td><center><font size=-1>" . $dtext['Lastname'] . "</td>");
  echo("<td><center><font size=-1>" . $dtext['Firstname'] . "</td>");
  echo("<td><center><font size=-1>" . $dtext['Password'] . "</td>");
  echo("<td><center><font size=-1>" . $dtext['Administrator'] . "</td>");
  echo("<td><center><font size=-1>" . $dtext['Counsellor'] . "</td>");
  echo("<td><center><font size=-1>" . $dtext['Abs_admin'] . "</td>");
  echo("<td><center><font size=-1>" . $dtext['Office_admin'] . "</td>");
  echo("<td><center><font size=-1>" . $dtext['Is_gone'] . "</td>");
  echo("<td></td></font></tr>");

  // Create a row in the table for every existing teacher
  if(isset($teachers))
  foreach($teachers AS $teach)
  {
    echo("<tr><form method=post action=updteacher.php name=ut". $teach->get_id(). ">");

    echo("<td><center><input type=hidden size=4 name=tid value=" . $teach->get_id() ."><font size=-1>" . $teach->get_id() ."</td>");
    echo("<td><center><input type=text size=20 name=lastname value=\"" . $teach->get_teacher_detail("*teacher.lastname") ."\"></td>");
    echo("<td><center><input type=text size=20 name=firstname value=\"" . $teach->get_teacher_detail("*teacher.firstname") ."\"></td>");
    echo("<td><center><input type=password size=12 name=password value=\"");
    if($encryptedpasswords != 1)
      echo($teach->get_password());
    echo("\"></td>");
    if($teach->has_role("admin")) {$YesChecked = "checked"; $NoChecked = "";}
    else {$YesChecked = ""; $NoChecked = "checked";}
    echo("<td><center><input type=radio name=is_admin value=Y " . $YesChecked.">" . $dtext['y']);
    echo("<input type=radio name=is_admin value=N " . $NoChecked.">" . $dtext['n'] . "</td>");
    if($teach->has_role("counsel")) {$YesChecked = "checked"; $NoChecked = "";}
    else {$YesChecked = ""; $NoChecked = "checked";}
    echo("<td><center><input type=radio name=is_counsel value=Y " . $YesChecked.">" . $dtext['y']);
    echo("<input type=radio name=is_counsel value=N " . $NoChecked.">" . $dtext['n'] . "</td>");
    if($teach->has_role("arman")) {$YesChecked = "checked"; $NoChecked = "";}
    else {$YesChecked = ""; $NoChecked = "checked";}
    echo("<td><center><input type=radio name=is_arman value=Y " . $YesChecked.">" . $dtext['y']);
    echo("<input type=radio name=is_arman value=N " . $NoChecked.">" . $dtext['n'] . "</td>");
    if($teach->has_role("office")) {$YesChecked = "checked"; $NoChecked = "";}
    else {$YesChecked = ""; $NoChecked = "checked";}
    echo("<td><center><input type=radio name=is_office value=Y " . $YesChecked.">" . $dtext['y']);
    echo("<input type=radio name=is_office value=N " . $NoChecked.">" . $dtext['n'] . "</td>");
    if($teach->is_gone()) {$YesChecked = "checked"; $NoChecked = "";}
    else {$YesChecked = ""; $NoChecked = "checked";}
    echo("<td><center><input type=radio name=is_gone value=Y " . $YesChecked.">" . $dtext['y']);
    echo("<input type=radio name=is_gone value=N " . $NoChecked.">" . $dtext['n'] . "</td>");
    // Last collumn contains the submit button and ends the form

    //echo("<td><center><input type=submit value=DO></td></form></tr>");
    echo("<td><center><img src='PNG/action_check.png' title='". $dtext['Edit']. "' onclick='document.ut". $teach->get_id(). ".submit();'></td></form></tr>");
  }
  // Insert the row for a new teacher
  echo("<tr><form method=post action=updteacher.php name=newt>");
  echo("<td><center><input type=hidden size=4 name=tid value=\"\">-</td>");
  echo("<td><center><input type=text size=20 name=lastname></td>");
  echo("<td><center><input type=text size=20 name=firstname></td>");
  echo("<td><center><input type=password size=12 name=password></td>");
  echo("<td><center><input type=radio name=is_admin value=Y>" . $dtext['y']);
  echo("<input type=radio name=is_admin value=N checked>" . $dtext['n'] . "</td>");
  echo("<td><center><input type=radio name=is_counsel value=Y>" . $dtext['y']);
  echo("<input type=radio name=is_counsel value=N checked>" . $dtext['n'] . "</td>");
  echo("<td><center><input type=radio name=is_arman value=Y>" . $dtext['y']);
  echo("<input type=radio name=is_arman value=N checked>" . $dtext['n'] . "</td>");
  echo("<td><center><input type=radio name=is_office value=Y>" . $dtext['y']);
  echo("<input type=radio name=is_office value=N checked>" . $dtext['n'] . "</td>");
  echo("<td><center><input type=radio name=is_gone value=Y>" . $dtext['y']);
  echo("<input type=radio name=is_gone value=N checked>" . $dtext['n'] . "</td>");
  echo("<td><center><img src='PNG/action_add.png' title='". $dtext['ADD_CAP']. "' onclick='document.newt.submit();'></td></form></tr>");
  
  // close the table
  echo("</table></html>");

?>
