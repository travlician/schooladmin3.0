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
require_once("inputlib/inputclasses.php");
require_once("group.php");

class teacher
{
  public static $definedroles=array(1 => "admin","counsel","arman","office"); 

  protected $userid;  
  protected $usernameflds;
  protected $passwfld;
  protected $rolesfld;
  protected $gonefld;
  protected $loadedroles; // Stored roles, in order to optimise rol check requests
  public function __construct($userid = NULL)
  {
    global $picturespath;
    if(isset($userid))
	  $this->userid = $userid;
	else
	  $this->userid = 0;
	if(isset($_FILES))
	  foreach($_FILES AS $fn => $fobj)
	  {
	    if(substr($fn,0,2) == "td" && (substr($fn, -1 - strlen($this->userid)) == "-". $this->userid))
		{ // This files is a picture for this teacher object!
		  $tabnam = substr($fn, 2, strlen($fn) - 3 - strlen($this->userid));
		  if($fobj["error"] == UPLOAD_ERR_OK)
		  {
            $newfilename = $tabnam .$userid;
            // Copy the extension from the tmp_file!
            $extension = (strstr($fobj['name'],'.')) ? @strstr($fobj['name'],'.') : '.file';
            $newfilename .= $extension;
			if(move_uploaded_file($fobj['tmp_name'],$picturespath. $newfilename))
			{ // File moved, now update database
              $sql_query = "REPLACE `" . $tabnam . "` VALUES (" . $userid . ",'" . $newfilename . "')";
              mysql_query($sql_query);  
			}
		  }
		}
	  }
  }
  public function load_from_username($lastname,$firstname)
  {
    $udata = inputclassbase::load_query("SELECT tid FROM teacher WHERE firstname = '". $firstname. "' AND lastname= '". $lastname. "'");
	if(isset($udata))
	  $this->userid = $udata['userid'][0];
  }
  public function load_current()
  {
    if(isset($_SESSION['uid']))
      $this->userid = $_SESSION['uid'];
	else
	  $this->userid = 0;
  }
  
  public function get_id()
  {
    return $this->userid;
  }
  
  public function get_username()
  {
    if($this->userid == 0)
	  return NULL;
    if(!isset($this->usernameflds))
	{
	  $this->usernameflds['firstname'] = new inputclass_textfield("tfname". $this->userid,40,NULL,"firstname","teacher",$this->userid,"tid",NULL,"datahandler.php","lastmodifiedby", $_SESSION['uid']);
	  $this->usernameflds['lastname'] = new inputclass_textfield("tlname". $this->userid,40,NULL,"lastname","teacher",$this->userid,"tid",NULL,"datahandler.php","lastmodifiedby", $_SESSION['uid']);
	}
	return($this->usernameflds['firstname']->__toString(). " ". $this->usernameflds['lastname']->__toString());
  }

  public function get_roles()
  {
    if(isset($this->loadedroles))
	  return($this->loadedroles);
    if(!isset($this->rolesfld))
	{
	  foreach(self::$definedroles AS $rix => $role)
	  {
	    if($rix == 1)
		  $selquery = "SELECT 1 AS id, '". $role. "' AS tekst";
		else
		  $selquery .= " UNION SELECT ". $rix. ",'". $role. "'";
	  }
	  $this->rolesfld = new inputclass_multiselect("troles". $this->userid,$selquery,NULL,"role","teacherroles",$this->userid,"tid",NULL,"datahandler.php");
	}
	$this->loadedroles = $this->rolesfld->__toString();
	return($this->loadedroles);
  }
  
  public function has_role($role)
  {
    if($this->userid == 0)
			return false;
		if($role == "teacher")
			return true;
		if($role == "mentor")
		{
			$curgrp = new group();
			$curgrp->load_current();
			if($curgrp->get_mentor() != NULL)
				return($this->get_id() == $curgrp->get_mentor()->get_id());
				else
				return false;
		}
		else if(strstr($this->get_roles(),$role))
			return true;
		else
			return false;
  }
  
