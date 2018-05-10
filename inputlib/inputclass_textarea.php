<?
require_once("inputclass_textfield.php");
class inputclass_textarea extends inputclass_textfield
{
  public function echo_html()
  {
    inputclassbase::echo_html();
    $val= $this->__toString();
    echo("<TEXTAREA NAME=\"". $this->fieldid. "\"");
	if($this->size != NULL)
	{
      $colrow = explode(",",$this->size);
	  echo(" COLS=". $colrow[0]);
	  if(isset($colrow[1]))
	  {
	    if($colrow[1] == "*")
		  echo(" ROWS=1 onKeyup=\"sz(this);\" onClick=\"sz(this);\"");
		else
		  echo(" ROWS=". $colrow[1]);
	  }
	}
	echo($this->styledata());
	if($this->dbfield != NULL || $this->handlerpage != NULL)
	  echo(" onChange='return(send_xml(\"". $this->fieldid. "\",this));'");
	echo(">". $val. "</TEXTAREA>");
	// See if auto resize script is needed
	if(isset($colrow[1]) && $colrow[1] == "*")
	  require_once("textareaautosize.js");
  }
}
?>