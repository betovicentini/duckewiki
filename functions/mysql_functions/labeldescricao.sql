CREATE FUNCTION labeldescricao(speclinkid int(10), pltid int(10), formid int(10), printn BOOLEAN, printclass BOOLEAN) RETURNS text CHARSET utf8
BEGIN
DECLARE done INT DEFAULT 0;
DECLARE ordd INT DEFAULT 0;
DECLARE contador INT DEFAULT 0;
DECLARE tvariation VARCHAR(1000) DEFAULT '';
DECLARE tvar VARCHAR(1000)  DEFAULT '';
DECLARE tname CHAR(255) DEFAULT '';
DECLARE tname2 CHAR(255)  DEFAULT '';
DECLARE tname3 CHAR(255) DEFAULT '';
DECLARE tunit CHAR(100)  DEFAULT '';
DECLARE ttipo CHAR(100) DEFAULT '';
DECLARE trtid INT(10) DEFAULT 0;
DECLARE tpathname CHAR(255) DEFAULT '';
DECLARE ncat INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 1;
DECLARE statename CHAR(255) DEFAULT '';
DECLARE statename2 CHAR(255) DEFAULT '';
DECLARE statevariation VARCHAR(500) DEFAULT '';
DECLARE statevariation2 VARCHAR(500) DEFAULT '';
DECLARE statevar FLOAT DEFAULT 0;
DECLARE statevar2 FLOAT DEFAULT 0;
DECLARE descricao TEXT DEFAULT '';
DECLARE ttbase CHAR(255)  DEFAULT '';
DECLARE ttbase2 CHAR(255)  DEFAULT '';
DECLARE descpar CHAR(255)  DEFAULT '';
DECLARE valmin CHAR(100) DEFAULT '';
DECLARE valmax CHAR(100) DEFAULT '';
DECLARE habclass CHAR(255) DEFAULT '';
DECLARE habtipo CHAR(100) DEFAULT '';
DECLARE cur1 CURSOR FOR SELECT * FROM ((SELECT TraitTipo,TraitName,Traits.PathName,TraitVariation,Traits_variation.TraitUnit,formlist.Ordem FROM Traits_variation JOIN Traits USING(TraitID) JOIN FormulariosTraitsList as formlist ON formlist.TraitID=Traits.TraitID WHERE Traits_variation.EspecimenID=speclinkid AND formlist.FormID=formid) UNION (SELECT TraitTipo,TraitName,Traits.PathName,TraitVariation,Monitoramento.TraitUnit,formlist.Ordem  FROM Monitoramento JOIN Traits USING(TraitID) JOIN FormulariosTraitsList as formlist ON formlist.TraitID=Traits.TraitID WHERE Monitoramento.PlantaID=pltid AND formlist.FormID=formid)) as firstab ORDER BY firstab.Ordem;
DECLARE cur1a CURSOR FOR SELECT * FROM ((SELECT TraitTipo,TraitName,Traits.PathName,TraitVariation,Traits_variation.TraitUnit,0 as Ordem FROM Traits_variation JOIN Traits USING(TraitID) WHERE Traits_variation.EspecimenID=speclinkid ORDER BY Traits.PathName) UNION (SELECT TraitTipo,TraitName,Traits.PathName,TraitVariation,Monitoramento.TraitUnit,0 as Ordem FROM Monitoramento JOIN Traits USING(TraitID) WHERE Monitoramento.PlantaID=pltid ORDER BY Traits.PathName)) as firstaba ORDER BY firstaba.PathName;
DECLARE cur2 CURSOR FOR SELECT TraitTipo,TraitName,Traits.PathName,TraitVariation,Traits_variation.TraitUnit,formlist.Ordem FROM Traits_variation JOIN Traits USING(TraitID) JOIN FormulariosTraitsList as formlist ON formlist.TraitID=Traits.TraitID WHERE Traits_variation.EspecimenID=speclinkid AND formlist.FormID=formid ORDER BY formlist.Ordem;
DECLARE cur2a CURSOR FOR SELECT TraitTipo,TraitName,Traits.PathName,TraitVariation,Traits_variation.TraitUnit, 0 as Ordem FROM Traits_variation JOIN Traits USING(TraitID) WHERE Traits_variation.EspecimenID=speclinkid ORDER BY Traits.PathName;
DECLARE cur3 CURSOR FOR SELECT * FROM ((SELECT TraitTipo,TraitName,Traits.PathName,TraitVariation,Traits_variation.TraitUnit, formlist.Ordem  FROM Traits_variation JOIN Traits USING(TraitID) JOIN FormulariosTraitsList as formlist ON formlist.TraitID=Traits.TraitID WHERE Traits_variation.PlantaID=pltid AND formlist.FormID=formid) UNION (SELECT TraitTipo,TraitName,Traits.PathName,TraitVariation,Monitoramento.TraitUnit, formlist.Ordem  FROM Monitoramento JOIN Traits USING(TraitID) JOIN FormulariosTraitsList as formlist ON formlist.TraitID=Traits.TraitID WHERE Monitoramento.PlantaID=pltid AND formlist.FormID=formid) UNION (SELECT TraitTipo,TraitName,Traits.PathName,TraitVariation,Traits_variation.TraitUnit, formlist.Ordem  FROM Traits_variation JOIN Traits USING(TraitID) JOIN FormulariosTraitsList as formlist ON formlist.TraitID=Traits.TraitID WHERE Traits_variation.EspecimenID=speclinkid AND formlist.FormID=formid)) as newtab ORDER BY newtab.Ordem;
DECLARE cur3a CURSOR FOR SELECT * FROM ((SELECT TraitTipo,TraitName,Traits.PathName,TraitVariation,Traits_variation.TraitUnit, 0 as Ordem FROM Traits_variation JOIN Traits USING(TraitID) WHERE Traits_variation.PlantaID=pltid ORDER BY Traits.PathName) UNION (SELECT TraitTipo,TraitName,Traits.PathName,TraitVariation,Monitoramento.TraitUnit, 0 as Ordem FROM Monitoramento JOIN Traits USING(TraitID) WHERE Monitoramento.PlantaID=pltid ORDER BY Traits.PathName) UNION  (SELECT TraitTipo,TraitName,Traits.PathName,TraitVariation,Traits_variation.TraitUnit, 0 as Ordem FROM Traits_variation JOIN Traits USING(TraitID) WHERE Traits_variation.EspecimenID=speclinkid ORDER BY Traits.PathName)) as newtaba ORDER BY newtaba.PathName;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
IF (speclinkid=0 AND pltid>0) THEN
	IF (formid>0) THEN
		OPEN cur1;
	ELSE
		OPEN cur1a;
	END IF;
