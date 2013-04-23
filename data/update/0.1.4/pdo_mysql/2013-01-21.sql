-- User retrieve password
ALTER TABLE `user`
ADD COLUMN retrieve_password_key VARCHAR(40) DEFAULT NULL,
ADD COLUMN retrieve_updated_at DATETIME DEFAULT NULL;

-- Change permissions for modules
DELETE FROM user_acl WHERE user_acl_permission_id IN (SELECT id FROM user_acl_permission WHERE user_acl_resource_id = 4);
DELETE FROM user_acl_permission WHERE user_acl_resource_id = 4;

INSERT INTO user_acl_permission (permission, user_acl_resource_id)
SELECT module.name, 4
FROM module;

INSERT INTO user_acl_permission (permission, user_acl_resource_id) VALUES ('list', 4);
INSERT INTO user_acl_permission (permission, user_acl_resource_id) VALUES ('install', 4);
INSERT INTO user_acl_permission (permission, user_acl_resource_id) VALUES ('uninstall', 4);

INSERT INTO user_acl (user_acl_permission_id, user_acl_role_id)
SELECT id, 1
FROM user_acl_permission
WHERE user_acl_resource_id = 4;
