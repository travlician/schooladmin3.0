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
require_once("group.php");

class testdefinition
{
  protected $tdid;
  protected $short_fld,$desc_fld,$date_fld,$type_fld,$period_fld,$week_fld,$domain_fld,$term_fld,$duration_fld,$assign_fld,$tools_fld,$realised_fld;

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
  
  public function get_short()
  {
    if($this->tdid <= 0)
	  return NULL;
	else
	 return($this->get_short_fld()->__toString());
  }
  
  protected function get_short_fld()
  {
    if(!isset($this->short_fld))
	  $this->short_fld = new inputclass_textfield("tdshort". $this->tdid,5,NULL,"short_desc","testdef",$this->tdid,"tdid",NULL,"testdhandler.php","lastmodifiedby", $_SESSION['uid']);
	return $this->short_fld;  
  }
  public function edit_short()
  {
	$this->get_short_fld()->echo_html();
  }
  
  public function get_description()
  {
    if($this->tdid <= 0)
	  return NULL;
	else
	  return($this->get_desc_fld()->__toString());
  }
  
  protected function get_desc_fld()
  {
    if(!isset($this->desc_fld))
	  $this->desc_fld = new inputclass_textarea("tddesc". $this->tdid,"1,*",NULL,"description","testdef",$this->tdid,"tdid",NULL,"testdhandler.php","lastmodifiedby", $_SESSION['uid']);
	return $this->desc_fld;  
  }
  public function edit_description()
  {
	$this->get_desc_fld()->echo_html();
  }
  
  public function get_date()
  {
    if($this->tdid <= 0)
	  return NULL;
	else
	  return($this->get_date_fld()->__toString());
  }
  
  protected function get_date_fld()
  {
    if(!isset($this->date_fld))
	  $this->date_fld = new inputclass_datefield("tddate". $this->tdid,NULL,NULL,"date","testdef",$this->tdid,"tdid",NULL,"testdhandler.php","lastmodifiedby", $_SESSION['uid']);
	return $this->date_fld;  
  }
  public function edit_date()
  {
	$this->get_date_fld()->echo_html();
  }
  
  public function get_type()
  {
    if($this->tdid <= 0)
	  return NULL;
	else
	  return($this->get_type_fld()->__toString());
  }
  
  protected function get_type_fld()
  {
    if(!isset($this->type_fld))
	  $this->type_fld = new inputclass_listfield("tdtype". $this->tdid,"SELECT '' AS id, '' AS tekst UNION SELECT type,translation FROM testtype ORDER BY id",NULL,"type","testdef",$this->tdid,"tdid",NULL,"testdhandler.php","lastmodifiedby", $_SESSION['uid']);
	return $this->type_fld;  
  }
  public function edit_type()
  {
	$this->get_type_fld()->echo_html();
  }
  
  public function get_period()
  {
    if($this->tdid <= 0)
	  return NULL;
	else
	  return($this->get_period_fld()->__toString());
  }
  
  protected function get_period_fld()
  {
    if(!isset($this->period_fld))
	  $this->period_fld = new inputclass_listfield("tdperiod". $this->tdid,"SELECT '' AS id, '' AS tekst UNION SELECT id, id FROM period WHERE status='open'",NULL,"period","testdef",$this->tdid,"tdid",NULL,"testdhandler.php","lastmodifiedby", $_SESSION['uid']);
	return $this->period_fld;  
  }
  public function edit_period()
  {
	$this->get_period_fld()->echo_html();
  }
  
  public function get_week()
  {
    if($this->tdid <= 0)
	  return NULL;
	return($this->get_week_fld()->__toString());
  }
  
  protected function get_week_fld()
  {
    if(!isset($this->week_fld))
	  $this->week_fld = new inputclass_textfield("tdweek". $this->tdid,2,NULL,"week","testdef",$this->tdid,"tdid",NULL,"testdhandler.php","lastmodifiedby", $_SESSION['uid']);
	return $this->week_fld;  
  }
  public function edit_week()
  {
	$this->get_week_fld()->echo_html();
  }
  
  public function get_domain()
  {
    if($this->tdid <= 0)
	  return NULL;
	return($this->get_domain_fld()->__toString());
  }
  
