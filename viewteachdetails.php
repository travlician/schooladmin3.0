<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014 Aim4me N.V.   (http://www.aim4me.info)       |
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

  $login_qualify = 'ACST';
  include ("schooladminfunctions.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];

  $tid = $HTTP_POST_VARS['tid'];
  
  $uid = intval($uid);

  // Check if an tid was given, if not, we end here
  if($tid == "")
  {
    echo($dtext['missing_params']);
    echo("<br>" . $dtext['press_back']);
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

  // get the details from the mentor groups
  $sql_query = "SELECT * FROM sgroup WHERE active=1 AND tid_mentor='$tid'";
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
       $groups_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $mentorGroups = $nrows;


  // First part of the page
  echo("<html><head><title>" . $dtext['vteachdet_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<a href=manteachdetails.php>" . $dtext['back_teach_page'] . "</a><br>");


  // check for each details
  for($det=1;$det<=$details_n;$det++)
  { // Action depends on entry in details table!
    // First of all: do we have access to the field!
    if(($details_array['raccess'][$det] != "N") && 
       ($details_array['raccess'][$det] != "C" || $LoginType == "C" || $LoginType== "A") && 
       ($details_array['raccess'][$det] != "M" || $LoginType == "C" || $loginType== "A" || intval($standard_array['tid_mentor'][1]) == $uid))
    {
      echo("<br>");
      if($details_array['label'][$det] != "")
        echo($details_array['label'][$det] . ": ");

      if($details_array['table_name'][$det] == "*tid")
      { // teacher ID
        echo($tid);
      }
      else if ($details_array['table_name'][$det] == "*teacher.lastname")
      { // Last name
        echo($standard_array['lastname'][1]);
      }
      else if ($details_array['table_name'][$det] == "*teacher.firstname")
      {  // Firstname
        echo($standard_array['firstname'][1]);
      }
      else if ($details_array['table_name'][$det] == "*sgroup.groupname")
      { // Mentor groups display
        if($mentorGroups == 0)
          echo($dtext['None']);
        else
        {
          for($g=1; $g<=$mentorGroups; $g++)
          {
            echo($groups_array['groupname'][$g]);
            if($g != $mentorGroups)
              echo(",");
          }
        }
      }
      else if ($details_array['table_name'][$det] == "*subject.fullname")
      {  // Create a table with all subjects and classes
         // First get the data from the database
         $sql_query = "SELECT * FROM sgroup,class INNER JOIN subject USING (mid) WHERE active=1 AND sgroup.gid=class.gid AND tid='$tid' ORDER BY fullname,sgroup.gid";
         $sql_result = mysql_query($sql_query,$userlink);
         //echo mysql_error($userlink);
         $nrows = 0;
         if (mysql_num_rows($sql_result)!=0)
         {
           $nfields = mysql_num_fields($sql_result);
           for($r=0;$r<mysql_num_rows($sql_result);$r++)
           {
             $nrows++;
             for ($i=0;$i<$nfields;$i++)
             {
               $fieldname = mysql_field_name($sql_result,$i);
               $fieldvalu = mysql_result($sql_result,$r,mysql_field_name($sql_result,$i));
               $classes_array[$fieldname][$nrows]=$fieldvalu;
             } // for $i
           } //for $r
           mysql_free_result($sql_result);
         }//If numrows != 0
         $classes_n = $nrows;
         // Now we got the data.
         if($classes_n > 0)
         { // Now we can display the table
           echo("<br><table border=1>");
           // Header row
           echo("<tr><td><center><b>" . $dtext['Subject'] . "</b></td><td><center><b>" . $dtext['Group_Cap'] . "</b></td></tr>");
           // A row for each subject / group combination
           for($c=1;$c<=$classes_n;$c++)
           {
             echo("<tr><td>" . $classes_array['fullname'][$c] . "</td><td>" . $classes_array['groupname'][$c] . "</td></tr>");
           }
           echo("</table>");
         }
         else
           echo($dtext['No_cls_assigned']);
      }
      else if ($details_array['table_name'][$det] == "t_picture")
      { // Picture
	    $params = explode("@",$details_array['params'][$det]);
        $sql_query = "SELECT filename FROM t_picture WHERE tid='$tid'";
        $sql_result = mysql_query($sql_query,$userlink);
        if($sql_result && mysql_num_rows($sql_result) > 0)
        {
          echo("<IMG SRC='" . $livepictures . @mysql_result($sql_result,0,'filename') . "'". (isset($params[1]) ? " style=\"position:absolute;". $params[1]. "\"" : ""). (isset($params[0]) ? " WIDTH=". $params[0]. "px" : ""). ">");
        }
        else
          echo($dtext['no_pic']);
      }
      else if ($details_array['type'][$det] == "text")
      { // admin configured texts
        // Get the details from the database
        $sql_query = "SELECT data FROM `" . $details_array['table_name'][$det] . "` WHERE tid='$tid'";
        $sql_result = mysql_query($sql_query,$userlink);
        if(!$sql_result || mysql_num_rows($sql_result) < 1)
          echo($dtext['No_data']);
        else
        {
          if($details_array['multi'][$det] == "Y")
          {
            for($n=0;$n<mysql_num_rows($sql_result);$n++)
            {
              echo(mysql_result($sql_result,$n,'data'));
              if((mysql_num_rows($sql_result) - $n) > 2)
                echo(", ");
              if((mysql_num_rows($sql_result) - $n) == 2)
                echo(" " . $dtext['and'] . " ");
              if((mysql_num_rows($sql_result) - $n) == 1)
                echo(".");

            }
          }
          else
            echo(mysql_result($sql_result,0,'data'));
        }
      }
      else
      { // admin configured pictures
	    $params = explode("@",$details_array['params'][$det]);
        $sql_query = "SELECT data FROM `" . $details_array['table_name'][$det] . "` WHERE tid='$tid'";
        $sql_result = mysql_query($sql_query,$userlink);
        if($sql_result && mysql_num_rows($sql_result) > 0)
        {
          echo("<IMG SRC='" . $livepictures . @mysql_result($sql_result,0,'data') . "'". (isset($params[1]) ? " style=\"position:absolute;". $params[1]. "\"" : ""). (isset($params[0]) ? " WIDTH=". $params[0]. "px" : ""). ">");
        }
        else
          echo($dtext['no_pic']);
      }
    } // End if security checked OK
  }
  echo '<BR><BR><a href="teacherpage.php">';
  echo $dtext['back_teach_page'];
  echo '</a>';
 
  echo("</html>");
  SA_closeDB();

?>