ELSE
	IF (speclinkid>0 AND pltid=0) THEN 
		IF (formid>0) THEN
			OPEN cur2;
		ELSE
			OPEN cur2a;
		END IF;
	ELSE
		IF (formid>0) THEN
			OPEN cur3;
		ELSE
			OPEN cur3a;
		END IF;
	END IF;
END IF;
loop1: LOOP
SET ncatstep = 1;
SET statevariation = '';
SET statevar2 = 0;
IF  (speclinkid=0 AND pltid>0) THEN
	IF (formid>0) THEN
		FETCH cur1 INTO ttipo,tname,tpathname,tvariation,tunit,ordd;
	ELSE
		FETCH cur1a INTO ttipo,tname,tpathname,tvariation,tunit,ordd;
	END IF;
ELSE
	IF (speclinkid>0 AND pltid=0) THEN 
		IF (formid>0) THEN
			FETCH cur2 INTO ttipo,tname,tpathname,tvariation,tunit,ordd;
		ELSE
			FETCH cur2a INTO ttipo,tname,tpathname,tvariation,tunit,ordd;
		END IF;
	ELSE
		IF (formid>0) THEN
			FETCH cur3 INTO ttipo,tname,tpathname,tvariation,tunit,ordd;
		ELSE
			FETCH cur3a INTO ttipo,tname,tpathname,tvariation,tunit,ordd;
		END IF;
	END IF;
END IF;
SET tname = TRIM(tname);
SET tname3 = tname;
SELECT SUBSTRING_INDEX(tpathname ,'-',1) INTO ttbase2;
IF (ttbase2<>ttbase AND UPPER(ttbase2)<>'COLETA' AND UPPER(ttbase2)<>'SPECIMEN'  AND UPPER(acentostosemacentos(ttbase2))<>'HABITO' AND tvariation<>'' AND tvariation IS NOT NULL AND UPPER(tname)<>UPPER(ttbase2)) THEN
	SET ttbase2 = TRIM(ttbase2);
	SET ttbase=ttbase2;
	IF printclass THEN
		IF contador=0 THEN
			SET descricao = CONCAT('<b>',UPPER(ttbase2),'</b>');
		ELSE
			SET descricao = CONCAT(descricao,'. <b>',UPPER(ttbase2),'</b>');
		END IF;
	END IF;
	SET contador = contador+1;
