<?
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011 Aim4me N.V.  (http://www.aim4me.info)        |
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

class groupselector extends displayelement
{
  protected $selfield;
  protected $teacher;
  public function __construct($divid = NULL, $style = NULL, $teacher = NULL)
  {
    if(isset($teacher))
	  $this->teacher = $teacher;
	parent::__construct($divid, $style);
  }
  protected function show_contents()
  {
    echo("<FORM name=gselect id=gselect METHOD=POST ACTION='". $_SERVER['REQUEST_URI']. "'>". $_SESSION['dtext']['Group_Cap']. " :<BR>");
	$this->selfield->echo_html();
	echo("</FORM>");
	// nice, but we need to select the current active one!
	$curgrp = new group();
	$curgrp->load_current(); 
	echo("<SCRIPT> document.gselect.gselectfld.value=". $curgrp->get_id(). ";</SCRIPT>");
  }
  protected function add_contents()
  { // setup my field
    if(isset($_POST['gselectfld']))
	{ // A new value was posted
	  $dgroup = new group($_POST['gselectfld']);
	  $_SESSION['CurrentGroup'] = $dgroup->get_groupname();
	}
    if(!isset($this->teacher))
	{
	  $this->teacher = new teacher();
	  $this->teacher->load_current();
	}
	if($this->teacher->has_role("admin") || $this->teacher->has_role("counsel") || $this->teacher->has_role("office"))
	  $gquery = "SELECT gid AS id,groupname AS tekst FROM sgroup WHERE active=1 ORDER BY groupname";
	else
	{
	  $gquery = "SELECT gid AS id,groupname AS tekst FROM class LEFT JOIN sgroup USING(gid) WHERE active=1 AND tid=". $this->teacher->get_id();
	  $gquery .= " UNION SELECT gid,groupname FROM sgroup WHERE active=1 AND tid_mentor=". $this->teacher->get_id(). " GROUP BY gid ORDER BY tekst";
	}
    $this->selfield = new inputclass_listfield("gselectfld",$gquery,NULL,NULL,NULL,NULL,NULL,"\" onChange=\"document.gselect.submit();","datahandler.php");
  }
}
?>
