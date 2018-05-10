<?
require_once("pageelement.php");

class plainpage extends pageelement
{
  protected $contents;
  public function __construct($contents, $title = NULL)
  {
    if(isset($contents))
	  $this->contents = $contents;
    parent::__construct(NULL, NULL, NULL, NULL, $title);
  }
  protected function show_contents()
  {
    echo($this->contents);
  }
  protected function add_contents()
  {
  }
}
?>
