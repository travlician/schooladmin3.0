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
require_once("displayelements/displayelement.php");
require_once("teacher.php");
require_once("message.php");

class Teacherdetails extends displayelement
{
  private $teachlist;
  private $uploadresult;
  protected function add_contents()
  {
  }
  
  public function show_contents()
  {
		if(isset($_POST['orgdesttype']))
		{ // Message to be sent indication, pass it on to message object
			$I = new teacher();
			$I->load_current();
			message::send_pending_message($I->get_id(),$_POST['orgdesttype'],$_POST['orgdestid'],$_POST['destgrp'],"t");
		}
    if(isset($_POST['edit']))
		{
			$this->edit_teacher($_POST['edit']);
		}
		else if(isset($_POST['view']))
		{
			$this->view_teacher($_POST['view']);
		}
		else if(isset($_POST['sendmessage']))
		{
			$this->compose_message($_POST['sendmessage']);
		}
		else
			$this->show_teachers();
  }
  
  private function show_teachers()
  {
    $dtext = $_SESSION['dtext'];
		$I = new teacher();
		$I->load_current();
    $this->teachlist = teacher::active_list();
	
    echo("<font size=+2>" . $dtext['teachdet_title'] . "</font><p>");
    echo("<br>" . $dtext['teachdet_expl_1a']);
    if($I->has_role("admin")) 
	  echo(" " . $dtext['teachdet_expl_1b']);
    echo(" ". $dtext['teachdet_expl_1c']); 

    // Now create a table with all students in the group to enable to view or edit their details
    // Create the heading row for the table
		if(isset($this->teachlist))
		{
      echo("<table border=1 cellpadding=0>");
      echo("<thead>");
      $fields = teacher::get_list_headers();
      foreach($fields AS $fieldname)
      {
        echo("<th><center>". $fieldname. "</th>");
      }
  
      if($I->has_role("admin"))
	    echo("<th colspan=2>&nbsp;</th>");  
      else
	    echo("<th>&nbsp;</th>");

			// If this teacher may send messages to individual teachers, an extra column is added to show the send message icon.
			$maysendmsg = false;
			$sendrqr = inputclassbase::load_query("SELECT * FROM messagerights WHERE destination='singleteacher'");
			if(isset($sendrqr['role']))
			{
				foreach($sendrqr['role'] AS $reqrole)
				{
					if($I->has_role($reqrole))
						$maysendmsg = true;
				}
			}
			
			if($maysendmsg)
				echo("<th>&nbsp;</th>");

      echo("</thead>");

      // Create a row in the table for every existing active teacher
			$altrow = false;
			foreach($this->teachlist AS $teach)
			{
				echo("<tr". ($altrow ? ' class=altbg' : ''). ">");
				$sdata = $teach->get_list_data();
				foreach($sdata AS $stdata)
					echo("<TD>". $stdata. "</TD>");
						// Add the view and edit buttons
						echo("<form method=post action=". $_SERVER['REQUEST_URI']. " name=vt". $teach->get_id(). "><td><center><input type=hidden name=view value=" . $teach->get_id() ."> <img src='PNG/search.png' title='". $dtext['VIEW_CAP']. "' onclick='document.vt". $teach->get_id(). ".submit();'></td></form>");
				// Decide if teacher can edit details.
				// Conditions are as follows:
				// - Teacher with admin role can edit all entries
				// - Teachers can edit their own entry if at least one detail is set to be editable by teachers
				// - Counselers can edit all entries if at least one detail is set to be editable by counselers
				// - Office can edit all entries if at least one detail is set to be editable by office
				$mayedit = false;
				if($I->has_role("admin"))
					$mayedit = true;
				if ($I->has_role("counsel") || $I->has_role("office"))
				{
					$taq = "SELECT waccess FROM teacher_details WHERE size IS NULL";
					if($I->has_role("counsel"))
						$taq .= " OR waccess='C'";
					if($I->has_role("office"))
						$taq .= " OR waccess='O'";
					$taqr = inputclassbase::load_query($taq);
					if(isset($taqr['waccess']))
						$mayedit = true;
				}
				// Teacher without special roles
				$taqr = inputclassbase::load_query("SELECT waccess FROM teacher_details WHERE waccess='T'");
				if(isset($taqr['waccess']) && $_SESSION['uid'] == $teach->get_id())
					$mayedit = true;			
				if($mayedit)
							echo("<form method=post action=". $_SERVER['REQUEST_URI']. " name=et". $teach->get_id(). "><td><center><input type=hidden name=edit value=" . $teach->get_id() ."> <img src='PNG/reply.png' title='". $dtext['Edit']. "' onclick='document.et". $teach->get_id(). ".submit();'></td></form>");
				if($maysendmsg)
					echo("<form method=post action=". $_SERVER['REQUEST_URI']. " name=sm". $teach->get_id(). "><td><center><input type=hidden name=sendmessage value=" . $teach->get_id() ."> <img src='PNG/comments.png' title='". $dtext['SendMessage']. "' onclick='document.sm". $teach->get_id(). ".submit();'></td></form>");
				echo("</tr>");
				$altrow = !$altrow;
			}
			echo("</table>");
		}
		// Now for a graphic of usage: a list fo teacher names, how many logins last month and subjects
		$graphqr = inputclassbase::load_query("SELECT firstname,lastname,COUNT(id) AS logins,GROUP_CONCAT(DISTINCT shortname) AS subjects FROM eventlog LEFT JOIN teacher ON(user=tid) LEFT JOIN class USING(tid) LEFT JOIN subject USING(mid) WHERE eventid='IN-TEA' AND lastupdate > DATE_SUB(NOW(),INTERVAL 1 MONTH) GROUP BY tid ORDER BY logins");
		if(isset($graphqr))
		{
			echo("<SCRIPT>\r\n");
			foreach($graphqr AS $akey => $anarray)
			{
				echo("var tdata_". $akey. " = [");
				foreach($anarray AS $ix => $tdat)
				{
					if($ix != 0)
						echo(",");
					echo("\"". $tdat. "\"");
				}
				echo("];\r\n");
			}
			echo("</SCRIPT>");
		}
		
		
		
  }
  
  private function view_teacher($tid)
  {
    $dtext = $_SESSION['dtext'];
	$teach = new teacher($tid);
	$todo = teacher::list_viewdetails();
	if(isset($todo))
	{
	  foreach($todo AS $tab => $lab)
	  {
	    $data = $teach->get_teacher_detail($tab);
			if($data == NULL)
				$data = $dtext['No_data'];
	    echo($lab. ":  ". $data. "<BR>");
	  }
	}
  }
  
  private function edit_teacher($tid)
  {
    $dtext = $_SESSION['dtext'];
		$teach = new teacher($tid);
		$todo = teacher::list_editdetails();
		if(isset($todo))
		{
			foreach($todo AS $tab => $lab)
			{
				if($tab != "*sgroup.groupname" && $tab != "*subject.fullname")
			{
				echo($lab. ":  ");
				echo($teach->edit_teacher_detail($tab));
				echo("<BR>");
			}
			}
		}
  }
	
	private function compose_message($tid)
	{
		$I = new teacher();
		$I->load_current();
		message::new_message_dialog("t",$tid,$I->get_id());
	}
  
}
?>
