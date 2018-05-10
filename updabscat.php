<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2016 Aim4me N.V.  (http://www.aim4me.info)        |
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

  $name = trim($HTTP_POST_VARS['name']);
  $image = trim($HTTP_POST_VARS['image']);
  $acid = trim($_POST['acid']);
  $classuse = trim($_POST['classuse']);
  $countabs = trim($_POST['countabs']);
  $ongui = trim($_POST['ongui']);
  $waccess = trim($_POST['waccess']);
  if($classuse == '')
    $classuse = 0;
  if($countabs == '')
    $countabs = 1;

  if ($acid == "" && $name == "")
  {
    echo($dtext['missing_absreason']);
    echo("<br><a href=manabsdetails.php>" . $dtext['back_abssetup'] . "</a>");
    SA_closeDB();
    exit;
  }
  if($acid == "") 
    $sql_query = "INSERT INTO absencecats (name,image,classuse,countabs,ongui,waccess) VALUES('$name','$image',$classuse,$countabs,$ongui,\"". $waccess. "\")";
  else
    $sql_query = "UPDATE absencecats SET name='$name',image='$image',classuse=$classuse,countabs=$countabs,ongui=$ongui,waccess='$waccess' WHERE acid=$acid;";
  $mysql_query = $sql_query;
  //echo $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);
  SA_closeDB();
  
  if($sql_result == 1)
  {	// operation succeeded, back to the mangroups page!
    header("Location: " . $livesite ."manabsdetails.php");
    exit;
  }
  else
  {
    echo("Operation failed! ". mysql_error($userlink));
    echo("<br><a href=manabsdetails.php>" . $dtext['back_abssetup'] . "</a>");
  }   

?>