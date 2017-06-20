<html>
<head>
	<title>Forwessun Production</title>
	<link rel="stylesheet" title="Standard" type="text/css" href="admin.css" />
	<!-- <link rel="alternate stylesheet" title="White" type="text/css" href="white.css" /> -->
	<script type="text/javascript" src="ftp.js"></script>
</head>
<body onload="showlogin()" onresize="showlogin()">

	<!-- BEGIN (no)CSS ERROR MESSAGE -->
	<DIV class='nocss'>
		<H1>CSS Error!</H1>
		<H1>Cascading Style Sheets (CSS) appears to be disabled or otherwise not working in your browser. This page requires CSS to function. Please enable CSS and try again. If you do not understand this message, please seek assistance.</H1>
		<BR /><HR /><BR /><BR /><BR /><BR /><BR /><BR /><BR /><BR />
	</DIV>
	<!-- END (no)CSS ERROR MESSAGE -->

	<H2>Forwessun Production</H2>

	<DIV id='login' class='dropshadow'>
		<DIV class='login'>
			<DIV class='tasktitle'>User Login</DIV>
			<FORM METHOD=POST ACTION="">
				<FIELDSET>
					<OL>
						<LI><LABEL for="login_username">Username :</LABEL><INPUT id="login_username" TYPE="text" NAME="Username" /></LI>						
						<LI><LABEL for="login_password">Password :</LABEL><INPUT id="login_password" TYPE="password" NAME="Password" /></LI>
					</OL>
				</FIELDSET>
				<INPUT id='login_submit' TYPE="submit" VALUE='Log in'>
				<?php if (isset($loginfail)){echo "<DIV class='loginfail'>Incorrect Username/Password</DIV>";} ?>
				<?php if (isset($login_permissiondenied)){echo "<DIV class='loginfail'>Login Correct, but Permission Denied</DIV>";} ?>
			</FORM>
		</DIV>
	</DIV>
</body>
</html>