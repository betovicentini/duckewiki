CREATE FUNCTION taxavalues(listataxa VARCHAR(200)) RETURNS text CHARSET utf8
BEGIN
DECLARE ntaxa INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 0;
DECLARE otax CHAR(100) DEFAULT ' ';
DECLARE basvar CHAR(100) DEFAULT ' ';
DECLARE idvar INT(10) DEFAULT 0;
DECLARE resultado VARCHAR(1000) DEFAULT ' ';
DECLARE respar VARCHAR(200) DEFAULT ' ';
DECLARE respar2 VARCHAR(200) DEFAULT ' ';
DECLARE resultado2 VARCHAR(1000) DEFAULT ' ';

SELECT substrCount(listataxa,';')+1 INTO ntaxa;
WHILE ncatstep <= ntaxa DO
SET respar = ' ';
SELECT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(listataxa,';',ncatstep),';',-1)) INTO otax;
SELECT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(otax,'_',1),'_',-1)) INTO basvar;
SELECT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(otax,'_',2),'_',-1)) INTO idvar;
IF (basvar='famid') THEN
SELECT CONCAT(Familia,'|',' ','|',' ','|',' ','|',' ','|',' ','|',' ') INTO respar FROM Tax_Familias WHERE FamiliaID=idvar;
SELECT Familia INTO respar2 FROM Tax_Familias WHERE FamiliaID=idvar;
ELSE
IF (basvar='genusid') THEN
SELECT CONCAT(Familia,'|',Genero,'|',' ','|',' ','|',' ','|',' ','|',' ') INTO respar FROM Tax_Generos JOIN Tax_Familias USING(FamiliaID) WHERE GeneroID=idvar;
SELECT Genero INTO respar2 FROM Tax_Generos JOIN Tax_Familias USING(FamiliaID) WHERE GeneroID=idvar;
ELSE
IF (basvar='speciesid') THEN
SELECT CONCAT(Familia,'|',Genero,'|',Especie,'|',EspecieAutor,'|',' ','|',' ','|',' ')  INTO respar FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE EspecieID=idvar;
SELECT CONCAT(Genero,' ',Especie)  INTO respar2 FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE EspecieID=idvar;
ELSE
IF (basvar='infspid') THEN
SELECT CONCAT(Familia,'|',Genero,'|',Especie,'|',EspecieAutor,'|',InfraEspecieNivel,'|',InfraEspecie,'|',InfraEspecieAutor) INTO respar FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE InfraEspecieID=idvar;
SELECT CONCAT(Genero,' ',Especie,' ',InfraEspecie) INTO respar2 FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE InfraEspecieID=idvar;
END IF;
END IF;
END IF;
END IF;
IF (respar<>' ' AND resultado=' ') THEN
SET resultado = respar;
ELSE
IF (respar<>' ') THEN
SET resultado = CONCAT(resultado,'&&',respar);
END IF;
END IF;
IF (respar2<>' ' AND resultado2=' ') THEN
SET resultado2 = respar2;
ELSE
IF (respar2<>' ') THEN
SET resultado2 = CONCAT(resultado2,'&&',respar2);
END IF;
END IF;
SET ncatstep = ncatstep+1;
END WHILE;
SET resultado = CONCAT(resultado,'$$$',resultado2);
RETURN resultado;
END