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

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];

  $uid = intval($uid);

  // First we get all the data from existing test types in an array.
  $sql_query = "SELECT * FROM testtype";
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
  SA_closeDB();

  // First part of the page
  echo("<html><head><title>" . $dtext['manttyp_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['manttyp_title'] . "</font><p>");
  echo '<a href="admin.php">';
  echo($dtext['back_admin'] . "</a><br>");
  echo("<br><div align=left>" . $dtext['manttyp_expl_1']);
  echo("<br>" . $dtext['manttyp_expl_2']);
  echo(" " . $dtext['manttyp_expl_3']);
  echo("<br>" . $dtext['manttyp_expl_4'] . "</dev><br><br>");
  echo("<table border=1 cellpadding=0>");
  
  // Create the heading row for the table
  echo("<tr><td><center>" . $dtext['Type'] . "</td>");
  echo("<td><center>" . $dtext['Description'] . "</td>");
  echo("<td></td>");
  echo("<td></td></font></tr>");

  // Create a row in the table for every existing test type
  for($r=1;$r<=$row_n;$r++)
  {
    echo("<tr><form method=post action=updtesttype.php name=ut". $r. ">");

    echo("<td><center><input type=hidden name=new value=no><input type=hidden name='type' value=" . $grade_array['type'][$r] .">");
    echo($grade_array['type'][$r]."</td>");
    // Add the description
    echo("<td><center><input type=text size=20 name=translation value='" . $grade_array['translation'][$r]."'></td>");
    // Add the change button
    //echo("<td><center><input type=submit value=" . $dtext['Change'] . "></td></form>");
    echo("<td><center><img src='PNG/action_check.png' title='". $dtext['Change']. "' onclick='document.ut". $r. ".submit();'></td></form>");
    // Add the delete button
    echo("<form method=post action=deltesttype.php name=dt". $r. "><input type=hidden name='type' value='");
    echo($grade_array['type'][$r]."'");
    //echo("><td><center><input type=submit value=" . $dtext['Delete'] . "></td></form></tr>");
    echo("><td><center><img src='PNG/action_delete.png' title='". $dtext['Delete']. "' onclick='if(confirm(\"". $dtext['manttyp_expl_4']. "\")) { document.dt". $r. ".submit(); }'></td></form></tr>");
  }
  // Insert the row for a new test type
  echo("<tr><form method=post action=updtesttype.php name=newt>");

  echo("<td><center><input type=hidden name=new value=yes><input type=text size=7 name='type'</td>");
  echo("<td><center><input type=text size=20 name=translation value=\"\"></td>");
  // Add the ADD button
  //echo("<td><center><input type=submit value=" . $dtext['ADD_CAP'] . "></td></form>");
  echo("<td><center><img src='PNG/action_add.png' title='". $dtext['ADD_CAP']. "' onclick='document.newt.submit();'></td></form>");
  // Here we don't have a delete button!
  echo("<td></td></tr>");
  
  // close the table
  echo("</table></html>");

?>
