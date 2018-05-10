<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2015 Aim4me N.V.  (http://www.aim4me.info)        |
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

  $login_qualify = 'S';
  require_once("schooladminfunctions.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  //$CurrentGroup = $_SESSION['CurrentGroup'];

  $uid = intval($uid);

  // Set the requested subject package
  mysql_query("DELETE FROM s_package WHERE sid=". $uid,$userlink);
  echo(mysql_error());
  mysql_query("INSERT INTO s_package (sid,packagename,extrasubject,extrasubject2,extrasubject3) VALUES(". $uid. ",'". $HTTP_POST_VARS['*package']. "',". $HTTP_POST_VARS['*extrasub']. ",". $HTTP_POST_VARS['*extrasub2']. ",". $HTTP_POST_VARS['*extrasub3']. ")",$userlink);
  echo(mysql_error());  
  
  header("Location: " . $livesite ."subsel.php");
?>
