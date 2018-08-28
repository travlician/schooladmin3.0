<?
/* vim: set expandtab tabstop=2 shiftwidth=2: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2015 Aim4me N.V.  (http://www.aim4me.info)        |
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

class testdef
{
  protected $tdid;
  protected $short_descfld,$descfld,$datefld,$typefld,$periodfld,$cidfld,$yearfld,$weekfld,$domainfld,$termfld,$durationfld,$assignfld,$toolsfld,$realisedfld;

  public function __construct($tdid = NULL)
  {
    if(isset($tdid))
			$this->tdid = $tdid;
		else
			$this->tdid = 0;
  }

  public function get_id()
  {
    return $this->tdid;
  }
  
  public function get_short_desc()
  {
    if($this->tdid <= 0)
	  return NULL;
		$this->get_short_descfld();
		return($this->short_descfld->__toString());
  }
  
  private function get_short_descfld()
  {
    if(!isset($this->short_descfld))
		{
			$this->short_descfld = new inputclass_textfield("tdsdesc". $this->tdid,5,NULL,"short_desc","testdef",$this->tdid,"tdid",NULL,"datahandler.php");
		}
  }

  public function edit_short_desc()
  {
    $this->get_short_descfld();
		$this->short_descfld->echo_html();
  }
  
  public function get_desc()
  {
    if($this->tdid <= 0)
			return NULL;
		$this->get_descfld();
		return($this->descfld->__toString());
  }
  
  private function get_descfld($cid=NULL,$date=NULL)
  {
    if(!isset($this->descfld))
		{
			$this->descfld = new inputclass_textarea("tddesc". $this->tdid,"20,*",NULL,"description","testdef",$this->tdid,"tdid",NULL,"datahandler.php");
			if(isset($cid) || isset($date))
				$this->set_cid_date($this->descfld,$cid,$date);
			else if($this->tdid <= 0)
			{
				$subj = new subject();
				$subj->load_current();
				$this->descfld->set_extrafield("cid",$subj->get_cid());
				$curyear = inputclassbase::load_query("SELECT year FROM period ORDER BY id");
				$this->descfld->set_extrafield("year",$curyear['year'][0]);
			}
		}
  }

  public function edit_desc($cid=NULL,$date=NULL,$adminflg=false)
  {
    $this->get_descfld($cid,$date);
		if($adminflg)
			$this->descfld->set_extrafield("admindefined",1);
		$this->descfld->echo_html();
  }
  
  public function get_date()
  {
    if($this->tdid <= 0)
	  return NULL;
	$this->get_datefld();
	return($this->datefld->__toString());
  }
  
  private function get_datefld()
  {
    global $locktestperiods;
    if(!isset($this->datecfld))
		{
			$this->datefld = new inputclass_datefield("tddate". $this->tdid,NULL,NULL,"date","testdef",$this->tdid,"tdid",NULL,"datahandler.php");
			// Disable dates that are not valid in the current periods
			$me = new teacher();
			$me->load_current();
			if($me->has_role("admin") || !isset($locktestperiods))
				$perdates = inputclassbase::load_query("SELECT * FROM period ORDER BY startdate");
			else
				$perdates = inputclassbase::load_query("SELECT * FROM period where status='open' ORDER BY startdate");
			foreach($perdates['startdate'] AS $pdix => $pst)
			{
				if(!isset($enstart) || $pst < $enstart)
				{  // We got new starting date, set that as the first enabled date.
					$this->datefld->set_parameter("dc-earliestdate",substr($pst,5,2). substr($pst,8,2). substr($pst,0,4));
					$enstart = $pst;
				}
				if(!isset($enend) || $perdates['enddate'][$pdix] > $enend)
				{ // We got a new ending date, set that as the latest enabled date
					$enend = $perdates['enddate'][$pdix];
					$this->datefld->set_parameter("dc-latestdate",substr($enend,5,2). substr($enend,8,2). substr($enend,0,4));
				}
			}
		}
  }

  public function edit_date()
  {
    $this->get_datefld();
		$this->datefld->echo_html();
  }
  
  public function get_type()
  {
    if($this->tdid <= 0)
	  return NULL;
		$this->get_typefld();
		return($this->typefld->__toString());
  }
  
  private function get_typefld($cid=NULL,$date=NULL)
  {
    if(!isset($this->typefld))
		{
			$this->typefld = new inputclass_listfield("tdtype". $this->tdid,"SELECT '' AS id, '' AS tekst UNION SELECT `type`, `type` FROM testtype ORDER BY id",NULL,"type","testdef",$this->tdid,"tdid",NULL,"datahandler.php");
			if(isset($cid) || isset($date))
				$this->set_cid_date($this->typefld,$cid,$date);
		}
  }

  public function edit_type($cid=NULL,$date=NULL)
  {
    $this->get_typefld($cid,$date);
		$this->typefld->echo_html();
  }
  
  public function get_period()
  {
    if($this->tdid <= 0)
	  return NULL;
		$this->get_periodfld();
		return($this->periodfld->__toString());
  }
  
  private function get_periodfld()
  {
    if(!isset($this->periodfld))
		{
			$me = new teacher();
			$me->load_current();
			if($me->has_role("admin"))
			{
				if($this->get_id() <= 0)
					$this->periodfld = new inputclass_listfield("tdperiod". $this->tdid,"SELECT '' id, '' tekst UNION SELECT id, id AS tekst FROM period ORDER BY id",NULL,"period","testdef",$this->tdid,"tdid",NULL,"datahandler.php");
				else
					$this->periodfld = new inputclass_listfield("tdperiod". $this->tdid,"SELECT period AS id, period AS tekst FROM testdef WHERE tdid=". $this->tdid. " UNION SELECT id, id AS tekst FROM period ORDER BY id",NULL,"period","testdef",$this->tdid,"tdid",NULL,"datahandler.php");
			}
			else
			{
				if($this->get_id() <= 0)
					$this->periodfld = new inputclass_listfield("tdperiod". $this->tdid,"SELECT '' id, '' tekst UNION SELECT id, id AS tekst FROM period WHERE status='open' ORDER BY id",NULL,"period","testdef",$this->tdid,"tdid",NULL,"datahandler.php");
				else
					$this->periodfld = new inputclass_listfield("tdperiod". $this->tdid,"SELECT period AS id, period AS tekst FROM testdef WHERE tdid=". $this->tdid. " UNION SELECT id, id AS tekst FROM period WHERE status='open' ORDER BY id",NULL,"period","testdef",$this->tdid,"tdid",NULL,"datahandler.php");
			}
		}
  }

  public function edit_period()
  {
    global $locktestperiods;
    $this->get_periodfld();
		$me = new teacher();
		$me->load_current();
    if(!$me->has_role("admin") && isset($locktestperiods))
	  $this->periodfld->set_readonly();
		$this->periodfld->echo_html();
  }
  
  public function get_cid()
  {
    if($this->tdid <= 0)
	  return NULL;
		$this->get_cidfld();
		return($this->cidfld->__toString());
  }
  
  protected function get_icid()
  {  // Returns cid for this object w/o group filter
    if($this->get_id() > 0)
		{
			$cidqr = inputclassbase::load_query("SELECT cid FROM testdef WHERE tdid=". $this->get_id());
			if(isset($cidqr['cid'][0]))
				return($cidqr['cid'][0]);
		}
		return NULL;
  }
  
  private function get_cidfld()
  {
    if(!isset($this->cidfld))
		{
			$this->cidfld = new inputclass_listfield("tdcid". $this->tdid,"SELECT cid AS id, cid AS tekst FROM `class` LEFT JOIN subject USING(mid) LEFT JOIN sgroup USING(gid) WHERE active=1 AND groupname='". $_SESSION['CurrentGroup']. "' ORDER BY shortname",NULL,"cid","testdef",$this->tdid,"tdid",NULL,"datahandler.php");
		}
  }

  public function edit_cid()
  {
    $this->get_cidfld();
		$this->cidfld->echo_html();
  }
  
  public function get_year()
  {
    if($this->tdid <= 0)
	  return NULL;
		$this->get_yearfld();
		return($this->yearfld->__toString());
  }
  
  private function get_yearfld()
  {
    if(!isset($this->yearfld))
		{
			$this->yearfld = new inputclass_textfield("tdyear". $this->tdid,"10",NULL,"year","testdef",$this->tdid,"tdid",NULL,"datahandler.php");
		}
  }

  public function edit_year()
  {
    $this->get_yearfld();
		$this->yearfld->echo_html();
  }
  
  public function get_week()
  {
    if($this->tdid <= 0)
	  return NULL;
		$this->get_weekfld();
		return($this->weekfld->__toString());
  }
  
  private function get_weekfld()
  {
    if(!isset($this->weekfld))
		{
			$this->weekfld = new inputclass_textfield("tdweek". $this->tdid,2,NULL,"week","testdef",$this->tdid,"tdid",NULL,"datahandler.php");
		}
  }

  public function edit_week()
  {
    $this->get_weekfld();
		$this->weekfld->echo_html();
  }
  
  public function get_domain()
  {
    if($this->tdid <= 0)
	  return NULL;
		$this->get_domainfld();
		return($this->domainfld->__toString());
  }
  
  private function get_domainfld()
  {
    if(!isset($this->domainfld))
		{
			$this->domainfld = new inputclass_textfield("tddomain". $this->tdid,10,NULL,"domain","testdef",$this->tdid,"tdid",NULL,"datahandler.php");
		}
  }

  public function edit_domain()
  {
    $this->get_domainfld();
		$this->domainfld->echo_html();
  }
  
  public function get_term()
  {
    if($this->tdid <= 0)
	  return NULL;
		$this->get_termfld();
		return($this->termfld->__toString());
  }
  
  private function get_termfld()
  {
    if(!isset($this->termfld))
		{
			$this->termfld = new inputclass_textarea("tdterm". $this->tdid,"10,*",NULL,"term","testdef",$this->tdid,"tdid",NULL,"datahandler.php");
		}
  }

  public function edit_term()
  {
    $this->get_termfld();
		$this->termfld->echo_html();
  }
  
  public function get_duration()
  {
    if($this->tdid <= 0)
	  return NULL;
		$this->get_durationfld();
		return($this->durationfld->__toString());
  }
  
  private function get_durationfld()
  {
    if(!isset($this->durationfld))
		{
			$this->durationfld = new inputclass_textfield("tdduration". $this->tdid,10,NULL,"duration","testdef",$this->tdid,"tdid",NULL,"datahandler.php");
		}
  }

  public function edit_duration()
  {
    $this->get_durationfld();
		$this->durationfld->echo_html();
  }
  
  public function get_assign()
  {
    if($this->tdid <= 0)
	  return NULL;
		$this->get_assignfld();
		return($this->assignfld->__toString());
  }
  
  private function get_assignfld($cid = NULL, $date = NULL)
  {
    if(!isset($this->assignfld))
		{
			$this->assignfld = new inputclass_textarea("tdassign". $this->tdid,"10,*",NULL,"assignments","testdef",$this->tdid,"tdid",NULL,"datahandler.php");
			if(isset($cid) || isset($date))
				$this->set_cid_date($this->assignfld,$cid,$date);
		}
  }

  public function edit_assign($cid=NULL,$date=NULL)
  {
    $this->get_assignfld($cid,$date);
		$this->assignfld->echo_html();
  }
  
  public function get_tools()
  {
    if($this->tdid <= 0)
	  return NULL;
		$this->get_toolsfld();
		return($this->toolsfld->__toString());
  }
  
  private function get_toolsfld()
  {
    if(!isset($this->toolsfld))
		{
			$this->toolsfld = new inputclass_textfield("tdtools". $this->tdid,10,NULL,"tools","testdef",$this->tdid,"tdid",NULL,"datahandler.php");
		}
  }

  public function edit_tools()
  {
    $this->get_toolsfld();
		$this->toolsfld->echo_html();
  }
  
  public function get_realised()
  {
    if($this->tdid <= 0)
	  return NULL;
		$this->get_realisedfld();
		return($this->realisedfld->__toString());
  }
  
  private function get_realisedfld($cid=NULL,$date=NULL)
  {
    if(!isset($this->realisedfld))
		{
			if($this->need_results())
					$style = 'background-color: #FFC0C0';
			else if($this->all_results())
					$style = 'background-color: #C0FFC0';
			else
				$style = '';
			$this->realisedfld = new inputclass_textarea("tdrealised". $this->tdid,"20,*",NULL,"realised","testdef",$this->tdid,"tdid",$style,"datahandler.php");
			if(isset($cid) || isset($date))
				$this->set_cid_date($this->realisedfld,$cid,$date);
			if($this->need_results())
				$this->realisedfld->set_initial_value($this->list_missing_names(),true);
		}
  }

  public function edit_realised($cid=NULL,$date=NULL)
  {
    $this->get_realisedfld($cid,$date);
		$this->realisedfld->echo_html();
  }
  
  public function get_last_update()
  {
    if($this->tdid <= 0)
	  return NULL;
		$this->get_last_updatefld();
		$udat = $this->last_updatefld->__toString();
		return(substr($udat,8,2). substr($udat,4,4). substr($udat,0,4). substr($udat,10,6));
  }
  
  private function get_last_updatefld()
  {
    if(!isset($this->last_updatefld))
		{
			$this->last_updatefld = new inputclass_textfield("tdlast_update". $this->tdid,20,NULL,"last_update","testdef",$this->tdid,"tdid",NULL,"datahandler.php");
		}
  }

  public function may_edit()
  {
		$lockeddata = inputclassbase::load_query("SELECT locked FROM testdef WHERE tdid=". $this->get_id());
		if(isset($lockeddata['locked'][0]) && $lockeddata['locked'][0] == 1)
			return FALSE;
		else
			return TRUE;
  }
  
  public function get_admindefined()
  {
		$lockeddata = inputclassbase::load_query("SELECT admindefined FROM testdef WHERE tdid=". $this->get_id());
		if(isset($lockeddata['admindefined'][0]) && $lockeddata['admindefined'][0] == 1)
			return TRUE;
		else
			return FALSE;
  }
  
  public function toggle_lock()
  {
    mysql_query("UPDATE testdef SET locked=IF(locked=1,0,1) WHERE tdid=". $this->get_id());
  }
  
  public function need_results()
  {
		global $testnotmadealternative;
    if($this->tdid <= 0)
	  return false;
		$mygroup = inputclassbase::load_query("SELECT gid FROM class WHERE cid=". $this->get_icid());
		$studcount = inputclassbase::load_query("SELECT COUNT(sid) AS res FROM sgrouplink WHERE gid=". $mygroup['gid'][0]. " AND sid<>0");
		$testcount = inputclassbase::load_query("SELECT COUNT(sid) AS res FROM testresult WHERE result IS NOT NULL". (isset($testnotmadealternative) ? " AND result <> \"". $testnotmadealternative. "\"" : ""). " AND tdid=". $this->tdid);
		if($testcount['res'][0] == 0 || $testcount['res'][0] >= $studcount['res'][0])
			return false;
		else
			return true;
  }
  
  public function all_results()
  {
    if($this->tdid <= 0)
	  return false;
		$mygroup = inputclassbase::load_query("SELECT gid FROM class WHERE cid=". $this->get_icid());
		$studcount = inputclassbase::load_query("SELECT COUNT(sid) AS res FROM sgrouplink WHERE gid=". $mygroup['gid'][0]. " AND sid<>0");
		$testcount = inputclassbase::load_query("SELECT COUNT(sid) AS res FROM testresult WHERE result IS NOT NULL AND tdid=". $this->tdid);
		return $testcount['res'][0] >= $studcount['res'][0];
  }
	
	public function list_missing_names($oderquery = "lastname,firstname")
	{
		global $testnotmadealternative;
    if($this->tdid <= 0)
			return ("");
		$mygroup = inputclassbase::load_query("SELECT gid FROM class WHERE cid=". $this->get_icid());
		$namesq = "SELECT GROUP_CONCAT(firstname,' ',lastname) AS names FROM sgrouplink LEFT JOIN student USING(sid) LEFT JOIN (SELECT sid,result FROM testresult WHERE tdid=". $this->tdid. ") AS t1 USING(sid) WHERE (result IS NULL". (isset($testnotmadealternative) ? " OR result=\"". $testnotmadealternative. "\"" : ""). ") AND gid=". $mygroup['gid'][0]. " ORDER BY ". $oderquery;
		$namesqr = inputclassbase::load_query($namesq);
		if(isset($namesqr['names']))
			return($namesqr['names'][0]);
		else
			return("");		
	}
  
  public static function testdef_list($group = NULL,$subject = NULL)
  {
    if(!isset($group))
		{
			$group = new group();
			$group->load_current();
		}
		if(!isset($subject))
			$subject = $_SESSION['CurrentSubject'];
		// Get the current year
		$yearinfo = inputclassbase::load_query("SELECT year FROM period ORDER BY id LIMIT 1");
		$yearnow = $yearinfo['year'][0];
		$query = "SELECT tdid FROM testdef LEFT JOIN class USING(cid) WHERE gid=". $group->get_id(). " AND mid=". $subject. " AND year='". $yearnow. "' ORDER BY date DESC";
			$tdefs = inputclassbase::load_query($query);
		if(isset($tdefs['tdid']))
		{
			foreach($tdefs['tdid'] AS $tdid)
				$tdlist[$tdid] = new testdef($tdid);
			return($tdlist);
		}
		else
			return NULL;
  }

  public static function testdef_listgroup($group = NULL)
  {
    if(!isset($group))
		{
			$group = new group();
			$group->load_current();
		}
		// Get the current year
		$yearinfo = inputclassbase::load_query("SELECT year FROM period ORDER BY id LIMIT 1");
		$yearnow = $yearinfo['year'][0];
		$query = "SELECT tdid FROM testdef LEFT JOIN class USING(cid) WHERE gid=". $group->get_id(). " AND year='". $yearnow. "' ORDER BY date";
			$tdefs = inputclassbase::load_query($query);
		if(isset($tdefs['tdid']))
		{
			foreach($tdefs['tdid'] AS $tdid)
				$tdlist[$tdid] = new testdef($tdid);
			return($tdlist);
		}
		else
			return NULL;
		}
		public function shift_foreward()
		{
			global $userlink;
			$year = $this->get_year();
			$cid = $this->get_cid();
			$mydate = $this->get_date();
			$inquery = "SELECT tdid,date FROM testdef WHERE year='". $year. "' AND cid=". $cid. " AND date >= '". inputclassbase::nldate2mysql($mydate). "' ORDER BY date DESC";
			$iqr = inputclassbase::load_query($inquery);
			unset($curdate);
			if(isset($iqr['tdid']))
				foreach($iqr['tdid'] AS $tdix => $tdid)
				{
					if(isset($curdate))
				{
					mysql_query("UPDATE testdef SET date='". $curdate. "' WHERE tdid=". $tdid);
				}
				$curdate = $iqr['date'][$tdix];
				}
		}
	public static function get_test($cid,$date)
	{
		$tests = inputclassbase::load_query("SELECT tdid FROM testdef WHERE cid=". $cid. " AND date='". date("Y-m-d",$date). "'");
		if(isset($tests))
			return(new testdef($tests['tdid'][0]));
		else
			return NULL;
  }
  
  private function set_cid_date($fld,$cid = NULL, $date = NULL)
  {
    if(isset($cid))
	  $fld->set_extrafield("cid",$cid);
		if(isset($date))
		{ // We set extra fields for date, period, short description (based on date), period, year and week
			$fld->set_extrafield("week", date("W", $date));
			$fld->set_extrafield("date", date("Y-m-d", $date));
			$fld->set_extrafield("short_desc", date("dm", $date));
			// year and period must come from period data
			$perdata = inputclassbase::load_query("SELECT * FROM period WHERE startdate <= '". date("Y-m-d", $date). "' AND enddate >= '". date("Y-m-d", $date). "'");
			if(isset($perdata['id'][0]))
			{
				$fld->set_extrafield("period", $perdata['id'][0]);
				$fld->set_extrafield("year", $perdata['year'][0]);
			}
		}
  }
}
?>