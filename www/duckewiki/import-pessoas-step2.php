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
$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' />"
);
$which_java = array(
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>"
);
$title = 'Importar Pessoas passo 02';
$body = '';

$vars = unserialize($_SESSION['destvararray']);
@extract($vars);
$cln = $tbprefix."PessoaID";

//SE EM $cln FOI INCLUIDO 'ERRO' SIGNIFICA QUE TEM + DE UM NOME PARECIDO NA BASE DE DADOS
//SE EM $cln FOR 0 SIGNIFICA QUE NAO TEM NADA PARECIDO E DEVE SER IMPORTADO COMO NOVO
//SE EM $cln HOUVER 1 NUMERO, ESTE É O PessoaID DO BANCO DE DADOS

$qq = "SELECT DISTINCT  `".$abreviacao."`,`".$prenome."`,`".$sobrenome."` FROM `".$tbname."`";
//echo $qq."<br />";
$rq = @mysql_query($qq,$conn);
$rqn0 = @mysql_numrows($rq);


$qq = "SELECT DISTINCT  `".$abreviacao."`,`".$prenome."`,`".$sobrenome."` FROM `".$tbname."` WHERE (`".$cln."`+0)>0";
$rq = @mysql_query($qq,$conn);
$rqn2 = @mysql_numrows($rq);
//echo $qq."<br />";

$qq = "SELECT DISTINCT  `".$abreviacao."`,`".$prenome."`,`".$sobrenome."` FROM `".$tbname."` WHERE (`".$cln."`+0)=0";
$rq = @mysql_query($qq,$conn);
$rqn3 = @mysql_numrows($rq);
//echo $qq."<br />";

$qq = "SELECT DISTINCT  `".$abreviacao."`,`".$prenome."`,`".$sobrenome."` FROM `".$tbname."` WHERE `".$cln."`='ERRO'";
$rq = @mysql_query($qq,$conn);
$rqn = @mysql_numrows($rq);
//echo $qq."<br />";

FazHeader($title,$body,$which_css,$which_java,$menu);
//$qq = "UPDATE `".$tbname."` SET `".$cln."`=checarpessoaimport(`".$abreviacao."`,`".$prenome."`,`".$sobrenome."`) WHERE `".$cln."`=0";
//mysql_query($qq,$conn);

	//mostra registros similares se houver algum
	echo "
<br />
<table align='left' class='myformtable' cellpadding='5'>
<thead>
  <tr><td colspan='2'>Atenção!</td></tr>
</thead>
<tbody>
<tr bgcolor = '".$bgcolor."'>
    <td colspan=2>".$rqn2."  dos ".$rqn0."  registros já estavam cadastrados no banco de dados</td>
</tr>    
<tr bgcolor = '".$bgcolor."'>
    <td >".$rqn3."  dos ".$rqn0."  registros são novos e serão cadastrados no banco de dados</td>";
if ($rqn3>0) {
echo "
    <td align='center'><input id='butidxcad' type='button' style=\"font-size:90%;background-color:#0066CC; color: white;border: thin outset gray;padding: 0.1em; cursor: pointer\"
 value='Cadastrar' ";
$myurl ="novaspessoas-cadastra.php?colname=".$cln."&abreviacao=".$abreviacao."&prenome=".$prenome."&sobrenome=".$sobrenome."&tbname=".$tbname."&segundonome=".$segundonome."&buttonidx=butidxcad"; 
echo " onclick = \"javascript:small_window('$myurl',800,400,'Cadastra essas novas pessoas');\" /></td>"; 
}  else {
echo "
    <td align='center'></td>";
}  
echo "    
</tr>    
<tr bgcolor = '".$bgcolor."'>
    <td>".$rqn."  dos ".$rqn0."  registros parecem ser pessoas já cadastradas e você precisa checar os valores</td>";
if ($rqn>0) {
echo "
    <td align='center'><input id='butidx' type='button' style=\"font-size:90%;background-color:#0066CC; color: white;border: thin outset gray;padding: 0.1em; cursor: pointer\"
 value='Checar' ";
$myurl ="novaspessoas-popup2.php?colname=".$cln."&abreviacao=".$abreviacao."&prenome=".$prenome."&sobrenome=".$sobrenome."&tbname=".$tbname."&segundonome=".$segundonome."&buttonidx=butidx"; 
echo " onclick = \"javascript:small_window('$myurl',800,400,'Corrigir valores de nomes de pessoas');\" /></td>"; 
} 
else {
echo "
    <td align='center'></td>";
}
echo "
</tr>";
if ($rqn2<$rqn0) {
echo "
  <tr bgcolor = '".$bgcolor."'><td align='center' colspan='2'>";
echo "
<form action='import-pessoas-step2.php' method='post'>";
foreach ($vars as $kk => $vv) {
	if (!empty($vv)) {
		echo "
  <input type='hidden' name='".$kk."' value='".$vv."' />"; 
		}
	}  
echo "<input type='submit' value='".GetLangVar('namecontinuar')."' class='bsubmit' />
    </form>
  </td>
  </tr>
";
} else {
	$qq = "SELECT tb.*,tb2.Abreviacao as Wiki_Abreviacao FROM `".$tbname."` as tb LEFT JOIN Pessoas as tb2 ON  tb.`".$cln."`=tb2.PessoaID";
	$rq = mysql_query($qq,$conn);
	$export_filename = $tbname.".csv";
	$fh = fopen("temp/".$export_filename, 'w') or die("nao foi possivel gerar o arquivo");
	$count = mysql_num_fields($rq);
	$header = '';
	for ($i = 0; $i < $count; $i++){
		if ($i<($count-1)) {
			$header .=  '"'. mysql_field_name($rq, $i).'"'."\t";
		} else {
			$header .=  '"'. mysql_field_name($rq, $i).'"';
		}
	}
	$header .= "\n";
	fwrite($fh, $header);
	while($rsw = mysql_fetch_assoc($rq)){
		$line = '';
		foreach($rsw as $value){
			if(!isset($value) || $value == ""){
				$value = "\t";
			} else{
				$value = str_replace('"', '""', $value);
				$value = '"' . $value . '"' . "\t";
			}
			$line .= $value;
		}
		$lin = trim($line)."\n";
		fwrite($fh, $lin);
	}
	fclose($fh);
echo "
<tr bgcolor = '".$bgcolor."'>
<td><a href=\"download.php?file=temp/".$export_filename."\">Seu arquivo corrigido</a></td>
<td align='left' >
<input type='button' value='Fechar' class='bsubmit' onclick=\"javascript: window.close();\" />
</td>
</tr>
";
}
echo "

</tbody>
</table>";
 

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>
