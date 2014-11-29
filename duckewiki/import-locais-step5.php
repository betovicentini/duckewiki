<?php
//Este script importa o arquivo CSV ou TXT selecionado para uma tabela temporaria mysql
//Depois sao perguntados quais colunas indicam amostras coletadas ou plantas marcadas
//Ultima atualizacao: 25 jun 2011 - AV
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

if (count($gget)>count($ppost)) {
	$ppost = $gget;
}

//CABECALHO
$ispopup=1;
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' />"
//, "<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />"
);
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>"
//"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
//"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>"
);
$title = 'Importar locais passo 05';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

$erro =0;
$errotxt = array();
#FAZ UM RESUMO DO QUE FOI SELECIONADO
$zz = preg_grep ('/localidade/' , $fieldsign);
//"/{$keyword}/i"
//echopre($ppost);
//echopre($zz);
arsort($zz);
//echopre($zz);
$nz = count($zz);
$initxt = array("");
$iz=0;
$prt = '';
foreach($zz as $kk => $vv) {
			$localdados = array();
			$idd = explode("_",$vv);
			$idd = $idd[1]+0;
			
			$cln = array();
			for ($idn=0;$idn<=$idd;$idn++) {
					$kz = array_search("localidade_".$idn, $zz);
					$cln[] = $kz;
			}
			$clnn = implode(",",$cln);
			
			$qq = "SELECT DISTINCT ".$clnn." as locais  FROM ".$tbname;
			//echo $qq."<br />";
			$rr = mysql_query($qq,$conn);
			$nr = mysql_numrows($rr);
			if ($idd<$nz && $idd>0) {
				$pppt = "|";
				$ll = 'localidade_'.($idd-1);
				$kl = array_search($ll,$fieldsign);
				$txtpos = $kl;
				$txtbb = " sublocais de ".$kl;
				#$initxt = str_repeat("--", 10);
			} else {
				$pppt = "";
				$kl = '';
				$txtbb = "  localidades";
				$txtpos = "não informado";
				if (!empty($gazetteer)) {
					$txtbb = " sublocais de ".$gazetteer;
				} else {
					if (!empty($municipioid)) {
						$txtbb = " sublocais do municipio ".$municipio;
					} else {
						$txtbb = " sublocais do municipio indicado na coluna ".$municipio;
					}
				}
				
			}
			$ntxt = implode("",$initxt);
			$prt .=  $ntxt."Coluna <b>".$kk.'</b>  ('.$nr." $txtbb)";
			if (in_array('latitude_'.$idd,$fieldsign) && in_array('longitude_'.$idd,$fieldsign)) {
				$kx =  array_search('longitude_'.$idd,$fieldsign);
				$ky = array_search('latitude_'.$idd,$fieldsign);
				//$prt .=  "Longitudes em <b>$kx</b>; Latitudes em <b>$ky</b>; ";
				$localdados[] = "longitudes na coluna $kx; latitudes na coluna $ky ";
				$latcol = $ky;
				$longcol = $kx;
				$qq = "SELECT * FROM `".$tbname."` WHERE `".$latcol."`<>'' AND `".$latcol."` IS NOT NULL AND (checkcoordenadas(`".$latcol."`,'LATITUDE') IS NULL OR checanumericos(".$latcol.") IS NULL)";
				$rr = mysql_query($qq,$conn);
				$laterr = mysql_numrows($rr);
				$qq = "SELECT * FROM `".$tbname."` WHERE `".$longcol."`<>'' AND `".$longcol."` IS NOT NULL AND (checkcoordenadas(`".$longcol."`,'LONGITUDE') IS NULL OR checanumericos(".$longcol.") IS NULL)";
				$rr = mysql_query($qq,$conn);
				$longerro = mysql_numrows($rr);
				if  ($laterr>0) {
					$erro++;
					$errotxt[] = "Há ".$laterr."  registros na coluna ".$latcol." que não batem com valores de latitude ou são caracteres";
				}
				if  ($longerro>0) {
					$erro++;
					$errotxt[] =  "Há ".$longerro."  registros na coluna ".$longcol." que não batem com valores de longitude ou são caracteres";
				}
			}
			if (in_array('dimx_'.$idd,$fieldsign) && in_array('dimy_'.$idd,$fieldsign)) {
				$kx =  array_search('dimx_'.$idd,$fieldsign);
				$ky = array_search('dimy_'.$idd,$fieldsign);
				//$prt .=  "Parcelas de X metros em <b>$kx</b> e Y metros em <b>$ky</b>; ";
				$localdados[] =  "Parcelas de X metros na coluna $kx e Y metros  na coluna $ky";
				$qq = "SELECT * FROM `".$tbname."` WHERE `".$kx."`<>'' AND `".$kx."` IS NOT NULL AND (checanumericos(".$kx.") IS NULL)";
				$rr = mysql_query($qq,$conn);
				$dimxerro = mysql_numrows($rr);
				$qq = "SELECT * FROM `".$tbname."` WHERE `".$ky."`<>'' AND `".$ky."` IS NOT NULL AND (checanumericos(".$ky.") IS NULL)";
				$rr = mysql_query($qq,$conn);
				$dimyerro = mysql_numrows($rr);
				if  ($dimxerro>0) {
					$erro++;
					$errotxt[] =  "Há ".$dimxerro."  registros na coluna ".$kx." que não são numéricos, inválidos como dimensão X da parcela";
				}
				if  ($dimyerro>0) {
					$erro++;
					$errotxt[] =  "Há ".$dimyerro."  registros na coluna ".$ky." que não são numéricos, inválidos como dimensão Y da parcela";
				}				
				
			}
			if (in_array('posx_'.$idd,$fieldsign) && in_array('posy_'.$idd,$fieldsign)) {
				$kx =  array_search('posx_'.$idd,$fieldsign);
				$ky = array_search('posy_'.$idd,$fieldsign);
				$localdados[] =  "Na posição X na coluna $kx e Y na coluna $ky nas localidades da coluna $txtpos";
				$qq = "SELECT * FROM `".$tbname."` WHERE `".$kx."`<>'' AND `".$kx."` IS NOT NULL AND (checanumericos(".$kx.") IS NULL)";
				$rr = mysql_query($qq,$conn);
				$posxerro = mysql_numrows($rr);
				$qq = "SELECT * FROM `".$tbname."` WHERE `".$ky."`<>'' AND `".$ky."` IS NOT NULL AND (checanumericos(".$ky.") IS NULL)";
				$rr = mysql_query($qq,$conn);
				$posyerro = mysql_numrows($rr);
				if  ($posxerro>0) {
					$erro++;
					$errotxt[] =  "Há ".$posxerro."  registros na coluna ".$kx." que não são numéricos, inválidos como posição X da parcela";
				}
				if  ($posyerro>0) {
					$erro++;
					$errotxt[] =  "Há ".$posyerro."  registros na coluna ".$ky." que não são numéricos, inválidos como posição Y da parcela";
				}
			}
			if (count($localdados)>0 ) {
					$lltxt = implode("\\n\\n",$localdados);
					$lltxt = "&nbsp;<input style='cursor: pointer' type='button'  value='com dados'  onclick=\"javascript:alert('".$lltxt."');\" />";
			} else {
					$lltxt = '';
			}
			
			$prt .= $lltxt."<br />";
			$initxt[$iz]  =  "|".str_repeat("&ndash;", 10).">";
			if ($iz>0) {
				$initxt[($iz-1)] = str_repeat("&nbsp;", 40);
			}
			$iz++;
}

