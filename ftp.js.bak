var orig_colour;
var orig_bg;
function select(a,value,colour){
	if (colour==null){colour='#CCCCCC';}

	if (value){
		orig_color=a.style.backgroundColor;
		orig_bg=a.style.background;
		a.style.background=""			
		a.style.backgroundColor=colour
	}else{
		a.style.backgroundColor=orig_color;
		a.style.background=orig_bg;
	}
}

function mainload(){
	document.getElementById("listtasks").style.display="block";
	document.getElementById("jlist").style.display="block";
	ajax_refresh();
	setInterval("refresh()", 60000);
}

function refresh(){
	ajax_refresh();

	if (document.getElementById("jinfo").style.display=="block"){
		ajax_details(document.getElementById("selected_job_jid").innerHTML);
	}
}

function resizefunc(){
	document.getElementById("working").style.top=((window.innerHeight/2)-(document.getElementById("working").clientHeight/2));	

	var utable=document.getElementById("joblist");
	if (((utable.tBodies[0].childElementCount*25)+3)>window.innerHeight-104){
		utable.tBodies[0].style.height=window.innerHeight-104;
	}else{
		utable.tBodies[0].style.height=(utable.tBodies[0].childElementCount*25)+3;
	}

	utable=document.getElementById("jobinfo");
	utable.tBodies[0].style.height=window.innerHeight-72;

	if (document.getElementById("addjob").style.display!="none"){
		document.getElementById("addjob").style.left=((window.innerWidth/2)-(document.getElementById("addjob").clientWidth/2));
		document.getElementById("addjob").style.top=((window.innerHeight/2)-(document.getElementById("addjob").clientHeight/2));			
	}
	
	if (document.getElementById("deljob").style.display!="none"){
		document.getElementById("deljob").style.left=((window.innerWidth/2)-(document.getElementById("deljob").clientWidth/2));
		document.getElementById("deljob").style.top=((window.innerHeight/2)-(document.getElementById("deljob").clientHeight/2));
	}

	if (document.getElementById("uploadhp").style.display!="none"){
		document.getElementById("uploadhp").style.left=((window.innerWidth/2)-(document.getElementById("uploadhp").clientWidth/2));
		document.getElementById("uploadhp").style.top=((window.innerHeight/2)-(document.getElementById("uploadhp").clientHeight/2));
	}
}

function addjobvisible(visibility){
	if (visibility==true){
		document.getElementById("fadediv").style.display="block";
		document.getElementById("addjob").style.display="block";
		document.getElementById("addjob").style.left=((window.innerWidth/2)-(document.getElementById("addjob").clientWidth/2));
		document.getElementById("addjob").style.top=((window.innerHeight/2)-(document.getElementById("addjob").clientHeight/2));

		document.getElementById("addjob_name").value="";
		document.getElementById("addjob_name").focus();

		check_addjob_name();
	}else{
		document.getElementById("addjob").style.display="none";
		document.getElementById("fadediv").style.display="none";
	}

	resizefunc();
}

function details(jid){
	document.location.replace("jobinfo.php?details="+jid);
}

function hidelog(){
	document.getElementById("fadediv").style.display="none";
	document.getElementById("userlog").style.display="none";
}

function showlogin(){
	document.getElementById("login").style.display="block";
	document.getElementById("login").style.left=((window.innerWidth/2)-(document.getElementById("login").clientWidth/2));
	document.getElementById("login").style.top=((window.innerHeight/2)-(document.getElementById("login").clientHeight/2));	
}

function showdeleteselectedjob(){
	showdeletejob(document.getElementById("selected_job_name").innerHTML,document.getElementById("selected_job_type").innerHTML,document.getElementById("selected_job_jid").innerHTML);
}

function showdeletejob(jobname,jobtype,jid){
	document.getElementById("fadediv").style.display="block";
	document.getElementById("deljob").style.display="block";
	document.getElementById("deljob").style.left=((window.innerWidth/2)-(document.getElementById("deljob").clientWidth/2));
	document.getElementById("deljob").style.top=((window.innerHeight/2)-(document.getElementById("deljob").clientHeight/2));	

	document.getElementById("deljob_name").value=jobname;
	document.getElementById("deljob_type").value=jobtype;

	document.getElementById("deljob_jid").value=jid;

	document.getElementById("delete_confirm").value="";

	check_delete_confirm();
}

