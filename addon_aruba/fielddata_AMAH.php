<?
// Definitions for all fields...
// First the choice lists (if too large to include directly)
$days = "SELECT '1' AS id, '1' AS tekst";
for($d=2;$d<=31;$d++)
  $days .= " UNION SELECT '". $d. "','". $d. "'";
$monthlist = "SELECT 1 AS id,'jan' AS tekst UNION SELECT 2,'feb' UNION SELECT 3,'mrt' UNION SELECT 4,'apr' UNION SELECT 5,'mei' UNION SELECT 6,'jun' UNION SELECT 7,'jul' UNION SELECT 8,'aug' UNION SELECT 9,'sep' UNION SELECT 10,'okt' UNION SELECT 11,'nov' UNION SELECT 12,'dec'";
$years = "SELECT '1968' AS id, '1968' AS tekst";
for($d=1969;$d<=2004;$d++)
  $years .= " UNION SELECT '". $d. "','". $d. "'";
$cstates = "SELECT '' AS id, '' AS tekst UNION SELECT 'Gehuwd','Gehuwd' UNION SELECT 'Ongehuwd','Ongehuwd' UNION SELECT 'Gescheiden','Gescheiden' UNION SELECT 'Weduwe','Weduw(e)(naar)'";
$janee = "SELECT '' AS id, '' AS tekst UNION SELECT '0','Nee' UNION SELECT '1','Ja'";
$pakketA_J = "SELECT '' AS id,'' AS tekst UNION SELECT 'A','A' UNION SELECT 'B','B' UNION SELECT 'C','C' UNION SELECT 'D','D' UNION SELECT 'E','E' UNION SELECT 'F','F' UNION SELECT 'G','G' UNION SELECT 'H','H' UNION SELECT 'I','I' UNION SELECT 'J','J'";
$pakketH = "SELECT '' AS id, '' AS tekst UNION SELECT 'HU','HU' UNION SELECT 'MM','MM' UNION SELECT 'NT','NT'";
$pakketAH = "SELECT '' AS id, '' AS tekst UNION SELECT 'MM01','MM01' UNION SELECT 'MM02','MM02' UNION SELECT 'MM03','MM03 UNION SELECT 'MM04','MM04' UNION SELECT 'MM05','MM05'
             UNION SELECT 'MM06','MM06' UNION SELECT 'MM07','MM07' UNION SELECT 'MM08','MM08' UNION SELECT 'MM09','MM09' UNION SELECT 'MM10','MM10'
             UNION SELECT 'HU11','HU11' UNION SELECT 'HU12','HU12' UNION SELECT 'NW13','NW13' UNION SELECT 'NW14','NW14'";
$vrijstellingen = "SELECT '' AS id,'' AS tekst UNION SELECT 'NE','NE' UNION SELECT 'EN','EN' UNION SELECT 'SP','SP' UNION SELECT 'PA','PA' UNION SELECT 'WI-A','WI-A'
                   UNION SELECT 'EC','EC' UNION SELECT 'NA','NA' UNION SELECT 'SK','SK' UNION SELECT 'BIO','BIO' UNION SELECT 'AK','AK' UNION SELECT 'GS','GS'
				   UNION SELECT 'IK','IK'";
$vakkenkeuze = "SELECT '' AS id,'' AS tekst UNION SELECT 'NE','NE' UNION SELECT 'EN','EN' UNION SELECT 'SP','SP' UNION SELECT 'PA','PA' UNION SELECT 'WI-A','WI-A'
                   UNION SELECT 'EC','EC' UNION SELECT 'BIO','BIO' UNION SELECT 'AK','AK' UNION SELECT 'GS','GS'";