if ($erro>0) {
			echo "
<br />
<table align='center' class='myformtable' cellpadding='7'>
  <thead>
    <tr><td>Há erros no arquivo!</td></tr>
  </thead>
  <tbody>";
	foreach ($errotxt as $vv) {
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'><td>".$vv."</td></tr>";
	}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'><td style='color: red'><b>Corrigir os erros no arquivo e tentar novamente!</b></td></tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'><td align='center'><input style='cursor: pointer' type='button' value='Fechar' class='bsubmit'  onclick='javascript: window.close();' ></td></tr>
</tbody>
</table>";
} elseif ($nz>0) {
			echo "
<br />
<table align='center' class='myformtable' cellpadding='7'>
  <thead>
    <tr><td colspan=2>Resumo da importação</td></tr>
  </thead>
  <tbody>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'><td colspan=2>".$prt."</td></tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'><td align='center'><input style='cursor: pointer' type='button' value='Confirmar' class='bsubmit'   onclick=\"javascript:document.getElementById('confirmform').submit(); \" ></td><td align='center'><input style='cursor: pointer' type='button' value='Cancelar' class='bblue'  onclick='javascript: window.close();' ></td></tr>
</tbody>
</table>";
}
echo "  <form action='import-locais-step6.php' method='post' id='confirmform'>";
			unset($ppost['fieldsign']);
			foreach ($ppost as $kk => $vv) {
						if (!empty($vv)) {
							echo "
            <input type='hidden' name='".$kk."' value='".$vv."' />"; 
						}
				}
				foreach ($fieldsign as $kk => $vv) {
						if (!empty($vv)) {
							echo "
            <input type='hidden' name='fieldsign[".$kk."]' value='".$vv."' />"; 
						}
				}
//echo "<input style='cursor: pointer'  type='submit' value='refresh'>";
echo "</form>";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
//,"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
//"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>
