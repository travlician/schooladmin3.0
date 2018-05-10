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
  if(isset($HTTP_GET_VARS['cid']))
    $cid = $HTTP_GET_VARS['cid'];
  if(isset($HTTP_GET_VARS['period']))
    $period = $HTTP_GET_VARS['period'];
  
  $uid = intval($uid);

  // First we get the data from existing students in an array.
  $sql_query = "SELECT * FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) WHERE active=1 AND sgroup.groupname='$CurrentGroup' ORDER BY lastname,firstname";
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
  $sql_query = "SELECT * FROM subject inner join class using (mid) where gid='$gid' AND cid='$cid'";
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
  $mid = $subject_array['mid'][1];

  // Get the list of test definitions with their details
  $sql_query = "SELECT tdid,mid,short_desc,type FROM testdef LEFT JOIN class USING(cid) LEFT JOIN period ON(period.id=testdef.period) WHERE mid='$mid' AND gid='$gid' AND period='$period' AND period.year=testdef.year AND testdef.type <> '0' ORDER BY date";
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


  // Get the list of grades for the given subject & period
  $sql_query = "SELECT testdef.tdid,sid,result FROM testdef inner join testresult using (tdid) LEFT JOIN class ON (testdef.cid=class.cid) where mid='$mid' AND gid='$gid' AND period='$period'";
  $mysql_query = $sql_query;
  //echo $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);
  echo mysql_error($userlink);
  if (mysql_num_rows($sql_result)!=0)
  {
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
      $results_array[mysql_result($sql_result,$r,'sid')][mysql_result($sql_result,$r,'tdid')] = mysql_result($sql_result,$r,'result');
    mysql_free_result($sql_result);
  }//If numrows != 0

  // Get the list of average grades for the given subject & period
  $sql_query = "SELECT testdef.tdid,AVG(result) AS `average` FROM testdef inner join testresult using (tdid) LEFT JOIN class ON (testdef.cid=class.cid) where mid='$mid' AND gid='$gid' AND period='$period' GROUP BY tdid";
  $mysql_query = $sql_query;
  //echo $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);
  echo mysql_error($userlink);
  if (mysql_num_rows($sql_result)!=0)
  {
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
      $average_array[mysql_result($sql_result,$r,'tdid')] = mysql_result($sql_result,$r,'average');
    mysql_free_result($sql_result);
  }//If numrows != 0

  // Get the list of stored grades for the given subject & period
  $sql_query = "SELECT * FROM period,student inner join gradestore using (sid) LEFT JOIN sgrouplink USING(sid) where period=". $period. " AND gradestore.year=period.year AND sgrouplink.gid='$gid' AND mid='$mid'";
  $sql_result = mysql_query($sql_query,$userlink);
  if(mysql_num_rows($sql_result) != 0)
  {
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
      $period_result[mysql_result($sql_result,$r,'sid')] = mysql_result($sql_result,$r,'result'); 
    }
  }
  // Get the average of stored grades for the given subject & period
  $sql_query = "SELECT AVG(result) AS average FROM period,student inner join gradestore using (sid) LEFT JOIN sgrouplink USING(sid) where period=". $period. " AND gradestore.year=period.year AND sgrouplink.gid='$gid' AND mid='$mid' GROUP BY period";
  $sql_result = mysql_query($sql_query,$userlink);
  echo(mysql_error($userlink));
  if(mysql_num_rows($sql_result) != 0)
  {
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
      $period_average = mysql_result($sql_result,$r,'average'); 
    }
  }

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
      $passpoint2[mysql_result($sql_result,$r,'mid')] = mysql_result($sql_result,$r,'minimumpass');
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0

  $digits = SA_loadquery("SELECT MAX(digitsafterdot) AS digits FROM reportcalc");

  SA_closeDB();

  // First part of the page
  echo("<html><head><title>" . $dtext['tressub_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['Tres_4'] . " " . $subject_array['fullname'][1] . " " . $dtext['in_grp'] . " " . $CurrentGroup . " " . $dtext['4_per'] . " " .$period . "</font><p>");
  echo("<a href=teacherpage.php>" . $dtext['back_teach_page'] . "</a><br>");
  echo("<a href=viewgrades.php>" . $dtext['back_grades'] . "</a><br>");
  echo("<br><div align=left>" . $dtext['tressub_expl_1'] . "</dev><br>"); 

  echo("<br>");

  // Now create a table with all students in the group to enable to go to their grade details
  // Create the first heading row for the table
  echo("<table border=1 cellpadding=0>");
  echo("<tr><td><center>" . $dtext['Lastname'] . "</td>");
  echo("<td><center>" . $dtext['Firstname'] . "</td>");
  // Now add the test definitions
  for($t=1; $t<=$testdefs; $t++)
    echo("<td><center>" . $testdef_array['short_desc'][$t] . "</td>");
  echo("<td align=center class=endgrade>". $dtext['Period_marker']. $period. "</td>");
  echo("</tr>");    
  

  // Create a row in the table for every existing student in the group
  for($r=1;$r<=$row_n;$r++)
  {
    $sid = $student_array['sid'][$r];
    echo("<tr><td>");
    echo("<a href=viewstudentgradedetails.php?sid=" . $sid ."&period=" . $period . "&mid=" . $mid . ">");
    echo($student_array['lastname'][$r]. "</a></td>");
    echo("<td><a href=viewstudentgradedetails.php?sid=" . $sid ."&period=" . $period . "&mid=" . $mid . ">");
    echo($student_array['firstname'][$r]."</a></td>");
    // Add the Grades
    for($t=1;$t<=$testdefs;$t++)
    { 
      echo("<td><center>");
      if(isset($results_array[$sid][$testdef_array['tdid'][$t]]))
      { 
        $result = $results_array[$sid][$testdef_array['tdid'][$t]];
		if($result < '@')
		{
          // Colour depends on pass criteria
          if($passpoint[$testdef_array['type'][$t]] > $result) echo("<font color=red>");
          else echo("<font color=blue>");
          echo(number_format($result,$digits['digits'][1],$dtext['dec_sep'],$dtext['mil_sep']));
          echo("</font>");
		}
		else
		  echo($result);
      }
      else
        echo("-");
      echo("</td>");
    }
    // Show the calculated period prognose/result
    echo("<td class=endgrade>");
    if(isset($period_result[$sid]))
    {
      if($period_result[$sid] < '@')
	  {
        if($passpoint2[$mid] > $period_result[$sid])
          echo("<font color=red>");
        else
          echo("<font color=blue>");
        echo(number_format($period_result[$sid],$digits['digits'][1],$dtext['dec_sep'],$dtext['mil_sep']). "</font></td>");
	  }
	  else
	    echo($period_result[$sid]. "</td>");
    }
    else
      echo("-</td>");
    echo("</tr>");
  }

  // Show group averages
  echo("<tr class=average><td>");
  echo($dtext['Group_average']. "</td>");
  echo("<td>&nbsp</td>");
  // Add the Grades
  for($t=1;$t<=$testdefs;$t++)
  { 
    echo("<td><center>");
    if(isset($average_array[$testdef_array['tdid'][$t]]))
    { 
      $result = round($average_array[$testdef_array['tdid'][$t]],2);
      // Colour depends on pass criteria
      if($passpoint[$testdef_array['type'][$t]] > $result) echo("<font color=red>");
      else echo("<font color=blue>");
      echo(number_format($result,2,$dtext['dec_sep'],$dtext['mil_sep']));
      echo("</font>");
    }
    else
      echo("-");
    echo("</td>");
  }
  // Show the calculated period prognose/result
  if(isset($period_average))
  {
    echo("<td class=endgrade>");
    if($passpoint2[$mid] > $period_average)
      echo("<font color=red>");
    else
      echo("<font color=blue>");
    echo(number_format($period_average,2,$dtext['dec_sep'],$dtext['mil_sep']). "</font></td>");
  }
  else 
    echo("<td>&nbsp</td>");
  echo("</tr>");

  echo("</table>");
  echo '<a href="teacherpage.php">';
  echo $dtext['back_teach_page'];
  echo '</a>';
 
  // close the page
  echo("</html>");

?>
