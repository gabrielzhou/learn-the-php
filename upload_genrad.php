<html>
<head>
	<script type="text/javascript" src="ftp.js"></script>
</head>
<body onLoad="uploadComplete();">
<?php

$time_start = microtime(true);

//Get POST parameters
$jid=$_POST['jid'];

if (!(($jid>0) && ($jid<100000000))){
	echo "ERROR : Invalid Job ID";
	exit;
}

ini_set("memory_limit","128M");

include("funclib.php");

//Check we have enough files
if (count($_FILES)<1){echo "ERROR : Not Enough Files"; exit;}

//Establish Database connection
if (!SQLConnect()){echo "SQL ERROR : ".SQLError();exit;}

if (!SQLQuery("DELETE FROM wiringdata WHERE wjid=(SELECT wjid FROM wiringjobs WHERE jid='".$jid."');")) echo "SQL ERROR : ".SQLError();
if (!SQLQuery("DELETE FROM wiringdataextras WHERE wjid=(SELECT wjid FROM wiringjobs WHERE jid='".$jid."');")) echo "SQL ERROR : ".SQLError();
if (!SQLQuery("DELETE FROM wiringjobs WHERE jid='".$jid."';")) echo "SQL ERROR : ".SQLError();

$wires=array();

//Read in wires files
$lines=file($_FILES["gfile"]["tmp_name"]);

$highchan=0;
$wireno=1;
$top=false;
$automatic=false;
foreach ($lines as $line_num => $line){	
	if ($line_num>0){
		if (preg_match("/^@\s+\S+\s+(?<Name>\S+)\s+/",$pre_line,$matches)){
			if (preg_match("/^\s+\S+\s+\S+\s+\S+\s+\S+\s+(?<x>[\d\.-]+)\s+(?<y>[\d\.-]+)/",$line,$matches2)){
				
				//Uncalculated entries
				$entry['WireNo']=$wireno++;				
				$entry['Gague']='0';
				$entry['Name']='';

				//Length blank for now
				$entry['Length']=0;

				//Set 'from' info
				$entry['FromType']="Probe";
				$entry['FromName']=$matches['Name'];
				if (is_numeric($matches['Name']) && $matches['Name']>$highchan){$highchan=$matches['Name'];}
				$temp = $matches2['x'];
				//$entry['FromX']=$matches2['x'];
				$entry['FromX'] = $matches2['y'];
				$entry['FromY'] = -$temp;
				$entry['FromCalib']='0';

				//Set 'to' info (check if we've got power wiring)
				if (preg_match("/^[\d\.]+$/",$matches['Name'])){
					$entry['ToType']='I/F';
					$entry['ToCalib']='1';
					
					//Calculate Genrad interface location
					$chan=$matches['Name'];
					$inf=floor((($chan-1)/128)+4);
					$chan-=($inf-4)*128;
					$pin=floor((($chan-1)/16)+2);
					$chan-=(($pin-2)*16)+1;
					if ($chan>=8){$side='A';$chan-=8;}else{$side='B';}
					$pin+=($chan*10);
					if ($pin<0){$pin=0;}

					//Format the name properly
					$entry['ToName']=sprintf("%u%s%02u",$inf,$side,$pin);
					$entry['I/F']=$inf;

					//Calculate MM location
					$entry['ToX']=-(($pin-82)*2.54);
					$entry['ToY']=-((($inf-4)*15.875)+(($side=='A')?2.54:0));

					//Select the correct colour based on interface number
					switch ($inf%10){
						case 0: $entry['Colour']='PINK'; break;
						case 1: $entry['Colour']='BROWN'; break;
						case 2: $entry['Colour']='RED'; break;
						case 3: $entry['Colour']='ORANGE'; break;
						case 4: $entry['Colour']='YELLOW'; break;
						case 5: $entry['Colour']='GREEN'; break;
						case 6: $entry['Colour']='BLUE'; break;
						case 7: $entry['Colour']='VIOLET'; break;
						case 8: $entry['Colour']='GREY'; break;
						case 9: $entry['Colour']='WHITE'; break;
					}
					
				}else{
					$entry['ToType']='Power';
					$entry['ToName']='Power';
					$entry['ToX']=0;
					$entry['ToY']=0;
					$entry['Colour']='';
					$entry['ToCalib']='1';
					$entry['I/F']=100;
				}

				$wires[]=$entry;

			}else{
				echo ("Line ".$line_num." Malformed : ".$line);
			}
		}
	}

	$pre_line=$line;
}

