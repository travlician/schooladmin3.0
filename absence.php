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
require_once("absencecategory.php");
require_once("subject.php");

class absence
{
  protected $asid;
  protected $aidfld,$datefld,$sidfld,$timefld,$authfld,$explanationfld,$classfld;
  protected static $add_dial_formed;

  public function __construct($asid = NULL)
  {
	if(isset($asid))
	  $this->asid = $asid;
	else
	  $this->asid = 0;
  }
  
  public function get_id()
  {
    return $this->asid;
  }
  
  public function get_date()
  {
    $this->get_datefld();
	return($this->datefld->__toString());
  }
  private function get_datefld()
  {
    if(!isset($this->datefld))
	{
	  $this->datefld = new inputclass_datefield("abd". (3000000 + $this->asid),NULL,NULL,"date","absence",$this->asid,"asid",NULL,"datahandler.php");
	  $this->datefld->set_extrafield("lastmodifiedby",$_SESSION['uid']);
	}
  }
  public function edit_date()
  {
    $this->get_datefld();
	$this->datefld->echo_html();
  }
	
	public function get_period()
	{
		$perq = "SELECT id FROM absence LEFT JOIN period ON(date>= startdate AND date <= enddate) WHERE asid=". $this->get_id();
		$perqr = inputclassbase::load_query($perq);
		if(!isset($perqr['id']))
			return null;
		else 
			return $perqr['id'][0];
	}
  
  public function get_sid($date=NULL,$acat=NULL,$hidden=false)
  {
    $this->get_sidfld($date,$acat);
		return($this->sidfld->__toString());
  }
  private function get_sidfld($date=NULL,$acat=NULL,$hidden=false)
  {
    if(!isset($this->sidfld))
		{
				$studq = "SELECT sid AS id, CONCAT(lastname,', ',firstname) AS tekst FROM student ORDER BY lastname,firstname";
			$this->sidfld = new inputclass_listfield("absid". $this->asid,$studq,NULL,"sid","absence",$this->asid,"asid",$hidden ? "display:none" : NULL,"datahandler.php");
			// Make it so that if a SID is sent, also date and reason are fille with the defaults
			if($date==NULL)
				$date = date("Y-m-d");
			$this->sidfld->set_extrafield("date",$date);
			if($acat == NULL)
				$rq = "SELECT aid AS id,description AS tekst FROM absencereasons ORDER BY tekst";
			else
				$rq = "SELECT aid AS id,description AS tekst FROM absencereasons WHERE acid=". $acat->get_id(). " ORDER BY tekst";
				$aids = inputclassbase::load_query($rq);
			if(isset($aids['id'][0]))
				$this->sidfld->set_extrafield("aid",$aids['id'][0]);
			$this->sidfld->set_extrafield("authorization","No");
			$this->sidfld->set_extrafield("class",$_SESSION['CurrentSubject']);
			$this->sidfld->set_extrafield("lastmodifiedby",$_SESSION['uid']);
		}
  }
  public function edit_sid($date=NULL,$acat=NULL,$hidden=false)
  {
    $this->get_sidfld($date,$acat,$hidden);
		$this->sidfld->echo_html();
  }
  
  public function get_time()
  {
    $this->get_timefld();
	return($this->timefld->__toString());
  }
  private function get_timefld()
  {
    if(!isset($this->timefld))
	{
	  $this->timefld = new inputclass_textfield("abt". $this->asid,8,NULL,"time","absence",$this->asid,"asid",NULL,"datahandler.php");
	  $this->timefld->set_extrafield("lastmodifiedby",$_SESSION['uid']);
	}
  }
  public function edit_time()
  {
    $this->get_timefld();
	$this->timefld->echo_html();
  }
  
