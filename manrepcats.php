<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2016-2016 Aim4me N.V.   (http://www.aim4me.info)	      |
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
  require_once("reportcategory.php");

  $login_qualify = 'A';
  include ("schooladminfunctions.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];

  $uid = intval($uid);

  // First we get all the data from report categories in an array.
  $repcats = SA_loadquery("SELECT * FROM reportcats ORDER BY rcid");
  SA_closeDB();

  // First part of the page
  echo("<html><head><title>" . $dtext['ReportCats'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['ReportCats'] . "</font><p>");
  echo '<a href="admin.php">';
  echo($dtext['back_admin'] . "</a><br>");
  echo("<BR><table border=1 cellpadding=0>");
  
  // Create the heading row for the table
  echo("<tr><th><center>". $dtext['Category']. "</th><th><center>". $dtext['Category']. " ". $dtext['Description'] . "</th>");
  echo("<th>". $dtext['W_acc']. "</th>");
  echo("<th></th><th></th></font></tr>");

  // Create a row in the table for every existing reporting category
  if(isset($repcats['rcid']))
  foreach($repcats['rcid'] AS $rcix => $rcid)
  {
    echo("<tr><form method=post action=updrepcat.php name=uca". $rcix. ">");
    echo("<td>". $rcid. "</td>");
    echo("<td><input type=hidden name=rcid value=" . $rcid .">");
    echo("<input type=text size=20 name=name value=\"" . $repcats['name'][$rcix] ."\"></td>");
    echo("<td><center><select name=waccess>");
    echo("<option value='A' ". (($repcats['waccess'][$rcix]=="A") ? " selected" : "") . ">" . $dtext['allow_all_short'] . "</option>");
    echo("<option value='T' ". (($repcats['waccess'][$rcix]=="T") ? " selected" : "") . ">" . $dtext['allow_teach_short'] . "</option>");
    echo("<option value='M' ". (($repcats['waccess'][$rcix]=="M") ? " selected" : "") . ">" . $dtext['allow_ment_short'] . "</option>");
    echo("<option value='C' ". (($repcats['waccess'][$rcix]=="C") ? " selected" : "") . ">" . $dtext['allow_couns_short'] . "</option>");
    echo("<option value='O' ". (($repcats['waccess'][$rcix]=="O") ? " selected" : "") . ">" . $dtext['Office_admin'] . "</option>");
    echo("<option value='P' ". (($repcats['waccess'][$rcix]=="P") ? " selected" : "") . ">" . $dtext['allow_ment_office'] . "</option>");
    echo("<option value='N' ". (($repcats['waccess'][$rcix]=="N") ? " selected" : "") . ">" . $dtext['allow_none'] . "</option>");
    echo("</select></td>");
    echo("<td><center><img src='PNG/action_check.png' title='". $dtext['Change']. "' onclick='document.uca". $rcix. ".submit();'></td></form>");
    // Add the delete button
    echo("<form method=post action=delrepcat.php name=dca". $rcix. "><input type=hidden name=rcid value=");
    echo($rcid);
    echo("><td><center><img src='PNG/action_delete.png' title='". $dtext['Delete']. "' onclick='if(confirm(\"". $dtext['confirm_delete']. "\")) { document.dca". $rcix. ".submit(); }'></td></form></tr>");
  }
  // Insert the row for a new category
  echo("<tr><form method=post action=updrepcat.php name=newca>");
  echo("<td>&nbsp;</td>");
  echo("<td><input type=hidden name=rcid value=\"\">");
  echo("<input type=text size=20 name='name' value=\"\"></td>");
	echo("<td><center><select name=waccess>");
	echo("<option value='A'>" . $dtext['allow_all_short'] . "</option>");
	echo("<option value='T' SELECTED>" . $dtext['allow_teach_short'] . "</option>");
	echo("<option value='M'>" . $dtext['allow_ment_short'] . "</option>");
	echo("<option value='C'>" . $dtext['allow_couns_short'] . "</option>");
	echo("<option value='O'>" . $dtext['Office_admin'] . "</option>");
	echo("<option value='P'>" . $dtext['allow_ment_office'] . "</option>");
	echo("<option value='N'>" . $dtext['allow_none'] . "</option>");
	echo("</select></td>");
  // Add the ADD button
  echo("<td><center><img src='PNG/action_add.png' title='". $dtext['ADD_CAP']. "' onclick='document.newca.submit();'></td></form>");
  // Here we don't have a delete button!
  echo("<td></td></tr>");
  // close the table
  echo("</table>");

  // Close the page
  echo("</html>");

?>
