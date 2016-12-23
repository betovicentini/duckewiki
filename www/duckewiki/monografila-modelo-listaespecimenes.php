<?php
//Start session
session_start();
//INCLUI FUNCOES PHP E VARIAVEIS
include "functions/HeaderFooter.php";
include "functions/MyPhpFunctions.php";
//include "bibtex2html.php";
require_once "functions/DescricaoModelo.php";


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

//PEGA O MODELO ANTIGO SE HOUVER
	$arrayoffields = array(
'coletorid' => 'Coletor',
'number' => 'Número de coleta',
'datacol' => 'Data de coleta',
'herbaria' => 'Lista de herbários',
'country' => 'País',
'majorarea' => 'Província/Estado',
'minorarea' => 'Municipio',
'pargazetteer' => 'Localidade de nível 1',
'gazetteer' => 'Localidade detalhada',
'longitude' => 'Longitude',
'latitude' => 'Latitude');

if ($monografiaid>0) {
	$qq = "SELECT * FROM Monografias WHERE MonografiaID='".$monografiaid."'";
	$res = mysql_query($qq,$conn);
	$rr = mysql_fetch_assoc($res);
	$modelo= $rr['ModeloListaEspecimenes'];
	$modelosimbolos= explode("SIMBOLO",$rr['ModeloSimbolosEspecimenes']);
	$simbpadrao1 = $modelosimbolos[0];
	$simbpadrao2 = $modelosimbolos[1];
	$simbpadrao3 = $modelosimbolos[2];
	$simbpadrao4 = $modelosimbolos[3];
	$simbpadrao5 = $modelosimbolos[4];
	//echo $modelo."<br />";
	$modeloarr = json_decode($modelo);
	//echopre($modeloarr);
	$mmodel = printModeloLista($modeloarr,$arrayoffields,$conn);
} 
//CABECALHO
$menu = FALSE;

$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' >",
"<link rel='stylesheet' type='text/css' href=\"javascript/jQueryTE/jquery-te-1.4.0.css\" />",
"<link rel='stylesheet' type='text/css' href='css/jquery-ui.css' />",
"<style>
.tools {
	top: 1px;
	width: 99%;
	height: 250px;
	padding: 5px;
	border:#cccccc 1px solid;
	border-radius:5px; -webkit-border-radius:5px; -moz-border-radius:5px;
}
.variaveis {
	padding: 3px;
	margin: 3px;
	float: left;
	width: 60%;
}
.textfields {
	padding: 5px;
	margin: 5px;
	width: 30%;
	float: left;
}
.subs {
	padding: 3px;
	align: left;
	font: 0.9em ;
	font-weight: bold;
	color: blue;
}
.ullist {
  overflow-y: scroll;
  height: 120px; 
  margin: 0px; 
  padding: 0px;\"
}

#allfields {
	border:#cccccc 1px solid;
	border-radius:5px; -webkit-border-radius:5px; -moz-border-radius:5px;
	padding: 10px;
}