  public function get_authorization()
  {
    $this->get_authfld();
	return($this->authfld->__toString());
  }
  private function get_authfld()
  {
    $dtext = $_SESSION['dtext'];
    if(!isset($this->authfld))
		{
			$authsel = "SELECT 'No' AS id, '". $dtext['No']. "' AS tekst UNION SELECT 'Yes','". $dtext['Yes']. "' UNION SELECT 'Pending','". $dtext['Pending']. "' UNION SELECT 'Parent','". $dtext['Parent']. "'";
			$this->authfld = new inputclass_listfield("aba". $this->asid,$authsel,NULL,"authorization","absence",$this->asid,"asid",NULL,"datahandler.php");
			$this->authfld->set_extrafield("lastmodifiedby",$_SESSION['uid']);
		}
  }
  private function get_authflde()
  {
		global $limitabsenceauthorization;
    $dtext = $_SESSION['dtext'];
    if(!isset($this->authfld))
		{
			if(isset($limitabsenceauthorization) && $limitabsenceauthorization)
				$authsel = "SELECT 'No' AS id, '". $dtext['No']. "' AS tekst UNION SELECT 'Yes','". $dtext['Yes']. "'";
			else
				$authsel = "SELECT 'No' AS id, '". $dtext['No']. "' AS tekst UNION SELECT 'Yes','". $dtext['Yes']. "' UNION SELECT 'Pending','". $dtext['Pending']. "' UNION SELECT 'Parent','". $dtext['Parent']. "'";
			$this->authfld = new inputclass_listfield("aba". $this->asid,$authsel,NULL,"authorization","absence",$this->asid,"asid",NULL,"datahandler.php");
			$this->authfld->set_extrafield("lastmodifiedby",$_SESSION['uid']);
		}
  }
  public function edit_authorization()
  {
    $this->get_authflde();
	$this->authfld->echo_html();
  }
  
  public function get_explanation()
  {
    $this->get_explanationfld();
	return($this->explanationfld->__toString());
  }
  private function get_explanationfld()
  {
    if(!isset($this->explanationfld))
	{
	  $this->explanationfld = new inputclass_textfield("abexpl". $this->asid,20,NULL,"explanation","absence",$this->asid,"asid",NULL,"datahandler.php");
	  $this->explanationfld->set_extrafield("lastmodifiedby",$_SESSION['uid']);
	}
  }
  public function edit_explanation()
  {
    $this->get_explanationfld();
	$this->explanationfld->echo_html();
  }
  
  public function get_reason()
  {
    $this->get_aidfld();
	return($this->aidfld->__toString());
  }
  public function get_reason_me()
  {
    $this->get_aidfld_me();
	return($this->aidfld_me->__toString());
  }
  private function get_aidfld($acat = NULL)
  {
   if(!isset($this->aidfld))
		{
			if($acat == NULL)
			{
				$rq = "SELECT aid AS id,description AS tekst FROM absencereasons ORDER BY tekst";
			}
			else
				$rq = "SELECT aid AS id,description AS tekst FROM absencereasons WHERE acid=". $acat->get_id(). " ORDER BY tekst";
			
			$this->aidfld = new inputclass_listfield("abaid". $this->asid,$rq,NULL,"aid","absence",$this->asid,"asid",NULL,"datahandler.php");
			$this->aidfld->set_extrafield("lastmodifiedby",$_SESSION['uid']);
		}
  }
  private function get_aidfld_me($acat = NULL)
  { // Available reasons depend on user roles (and absence category)
    $I = new teacher();
	$I->load_current();
    if(!isset($this->aidfld_me))
	{
	  if($acat == NULL)
	  {
	    $rq = "";
		if($_SESSION['LoginType'] == "S")
		$rq .= "SELECT '' AS id, '' AS tekst UNION ";
	    $rq .= "SELECT aid AS id,description AS tekst FROM absencereasons LEFT JOIN absencecats USING(acid) WHERE waccess='A'";
		if($_SESSION['LoginType'] != "S")
		{
		  $rq .= " OR waccess='T'";
	      if($I->has_role("mentor") || $I->has_role("admin"))
		    $rq .= " OR waccess='M'";
		  if($I->has_role("counsel") || $I->has_role("admin"))
		    $rq .= " OR waccess='C'";
		  if($I->has_role("office") || $I->has_role("admin"))
		    $rq .= " OR waccess='O'";
		  if($I->has_role("mentor") || $I->has_role("office") || $I->has_role("admin"))
		    $rq .= " OR waccess='P'";
		  if($I->has_role("admin"))
		    $rq .= " OR waccess='N'";
        }			
		$rq .= " ORDER BY tekst";
	  }
	  else
	    $rq = "SELECT aid AS id,description AS tekst FROM absencereasons WHERE acid=". $acat->get_id(). " ORDER BY tekst";
	  
	  $this->aidfld_me = new inputclass_listfield("abaid". $this->asid,$rq,NULL,"aid","absence",$this->asid,"asid",NULL,"datahandler.php");
	  $this->aidfld_me->set_extrafield("lastmodifiedby",$_SESSION['uid']);
	}
  }
  public function edit_reason($acat = NULL)
  {
    $this->get_aidfld_me($acat);
	$this->aidfld_me->echo_html();
  }
  
