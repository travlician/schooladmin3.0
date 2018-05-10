<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014 Aim4me N.V.   (http://www.aim4me.info)       |
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
  require_one("inputlib/inputclasses.php");

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
  // Get the fields to display in the summary
  $fields = SA_loadquery("SELECT * FROM student_details WHERE overview=1 ORDER BY seq_no");

  // Get the data from existing students in an array.
  $sql_query = "SELECT * FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING (gid) WHERE active=1 AND sgroup.groupname='$CurrentGroup' ORDER BY lastname,firstname";
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
  // Add additional fields as defined in list
  if(isset($fields))
  {
    foreach($fields['table_name'] AS $ti => $tname)
	{
	  if($tname == "*package")
	  {
	    $packages = SA_loadquery("SELECT CONCAT(s_package.packagename,IF(s_package.extrasubject IS NOT NULL,CONCAT(\"+\",subject.shortname),\"\")) AS packinfo FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) LEFT JOIN s_package ON(s_package.sid=student.sid) LEFT JOIN subject ON (subject.mid=s_package.extrasubject) WHERE active=1 AND sgroup.groupname='$CurrentGroup' ORDER BY lastname,firstname");
		if(isset($packages))
   		  $grade_array['*package'] = $packages['packinfo'];
	  }
	  else if(substr($tname,0,1) != "*")
	  {
	    $extradata = SA_loadquery("SELECT `data` FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) LEFT JOIN `". $tname. "` ON(`". $tname. "`.sid=student.sid) WHERE active=1 AND sgroup.groupname='$CurrentGroup' ORDER BY lastname,firstname");
		if(isset($extradata))
		  $grade_array[$tname] = $extradata['data'];
	  }
	}
  }

  SA_closeDB();

  // First part of the page
  echo("<html><head><title>" . $dtext['studet_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['studet_title'] . "</font><p>");
  // Show for which group current editing and allow changing the group
  echo($dtext['Group_Cap'] . " <b>$CurrentGroup</b> (<a href=selectgroup.php?ReturnTo=manstudetails.php>" . $dtext['Change'] . "</a>)<br>");
  echo("<br>");

  echo '<a href="teacherpage.php">';
  echo($dtext['back_teach_page'] . "</a><br>");
  echo("<br><div align=left>" . $dtext['studet_expl_1'] . "</dev><br>");

  // Now create a table with all students in the group to enable to view or edit their details
  // Create the heading row for the table
  echo("<br><table border=1 cellpadding=0>");
  echo("<tr>");
  //echo("<td><center>" . $dtext['Lastname'] . "</td>");
  //echo("<td><center>" . $dtext['Firstname'] . "</td>");
  foreach($fields['label'] AS $fieldname)
  {
    echo("<td><center>". $fieldname. "</td>");
  }
  
  //echo("<td><center>" . $dtext['Go_reps'] . "</td>");  
  echo("<td>&nbsp</td>");
  echo("<td>&nbsp</td>");
  echo("</tr>");

  // Create a row in the table for every existing student in the group
  for($r=1;$r<=$row_n;$r++)
  {
    echo("<tr><form method=post action=viewstudetails.php name=vs". $r. ">");
    // Put in the hidden field for student id 
    //echo("<td>");
    //echo($grade_array['lastname'][$r]. "</td>");
    //echo("<td>" . $grade_array['firstname'][$r]."</td>");
	foreach($fields['table_name'] AS $fix => $tname)
	{
	  echo("<td>");
	  if($tname == "*student.lastname")
	    echo($grade_array['lastname'][$r]);
	  else if ($tname == "*student.firstname")
	    echo($grade_array['firstname'][$r]);
	  else if($tname == "*sid")
	    if(isset($altsids) && $altsids==1)
    	  echo($grade_array['altsid'][$r]);
	    else
		  echo($grade_array['sid'][$r]);
	  else if($tname == "*sgroup.groupname")
	    echo($grade_array['groupname'][$r]);
	  else if($fields['type'][$fix] == "picture" && isset($grade_array[$tname][$r]))
	    echo("<IMG SRC=". $livepictures. $grade_array[$tname][$r]. " HEIGHT=30>");
	  else
	  {
	    if(isset($grade_array[$tname][$r]))
  	      echo($grade_array[$tname][$r]);
		else
		  echo("&nbsp");
      }
	  echo("</td>");
	}
    // Add the View button
    //echo("<td><center><input type=submit value='" . $dtext['VIEW_CAP'] . "'></td></form>");
    echo("<td><center><input type=hidden name=sid value=" . $grade_array['sid'][$r] ."><img src='PNG/search.png' title='". $dtext['VIEW_CAP']. "' onclick='document.vs". $r. ".submit();'></td></form>");
    // Add the edit button
    echo("<form method=post action=editstudetails.php name=es". $r. ">");
    echo("<td><center><input type=hidden name=sid value=" . $grade_array['sid'][$r] . ">");
    //echo("<input type=submit value=" . $dtext['Edit'] . "></td></form></tr>");
    echo("<center><img src='PNG/reply.png' title='". $dtext['Edit']. "' onclick='document.es". $r. ".submit();'></td></form></tr>");
  }
  echo("</table>");

  // Add the link to seach a student based on the details
  echo("<br><a href=detailstudsearch.php>" . $dtext['Search_stu_det'] . "</a>");
  echo '<br><a href="teacherpage.php">';
  echo $dtext['back_teach_page'];
  echo '</a><br>';
  
  // Show a drop down list of tables for choices
  $ctablenameq = inputclassbase::load_query("SELECT params FROM student_details WHERE type='choice' AND params LIKE '*%'");
  if(isset($ctablenameq['params']))
  {
    echo("<FORM METHOD=POST NAME=stablename ID=stablename action=". $_SERVER['PHP_SELF']. "><SELECT NAME=stabbleselect onChange='document.stablename.submit();'><OPTION VALUE=''> </OPTION>");
	foreach($ctablenameq['params'] AS $ctabopt)
	  echo("<OPTION VALUE='". substr($ctabopt,1). "'". ((isset($_POST['stableselect']) && $_POST['stableselect'] == substr($ctabopt,1)) ? ' selected' : ''). ">". substr($ctabopt,1). "</OPTION>");
	echo("</SELECT></FORM>");
  }
  // close the page
  echo("</html>");

?>
