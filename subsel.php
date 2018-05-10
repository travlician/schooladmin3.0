<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2015 Aim4me N.V.   (http://www.aim4me.info)	      |
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
  echo("<html><head><title>" . $dtext['Subpack'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['Subpack'] . " " . $standard_array['firstname'][1] . " " . $standard_array['lastname'][1] . "</font><p>");
  include("studentmenu.php");
  echo '<br><div align=left>';
  echo("<FORM METHOD=POST ACTION=updsubsel.php>");


	    $packages = SA_loadquery("SELECT * FROM subjectpackage GROUP BY packagename ORDER BY packagename");
		$studpackage = SA_loadquery("SELECT * FROM s_package WHERE sid='$sid'");
		$subjects = SA_loadquery("SELECT mid,shortname FROM subjectpackage LEFT JOIN subject USING(mid) GROUP BY mid ORDER BY shortname");
		if(!isset($packages))
          echo($dtext['No_data']);
        else
        {
		  echo($dtext['Subpack']. ": <SELECT name=\"*package\" id=\"*package\">");
		  foreach($packages['packagename'] AS $pname)
		  {
		    echo("<OPTION VALUE='". $pname. "'");
			if(isset($studpackage['packagename'][1]) && $studpackage['packagename'][1] == $pname)
			  echo(" selected");
			echo(">");

		    if(isset($pname))
		    {
		      echo($pname. " (");
		      $package = SA_loadquery("SELECT * FROM subjectpackage LEFT JOIN subject USING(mid) WHERE packagename='". $pname. "' ORDER BY shortname");
		      $firstfield=1;
		      foreach($package['shortname'] AS $sname)
		      {
			    if($firstfield != 1)
			      echo(",");
		        echo($sname);
			    $firstfield = 0;
		      }
		      echo(")");
			}
            echo("</OPTION>");


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

		  echo(" ". $dtext['extra_subject2']. " : <SELECT name=\"*extrasub2\" id=\"*extrasub2\"><OPTION value=0> </OPTION>");
		  foreach($subjects['shortname'] AS $six => $sname)
		  {
		    echo("<OPTION VALUE='". $subjects['mid'][$six]. "'");
			if(isset($studpackage['extrasubject2'][1]) && $studpackage['extrasubject2'][1] == $subjects['mid'][$six])
			  echo(" selected");
			echo(">". $sname. "</OPTION>");
	      }
		  echo("</SELECT>");		  

		  echo(" ". $dtext['extra_subject3']. " : <SELECT name=\"*extrasub3\" id=\"*extrasub3\"><OPTION value=0> </OPTION>");
		  foreach($subjects['shortname'] AS $six => $sname)
		  {
		    echo("<OPTION VALUE='". $subjects['mid'][$six]. "'");
			if(isset($studpackage['extrasubject3'][1]) && $studpackage['extrasubject3'][1] == $subjects['mid'][$six])
			  echo(" selected");
			echo(">". $sname. "</OPTION>");
	      }
		  echo("</SELECT>");		  


			}
  echo("<INPUT TYPE=SUBMIT VALUE=\"". $dtext['COMM_CHNG_CAP']. "\"></FORM>");
  echo("</html>");
  SA_closeDB();

?>
