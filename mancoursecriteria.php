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

  $login_qualify = 'A';
  require_once("schooladminfunctions.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];

  $uid = intval($uid);

  // First we get all the data from existing course pass criteria records in an array.
  $sql_query = "SELECT * FROM coursepasscriteria ORDER BY masterlink";
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

  // Create a separate array with the masterlinks
  $sql_query = "SELECT DISTINCT masterlink FROM class GROUP BY masterlink";
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
       $masterlink[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $masterlink_n = $nrows;

  SA_closeDB();

  // First part of the page
  echo("<html><head><title>" . $dtext['cpcman_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['cpcman_title'] . "</font><p>");
  echo '<a href="admin.php">';
  echo($dtext['back_admin'] . "</a><br>");
  echo("<br><div align=left>" . $dtext['cpcman_expl_1']);
  echo("<br>" . $dtext['cpcman_expl_2'] . "</dev><br><br>");
  echo("<table border=1 cellpadding=0>");

  // Create the heading row for the table
  echo("<tr><td><center>" . $dtext['spec_class'] . "</td>");
  echo("<td><center>" . $dtext['dig_after_dot'] . "(". $dtext['Final']. ")</td>");
  echo("<td><center>" . $dtext['dig_after_dot'] . "(". $dtext['Period']. ")</td>");
  echo("<td><center>" . $dtext['pass_grade'] . "</td>");
  echo("<td><center>" . $dtext['max_fail_subs'] . "</td>");
  echo("<td><center>" . $dtext['min_pt_bal'] . "</td>");
  echo("<td></td>");
  echo("<td></td></font></tr>");

  // Create a row in the table for every existing coursepasscriteria record
  for($r=1;$r<=$row_n;$r++)
  {
    echo("<tr><form method=post action=updcoursecriteria.php name=uc". $r. ">");
    // Insert the original masterlink as hidden to be able to delete b4 reinsert.
    echo("<td><center><input type=hidden name=new value=no>");
    echo("<input type=hidden name=masterlink value='" . $grade_array['masterlink'][$r]."'>");
    echo($grade_array['masterlink'][$r]."</td>");
    
    // Add the parameter fields
    echo("<td><input type=text size=3 name=digitsafterdotfinal value=\"" . $grade_array['digitsafterdotfinal'][$r] ."\"></td>");
    echo("<td><input type=text size=3 name=digitsafterdotperiod value=\"" . $grade_array['digitsafterdotperiod'][$r] ."\"></td>");
    echo("<td><input type=text size=4 name=minimumpass value=\"" . $grade_array['minimumpass'][$r] ."\"></td>");
    echo("<td><input type=text size=4 name=maxfails value=\"" . $grade_array['maxfails'][$r] ."\"></td>");
    echo("<td><input type=text size=5 name=minpasspointbalance value=\"" . $grade_array['minpasspointbalance'][$r] ."\"></td>");

    // Add the change button
    //echo("<td><center><input type=submit value=" . $dtext['Change'] . "></td></form>");
    echo("<td><center><img src='PNG/action_check.png' title='". $dtext['Change']. "' onclick='document.uc". $r. ".submit();'></td></form>");
    // Add the delete button
    echo("<form method=post action=delcoursecriteria.php name=dc". $r. "><input type=hidden name=masterlink value=");
    echo($grade_array['masterlink'][$r]);
    //echo("><td><center><input type=submit value=" . $dtext['Delete'] . "></td></form></tr>");
    echo("><td><center><img src='PNG/action_delete.png' title='". $dtext['Delete']. "' onclick='if(confirm(\"". $dtext['confirm_delete']. "\")) { document.dc". $r. ".submit(); }'></td></form></tr>");
  }
  // Insert the row for a new coursepascriteria record
  echo("<tr><form method=post action=updcoursecriteria.php name=newc>");
  // The masterlink needs to be selected from a list!
  echo("<td><center><input type=hidden name=new value=yes><select name=masterlink>");
  for($mc=1;$mc<=$masterlink_n;$mc++)
  {
    echo("<option value=" . $masterlink['masterlink'][$mc].">" . $masterlink['masterlink'][$mc]."</option>");
  }
  echo("</select></td>");
  // Add the parameter fields
  echo("<td><input type=text size=3 name=digitsafterdotfinal value=0></td>");
  echo("<td><input type=text size=3 name=digitsafterdotperiod value=0></td>");
  echo("<td><input type=text size=4 name=minimumpass value=\"\"></td>");
  echo("<td><input type=text size=4 name=maxfails value=\"\"></td>");
  echo("<td><input type=text size=5 name=minpasspointbalance value=\"\"></td>");

  // Add the ADD button
  //echo("<td><center><input type=submit value=" . $dtext['ADD_CAP'] . "></td></form>");
  echo("<td><center><img src='PNG/action_add.png' title='". $dtext['ADD_CAP']. "' onclick='document.newc.submit();'></td></form>");
  // Here we don't have a delete button!
  echo("<td></td></tr>");
  
  // close the table
  echo("</table>");

  // Add the explanations for the fields
  echo("<br><div align=left>");
  echo("<br><u>" . $dtext['dig_after_dot'] . "</u>: " . $dtext['cpcman_expl_3']);
  echo("<br><u>" . $dtext['pass_grade'] . "</u>: " . $dtext['cpcman_expl_4']);
  echo("<br><u>" . $dtext['max_fail_subs'] . "</u>: " . $dtext['cpcman_expl_5']);
  echo("<br><u>" . $dtext['min_pt_bal'] . "</u>: " . $dtext['cpcman_expl_6']);
  echo(" " . $dtext['cpcman_expl_7']);
  echo(" " . $dtext['cpcman_expl_8']);
  
  echo("</div></html>");

?>
