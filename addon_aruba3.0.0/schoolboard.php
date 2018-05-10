<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011 Aim4me N.V.   (http://www.aim4me.info)	      |
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
  require_once("inputlib/inputclasses.php");

  include ("schooladminfunctions.php");
  require_once("teacher.php");
  inputclassbase::DBconnect($userlink);
  $me = new teacher();
  $me->load_current();  

  // First part of the page
  echo("<html><head><title>Schoolboard</title></head><body link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="schoolboard.css" title="style1">';

  // Create the heading row / table
  echo("<table border=1 cellpadding=0 class=toptable>");
  // Create the row with the school data
  echo("<tr><td class=leftaddress>");
  $sdulfield = new inputclass_textarea("sdul","30,5",$userlink,"boardtext","schoolboard",1,"textid","","hdprocpage.php");
  echo($sdulfield->__toString());
  echo("</td><td class=logo><img src=schoollogo.png class=logoimage></td><td class=rightaddress>");
  $sdurfield = new inputclass_textarea("sdur","30,5",$userlink,"boardtext","schoolboard",2,"textid","","hdprocpage.php");
  echo($sdurfield->__toString());
  echo("</td></tr></table>");
  // Create the content row / table
  echo("<table border=1 cellpadding=0 class=contenttable>");
  // Create the row with the school data content
  echo("<tr><td class=contenttd>");
  $sdllfield = new inputclass_textarea("sdll","50,20",$userlink,"boardtext","schoolboard",3,"textid","","hdprocpage.php");
  echo($sdllfield->__toString());
  echo("</td><td class=contenttd>");
  $sdlrfield = new inputclass_textarea("sdlr","50,20",$userlink,"boardtext","schoolboard",4,"textid","","hdprocpage.php");
  echo($sdlrfield->__toString());
  echo("</td></tr></table>");
 
  // close the page
  echo("</html>");
?>
