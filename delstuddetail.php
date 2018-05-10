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

  $table_name = trim($HTTP_POST_VARS['table_name']);

  if ($table_name == "")
  {
    echo($dtext['missing_params']);
    echo("<br><a href=manstuddetails.php>" . $dtext['back_studetman'] . "</a>");
    SA_closeDB();
    exit;
  }

  // now, it seems we got our params right, so we need to reshuffle the seq_no fields
  $sql_query = "SELECT seq_no FROM student_details WHERE table_name='" . $table_name . "'";
  $sql_result = mysql_query($sql_query,$userlink);
  if((!$sql_result) || (mysql_num_rows($sql_result) < 1))
  { // O O, no result while there should be a table!
    echo("Internal error: no table_name found with given table name and not new!");
    echo("<br><a href=manstuddetails.php>Back to the student details management page</a>");
    SA_closeDB();
    exit;
  }
  $old_seq_no = intval(mysql_result($sql_result,0,'seq_no'));
  // need to reshuffle seq_no!
  $sql_query = "SELECT table_name,seq_no FROM student_details WHERE seq_no>'" . $old_seq_no . "'";
  $sql_result = mysql_query($sql_query,$userlink);
  // Increase seq_no's in special range
  for($n=0;$n<mysql_num_rows($sql_result);$n++)
  {
    $upd_query = "UPDATE student_details SET seq_no='" . (@intval(mysql_result($sql_result,$n,'seq_no')) - 1) . "' WHERE table_name='" . @mysql_result($sql_result,$n,'table_name') . "'";
    mysql_query($upd_query,$userlink);
  }
  // Now it's time to delete the entery from the student_details table
  $sql_query = "DELETE FROM student_details WHERE table_name='" . $table_name . "'";
  $sql_result = mysql_query($sql_query,$userlink);
  if(!$sql_result)
  { // Unable to remove the entry table!
    echo($dtext['op_fail'] . " " . $dtext['Reason'] . ": " . @mysql_error($userlink));
    echo("<br><a href=manstuddetails.php>" . $dtext['back_studetman'] . "</a>");
    SA_closeDB();
    exit;
  }
  // Now we drop the table storing the details
  $sql_query = "DROP TABLE `" . $table_name . "`";
  mysql_query($sql_query, $userlink);
  
  // OK, we're ready to link back to the original page
  SA_closeDB();
  
  header("Location: " . $livesite ."manstuddetails.php");

?>