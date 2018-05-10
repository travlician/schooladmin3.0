<?
require_once("inputclass_textfield.php");
class inputclass_password extends inputclass_textfield
{
  public function echo_html()
  {
    inputclassbase::echo_html();
    $val= $this->__toString();
    echo("<INPUT TYPE=PASSWORD NAME=\"". $this->fieldid. "\" ID=\"". $this->fieldid. "\" VALUE=\"". htmlspecialchars($val). "\"");
	if($this->size != NULL)
	  echo(" SIZE=". $this->size);
	echo($this->styledata());
	if($this->dbfield != NULL || $this->handlerpage != NULL)
	  echo(" onChange='return(send_xml(\"". $this->fieldid. "\",this));'");
    echo(">");
  }
}
?>