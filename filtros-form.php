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
if ($ispopup==1) {
	$menu = FALSE;
} else {
	$menu = TRUE;
}
$which_css = array(
"<link href='css/geral.css' rel='stylesheet' type='text/css' />",
"<link rel='stylesheet' type='text/css' href='css/cssmenu.css' />",
"<link rel='stylesheet' type='text/css' media='screen' href='javascript/tabber/tabber.css' >",
"<link rel='stylesheet' type='text/css' media='screen' href='css/autosuggest.css' >"
);


$which_java = array(
"<script type='text/javascript' src='css/cssmenuCore.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOns.js'></script>",
"<script type='text/javascript' src='css/cssmenuAddOnsItemBullet.js'></script>",
"<script type='text/javascript' src='javascript/ajax_framework.js'></script>",
"<script type='text/javascript' src='javascript/jquery-latest.js'></script>",
"<script type='text/javascript'> $(document).ready(function(){ $('.toggle_container').hide(); $('h2.trigger').click(function(){ $(this).toggleClass('active').next().slideToggle('slow'); }); });</script>",
"<script type='text/javascript' src='javascript/filterlist.js'></script>"
);
$title = 'Filtros';
$body = '';
FazHeader($title,$body,$which_css,$which_java,$menu);

if (empty($final)) {
if (!empty($nomesciid)) {
	$taxsearch = $nomesciid;
}
if (!empty($localid)) {
	$gazsearch = $localid;
}
unset($_SESSION['filtrospecids']);
echo "
<form name='finalform' action='filtros-form.php' method='post'>
  <input type=hidden name='ispopup' value='".$ispopup."' />
  <input type=hidden name='final' value='' />
<h2 class='trigger'><a href='#'>Por taxonomia</a></h2>
<div class='toggle_container'>
<div class='block'>
    <table>
      <tr>
        <input type='hidden'  name='taxsearch' value='".$taxsearch."' />
        <td class='tdsmallboldleft' >".GetLangVar('namenomecientifico')."</td>
        <td>"; autosuggestfieldval3('search-name-simple-search.php','nomesearch',$nomesearch,'nomeres','nomesciid',$nomesciid,true,60); echo "</td>
        <td class='tdformnotes' >*".GetLangVar('autosuggesttoselect')."</td>
      </tr>
        <tr>
        <td  class='tdsmallboldleft' ><input type='checkbox'  name='nodets' value='1'>&nbsp;Sem&nbsp;det</td>
        <td class='tdsmallboldleft' ><input type='checkbox'  name='morfodets' value='1'>&nbsp;Morfotipos</td>
        <td class='tdformnotes' >&nbsp</td>
      </tr>
    </table>
</div>
</div>
<h2 class='trigger'><a href='#'>Por localidade</a></h2>
<div class='toggle_container'>
<div class='block'>
<table>
<tr>
  <input type='hidden'  name='gazsearch' value='".$gazsearch."' />
  <td class='tdsmallboldleft'>Por ".strtolower(GetLangVar('namelocalidade'))."</td>
  <td>"; autosuggestfieldval3('search-locality-search.php','localnome',$localnome,'localres','localid',$localid,true,60); 
echo "</td>
  <td class='tdformnotes' >*".GetLangVar('autosuggesttoselect')."</td>
</tr>
<tr>
  <td >
    <table>
      <tr>
        <td class='tdsmallboldleft' >Por coordenadas</td>
        <td><img height=14 src=\"icons/icon_question.gif\" ";
		$help = strip_tags(GetLangVar('searchbycoord'));
		echo " onclick=\"javascript:alert('$help');\" /></td>
      </tr>
    </table>
 </td>
 <td>
     <table>
      <tr>
       <td>
        <table >
         <tr><td class='tdsmallboldcenter' colspan='100%'>NW</td></tr>
         <tr><td class='tdformnotes' align='right'>Latitude</td><td><input type='text' size='6' name='latno' value='$latno' /></td></tr>
         <tr><td class='tdformnotes' align='right'>Longitude</td><td><input type='text' size='6' name='longno' value='$longno' /></td></tr>
        </table>
       </td>
       <td>
        <table>
         <tr><td class='tdsmallboldcenter' colspan='100%'>SE</td></tr>
         <tr><td class='tdformnotes' align='right'>Latitude</td><td><input type='text' size='6' name='latse' value='$latse' /></td></tr>
         <tr><td class='tdformnotes' align='right'>Longitude</td><td><input type='text' size='6' name='longse' value='$longse' /></td></tr>
        </table>
       </td>
      </tr>
     </table>
  </td>
</tr>
</table>
</div>
</div>
<h2 class='trigger'><a href='#'>Dados de coletas</a></h2>
<div class='toggle_container'>
<div class='block'>    
<table cellpadding=\"3\">
  <tr>
    <td class='tdsmallboldleft'>".GetLangVar('namecoletores')."</td>
    <td align='left'>
      <table>
        <tr>
          <td>
            <select id='coletorform' name='coletoresids[]' multiple size='8' style='width: 200px;'>";
		  $qq = "SELECT DISTINCT ColetorID,addcolldescr(ColetorID) as ColetorTXT FROM Especimenes ORDER BY addcolldescr(ColetorID)";
		  $rr = mysql_query($qq,$conn);
		  while ($row = mysql_fetch_assoc($rr)) {
		  	$clid = $row['ColetorID'];
		  	if (@in_array($clid,$coletoresids)) {
				$selt = "selected";
		  	} else {
		  		$selt = '';
		  	}
			 echo "
           <option $selt value='".$row['ColetorID']."'>".$row['ColetorTXT']."</option>";
			}

echo "
          </select>
        </td>
<script type=\"text/javascript\">
<!--
var myfilter = new filterlist(document.getElementById('coletorform'));
//-->
</script> 
        <td >
          <table style='font-size: 0.7em;'>
            <tr><td colspan='3'>&nbsp;</td></tr>
            <tr>
              <td>Filtrar:</td>
              <td colspan='2'><input name='regexp' onKeyUp=\"myfilter.set(this.value);\" size='30'/></td>
            </tr>
            <tr>
              <td></td>
              <td><input type='button' onclick=\"myfilter.reset();this.form.regexp.value=''\" value=\"Mostrar todos\" /></td>
              <td><input type='checkbox' name=\"toLowerCase\" onclick=\"myfilter.set_ignore_case(!this.checked);\" />&nbsp;Case sensitive</td></tr>
           </table>
       </td>
      </tr>
    </table>
    </td>
  </tr>
  <tr>
    <td class='tdsmallboldleft'>".GetLangVar('namenumber')."s&nbsp;de&nbsp;coleta:</td>
    <td>
      <table>
        <tr>
          <td><input type='text' name='colnumfrom' value='$colnumfrom' size='6' /></td>
          <td>&nbsp;".strtolower(GetLangVar('namefrom'))."&nbsp;</td>
          <td><input type='text' name='colnumto' value='$colnumto'size='6' /></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td class='tdsmallboldleft'>".GetLangVar('namedata')." ".strtolower(GetLangVar('namecoleta')).":</td>
    <td >
      <table>
        <tr>
          <td>
           <table cellpadding=\"3\" style='border: thin solid darkgray; border-collapse: collapse'>
            <tr>
              <td colspan='2' class='tdsmallbold'>".GetLangVar('namefrom')."</td>
            </tr>
            <tr>
             <td class='tdformnotes'>Ano</td><td><input type='text' size='4' name='anofrom' value='$anofrom' /></td>
            </tr>
            <tr>             
             <td class='tdformnotes'>Mes</td><td><input type='text' size='4' name='mesfrom' value='$mesfrom' /></td>
            </tr>
            <tr>             
             <td class='tdformnotes'>Dia</td><td><input type='text' size='4' name='diafrom' value='$diafrom' /></td>
            </tr>
          </table>
         </td>
         <td>
          <table cellpadding=\"3\" style='border: thin solid darkgray; border-collapse: collapse'>
            <tr>
              <td colspan='2'class='tdsmallbold'>".GetLangVar('nameto')."</td>
            </tr>
            <tr>
              <td class='tdformnotes'>Ano</td><td><input type='text' size='4' name='anoto' value='$anoto' /></td>
            </tr>
            <tr>                
              <td class='tdformnotes'>Mes</td><td><input type='text' size='4' name='mesto' value='$mesto' /></td>
            </tr>
            <tr>                
              <td class='tdformnotes'>Dia</td><td><input type='text' size='4' name='diato' value='$diato' /></td>              
            </tr>
          </table>
        </td>
      </tr>
      </table>
  </td>
</tr>
    <tr>
    <td class='tdsmallboldleft'>Número ".$herbariumsigla.":</td>
    <td>
      <table>
        <tr>
          <td><input type='radio' name='herbariumnum' value='1' />&nbsp;com número</td>
          <td><input type='radio' name='herbariumnum' value='2' />&nbsp;sem número</td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</div>
</div>
<h2 class='trigger'><a href='#'>Ambiental</a></h2>
<div class='toggle_container'>
<div class='block'>
<table>
  <tr>
    <td class='tdsmallboldleft' >".GetLangVar('namehabitat')."</td>
    <td>
      <select name='habitatid'>
        <option value=''>".GetLangVar('nameselect')."</option>";
		$qq = "SELECT * FROM Habitat WHERE HabitatTipo LIKE '%lass%' ORDER BY Habitat";
		$wr = mysql_query($qq,$conn);
		while ($ww = mysql_fetch_assoc($wr)) {
			if ($habitatid==$ww['HabitatID']) {
				$sh = 'selected';
			} else {
				$sh = '';
			}
			echo "
        <option $sh value='".$ww['HabitatID']."'>".$ww['Habitat']."</option>";
		}
echo "
      </select>
    </td>
  </tr>
</table>
</div>
</div>
<h2 class='trigger'><a href='#'>Outros critérios</a></h2>
<div class='toggle_container'>
<div class='block'>
  <table>
      <tr>
        <td class='tdsmallbold'>".GetLangVar('nameselect')." ".GetLangVar('namevariavel')."</td>
        <td class='tdformnotes' colspan='2'>
          <table style='font-size: 0.8em;'>
            <tr>
              <td colspan='4'>
                <select id='traitslist' name='traitid[]' multiple size='10' style='width: 400px;'>";
			//formularios usuario
			$qq = "SELECT * FROM Traits WHERE TraitTipo LIKE 'Variavel|%' ORDER BY PathName ASC";
			$rr = mysql_query($qq,$conn);
			while ($row= mysql_fetch_assoc($rr)) {
				$trid = $row['TraitID'];
				if (@in_array($trid,$traitid)) {
					$ss = 'selected';
				} else {
					$ss = '';
				}
				echo "
                  <option $ss value='".$row['TraitID']."'>".($row['PathName'])."</option>";
		}		
		echo "
                </select>
              </td>
      </tr>              
<script type=\"text/javascript\">
<!--
var myfilter2 = new filterlist(document.getElementById('traitslist'));
//-->
</script> 
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td align='left' colspan='3'>
                <input type='submit' value='Gerar formulário para variáveis selecionadas' class='bblue' style='font-size: 0.8em;' />
              </td>
            </tr>
            <tr>
              <td>Filtrar a lista:</td>
              <td><input name='regexp' onKeyUp=\"myfilter2.set(this.value);\" /></td>
              <td><input type='button' onclick=\"myfilter2.reset();this.form.regexp.value=''\" value=\"Mostrar todos\" /></td>
              <td ><input type='checkbox' name=\"toLowerCase\" onclick=\"myfilter2.set_ignore_case(!this.checked);\" />&nbsp;Case sensitive</td>
            </tr>
           </table>
       </td>
      </tr>";
if (count($traitid)>0) {
		$qq = "SELECT * FROM Traits WHERE ";
		$i=0;
		foreach ($traitid as $key => $value) {
				if ($i==0) {
					$qq = $qq." TraitID='".$value."'";
				} else {
					$qq = $qq." OR TraitID='".$value."'";
				}
				$i++;
		}
		$qq = $qq." ORDER BY PathName";
		$rr = mysql_query($qq);
		$nvar = mysql_numrows($rr);
echo "
     <tr><td colspan='3'><hr></td></tr>
     <tr><td class='tdsmallbold' colspan='3' align='left'>BUSCA POR VARIÁVEIS DE USUÁRIO</td></tr>
     <tr><td colspan='3'><hr></td></tr>";
		
		while ($row= mysql_fetch_assoc($rr)) { //para cada variavel no relatorio
				$zz = explode("-",$row['PathName']);
				$trclass = trim($zz[0]);

if ($bgi % 2 == 0){$bgcolor = $linecolor2 ;}  else {$bgcolor = $linecolor1 ;} $bgi++;
echo "
<tr bgcolor = '".$bgcolor."' style='font-size: 0.9em;'>
  <td>
    <table >
      <tr>
        <td class='tdsmallbold'>".str_replace(" ","&nbsp;",$row['TraitName'])."</td>
        <td align='left'><img height='12' src=\"icons/icon_question.gif\" ";
							$help = ($row['TraitDefinicao']);
							echo " onclick=\"javascript:alert('$help');\" /></td>
        <td>
      </tr>
    </table>
  </td>
  <td colspan='2'>";

				//se categoria
				if ($row['TraitTipo']=='Variavel|Categoria') {
					//opcoes de variaves categoricas
					$qq = "SELECT * FROM Traits WHERE ParentID='".$row['TraitID']."' ORDER BY TraitName";
					$res = mysql_query($qq,$conn);
					$nres = mysql_numrows($res);
					echo "
    <table>";
					$tname = "traitvar_".$row['TraitID']."[]";
					$cr=0;
					while ($rw= mysql_fetch_assoc($res)) { //para cada estado de variacao
						if ($cr % 4 == 0 || $cr==0) {
							echo "
      <tr class='cl'>";
					    }
						$tid = $rw['TraitID'];
						$stname = str_replace(" ","&nbsp;",$rw['TraitName']);
		echo "
        <td>
                ";
                  if ($cr==0) {
		                echo"
          <table>
            <tr>
              <td><input type='checkbox' name='".$tname."' value='none' /></td>
              <td class='tdformnotes' style='color: #990000; font-size: 0.9em;'>registros&nbsp;sem&nbsp;valor</td>
            </tr>
          </table>
        </td>
        <td>";
                     $cr++;
                  }
                  
                echo"
          <table>
            <tr>
              <td><input type='checkbox' name='".$tname."' value='".$tid."' /></td>
              <td class='tdformnotes'>$stname</td>
            </tr>
          </table>
        </td>";
						$cr++;
						if ($cr % 4 == 0 || $cr==$nres) {
							echo "
      </tr>";
					    }
					} 
						echo "
    </table>";
				}
				//se quantitativo
				if ($row['TraitTipo']=='Variavel|Quantitativo') {
					$string = 'traitvar_'.$row['TraitID'];
					$val= $ppost[$string];
					if (@in_array('none',$val)) {
						$selc = 'selected';
						$vv1 = '';
						$vv2 = '';
					} else {
						$vv1 = $val[0];
						$vv2 = $val[1];
						$selc = '';
					}
					echo "
    <table>
      <tr>
        <td >".strtolower(GetLangVar('namefrom')).":</td>
          <td><input name='traitvar_".$row['TraitID']."[]' value='$vv1' size='5'/></td> 
          <td >".strtolower(GetLangVar('nameto')).":</td>
          <td><input name='traitvar_".$row['TraitID']."[]' value='$vv2' size='5'/></td>
          <td>".($row['TraitUnit'])."</td>
          <td><input type='checkbox' name='traitvar_".$row['TraitID']."[]' $selc value='none' /></td>
          <td class='tdformnotes' style='color: #990000'>registros&nbsp;sem&nbsp;valor</td>
      </tr>
     </table>";
				}
				//se texto
				if ($row['TraitTipo']=='Variavel|Texto') {
					$string = 'traitvar_'.$row['TraitID'];
					$val= $ppost[$string];
					if (@in_array('empty',$val)) {
						$selctxt = 'selected';
						$vv1 = '';
					} else {
						$vv1 = $val[0];
						$selc = '';
					}
					echo "
            <table>
                <tr>
                  <td class='tdformnotes' ><input type='text' name='traitvar_".$row['TraitID']."[]' value='$vv1' /></td>
                  <td class='tdformnotes' ><input type='checkbox' name='traitvar_".$row['TraitID']."[]' $selctxt value='empty' /></td>
          		  <td class='tdformnotes' style='color: #990000'>registros&nbsp;sem&nbsp;valor</td>
                </tr>
            </table>";
            }

			if ($row['TraitTipo']=='Variavel|Imagem') {
					$string = 'traitvar_'.$row['TraitID'];
					$val= $ppost[$string];
					echo "
            <table>
                <tr>
                  <td class='tdformnotes' ><input type='radio' name='traitvar_".$row['TraitID']."[]' value='img' /></td>
                  <td class='tdformnotes' >registros&nbsp;com&nbsp;imagens</td>
                </tr>
                <tr>
                  <td class='tdformnotes' ><input type='radio' name='traitvar_".$row['TraitID']."[]' value='noimg' /></td>
                  <td class='tdformnotes' >registros&nbsp;sem&nbsp;imagens</td>
                </tr>
            </table>";
			}
echo "
      </td>
    </tr>";
		}//end of loop de cada variavel relatorio
echo "
      <tr><td colspan='3' ><hr></td></tr>";

}
echo "
</table>
</div>
</div>
<table style='position: relative' align='center'>
<tr><td align='center'><input style='cursor: pointer' type='submit' value='".GetLangVar('namebuscar')."' class='bsubmit' onclick=\"javascript:document.finalform.final.value=1\" /></td></tr>
</table>
</form>
";

} 
elseif ($final==1) {
	if ($updating==1) {
		$qq = "SELECT * FROM Filtros WHERE FiltroID='".$filtroid."'";
		$res = @mysql_query($qq,$conn);
		$rr = @mysql_fetch_assoc($res);
		$_SESSION['searchcriteria'] = $rr['FiltroDefinitions'];
		header("location: filtros-exec.php?ispopup=1&filtroid=".$filtroid);
	} else {
		$_SESSION['searchcriteria'] = serialize($ppost);
		header("location: filtros-exec.php?ispopup=1");
	}
}
$which_java = array("<script type='text/javascript' src='javascript/myjavascripts.js'></script>",
"<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click ('all' or 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->",
"<script type='text/javascript'>qm_create(0,false,0,500,false,false,false,false,false);</script>");
FazFooter($which_java,$calendar=FALSE,$footer=$menu);

?>