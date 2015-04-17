<?php
//IMPORTA UMA TABELA QUALQUER AO MYSQL
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

//echopre($ppost);
//CABECALHO
$ispopup=1;
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />"
);
$which_java = array(
);
$title = 'Variável Salvando';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);


//transferencia de nova imagem para folder
if (!empty($_POST['newimage'])) {
	$myfile = $_FILES['novaimg']['name'];
	if ($myfile) {
		$basename = explode(".",$_FILES['novaimg']['name']);
		$basename = $basename[0];
		list ($ftypename, $ftype) = explode("/",$_FILES['novaimg']['type']);
		$filedate = date("Y-m-d");
		$myfile = $filedate."_".$basename.".".trim($ftype);
		move_uploaded_file($_FILES["novaimg"]["tmp_name"],"img/traits_icons/$myfile");
		$traiticone = trim($myfile);
	}
}
//process results
if (empty($traittipo)) {$traittipo=$traitkind;}
$erro=0;
	//mensagens de erro por dados incompletos
if ($traitkind=='Classe') { //se classe
		//required
		if (empty($traitname) || empty($traitdefinicao)) {
			echo "
<br>
<table cellpadding=\"7\" align='center' class='erro'>
  <tr><td><b>".GetLangVar('erro1')."</b></td></tr>
  <tr><td>".GetLangVar('namedefinicao').",  ".GetLangVar('namenome')."</td></tr>
  </table>
<br>
";
			$erro++;
		}
		if ($parenttraitid=='padrao') {unset($parenttraitid);}
	}
	$tt = explode("|",$traittipo);
	if ($tt[0]=='Variavel' && $tt[1]!='Pelos') { //se caractere
		if (empty($traitname) || empty($traitdefinicao) || empty($traittipo) || empty($parenttraitid)) {
			echo "
<br>
<table cellpadding=\"7\" align='center' class='erro'>
  <tr><td><b>".GetLangVar('erro1')."</b></td></tr>
  <tr><td>".GetLangVar('namenome').",  ".GetLangVar('messagepertenceaclasse').", ".GetLangVar('nametipo').",  ".GetLangVar('namedefinicao')."</td></tr>
</table>
<br>";
			$erro++;
		}
	}


	if ($traitkind=='Estado') { //se estado
		if (empty($traitname) || empty($traitdefinicao) || empty($parenttraitid)) {
			echo "
<br>
<table cellpadding=\"3\" width='50%' align='center' class='erro'>
  <tr><td><b>".GetLangVar('erro1')."</b></td></tr>
  <tr><td>".GetLangVar('namenome').",  ".GetLangVar('messagepertenceaclasse').",  ".GetLangVar('namedefinicao')."</td></tr>
</table>
<br>
";
			$erro++;
		}
	}

	$traitunit = trim($traitunit);
	$traitunitnew = trim($traitunitnew);
	if (!empty($traitunitnew)) { $traitunit= $traitunitnew;}
	if ($traittipo=='Variavel|Quantitativo' && empty($traitunit)) {
		echo "
<br>
<table cellpadding=\"7\" align='center' class='erro'>
<tr><td><b>".GetLangVar('erro1')."</b></td></tr>
<tr><td>".GetLangVar('nameunidade')."</td></tr>
</table>
<br>";
		$erro++;
	} 

//checar se o nome nao foi duplicado
if ($traittipo=='Variavel|Cores' || $traittipo=='Variavel|Pelos') {
		$ttttipo = 'Variavel|Categoria';
} else {
	$ttttipo = $traittipo;
}

$dupsnome = TraitNameCheck($traitname,$ttttipo,TRUE,$conn,$traitid,$parenttraitid);
if (count($dupsnome)>0) {
		echo "
<br>
<table cellpadding=\"7\" align='center' class='tablethinborder'>
 <tr class='tdthinborder2'><td class='erro' align='center'>".GetLangVar('erro9')."</td></tr>";
	foreach ($dupsnome as $tid => $tn){
	 echo "
  <tr><td class='tdformnotes'><i>$tn</i></td></tr>";
    }
		echo "
</table>
<br>";
		$erro++;
	}

$qq= "SELECT * FROM Traits WHERE TraitID='".$parenttraitid."'";
$res = mysql_query($qq,$conn);
$rr=mysql_fetch_assoc($res);
$tipo = $rr['TraitTipo'];

	//se parentid for categoria e trait nao for estado nao permitir o cadastro
