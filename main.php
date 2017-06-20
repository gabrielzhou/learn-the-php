<html>
<head>
	<title>Forwessun Production</title>
	<link rel="stylesheet" title="Standard" type="text/css" href="admin.css" />
	<!-- <link rel="alternate stylesheet" title="White" type="text/css" href="white.css" /> -->
	<script type="text/javascript" src="ftp.js"></script>
</head>
<body onload='mainload()' onresize='resizefunc()'>

<?php

$oddline=false;

?>
	<!-- BEGIN (no)CSS ERROR MESSAGE -->
	<DIV class='nocss'>
		<H1>CSS Error!</H1>
		<H1>Cascading Style Sheets (CSS) appears to be disabled or otherwise not working in your browser. This page requires CSS to function. Please enable CSS and try again. If you do not understand this message, please seek assistance.</H1>
		<BR /><HR /><BR /><BR /><BR /><BR /><BR /><BR /><BR /><BR />
	</DIV>
	<!-- END (no)CSS ERROR MESSAGE -->

	<H2>Forwessun Production</H2>

	<!-- BEGIN BLOCKING DIVISION -->
	<DIV id='fadediv' class='fadediv'>&nbsp;</DIV>
	<!-- END BLOCKING DIVISION -->

	<!-- BEGIN SIGNATURE DIVISION -->
	<DIV class='kasper'><IMG SRC='images/kasper.png'></DIV>
	<!-- END SIGNATURE DIVISION -->

	<!-- BEGIN WORKING DIVISION -->
	<DIV id='working' class='working'><IMG SRC='images/loading_big.gif'></DIV>
	<!-- END WORKING DIVISION -->

	<!-- BEGIN TASK PANEL -->
	<DIV class='taskpanel'>
		<DIV class='tasktitle'>Tasks</DIV>
		<DIV id='listtasks' class='tasklist'>
			<?php
			if (checkPermission($_SESSION['uid'],"Add Jobs")) echo("<A onclick='addjobvisible(true)'><IMG SRC='images/add.png'/>Add Job</A><br />");
			echo "<A onclick='ajax_refresh(); resizefunc();'><IMG SRC='images/refresh.png'/>Refresh list</A><br />";
			echo "<hr /><A onClick='window.location.replace(\"index.php?action=logout\")'><IMG SRC='images/key.png'/>Logout (".$_SESSION['username'].")</A>"; ?>
		</DIV>
		<DIV id='infotasks' class='tasklist'>
			<?php
			echo("<A onclick='showUpload()'><IMG SRC='images/upload.png'/>Upload Data</A><br />");
			echo("<A onclick='downloadEfile()'><IMG SRC='images/download.png'/>Download E-File</A><br />");
			echo("<A onclick='ajax_jobview(document.getElementById(\"selected_job_jid\").innerHTML)'><IMG SRC='images/mag_glass.png'/>View/Rotate</A><br />");
			if (checkPermission($_SESSION['uid'],"Delete Jobs")) echo("<A onclick='showdeleteselectedjob();'><IMG SRC='images/cross.png'/>Delete Job</A><br />");
			echo "<A onclick='showWait(true); ajax_details(document.getElementById(\"selected_job_jid\").innerHTML); resizefunc();'><IMG SRC='images/refresh.png'/>Refresh Details</A><br />";
			echo "<hr /><A onclick='hideJobInfo(); ajax_refresh(); resizefunc();'><IMG SRC='images/log.png'/>View Jobs</A><br />";
			echo "<hr /><A onClick='window.location.replace(\"index.php?action=logout\")'><IMG SRC='images/key.png'/>Logout (".$_SESSION['username'].")</A>"; ?>
		</DIV>

		<DIV id='viewtasks' class='tasklist'>
			<?php
			echo "<A onclick='showWait(true); ajax_details(document.getElementById(\"selected_job_jid\").innerHTML); resizefunc();'><IMG SRC='images/back.png'/>Back to job</A><br />";
			echo "<hr /><A onclick='hideJobInfo(); resizefunc();'><IMG SRC='images/log.png'/>View Jobs</A><br />";
			echo "<hr /><A onClick='window.location.replace(\"index.php?action=logout\")'><IMG SRC='images/key.png'/>Logout (".$_SESSION['username'].")</A>"; ?>
		</DIV>
	</DIV>
	<!-- END TASK PANEL -->

	<!-- BEGIN ADD JOB DIALOG -->
	<DIV id='addjob' class='dropshadow'>
		<DIV class='addjob'>
			<DIV class='tasktitle'>Add Job</DIV>
			<FORM METHOD=POST onsubmit="return false;">
				<FIELDSET>
					<LEGEND>Details</LEGEND>
					<OL>
						<LI><LABEL for="addjob_name">Job Name</LABEL><INPUT id="addjob_name" TYPE="text" NAME="Real" onchange='check_addjob_name()' onkeyup='check_addjob_name()' /><DIV class='validity' id='addjob_namevalid'>Invalid Name</DIV></LI>
						<LI><LABEL for="addjob_type">Job Type</LABEL>
						<SELECT id="addjob_type" NAME="addjob_type">
						<OPTION VALUE="HP">HP</OPTION>
						<OPTION VALUE="Genrad">Genrad</OPTION>
						<OPTION VALUE="Teradyne">Teradyne</OPTION>
						<OPTION VALUE="Spectrum">Spectrum</OPTION>
						<OPTION VALUE="Other">Other</OPTION>
						</SELECT>
						</LI>
					</OL>
				</FIELDSET>
				
				<INPUT id='addjob_submit' TYPE="submit" VALUE='Add Job' onclick="addJob(); return false;">&nbsp;<INPUT TYPE="submit" VALUE='Cancel' onclick="addjobvisible(false); return false;">
			</FORM>
		</DIV>
	</DIV>
	<!-- END ADD JOB DIALOG -->

	<!-- BEGIN DELETE JOB DIALOG -->
	<DIV id='deljob' class='dropshadow'>
		<DIV class='deljob'>		
			<DIV class='tasktitle'>Delete Job</DIV>
			<FORM METHOD=POST onsubmit="return false;">
				<FIELDSET>
					<OL>
						<LI><LABEL for="deljob_name">Name :</LABEL><INPUT disabled="disabled" id="deljob_name" TYPE="text" NAME="Name" /></LI>
						<LI><LABEL for="deljob_type">Type :</LABEL><INPUT disabled="disabled" id="deljob_type" TYPE="text" NAME="Type" /></LI>
					</OL>
				</FIELDSET>
				<INPUT id='deljob_jid' TYPE="hidden" NAME="jid" VALUE="">

				Are you sure you wish to delete this job?<BR />Type 'DELETE' to confirm.<BR /><DIV class='warning'>This action cannot be undone.</DIV><BR />

				<INPUT id='delete_confirm' TYPE="text" size='6' maxlength='6' onchange='check_delete_confirm()' onkeyup='check_delete_confirm()' />
				<INPUT id='delete_submit' TYPE="submit" VALUE='Delete Job' onclick="delJob(); return false;">&nbsp;<INPUT TYPE="submit" VALUE='Cancel' onclick="hidedeletejob(); return false;">
			</FORM>
		</DIV>
	</DIV>
	<!-- END DELETE JOB DIALOG -->

	<!-- BEGIN HP UPLOAD FORM -->
	<DIV id='uploadhp' class='dropshadow'>
		<DIV class='deljob'>		
			<DIV class='tasktitle'>Upload HP Data</DIV>
			<FORM name='upload_hp_form' METHOD=POST enctype="multipart/form-data" onSubmit="uploading();" target="upload_target_hp" action="upload_hp.php">
				<FIELDSET>
					<OL>
						<LI><label for="wfile">Wires File:</label><input type="file" name="wfile" id="wfile" /></LI>
						<LI><label for="ifile">Inserts File:</label><input type="file" name="ifile" id="ifile" /></LI>
					</OL>
				</FIELDSET>
				<INPUT id='uploadhp_jid' TYPE="hidden" NAME="jid" VALUE="">
	
				<INPUT id='uploadhp_submit' TYPE="submit" VALUE='Upload Data'>&nbsp;<INPUT id='uploadhp_cancel' TYPE="submit" VALUE='Cancel' onclick="hideUploads(false); return false;"><INPUT id='uploadhp_close' TYPE="submit" VALUE='Close' onclick="hideUploads(true); return false;">
				<DIV id='uploadhpprogress' class='uploadprogress'><IMG src='images/upload.gif'></DIV>
			</FORM>
			<IFRAME class='uploadiframe' id="upload_target_hp" name="upload_target_hp"></IFRAME>
		</DIV>
	</DIV>
	<!-- END HP UPLOAD FORM -->

	<!-- BEGIN GENRAD UPLOAD FORM -->
	<DIV id='uploadgenrad' class='dropshadow'>
		<DIV class='deljob'>		
			<DIV class='tasktitle'>Upload Genrad Data</DIV>
			<FORM name='upload_genrad_form' METHOD=POST enctype="multipart/form-data" onSubmit="uploading();" target="upload_target_gr" action="upload_genrad.php">
				<FIELDSET>
					<OL>
						<LI><label for="gfile">NAE File:</label><input type="file" name="gfile" id="gfile" /></LI>
					</OL>
				</FIELDSET>
				<INPUT id='uploadgenrad_jid' TYPE="hidden" NAME="jid" VALUE="">
	
				<INPUT id='uploadgenrad_submit' TYPE="submit" VALUE='Upload Data'>&nbsp;<INPUT id='uploadgenrad_cancel' TYPE="submit" VALUE='Cancel' onclick="hideUploads(false); return false;"><INPUT id='uploadgenrad_close' TYPE="submit" VALUE='Close' onclick="hideUploads(true); return false;">
				<DIV id='uploadgenradprogress' class='uploadprogress'><IMG src='images/upload_ready.gif'></DIV>
			</FORM>
			<IFRAME class='uploadiframe' id="upload_target_gr" name="upload_target_gr"></IFRAME>
		</DIV>
	</DIV>
	<!-- END GENRAD UPLOAD FORM -->

	<!-- BEGIN TERADYNE UPLOAD FORM -->
	<DIV id='uploadteradyne' class='dropshadow'>
		<DIV class='deljob'>		
			<DIV class='tasktitle'>Upload Teradyne Data</DIV>
			<FORM name='upload_teradyne_form' METHOD=POST enctype="multipart/form-data" onSubmit="uploading();" target="upload_target_tr" action="upload_teradyne.php">
				<FIELDSET>
					<OL>
						<LI><label for="tfile">NAE File:</label><input type="file" name="tfile" id="tfile" /></LI>
					</OL>
				</FIELDSET>
				<INPUT id='uploadteradyne_jid' TYPE="hidden" NAME="jid" VALUE="">
	
				<INPUT id='uploadteradyne_submit' TYPE="submit" VALUE='Upload Data'>&nbsp;<INPUT id='uploadteradyne_cancel' TYPE="submit" VALUE='Cancel' onclick="hideUploads(false); return false;"><INPUT id='uploadteradyne_close' TYPE="submit" VALUE='Close' onclick="hideUploads(true); return false;">
				<DIV id='uploadteradyneprogress' class='uploadprogress'><IMG src='images/upload_ready.gif'></DIV>
			</FORM>
			<IFRAME class='uploadiframe' id="upload_target_tr" name="upload_target_tr"></IFRAME>
		</DIV>
	</DIV>
	<!-- END TERADYNE UPLOAD FORM -->

	<!-- BEGIN SPECTRUM UPLOAD FORM -->
	<DIV id='uploadspectrum' class='dropshadow'>
		<DIV class='deljob'>		
			<DIV class='tasktitle'>Upload Spectrum Data</DIV>
			<FORM name='upload_spectrum_form' METHOD=POST enctype="multipart/form-data" onSubmit="uploading();" target="upload_target_tr" action="upload_spectrum.php">
				<FIELDSET>
					<OL>
						<LI><label for="tfile">NAE File:</label><input type="file" name="tfile" id="tfile" /></LI>
					</OL>
				</FIELDSET>
				<INPUT id='uploadspectrum_jid' TYPE="hidden" NAME="jid" VALUE="">
	
				<INPUT id='uploadspectrum_submit' TYPE="submit" VALUE='Upload Data'>&nbsp;<INPUT id='uploadspectrum_cancel' TYPE="submit" VALUE='Cancel' onclick="hideUploads(false); return false;"><INPUT id='uploadspectrum_close' TYPE="submit" VALUE='Close' onclick="hideUploads(true); return false;">
				<DIV id='uploadspectrumprogress' class='uploadprogress'><IMG src='images/upload_ready.gif'></DIV>
			</FORM>
			<IFRAME class='uploadiframe' id="upload_target_tr" name="upload_target_tr"></IFRAME>
		</DIV>
	</DIV>
	<!-- END SPECTRUM UPLOAD FORM -->

	<!-- BEGIN OTHER UPLOAD FORM -->
	<DIV id='uploadother' class='dropshadow'>
		<DIV class='deljob'>		
			<DIV class='tasktitle'>Upload Other Data</DIV>
			<FORM name='upload_other_form' METHOD=POST enctype="multipart/form-data" onSubmit="uploading();" target="upload_target_ot" action="upload_other.php">
				<FIELDSET>
					<OL>
						<LI><label for="ofile">Coords File 1:</label><input type="file" name="ofile" id="ofile" /></LI>
					</OL>
				</FIELDSET>
				<INPUT id='uploadother_jid' TYPE="hidden" NAME="jid" VALUE="">
	
				<INPUT id='uploadother_submit' TYPE="submit" VALUE='Upload Data'>&nbsp;<INPUT id='uploadother_cancel' TYPE="submit" VALUE='Cancel' onclick="hideUploads(false); return false;"><INPUT id='uploadother_close' TYPE="submit" VALUE='Close' onclick="hideUploads(true); return false;">
				<DIV id='uploadotherprogress' class='uploadprogress'><IMG src='images/upload_ready.gif'></DIV>
			</FORM>
			<IFRAME class='uploadiframe' id="upload_target_ot" name="upload_target_ot"></IFRAME>
		</DIV>
	</DIV>
	<!-- END OTHER UPLOAD FORM -->

	<!-- BEGIN JOB LIST -->
	<DIV id='jlist' class='jlistholder'></DIV>
	<!-- END JOB LIST -->

	<!-- BEGIN JOB INFO -->
	<DIV id='jinfo' class='jlistholder'></DIV>
	<!-- END JOB INFO -->

	<!-- BEGIN JOB VIEW -->
	<DIV id='jview' class='jlistholder'></DIV>
	<!-- END JOB VIEW -->

</body>
</html>