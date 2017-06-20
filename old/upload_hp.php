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
$jid=$_POST['job'];

if (!(($jid>0) && ($jid<100000000))){
	echo "Invalid Job ID";
	exit;
}

ini_set("memory_limit","128M");

include("funclib.php");

//Check we have enough files
if (count($_FILES)<2){echo "NOT ENOUGH FILES"; exit;}

//Establish Database connection
if (!SQLConnect()){echo "SQL Error : ".SQLError();exit;}

//Execute the command to combine the data
$command = $combiner.' "'.$_FILES["wfile"]["tmp_name"].'" "'.$_FILES["ifile"]["tmp_name"].'" "'.$comined_output.'"';
exec($command, $output, $result);
if ($result!=0){echo "Failed to Integrate Information : ".implode($output)."<br />\n"; exit;}
unset($output);

//Get the combined data
exec($catter.' "'.$comined_output.'"',$output,$result);
if ($result!=0){echo "Failed to Retrieve Integrated Information : ".implode($output)."<br />\n"; exit;}

//Clear any old data
if (!SQLQuery("DELETE FROM wiringdata WHERE wjid=(SELECT wjid FROM wiringjobs WHERE jid='".$_POST['job']."');")) echo "SQL Error : ".SQLError();
if (!SQLQuery("DELETE FROM wiringdataextras WHERE wjid=(SELECT wjid FROM wiringjobs WHERE jid='".$_POST['job']."');")) echo "SQL Error : ".SQLError();
if (!SQLQuery("DELETE FROM wiringjobs WHERE JID='".$_POST['job']."';")) echo "SQL Error : ".SQLError();

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
				echo "Malformed Integrated Information : ".$line."<br />\n"; exit;
			}
			break;

		case 'W':	//Wire
			if (preg_match("/.(?<FromName>[^,]*),(?<ToName>[^,]*),(?<FromX>[^,]*),(?<FromY>[^,]*),(?<ToX>[^,]*),(?<ToY>[^,]*),(?<Length>[^,]*),(?<Gague>[^,]*),(?<Colour>[^,]*),(?<FromType>[^,]*),(?<ToType>[^,]*),(?<Device>[^,]*),(?<Top>[^,]*)/",$line,$matches)){
				$matches['FromName']=urldecode($matches['FromName']);
				$matches['ToName']=urldecode($matches['ToName']);
				$matches['Colour']=urldecode($matches['Colour']);
				$matches['FromType']=urldecode($matches['FromType']);
				$matches['ToType']=urldecode($matches['ToType']);
				$matches['Device']=urldecode($matches['Device']);
				$wires[]=$matches;
			}else{
				echo "Malformed Integrated Information : ".$line."<br />\n"; exit;
			}
			break;

		case 'X':	//Extra
			if (preg_match("/.(?<X>[\d\.]*),(?<Y>[\d\.]*),(?<Type>[^,]*),(?<Top>[^,]*)/",$line,$matches)){
				$matches['data']=$matches['Type'].$matches['X'].','.$matches['Y'].(($matches['Top']==1)?"T":"");
				$matches['print']=0;
				$extras[]=$matches;
			}else{
				echo "Malformed Integrated Information : ".$line."<br />\n"; exit;
			}
			break;
	}
}
echo "<PRE>";
print_r($header);
echo "</PRE>";
echo "<hr />";

$calibration="HP/".$header['Size'];

//Insert header info into jobs database
$query = "INSERT INTO wiringjobs (`jid`,`size`,`wirecount`,`extracount`,`lowX`,`lowY`,`hiX`,`hiY`,`hasTop`,`calibration`) VALUES ('".$jid."','".$header['Size']."','".$header['Wires']."','".$header['Extras']."','".$header['LowX']."','".$header['LowY']."','".$header['HiX']."','".$header['HiY']."','".(($header['Top']!='NO')?"1":"0")."','".$calibration."');";
if (!SQLQuery($query)){echo "SQL Error : ".SQLError();}

//Get the wiring job number
if (!($res=SQLQuery("SELECT wjid FROM wiringjobs WHERE jid='".$jid."'"))){echo "SQL Error : ".SQLError();}
$jobdata=SQLGetRow($res);
$wjid=$jobdata['wjid'];

//Insert wire info into jobs database
$wireno=0;
unset($counts);
foreach ($wires as $matches){
	$priority=0;
	if ($matches['Colour']=='Black'){$priority+=200;}
	if ($matches['Colour']=='Red'){$priority+=100;}
	if ($matches['FromType']=='39 mil'){$priority+=25;}
	if ($matches['FromType']=='50 mil'){$priority+=10;}
	if ($matches['ToType']=='39 mil'){$priority+=25;}
	if ($matches['ToType']=='50 mil'){$priority+=10;}
	$counts[$priority]++;	//Store the count of each type of priority

	$query = "INSERT DELAYED INTO wiringdata (`wjid`,`WireNo`,`From`,`To`,`FromX`,`FromY`,`ToX`,`ToY`,`Length`,`Gague`,`Colour`,`FromType`,`ToType`,`Name`,`Top`,`fromCalib`,`toCalib`,`priority`) VALUES ('".$wjid."','".$wireno++."','".$matches['FromName']."','".$matches['ToName']."','".$matches['FromX']."','".$matches['FromY']."','".$matches['ToX']."','".$matches['ToY']."','".$matches['Length']."','".$matches['Gague']."','".$matches['Colour']."','".$matches['FromType']."','".$matches['ToType']."','".$matches['Device']."','".$matches['Top']."','0','0','".$priority."');";
	if (!SQLQuery($query)){echo "SQL Error : ".SQLError();}
//	echo $wireno." (".(microtime(true) - $time_start).") [".($wireno/(microtime(true) - $time_start))."/s]<br/>\n";
//	flush();
}

