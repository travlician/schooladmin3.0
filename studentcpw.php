<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011 Aim4me N.V.   (http://www.aim4me.info)       |
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

  // First we get the data from student in an array.
  $sql_query = "SELECT * FROM student LEFT JOIN sgrouplink USING(sid) WHERE student.sid='$sid'";
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
  $gid = $student_array['gid'][1];

  
  SA_closeDB();

  // First part of the page
  echo("<html><head><title>" . $dtext['cpw_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['cpw_4'] . " " . $student_array['firstname'][1] . " " . $student_array['lastname'][1] . "</font><p>");
  include("studentmenu.php");

  echo("<br><div align=left>"); 

  echo("<br>");

  // Now create a table with the form fields to change a password
?>
<form method=post action=studentcpwexecute.php>
<table border=0 cellpadding=5>
<tr><td><div align=right><?php echo($dtext['Cur_pw']); ?>:</div></td><td><input type=password name=oldpw size=6></td></tr>
<tr><td><div align=right><?php echo($dtext['New_pw']); ?>:</div></td><td><input type=password name=newpw size=6></td></tr>
<tr><td><div align=right><?php echo($dtext['New_pw']); ?>:</div></td><td><input type=password name=newpw2 size=6></td></tr>
</table>
<br>
<input type=submit value="<?php echo($dtext['cpw_submit']); ?>">
<br><br><?php echo($dtext['cpw_expl_1']); ?>
</html>
