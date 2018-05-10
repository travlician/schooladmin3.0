<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011 Aim4me N.V.   (http://www.aim4me.info)       |
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

class Notes extends displayelement
{
  private $formlist;
  protected function add_contents()
  {
    $sqlquery = "CREATE TABLE IF NOT EXISTS `notes` (
      `tid` INTEGER(11) UNSIGNED NOT NULL,
      `data` TEXT DEFAULT NULL,
	  PRIMARY KEY (`tid`)
      ) ENGINE=InnoDB;";
    mysql_query($sqlquery);
    echo(mysql_error());
  }
  
  public function show_contents()
  {
    global $userlink, $livesite;
    $me = new teacher();
	$me->load_current();
    $dtext = $_SESSION['dtext'];
    echo("<font size=+2><center>" . $dtext['tpage_notes'] . "</font><p>");
	// Get the language to set for the editor
	if(isset($_SESSION['currentlanguage']))
	{
	  switch($_SESSION['currentlanguage'])
	  {
		case 'nederlands' :
		  $langtoset = 'nl';
		  break;
		case 'español' :
		  $langtoset = 'sp';
		  break;
		default:
		  $langtoset = 'en';
	  }
	}
    $notesfield = new inputclass_ckeditor("notes","80,25",$userlink,"data","notes",$_SESSION['uid'],"tid","","hdprocpage.php");
    $notesfield->set_language($langtoset);
    $notesfield->set_stylefile($livesite. "style.css");
    $notesfield->echo_html();
  }
  
}
?>
