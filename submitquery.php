<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011  Aim4me N.V.  (http://www.aim4me.info)       |
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

  $login_qualify = 'A';
  require_once("schooladminfunctions.php");

  $sqltext = $HTTP_POST_VARS['sqltext'];
  $returnstyle = $HTTP_POST_VARS['returnstyle'];
  $asciidelim = $HTTP_POST_VARS['asciidelim'];

  echo("<html><head><title>" . $dtext['Qres_title'] . "</title></head>");
  echo("<body background=schooladminbg.jpg>");
  echo '<LINK rel="stylesheet" type="text/css" href="style.css" title="style1">';
  echo("<font size=+1>");
  echo($dtext['Qres_expl_1'] . ":<br> <i>");
 
  $slash = addslashes("\\");
  $emptys = "";
  $sqltext = ereg_replace($slash,$emptys,$sqltext);
  echo($sqltext);
  echo("</i><br>");
  echo '<a href="admin.php">';
  echo($dtext['back_admin'] . "</a><hr>");

  $mysqltext = $sqltext;
  $sql_result = mysql_query($mysqltext,$userlink);
  echo(mysql_error());
 
   if (($sql_result)!=1)
   {
     $nfields = mysql_num_fields($sql_result);
     for ($i=0;$i<$nfields;$i++)
     {
       $titles[$i+1] = mysql_field_name($sql_result,$i);
     } // for
     $nrows = 0;
     while (mysql_fetch_row($sql_result))
     {
       $nrows++;
       for ($i=0;$i<$nfields;$i++)
       {
         $ary[$nrows][$i+1] = mysql_result($sql_result,$nrows-1,$i);
       } // for
     } //while

     if ($returnstyle == "returnhtml")
     {
       //Now print the table (entirely contained in $ary...
       echo("<table border=0><tr bgcolor=yellow>");
  
       //First the headers...
       for ($f =1;$f<=$nfields;$f++)
       {
         echo("<th>".$titles[$f]."</th>");
       }


       //Now the data...
       for ($r=1;$r<=$nrows;$r++)
       {
         if (intval($r/2) == $r/2)
         { 
           $rowcolor = "99ff99";
         }
         else 
         {
           $rowcolor="9999ff";
         }
         echo ("<tr bgcolor=$rowcolor>");
         for ($f=1;$f<=$nfields;$f++)
         {
           echo("<td>".$ary[$r][$f]."</td>");
         }
       }

       echo ("</table>");
     } //returnstyle = html


     if ($returnstyle != "returnhtml")
     {
       if ($returnstyle == "returntabtext")
       {
         $delim = chr(9);
       }
       if ($returnstyle == "returncommatext")
       {
         $delim = ",";
       }
       if ($returnstyle == "returnothertext")
       {
         $delim = chr($asciidelim);
       }
       echo ("<pre>");
       for ($r=1;$r<=$nrows;$r++)
       {
         $lineoftext = "";
         for ($f=1;$f<=$nfields;$f++)
         {
           $lineoftext = $lineoftext . $ary[$r][$f] .$delim;

           //echo("<td>".$ary[$r][$f]."</td>");
         }
         $lineoftext = substr($lineoftext,0,strlen($lineoftext)-1);
         echo $lineoftext;
         echo(chr(13));

       } //for r



     } //$returnstyle != html


     mysql_free_result($sql_result);
   }//If numrows != 0
   SA_closeDB();
   echo("</html>");
?>
