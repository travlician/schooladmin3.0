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

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];
  if(isset($HTTP_POST_VARS['sid']))
    $StudentID = $HTTP_POST_VARS['sid'];
  else
    if(isset($_SESSION['sid']))
    {
      $StudentID = $_SESSION['sid'];
      $_SESSION['sid'] = "";
    }
  
  $uid = intval($uid);

  // First we get the data from concerned student in an array.
  $sql_query = "SELECT * FROM student WHERE sid='$StudentID'";
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

  // Create a separate array with the absence records on the student
  $sql_query = "SELECT *";
  $sql_query .= " FROM absence";
  $sql_query .= " WHERE sid='".$StudentID."'";
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
       $absence_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $report_n = $nrows;

  // Create a separate array with the absence reasons
  $sql_query = "SELECT * FROM absencereasons";
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
       $reason_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $reason_n = $nrows;

  // Create a separate array with the teacher details (needed to see if can edit absence)
  $sql_query = "SELECT * FROM teacher WHERE tid='$uid'";
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
       $teacher_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0

  SA_closeDB();

  // First part of the page
  echo("<html><head><title>" . $dtext['abs_stu_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['abs_stu_header'] . "</font><p>");
  echo '<a href="teacherpage.php">';
  echo $dtext['back_teach_page'];
  echo '</a><br>';
  echo '<a href="manabsents.php">';
  echo $dtext['back_abs_reg'];
  echo '</a><br>';
  echo("<br><div align=left>" . $dtext['abs_expl_1'] . "</dev><br>");

  // Show for which student editing
  echo($dtext['abs_expl_2'] . " <b>");
  echo($grade_array['firstname'][1] . " " . $grade_array['lastname'][1]);
  echo("</b><br><br>");

  // Create the heading row for the table
  echo("<table border=1 cellpadding=0>");
  echo("<tr><td><center>" . $dtext['Reason'] . "</td>");
  echo("<td><center>" . $dtext['Date'] . "</td>");
  echo("<td><center>" . $dtext['Time'] . "</td>");
  echo("<td><center>" . $dtext['Authorization'] . "</td>");
  echo("<td><center>" . $dtext['Remarks'] . "</td>");
  echo("<td></td>");
  echo("<td></td></font></tr>");

  // Create a row in the table for every existing absence record
  for($r=1;$r<=$report_n;$r++)
  {
    echo("<tr><form method=post action=updabsrecord.php name=ua". $r. ">");
    // Put in the hidden field for student ID  and date of the absence
    echo("<td><center><input type=hidden name=sid value=" . $absence_array['sid'][$r] .">");
    echo("<input type=hidden name=orgdate value='" . $absence_array['date'][$r] . "'>");
    // Add the reason, drop box style
    echo("<select name=reason>");
    for($a=1;$a<=$reason_n;$a++)
    {
      if($reason_array['aid'][$a] == $absence_array['aid'][$r])
        $selectInd = " selected";
      else
        $selectInd = "";
      echo("<option value='".$reason_array['aid'][$a]. "'" . $selectInd .">".$reason_array['description'][$a]."</option>"); 
    }
    echo("</select></td>");
    // Add date and time fields
    echo("<td><center><input type=text name=date size=10 value='" .$absence_array['date'][$r] . "'></td>");
    echo("<td><center><input type=text name=time size=8 value='" .$absence_array['time'][$r] . "'></td>");
    // Add the checkboxes for authorization
    echo("<td><center><font size=-1><input type=radio name=authorization value=No");
    if($absence_array['authorization'][$r] == "No")
      echo(" checked");
    echo(">" . $dtext['No'] . "<input type=radio name=authorization value=Yes");
    if($absence_array['authorization'][$r] == "Yes")
      echo(" checked");
    echo(">" . $dtext['Yes'] . "<input type=radio name=authorization value=Pending");
    if($absence_array['authorization'][$r] == "Pending")
      echo(" checked");
    echo(">" . $dtext['Pending'] . "</font></td>");
    // Add the explanation
    echo("<td><center><input type=text name=explanation value='" . $absence_array['explanation'][$r]."'></td>");

    // Add the Edit and delete buttons if teacher is absence manager
    if($LoginType == "A" || $teacher_array['is_arman'][1] == "Y")
    {
      //echo("<td><center><input type=submit value=" . $dtext['Edit'] . "></td></form>");
      echo("<td><center><img src='PNG/action_check.png' title='". $dtext['Edit']. "' onclick='document.ua". $r. ".submit();'></td></form>");
      echo("<form method=post action=delabsrecord.php name=da". $r. "><input type=hidden name=sid value=");
      echo($absence_array['sid'][$r]);
      echo("><input type=hidden name=orgdate value='" .$absence_array['date'][$r]. "'><td>");
	  //echo("<center><input type=submit value=" . $dtext['Delete'] . "></td></form></tr>");
      echo("<center><img src='PNG/action_delete.png' title='". $dtext['Delete']. "' onclick='document.da". $r. ".submit();'></td></form></tr>");
    }
    else
    {	// create two empty cells in the table because user has no access
      echo("</form></tr>");
    }   
  }

  // Create an empty row to add a new absence record.
  echo("<tr><form method=post action=updabsrecord.php name=newabs>");
  // Put in the hidden field for student ID, don't put an original date!
  echo("<td><center><input type=hidden name=sid value=" . $StudentID .">");
  // Add the reason, drop box style
  echo("<select name=reason>");
  for($a=1;$a<=$reason_n;$a++)
    echo("<option value='".$reason_array['aid'][$a]. "'>".$reason_array['description'][$a]."</option>"); 
  echo("</select></td>");
  // Add date and time fields
  echo("<td><center><input type=text name=date size=10 value='" .@Date('Y-m-d') . "'></td>");
  echo("<td><center><input type=text name=time size=8 value='" .@Date('H:i:s') . "'></td>");
  // Add the checkboxes for authorization
  echo("<td><center><font size=-1><input type=radio name=authorization value=No");
  echo(">" . $dtext['No'] . "<input type=radio name=authorization value=Yes");
  echo(">" . $dtext['Yes'] . "<input type=radio name=authorization value=Pending");
  echo(" checked");
  echo(">" . $dtext['Pending'] . "</font></td>");
  // Add the explanation
  echo("<td><center><input type=text name=explanation value=''></td>");

  // Add the ADD  button if teacher is absence manager
  if($LoginType == "A" || $teacher_array['is_arman'][1] == "Y")
  {
    //echo("<td><center><input type=submit value=" . $dtext['ADD_CAP'] . "></td></form>");
    echo("<td><center><img src='PNG/action_add.png' title='". $dtext['ADD_CAP']. "' onclick='document.newabs.submit();'></td></form>");
    echo("</tr>");
  }
  else
  {	// create two empty cells in the table because user has no access
    echo("/form></tr>");
  }   

  echo("</table>");
  echo '<a href="teacherpage.php">';
  echo $dtext['back_teach_page'];
  echo '</a>';


  // close the page
  echo("</html>");

?>
