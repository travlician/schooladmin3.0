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
 
  $login_qualify = 'A';
  include ("schooladminfunctions.php");


  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];
  
  $uid = intval($uid);

  // First we get all the data from existing finalcalc records in an array.
  $sql_query = "SELECT * FROM finalcalc ORDER BY mid,period";
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

  // Create a separate array with the subjects
  $sql_query = "SELECT mid,shortname FROM subject ORDER BY shortname";
  $sql_result = mysql_query($sql_query,$userlink);
  //echo mysql_error($userlink);
  $nrows = 0;
  // Add default subject!
  $subject_array['mid'][0] = "0";
  $subject_array['shortname'][0]="Default";
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
  $subject_n = $nrows;
  
  // If alternative weight factors are enabled, we represent those as subjects!
  if(isset($altwftable))
  {
    // First see which alternatives are already used
	$altused = SA_loadquery("SELECT MIN(mid) AS minmid FROM finalcalc");
	$lowestmid = $altused['minmid'][1];
	if($lowestmid >= 0)
	  $lowestmid = 0;
	// Aggregate the needed alternatives to the found subjects
	$alttxt = substr($altwftable,2);
	for($am = 0; $am >= $lowestmid; $am--)
	{
	  $subject_n++;
	  $subject_array['mid'][$subject_n] = $am - 1;
	  $subject_array['shortname'][$subject_n] = $alttxt . ($am-1);
	}	
 }

  // Create a separate array with the periods
  $sql_query = "SELECT id FROM period ORDER BY id";
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
       $period_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $period_n = $nrows;
  SA_closeDB();

  // First part of the page
  echo("<html><head><title>" . $dtext['fincalc_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['fincalc_title'] . "</font><p>");
  echo '<a href="admin.php">';
  echo($dtext['back_admin'] . "</a><br>");
  echo("<br><div align=left>" . $dtext['fincalc_expl_1']);
  echo(" " . $dtext['fincalc_expl_2']);
  echo("<br>" . $dtext['fincalc_expl_3'] . "</dev><br><br>");
  echo("<table border=1 cellpadding=0>");

  // Create the heading row for the table
  echo("<tr><td><center>" . $dtext['Subject'] . "</td>");
  echo("<td><center>" . $dtext['Period'] . "</td>");
  echo("<td><center>" . $dtext['Weight'] . "</td>");
  echo("<td></td>");
  echo("<td></td></font></tr>");

  // Create a row in the table for every existing reportcalc record
  for($r=1;$r<=$row_n;$r++)
  {
    echo("<tr><form method=post action=updfinalcalc.php name=uf". $r. ">");
    // Insert the original mid and period as hidden to be able to delete b4 reinsert.
    echo("<td><input type=hidden name=orgmid value='" . $grade_array['mid'][$r]."'>");
    echo("<input type=hidden name=orgperiod value='" . $grade_array['period'][$r]."'>");
    // Insert the options for each subject
    echo("<center><select name=mid>");
    for($sc=0;$sc<=$subject_n;$sc++)
    {
      if($grade_array['mid'][$r] == $subject_array['mid'][$sc])
        $IsSelected = "selected";
      else
        $IsSelected = "";
      echo("<option value=" . $subject_array['mid'][$sc]." " . $IsSelected.">" . $subject_array['shortname'][$sc]."</option>");
    }
    echo("</select></td>");

    // Insert the options for each period
    echo("<td><center><select name=period>");
    for($tc=1;$tc<=$period_n;$tc++)
    {
      if($grade_array['period'][$r] == $period_array['id'][$tc])
        $IsSelected = " selected";
      else
        $IsSelected = "";
      echo("<option value='" . $period_array['id'][$tc]."'" . $IsSelected.">" . $period_array['id'][$tc]."</option>");
    }
    echo("</select></td>");
    
    // Add the test type field
    echo("<td><input type=text size=5 name=weight value=\"" . $grade_array['weigth'][$r] ."\"></td>");

    // Add the change button
    //echo("<td><center><input type=submit value=" . $dtext['Change'] . "></td></form>");
    echo("<td><center><img src='PNG/action_check.png' title='". $dtext['Change']. "' onclick='document.uf". $r. ".submit();'></td></form>");
    // Add the delete button
    echo("<form method=post action=delfinalcalc.php name=df". $r. "><input type=hidden name=mid value=");
    echo($grade_array['mid'][$r]);
    echo("><input type=hidden name=period value=");
    echo($grade_array['period'][$r]);
    //echo("><td><center><input type=submit value=" . $dtext['Delete'] . "></td></form></tr>");
    echo("><td><center><img src='PNG/action_delete.png' title='". $dtext['Delete']. "' onclick='if(confirm(\"". $dtext['confirm_delete']. "\")) { document.df". $r. ".submit(); }'></td></form></tr>");
  }
  // Insert the row for a new reportcalc record
  echo("<tr><form method=post action=updfinalcalc.php name=newf>");
  // Insert the original mid and period as hidden to be able to delete b4 reinsert.
  echo("<td><input type=hidden name=orgmid value=\"\">");
  echo("<input type=hidden name=orgperiod value=\"\">");
  // Insert the options for each subject
  echo("<center><select name=mid>");
  for($sc=0;$sc<=$subject_n;$sc++)
  {
    echo("<option value=" . $subject_array['mid'][$sc].">" . $subject_array['shortname'][$sc]."</option>");
  }
  echo("</select></td>");

  // Insert the options for each period
  echo("<td><center><select name=period>");
  for($tc=1;$tc<=$period_n;$tc++)
  {
    echo("<option value='" . $period_array['id'][$tc]."'>" . $period_array['id'][$tc]."</option>");
  }
  echo("</select></td>");
    
  // Add the weight field
  echo("<td><input type=text size=5 name=weight value=\"1.0\"></td>");


  // Add the ADD button
  //echo("<td><center><input type=submit value=" . $dtext['ADD_CAP'] . "></td></form>");
  echo("<td><center><img src='PNG/action_add.png' title='". $dtext['ADD_CAP']. "' onclick='document.newf.submit();'></td></form>");
  // Here we don't have a delete button!
  echo("<td></tr>");
  
  // close the table
  echo("</table>");

  // Add the explanations for the fields
  echo("<br><div align=left>");
  echo("<br><u>" . $dtext['Period'] . "</u>: " . $dtext['fincalc_expl_4']);
  echo("<br><u>" . $dtext['Weight'] . "</u>: " . $dtext['fincalc_expl_5']);
  
  echo("</div></html>");

?>
