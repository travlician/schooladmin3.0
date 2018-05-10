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

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
  include ("schooladmingradecalc.php"); //@grades

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];
  if(isset($HTTP_POST_VARS['NewGroup']) && SA_verifyGroupAccess($uid,$HTTP_POST_VARS['NewGroup']))
    $CurrentGroup = $HTTP_POST_VARS['NewGroup'];
  if(isset($HTTP_GET_VARS['NewGroup']) && SA_verifyGroupAccess($uid,$HTTP_GET_VARS['NewGroup']))
    $CurrentGroup = $HTTP_GET_VARS['NewGroup'];
  // Store the new group or future pages
  $_SESSION['CurrentGroup']=$CurrentGroup;
  if(isset($HTTP_GET_VARS['mid']))
    $mid = $HTTP_GET_VARS['mid'];
  if(isset($HTTP_GET_VARS['period']))
    $period = $HTTP_GET_VARS['period'];
  if(isset($HTTP_GET_VARS['sid']))
    $sid = $HTTP_GET_VARS['sid'];
  
  $uid = intval($uid);

  // First we get the data from existing students in an array.
  $sql_query = "SELECT * FROM student LEFT JOIN sgrouplink USING(sid) WHERE student.sid='$sid' ORDER BY lastname";
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

  // Get the list of applicable subjects with their details
  $sql_query = "SELECT * FROM subject inner join class using (mid) where gid='$gid' AND subject.mid='$mid'";
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
       $subject_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $subjects = $nrows;
  $cid = $subject_array['cid'][1];

  // Calculate the grade again!
  SA_calcGrades($sid, $cid, $period); //@grades

  // Get the list of test definitions with their details
  $sql_query = "SELECT testdef.*,AVG(testresult.result) AS `average`,STD(testresult.result) AS `std` FROM testdef LEFT JOIN class USING(cid) LEFT JOIN period ON(period.id=testdef.period) LEFT JOIN testresult ON(testresult.tdid=testdef.tdid) WHERE class.mid='$mid' AND class.gid='$gid' AND testdef.period='$period' AND period.year=testdef.year AND testdef.type <> '0' GROUP BY tdid ORDER BY date";
  $mysql_query = $sql_query;
  //echo $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);
  echo mysql_error($userlink);
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
       $testdef_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $testdefs = $nrows;


  // Get the list of grades for the given subject & period & student
  $sql_query = "SELECT tdid,result FROM testresult where sid='$sid'";
  $mysql_query = $sql_query;
  //echo $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);
  //echo mysql_error($userlink);
  if (mysql_num_rows($sql_result)!=0)
  {
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
      $results_array[mysql_result($sql_result,$r,'tdid')] = mysql_result($sql_result,$r,'result');
    mysql_free_result($sql_result);
  }//If numrows != 0

  // Get the list of pass criteria per testtype
  $sql_query = "SELECT * FROM reportcalc";
  $mysql_query = $sql_query;
  //echo $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);
  //echo mysql_error($userlink);
  if (mysql_num_rows($sql_result)!=0)
  {
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
      if(mysql_result($sql_result,$r,'mid') == $mid)
        $passpoint[mysql_result($sql_result,$r,'testtype')] = mysql_result($sql_result,$r,'passthreshold');
      else
      {
        if(mysql_result($sql_result,$r,'mid') == 0 || !isset($passpoint[mysql_result($sql_result,$r,'testtype')]))
          $passpoint[mysql_result($sql_result,$r,'testtype')] = mysql_result($sql_result,$r,'passthreshold');
      }
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0

  // Get the final result from gradestore.
  $sql_query = "SELECT * FROM gradestore inner join period using (year) WHERE gradestore.period=period.id AND sid='$sid' AND mid='$mid' AND period='$period'";
  $mysql_query = $sql_query;
  //echo $sql_query;
  $sql_result = mysql_query($mysql_query,$userlink);
  //echo mysql_error($userlink);
  if (mysql_num_rows($sql_result)!=0)
  {
    $finalresult = mysql_result($sql_result,0,'result');
    mysql_free_result($sql_result);
  }//If numrows != 0
  
  $digits = SA_loadquery("SELECT MAX(digitsafterdot) AS digits FROM reportcalc");
  

  SA_closeDB();

  // First part of the page
  echo("<html><head><title>" . $dtext['vtststusub_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['Tres_4'] . " " . $student_array['firstname'][1] . " " . $student_array['lastname'][1] . " " . $dtext['on'] . " " . $subject_array['fullname'][1] . " " . $dtext['4_per'] . " " .$period . "</font><p>");
  echo("<a href=teacherpage.php>" . $dtext['back_teach_page'] . "</a><br>");
  echo("<a href=viewgrades.php>" . $dtext['back_grades'] . "</a><br>");
  echo("<br><div align=left>"); 

  echo("<br>");

  // Now create a table with test definitions and their results for this student
  // Create the first heading row for the table
  echo("<table border=1 cellpadding=0>");
  echo("<tr><td><center>" . $dtext['Tst_des'] . "</td><td><center>" . $dtext['Type'] . "</td><td><center>" . $dtext['Date'] . "</td><td>" . $dtext['Grade'] . "</td><td>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</td><td>". $dtext['Group_average']. "</td><td>". $dtext['Group_std']. "</td></tr>");
  // Now add the test definitions
  // Create a row in the table for every existing test definition
  for($t=1;$t<=$testdefs;$t++)
  {
    echo("<tr>");
    echo("<td>" . $testdef_array['description'][$t] . "</td>");
    echo("<td>" . $testdef_array['type'][$t] . "</td>");
    echo("<td>" . $testdef_array['date'][$t] . "</td>");
    // Add the Grades
    echo("<td><center>");
    if(isset($results_array[$testdef_array['tdid'][$t]]))
    { 
      $result = $results_array[$testdef_array['tdid'][$t]];
	  if($result < '@')
	  { // Numeric result
        // Colour depends on pass criteria
        if($passpoint[$testdef_array['type'][$t]] > $result) echo("<font color=red>");
        else echo("<font color=blue>");
        echo(number_format($result,$digits['digits'][1],$dtext['dec_sep'],$dtext['mil_sep']));
        echo("</font>");
	  }
	  else // Alphabetic result
	    echo($result);
    }
    else
      echo("-");
    echo("</td><td>&nbsp</td>");
	echo("<td><center>". number_format($testdef_array['average'][$t],2,$dtext['dec_sep'],$dtext['mil_sep']). "</td><td><center>". number_format($testdef_array['std'][$t],2,$dtext['dec_sep'],$dtext['mil_sep']). "</td>");
    echo("</tr>");
  }
  // Now add one row with the calculated result
  if(isset($finalresult))
  {
    echo("<tr><td><center>");
    if($testdefs > 0)
      echo($dtext['Calc_res']);
    else
      echo($dtext['His_res']);
	if($finalresult < '@')
	{ // Numeric result
      echo("</td><td><center>-</td><td><center>-</td><td><center><B>" . number_format($finalresult,$digits['digits'][1],$dtext['dec_sep'],$dtext['mil_sep']) . "</B></td><td>&nbsp</td><td>&nbsp</td><td>&nbsp</td>");
	}
	else // Alphabetic result
      echo("</td><td><center>-</td><td><center>-</td><td><center><B>" . $finalresult . "</B></td><td>&nbsp</td><td>&nbsp</td><td>&nbsp</td>");	
  }
  echo("</table>");
  echo '<a href="teacherpage.php">';
  echo $dtext['back_teach_page'];
  echo '</a>';
 
  // close the page
  echo("</html>");

?>
