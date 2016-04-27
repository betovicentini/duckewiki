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

//echopre($ppost);
//CABECALHO
$menu = FALSE;

$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />"
);
$which_java = array(
"<script type='text/javascript'>
function verdescricoes(monografiaid) {
 var el = document.getElementById('especimenestxt').innerHTML;  
 var ii = el.substring(0,1);
 var md = document.getElementById('descricaomodelo').innerHTML;  
 var idd = md.substring(0,1);
 if (ii>0 && idd>0) {
    small_window('monografila-descricao-view.php?monografiaid='+monografiaid,1000,700,'Visualiza descrições');
 } else {
    alert('Amostras não foram selecionadas ou um modelo não foi definido!');
}

}
function verlistaespecs(monografiaid) {
 var el = document.getElementById('especimenestxt').innerHTML;  
 var ii = el.substring(0,1);
 var md = document.getElementById('descricaomodelolista').innerHTML;  
 var idd = md.substring(0,1);
 if (ii>0 && idd>0) {
    small_window('monografila-descricao-listaespecimenes-view.php?monografiaid='+monografiaid,1000,700,'Visualiza lista');
 } else {
    alert('Amostras não foram selecionadas ou um modelo de lista não foi definido!');
}

}
</script>"
);
$title = 'Fazer monografia';
$body = '';

//gerando
if ($final==2 && !empty($monografiaid)) {
	header("location: monografia-print.php?ispopup=1&monografiaid=".$monografiaid."&english=".$english);
} elseif ($final==2) {
	header("location: monografia-form.php?ispopup=1");
}

//Create table if not exists
$qq = "CREATE TABLE IF NOT EXISTS Monografias (
MonografiaID INT(10) unsigned NOT NULL auto_increment,
Titulo VARCHAR(100),
ModeloDescricoes TEXT,
ModeloSimbolos VARCHAR(500),
EspecimenesIDS LONGTEXT,
PlantasIDS LONGTEXT,
TraitIdsArray LONGTEXT,
TraitIdToBreak INT(10),
TraitIdToBreakArray LONGTEXT,
ComentariosArray LONGTEXT,
TraitIdsGenera LONGTEXT,
AddParts VARCHAR(1000),
AddedBy INT(10),
AddedDate DATE,
PRIMARY KEY (MonografiaID)) CHARACTER SET utf8";
mysql_query($qq,$conn);

$qq ='ALTER TABLE `Monografias`  ADD `ModeloDescricoes` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `Titulo`';
@mysql_query($qq);
$qq ='ALTER TABLE `Monografias`  ADD `ModeloSimbolos` VARCHAR(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `ModeloDescricoes`';
@mysql_query($qq);
$qq ='ALTER TABLE `Monografias`  ADD `ModeloListaEspecimenes` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `ModeloSimbolos`';
@mysql_query($qq);
$qq ='ALTER TABLE `Monografias`  ADD `ModeloSimbolosEspecimenes` VARCHAR(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `ModeloListaEspecimenes`';
@mysql_query($qq);
$qq = "ALTER TABLE `Monografias`  ADD `Autores` CHAR(255) NULL DEFAULT NULL COMMENT 'PessoaID' AFTER `Titulo`,  ADD INDEX (`Autores`)";
@mysql_query($qq);

