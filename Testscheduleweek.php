4<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011 Aim4me N.V.   (http://www.aim4me.info)       |
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

class Testscheduleweek extends displayelement
{
  protected function add_contents()
  {
  }
  
  public function show_contents()
  {
	$dtext = $_SESSION['dtext'];
	$tdefs = testdef::testdef_listgroup();
    // Get all weight data in an array
    $iweights = inputclassbase::load_query("SELECT mid,testtype,weight FROM reportcalc");
    if(isset($iweights))
    {
      foreach($iweights['mid'] AS $wix => $dummy)
	    $weight[$iweights['mid'][$wix]][$iweights['testtype'][$wix]] = $iweights['weight'][$wix];
    }
    // Get a translation table for the testtypes
    $itt = inputclassbase::load_query("SELECT * FROM testtype");
    if(isset($itt))
      foreach($itt['type'] AS $tix => $ttyp)
	    $testtrans[$ttyp] = $itt['translation'][$tix];

    // First part of the page
    echo("<font size=+2><center>" . $dtext['lpt_title'] . "</font><p>");

    // Show for which group current 
    echo($dtext['Group_Cap'] . " <b>". $_SESSION['CurrentGroup']. "</b>");
    if(isset($_GET['subject']))
      echo(" ". $dtext['Subject']. " : " .$_GET['subject']. "<br><br>");
    else
      echo(" ". $dtext['Week']. " : <b>" .$_GET['week']. "</b><br><br>");

    if(isset($tdefs))
    {
      // Now create a table with all info on tests per week for the requested subject
      // Create the first heading row for the table
      echo("<table border=1 cellpadding=0>");
      echo("<tr><th>". $dtext['Date']. "</th>");
      echo("<th>". $dtext['Subject']. "</th>");
      echo("<th>". $dtext['Description']. "</th>");
      echo("<th>". $dtext['Domain']. "</th>");
      echo("<th>". $dtext['Term']. "</th>");
      echo("<th>". $dtext['Tools']. "</th>");
      echo("<th>". $dtext['Type']. "</th>");
      echo("<th>". $dtext['Short']. "</th>");
      echo("<th>". $dtext['Duration']. "</th>");
      echo("<th>". $dtext['Assignments']. "</th>");
      echo("<th>". $dtext['Weight']. "</th></tr>");
      // Now add each date (week) it's info
      foreach($tdefs AS $tid => $tdef)
      {
	    $tdate = inputclassbase::nldate2mysql($tdef->get_date());
	    $sclass = new sclass($tdef->get_cid());
	    if(isset($weight[$sclass->get_subject()->get_id()][$tdef->get_type()]))
	      $wgt = $weight[$sclass->get_subject()->get_id()][$tdef->get_type()];
	    else if(isset($weight['0'][$tdef->get_type()]))
	      $wgt = $weight['0'][$tdef->get_type()];
	    else
	      $wgt = "-";
        $wkno = date("W",mktime(0,0,0,substr($tdate,5,2),substr($tdate,8,2),substr($tdate,0,4)));
		if($wkno == $_GET['week'])
		{
	      if($wgt != "-")
	        echo("<tr class=altbg>");
	      else if($tdef->get_type() == $dtext['no_lesson'])
	        echo("<tr class=no_lesson>");
	      else
	        echo("<tr>");
	      echo("<td>". $tdef->get_date(). " (". $tdef->get_last_update(). ")</td>");
		  echo("<td>". $sclass->get_subject()->get_shortname(). "</td>");
	      echo("<td>". $tdef->get_desc(). "</td>");
	      echo("<td>". $tdef->get_domain(). "</td>");
	      echo("<td>". $tdef->get_term(). "</td>");
	      echo("<td>". $tdef->get_tools(). "</td>");
	      echo("<td>". ($tdef->get_type() != '' ? ("<a href=# class=hidelink title=\"". $testtrans[$tdef->get_type()]. "\">". $tdef->get_type(). "</a>") : "&nbsp"). "</td>");
	      echo("<td>". $tdef->get_short_desc(). "</td>");
	      echo("<td>". $tdef->get_duration(). "</td>");
	      echo("<td>". $tdef->get_assign(). "</td>");
	  
	      echo("<td><center>". $wgt. "</td></tr>");
		}
    }
    echo("</table>");
  }
  }
}
?>
