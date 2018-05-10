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
require_once("teacher.php");
require_once("student.php");
require_once("group.php");
require_once("absence.php");

class absencecategory
{
  protected $acid;
  protected $descriptionfld,$imagefld,$classusefld;

  public function __construct($acid = NULL)
  {
	if(isset($acid))
	  $this->acid = $acid;
  }
  
  public function get_id()
  {
    return $this->acid;
  }
  
  public function show_image($showClass = NULL)
  {
    if(!isset($this->acid))
	  return;
    $image = inputclassbase::load_query("SELECT image,name FROM absencecats WHERE acid=". $this->acid);
	if(isset($image['image'][0]))
	{
	  if(isset($showClass) && substr($showClass,0,16) == "studentStateIcon")
	  {
	    echo("<IMG SRC='PNG/". $image['image'][0]. "' BORDER=0". 
	      (isset($showClass) ? " class=". $showClass : ""). ">");
	  }
	  else
	  {
	    echo("<IMG SRC='PNG/". $image['image'][0]. "' TITLE='". $image['name'][0]. "' BORDER=0". 
	      (isset($showClass) ? " class=". $showClass : ""). ">");
	  }
	}
  }
  
  public function get_classuse()
  {
    $this->get_classusefld();
	return($this->classusefld->__toString());
  }
  
  private function get_classusefld()
  {
    if(!isset($this->classusefld))
	  $this->classusefld = new inputclass_checkbox("acidcu". $this->acid,0,NULL,"classuse","absencecats",$this->acid,"acid",NULL,"datahandler.php");
  }
  
  public static function list_categories($guionly = false)
  { // Categories depend on user level
    $I = new teacher();
		$I->load_current();
			$clqry = "SELECT acid FROM absencecats WHERE (waccess='A' OR waccess='T'";
		if($I->has_role("mentor") || $I->has_role("admin"))
			$clqry .= " OR waccess='M'";
		if($I->has_role("counsel") || $I->has_role("admin"))
			$clqry .= " OR waccess='C'";
		if($I->has_role("office") || $I->has_role("admin"))
			$clqry .= " OR waccess='O'";
		if($I->has_role("mentor") || $I->has_role("office") || $I->has_role("admin"))
			$clqry .= " OR waccess='P'";
		if($I->has_role("admin"))
			$clqry .= " OR waccess='N'";
		$clqry .= ")";
		if($guionly)
			$clqry .= " AND ongui=1";
		$clqry .= " ORDER BY acid";
		if($guionly)
			$clqry .= " LIMIT 6";
		$clist = inputclassbase::load_query($clqry);
		if(isset($clist['acid']))
		{
			foreach($clist['acid'] AS $acid)
				$catlist[$acid] = new absencecategory($acid);
			return $catlist;
		}
		else
			return NULL;
  }
  
  public function get_name()
  {
    if($this->get_id() > 0)
		{
			$catnameqr = inputclassbase::load_query("SELECT name FROM absencecats WHERE acid=". $this->get_id());
			return($catnameqr['name'][0]);
		}
		else
			return null;
  }
  public function get_letter()
  {
		$name=$this->get_name();
		if(isset($name))
			return(substr($name,0,1));
		else
			return null;
  }
  
}
?>