$fielddata["Personalia"] =
               array("Identiteitsnummer leerling / Number di cedula alumno" => array("stylesuffix"=>"ID","fname"=>"IdenNr","ftype"=>"textfield","db"=>"nieuwe_registratie","fpar"=>"11"),
			         "Achternaam student / Fam studiante" => array("fname"=>"Lastname","ftype"=>"textfield","db"=>"nieuwe_registratie","fpar"=>"40"),
			         "Voorna(a)m(en) <i>(voluit)</i> / Nomber(nan) completo" => array("fname"=>"Firstname","ftype"=>"textfield","db"=>"nieuwe_registratie","fpar"=>"60"),
			         "Geslacht / Sexo" => array("fname"=>"Mankind","ftype"=>"listfield","db"=>"nieuwe_registratie","fpar"=>"SELECT '' AS id,'' AS tekst UNION SELECT 'm','man / mascullino' UNION SELECT 'v','vrouw / femenino'"),
			         "Geboortedatum / Fecha di nacemento" => array("fname"=>"Bday","prefix"=>"Dag / Dia","ftype"=>"listfield","db"=>"nieuwe_registratie","fpar"=>$days,"noend"=>true),
			         "*bm" => array("fname"=>"Bmonth","prefix"=>"Maand / Luna","ftype"=>"listfield","db"=>"nieuwe_registratie","fpar"=>$monthlist,"noend"=>true),
			         "*by" => array("fname"=>"Byear","prefix"=>"Jaar / A&ntilde;a","ftype"=>"listfield","db"=>"nieuwe_registratie","fpar"=>$years),
			         "Geboorteland / Pais di nacemento" => array("fname"=>"BirthCountry","ftype"=>"listfield","db"=>"nieuwe_registratie","fpar"=>"SELECT '' AS id,'' AS tekst UNION SELECT id,tekst FROM landencodes ORDER BY tekst"),
			         "Nationaliteit / Nacionalidad" => array("fname"=>"Nationality","ftype"=>"textfield","db"=>"nieuwe_registratie","fpar"=>"40"),
			         "Burgelijke staat student / Estado civil studiante" => array("fname"=>"EstCivil","ftype"=>"listfield","db"=>"nieuwe_registratie","fpar"=>$cstates),
			         "*tr1" => array("special"=>"TussenRegel"),
			         "Adres" => array("fname"=>"Address","ftype"=>"textfield","db"=>"nieuwe_registratie","fpar"=>"40"),
			         "Telefoon student thuis / Telefon studiante na cas" => array("fname"=>"PhoneHome","ftype"=>"textfield","db"=>"nieuwe_registratie","fpar"=>"7"),
			         "Mobiel student / cellular di e studiante" => array("fname"=>"MobilePhone","ftype"=>"textfield","db"=>"nieuwe_registratie","fpar"=>"7"),
			         "eMail van de student / eMail di e studiante" => array("fname"=>"EmailAddress","ftype"=>"textfield","db"=>"nieuwe_registratie","fpar"=>"40"));
$fielddata["Ingeval van nood waarschuwen"] =
			   array("Naam / Nomber" => array("stylesuffix"=>"ID","fname"=>"VerentwPersoon","ftype"=>"textfield","db"=>"nieuwe_registratie","fpar"=>"60"),
			         "Telefoon indien noodgeval / Telefoon di emergencia" => array("fname"=>"EmergPhoneNr","ftype"=>"textfield","db"=>"nieuwe_registratie","fpar"=>"7"),
			         "Mobiel / Cellular" => array("fname"=>"MobilePhoneRespPers","ftype"=>"textfield","db"=>"nieuwe_registratie","fpar"=>"40"),
			         "Adres" => array("fname"=>"AddressPersResp","ftype"=>"textfield","db"=>"nieuwe_registratie","fpar"=>"40"));
$fielddata["Informatie over vooropleiding"]=
			   array("Laatst bezochte school" => array("fname"=>"LastSchool","ftype"=>"textfield","db"=>"nieuwe_registratie","fpar"=>"60"),
			         "Diploma gehaald" => array("fname"=>"GainDiploma","ftype"=>"listfield","db"=>"nieuwe_registratie","fpar"=>$janee,"suffix"=>" <i> Indien 'Ja', diploma en cijferlijst meenemen.</i>"));
