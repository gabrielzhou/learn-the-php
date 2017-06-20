<html>
<head>
	<script type="text/javascript" src="ftp.js"></script>
</head>
<body onLoad="uploadComplete();">
<?php

//Windows
//$combiner='wicombine';
//$comined_output='tmp\\wicombined';
//$catter='type';

//Linux
$combiner='./wicombine';
$comined_output='/tmp/wicombined';
$catter='cat';


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
if (count($_FILES)<2){echo "ERROR : Not Enough Files"; exit;}

//Establish Database connection
if (!SQLConnect()){echo "SQL ERROR : ".SQLError();exit;}

//Execute the command to combine the data
$command = $combiner.' "'.$_FILES["wfile"]["tmp_name"].'" "'.$_FILES["ifile"]["tmp_name"].'" "'.$comined_output.'"';
exec($command, $output, $result);
if ($result!=0){echo "ERROR : Failed to Integrate Information : ".implode($output)."<br />\n"; exit;}
unset($output);

//Get the combined data
exec($catter.' "'.$comined_output.'"',$output,$result);
if ($result!=0){echo "ERROR : Failed to Retrieve Integrated Information : ".implode($output)."<br />\n"; exit;}

//Clear any old data
if (!SQLQuery("DELETE FROM wiringdata WHERE wjid=(SELECT wjid FROM wiringjobs WHERE jid='".$jid."');")) echo "SQL ERROR : ".SQLError();
if (!SQLQuery("DELETE FROM wiringdataextras WHERE wjid=(SELECT wjid FROM wiringjobs WHERE jid='".$jid."');")) echo "SQL ERROR : ".SQLError();
if (!SQLQuery("DELETE FROM wiringjobs WHERE JID='".$jid."';")) echo "SQL ERROR : ".SQLError();

//Prepare the arrays
$wires=array();
$wireextras=array();

//Parse the returnd data
foreach ($output as $line_num => $line){
	unset($matches);
	switch ($line[0]){
		case 'H':	//Header
			if (preg_match("/.(?<Key>.*):(?<Value>.*)/",$line,$matches)){
				$header[$matches['Key']]=$matches['Value'];
			}else{
				echo "ERROR : Malformed Integrated Information : ".$line."<br />\n"; exit;
			}
			break;

		case 'W':	//Wire
			if (preg_match("/.(?<ToName>[^,]*),(?<FromName>[^,]*),(?<ToX>[^,]*),(?<ToY>[^,]*),(?<FromX>[^,]*),(?<FromY>[^,]*),(?<Length>[^,]*),(?<Gague>[^,]*),(?<Colour>[^,]*),(?<ToType>[^,]*),(?<FromType>[^,]*),(?<Device>[^,]*),(?<Top>[^,]*)/",$line,$matches)){
				$matches['FromName']=urldecode($matches['FromName']);
				$matches['ToName']=urldecode($matches['ToName']);
				$matches['Colour']=urldecode($matches['Colour']);
				$matches['FromType']=urldecode($matches['FromType']);
				$matches['ToType']=urldecode($matches['ToType']);
				$matches['Device']=urldecode($matches['Device']);
				$wires[]=$matches;
			}else{
				echo "ERROR : Malformed Integrated Information : ".$line."<br />\n"; exit;
			}
			break;

		case 'X':	//Extra
			if (preg_match("/.(?<X>[\d\.]*),(?<Y>[\d\.]*),(?<Type>[^,]*),(?<Top>[^,]*)/",$line,$matches)){
				$matches['data']=$matches['Type'].$matches['X'].','.$matches['Y'].(($matches['Top']==1)?"T":"");
				$matches['print']=0;
				$extras[]=$matches;
			}else{
				echo "ERROR : Malformed Integrated Information : ".$line."<br />\n"; exit;
			}
			break;
	}
}

$calibration="HP/".$header['Size'];

//Insert header info into jobs database
$query = "INSERT INTO wiringjobs (`jid`,`size`,`wirecount`,`extracount`,`lowX`,`lowY`,`hiX`,`hiY`,`hasTop`,`calibration`) VALUES ('".$jid."','".$header['Size']."','".$header['Wires']."','".$header['Extras']."','".$header['LowX']."','".$header['LowY']."','".$header['HiX']."','".$header['HiY']."','".(($header['Top']!='NO')?"1":"0")."','".$calibration."');";
if (!SQLQuery($query)){echo "SQL ERROR : ".SQLError();}

//Get the wiring job number
if (!($res=SQLQuery("SELECT wjid FROM wiringjobs WHERE jid='".$jid."'"))){echo "SQL ERROR : ".SQLError();}
$jobdata=SQLGetRow($res);
$wjid=$jobdata['wjid'];

