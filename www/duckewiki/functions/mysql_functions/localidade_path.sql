CREATE FUNCTION localidade_path(gazid INT(10),gpsptid INT(10),munid INT(10),provid INT(10),crtid INT(10)) RETURNS text CHARSET utf8
BEGIN
DECLARE resultado TEXT DEFAULT '';
DECLARE coordenadas TEXT DEFAULT '';
DECLARE countr TEXT DEFAULT '';
DECLARE munici TEXT DEFAULT '';
DECLARE provincia TEXT DEFAULT '';
DECLARE gazz TEXT DEFAULT '';
DECLARE gazparid INT(10) DEFAULT 0;
DECLARE gazrunid INT(10) DEFAULT 0;
IF (gpsptid>0) THEN
SELECT Country,Province,Municipio, Gazetteer.PathName INTO countr,provincia,munici,gazz FROM GPS_DATA as gps JOIN Gazetteer USING(GazetteerID) JOIN Municipio USING(MunicipioID) JOIN Province USING(ProvinceID) JOIN Country USING(CountryID) WHERE PointID=gpsptid;
ELSE
IF (gazid>0) THEN
SELECT Country,Province,Municipio,Gazetteer.PathName INTO countr,provincia,munici,gazz FROM Gazetteer JOIN Municipio USING(MunicipioID) JOIN Province USING(ProvinceID) JOIN Country USING(CountryID) WHERE GazetteerID=gazid;
ELSE
IF (munid>0) THEN
SELECT Country,Province,Municipio INTO countr,provincia,munici FROM Municipio JOIN Province USING(ProvinceID) JOIN Country USING(CountryID) WHERE MunicipioID=munid;
ELSE
IF (provid>0) THEN
SELECT Country,Province INTO countr,provincia FROM Province JOIN Country USING(CountryID) WHERE ProvinceID=provid;
ELSE
IF (crtid>0) THEN
SELECT Country INTO countr FROM Country WHERE CountryID=crtid;
END IF;
END IF;
END IF;
END IF;
END IF;
SET resultado = CONCAT(countr,'__',provincia,'__',munici,'__',gazz);
RETURN resultado;
END
