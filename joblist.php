<?php

include 'funclib.php';

session_start();

function sendlist(){
	SQLConnect();

	$delpermission=checkPermission($_SESSION['uid'],"Delete Jobs");
	$viewpermission=checkPermission($_SESSION['uid'],"View Jobs");

	$res=SQLQuery("CALL active_progress()");

?>
	<TABLE id='joblist' class='joblist'>
		<THEAD>
			<TR>
				<TH class='left'>Job Name</TH>
				<TH class='left'>Type</TH>
				<TH class='left'>Completion %</TH>
				<TH class='left'>ETC (Job Avg)</TH>
				<TH class='right'>ETC (1 month Avg)</TH>
				<TH width='16px' class='transparent'></TH> 
			</TR>
		</THEAD>

		<TBODY>
<?php

//Get the rows
unset($rows);
while ($line = SQLGetRow($res)){$rows[]=$line;}

$oddline=false;
foreach ($rows as $line){
	$oddline=!$oddline;

	if ($viewpermission){
		echo "\t\t\t<TR class=".($oddline?'"line1"':'"line2"')."  onmouseover='select(this,true,\"#AAAAAA\")' onmouseout='select(this,false)' onclick='ajax_details(\"".$line['jid']."\")'>\n";
	}else{
		echo "\t\t\t<TR class=".($oddline?'"line1"':'"line2"')."  onmouseover='select(this,true,\"#AAAAAA\")' onmouseout='select(this,false)'>\n";
	}

	if (is_null($line['wjid'])){
		echo "\t\t\t\t<TD class='left'><DIV class='warning' ALT='No Wiring Data'>".$line['name']."</DIV></TD>\n";
	}else{
		echo "\t\t\t\t<TD class='left'>".$line['name']."</TD>\n";
	}
	echo "\t\t\t\t<TD class='mid'>".$line['type']."</TD>\n";


	if ($line['complete']==1){
		echo "\t\t\t\t<TD class='mid'>Complete</TD>\n";
		echo "\t\t\t\t<TD class='mid'>-</TD>\n";
		echo "\t\t\t\t<TD class='right'>-</TD>\n";
	}else{
		echo "\t\t\t\t<TD class='mid'>".sprintf("%3.1f%%",$line['Progress'])." (".$line['wGrade']."/".$line['sGrade'].")</TD>\n";

		if (is_null($line['EstComplete'])){echo "\t\t\t\t<TD class='mid'>Unknown</TD>\n";}
		else{echo "\t\t\t\t<TD class='mid'>".formatSecondsRemaining($line['EstComplete'])."</TD>\n";}

		if  (is_null($line['RecentAvgEstComplete'])){echo "\t\t\t\t<TD class='right'>Unknown</TD>\n";}
		else{echo "\t\t\t\t<TD class='right'>".formatSecondsRemaining($line['RecentAvgEstComplete'])."</TD>\n";}
	}

	echo "\t\t\t</TR>\n";
}

?>
		</TBODY>
	</TABLE>
<?php

}

sendlist();

?>