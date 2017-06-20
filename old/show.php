<?php

include("funclib.php");

if (!SQLConnect()){
	echo "SQL Error : ".SQLError();
	exit;
}

//Get Job Data
if (!($res=SQLQuery("SELECT * FROM wiringjobs WHERE wjid='".$_POST['job']."';"))){echo "SQL Error : ".SQLError(); exit;}
$jobdata=SQLGetRow($res);
$lowX=$jobdata['lowX'];
$lowY=$jobdata['lowY'];
$hiX=$jobdata['hiX'];
$hiY=$jobdata['hiY'];

//Get the wire data
if (!($res=SQLQuery("SELECT * FROM wiringdata WHERE wjid='".$_POST['job']."';"))){echo "SQL Error : ".SQLError(); exit;}
$wires=array(); while ($wire=SQLGetRow($res)){$wires[]=$wire;}

//Get the extras data
if (!($res=SQLQuery("SELECT * FROM wiringdataextras WHERE wjid='".$_POST['job']."';"))){echo "SQL Error : ".SQLError(); exit;}
$extras=array(); while ($extra=SQLGetRow($res)){$extras[]=$extra;}

//Calculate parameters
if (($hiX-$lowX)!=0){
	$aspect=($hiY-$lowY)/($hiX-$lowX);
}else{
	$aspect=0;
}
$width=($hiX-$lowX); if ($width==0){$width=1;}
$height=($hiY-$lowY); if ($height==0){$height=1;}

$img_width=1000;
$img_height=$img_width*$aspect;

$im=imagecreatetruecolor($img_width+10,$img_height+10);

//Grays
$black=imagecolorallocate($im, 0, 0, 0);
$gray=imagecolorallocate($im, 150, 150, 150);
$light_gray=imagecolorallocate($im, 200, 200, 200);
$white=imagecolorallocate($im, 255, 255, 255);

//Primaries
$red=imagecolorallocate($im, 255, 150, 0);
$green=imagecolorallocate($im, 0, 255, 150);
$blue=imagecolorallocate($im, 0, 150, 255);

//Secondaries
$yellow=imagecolorallocate($im, 255, 255, 0);
$magenta=imagecolorallocate($im, 255, 0, 255);
$cyan=imagecolorallocate($im, 0, 255, 255);

//Others
$brown=imagecolorallocate($im, 162, 42, 42);
$pink=imagecolorallocate($im, 255, 192, 203);
$orange=imagecolorallocate($im, 255, 165, 0);

imagefill($im, 0, 0, $white);

foreach ($wires as $wire){
	if ($wire['ToType']!=""){	
		$x1 = 5+(($wire['FromX']-$lowX)/$width)*$img_width;
		$y1 = 5+(($wire['FromY']-$lowY)/$height)*$img_height;
		$x2 = 5+(($wire['ToX']-$lowX)/$width)*$img_width;
		$y2 = 5+(($wire['ToY']-$lowY)/$height)*$img_height;

/*
		switch (strtoupper($wire['Colour'])){
			case 'BLACK': imageline($im, $x1, $y1, $x2, $y2, $gray); break;
			
			case 'PINK': imageline($im, $x1, $y1, $x2, $y2, $pink); break;
			case 'BROWN': imageline($im, $x1, $y1, $x2, $y2, $brown); break;
			case 'RED': imageline($im, $x1, $y1, $x2, $y2, $red); break;
			case 'ORANGE': imageline($im, $x1, $y1, $x2, $y2, $orange); break;
			case 'YELLOW': imageline($im, $x1, $y1, $x2, $y2, $yellow); break;
			case 'GREEN': imageline($im, $x1, $y1, $x2, $y2, $green); break;
			case 'BLUE': imageline($im, $x1, $y1, $x2, $y2, $blue); break;
			case 'VIOLET': imageline($im, $x1, $y1, $x2, $y2, $magenta); break;
			case 'GREY': imageline($im, $x1, $y1, $x2, $y2, $gray); break;
			case 'WHITE': imageline($im, $x1, $y1, $x2, $y2, $light_gray); break;

		}*/
	}
}

foreach ($wires as $wire){
	$x = (($wire['FromX']-$lowX)/$width)*$img_width;
	$y = (($wire['FromY']-$lowY)/$height)*$img_height;
	if ($wire['FromType']=="Pin"){
		imageellipse($im, $x+5, $y+5, 7, 7, $black);
	}else{
		imageellipse($im, $x+5, $y+5, 4, 4, $orange);
	}

	$x = (($wire['ToX']-$lowX)/$width)*$img_width;
	$y = (($wire['ToY']-$lowY)/$height)*$img_height;
	if ($wire['ToType']=="Pin"){
		imageellipse($im, $x+5, $y+5, 7, 7, $black);
	}else{
		imageellipse($im, $x+5, $y+5, 4, 4, $orange);
	}
}

/*
foreach ($extras as $extra){
	$x = (($extra['X']-$lowX)/$width)*$img_width;
	$y = (($extra['Y']-$lowY)/$height)*$img_height;
	imageellipse($im, $x+5, $y+5, 2, 2, $red);	
}*/


header('Content-type: image/png');

imagepng($im);
imagedestroy($im);