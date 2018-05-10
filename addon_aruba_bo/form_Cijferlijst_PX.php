<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014 Aim4me N.V.   (http://www.aim4me.info)	      |
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
  if(isset($HTTP_POST_VARS['NewGroup']) && SA_verifyGroupAccess($uid,$HTTP_POST_VARS['NewGroup']))
    $CurrentGroup = $HTTP_POST_VARS['NewGroup'];
  if(isset($HTTP_GET_VARS['NewGroup']) && SA_verifyGroupAccess($uid,$HTTP_GET_VARS['NewGroup']))
    $CurrentGroup = $HTTP_GET_VARS['NewGroup'];
  // Store the new group or future pages
  $_SESSION['CurrentGroup']=$CurrentGroup;

  $uid = intval($uid);

  // Table with month texts
  $months = array(1 => "januari","februari","maart","april","mei","juni","juli","augustus","september","oktober","november","december");
  // Translate the current group to a group id (gid)
  $sql_result = mysql_query("SELECT gid FROM sgroup WHERE active=1 AND groupname='$CurrentGroup'",$userlink);
  $gid = mysql_result($sql_result,0,'gid');

  // First we get the data from the students in an array.
  $sql_query = "SELECT student.* FROM student LEFT JOIN sgrouplink USING(sid) WHERE gid='$gid' ORDER BY lastname,firstname";
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

  // Get the list of periods with their details
  $periods = SA_loadquery("SELECT * FROM period WHERE status='open' ORDER BY id");

  // Get the list of applicable subjects with their details
  $sql_query = "SELECT * FROM subject inner join class using (mid) where gid='$gid' AND show_sequence IS NOT NULL GROUP BY subject.mid ORDER BY show_sequence";
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

  foreach($student_array['sid'] AS $sidx)
  {
    // Get a list of testresults for the current period
    $sql_query = "SELECT result,type,mid,testdef.tdid FROM testresult LEFT JOIN testdef using (tdid) LEFT JOIN class USING (cid) LEFT JOIN period ON(period.id=testdef.period) where sid='$sidx' AND period=". $periods['id'][1]. " AND period.year=testdef.year AND testdef.type <> '0' ORDER BY testresult.last_update";
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
       $test_array[$sidx][mysql_result($sql_result,$r,'mid')][mysql_result($sql_result,$r,'type')][mysql_result($sql_result,$r,'tdid')] = mysql_result($sql_result,$r,'result');
      } //for $r
      mysql_free_result($sql_result);
    }//If numrows != 0
    $tests[$sidx] = $nrows;
  }
  
  //  Get the list of calculated results
  $cq = "SELECT sid,mid,result FROM gradestore LEFT JOIN period ON(period.id=gradestore.period) LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid)";
  $cq .= " WHERE active=1 AND groupname='". $CurrentGroup. "' AND period.year=gradestore.year AND period=". $periods['id'][1];
  $calcs = SA_loadquery($cq);
  if(isset($calcs))
    foreach($calcs['sid'] AS $cix => $stid)
	  $cr[$stid][$calcs['mid'][$cix]] = $calcs['result'][$cix];

  // Get the list of pass criteria per subject & test type
  $sql_query = "SELECT * FROM reportcalc ORDER BY testtype,mid";
  $mysql_query = $sql_query;
  //echo $sql_query;
  $sql_result = mysql_query($mysql_query,$userlink);
  echo mysql_error($userlink);
  $nrows = 0;
  if (mysql_num_rows($sql_result)!=0)
  {
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
      $passpoints[mysql_result($sql_result,$r,'testtype')][mysql_result($sql_result,$r,'mid')] = mysql_result($sql_result,$r,'passthreshold');
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  
  // Get the mentor for this group
  $teacher = SA_loadquery("SELECT teacher.* FROM sgroup LEFT JOIN teacher ON(teacher.tid=sgroup.tid_mentor) WHERE active=1 AND groupname='". $CurrentGroup. "'");

  SA_closeDB();

  // First part of the page
  echo("<html><head><title>Cijferlijst</title></head><body link=blue vlink=blue");
  //echo(" onload=\"window.print();setTimeout(window.close(),10000);\"");
  echo(">");
  echo '<LINK rel="stylesheet" type="text/css" href="cijferlijst.css" title="style1">';
  
  foreach($student_array['sid'] AS $si => $sidx)
  {
    echo("<p class=\"pagebreak\">");
	echo("<center><img src=schoollogo.png border=0 width=100 height=100 align=center></center><BR>");
	echo("<BR><label>Cijferlijst van:</label><B><FONT SIZE=+1>". $student_array['firstname'][$si]. " ". $student_array['lastname'][$si]. "</FONT></B>");
	echo("<BR><label>Klas:</label><B><FONT SIZE=+1>". $CurrentGroup. "</FONT></B>");
	echo("<BR><label>Klasse-leerkracht:</label><B>". substr($teacher['firstname'][1],0,1). ". ". $teacher['lastname'][1]. "</B>");
	echo("<BR><BR><BR><P class=tableheader><B>Cijferlijst voor de periode <font size=+1>". $periods['id'][1]. "</font></B></P>");
    unset($testcount);
    unset($typecount);

    // Now we must find out how many entries max. for each type of test (max # of collumns)
    if(isset($test_array[$sidx]))
    {
      foreach($test_array[$sidx] AS $subji => $subtest)
      {
        foreach($subtest AS $tti => $testtype)
          $testcount[$tti][$subji] = count($testtype);
      }
    }

    if($tests[$sidx] > 0)
    {
      foreach($passpoints as $type => $value)
      {
        $typecount[$type] = 0;
        if(isset($testcount[$type]))
        {
          foreach($testcount[$type] as $count)
          {
            if($typecount[$type] < $count)
              $typecount[$type] = $count;
          }
        }
      }
    }

    if($tests[$sidx] > 0)
    {   
      // Now create a table with all subjects for this student to enable to go to the grade details
      // Create the first heading row for the table
      echo("<table border=1>");
      echo("<tr><th><center>" . $dtext['Subject'] . "</th>");
      // Now add results heading
	  $rcount = 0;
      foreach($typecount as $type => $count)
      {
        if($count > 0)
		  $rcount += $count;
      }
      echo("<th COLSPAN='$rcount'><center>Resultaten</th><th>Gem</th></tr>");
  

      // Create a row in the table for every subject
      $currentTest = 1;
      for($s=1;$s<=$subjects;$s++)
      { // each subject
        $mid = $subject_array['mid'][$s];
        $cid = $subject_array['cid'][$s];
        echo("<tr><td>" . $subject_array['fullname'][$s] . "&nbsp;</td>");
        foreach($typecount as $type => $count)
        {
           if(isset($passpoints[$type][$mid]))
             $passpoint=$passpoints[$type][$mid];
           else
             $passpoint=$passpoints[$type][0];
           if(isset($testcount[$type][$mid]))
           {
             foreach($test_array[$sidx][$mid][$type] AS $tdid => $result)
             {
			   if($result > 0.0)
			   {
                 echo("<td class=digit>");
                 // Colour depends on pass criteria
                 if($passpoint > $result) echo("<font color=red>");
                 else echo("<font color=blue>");
                 echo(number_format($result,1,",","."));
                 echo("</font></td>");
			   }
			   else
			     echo("<td>". $result. "</td>");
             }

             // Now pad with empty cells
             for($r=$testcount[$type][$mid]; $r<$count; $r++)
               echo("<td>&nbsp;</td>");
           }
           else
           { // No tests found for this type & subject!
             for($r=0;$r<$count;$r++)
               echo("<td>&nbsp;</td>");
           }
        }
		// Add the average
		if(isset($cr[$sidx][$mid]))
		{
		  echo("<td class=digit>");
		  $result = $cr[$sidx][$mid];
		  if($result > 0.0)
		  {
		    if(5.5 > $result) echo("<font color=red>");
		    else echo("<font color=blue>");
		    echo(number_format($result,1,",","."));
		    echo("</font></td>");
		  }
		  else
		    echo($result. "</td>");
		}
		else
		  echo("<td>&nbsp;</td>");
        echo("</tr>");
      }
      echo("</tr>");
      echo("</table>");
    }
    else
    { // No test results found or period is closed
      if($period_array['status'][$period] == 'closed')
        echo($dtext['perres_expl_1']);
      else
        echo($dtext['perres_expl_2']);

    }
	echo("<BR><P class=remarks>Opmerkingen van de klasse-leerkracht:</P><BR><HR><BR><HR>");
	echo("<BR><BR><BR><SPAN class=leftsign>Datum: ". date("j"). " ". $months[date("n")]. " ". date("Y"). ",</SPAN><BR><BR><BR>");
	echo("<BR><SPAN class=leftsign>___________________</SPAN><SPAN class=rightsign>___________________</SPAN>");
	echo("<BR><SPAN class=leftsign>klasse-leerkracht</SPAN><SPAN class=rightsign>ouder(s)</SPAN>");
  } // End loop for each student

  // close the page
  echo("</html>");

?>