  public function validate_password($password)
  {
		echo("Validating password ". $password);
    if($this->userid == 0)
	  return false;
    if(!isset($this->passwfld))
			$this->passwfld = new inputclass_password("upassw". $this->userid,40,NULL,"password","teacher",$this->userid,"tid",NULL,"datahandler.php");
		$storedpassw = $this->passwfld->__toString();
		$validateok = false;
		if($storedpassw == "" && $password == "")
			$validateok = true;
		if($storedpassw == md5($password))
			$validateok = true;
		if($storedpassw == $password)
			$validateok = true;
		if($validateok)
		{ // Store this one as the current user
			$_SESSION['userid'] = $this->userid;
			$_SESSION['username'] = $this->get_username();
		}
		return $validateok;
  }
  
  public function get_password()
  {
    $this->get_passwfield();
	return $this->passwfld->__toString();
  }
  public function edit_password()
  {
    $this->get_passwfield();
	$this->passwfld->echo_html();
  }
  
  private function get_passwfield()
  {
    if(!isset($this->passwfld))
	  $this->passwfld = new inputclass_password("upassw". $this->userid,40,NULL,"password","teacher",$this->userid,"tid",NULL,"datahandler.php");   
  }
  
  public function get_isgone()
  {
    $this->get_gonefield();
	return $this->gonefld->__toString();
  }
  public function edit_isgone()
  {
    $this->get_gonefield();
	$this->gonefld->echo_html();
  }
  
  private function get_gonefield()
  {
    if(!isset($this->gonefld))
	  $this->gonefld = new inputclass_checkbox("upgone". $this->userid,0,NULL,"is_gone","teacher",$this->userid,"tid",NULL,"datahandler.php");   
  }
  
  public function get_pwexpiry()
  {
	  $expiryfld = new inputclass_datefield("pwexpiry". $this->userid,0,NULL,"pwexpirydate","teacher",$this->userid,"tid",NULL,"datahandler.php"); 
		return($expiryfld->__toString());
  }
  
  public function is_gone()
  {
    if($this->get_isgone() == "Y")
	  return true;
	else
	  return false;
  }
  
  /*
  public function edit_username()
  {
    if(!isset($this->usernamefld))
	  $this->usernamefld = new inputclass_textfield("uname". $this->userid,40,NULL,"username","users",$this->userid,"userid",NULL,"datahandler.php","lastmodifiedby", $_SESSION['uid']);
	$this->usernamefld->echo_html();
  } */

  public function edit_roles()
  {
    if(!isset($this->rolesfld))
	{
	  foreach(self::$definedroles AS $rix => $role)
	  {
	    if($rix == 1)
		  $selquery = "SELECT 1 AS id, '". $role. "' AS tekst";
		else
		  $selquery .= " UNION SELECT ". $rix. ",'". $role. "'";
	  }
	  $this->rolesfld = new inputclass_multiselect("troles". $this->userid,$selquery,NULL,"role","teacherroles",$this->userid,"tid",NULL,"datahandler.php","lastmodifiedby", $_SESSION['uid']);
	}
	$this->rolesfld->echo_html();
  }
  
  public static function teacher_list()
  {
    $users = inputclassbase::load_query("SELECT tid FROM teacher ORDER BY lastname,firstname");
	foreach($users['tid'] AS $tid)
	  $userlist[$tid] = new teacher($tid);
	return($userlist);
  }

  public static function active_list()
  {
    $users = inputclassbase::load_query("SELECT tid FROM teacher WHERE is_gone <> 'Y' ORDER BY lastname,firstname");
	foreach($users['tid'] AS $tid)
	  $userlist[$tid] = new teacher($tid);
	return($userlist);
  }

  public function get_defaultgroup()
  {
    $defgrp = inputclassbase::load_query("SELECT * FROM sgroup WHERE active=1 AND tid_mentor = '" . $this->userid ."'");
	if(!isset($defgrp))
      $defgrp = inputclassbase::load_query("SELECT sgroup.gid, sgroup.groupname FROM sgroup,class WHERE active=1 AND sgroup.gid = class.gid AND class.tid = '" . $this->userid ."'");
	if(!isset($defgrp))
	{ // No groups found, take any group for admin or counseller
	  if($this->has_role("admin") || $this->has_role("counsel"))
	    $defgrp = inputclassbase::load_query("SELECT * FROM sgroup WHERE active=1");
    }
		if(isset($defgrp))
			return($defgrp['groupname'][0]);
		return NULL;
  }
	
