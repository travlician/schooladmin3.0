4<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2015 Aim4me N.V.   (http://www.aim4me.info)       |
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
require_once("group.php");
require_once("testdef.php");
require_once("sclass.php");

class Testscheduleyear extends displayelement
{
  protected function add_contents()
  {
  }
  
  public function show_contents()
  {
	$dtext = $_SESSION['dtext'];
	$tdefs = testdef::testdef_listgroup();
	$cury = date('Y');
    if(isset($tdefs))
    {
	  unset($firstweek);
      foreach($tdefs AS $tid => $tdef)
      {
	    if($tdef->get_date() != '00-00-0000')
		{
	      $cid = new sclass($tdef->get_cid());
		  $ssub = $cid->get_subject()->get_shortname();
	      $tdate = $tdef->get_date();
	      $tdy = date("j",mktime(0,0,0,substr($tdate,3,2),substr($tdate,0,2),substr($tdate,6,4)));
	      $tdyr = date("Y",mktime(0,0,0,substr($tdate,3,2),substr($tdate,0,2),substr($tdate,6,4)));

          $twk = 1 * date("W",mktime(0,0,0,substr($tdate,3,2),substr($tdate,0,2),substr($tdate,6,4)));
		  
	      if($tdef->get_type() != "")
		  {
            if(isset($ts[$ssub][$twk]))
	          $ts[$ssub][$twk] .= "+". $tdef->get_type();
	        else
	          $ts[$ssub][$twk] = $tdef->get_type();

/*			if(isset($tl[$ssub][$twk]))
	          $tl[$ssub][$twk] .= "+". $tdy;
	        else
	          $tl[$ssub][$twk] = $tdy; */

            if(isset($tl[$ssub][$twk]))
	          $tl[$ssub][$twk] .= "+". $tdy;
	        else
	          $tl[$ssub][$twk] = $tdy;

			if(isset($wl[$twk]))
	          $wl[$twk] .= "+". $tdy;
	        else
	          $wl[$twk] = $tdy;
          }
		  if(!isset($firstweek) && $tdyr < ($cury +2) && $tdyr > ($cury - 2))
		  {
	        $firstweek = $twk;
			$fwdate = $tdate;
	      }

	      $lastweek = $twk;
	      if($twk == 53)
	        $week53 = 1;
	    }
	  }
    }
    // Create a list of used weeks
	if(!isset($firstweek))
	  $firstweek=1;
	if(!isset($lastweek))
	  $lastweek=$firstweek;
    if($firstweek > $lastweek)
    {
      if(isset($week53))
	  {
	    for($wkn = $firstweek; $wkn <= 53; $wkn++)
	      $usedweeks[$wkn] = 1;
	    for($wkn = 1; $wkn <= $lastweek; $wkn++)
	    $usedweeks[$wkn] = 1;
	  }
	  else
	  {
	    for($wkn = $firstweek; $wkn <= 52; $wkn++)
	      $usedweeks[$wkn] = 1;
	    for($wkn = 1; $wkn <= $lastweek; $wkn++)
	      $usedweeks[$wkn] = 1;
	  }
    }
    else
    {
      for($wkn = $firstweek;$wkn <= $lastweek; $wkn++)
	    $usedweeks[$wkn] = 1;
    } 
    

    // First part of the page
    echo("<font size=+2><center>" . $dtext['tschd_title'] . " ". $dtext['group']. " ". $_SESSION['CurrentGroup']. "</font><p>");

    // Show for which group current editing and allow changing the group if teacher
    echo("<br><br>");

    if(isset($ts))
    {
	  ksort($ts);
      // Now create a table with all info on tests per week
      // Create the first heading row for the table
      echo("<table border=1 cellpadding=0>");
      echo("<tr><th>". $dtext['Week']. ":</th>");
      foreach($usedweeks AS $wkno => $dum1)
        echo("<th><a href=". $_SERVER['REQUEST_URI']. "&week=". $wkno. " title=\"". (isset($wl[$wkno]) ? $wl[$wkno] : "")."\">". $wkno. "</th>");
      echo("</tr>");
      // Now add each subjects info
	  $altrow = false;
      foreach($ts AS $subj => $dum2)
      {
        echo("<tr". ($altrow ? ' class=altbg' : ''). "><th>");
	    if(isset($lessonplan) && $lessonplan == 1)
	    {
	      echo("<a href=\"viewltp.php?subject=". $subj. "\">". $subj. "</th>");
	    }
	    else
	      echo($subj. "</th>");
	    foreach($usedweeks AS $wkno => $dum3)
	    {
	      if(isset($ts[$subj][$wkno]))
	        echo("<td><a href=# class=hidelink title=\"". $tl[$subj][$wkno]. "\">". $ts[$subj][$wkno]. "</a></td>");
	      else
	        echo("<td>&nbsp;</td>");
	    }
	    echo("</tr>");
		$altrow = !$altrow;
      }
      echo("</table>");
	
    }
  }
}
?>
