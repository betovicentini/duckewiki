CREATE FUNCTION getpessoas(AddColIDS varchar(500)) RETURNS VARCHAR(1000) CHARSET utf8
BEGIN
DECLARE addcolldesc  VARCHAR(1000)  DEFAULT '';
DECLARE colname  VARCHAR(500)  DEFAULT '';
DECLARE ncat INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
DECLARE pessid INT(10) DEFAULT 0;
DECLARE cln CHAR(100) DEFAULT '';
DECLARE clnn CHAR(100) DEFAULT '';
SELECT substrCount(AddColIDS,';')+1 INTO ncat;
WHILE ncatstep <= ncat DO
SET colname = '';
SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(AddColIDS,';',ncatstep),';',-1)) INTO pessid;
SELECT Abreviacao INTO colname FROM Pessoas WHERE PessoaID=pessid;
SELECT SUBSTRING_INDEX(colname,",",1) INTO cln;
SELECT SUBSTRING_INDEX(colname,",",-1) INTO clnn;
SET colname = CONCAT(clnn," ",cln);
IF (ncatstep=1) THEN
	SET addcolldesc = colname;
ELSE
	IF (ncatstep=ncat) THEN
		SET addcolldesc = CONCAT(addcolldesc,' and ',colname);
	ELSE
		SET addcolldesc = CONCAT(addcolldesc,', ',colname);
	END IF;
END IF;
	SET ncatstep = ncatstep+1;
END WHILE;
	RETURN addcolldesc;
END