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
  if(isset($HTTP_POST_VARS['rid']))
    $ReportID = $HTTP_POST_VARS['rid'];

  // If no report ID is set, you are in error
  if(!isset($ReportID))
  {
    echo($dtext['missing_rid'] . " " . $dtext['press_back']);
    SA_closeDB();
    exit;
  }
  
  $uid = intval($uid);

  // Get all the data from the report
  $sql_query = "SELECT * FROM reports WHERE rid=$ReportID";
  $sql_result = mysql_query($sql_query,$userlink);
  //echo mysql_error($userlink);
  $nrows = 0;
  if (mysql_num_rows($sql_result)!=0)
  {
    $nfields = mysql_num_fields($sql_result);
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
      $nrows++;
      for ($i=0;$i<$nfields;$i++)
      {
        $fieldname = mysql_field_name($sql_result,$i);
        $fieldvalu = mysql_result($sql_result,$r,mysql_field_name($sql_result,$i));
        $report_array[$fieldname][$nrows]=$fieldvalu;
      } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $report_n = $nrows;
  $StudentID = $report_array['sid'][1];
  if($report_array['type'][1] == "F" || $report_array['type'][1] == "T")
    $ReportType = "Student";
  else
    $ReportType = "Group";

  
  // Get the details of the group or student, depending on the report type.
  if($ReportType == "Student")  
    $sql_query = "SELECT * FROM student WHERE sid='" . $StudentID . "'";
  else
    $sql_query = "SELECT * FROM sgroup WHERE active=1 AND groupname='" . $CurrentGroup . "'";
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
      for ($i=0;$i<$nfields;$i++)
      {
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
  echo("<html><head><title>" . $dtext['vrep_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['vrep_title'] . "</font><p>");

  // Show for which group or student current editing is done
  echo("<div align=left>" . $dtext['vrep_4'] . " <b>");
  if($ReportType == "Student")
    echo($grade_array['firstname'][1] . " " . $grade_array['lastname'][1]);
  else
    echo($dtext['group'] . " " . $CurrentGroup);
  echo("</b><br>");

  // Put a submit to return to the group reports or student reports page
  if($ReportType == "Student")
  {
    echo("<form method=post action=reportsonstudent.php>");
    echo("<input type=hidden name=sid value=" . $report_array['sid'][1] . ">");
    echo("<input type=submit value='" . $dtext['vrep_expl_1'] . "'></form>");
  }
  else
  {
    echo("<form method=post action=reportsongroup.php>");
    echo("<input type=submit value='" . $dtext['vrep_expl_2'] . "'></form>");
  }
  echo($dtext['vrep_expl_3'] . ": " . $report_array['date'][1] . "<br>");

  // Add the summary text box
  echo("<br><b>" . $dtext['Summary'] . ":</b><br>");
  echo("<pre>" . $report_array['summary'][1] . "</pre><br>");

  if($report_array['type'][1] == "F" || $report_array['type'][1] == "X")
  {
    echo($dtext['vrep_expl_4'] . "<br>");
    echo("<a href=getreport.php?".$ReportID.">" . $dtext['vrep_expl_5'] . "</a>");
    // Allow the download link to work by registering and setting report id!
    $_SESSION['rid'] = $ReportID;
  }
  else
  {
    echo("<br><b>" . $dtext['Content'] . ":</b><br>");
    echo("<pre>" . $report_array['content'][1] . "</pre><br><br>");
  }

  // close the page
  echo("</div></html>");

?>
