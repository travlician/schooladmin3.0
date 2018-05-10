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

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  $CurrentGroup = $_SESSION['CurrentGroup'];

  $sid = $HTTP_POST_VARS['sid'];
  
  $uid = intval($uid);

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

  // First part of the page
  echo("<html><head><title>" . $dtext['editstudet_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo '<a href="manstudetails.php">';
  echo ($dtext['back_studetsel']);
  echo '</a><br>';

  // All edits done in a big form
  echo("<form method=post action=updstudetails.php ENCTYPE=\"multipart/form-data\">");
//  echo("<form method=post action=updstudetails.php>");
  echo("<input type=hidden name=sid value='$sid'>");
  // check for each details
  for($det=1;$det<=$details_n;$det++)
  { // Action depends on entry in details table!
    // First of all: do we have access to the field!
    if(($details_array['waccess'][$det] != "N") && 
       ($details_array['waccess'][$det] != "C" || $LoginType == "C" || $LoginType== "A") && 
       ($details_array['waccess'][$det] != "M" || $LoginType == "C" || $LoginType== "A" || intval($standard_array['tid_mentor'][1]) == $uid))
    {
      echo("<br>");
      if($details_array['label'][$det] != "")
        echo($details_array['label'][$det] . ": ");

      if($details_array['table_name'][$det] == "*sid")
      { // Student ID, definitely readonly if $altsids is not set to 1!
        if($altsids != 1)
          echo($sid);
        else
        {
          echo("<input type=text name='*sid' size=16 value='");
          echo($standard_array['altsid'][1]);
          echo("'>");
        }
      }
      else if ($details_array['table_name'][$det] == "*student.lastname")
      { // Last name
        echo("<input type=text name='*student.lastname' size=60 value='");
        echo($standard_array['lastname'][1]);
        echo("'>");
      }
      else if ($details_array['table_name'][$det] == "*student.firstname")
      {  // Firstname
        echo("<input type=text name='*student.firstname' size=60 value='");
        echo($standard_array['firstname'][1]);
        echo("'>");
      }
      else if ($details_array['table_name'][$det] == "*sgroup.groupname")
      { // Groupname, definitively read-only if $altsids is off!
        if($altsids != 1)
          echo($standard_array['groupname'][1]);
        else
        {
        }
      }
      else if ($details_array['table_name'][$det] == "s_picture")
      { // Picture
        $sql_query = "SELECT filename FROM s_picture WHERE sid='$sid'";
        $sql_result = mysql_query($sql_query,$userlink);
        if($sql_result && mysql_num_rows($sql_result) > 0)
        {
          echo("<IMG SRC='" . $livepictures . @mysql_result($sql_result,0,'filename') . "'>");
        }
        else
          echo($dtext['no_pic']);
        // Put the field to specify a file!
        echo("<input type=file name=\"s_picture\">");
      }
      else if ($details_array['table_name'][$det] == "*gradestore.*")
      { // grades stored, READ ONLY!
        // First get the year, we'll do a new query fro each year     
        $sql_query = "SELECT * FROM gradestore WHERE sid='$sid' GROUP BY year DESC";
        $sql_result = mysql_query($sql_query,$userlink);
        if(!$sql_result)
          echo("<br>" . @mysql_error($userlink) . "<br>");
        if($sql_result && mysql_num_rows($sql_result) > 0)
        {
          for($y=0;$y<mysql_num_rows($sql_result);$y++)
          { // create a grade table for 1 year
            $year = mysql_result($sql_result,$y,'year');
            echo("<br>" . $dtext['Grades4'] . " " . $year . " :");
            // Now we do a query to get all results for a year, and store them in a array
            $sql_query="SELECT DISTINCT * FROM gradestore INNER JOIN subject USING (mid) WHERE sid='$sid' AND year='$year' ORDER BY subject.mid,period";
            $grades_result = mysql_query($sql_query,$userlink);
            if(!$grades_result)
              echo("<br>" . @mysql_error($userlink) . "<br>");
            $periodhi = -1;
            $periodlo = 1000;
            for($g=0;$g<mysql_num_rows($grades_result);$g++)
            {
              $grade_array['subject'][$g] = @mysql_result($grades_result,$g,'fullname');
              $grade_array['period'][$g] = @mysql_result($grades_result,$g,'period');
              $grade_array['result'][$g] = @mysql_result($grades_result,$g,'result');
              if(intval($grade_array['period'][$g]) > $periodhi)
                $periodhi = intval($grade_array['period'][$g]);
              if(intval($grade_array['period'][$g]) < $periodlo)
                $periodlo = intval($grade_array['period'][$g]);
            }
            // And now, we create a nice header for the table:
            echo("<table border=1 celpadding=2>");
            echo("<tr><td><center>" . $dtext['Subject'] . "</td>");
            for($c=$periodlo; $c<=$periodhi; $c++)
            {
              if($c == 0)
                echo("<td><center>" . $dtext['Final'] . "</td>");
              else
                echo("<td><center>" . $dtext['Period'] . " " . $c . "</td>");
            }
            echo("<tr>");
            // Now we need to put the results in the table!
            $perpos = $periodlo;
            for($g=0;$g<mysql_num_rows($grades_result);$g++)
            {
              // See if we need to start a new row...
              if($g == 0 || $grade_array['subject'][$g] != $grade_array['subject'][$g-1])
              { // new row!
                if($perpos != $periodlo)
                  echo("</tr><tr>");	// Must close previous row and start a new one
                else
                  echo("<tr>");		// Open new row (the first one)
                $perpos = $periodlo;
                // Put in the subject!
                echo("<td>" . $grade_array['subject'][$g] . "</td>");
              }
              // fill with '-' signs in periods which are not filled.
              for($p=$perpos;$p<intval($grade_array['period'][$g]);$p++)
              {
                $perpos++;
                echo("<td><center>-</td>");
              }
              // now we can put in the result!
              $perpos++;
              echo("<td><center>" . $grade_array['result'][$g] . "</td>");
            }
            echo("</table>");
          } // end year
        } // end if years in grades available
        else
        {
          echo($dtext['No_grades']);
        }
      }
      else if ($details_array['table_name'][$det] == "*absence.*")
      { // absence records, READ ONLY
        echo($dtext['Use_abs_man_tools'] . "<br>");
        $sql_query = "SELECT * FROM absence INNER JOIN absencereasons USING (aid) WHERE sid='$sid' ORDER BY date DESC,time";
        $sql_result = mysql_query($sql_query, $userlink);
        if(!$sql_result || (mysql_num_rows($sql_result) < 1))
          echo($dtext['No_abs_recs']);
        else
        { // absence records found, create the table
          echo("<table border=1 celpadding=2>");
          // Add the heading
          echo("<tr><td><center>" . $dtext['Date'] . "</td><td><center>" . $dtext['Time'] . "</td>");
          echo("<td><center>" . $dtext['Reason'] . "</td><td><center>" . $dtext['Authorization'] . "</td></tr>");
          for($ar=0;$ar<mysql_num_rows($sql_result);$ar++)
          { // Add the details for each row.
            echo("<tr><td><center>" . @mysql_result($sql_result, $ar, 'date') . "</td>");
            echo("<td><center>" . @mysql_result($sql_result, $ar, 'time') . "</td>");
            echo("<td>" . @mysql_result($sql_result, $ar, 'description') . "</td>");
            echo("<td><center>" . @mysql_result($sql_result, $ar, 'authorization') . "</td></tr>");
          }
          echo("</table>");
        }
      }
	  else if ($details_array['table_name'][$det] == "*package")
	  {
	    $packages = SA_loadquery("SELECT * FROM subjectpackage GROUP BY packagename ORDER BY packagename");
			$studpackage = SA_loadquery("SELECT * FROM s_package WHERE sid='$sid'");
			$subjects = SA_loadquery("SELECT mid,shortname FROM subject ORDER BY shortname");
			if(!isset($packages))
				echo($dtext['No_data']);
			else
			{
				echo(" <SELECT name=\"*package\" id=\"*package\"><OPTION value=\"\"> </OPTION>");
				foreach($packages['packagename'] AS $pname)
				{
					echo("<OPTION VALUE='". $pname. "'");
					if(isset($studpackage['packagename'][1]) && $studpackage['packagename'][1] == $pname)
						echo(" selected");
					echo(">". $pname. "</OPTION>");
				}
				echo("</SELECT> ". $dtext['extra_subject1']. " : <SELECT name=\"*extrasub\" id=\"*extrasub\"><OPTION value=0> </OPTION>");
				foreach($subjects['shortname'] AS $six => $sname)
				{
					echo("<OPTION VALUE='". $subjects['mid'][$six]. "'");
					if(isset($studpackage['extrasubject'][1]) && $studpackage['extrasubject'][1] == $subjects['mid'][$six])
						echo(" selected");
					echo(">". $sname. "</OPTION>");
				}
				echo("</SELECT>");		  
			}
	  }
      else if ($details_array['type'][$det] == "text")
      { // admin configured texts
        // Get the details from the database
        $sql_query = "SELECT data FROM `" . $details_array['table_name'][$det] . "` WHERE sid='$sid'";
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
      else if ($details_array['type'][$det] == "choice")
      { // admin configured choices
	    $choices = explode(",",$details_array['params'][$det]);
        // Get the details from the database
        $sql_query = "SELECT data FROM `" . $details_array['table_name'][$det] . "` WHERE sid='$sid'";
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
			echo("<tr><td><SELECT name=". $details_array['table_name'][$det]. "><option value=''></option>");
		    foreach($choices AS $xchoice)
			{
			  echo("<option value=\"". $xchoice. "\"");
			  echo(">". $xchoice. "</option>");
			}
			echo("</SELECT></td><td>&nbsp</td></tr>");
            // Close this table
            echo("</table>");
            
          }
          else
          { // Single record
            if(mysql_num_rows($sql_result) > 0)
              $fdata = mysql_result($sql_result,0,'data');
			else
			  $fdata = "";
			echo("<SELECT name=". $details_array['table_name'][$det]. ">");
		    foreach($choices AS $xchoice)
			{
			  echo("<option value=\"". $xchoice. "\"");
			  if($xchoice == $fdata)
			    echo(" selected");
			  echo(">". $xchoice. "</option>");
			}
			echo("</SELECT>");
          }
        }
      }
	  
      else
      { // admin configured pictures
        $sql_query = "SELECT data FROM `" . $details_array['table_name'][$det] . "` WHERE sid='$sid'";
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
  echo("<br><input type=submit value='" . $dtext['submit_chng'] . "'></form>");

  echo("</html>");
  SA_closeDB();


?>
