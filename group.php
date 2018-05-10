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
require_once("inputlib/inputclasses.php");
require_once("teacher.php");

class group
{
  protected $groupid;
  protected $groupnamefld,$mentorfld;

  public function __construct($groupid = NULL)
  {
    if(isset($groupid))
	  $this->groupid = $groupid;
	else
	  $this->groupid = 0;
  }
  public function load_from_groupname($groupname)
  {
    $gdata = inputclassbase::load_query("SELECT gid FROM sgroup WHERE active=1 AND groupname = '". $groupname. "'");
	if(isset($gdata))
	  $this->groupid = $gdata['gid'][0];
  }
  public function load_current()
  {
    if(isset($_SESSION['CurrentGroup']) && $_SESSION['CurrentGroup'] != '')
	{
      $this->load_from_groupname($_SESSION['CurrentGroup']);
	}
	else
	{
	  $I = new teacher();
	  $I->load_current();
	  $this->load_from_groupname($I->get_defaultgroup());
	  $_SESSION['CurrentGroup'] = $this->get_groupname();
	}
  }
  
  public function get_id()
  {
    return $this->groupid;
  }
  
  public function get_groupname()
  {
    if($this->groupid == 0)
	  return NULL;
    if(!isset($this->groupnamefld))
	{
	  $this->groupnamefld = new inputclass_textfield("tgname". $this->groupid,40,NULL,"groupname","sgroup",$this->groupid,"gid",NULL,"datahandler.php");
	}
	return($this->groupnamefld->__toString());
  }

  public function edit_groupname()
  {
    if(!isset($this->groupnamefld))
	  $this->groupnamefld = new inputclass_textfield("gname". $this->groupid,40,NULL,"groupname","sgroup",$this->groupid,"gid",NULL,"datahandler.php");
	$this->groupnamefld->echo_html();
  }
  
  public static function group_list($groupfilter = NULL)
  {
    if(isset($groupfilter))
      $groups = inputclassbase::load_query("SELECT gid FROM sgroup WHERE active=1 AND groupname LIKE '". $groupfilter. "' ORDER BY groupname");
	else
      $groups = inputclassbase::load_query("SELECT gid FROM sgroup WHERE active=1 ORDER BY groupname");
	foreach($groups['gid'] AS $gid)
	  $grouplist[$gid] = new group($gid);
	return($grouplist);
  }
  
  public function get_mentor()
  {
    if($this->groupid == 0)
	  return NULL;
    $tid_mentor = inputclassbase::load_query("SELECT tid_mentor FROM sgroup WHERE active=1 AND gid=". $this->groupid);
		return new teacher($tid_mentor['tid_mentor'][0]);
  }
    
  public function edit_mentor()
  {
    if(!isset($this->mentorfld))
	  $this->mentorfld = new inputclass_listfield("gname". $this->groupid,"SELECT tid AS id, CONCAT(firstname,' ',lastname) AS tekst FROM teacher WHERE is_gone='N' ORDER BY lastname,firstname",NULL,"tid_mentor","sgroup",$this->groupid,"gid",NULL,"datahandler.php");
	$this->mentorfld->echo_html();
  }
  
  public function filter_package()
  {
    if($this->groupid == 0)
	  return FALSE;
	$qr = inputclassbase::load_query("SELECT `group` FROM subjectfiltergroups WHERE `group`=". $this->groupid);
	return isset($qr['group'][0]);
  }

  public function edit_package()
  {
    if($this->groupid == 0)
	  return FALSE;
	$qr = inputclassbase::load_query("SELECT `group` FROM subjectselectgroups WHERE `group`=". $this->groupid);
	return isset($qr['group'][0]);
  }
}
?>