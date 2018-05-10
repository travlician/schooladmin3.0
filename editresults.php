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
   
  $uid = intval($uid);
  $tdid = $HTTP_POST_VARS['tdid'];

  if($tdid == "")
  {
    echo("Undefined testid, <a href='mantests.php'>" . $dtext['back_testman'] . "</a>");
    SA_closeDB();
    exit;
  }

  // Create an array with the current test results
  $sql_query = "SELECT testresult.sid,testresult.result FROM testresult WHERE tdid='$tdid'";
  $sql_result = mysql_query($sql_query,$userlink);
  //echo mysql_error($userlink);
  $nrows = 0;
  if (mysql_num_rows($sql_result)!=0)
  {
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
     $nrows++;
     $result_array[mysql_result($sql_result,$r,'sid')] = mysql_result($sql_result,$r,'result');
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $result_n = $nrows;



  // Create an array with the test details
  $sql_query = "SELECT * FROM testtype INNER JOIN testdef USING (type) INNER JOIN class USING (cid) INNER JOIN subject USING (mid) WHERE tdid='$tdid'";

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
       $testdef_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $testdef_n = $nrows;
  
  // See if this testdef is for a group for which filtering based on subject package is active
  // Create an array with the students in the current group
  $isfiltered = SA_loadquery("SELECT * FROM subjectfiltergroups WHERE `group`=". $testdef_array['gid'][1]);
  if(isset($isfiltered))
  {
    $sql_querys = "SELECT student.*,t1.* FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING (gid) 
                   LEFT JOIN (select sid 
                   from s_package 
                   left join subjectpackage USING(packagename)
                   WHERE mid=". $testdef_array['mid'][1]. " OR extrasubject=". $testdef_array['mid'][1]. "
                   group by sid) AS t1 on (`student`.sid=t1.sid)
                   WHERE active=1 AND sgroup.groupname='$CurrentGroup' AND t1.sid IS NOT NULL ORDER BY student.lastname, student.firstname";
  }
  else
  {
    $sql_querys = "SELECT student.* FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING (gid) WHERE active=1 AND sgroup.groupname='$CurrentGroup'";
    $sql_querys .= " ORDER BY student.lastname, student.firstname";
  }
  $sql_result = mysql_query($sql_querys,$userlink);
  // echo mysql_error($userlink);
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
       $student_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $student_n = $nrows;
  
  SA_closeDB();

  // First part of the page
  echo("<html><head><title>" . $dtext['testres_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['testres_title'] . "</font><p>");
  echo '<a href="mantests.php">';
  echo ($dtext['back_testdef']);
  echo '</a><br>';

  // Show the test details
  echo("<div align=left>" . $dtext['testres_expl_1'] . " <b>$CurrentGroup</b>, <b>");
  echo($testdef_array['translation'][1] . "</b>: <b>" . $testdef_array['description'][1]);
  echo("</b> " . $dtext['for'] . " <b>" . $testdef_array['fullname'][1] . "</b> " . $dtext['on'] . " <b>");
  echo($testdef_array['date'][1] . "</b> " . $dtext['in_per'] . " <b>" . $testdef_array['period'][1] . "</b>.<br>");

  echo($dtext['testres_expl_2'] . "</dev><br>");

  echo("<form method=post action=updtestres.php><input type=hidden name=tdid value='$tdid'>");
  // Create the heading row for the table
  echo("<table border=1 cellpadding=0>");
  echo("<tr><td><center>" . $dtext['Lastname'] . "</td>");
  echo("<td><center>" . $dtext['Firstname'] . "</td>");
  echo("<td><center>" . $dtext['Result'] . "</td></font></tr>");


  // Create a row in the table for every existing student
  for($r=1;$r<=$student_n;$r++)
  {
    echo("<tr><td>" . $student_array['lastname'][$r]. "</td>");
    echo("<td>" . $student_array['firstname'][$r]. "</td>");
    echo("<td><center><input type=hidden name=sno".$r." value=".$student_array['sid'][$r].">");
    echo("<input type=text size=4 name='sres" .$r."' value='");
    if(isset($result_array[$student_array['sid'][$r]]))
      echo($result_array[$student_array['sid'][$r]]);
    echo("'></td></tr>");
  }
  // close the table
  echo("</table>");

  // Put the commit button
  echo("<input type=submit value='" . $dtext['COMM_CHNG_CAP'] . "'>");


  echo("</html>");

?>
