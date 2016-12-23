CREATE FUNCTION checkgeneros(generosfield varchar(500), familiaid int(10)) RETURNS CHAR(255) CHARSET utf8
BEGIN
DECLARE resultado CHAR(255) DEFAULT '';
DECLARE res CHAR(255) DEFAULT '';
DECLARE res1 CHAR(255) DEFAULT '';
DECLARE generovar CHAR(255) DEFAULT '';
DECLARE pespalavra CHAR(255) DEFAULT '';
DECLARE pespalavra2 CHAR(255) DEFAULT '';
DECLARE sobnome CHAR(255) DEFAULT '';
DECLARE lastname CHAR(255) DEFAULT '';
DECLARE ncat INT(10) DEFAULT 0;
DECLARE newcat INT(10) DEFAULT 0;
DECLARE np1 INT(10) DEFAULT 0;
DECLARE np2 INT(10) DEFAULT 0;
DECLARE nmatches INT(10) DEFAULT 0;
DECLARE matchagen INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
SELECT substrCount(generosfield,';')+1 INTO ncat;
WHILE ncatstep <= ncat DO
	SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(generosfield,';',ncatstep),';',-1)) INTO generovar;
	SELECT TRIM(generovar) INTO generovar;
	SET res = '';
	IF (generovar<>'') THEN
	IF (familiaid>0) THEN
		SELECT COUNT(*) INTO matchagen FROM Tax_Generos WHERE UPPER(Genero) LIKE UPPER(generovar) AND FamiliaID=familiaid;
	ELSE 
		SELECT COUNT(*) INTO matchagen FROM Tax_Generos WHERE UPPER(Genero) LIKE UPPER(generovar);
	END IF;
	IF (matchagen=1) THEN
		IF (familiaid>0) THEN
			SELECT GeneroID INTO res FROM Tax_Generos WHERE UPPER(Genero) LIKE UPPER(generovar) AND FamiliaID=familiaid;
		ELSE 
			SELECT GeneroID INTO res FROM Tax_Generos WHERE UPPER(Genero) LIKE UPPER(generovar);
		END IF;
	ELSE 
		SET res1 = 'ERRO';
	END IF;
	IF (ncatstep=1) THEN
		SET resultado = res;
	ELSE
		SET resultado = CONCAT(resultado,';',res);
	END IF;
	END IF;
	SET ncatstep = ncatstep+1;
END WHILE;
IF res1='ERRO' THEN
SET resultado = res1;
END IF;
RETURN resultado;
END
