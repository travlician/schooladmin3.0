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

  // First we get all the data from existing subjects in an array.
  $sql_query = "SELECT * FROM subject ORDER BY shortname";
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
  SA_closeDB();
  $row_n = $nrows;

  // Create a separate array with the meta subjects
  $meta_count=0;
  $meta_subs['mid'][$meta_count]="0";
  $meta_subs['name'][$meta_count]="";
  for($r=1;$r<=$row_n;$r++)
  {
    if($grade_array['type'][$r] == 'meta')
    { 
      $meta_count++;
      $meta_subs['mid'][$meta_count]=$grade_array['mid'][$r];
      $meta_subs['name'][$meta_count]=$grade_array['shortname'][$r];
    }
  }
  // First part of the page
  echo("<html><head><title>" . $dtext['subman_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['subman_title'] . "</font><p>");
  echo '<a href="admin.php">';
  echo($dtext['back_admin'] . "</a><br>");
  echo("<br><div align=left>" . $dtext['subman_expl_1']);
  echo("<br>" . $dtext['subman_expl_2']);
  echo("<br>" . $dtext['subman_expl_3']);
  echo("<br>" . $dtext['subman_expl_4'] . "</dev><br>");
  echo("<table border=1 cellpadding=1>");
  
  // Create the heading row for the table
  echo("<tr><td><center>" . $dtext['Short'] . "</td>");
  echo("<td><center>" . $dtext['Fullname'] . "</td>");
  echo("<td><center>" . $dtext['Type'] . "</td>");
  echo("<td><center>" . $dtext['Meta_sub'] . "</td>");
  echo("<td>&nbsp</td>");
  echo("<td>&nbsp</td></font></tr>");

  // Create a row in the table for every existing subject
  for($r=1;$r<=$row_n;$r++)
  {
    echo("<tr><form method=post action=updsubject.php name=us". $r. ">");

    echo("<td><center><input type=hidden name=mid value=" . $grade_array['mid'][$r] .">");
    echo("<input type=text size=8 name=shortname value=\"" . $grade_array['shortname'][$r] ."\"></td>");
    echo("<td><center><input type=text size=20 name=fullname value=\"" . $grade_array['fullname'][$r] ."\"></td>");
    // Add the type
    // echo("t="); echo($grade_array['type'][$r]);
    if($grade_array['type'][$r] == "normal")
      {$NormSelect = "selected"; $SubSelect = ""; $MetaSelect="";}
    if($grade_array['type'][$r] == "sub")
      {$NormSelect = ""; $SubSelect = "selected"; $MetaSelect="";}
    if($grade_array['type'][$r] == "meta")
      {$NormSelect = ""; $SubSelect = ""; $MetaSelect="selected";}
    echo("<td><center><select name=type><option value=normal " .$NormSelect.">" . $dtext['Normal'] . "</option>");
    echo("<option value=sub " .$SubSelect.">" . $dtext['Sub'] . "</option>");
    echo("<option value=meta " .$MetaSelect.">" . $dtext['Meta'] . "</option></select></td>");
    // Add the meta subject
    echo("<td><center><select name=meta_subject>");
    for($mc=0;$mc<=$meta_count;$mc++)
    { // add each meta subject!
      if($grade_array['meta_subject'][$r] == $meta_subs['mid'][$mc]) $SelectInd = "selected";
      else $SelectInd = "";
      echo("<option value=" . $meta_subs['mid'][$mc]. " " . $SelectInd.">");
      echo($meta_subs['name'][$mc]);
      echo("</option>");
    }
    echo("</select></td>");
    // Add the change button
    //echo("<td><center><input type=submit value=" . $dtext['Change'] . "></td></form>");
    echo("<td><center><img src='PNG/action_check.png' title='". $dtext['Change']. "' onclick='document.us". $r. ".submit();'></td></form>");
    // Add the delete button
    echo("<form method=post action=delsubject.php name=ds". $r. "><input type=hidden name=mid value=");
    echo($grade_array['mid'][$r]);
    //echo("><td><center><input type=submit value=" . $dtext['Delete'] . "></td></form></tr>");
    echo("><td><center><img src='PNG/action_delete.png' title='". $dtext['Delete']. "' onclick='if(confirm(\"". $dtext['man_class_expl_5']. "\")) { document.ds". $r. ".submit(); }'></td></form><tr>");
  }
  // Insert the row for a new subject
  echo("<tr><form method=post action=updsubject.php name=newsub>");
  echo("<td><center><input type=hidden name=mid value=\"\">");
  echo("<input type=text size=8 name=shortname value=\"\"></td>");
  echo("<td><center><input type=text size=20 name=fullname value=\"\"></td>");
  // Add the type
  echo("<td><center><select name=type><option value=normal selected>" . $dtext['Normal'] . "</option>");
  echo("<option value=sub>" . $dtext['Sub'] . "</option>");
  echo("<option value=meta>" . $dtext['Meta'] . "</option></select></td>");
  // Add the meta subject
  echo("<td><center><select name=meta_subject>");
  for($mc=0;$mc<=$meta_count;$mc++)
  { // add each meta subject!
    echo("<option value=" . $meta_subs['mid'][$mc].">" . $meta_subs['name'][$mc]. "</option>");
  }
  echo("</select></td>");
  // Add the ADD button
  //echo("<td><center><input type=submit value=" . $dtext['ADD_CAP'] . "></td></form>");
  echo("<td><center><img src='PNG/action_add.png' title='". $dtext['ADD_CAP']. "' onclick='document.newsub.submit();'></td></form>");
  // Here we don't have a delete button!
  echo("</tr>");
  
  // close the table
  echo("</table></html>");

?>
