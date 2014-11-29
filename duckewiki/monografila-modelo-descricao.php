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
if ($monografiaid>0) {
	$qq = "SELECT * FROM Monografias WHERE MonografiaID='".$monografiaid."'";
	$res = mysql_query($qq,$conn);
	$rr = mysql_fetch_assoc($res);
	$modelo= $rr['ModeloDescricoes'];
	$modelosimbolos= explode("SIMBOLO",$rr['ModeloSimbolos']);
	$simbpadrao1 = $modelosimbolos[0];
	$simbpadrao2 = $modelosimbolos[1];
	$simbpadrao3 = $modelosimbolos[2];
	$simbpadrao4 = $modelosimbolos[3];
	$simbpadrao5 = $modelosimbolos[4];
	$modeloarr = json_decode($modelo);
	//echopre ($modeloarr);
	$mmodel = printModelo($modeloarr,$conn);
} 


//CABECALHO
$ispopup=1;
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' >",
"<link rel='stylesheet' type='text/css' href=\"javascript/jQueryTE/jquery-te-1.4.0.css\" />",
"<link rel='stylesheet' type='text/css' href='css/jquery-ui.css' />",
"<style>
.tools {
	top: 1px;
	width: 99%;
	height: 220px;
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
            var corcat = '#99CCFF';
            var cortxt = '#FFCCCC';
            var corsym = '#CC99FF';
          } else {
            var corquant = '#EBFFEB';
            var corcat = '#CCFFFF';
            var cortxt = '#FFF0F0';
            var corsym = '#E0E0FF';
          }
          //se for uma variavel
          if (tempid!='textbox' && tempid!='textsymbol') {
             //PEGA O TIPO E O NOME DA VARIAVEL ARRASTADA
             var traitname = ui.draggable.attr(\"data-text\");
             var tipo = ui.draggable.attr(\"data-tipo\");
            //VERIFICA SE A VARIAVEL JÁ FOI ARRASTADA ANTERIORMENTE E CRIA UM ID UNICO PARA O OBJETO
             if (onde=='maingp') {
               var ff = '.variavel '+ tempid;
               var tt = $( thisid ).find('div.variavel[data-traitid='+tempid+']').length;
               var temppid = tempid+'_'+tt;
               var gpby = '&nbsp;&nbsp;<input id=\"groupby'+temppid+'\" type=\"checkbox\"  onclick=\"javascript: groupby(\'' + temppid + '\',\''+ traitname + '\');\">AgruparPor';
               var cls = 'variavel '+tempid;
               var clss = 'level1';
             } else {
               var ff = '.gpvariavel '+ tempid;
               var tt = $( thisid ).find('div.gpvariavel[data-traitid='+tempid+']').length;
               var temppid = tempid+'_'+onde+'_'+tt;
               var gpby = '';
               var cls = 'gpvariavel '+tempid;
               var clss = 'level2';
             }
             //DEFINE OS VARIAVEIS
             if (tipo=='Quantitativo') {
                var novoval =  '<div data-traitid=\"'+tempid+'\" class=\"'+cls+'\"  style=\"border: solid 1px gray; border-radius: 5px; background-color: '+corquant+'; padding: 3px; margin: 5px 0 5px 0; font-size: 0.8em; cursor: move\" ><span onmouseover=\"Tip(\''+ traitname +'\');\">'+traitname+'</span>&nbsp;<span onmouseover=\"Tip(\'Defina o formato para os valores\');\">[N-formato:<input  type=\"radio\"   value=\"media\"  class=\"'+clss+'numformat\" name=\"nf'+ temppid +'\">média&nbsp;<input  type=\"radio\"   value=\"range\" class=\"'+clss+'numformat\" name=\"nf'+ temppid +'\">range&nbsp;<input  type=\"radio\" value=\"min\" class=\"'+clss+'numformat\" name=\"nf'+ temppid +'\">min&nbsp;<input  type=\"radio\"   value=\"max\" class=\"'+clss+'numformat\" name=\"nf'+ temppid +'\">max]</span>&nbsp;&nbsp;<span onmouseover=\"Tip(\'Defina o formato do texto\');\">[Txt-formato:<input type=\"checkbox\" class=\"'+clss+'negrito\"><b>N</b>&nbsp;<input type=\"checkbox\" class=\"'+clss+'sublinhado\"><u>S</u>&nbsp;<input type=\"checkbox\" class=\"'+clss+'italico\"><i>I</i>]</span>&nbsp;&nbsp;<input type=\"checkbox\" class=\"'+clss+'unidade\">adicionar unidade &nbsp;&nbsp;&nbsp;<img src=\"icons/trashcan.png\" height=16 style=\"cursor: pointer\" onclick=\"javascript:removethisli(\''+temppid+'\');\" onmouseover=\"Tip(\'remove o item\');\"></div>'; 
             } else {
               var novoval =  '<div data-traitid=\"'+tempid+'\" class=\"'+cls+'\" style=\"border: solid 1px gray; border-radius: 5px; background-color:  '+corcat+'; padding: 3px; margin: 5px 0 5px 0; font-size: 0.8em; cursor: move\" ><span onmouseover=\"Tip(\''+ traitname +'\');\">'+traitname+'</span>&nbsp;<span onmouseover=\"Tip(\'Defina o formato do texto\');\">[Txt-formato:&nbsp;<input type=\"checkbox\" class=\"'+clss+'negrito\"><b>N</b>&nbsp;<input type=\"checkbox\" class=\"'+clss+'sublinhado\"><u>S</u>&nbsp;<input type=\"checkbox\" class=\"'+clss+'italico\"><i>I</i>&nbsp;<input type=\"radio\" class=\"'+clss+'lettercase\" value=\"uppercase\" name=\"cba'+ temppid +'\">CA&nbsp;<input type=\"radio\" class=\"'+clss+'lettercase\" value=\"lowercase\" name=\"cba'+ temppid +'\">cb]</span>&nbsp;'+gpby+'&nbsp;&nbsp;<img src=\"icons/trashcan.png\" height=16 style=\"cursor: pointer\" onclick=\"javascript:removethisli(\''+temppid+'\');\" onmouseover=\"Tip(\'remove o item\');\"> <span class=\"grupo\" id=\"grupo_'+temppid+'\"></span></div>'; 
               }
               var idd = '<li class=\"'+clss+'\" id=\"'+temppid+'\" ></li>';
            } else {
            if (tempid=='textbox' && tempid!='textsymbol') {
              if (onde=='maingp') {
                  var tt = $( thisid ).find('div.variavel textoli').length;
                  temppid = 'txt_'+tt;
                  cls = 'variavel textoli';
                  clss = 'level1';
              } else {
                  var tt = $( thisid ).find('div.gpvariavel subtextoli').length;
                  temppid = 'txt_'+'_'+onde+'_'+tt;
                  cls = 'gpvariavel subtextoli';
                  clss = 'level2';
             }
              var novoval =  '<div class=\"'+cls+'\" style=\"padding: 5px; margin: 5px 0 5px 0; border-radius:5px; -webkit-border-radius:5px; -moz-border-radius:5px; background-color:  '+cortxt+'; border: #cccccc solid thin; cursor: move;\"  onmouseover=\"Tip(\'Caixa para texto livre\');\"><img height=\"16\" src=\"icons/BrasilFlagicon.png\">&nbsp;<input class=\"'+clss+'port\" size=\"60%\" type=\"text\"><br /><img height=\"16\" src=\"icons/usFlagicon.png\">&nbsp;<input class=\"'+clss+'engl\" size=\"60%\" type=\"text\">&nbsp;<span onmouseover=\"Tip(\'Defina o formato do texto\');\">[Txt-formato:&nbsp;<input type=\"checkbox\" class=\"'+clss+'negrito\"><b>N</b>&nbsp;<input type=\"checkbox\" class=\"'+clss+'sublinhado\"><u>S</u>&nbsp;<input type=\"checkbox\" class=\"'+clss+'italico\"><i>I</i>&nbsp;<input type=\"radio\" class=\"'+clss+'lettercase\" value=\"uppercase\" name=\"cba'+ temppid +'\">CA&nbsp;<input type=\"radio\" class=\"'+clss+'lettercase\" value=\"lowercase\" name=\"cba'+ temppid +'\">cb]</span>&nbsp;&nbsp;&nbsp;<img src=\"icons/trashcan.png\" height=16 style=\"cursor: pointer\" onclick=\"javascript:removethisli(\''+temppid+'\');\" onmouseover=\"Tip(\'remove o item\');\"></div>';
                  var idd = '<li class=\"'+clss+'\"  id=\"'+temppid+'\" ></li>';
} else {
              if (onde=='maingp') {
                  var tt = $( thisid ).find('div.variavel simbololi').length;
                  temppid = 'symb_'+tt;
                  cls = 'variavel simbololi';
                  clss = 'level1';
              } else {
                  var tt = $( thisid ).find('div.gpvariavel subsimbololi').length;
                  temppid = 'symb_'+'_'+onde+'_'+tt;
                  cls = 'gpvariavel subsimbololi';
                  clss = 'level2';
             }
             var novoval =  '<div class=\"'+cls+'\" style=\"padding: 5px; margin: 5px 0 5px 0; border-radius:5px; -webkit-border-radius:5px; -moz-border-radius:5px; background-color:  '+corsym+'; border: #cccccc solid thin; cursor: move;\"  onmouseover=\"Tip(\'Defina símbolo\');\"><span>Qual símbolo?&nbsp;<input type=\"radio\" class=\"'+clss+'simbolo\" value=1 name=\"symb_'+ temppid +'\">Tipo1&nbsp;<input type=\"radio\" class=\"'+clss+'simbolo\" value=2 name=\"symb_'+ temppid +'\">Tipo2&nbsp;<input type=\"radio\" class=\"'+clss+'simbolo\" value=3 name=\"symb_'+ temppid +'\">Tipo3&nbsp;<input type=\"radio\" class=\"'+clss+'simbolo\" value=4 name=\"symb_'+ temppid +'\">Tipo4&nbsp;<input type=\"radio\" class=\"'+clss+'simbolo\" value=5 name=\"symb_'+ temppid +'\">Tipo5</span>&nbsp;&nbsp;&nbsp;&nbsp;<img src=\"icons/trashcan.png\" height=16 style=\"cursor: pointer\" onclick=\"javascript:removethisli(\''+temppid+'\');\" onmouseover=\"Tip(\'remove o item\');\"></div>';
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
        function groupby(idd, trname) {
                var gp2 = '#grupo_'+idd;
                //adiciona
                if ($(gp2).is(':empty')) {
                  var ttt = '<br /><input id=\"gp'+idd+'\" type=\"button\" value=\"Esconder grupo\" onclick=\"javascript:mostraresconder(\''+idd+'\');\" ><div id=\"grupo'+idd+'\"></div>';
                  $(ttt).appendTo(gp2);
                  var ttt2 = '<ol data-onde=\"subgp'+idd+'\" class=\"subgrupo\" type=\"a\" style=\"height: 150px; background-color: #FFFFCC;\"><li class=\"modelo_inicio\">Arraste aqui as variáveis que deseja agrupar segundo as categorias de <b>' + trname +'</b></li></ol>';
                  var gp3 = '#grupo'+idd;
                  $( ttt2 ).droppable({greedy: true, accept: \"#allfields li, #textbox li, #textsymbol li\", drop: function( event, ui ){ mydropfunction(event,ui, this); } }).sortable().appendTo( gp3);
                } else {
                    $(gp2).html('');
                }
        };
        function mostraresconder(id) {
            var idd = '#grupo'+id;
            var idd2 = '#gp'+id;
            var txt = $(idd2).val();
            if (txt=='Esconder grupo') {
               $(idd2).val('Mostrar grupo');
            } else {
               $(idd2).val('Esconder grupo');
            }
            $(idd).toggle();
        }
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
                var traitid = $(this).find('div.variavel').attr('data-traitid');
                item.traitid = traitid;
                
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
                //GET UNIT IF IS THE CASE
                var unit = $(this).find(\"div input.level1unidade:checked\").val();
                    item.unidade = unit;
                //IF CATEGORY CHECK FOR GROUPING
                var traitipo = $(this).find('div.variavel').attr('data-tipo');
                if (traitid>0 & traitipo!='Quantitativo') {
                  var ll = $(this).find('ol li.level2').length;
                  if (ll>0) {
                       item.grupo = [];
                       $(this).find('ol li.level2').each(function(){
                           subitem = {};
                           var traitid = $(this).find('div.gpvariavel').attr('data-traitid');
                           subitem.traitid = traitid;
                           
                           var itemid = $(this).attr('id');
                           subitem.itemid = itemid;
                           var clss = $(this).attr('class');
                           subitem.clss = clss;
                           var cls = $(this).find('div.gpvariavel').attr('class');
                           subitem.cls = cls;
                           var color = $(this).find('div.gpvariavel').css('background-color');
                           if (color=='') {
                              color = $(this).find('div').css('background-color');
                            }
                           subitem.divcolor = color;
                           
                           
                           var txtport = $(this).find(\"div input.level2port\").val();
                           subitem.txtPortugues = txtport;
                           
                           var txtengl = $(this).find(\"div input.level2engl\").val();
                           subitem.txtEnglish = txtengl;
                           
                           var simbolo1 = $(this).find(\"div input.level2simbolo:checked\").val();
                           subitem.simbolo = simbolo1;
                           
                           //GET NUMBER FORMAT
                           var nf = $(this).find(\"div input.level2numformat:checked\").val();
                           subitem.numberFormat = nf;
                           //GET TEXT FORMAT VALUES
                           subtxtf = {};
                           var neg = $(this).find(\"div input.level2negrito:checked\").val();
                               subtxtf.bold = neg;
                           var ita =  $(this).find(\"div input.level2italico:checked\").val();
                               subtxtf.italics = ita;
                           var subl =  $(this).find(\"div input.level2sublinhado:checked\").val();
                               subtxtf.underscore = subl;
                           var letcase = $(this).find(\"div input.level2lettercase:checked\").val();
                               subtxtf.caso = letcase;
                           var obj2 = JSON.stringify(subtxtf);
                           if (obj2!='{}') {
                              subitem.textformat = subtxtf;
                           }
                           //GET UNIT IF IS THE CASE
                           var unit = $(this).find(\"div input.level2unidade:checked\").val();
                               subitem.unidade = unit;
                           item.grupo.push(subitem);
                       });
                  }
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
          $.get('monografia-modelo-save.php', { t: time, monografiaid: '".$monografiaid."', modelo: model, simbolos: simbs}, function (data) {
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
            var el = self.opener.window.document.getElementById('descricaomodelo');
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
//                  $(gp).find('span.grupo').html('<br /><input id=\"gp'+idd+'\" type=\"button\" value=\"Esconder grupo\" onclick=\"javascript:mostraresconder(\''+idd+'\')\" ><br /><div class=\"subgrupo\"><ol id=\"grupo'+idd+'\"  type=\"a\" style=\"height: 50px;\"><li class=\"grupo_inicio\">Arraste aqui as variáveis que deseja agrupar segundo as categorias de <b>' + trname +'</b></li></ol></div>');

// $('.mycheck').On('click', function() { alert('funciona');}); 
$title = 'Modelo de Descrição';
//$body = "onload='getModelo();' ";
FazHeader($title,$body,$which_css,$which_java,$menu);
echo "<script type='text/javascript' src='javascript/wz_tooltip.js'></script>";

//*var novoval =  val + '<input id=\"teste1\" draggable=\"true\" style=\"background-color: #99CCFF\" readonly value=\"{'+tempid+'}\" >&nbsp;'; */

if (!isset($enviado)) {
//<textarea id='lugareditando' name=\"meutexto\" class=\"jqte-test\"></textarea>

} 
echo "<div>
<input type='submit' value='Salvar'  onclick=\"javascript:SaveModelo();\" />&nbsp;&nbsp;<input type='button' value='Fechar' onclick='javascript: fecharwin();'  />&nbsp;<span style='color: red;' id='resultado'></span>
</div>
<div class=\"tools\" >
<div class=\"variaveis\" >
<span class='subs'>Variáveis</span> </span>&nbsp;<span style='font-size: 0.8em;'>(digite para buscar na lista de variáveis e arraste-a ao modelo)</span>&nbsp;<input type='text' id='filtralista'  style='height: 25px; width: 98%; border-radius:5px; -webkit-border-radius:5px; -moz-border-radius:5px; background-color: #F2F2F2; border: solid thin gray' />
<br />
<ul id=\"allfields\"  class='ullist'>";
		$filtro ="SELECT * FROM `Traits` WHERE `TraitName`<>'' AND TraitTipo<>'Estado' AND TraitTipo<>'Classe' AND TraitTipo NOT LIKE '%text%' AND TraitTipo NOT LIKE '%Imag%' ORDER BY `PathName` ASC";
		$res = mysql_query($filtro,$conn);
		while ($aa = mysql_fetch_assoc($res)){
			$PathName = $aa['PathName'];
			$tipo = $aa['TraitTipo'];
			$name = $aa['TraitName'];
			if ($tipo!='Classe') { //if is a class or a state does not allow selection
				$tp = explode("|",$tipo);
				if ($tp[1]=='Categoria') {
					$qu = "SELECT * FROM `Traits` WHERE `ParentID`='".$aa['TraitID']."'";
					$ru = mysql_query($qu,$conn);
					$ncat = mysql_numrows($ru);
					$std = array();
					while ($rwu = mysql_fetch_assoc($ru)) {
						$std[] = strtolower($rwu['TraitName']);
					}
					$stads = implode(";",$std);
					$stlen = strlen($stads);
					if ($stlen>30) {
						$stt = substr($stads,0,50);
						$stt = $stt."....";
					} else {
						$stt = $stads;
					}
					$nn = $aa['PathName']." [Categorias: $stt]";
					$bgcol = "#99CCFF";
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
				}
				echo "
<li class='dalista' style='background: ".$bgcol.";' data-value='".$aa['TraitID']."'  data-tipo='".$tp[1]."' data-text='".$name."' >".$nn."</li>";
				
			}
	}
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
<span style='font-size: 0.8em;'>
<input type='text' size=5 value='".$simbpadrao1."' id='simbolo1' value=''>&nbsp;Tipo1&nbsp;
<input type='text' size=5 value='".$simbpadrao2."' id='simbolo2' value=''>&nbsp;Tipo2&nbsp;
<input type='text' size=5 value='".$simbpadrao3."' id='simbolo3' value=''>&nbsp;Tipo3&nbsp;
<input type='text' size=5 value='".$simbpadrao4."' id='simbolo4' value=''>&nbsp;Tipo4&nbsp;
<input type='text' size=5 value='".$simbpadrao5."' id='simbolo5' value=''>&nbsp;Tipo5&nbsp;
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
