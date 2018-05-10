<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011 Aim4me N.V.  (http://www.aim4me.info)        |
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

  $cid = trim($HTTP_POST_VARS['cid']);
  $gid = trim($HTTP_POST_VARS['gid']);
  $mid = trim($HTTP_POST_VARS['mid']);
  $tid = trim($HTTP_POST_VARS['tid']);
  $masterlink = trim($HTTP_POST_VARS['masterlink']);
  $show_sequence = trim($HTTP_POST_VARS['show_sequence']);
  if($show_sequence == "")
    $show_sequence = "NULL";

  if ($gid == "")
  {
    echo($dtext['missing_gid']);
    echo("<br><a href=manclasses.php>" . $dtext['back_classman'] . "</a>");
    SA_closeDB();
    exit;
  }
  if ($mid == "")
  {
    echo($dtext['missing_mid']);
    echo("<br><a href=manclasses.php>" . $dtext['back_classman'] . "</a>");
    SA_closeDB();
    exit;
  }
  if ($tid == "")
  {
    echo($dtext['missing_tid']);
    echo("<br><a href=manclasses.php>" . $dtext['back_classman'] . "</a>");
    SA_closeDB();
    exit;
  }
  if ($masterlink == "")
  {
    echo($dtext['missing_special']);
    echo("<br><a href=manclasses.php>" . $dtext['back_classman'] . "</a>");
    SA_closeDB();
    exit;
  }

  if($cid == "") 
    $sql_query = "INSERT INTO class VALUES(NULL, '$gid', '$mid', '$tid', '$masterlink', ". $show_sequence. ")";
  else
    $sql_query = "UPDATE class SET gid='$gid',mid='$mid',tid='$tid',masterlink='$masterlink',show_sequence=". $show_sequence. " WHERE cid=$cid;";
  $mysql_query = $sql_query;
  //echo $sql_query;
  $sql_result = mysql_query($mysql_query,$userlink);
  SA_closeDB();
  
  if($sql_result == 1)
  {	// operation succeeded, back to the manclasses page!
    header("Location: " . $livesite ."manclasses.php");
    exit;
  }
  else
  {
    echo($dtext['op_fail']);
    echo("<br><a href=manclasses.php>" . $dtext['back_classman'] . "</a>");
  }   

?>


