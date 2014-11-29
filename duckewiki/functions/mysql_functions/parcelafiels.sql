CREATE FUNCTION parcelafiels(gazid INT(10), gpsptid INT(10), qual VARCHAR(50)) RETURNS VARCHAR(100) CHARSET utf8
BEGIN
DECLARE gaztipo VARCHAR(100) DEFAULT '';
DECLARE gaztxt VARCHAR(100) DEFAULT '';
DECLARE gazpath VARCHAR(100) DEFAULT '';
DECLARE gazparid INT(10) DEFAULT 0;
DECLARE gazstartx DOUBLE DEFAULT 0;
DECLARE gazstarty DOUBLE DEFAULT 0;
DECLARE gazdimx DOUBLE DEFAULT 0;
DECLARE gazdimy DOUBLE DEFAULT 0;
DECLARE gazdimdiamenter DOUBLE DEFAULT 0;
DECLARE partipo VARCHAR(100) DEFAULT '';
DECLARE partxt VARCHAR(100) DEFAULT '';
DECLARE pardimx DOUBLE DEFAULT 0;
DECLARE pardimy DOUBLE DEFAULT 0;
IF (gpsptid>0) THEN
	/*GazetteerTIPOtxt, INTO gaztipo*/ 

SELECT Gazetteer,Gazetteer.PathName, Gazetteer.ParentID,Gazetteer.StartY,Gazetteer.StartX,Gazetteer.DimX,Gazetteer.DimY,Gazetteer.DimDiameter
INTO gaztxt,gazpath,gazparid,gazstartx,gazstarty,gazdimx,gazdimy,gazdimdiamenter FROM GPS_DATA JOIN Gazetteer USING(GazetteerID) WHERE PointID=gpsptid;
ELSE 
SELECT Gazetteer,Gazetteer.PathName, Gazetteer.ParentID,Gazetteer.StartY,Gazetteer.StartX,Gazetteer.DimX,Gazetteer.DimY,Gazetteer.DimDiameter
INTO gaztxt,gazpath,gazparid,gazstartx,gazstarty,gazdimx,gazdimy,gazdimdiamenter FROM Gazetteer WHERE GazetteerID=gazid;
END IF;
IF gazparid>0 THEN
SELECT Gazetteer,Gazetteer.DimX,Gazetteer.DimY
INTO partxt,pardimx,pardimy FROM Gazetteer WHERE GazetteerID=gazparid;
END IF; 
IF qual='DIMX' THEN
	RETURN gazdimx;
END IF;
IF qual='DIMY' THEN
	RETURN gazdimy;
END IF;
IF qual='STARTX' THEN
	RETURN gazstartx;
END IF;
IF qual='STARTY' THEN
	RETURN gazstarty;
END IF;
IF qual='PARDIMY' THEN
	RETURN pardimy;
END IF;
IF qual='PARDIMX' THEN
	RETURN pardimx;
END IF;
IF qual='PARGAZ_SPEC' THEN
	/*	RETURN CONCAT(partipo,' ',partxt);*/ 
	RETURN partxt;
END IF;
END
