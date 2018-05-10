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
  else
    $ReportID = "";
  if(isset($HTTP_POST_VARS['rtype']))
    $ReportType = $HTTP_POST_VARS['rtype'];
  if(isset($HTTP_POST_VARS['sid']))
    $StudentID = $HTTP_POST_VARS['sid'];

  // If no report type is set, you are in error
  if(!isset($ReportType))
  {
    echo("ERROR: Report type is not set, press back to return to previous page");
    SA_closeDB();
    exit;
  }
  
  // If it's a new report for a student and no sid is given it's an error
  if($ReportType == "Student" && $ReportID == "" && !isset($StudentID))
  {
    echo($dtext['missing_sid'] . " " . $dtext['press_back']);
    SA_closeDB();
    exit;
  }
  
  $uid = intval($uid);

  // Get all the data from the report if a report ID is given.
  if($ReportID != "")
  {
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
  }

  
  // Get the details of the group or student, depending on the report type.
  if($ReportType == "Student")  
    $sql_query = "SELECT * FROM student LEFT JOIN sgrouplink USING(sid) WHERE sid='" . $StudentID . "'";
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

  // Add the function to be able to limit characters in textareas
  SA_addLimitScript();

  // First part of the page
  echo("<html><head><title>" .$dtext['editrep_title'] . "</title>");
  // Add the function to be able to limit characters in textareas
  SA_addLimitScript();
  echo("</head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" .$dtext['editrep_title'] . "</font><p>");
  echo '<a href="teacherpage.php">';
  echo ($dtext['back_teach_page']);
  echo '</a><br>';
  echo '<a href="reportsongroup.php">';
  echo ($dtext['back_repgrp']);
  echo '</a><br>';
  echo("<br><div align=left>" . $dtext['editrep_expl_1'] . "</dev><br>");

  // Show for which group or student current editing is done
  echo($dtext['editrep_for'] . " <b>");
  if($ReportType == "Student")
    echo($grade_array['firstname'][1] . " " . $grade_array['lastname'][1]);
  else
    echo($dtext['group'] . " " . $CurrentGroup);
  echo("</b><br>");

  echo("<form method=post action=updreport.php ENCTYPE=\"multipart/form-data\">");
  echo("<input type=hidden name=rid value=" . $ReportID . ">");
  echo("<input type=text size=10 name=date label=\"Date\" value=\"");
  if($ReportID == "")
    echo(@Date('Y-m-d'));
  else
    echo($report_array['date'][1]);
  echo("\"><input type=hidden name=tid value=" . $uid . ">");
  echo("<input type=hidden name=sid value=\"");
  if($ReportType == "Student")
    echo($StudentID);
  else
    echo($grade_array['gid'][1]);
  echo("\">");
  // Add the radio buttons for the protection
  if(isset($report_array['protect'][1]))
    $Protection = $report_array['protect'][1];
  else
    $Protection = "T";
  echo("<br><input type=radio name=protect value=A" . (($Protection=="A") ? " checked" : "") . ">" . $dtext['allow_all']);
  echo("<br><input type=radio name=protect value=T" . (($Protection=="T") ? " checked" : "") . ">" . $dtext['allow_teach']);
  echo("<br><input type=radio name=protect value=M" . (($Protection=="M") ? " checked" : "") . ">" . $dtext['allow_mentcouns']);
  echo("<br><input type=radio name=protect value=C" . (($Protection=="C") ? " checked" : "") . ">" . $dtext['allow_couns']);

  // Add the summary text box
  echo("<br>" . $dtext['Summary']);
  echo("<br><textarea name=summary rows=3 cols=60 onKeyDown=\"textLimit(this.form.summary,255);\" onKeyUp=\"textLimit(this.form.summary,255);\">");
  if(isset($report_array['summary'][1]))
    echo($report_array['summary'][1]);
  echo("</textarea>");

  // Add the checkbox and field for input as a file
  echo("<br><input type=radio name=type value=");
  if(isset($report_array['type'][1]))
  {
    if($report_array['type'][1] == "F")
      echo("F checked");
    else if($report_array['type'][1] == "X")
      echo("X checked");
    else if($ReportType == "Student")
      echo("F");
    else
      echo("X");
  }
  else
  {
    if($ReportType == "Student")
      echo("F");
    else
      echo("X");
  }
  echo(">" . $dtext['use_file']);
  echo("<input type=file name=\"userfile\" value=\"" . $dtext['locate_file'] . "\"><br>");

  // Add the full content textbox
  echo("<br><input type=radio name=type value=");
  if(isset($report_array['type'][1]))
  {
    if($report_array['type'][1] == "T")
      echo("T checked");
    else if($report_array['type'][1] == "C")
      echo("C checked");
    else if($ReportType == "Student")
      echo("T");
    else
      echo("C");
  }
  else
  {
    if($ReportType == "Student")
      echo("T checked");
    else
      echo("C checked");
  }
  echo(">" . $dtext['use_rep_text']);
  echo("<br>" . $dtext['rep_content'] . "<br><textarea name=content rows=20 cols=60>");
  if(isset($report_array['content'][1]))
    echo($report_array['content'][1]);
  echo("</textarea>");
  
  echo("<br><input type=submit value='" . $dtext['submit_chng'] . "'></form>");
  echo '<a href="teacherpage.php">';
  echo $dtext['back_teach_page'];
  echo '</a>';


  // close the page
  echo("</html>");

?>
