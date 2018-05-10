<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2016 Aim4me N.V.   (http://www.aim4me.info)	      |
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
  require_once("absencecategory.php");

  $login_qualify = 'A';
  include ("schooladminfunctions.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];

  $uid = intval($uid);

  // First we get all the data from absence reasons in an array.
  $absreasons = SA_loadquery("SELECT * FROM absencereasons ORDER BY acid,description");
  $abscats = SA_loadquery("SELECT * FROM absencecats ORDER BY acid");
  SA_closeDB();

  // First part of the page
  echo("<html><head><title>" . $dtext['abs_setup_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['abs_setup_title'] . "</font><p>");
  echo '<a href="admin.php">';
  echo($dtext['back_admin'] . "</a><br>");
  echo("<br><div align=left>" . $dtext['abs_setup_expl_1']);
  echo(" " . $dtext['abs_setup_expl_2']);
  echo(" " . $dtext['abs_setup_expl_3']);
  echo(" " . $dtext['abs_setup_expl_4'] . "</dev><br>");
  echo("<BR><table border=1 cellpadding=0>");
  
  // Create the heading row for the table
  echo("<tr><th><center>". $dtext['Category']. "</th><th><center>". $dtext['Category']. " ". $dtext['Description'] . "</th>");
  echo("<th><center>". $dtext['Image']. "</th><th><center>". $dtext['Single']. " ". $dtext['Subject']. "</th>");
  echo("<th>". $dtext['CountAbs']. "</th>");
  echo("<th>". $dtext['On_studgui']. "</th>");
  echo("<th>". $dtext['W_acc']. "</th>");
  echo("<th></th><th></th></font></tr>");

  // Create a row in the table for every existing absence reason
  if(isset($abscats['acid']))
  foreach($abscats['acid'] AS $acix => $acid)
  {
    echo("<tr><form method=post action=updabscat.php name=uca". $acix. ">");
    echo("<td>". $acid. "</td>");
    echo("<td><input type=hidden name=acid value=" . $acid .">");
    echo("<input type=text size=20 name=name value=\"" . $abscats['name'][$acix] ."\"></td>");
    echo("<td><input type=text size=20 name=image value=\"" . $abscats['image'][$acix] ."\"></td>");
    echo("<td><SELECT name=classuse><OPTION value=0". ($abscats['classuse'][$acix] == 0 ? " SELECTED" : ""). ">". $dtext['No']. "</option><OPTION value=1". ($abscats['classuse'][$acix] == 1 ? " SELECTED" : ""). ">". $dtext['Yes']. "</option></select></td>");
    echo("<td><SELECT name=countabs><OPTION value=0". ($abscats['countabs'][$acix] == 0 ? " SELECTED" : ""). ">". $dtext['No']. "</option><OPTION value=1". ($abscats['countabs'][$acix] == 1 ? " SELECTED" : ""). ">". $dtext['Yes']. "</option></select></td>");
    echo("<td><SELECT name=ongui><OPTION value=0". ($abscats['ongui'][$acix] == 0 ? " SELECTED" : ""). ">". $dtext['No']. "</option><OPTION value=1". ($abscats['ongui'][$acix] == 1 ? " SELECTED" : ""). ">". $dtext['Yes']. "</option></select></td>");
    echo("<td><center><select name=waccess>");
    echo("<option value='A' ". (($abscats['waccess'][$acix]=="A") ? " selected" : "") . ">" . $dtext['allow_all_short'] . "</option>");
    echo("<option value='T' ". (($abscats['waccess'][$acix]=="T") ? " selected" : "") . ">" . $dtext['allow_teach_short'] . "</option>");
    echo("<option value='M' ". (($abscats['waccess'][$acix]=="M") ? " selected" : "") . ">" . $dtext['allow_ment_short'] . "</option>");
    echo("<option value='C' ". (($abscats['waccess'][$acix]=="C") ? " selected" : "") . ">" . $dtext['allow_couns_short'] . "</option>");
    echo("<option value='O' ". (($abscats['waccess'][$acix]=="O") ? " selected" : "") . ">" . $dtext['Office_admin'] . "</option>");
    echo("<option value='P' ". (($abscats['waccess'][$acix]=="P") ? " selected" : "") . ">" . $dtext['allow_ment_office'] . "</option>");
    echo("<option value='N' ". (($abscats['waccess'][$acix]=="N") ? " selected" : "") . ">" . $dtext['allow_none'] . "</option>");
    echo("</select></td>");
    echo("<td><center><img src='PNG/action_check.png' title='". $dtext['Change']. "' onclick='document.uca". $acix. ".submit();'></td></form>");
    // Add the delete button
    echo("<form method=post action=delabscat.php name=dca". $acix. "><input type=hidden name=acid value=");
    echo($acid);
    echo("><td><center><img src='PNG/action_delete.png' title='". $dtext['Delete']. "' onclick='if(confirm(\"". $dtext['confirm_delete']. "\")) { document.dca". $acix. ".submit(); }'></td></form></tr>");
  }
  // Insert the row for a new category
  echo("<tr><form method=post action=updabscat.php name=newca>");
  echo("<td>&nbsp;</td>");
  echo("<td><input type=hidden name=acid value=\"\">");
  echo("<input type=text size=20 name=name value=\"\"></td>");
  echo("<td><input type=text size=20 name=image value=''></td>");
  echo("<td><SELECT name=classuse><option value=0>". $dtext['No']. "</option><option value=1>". $dtext['Yes']. "</option></select></td>");
  echo("<td><SELECT name=countabs><option value=0>". $dtext['No']. "</option><option value=1 selected>". $dtext['Yes']. "</option></select></td>");
  echo("<td><SELECT name=ongui><option value=0>". $dtext['No']. "</option><option value=1 selected>". $dtext['Yes']. "</option></select></td>");
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

  echo("<BR><table border=1 cellpadding=0>");
  
  // Create the heading row for the table
  echo("<tr><th><center>" . $dtext['Description'] . "</th><th><center>". $dtext['Category']. "</th>");
  echo("<th></th>");
  echo("<th></th></font></tr>");

  // Create a row in the table for every existing absence reason
  if(isset($absreasons['aid']))
  foreach($absreasons['aid'] AS $aix => $aid)
  {
    echo("<tr><form method=post action=updabsreason.php name=ua". $aix. ">");

    echo("<td><input type=hidden name=aid value=" . $aid .">");
    echo("<input type=text size=60 name=description value=\"" . $absreasons['description'][$aix] ."\"></td>");
    echo("<td><input type=text size=2 name=category value=\"" . $absreasons['acid'][$aix] ."\"></td>");
    echo("<td><center><img src='PNG/action_check.png' title='". $dtext['Change']. "' onclick='document.ua". $aix. ".submit();'></td></form>");
    // Add the delete button
    echo("<form method=post action=delabsreason.php name=da". $aix. "><input type=hidden name=aid value=");
    echo($aid);
    echo("><td><center><img src='PNG/action_delete.png' title='". $dtext['Delete']. "' onclick='if(confirm(\"". $dtext['confirm_delete']. "\")) { document.da". $aix. ".submit(); }'></td></form></tr>");
  }
  // Insert the row for a new subject
  echo("<tr><form method=post action=updabsreason.php name=newa>");
  echo("<td><input type=hidden name=aid value=\"\">");
  echo("<input type=text size=60 name=description value=\"\"></td>");
  echo("<td><input type=text size=2 name=category value=1></td>");
  // Add the ADD button
  echo("<td><center><img src='PNG/action_add.png' title='". $dtext['ADD_CAP']. "' onclick='document.newa.submit();'></td></form>");
  // Here we don't have a delete button!
  echo("<td></td></tr>");
  // close the table
  echo("</table>");

  // Put a form to delete old absence records.
  echo("<BR><form method=post action=purgeabsrecords.php><input type=text size=10 name=date value=");
  echo(@date('Y-m-d',mktime(0,0,0,date("m"),date("d"),date("Y")-1)) . "><input type=submit value=\"" . $dtext['Rem_absrecs'] . "\"");
  // Close the page
  echo("</html>");

?>
