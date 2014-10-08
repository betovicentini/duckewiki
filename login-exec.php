<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//CABECALHO
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />"
);
$which_java = array();

$title = 'Login';
$body = '';
	//Array to store validation errors
	$errmsg_arr = array();

	//Validation error flag
	$errflag = false;

	//Connect to mysql server
	$dbname = $_SESSION['dbname'];
	$conn = ConectaDB($dbname);

	//Function to sanitize values received from the form. Prevents SQL injection
	function clean($str) {
		$str = @trim($str);
		if(get_magic_quotes_gpc()) {
			$str = stripslashes($str);
		}
		return mysql_real_escape_string($str);
	}

	//Sanitize the POST values
	$login = clean($_POST['login']);
	$password = clean($_POST['password']);
	//echo $login;
	//echo $password;
	//Input Validations
	if($login == '') {
		$errmsg_arr[] = $namefaileduser;
		$errflag = true;
	}
	if($password == '') {
		$errmsg_arr[] = $namefailedpwd;
		$errflag = true;
	}

	//If there are input validations, redirect back to the login form
	if($errflag) {
		$_SESSION['ERRMSG_ARR'] = $errmsg_arr;
		session_write_close();
		header("location: login-form.php");
		exit();
	}
	echo "&nbsp;";
	//Create query
	//if ($login=='beto') {
	$qry="SELECT * FROM Users WHERE Login='".$login."' AND Passwd='".MD5($password)."'";
	//echo $qry;
	$result=mysql_query($qry,$conn);
	//}
	//Check whether the query was successful or not
	if($result) {
		if(mysql_num_rows($result) == 1) {
			//Login Successful
			session_regenerate_id();
			$member = mysql_fetch_assoc($result);
			$_SESSION['userid'] = $member['UserID'];
			$_SESSION['userfirstname'] = $member['FirstName'];
			$_SESSION['userlastname'] = $member['LastName'];
			$_SESSION['userlogin'] = $member['Login'];
			$_SESSION['accesslevel'] = $member['AccessLevel'];
			$_SESSION['sessiondate'] = @date("Y-m-d");
			//$_SESSION['dbname'] = $dbname;
			session_write_close();
			if (($blockacess+0)==0  ||   $member['AccessLevel']=='admin') {
				header("location: check_listall.php");
			} else {
				header("location: index.php");
			}
			exit();
		}else {
			//Login failed
			header("location: index.php");
			exit();
		}
	} else {
		$menu=FALSE;
		FazHeader($title,$body,$which_css,$which_java,$menu);
		//$namefailedlogin = "Em manutenção. Desculpe!";
		echo($namefailedlogin);
		$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
		"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
		"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
		FazFooter($which_java,$calendar=FALSE,$footer=$menu);
	}
?>