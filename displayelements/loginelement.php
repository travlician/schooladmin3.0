<?
require_once("displayelement.php");
class loginelement extends displayelement
{
  protected $unamelabel;
  protected $pwlabel;
  protected $loginpage;
  protected $loginlabel;
  protected $logofflabel;
  protected $logoffpage;
  protected $nameindex;
  public function __construct($divid = NULL, $style = NULL, $unamelabel = "Username", $pwlabel = "Password", $loginpage = NULL, $loginlabel = "Sign in",
                               $logofflabel = NULL, $logoffpage = NULL, $nameindex = "showusername")
  {
    $this->unamelabel = $unamelabel;
	$this->pwlabel = $pwlabel;
	if(isset($loginpage))
      $this->loginpage = $loginpage;
	$this->loginlabel = $loginlabel;
	if(isset($logofflabel))
	  $this->logofflabel = $logofflabel;
	if(isset($logoffpage))
	  $this->logoffpage = $logoffpage;
	$this->nameindex = $nameindex;
	parent::__construct($divid, $style);
  }
  protected function add_contents()
  {
  }
  protected function show_contents()
  {
    if(isset($_SESSION[$this->nameindex]))
	{ // Already logged in
	  echo($_SESSION[$this->nameindex]);
	  if(isset($this->logofflabel))
	    echo(" <a href=\"". $this->logoffpage. "\">". $this->logofflabel. "</a>");
	}
	else
	{ // Can login
      echo("<form method=post action=\"". $this->loginpage. "\">");
      echo("<label for=username>". $this->unamelabel. " : </label><input type=text name=username id=username /> ");
      echo("<label for=password>". $this->pwlabel. " : </label><input type=password name=password id=password/> ");
	  echo("<input type=submit name=logon value=\"". $this->loginlabel. "\" /></form>");
	}
  }
  public function set_username_label($unamelabel)
  {
    $this->unamelabel = $unamelabel;
  }
  public function set_password_label($pwlabel)
  {
    $this->pwlabel = $pwlabel;
  }
  public function set_login_page($loginpage)
  {
    $this->loginpage = $loginpage;
  }
  public function  set_login_label($loginlabel)
  {
    $this->loginlabel = $loginlabel;
  }
  public function  set_logoff_label($logofflabel)
  {
    $this->logofflabel = $logofflabel;
  }
  public function  set_logoff_page($logoffpage)
  {
    $this->logoffpage = $logoffpage;
  }
  public function  set_name_index($nameindex)
  {
    $this->nameindex = $nameindex;
  }
}
?>
