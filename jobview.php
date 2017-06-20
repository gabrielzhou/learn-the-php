<?php

include 'funclib.php';

session_start();

SQLConnect();

if (isset($_POST['action'])){
	switch ($_POST['action']){
		case 'anticlockwise':
			SQLQuery("UPDATE wiringdata LEFT JOIN wiringjobs ON wiringdata.wjid=wiringjobs.wjid SET FromX=(@t:=FromX), FromX=FromY, FromY=-@t WHERE fromCalib=0 AND jid=".$_POST['jid'].";");
			SQLQuery("UPDATE wiringdata LEFT JOIN wiringjobs ON wiringdata.wjid=wiringjobs.wjid SET ToX=(@t:=ToX), ToX=ToY, ToY=-@t WHERE toCalib=0 AND jid=".$_POST['jid'].";");

			SQLFreeResult(SQLQuery("SELECT @minX:=min(FromX),@minY:=min(FromY),@maxX:=max(FromX),@maxY:=max(FromY) FROM wiringdata w LEFT JOIN wiringjobs j ON w.wjid=j.wjid WHERE fromCalib=0 AND jid=".$_POST['jid'].";"));
			SQLQuery("UPDATE wiringjobs SET lowX=@minX, lowY=@minY, hiX=@maxX, hiY=@maxY WHERE jid=".$_POST['jid'].";");
			break;

		case 'clockwise':
			SQLQuery("UPDATE wiringdata LEFT JOIN wiringjobs ON wiringdata.wjid=wiringjobs.wjid SET FromX=(@t:=FromX), FromX=-FromY, FromY=@t WHERE fromCalib=0 AND jid=".$_POST['jid'].";");
			SQLQuery("UPDATE wiringdata LEFT JOIN wiringjobs ON wiringdata.wjid=wiringjobs.wjid SET ToX=(@t:=ToX), ToX=-ToY, ToY=@t WHERE toCalib=0 AND jid=".$_POST['jid'].";");

			SQLFreeResult(SQLQuery("SELECT @minX:=min(FromX),@minY:=min(FromY),@maxX:=max(FromX),@maxY:=max(FromY) FROM wiringdata w LEFT JOIN wiringjobs j ON w.wjid=j.wjid WHERE fromCalib=0 AND jid=".$_POST['jid'].";"));
			SQLQuery("UPDATE wiringjobs SET lowX=@minX, lowY=@minY, hiX=@maxX, hiY=@maxY WHERE jid=".$_POST['jid'].";");
			break;

		case 'flip':
			SQLQuery("UPDATE wiringdata LEFT JOIN wiringjobs ON wiringdata.wjid=wiringjobs.wjid SET FromX=-FromX WHERE fromCalib=0 AND jid=".$_POST['jid'].";");
			SQLQuery("UPDATE wiringdata LEFT JOIN wiringjobs ON wiringdata.wjid=wiringjobs.wjid SET ToX=-ToX WHERE toCalib=0 AND jid=".$_POST['jid'].";");

			SQLFreeResult(SQLQuery("SELECT @minX:=min(FromX),@minY:=min(FromY),@maxX:=max(FromX),@maxY:=max(FromY) FROM wiringdata w LEFT JOIN wiringjobs j ON w.wjid=j.wjid WHERE fromCalib=0 AND jid=".$_POST['jid'].";"));
			SQLQuery("UPDATE wiringjobs SET lowX=@minX, lowY=@minY, hiX=@maxX, hiY=@maxY WHERE jid=".$_POST['jid'].";");
			break;
	}
}

SQLClose();

//clockwise:
// x'=-y
// y'=x

//Ani:
// x'=y
// y=-x

// cos 90 = 0
// sin 90 = 1
// sin-90 =-1

echo "<img src='support/show.php?jid=".$_POST['jid']."&width=".$_POST['width']."&t=". time()."'>";

?>

<INPUT value='CCW' TYPE='submit' onclick='rotateJobAntiClockwise(document.getElementById("selected_job_jid").innerHTML,<?php echo $_POST['width']; ?>);'>
<INPUT value='FLIP' TYPE='submit' onclick='flipJob(document.getElementById("selected_job_jid").innerHTML,<?php echo $_POST['width']; ?>);'>
<INPUT value='CW' TYPE='submit' onclick='rotateJobClockwise(document.getElementById("selected_job_jid").innerHTML,<?php echo $_POST['width']; ?>);'>
