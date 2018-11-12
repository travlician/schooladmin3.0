<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011 Aim4me N.V.   (http://www.aim4me.info)       |
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
	if(!isset($pngsource))
		$pngsource="PNG";
	
	// If schoolyear changed, we do that first thing!
	if(isset($_POST['newyear']))
	{
		mysql_query("UPDATE period SET year='". $_POST['newyear']. "',status='open',startdate=DATE_ADD(startdate, INTERVAL 1 YEAR),enddate=DATE_ADD(enddate,INTERVAL 1 YEAR)");
		echo(mysql_error());
	}

  // First we get all the data from existing periods in an array.
  $sql_query = "SELECT * FROM period ORDER BY id";
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
  SA_closeDB();

  // First part of the page
  echo("<html><head><title>" . $dtext['perman_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['perman_title'] . "</font><p>");
  echo '<a href="admin.php">';
  echo($dtext['back_admin'] . "</a><br>");
  echo("<br><div align=left>" . $dtext['perman_expl_1']);
  echo("<br>\"" . $dtext['Open'] . "\" " . $dtext['perman_expl_2']);
  echo("<br>\"" . $dtext['Closed'] . "\" " . $dtext['perman_expl_3']);
  echo("<br>\"" . $dtext['Final'] . "\" " . $dtext['perman_expl_4']);
  echo("<br>" . $dtext['perman_expl_5']);
  echo(" " . $dtext['perman_expl_6'] . "</dev><br><br>");
  echo("<table border=1 cellpadding=0>");
  
  // Create the heading row for the table
  echo("<tr><td><center>" . $dtext['Period'] . "</td>");
  echo("<td><center>" . $dtext['Status'] . "</td>");
  echo("<td><center>" . $dtext['Year'] . "</td>");
  echo("<td><center>" . $dtext['Startdate'] . " (YYYY-MM-DD)</td>");
  echo("<td><center>" . $dtext['Enddate'] . " (YYYY-MM-DD)</td>");
  echo("<td></td>");
  echo("<td></td></font></tr>");

  // Create a row in the table for every existing period
  for($r=1;$r<=$row_n;$r++)
  {
    echo("<tr><form method=post action=updperiod.php name=up". $r. ">");

    echo("<td><center><input type=hidden name=id value=" . $grade_array['id'][$r] .">");
    echo($grade_array['id'][$r]."</td>");
    // Add the status
    echo("<td><center><select name=status>");
    echo("<option value=open");
    if($grade_array['status'][$r] == "open")
       echo(" selected");
    echo(">" . $dtext['Open'] . "</option>");
    echo("<option value=closed");
    if($grade_array['status'][$r] == "closed")
       echo(" selected");
    echo(">" . $dtext['Closed'] . "</option>");
    echo("<option value=final");
    if($grade_array['status'][$r] == "final")
       echo(" selected");
    echo(">" . $dtext['Final'] . "</option>");
    echo("</select></td>");
    // Add the year field
    echo("<td><input type=hidden size=20 name='year' value='" . $grade_array['year'][$r] . "'>". $grade_array['year'][$r] . "</td>");
    // Add the start date field
    echo("<td><input type=text size=20 name='startdate' value='" . $grade_array['startdate'][$r] . "'></td>");
    // Add the enddate field
    echo("<td><input type=text size=20 name='enddate' value='" . $grade_array['enddate'][$r] . "'></td>");
    // Add the change button
    //echo("<td><center><input type=submit value=" . $dtext['Change'] . "></td></form>");
    echo("<td><center><img src='". $pngsource. "/action_check.png' title='". $dtext['Change']. "' onclick='document.up". $r. ".submit();'></td>");
		// Add the recalc button
    echo("<td><center><input type=hidden name=recalc value=0 id=recalc". $r. "><img src='". $pngsource. "/calc.png' title='". $dtext['Recalc_submit']. "' onclick=' document.getElementById(\"recalc". $r. "\").value=1; document.up". $r. ".submit();'></td></form>");

    // Add the delete button
    if($r == $row_n)
    { // last entry, can delete!
      echo("<form method=post action=delperiod.php name=delper><input type=hidden name=id value=");
      echo($grade_array['id'][$r]);
      //echo("><td><center><input type=submit value=" . $dtext['Delete'] . "></td></form></tr>");
      echo("><td><center><img src='". $pngsource. "/action_delete.png' title='". $dtext['Delete']. "' onclick='if(confirm(\"". $dtext['perman_expl_6']. "\")) { document.delper.submit(); }'></td></form></tr>");
    }
    else
    { // Not last entry, can NOT delete
      echo("<td></td></tr>"); 
    }
  }
  // Insert the row for a new period
  echo("<tr><form method=post action=updperiod.php name=newper>");
  // id is fixed, calculated here
  $fixid = $row_n + 1;

  echo("<td><center><input type=hidden name=id value=\"\">$fixid</td>");
  // Add the status options
  echo("<td><center><select name=status>");
  echo("<option value=open>" . $dtext['Open'] . "</option>");
  echo("<option value=closed>" . $dtext['Closed'] . "</option>");
  echo("<option value=final>" . $dtext['Final'] . "</option>");
  echo("</select></td>");
  // Add the year field
  echo("<td><input type=hidden size=20 name='year' value='". $grade_array['year'][1] . "'>". $grade_array['year'][1] . "</td>");
  // Add the year field
  echo("<td><input type=text size=20 name='startdate'></td>");
  // Add the year field
  echo("<td><input type=text size=20 name='enddate'></td>");
  // Add the ADD button
  //echo("<td><center><input type=submit value=" . $dtext['ADD_CAP'] . "></td></form>");
  echo("<td><center><img src='". $pngsource. "/action_add.png' title='". $dtext['ADD_CAP']. "' onclick='document.newper.submit();'></td></form>");
  // Here we don't have a delete button!
  echo("<td></td></tr>");
  
  // close the table
  echo("</table>");
	
	// Show the dialog part to create a new schoolyear
	echo("<BR><BR><FORM METHOD=POST NAME=newyearform>". $dtext['New_year']. " : <INPUT TYPE=TEXT NAME=newyear onChange='document.newyearform.submit();'></FORM>");
  echo("</html>");

?>
