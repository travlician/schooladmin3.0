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

  $login_qualify = 'S';
  include ("schooladminfunctions.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
    
  $uid = intval($uid);
  $sid = $uid;

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

  // Get the list of applicable subjects with their details
  $sql_query = "SELECT * FROM class left join subject using (mid) left join sgrouplink using(gid) where sid='$sid' GROUP BY cid";
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

  // Get the fields to display in the summary
  $fields = SA_loadquery("SELECT * FROM teacher_details WHERE overview=1 AND raccess='A' ORDER BY seq_no");

  // Get the list teachers with their details
  $sql_query = "SELECT * FROM teacher ORDER BY lastname,firstname";
  $mysql_query = $sql_query;
  //echo $sql_query;
  $sql_result = mysql_query($mysql_query,$userlink);
  //echo mysql_error($userlink);
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
       $teacher_array[$fieldname][mysql_result($sql_result,$r,'tid')]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  
   // Add additional fields as defined in list
  if(isset($fields))
  {
    foreach($fields['table_name'] AS $ti => $tname)
	{
	  if(substr($tname,0,1) != "*")
	  {
	    $extradata = SA_loadquery("SELECT `data`,teacher.tid FROM teacher LEFT JOIN `". $tname. "` ON(`". $tname. "`.tid=teacher.tid) ORDER BY lastname,firstname");
		if(isset($extradata))
		  foreach($extradata['tid'] AS $edix => $edtid)
		    $teacher_array[$tname][$edtid] = $extradata['data'][$edix];
	  }
	}
  }

  
  // Get the mentor id for my groups if this is to be shown
  $mayshowmentors = SA_loadquery("SELECT table_name FROM teacher_details WHERE table_name='*sgroup.groupname' AND raccess ='A'");
  if(isset($mayshowmentors['table_name']))
  {
    $sql_query = "SELECT tid_mentor FROM sgroup left join sgrouplink USING(gid) WHERE active=1 AND sid='$sid' AND tid_mentor != 1";
    $tid_mentors = SA_loadquery($sql_query);
  }
  SA_closeDB();

  // First part of the page
  echo("<html><head><title>" . $dtext['shteach_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['Teach_4'] . " " . $student_array['firstname'][1] . " " . $student_array['lastname'][1] . "</font><p>");
  include("studentmenu.php");
  if(isset($tid_mentors['tid_mentor']))
    foreach($tid_mentors['tid_mentor'] AS $tid_mentor)
      echo("<br><div align=left>" . $dtext['Mentor_4U'] . " <a href=showteacherdetails.php?tid=" . $tid_mentor . ">" . $teacher_array['firstname'][$tid_mentor] . " " . $teacher_array['lastname'][$tid_mentor] . "</a></dev><br>"); 

  echo("<br>");

  // Now create a table with all subjects for this student with teacher assigned
  // Create the first heading row for the table
  echo("<table border=1 cellpadding=0>");
  echo("<tr><th><center>" . $dtext['Subject'] . "</th>");
  //echo("<td><center><b>" . $dtext['Teacher'] . "</b></td>");
  if(isset($fields))
    foreach($fields['label'] AS $fieldname)
  {
    echo("<th><center>". $fieldname. "</th>");
  }

  echo("</tr>");

  // Create a row in the table for every subject
  for($s=1;$s<=$subjects;$s++)
  { // each subject
    $mid = $subject_array['mid'][$s];
    $cid = $subject_array['cid'][$s];
    $tid = $subject_array['tid'][$s];
    echo("<tr><td>" . $subject_array['fullname'][$s] . "</td>");
    //echo("<td><a href=showteacherdetails.php?tid=" . $tid . ">" . $teacher_array['firstname'][$tid] . " " . $teacher_array['lastname'][$tid] . "</a></td>");
	if(isset($fields))
	  foreach($fields['table_name'] AS $fix => $tname)
	{
	  echo("<td><a href=showteacherdetails.php?tid=" . $tid . ">");
	  if($tname == "*teacher.lastname")
	    echo($teacher_array['lastname'][$tid]);
	  else if ($tname == "*teacher.firstname")
	    echo($teacher_array['firstname'][$tid]);
	  else if($tname == "*tid")
	    echo($teacher_array['tid'][$tid]);
	  else if($fields['type'][$fix] == "picture" && isset($teacher_array[$tname][$tid]))
	    echo("<IMG SRC=". $livepictures. $teacher_array[$tname][$tid]. " HEIGHT=30>");
	  else
	  {
	    if(isset($teacher_array[$tname][$tid]))
  	      echo($teacher_array[$tname][$tid]);
		else
		  echo("&nbsp");
      }
	  echo("</a></td>");
	}

	echo("</tr>");
  }
  echo("</table>");
  // close the page
  echo("</html>");

?>
