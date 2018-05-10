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
	require_once("message.php");
  require_once("inputlib/inputclassbase.php");
  inputclassbase::dblogon($databaseserver,$datausername,$datapassword,$databasename);

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
    
  $uid = intval($uid);
  $sid = $uid;
  // First we get the data from student in an array.
  $sql_query = "SELECT * FROM student LEFT JOIN sgrouplink USING(sid) WHERE student.sid='$sid'";
  $mysql_query = $sql_query;
  //echo $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);
  echo mysql_error($userlink);
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

  // Get the list of periods with their details
  $sql_query = "SELECT * FROM period";
  $mysql_query = $sql_query;
  //echo $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);
  echo mysql_error($userlink);
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
       $period_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $periods = $nrows;
  // Now add an extra period for final results
  $period_array['id'][0] = '0';
  $period_array['year'][0] = $period_array['year'][$periods];
  // Depending on the states of the periods we set the state of the final period.
  $all_final = 'Y';
  $any_open = 'N';
  $any_closed = 'N';
  for($p=1;$p<= $periods;$p++)
  {
    if($period_array['status'][$p] == 'open')
      $any_open = 'Y';
    if($period_array['status'][$p] != 'final')
      $all_final = 'N';
    if($period_array['status'][$p] == 'closed')
      $any_closed = 'Y';
  }
  
  // See if weight factors for periods other then 0 are present, if not: supress final period
  $finalshow = SA_loadquery("SELECT * FROM finalcalc");

  // Get the list of applicable subjects with their details
  $sql_query = "SELECT * FROM class left join subject using (mid) left join sgrouplink using(gid) where sid='$uid' AND show_sequence IS NOT NULL GROUP BY subject.mid ORDER BY show_sequence";
  $mysql_query = $sql_query;
  //echo $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);
  echo mysql_error($userlink);
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
       $subject_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $subjects = $nrows;

  // Get the list of grades for normal periods
  $sql_query = "SELECT * FROM period,student inner join gradestore using (sid) where period=id AND gradestore.year=period.year AND student.sid='$sid'";
  $mysql_query = $sql_query;
  //echo $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);
  echo mysql_error($userlink);
  if (mysql_num_rows($sql_result)!=0)
  {
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
      $results_array[mysql_result($sql_result,$r,'period')][mysql_result($sql_result,$r,'mid')] = mysql_result($sql_result,$r,'result');
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0

  // Get the list of final grades
  $sql_query = "SELECT * FROM student inner join gradestore using (sid) where period='0' AND gradestore.year='" . $period_array['year'][0] . "' AND student.sid='$sid'";
  $mysql_query = $sql_query;
  //echo $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);
  echo mysql_error($userlink);
  if (mysql_num_rows($sql_result)!=0)
  {
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
      $final_results_array[mysql_result($sql_result,$r,'mid')] = mysql_result($sql_result,$r,'result');
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0

  // Get the list of pass criteria per subject
  $sql_query = "SELECT mid,minimumpass FROM class LEFT JOIN coursepasscriteria USING (masterlink) LEFT JOIN sgrouplink USING(gid) WHERE sid='$uid'";
  $mysql_query = $sql_query;
  //echo $sql_query;

  $sql_result = mysql_query($mysql_query,$userlink);
  echo mysql_error($userlink);
  if (mysql_num_rows($sql_result)!=0)
  {
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
      $passpoint[mysql_result($sql_result,$r,'mid')] = mysql_result($sql_result,$r,'minimumpass');
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  
  // See if grades are blocked for this student
  $gblock = SA_loadquery("SELECT gradesblock FROM sgrouplink LEFT JOIN sgroup USING(gid) WHERE active=1 AND sid='$uid' AND gradesblock=1");
  $gblock = (isset($gblock) && $gblock != NULL);
  
  SA_closeDB();

  // First part of the page
  echo("<html><head><title>" . $dtext['gcrd_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['gcard_4'] . " " . $student_array['firstname'][1] . " " . $student_array['lastname'][1] . "</font><p>");
  include("studentmenu.php");
  if($gblock)
  {
    echo("<BR>". $dtext['No_grades']. "</html>");
  }
	else
	{
		echo("<br><div align=left>" . $dtext['gcrd_expl_1'] . "</dev><br>"); 

		echo("<br>");

		// Now create a table with all subjects for this student to enable to go to the grade details
		// Create the first heading row for the table
		echo("<table border=1 cellpadding=0>");
		echo("<tr><td><center>" . $dtext['Subject'] . "</td>");
		// Now add the periods heading
		for($p=1; $p<=$periods; $p++)
		{
			echo("<td><center><a href=showperiodresults.php?period=" . $p . ">". $dtext['Period_marker']. $p . "</a></td>");
		}
		if(isset($finalshow))
			echo("<td><center>" . $dtext['fin_per_ind'] . "</td></tr>"); 
		

		// Create a row in the table for every subject
		for($s=1;$s<=$subjects;$s++)
		{ // each subject
			$mid = $subject_array['mid'][$s];
			echo("<tr><td>" . $subject_array['fullname'][$s] . "</td>");
			for($p=1;$p<=$periods;$p++)
			{ // add the grades for regular periods
				$pp = $period_array['id'][$p];
				if($period_array['status'][$p] != 'closed')
				{
					echo("<td><center><a href=showstudentgradedetails.php?period=$pp&mid=$mid>");
					if(isset($results_array[$pp][$mid]))
					{ 
						$result = $results_array[$pp][$mid];
						// Colour depends on pass criteria
						if($passpoint[$mid] > $result) echo("<font color=red>");
						else echo("<font color=blue>");
						if($period_array['status'][$pp] == 'final') echo("<b>"); else echo("<i>");
				// Added 13 nov 2014: show something standard if period is open and result below threshold (if set)
				if(isset($nonteacheropenresultlimit) && $result < $nonteacheropenresultlimit && $period_array['status'][$pp] == 'open' && $result > 0)
					echo($nonteacheropenresultlimitalt);
				else
							echo(str_replace(".",$dtext['dec_sep'],"".$result));
						if($period_array['status'][$pp] == 'final') echo("</b>"); else echo("</i>");
						echo("</font>");
					}
					else
						echo("-");
					echo("</a></td>");
				}
				else
					echo("<td><center>X</td>");
			}
			// Add the final grade
		if(isset($finalshow))
		{
			echo("<td><center>");
			if(isset($final_results_array[$mid]))
			{
				if($any_closed == 'Y')
				echo("X");
				else
				{
				$result = $final_results_array[$mid];
				// Colour depends on pass criteria
				if($passpoint[$mid] > $result) echo("<font color=red>");
				else echo("<font color=blue>");
				if($any_open == 'N') echo("<b>"); else echo("<i>");
				if(isset($nonteacheropenresultlimit) && $result < $nonteacheropenresultlimit && $any_open == 'Y' && $result > 0)
					echo($nonteacheropenresultlimitalt);
				else
					echo(str_replace(".",$dtext['dec_sep'],"".$result));
				if($any_open == 'N') echo("</b>"); else echo("</i>");
				echo("</font>");
				}
			}
			else
				echo("-");
			echo("</td>");
		}
		}
		echo("</tr>");
		echo("</table>");
		echo("<br>" . $dtext['gcrd_expl_2']);
		echo("<br>" . $dtext['gcrd_expl_3']);
	}
  // See if there are any messages
	$msgs = message::list_messages($_SESSION['usertype'] == "student" ? "s" : "p",$_SESSION['uid']);
	if(isset($msgs))
		echo("<IFRAME width=80% height=70% style='margin-top:5%; border: 3px solid green; z-index=3000; position: fixed; top: 10%; left: 10%; background-color: white;' src=showmessages.php>");
	else
		echo("no messages for ". $_SESSION['usertype']. " ". $_SESSION['userid']);
  // close the page
  echo("</html>");

?>
