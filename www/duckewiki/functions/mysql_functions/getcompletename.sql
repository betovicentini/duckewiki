CREATE FUNCTION getcompletename(identid INT(10), sinon BOOLEAN) RETURNS text CHARSET utf8
BEGIN
DECLARE famid INT(10) DEFAULT 0;
DECLARE genid INT(10) DEFAULT 0;
DECLARE specid INT(10) DEFAULT 0;
DECLARE infspid INT(10) DEFAULT 0;
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
DECLARE pubrev VARCHAR(500) DEFAULT '';
DECLARE pubvol VARCHAR(500) DEFAULT '';
DECLARE pubano VARCHAR(100) DEFAULT '';
DECLARE sinos VARCHAR(1000) DEFAULT '';
DECLARE nome VARCHAR(1000) DEFAULT '';
SELECT FamiliaID,GeneroID,EspecieID,InfraEspecieID INTO famid,genid,specid,infspid FROM Identidade WHERE DetID=identid;
IF infspid>0 THEN
	SELECT TRIM(Tax_Familias.Familia),TRIM(Tax_Generos.Genero),TRIM(Tax_Especies.Especie),TRIM(Tax_Especies.EspecieAutor),TRIM(Tax_Especies.BasionymAutor),TRIM(Tax_InfraEspecies.InfraEspecieNivel),TRIM(Tax_InfraEspecies.InfraEspecie),TRIM(Tax_InfraEspecies.BasionymAutor),TRIM(Tax_InfraEspecies.InfraEspecieAutor),TRIM(Tax_InfraEspecies.PubRevista), TRIM(Tax_InfraEspecies.PubVolume),TRIM(Tax_InfraEspecies.PubAno),TRIM( Tax_InfraEspecies.Sinonimos) INTO fam, gen, sp, spaut, spbasio, infrni, infsp, infbasio, infaut, pubrev, pubvol, pubano, sinos FROM Tax_InfraEspecies JOIN Tax_Especies USING(EspecieID) JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE InfraEspecieID=infspid;
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
	SET nome = CONCAT("<b><i>",gen," ",sp,"</i></b> ",spaut," ",infrni," <b><i>",infsp,"</i></b> ",infaut);	
	IF pubrev<>'' THEN
		IF (pubano='' OR (pubano+0)=0) THEN
			SET pubano = "<b>?data?</b>";
		END IF;
		SET nome = CONCAT(nome,", ",pubrev,", ",pubvol,", ",pubano,".");
	END IF;
ELSE
	IF specid>0 THEN
		SELECT TRIM(Tax_Familias.Familia),TRIM(Tax_Generos.Genero),TRIM(Tax_Especies.Especie),TRIM(Tax_Especies.EspecieAutor),TRIM(Tax_Especies.BasionymAutor),TRIM(Tax_Especies.PubRevista), TRIM(Tax_Especies.PubVolume), TRIM(Tax_Especies.PubAno), TRIM(Tax_Especies.Sinonimos)  INTO fam,gen,sp,spaut,spbasio,pubrev, pubvol, pubano, sinos FROM Tax_Especies JOIN Tax_Generos USING(GeneroID) JOIN Tax_Familias USING(FamiliaID) WHERE EspecieID=specid;
	IF spbasio<>'' THEN
		SELECT spaut LIKE CONCAT('%',spbasio,'%') INTO spbasiotest;
		IF (spbasiotest=0) THEN
			SET spaut = CONCAT(spbasio,' ',spaut);
		END IF;
	END IF;	
	SET nome = CONCAT("<b><i>",gen," ",sp,"</i></b> ",spaut);
	IF pubrev<>'' THEN
		IF (pubano='' OR (pubano+0)=0) THEN
			SET pubano = "<b>?data?</b>";
		END IF;
		SET nome = CONCAT(nome,", ",pubrev,", ",pubvol,", ",pubano,".");
	END IF;
	ELSE 
		IF genid>0 THEN
			SELECT TRIM(Tax_Familias.Familia),TRIM(Tax_Generos.Genero),TRIM(Tax_Generos.PubRevista), TRIM(Tax_Generos.PubVolume), TRIM(Tax_Generos.PubAno), TRIM(Tax_Generos.Sinonimos)  INTO fam,gen,pubrev, pubvol, pubano, sinos  FROM Tax_Generos JOIN Tax_Familias USING(FamiliaID) WHERE GeneroID=genid;
			SET nome = CONCAT("<b><i>",gen,"</i></b>");
			IF pubrev<>'' THEN
				IF (pubano='' OR (pubano+0)=0) THEN
					SET pubano = "<b>?data?</b>";
				END IF;
				SET nome = CONCAT(nome,", ",pubrev,", ",pubvol,", ",pubano,".");
			END IF;
		ELSE 
			SELECT Tax_Familias.Familia INTO fam FROM Tax_Familias WHERE FamiliaID=famid;
			SET nome = CONCAT("<b><i>",fam,"</i></b>");
		END IF;
	END IF;
END IF;
IF (sinon=TRUE) THEN
	RETURN sinos;
ELSE 
	RETURN nome;
END IF;
END
