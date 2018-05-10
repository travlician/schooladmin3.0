<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2012 Aim4me N.V.   (http://www.aim4me.com)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
if(isset($_POST['rid']))
  include("RegistratieformulierAHA.php");
else
{
  session_start();
  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
  include ("schooladminconstants.php");
  include ("inputlib/inputclasses.php");
  require_once("InschrijvingAHAfuncs.php");
  inputclassbase::dbconnect($userlink);
  if(isset($_POST['rrid']))
  { // Need to remove a record.
    mysql_query("DELETE FROM inschrijvingAHA WHERE rid=". $_POST['rrid'], $userlink);
  }
  echo ('<HTML><LINK rel="stylesheet" type="text/css" href="style_InschrijfAHA.css" title="style1">');

  if(isset($_GET['paidlist']) || isset($_GET['unpaidlist']))
  {
    if(isset($_GET['paidlist']))
	  echo("<H1>Lijst inschrijvers die betaald hebben</H1>");
	else
	  echo("<H1>Lijst inschrijvers die nog niet betaald hebben</H1>");
    { // Student search requested, display a list of complying students
      $ridsqr = inputclassbase::load_query("SELECT rid FROM inschrijvingAHA WHERE ". (isset($_GET['paidlist']) ? "betaald=1" : "(betaald IS NULL OR betaald=0)"). " AND year='". (date("Y"). "-". (date("Y")+1)). "' ORDER BY lastname,firstname"); 
	  if(isset($ridsqr['rid']))
	  {
	    echo("<TABLE class=studentlist><TR><TH>Achternaam</TH><TH>Voornamen</TH><TH>Roepnaam</TH><TH>Geb. datum</TH>
		      <TH>Pakket</TH><TH>Cert.</TH><TH>Vrijst.</TH><TH>Lesvakken</TH><TH>Bedrag</TH><TH>Jr./Lok.</TH><TH><img src='PNG/reply.png'>");
		if(isset($_GET['unpaidlist']))
		  echo("/<img src='PNG/action_delete.png'>");
	    echo("</TH></TR>");
	    foreach($ridsqr['rid'] AS $rid)
		{
          $fld = new inputclass_textfield("lslname",20,NULL,"lastname","inschrijvingAHA",$rid,"rid",NULL,NULL);
		  echo("<TR><TD>". $fld->__toString(). "</TD>");
          $fld = new inputclass_textfield("lsfname",20,NULL,"firstname","inschrijvingAHA",$rid,"rid",NULL,NULL);
		  echo("<TD>". $fld->__toString(). "</TD>");
          $fld = new inputclass_textfield("lsrname",20,NULL,"roepnaam","inschrijvingAHA",$rid,"rid",NULL,NULL);
		  echo("<TD>". $fld->__toString(). "</TD>");
          $fld = new inputclass_textfield("lsgdate1",20,NULL,"gebdag","inschrijvingAHA",$rid,"rid",NULL,NULL);
		  echo("<TD>". $fld->__toString());
          $fld = new inputclass_textfield("lsgdate2",20,NULL,"gebmaand","inschrijvingAHA",$rid,"rid",NULL,NULL);
		  echo(" ". $fld->__toString());
          $fld = new inputclass_textfield("lsgdate3",20,NULL,"gebjaar","inschrijvingAHA",$rid,"rid",NULL,NULL);
		  echo(" ". $fld->__toString(). "</TD>");
          $fld = new inputclass_textfield("lspakket2",2,NULL,"pakket","inschrijvingAHA",$rid,"rid",NULL,NULL);
		  echo("<TD>". $fld->__toString(). "</TD>");
		  // Retrieve a list of certificates
		  $clist = inputclassbase::load_query("SELECT GROUP_CONCAT(shortname) AS clist FROM inschrijvingCerts LEFT JOIN subject USING(mid) WHERE rid=". $rid);
		  if(isset($clist['clist']))
		    echo("<TD>". $clist['clist'][0]. "</TD>");
		  else
		    echo("<TD>&nbsp;</TD>");
		  // Retrieve a list of vrijstelling
		  $vlist = inputclassbase::load_query("SELECT GROUP_CONCAT(shortname) AS vlist FROM inschrijvingVrijst LEFT JOIN subject USING(mid) WHERE rid=". $rid);
		  if(isset($vlist['vlist']))
		    echo("<TD>". $vlist['vlist'][0]. "</TD>");
		  else
		    echo("<TD>&nbsp;</TD>");		  
		  // Retrieve list of subjects and administration fee
          $scq = "SELECT SUM(IF(inschrijvingVrijst.mid IS NULL AND inschrijvingCerts.mid IS NULL,1,0)) AS sc, GROUP_CONCAT(IF(inschrijvingVrijst.mid IS NULL AND inschrijvingCerts.mid IS NULL,shortname,NULL)) AS sjs";
          $scq .= " FROM inschrijvingPakket LEFT JOIN inschrijvingVrijst USING(rid,mid) LEFT JOIN inschrijvingCerts USING(rid,mid)";
          $scq .= " LEFT JOIN subject USING(mid) WHERE rid=". $rid. " GROUP BY rid";
          $scqr = inputclassbase::load_query($scq);
          if(isset($scqr['sc'][0]))
          {
            echo("<TD>". $scqr['sjs'][0]. "</TD><TD>". calcfee($rid). ",-</TD>");
          }
          else
            echo("<TD>&nbsp</TD><TD>50,-</TD>");
		  // Show in which year/location placed
          $fld = new inputclass_textfield("lsryearpl",20,NULL,"plaatsjaar","inschrijvingAHA",$rid,"rid",NULL,NULL);
		  echo("<TD>". $fld->__toString(). "</TD>");
		  // Show function buttons
		  echo("<TD><IMG src='PNG/reply.png' onClick='sendrid(". $rid. ");'>");
		  if(isset($_GET['unpaidlist']))
		    echo("/<IMG src='PNG/action_delete.png' onClick='remrid(". $rid. ");'>");
		  echo("</TD></TR>");
		}
		echo("</TABLE>");
		echo("<FORM ACTION=". $_SERVER['REQUEST_URI']. " METHOD=POST NAME=ridsend ID=ridsend>");
		echo("<INPUT TYPE=hidden NAME=rid VALUE=0 ID=rid></FORM>");
		echo("<FORM ACTION=". $_SERVER['REQUEST_URI']. " METHOD=POST NAME=ridrem ID=ridrem>");
		echo("<INPUT TYPE=hidden NAME=rrid VALUE=0 ID=rrid></FORM>");
		echo("<SCRIPT> function sendrid(rid) { document.getElementById('rid').value=rid; document.getElementById('ridsend').submit(); } </SCRIPT>");
		echo("<SCRIPT> function remrid(rid) { document.getElementById('rrid').value=rid; if(confirm('Weet je zeker dat je dit record wilt verijderen?')) document.getElementById('ridrem').submit(); } </SCRIPT>");
	  }
    }
	echo("<BR><a href=form_Inschrijven_AHA.php>Terug naar zoeken</a>");
  }
  else
  { // Need to search or present search fields
   echo("<H1>Inschrijving AHA</H1>");
   if(isset($_POST['slname']) && ($_POST['slname'] != "" || $_POST['sfname'] != ""))
    { // Student search requested, display a list of complying students
      $ridsqr = inputclassbase::load_query("SELECT rid FROM inschrijvingAHA WHERE firstname LIKE '%". $_POST['sfname']. "%' AND lastname LIKE '%". $_POST['slname']. "%' AND year='". (date("Y"). "-". (date("Y")+1)). "' ORDER BY lastname,firstname"); 
	  if(isset($ridsqr['rid']))
	  {
	    echo("<TABLE class=studentlist><TR><TH>Achternaam</TH><TH>Voornamen</TH><TH>Roepnaam</TH><TH>Geb. datum</TH>
		        <TH>Cert.</TH><TH>Vrijst.</TH><TH>Lesvakken</TH><TH>Bedrag</TH><TH>Pakket</TH><TH>Betaald</TH><TH>Jr./Lok.</TH>
				<TH><img src='PNG/reply.png'></TH></TR>");
	    foreach($ridsqr['rid'] AS $rid)
		{
          $fld = new inputclass_textfield("lslname",20,NULL,"lastname","inschrijvingAHA",$rid,"rid",NULL,NULL);
		  echo("<TR><TD>". $fld->__toString(). "</TD>");
          $fld = new inputclass_textfield("lsfname",20,NULL,"firstname","inschrijvingAHA",$rid,"rid",NULL,NULL);
		  echo("<TD>". $fld->__toString(). "</TD>");
          $fld = new inputclass_textfield("lsrname",20,NULL,"roepnaam","inschrijvingAHA",$rid,"rid",NULL,NULL);
		  echo("<TD>". $fld->__toString(). "</TD>");
          $fld = new inputclass_textfield("lsgdate1",20,NULL,"gebdag","inschrijvingAHA",$rid,"rid",NULL,NULL);
		  echo("<TD>". $fld->__toString());
          $fld = new inputclass_textfield("lsgdate2",20,NULL,"gebmaand","inschrijvingAHA",$rid,"rid",NULL,NULL);
		  echo(" ". $fld->__toString());
          $fld = new inputclass_textfield("lsgdate3",20,NULL,"gebjaar","inschrijvingAHA",$rid,"rid",NULL,NULL);
		  echo(" ". $fld->__toString(). "</TD>");
		  // Retrieve a list of certificates
		  $clist = inputclassbase::load_query("SELECT GROUP_CONCAT(shortname) AS clist FROM inschrijvingCerts LEFT JOIN subject USING(mid) WHERE rid=". $rid);
		  if(isset($clist['clist']))
		    echo("<TD>". $clist['clist'][0]. "</TD>");
		  else
		    echo("<TD>&nbsp;</TD>");
		  // Retrieve a list of vrijstelling
		  $vlist = inputclassbase::load_query("SELECT GROUP_CONCAT(shortname) AS vlist FROM inschrijvingVrijst LEFT JOIN subject USING(mid) WHERE rid=". $rid);
		  if(isset($vlist['vlist']))
		    echo("<TD>". $vlist['vlist'][0]. "</TD>");
		  else
		    echo("<TD>&nbsp;</TD>");
		  // Retrieve list of subjects and administration fee
          $scq = "SELECT SUM(IF(inschrijvingVrijst.mid IS NULL AND inschrijvingCerts.mid IS NULL,1,0)) AS sc, GROUP_CONCAT(IF(inschrijvingVrijst.mid IS NULL AND inschrijvingCerts.mid IS NULL,shortname,NULL)) AS sjs";
          $scq .= " FROM inschrijvingPakket LEFT JOIN inschrijvingVrijst USING(rid,mid) LEFT JOIN inschrijvingCerts USING(rid,mid)";
          $scq .= " LEFT JOIN subject USING(mid) WHERE rid=". $rid. " GROUP BY rid";
          $scqr = inputclassbase::load_query($scq);
          if(isset($scqr['sc'][0]))
          {
            echo("<TD>". $scqr['sjs'][0]. "</TD><TD>". calcfee($rid). ",-</TD>");
          }
          else
            echo("<TD>&nbsp</TD><TD>50,-</TD>");
          $fld = new inputclass_textfield("lspakket2",2,NULL,"pakket","inschrijvingAHA",$rid,"rid",NULL,NULL);
		  echo("<TD>". $fld->__toString(). "</TD>");
		  
          $fld = new inputclass_checkbox("lspaid",NULL,NULL,"betaald","inschrijvingAHA",$rid,"rid",NULL,NULL);
		  echo("<TD>". ($fld->__toString() == '1' ? "Ja" : "Nee"). "</TD>");
          $fld = new inputclass_textfield("lsrplace",20,NULL,"plaatsjaar","inschrijvingAHA",$rid,"rid",NULL,NULL);
		  echo("<TD>". $fld->__toString(). "</TD>");
		  echo("<TD><IMG src='PNG/reply.png' onClick='sendrid(". $rid. ");'>");
		  echo("</TD></TR>");
		}
		echo("</TABLE>");
		echo("<FORM ACTION=". $_SERVER['REQUEST_URI']. " METHOD=POST NAME=ridsend ID=ridsend>");
		echo("<INPUT TYPE=hidden NAME=rid VALUE=0 ID=rid></FORM>");
		echo("<SCRIPT> function sendrid(rid) { document.getElementById('rid').value=rid; document.getElementById('ridsend').submit(); } </SCRIPT>");
	  }
    }
    {  // "Virgin" entry, give fields to search students
	  echo("Vul de gegevens in om studenten te zoeken");
	  echo("<FORM METHOD=POST ACTION='form_Inschrijven_AHA.php'>");
	  echo("<LABEL>Achternaam:</LABEL><INPUT TYPE=TEXT SIZE=40 NAME=slname>");
	  echo("<BR><LABEL>Voornamen:</LABEL><INPUT TYPE=TEXT SIZE=40 NAME=sfname>");
	  echo("<BR><LABEL>&nbsp;</LABEL><INPUT TYPE=SUBMIT VALUE='ZOEKEN'>");
	  echo("</FORM>");
	  echo("<BR><a href='form_Inschrijven_AHA.php?paidlist=1'>Lijst inschrijvers die betaald hebben</a>");
	  echo("<BR><a href='form_Inschrijven_AHA.php?unpaidlist=1'>Lijst inschrijvers die nog niet betaald hebben</a>");
	}
  }
  echo("</HTML>");  
}
?>
