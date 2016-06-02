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

//CABECALHO
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
$title = 'Variável';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);


//SE EDITANDO, COLETA DADOS ARMAZENADOS
if ($traitid>0 && is_numeric($traitid)) {
	$qq = "SELECT * FROM Traits WHERE TraitID='".$traitid."'";
	$rr = mysql_query($qq,$conn);
	$row = mysql_fetch_assoc($rr);
	$parenttraitid = $row['ParentID'];
	$traitname = $row['TraitName'];
	$traitname_english = $row['TraitName_English'];
	$traittipo = $row['TraitTipo'];
	$traitdefinicao = $row['TraitDefinicao'];
	$traitdefinicao_english = $row['TraitDefinicao_English'];
	$traitunit = $row['TraitUnit'];
	$traiticone = $row['TraitIcone'];
	$traitmulval = $row['MultiSelect'];
	if ($traittipo=='Classe') {
		$traitkind = 'Classe';
	} else {
		if ($traittipo=='Estado') {
			$traitkind = 'Estado';
		} else {
			$tt = explode("|",$traittipo);
			$traitkind = $tt[0];
		}
	}
	$titulo = GetLangVar('nameeditar')." ".strtolower($traitkind);
	if ($traitkind!=$traittipo) { $titulo .=  " (".strtolower($traittipo).")";}
} 
else {
	$titulo = GetLangVar('namecadastrar')." ".strtolower($traitkind);
}

echo "
<br>
<table class='myformtable' cellpadding=\"7\" align='center'>
<thead>
  <tr><td colspan='100%'>".$titulo."</td></tr>
</thead>
<tbody>
<form enctype='multipart/form-data' action='traitsregister-exec.php' method='post'>
  <input type='hidden' name='traitkind' value='$traitkind' />
  <input type='hidden' name='traittipo' value='$traittipo' />
  <input type='hidden' name='traitunit' value='$traitunit' />
  <input type='hidden' name='traitid' value='$traitid' />
  <input type='hidden' name='ispopup' value='$ispopup' />
  
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td>
  <table align='left' >
    <tr>
      <td class='tdsmallbold'>".GetLangVar('namenome')."</td>
      <td class='tdsmallbold'><input type='text' size=50 name='traitname' value='".$traitname."' /></td>
      <td class='tdsmallbold' style='color:red'>em inglês</td>
      <td class='tdsmallbold' style='color:red'><input type='text' size='50' name='traitname_english' value='".$traitname_english."' /></td>
    </tr>
  </table>
  </td>
</tr>";

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
<td>
  <table align='left'>
    <tr>";
	//a quem pertence
	if ($traitkind=='Classe') {echo "
      <td class='tdsmallbold'>".GetLangVar('messagepertenceasubclasse')."</td>";}
	if ($traitkind=='Estado') {echo "
      <td class='tdsmallbold'>".GetLangVar('messagepertenceaocaractere')."</td>";}
echo "
      <td class='tdformleft'>
        <select name='parenttraitid' >";
			if ($parenttraitid=='padrao') {echo "
          <option  selected value='padrao'>".GetLangVar('messagenenhumgrupo')."</option>";
			} 
			elseif ($traitkind=='Classe') {echo "
          <option value='padrao'>".GetLangVar('messagenenhumgrupo')."</option>";
			} 
			if (empty($parenttraitid)) {
				echo "
          <option value=''>".GetLangVar('nameselect')."</option>";
			} 
			elseif ($parenttraitid!='padrao') {
				$qq = "SELECT * FROM Traits WHERE TraitID='".$parenttraitid."'";
				$rr = mysql_query($qq,$conn);
				$row = mysql_fetch_assoc($rr);
				echo "
          <option selected value='".$row['TraitID']."' >".$row['TraitName']." (".$row['PathName'].")</option>
          <option value='padrao'>".GetLangVar('messagenaopertence')."</option>";}
			echo "<option value=''>----</option>";
			if ($traitkind=='Classe') {
				$filtro = "SELECT * FROM Traits WHERE TraitTipo='Classe' ORDER BY PathName";
			} 
			if ($traitkind=='Estado') {
				$filtro = "SELECT * FROM Traits WHERE TraitTipo='Variavel|Categoria' OR TraitTipo='Classe' ORDER BY TraitName";
			} 
			$res = mysql_query($filtro,$conn);
			while ($aa = mysql_fetch_assoc($res)){
					$PathName = $aa['PathName'];
					$espaco = str_repeat('&nbsp;',$level);
					$espaco = $espaco.str_repeat('-',$level-1);
					echo "
          <option value='".$aa['TraitID']."'  >".$espaco.$aa['TraitName']." (".$aa['PathName'].")</option>";
			}
echo "
          </select>
        </td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
  <td>
    <table align='left' >
      <tr>
        <td class='tdsmallbold'>".GetLangVar('namedefinicao')."</td>
        <td class='tdsmallbold'><textarea name='traitdefinicao' cols=40 rows=4 wrap=SOFT>".$traitdefinicao."</textarea></td>
        <td class='tdsmallbold' style='color:red'>em inglês</td>
        <td class='tdsmallbold' style='color:red'><textarea name='traitdefinicao_english' cols=40 rows=4 wrap=SOFT>$traitdefinicao_english</textarea></td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
<td>
  <table align='left' >
    <tr>
      <td class='tdsmallbold'>".GetLangVar('messagetraiticon')."</td>";
		if (!empty($traiticone)) {
			echo "
      <td align='right'><img src='img/traits_icons/$traiticone' height='100' border='0' /></td>";
			$fileinfo = getimagesize("img/traits_icons/".$traiticone);
			//echo "<table border=1 cellspacing=\"5\" align='center'><tr>";
			$filename = "<b>".GetLangVar('namefile')."</b>: <i>$traiticone</i> <br>";
			$filename = $filename." "."<b>".GetLangVar('namesize')."</b>: ".@round(filesize("img/traits_icons/".
			$traiticone))." bytes (".$fileinfo[0]."x".$fileinfo[1].")<br>";
			echo "
      <td class='tdformnotes' align='left'>".$filename."<b>".GetLangVar('namefolder')."</b>img/traits_icons/</td> </tr>";
		}
echo "
      <input type='hidden' name='traiticone' value='$traiticone' />
      <input type='hidden' name='newimage' value='1' />
      <input type='hidden' name='MAX_FILE_SIZE' value='10000000' />
      <td>&nbsp;</td><td colspan='2'><input type='file' size='20' name='novaimg' /></td></tr>
    </table>
  </td>
</tr>
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."'>
<td>
  <table align='center' >
    <tr>
      <td align='center' colspan='100%'><input type = 'submit'  class='bsubmit' value=".GetLangVar('namecadastrar')." /></td>
</form>
<form action='traits-form.php' method='post'>
  <input type='hidden' name='ispopup' value='$ispopup' />
      <td align='center'><input type = 'submit'  class='breset' value=".GetLangVar('namereset')." /></td> 
</form> 
    </tr>
  </table>
  </td>
</tr>
</tbody>
</table>
";
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>