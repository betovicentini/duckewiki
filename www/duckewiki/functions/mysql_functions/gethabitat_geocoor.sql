CREATE FUNCTION gethabitat_geocoor(habid INT(10), qual VARCHAR(100)) RETURNS text CHARSET utf8
BEGIN
DECLARE resultado TEXT DEFAULT '';
DECLARE gazlat DOUBLE DEFAULT 0;
DECLARE gazlong DOUBLE DEFAULT 0;
DECLARE coords DOUBLE DEFAULT 0;
DECLARE coords2 DOUBLE DEFAULT 0;

SELECT IF (pltb.LocalityID>0, getlocalityFields(pltb.LocalityID, 'LONGITUDE'),IF(pltb.GPSPointID>0, getGPSlocalityFields(pltb.GPSPointID, 'LONGITUDE'), NULL)),IF (pltb.LocalityID>0, getlocalityFields(pltb.LocalityID, 'LATITUDE'),IF(pltb.GPSPointID>0, getGPSlocalityFields(pltb.GPSPointID, 'LATITUDE'), NULL)) INTO gazlong, gazlat FROM Habitat as pltb WHERE pltb.HabitatID=habid;
SET coords = ABS(gazlat)+ABS(gazlong);
IF coords=0 THEN
	SELECT IF (specpltb.GazetteerID>0,getlocalityFields(specpltb.GazetteerID, 'LONGITUDE'),IF(specpltb.GPSPointID>0, getGPSlocalityFields(specpltb.GPSPointID, 'LONGITUDE'), NULL)),IF (specpltb.GazetteerID>0, getlocalityFields(specpltb.GazetteerID, 'LATITUDE'),IF(specpltb.GPSPointID>0, getGPSlocalityFields(specpltb.GPSPointID, 'LATITUDE'), NULL)) INTO gazlong, gazlat FROM Especimenes as specpltb WHERE specpltb.HabitatID=habid AND 
	ABS(IF (specpltb.GazetteerID>0, getlocalityFields(specpltb.GazetteerID, 'LONGITUDE'),IF(specpltb.GPSPointID>0, getGPSlocalityFields(specpltb.GPSPointID, 'LONGITUDE'),0)))>0 LIMIT 0,1;
	SET coords2 = ABS(gazlat)+ABS(gazlong);
	IF coords2=0 THEN
	SELECT IF (plpltb.GazetteerID>0, getlocalityFields(plpltb.GazetteerID, 'LONGITUDE'),IF(plpltb.GPSPointID>0, getGPSlocalityFields(plpltb.GPSPointID, 'LONGITUDE'), NULL)),IF (plpltb.GazetteerID>0, getlocalityFields(plpltb.GazetteerID, 'LATITUDE'),IF(plpltb.GPSPointID>0, getGPSlocalityFields(plpltb.GPSPointID, 'LATITUDE'), NULL)) INTO gazlong, gazlat FROM Plantas as plpltb WHERE plpltb.HabitatID=habid AND ABS(IF (specpltb.GazetteerID>0, getlocalityFields(specpltb.GazetteerID, 'LONGITUDE'),IF(specpltb.GPSPointID>0, getGPSlocalityFields(specpltb.GPSPointID, 'LONGITUDE'),0)))>0 LIMIT 0,1;
	END IF;
END IF;
SET coords = abs(gazlat)+abs(gazlong);
IF coords>0 THEN
	IF qual='LATITUDE' THEN
		SET resultado=gazlat;
	END IF;
	IF qual='LONGITUDE' THEN
		SET resultado=gazlong;
	END IF;
END IF;
RETURN resultado;
END
