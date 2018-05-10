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

  $sid = trim($HTTP_POST_VARS['sid']);

  if ($sid == "")
  {
    echo($dtext['missing_sid']);
    echo("<br><a href=manstudents.php>" . $dtext['back_stuman'] . "</a>");
    SA_closeDB();
    exit;
  }

  // Delete the related testresults
  $sql_query = "DELETE FROM testresult WHERE sid=$sid";
  mysql_query($sql_query,$userlink);
  // And finally, delete the student (and check!)
  $sql_query = "DELETE FROM sgrouplink WHERE sid=$sid;";
  $mysql_query = $sql_query;
  $sql_result = mysql_query($mysql_query,$userlink);
  $sql_query = "DELETE FROM student WHERE sid=$sid;";
  $mysql_query = $sql_query;
  //echo $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);
  SA_closeDB();
  
  if($sql_result == 1)
  {	// operation succeeded, back to the manage subject page!
    header("Location: " . $livesite ."manstudents.php");
    exit;
  }
  else
  {
    echo($dtext['op_fail']);
    echo("<br><a href=manstudents.php>" . $dtext['back_stuman'] . "</a>");
  }

?>


