<html>
<body>
<b>Upload</b>
<form action="upload.php" method="post" enctype="multipart/form-data">

<label for="job">Job:</label>
<input type="text" name="job" />
<br />

<label for="wfile">Wires File:</label>
<input type="file" name="wfile" id="wfile" /> 
<br />
<label for="ifile">Inserts File:</label>
<input type="file" name="ifile" id="ifile" /> 
<br />


<input type="submit" name="submit" value="Submit" />

</form>

<br/>
<hr/>

<b>Show</b>
<form action="show.php" method="post" enctype="multipart/form-data">

<label for="job">Job:</label>
<input type="text" name="job" />
<br />

<input type="submit" name="submit" value="Submit" />

</form>

</body>
</html>