<?
/* vim: set expandtab tabstop=4 shiftwidth=4: */
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
require_once("group.php");

class student
{
  protected $sid;
  protected $nameflds;  
  public function __construct($sid = NULL)
  {
    global $picturespath;
    if(isset($sid))
	  $this->sid = $sid;
	else
	  $this->sid = 0;
	if(isset($_FILES))
	  foreach($_FILES AS $fn => $fobj)
	  {
	    if(substr($fn,0,2) == "sd" && (substr($fn, -1 - strlen($this->sid)) == "-". $this->sid))
		{ // This files is a picture for this student object!
		  $tabnam = substr($fn, 2, strlen($fn) - 3 - strlen($this->sid));
		  if($fobj["error"] == UPLOAD_ERR_OK)
		  {
            $newfilename = $tabnam .$sid;
            // Copy the extension from the tmp_file!
            $extension = (strstr($fobj['name'],'.')) ? @strstr($fobj['name'],'.') : '.file';
            $newfilename .= $extension;
			if(move_uploaded_file($fobj['tmp_name'],$picturespath. $newfilename))
			{ // File moved, now update database
              $sql_query = "REPLACE `" . $tabnam . "` VALUES (" . $sid . ",'" . $newfilename . "')";
              mysql_query($sql_query);  
			}
		  }
		}
	  }
  }

  public function get_id()
  {
    return $this->sid;
  }
  
  public function get_name()
  {
    if($this->sid == 0)
	  return NULL;
    if(!isset($this->nameflds))
	{
	  $this->nameflds['firstname'] = new inputclass_textfield("sfname". $this->sid,40,NULL,"firstname","student",$this->sid,"sid",NULL,"datahandler.php");
	  $this->nameflds['lastname'] = new inputclass_textfield("slname". $this->sid,40,NULL,"lastname","student",$this->sid,"sid",NULL,"datahandler.php");
	}
	return($this->nameflds['firstname']->__toString(). " ". $this->nameflds['lastname']->__toString());
  }

  public function get_lastname()
  {
    if($this->sid == 0)
	  return NULL;
    if(!isset($this->nameflds))
	{
	  $this->nameflds['firstname'] = new inputclass_textfield("sfname". $this->sid,40,NULL,"firstname","student",$this->sid,"sid",NULL,"datahandler.php");
	  $this->nameflds['lastname'] = new inputclass_textfield("slname". $this->sid,40,NULL,"lastname","student",$this->sid,"sid",NULL,"datahandler.php");
	}
	return($this->nameflds['lastname']->__toString());
  }

  public function get_firstname()
  {
    if($this->sid == 0)
	  return NULL;
    if(!isset($this->nameflds))
	{
	  $this->nameflds['firstname'] = new inputclass_textfield("sfname". $this->sid,40,NULL,"firstname","student",$this->sid,"sid",NULL,"datahandler.php");
	  $this->nameflds['lastname'] = new inputclass_textfield("slname". $this->sid,40,NULL,"lastname","student",$this->sid,"sid",NULL,"datahandler.php");
	}
	return($this->nameflds['firstname']->__toString());
  }

  public function validate_password($password)
  {
    if($this->userid == 0)
	  return false;
    if(!isset($this->passwfld))
	  $this->passwfld = new inputclass_passwordfield("upassw". $this->userid,40,NULL,"password","users",$this->userid,"userid",NULL,"datahandler.php");
	$storedpassw = $this->passwfld->__toString();
	$validateok = false;
	if($storedpassw == "" && $password == "")
	  $validateok = true;
	if($storedpassw == md5($password))
	  $validateok = true;
	if($validateok)
	{ // Store this one as the current user
	  $_SESSION['userid'] = $this->userid;
	  $_SESSION['username'] = $this->get_username();
	}
	return $validateok;
  }
  public function edit_password()
  {
    if(!isset($this->passwfld))
	  $this->passwfld = new inputclass_passwordfield("upassw". $this->userid,40,NULL,"password","users",$this->userid,"userid",NULL,"datahandler.php");
	$this->passwfld->echo_html();
  }
  public function edit_username()
  {
    if(!isset($this->usernamefld))
	  $this->usernamefld = new inputclass_textfield("uname". $this->userid,40,NULL,"username","users",$this->userid,"userid",NULL,"datahandler.php");
	$this->usernamefld->echo_html();
  }
  
