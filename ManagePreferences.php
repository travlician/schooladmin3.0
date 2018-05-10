<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014 Aim4me N.V.   (http://www.aim4me.info)       |
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
require_once("inputlib/inputclasses.php");

class ManagePreferences extends displayelement
{
	protected $absencepreffld;
  protected function add_contents()
  {
    global $userlink,$currentuser;
		if(isset($_POST['prefkey']))
		{
			if($_POST['prefval'] == "")
				mysql_query("DELETE FROM teacherpreferences WHERE tid=". $currentuser->get_id(). " AND aspect='". $_POST['prefkey']. "'",$userlink);
			else
			{
				if($_POST['prefkey'] == "background" && substr($_POST['prefval'],0,1) != "#")
					$_POST['prefkey'] = "backgroundimage";
				else if($_POST['prefkey'] == "background" && substr($_POST['prefval'],0,1) == "#")
					mysql_query("DELETE FROM teacherpreferences WHERE tid=". $currentuser->get_id(). " AND aspect='backgroundimage'",$userlink);
				// Now we are ready to set the preference in the database
				mysql_query("REPLACE INTO teacherpreferences (tid,aspect,avalue) VALUES(". $currentuser->get_id(). ",'". $_POST['prefkey']. "','". $_POST['prefval']. "')", $userlink);
			}
		}		
		$absopts = "SELECT 'AbsenceScreenAutomatic' AS id, '". $_SESSION['dtext']['AbsenceScreenAutomatic']. "' AS tekst UNION SELECT 'AbsenceScreenTouch', '". $_SESSION['dtext']['AbsenceScreenTouch']. "' UNION SELECT 'AbsenceScreenTraditional', '". $_SESSION['dtext']['AbsenceScreenTraditional']. "'";
		$this->absencepreffld = new inputclass_listfield("abspref",$absopts,NULL,"avalue","teacherpreferences",400,"aspect",NULL,"datahandler.php");
		$this->absencepreffld->set_extrakey("tid",$currentuser->get_id());
		
		$searchopts = "SELECT '0' AS id, '". $_SESSION['dtext']['No']. "' AS tekst UNION SELECT 1, '". $_SESSION['dtext']['Yes']. "'";
		$this->searchpreffld = new inputclass_listfield("searchpref",$searchopts,NULL,"avalue","teacherpreferences",401,"aspect",NULL,"datahandler.php");
		$this->searchpreffld->set_extrakey("tid",$currentuser->get_id());
		
		if(isset($_SESSION['dtext']['PrefResultsEntry']))
		{
			$resentryopts = "SELECT 'WithDetails' AS id, '". $_SESSION['dtext']['WithDetails']. "' AS tekst UNION SELECT 'WithoutDetails', '". $_SESSION['dtext']['WithoutDetails']. "'";
			$this->resentrypreffld = new inputclass_listfield("resentrypref",$resentryopts,NULL,"avalue","teacherpreferences",407,"aspect",NULL,"datahandler.php");
			$this->resentrypreffld->set_extrakey("tid",$currentuser->get_id());
		}
	}
  
  public function show_contents()
  {
    global $userlink, $carecodecolors,$currentuser,$AllowPrefs,$AllowMultiPage;
    $dtext = $_SESSION['dtext'];
    echo("<font size=+2><center>" . $dtext['Preferences'] . "</font></center><p>");



		//$bgcolors = array("#C7FFEA","#ECFFD7","#FAD7FF","#FFD7EC","#FFFAD7","#F0F0F0");
		$bgcolors = array("#F0F8FF", "#FAEBD7", "#F0FFFF", "#F5F5DC", "#FFEBCD", "#FFF8DC", "#FFFAF0", "#DCDCDC", "#F8F8FF", "#F0FFF0", "#FFFFF0", "#E6E6FA", "#FFF0F5", "#FFFACD", "#E0FFFF", "#FAFAD2", "#FFFFE0", "#FAF0E6", "#F5FFFA", "#FFE4E1", "#FDF5E6", "#FFEFD5", "#FFF5EE", "#FFFAFA", "#FFFFFF", "#F5F5F5");
		$libexistqr = inputclassbase::load_query("SHOW TABLES LIKE 'libraryfiles'");
		if(isset($libexistqr))
		  $imgsqr = inputclassbase::load_query("SELECT libid FROM libraryfiles WHERE type LIKE 'image%' ORDER BY folder,filename");
		echo("<P>". $dtext['Background']. ":<BR>");
		$itemcount=1;
		foreach($bgcolors AS $acolor)
		{
			echo("<SPAN style='padding-left: 10px;'><SVG width='40' height='40' onClick=setpref('background','". $acolor. "');><rect x=0 y=0 width=40 height=40 style='fill:". $acolor. ";stroke:black;strike-width:5;opacity:1.0;' /></SVG></span>");
		}
		if(isset($imgsqr['libid']))
		{
			foreach($imgsqr['libid'] AS $libid)
			{
				echo("<SPAN style='padding-left: 10px;'><IMG SRC='Library.php?DownloadFile=". $libid. "' width='40' height='40' onClick=setpref('background','". $libid. "');></span>");				
			}
		}
		echo("</p>");
		echo("<P>". $_SESSION['dtext']['AbsenceScreenPreference']. " : ");
		$this->absencepreffld->echo_html();
		echo("</p>");

		echo("<P>". $_SESSION['dtext']['ShowSearch']. " : ");
		$this->searchpreffld->echo_html();
		echo("</p>");
		
		if(isset($_SESSION['dtext']['PrefResultsEntry']))
		{
			echo("<P>". $_SESSION['dtext']['PrefResultsEntry']. " : ");
			$this->resentrypreffld->echo_html();
			echo("</p>");			
		}
		
		echo("<P>". $dtext['StartPage']. (isset($AllowMultiPage) && $AllowMultiPage ? " 1" : ""). ":<BR><IMG style='padding-left: 10px' WIDTH=48 HEIGHT=48 SRC='PNG/action_delete.png' onClick=setpref('startpage','');>");
		if(isset($dtext['tpage_classbook']))
			$sps['tpage_classbook'] = "ClassBook";
		if(!$currentuser->has_role("office"))
		{
			$sps['tpage_manreps']="Reports";
			$sps['tpage_grades']="Grades";
			$sps['tpage_tests']="Tests";
			$sps['tschd_title']="Testschedule";
		}
		if(isset($_SESSION['dtext']['tpage_calendar']))
			$sps['tpage_calendar']="Calendar";
		$sps['tpage_studets']="Studentdetails";
		$sps['tpage_teachdets']="Teacherdetails";
		if($currentuser->has_role("admin") || $currentuser->has_role("counsel") || $currentuser->has_role("mentor"))
			$sps['tpage_stupw']="Passwords";
		if($currentuser->has_role("admin") || $currentuser->has_role("arman"))
			$sps['tpage_absman']="Absenceman";
		if ($handle = opendir('.'))
		{
			$hasforms = 0;
			while (false !== ($file = readdir($handle)))
			{
				if(substr($file,0,5) == "form_")
				 $hasforms = 1;
			}
			if($hasforms > 0)
				$sps['forms']="Formsview";
			closedir($handle);
		}
		
		if($currentuser->has_role("admin") || ($currentuser->has_role("counsel") && isset($carecodetable)))
		{
			$sps['tpage_syspars']="Adminfuncs";
		}
		$sps['tpage_notes']="Notes";
		if(isset($_SESSION['dtext']['tpage_studplacement']))
			$sps['tpage_studplacement']="StudentPlacement";
		if(isset($_SESSION['dtext']['Library']))
			$sps['Library']="Library";

		foreach($sps AS $txtix => $pgname)
		  echo("<SPAN style='padding-left: 10px;' onClick=setpref('startpage','". $pgname. "');>". $dtext[$txtix]. "</span>");		
		echo("</p>");
		
		if(isset($AllowMultiPage) && $AllowMultiPage)
		{
			for($p = 2; $p < 5; $p++)
			{
				echo("<P>". $dtext['StartPage']. " ". $p. ":<BR><IMG style='padding-left: 10px' WIDTH=48 HEIGHT=48 SRC='PNG/action_delete.png' onClick=setpref('startpage". $p. "','');>");
				foreach($sps AS $txtix => $pgname)
					echo("<SPAN style='padding-left: 10px;' onClick=setpref('startpage". $p. "','". $pgname. "');>". $dtext[$txtix]. "</span>");		
				echo("</p>");				
			}
		}
		
		// Form and script to set the preferences
		echo("<FORM ID=prefform METHOD=POST><INPUT TYPE=HIDDEN NAME=prefkey ID=prefkey VALUE=''><INPUT TYPE=HIDDEN NAME=prefval ID=prefval VALUE=''>");
		echo("<SCRIPT> function setpref(prefkey,prefval) { document.getElementById('prefkey').value=prefkey; document.getElementById('prefval').value=prefval; document.getElementById('prefform').submit();  } </SCRIPT>");
  } 
}
?>
