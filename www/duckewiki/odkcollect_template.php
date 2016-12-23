<?php

$txt ="<?xml version=\"1.0\"?>
<h:html xmlns=\"http://www.w3.org/2002/xforms\" xmlns:ev=\"http://www.w3.org/2001/xml-events\" xmlns:h=\"http://www.w3.org/1999/xhtml\" xmlns:jr=\"http://openrosa.org/javarosa\" xmlns:orx=\"http://openrosa.org/xforms\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\">
  <h:head>";
//titulo do forumlario
$txt .= "
  <h:title>".$formname."</h:title>";
//define o modelo com preenchimento padrao
$txt .=  "
    <model>
      <instance>
        <".$instancecode." id=\"".$odkxmlformid."\" version=\"".$odkformversion."\" >
          <collector>".$collector_def."</collector>
          <number/>
          <colldata>today</colldata>";
if (count($collectors)>1) {
	if (!empty($addcoll_def)) {
	$txt .=  "
          <addcoll>".$addcoll_def."</addcoll>";
	} else {
$txt .=  "
          <addcoll/>";
	}
}
$txt .=  "
          <family/>
          <genus/>
          <species/>";
if ($coordenadas_cell==1 && $coordenadas_gps==1) {
$txt .=  "
          <coordenadas/>";
}
if ($coordenadas_cell==1) {
$txt .=  "
          <coordinates/>";
}
if ($coordenadas_gps==1) {
$txt .=  "
          <nomegp>".$nomegpsdef."</nomegp>
          <pontogps/>";
}
 if ($addhabitatclass==1) {
 $txt .=  "
          <habitatclasse/>";
 }
 $varslooptxt = "";
 foreach($uservariables as $val => $lab) {
	$looptxt = "";
	//$vv_def = $uservariables_def[$val];
	//if (!empty($vv_def)) {
		//$looptxt = "<".$val.">".$vv_def."</".$val.">";
	//} else {
	$looptxt = "<".$val."/>";
	//}
	$varslooptxt .= "
          ".$looptxt;
 }
$txt .= "
".$varslooptxt;

//IMAGENS
//$varimgs = array(); //se pediu para incluir captura de imagens no formulario;
if (count($varimgs)>0) {
//          
$txt .= "
          <temimgs/>
          <imagens jr:template=\"\">
            <imgtag/>
            <img/>
          </imagens>";
}
$txt .= "
          <meta>
            <instanceID/>
            <instanceName/>
          </meta>
        </".$instancecode.">
      </instance>";
//defini agora os   campos obrigatorios e os bind options
$txt .="
      <bind nodeset=\"/".$instancecode."/collector\" required=\"true()\" type=\"select1\"/>
      <bind nodeset=\"/".$instancecode."/number\" required=\"true()\" type=\"int\"/>
      <bind nodeset=\"/".$instancecode."/colldata\" required=\"true()\" type=\"date\"/>";
if (count($collectors)>1) {
$txt .=  "
      <bind nodeset=\"/".$instancecode."/addcoll\" type=\"select\"/>";
}
$txt .=  "
      <bind nodeset=\"/".$instancecode."/family\" type=\"string\"/>
      <bind nodeset=\"/".$instancecode."/genus\" type=\"string\"/>
      <bind nodeset=\"/".$instancecode."/species\" type=\"string\"/>";
if ($coordenadas_cell==1 && $coordenadas_gps==0) {
$txt .=  "
    <bind nodeset=\"/".$instancecode."/coordinates\" required=\"true()\" type=\"geopoint\"/>";
}
if ($coordenadas_gps==1 && $coordenadas_cell==0) {
$txt .=  "
      <bind nodeset=\"/".$instancecode."/nomegp\" required=\"true()\" type=\"select1\"/>
      <bind nodeset=\"/".$instancecode."/pontogps\" required=\"true()\" type=\"int\"/>";
} 
if ($coordenadas_gps==1 && $coordenadas_cell==1) {
$txt .=  "
      <bind nodeset=\"/".$instancecode."/coordenadas\" type=\"select1\"/>
      <bind nodeset=\"/".$instancecode."/coordinates\" relevant=\"selected( /".$instancecode."/coordenadas , &quot;coords&quot;)\" required=\"true()\" type=\"geopoint\"/>
      <bind nodeset=\"/".$instancecode."/nomegp\" relevant=\"selected( /".$instancecode."/coordenadas , &quot;waypts&quot;)\" required=\"true()\" type=\"select1\"/>
      <bind nodeset=\"/".$instancecode."/pontogps\" relevant=\"selected( /".$instancecode."/coordenadas , &quot;waypts&quot;)\" required=\"true()\" type=\"int\"/>";
}
 if ($addhabitatclass==1) {
$txt .= "
      <bind nodeset=\"/".$instancecode."/habitatclasse\" type=\"select\"/>";
}
//adiciona binds para variaveis de formulario
 $varslooptxt = "";
 foreach($uservariables as $val => $lab) {
	$looptxt = "";
	$vv_tipo = $uservariables_tipo[$val];
    $looptxt = "      <bind nodeset=\"/".$instancecode."/".$val."\" type=\"".$vv_tipo."\"/>";
	$varslooptxt .= "
	".$looptxt;
 }
