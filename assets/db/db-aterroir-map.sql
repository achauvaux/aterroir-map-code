-- ----------------------------------------------------------------------------
-- MySQL Workbench Migration
-- Migrated Schemata: aterroir-map
-- Source Schemata: aterroir-map
-- Created: Mon Dec 13 00:08:02 2021
-- Workbench Version: 8.0.22
-- ----------------------------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------------------
-- Schema aterroir-map
-- ----------------------------------------------------------------------------
DROP SCHEMA IF EXISTS `aterroir-map` ;
CREATE SCHEMA IF NOT EXISTS `aterroir-map` ;

-- ----------------------------------------------------------------------------
-- Table aterroir-map.country
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `aterroir-map`.`country` (
  `id_country` INT(11) NOT NULL AUTO_INCREMENT,
  `code_country` VARCHAR(3) NOT NULL,
  `code_zone` VARCHAR(3) NULL DEFAULT NULL,
  `name_FR` VARCHAR(200) NULL DEFAULT NULL,
  `name_EN` VARCHAR(200) NULL DEFAULT NULL,
  `name_CN` VARCHAR(200) CHARACTER SET 'utf8' NULL DEFAULT NULL,
  `name_local` VARCHAR(200) NULL DEFAULT NULL,
  `name_capital` VARCHAR(200) NULL DEFAULT NULL,
  `img_icon` TEXT NULL DEFAULT NULL,
  `lat_icon` DOUBLE NULL DEFAULT NULL,
  `lon_icon` DOUBLE NULL DEFAULT NULL,
  `direction_heel` VARCHAR(45) NULL DEFAULT NULL,
  PRIMARY KEY (`id_country`),
  UNIQUE INDEX `code_country_UNIQUE` (`code_country` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 25
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin;

-- ----------------------------------------------------------------------------
-- Table aterroir-map.label
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `aterroir-map`.`label` (
  `id_label` INT(11) NOT NULL AUTO_INCREMENT,
  `id_region` INT(11) NULL DEFAULT NULL,
  `name_FR` VARCHAR(200) NULL DEFAULT NULL,
  `name_EN` VARCHAR(200) NULL DEFAULT NULL,
  `name_CN` VARCHAR(200) NULL DEFAULT NULL,
  `name_local` VARCHAR(200) NULL DEFAULT NULL,
  `code_label` VARCHAR(5) NULL DEFAULT NULL,
  `code_category` VARCHAR(5) NULL DEFAULT NULL,
  `name_town_label` VARCHAR(200) NULL DEFAULT NULL,
  `name_town_label_CN` VARCHAR(200) NULL DEFAULT NULL,
  `zip_town_label` VARCHAR(45) NULL DEFAULT NULL,
  `lat` DOUBLE NULL DEFAULT NULL,
  `lon` DOUBLE NULL DEFAULT NULL,
  `img_icon_filename` TEXT NULL DEFAULT NULL,
  `height_img_icon` INT(11) NULL DEFAULT NULL,
  `direction_heel` VARCHAR(45) NULL DEFAULT NULL,
  `level` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id_label`))
ENGINE = InnoDB
AUTO_INCREMENT = 492
DEFAULT CHARACTER SET = utf8;

-- ----------------------------------------------------------------------------
-- Table aterroir-map.labelmap
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `aterroir-map`.`labelmap` (
  `id_labelmap` INT(11) NOT NULL AUTO_INCREMENT,
  `id_label` INT(11) NOT NULL,
  `img_map_filename` TEXT NULL DEFAULT NULL,
  `lat_lefttop` DOUBLE NULL DEFAULT NULL,
  `lon_lefttop` DOUBLE NULL DEFAULT NULL,
  `lat_rightbottom` DOUBLE NULL DEFAULT NULL,
  `lon_rightbottom` DOUBLE NULL DEFAULT NULL,
  PRIMARY KEY (`id_labelmap`))
ENGINE = InnoDB
AUTO_INCREMENT = 4
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin;

-- ----------------------------------------------------------------------------
-- Table aterroir-map.phpgen_user_perms
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `aterroir-map`.`phpgen_user_perms` (
  `user_id` INT(11) NOT NULL,
  `page_name` VARCHAR(255) NOT NULL,
  `perm_name` VARCHAR(6) NOT NULL,
  PRIMARY KEY (`user_id`, `page_name`, `perm_name`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

-- ----------------------------------------------------------------------------
-- Table aterroir-map.pi
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `aterroir-map`.`pi` (
  `id_pi` INT(11) NOT NULL AUTO_INCREMENT,
  `id_label` INT(11) NOT NULL,
  `name_FR` VARCHAR(200) NULL DEFAULT NULL,
  `name_EN` VARCHAR(200) NULL DEFAULT NULL,
  `name_CN` VARCHAR(200) NULL DEFAULT NULL,
  `name_local` VARCHAR(200) NULL DEFAULT NULL,
  `id_picategory` INT(11) NULL DEFAULT NULL,
  `code_category` VARCHAR(20) NULL DEFAULT NULL,
  `name_town` VARCHAR(200) NULL DEFAULT NULL,
  `zip` VARCHAR(45) NULL DEFAULT NULL,
  `address` TEXT NULL DEFAULT NULL,
  `lat` DOUBLE NULL DEFAULT NULL,
  `lon` DOUBLE NULL DEFAULT NULL,
  `img_pi_filename` TEXT NULL DEFAULT NULL,
  `img_pi` TEXT NULL DEFAULT NULL,
  `link` TEXT NULL DEFAULT NULL,
  `level` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id_pi`))
ENGINE = InnoDB
AUTO_INCREMENT = 11
DEFAULT CHARACTER SET = utf8;

-- ----------------------------------------------------------------------------
-- Table aterroir-map.picategory
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `aterroir-map`.`picategory` (
  `id_picategory` INT(11) NOT NULL AUTO_INCREMENT,
  `code_picategory` VARCHAR(45) CHARACTER SET 'latin1' NULL DEFAULT NULL,
  `name_FR` VARCHAR(200) CHARACTER SET 'latin1' NULL DEFAULT NULL,
  `name_EN` VARCHAR(200) CHARACTER SET 'latin1' NULL DEFAULT NULL,
  `name_CN` VARCHAR(200) CHARACTER SET 'latin1' NULL DEFAULT NULL,
  `img_icon_category` TEXT CHARACTER SET 'latin1' NULL DEFAULT NULL,
  PRIMARY KEY (`id_picategory`))
ENGINE = InnoDB
AUTO_INCREMENT = 9
DEFAULT CHARACTER SET = utf8;

-- ----------------------------------------------------------------------------
-- Table aterroir-map.region
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `aterroir-map`.`region` (
  `id_region` INT(11) NOT NULL AUTO_INCREMENT,
  `id_country` INT(11) NULL DEFAULT NULL,
  `code_region` VARCHAR(5) NULL DEFAULT NULL,
  `name_FR` VARCHAR(200) NULL DEFAULT NULL,
  `name_EN` VARCHAR(200) NULL DEFAULT NULL,
  `name_CN` VARCHAR(200) NULL DEFAULT NULL,
  `name_local` VARCHAR(200) NULL DEFAULT NULL,
  `name_capital_region` VARCHAR(200) NULL DEFAULT NULL,
  `name_capital_region_CN` VARCHAR(200) NULL DEFAULT NULL,
  `img_logo` TEXT NULL DEFAULT NULL,
  `lat_capital` DOUBLE NULL DEFAULT NULL,
  `lon_capital` DOUBLE NULL DEFAULT NULL,
  `direction_heel` VARCHAR(45) NULL DEFAULT NULL,
  PRIMARY KEY (`id_region`))
ENGINE = InnoDB
AUTO_INCREMENT = 338
DEFAULT CHARACTER SET = utf8;

-- ----------------------------------------------------------------------------
-- Table aterroir-map.tableau-label-CN
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `aterroir-map`.`tableau-label-CN` (
  `N` INT(11) NULL DEFAULT NULL,
  `Nom_EU` TEXT NULL DEFAULT NULL,
  `Type` TEXT NULL DEFAULT NULL,
  `Nom_CN` TEXT NULL DEFAULT NULL,
  `Product` TEXT NULL DEFAULT NULL,
  `Nom_Local` TEXT NULL DEFAULT NULL,
  `Postal_Prov_CN` INT(11) NULL DEFAULT NULL,
  `Zone` TEXT NULL DEFAULT NULL,
  `Country_EU` TEXT NULL DEFAULT NULL,
  `Country_CN` TEXT NULL DEFAULT NULL,
  `Country_Local` TEXT NULL DEFAULT NULL,
  `Country_FR` TEXT NULL DEFAULT NULL,
  `Region_EU` TEXT NULL DEFAULT NULL,
  `Region_CN` TEXT NULL DEFAULT NULL,
  `Region_Local` TEXT NULL DEFAULT NULL,
  `Region_FR` TEXT NULL DEFAULT NULL,
  `Capital_Region_EU` TEXT NULL DEFAULT NULL,
  `Capital_Region_CN` TEXT NULL DEFAULT NULL,
  `IG_Town` TEXT NULL DEFAULT NULL,
  `IG_Town_CN` TEXT NULL DEFAULT NULL,
  `Postal_Code_IG` INT(11) NULL DEFAULT NULL)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

-- ----------------------------------------------------------------------------
-- Table aterroir-map.user
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `aterroir-map`.`user` (
  `id_user` INT(11) NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(100) NULL DEFAULT NULL,
  `last_name` VARCHAR(100) NULL DEFAULT NULL,
  `email` VARCHAR(100) NULL DEFAULT NULL,
  `pwd` VARCHAR(45) NULL DEFAULT NULL,
  `id_label` INT(11) NULL DEFAULT NULL,
  `type_user` VARCHAR(45) NULL DEFAULT NULL,
  PRIMARY KEY (`id_user`))
ENGINE = InnoDB
AUTO_INCREMENT = 3
DEFAULT CHARACTER SET = utf8;

-- ----------------------------------------------------------------------------
-- Routine aterroir-map.getDetailLabel
-- ----------------------------------------------------------------------------
DELIMITER $$

DELIMITER $$
USE `aterroir-map`$$
CREATE DEFINER=`root`@`%` PROCEDURE `getDetailLabel`(in pid_label int)
BEGIN

	select
		l.* 
	from 
		label l 
	where 
		l.id_label=pid_label;
        
END$$

DELIMITER ;

-- ----------------------------------------------------------------------------
-- Routine aterroir-map.getDetailRegion
-- ----------------------------------------------------------------------------
DELIMITER $$

DELIMITER $$
USE `aterroir-map`$$
CREATE DEFINER=`root`@`%` PROCEDURE `getDetailRegion`(in pid_region int)
BEGIN

	select
		r.* 
	from 
		region r 
	where 
		r.id_region=pid_region;
        
END$$

DELIMITER ;

-- ----------------------------------------------------------------------------
-- Routine aterroir-map.getDetailRegion2
-- ----------------------------------------------------------------------------
DELIMITER $$

DELIMITER $$
USE `aterroir-map`$$
CREATE DEFINER=`root`@`%` PROCEDURE `getDetailRegion2`(in pcode_region varchar(5))
BEGIN

select
	r.* 
from 
	region r 
where 
	r.code_region=pcode_region;
        
END$$

DELIMITER ;

-- ----------------------------------------------------------------------------
-- Routine aterroir-map.getListCountries
-- ----------------------------------------------------------------------------
DELIMITER $$

DELIMITER $$
USE `aterroir-map`$$
CREATE DEFINER=`root`@`%` PROCEDURE `getListCountries`(in pcode_zone varchar(3))
BEGIN

select
	c.*
from
	country c
where
	c.code_zone=pcode_zone or pcode_zone is null;

END$$

DELIMITER ;

-- ----------------------------------------------------------------------------
-- Routine aterroir-map.getListLabelMaps
-- ----------------------------------------------------------------------------
DELIMITER $$

DELIMITER $$
USE `aterroir-map`$$
CREATE DEFINER=`root`@`%` PROCEDURE `getListLabelMaps`(
	in pid_country int,
    in pid_region int,
    in pid_label int
)
BEGIN

select
	lm.*,
	r.code_region,
    c.code_country
from
	labelmap lm join
    label l on l.id_label=lm.id_label join
	region r on r.id_region=l.id_region join
    country c on c.id_country=r.id_country
where
	(c.id_country=pid_country or pid_country is null) and
    (r.id_region=pid_region or pid_region is null) and
    (l.id_label=pid_label or pid_label is null)
order by
	c.id_country,
    r.id_region,
    l.id_label;

END$$

DELIMITER ;

-- ----------------------------------------------------------------------------
-- Routine aterroir-map.getListLabels
-- ----------------------------------------------------------------------------
DELIMITER $$

DELIMITER $$
USE `aterroir-map`$$
CREATE DEFINER=`root`@`%` PROCEDURE `getListLabels`(
	in pcode_zone varchar(3),
	in pid_country int,
    in pid_region int
)
BEGIN

select
	l.*,
	r.code_region,
    c.code_country
from
	label l join
	region r on r.id_region=l.id_region join
    country c on c.id_country=r.id_country
where
	(c.code_zone=pcode_zone or pcode_zone is null) and
	(c.id_country=pid_country or pid_country is null) and
    (r.id_region=pid_region or pid_region is null)
order by
	-- c.id_country,
    -- r.id_region,
    l.id_label;

END$$

DELIMITER ;

-- ----------------------------------------------------------------------------
-- Routine aterroir-map.getListPICategories
-- ----------------------------------------------------------------------------
DELIMITER $$

DELIMITER $$
USE `aterroir-map`$$
CREATE DEFINER=`root`@`%` PROCEDURE `getListPICategories`()
BEGIN

select
	pic.*
from
	picategory pic
order by
	pic.id_picategory;

END$$

DELIMITER ;

-- ----------------------------------------------------------------------------
-- Routine aterroir-map.getListPIs
-- ----------------------------------------------------------------------------
DELIMITER $$

DELIMITER $$
USE `aterroir-map`$$
CREATE DEFINER=`root`@`%` PROCEDURE `getListPIs`(
	in pid_country int,
    in pid_region int,
    in pid_label int,
    in pid_picategory int
)
BEGIN

select
	p.*,
	r.code_region,
    c.code_country
from
	pi p join
    label l on l.id_label=p.id_label join
	region r on r.id_region=l.id_region join
    country c on c.id_country=r.id_country
where
	(c.id_country=pid_country or pid_country is null) and
    (r.id_region=pid_region or pid_region is null) and
    (l.id_label=pid_label or pid_label is null) and
    (p.id_picategory=pid_picategory or pid_picategory is null)
order by
	c.id_country,
    r.id_region,
    l.id_label,
    p.id_picategory;

END$$

DELIMITER ;

-- ----------------------------------------------------------------------------
-- Routine aterroir-map.getListRegions
-- ----------------------------------------------------------------------------
DELIMITER $$

DELIMITER $$
USE `aterroir-map`$$
CREATE DEFINER=`root`@`%` PROCEDURE `getListRegions`(in pcode_zone varchar(3), in pid_country int)
BEGIN

select
	r.*,
    c.code_country
from
	region r join
    country c on c.id_country=r.id_country
where
	(c.code_zone=pcode_zone or pcode_zone is null) and
	(c.id_country=pid_country or pid_country is null)
order by
	c.id_country;

END$$

DELIMITER ;

-- ----------------------------------------------------------------------------
-- Routine aterroir-map.getIdCountryRegion
-- ----------------------------------------------------------------------------
DELIMITER $$

DELIMITER $$
USE `aterroir-map`$$
CREATE DEFINER=`root`@`%` FUNCTION `getIdCountryRegion`(pid_region int) RETURNS int(11)
BEGIN

declare lid_country integer;

select
	r.id_country
into
	lid_country
from
	region r
where
	r.id_region=pid_region;

return lid_country;

END$$

DELIMITER ;

-- ----------------------------------------------------------------------------
-- Routine aterroir-map.getIdCountryRegion2
-- ----------------------------------------------------------------------------
DELIMITER $$

DELIMITER $$
USE `aterroir-map`$$
CREATE DEFINER=`root`@`%` FUNCTION `getIdCountryRegion2`(pcode_region int) RETURNS int(11)
BEGIN

declare lid_country integer;

select
	r.id_country
into
	lid_country
from
	region r
where
	r.code_region=pcode_region;

return lid_country;

END$$

DELIMITER ;
SET FOREIGN_KEY_CHECKS = 1;
