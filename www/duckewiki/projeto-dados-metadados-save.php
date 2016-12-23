<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";

//FAZ A CONEXAO COM O BANCO DE DADOS
$lang = $_SESSION['lang'];
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


//ARQUIVO PARA PROGRESSOS
$pgfilename = 'projdadosmeta'.substr(session_id(),0,10)."_pg.txt";

function human_filesize($bytes, $decimals = 2) {
  $sz = 'BKMGTP';
  $factor = floor((strlen($bytes) - 1) / 3);
  return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}

//CABECALHO
$menu = FALSE;
$which_css = array("<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array(
"<script>
 	function limpapgfile(id) {
    	var apxhttp = new XMLHttpRequest();
		apxhttp.onreadystatechange = function() {
			if (apxhttp.readyState == 4 && apxhttp.status == 200) {
				var progress = apxhttp.responseText;
				progress = parseInt(progress, 10);
				var theid = 'probarperc'+id;
				document.getElementById(theid).innerHTML = progress + '%';
			}
		};
		var url = 'progress-delete.php?filename=temp_'+id+'_".$pgfilename."';
		apxhttp.open(\"GET\", url, true);
		apxhttp.send();        
	}
    function CheckProgress(id) {
    	var pgxhttp = new XMLHttpRequest();
		pgxhttp.onreadystatechange = function() {
			if (pgxhttp.readyState == 4 && pgxhttp.status == 200) {
				var progress = pgxhttp.responseText;
				progress = parseInt(progress, 10);
				var theid = 'probarperc'+id;
				if (progress<100) {
					document.getElementById(theid).innerHTML = progress + '%';
		         	setTimeout(CheckProgress(id), 3000);
		      	} else {
					document.getElementById(theid).innerHTML = 'Concluido';
	     	 	}
			}
		};
		var url = 'import-data-progress.php?filename=temp_'+id+'_".$pgfilename."';
		pgxhttp.open(\"GET\", url, true);
		pgxhttp.send();        
	}
	function fazgeradados(id) {
		limpapgfile(id);
		CheckProgress(id);
		geradados(id);
	}       
  function geradados(id) {
  		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
			if (xhttp.readyState == 4 && xhttp.status == 200) {
				document.getElementById('resultado'+id).innerHTML = xhttp.responseText;
			}
		};
		var url = 'projeto-dados-metadados-script.php?id='+id+'&pgfilename=temp_'+id+'_".$pgfilename."&projetoid=".$projetoid."';
		//alert(url);
		xhttp.open(\"GET\", url, true);
		xhttp.send(); 
	}
</script>"
);

$qprj = "SELECT * FROM Projetos WHERE ProjetoID=".$projetoid;
$rq = mysql_query($qprj,$conn);
$rqw = mysql_fetch_assoc($rq);
$nomeprj = $rqw['ProjetoNome'];
//$morfoformid = $rqw['MorfoFormID'];

$morfoformid = explode(";",$rqw['MorformsIDs']);
$habitatformid = $rqw['HabitatFormID'];

$sql = "SELECT * FROM ProjetosEspecs WHERE ProjetoID=".$projetoid;
$rq = mysql_query($sql,$conn);
$ntot = mysql_numrows($rq);
if ($ntot>0) {
	//CHECA SE TEM ESPECIMENES
	$qsamples = "SELECT * FROM ProjetosEspecs WHERE ProjetoID=".$projetoid." AND EspecimenID>0";
	$rq = mysql_query($qsamples,$conn);
	$nsamples = mysql_numrows($rq);
	$files = array();
	$nrecords = array();
	if ($nsamples>0) {
		 $files['Especimenes'] = array("Especimenes" => "dadosAmostras_".$projetoid.".csv",
 'Especimenes-Metadados' =>  "dadosAmostras_".$projetoid."_metadados.csv");
 		$nrecords["Especimenes"] = "Contém ".$nsamples." especímenes";
 		$osids["Especimenes"] = 'especimenes';
			//SE TE FORMULARIOS DE VARIAVEIS
		 if (count($morfoformid)>0) {
			 	//$files["Formulários Usuário"] = array();
			 	foreach($morfoformid  as $vv) {
			 		if ($vv>0) {
				 		$qqr = "SELECT * FROM Formularios WHERE FormID=".$vv;
						$runr = mysql_query($qqr,$conn);
						$runw= mysql_fetch_assoc($runr);
						$kn1 = $runw['FormName'];
						$kn2 = $runw['FormName']."_metadados";
						$files["Especimenes"][$kn1] = "dados_form-".$vv."_projeto-".$projetoid.".csv";
						$files["Especimenes"][$kn2] = "dados_form-".$vv."_projeto-".$projetoid."_metadados.csv";
						}
	 			}
		}
	 if ($habitatformid>0) {
		 $files['Habitat'] = array("Habitat" => "dadosAmbientais_".$projetoid.".csv",
'Habitat-Metadados' =>  "dadosAmbientais_".$projetoid."_metadados.csv");
		$nrecords["Habitat"] = "Procurar dados ambientais associados";
		 $osids["Habitat"] = 'habitat';
	 }
	 $qmol = "SELECT Marcador, count(*) as Namostras FROM MolecularData JOIN ProjetosEspecs as prj USING(EspecimenID) WHERE prj.ProjetoID=".$projetoid." GROUP BY Marcador";
	 $rmol = mysql_query($qmol, $conn);
	 $nrmol = mysql_numrows($rmol);
	 $moleculares = array();
	 if ($nrmol>0) {
 		 $files["Dados Moleculares"] = array();
 		 $nrecords["Dados Moleculares"] = "Contém ".$nrmol." dados moleculares";
 		 $osids["Dados Moleculares"] = 'moleculares';
		 while($rwmol = mysql_fetch_assoc($rmol)) {
		 	if ($rwmol['Namostras']>0) {
			 	$mark= $rwmol['Marcador'];
	 			$mark = str_replace(" ","-",$mark);
	 			$files["Dados Moleculares"][$rwmol['Marcador']] = "dadosMoleculares_".$mark."_".$projetoid.".fasta";
	 			$files["Dados Moleculares"][$rwmol['Marcador']."Metadados"] = "dadosMoleculares_".$mark."_".$projetoid."_metadados.csv";
	 		}
		}
 	}
}

//CHECA SE TEM PLANTAS
$qpl = "SELECT * FROM ProjetosEspecs WHERE ProjetoID=".$projetoid." AND PlantaID>0";
$rqpl = mysql_query($qpl,$conn);
$npl = mysql_numrows($rqpl);
	if ($npl>0) {
		$files['Plantas marcadas'] = array("Plantas" => "dadosPlantas_".$projetoid.".csv", 'Plantas Metadados' =>  "dadosPlantas_".$projetoid."_metadados.csv");
		$nrecords['Plantas marcadas'] =  "Contém ".$npl."  registros de plantas marcadas";
		$osids['Plantas marcadas'] = 'plantas';
		
					//SE TE FORMULARIOS DE VARIAVEIS
		if (count($morfoformid)>0) {
			 	//$files["Formulários Usuário"] = array();
			 	foreach($morfoformid  as $vv) {
			 		if ($vv>0) {
				 		$qqr = "SELECT * FROM Formularios WHERE FormID=".$vv;
						$runr = mysql_query($qqr,$conn);
						$runw= mysql_fetch_assoc($runr);
						$kn1 = $runw['FormName'];
						$kn2 = $runw['FormName']."_metadados";
						$files["Plantas marcadas"][$kn1] = "dados_form-pl".$vv."_projeto-".$projetoid.".csv";
						$files["Plantas marcadas"][$kn2] = "dados_form-pl".$vv."_projeto-".$projetoid."_metadados.csv";
						}
	 			}
		}
		$files["Arquivos Para Recenso"] = array("ParaRecenso" => "dadosParaRecenso_".$projetoid.".csv");
		$osids["Arquivos Para Recenso"] = 'pararecenso';		
		$nrecords["Arquivos Para Recenso"] = "Contém ".$npl." registros de plantas marcadas";
		$qpl = "SELECT * FROM Monitoramento as moni JOIN  ProjetosEspecs as oprj ON moni.PlantaID=oprj.PlantaID WHERE oprj.ProjetoID=".$projetoid;
		//echo $qpl;
		$rsql = mysql_query($qpl,$conn);
		$nrsql = mysql_num_rows($rsql);
		if ($nrsql>0) {
			$nrecords["Dados de Censos"] = "Contém ".$nrsql."  registros de plantas marcadas com dados de censos";
			$osids["Dados de Censos"] = 'censos';
			$files["Dados de Censos"] = array("Censos" => "dadosPlantasCensos_".$projetoid.".csv", "Censos Metadados" => "dadosPlantasCensos_".$projetoid."_metadados.csv");
		}
	}
	//ARQUIVOS NIR
$sql1 = "(SELECT SpectrumID FROM NirSpectra AS spec JOIN Plantas as pl ON pl.PlantaID=spec.PlantaID JOIN ProjetosEspecs as prj ON prj.PlantaID=pl.PlantaID WHERE prj.ProjetoID=".$projetoid.")";
$sql2 = "(SELECT SpectrumID FROM NirSpectra AS spec JOIN Especimenes as pl ON pl.EspecimenID=spec.EspecimenID JOIN ProjetosEspecs as prj ON prj.EspecimenID=pl.EspecimenID WHERE prj.ProjetoID=".$projetoid.")";
$qz = "SELECT DISTINCT newtb.SpectrumID FROM (".$sql1."  UNION ".$sql2.") as newtb";
$res = mysql_query($qz,$conn);
$nrecsnir = mysql_numrows($res);
if ($nrecsnir>0) {
		$files["NIR"] = array("NIR" => "dadosNIR_".$projetoid.".csv");
		$nrecords['NIR'] =  "Contém ".$nrecsnir." registros de espectros NIR";
		$osids["NIR"] = 'nir';
}


foreach($osids as $oid) {
		$fh = fopen("temp/temp_".$oid."_".$pgfilename, 'w');
		fwrite($fh,"0");
		fclose($fh);
		session_write_close();
}
//CHECA SE OS ARQUIVOS E
//$existem=0;
//foreach($files as $ff) {
//	$fname = $ff[1];
//	if (file_exists("temp/".$fname)) {
//		$existem++;
//	}
//}
$title = 'Dados e metadados de projeto';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
//echopre($files);
echo "
<br />
<table class='myformtable' cellpadding='5' align='left' >
<thead>
<tr><td colspan='2'>Baixar dados de projeto</td></tr>
</thead>
<tbody>
<tr><td class='tdsmallbold'  colspan='2'>Dados e arquivos pré-formatados</td></tr>
";
$k0="";
foreach($files as $kk => $ffarr) {
	if($kk!=$k0) { 
		$k0=$kk; 
		$oid = $osids[$kk];
		echo "<tr><td colpsan='2'><br><b>".$k0."</b><br /><div id='probarperc".$oid."'  style='color: red;'></div>&nbsp;&nbsp;<input type='button' value='Atualizar/Gerar arquivos!' onclick=\"javascript: fazgeradados('".$oid."');\"/></td></tr>";
	}
	$tem = 0;
	$arqlist = "<tr><td colspan=2><div id='resultado".$oid."'><table>";
	foreach($ffarr as $fk => $fname) {
		if (file_exists("temp/".$fname)) {
		$fzide = filesize("temp/".$fname);
		$fsize = human_filesize($fzide,2);
		//echo $fname."OK <br />";
			$adt = @date ("F d Y H:i:s.", filemtime("temp/".$fname));
			$arqlist .=  "<tr style='font-size: 0.8em;' ><td>".$fk."</td><td><a href=\"download.php?file=temp/".$fname."\">".$fname."</a> [".$adt."&nbsp;&nbsp;&nbsp;".$fsize."]</td></tr>";
			$tem++;
		} else {
			//echo $fname." NAO EXISTE<br />";
			//$arqlist .=  "<tr style='font-size: 0.8em;' ><td>".$fname."</td><td>Não encontrado</td></tr>";
		
		}
	}
	if ($tem==0) {
		$txt = $nrecords[$kk];
		$arqlist .=  "<tr style='font-size: 0.8em;' ><td colspan=2 >".$txt."</td></tr>";
	}
	$arqlist .=  "</table></div></td></tr>";
	echo $arqlist;
}
echo "
<tr><td colspan='2'><hr></td></tr>
<tr><td colspan='2' class='tdformnotes'>*Os arquivos estão separados por TABULAÇÃO, tem quebras de linha em formato Unix, e o encoding de caracteres é UTF-8. O planilha do openoffice é melhor que o Excel para abrir o arquivo porque reconhece automaticamente o encoding dos caracteres (UTF-8). No Excel pode ter erros de grafia.</td></tr>
</tbody>
</table>";


}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>