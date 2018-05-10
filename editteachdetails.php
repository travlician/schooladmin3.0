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

  $login_qualify = 'A';
  include ("schooladminfunctions.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];

  $tid = $HTTP_POST_VARS['tid'];
  
  $uid = intval($uid);

  // Check if a tid was given, if not, we end here
  if($tid == "")
  {
    echo($dtext['missing_params']);
    echo("<br><a href=manteachdetails>" . $dtext['back_teachdetsel'] . "</a>");
    SA_closeDB();
    exit;
  }

  // We get the data structure for the teacher details in an array.
  $sql_query = "SELECT * FROM teacher_details ORDER BY seq_no";
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

  // get some core details about the teacher
  $sql_query = "SELECT * FROM teacher WHERE tid='$tid'";
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
       $standard_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0

  // First part of the page
  echo("<html><head><title>" . $dtext['editteachdet_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo '<a href="manteachdetails.php">';
  echo($dtext['back_teachdetsel']);
  echo '</a><br>';

  // All edits done in a big form
  echo("<form method=post action=updteachdetails.php ENCTYPE=\"multipart/form-data\">");
//  echo("<form method=post action=updstudetails.php>");
  echo("<input type=hidden name=tid value='$tid'>");
  // check for each details
  for($det=1;$det<=$details_n;$det++)
  { // Action depends on entry in details table!
    // First of all: do we have access to the field!
    if($LoginType == "A")
    {
      echo("<br>");
      if($details_array['label'][$det] != "")
        echo($details_array['label'][$det] . ": ");

      if($details_array['table_name'][$det] == "*tid")
      { // Student ID, definitely readonly!
        echo($tid);
      }
      else if ($details_array['table_name'][$det] == "*teacher.lastname")
      { // Last name
        echo("<input type=text name='*teacher.lastname' size=60 value=\"");
        echo($standard_array['lastname'][1]);
        echo("\">");
      }
      else if ($details_array['table_name'][$det] == "*teacher.firstname")
      {  // Firstname
        echo("<input type=text name='*teacher.firstname' size=60 value=\"");
        echo($standard_array['firstname'][1]);
        echo("\">");
      }
      else if ($details_array['table_name'][$det] == "*sgroup.groupname")
      { // Groupname, definitively read-only! Don't even display it
        echo($dtext['editteachdet_noneditable']);
      }
      else if ($details_array['table_name'][$det] == "*subject.fullname")
      { // classes & subjects, definitively read-only! Don't even display it
        echo($dtext['editteachdet_noneditable']);
      }
      else if ($details_array['type'][$det] == "text")
      { // admin configured texts
        // Get the details from the database
        $sql_query = "SELECT data FROM `" . $details_array['table_name'][$det] . "` WHERE tid='$tid'";
        $sql_result = mysql_query($sql_query,$userlink);
        if(!$sql_result)
          echo($dtext['No_data']);
        else
        {
          if($details_array['multi'][$det] == "Y")
          { // Multiple records now show in a table with delete checkbox
            echo("<table border=1 celpadding=2>");
            echo("<tr><td><center>" . $dtext['Data'] . "</td><td>" . $dtext['Delete'] . "</td></tr>");
            for($n=0;$n<mysql_num_rows($sql_result);$n++)
            {
              // Put the data
              echo("<tr><td>");
              echo(@mysql_result($sql_result,$n,'data'));
              echo("</td>");
              // Add the delete checkbox
              echo("<td><center><input type=checkbox name='D" . $n . "." . $details_array['table_name'][$det]);
              echo("' value='" . @mysql_result($sql_result,$n,'data') . "'></td></tr>");
            }
            // Add an empty row to add a new entry
            if($details_array['size'][$det] < 256)
            {
              echo("<tr><td><input type=text size=" . $details_array['size'][$det] . " name='" . $details_array['table_name'][$det] . "' value='");
              //echo(mysql_result($sql_result,$n,'data'));
              echo("'></td></tr>");
            }
            else
            {
              echo("<tr><td><textarea rows=5 cols=60 name='" . $details_array['table_name'][$det] . "'>");
              //echo(mysql_result($sql_result,$n,'data'));
              echo("</textarea></td></tr>");
            }
            // Close this table
            echo("</table>");
            
          }
          else
          {
            if($details_array['size'][$det] < 256)
            {
              echo("<input type=text size=" . $details_array['size'][$det] . " name='" . $details_array['table_name'][$det] . "' value='");
              if(mysql_num_rows($sql_result) > 0)
                echo(mysql_result($sql_result,0,'data'));
              echo("'>");
            }
            else
            {
              echo("<textarea rows=5 cols=60 name='" . $details_array['table_name'][$det] . "'>");
              if(mysql_num_rows($sql_result) > 0)
                echo(mysql_result($sql_result,0,'data'));
              echo("</textarea>");
            }
          }
        }
      }
      else
      { // admin configured pictures
        $sql_query = "SELECT data FROM `" . $details_array['table_name'][$det] . "` WHERE tid='$tid'";
        $sql_result = mysql_query($sql_query,$userlink);
        if($sql_result && mysql_num_rows($sql_result) > 0)
        {
          echo("<IMG SRC='" . $livepictures . @mysql_result($sql_result,0,'data') . "'>");
        }
        else
          echo($dtext['no_pic']);
        // Put the field to specify a file!
        echo("<input type=file name=\"" . $details_array['table_name'][$det] ."\">");
      }
    } // End if security checked OK
  }
  // Add the submit button to make all changes
  echo("<input type=submit value='" . $dtext['submit_chng'] . "'></form>");

  echo("</html>");
  SA_closeDB();


?>
