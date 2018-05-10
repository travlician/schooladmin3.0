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
require_once("group.php");
require_once("subject.php");

class sclass
{
  protected $cid;
  protected $groupfld,$subjectfld,$teacherfld,$masterlinkfld,$sequencefld;

  public function __construct($cid = NULL)
  {
    if(isset($cid))
	  $this->cid = $cid;
	else
	  $this->cid = 0;
  }
  
  public function get_id()
  {
    return $this->cid;
  }
  
  public function get_group()
  {
    if($this->cid == 0)
	  return NULL;
	$gidq = inputclassbase::load_query("SELECT gid FROM `class` WHERE cid=". $this->cid);
	if(isset($gidq['gid'][0]))
	  return(new group($gidq['gid'][0]));
	else
	  return NULL;
  }

  public function get_teacher()
  {
    if($this->cid == 0)
	  return NULL;
	$gidq = inputclassbase::load_query("SELECT tid FROM `class` WHERE cid=". $this->cid);
	if(isset($gidq['tid'][0]))
	  return(new teacher($gidq['tid'][0]));
	else
	  return NULL;
  }

  public function get_subject()
  {
    if($this->cid == 0)
	  return NULL;
	$gidq = inputclassbase::load_query("SELECT mid FROM `class` WHERE cid=". $this->cid);
	if(isset($gidq['mid'][0]))
	  return(new subject($gidq['mid'][0]));
	else
	  return NULL;
  }

  public function get_groupname()
  {
    if($this->cid == 0)
	  return NULL;
	$this->get_groupfld();
	return($this->groupfld->__toString());
  }
  
  private function get_groupfld()
  {
    if(!isset($this->groupfld))
	{
	  $this->groupfld = new inputclass_listfield("cgname". $this->cid,"SELECT 0 AS id, '' AS tekst UNION SELECT gid, groupname FROM sgroup WHERE active=1 ORDER BY tekst",NULL,"gid","class",$this->cid,"cid",NULL,"datahandler.php");
	}
  }

  public function edit_groupname()
  {
    $this->get_groupfld();
	$this->groupfld->echo_html();
  }

  public function get_subjectname()
  {
    if($this->cid == 0)
	  return NULL;
	$this->get_subjectfld();
	return($this->subjectfld->__toString());
  }
  
  private function get_subjectfld()
  {
    if(!isset($this->subjectfld))
	{
	  $this->subjectfld = new inputclass_listfield("csubject". $this->cid,"SELECT 0 AS id, '' AS tekst UNION SELECT mid, fullname FROM subject ORDER BY tekst",NULL,"mid","class",$this->cid,"cid",NULL,"datahandler.php");
	}
  }

  public function edit_subject()
  {
    $this->get_subjectfld();
	$this->subjectfld->echo_html();
  }

  public function get_teachername()
  {
    if($this->cid == 0)
	  return NULL;
	$this->get_teacherfld();
	return($this->teacherfld->__toString());
  }
  
  private function get_teacherfld()
  {
    if(!isset($this->teacherfld))
	{
	  $this->teacherfld = new inputclass_listfield("cteacher". $this->cid,"SELECT 0 AS id, '' AS tekst UNION SELECT tid, CONCAT(firstname,' ',lastname) FROM teacher ORDER BY tekst",NULL,"tid","class",$this->cid,"cid",NULL,"datahandler.php");
	}
  }

  public function edit_teachername()
  {
    $this->get_teacherfld();
	$this->teacherfld->echo_html();
  }

  public function get_masterlink()
  {
    if($this->cid == 0)
	  return NULL;
	$this->get_masterlinkfld();
	return($this->masterlinkfld->__toString());
  }
  
  private function get_masterlinkfld()
  {
    if(!isset($this->masterlinkfld))
	{
	  $this->masterlinkfld = new inputclass_textfield("cmasterlink". $this->cid,2,NULL,"masterlink","class",$this->cid,"cid",NULL,"datahandler.php");
	}
  }

  public function edit_masterlink()
  {
    $this->get_masterlinkfld();
	$this->masterlinkfld->echo_html();
  }
  public function get_sequence()
  {
    if($this->cid == 0)
	  return NULL;
	$this->get_sequencefld();
	return($this->sequencefld->__toString());
  }
  
  private function get_sequencefld()
  {
    if(!isset($this->sequencefld))
	{
	  $this->sequencefld = new inputclass_textfield("csequence". $this->cid,2,NULL,"show_sequence","class",$this->cid,"cid",NULL,"datahandler.php");
	}
  }

  public function edit_sequence()
  {
    $this->get_sequencefld();
	$this->sequencefld->echo_html();
  }
}
?>