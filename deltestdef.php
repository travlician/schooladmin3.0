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

  $login_qualify = 'ACT';
  require_once("schooladminfunctions.php");
  require_once("schooladmingradecalc.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];
  
  $uid = intval($uid);

  $tdid = trim($HTTP_POST_VARS['tdid']);

  if ($tdid == "")
  {
    echo($dtext['missing_tdid']);
    echo("<br><a href=mantests.php>" . $dtext['back_testman'] . "</a>");
    SA_closeDB();
    exit;
  }

  // Prepare data to later recalc results
  $sql_query = "SELECT cid,period FROM testdef WHERE tdid='$tdid'";
  $sql_result = mysql_query($sql_query,$userlink);
  $cid = mysql_result($sql_result,0,'cid');
  $period = mysql_result($sql_result,0,'period');

  // Delete the related testresults
  $sql_query = "DELETE FROM testresult WHERE tdid=$tdid";
  mysql_query($sql_query,$userlink);
  // And finally, delete the teast defintion (and check!)
  $sql_query = "DELETE FROM testdef WHERE tdid=$tdid;";
  $mysql_query = $sql_query;
  //echo $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);

  // recalculate grades on card
  SA_calcGradeGroup($cid, $period);

  SA_closeDB();
  
  if($sql_result == 1)
  {	// operation succeeded, back to the manage test page!
    header("Location: " . $livesite ."mantests.php");
    exit;
  }
  else
  {
    echo($dtext['op_fail']);
    echo("<br><a href=mantests.php>" . $dtext['back_testman'] . "</a>");
  }

?>