  protected function get_domain_fld()
  {
    if(!isset($this->domain_fld))
	  $this->domain_fld = new inputclass_textfield("tddomain". $this->tdid,20,NULL,"domain","testdef",$this->tdid,"tdid",NULL,"testdhandler.php","lastmodifiedby", $_SESSION['uid']);
	return $this->domain_fld;  
  }
  public function edit_domain()
  {
	$this->get_domain_fld()->echo_html();
  }
  
  public function get_term()
  {
    if($this->tdid <= 0)
	  return NULL;
	return($this->get_term_fld()->__toString());
  }
  
  protected function get_term_fld()
  {
    if(!isset($this->term_fld))
	  $this->term_fld = new inputclass_textfield("tdterm". $this->tdid,20,NULL,"term","testdef",$this->tdid,"tdid",NULL,"testdhandler.php","lastmodifiedby", $_SESSION['uid']);
	return $this->term_fld;  
  }
  public function edit_term()
  {
	$this->get_term_fld()->echo_html();
  }
  
  public function get_duration()
  {
    if($this->tdid <= 0)
	  return NULL;
	return($this->get_duration_fld()->__toString());
  }
  
  protected function get_duration_fld()
  {
    if(!isset($this->duration_fld))
	  $this->duration_fld = new inputclass_textfield("tdduration". $this->tdid,8,NULL,"domain","testdef",$this->tdid,"tdid",NULL,"testdhandler.php","lastmodifiedby", $_SESSION['uid']);
	return $this->duration_fld;  
  }
  public function edit_duration()
  {
	$this->get_duration_fld()->echo_html();
  }
  
  public function get_assignments()
  {
    if($this->tdid <= 0)
	  return NULL;
	return($this->get_assign_fld()->__toString());
  }
  
  protected function get_assign_fld()
  {
    if(!isset($this->assign_fld))
	  $this->assign_fld = new inputclass_textarea("tdassign". $this->tdid,"1,*",NULL,"assignments","testdef",$this->tdid,"tdid",NULL,"testdhandler.php","lastmodifiedby", $_SESSION['uid']);
	return $this->assign_fld;  
  }
  public function edit_assignments()
  {
	$this->get_assign_fld()->echo_html();
  }
  
  public function get_tools()
  {
    if($this->tdid <= 0)
	  return NULL;
	return($this->get_tools_fld()->__toString());
  }
  
  protected function get_tools_fld()
  {
    if(!isset($this->tools_fld))
	  $this->tools_fld = new inputclass_textfield("tdtools". $this->tdid,"1,*",NULL,"tools","testdef",$this->tdid,"tdid",NULL,"testdhandler.php","lastmodifiedby", $_SESSION['uid']);
	return $this->tools_fld;  
  }
  public function edit_tools()
  {
	$this->get_tools_fld()->echo_html();
  }
  
  public function get_realised()
  {
    if($this->tdid <= 0)
	  return NULL;
	return($this->get_realised_fld()->__toString());
  }
  
  protected function get_realised_fld()
  {
    if(!isset($this->realised_fld))
	  $this->realised_fld = new inputclass_textfield("tdrealised". $this->tdid,"1,*",NULL,"realised","testdef",$this->tdid,"tdid",NULL,"testdhandler.php","lastmodifiedby", $_SESSION['uid']);
	return $this->realised_fld;  
  }
  public function edit_realised()
  {
	$this->get_realised_fld()->echo_html();
  }
  
  public static function test_list($subject, $group = NULL)
  {
    // If no group given, use default group
	if($group == NULL)
	{
	  $gobj = new group();
	  $gobj->load_current();
	  $group = $gobj->get_id();
	}
	// Now convert group and subject to class ID
	$classinfo = inputclassbase::load_query("SELECT cid FROM WHERE gid=". $group. " AND mid=". $subject);
	if(isset($classinfo['cid'][1]))
	{
      $tests = inputclassbase::load_query("SELECT tdid FROM testdef WHERE cid=". $classinfo['cid'][1]. " ORDER BY date DESC");
	  if(isset($tests))
	  {
	    foreach($tests['tdid'] AS $tdid)
	      $testlist[$tdid] = new testdefinition($tdid);
	    return($testlist);
	  }
	}
	return NULL; // In case something went wrong...
  }
  
  public static function get_test($cid,$date)
  {
    $tests = inputclassbase::load_query("SELECT tdid FROM testdef WHERE cid=". $cid. " AND date='". date("Y-m-d",$date). "'");
	if(isset($tests))
	  return(new testdefinition($tests['tdid'][0]));
	else
	  return NULL;
  }
}
?>