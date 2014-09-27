CREATE TABLE `eav_set` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary surrogate key',
  `name` varchar(255) NOT NULL COMMENT 'Set name',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `eav_attribute` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary surrogate key',
  `type` tinyint(1) unsigned NOT NULL COMMENT '0 if the attribute can have only one value or 1 if the attribute can have multiple values',
  `data_type` varchar(255) NOT NULL COMMENT 'The attribute data type',
  `name` varchar(255) NOT NULL COMMENT 'The attribute name',
  `label` varchar(255) DEFAULT NULL COMMENT 'The attribute label',
  `data` text COMMENT 'The serialized data',
  PRIMARY KEY (`id`),
  UNIQUE KEY `un_eav_attribute_name` (`name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `eav_attribute_set` (
  `eav_attribute_id` int(10) unsigned NOT NULL COMMENT 'Composite primary key',
  `eav_set_id` int(10) unsigned NOT NULL COMMENT 'Composite primary key',
  `weight` int(10) NOT NULL COMMENT 'The weight of the attribute',
  PRIMARY KEY (`eav_attribute_id`,`eav_set_id`),
  KEY `no_eav_attribute_set_attribute_id` (`eav_attribute_id`) USING BTREE,
  KEY `no_eav_attribute_set_set_id` (`eav_set_id`) USING BTREE,
  KEY `no_eav_attribute_set_weight` (`weight`) USING BTREE,
  CONSTRAINT `fk_eav_attribute_id_eav_attribute_set` FOREIGN KEY (`eav_attribute_id`) REFERENCES `eav_attribute` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_eav_set_id_eav_attribute_set` FOREIGN KEY (`eav_set_id`) REFERENCES `eav_set` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `eav_attribute_date` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary surrogate key',
  `eav_attribute_id` int(10) unsigned NOT NULL COMMENT 'Foreign key references eav_attribute(id)',
  `entity_id` int(11) NOT NULL COMMENT 'Primary key of an entity',
  `entity` varchar(255) NOT NULL COMMENT 'The entity name',
  `value` datetime NOT NULL COMMENT 'The value of the attribute',
  PRIMARY KEY (`id`),
  KEY `no_eav_attribute_date_entity_entity_id` (`entity`,`entity_id`) USING BTREE,
  KEY `no_eav_attribute_date_eav_attribute_id` (`eav_attribute_id`) USING BTREE,
  KEY `no_eav_attribute_date_value` (`value`) USING BTREE,
  CONSTRAINT `fk_eav_attribute_id_eav_attribute_date` FOREIGN KEY (`eav_attribute_id`) REFERENCES `eav_attribute` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `eav_attribute_varchar` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary surrogate key',
  `eav_attribute_id` int(10) unsigned NOT NULL COMMENT 'Foreign key references eav_attribute(id)',
  `entity_id` int(11) NOT NULL COMMENT 'Primary key of an entity',
  `entity` varchar(255) NOT NULL COMMENT 'The entity name',
  `value` varchar(255) NOT NULL COMMENT 'The value of the attribute',
  PRIMARY KEY (`id`),
  KEY `no_eav_attribute_varchar_entity_entity_id` (`entity`,`entity_id`) USING BTREE,
  KEY `no_eav_attribute_varchar_eav_attribute_id` (`eav_attribute_id`) USING BTREE,
  CONSTRAINT `fk_eav_attribute_id_eav_attribute_varchar` FOREIGN KEY (`eav_attribute_id`) REFERENCES `eav_attribute` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `eav_attribute_int` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary surrogate key',
  `eav_attribute_id` int(10) unsigned NOT NULL COMMENT 'Foreign key references eav_attribute(id)',
  `entity_id` int(11) NOT NULL COMMENT 'Primary key of an entity',
  `entity` varchar(255) NOT NULL COMMENT 'The entity name',
  `value` int(11) NOT NULL COMMENT 'The value of the attribute',
  PRIMARY KEY (`id`),
  KEY `no_eav_attribute_int_entity_entity_id` (`entity`,`entity_id`) USING BTREE,
  KEY `no_eav_attribute_int_eav_attribute_id` (`eav_attribute_id`) USING BTREE,
  KEY `no_eav_attribute_int_value` (`value`) USING BTREE,
  CONSTRAINT `fk_eav_attribute_id_eav_attribute_int` FOREIGN KEY (`eav_attribute_id`) REFERENCES `eav_attribute` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `eav_attribute_text` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary surrogate key',
  `eav_attribute_id` int(10) unsigned NOT NULL COMMENT 'Foreign key references eav_attribute(id)',
  `entity_id` int(11) NOT NULL COMMENT 'Primary key of an entity',
  `entity` varchar(255) NOT NULL COMMENT 'The entity name',
  `value` text COMMENT 'The value of the attribute',
  PRIMARY KEY (`id`),
  KEY `no_eav_attribute_int_entity_entity_id` (`entity`,`entity_id`) USING BTREE,
  KEY `no_eav_attribute_int_eav_attribute_id` (`eav_attribute_id`) USING BTREE,
  CONSTRAINT `fk_eav_attribute_id_eav_attribute_text` FOREIGN KEY (`eav_attribute_id`) REFERENCES `eav_attribute` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;