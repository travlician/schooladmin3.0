<?
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2016-2016 Aim4me N.V.  (http://www.aim4me.info)        |
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
//require_once("student.php");
//require_once("group.php");

class reportcategory
{
  protected $rcid;
  protected $descriptionfld;

  public function __construct($rcid = NULL)
  {
	if(isset($rcid))
	  $this->rcid = $rcid;
  }
  
  public function get_id()
  {
    return $this->rcid;
  }
  
  public static function list_categories($showall=false)
  { // Categories depend on user level if showall is not set
		if($showall)
			$clqry = "SELECT rcid FROM reportcats ORDER BY rcid";
		else
		{
			$I = new teacher();
			$I->load_current();
			$clqry = "SELECT rcid FROM reportcats WHERE waccess='A' OR waccess='T'";
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
			$clqry .= " ORDER BY rcid";
		}
		$clist = inputclassbase::load_query($clqry);
		if(isset($clist['rcid']))
		{
			foreach($clist['rcid'] AS $rcid)
				$catlist[$rcid] = new reportcategory($rcid);
			return $catlist;
		}
		else
			return NULL;
  }
	
	public static function get_catqry($showall = false)
	{
		$catlst = self::list_categories($showall);
		$retstr = "SELECT '' AS id, '' AS tekst";
		if(isset($catlst))
			foreach($catlst AS $catobj)
			{
				$retstr .= " UNION SELECT ". $catobj->get_id(). ",'". $catobj->get_name(). "'";
			}
		return($retstr);
	}
  
  public function get_name()
  {
    if($this->get_id() > 0)
		{
			$catnameqr = inputclassbase::load_query("SELECT name FROM reportcats WHERE rcid=". $this->get_id());
			return($catnameqr['name'][0]);
		}
		else
			return null;
  } 
}
?>