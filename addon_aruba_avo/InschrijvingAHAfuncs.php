<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Schooladmin -- Version 2.1                                           |
// +----------------------------------------------------------------------+
// | Copyright (C) 2004-2011 Aim4me N.V.   (http://www.aim4me.com)	      |
// +----------------------------------------------------------------------+
// | Authors: Wilfred van Weert - travlician@bigfoot.com                  |
// +----------------------------------------------------------------------+
//
//
function showfee($rid, $showcontrols = true)
{
  $amount = calcfee($rid);
  $exqr = inputclassbase::load_query("SELECT leerjaar FROM inschrijvingAHA WHERE rid=". $rid. " AND (leerjaar LIKE '3%' OR leerjaar LIKE '%V%')");
  if($showcontrols)
    echo("<ADMINFEE>{");
  echo("<B>Op basis van de administratiekosten, examengeld, gekozen vakken, vrijstellingen en certificaten is het te betalen bedrag Afl. ". $amount. ",-");
  echo("</b>");
  if($showcontrols)
    echo("}");
}
function calcfee($rid)
{
  $scq = "SELECT SUM(IF(inschrijvingVrijst.mid IS NULL AND inschrijvingCerts.mid IS NULL,1,0)) AS sc";
  $scq .= " FROM inschrijvingPakket LEFT JOIN inschrijvingVrijst USING(rid,mid) LEFT JOIN inschrijvingCerts USING(rid,mid)";
  $scq .= " LEFT JOIN subject USING(mid)";
  $scq .= " WHERE rid=". $rid. " AND shortname <> 'I&S' AND shortname <> 'Pfw' AND shortname <> 'Re' GROUP BY rid";
  $scqr = inputclassbase::load_query($scq);
  $exqr = inputclassbase::load_query("SELECT leerjaar FROM inschrijvingAHA WHERE rid=". $rid. " AND (leerjaar LIKE '3%' OR leerjaar LIKE '%V%')");
  if(isset($scqr['sc'][0]))
  {
    if(isset($exqr['leerjaar']))
	  return ((2 + $scqr['sc'][0]) * 50);
	else
	  return ((1 + $scqr['sc'][0]) * 50);
  }
  else
    return 50;
}
?>
