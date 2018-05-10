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
require_once("group.php");
require_once("absencecategory.php");
require_once("absence.php");
require_once("subjectselector.php");
require_once("studentsorter.php");
require_once("subject.php");

class Absenceman extends displayelement
{
  private $studlist;
  private $uploadresult;
  private $adate,$gid,$mid;
  
  public function set_cid_date($adate=NULL,$cid=NULL)
  {
	if(isset($adate))
	{
	  $this->adate = $adate;
		$this->absdate = $adate;
	}
	if(isset($cid))
	{ // Need to get the gid and mid from the cid data
	  $ciddata = inputclassbase::load_query("SELECT * FROM `class` WHERE cid=". $cid);
	  if(isset($ciddata['gid'][0]))
	  {
	    $this->gid = $ciddata['gid'][0];
	    $this->mid = $ciddata['mid'][0];
	  }
	}
  }
  protected function add_contents()
  {
    if(isset($_POST['delte']))
		{
			if($_POST['delte'] == 0)
			{ // Delete the latest added absence record
				$lastabsrec = inputclassbase::load_query("SELECT MAX(asid) AS `todelete` FROM absence");
				$_POST['delte']=$lastabsrec['todelete'][0];
			}
			mysql_query("DELETE FROM absence WHERE asid=". $_POST['delte']);
		}
		if(isset($_POST['absmodefld']))
			mysql_query("UPDATE teacher SET shortabs=". $_POST['absmodefld']. " WHERE tid=". $_SESSION['uid']);
  }
  
  public function show_contents()
  {
		global $currentuser;
		if(isset($_POST['student']))
			$this->show_student($_POST['student']);
		else
		{
			if((isset($_SESSION['TouchScreen']) || $currentuser->get_preference("400") == "AbsenceScreenTouch") && $currentuser->get_preference("400") != "AbsenceScreenTraditional")
				$this->show_group_TS();
			else
			{
				//echo("USERPREF". $currentuser->get_preference("400"));
				$this->show_group();
			}
		}
  }
  
  private function show_group()
  {
		global $show_abs_cat_counters;
		global $repeatabsheader;
    $dtext = $_SESSION['dtext'];
    require_once("absscripts.php");
		$I = new teacher();
		$I->load_current();
		if(isset($this->gid))
		{
			$group = new group($this->gid);
		}
		else
		{
			$group = new group();
			$group->load_current();
		}
    $this->studlist = student::student_list($group);
		$cats = absencecategory::list_categories();
	
    echo("<font size=+2>" . $dtext['absreg_title'] . " ". $dtext['group']. " ". $group->get_groupname(). "</font>");
    // Show for which subject current editing applies and allow change
		$subselbox = new subjectselector(NULL,NULL,NULL,isset($this->mid) ? $this->mid : NULL);
		$subselbox->show();	
		// Enable selection of student sorting
		$ssortbox = new studentsorter();
		$ssortbox->show();

    // Now create a table with all students in the group to enable to view or edit their absence state
    // Create the heading row for the table
		if(isset($this->studlist))
		{
      echo("<table border=1 cellpadding=0>");
			//$this->show_abs_header($cats);
      // Create a row in the table for every existing student in the group
	  $altrow = false;
	  $seqn = 0;
	  foreach($this->studlist AS $stud)
      {
	    if($stud <> null)
		{
		  if($seqn % (isset($repeatabsheader) ? $repeatabsheader : 10) == 0)
		    $this->show_abs_header($cats);
          echo("<tr". ($altrow ? ' class=altbg' : ''). ">");
		  $sdata = $stud->get_list_data(isset($this->gid) ? $this->gid : NULL);
		  if(isset($sdata))
		  foreach($sdata AS $stdata)
		    echo("<TD>". $stdata. "</TD>");
          // Add the per student detail object and add/view option for each category
		  $totabs = $stud->get_abscount(TRUE);
		  $totabsnok = $stud->get_abscount(FALSE);
		  echo("<td><IMG SRC='PNG/search.png' BORDER=0 TITLE='". $dtext["VIEW_CAP"]. " ". $dtext['Authorization']. " ". $dtext['Yes']. ": ". $totabs. ", ". $dtext['No']. ": ". $totabsnok. "' onClick='viewabs_stud(". $stud->get_id(). ");'> ". $totabs. "/". $totabsnok. "</td>");
		  if(isset($cats))
		    foreach($cats AS $acat)
		    {
		      echo("<TD>");
					if(isset($show_abs_cat_counters) && $show_abs_cat_counters)
					{
						echo($stud->get_abscount(TRUE,$acat). "/". $stud->get_abscount(FALSE,$acat));
					}
					$absr = absence::get_abs_record($stud,$acat,isset($this->adate) ? date("Y-m-d",$this->adate) : NULL);
					if(isset($absr))
						$absr->add_hidden_edit_dialog();
					else
						absence::add_hidden_add_dialog($stud,$acat,isset($this->adate) ? $this->adate : NULL);
					echo("</TD>");
		    }
		  // Create an icon to create a new report
		  echo("<TD><IMG SRC='PNG/reply.png' border=0 onClick='toreport(". $stud->get_id(). ");'></TD>");
		  echo("</tr>");
		  $altrow = !$altrow;
		  $seqn++;
		}
		else
		{
		  echo("<TR><TD COLSPAN=". (count($sdata)+count($cats)+1). ">&nbsp;</td></tr>");
		}
      }
      echo("</table>");
	  echo("<FORM METHOD=POST ACTION='teacherpage.php?Page=Reports' ID=to_report NAME=to_report>");
	  echo("<INPUT NAME=sid ID=sid VALUE=0 TYPE=HIDDEN><INPUT NAME=edit ID=edit VALUE=0 TYPE=HIDDEN></FORM>");
	  echo("<SCRIPT> function toreport(sid) { document.to_report.sid.value=sid; document.to_report.submit(); } </SCRIPT>");
	}
  }
  