if ($tipo=='Variavel|Categoria' && $traitkind!='Estado') { 
	echo "
<br>
<table cellpadding=\"7\" align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('erro12')."</td></tr>
</table>
<br>";
	$erro++;
}

//se for estado e parent nao for categoria, nao permitir
if ($traitkind=='Estado' && $tipo!='Variavel|Categoria') { 
	echo "
<br>
<table cellpadding=\"7\" align='center' class='erro'>
  <tr><td class='tdsmallbold' align='center'>".GetLangVar('erro14')."</td></tr>
</table>
<br>";
		$erro++;
	}
	
//checar se um  registro semelhante ja nao existe e solicitar confirmacao do cadastro
$qq= "SELECT * FROM Traits WHERE TraitID='".$traitid."'";
$res = mysql_query($qq,$conn);
$rr=mysql_fetch_assoc($res);
if (!isset($namechecked) && $traitname!=$rr['TraitName']) {
		$wordsinname = explode(" ",$traitname);
		$ferro=0;
		foreach ($wordsinname as $key => $value) {
			$value = trim($value);
			if (strlen($value)>3) {
				if (!empty($traitid)) {
					$qq = "SELECT * FROM Traits WHERE TraitName LIKE '%$value%' AND TraitTipo='$ttttipo' AND TraitID!='$traitid'";
				} else {
					$qq = "SELECT * FROM Traits WHERE TraitName LIKE '%$value%' AND TraitTipo='$ttttipo'";
				}
				$rr = mysql_query($qq,$conn);
				$nr = mysql_numrows($rr);
				if ($nr>0) {
					$ferro++;
					$erro++;
				}
			}
		}
		if ($ferro>0) {
			echo "
<br>
<table cellpadding=\"7\" align='center' class='myformtable'>
<thead>
  <tr ><td colspan=100% >".GetLangVar('erro13')."</td></tr>
  <tr class='subhead'>
    <td class='tdsmallbold'>".GetLangVar('nameclasse')."</td>
    <td class='tdsmallbold'>".GetLangVar('namenome')."</td>
    <td class='tdsmallbold'>".GetLangVar('namedefinicao')."</td>
  </tr>
</thead>
<tbody>";
foreach ($wordsinname as $key => $value) {
		$value = trim($value);
		if (strlen($value)>3) {
			//echo $traitid;
			if (!empty($traitid)) {
				$qq = "SELECT * FROM Traits WHERE TraitName LIKE '%$value%' AND TraitID!='".$traitid."'";
			} else {
				$qq = "SELECT * FROM Traits WHERE TraitName LIKE '%$value%'";
			}
			$rr = mysql_query($qq,$conn);
			$nr = mysql_numrows($rr);
			if ($nr>0) {
				while ($row = mysql_fetch_assoc($rr)) {
					if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;}$bgi++;
					echo "
  <tr bgcolor = '".$bgcolor."'>
    <td class='tdformnotes'>".$row['PathName']."</td>
    <td class='tdformnotes'><b>".$row['TraitName']."</b></td>
    <td class='tdformnotes'>".$row['TraitDefinicao']."</td>
  </tr>";
				}
			}
		}
	}
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else{$bgcolor = $linecolor1 ;}$bgi++;
echo "
<form action='traitsregister-exec.php' method='post'>
  <input type='hidden' name='traitkind' value='$traitkind' />
  <input type='hidden' name='traittipo' value='$traittipo' />
  <input type='hidden' name='traitunit' value='$traitunit' />
  <input type='hidden' name='traitname' value='$traitname' />
  <input type='hidden' name='traitdefinicao' value='$traitdefinicao' />
  <input type='hidden' name='traitname_english' value='$traitname_english' />
  <input type='hidden' name='traitdefinicao_english' value='$traitdefinicao_english' />
  <input type='hidden' name='parenttraitid' value='$parenttraitid' />
  <input type='hidden' name='traiticone' value='$traiticone' />
  <input type='hidden' name='traitunit' value='$traitunit' />
  <input type='hidden' name='parentform' value='$parentform' />
  <input type='hidden' name='traitimgscale' value='$traitimgscale' />
  <input type='hidden' name='namechecked' value='1' />
  <input type='hidden' name='traitid' value='$traitid' />
  <input type='hidden' name='traitmulval' value='$traitmulval' />
  <input type='hidden' name='ispopup' value='$ispopup' />
<tr bgcolor = '".$bgcolor."'>
  <td colspan='2' align='center'>
    <input type='submit' value='".GetLangVar('nameconfirmar')."' class='bsubmit' />
  </td>
</form>";
if ($traitkind=='Classe' || $traitkind=='Estado') {
	echo "
<form action='traitsclasstate-exec.php' method='post'>";
} else {
	echo "
<form action='traitsvar-exec.php' method='post'>";
}
echo "
<input type='hidden' name='traitkind' value='$traitkind' />
<input type='hidden' name='traittipo' value='$traittipo' />
<input type='hidden' name='traitunit' value='$traitunit' />
<input type='hidden' name='traitname' value='$traitname' />
<input type='hidden' name='traitdefinicao' value='$traitdefinicao' />
<input type='hidden' name='traitname_english' value='$traitname_english' />
<input type='hidden' name='traitdefinicao_english' value='$traitdefinicao_english' />
<input type='hidden' name='parenttraitid' value='$parenttraitid' />
<input type='hidden' name='traiticone' value='$traiticone' />
<input type='hidden' name='traitunit' value='$traitunit' />
<input type='hidden' name='parentform' value='$parentform' />
<input type='hidden' name='traitimgscale' value='$traitimgscale' />
<input type='hidden' name='traitid' value='$traitid' />
<input type='hidden' name='traitmulval' value='$traitmulval' />
<input type='hidden' name='ispopup' value='$ispopup' />
  <td colspan='2' align='center'><input type='submit' value='".GetLangVar('namecorrigir')."' class='breset' /></td>
</form>
</tr>
</tbody>
</table>
<br>";
}

} //end if name is checked!

