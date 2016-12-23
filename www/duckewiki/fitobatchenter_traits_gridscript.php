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
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}


		
//$tbname = 'fitobatchenter_especimenes_386_89';
//$filtroid = 386;
//$formid = 89;
//$sampletype = 'especimenes';

$qq = "SELECT * FROM ".$tbname;
$rr = @mysql_query($qq,$conn);
$nr = @mysql_numrows($rr);
@mysql_free_result($rr);

if (($nr==0 || $updatetable>0)) {
	$qq = "DROP TABLE ".$tbname;
	$rq = @mysql_query($qq,$conn);

$qq = "SELECT 
'edit-icon.png' AS EDIT,";
if ($sampletype=='especimenes') {
	$qq .= "pltb.EspecimenID, ";
	$ssp = "pltb.EspecimenID";
} else {
	$ssp = 0;
}
$qq .= "
pltb.PlantaID, 
pltb.DetID,
if (checkimgs(".$ssp.", pltb.PlantaID)>0,'camera.png','') as IMG,";
if ($sampletype=='especimenes') {
	$qq .= "(colpessoa.Abreviacao) as COLETOR,  
	pltb.Number as NUMERO,";
} else {
	$qq .= "checkplantaspecimens(pltb.PlantaID) AS ESPECS,
	pltb.PlantaTag as TAG_NUM,
	(localidadefields(pltb.GazetteerID, pltb.GPSPointID,0,0,0, 'GAZ_PAR1')) as LOCAL,
";
}
$qq .= "famtb.Familia as FAMILIA,
IF(iddet.InfraEspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie,' ',infsptb.InfraEspecie),IF(iddet.EspecieID>0,CONCAT(gentb.Genero,' ',sptb.Especie),IF(iddet.GeneroID>0,gentb.Genero,famtb.Familia))) as NOME";
if ($formid>0) {
	$qf = "SELECT  form.* ,TraitTipo, maketraitname(tr.TraitID) AS nome FROM FormulariosTraitsList as form JOIN Traits AS tr USING(TraitID) WHERE FormID=".$formid." AND TraitTipo<>'Variavel|Imagem' ORDER BY Ordem";
	$resf = mysql_query($qf,$conn);
	//echo $qf."<br />";
	while ($rowf = mysql_fetch_assoc($resf)) {
		//echopre($rowf);
		$qp = '';
		$trid = $rowf['TraitID'];
		$trn = $rowf['nome'];
		$trtp = $rowf['TraitTipo'];
		if ($sampletype=='especimenes') {
			if ($trtp=='Variavel|Quantitativo') {
				$qp = ", traitvariation_forgrid(".$trid.",pltb.EspecimenID,0,1) AS ".$trn.", traitvariation_forgrid(".$trid.",pltb.EspecimenID,1,1) AS  ".$trn."_UNIT";
			} else {
				if ($trtp=='Variavel|Categoria') {
					$qp = ", (traitvariation_forgrid(".$trid.",pltb.EspecimenID,0,1)) AS ".$trn;
				} else {
					$qp = ", (traitvariation_forgrid(".$trid.",pltb.EspecimenID,0,1)) AS ".$trn;
				}
			}
		} else {
			if ($trtp=='Variavel|Quantitativo') {
				$qp = ", traitvariation_forgrid(".$trid.",pltb.PlantaID,0,0) AS ".$trn.", traitvariation_forgrid(".$trid.",pltb.PlantaID,1,0) AS  ".$trn."_UNIT";
			} else {
				if ($trtp=='Variavel|Categoria') {
					$qp = ", (traitvariation_forgrid(".$trid.",pltb.PlantaID,0,0)) AS ".$trn;
				} else {
					$qp = ", (traitvariation_forgrid(".$trid.",pltb.PlantaID,0,0)) AS ".$trn;
				}
	
			}
		}
		//echo $qp."<br />";
		$qq .= $qp;
		}
}
if ($sampletype=='especimenes') {
	$qq .= " FROM Especimenes as pltb
	LEFT JOIN Pessoas as colpessoa ON pltb.ColetorID=colpessoa.PessoaID";
	$qwhere =   " JOIN FiltrosSpecs as fl ON pltb.EspecimenID=fl.EspecimenID WHERE fl.FiltroID=".$filtro;
} else {
	$qq .= " FROM Plantas as pltb";
	$qwhere =   " JOIN FiltrosSpecs as fl ON pltb.PlantaID=fl.PlantaID WHERE fl.FiltroID=".$filtro;
}
$qq .= "
LEFT JOIN Identidade as iddet ON pltb.DetID=iddet.DetID 
LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID 
LEFT JOIN Tax_Especies as sptb ON iddet.EspecieID=sptb.EspecieID 
LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID  
LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID ";

