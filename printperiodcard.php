<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2015 Aim4me N.V.   (http://www.aim4me.info)	      |
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
  require_once("student.php");
  inputclassbase::dbconnect($userlink);

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];
  if(isset($HTTP_POST_VARS['NewGroup']) && SA_verifyGroupAccess($uid,$HTTP_POST_VARS['NewGroup']))
    $CurrentGroup = $HTTP_POST_VARS['NewGroup'];
  if(isset($HTTP_GET_VARS['NewGroup']) && SA_verifyGroupAccess($uid,$HTTP_GET_VARS['NewGroup']))
    $CurrentGroup = $HTTP_GET_VARS['NewGroup'];
  // Store the new group or future pages
  $_SESSION['CurrentGroup']=$CurrentGroup;
  if(isset($HTTP_GET_VARS['period']))
    $period = $HTTP_GET_VARS['period'];
	
	// Get the schoolyear
	$schoolyear = SA_loadquery("SELECT year FROM period");
	$schoolyear = $schoolyear['year'][1];
  
  $uid = intval($uid);

  // Translate the current group to a group id (gid)
  $sql_result = mysql_query("SELECT gid FROM sgroup WHERE active=1 AND groupname='$CurrentGroup'",$userlink);
  $gid = mysql_result($sql_result,0,'gid');

  // First we get the data from the students in an array.
  $sql_query = "SELECT * FROM student LEFT JOIN sgrouplink USING(sid) WHERE gid='$gid' ORDER BY lastname,firstname";
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
       $student_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $row_n = $nrows;

  // Get the list of periods with their details
  $sql_query = "SELECT * FROM period";
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
       $period_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $periods = $nrows;

  // Get the list of applicable subjects with their details
  $sql_query = "SELECT * FROM subject inner join class using (mid) where gid='$gid' AND show_sequence IS NOT NULL GROUP BY subject.mid ORDER BY show_sequence";
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

  foreach($student_array['sid'] AS $sidx)
  {
    // Get a list of testresults for the current period
    $sql_query = "SELECT result,type,mid,testdef.tdid FROM testresult LEFT JOIN testdef using (tdid) LEFT JOIN class USING (cid) LEFT JOIN period ON(period.id=testdef.period) where sid='$sidx' AND period='$period' AND period.year=testdef.year AND testdef.type <> '0' ORDER BY testresult.last_update";
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
       $test_array[$sidx][mysql_result($sql_result,$r,'mid')][mysql_result($sql_result,$r,'type')][mysql_result($sql_result,$r,'tdid')] = mysql_result($sql_result,$r,'result');
      } //for $r
      mysql_free_result($sql_result);
    }//If numrows != 0
    $tests[$sidx] = $nrows;
    // Get a list of the avergages
    $avgq = "SELECT * FROM gradestore WHERE sid=". $sidx. " AND year='". $period_array['year'][1]. "' AND period=". $period;
	$avgqr = SA_loadquery($avgq);
    if(isset($avgqr))
    {
      foreach($avgqr['mid'] AS $avix => $avmid)
	    $avg[$sidx][$avmid] = $avgqr['result'][$avix];
    }
  }
  
  // Get the list of pass criteria per subject & test type
  $sql_query = "SELECT * FROM reportcalc ORDER BY testtype,mid";
  $mysql_query = $sql_query;
  //echo $sql_query;
  $sql_result = mysql_query($mysql_query,$userlink);
  echo mysql_error($userlink);
  $nrows = 0;
  if (mysql_num_rows($sql_result)!=0)
  {
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
      $passpoints[mysql_result($sql_result,$r,'testtype')][mysql_result($sql_result,$r,'mid')] = mysql_result($sql_result,$r,'passthreshold');
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0

  //SA_closeDB();

  // First part of the page
  // echo("<html><head><title>" . $dtext['gcrd_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue onload=\"window.print();setTimeout(window.close(),10000);\">");
  echo("<html><head><title>" . $dtext['gcrd_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
?>
<STYLE>
.abstable TABLE TR TD
{
	font-size: 12px;
}
</STYLE>
<?
  
  foreach($student_array['sid'] AS $si => $sidx)
  {
    echo("<p class=\"pagebreak\">");
		echo("<center><img src=schoollogo.png height=100 border=0> ". $dtext['Year']. " : ". $schoolyear. "</center><BR>");
    echo("<font size=+2 color=black><center>" . $dtext['gcard_4'] . " " . $student_array['firstname'][$si] . " " .
        	$student_array['lastname'][$si] . " " . $dtext['in_grp']. " ". $CurrentGroup. " ". $dtext['4_per'] . " " .
			$period. "</font><p>");
    //echo("<a href=teacherpage.php>" . $dtext['back_teach_page'] . "</a><br>");
    //echo("<a href=viewgrades.php>" . $dtext['back_grades'] . "</a><br>");
    //echo("<br><div align=left>" . $dtext['gcrd_expl_1'] . "</dev><br>");

    echo("<br>");
    unset($testcount);
    unset($typecount);
    // Now we must find out how many entries max. for each type of test (max # of collumns)
    if(isset($test_array[$sidx]))
    {
      foreach($test_array[$sidx] AS $subji => $subtest)
      {
        foreach($subtest AS $tti => $testtype)
          $testcount[$tti][$subji] = count($testtype);
      }
    }

    if($tests[$sidx] > 0)
    {
      foreach($passpoints as $type => $value)
      {
        $typecount[$type] = 0;
        if(isset($testcount[$type]))
        {
          foreach($testcount[$type] as $count)
          {
            if($typecount[$type] < $count)
              $typecount[$type] = $count;
          }
        }
      }
    }

    if($tests[$sidx] > 0 && $period_array['status'][$period] != 'closed')
    {   
      // Now create a table with all subjects for this student to enable to go to the grade details
      // Create the first heading row for the table
      echo("<table border=1>");
      echo("<tr><td><center>" . $dtext['Subject'] . "</td>");
      // Now add types heading
      foreach($typecount as $type => $count)
      {
        if($count > 0)
          echo("<td COLSPAN='$count'><center>" . $type . "</td>");
      }
	  echo("<TD style='text-align: center; border-left: 2px solid black;'>". $period. "</td>");
      echo("</tr>"); 
  

      // Create a row in the table for every subject
      $currentTest = 1;
      for($s=1;$s<=$subjects;$s++)
      { // each subject
        $mid = $subject_array['mid'][$s];
        $cid = $subject_array['cid'][$s];
        echo("<tr><td>" . ($subject_array['type'][$s] == "sub" ? "&nbsp;&nbsp;" : ""). $subject_array['fullname'][$s] . "</td>");
        foreach($typecount as $type => $count)
        {
           if(isset($passpoints[$type][$mid]))
             $passpoint=$passpoints[$type][$mid];
           else
             $passpoint=$passpoints[$type][0];
           if(isset($testcount[$type][$mid]))
           {
             foreach($test_array[$sidx][$mid][$type] AS $tdid => $result)
             {
               echo("<td style='text-align: center'>");
               // Colour depends on pass criteria
               if($passpoint > $result) echo("<font color=red>");
               else echo("<font color=blue>");
               echo($result);
               echo("</font></td>");
             }

             // Now pad with empty cells
             for($r=$testcount[$type][$mid]; $r<$count; $r++)
               echo("<td> </td>");
           }
           else
           { // No tests found for this type & subject!
             for($r=0;$r<$count;$r++)
               echo("<td> </td>");
           }
        }
		if(isset($avg[$sidx][$mid]))
		{
               echo("<td style='text-align: center; border-left: 2px solid black;'>");
               // Colour depends on pass criteria
               if($passpoint > $avg[$sidx][$mid]) echo("<font color=red>");
               else echo("<font color=blue>");
               echo($avg[$sidx][$mid]);
               echo("</font></td>");
		}
		else
		  echo("<td style='text-align: center; border-left: 2px solid black;'> </td>");
        echo("</tr>");
      }
      echo("</tr>");
      echo("</table>");
	  if(isset($absenceonperiodcard) && $absenceonperiodcard)
	  {
	    $mystudent = new student($sidx);
	    echo($dtext['My_absence']. "<BR><DIV class=abstable>". $mystudent->get_student_detail("*absence.*"). "</DIV>");
	  }
    }
    else
    { // No test results found or period is closed
      if($period_array['status'][$period] == 'closed')
        echo($dtext['perres_expl_1']);
      else
        echo($dtext['perres_expl_2']);

    }
  } // End loop for each student

  // close the page
  echo("</html>");

?>
