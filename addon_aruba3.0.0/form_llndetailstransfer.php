<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2014 Aim4me N.V.   (http://www.aim4me.com)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
  session_start();

  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
  require_once("student.php");
  // Link input library with database
  inputclassbase::dbconnect($userlink);
   
  // Get a list of groups
  $groups = SA_loadquery("SELECT * FROM sgroup LEFT JOIN ". $teachercode. " ON(tid_mentor=tid) WHERE active=1 AND tid_mentor <> 1 ORDER BY groupname");
  
  $tables = SA_loadquery("SELECT table_name FROM student_details WHERE table_name NOT LIKE '%*%' AND type<>'picture' ORDER BY seq_no");
  
  if(isset($groups))
 {
 // Voorkant KlasKaart:
    echo("<html><head><title>llndetailstransfer</title></head><body link=blue vlink=blue>");
			echo("<table>
				<tr><th>Klas</th><th>Nr.</th><th>Achternaam</th><th>Voornaam</th>");
		foreach($tables['table_name'] AS $tname)
		  echo("<th>". $tname. "</th>");
		echo("</tr>");
	foreach($groups['gid'] AS $sgid)
	  $KlasLijst[$sgid] = new group($sgid);
	//$KlasLijst = group::group_list();
    foreach ($KlasLijst AS $group)
	{
		
// 	Tabel met de gewenste gegevens van de leerling uit de betreffende klas:
	    $LLlijst = student::student_list($group);
		if(isset($LLlijst))
		foreach ($LLlijst AS $student)
		{
		  if($student <> null)
		  {
				 echo("<tr><td>". $group->get_groupname(). "</td><td>". $student->get_student_detail("*sid"). "</td><td>". $student->get_lastname(). "</td><td>". $student->get_firstname(). "</td>");
				 foreach($tables['table_name'] AS $tname)
				   echo("<td>". $student->get_student_detail($tname). "</td>");
				 echo("</tr>");
		  }
  		} // einde foreach student / leerling uit de klas			
	} // einde foreach group / klas
	echo("</table>");



  } // Endif 1
    
  // close the page
  echo("</html>");
?>
