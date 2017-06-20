<?php

include 'funclib.php';

session_start();

SQLConnect();

//Check for logout attempts
if (isset($_GET['action']) && ($_GET['action']=='logout')){	
	$_SESSION = array();	// Unset all of the session variables.

	if (ini_get("session.use_cookies")) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$params["path"], $params["domain"],
			$params["secure"], $params["httponly"]
		);
	}
	
	session_destroy();	// Finally, destroy the session.

	session_start();	// Create the new session
}

//Check for login attempts
if (isset($_POST['Username']) && isset($_POST['Password'])){
	$res=SQLQuery("SELECT * FROM users WHERE username='".$_POST['Username']."' AND password=md5('".$_POST['Password']."');");
	if (SQLRowCount($res)>0){
		$line = SQLGetRow($res);
		if (checkPermission($line['uid'],"Log On")){
			$_SESSION['uid']=$line['uid'];
			$_SESSION['username']=$line['username'];
			header('Location: index.php');
		}else{
			$login_permissiondenied=true;
		}
	}else{
		$loginfail=true;
	}
}

//Check the user is logged in
if (!isset($_SESSION['uid']) || $_SESSION['uid']==""){
	include("login.php");
}else{ 
	include("main.php");
}

?>
