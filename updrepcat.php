<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2016-2016 Aim4me N.V.  (http://www.aim4me.info)        |
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

  $name = trim($HTTP_POST_VARS['name']);
  $rcid = trim($_POST['rcid']);
  $waccess = trim($_POST['waccess']);

  if ($rcid == "" && $name == "")
  {
    echo($dtext['missing_absreason']);
    echo("<br><a href=manrepcats.php>" . $dtext['back_abssetup'] . "</a>");
    SA_closeDB();
    exit;
  }
  if($rcid == "") 
    $sql_query = "INSERT INTO reportcats (name,waccess) VALUES('$name',\"". $waccess. "\")";
  else
    $sql_query = "UPDATE reportcats SET name='$name',waccess='$waccess' WHERE rcid=$rcid;";
  $mysql_query = $sql_query;
  //echo $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);
  SA_closeDB();
  
  if($sql_result == 1)
  {	// operation succeeded, back to the mangroups page!
    header("Location: " . $livesite ."manrepcats.php");
    exit;
  }
  else
  {
    echo("Operation failed! ". mysql_error($userlink));
    echo("<br><a href=manrepcats.php>" . $dtext['back_abssetup'] . "</a>");
  }   

?>