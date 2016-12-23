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
$menu = FALSE;
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
$title = 'Importar locais passo 04';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

$definedfields = array($pais,$provincia,$municipio,'ImportID');

//echopre($ppost);
//echopre($gget);
echo "
<form action='import-locais-step5.php' method='post' >";
//coloca as variaveis anteriores
foreach ($ppost as $kk => $vv) {
echo "
  <input type='hidden' name='".$kk."' value='".$vv."' />"; 
}

echo "
<br />
    <table cellpadding='7' class='myformtable' align='left'>
        <thead>
            <tr><td colspan='5'>Definir o significado das demais colunas &nbsp;<img height=14 src=\"icons/icon_question.gif\" ";
$help = "Agora defina o significado das demais colunas. Elas podem ser localidades e sublocalidades, e até três níveis hierárquicos podem ser importados simultaneamente.  Pode informar a latitude e longitude de cada localidade, e indicar se é uma parcela ou transecto indicando suas dimensões X e Y, e/ou posição (no caso de subparcelas) X e Y na localidade de nível superior.";
echo " onclick=\"javascript:alert('$help');\" /></td></tr>
            <tr class='subhead'>
                <td>Coluna</td>
                <td>Valor mínimo</td>
                <td>Valor máximo</td>
                <td class='redtext' >Selecione significado</td>
            </tr>
        </thead>
        <tbody>
";
	$idx=1;
	$qq = "SELECT * FROM `".$tbname."` PROCEDURE ANALYSE ()";
	$rq = mysql_query($qq,$conn);
	//echo $qq."<br />";
	while ($rw = mysql_fetch_assoc($rq)) {
		$fin = $rw['Field_name'];
		$zz = explode(".",$fin);
		$xt = count($zz)-1;
		$fieldname = $zz[$xt];
		$kkv = in_array($fieldname,$definedfields);
		$npr = strlen($tbprefix);
		//echo "kkv:".$kkv."   fieldname:".$fieldname."  tbprefix:".$tbprefix."<br />";
		if (!$kkv && substr($fieldname,0,$npr)!=$tbprefix) {
			if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
			echo "
            <tr bgcolor = '".$bgcolor."'>
                <td class='tdsmallbold'>".$fieldname."</td>
                <td style='text-align:center' class='tdformnotes'>".$rw['Min_value']."</td>
                <td style='text-align:center' class='tdformnotes'>".$rw['Max_value']."</td>
                <td>
                    <select name='fieldsign[".$fieldname."]'>
                        <option value=''>".GetLangVar('nameselect')."</option>
                        <option value=''>--Não importar--</option>";
                        $ll=0;
                        for ($ll=0;$ll<=3;$ll++) {
                        	$brh = 'localidade_'.$ll;
                        	$brhtxt = 'Localidade de nível '.$ll;
							echo "
                        <option style='color: red; font: bold; ' value='".$brh."' >".$brhtxt."</option>";
                        	$brh = 'latitude_'.$ll;
                        	$brhtxt = '&nbsp;&nbsp;&nbsp;&nbsp;Latitude local de nível'.$ll;
							echo "
                        <option value='".$brh."' >".$brhtxt."</option>";
                        	$brh = 'longitude_'.$ll;
                        	$brhtxt = '&nbsp;&nbsp;&nbsp;&nbsp;Longitude local de nível'.$ll;
							echo "
                        <option value='".$brh."' >".$brhtxt."</option>";
                        	$brh = 'dimx_'.$ll;
                        	$brhtxt = '&nbsp;&nbsp;&nbsp;&nbsp;Dimensão X da localidade (parcela) de nível '.$ll;
							echo "
                        <option value='".$brh."' >".$brhtxt."</option>";                        
                        	$brh = 'dimy_'.$ll;
                        	$brhtxt = '&nbsp;&nbsp;&nbsp;&nbsp;Dimensão Y da localidade (parcela) de nível '.$ll;
							echo "
                        <option value='".$brh."' >".$brhtxt."</option>";
                        	$brh = 'posx_'.$ll;
                        	$brhtxt = '&nbsp;&nbsp;&nbsp;&nbsp;Posição X da localidade (subparcela) de nível '.$ll;
							echo "
                        <option value='".$brh."' >".$brhtxt."</option>";                        
                        	$brh = 'posy_'.$ll;
                        	$brhtxt = '&nbsp;&nbsp;&nbsp;&nbsp;Posição Y da localidade (subparcela) de nível '.$ll;
							echo "
                        <option value='".$brh."' >".$brhtxt."</option>";                        
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
            <tr bgcolor = '".$bgcolor."'><td colspan='5' align='center'><input style='cursor: pointer'  type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' /></td></tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;} $bgi++;
		echo "
            <tr bgcolor = '".$bgcolor."'><td class='redtext' colspan='5' align='left' >*Colunas não definidas serão ignoradas, mas o arquivo original será armazenado no servidor!</td></tr>            
        </tbody>
    </table>
</form>";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
//,"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
//"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>
