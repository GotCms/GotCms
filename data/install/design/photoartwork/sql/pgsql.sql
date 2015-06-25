
SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: datatype_id_seq; Type: SEQUENCE SET; Schema: public
--

SELECT pg_catalog.setval('datatype_id_seq', 5, true);


--
-- Name: document_id_seq; Type: SEQUENCE SET; Schema: public
--

SELECT pg_catalog.setval('document_id_seq', 3, true);


--
-- Name: document_type_dependency_id_seq; Type: SEQUENCE SET; Schema: public
--

SELECT pg_catalog.setval('document_type_dependency_id_seq', 1, false);


--
-- Name: document_type_id_seq; Type: SEQUENCE SET; Schema: public
--

SELECT pg_catalog.setval('document_type_id_seq', 3, true);


--
-- Name: document_type_view_id_seq; Type: SEQUENCE SET; Schema: public
--

SELECT pg_catalog.setval('document_type_view_id_seq', 1, false);


--
-- Name: layout_id_seq; Type: SEQUENCE SET; Schema: public
--

SELECT pg_catalog.setval('layout_id_seq', 1, true);

--
-- Name: property_id_seq; Type: SEQUENCE SET; Schema: public
--

SELECT pg_catalog.setval('property_id_seq', 14, true);


--
-- Name: property_value_id_seq; Type: SEQUENCE SET; Schema: public
--

SELECT pg_catalog.setval('property_value_id_seq', 14, true);


--
-- Name: script_id_seq; Type: SEQUENCE SET; Schema: public
--

SELECT pg_catalog.setval('script_id_seq', 1, true);


--
-- Name: tab_id_seq; Type: SEQUENCE SET; Schema: public
--

SELECT pg_catalog.setval('tab_id_seq', 8, true);

--
-- Name: view_id_seq; Type: SEQUENCE SET; Schema: public
--

SELECT pg_catalog.setval('view_id_seq', 6, true);

--
-- Data for Name: view; Type: TABLE DATA; Schema: public;
--
INSERT INTO view VALUES (1, '2012-09-19 19:29:04', '2012-10-08 22:42:57', 'Home page', 'home', 'Home page content');
INSERT INTO view VALUES (2, '2012-09-19 19:29:50', '2012-09-19 19:29:50', 'Blog', 'blog', 'Blog');
INSERT INTO view VALUES (3, '2012-09-19 19:32:56', '2012-09-20 22:27:02', 'Contact', 'contact', 'Contact form');
INSERT INTO view VALUES (4, '2012-09-19 19:33:51', '2012-10-08 22:32:28', 'About', 'about',  'About page');
INSERT INTO view VALUES (5, '2012-09-20 22:12:33', '2012-09-20 22:21:24', 'Navigation', 'navigation',  'Navigation');
INSERT INTO view VALUES (6, '2012-10-25 19:57:05', '2012-10-25 20:27:48', 'Flash messages', 'flash-messages',  'Flash messages displayer');


--
-- Data for Name: document_type; Type: TABLE DATA; Schema: public;
--

INSERT INTO document_type VALUES (2, '2012-09-20 22:05:53', '2012-10-08 20:44:24', 'Contact', 'Contact form', 11, 3, 1);
INSERT INTO document_type VALUES (1, '2012-09-20 22:01:55', '2012-10-08 21:01:06', 'Home', 'Home page', 1, 1, 1);
INSERT INTO document_type VALUES (3, '2012-09-20 22:06:37', '2012-10-08 21:02:07', 'About', 'About this website', 13, 4, 1);


--
-- Data for Name: layout; Type: TABLE DATA; Schema: public;
--

INSERT INTO layout VALUES (1, '2012-09-19 19:28:34', '2012-09-20 22:31:50', 'Main', 'main-layout',  'Main layout');

--
-- Data for Name: document; Type: TABLE DATA; Schema: public;
--

