<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014 Aim4me N.V.   (http://www.aim4me.info)	      |
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
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();

  $login_qualify = 'S';
  include ("schooladminfunctions.php");
  require_once("student.php");
  require_once("StudentCalendar.php");
  
  // Link with database for inputlib (student object)
  inputclassbase::dbconnect($userlink);

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
    
  $uid = intval($uid);
  $sid = $uid;

  // First part of the page
  $me = new student($uid);
  echo("<html><head><title>" . $announcement . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['tpage_calendar'] . " " . $me->get_firstname() . " " . $me->get_lastname() . "</font><p>");
  include("studentmenu.php");
  
  // Show the student classbook
  $_SESSION['ClientTimeOffset'] = 0;
  $calendar = new StudentCalendar();
  echo("</center>");
  $calendar->show();
  echo("</html>");

?>
