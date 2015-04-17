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
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />"
//,"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
//"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'Imprime Etiquetas';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if (empty($herbariumsigla)) {
	$herbariumsigla = 'HERB_NO';
}

//echopre($ppost);
//definicoes
$filename = $_SESSION['sessiondate']."_".$_SESSION['userid']."_".substr(session_id(),0,10).".pdf";
$temptable = "temp_Etiqueta_".$_SESSION['userid']."_".substr(session_id(),0,10);


if (!empty($filtro)) { 
	if (empty($etitype)) { $etitype='EspecimenesIDS';}
	$qq = "SELECT * FROM Filtros WHERE FiltroID='".$filtro."'";
	$res = mysql_query($qq);
	$rr = mysql_fetch_assoc($res);
	$ids_string= $rr[$etitype];
	if ($etitype =='EspecimenesIDS') { 
		$tbname = 'Especimenes';
		$tbname2 = 'Plantas';
	} else {
		$tbname = 'Plantas';
		$tbname2 = 'Especimenes';
	}
} elseif (!empty($especimenesids) || $processoid>0) {
	$tbname = 'Especimenes';
}

if (isset($tbname)) {
	$qd = "DROP TABLE IF EXISTS ".$temptable;
	mysql_query($qd,$conn);

	$qd = "SET lc_time_names = 'pt_BR'";
	mysql_query($qd,$conn);

	$qq = "CREATE TABLE ".$temptable." (TempID INT(10) NOT NULL AUTO_INCREMENT, PRIMARY KEY (TempID))";

	if ($etitype =='EspecimenesIDS') { 
		$qq .= " SELECT
maintb.EspecimenID as wikid, 
colpessoa.Abreviacao as coletor, 
maintb.Ano as year,
maintb.Mes as month,
maintb.Day as day,
CONCAT(IF(maintb.Prefixo IS NULL OR maintb.Prefixo='','',CONCAT(maintb.Prefixo,'-')),maintb.Number,IF(maintb.Sufix IS NULL OR maintb.Sufix='','',CONCAT('-',maintb.Sufix))) as numcol, 
addcolldescr(maintb.AddColIDS) as addcol";
//DATE_FORMAT(concat(IF(maintb.Ano>0,maintb.Ano,1),'-',IF(maintb.Mes>0,maintb.Mes,1),'-',IF(maintb.Day>0,maintb.Day,1)),'%d-%b-%Y') as datacol,
		//$qq .= ", IF (maintb.PlantaID>0,localidadestring(secondtb.GazetteerID,secondtb.GPSPointID,0,0,0,secondtb.Latitude,secondtb.Longitude,secondtb.Altitude), localidadestring(maintb.GazetteerID,maintb.GPSPointID,maintb.MunicipioID,maintb.ProvinceID,maintb.CountryID,maintb.Latitude,maintb.Longitude,maintb.Altitude)) as locality";
		$qq .= ", 
localidadestring(maintb.GazetteerID,maintb.GPSPointID,maintb.MunicipioID,maintb.ProvinceID,maintb.CountryID,maintb.Latitude,maintb.Longitude,maintb.Altitude) as locality";
		if ($processoid>0) {
			$qq .= ", prcc.".$herbariumsigla." as herbnum";
			$qq .= ", prcc.Herbaria as herbarios";
		} else {
			$qq .= ", INPA_ID as herbnum";
			$qq .= ", maintb.Herbaria as herbarios";
		}
		$qq .= ", plantatag(maintb.PlantaID) as tagnum";
		if ($duplisTraitID>0) {
			$qq .= ", nduplicates(".$duplisTraitID.",maintb.EspecimenID,'Especimenes') as ndups";
		} else {
			if ($duplicatesTraitID2>0) {
				$qq .= ", ".$duplicatesTraitID2." as ndups";
			} else {
				$qq .= ", 1 as ndups";
			}
		}

	} else {
		$qq .= " SELECT 
		maintb.PlantaID as wikid, 
		plantatag(maintb.PlantaID) as tagnum, 
		'' as coletor, '' as numcol, 
		IF(maintb.TaggedDate>0,YEAR(maintb.TaggedDate),0) as year, 
		IF(maintb.TaggedDate>0,MONTH(maintb.TaggedDate),0) as month, 
		IF(maintb.TaggedDate>0,DAY(maintb.TaggedDate),0) as day, 
		addcolldescr(maintb.TaggedBy) as addcol";
//		IF(maintb.TaggedDate>0,DATE_FORMAT(maintb.TaggedDate,'%d-%b-%Y'),'') as datacol, 
		$qq .= ", localidadestring(maintb.GazetteerID,maintb.GPSPointID,0,0,0,maintb.Latitude+0,maintb.Longitude+0,maintb.Altitude+0) as locality";
		$qq .= ", '' as herbnum";
		if ($formid>0) {
			//$qq .= ", notastring(PlantaID,$formid,TRUE,'Plantas') as descricao";
		}
		$qq .= ", '' as herbarios";
		if ($duplisTraitID>0) {
			$qq .= ", nduplicates(".$duplisTraitID.",PlantaID,'Plantas') as ndups";
		} else {
			if ($duplicatesTraitID2>0) {
				$qq .= ", ".$duplicatesTraitID2." as ndups";
			} else {
				$qq .= ", 1 as ndups";
			}
		}
	}
	$qq .=", famtb.Familia as familia";
	$qq .=", IF(iddet.InfraEspecieID>0 AND infsptb.Morfotipo=0,CONCAT('<i>',gentb.Genero,' ',sptb.Especie,' </i> ',sptb.EspecieAutor,' <i>',infsptb.InfraEspecieNivel,' ',infsptb.InfraEspecie,'</i> ',infsptb.InfraEspecieAutor),IF(iddet.EspecieID>0  AND sptb.Morfotipo=0,CONCAT('<i>',gentb.Genero,' ',sptb.Especie,'</i> ',sptb.EspecieAutor), IF(iddet.GeneroID>0 AND gentb.Genero<>'Indet',CONCAT('<i>',gentb.Genero,'<i>'),''))) as detnome";
	$qq .= ", CONCAT(detpessoa.Abreviacao,' [', getdetdate(iddet.DetDate,iddet.DetDateYY, iddet.DetDateMM, iddet.DetDateDD),']') as detdetby";
	if ($formidhabitat>0) {
		//$qq .= ", IF (maintb.HabitatID>0,habitatstring(maintb.HabitatID, ".$formidhabitat.", TRUE,FALSE),habitatstring(secondtb.HabitatID, ".$formidhabitat.", TRUE,FALSE))  as habitat";
		$qq .= ", habitatstring(maintb.HabitatID, ".$formidhabitat.", TRUE,FALSE)  as habitat";
	}
	if ($daptraitid>0) {
	$qq .=", traitvaluespecs(".$daptraitid.",maintb.PlantaID,maintb.EspecimenID,'mm',0,1) as DAPmm";
	}
	if ($alturatraitid>0) {
	$qq .=", traitvaluespecs(".$alturatraitid.",maintb.PlantaID,maintb.EspecimenID,'m',0,1) as ALTURAm";
	}
			//$qq .= ", traitvariation_nota($formnotes,EspecimenID) as NOTAS";
	if ($monidata==1) {
		$qq .= ", labeldescricao(maintb.EspecimenID+0,maintb.PlantaID+0,".$formid.",TRUE,FALSE) as descricao";
	} else {
		$qq .= ", labelnotes_nomoni(maintb.EspecimenID+0,0,".$formid.",TRUE,FALSE) as descricao";
	}

	//$qq .=",  IF (maintb.VernacularIDS<>'',vernaculars(maintb.VernacularIDS),vernaculars(secondtb.VernacularIDS)) as vernacular";
	$qq .=", vernaculars(maintb.VernacularIDS) as vernacular";

	//$qq .= ", IF (maintb.ProjetoID>0,projetostring(maintb.ProjetoID,TRUE,TRUE),projetostring(secondtb.ProjetoID,TRUE,TRUE)) as projeto";
	$qq .= ", projetostring(maintb.ProjetoID,TRUE,TRUE) as projeto";

	//$qq .=", IF (maintb.ProjetoID>0,projetologo(maintb.ProjetoID),projetologo(secondtb.ProjetoID)) as logofile";
	$qq .=", projetologo(maintb.ProjetoID) as logofile";

	//$qq .=", IF (maintb.ProjetoID>0,projetourl(maintb.ProjetoID),projetourl(secondtb.ProjetoID)) as prjurl";
	$qq .=", projetourl(maintb.ProjetoID) as prjurl";

	$qq .= ", '".GetLangVar('herbariocaps')."' as herbariosinpa";




	//$qq .= " FROM ".$tbname."  as maintb LEFT JOIN ".$tbname2." as secondtb ON maintb.PlantaID=secondtb.PlantaID";
	$qq .= " FROM ".$tbname."  as maintb";
	if ($processoid>0) {
		$qq .= " LEFT JOIN ProcessosLIST as prcc ON maintb.EspecimenID=prcc.EspecimenID";
	}
	if ($etitype =='EspecimenesIDS') { 
		$qq .= " LEFT JOIN Pessoas as colpessoa ON maintb.ColetorID=colpessoa.PessoaID";
	}
	//$qq .= " LEFT JOIN Identidade as iddet ON IF(maintb.DetID>0,maintb.DetID=iddet.DetID,secondtb.DetID=iddet.DetID)";
	$qq .= " LEFT JOIN Identidade as iddet ON maintb.DetID=iddet.DetID";

	$qq .= " LEFT JOIN Tax_InfraEspecies as infsptb ON iddet.InfraEspecieID=infsptb.InfraEspecieID LEFT JOIN Tax_Especies as sptb ON iddet.EspecieID=sptb.EspecieID LEFT JOIN Tax_Generos as gentb ON iddet.GeneroID=gentb.GeneroID  LEFT JOIN Tax_Familias as famtb ON iddet.FamiliaID=famtb.FamiliaID LEFT JOIN Pessoas as detpessoa ON detpessoa.PessoaID=iddet.DetbyID ";


	if ($filtro>0) {
		$qqff = " WHERE maintb.FiltrosIDS LIKE '%filtroid_".$filtro.";%' OR maintb.FiltrosIDS LIKE '%filtroid_".$filtro."'";
	} else {
		if (!empty($especimenesids)) {
		$specarr = explode(";",$especimenesids);
		$n = 0;
		foreach ($specarr as $vv) {
			if ($n==0) {
				$qqff = " WHERE maintb.EspecimenID=".$vv;
			} else {
				$qqff .= " OR maintb.EspecimenID=".$vv;
			}
			$n++;
		}

		} elseif ($processoid>0) {
			if ($quais==2) {
				$inpa = " AND ".$herbariumsigla.">0 ";
			}
			if ($quais==3) {
				$inpa = " AND (".$herbariumsigla."=0 OR ".$herbariumsigla." IS NULL)";
			}	
			$qqff = " WHERE prcc.EXISTE=1 AND prcc.ProcessoID=".$processoid." ".$inpa;
		}
	}
	$qq = $qq.$qqff;


	//echo $qq."<br />";
	$criou = mysql_query($qq,$conn);
	//if ($criou1) {
		//$qu = "ALTER TABLE ".$temptable." ADD COLUMN DESCRICAO TEXT";
		//mysql_query($qq,$conn);
		//$qu .= "UPDATE TABLE ".$temptable." SELECT labeldescricao(maintb.EspecimenID+0,maintb.PlantaID+0,".$formid.",TRUE,FALSE)  INTO DESCRICAO";
		//$qu .= " FROM ".$tbname."  as maintb";
		//$qu = $qu.$qqff;
		//echo $qu."<br />";
	//}
	//if ($formid>0) {
			//$qq .= ", notastring(EspecimenID, $formid,TRUE,'Especimenes')  as descricao";
		//}
	if (!$criou) {
		$erro=1;
	}
}

if ($erro==0 && $criou) {
  echo "
<form name='myform' action='label-pdf.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  <input type='hidden' name='logofile' value='".$logofile."' />
  <input type='hidden' name='useprojectlog' value='".$useprojectlog."' />
  <input type='hidden' name='spec_label' value='".$spec_label."' />
  <input type='hidden' name='mini_label' value='".$mini_label."' />
  <input type='hidden' name='det_label' value='".$det_label."' />
  <script language=\"JavaScript\">setTimeout('document.myform.submit()',1);</script>
</form>";

}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
//,"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
//"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>