  public function get_groups()
  {
    if($this->sid != 0)
		{
			$gidq = "SELECT gid FROM sgrouplink WHERE sid=". $this->sid;
			$gidd = inputclassbase::load_query($gidq);
			if(isset($gidd['gid'][0]))
			{
				foreach($gidd['gid'] AS $agid)
					$retval[$agid] = new group($agid);
			}
			else
				$retval[0] = new group(0);
		}
		else
			$retval = new group(0);
		return $retval;
  }

  public function get_primary_group()
  {
		global $PrimaryGroupFilter;
    if($this->sid != 0)
		{
			if(isset($PrimaryGroupFilter))
				$grpfilt = $PrimaryGroupFilter;
			else
				$grpfilt = "__";
			$gidq = "SELECT gid FROM sgrouplink LEFT JOIN sgroup USING(gid) WHERE sid=". $this->sid. " AND (groupname LIKE '". $grpfilt. "')";
			$gidd = inputclassbase::load_query($gidq);
			if(isset($gidd['gid'][0]))
			{
				$retval = new group($gidd['gid'][0]);
			}
			else
				$retval = new group(0);
		}
		else
			$retval = new group(0);
		return $retval;
  }
	
	public function get_mentor_code()
	{
		global $teachercode;
		$grp = $this->get_primary_group();
		$ment = $grp->get_mentor();
		if(isset($ment) && isset($teachercode))
			return($ment->get_teacher_detail($teachercode));
		else
			return("");
	}

  public static function student_list($group = NULL)
  {
    if(isset($_POST['ssorterfld']))
	{ // A new value was posted for sorting
	  $_SESSION['ssortertable'] = $_POST['ssorterfld'];
	}
    if(!isset($group))
	{
	  $group = new group();
	  $group->load_current();
	}
	if($group->get_id() == 0)
	  return NULL;
	$uqs = "SELECT sid";
	if(isset($_SESSION['ssortertable']) && $_SESSION['ssortertable'] != '' && $_SESSION['ssortertable'] != '-')
	  $uqs .= ",data";
	$uqs .= " FROM student LEFT JOIN sgrouplink USING(sid)";
	if(isset($_SESSION['ssortertable']) && $_SESSION['ssortertable'] != '' && $_SESSION['ssortertable'] != '-')
	  $uqs .= " LEFT JOIN `". $_SESSION['ssortertable']. "` USING(sid)";
    $uqs .= " WHERE gid = ". $group->get_id(). " ORDER BY ";
	if(isset($_SESSION['ssortertable']) && $_SESSION['ssortertable'] != '' && $_SESSION['ssortertable'] != '-')
	  $uqs .= " data,";
	if(isset($_SESSION['ssortertable']) && $_SESSION['ssortertable'] == '-')
	  $uqs .= "firstname,lastname";
	else
	  $uqs .= "lastname,firstname";
    $users = inputclassbase::load_query($uqs);
	
	if(isset($_SESSION['ssortertable']) && $_SESSION['ssortertable'] != '' && $_SESSION['ssortertable'] != '-')
	  $curdata = $users['data'][0];
	if(isset($users['sid']))
	{
		$totstud=0;
		$emptystud=0;
	  foreach($users['sid'] AS $six => $sid)
	  {
	    if(isset($_SESSION['ssortertable']) && $_SESSION['ssortertable'] != ''  && $_SESSION['ssortertable'] != '-' && $users['data'][$six] != $curdata)
			{ // insert empty record betwen sorting data changes
				$curdata = $users['data'][$six];
				$userlist[$curdata] = NULL;
				$emptystud++;
			}
	    $userlist[$sid] = new student($sid);
			$totstud++;
	  }
		if($totstud-$emptystud==1)
		{ // Each record is unique so forget about empty records!
			foreach($userlist AS $dd => $usd)
			{
				if($usd == NULL)
					unset($userlist[$dd]);
			}			
		}
	  return($userlist);
	}
	else
	  return NULL;
  }
  
