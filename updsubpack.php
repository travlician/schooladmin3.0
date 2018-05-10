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
  require_once("schooladmingradecalc.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];

  $uid = intval($uid);
  
  /*
  // test: dump posted values
  foreach($HTTP_POST_VARS AS $name => $value)
    echo("<BR>". $name. " = ". $value);
  exit;
  */

  if ($HTTP_POST_VARS['pname'] == "")
  {
    echo($dtext['missing_params']);
    echo("<br><a href=mansubjectpacks.php>" . $dtext['subpack_title'] . "</a>");
    SA_closeDB();
    exit;
  }
  
  if(isset($HTTP_POST_VARS['opname']))
  {
    if($HTTP_POST_VARS['pname'] != $HTTP_POST_VARS['opname'])
	{ // Need to update the name for the package!
	  mysql_query("UPDATE subjectpackage SET packagename='". $HTTP_POST_VARS['pname']. "' WHERE packagename='". $HTTP_POST_VARS['opname']. "'",$userlink);
	}
  }
 
  // Now we need to delete all defined subjects and insert the new ones.
  mysql_query("DELETE FROM subjectpackage WHERE packagename='". $HTTP_POST_VARS['pname']. "'",$userlink);
  
  // Now the fun part, inserting all the subjects
  foreach($HTTP_POST_VARS AS $pdat => $dummy)
  {
    if($pdat > 0)
	  mysql_query("INSERT INTO subjectpackage (packagename,mid) VALUES('" .$HTTP_POST_VARS['pname']. "',". $pdat. ")",$userlink);
  }

  SA_closeDB();
  
  header("Location: " . $livesite ."mansubjectpacks.php");
?>