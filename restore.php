<?php
// Get the provided arg
$id=$_GET['id'];

// Check if the file has needed args
if ($id==NULL){
  print("<script type='text/javascript'>window.alert('You have not provided a backup to restore.')</script>");
  print("<script type='text/javascript'>window.location='manage.php'</script>");
  print("You have not provided a backup to restore.<br>Click <a href='manage.php'>here</a> if your browser doesn't automatically redirect you.");
  die();
}

// Include settings
include("config.php");

// Generate filename and set error variables
$filename = 'backup/' . $id . '.sql';
$sqlErrorText = '';
$sqlErrorCode = 0;
$sqlStmt      = '';

// Restore the backup
$con = mysql_connect($DBhost,$DBuser,$DBpass);
if ($con !== false){
  // Load and explode the sql file
  mysql_select_db("$DBName");
  $f = fopen($filename,"r+");
  $sqlFile = fread($f,filesize($filename));
  $sqlArray = explode(';<|||||||>',$sqlFile);
          
  // Process the sql file by statements
  foreach ($sqlArray as $stmt) {
    if (strlen($stmt)>3){
         $result = mysql_query($stmt);
    }
  }
}

// Print message (error or success)
if ($sqlErrorCode == 0){
   print("Database restored successfully!<br>\n");
   print("Backup used: " . $filename . "<br>\n");
} else {
   print("An error occurred while restoring backup!<br><br>\n");
   print("Error code: $sqlErrorCode<br>\n");
   print("Error text: $sqlErrorText<br>\n");
   print("Statement:<br/> $sqlStmt<br>");
}

// Close the connection
mysql_close();

// Change the filename from sql to zip
$filename = str_replace('.sql', '.zip', $filename);

// Include this library so we could delete the file
include('pclzip.lib.php');

// Remove the current dir
rrmdir(dirname(getcwd()));

// Recursively remove dir
function rrmdir($dir) { 
	if (is_dir($dir)) { 
		$objects = scandir($dir); 
		foreach ($objects as $object) { 
			if ($object != "." && $object != ".." && $object != "restore.php" && $object != $filename) { 
				if (filetype($dir."/".$object) == "dir") {
					rrmdir($dir."/".$object); 
				} else {
					unlink($dir."/".$object); 
				}
			} 
		} 
	reset($objects); 
	} 
}

// Extract archive
$archive = new PclZip($filename);
if ($archive->extract(PCLZIP_OPT_PATH, "../") == 0) {
	die("Error : ".$archive->errorInfo(true));
}

// Remove two left files
unlink($filename);
rmdir("backup");
unlink("restore.php");
rmdir(getcwd());

// Files restored successfully
print("Files restored successfully!<br>\n");
print("Backup used: " . $filename . "<br>\n");
?> 
