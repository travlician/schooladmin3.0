<?php
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

  session_start();
  $login_qualify = 'ACT';
  require_once("schooladminfunctions.php");

  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  if(isset($_SESSION['CurrentGroup']))
    $CurrentGroup = $_SESSION['CurrentGroup'];
  else
    $CurrentGroup = "";
  if(isset($HTTP_POST_VARS['NewGroup']) && SA_verifyGroupAccess($uid,$HTTP_POST_VARS['NewGroup']))
    $CurrentGroup = $HTTP_POST_VARS['NewGroup'];
  if(isset($HTTP_GET_VARS['NewGroup']) && SA_verifyGroupAccess($uid,$HTTP_GET_VARS['NewGroup']))
    $CurrentGroup = $HTTP_GET_VARS['NewGroup'];
  $uid = intval($uid);

  SA_closeDB();

  // Save the current group for later!
  $_SESSION['CurrentGroup']=$CurrentGroup;
  
  echo("<body background=schooladminbg.jpg>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2>");
  echo("<font size=+2><center>" . $dtext['forms'] . "</font><p>");
  echo '<a href="teacherpage.php">';
  echo($dtext['back_teach_page'] . "</a><br>");

  echo("<ul>");
  // Show form entry if form_*.php files exist
  if ($handle = opendir('.'))
  {
    while (false !== ($file = readdir($handle)))
	{
	  if(substr($file,0,5) == "form_")
	  {
	    $formname = substr(substr($file,5),0,-4);
        echo("<li><a href=form_". $formname. ".php target=print>" . $formname . "</a></li>");		
	  }
    }
    closedir($handle);
  }
  echo("</ul>");

  echo("</html>");
  exit;


?>



