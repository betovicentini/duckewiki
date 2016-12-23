CREATE FUNCTION search_recs_bytraitnew(ttid int(10), tvalor varchar(500), linktype varchar(10), linkid int(10)) RETURNS INT
BEGIN
DECLARE ttipo TEXT DEFAULT '';
DECLARE ncat INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
DECLARE val1 FLOAT DEFAULT 0;
DECLARE val2 FLOAT DEFAULT 0;
DECLARE trtid varchar(500) DEFAULT '';
DECLARE contador INT(10) DEFAULT 0;
DECLARE temNtem INT(10) DEFAULT 0;
IF tvalor<>'' THEN
	SELECT TraitTipo INTO ttipo FROM Traits WHERE TraitID=ttid;
	IF ttipo='Variavel|Categoria' THEN
		SELECT substrCount(tvalor,';')+1 INTO ncat;
		myloop: WHILE ncatstep <= ncat DO
			SET temNtem = 0;
			SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(tvalor,';',ncatstep),';',-1)) INTO trtid;
			IF linktype='plantas' THEN
				SELECT COUNT(*) INTO temNtem FROM Monitoramento WHERE TraitID=ttid AND PlantaID=linkid AND (TraitVariation LIKE CONCAT('%;',trtid) OR TraitVariation LIKE CONCAT('%;',trtid,';%') OR TraitVariation LIKE CONCAT(trtid,';%') OR TraitVariation=trtid);
				IF (temNtem>0) THEN
					SET contador = contador+1;
					LEAVE myloop;
				ELSE
					SELECT COUNT(*) INTO temNtem FROM Traits_variation WHERE TraitID=ttid AND PlantaID=linkid AND (TraitVariation LIKE CONCAT('%;',trtid) OR TraitVariation LIKE CONCAT('%;',trtid,';%') OR TraitVariation LIKE CONCAT(trtid,';%') OR TraitVariation=trtid);
					IF (temNtem>0) THEN
						SET contador = contador+1;
						LEAVE myloop;
					END IF;
				END IF;
			END IF;
			IF linktype='coletas' THEN
				SELECT COUNT(*) INTO temNtem FROM Traits_variation WHERE TraitID=ttid AND EspecimenID=linkid AND (TraitVariation LIKE CONCAT('%;',trtid) OR TraitVariation LIKE CONCAT('%;',trtid,';%') OR TraitVariation LIKE CONCAT(trtid,';%') OR TraitVariation=trtid);
				IF (temNtem>0 ) THEN
					SET contador = contador+1;
					LEAVE myloop;
				END IF;
			END IF;
 			SET ncatstep = ncatstep+1;
	  END WHILE;
	END IF;
	IF ttipo='Variavel|Quantitativo' THEN
		SELECT SPLIT_STR_MIN(tvalor,';') INTO val1;
		SELECT SPLIT_STR_MAX(tvalor,';') INTO val2;
		SET temNtem = 0;
		IF linktype='plantas' THEN
			SELECT COUNT(*) INTO temNtem FROM Monitoramento WHERE TraitID=ttid AND PlantaID=linkid AND SPLIT_STR_COMP(TraitVariation,';',val1,val2)>0;
			IF (temNtem>0 ) THEN
				SET contador = contador+1;
			ELSE
				SELECT COUNT(*) INTO temNtem FROM Traits_variation WHERE TraitID=ttid AND PlantaID=linkid AND SPLIT_STR_COMP(TraitVariation,';',val1,val2)>0;
				IF (temNtem>0 ) THEN
					SET contador = contador+1;
				END IF;
			END IF;
		END IF;
		IF linktype='coletas' THEN
			SELECT COUNT(*) INTO temNtem FROM Traits_variation WHERE TraitID=ttid AND EspecimenID=linkid AND SPLIT_STR_COMP(TraitVariation,';',val1,val2)>0;
			IF (temNtem>0 ) THEN
				SET contador = contador+1;
			END IF;
		END IF;
	END IF; 
	IF ttipo='Variavel|Texto' THEN
		SET temNtem = 0;
		IF linktype='plantas' THEN
			SELECT COUNT(*) INTO temNtem FROM Monitoramento WHERE TraitID=ttid AND PlantaID=linkid AND LOWER(TraitVariation) LIKE CONCAT('%',LOWER(tvalor),'%');
			IF (temNtem>0 ) THEN
				SET contador = contador+1;
			ELSE
				SELECT COUNT(*) INTO temNtem FROM Traits_variation WHERE TraitID=ttid AND PlantaID=linkid AND LOWER(TraitVariation) LIKE CONCAT('%',LOWER(tvalor),'%');
				IF (temNtem>0 ) THEN
					SET contador = contador+1;
				END IF;
			END IF;
		END IF;
		IF linktype='coletas' THEN
			SELECT COUNT(*) INTO temNtem FROM Traits_variation WHERE TraitID=ttid AND EspecimenID=linkid AND LOWER(TraitVariation) LIKE CONCAT('%',LOWER(tvalor),'%');
			IF (temNtem>0 ) THEN
				SET contador = contador+1;
			END IF;
		END IF;
	END IF; 
	IF ttipo='Variavel|Imagem' THEN
		SET temNtem = 0;
		IF linktype='plantas' THEN
			SELECT COUNT(*) INTO temNtem FROM Monitoramento WHERE TraitID=ttid AND PlantaID=linkid;
			IF (temNtem>0 ) THEN
				SET contador = contador+1;
			ELSE
				SELECT COUNT(*) INTO temNtem FROM Traits_variation WHERE TraitID=ttid AND PlantaID=linkid;
				IF (temNtem>0 ) THEN
					SET contador = contador+1;
				END IF;
			END IF;
		END IF;
		IF linktype='coletas' THEN
			SELECT COUNT(*) INTO temNtem FROM Traits_variation WHERE TraitID=ttid AND EspecimenID=linkid;
			IF (temNtem>0 ) THEN
				SET contador = contador+1;
			END IF;
		END IF;
	END IF;
END IF;
RETURN contador;
END