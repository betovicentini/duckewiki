<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
include_once("functions/class.Numerical.php") ;
require_once ("javascript/jpgraph/src/jpgraph.php");

//FAZ A CONEXAO COM O BANCO DE DADOS
$lang = $_SESSION['lang'];
$dbname = $_SESSION['dbname'];
$conn = ConectaDB($dbname);

//CHECA SE O USUARIO TEM PERMISSAO
$uuid = cleanQuery($_SESSION['userid'],$conn);
if((!isset($uuid) || 
	(trim($uuid)=='')) && count($listsarepublic)==0) {
		header("location: access-denied.php?ispopup=1");
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

if ($especimes==1 && empty($enviado)) {
	$tbname = 'Especimenes';
	$idfield = 'EspecimenID';
	$qwhere =   " JOIN FiltrosSpecs as fl ON spec.EspecimenID=fl.EspecimenID WHERE fl.FiltroID=".$filtro;
}
if ($plantas==1 && empty($enviado)) {
	$tbname = 'Plantas';
	$idfield = 'PlantaID';
	$qwhere =   " JOIN FiltrosSpecs as fl ON spec.PlantaID=fl.PlantaID WHERE fl.FiltroID=".$filtro;
}

if (!empty($filtro) && empty($enviado)) {

		$qq = "SELECT ".$idfield.",idd.DetID,Familia,Genero,Especie,InfraEspecie FROM ".$tbname." as spec JOIN Identidade as idd USING(DetID) LEFT JOIN Tax_InfraEspecies as taxisp ON taxisp.InfraEspecieID=idd.InfraEspecieID LEFT JOIN Tax_Especies as taxsp ON taxsp.EspecieID=idd.EspecieID JOIN Tax_Generos as taxgen ON taxgen.GeneroID=idd.GeneroID JOIN Tax_Familias as taxfam ON idd.FamiliaID=taxfam.FamiliaID ".$qwhere;
		$uid = $_SESSION['userlastname'];
		$fname = "Temp_GraphVars_".$uid;
		$qu = "DROP TABLE $fname";
		mysql_query($qu,$conn);
		$qu = "CREATE TABLE $fname ".$qq;
		mysql_query($qu,$conn);
		$qu = " ALTER TABLE $fname CHANGE ".$idfield." ".$idfield." INT( 10 ) NOT NULL ";
		mysql_query($qu,$conn);
		$qu = "ALTER TABLE $fname DROP PRIMARY KEY";
		mysql_query($qu,$conn);
		$qu = "ALTER TABLE $fname ADD TempID INT(10) unsigned NOT NULL auto_increment PRIMARY KEY";
		mysql_query($qu,$conn);
		$qu = "ALTER TABLE $fname ADD NOME VARCHAR(200)";
		mysql_query($qu,$conn);
		$qu = "SELECT * FROM $fname";
		$res = mysql_query($qu,$conn);
		$nno = '';
		while ($row = mysql_fetch_assoc($res)) {
			$tid = $row['TempID'];
			$newsp = array($row[$idfield]);
			$detid = trim($row['DetID']);
			$nome = getdetnoautor($detid,$conn);
			$newarr = array('NOME' => $nome);
			$qu = "UPDATE $fname SET NOME='".$nome."'";
			$qu = $qu." WHERE TempID='".$tid."'";
			mysql_query($qu,$conn);
		}
		$qu = "SELECT DISTINCT NOME FROM $fname ORDER BY Familia,Genero,Especie,InfraEspecie";
		$res = mysql_query($qu,$conn);
		$resultado = array();
		$traitids = array();
		$vouchersids = array();
		while ($row = mysql_fetch_assoc($res)) {
			$nome = $row['NOME'];
			$qq = "SELECT ".$idfield." FROM $fname WHERE NOME='".$nome."'";
			$rse = mysql_query($qq,$conn);
			$specid = array();
			while ($spe = mysql_fetch_assoc($rse)) {
					$specid = array_merge((array)$specid,(array)array($spe[$idfield]));
			}
			$typeid = $idfield;
			$vararr = summarize_variables_array($specid,$formid,$typeid,$conn);
			$resultado = array_merge((array)$resultado,(array)array($nome => $vararr[0]));
			$vouchersids = array_merge((array)$vouchersids,(array)array($nome => $vararr[2]));
			$traitids = array_merge((array)$traitids,(array)$vararr[1]);
		}
		$enviado=0;
		$traitids = array_unique($traitids);
		$ress = serialize($resultado);
		$vouids =  serialize($vouchersids);
		$gvtids =  implode(";",$traitids);
} //if !empty($especimensids)

if (isset($enviado)) {
		//agora plota os valores
			$resultado = unserialize($ress);
			$vouchersids = unserialize($vouids);
			$traitids =  explode(";",$gvtids);
			$n= $enviado;
			$cid = trim($traitids[$n])+0;
			if ($cid==0 || empty($cid)) {
				header("location: graph-variables-form.php?ispopup=1&formid=$formid&filtro=$filtro");
			}
			$tid = "tid_".$cid;
			$qq = "SELECT TraitName,PathName,TraitUnit,TraitTipo FROM Traits WHERE TraitID='".$cid."'";
			$rrw = mysql_query($qq,$conn);
			$rwr = mysql_fetch_assoc($rrw);
			$pathname = $rwr['PathName'];
			$traitname = $rwr['TraitName'];
			$traittipo = $rwr['TraitTipo'];
			$traitunit = $rwr['TraitUnit'];

			$basename = explode("-",$pathname);
			$nbase = count($basename)-2;
			$pbase = trim($basename[$nbase]);
			$nspecies = count($resultado);
			$species = array_keys($resultado);
			
$title = 'Plotar variáveis';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
			if ($traittipo=='Variavel|Categoria') {
					$title = $enviado.". ".$traitname." (".$pbase.")";
					$ylab = $traitname." (".$pbase.")";
					$quantidades = array();
					$valores = array();
					$specarr = array();
					foreach ($resultado as $kk => $vv) {
							$arvals = $resultado[$kk][$tid];
							$vvids = $vouchersids[$kk][$tid];
							$specarr = array_merge((array)$specarr,(array)array($kk => $vvids));
							
							$aa = @array_keys($arvals);
							$valores = array_merge((array)$valores,(array)$aa);
							
							$qt = @array_values($arvals);
							$quantidades = array_merge((array)$quantidades,(array)$qt);
					}
					$xlab = array_unique($valores);
					$vals = array();
					foreach ($xlab as $stval) {
						$ncharst = strlen($stval);
						$vals[] = $ncharst+0;
					}
					$maxchar = max($vals);
					$dataarray = array();
					$basiccolors = array("#000000","#FF0000","#00FF00","#0000FF","#FFFF00","#00FFFF","#FF00FF","#C0C0C0","#FFFFFF");
					$basiccolors = array("blue","yellow","red","green","orange","purple","gray","wheat","white","pink");
					$colors = array_merge((array)$basiccolors,(array)array("#0000CC","#009900","#FF9900","#CC00FF","#990000", "#66CCCC", "#9900FF","#666600","#00FF99"));
					$colors = array_merge((array)$colors,(array)$colors);
					$data = array();
					$y=0;
					foreach ($xlab as $key => $val) {
						$y++;
						$xx=0;
						foreach ($species as $non) {
							$color = $colors[$xx];
							$xx++;
							$maxnew = 6;
							$maxobs = max($quantidades);
							$zval = $resultado[$non][$tid][$val]+0;
							$zlabel = $zval;
							$zval = ($zval*$maxnew)/$maxobs;
							if (!empty($zval) && $zval>0) {
								$aa = array(array($xx,$y,$zval,$color,$zlabel));
								$data = array_merge((array)$data,(array)$aa);
							}
						}
					}
					$ndad = count($data);
					//make the plot
					if ($ndad>0) {
						$xlab = array_values($xlab);
						$dd = array($data,$species,$xlab,$title,$maxchar,$ylab,$specarr);
                        $_SESSION['gvcateg'] = $dd;
                        echo "
                       <div align='center'>
                        <p align='center' style='font-size: 1.5em; color: darkred;'>$ylab</p>
                        <div align='left'><img src='graph-variables-categorical.php' /></div>
                       </div>";

					}
			} elseif ($traittipo=='Variavel|Quantitativo') {
					$title = $enviado.". ".ucfirst(strtolower($pbase))." ".mb_strtolower($traitname)." (".$traitunit.")";
					$ylab = ucfirst(strtolower($traitname))." (".$traitunit.")";
					$minmax = array();
					$meanarr = array();
					$data = array();
					$specarr = array();
					foreach ($resultado as $kk => $vv) {
							$arvals = $resultado[$kk][$tid];
							$vvids = $vouchersids[$kk][$tid];
							$specarr = array_merge((array)$specarr,(array)array($kk => $vvids));
							if (empty($arvals['SDValue'])) { 
								$sdva = 0;
								$maxval = $arvals['MeanValue']+0;
								$minval = $arvals['MeanValue']+0;
							} else {
								$maxval = $arvals['MaxValue']+0;
								$minval = $arvals['MinValue']+0;
								$sdva = $arvals['SDValue']+0;
							}
							$openvalue = $arvals['MeanValue']-$sdva;
							$closevalue = $arvals['MeanValue']+$sdva;
							$nmeasurements = $arvals['Nmeasurements']+0;
							if ($openvalue<$minval) {$openvalue=$minval;}
							if ($closevalue>$maxval) {$closevalue=$maxval;}
							$valunit = $arvals['Unit'];
							$mean = $arvals['MeanValue']+0;
							$aa = array($kk => array(array($openvalue,$closevalue,$minval,$maxval,$mean),$nmeasurements));
							$data = array_merge((array)$data,(array)$aa);
							$qt = array($minval,$maxval);
							$minmax = array_merge((array)$minmax,(array)$qt);
							$meanarr[$kk] = $mean;
					}
					asort($meanarr);
					$dados = array();
					$nmeasarr = array();
					foreach ($meanarr as $k => $v) {
						$dd = $data[$k][0];
						$dados = array_merge((array)$dados,(array)$dd);
						$dd = $data[$k][1]+0;
						$nmeasarr = array_merge((array)$nmeasarr,(array)$dd);
					}
					$species = array_keys($meanarr);
					$ndad = count($dados);
					$ylim = array(min($minmax),max($minmax));

					if ($ndad>=4) {
						$dd = array($dados,$species,$ylim,$title,$nmeasarr,$ylab,$specarr);
						//echopre($dd);
                        $_SESSION['gvqt'] = $dd;
                        //$gvqt = serialize($dd);
                       echo "
                       <div align='center'>
                        <p align='center' style='font-size: 1.5em; color: darkred;'>$ylab</p>
                        <div align='left'><img src='graph-variables-quantity.php' /></div>
                       </div>";
					}
			}
			echo "
<table align='center'>
<tr>
<form action='graph-variables-exec.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  <input type='hidden' name='formid' value='".$formid."' />
  <input type='hidden' name='filtro' value='".$filtro."' />
  <input type='hidden' name='ress' value='".$ress."' />
  <input type='hidden' name='vouids' value='".$vouids."' />
  <input type='hidden' name='gvtids' value='".$gvtids."' />";

				$nn = $enviado-1;
				$nz = count($traitids)-1;
				if ($nn<0) {$nn=$nz;}
			echo "
  <input type='hidden' name='enviado' value='".$nn."' />
  <td><input type='submit' class='bblue' value=\"<<\" /></td>
</form>
  <td>&nbsp;&nbsp;&nbsp;</td>
<form action='graph-variables-exec.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  <input type='hidden' name='formid' value='".$formid."' />
  <input type='hidden' name='filtro' value='".$filtro."' />
  <input type='hidden' name='ress' value='".$ress."' />
  <input type='hidden' name='vouids' value='".$vouids."' />
  <input type='hidden' name='gvtids' value='".$gvtids."' />";
				$nn = $enviado+1;
				$nz = count($traitids)-1;
				if ($nn>$nz) {$nn=0;}
			echo "
  <input type='hidden' name='enviado' value='".$nn."' />
  <td><input type='submit' class='bsubmit' value=\">>\" /></td>
</form>
</tr>
</table>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
} 
?>