function hidedeletejob(){
	document.getElementById("fadediv").style.display="none";
	document.getElementById("deljob").style.display="none";
}

function showedit(realname,company,username,uid){
	document.getElementById("fadediv").style.display="block";
	document.getElementById("edituser").style.display="block";
	document.getElementById("edituser").style.left=((window.innerWidth/2)-(document.getElementById("edituser").clientWidth/2));
	document.getElementById("edituser").style.top=((window.innerHeight/2)-(document.getElementById("edituser").clientHeight/2));	

	document.getElementById("edit_realname").value=realname;
	document.getElementById("edit_company").value=company;
	document.getElementById("edit_username").value=username;
	document.getElementById("edit_password").value="<Click to edit>";
	document.getElementById("edit_password").style.color="#AAAAAA";

	document.getElementById("edit_password").style.background="#DDDDDD";
	document.getElementById("edit_uid").value=uid;

	check_edit_password();
}

function hideedit(){
	document.getElementById("fadediv").style.display="none";
	document.getElementById("edituser").style.display="none";
}

function enable_addjob_submit(){
	if (document.getElementById("addjob_namevalid").style.color=="green"){
		document.getElementById("addjob_submit").disabled=false;
	}else{
		document.getElementById("addjob_submit").disabled=true;
	}
}

function check_addjob_name(){
	var jobname = document.getElementById("addjob_name").value;

	if (jobname.length==0){
		document.getElementById("addjob_namevalid").innerHTML="Job Name cannot be empty";
		document.getElementById("addjob_namevalid").style.color="red";
	}else{
		var found=false;

		var rows = document.getElementById("joblist").tBodies[0].rows;
		for (var index=0; index<rows.length; index++){
			if (rows[index].cells[0].textContent==jobname){found=true;}
		}

		if (found){
			document.getElementById("addjob_namevalid").innerHTML="Duplicate Job Name";
			document.getElementById("addjob_namevalid").style.color="red";
		}else{
			document.getElementById("addjob_namevalid").innerHTML="Job Name OK";
			document.getElementById("addjob_namevalid").style.color="green";
		}
	}


	enable_addjob_submit();
}

function editpass(){
	if (document.getElementById("edit_password").value=='<Click to edit>'){
		document.getElementById("edit_password").value="";
		document.getElementById("edit_password").style.background="white";
		document.getElementById("edit_password").style.color="black";

		check_edit_password();
	}
}

function enable_edit_submit(){
	if (document.getElementById("edit_passvalid").style.color=="green"){
		document.getElementById("edit_submit").disabled=false;
	}else{
		document.getElementById("edit_submit").disabled=true;
	}
}

function check_edit_password(){
	if (document.getElementById("edit_password").value=='<Click to edit>'){
		document.getElementById("edit_passvalid").innerHTML="&nbsp;";
		document.getElementById("edit_passvalid").style.color="green";
	}else{
		if (document.getElementById("edit_password").value.length==0){
			document.getElementById("edit_passvalid").innerHTML="Password cannot be empty";
			document.getElementById("edit_passvalid").style.color="red";
		}else{
			if (document.getElementById("edit_password").value.search(/[^abcdefghijklmnopqrstuvwxyz1234567890]/gi)>=0){
				document.getElementById("edit_passvalid").innerHTML="Must be alphanumeric";
				document.getElementById("edit_passvalid").style.color="red";
			}else{
				if (document.getElementById("edit_password").value.length<6){
					document.getElementById("edit_passvalid").innerHTML="Too Short (>6 Chars)";
					document.getElementById("edit_passvalid").style.color="red";
				}else{
					document.getElementById("edit_passvalid").innerHTML="Password OK";
					document.getElementById("edit_passvalid").style.color="green";
				}
			}
		}
	}

	enable_edit_submit();
}

function check_delete_confirm(){
	if (document.getElementById("delete_confirm").value=='DELETE'){
		document.getElementById("delete_submit").disabled=false;
	}else{
		document.getElementById("delete_submit").disabled=true;
	}
}

//Begin Action Code

function addJob(){
	showWait(true);

	//Formulate and send the ajax request
	ajax('process.php', 'action=addjob&addjob_name='+document.getElementById("addjob_name").value+'&addjob_type='+document.getElementById("addjob_type").value);

	//Hide the add job form
	document.getElementById("addjob").style.display="none";

	//Request a page refresh
	//ajax_refresh();
}

