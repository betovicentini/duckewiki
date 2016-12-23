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

//GERA A TABELA SE ELA NAO EXISTE E PEGA DADOS DA MONOGRAFIA
if ($monografiaid>0) {
	$qq = "SELECT * FROM Monografias WHERE MonografiaID='".$monografiaid."'";
	$res = mysql_query($qq,$conn);
	$rr = mysql_fetch_assoc($res);
	$modelo= $rr['ModeloDescricoes'];
	$simbolos= explode("SIMBOLO",$rr['ModeloSimbolos']);
	$modeloarr = json_decode($modelo);
	//echopre ($modeloarr);
	//$mmodel = printModelo($modeloarr,$conn);
	
	//GERA A TABELA DE DADOS PARA A MONOGRAFIA
	if (!isset($prepared)) {
		$tbname = 'temp_monografiavariation_'.$monografiaid.'_'.$uuid;
		$criatb  = criaTabelaParaDescrever($monografiaid,$tbname,$unidade='mm',$includetaxa=FALSE,$conn);
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
	$tbname = 'temp_monografiavariation_'.$monografiaid.'_'.$uuid;
	$decimals = 1;
	$theunit = 'mm';
	$includeN = 'on';
	$outtype = 'html';
	$withigroupseparator = ', ';
	$groupseparator = '. ';
	$groupclassseparator = "; ";
	$groupclassseinitial = " ";
}

if ($erro==0) {
echo "
<form action='monografila-descricao-view.php'  metho='post' >
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
&nbsp;&nbsp;<input type='button' value='Fechar Janela' onclick='javascript:  window.close();'  style=\"color:#4E889C; font-size: 1em; font-weight:bold; padding: 4px; cursor:pointer;\" />
</div>
<br />
<div class=\"tools\" >
<span class='subs'>Definições de pontuação do modelo</span>
<br />
<div class=\"variaveis\" >
<table cellpadding=\"7px\" >
<tr>
<td align='right' class='tdsmallbold' >Casas decimais&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
	$help = "Número de casas decimais das variáveis numéricas";
	echo "onclick=\"javascript:alert('$help');\" /></td><td align='left' ><input type='text' size='10'  style='font-size: 1.2em;'  name='decimals'  value='".$decimals."' ></td>
<td align='right' class='tdsmallbold' >Unidade de medida&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
$help = "Unidade de medida para a descrição. Converte os valores padronizando a descrição";
echo "onclick=\"javascript:alert('$help');\" /></td><td align='left' ><select style=\"color:#000000; font-size: 1em; padding: 4px; cursor:pointer;\"  name='theunit' onchange='javascript: this.form.submit();'>";
if ($theunit =='mm') {
	$txt = 'selected';
} else {
	$txt = '';
}
echo "
<option ".$txt."  value='mm'>mm</option>";
if ($theunit =='cm') {
	$txt = 'selected';
} else {
	$txt = '';
}
echo "
<option ".$txt."  value='cm'>cm</option>";
if ($theunit =='m') {
	$txt = 'selected';
} else {
	$txt = '';
}
echo "
<option ".$txt."  value='m'>m</option>";
echo "
</select></td>";
if ($includeN=='on') {
	$txt = 'checked';
} else {
	$txt = '';
}
echo "
</tr>
<tr>
<td align='right' class='tdsmallbold' >Inclui N?&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
	$help = "Inclui o N amostral ao final de cada variável, numérica ou categórica. FIXME: isso deve ser transferido para o a definição de cada variável no modelo!";
	echo "onclick=\"javascript:alert('$help');\" /></td><td align='left' ><input type='checkbox' name='includeN' ".$txt." value='".$includeN."' ></td>
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
</tr>
<tr>
<td align='right' class='tdsmallbold' >Separador&nbsp;de&nbsp;variáveis&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
	$help = "Pontuação a ser colocada entre cada variável, excluindo aquelas que no modelo estão agrupadas dentro de uma variável categórica. A pontuação não é colocada se o item do modelo for um texto ou um símbolo, apenas se for uma variável";
	echo "onclick=\"javascript:alert('$help');\" /></td><td align='left' ><input type='text' name='groupseparator'  value='".$groupseparator."' size='10'  style='font-size: 1.2em;' /></td>
<td align='right' class='tdsmallbold' >Separador&nbsp;de&nbsp;variáveis&nbsp;agrupadas&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
	$help = "Pontuação a ser colocada APENAS entre as variáveis que no modelo estão agrupadas dentro de uma variável categórica. A pontuação não é colocada se o item do modelo for um texto ou um símbolo, apenas se for uma variável";
	echo "onclick=\"javascript:alert('$help');\" /></td><td align='left' ><input type='text' name='withigroupseparator'  value='".$withigroupseparator."' size='10'  style='font-size: 1.2em;' /></td>
</tr>
<tr>
<td align='right' class='tdsmallbold' >Separador&nbsp;das&nbsp;variáveis&nbsp;nos&nbsp;grupos/categoria&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
	$help = "Pontuação a ser colocada entre as descrições de cada conjunto de variáveis agrupadas em uma variável categórica, separando a variação por categoria. A pontuação não é colocada se o item do modelo for um texto ou um símbolo, apenas se for uma variável";
	echo "onclick=\"javascript:alert('$help');\" /></td><td align='left' ><input type='text' name='groupclassseparator'  value='".$groupclassseparator."' size='10'  style='font-size: 1.2em;'/></td> 
<td align='right' class='tdsmallbold' >Separador&nbsp;dos&nbsp;grupos&nbsp;por&nbsp;categoria&nbsp;<img height=15 src=\"icons/icon_question.gif\" ";
	$help = "Pontuação a ser colocada ao final da categoria, em variáveis agrupadas";
	echo "onclick=\"javascript:alert('$help');\" /></td><td align='left' ><input type='text' name='groupclassseinitial'  value='".$groupclassseinitial."' size='10'  style='font-size: 1.2em;' /></td> 
</tr>
</table>
</div>
</div>
";
if (!empty($taxanome)) {
		if ($includeN=='on') { $includeN=TRUE;}
		$res = sumarizaVALORES($modeloarr,$tbname, $taxanome, $decimals,$theunit, $includeN, $outtype,$simbolos, $withigroupseparator, $groupseparator, $groupclassseparator, $language, $groupclassseinitial,$conn);
		echo "
<br />
<div style='position: relative; width: 99%;' >
<span class='subs'>Descrição ".$taxanome."</span>&nbsp;<span style='font-size: 0.8em;'></span>
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
</div>
</form>
";
}

}

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>
