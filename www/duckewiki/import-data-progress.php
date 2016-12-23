<?php
//Start session
session_start();
$filename = 'temp/'.$_GET['filename'];
$progresso = '';
if (file_exists($filename)) {
	//echo $filename."<br >";
	$progresso = file_get_contents($filename, true);
	//echopre($progresso);
}
echo $progresso+0;
session_write_close();
?>
