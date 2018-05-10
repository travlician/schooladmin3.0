<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2014 Aim4me N.V.   (http://www.aim4me.info)		      |
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

  // First we get all the data from adds in an array.
  $sql_query = "SELECT * FROM adds ORDER BY position";
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
  SA_closeDB();
  $row_n = $nrows;

  // First part of the page
  echo("<html><head><title>" . $dtext['adds_setup_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['adds_setup_title'] . "</font><p>");
  echo '<a href="admin.php">';
  echo($dtext['back_admin'] . "</a><br>");
  echo("<br><div align=left>" . $dtext['adds_setup_expl_1']);
  echo(" " . $dtext['adds_setup_expl_2'] . "</dev><br>");
  echo("<table border=1 cellpadding=0>");
  
  // Create the heading row for the table
  echo("<tr><td><center>" . $dtext['Position'] . "</td>");
  echo("<td><center>" . $dtext['Description'] . "</td>");
  echo("<td><center>" . $dtext['HTML'] . "</td>");
  echo("<td></td></font></tr>");

  // Create a row in the table for every existing add position
  for($r=1;$r<=$row_n;$r++)
  {
    echo("<tr><form method=post action=updadd.php name=ua". $r. ">");

    echo("<td><input type=hidden name=position value=" . $grade_array['position'][$r] .">" .$grade_array['position'][$r]. "</td>");
    echo("<TD><input type=text size=60 name=description value=\"" . $grade_array['description'][$r] ."\"></td>");
    echo("<TD><TEXTAREA name=HTML rows=2 cols=60>" . $grade_array['HTML'][$r] ."</TEXTAREA></td>");
    //echo("<td><center><input type=submit value=" . $dtext['Change'] . "></td></form>");
    echo("<td><center><img src='PNG/action_check.png' title='". $dtext['Change']. "' onclick='document.ua". $r. ".submit();'></td></form>");
    echo("</tr>");
  }
  // Insert the row for a new add position
  echo("<tr><form method=post action=updadd.php name=newa>");
  echo("<td><input type=hidden name=position value=\"\">-</td>");
  echo("<td><input type=text size=60 name=description value=\"\"></td>");
  echo("<td><TEXTAREA name=HTML rows=2 cols=60></TEXTAREA></td>");
  // Add the ADD button
  //echo("<td><center><input type=submit value=" . $dtext['ADD_CAP'] . "></td></form></tr>");
  echo("<td><center><img src='PNG/action_add.png' title='". $dtext['ADD_CAP']. "' onclick='document.newa.submit();'></td></form></tr>");
  
  // close the table
  echo("</table>");

  // Close the page
  echo("</html>");

?>
