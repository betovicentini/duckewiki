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
extract($ppost);
$arval = $ppost;

$gget = cleangetpost($_GET,$conn);
extract($gget);

//echopre($ppost);
//CABECALHO
$menu = FALSE;
$which_css = array("<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' href='css/jquery-ui.css' />");
$which_java = array(
"<script src=\"javascript/jquery-1.10.2.js\"></script>",
"<script src=\"javascript/jquery-ui.js\"></script>",
"<script> 
function myFunction() {
    var selects = document.getElementsByTagName(\"select\");
    var len = selects.length;
    var message = \"\";
    for ( var i = 0; i<len; ++i ) {
      var kname = selects[i].name;
      var kname = kname.replace(\"mesmoque\", \"\");
      var kname = kname.replace(\"[\", \"\");
      var kname = kname.replace(\"]\", \"\");
      var kname = kname.replace(\"'\", \"\");
      var kname = kname.replace(\"'\", \"\");
      var runn =  selects[i].options[selects[i].selectedIndex].value;
      if (runn>0) {
         if (message=='') {
            message = \"pessoa_\"+kname+\"=\"+runn;
         } else {
            message += \"&pessoa_\"+kname+\"=\"+runn;
         }
      }
   }
   if (message!='') {
         $.ajax(
            {
                type: 'GET',
                url: 'pessoas-duplicadas-corrige.php',
                data: message,
                async: true,
                success:
                    function (data) {
                           alert(data);
                    }
            });
   } else {
      alert('Você não indicou nenhuma mudança!');
    }
}
</script>"
);
$title = 'Novas pessoas';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if (!isset($idstocheckser)) {
	$qq = "SELECT GROUP_CONCAT(PessoaID SEPARATOR ';') as ids, count(PessoaID) as cnt FROM  Pessoas 
GROUP BY CONCAT(UPPER(SUBSTRING(Prenome,1,1)),UPPER(acentostosemacentos(Sobrenome))) HAVING cnt>1";
	$res = mysql_query($qq,$conn);
	$idstocheck = array();
	while($row = mysql_fetch_assoc($res)) {
		$idstocheck[] = $row['ids'];
	}
} else {
	$idstocheck = unserialize($idstocheckser);
	///CORRIGI SE FOR O CASO
	//echopre($ppost);
	if ($final<3) {
		unset($idstocheck[0]);
		$idstocheck = array_values($idstocheck);
	}
}
$currid = $idstocheck[0];
$curids = explode(";",$currid);
$nc = count($curids);
echo "
<form action='pessoas-duplicadas.php' method='post' name='coletaform' >
<input type='hidden'  name='idstocheckser'  value='".serialize($idstocheck)."' >
<table align='center' cellpadding='7' class='myformtable'>
<thead>
<tr><td colspan='3'>".$nc." pessoas parecem ser a mesma</td></tr>
<tr class='subhead'><td>Nome cadastrado</td><td colspan=2>Substituir por</td></tr>
</thead>
<tbody>";
if (count($curids)>0) {
$ii=0;
foreach ($curids as $cid) {
	$cid = $cid+0;
	if ($cid>0) {
	$q = "SELECT * FROM Pessoas WHERE PessoaID=".$cid;
	$m = mysql_query($q,$conn);
	$mn = mysql_numrows($m);
	if ($mn>0) {
		$r = mysql_fetch_assoc($m);
		$nome = trim($r['Prenome']." ".$r['SegundoNome']);
		$nome .= " ".$r['Sobrenome'];
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else { $bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'><td>".$nome." [".$r['Abreviacao']."] </td>
<td colspan=2>
<select name=\"mesmoque[".$cid."]\" >
<option value='' >Selecione o nome válido</option>";
foreach ($curids as $cid2) {
	$qp = "SELECT * FROM Pessoas WHERE PessoaID=".$cid2;
	$mp = mysql_query($qp,$conn);
	$mnp = mysql_numrows($mp);
	if ($mnp>0) {
	$rp = mysql_fetch_assoc($mp);
	$nome = trim($rp['Prenome']." ".$rp['SegundoNome']);
	echo "
<option value='".$rp['PessoaID']."' >".$nome." [".$rp['Abreviacao']."]</option>";
	} 
}
echo "
</select>
</td></tr>
";
	} else {
echo "
<tr bgcolor = '".$bgcolor."'><td>".$cid." [PessoaID não encontrado] </td></tr>";
	}
	$ii++;
	}
}
}
if ($ii>0) {
echo "
<tr><td align='center'>
<input type='hidden' name='final' value='' />
<input style='cursor: pointer'  type='submit' value='Não são os mesmos - pular' class='bsubmit' onclick=\"javascript:document.coletaform.final.value='1'\" /></td>
<td align='center'>
<input style='cursor: pointer'  type='button' value='Salvar mudanças indicadas' class='bblue' onclick=\"javascript: myFunction();\" /></td>
<td>
<input style='cursor: pointer'  type='submit' value='Refresh' class='bsubmit' onclick=\"javascript:document.coletaform.final.value='3'\" /></td>
</tr>
";
} else {
echo "
<tr><td align='center' colspan='3'>
<input style='cursor: pointer'  type='button' value='Fechar nada mais encontrado!' class='bsubmit' onclick=\"javascript:window.close();\" /></td>
</tr>";
}
echo "
</table>
</form>
<br />";


$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>