  private function show_abs_header($cats)
  {
    $dtext = $_SESSION['dtext'];
      echo("<tr>");
      $fields = student::get_list_headers(isset($this->gid) ? $this->gid : NULL);
      if(isset($fields))
	  foreach($fields AS $fieldname)
      {
        echo("<th><center>". $fieldname. "</th>");
      }
      echo("<th><center><IMG SRC='PNG/search.png' BORDER=0></th>");
	  if(isset($cats))
	    foreach($cats AS $acat)
		{
		  echo("<TH>");
		  $acat->show_image();
		  echo("</TH>");
		}
	  echo("<TH>". $dtext['My_reports']. "</th>");
      echo("</tr>");
  }
  
  private function show_group_TS()
  {
    $dtext = $_SESSION['dtext'];
    require_once("absscripts.php");
    echo('<LINK href="TSstudent.css" rel="stylesheet" type="text/css">');	
	$I = new teacher();
	$I->load_current();
	if(isset($this->gid))
	{
	  $group = new group($this->gid);
	}
	else
	{
	  $group = new group();
	  $group->load_current();
	}
    $this->studlist = student::student_list($group);
	$cats = absencecategory::list_categories();
	
    echo("<font size=+2>" . $dtext['absreg_title'] . " ". $dtext['group']. " ". $group->get_groupname(). "</font>");
    // Show for which subject current editing applies and allow change
		//echo("Date=". date("Y-m-d",$this->adate). "<BR>");
	$subselbox = new subjectselector(NULL,NULL,NULL,isset($this->mid) ? $this->mid : NULL);
	$subselbox->show();	
	
	// Query the dialog mode for absence
	$absmodeqr = inputclassbase::load_query("SELECT shortabs FROM teacher WHERE tid=". $_SESSION['uid']);

    // Create the heading row for the table
	if(isset($this->studlist))
	{
	  // Add some script to make the selection of the active function (default: viewing)
	  if(isset($cats))
	  {
	    // Add some script to make the selection of the active function (default: viewing)
		echo('<SCRIPT> var curfunc="_view"; function selectfunc(funcname) 
		{
          curfunc = funcname;		
		  cursel = document.getElementsByClassName("catselbutselected");
          i = cursel.length;
          while(i--) 
		  {
            cursel[i].className = "catselbut";		  
		  }
          rblist = document.getElementsByClassName("catselbut");
          i = rblist.length;
          while(i--)
          {
		    if(rblist[i].id == "catselbut" + curfunc)
			  rblist[i].className = "catselbutselected";
          }		  
		}
		</SCRIPT>');
	    echo("<SPAN class=catselbutselected ID=catselbut_view onClick='selectfunc(\"_view\");'><img src='PNG/search.png' BORDER=0></SPAN>");
	    foreach($cats AS $acat)
		{
		  echo("<SPAN class=catselbut ID=catselbut". $acat->get_name(). " onClick='selectfunc(\"". $acat->get_name(). "\");'>");
		    $acat->show_image();
		  echo("</SPAN>");
		  // Add a hidden dialog for new entry
		  absence::form_add_dialogTS($acat,isset($this->adate) ? $this->adate : mktime());
		}
		// Add the icon for reporting
	    echo("<SPAN class=catselbut ID=catselbut_report onClick='selectfunc(\"_report\");'>". $dtext['tpage_manreps']. "</SPAN>");
		if(isset($_POST['funcrecover']))
		{
		  echo("<SCRIPT> selectfunc('". $_POST['funcrecover']. "'); </script>");
		}
        echo("<BR>");
	  }
	  // Add the script to handle clicking (touching) a student
	  echo("<SCRIPT>
	    function handle_student(sid)
		{
		  if(curfunc == \"_view\")
		  {
		    viewabs_stud(sid);
		  }
		  else if(curfunc == \"_report\")
		  {
		    toreport(sid);
		  }
		  else
		  {
		    dataval=\"\"+sid+\";\"+curfunc;
			fobj = document.getElementById(\"sidabsset\");
			fobj.value = dataval;
			// Now toggle the correspondig state icon
			sIcon = document.getElementById(\"AbsStateIcon-\"+sid+\"-\"+curfunc);
			if(sIcon.className == \"studentStateIconHidden\")
			{
			  sIcon.className = \"studentStateIcon\";");
	  if(isset($absmodeqr['shortabs']) && $absmodeqr['shortabs'][0] == 1)
	    echo(" send_xml(\"sidabsset\",fobj); ");
	  else
	    foreach($cats AS $acat)
	      echo("if(curfunc == '". $acat->get_name(). "') show_add_abs_dial". $acat->get_id(). "(sid);");
	  echo("              			  
			}
			else
			{
			  sIcon.className = \"studentStateIconHidden\";
		      send_xml(\"sidabsset\",fobj);
			}
		  }
		}
		</SCRIPT>
		<INPUT TYPE=hidden ID=sidabsset NAME=sidabsset VALUE=\"\">
		");
	  echo("<FORM METHOD=POST ACTION='teacherpage.php?Page=Reports' ID=to_report NAME=to_report>");
	  echo("<INPUT NAME=sid ID=sid VALUE=0 TYPE=HIDDEN><INPUT NAME=edit ID=edit VALUE=0 TYPE=HIDDEN></FORM>");
	  echo("<SCRIPT> function toreport(sid) { document.to_report.sid.value=sid; document.to_report.submit(); } </SCRIPT>");

      // Create an representation object for each student in the group
	  foreach($this->studlist AS $stud)
      {
	   if($stud <> null)
		{
		$sdata = $stud->get_list_data(isset($this->gid) ? $this->gid : NULL);
	    echo("<SPAN class=studentTSview onClick='handle_student(". $stud->get_id(). ");'>");
	    foreach($cats AS $acat)
		{
		  if($stud->get_absstate($acat,isset($this->absdate) ? $this->absdate : NULL, isset($this->mid) ? $this->mid : NULL))
		    $acat->show_image("studentStateIcon ID='AbsStateIcon-". $stud->get_id(). "-". $acat->get_name(). "'");
		  else
		    $acat->show_image("studentStateIconHidden ID='AbsStateIcon-". $stud->get_id(). "-". $acat->get_name(). "'");
		}
		foreach($sdata AS $stdata)
		  echo($stdata. "<BR>");
		echo("</span>");
	   }
      }
	}
	
	// Show a link to show or supress absence dialogues
	if(isset($absmodeqr['shortabs']))
	{
	  // Create a form to enable selection of abs mode
	  echo("<FORM METHOD=POST ID=absmodeselform ACTION='". $_SERVER['REQUEST_URI']. "'><INPUT TYPE=HIDDEN ID=absmodefld NAME=absmodefld VALUE=0></FORM>");
	  // Create a script to switch absence dialog mode
	  echo("<SCRIPT> function absmodesel(newmode) { document.getElementById('absmodefld').value=newmode; document.getElementById('absmodeselform').submit(); } </SCRIPT>");
	  if($absmodeqr['shortabs'][0] == 1)
	    echo("<SPAN onClick='absmodesel(0);'>". $dtext['FullAbs']. "</SPAN>");
	  else
	    echo("<SPAN onClick='absmodesel(1);'>". $dtext['ShortAbs']. "</SPAN>");	    
	}
  }
  
  private function show_student()
  {
		if(isset($_POST['absfiltcat']))
			$_SESSION['absfiltcat'] = $_POST['absfiltcat'];
		if(isset($_POST['absfiltper']))
			$_SESSION['absfiltper'] = $_POST['absfiltper'];
    global $currentuser;
    $dtext = $_SESSION['dtext'];
    require_once("absscripts.php");
		$stud = new student($_POST['student']);
		$testabs = new absence();
    echo("<font size=+2>" . $dtext['absreg_title'] . "</font><BR><font size=+1>". $stud->get_name(). "</font>");
		$abslist = absence::list_student($stud);
		// added 5-2-2018: drop box for absence cat and period filter
		$acatlist = absencecategory::list_categories();
		if(isset($acatlist))
		{
			echo("<FORM method=POST ID=absfiltdrops><INPUT TYPE=HIDDEN NAME=student VALUE=". $stud->get_id(). ">");
			echo($dtext['Category']. " : ");
			echo("<SELECT onChange='document.getElementById(\"absfiltdrops\").submit()' name=absfiltcat>");
			echo("<OPTION value=0>*</OPTION>");
			foreach($acatlist AS $acid => $aabscat)
				echo("<OPTION VALUE=". $acid. (isset($_SESSION['absfiltcat']) && $_SESSION['absfiltcat'] == $acid ? " SELECTED" : ""). ">". $aabscat->get_name(). "</OPTION>");
			echo("</SELECT>");
			
			// Now the periods
			$perdata = inputclassbase::load_query("SELECT * FROM period ORDER BY id");
			foreach($perdata['id'] AS $pix => $periodid)
			{
				$perstart[$periodid] = $perdata['startdate'][$pix];
				$perend[$periodid] = $perdata['enddate'][$pix];
				$perid[$periodid] = $periodid;
			}
			
			echo(" ". $dtext['Period']. " : ");
			echo("<SELECT onChange='document.getElementById(\"absfiltdrops\").submit()' name=absfiltper>");
			echo("<OPTION value=0>*</OPTION>");
			foreach($perid AS $pid)
				echo("<OPTION VALUE=". $pid. (isset($_SESSION['absfiltper']) && $_SESSION['absfiltper'] == $pid ? " SELECTED" : ""). ">". $pid. "</OPTION>");
			echo("</SELECT>");
			echo("</FORM>");
		
		}
		
		echo("<TABLE><TR><TH>&nbsp</TH><TH>". $dtext['Date']. "</TH><TH>". $dtext['Time']. "</TH>");
		if($testabs->use_subjects())
			echo("<TH>". $dtext['Timeslot']. "</TH>");
		echo("<TH>". $dtext['Reason']. "</TH><TH>". $dtext['Authorization']. "</TH>");
		if($testabs->use_subjects())
			echo("<TH>". $dtext['Subject']. "</TH>");
		echo("<TH>". $dtext['Remarks']. "</TH>");
		echo("<TH></TH>");
		echo("</TR>");
		echo("<TR><TD COLSPAN=7>");
		absence::add_hidden_add_full_dialog($stud);
		echo("</TD></TR>");
		if($abslist != NULL)
		{
			$altrow = false;
			foreach($abslist AS $absr)
			{
				if((!isset($_SESSION['absfiltcat']) || $_SESSION['absfiltcat'] == 0 || $absr->get_cat()->get_id() == $_SESSION['absfiltcat']) &&
						(!isset($_SESSION['absfiltper']) || $_SESSION['absfiltper'] == 0 || $absr->get_period() == $_SESSION['absfiltper']))
				{ // The item is not filtered.
					$cursub = new subject($_SESSION['CurrentSubject']);
					$ro = (($absr->get_subject() != "" && $absr->get_subject() != $cursub->get_shortname() && !$currentuser->has_role("admin") && !$currentuser->has_role("arman")) || $absr->get_reason_me() == "");
					if(!$ro && !(isset($currentuser) && $currentuser->has_role("admin")) && $absr->get_authorization() == $dtext['Parent'])
						$ro = true;
						echo("<TR". ($altrow ? ' class=altbg' : ''). "><TD>");
					if($ro)
						echo("&nbsp;");
					else
						echo("<IMG SRC='PNG/action_delete.png' BORDER=0 TITLE='". $dtext["Delete"]. "' onClick='deleteabsstud(". $absr->get_id(). ",". $stud->get_id().");'>");		
					echo("</TD><TD>");
					if($ro)
						echo($absr->get_date());
					else
						$absr->edit_date();
					echo("</TD><TD>");
					if($ro)
						echo($absr->get_time());
					else
						$absr->edit_time();
					echo("</TD><TD>");
					if($testabs->use_subjects())
					{
						if($ro)
							echo($absr->get_timeslot());
						else
							$absr->edit_timeslot();
						echo("</TD><TD>");
					}
					if($ro)
						echo($absr->get_reason());
					else
						$absr->edit_reason();
					echo("</TD><TD>");
					if($ro)
						echo($absr->get_authorization());
					else
						$absr->edit_authorization();
					echo("</TD><TD>");
					if($testabs->use_subjects())
					{
						if($ro)
							echo($absr->get_subject());
						else
							$absr->edit_subject();
						echo("</TD><TD>");
					}
					if($ro)
						echo($absr->get_explanation());
					else
						$absr->edit_explanation();
					echo("</TD><TD>");
					echo($absr->get_modinfo());
							echo("</TD></TR>");
					$altrow = !$altrow;
				}
			}
		}
		echo("</TABLE>");	
  }
}
?>
