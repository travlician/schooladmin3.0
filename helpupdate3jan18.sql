-- MySQL dump 10.15  Distrib 10.0.15-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: fillvs
-- ------------------------------------------------------
-- Server version	10.0.15-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `helpcontent`
--

DROP TABLE IF EXISTS `helpcontent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `helpcontent` (
  `hid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parentid` int(11) unsigned DEFAULT NULL,
  `lang` text COLLATE utf8_unicode_ci,
  `helptitle` text COLLATE utf8_unicode_ci,
  `pageref` text COLLATE utf8_unicode_ci,
  `content` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`hid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `helpcontent`
--

LOCK TABLES `helpcontent` WRITE;
/*!40000 ALTER TABLE `helpcontent` DISABLE KEYS */;
INSERT INTO `helpcontent` VALUES (91,NULL,'nederlands','Berichten configureren',NULL,'<h2>Toegang tot berichtenverkeer</h2>\nOp het beheerschern is een item &quot;Berichtenverkeer toegang instellingen&quot;. Hiermee kan bepaald worden wie berichten mag sturen en naar wie.<br />\nAlleen docenten kunnen berichten sturen (leerlingen en ouders niet).\n<h2>Wie kan versturen</h2>\nDe volgende groepen leraren worden onderscheiden:\n\n<ul>\n	<li>Beheerders</li>\n	<li>Vertrouwenspersonen</li>\n	<li>Leraren met toegang tot afwezigheid registratie</li>\n	<li>Administratie</li>\n	<li>Mentoren</li>\n	<li>Alle leraren</li>\n</ul>\n\n<h2>Naar wie kan je sturen</h2>\nDaarnaast zijn er bestemmingen, daar waar het bericht heen gestuurd kan worden. De volgende bestemmingen zijn beschikbaar:\n\n<ul>\n	<li>Een leraar</li>\n	<li>Alle beheerders</li>\n	<li>Alle vertrouwenspersonen</li>\n	<li>Alle leraren met troegang tot afwezigheid registratie</li>\n	<li>Administratie</li>\n	<li>Alle mentoren</li>\n	<li>Alle leraren van een groep leerlingen</li>\n	<li>Alle leraren</li>\n	<li>Een leerling</li>\n	<li>Een ouder</li>\n	<li>Alle leerlingen van een groep</li>\n	<li>Alle ouders van een groep</li>\n</ul>\n\n<h2>Waar moet je aan denken</h2>\nMet de instellingen kan worden bepaald welke groep leraren berichten mag sturen aan welke bestemmingen.<br />\nOm berichten naar leraren te kunnen sturen moet in ieder geval het sturen aan het bericht van een leraar zijn toegekend.<br />\nOm berichten naar leerlingen en/of ouders te kunnen sturen moet in ieder geval het sturen van en bericht aan een leerlingn zijn toegekend.<br />\nVergeet niet op het vinkje te klikken aan het eind van de regel te klikken als een item gewijzigd is.'),(92,NULL,'nederlands','Berichten verzenden naar leraren','teacherdetails','<h2>Naar wie kun je berichten sturen</h2>\nAfhankelijk van de instellingen die de beheer heeft gedaan kun je berichten sturen aan:\n\n<ul>\n	<li>Een collega docent</li>\n	<li>De beheerders</li>\n	<li>De vertouwenspersonen</li>\n	<li>De administratie</li>\n	<li>De mentoren</li>\n	<li>De leraren die les geven aan een groep leerlingen</li>\n	<li>Alle leraren</li>\n</ul>\n\n<h2>Wat kun je sturen</h2>\nEen bericht bestaat uit een tekst met opmaak. Hierin kunnen ook plaatjes zijn opgenomen.<br />\nDe opmaak is vergelijkbaar met een tekstverwerker.<br />\nJe kunt <em>geen</em> bestanden versturen.\n\n<h2>Waar kun berichten sturen</h2>\nOp de pagina &quot;Details van leraren&quot; staat achter ieder regel een icoon <img alt=\"\" src=\"PNG/comments.png\" />, als daartoe toegang is gegeven.<br />\nDoor te klikken op dit icoon kan een bericht worden verstuurd naar de betreffende leraar of naar een groep leraren.\n<h2>Hoe stuur je een bericht</h2>\nNa het klikken op het icoon verschijn een dialoogscherm met 2 velden, het eerste is een keuzelijst waar gekozen kan worden naar wie het bericht wordt verstuurd, standaard is dat de leraar achter wiens naam het icoon dat gekilkt is staat.<br />\nWelke bestemmingen kunnen worden gekozen hangt af van de rechten die door de beheerder zijn ingesteld.<br />\nHet volgende veld bevat de berichttekst.<br />\nDeze tekst kan worden opgemaakt met de beschikbare hulpmiddelen die bovenaan het veld worden gegeven.<br />\nHet is ook mogelijk het bericht op te maken in een tekstverwerker (bijvoorbeeld Word) en dan in het berichtveld te kopieren.<br />\nAls het bericht klaar is, klik dan op &quot;Stuur bericht&quot;.'),(93,NULL,'nederlands','Berichten sturen aan leerlingen en ouders','Studentdetails','<h2>Naar wie kun je berichten sturen</h2>\nAfhankelijk van de instellingen die de beheer heeft gedaan kun je berichten sturen aan:\n\n<ul>\n	<li>Een leerling</li>\n	<li>De ouder(s) van een leerling</li>\n	<li>Een leerling en de ouder(s)</li>\n	<li>Alle leerlingen in een klas/cluster</li>\n	<li>Alle ouders in een klas/cluster</li>\n	<li>Alle leerlingen en ouders in een klas/cluster</li>\n</ul>\n\n<h2>Wat kun je sturen</h2>\nEen bericht bestaat uit een tekst met opmaak. Hierin kunnen ook plaatjes zijn opgenomen.<br />\nDe opmaak is vergelijkbaar met een tekstverwerker.<br />\nJe kunt <em>geen</em> bestanden versturen.\n\n<h2>Waar kun berichten sturen</h2>\nOp de pagina &quot;Details van leerlingen&quot; staat achter ieder regel een icoon <img alt=\"\" src=\"PNG/comments.png\" />, als daartoe toegang is gegeven.<br />\nDoor te klikken op dit icoon kan een bericht worden verstuurd naar de betreffende leerling en/of ouder(s) en naar een groep leerlingen en/of ouders.\n<h2>Hoe stuur je een bericht</h2>\nNa het klikken op het icoon verschijn een dialoogscherm met 2 velden, het eerste is een keuzelijst waar gekozen kan worden naar wie het bericht wordt verstuurd, standaard is dat de leerling achter wiens naam het icoon dat gekilkt is staat.<br />\nWelke bestemmingen kunnen worden gekozen hangt af van de rechten die door de beheerder zijn ingesteld.<br />\nHet volgende veld bevat de berichttekst.<br />\nDeze tekst kan worden opgemaakt met de beschikbare hulpmiddelen die bovenaan het veld worden gegeven.<br />\nHet is ook mogelijk het bericht op te maken in een tekstverwerker (bijvoorbeeld Word) en dan in het berichtveld te kopieren.<br />\nAls het bericht klaar is, klik dan op &quot;Stuur bericht&quot;.'),(94,1,'nederlands','Instellen automatische e-mail berichten aan leerlingen of ouders',NULL,'<h2>Automatische e-mail berichten</h2>\nBij handelingen als het toevoegen van cijfers, rapportages of afwezigheidsgegevens kan automatisch een e-mail bericht worden verstuurd aan de leerling en/of ouders.\n\n<h2>Wat staat er in het bericht</h2>\n\n<ul>\n	<li>Een te configureren tekst afhankelijk van het soort wijziging</li>\n	<li>Een link naar het LVS met een te configureren tekst afhankelijk van het soort wijziging, daarmee wordt automatisch ingelogd op het LVS.</li>\n</ul>\n\n<h2>Waar te configuren</h2>\nHet formulier &quot;Email_config&quot; geeft de configuratie mogelijkheid.\n\n<h2>Hoe te configuren</h2>\nNadat het formulier is gekozen verschijnt een dialoogscherm als hieronder:<br />\n<img alt=\"\" src=\"https://myschoolresults.com/emailconfig.png\" style=\"width: 50%; border-width: 2px; border-style: solid;\" /><br />\nOp de eerste regel wordt een aantal keuzelijsten getoond met daarin de keuze uit welk veld het e-mail adres moet worden gehaald.<br />\nHet is mogelijk het bericht naar meerdere e-mail adressen te sturen door bij meerder keuzelijsten een veldnaam te kiezen.<br />\nDe veldnamen die worden getoond zijn die waarin een e-mail adres voorkomt.<br />\nVul verder in het dialoogscherm in bij welke veranderingen een bericht moet worden gestuurd en welke teksten daarbij in het bericht moeten voorkomen.');
/*!40000 ALTER TABLE `helpcontent` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-01-02 21:08:08
