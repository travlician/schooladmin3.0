<?
require_once("pageelement.php");

class plainfiledpage extends pageelement
{
  protected $filename;
  public function __construct($filename, $title = NULL)
  {
    if(isset($filename))
	  $this->filename = $filename;
    parent::__construct(NULL, NULL, NULL, NULL, $title);
  }
  protected function show_contents()
  {
    require($this->filename);
  }
  protected function add_contents()
  {
  }
}
?>
