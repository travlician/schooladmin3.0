<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2016 Aim4me N.V.   (http://www.aim4me.info)       |
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
require_once("report.php");
require_once("group.php");
require_once("studentsorter.php");

class Reports extends displayelement
{
  private $studlist;
  private $uploadresult;
  protected function add_contents()
  {
    $this->studlist = student::student_list();
		if(isset($_POST['delete']))
			mysql_query("DELETE FROM reports WHERE rid=". $_POST['delete']);
		if(isset($_POST['function']) && $_POST['function'] == "reportfile")
		{ // We have a file upload!
			if($_POST['edit'] == 0)
			{ // It's a new report!
				$anobj = $_SESSION['inputobjects']['repprot0'];
				$test = new inputclass_textfield("x",20);
				$curkey = $anobj->get_key();
				if($curkey == 0)
				{ // New record and nothing set so far
					mysql_query("INSERT INTO reports (sid,tid,type) VALUES(". $_POST['sid']. ",". $_SESSION['uid']. ",'". (isset($_POST['grouptype']) ? "X" : "F"). "')");
					$newkey = mysql_insert_id();
					$_POST['edit'] = $newkey;
				}
				else // Record already created, set new key
					$_POST['edit'] = $curkey;		
			}
			if(isset($_FILES['reportfile']))
			{				
				$dtext = $_SESSION['dtext'];
				global $reportspath;
				$newfilename = "Report" .$_POST['edit'];
				// Copy the extension from the tmp_file!
				$extension = (strstr($_FILES['reportfile']['name'],'.')) ? @strstr($_FILES['reportfile']['name'],'.') : '.file';
				$newfilename .= $extension;
				// Put the new filename in the database
				$sql_query = "UPDATE reports SET content='" . $newfilename . "',type='". (isset($_POST['grouptype']) ? "X" : "F"). "' WHERE rid=" . $_POST['edit'];
				mysql_query($sql_query);
				// Prepend the directory name as specified in the configuration.
				$newfilename = $reportspath . $newfilename;
				$this->uploadresult = move_uploaded_file($_FILES['reportfile']['tmp_name'],$newfilename);
				if(function_exists("report_hook"))
					report_hook($_POST['edit']);
			}
		}
  }
  
  public function show_contents()
  {
    global $userlink;
    if(isset($_POST['edit']) || isset($_POST['view']))
		{ // Care table should exist and report is edited or viewed, mark for this teacher and student combi last read date
			// First extract sid from report (if present)
      mysql_query("CREATE TABLE IF NOT EXISTS `lastreportaccess`  (`sid` int(11) unsigned NOT NULL, `tid` int(11) unsigned, `lastaccess` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (`sid`,`tid`),  UNIQUE KEY `sidtid` (`sid`,`tid`) ) ENGINE=MyISAM", $userlink);
			echo(mysql_error());
			$reportsidqr = inputclassbase::load_query("SELECT sid FROM reports WHERE sid IS NOT NULL AND rid=". (isset($_POST['edit']) ? $_POST['edit'] : $_POST['view']));
			if(isset($reportsidqr['sid'][0]))
			{ // So set last read data in database
				mysql_query("REPLACE INTO lastreportaccess (sid,tid) VALUES(". $reportsidqr['sid'][0]. ",". $_SESSION['uid']. ")", $userlink);
			echo(mysql_error());
			}
		}
    if(isset($_POST['edit']))
		{
			$this->edit_report($_POST['edit']);
		}
		else if(isset($_POST['view']))
		{
			$this->view_report($_POST['view']);
		}
		else if(isset($_POST['student']))
		{
			if(!isset($_POST['repcat']) || $_POST['repcat'] == 0)
				$this->show_student($_POST['student']);
			else
				$this->view_student_cat(new student($_POST['student']),$_POST['repcat']);
		}
		else if(isset($_POST['firstname']))
			$this->search_student($_POST['firstname'],$_POST['lastname']);
		else if(isset($_POST['grpcat']))
			$this->view_group_cat($_POST['grpcat']);
		else
			$this->show_group();
  }
  
  private function show_group()
  {
		global $showallreports;
    $dtext = $_SESSION['dtext'];
		$I = new teacher();
		$I->load_current();
		$group = new group();
		$group->load_current();
	
    echo("<font size=+2>" . $dtext['repgrp_title'] . " ". $_SESSION['CurrentGroup']. "</font><p>");
    echo("<div align=left>" . $dtext['repgrp_expl_1'] . "</dev><br>");

		// Allow counseler and administrator to get a full view of reposts on a student to search
		if($I->has_role("counsel") || $I->has_role("admin"))
		{
			echo("<FORM METHOD=POST ACTION='". $_SERVER['REQUEST_URI']. "'>");
			echo("<H2>". $dtext['studsearch_title']. "</H2><label>". $dtext['Firstname']. ": </label><input type=text size=30 name=firstname id=firstname>
						<label>". $dtext['Lastname']. ": </label><input type=text size=30 name=lastname id=lastname>
				<input type=submit value='". $dtext['Find_it']. "'>");
			echo("</FORM><BR><BR>");
		}
	
		// See if groups come first or last
		$repseqqr = inputclassbase::load_query("SELECT repsequence FROM teacher WHERE tid=". $_SESSION['uid']);
		$repseq = $repseqqr['repsequence'][0];
		if($repseq == 'G')
		{
			$replist = report::report_group_list(NULL,$I,$I->has_role("admin") || $I->has_role("counsel") || (isset($showallreports) && $showallreports) ? '0000-00-00' : NULL);
			$this->report_list($replist,$I);
			// Show the categories with reports but only if there are any
			$this->report_cat_list($I,$I->has_role("admin") || $I->has_role("counsel") || (isset($showallreports) && $showallreports) ? '0000-00-00' : NULL);
			// Create a button for a new report
			// Insert the row for a new report
			echo("<form method=post action=". $_SERVER['REQUEST_URI']. "><input type=hidden name=edit value=0>");
			echo("<input type=hidden name=gid value=". $group->get_id(). ">");
			echo("<input type=submit value='" . $dtext['Crea_rep_4grp'] . "'></form><BR>");
		}
		// Enable selection of student sorting
		$ssortbox = new studentsorter();
		$ssortbox->show();

		// Now create a table with all students in the group to enable to go to their reports
		// Create the heading row for the table
		if(isset($this->studlist))
		{
			echo("<table border=1 cellpadding=0>");
			echo("<tr>");
			$fields = student::get_list_headers();
			foreach($fields AS $fieldname)
			{
				echo("<th><center>". $fieldname. "</th>");
			}
	
			echo("<th><center>" . $dtext['Go_reps'] . "</th><th>". $dtext['My_reports']. "</th><th>". $dtext['ReportCats']. "</th>");  
			echo("</tr>");

				// Create a row in the table for every existing student in the group
			$altrow = false;
			foreach($this->studlist AS $stud)
			{
				if($stud <> null)
				{
					echo("<tr". ($altrow ? " class=altbg" : ""). "><form method=post action=". $_SERVER['REQUEST_URI']. " name=repsview". $stud->get_id(). " id=repsview". $stud->get_id(). ">");
					$sdata = $stud->get_list_data();
					foreach($sdata AS $stdata)
						echo("<TD>". $stdata. "</TD>");
					// Add the Goto button
					echo("<td><center><input type=hidden name=student value=" . $stud->get_id() ."><input type=hidden id=repcat". $stud->get_id(). " name=repcat value=0><img src=PNG/search.png onClick='document.repsview". $stud->get_id(). ".submit();'></td><td>". $stud->get_reportcount($I->has_role("admin") || $I->has_role("counsel") || (isset($showallreports) && $showallreports) ? '0000-00-00' : NULL). "</td><td>");
					// List used report catagories with number of items in it
					$catlist = reportcategory::list_categories(true);
					if(isset($catlist))
					{
						$catstr = "";
						foreach($catlist AS $catobj)
						{
							$curcatcnt = $stud->get_reportcount($I->has_role("admin") || $I->has_role("counsel") || (isset($showallreports) && $showallreports) ? '0000-00-00' : NULL,$catobj->get_id());
							if($curcatcnt > 0)
								$catstr .= ",<a href=# onClick=viewrepcat(". $stud->get_id(). ",". $catobj->get_id(). ")>". $catobj->get_name(). "(". $curcatcnt. ")</a>";
						}
						if($catstr != "")
							$catstr = substr($catstr,1);
						echo($catstr);
					}
					echo("</td></form></tr>");
					$altrow = !$altrow;
				}
				else
				{
					echo("<TR><TD COLSPAN=". (count($sdata)+2). ">&nbsp;</td></tr>");
				}
			}
			echo("<SCRIPT> function viewrepcat(studid,catid) { document.getElementById('repcat'+studid).value=catid; document.getElementById('repsview'+studid).submit(); } </SCRIPT>");
			echo("</table>");
		}
		if($repseq == 'S')
		{
			echo("<BR><BR>");
			$replist = report::report_group_list(NULL,$I,$I->has_role("admin") || $I->has_role("counsel") || (isset($showallreports) && $showallreports) ? '0000-00-00' : NULL);
			$this->report_list($replist,$I);
			// Show the categories with reports but only if there are any
			$this->report_cat_list($I,$I->has_role("admin") || $I->has_role("counsel") || (isset($showallreports) && $showallreports) ? '0000-00-00' : NULL);
			// Create a button for a new report
			// Insert the row for a new report
			echo("<form method=post action=". $_SERVER['REQUEST_URI']. "><input type=hidden name=edit value=0>");
			echo("<input type=hidden name=gid value=". $group->get_id(). ">");
			echo("<input type=submit value='" . $dtext['Crea_rep_4grp'] . "'></form><BR>");
		}
		
		// Allow teacher some preferences:
		$defrepseqfld = new inputclass_listfield("teacherrepseq","SELECT 'G' AS id,'". $dtext['ShowGroupFirst']. "' AS tekst UNION SELECT 'S','". $dtext['ShowStudentsFirst']. "'",NULL,"repsequence","teacher",$I->get_id(),"tid",NULL,"datahandler.php");
		echo("<BR>". $dtext['ReportSequence']. " : ");
		$defrepseqfld->echo_html();

		$defrepprotfld = new inputclass_listfield("teacherrepprot",report::get_protect_query(),NULL,"defrepaccess","teacher",$I->get_id(),"tid",NULL,"datahandler.php");
		echo("<BR>". $dtext['DefRepAccess']. " : ");
		$defrepprotfld->echo_html();
  }
  
  private function show_student($sid)
  {
		global $showallreports;
    $dtext = $_SESSION['dtext'];
	$I = new teacher();
	$I->load_current();
	$stud = new student($sid);
	

    echo("<font size=+2>" . $dtext['repstu_title'] . "</font><p>");
    echo("<br><div align=left>" . $dtext['repstu_expl_1'] . "</dev><br>");

    // Show for which student
    echo($dtext['repstu_expl_2'] . " <b>");
    echo($stud->get_name());
    echo("</b><br><br>");

	$replist = report::report_student_list($stud,$I,$I->has_role("admin") || $I->has_role("counsel") || (isset($showallreports) && $showallreports) ? '0000-00-00' : NULL);
    $this->report_list($replist,$I);
    // Create a button for a new report
    // Insert the row for a new report
    echo("<form method=post action=". $_SERVER['REQUEST_URI']. "><input type=hidden name=edit value=0>");
    echo("<input type=hidden name=sid value=". $stud->get_id(). ">");
    echo("<input type=submit value='" . $dtext['Crea_rep_4stu'] . "'></form>");
  }
  
  private function report_list($replist,$I)
  {
		global $showallreports;
    $dtext = $_SESSION['dtext'];
    if(isset($replist))
    {
      // Create the heading row for the table
      echo("<table border=1 cellpadding=0>");
      echo("<tr><td><center>" . $dtext['Author'] . "</td>");
      echo("<td><center>" . $dtext['Date'] . "</td>");
      echo("<td><center>" . $dtext['L_update'] . "</td>");
      echo("<td><center>" . $dtext['Summary'] . "</td>");
      echo("<td>&nbsp;</td>");
      echo("<td>&nbsp;</td>");
      echo("<td>&nbsp;</td></font></tr>");
    }
    else
	{
	  if(isset($_POST['student']))
        echo($dtext['no_rep_4stu'] . "<br><br>");
	  else
        echo($dtext['No_rep_4grp'] . "<br><br>");
    }
    // Create a row in the table for every existing report
	$altrow = false;
	if(isset($replist))
    foreach($replist AS $arep)
    {
      echo("<tr". ($altrow ? " class=altbg" : ""). "><form method=post action=". $_SERVER['REQUEST_URI']. " name=vr". $arep->get_id(). ">");
      // Put in the hidden field for report id and put the name of the teacher that created the report
      echo("<td><center><input type=hidden name=view value=" . $arep->get_id() .">");
      echo($arep->get_teacher()->get_username(). "</td>");
      // Add date, last update and summary fields
      echo("<td><center>" . $arep->get_date() . "</td>");
      echo("<td><center>" . $arep->get_last_update() . "</td>");
      echo("<td>" . $arep->get_summary() . "</td>");
      // Add the View button
      echo("<td><center><img src='PNG/search.png' title='". $dtext['View']. "' onclick='document.vr". $arep->get_id(). ".submit();'></td></form>");
      // Add the Edit & delete buttons (only if this theacher is the creator or counseller
      if($I->has_role("admin") || $arep->get_teacher()->get_id() == $I->get_id())
      {
        echo("<form method=post action=". $_SERVER['REQUEST_URI']. " name=er". $arep->get_id(). "><input type=hidden name=edit value=");
        echo($arep->get_id());
        echo("><td><center><img src='PNG/reply.png' title='". $dtext['Edit']. "' onclick='document.er". $arep->get_id(). ".submit();'></td></form>");
        echo("<form method=post action=". $_SERVER['REQUEST_URI']. " name=dr". $arep->get_id(). "><input type=hidden name=delete value=");
        echo($arep->get_id());
        echo("><td><center><img src='PNG/action_delete.png' title='". $dtext['Delete']. "' onclick='if(confirm(\"". $dtext['confirm_delete']. "\")) { document.dr". $arep->get_id(). ".submit(); }'></td></form></tr>");
      }
      else
      {	// create two empty cells in the table because user has no access
        echo("<td>&nbsp;</td>");
        echo("<td>&nbsp;</td>");
        echo("</tr>");
      }   
	  $altrow = !$altrow;
    }
    echo("</table>");
  }
  
	private function report_cat_list($I,$showall)
  {
    $dtext = $_SESSION['dtext'];
		$repcats = reportcategory::list_categories(true);
		if(isset($repcats))
		{
			$catstr = "";
			foreach($repcats AS $repcatobj)
			{
				$replist = report::report_group_list(NULL,$I,$I->has_role("admin") || $I->has_role("counsel") || (isset($showallreports) && $showallreports) ? '0000-00-00' : NULL,$repcatobj->get_id());
				if(isset($replist) && count($replist) > 0)
				  $catstr .= ",<a href=# onClick=viewgrpcat(". $repcatobj->get_id(). ")>". $repcatobj->get_name(). "(". count($replist). ")</a>";
			}
		}
		if(isset($catstr) && $catstr != "")
		{
			// Script and form to switch to view of group category reports
			echo("<FORM METHOD=POST ID=grpcatv><INPUT TYPE=HIDDEN ID=grpcat NAME=grpcat VALUE=0></FORM>");
			echo("<SCRIPT> function viewgrpcat(cat) { document.getElementById('grpcat').value=cat; document.getElementById('grpcatv').submit(); } </script>");
			echo("<b>". $dtext['ReportCats']. "</b> : ". substr($catstr,1));
		}
  }
  
  private function view_report($repid)
  {
		global $showallreports;
    $dtext = $_SESSION['dtext'];
    $report = new report($repid);
    echo($dtext['vrep_4'] . " <b>");
    if($report->get_type() == "F" || $report->get_type() == "T")
      echo($report->get_subject()->get_name());
    else
      echo($dtext['group'] . " " . $report->get_subject()->get_groupname());
    echo("</b><br>");

    echo($dtext['vrep_expl_3'] . ": " . $report->get_date() . "<br>");
		// Show the category
    echo("<br><b>" . $dtext['Category'] . ":</b> ". $report->get_category() . "<br>");
		
    // Add the summary text box
    echo("<br><b>" . $dtext['Summary'] . ":</b><br>");
    echo("<pre>" . $report->get_summary() . "</pre><br>");

    if($report->get_type() == "F" || $report->get_type() == "X")
    {
      echo($dtext['vrep_expl_4'] . "<br>");
      echo("<a href=getreport.php?". $report->get_id(). ">" . $dtext['vrep_expl_5'] . "</a>");
      // Allow the download link to work by registering and setting report id!
      $_SESSION['rid'] = $report->get_id();
    }
    else
    {
      echo("<br><b>" . $dtext['Content'] . ":</b><br>");
      echo(nl2br($report->get_contents()) . "<br><br>");
    }
	
		// Add a link to return to the student's overview
		if($report->get_type() == "F" || $report->get_type() == "T")
		{
			echo("<FORM NAME=backstu ID=backstu METHOD=POST ACTION=". $_SERVER['REQUEST_URI']. "><INPUT TYPE=HIDDEN NAME=student VALUE=". $report->get_subject()->get_id(). ">");
			echo("<a href='#' onClick='document.getElementById(\"backstu\").submit();'>". $report->get_subject()->get_name(). "</a>");
		}	
  }
  
  private function view_student_cat($student,$cat)
  {
		global $showallreports;
    $dtext = $_SESSION['dtext'];
		$I = new teacher();
		$I->load_current();
		$catobj = new reportcategory($cat);
    echo($dtext['vrep_4'] . " <b>". $student->get_name(). "</b> ". $dtext['for']. " ". $dtext['Category']. " <b>". $catobj->get_name(). "</b><br>");
		// Get a list of reports for the student in this category
		$replist = report::report_student_list($student, $I,$I->has_role("admin") || $I->has_role("counsel") || (isset($showallreports) && $showallreports) ? '0000-00-00' : NULL, $cat);
		if(isset($replist))
		 foreach($replist AS $report)
		 {
			echo($dtext['vrep_expl_3'] . ": " . $report->get_date() . "<br>");
			
			// Add the summary text box
			echo("<br><b>" . $dtext['Summary'] . ":</b><br>");
			echo("<pre>" . $report->get_summary() . "</pre><br>");

			if($report->get_type() == "F" || $report->get_type() == "X")
			{
				echo($dtext['vrep_expl_4'] . "<br>");
				echo("<a href=getreport.php?". $report->get_id(). ">" . $dtext['vrep_expl_5'] . "</a>");
				// Allow the download link to work by registering and setting report id!
				$_SESSION['rid'] = $report->get_id();
			}
			else
			{
				echo("<br><b>" . $dtext['Content'] . ":</b><br>");
				echo(nl2br($report->get_contents()) . "<br><br>");
			}
	
		 }
		echo("<FORM NAME=backstu ID=backstu METHOD=POST ACTION=". $_SERVER['REQUEST_URI']. "><INPUT TYPE=HIDDEN NAME=student VALUE=". $report->get_subject()->get_id(). ">");
		echo("<a href='#' onClick='document.getElementById(\"backstu\").submit();'>". $report->get_subject()->get_name(). "</a>");
	}	

private function view_group_cat($cat)
  {
		global $showallreports;
    $dtext = $_SESSION['dtext'];
		$I = new teacher();
		$I->load_current();
		$group = new group();
		$group->load_current();
		$catobj = new reportcategory($cat);
    echo($dtext['repgrp_title'] . " <b>". $group->get_groupname(). "</b> ". $dtext['for']. " ". $dtext['Category']. " <b>". $catobj->get_name(). "</b><br>");
		// Get a list of reports for the group in this category
		$replist = report::report_group_list($group, $I,$I->has_role("admin") || $I->has_role("counsel") || (isset($showallreports) && $showallreports) ? '0000-00-00' : NULL, $cat);
		if(isset($replist))
		 foreach($replist AS $report)
		 {
			echo($dtext['vrep_expl_3'] . ": " . $report->get_date() . "<br>");
			
			// Add the summary text box
			echo("<br><b>" . $dtext['Summary'] . ":</b><br>");
			echo("<pre>" . $report->get_summary() . "</pre><br>");

			if($report->get_type() == "F" || $report->get_type() == "X")
			{
				echo($dtext['vrep_expl_4'] . "<br>");
				echo("<a href=getreport.php?". $report->get_id(). ">" . $dtext['vrep_expl_5'] . "</a>");
				// Allow the download link to work by registering and setting report id!
				$_SESSION['rid'] = $report->get_id();
			}
			else
			{
				echo("<br><b>" . $dtext['Content'] . ":</b><br>");
				echo(nl2br($report->get_contents()) . "<br><br>");
			}
	
		 }
	}	

  
  private function edit_report($repid)
  {
		global $showallreports;
    $dtext = $_SESSION['dtext'];
    $report = new report($repid);
		if($repid == 0)
		{
			if(isset($_POST['sid']))
			{
				$student = new student($_POST['sid']);
				$report->set_subject($student);
			}
				if(isset($_POST['gid']))
			{
				$group = new group($_POST['gid']);
				$report->set_subject($group);
			}
		}
		else
		{
			if($report->get_type() == "T" || $report->get_type() == "F")
				$student = $report->get_subject();
			else
				$group = $report->get_subject();
		}
		echo($dtext['editrep_for'] . " <b>");
		$groupedit = false;
		if($repid != 0)
		{
			if($report->get_type() == "X" || $report->get_type() == "C")
				$groupedit = true;
		}
		else
		{
			if(isset($_POST['gid']))
				$groupedit = true;
		}
		if($groupedit)
			echo($dtext['group'] . " " . $report->get_subject()->get_groupname());
		else
			echo($report->get_subject()->get_name());
		echo("</b><br>");
		$report->edit_protect();
		echo("<br>". $dtext['vrep_expl_3']. "<br>");
		$report->edit_date();
		echo("<br>". $dtext['Category']. "<br>");
		$report->edit_category();
		echo("<br>". $dtext['Summary']. "<br>");
		$report->edit_summary();
		echo("<br>");
		//$report->edit_type($groupedit);
		echo("<br>". $dtext['rep_content']. "<br>");
		$report->edit_contents($groupedit);
		echo("<br>");
		// Now a little form to send the file
		echo($dtext['use_file']);
		echo("<FORM name=repeditform id=repeditform method=POST action=". $_SERVER['REQUEST_URI']. " ENCTYPE=\"multipart/form-data\">");
		echo("<input type=hidden name=edit value=". $report->get_id(). ">");
		echo("<input type=hidden name=function value=reportfile>");
		echo("<input type=hidden name=sid value=". $report->get_subject()->get_id(). ">");
		if($groupedit)
			echo("<input type=hidden name=grouptype value=1>");
		echo("<input type=file name=\"reportfile\" value=\"" . $dtext['locate_file'] . "\" onChange=\"document.repeditform.submit()\">");
		if($report->get_id() > 0 && ($report->get_type() == "F" || $report->get_type() == "X"))
		{
			echo("<a href=getreport.php?". $report->get_id(). ">" . $dtext['vrep_expl_5'] . "</a>");
			// Allow the download link to work by registering and setting report id!
			$_SESSION['rid'] = $report->get_id();
		}
		if(isset($this->uploadresult))
		{
			if($this->uploadresult)
				echo("<img src='PNG/action_check.png'>");
			else
				echo("<img src='PNG/action_delete.png'>");
		}
		echo("</FORM>");	
		// Add a link to return to the student's overview
		if($report->get_type() == "F" || $report->get_type() == "T")
		{
			echo("<FORM NAME=backstu ID=backstu METHOD=POST ACTION=". $_SERVER['REQUEST_URI']. "><INPUT TYPE=HIDDEN NAME=student VALUE=". $report->get_subject()->get_id(). ">");
			echo("<a href='#' onClick='document.getElementById(\"backstu\").submit();'>". $report->get_subject()->get_name(). "</a></FORM>");
		}	
		else if($repid == 0 && isset($_POST['sid']))
		{
			echo("<FORM NAME=backt ID=backt METHOD=POST ACTION=". $_SERVER['REQUEST_URI']. "><INPUT TYPE=HIDDEN NAME=student VALUE=". $_POST['sid']. ">");
			echo("<BUTTON TYPE=BUTTON onClick='setTimeout(\"document.backt.submit()\",500);'>". $dtext['ADD_CAP']. "</BUTTON></FORM>");
		}
		else if($repid == 0)
		{
			echo("<FORM NAME=backt ID=backt METHOD=POST ACTION=". $_SERVER['REQUEST_URI']. ">");
			echo("<BUTTON TYPE=BUTTON onClick='setTimeout(\"document.backt.submit()\",500);'>". $dtext['ADD_CAP']. "</BUTTON></FORM>");
		}
	} 
  
  private function search_student($fname, $lname)
  {
    $dtext = $_SESSION['dtext'];
    $slist = inputclassbase::load_query("SELECT sid,firstname,lastname,GROUP_CONCAT(groupname) AS grps FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) WHERE active=1 AND firstname LIKE '%". $fname. "%' AND lastname LIKE '%". $lname. "%' GROUP BY sid ORDER BY lastname,firstname");
		if(!isset($slist['sid']))
		{ // No matching student found
			echo("<H2><font color=red>". $dtext['no_rep_4stu']. "</font></H2>");
			$this->show_group();
		}
		else if(count($slist['sid']) == 1)
		{ // only 1 student found, go to this one.
			$this->show_student($slist['sid'][0]);
		}
		else
		{ // Create a list of the students
			echo("<table border=1 cellpadding=0>");
			echo("<tr><th>". $dtext['Firstname']. "</th><th>". $dtext['Lastname']. "</th><th>". $dtext['in_grp']. "</th>");	
			echo("<th><center>" . $dtext['Go_reps'] . "</th>");  
			echo("</tr>");
			$altrow = false;
			foreach($slist['sid'] AS $six => $sid)
			{  
				echo("<tr". ($altrow ? " class=altbg" : ""). "><form method=post action=". $_SERVER['REQUEST_URI']. " name=repsview". $sid. " id=repsview". $sid. ">");
				echo("<TD>". $slist['firstname'][$six]. "</TD>");
				echo("<TD>". $slist['lastname'][$six]. "</TD>");
				echo("<TD>". $slist['grps'][$six]. "</TD>");
				// Add the Goto button
				echo("<td><center><input type=hidden name=student value=" . $sid ."><img src=PNG/search.png onClick='document.repsview". $sid. ".submit();'></td></form></tr>");
				$altrow = !$altrow;
      }
      echo("</table>");
    }
  }
}
?>