  public static function get_list_headers($gid = NULL)
  {
    global $TSmode;
	if(isset($TSmode) && $TSmode)
      $fields = inputclassbase::load_query("SELECT label,table_name,raccess FROM student_details WHERE toverview=1 ORDER BY seq_no");
	else
      $fields = inputclassbase::load_query("SELECT label,table_name,raccess FROM student_details WHERE overview=1 ORDER BY seq_no");
	if(isset($gid))
	  $grp = new group($gid);
	else
	{
	  $grp = new group();
	  $grp->load_current();
	}
	$I = new teacher();
	$I->load_current();

	if(isset($fields))
	{
	  $ix = 0;
	  foreach($fields['label'] AS $dix => $label)
	  {
	    $ac = $fields['raccess'][$dix];
	    if($ac == 'A' || ($ac == 'T' && $I->get_id() != 0) || ($ac == 'M' && $I->get_id() == $grp->get_mentor()->get_id()) ||
		   ($ac == 'C' && $I->has_role("counsel")) || $I->has_role("admin") || ($ac == 'O' && $I->has_role("office")) || 
		   ($ac=='P' && ($I->get_id() == $grp->get_mentor()->get_id() || $I->has_role("office"))))
		{
	      $labellist[$ix++] = $label;
		}
	  }
	  if(isset($labellist))
	    return($labellist);
	}
    return NULL;
  }

  public function get_list_data($gid = NULL)
  {
    global $TSmode, $carecodetable, $carecodecolors;
    global $overviewimageheight;
	if(!isset($overviewimageheight))
	  $overviewimageheight=16;
	// Since package info is only shown if member of group to filter or edit packages, check it and set flags accordingly
	if(isset($gid))
	  $grp = new group($gid);
	else
	{
	  $grp = new group();
	  $grp->load_current();
	}
	$I = new teacher();
	$I->load_current();
	if(isset($TSmode) && $TSmode)
      $fields = inputclassbase::load_query("SELECT table_name,raccess FROM student_details WHERE toverview=1 ORDER BY seq_no");
	else
      $fields = inputclassbase::load_query("SELECT table_name,raccess FROM student_details WHERE overview=1 ORDER BY seq_no");
	if(isset($fields))
	{
	  $ix = 0;
	  foreach($fields['table_name'] AS $dix => $tablename)
	  {
	    $ac = $fields['raccess'][$dix];
	    if($ac == 'A' || ($ac == 'T' && $I->get_id() != 0) || ($ac == 'M' && $I->get_id() == $grp->get_mentor()->get_id()) ||
		   ($ac == 'C' && $I->has_role("counsel")) || $I->has_role("admin") || ($ac == 'O' && $I->has_role("office")) || 
		   ($ac=='P' && ($I->get_id() == $grp->get_mentor()->get_id() || $I->has_role("office"))))
		{
	      $datalist[$ix] = $this->get_student_detail($tablename,$overviewimageheight);
		  if(isset($carecodetable))
		  {
		    $careprefix = inputclassbase::load_query("SELECT `". $carecodecolors. "`.tekst AS pref FROM `". $carecodetable. "` LEFT JOIN `". $carecodecolors. "` ON(data=id) WHERE sid=". $this->get_id());
			if(isset($careprefix['pref'][0]))
			{
			  // See what label the carecode field has
			  $cclabelqr = inputclassbase::load_query("SELECT label FROM student_details WHERE table_name='". $carecodetable. "'");
			  if(isset($cclabelqr['label']))
			    $label = $cclabelqr['label'][0];
			  else
			    $label = "";
			  $datalist[$ix] = "<SPAN STYLE='". $careprefix['pref'][0]. "' title='". $label. ": ". $this->get_student_detail($carecodetable). "'>". $datalist[$ix]. "</span>";
			}
		  }
		  $ix++;
		}
      }
	  if(isset($datalist))
	    return($datalist);
	}
	return NULL;
  }
  
  public function get_student_detail($tablename,$imageheight=NULL)
  {
    return $this->do_student_detail($tablename,false,$imageheight);
  }
  
  public function edit_student_detail($tablename)
  {
    return $this->do_student_detail($tablename,true);
  }

