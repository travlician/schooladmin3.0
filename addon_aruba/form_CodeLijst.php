<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011 Aim4me N.V.   (http://www.aim4me.com)	      |
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

  echo ('<LINK rel="stylesheet" type="text/css" href="style_CodeLijst.css" title="style1">');
  
  echo("<html><head><title>Code Lijst LVS</title></head><body link=blue vlink=blue>");
  echo("<p>Onderstaande afkortingen worden gebruikt om informatie uit de database van het LVS te kunnen trekken. 
		Door deze een-een-duidige manier van invullen lukt dat. Onthoud: garbage in gabage out!. Geen intikfouten s.v.p<br><br>
		Alle datums wordt zo ingevoerd: <b>dd-mm-jjjj</b></p>");
		
		
// 	Tabel met de vereiste afkortingen voor de leerlingdetails:
  echo("<table>
		<tr><th colspan=3 class = OpmaakTitel>Afkortingen BO-scholen</th><th colspan=3 class = OpmaakTitel>Afkortingen AVO-scholen</th>
			<th colspan=3 class = OpmaakTitel>Afkortingen Landen</th><tr>	
		<tr><td></td> <td class = OpmaakCode>Code</td> <td class = OpmaakScholen>BO-School</td>
			<td></td> <td class = OpmaakCode>Code</td> <td class = OpmaakScholen>AVO-School</td>
					  <td class = OpmaakCode>Code</td> <td class = OpmaakScholen>Land</td>");
	
		$Nr = 0;
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>AN</td><td>ST Anna School</td>
				<td class = OpmaakNr>" .$Nr. ". </td><td class = OpmaakMem>AC</td><td>Colegio San Antonio</td>
				<td class = OpmaakMem>AUA</td><td>Aruba</td>") ;
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>AS</td><td>St Aloysius school</td>
				<td class = OpmaakNr>" .$Nr. ". </td><td class = OpmaakMem>AL</td><td>Avondleergangen mavo</td>
				<td class = OpmaakMem>NED</td><td>Nederland</td>") ;
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>BB</td><td>Colegio Bon Bini</td>
				<td class = OpmaakNr>" .$Nr. ". </td><td class = OpmaakMem>AV</td><td>Abraham de Veer</td>
				<td class = OpmaakMem>BON</td><td>Bonaire</td>") ;
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>CQ</td><td>Scol Caiquetio</td>
				<td class = OpmaakNr>" .$Nr. ". </td><td class = OpmaakMem>CA</td><td>Colegio Arubano</td>
				<td class = OpmaakMem>CUR</td><td>Cura&ccedil;ao</td>") ;
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>CC</td><td>Conrado Coronel</td>
				<td class = OpmaakNr>" .$Nr. ". </td><td class = OpmaakMem>EB</td><td>EPB</td>
				<td class = OpmaakMem>SXM</td><td>St Maarten</td>") ;
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>CM</td><td>Cacique Macuarima school</td>
				<td class = OpmaakNr>" .$Nr. ". </td><td class = OpmaakMem>EI</td><td>EPI</td>
				<td class = OpmaakMem>SUR</td><td>Suriname</td>") ;
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>CR</td><td>Colegio Cristo Rey</td>
				<td class = OpmaakNr>" .$Nr. ". </td><td class = OpmaakMem>FC</td><td>Filomena College</td>
				<td class = OpmaakMem>COL</td><td>Colombia</td>") ;
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>DC</td><td>St Domincus College</td>
				<td class = OpmaakNr>" .$Nr. ". </td><td class = OpmaakMem>JU</td><td>Juliana school</td>
				<td class = OpmaakMem>CHI</td><td>Chile</td>") ;
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>FA</td><td>Fatima College</td>
				<td class = OpmaakNr>" .$Nr. ". </td><td class = OpmaakMem>JW</td><td>John Wesley College</td>
				<td class = OpmaakMem>CHN</td><td>China</td>") ;
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>FB</td><td>Colegio Fr&egrave;re Bonifacius</td>
				<td class = OpmaakNr>" .$Nr. ". </td><td class = OpmaakMem>LS</td><td>La Salle College</td>
				<td class = OpmaakMem>DOM</td><td>Dominicaanse Republiek</td>") ;
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>FR</td><td>St Franciscus College</td>
				<td class = OpmaakNr>" .$Nr. ". </td><td class = OpmaakMem>MC</td><td>Maria College</td>
				<td class = OpmaakMem>HTI</td><td>Ha&iuml;ti</td>") ;
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>FT</td><td>Colegio Flipe B. Tromp</td>
				<td class = OpmaakNr>" .$Nr. ". </td><td class = OpmaakMem>MP</td><td>Mon Plaisir</td>
				<td class = OpmaakMem>JAM</td><td>Jamaica</td>") ;			
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>GZ</td><td>Graf von Zinzendorf</td>
				<td class = OpmaakNr>" .$Nr. ". </td><td class = OpmaakMem>SA</td><td>Colegio San Augustin</td>
				<td class = OpmaakMem>PER</td><td>Peru</td>") ;
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>HA</td><td>Hilario Angela</td>
				<td class = OpmaakNr>" .$Nr. ". </td><td class = OpmaakMem>BU</td><td>Buitenlandse scholen</td>
				<td class = OpmaakMem>PHL</td><td>Philipijnen</td>") ;		
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>KS</td><td>Kukwisa school</td>
				<td rowspan = 2 colspan = 3 class = OpmaakTitel>Geloof</td>
				<td class = OpmaakMem>USA</td><td>Verenigd Staten</td>") ;	

		$No = 0;
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>LW</td><td>Colegio Laura Wernet Paskel</td>
				<td rowspan = 2 colspan = 3 class = OpmaakTitel>Taal thuis</td>") ;
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>MA</td><td>Maria School</td>
				<td class = OpmaakNr>" .++$No. ". </td><td class = OpmaakMem>ADV</td><td>Adventist</td>") ;
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>MG</td><td>Maria Coretti college</td>
				<td class = OpmaakNr>" .++$No. ". </td><td class = OpmaakMem>ANG</td><td>Anglican</td>
				<td class = OpmaakMem>PA</td><td>Papiaments</td>") ;	
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>MI</td><td>St Michael school</td>
				<td class = OpmaakNr>" .++$No. ". </td><td class = OpmaakMem>BDM</td><td>Boedisme</td>
				<td class = OpmaakMem>NE</td><td>Nederlands</td>") ;
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>MO</td><td>Basis Mon Plaisir</td>
				<td class = OpmaakNr>" .++$No. ". </td><td class = OpmaakMem>CHR</td><td>Chistian</td>
				<td class = OpmaakMem>EN</td><td>Engels</td>") ;
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>MR</td><td>Maria Regina school</td>
				<td class = OpmaakNr>" .++$No. ". </td><td class = OpmaakMem>DDJ</td><td>Diciple di Jesus</td>
				<td class = OpmaakMem>SP</td><td>Spaans</td>") ;
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>OU</td><td>Colegio Ora Ubao</td>
				<td class = OpmaakNr>" .++$No. ". </td><td class = OpmaakMem>EVG</td><td>Evangelist</td>
				<td class = OpmaakMem>CH</td><td>Chinees</td>") ;
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>PK</td><td>Colegio Pastoor Kranwinkel</td>
				<td class = OpmaakNr>" .++$No. ". </td><td class = OpmaakMem>GRF</td><td>Gereformeerd</td>
				<td class = OpmaakMem>CR</td><td>Creols</td>") ;
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>PS</td><td>St Paulus school</td>
				<td class = OpmaakNr>" .++$No. ". </td><td class = OpmaakMem>HND</td><td>Hindo&iuml;sme</td>
				<td class = OpmaakMem>PT</td><td>Ha&iuml;tiaans Creools / Krey&ograve;l of Patua</td>") ;
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>PX</td><td>Pius X school</td>
				<td class = OpmaakNr>" .++$No. ". </td><td class = OpmaakMem>ISL</td><td>Islam</td>
				<td class = OpmaakMem>FR</td><td>Frans</td>") ;
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>RB</td><td>Reina Beatrix</td>
				<td class = OpmaakNr>" .++$No. ". </td><td class = OpmaakMem>JHV</td><td>Jehova</td>
				<td rowspan = 2 colspan = 3 class = OpmaakTitel>Burgelijke staat</td>") ;
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>RC</td><td>St Rosa College</td>
				<td class = OpmaakNr>" .++$No. ". </td><td class = OpmaakMem>JDD</td><td>Jodendom</td>") ;
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>RE</td><td>Faith Revival</td>
				<td class = OpmaakNr>" .++$No. ". </td><td class = OpmaakMem>RKK</td><td>Rooms Katholiek</td>
				<td class = OpmaakMem>GHW</td><td>Gehuw</td>") ;
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>RO</td><td>Rosario College</td>
				<td class = OpmaakNr>" .++$No. ". </td><td class = OpmaakMem>MRM</td><td>Mormonen</td>
				<td class = OpmaakMem>ONG</td><td>Ongehuw</td>") ;
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>SC</td><td>Colegio Sagrado Curason</td>
				<td class = OpmaakNr>" .++$No. ". </td><td class = OpmaakMem>PTC</td><td>Pentecostal</td>
				<td class = OpmaakMem>ALL</td><td>Alleenstaand</td>") ;
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>SF</td><td>Colegio Sta Filomena</td>
				<td class = OpmaakNr>" .++$No. ". </td><td class = OpmaakMem>PGM</td><td>Pinkstergemeente</td>
				<td class = OpmaakMem>GSH</td><td>Gescheiden</td>") ;
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>SH</td><td>Colegio San Hose</td>
				<td class = OpmaakNr>" .++$No. ". </td><td class = OpmaakMem>PRT</td><td>Protestant</td>
				<td class = OpmaakMem>SMW</td><td>Samenwonend</td>") ;
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>SK</td><td>Basisschool de Schakel</td>
				<td class = OpmaakNr>" .++$No. ". </td><td class = OpmaakMem>ONG</td><td>Geen</td>
				<td class = OpmaakMem>WDW</td><td>Weduw(e)naar</td>") ;
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>ST</td><td>Colegio Sta Teresita</td>
				<td class = OpmaakNr>" .++$No. ". </td><td class = OpmaakMem>ONB</td><td>Onbekend</td>") ;
		echo("<tr><td class = OpmaakNr>" .++$Nr. ". </td><td class = OpmaakMem>WA</td><td>Washington</td>") ;
//				<td class = OpmaakNr>" .++$No. ". </td><td class = OpmaakMem>ONG</td><td>Geen</td>

  echo("</table>");
  echo("<SPAN class=pagebreak>&nbsp;</SPAN>");
    
  // close the page
  echo("</html>");
?>