  public function edit_reason_parent($acat = NULL)
  {
    $I = new teacher();
		$I->load_current();
			$this->get_aidfld_me($acat);
		$this->aidfld_me->set_extrafield("sid",$_SESSION['uid']);
		$this->aidfld_me->set_extrafield("date",date("Y-m-d"));
		$this->aidfld_me->set_extrafield("time",date("H:i:s"));
		$this->aidfld_me->set_extrafield("authorization","Parent");
		$this->aidfld_me->echo_html();
  }
  
  public function get_subject($listall = false)
  {
    $this->get_classfld($listall);
		return($this->classfld->__toString());
  }
  private function get_classfld($listall = false)
  {
    $rq = "SELECT '' AS id, '' AS tekst";
	if($listall)
	  $sblist = subject::subject_list(new teacher(1));
	else
	  $sblist = subject::subject_list();
	if(isset($sblist))
	  foreach($sblist AS $sbobj)
	    $rq .= " UNION SELECT ". $sbobj->get_id(). ",\"". $sbobj->get_shortname(). "\"";
	$this->classfld = new inputclass_listfield("abcl". $this->asid,$rq,NULL,"class","absence",$this->asid,"asid",NULL,"datahandler.php");
	$this->classfld->set_extrafield("lastmodifiedby",$_SESSION['uid']);
  }
  public function edit_subject()
  {
    $this->get_classfld();
	$this->classfld->echo_html();
  }
  
  public function get_timeslot()
  {
    $this->get_timeslotfld();
		return($this->timeslotfld->__toString());
  }
  private function get_timeslotfld()
  {
		// See which is the max timeslot
		global $maxtimeslot;
		$tstqr = inputclassbase::load_query("SHOW TABLES LIKE 'timetabletimes'");
		if(isset($tstqr))
		{
		  $maxtsqr = inputclassbase::load_query("SELECT MAX(timeslot) AS mxts FROM timetabletimes");
		}
		if(isset($maxtsqr['mxts']) && $maxtsqr['mxts'][0] > 0)
			$maxts = $maxtsqr['mxts'][0];
		else if(isset($maxtimeslot))
			$maxts = $maxtimeslot;
		else
			$maxts = 8;
    $rq = "SELECT '' AS id, '' AS tekst";
		for($ts = 1; $ts <= $maxts; $ts++)
			$rq .= " UNION SELECT ". $ts. ",". $ts;

		$this->timeslotfld = new inputclass_listfield("abts". $this->asid,$rq,NULL,"timeslot","absence",$this->asid,"asid",NULL,"datahandler.php");
		$this->timeslotfld->set_extrafield("lastmodifiedby",$_SESSION['uid']);
  }
  public function edit_timeslot()
  {
    $this->get_timeslotfld();
		$this->timeslotfld->echo_html();
  }
  