//editando
if (($monografiaid+0)>0 && !isset($saving) && $saving!=1) {
	$qq = "SELECT mn.*,addcolldescr(Autores) as addcoltxt FROM Monografias mn WHERE mn.MonografiaID='".$monografiaid."'";
	$res = mysql_query($qq);
	$rr = mysql_fetch_assoc($res);
	//echopre($rr);
	$titulo= $rr['Titulo'];
	$addcolvalue= $rr['Autores'];
	$addcoltxt= $rr['addcoltxt'];
	///NAO PRECISA MAIS DISSO DESABILITAR
	//$especimenesids= $rr['EspecimenesIDS'];
	//$plantasids= $rr['PlantasIDS'];
	//$traitidsarray= $rr['TraitIdsArray'];
	//$tt = explode(";",$traitidsarray);
	//echopre($tt);
	//$traitidsgenera= $rr['TraitIdsGenera'];
	//$traitidtobreak= $rr['TraitIdToBreak'];
	//$traitsidstobreak= $rr['TraitIdToBreakArray'];
	//$addcomentsid= $rr['ComentariosArray'];
	$traittxt = $rr['ModeloDescricoes'];
	$modeloarr = json_decode($traittxt);
	//echopre($modeloarr);
	$mm = $modeloarr->items;
	$traittxt = count($mm)."  variáveis no modelo";

	$traittxt2 = $rr['ModeloListaEspecimenes'];
	$modeloarr2 = json_decode($traittxt2);
	//echopre($modeloarr);
	$mm2 = $modeloarr2->items;
	$traittxt2 = count($mm2)."  variáveis na lista de especímenes";

	//$_SESSION['monospecids'] = $especimenesids;
	//$_SESSION['comentarios'] = $addcomentsid;
	//$addparts= unserialize($rr['AddParts']);
	//if (isset($addparts['quantvarformat'])) {
		//$quantvarformat = $addparts['quantvarformat'];
		//unset($addparts['quantvarformat']);
	//} else {
		//$quantvarformat = 1;
	//}
} 
else {
	//unset($_SESSION['monospecids']);
	//unset($_SESSION['comentarios']);
	//unset($addcomentsid);
}
FazHeader($title,$body,$which_css,$which_java,$menu);

