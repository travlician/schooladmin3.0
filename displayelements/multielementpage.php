<?
require_once("pageelement.php");

class multielementpage extends pageelement
{
  protected $elements;
  protected $elementcount;
  protected function show_contents()
  {
    if($this->elementcount > 0)
	  foreach($this->elements AS $delement)
	    $delement->show();
  }
  protected function add_contents()
  {
    $this->elementcount = 0;
  }
  public function add_element($newelement)
  {
    $this->elements[$this->elementcount++] = $newelement;
  }
}
?>
