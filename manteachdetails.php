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
  $fields = SA_loadquery("SELECT * FROM teacher_details WHERE overview=1 ORDER BY seq_no");

  // Get the data from existing teachers in an array.
  $sql_query = "SELECT * FROM teacher WHERE is_gone <> 'Y' ORDER BY lastname,firstname";
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
	  if(substr($tname,0,1) != "*")
	  {
	    $extradata = SA_loadquery("SELECT `data` FROM teacher LEFT JOIN `". $tname. "` ON(`". $tname. "`.tid=teacher.tid) ORDER BY lastname,firstname");
		if(isset($extradata))
		  $grade_array[$tname] = $extradata['data'];
	  }
	}
  }

  SA_closeDB();

  // First part of the page
  echo("<html><head><title>" . $dtext['teachdet_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['teachdet_title'] . "</font><p>");
  echo '<a href="teacherpage.php">';
  echo($dtext['back_teach_page'] . "</a><br>");
  echo("<br><div align=left>" . $dtext['teachdet_expl_1a']);
  if($LoginType == "A") echo(" " . $dtext['teachdet_expl_1b']);
  echo(" ". $dtext['teachdet_expl_1c']); 

  // Now create a table with all teachers to enable to view or edit their details
  // Create the heading row for the table
  echo("<br><table border=1 cellpadding=0>");
  echo("<tr>");
  //echo("<td><center>" . $dtext['Lastname'] . "</td>");
  //echo("<td><center>" . $dtext['Firstname'] . "</td>");
  foreach($fields['label'] AS $fieldname)
  {
    echo("<td><center>". $fieldname. "</td>");
  }

  echo("<td>&nbsp</td>");
  if($LoginType == "A")
    echo("<td>&nbsp</td>");
  echo("</tr>");

  // Create a row in the table for every existing teacher
  for($r=1;$r<=$row_n;$r++)
  {
    echo("<tr><form method=post action=viewteachdetails.php name=vt". $r. ">");
    // Put in the hidden field for teacher id 
    //echo("<td>");
    //echo($grade_array['lastname'][$r]. "</td>");
    //echo("<td>" . $grade_array['firstname'][$r]."</td>");
	foreach($fields['table_name'] AS $fix => $tname)
	{
	  echo("<td>");
	  if($tname == "*teacher.lastname")
	    echo($grade_array['lastname'][$r]);
	  else if ($tname == "*teacher.firstname")
	    echo($grade_array['firstname'][$r]);
	  else if($tname == "*tid")
	    echo($grade_array['tid'][$r]);
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
    echo("<td><center><input type=hidden name=tid value=" . $grade_array['tid'][$r] ."><img src='PNG/search.png' title='". $dtext['VIEW_CAP']. "' onclick='document.vt". $r. ".submit();'></td></form>");
    // Add the edit button if admin user
    if($LoginType == "A")
    {
      echo("<form method=post action=editteachdetails.php name=et". $r. ">");
      echo("<td><center><input type=hidden name=tid value=" . $grade_array['tid'][$r] . ">");
      //echo("<input type=submit value=" . $dtext['Edit'] . "></td></form></tr>");
      echo("<center><img src='PNG/reply.png' title='". $dtext['Edit']. "' onclick='document.et". $r. ".submit();'></td></form></tr>");
    }
	else
	  echo("</tr>");
  }
  echo("</table>");
  echo '<a href="teacherpage.php">';
  echo $dtext['back_teach_page'];
  echo '</a>';
  // close the page
  echo("</html>");

?>
