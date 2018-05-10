<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011 Aim4me N.V.   (http://www.aim4me.info)	      |
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

  // First we get all the data from existing gradecalc records in an array.
  $sql_query = "SELECT * FROM reportcalc ORDER BY mid,testtype";
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

  // Create a separate array with the subjects
  $sql_query = "SELECT mid,shortname FROM subject ORDER BY shortname";
  $sql_result = mysql_query($sql_query,$userlink);
  //echo mysql_error($userlink);
  $nrows = 0;
  // Add default subject!
  $subject_array['mid'][0] = "0";
  $subject_array['shortname'][0]="Default";
  if (mysql_num_rows($sql_result)!=0)
  {
    $nfields = mysql_num_fields($sql_result);
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
     $nrows++;
     for ($i=0;$i<$nfields;$i++){
       $fieldname = mysql_field_name($sql_result,$i);
       $fieldvalu = mysql_result($sql_result,$r,mysql_field_name($sql_result,$i));
       $subject_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $subject_n = $nrows;

  // Create a separate array with the event types
  $sql_query = "SELECT type FROM testtype ORDER BY type";
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
       $testtype_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $testtype_n = $nrows;
  SA_closeDB();

  // First part of the page
  echo("<html><head><title>" . $dtext['gcalc_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['gcalc_title'] . "</font><p>");
  echo '<a href="admin.php">';
  echo($dtext['back_admin'] . "</a><br>");
  echo("<br><div align=left>" . $dtext['gcalc_expl_1']);
  echo("<br>" . $dtext['gcalc_expl_2']);
  echo("<br>" . $dtext['gcalc_expl_3'] . "</dev><br><br>");
  echo("<table border=1 cellpadding=0>");

  // Create the heading row for the table
  echo("<tr><td><center>" . $dtext['Subject'] . "</td>");
  echo("<td><center>" . $dtext['Testtype'] . "</td>");
  echo("<td><center>" . $dtext['discard_cnt'] . "</td>");
  echo("<td><center>" . $dtext['min_results'] . "</td>");
  echo("<td><center>" . $dtext['Weight'] . "</td>");
  echo("<td><center>" . $dtext['dig_after_dot'] . "</td>");
  echo("<td><center>" . $dtext['pass_grade'] . "</td>");
  echo("<td><center>" . $dtext['use_avg'] . "</td>");
  echo("<td></td>");
  echo("<td></td></font></tr>");

  // Create a row in the table for every existing reportcalc record
  for($r=1;$r<=$row_n;$r++)
  {
    echo("<tr><form method=post action=updgradecalc.php name=ug". $r. ">");
    // Insert the original mid and testype as hidden to be able to delete b4 reinsert.
    echo("<td><input type=hidden name=orgmid value='" . $grade_array['mid'][$r]."'>");
    echo("<input type=hidden name=orgtesttype value='" . $grade_array['testtype'][$r]."'>");
    // Insert the options for each subject
    echo("<center><select name=mid>");
    for($sc=0;$sc<=$subject_n;$sc++)
    {
      if($grade_array['mid'][$r] == $subject_array['mid'][$sc])
        $IsSelected = "selected";
      else
        $IsSelected = "";
      echo("<option value=" . $subject_array['mid'][$sc]." " . $IsSelected.">" . $subject_array['shortname'][$sc]."</option>");
    }
    echo("</select></td>");

    // Insert the options for each testtype
    echo("<td><center><select name=testtype>");
    for($tc=1;$tc<=$testtype_n;$tc++)
    {
      if($grade_array['testtype'][$r] == $testtype_array['type'][$tc])
        $IsSelected = " selected";
      else
        $IsSelected = "";
      echo("<option value='" . $testtype_array['type'][$tc]."'" . $IsSelected.">" . $testtype_array['type'][$tc]."</option>");
    }
    echo("</select></td>");
    
    // Add the test type fields
    echo("<td><input type=text size=5 name=dropworst value=\"" . $grade_array['dropworst'][$r] ."\"></td>");
    echo("<td><input type=text size=5 name=validifatleast value=\"" . $grade_array['validifatleast'][$r] ."\"></td>");
    echo("<td><input type=text size=5 name=weight value=\"" . $grade_array['weight'][$r] ."\"></td>");
    echo("<td><input type=text size=5 name=digitsafterdot value=\"" . $grade_array['digitsafterdot'][$r] ."\"></td>");
    echo("<td><input type=text size=7 name=passthreshold value=\"" . $grade_array['passthreshold'][$r] ."\"></td>");

    // Add the on average option
    if($grade_array['on_average'][$r] == "Y") {$YesChecked = "checked"; $NoChecked = "";}
    else {$YesChecked = ""; $NoChecked = "checked";}
    echo("<td><center><input type=radio name=on_average value=Y " . $YesChecked.">" . $dtext['y']);
    echo("<input type=radio name=on_average value=N " . $NoChecked.">" . $dtext['n'] . "</td>");

    // Add the change button
    //echo("<td><center><input type=submit value=" . $dtext['Change'] . "></td></form>");
    echo("<td><center><img src='PNG/action_check.png' title='". $dtext['Change']. "' onclick='document.ug". $r. ".submit();'></td></form>");
    // Add the delete button
    echo("<form method=post action=delgradecalc.php name=dg". $r. "><input type=hidden name=mid value=");
    echo($grade_array['mid'][$r]);
    echo("><input type=hidden name=testtype value=");
    echo($grade_array['testtype'][$r]);
    //echo("><td><center><input type=submit value=" . $dtext['Delete'] . "></td></form></tr>");
    echo("><td><center><img src='PNG/action_delete.png' title='". $dtext['Delete']. "' onclick='if(confirm(\"". $dtext['confirm_delete']. "\")) { document.dg". $r. ".submit(); }'></td></form></tr>");
  }
  // Insert the row for a new reportcalc record
  echo("<tr><form method=post action=updgradecalc.php name=newg>");
  // Insert the original mid and testype as hidden to be able to delete b4 reinsert.
  echo("<td><input type=hidden name=orgmid value=\"\">");
  echo("<input type=hidden name=orgtesttype value=\"\">");
  // Insert the options for each subject
  echo("<center><select name=mid>");
  for($sc=0;$sc<=$subject_n;$sc++)
  {
    echo("<option value=" . $subject_array['mid'][$sc].">" . $subject_array['shortname'][$sc]."</option>");
  }
  echo("</select></td>");

  // Insert the options for each testtype
  echo("<td><center><select name=testtype>");
  for($tc=1;$tc<=$testtype_n;$tc++)
  {
    echo("<option value='" . $testtype_array['type'][$tc]."'>" . $testtype_array['type'][$tc]."</option>");
  }
  echo("</select></td>");
    
  // Add the test type fields
  echo("<td><input type=text size=5 name=dropworst value=0></td>");
  echo("<td><input type=text size=5 name=validifatleast value=1></td>");
  echo("<td><input type=text size=5 name=weight value=1></td>");
  echo("<td><input type=text size=5 name=digitsafterdot value=1></td>");
  echo("<td><input type=text size=7 name=passthreshold value=10.0></td>");

  // Add the on average option
  echo("<td><center><input type=radio name=on_average value=Y>" . $dtext['y']);
  echo("<input type=radio name=on_average value=N checked>" . $dtext['n'] . "</td>");

  // Add the ADD button
  //echo("<td><center><input type=submit value=" . $dtext['ADD_CAP'] . "></td></form>");
  echo("<td><center><img src='PNG/action_add.png' title='". $dtext['ADD_CAP']. "' onclick='document.newg.submit();'></td></form>");
  // Here we don't have a delete button!
  echo("<td></td></tr>");
  
  // close the table
  echo("</table>");

  // Add the explanations for the fields
  echo("<br><div align=left>");
  echo("<u>" . $dtext['discard_cnt'] . "</u>: " . $dtext['gcalc_expl_4']);
  echo("<br><u>" . $dtext['min_results'] . "</u>: " . $dtext['gcalc_expl_5']);
  echo("<br><u>" . $dtext['Weight'] . "</u>: " . $dtext['gcalc_expl_6']);
  echo("<br><u>" . $dtext['dig_after_dot'] . "</u>: " . $dtext['gcalc_expl_7']);
  echo("<br><u>" . $dtext['pass_grade'] . "</u>: " . $dtext['gcalc_expl_8']);
  echo("<br><u>" . $dtext['use_avg'] . "</u>: " . $dtext['gcalc_expl_9']);
  
  echo("</div></html>");

?>
