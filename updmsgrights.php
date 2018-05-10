<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 3.0                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2018 Aim4me N.V.  (http://www.aim4me.info)        |
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

  $orgdest = trim($HTTP_POST_VARS['orgdest']);
  $orgrole = trim($HTTP_POST_VARS['orgrole']);
  $dest = trim($HTTP_POST_VARS['dest']);
  $role = trim($HTTP_POST_VARS['role']);


	if($orgdest != "" && $orgrole != "")
		{
			$del_query = "DELETE FROM messagerights WHERE destination='$orgdest' AND role='$orgrole'";
			mysql_query($del_query,$userlink);
		}

  if($dest != "")
    if($role != "")
    {
			$sql_query = "INSERT INTO messagerights (destination,role) VALUES('$dest','$role')";
			$mysql_query = $sql_query;

			$sql_result = mysql_query($mysql_query,$userlink);
			SA_closeDB();
		
			if($sql_result != 1)
			{
				echo($dtext['op_fail']);
				echo("<br><a href=manmessages.php>" . $dtext['back_teach_page'] . "</a>");
			}
		}
	header("Location: " . $livesite ."manmessages.php");

?>