  public function get_modinfo()
  {
    global $teachercode;
		if(isset($teachercode))
			$modiqr = inputclassbase::load_query("SELECT lastmodifiedat,data AS tidinfo FROM absence LEFT JOIN `". $teachercode. "` ON(tid=lastmodifiedby) WHERE asid=". $this->get_id());
		else
			$modiqr = inputclassbase::load_query("SELECT lastmodifiedat,lastmodifiedby AS tidinfo FROM absence WHERE asid=". $this->get_id());
		if(isset($modiqr['lastmodifiedat']))
			return($modiqr['lastmodifiedat'][0]. " (". $modiqr['tidinfo'][0]. ")");
		else
	  return("");
  }
  
  public function get_student()
  {
    $ssidqr = inputclassbase::load_query("SELECT sid FROM absence WHERE asid=". $this->get_id());
	if(isset($ssidqr['sid']))
	  return(new student($ssidqr['sid'][0]));
	else
	  return NULL;
  }
  

  public static function get_abs_record($stud, $acat, $absdate = NULL)
  {
    if($absdate == NULL)
	  $absdate = date('Y-m-d');
	if(!isset($_SESSION['CurrentSubject']))
		$cursub =0;
	else
		$cursub = $_SESSION['CurrentSubject'];
	if($acat->get_classuse() == 1)
	  $absq = "SELECT asid FROM absence LEFT JOIN absencereasons USING(aid) WHERE acid=". $acat->get_id(). " AND sid=". $stud->get_id(). " AND date='". $absdate. "' AND class=". $cursub. " ORDER BY time";
	else
	  $absq = "SELECT asid FROM absence LEFT JOIN absencereasons USING(aid) WHERE acid=". $acat->get_id(). " AND sid=". $stud->get_id(). " AND date='". $absdate. "' ORDER BY time";
	$absr = inputclassbase::load_query($absq);
	if(isset($absr['asid']))
	  return (new absence($absr['asid'][0]));
	else
	  return(NULL);
  }
  
  public static function add_hidden_add_dialog($stud,$acat = NULL,$absdat = NULL)
  {
    $dtext = $_SESSION['dtext'];
		if($acat == NULL)
				$ci = 0;
			else
				$ci = $acat->get_id();
		if(!isset($absdat))
			$absdat = mktime() + $_SESSION['ClientTimeOffset'];
		absence::form_add_dialog($acat,$absdat);
		echo("<IMG SRC='PNG/action_add.png' border=0 TITLE='". $dtext['ADD_CAP']. "' onClick='show_add_abs_dial". $ci. "(". $stud->get_id(). "); '");
		// See if a record already exists for this student, category and date. If so, show red BG
		$absalready = inputclassbase::load_query("SELECT asid FROM absence LEFT JOIN absencereasons USING(aid) WHERE acid=". $ci. " AND sid=". $stud->get_id(). " AND date='". date("Y-m-d", $absdat). "'");
		if(isset($absalready['asid']))
			echo(" style='background-color: red;'");
		echo(">"); 
  }
  
  public static function add_hidden_add_dialog_link($stud,$acat,$absdat = NULL)
  {
    $dtext = $_SESSION['dtext'];
		$ci = $acat->get_id();
		if(!isset($absdat))
			$absdat = mktime() + $_SESSION['ClientTimeOffset'];
		absence::form_add_dialog($acat,$absdat);
		$ret="<A HREF=# onClick='show_add_abs_dial". $ci. "(". $stud->get_id(). "); '";
		// See if a record already exists for this student, category and date. If so, show red BG
		$absalready = inputclassbase::load_query("SELECT asid FROM absence LEFT JOIN absencereasons USING(aid) WHERE acid=". $ci. " AND sid=". $stud->get_id(). " AND date='". date("Y-m-d", $absdat). "'");
		if(isset($absalready['asid']))
			$ret .= " style='background-color: red;'";
		$ret .= ">". $acat->get_letter(). $stud->get_abscount(NULL,$acat). "</a>";
		return($ret);
  }
  
