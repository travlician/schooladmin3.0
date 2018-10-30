<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2013 Aim4me N.V.   (http://www.aim4me.info)       |
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

class Passwords extends displayelement
{
  private $studlist;
  private $uploadresult;
	private $errmsg;
  protected function add_contents()
  {
		$this->errmsg = "";
		if(isset($_POST['newpw']))
			$this->change_password();
		if(isset($_POST['resetgrppws']))
			$this->reset_group_passwords();
		if(isset($_POST['resetstudpws']))
			$this->reset_student_passwords();
  }
  
  public function show_contents()
  {
    $dtext = $_SESSION['dtext'];
		$I = new teacher();
		$I->load_current();
		$group = new group();
		$group->load_current();
    $this->studlist = student::student_list();
		
		if($this->errmsg != "")
			echo("<H2 style='color: red;'>". $this->errmsg. "</h2>");
	
		if($I->has_role("admin") || $I->has_role("counsel") || $I->has_role("mentor"))
		{ // these teachers can see or change student passwords
			echo("<font size=+2>" . $dtext['stupw_title'] . " ". $dtext['group']. " ". $_SESSION['CurrentGroup']. "</font>");
			echo("<br>" . $dtext['stupw_expl_2']);
			echo("<br>" . $dtext['stupw_expl_3']);
			// Enable selection of student sorting
			$ssortbox = new studentsorter();
			$ssortbox->show();

			// Now create a table with all students in the group to enable to view or edit their passwords
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
				echo("<th><center>" . $dtext['ID_CAP'] . "</th>");
				echo("<th><center>" . $dtext['Student'] . "</th>");
				echo("<th><center>" . $dtext['Parent'] . "</th>");
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
							// Add the ID and password fields
					echo("<td>". $stud->get_student_detail("*sid"). "</td>");
					$pwfld = new inputclass_textfield("spw". $stud->get_id(),12,NULL,"password","student",$stud->get_id(),"sid");
					$ppwfld = new inputclass_textfield("sppw". $stud->get_id(),12,NULL,"ppassword","student",$stud->get_id(),"sid");
					echo("<td>");
					if($I->has_role("admin") || $I->has_role("mentor"))
					{
						$pwfld->echo_html();
						echo("</td><td>");
						$ppwfld->echo_html();
					}
					else
						echo($pwfld->__toString(). "</td><td>". $ppwfld->__toString());
					echo("</td>");
					echo("</tr>");
					$altrow = !$altrow;
				 }
				 else
				 {
				 echo("<TR><TD COLSPAN=". (count($fields)+3). ">&nbsp;</td></tr>");
				 }
				}
				echo("</table>");
			}
			// Admins and mentors can generate random passwords for the group
			if($I->has_role("admin") || $I->has_role("mentor"))
			{
				echo("<FORM ID=resetgrouppws NAME=resetgrouppws METHOD=POST>");
				echo("<INPUT TYPE=HIDDEN NAME=resetgrppws VALUE=1>");
				echo("<INPUT TYPE=BUTTON VALUE='". $dtext['cpw_change_pw_group_submit']. "' OnClick=confirm_reset('resetgrouppws')></FORM>");
			}
			
			// Admins can generate random passwords for all students
			if($I->has_role("admin"))
			{
				echo("<FORM ID=resetstudpws NAME=resetstudpws METHOD=POST>");
				echo("<INPUT TYPE=HIDDEN NAME=resetstudpws VALUE=1>");
				echo("<INPUT TYPE=BUTTON VALUE='". $dtext['cpw_change_pw_students_submit']. "' OnClick=confirm_reset('resetstudpws')></FORM>");
			}
			
			// Javascript to confirm submits
			echo("<SCRIPT> function confirm_reset(formid) { var form=document.getElementById(formid); if(confirm('". $dtext['cpw_stud_confirm']. "')) form.submit(); } </SCRIPT>");
		}
		// Any teacher can change their own password
		echo("<H2>". $dtext['Chng_pw']. "</h2>");
		echo("<FORM METHOD=POST>". $dtext['Cur_pw']. " <INPUT TYPE=PASSWORD NAME=curpw><br>");
		echo($dtext['New_pw']. " <INPUT TYPE=PASSWORD NAME=newpw><br>");
		echo($dtext['New_pw']. " <INPUT TYPE=PASSWORD NAME=newpw2><br>");
		echo("<INPUT TYPE=SUBMIT VALUE='". $dtext['cpw_submit']. "'></FORM>");
		// Admins can change the teacher password criteria, others just see it.
		{
			echo("<BR><BR>");
			$defteacher = new teacher(1);
			$pwcfld = new inputclass_integer("pwc_size",2,NULL,"avalue","teacherpreferences",1,"tid");
			$pwcfld->set_extrakey("aspect",10001);
			echo($dtext['cpw_min_size']. " : ");
			if($I->has_role("admin"))
				$pwcfld->echo_html();
			else
				echo($pwcfld->__toString());

			$pwcfld = new inputclass_checkbox("pwc_lc",$defteacher->get_preference("pwneedlc"),NULL,"avalue","teacherpreferences",1,"tid");
			$pwcfld->set_extrakey("aspect",10002);
			echo("<BR>". $dtext['cpw_need_lowercase']. " : ");
			if($I->has_role("admin"))
				$pwcfld->echo_html();
			else
				echo($pwcfld->__toString() == 1 ? $dtext['Yes'] : $dtext['No']);

			$pwcfld = new inputclass_checkbox("pwc_uc",$defteacher->get_preference("pwneedlc"),NULL,"avalue","teacherpreferences",1,"tid");
			$pwcfld->set_extrakey("aspect",10003);
			echo("<BR>". $dtext['cpw_need_uppercase']. " : ");
			if($I->has_role("admin"))
				$pwcfld->echo_html();
			else
				echo($pwcfld->__toString() == 1 ? $dtext['Yes'] : $dtext['No']);

			$pwcfld = new inputclass_checkbox("pwc_sc",$defteacher->get_preference("pwneedlc"),NULL,"avalue","teacherpreferences",1,"tid");
			$pwcfld->set_extrakey("aspect",10004);
			echo("<BR>". $dtext['cpw_need_specialchar']. " : ");
			if($I->has_role("admin"))
				$pwcfld->echo_html();
			else
				echo($pwcfld->__toString() == 1 ? $dtext['Yes'] : $dtext['No']);

			$pwcfld = new inputclass_integer("pwc_expiry",5,NULL,"avalue","teacherpreferences",1,"tid");
			$pwcfld->set_extrakey("aspect",10005);
			echo("<BR>". $dtext['cpw_set_expiry_period']. " : ");
			if($I->has_role("admin"))
				$pwcfld->echo_html();
			else
				echo($pwcfld->__toString());
		}
  }
	
	protected function change_password()
	{
		global $encryptedpasswords;
		// Before we change the password we do a lot of checks...
		// First, is the old password correct
    $dtext = $_SESSION['dtext'];
		$I = new teacher();
		$I->load_current();
		if(!$I->validate_password($_POST['curpw']))
		{ // Password does not match
			$this->errmsg = $dtext['cpw_err_lead']. " ". $dtext['cpw_err_invpw'];
			return;
		}
		if($_POST['newpw'] != $_POST['newpw2'])
		{ // New passwords does not match
			$this->errmsg = $dtext['cpw_err_lead']. " ". $dtext['cpw_err_pwmis'];
			return;
		}
		
		// Checking criteria
		$defteach = new teacher(1);
		$newvalid=true;
		if(strlen($_POST['newpw']) < $defteach->get_preference(10001))
			$newvalid = false;
		
		if($defteach->get_preference(10002) == 1)
		{ // Need to check for presence of lowercase
			if(! (bool) preg_match("/[a-z]/", $_POST['newpw']))
				$newvalid = false;
		}
		
		if($defteach->get_preference(10003) == 1)
		{ // Need to check for presence of uppercase
			if(! (bool) preg_match("/[A-Z]/", $_POST['newpw']))
				$newvalid = false;
		}
		
		if($defteach->get_preference(10004) == 1)
		{ // Need to check for presence of puntuation
			if(str_replace(array('.',',',':',';','?','!'), '', $_POST['newpw']) == $_POST['newpw'])
				$newvalid = false;
		}
		
		if(!$newvalid)
		{ // New password does not match criteria
			$this->errmsg = $dtext['cpw_err_lead']. " ". $dtext['cpw_err_pwnocomply'];
			return;
		}
		
		// Password is valid, now set it!
		if($encryptedpasswords == 1)
			$npw = md5($_POST['newpw']);
		else
			$npw = $_POST['newpw'];
		mysql_query("UPDATE teacher SET password=\"". $npw. "\" WHERE tid=". $I->get_id());
		if($defteach->get_preference(10005) != "")
		{ // Set the new expiry date
			$expqry = "UPDATE teacher SET pwexpirydate=DATE_ADD(CURDATE(), INTERVAL ". $defteach->get_preference(10005). " DAY) WHERE tid=". $I->get_id();	
		}
		else
			$expqry = "UPDATE teacher SET pwexpirydate=NULL WHERE tid=". $I->get_id();	
		mysql_query($expqry);	
		
		$this->errmsg = $dtext['cpw_expl_2'];
	}
	
	protected function reset_group_passwords()
	{
		$resetq = "UPDATE student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) SET password=SUBSTR(MD5(RAND()),1,9), ppassword=SUBSTR(MD5(RAND()),1,8) WHERE groupname='". $_SESSION['CurrentGroup']. "'";
		mysql_query($resetq);
		if(mysql_error())
			$this->errmsg = mysql_error();
	}
	protected function reset_student_passwords()
	{
		$resetq = "UPDATE student SET password=SUBSTR(MD5(RAND()),1,8), ppassword=SUBSTR(MD5(RAND()),1,9)";
		mysql_query($resetq);
		if(mysql_error())
			$this->errmsg = mysql_error();
	}
}
?>
