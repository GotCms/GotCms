-- ----------------------------
-- Data for GotCms
--
-- icon (name, url) VALUES
-- ----------------------------

INSERT INTO icon (name, url) VALUES ('Home', '/media/icons/home.png');
INSERT INTO icon (name, url) VALUES ('Camera', '/media/icons/camera.png');
INSERT INTO icon (name, url) VALUES ('Box', '/media/icons/box.png');
INSERT INTO icon (name, url) VALUES ('Calendar', '/media/icons/calendar.png');
INSERT INTO icon (name, url) VALUES ('Configuration', '/media/icons/configuration.png');
INSERT INTO icon (name, url) VALUES ('File', '/media/icons/file.gif');
INSERT INTO icon (name, url) VALUES ('Film', '/media/icons/film.png');
INSERT INTO icon (name, url) VALUES ('Folder', '/media/icons/folder.gif');
INSERT INTO icon (name, url) VALUES ('Folder closed', '/media/icons/folder-closed.gif');
INSERT INTO icon (name, url) VALUES ('Image', '/media/icons/image.png');
INSERT INTO icon (name, url) VALUES ('Letter blue', '/media/icons/letter-blue.png');
INSERT INTO icon (name, url) VALUES ('Letter red', '/media/icons/letter-red.png');
INSERT INTO icon (name, url) VALUES ('Pen green', '/media/icons/pen-green.png');
INSERT INTO icon (name, url) VALUES ('Pen yellow', '/media/icons/pen-yellow.png');
INSERT INTO icon (name, url) VALUES ('Printer', '/media/icons/printer.png');
INSERT INTO icon (name, url) VALUES ('Rss', '/media/icons/rss.png');
INSERT INTO icon (name, url) VALUES ('Save', '/media/icons/save-black.png');
INSERT INTO icon (name, url) VALUES ('Save blue', '/media/icons/save-blue.png');
INSERT INTO icon (name, url) VALUES ('Shell', '/media/icons/shell.png');
INSERT INTO icon (name, url) VALUES ('Tool', '/media/icons/tool.png');
INSERT INTO icon (name, url) VALUES ('Trash', '/media/icons/trash.png');
INSERT INTO icon (name, url) VALUES ('Trash empty', '/media/icons/trash-empty.png');
INSERT INTO icon (name, url) VALUES ('TV', '/media/icons/tv.png');
INSERT INTO icon (name, url) VALUES ('Write', '/media/icons/write.png');


-- Core_config_data
INSERT INTO core_config_data (identifier, value) VALUES ('dashboard_widgets', '');
INSERT INTO core_config_data (identifier, value) VALUES ('debug_is_active', '0');
INSERT INTO core_config_data (identifier, value) VALUES ('cache_is_active', '0');
INSERT INTO core_config_data (identifier, value) VALUES ('cache_handler', 'filesystem');
INSERT INTO core_config_data (identifier, value) VALUES ('cache_lifetime', '600');
INSERT INTO core_config_data (identifier, value) VALUES ('session_path', '');
INSERT INTO core_config_data (identifier, value) VALUES ('session_handler', '0');
INSERT INTO core_config_data (identifier, value) VALUES ('site_offline_document', '');
INSERT INTO core_config_data (identifier, value) VALUES ('site_404_layout', '');
INSERT INTO core_config_data (identifier, value) VALUES ('site_exception_layout', '');
INSERT INTO core_config_data (identifier, value) VALUES ('cookie_path', '/');
INSERT INTO core_config_data (identifier, value) VALUES ('unsecure_frontend_base_path', '');
INSERT INTO core_config_data (identifier, value) VALUES ('secure_frontend_base_path', '');
INSERT INTO core_config_data (identifier, value) VALUES ('unsecure_backend_base_path', '');
INSERT INTO core_config_data (identifier, value) VALUES ('secure_backend_base_path', '');
INSERT INTO core_config_data (identifier, value) VALUES ('unsecure_cdn_base_path', '');
INSERT INTO core_config_data (identifier, value) VALUES ('secure_cdn_base_path', '');
INSERT INTO core_config_data (identifier, value) VALUES ('force_backend_ssl', '');
INSERT INTO core_config_data (identifier, value) VALUES ('force_frontend_ssl', '');
INSERT INTO core_config_data (identifier, value) VALUES ('stream_wrapper_is_active', '1');
