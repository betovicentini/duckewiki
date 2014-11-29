CREATE FUNCTION specimen_optiontag(colid INT(10),iddid INT(10), ncoln VARCHAR(10)) RETURNS text CHARSET utf8
BEGIN
DECLARE abr CHAR(100) DEFAULT '';
DECLARE pren CHAR(100) DEFAULT '';
DECLARE fam CHAR(200) DEFAULT '';
DECLARE genus CHAR(200) DEFAULT '';
DECLARE spp VARCHAR(200) DEFAULT '';
DECLARE infspnome VARCHAR(200) DEFAULT '';
DECLARE infspnivel VARCHAR(200) DEFAULT '';
DECLARE resultado VARCHAR(1000) DEFAULT '';
SELECT Abreviacao,PreNome INTO abr,pren FROM Pessoas WHERE PessoaID=colid;
SELECT Familia,Genero,Especie,InfraEspecie,InfraEspecieNivel INTO fam,genus,spp,infspnome,infspnivel FROM Identidade as idd LEFT JOIN Tax_InfraEspecies as infsp ON infsp.InfraEspecieID=idd.InfraEspecieID LEFT JOIN Tax_Especies as spp ON spp.EspecieID=idd.EspecieID LEFT JOIN Tax_Generos as genl ON genl.GeneroID=idd.GeneroID LEFT JOIN Tax_Familias as faml ON faml.FamiliaID=idd.FamiliaID WHERE DetID=iddid;
IF (infspnome<>'') THEN
SET resultado = CONCAT(infspnivel,' ',infspnome);
END IF;
IF (spp<>'') THEN
SET resultado = TRIM(CONCAT(spp,' ',resultado));
END IF;
IF (genus<>'') THEN
SET resultado = TRIM(CONCAT(genus,' ',resultado));
END IF;
IF (fam<>'') THEN
SET resultado = TRIM(CONCAT(resultado,IF(resultado<>'',' - ',''),fam));
END IF;
SET resultado = TRIM(CONCAT(abr,' ',ncoln,' ',resultado));
RETURN resultado;
END
