<?
require_once("displayelement.php");

class plainelement extends displayelement
{
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
  }
  protected function add_contents()
  {
  }
  public function set_contents($contents)
  {
    $this->contents = $contents;
  }
}
?>
