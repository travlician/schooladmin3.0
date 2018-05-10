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
  require_once("teacher.php");

  $login_qualify = 'A';
  include ("schooladminfunctions.php");
  // Connect input library to database
  inputclassbase::dbconnect($userlink);
  
  // Check for deletion
  if(isset($_POST['form2delete']))
  {
    if($_POST['form2delete'] != 0)
	  mysql_query("DELETE FROM forms WHERE formid=". $_POST['form2delete'], $userlink);
  }
      
  // The possible roles are predefined, here is the query to get that list (actually no DB access but the library works that way...)
  $rolequery = "SELECT '' AS id, '". $dtext['allow_teach_short']. "' AS tekst UNION SELECT 'mentor','". $dtext['Mentor']. "'
                UNION SELECT 'admin','". $dtext['Administrator']. "' UNION SELECT 'counsel','". $dtext['Counsellor']. "' 
                UNION SELECT 'arman','". $dtext['Abs_admin']. "' UNION SELECT 'office','". $dtext['Office_admin']. "'";
  mysql_query("SELECT * FROM forms", $userlink);
 
 // Get a list of existing forms in a select query
  $formlistquery = "SELECT '' AS id, '' AS tekst";
	// july 31 2017: changed code to ensure sorting in alphbetical order.
/*  if ($handle = opendir('.'))
  {
    while (false !== ($file = readdir($handle)))
	{
	  if(substr($file,0,5) == "form_")
	  {
	    $formname = substr(substr($file,5),0,-4);
		$formlistquery .= " UNION SELECT '". $formname. "','". $formname. "'";
	  }
    }
    closedir($handle);
  }
*/
	$formfilelist = scandir('.');
	foreach($formfilelist AS $formname)
	{
		if(substr($formname,0,5) == "form_")
		{
			$frname = substr(substr($formname,5),0,-4);
			$formlistquery .= " UNION SELECT '". $frname. "','". $frname. "'";
		}		
	}

  
  // First part of the page
  echo("<html><head><title>FORMS</title></head><body background=schooladminbg.jpg link=blue vlink=blue>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+2><center>". $dtext['forms']. "</font><p>");
  echo("<a href=admin.php>" . $dtext['back_admin'] . "</a>");

  // Create the heading row for the table
  echo("<table border=1 cellpadding=0>");
  echo("<tr><th>". $dtext['Description']. "</th><th>". $dtext['Category']. "</th><th>". $dtext['R_acc']. "</th><th>&nbsp</th></tr>");
  $formlist = inputclassbase::load_query("SELECT formid FROM forms ORDER BY category,formname");
  if(isset($formlist['formid']))
  foreach($formlist['formid'] AS $formid)
  {
    $formnamefield = new inputclass_textfield("formnamefield". $formid,40,$userlink,"formname","forms",$formid,"formid","","datahandler.php");
    echo("<tr><td>". $formnamefield->__toString(). "</td><td>");
	$catfield = new inputclass_textfield("catfield". $formid,40,$userlink,"category","forms",$formid,"formid","","datahandler.php");
	$catfield->echo_html();
    echo("</TD><TD>");
	$rolefield = new inputclass_listfield("rolefield". $formid,$rolequery,$userlink,"accessrole","forms",$formid,"formid","","datahandler.php");
	$rolefield->echo_html();
	echo("</td><td><img src=PNG/action_delete.png onClick='if(confirm(\"". $dtext['confirm_delete']. "\")) { deleteform(". $formid. "); }'></td></tr>");
  }
  // Add an new entry 
  $formnamefield = new inputclass_listfield("formnamefield0",$formlistquery,$userlink,"formname","forms",0,"formid","","datahandler.php");
  echo("<tr><td>");
  $formnamefield->echo_html();
  echo("</td><td>");
  $catfield = new inputclass_textfield("catfield0",40,$userlink,"category","forms",0,"formid","","datahandler.php");
  $catfield->echo_html();
  echo("</TD><TD>");
  $rolefield = new inputclass_listfield("rolefield0",$rolequery,$userlink,"accessrole","forms",0,"formid","","datahandler.php");
  $rolefield->echo_html();
  echo("</td><td><img src=PNG/action_check.png onClick='deleteform(0);'></td></tr>");
  
  echo("</table>");
  // Form for deletion
  echo("<FORM id=delform name=delform METHOD=POST action=". $_SERVER['PHP_SELF']. "><INPUT TYPE=HIDDEN NAME=form2delete ID=form2delete VALUE=0></FORM>");
  // funtion for deletion
  echo("<SCRIPT> function deleteform(fid) 
                { 
				  document.getElementById('form2delete').value=fid; 
				  setTimeout(\"document.getElementById('delform').submit();\",1500); 
				} </SCRIPT>");
  // close the page
  echo("</html>");
?>
