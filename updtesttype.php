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

  $type = trim($HTTP_POST_VARS['type']);
  $translation = trim($HTTP_POST_VARS['translation']);
  $new = trim($HTTP_POST_VARS['new']);


  if ($type == "")
  {
    echo($dtext['missing_params']);
    echo("<br><a href=mantesttyp.php>" . $dtext['back_tsttypman'] . "</a>");
    SA_closeDB();
    exit;
  }
 
  if ($translation == "")
  {
    echo($dtext['missing_params']);
    echo("<br><a href=mantesttyp.php>" . $dtext['back_tsttypman'] . "</a>");
    SA_closeDB();
    exit;
  }
 
  if($new == "no") 
    $sql_query = "UPDATE testtype SET translation='$translation' WHERE type='$type';";
  else
    $sql_query = "INSERT INTO testtype VALUES('$type', '$translation')";
  $mysql_query = $sql_query;
  //echo $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);
  SA_closeDB();
  
  if($sql_result == 1)
  {	// operation succeeded, back to the manage testtypes page!
    header("Location: " . $livesite ."mantesttyp.php");
    exit;
  }
  else
  {
    echo($dtext['op_fail']);
    echo("<br><a href=mantesttyp.php>" . $dtext['back_tsttypman'] . "</a>");
  }   

?>