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

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];

  $sid = $HTTP_POST_VARS['sid'];
  $transfiles = $HTTP_POST_FILES;
  
  $uid = intval($uid);

  /* reset($HTTP_POST_VARS);
  while(list($key,$val) = each($HTTP_POST_VARS))
    echo("$key,$val<br>"); */

  // Check if a sid was given, if not, we end here
  if($sid == "")
  {
    echo($dtext['missing_sid']);
    echo("<br><a href=manstudetails>" . $dtext['back_studetsel'] . "</a>");
    SA_closeDB();
    exit;
  }

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

  // get some core details about the student
  $sql_query = "SELECT * FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING (gid) WHERE active=1 AND student.sid='$sid'";
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

  // check for each details

  for($det=1;$det<=$details_n;$det++)
  { // Action depends on entry in details table!
    // First of all: do we have access to the field!
    if(($details_array['waccess'][$det] != "N") && 
       ($details_array['waccess'][$det] != "C" || $LoginType == "C" || $LoginType== "A") && 
       ($details_array['waccess'][$det] != "M" || $LoginType == "C" || $LoginType== "A" || intval($standard_array['tid_mentor'][1]) == $uid))
    {
      if($details_array['table_name'][$det] == "*sid")
      { // Student ID, do nothing here if not $altsids is 1!
        if($altsids == 1)
        {
          $altsid = $HTTP_POST_VARS['*sid'];
          if($altsid != "")
          {
            $update_query = "UPDATE student SET altsid='" . $altsid . "' WHERE sid='$sid'";
            mysql_query($update_query,$userlink);
          }  
        }
      }
      else if ($details_array['table_name'][$det] == "*student.lastname")
      { // Last name
        if(isset($HTTP_POST_VARS['*student.lastname']))
          $lastname = $HTTP_POST_VARS['*student.lastname'];
        else
          $lastname = $HTTP_POST_VARS['*student_lastname'];
        if($lastname != "")
        {
          $update_query = "UPDATE student SET lastname='" . $lastname . "' WHERE sid='$sid'";
          mysql_query($update_query,$userlink);
        }
      }

      else if ($details_array['table_name'][$det] == "*student.firstname")
      {  // Firstname
        if(isset($HTTP_POST_VARS['*student.firstname']))
          $firstname = $HTTP_POST_VARS['*student.firstname'];
        else
          $firstname = $HTTP_POST_VARS['*student_firstname'];
        if($firstname != "")
        {
          $update_query = "UPDATE student SET firstname='" . $firstname . "' WHERE sid='$sid'";
          mysql_query($update_query,$userlink);
        }
      }
      else if ($details_array['table_name'][$det] == "*sgroup.groupname")
      { // Groupname, definitively read-only!
      }
      else if ($details_array['table_name'][$det] == "s_picture")
      { // Picture
        if(isset($transfiles['s_picture']['tmp_name']) && $transfiles['s_picture']['tmp_name'] != "")
        {
          $file = $transfiles['s_picture']['tmp_name'];
          $newfilename = "s_picture" .$sid;
          // Copy the extension from the tmp_file!
          $extension = (strstr($transfiles['s_picture']['name'],'.')) ? @strstr($transfiles['s_picture']['name'],'.') : '.file';
          $newfilename .= $extension;
          // Put the new filename in the database
          $sql_query = "REPLACE s_picture VALUES (" . $sid . ",'" . $newfilename . "')";
          mysql_query($sql_query,$userlink);
          // Prepend the directory name as specified in the configuration.
          $newfilename = $picturespath . $newfilename;
          // Open the temporary file & newfile, copy this way
          if (!($fp = fopen($file,"rb")))
          {
            echo($dtext['err_openfile'] . " \"". $file . "\" " . $dtext['4read']);
            echo("<br><a href=manstudetails.php>" . $dtext['back_studetsel'] . "</a>");
            SA_closeDB();
            exit;
          }
          if (!($fpn = fopen($newfilename,"wb")))
          {
            echo($dtext['err_openwrite']);
            echo("<br><a href=manstudetails.php>" . $dtext['back_studetsel'] . "</a>");
            SA_closeDB();
            exit;
          }
          // files are opened, now we start the copy
          while ($lineoftext=fgets($fp,65536))
          {   
            fputs($fpn,$lineoftext);
          }
          fclose($fp);
          fclose($fpn);
        } // End if picture file uploaded. If not: No picture change, so do nothing here

      }
	  else if($details_array['table_name'][$det] == "*package")
	  { // replace students package data
	    mysql_query("DELETE FROM s_package WHERE sid=". $sid,$userlink);
		if($HTTP_POST_VARS['*package'] != "")
	      mysql_query("INSERT INTO s_package VALUES(". $sid. ",'". $HTTP_POST_VARS['*package']. "',". $HTTP_POST_VARS['*extrasub']. ")",$userlink);
	  }
      else if ($details_array['table_name'][$det] == "*gradestore.*")
      { // grades stored, READ ONLY!
      }
      else if ($details_array['table_name'][$det] == "*absence.*")
      { // absence records, READ ONLY
      }
      else if ($details_array['type'][$det] == "text" || $details_array['type'][$det] == "choice")
      { // admin configured texts
        $newdata = $HTTP_POST_VARS[$details_array['table_name'][$det]];
        // Get the details from the database
        if($details_array['multi'][$det] == "Y")
        {
          // First see if there are any entries (of the first 100!) to delete
          for($ii=0;$ii<100;$ii++)
          {
            $varchk = "D" . $ii . "_" . $details_array['table_name'][$det];
            if(isset($HTTP_POST_VARS[$varchk]))
            { // need to delete an entry
              $del_query = "DELETE FROM `" . $details_array['table_name'][$det] . "` WHERE sid='$sid' AND data='";
              $del_query .= $HTTP_POST_VARS[$varchk] . "'";
              mysql_query($del_query,$userlink);
            }
          }
          // New entry added if not empty!
          if($newdata != "")
          {
            $new_query = "INSERT INTO `" . $details_array['table_name'][$det] . "` VALUES ('$sid','$newdata')";
            mysql_query($new_query,$userlink);
          }
        }
        else
        { // Single entry, store it in the database
          $new_query = "REPLACE `" . $details_array['table_name'][$det] . "` VALUES ('$sid','$newdata')";
          mysql_query($new_query,$userlink);
        }
      }
      else
      { // admin configured pictures
        $tabname = $details_array['table_name'][$det];
        if(isset($transfiles[$tabname]['tmp_name']) && $transfiles[$tabname]['tmp_name'] != "")
        {
          $file = $transfiles[$tabname]['tmp_name'];
          $newfilename = $tabname .$sid;
          // Copy the extension from the tmp_file!
          $extension = (strstr($transfiles[$tabname]['name'],'.')) ? @strstr($transfiles[$tabname]['name'],'.') : '.file';
          $newfilename .= $extension;
          // Put the new filename in the database
          $sql_query = "REPLACE `" . $tabname . "` VALUES (" . $sid . ",'" . $newfilename . "')";
          mysql_query($sql_query,$userlink);
          // Prepend the directory name as specified in the configuration.
          $newfilename = $picturespath . $newfilename;
          // Open the temporary file & newfile, copy this way
          if (!($fp = fopen($file,"rb")))
          {
            echo($dtext['err_openfile'] . " \"". $file . "\" " . $dtext['4read']);
            echo("<br><a href=manstudetails.php>" . $dtext['back_studetsel'] . "</a>");
            SA_closeDB();
            exit;
          }
          if (!($fpn = fopen($newfilename,"wb")))
          {
            echo($dtext['err_openwrite']);
            echo("<br><a href=manstudetails.php>" . $dtext['back_studetsel'] . "</a>");
            SA_closeDB();
            exit;
          }
          // files are opened, now we start the copy
          while ($lineoftext=fgets($fp,65536))
          {   
            fputs($fpn,$lineoftext);
          }
          fclose($fp);
          fclose($fpn);
        } // End if picture file uploaded. If not: No picture change, so do nothing here
      }
    } // End if security checked OK
  }
  // Go back to the student details management page
  SA_closeDB();
  header("Location: " . $livesite ."manstudetails.php");

?>
