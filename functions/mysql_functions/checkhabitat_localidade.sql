CREATE FUNCTION checkhabitat_localidade(theid INT(10), theref CHAR(100)) RETURNS INT
BEGIN
DECLARE nhab INT DEFAULT 0;
IF (theref='Country') THEN
SELECT COUNT(*) INTO nhab FROM (SELECT DISTINCT newtbb.HabitatID FROM ((SELECT pllltb.HabitatID FROM Plantas AS pllltb JOIN Habitat as habbb ON habbb.HabitatID=pllltb.HabitatID JOIN Gazetteer AS gazzz ON pllltb.GazetteerID=gazzz.GazetteerID JOIN Municipio as munii ON gazzz.MunicipioID=munii.MunicipioID JOIN Province as provv ON munii.ProvinceID=provv.ProvinceID WHERE provv.CountryID=theid AND habbb.HabitatTipo='Local') UNION 
(SELECT spectb.HabitatID FROM Especimenes AS spectb JOIN Habitat as spechab ON spechab.HabitatID=spectb.HabitatID JOIN Gazetteer AS specgaz ON spectb.GazetteerID=specgaz.GazetteerID JOIN Municipio as specmuni ON specgaz.MunicipioID=specmuni.MunicipioID JOIN Province as specprov ON specmuni.ProvinceID=specprov.ProvinceID WHERE specprov.CountryID=theid AND spechab.HabitatTipo='Local')) AS newtbb) AS lastb;
END IF;
IF (theref='Province') THEN
SELECT COUNT(*) INTO nhab FROM (SELECT DISTINCT newtbb.HabitatID FROM ((SELECT pllltb.HabitatID FROM Plantas AS pllltb JOIN Habitat as habbb ON habbb.HabitatID=pllltb.HabitatID JOIN Gazetteer AS gazzz ON pllltb.GazetteerID=gazzz.GazetteerID JOIN Municipio as munii ON gazzz.MunicipioID=munii.MunicipioID WHERE munii.ProvinceID=theid AND habbb.HabitatTipo='Local') UNION 
(SELECT spectb.HabitatID FROM Especimenes AS spectb JOIN Habitat as spechab ON spechab.HabitatID=spectb.HabitatID JOIN Gazetteer AS specgaz ON spectb.GazetteerID=specgaz.GazetteerID JOIN Municipio as specmuni ON specgaz.MunicipioID=specmuni.MunicipioID WHERE specmuni.ProvinceID=theid AND spechab.HabitatTipo='Local')) AS newtbb) AS lastb;
END IF;
IF (theref='Municipio') THEN
SELECT COUNT(*) INTO nhab FROM (SELECT DISTINCT newtbb.HabitatID FROM ((SELECT pllltb.HabitatID FROM Plantas AS pllltb JOIN Habitat as habbb ON habbb.HabitatID=pllltb.HabitatID JOIN Gazetteer AS gazzz ON pllltb.GazetteerID=gazzz.GazetteerID WHERE gazzz.MunicipioID=theid AND habbb.HabitatTipo='Local') UNION 
(SELECT spectb.HabitatID FROM Especimenes AS spectb JOIN Habitat as spechab ON spechab.HabitatID=spectb.HabitatID JOIN Gazetteer AS specgaz ON spectb.GazetteerID=specgaz.GazetteerID WHERE specgaz.MunicipioID=theid AND spechab.HabitatTipo='Local')) AS newtbb) AS lastb;
END IF;
IF (theref='Gazetteer') THEN
SELECT COUNT(*) INTO nhab FROM (SELECT DISTINCT newtbb.HabitatID FROM ((SELECT pllltb.HabitatID FROM Plantas AS pllltb JOIN Habitat as habbb ON habbb.HabitatID=pllltb.HabitatID WHERE (pllltb.GazetteerID=theid OR checkissub(pllltb.GazetteerID,theid)=1)  AND habbb.HabitatTipo='Local') UNION 
(SELECT spectb.HabitatID FROM Especimenes AS spectb JOIN Habitat as spechab ON spechab.HabitatID=spectb.HabitatID WHERE (spectb.GazetteerID=theid OR checkissub(spectb.GazetteerID,theid)=1) AND spechab.HabitatTipo='Local')) AS newtbb) AS lastb;
END IF;
RETURN nhab;
END
