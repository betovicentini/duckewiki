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

//CABECALHO
$menu = FALSE;
$which_css = array("<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();

$qprj = "SELECT * FROM Projetos WHERE ProjetoID=".$projetoid;
$rq = mysql_query($qprj,$conn);
$rqw = mysql_fetch_assoc($rq);
$nomeprj = $rqw['ProjetoNome'];
//$morfoformid = $rqw['MorfoFormID'];
$morfoformid = explode(";",$rqw['MorformsIDs']);
$habitatformid = $rqw['HabitatFormID'];

$qsamples = "SELECT * FROM ProjetosEspecs WHERE ProjetoID=".$projetoid." AND EspecimenID>0";
$rq = mysql_query($qsamples,$conn);
$nsamples = mysql_numrows($rq);
$files = array();
if ($nsamples>0) {
 $files['Especimenes'] = "dadosAmostras_".$projetoid.".csv";
 $files['Especimenes-Metadados'] =  "dadosAmostras_".$projetoid."_metadados.csv";
 if (count($morfoformid)>0) {
 	foreach($morfoformid  as $vv) {
		 	$qqr = "SELECT * FROM Formularios WHERE FormID=".$vv;
			$runr = mysql_query($qqr,$conn);
			$runw= mysql_fetch_assoc($runr);
			//$fn = str_replace(" ","-",$runw['FormName']);
			$kn1 = $runw['FormName'];
			$kn2 = $runw['FormName']."_metadados";
		 	$files[$kn1] = "dados_form-".$vv."_projeto-".$projetoid.".csv";
			$files[$kn2] = "dados_form-".$vv."_projeto-".$projetoid."_metadados.csv";
 	}
 }
 if ($habitatformid>0) {
 $files['Habitat'] = "dadosAmbientais_".$projetoid.".csv";
 $files['Habitat-Metadados'] =  "dadosAmbientais_".$projetoid."_metadados.csv";
 }
 $qmol = "SELECT Marcador, count(*) as Namostras FROM MolecularData JOIN ProjetosEspecs as prj USING(EspecimenID) WHERE prj.ProjetoID=".$projetoid." GROUP BY Marcador";
 $rmol = mysql_query($qmol, $conn);
 $nrmol = mysql_numrows($rmol);
 $moleculares = array();
 if ($nrmol>0) {
	 while($rwmol = mysql_fetch_assoc($rmol)) {
	 	if ($rwmol['Namostras']>0) {
		 	$mark= $rwmol['Marcador'];
	 		$mark = str_replace(" ","-",$mark);
	         $files[$rwmol['Marcador']] = "dadosMoleculares_".$mark."_".$projetoid.".fasta";
	         $files[$rwmol['Marcador']."Metadados"] = "dadosMoleculares_".$mark."_".$projetoid."_metadados.csv";
	    }
 	}
 }
}
//CHECA POR ARQUIVOS
$existem=0;
foreach($files as $ff) {
	if (file_exists("temp/".$ff)) {
		$existem++;
	}
}
if ($existem>0) {
	$title = 'Dados e metadados de projeto';
	$body = '';
	FazHeader($title,$body,$which_css,$which_java,$menu);
	echo "
<br />
<table class='myformtable' cellpadding='5' align='center' width='90%'>
<thead>
<tr><td colspan='2'>Baixar dados de projeto</td></tr>
</thead>
<tbody>
<tr><td class='tdsmallbold'  colspan='2'>Existem alguns arquivos de exportação prontos para este projeto!</td></tr>
<tr><td class='tdsmallbold' colspan='2'>Os arquivos foram gerados em ".date ("F d Y H:i:s.", filemtime("temp/".$files['Especimenes']))."</td></tr>";
foreach($files as $kk => $ff) {
	if (file_exists("temp/".$ff)) {
	  echo "
<tr><td>".$kk."</td><td><a href=\"download.php?file=temp/".$ff."\">".$ff."</a></td></tr>";
	} else {
	  echo "
<tr><td>".$kk."</td><td>Existem dados mas o arquivo não foi ainda gerado!</td></tr>";
	}
}
echo "
<tr><td colspan='2'><hr></td></tr>
<tr><td colspan='2' class='tdformnotes'>*Os arquivos estão separados por TABULAÇÃO, tem quebras de linha em formato Unix, e o encoding de caracteres é UTF-8. O planilha do openoffice é melhor que o Excel para abrir o arquivo porque reconhece automaticamente o encoding dos caracteres (UTF-8). No Excel pode ter erros de grafia.</td></tr>
<tr>
  <td colspan='2' align='center'>
     <form action='projeto-dados-metadados.php' name='myform' method='post'>
      <input type='hidden' name='projetoid' value='".$projetoid."'>
      <input type='submit' value='Atualizar ou gerar arquivos!' class='bsubmit' />
     </form>
  </td>
</tr>
</tbody>
</table>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
} 
else {
		header("location: projeto-dados-metadados.php?projetoid=".$projetoid);
}
?>