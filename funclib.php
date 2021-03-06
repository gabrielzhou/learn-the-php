<?php

/*
TODO:
	Web:
		User Admin
		Machine Admin

	Machine:
		Download latest version

	Total:
		Installs and docs
*/


$mysqli;

date_default_timezone_set("Asia/Shanghai");

function SQLConnect(){
	global $mysqli;
	$server="10.4.56.3";
	$user="ims";
	$pass='ims';
	$db='ims';

	$mysqli=new mysqli($server, $user, $pass, $db);

	if ($mysqli->connect_error){
		return false;
		//die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
	}

	return true;
}

function SQLClose(){
	global $mysqli;
	$mysqli->close();
	unset($mysqli);
}

function SQLQuery($query){
	global $mysqli;
	return $mysqli->query($query);
}

function SQLRowCount($res){
	return $res->num_rows;
}

function SQLGetRow($res){
	return $res->fetch_assoc();
}

function SQLFreeResult($res){
	global $mysqli;
	while(mysqli_more_results($mysqli)) { 
		if(mysqli_next_result($mysqli)){ 
			if ($result = mysqli_use_result($mysqli)) mysql_free_result($result); 
		} 
	}

	if ($res){
		return $res->free();
	}
}

function SQLError(){
	global $mysqli;
	return "[".$mysqli->errno . "] " . $mysqli->error."\n";
}

function formatSeconds($seconds){
	if ($seconds==0){return "Zero";}
	$minutes=$seconds/60;
	
	//See if we're talking days
	$var="";
	$days=0;
	$hours=0;
	if ($minutes>3600){$days=floor($minutes/1440); $minutes%=1440;}
	if ($minutes>60){$hours=floor($minutes/60); $minutes%=60;}

	if ($days>0){
		return sprintf("%udays, %u:%02u",$days,$hours,$minutes);
	}else{
		return sprintf("%u:%02u",$hours,$minutes);
	}
}

function formatSecondsRemaining($seconds){
	if ($seconds==0){return "Complete";}
	$minutes=$seconds/60;
	
	//See if we're talking days
	$var="";
	$days=0;
	$hours=0;
	if ($minutes>3600){$days=floor($minutes/1440); $minutes%=1440;}
	if ($minutes>60){$hours=floor($minutes/60); $minutes%=60;}

	if ($days>0){
		return sprintf("%udays, %u:%02u",$days,$hours,$minutes);
	}else{
		return sprintf("%u:%02u",$hours,$minutes);
	}
}

function addWorkingMins($mins){
	$mr=$mins;
	$now = strtotime("now");
	$start_of_day = 32400;	//9 * 60 * 60;
	$end_of_day = 61200;	//17 * 60 * 60;
	$working_day_mins=480;
	$working_day=$end_of_day-$start_of_day;
	$one_day = 86400;		//24 * 60 * 60;
	$date = strtotime(date('Y-m-d', $now));
	$dow = date('N', $now);

	while($mr>0){
		if ($dow<6){
			if ($now>($date+$end_of_day)){
				$now=$date+$one_day+$start_of_day;	//Move to the start of the next day
				$date = strtotime(date('Y-m-d', $now));
				$dow = date('N', $now);
			}else if ($now<($date+$start_of_day)){
				$now=$date+$start_of_day;	//Move to the working start of the day
			}

			$hours_remain=($date+$end_of_day)-$now;
			$mr-=$hours_remain/60;
			$now+=$hours_remain;

			if ($mr<0){
				$now-=-$mr*60; $mr=0;
				break;
			}else{
				//Move to the next day
				$now=$date+$one_day+$start_of_day;	//Move to the start of the next day
				$date = strtotime(date('Y-m-d', $now));
				$dow = date('N', $now);
			}
		}else{
			$now=$date+$one_day+$start_of_day;	//Move to the start of the next day
			$date = strtotime(date('Y-m-d', $now));
			$dow = date('N', $now);
		}
	}

	return $now;
}

function checkPermission($uid, $permission){
$query="SELECT 1 as granted FROM (
SELECT description FROM group_permissions JOIN permissions ON group_permissions.pid=permissions.pid JOIN (SELECT gid FROM group_membership WHERE uid=".$uid.") as A ON group_permissions.gid=A.gid
UNION
SELECT description FROM user_permissions JOIN permissions ON user_permissions.pid=permissions.pid WHERE uid=".$uid."
UNION
SELECT description FROM permissions WHERE (SELECT 1 FROM group_membership WHERE uid=".$uid." AND gid=1)=1
) AS B WHERE description='".$permission."';";
	$res=SQLQuery($query);
	$rowcount=SQLRowCount($res);
	return ($rowcount>0);
}

?>
