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
require_once("displayelements/displayelement.php");
require_once("student.php");
require_once("teacher.php");
require_once("group.php");
require_once("studentsorter.php");
require_once("message.php");

class Studentdetails extends displayelement
{
  private $studlist;
  private $uploadresult;
  protected function add_contents()
  {
		if(isset($_POST['orgdesttype']))
		{ // Message to be sent indication, pass it on to message object
			$I = new teacher();
			$I->load_current();
			message::send_pending_message($I->get_id(),$_POST['orgdesttype'],$_POST['orgdestid'],$_POST['destgrp'],"t");
		}
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
			}
		}
  }
  
  public function show_contents()
  {
    if(isset($_POST['edit']))
		{
			$this->edit_student($_POST['edit']);
		}
		else if(isset($_POST['view']))
		{
			$this->view_student($_POST['view']);
		}
		else if(isset($_POST['sendmessage']))
		{
			$this->compose_message($_POST['sendmessage']);
		}
		else
			$this->show_group();
  }
  
  private function show_group()
  {
    $dtext = $_SESSION['dtext'];
		$I = new teacher();
		$I->load_current();
		$group = new group();
		$group->load_current();
			$this->studlist = student::student_list();
	
    echo("<font size=+2>" . $dtext['studet_title'] . " ". $dtext['group']. " ". $_SESSION['CurrentGroup']. "</font><p>");

		// Show a box for sorting selection
		$ssortbox = new studentsorter();
		$ssortbox->show();
	
		// If this teacher may send messages to individual students/parent, an extra column is added to show the send message icon.
		$maysendmsg = false;
		$sendrqr = inputclassbase::load_query("SELECT * FROM messagerights WHERE destination='singlestudent'");
		if(isset($sendrqr['role']))
		{
			foreach($sendrqr['role'] AS $reqrole)
			{
				if($I->has_role($reqrole))
					$maysendmsg = true;
			}
		}
			
    // Now create a table with all students in the group to enable to view or edit their details
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

			echo("<th colspan=". ($maysendmsg ? 3 : 2). ">&nbsp;</th>");  
			echo("</tr>");

			// Create a row in the table for every existing student in the group
			$altrow = false;
			foreach($this->studlist AS $stud)
			{
				if($stud <> null)
				{
					echo("<tr". ($altrow ? ' class=altbg' : ''). ">");
					$sdata = $stud->get_list_data();
					foreach($sdata AS $stdata)
						echo("<TD>". $stdata. "</TD>");
					// Add the view and edit buttons
					echo("<form method=post action=". $_SERVER['REQUEST_URI']. " name=vs". $stud->get_id(). "><td><center><input type=hidden name=view value=" . $stud->get_id() ."> <img src='PNG/search.png' title='". $dtext['VIEW_CAP']. "' onclick='document.vs". $stud->get_id(). ".submit();'></td></form>");
					echo("<form method=post action=". $_SERVER['REQUEST_URI']. " name=es". $stud->get_id(). "><td><center><input type=hidden name=edit value=" . $stud->get_id() ."> <img src='PNG/reply.png' title='". $dtext['Edit']. "' onclick='document.es". $stud->get_id(). ".submit();'></td></form>");
					if($maysendmsg)
						echo("<form method=post action=". $_SERVER['REQUEST_URI']. " name=sm". $stud->get_id(). "><td><center><input type=hidden name=sendmessage value=" . $stud->get_id() ."> <img src='PNG/comments.png' title='". $dtext['SendMessage']. "' onclick='document.sm". $stud->get_id(). ".submit();'></td></form>");
					echo("</tr>");
					$altrow = !$altrow;
				}
				else
				{
					echo("<TR><TD COLSPAN=". (count($sdata)+2). ">&nbsp;</td></tr>");
				}
			}
			echo("</table>");
		}		
  }
  
  private function show_student($sid)
  {
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

	$replist = report::report_student_list($stud,$I);
    $this->report_list($replist,$I);
    // Create a button for a new report
    // Insert the row for a new report
    echo("<form method=post action=". $_SERVER['REQUEST_URI']. "><input type=hidden name=edit value=0>");
    echo("<input type=hidden name=sid value=". $stud->get_id(). ">");
    echo("<input type=submit value='" . $dtext['Crea_rep_4stu'] . "'></form>");
  }
  
  private function report_list($replist,$I)
  {
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
	if(isset($replist))
    foreach($replist AS $arep)
    {
      echo("<tr><form method=post action=". $_SERVER['REQUEST_URI']. " name=vr". $arep->get_id(). ">");
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
      if($I->has_role("counsel") || $arep->get_teacher()->get_id() == $I->get_id())
      {
        echo("<form method=post action=". $_SERVER['REQUEST_URI']. " name=er". $arep->get_id(). "><input type=hidden name=edit value=");
        echo($arep->get_id());
        echo("><td><center><img src='PNG/reply.png' title='". $dtext['Edit']. "' onclick='document.er". $arep->get_id(). ".submit();'></td></form>");
        echo("<form method=post action=". $_SERVER['REQUEST_URI']. " name=dr". $arep->get_id(). "><input type=hidden name=delete value=");
        echo($arep->get_id());
        echo("><td><center><img src='PNG/action_delete.png' title='". $dtext['Delete']. "' onclick='document.dr". $arep->get_id(). ".submit();'></td></form></tr>");
      }
      else
      {	// create two empty cells in the table because user has no access
        echo("<td>&nbsp;</td>");
        echo("<td>&nbsp;</td>");
        echo("</tr>");
      }   
    }
    echo("</table>");
  }
  
  private function view_student($sid)
  {
    echo("<BR>");
    $dtext = $_SESSION['dtext'];
	$stud = new student($sid);
	$todo = student::list_viewdetails();
	if(isset($todo))
	{
	  foreach($todo AS $tab => $lab)
	  {
	    $data = $stud->get_student_detail($tab);
		if($data == NULL)
		  $data = $dtext['No_data'];
	    echo($lab. ":  ". $data. "<BR>");
	  }
	}
  }
  
  private function edit_student($sid)
  {
    echo("<BR>");
    $dtext = $_SESSION['dtext'];
		$stud = new student($sid);
		$todo = student::list_editdetails();
		if(isset($todo))
		{
			foreach($todo AS $tab => $lab)
			{
				if($tab != "*gradestore.*" && $tab != "*absence.*")
			{
					echo($lab. ":  ");
					echo($stud->edit_student_detail($tab));
				echo("<BR>");
			}
			}
		}
  }

	private function compose_message($tid)
	{
		$I = new teacher();
		$I->load_current();
		message::new_message_dialog("s",$tid,$I->get_id());
	}
}
?>
