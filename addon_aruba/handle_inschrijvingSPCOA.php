<?
 session_start();

 // $login_qualify = 'ACT';
  include ("schooladminfunctions.php");
  include ("schooladminconstants.php");

  echo ('<LINK rel="stylesheet" type="text/css" href="style_Inschrijf.css" title="style1">');
  if(!isset($_POST['tablename']))
    exit;
  if(!isset($_POST['IdenNr']))
    exit;
  // Checks on ID nr
  // first strip spaces and dots
  $_POST['IdenNr'] = str_replace(".","",$_POST['IdenNr']);
  $_POST['IdenNr'] = str_replace(" ","",$_POST['IdenNr']);
  if(strlen($_POST['IdenNr']) != 8 || substr($_POST['IdenNr'],2,2) < 1 || substr($_POST['IdenNr'],2,2) > 12 || substr($_POST['IdenNr'],4,2) < 1 || substr($_POST['IdenNr'],4,2) > 31)
  {
    echo("<p class=FoutMelding>Cedulanummer is niet correct, ga <a href=RegistratieformulierSPCOA.php> <font color=blue><u>terug naar het inschrijfformulier</u></font> </a>en vul alles opnieuw in!</p>");
	exit;
  }
  
  // If birthdate is left as default, change it to blanks
  if(isset($_POST['Bday']) && $_POST['Bday'] == 1 && isset($_POST['Bmonth']) && $_POST['Bmonth'] == 1 && isset($_POST['Byear']) && $_POST['Byear'] == '1997')
  {
    $_POST['Bday'] = '';
	$_POST['Bmonth'] = '';
	$_POST['Byear'] = '';
  }
	
  $existstable = SA_loadquery("SHOW TABLES LIKE '". $_POST['tablename']. "'");
  if(!isset($existstable))
  {
	$createq = "CREATE TABLE `". $_POST['tablename']. "` (`regid` INTEGER(11) NOT NULL AUTO_INCREMENT UNIQUE,";
    foreach($_POST AS $key => $value)
    {
      if($key != "tablename")
	    $createq .= "`". $key. "` TEXT, ";
    }
    $createq .= "PRIMARY KEY (`regid`) ) ENGINE=InnoDB CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'";
	mysql_query($createq);
	echo(mysql_error());
  }
  
  // See if a record already exists with the cedulanumber
  $existscedula = SA_loadquery("SELECT * FROM `". $_POST['tablename']. "` WHERE IdenNr='". $_POST['IdenNr']. "'");
  if(isset($existscedula))
  {
    if(isset($_POST['Firstname']) && isset($existscedula['Firstname']) && $_POST['Firstname'] == $existscedula['Firstname'][1] &&
	   isset($_POST['Lastname']) && isset($existscedula['Lastname']) && $_POST['Lastname'] == $existscedula['Lastname'][1])
	{ // Same person, update data that is not blank
	  $updq = "UPDATE `". $_POST['tablename']. "` SET ";
	  $dataq = "";
	  foreach($_POST AS $key => $value)
	  {
	    if($key != "tablename")
		{
		  if($value != "")
		  {
		    $dataq .= "`". $key. "`=\"". str_replace("\"","'",$value). "\",";
		  }
		}
	  }
	  $updq .= substr($dataq,0,-1); // Strip extra comma
	  $updq .= " WHERE IdenNr='". $_POST['IdenNr']. "'";
	  mysql_query($updq);
	  if(mysql_error())
	  {
	    echo(mysql_error(). "<BR>". $updq. "<BR>");
		exit;
	  }
	}
	else
      echo("<p class=FoutMelding>Cedulanummer is al gebruikt, ga <a href=RegistratieformulierSPCOA.php> <font color=blue><u>terug naar het inschrijfformulier</u></font> </a>en vul alles opnieuw in!</p>
	        <p>Het is ook mogelijk het cedulanummer, achternaam en voornaam hetzelfde in te vullen als voorheen en dan alleen de aanvullende of te wijzigen informatie in te vullen.</p>");
	  exit;
  }
  else
  { // Add a new record.
    $newrecq = "INSERT INTO `". $_POST['tablename']. "` (";
	$fieldnames = "";
	foreach($_POST AS $key => $value)
	  if($key != "tablename")
	    $fieldnames .= "`". $key. "`,";
	$newrecq .= substr($fieldnames,0,-1). ") VALUES(";
	$values = "";
	foreach($_POST AS $key => $value)
	  if($key != "tablename")
	    $values .= "\"". str_replace("\"","'",$value). "\",";
	$newrecq .= substr($values,0,-1). ")";
	mysql_query($newrecq);
	if(mysql_error())
	{
	  echo(mysql_error(). "<BR>". $newrecq. "<BR>");
	  exit;
	}
  }
  echo("<p class=Koptekst>Dank u voor het invullen van de gegevens, deze zijn nu bij de school geregistreerd.</p>");
  echo("<p class=Koptekst>Danki pa yena e datonan, awor nan ta registra na e scol.</p>");
