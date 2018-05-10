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
require_once("displayelements/extendableelement.php");
require_once("CalendarLayer.php");

class Calendar extends extendableelement
{
  protected function add_contents()
  {
    parent::add_contents();
    $this->add_element(new LayeredCalendar);
  }
}

require_once("teacher.php");
class LayeredCalendar extends displayelement
{
  protected $startDate,$endDate;
  protected $calendarlayers;
  protected function add_contents()
  {
    // Clear flilters if another group is selected
	if(isset($_POST['gselectfld']))
	{
	  unset($_SESSION['flt_timetablegroup']);
	  unset($_SESSION['flt_timetablesubject']);
	  unset($_SESSION['flt_timetableteacher']);
	  unset($_SESSION['flt_timetablelocation']);
	  echo("Filters cleared");
	}
    if(isset($_SESSION['CurrentCalendarDate']))
	  $this->startDate = $_SESSION['CurrentCalendarDate'];
	else
	  $this->startDate = mktime(0,0,0,date("n"),1);
	// Adjust month if requested by navigation
	if(isset($_POST['nextmonth']))
	  $this->startDate = mktime(0,0,0,date("n",$this->startDate)+1,1,date("Y",$this->startDate));
	else if(isset($_POST['prvmonth']))
	  $this->startDate = mktime(0,0,0,date("n",$this->startDate)-1,1,date("Y",$this->startDate));
	$_SESSION['CurrentCalendarDate'] = $this->startDate;
	$this->endDate = mktime(0,0,0,date("n",$this->startDate)+1,0,date("Y",$this->startDate));
	
	// Create the layers
	$dtext = $_SESSION['dtext'];
	$this->calendarlayers = array(new DisabledDays($dtext['DisabledDays'],true,$this->startDate,$this->endDate),
								  new OffDays($dtext['OffDays'],true,$this->startDate,$this->endDate),
								  new TimetableTimes($dtext['TimetableTimes'],true,$this->startDate,$this->endDate),
								  new TimetableActivities($dtext['TimetableActivities'],true,$this->startDate,$this->endDate),
								  new CalendarAnnouncements($dtext['CalendarAnnouncements'],true,$this->startDate,$this->endDate));
  }

