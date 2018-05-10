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

  $login_qualify = 'A';
  require_once("schooladminfunctions.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];
  
  if(isset($_POST['gid']))
  { // Reactive the given group
    mysql_query("UPDATE sgroup SET active=1 WHERE gid=". $_POST['gid'], $userlink);
  }
  
  $uid = intval($uid);

  // First we get all the data from existing groups in an array.
  $sql_query = "SELECT sgroup.*,COUNT(sid) AS nbstuds FROM sgroup LEFT JOIN sgrouplink USING(gid) GROUP BY gid ORDER BY groupname";
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

  // Create a separate array with the teachers
  $sql_query = "SELECT tid,lastname,firstname FROM teacher WHERE is_gone <> 'Y' ORDER BY lastname";
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
       $teach_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $teach_n = $nrows;
  SA_closeDB();

  // First part of the page
  echo("<html><head><title>" . $dtext['grpman_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['grpman_title'] . "</font><p>");
  echo '<a href="admin.php">';
  echo($dtext['back_admin'] . "</a><br>");
  echo("<br><div align=left>" . $dtext['grpman_expl_1']);
  echo("<br>" . $dtext['grpman_expl_2']);
  echo("<br>" . $dtext['grpman_expl_3']);
  echo("<br>" . $dtext['grpman_expl_4'] . "</dev><br>");
  echo("<table border=1 cellpadding=0>");
  
  // Create the heading row for the table
  echo("<tr><td><center>" . $dtext['Group_Cap'] . "</td>");
  echo("<td><center>" . $dtext['Mentor'] . "</td>");
  echo("<td><center>" . $dtext['Gradesblock'] . "</td>");
  echo("<td><center>" . $dtext['numb_token'] . "</td>");
  echo("<td></td>");
  echo("<td></td></font></tr>");

  // Create a row in the table for every existing group
  for($r=1;$r<=$row_n;$r++)
  {
    echo("<tr><form method=post action=updgroup.php name=ug". $r. ">");

    echo("<td><center><input type=hidden name=gid value=" . $grade_array['gid'][$r] .">");
    echo("<input type=text size=8 name=groupname value=\"" . $grade_array['groupname'][$r] ."\"></td>");
    // Add the mentor name
    echo("<td><center><select name=tid_mentor>");
    for($mc=1;$mc<=$teach_n;$mc++)
    { // add each teacher!
      if($grade_array['tid_mentor'][$r] == $teach_array['tid'][$mc]) $SelectInd = "selected";
      else $SelectInd = "";
      echo("<option value=" . $teach_array['tid'][$mc]. " " . $SelectInd.">");
      echo($teach_array['firstname'][$mc]." " . $teach_array['lastname'][$mc]);
      echo("</option>");
    }
    echo("</select></td>");
	// Add checkbox for disabling results display
	echo("<td><center><input type=checkbox name=gradesblock". ($grade_array['gradesblock'][$r] == 1 ? " checked" : ""). "></td>");
	// Add the number of students as display value
	echo("<td>". $grade_array['nbstuds'][$r]. "</td>");
    // Add the change button
    //echo("<td><center><input type=submit value=" . $dtext['Change'] . "></td></form>");
    echo("<td><center><img src='PNG/action_check.png' title='". $dtext['Change']. "' onclick='document.ug". $r. ".submit();'></td></form>");
    // Add the delete button
	if($grade_array['active'][$r] == 1)
	{
      echo("<form method=post action=delgroup.php name=dg". $r. "><input type=hidden name=gid value=");
      echo($grade_array['gid'][$r]);
      echo("><td><center><img src='PNG/action_delete.png' title='". $dtext['Delete']. "' onclick='document.dg". $r. ".submit();'></td></form></tr>");
	}
	else
	{
      echo("<form method=post action=mangroups.php name=ag". $r. "><input type=hidden name=gid value=");
      echo($grade_array['gid'][$r]);
      echo("><td><center><img src='PNG/action_add.png' title='". $dtext['ADD_CAP']. "' onclick='document.ag". $r. ".submit();'></td></form></tr>");
	}
  }
  // Insert the row for a new group
  echo("<tr><form method=post action=updgroup.php name=newg>");

  echo("<td><center><input type=hidden name=gid value=\"\">");
  echo("<input type=text size=8 name=groupname value=\"\"></td>");
  // Add the mentor name
  echo("<td><center><select name=tid_mentor>");
  for($mc=1;$mc<=$teach_n;$mc++)
  { // add each teacher!
    echo("<option value=" . $teach_array['tid'][$mc]. ">");
    echo($teach_array['firstname'][$mc]. " " . $teach_array['lastname'][$mc]);
    echo("</option>");
  }
  echo("</select></td>");
  // Dummy space for student count and groups block
  echo("<td>&nbsp</td><td>&nbsp</td>");
  // Add the ADD button
  //echo("<td><center><input type=submit value=" . $dtext['ADD_CAP'] . "></td></form>");
  echo("<td><center><img src='PNG/action_add.png' title='". $dtext['ADD_CAP']. "' onclick='document.newg.submit();'></td></form>");
  // Here we don't have a delete button!
  echo("</tr>");
  
  // close the table
  echo("</table></html>");

?>
