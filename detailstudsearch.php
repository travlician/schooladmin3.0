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

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];

  $uid = intval($uid);

  // We get the data structure for the student details in an array.
  $sql_query = "SELECT * FROM student_details ORDER BY seq_no";
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
       $details_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $details_n = $nrows;


  // First part of the page
  echo("<html><head><title>" . $dtext['studsearch_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['studsearch_title'] . "</font><p>");
  echo '<a href="manstudetails.php">';
  echo ($dtext['back_studetsel']);
  echo '</a><br>';

  // All search criteria obtained from a big form
  echo("<form method=post action=studetailsearch.php>");
  echo("<table border=1 celpadding=2>");
  // check for each details
  for($det=1;$det<=$details_n;$det++)
  { // Action depends on entry in details table!
    // First of all: do we have access to the field!
    if(($details_array['raccess'][$det] != "N") && 
       ($details_array['raccess'][$det] != "C" || $LoginType == "C" || $LoginType== "A"))
    {
      if($details_array['table_name'][$det] == "*sid")
      { // Student ID, No search criterium!
        echo("<tr><td>");
        echo($details_array['label'][$det] . "</td><td>");
        echo("<input type=text name=*sid size=40 value=''></td></tr>");
      }
      else if ($details_array['table_name'][$det] == "*student.lastname")
      { // Last name, make the input field
        echo("<tr><td>");
        echo($details_array['label'][$det] . "</td><td>");
        echo("<input type=text name=*student.lastname size=60 value=''></td></tr>");
      }
      else if ($details_array['table_name'][$det] == "*student.firstname")
      {  // Firstname, make the input field
        echo("<tr><td>");
        echo($details_array['label'][$det] . "</td><td>");
        echo("<input type=text name=*student.firstname size=60 value=''></td></tr>");
      }
      else if ($details_array['table_name'][$det] == "*sgroup.groupname")
      { // Groupname, Don't use as search criterium, not required in this system
      }
      else if ($details_array['table_name'][$det] == "s_picture")
      { // Picture, too hard to search on pictures, don't use it
      }
      else if ($details_array['table_name'][$det] == "*gradestore.*")
      { // Too complex for now to search an a grade!
      }
      else if ($details_array['table_name'][$det] == "*absence.*")
      { // absence records, Too hard on search on these too!
      }
      else if ($details_array['type'][$det] == "text")
      { // admin configured texts,
        echo("<tr><td>");
        echo($details_array['label'][$det] . "</td><td>");
        // Just show a text field to enable entry of some data to search on.
        if($details_array['size'][$det] < 256)
          echo("<input type=text size=" . $details_array['size'][$det] . " name='" . $details_array['table_name'][$det] . "' value=''>");
        else
        {
          echo("<textarea rows=5 cols=60 name='" . $details_array['table_name'][$det] . "'>");
          echo("</textarea>");
        }
        echo("</td></tr>");
      }
      // Only pictures set up by admin definitions can follow, let's not check that!
    } // End if security checked OK
  }
  echo("</table>");
  // Add the submit button to perform the search
  echo("<br><input type=submit value='" . $dtext['Find_it'] . "'></form>");

  echo("</html>");
  SA_closeDB();

?>