  private function do_student_detail($tablename,$edit,$imageheight=NULL)
  {
    global $altsids,$livepictures;
	global $student_sequence_number;
	$dtext = $_SESSION['dtext'];
    if(substr($tablename,0,1) == "*")
	{
	  switch($tablename)
	  {
	    case "*student.firstname":
		  $fielddata = new inputclass_textfield("sdfirstname". $this->sid,40,NULL,"firstname","student",$this->sid,"sid");
		  break;
		case "*student.lastname":
		  $fielddata = new inputclass_textfield("sdlastname". $this->sid,40,NULL,"lastname","student",$this->sid,"sid");
		  break;
		case "*sid":
		  if(isset($altsids) && $altsids == 1)
		    $fielddata = new inputclass_textfield("sd*sid". $this->sid,40,NULL,"altsid","student",$this->sid,"sid");
	      else
		    $fielddata = new inputclass_textfield("sd*sid". $this->sid,40,NULL,"sid","student",$this->sid,"sid");
		  break;
		case "*sgroup.groupname":
		  if($edit)
		    $fielddata = new inputclass_catmultiselect("sdsgroup". $this->sid,"SELECT gid AS id, groupname AS tekst, SUBSTRING(UPPER(groupname),1,1) AS cat FROM sgroup WHERE active=1 ORDER BY groupname",NULL,"gid","sgrouplink",$this->sid,"sid");
          else
		    $fielddata = new inputclass_multiselect("sdsgroup". $this->sid,"SELECT gid AS id, groupname AS tekst FROM sgroup WHERE active=1 ORDER BY groupname",NULL,"gid","sgrouplink",$this->sid,"sid");
		  break;
		case "*gradestore.*":
		  return $this->show_grades();
		  break;
		case "*absence.*":
		  return $this->show_absence();
		  break;
		case "*package":
		  if($edit)
		  {
		    $pkgs = inputclassbase::load_query("SELECT DISTINCT packagename FROM subjectpackage ORDER BY packagename");
		    $selq = "SELECT '' AS id, '' AS tekst";
		    if(isset($pkgs['packagename']))
		      foreach($pkgs['packagename'] AS $pkgnm)
		        $selq .= " UNION SELECT '". $pkgnm. "','". $pkgnm. "'";
		    $fielddata = new inputclass_listfield("sdpack1". $this->sid,$selq,NULL,"packagename", "s_package", $this->sid, "sid");
			$fielddata->echo_html();
			echo(" ". $dtext['extra_subject1']. ": ");
			$fielddata = new inputclass_listfield("sdpack2". $this->sid,"SELECT 0 AS id, '' AS tekst UNION SELECT mid,shortname FROM subject ORDER BY tekst", NULL, "extrasubject", "s_package", $this->sid, "sid");
			$fielddata->echo_html();
			echo(" ". $dtext['extra_subject2']. ": ");
			$fielddata = new inputclass_listfield("sdpack3". $this->sid,"SELECT 0 AS id, '' AS tekst UNION SELECT mid,shortname FROM subject ORDER BY tekst", NULL, "extrasubject2", "s_package", $this->sid, "sid");
			$fielddata->echo_html();
			echo(" ". $dtext['extra_subject3']. ": ");
			$fielddata = new inputclass_listfield("sdpack4". $this->sid,"SELECT 0 AS id, '' AS tekst UNION SELECT mid,shortname FROM subject ORDER BY tekst", NULL, "extrasubject3", "s_package", $this->sid, "sid");
		  }
		  else
		  {
		    return $this->show_package();
	    }
		  break;
		case "*seq_no":
		  if(!isset($student_sequence_number))
		    $student_sequence_number = 1;
		  if($edit)
		    echo($student_sequence_number++);
		  else
		    return($student_sequence_number++);
		  break;
		case "*grouphistory.*":
		  return $this->show_grouphistory();
		  break;
		// Need to add more cases here!
	  }
	}
	else
	{
      // get the info about this field
	  $fsdata = inputclassbase::load_query("SELECT * FROM student_details WHERE table_name='". $tablename. "'");
	  if(isset($fsdata))
	  {
        switch($fsdata['type'][0])
	    {
	      case "text":
		    if($fsdata['multi'][0] == "Y")
		      $fielddata = new inputclass_multitext("sd". $tablename. $this->sid,$fsdata['size'][0],NULL,"data",$tablename,$this->sid,"sid");
			else
		      $fielddata = new inputclass_textfield("sd". $tablename. $this->sid,$fsdata['size'][0],NULL,"data",$tablename,$this->sid,"sid");
		    break;
		  case "picture":
	        $params = explode("@",$fsdata['params'][0]);
            $imagedata = inputclassbase::load_query("SELECT data FROM `" . $fsdata['table_name'][0] . "` WHERE sid='$this->sid'");
            if(isset($imagedata['data'][0]))
            {
              $pstring = "<IMG SRC='" . $livepictures . $imagedata['data'][0] . "'". (isset($params[1]) ? " style=\"position:absolute;". $params[1]. "\"" : "");
			  if($imageheight == NULL)
			    $pstring .= (isset($params[0]) ? " WIDTH=". $params[0]. "px" : ""). ">";
			  else
			  {
			    $pstring .= " HEIGHT='". $imageheight. "px'";
				if(isset($params[0]))
				{ // Overview height and parameterized hiegt set, so enable toggling on mouse over
				  $pstring .= " onMouseover=\"this.style.height='". $params[0]. "px';\" onMouseout=\"this.style.height='". $imageheight. "px';\""; 
				}
				$pstring .= ">";
			  }
            }
            else
              $pstring = $dtext['no_pic'];
			if($edit)
			{ // Adding form for image editing
			  $pstring .= "<FORM NAME=sdf". $tablename. "-". $this->sid. " id=sdf". $tablename. "-". $this->sid. " METHOD=POST ACTION=". $_SERVER['REQUEST_URI']. " ENCTYPE='multipart/form-data'>";
			  $pstring .= "<INPUT type=hidden name=edit value=". $this->sid. ">";
			  $pstring .= "<INPUT type=file name=sd". $tablename. "-". $this->sid. " onChange='document.forms[\"sdf". $tablename. "-". $this->sid. "\"].submit();'>";
			  $pstring .= "</FORM>";
			}
			return $pstring;
			break;
		  case "choice":
		    $choices = explode(",",$fsdata['params'][0]);
			$choiceq = "SELECT '' AS id, '' AS tekst";
			if(substr($choices[0],0,1) == "*")
			  $choiceq .= " UNION SELECT id,tekst FROM ". substr($choices[0],1). " ORDER BY tekst";
			else
			{
			  foreach($choices AS $ch)
			    $choiceq .= " UNION SELECT '". $ch. "','". $ch. "'";
			}
			if($fsdata['multi'][0] == "Y")
			  $fielddata = new inputclass_multiselect("sd". $tablename. $this->sid,$choiceq,NULL,"data",$tablename,$this->sid,"sid");
			else
			  $fielddata = new inputclass_listfield("sd". $tablename. $this->sid,$choiceq,NULL,"data",$tablename,$this->sid,"sid");
            break;
		  // Need to add other cases here! 
	    }
	  }
    } // End else for special fields
	if(isset($fielddata))
	{
	  if($edit)
	  {
	    $fielddata->echo_html();
		return("");
	  }
	  else
	  {
	    return($fielddata->__toString());
	  }
	}
  }
  
