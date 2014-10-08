CREATE FUNCTION monitoramentosubstring(plantid int(10), trtraitid int(10), printn BOOLEAN, printimg BOOLEAN,dataobsval  DATE) RETURNS text CHARSET utf8
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
DECLARE tdate TEXT DEFAULT '';
SELECT trt.TraitTipo,trt.TraitName,trt.PathName,moni.TraitVariation,moni.TraitUnit,moni.DataObs INTO ttipo,tname,tpathname,tvariation,tunit,tdate FROM Monitoramento as moni JOIN Traits as trt USING(TraitID) WHERE moni.PlantaID=plantid AND trt.TraitID=trtraitid AND moni.DataObs=dataobsval;
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
END IF;
IF ttipo='Variavel|Texto' THEN
SET statevariation = tvariation;
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
SET statevariation = CONCAT(valmin,'-',valmax,' ',tunit);
IF (printn) THEN
SET statevariation = CONCAT(statevariation,' \(N=',ncat,'\)');
END IF;
ELSE
IF (ncat>2) THEN
SET statevariation = CONCAT(statevar,' [',valmin,'-',valmax,'] ',tunit);
IF (printn) THEN
SET statevariation = CONCAT(statevariation,' \(N=',ncat,'\)');
END IF;
ELSE
SET statevariation = CONCAT(tvariation,' ',tunit);
END IF;
END IF;
END IF;
END IF;
IF ttipo='Variavel|Imagem' THEN
SELECT substrCount(tvariation,';')+1 INTO ncat;
SET statename = '';
IF (ncat>0) THEN
SET statevariation = CONCAT('<img src=\"icons/ico_open.gif\" onclick = \"javascript:small_window(\'showpicture.php?fn=',tvariation,',700,500,\'MostrarImg\');\">','&nbsp;<b>',ncat,'</b>&nbsp;imgs');
END IF;
END IF;
RETURN statevariation;
END
