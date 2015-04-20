CREATE FUNCTION monitoramentostring(habid int(10), formid int(10), printnobs BOOLEAN, printimgtag BOOLEAN) RETURNS TEXT CHARSET utf8
BEGIN
DECLARE done INT DEFAULT 0;
DECLARE respar TEXT DEFAULT '';
DECLARE resultado TEXT DEFAULT '';
DECLARE lixo TEXT DEFAULT '';
DECLARE contador INT DEFAULT 0;
DECLARE tvariation VARCHAR(500) DEFAULT '';
DECLARE tname CHAR(100) DEFAULT '';
DECLARE tunit CHAR(100)  DEFAULT '';
DECLARE ttipo CHAR(100)  DEFAULT '';
DECLARE toprint CHAR(100)  DEFAULT '';
DECLARE trtid INT(10) DEFAULT 0;
DECLARE tpathname CHAR(100)  DEFAULT '';
DECLARE ncat INT(10) DEFAULT 0;
DECLARE statename CHAR(100)  DEFAULT '';
DECLARE statevariation CHAR(100)  DEFAULT '';
DECLARE statevar FLOAT DEFAULT 0;
DECLARE descricao TEXT DEFAULT '';
DECLARE ttbase CHAR(100)  DEFAULT '';
DECLARE ttbase2 CHAR(100)  DEFAULT '';
DECLARE descpar CHAR(100)   DEFAULT '';
DECLARE valmin CHAR(100)  DEFAULT '';
DECLARE valmax CHAR(100)  DEFAULT '';
DECLARE habclass CHAR(100)  DEFAULT '';
DECLARE habtipo CHAR(100)  DEFAULT '';
DECLARE txlist CHAR(255)  DEFAULT '';
DECLARE txlist2 CHAR(255) DEFAULT '';
DECLARE tdate CHAR(100) DEFAULT '';
DECLARE trtid2 INT DEFAULT 0;
DECLARE cur1 CURSOR FOR SELECT trt.TraitID,trt.TraitTipo,trt.TraitName,trt.PathName,moni.TraitVariation,moni.TraitUnit,moni.DataObs FROM Monitoramento as moni JOIN Traits as trt USING(TraitID) WHERE moni.PlantaID=habid AND (trt.FormulariosIDS LIKE CONCAT('%formid_',formid) OR trt.FormulariosIDS LIKE CONCAT('%formid_',formid,';%')) ORDER BY trt.PathName,trt.TraitID,moni.DataObs;
DECLARE cur2 CURSOR FOR SELECT DISTINCT trt.TraitID,trt.TraitTipo,trt.TraitName,trt.PathName,moni.TraitVariation,moni.TraitUnit,moni.DataObs FROM Monitoramento as moni JOIN Traits as trt USING(TraitID) WHERE moni.PlantaID=habid ORDER BY trt.PathName,trt.TraitID,moni.DataObs;
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
SELECT monitoramentosubstring(habid,trtid,printnobs, printimgtag,tdate) INTO statevariation; 
IF (statevariation<>'') THEN
	SET tname = TRIM(tname);
	SELECT SUBSTRING_INDEX(tpathname ,'-',1) INTO ttbase2;
	IF (ttbase2<>ttbase AND ttbase2<>'Coleta' AND tvariation<>'' AND tvariation IS NOT NULL AND UPPER(tname)<>UPPER(ttbase2)) THEN
		SET ttbase2 = TRIM(ttbase2);
		SET ttbase=ttbase2;
		IF contador=0 THEN
			SET descricao = CONCAT('<b>',UPPER(ttbase2),'</b>');
			SET contador = contador+1;
		ELSE
			SET descricao = CONCAT(descricao,'\n<b>',UPPER(ttbase2),'</b>');
		END IF;
	END IF;
	IF trtid2<>trtid  THEN
		SET trtid2 = trtid;
		SET descpar = CONCAT("\n    ",tname,': ');
	ELSE 
		SET descpar = ', ';
	END IF;
	IF contador=0 THEN
		SET descricao = CONCAT(descpar,statevariation,' \(',tdate,'\)');
		SET contador = contador+1;
	ELSE
		SET descricao = CONCAT(descricao,descpar,statevariation,' \(',tdate,'\)');
	END IF;
END IF;
IF (done=1) THEN
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
