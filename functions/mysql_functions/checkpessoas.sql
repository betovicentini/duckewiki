CREATE FUNCTION checkpessoas(pessoasfield varchar(100)) RETURNS text CHARSET utf8
BEGIN
DECLARE resultado CHAR(255) DEFAULT '';
DECLARE res CHAR(255) DEFAULT '';
DECLARE res1 CHAR(255) DEFAULT '';
DECLARE pess CHAR(255) DEFAULT '';
DECLARE pespalavra CHAR(255) DEFAULT '';
DECLARE pespalavra2 CHAR(255) DEFAULT '';
DECLARE sobnome CHAR(255) DEFAULT '';
DECLARE lastname CHAR(255) DEFAULT '';
DECLARE ncat INT(10) DEFAULT 0;
DECLARE newcat INT(10) DEFAULT 0;
DECLARE np1 INT(10) DEFAULT 0;
DECLARE np2 INT(10) DEFAULT 0;
DECLARE nmatches INT(10) DEFAULT 0;
DECLARE matchabr INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
SELECT substrCount(pessoasfield,';')+1 INTO ncat;
WHILE ncatstep <= ncat DO
SET sobnome = '';
SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(pessoasfield,';',ncatstep),';',-1)) INTO pess;
SELECT TRIM(pess) INTO pess;
SELECT COUNT(PessoaID) INTO matchabr FROM Pessoas WHERE UPPER(Abreviacao) LIKE UPPER(pess);
IF (matchabr=1) THEN
SELECT PessoaID INTO res FROM Pessoas WHERE UPPER(Abreviacao) LIKE UPPER(pess);
ELSE
	IF (matchabr=0) THEN
		SELECT SUBSTRING_INDEX(pess,' ',-1) INTO lastname;
		SELECT COUNT(PessoaID) INTO matchabr  FROM Pessoas WHERE UPPER(Sobrenome) LIKE UPPER(lastname);
		IF (matchabr=1) THEN
			SELECT PessoaID INTO res FROM Pessoas WHERE UPPER(Sobrenome) LIKE UPPER(lastname);
		ELSE
			SELECT SUBSTRING_INDEX(pess,' ',1) INTO lastname;
			SELECT COUNT(PessoaID) INTO matchabr  FROM Pessoas WHERE UPPER(Sobrenome) LIKE UPPER(lastname);
			IF (matchabr=1) THEN
				SELECT PessoaID INTO res FROM Pessoas WHERE UPPER(Sobrenome) LIKE UPPER(lastname);
			ELSE
				SET res1 = 'ERRO';
			END IF;
		END IF; 
	ELSE
		SET res1 = 'ERRO';
	END IF;
END IF;
IF (ncatstep=1) THEN
SET resultado = res;
ELSE
SET resultado = CONCAT(resultado,';',res);
END IF;
SET ncatstep = ncatstep+1;
END WHILE;
IF res1='ERRO' THEN
SET resultado = res1;
END IF;
RETURN resultado;
END
