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

$tbcss = "
<style>
.m2table {
    border-collapse: collapse;
}

.m2table th, td {
    text-align: left;
    padding: 3px;
}
.m2table tr:nth-child(even){background-color: ".$linecolor2."}
.m2table tr:nth-child(odd){background-color: ".$linecolor1."}

.m2table th {
    background-color: #4CAF50;
    color: white;
 }

.m2table th, td, tr {
	border: none;
}
 
//.m2table thead, tbody { display: block; }
 
.m2table tbody { 
    overflow-y: auto;    /* vertical scroll    */
    overflow-x: hidden; 
}

 
.grupo {
   border-collapse: collapse;
	border: 4px solid  #4CAF50;
}
.grupo td,th {border:none;} 

</style>";
$which_css = array("<link href='css/geral.css' rel='stylesheet' type='text/css' />");

$which_java = array(
"<script  type='text/javascript' >
function getcurval() {
    varparent = self.opener.window.document.forms['".$formname."'].elements['".$valuevar."'].value;
    document.getElementById(\"traitids\").value = varparent;
    document.getElementById(\"myform\").submit();
}

function groupvars() {	
	var trs = document.getElementById('tbid').getElementsByTagName('tr');
	var temgrupos = document.getElementsByClassName('grupo').length;
	var grhead = 0;
	var iscon = 0;
	var fezalgo = 0;
	temgrupos = temgrupos+1;	
	var toremove = '';
	var foraseq = 0;
	var firstnotcateg =0;
	//alert(trs.length);
	for (var i=1;i<(trs.length-1);i++){
		var trid  = trs[i].id;
		//alert(trid);
		if(trid) {
			var tid = trid.replace('tr','');
			theid = 'ch'+tid;
			var cco = document.getElementById(theid).checked;
			//alert(trid+'  '+theid+'  '+cco+'  '+i);		
			if (cco==1) {
			   if (grhead===0) {
			   	var otipo = document.getElementById(trid).getAttribute('data-otipo');
			   	if(otipo!='Categoria') { firstnotcateg =1;}
					grhead = tid;
					var cabeca = '<tr id=\'ingp_'+trid+'\'>';
 		     		var otd = document.getElementById(trid).getElementsByTagName('td');
					var ogrupo = '<td colspan=\''+otd.length+'\' ><table width=\'100%\' class=\'grupo\' ><tr id=\'grphead'+temgrupos+'\'><th colspan=\''+otd.length+'\' ><input class=\'temgrp\' type=\'checkbox\' id=\'grpn'+temgrupos+'\' value=1 >&nbsp;GRUPO '+temgrupos+'</th></tr>';
					for (var j=0; j<6; j++){
						var acl = otd[j].className;
						if (acl=='keepingrp') {
						   cabeca = cabeca+'<td class=\'keepingrp\' >'+otd[j].innerHTML+'</td>';
      				} else {
      					cabeca = cabeca+'<td class=\'notingrp\'></td>';
      				}
      			}
      			var gptag = '<input id=\"grpby_'+trid+'\" type=\'hidden\' name=\"groupby[trid_'+tid+']\" value=\"'+grhead+'\" >';
				   cabeca = cabeca+'<td class=\'notingrp\' colspan=\'5\' >PARA CADA CATEGORIA DESTA VARIÁVEL SERÃO RESUMIDAS AS DEMAIS VARIÁVEIS NO GRUPO'+gptag+'</td>';      				
      			ogrupo = ogrupo+cabeca+'</tr>';
      		} else {
      			//alert(iscon+'   '+i);
      			if (iscon==(i-1)) {
      				fezalgo++;
      				var gptag = '<input id=\"grpby_'+trid+'\" type=\'hidden\' name=\"groupby[trid_'+tid+']\" value=\"'+grhead+'\" >';
      				ogrupo = ogrupo+'<tr id=\'ingp_'+trid+'\' >'+trs[i].innerHTML+gptag+'</tr>';
      				toremove = toremove+';'+trid;
   	   		} else {
   	   		   foraseq++;
   	   		}
      		}
      		iscon = i; 		
         }
   	}
	}
	if (grhead>0 & fezalgo>0 & foraseq===0 & firstnotcateg===0) {
		ogrupo = ogrupo+'</table></td>';
		var final = document.getElementById('tr'+grhead);
		final.id = 'grupo'+temgrupos;
	   final.innerHTML = ogrupo;
	   trm = toremove.split(';');
	   //alert(trm.length);
	   for(r=1;r<trm.length;r++) {
	   	//alert(trm[r]);
			document.getElementById(trm[r]).remove();
	   }

	} else {
		if (foraseq>0) { alert('As variáveis selecionadas precisam estar na sequência');}
		if (firstnotcateg>0) {alert('A primeira variável do grupo precisa ser categoria.');}
	}
}

function removegrp() {
	var temgrupos = document.getElementsByClassName('temgrp');
	for(v=0;v<temgrupos.length;v++) {
		 var cco = temgrupos[v].checked;
		 if (cco==1) {
		 	var oid = temgrupos[v].id;
		 	oid = oid.replace('grpn','');
			var gpid = 'grupo'+oid;
			//alert(gpid);
			 
			var vargp = document.getElementById(gpid).getElementsByTagName('tr');
			var trid = 0;
			var gphead = 0;
			var grupout = '';
			var desgrupo = '';
         for(g=1;g<vargp.length;g++) {
           trid = vargp[g].id;
           var trn = trid.split('_');
           trn = trn[1];
           var theid = trn.replace('tr','');
           var ogpid = 'grpby_'+trn;
			  document.getElementById(ogpid).remove();
           if (gphead==0) {
					gphead=1;
					var cabeca = '<tr id=\''+trn+'\' data-otipo=\'Categoria\' >';
 		     		var otd = vargp[g].getElementsByTagName('td');
					for (var j=0; j<6; j++){
						var acl = otd[j].className;
						if (acl=='keepingrp') {
						   cabeca = cabeca+'<td class=\'keepingrp\' >'+otd[j].innerHTML+'</td>';
      				} else {
      					if (j==2) {
      						cabeca = cabeca+'<td class=\'notingrp\' ><input type=checkbox name=\'prefixoprevio[trid_'+theid+'\' ></td>';
      					} else {      					
      						cabeca = cabeca+'<td class=\'notingrp\'></td>';
      					} 
      				}
      			}
					for (var j=0; j<4; j++){
					   cabeca = cabeca+'<td class=\'notingrp\'></td>';
				   }
				   cabeca = cabeca+'<td class=\'notingrp\'>Sb&nbsp;<input type=\'text\' size=5 name=\'categpontuacao[trid_'+theid+'\'  value=\',\' ><br>Txt&nbsp;<input type=\'text\' size=5 name=\'categtxt[trid_'+theid+'\'  value=\'\' >&nbsp;<input type=\'text\' size=5 name=\'categtxteng[trid_'+theid+'\'  value=\'\' ></td></tr>';
				   desgrupo = desgrupo+cabeca;   
           } else {
           	var dtp = vargp[g].getAttribute('data-otipo');
           	
           	var linha = '<tr id=\''+trn+'\' data-otipo=\''+dtp+'\' >';
            linha = linha+vargp[g].innerHTML+'</tr>';
            desgrupo = desgrupo+linha;                       
           }
           //alert(g+' '+trn[1]);
         }
         //alert(desgrupo)
			var trstabela = document.getElementById('tbid').getElementsByTagName('tr');
			var newtrs = '';
			for(t=0;t<trstabela.length;t++) {
				aid = trstabela[t].id;
			   if (aid==gpid) {
			   	newtrs = newtrs+desgrupo;
			   	
			   } else {
			   	var curid = 'grphead'+oid;
			   	var res = aid.substring(0, 4);
			   	if (aid!=curid & res!='ingp' & res!='grph') {
			   		var dtp = trstabela[t].getAttribute('data-otipo');
			   		if (dtp) {
				      	var linha =  '<tr id=\''+aid+'\' data-otipo=\''+dtp+'\'>'+trstabela[t].innerHTML+'</tr>';
			         } else {
				      	var linha =  '<tr id=\''+aid+'\' >'+trstabela[t].innerHTML+'</tr>';
			         }
			      	newtrs = newtrs+linha;
			   	}
			   }
			}
			var tabela =   document.getElementById('tbid');
			//alert(newtrs);
			tabela.innerHTML = newtrs;
			//document.getElementById(gpid).remove();		 
		 }

	}
}	

</script>",$tbcss);
if (!isset($traitids)) {
$body= " onload='getcurval();' ";
} else {
$body = "";
}
$title = 'Formatação de formulário';
FazHeader($title,$body,$which_css,$which_java,$menu);
//echopre($ppost);
if (isset($traitids)) {
	$arrayoftraists = explode(";",$traitids);
	if (count($arrayoftraists)>0) {
		if (!isset($salvando)) {
		//EXTRAIS VALORES SALVOS PARA EDIÇÃO
			$prefixo = array();
			$prefixoeng = array();
			$prefixoprevio = array();
			$ucfirstvalue = array();
			$sufixo = array();
			$sufixoeng = array();
			$formato = array();
			$unitformat = array();
			$unitinclude = array();
			$namostral = array();
			$categpontu = array();
			$categtxt = array();
			$categtxteng = array();
			$categpontu = array();
			$categ = array();
			foreach ($arrayoftraists as $thetrait) {
				$val = $thetrait+0;
				$qq = "SELECT * FROM `FormulariosTraitsList` WHERE `TraitID`='".$val."' AND `FormID`='".$formid."'";
				$res = mysql_query($qq,$conn);
				$nres = mysql_numrows($res);
				if ($nres==1) {
					$aa = mysql_fetch_assoc($res);
					//echopre($aa);
					$prefixo["trid_".$aa['TraitID']] = $aa['prefixo'];
					$prefixoeng["trid_".$aa['TraitID']] = $aa['prefixoeng'];

					$prefixoprevio["trid_".$aa['TraitID']] = $aa['prefixoprevio'];

					$ucfirstvalue["trid_".$aa['TraitID']] = $aa['ucfirstvalue'];
					$sufixo["trid_".$aa['TraitID']] = $aa['sufixo'];
					$sufixoeng["trid_".$aa['TraitID']] = $aa['sufixoeng'];									
					$formato["trid_".$aa['TraitID']] = $aa['formato'];
					$unitformat["trid_".$aa['TraitID']] = $aa['unitformat'];
					$unitinclude["trid_".$aa['TraitID']] = $aa['unitinclude'];
					$namostral["trid_".$aa['TraitID']] = $aa['namostral'];
					$categpontu["trid_".$aa['TraitID']] = $aa['categpontuacao'];
					$categtxt["trid_".$aa['TraitID']] = $aa['categtxt'];
					$categtxteng["trid_".$aa['TraitID']] = $aa['categtxteng'];
					$groupby["trid_".$aa['TraitID']] = $aa['groupby'];
					$quantdec["trid_".$aa['TraitID']]= $aa['quantdec'];					
				}
			}
			//if (count($categ)>0) {
				//$cat = array_unique($categ);
				//$categpontuacao = $cat[0];
			//}
			//echo "SELECT * FROM `FormulariosTraitsList` WHERE  `FormID`='".$formid."'";
		} 
		elseif ($salvando==1) {
			//ATUALIZA A TABELA SE ISSO AINDA NAO FOI FEITO
			$sql = "ALTER TABLE `FormulariosTraitsList` 
ADD COLUMN prefixo CHAR(255) DEFAULT NULL, 
ADD COLUMN sufixo CHAR(255) DEFAULT NULL, 
ADD COLUMN formato CHAR(20) DEFAULT NULL, 
ADD COLUMN unitformat CHAR(20) DEFAULT NULL, 
ADD COLUMN unitinclude CHAR(2) DEFAULT NULL, 
ADD COLUMN namostral CHAR(2) DEFAULT NULL, 
ADD COLUMN categpontuacao CHAR(2) DEFAULT NULL";
			@mysql_query($sql,$conn);

			$sql = "ALTER TABLE `FormulariosTraitsList` 
ADD COLUMN categtxt CHAR(10) DEFAULT NULL AFTER categpontuacao";
			@mysql_query($sql,$conn);

$sql = "ALTER TABLE `FormulariosTraitsList` 
ADD COLUMN prefixoeng CHAR(255) DEFAULT NULL AFTER prefixo,
ADD COLUMN sufixoeng CHAR(255) DEFAULT NULL AFTER sufixo";
			@mysql_query($sql,$conn);
			
			$sql = "ALTER TABLE `FormulariosTraitsList` 
ADD COLUMN prefixoprevio CHAR(2) DEFAULT NULL AFTER prefixo";
			@mysql_query($sql,$conn);

			$sql = "ALTER TABLE `FormulariosTraitsList` 
ADD COLUMN ucfirstvalue CHAR(2) DEFAULT NULL AFTER prefixoprevio";
			@mysql_query($sql,$conn);
			
			$sql = "ALTER TABLE `FormulariosTraitsList` 
ADD COLUMN groupby INT(10)";
			@mysql_query($sql,$conn);			

			$sql = "ALTER TABLE `FormulariosTraitsList` 
ADD COLUMN categtxteng CHAR(50) DEFAULT NULL AFTER categtxt";

			@mysql_query($sql,$conn);				
		
			$sql = "ALTER TABLE `FormulariosTraitsList` 
ADD COLUMN quantdec INT(2)";
			@mysql_query($sql,$conn);				
		
			//ATUALIZA O CADASTRO
			$erro =0;
			foreach ($arrayoftraists as $thetrait) {
				$thetrait = $thetrait+0;
				$qq = "SELECT * FROM `FormulariosTraitsList` WHERE `TraitID`='".$thetrait."' AND `FormID`='".$formid."'";
				$res = mysql_query($qq,$conn);
				$nres = mysql_numrows($res);
				if ($nres==1) {
					$aa = mysql_fetch_assoc($res);
					$arrayofvalues = array(
					'prefixo' => $prefixo["trid_".$aa['TraitID']],
					'prefixoprevio' => $prefixoprevio["trid_".$aa['TraitID']],
					'prefixoeng' => $prefixoeng["trid_".$aa['TraitID']],
					'sufixoeng' => $sufixoeng["trid_".$aa['TraitID']],
					'ucfirstvalue' => $ucfirstvalue["trid_".$aa['TraitID']],
					'sufixo' => $sufixo["trid_".$aa['TraitID']],
					'formato' => $formato["trid_".$aa['TraitID']],
					'unitformat' => $unitformat["trid_".$aa['TraitID']],
					'unitinclude' => $unitinclude["trid_".$aa['TraitID']],
					'namostral' => $namostral["trid_".$aa['TraitID']],
					"categtxt" => $categtxt["trid_".$aa['TraitID']],
					"categtxteng" => $categtxteng["trid_".$aa['TraitID']],
					"groupby" => ($groupby["trid_".$aa['TraitID']]+0),
					"categpontuacao" => $categpontu["trid_".$aa['TraitID']],
					"quantdec" => $quantdec["trid_".$aa['TraitID']]
					);
					//,'categpontuacao' => $categpontuacao
					
					$qqq = "UPDATE `FormulariosTraitsList` SET";
					$i=1;
					$nc = count($arrayofvalues);
					foreach($arrayofvalues as $key => $vv) {
							if ($i==$nc) {
								$qqq = $qqq." ".$key."= '".$vv."'";
							} else {
								$qqq = $qqq." ".$key."= '".$vv."', ";
							}
						$i++;
					}
					$qqq = $qqq." WHERE `TraitID`='".$thetrait."' AND `FormID`='".$formid."'";
					//echo $qqq."<br />";
					$rr = mysql_query($qqq,$conn);
					if (!$rr) {
						$erro++;
					}
				}
			}
			if ($erro==0) {
			echo "<span style='padding: 5px; background-color: yellow; font-size: 1.5em; font-weight: bold; color: black;' >O modelo foi salvo!<br><br></span>";
			}
		}
		$estilo = "style=\"background-color: yellow;\"";

		echo "
<form action=\"formularios_descricao.php\" method=\"post\" >
<input type=\"hidden\" name=\"traitids\" id=\"traitids\"  size=150 value='".$traitids."'/>
<input type=\"hidden\" name=\"formid\" value='".$formid."'/>
<input type=\"hidden\" name=\"salvando\" value='1'/>
<div style=\"color: red; \">
<span style='font-size: 1.5em; font-weight: bold;' >Modelo do formulário para descrições</span>
<ul style='font-size: 1.1em;' >
<li>Desenhe o formulário considerando acrescentando (ou não) prefixos e sufixos de texto a cada uma das variáveis no formulário e marque as demais opções para cada variável.</li>
<li>A descrição será feita concatenando as linhas na ordem do formulário para fazer um texto único que pode ser usado em etiquetas e exportação de dados para herbário, geração de descrições de espécies etc.</li>
<li>Se você apagar variáveis do formulário, a linha correspondente irá desaparecer. Se você reordenar variáveis, as informações abaixo serão preservadas.</li>
</ul>
</div>
<br/>
<table align='left' class='m2table' id='tbid' >
<thead>
<tr align='center' >
<th>Tag</th>
<th>Prefixo</th>
<th>Prefixo Prévio&nbsp;<img style='cursor: pointer;' height=12 src=\"icons/icon_question.gif\" ";
$help = "Se selecionado o prefixo da variável anterior será concatenado como prefixo do prefixo desta variável caso a variável anterior não tenha valores no conjunto de dados selecionado. Isso é útil, por exemplo, quando há valores faltantes e você especifica apenas um valor num conjunto de variáveis selecionadas: e.g. exsudado tipo, quantidade e cor.";
echo "onclick=\"javascript:alert('$help');\" /></th>
<th>Fixa small caps<img style='cursor: pointer;' height=12 src=\"icons/icon_question.gif\" ";
$help = "Força que a variação seja colocada em minúsculo";
echo "onclick=\"javascript:alert('$help');\" /></th>
<th>Variável</th>
<th>Sufixo</th>
<th>Formato</th>
<th>Valor em&nbsp;<img style='cursor: pointer;' height=12 src=\"icons/icon_question.gif\" ";
$help = "Em qual unidade a variação deve ser calculada?";
echo "onclick=\"javascript:alert('$help');\" /></th>
<th> Inclui unidade&nbsp;<img style='cursor: pointer;' height=12 src=\"icons/icon_question.gif\" ";
$help = "Marcar se você quer que a unidade seja adicionada após o valor (antes do sufixo)";
echo "onclick=\"javascript:alert('$help');\" /></th>
<th>Decimal&nbsp;<img style='cursor: pointer;' height=12 src=\"icons/icon_question.gif\" ";
$help = "Número de casa decimais para os valores da variável";
echo "onclick=\"javascript:alert('$help');\" /></th>

<th>Pontuaçãol&nbsp;<img style='cursor: pointer;' height=12 src=\"icons/icon_question.gif\" ";
$help = "Indicar o tipo de pontuação da variável categórica: (1) para separar múltiplos valores e, (2) para separar dois valores ou como prefixo do último";
echo "onclick=\"javascript:alert('$help');\" /></th>
<!---
<th>N amostral&nbsp;<img style='cursor: pointer;' height=12 src=\"icons/icon_question.gif\" ";
$help = "Marcar se você quer que o N amostral dos valores quantitativos ou categóricos seja adiconado ao final da variação (após a unidade e antes do sufixo) no formato (N=valor)";
echo "onclick=\"javascript:alert('$help');\" /></th>
--->
</tr>
</thead>
<tbody>
";
foreach ($arrayoftraists as $thetrait) {
		$equant=0;
		$catego=0;
		$val = $thetrait+0;
		$qq = "SELECT * FROM `Traits` WHERE `TraitID`='".$val."'";
		$res = mysql_query($qq,$conn);
		$aa = mysql_fetch_assoc($res);
		$PathName = $aa['TraitName'];
		$tipo = $aa['TraitTipo'];
		$tp = explode("|",$tipo);
		if ($tp[1]=='Categoria') {
			$catego=1;
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
		} 
		else {
			$nn = $aa['PathName']." [".$tp[1]."]";
			if ($tp[1]=='Quantitativo') {
				$equant = 1;
				$bgcol = "#D6FFD6";
				$aunit = $aa['TraitUnit'];
			}
			if ($tp[1]=='Texto') {
				$bgcol = "#D3D3D3";
			}
			if ($tp[1]=='Imagem') {
				$bgcol = "#FFE4E1";
			}
		}


		$egrupo = $groupby['trid_'.$aa['TraitID']];
		
		if (!empty($egrupo) && $egrupo>0) {
			if (empty($gpnum) || !isset($gpnum)) {
				$gpnum = 1;			
			} elseif ($gpnum==0) { $gpnum=$previousgp+1;}
			if ($egrupo==$aa['TraitID']) {
				//grupo head
				$gphead = "<tr id='grupo".$gpnum."' >";
				$gphead .= "<td colspan='100%' ><table width='100%' class='grupo' >";
				$gphead .= "<tr id='grphead".$gpnum."'><th colspan='100%' >";
				$gphead .= "<input class='temgrp' type='checkbox' id='grpn".$gpnum."' value=1 >&nbsp;GRUPO ".$gpnum."</th></tr>";
				$gphead .= "<tr id='ingp_tr".$aa['TraitID']."' >";		
				$gphead .= "<td class='notingrp' >&nbsp;</td>"; 
				$gphead .= "<td class='keepingrp'>";
					$flagbr = "brasilFlagicon.png";
					$flagus = "usFlagicon.png";
					$gphead .= "<img height='20' src=\"icons/".$flagbr."\" />
<textarea $estilo  type='text'  name=\"prefixo[trid_".$aa['TraitID']."]\">".$prefixo['trid_'.$aa['TraitID']]."</textarea> 
<br><img height='20' src=\"icons/".$flagus."\" /><textarea $estilo  type='text'  name=\"prefixoeng[trid_".$aa['TraitID']."]\">
".$prefixoeng['trid_'.$aa['TraitID']]."</textarea> </td>";
				$gphead .= "<td class='notingrp' ></td>";
				$auu = $ucfirstvalue["trid_".$aa['TraitID']];
				if ($auu=='on') { $txt='checked';} else {$txt="";}
				$gphead .= "<td class='keepingrp'><input type='checkbox' name=\"ucfirstvalue[trid_".$aa['TraitID']."]\" $txt ></td>";
				$gphead .= "<td class='keepingrp'><textarea style='background: ".$bgcol.";' readonly>".$nn."</textarea></td>";
				$gphead .= "<td class='notingrp' ></td>";
				$gptag = "<input id=\"grpby_tr".$aa['TraitID']."\" type='hidden' name=\"groupby[trid_".$aa['TraitID']."]\" value=\"".$egrupo."\" >";
				$gphead .= "<td class='notingrp' colspan='5' >PARA CADA CATEGORIA DESTA VARIÁVEL SERÃO RESUMIDAS AS DEMAIS VARIÁVEIS NO GRUPO".$gptag."</td>";
				$gphead .= "</tr>";
				echo $gphead;
			} else {
				//membros do grupo			
				$gptag = "<input id=\"grpby_tr".$aa['TraitID']."\" type='hidden' name=\"groupby[trid_".$aa['TraitID']."]\" value=\"".$egrupo."\" >";
				echo "<tr id='ingp_tr".$val."' >".$gptag;
			}		
		} else {
			$previousgp = $gpnum;
			if ($gpnum>0) {
				echo "
</table></td></tr>";					//entao é o fim de um grupo
			}
			$gpnum=0;
echo "
<tr id='tr".$val."' data-otipo='".$tp[1]."' >";
		}

if ($egrupo!=$aa['TraitID'] || empty($egrupo) || $egrupo==0) {

echo "<td class='keepingrp' ><input type='checkbox' id='ch".$val."' value='1' ></td> 
<td class='keepingrp'>";
$flagbr = "brasilFlagicon.png";
$flagus = "usFlagicon.png";
echo "
<img height='20' src=\"icons/".$flagbr."\" />
<textarea $estilo  type='text'  name=\"prefixo[trid_".$aa['TraitID']."]\">".$prefixo['trid_'.$aa['TraitID']]."</textarea> 
<br>
<img height='20' src=\"icons/".$flagus."\" />
<textarea $estilo  type='text'  name=\"prefixoeng[trid_".$aa['TraitID']."]\">".$prefixoeng['trid_'.$aa['TraitID']]."</textarea> 
</td>";
$auu = $prefixoprevio["trid_".$aa['TraitID']];
if ($auu=='on') { $txt='checked';} else {$txt="";}
echo "
<td class='notingrp' ><input type='checkbox' name=\"prefixoprevio[trid_".$aa['TraitID']."]\" $txt ></td>";
$auu = $ucfirstvalue["trid_".$aa['TraitID']];
if ($auu=='on') { $txt='checked';} else {$txt="";}
if ($catego==1) {
echo "
<td class='keepingrp'><input type='checkbox' name=\"ucfirstvalue[trid_".$aa['TraitID']."]\" $txt ></td>";
} else { echo "<td></td>";}
echo "
<td class='keepingrp'>
<textarea style='background: ".$bgcol.";' rows='6' readonly>".$nn."</textarea>
</td>
<td class='keepingrp'>
<img height='20' src=\"icons/".$flagbr."\" />
<textarea $estilo  type='text'  name=\"sufixo[trid_".$aa['TraitID']."]\">".$sufixo['trid_'.$aa['TraitID']]."</textarea> 
<br>
<img height='20' src=\"icons/".$flagus."\" />
<textarea $estilo  type='text'  name=\"sufixoeng[trid_".$aa['TraitID']."]\">".$sufixoeng['trid_'.$aa['TraitID']]."</textarea> 
</td>";
if ($equant==1) {
$auu = $formato["trid_".$aa['TraitID']];
echo "
<td class='notingrp' >
<select name=\"formato[trid_".$aa['TraitID']."]\" >";
$txt = '';
if ($auu=='range' || empty($auu)) {$txt='selected';}  
echo "
<option $txt value='range'>Min-Max</option>";
$txt1 = '';
if ($auu=='meansd') {$txt1='selected';} 
echo "
<option $txt1 value='meansd'>Mean&plusmn;Sd</option>";
$txt2 = '';
if ($auu=='meansdrange') {$txt2='selected';}  
echo "
<option $txt2 value='meansdrange' >Mean&plusmn;Sd (Min-Max)</option>
</select>
</td>
<td class='notingrp' >
<select name=\"unitformat[trid_".$aa['TraitID']."]\">";
$auu = $unitformat["trid_".$aa['TraitID']];
$txt = "";
if (empty($auu)) { $txt = 'padrao'; } 
if ($auu=='padrao') {$txt='selected';} 
echo "<option  ".$txt."  value='".$aunit."' >Padrão $aunit</option>";
$txt1 = '';
if ($auu=='mm') {$txt1='selected';} 
echo "<option  ".$txt1."  value='mm' >mm</option>";
$txt2 = '';
if ($auu=='cm') {$txt2='selected';} 
echo "<option  ".$txt2."  value='cm' >cm</option>";
$txt3 = '';
if ($auu=='m') {$txt3='selected'; }  
echo "<option  ".$txt3."  value='m' >m</option>
</select>
</td>";
$auu = $unitinclude["trid_".$aa['TraitID']];
if ($auu=='on') {$txt='checked';}  else {$txt="";}
echo "<td class='notingrp'  align='center'>
<input type='checkbox' name=\"unitinclude[trid_".$aa['TraitID']."]\" $txt >
</td>";

$auu = $quantdec["trid_".$aa['TraitID']];
if (empty($auu) && $auu!=0) { $auu=1;}
echo "
<td class='notingrp' >
<input type='number' name=\"quantdec[trid_".$aa['TraitID']."]\" 
value='".$auu."' min=0 max=6 maxlength=2></td>";



} else {
echo "<td >&nbsp;</td><td >&nbsp;</td><td >&nbsp;</td><td >&nbsp;</td>";
}
if ($catego==1) {
//if ($equant==1 || $catego==1) {
$auu = $categpontu["trid_".$aa['TraitID']];
if (empty($auu)) { $auu=",";}
$auu2 = $categtxt["trid_".$aa['TraitID']];
if (empty($auu2)) { $auu2="ou";}
$auu3 = $categtxteng["trid_".$aa['TraitID']];
if (empty($auu3)) { $auu3="or";}
echo "<td class='notingrp' align='center'>
Sb&nbsp;<img style='cursor: pointer;' height=12 src=\"icons/icon_question.gif\" ";
$help = "Símbolo para separar múltiplos valores";
echo "onclick=\"javascript:alert('$help');\" />&nbsp;<input type='text' size=5 name=\"categpontu[trid_".$aa['TraitID']."]\"  value='".$auu."' >
<br>Txt<img style='cursor: pointer;' height=12 src=\"icons/icon_question.gif\" ";
$help = "Palavra para separar dois valores ou como prefixo do último";
echo "onclick=\"javascript:alert('$help');\" />&nbsp;<input type='text' size=5 name=\"categtxt[trid_".$aa['TraitID']."]\"  value='".$auu2."' >&nbsp;<input type='text' size=5 name=\"categtxteng[trid_".$aa['TraitID']."]\"  value='".$auu3."' >
</td>";
} else {
echo "<td class='notingrp' >&nbsp;</td>";
}
echo "
</tr>";

}



	}
	if ($gpnum>0) {
				echo "
</table></td></tr>";					//entao é o fim de um grupo
	}
	echo "
<tr ><td colspan='100%' align='center' >
<input type='button' value='Fechar' onclick='javascript: window.close();' >
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type='submit' value='Salvar' >
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type='button'  value='Agrupa Selecionadas'  onclick='javascript: groupvars();'>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type='button'  value='Remove grupo selecionado'  onclick='javascript: removegrp();'>
</td>
</tr>
</tbody>
</table>
</form>

";
} 

} else {
	//PRIMEIRO FORM SUBMIT VIA getcurval() PARA PEGAR VALOR NA PÁGINA ANTERIOR
echo "
<form id=\"myform\" action=\"formularios_descricao.php\" method=\"POST\" >
<input type=\"hidden\" name=\"traitids\" id=\"traitids\"  size=150 value=''/>
<input type=\"hidden\" name=\"formid\" value='".$formid."'/>
</form>";
////
}

//echopre($ppost);

$which_java = array();
FazFooter($which_java,$calendar=FALSE,$footer=$menu);
?>