	public function get_preference($prefkey)
	{
		$prqr = inputclassbase::load_query("SELECT avalue FROM teacherpreferences WHERE tid=". $this->userid. " AND aspect='". $prefkey. "'");
		if(isset($prqr['avalue']))
			return($prqr['avalue'][0]);
		else
			return NULL;
	}

  public static function get_list_headers()
  {
    $fields = inputclassbase::load_query("SELECT label FROM teacher_details WHERE overview=1 ORDER BY seq_no");
	if(isset($fields))
	{
	  $ix = 0;
	  foreach($fields['label'] AS $label)
	    $labellist[$ix++] = $label;
	  return($labellist);
	}
	else
	  return NULL;
  }

  public function get_list_data()
  {
    $fields = inputclassbase::load_query("SELECT table_name FROM teacher_details WHERE overview=1 ORDER BY seq_no");
	if(isset($fields))
	{
	  $ix = 0;
	  foreach($fields['table_name'] AS $tablename)
	    $datalist[$ix++] = $this->get_teacher_detail($tablename);
	  return($datalist);
	}
	else
	  return NULL;
  
  }

  public function get_teacher_detail($tablename)
  {
    return $this->do_teacher_detail($tablename,false);
  }
  
  public function edit_teacher_detail($tablename)
  {
    return $this->do_teacher_detail($tablename,true);
  }