//Insert wire info into jobs database
$wireno=0;
unset($counts);
foreach ($wires as $matches){
	$priority=0;
	if ($matches['Colour']=='Green'){$priority+=300;}
	if ($matches['Colour']=='Black'){$priority+=200;}
	if ($matches['Colour']=='Red'){$priority+=100;}
	if ($matches['FromType']=='39 mil'){$priority+=25;}
	if ($matches['FromType']=='50 mil'){$priority+=10;}
	if ($matches['ToType']=='39 mil'){$priority+=25;}
	if ($matches['ToType']=='50 mil'){$priority+=10;}
	//Store the count of each type of priority
	if (isset($counts[$priority])){$counts[$priority]++;}else{$counts[$priority]=0;}

	$query = "INSERT DELAYED INTO wiringdata (`wjid`,`WireNo`,`From`,`To`,`FromX`,`FromY`,`ToX`,`ToY`,`Length`,`Gague`,`Colour`,`FromType`,`ToType`,`Name`,`Top`,`fromCalib`,`toCalib`,`priority`) VALUES ('".$wjid."','".$wireno++."','".$matches['FromName']."','".$matches['ToName']."','".$matches['FromX']."','".$matches['FromY']."','".$matches['ToX']."','".$matches['ToY']."','".$matches['Length']."','".$matches['Gague']."','".$matches['Colour']."','".$matches['FromType']."','".$matches['ToType']."','".$matches['Device']."','".$matches['Top']."','0','0','".$priority."');";
	if (!SQLQuery($query)){echo "SQL ERROR : ".SQLError();}
//	echo $wireno." (".(microtime(true) - $time_start).") [".($wireno/(microtime(true) - $time_start))."/s]<br/>\n";
//	flush();
}

//Insert extra data into jobs database
$wireno=0;
foreach ($extras as $matches){
	$query = "INSERT DELAYED INTO wiringdataextras (`wjid`,`data`,`print`) VALUES ('".$wjid."','".$matches['data']."','".$matches['print']."');";
	if (!SQLQuery($query)){echo "SQL ERROR : ".SQLError();}
}

//Add non-wire extra data
switch ($header['Size']){
	case 1:
		$query = "INSERT DELAYED INTO wiringdataextras (`wjid`,`data`,`print`) VALUES ('".$wjid."','Fixture Size : Bank 1','1');";
		if (!SQLQuery($query)){echo "SQL ERROR : ".SQLError();}

		$query = "INSERT DELAYED INTO wiringdataextras (`wjid`,`data`,`print`) VALUES ('".$wjid."','S1','0');";
		if (!SQLQuery($query)){echo "SQL ERROR : ".SQLError();}
		break;

	case 2:
		$query = "INSERT DELAYED INTO wiringdataextras (`wjid`,`data`,`print`) VALUES ('".$wjid."','Fixture Size : Bank 2','1');";
		if (!SQLQuery($query)){echo "SQL ERROR : ".SQLError();}

		$query = "INSERT DELAYED INTO wiringdataextras (`wjid`,`data`,`print`) VALUES ('".$wjid."','S2','0');";
		if (!SQLQuery($query)){echo "SQL ERROR : ".SQLError();}
		break;

	case 3:
		$query = "INSERT DELAYED INTO wiringdataextras (`wjid`,`data`,`print`) VALUES ('".$wjid."','Fixture Size : Full','1');";
		if (!SQLQuery($query)){echo "SQL ERROR : ".SQLError();}

		$query = "INSERT DELAYED INTO wiringdataextras (`wjid`,`data`,`print`) VALUES ('".$wjid."','S3','0');";
		if (!SQLQuery($query)){echo "SQL ERROR : ".SQLError();}
		break;
}

//Store 'top' information
if ($header['Top']!='NO'){
	$query = "INSERT DELAYED INTO wiringdataextras (`wjid`,`data`,`print`) VALUES ('".$wjid."','Top Wiring','1');";
	if (!SQLQuery($query)){echo "SQL ERROR : ".SQLError();}
}

//Store the prioriy counts
for ($i=0; $i<400; $i++){
	if (isset($counts[$i])){
		$query = "INSERT DELAYED INTO wiringdataextras (`wjid`,`data`,`print`) VALUES ('".$wjid."','C".$i."=".$counts[$i]."','0');";
		if (!SQLQuery($query)){echo "SQL ERROR : ".SQLError();}
	}
}

//350 - Green 39->39
//335 - Green 39->50
//320 - Green 50->50
//310 - Green 50->Large
//300 - Green Large (100&75)

//250 - Black 39->39
//235 - Black 39->50
//220 - Black 50->50
//210 - Black 50->Large
//200 - Black Large (100&75)

//150 - Red 39->39
//135 - Red 39->50
//120 - Red 50->50
//110 - Red 50->Large
//100 - Red Large (100&75)

// 50 - Blue 39->39
// 35 - Blue 39->50
// 20 - Blue 50->50
// 10 - Blue 50->Large
//  0 - Blue Large (100&75)

echo "<u>Data Entereted</u><BR/>";
echo "Wire Count : ".count($wires)."<BR/>";
echo "Extras Count : ".count($extras)."<BR/>";
switch ($header['Size']){
	case 1:	echo "Fixture Size : Bank 1<BR/>"; break;
	case 2:	echo "Fixture Size : Bank 2<BR/>"; break;
	case 3:	echo "Fixture Size : Full<BR/>"; break;
}

$time = microtime(true) - $time_start;
echo "Processing time : ".sprintf("%.2f",$time)." seconds\n";

?>
</body>
</html>