function editUser(){
	//Formulate and send the ajax request
	ajax('process.php', 'action=edit&uid='+document.getElementById("edit_uid").value+'&password='+document.getElementById("edit_password").value+'&real='+document.getElementById("edit_realname").value+'&company='+document.getElementById("edit_company").value);

	//Hide the edit user form
	hideedit();
}

function toggleEnabled(uid,value){
	ajax('process.php', 'action=enable&uid='+uid+'&state='+value);
}

function delJob(){
	showWait(true);

	ajax('process.php', 'action=deletejob&jid='+document.getElementById("deljob_jid").value);

	//Hide the delete job dialog
	document.getElementById("deljob").style.display="none";

	//Hide the job information
	document.getElementById("jlist").style.display="block";
	document.getElementById("jinfo").style.display="none";
	document.getElementById("jview").style.display="none";
	document.getElementById("listtasks").style.display="block";
	document.getElementById("infotasks").style.display="none";
	document.getElementById("viewtasks").style.display="none";

	//Request a page refresh
	ajax_refresh();
}

function hideJobInfo(){
	document.getElementById("jlist").style.display="block";
	document.getElementById("jinfo").style.display="none";
	document.getElementById("jview").style.display="none";
	document.getElementById("listtasks").style.display="block";
	document.getElementById("infotasks").style.display="none";
	document.getElementById("viewtasks").style.display="none";
}

function showWait(visibility){
	if (visibility){
		document.getElementById("fadediv").style.display="block";
		document.getElementById("working").style.display="block";
	}else{
		document.getElementById("fadediv").style.display="none";
		document.getElementById("working").style.display="none";
	}
}

function showUpload(){
	switch (document.getElementById("selected_job_type").innerHTML){
		case 'HP':
			document.getElementById("uploadhp_submit").disabled=false;
			document.getElementById("uploadhp_cancel").style.display="inline";
			document.getElementById("uploadhp_close").style.display="none";
			document.getElementById("uploadhpprogress").innerHTML="<IMG src='images/upload_ready.gif'>";

			document.getElementById("wfile").value='';
			document.getElementById("ifile").value='';

			document.getElementById("uploadhp_jid").value=document.getElementById("selected_job_jid").innerHTML;
			document.getElementById("fadediv").style.display="block";
			document.getElementById("uploadhp").style.display="block";

			document.getElementById("uploadhp").style.left=((window.innerWidth/2)-(document.getElementById("uploadhp").clientWidth/2));
			document.getElementById("uploadhp").style.top=((window.innerHeight/2)-(document.getElementById("uploadhp").clientHeight/2));

			document.getElementById("upload_target_hp").src="iframe_uploadready.php";
			break;

		case 'Genrad':
			document.getElementById("uploadgenrad_submit").disabled=false;
			document.getElementById("uploadgenrad_cancel").style.display="inline";
			document.getElementById("uploadgenrad_close").style.display="none";
			document.getElementById("uploadgenradprogress").innerHTML="<IMG src='images/upload_ready.gif'>";

			document.getElementById("gfile").value='';

			document.getElementById("uploadgenrad_jid").value=document.getElementById("selected_job_jid").innerHTML;
			document.getElementById("fadediv").style.display="block";
			document.getElementById("uploadgenrad").style.display="block";

			document.getElementById("uploadgenrad").style.left=((window.innerWidth/2)-(document.getElementById("uploadgenrad").clientWidth/2));
			document.getElementById("uploadgenrad").style.top=((window.innerHeight/2)-(document.getElementById("uploadgenrad").clientHeight/2));

			document.getElementById("upload_target_gr").src="iframe_uploadready.php";
			break;

		case 'Teradyne':
			document.getElementById("uploadteradyne_submit").disabled=false;
			document.getElementById("uploadteradyne_cancel").style.display="inline";
			document.getElementById("uploadteradyne_close").style.display="none";
			document.getElementById("uploadteradyneprogress").innerHTML="<IMG src='images/upload_ready.gif'>";

			document.getElementById("tfile").value='';

			document.getElementById("uploadteradyne_jid").value=document.getElementById("selected_job_jid").innerHTML;
			document.getElementById("fadediv").style.display="block";
			document.getElementById("uploadteradyne").style.display="block";

			document.getElementById("uploadteradyne").style.left=((window.innerWidth/2)-(document.getElementById("uploadteradyne").clientWidth/2));
			document.getElementById("uploadteradyne").style.top=((window.innerHeight/2)-(document.getElementById("uploadteradyne").clientHeight/2));

			document.getElementById("upload_target_tr").src="iframe_uploadready.php";
			break;

		case 'Spectrum':
			document.getElementById("uploadspectrum_submit").disabled=false;
			document.getElementById("uploadspectrum_cancel").style.display="inline";
			document.getElementById("uploadspectrum_close").style.display="none";
			document.getElementById("uploadspectrumprogress").innerHTML="<IMG src='images/upload_ready.gif'>";

			document.getElementById("tfile").value='';

			document.getElementById("uploadspectrum_jid").value=document.getElementById("selected_job_jid").innerHTML;
			document.getElementById("fadediv").style.display="block";
			document.getElementById("uploadspectrum").style.display="block";

			document.getElementById("uploadspectrum").style.left=((window.innerWidth/2)-(document.getElementById("uploadspectrum").clientWidth/2));
			document.getElementById("uploadspectrum").style.top=((window.innerHeight/2)-(document.getElementById("uploadspectrum").clientHeight/2));

			document.getElementById("upload_target_tr").src="iframe_uploadready.php";
			break;

		case 'Other':
			document.getElementById("uploadother_submit").disabled=false;
			document.getElementById("uploadother_cancel").style.display="inline";
			document.getElementById("uploadother_close").style.display="none";
			document.getElementById("uploadotherprogress").innerHTML="<IMG src='images/upload_ready.gif'>";

			document.getElementById("ofile").value='';

			document.getElementById("uploadother_jid").value=document.getElementById("selected_job_jid").innerHTML;
			document.getElementById("fadediv").style.display="block";
			document.getElementById("uploadother").style.display="block";

			document.getElementById("uploadother").style.left=((window.innerWidth/2)-(document.getElementById("uploadother").clientWidth/2));
			document.getElementById("uploadother").style.top=((window.innerHeight/2)-(document.getElementById("uploadother").clientHeight/2));

			document.getElementById("upload_target_ot").src="iframe_uploadready.php";
			break;
	}
}

