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

  $login_qualify = 'A';
  include ("schooladminfunctions.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];

  $tid = $HTTP_POST_VARS['tid'];
  $transfiles = $HTTP_POST_FILES;
  
  $uid = intval($uid);

  /* reset($HTTP_POST_VARS);
  while(list($key,$val) = each($HTTP_POST_VARS))
    echo("$key,$val<br>"); */

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

  // check for each details

  for($det=1;$det<=$details_n;$det++)
  { // Action depends on entry in details table!
    if($LoginType== "A")
    {
      if($details_array['table_name'][$det] == "*tid")
      { // teacher ID, do nothing here!
      }
      else if ($details_array['table_name'][$det] == "*teacher.lastname")
      { // Last name
        if(isset($HTTP_POST_VARS['*teacher.lastname']))
          $lastname = $HTTP_POST_VARS['*teacher.lastname'];
        else
          $lastname = $HTTP_POST_VARS['*teacher_lastname'];
        if($lastname != "")
        {
          $update_query = "UPDATE teacher SET lastname='" . $lastname . "' WHERE tid='$tid'";
          mysql_query($update_query,$userlink);
        }
      }

      else if ($details_array['table_name'][$det] == "*teacher.firstname")
      {  // Firstname
        if(isset($HTTP_POST_VARS['*teacher.firstname']))
          $firstname = $HTTP_POST_VARS['*teacher.firstname'];
        else
          $firstname = $HTTP_POST_VARS['*teacher_firstname'];
        if($firstname != "")
        {
          $update_query = "UPDATE teacher SET firstname='" . $firstname . "' WHERE tid='$tid'";
          mysql_query($update_query,$userlink);
        }
      }
      else if ($details_array['table_name'][$det] == "*sgroup.groupname")
      { // Groupname, definitively read-only!
      }
      else if ($details_array['table_name'][$det] == "*subject.fullname")
      { // Subject & classes list, do nothing
      }
      else if ($details_array['table_name'][$det] == "t_picture")
      { // Picture
        if(isset($transfiles['t_picture']['tmp_name']) && $transfiles['t_picture']['tmp_name'] != "")
        {
          $file = $transfiles['t_picture']['tmp_name'];
          $newfilename = "t_picture" .$tid;
          // Copy the extension from the tmp_file!
          $extension = (strstr($transfiles['t_picture']['name'],'.')) ? @strstr($transfiles['t_picture']['name'],'.') : '.file';
          $newfilename .= $extension;
          // Put the new filename in the database
          $sql_query = "REPLACE t_picture VALUES (" . $tid . ",'" . $newfilename . "')";
          mysql_query($sql_query,$userlink);
          // Prepend the directory name as specified in the configuration.
          $newfilename = $picturespath . $newfilename;
          // Open the temporary file & newfile, copy this way
          if (!($fp = fopen($file,"rb")))
          {
            echo($dtext['err_openfile'] . " \"". $file . "\" " . $dtext['4read']);
            echo("<br><a href=manteachdetails.php>" . $dtext['back_teachdetsel'] . "</a>");
            SA_closeDB();
            exit;
          }
          if (!($fpn = fopen($newfilename,"wb")))
          {
            echo($dtext['err_openwrite']);
            echo("<br><a href=manteachdetails.php>" . $dtext['back_teachdetsel'] . "</a>");
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
      else if ($details_array['type'][$det] == "text")
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
              $del_query = "DELETE FROM `" . $details_array['table_name'][$det] . "` WHERE tid='$tid' AND data='";
              $del_query .= $HTTP_POST_VARS[$varchk] . "'";
              mysql_query($del_query,$userlink);
            }
          }
          // New entry added if not empty!
          if($newdata != "")
          {
            $new_query = "INSERT INTO `" . $details_array['table_name'][$det] . "` VALUES ('$tid','$newdata')";
            mysql_query($new_query,$userlink);
          }
        }
        else
        { // Single entry, store it in the database
          $new_query = "REPLACE `" . $details_array['table_name'][$det] . "` VALUES ('$tid','$newdata')";
          mysql_query($new_query,$userlink);
        }
      }
      else
      { // admin configured pictures
        $tabname = $details_array['table_name'][$det];
        if(isset($transfiles[$tabname]['tmp_name']) && $transfiles[$tabname]['tmp_name'] != "")
        {
          $file = $transfiles[$tabname]['tmp_name'];
          $newfilename = $tabname .$tid;
          // Copy the extension from the tmp_file!
          $extension = (strstr($transfiles[$tabname]['name'],'.')) ? @strstr($transfiles[$tabname]['name'],'.') : '.file';
          $newfilename .= $extension;
          // Put the new filename in the database
          $sql_query = "REPLACE `" . $tabname . "` VALUES (" . $tid . ",'" . $newfilename . "')";
          mysql_query($sql_query,$userlink);
          // Prepend the directory name as specified in the configuration.
          $newfilename = $picturespath . $newfilename;
          // Open the temporary file & newfile, copy this way
          if (!($fp = fopen($file,"rb")))
          {
            echo($dtext['err_openfile'] . " \"". $file . "\" " . $dtext['4read']);
            echo("<br><a href=manteachdetails.php>" . $dtext['back_teachdetsel'] . "</a>");
            SA_closeDB();
            exit;
          }
          if (!($fpn = fopen($newfilename,"wb")))
          {
            echo($dtext['err_openwrite']);
            echo("<br><a href=manteachdetails.php>" . $dtext['back_teachdetsel'] . "</a>");
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
  // Go back to the teacher details management page
  SA_closeDB();
  header("Location: " . $livesite ."manteachdetails.php");

?>
