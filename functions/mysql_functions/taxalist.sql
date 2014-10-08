CREATE FUNCTION taxalist(listataxa VARCHAR(200)) RETURNS text CHARSET utf8
BEGIN
DECLARE ntaxa INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 0;
DECLARE otax CHAR(100) DEFAULT '';
DECLARE basvar CHAR(100) DEFAULT '';
DECLARE idvar INT(10) DEFAULT 0;
DECLARE resultado VARCHAR(1000) DEFAULT '';
DECLARE respar VARCHAR(200) DEFAULT '';
SELECT substrCount(listataxa,';')+1 INTO ntaxa;
WHILE ncatstep <= ntaxa DO
SET respar = '';
SELECT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(listataxa,';',ncatstep),';',-1)) INTO otax;
SELECT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(otax,'|',1),'|',-1)) INTO basvar;
SELECT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(otax,'|',2),'|',-1)) INTO idvar;
IF (basvar='familia') THEN
SELECT Familia INTO respar FROM Tax_Familias WHERE FamiliaID=idvar;
ELSE
IF (basvar='genero') THEN
SELECT CONCAT('<i>',Genero,'</i>') INTO respar FROM Tax_Generos WHERE GeneroID=idvar;
ELSE
IF (basvar='especie') THEN
SELECT CONCAT('<i>',Genero,' ',Especie,'</i>') INTO respar FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) WHERE EspecieID=idvar;
ELSE
IF (basvar='infraespecie') THEN
SELECT CONCAT('<i>',Genero,' ',Especie,' ',InfraEspecieNivel,' ',InfraEspecie,'</i>') INTO respar FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) WHERE InfraEspecieID=idvar;
END IF;
END IF;
END IF;
END IF;
IF (respar<>'' AND resultado='') THEN
SET resultado = respar;
ELSE
IF (respar<>'') THEN
SET resultado = CONCAT(resultado,'; ',respar);
END IF;
END IF;
SET ncatstep = ncatstep+1;
END WHILE;
RETURN resultado;
END