function uploading(){
	//HP
	document.getElementById("uploadhp_submit").disabled=true;
	document.getElementById("uploadhpprogress").innerHTML="<IMG src='images/upload.gif'>";

	//Genrad
	document.getElementById("uploadgenrad_submit").disabled=true;
	document.getElementById("uploadgenradprogress").innerHTML="<IMG src='images/upload.gif'>";

	//Teradyne
	document.getElementById("uploadteradyne_submit").disabled=true;
	document.getElementById("uploadteradyneprogress").innerHTML="<IMG src='images/upload.gif'>";

	//Spectrum
	document.getElementById("uploadspectrum_submit").disabled=true;
	document.getElementById("uploadspectrumprogress").innerHTML="<IMG src='images/upload.gif'>";

	//Other
	document.getElementById("uploadother_submit").disabled=true;
	document.getElementById("uploadotherprogress").innerHTML="<IMG src='images/upload.gif'>";
}

function uploadComplete(){
	//HP
	parent.document.getElementById("uploadhpprogress").innerHTML="<IMG src='images/upload_complete.gif'>";
	parent.document.getElementById("uploadhp_cancel").style.display="none";
	parent.document.getElementById("uploadhp_close").style.display="inline";

	//Genrad
	parent.document.getElementById("uploadgenradprogress").innerHTML="<IMG src='images/upload_complete.gif'>";
	parent.document.getElementById("uploadgenrad_cancel").style.display="none";
	parent.document.getElementById("uploadgenrad_close").style.display="inline";

	//Teradyne
	parent.document.getElementById("uploadteradyneprogress").innerHTML="<IMG src='images/upload_complete.gif'>";
	parent.document.getElementById("uploadteradyne_cancel").style.display="none";
	parent.document.getElementById("uploadteradyne_close").style.display="inline";

	//Spectrum
	parent.document.getElementById("uploadspectrumprogress").innerHTML="<IMG src='images/upload_complete.gif'>";
	parent.document.getElementById("uploadspectrum_cancel").style.display="none";
	parent.document.getElementById("uploadspectrum_close").style.display="inline";

	//Other
	parent.document.getElementById("uploadotherprogress").innerHTML="<IMG src='images/upload_complete.gif'>";
	parent.document.getElementById("uploadother_cancel").style.display="none";
	parent.document.getElementById("uploadother_close").style.display="inline";
}

