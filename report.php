<?
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2016 Aim4me N.V.  (http://www.aim4me.info)        |
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
require_once("student.php");
require_once("group.php");
require_once("reportcategory.php");

class report
{
  protected $rid;
  protected $typefld,$teachfld,$sidfld,$protectfld,$datefld,$summaryfld,$contentfld,$catgfld,$catefld;
  protected static $typequery;
  protected static $protquery;
  protected $subject;

  public function __construct($rid = NULL)
  {
    if(isset($rid))
			$this->rid = $rid;
		else
			$this->rid = 0;
    $this->typequery = "SELECT 'F' AS id, 'F' AS tekst UNION SELECT 'T','T' UNION SELECT 'C','C' UNION SELECT 'X','X'";
  }
  
  public function get_id()
  {
    return $this->rid;
  }
  
  public function get_type()
  {
    if(!isset($this->typefld))
			$this->typefld = new inputclass_listfield("reptype". $this->rid,$this->typequery,NULL,"type","reports",$this->rid,"rid",NULL,"datahandler.php","lastmodifiedby", $_SESSION['uid']);
		return $this->typefld->__toString();
  }
  
  public function edit_type($grpflg)
  {
    if(isset($grpflg) && $grpflg)
		{
			$tquery = "SELECT 'C' AS id,\"". $_SESSION['dtext']['use_rep_text']. "\" AS tekst UNION SELECT 'X',\"". $_SESSION['dtext']['use_file']. "\"";
		}
		else
		{
			$tquery = "SELECT 'T' AS id,\"". $_SESSION['dtext']['use_rep_text']. "\" AS tekst UNION SELECT 'F',\"". $_SESSION['dtext']['use_file']. "\"";
		}
    $this->typefld = new inputclass_listfield("reptype". $this->rid,$tquery,NULL,"type","reports",$this->rid,"rid",NULL,"datahandler.php");
		$this->typefld->set_extrafield("tid", $_SESSION['uid']);
		$this->typefld->set_extrafield("sid",$this->get_subject()->get_id());
		$this->typefld->echo_html();
  }
  
  public function get_teacher()
  {
    if(!isset($this->teachfld))
			$this->teachfld = new inputclass_textfield("repteachro". $this->rid,10,NULL,"tid","reports",$this->rid,"rid");
		return new teacher($this->teachfld->__toString());
  }
  
  public function get_protect()
  {
    if(!isset($this->protectfld))
			$this->protectfld = new inputclass_listfield("repprot". $this->rid,self::get_protect_query(),NULL,"protect","reports",$this->rid,"rid",NULL,"datahandler.php");
		return $this->protectfld->__toString();
  }
  
  public function edit_protect()
  {
    if(!isset($this->protectfld))
			$this->protectfld = new inputclass_listfield("repprot". $this->rid,self::get_protect_query(),NULL,"protect","reports",$this->rid,"rid",NULL,"datahandler.php");
		$this->protectfld->set_extrafield("tid", $_SESSION['uid']);
		$this->protectfld->set_extrafield("sid",$this->get_subject()->get_id());
		$this->protectfld->echo_html();
  }
  
  public function get_date()
  {
    if(!isset($this->datefld))
			$this->datefld = new inputclass_datefield("repdate". $this->rid,date("d-m-Y"),NULL,"date","reports",$this->rid,"rid",NULL,"datahandler.php","lastmodifiedby", $_SESSION['uid']);
		return $this->datefld->__toString();
  }
  
  public function edit_date()
  {
    if(!isset($this->datefld))
			$this->datefld = new inputclass_datefield("repdate". $this->rid,date("d-m-Y"),NULL,"date","reports",$this->rid,"rid",NULL,"datahandler.php","tid", $_SESSION['uid']);
		$this->datefld->set_extrafield("tid", $_SESSION['uid']);
		$this->datefld->set_extrafield("sid",$this->get_subject()->get_id());
		$this->datefld->echo_html();
  }
	
	public function get_category()
	{
		if(!isset($this->catgfld))
			$this->catgfld = new inputclass_listfield("repcat". $this->rid,"SELECT '' AS id, '' AS tekst UNION SELECT rcid,name FROM reportcats ORDER BY id", NULL, "rcid","reports",$this->rid,"rid",NULL,"datahandler.php");
		return $this->catgfld->__toString();
	}
	
	public function edit_category()
	{
		if(!isset($this->catefld))
			$this->catefld = new inputclass_listfield("repcat". $this->rid,reportcategory::get_catqry(), NULL, "rcid","reports",$this->rid,"rid",NULL,"datahandler.php");
		$this->catefld->set_extrafield("tid", $_SESSION['uid']);
		$this->catefld->set_extrafield("sid",$this->get_subject()->get_id());
		$this->catefld->echo_html();
	}
  
  public function get_summary()
  {
    if(!isset($this->summaryfld))
			$this->summaryfld = new inputclass_textarea("repsum". $this->rid,"80,*",NULL,"summary","reports",$this->rid,"rid",NULL,"datahandler.php");
		return $this->summaryfld->__toString();    
  }
  
  public function edit_summary()
  {
    if(!isset($this->summaryfld))
			$this->summaryfld = new inputclass_textarea("repsum". $this->rid,"80,*",NULL,"summary","reports",$this->rid,"rid",NULL,"datahandler.php");
		$this->summaryfld->set_extrafield("tid",$_SESSION['uid']);
		$this->summaryfld->set_extrafield("sid",$this->get_subject()->get_id());
		$this->summaryfld->echo_html();    
  }
  
  public function get_contents()
  {
    $type = $this->get_type();
		if($type == "F" || $type == "X")
		{
				$retstr = "<a href=getreport.php?". $this->rid. ">" . $_SESSION['dtext']['vrep_expl_5'] . "</a>";
				// Allow the download link to work by registering and setting report id!
				$_SESSION['rid'] = $ReportID;
			return($retstr);
		}
		else
		{
				if(!isset($this->contentsfld))
				$this->contentsfld = new inputclass_textarea("repcont". $this->rid,"80,*",NULL,"content","reports",$this->rid,"rid",NULL,"datahandler.php","lastmodifiedby", $_SESSION['uid']);
			return $this->contentsfld->__toString();    
		}
  }
  
  public function edit_contents($grpflg)
  {
    $this->contentsfld = new inputclass_textarea("repcont". $this->rid,"80,*",NULL,"content","reports",$this->rid,"rid",NULL,"datahandler.php","tid", $_SESSION['uid']);
		$this->contentsfld->set_extrafield("type",(isset($grpflg) && $grpflg) ? "C" : "T");
		$this->contentsfld->set_extrafield("tid", $_SESSION['uid']);
		$this->contentsfld->set_extrafield("sid",$this->get_subject()->get_id());
		$this->contentsfld->echo_html();
  }
  
  public function get_last_update()
  {
    $lupd = inputclassbase::load_query("SELECT LastUpdate FROM reports WHERE rid=". $this->rid);
		$lu = $lupd['LastUpdate'][0];
		return(substr($lu,8,2). "-". substr($lu,5,2). "-". substr($lu,0,4). substr($lu,10));
  }
  
  public function get_subject()
  {
    if(!isset($this->subject))
		{
      $subj = inputclassbase::load_query("SELECT sid FROM reports WHERE rid=". $this->rid);
			$sid = $subj['sid'][0];
				if($this->get_type() == "F" || $this->get_type() == "T")
			{ // student
				$this->subject = new student($sid);
			}
			else
			{ // Group
				$this->subject = new group($sid);
			}
		}
		return($this->subject);
  }
  
  public function set_subject($subject)
  {
    $this->subject = $subject;
  }
  
  public static function report_group_list($group = NULL, $teacher = NULL, $fromdate=NULL,$repcat=0)
  {
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
		// We only get reports on or after fromdate. If fromdate is not set we get the lowest date in period startdates
    if(!isset($fromdate))
    {
			$fdqr = inputclassbase::load_query("SELECT MIN(startdate) AS fd FROM period");
			$fromdate = $fdqr['fd'][0];
    }	
    $sql_query = "SELECT DISTINCT reports.rid";
    $sql_query .= " FROM reports LEFT JOIN teacher USING(tid) LEFT JOIN sgroup ON (reports.sid=sgroup.gid)";
    $sql_query .= " WHERE (reports.type='C' OR reports.type='X')";
    $sql_query .= " AND sid='". $group->get_id()."'";
		$sql_query .= " AND date >= '". $fromdate. "'";
    // extra limits that apply to non counseller teachers only
    if(!$teacher->has_role("counsel"))
    {
      $sql_query .= " AND (protect='A' OR protect='T' OR (protect='M' AND sgroup.tid_mentor='". $teacher->get_id(). "') OR reports.tid='". $teacher->get_id(). "')";
    }
		// If procted is set to N (None), only the author can see it
		$sql_query .= " AND (protect <> 'N' OR reports.tid='". $teacher->get_id(). "')";
		if($repcat != 0)
			$sql_query .= " AND rcid=". $repcat;
    $sql_query .= " ORDER BY reports.date DESC";

    $reports = inputclassbase::load_query($sql_query);
		if(isset($reports))
		{
			foreach($reports['rid'] AS $rid)
				$reportlist[$rid] = new report($rid);
			return($reportlist);
		}
		else
			return NULL;
  }

  public static function report_student_list($student, $teacher = NULL, $fromdate=NULL, $repcat=0)
  {
    if(!isset($teacher))
		{
			$teacher = new teacher();
			$teacher->load_current();
		}
    if(!isset($fromdate))
    {
			$fdqr = inputclassbase::load_query("SELECT MIN(startdate) AS fd FROM period");
			$fromdate = $fdqr['fd'][0];
    }	
    $sql_query = "SELECT DISTINCT reports.rid";
    $sql_query .= " FROM reports LEFT JOIN teacher USING(tid) LEFT JOIN student USING(sid) LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid)";
    $sql_query .= " WHERE (reports.type='F' OR reports.type='T')";
    $sql_query .= " AND sid='". $student->get_id()."'";
		$sql_query .= " AND date >= '". $fromdate. "'";
		if($repcat > 0)
			$sql_query .= " AND rcid=". $repcat;
    // extra limits that apply to non counseller teachers only
    if(!$teacher->has_role("counsel"))
    {
      $sql_query .= " AND (protect='A' OR protect='T' OR (protect='M' AND sgroup.tid_mentor='";
			$sql_query .= $teacher->get_id(). "')";
			$sql_query .= " OR reports.tid='". $teacher->get_id(). "')";
			}
		$sql_query .= " GROUP BY rid";
    $sql_query .= " ORDER BY reports.date DESC";
    $reports = inputclassbase::load_query($sql_query);
		if(isset($reports))
		{
			foreach($reports['rid'] AS $rid)
				$reportlist[$rid] = new report($rid);
			return($reportlist);
		}
		else
			return NULL;
  }
  
  public static function get_protect_query()
  {
    if(!isset(self::$protquery))
		{
			$prottab = array("A"=>$_SESSION['dtext']['allow_all'],"T"=>$_SESSION['dtext']['allow_teach'],"M"=>$_SESSION['dtext']['allow_mentcouns'],"C"=>$_SESSION['dtext']['allow_couns'],"N"=>$_SESSION['dtext']['allow_none']);
			$defprotqr = inputclassbase::load_query("SELECT defrepaccess FROM teacher WHERE tid=". $_SESSION['uid']);
			if(isset($defprotqr['defrepaccess'][0]))
				$defprot = $defprotqr['defrepaccess'][0];
			$q = "SELECT '". $defprot. "' AS id,\"". $prottab[$defprot]. "\" AS tekst";
			foreach($prottab AS $ptix => $pttxt)
				if($ptix != $defprot)
					$q .= " UNION SELECT '". $ptix. "',\"". $pttxt. "\"";
			self::$protquery = $q;
		}
		return(self::$protquery); 
  }
}
?>