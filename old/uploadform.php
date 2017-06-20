<html>
<body>
<b>Upload HP</b>
<form action="upload_hp.php" method="post" enctype="multipart/form-data">

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

<b>Upload Genrad</b>
<form action="upload_genrad.php" method="post" enctype="multipart/form-data">

<label for="job">Job:</label>
<input type="text" name="job" />
<br />

<label for="gfile">NAE File:</label>
<input type="file" name="gfile" id="gfile" /> 
<br />

<input type="submit" name="submit" value="Submit" />

</form>

<br/>
<hr/>

<b>Upload Teradyne</b>
<form action="upload_teradyne.php" method="post" enctype="multipart/form-data">

<label for="job">Job:</label>
<input type="text" name="job" />
<br />

<label for="tfile">NAE File:</label>
<input type="file" name="tfile" id="tfile" /> 
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