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
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'Nova categoria de variação';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
$query = "SELECT DISTINCT `".$orgcol."` FROM `".$tbname."`  WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND (`".$colname."` LIKE 'ERRO' OR `".$colname."` LIKE '')";
$erro=0;
if ($final==1) {
	$res = mysql_query($query,$conn);
	$nres = mysql_numrows($res);
	if ($nres>0) {
		$n=1;
		while ($row = mysql_fetch_assoc($res)) {
			$idx = 'idx_'.$n;
			$original = $row[$orgcol];
			$estd = explode(";",$row[$orgcol]);
			$newestd = $estd;
			foreach ($estd as $kk => $vv) {
				$jaexistente = $estadoid[$idx][$kk]+0;
				$novo = $estadonovo[$idx][$kk];
				$definicao = $estadodefinicoes[$idx][$kk];
				//quando selecionou um ja existente para substituir
				if ($jaexistente>0) {
					$stateid = $jaexistente;
					$qq = "SELECT * FROM Traits WHERE TraitID='".$stateid."'";
					$rr = mysql_query($qq,$conn);
					$rw = mysql_fetch_assoc($rr);
					$stn = $rw['TraitName'];
					$newestd[$kk] = $stn;
				} else {
					if (empty($definicao)) {
echo "<br />
<table cellpadding=\"1\" width='50%' align='center' class='erro'>
	<tr class='tdsmallbold' ><td align='center'>".GetLangVar('erro1')."</td></tr>
	<tr class='tdsmallbold' ><td class='tdsmallnotes' align='center'>".GetLangVar('namedefinicao')."</td></tr>
</table><br />";
					} else {
						##checar se ja nao existe com o mesmo nome
						$qq = "SELECT * FROM Traits WHERE ParentID='".$parentid."' AND LOWER(TRIM(TraitName))='".strtolower(trim($novo))."' AND TraitTipo='Estado'";
						$ru = mysql_query($qq,$conn);
						$nr = mysql_numrows($ru);
						if ($nr==1) { // existe
							$rwu = mysql_fetch_assoc($ru);
							$stn = $rwu['TraitName'];
							$newestd[$kk] = $stn;
						} else {
							$fieldsaskeyofvaluearray = array(
								'ParentID' => $parentid,
								'TraitName' => $novo,
								'TraitTipo' => 'Estado',
								'TraitDefinicao' => $definicao);
							$newtrait = InsertIntoTable($fieldsaskeyofvaluearray,'TraitID','Traits',$conn);
							if (!$newtrait) {
								$erro++;
							} else {
								$dn = updatesingletraitpath($newtrait,$conn);
								$qq = "SELECT * FROM Traits WHERE TraitID='".$newtrait."'";
								$rr = mysql_query($qq,$conn);
								$rw = mysql_fetch_assoc($rr);
								$stn = $rw['TraitName'];
								$newestd[$kk] = $stn;
							}
						}
					}
				}
			}
			$novosestados = implode(";",$newestd);
			if ($novosestados<>$original) {
			 $qq = "UPDATE `".$tbname."` SET `".$orgcol."`='".$novosestados."' WHERE `".$orgcol."`='".$original."'";
			 $up = mysql_query($qq,$conn);
			}
			$n++;
		}
	}
	$qq = "UPDATE `".$tbname."` SET `".$colname."`=checkcategories(".$orgcol.",".$parentid.") WHERE `".$orgcol."`<>'' AND `".$orgcol."` IS NOT NULL AND (`".$colname."` LIKE 'ERRO' OR `".$colname."` LIKE '')";
	mysql_query($qq,$conn);
}
$res = mysql_query($query,$conn);
$nres = mysql_numrows($res);
if ($nres>0) {
echo "
<br />
<table align='center' class='myformtable' cellpadding='5'>
<thead>
  <tr><td colspan='100%'>As seguintes categorias da variável <b>$tname</b> [coluna ".$orgcol."] não estão cadastradas</td></tr>
  <tr class='subhead'>
    <td>O valor original no arquivo</td>
    <td>
      <table align='center' cellpadding='5'>
        <tr>
          <td>Categorias</td>
          <td>Pode ser uma dessas?</td>
          <td style='color:#990000'>Será cadastrado assim!*</td>
          <td style='color:#990000'>Qual a definição dessa categoria?*</td>
        </tr>
      </table>
    </td>
  </tr>
</thead>
<tbody>
<form action='traitscategoria-popup.php' method='post'>
  <input type='hidden' name='qry' value='".$qry."' />
  <input type='hidden' name='parentid' value='".$parentid."' />
  <input type='hidden' name='tbname' value='".$tbname."' />
  <input type='hidden' name='tname' value='".$tname."' />
  <input type='hidden' name='colname' value='".$colname."' />
  <input type='hidden' name='orgcol' value='".$orgcol."' />
  <input type='hidden' name='buttonidx' value='".$buttonidx."' />
  <input type='hidden' name='final' value='1'>";
$n=1;
while ($row = mysql_fetch_assoc($res)) {
	$idx = 'idx_'.$n;
	$estd = explode(";",$row[$orgcol]);
		if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
  <tr bgcolor = '".$bgcolor."'>
    <td align='center'>".$row[$orgcol]."</td>
    <td>
      <table align='center' cellpadding='5'>";      
	foreach ($estd as $kest => $vv) {
		echo "
        <tr bgcolor = '".$bgcolor."'>
          <td align='center'>".$vv."</td>
          <td align='center'>
            <select name='estadoid[$idx][]'>
              <option value=0>".GetLangVar('nameselect')."</option>";
		$filtro = "SELECT * FROM Traits WHERE TraitTipo='Estado' AND ParentID='".$parentid."' ORDER BY TraitName";
		$rs = mysql_query($filtro,$conn);
		$nrs = mysql_numrows($rs);
		if ($nrs>0) {
			while ($aa = mysql_fetch_assoc($rs)){
				echo "
              <option ";
				if (strtolower(trim($rs['TraitName']))==strtolower(trim($vv))) {
				echo "selected";
				}
				echo " value=".$aa['TraitID'].">".$aa['TraitName']."</option>";
			}
		} else {
			echo "
              <option selected value=0>Não há nenhuma todavia!</option>";
		}
		$vv = ucfirst(strtolower(trim($vv)));
		echo "
            </select>
          </td>
          <td align='center' style='text-size:2em'><input size='10' type='text' name='estadonovo[$idx][]' value='".$vv."'></td>
          <td><textarea name='estadodefinicoes[$idx][]' cols='30' rows='3' wrap=SOFT></textarea></td>
        </tr>";
	}
echo "
      </table>
    </td>
  </tr>";
$n++;
}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
  <tr bgcolor = '".$bgcolor."'><td align='center' colspan='100%'><input type='submit' value='".GetLangVar('namesalvar')."' class='bsubmit' /></td></tr>
</tbody>
</table>
</form>";

} else {
	//concluido
	echo "
  <form >
    <script language=\"JavaScript\">
      setTimeout( 
      function() { 
        var element = window.opener.document.getElementById('".$buttonidx."');
        element.value = 'CORRIGIDO';
        this.window.close();
      }
      
      
      
      ,0.0001);
    </script>
  </form>";
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>
