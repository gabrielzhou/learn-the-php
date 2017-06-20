<?php

include 'funclib.php';

session_start();

SQLConnect();

if (isset($_POST['action'])){
	switch ($_POST['action']){
		case 'setComplete':
			SQLQuery("CALL update_stats(".$_POST['jid'].");");
			SQLQuery("UPDATE jobs SET complete=1 WHERE jid=".$_POST['jid']." LIMIT 1;");
			break;

		case 'setNotComplete':
			SQLQuery("UPDATE jobs SET complete=0 WHERE jid=".$_POST['jid']." LIMIT 1;");
			break;
	}
}

$res=SQLQuery("CALL job_info(".$_POST['jid'].")");

if ($line = SQLGetRow($res)){
	echo "	<TABLE id='jobinfo' class='joblist'><TBODY><TR><TD>";
?>
	<TABLE class='infopagetable'>
		<TR>
			<TD>
				<DIV class='infopanel'>
					<DIV class='infotitle'>General</DIV>
					<TABLE class='infotable'><?php
echo "<TR><TD class='inforight'>Name</TD><TD>:</TD><TD class='infoleft'>".$line['name']."</TD></TR>";
echo "<TR><TD class='inforight'>Type</TD><TD>:</TD><TD class='infoleft'>".$line['type']."</TD></TR>";
echo "<TR><TD class='inforight'>Created</TD><TD>:</TD><TD class='infoleft'>".$line['created']."</TD></TR>";
echo "<TR><TD class='inforight'>Created By</TD><TD>:</TD><TD class='infoleft'>".$line['username']."</TD></TR>";
echo "<TR><TD class='inforight'>Job ID</TD><TD>:</TD><TD class='infoleft'>JID".$line['jid']."</TD></TR>";
echo "<TR><TD class='inforight'>Wiring ID</TD><TD>:</TD><TD class='infoleft'>WJID".((is_null($line['wjid']))?(""):($line['wjid']))."</TD></TR>";
echo "<TR><TD class='inforight'>Top Wiring</TD><TD>:</TD><TD class='infoleft'>".(($line['wjid']==1)?("Yes"):("No"))."</TD></TR>";
					?></TABLE>
				</DIV>
			</TD>
			<TD>
				<DIV class='infopanel'>
					<DIV class='infotitle'>Progress</DIV>
					<TABLE class='infotable'><?php
if ($line['complete']==0){echo "<TR><TD class='inforight'>Status</TD><TD>:</TD><TD class='infoleft'><img onclick='selectedComplete(document.getElementById(\"selected_job_jid\").innerHTML,true)' src='images/active_switch.png'></TD></TR>";}
else{echo "<TR><TD class='inforight'>Status</TD><TD>:</TD><TD class='infoleft'><img onclick='selectedComplete(document.getElementById(\"selected_job_jid\").innerHTML,false)' src='images/complete_switch.png'></TD></TR>";}
echo "<TR><TD class='inforight'>Progress</TD><TD>:</TD><TD class='infoleft'>".sprintf("%3.1f%%",$line['Progress'])."</TD></TR>";
echo "<TR><TD class='inforight'>Latest Wire #</TD><TD>:</TD><TD class='infoleft'>".((is_null($line['LastWire'])?("-"):($line['LastWire'])))."</TD></TR>";
echo "<TR><TD class='inforight'>Wires Remaining</TD><TD>:</TD><TD class='infoleft'>".((is_null($line['WiresRemaining'])?(((is_null($line['wirecount'])?("-"):($line['wirecount'])))):($line['WiresRemaining'])))."</TD></TR>";
echo "<TR><TD class='inforight'>Latest Wire Time</TD><TD>:</TD><TD class='infoleft'>".((is_null($line['LastAction'])?("-"):($line['LastAction'])))."</TD></TR>";
echo "<TR><TD class='inforight'>Earliest Wire Time</TD><TD>:</TD><TD class='infoleft'>".((is_null($line['FirstAction'])?("-"):($line['FirstAction'])))."</TD></TR>";
echo "<TR><TD class='inforight'>Wiring Time</TD><TD>:</TD><TD class='infoleft'>".formatSeconds($line['Active_Duration']+$line['Inactive_Duration'])."</TD></TR>";
					?></TABLE>
				</DIV>
			</TD>
		</TR>
		<TR>
			<TD colspan='2'>
				<DIV class='infopanel'>
					<DIV class='infotitle'>Projections</DIV>			
					<TABLE class='infotable'><?php
echo "<TR><TD class='inforight'>Estimated Time to Completion (Job Rate)</TD><TD>:</TD><TD class='infoleft'>".(is_null($line['EstComplete'])?("-"):(formatSecondsRemaining($line['EstComplete'])))."</TD></TR>";

if (!is_null($line['WiresRemaining']) && ($line['WiresRemaining']==0)){
		echo "<TR><TD class='inforight'>Workday Completion Estimate (Job Rate)</TD><TD>:</TD><TD class='infoleft'>Complete</TD></TR>";
		echo "<TR><TD class='inforight'>Shiftwork Completion Estimate (Job Rate)</TD><TD>:</TD><TD class='infoleft'>Complete</TD></TR>";

		echo "<TR><TD class='inforight'>Workday Completion Estimate (".$line['type']." M.A.R)</TD><TD>:</TD><TD class='infoleft'>Complete</TD></TR>";
		echo "<TR><TD class='inforight'>Shiftwork Completion Estimate (".$line['type']." M.A.R)</TD><TD>:</TD><TD class='infoleft'>Complete</TD></TR>";

}else{
	if (is_null($line['EstComplete'])){
		echo "<TR><TD class='inforight'>Workday Completion Estimate (Job Rate)</TD><TD>:</TD><TD class='infoleft'>-</TD></TR>";

		echo "<TR><TD class='inforight'>Shiftwork Completion Estimate (Job Rate)</TD><TD>:</TD><TD class='infoleft'>-</TD></TR>";
	}else{
		$eta=addWorkingMins(floor($line['EstComplete']/60));
		echo "<TR><TD class='inforight'>Workday Completion Estimate (Job Rate)</TD><TD>:</TD><TD class='infoleft'>".date('Y-m-d H:i (l)', $eta)."</TD></TR>";

		$eta = strtotime("+".floor($line['EstComplete'])." seconds now");
		echo "<TR><TD class='inforight'>Shiftwork Completion Estimate (Job Rate)</TD><TD>:</TD><TD class='infoleft'>".date('Y-m-d H:i (l)', $eta)."</TD></TR>";
	}

	echo "<TR><TD class='inforight'>Estimated Time to Completion (".$line['type']." Monthly Average Rate)</TD><TD>:</TD><TD class='infoleft'>".(is_null($line['RecentAvgEstComplete'])?("-"):(formatSecondsRemaining($line['RecentAvgEstComplete'])))."</TD></TR>";

	if (is_null($line['RecentAvgEstComplete'])){
		echo "<TR><TD class='inforight'>Workday Completion Estimate (".$line['type']." M.A.R)</TD><TD>:</TD><TD class='infoleft'>-</TD></TR>";

		echo "<TR><TD class='inforight'>Shiftwork Completion Estimate (".$line['type']." M.A.R)</TD><TD>:</TD><TD class='infoleft'>-</TD></TR>";
	}else{
		$eta=addWorkingMins(floor($line['RecentAvgEstComplete']/60));
		echo "<TR><TD class='inforight'>Workday Completion Estimate (".$line['type']." M.A.R)</TD><TD>:</TD><TD class='infoleft'>".date('Y-m-d H:i (l)', $eta)."</TD></TR>";

		$eta = strtotime("+".floor($line['RecentAvgEstComplete'])." seconds now");
		echo "<TR><TD class='inforight'>Shiftwork Completion Estimate (".$line['type']." M.A.R)</TD><TD>:</TD><TD class='infoleft'>".date('Y-m-d H:i (l)', $eta)."</TD></TR>";
	}
}

					?></TABLE>
				</DIV>
			</TD>
		</TR>

		<TR>
			<TD>
				<DIV class='infopanel'>
					<DIV class='infotitle'>Activity</DIV>			
					<TABLE class='infotable'><?php
if (($line['Active_Duration']+$line['Inactive_Duration'])>0){
	$wActivity=($line['Active_Duration']/($line['Active_Duration']+($line['Inactive_Duration']-$line['OT_Inactive_Duration'])))*100;
	$sActivity=($line['Active_Duration']/($line['Active_Duration']+$line['Inactive_Duration']))*100;
	$active=$line['Active_Duration'];
	$inactive=$line['Inactive_Duration'];
	$oooinactive=$line['OT_Inactive_Duration'];
}else{
	$activity=0;
	$active=0;
	$inactive=0;
	$oooinactive=0;
}
echo "<TR><TD class='inforight'>Activity</TD><TD>:</TD><TD class='infoleft'>".sprintf("%3.1f%%",$sActivity)."</TD></TR>";
echo "<TR><TD class='inforight'>Workday Effective Activity</TD><TD>:</TD><TD class='infoleft'>".sprintf("%3.1f%%",$wActivity)."</TD></TR>";

echo "<TR><TD class='inforight'>Total Time</TD><TD>:</TD><TD class='infoleft'>".formatSeconds($active+$inactive)."</TD></TR>";
echo "<TR><TD class='inforight'>Active Time</TD><TD>:</TD><TD class='infoleft'>".formatSeconds($active)."</TD></TR>";
echo "<TR><TD class='inforight'>Out Of Hours Inactive Time</TD><TD>:</TD><TD class='infoleft'>".formatSeconds($oooinactive)."</TD></TR>";
echo "<TR><TD class='inforight'>Workday Inactive Time</TD><TD>:</TD><TD class='infoleft'>".formatSeconds($inactive-$oooinactive)."</TD></TR>";
if (($active==0) && ($inactive==0)){
	echo "<TR><TD class='infocenter' colspan='3'><IMG SRC='support/pie.php?label=Active*Inactive&data=0*1' ALT='Activity Pie Chart'></TD></TR>";
}else{
	echo "<TR><TD class='infocenter' colspan='3'><IMG SRC='support/pie.php?label=Active*Inactive (Workday)*Inactive (Shiftwork)&data=".$active."*".($inactive-$oooinactive)."*".($oooinactive)."' ALT='Activity Pie Chart'></TD></TR>";
}
					?></TABLE>
				</DIV>
			</TD>
			<TD>
				<DIV class='infopanel'>
					<DIV class='infotitle'>Performance</DIV>			
					<TABLE class='infotable'><?php
//Calculate performance grads

if (is_null($line['RecentAvgWiresPerMin']) || is_null($line['WiresPerMin']) ||	$line['RecentAvgWiresPerMin']==0){
	echo "<TR><TD class='inforight'>Workday Performance Grade</TD><TD>:</TD><TD class='infoleft'>-</TD></TR>";
	echo "<TR><TD class='inforight'>Shiftwork Performance Grade (</TD><TD>:</TD><TD class='infoleft'>-</TD></TR>";
}else{
	$activespeed=$wActivity*($line['WiresPerMin']/$line['RecentAvgWiresPerMin']);
	echo "<TR><TD class='inforight'>Workday Performance Grade</TD><TD>:</TD><TD class='infoleft'>".$line['wGrade']." (".sprintf("%3.2f%%",$activespeed)." effective)</TD></TR>";

	$activespeed=$sActivity*($line['WiresPerMin']/$line['RecentAvgWiresPerMin']);
	echo "<TR><TD class='inforight'>Shiftwork Performance Grade</TD><TD>:</TD><TD class='infoleft'>".$line['sGrade']." (".sprintf("%3.2f%%",$activespeed)." effective)</TD></TR>";
}

echo "<TR><TD class='inforight'>Wires/Min (Job Average)</TD><TD>:</TD><TD class='infoleft'>".((is_null($line['WiresPerMin'])?("-"):($line['WiresPerMin'])))."</TD></TR>";
echo "<TR><TD class='inforight'>Wires/Hour (Job Average)</TD><TD>:</TD><TD class='infoleft'>".((is_null($line['WiresPerMin'])?("-"):($line['WiresPerMin']*60)))."</TD></TR>";
echo "<TR><TD class='inforight'>Wires/Min (Monthly ".$line['type']." Average)</TD><TD>:</TD><TD class='infoleft'>".((is_null($line['RecentAvgWiresPerMin'])?("-"):($line['RecentAvgWiresPerMin'])))."</TD></TR>";
echo "<TR><TD class='inforight'>Wires/Hour (Monthly ".$line['type']." Average)</TD><TD>:</TD><TD class='infoleft'>".((is_null($line['RecentAvgWiresPerMin'])?("-"):($line['RecentAvgWiresPerMin']*60)))."</TD></TR>";

if (is_null($line['RecentAvgWiresPerMin']) || is_null($line['WiresPerMin']) ||	$line['RecentAvgWiresPerMin']==0){
	echo "<TR><TD class='inforight'>Speed Vs Average</TD><TD>:</TD><TD class='infoleft'>-</TD></TR>";
}else{
	echo "<TR><TD class='inforight'>Speed Vs Average</TD><TD>:</TD><TD class='infoleft'>".sprintf("%3.2f%%",(($line['WiresPerMin']/$line['RecentAvgWiresPerMin'])*100))."</TD></TR>";
}
					?></TABLE>
				</DIV>
			</TD>
		</TR>

		<TR>
			<TD>
				<DIV class='infopanel'>
					<DIV class='infotitle'>Probe Types</DIV><TABLE class='borderinfotable'><tr><th>Type</th><th>Count</th></tr>
<?php
if (isset($line['wjid'])){
	SQLFreeResult($res);
	if ($res=SQLQuery("SELECT Type, sum(Count) as Count FROM
	(SELECT ToType as Type,count(*) as Count FROM wiringdata WHERE wjid=".$line['wjid']." GROUP BY ToType
	UNION
	SELECT FromType as Type,count(*) as Count FROM wiringdata WHERE wjid=".$line['wjid']." GROUP BY FromType) as A GROUP BY Type ORDER BY Count DESC;")){

		while ($probeinfo = SQLGetRow($res)){
			echo "<TR><TD class='infocenter'>".(($probeinfo['Type']=='')?"&lt;Unknown&gt;":($probeinfo['Type']))."</TD><TD class='infocenter'>".$probeinfo['Count']."</TD></TR>";
		}
	}else{
		echo SQLError();
	}
}
			?></TABLE>
				</DIV>
			</TD>
			<TD>
				<DIV class='infopanel'>
					<DIV class='infotitle'>Wire Lengths</DIV><TABLE class='borderinfotable'><tr><th>Colour</th><th>Length (Inches)</th><th>Count</th></tr>
<?php

if (isset($line['wjid'])){
	SQLFreeResult($res);
	if ($res=SQLQuery("SELECT Colour, Length, count(*) as Count FROM wiringdata WHERE wjid=".$line['wjid']." GROUP BY Length,Colour ORDER BY Colour, Length;")){

		while ($probeinfo = SQLGetRow($res)){
			echo "<TR><TD class='infocenter'>".$probeinfo['Colour']."</TD><TD class='infocenter'>".$probeinfo['Length']."</TD><TD class='infocenter'>".$probeinfo['Count']."</TD></TR>";
		}
	}else{
		echo SQLError();
	}
}
			?></TABLE>
				</DIV>
			</TD>
		</TR>
	</TABLE>

<?php
	echo "
		<DIV class='hidden' id='selected_job_name'>".$line['name']."</DIV>
		<DIV class='hidden' id='selected_job_type'>".$line['type']."</DIV>
		<DIV class='hidden' id='selected_job_jid'>".$_POST['jid']."</DIV>
		";

	echo "	</TD></TR></TBODY></TABLE>";
}else{
	echo "Failed to retrieve job information for job '".$_POST['jid']."'";
}
?>