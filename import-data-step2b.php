<?php
//Este script checa se identificadores de plantas marcadas e/ou amostras coletadas foram selecionados e pede definição para todas as colunas no arquivo importando, buscando automaticamente campos que tenham os mesmos nome da coluna BRAHMS dat tabela Import_Fields (note que nem todas as colunas desta tabela estão sendo usadas, mas acrescentar novas linhas permite adicionar novas definições automátcias//Modificado por AV em 25 de jun 2011
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
$title = 'Importar Dados Passo 2b';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

$definedfields = array();
if (!empty($tagnumfield)) { $definedfields[] = $tagnumfield; }
if (!empty($plantagazfield)) { $definedfields[] = $plantagazfield; }
if (!empty($specimenidfield)) { $definedfields[] = $specimenidfield;}
if (!empty($coletorfield)) { $definedfields[] = $coletorfield;}
if (!empty($plantaidfield)) { $definedfields[] = $plantaidfield;}
if (!empty($numcolfield)) { $definedfields[] = $numcolfield;}

unset($_POST['locality']);
unset($_POST['locidadeall']);
echo "
<form action='import-data-hub.php' method='post' name='impprepform'>";
//coloca as variaveis anteriores
foreach ($_POST as $kk => $vv) {
echo "
  <input type='hidden' name='".$kk."' value='".$vv."' />"; 
}

echo "
<br />
    <table cellpadding='7' class='myformtable' align='center'>
        <thead>
            <tr><td colspan='100%'>Definir o significado das demais colunas</td></tr>
            <tr class='subhead'>
                <td>Coluna</td>
                <td>Valor mínimo</td>
                <td>Valor máximo</td>
                <td class='redtext'>Selecione significado*</td>
            </tr>
        </thead>
        <tbody>
";
	$idx=1;
	$qq = "SELECT * FROM `".$tbname."` PROCEDURE ANALYSE ()";
	$rq = mysql_query($qq,$conn);
	while ($rw = mysql_fetch_assoc($rq)) {
		$fin = $rw['Field_name'];
		$zz = explode(".",$fin);
		$xt = count($zz)-1;
		$fieldname = $zz[$xt];
		$kkv = in_array($fieldname,$definedfields);
		$npr = strlen($tbprefix);
		if (!$kkv && $fieldname!='ImportID' && substr($fieldname,0,$npr)!=$tbprefix) {
				$qf = "SELECT * FROM Import_Fields WHERE LOWER(NamesToMatch) LIKE '".strtolower($fieldname)."%' OR LOWER(NamesToMatch) LIKE '%;".strtolower($fieldname).";%' OR LOWER(NamesToMatch) LIKE '%;".strtolower($fieldname)."'";
				if ($coletas==1 || $coletas==3) {
					$qf .= " AND (TabelaParaPor LIKE '%Plantas%'";
				}
				if ($coletas==3) {
					$qf .= " OR TabelaParaPor LIKE '%Especimenes%'"; 
				} elseif ($coletas==2) {
					$qf .= " AND (TabelaParaPor LIKE '%Especimenes%'"; 
				}
				$qf .= " OR TabelaParaPor LIKE '%Identidade%')";
				$rqq = mysql_query($qf,$conn);
				//echo $qf."<br />";
				$nrqq = mysql_numrows($rqq);
				if ($nrqq==1) {
					$rwqf = mysql_fetch_assoc($rqq);
					$ipid = $rwqf['id'];
				} else {
					$ipid =0;
				}
			if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
			echo "
            <tr bgcolor = '".$bgcolor."'>
                <td class='tdsmallbold'>".$fieldname."</td>
                <td style='text-align:center' class='tdformnotes'>".$rw['Min_value']."</td>
                <td style='text-align:center' class='tdformnotes'>".$rw['Max_value']."</td>
                <td>
                    <select name='fieldsign[".$fieldname."]'>";
				$qf = "SELECT * FROM Import_Fields WHERE ";
				if ($coletas==1 || $coletas==3) {
					$qf .= " TabelaParaPor LIKE '%Plantas%'";
				}
				if ($coletas==3) {
					$qf .= " OR TabelaParaPor LIKE '%Especimenes%'"; 
				} elseif ($coletas==2) {
					$qf .= " TabelaParaPor LIKE '%Especimenes%'"; 
				}
				$qf .= " OR TabelaParaPor LIKE '%Identidade%'";
				if (($coletas==1 || $coletas==3) && empty($plantaidfield)) { 
					$qf .= " AND LocalityFields=0 ";
				}
				$qf .= "ORDER BY CLASS,ORDEM";
				$rqf = mysql_query($qf,$conn);
				$pa = '';
echo "
                        <option value=''>".GetLangVar('nameselect')."</option>
                        <option value=''>--Não importar--</option>";
				while ($rwqf = mysql_fetch_assoc($rqf)) {
					$brh = $rwqf['BRAHMS'];
					$cl = $rwqf['CLASS'];
					$def = $rwqf['DEFINICAO'];
					if ($ipid==$rwqf['id']) {
						$ch = 'selected'; 
					} else {$ch =''; }
					if ($pa!=$cl) {
						echo "
                        <option value='' style='font: bold;' >----".$cl."----</option>";
						$pa=$cl;
					} 
echo "
                        <option $ch value='".$brh."' >$def</option>";
				}
			echo "
                    </select>
            </td>
        </tr>";
		}
		$idx++;
	}
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
		echo "
            <tr bgcolor = '".$bgcolor."'><td colspan='100%' align='center'><input style='cursor: pointer' type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td></tr>";
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
		echo "
            <tr bgcolor = '".$bgcolor."'><td class='redtext' colspan='100%' align='left' >*Colunas não definidas serão ignoradas, mas o arquivo original será armazenado no servidor!</td></tr>
        </tbody>
    </table>
</form>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>
