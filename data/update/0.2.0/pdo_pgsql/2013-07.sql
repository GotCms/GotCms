-- Session save path
UPDATE "user_acl_resource" SET resource = 'settings' WHERE resource = 'Config';
UPDATE "user_acl_resource" SET resource = 'content' WHERE resource = 'Content';
UPDATE "user_acl_resource" SET resource = 'development' WHERE resource = 'Development';
UPDATE "user_acl_resource" SET resource = 'modules' WHERE resource = 'Modules';
UPDATE "user_acl_resource" SET resource = 'stats' WHERE resource = 'Stats';

TRUNCATE "user_acl_permission" CASCADE;

-- Settings
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('user/list', 1);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('user/create', 1);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('user/edit', 1);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('user/delete', 1);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('config/system', 1);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('config/general', 1);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('config/server', 1);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('config/update', 1);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('role/list', 1);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('role/create', 1);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('role/edit', 1);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('role/delete', 1);
-- Content
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('media', 2);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('document', 2);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('translation', 2);
-- Development
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('view/list', 3);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('view/create', 3);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('view/edit', 3);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('view/delete', 3);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('layout/list', 3);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('layout/create', 3);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('layout/edit', 3);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('layout/delete', 3);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('datatype/list', 3);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('datatype/create', 3);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('datatype/edit', 3);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('datatype/delete', 3);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('document-type/list', 3);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('document-type/create', 3);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('document-type/edit', 3);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('document-type/delete', 3);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('script/list', 3);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('script/create', 3);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('script/edit', 3);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('script/delete', 3);
-- Modules
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('list', 4);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('install', 4);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('uninstall', 4);
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") SELECT name, 4 FROM module;
-- Stats
INSERT INTO user_acl_permission ("permission", "user_acl_resource_id") VALUES ('all', 5);
