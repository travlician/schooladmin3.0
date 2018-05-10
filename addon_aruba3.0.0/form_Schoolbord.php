<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011 Aim4me N.V.   (http://www.aim4me.info)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();
  require_once("inputlib/inputclasses.php");

  //$login_qualify = 'A';
  include ("schooladminfunctions.php");
  require_once("teacher.php");
  inputclassbase::DBconnect($userlink);
  $me = new teacher();
  $me->load_current();
  
  $uid = $_SESSION['uid'];
  $CurrentUID = $uid;
  
  // This function is based on tables that is created as needed. So now we create it if it does not exist.
  $sqlquery = "CREATE TABLE IF NOT EXISTS `schoolboard` (
    `textid` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `boardtext` TEXT DEFAULT NULL,
	PRIMARY KEY (`textid`)
    ) ENGINE=InnoDB;";
  mysql_query($sqlquery,$userlink);
  echo(mysql_error());
  

  // First part of the page
  echo("<html><head><title>Schoolboard instelling</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>Schoolboard instellingen</font><p>");
  echo("<a href=# onClick='window.close();'>" . $dtext['back_teach_page'] . "</a><br>");

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
  // Create the heading row / table
  echo("<table border=1 cellpadding=0>");
  // Create the row with the school data
  echo("<tr><td>");
  $sdulfield = new inputclass_ckeditor("sdul","30,5",$userlink,"boardtext","schoolboard",1,"textid","","hdprocpage.php");
  $sdulfield->set_language($langtoset);
  $sdulfield->set_stylefile($livesite. "schoolboard.css");
  $sdulfield->echo_html();
  echo("</td><td><img src=schoollogo.png width=100 height=60></td><td>");
  $sdurfield = new inputclass_ckeditor("sdur","30,5",$userlink,"boardtext","schoolboard",2,"textid","","hdprocpage.php");
  $sdurfield->set_language($langtoset);
  $sdurfield->set_stylefile($livesite. "schoolboard.css");
  $sdurfield->echo_html();
  echo("</td></tr></table>");
  // Create the content row / table
  echo("<table border=1 cellpadding=0>");
  // Create the row with the school data content
  echo("<tr><td>");
  $sdllfield = new inputclass_ckeditor("sdll","50,20",$userlink,"boardtext","schoolboard",3,"textid","","hdprocpage.php");
  $sdllfield->set_language($langtoset);
  $sdllfield->set_stylefile($livesite. "schoolboard.css");
  $sdllfield->echo_html();
  echo("</td><td>");
  $sdlrfield = new inputclass_ckeditor("sdlr","50,20",$userlink,"boardtext","schoolboard",4,"textid","","hdprocpage.php");
  $sdlrfield->set_language($langtoset);
  $sdlrfield->set_stylefile($livesite. "schoolboard.css");
  $sdlrfield->echo_html();
  echo("</td></tr></table>");
  
  // Show images available
  echo("<H2>Beschikbare beeldbestanden:</H2>");
  echo("<table border=1><TR><TH>URL</TH><TH>Voorbeeld</TH></TR>");
  // Get the available files
  if ($handle = opendir($picturespath))
  {
    while (false !== ($file = readdir($handle)))
	{
	  if(substr($file,0,2) != "s_" && substr($file,0,2) != "t_" && substr($file,0,1) != ".")
	  { // Excludes student and teacher images
	    echo("<TR><TD>". $livepictures. $file. "</TD><TD><IMG SRC='". $livepictures.$file. "' WIDTH=100></TD></TR>");
	  }
    }
    closedir($handle);
  }
  
  echo("</table>");
  // close the page
  echo("</html>");
?>
