<?
require_once("displayelement.php");

class extendableelement extends displayelement
{
  protected $elements;
  protected $elementcount;
  protected $contents;
  public function __construct($divid = NULL, $style = NULL, $contents = NULL)
  {
	if(isset($contents))
      $this->contents = $contents;
	parent::__construct($divid, $style);
  }
  protected function show_contents()
  {
    echo($this->contents);
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
  public function set_contents($contents)
  {
    $this->contents = $contents;
  }
}
?>