  public static function form_add_dialog($acat,$absdat)
  {
		if($acat == NULL)
      $ci = 0;
    else
	  $ci = $acat->get_id();
    if(!isset(absence::$add_dial_formed[$ci]))
		{
			$dtext = $_SESSION['dtext'];
			absence::$add_dial_formed[$ci]=true;
			echo("<DIV class='absencedialog' NAME=add_abs_div". $ci. " id=add_abs_div". $ci. " STYLE='z-index:4000;'>");
				$newabs = new absence(0-$ci);
			echo("<BR><LABEL>". $dtext['Student']. ": </LABEL>");
			$newabs->edit_sid(NULL,$acat,false);
			echo("<BR><LABEL>". $dtext['Reason']. ": </LABEL>");
			$newabs->edit_reason($acat);
			echo("<BR><LABEL>". $dtext['Date']. "/". $dtext['Time']. ": </LABEL>");
			$newabs->edit_date();
			$newabs->edit_time();
			if($newabs->use_subjects())
			{
				echo("<BR><LABEL>". $dtext['Timeslot']. ": </LABEL>");
				$newabs->edit_timeslot();
				echo("<BR><LABEL>". $dtext['Subject']. ": </LABEL>");
				$newabs->edit_subject();
			}
			echo("<BR><LABEL>". $dtext['Remarks']. ": </LABEL>");
			$newabs->edit_explanation();
			echo("<BR><LABEL>". $dtext['Authorization']. ": </LABEL>");
			$newabs->edit_authorization();
			echo("<BR><BR><BUTTON type='BUTTON' onCLick=\"setTimeout('ahdleave();',500);\">". $dtext['ADD_CAP']. "</BUTTON>");
			echo("&nbsp;<INPUT type=submit NAME=delte VALUE='". $dtext['Delete']. "' onCLick='deleteabs(0);'>");
			echo("<FORM NAME=ahdleaveform ID=ahdleaveform METHOD=POST ACTION='". $_SERVER['REQUEST_URI']. "'>");
			if(isset($_POST) && count($_POST) > 0)
				foreach($_POST AS $pkey => $pval)
				if($pkey != "student" && $pkey != "delte")
					echo("<input type=hidden name='". $pkey. "' value='". $pval. "'>");
			echo("</FORM>");
			echo("<SCRIPT>function ahdleave() { document.getElementById('ahdleaveform').submit(); } </SCRIPT>");
			echo("</DIV>");
			echo("<SCRIPT> function show_add_abs_dial". $ci. "(stud) {");
			echo(" document.getElementById('add_abs_div". $ci. "').style.display='block';");
			echo(" document.getElementById('absid". ($ci > 0 ? '-' : ''). $ci. "').value=stud;");
			echo(" document.getElementById('abd". (3000000 - $ci). "').value='". date("d-m-Y",$absdat). "';");
			echo(" document.getElementById('abt". ($ci > 0 ? '-' : ''). $ci. "').value=new Date().toTimeString().substr(0,8);");
			echo(" send_xml(\"abt". ($ci > 0 ? '-' : ''). $ci. "\",document.getElementById('abt". ($ci > 0 ? '-' : ''). $ci. "'));");
			
			echo(" setTimeout(\"send_xml(\'absid". ($ci > 0 ? '-' : ''). $ci. "\',document.getElementById('absid". ($ci > 0 ? '-' : ''). $ci. "'));\",500);");
			echo(" setTimeout(\"send_xml(\'abd". (3000000 - $ci). "\',document.getElementById('abd". (3000000 - $ci). "'));\",800);");
			echo(" } </SCRIPT>");
		}
  }
  