  private function show_grades()
  { // grades stored
    // First get the year, we'll do a new query fro each year 
	$dtext = $_SESSION['dtext'];
	$years = inputclassbase::load_query("SELECT DISTINCT year FROM gradestore WHERE sid='$this->sid' ORDER BY year DESC");
	if(isset($years['year']))
	{
	  $retstr = "";
	  foreach($years['year'] AS $year)
      { // create a grade table for 1 year
			  unset($grade_array);
        $retstr .= "<br>" . $dtext['Grades4'] . " " . $year . " :";
        // Now we do a query to get all results for a year, and store them in a array
		$gqry = "SELECT * FROM gradestore LEFT JOIN (SELECT mid,AVG(show_sequence) AS ss,fullname FROM subject LEFT JOIN class USING(mid) GROUP BY mid) AS t1 USING (mid) WHERE sid='$this->sid' AND year='$year' GROUP BY mid,period ORDER BY ss,mid,period";
		$grades_result = inputclassbase::load_query($gqry);
        $periodhi = -1;
        $periodlo = 1000;
		foreach($grades_result['sid'] AS $g => $dummy)
		{
          $grade_array['subject'][$g] = $grades_result['fullname'][$g];
          $grade_array['period'][$g] = $grades_result['period'][$g];
          $grade_array['result'][$g] = $grades_result['result'][$g];
          $grade_array['mid'][$g] = $grades_result['mid'][$g];
          if(intval($grades_result['period'][$g]) > $periodhi)
            $periodhi = intval($grades_result['period'][$g]);
          if(intval($grades_result['period'][$g]) < $periodlo)
            $periodlo = intval($grades_result['period'][$g]);
        }
        // And now, we create a nice header for the table:
        $retstr .= "<table border=1 celpadding=2>";
        $retstr .= "<tr><td><center>" . $dtext['Subject'] . "</td>";
        for($c=$periodlo; $c<=$periodhi; $c++)
        {
          if($c == 0)
            $retstr .= "<td><center>" . $dtext['Final'] . "</td>";
          else
            $retstr .= "<td><center>" . $dtext['Period'] . " " . $c . "</td>";
        }
        $retstr .= "<tr>";
        // Now we need to put the results in the table!
        $perpos = $periodlo;
		foreach($grade_array['subject'] AS $g => $subj)
		{
          // See if we need to start a new row...
          if(!isset($grade_array['mid'][$g-1]) || $grade_array['mid'][$g] != $grade_array['mid'][$g-1])
          { // new row!
            if($perpos != $periodlo)
              $retstr .= "</tr><tr>";	// Must close previous row and start a new one
            else
              $retstr .= "<tr>";		// Open new row (the first one)
            $perpos = $periodlo;
            // Put in the subject!
            $retstr .= "<td>" . $subj . "</td>";
          }
          // fill with '-' signs in periods which are not filled.
          for($p=$perpos;$p<intval($grade_array['period'][$g]);$p++)
          {
            $perpos++;
            $retstr .= "<td><center>-</td>";
          }
          // now we can put in the result!
          $perpos++;
          $retstr .= "<td><center>" . $grade_array['result'][$g] . "</td>";
        }
        $retstr .= "</table>";
      } // end year
	  return $retstr;
    } // end if years in grades available
    else
    {
      return($dtext['No_grades']);
    }
  }
  