$jobsize=($highchan<1536)?15:(($highchan<1919)?18:31);

//Calculate some parameters
$lowX=PHP_INT_MAX;
$lowY=PHP_INT_MAX;
$hiX=-PHP_INT_MAX;
$hiY=-PHP_INT_MAX;

foreach ($wires as $wire){
	if ($wire['FromX']<$lowX){$lowX=$wire['FromX'];}
	if ($wire['FromX']>$hiX){$hiX=$wire['FromX'];}
	if ($wire['FromY']<$lowY){$lowY=$wire['FromY'];}
	if ($wire['FromY']>$hiY){$hiY=$wire['FromY'];}
}

//Insert header info into jobs database
$query = "INSERT INTO wiringjobs (`jid`,`size`,`wirecount`,`extracount`,`lowX`,`lowY`,`hiX`,`hiY`,`hasTop`,`calibration`) VALUES ('".$jid."','X','".count($wires)."','0','".$lowX."','".$lowY."','".$hiX."','".$hiY."','0','GRC/".$jobsize.",GRIF/".$jobsize."');";
if (!SQLQuery($query)){echo "SQL ERROR : ".SQLError();}

//Get the wiring job number
if (!($res=SQLQuery("SELECT wjid FROM wiringjobs WHERE jid='".$jid."'"))){echo "SQL ERROR : ".SQLError();}
$jobdata=SQLGetRow($res);
$wjid=$jobdata['wjid'];

//TODO : Lengths need to be somehow calculated

//Insert wire info into jobs database
$wireno=0;

foreach ($wires as $matches){
	$priority=100-$matches['I/F'];
	$query = "INSERT DELAYED INTO wiringdata (`wjid`,`WireNo`,`From`,`To`,`FromX`,`FromY`,`ToX`,`ToY`,`Length`,`Gague`,`Colour`,`FromType`,`ToType`,`Name`,`Top`,`fromCalib`,`toCalib`,`priority`) VALUES ('".$wjid."','".$wireno++."','".$matches['FromName']."','".$matches['ToName']."','".$matches['FromX']."','".$matches['FromY']."','".$matches['ToX']."','".$matches['ToY']."','".$matches['Length']."','".$matches['Gague']."','".$matches['Colour']."','".$matches['FromType']."','".$matches['ToType']."','','0','".$matches['FromCalib']."','".$matches['ToCalib']."','".$priority."');";
	if (!SQLQuery($query)){echo "SQL ERROR : ".SQLError();}
}

//Store size information
$query = "INSERT DELAYED INTO wiringdataextras (`wjid`,`data`,`print`) VALUES ('".$wjid."','Job Size : ".$jobsize." Banks','1');";
if (!SQLQuery($query)){echo "SQL ERROR : ".SQLError();}

$query = "INSERT DELAYED INTO wiringdataextras (`wjid`,`data`,`print`) VALUES ('".$wjid."','S".$jobsize."','0');";
if (!SQLQuery($query)){echo "SQL ERROR : ".SQLError();}

$query = "INSERT DELAYED INTO wiringdataextras (`wjid`,`data`,`print`) VALUES ('".$wjid."','Dimensions : ".($hiX-$lowX)." x ".($hiY-$lowY)."mm','1');";
if (!SQLQuery($query)){echo "SQL ERROR : ".SQLError();}

$query = "INSERT DELAYED INTO wiringdataextras (`wjid`,`data`,`print`) VALUES ('".$wjid."','D".$lowX.",".$lowY.",".$hiX.",".$hiY."','0');";
if (!SQLQuery($query)){echo "SQL ERROR : ".SQLError();}


echo "<u>Data Entereted</u><BR/>";
echo "Wire Count : ".count($wires)."<BR/>";
echo "Interface Size : ".$jobsize." Banks<BR/>";
echo "Dimensions : ".abs($hiX-$lowX)."x".abs($hiY-$lowY)."mm<BR/>";

$time_end = microtime(true);
$time = $time_end - $time_start;
echo "Processing time : ".sprintf("%.2f",$time)." seconds\n";

?>
</body>
</html>