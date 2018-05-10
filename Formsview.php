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

class Formsview extends displayelement
{
  private $formlist;
  protected function add_contents()
  {
    $sqlquery = "CREATE TABLE IF NOT EXISTS `forms` (
      `formid` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `category` TEXT DEFAULT NULL,
      `formname` TEXT NOT NULL,
      `accessrole` TEXT DEFAULT NULL,
      `lastmodifiedat` TIMESTAMP(9) NOT NULL ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	  PRIMARY KEY (`formid`)
      ) ENGINE=InnoDB;";
    mysql_query($sqlquery);
    echo(mysql_error());
    $this->formlist = inputclassbase::load_query("SELECT * FROM forms ORDER BY category,formname");
	if(!isset($this->formlist['formid']))
	{ // No forms defined, load from existing filenames 
      if ($handle = opendir('.'))
      {
        while (false !== ($file = readdir($handle)))
	    {
	      if(substr($file,0,5) == "form_")
	      {
	        $formname = substr(substr($file,5),0,-4);
			mysql_query("INSERT INTO forms (category,formname,accessrole) VALUES(\"Miscellaneous\",\"". $formname. "\",'')");
	      }
        }
        closedir($handle);
		// Reload the forms list
        $this->formlist = inputclassbase::load_query("SELECT * FROM forms ORDER BY category,formname");
      }
	}
  }
  
  public function show_contents()
  {
    $me = new teacher();
	$me->load_current();
    $dtext = $_SESSION['dtext'];
    echo("<font size=+2><center>" . $dtext['forms'] . "</font><p>");
	if(isset($this->formlist['formid']))
	{
	  foreach($this->formlist['category'] AS $catname)
	    $catlist[$catname] = 1;
	  // Table headings
	  echo("<TABLE><TR>");
	  foreach($catlist AS $catname => $dummy)
	    echo("<TH>". $catname. "</TH>");
	  echo("</TR><TR>");
	  foreach($catlist AS $catname => $dummy)
	  {
	    echo("<TD>");
		foreach($this->formlist['category'] AS $fix => $fcatname)
		{
		  if($catname == $fcatname)
		  {
	        if($this->formlist['accessrole'][$fix] == '' || $me->has_role($this->formlist['accessrole'][$fix]))
              echo("<a href=form_". $this->formlist['formname'][$fix]. ".php target=lvsform>" . $this->formlist['formname'][$fix]. "</a><BR>");
		  }
		}
		echo("</TD>");
	  }
	  echo("</TR></TABLE>");
    }		  
  }
  
}
?>
