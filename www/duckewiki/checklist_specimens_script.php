<?php
//Start session
ini_set("memory_limit","-1");
ini_set("mysql.allow_persistent","-1");

//Start session
session_start();

//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
//$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

//////PEGA E LIMPA VARIAVEIS
$ppost = cleangetpost($_POST,$conn);
@extract($ppost);
$arval = $ppost;
$gget = cleangetpost($_GET,$conn);
@extract($gget);

//CHECA SE O USUARIO TEM PERMISSAO
$uuid = cleanQuery($_SESSION['userid'],$conn);
if(!isset($uuid) || 
	(trim($uuid)=='')) {
		header("location: access-denied.php");
	exit();
} else {
	$acclevel = $_SESSION['accesslevel'];
}

//CABECALHO
$ispopup=1;
$menu = FALSE;
if (empty($herbariumsigla)) {
		$herbariumsigla = 'HERB_NO';
}


if ($idd>0 && !empty($tableref)) {
			$qq = "DROP TABLE ".$tbname;
			@mysql_query($qq,$conn);
			$qq = "CREATE TABLE ".$tbname." (SELECT * FROM checklist_speclist WHERE isvalidlocal(GazetteerID,GPSPointID, ".$idd.", '".$tableref."'))"; 
			mysql_query($qq,$conn);
			$update=0;
}

$qq = "SELECT * FROM ".$tbname;
$rr = @mysql_query($qq,$conn);
$nr = @mysql_numrows($rr);
@mysql_free_result($rr);

//echo $update."  aqui";
if (($nr==0 || $update>0)) {
//echo $update."  aqui2";
	$qnu = "UPDATE `temp_progspec".$tbname."` SET percentage=1"; 
	mysql_query($qnu);
	session_write_close();
		
	unset($_SESSION['plothervars']);
	unset($_SESSION['qq']);
	unset($qq);
	$qq = "DROP TABLE ".$tbname;
	$rq = @mysql_query($qq,$conn);
$qq = "(".checklistspecsqq($herbariumsigla,$duplicatesTraitID,$daptraitid,$alturatraitid,$habitotraitid,$traitfertid,$quickview,$quicktbname,$checkoleo=0,$traitsilica);
if ($filtro>0) {
	$qwhere =  " JOIN FiltrosSpecs as fl ON fl.EspecimenID=pltb.EspecimenID WHERE fl.FiltroID=".$filtro.")";
} 
else {
	if ($detid>0) {
			if ($infspecid>0) {
				$qwhere = " WHERE iddet.InfraEspecieID=".$infspecid;
			} else {
				if ($specid>0) {
					$qwhere = " WHERE iddet.EspecieID=".$specid;
				} else {
					if ($genid>0) {
						$qwhere = " WHERE iddet.GeneroID=".$genid;
					} 
					else {
						$qwhere = " WHERE iddet.FamiliaID=".$famid;
					}
				}
			}		
	}
	if ($quickview>0 && !empty($quicktbname)) {
		$qwhere =" WHERE filtertab.EspecimenID>0";
	}
	if ($plantaid>0) {
		$qwhere=" WHERE pltb.PlantaID=".$plantaid;
	}
}

$qz = 'SELECT COUNT(*) as nrecs';
if ($quickview>0 && !empty($quicktbname)) {
	$qz .= " FROM ".$quicktbname." as filtertab JOIN Especimenes as pltb USING(EspecimenID)"; 
} 
else {
	$qz .= " FROM Especimenes as pltb";
}
$qz .= "
LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID ".$qwhere;


$rz = mysql_query($qz,$conn);
$rwz = mysql_fetch_assoc($rz);
$nrz = $rwz['nrecs'];
//mysql_free_result($rz);
//$_SESSION['exportnresult'] = $nrz;
$stepsize = 100;
$nsteps = ceil($nrz/$stepsize);
$qq = $qq.$qwhere;
//$_SESSION['qq'] = $qq;
$step=0;
//echo $qq."<br />";
$lixo = 10230;
if ($lixo==10230) {
while ( $step<=$nsteps ) {
//if ($prepared==1 && $step<=$nsteps && $update>0) {
	if ($step==0) {
		$st1 = 0;
		$qbase = "CREATE TABLE IF NOT EXISTS ".$tbname;
	} 
	else {
		//$qq = $_SESSION['qq'];
		//$plothervars = unserialize($_SESSION['plothervars']);
		$st1 = $st1+$stepsize;
		$qbase = "INSERT INTO ".$tbname;
	}
	$qqq = $qbase." ".$qq."  ORDER BY pltb.Ano,pltb.Mes,pltb.Day DESC LIMIT $st1,$stepsize)";
	//echo $qqq."<br />";
	$check = mysql_query($qqq,$conn);
	if ($check) {
		$perc = ceil(($step/$nsteps)*100);
		$qnu = "UPDATE `temp_progspec".$tbname."` SET percentage=".$perc; 
		mysql_query($qnu);
		//echo $perc."<br />";
		session_write_close();
	}
	$step = $step+1;
} 
if ($step>$nsteps) {
		$qq = "ALTER TABLE ".$tbname." ADD PRIMARY KEY(EspecimenID)";
		mysql_query($qq,$conn);
		$sql = "CREATE INDEX COLETOR ON ".$tbname."  (COLETOR)";
		mysql_query($sql,$conn);
		$sql = "CREATE INDEX NUMERO ON ".$tbname."  (NUMERO)";
		mysql_query($sql,$conn);
		$sql = "CREATE INDEX ".$herbariumsigla." ON ".$tbname."  (".$herbariumsigla.")";
		mysql_query($sql,$conn);
		$sql = "CREATE INDEX NOME ON ".$tbname."  (NOME)";
		mysql_query($sql,$conn);
		$sql = "CREATE INDEX NOMEA ON ".$tbname."  (NOME_AUTOR)";
		mysql_query($sql,$conn);
		$sql = "CREATE INDEX PAIS ON ".$tbname."  (PAIS)";
		mysql_query($sql,$conn);
		$sql = "CREATE INDEX ESTADO ON ".$tbname."  (ESTADO)";
		mysql_query($sql,$conn);
		$sql = "CREATE INDEX MUNICIPIO ON ".$tbname."  (MUNICIPIO)";
		mysql_query($sql,$conn);
		$sql = "CREATE INDEX LOCAL ON ".$tbname."  (LOCAL)";
		mysql_query($sql,$conn);
		$sql = "CREATE INDEX LOCALSIMPLES ON ".$tbname."  (LOCALSIMPLES)";
		mysql_query($sql,$conn);
		unset($_SESSION['qq']);
		unset($_SESSION['exportnresult']);
}
$qnu = "UPDATE `temp_progspec".$tbname."` SET percentage='100'"; 
mysql_query($qnu);
echo "Concluido";
}
//session_write_close();
} 
else {
	$qnu = "UPDATE `temp_progspec".$tbname."` SET percentage='100'"; 
	mysql_query($qnu);
	echo "Concluido";
}
session_write_close();

?>