function hideUploads(update){
	document.getElementById("fadediv").style.display="none";
	document.getElementById("uploadhp").style.display="none";
	document.getElementById("uploadgenrad").style.display="none";
	document.getElementById("uploadteradyne").style.display="none";
	document.getElementById("uploadother").style.display="none";

	if (update){
		ajax_refresh();
		ajax_details(document.getElementById("selected_job_jid").innerHTML);
	}
}

function downloadEfile(){
	window.open("efile.php?jid="+document.getElementById('selected_job_jid').innerHTML,"_self");
}

function selectedComplete(jid, complete){
	showWait(true);
	if (complete){
		try{
			xml_request = new XMLHttpRequest();
			xml_request.onreadystatechange = ajax_details_reply_and_list_update;
			xml_request.open("POST", 'jobinfo.php', true);
			xml_request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			xml_request.send('action=setComplete&jid='+jid);
		}catch (e){
			alert(e);
		}
	}else{
		try{
			xml_request = new XMLHttpRequest();
			xml_request.onreadystatechange = ajax_details_reply_and_list_update;
			xml_request.open("POST", 'jobinfo.php', true);
			xml_request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			xml_request.send('action=setNotComplete&jid='+jid);
		}catch (e){
			alert(e);
		}
	}
}

function rotateJobClockwise(jid,width){
	showWait(true);
	try{
		xml_request = new XMLHttpRequest();
		xml_request.onreadystatechange = ajax_jobview_reply;
		xml_request.open("POST", 'jobview.php', true);
		xml_request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		xml_request.send('action=clockwise&jid='+jid+'&width='+width);
	}catch (e){
		alert(e);
	}
}

function rotateJobAntiClockwise(jid,width){
	showWait(true);
	try{
		xml_request = new XMLHttpRequest();
		xml_request.onreadystatechange = ajax_jobview_reply;
		xml_request.open("POST", 'jobview.php', true);
		xml_request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		xml_request.send('action=anticlockwise&jid='+jid+'&width='+width);
	}catch (e){
		alert(e);
	}
}

function flipJob(jid,width){
	showWait(true);
	try{
		xml_request = new XMLHttpRequest();
		xml_request.onreadystatechange = ajax_jobview_reply;
		xml_request.open("POST", 'jobview.php', true);
		xml_request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		xml_request.send('action=flip&jid='+jid+'&width='+width);
	}catch (e){
		alert(e);
	}
}

//Begin AJAX Code

var xml_request;

function ajax(url, vars) {
	try{
		xml_request = new XMLHttpRequest();
		xml_request.onreadystatechange = ajax_state_change;
		xml_request.open("POST", url, true);
		xml_request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		xml_request.send(vars);
	}catch (e){
		alert(e);
	}
}

function ajax_state_change(){
	if (xml_request.readyState==4){
		if (xml_request.status==200) {
			if (xml_request.responseText) {
				if (xml_request.responseText=='ar_refresh'){
					document.getElementById("jlist").style.display="block";
					document.getElementById("jinfo").style.display="none";
					document.getElementById("jview").style.display="none";
					document.getElementById("listtasks").style.display="block";
					document.getElementById("infotasks").style.display="none";
					document.getElementById("viewtasks").style.display="none";
					ajax_refresh();
				}else{
					alert(xml_request.responseText);
				}
			}
		}else{
			alert("AJAX processing error. Please seek assistance. (Status:"+xml_request.status+")");
		}
	}
}

function ajax_details(jid) {
	try{
		showWait(true);
		xml_request = new XMLHttpRequest();
		xml_request.onreadystatechange = ajax_details_reply;
		xml_request.open("POST", "jobinfo.php", true);
		xml_request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		xml_request.send('jid='+jid);
	}catch (e){
		alert(e);
	}
}

