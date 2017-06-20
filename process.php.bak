<?php

session_start();
ini_set("memory_limit","128M");

include 'funclib.php';

SQLConnect();

if ($_POST['action']=='addjob'){
	$res=SQLQuery("SELECT * FROM jobs WHERE name='".$_POST['addjob_name']."';");

	if (SQLRowCount($res)>0){
		echo "Job '".$_POST['addjob_name']."' already exists. Job names must be unique.";
	}else{
		$res=SQLQuery("INSERT INTO jobs (name, type, created, createdby_uid) VALUES ('".$_POST['addjob_name']."','".$_POST['addjob_type']."',now(),'".$_SESSION['uid']."');");

		echo "ar_refresh";
	}
}

if ($_POST['action']=='edit'){
	$res=execSQL("SELECT username FROM users WHERE uid=".$_POST['uid']." LIMIT 1;");
	$username='<UNKNOWN>';
	if (mysql_num_rows($res)>0){
		$line = mysql_fetch_array($res);
		$username = $line['username'];
	}

	if ($_POST['password']!="<Click to edit>"){
		$res=execSQL("UPDATE users SET password=encrypt('".$_POST['password']."'), realname='".$_POST['real']."', company='".$_POST['company']."' WHERE uid=".$_POST['uid'].";");		
	}else{
		$res=execSQL("UPDATE users SET realname='".$_POST['real']."', company='".$_POST['company']."' WHERE uid=".$_POST['uid'].";");
	}

	$res=execSQL("INSERT INTO log (message,username,host,time) VALUES ('USER MODIFIED (".$_SESSION['username'].")','".$username."','".$_SERVER['REMOTE_ADDR']."',now());");
}

if ($_POST['action']=='enable'){
	$res=execSQL("SELECT username FROM users WHERE uid=".$_POST['uid']." LIMIT 1;");
	if (mysql_num_rows($res)>0){
		$line = mysql_fetch_array($res);
		if ($_POST['state']==1){
			$res=execSQL("INSERT INTO log (message,username,host,time) VALUES ('USER ENABLED (".$_SESSION['username'].")','".$line['username']."','".$_SERVER['REMOTE_ADDR']."',now());");
		}else{
			$res=execSQL("INSERT INTO log (message,username,host,time) VALUES ('USER DISABLED (".$_SESSION['username'].")','".$line['username']."','".$_SERVER['REMOTE_ADDR']."',now());");
		}

		$res=execSQL("UPDATE users SET enabled=".$_POST['state']." WHERE uid=".$_POST['uid'].";");
	}
}

if ($_POST['action']=='deletejob'){
	//Get the wjid (if presnet)
	$wjid=NULL;
	$res=SQLQuery("SELECT wjid FROM wiringjobs WHERE jid='".$_POST['jid']."';");
	if (SQLRowCount($res)>0){
		$row=SQLGetRow($res);
		$wjid=$row['wjid'];
	}
	
	//Delete the job
	SQLQuery("DELETE FROM jobs WHERE jid=".$_POST['jid']." LIMIT 1;");

	if (!is_null($wjid)){
		//Delete the wiringjob
		SQLQuery("DELETE FROM wiringjobs WHERE wjid=".$wjid." LIMIT 1;");

		//Delete wiring data
		SQLQuery("DELETE FROM wiringdata WHERE wjid=".$wjid.";");

		//Delete wiring extras
		SQLQuery("DELETE FROM wiringdataextras WHERE wjid=".$wjid.";");

		//Delete progress
		SQLQuery("DELETE FROM wiringprogress WHERE wjid=".$wjid.";");

		//Delete calibrations
		SQLQuery("DELETE FROM wiringcalibration WHERE wjid=".$wjid.";");
	}

	echo "ar_refresh";
}

?>