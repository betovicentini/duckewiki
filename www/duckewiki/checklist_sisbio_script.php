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

$detid =0;
$detid = $famid+$genid+$specid+$infspecid+$detid;

$tbname = "temp_sisbio_".substr(session_id(),0,10);

$qq = "DROP TABLE ".$tbname;
@mysql_query($qq,$conn);

$qq = "
CREATE TABLE IF NOT EXISTS ".$tbname." (SELECT
0 as Marcado,
pltb.EspecimenID, 
'edit-icon.png' AS EDIT,
ROUND(getlatlong(pltb.Latitude+0, pltb.Longitude+0, pltb.GPSPointID, pltb.GazetteerID, 0, 0, 0, 1),6) AS Latitude_Dg,
ROUND(getlatlong(pltb.Latitude+0, pltb.Longitude+0, pltb.GPSPointID, pltb.GazetteerID, 0, 0, 0, 0),6) AS Longitude_Dg,
'WGS-84' AS DATUM,
IF(ABS(pltb.Longitude)>0,'Coordenada exata da ocorrência', IF(pltb.GPSPointID>0,'Coordenada exata da ocorrência', IF(pltb.GazetteerID>0,'Coordenada de referência da área amostrada',IF(ABS(getlatlong(pltb.Latitude+0, pltb.Longitude+0, pltb.GPSPointID, pltb.GazetteerID, 0, 0, 0, 1))>0, 'Coordenada de referência da área amostrada','')))) AS CoorRef,
'Plantae' as REINO,
(if(gettaxonname(pltb.DetID,0,0) IS NULL,'Plantae',gettaxonname(pltb.DetID,0,0))) as TAXON,
(gettaxonlevel(pltb.DetID,0)) as NIVEL_TAXONOMICO,
('Coleta de espécimes') as METODO,
'Indivíduo' as UNIDADE,
nduplicates(".$duplicatesTraitID.",pltb.EspecimenID,'Especimenes')+0 AS QUANTIDADE,
IF(CONCAT(pltb.Day,'-',pltb.Mes,'-',pltb.Ano)<>'00-00-0000',CONCAT(pltb.Day,'/',pltb.Mes,'/',pltb.Ano),'') as DATA_INICIAL,
IF(CONCAT(pltb.Day,'-',pltb.Mes,'-',pltb.Ano)<>'00-00-0000',CONCAT(pltb.Day,'/',pltb.Mes,'/',pltb.Ano),'')  AS DATA_FINAL,
('Depositado em coleção/museu') AS  TIPO_DESTINACAO,
('".$herbariumnome."') AS  INSTITUICAO,
CONCAT(colpessoa.Sobrenome,' ',pltb.Number) as TOMBAMENTO
FROM Especimenes as pltb
LEFT JOIN Pessoas as colpessoa ON pltb.ColetorID=colpessoa.PessoaID ";
if ($filtro>0) {
	$qq .= " JOIN FiltrosSpecs as fl ON fl.EspecimenID=pltb.EspecimenID WHERE fl.FiltroID=".$filtro.")";
} else {
	if ($detid>0) {
			if ($infspecid>0) {
				$qq .= " WHERE iddet.InfraEspecieID=".$infspecid;
			} else {
				if ($specid>0) {
					$qq .= " WHERE iddet.EspecieID=".$specid;
				} else {
					if ($genid>0) {
						$qq .= " WHERE iddet.GeneroID=".$genid;
					} 
					else {
						$qq .= " WHERE iddet.FamiliaID=".$famid;
					}
				}
			}		
	}
	$qq .= ")";
}
//echo $qq."<br>";
$check = mysql_query($qq,$conn);
if ($check) {
	$qq = "ALTER TABLE ".$tbname." ADD PRIMARY KEY(EspecimenID)";
	mysql_query($qq,$conn);

	$qq = "SELECT * FROM ".$tbname;
	$res = mysql_query($qq,$conn);
	$nr = mysql_numrows($res);
} else {
	$nr =0;
}
echo $nr;
session_write_close();

?>