INSERT INTO document VALUES (1, '2012-09-20 22:06:53', '2012-10-08 22:30:43', 'Home', '', 1, 0, true, true, NULL, 1, 1, 1, 1, NULL);
INSERT INTO document VALUES (2, '2012-09-20 22:09:06', '2012-10-08 22:30:55', 'About', 'about', 1, 1, true, true, NULL, 1, 3, 4, 1, NULL);
INSERT INTO document VALUES (3, '2012-09-20 22:09:29', '2012-10-09 08:00:21', 'Contact', 'contact', 1, 2, true, true, NULL, 1, 2, 3, 1, NULL);


--
-- Data for Name: datatype; Type: TABLE DATA; Schema: public;
--

INSERT INTO datatype VALUES (1, 'Text field', 'N;', 'Textstring');
INSERT INTO datatype VALUES (2, 'Rich text', 'a:1:{s:13:"toolbar-items";a:67:{s:6:"Source";s:1:"1";s:4:"Save";s:1:"1";s:7:"NewPage";s:1:"1";s:8:"DocProps";s:1:"1";s:7:"Preview";s:1:"1";s:5:"Print";s:1:"1";s:9:"Templates";s:1:"1";s:3:"Cut";s:1:"1";s:4:"Copy";s:1:"1";s:5:"Paste";s:1:"1";s:9:"PasteText";s:1:"1";s:13:"PasteFromWord";s:1:"1";s:4:"Undo";s:1:"1";s:4:"Redo";s:1:"1";s:4:"Find";s:1:"1";s:7:"Replace";s:1:"1";s:9:"SelectAll";s:1:"1";s:12:"SpellChecker";s:1:"1";s:5:"Scayt";s:1:"1";s:4:"Form";s:1:"1";s:8:"Checkbox";s:1:"1";s:5:"Radio";s:1:"1";s:9:"TextField";s:1:"1";s:8:"Textarea";s:1:"1";s:6:"Select";s:1:"1";s:6:"Button";s:1:"1";s:11:"ImageButton";s:1:"1";s:11:"HiddenField";s:1:"1";s:4:"Bold";s:1:"1";s:6:"Italic";s:1:"1";s:9:"Underline";s:1:"1";s:6:"Strike";s:1:"1";s:9:"Subscript";s:1:"1";s:11:"Superscript";s:1:"1";s:12:"RemoveFormat";s:1:"1";s:12:"NumberedList";s:1:"1";s:12:"BulletedList";s:1:"1";s:7:"Outdent";s:1:"1";s:6:"Indent";s:1:"1";s:10:"Blockquote";s:1:"1";s:9:"CreateDiv";s:1:"1";s:11:"JustifyLeft";s:1:"1";s:13:"JustifyCenter";s:1:"1";s:12:"JustifyRight";s:1:"1";s:12:"JustifyBlock";s:1:"1";s:7:"BidiLtr";s:1:"1";s:7:"BidiRtl";s:1:"1";s:4:"Link";s:1:"1";s:6:"Unlink";s:1:"1";s:6:"Anchor";s:1:"1";s:5:"Image";s:1:"1";s:5:"Flash";s:1:"1";s:5:"Table";s:1:"1";s:14:"HorizontalRule";s:1:"1";s:6:"Smiley";s:1:"1";s:11:"SpecialChar";s:1:"1";s:9:"PageBreak";s:1:"1";s:6:"Iframe";s:1:"1";s:6:"Styles";s:1:"1";s:6:"Format";s:1:"1";s:4:"Font";s:1:"1";s:8:"FontSize";s:1:"1";s:9:"TextColor";s:1:"1";s:7:"BGColor";s:1:"1";s:8:"Maximize";s:1:"1";s:10:"ShowBlocks";s:1:"1";s:5:"About";s:1:"1";}}', 'Textrich');
INSERT INTO datatype VALUES (3, 'Text area', 'a:3:{s:4:"cols";s:2:"50";s:4:"rows";s:2:"30";s:4:"wrap";s:4:"hard";}', 'Textarea');
INSERT INTO datatype VALUES (4, 'Simple Image', 'a:2:{s:9:"mime_list";a:3:{i:0;s:9:"image/gif";i:1;s:10:"image/jpeg";i:2;s:9:"image/png";}s:11:"is_multiple";b:0;}', 'Upload');
INSERT INTO datatype VALUES (5, 'Slider', 'a:1:{s:9:"datatypes";a:2:{i:0;a:3:{s:4:"name";s:6:"Upload";s:5:"label";s:4:"File";s:6:"config";a:2:{s:9:"mime_list";a:3:{i:0;s:9:"image/gif";i:1;s:10:"image/jpeg";i:2;s:9:"image/png";}s:11:"is_multiple";b:0;}}i:1;a:3:{s:4:"name";s:8:"Textarea";s:5:"label";s:7:"Content";s:6:"config";a:3:{s:4:"cols";s:0:"";s:4:"rows";s:0:"";s:4:"wrap";s:4:"hard";}}}}', 'Mixed');




