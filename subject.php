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

class subject
{
  protected $mid;
  protected $shortnamefld,$fullnamefld,$typefld,$metafld;

  public function __construct($mid = NULL)
  {
    if(isset($mid))
	  $this->mid = $mid;
	else
	  $this->mid = 0;
  }

  public function load_current()
  {
    // Must load a list of possible subjects in current group and select the id of the first one.
	$possubs = subject::subject_list();
	if(isset($_SESSION['CurrentSubject']))
	{ // Need to see if current subject is valid!
		if(isset($possubs))
			foreach($possubs AS $asub)
			{
				if($asub->get_id() == $_SESSION['CurrentSubject'])
				{
					$this->mid = $_SESSION['CurrentSubject'];
					return;
				}
			}
	}
	if(isset($possubs))
	{
	  $this->mid = reset($possubs)->get_id();
	  // And signal it as the current subject!
	  $_SESSION['CurrentSubject'] = $this->mid;
	}
	else
	  $_SESSION['CurrentSubject'] = 0;
  }
  
  public function get_id()
  {
    return $this->mid;
  }
  
  public function get_cid()
  {
    // Must get it using the group
	$group = new group();
	$group->load_current();
	$cidinfo = inputclassbase::load_query("SELECT cid FROM class WHERE mid=". $this->mid. " AND gid=". $group->get_id());
	if(isset($cidinfo))
	  return($cidinfo['cid'][0]);
	else
	  return NULL;
  }
  
  public function get_teacher()
  {
    // Must get it using the group
	$group = new group();
	$group->load_current();
	$cidinfo = inputclassbase::load_query("SELECT tid FROM class WHERE mid=". $this->mid. " AND gid=". $group->get_id());
	if(isset($cidinfo))
	  return(new teacher($cidinfo['tid'][0]));
	else
	  return NULL;
  }
  
  public function get_shortname()
  {
    if($this->mid == 0)
	  return NULL;
	$this->get_shortnamefld();
	return($this->shortnamefld->__toString());
  }

  private function get_shortnamefld()
  {
    if(!isset($this->shortnamefld))
	{
	  $this->shortnamefld = new inputclass_textfield("sbshort". $this->mid,4,NULL,"shortname","subject",$this->mid,"mid",NULL,"datahandler.php");
	}
  }

  public function edit_shortname()
  {
    $this->get_shortnamefld();
	$this->shortnamefld->echo_html();
  }
  public function get_fullname()
  {
    if($this->mid == 0)
	  return NULL;
	$this->get_fullnamefld();
	return($this->fullnamefld->__toString());
  }

  private function get_fullnamefld()
  {
    if(!isset($this->fullnamefld))
	{
	  $this->fullnamefld = new inputclass_textfield("sbfull". $this->mid,4,NULL,"fullname","subject",$this->mid,"mid",NULL,"datahandler.php");
	}
  }

  public function edit_fulltname()
  {
    $this->get_fullnamefld();
	$this->fullnamefld->echo_html();
  }
  