  private function do_teacher_detail($tablename,$edit)
  {
    global $altsids,$livepictures;
	$dtext = $_SESSION['dtext'];
    if(substr($tablename,0,1) == "*")
	{
	  switch($tablename)
	  {
	    case "*teacher.firstname":
		  $fielddata = new inputclass_textfield("tdfirstname". $this->userid,40,NULL,"firstname","teacher",$this->userid,"tid");
		  break;
		case "*teacher.lastname":
		  $fielddata = new inputclass_textfield("tdlastname". $this->userid,40,NULL,"lastname","teacher",$this->userid,"tid");
		  break;
		case "*tid":
   	      $fielddata = new inputclass_textfield("td*tid". $this->userid,40,NULL,"tid","teacher",$this->userid,"tid");
		  break;
		case "*sgroup.groupname":
		  return $this->show_groupnames();
		  break;
		case "*subject.fullname":
		  return $this->show_classes();
		  break;
	  }
	}
	else
	{
      // get the info about this field
	  $fsdata = inputclassbase::load_query("SELECT * FROM teacher_details WHERE table_name='". $tablename. "'");
	  if(isset($fsdata))
	  {
        switch($fsdata['type'][0])
	    {
	      case "text":
		    if($fsdata['multi'][0] == "Y")
		      $fielddata = new inputclass_multitext("td". $tablename. $this->userid,$fsdata['size'][0],NULL,"data",$tablename,$this->userid,"tid");
			else
		      $fielddata = new inputclass_textfield("td". $tablename. $this->userid,$fsdata['size'][0],NULL,"data",$tablename,$this->userid,"tid");
		    break;
		  case "picture":
	        $params = explode("@",$fsdata['params'][0]);
            $imagedata = inputclassbase::load_query("SELECT data FROM `" . $fsdata['table_name'][0] . "` WHERE tid='$this->userid'");
            if(isset($imagedata['data'][0]))
            {
              $pstring = "<IMG SRC='" . $livepictures . $imagedata['data'][0] . "'". (isset($params[1]) ? " style=\"position:absolute;". $params[1]. "\"" : ""). (isset($params[0]) ? " WIDTH=". $params[0]. "px" : ""). ">";
            }
            else
              $pstring = $dtext['no_pic'];
			if($edit)
			{ // Adding form for image editing
			  $pstring .= "<FORM NAME=tdf". $tablename. "-". $this->userid. " id=tdf". $tablename. "-". $this->userid. " METHOD=POST ACTION=". $_SERVER['REQUEST_URI']. " ENCTYPE='multipart/form-data'>";
			  $pstring .= "<INPUT type=hidden name=edit value=". $this->userid. ">";
			  $pstring .= "<INPUT type=file name=td". $tablename. "-". $this->userid. " onChange='document.forms[\"tdf". $tablename. "-". $this->userid. "\"].submit();'>";
			  $pstring .= "</FORM>";
			}
			return $pstring;
			break;
		  case "choice":
		    $choices = explode(",",$fsdata['params'][0]);
			$choiceq = "SELECT '' AS id, '' AS tekst";
			foreach($choices AS $ch)
			  $choiceq .= " UNION SELECT '". $ch. "','". $ch. "'";
			if($fsdata['multi'][0] == "Y")
			  $fielddata = new inputclass_multiselect("td". $tablename. $this->userid,$choiceq,NULL,"data",$tablename,$this->userid,"tid");
			else
			  $fielddata = new inputclass_listfield("td". $tablename. $this->userid,$choiceq,NULL,"data",$tablename,$this->userid,"userid");
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
  
  public static function list_viewdetails()
  {
    $fsdata = inputclassbase::load_query("SELECT * FROM teacher_details ORDER BY seq_no");
	$I = new teacher();
	$I->load_current();
	if(isset($fsdata))
	{
	  foreach($fsdata['table_name'] AS $dix => $tname)
	  { // Need to add read access check for mentors! ~~~
	    $mentor = inputclassbase::load_query("SELECT gid FROM sgroup WHERE active=1 AND tid_mentor=". $_SESSION['uid']);
		$is_mentor = isset($mentor['gid'][0]);
	    $ac = $fsdata['raccess'][$dix];
	    if($ac == 'A' || ($ac == 'T' && $I->get_id() != 0) || ($ac == 'M' && $I->has_role("counsel")) || ($ac == 'M' && $is_mentor) ||
		   ($ac == 'C' && $I->has_role("counsel")) || $I->has_role("admin") )
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
    $fsdata = inputclassbase::load_query("SELECT * FROM teacher_details ORDER BY seq_no");
		$I = new teacher();
		$I->load_current();
		if(isset($fsdata))
		{
			foreach($fsdata['table_name'] AS $dix => $tname)
			{ 
				if($I->has_role("admin") || $fsdata['waccess'][$dix] == "T" || ($fsdata['waccess'][$dix] == "C" && $I->has_role("counsel")) || ($fsdata['waccess'][$dix] == "O" && $I->has_role("office")))
					$resdata[$tname] = $fsdata['label'][$dix];
			}
		}
		else
			return NULL;
		return ($resdata);
  }

  private function show_groupnames()
  {
    $dtext = $_SESSION['dtext'];
    $mgs = inputclassbase::load_query("SELECT * FROM sgroup WHERE active=1 AND tid_mentor=". $this->userid. " ORDER BY groupname");
	if(isset($mgs['groupname']))
	{
	  $retstr = "";
	  foreach($mgs['groupname'] AS $mg)
	    $retstr .= ", ". $mg;
      return(substr($retstr,2)); 
	}
	else
      return ($dtext['None']);
  }
  
  private function show_classes()
  {
    $dtext = $_SESSION['dtext'];
    // First get the data from the database
    $clquery = "SELECT * FROM sgroup,class INNER JOIN subject USING (mid) WHERE active=1 AND sgroup.gid=class.gid AND tid='$this->userid' ORDER BY fullname,sgroup.gid";
	$classes = inputclassbase::load_query($clquery);
    if(isset($classes['fullname']))
    { // Now we can display the table
      $resstr = "<br><table border=1>";
      // Header row
      $resstr .= "<tr><td><center><b>" . $dtext['Subject'] . "</b></td><td><center><b>" . $dtext['Group_Cap'] . "</b></td></tr>";
      // A row for each subject / group combination
	  foreach($classes['fullname'] AS $cix => $subjname)
      {
        $resstr .= "<tr><td>" . $subjname . "</td><td>" . $classes['groupname'][$cix] . "</td></tr>";
      }
      $resstr .= "</table>";
	  return($resstr);
    }
    else
      return($dtext['No_cls_assigned']);
  }
  
}
?>