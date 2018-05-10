<?php
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
require_once("displayelements/displayelement.php");
class Adminfuncs extends displayelement
{
  protected function add_contents()
  {
  }
  
  protected function show_contents()
  {
    global $currentuser, $carecodetable, $AllowPrefs;
    $dtext = $_SESSION['dtext'];
    echo("<BR><BR><BR><BR><font size=+2><table width=\"100%\">
      <tr>");
	if($currentuser->has_role("admin"))
	{
	  echo("<td width=\"25%\" align=\"center\"><b>". $dtext['School_struct']. "</b></td>");
      echo("<td width=\"25%\" align=\"center\"><b>". $dtext['Grade_system']. "</b></td>");
	}
	if($currentuser->has_role("admin") || $currentuser->has_role("counsel"))
	  echo("<td width=\"25%\" align=\"center\"><b>". $dtext['Details']. "</b></td>");
	if($currentuser->has_role("admin"))
      echo("<td width=\"25%\" align=\"center\"><b>". $dtext['DB_Maint']. "</b></td>");
	echo("</tr><tr>");
	if($currentuser->has_role("admin"))
	{
	  echo("<td width=\"25%\" align=\"center\"><a href=\"manteacher.php\">". $dtext['Man_teachers']. "</a>
            <BR><BR><a href=\"mansubjects.php\">". $dtext['Man_subjects']. "</a>
            <BR><BR><a href=\"mangroups.php\">". $dtext['Man_groups']. "</a>
            <BR><BR><a href=\"manclasses.php\">". $dtext['Man_class']. "</a>
            <BR><BR><a href=\"manstudents.php\">". $dtext['Man_stud']. "</a>
						<BR><BR><a href=\"manmessages.php\">". $dtext['MessageRightsTitle']. "</a></td>");
      echo("<td width=\"25%\" align=\"center\"><a href=\"manperiods.php\">". $dtext['perman_title']. "</a>
            <BR><BR><a href=\"mantesttyp.php\">". $dtext['Man_testtype']. "</a>
            <BR><BR><a href=\"mangradecalc.php\">". $dtext['Man_gradecalc']. "</a>
            <BR><BR><a href=\"manfinalcalc.php\">". $dtext['Man_finalcalc']. "</a>
            <BR><BR><a href=\"manformulas.php\">". $dtext['Specialformulas']. "</a>
            <BR><BR><a href=\"mancoursecriteria.php\">". $dtext['Man_coursecrit']. "</a></td>");
	}
	if($currentuser->has_role("admin") || $currentuser->has_role("counsel") || (isset($AllowPrefs) && $AllowPrefs))
	{
	  echo("<td width=\"25%\" align=\"center\">");
	  if($currentuser->has_role("admin"))
	    echo("<a href=\"manstuddetails.php\">". $dtext['Man_studdets']. "</a>
              <BR><BR><a href=\"mansubjectpacks.php\">". $dtext['subpack_title']. "</a>
			  <BR><BR><a href=\"manteacherdetails.php\">". $dtext['Man_teachdet']. "</a>
        <BR><BR><a href=\"manabsdetails.php\">". $dtext['Man_absdet']. "</a>
        <BR><BR><a href=\"manrepcats.php\">". $dtext['ReportCats']. "</a>
        <BR><BR><a href=\"manforms.php\">". $dtext['forms']. "</a>
        <BR><BR><a href=\"manadds.php\">". $dtext['Man_adds']. "</a>");
	  if(isset($carecodetable) && ($currentuser->has_role("admin") || $currentuser->has_role("counsel")))
	    echo("<BR><BR><a href=\"teacherpage.php?Page=ManageCareCodes\">". $dtext['Man_carecodes']. "</a>");
	  if($currentuser->has_role("admin") || (isset($AllowPrefs) && $AllowPrefs))
	    echo("<BR><BR><a href=\"teacherpage.php?Page=ManagePreferences\">". $dtext['Preferences']. "</a>");
	  echo("</td>");
	}
	if($currentuser->has_role("admin"))
	  echo("<td width=\"25%\" align=\"center\"><a href=\"archive.php\">". $dtext['Archive']. "</a>
						<BR><BR><a href=\"fullbackup.php\">". $dtext['Backup']. "</a>
            <BR><BR><a href=\"restoredb.php\">". $dtext['Restore']. "</a>
            <BR><BR><a href=\"mantranslations.php\">". $dtext['Translations']. "</a>
            <BR><BR><a href=\"teacherpage.php?Page=ManGUIQueries\">". $dtext['Man_GUIQueries']. "</a>
            <BR><BR><a href=\"manquery.php\">". $dtext['Man_query']. "</a></td>");
	echo("</tr></table>
          <p align=\"center\"><a href=\"teacherpage.php\">". $dtext['back_teach_page']. "</a></p>");
  }
}
?>



