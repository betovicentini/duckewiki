CREATE FUNCTION getspecexsicataimg(specid INT(10), httppath CHAR(255), imgtrid INT(10)) RETURNS VARCHAR(5000)
BEGIN
DECLARE resultado VARCHAR(5000) DEFAULT "";
DECLARE trvar VARCHAR(5000) DEFAULT '';
DECLARE fnn CHAR(100) DEFAULT '';
DECLARE runn CHAR(200) DEFAULT '';
DECLARE ncat INT DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
DECLARE imgid INT(10) DEFAULT 0;
SELECT TraitVariation INTO trvar FROM Traits_variation WHERE EspecimenID=specid AND TraitID=imgtrid;
SELECT substrCount(trvar,';')+1 INTO ncat;
WHILE ncatstep <= ncat DO
	SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(trvar,';',ncatstep),';',-1)) INTO imgid;
	SELECT (imgid+0) INTO imgid;
	SET fnn="";
	SELECT FileName INTO fnn FROM Imagens WHERE ImageID=imgid;
	IF (fnn<>"") THEN
		SET runn = CONCAT(httppath,"/",fnn);
		IF (resultado="") THEN
			SET resultado = runn;
		ELSE 
			SET resultado = CONCAT(resultado,";",runn);
		END IF;
	END IF;
	SET ncatstep = ncatstep+1;
END WHILE;
RETURN resultado;
END
