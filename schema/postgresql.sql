CREATE SEQUENCE eav_set_id_sequence;
CREATE SEQUENCE eav_attribute_id_sequence;
CREATE SEQUENCE eav_attribute_date_id_sequence;
CREATE SEQUENCE eav_attribute_varchar_id_sequence;
CREATE SEQUENCE eav_attribute_int_id_sequence;
CREATE SEQUENCE eav_attribute_text_id_sequence;

CREATE TABLE eav_set (
  id integer NOT NULL DEFAULT NEXTVAL('eav_set_id_sequence') PRIMARY KEY,
  name character varying(255) NOT NULL
);

CREATE TABLE eav_attribute (
  id integer NOT NULL DEFAULT NEXTVAL('eav_attribute_id_sequence') PRIMARY KEY,
  type integer NOT NULL,
  data_type character varying(255),
  name character varying(255) UNIQUE NOT NULL,
  label character varying(255) DEFAULT NULL,
  data text
);

CREATE TABLE eav_attribute_set (
  eav_attribute_id integer NOT NULL REFERENCES eav_attribute(id) ON DELETE CASCADE ON UPDATE CASCADE,
  eav_set_id integer  NOT NULL REFERENCES eav_set(id) ON DELETE CASCADE ON UPDATE CASCADE,
  weight integer NOT NULL,
  PRIMARY KEY (eav_attribute_id, eav_set_id)
);

CREATE TABLE eav_attribute_date (
  id integer NOT NULL DEFAULT NEXTVAL('eav_attribute_date_id_sequence') PRIMARY KEY,
  eav_attribute_id integer NOT NULL REFERENCES eav_attribute(id) ON DELETE CASCADE ON UPDATE CASCADE,
  entity_id integer NOT NULL,
  entity character varying(255) NOT NULL,
  value timestamp without time zone NOT NULL
);

CREATE TABLE eav_attribute_int (
  id integer NOT NULL DEFAULT NEXTVAL('eav_attribute_int_id_sequence') PRIMARY KEY,
  eav_attribute_id integer NOT NULL REFERENCES eav_attribute(id) ON DELETE CASCADE ON UPDATE CASCADE,
  entity_id integer NOT NULL,
  entity character varying(255) NOT NULL,
  value integer NOT NULL
);

CREATE TABLE eav_attribute_varchar (
  id integer NOT NULL DEFAULT NEXTVAL('eav_attribute_varchar_id_sequence') PRIMARY KEY,
  eav_attribute_id integer NOT NULL REFERENCES eav_attribute(id) ON DELETE CASCADE ON UPDATE CASCADE,
  entity_id integer NOT NULL,
  entity character varying(255) NOT NULL,
  value character varying(255) NOT NULL
);

CREATE TABLE eav_attribute_text (
  id integer NOT NULL DEFAULT NEXTVAL('eav_attribute_text_id_sequence') PRIMARY KEY,
  eav_attribute_id integer NOT NULL REFERENCES eav_attribute(id) ON DELETE CASCADE ON UPDATE CASCADE,
  entity_id integer NOT NULL,
  entity character varying(255) NOT NULL,
  value text
);

CREATE INDEX no_eav_attribute_set_attribute_id ON eav_attribute_set(eav_attribute_id);
CREATE INDEX no_eav_attribute_set_set_id ON eav_attribute_set(eav_set_id);
CREATE INDEX no_eav_attribute_set_weight ON eav_attribute_set(weight);
CREATE INDEX no_eav_attribute_date_entity_entity_id ON eav_attribute_date(entity, entity_id);
CREATE INDEX no_eav_attribute_date_eav_attribute_id ON eav_attribute_date(eav_attribute_id);
CREATE INDEX no_eav_attribute_date_value ON eav_attribute_date(value);
CREATE INDEX no_eav_attribute_varchar_entity_entity_id ON eav_attribute_varchar(entity, entity_id);
CREATE INDEX no_eav_attribute_varchar_eav_attribute_id ON eav_attribute_varchar(eav_attribute_id);
CREATE INDEX no_eav_attribute_int_entity_entity_id ON eav_attribute_int(entity, entity_id);
CREATE INDEX no_eav_attribute_int_eav_attribute_id ON eav_attribute_int(eav_attribute_id);
CREATE INDEX no_eav_attribute_int_value ON eav_attribute_int(value);
CREATE INDEX no_eav_attribute_text_entity_entity_id ON eav_attribute_text(entity, entity_id);
CREATE INDEX no_eav_attribute_text_eav_attribute_id ON eav_attribute_text(eav_attribute_id);
