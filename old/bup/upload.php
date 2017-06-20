<?php

$time_start = microtime(true);

//Get POST parameters
$jid=$_POST['job'];

ini_set("memory_limit","50M");

include("funclib.php");

//Check we have enough files
if (count($_FILES)<2){echo "NOT ENOUGH FILES"; exit;}

//Establish Database connection
if (!SQLConnect()){echo "SQL Error : ".SQLError();exit;}

//Execute the command to combine the data
$command = './wicombine "'.$_FILES["wfile"]["tmp_name"].'" "'.$_FILES["ifile"]["tmp_name"].'" "/tmp/wicombined"';
exec($command, $output, $result);
if ($result!=0){echo "Failed to Integrate Information : ".implode($output)."<br />\n"; exit;}
unset($output);

//Get the combined data
exec("cat /tmp/wicombined",$output,$result);
if ($result!=0){echo "Failed to Retrieve Integrated Information : ".implode($output)."<br />\n"; exit;}

/*
foreach ($output as $line_num => $line){
	if ($line_num%2==0){
		echo "<b>".$line."</b><br />\n";
	}else{
		echo $line."<br />\n";
	}
}*/

SQLQuery("DELETE FROM WiringJobs WHERE JID='".$_POST['job']."';");
SQLQuery("DELETE FROM WiringData WHERE JID='".$_POST['job']."';");
SQLQuery("DELETE FROM WiringDataExtra WHERE JID='".$_POST['job']."';");

$wires=array();
$wireextras=array();

//Parse the returnd data
foreach ($output as $line_num => $line){
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
				$wires[]=$matches;
			}else{
				echo "Malformed Integrated Information : ".$line."<br />\n"; exit;
			}
			break;

		case 'X':	//Extra
			if (preg_match("/.(?<X>[\d\.]*),(?<Y>[\d\.]*),(?<Type>[^,]*),(?<Name>[^,]*)/",$line,$matches)){
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
echo "<PRE>";
//print_r($wires);
echo "</PRE>";
echo "<hr />";
echo "<PRE>";
//print_r($extras);
echo "</PRE>";

//Insert header info into jobs database
$query = "INSERT INTO WiringJobs (`jid`,`size`,`wirecount`,`extracount`,`lowX`,`lowY`,`hiX`,`hiY`) VALUES ('".$jid."','".$header['Size']."','".$header['Wires']."','".$header['Extras']."','".$header['LowX']."','".$header['LowY']."','".$header['HiX']."','".$header['HiY']."');";

if (!SQLQuery($query)){echo "SQL Error : ".SQLError();}

/*
//Read in wires files
$lines=file($_FILES["wfile"]["tmp_name"]);
$wireno=1;
$top=false;
$jobsize=0;
$automatic=false;
foreach ($lines as $line_num => $line){
	if (strstr($line,"Wiring Method : Automatic")){$automatic=true;}
	if (strstr($line,"Fixture Size : Bank 1")){$jobsize=1;}
	if (strstr($line,"Fixture Size : Bank 2")){$jobsize=2;}
	if (strstr($line,"Fixture Size : Full")){$jobsize=3;}
	if (strstr($line,"*+*+* Top *+*+*")){$top=true;}

	if (preg_match("/^\s*\S(?<Bank>\d)\s+(?<Row>[\d\.]+)\s+(?<Column>[\d\.]+)\D/",$line,$matches)){
		$wireextras[]=$matches;
	}else{
		//Check for and parse a Wire line
		if ($automatic){
			if (preg_match( "/^\s*(?P<Length>[\d\.]+)\s+(?P<Gague>\d+)\s+(?P<Colour>\S+)\s+(?P<FromBracket>\S)(?P<FromBank>\d)\s+(?P<FromRow>[\d\.]+)\s+(?P<FromColumn>[\d\.]+)\D\s+(?P<ToBracket>\S)(?P<ToBank>\d)\s+(?P<ToRow>[\d\.]+)\s+(?P<ToColumn>[\d\.]+)\D/",$line,$matches)){
				$matches['FromType']="?";
				$matches['ToType']="?";
				$matches['Device']="?";
				$wires[]=$matches;
			}
		}else{
			if (preg_match( "/^\s*(?P<Length>[\d\.]+)\s+(?P<Gague>\d+)\s+(?P<Colour>\S+)\s+(?P<FromType>\S+)\s+(?P<FromBracket>\S)(?P<FromBank>\d)\s+(?P<FromRow>[\d\.]+)\s+(?P<FromColumn>[\d\.]+)\D\s+.\s+(?P<ToType>\S+)\s+(?P<ToBracket>\S)(?P<ToBank>\d)\s+(?P<ToRow>[\d\.]+)\s+(?P<ToColumn>[\d\.]+)\D\s+.(?P<Device>.*)/",$line,$matches)){
				$wires[]=$matches;
			}
		}
	}
}

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

$matches=0;
//Extract and store the inserts file
$lines=file($_FILES["ifile"]["tmp_name"]);
foreach ($lines as $line_num => $line){
	if (preg_match("/^[\[\(](?<Bank>\d)\s+(?<Row>[\d\.]+)\s+(?<Column>[\d\.]+)\D/",$line,$matches)){
		$matches['X']=(((($matches['Column']+(($matches['Bank']==2)?95:0))*-1500)+279111)*-0.002545333333)+741.6966045;
		$matches['Y']=((($matches['Row']*7000)-88725)*0.002544285714)+241.17975;
		$matches['Type']=substr($line,32,8);
		$matches['Name']=substr($line,48);
		$inserts[]=$matches;

		$query = "INSERT INTO TMP_Inserts (`X`,`Y`,`Type`,`Name`) VALUES ('".$matches['X']."','".$matches['Y']."','".$matches['Type']."','".$matches['Name']."');";
		
		if (!SQLQuery($query)){echo "SQL Error : ".SQLError();}
	}
}

echo $matches." matches<br/>\n";

//Integrate the wires and inserts
$query="INSERT INTO WiringData (`jid`,`From`,`To`,`FromX`,`FromY`,`ToX`,`ToY`,`Length`,`Gague`,`Colour`,`FromType`,`ToType`,`Name`,`WireNo`) SELECT TMP_Wiring.`jid`,TMP_Wiring.`From`,TMP_Wiring.`To`,TMP_Wiring.`FromX`,TMP_Wiring.`FromY`,TMP_Wiring.`ToX`,TMP_Wiring.`ToY`,TMP_Wiring.`Length`,TMP_Wiring.`Gague`,TMP_Wiring.`Colour`,TMP_Inserts.`Type`,TMP_Wiring.`ToType`,TMP_Inserts.`Name`,TMP_Wiring.`WireNo` FROM TMP_Wiring LEFT JOIN TMP_Inserts ON ((TMP_Inserts.X=TMP_Wiring.FromX) AND (TMP_Inserts.Y=TMP_Wiring.FromY))";
if (!SQLQuery($query)){echo "SQL Error : ".SQLError();}


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
echo "Fixture Size : ".$jobsize."<BR/>";
echo "Method : ".($automatic?"AUTO":"MANUAL")."<BR/>";

$time_end = microtime(true);
$time = $time_end - $time_start;
echo "Execution time : $time seconds\n";


?>