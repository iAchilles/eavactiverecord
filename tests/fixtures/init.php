<?php
$db = $this->getDbConnection();

if ($db->getSchema() instanceof CMysqlSchema)
{
    if ($db->schema->getTable('eav_test_entity', true) === null)
    {
        $sql = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'mysql.sql');
        $db->createCommand($sql)->execute();
        $sql = "CREATE TABLE `eav_test_entity` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(255) DEFAULT NULL,
                `eav_set_id` int(10) unsigned DEFAULT NULL COMMENT 'Foreign key references eav_set(id)',
                PRIMARY KEY (`id`),
                KEY `no_eav_test_entity_eav_set_id` (`eav_set_id`),
                CONSTRAINT `fk_eav_set_id_eav_test_entity` FOREIGN KEY (`eav_set_id`) REFERENCES `eav_set` (`id`))
                ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8";
        $db->createCommand($sql)->execute();
    }
}
else if ($db->getSchema() instanceof CPgsqlSchema)
{
    if ($db->schema->getTable('eav_test_entity', true) === null)
    {
        $sql = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'postgresql.sql');
        $db->createCommand($sql)->execute();
        $sql = "CREATE SEQUENCE eav_test_entity_seq;
                CREATE TABLE eav_test_entity (
                id integer NOT NULL DEFAULT NEXTVAL('eav_test_entity_seq') PRIMARY KEY,
                name varchar(255) DEFAULT NULL,
                eav_set_id integer DEFAULT NULL REFERENCES eav_set(id));
                CREATE INDEX no_eav_test_entity_eav_set_id ON eav_test_entity(eav_set_id);
                ALTER TABLE eav_test_entity ADD CONSTRAINT fk_eav_set_id_eav_test_entity FOREIGN KEY (eav_set_id) REFERENCES eav_set(id);
                ALTER SEQUENCE eav_test_entity_seq OWNED BY eav_test_entity.id";
        $db->createCommand($sql)->execute();
    }
}