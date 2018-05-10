<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2016 Aim4me N.V.   (http://www.aim4me.info)       |
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
require_once("displayelements/extendableelement.php");
require_once("emptyscreen.php");

class MultiPage extends extendableelement
{
  protected $abselem;
  protected function add_contents()
  {
		global $currentuser;
    parent::add_contents();
		if($currentuser->get_preference("startpage3") || $currentuser->get_preference("startpage4"))
			$heightspec = " heigth: 2px; max-height: 2px;";
		else
			$heightspec = "";
		if($currentuser->get_preference("startpage2"))
			$widthspec12 = "width: 50%;";
		else
			$widthspec12 = "width: 100%;";
		if($currentuser->get_preference("startpage3") && $currentuser->get_preference("startpage4"))
			$widthspec34 = "width: 50%;";
		else
			$widthspec34 = "width: 100%;";
		$showClass1 = $currentuser->get_preference("startpage");
		require_once($showClass1. ".php");
		$this->add_element(new $showClass1("MPDIV1",$widthspec12. $heightspec. " display: inline-block; overflow: scroll; vertical-align: top;"));
		if($currentuser->get_preference("startpage2"))
		{
			$showClass2 = $currentuser->get_preference("startpage2");
			require_once($showClass2. ".php");
			$this->add_element(new $showClass2("MPDIV2",$widthspec12. $heightspec. " display: inline-block; overflow: scroll; vertical-align: top;"));
		}
		if($currentuser->get_preference("startpage3"))
		{
			$showClass3 = $currentuser->get_preference("startpage3");
			require_once($showClass3. ".php");
			$this->add_element(new $showClass3("MPDIV3",$widthspec34. $heightspec. " display: inline-block; overflow: scroll; vertical-align: top;"));
		}
		if($currentuser->get_preference("startpage4"))
		{
			$showClass4 = $currentuser->get_preference("startpage4");
			require_once($showClass4. ".php");
			$this->add_element(new $showClass4("MPDIV4",$widthspec34. $heightspec. " display: inline-block; overflow: scroll; vertical-align: top;"));
		}
  }
}
?>