  public function show_contents()
  {
    global $currentuser;
	$dtext = $_SESSION['dtext'];
    echo('<LINK href="Calendar.css" rel="stylesheet" type="text/css">');
    echo("<p><center>" . $dtext['tpage_calendar'] . "</center></p>");
    if(isset($_POST['eday']))
	{
	  $this->editday($_POST['eday']);
	  return;
	}
	else if (isset($_POST['tday']))
	{
	  $this->viewday($_POST['tday']);
	  return;
	}
	foreach($this->calendarlayers AS $callayer)
	{
	  if($callayer->request_control())
	  {
	    $callayer->show_control();
		return;
	  }
	}
	echo("<FORM METHOD=POST ACTION=". $_SERVER['REQUEST_URI']. " ID=viewdayform NAME=viewdayform><INPUT TYPE=hidden NAME=tday></FORM>");
	echo("<FORM METHOD=POST ACTION=". $_SERVER['REQUEST_URI']. " ID=editdayform NAME=editdayform><INPUT TYPE=hidden NAME=eday></FORM>");
	echo(" <SCRIPT> function viewday(tdate) { var formref = document.getElementById('viewdayform'); formref.tday.value=tdate; formref.submit(); } </SCRIPT> ");
	echo(" <SCRIPT> function editday(tdate) { var formref = document.getElementById('editdayform'); formref.eday.value=tdate; formref.submit(); } </SCRIPT> ");
	$startday = date("w",$this->startDate);
	$daysinmonth = date("t",$this->startDate);
	// Show month navigation
	echo("<FORM name=pmonth id=pmonth method=POST action='". $_SERVER['REQUEST_URI']. "'><p><center><INPUT TYPE=SUBMIT VALUE='<--' NAME=prvmonth>");
	echo(" ". $dtext["month_". date("n",$this->startDate)]. " ". date("Y", $this->startDate));
	echo("<INPUT TYPE=SUBMIT VALUE='-->' NAME=nextmonth></center></p></FORM>");
	echo("<DIV style='width: 100%; overflow: auto; white-space: nowrap;'>");
	// Show inactive boxes for first days
	for($dd = 0; $dd < $startday; $dd++)
	{
	  $checkday = mktime(0,0,0,date('n',$this->startDate),7-$dd,date("Y",$this->startDate));
	  $daystyle = "disabledday";
	  foreach($this->calendarlayers AS $callayer)
	  {
	    if($callayer->get_dateinfo($checkday) != NULL && $daystyle == "disabledday")
		{
		  if(get_class($callayer) == "DisabledDays")
		    $daystyle = "disablednwday";
		}
	  }	  
	  echo("<div class=". $daystyle. ">&nbsp;</div>");
	}
	$pos = $dd;
	for($rd = 1; $rd <= $daysinmonth; $rd++)
	{
	  echo("<div class=");
	  // Select the class based on wether data is present in certain layers
	  $checkday = mktime(0,0,0,date('n',$this->startDate),$rd,date("Y",$this->startDate));
	  $daystyle = "realday";
	  $dayspecialstyle = "";
	  foreach($this->calendarlayers AS $callayer)
	  {
	    if($callayer->get_dateinfo($checkday) != NULL && $daystyle == "realday")
		{
		  if(get_class($callayer) == "DisabledDays")
		    $daystyle = "noworkday";
		  else if(get_class($callayer) == "OffDays")
		  {
		    $daystyle = "offday";
			$odd = $callayer->get_dateinfo($checkday);
			$bgpos = stripos($odd,"background-color");
			if($bgpos > 0)
			{
			  $epos = stripos($odd,";",$bgpos);
			  $dayspecialstyle = substr($odd,$bgpos,$epos - $bgpos);
			}
		  }
	      else
		    $daystyle = "lessonday";
		}
	  }
	  echo($daystyle. ($dayspecialstyle != "" ? " style='". $dayspecialstyle. "'" : ""). "><SPAN onClick='toClassBook(\"". date('d-m-Y',$checkday). "\");'>". $dtext["dayabbrev_". (($startday + $rd - 1) % 7)]. " ". $rd. "</SPAN>");
	  // Now add the view and edit icons
	  echo("<img src='PNG/search.png' onClick='viewday(\"". date('Y-m-d',mktime(0,0,0,date("n",$this->startDate),$rd,date("Y",$this->startDate))). "\");'>");
	  if(isset($currentuser))
	    echo("<img src='PNG/reply.png' onClick='editday(\"". date('Y-m-d',mktime(0,0,0,date("n",$this->startDate),$rd,date("Y",$this->startDate))). "\");'>");
	  echo("<BR>");
      // Add the contents based on the layers
	  foreach($this->calendarlayers AS $callayer)
	  {
	    if($callayer->get_enabled() && $callayer->get_dateinfo($checkday) != NULL && $callayer->get_dateinfo($checkday) != " ")
	      echo($callayer->get_dateinfo($checkday). "<BR>");
	  }
	  
	  echo("</div>");
	  if(($startday + $rd) % 7 == 0)
	    echo("<BR>");
	}  
    echo("</DIV>");
    // Add form and script to link with classbook
    echo("<FORM ID=cblink MEHTOD=GET action='teacherpage.php'><INPUT TYPE=HIDDEN NAME=ClassBookDate ID=cblinkdate VALUE=''><INPUT TYPE=HIDDEN NAME=Page VALUE='ClassBook'></FORM>")	;
    if(isset($_SESSION['dtext']['tpage_classbook']))
	  echo("<SCRIPT> function toClassBook(cbdat) { document.getElementById('cblinkdate').value=cbdat; document.getElementById('cblink').submit(); } </SCRIPT>");
    else
	  echo("<SCRIPT> function toClassBook(cbdat) {  } </SCRIPT>");
  }
  
  private function viewday($vdate)
  {
    $mydate = mktime(0,0,0,substr($vdate,5,2),substr($vdate,8,2),substr($vdate,0,4));
    echo("<img src=PNG/search.png> ". date("j",$mydate). " ". $_SESSION['dtext']["month_". date("n",$mydate)]. " ". date("Y",$mydate). "<BR>");
	foreach($this->calendarlayers AS $callayer)
	{
	  if($callayer->get_enabled())
	    echo("<SPAN class=layerlabel>". $callayer->get_layername(). "</span> : ". $callayer->get_dateinfo($mydate). "<BR>");
	}
  }
  private function editday($edate)
  {
    $mydate = mktime(0,0,0,substr($edate,5,2),substr($edate,8,2),substr($edate,0,4));
    echo("<img src=PNG/reply.png> ". date("j",$mydate). " ". $_SESSION['dtext']["month_". date("n",$mydate)]. " ". date("Y",$mydate). "<BR>");
	foreach($this->calendarlayers AS $callayer)
	{
	  if($callayer->get_enabled())
	    echo("<SPAN class=layerlabel>". $callayer->get_layername(). "</span> : ");
		$callayer->edit_dateinfo($mydate);
		echo("<BR>");
	}	
  }
}

?>
