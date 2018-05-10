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
  $sql_query = "SELECT * FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid)WHERE active=1 AND sgroup.groupname='$CurrentGroup' ORDER BY lastname,firstname";
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
  if(isset($teachercode))
    $sql_query = "SELECT class.mid,cid,shortname,". $teachercode. ".data AS `tcode` FROM subject inner join class using (mid) left join ". $teachercode. " USING(tid) where gid='$gid' AND show_sequence IS NOT NULL ORDER BY show_sequence";
  else
    $sql_query = "SELECT class.mid,cid,shortname FROM subject inner join class using (mid) where gid='$gid' AND show_sequence IS NOT NULL ORDER BY show_sequence";
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
       $subject_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $subjects = $nrows;

  // Get the list of grades for normal periods
  $sql_query = "SELECT * FROM period,student inner join gradestore using (sid) LEFT JOIN sgrouplink USING(sid) where period=id AND gradestore.year=period.year AND sgrouplink.gid='$gid'";
  $mysql_query = $sql_query;
  //echo $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);
  //echo mysql_error($userlink);
  if (mysql_num_rows($sql_result)!=0)
  {
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
      $results_array[mysql_result($sql_result,$r,'period')][mysql_result($sql_result,$r,'mid')][mysql_result($sql_result,$r,'sid')] = mysql_result($sql_result,$r,'result');
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  
  // Get the list of average grades for normal periods
  $sql_query = "SELECT AVG(result) as `average`,period,mid FROM period,student inner join gradestore using (sid) LEFT JOIN sgrouplink USING(sid) where period=id AND gradestore.year=period.year AND student.gid='$gid' GROUP BY period,mid";
  $mysql_query = $sql_query;
  //echo $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);
  //echo mysql_error($userlink);
  if (mysql_num_rows($sql_result)!=0)
  {
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
      $average_array[mysql_result($sql_result,$r,'period')][mysql_result($sql_result,$r,'mid')] = mysql_result($sql_result,$r,'average');
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0

  // Get the list of final grades
  $sql_query = "SELECT * FROM student inner join gradestore using (sid) LEFT JOIN sgrouplink USING(sid) where period='0' AND gradestore.year='" . $period_array['year'][0] . "' AND sgrouplink.gid='$gid'";
  $mysql_query = $sql_query;
  //echo $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);
  //echo mysql_error($userlink);
  if (mysql_num_rows($sql_result)!=0)
  {
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
      $final_results_array[mysql_result($sql_result,$r,'mid')][mysql_result($sql_result,$r,'sid')] = mysql_result($sql_result,$r,'result');
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0

  // Get the list of average final grades
  $sql_query = "SELECT mid,AVG(result) as `average` FROM student inner join gradestore using (sid) LEFT JOIN sgrouplink USING(sid) where period='0' AND gradestore.year='" . $period_array['year'][0] . "' AND sgrouplink.gid='$gid' GROUP BY mid";
  $mysql_query = $sql_query;
  //echo $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);
  echo mysql_error($userlink);
  if (mysql_num_rows($sql_result)!=0)
  {
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
      $final_average_array[mysql_result($sql_result,$r,'mid')] = mysql_result($sql_result,$r,'average');
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
  echo("<html><head><title>" . $dtext['grdo_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['grdo_title'] . "</font><p>");
  echo("<a href=teacherpage.php>" . $dtext['back_teach_page'] . "</a><br>");
  echo("<br><div align=left>" . $dtext['grdo_expl_1'] . "</dev><br>"); 

  // Show for which group current editing and allow changing the group
  echo($dtext['grdo_view4'] . " <b>$CurrentGroup</b> (<a href=selectgroup.php?ReturnTo=viewgrades.php>" . $dtext['Change'] . "</a>)<br>");
  echo("<br>");

  // Now create a table with all students in the group to enable to go to their grade details
  // Create the first heading row for the table
  echo("<table border=1 cellpadding=0>");
  echo("<tr><th ROWSPAN=2><center>" . $dtext['Lastname'] . "</th>");
  echo("<th ROWSPAN=2><center>" . $dtext['Firstname'] . "</th>");
  // Now add the heading for the subjects
  for($s=1;$s<=$subjects;$s++)
  {
    echo("<th COLSPAN=" . ($periods + 1) . "<center>");
    echo("<a href=viewsubjectgrades.php?cid=" . $subject_array['cid'][$s] . ">");
    echo($subject_array['shortname'][$s]);
    echo("</a>");
	if(isset($subject_array['tcode'][$s]) && $subject_array['tcode'] != "")
	{
	  echo("<font size=-1>(". $subject_array['tcode'][$s]. ")</font>");
	}
	echo("</th>");
  }
  echo("</tr>");
  // Create the second heading row for the table
  echo("<tr>");
  // Now add the periods below each subject
  for($s=1;$s<=$subjects;$s++)
  {
    for($p=1; $p<=$periods; $p++)
    {
      echo("<td><center><a href=viewsubjectgradedetails.php?period=$p&cid=" . $subject_array['cid'][$s] . ">". $dtext['Period_marker']. $p . "</a></td>");
    }
    echo("<td><center>" . $dtext['fin_per_ind'] . "</td>");    
  }
  echo("</tr>");
  

  // Create a row in the table for every existing student in the group
  for($r=1;$r<=$row_n;$r++)
  {
    $sid = $student_array['sid'][$r];
    echo("<tr><td>");
    echo("<a href=viewgradecard.php?sid=" . $sid .">");
    echo($student_array['lastname'][$r]. "</a></td>");
    echo("<td><a href=viewgradecard.php?sid=" . $sid .">");
    echo($student_array['firstname'][$r]."</a></td>");
    // Add the Grades
    for($s=1;$s<=$subjects;$s++)
    { // each subject
      $mid = $subject_array['mid'][$s];
      for($p=1;$p<=$periods;$p++)
      { // add the grades for regular periods
        $pp = $period_array['id'][$p];
        echo("<td><center><a href=viewstudentgradedetails.php?period=$pp&sid=$sid&mid=$mid>");
        if(isset($results_array[$pp][$mid][$sid]))
        { 
          $result = $results_array[$pp][$mid][$sid];
		  if($results_array[$pp][$mid][$sid] < '@')
		  { // Numeric result
            // Colour depends on pass criteria
            if($passpoint[$mid] > $result) echo("<font color=red>");
            else echo("<font color=blue>");
            if($period_array['status'][$pp] == 'final') echo("<b>"); else echo("<i>");
            echo(number_format($results_array[$pp][$mid][$sid],$digits['digits'][1],$dtext['dec_sep'],$dtext['mil_sep']));
            if($period_array['status'][$pp] == 'final') echo("</b>"); else echo("</i>");
            echo("</font>");
		  }
		  else // Alphabetic result
		    echo($results_array[$pp][$mid][$sid]);
        }
        else
          echo("-");
        echo("</a></td>");
      }
      // Add the final grade
      echo("<td><center>");
      if(isset($final_results_array[$mid][$sid]))
      {
        $result = $final_results_array[$mid][$sid];
        // Colour depends on pass criteria
        if($passpoint[$mid] > $result) echo("<font color=red>");
        else echo("<font color=blue>");
        if($any_open == 'N') echo("<b>"); else echo("<i>");
        echo($final_results_array[$mid][$sid]);
        if($any_open == 'N') echo("</b>"); else echo("</i>");
        echo("</font>");
      }
      else
        echo("-");
      echo("</td>");
    }
    echo("</tr>");
  }
  
  // Add the averages
  echo("<tr class=average><td>");
  echo($dtext['Group_average']. "</td>");
  echo("<td>&nbsp</td>");
  // Add the Grades
  for($s=1;$s<=$subjects;$s++)
  { // each subject
    $mid = $subject_array['mid'][$s];
    for($p=1;$p<=$periods;$p++)
    { // add the average grades for regular periods
      $pp = $period_array['id'][$p];
      echo("<td>");
      if(isset($average_array[$pp][$mid]))
      { 
        $result = $average_array[$pp][$mid];
        // Colour depends on pass criteria
        if($passpoint[$mid] > $result) echo("<font color=red>");
        else echo("<font color=blue>");
        if($period_array['status'][$pp] == 'final') echo("<b>"); else echo("<i>");
        echo(number_format($average_array[$pp][$mid],$digits['digits'][1],$dtext['dec_sep'],$dtext['mil_sep']));
        if($period_array['status'][$pp] == 'final') echo("</b>"); else echo("</i>");
        echo("</font>");
      }
      else
        echo("-");
      echo("</td>");
    }
    // Add the final average grade
    echo("<td><center>");
    if(isset($final_average_array[$mid]))
    {
      $result = $final_average_array[$mid];
      // Colour depends on pass criteria
      if($passpoint[$mid] > $result) echo("<font color=red>");
      else echo("<font color=blue>");
      if($any_open == 'N') echo("<b>"); else echo("<i>");
      echo(number_format($final_average_array[$mid],$digits['digits'][1],$dtext['dec_sep'],$dtext['mil_sep']));
      if($any_open == 'N') echo("</b>"); else echo("</i>");
      echo("</font>");
    }
    else
      echo("-");
    echo("</td>");
  }
  echo("</tr>");

  
  echo("</table>");

  // Now we show links for each period to print the results for each student
  foreach($period_array['id'] AS $p)
    if($p != 0)
      echo("<BR><a href=\"printperiodcard.php?period=". $p. "\" target=\"print\">". $dtext['print_res']. " ". $dtext['4_per']. " ". $p. "</a>");

  echo '<BR><BR><a href="teacherpage.php">';
  echo $dtext['back_teach_page'];
  echo '</a>';
   // close the page
  echo("</html>");
?>
