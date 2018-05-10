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


  // Create the array with short names for the default language
  $table_name = "tt_" . $defaultlanguage;
  $sql_query = "SELECT short FROM " . $table_name . " ORDER BY short";
  $sql_result = mysql_query($sql_query,$userlink);
  //echo mysql_error($userlink);
  $nrows = 0;
  if (mysql_num_rows($sql_result)!=0)
  {
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
     $nrows++;
     $short_array[$nrows] = mysql_result($sql_result,$r,'short');
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $short_n = $nrows;

  // if no item is requested, we set the first item!
  if(!isset($HTTP_GET_VARS['item']))
    $item=1;
  else
    $item=$HTTP_GET_VARS['item'];

  // Get the full text for the current item in the default language
  $sql_query = "SELECT full FROM " .$table_name . " WHERE short='" . $short_array[$item] . "'";
  $sql_result = mysql_query($sql_query,$userlink);
  if($sql_result)
    $fulldefault = mysql_result($sql_result,0,'full');
  else
    $fulldefault="";

  // get the full test from the current item in the current language
  $table_name = "tt_" . $_SESSION['currentlanguage'];
  $sql_query = "SELECT full FROM " .$table_name . " WHERE short='" . $short_array[$item] . "'";
  $sql_result = mysql_query($sql_query,$userlink);
  if($sql_result && mysql_num_rows($sql_result) > 0)
    $fullcurrent = mysql_result($sql_result,0,'full');
  else
    $fullcurrent="";

  SA_closeDB();

  // First part of the page
  echo("<html><head><title>" . $dtext['transm_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['transm_title'] . "</font><p>");
  echo("<a href=admin.php>" . $dtext['back_admin'] . "</a>");

  echo("<br>");

  // Create the heading row for the table
  echo("<form method=post action='updtextdef.php'>");
  echo("<table border=1>");
  // Now comes the input field for the short value
  echo("<tr><td>" . $dtext['Short'] . " : <input name=short size=30 value='" . $short_array[$item] . "'</td></tr>");
  // Now the text as found in the default language
  echo("<tr><td>" . $fulldefault . "</td></tr>");

  //Now the text area for the current language and submit button
  echo("<tr><td><textarea name='full' rows=6 cols=80>" . $fullcurrent . "</textarea><br>");
  echo("<input type=hidden name=nextitem value='" . (($item == $short_n) ? 1 : $item+1) . "'>");
  echo("<input type=submit value=" . $dtext['Change'] . "></form></td></tr>");

  // Add a row to create a new language
  echo("<tr><td>" . $dtext['transm_addlang'] . "<form action=addlanguage.php method=post><input name=language size=40><input type=submit value=" . $dtext['ADD_CAP'] . "></form></td></tr>");

  // Now all the shorts 
  echo("<tr><td>");
  for($s=1;$s<=$short_n;$s++)
  {
    if($s != 1) echo(" ");
    echo("<a href=mantranslations.php?item=" . $s . ">" . $short_array[$s] . "</a>");
  }
  echo("</td></tr>");

  // close the table and page
  echo("</table></html>");

?>
