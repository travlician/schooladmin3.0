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

  $mid = trim($HTTP_POST_VARS['mid']);

  if ($mid == "")
  {
    echo($dtext['missing_mid']);
    echo("<br><a href=mansubjects.php>" . $dtext['back_subman'] . "</a>");
    SA_closeDB();
    exit;
  }

  // First we must delete the related testresults and test definitions
  $sql_querytdid = "SELECT testdef.tdid FROM testdef,class WHERE class.mid='$mid' AND testdef.cid=class.cid";
  $sql_resulttdid = mysql_query($sql_querytdid,$userlink);
  //echo mysql_error($userlink);
  if (mysql_num_rows($sql_resulttdid)!=0)
  {
    for($r=0;$r<mysql_num_rows($sql_resulttdid);$r++)
    {
      $fieldvalu = mysql_result($sql_resulttdid,$r,'testdef.tdid');
      // Now we do the delete for the testresults
      $sql_queryrmt = "DELETE FROM testresult WHERE tdid='$fieldvalu'";
      mysql_query($sql_queryrmt,$userlink);
      // Now we delete the test definition
      $sql_queryrmt = "DELETE FROM testdef WHERE tdid='$fieldvalu'";
      mysql_query($sql_queryrmt,$userlink);
    } //for $r
  }//If numrows != 0
  mysql_free_result($sql_resulttdid);
  
  // Delete the related classes
  $sql_query = "DELETE FROM class WHERE mid=$mid";
  mysql_query($sql_query,$userlink);
  // And finally, delete the subject (and check!)
  $sql_query = "DELETE FROM subject WHERE mid=$mid;";
  $mysql_query = $sql_query;
  //echo $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);
  SA_closeDB();
  
  if($sql_result == 1)
  {	// operation succeeded, back to the manage subject page!
    header("Location: " . $livesite ."mansubjects.php");
    exit;
  }
  else
  {
    echo($dtext['op_fail']);
    echo("<br><a href=mansubjects.php>" . $dtext['back_subman'] . "</a>");
  }

?>


