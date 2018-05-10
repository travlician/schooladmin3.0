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
  include ("schooladminfunctions.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];

  $uid = intval($uid);

  // First we get all the data from existing teacher_details in an array.
  $sql_query = "SELECT * FROM teacher_details ORDER BY seq_no";
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
  echo("<html><head><title>" . $dtext['teachdetman_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['teachdetman_title'] . "</font><p>");
  echo '<a href="admin.php">';
  echo($dtext['back_admin'] . "</a><br>");
  echo("<br><div align=left>" . $dtext['teachdetman_expl_1']);
  echo("<br>" . $dtext['teachdetman_expl_2']);
  echo("<br>" . $dtext['teachdetman_expl_3'] . "</dev><br>");
  echo("<table border=1 cellpadding=0>");
  
  // Create the heading row for the table
  echo("<tr><td><center><font size=-1>" . $dtext['Fieldname'] . "</td>");
  echo("<td><center><font size=-1>" . $dtext['Label'] . "</td>");
  echo("<td><center><font size=-1>" . $dtext['Type'] . "</td>");
  echo("<td><center><font size=-1>" . $dtext['Size'] . "</td>");
  echo("<td><center><font size=-1>" . $dtext['params'] . "</td>");  
  echo("<td><center><font size=-1>" . $dtext['Records'] . "</td>");
  echo("<td><center><font size=-1>" . $dtext['R_acc'] . "</td>");
  echo("<td><center><font size=-1>" . $dtext['W_acc'] . "</td>");
  echo("<td><center><font size=-1>" . $dtext['numb_token'] . "</td>");
  echo("<td><center><font size=-1>" . $dtext['Overview']. "</td>");
  echo("<td></td>");
  echo("<td></td></font></tr>");

  // Create a row in the table for every existing detail
  for($r=1;$r<=$row_n;$r++)
  {
    echo("<tr><form method=post action=updteacherdetail.php name=ut". $r. ">");
    echo("<input type=hidden name=table_name value='" . $grade_array['table_name'][$r] . "'>");
    // Field name, label and type
    echo("<td><font size=-1>". $grade_array['table_name'][$r] . "</td>");
    echo("<td><center><input type=text size=15 name=label value=\"" . $grade_array['label'][$r] ."\"></td>");
    echo("<td><center><font size=-1>");
    if($grade_array['type'][$r] == "text")
      echo($dtext['Text']);
    else
      echo($dtext['Picture']);
    echo("</td>");
    // Size, params and multiple or single records
    echo("<td><center><font size=-1>-</td>");
    echo("<td><center><input type=text size=15 name=params value=\"" . $grade_array['params'][$r] ."\"></td>");
    if($grade_array['multi'][$r] == "Y")
      echo("<td><font size=-1>" . $dtext['Multi'] . "</td>");
    else
      echo("<td><font size=-1>" . $dtext['Single'] . "</td>");
    // Read access
    echo("<td><center><select name=raccess>");
    echo("<option value='A' ". (($grade_array['raccess'][$r]=="A") ? " selected" : "") . ">" . $dtext['allow_all_short'] . "</option>");
    echo("<option value='T' ". (($grade_array['raccess'][$r]=="T") ? " selected" : "") . ">" . $dtext['allow_teach_short'] . "</option>");
    echo("<option value='M' ". (($grade_array['raccess'][$r]=="M") ? " selected" : "") . ">" . $dtext['allow_ment_short'] . "</option>");
    echo("<option value='C' ". (($grade_array['raccess'][$r]=="C") ? " selected" : "") . ">" . $dtext['allow_couns_short'] . "</option>");
    echo("<option value='O' ". (($grade_array['raccess'][$r]=="O") ? " selected" : "") . ">" . $dtext['Office_admin'] . "</option>");
    echo("<option value='N' ". (($grade_array['raccess'][$r]=="N") ? " selected" : "") . ">" . $dtext['allow_none'] . "</option>");
    echo("</select></td>");
    // Write access
    echo("<td><center><select name=waccess>");
    echo("<option value='T' ". (($grade_array['waccess'][$r]=="T") ? " selected" : "") . ">" . $dtext['allow_teach_short'] . "</option>");
    echo("<option value='C' ". (($grade_array['waccess'][$r]=="C") ? " selected" : "") . ">" . $dtext['allow_couns_short'] . "</option>");
    echo("<option value='O' ". (($grade_array['waccess'][$r]=="O") ? " selected" : "") . ">" . $dtext['Office_admin'] . "</option>");
    echo("<option value='N' ". (($grade_array['waccess'][$r]=="N") ? " selected" : "") . ">" . $dtext['allow_none'] . "</option>");
    echo("</select></td>");
    // sequence number, here we make a drop-down /w all the available numbers
    echo("<td><select name=seq_no>");
    for($s=1;$s<=$row_n;$s++)
      echo("<option value=". $s . (($grade_array['seq_no'][$r] == $s) ? " selected" : "") . ">" . $s . "</option>");
    echo("</select></td>");
	// Flag (checkbox) to show item in overview or not, ONLY if not a multiple record!
	if($grade_array['multi'][$r] == "N")
      echo("<td><center><input type=checkbox name=overview". ($grade_array['overview'][$r] == 1 ? " checked" : ""). "></td>");
	else
	  echo("<td><center>-</td>");

    // DO button and ends the form
    echo("<td><center><img src='PNG/action_check.png' title='". $dtext['DO_CAP']. "' onclick='document.ut". $r. ".submit();'></td></form>");
    // Delete button (only if not fixed!)
    if($grade_array['fixed'][$r] == "Y")
      echo("<td></td></tr>");
    else
    {
      echo("<form method=post action=delteachdetail.php name=dt". $r. "><input type=hidden name=table_name value=");
      echo($grade_array['table_name'][$r]);
      echo("><td><center><img src='PNG/action_delete.png' title='". $dtext['Delete']. "' onclick='if(confirm(\"". $dtext['confirm_delete']. "\")) { document.dt". $r. ".submit(); }'></td></form></tr>");
    }
  }


  // Insert the row for a new teacher detail field
  echo("<tr><form method=post action=updteacherdetail.php name=newt><input type=hidden name=new value=Y>");
  // Field name, label and type
  echo("<td><input type=text size=17 name=table_name></td>");
  echo("<td><center><input type=text size=15 name=label></td>");
  echo("<td><center><select name=type><option value=text selected>" . $dtext['Text'] . "</option>");
  echo("<option value=picture>" . $dtext['Picture'] . "</select></td>");
  // size
  echo("<td><center><input type=text size=3 name=size></td>");
  echo("<td><center><input type=text size=15 name=params>");
  // Single or multiple records
  echo("<td><center><select name=multi><option value=Y>" . $dtext['Multi'] . "</option>");
  echo("<option value=N selected>" . $dtext['Single'] . "</option></select></td>");
  // Read access
  echo("<td><center><select name=raccess>");
  echo("<option value='A'>" . $dtext['allow_all_short'] . "</option>");
  echo("<option value='T' selected>" . $dtext['allow_teach_short'] . "</option>");
  echo("<option value='M'>" . $dtext['allow_ment_short'] . "</option>");
  echo("<option value='C'>" . $dtext['allow_couns_short'] . "</option>");
  echo("<option value='O'>" . $dtext['Office_admin'] . "</option>");
  echo("<option value='N'>" . $dtext['allow_none'] . "</option>");
  echo("</select></td>");
  // Write access
  echo("<td><center><select name=waccess>");
  echo("<option value='A'>" . $dtext['allow_all_short'] . "</option>");
  echo("<option value='T'>" . $dtext['allow_teach_short'] . "</option>");
  echo("<option value='C'>" . $dtext['allow_couns_short'] . "</option>");
  echo("<option value='O'>" . $dtext['Office_admin'] . "</option>");
  echo("<option value='N' selected>" . $dtext['allow_none'] . "</option>");
  echo("</select></td>");
  // sequence number, here we make a drop-down /w all the available numbers
  echo("<td><select name=seq_no>");
  for($s=1;$s<=($row_n);$s++)
    echo("<option value=". $s. ">" . $s . "</option>");
  echo("<option value=" . ($row_n+1) . " selected>" . ($row_n + 1) . "</option>");
  echo("</select></td>");
  echo("<td><center><input type=checkbox name=overview></td>");

  // ADD button and ends the form
  //echo("<td><center><input type=submit value=" . $dtext['ADD_CAP'] . "></td></form>");
  echo("<td><center><img src='PNG/action_add.png' title='". $dtext['ADD_CAP']. "' onclick='document.newt.submit();'></td></form>");
  // No delete button!
  echo("<td></td></tr>");
  
  // close the table
  echo("</table></html>");
  echo("</html>");

?>