//Insert extra data into jobs database
$wireno=0;
foreach ($extras as $matches){
	$query = "INSERT DELAYED INTO wiringdataextras (`wjid`,`data`,`print`) VALUES ('".$wjid."','".$matches['data']."','".$matches['print']."');";
	if (!SQLQuery($query)){echo "SQL Error : ".SQLError();}
}

//Add non-wire extra data
switch ($header['Size']){
	case 1:
		$query = "INSERT DELAYED INTO wiringdataextras (`wjid`,`data`,`print`) VALUES ('".$wjid."','Fixture Size : Bank 1','1');";
		if (!SQLQuery($query)){echo "SQL Error : ".SQLError();}

		$query = "INSERT DELAYED INTO wiringdataextras (`wjid`,`data`,`print`) VALUES ('".$wjid."','S1','0');";
		if (!SQLQuery($query)){echo "SQL Error : ".SQLError();}
		break;

	case 2:
		$query = "INSERT DELAYED INTO wiringdataextras (`wjid`,`data`,`print`) VALUES ('".$wjid."','Fixture Size : Bank 2','1');";
		if (!SQLQuery($query)){echo "SQL Error : ".SQLError();}

		$query = "INSERT DELAYED INTO wiringdataextras (`wjid`,`data`,`print`) VALUES ('".$wjid."','S2','0');";
		if (!SQLQuery($query)){echo "SQL Error : ".SQLError();}
		break;

	case 3:
		$query = "INSERT DELAYED INTO wiringdataextras (`wjid`,`data`,`print`) VALUES ('".$wjid."','Fixture Size : Full','1');";
		if (!SQLQuery($query)){echo "SQL Error : ".SQLError();}

		$query = "INSERT DELAYED INTO wiringdataextras (`wjid`,`data`,`print`) VALUES ('".$wjid."','S3','0');";
		if (!SQLQuery($query)){echo "SQL Error : ".SQLError();}
		break;
}

//Store 'top' information
if ($header['Top']!='NO'){
	$query = "INSERT DELAYED INTO wiringdataextras (`wjid`,`data`,`print`) VALUES ('".$wjid."','Top Wiring','1');";
	if (!SQLQuery($query)){echo "SQL Error : ".SQLError();}
}

//Store the prioriy counts
for ($i=0; $i<300; $i++){
	if ($counts[$i]>0){
		$query = "INSERT DELAYED INTO wiringdataextras (`wjid`,`data`,`print`) VALUES ('".$wjid."','C".$i."=".$counts[$i]."','0');";
		if (!SQLQuery($query)){echo "SQL Error : ".SQLError();}
	}
}
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


/*
//Store wire results in database
foreach ($wires as $matches){
	//Calculate the physical co-ordinates based on the HP BRC values
	$matches['FromX'] = (((($matches['FromColumn']+(($matches['FromBank']==2)?95:0))*-1500)+279111)*-0.002545333333)+741.6966045;
	$matches['FromY'] = ((($matches['FromRow']*7000)-88725)*0.002544285714)+241.17975;
	$matches['ToX'] = (((($matches['ToColumn']+(($matches['ToBank']==2)?95:0))*-1500)+279111)*-0.002545333333)+741.6966045;
	$matches['ToY'] = ((($matches['ToRow']*7000)-88725)*0.002544285714)+241.17975;
	
	$matches['From']=$matches['FromBank']." ".$matches['FromRow']." ".$matches['FromColumn'];
	$matches['To']=$matches['ToBank']." ".$matches['ToRow']." ".$matches['ToColumn'];

	$query = "INSERT INTO TMP_Wiring (`jid`,`From`,`To`,`FromX`,`FromY`,`ToX`,`ToY`,`Length`,`Gague`,`Colour`,`FromType`,`ToType`,`Name`,`WireNo`) VALUES ('".$jid."','".$matches['From']."','".$matches['To']."','".$matches['FromX']."','".$matches['FromY']."','".$matches['ToX']."','".$matches['ToY']."','".$matches['Length']."','".$matches['Gague']."','".$matches['Colour']."','".$matches['FromType']."','".$matches['ToType']."','".$matches['Device']."','".$wireno++."');";

	if (!SQLQuery($query)){echo "SQL Error : ".SQLError();}
}


/*
//Store wire extra results in database
foreach ($wireextras as $matches){
	$x = (((($matches['Column']+(($matches['Bank']==2)?95:0))*-1500)+279111)*-0.002545333333)+741.6966045;
	$y = ((($matches['Row']*7000)-88725)*0.002544285714)+241.17975;

	$name=$matches['Bank']." ".$matches['Row']." ".$matches['Column'];
	$query = "INSERT INTO WiringDataExtras (`jid`,`X`,`Y`,`type`,`name`) VALUES ('".$jid."','".$x."','".$y."','extra','".$name."');";

	if (!SQLQuery($query)){echo "SQL Error : ".SQLError();}
}

*/
echo "<u>Data Entereted</u><BR/>";

$time = microtime(true) - $time_start;
echo "Execution time : $time seconds\n";


?>