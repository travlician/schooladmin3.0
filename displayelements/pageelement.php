<?
require_once("displayelement.php");

abstract class pageelement extends displayelement
{
  protected $charset;
  protected $stylesheet;
  protected $title;
  protected $scriptfile;
  public function __construct($divid = NULL, $style = NULL, $charset = NULL, $stylesheet = NULL, $title = NULL, $scriptfile = NULL)
  {
    if(isset($charset))
	  $this->charset = $charset;
    if(isset($stylesheet))
	  $this->stylesheet = $stylesheet;
    if(isset($title))
	  $this->title = $title;
    if(isset($scriptfile))
	  $this->scriptfile = $scriptfile;
	if(!isset($divid) && !isset($style))
	  $style = "margin: 0 auto; text-align: center; border: 1px solid #CCC; width: 99.6%; background-color:#FFF; position:absolute; top: 0; left:0; height: auto;";
    parent::__construct($divid, $style);
  } 
  public function set_charset($charset)
  {
    $this->charset = $charset;
  }
  public function set_stylesheet($stylesheet)
  {
    $this->stylesheet = $stylesheet;
  }
  public function set_title($title)
  {
    $this->title = $title;
  }
  public function set_scriptfile($scriptfile)
  {
    $this->scriptfile = $scriptfile;
  }
  protected function pre_show()
  {
    echo("<html><head>");
	if(isset($this->charset))
	  echo("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=". $this->charset. "\" />");
	if(isset($this->stylesheet))
      echo("<link rel=\"stylesheet\" type=\"text/css\" href=\"". $this->stylesheet. "\" /> ");
	if(isset($this->title))
      echo("<title>". $this->title. "</title>");
    if(isset($this->scriptfile))
      echo("<script type=\"text/javascript\" src=\"". $this->scriptfile. "\"></script>");
	echo("</head><body>");
    parent::pre_show();
  }
  protected function post_show()
  {
    parent::post_show();
	echo("</body></html>");
  }
}
?>