  public static function form_add_dialogTS($acat,$absdat)
  {
	if($acat == NULL)
      $ci = 0;
    else
	  $ci = $acat->get_id();
    if(!isset(absence::$add_dial_formed[$ci]))
	{
      $dtext = $_SESSION['dtext'];
	  absence::$add_dial_formed[$ci]=true;
	  echo("<DIV class='absencedialog' NAME=add_abs_div". $ci. " id=add_abs_div". $ci. ">");
      $newabs = new absence(0-$ci);
	  echo("<BR><LABEL>". $dtext['Student']. ": </LABEL>");
	  $newabs->edit_sid(NULL,$acat,false);
	  echo("<BR><LABEL>". $dtext['Reason']. ": </LABEL>");
	  $newabs->edit_reason($acat);
	  echo("<BR><LABEL>". $dtext['Date']. "/". $dtext['Time']. ": </LABEL>");
	  $newabs->edit_date();
	  $newabs->edit_time();
		if($newabs->use_subjects())
		{
			echo("<BR><LABEL>". $dtext['Timeslot']. ": </LABEL>");
			$newabs->edit_timeslot();
			echo("<BR><LABEL>". $dtext['Subject']. ": </LABEL>");
			$newabs->edit_subject();
		}
	  echo("<BR><LABEL>". $dtext['Remarks']. ": </LABEL>");
	  $newabs->edit_explanation();
	  echo("<BR><LABEL>". $dtext['Authorization']. ": </LABEL>");
	  $newabs->edit_authorization();
	  echo("<BR><BR><BUTTON type='BUTTON' onCLick=\"setTimeout('ahdleave();',500);\">". $dtext['ADD_CAP']. "</BUTTON>");
	  echo("<FORM NAME=ahdleaveform ID=ahdleaveform METHOD=POST ACTION='". $_SERVER['REQUEST_URI']. "'>");
	  echo("<INPUT TYPE=hidden NAME=funcrecover ID=funcrecover VALUE=''>");
	  if(isset($_POST) && count($_POST) > 0)
      foreach($_POST AS $pkey => $pval)
	    if($pkey != "student" && $pkey != "delte" && $pkey != 'funcrecover')
	      echo("<input type=hidden name='". $pkey. "' value='". $pval. "'>");
	  echo("</FORM>");
	  echo("<SCRIPT>function ahdleave() { document.getElementById('funcrecover').value=curfunc; document.getElementById('ahdleaveform').submit(); } </SCRIPT>");
	  echo("</DIV>");
	  echo("<SCRIPT> function show_add_abs_dial". $ci. "(stud) {");
	  echo(" document.getElementById('add_abs_div". $ci. "').style.display='block';");
	  echo(" document.getElementById('absid". ($ci > 0 ? '-' : ''). $ci. "').value=stud;");
	  echo(" document.getElementById('abd". (3000000 - $ci). "').value='". date("d-m-Y",$absdat). "';");
	  echo(" document.getElementById('abt". ($ci > 0 ? '-' : ''). $ci. "').value=new Date().toTimeString().substr(0,8);");
	  echo(" send_xml(\"abt". ($ci > 0 ? '-' : ''). $ci. "\",document.getElementById('abt". ($ci > 0 ? '-' : ''). $ci. "'));");
	  
	  echo(" setTimeout(\"send_xml(\'absid". ($ci > 0 ? '-' : ''). $ci. "\',document.getElementById('absid". ($ci > 0 ? '-' : ''). $ci. "'));\",500);");
	  echo(" setTimeout(\"send_xml(\'abd". (3000000 - $ci). "\',document.getElementById('abd". (3000000 - $ci). "'));\",800);");
	  echo(" } </SCRIPT>");
	}
  }
  
