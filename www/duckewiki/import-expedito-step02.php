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
$title = 'Importar Expedito 02';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);


$colunas_significado = array(
"PTID" =>  "identificador de cada ponto (nome ou número)",
"OBSERVADOR" =>  "Nome/abreviacao da pessoa que fez a observação",
"INTERVALO" =>  "Intervalo de tempo em que a observação foi feita períodos de 15 minutos (de 1 a 4, em geral)",
"LOCALIDADE_ESPECIFICA" => "Localidade mais específica de cada ponto (nome, você será avisado se já há cadastro para a localidade, ou se cada localidade faz parte de outra localidade",
"DATA_LEVANTAMENTO" =>  "Data em que o ponto foi inventariado",
"LONGITUDE_PONTOGPS" =>  "Longitude geral para o ponto de inventário em Décimos de GRAU (S e W negativos)",
"LATITUDE_PONTOGPS" =>  "Longitude geral para o ponto de inventário em Décimos de GRAU (S e W negativos)",
"TESTEMUNHO_COLETOR" =>  "Nome do coletor do material testemunho",
"TESTEMUNHO_NUMBERO" =>  "Número de coleta do coletor do material",
"FAMILIA" =>  "Familia",
"GENERO" =>  "Genero sem autor",
"ESPECIE" =>  "Epiteto especifico sem autor",
"SUBESPECIE" =>  "Epiteto infraespecifico sem autor"); 

$colunasdefaults = array(
"PTID" =>  "ponto",
"OBSERVADOR" =>  "observador",
"INTERVALO" =>  "intervalo",
"LOCALIDADE_ESPECIFICA" => "localidade;gazetteer",
"DATA_LEVANTAMENTO" =>  "data;dataobs;date;dateobs;datacol",
"LONGITUDE_PONTOGPS" =>  "longitude",
"LATITUDE_PONTOGPS" =>  "latitude",
"TESTEMUNHO_COLETOR" =>  "coletor;collector",
"TESTEMUNHO_NUMBERO" =>  "numero;number",
"FAMILIA" =>  "familia;family",
"GENERO" =>  "genero;genus",
"ESPECIE" =>  "espécie;especie;sp1",
"SUBESPECIE" =>  "subespecie;sub-espécie;subespécie;infraespécie;infraespecie"); 
echo "
<form action='import-expedito-step03.php' method='post' name='impprepform'>";
foreach ($ppost as $kk => $vv) {
echo "
  <input type='hidden' name='".$kk."' value='".$vv."'>"; 
}

echo "
<br />
    <table cellpadding='7' class='myformtable' align='left' width='80%'>
        <thead>
            <tr><td colspan='100%'>Definir o significado de cada coluna no arquivo</td></tr>
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
		$fin = $zz[$xt];
		$npr = strlen($tbprefix);
		if ($fin!='ImportID' && substr($fin,0,$npr)!=$tbprefix) {
			if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
			echo "
            <tr bgcolor = '".$bgcolor."'>
                <td class='tdsmallbold'>".$fin."</td>
                <td style='text-align:center' class='tdformnotes'>".$rw['Min_value']."</td>
                <td style='text-align:center' class='tdformnotes'>".$rw['Max_value']."</td>
                <td>
                  <select name='fieldsign[".$fin."]'>";
			echo "
                  <option style=\"font-weight:bold;color:#990000\" value=''>".GetLangVar('nameselect')."</option>";
				foreach ($colunas_significado as $kk => $vv) {
					$deff = $colunasdefaults[$kk];
					$dd = mb_strtolower($fin);
					$ddd = explode(";",$deff);
					if (in_array($dd,$ddd)) {
						$ch = "selected";
						$vv = strtoupper($vv);
					} else {
						$ch = '';
					}
					echo "
                  <option $ch2 $ch value='".$kk."'>$vv</option>";
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
        <tr bgcolor = '".$bgcolor."'><td colspan='100%' align='center'><input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td></tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
echo "
        <tr bgcolor = '".$bgcolor."'><td class='selectedval' colspan='100%' align='left' >*Colunas não definidas serão ignoradas!</td></tr>
        </tbody>
    </table>
</form>";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>