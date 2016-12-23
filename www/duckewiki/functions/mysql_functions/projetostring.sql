CREATE FUNCTION projetostring(projid int(10),printprocesso BOOLEAN, printtags  BOOLEAN) RETURNS VARCHAR(200) CHARSET utf8
BEGIN
DECLARE nfunds INT(10) DEFAULT 0;
DECLARE ncatstep INT(10) DEFAULT 0;
DECLARE basvar CHAR(100) DEFAULT '';
DECLARE idvar INT(10) DEFAULT 0;
DECLARE resultado VARCHAR(200) DEFAULT '';
DECLARE respar VARCHAR(200) DEFAULT '';
DECLARE projn VARCHAR(200) DEFAULT '';
DECLARE projfin VARCHAR(200) DEFAULT '';
DECLARE projproc VARCHAR(200) DEFAULT '';
DECLARE agg VARCHAR(200) DEFAULT '';
DECLARE proc VARCHAR(200) DEFAULT '';
SELECT ProjetoNome,Financiamento,Processos INTO projn,projfin,projproc FROM Projetos WHERE ProjetoID=projid;
SELECT substrCount(projfin,';')+1 INTO nfunds;
WHILE ncatstep <= nfunds DO
SET respar = '';
SELECT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(projfin,';',ncatstep),';',-1)) INTO agg;
SELECT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(projproc,';',ncatstep),';',-1)) INTO proc;
IF (agg<>'') THEN
IF (proc<>'' AND printprocesso) THEN
SET respar = CONCAT(agg,' \(',proc,'\)');
ELSE
SET respar = agg;
END IF;
IF (resultado='') THEN
IF (printtags) THEN
SET resultado = CONCAT('<u>Apoio</u>: ',respar);
ELSE 
SET resultado = CONCAT('Apoio: ',respar);
END IF;
ELSE
SET resultado = CONCAT(resultado,'; ',respar);
END IF;
END IF;
SET ncatstep = ncatstep+1;
END WHILE;
IF projn<>'' THEN
	SET resultado = CONCAT(projn,". ",resultado);
END IF;
RETURN resultado;
END
