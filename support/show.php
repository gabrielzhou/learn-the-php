<?php

include("../funclib.php");

if (!SQLConnect()){
	echo "SQL Error : ".SQLError();
	exit;
}

//Get Job Data
if (!($res=SQLQuery("SELECT * FROM wiringjobs WHERE JID='".$_GET['jid']."';"))){echo "SQL Error : ".SQLError(); exit;}
$jobdata=SQLGetRow($res);
$wjid=$jobdata['wjid'];
$lowX=$jobdata['lowX'];
$lowY=$jobdata['lowY'];
$hiX=$jobdata['hiX'];
$hiY=$jobdata['hiY'];

//Get the wire data
if (!($res=SQLQuery("SELECT * FROM wiringdata WHERE wjid='".$wjid."' AND (fromCalib=0 OR toCalib=0);"))){echo "SQL Error : ".SQLError(); exit;}
$wires=array(); while ($wire=SQLGetRow($res)){$wires[]=$wire;}

//Get the extras data
if (!($res=SQLQuery("SELECT * FROM wiringdataextras WHERE wjid='".$wjid."' AND print!=1;"))){echo "SQL Error : ".SQLError(); exit;}
$extras=array(); while ($extra=SQLGetRow($res)){$extras[]=$extra;}

//Calculate parameters
$aspect=($hiY-$lowY)/($hiX-$lowX);
$width=($hiX-$lowX);
$height=($hiY-$lowY);

$img_width=$_GET['width'];
$img_height=$img_width*$aspect;

$dpmm=$img_width/$width;

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

//imagestring($im, 5, 0, 0, time(date("now")), $textcolor);

/*
foreach ($wires as $wire){
	if ($wire['ToType']!=""){	
		$x1 = 5+(($wire['FromX']-$lowX)/$width)*$img_width;
		$y1 = 5+(($wire['FromY']-$lowY)/$height)*$img_height;
		$x2 = 5+(($wire['ToX']-$lowX)/$width)*$img_width;
		$y2 = 5+(($wire['ToY']-$lowY)/$height)*$img_height;

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

		}
	}
}*/

foreach ($wires as $wire){
	$x = (($wire['FromX']-$lowX)/$width)*$img_width;
	$y = (($wire['FromY']-$lowY)/$height)*$img_height;
	switch ($wire['FromType']){
		case 'Pin':
		case 'BRC':
			imagefilledellipse($im, $x+5, $y+5, 3*$dpmm, 3*$dpmm, $black);
			break;

		case 'Probe':
		case '100 mil':
			imagefilledellipse($im, $x+5, $y+5, 1.7*$dpmm, 1.7*$dpmm, $orange);
			break;

		case '75 mil':
			imagefilledellipse($im, $x+5, $y+5, 1.3*$dpmm, 1.3*$dpmm, $orange);
			break;

		case '50 mil':
			imagefilledellipse($im, $x+5, $y+5, 0.95*$dpmm, 0.95*$dpmm, $blue);
			break;

		case '39 mil':
			imagefilledellipse($im, $x+5, $y+5, 0.8*$dpmm, 0.8*$dpmm, $green);
			break;
	}

	$x = (($wire['ToX']-$lowX)/$width)*$img_width;
	$y = (($wire['ToY']-$lowY)/$height)*$img_height;
	switch ($wire['ToType']){
		case 'Pin':
		case 'BRC':
			imagefilledellipse($im, $x+5, $y+5, 3*$dpmm, 3*$dpmm, $black);
			break;

		case 'Probe':
		case '100 mil':
			imagefilledellipse($im, $x+5, $y+5, 1.7*$dpmm, 1.7*$dpmm, $orange);
			break;

		case '75 mil':
			imagefilledellipse($im, $x+5, $y+5, 1.3*$dpmm, 1.3*$dpmm, $orange);
			break;

		case '50 mil':
			imagefilledellipse($im, $x+5, $y+5, 0.95*$dpmm, 0.95*$dpmm, $blue);
			break;

		case '39 mil':
			imagefilledellipse($im, $x+5, $y+5, 0.8*$dpmm, 0.8*$dpmm, $green);
			break;
	}
}

foreach ($extras as $extra){
	if (preg_match("/(?<XType>.)(?<Data>.*)/",$extra['data'],$matches)){
		switch ($matches['XType']){
			case '1':
				if (preg_match("/(?<X>[^,]*),(?<Y>[^,]*)/",$matches['Data'],$matches2)){
					$x = (($extra['X']-$lowX)/$width)*$img_width;
					$y = (($extra['Y']-$lowY)/$height)*$img_height;
					imagefilledellipse($im, $x+5, $y+5, 2, 2, $red);						
				}
				break;
			case '2':
				if (preg_match("/(?<X>[^,]*),(?<Y>[^,]*)/",$matches['Data'],$matches2)){
					$x = (($extra['X']-$lowX)/$width)*$img_width;
					$y = (($extra['Y']-$lowY)/$height)*$img_height;
					imagefilledellipse($im, $x+5, $y+5, 2, 2, $red);						
				}
				break;
			case '3':
				if (preg_match("/(?<X>[^,]*),(?<Y>[^,]*)/",$matches['Data'],$matches2)){
					$x = (($extra['X']-$lowX)/$width)*$img_width;
					$y = (($extra['Y']-$lowY)/$height)*$img_height;
					imagefilledellipse($im, $x+5, $y+5, 2, 2, $red);						
				}
				break;
			case '4':
				if (preg_match("/(?<X>[^,]*),(?<Y>[^,]*)/",$matches['Data'],$matches2)){
					$x = (($extra['X']-$lowX)/$width)*$img_width;
					$y = (($extra['Y']-$lowY)/$height)*$img_height;
					imagefilledellipse($im, $x+5, $y+5, 2, 2, $red);						
				}
				break;
			case 'P':
				if (preg_match("/(?<X>[^,]*),(?<Y>[^,]*)/",$matches['Data'],$matches2)){
					$x = (($extra['X']-$lowX)/$width)*$img_width;
					$y = (($extra['Y']-$lowY)/$height)*$img_height;
					imagefilledellipse($im, $x+5, $y+5, 2, 2, $red);						
				}
				break;
		}
	}
}


header('Content-type: image/png');
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

imagepng($im);
imagedestroy($im);