function ajax_details_reply(){
	if (xml_request.readyState==4){
		showWait(false);
		document.getElementById("jlist").style.display="none";
		document.getElementById("jinfo").style.display="block";
		document.getElementById("jview").style.display="none";
		if (xml_request.status==200) {
			if (xml_request.responseText) {
				jinfo.innerHTML=xml_request.responseText;
				document.getElementById("listtasks").style.display="none";
				document.getElementById("infotasks").style.display="block";
				document.getElementById("viewtasks").style.display="none";
				resizefunc();
			}else{
				//Notify error
				jinfo.innerHTML="Error loading job data via AJAX";
			}
		}else{
			//Notify error
			jinfo.innerHTML="<TABLE><TR><TD colspan='5' align='center'>AJAX Load Failed</TD></TR></TABLE>";
		}
	}
}

function ajax_details_reply_and_list_update(){
	if (xml_request.readyState==4){
		document.getElementById("jlist").style.display="none";
		document.getElementById("jinfo").style.display="block";
		document.getElementById("jview").style.display="none";
		if (xml_request.status==200) {
			if (xml_request.responseText) {
				jinfo.innerHTML=xml_request.responseText;
				document.getElementById("listtasks").style.display="none";
				document.getElementById("infotasks").style.display="block";
				document.getElementById("viewtasks").style.display="none";
				ajax_refresh();
			}else{
				//Notify error
				jinfo.innerHTML="Error loading job data via AJAX";
			}
		}else{
			//Notify error
			jinfo.innerHTML="<TABLE><TR><TD colspan='5' align='center'>AJAX Load Failed</TD></TR></TABLE>";
		}
	}
}

function ajax_jobview(jid) {
	try{
		showWait(true);
		jview.innerHTML="";
		xml_request = new XMLHttpRequest();
		xml_request.onreadystatechange = ajax_jobview_reply;
		xml_request.open("POST", "jobview.php", true);
		xml_request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		document.getElementById("jview").style.display="block";
		xml_request.send('jid='+jid+"&width="+(document.getElementById("jview").clientWidth-16));
	}catch (e){
		alert(e);
	}
}

function ajax_jobview_reply(){
	if (xml_request.readyState==4){
		showWait(false);
		document.getElementById("jlist").style.display="none";
		document.getElementById("jinfo").style.display="none";
		document.getElementById("jview").style.display="block";
		if (xml_request.status==200) {
			if (xml_request.responseText) {
				jview.innerHTML=xml_request.responseText;
				document.getElementById("listtasks").style.display="none";
				document.getElementById("infotasks").style.display="none";
				document.getElementById("viewtasks").style.display="block";
				resizefunc();
			}else{
				//Notify error
				jview.innerHTML="Error loading job view via AJAX";
			}
		}else{
			//Notify error
			jview.innerHTML="<TABLE><TR><TD colspan='5' align='center'>AJAX Load Failed</TD></TR></TABLE>";
		}
	}
}

function ajax_refresh() {
	try{
		showWait(true);
		xml_request = new XMLHttpRequest();
		xml_request.onreadystatechange = ajax_refresh_reply;
		xml_request.open("POST", "joblist.php", true);
		xml_request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		xml_request.send();
	}catch (e){
		alert(e);
	}
}

function ajax_refresh_reply(){
	if (xml_request.readyState==4){
		showWait(false);
		if (xml_request.status==200) {
			if (xml_request.responseText) {
				jlist.innerHTML=xml_request.responseText;
				resizefunc();
			}else{
				//Notify error
				jlist.innerHTML="Error loading jobs via AJAX";
			}
		}else{
			//Notify error
			jlist.innerHTML="<TABLE><TR><TD colspan='5' align='center'>AJAX Load Failed</TD></TR></TABLE>";
		}
	}
}

function ajax_loadlog(username) {
	try{
		xml_request = new XMLHttpRequest();
		xml_request.onreadystatechange = ajax_loadlog_reply;
		xml_request.open("POST", "userlog.php", true);
		xml_request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		xml_request.send("username="+username);
	}catch (e){
		alert(e);
	}
}

function ajax_loadlog_reply(){
	if (xml_request.readyState==4){
		if (xml_request.status==200) {
			if (xml_request.responseText) {
				eventlist.innerHTML=xml_request.responseText;
				document.getElementById("userlog").style.display="block";
				resizefunc();
			}else{
				//Notify error
				eventlist.innerHTML="Error loading users via AJAX";
			}
		}else{
			//Notify error
			eventlist.innerHTML="AJAX Load Failed";
		}
	}
}

//End AJAX Code