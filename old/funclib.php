<?php

function SQLConnect(){
	$server="localhost";
	$user="ims";
	$pass='ims';
	$db='ims';

	if (!(@mysql_connect($server,$user,$pass))){return false;}
	if (!(@mysql_select_db($db))){return false;}

	return true;
}

function SQLClose(){
	return @mysql_close();
}

function SQLQuery($query){
	return @mysql_query($query);
}

function SQLGetRow($res){
	return @mysql_fetch_assoc($res);
}

function SQLError(){
	return "[".mysql_errno() . "] " . mysql_error()."\n";
}

?>