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
// | Changenote: added facilities for lessonplan 10 sep 08                |
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
  
  // Now see which subject we deal with
  if(isset($HTTP_POST_VARS['newsubject']))
  {
    $CurrentSubject = $HTTP_POST_VARS['newsubject'];
    $_SESSION['CurrentSubject'] = $CurrentSubject;
  }
  else if(isset($_SESSION['CurrentSubject']))
    $CurrentSubject = $_SESSION['CurrentSubject'];
 
  
  $uid = intval($uid);
  
  // If we use the lessonplan option, the database might be in need of extension...
  if(isset($lessonplan) && $lessonplan==1)
  {
    $planfields = SA_loadquery("SHOW COLUMNS FROM testdef LIKE 'week'");
	if(!isset($planfields['Field'][1]))
	{ // Need to add fields for lessonplans
	  echo("Extending test definition table for lesson plan");
	  mysql_query("ALTER TABLE testdef ADD week int(2)");
	  mysql_query("ALTER TABLE testdef ADD domain text");
	  mysql_query("ALTER TABLE testdef ADD term text");
	  mysql_query("ALTER TABLE testdef ADD duration text");
	  mysql_query("ALTER TABLE testdef ADD assignments text");
	  mysql_query("ALTER TABLE testdef ADD tools text");
	}
	// See if result column exists
	$resfield = SA_loadquery("SHOW COLUMNS FROM testdef LIKE 'realised'");
	if(!isset($resfield['Field'][1]))
	{ // Need to add a result field (as part of update 1.5)
	  echo("<BR>Extending test definition table for lesson plan with realised field");
	  mysql_query("ALTER TABLE testdef ADD realised text");
	  mysql_query("INSERT INTO tt_english VALUES('Realised','Realised')");
	  mysql_query("INSERT INTO tt_nederlands VALUES('Realised','Gerealiseerd')");
	  $dtext['Realised'] = "Gerealiseerd";
	  $_SESSION['dtext']['Realised'] = "Gerealiseerd";
	}
  }

  // Get the applicable subjects in an array.
  $sql_query = "SELECT subject.shortname,class.cid,subject.mid FROM subject LEFT JOIN class USING (mid) LEFT JOIN sgroup USING (gid) LEFT JOIN (SELECT meta_subject,COUNT(mid) AS mcnt FROM subject GROUP BY meta_subject) AS mx ON(subject.mid=mx.meta_subject) WHERE active=1 AND sgroup.groupname='" .$CurrentGroup . "'";
  if($LoginType != "A")
    $sql_query .= "AND (class.tid='" . $uid . "' OR (subject.type = 'meta' AND mx.mcnt IS NULL))";

  $sql_result = mysql_query($sql_query,$userlink);
  echo(mysql_error($userlink));
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
  $subject_n = $nrows;
  // 15 dec 2008: problem detected: if CurrentSubject was set for another group and is no longer valid, no tests are displayed.
  // SOLUTION: Check if CurrentSubject is valid for this group and if not, reset it.
  $cursubvalid = false;
  foreach($subject_array['mid'] AS $chksub)
    if(isset($CurrentSubject) && $chksub == $CurrentSubject)
	  $cursubvalid = true;
  if(!$cursubvalid)
    unset($CurrentSubject); 
  if(!isset($CurrentSubject))
    $CurrentSubject = $subject_array['mid'][1];

  // Get an array with the open periods.
  $sql_query = "SELECT * FROM period WHERE status='open' ORDER BY id";
  $sql_result = mysql_query($sql_query,$userlink);
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
  $period_n = $nrows;

  // Get an array with the test types.
  $sql_query = "SELECT * FROM testtype ORDER BY type";
  $sql_result = mysql_query($sql_query,$userlink);
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
       $type_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $type_n = $nrows;
  
  // Get the number of students in the group
  $studcount = SA_loadquery("SELECT COUNT(sid) AS studs FROM sgrouplink LEFT JOIN sgroup USING(gid) WHERE active=1 AND groupname='". $CurrentGroup. "'");

  // Create a separate array with the test definitions the teacher is allowed to see
  $sql_query = "SELECT testdef.*,subject.shortname, COUNT(testresult.tdid) AS ress";
  //$sql_query .= " FROM period,sgroup,testdef LEFT JOIN class USING (cid) LEFT JOIN subject USING (mid)";
  $sql_query .= " FROM testdef LEFT JOIN period ON (testdef.period=period.id AND testdef.year=period.year)";
  $sql_query .= " LEFT JOIN class USING (cid) LEFT JOIN sgroup USING(gid) LEFT JOIN subject USING (mid)";
  $sql_query .= " LEFT JOIN testresult USING(tdid)";
  //$sql_query .= " WHERE testdef.period=period.id AND sgroup.gid=class.gid AND period.year=testdef.year";
  $sql_query .= " WHERE active=1 AND sgroup.groupname='". $CurrentGroup."' AND subject.mid=". $CurrentSubject;
  if($LoginType != "A")
    $sql_query .= " AND (class.tid='" . $uid. "' OR subject.type = 'meta') AND period.status='open'";
  $sql_query .= " AND period.year=testdef.year";
  $sql_query .= " GROUP BY testdef.tdid ORDER BY testdef.period DESC, subject.shortname, testdef.date DESC";

  $sql_result = mysql_query($sql_query,$userlink);
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
       $test_array[$fieldname][$nrows]=$fieldvalu;
     } // for $i
    } //for $r
    mysql_free_result($sql_result);
  }//If numrows != 0
  $test_n = $nrows;
  SA_closeDB();

  // First part of the page
  echo("<html><head><title>" . $dtext['tstdef_title'] . "</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>" . $dtext['tstdef_title'] . "</font><p>");
  echo '<a href="teacherpage.php">';
  echo($dtext['back_teach_page'] . "</a><br>");
    
  // Below some javascript to enable dynamic resizing of textareas
  ?>
  <SCRIPT>
  function sz(t)
  {
    a = t.value.split('\n');
    b=1;
    for (x=0;x < a.length; x++)
	{
      if (a[x].length >= t.cols) b+= Math.floor(a[x].length/t.cols);
    }
    b+= a.length;
    if (b > t.rows) t.rows = b;
  }
  </SCRIPT>
  <?PHP

  // If not administrator and not giving any classes to this group, report it and end, it's no use entering
  // test definitions as no subject could be given!
  if($subject_n == 0)
  {
    echo("<br>" . $dtext['tstdef_expl_1'] . " <b>$CurrentGroup</b> (<a href=selectgroup.php?ReturnTo=mantests.php>" . $dtext['Change'] . "</a>)<br>");
    echo("<br><b>" . $dtext['tstdef_expl_2'] . "</b></html>");
    exit;
  }
  echo("<br><div align=left>" . $dtext['tstdef_expl_3']);
  echo("<br>" . $dtext['tstdef_expl_4']);
  echo("<br>" . $dtext['tstdef_expl_5'] . " ");
  echo($dtext['tstdef_expl_6'] . "</dev><br>");

  // Show for which group current editing and allow changing the group
  echo($dtext['tstdef_expl_7'] . " <b>$CurrentGroup</b> (<a href=selectgroup.php?ReturnTo=mantests.php>" . $dtext['Change'] . "</a>)<br>");
  // Show for which subject current editing applies and allow change
  echo("<form method=post action='mantests.php' name=selsub id=selsub>". $dtext['Subject']. " : <SELECT name=newsubject onChange=\"document.selsub.submit();\">");
  for($sn=1;$sn<=$subject_n;$sn++)
  {
    echo("<OPTION value='". $subject_array['mid'][$sn]. "'");
	if($CurrentSubject == $subject_array['mid'][$sn])
	  echo(" selected");
	echo(">". $subject_array['shortname'][$sn]. "</option>");
  }
  echo("</SELECT></FORM><br>");

  // Create the heading row for the table
  echo("<table border=1 cellpadding=0>");
  echo("<tr>");
  echo("<td><center>" . $dtext['Type'] . "</td>");
  echo("<td><center>" . $dtext['Period'] . "</td>");
  echo("<td><center>" . $dtext['Date'] . "</td>");
  echo("<td><center>" . $dtext['Short'] . "</td>");
  echo("<td><center>" . $dtext['Description'] . "</td>");
  if(isset($lessonplan) && $lessonplan == 1)
  { // Add headers for fields present if lessonplan used
    echo("<td><center>". $dtext['Realised']. "</td>");
    echo("<td><center>". $dtext['Week']. "</td>");
    echo("<td><center>". $dtext['Domain']. "</td>");
    echo("<td><center>". $dtext['Term']. "</td>");
    echo("<td><center>". $dtext['Duration']. "</td>");
    echo("<td><center>". $dtext['Assignments']. "</td>");
    echo("<td><center>". $dtext['Tools']. "</td>");
  }
  echo("<td></td>");
  echo("<td></td>");
  echo("<td></td></font></tr>");

  // Create the first row in the table to add a new test definition
  echo("<tr><form method=post action='updtestdef.php' name=newtest>");
  // Add a hidden field for the cid
  for($sc=1;$sc<=$subject_n;$sc++)
    if($subject_array['mid'][$sc] == $CurrentSubject)
	  echo("<input type=hidden name=cid value=". $subject_array['cid'][$sc]. ">");
  echo("<td><center>");
  addTypeDropbox();
  echo("</td><td><center>");
  addPeriodDropbox();
  echo("</td>");
  // Add the date entry
  echo("<td><center><input type=text name=date size=10 value='" . @date('Y-m-d') . "'></td>");
  // Add the short description entry
  echo("<td><center><input type=text name=short_desc size=6></td>");
  // Add the full description entry
  //echo("<td><input type=text name=description size=20></td>");
  echo("<td><TEXTAREA name=description cols=20 rows=1 onKeyup=\"sz(this);\" onClick=\"sz(this);\"></TEXTAREA></td>");
  if(isset($lessonplan) && $lessonplan == 1)
  { // Add the extra fields for lessoonplans
    echo("<td><TEXTAREA name=realised cols=20 rows=1 onKeyup=\"sz(this);\" onClick=\"sz(this);\"></TEXTAREA></td>");
    echo("<td><input type=text name=week size=2></td>");
    echo("<td><input type=text name=domain size=6></td>");
    echo("<td><input type=text name=term size=8></td>");
    echo("<td><input type=text name=duration size=6></td>");
    echo("<td><input type=text name=assignments size=10></td>");
    echo("<td><input type=text name=tools size=10></td>");
  }
  // Add the ADD! button
  //echo("<td><center><input type=hidden name=tdid value=''><input type=submit value=" . $dtext['ADD_CAP'] . "></td></form>");
  echo("<td><center><input type=hidden name=tdid value=''><img src='PNG/action_add.png' title='". $dtext['ADD_CAP']. "' onclick='document.newtest.submit();'></td>");
  // Add two collumns wide add new for all groups in year
  echo("<td colspan=2><center>". substr($CurrentGroup,0,1). "*<input type=hidden name=tdid value=''><input type=hidden name=yearsflag value=0><img src='PNG/action_add.png' title='". $dtext['ADD_CAP']. "' onclick='add4year();'></td>");
  echo("</tr></form>");

  // Create a row in the table for every existing test defintion
  for($r=1;$r<=$test_n;$r++)
  {
    echo("<form method=post action=updtestdef.php name=ut". $r. "><tr>");
    // Put in the hidden fields for tdid and cid (although the later is obsolete!)
    echo("<td><center><input type=hidden name=tdid value=" . $test_array['tdid'][$r] .">");
    echo("<input type=hidden name=cid value=" . $test_array['cid'][$r] . ">");
    // Add the drop-down lists for the type and period
    echo("<center>");
    addTypeDropbox($test_array['type'][$r]);
    echo("</td><td><center>");
    addPeriodDropbox($test_array['period'][$r]);
    echo("</td>");
    // Add the date, short description and description as a text edit fields
    echo("<td><center><input type=text name=date size=10 value='" . $test_array['date'][$r] . "'></td>");
    echo("<td><center><input type=text name=short_desc size=6 value=\"" . $test_array['short_desc'][$r] . "\"></td>");
    //echo("<td><center><input type=text name=description size=20 value='" . $test_array['description'][$r] . "'></td>");
    echo("<td><TEXTAREA name=description cols=20 rows=1 onKeyup=\"sz(this);\" onClick=\"sz(this);\">". $test_array['description'][$r] ."</TEXTAREA></td>");
  if(isset($lessonplan) && $lessonplan == 1)
  { // Add the extra fields for lessoonplans
    echo("<td><TEXTAREA ". ((($studcount['studs'][1] > $test_array['ress'][$r]) && $test_array['ress'][$r] > 0) ? "style='background-color: #FFC0C0' " : ""). "name=realised cols=20 rows=1 onKeyup=\"sz(this);\" onClick=\"sz(this);\">". $test_array['realised'][$r] ."</TEXTAREA></td>");
    echo("<td><input type=text name=week size=2 value='" . $test_array['week'][$r] . "'></td>");
    echo("<td><input type=text name=domain size=6 value=\"" . $test_array['domain'][$r] . "\"></td>");
    echo("<td><input type=text name=term size=8 value=\"" . $test_array['term'][$r] . "\"></td>");
    echo("<td><input type=text name=duration size=6 value=\"" . $test_array['duration'][$r] . "\"></td>");
    echo("<td><input type=text name=assignments size=10 value=\"" . $test_array['assignments'][$r] . "\"></td>");
    echo("<td><input type=text name=tools size=10 value=\"" . $test_array['tools'][$r] . "\"></td>");
  }
    // Add the change, delete and results buttons
    //echo("<td><center><input type=submit value=" . $dtext['Change'] . "></td></form>");
    echo("<td><center><img src='PNG/action_check.png' title='". $dtext['Change']. "' onclick='document.ut". $r. ".submit();'></td></form>");
    echo("<form method=post action=deltestdef.php name=dt". $r. "><input type=hidden name=tdid value=" . $test_array['tdid'][$r]. ">");
    //echo("<td><center><input type=submit value=" . $dtext['Delete'] . "></td></form>");
    echo("<td><center><img src='PNG/action_delete.png' title='". $dtext['Delete']. "' onclick='document.dt". $r. ".submit();'></td></form>");
    echo("<form method=post action=editresults.php name=er". $r. "><input type=hidden name=tdid value=" . $test_array['tdid'][$r]. ">");
    //echo("<td><center><input type=submit value=" . $dtext['Results'] . "></td></form></tr>");
	if($test_array['type'][$r] != "0")
      echo("<td><center><img src='PNG/reply.png' title='". $dtext['Results']. "' onclick='document.er". $r. ".submit();'></td></form>");
	else
	  echo("<td></td></form>");
	// Testing: show # of students and results
	//echo("<TD>". $studcount['studs'][1]. "/". $test_array['ress'][$r]. "</td>");
	echo("</tr>");
  }
 // close the table
  echo("</table>");
  echo '<a href="teacherpage.php">';
  echo $dtext['back_teach_page'];
  echo '</a>';
  echo("</html>");

function addTypeDropbox($defaultDrop = '')
{
  global $type_array,$type_n;
  echo("<select name=type><option value=0></option>");
  for($tc=1;$tc<=$type_n;$tc++)
  {
    echo("<option value='" . $type_array['type'][$tc] . "'");
    if($defaultDrop == $type_array['type'][$tc])
      echo(" selected");
    echo(">" . $type_array['type'][$tc] . "</option>");
  }
}

function addPeriodDropbox($defaultDrop = '')
{
  global $period_array,$period_n;
  echo("<select name=period>");
  if($defaultDrop != '') // Need to add as it might not be valid as new period but is valid as existing!
    echo("<option value='" . $defaultDrop . "' selected>" . $defaultDrop . "</option>");
  for($tc=1;$tc<=$period_n;$tc++)
  {
    echo("<option value='" . $period_array['id'][$tc] . "'");
    if($defaultDrop == $period_array['id'][$tc])
      echo(" selected");
    echo(">" . $period_array['id'][$tc] . "</option>");
  }
}
?>

<SCRIPT>
  function add4year()
  {
    document.newtest.yearsflag.value = 1;
	document.newtest.submit();
  }
 </SCRIPT>