  private function show_absence()
  { // absence records
	global $hideoldabsence;
    // if $hideoldabsence is set, we don't show that info
	if(isset($hideoldabsence) && $hideoldabsence)
	{
	  $startdate = inputclassbase::load_query("SELECT MIN(startdate) AS sdat FROM period");
	  if(isset($startdate['sdat'][0]))
	    $startdate = $startdate['sdat'][0];
      else
	    $startdate='1971-01-01';
		$startdatesqr = inputclassbase::load_query("SELECT id,startdate FROM period ORDER BY startdate");
		foreach($startdatesqr['id'] AS $pix => $pid)
		  $startdates[$pid] = $startdatesqr['startdate'][$pix];
	}
	else $startdate = '1971-01-01';
    $dtext = $_SESSION['dtext'];
    $sql_query = "SELECT * FROM absence INNER JOIN absencereasons USING (aid) WHERE sid='$this->sid' AND date >= '". $startdate. "' ORDER BY date DESC,time";
	$absdata = inputclassbase::load_query($sql_query);
	if(!isset($absdata))
      $retstr = $dtext['No_abs_recs'];
    else
    { // absence records found, create the table
      $retstr = "<table border=1 celpadding=2>";
      // Add the heading
      $retstr .= "<tr><td><center>";
			if(isset($hideoldabsence) && $hideoldabsence)
			  $retstr .= $dtext['Period']. "</td><td><center>";	
			$retstr .= $dtext['Date'] . "</td><td><center>" . $dtext['Time'] . "</td><td><center>" . $dtext['Reason'] . "</td><td><center>" . $dtext['Authorization'] . "</td></tr>";
	  foreach($absdata['date'] AS $aix => $absdate)
      { // Add the details for each row.
			  // Start finding out which trimester if old dabsence is hidden
			  if(isset($hideoldabsence) && $hideoldabsence)
				{
					$trim=1;
					foreach($startdates AS $pid => $ped)
					  if($absdate > $ped)
						  $trim=$pid;
					$retstr .= "<tr><td><center>". $trim. "</td>";
				}
				else
					$retstr .= "<tr>";
				
        $retstr .= "<td><center>" . inputclassbase::mysqldate2nl($absdate) . "</td>";
        $retstr .= "<td><center>" . $absdata['time'][$aix] . "</td>";
        $retstr .= "<td>" . $absdata['description'][$aix] . "</td>";
        $retstr .= "<td><center>" . $dtext[$absdata['authorization'][$aix]] . "</td></tr>";
      }
      $retstr .= "</table>";
    }
	return $retstr;
  }
  
