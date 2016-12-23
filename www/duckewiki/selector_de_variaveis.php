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
  #fitrable3 { list-style-type: none;  margin: 2px; background: #ffffff; padding: 5px; width: 300px; height:20px; overflow: none; }
  #fitrable1 li {
  margin: 1px; 
  padding: 1px; 
  font-size: 0.9em; 
  color: #363636;
  cursor: move;
  border: solid 1px grey;
  border-radius:5px; -webkit-border-radius:5px; -moz-border-radius:5px;
  }
  #fitrable2 li {
  margin: 1px; 
  padding: 1px; 
  font-size: 0.9em; 
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
    $( '#resultadotxt' ).val( fimtxt );
    var el = self.opener.window.document.getElementById('".$spanid."');
    el.innerHTML = fim.length + ' variáveis selecionadas ';
    window.close();
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
    varparenttxt = self.opener.window.document.forms[formName].elements[tagtxt];
    varparenttxt.innerHTML =  document.forms['finalform'].elements['resultadotxt'].value;
    varparent.value =  document.forms['finalform'].elements['resultado'].value;
    window.close();
    }    
</script>"
);
//    //SetValueInParent('".$formname."','".$valuevar."','".$valuetxt."');
    //window.close();

//var nselvars = $('#fitrable2 li');
//var total = nselvars.length;
//$('#count').html(' Total selecionado: ' + total);
$body='';
$title = 'Seletor de variáveis';
FazHeader($title,$body,$which_css,$which_java,$menu);

//echo "<script type='text/javascript' src='javascript/wz_tooltip.js'></script>";

echo "
<table class='tableform' align='center' cellpadding=\"7\">
  <tr class='tabhead'>
    <td >Variáveis disponíveis</td>
    <td ></td>    
    <td >Variáveis selecionadas</td>
  </tr>
  <tr>
<td >
<div style='align: center;'>
Fitrar:&nbsp;&nbsp;<input type='text' id='filtralista'  style='height: 20px; width:260px' />
<br />
<ul  id='fitrable1' class='droptrue' >";
		$filtro ="SELECT * FROM `Traits` WHERE `TraitName`<>'' AND TraitTipo<>'Estado' AND TraitTipo<>'Classe' AND TraitTipo NOT LIKE '%text%' AND TraitTipo NOT LIKE '%Imag%' ORDER BY `PathName` ASC";
		$res = mysql_query($filtro,$conn);
		while ($aa = mysql_fetch_assoc($res)){
			$PathName = $aa['PathName'];
			$level = $aa['MenuLevel'];
			$tipo = $aa['TraitTipo'];
			if ($level==1) {
				//$espaco='';
			} else {
				//$espaco = str_repeat('&nbsp;&nbsp;&nbsp;',$level);
			}
			if ($tipo=='Classe') { //if is a class or a state does not allow selection
			} 
			else {
				//$espaco = $espaco.str_repeat('- ',$level-1);
				$tp = explode("|",$tipo);
				if ($tp[1]=='Categoria') {
					$qu = "SELECT * FROM `Traits` WHERE `ParentID`='".$aa['TraitID']."'";
					$ru = mysql_query($qu,$conn);
					$ncat = mysql_numrows($ru);
					$std = array();
					while ($rwu = mysql_fetch_assoc($ru)) {
						$std[] = mb_strtolower($rwu['TraitName']);
					}
					$stads = implode("; ",$std);
					$stlen = strlen($stads);
					if ($stlen>30) {
						$stt = substr($stads,0,50);
						$stt = $stt."....";
					} else {
						$stt = $stads;
					}
					$nn = $aa['PathName']." [Categorias: $stt]";
					$bgcol = "#99CCFF";
				echo "
        <li class='dalista' style='background: ".$bgcol.";' data-value='".$aa['TraitID']."|".$tp[1]."' >".$espaco.$nn."<input type='checkbox' data-value='ul".$aa['TraitID']."');\" /><ul hidden id='ul".$aa['TraitID']."' style='background-color: orange; height: 50px'></ul></li>";
				} 
				else {
					$nn = $aa['PathName']." [".$tp[1]."]";
					if ($tp[1]=='Quantitativo') {
						$bgcol = "#D6FFD6";
					}
					if ($tp[1]=='Texto') {
						$bgcol = "#D3D3D3";
					}
					if ($tp[1]=='Imagem') {
						$bgcol = "#FFE4E1";
					}
				echo "
        <li class='dalista' style='background: ".$bgcol.";' data-value='".$aa['TraitID']."|".$tp[1]."' >".$espaco.$nn."</li>";
				}
				
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
		if (count($arrayoftraists)>0) {
			foreach ($arrayoftraists as $thetrait) {
				$val = $thetrait+0;
				$qq = "SELECT * FROM `Traits` WHERE `TraitID`='".$val."'";
				$rr = mysql_query($qq,$conn);
				$rwt = mysql_fetch_assoc($rr);
				echo "
          <li data-value='".$aa['TraitID']."|".$tp[1]."'>".$rwt['PathName']."</li>";
			}
} 
echo "
</ul>
</div>
    </td>
  </tr>
<tr>
    <td colspan='2' align='center'></td>
    <td>
    <div>
    <ul  id='fitrable3'  class='droptrue' >
    <li data-value='groupby'>Agrupar por</li>
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
<input type='text' id='resultado'  size=100/>
<input type='text' id='resultadotxt' />
</form>
";

$which_java = array(
//"<script type='text/javascript' src='javascript/myjavascripts.js'></script>"
);
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>