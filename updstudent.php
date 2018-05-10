<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 1.0                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014 Aim4me N.V.  (http://www.aim4me.info)        |
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
  /* foreach($HTTP_POST_VARS AS $key => $val)
    echo("<BR>". $key. " : ". $val);
  exit; */
  
  $login_qualify = 'A';
  require_once("schooladminfunctions.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  if(isset($_SESSION['CurrentGroup']))
    $CurrentGroup = $_SESSION['CurrentGroup'];
  
  $uid = intval($uid);

  $sid = trim($HTTP_POST_VARS['sid']);
  if($altsids == 1)
    $altsid = trim($HTTP_POST_VARS['altsid']);
  if(isset($HTTP_POST_VARS['gid']))
    $gids = $HTTP_POST_VARS['gid'];
  $lastname = trim($HTTP_POST_VARS['lastname']);
  $firstname = trim($HTTP_POST_VARS['firstname']);
  $password = trim($HTTP_POST_VARS['password']);
  $ppassword = trim($HTTP_POST_VARS['ppassword']);
  if($encryptedpasswords == 1)
  {
    $password = MD5($password);
    $ppassword = MD5($ppassword);
  }

  if ($altsids == 1 && $altsid == "")
  {
    echo($dtext['missing_altsid']);
    echo("<br><a href=manstudents.php>" . $dtext['back_stuman'] . "</a>");
    SA_closeDB();
    exit;
  }
  if(isset($gids) && count($gids) == 0)
  {
    echo($dtext['missing_gid']);
    echo("<br><a href=manstudents.php>" . $dtext['back_stuman'] . "</a>");
    SA_closeDB();
    exit;
  }
  if ($firstname == "")
  {
    echo($dtext['missing_firstname']);
    echo("<br><a href=manstudents.php>" . $dtext['back_stuman'] . "</a>");
    SA_closeDB();
    exit;
  }
  if ($lastname == "")
  {
    echo($dtext['missing_lastname']);
    echo("<br><a href=manstudents.php>" . $dtext['back_stuman'] . "</a>");
    SA_closeDB();
    exit;
  }
 
  if($sid == "")
    if($altsids == 1) 
      $sql_query = "INSERT INTO student (lastname,firstname,password,ppassword,altsid) VALUES(\"". $lastname. "\", \"". $firstname. "\", '$password', '$ppassword', '$altsid')";
    else
      $sql_query = "INSERT INTO student (lastname,firstname,password,ppassword,altsid) VALUES(\"". $lastname. "\", \"". $firstname. "\", '$password', '$ppassword', NULL)";

  else
    if($altsids == 1) 
      $sql_query = "UPDATE student SET lastname=\"". $lastname. "\",firstname=\"". $firstname. "\",password='$password',ppassword='$ppassword',altsid='$altsid' WHERE sid=$sid;";
    else
      $sql_query = "UPDATE student SET lastname=\"". $lastname. "\",firstname=\"". $firstname. "\",password='$password',ppassword='$ppassword' WHERE sid=$sid;";

  $mysql_query = $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);
  echo(mysql_error());
  if($sid == "") // New student, so get its inserted sid and group
  {
    $sid = mysql_insert_id();
	//echo($gids);
	$mygroup = $gids;
	unset($gids);
	$gids[1] = $mygroup;
  }

  // Now update the group memberships
  mysql_query("DELETE FROM sgrouplink WHERE sid=$sid");
  echo(mysql_error());
  if(isset($gids))
    foreach($gids AS $newgid)
	{
      mysql_query("INSERT INTO sgrouplink (sid,gid) VALUES($sid,$newgid)");
      echo(mysql_error());
	}
  SA_closeDB();

  if($sql_result == 1)
  {	// operation succeeded, back to the manage students page!
    header("Location: " . $livesite ."manstudents.php");
    exit;
  }
  else
  {
    echo($dtext['op_fail']);
    echo("<br><a href=manstudents.php>" . $dtext['back_stuman'] . "</a>");
  }   

?>