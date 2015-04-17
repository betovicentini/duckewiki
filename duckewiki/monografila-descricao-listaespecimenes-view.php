<?php
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
include "functions/DescricaoModelo.php";

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

$erro=0;


$tbname = 'temp_monografiaspecimenes_'.$monografiaid.'_'.$uuid;
//GERA A TABELA SE ELA NAO EXISTE E PEGA DADOS DA MONOGRAFIA
if ($monografiaid>0) {
	$qq = "SELECT * FROM Monografias WHERE MonografiaID='".$monografiaid."'";
	$res = mysql_query($qq,$conn);
	$rr = mysql_fetch_assoc($res);
	//echopre($rr);
	$modelo= $rr['ModeloListaEspecimenes'];
	//echo $modelo."<br />";
	$modelosimbolos= explode("SIMBOLO",$rr['ModeloSimbolosEspecimenes']);
	//echopre($modelosimbolos);

	$modeloarr = json_decode($modelo);
	//echopre($modeloarr);

	//$mmodel = printModeloLista($modeloarr,$arrayoffields,$conn);
	
	//GERA A TABELA DE DADOS PARA A MONOGRAFIA
	if (!isset($prepared)) {
		$criatb  = criaTabelaParaLista($monografiaid,$tbname,$herbariumsigla,$conn);
		if (!$criatb) {
			$erro++;
			echo "Não foi possível criar a tabela de dados ".$tbname;
		} 
	}
} 


//CABECALHO
$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<style>
.tools {
	top: 1px;
	width: 99%;
	height: 220px;
	padding: 5px;
	border:#cccccc 1px solid;
	border-radius:5px; -webkit-border-radius:5px; -moz-border-radius:5px;
}
#modelo {
	border:#cccccc 1px solid;
	font-size: 1.2em;
	border-radius:5px; -webkit-border-radius:5px; -moz-border-radius:5px;
	position: relative;
	height: 300px;
	width: 99%;
	overflow: scroll;
	padding: 10px;
	line-height: 120%;
}
.subs {
	padding: 3px;
	align: left;
	font: 0.9em ;
	font-weight: bold;
	color: blue;
}
.variaveis {
	padding: 3px;
	margin: 3px;
	float: left;
	width: 60%;
}
</style>"
);
$which_java = array();
$title = 'Visualizar descrição';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if ($erro==0 && !isset($taxanome)) {
	$decimals = 1;
	$outtype = 'html';
	$municipioseparator = '. ';
	$provinceseparator = '. ';
	$countryseparator = '. ';
	$specimenseparator = '; ';
	$gazetteerseparator  = ': ';
	$herbariaseparator = ' ';
	$variableseparator  = ' ';
}

