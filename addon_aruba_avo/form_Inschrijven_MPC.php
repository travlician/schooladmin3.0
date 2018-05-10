<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2015 Aim4me N.V.   (http://www.aim4me.com)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
if(isset($_POST['rid']))
  include("RegistratieformulierMPC.php");
else
{
  session_start();
  $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
  include ("schooladminconstants.php");
  include ("inputlib/inputclasses.php");
  inputclassbase::dbconnect($userlink);
  if(isset($_POST['rrid']))
  { // Need to remove a record.
    mysql_query("DELETE FROM inschrijvingMPC WHERE rid=". $_POST['rrid'], $userlink);
  }
  echo ('<HTML><LINK rel="stylesheet" type="text/css" href="style_InschrijfMB.css" title="style1">');
	
	// Get which years appear in registration
	$insyrsqr = inputclassbase::load_query("SELECT DISTINCT year FROM inschrijvingMPC WHERE year IS NOT NULL ORDER BY year");
	$instypeqr = inputclassbase::load_query("SELECT DISTINCT schooltype FROM inschrijvingMPC WHERE schooltype IS NOT NULL ORDER BY schooltype");
  echo("<H1>Inschrijving Mon Plaisir College </H1>");
	// Show the year filter
	// First handle year and type filter changes
	if(isset($_POST['insMBfiltyear']))
		$_SESSION['insMBfiltyear'] = $_POST['insMBfiltyear'];
	if(!isset($_SESSION['insMBfiltyear']))
		$_SESSION['insMBfiltyear'] = date("Y"). "-". (date("Y") + 1);
	if(isset($_POST['insMBfilttype']))
		$_SESSION['insMBfilttype'] = $_POST['insMBfilttype'];
	if(!isset($_SESSION['insMBfilttype']))
		$_SESSION['insMBfilttype'] = "MAVO";
	// Now make the form for year selection
	echo("<FORM ID=yearfilt METHOD=POST><SELECT ID=insMBfiltyear NAME=insMBfiltyear onChange='document.getElementById(\"yearfilt\").submit();'>");
	foreach($insyrsqr['year'] AS $fyr)
	{
		echo("<OPTION VALUE=". $fyr. ($fyr == $_SESSION['insMBfiltyear'] ? " selected" : ""). ">". $fyr. "</option>");
	}
	echo("</select></form>");
	// Now make the form for type selection
	echo("<FORM ID=typefilt METHOD=POST><SELECT ID=insMBfilttype NAME=insMBfilttype onChange='document.getElementById(\"typefilt\").submit();'><OPTION VALUE=''>-</OPTION>");
	foreach($instypeqr['schooltype'] AS $fyr)
	{
		echo("<OPTION VALUE=". $fyr. ($fyr == $_SESSION['insMBfilttype'] ? " selected" : ""). ">". $fyr. "</option>");
	}
	echo("</select></form>");
	
	if(isset($_POST['mailfield']))
	{
		$mailli = 0;
		$maildataqr = inputclassbase::load_query("SELECT * FROM inschrijvingMPC where rid=". $_POST['mailfield']);
		$maila = $maildataqr['email'][0];
		mailadd($maila);
		$maila = $maildataqr['emailmother'][0];
		mailadd($maila);
		$maila = $maildataqr['emailfather'][0];
		mailadd($maila);
		// See if accepted, not aceepted or acceptation pending
		if($maildataqr['accepted'][0] == "")
		{ // No accept data
			$fname = "wachtende.pdf";			
		}
		else if($maildataqr['accepted'][0] == 0)
		{ // Not accepted
			$fname = "nietgeaccepteerd.pdf";
		}
		else if($maildataqr['accepted'][0] == 1)
		{ // Accepted
			$fname = "geaccepteerd.pdf";			
		}
		$atfqr = inputclassbase::load_query("SELECT data FROM libraryfiles WHERE folder='/acceptatiebrieven' AND filename='". $fname. "'");
		if(isset($atfqr['data'][0]) && isset($mailaddresses))
		{ // Ready to send mail
			SendMailCcAttach($mailaddresses,"Bericht betreffende aanname ". $maildataqr['firstname'][0]. " ". $maildataqr['lastname'][0],"Zie bijlage",null,$atfqr['data'][0],$fname);
		}
		else
		{ // Can not send mail, no address or no content found
			if(!isset($mailaddresses))
				echo("<H1 style='color: red;'>Kan geen e-mail sturen omdat er geen e-mail adres is ingevuld!</H1>");
			else
				echo("<H1 style='color: red;'>Kan geen e-mail sturen omdat het document ". $fname. " niet aanwezig is in de bibliotheek folder acceptatiebrieven!</H1>");			
		}
	}


	
	// Sorting aspects
	if(!isset($_SESSION['insMBsortfield']) || $_SESSION['insMBsortfield']=='')
	{
		$_SESSION['insMBsortfield']='lastname';
		$_SESSION['insMBsortdir'] = '';
	}
	if(isset($_POST['sortfield']))
	{
		// Special case for birthdate:
		if($_POST['sortfield']=='gebjaar,mcode,gebdag' && $_POST['sortfield'] == $_SESSION['insMBsortfield'] && $_SESSION['insMBsortdir'] == '')
		{ // Setup for decending birth date
			$_SESSION['insMBsortfield'] = 'gebjaar DESC, mcode DESC, gebdag';
			$_SESSION['insMBsortdir'] = ' DESC';
		}
		else if($_POST['sortfield'] == $_SESSION['insMBsortfield'])
		{ // already sorting on this field, so change direction
			if($_SESSION['insMBsortdir'] == '')
				$_SESSION['insMBsortdir'] = ' DESC';
			else
				$_SESSION['insMBsortdir'] = '';				
		}
		else
		{ // New field to sort on
			$_SESSION['insMBsortdir'] = '';				
			$_SESSION['insMBsortfield'] = $_POST['sortfield'];							
		}
	}

  if(isset($_GET['listtype']) || isset($_POST['slname']))
  {
		if(isset($_POST['slname']))
		{
      $ridsq = "SELECT rid FROM inschrijvingMPC LEFT JOIN (SELECT 1 AS mcode, 'januari' AS gebmaand UNION SELECT 2,'februari' UNION SELECT 3,'maart' UNION SELECT 4,'april' UNION SELECT 5,'mei' UNION SELECT 6,'juni' UNION SELECT 7,'juli' UNION SELECT 8,'augustus' UNION SELECT 9,'september' UNION SELECT 10,'oktober' UNION SELECT 11,'november' UNION SELECT 12,'december') AS mctab USING(gebmaand) WHERE year='". $_SESSION['insMBfiltyear']. "' AND firstname LIKE '%". $_POST['sfname']. "%'".  ($_POST['slname'] != "" ? " AND lastname LIKE '%". $_POST['slname']. "%'" : ""). ($_POST['scensid'] != "" ? " AND censoid LIKE '%". $_POST['scensid']. "%'" : ""). ($_SESSION['insMBfilttype'] != "" ? " AND schooltype='". $_SESSION['insMBfilttype']. "'" : ""). " ORDER BY ". $_SESSION['insMBsortfield']. $_SESSION['insMBsortdir']. ",lastname,firstname"; 			
		}
    else if($_GET['listtype'] == 'paidlist')
		{
			echo("<H1>Lijst inschrijvers die betaald hebben</H1>");
      $ridsq = "SELECT rid FROM inschrijvingMPC LEFT JOIN (SELECT 1 AS mcode, 'januari' AS gebmaand UNION SELECT 2,'februari' UNION SELECT 3,'maart' UNION SELECT 4,'april' UNION SELECT 5,'mei' UNION SELECT 6,'juni' UNION SELECT 7,'juli' UNION SELECT 8,'augustus' UNION SELECT 9,'september' UNION SELECT 10,'oktober' UNION SELECT 11,'november' UNION SELECT 12,'december') AS mctab USING(gebmaand) WHERE paid=1 AND accepted=1 AND year='". $_SESSION['insMBfiltyear']. "'". ($_SESSION['insMBfilttype'] != "" ? " AND schooltype='". $_SESSION['insMBfilttype']. "'" : ""). " ORDER BY ". $_SESSION['insMBsortfield']. $_SESSION['insMBsortdir']. ",lastname,firstname"; 
		}
		else if($_GET['listtype'] == 'unpaidlist')
		{
			echo("<H1>Lijst geaccepteerde inschrijvers die nog niet betaald hebben</H1>");
      $ridsq = "SELECT rid FROM inschrijvingMPC LEFT JOIN (SELECT 1 AS mcode, 'januari' AS gebmaand UNION SELECT 2,'februari' UNION SELECT 3,'maart' UNION SELECT 4,'april' UNION SELECT 5,'mei' UNION SELECT 6,'juni' UNION SELECT 7,'juli' UNION SELECT 8,'augustus' UNION SELECT 9,'september' UNION SELECT 10,'oktober' UNION SELECT 11,'november' UNION SELECT 12,'december') AS mctab USING(gebmaand) LEFT JOIN (SELECT rid,IF(baptised='ja',10,IF(fbaptised='ja' OR mbaptised='ja',9,IF(workSPCOA='ja',7,IF(brusonschool='ja',5,0)))) AS sortprio FROM inschrijvingMPC) AS priotab USING(rid)  WHERE (paid=0 OR paid IS NULL) AND accepted=1 AND year='". $_SESSION['insMBfiltyear']. "'". ($_SESSION['insMBfilttype'] != "" ? " AND schooltype='". $_SESSION['insMBfilttype']. "'" : ""). " ORDER BY sortprio DESC, ". $_SESSION['insMBsortfield']. $_SESSION['insMBsortdir']. ",lastname,firstname"; 
		}
		else if($_GET['listtype'] == 'pendingplist')
		{
			echo("<H1>Lijst inschrijvers zonder beslissing acceptatie met bewijs ingeleverd</H1>");
      $ridsq = "SELECT rid FROM inschrijvingMPC LEFT JOIN (SELECT 1 AS mcode, 'januari' AS gebmaand UNION SELECT 2,'februari' UNION SELECT 3,'maart' UNION SELECT 4,'april' UNION SELECT 5,'mei' UNION SELECT 6,'juni' UNION SELECT 7,'juli' UNION SELECT 8,'augustus' UNION SELECT 9,'september' UNION SELECT 10,'oktober' UNION SELECT 11,'november' UNION SELECT 12,'december') AS mctab USING(gebmaand) LEFT JOIN (SELECT rid,IF(baptised='ja',10,IF(fbaptised='ja' OR mbaptised='ja',9,IF(workSPCOA='ja',7,IF(brusonschool='ja',5,(IF(exMBstudent='ja',4,0)))))) AS sortprio FROM inschrijvingMPC) AS priotab USING(rid) WHERE accepted IS NULL AND proof=1 AND year='". $_SESSION['insMBfiltyear']. "'". ($_SESSION['insMBfilttype'] != "" ? " AND schooltype='". $_SESSION['insMBfilttype']. "'" : ""). " AND (MAVOniveau IS NULL OR MAVOniveau<>'nee') ORDER BY sortprio DESC, ". $_SESSION['insMBsortfield']. $_SESSION['insMBsortdir']. ",rid"; 
		}
		else if($_GET['listtype'] == 'pendinglist')
		{
			echo("<H1>Lijst inschrijvers zonder beslissing acceptatie</H1>");
      $ridsq = "SELECT rid FROM inschrijvingMPC LEFT JOIN (SELECT 1 AS mcode, 'januari' AS gebmaand UNION SELECT 2,'februari' UNION SELECT 3,'maart' UNION SELECT 4,'april' UNION SELECT 5,'mei' UNION SELECT 6,'juni' UNION SELECT 7,'juli' UNION SELECT 8,'augustus' UNION SELECT 9,'september' UNION SELECT 10,'oktober' UNION SELECT 11,'november' UNION SELECT 12,'december') AS mctab USING(gebmaand) WHERE accepted IS NULL AND (proof <> 1 OR proof IS NULL) AND year='". $_SESSION['insMBfiltyear']. "'". ($_SESSION['insMBfilttype'] != "" ? " AND schooltype='". $_SESSION['insMBfilttype']. "'" : ""). " AND (MAVOniveau IS NULL OR MAVOniveau<>'nee') ORDER BY ". $_SESSION['insMBsortfield']. $_SESSION['insMBsortdir']. ",lastname,firstname"; 
		}
		else if($_GET['listtype'] == 'unacceptedlist')
		{
			echo("<H1>Lijst niet geaccepteerde inschrijvers</H1>");
      $ridsq = "SELECT rid FROM inschrijvingMPC LEFT JOIN (SELECT 1 AS mcode, 'januari' AS gebmaand UNION SELECT 2,'februari' UNION SELECT 3,'maart' UNION SELECT 4,'april' UNION SELECT 5,'mei' UNION SELECT 6,'juni' UNION SELECT 7,'juli' UNION SELECT 8,'augustus' UNION SELECT 9,'september' UNION SELECT 10,'oktober' UNION SELECT 11,'november' UNION SELECT 12,'december') AS mctab USING(gebmaand) WHERE (accepted=0 OR MAVOniveau='nee') AND year='". $_SESSION['insMBfiltyear']. "'". ($_SESSION['insMBfilttype'] != "" ? " AND schooltype='". $_SESSION['insMBfilttype']. "'" : ""). " ORDER BY ". $_SESSION['insMBsortfield']. $_SESSION['insMBsortdir']. ",lastname,firstname"; 
		}
		//echo($ridsq);
		echo("<FORM METHOD=POST ID=sortform><INPUT type=hidden name=sortfield id=sortfield value=''></FORM>");
		echo("<SCRIPT> function setsort(sortfld) { document.getElementById('sortfield').value=sortfld; document.getElementById('sortform').submit(); } </SCRIPT>");
		echo("<FORM METHOD=POST ID=mailform><INPUT type=hidden name=mailfield id=mailfield value=''></FORM>");
		echo("<SCRIPT> function mailtrigger(rid) { document.getElementById('mailfield').value=rid; document.getElementById('mailform').submit(); } </SCRIPT>");
		$ridsqr = inputclassbase::load_query($ridsq); 
		if(isset($ridsqr['rid']))
		{
			echo("<TABLE class=studentlist><TR><TH>#</TH><TH><a href=# onClick=setsort(\"lastname\")>Achternaam</TH><TH><a href=# onClick=setsort(\"firstname\")>Voornamen</TH><TH><a href=# onClick=setsort(\"gebjaar,mcode,gebdag\")>Geb. datum</TH><TH><a href=# onClick=setsort(\"geslacht\")>M/V</a></TH><TH>Jaar</TH><TH>Lln gedoopt</th><TH>Ouder gedoopt</th><TH>Werk SPCOA</th><TH>Brus MP</th><TH>Ouder exMB lln</th><TH>Geaccepteerd</TH><TH>Klas</TH><TH>Betaald</TH><TH><img src='PNG/reply.png'>");
			echo("/<img src='PNG/action_delete.png'></TH><TH><img src='PNG/letter.png'></TH></TR>");
			$seqi = 1;
			foreach($ridsqr['rid'] AS $rid)
			{
				echo("<TR><TD>". $seqi++. "</TD>");
				$fld = new inputclass_textfield("lslname",20,NULL,"lastname","inschrijvingMPC",$rid,"rid",NULL,NULL);
				echo("<TD>". $fld->__toString(). "</TD>");
				$fld = new inputclass_textfield("lsfname",20,NULL,"firstname","inschrijvingMPC",$rid,"rid",NULL,NULL);
				echo("<TD>". $fld->__toString(). "</TD>");
				$fld = new inputclass_textfield("lsgdate1",20,NULL,"gebdag","inschrijvingMPC",$rid,"rid",NULL,NULL);
				echo("<TD>". $fld->__toString());
				$fld = new inputclass_textfield("lsgdate2",20,NULL,"gebmaand","inschrijvingMPC",$rid,"rid",NULL,NULL);
				echo(" ". $fld->__toString());
				$fld = new inputclass_textfield("lsgdate3",20,NULL,"gebjaar","inschrijvingMPC",$rid,"rid",NULL,NULL);
				echo(" ". $fld->__toString(). "</TD>");
				$fld = new inputclass_textfield("lsgender",2,NULL,"geslacht","inschrijvingMPC",$rid,"rid",NULL,NULL);
					echo("<TD>". $fld->__toString(). "</TD>");
				$fld = new inputclass_textfield("lsyear",2,NULL,"year","inschrijvingMPC",$rid,"rid",NULL,NULL);
					echo("<TD>". $fld->__toString(). "</TD>");
				$fld = new inputclass_textfield("lbapt",2,NULL,"baptised","inschrijvingMPC",$rid,"rid",NULL,NULL);
					echo("<TD>". $fld->__toString(). "</TD>");
				$fld1 = new inputclass_textfield("fbapt",2,NULL,"fbaptised","inschrijvingMPC",$rid,"rid",NULL,NULL);
				$fld2 = new inputclass_textfield("mbapt",2,NULL,"mbaptised","inschrijvingMPC",$rid,"rid",NULL,NULL);
					echo("<TD>". $fld1->__toString(). "/". $fld2->__toString(). "</TD>");
				$fld = new inputclass_textfield("wspcoa",2,NULL,"workSPCOA","inschrijvingMPC",$rid,"rid",NULL,NULL);
					echo("<TD>". $fld->__toString(). "</TD>");
				$fld = new inputclass_textfield("brusmp",2,NULL,"brusonschool","inschrijvingMPC",$rid,"rid",NULL,NULL);
					echo("<TD>". $fld->__toString(). "</TD>");
				$fld = new inputclass_textfield("exmbstud",2,NULL,"exMBstudent","inschrijvingMPC",$rid,"rid",NULL,NULL);
					echo("<TD>". $fld->__toString(). "</TD>");
				$fld = new inputclass_textfield("lsaccept",2,NULL,"accepted","inschrijvingMPC",$rid,"rid",NULL,NULL);
					echo("<TD>". ($fld->__toString() == '1' ? "Ja" : "Nee"). "</TD>");
				$fld = new inputclass_listfield("lsclassl","SELECT '' AS id,'' AS tekst UNION SELECT gid,groupname FROM sgroup",NULL,"registeredinclass","inschrijvingMPC",$rid,"rid",NULL,NULL);
				echo("<TD>". $fld->__toString(). "</TD>");
				$fld = new inputclass_textfield("lspaid",20,NULL,"paid","inschrijvingMPC",$rid,"rid",NULL,NULL);
					echo("<TD>". ($fld->__toString() == '1' ? "Ja" : "Nee"). "</TD>");
				// Show function buttons
				echo("<TD><IMG src='PNG/reply.png' onClick='sendrid(". $rid. ");'>");
				echo("/<IMG src='PNG/action_delete.png' onClick='remrid(". $rid. ");'></TD>");
				echo("<TD><IMG src='PNG/letter.png' onClick='mailtrigger(". $rid. ");'></TD></TR>");
			}
			echo("</TABLE>");
			echo("<FORM ACTION=". $_SERVER['REQUEST_URI']. " METHOD=POST NAME=ridsend ID=ridsend>");
			echo("<INPUT TYPE=hidden NAME=rid VALUE=0 ID=rid></FORM>");
			echo("<FORM ACTION=". $_SERVER['REQUEST_URI']. " METHOD=POST NAME=ridrem ID=ridrem>");
			echo("<INPUT TYPE=hidden NAME=rrid VALUE=0 ID=rrid></FORM>");
			echo("<SCRIPT> function sendrid(rid) { document.getElementById('rid').value=rid; document.getElementById('ridsend').submit(); } </SCRIPT>");
			echo("<SCRIPT> function remrid(rid) { document.getElementById('rrid').value=rid; if(confirm('Weet je zeker dat je dit record wilt verijderen?')) document.getElementById('ridrem').submit(); } </SCRIPT>");
		}
		echo("<BR><a href=form_Inschrijven_MPC.php>Terug naar zoeken</a>");
  }
  else
  { // Need to search or present search fields
    // "Virgin" entry, give fields to search students
		echo("Vul de gegevens in om studenten te zoeken");
		echo("<FORM METHOD=POST ACTION='form_Inschrijven_MPC.php'>");
		echo("<LABEL>Achternaam:</LABEL><INPUT TYPE=TEXT SIZE=40 NAME=slname>");
		echo("<BR><LABEL>Voornamen:</LABEL><INPUT TYPE=TEXT SIZE=40 NAME=sfname>");
		echo("<BR><LABEL>Censo ID:</LABEL><INPUT TYPE=TEXT SIZE=40 NAME=scensid>");
		echo("<BR><LABEL>&nbsp;</LABEL><INPUT TYPE=SUBMIT VALUE='ZOEKEN'>");
		echo("</FORM>");
		echo("<BR><a href='form_Inschrijven_MPC.php?listtype=pendinglist'>Lijst 1 : inschrijvers zonder beslissing acceptatie zonder bewijs ingeleverd</a>");
		echo("<BR><a href='form_Inschrijven_MPC.php?listtype=pendingplist'>Lijst 2:  inschrijvers zonder beslissing acceptatie met bewijs ingeleverd</a>");
		echo("<BR><a href='form_Inschrijven_MPC.php?listtype=unpaidlist'>Lijst 3 : geaccepteerde inschrijvers die nog niet betaald hebben</a>");
		echo("<BR><a href='form_Inschrijven_MPC.php?listtype=unacceptedlist'>Lijst 4 : niet geaccepteerde inschrijvers</a>");
		echo("<BR><a href='form_Inschrijven_MPC.php?listtype=paidlist'>Lijst 5 : inschrijvers die betaald hebben</a>");

  }
  echo("</HTML>");  
}
function mailadd($maila)
{
	global $mailaddresses,$mailli;
	$mailas = explode(" ",$maila);
	foreach($mailas AS $mailadr)
	{
		if(strpos($mailadr,"@"))
		{
			$mailadr = str_replace(";","",$mailadr);
			$mailadr = str_replace("/","",$mailadr);
			$mailadr = str_replace("&","",$mailadr);
			$alreadydef = false;
			if(isset($mailaddresses))
				foreach($mailaddresses AS $exmail)
					if($exmail == $mailadr)
						$alreadydef = true;
			if(!$alreadydef)
			{
				$mailaddresses[$mailli++] = $mailadr;
			}
		}
	}
}
  function SendMailCcAttach($to,$subject,$body,$cc,$attach,$attname)
  {
    global $dontmail;
		//$dontmail=true;
    // Create a single string for the "To:" part
    if(is_array($to))
    {
      $mo1 = 0;
      foreach($to AS $tpt)
      {
        if($mo1 > 0)
          $totxt .= ",". $tpt;
        else
          $totxt = $tpt;
        $mo1++;
      }
    }
    else
      $totxt = $to;
    // Create a single string for the "Cc:" part
    if(isset($cc))
    {
      if(is_array($cc))
      {
        $mo1 = 0;
        foreach($cc AS $cpt)
        {
          if($mo1 > 0)
            $cctxt .= ",". $cpt;
          else
            $cctxt = $cpt;
          $mo1++;
        }
      }
      else
        $cctxt = $cc;
    }

    $headers = "From:noreply@myschoolresults.com";
    if(isset($cctxt))
      $headers .= "\nCc:". $cctxt;
    $headers .= "\nX-mailer: PHP";

    $semi_rand = md5(time());
    $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
    
    $headers .= "\nMIME-Version: 1.0\nContent-Type: multipart/mixed;\n boundary=\"{$mime_boundary}\"";
    $mbody = "This is a multi-part message in MIME format.\n\n".
             "--{$mime_boundary}\n".
             "Content-Type: text/plain; charset=\"iso-8859-1\"\n".
             "Content-Transfer-Encoding: 7bit\n\n".
             $body. "\n\n".
             "--{$mime_boundary}\n";
    if(isset($attach))
    {
      $adata = chunk_split(base64_encode($attach));
      $mbody .= "Content-Type: application/pdf;\n".
                " name=\"{$attname}\"\n".
                "Content-Disposition: attachment;\n".
                " filename=\"{$attname}\"\n".
                "Content-Transfer-Encoding: base64\n\n".
                $adata. "\n\n".
                "--{$mime_boundary}--\n";
    }


    if(!isset($dontmail))
    {
      $ok = mail($totxt,$subject,$mbody,$headers,"-fnoreply@myschoolresults.com");
      if(!$ok)
        echo("Failure sending mail!");
    }
    else
    {
      echo("<BR>Should mail: ". $totxt. ". With subject: ". $subject. ".");
      echo("<BR>Header: ". $headers);
      echo("<BR>Body: ". $mbody);
      return;
    }

  }
?>
