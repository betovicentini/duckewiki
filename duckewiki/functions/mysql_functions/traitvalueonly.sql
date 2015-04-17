CREATE FUNCTION traitvalueonly(tvariation varchar(500),ttid int(10),trunit char(100), tunit char(100),  somedia BOOLEAN, somax BOOLEAN) RETURNS varchar(500) CHARSET UTF8
BEGIN
DECLARE statesids INT DEFAULT 0;
DECLARE ncatstep INT DEFAULT 1;
DECLARE ncat INT DEFAULT 0;
DECLARE statename CHAR(255) DEFAULT '';
DECLARE statevariation CHAR(255) DEFAULT '';
DECLARE ttipo CHAR(100) DEFAULT '';
DECLARE resultado VARCHAR(500)  DEFAULT tvariation;
DECLARE statevar FLOAT DEFAULT 0;
DECLARE statevar2 FLOAT DEFAULT 0;
DECLARE statemin FLOAT DEFAULT 0;
DECLARE statemax FLOAT DEFAULT 0;
DECLARE newtvariation varchar(500) DEFAULT '';
SELECT tr.TraitTipo INTO ttipo FROM Traits as tr WHERE tr.TraitID=ttid;
/* SE E CATEGORIA PEGA OS NOMES DAS CATEGORIAS PELOS SEUS IDS */
IF (ttipo='Variavel|Categoria') THEN
		SELECT substrCount(tvariation,';')+1 INTO ncat;
		WHILE ncatstep <= ncat DO
			SET statename = '';
			SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(tvariation,';',ncatstep),';',-1)) INTO statesids;
			SELECT TraitName INTO statename FROM Traits WHERE TraitID=statesids;
			IF (ncatstep=1) THEN
				SET statevariation = statename;
			ELSE
				SET statevariation = CONCAT(statevariation,'; ',statename);
			END IF;
			SET ncatstep = ncatstep+1;
		END WHILE;
END IF;
/*SE FOR QUANTITATIVO, PADRONIZA A UNIDADE E OU CALCULA A MEDIA, MINIMO E MAXIMO */
IF (ttipo='Variavel|Quantitativo') THEN
	IF (tvariation<>'') THEN
		SET ncatstep=1;
		SELECT substrCount(tvariation,';')+1 INTO ncat;
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
			IF (trunit='mm' AND tunit='m') THEN
				SET statevar2 = (statevar2)/1000;
			END IF; 
			IF (trunit='m' AND tunit='mm') THEN
				SET statevar2 = (statevar2)*1000;
			END IF; 
			IF (ncatstep=1) THEN
				SET statevar = statevar2;
				SET statemax = statevar2;
				SET newtvariation  = statevar2;
			ELSE
				IF (statevar2>statemax) THEN
					SET statemax = statevar2;
				END IF;
				SET statevar = statevar+statevar2;
				SET newtvariation = CONCAT(newtvariation,";",statevar2);
			END IF;
			SET ncatstep = ncatstep+1;
		END WHILE;
		IF (somedia) THEN
			SET statevariation = ROUND(statevar/ncat,2);
		ELSE
			IF (somax) THEN
				SET statevariation = ROUND(statemax,2);
			ELSE 
				SET statevariation = newtvariation;
			END IF;
		END IF;
	END IF;
END IF;
SET resultado = statevariation;
RETURN resultado;
END

