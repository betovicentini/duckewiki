CREATE FUNCTION labeldescricaomodelo(ospecid int(10), aplantaid int(10), formid int(10)) RETURNS TEXT CHARSET utf8
BEGIN
DECLARE done INT DEFAULT 0;
DECLARE otraitid INT DEFAULT 0;
DECLARE contador INT DEFAULT 0;
DECLARE oprefixo VARCHAR(1000) DEFAULT '';
DECLARE osufixo VARCHAR(1000)  DEFAULT '';
DECLARE variacao VARCHAR(1000)  DEFAULT '';
DECLARE variacao2 VARCHAR(1000)  DEFAULT '';
DECLARE partres VARCHAR(1000)  DEFAULT '';
DECLARE oformato CHAR(50) DEFAULT '';
DECLARE ounitformat CHAR(10) DEFAULT '';
DECLARE ounitinclude CHAR(5)  DEFAULT '';
DECLARE onamostral CHAR(5) DEFAULT '';
DECLARE ocategpontuacao CHAR(5) DEFAULT '';
DECLARE descricao TEXT DEFAULT '';
DECLARE cur1 CURSOR FOR SELECT TraitID, prefixo , sufixo , formato , unitformat , unitinclude ,namostral ,categpontuacao FROM FormulariosTraitsList WHERE FormID=formid ORDER BY Ordem;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
OPEN cur1;
loop1: LOOP
SET partres = "";
FETCH cur1 INTO otraitid, oprefixo , osufixo , oformato , ounitformat , ounitinclude ,onamostral ,ocategpontuacao;
IF (ospecid>0 AND aplantaid=0) THEN
	SELECT traitvariation_formated(TraitVariation,otraitid,TraitUnit, ounitformat,"",";","")  INTO variacao FROM Traits_variation WHERE TraitID=otraitid AND EspecimenID=ospecid;
END IF;
IF (ospecid=0 AND aplantaid>0) THEN
	SELECT traitvariation_formated(TraitVariation,otraitid,TraitUnit, ounitformat,"",";","")  INTO variacao FROM Traits_variation WHERE TraitID=otraitid AND PlantaID=aplantaid;
	SELECT GROUP_CONCAT(traitvariation_formated(TraitVariation,otraitid,TraitUnit, ounitformat,"",";","")  SEPARATOR ';') INTO variacao2 FROM Monitoramento WHERE TraitID=otraitid AND PlantaID=aplantaid;
	IF (variacao<>"" AND variacao2<>'') THEN
		SET variacao = CONCAT(variacao,";",variacao2);
	END IF;
	IF (variacao="" AND variacao2<>'') THEN
		SET variacao = variacao2;
	END IF;
END IF;
IF (ospecid>0 AND aplantaid>0) THEN
	SELECT GROUP_CONCAT(traitvariation_formated(TraitVariation,otraitid,TraitUnit, ounitformat,"",";","")  SEPARATOR ';')  INTO variacao FROM Traits_variation WHERE TraitID=otraitid AND (PlantaID=aplantaid OR EspecimenID=ospecid);
	SELECT GROUP_CONCAT(traitvariation_formated(TraitVariation,otraitid,TraitUnit, ounitformat,"",";","")  SEPARATOR ';') INTO variacao2 FROM Monitoramento WHERE TraitID=otraitid AND PlantaID=aplantaid;
	IF (variacao<>'' AND variacao2<>'') THEN
		SET variacao = CONCAT(variacao,";",variacao2);
	END IF;
	IF (variacao='' AND variacao2<>'') THEN
		SET variacao = variacao2;
	END IF;
END IF;
IF (variacao<>"") THEN
	SELECT traitvariation_formated(variacao,otraitid,ounitformat, ounitformat,oformato,",",ounitinclude) INTO variacao; 
	SET partres = CONCAT(oprefixo," ",variacao," ",osufixo);
END IF;
IF (partres<>"") THEN
		IF contador=0 THEN
			SET descricao = partres;
		ELSE
			SET descricao = CONCAT(descricao,' ',partres);
		END IF;
		SET contador = contador+1;
END IF;
IF done=1 THEN
	CLOSE cur1;
	LEAVE loop1;
END IF;
END LOOP loop1;
SELECT TRIM(REPLACE(descricao,'  ',' ')) INTO descricao;
SELECT TRIM(REPLACE(descricao,'  ',' ')) INTO descricao;
SELECT TRIM(REPLACE(descricao,'  ',' ')) INTO descricao;
SELECT TRIM(REPLACE(descricao,'. .','.')) INTO descricao;
SELECT TRIM(REPLACE(descricao,'..','.')) INTO descricao;
SELECT TRIM(REPLACE(descricao,' .','.')) INTO descricao;
RETURN descricao;
END