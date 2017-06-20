<?php

include("funclib.php");

if (!SQLConnect()){
	echo "SQL Error : ".SQLError();
	exit;
}

$lowX=PHP_INT_MAX;
$lowY=PHP_INT_MAX;
$hiX=-PHP_INT_MAX;
$hiY=-PHP_INT_MAX;

if (!($res=SQLQuery("SELECT * FROM WiringData WHERE JID='".$_POST['job']."';")))
	{echo "SQL Error : ".SQLError(); exit;}

$wires=array();

while ($wire=SQLGetRow($res)){
	$wires[]=$wire;

	if ($wire['FromX']<$lowX){$lowX=$wire['FromX']; $lowXat=$wire;}
	if ($wire['FromX']>$hiX){$hiX=$wire['FromX']; $hiXat=$wire;}
	if ($wire['ToX']<$lowX){$lowX=$wire['ToX']; $lowXat=$wire;}
	if ($wire['ToX']>$hiX){$hiX=$wire['ToX']; $hiXat=$wire;}

	if ($wire['FromY']<$lowY){$lowY=$wire['FromY']; $lowYat=$wire;}
	if ($wire['FromY']>$hiY){$hiY=$wire['FromY']; $hiYat=$wire;}
	if ($wire['ToY']<$lowY){$lowY=$wire['ToY']; $lowYat=$wire;}
	if ($wire['ToY']>$hiY){$hiY=$wire['ToY']; $hiYat=$wire;}
}

$extras=array();

if (!($res=SQLQuery("SELECT * FROM WiringDataExtras WHERE JID='".$_POST['job']."';")))
	{echo "SQL Error : ".SQLError(); exit;}

while ($extra=SQLGetRow($res)){
	$extras[]=$extra;

	if ($extra['X']<$lowX){$lowX=$extra['FromX']; $lowXat=$extra;}
	if ($extra['X']>$hiX){$hiX=$extra['FromX']; $hiXat=$extra;}
	if ($extra['Y']<$lowY){$lowY=$extra['ToY']; $lowYat=$extra;}
	if ($extra['Y']>$hiY){$hiY=$extra['ToY']; $hiYat=$extra;}
}


$aspect=($hiY-$lowY)/($hiX-$lowX);
$width=($hiX-$lowX);
$height=($hiY-$lowY);

$img_width=1000;
$img_height=$img_width*$aspect;

$im=imagecreatetruecolor($img_width,$img_height);
$green=imagecolorallocate($im, 0, 255, 0);
$red=imagecolorallocate($im, 255, 0, 0);
$gray=imagecolorallocate($im, 200, 200, 200);
$black=imagecolorallocate($im, 150, 150, 150);
$white=imagecolorallocate($im, 255, 255, 255);

imagefill($im, 0, 0, $white);

foreach ($wires as $wire){
	$x1 = (($wire['FromX']-$lowX)/$width)*$img_width;
	$y1 = (($wire['FromY']-$lowY)/$height)*$img_height;
	$x2 = (($wire['ToX']-$lowX)/$width)*$img_width;
	$y2 = (($wire['ToY']-$lowY)/$height)*$img_height;

	//imageline($im, $x1, $y1, $x2, $y2, $black);
}

foreach ($wires as $wire){
	$x = (($wire['FromX']-$lowX)/$width)*$img_width;
	$y = (($wire['FromY']-$lowY)/$height)*$img_height;
	imageellipse($im, $x, $y, 2, 2, $gray);

	$x = (($wire['ToX']-$lowX)/$width)*$img_width;
	$y = (($wire['ToY']-$lowY)/$height)*$img_height;
	imageellipse($im, $x, $y, 2, 2, $black);
}

foreach ($extras as $extra){
	$x = (($extra['X']-$lowX)/$width)*$img_width;
	$y = (($extra['Y']-$lowY)/$height)*$img_height;
	imageellipse($im, $x, $y, 2, 2, $red);	
}


header('Content-type: image/png');

imagepng($im);
imagedestroy($im);