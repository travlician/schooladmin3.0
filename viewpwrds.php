<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
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

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];
  if(isset($HTTP_POST_VARS['NewGroup']) && SA_verifyGroupAccess($uid,$HTTP_POST_VARS['NewGroup']))
    $CurrentGroup = $HTTP_POST_VARS['NewGroup'];
  if(isset($HTTP_GET_VARS['NewGroup']) && SA_verifyGroupAccess($uid,$HTTP_GET_VARS['NewGroup']))
    $CurrentGroup = $HTTP_GET_VARS['NewGroup'];
  // Store the new group or future pages
  $_SESSION['CurrentGroup']=$CurrentGroup;
  
  $uid = intval($uid);

  // First we get the data from existing students in an array.
  $sql_query = "SELECT * FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING (gid) WHERE active=1 AND sgroup.groupname='$CurrentGroup' ORDER BY lastname,firstname";
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
  echo("<html><head><title>" . $dtext['stupw_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['stupw_title'] . "</font><p>");
  echo("<a href=teacherpage.php>" . $dtext['back_teach_page'] . "</a><br>");

  echo("<br><div align=left>" . $dtext['stupw_expl_1']);
  echo("<br>" . $dtext['stupw_expl_2']);
  echo("<br>" . $dtext['stupw_expl_3'] . "</dev><br>");

  // Show for which group current editing and allow changing the group
  echo($dtext['stupw_expl_4'] . " <b>$CurrentGroup</b> (<a href=selectgroup.php?ReturnTo=viewpwrds.php>" . $dtext['Change'] . "</a>)<br>");
  echo("<br>");

  // Set a flag to decide if the teacher can modify passwords for this group.
  $mayEdit = ($LoginType == "A" || $grade_array['tid_mentor'][1] == $uid);
    
  
  if($row_n > 0)
  {
    // Create the heading row for the table
    echo("<table border=1 cellpadding=0>");
    echo("<tr><td><center>" . $dtext['Lastname'] . "</td>");
    echo("<td><center>" . $dtext['Firstname'] . "</td>");
    echo("<td><center>" . $dtext['ID_CAP'] . "</td>");
    echo("<td><center>" . $dtext['Password'] . "</td>");
    if($mayEdit)
      echo("<td></td>");
    echo("</font></tr>");
  }
  else
    echo($dtext['stupw_expl_5'] . "<br><br>");

  // Create a row in the table for every existing report
  for($r=1;$r<=$row_n;$r++)
  {
    echo("<tr>");
    if($mayEdit)
    {
      echo("<form method=post action=changestudentpw.php name=cp". $r. ">");
      // Put in the hidden field for student id 
      echo("<td><input type=hidden name=sid value=" . $grade_array['sid'][$r] .">");
    }
    else
      echo("<td>");

    // Add lastname, firstname and password fields
    echo($grade_array['lastname'][$r] . "</td>");
    echo("<td>" . $grade_array['firstname'][$r] . "</td>");
    if($altsids == 1)
      echo("<td>" . $grade_array['altsid'][$r] . "</td>");
    else
      echo("<td>" . $grade_array['sid'][$r] . "</td>");
    if($mayEdit)
    {
      echo("<td><center><input type=text size=16 name=password value='");
      if($encryptedpasswords != 1)
        echo($grade_array['password'][$r]);
      echo("'></td>");
      //echo("<td><center><input type=submit value='" . $dtext['Change'] . "'></td></form></tr>");
      echo("<td><center><img src='PNG/action_check.png' title='". $dtext['Change']. "' onclick='document.cp". $r. ".submit();'></td></form></tr>");
    }
    else
    {
      if($encryptedpasswords == 1)
        echo("<td>*****</td></tr>");
      else
        echo("<td>". $grade_array['password'][$r] . "</td></tr>");
    }
  }
  echo("</table>");
  echo '<a href="teacherpage.php">';
  echo $dtext['back_teach_page'];
  echo '</a>';
 
  // close the page
  echo("</html>");

?>
