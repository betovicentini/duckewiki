CREATE FUNCTION notastring(habid int(10), formid int(10), printn BOOLEAN, linktype CHAR(100)) RETURNS text CHARSET utf8
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
DECLARE valmin TEXT DEFAULT '';
DECLARE valmax TEXT DEFAULT '';
DECLARE habclass TEXT DEFAULT '';
DECLARE habtipo TEXT DEFAULT '';
DECLARE txlist TEXT DEFAULT '';
DECLARE txlist2 TEXT DEFAULT '';
DECLARE cur1 CURSOR FOR SELECT TraitTipo,TraitName,Traits.PathName,TraitVariation,Traits_variation.TraitUnit FROM Traits_variation JOIN Traits USING(TraitID) JOIN FormulariosTraitsList ON FormulariosTraitsList.TraitID=Traits.TraitID  WHERE Traits_variation.PlantaID=habid AND FormulariosTraitsList.FormID=formid  ORDER BY FormulariosTraitsList.Ordem;
DECLARE cur2 CURSOR FOR SELECT TraitTipo,TraitName,Traits.PathName,TraitVariation,Traits_variation.TraitUnit FROM Traits_variation JOIN Traits USING(TraitID) JOIN FormulariosTraitsList ON FormulariosTraitsList.TraitID=Traits.TraitID   WHERE Traits_variation.EspecimenID=habid AND FormulariosTraitsList.FormID=formid  ORDER BY FormulariosTraitsList.Ordem;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
IF (linktype='Plantas') THEN
OPEN cur1;
ELSE
OPEN cur2;
END IF;
loop1: LOOP
SET ncatstep = 1;
SET statevariation = '';
SET statevar2 = 0;
IF (linktype='Plantas') THEN
FETCH cur1 INTO ttipo,tname,tpathname,tvariation,tunit;
ELSE
FETCH cur2 INTO ttipo,tname,tpathname,tvariation,tunit;
END IF;
SET tname = TRIM(tname);
SELECT SUBSTRING_INDEX(tpathname ,'-',1) INTO ttbase2;
IF (ttbase2<>ttbase AND UPPER(ttbase2)<>'Coleta' AND UPPER(ttbase2)<>'Specimen'  AND UPPER(ttbase2)<>'HÁBITO' AND tvariation<>'' AND tvariation IS NOT NULL AND UPPER(tname)<>UPPER(ttbase2)) THEN
SET ttbase2 = TRIM(ttbase2);
SET ttbase=ttbase2;
IF contador=0 THEN
SET descricao = CONCAT('<b>',UPPER(ttbase2),'</b>');
ELSE
SET descricao = CONCAT(descricao,'. <b>',UPPER(ttbase2),'</b>');
END IF;
SET contador = contador+1;
END IF;
IF (tname LIKE 'Nota%' OR  tname LIKE 'Note%') THEN
SET tname = '';
END IF;
IF done=1 THEN
IF (linktype='Plantas') THEN
CLOSE cur1;
ELSE
CLOSE cur2;
END IF;
LEAVE loop1;
END IF;
IF ttipo='Variavel|Categoria' THEN
SELECT substrCount(tvariation,';')+1 INTO ncat;
WHILE ncatstep <= ncat DO
SET statename = '';
SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(tvariation,';',ncatstep),';',-1)) INTO trtid;
SELECT TraitName INTO statename FROM Traits WHERE TraitID=trtid;
IF (tname<>'') THEN 
SET statename = lower(statename); 
END IF;
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
IF ttipo='Variavel|Texto' THEN
IF contador=0 THEN
SET descricao = CONCAT(tname,' ',tvariation);
ELSE
SET descricao = CONCAT(descricao,'. ',tname,' ',tvariation);
END IF;
SET contador = contador+1;
END IF;
IF ttipo='Variavel|Quantitativo' THEN
SELECT substrCount(tvariation,';')+1 INTO ncat;
SET valmin = '';
SET valmax = '';
WHILE ncatstep <= ncat DO
SET statevar2 = 0;
SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(tvariation,';',ncatstep),';',-1)) INTO statevar2;
IF (ncatstep=1) THEN
SET statevar = statevar2;
ELSE
SET statevar = statevar+statevar2;
END IF;
IF (valmin='') THEN
SET valmin = statevar2;
ELSE
IF (statevar2<valmin) THEN
SET valmin = statevar2;
END IF;
END IF;
IF (valmax='') THEN
SET valmax = statevar2;
ELSE
IF (statevar2>valmax) THEN
SET valmax = statevar2;
END IF;
END IF;
SET ncatstep = ncatstep+1;
END WHILE;
SET statevar = round(statevar/ncat,2);
IF (statevar>0) THEN
IF (ncat=2) THEN
SET descpar = CONCAT(tname,' ',valmin,'-',valmax,' ',tunit);
IF (printn) THEN
SET descpar = CONCAT(descpar,' \(N=',ncat,'\)');
END IF;
ELSE
IF (ncat>2) THEN
SET descpar = CONCAT(tname,' ',statevar,' [',valmin,'-',valmax,'] ',tunit);
IF (printn) THEN
SET descpar = CONCAT(descpar,' \(N=',ncat,'\)');
END IF;
ELSE
SET descpar = CONCAT(tname,' ',tvariation,' ',tunit);
END IF;
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
SET descricao = CONCAT(descricao,'. ');
SELECT TRIM(REPLACE(descricao,'..','.')) INTO descricao;
RETURN descricao;
END