END IF;
IF (tname LIKE 'Nota%' OR  tname LIKE 'Note%' OR acentostosemacentos(tname)=acentostosemacentos(tname2) OR UPPER(acentostosemacentos(tname))='HABITO') THEN
	SET tname = '';
ELSE 
	SET statevariation2 = '';
END IF;
IF done=1 THEN
	IF (speclinkid=0 AND pltid>0) THEN
		IF (formid>0) THEN
			CLOSE cur1;
		ELSE
			CLOSE cur1a;
		END IF;
	ELSE
		IF (speclinkid>0 AND pltid=0) THEN 
			IF (formid>0) THEN
				CLOSE cur2;
			ELSE
				CLOSE cur2a;
			END IF;
		ELSE
			IF (formid>0) THEN
				CLOSE cur3;
			ELSE
				CLOSE cur3a;
			END IF;
		END IF;
	END IF;
	LEAVE loop1;
END IF;
IF ttipo='Variavel|Categoria' THEN
	SELECT substrCount(tvariation,';')+1 INTO ncat;
	WHILE ncatstep <= ncat DO
		SET statename = '';
		SELECT (SUBSTRING_INDEX(SUBSTRING_INDEX(tvariation,';',ncatstep),';',-1)) INTO trtid;
		SELECT TraitName INTO statename FROM Traits WHERE TraitID=trtid;
		IF (tname<>'' OR tname3=tname2) THEN 
			SET statename = lower(statename); 
		END IF;
		IF ((statevariation LIKE CONCAT('%',statename,'%')) OR (lower(statevariation2) LIKE CONCAT('%',lower(statename),'%'))) THEN
			SET statevariation = statevariation;
		ELSE 
			IF (TRIM(statevariation)='') THEN
				SET statevariation = statename;
			ELSE
				SET statevariation = CONCAT(statevariation,'; ',statename);
			END IF;
		END IF;
		SET ncatstep = ncatstep+1;
		IF (statevariation2<>'') THEN
			SET statevariation2 = CONCAT(statevariation2,"; ",statevariation);
		ELSE 
			SET statevariation2 = statevariation;
		END IF;
	END WHILE;
	SET statevariation = TRIM(statevariation);
	IF (statevariation<>'') THEN
		IF contador=0 THEN
			SET descricao = CONCAT(tname,' ',statevariation);
		ELSE
			IF (tname='' OR tname3=tname2) THEN
				SET descricao = CONCAT(descricao,'; ',statevariation);
			ELSE 
				SET descricao = CONCAT(descricao,'. ',tname,' ',statevariation);
			END IF;
		END IF;
		SET contador = contador+1;
	END IF;
END IF;
IF ttipo='Variavel|Texto' THEN
	SET tvar = CONCAT(UPPER(SUBSTRING(tvariation, 1, 1)), LOWER(SUBSTRING(tvariation FROM 2)));
	SET tvar = TRIM(tvar);
	IF tvar<>'' THEN
		IF contador=0 THEN
			SET descricao = CONCAT(tname,' ',tvar);
		ELSE
			SET descricao = CONCAT(descricao,'. ',tname,' ',tvar);
		END IF;
		SET contador = contador+1;
	END IF;
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
	IF tunit LIKE '%mero' THEN
		SET tunit = '';
	END IF;
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
			IF (tname<>'') THEN
				SET descricao = CONCAT(descricao,'. ',TRIM(descpar));
			ELSE 
				SET descricao = CONCAT(descricao,'-',TRIM(descpar));
			END IF;
		END IF;
		SET contador = contador+1;
	END IF;
END IF;
SET tname2 = tname3;
END LOOP loop1;
SET descricao = CONCAT(descricao,'. ');
SELECT TRIM(REPLACE(descricao,'  ',' ')) INTO descricao;
SELECT TRIM(REPLACE(descricao,'  ',' ')) INTO descricao;
SELECT TRIM(REPLACE(descricao,'  ',' ')) INTO descricao;
SELECT TRIM(REPLACE(descricao,'. .','.')) INTO descricao;
SELECT TRIM(REPLACE(descricao,'..','.')) INTO descricao;
SELECT TRIM(REPLACE(descricao,' .','.')) INTO descricao;
RETURN descricao;
END