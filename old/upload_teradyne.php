<?php

$time_start = microtime(true);

//Get POST parameters
$jid=$_POST['job'];

if (!(($jid>0) && ($jid<100000000))){
	echo "Invalid Job ID";
	exit;
}

ini_set("memory_limit","50M");

include("funclib.php");

//Check we have enough files
if (count($_FILES)<1){echo "NOT ENOUGH FILES"; exit;}

//Establish Database connection
if (!SQLConnect()){echo "SQL Error : ".SQLError();exit;}

if (!SQLQuery("DELETE FROM wiringdata WHERE wjid=(SELECT wjid FROM wiringjobs WHERE jid='".$_POST['job']."');")) echo "SQL Error : ".SQLError();
if (!SQLQuery("DELETE FROM wiringdataextras WHERE wjid=(SELECT wjid FROM wiringjobs WHERE jid='".$_POST['job']."');")) echo "SQL Error : ".SQLError();
if (!SQLQuery("DELETE FROM wiringjobs WHERE jid='".$_POST['job']."';")) echo "SQL Error : ".SQLError();

$wires=array();

//Read in wires files
$lines=file($_FILES["tfile"]["tmp_name"]);
print_r($_FILES);

$highchan=0;
$wireno=1;
$top=false;
$automatic=false;
foreach ($lines as $line_num => $line){	
	if ($line_num>0){
		if (preg_match("/^@\s+\S+\s+(?<Name>\S+)\s+/",$pre_line,$matches)){
			if (preg_match("/^\s+\S+\s+\S+\s+\S+\s+\S+\s+(?<x>[\d\.]+)\s+(?<y>[\d\.]+)/",$line,$matches2)){
				
				//Uncalculated entries
				$entry['WireNo']=$wireno++;				
				$entry['Gague']='0';
				$entry['Name']='';

				//Length blank for now
				$entry['Length']=0;

				//Set 'from' info
				$entry['FromType']="Probe";
				$entry['FromName']=$matches['Name'];
				if ($matches['Name']>$highchan){$highchan=$matches['Name'];}
				$entry['FromX']=$matches2['x'];
				$entry['FromY']=$matches2['y'];
				$entry['FromCalib']='0';

				//Set 'to' info (check if we've got power wiring)
				if (preg_match("/^[\d\.]+$/",$matches['Name'])){
					$entry['ToType']='I/F';
					$entry['ToCalib']='1';
					$entry['ToName']=$matches['Name'];

					//Calculate Teradyne Interface
					$chan=$matches['Name'];
					$bank=floor($chan/32);
					$chan-=32*$bank;
					$x=($bank%32)*-19.05;
					$y=($bank>=32)?(-127):(-3.175);
										
					if ($bank<32){
						if ($chan>15){$chan-=16;$y-=38.1;}
						if ($chan>7){$chan-=8; $x-=3.175;}
						$y-=(7-$chan)*3.175;
					}else{
						if ($chan>15){$chan-=16;}else{$y-=38.1;}
						if ($chan>7){$chan-=8;}else{$x-=3.175;}
						$y-=$chan*3.175;		
					}

					//Format the name properly
					$entry['I/F']=$inf;

					//Calculate MM location
					$entry['ToX']=$x;
					$entry['ToY']=$y;

					$entry['Colour']='BLUE';
					
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
				die("Malformed : ".$pre_line);
			}
		}
	}

	$pre_line=$line;
}

//Set the job size based on channel numbers
if      ($highchan>1023) $jobsize=64;
else if ($highchan>639)	$jobsize=32;
else if ($highchan>319)	$jobsize=20;
else					$jobsize=10;

echo "<pre>";
//foreach ($wires as $wire){
//echo $wire['fromName']." -> ".$wire['toName']."\n";
//}
//print_r($wires);
echo "</pre>";

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
$query = "INSERT INTO wiringjobs (`jid`,`size`,`wirecount`,`extracount`,`lowX`,`lowY`,`hiX`,`hiY`,`hasTop`,`calibration`) VALUES ('".$jid."','X','".count($wires)."','0','".$lowX."','".$lowY."','".$hiX."','".$hiY."','0','TRC/".$jobsize.",TRIF/".$jobsize."');";
if (!SQLQuery($query)){echo "SQL Error : ".SQLError();}

//Get the wiring job number
if (!($res=SQLQuery("SELECT wjid FROM wiringjobs WHERE jid='".$jid."'"))){echo "SQL Error : ".SQLError();}
$jobdata=SQLGetRow($res);
$wjid=$jobdata['wjid'];

echo $query."<br/>\n\n";

//TODO : Lengths need to be somehow calculated

//Insert wire info into jobs database
$wireno=0;

foreach ($wires as $matches){
	$priority=100-$matches['I/F'];
	$query = "INSERT DELAYED INTO wiringdata (`wjid`,`WireNo`,`From`,`To`,`FromX`,`FromY`,`ToX`,`ToY`,`Length`,`Gague`,`Colour`,`FromType`,`ToType`,`Name`,`Top`,`fromCalib`,`toCalib`,`priority`) VALUES ('".$wjid."','".$wireno++."','".$matches['FromName']."','".$matches['ToName']."','".$matches['FromX']."','".$matches['FromY']."','".$matches['ToX']."','".$matches['ToY']."','".$matches['Length']."','".$matches['Gague']."','".$matches['Colour']."','".$matches['FromType']."','".$matches['ToType']."','".$matches['Device']."','".(($matches['Top']!='NO')?"1":"0")."','".$matches['FromCalib']."','".$matches['ToCalib']."','".$priority."');";
	if (!SQLQuery($query)){echo "SQL Error : ".SQLError();}
}

//Store size information
$query = "INSERT DELAYED INTO wiringdataextras (`wjid`,`data`,`print`) VALUES ('".$wjid."','Job Size : ".$jobsize." Banks','1');";
if (!SQLQuery($query)){echo "SQL Error : ".SQLError();}

$query = "INSERT DELAYED INTO wiringdataextras (`wjid`,`data`,`print`) VALUES ('".$wjid."','S".$jobsize."','0');";
if (!SQLQuery($query)){echo "SQL Error : ".SQLError();}

$query = "INSERT DELAYED INTO wiringdataextras (`wjid`,`data`,`print`) VALUES ('".$wjid."','Dimensions : ".($hiX-$lowX)." x ".($hiY-$lowY)."mm','1');";
if (!SQLQuery($query)){echo "SQL Error : ".SQLError();}

$query = "INSERT DELAYED INTO wiringdataextras (`wjid`,`data`,`print`) VALUES ('".$wjid."','D".$lowX.",".$lowY.",".$hiX.",".$hiY."','0');";
if (!SQLQuery($query)){echo "SQL Error : ".SQLError();}


echo "<u>Data Entereted</u><BR/>";
echo "Fixture Size : ".$jobsize."<BR/>";

$time_end = microtime(true);
$time = $time_end - $time_start;
echo "Execution time : $time seconds\n";


?>