  public static function add_hidden_add_full_dialog($stud,$acat = NULL)
  {
    $dtext = $_SESSION['dtext'];
	if($acat == NULL)
      $ci = 0;
    else
	  $ci = $acat->get_id();
    if(!isset(absence::$add_dial_formed[$ci]))
	{
	  absence::$add_dial_formed[$ci]=true;
	  echo("<DIV class='absencedialog' NAME=add_abs_div". $ci. " id=add_abs_div". $ci. ">");
      $newabs = new absence(0-$ci);
	  //echo("<BR><LABEL>". $dtext['Student']. ": </LABEL>");
	  $newabs->edit_sid(NULL,$acat,TRUE);
	  echo("<BR><LABEL>". $dtext['Date']. ": </LABEL>");
	  $newabs->edit_date();
	  echo("<BR><LABEL>". $dtext['Time']. ": </LABEL>");
	  $newabs->edit_time();
		if($newabs->use_subjects())
		{
			echo("<BR><LABEL>". $dtext['Subject']. ": </LABEL>");
			$newabs->edit_subject();
			echo("<BR><LABEL>". $dtext['Timeslot']. ": </LABEL>");
			$newabs->edit_timeslot();
		}
	  echo("<BR><LABEL>". $dtext['Reason']. ": </LABEL>");
	  $newabs->edit_reason($acat);
	  echo("<BR><LABEL>". $dtext['Remarks']. ": </LABEL>");
	  $newabs->edit_explanation();
	  echo("<BR><LABEL>". $dtext['Authorization']. ": </LABEL>");
	  $newabs->edit_authorization();
	  echo("<BR><BR><BUTTON type='BUTTON' onCLick=\"setTimeout('ahfdleave();',500);\">". $dtext['ADD_CAP']. "</BUTTON>");
	  echo("&nbsp;<INPUT type=submit NAME=delte VALUE='". $dtext['Delete']. "' onCLick='deleteabs(0);'>");
	  echo("<FORM NAME=ahfdleaveform ID=ahfdleaveform METHOD=POST ACTION='". $_SERVER['REQUEST_URI']. "'>");
	  if(isset($_POST) && count($_POST) > 0)
      foreach($_POST AS $pkey => $pval)
	    if($pkey != "student" && $pkey != "delte")
	      echo("<input type=hidden name='". $pkey. "' value='". $pval. "'>");
	  echo("</FORM>");
	  echo("<SCRIPT>function ahfdleave() { document.getElementById('ahfdleaveform').submit(); } </SCRIPT>");
	  echo("</DIV>");
	  echo("<SCRIPT> function show_add_abs_dial". $ci. "(stud) {");
	  echo(" document.getElementById('add_abs_div". $ci. "').style.display='block';");
	  echo(" document.getElementById('absid". ($ci > 0 ? '-' : ''). $ci. "').value=stud;");
	  echo(" document.getElementById('abt". ($ci > 0 ? '-' : ''). $ci. "').value=new Date().toTimeString().substr(0,8);");
	  echo(" send_xml(\"absid". ($ci > 0 ? '-' : ''). $ci. "\",document.getElementById('absid". ($ci > 0 ? '-' : ''). $ci. "'));");
	  echo(" send_xml(\"abt". ($ci > 0 ? '-' : ''). $ci. "\",document.getElementById('abt". ($ci > 0 ? '-' : ''). $ci. "'));");
	  echo(" } </SCRIPT>");
	}
	echo("<IMG SRC='PNG/action_add.png' border=0 TITLE='". $dtext['ADD_CAP']. "' onClick='show_add_abs_dial". $ci. "(". $stud->get_id(). "); '>"); 
  }
  
  public function add_hidden_edit_dialog()
  {
    $dtext = $_SESSION['dtext'];
    echo("<DIV class='absencedialog' NAME=edit_abs_div". $this->asid. " id=edit_abs_div". $this->asid. ">");
		echo("<BR><LABEL>". $dtext['Student']. ": </LABEL>");
		$this->edit_sid();
			echo("<BR><LABEL>". $dtext['Reason']. ": </LABEL>");
		$this->edit_reason();
		echo("<BR><LABEL>". $dtext['Time']. ": </LABEL>");
		$this->edit_time();
		if($this->use_subjects())
		{
			echo("<BR><LABEL>". $dtext['Timeslot']. ": </LABEL>");
			$this->edit_timeslot();
			echo("<BR><LABEL>". $dtext['Subject']. ": </LABEL>");
			$this->edit_subject();
		}
		echo("<BR><LABEL>". $dtext['Remarks']. ": </LABEL>");
		$this->edit_explanation();
		echo("<BR><LABEL>". $dtext['Authorization']. ": </LABEL>");
		$this->edit_authorization();
		echo("<BR><BR><INPUT type=submit NAME=submit VALUE='". $dtext['Change']. "' onCLick='document.getElementById(\"edit_abs_div". $this->asid. "\").style.display=\"none\";'>");	
		echo("<BR><INPUT type=submit NAME=delte VALUE='". $dtext['Delete']. "' onCLick='deleteabs(". $this->asid. ");'>");
			echo("</DIV>");
		echo("<SCRIPT> function show_edit_abs_dial". $this->asid. "() { document.getElementById('edit_abs_div". $this->asid. "').style.display='block';  } </SCRIPT>");
		echo("<IMG SRC='PNG/reply.png' border=0 TITLE='". $dtext['Change']. "' onClick='show_edit_abs_dial". $this->asid. "(); '>"); 
  }
	
