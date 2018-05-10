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
require_once("teacher.php");
require_once("inputlib/inputclasses.php");

class ManGUIQueries extends displayelement
{
	protected $labels,$fields;
  protected function add_contents()
  {		
    global $userlink,$currentuser;
		$this->labels = array(1 => "BellQueryLabel","OverallPassQueryLabel","SubjectPassQueryLabel","GUIOverallColor","GUISubjectColor","CatchupResult","ActionResult","EmailContact","PhoneContact","GUIBBWhite","GUIBBYellow","GUIBBRed","GUIBBGreen");
		foreach($this->labels AS $fieldnr => $label)
		{
			if($label != "")
				$this->fields[$fieldnr] = new inputclass_textarea("guiqueryfld". $fieldnr,"80,*",NULL,"query","guiqueries",$fieldnr,"qname",NULL,"datahandler.php");
		}
		$this->libimgsfld = new inputclass_listfield("libimgs","SELECT folder AS id, folder AS tekst FROM libraryfiles WHERE type LIKE 'image%' GROUP BY folder",NULL,"query","guiqueries", 9999, "qname");
  }
  
  public function show_contents()
  {
		echo("<H1>". $_SESSION['dtext']['Man_GUIQueries']. "</h1>");
		foreach($this->labels AS $fieldnr => $label)
		{
			if($label != "")
			{
				echo("<SPAN style='width:300px; display: inline-block; vertical-align: top;'>". $_SESSION['dtext'][$this->labels[$fieldnr]]. " :</span>");
				$this->fields[$fieldnr]->echo_html();
				echo("<BR>");
			}
		}
		echo("<SPAN style='width:300px; display: inline-block; vertical-align: top;'>". $_SESSION['dtext']['GUILibImagePath']. " :</span>");
		$this->libimgsfld->echo_html();
  } 
}
?>
