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
require_once("displayelements/displayelement.php");
require_once("teacher.php");
require_once("group.php");
require_once("subject.php");
class subjectselector extends displayelement
{
  protected $selfield;
	protected $metas;
  protected $teacher;
  protected $defaultsubject;
  public function __construct($divid = NULL, $style = NULL, $teacher = NULL,$defaultsubject=NULL,$vertical=false)
  {
    if(isset($teacher))
	  $this->teacher = $teacher;
		$this->vertical = $vertical;
		if(isset($defaultsubject))
		{
			$this->defaultsubject = $defaultsubject;
			$_SESSION['CurrentSubject'] = $defaultsubject;
		}
		parent::__construct($divid, $style);
  }
  protected function show_contents()
  {
    global $teachercode;
		if(isset($this->selfield[0]))
		{
			echo("<FORM name=sselect id=sselect METHOD=POST ACTION='". $_SERVER['REQUEST_URI']. "' style='display: inline'>". $_SESSION['dtext']['Subject']. " : ");
			$this->selfield[0]->echo_html();
			if(isset($_POST['tdresults']))
				echo("<INPUT TYPE=hidden NAME=tdresults VALUE=". $_POST['tdresults']. ">");
			echo("</FORM>");
			if($this->vertical)
				echo("<BR>");
		}
		if(isset($this->metas))
		{
			foreach($this->metas AS $mkey => $metasub)
			{
				echo("<FORM name=sselect". $metasub->get_id(). " id=sselect". $metasub->get_id(). " METHOD=POST ACTION='". $_SERVER['REQUEST_URI']. "' style='display: inline'> ". $metasub->get_shortname(). " : ");
				$this->selfield[$mkey]->echo_html();
				if(isset($_POST['tdresults']))
					echo("<INPUT TYPE=hidden NAME=tdresults VALUE=". $_POST['tdresults']. ">");
				echo("</FORM>");
				if($this->vertical)
					echo("<BR>");
			}
		}
		
		// nice, but we need to select the current active one!
		if(isset($this->defaultsubject))
		{
			$cursub = new subject($this->defaultsubject);
			//wecho(" <SCRIPT> alert('Current (default) subject = ". $cursub->get_id(). "'); </SCRIPT>");
		}
		else
		{
			$cursub = new subject();
			$cursub->load_current(); 
		}
		if(isset($this->selfield[0]))
			echo("<SCRIPT> document.sselect.sselectfld.value=". $cursub->get_id(). ";</SCRIPT>");
		if(isset($this->metas))
			foreach($this->metas AS $msub)
				echo("<SCRIPT> document.sselect". $msub->get_id(). ".sselectfld.value=". $cursub->get_id(). ";</SCRIPT>");
		// Display the teacher code if applicable
		if(isset($teachercode))
		{
			$subteach = $cursub->get_teacher();
			if(isset($subteach))
			{
				$tc = $subteach->get_teacher_detail($teachercode);
				if(isset($tc) && $tc != "")
					echo(" (". $tc. ")");
			}
		}
		if(isset($_POST) && count($_POST) > 0)
				foreach($_POST AS $pkey => $pval)
				if($pkey != "student" && $pkey != "delte" && $pkey != "sselectfld")
					echo("<input type=hidden name='". $pkey. "' value='". $pval. "'>");
  }
  protected function add_contents()
  { // setup my field
    if(isset($_POST['sselectfld']))
		{ // A new value was posted
			$dsub = new subject($_POST['sselectfld']);
			$_SESSION['CurrentSubject'] = $dsub->get_id();
		}
		if(!isset($this->teacher))
		{
			$this->teacher = new teacher();
			$this->teacher->load_current();
		}
		$this->metas = subject::subject_metalist();
		$subs = subject::subject_list(NULL,NULL,0);
		if(isset($subs))
		{
			$lqstr = "SELECT '' AS id, '' AS tekst";
			foreach($subs AS $mid => $sobj)
			$lqstr .= " UNION SELECT ". $mid. ", '". $sobj->get_shortname(). "'";
			$this->selfield[0] = new inputclass_listfield("sselectfld",$lqstr,NULL,NULL,NULL,NULL,NULL,"\" onChange=\"document.sselect.submit();","datahandler.php");
		}
		if(isset($this->metas))
			foreach($this->metas AS $metasub)
			{
				$subs = subject::subject_list(NULL,NULL,$metasub);
				if(isset($subs))
				{
					$lqstr = "SELECT '' AS id, '' AS tekst";
					foreach($subs AS $mid => $sobj)
					$lqstr .= " UNION SELECT ". $mid. ", '". $sobj->get_shortname(). "'";
					$this->selfield[$metasub->get_id()] = new inputclass_listfield("sselectfld",$lqstr,NULL,NULL,NULL,NULL,NULL,"\" onChange=\"document.sselect". $metasub->get_id(). ".submit();","datahandler.php");
				}				
			}		
  }
}
?>
