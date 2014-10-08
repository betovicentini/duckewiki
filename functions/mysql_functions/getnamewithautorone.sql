CREATE FUNCTION getnamewithautorone(idd INT(10),nmid CHAR(50), morftp BOOLEAN, autors BOOLEAN) RETURNS text CHARSET utf8
BEGIN
DECLARE infbasiotest INT(10) DEFAULT 0;
DECLARE spbasiotest INT(10) DEFAULT 0;
DECLARE fam VARCHAR(150) DEFAULT '';
DECLARE gen VARCHAR(100) DEFAULT '';
DECLARE sp VARCHAR(150) DEFAULT '';
DECLARE spaut VARCHAR(150) DEFAULT '';
DECLARE spbasio VARCHAR(150) DEFAULT '';
DECLARE infrni VARCHAR(150) DEFAULT '';
DECLARE infsp VARCHAR(150) DEFAULT '';
DECLARE infbasio VARCHAR(150) DEFAULT '';
DECLARE infaut VARCHAR(150) DEFAULT '';
DECLARE nome VARCHAR(500) DEFAULT '';
DECLARE nnsp VARCHAR(100) DEFAULT '';
DECLARE npess INT(10) DEFAULT 0;
DECLARE famid INT(10) DEFAULT 0;
DECLARE genid INT(10) DEFAULT 0;
DECLARE specid INT(10) DEFAULT 0;
DECLARE infspid INT(10) DEFAULT 0;
DECLARE nometxt CHAR(10) DEFAULT '';
SELECT SUBSTRING(nmid,1,5) INTO nometxt;
IF nometxt='famid' THEN    
SET famid= idd;
END IF;
IF nometxt='genus' THEN    
SET genid= idd;
END IF;
IF nometxt='speci' THEN    
SET specid= idd;
END IF;
IF nometxt='infsp' THEN    
SET infspid= idd;
END IF;
IF infspid>0 THEN
	SELECT TRIM(Tax_Familias.Familia),TRIM(Tax_Generos.Genero),TRIM(Tax_Especies.Especie),TRIM(Tax_Especies.EspecieAutor),TRIM(Tax_Especies.BasionymAutor),TRIM(Tax_InfraEspecies.InfraEspecieNivel),TRIM(Tax_InfraEspecies.InfraEspecie),TRIM(Tax_InfraEspecies.BasionymAutor),TRIM(Tax_InfraEspecies.InfraEspecieAutor) INTO fam, gen, sp, spaut, spbasio, infrni, infsp, infbasio, infaut FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE InfraEspecieID=infspid;
	IF infbasio<>'' THEN
		SELECT infaut LIKE CONCAT('%',infbasio,'%') INTO infbasiotest;
		IF (infbasiotest=0) THEN
			SET infaut = CONCAT(infbasio,' ',infaut);
		END IF;
	END IF;	
	IF spbasio<>'' THEN
		SELECT spaut LIKE CONCAT('%',spbasio,'%') INTO spbasiotest;
		IF (spbasiotest=0) THEN
			SET spaut = CONCAT(spbasio,' ',spaut);
		END IF;
	END IF;	
	SET infaut = TRIM(infaut);
	SET nnsp = UPPER(SUBSTRING(sp,1,3));
	IF (nnsp='SP.' OR infrni='morfossp' OR infrni='') THEN
			SET nome = 'morfotipo';
	ELSE 
			SET nome = 'infraespecie';
	END IF;
	IF (nome='morfotipo' AND  morftp=0) THEN
    	SET nome = 'especie';
    ELSE 
        IF autors=1 THEN
	    	SET nome = CONCAT(gen," ",sp," ",spaut," ",infrni," ",infsp," ",infaut);    
	    ELSE 
	    	SET nome = CONCAT(gen," ",sp," ",infrni," ",infsp);    
	    END IF;
    END IF;
END IF;
IF ((nome='especie' OR infspid=0)  AND specid>0) THEN
	SELECT TRIM(Tax_Familias.Familia),TRIM(Tax_Generos.Genero),TRIM(Tax_Especies.Especie),TRIM(Tax_Especies.EspecieAutor),TRIM(Tax_Especies.BasionymAutor) INTO fam,gen,sp,spaut,spbasio FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE EspecieID=specid;
	IF spbasio<>'' THEN
		SELECT spaut LIKE CONCAT('%',spbasio,'%') INTO spbasiotest;
		IF (spbasiotest=0) THEN
			SET spaut = CONCAT(spbasio,' ',spaut);
		END IF;
	END IF;	
	SET spaut = TRIM(spaut);
	SET nnsp = UPPER(SUBSTRING(sp,1,3));
	IF (nnsp='SP.' OR spaut='') THEN
			SET nome = 'morfotipo';
	ELSE 
		SET nome = 'especie';
	END IF;
	IF (nome='morfotipo' AND  morftp=0) THEN
    	SET nome = 'genero';
    ELSE 
        IF autors=1 THEN
	    	SET nome = CONCAT(gen," ",sp," ",spaut);    
	    ELSE 
	    	SET nome = CONCAT(gen," ",sp);    
	    END IF;  
    END IF;
END IF;    
IF ((nome='genero' OR specid=0) AND genid>0) THEN	
	SELECT TRIM(Tax_Familias.Familia),TRIM(Tax_Generos.Genero) INTO fam,gen FROM Tax_Generos JOIN Tax_Familias USING(FamiliaID) WHERE GeneroID=genid;
	IF (UPPER(gen)='INDET') THEN
		SET nome = 'familia';
	ELSE 
		SET nome = gen;
	END IF;
END IF;    
IF ((nome='familia' OR genid=0) AND famid>0) THEN
	SELECT Tax_Familias.Familia INTO fam FROM Tax_Familias WHERE FamiliaID=famid;
	IF (UPPER(fam)='INDET') THEN
		SET nome = NULL;
	ELSE 
		SET nome = fam;
	END IF;
END IF;
RETURN nome;
END
