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
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'Identifica várias árvores';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if (empty($tagnumbers) || empty($filtro) || empty($detset)) {
	echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('erro1')."</td></tr>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('namefiltros')."</td></tr>
  <tr>
    <td>
      <form action='identify-batch-trees.php' method='post'>
        <input type='hidden' value='".$filtro."' name='filtro' />
        <input type='hidden' value='".$detset."' name='detset' />
        <input type='hidden' value='".$tagnumbers."' name='tagnumbers' />
        <input type='submit' value='".GetLangVar('namevoltar')."' />
      </form>
    </td>
  </tr>
</table>
<br />";
	unset($plantasids);
} 
elseif (!empty($tagnumbers) && !empty($detset) && $filtro>0) {
	$qq = "SELECT * FROM Filtros WHERE FiltroID='".$filtro."'";
	$res = mysql_query($qq,$conn);
	$rr = mysql_fetch_assoc($res);
	$plantasids= explode(";",$rr['PlantasIDS']);
	$tagarr = explode(";",$tagnumbers);
	$tagplids = array();
	$nofound = array();
	foreach ($tagarr as $tt) {
			$tag = $tt+0;
			if ($tag>0) {
				$qq  = "SELECT PlantaID From Plantas WHERE PlantaTag='".$tag."'";
				$qr = mysql_query($qq,$conn);
				$nqr = mysql_numrows($qr);
				if ($nqr>0) {
					$i=1;
					while ($rq = mysql_fetch_assoc($qr)) {
							$idpl = $rq['PlantaID'];
							$tk = "tag_".$tag."_".$i;
							$tagplids[$tk] = $idpl;
							$i++;
					}
				} else {
					$nofound[] = $tag;
				}
			}
	}
	
	$notinfilter = array();
	foreach ($tagplids as $kk => $tg) {
		if (!in_array($tg,$plantasids)) {
			unset($tagplids[$kk]);
			$tkk = explode("_",$kk);
			$notinfilter[] = $tkk[1];
		}
	}
}
$ntags = count($tagplids);
if ($ntags>0 && !empty($detset))  { //se tiver enviado algo, ent„o faz o cadastro
	$detarray = unserialize($detset);
	$newdetid = InsertIntoTable($detarray,'DetID','Identidade',$conn);
	if ($newdetid) { //se cadastrou a identificacao corretamente, entao atualiza os registros das coletas com essa nova determinacao
		$nok =0;
		$naomudou=0;
		$falhou=0;
		foreach ($tagplids as $plid) {  //for earch specimen
			//pega o valor antigo para compara e ver se precisa mudar
			$qq = "SELECT Identidade.* FROM Plantas JOIN Identidade USING(DetID) WHERE PlantaID='".$plid."'";
			$res = mysql_query($qq,$conn);
			$row = mysql_fetch_assoc($res);
			$olddetid = $row['DetID'];
			$detchanged = CompareOldWithNewValues('Identidade','DetID',$olddetid,$detarray,$conn);
			if ($detchanged==0 || empty($detchanged)) { //se for identifico nesse campos nao faz nada
				$naomudou++;
			} else {
				CreateorUpdateTableofChanges($plid,'PlantaID','Plantas',$conn);
				$arrayofvalues = array('DetID' => $newdetid);
				$newupdate = UpdateTable($plid,$arrayofvalues,'PlantaID','Plantas',$conn);
				if (!$newupdate) {
					$falhou++;
				} else {
					$nok++;
				}
			}
		} //end for each planta
		
	} else {  //end se identidade for cadastrada corretamente
		echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='success'>
  <tr><td class='tdsmallbold' align='center'>Houve um erro do programa, a atualização não foi feita</td></tr>
</table>
<br />";
	}

} //fecha se entao faz o cadastro

if ($nok>0) {
		echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='success'>
<tr><td class='tdsmallbold' align='center'>$nok ".GetLangVar('sucessoregistrosatualizados')."</td></tr>
</table><br />";
}

if ($falhou>0) {
		echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>$falhou ".GetLangVar('erroregistrofalhou')."</td></tr>
</table>
<br />";
}
if ($naomudou>0) {
		echo "
<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>$naomudou ".GetLangVar('erroregistronaomudou')."</td></tr>
</table>
<br />";
}

$nf = count($nofound);
if ($nf>0) {
		$nnf = implode(";",$nofound);
		echo "
<br />
<table cellpadding=\"3\" width='50%' align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>$nf placas não foram encontradas no banco de dados</td></tr>
  <tr><td class='tdsmallbold' align='center'><textarea rows='5' cols='60' readonly>$nnf</textarea></td></tr>
</table>
<br />";
}

$nf = count($notinfilter);
if ($nf>0) {
		$nnf = implode(";",$notinfilter);
		echo "
<br />
<table cellpadding=\"3\" width='50%' align='center' class='erro'>
<tr><td class='tdsmallbold' align='center'>$nf placas não fazem parte do filtro selecionado.Tem certeza de que escolheu o filtro corretamente?</td></tr>
<tr><td class='tdsmallbold' align='center'><textarea rows='5' cols='60' readonly>$nnf</textarea></td></tr>
<tr>
  <td>
    <form action='identify-batch-trees.php' method='post'>
      <input type='hidden' value='".$detset."' name='detset' />
      <input type='hidden' value='".$nnf."' name='tagnumbers' />
      <input type='submit' value='".GetLangVar('namevoltar')."' />
    </form>
  </td>
</tr>
</table>
<br />";
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>