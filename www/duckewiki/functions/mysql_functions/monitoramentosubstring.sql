CREATE FUNCTION monitoramentosubstring(plantid int(10), trtraitid int(10), printn BOOLEAN, printimg BOOLEAN,dataobsval  DATE) RETURNS VARCHAR(500) CHARSET utf8
BEGIN
DECLARE done INT DEFAULT 0;
DECLARE respar VARCHAR(500) DEFAULT '';
DECLARE resultado VARCHAR(500) DEFAULT '';
DECLARE lixo VARCHAR(500) DEFAULT '';
DECLARE contador INT DEFAULT 0;
DECLARE tvariation VARCHAR(500) DEFAULT '';
DECLARE tname CHAR(255) DEFAULT '';
DECLARE tunit CHAR(100) DEFAULT '';
DECLARE ttipo CHAR(255)  DEFAULT '';
DECLARE toprint CHAR(255)  DEFAULT '';
DECLARE trtid INT(10) DEFAULT 0;
DECLARE tpathname CHAR(255)  DEFAULT '';
DECLARE ncat INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
DECLARE statename CHAR(255)  DEFAULT '';
DECLARE statevariation CHAR(255)  DEFAULT '';
DECLARE statevar FLOAT DEFAULT 0;
DECLARE statevar2 FLOAT DEFAULT 0;
DECLARE descricao VARCHAR(500)  DEFAULT '';
DECLARE ttbase CHAR(100)  DEFAULT '';
DECLARE ttbase2 CHAR(100)  DEFAULT '';
DECLARE descpar CHAR(255)  DEFAULT '';
DECLARE valmin CHAR(100)  DEFAULT '';
DECLARE valmax CHAR(100)  DEFAULT '';
DECLARE habclass CHAR(100)  DEFAULT '';
DECLARE habtipo CHAR(100)  DEFAULT '';
DECLARE txlist CHAR(255)  DEFAULT '';
DECLARE txlist2 CHAR(255)  DEFAULT '';
DECLARE tdate CHAR(100)  DEFAULT '';
DECLARE taxtipo CHAR(100)  DEFAULT '';
DECLARE taxname CHAR(100)  DEFAULT '';
DECLARE taxid INT(10) DEFAULT 0;
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
IF ttipo='Variavel|LinkEspecimenes' THEN
SELECT CONCAT(pess.Abreviacao,' ',spec.Number,'    -  ', if(gettaxonname(spec.DetID,1,0) IS NULL,'',gettaxonname(spec.DetID,1,0))) INTO statevariation FROM Especimenes as spec JOIN Pessoas as pess ON spec.ColetorID=pess.PessoaID WHERE spec.EspecimenID=tvariation;
END IF;
IF ttipo='Variavel|Taxonomy' THEN
	SELECT substrCount(tvariation,';')+1 INTO ncat;
	SET ncatstep=1;
	WHILE ncatstep <= ncat DO
		SET statename = '';
		SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(tvariation,';',ncatstep),';',-1)) INTO taxname;
		SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(taxname,'|',1),'|',-1)) INTO taxtipo;
		SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(taxname,'|',2),'|',-1)) INTO taxid;
		SET taxtipo = TRIM(taxtipo);
		IF (taxtipo='infraspecies') THEN
			SELECT gettaxonname2(0,0,0, 0,taxid, 1,0)  INTO statename;
		END IF;
		IF (taxtipo='especie') THEN
				SELECT gettaxonname2(0,0,0,taxid, 0, 1,0)  INTO statename;
		END IF; 
		IF (taxtipo='genero') THEN
					SELECT gettaxonname2(0,0,taxid, 0, 0, 1,0)  INTO statename;
		END IF;
		IF (taxtipo='familia') THEN
					SELECT gettaxonname2(0,taxid, 0, 0, 0, 1,0) INTO statename;
		END IF;
		IF (ncatstep=1) THEN
			SET statevariation = statename;
		ELSE
			SET statevariation = CONCAT(statevariation,'; ',statename);
		END IF;
		SET ncatstep = ncatstep+1;
END WHILE;
END IF;
RETURN statevariation;
END
