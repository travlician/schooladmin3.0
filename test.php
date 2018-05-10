<?php
$s = $_GET['s'];
$n = $_GET['n'];

for($i=0; $i<=$s; $i++)
{
  $res = round($n + (9.0 * ($i/$s)),1);
  if($n > 1.0)
  {
    $res1 = round(1.0 + $i * (9.0 / $s) * 2.0,1);
	//$res10 = round(10.0 – (($s – $i) * (9.0 / $s)) * 0.5,1);
	$res10 = round(10.0 - ($s-$i) * (9.0 / $s) * 0.5,1);
	if($res > $res1)
	  $res = $res1;
	if($res > $res10)
	  $res = $res10;
  }
  else
  {
    $res1 = round(1.0 + $i * (9.0 / $s) * 0.5,1);
	$res10 = round(10.0 - ($s-$i) * (9.0 / $s) * 2.0,1);
	if($res < $res1)
	  $res = $res1;
	if($res < $res10)
	  $res = $res10;
  }
  echo("<BR>". $i. " => ". $res);
}
?>