$txt .= "
".$varslooptxt;
//imgs
if (count($varimgs)>0) {
$txt .= "
       <bind nodeset=\"/".$instancecode."/temimgs\" type=\"select1\"/>
       <bind nodeset=\"/".$instancecode."/imagens\" relevant=\"selected( /".$instancecode."/temimgs , &quot;sim&quot;)\"/>
      <bind nodeset=\"/".$instancecode."/imagens/imgtag\" type=\"select1\"/>
      <bind nodeset=\"/".$instancecode."/imagens/img\" type=\"binary\"/>";
}
$txt .= "
      <bind calculate=\"concat('uuid:', uuid())\" nodeset=\"/".$instancecode."/meta/instanceID\" readonly=\"true()\" type=\"string\"/>
      <bind calculate=\"concat( /".$instancecode."/number ,' - ', /".$instancecode."/collector,'  ',/".$instancecode."/family ,' ', /".$instancecode."/genus)\" nodeset=\"/".$instancecode."/meta/instanceName\" type=\"string\"/>
    </model>
  </h:head>
  <h:body>";
  
//valores
$txt .= "
    <select1 ref=\"/".$instancecode."/collector\">
      <label>Coletor</label>";
foreach($collectors as $collvalue => $collab) {
$txt .= "
      <item>
        <label>".$collab."</label>
        <value>".$collvalue."</value>
      </item>";
}
$txt .= "
   </select1>
    <input ref=\"/".$instancecode."/number\">
      <label>Número da coleta</label>
      <hint>Número sequencial do coletor</hint>
    </input>
    <input ref=\"/".$instancecode."/colldata\">
      <label>Data da coleta</label>
    </input>";
if (count($collectors)>1) {
$txt .= "
    <select ref=\"/".$instancecode."/addcoll\">
      <label>Coletores adicionais</label>
      <hint>Outras pessoas que estão na equipe</hint>";
foreach($collectors as $collvalue => $collab) {
$txt .= "
      <item>
        <label>".$collab."</label>
        <value>".$collvalue."</value>
      </item>";
}
$txt .= "
    </select>";
}
$txt .= "
    <input ref=\"/".$instancecode."/family\">
      <label>Família</label>
    </input>
    <input ref=\"/".$instancecode."/genus\">
      <label>Genero</label>
    </input>
    <input ref=\"/".$instancecode."/species\">
      <label>Especie</label>
    </input>";
    
if ($coordenadas_gps==1 && $coordenadas_cell==1) {
$txt .=  "
    <select1 ref=\"/".$instancecode."/coordenadas\">
      <label>Ler coordenadas ou inserir No. Waypoint?</label>
      <item>
        <label>Coordenadas deste aparelho</label>
        <value>coords</value>
      </item>
      <item>
        <label>Waypoints de um GPS</label>
        <value>waypts</value>
      </item>
    </select1>";
}
if ($coordenadas_cell==1) {
$txt .=  "
    <input ref=\"/".$instancecode."/coordinates\">
      <label>Salva coordenadas</label>
      <hint>Coordenadas pode demorar e não encontrar se o sinal for ruim</hint>
    </input>";
}
if ($coordenadas_gps==1) {
$txt .=  "
    <select1 ref=\"/".$instancecode."/nomegp\">
      <label>Qual GPS?</label>";
foreach($gpsunits as $val => $lab) {
$txt .=  "
    <item>
      <label>".$lab."</label>
      <value>".$val."</value>
    </item>";
}
$txt .=  "
    </select1>
    <input ref=\"/".$instancecode."/pontogps\">
      <label>Número do ponto</label>
    </input>";
}
if ($addhabitatclass==1) {
$txt .= "
    <select ref=\"/".$instancecode."/habitatclasse\">
    <label>Classe de hábitat</label>";
	foreach($habitatclass as $val => $lab) {
$txt .=  "
    <item>
      <label>".$lab."</label>
      <value>".$val."</value>
	</item>";
	}
$txt .=  "</select>";
}


//variaveis de usuario
if(count($uservariables)>0) {
foreach($uservariables as $val => $lab) {
	$tp = $uservariables_tipo[$val];
	if ($tp=="decimal" || $tp=="string" || $tp=="int") {
		if ($tp=="string") {
		   $layot = "appearance=\"multiline\""; 
		} else {
		   $layot = ""; 
		}
		$txt .=  "
    <input ".$layot." ref=\"/".$instancecode."/".$val."\">
      <label>".$lab."</label>
    </input>";
	} else {
		$txt .=  "
    <".$tp." ref=\"/".$instancecode."/".$val."\">
      <label>".$lab."</label>";
		$categs = $uservariables_cat[$val];
		foreach ($categs as $vv => $kk) {
			$txt .=  "
      <item>
        <label>".$kk."</label>
        <value>".$vv."</value>
      </item>";
		}
			$txt .=  "
    </".$tp.">";
    }
 }
 } 
if (count($varimgs)>0) {
$txt .= "
    <select1 ref=\"/".$instancecode."/temimgs\">
      <label>Coleta Imagens?</label>
      <hint>Cada grupo é uma imagem diferente!</hint>
      <item>
        <label>Não</label>
        <value>nao</value>
      </item>
      <item>
        <label>Sim</label>
        <value>sim</value>
      </item>
    </select1>";
$txt .=  "
    <group ref=\"/".$instancecode."/imagens\">
      <label>Imagens</label>
      <repeat nodeset=\"/".$instancecode."/imagens\">
        <select1 ref=\"/".$instancecode."/imagens/imgtag\">
        <label>Imagem de que?</label>";        
        foreach($varimgs as $val => $lab) {
			$txt .=  "          
          <item>
            <label>".$lab."</label>
            <value>".$val."</value>
          </item>";
        }
        $txt .=  "          
        </select1>
        <upload mediatype=\"image/*\" ref=\"/".$instancecode."/imagens/img\">
          <label>Tire a fotografia</label>
        </upload>
      </repeat>
    </group>";
}

$txt .=  "
    </h:body>
</h:html>";


?>