--
-- Data for Name: tab; Type: TABLE DATA; Schema: public;
--

INSERT INTO tab VALUES (7, 'Title and meta', 'Meta description', NULL, 2);
INSERT INTO tab VALUES (2, 'Content', 'Content', NULL, 1);
INSERT INTO tab VALUES (5, 'Title and meta', 'Meta description', NULL, 1);
INSERT INTO tab VALUES (4, 'Content', 'Content', NULL, 3);
INSERT INTO tab VALUES (8, 'Title and meta', 'Meta description', NULL, 3);


--
-- Data for Name: property; Type: TABLE DATA; Schema: public;
--

INSERT INTO property VALUES (6, 'Meta description', 'metaDescription', 'Description', false, NULL, 7, 1);
INSERT INTO property VALUES (7, 'Keywords', 'metaKeywords', 'Keywords', false, NULL, 7, 1);
INSERT INTO property VALUES (8, 'Title', 'pageTitle', 'Title', false, NULL, 7, 1);
INSERT INTO property VALUES (9, 'Main Title', 'mainTitle', 'Title', false, NULL, 7, 1);
INSERT INTO property VALUES (14, 'Slider', 'slider', 'Slider', false, NULL, 2, 5);
INSERT INTO property VALUES (1, 'Meta description', 'metaDescription', 'Description', false, NULL, 5, 1);
INSERT INTO property VALUES (2, 'Keywords', 'metaKeywords', 'Keywords', false, NULL, 5, 1);
INSERT INTO property VALUES (3, 'Title', 'pageTitle', 'Title', false, NULL, 5, 1);
INSERT INTO property VALUES (4, 'Main Title', 'mainTitle', 'Title', false, NULL, 5, 1);
INSERT INTO property VALUES (5, 'Content', 'content', 'content', false, NULL, 4, 2);
INSERT INTO property VALUES (10, 'Meta description', 'metaDescription', 'Description', false, NULL, 8, 1);
INSERT INTO property VALUES (11, 'Keywords', 'metaKeywords', 'Keywords', false, NULL, 8, 1);
INSERT INTO property VALUES (12, 'Title', 'pageTitle', 'Title', false, NULL, 8, 1);
INSERT INTO property VALUES (13, 'Main Title', 'mainTitle', 'Title', false, NULL, 8, 1);


--
-- Data for Name: property_value; Type: TABLE DATA; Schema: public;
--

