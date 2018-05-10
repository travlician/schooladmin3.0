<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014 Aim4me N.V.   (http://www.aim4me.info)       |
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

class ManageCareCodes extends displayelement
{
  protected function add_contents()
  {
    global $carecodetable, $carecodecolors, $userlink;
    // Find out which table takes care of carecodes
	$cdtableqr = inputclassbase::load_query("SELECT params,label FROM student_details WHERE table_name='". $carecodetable. "'");
	if(isset($cdtableqr['params'][0]) && substr($cdtableqr['params'][0],0,1) == "*")
	{
	  $this->caredefinitions = substr($cdtableqr['params'][0],1);
	}
	if(isset($_POST['delcc']))
      mysql_query("DELETE FROM `". $this->caredefinitions. "` WHERE id=". $_POST['delcc'], $userlink);
	if(isset($_POST['fgcol']))
	{ // color picked, convert to style and set in database
	  $colordata = "color: ". $_POST['fgcol']. "; background-color: #". $_POST['bgcol']. ";";
	  mysql_query("REPLACE INTO `". $carecodecolors. "` (id,tekst) VALUES(". $_POST['ccid']. ",\"". $colordata. "\")", $userlink);
	}
  }
  
  public function show_contents()
  {
    global $userlink, $carecodecolors;
    $dtext = $_SESSION['dtext'];
    echo("<font size=+2><center>" . $dtext['Man_carecodes'] . "</font><p>");
	// Here we build a table with all colours selectable
	$R = array(2,2,2,1,0,0,0,0,0,1,2,1,3);
	$G = array(0,1,2,2,2,2,2,1,0,0,0,0,3);
	$B = array(0,0,0,0,0,1,2,2,2,2,2,1,3);
	$RGB[0] = array("00","00","00","00","00","33","66","99","CC");
	$RGB[1] = array("19","33","4C","66","80","99","B2","CC","E5");
	$RGB[2] = array("33","66","99","CC","FF","FF","FF","FF","FF");
	$RGB[3] = array("00","20","40","60","80","A0","C0","E0","FF");
	echo("<table style='display: none;' ID='popupcolorpicker'>");
	foreach($RGB[0] AS $cix => $dummy)
	{
	  echo("<tr>");
	  foreach($R AS $rix => $dummy2)
	    echo("<TD style='width: 20px; background-color: #". $RGB[$R[$rix]][$cix]. $RGB[$G[$rix]][$cix]. $RGB[$B[$rix]][$cix]. "; color: ". ($cix < 4 ? "white" : "black"). "; text-align: center;' onClick=colorpicked('". ($cix < 4 ? "white" : "black"). "','". $RGB[$R[$rix]][$cix]. $RGB[$G[$rix]][$cix]. $RGB[$B[$rix]][$cix]. "')>A</td>");
      echo("</tr>");
	}
	echo("</table>");
	if(!isset($this->caredefinitions))
	{ // No care definitions available, so just allow color setting
	  echo($dtext['CareConfigError']); 
	}
	else
	{
	  $caredefsqr = inputclassbase::load_query("SELECT `". $this->caredefinitions. "`.*, `". $carecodecolors. "`.tekst AS pstyle FROM `". $this->caredefinitions. "` LEFT JOIN `". $carecodecolors. "` USING(id) ORDER BY id");
	  if(isset($caredefsqr['id']))
	  {
	    echo("<table border=0>");
	    foreach($caredefsqr['id'] AS $cdix => $cdid)
		{
		  echo("<tr><td>");
		  $caredeffield = new inputclass_textfield("caredef". $cdid,"40",$userlink,"tekst",$this->caredefinitions,$cdid,"id","","datahandler.php");
		  $caredeffield->echo_html();
	      echo("</td><td><img src='PNG/action_delete.png' onClick=\"setTimeout('deletecc(". $cdid. ")',1000);\">");
		  echo("</td><td style='". $caredefsqr['pstyle'][$cdix]. "' onClick=openpicker(". $cdid. ")>");
		  echo($caredefsqr['tekst'][$cdix]);
		  echo("</td></tr>");
		}
	  }
	  $caredeffield = new inputclass_textfield("caredef0","40",$userlink,"tekst",$this->caredefinitions,0,"id","","datahandler.php");
	  echo("<tr><td>");
	  $caredeffield->echo_html();
	  echo("</td><td><img src='PNG/action_add.png' onClick=\"setTimeout('refresh()',1000);\"></td></tr></table>");
	}
	echo("<SCRIPT> function refresh() { window.location =\"". $_SERVER['REQUEST_URI']. "\"; } </SCRIPT>");
	echo("<FORM METHOD=POST ACTION='". $_SERVER['REQUEST_URI']. "' ID=delform><input type=hidden name=delcc id=delcc></FORM>");
	echo("<SCRIPT> function deletecc(ccid) { document.getElementById('delcc').value=ccid; document.getElementById('delform').submit(); } </SCRIPT>");
	echo("<FORM METHOD=POST ACTION='". $_SERVER['REQUEST_URI']. "' ID=pickform><input type=hidden name=ccid id=ccid><input type=hidden name=bgcol id=bgcol><input type=hidden name=fgcol id=fgcol></FORM>");
    echo("<SCRIPT> function openpicker(ccid) { document.getElementById('ccid').value=ccid; document.getElementById('popupcolorpicker').style.display='table'; } </SCRIPT>");
    // Script to handle color setting from picker popup
    echo("<SCRIPT> function colorpicked(fgcol,bgcol) { document.getElementById('fgcol').value=fgcol; document.getElementById('bgcol').value=bgcol; document.getElementById('pickform').submit(); } </SCRIPT>");
  }
  
}
?>