	public function get_cat()
	{
		if($this->asid > 0)
			$acidqr = inputclassbase::load_query("SELECT acid FROM absence LEFT JOIN absencereasons USING(aid) WHERE asid=". $this->asid);
		if(isset($acidqr['acid'][0]))
			return(new absencecategory($acidqr['acid'][0]));
		else
			return NULL;
	}

  public function add_hidden_edit_dialog_link()
  {
    $dtext = $_SESSION['dtext'];
    echo("<DIV class='absencedialog' NAME=edit_abs_div". $this->asid. " id=edit_abs_div". $this->asid. " style='z-index: 4001;'>");
		echo("<BR><LABEL>". $dtext['Student']. ": </LABEL>");
		$this->edit_sid();
			echo("<BR><LABEL>". $dtext['Reason']. ": </LABEL>");
		$this->edit_reason();
		echo("<BR><LABEL>". $dtext['Time']. ": </LABEL>");
		$this->edit_time();
		if($this->use_subjects())
		{
			echo("<BR><LABEL>". $dtext['Timeslot']. ": </LABEL>");
			$this->edit_timeslot();
			echo("<BR><LABEL>". $dtext['Subject']. ": </LABEL>");
			$this->edit_subject();
		}
		echo("<BR><LABEL>". $dtext['Remarks']. ": </LABEL>");
		$this->edit_explanation();
		echo("<BR><LABEL>". $dtext['Authorization']. ": </LABEL>");
		$this->edit_authorization();
		echo("<BR><BR><INPUT type=submit NAME=submit VALUE='". $dtext['Change']. "' onCLick='document.getElementById(\"edit_abs_div". $this->asid. "\").style.display=\"none\";'>");	
		echo("<BR><INPUT type=submit NAME=delte VALUE='". $dtext['Delete']. "' onCLick='deleteabs(". $this->asid. ");'>");
			echo("</DIV>");
		echo("<SCRIPT> function show_edit_abs_dial". $this->asid. "() { document.getElementById('edit_abs_div". $this->asid. "').style.display='block';  } </SCRIPT>");
		return("<a href=# onClick='show_edit_abs_dial". $this->asid. "(); ' STYLE='background-color: #F80;'>". $this->get_cat()->get_letter(). $this->get_student()->get_abscount(NULL,$this->get_cat()). "</a>"); 
  }
	
	public function use_subjects()
	{
		$classcatsqr = inputclassbase::load_query("SELECT acid FROM absencecats WHERE classuse=1");
		return(isset($classcatsqr['acid']));
	}
  
  public static function list_student($stud,$ondate=NULL)
  {
    if(isset($ondate))
      $abslist = inputclassbase::load_query("SELECT asid FROM absence WHERE sid=". $stud->get_id(). " AND date = '". date("Y-m-d",$ondate). "' ORDER BY date DESC,time DESC");
	else
	{
      $ystqr = inputclassbase::load_query("SELECT MIN(startdate) AS yst FROM period");
	  if(!isset($ystqr['yst'][0]))
	    $yst = date("Y-m-d",mktime(0,0,0,date("n"),date("j"),date("Y")-1));
	  else
	    $yst = $ystqr['yst'][0];
      $abslist = inputclassbase::load_query("SELECT asid FROM absence WHERE sid=". $stud->get_id(). " AND date >= '". $yst. "' ORDER BY date DESC,time DESC");
	}
	if(isset($abslist['asid']))
	{
	  foreach($abslist['asid'] AS $asid)
	    $retlist[$asid] = new absence($asid);
	  return($retlist);
    }
	else
	  return NULL;
	
  }
  
  
}
?>