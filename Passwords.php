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
  protected function add_contents()
  {
  }
  
  public function show_contents()
  {
    $dtext = $_SESSION['dtext'];
	$I = new teacher();
	$I->load_current();
	$group = new group();
	$group->load_current();
    $this->studlist = student::student_list();
	
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
  }
}
?>
