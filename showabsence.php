<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014 Aim4me N.V.   (http://www.aim4me.info)       |
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

  $login_qualify = 'S';
  include ("schooladminfunctions.php");
  include ("absence.php");
  inputclassbase::dbconnect($userlink);

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
    
  $uid = intval($uid);
  $sid = $uid;
  $StudentID = $sid;

  // First we get the data from student in an array.
  $sql_query = "SELECT * FROM student LEFT JOIN sgrouplink USING(sid) WHERE student.sid='$sid'";
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
       $student_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $row_n = $nrows;
  // set the group id for smarter queries following
  $gid = $student_array['gid'][1];

  // Create a separate array with the absence records on the student
	if(isset($hideoldabsence) && $hideoldabsence)
	{
	  $startdate = SA_loadquery("SELECT MIN(startdate) AS sdat FROM period");
	  if(isset($startdate['sdat'][1]))
	    $startdate = $startdate['sdat'][1];
      else
	    $startdate='1971-01-01';
	}
	else $startdate = '1971-01-01';
  $sql_query = "SELECT *";
  $sql_query .= " FROM absence";
  $sql_query .= " WHERE sid='".$StudentID."' AND date >= '". $startdate. "'";
  $absence_array = SA_loadquery($sql_query);
  
  // Create a separate array with the absence reasons
  $sql_query = "SELECT * FROM absencereasons";
  $reason_array = SA_loadquery($sql_query);
  
  SA_closeDB();

  // First part of the page
  echo("<html><head><title>" . $dtext['My_absence'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['My_absence'] . " " . $student_array['firstname'][1] . " " . $student_array['lastname'][1] . "</font><p>");
  include("studentmenu.php");

  echo("<br><div align=left>"); 

  echo("<br>");

  // Create the heading row for the table
  echo("<table border=1 cellpadding=0>");
  echo("<tr><td><center>" . $dtext['Reason'] . "</td>");
  echo("<td><center>" . $dtext['Date'] . "</td>");
  echo("<td><center>" . $dtext['Time'] . "</td>");
  echo("<td><center>" . $dtext['Authorization'] . "</td>");
  echo("<td><center>" . $dtext['Remarks'] . "</td>");
  echo("</tr>");

  // Create a row in the table for every existing absence record
  if(isset($absence_array))
  foreach($absence_array['aid'] AS $r => $aid)
  {
    echo("<tr>");
    // Add the reason, drop box style
    echo("<td>");
	foreach($reason_array['aid'] AS $a => $arid)
    {
      if($reason_array['aid'][$a] == $absence_array['aid'][$r])
        echo($reason_array['description'][$a]); 
    }
    echo("</td>");
    // Add date and time fields
    echo("<td>" .$absence_array['date'][$r] . "</td>");
    echo("<td>" .$absence_array['time'][$r] . "</td>");
    // Add the checkboxes for authorization
    echo("<td>");
    if($absence_array['authorization'][$r] == "No")
      echo($dtext['No']);
    if($absence_array['authorization'][$r] == "Yes")
      echo($dtext['Yes']);
    if($absence_array['authorization'][$r] == "Pending")
      echo($dtext['Pending'] . "</td>");
    if($absence_array['authorization'][$r] == "Parent")
      echo($dtext['Parent'] . "</td>");
    // Add the explanation
    echo("<td>" . $absence_array['explanation'][$r]."</td>");
  }
  echo("</table>");
    if($_SESSION['usertype'] != "parent")
    exit;
  $rq = "SELECT aid AS id,description AS tekst FROM absencereasons LEFT JOIN absencecats USING(acid) WHERE waccess='A'";
  $rqr = inputclassbase::load_query($rq);
  if(isset($rqr['id']))
  {
    echo("<H2>". $dtext['parent_abs_set_title']. "</H2>");
    echo($dtext['Reason']. ": ");
    $absobj = new absence();
    $absobj->edit_reason_parent();
    echo("<BR>". $dtext['Remarks']. ": ");
    $absobj->edit_explanation();
  }

?>
</html>
