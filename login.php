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
 require_once("captcha/simple-php-captcha.php");
 if(file_exists("logincondition.php"))
 {
   include("logincondition.php");
 }
 include ("schooladminfunctions.php");
 
 $myip = getenv("REMOTE_ADDR");
 $secqr = SA_loadquery("SELECT id FROM eventlog WHERE ipaddr='". $myip. "' AND LastUpdate > TIMESTAMPADD(HOUR,-1,NOW()) AND eventid LIKE 'DEN%'");
 if(isset($secqr['id']))
 { //A login from the current ip address has failed during the last hour!
   $_SESSION['captcha'] = simple_php_captcha();
 }
 else
   unset($_SESSION['captcha']);


 $LoginType = "Z";

 // Get a list of available languages
 
 $sql_query = "SET NAMES utf8";
 $sql_result = mysql_query($sql_query,$userlink);
 
 $sql_query = "SHOW TABLES LIKE 'tt_%'";
 $sql_result = mysql_query($sql_query,$userlink);
 if($sql_result)
 {
   for($r=0;$r<mysql_num_rows($sql_result);$r++)
   {
     $language[mysql_result($sql_result,$r,mysql_field_name($sql_result,0))] = substr(mysql_result($sql_result,$r,mysql_field_name($sql_result,0)),3);
   }
   $langcount = mysql_num_rows($sql_result);
 }
 else
   echo("ERROR: No language tables found in database!");

 // Get the language announcements
 if(isset($language))
 {
   foreach($language as $ltable => $defannounce)
   {
     $sql_query = "SELECT * FROM " . $ltable . " WHERE short='lang_announce'";
     $sql_result = mysql_query($sql_query,$userlink);
     if($sql_result && mysql_num_rows($sql_result) > 0)
     {
       $announce[$defannounce] = mysql_result($sql_result,0,'full');
     }
     else
     {
       $announce[$defannounce] = $defannounce;
     }
   }
 }

 // See if we need to switch current language
 if(isset($HTTP_GET_VARS['setlang']))
 {
   SA_loadLanguage($HTTP_GET_VARS['setlang']);
 }

 // Gat all the data from adds in an array.
 $sql_query = "SELECT * FROM adds ORDER BY position";
 $mysql_query = $sql_query;
 //echo $sql_query;
 $sql_result = mysql_query($mysql_query,$userlink);
 //echo mysql_error($userlink);
 $nrows = 0;
 if (mysql_num_rows($sql_result)!=0)
 {
   $nfields = mysql_num_fields($sql_result);
   for($r=0;$r<mysql_num_rows($sql_result);$r++)
   {
    $nrows++;
    $adds[mysql_result($sql_result,$r,'position')]=mysql_result($sql_result,$r,'HTML');
   } //for $r
   mysql_free_result($sql_result);
 }//If numrows != 0

 echo("<html><head><title>" . $dtext['login_title'] . "</title></head><body link=blue vlink=blue>");
 echo("<STYLE>BODY {background: url(loginbg.jpg) no-repeat center center fixed; 
  -webkit-background-size: cover;
  -moz-background-size: cover;
  -o-background-size: cover;
 background-size: cover; } </style>");
 echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';


 // Create a table with add cells and the login part
 echo("<table border=0 width=100%><TR>");

 // Top center add
 if(isset($adds[1]) && $adds[1] != "")
 { // Put the add in a 3 column spanned row
   echo("<TD COLSPAN=3 ALIGN=CENTER>" .$adds[1]. "</TD></TR><TR>");
 }

 // Left pane adds
 if(isset($adds[3]))
 {
   echo("<TD ALIGN=LEFT>");
   $ai = 3;
   while(isset($adds[$ai]))
   {
     if($adds[$ai] != "")
       echo("<BR>" . $adds[$ai]);
     $ai = $ai + 2;
   }
   echo("</TD>");
 }
 
 echo("<TD><font size=+2><center>" . $dtext['login_title'] . "</font><p>");

 // Show a link to select another language if present
 if($langcount > 1)
 {
   echo("<table border=0 width=100%><tr>");
   foreach($announce as $langid => $langannounce)
   {
     if($langid != $_SESSION['currentlanguage'])
       echo("<td><center><a href=login.php?setlang=" . $langid . ">" . $langannounce . "</a></td>");
   }
   echo("</tr></table><br>");
 }

 echo("<table border=1 style='background: rgba(255,255,255,0.4);' cellpadding=30><tr>");

 echo("<form method=post action=generallogin.php name=logindial>");

 echo("<td><center>");
 echo("<input type=text size=10 name=uid><br><font size=+1><b>" . $dtext['ID_CAP']);
 echo("<BR><BR>");
 echo("<input type=password size=10 name=pword><br>" . $dtext['Password'] . "</b></font>");
	if(isset($_SESSION['captcha']))
	{ // Extra security field must be asked
    echo '<br><img src="' . $_SESSION['captcha']['image_src'] . '" alt="CAPTCHA code">';
    echo("<br><input type=text size=5 name=captcha>");	  	
	}
 echo("</center></td>");

if ($usetextbox == 0)
{
  echo("<td><font><b>" . $dtext['Sel_utype'] . "</b><br><font color=brown>");
  echo("<input type=radio name = radio1 value = parent onClick=\"document.logindial.submit()\">");
  echo("<b>" . $dtext['Parent'] . "</b><br>");
  echo("<input type=radio name = radio1 value = student onClick=\"document.logindial.submit()\">");
  echo("<b>" . $dtext['Student'] . "</b><br>");
  echo("<input type=radio name = radio1 value = teacher onClick=\"document.logindial.submit()\">");
  echo("<b>" . $dtext['Teacher'] . "</b><br>");
} 
else 
{
  echo("<td><font><center><input type=text name=radio1><br><b>User type</b></center>");
}

   //echo("<p>");
   //echo ("<input type=\"submit\" value=\"" . $dtext['Login'] . "\">");

 echo("</font></td></tr>");
 echo("</table>");
 echo ("</form>");
 if ($privacyurl != "") 
   echo "<P><CENTER><A HREF=\"$privacyurl\">" . $dtext['Priv_policy'] . "</a></TD>";

 // Right pane adds
 if(isset($adds[4]))
 {
   echo("<TD ALIGN=RIGHT>");
   $ai = 4;
   while(isset($adds[$ai]))
   {
     if($adds[$ai] != "")
       echo("<BR>" . $adds[$ai]);
     $ai = $ai + 2;
   }
   echo("</TD>");
 }
 
 // bottom center add
 if(isset($adds[2]) && $adds[2] != "")
 { // Put the add in a 3 column spanned row
   echo("</TR><TR><TD COLSPAN=3 ALIGN=CENTER>" .$adds[2]. "</TD>");
 }


 echo("</TR></TABLE>");
 echo("</html>");
 exit;

?>