if ($erro==0) {
echo "
<form action='monografila-descricao-listaespecimenes-view.php'  metho='post' >
<input type='hidden' name='tbname'  value='".$tbname."' > 
<input type='hidden' name='monografiaid'  value='".$monografiaid."' >
<input type='hidden' name='oldtaxanome'  value='".$taxanome."' >
<input type='hidden' name='prepared'  value='1' > 
<div>
<select style=\"color:#000000; font-size: 1em; font-weight:bold; padding: 4px; cursor:pointer;\" name='taxanome' onchange='javascript: this.form.submit();' >";
if (empty($taxanome)) {
		echo "
<option selected  value=''>Selecione um táxon</option>";
}
	$sql = "SELECT DISTINCT taxanome FROM ".$tbname;
	$rsql = mysql_query($sql,$conn);
	$resultado = '';
	while($rsqlw = mysql_fetch_assoc($rsql)) {
		if ($taxanome==$rsqlw['taxanome']) {
			$txt = 'selected';
		} else {
			$txt = '';
		}
		echo "
<option ".$txt."  value='".$rsqlw['taxanome']."'>".$rsqlw['taxanome']."</option>";
	}
echo "
</select>
&nbsp;<input type='button' value='Fechar Janela' onclick='javascript:  window.close();'  style=\"color:#4E889C; font-size: 1em; font-weight:bold; padding: 4px; cursor:pointer;\" />
</div>
<br />
<div class=\"tools\" >
<span class='subs'>Definições de pontuação do modelo</span>
<br />
<div class=\"variaveis\" >
<table cellpadding=\"5px\" >
<tr>
<td align='right' class='tdsmallbold' >Casas decimais&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
	$help = "Número de casas decimais das variáveis numéricas  latitude e longitude";
	echo "onclick=\"javascript:alert('$help');\" /></td><td align='left' ><input type='text' size='10'  style='font-size: 1.2em;'  name='decimals'  value='".$decimals."' ></td>
<td align='right' class='tdsmallbold' >Formato do output&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
	$help = "A descrição é gerada em formato HTML ou latex";
	echo "onclick=\"javascript:alert('$help');\" /></td><td align='left' >";
if ($outtype=='html') {
	$txt = 'selected';
} else {
	$txt2 = 'selected';
}
echo "
<select style=\"color:#000000; font-size: 1.2em; padding: 4px; cursor:pointer;\"  name='outtype' onchange='javascript: this.form.submit();'>
<option ".$txt."  value='html' >html</option>
<option ".$txt2."  value='latex' >latex</option>
</select></td>
<td align='right' class='tdsmallbold' >Após&nbsp;amostras&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
	$help = "Pontuação a ser colocada após cada amostra";
	echo "onclick=\"javascript:alert('$help');\" /></td><td align='left' ><input type='text' name='specimenseparator'  value='".$specimenseparator."' size='10'  style='font-size: 1.2em;' /></td>
</tr>
<tr>
<td align='right' class='tdsmallbold' >Após&nbsp;país<img height=15 src=\"icons/icon_question.gif\" ";
	$help = "Pontuacao após cada país";
	echo "onclick=\"javascript:alert('$help');\" /></td><td align='left' ><input type='text' name='countryseparator'  value='".$countryseparator."' size='10'  style='font-size: 1.2em;' /></td>
<td align='right' class='tdsmallbold' >Após&nbsp;majorarea<img height=15 src=\"icons/icon_question.gif\" ";
	$help = "Pontuacao após majorarea/provincia";
	echo "onclick=\"javascript:alert('$help');\" /></td><td align='left' ><input type='text' name='provinceseparator'  value='".$provinceseparator."' size='10'  style='font-size: 1.2em;' /></td>
<td align='right' class='tdsmallbold' >Após&nbsp;minorarea<img height=15 src=\"icons/icon_question.gif\" ";
	$help = "Pontuacao após cada minorarea/municipio";
	echo "onclick=\"javascript:alert('$help');\" /></td><td align='left' ><input type='text' name='municipioseparator'  value='".$municipioseparator."' size='10'  style='font-size: 1.2em;' /></td> 
</tr>
<tr>
<td align='right' class='tdsmallbold' >Após&nbsp;localidade<img height=15 src=\"icons/icon_question.gif\" ";
	$help = "Pontuacao após cada localidade";
	echo "onclick=\"javascript:alert('$help');\" /></td><td align='left' ><input type='text' name='gazetteerseparator'  value='".$gazetteerseparator."' size='10'  style='font-size: 1.2em;' /></td>
<td align='right' class='tdsmallbold' >Após&nbsp;variáveis<img height=15 src=\"icons/icon_question.gif\" ";
	$help = "Caracteres colocados depois de cada variável, exceto simbolos";
	echo "onclick=\"javascript:alert('$help');\" /></td><td align='left' ><input type='text' name='variableseparator'  value='".$variableseparator."' size='10'  style='font-size: 1.2em;' /></td>
<td align='right' class='tdsmallbold' >Entre&nbsp;herbaria<img height=15 src=\"icons/icon_question.gif\" ";
	$help = "Caracteres colocados entre as siglas de herbário, se a variável estiver incluida";
	echo "onclick=\"javascript:alert('$help');\" /></td><td align='left' ><input type='text' name='herbariaseparator'  value='".$herbariaseparator."' size='10'  style='font-size: 1.2em;' /></td>
</tr>
</table>
</div>
</div>
";
if (!empty($taxanome)) {
		 $res =  sumarizaLISTA($modeloarr,$tbname, $taxanome, $decimals, $outtype,$modelosimbolos, $countryseparator, $provinceseparator, $municipioseparator,$specimenseparator, $gazetteerseparator, $variableseparator, $herbariaseparator,$conn);
		echo "
<br />
<div style='position: relative; width: 99%;' >
<span class='subs'>Lista de especímenes para ".$taxanome."</span>
<br />
 <div id=\"modelo\">";
 if ( $outtype=='html') {
			echo  $res;
} else {
echo  "<code>".$res."</code>";
}
echo "
</div>
</div>
";
}
echo "
</div>
</form>
";


}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>
