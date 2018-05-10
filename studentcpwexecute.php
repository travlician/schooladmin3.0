<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011 Aim4me N.V.   (http://www.aim4me.info)	      |
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

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
    
  $uid = intval($uid);
  $sid = $uid;

  if(isset($HTTP_POST_VARS['oldpw']))
    $oldpw = $HTTP_POST_VARS['oldpw'];
  if(isset($HTTP_POST_VARS['newpw']))
    $newpw = $HTTP_POST_VARS['newpw'];
  if(isset($HTTP_POST_VARS['newpw2']))
    $newpw2 = $HTTP_POST_VARS['newpw2'];

  if($encryptedpasswords == 1)
    $oldpw = MD5($oldpw);

  // First we get the data from student in an array.
  $sql_query = "SELECT * FROM student WHERE student.sid='$sid'";
  $mysql_query = $sql_query;
  //echo $sql_query;
  $sql_result = mysql_query($mysql_query,$userlink);
  //echo mysql_error($userlink);
  $nrows = 0;
  if (mysql_num_rows($sql_result)!=0)
  {
    $nfields = mysql_num_fields($sql_result);
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
     $nrows++;
     for ($i=0;$i<$nfields;$i++){
       $fieldname = mysql_field_name($sql_result,$i);
       $fieldvalu = mysql_result($sql_result,$r,mysql_field_name($sql_result,$i));
       $student_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $row_n = $nrows;
  // set the group id for smarter queries following

  // Check if the current password matches the database version
  if(isset($oldpw) && $oldpw == $student_array['password'][1])
  {
    if(isset($newpw) && isset($newpw2) && $newpw == $newpw2)
    {
      // Now set the new password for the user
      $sql_query = "UPDATE student SET password='" .$newpw. "' WHERE sid='$sid'";
      if(mysql_query($sql_query,$userlink) != 1)
        $errmessage=$dtext['cpw_err_db'] . " " . mysql_error($userlink);
    }
    else
      $errmessage=$dtext['cpw_err_pwmis'] . " (" . $newpw . "," . $newpw2 . ")";
  }
  else if(isset($oldpw) && $oldpw == $student_array['ppassword'][1])
  {
    if(isset($newpw) && isset($newpw2) && $newpw == $newpw2)
    {
      // Now set the new password for the user
      $sql_query = "UPDATE student SET ppassword='" .$newpw. "' WHERE sid='$sid'";
      if(mysql_query($sql_query,$userlink) != 1)
        $errmessage=$dtext['cpw_err_db'] . " " . mysql_error($userlink);
    }
    else
      $errmessage=$dtext['cpw_err_pwmis'] . " (" . $newpw . "," . $newpw2 . ")";
  }
  else
    $errmessage=$dtext['cpw_err_invpw'];

  SA_closeDB();

  // First part of the page
  echo("<html><head><title>" . $dtext['cpw_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['cpw_4'] . " " . $student_array['firstname'][1] . " " . $student_array['lastname'][1] . "</font><p>");
  include("studentmenu.php");

  echo("<br><div align=left>"); 

  echo("<br>");
  echo("<font size=+1>");

  if(isset($errmessage))
    echo($dtext['cpw_err_lead'] . " " . $errmessage);
  else
    echo($dtext['cpw_expl_2']);

  echo("</font>");
  echo("</html>");
?>