#modelo {
	border:#cccccc 1px solid;
	border-radius:5px; -webkit-border-radius:5px; -moz-border-radius:5px;
	position: relative;
	height: 300px;
	width: 99%;
	overflow: scroll;
	padding-right: 5px;
}
#modelo ol {
	height: 400px;
}
.subgrupo {
	background-color: yellow;
	overflow: scroll;
	type: a;
	height: 200px;
}
.teste {
	padding:  3px;
	top: 300px;
	position: relative;
	background-color: yellow;

}
#textbox {
	list-style-type: none; 
	font-size: 0.9em;
	padding: 3px;
	border: #cccccc solid 1px;
	border-radius:5px; -webkit-border-radius:5px; -moz-border-radius:5px;
	cursor: move;
}
#textsymbol {
	list-style-type: none; 
	font-size: 0.9em;
	padding: 3px;
	border: #cccccc solid 1px;
	border-radius:5px; -webkit-border-radius:5px; -moz-border-radius:5px;
	cursor: move;
}
.dalista {
	list-style-type: none; 
	font-size: 0.9em;
	padding: 3px;
	border: solid 1px grey;
	border-radius:5px; -webkit-border-radius:5px; -moz-border-radius:5px;
}
</style>"
);
$which_java = array(
"<script src=\"javascript/jquery-1.10.2.js\"></script>",
"<script src=\"javascript/jquery-ui.js\"></script>",
//"<script src=\"javascript/jQueryTE/jquery-te-1.4.0.min.js\"></script>",
"<script src=\"javascript/jQueryTE/uncompressed/jquery-te-1.4.0.js\"></script>",
"<script src=\"javascript/jquery.caret.js\"></script>",
"<script  type='text/javascript' >
$(function() {
$.extend($.expr[':'], {
'containsIN': function(elem, i, match, array) {
return (elem.textContent || elem.innerText || '').toLowerCase().indexOf((match[3] || '').toLowerCase()) >= 0;
}
});
$('#filtralista').keyup(function(){
        var filtro = $(this).val();
        $('#allfields li').not(':containsIN(\"'+filtro+'\")').each(function(){
                $(this).hide(); 
        });
       $('#allfields li:containsIN(\"'+filtro+'\")').each(function(){
                $(this).show(); 
        });
});
});
</script>",
"<script language=\"javascript\" type=\"text/javascript\">
        function meucheck(id, where) {
                var oldval = document.getElementById(where).value;
                var newval =  document.getElementById(id).value;
                var isch = document.getElementById(id).checked;
                if (oldval!='') {
                  arrvals = oldval.split(';');
                  var len = arrvals.length;
                  if (isch) {
                    arrvals[len] = newval;
                    var ress = arrvals.join(';');
                  } else {
                      var resval = Array((len-1));
                      var idx = 0;
                      for (i = 0; i < len; i++) {
                          if (newval!=arrvals[i]) {
                            resval[idx] = arrvals[i];
                            idx++;
                          }
                      }
                      var ress = resval.join(';');
                  }
                } else {
                  var ress = newval;
                }
                document.getElementById(where).value = ress;
        };
        function  mydropfunction( event, ui, thisid ) {
          $('#resultado').html('Editando..');
          //remove o item info se houver
          $( thisid ).find( \".modelo_inicio\" ).remove();
          //pega os atributos do objeto arrastado (variavel id e variavel name)
          var tempid = ui.draggable.attr(\"data-value\");
          //DEFINE AS CORES DAS VARIAVEIS
          var onde = $(thisid).attr(\"data-onde\");
          if (onde=='maingp') {
            var corquant = '#D6FFD6';
            var cortxt = '#FFCCCC';
            var corsym = '#CC99FF';
            var corcat = '#99CCFF';
          }
          //se for uma variavel
          if (tempid!='textbox' && tempid!='textsymbol') {
            var termina=0;
             //PEGA O TIPO E O NOME DA VARIAVEL ARRASTADA
             var traitname = ui.draggable.attr(\"data-text\");
             //var tipo = ui.draggable.attr(\"data-tipo\");
            //VERIFICA SE A VARIAVEL JÁ FOI ARRASTADA ANTERIORMENTE E CRIA UM ID UNICO PARA O OBJETO
             if (onde=='maingp') {
               var tt = $( thisid ).find('div.variavel[data-value='+tempid+']').length;
               if (tt>0) {
                  var termina=1;
               } else {
                 var temppid = tempid;
                 var cls = 'variavel '+tempid;
                 var clss = 'level1';
                 if (tempid=='longitude' || tempid=='latitude') {
               var novoval =  '<div data-value=\"'+tempid+'\"  class=\"'+cls+'\"  style=\"border: solid 1px gray; border-radius: 5px; background-color: '+corcat+'; padding: 3px; margin: 5px 0 5px 0; font-size: 0.8em; cursor: move\" ><span onmouseover=\"Tip(\''+ traitname +'\');\">'+traitname+'</span>&nbsp;<span onmouseover=\"Tip(\'Defina o formato para os valores\');\">[N-formato:<input  type=\"radio\"   value=\"decimaldg\"  class=\"'+clss+'numformat\" name=\"nf'+ temppid +'\">Decimal degree&nbsp;<input  type=\"radio\"   value=\"ddmmss\" class=\"'+clss+'numformat\" name=\"nf'+ temppid +'\">Degree Minutes Seconds]</span>&nbsp;&nbsp;<span onmouseover=\"Tip(\'Defina o formato do texto\');\">[Txt-formato:<input type=\"checkbox\" class=\"'+clss+'negrito\"><b>N</b>&nbsp;<input type=\"checkbox\" class=\"'+clss+'sublinhado\"><u>S</u>&nbsp;<input type=\"checkbox\" class=\"'+clss+'italico\"><i>I</i>]</span>&nbsp;&nbsp;&nbsp;<img src=\"icons/trashcan.png\" height=16 style=\"cursor: pointer\" onclick=\"javascript:removethisli(\''+temppid+'\');\" onmouseover=\"Tip(\'remove o item\');\"></div>'; 
                } else {
                if (tempid=='coletorid') {
                var coletortipo = '&nbsp;<span onmouseover=\"Tip(\'Defina o formato para os valores\');\">[N-formato:<input  type=\"radio\"   value=\"lastname\"  class=\"'+clss+'numformat\" name=\"nf'+ temppid +'\">sobrenome&nbsp;<input  type=\"radio\"   value=\"abreviacao\" class=\"'+clss+'numformat\" name=\"nf'+ temppid +'\">abreviação]</span>&nbsp;';
                } else {
                  var coletortipo = '';
                }
               //DEFINE OS VARIAVEIS
               var novoval =  '<div data-value=\"'+tempid+'\" class=\"'+cls+'\" style=\"border: solid 1px gray; border-radius: 5px; background-color:  '+corquant+'; padding: 3px; margin: 5px 0 5px 0; font-size: 0.8em; cursor: move\" ><span onmouseover=\"Tip(\''+ traitname +'\');\">'+traitname+'</span>&nbsp;'+coletortipo+'<span onmouseover=\"Tip(\'Defina o formato do texto\');\">[Txt-formato:&nbsp;<input type=\"checkbox\" class=\"'+clss+'negrito\"><b>N</b>&nbsp;<input type=\"checkbox\" class=\"'+clss+'sublinhado\"><u>S</u>&nbsp;<input type=\"checkbox\" class=\"'+clss+'italico\"><i>I</i>&nbsp;<input type=\"radio\" class=\"'+clss+'lettercase\" value=\"uppercase\" name=\"cba'+ temppid +'\">CALTA&nbsp;<input type=\"radio\" class=\"'+clss+'lettercase\" value=\"lowercase\" name=\"cba'+ temppid +'\">cbaixa&nbsp;<input type=\"radio\" class=\"'+clss+'lettercase\" value=\"nochange\" name=\"cba'+ temppid +'\">comoEscrito]</span>&nbsp;&nbsp;<img src=\"icons/trashcan.png\" height=16 style=\"cursor: pointer\" onclick=\"javascript:removethisli(\''+temppid+'\');\" onmouseover=\"Tip(\'remove o item\');\"> <span class=\"grupo\" id=\"grupo_'+temppid+'\"></span></div>'; 
                 }
                   var idd = '<li class=\"'+clss+'\" id=\"'+temppid+'\" ></li>';
                 } 
               }
            } else {
            if (tempid=='textbox' && tempid!='textsymbol') {
              if (onde=='maingp') {
                  var tt = $( thisid ).find('div.variavel[data-textid='+tempid+']').length;
                  temppid = 'txt_'+tt;
                  cls = 'variavel textoli';
                  clss = 'level1';
              } 
              var novoval =  '<div data-textid=\"'+tempid+'\"  class=\"'+cls+'\" style=\"padding: 5px; margin: 5px 0 5px 0; border-radius:5px; -webkit-border-radius:5px; -moz-border-radius:5px; background-color:  '+cortxt+'; border: #cccccc solid thin; cursor: move; font-size: 0.8em;\"  onmouseover=\"Tip(\'Caixa para texto livre\');\"><img height=\"16\" src=\"icons/BrasilFlagicon.png\">&nbsp;<input class=\"'+clss+'port\" size=\"60%\" type=\"text\"><br /><img height=\"16\" src=\"icons/usFlagicon.png\">&nbsp;<input class=\"'+clss+'engl\" size=\"60%\" type=\"text\">&nbsp;<span onmouseover=\"Tip(\'Defina o formato do texto\');\">[Txt-formato:&nbsp;<input type=\"checkbox\" class=\"'+clss+'negrito\"><b>N</b>&nbsp;<input type=\"checkbox\" class=\"'+clss+'sublinhado\"><u>S</u>&nbsp;<input type=\"checkbox\" class=\"'+clss+'italico\"><i>I</i>&nbsp;<input type=\"radio\" class=\"'+clss+'lettercase\" value=\"uppercase\" name=\"cba'+ temppid +'\">CALTA&nbsp;<input type=\"radio\" class=\"'+clss+'lettercase\" value=\"lowercase\" name=\"cba'+ temppid +'\">cbaixa&nbsp;<input type=\"radio\" class=\"'+clss+'lettercase\" value=\"nochange\" name=\"cba'+ temppid +'\">comoEscrito]</span>&nbsp;&nbsp;&nbsp;<img src=\"icons/trashcan.png\" height=16 style=\"cursor: pointer\" onclick=\"javascript:removethisli(\''+temppid+'\');\" onmouseover=\"Tip(\'remove o item\');\"></div>';
                  var idd = '<li class=\"'+clss+'\"  id=\"'+temppid+'\" ></li>';
} else {
              if (onde=='maingp') {
                  var tt = $( thisid ).find('div.variavel[data-symbolid='+tempid+']').length;
                  temppid = 'symb_'+tt;
                  cls = 'variavel simbololi';
                  clss = 'level1';
              }
             var novoval =  '<div data-symbolid=\"'+tempid+'\" class=\"'+cls+'\" style=\"padding: 5px; margin: 5px 0 5px 0; border-radius:5px; -webkit-border-radius:5px; -moz-border-radius:5px; background-color:  '+corsym+'; border: #cccccc solid thin; cursor: move; font-size: 0.8em;\"  onmouseover=\"Tip(\'Defina símbolo\');\"><span>Qual símbolo?&nbsp;<input type=\"radio\" class=\"'+clss+'simbolo\" value=1 name=\"symb_'+ temppid +'\">Tipo1&nbsp;<input type=\"radio\" class=\"'+clss+'simbolo\" value=2 name=\"symb_'+ temppid +'\">Tipo2&nbsp;<input type=\"radio\" class=\"'+clss+'simbolo\" value=3 name=\"symb_'+ temppid +'\">Tipo3&nbsp;<input type=\"radio\" class=\"'+clss+'simbolo\" value=4 name=\"symb_'+ temppid +'\">Tipo4&nbsp;<input type=\"radio\" class=\"'+clss+'simbolo\" value=5 name=\"symb_'+ temppid +'\">Tipo5</span>&nbsp;&nbsp;&nbsp;&nbsp;<img src=\"icons/trashcan.png\" height=16 style=\"cursor: pointer\" onclick=\"javascript:removethisli(\''+temppid+'\');\" onmouseover=\"Tip(\'remove o item\');\"></div>';
            var idd = '<li class=\"'+clss+'\"  id=\"'+temppid+'\" ></li>';
            }
        }
            $( idd ).html( novoval ).appendTo( thisid );
        };
        $(function() {
           $('#allfields li').mouseover(function() {
            $( this ).css('cursor', 'move');
            });
            $('#textbox li').draggable({
                appendTo: \"body\",
                helper: \"clone\"
            });
            $('#textsymbol li').draggable({
                appendTo: \"body\",
                helper: \"clone\"
            });
            $('#allfields li').draggable({
                appendTo: \"body\",
                helper: \"clone\"
            });
             $( \"#modelo ol\" ).droppable({
                   greedy: true,
                   activeClass: \"ui-state-default\",
                   hoverClass: \"ui-state-hover\",
                   accept: \"#allfields li, #textbox li, #textsymbol li\",
                   drop: function( event, ui ) { mydropfunction(event,ui, this);  }
              }).sortable({
                 items: \"li:not(.modelo_inicio, .grupo_inicio)\",
                 sort: function() {
                   $( this ).removeClass( \"ui-state-default\" );
                }
              });
        });
        function removethisli(id) {
           var idd = '#'+id;
           $(idd).remove();
           //alert(id);
        }
        function getdefinition() {
            var modelo = {};
            modelo.items = [];
            $('#modelo ol li.level1').each(function(){
                item = {};
                var itemid = $(this).attr('id');
                item.itemid = itemid;
                var clss = $(this).attr('class');
                item.clss = clss;
                var cls = $(this).find('div.variavel').attr('class');
                item.cls = cls;
                var color = $(this).find('div.variavel').css('background-color');
                if (color=='') {
                    color = $(this).find('div').css('background-color');
                }
                item.divcolor = color;
                var txtport = $(this).find(\"div input.level1port\").val();
                item.txtPortugues = txtport;

                var txtengl = $(this).find(\"div input.level1engl\").val();
                item.txtEnglish = txtengl;

                var simbolo = $(this).find(\"div input.level1simbolo:checked\").val();
                item.simbolo = simbolo;
                
                //GET NUMBER FORMAT
                var nf = $(this).find(\"div input.level1numformat:checked\").val();
                item.numberFormat = nf;
                //GET TEXT FORMAT VALUES
                txtf = {};
                var neg = $(this).find(\"div input.level1negrito:checked\").val();
                    txtf.bold = neg;
                var ita =  $(this).find(\"div input.level1italico:checked\").val();
                    txtf.italics = ita;
                var subl =  $(this).find(\"div input.level1sublinhado:checked\").val();
                    txtf.underscore = subl;
                var letcase = $(this).find(\"div input.level1lettercase:checked\").val();
                    txtf.caso = letcase;
                var obj2 = JSON.stringify(txtf);
                if (obj2!='{}') {
                   item.textformat = txtf;
                }
                modelo.items.push(item);
            });
            var md = JSON.stringify(modelo);
            return md;
        }
        
        function SaveModelo() {
           var simbolos = new Array();
           simbolos[0] = document.getElementById('simbolo1').value;
           simbolos[1] = document.getElementById('simbolo2').value;
           simbolos[2] = document.getElementById('simbolo3').value;
           simbolos[3] = document.getElementById('simbolo4').value;
           simbolos[4] = document.getElementById('simbolo5').value;
           var simbs = simbolos.join('SIMBOLO');
           //alert(simbs);
          var time = new Date().getTime();
          var model = getdefinition();
          $.get('monografia-modelo-save.php', { t: time, monografiaid: '".$monografiaid."', modelo: model, simbolos: simbs, listaespecs: 1}, function (data) {
            var progress = parseInt(data, 10);
            if (progress==0 && data!='nao mudou') {
                alert('Houve um erro consulte o administrador!');
            } else {
                if (data!='nao mudou') {
                   alert('Concluido');
                   $('#resultado').html(data+' variáveis no modelo foram salvas');
                } else {
                   alert('Não houve mudança, portanto, não salvei! ');
                }
            }
           });
        }
        function fecharwin() {
            if ($('#resultado').html()=='Editando..') {
                alert('você não salvou o modelo!');
                $('#resultado').html('Não foi salvo');
            } else {
            var el = self.opener.window.document.getElementById('descricaomodelolista');
            el.innerHTML = $('#resultado').html();
            window.close();
            }
       }
       function getModelo() {
          var str = '".$modelo."';
          var modelo = JSON.parse(str);
          //alert(modelo.items[0].traitid);
       }
</script>"
);
$title = 'Modelo de Descrição';
//$body = "onload='getModelo();' ";
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "<script type='text/javascript' src='javascript/wz_tooltip.js'></script>";
echo "<div>
<input type='submit' value='Salvar'  onclick=\"javascript:SaveModelo();\" />&nbsp;&nbsp;<input type='button' value='Fechar' onclick='javascript: fecharwin();'  />&nbsp;<span style='color: red;' id='resultado'></span>
</div>
<div class=\"tools\" >
<div class=\"variaveis\" >
<span class='subs'>Variáveis</span> </span>&nbsp;<span style='font-size: 0.8em;'>(digite para buscar na lista de variáveis e arraste-a ao modelo)</span>&nbsp;<input type='text' id='filtralista'  style='height: 25px; width: 98%; border-radius:5px; -webkit-border-radius:5px; -moz-border-radius:5px; background-color: #F2F2F2; border: solid thin gray' />
<br />
<ul id=\"allfields\"  class='ullist'>";
	 foreach($arrayoffields as $kk => $aa){
			$bgcol = "#D6FFD6";
			echo "
<li class='dalista' style='background: ".$bgcol.";' data-value='".$kk."'  data-text='".$aa."' >".$aa."</li>";
				
	}
//data-tipo='".$tp[1]."' 
echo "
</ul>
</div>
<div class=\"textfields\" >
<span class='subs'>Textos & Símbolos</span><span style='font-size: 0.8em;'>(arraste para o modelo)</span>
<br />
<ul id=\"textbox\" style='background-color: #FFF0F0; '>
<li data-value='textbox'  >Caixa para texto</li>
</ul>
<ul id=\"textsymbol\" style='background-color: #E0E0FF; ' >
<li data-value='textsymbol' >Símbolos</li>
</ul>
<span class='subs'>Definição de simbolos</span><br />
<span>
<table cellpadding='3px'  style='font-size: 0.7em;'>
<tr>
  <td align='left' >Tipo1</td><td align='right' ><input type='text' size=8 value='".$simbpadrao1."' id='simbolo1' value=''></td>
  <td align='left' >Tipo2</td><td align='right' ><input type='text' size=8 value='".$simbpadrao2."' id='simbolo2' value=''></td>
  <td align='left' >Tipo3</td><td align='right' ><input type='text' size=8 value='".$simbpadrao3."' id='simbolo3' value=''></td>
</tr>
<tr>
  <td align='left' >Tipo4</td><td align='right' ><input type='text' size=8 value='".$simbpadrao4."' id='simbolo4' value=''></td>
  <td align='left' >Tipo5</td><td align='right' ><input type='text' size=8 value='".$simbpadrao5."' id='simbolo5' value=''></td>
  <td colspan='2'>&nbsp;</td>
</tr>
</table>
</span>
</div>
</div>
<br />
<br />
<br />
<div style='position: relative; width: 99%;' >
<span class='subs'>Modelo</span>&nbsp;<span style='font-size: 0.8em;'>(arraste variáveis, textos e símbolos, para construir o modelo)</span>
<br />
 <div id=\"modelo\">
    <ol data-onde=\"maingp\" >";
    if (!empty($mmodel)) {
echo $mmodel;
    }
    else {
echo "
      <li class='modelo_inicio'>Arraste variáveis ou caixas de texto aqui e ordene como quiser!</li>";
    }  
echo "
    </ol>
</div>
</div>
";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>
