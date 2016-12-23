<?php
//Start session
session_start();
$filename = 'temp/'.$_GET['filename'];
$fh = fopen("temp/".$pgfilename, 'w');
$perc = 0;
fwrite($fh, $perc);
fclose($fh);
echo 0;
session_write_close();

?>
