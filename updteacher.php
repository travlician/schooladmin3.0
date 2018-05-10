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

  $tid = trim($HTTP_POST_VARS['tid']);
  $lastname = trim($HTTP_POST_VARS['lastname']);
  $firstname = trim($HTTP_POST_VARS['firstname']);
  $password = trim($HTTP_POST_VARS['password']);
  $is_admin = trim($HTTP_POST_VARS['is_admin']);
  $is_counsel = trim($HTTP_POST_VARS['is_counsel']);
  $is_arman = trim($HTTP_POST_VARS['is_arman']);
  $is_office = trim($HTTP_POST_VARS['is_office']);
  $is_gone = trim($HTTP_POST_VARS['is_gone']);

  if ($lastname == "")
  {
    echo($dtext['missing_lastname']);
    echo("<br><a href=manteacher.php>" . $dtext['back_teachman'] . "</a>");
    SA_closeDB();
    exit;
  }
  if ($firstname == "")
  {
    echo($dtext['missing_firstname']);
    echo("<br><a href=manteacher.php>" . $dtext['back_teachman'] . "</a>");
    SA_closeDB();
    exit;
  }
//  if ($password == "")
//  {
//    echo($dtext['missing_pw']);
//    echo("<br><a href=manteacher.php>" . $dtext['back_teachman'] . "</a>");
//    SA_closeDB();
//    exit;
//  }

  if($encryptedpasswords == 1 && $password != "")
    $password = MD5($password);

  if($tid == "") 
    $sql_query = "INSERT INTO teacher (lastname,firstname,password,is_gone) VALUES(\"". $lastname. "\", \"". $firstname. "\", '$password', '$is_gone')";
  else
    if($password != "")
      $sql_query = "UPDATE teacher SET lastname=\"". $lastname. "\",firstname=\"". $firstname. "\",password='$password',is_gone='$is_gone' WHERE tid=$tid;";
    else
      $sql_query = "UPDATE teacher SET lastname=\"". $lastname. "\",firstname=\"". $firstname. "\",is_gone='$is_gone' WHERE tid=$tid;";
  $mysql_query = $sql_query;
  //echo $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);
  if($sql_result == 1)
  { // Now set the roles
    if($tid != "")
	  mysql_query("DELETE FROM teacherroles WHERE tid=". $tid,$userlink);
	else
	  $tid = mysql_insert_id($userlink);
	if($is_admin == "Y")
	  mysql_query("INSERT INTO teacherroles (tid,role,lastmodifiedby) VALUES($tid,1,". $_SESSION['uid']. ")", $userlink);
	if($is_counsel == "Y")
	  mysql_query("INSERT INTO teacherroles (tid,role,lastmodifiedby) VALUES($tid,2,". $_SESSION['uid']. ")", $userlink);
	if($is_arman == "Y")
	  mysql_query("INSERT INTO teacherroles (tid,role,lastmodifiedby) VALUES($tid,3,". $_SESSION['uid']. ")", $userlink);
	if($is_office == "Y")
	  mysql_query("INSERT INTO teacherroles (tid,role,lastmodifiedby) VALUES($tid,4,". $_SESSION['uid']. ")", $userlink);
  }
  SA_closeDB();
  
  if($sql_result == 1)
  {	// operation succeeded, back to the manteacher page!
    header("Location: " . $livesite ."manteacher.php");
    exit;
  }
  else
  {
    echo($dtext['op_fail']);
    echo("<br><a href=manteacher.php>" . $dtext['back_teachman'] . "</a>");
  }   

?>


