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
$title = '';

if (empty($valuevar)) { $valuevar = 'addcolvalue';}
if (empty($valuetxt)) { $valuetxt = 'addcoltxt';}
if (empty($formname)) { $formname = 'coletaform';}

$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link href='css/jquery-ui.css' rel='stylesheet' type='text/css' />",
"<style type='text/css'>
  #fitrable1, #fitrable2 { list-style-type: none;  margin: 2px; background: #ffffff; padding: 5px; width: 300px; height:200px; overflow: auto; }
  #fitrable1 li {
  margin: 1px; 
  padding: 3px; 
  font-size: 0.9em; 
  border: 1px solid #fcefa1;
  background: #fbf9ee;
  color: #363636;
  cursor: move;
  }
  #fitrable2 li {
  margin: 1px; 
  padding: 3px; 
  font-size: 0.9em; 
  border: 1px solid #99FF66;
  background: #CCFFCC;
  color: #363636;
  cursor: move;
  }
</style>"
);
$which_java = array(
"<script type='text/javascript' src=\"javascript/jquery-1.10.2.js\"></script>",
"<script type='text/javascript' src=\"javascript/jquery-ui.js\"></script>",
"<script  type='text/javascript' >
$(function() {
$.extend($.expr[':'], {
'containsIN': function(elem, i, match, array) {
return (elem.textContent || elem.innerText || '').toLowerCase().indexOf((match[3] || '').toLowerCase()) >= 0;
}
});
$('#filtralista').keyup(function(){
        var filtro = $(this).val();
        $('#fitrable1 li').not(':containsIN(\"'+filtro+'\")').each(function(){
                $(this).hide(); 
        });
       $('#fitrable1 li:containsIN(\"'+filtro+'\")').each(function(){
                $(this).show(); 
        });
});
$('#filtralista2').keyup(function(){
        var filtro2 = $(this).val();
        $('#fitrable2 li').not(':containsIN(\"'+filtro2+'\")').each(function(){
                $(this).hide(); 
        });
       $('#fitrable2 li:containsIN(\"'+filtro2+'\")').each(function(){
                $(this).show(); 
        });
});
});
</script>",
"<script  type='text/javascript' >
$(function() {
    $( 'ul.droptrue' ).sortable({
      connectWith: 'ul'
    });
    $( '#sortable1, #sortable2' ).disableSelection();
  });
</script>",
"<script  type='text/javascript' >
$(function() {
$( '#botao' ).click(function() {
    var fim = '';
    var fimtxt  = '';
    var count = 1;
    $('#fitrable2 > li').each(function() {
        if (count==1) {
            fim += $( this ).val();
            fimtxt += $( this ).text();
        } else {
            fim +=  '\\; ' + $( this ).val();
            fimtxt += '\\; ' + $( this ).text();
        }
        count++;
    });
    $( '#resultado' ).val( fim );
    $( '#resultadotxt' ).val( fimtxt );
    varparent = self.opener.window.document.forms['".$formname."'].elements['".$valuevar."'];
    varparenttxt = self.opener.window.document.forms['".$formname."'].elements['".$valuetxt."'];
    varparenttxt.innerHTML =  document.forms['finalform'].elements['resultadotxt'].value;
    varparent.value =  document.forms['finalform'].elements['resultado'].value;
    window.close();
});
});
</script>",
"<script  type='text/javascript' >
function getcurval() {
    varparent = self.opener.window.document.forms['".$formname."'].elements['".$valuevar."'].value;
    varparenttxt = self.opener.window.document.forms['".$formname."'].elements['".$valuetxt."'].innerHTML;
    var pararr = varparent.split(';');
    var eln = pararr.length;
    var parartxt = varparenttxt.split(';');
    if (eln>0 & pararr[0]!='')  {
    for (i=0;i<eln;i++) {
        $('#fitrable2').append('<li value=\"' + pararr[i] + '\" >'+parartxt[i]+'</li>');
        $('#fitrable1 > li[value='+pararr[i]+']').remove();
    }
    }
}
</script>"
);

//    SetValueInParent('".$formname."','".$valuevar."','".$valuetxt."');
//    
//var nselvars = $('#fitrable2 li');
//var total = nselvars.length;
//$('#count').html(' Total selecionado: ' + total);
$body= " onload='getcurval();' ";
$title = GetLangVar('namecoletor');
FazHeader($title,$body,$which_css,$which_java,$menu);

echo "<script type='text/javascript' src='javascript/wz_tooltip.js'></script>";

echo "

<table class='tableform' align='center' cellpadding=\"7\">
  <tr class='tabhead'>
    <td >".GetLangVar('namedisponivel')."</td>
    <td ></td>    
    <td >".GetLangVar('nameselecionado')."</td>
  </tr>
  <tr>
<td >

<div style='align: center;'>
<!---
Filtrar:&nbsp;&nbsp;<input type='text' id='filtralista'  style='height: 20px; width:260px' />
<br />
--->
<ul  id='fitrable1' class='droptrue' >";
$rrr = getpessoa('',$abb=TRUE,$conn);
while ($row = mysql_fetch_assoc($rrr)) {
echo "
  <li value='".$row['PessoaID']."'>".$row['Abreviacao']."  [".$row['Prenome']."]</li>";
	}
echo "
</ul>
</div>
</td>
<td>
<img src='icons/drag_all.png' width='50px' onmouseover=\"Tip('Arraste os Nomes entre as listas e ordene dentro de Selecionado como quiser!');\"/>
</td>
<td >
<div style='align: center;'>
<span style=\"color: red; font-size: 1em;\">
Indique o Coletor Padrão como o primeiro da lista.
</span>
<!---
Filtrar:&nbsp;&nbsp;<input type='text' id='filtralista2'  style='height: 20px; width:260px' />
<br />
--->
<ul  id='fitrable2' class='droptrue' >";
echo "
</ul>
</div>
    </td>
  </tr>
  <tr>
    <td colspan='3' align='center'>
      <br />
      <input type='button' id='botao' style='cursor: pointer;' value='".GetLangVar('nameenviar')."' class='bsubmit' />
      &nbsp;
      <input type='button' style='cursor: pointer;' value='".GetLangVar('namefechar')."' class='bblue'  onclick='javascript: window.close();' />
    </td>
  </tr>
</table>
<form name='finalform'  action='ScriptTeste.php' method='post' >
<input type='hidden' id='resultado' />
<input type='hidden' id='resultadotxt' />
</form>
";

$which_java = array(
//"<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>