  private function show_grouphistory()
  { // group history records
    $dtext = $_SESSION['dtext'];
    $sql_query = "SELECT year,GROUP_CONCAT(groupname) AS hist FROM (SELECT year,groupname FROM testresult LEFT JOIN testdef USING(tdid) LEFT JOIN class USING(cid) LEFT JOIN sgroup USING(gid) WHERE sid=". $this->get_id(). " GROUP BY year,groupname ORDER BY year,groupname) AS t1 GROUP BY year ORDER BY year";
	$ghisdata = inputclassbase::load_query($sql_query);
	if(!isset($ghisdata))
      $retstr = "-";
    else
    { // group history records found, create the table
      $retstr = "<table border=1 celpadding=2>";
      // Add the heading
      $retstr .= "<tr><td><center>" . $dtext['Year'] . "</td><td><center>" . $dtext['in_grp'] . "</td></tr>";
	  foreach($ghisdata['year'] AS $aix => $ghisyr)
      { // Add the details for each row.
        $retstr .= "<tr><td><center>" . $ghisyr . "</td>";
        $retstr .= "<td><center>" . $ghisdata['hist'][$aix] . "</td></tr>";
      }
      $retstr .= "</table>";
    }
	return $retstr;
  }
  
  
  
  private function show_package()
  { // subject package
    $dtext = $_SESSION['dtext'];
		$subjects = inputclassbase::load_query("SELECT * FROM subject ORDER BY shortname");
		$studpackage = inputclassbase::load_query("SELECT * FROM s_package WHERE sid=". $this->sid);
    if(isset($studpackage['packagename'][0]))
    {
      $retstr = $studpackage['packagename'][0]. " (";
			$package = inputclassbase::load_query("SELECT * FROM subjectpackage LEFT JOIN subject USING(mid) WHERE packagename='". $studpackage['packagename'][0]. "' ORDER BY shortname");
			$firstfield=1;
			if(isset($package['shortname']))
			foreach($package['shortname'] AS $sname)
			{
				if($firstfield != 1)
					$retstr .= ",";
				$retstr .= $sname;
						$firstfield = 0;
			}
			$retstr .= ")";
			if($studpackage['extrasubject'][0] != 0)
			{
				$retstr .= " ". $dtext['extra_subject1']. " : ";
				foreach($subjects['mid'] AS $six => $smid)
				{
						if($smid == $studpackage['extrasubject'][0])
							$retstr .= $subjects['shortname'][$six];
				}
			}
			if($studpackage['extrasubject2'][0] != 0)
			{
				$retstr .= " ". $dtext['extra_subject2']. " : ";
				foreach($subjects['mid'] AS $six => $smid)
				{
						if($smid == $studpackage['extrasubject2'][0])
							$retstr .= $subjects['shortname'][$six];
				}
			}
			if($studpackage['extrasubject3'][0] != 0)
			{
				$retstr .= " ". $dtext['extra_subject3']. " : ";
				foreach($subjects['mid'] AS $six => $smid)
				{
						if($smid == $studpackage['extrasubject3'][0])
							$retstr .= $subjects['shortname'][$six];
				}
			}
		}
		else
      $retstr = $dtext['No_data'];
		return $retstr;
  }

  
  public static function list_viewdetails()
  {
    global $currentuser; // If set refers to a teacher object (teacher login, no set with student or parent login)
    $fsdata = inputclassbase::load_query("SELECT * FROM student_details WHERE table_name <> '*seq_no' ORDER BY seq_no");
	if(isset($currentuser))
	  $I= $currentuser;
	else
	  $I = new teacher(0);
	$dgroup = new group();
	$dgroup->load_current();
	if(isset($fsdata))
	{
	  foreach($fsdata['table_name'] AS $dix => $tname)
	  {
	    $ac = $fsdata['raccess'][$dix];
	    if(($_SESSION['LoginType'] == 'S' && $ac == 'A') || ($_SESSION['LoginType'] != "S" && ($ac == 'A' || ($ac == 'T' && isset($currentuser)) || ($ac == 'M' && (isset($currentuser) && $I->get_id() == $dgroup->get_mentor()->get_id())) ||
		   ($ac == 'C' && $I->has_role("counsel")) || $I->has_role("admin") || ($ac == 'O' && $I->has_role("office")) || 
		   ($ac=='P' && ($I->get_id() == $dgroup->get_mentor()->get_id() || $I->has_role("office"))))))
			{
				$resdata[$tname] = $fsdata['label'][$dix];
			}
	  }
	}
	else
	  return NULL;
	return ($resdata);
  }

