CREATE FUNCTION trativaluesearch(ttipo varchar(50), tvtosearch varchar(100), tvrefvar varchar(300), tvrefmoni varchar(300)) RETURNS INT(10)
BEGIN
DECLARE ncat INT(10) DEFAULT 0;
DECLARE ncat2 INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
DECLARE ncatstep2 INT(10) DEFAULT 1;
DECLARE trtid INT(10) DEFAULT 0;
DECLARE trtid2 INT(10) DEFAULT 0;
DECLARE val1 FLOAT DEFAULT 0;
DECLARE val2 FLOAT DEFAULT 0;
DECLARE contador INT(10) DEFAULT 0;
DECLARE temNtem INT(10) DEFAULT 0;
IF tvtosearch<>'' THEN
	IF ttipo='Variavel|Categoria' THEN
		SELECT substrCount(tvtosearch,';')+1 INTO ncat;
		WHILE ncatstep <= ncat DO
			SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(tvtosearch,';',ncatstep),';',-1)) INTO trtid;
			IF tvrefvar<>'' THEN
				SELECT substrCount(tvrefvar,';')+1 INTO ncat2;
				myloop: WHILE ncatstep2 <= ncat2 
				DO
					SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(tvrefvar,';',ncatstep2),';',-1)) INTO trtid2;
					IF ((trtid2+0)=(trtid+0)) THEN
						SET contador = contador+1;
						LEAVE myloop;
					END IF;
					SET ncatstep2 = ncatstep2+1;
				END WHILE;
			END IF;
			SET ncatstep2 = 1;
			IF tvrefmoni<>'' THEN
				SELECT substrCount(tvrefmoni,';')+1 INTO ncat2;
				myloop: WHILE ncatstep2 <= ncat2 
				DO
					SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(tvrefvar,';',ncatstep2),';',-1)) INTO trtid2;
					IF ((trtid2+0)=(trtid+0)) THEN
						SET contador = contador+1;
						LEAVE myloop;
					END IF;
					SET ncatstep2 = ncatstep2+1;
				END WHILE;
			END IF;
			SET ncatstep2 = 1;
			SET ncatstep = ncatstep+1;
		END WHILE;
	END IF;
	IF ttipo='Variavel|Quantitativo' THEN
		SELECT SPLIT_STR_MIN(tvtosearch,';') INTO val1;
		SELECT SPLIT_STR_MAX(tvtosearch,';') INTO val2;
		IF tvrefvar<>'' THEN
			SELECT substrCount(tvrefvar,';')+1 INTO ncat2;
			myloop: WHILE ncatstep2 <= ncat2 
			DO
				SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(tvrefvar,';',ncatstep2),';',-1)) INTO trtid2;
				IF ((trtid2+0)>=val1 AND (trtid2+0)<=val2) THEN
					SET contador = contador+1;
					LEAVE myloop;
				END IF;
				SET ncatstep2 = ncatstep2+1;
			END WHILE;
			SET ncatstep2 = 1;
		END IF;
		IF tvrefmoni<>'' THEN
			SELECT substrCount(tvrefmoni,';')+1 INTO ncat2;
			myloop: WHILE ncatstep2 <= ncat2 
			DO
				SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(tvrefvar,';',ncatstep2),';',-1)) INTO trtid2;
				IF ((trtid2+0)>=val1 AND (trtid2+0)<=val2) THEN
					SET contador = contador+1;
					LEAVE myloop;
				END IF;
				SET ncatstep2 = ncatstep2+1;
			END WHILE;
			SET ncatstep2 = 1;
		END IF;
	END IF;
	IF ttipo='Variavel|Texto' THEN
		IF tvrefvar<>'' THEN
			SELECT LOWER(tvrefvar) LIKE CONCAT('%',LOWER(tvtosearch),'%') INTO temNtem;
			IF (temNtem>0 ) THEN
				SET contador = contador+1;
			END IF;
		END IF;
		IF tvrefmoni<>'' THEN
			SELECT LOWER(tvrefmoni) LIKE CONCAT('%',LOWER(tvtosearch),'%') INTO temNtem;
			IF (temNtem>0 ) THEN
				SET contador = contador+1;
			END IF;
		END IF;
	END IF;
	IF ttipo='Variavel|Imagem' THEN
		IF (tvrefvar<>'' OR tvrefmoni<>'') THEN
			SET contador = contador+1;
		END IF;
	END IF;
ELSE 
	SET contador = 0;
END IF;
RETURN contador;
END