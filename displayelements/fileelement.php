<?
require_once("displayelement.php");

class fileelement extends displayelement
{
  protected $file;
  public function __construct($divid = NULL, $style = NULL, $file = NULL)
  {
	if(isset($file))
      $this->file = $file;
	parent::__construct($divid, $style);
  }
  protected function show_contents()
  {
    include($this->file);
  }
  protected function add_contents()
  {
  }
  public function set_file($file)
  {
    $this->file = $file;
  }
}
?>