$fielddata["Informatie over werk & werkgever indien van toepassing"]=
			   array("Werkzaam" => array("fname"=>"Employed","ftype"=>"listfield","db"=>"nieuwe_registratie","fpar"=>$janee,"suffix"=>"&nbsp;&nbsp;&nbsp;<i>Indien nee, ga verder met Instroom-Informatie</i>"),
			         "Bedrijf of Werkplaats" => array("fname"=>"Compagny","ftype"=>"textfield","db"=>"nieuwe_registratie","fpar"=>"60"),
			         "Adres werkgever" => array("fname"=>"AddressCompagny","ftype"=>"textfield","db"=>"nieuwe_registratie","fpar"=>"40"),
			         "Werktijden" => array("fname"=>"WorktimeFrom","ftype"=>"textfield","db"=>"nieuwe_registratie","fpar"=>"10","prefix"=>"Van","noend"=>true),
			         "*wt" => array("fname"=>"WorktimeTill","ftype"=>"textfield","db"=>"nieuwe_registratie","fpar"=>"10","prefix"=>"Tot"),
			         "Telefoon op het werk" => array("fname"=>"WorkPhoneNr","ftype"=>"textfield","db"=>"nieuwe_registratie","fpar"=>"7"));
if($schoolname == "Openbare Avondleergangen Aruba UNIT AVO/mavo")
  $fielddata["Instroom-informatie"] =
			   array("Ik kies voor de Instaptoets" => array("fname"=>"InstapToets","ftype"=>"listfield","db"=>"nieuwe_registratie","fpar"=>$janee),
			         "Ik kies voor de Schakelklas" => array("fname"=>"SchakelKlas","ftype"=>"listfield","db"=>"nieuwe_registratie","fpar"=>$janee),
			         "Ik kies voor" => array("fname"=>"AMKlas","ftype"=>"listfield","db"=>"nieuwe_registratie","fpar"=>"SELECT '' AS id,'' AS tekst UNION SELECT '2','Klas 2' UNION SELECT '3','Klas 3'","noend"=>true),
			         "*p2" => array("fname"=>"ProfielAMKlas","ftype"=>"listfield","db"=>"nieuwe_registratie","fpar"=>$pakketA_J,"prefix"=>"&nbsp;Pakket:"),
			         "Ik kies voor Klas 4" => array("fname"=>"AMKlas4","ftype"=>"listfield","db"=>"nieuwe_registratie","fpar"=>$janee,"noend"=>true,"stylesuffix"=>"Klas4nb"),
			         "*p4" => array("fname"=>"PakketAMKlas4","ftype"=>"listfield","db"=>"nieuwe_registratie","fpar"=>$pakketA_J,"prefix"=>"&nbsp;Pakket:"),
			         "Vrijstellingen" => array("stylesuffix"=>"Klas4nb","fname"=>"Vrijstelling1","ftype"=>"listfield","db"=>"nieuwe_registratie","fpar"=>$vrijstellingen,"noend"=>true),
			         "*v2" => array("fname"=>"Vrijstelling2","ftype"=>"listfield","db"=>"nieuwe_registratie","fpar"=>$vrijstellingen,"noend"=>true),
			         "*v3" => array("fname"=>"Vrijstelling3","ftype"=>"listfield","db"=>"nieuwe_registratie","fpar"=>$vrijstellingen,"noend"=>true),
			         "*v4" => array("fname"=>"Vrijstelling4","ftype"=>"listfield","db"=>"nieuwe_registratie","fpar"=>$vrijstellingen,"noend"=>true),
			         "*v5" => array("fname"=>"Vrijstelling5","ftype"=>"listfield","db"=>"nieuwe_registratie","fpar"=>$vrijstellingen,"noend"=>true),
			         "Ik volg het (de) vak(ken)" => array("stylesuffix"=>"Klas4nb","fname"=>"VolgtVak1","ftype"=>"listfield","db"=>"nieuwe_registratie","fpar"=>$vakkenkeuze,"noend"=>true),
			         "*vv2" => array("fname"=>"VolgtVak2","ftype"=>"listfield","db"=>"nieuwe_registratie","fpar"=>$vakkenkeuze,"noend"=>true),
			         "*vv3" => array("fname"=>"VolgtVak3","ftype"=>"listfield","db"=>"nieuwe_registratie","fpar"=>$vakkenkeuze,"noend"=>true),
			         "*vv4" => array("fname"=>"VolgtVak4","ftype"=>"listfield","db"=>"nieuwe_registratie","fpar"=>$vakkenkeuze,"noend"=>true),
			         "*vv5" => array("fname"=>"VolgtVak5","ftype"=>"listfield","db"=>"nieuwe_registratie","fpar"=>$vakkenkeuze,"noend"=>true));
