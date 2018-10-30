<?
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
  require_once("inputlib/inputclasses.php");
  session_start();
  require_once("schooladminconstants.php");
  require_once("schooladminfunctions.php");
  require_once("displayelements/multielementpage.php");
  require_once("displayelements/extendableelement.php");
  require_once("displayelements/loginelement.php");
  require_once("displayelements/menulistelement.php");
  require_once("displayelements/vanishingmenulistelement.php");
  require_once("displayelements/menuclasselement.php");
  require_once("displayelements/plainelement.php");
  require_once("teacher.php");
  require_once("groupselector.php");
	require_once("helppage.php");
	require_once("message.php");
  
  if(isset($hmenu) && $hmenu > 0)
    echo("<!DOCTYPE html>");
  
  class vanisher extends displayelement
  {
    public function add_contents()
		{
		}
		public function show_contents()
		{
			global $AllowPrefs;
			echo("<SCRIPT>\r\n");
			echo("var menuelem=document.getElementById('menudiv');\r\n");
			echo("var contentelem=document.getElementById('Contents');\r\n");
			echo("function Enter(e) {\r\n");
			echo(" clearTimeout(starttimeout);\r\n");
			echo(" if(slider != null) { clearInterval(slider); slider=null; }\r\n");
			echo(" menuelem.style.marginLeft=-1;\r\n");
			echo("}\r\n");
			echo("function Leave(e) {\r\n");
			echo(" if(slider == null) {\r\n");
			echo("  menuelem.style.marginLeft=0;\r\n");
			echo("  slider=setInterval('slide();',50);\r\n");
			echo(" }\r\n");
			echo("}\r\n");
			echo("function slide() {\r\n");
			echo(" menuelem.style.marginLeft=parseInt(menuelem.style.marginLeft) - 10;\r\n");
			echo(" if(parseInt(menuelem.style.marginLeft) < (8 - menuelem.offsetWidth)) {\r\n");
			echo("  clearInterval(slider);\r\n");
			echo("  menuelem.style.marginLeft=4-menuelem.offsetWidth; }\r\n");
			echo("}\r\n");
			echo("function delayleave(e) { starttimeout=setTimeout(\"Leave(1)\",500); }\r\n");
			echo("var starttimeout=setTimeout(\"Leave(1)\",2000);\r\n");
			echo("var slider=null;\r\n");
			echo("menuelem.onmouseover=Enter;\r\n");
			echo("contentelem.onmousemove=delayleave;\r\n");
			echo("</SCRIPT>");
		}
  }

  // Connect to the database
  require_once("inputlib/inputclassbase.php");
  inputclassbase::dblogon($databaseserver,$datausername,$datapassword,$databasename);
  // If carecode is configured, make sure table is defined
  if(isset($carecodecolors))
  mysql_query("CREATE TABLE IF NOT EXISTS `". $carecodecolors. "` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT, `tekst` text, PRIMARY KEY (`id`), UNIQUE KEY `id` (`id`)) ENGINE=MyISAM", $userlink);
  if(isset($carecodecolors))
  {
    $carecolors = inputclassbase::load_query("SELECT * FROM `". $carecodecolors. "`");
		if(!isset($casecolors) || count($carecolors) < 3)
		{
			mysql_query("INSERT INTO `". $carecodecolors. "` (id,tekst) VALUES(1,'background-color: #88FF88')", $userlink);
			mysql_query("INSERT INTO `". $carecodecolors. "` (id,tekst) VALUES(2,'background-color: #FFCC88')", $userlink);
			mysql_query("INSERT INTO `". $carecodecolors. "` (id,tekst) VALUES(3,'background-color: #FF8888')", $userlink);
		}
  }
  // Get an object for the current user
  $currentuser = new teacher();
  $currentuser->load_current();
  if($_SESSION['LoginType'] == "S" || $_SESSION['Schoolkey'] != $databasename)
    exit();
	if(!isset($_GET['Page']))
	{
		$firstpage=true;
		if($currentuser->get_preference("startpage"))
		{
			if($currentuser->get_preference("startpage2") || $currentuser->get_preference("startpage3") || $currentuser->get_preference("startpage4"))
				$_GET['Page'] = "MultiPage";
			else
				$_GET['Page'] = $currentuser->get_preference("startpage");
		}
	}
	else
		$firstpage=false;
  if(isset($hmenu) && $hmenu > 0)
    $page = new multielementpage("pageh",NULL,"utf-8","layout.css",$announcement);
  else
    $page = new multielementpage("page",NULL,"utf-8","layout.css",$announcement);

  $header = new extendableelement("header",NULL,$announcement);
  $header->add_element(new extendableelement("loginspace",NULL,$currentuser->get_username()));
  $page->add_element($header);
  if(isset($hmenu) && $hmenu > 0)
    $menu = new helpenabledmenulistelement("menudivh",NULL,NULL,"submenuh",NULL,"class=menuitemh","class=activemenuitemh","Page");
  else
    $menu = new helpenabledmenulistelement("menudiv",NULL,NULL,"submenu",NULL,"class=menuitem","class=activemenuitem","Page");
  $menu->add_element(new groupselector(NULL,NULL,$currentuser));
  $menu->add_element(new plainelement(NULL,NULL,"<HR>"));
  if(isset($_SESSION['dtext']['tpage_classbook']))
  {
    $menu->add_item($_SESSION['dtext']['tpage_classbook'],"ClassBook");
    $menu->add_element(new plainelement(NULL,NULL,"<HR>"));
  }
  if(!$currentuser->has_role("office"))
  {
    $menu->add_item($_SESSION['dtext']['tpage_manreps'],"Reports");
    $menu->add_element(new plainelement(NULL,NULL,"<HR>"));
    $menu->add_item($_SESSION['dtext']['tpage_grades'],"Grades");
    $menu->add_element(new plainelement(NULL,NULL,"<HR>"));
    $menu->add_item($_SESSION['dtext']['tpage_tests'],"Tests");
    $menu->add_element(new plainelement(NULL,NULL,"<HR>"));
    $menu->add_item($_SESSION['dtext']['tschd_title'],"Testschedule");
    $menu->add_element(new plainelement(NULL,NULL,"<HR>"));
  }
  if(isset($_SESSION['dtext']['tpage_calendar']))
  {
    $menu->add_item($_SESSION['dtext']['tpage_calendar'],"Calendar");
    $menu->add_element(new plainelement(NULL,NULL,"<HR>"));
  }
  $menu->add_item($_SESSION['dtext']['tpage_studets'],"Studentdetails");
  $menu->add_element(new plainelement(NULL,NULL,"<HR>"));
  if(isset($_SESSION['dtext']['tpage_studentoverviews']))
  {
    $menu->add_item($_SESSION['dtext']['tpage_studentoverviews'],"StudentOverviews");
    $menu->add_element(new plainelement(NULL,NULL,"<HR>"));
  }
  $menu->add_item($_SESSION['dtext']['tpage_teachdets'],"Teacherdetails");
  //if($currentuser->has_role("admin") || $currentuser->has_role("counsel") || $currentuser->has_role("mentor"))
  { 
    $menu->add_element(new plainelement(NULL,NULL,"<HR>"));
    $menu->add_item($_SESSION['dtext']['tpage_stupw'],"Passwords");
  }
  if($currentuser->has_role("admin") || $currentuser->has_role("arman"))
  {
    $menu->add_element(new plainelement(NULL,NULL,"<HR>"));
    $menu->add_item($_SESSION['dtext']['tpage_absman'],"Absenceman");
  }
  if ($handle = opendir('.'))
  {
    $hasforms = 0;
    while (false !== ($file = readdir($handle)))
	{
	  if(substr($file,0,5) == "form_")
	   $hasforms = 1;
    }
	if($hasforms > 0)
	{
      $menu->add_element(new plainelement(NULL,NULL,"<HR>"));
	  $menu->add_item($_SESSION['dtext']['forms'],"Formsview");
	}
    closedir($handle);
  }
	
  if($currentuser->has_role("admin") || ($currentuser->has_role("counsel") && isset($carecodetable)) || (isset($AllowPrefs) && $AllowPrefs))
  {
    $menu->add_element(new plainelement(NULL,NULL,"<HR>"));
    $menu->add_item($_SESSION['dtext']['tpage_syspars'],"Adminfuncs");
  }
  $menu->add_element(new plainelement(NULL,NULL,"<HR>"));
  $menu->add_item($_SESSION['dtext']['tpage_notes'],"Notes");
  if(isset($_SESSION['dtext']['tpage_studgui']))
    $menu->add_item($_SESSION['dtext']['tpage_studgui'],"StudentGUI");
  else if(isset($_SESSION['dtext']['tpage_studplacement']))
    $menu->add_item($_SESSION['dtext']['tpage_studplacement'],"StudentPlacement");
  if(isset($_SESSION['dtext']['Library']))
    $menu->add_item($_SESSION['dtext']['Library'],"Library");
  $menu->add_element(new plainelement(NULL,NULL,"<HR>"));
	if($currentuser->get_preference(401) > 0)
		$menu->add_item($_SESSION['dtext']["Search_title"],"Search");
	$helppage = new helppage();
	$helppage->conditional_add($menu);
  $menu->add_item($_SESSION['dtext']['Logoff'],"CloseSession");
  $page->add_element($menu);
  if(isset($hmenu) && $hmenu > 0)
    $page->add_element(new menuclasselement("Contentsh",NULL,"","Page","emptyscreen","contents"));
  else
    $page->add_element(new menuclasselement("Contents",NULL,"","Page","emptyscreen","contents"));
  if(isset($_GET['Page']) && (!isset($hmenu) || $hmenu==0))
    $page->add_element(new vanisher());
  echo($page->show());
  include("touchfix.js");
	if(isset($_SESSION['pw_expiry_warn']))
	{ // Show a warning is password is about to expire and not shown yet
		echo("<SCRIPT> alert('". $dtext['cpw_expiry_warning1']. $_SESSION['pw_expiry_warn']. "'); </SCRIPT>");
		unset($_SESSION['pw_expiry_warn']);
	}
	if($firstpage)
	{ // See if there are any messages
		$msgs = message::list_messages("t",$_SESSION['uid']);
		if(isset($msgs))
			echo("<IFRAME width=80% height=70% style='margin-top:5%; border: 3px solid green; z-index=3000; position: fixed; top: 10%; left: 10%; background-color: white;' src=showmessages.php>");
	}
  if($firstpage)
  { // Send client time and touchscreen status to server
    echo("
		<INPUT TYPE=HIDDEN NAME=ClientTime ID=ClientTime VALUE=0>
		<INPUT TYPE=HIDDEN NAME=TouchScreen ID=TouchScreen VALUE=0>
		<SCRIPT> var clienttimefld = document.getElementById('ClientTime');
		var curDate = new Date();
		clienttimefld.value= Math.round(curDate.valueOf() / 1000);
		send_xml('ClientTime',clienttimefld);
		var clientTSfld = document.getElementById('TouchScreen');
		var TSon = 'ontouchstart' in document.documentElement;
		if(TSon)
		{
			clientTSfld.value= 1;
			send_xml('TouchScreen',clientTSfld);
		}
		</SCRIPT>");
  }
	// Set preferred background color
	if($currentuser->get_preference("background"))
		echo("<SCRIPT> document.body.style.backgroundColor='". $currentuser->get_preference("background"). "'; </SCRIPT>");
	// Set preferred background image
	if($currentuser->get_preference("backgroundimage"))
		echo("<SCRIPT> document.body.style.backgroundImage=\"url('Library.php?DownloadFile=". $currentuser->get_preference("backgroundimage"). "')\"; document.body.style.backgroundSize='cover'; </SCRIPT>");
	// Adjust height of multi page blocks if present
	echo("<SCRIPT> var multidivheight = (window.innerHeight-110)/2; if(document.getElementById('MPDIV3') || document.getElementById('MPDIV4')) 
		{ document.getElementById('MPDIV1').style.maxHeight=multidivheight+'px'; 
			if(document.getElementById('MPDIV2')) document.getElementById('MPDIV2').style.maxHeight=multidivheight+'px';
			if(document.getElementById('MPDIV3')) document.getElementById('MPDIV3').style.maxHeight=multidivheight+'px';
			if(document.getElementById('MPDIV4')) { document.getElementById('MPDIV4').style.maxHeight=multidivheight+'px'; 
		} } </SCRIPT>");
		

?>

