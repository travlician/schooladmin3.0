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

  $date = trim($HTTP_POST_VARS['date']);

  if ($date == "")
  {
    echo("You must supply a date");
    echo("<br><a href=manabsdetails.php>" . $dtext['back_abssetup'] . "</a>");
    SA_closeDB();
    exit;
  }

  // Now it's time to delete the enteries in the absence table from before the given date
  $sql_query = "DELETE FROM absence WHERE date<'" . $date . "'";
  $sql_result = mysql_query($sql_query,$userlink);
  if(!$sql_result)
  { // Unable to remove the entry table!
    echo($dtext['op_fail'] . " " . $dtext['Reason'] . ": " . @mysql_error($userlink));
    echo("<br><a href=manabsdetails.php>" . $dtext['back_abssetup'] . "</a>");
    SA_closeDB();
    exit;
  }
  
  // OK, we're ready to link back to the original page
  SA_closeDB();
  
  header("Location: " . $livesite ."manabsdetails.php");

?>