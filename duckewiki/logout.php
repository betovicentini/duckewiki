<?php
//Start session
session_start();

//Unset the variables stored in session
unset($_SESSION['userid']);
unset($_SESSION['userfirstname']);
unset($_SESSION['userlastname']);
unset($_SESSION['userlogin']);
unset($_SESSION['sessiondate']);
unset($_SESSION['accesslevel']);
unset($_SESSION['checklist_species']);
unset($_SESSION['checklist_specimens']);
unset($_SESSION['checklist_plantas']);
unset($_SESSION['checklist_plots']);




session_write_close();
header("location: index.php");
exit();

?>

