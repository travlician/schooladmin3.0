<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014  Aim4me N.V.   (http://www.aim4me.info)	  |
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
// Schooladmin Functions for grade calculation, results are stored in the database!

require_once("schooladminconstants.php");
require_once("schooladminfunctions.php");

// Function to calculate the grade of the reportcard for a specific student, a specific class for a specific period.
// The sid, cid and period are passed as parameters, the result is stored in the database table gradestore.
// In case no test definitions are available, no result is calculated (efficient for students that are present
// for history purposes only).
// any existing result is removed if the minimum criteria for a reportcard value are not met and
// the status for the period is not open (hence, proyections are made ONLY for open periods!).
// If a value is set (or deleted!) in the gradestore table, the (projected) final grade is also calculated and stored,
// using the function SA_calcFinal().
function SA_calcGrades($sid,$cid,$period)
{
  global $userlink,$MetaCalcWithoutAverage;
  $dtext = $_SESSION['dtext'];
  // translate the cid to a mid and gid.
  $sql_query = "SELECT class.*,shortname FROM class LEFT JOIN subject USING(mid) WHERE cid='$cid'";
  $mysql_query = $sql_query;
  //echo $sql_query;
  $sql_result = mysql_query($mysql_query,$userlink);
  //echo mysql_error($userlink);
  if($cid == "") return;
  $mid = mysql_result($sql_result,0,'mid');
  $gid = mysql_result($sql_result,0,'gid');
  $shortname = mysql_result($sql_result,0,'shortname');
  mysql_free_result($sql_result);
	
	// See if special formulas are applicable and handle these first
	if(isset($sid) && $sid > 0)
		SA_CalcSpecialFormula($sid,$mid,$gid,$period);

  // Get the list of test definitions with their details
  $sql_query = "SELECT * FROM testdef LEFT JOIN class USING (cid) LEFT JOIN period ON(period.id=period) WHERE mid='$mid' AND period='$period' AND period.year=testdef.year AND testdef.type <> '0' ORDER BY tdid";
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
       $testdef_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $testdefs = $nrows;

  // Get the period details
  $sql_query = "SELECT * FROM period WHERE id='$period'";
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
       $period_details[$fieldname]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0

  // Delete any existing gradestore values for this student/subject/period/year combination
  $mysql_query = "DELETE gradestore FROM gradestore LEFT JOIN subject USING(mid) WHERE sid='$sid' AND mid='$mid' AND year='" . $period_details['year'] . "' AND period='$period' AND type <> 'meta'";
  $sql_result = mysql_query($mysql_query,$userlink);
  if(mysql_error($userlink))
    echo(mysql_error($userlink));
  
  // IF no tests are defined, return because there is nothing to be done!
  if($testdefs == 0)
    return;

  // Get the testresults for the student
  $sql_query = "SELECT testresult.* FROM testresult LEFT JOIN testdef USING(tdid) LEFT JOIN class USING(cid) LEFT JOIN reportcalc ON(testdef.type=testtype) WHERE weight > 0.0 AND sid='$sid' AND result < '@' AND class.mid='$mid' AND year='";
  $sql_query .= $period_details['year']. "' AND period='$period' ORDER BY result";
  $mysql_query = $sql_query;
  //echo $sql_query;
  $sql_result = mysql_query($mysql_query,$userlink);
  //echo mysql_error($userlink);
  $nrows = 0;
  if (mysql_num_rows($sql_result)!=0)
  {
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
     $nrows++;
     $testres[mysql_result($sql_result,$r,'tdid')] = mysql_result($sql_result,$r,'result');
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $testress = $nrows;
  
  // New march 2013: use coursepasscriteria to see if a valid result can be produced for this period
  // Step 1: get the criteria
  $cpc = SA_loadquery("SELECT coursepasscriteria.* FROM class LEFT JOIN coursepasscriteria USING(masterlink) WHERE cid=". $cid);
  $mintests = $cpc['minpasspointbalance'][1];
  $maxmissing = $cpc['maxfails'][1];
  // Step 2: Get the amount of tests with weight factor > 0 defined.
  $vtest = SA_loadquery("SELECT COUNT(tdid) AS vtest FROM testdef LEFT JOIN reportcalc ON(reportcalc.testtype = testdef.type) WHERE cid=". $cid. " AND period=". $period. " AND year='". $period_details['year']. "' AND weight > 0");
  // Step 3: Get the amount of test results with weight factor > 0 present
  $rcount = SA_loadquery("SELECT COUNT(tdid) AS rcount FROM testresult LEFT JOIN testdef USING(tdid) LEFT JOIN reportcalc ON(reportcalc.testtype = testdef.type) WHERE sid='". $sid. "' AND period=". $period. " AND year='". $period_details['year']. "' AND weight > 0 AND result IS NOT NULL");
  // Step 4: apply the results
  if($vtest['vtest'][1] >= $mintests && ($vtest['vtest'][1] - $rcount['rcount'][1] <= $maxmissing))
    $initialvalid = 'Y';
  else
    $initialvalid = 'N';
	
  // Get the list of calculation rules per testtype
  $sql_query = "SELECT * FROM reportcalc WHERE mid='0' OR mid='$mid' ORDER BY mid";
  $mysql_query = $sql_query;
  //echo $sql_query;
  $sql_result = mysql_query($mysql_query,$userlink);
  //echo mysql_error($userlink);
  if (mysql_num_rows($sql_result)!=0)
  {
    $nfields = mysql_num_fields($sql_result);
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
     $type = mysql_result($sql_result,$r,'testtype');
     for ($i=0;$i<$nfields;$i++)
     {
       $fieldname = mysql_field_name($sql_result,$i);
       $fieldvalu = mysql_result($sql_result,$r,mysql_field_name($sql_result,$i));
       $calcRule[$fieldname][$type]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0

  // OK, now we got all results in arrays and can start crunshing numbers on it.
  // Make two arrays with the summed result and count per testtype.
  for($t=1; $t<=$testdefs; $t++)
  { // we use test definition here, results also include for other subjects and periods!
    $tdid = $testdef_array['tdid'][$t];
    $type = $testdef_array['type'][$t];
	if($type != "")
	{
      if(!isset($ressum[$type]))
      {
	    // Only do this if the test definition is for this group!
	    if($testdef_array['gid'][$t] == $gid)
	    {
          $ressum[$type] = 0.0;
          $rescount[$type] = 0.0;
	    }
      }
      if(isset($testres[$tdid]))
      {
        if(!isset($ressum[$type])) // This happens only if student switched from one group to another
        {
          $ressum[$type] = 0.0;
          $rescount[$type] = 0.0;
	    }
        $ressum[$type] += $testres[$tdid];
        $rescount[$type]++;
      }
	}
  }

  
  // Now pass through all elements of these arrays to do the required things...
  $resultValid = $initialvalid;
  $finalres = 0.0;
  $finalcount = 0.0;
  $digits = -2;
  if(isset($rescount))
  foreach($rescount as $type => $count)
  {
    // Signal problem if no calculation rule is present for this type of test
    if(!isset($calcRule['weight'][$type]))
      echo("<br>" . $dtext['gcalc_w1'] . " " . $type . "<br>"); 
    // First we drop the values that can be discarded.
    $discardcount = $count - ($calcRule['validifatleast'][$type] > 0 ? $calcRule['validifatleast'][$type] : 1);
    if($discardcount > $calcRule['dropworst'][$type])
      $discardcount = $calcRule['dropworst'][$type];
    for($dc=0; $dc<$discardcount; $dc++)
    { // for each result to drop....
      // Find the result to drop
      $todrop = -1;
      $lowsofar = 100000;
      for($ti=1; $ti<=$testdefs; $ti++)
      {
        $tdid = $testdef_array['tdid'][$ti];
        if($testdef_array['type'][$ti] == $type && isset($testres[$tdid]))
          if($testres[$tdid] < $lowsofar)
          {
            $lowsofar = $testres[$tdid];
            $todrop = $tdid;
          }
      }
      if($todrop >= 0)
      { // drop this value by adjusting arrays and unsetting the result
        $ressum[$type] -= $testres[$todrop];
        $rescount[$type]--;
        unset($testres[$todrop]);
      }
    }

    // Calculate the averages for those types that use it.
    if($calcRule['on_average'][$type] == 'Y' && $rescount[$type] > 0)
    {
      $ressum[$type] = round($ressum[$type] / $rescount[$type],$calcRule['digitsafterdot'][$type]);
      $rescount[$type] = 1;
    }
  
    // set the invalid flag if the required number of results is not met.
    if($count < $calcRule['validifatleast'][$type])
      $resultValid = 'N';

    // Adjust the amount of digits needed in the final result
    if($calcRule['digitsafterdot'][$type] > $digits)
	{
      $digits = $calcRule['digitsafterdot'][$type];
	  //echo("Digits set to ". $digits. " (type=". $type. ",mid=". $mid. ")<BR>");
	}

    // Finally, apply the weight to put it into the final result
    $finalres += $calcRule['weight'][$type] * $ressum[$type];
    $finalcount += $calcRule['weight'][$type] * $rescount[$type];
  }
  // Now we can calculate the final result!
  if($finalcount > 0)
    $finalres = round($finalres / $finalcount, $digits);
  
  // Update the database with the calculated result.
  // First see if whe have a valid result
  if($period_details['status'] == 'open')
    $resultValid = 'Y';
  if($finalcount < 1)
    $resultValid = 'N';

  // Maybe we don't have any values... in that case we try if letter values are present
  if($testress == 0)
  {
    $rquery = "SELECT result FROM testresult LEFT JOIN testdef USING(tdid) WHERE sid='$sid' AND result > '@' AND cid='$cid' AND year='";
    $rquery .= $period_details['year']. "' AND period='$period' ORDER BY testdef.date DESC LIMIT 1";
	$rqr = SA_loadquery($rquery);
	if(isset($rqr['result'][1]))
	{
	  $resultValid = 'Y';
	  $finalres = $rqr['result'][1];
	}
  }
  // Now insert a new one if applicable
  if($resultValid == 'Y')
  {
    $sql_query = "INSERT INTO gradestore VALUES('$sid', '$period', '";
    $sql_query = $sql_query . $period_details['year'];
    $sql_query = $sql_query . "', '$mid', '$finalres')";
    $mysql_query = $sql_query;
    //echo $sql_query;
    $sql_result = mysql_query($mysql_query,$userlink);
  }
  if(!$sql_result)
  {
    echo("<br>" . $dtext['gcalc_w2'] . " " . mysql_error($userlink));
  }
  
  // Adjust the final period grade
  SA_calcFinal($sid,$cid);
  // Adjust the results for the Meta subject
  if(isset($MetaCalcWithoutAverage))
    $MCWAshortnames = explode(",",$MetaCalcWithoutAverage);
  if((isset($MetaCalcWithoutAverage) && $MetaCalcWithoutAverage == 1) || (isset($MCWAshortnames) && in_array($shortname,$MCWAshortnames)))
    SA_calcMetaNonAverage($sid,$cid,$period);
  else
    SA_calcMeta($sid,$cid); 
}

// Function as above, but not for one student but a group of students (typical use: grades for a 
// class might have been changed, e.g. the results for a test entered).
function SA_calcGradeGroup($cid, $period)
{
  global $userlink;
 
  @set_time_limit(300);

  // First we get a list of the students ($cid indicates which group!)
  $sql_query = "SELECT sid FROM class LEFT JOIN sgrouplink USING (gid) WHERE cid='$cid'";
  $mysql_query = $sql_query;
  //echo $sql_query;
  $sql_result = mysql_query($mysql_query,$userlink);
  //echo mysql_error($userlink);
  $nrows = 0;
  if (mysql_num_rows($sql_result)!=0)
  {
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
     $nrows++;
       $sids[$nrows]=mysql_result($sql_result,$r,'sid');
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $students = $nrows;
  
  // Now traverse all students to call the basic routine
  for($s=1;$s<=$students;$s++)
    SA_calcGrades($sids[$s], $cid, $period);
}

// Function as the ones above, however for all classes for the specified period. 
// Typical use is when a period changes state (calculation might take a while!)
function SA_calcGradePeriod($period)
{
  global $userlink;

  ini_set('max_execution_time',300);
 // Changed on nov 30 2013: Make sure meta subjects come last to ensure these are not unduly overwritten by sub-subjects
  $sql_query = "SELECT cid FROM class LEFT JOIN subject USING(mid) LEFT JOIN sgroup USING(gid) WHERE active=1 ORDER BY `type` DESC";
  //$sql_query = "SELECT cid FROM class";
  $mysql_query = $sql_query;
  //echo $sql_query;
  $sql_result = mysql_query($mysql_query,$userlink);
  echo mysql_error($userlink);
  $nrows = 0;
  if (mysql_num_rows($sql_result)!=0)
  {
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
     $nrows++;
       $cids[$nrows]=mysql_result($sql_result,$r,'cid');
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $cidcnt = $nrows;
  
  // Now traverse all students to call the basic routine
  for($c=1;$c<=$cidcnt;$c++)
    SA_calcGradeGroup($cids[$c], $period);
  
}


// function to (re)calculate the projected or final reportcard grade over all periods.
// In case any of the periods has a status of "open", a grade is calculated no matter what.
// Otherwise, the existing grade can even be removed if no valid grade is found for all periods.
function SA_calcFinal($sid, $cid)
{
  global $userlink;
  global $altwftable;
  global $allownongradedperiods;
  // Get the mid & masterlink values
  $sql_query = "SELECT * FROM class WHERE cid='$cid'";
  $mysql_query = $sql_query;
  //echo $sql_query;
  $sql_result = mysql_query($mysql_query,$userlink);
  //echo mysql_error($userlink);
  $mid = mysql_result($sql_result,0,'mid');
  $masterlink = mysql_result($sql_result,0,'masterlink');
  
  mysql_free_result($sql_result);

  // Load the stored calculated result details
  // $sql_query = "SELECT * FROM gradestore INNER JOIN period USING (year) WHERE sid='$sid' AND period.id=gradestore.period AND mid='$mid' AND result < '@' AND period > 0 GROUP BY period ORDER BY period";
  $sql_query = "SELECT * FROM gradestore INNER JOIN period USING (year) WHERE sid='$sid' AND period.id=gradestore.period AND mid='$mid' AND period > 0 GROUP BY period ORDER BY period";
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
     for ($i=0;$i<$nfields;$i++)
     {
       $fieldname = mysql_field_name($sql_result,$i);
       $fieldvalu = mysql_result($sql_result,$r,mysql_field_name($sql_result,$i));
       $grade_details[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $results = $nrows;


  // Load the stored calculated result details
  $sql_query = "SELECT * FROM period ORDER BY id";
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
     for ($i=0;$i<$nfields;$i++)
     {
       $fieldname = mysql_field_name($sql_result,$i);
       $fieldvalu = mysql_result($sql_result,$r,mysql_field_name($sql_result,$i));
       $period_details[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $periods = $nrows;

  // If specific period weight factors are enabled and available for this student, these take precedence.
  if(isset($altwftable))
  {
    $altwfqr = SA_loadquery("SELECT data FROM `". $altwftable. "` WHERE sid='". $sid. "'");
	if(isset($altwfqr['data'][1]) && intval($altwfqr['data'][1]) > 0)
	{
	  $wfmid = 0 - intval($altwfqr['data'][1]);
	}
	else
	  $wfmid = $mid;
  }
  else
    $wfmid = $mid;

  // Load the applicable final calculation weights
  $sql_query = "SELECT * FROM finalcalc WHERE mid='0' UNION SELECT * FROM finalcalc WHERE mid='$wfmid'";
  $mysql_query = $sql_query;
  //echo $sql_query;
  $sql_result = mysql_query($mysql_query,$userlink);
  //echo mysql_error($userlink);
  if (mysql_num_rows($sql_result)!=0)
  {
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
     $period = mysql_result($sql_result,$r,'period');
     $weight[$period]=mysql_result($sql_result,$r,'weigth');
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  
  // Load the course pass criteria (just for digits after dot value!)
  $sql_query = "SELECT * FROM coursepasscriteria WHERE masterlink='0' OR masterlink='$masterlink' ORDER BY masterlink";
  $mysql_query = $sql_query;
  //echo $sql_query;
  $sql_result = mysql_query($mysql_query,$userlink);
  //echo mysql_error($userlink);
  if (mysql_num_rows($sql_result)!=0)
  {
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
     $digits=mysql_result($sql_result,$r,'digitsafterdotfinal');
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0

  // calculate the final grade
  $finsum = 0.0;
  $fincnt = 0.0;
  for($r=1; $r<=$results;$r++)
  {
    if($grade_details['result'][$r] < '@')
	{
      $finsum += $grade_details['result'][$r] * $weight[$grade_details['period'][$r]];
      $fincnt += $weight[$grade_details['period'][$r]];
	}
  }
  if($fincnt > 0.0)
    $finsum = round($finsum / $fincnt, $digits);

  // Store final grade or remove it if not valid
  $isValid = 'N';
  for($p=1;$p<=$periods;$p++)
    if($period_details['status'][$p] == 'open')
      $isValid = 'Y';

  // Changed on jun 25 2013: End result is valid if at least one result is present (used to be result for every period) depeding on config!
  // Subjects given in 1 or 2 periods only (like Colegio Pariba) 
  if(isset($allownongradedperiods) && $allownongradedperiods > 0)
  {
    if($results >= 0)
      $isValid = 'Y';
  }
  else
  {
    if($periods == $results)
      $isValid = 'Y';
  }
  if($fincnt <= 0.0) // No results or alphanumeric results only
    $isValid = 'N';
  // First delete any existing values
  //$mysql_query = "DELETE gradestore FROM gradestore LEFT JOIN subject USING(mid) WHERE sid='$sid' AND mid='$mid' AND year='" . $period_details['year'][$periods] . "' AND period=0 AND type <> 'meta'";
  $mysql_query = "DELETE gradestore FROM gradestore LEFT JOIN subject USING(mid) WHERE sid='$sid' AND mid='$mid' AND year='" . $period_details['year'][$periods] . "' AND period=0";
  $sql_result = mysql_query($mysql_query,$userlink);
  // Now insert a new one if applicable
  if($isValid == 'Y')
  {
    $sql_query = "INSERT INTO gradestore VALUES('$sid', '0', '";
    $sql_query = $sql_query . $period_details['year'][$periods];
    $sql_query = $sql_query . "', '$mid', '$finsum')";
    $mysql_query = $sql_query;
    //echo $sql_query;
    $sql_result = mysql_query($mysql_query,$userlink);
  }
  if(!$sql_result)
  {
    $dtext = $_SESSION['dtext'];
    echo("<br>" . $dtext['gcalc_w2'] . " " . mysql_error($userlink));
  }
}

// function to (re)calculate the projected or final reportcard grades for a Meta subject over all periods.
// The parameter given is the class (translatable to a subject). The grade is calculated for the
// corresponding Meta subject if any.
// In case any of the periods has a status of "open", a grade is calculated no matter what.
// Otherwise, the existing grade can even be removed if no valid grade is found for all periods.
function SA_calcMeta($sid, $cid)
{
  global $userlink;
  global $altwftable;
  global $allownongradedperiods;
  // Get the mid value and gid of the meta subject
  $sql_query = "SELECT * FROM class inner join subject using (mid) WHERE cid='$cid'";
  $mysql_query = $sql_query;
  //echo $sql_query;
  $sql_result = mysql_query($mysql_query,$userlink);
  //echo mysql_error($userlink);
  $mytype = mysql_result($sql_result,0,'type');
  // If the type is NOT 'sub', there are no associated meta subjects so just leave
  if($mytype != 'sub')
    return;
  $mid = mysql_result($sql_result,0,'meta_subject');
  $gid = mysql_result($sql_result,0,'gid');
  
  // Get the masterlink value of the meta subject
  $sql_query = "SELECT * FROM class WHERE mid='$mid' AND gid='$gid'";
  $mysql_query = $sql_query;
  //echo $sql_query;
  $sql_result = mysql_query($mysql_query,$userlink);
  if(mysql_num_rows($sql_result) == 0)
  { // sub type subject but no meta was found!
    return;
  }
  //echo mysql_error($userlink);
  $masterlink = mysql_result($sql_result,0,'masterlink');
  mysql_free_result($sql_result);

  // Now get the list of subjects that apply to the meta subject.
  $sql_query = "SELECT subject.mid FROM class INNER JOIN subject USING (mid) WHERE meta_subject='$mid' AND gid='$gid' GROUP BY subject.mid";
  $mysql_query = $sql_query;
  //echo $sql_query;
  $sql_result = mysql_query($mysql_query,$userlink);
  //echo mysql_error($userlink);
  $nrows = 0;
  if (mysql_num_rows($sql_result)!=0)
  {
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
      $nrows++;
      $submid[$nrows] = mysql_result($sql_result,$r,'mid');
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $subjects = $nrows;

  // Load the stored calculated result details (numerics)
  $sql_query = "SELECT * FROM gradestore INNER JOIN period USING (year) WHERE sid='$sid' AND period.id=gradestore.period AND result < '@'";
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
      $grade_details[mysql_result($sql_result,$r,'mid')][mysql_result($sql_result,$r,'period')] = mysql_result($sql_result,$r,'result');
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $results = $nrows;

  // Load the stored calculated result details (non numerics)
  $sql_query = "SELECT * FROM gradestore INNER JOIN period USING (year) WHERE sid='$sid' AND period.id=gradestore.period AND result >= '@'";
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
      $alfa_details[mysql_result($sql_result,$r,'mid')][mysql_result($sql_result,$r,'period')] = mysql_result($sql_result,$r,'result');
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $alfaresults = $nrows;

  // Load the stored period details
  $sql_query = "SELECT * FROM period ORDER BY id";
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
     for ($i=0;$i<$nfields;$i++)
     {
       $fieldname = mysql_field_name($sql_result,$i);
       $fieldvalu = mysql_result($sql_result,$r,mysql_field_name($sql_result,$i));
       $period_details[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $periods = $nrows;
  
  // If specific period weight factors are enabled and available for this student, these take precedence.
  if(isset($altwftable))
  {
    $altwfqr = SA_loadquery("SELECT data FROM `". $altwftable. "` WHERE sid=". $sid);
	if(isset($altwfqr['data'][1]) && intval($altwfqr['data'][1]) > 0)
	{
	  $wfmid = 0 - intval($altwfqr['data'][1]);
	}
	else
	  $wfmid = $mid;
  }
  else
    $wfmid = $mid;

  // Load the applicable final calculation weights
  $sql_query = "SELECT * FROM finalcalc WHERE mid='0' UNION SELECT * FROM finalcalc WHERE mid='$wfmid'";
  $mysql_query = $sql_query;
  //echo $sql_query;
  $sql_result = mysql_query($mysql_query,$userlink);
  //echo mysql_error($userlink);
  if (mysql_num_rows($sql_result)!=0)
  {
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
     $period = mysql_result($sql_result,$r,'period');
     $weight[$period]=mysql_result($sql_result,$r,'weigth');
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  
  // Get weight factors for subjects per period (used for sub-subjects to calc meta subject period result)
  $subweightqr = SA_loadquery("SELECT * FROM finalcalc");
  if(isset($subweightqr['period']))
  {
    foreach($subweightqr['mid'] AS $swix => $asubmid)
	{
	  $subweight[$asubmid][$subweightqr['period'][$swix]] = $subweightqr['weigth'][$swix];
	}
    // If all submid weights are 0, make them 1
    $allsmidw0 = true;
    foreach($subweightqr['mid'] AS $swix => $asubmid)
	{
	  if($subweightqr['weigth'][$swix] > 0.0)
	    $allsmidw0 = false;
	}
	if($allsmidw0)
	{
      foreach($subweightqr['mid'] AS $swix => $asubmid)
	  {
	    $subweight[$asubmid][$subweightqr['period'][$swix]] = 1.0;
	  }
	}
  }
  else
    echo("No finalcalc values present!<BR>");

// Load the course pass criteria (just for digits after dot value!)
  $sql_query = "SELECT * FROM coursepasscriteria WHERE masterlink='0' OR masterlink='$masterlink' ORDER BY masterlink";
  $mysql_query = $sql_query;
  //echo $sql_query;
  $sql_result = mysql_query($mysql_query,$userlink);
  //echo mysql_error($userlink);
  if (mysql_num_rows($sql_result)!=0)
  {
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
     $digits=mysql_result($sql_result,$r,'digitsafterdotfinal');
		 $perdigitsqr=mysql_result($sql_result,$r,'digitsafterdotperiod');
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0

  // Load the calculation rules, just to find the #of digits required. !! Use coursepasscriteria column digitsafterdotperiod if defined!
  $sql_query = "SELECT mid,testtype,digitsafterdot FROM reportcalc WHERE mid='0' OR mid='$mid' ORDER BY mid";
  $sql_result = mysql_query($sql_query,$userlink);
  if(mysql_num_rows($sql_result) != 0)
  {
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
      $ttdigs[mysql_result($sql_result,$r,'testtype')] = mysql_result($sql_result,$r,'digitsafterdot');
    }
    mysql_free_result($sql_result);
  }

  // Calculte the digits required for the period result.
  $perdigits = -2;
  if(isset($ttdigs))
    foreach($ttdigs as $digs)
    {
      if($digs > $perdigits)
        $perdigits = $digs;
    }
	if(isset($perdigitsqr) && $perdigitsqr != "")
		$perdigits=$perdigitsqr;
  // Calculate & store the meta grades for each period and maintain data for the total average
  $finsum = 0.0;
  $fincnt = 0.0;
  $results = 0;
  for($p=1;$p<=$periods;$p++)
  {
    $perres = 0.0;
    $percnt = 0.0;
    $allSet = 'Y';
		unset($alfaresult);
    for($s=1; $s<=$subjects;$s++)
    {
	    $smid = $submid[$s];
      if(isset($grade_details[$smid][$period_details['id'][$p]]))
      {
	      if(isset($subweight[$submid[$s]][$p]))
		     $sweight = $subweight[$submid[$s]][$p];
		    else
		      $sweight = $subweight[0][$p];
        $perres += $sweight * $grade_details[$submid[$s]][$period_details['id'][$p]];
        $percnt += $sweight;
      }
      else
	    if(!isset($allownongradedperiods) || $allownongradedperiods == 0)
        $allSet = 'N';
			if(isset($alfa_details[$submid[$s]][$period_details['id'][$p]]))
			  $alfaresult = $alfa_details[$submid[$s]][$period_details['id'][$p]];
    }
    // Calculate the result for the Meta subject for this period
    if($percnt > 0.0)
    {
      $perres = round($perres / $percnt,$perdigits);
      $finsum += $perres * $weight[$p];
      $fincnt += $weight[$p];
    }
    // Now delete any existing value from the database
    $sql_query = "DELETE FROM gradestore WHERE sid='$sid' AND mid='$mid' AND period='" . $period_details['id'][$p] . "'AND year='" . $period_details['year'][$periods] . "'";
    mysql_query($sql_query,$userlink);
		//echo(mysql_error($userlink));
			// Store the value if period is open or allset is 'Y'
		// Changed on may 27 2010: Store always!
			//if($percnt > 0.0 && ($period_details['status'][$p] == 'open' || $allSet == 'Y'))
			//{
		if($perres != "0")
		{
				$sql_query = "INSERT INTO gradestore values('$sid', '" . $period_details['id'][$p] . "', '" . $period_details['year'][$p] . "', '$mid', '$perres')";
				mysql_query($sql_query, $userlink);
				$results++;
		}
		else if(isset($alfaresult))
		{
				$sql_query = "INSERT INTO gradestore values('$sid', '" . $period_details['id'][$p] . "', '" . $period_details['year'][$p] . "', '$mid', '$alfaresult')";
				mysql_query($sql_query, $userlink);			
		}
    //}
  }

  // calculate the final grade
  if($fincnt > 0.0)
    $finsum = round($finsum / $fincnt, $digits);

  // Store final grade or remove it if not valid
  $isValid = 'N';
  for($p=1;$p<=$periods;$p++)
    if($period_details['status'][$p] == 'open')
      $isValid = 'Y';
  // Changed on jun 25 2013: End result is valid if at least one result is present (used to be result for every period) depeding on config!
  // Subjects given in 1 or 2 periods only (like Colegio Pariba) 
  if(isset($allownongradedperiods) && $allownongradedperiods > 0)
  {
    if($results >= 0)
      $isValid = 'Y';
  }
  else
  {
    if($periods == $results)
      $isValid = 'Y';
  }
  if($fincnt <= 0.0)
    $isValid = 'N';
  // First delete any existing values
  $mysql_query = "DELETE FROM gradestore WHERE sid='$sid' AND mid='$mid' AND year='" . $period_details['year'][$periods] . "' AND period='0'";
  $sql_result = mysql_query($mysql_query,$userlink);
  //echo(mysql_error($userlink));
  // Now insert a new one if applicable
  if($isValid == 'Y')
  {
    $sql_query = "INSERT INTO gradestore VALUES('$sid', '0', '";
    $sql_query = $sql_query . $period_details['year'][$periods];
    $sql_query = $sql_query . "', '$mid', '$finsum')";
    $mysql_query = $sql_query;
    //echo $sql_query;
    $sql_result = mysql_query($mysql_query,$userlink);
  }
  if(!$sql_result)
  {
    echo("<br>" . $dtext['gcalc_w2'] . " " . mysql_error($userlink));
  }
}

// New form to calculate the result for a Meta subject, using all results for the sub-subjects instead of the results calculated for the sub-subjects.
// This method is used if the $MetaCalcWithoutAverage is set to 1
function SA_calcMetaNonAverage($sid,$cid,$period)
{
  global $userlink;
  $dtext = $_SESSION['dtext'];
  // translate the cid to a mid and gid.
  $sql_query = "SELECT * FROM class LEFT JOIN subject USING(mid) WHERE cid='$cid'";
  $mysql_query = $sql_query;
  //echo $sql_query;
  $sql_result = mysql_query($mysql_query,$userlink);
  //echo mysql_error($userlink);
  $mid = mysql_result($sql_result,0,'meta_subject');
  $gid = mysql_result($sql_result,0,'gid');
  $mytype = mysql_result($sql_result,0,'type');
  // If the type is NOT 'sub', there are no associated meta subjects so just leave
  if($mytype != 'sub')
    return;
  mysql_free_result($sql_result);
  // Get the cid for final period calculation
  $sql_query = "SELECT cid FROM class LEFT JOIN subject USING(mid) WHERE mid='$mid' AND gid='$gid'";
  $mysql_query = $sql_query;
  //echo $sql_query;
  $sql_result = mysql_query($mysql_query,$userlink);
    //echo mysql_error($userlink);
  if(mysql_num_rows($sql_result) < 1)
    echo("ERROR no meta subject for cid ". $cid. ", mid ". $mid);
  $meta_cid = mysql_result($sql_result,0,'cid');
  mysql_free_result($sql_result);
  

  // Get the list of test definitions with their details
  $sql_query = "SELECT * FROM testdef LEFT JOIN class USING (cid) LEFT JOIN period ON(period.id=period) LEFT JOIN subject USING (mid) WHERE meta_subject='$mid' AND period='$period' AND period.year=testdef.year AND testdef.type <> '0' ORDER BY tdid";
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
       $testdef_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $testdefs = $nrows;

  // Get the period details
  $sql_query = "SELECT * FROM period WHERE id='$period'";
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
       $period_details[$fieldname]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0

  // Delete any existing gradestore values for this student/subject/period/year combination
  //$mysql_query = "DELETE gradestore FROM gradestore LEFT JOIN subject USING(mid) WHERE sid='$sid' AND mid='$mid' AND year='" . $period_details['year'] . "' AND period='$period' AND type <> 'meta'";
  $mysql_query = "DELETE gradestore FROM gradestore LEFT JOIN subject USING(mid) WHERE sid='$sid' AND mid='$mid' AND year='" . $period_details['year'] . "' AND period='$period'";
  $sql_result = mysql_query($mysql_query,$userlink);
  if(mysql_error($userlink))
    echo(mysql_error($userlink));
  
  // IF no tests are defined, return because there is nothing to be done!
  if($testdefs == 0)
    return;

  // Get the testresults for the student
  $sql_query = "SELECT testresult.* FROM testresult LEFT JOIN testdef USING(tdid) LEFT JOIN class USING(cid) LEFT JOIN subject USING(mid) LEFT JOIN reportcalc ON (testdef.type=testtype) WHERE weight > 0.0 AND sid='$sid' AND result < '@' AND meta_subject='$mid' AND year='";
  $sql_query .= $period_details['year']. "' AND period='$period' ORDER BY result";
  $mysql_query = $sql_query;
  //echo $sql_query;
  $sql_result = mysql_query($mysql_query,$userlink);
  //echo mysql_error($userlink);
  $nrows = 0;
  if (mysql_num_rows($sql_result)!=0)
  {
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
     $nrows++;
     $testres[mysql_result($sql_result,$r,'tdid')] = mysql_result($sql_result,$r,'result');
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $testress = $nrows;


  // Get the list of calculation rules per testtype
  $sql_query = "SELECT * FROM reportcalc WHERE mid='0' OR mid='$mid' ORDER BY mid";
  $mysql_query = $sql_query;
  //echo $sql_query;
  $sql_result = mysql_query($mysql_query,$userlink);
  //echo mysql_error($userlink);
  if (mysql_num_rows($sql_result)!=0)
  {
    $nfields = mysql_num_fields($sql_result);
    for($r=0;$r<mysql_num_rows($sql_result);$r++)
    {
     $type = mysql_result($sql_result,$r,'testtype');
     for ($i=0;$i<$nfields;$i++)
     {
       $fieldname = mysql_field_name($sql_result,$i);
       $fieldvalu = mysql_result($sql_result,$r,mysql_field_name($sql_result,$i));
       $calcRule[$fieldname][$type]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0

  // OK, now we got all results in arrays and can start crunshing numbers on it.
  // Make two arrays with the summed result and count per testtype.
  for($t=1; $t<=$testdefs; $t++)
  { // we use test definition here, results also include for other subjects and periods!
    $tdid = $testdef_array['tdid'][$t];
    $type = $testdef_array['type'][$t];
	if($type != "")
	{
      if(!isset($ressum[$type]))
      {
	    // Only do this if the test definition is for this group!
	    if($testdef_array['gid'][$t] == $gid)
	    {
          $ressum[$type] = 0.0;
          $rescount[$type] = 0.0;
	    }
      }
      if(isset($testres[$tdid]))
      {
        if(!isset($ressum[$type])) // This happens only if student switched from one group to another
        {
          $ressum[$type] = 0.0;
          $rescount[$type] = 0.0;
	    }
        $ressum[$type] += $testres[$tdid];
        $rescount[$type]++;
      }
	}
  }

  
  // Now pass through all elements of these arrays to do the required things...
  $resultValid = 'Y';
  $finalres = 0.0;
  $finalcount = 0.0;
  $digits = -2;
  if(isset($rescount))
  foreach($rescount as $type => $count)
  {
    // Signal problem if no calculation rule is present for this type of test
    if(!isset($calcRule['weight'][$type]))
      echo("<br>" . $dtext['gcalc_w1'] . " " . $type . "<br>"); 
    // First we drop the values that can be discarded.
    $discardcount = $count - ($calcRule['validifatleast'][$type] > 0 ? $calcRule['validifatleast'][$type] : 1);
    if($discardcount > $calcRule['dropworst'][$type])
      $discardcount = $calcRule['dropworst'][$type];
    for($dc=0; $dc<$discardcount; $dc++)
    { // for each result to drop....
      // Find the result to drop
      $todrop = -1;
      $lowsofar = 100000;
      for($ti=1; $ti<=$testdefs; $ti++)
      {
        $tdid = $testdef_array['tdid'][$ti];
        if($testdef_array['type'][$ti] == $type && isset($testres[$tdid]))
          if($testres[$tdid] < $lowsofar)
          {
            $lowsofar = $testres[$tdid];
            $todrop = $tdid;
          }
      }
      if($todrop >= 0)
      { // drop this value by adjusting arrays and unsetting the result
        $ressum[$type] -= $testres[$todrop];
        $rescount[$type]--;
        unset($testres[$todrop]);
      }
    }

    // Calculate the averages for those types that use it.
    if($calcRule['on_average'][$type] == 'Y' && $rescount[$type] > 0)
    {
      $ressum[$type] = round($ressum[$type] / $rescount[$type],$calcRule['digitsafterdot'][$type]);
      $rescount[$type] = 1;
    }
  
    // set the invalid flag if the required number of results is not met.
    if($count < $calcRule['validifatleast'][$type])
      $resultValid = 'N';

    // Adjust the amount of digits needed in the final result
    if($calcRule['digitsafterdot'][$type] > $digits)
      $digits = $calcRule['digitsafterdot'][$type];

    // Finally, apply the weight to put it into the final result
    $finalres += $calcRule['weight'][$type] * $ressum[$type];
    $finalcount += $calcRule['weight'][$type] * $rescount[$type];
  }
  // Now we can calculate the final result!
  if($finalcount > 0)
    $finalres = round($finalres / $finalcount, $digits);
  
  // Update the database with the calculated result.
  // First see if whe have a valid result
  if($period_details['status'] == 'open')
    $resultValid = 'Y';
  if($finalcount < 1)
    $resultValid = 'N';

  // Maybe we don't have any values... in that case we try if letter values are present
  if($testress == 0)
  {
    $rquery = "SELECT result FROM testresult LEFT JOIN testdef USING(tdid) LEFT JOIN class USING(cid) LEFT JOIN subject USING(mid) WHERE sid='$sid' AND result > '@' AND meta_subject='$mid' AND year='";
    $rquery .= $period_details['year']. "' AND period='$period' ORDER BY testdef.date DESC LIMIT 1";
	$rqr = SA_loadquery($rquery);
	if(isset($rqr['result'][1]))
	{
	  $resultValid = 'Y';
	  $finalres = $rqr['result'][1];
	}
  }
  // Now insert a new one if applicable
  if($resultValid == 'Y')
  {
    $sql_query = "INSERT INTO gradestore VALUES('$sid', '$period', '";
    $sql_query = $sql_query . $period_details['year'];
    $sql_query = $sql_query . "', '$mid', '$finalres')";
    $mysql_query = $sql_query;
    //echo $sql_query;
    $sql_result = mysql_query($mysql_query,$userlink);
  }
  if(!$sql_result)
  {
    echo("<br>" . $dtext['gcalc_w2'] . " " . mysql_error($userlink));
  }
  
  // Adjust the final period grade
  SA_calcFinal($sid,$meta_cid);
}

// Calculate specials cases as defined in special formulas.
function SA_CalcSpecialFormula($sid,$mid,$gid,$period)
{
	global $userlink,$specialformulalock;
	if(isset($specialformulalock) && $specialformulalock)
		return;
	$sflistqr = SA_loadquery("SELECT * FROM specialformulas WHERE sourcemid=". $mid);
	if(isset($sflistqr['formulaid']))
	{ // So there are special formulas applicable with result source in the subject to be used as source.
    // Create a temporary table for storage of results
		mysql_query("CREATE TEMPORARY TABLE IF NOT EXISTS specialresults (data TEXT, short_desc VARCHAR(7), tdid int(11), sid int(11)) ENGINE=InnoDB CHARSET=utf8", $userlink);
		echo(mysql_error($userlink));
		// Get the schoolyear
		$yearqr = SA_loadquery("SELECT year FROM period WHERE id=". $period);
		$schoolyear = $yearqr['year'][1];
		foreach($sflistqr['formulaid'] AS $sfix => $formulaid)
		{ // See if a result should be created, modified or removed for this student.
		  // First a see if this student has results that relate to this special formula
			mysql_query("DELETE FROM specialresults", $userlink);
			$formclrq = "INSERT INTO specialresults SELECT result,short_desc,testdef.tdid,sid FROM testresult LEFT JOIN testdef USING(tdid) LEFT JOIN class USING(cid) LEFT JOIN specialformula_sources USING(short_desc) WHERE mid=". $mid. " AND formulaid=". $formulaid. " AND period=". $period. " AND year='". $schoolyear. "' AND result IS NOT NULL AND sid=". $sid;
			mysql_query($formclrq, $userlink);
			if(mysql_error($userlink))
				echo(mysql_error($userlink). " In query {". $formclrq. "} mid=". $mid. ", gid=". $gid. ", period=". $period);
			if(mysql_affected_rows($userlink) > 0)
			{ // results have been found, we need to calculate and add the result
				//echo("Special formula results have been defined");
				// First see for which tdid
				$tdq = "SELECT tdid FROM testdef LEFT JOIN class USING(cid) WHERE gid=". $gid. " AND period=". $period. " AND mid=". $sflistqr['targetmid'][$sfix]. " AND year='". $schoolyear. "' AND short_desc='". $sflistqr['short_desc'][$sfix]. "'";
				$tdqr = SA_loadquery($tdq);
				unset($tdid);
				if(isset($tdqr['tdid']))
				{
					$tdid = $tdqr['tdid'][1];
					$cidqr = SA_loadquery("SELECT cid FROM testdef WHERE tdid=". $tdid);
				}
				else
				{ // Testdef does not exit yet, so we need to create it
					// First see if we can get a cid
					$cidqr = SA_loadquery("SELECT cid FROM class LEFT JOIN sgrouplink USING(gid) WHERE sid=". $sid. " AND mid=". $sflistqr['targetmid'][$sfix]);
					if(isset($cidqr['cid']))
					{
						mysql_query("INSERT INTO testdef (short_desc,description,date,type,period,cid,year,locked) VALUES('". $sflistqr['short_desc'][$sfix]. "','". $sflistqr['description'][$sfix]. "','". date("Y-m-d"). "','". $sflistqr['targettesttype'][$sfix]. "',". $period. ",". $cidqr['cid'][1]. ",'". $schoolyear. "',1)", $userlink);
						echo(mysql_error($userlink));
						$tdid = mysql_insert_id($userlink);
					}
				}
				if(isset($tdid))
				{ // testdef is defined, now get the result using the formula
					$resqr = SA_loadquery($sflistqr['formula'][$sfix]);
					if(isset($resqr['result']))
					{ // Result has been defined
						//echo("Result from special formula = ". $resqr['result'][1]. "<BR>");
						if($resqr['result'][1] != "")
							$setresq = "REPLACE INTO testresult (tdid,sid,result,tid) VALUES(". $tdid. ",". $sid. ",'". $resqr['result'][1]. "',". $_SESSION['uid']. ")";
						else
							$setresq = "DELETE FROM testresult WHERE tdid=". $tdid. " AND sid=". $sid;
						mysql_query($setresq, $userlink);
						echo(mysql_error($userlink));
						//echo(mysql_affected_rows($userlink). " rows affected by query ". $setresq);
						// Now we might need to recalculate the target results!
					  $specialformulalock=true;	
						SA_calcGrades($sid,$cidqr['cid'][1],$period);
					  $specialformulalock=false;	
					}	
				}
			}
			else
			{ // No results have been found, we may need to remove a result...
				$tdq = "SELECT tdid,cid FROM testdef LEFT JOIN class USING(cid) WHERE gid=". $gid. " AND period=". $period. " AND mid=". $sflistqr['targetmid'][$sfix]. " AND year='". $schoolyear. "' AND short_desc='". $sflistqr['short_desc'][$sfix]. "'";
				$tdqr = SA_loadquery($tdq);
				unset($tdid);
				if(isset($tdqr['tdid']))
					$tdid = $tdqr['tdid'][1];
				if(isset($tdid))
				{
					mysql_query("DELETE FROM testresult WHERE sid=". $sid. " AND tdid=". $tdid, $userlink);
					echo(mysql_error($userlink));
					// Might need to recalculate the result for the target
					$specialformulalock=true;	
					SA_calcGrades($sid,$tdqr['cid'][1],$period);
					$specialformulalock=false;	
				}
			}
		} // End foreach formula
	} // End if applicable formulas exist
}
?>