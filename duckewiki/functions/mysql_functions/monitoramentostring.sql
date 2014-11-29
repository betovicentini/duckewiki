CREATE FUNCTION monitoramentostring(habid int(10), formid int(10), printnobs BOOLEAN, printimgtag BOOLEAN) RETURNS text CHARSET utf8
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
DECLARE trtid2 INT DEFAULT 0;
DECLARE cur1 CURSOR FOR SELECT trt.TraitID,trt.TraitTipo,trt.TraitName,trt.PathName,moni.TraitVariation,moni.TraitUnit,moni.DataObs FROM Monitoramento as moni JOIN Traits as trt USING(TraitID) WHERE moni.PlantaID=habid AND (trt.FormulariosIDS LIKE CONCAT('%formid_',formid) OR trt.FormulariosIDS LIKE CONCAT('%formid_',formid,';%')) ORDER BY trt.PathName,trt.TraitID,moni.DataObs;
DECLARE cur2 CURSOR FOR SELECT trt.TraitID,trt.TraitTipo,trt.TraitName,trt.PathName,moni.TraitVariation,moni.TraitUnit,moni.DataObs FROM Monitoramento as moni JOIN Traits as trt USING(TraitID) WHERE moni.PlantaID=habid ORDER BY trt.PathName,trt.TraitID,moni.DataObs;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
IF (formid>0) THEN
OPEN cur1;
ELSE 
OPEN cur2;
END IF;
loop1: LOOP
IF (formid>0) THEN
FETCH cur1 INTO trtid,ttipo,tname,tpathname,tvariation,tunit,tdate;
ELSE 
FETCH cur2 INTO trtid,ttipo,tname,tpathname,tvariation,tunit,tdate;
END IF;
SET ncatstep = 1;
SET statevar2 = 0;
SELECT monitoramentosubstring(habid,trtid,printnobs, printimgtag,tdate) INTO statevariation; 
IF (statevariation<>'') THEN
SET tname = TRIM(tname);
SELECT SUBSTRING_INDEX(tpathname ,'-',1) INTO ttbase2;
IF (ttbase2<>ttbase AND ttbase2<>'Coleta' AND tvariation<>'' AND tvariation IS NOT NULL AND UPPER(tname)<>UPPER(ttbase2)) THEN
SET ttbase2 = TRIM(ttbase2);
SET ttbase=ttbase2;
IF contador=0 THEN
SET descricao = CONCAT('<b>',UPPER(ttbase2),'</b>');
ELSE
SET descricao = CONCAT(descricao,'\n<b>',UPPER(ttbase2),'</b>');
END IF;
SET contador = contador+1;
END IF;
IF trtid2<>trtid  THEN
SET trtid2 = trtid;
SET descpar = CONCAT("\n    ",tname,': ');
ELSE 
SET descpar = ', ';
END IF;
IF contador=0 THEN
SET descricao = CONCAT(descpar,statevariation,' \(',tdate,'\)');
ELSE
SET descricao = CONCAT(descricao,descpar,statevariation,' \(',tdate,'\)');
END IF;
SET contador = contador+1;
END IF;
IF done=1 THEN
IF (formid>0) THEN
CLOSE cur1;
ELSE 
CLOSE cur2;
END IF;
LEAVE loop1;
END IF;
END LOOP loop1;
SET descricao = CONCAT(descricao,'. ');
SELECT TRIM(REPLACE(descricao,'..','.')) INTO descricao;
RETURN descricao;
END