  public static function list_editdetails()
  {
    $fsdata = inputclassbase::load_query("SELECT * FROM student_details WHERE table_name <> '*seq_no' ORDER BY seq_no");
	$I = new teacher();
	$I->load_current();
	$dgroup = new group();
	$dgroup->load_current();
	if(isset($fsdata))
	{
	  foreach($fsdata['table_name'] AS $dix => $tname)
	  {
	    $ac = $fsdata['waccess'][$dix];
	    if($ac == 'A' || ($ac == 'T' && $I->get_id() != 0) || ($ac == 'M' && ($I->get_id() == $dgroup->get_mentor()->get_id() || $I->has_role("counsel"))) ||
		   ($ac == 'C' && $I->has_role("counsel")) || $I->has_role("admin") || ($ac == 'O' && $I->has_role("office")) ||
		   ($ac=='P' && ($I->get_id() == $dgroup->get_mentor()->get_id() || $I->has_role("office"))))
		{
		  $resdata[$tname] = $fsdata['label'][$dix];
		}
	  }
	}
	if(isset($resdata))
	  return $resdata;
	else
	  return NULL;
  }
  
  public function get_abscount($authorized = NULL, $cat = NULL)
  {
    $ystqr = inputclassbase::load_query("SELECT MIN(startdate) AS yst FROM period");
		if(!isset($ystqr['yst'][0]))
			$yst = date("Y-m-d",mktime(0,0,0,date("n"),date("j"),date("Y")-1));
		else
			$yst = $ystqr['yst'][0];
		$abq = "SELECT COUNT(asid) AS abscount FROM absence LEFT JOIN absencereasons USING(aid) LEFT JOIN absencecats USING(acid) ";
		if($authorized === NULL)
			$abq .= "WHERE sid=". $this->get_id();
		else if($authorized == TRUE)
			$abq .= "WHERE authorization='Yes' AND sid=". $this->get_id();
		else // NOT authorized
			$abq .= "WHERE authorization<>'Yes' AND sid=". $this->get_id();
		$abq .= " AND date >= '". $yst. "' AND countabs=1";
		if(isset($cat))
			$abq .= " AND acid=". $cat->get_id();
		$absd = inputclassbase::load_query($abq);
		if(isset($absd['abscount']))
			return $absd['abscount'][0];
		else
			return 0;
  }
  
  public function get_absstate($acat, $absdate = NULL, $absmid = NULL)
  { // Return true if absence is registered for the student for the category on the date for the subject (if applicable)
    // First get the missing values
		if($absdate==NULL)
			$absdate=mktime();
		if($absmid==NULL)
			if(isset($_SESSION['CurrentSubject']))
				$absmid = $_SESSION['CurrentSubject'];
			else
				$absmid = 0;
			// Do the query
		$absqr = inputclassbase::load_query("SELECT acid FROM absence LEFT JOIN absencereasons USING(aid) LEFT JOIN absencecats USING(acid)
																					WHERE sid=". $this->get_id(). " AND date='". date("Y-m-d",$absdate). "' AND acid=". $acat->get_id().
												" AND (class=". $absmid. " OR classuse=0)");
		return(isset($absqr['acid']));
  }

  public function get_reportcount($fromdate=NULL,$repcat=0)
  {
    if(!isset($fromdate))
		{
				$fd = inputclassbase::load_query("SELECT MIN(startdate) AS fd FROM period");
			$fromdate = $fd['fd'][0];
		}
    $rc = inputclassbase::load_query("SELECT COUNT(rid) AS repcount FROM reports WHERE sid=". $this->get_id(). " AND date >= '". $fromdate. "'". ($repcat == 0 ? "" : " AND rcid=". $repcat));
		if(isset($rc['repcount']))
			return $rc['repcount'][0];
		else
			return 0;
  }
	
}
?>