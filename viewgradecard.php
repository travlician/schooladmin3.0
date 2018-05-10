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
  if(isset($HTTP_GET_VARS['sid']))
    $sid = $HTTP_GET_VARS['sid'];
  
  $uid = intval($uid);

  // First we get the data from student in an array.
  $sql_query = "SELECT * FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) WHERE active=1 AND sgroup.groupname='$CurrentGroup' AND student.sid='$sid'";
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

  // Get the list of periods with their details
  $sql_query = "SELECT * FROM period";
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
       $period_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $periods = $nrows;
  // Now add an extra period for final results
  $period_array['id'][0] = '0';
  $period_array['year'][0] = $period_array['year'][$periods];
  // Depending on the states of the periods we set the state of the final period.
  $all_final = 'Y';
  $any_open = 'N';
  for($p=1;$p<= $periods;$p++)
  {
    if($period_array['status'][$p] == 'open')
      $any_open = 'Y';
    if($period_array['status'][$p] != 'final')
      $all_final = 'N';
  }

  // Get the list of applicable subjects with their details
  $sql_query = "SELECT * FROM subject inner join class using (mid) where gid='$gid' AND show_sequence IS NOT NULL ORDER BY show_sequence";
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

  // Get the list of grades for normal periods
  $sql_query = "SELECT * FROM period,student inner join gradestore using (sid) where period=id AND gradestore.year=period.year AND student.sid='$sid'";
  $mysql_query = $sql_query;
  //echo $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);
  //echo mysql_error($userlink);
  if (mysql_num_rows($sql_result)!=0)
  {
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
      $results_array[mysql_result($sql_result,$r,'period')][mysql_result($sql_result,$r,'mid')] = mysql_result($sql_result,$r,'result');
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0

  // Get the list of final grades
  $sql_query = "SELECT * FROM student inner join gradestore using (sid) where period='0' AND gradestore.year='" . $period_array['year'][0] . "' AND student.sid='$sid'";
  $mysql_query = $sql_query;
  //echo $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);
  //echo mysql_error($userlink);
  if (mysql_num_rows($sql_result)!=0)
  {
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
      $final_results_array[mysql_result($sql_result,$r,'mid')] = mysql_result($sql_result,$r,'result');
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0

  // Get the list of pass criteria per subject
  $sql_query = "SELECT * FROM class inner join coursepasscriteria using (masterlink) WHERE gid='$gid'";
  $mysql_query = $sql_query;
  //echo $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);
  //echo mysql_error($userlink);
  if (mysql_num_rows($sql_result)!=0)
  {
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
      $passpoint[mysql_result($sql_result,$r,'mid')] = mysql_result($sql_result,$r,'minimumpass');
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0

  $digits = SA_loadquery("SELECT MAX(digitsafterdot) AS digits FROM reportcalc");
  
  SA_closeDB();

  // First part of the page
  echo("<html><head><title>" . $dtext['gcrd_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['gcard_4'] . " " . $student_array['firstname'][1] . " " . $student_array['lastname'][1] . "</font><p>");
  echo("<a href=teacherpage.php>" . $dtext['back_teach_page'] . "</a><br>");
  echo("<a href=viewgrades.php>" . $dtext['back_grades'] . "</a><br>");
  echo("<br><div align=left>" . $dtext['gcrd_expl_1'] . "</dev><br>"); 

  echo("<br>");

  // Now create a table with all subjects for this student to enable to go to the grade details
  // Create the first heading row for the table
  echo("<table border=1 cellpadding=0>");
  echo("<tr><td><center>" . $dtext['Subject'] . "</td>");
  // Now add the periods heading
  for($p=1; $p<=$periods; $p++)
  {
    echo("<td><center><a href=viewperiodcard.php?period=". $p. "&sid=". $sid. ">". $dtext['Period_marker']. $p . "</a></td>");
  }
  echo("<td><center>" . $dtext['fin_per_ind'] . "</td></tr>"); 
  

  // Create a row in the table for every subject
  for($s=1;$s<=$subjects;$s++)
  { // each subject
    $mid = $subject_array['mid'][$s];
    echo("<tr><td>" . $subject_array['fullname'][$s] . "</td>");
    for($p=1;$p<=$periods;$p++)
    { // add the grades for regular periods
      $pp = $period_array['id'][$p];
      echo("<td><center><a href=viewstudentgradedetails.php?period=$pp&sid=$sid&mid=$mid>");
      if(isset($results_array[$pp][$mid]))
      { 
        $result = $results_array[$pp][$mid];
		if($result < '@')
		{
          // Colour depends on pass criteria
          if($passpoint[$mid] > $result) echo("<font color=red>");
          else echo("<font color=blue>");
          if($period_array['status'][$pp] == 'final') echo("<b>"); else echo("<i>");
          echo(number_format($result,$digits['digits'][1],$dtext['dec_sep'],$dtext['mil_sep']));
          if($period_array['status'][$pp] == 'final') echo("</b>"); else echo("</i>");
          echo("</font>");
		}
		else
		  echo($result);
      }
      else
        echo("-");
      echo("</a></td>");
    }
    // Add the final grade
    echo("<td><center>");
    if(isset($final_results_array[$mid]))
    {
      $result = $final_results_array[$mid];
      // Colour depends on pass criteria
      if($passpoint[$mid] > $result) echo("<font color=red>");
      else echo("<font color=blue>");
      if($any_open == 'N') echo("<b>"); else echo("<i>");
      echo($result);
      if($any_open == 'N') echo("</b>"); else echo("</i>");
      echo("</font>");
    }
    else
      echo("-");
    echo("</td>");
  }
  echo("</tr>");
  echo("</table>");
  echo '<a href="teacherpage.php">';
  echo $dtext['back_teach_page'];
  echo '</a>';
 
  // close the page
  echo("</html>");

?>
