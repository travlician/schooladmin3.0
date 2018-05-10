<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2015 Aim4me N.V.   (http://www.aim4me.info)       |
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
require_once("displayelements/displayelement.php");
require_once("student.php");
require_once("teacher.php");
require_once("group.php");
require_once("testdef.php");
require_once("subject.php");
require_once("subjectselector.php");
require_once("schooladmingradecalc.php"); //@grades

class Testslisted extends displayelement
{
  protected $showlocking;
  protected function add_contents()
  {
    global $userlink, $lessonplan, $yearpositioningroup;
		if(isset($_POST['copyPLTyear']))
		{ // Maybe we need to copy PLT from a previous year first
			if($_POST['copyPLTyear'] != "" && $_POST['copyPLTgroup'] != '')
			{ // Valid  params given, so ee if there is source data to copy
				$prvpltqr = inputclassbase::load_query("SELECT testdef.* FROM testdef LEFT JOIN class USING(cid) WHERE year='". $_POST['copyPLTyear']. "' AND gid=". $_POST['copyPLTgroup']. " AND date>'2000-01-01' AND mid=". $_SESSION['CurrentSubject']. " ORDER BY date");
				if(isset($prvpltqr['tdid']))
				{ // PLT items exist, now see how many years we need to shift
					$yrshift = date("Y") - substr($prvpltqr['date'][0],0,4);
					// Get my cid to put into the coied testdef
					$mycidqr = inputclassbase::load_query("SELECT cid FROM class LEFT JOIN sgroup USING(gid) WHERE groupname='". $_SESSION['CurrentGroup']. "' AND mid=". $_SESSION['CurrentSubject']);
					$mycid = $mycidqr['cid'][0];
					if($yrshift >= 0)
					{ // So we have a valid yearshift, now we copy all
						// First get current year
						$curyrqr = inputclassbase::load_query("SELECT year FROM period WHERE id=1");
						$curyr = $curyrqr['year'][0];
						$copyPLTq = "INSERT INTO testdef SELECT NULL";
						foreach($prvpltqr AS $pltfld => $dummy)
						{
							if($pltfld != "tdid")
							{
								if($pltfld == "year")
									$copyPLTq .= ",'". $curyr. "'";
								else if($pltfld == "date")
									$copyPLTq .= ",DATE_ADD(date, INTERVAL ". $yrshift. " YEAR)";
								else if($pltfld == "cid")
									$copyPLTq .= ",". $mycid;
								else if($pltfld == "realised" || $pltfld=="locked")
									$copyPLTq .= ",NULL";
								else
									$copyPLTq .= ",". $pltfld;
							}
						}
						$copyPLTq .= " FROM testdef LEFT JOIN class USING(cid) WHERE year='". $_POST['copyPLTyear']. "' AND gid=". $_POST['copyPLTgroup']. " AND date>'2000-01-01' AND mid=". $_SESSION['CurrentSubject']. " ORDER BY date";
						mysql_query($copyPLTq,$userlink);
						echo(mysql_error($userlink));
					}
					
				}				
			}
			
		}
		if(!isset($yearpositioningroup))
		{
			$yearpositioningroup=1;
		}
		
		//  Handle plan filter value
		if(!isset($_SESSION['planfilter']))
			$_SESSION['planfilter'] = 'Full_plan';
		if(isset($_POST['planfilt']))
			$_SESSION['planfilter'] = $_POST['planfilt'];
		// Signal locking is to be shown if administrator
		$me = new teacher();
		$me->load_current();
		if($me->has_role('admin'))
			$this->showlocking = TRUE;
		else
			$this->showlocking = FALSE;
			// Catch deletion of test definition -> need to delete and recalculate
		if(isset($_POST['tddelete']))
		{
			// get the period and cid for reclaculation after deletion
			$todelete = new testdef($_POST['tddelete']);
			$recalcperiod = $todelete->get_period();
			$recalccid = $todelete->get_cid();
			// get database link ID
			$userlink = inputclassbase::$dbconnection;
			mysql_query("DELETE FROM testresult WHERE tdid=". $todelete->get_id());
			echo(mysql_error());
			mysql_query("DELETE FROM testdef WHERE tdid=". $todelete->get_id());
			echo(mysql_error());
			SA_calcGradeGroup($recalccid,$recalcperiod);
		}
		// Catch shifting of LTP
		if(isset($_POST['tdshift']))
		{
			$shiftfrom = new testdef($_POST['tdshift']);
			$shiftfrom->shift_foreward();
		}
		if(isset($_POST['tdlocktoggle']))
		{
			$ltoggle = new testdef($_POST['tdlocktoggle']);
			$ltoggle->toggle_lock();
		}
			// Catch yearlayer activation
		{
			if(isset($_POST['yeartestdef']))
			{
				// Get the key for this test definition
				$key=$_SESSION['inputobjects']['tddesc0']->get_key();
				$copytestdef = new testdef($key);
				if($copytestdef->get_desc() == "" || $copytestdef->get_date() == "" || $copytestdef->get_period() == "")
				{  // DONT copy as data needs to be filles first
					echo("<SCRIPT> alert('Fill other fields first!'); </SCRIPT>");
				}
				else
				{
					// First find out which other classes are to be done
					$mid = inputclassbase::load_query("SELECT mid FROM class LEFT JOIN testdef USING(cid) WHERE tdid=". $key); // Get subject for current test definition
					if(isset($mid['mid'][0]))
					{
						$cq = "SELECT cid FROM class LEFT JOIN sgroup USING(gid) WHERE active=1 AND mid=". $mid['mid'][0]. " AND SUBSTR(groupname,". $yearpositioningroup. ",1) = '". substr($_SESSION['CurrentGroup'],$yearpositioningroup-1,1). "'";
						if(!$me->has_role("admin") && $_POST['yeartestdef'] == 1)
							$cq .= " AND tid=". $me->get_id();
							// If admin and yeartestdef = 2, set the testdef for all classes with the subject in all years (change 2/2/2015)
						if($me->has_role("admin") && $_POST['yeartestdef'] == 2)
								$cq = "SELECT cid FROM class LEFT JOIN sgroup USING(gid) WHERE active=1 AND mid=". $mid['mid'][0];
							$classeslist = inputclassbase::load_query($cq);
						if($classeslist)
						{
							foreach($classeslist['cid'] AS $cpcid)
							{
								if($lessonplan)
								{
									$itdq = "INSERT INTO testdef (short_desc,description,date,type,period,cid,year,week,domain,term,duration,assignments,tools) VALUES(";
									$itdq .= "\"". $copytestdef->get_short_desc(). "\",\"". $copytestdef->get_desc(). "\",'". inputclassbase::nldate2mysql($copytestdef->get_date()). "'";
									$itdq .= ",'". $copytestdef->get_type(). "',". $copytestdef->get_period(). ",". $cpcid. ",'". $copytestdef->get_year(). "'";
									$itdq .= ",'". $copytestdef->get_week(). "'";
									$itdq .= ",\"". $copytestdef->get_domain(). "\",\"". $copytestdef->get_term(). "\",\"". $copytestdef->get_duration(). "\"";
									$itdq .= ",\"". $copytestdef->get_assign(). "\",\"". $copytestdef->get_tools(). "\")";				    
								}
									else
								{
									$itdq = "INSERT INTO testdef (short_desc,description,date,type,period,cid,year) VALUES(\"". $copytestdef->get_short_desc(). "\"";
									$itdq .= ",\"". $copytestdef->get_desc(). "\",'". inputclassbase::nldate2mysql($copytestdef->get_date()). "','". $copytestdef->get_type(). "'";
									$itdq .= ",". $copytestdef->get_period(). ",". $cpcid. ",'". $copytestdef->get_year(). "')";
								}
								if($cpcid != $copytestdef->get_cid())
								{
									mysql_query($itdq);
										//echo("Query done=". $itdq);
									echo(mysql_error());
								}
							}
						}
						//echo("Year layer copy! key=". $key);
					}
				}
			}
		}
  }
  
  public function show_contents()
  {
    global $lessonplan,$CurrentSubject,$yearpositioningroup;
		if(!isset($yearpositioningroup))
			$yearpositioningroup=1;
		$dtext = $_SESSION['dtext'];
		if(isset($_POST['sselectfld']))
			echo("<SCRIPT> document.getElementById('menudiv').style.marginLeft=4-document.getElementById('menudiv').offsetWidth; clearTimeout(starttimeoutmenudiv); </SCRIPT>");
    // Now see which subject we deal with
    if(isset($_POST['newsubject']))
    {
      $CurrentSubject = $_POST['newsubject'];
      $_SESSION['CurrentSubject'] = $CurrentSubject;
	  // Let the menu go right away!
    }
    else if(isset($_SESSION['CurrentSubject']))
      $CurrentSubject = $_SESSION['CurrentSubject'];
 
    $uid = $_SESSION['uid'];
    $CurrentUID = $uid;  
    $uid = intval($uid);
    $CurrentGroup = $_SESSION['CurrentGroup'];
  
    // If we use the lessonplan option, the database might be in need of extension...
    if(isset($lessonplan) && $lessonplan==1)
    {
      $planfields = inputclassbase::load_query("SHOW COLUMNS FROM testdef LIKE 'week'");
			if(!isset($planfields['Field'][0]))
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
			$resfield = inputclassbase::load_query("SHOW COLUMNS FROM testdef LIKE 'realised'");
			if(!isset($resfield['Field'][0]))
			{ // Need to add a result field (as part of update 1.5)
				echo("<BR>Extending test definition table for lesson plan with realised field");
				mysql_query("ALTER TABLE testdef ADD realised text");
				mysql_query("INSERT INTO tt_english VALUES('Realised','Realised')");
				mysql_query("INSERT INTO tt_nederlands VALUES('Realised','Gerealiseerd')");
				$dtext['Realised'] = "Gerealiseerd";
				$_SESSION['dtext']['Realised'] = "Gerealiseerd";
			}
    }
		// See if the lock/unlock column exists and if not, create and fill it!
		$lockfield = inputclassbase::load_query("SHOW COLUMNS FROM testdef LIKE 'locked'");
		if(!isset($lockfield['Field'][0]))
		{ // Need to add locked field and fill it as required
			mysql_query("ALTER TABLE testdef ADD locked int(1)");
			mysql_query("UPDATE testdef LEFT JOIN period ON(period=id) SET locked=1 WHERE period.year <> testdef.year OR status <> 'open'");	  
		}

    // Get the number of students in the group
    //$studcount = inputclassbase::load_query("SELECT COUNT(sgrouplink.sid) AS studs FROM sgrouplink LEFT JOIN sgroup USING(gid) LEFT JOIN student USING(sid) WHERE active=1 AND firstname IS NOT NULL AND groupname='". $CurrentGroup. "'");

    echo("<font size=+2><center>" . $dtext['tstdef_title'] . " ". $dtext['group']. " ". $_SESSION['CurrentGroup']. "</font><p>");
    // If not administrator and not giving any classes to this group, report it and end, it's no use entering
    // test definitions as no subject could be given!
		$subjectlist = subject::subject_list();
	
    if(count($subjectlist) == 0)
    {
      echo("<br>" . $dtext['tstdef_expl_1'] . " <b>$CurrentGroup</b><br>");
      echo("<br><b>" . $dtext['tstdef_expl_2'] . "</b></html>");
      exit;
    }
    echo("<br><div align=left>" . $dtext['tstdef_expl_3']);
    echo("<br>" . $dtext['tstdef_expl_4']);
    echo("<br>" . $dtext['tstdef_expl_5'] . " ");
    echo($dtext['tstdef_expl_6'] . "</div><br>");

    // Show for which subject current editing applies and allow change
		$subselbox = new subjectselector();
		$subselbox->show();

		// Show a selector for full plan (default), only tests or only lessons
		echo("<FORM METHOD=POST ACTION='". $_SERVER['REQUEST_URI']. "' ID=planfilter NAME=planfilter>
				<SELECT name=planfilt onChange='document.planfilter.submit()'>
				<OPTION value='Full_plan' ". ($_SESSION['planfilter'] == 'Full_plan' ? ' selected' : ''). ">". $dtext['Full_plan']. "</OPTION>
				<OPTION value='Lesson_plan' ". ($_SESSION['planfilter'] == 'Lesson_plan' ? ' selected' : ''). ">". $dtext['Lesson_plan']. "</OPTION>
				<OPTION value='Test_plan' ". ($_SESSION['planfilter'] == 'Test_plan' ? ' selected' : ''). ">". $dtext['Test_plan']. "</OPTION>
				</SELECT></FORM>");
		
		// Get a list of groups that this teacher teaches (all for admin) in the same year with the same subject
		$me = new teacher($_SESSION['uid']);
		$mygq = "SELECT DISTINCT groupname from `class` LEFT JOIN sgroup USING(gid) 
						 WHERE active=1 AND mid=". $_SESSION['CurrentSubject']. " AND
					SUBSTR(groupname,". $yearpositioningroup. ",1) = '". substr($CurrentGroup,$yearpositioningroup-1,1). "'";
		if(!$me->has_role("admin"))
			$mygq .= " AND tid=". $_SESSION['uid'];
		$mygrps = inputclassbase::load_query($mygq);
		$mygnames = "";
		if(isset($mygrps['groupname']))
			foreach($mygrps['groupname'] AS $gnm)
				$mygnames .= "<BR>". $gnm;
		
		// Get a list off all groups in the same year with the same subject
		$allgq = "SELECT DISTINCT groupname from `class` LEFT JOIN sgroup USING(gid) 
						 WHERE active=1 AND mid=". $_SESSION['CurrentSubject']. " AND
					SUBSTR(groupname,". $yearpositioningroup. ",1) = '". substr($CurrentGroup,($yearpositioningroup-1),1). "'";
		$allgqr = inputclassbase::load_query($allgq);
		$allgnames = "";
		if(isset($allgqr['groupname']))
			foreach($allgqr['groupname'] AS $gnm)
				$allgnames .= "<BR>". $gnm;
		// In case of admin, need to just show *, referrring to all groups in all years (changed 2/2/2014)
		if($me->has_role("admin"))
			$allgnames = "*";
		
    // Create the heading row for the table
    echo("<table border=1 cellpadding=0>");
    echo("<tr>");
    echo("<td><center>" . $dtext['Description'] . "</td>");
    echo("<td><center>" . $dtext['Date'] . "</td>");
    echo("<td><center>" . $dtext['Period'] . "</td>");
    echo("<td><center>" . $dtext['Short'] . "</td>");
    echo("<td><center>" . $dtext['Type'] . "</td>");
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
    echo("<td COLSPAN=2></td></font></tr>");

    // Create the first row in the table to add a new test definition
		$newtd = new testdef();
    echo("<tr>");
    echo("<td>");
		$newtd->edit_desc(NULL,NULL,$me->has_role('admin'));
    echo("</td><td>");
    // Add the date entry
		$newtd->edit_date();
    echo("</td><td><center>");
		$newtd->edit_period();
    // Add the short description entry
    echo("</td><td>");
		$newtd->edit_short_desc();
    // Add the full description entry
    echo("</td><td>");
		$newtd->edit_type();
    if(isset($lessonplan) && $lessonplan == 1)
    { // Add the extra fields for lessoonplans
      echo("<td>");
			$newtd->edit_realised();
			echo("</td><td>");
			$newtd->edit_week();
			echo("</td><td>");
			$newtd->edit_domain();
			echo("</td><td>");
			$newtd->edit_term();
			echo("</td><td>");
			$newtd->edit_duration();
			echo("</td><td>");
			$newtd->edit_assign();
			echo("</td><td>");
			$newtd->edit_tools();
       echo("</td>");
    }
    // Add three collumns , normal add and add for all own groups in year and add for all groups in year
		//echo("<td><img src='PNG/action_add.png' title='". $dtext['ADD_CAP']. "' onclick=\"setTimeout('location.reload();',500);\"></td>");
		echo("<td><img src='PNG/action_add.png' title='". $dtext['ADD_CAP']. "' onclick=\"setTimeout('window.location=document.location',500);\"></td>");
		//    echo("<td><center>". substr($CurrentGroup,0,1). "*<input type=hidden name=tdid value=''><img src='PNG/action_add.png' title='". $dtext['ADD_CAP']. "' onclick=\"setTimeout('document.yeartestdef.submit()',500);\"></td>");
		//    echo("<td><center>". substr($CurrentGroup,0,1). "**<input type=hidden name=tdid value=''><img src='PNG/action_add.png' title='". $dtext['ADD_CAP']. "' onclick=\"setTimeout('document.fullyeartestdef.submit()',500);\"></td>");
    echo("<td><center><input type=hidden name=tdid value=''><img src='PNG/action_add.png' title='". $dtext['ADD_CAP']. "' onclick=\"setTimeout('document.yeartestdef.submit()',500);\">". $mygnames. "</td>");
    echo("<td><center><input type=hidden name=tdid value=''><img src='PNG/action_add.png' title='". $dtext['ADD_CAP']. "' onclick=\"setTimeout('document.fullyeartestdef.submit()',500);\">". $allgnames. "</td>");
    echo("</tr></form>");

    // Create a row in the table for every existing test defintion
		// First get the list
		$tests = testdef::testdef_list();
		$altrow = true;
		if(isset($tests))
		{
			foreach($tests AS $tdef)
			{
				if(($tdef->get_type() != '' && $_SESSION['planfilter'] != 'Lesson_plan') || 
				 ($tdef->get_type() == '' && $_SESSION['planfilter'] != 'Test_plan'))
				{
					echo("<tr". ($altrow ? ' class=altbg' : ''). ">");
					echo("<td>");
					$tdef->edit_desc(NULL,NULL,$me->has_role('admin'));
					echo("</td><td><center>");
					$noadminlock = !$tdef->get_admindefined() || $me->has_role('admin');
					if($tdef->may_edit() && $noadminlock)
						$tdef->edit_date();
					else
						echo($tdef->get_date());
					echo("</td><td><center>");
					// Add the date entry
					if($tdef->may_edit() && $noadminlock)
						$tdef->edit_period();
					else
						echo($tdef->get_period());
					// Add the short description entry
					echo("</td><td>");
					if($tdef->may_edit() && $noadminlock)
						$tdef->edit_short_desc();
					else
						echo($tdef->get_short_desc());
					// Add the full description entry
					echo("</td><td><center>");
					if($tdef->may_edit() && $noadminlock)
						$tdef->edit_type();
					else
						echo($tdef->get_type());
					if(isset($lessonplan) && $lessonplan == 1)
					{ // Add the extra fields for lessoonplans
						echo("<td>");
						$tdef->edit_realised();
						echo("</td><td><center>");
						if($tdef->may_edit() && $noadminlock)
						  $tdef->edit_week();
						else
						  echo($tdef->get_week());
						echo("</td><td>");
						$tdef->edit_domain();
						echo("</td><td>");
						$tdef->edit_term();
						echo("</td><td>");
						$tdef->edit_duration();
						echo("</td><td>");
						$tdef->edit_assign();
						echo("</td><td>");
						$tdef->edit_tools();
						echo("</td>");
					}
					// Add the delete, results and shift foreward buttons
					$may_edit = $tdef->may_edit() == TRUE;
					echo("<form method=post action=". $_SERVER['REQUEST_URI']. " name=dt". $tdef->get_id(). "><input type=hidden name=tddelete value=" . $tdef->get_id(). ">");
					if($may_edit && $noadminlock)
						echo("<td><center><img src='PNG/action_delete.png' title='". $dtext['Delete']. "' onclick='if(confirm(\"". $dtext['tstdef_expl_6']. "\")) { document.dt". $tdef->get_id(). ".submit(); }'></td>");
					else
						echo("<td>&nbsp;</td></form>");
					echo("</form>");
					echo("<form method=post action=". $_SERVER['REQUEST_URI']. " name=er". $tdef->get_id(). "><input type=hidden name=tdresults value=" . $tdef->get_id(). ">");
					if($may_edit && $tdef->get_type() != "")
						echo("<td><center><img src='PNG/reply.png' title='". $dtext['Results']. "' onclick='document.er". $tdef->get_id(). ".submit();'></td></form>");
					else
						echo("<td>&nbsp;</td></form>");
					echo("<form method=post action=". $_SERVER['REQUEST_URI']. " name=sf". $tdef->get_id(). "><input type=hidden name=tdshift value=" . $tdef->get_id(). "></form>");
					if($this->showlocking)
						echo("<form method=post action=". $_SERVER['REQUEST_URI']. " name=tl". $tdef->get_id(). "><input type=hidden name=tdlocktoggle value=" . $tdef->get_id(). "></form>");
					if($may_edit && $noadminlock)
					{
						echo("<td><center><img src='PNG/arrow_next.png' title='". $dtext['ShiftLater']. "' onclick='document.sf". $tdef->get_id(). ".submit();'>");
						if($this->showlocking)
						echo("<img src='PNG/unlock.png' onclick='document.tl". $tdef->get_id(). ".submit();'>");		
						echo("</td>");
					}
					else
					{
						echo("<td>");
						if($this->showlocking)
						echo("<img src='PNG/login.png' onclick='document.tl". $tdef->get_id(). ".submit();'>");
						else
						echo("&nbsp;");		  
						echo("</td>");
					}
					echo("</tr>");
					$altrow = !$altrow;
				}
      }
		}
    // close the table
    echo("</table>");
	// Add a form to send request for yearlayer activation
	echo("<FORM METHOD=POST action=". $_SERVER['REQUEST_URI']. " name=yeartestdef><input type=hidden name=yeartestdef value=1></FORM>");
	echo("<FORM METHOD=POST action=". $_SERVER['REQUEST_URI']. " name=fullyeartestdef><input type=hidden name=yeartestdef value=2></FORM>");
	// Show the form to copy the PLT if still in the first period.
	$perverifqr = inputclassbase::load_query("SELECT id FROM period WHERE enddate > NOW()");
	if(isset($perverifqr['id'][0]) && $perverifqr['id'][0] == 1)
	{
		//. Get a list of years 
		$yearlstqr = inputclassbase::load_query("SELECT DISTINCT year FROM testdef LEFT JOIN class USING(cid) WHERE mid=". $_SESSION['CurrentSubject']. " ORDER BY year");
		$grplstqr = inputclassbase::load_query("SELECT DISTINCT gid,groupname FROM testdef LEFT JOIN class USING(cid) LEFT JOIN sgroup USING(gid) WHERE mid=". $_SESSION['CurrentSubject']);
		echo("<BR><BR><FORM METHOD=POST>". $dtext['copy_PLT']. " ". $dtext['Year']. " <SELECT NAME=copyPLTyear><OPTION VALUE=''> </OPTION>");
		if(isset($yearlstqr['year']))
		{
			foreach($yearlstqr['year'] AS $lstyr)
				echo("<OPTION VALUE='". $lstyr. "'>". $lstyr. "</OPTION>");
		}
		echo("</SELECT>". $dtext['group']. " <SELECT NAME=copyPLTgroup><OPTION VALUE=''> </OPTION>");
		if(isset($grplstqr['gid']))
			foreach($grplstqr['gid'] AS $gix => $gid)
				echo("<OPTION VALUE='". $gid. "'>". $grplstqr['groupname'][$gix]. "</option>");
		
		echo("</SELECT><INPUT TYPE=SUBMIT VALUE='". $dtext['copy_PLT']. "'></FORM>");
	}
	// Define an array in javascript containing open periods start and end dates
	$perdates = inputclassbase::load_query("SELECT * FROM period WHERE status='open' ORDER BY startdate");
	if(isset($perdates))
	{
	  echo("<SCRIPT> var perstart = new Array(); var perend = new Array();");
	  foreach($perdates['id'] AS $pdix => $perid)
	  {
	    echo(" perstart[". $perid. "] = '". $perdates['startdate'][$pdix]. "';");
	    echo(" perend[". $perid. "] = '". $perdates['enddate'][$pdix]. "';");
	  }
	  echo("</SCRIPT>");
	}
	require_once("setweek.js");
    echo("</html>");
  }
}
?>
