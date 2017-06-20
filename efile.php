<?php
	include 'funclib.php';

	//Output the headers
	header('Expires: 0');
	header('Cache-control: private');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Content-Description: File Transfer');
	header('Content-Type: text/plain');

	//Establish Database connection
	if (!SQLConnect()){echo "SQL ERROR : ".SQLError();exit;}

	//Get the wjid (if presnet)
	$wjid=NULL;
	$res=SQLQuery("SELECT * FROM jobs LEFT JOIN wiringjobs ON jobs.jid=wiringjobs.jid WHERE jobs.jid='".$_GET['jid']."';");
	if (SQLRowCount($res)>0){
		$row=SQLGetRow($res);
		$wjid=$row['wjid'];

		header('Content-disposition: attachment; filename="'.$row['name'].'.efile"');
	}else{
		header('Content-disposition: attachment; filename="bad.efile"');

		echo "SELECT * FROM jobs LEFT JOIN wiringjobs ON jobs.jid=wiringjobs.jid WHERE jid='".$_GET['jid']."';";
		print_r($_GET);
		echo "Invalid JID";
		exit;
	}

	//Output the header
	echo "H,NAME,".$row['name']."\n";
	echo "H,CALIBRATIONS,".$row['calibration']."\n";
	SQLFreeResult($res);

	//Output the wiring data
	$res=SQLQuery("SELECT * FROM wiringdata WHERE wjid='".$wjid."';");
	while ($wire = SQLGetRow($res)){
		echo 'W,"'.$wire['FromType'].'","'.$wire['From'].'",'.$wire['FromX'].','.$wire['FromY'].','.$wire['fromCalib'].',"'.$wire['ToType'].'","'.$wire['To'].'",'.$wire['ToX'].','.$wire['ToY'].','.$wire['toCalib'].',"'.$wire['Colour'].'"'."\n";
	}
	SQLFreeResult($res);

	//Output the extra data
	$res=SQLQuery("SELECT * FROM wiringdataextras WHERE wjid='".$wjid."';");
	while ($xdata = SQLGetRow($res)){
		if ($xdata['print']==0){
			echo "X\"".$xdata['data']."\"\n";
		}else{
			echo "P\"".$xdata['data']."\"\n";
		}
	}
	SQLFreeResult($res);

?>