  public static function subject_list($teacher = NULL, $group = NULL, $metasub = NULL)
  {
    // Subject list is related to teacher and group
		if(!isset($teacher))
		{
			$teacher = new teacher();
			$teacher->load_current();
		}
		if(!isset($group))
		{
			$group = new group();
			$group->load_current();
		}
    // Get the applicable subjects in an array.
		if(!isset($metasub))
		{ // List subjects independent of meta subjects
			if($group->get_id() == 0)
				$sql_query = "SELECT subject.mid FROM subject
									LEFT JOIN (SELECT meta_subject,COUNT(mid) AS mcnt FROM subject GROUP BY meta_subject) AS mx ON(subject.mid=mx.meta_subject)
						WHERE mid IS NOT NULL";
			else
				$sql_query = "SELECT subject.mid FROM (SELECT DISTINCT sid FROM sgrouplink WHERE gid=" .$group->get_id() . ") AS t1 LEFT JOIN sgrouplink USING(sid) 
									LEFT JOIN class USING(gid) LEFT JOIN subject USING (mid) 
									LEFT JOIN (SELECT meta_subject,COUNT(mid) AS mcnt FROM subject GROUP BY meta_subject) AS mx ON(subject.mid=mx.meta_subject)
						WHERE mid IS NOT NULL";
			if(!$teacher->has_role("admin") && !$teacher->has_role("office"))
				$sql_query .= " AND (class.tid='" . $teacher->get_id() . "' OR (subject.type = 'meta' AND mx.mcnt IS NULL)) GROUP BY mid";
		}
		else if(is_int($metasub) && $metasub == 0)
		{ // List subjects that are normal or is a meta subject with no sub-subjects
			$sql_query = "SELECT subject.mid FROM (SELECT DISTINCT sid FROM sgrouplink WHERE gid=" .$group->get_id() . ") AS t1 LEFT JOIN sgrouplink USING(sid) 
									LEFT JOIN class USING(gid) LEFT JOIN subject USING (mid) 
									LEFT JOIN (SELECT meta_subject,COUNT(mid) AS mcnt FROM subject GROUP BY meta_subject) AS mx ON(subject.mid=mx.meta_subject)
						WHERE mid IS NOT NULL AND (subject.type='normal' OR (subject.type='meta' AND mx.mcnt IS NULL))";
			if(!$teacher->has_role("admin") && !$teacher->has_role("office"))
				$sql_query .= " AND (class.tid='" . $teacher->get_id() . "' OR (subject.type = 'meta' AND mx.mcnt IS NULL)) GROUP BY mid";			
		}
		else
		{ // List subjects that have the given meta-subject
			$sql_query = "SELECT subject.mid FROM (SELECT DISTINCT sid FROM sgrouplink WHERE gid=" .$group->get_id() . ") AS t1 LEFT JOIN sgrouplink USING(sid) 
									LEFT JOIN class USING(gid) LEFT JOIN subject USING (mid) 
									LEFT JOIN (SELECT meta_subject,COUNT(mid) AS mcnt FROM subject GROUP BY meta_subject) AS mx ON(subject.mid=mx.meta_subject)
						WHERE mid IS NOT NULL AND subject.meta_subject=". $metasub->get_id();
			if(!$teacher->has_role("admin") && !$teacher->has_role("office"))
				$sql_query .= " AND (class.tid='" . $teacher->get_id() . "' OR (subject.type = 'meta' AND mx.mcnt IS NULL)) GROUP BY mid";			
		}
    $subjects = inputclassbase::load_query($sql_query);
		if(isset($subjects))
			foreach($subjects['mid'] AS $mid)
				$subjectlist[$mid] = new subject($mid);
		else
			return NULL;
		return($subjectlist);
  }

  public static function subject_metalist($teacher = NULL, $group = NULL)
  {
    // Subject list is related to teacher and group
		if(!isset($teacher))
		{
			$teacher = new teacher();
			$teacher->load_current();
		}
		if(!isset($group))
		{
			$group = new group();
			$group->load_current();
		}
    // Get the applicable subjects in an array.
    $sql_query = "SELECT subject.meta_subject FROM (SELECT DISTINCT sid FROM sgrouplink WHERE gid=" .$group->get_id() . ") AS t1 LEFT JOIN sgrouplink USING(sid) 
	              LEFT JOIN class USING(gid) LEFT JOIN subject USING (mid) 
	              LEFT JOIN (SELECT meta_subject,COUNT(mid) AS mcnt FROM subject GROUP BY meta_subject) AS mx ON(subject.mid=mx.meta_subject)
				  WHERE mid IS NOT NULL AND subject.meta_subject IS NOT NULL AND subject.meta_subject <> 0";
    if(!$teacher->has_role("admin") && !$teacher->has_role("office"))
      $sql_query .= " AND (class.tid='" . $teacher->get_id() . "' OR (subject.type = 'meta' AND mx.mcnt IS NULL))";
		$sql_query .= " GROUP BY meta_subject";
    $subjects = inputclassbase::load_query($sql_query);
		if(isset($subjects))
			foreach($subjects['meta_subject'] AS $mid)
				$subjectlist[$mid] = new subject($mid);
		else
			return NULL;
		return($subjectlist);
  }
}
?>