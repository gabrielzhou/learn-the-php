<?php

global $mysql_link;

function SQLConnect(){
	$server="10.4.56.3";
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

function SQLCreateTempWiring(){
	$query="CREATE TEMPORARY TABLE  `ims`.`TMP_Wiring` (
  `wdid` int(10) unsigned NOT NULL auto_increment,
  `jid` int(10) unsigned NOT NULL,
  `WireNo` int(10) unsigned NOT NULL,
  `From` varchar(45) default NULL,
  `To` varchar(45) default NULL,
  `FromX` float default NULL,
  `FromY` float default NULL,
  `ToX` float default NULL,
  `ToY` float default NULL,
  `Length` float default NULL,
  `Gague` varchar(45) default NULL,
  `Colour` varchar(45) default NULL,
  `FromType` varchar(45) default NULL,
  `ToType` varchar(45) default NULL,
  `Name` varchar(45) default NULL,
  PRIMARY KEY  (`wdid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;";
	return SQLQuery($query);
}

function SQLCreateTempInserts(){
	$query="CREATE TEMPORARY TABLE  `ims`.`TMP_Inserts` (
  `wdid` int(10) unsigned NOT NULL auto_increment,	
  `X` float default NULL,
  `Y` float default NULL,
  `Type` varchar(45) default NULL,
  `Name` varchar(45) default NULL,
  PRIMARY KEY  (`wdid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;";
	return SQLQuery($query);
}

?>