if ($sampletype=='especimenes') {
	$qz = 'SELECT COUNT(*) as nrecs FROM Especimenes as pltb  '.$qwhere;
} else {
	$qz = 'SELECT COUNT(*) as nrecs FROM Plantas as pltb  '.$qwhere;
}
$rz = mysql_query($qz,$conn);
$rwz = mysql_fetch_assoc($rz);
$nrz = $rwz['nrecs'];
$stepsize = 2000;
$nsteps = ceil($nrz/$stepsize);
$qq = $qq.$qwhere;

$step=0;
while ( $step<=$nsteps ) {
	if ($step==0) {
		$st1 = 0;
		$qbase = "CREATE TABLE IF NOT EXISTS ".$tbname;
	} 
	else {
		$st1 = $st1+$stepsize;
		$qbase = "INSERT INTO ".$tbname;
	}
	$qqq = $qbase." (".$qq." LIMIT $st1,$stepsize)";
	//echo $qqq."<br><br>";
	$check = mysql_query($qqq,$conn);
	if ($check) {
		$perc = ceil(($step/$nsteps)*100);
		$qnu = "UPDATE `temp_".$tbname."` SET percentage=".$perc; 
		mysql_query($qnu);
		session_write_close();
	}
	$step = $step+1;
} 
if ($step>$nsteps) {
	$qq = "ALTER TABLE `".$tbname."`  ADD `EXISTE` INT(10) DEFAULT NULL AFTER `EDIT`";
	mysql_query($qq,$conn);
	if ($sampletype=='especimenes') {
		$qq = "ALTER TABLE ".$tbname." ADD PRIMARY KEY(EspecimenID)";
		mysql_query($qq,$conn);
		$sql = "CREATE INDEX COLETOR ON ".$tbname."  (COLETOR)";
		mysql_query($sql,$conn);
		$sql = "CREATE INDEX NUMERO ON ".$tbname."  (NUMERO)";
		mysql_query($sql,$conn);
	} else {
		$qq = "ALTER TABLE ".$tbname."  ADD `TEM_ETIQUETA` INT(10) DEFAULT NULL AFTER `LOCAL`";
		mysql_query($qq,$conn);
		$qq = "ALTER TABLE ".$tbname."  ADD `TEMP_COLETOR` CHAR(100) DEFAULT NULL AFTER `TEM_ETIQUETA`";
		mysql_query($qq,$conn);
		$qq = "ALTER TABLE ".$tbname."  ADD `TEMP_NUMBER` CHAR(20) DEFAULT NULL AFTER `TEMP_COLETOR`";
		mysql_query($qq,$conn);
		$qq = "ALTER TABLE `".$tbname."`  ADD `TEMP_DATA_COLETA` DATE DEFAULT NULL AFTER `TEMP_NUMBER`";
		mysql_query($qq,$conn);
		$qq = "ALTER TABLE ".$tbname."  ADD `TEMP_FERT` CHAR(100) DEFAULT NULL AFTER `TEMP_DATA_COLETA`";
		mysql_query($qq,$conn);
		$qq = "ALTER TABLE ".$tbname."  ADD `TEMP_NDUPS` INT(10) DEFAULT NULL AFTER `TEMP_FERT`";
		mysql_query($qq,$conn);

		$qq = "ALTER TABLE ".$tbname." ADD PRIMARY KEY(PlantaID)";
		mysql_query($qq,$conn);
		$sql = "CREATE INDEX LOCAL ON ".$tbname."  (LOCAL)";
		mysql_query($sql,$conn);
		$sql = "CREATE INDEX TAG_NUM ON ".$tbname."  (TAG_NUM)";
		mysql_query($sql,$conn);
	}
	$sql = "CREATE INDEX NOME ON ".$tbname."  (NOME)";
	mysql_query($sql,$conn);
	$sql = "CREATE INDEX FAMILIA ON ".$tbname."  (FAMILIA)";
	mysql_query($sql,$conn);
}
		$perc = 100;
		$qnu = "UPDATE `temp_".$tbname."` SET percentage=".$perc; 
		mysql_query($qnu,$conn);
		echo "Concluido";
} 
else {
		$perc = 100;
		$qnu = "UPDATE `temp_".$tbname."` SET percentage=".$perc; 
		mysql_query($qnu,$conn);
		echo "Concluido";
}
session_write_close();
?>