if($schoolname == "Openbare Avondleergangen Aruba UNIT AVO/havo")
  $fielddata["Instroom-informatie"] =
			   array("Voor Klas 1" => array("fname"=>"AHKlas1","ftype"=>"listfield","db"=>"nieuwe_registratie","fpar"=>$janee),
			         "*p1" => array("fname"=>"ProfielAHKlas1","ftype"=>"listfield","db"=>"nieuwe_registratie","fpar"=>$pakketH,"prefix"=>"&nbsp;Profiel:"),
			         "Voor Klas 2" => array("fname"=>"AHKlas2","ftype"=>"listfield","db"=>"nieuwe_registratie","fpar"=>$janee),
			         "*p2" => array("fname"=>"ProfielAHKlas2","ftype"=>"listfield","db"=>"nieuwe_registratie","fpar"=>$pakketH,"prefix"=>"&nbsp;Profiel:"),
			         "Voor Klas 3" => array("fname"=>"AHKlas3","ftype"=>"listfield","db"=>"nieuwe_registratie","fpar"=>$janee),
			         "*p3" => array("fname"=>"ProfielAHKlas3","ftype"=>"listfield","db"=>"nieuwe_registratie","fpar"=>$pakketH,"prefix"=>"&nbsp;Profiel:"));
if($schoolname == "Avondhavo Aruba")
  $fielddata["Instroom-informatie"] =
			   array("Registratie voor" => array("fname"=>"AHKlas","ftype"=>"listfield","db"=>"nieuwe_registratie","fpar"=>"SELECT '' AS id, '' AS tekst UNION SELECT '1','Klas 1' UNION SELECT '2','Klas 2' UNION SELECT '3','Klas 3'"),
			         "*p" => array("fname"=>"ProfielAH","ftype"=>"listfield","db"=>"nieuwe_registratie","fpar"=>$pakketAH,"prefix"=>"&nbsp;Profiel:"));