if (($traittipo=='Variavel|Categoria' || $traittipo=='Variavel|Cores') && empty($traitmulval)) {
	echo "
<br>
  <table cellpadding=\"7\"  align='center' class='erro'>
    <tr><td class='tdsmallbold' align='center'>".GetLangVar('erro15')."</td></tr>
  </table>
<br>
";
	$erro++;
}

//proceder para cadastro
if ($erro==0) {
	$traitname = trim($traitname);
	$traitname = ucfirst(strtolower($traitname));
	if (($traittipo=='Variavel|Categoria' || $traittipo=='Variavel|Cores') && $traittipo!='Variavel|Pelos') {
		$tipocor = 'Variavel|Categoria';
		$fieldsaskeyofvaluearray = array(
			'ParentID' => $parenttraitid,
			'TraitName' => $traitname,
			'TraitName_English'  => $traitname_english,
			'TraitTipo' => $tipocor,
			'TraitDefinicao' => $traitdefinicao,
			'TraitDefinicao_English' => $traitdefinicao_english,
			'TraitUnit' => $traitunit,
			'TraitIcone' => $traiticone,
			'MultiSelect' => $traitmulval
			);
	} elseif ($traittipo!='Variavel|Pelos') {
		$fieldsaskeyofvaluearray = array(
			'ParentID' => $parenttraitid,
			'TraitName' => $traitname,
			'TraitName_English'  => $traitname_english,
			'TraitTipo' => $traittipo,
			'TraitDefinicao' => $traitdefinicao,
			'TraitDefinicao_English' => $traitdefinicao_english,
			'TraitUnit' => $traitunit,
			'TraitIcone' => $traiticone,
			'Keywords' => $traitimgscale
			);
	}
	//se editando
	if (!empty($traitid) && $traitid!=GetLangVar('nameselect')) {
			//echopre($fieldsaskeyofvaluearray);
			CreateorUpdateTableofChanges($traitid,'TraitID','Traits',$conn);
			$newtrait = UpdateTable($traitid,$fieldsaskeyofvaluearray,'TraitID','Traits',$conn);
			if (!$newtrait) {
				$erro++;
			}
	//se criando
	} else {
		$newtrait = InsertIntoTable($fieldsaskeyofvaluearray,'TraitID','Traits',$conn);
	}
	updatesingletraitpath($newtrait,$conn);

	if ($newtrait && $traittipo=='Variavel|Cores') {
		$colorarr = array('Amarelo', 'Azul', 'Bege', 'Branco', 'Ciano', 'Cinza', 'Claro', 'Creme', 'Escuro', 'Laranja', 'Lilas', 'Magenta', 'Marrom', 'Negro', 'Ocre', 'Rosa', 'Translucido', 'Verde', 'Vermelho', 'Violeta');
		$colorengl = array('Yellow', 'Blue', 'Beige', 'White', 'Cyan', 'Gray', 'Light', 'Cream', 'Dark', 'Orange', 'Lilac', 'Magenta', 'Brown', 'Black', 'Ocher', 'Pink', 'Translucent', 'Green', 'Red', 'Purple');
		foreach($colorarr as $kk => $vv) {
			$fieldsaskeyofvaluearray = array(
				'ParentID' => $newtrait,
				'TraitName' => $vv,
				'TraitName_English' => $colorengl[$kk],
				'TraitTipo' => 'Estado',
				'TraitDefinicao' => $vv,
				'TraitDefinicao_English' => $colorengl[$kk]);
				$newcolor = InsertIntoTable($fieldsaskeyofvaluearray,'TraitID','Traits',$conn);
				updatesingletraitpath($newcolor,$conn);
		}
	}

	if ($traittipo=='Variavel|Pelos') {
		$qq = "SELECT * FROM Traits WHERE TraitName='".GetLangVar('traittipo6')."' AND TraitTipo='Classe' AND ParentID='".$parenttraitid."'";
		$rr = mysql_query($qq,$conn);
		$nr = mysql_numrows($rr);
		if ($nr>0) {
			$rt = mysql_fetch_assoc($rr);
			$parentt = $rt['TraitID'];
		} else {
			$fieldsaskeyofvaluearray = array(
				'TraitName' => GetLangVar('traittipo6'),
				'TraitName_English' => 'Indument',
				'TraitTipo' => 'Classe',
				'TraitDefinicao' => 'Variáveis relativas ao indumento',
				'TraitDefinicao_English' => 'Variables related to the indument'
				);
				$parentt = InsertIntoTable($fieldsaskeyofvaluearray,'TraitID','Traits',$conn);
				updatesingletraitpath($parentt,$conn);
		}
		$tipocor = 'Variavel|Categoria';
		//primeiro tipo de pelo
		$tipoport = array('Aracnóide', 'Bífido', 'Dendrítico', 'Escabro', 'Escamas lepidotas', 'Escamas simples', 'Espinhos ou acúleos', 'Estrelado', 'Malpiguiáceos', 'Glandular', 'Papilas', 'Simples adpresso', 'Simples ereto', 'Simples uncinado', 'Trífido');
		$tipoengl = array('Aracnoid', 'Bifid', 'Dendritic', 'Scabrous', 'Lepidote scales', 'Simple scales', 'Spines or aculeous', 'Stellate', 'Malpighiaceous', 'Glandular', 'Papillae', 'Simple adpressed', 'Simple erect', 'Simple uncinate', 'Trifid');
		$defport = array("Tricomas enrolados, geralmente brancos, formando um emaranhado que lembra uma teia de aranha", "Tricoma com duas ramificações saindo do mesmo ponto (ou nó)", "Como um árvore, com várias ramificações saindo de pontos diferentes", "A superfície fica áspera como uma lixa", "Escamas lepidotas são tricomas que lembram um guardachuva e que vistos de cima são circulares", "Escamas são tricomas achatados e foliáceos", "Tricomas rígidos, pungentes", "Tricoma com mais de três ramificações saindo do mesmo ponto", "Em forma de T, i.e. com duas ramificações saindo do mesmo ponto e paralelas à superfície conhecido como tricomas Malpiguiáceos", "Tricomas geralmente simples que apresentam uma glândula no ápice", "Papilas são projeções digitiformes (em forma de dedos) da epiderme, geralmente pequenas", "Tricoma simples, não divido adpresso (deitado)", "Tricoma simples, não divido ereto", "Simples com o ápice em forma de gancho", "Tricoma com três ramificações saindo do mesmo ponto (ou nó)");
		$defengl = array("Coiled trichomes, usually white, forming a tangle that resembles a spider web", "Trichomes with 2 ramifications born leaving from the same point (or node)", "As a tree with many branches coming out from different points", "The surface is rough like sandpaper", "Lepidote scales are trichomes that are shaped like an umbrella and have a circular form when seen from above", "Scales are trichomes flattened and foliaceous", "Trichomes rigid, pungent", "Trichome with three branches coming out over the same point", "T-shaped, i.e. with two branches leaving from same point and parallel to the surface known as Malpighiaceous trichomes ", "Usually simple trichomes that have a gland at the apex", "A projection from a cell, usually of the epidermis , often regarded as a kind of trichome. Papillae are often swollen and covered with wax", "Simple adpressed trichomes, i.e. trichomes not divided and laying closer to the surface", "Trichomes simple, undivided erect", "Simple with hook-shaped apex", "Trichome with three branches arising from the same point (or node)");
		$deftrport = "Tipo de trichoma";
		$deftrengl = "Type of trichomes";
		$fieldsaskeyofvaluearray = array(
				'ParentID' => $parentt,
				'TraitName' => $traitname." ".GetLangVar('nametipo'),
				'TraitName_English' => $traitname_english." Type",
				'TraitTipo' => $tipocor,
				'MultiSelect' => 'Sim',
				'TraitDefinicao' => $deftrport,
				'TraitDefinicao_English' => $deftrengl);
		$newpelostipo = InsertIntoTable($fieldsaskeyofvaluearray,'TraitID','Traits',$conn);
		updatesingletraitpath($newpelostipo,$conn);
		foreach($tipoport as $kk => $vv) {
			$defpt = $defport[$kk];
			$defeg = $defengl[$kk];
			$ingtr = $tipoengl[$kk];
			$fieldsaskeyofvaluearray = array(
				'ParentID' => $newpelostipo,
				'TraitName' => $vv,
				'TraitName_English' => $ingtr,
				'TraitDefinicao' => $defpt,
				'TraitDefinicao_English' => $defeg,
				'TraitTipo' => 'Estado');
			$newtt = InsertIntoTable($fieldsaskeyofvaluearray,'TraitID','Traits',$conn);
			updatesingletraitpath($newtt,$conn);
		}
		//segundo densidade
		$defpt = "Densidade do indumento na superfície observada";
		$defeng = "Density of the indument in the observed surface";
		$fieldsaskeyofvaluearray = array(
				'ParentID' => $parentt,
				'TraitName' => $traitname." ".GetLangVar('namedensidade'),
				'TraitName_English' => $traitname_english." Density",
				'TraitTipo' => $tipocor,
				'TraitDefinicao' => $defpt,
				'TraitDefinicao_English' => $defeng);
		$newpelosdens = InsertIntoTable($fieldsaskeyofvaluearray,'TraitID','Traits',$conn);
		updatesingletraitpath($newpelosdens,$conn);
		$denspt = array('Denso', 'Esparso', 'Glabrescente', 'Glabro', 'Recobrindo a superfície');
		$denseng = array('Dense', 'Sparse', 'Glabrescent', 'Glabrous', 'Covering the surface');
		$densdefpt = array("Tricomas densamentes distribuídos, mas a superfície visível", "Tricomas evidentes esparsos, mas uniformemente distribuídos", "Pêlos isolados, não uniformemente distribuídos", "Não tem nenhum indumento", "Indumento tão denso que não dá para ver a superfície");
		$densdefeng = array("Trichomes densely distributed, but leaving the surface visible", "Trichomes sparsely and uniformly distributed on the surface", "Isolated trichomes that are not uniformly distributed", "It has no indumentum", "Indument is so dense that the surface can not be visualized");
		foreach($denspt as $kk => $vv) {
			$defp = $densdefpt[$kk];
			$defe = $densdefeng[$kk];
			$nengl =  $denseng[$kk];
			$fieldsaskeyofvaluearray = array(
				'ParentID' => $newpelosdens,
				'TraitName' => $vv,
				'TraitName_English' => $nengl,
				'TraitTipo' => 'Estado',
				'TraitDefinicao' => $defp,
				'TraitDefinicao_English' => $defe);
			$newdens = InsertIntoTable($fieldsaskeyofvaluearray,'TraitID','Traits',$conn);
			updatesingletraitpath($newdens,$conn);
		}
		//segundo tamanho
		$ddpt = "Tamanho relativo do indumento";
		$dden = "Relative indument size";
		$fieldsaskeyofvaluearray = array(
				'ParentID' => $parentt,
				'TraitName' => $traitname." ".GetLangVar('nametamanho'),
				'TraitName_English' => $traitname_english." Size",
				'TraitDefinicao' => $ddpt,
				'TraitDefinicao_English' => $dden,
				'TraitTipo' => $tipocor);
		$newpelossize = InsertIntoTable($fieldsaskeyofvaluearray,'TraitID','Traits',$conn);
		updatesingletraitpath($newpelossize,$conn);
		$indsizept =  array("Diminuto (<1 mm)", "Longos (> 3 mm)", "Pequeno (1-3 mm)");
		$indsizeengl =  array("Diminute (<1 mm)", "Long (> 3 mm)", "Short (1-3 mm)");
		foreach($indsizept as $kk => $vv) {
			$defpt = $vv;
			$defeng = $indsizeengl[$kk];
			$fieldsaskeyofvaluearray = array(
				'ParentID' => $newpelossize,
				'TraitName' => $vv,
				'TraitName_English' => $defeng,
				'TraitTipo' => 'Estado',
				'TraitDefinicao' => $defpt,
				'TraitDefinicao_English' => $defeng);
			$newsize = InsertIntoTable($fieldsaskeyofvaluearray,'TraitID','Traits',$conn);
			updatesingletraitpath($newsize,$conn);
		}
		if ($newpelossize) {
			$newtrait = true;
		}
	}
	//confirmacao de cadastro ou erro
	if ($newtrait) {
	if (!empty($trainame_val)) {
			 		echo "
				<form >
				<input type='hidden' id='ttid' value='$newtrait' >
				<input type='hidden' id='txtid' value='$txt' >

				<script language=\"JavaScript\">
				setTimeout(
					function() {
						passnewidandtxtoinputfield('ttid','txtid','".$traitid_val."','".$traitname_val."')
						}
						,0.0001);
				</script>
				</form>";
} else {
		echo "
<br>
<table align='center' class='success' cellpadding=\"5\">
<tr><td>".GetLangVar('sucesso1')."</td></tr>
<tr>
<td>
  <form action='traits-form.php' method='post'>
    <input type='hidden' name='ispopup' value='$ispopup' />
    
    <input type='submit' class='bsubmit' value='".GetLangVar('namenova')." ".GetLangVar('nametraits')."' />
  </form>
</td></tr>
</table>
<br>
";

}


} 
else {
		echo "
<br>
<table align='center' class='erro' cellpadding=\"7\">
<tr><td>".GetLangVar('erro2')."</td></tr>
<tr>
  <td>";
if ($traitkind=='Classe' || $traitkind=='Estado') {
		echo "
    <form action='traitsclasstate-exec.php' method='post'>";
} else {
	echo "
    <form action='traitsvar-exec.php' method='post'>";
}
echo "
  <input type='hidden' name='traitkind' value='$traitkind' />
  <input type='hidden' name='traittipo' value='$traittipo' />
  <input type='hidden' name='traitunit' value='$traitunit' />
  <input type='hidden' name='traitname' value='$traitname' />
  <input type='hidden' name='traitname_english' value='$traitname_english' />
  <input type='hidden' name='traitdefinicao_english' value='$traitdefinicao_english' />
  <input type='hidden' name='traitdefinicao' value='$traitdefinicao' />
  <input type='hidden' name='parenttraitid' value='$parenttraitid' />
  <input type='hidden' name='traiticone' value='$traiticone' />
  <input type='hidden' name='traitunit' value='$traitunit' />
  <input type='hidden' name='parentform' value='$parentform' />
  <input type='hidden' name='traitimgscale' value='$traitimgscale' />
  <input type='hidden' name='traitid' value='$traitid' />
  <input type='hidden' name='traitmulval' value='$traitmulval' />
  <input type='hidden' name='ispopup' value='$ispopup' />
    <input type='hidden' name='traitid_val' value='$traitid_val' />
    <input type='hidden' name='traitname_val' value='$traitname_val' />  
  <input type='submit' value='".GetLangVar('namecorrigir')."' />
</form>
</td>
</tr>
</table>
<br>
";
  }
} //end if erro=0
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>