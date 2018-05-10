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

  if ($cid == "")
  {
    echo($dtext['missing_cid']);
    echo("<br><a href=manclasses.php>" . $dtext['back_classman'] . "</a>");
    exit;
  }

  // First we must delete the related testresults and test definitions
  $sql_querytdid = "SELECT tdid FROM testdef WHERE cid='$cid'";
  $sql_resulttdid = mysql_query($sql_querytdid,$userlink);
  //echo mysql_error($userlink);
  if (mysql_num_rows($sql_resulttdid)!=0)
  {
    for($r=0;$r<mysql_num_rows($sql_resulttdid);$r++)
    {
      $fieldvalu = mysql_result($sql_resulttdid,$r,'tdid');
      // Now we do the delete for the testresults
      $sql_queryrmt = "DELETE FROM testresult WHERE tdid='$fieldvalu'";
      mysql_query($sql_queryrmt,$userlink);
      // Now we delete the test definition
      $sql_queryrmt = "DELETE FROM testdef WHERE tdid='$fieldvalu'";
      mysql_query($sql_queryrmt,$userlink);
    } //for $r
  }//If numrows != 0
  mysql_free_result($sql_resulttdid);
  
  // And finally, delete the class (and check!)
  $sql_query = "DELETE FROM class WHERE cid=$cid";
  $mysql_query = $sql_query;
  //echo $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);
  SA_closeDB();
  
  if($sql_result == 1)
  {	// operation succeeded, back to the manclass page!
    header("Location: " . $livesite ."manclasses.php");
    exit;
  }
  else
  {
    echo("Operation failed!");
    echo("<br><a href=manclasses.php>" . $dtext['back_classman'] . "</a>");
  }
  exit;   

?>


