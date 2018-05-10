<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2013-2014 Aim4me N.V.   (http://www.aim4me.info)       |
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
require_once("student.php");
require_once("groupselector.php");
if(isset($_GET['DownloadFile']))
{
  //echo("Should download file ". $_GET['DownloadFile']);
  session_start();
  require_once("schooladminfunctions.php");
  if($_SESSION['LoginType'] == "S")
    $me = new student($_SESSION['uid']);
  else
    $currentuser = new teacher($_SESSION['CurrentUID']);
  inputclassbase::dbconnect($userlink);
  $dlfile = new LibraryFile($_GET['DownloadFile']);
  $dlfile->Download();
  exit;
}

class Library extends extendableelement
{
  private $formlist;
  protected function add_contents()
  {
    global $userlink, $currentuser;
    if(!isset($_SESSION['CurrentLibraryPath']))
	  $_SESSION['CurrentLibraryPath'] = "";
	if(isset($_POST['SelectFolder']))
	{
	  $_SESSION['CurrentLibraryPath'] = $_POST['SelectFolder'];
	}
	if(isset($_POST['CreateFolderName']))
	{
	  mysql_query("INSERT INTO libraryfiles (tid,folder) VALUES(". $currentuser->get_id(). ",\"". $_SESSION['CurrentLibraryPath']. "/". $_POST['CreateFolderName']. "\")", $userlink);
	}
	if(isset($_POST['DeleteFile']))
	{
	  mysql_query("DELETE FROM libraryfiles WHERE libid=". $_POST['DeleteFile'], $userlink);
	}
	if(isset($_FILES['UploadedLibraryFile']))
	  LibraryFile::FileUploaded($_FILES['UploadedLibraryFile']);
  }
  
  public function show_contents()
  {
    $dtext = $_SESSION['dtext'];
    echo("<font size=+2><center>" . $dtext['Library'] . "</font></center><p>");
	if(isset($_POST['SetAccess']))
	{
	  $afile = new LibraryFile($_POST['SetAccess']);
	  $afile->AccessDialog();
	}
	else
	{ // Show tree with files and allow new file and folder
	  LibraryFolder::ListFolders();
	  LibraryFile::ShowUploadError();
	  LibraryFile::UploadDialog();
	  LibraryFolder::CreateDialog();
	}
  }
  
}

class LibraryFolder
{
  static function ListFolders($startfolder = NULL)
  {
    global $currentuser;
    if(!isset($startfolder))
	  $startfolder = $_SESSION['CurrentLibraryPath'];
	// Create a hidden form and javascript to enable selection of a folder
	echo("<FORM ID=selfolderform NAME=selfolderform METHOD=POST ACTION='". $_SERVER['REQUEST_URI']. "'><INPUT TYPE=HIDDEN NAME='SelectFolder' VALUE='' ID='SelectFolder'></FORM>");
	echo("<SCRIPT> function SelectFolderF(fname) { document.getElementById('SelectFolder').value = fname; document.getElementById('selfolderform').submit(); } </SCRIPT>");
	// Create a hidden form and javascript to edit access
	echo("<FORM ID=setaccessform NAME=setaccessform METHOD=POST ACTION='". $_SERVER['REQUEST_URI']. "'><INPUT TYPE=HIDDEN NAME='SetAccess' VALUE='' ID='SetAccess'></FORM>");
	echo("<SCRIPT> function SetAccessF(fileid) { document.getElementById('SetAccess').value = fileid; document.getElementById('setaccessform').submit(); } </SCRIPT>");
	// Create a hidden form and javascript to delete a file
	echo("<FORM ID=deletefileform NAME=deletefileform METHOD=POST ACTION='". $_SERVER['REQUEST_URI']. "'><INPUT TYPE=HIDDEN NAME='DeleteFile' VALUE='' ID='DeleteFile'></FORM>");
	echo("<SCRIPT> function DeleteFileF(fileid) { if(confirm(\"". $_SESSION['dtext']['confirm_delete']. "\")) { document.getElementById('DeleteFile').value = fileid; document.getElementById('deletefileform').submit(); } } </SCRIPT>");
	// Show the root folder
	echo("<BR><a onClick='SelectFolderF(\"\");'><img src='PNG/". ($startfolder == "" ? "folder_open.png" : "folder.png"). "'></a>");
	if($startfolder == "")
	  LibraryFile::ListFiles();
	$activetree = explode("/",$startfolder);
	$activelevel = count($activetree);
	$foldersqr = inputclassbase::load_query("SELECT folder,libid,tid FROM libraryfiles GROUP BY folder ORDER BY LOWER(folder)");
	if(isset($foldersqr['folder']))
	{
	  foreach($foldersqr['folder'] AS $fix => $folname)
	  {
	   if($folname != "") // Skip the root folder as it's already there.
	   {
	    $thistree = explode("/",$folname);
		$level = count($thistree);
		// Conditions to show the folder: (1) is part of the activetree (2) starts with the activetree and is 1 lever deeper
		$doshow = true;
		if($level < $activelevel)
		{
		  foreach($thistree AS $lix => $lname)
		    if($lname != $activetree[$lix])
			  $doshow = false;
		}
		else if($level == $activelevel)
		{
		  if($folname != $startfolder)
		    $doshow = false;
		}
		else
		{
		  if($level > ($activelevel + 1))
		    $doshow = false;
		  else
		  {
		    foreach($activetree AS $lix => $lname)
			  if($lname != $thistree[$lix])
			    $doshow = false;
		  }
		}
		// Now $doshow indicates whether we need to show it or not
		if($doshow)
		{
		  echo("<BR>");
		  for($i=1; $i < $level; $i++)
		    echo("&nbsp;&nbsp;");
		  echo("<a onClick='SelectFolderF(\"". $folname. "\");'><img src='PNG/". ($startfolder == $folname ? "folder_open.png" : "folder.png"). "'> ". $thistree[$level - 1]. "</a>");
		  // See if we can delete this folder
		  $icountqr = inputclassbase::load_query("SELECT count(libid) AS scount FROM libraryfiles WHERE (LOCATE('". $folname. "/',BINARY folder) = 1 OR folder = '". $folname. "') AND ((filename IS NULL AND folder <> '". $folname. "') OR filename IS NOT NULL)");
		  if(isset($currentuser) && ($currentuser->has_role('admin') || $currentuser->get_id() == $foldersqr['tid'][$fix]) && (!isset($icountqr['scount']) || $icountqr['scount'][0] == 0))
		    echo(" <img src='PNG/action_delete.png' onClick='DeleteFileF(". $foldersqr['libid'][$fix]. ");'>");
		  else if(isset($icountqr['scount']))
		    echo(" (". $icountqr['scount'][0]. ")");
		  if($startfolder == $folname)
		    LibraryFile::ListFiles();
		}
	   } // End if folname is empty (root folder)
	  }
	}
  }
  static function CreateDialog($startfolder = NULL)
  {
    global $currentuser; // tid of current teacher (if accessed through teacherpage)
    if(!isset($startfolder))
	  $startfolder = $_SESSION['CurrentLibraryPath'];
	if(!isset($currentuser))
	  return; // We don't allow non teachers to create folders.
	echo("<FORM METHOD=POST ACTION='". $_SERVER['REQUEST_URI']. "'><INPUT TYPE=TEXT NAME=CreateFolderName ID=CreateFolderName><INPUT TYPE=SUBMIT VALUE='". $_SESSION['dtext']['Create_Folder']. "'></FORM>");
	// Now a part of javascript to avoid illegal characters in folder name
	echo("<SCRIPT> 
			document.getElementById('CreateFolderName').onkeyup = function(event)
			{
			  this.value = this.value.replace(/[^a-zA-Z\d _.\(\)-]/, '');
			} </SCRIPT>");
  }
}

class LibraryFile
{
  protected $libid;
  static $uploaderror=0;
  
  public function __construct($libid)
  {
	  $this->libid = $libid;
  }
  
  public function ShowInList()
  {
    global $currentuser;
	if(!$this->checkAccess())
	  return;
	//$filename = $this->getName();
	$level = count(explode("/",$this->getFolder()));
	echo("<BR>");
	for($i=0;$i < $level; $i++)
	  echo("&nbsp;&nbsp;");
    echo("<a href='Library.php?DownloadFile=". $this->libid. "' target='download'>". $this->getName(). "</a>");
	echo(" (". $this->getOwnerCode(). " ". $this->getLastModified(). ")");
	// If user is owner or administrator, show delete and modify access icons with function_exists
	if(isset($currentuser) && ($this->getOwner() == $currentuser->get_id() || $currentuser->has_role('admin')))
	{
	  echo(" <img src='PNG/action_delete.png' onCLick='DeleteFileF(". $this->libid. ");'>");
	  echo(" <img src='PNG/unlock.png' onCLick='SetAccessF(". $this->libid. ");'>");
	}	  
  }
  
  public function Download()
  {
	//echo($this->getName());
	if(!$this->checkAccess())
	  return;
    header("Content-disposition: download; filename=\"". $this->getName(). "\";"); //disposition of download forces a download
    header("Content-type: ". $this->getType());
	$filedataqr = inputclassbase::load_query("SELECT data FROM libraryfiles WHERE libid=". $this->libid);
	if(isset($filedataqr['data'][0]))
      echo $filedataqr['data'][0]; 
  }
  
  public function AccessDialog()
  {
    global $currentuser;
	if(!isset($currentuser))
	  return; // Only teacher can set access
    echo("<BR>". $_SESSION['dtext']['R_acc']. " ". $_SESSION['dtext']['for']. " ". $this->getFolder(). "/". $this->getName());
	// Show the list of (categorized) groups for selection of access
	$grpselqry = "SELECT gid AS id, groupname AS tekst, SUBSTRING(groupname,1,1) AS cat FROM sgroup ORDER BY groupname";
	$grpfld = new inputclass_catmultiselect("libgrpaccess",$grpselqry,NULL,"sgid","libraryaccess",$this->libid,"libid",NULL,"datahandler.php");
	$grpfld->set_extrafield("idtype","G");
	echo("<DIV style='float: left;'>");
    $grpfld->echo_html();
	echo("</DIV>");
	// Student selection (in current group)
	$stuselqry = "SELECT sid AS id, CONCAT(lastname, ', ',firstname) AS tekst FROM student LEFT JOIN sgrouplink USING(sid) LEFT JOIN sgroup USING(gid) WHERE groupname='". $_SESSION['CurrentGroup']. "' ORDER BY lastname,firstname";
	$stufld = new inputclass_multiselect("libstuaccess",$stuselqry,NULL,"sgid","libraryaccess",$this->libid,"libid",NULL,"datahandler.php");
	$stufld->set_extrafield("idtype","S");
	echo("<DIV style='float: left;'>");
    $stufld->echo_html();
	echo("</DIV>");	
  }
  
  public function getName()
  {
	  $this->filenamefld = new inputclass_textfield("lib_fname". $this->libid,40,NULL,"filename","libraryfiles",$this->libid,"libid");
	  return($this->filenamefld->__toString());
  }
  
  public function getFolder()
  {
	$this->folderfld = new inputclass_textfield("lib_folder". $this->libid,40,NULL,"folder","libraryfiles",$this->libid,"libid");
	return($this->folderfld->__toString());
  }

  public function getType()
  {
	  $this->filetypefld = new inputclass_textfield("lib_ftype". $this->libid,40,NULL,"type","libraryfiles",$this->libid,"libid");
	  return($this->filetypefld->__toString());
  }
  
  public function getOwner()
  {
    $ownqr = inputclassbase::load_query("SELECT tid FROM libraryfiles WHERE libid=". $this->libid);
	if(isset($ownqr['tid']))
	  return($ownqr['tid'][0]);
	else
	  return 0;
  }
  
  public function getOwnerCode()
  {
    global $teachercode;
	if(isset($teachercode))
	  $ownqr = inputclassbase::load_query("SELECT `". $teachercode. "`.data AS tcode FROM libraryfiles LEFT JOIN `". $teachercode. "` USING(tid) WHERE libid=". $this->libid);
	else
	  $ownqr = inputclassbase::load_query("SELECT tid AS tcode FROM libraryfiles WHERE libid=". $this->libid);
	if(isset($ownqr['tcode']))
	  return($ownqr['tcode'][0]);
	else
	  return "";	
  }
  public function getLastModified()
  {
	$timeqr = inputclassbase::load_query("SELECT lastmodified FROM libraryfiles WHERE libid=". $this->libid);
	if(isset($timeqr['lastmodified']))
	  return($timeqr['lastmodified'][0]);
	else
	  return "";	
  }
  
  public function checkAccess()
  {
    global $currentuser, $me;
	if(isset($currentuser))
      return(true);
	$aclqry = "SELECT libid FROM libraryaccess LEFT JOIN sgrouplink ON(gid=sgid) LEFT JOIN student USING(sid) WHERE idtype='G' AND sid=". $me->get_id(). " AND libid=". $this->libid;
	$aclqry .= " UNION SELECT libid FROM libraryaccess WHERE idtype='S' AND sgid=". $me->get_id(). " AND libid=". $this->libid;
	$aclqr = inputclassbase::load_query($aclqry);
	return isset($aclqr['libid']);
  }

  static function UploadDialog()
  {
    global $currentuser;
	if(isset($currentuser))
      echo("<FORM NAME=UploadLibraryFile id=UploadLibraryFile METHOD=POST ACTION='". $_SERVER['REQUEST_URI']. "' ENCTYPE='multipart/form-data'><INPUT type=file name=UploadedLibraryFile onChange='document.forms[\"UploadLibraryFile\"].submit();'></FORM>");
  }
  static function FileUploaded($filedata)
  {
    global $userlink, $currentuser;
	if(!isset($currentuser))
	  return; // Make sure only teachers upload files
	if($filedata['error'] != 0)
	{
	  self::$uploaderror = $filedata['error'];
	  return;
	}
	// We don't just overwrite files, only if it's the owner or administrator
	$fexistqr = inputclassbase::load_query("SELECT tid FROM libraryfiles WHERE folder=\"". $_SESSION['CurrentLibraryPath']. "\" AND filename=\"". $filedata['name']. "\"");
	if(isset($fexistqr['tid']) && $currentuser->get_id() != $fexistqr['tid'][0] && !$currentuser->has_role('admin'))
	{ // File exits and user may not overwrite
	  self::$uploaderror = 1001;
	  return;
	} 
	mysql_query("REPLACE INTO libraryfiles (tid,folder,filename,type,data) VALUES(". $currentuser->get_id(). ",\"". $_SESSION['CurrentLibraryPath']. "\",\"". $filedata['name']. "\",\"". mysql_real_escape_string($filedata['type']). "\",\"". mysql_real_escape_string(file_get_contents($filedata['tmp_name']),$userlink). "\")", $userlink);
	if(mysql_error($userlink))
	  self::$uploaderror = mysql_error($userlink);
  }
  static function ShowUploadError()
  {
    if(self::$uploaderror != 0)
	{
	  echo("<BR><SPAN STYLE='color: RED; font-weight: bold;'>". $_SESSION['dtext']['Lib_error_upload']);
	  if(self::$uploaderror == UPLOAD_ERR_INI_SIZE || self::$uploaderror == UPLOAD_ERR_FORM_SIZE)
	    echo(": ". $_SESSION['dtext']['Lib_error_oversize']);
	  else if(self::$uploaderror == UPLOAD_ERR_PARTIAL)
	    echo(": ". $_SESSION['dtext']['Lib_error_incomplete']);
      else if(self::$uploaderror == 1001)
	    echo(": ". $_SESSION['dtext']['Lib_error_exists']);
	  echo(" (". self::$uploaderror. ")</span>");
	}
  }
  static function ListFiles($folder = NULL)
  {
    global $currentuser;
    if(!isset($folder))
	  $folder = $_SESSION['CurrentLibraryPath'];
	$flistqr = inputclassbase::load_query("SELECT libid FROM libraryfiles WHERE folder=\"". $folder. "\" AND filename IS NOT NULL ORDER BY LOWER(filename)");
	if(isset($flistqr['libid']))
	{
	  foreach($flistqr['libid'] AS $alibid)
	  {
	    $afile = new LibraryFile($alibid);
		$afile->ShowInList();
	  }
	}
  }
}
?>
