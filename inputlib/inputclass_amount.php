<?
require_once("inputclass_textfield.php");
class inputclass_amount extends inputclass_textfield
{
  public function __toString()
  {

    if($this->dbkey > 0)
    {
      $getval = $this->load_query("SELECT `". $this->dbfield. "`,`". $this->dbkeyfield. "` FROM ". $this->dbtable. " WHERE `". $this->dbkeyfield. "`=". $this->dbkey. (isset($this->extrakey) ? " AND `". $this->extrakeyfield. "`=". $this->extrakey : ""));
      if(isset($getval))
        if(isset($getval[$this->dbfield][0]))
	  return(number_format($getval[$this->dbfield][0],2,'.',''));
	else
	  return("");
      else
	return(NULL);
    }
    else
    {
      if(isset($this->initial_value))
        return($this->initial_value);
      else
        return("0.00");
    }
  }
  public function echo_html()
  {
	require_once("amountlimited.js");
    inputclassbase::echo_html();
    $val= $this->__toString();
    echo("<INPUT TYPE=TEXT NAME=\"". $this->fieldid. "\" VALUE=\"". htmlspecialchars($val). "\"");
	if($this->size != NULL)
	  echo(" SIZE=". $this->size);
	echo($this->styledata());
	if($this->dbfield != NULL || $this->handlerpage != NULL)
	  echo(" onChange='return(send_xml(\"". $this->fieldid. "\",this));'");
	echo(" onKeyPress=\"return amountlimited(this, event);\"");
    echo(">");
  }
}
?>