if ($saving==1) {
		
//		if ($filtro>0) {
//			$qq = "SELECT * FROM Filtros WHERE FiltroID='".$filtro."'";
//			$res = mysql_query($qq);
//			$rr = mysql_fetch_assoc($res);
//			$newspids= explode(";",$rr['EspecimenesIDS']);
//			mysql_free_result($res);
//		}
//		if (count($newspids)>0) {
//			if (!empty($especimenesids)) {
//				$olspids = explode(";",$especimenesids);
//				$mergedspids = array_merge((array)$olspids,(array)$newspids);
//				$uniquesids = array_unique($mergedspids);
//			} else {
//				$uniquesids = $newspids;
//			}
//			$especimenesids = implode(";",$uniquesids);
//
//		} 
//		if ($formid>0 && ($atualizarform==1 || empty($traitidsarray))) {
//				if ($formid>0) {
//					$qu = "SELECT * FROM Formularios WHERE FormID='$formid'";
//					$res = mysql_query($qu,$conn);
//					$rzw = mysql_fetch_assoc($res);
//					$traitsidsarr = explode(";",$rzw['FormFieldsIDS']);
//					$traitidsarray =  $rzw['FormFieldsIDS'];
//
//			}
//		} 
//		if ($formidgenus>0 && ($atualizarformgenera==1 || empty($traitidsgenera))) {
//				if ($formidgenus>0) {
//					$qu = "SELECT * FROM Formularios WHERE FormID='$formidgenus'";
//					$res = mysql_query($qu,$conn);
//					$rzw = mysql_fetch_assoc($res);
//					$traitsidsarrgenera = explode(";",$rzw['FormFieldsIDS']);
//					$traitidsgenera =  $rzw['FormFieldsIDS'];
//
//			}
//		} 
//		if ($formidtobreak>0 && ($atualizarbreakform==1 || empty($traitidstobreak))) {
//				if ($formidtobreak>0) {
//					$qu = "SELECT * FROM Formularios WHERE FormID='$formidtobreak'";
//					$res = mysql_query($qu,$conn);
//					$rzw = mysql_fetch_assoc($res);
//					$traitstobreakarr = explode(";",$rzw['FormFieldsIDS']);
//					$traitsidstobreak =  $rzw['FormFieldsIDS'];
//				}
//		}
//
$addpkk = array('sinonimos', 'descricao', 'habitat', 'fenologia', 'materiaexaminado','comentarios');

$addparts = array($sinonimos, $descricao, $habitat, $fenologia, $materiaexaminado,$comentarios);
$addparts = array_combine($addpkk,$addparts);
$addp = serialize($addparts);
$arrayofvalues = array( 
'Titulo' => $titulo,
'Autores' => $addcolvalue,
//'EspecimenesIDS' => $especimenesids,
//'PlantasIDS' => $plantasids, 
//'TraitIdsArray' => $traitidsarray, 
//'TraitIdToBreak' => $traitidtobreak, 
//'TraitIdToBreakArray' => $traitsidstobreak, 
//'TraitIdsGenera' => $traitidsgenera, 
'ComentariosArray' => $addcomentsid, 
'AddParts' => $addp
);
	$updated=0;
	$erro =0;
	$novo=0;
	$upp =0;
	//echopre($addparts);
	//echo "monografiaid: ".$monografiaid;
	if ($monografiaid>0) {
			$upp = CompareOldWithNewValues('Monografias','MonografiaID',$monografiaid,$arrayofvalues,$conn);
			if (!empty($upp) && $upp>0) { //if new values differ from old, then update
				CreateorUpdateTableofChanges($monografiaid,'MonografiaID','Monografias',$conn);
				$updated = UpdateTable($monografiaid,$arrayofvalues,'MonografiaID','Monografias',$conn);
				if (!$updatespecid) {
					$erro++;
				} else {
					$updated++;
				}
			}
		} else {
			$new = InsertIntoTable($arrayofvalues,'MonografiaID','Monografias',$conn);
			if (!$new) {
				$erro++;
			} else {
				$novo++;
				$monografiaid = $new;
			}
		}

		if (($updated==0 && $upp==0 && $erro==0 && $novo==0)) {
			echo "
<br /><table cellpadding=\"1\" width='50%' align='center' class='erro'>
<tr><td class='tdsmallbold' align='center'>".GetLangVar('messagenochange')."</td></tr>
<tr><td align='center'>
<br />
<table><tr><td>
<input type='submit' style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Fechar'  onclick='javascript: window.close();' ></td>
<td align='center'>
        <form action='monografia-exec.php' method='post'>
          <input type='hidden' name='ispopup' value='".$ispopup."' />
          <input type='hidden' name='monografiaid' value='".$monografiaid."' />
          <input type='submit' style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Voltar'>
        </form>
</td>
</tr>
</table>
</td>
</tr>
</table>
<br />
<br />";
		} 
		if (($updated>0 || $novo>0)) {
			echo "
<br /><table cellpadding=\"1\" width='50%' align='center' class='success'>
<tr><td class='tdsmallbold' align='center'>".GetLangVar('sucesso1')."</td></tr>
<tr><td align='center'>
<br />
<table><tr><td>
<input type='submit' style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Fechar'  onclick='javascript: window.close();' ></td>
<td align='center'>
        <form action='monografia-exec.php' method='post'>
          <input type='hidden' name='ispopup' value='".$ispopup."' />
          <input type='hidden' name='monografiaid' value='".$monografiaid."' />
          <input type='submit' style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Voltar'>
        </form>
</td>
</tr>
</table>
</td>
</tr>
</table>
";
		}
} 
else {
$qt = "SELECT DISTINCT EspecimenID FROM MonografiaEspecs WHERE MonografiaID=".$monografiaid." AND Incluido=1";
$rt = mysql_query($qt,$conn);
$nrt = mysql_numrows($rt);
$especimenestxt = $nrt." registros incluidos na monografia";

//	if (!empty($especimenesids) && empty($especimenestxt)) {
//		$aa = explode(";",$especimenesids);
//		$naa = count($aa);
//		$especimenestxt = $naa." ".strtolower(GetLangVar('nameregistro'))."s ".strtolower(GetLangVar('nameselecionado'))."s";
//		$kv = 'selec_'.$_SESSION['userid'];
//        $_SESSION[$kv] = $especimenesids;
//	}
//	
//
echo "
<br />
<form method='post' name='coletaform' action='monografia-exec.php'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />
  <input type='hidden' name='monografiaid' value='".$monografiaid."'>

<table class='myformtable' align='center' cellpadding='5'>
<thead><tr ><td colspan='2'>".GetLangVar('namemonografia')."</td></tr></thead>
<tbody>";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
<td class='tdsmallbold'>".GetLangVar('nametitle')."&nbsp;<img height='15' src=\"icons/icon_question.gif\" "; $help = "O título que você quer dar ao tratamento. Não é imprescindível que seja idêntico ao que será publicado, mas importante manter uma consistência"; echo " onclick=\"javascript:alert('$help');\" /></td>
<td>
  <table>
    <tr>
      <td><textarea style='font-size: 1em; color: red;' cols=55 rows=2 name='titulo' >".$titulo."</textarea></td>
    </tr>
  </table>
</td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
<td class='tdsmallbold'>Autor&nbsp;<img height='15' src=\"icons/icon_question.gif\" ";
$help = "Selecione os autores deste trabalho, na ordem desejada";
echo " onclick=\"javascript:alert('$help');\" /></td>
  <td >
    <table>
      <tr>
        <td class='tdformnotes' >
        <input type='hidden' id='addcolvalue'  name='addcolvalue' value='".$addcolvalue."' />
        <textarea name='addcoltxt' id='addcoltxt'  cols=80 rows=2 readonly>".$addcoltxt."</textarea></td>
        <td><input type=button style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Autor(es)'  onmouseover=\"Tip('Adiciona ou Edita os Autores do Trabalho');\" ";
		$myurl ="addcollpopupNOVO.php?valuevar=addcolvalue&valuetxt=addcoltxt&formname=coletaform"; 
		echo " onclick = \"javascript:small_window('".$myurl."',800,500,'Autores');\" /></td>
      </tr>
    </table>
  </td>
</tr>
";
if ($monografiaid>0) {
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
   <td class='tdsmallbold'  >PASSO 01 - Amostras&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
	$help = "Neste passo você deve indicar as amostras que devem ser incluídas no tratamento. É com base nesta seleção  que todo o tratamento será estruturado. As espécies incluídas no tratamento serão aquelas incluídas nessas amostras";
	echo "onclick=\"javascript:alert('$help');\" /></td>
  <td >
    <table>
      <tr>
        <td ><span id='especimenestxt'>".$especimenestxt."</span></td>
        <td><input type=button style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Especimenes'  onmouseover=\"Tip('Adiciona ou Edita as Amostras do Trabalho');\" ";
		$myurl = "monografia-amostras.php?monografiaid=".$monografiaid;
		echo " onclick = \"javascript:small_window('".$myurl."',800,500,'Amostras Monografia');\" /></td>";
echo "
      </tr>
    </table>
  </td>
</tr>
";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
<td class='tdsmallbold'>PASSO 02 - Modelo para descrições&nbsp;&nbsp;<img height='15' src=\"icons/icon_question.gif\" ";
		$help = "Defina um modelo para as descrições, incluindo as variáveis que deseja e outras opções";
		echo " onclick=\"javascript:alert('$help');\" />
</td>
  <td >
    <table>
      <tr>
        <td><input type=button style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Criar/editar modelo'  onmouseover=\"Tip('Definir um modelo para a descrição');\" ";
		$myurl = "monografila-modelo-descricao.php?monografiaid=".$monografiaid;
		echo " onclick = \"javascript:small_window('".$myurl."',1000,700,'Modelo Descrição');\" /></td>
		<td ><span id='descricaomodelo'>".$traittxt."</span></td>
        <td><input type=button style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Visualiza descrições'  onmouseover=\"Tip('Se tiver definido um modelo e especímenes, visualiza as descrições');\" ";
		$myurl = "monografila-descricao-view.php?monografiaid=".$monografiaid;
		echo " onclick = \"javascript: verdescricoes(".$monografiaid.");\" /></td>
      </tr>
    </table>
  </td>
</tr>
";
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
<td class='tdsmallbold'>PASSO 03 - Modelo para lista de amostras&nbsp;&nbsp;<img height='15' src=\"icons/icon_question.gif\" ";
		$help = "Defina um modelo para a lista de material examinado";
		echo " onclick=\"javascript:alert('$help');\" />
</td>
  <td >
    <table>
      <tr>
        <td><input type=button style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Criar/editar modelo lista'  onmouseover=\"Tip('Definir um modelo material examinado');\" ";
		$myurl = "monografila-modelo-listaespecimenes.php?monografiaid=".$monografiaid;
		echo " onclick = \"javascript:small_window('".$myurl."',1000,700,'Modelo Descrição');\" /></td>
		<td ><span id='descricaomodelolista'>".$traittxt2."</span></td>
        <td><input type=button style=\"color:#4E889C; font-size: 1.2em; font-weight:bold; padding: 4px; cursor:pointer;\"  value='Visualiza lista'  onmouseover=\"Tip('Se tiver definido um modelo, visualiza listas');\" ";
		//$myurl = "monografila-descricao-listaespecimenes-view.php?monografiaid=".$monografiaid;
		echo " onclick = \"javascript: verlistaespecs(".$monografiaid.");\" /></td>
      </tr>
    </table>
  </td>
</tr>
";
if ($lixo==9090) {
if (empty($addcomentstxt) && !empty($addcomentsid)) {
	$arofvals = unserialize($addcomentsid);
	$nsp = count($arofvals);
	$comm=0;
		foreach ($arofvals as $vv) {
			if (!empty($vv)) {
				$comm++;
			}
		}
		$addcomentstxt = "Comentários adicionados para $comm dos $nsp taxa";
}
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
<td class='tdsmallbold'>".GetLangVar('adicionarcomentarios')."&nbsp;&nbsp;<img height='15' src=\"icons/icon_question.gif\" ";
		$help = strip_tags(GetLangVar('adicionarcomentarios_help'));
		echo " onclick=\"javascript:alert('$help');\" />&nbsp;</td>
  <td >
    <table>
      <tr>
        <td><input type='hidden' id='addcomentsid' value='$addcomentsid' name='addcomentsid' /><input type='text' id='addcomentstxt' class='selectedval' size=50 value='$addcomentstxt' readonly /></td>
      </tr>";
		$myurl = "addspecies-coments.php?tagtoputid=addcomentsid&tagtoputtxt=addcomentstxt";
		if (empty($addcomentsid)) {
				$butname = GetLangVar('nameselect');
		} else {
			$butname = GetLangVar('nameeditar');
		} 
		echo "
      <tr><td><input type=button value='$butname' class='bsubmit' onclick = \"javascript:small_window('$myurl',850,400,'Adicione comentarios');\" /></td></tr>
    </table>
  </td>
</tr>";

if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
if (!isset($addparts)) { $addparts = array('sinonimos' => 1, 'descricao' => 1, 'habitat' => 1, 'fenologia' => 1, 'materiaexaminado' => 1); }
echo "
<tr bgcolor = '".$bgcolor."'>
        <td class='tdsmallbold'>".GetLangVar('nameincluir')."</td>
  <td>
    <table>
      <tr>
        <td><input type='checkbox' name='sinonimos' ";
		if ($addparts['sinonimos']==1) {
			echo "checked";
		} 
		echo " value='1' />&nbsp;".GetLangVar('namesinonimos')."</td>
        <td><input type='checkbox' name='descricao' ";
		if ($addparts['descricao']==1) {
			echo "checked";
		} 
		echo " value='1' />&nbsp;".GetLangVar('namedescricao')."</td>
        <td><input type='checkbox' name='habitat' ";
		if ($addparts['habitat']==1) {
			echo "checked";
		} 
		echo " value='1' />&nbsp;".GetLangVar('namehabitat')."</td>
        <td><input type='checkbox' name='fenologia' ";
		if ($addparts['fenologia']==1) {
			echo "checked";
		} 
		echo " value='1' />&nbsp;".GetLangVar('namefenologia')."</td>
        <td><input type='checkbox' name='materiaexaminado' ";
		if ($addparts['materiaexaminado']==1) {
			echo "checked";
		} 
		echo " value='1' />&nbsp;".GetLangVar('materialexaminado')."</td>
        <td><input type='checkbox' name='comentarios' ";
		if ($addparts['comentarios']==1) {
			echo "checked";
		} 
		echo " value='1' />&nbsp;Comentarios</td>
      </tr>
    </table>
  </td>
</tr>";
}
}
if ($bgi % 2 == 0) {$bgcolor = $linecolor2 ;} else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td colspan='2'>
    <table align='center' >
      <tr>
        <td align='center' ><input type='hidden' name='saving' value='1' /><input type='submit' value='".GetLangVar('namesalvar')." ".strtolower(GetLangVar('namedefinicoes'))."' class='bsubmit' /></td>
</form>
<form action='monografia-form.php' method='post'>
 <input type='hidden' name='ispopup' value='".$ispopup."' />
        <td align='left'><input type='submit' value='Outra monografia' class='breset' /></td>
</form>
      </tr>
    </table>
  </td>
</tr>
</tbody>
</table>";
}
$which_java = array(
"<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>
