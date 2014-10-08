<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

//CHECA SE O USUARIO TEM PERMISSAO
$uuid = cleanQuery($_SESSION['userid'],$conn);
if(!isset($uuid) || 
	(trim($uuid)=='')) {
		header("location: access-denied.php");
	exit();
} else {
	$acclevel = $_SESSION['accesslevel'];
}

//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;

$gget = cleangetpost($_GET,$conn);
@extract($gget);

//CABECALHO
$ispopup=1;
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
//"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
//"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'Cria relações entre tabelas InnoDB';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if ($acclevel=='admin') {

if (!isset($enviado)) {
echo "
<br />
<form enctype='multipart/form-data' action='updateSetRelations.php' method='post'>
<input type='hidden' name='enviado' value='1'>
<table align='center' class='myformtable' cellpadding=\"8\">
<thead>
<tr>
<td class='tabhead' >Relações entre tabelas InnoDB&nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
		$help = "Este script cria relações entre as tabelas do banco de dados. Se você não sabe o que está fazendo, NÃO CONTINUE!!";
		echo " onclick=\"javascript:alert('$help');\" /></td>
</tr>
</thead>
<tbody>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td align='center'>
    <input type='submit' value='Executar' class='bsubmit' />
  </td>
</tr>
</tbody>
</table>
</form>
";
} 
else {

$qs = "SELECT * FROM ConstrainsTAB";
$res = mysql_query($qs,$conn);
if ($res)  {
$i=1;
while($row = mysql_fetch_assoc($res)) {
	//PEGA OS VALORES
	$tbref = $row['RefTable'];
	$tbrun = trim($row['Tabela']);
	if (empty($tbrun))  {
	
		$qs = "SELECT tb.`TABLE_NAME` as tabb FROM information_schema.`TABLES` AS tb WHERE  tb.`TABLE_SCHEMA`='".$dbname."'  AND tb.`TABLE_NAME` NOT LIKE 'temp%' AND tb.`TABLE_NAME` NOT LIKE 'batchenter%'  AND tb.`TABLE_NAME` NOT LIKE 'checklist%'  AND tb.`TABLE_NAME` NOT LIKE 'processo_%'  AND tb.`TABLE_NAME` NOT LIKE 'Especialistas_%'  AND tb.`TABLE_NAME` NOT LIKE 'fitobatchenter%'   AND tb.`TABLE_NAME` NOT LIKE 'tolink_%'  AND tb.`TABLE_NAME` NOT LIKE 'Tax_Old%'  AND tb.`TABLE_NAME` NOT LIKE 'TABLE%'  AND tb.`TABLE_NAME`<>'".$tbref."' AND tb.`TABLE_NAME` NOT LIKE 'Change%'  AND tb.`TABLE_NAME` NOT LIKE 'IPNIextended%'  ";
		//echo $qs;
		$rs = mysql_query($qs,$conn);
		$ii=1;
		while($rws = mysql_fetch_assoc($rs)) {
			 $qf = "SHOW COLUMNS FROM `".$rws['tabb']."` LIKE '".$row['Coluna']."'";
			 //echo $qf."<br />";
			 $rf = mysql_query($qf,$conn);
			 $nrf = mysql_numrows($rf);
			 if ($nrf>0) {
$qn -= "SET FOREIGN_KEY_CHECKS=0";
mysql_query($qn,$conn);

			 	echo $i."   ".$row['Coluna']."  encontrada em ".$rws['tabb']."    ";
			 	
			 	$qrr = "ALTER TABLE `".$rws['tabb']."` CHANGE `".$row['Coluna']."` `".$row['Coluna']."` INT(10) UNSIGNED NULL DEFAULT NULL";
			 	mysql_query($qrr,$conn);
				//echo "<br />".$qrr."<br />";
				
				$qrrr = "ALTER TABLE `".$tbref."` CHANGE `".$row['RefColuna']."`  `".$row['RefColuna']."`  INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;";
			 	mysql_query($qrrr,$conn);

				$qrr =  "UPDATE `".$rws['tabb']."` SET `".$row['Coluna']."`= NULL WHERE `".$row['Coluna']."`=0";
			 	mysql_query($qrr,$conn);
				$qrr =  "ALTER TABLE `".$rws['tabb']."` DROP INDEX `".$row['Coluna']."`, ADD INDEX `".$row['Coluna']."` (`".$row['Coluna']."`) COMMENT ''  ";
				//echo "<br />".$qrr."<br />";

				mysql_query($qrr,$conn);
				$consname = $rws['tabb'].'_'.$row['Coluna'];
				//CONSTRAINT  `".$consname."` 
				$qrr  = "ALTER TABLE `".$rws['tabb']."` ADD FOREIGN KEY ( `".$row['Coluna']."` ) REFERENCES `".$tbref."` ( `".$row['RefColuna']."` )";
			 	$rd = mysql_query($qrr,$conn);
				//echo "<br />".$qrr."<br />";
			 	if ($rd) {
			 		echo " CONSTRAIN CRIADO<br />";
			 	} else {
			 		$ii++;
			 		echo " ERRO<br />";
			 		echo "<br />".$qrr."<br />"; 
			 	}
$qn -= "SET FOREIGN_KEY_CHECKS=1";
mysql_query($qn,$conn);
			 	
			 }
		}
	} 
	else {
		$consname = $tbrun.'_'.$row['Coluna'];
	 	echo $i."   ".$row['Coluna']."  da tabela ".$tbrun."    ";
		$qrr = "ALTER TABLE `". $tbrun."` CHANGE `".$row['Coluna']."` `".$row['Coluna']."` INT(10) UNSIGNED NULL DEFAULT NULL";
	 	mysql_query($qrr,$conn);
		$qrr =  "UPDATE `". $tbrun."` SET `".$row['Coluna']."`= NULL WHERE `".$row['Coluna']."`=0";
	 	mysql_query($qrr,$conn);
		$qrr =  "ALTER TABLE `". $tbrun."` DROP INDEX `".$row['Coluna']."`, ADD INDEX `".$row['Coluna']."` (`".$row['Coluna']."`) COMMENT ''  ";
	 	mysql_query($qrr,$conn);
	 	//CONSTRAINT   `".$consname."`   
		$qrr  = "ALTER TABLE `". $tbrun."` ADD FOREIGN KEY ( `".$row['Coluna']."` ) REFERENCES `".$tbref."` ( `".$row['RefColuna']."` )";
		$rd = mysql_query($qrr,$conn);
		//echo "<br />".$qrr."<br />";
		if ($rd) {
			 		echo " CONSTRAIN CRIADO<br />";
			 	} else {
			 		echo " ERRO<br />";
			 		echo "<br />".$qrr."<br />"; 
		}
		//echo $tbrun."  not empty<br />";
	}
	$i++;
	session_write_close();
	flush();
}
}
echo "O TOTAL DE ERROS FOI  ".$ii;

$qn -= "SET FOREIGN_KEY_CHECKS=1";
mysql_query($qn,$conn);

//PARA APAGAR FOREIGN_KEY CONSTRAINTS
//$qq = "SELECT * FROM information_schema.`KEY_COLUMN_USAGE` WHERE `REFERENCED_TABLE_NAME` IS NOT NULL  ";
//$res = mysql_query($qq,$conn);
//if ($res)  {
//while($row = mysql_fetch_assoc($res)) {
//	//echopre($row);
//	$qu = "ALTER TABLE `".$row['TABLE_NAME']."` DROP FOREIGN KEY `".$row['CONSTRAINT_NAME']."`";
//	//echo $qu."<br>";
//	$rr = mysql_query($qu,$conn);
//	if ($rr)  {
//		echo $row['CONSTRAINT_NAME']."apagado<br >";
//	}
//}
//}
//
}

} 
else {
	echo "Você não tem PERMISSÃO aqui!<br />";
}


$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
//, "<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
//"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
FazFooter($which_java,$calendar=TRUE,$footer=$menu);
?>