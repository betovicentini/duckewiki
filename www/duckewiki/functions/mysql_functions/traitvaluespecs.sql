CREATE FUNCTION traitvaluespecs(ttid int(10), pltid int(10), specid int(10), trunit char(100), somedia BOOLEAN, somax BOOLEAN) RETURNS CHAR(100) CHARSET utf8
BEGIN
DECLARE done INT DEFAULT 0;
DECLARE respar CHAR(150) DEFAULT '';
DECLARE resultado CHAR(100) DEFAULT '';
DECLARE contador INT DEFAULT 0;
DECLARE tvariation CHAR(100) DEFAULT '';
DECLARE tunit CHAR(50) DEFAULT '';
DECLARE ttipo CHAR(50) DEFAULT '';
DECLARE tmvariation CHAR(100) DEFAULT '';
DECLARE tmunit CHAR(50) DEFAULT '';
DECLARE tmtipo CHAR(50) DEFAULT '';
DECLARE tsvariation CHAR(100) DEFAULT '';
DECLARE tsunit CHAR(50) DEFAULT '';
DECLARE tstipo CHAR(50) DEFAULT '';
DECLARE ncat INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
DECLARE statename CHAR(50) DEFAULT '';
DECLARE statevariation CHAR(100) DEFAULT '';
DECLARE statevar FLOAT DEFAULT 0;
DECLARE statevar2 FLOAT DEFAULT 0;
DECLARE statemin FLOAT DEFAULT 0;
DECLARE statemax FLOAT DEFAULT 0;
DECLARE trtid INT(10) DEFAULT 0;
DECLARE stateunique INT(10) DEFAULT 0;
IF (pltid>0) THEN
SELECT TraitVariation,Traits_variation.TraitUnit,Traits.TraitTipo INTO tvariation, tunit,ttipo FROM Traits_variation JOIN Traits USING(TraitID) WHERE Traits_variation.TraitID=ttid AND Traits_variation.PlantaID=pltid LIMIT 0,1;
SELECT TraitVariation,Monitoramento.TraitUnit,Traits.TraitTipo INTO tmvariation,tmunit,tmtipo FROM Monitoramento JOIN Traits USING(TraitID) WHERE Monitoramento.TraitID=ttid AND Monitoramento.PlantaID=pltid ORDER BY Monitoramento.DataObs DESC LIMIT 0,1;
END IF;
IF (specid>0) THEN
SELECT TraitVariation,Traits_variation.TraitUnit,Traits.TraitTipo INTO tsvariation, tsunit,tstipo FROM Traits_variation JOIN Traits USING(TraitID) WHERE Traits_variation.TraitID=ttid AND Traits_variation.EspecimenID=specid LIMIT 0,1;
END IF;
IF (tmvariation<>'' AND tvariation<>'') THEN
	SET tvariation = CONCAT(tvariation,'; ',tmvariation); 
END IF;
IF (tmvariation<>'' AND tvariation='') THEN
	SET tvariation = tmvariation; 
END IF;
IF (tsvariation<>'' AND tvariation<>'') THEN
	SET tvariation = CONCAT(tvariation,'; ',tsvariation); 
END IF;
IF (tsvariation<>'' AND tvariation='') THEN
	SET tvariation = tsvariation; 
END IF;
IF (ttipo='Variavel|Categoria' OR tmtipo='Variavel|Categoria' OR tstipo='Variavel|Categoria') THEN
	SELECT substrCount(tvariation,';')+1 INTO ncat;
	WHILE ncatstep <= ncat DO
		SET statename = '';
		SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(tvariation,';',ncatstep),';',-1)) INTO trtid;
		SELECT TraitName INTO statename FROM Traits WHERE TraitID=trtid;
		SET statename = lower(statename);
		IF (ncatstep=1) THEN
			SET statevariation = statename;
		ELSE
			SELECT statevariation LIKE CONCAT('%',statename,'%') INTO stateunique;
			IF (stateunique=0) THEN 
				SET statevariation = CONCAT(statevariation,'; ',statename);
			END IF;
		END IF;
		SET ncatstep = ncatstep+1;
	END WHILE;
END IF;
IF (ttipo='Variavel|Quantitativo' OR tmtipo='Variavel|Quantitativo' OR tstipo='Variavel|Quantitativo') THEN
	IF (tvariation<>'') THEN
		SELECT substrCount(tvariation,';')+1 INTO ncat;
		IF (somax OR somedia) THEN
			WHILE ncatstep <= ncat DO
				SET statevar2 = 0;
				SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(tvariation,';',ncatstep),';',-1)) INTO statevar2;
				IF (trunit='mm' AND tunit='cm') THEN
					SET statevar2 = statevar2*10;
				END IF; 
				IF (trunit='cm' AND tunit='mm') THEN
					SET statevar2 = statevar2/10;
				END IF; 
				IF (trunit='m' AND tunit='cm') THEN
					SET statevar2 = (statevar2)/100;
				END IF; 
				IF (trunit='cm' AND tunit='m') THEN
					SET statevar2 = (statevar2)*100;
				END IF; 
				IF (ncatstep=1) THEN
					SET statevar = statevar2;
					SET statemax = statevar2;
				ELSE
					IF (statevar2>statemax) THEN
						SET statemax = statevar2;
					END IF;
					SET statevar = statevar+statevar2;
				END IF;
				SET ncatstep = ncatstep+1;
			END WHILE;
			IF (somedia) THEN
				SET statevariation = round(statevar/ncat,2);
			ELSE
				IF (somax) THEN
					SET statevariation = ROUND(statemax,2);
				END IF;
			END IF;
		ELSE 
			SET statevariation = tvariation;
		END IF;
	END IF;
END IF;
IF (statevariation='' AND tvariation<>'') THEN
	SET statevariation = tvariation;
END IF;
RETURN statevariation;
END
