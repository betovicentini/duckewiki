CREATE FUNCTION checkgazetteer_import(gaztxt varchar(500),parid int(10), muniid int(10), provid int(10), countrid int(10)) RETURNS INT
BEGIN
DECLARE gazid INT(10) DEFAULT 0;
DECLARE nmacthes INT(10) DEFAULT 0;
IF (parid>0) THEN
/* CONCAT(GazetteerTIPOtxt,' ',Gazetteer) */
	SELECT COUNT(GazetteerID),GazetteerID INTO nmacthes,gazid  FROM Gazetteer WHERE ParentID=parid AND LOWER(Gazetteer) LIKE CONCAT('%',LOWER(gaztxt),'%');
ELSE
	IF (muniid>0) THEN 
		SELECT COUNT(GazetteerID),GazetteerID INTO nmacthes,gazid  FROM Gazetteer WHERE LOWER(Gazetteer) LIKE CONCAT('%',LOWER(gaztxt),'%') AND MunicipioID=muniid;
	ELSE 
		IF (provid>0) THEN
			SELECT COUNT(GazetteerID),GazetteerID INTO nmacthes,gazid  FROM Gazetteer JOIN Municipio USING(MunicipioID) WHERE LOWER(Gazetteer) LIKE CONCAT('%',LOWER(gaztxt),'%') AND ProvinceID=provid;
		ELSE
			IF (countrid>0) THEN
				SELECT COUNT(GazetteerID),GazetteerID INTO nmacthes,gazid  FROM Gazetteer JOIN Municipio USING(MunicipioID) JOIN Province USING(ProvinceID) WHERE LOWER(Gazetteer) LIKE CONCAT('%',LOWER(gaztxt),'%') AND CountryID=countrid;
			ELSE
				SELECT COUNT(GazetteerID),GazetteerID INTO nmacthes,gazid FROM Gazetteer WHERE LOWER(Gazetteer) LIKE CONCAT('%',LOWER(gaztxt),'%');
			END IF;
		END IF;
	END IF;
END IF;
IF (nmacthes>1) THEN
RETURN nmacthes;
ELSE
RETURN gazid;
END IF;
END
