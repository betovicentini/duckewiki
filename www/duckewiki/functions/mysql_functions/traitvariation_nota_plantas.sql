CREATE FUNCTION traitvariation_nota_plantas(formid int(10), linkid INT(10)) RETURNS text CHARSET utf8
BEGIN
DECLARE done INT DEFAULT 0;
DECLARE respar TEXT DEFAULT '';
DECLARE resultado TEXT DEFAULT '';
DECLARE lixo TEXT DEFAULT '';
DECLARE contador INT DEFAULT 0;
DECLARE tvariation TEXT DEFAULT '';
DECLARE tname TEXT DEFAULT '';
DECLARE tunit TEXT DEFAULT '';
DECLARE ttipo TEXT DEFAULT '';
DECLARE toprint TEXT DEFAULT '';
DECLARE trtid INT(10) DEFAULT 0;
DECLARE tpathname TEXT DEFAULT '';
DECLARE ncat INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
DECLARE statename TEXT DEFAULT '';
DECLARE statevariation TEXT DEFAULT '';
DECLARE statevar FLOAT DEFAULT 0;
DECLARE statevar2 FLOAT DEFAULT 0;
DECLARE descricao TEXT DEFAULT '';
DECLARE ttbase TEXT DEFAULT '';
DECLARE ttbase2 TEXT DEFAULT '';
DECLARE descpar TEXT DEFAULT '';
DECLARE cur1 CURSOR FOR SELECT TraitTipo,TraitName,Traits.PathName,TraitVariation,Traits_variation.TraitUnit FROM Traits_variation JOIN Traits USING(TraitID) JOIN FormulariosTraitsList ON FormulariosTraitsList.TraitID=Traits.TraitID  WHERE Traits_variation.EspecimenID=linkid AND FormulariosTraitsList.FormID=formid  ORDER BY FormulariosTraitsList.Ordem;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
OPEN cur1;
loop1: LOOP
SET ncatstep = 1;
SET statevariation = '';
SET statevar2 = 0;
FETCH cur1 INTO ttipo,tname,tpathname,tvariation,tunit;
SET tname = TRIM(tname);
SELECT SUBSTRING_INDEX(tpathname ,'-',1) INTO ttbase2;
IF (ttbase2<>ttbase) THEN
SET ttbase2 = TRIM(ttbase2);
SET ttbase=ttbase2;
IF contador=0 THEN
SET descricao = UPPER(ttbase2);
ELSE
SET descricao = CONCAT(descricao,'. ',UPPER(ttbase2));
END IF;
SET contador = contador+1;
END IF;
IF done=1 THEN
CLOSE cur1;
LEAVE loop1;
END IF;
IF ttipo='Variavel|Categoria' THEN
SELECT substrCount(tvariation,';')+1 INTO ncat;
WHILE ncatstep <= ncat DO
SET statename = '';
SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(tvariation,';',ncatstep),';',-1)) INTO trtid;
SELECT TraitName INTO statename FROM Traits WHERE TraitID=trtid;
SET statename = lower(statename);
IF (ncatstep=1) THEN
SET statevariation = statename;
ELSE
SET statevariation = CONCAT(statevariation,'; ',statename);
END IF;
SET ncatstep = ncatstep+1;
END WHILE;
IF (statevariation<>'') THEN
IF contador=0 THEN
SET descricao = CONCAT(tname,' ',statevariation);
ELSE
SET descricao = CONCAT(descricao,'. ',tname,' ',statevariation);
END IF;
SET contador = contador+1;
END IF;
END IF;
IF ttipo='Variavel|Quantitativo' THEN
SELECT substrCount(tvariation,';')+1 INTO ncat;
WHILE ncatstep <= ncat DO
SET statevar2 = 0;
SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(tvariation,';',ncatstep),';',-1)) INTO statevar2;
IF (ncatstep=1) THEN
SET statevar = statevar2;
ELSE
SET statevar = statevar+statevar2;
END IF;
SET ncatstep = ncatstep+1;
END WHILE;
SET statevar = round(statevar/ncat,2);
IF (statevar>0) THEN
IF ncat>1 THEN
SET descpar = CONCAT(tname,' ',tvariation,' (media: ',statevar,' ',tunit,' N=',ncat,')');
ELSE
SET descpar = CONCAT(tname,' ',tvariation,' ',tunit);
END IF;
IF contador=0 THEN
SET descricao = descpar;
ELSE
SET descricao = CONCAT(descricao,'. ',descpar);
END IF;
SET contador = contador+1;
END IF;
END IF;
END LOOP loop1;
RETURN descricao;
END
