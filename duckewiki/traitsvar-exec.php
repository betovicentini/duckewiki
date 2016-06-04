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
$menu = FALSE;
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />");

$which_java = array();
$title = 'Variável Definição';
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
	$tt = explode("|",$traittipo);
	$traitkind = $tt[0];
	$nnn = $tt[1];
	if ($traittipo=='Variavel|Categoria') {
		$qt = "SELECT GROUP_CONCAT(TraitName SEPARATOR ';') AS categorias FROM Traits WHERE ParentID='".$traitid."' ORDER BY TraitName";
		$rt = mysql_query($qt,$conn);
		$rwt = mysql_fetch_assoc($rt);
		$categoriasvariacao = $rwt['categorias'];
	}
	$titulo = GetLangVar('nameeditar')." ".strtolower($traitkind);
	if ($traitkind!=$traittipo) { $titulo .=  " (".strtolower($nnn).")";}
} 
else {
	$titulo = GetLangVar('namecadastrar')." ".strtolower($traitkind);
}

echo "
<br>
<table class='myformtable' cellpadding=\"7\" align='center'>
<thead>
  <tr><td >".$titulo."</td></tr>
</thead>
<tbody>";
if (empty($traitid) || $traitid==GetLangVar('nameselect')) { //start if editing tipo change option not allowed
//tipo do novo caractere
echo "
<form action='traitsvar-exec.php' method='post'>
  <input type='hidden' name='traitkind' value='$traitkind' />
  <input type='hidden' name='ispopup' value='$ispopup' />
    <input type='hidden' name='traitid_val' value='$traitid_val' />
    <input type='hidden' name='traitname_val' value='$traitname_val' />  
";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = $bgcolor>
  <td>
    <table align='left' >
      <tr>
        <td class='tdsmallbold'>".GetLangVar('nametipo')."</td>
        <td class='tdformnotes'>
          <table>
            <tr>
              <td><input type='radio' name='traittipo'";
				if ($traittipo=='Variavel|Categoria') {echo " checked ";}
				echo " value='Variavel|Categoria' onchange='this.form.submit();' /></td>
              <td>".GetLangVar('traittipo1')."</td><td align='left'><img src=\"icons/icon_question.gif\" ";
				$help = strip_tags(GetLangVar('traittipo1_desc'));
				echo " onclick=\"javascript:alert('$help');\" /></td>
            </tr>
          </table>
        </td>
        <td class='tdformnotes'>
          <table>
            <tr>
              <td><input type='radio' name='traittipo'";
				if ($traittipo=='Variavel|Quantitativo') {echo " checked ";}
				echo " value='Variavel|Quantitativo' onchange='this.form.submit();' /></td><td>".GetLangVar('traittipo2')."</td>
              <td align='left'><img src=\"icons/icon_question.gif\" ";
				$help = strip_tags(GetLangVar('traittipo2_desc'));
				echo " onclick=\"javascript:alert('$help');\" /></td>
            </tr>
          </table>
        </td>
        <td class='tdformnotes'>
          <table>
            <tr>
              <td><input type='radio' name='traittipo'";
				if ($traittipo=='Variavel|Imagem') {echo " checked ";}
				echo " value='Variavel|Imagem' onchange='this.form.submit();' /></td><td>".GetLangVar('traittipo4')."</td>
              <td align='left' ><img src=\"icons/icon_question.gif\" ";
				$help = strip_tags(GetLangVar('traittipo4_desc'));
				echo " onclick=\"javascript:alert('$help');\" /></td>
            </tr>
          </table>
        </td>
        <td class='tdformnotes'>
          <table>
            <tr>
              <td><input type='radio' name='traittipo'";
				if ($traittipo=='Variavel|Texto') {echo " checked ";}
				echo " value='Variavel|Texto' onchange='this.form.submit();' /></td><td>".GetLangVar('traittipo5')."</td>
              <td align='left' ><img src=\"icons/icon_question.gif\" ";
				$help = strip_tags(GetLangVar('traittipo5_desc'));
				echo " onclick=\"javascript:alert('$help');\" /></td>
            </tr>
          </table>
        </td>
        <td class='tdformnotes'>
          <table>
            <tr>
              <td><input type='radio' name='traittipo'";
				if ($traittipo=='Variavel|Cores') {echo " checked ";}
				echo " value='Variavel|Cores' onchange='this.form.submit();' /></td><td>".GetLangVar('traittipo3')."</td>
              <td align='left' ><img src=\"icons/icon_question.gif\" ";
				$help = strip_tags(GetLangVar('traittipo3_desc'));
				echo " onclick=\"javascript:alert('$help');\" /></td>
            </tr>
          </table>
        </td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td class='tdformnotes'>
            <table>
              <tr>
                <td><input type='radio' name='traittipo'";
				if ($traittipo=='Variavel|Pelos') {echo " checked ";}
				echo " value='Variavel|Pelos' onchange='this.form.submit();' /></td>
                <td>".GetLangVar('traittipo6')."</td>
                <td align='left' ><img src=\"icons/icon_question.gif\" ";
				$help = strip_tags(GetLangVar('traittipo6_desc'));
				echo  " onclick=\"javascript:alert('$help');\" />
                </td>
              </tr>
            </table>
          </td>
          <td class='tdformnotes'>
            <table>
              <tr>
                <td><input type='radio' name='traittipo'";
				if ($traittipo=='Variavel|Taxonomy') {echo " checked ";}
				echo " value='Variavel|Taxonomy' onchange='this.form.submit();' /></td>
                <td>LinkTaxonomico</td>
                <td align='left' ><img src=\"icons/icon_question.gif\" ";
				$help = "Este tipo de variável permite a seleção de nomes taxonômicos como variação. Útil para fazer links taxonômicos entre organismos";
				echo  " onclick=\"javascript:alert('$help');\" />
                </td>
              </tr>
            </table>
          </td>
          <td class='tdformnotes'>
            <table>
              <tr>
                <td><input type='radio' name='traittipo'";
				if ($traittipo=='Variavel|Pessoa') {echo " checked ";}
				echo " value='Variavel|Pessoa' onchange='this.form.submit();' /></td>
                <td>Pessoas</td>
                <td align='left' ><img src=\"icons/icon_question.gif\" ";
				$help = "Este tipo de variável permite a seleção de nomes de pessoas como variação. ";
				echo  " onclick=\"javascript:alert('$help');\" />
                </td>
              </tr>
            </table>
          </td>
          <td class='tdformnotes'>
            <table>
              <tr>
                <td><input type='radio' name='traittipo'";
				if ($traittipo=='Variavel|LinkEspecimenes') {echo " checked ";}
				echo " value='Variavel|LinkEspecimenes' onchange='this.form.submit();' /></td>
                <td>Link Especimenes</td>
                <td align='left' ><img src=\"icons/icon_question.gif\" ";
				$help = "Este tipo de variável permite selecionar o identificador de especímenes, permitindo a criação de vínculos entre especímenes diferentes";
				echo  " onclick=\"javascript:alert('$help');\" />
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
</tr>
</form>
";
} //end if editing tipo change option not allowed
if (isset($traittipo)) {
echo "
<form enctype='multipart/form-data' action='traitsregister-exec.php' method='post'>
<input type='hidden' name='MAX_FILE_SIZE' value='100000000000' />
<input type='hidden' name='traittipo' value='$traittipo' />
<input type='hidden' name='traitid' value='$traitid' />
<input type='hidden' name='traitkind' value='$traitkind' />
<input type='hidden' name='ispopup' value='$ispopup' />
    <input type='hidden' name='traitid_val' value='$traitid_val' />
    <input type='hidden' name='traitname_val' value='$traitname_val' />

";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;

if ($traittipo=='Variavel|Pelos') {
	$valn = GetLangVar('namenome')." ".GetLangVar('namebase')."&nbsp;<img src=\"icons/icon_question.gif\" ";
	$help = strip_tags(GetLangVar('basename_help'));
    $valn .= " onclick=\"javascript:alert('$help');\">";
		} else {
	$valn = GetLangVar('namenome');
}

echo "
<tr bgcolor = $bgcolor>
  <td>
  <table align='left' >
    <tr>
      <td class='tdsmallbold'>".$valn."</td>
      <td class='tdsmallbold'><input type='text' size='50' name='traitname' value='$traitname' /></td>
      <td class='tdsmallbold' style='color:red'>em inglês</td>
      <td class='tdsmallbold' style='color:red'><input type='text' size='50' name='traitname_english' value='$traitname_english' /></td>
    </tr>
  </table>
  </td>
</tr>";

	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = $bgcolor>
  <td>
    <table align='left'>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('messagepertenceaclasse')."</td>
        <td class='tdformleft'>
          <select name='parenttraitid'>";
			if ($parenttraitid=='padrao') {
				echo "
            <option  selected value='padrao'>".GetLangVar('messagenenhumgrupo')."</option>";
			} elseif ($traitkind=='Classe') {
					echo "
            <option value='padrao'>".GetLangVar('messagenenhumgrupo')."</option>";
			} 
			if (empty($parenttraitid)) {
				echo "
            <option value='' >".GetLangVar('nameselect')."</option>";
			} elseif ($parenttraitid!='padrao') {
				$qq = "SELECT * FROM Traits WHERE TraitID='".$parenttraitid."'";
				$rr = mysql_query($qq,$conn);
				$row = mysql_fetch_assoc($rr);
				if ($row['PathName']!=$row['TraitName']) {
					$vvt = str_replace($row['TraitName'],'',$row['PathName']);
					$valt = $row['TraitName']." (".$vvt.")";
				} else {
					$valt = $row['TraitName'];
				}
				echo "
            <option selected value=".$row['TraitID'].">".$valt."</option>
            <option value='padrao'>".GetLangVar('messagenaopertence')."</option>";
			}
			echo "
            <option value=''>----</option>";
			if ($traitkind=='Variavel') {
				$filtro = "SELECT * FROM Traits WHERE TraitTipo='Classe' ORDER BY PathName";
			} 
			$res =  mysql_query($filtro,$conn);
			while ($aa = mysql_fetch_assoc($res)){
					$PathName = $aa['PathName'];
					$level = $aa['MenuLevel'];
					if ($aa['PathName']!=$aa['TraitName']) {
						$vvt = str_replace($aa['TraitName'],'',$aa['PathName']);
						$valt = $aa['TraitName']." (".$vvt.")";
					} else {
						$valt = $aa['TraitName'];
					}
					if ($level==1) {
						$espaco='';
						echo "
            <option class='optselectdowlight' value=".$aa['TraitID'].">".$valt."</option>";
					} else {
						$espaco = str_repeat('&nbsp;',$level);
						$espaco = $espaco.str_repeat('-',$level-1);
						echo "
            <option value=".$aa['TraitID'].">$espaco".$valt."</option>";
					}
			}
echo "
          </select>
        </td>
      </tr>
    </table>
  </td>
</tr>
";
if ($traittipo!='Variavel|Pelos') {

if ($traittipo=='Variavel|Quantitativo') { //se for quantitativo
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = $bgcolor>
  <td>
    <table align='left' >
      <tr>
        <td class='tdsmallbold'>".GetLangVar('nameunidade')."&nbsp;<img src=\"icons/icon_question.gif\" ";
			$help = strip_tags(GetLangVar('traitsunidadehelp'));
			echo " onclick=\"javascript:alert('$help');\">&nbsp;&nbsp;</td>";
			$qq = "SELECT * FROM VarLang WHERE VariableName LIKE '%traitunit%' ORDER BY '$lang' ASC";
			$res = mysql_query($qq,$conn);
			$varn = array();
			while ($row=mysql_fetch_assoc($res)) {
				$varname = $row[$lang];
				$varn[] = $varname;
				echo "
        <td class='tdformnotes'><input type='radio' name='traitunit' ";
				if ($traitunit==$varname) {echo " checked ";}
				echo "value='".$varname."' />&nbsp;".$varname."&nbsp;</td>";
			}
			$qq = "SELECT DISTINCT TraitUnit FROM Traits WHERE TraitUnit IS NOT NULL AND TraitUnit!='' ORDER BY TraitUnit ASC";
			$res = @mysql_query($qq,$conn);
			$nres = @mysql_numrows($res);
			if ($nres>0) {
				while ($row=mysql_fetch_assoc($res)) {
					$varname = $row['TraitUnit'];
					if (!in_array($varname,$varn)) {
						echo "
        <td class='tdformnotes'><input type='radio' name='traitunit' ";
						if ($traitunit==$varname) {echo " checked ";}
						echo "value='".$varname."' />&nbsp;".$varname."&nbsp;</td>";
					}
				}
			}
			echo "
        <td class='tdformnotes'><input class='tdformnotes' type='text' size=6 value='".$traitunitnew."' name='traitunitnew' />&nbsp;&nbsp;".strtolower(GetLangVar('namenova'))."&nbsp;&nbsp;</td>
      </tr>
    </table>
  </td>
</tr>";
}
if ($traittipo=='Variavel|Imagem') { //se for uma imagem entao abre um campo para palavras chaves
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = $bgcolor>
  <td>
    <table align='left' >
      <tr>
        <td class='tdsmallbold'>".GetLangVar('namekeywords')."*</td>
        <td><textarea name='traitimgscale' cols=30 rows=2 wrap=SOFT>$traitimgscale</textarea></td>
        <td class='tdformnotes'>*".GetLangVar('messagekeywords')."</td>
      </tr>
    </table>
  </td>
</tr>
";
} 
if ($traittipo=='Variavel|Categoria' || $traittipo=='Variavel|Cores') { //se for categoria
	if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = $bgcolor>
  <td>
    <table align='left' >
      <tr>
        <td class='tdsmallbold'>".GetLangVar('messageallowmultival')."&nbsp;<img src=\"icons/icon_question.gif\" ";
          $help = strip_tags(GetLangVar('messageallowmultival_help'));
          echo " onclick=\"javascript:alert('$help');\">&nbsp;&nbsp;</td>
        <td class='tdformnotes'><input type='radio' name='traitmulval' ";
          if ($traitmulval=='Sim') { echo " checked ";}
          echo " value='Sim' />&nbsp;".GetLangVar('namesim')."</td>
        <td class='tdformnotes'><input type='radio' name='traitmulval' ";
          if ($traitmulval=='Nao') { echo " checked ";}
          echo " value='Nao' />&nbsp;".GetLangVar('namenao')."</td>
      </tr>
    </table>
  </td>
</tr>
";
if ($traitid>0) {
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
	echo "
<tr bgcolor = $bgcolor>
  <td class='tdformnotes'>CATEGORIAS CADASTRADAS: ".$categoriasvariacao."</td>
</tr>";
}
} 

//trait definicao
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = $bgcolor>
  <td>
    <table align='left' >
      <tr>
        <td class='tdsmallbold'>".GetLangVar('namedefinicao')."</td>
        <td class='tdsmallbold'><textarea name='traitdefinicao' cols=40 rows=4 wrap=SOFT>$traitdefinicao</textarea></td>
        <td class='tdsmallbold' style='color:red'>em inglês</td>
        <td class='tdsmallbold' style='color:red'><textarea name='traitdefinicao_english' cols=40 rows=4 wrap=SOFT>$traitdefinicao_english</textarea></td>
      </tr>
    </table>
  </td>
</tr>";
if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = $bgcolor>
<td>
  <table align='left' >
    <tr>
      <td class='tdsmallbold'>".GetLangVar('messagetraiticon')."</td>";
		if (!empty($traiticone)) {
			echo "
      <td align='right'><img src='img/traits_icons/$traiticone' height=100 border=0></td>";
			$fileinfo = getimagesize("img/traits_icons/".$traiticone);
			//echo "<table border=1 cellspacing=\"5\" align='center'><tr>";
			$filename = "<b>".GetLangVar('namefile')."</b>: <i>$traiticone</i> <br>";
			$filename = $filename." "."<b>".GetLangVar('namesize')."</b>: ".@round(filesize("img/traits_icons/".
			$traiticone))." bytes (".$fileinfo[0]."x".$fileinfo[1].")<br>";
			echo "
      <td class='tdformnotes' align='left'>".$filename."<b>".GetLangVar('namefolder')."</b>img/traits_icons/</td> </tr>";
		}
echo "
      <input type='hidden' name='traiticone' value='".$traiticone."' />
      <input type='hidden' name='newimage' value='1' />
      <td>&nbsp;</td><td colspan=2><input type='file' size='20' name='novaimg' /></td></tr>
    </table>
  </td>
</tr>
";

} // if traittipo==Variavel|Pelos


if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;} else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = $bgcolor>
<td>
  <table align='center' >
    <tr>
      <td align='center'><input type = 'submit'  class='bsubmit' value=".GetLangVar('namesalvar')." /></td>
</form>
<form action='traits-form.php' method='post'>
    <input type='hidden' name='traitid_val' value='$traitid_val' />
    <input type='hidden' name='traitname_val' value='$traitname_val' />
  <input type='hidden' name='ispopup' value='$ispopup' />
      <td align='center'><input type = 'submit'  class='breset' value=".GetLangVar('namevoltar')." /></td> 
</form> 
<form action='traits-delete.php' method='post'>
    <input type='hidden' name='traitid_val' value='$traitid_val' />
    <input type='hidden' name='traitname_val' value='$traitname_val' />
  <input type='hidden' name='ispopup' value='$ispopup' />
  <input type='hidden' name='traitid' value='".$traitid."'>
  <td align='center'><input type = 'submit'  class='borange' value=".GetLangVar('nameapagar')." /></td> 
</form> 
    </tr>
  </table>
</td>
</tr>";

} //end if traittipo isset

echo "
</tbody>
</table>";

$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>
