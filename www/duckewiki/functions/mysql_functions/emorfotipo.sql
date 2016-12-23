CREATE FUNCTION emorfotipo(identid INT(10), spid INT(10), infspid INT(10) ) RETURNS CHAR(100) CHARSET utf8
BEGIN
DECLARE sppM INT(1) DEFAULT 0;
DECLARE infM INT(1) DEFAULT 0;
DECLARE resultado CHAR(100) DEFAULT '';
IF identid>0 THEN
	SELECT spp.Morfotipo, infspp.Morfotipo INTO sppM, infM FROM Identidade as idd LEFT JOIN Tax_Especies as spp ON spp. EspecieID=idd.EspecieID LEFT JOIN Tax_InfraEspecies as infspp ON infspp.InfraEspecieID=idd.InfraEspecieID WHERE idd.DetID=identid;
ELSE 
	IF infspid>0 THEN
		SELECT  infspp.Morfotipo INTO infM FROM Tax_InfraEspecies as infspp WHERE infspp.InfraEspecieID=infspid;
	END IF;
	IF spid>0 THEN
		SELECT spp.Morfotipo INTO sppM FROM Tax_Especies as spp WHERE spp.EspecieID=spid;
	END IF;
END IF;
IF (sppM>0 AND infM>0) THEN
		SET resultado =  "spp_infspp";
ELSE
	IF (sppM>0) THEN
		SET resultado = "spp";
	ELSE 
		IF (infM>0) THEN
			SET resultado = "infspp";
		END IF;
	END IF;
END IF;
RETURN resultado;
END