if($schoolname == "Openbare Avondleergangen Aruba UNIT AVO/mavo")
  $fielddata["Documenten"] =
			   array("Bij inschrijving meenemen" => array("fname"=>"DocPaid","ftype"=>"checkmark","db"=>"nieuwe_inschrijving","fpar"=>"0",
			          "suffix"=>"<i>&nbsp;&nbsp;Ricibo&nbsp;di&nbsp;pago&nbsp;di inschrijfgeldgeld / re&ccedil;u overleggen</i><br>
					<center>Te betalen op rekening van:<br>
					<del>AVONDHAVO/AVONDVWO<br>SHAKESPEARSTRAAT 17<br><b>CMB 617.168.00</del></b>","rowspan"=>6),
			        "*d2" => array("fname"=>"DocExtract","ftype"=>"checkmark","db"=>"nieuwe_inschrijving","fpar"=>"0",
			          "suffix"=>"<i>Uittreksel bevolkingsregister</i>"),
			        "*d3" => array("fname"=>"DocPortfolio","ftype"=>"checkmark","db"=>"nieuwe_inschrijving","fpar"=>"0",
			          "suffix"=>"<i>Document profielwerkstuk</i>"),
			        "*d4" => array("fname"=>"DocIS","ftype"=>"checkmark","db"=>"nieuwe_inschrijving","fpar"=>"0",
			          "suffix"=>"<i>Document I&S</i>"),
			        "*d5" => array("fname"=>"DocDiploma","ftype"=>"checkmark","db"=>"nieuwe_inschrijving","fpar"=>"0",
			          "suffix"=>"<i>Diploma(s) / Cerfifica(a)t(en)</i>"),
			        "*d6" => array("fname"=>"DocResults","ftype"=>"checkmark","db"=>"nieuwe_inschrijving","fpar"=>"0",
			          "suffix"=>"<i>Cijferlijst(en)</i>"));
if($schoolname == "Openbare Avondleergangen Aruba UNIT AVO/havo")
  $fielddata["Documenten"] =
			   array("Bij inschrijving meenemen" => array("fname"=>"DocPaid","ftype"=>"checkmark","db"=>"nieuwe_inschrijving","fpar"=>"0",
			          "suffix"=>"<i>&nbsp;&nbsp;Ricibo&nbsp;di&nbsp;pago&nbsp;di inschrijfgeldgeld / re&ccedil;u overleggen</i><br>
					<center>Te betalen op rekening van:<br>
					<del>AVONDHAVO/AVONDVWO<br>SHAKESPEARSTRAAT 17<br><b>CMB 617.168.00</del></b>","rowspan"=>6),
			        "*d2" => array("fname"=>"DocExtract","ftype"=>"checkmark","db"=>"nieuwe_inschrijving","fpar"=>"0",
			          "suffix"=>"<i>Uittreksel bevolkingsregister</i>"),
			        "*d3" => array("fname"=>"DocPortfolio","ftype"=>"checkmark","db"=>"nieuwe_inschrijving","fpar"=>"0",
			          "suffix"=>"<i>Document profielwerkstuk</i>"),
			        "*d4" => array("fname"=>"DocIS","ftype"=>"checkmark","db"=>"nieuwe_inschrijving","fpar"=>"0",
			          "suffix"=>"<i>Document I&S</i>"),
			        "*d5" => array("fname"=>"DocDiploma","ftype"=>"checkmark","db"=>"nieuwe_inschrijving","fpar"=>"0",
			          "suffix"=>"<i>Diploma(s) / Cerfifica(a)t(en)</i>"),
			        "*d6" => array("fname"=>"DocResults","ftype"=>"checkmark","db"=>"nieuwe_inschrijving","fpar"=>"0",
			          "suffix"=>"<i>Cijferlijst(en)</i>"));
if($schoolname == "Avondhavo Aruba")
  $fielddata["Documenten"] =
			   array("Bij inschrijving meenemen" => array("fname"=>"DocPaid","ftype"=>"checkmark","db"=>"nieuwe_inschrijving","fpar"=>"0",
			          "suffix"=>"<i>&nbsp;&nbsp;Ricibo&nbsp;di&nbsp;pago&nbsp;di inschrijfgeldgeld / re&ccedil;u overleggen</i><br>
					<center>Te betalen op rekening van:<br>
					AVONDHAVO/AVONDVWO<br>SHAKESPEARSTRAAT 17<br><b>CMB 617.168.00</b>","rowspan"=>6),
			        "*d2" => array("fname"=>"DocExtract","ftype"=>"checkmark","db"=>"nieuwe_inschrijving","fpar"=>"0",
			          "suffix"=>"<i>Uittreksel bevolkingsregister</i>"),
			        "*d3" => array("fname"=>"DocPortfolio","ftype"=>"checkmark","db"=>"nieuwe_inschrijving","fpar"=>"0",
			          "suffix"=>"<i>Document profielwerkstuk</i>"),
			        "*d4" => array("fname"=>"DocIS","ftype"=>"checkmark","db"=>"nieuwe_inschrijving","fpar"=>"0",
			          "suffix"=>"<i>Document I&S</i>"),
			        "*d5" => array("fname"=>"DocDiploma","ftype"=>"checkmark","db"=>"nieuwe_inschrijving","fpar"=>"0",
			          "suffix"=>"<i>Diploma(s) / Cerfifica(a)t(en)</i>"),
			        "*d6" => array("fname"=>"DocResults","ftype"=>"checkmark","db"=>"nieuwe_inschrijving","fpar"=>"0",
			          "suffix"=>"<i>Cijferlijst(en)</i>"));
//			         "" => array("fname"=>"","ftype"=>"textfield","db"=>"nieuwe_registratie","fpar"=>"40"),
?>
