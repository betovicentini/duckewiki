<?php
//Este script checa se alguns campos que se referem a localidades e ve se estas ja estao cadastradas
//Modificado por AV em 25 de jun 2011. 
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
$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");
$which_java = array();
$title = 'Importar Dados Passo 05';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);
$fields = unserialize($_SESSION['fieldsign']);
$clnl = $tbprefix."CountryID";
if (in_array('COUNTRY',$fields) && !isset($paisesdone)) {
		$colcol = array_search('COUNTRY',$fields);
		if (count($colcol)>1) {
			$erro = '<br />Tem mais de uma coluna que define pais??';
		} else {
			$clnl = $tbprefix."CountryID";
			if (!isset($countrydone)) {
				$qq = "ALTER TABLE ".$tbname." ADD COLUMN ".$clnl." INT(10) DEFAULT 0";
				@mysql_query($qq,$conn);
				$qq = "UPDATE ".$tbname." as tb, Country as pl set tb.".$clnl."=pl.CountryID where LOWER(TRIM(tb.".$colcol."))=LOWER(pl.Country)";
				mysql_query($qq,$conn);
			} else {
				if (count($countryid)>0) {
					foreach ($countryid as $kk => $vv) {
						$vv = $vv+0;
						if ($vv>0) {
							$qq = "UPDATE `".$tbname."` set `".$clnl."`= ".$vv." WHERE `".$colcol."`='".$kk."'";
							mysql_query($qq,$conn);
							flush();
						}
					}
				}
			}
			$qq = "SELECT DISTINCT `".$colcol."` as missgen FROM `".$tbname."` WHERE `".$clnl."`=0 AND `".$colcol."`<>'' AND `".$colcol."` IS NOT NULL";
			$rr = mysql_query($qq,$conn);
			$nr = mysql_numrows($rr);
			unset($_POST['countryid']);
			if ($nr>0) {
			echo "
<br />
    <table align='left' class='myformtable' cellpadding='3'>
        <thead>
          <tr><td colspan='3'>Países não encontrados no Wiki</td>
          </tr>
          <tr class='subhead'>
          <td>Nome</td>
          <td>Pode ser um desses</td>
          <td>Cadastre novo</td>
          </tr>
        </thead>
        <tbody>
        <form action='import-data-step5.php' method='post'>";
					foreach ($_POST as $kk => $vv) {
						if (!empty($vv)) {
							echo "
          <input type='hidden' name='".$kk."' value='".$vv."' />"; 
						}
					}
					echo "
           <input type='hidden' name='countrydone' value='1' />";
					while ($rw = mysql_fetch_assoc($rr)) {
						$gen = mb_strtolower(trim($rw['missgen']));
						$gggen = explode(" ",$gen);
						$ggen = implode("-",$gggen);
						$gk = $rw['missgen'];
						$nc = strlen($gen);
						$n1 = floor($nc/2)-1;
						$n2 = ceil($nc/2)-1;
						$g1 = substr($gen,0,$n1);
						$g2 = substr($gen,$n1+1,$n2);
						$qq = "SELECT * FROM Country WHERE (LOWER(Country) LIKE '%".$g1."%' OR LOWER(Country) LIKE '%".$g2."%'";
							if (count($gggen)>1) {
								$qqq = "";
								foreach ($gggen as $gg) {
									$gg = trim($gg);
									if (!empty($gg) && strlen($gg)>2) {
										$qqq .= " OR LOWER(Country) LIKE '%".$gg."%'";
									}
								}
							}
						$qq .= $qqq.") ORDER BY Country";
						if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
						echo "
          <tr>
            <td>".$gen."</td>
            <td><select id=\"country_".$ggen."\" name=\"countryid[".$gk."]\">
              <option value=''>".GetLangVar('nameselect')."</option>";
              					$qq = "SELECT * FROM Country ORDER BY Country ASC";
								$res = mysql_query($qq,$conn);
								$nres = mysql_numrows($res);
								if ($nres>0) {
									while ($row = mysql_fetch_assoc($res)) {
										echo "<option value=".$row['CountryID'].">".$row['Country']."</option>";
									}
								} else {
									echo "
              <option selected value=''>Não se parece com nada, cadastre novo!</option>";
								}
						echo "
            </select></td>
            <td align='center'><img src='icons/list-add.png' height=15 ";
				$myurl ="country-popup.php?countryid_val=country_".$ggen."&nome=".$gen; 
				echo " onclick = \"javascript:small_window('$myurl',500,350,'Novo Pais');\"></td>
            </tr>";

				}
					echo "
            <tr><td align='center' colspan='3'><input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td></tr>
    </form>
</tbody>
</table>
";
		} else {
			$paisesdone = 1;
		}
		}
} 
elseif (!in_array('COUNTRY',$fields)) { 
	$paisesdone = 1;
}

if ($paisesdone==1) {
	//cria variável de sessao com as definicoes dos campos feitas pelo usuario
	$steps = unserialize($_SESSION['importacaostep']);
	unset($steps[0]);
	$stt = array_values($steps);
	$_SESSION['importacaostep'] = serialize($stt);
echo "
  <form name='myform' action='import-data-hub.php' method='post'>
  <input type='hidden' name='ispopup' value='".$ispopup."' />  
  <script language=\"JavaScript\">setTimeout('document.myform.submit()',0.0001);</script>
  </form>";
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>