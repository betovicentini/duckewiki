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
$menu = FALSE;
$title = '';

if (empty($valuevar)) { $valuevar = 'addcolvalue';}
if (empty($valuetxt)) { $valuetxt = 'addcoltxt';}
if (empty($formname)) { $formname = 'coletaform';}
if (!empty($userids)) {
	$arrayofuserids = explode(";",$userids);
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link href='css/jquery-ui.css' rel='stylesheet' type='text/css' />",
"<style type='text/css'>
  #fitrable1, #fitrable2 { list-style-type: none;  margin: 2px; background: #ffffff; padding: 5px; width: 300px; height:200px; overflow: auto; }
  #fitrable3 { list-style-type: none;  margin: 2px; background: #ffffff; padding: 5px; width: 300px; height:20px; overflow: none; }
  #fitrable1 li {
  margin: 1px; 
  padding: 1px; 
  font-size: 0.9em; 
  background-color: yellow;
  color: #363636;
  cursor: move;
  border: solid 1px grey;
  border-radius:5px; -webkit-border-radius:5px; -moz-border-radius:5px;
  }
  #fitrable2 li {
  margin: 1px; 
  padding: 1px; 
  font-size: 0.9em; 
  background-color: #87CEEB;
  color: #363636;
  cursor: move;
  border: solid 1px grey;
  border-radius:5px; -webkit-border-radius:5px; -moz-border-radius:5px;
  }
  #fitrable3 li {
  margin: 1px; 
  padding: 1px; 
  font-size: 0.9em; 
  color: #363636;
  cursor: move;
  border: solid 1px grey;
  border-radius:5px; -webkit-border-radius:5px; -moz-border-radius:5px;
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
            fim += $( this ).attr('data-value');
            fimtxt += $( this ).text();
        } else {
            fim +=  '\\; ' + $( this ).attr('data-value');
            fimtxt += '\\; ' + $( this ).text();
        }
        count++;
    });
    $( '#resultado' ).val( fim );
    $( '#resultadotxt' ).val( (count-1) + ' usuários selecionadas ' );
    SetValueInParent('".$formname."','".$valuevar."','".$valuetxt."');
});
function hideul(theid) {
  $( '#' + theid).toggle();
}


});
</script>",
"<script type='text/javascript' >
    function SetValueInParent(formName,tagvalue,tagtxt)
    {
    varparent = self.opener.window.document.forms[formName].elements[tagvalue];
    var varparenttxt = self.opener.window.document.getElementById(tagtxt);
    varparenttxt.innerHTML =  document.forms['terminaform'].elements['resultadotxt'].value;
    var temp = document.forms['terminaform'].elements['resultado'].value;
    varparent.value =  document.forms['terminaform'].elements['resultado'].value;
    window.close();
    }    
</script>"
);
$body='';
$title = 'Seletor de usuários';
FazHeader($title,$body,$which_css,$which_java,$menu);

//echo "<script type='text/javascript' src='javascript/wz_tooltip.js'></script>";

echo "
<table class='tableform' align='left' cellpadding=\"7\">
  <tr class='tabhead'>
    <td >Usuários disponíveis</td>
    <td ></td>    
    <td >Usuários selecionados</td>
  </tr>
  <tr>
<td >
<div style='align: center;'>
Fitrar:&nbsp;&nbsp;<input type='text' id='filtralista'  style='height: 20px; width:260px' />
<br />
<ul  id='fitrable1' class='droptrue' >";
		$filtro ="SELECT * FROM `Users` ORDER BY `FirstName`,`LastName` ASC";
		$res = mysql_query($filtro,$conn);
		while ($aa = mysql_fetch_assoc($res)){
				$nn = $aa['FirstName']." ".$aa['LastName']." [".$aa['Login']."]";
				if (!in_array($aa['UserID'],$arrayofuserids)) {
				echo "
        <li data-value='".$aa['UserID']."' >".$nn."</li>";
		        }
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
Fitrar:&nbsp;&nbsp;<input type='text' id='filtralista2'  style='height: 20px; width:260px' />
<br />
<ul  id='fitrable2' class='droptrue' >";
		if (count($arrayofuserids)>0) {
			foreach ($arrayofuserids as $thetrait) {
				$filtro ="SELECT * FROM `Users` WHERE UserID=".$thetrait;
				$res = mysql_query($filtro,$conn);
				$aa = mysql_fetch_assoc($res);
				$nn = $aa['FirstName']." ".$aa['LastName']." [".$aa['Login']."]";
				echo "
        <li data-value='".$aa['UserID']."' >".$nn."</li>";
		}
	} 
echo "
</ul>
</div>
    </td>
  </tr>
  <tr>
    <td colspan='3' align='center'>
      <br />
      <input type='button' id='botao' style='cursor: pointer;' value='".GetLangVar('nameenviar')."' class='bsubmit'  />
      &nbsp;
      <input type='button' style='cursor: pointer;' value='".GetLangVar('namefechar')."' class='bblue'  onclick='javascript: window.close();' />
    </td>
  </tr>
</table>
<form name='terminaform' method='post' >
<input type='hidden' id='resultado' /><br ><br>
<input type='hidden' id='resultadotxt' />
</form>
";
$which_java = array();
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>