INSERT INTO property_value VALUES (6, 2, 5, '\x3c68313e0a0941626f757420546869732054656d706c6174653c2f68313e0a3c703e0a09546869732073696d706c652c20666978656420776964746820776562736974652074656d706c6174652069732072656c656173656420756e6465722061203c6120687265663d22687474703a2f2f6372656174697665636f6d6d6f6e732e6f72672f6c6963656e7365732f62792f332e30223e437265617469766520436f6d6d6f6e73204174747269627574696f6e20332e30204c6963656e63653c2f613e2e2054686973206d65616e7320796f7520617265206672656520746f20646f776e6c6f616420616e642075736520697420666f7220706572736f6e616c20616e6420636f6d6d65726369616c2070726f6a656374732e20486f77657665722c20796f75203c7374726f6e673e6d757374206c656176652074686520262333393b64657369676e2066726f6d206373733374656d706c617465732e636f2e756b262333393b206c696e6b20696e2074686520666f6f746572206f66207468652074656d706c6174653c2f7374726f6e673e2e20546869732074656d706c617465206973207772697474656e20656e746972656c7920696e203c7374726f6e673e48544d4c353c2f7374726f6e673e20616e64203c7374726f6e673e435353333c2f7374726f6e673e2e3c2f703e0a');
INSERT INTO property_value VALUES (7, 2, 10, '\x41626f7574');
INSERT INTO property_value VALUES (8, 2, 11, '\x');
INSERT INTO property_value VALUES (9, 2, 12, '\x41626f7574');
INSERT INTO property_value VALUES (10, 2, 13, '\x41626f7574');
INSERT INTO property_value VALUES (11, 3, 6, '\x436f6e74616374');
INSERT INTO property_value VALUES (12, 3, 7, '\x436f6e74616374');
INSERT INTO property_value VALUES (13, 3, 8, '\x436f6e74616374');
INSERT INTO property_value VALUES (14, 3, 9, '\x436f6e74616374');
INSERT INTO property_value VALUES (1, 1, 14, '\x613a333a7b693a313b613a323a7b693a303b613a313a7b733a353a2276616c7565223b733a3134323a22613a353a7b733a353a2276616c7565223b733a33353a222f6d656469612f66696c65732f312f31342f353262313538353062383138662e6a7067223b733a353a227769647468223b693a3935303b733a363a22686569676874223b693a3435303b733a343a2268746d6c223b693a323b733a343a226d696d65223b733a31303a22696d6167652f6a706567223b7d223b7d693a313b613a313a7b733a353a2276616c7565223b733a35313a2222596f752063616e2070757420610d0a63617074696f6e20666f7220796f75720d0a696d616765207269676874206865726522223b7d7d693a323b613a323a7b693a303b613a313a7b733a353a2276616c7565223b733a3134323a22613a353a7b733a353a2276616c7565223b733a33353a222f6d656469612f66696c65732f312f31342f353262313538353062613939622e6a7067223b733a353a227769647468223b693a3935303b733a363a22686569676874223b693a3435303b733a343a2268746d6c223b693a323b733a343a226d696d65223b733a31303a22696d6167652f6a706567223b7d223b7d693a313b613a313a7b733a353a2276616c7565223b733a35313a2222596f752063616e2070757420610d0a63617074696f6e20666f7220796f75720d0a696d616765207269676874206865726522223b7d7d693a333b613a323a7b693a303b613a313a7b733a353a2276616c7565223b733a3134323a22613a353a7b733a353a2276616c7565223b733a33353a222f6d656469612f66696c65732f312f31342f353262313538353062633435362e6a7067223b733a353a227769647468223b693a3935303b733a363a22686569676874223b693a3435303b733a343a2268746d6c223b693a323b733a343a226d696d65223b733a31303a22696d6167652f6a706567223b7d223b7d693a313b613a313a7b733a353a2276616c7565223b733a35313a2222596f752063616e2070757420610d0a63617074696f6e20666f7220796f75720d0a696d616765207269676874206865726522223b7d7d7d');
INSERT INTO property_value VALUES (2, 1, 1, '\x4d792077656273697465');
INSERT INTO property_value VALUES (3, 1, 2, '\x');
INSERT INTO property_value VALUES (4, 1, 3, '\x4d792077656273697465');
INSERT INTO property_value VALUES (5, 1, 4, '\x4d792077656273697465');


--
-- Data for Name: script; Type: TABLE DATA; Schema: public;
--

INSERT INTO script VALUES (1, '2012-09-20 22:26:23', '2012-09-20 22:26:23', 'Contact', 'contact', 'Contact');

--
-- PostgreSQL database dump complete
--
