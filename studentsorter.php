<?
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014 Aim4me N.V.  (http://www.aim4me.info)        |
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
require_once("group.php");
require_once("subject.php");

// get the default form config if no currently active
global $ssortertable;
if(!isset($_SESSION['ssortertable']))
{
  if(isset($ssortertable))
  {
    $_SESSION['ssortertable'] = $ssortertable;
  }
}


class studentsorter extends displayelement
{
  protected $selfield;
  protected $teacher;
  protected $defaultsubject;
  protected function show_contents()
  {
    global $ssortertable;
    echo("<FORM name=ssorter id=ssorter METHOD=POST ACTION='". $_SERVER['REQUEST_URI']. "'>". $_SESSION['dtext']['ssorterlabel']. " : ");
	$this->selfield->echo_html();
	if(isset($_SESSION['ssortertable']))
	echo("<SCRIPT> document.ssorter.ssorterfld.value='". $_SESSION['ssortertable']. "';</SCRIPT>");
	if(isset($_POST) && count($_POST) > 0)
      foreach($_POST AS $pkey => $pval)
	    if($pkey != "student" && $pkey != "delte" && $pkey != "ssorterfld")
	      echo("<input type=hidden name='". $pkey. "' value='". $pval. "'>");
	echo("</FORM>");
  }
  protected function add_contents()
  { // setup my field
    // get the default form config if no currently active
    if(isset($_POST['ssorterfld']))
	{ // A new value was posted
	  $_SESSION['ssortertable'] = $_POST['ssorterfld'];
	}

	$studentfields = inputclassbase::load_query("SELECT table_name, `label` FROM student_details WHERE table_name LIKE 's_%' GROUP BY seq_no ORDER BY seq_no");
	$sfqs = "SELECT '' AS id, '". $_SESSION['dtext']['Lastname']. ",". $_SESSION['dtext']['Firstname']. "' as tekst";
	$sfqs .= " UNION SELECT '-', '". $_SESSION['dtext']['Firstname']. ",". $_SESSION['dtext']['Lastname']. "' as tekst";
	foreach($studentfields['table_name'] AS $sfix => $stn)
	{
	  $sfqs .= " UNION SELECT '". $stn. "','". $studentfields['label'][$sfix]. ",". $_SESSION['dtext']['Lastname']. ",". $_SESSION['dtext']['Firstname']. "'";
	}
    $this->selfield = new inputclass_listfield("ssorterfld",$sfqs,NULL,NULL,NULL,NULL,NULL,"\" onChange=\"document.ssorter.submit();","datahandler.php");
  }
}
?>
