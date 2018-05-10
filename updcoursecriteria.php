<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2016 Aim4me N.V.  (http://www.aim4me.info)        |
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
// | Authors: Wilfred van Weert - travlcian@bigfoot.com                   |
// +----------------------------------------------------------------------+
//
  session_start();

  $login_qualify = 'A';
  require_once("schooladminfunctions.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];
  
  $uid = intval($uid);

  $new = trim($HTTP_POST_VARS['new']);
  $masterlink = trim($HTTP_POST_VARS['masterlink']);
  $digitsafterdotfinal = trim($HTTP_POST_VARS['digitsafterdotfinal']);
  $digitsafterdotperiod = trim($HTTP_POST_VARS['digitsafterdotperiod']);
  $minimumpass = trim($HTTP_POST_VARS['minimumpass']);
  $maxfails = trim($HTTP_POST_VARS['maxfails']);
  $minpasspointbalance = trim($HTTP_POST_VARS['minpasspointbalance']);

  // Format values that can be NULL!
  if($minimumpass == "")
    $minimumpass = "NULL";
  else
    $minimumpass = "'" . $minimumpass."'";
  if($maxfails == "")
    $maxfails = "NULL";
  else
    $maxfails = "'" . $maxfails."'";
  if($minpasspointbalance == "")
    $minpasspointbalance = "NULL";
  else
    $minpasspointbalance = "'" . $minpasspointbalance."'";

  // Check mandatory params
  if ($masterlink == "")
  {
    echo($dtext['missing_special']);
    echo("<br><a href=mancoursecriteria.php>" . $dtext['coursecritman'] . "</a>");
    SA_closeDB();
    exit;
  }

  if($new != "no") 
    $sql_query = "INSERT INTO coursepasscriteria VALUES('$digitsafterdotfinal',$minimumpass,$maxfails,$minpasspointbalance,'$masterlink','$digitsafterdotperiod')";
  else
    $sql_query = "UPDATE coursepasscriteria SET digitsafterdotfinal='$digitsafterdotfinal',digitsafterdotperiod='$digitsafterdotperiod',minimumpass=$minimumpass,maxfails=$maxfails,minpasspointbalance=$minpasspointbalance WHERE masterlink='$masterlink';";
  $mysql_query = $sql_query;
  //echo $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);
	echo(mysql_error($userlink));
  SA_closeDB();
  
  if($sql_result == 1)
  {	// operation succeeded, back to the mangroups page!
    header("Location: " . $livesite ."mancoursecriteria.php");
    exit;
  }
  else
  {
    echo($dtext['op_fail']);
    echo("<br><a href=mancoursecriteria.php>" . $dtext['coursecritman'] . "</a>");
  }   

?>


