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
  inputclassbase::dbconnect($userlink);

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  
  $uid = intval($uid);
  $sid = $uid;
  $dtext = $_SESSION['dtext'];
  $stud = new student($sid);

  echo("<html><head><title>" . $dtext['shstudet_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['dets_4'] . " " . $stud->get_firstname() . " " . $stud->get_lastname() . "</font><p>");
  include("studentmenu.php");
  echo '</center><br>';
  // See if display of grades is disabled for a group with this student
  $gblock = inputclassbase::load_query("SELECT gradesblock FROM sgrouplink LEFT JOIN sgroup USING(gid) WHERE active=1 AND sid=". $stud->get_id(). " AND gradesblock=1");
  $gblock = (isset($gblock['gradesblock']));
	$todo = student::list_viewdetails();
	if(isset($todo))
	{
	  foreach($todo AS $tab => $lab)
	  {
	    if($tab == "*gradestore.*" && $gblock)
		  $data = $dtext['No_grades'];
		else
	      $data = $stud->get_student_detail($tab);
		if($data == NULL)
		  $data = $dtext['No_data'];
	    echo($lab. ":  ". $data. "<BR>");
	  }
	}


  echo("</html>");
  SA_closeDB();

?>
