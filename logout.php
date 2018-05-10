<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011  Aim4me N.V.  (http://www.aim4me.info)       |
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
// | Authors: Wilfred van Weert (travlician@bigfoot.com)                  |
// +----------------------------------------------------------------------+
//

session_start();
include ("schooladminfunctions.php");

//Allows for register_globals=off
$_SESSION['LoginType'] = "";
$_SESSION['uid'] = "";
if(isset($_SESSION['CurrentGroup']))
  $_SESSION['CurrentGroup'] = "";

//Allows for register_globals=on
$LoginType = "";
$CurrentGroup = "";
$uid="";


 echo("<body bgcolor=white><center><img src=schooladmin.jpg><br>");
 echo("<center>" . $dtext['logged_out'] . "</center>");
 echo("<br><center><a href=login.php>" . $dtext['back_login'] . "</a></center>");
 

 exit;
?>
