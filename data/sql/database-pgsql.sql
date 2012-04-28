--
-- PostgreSQL database dump
--
SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;
SET default_tablespace = '';
SET default_with_oids = true;
--
-- Name: datatypes; Type: TABLE; Schema: public; Tablespace: 
--
CREATE TABLE datatypes (
    id integer NOT NULL,
    name character varying(50) NOT NULL,
    prevalue_value text,
    model_id integer
);

--
-- Name: datatypes_id_seq; Type: SEQUENCE; Schema: public;
--
CREATE SEQUENCE datatypes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: datatypes_id_seq; Type: SEQUENCE SET; Schema: public;
--
SELECT pg_catalog.setval('datatypes_id_seq', 1, false);

--
-- Name: document_type_views; Type: TABLE; Schema: public; Tablespace: 
--
CREATE TABLE document_type_views (
    id integer NOT NULL,
    view_id integer NOT NULL
);

--
-- Name: document_types; Type: TABLE; Schema: public; Tablespace: 
--
CREATE TABLE document_types (
    id integer NOT NULL,
    created_at timestamp without time zone NOT NULL,
    updated_at timestamp without time zone NOT NULL,
    name character varying(50) NOT NULL,
    description text,
    icon_id integer,
    default_view_id integer,
    user_id integer NOT NULL
);

--
-- Name: document_types_id_seq; Type: SEQUENCE; Schema: public;
--
CREATE SEQUENCE document_types_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: document_types_id_seq; Type: SEQUENCE SET; Schema: public;
--
SELECT pg_catalog.setval('document_types_id_seq', 1, false);

--
-- Name: documents; Type: TABLE; Schema: public; Tablespace: 
--
CREATE TABLE documents (
    id integer NOT NULL,
    created_at timestamp without time zone NOT NULL,
    updated_at timestamp without time zone NOT NULL,
    name character varying(100) NOT NULL,
    url_key character varying(50) NOT NULL,
    status boolean DEFAULT false,
    show_in_nav boolean DEFAULT false,
    user_id integer,
    document_type_id integer,
    view_id integer,
    layout_id integer,
    parent_id integer DEFAULT 0
);

--
-- Name: documents_id_seq; Type: SEQUENCE; Schema: public;
--
CREATE SEQUENCE documents_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
--
-- Name: documents_id_seq; Type: SEQUENCE SET; Schema: public;
--
SELECT pg_catalog.setval('documents_id_seq', 1, false);

--
-- Name: icons; Type: TABLE; Schema: public; Tablespace: 
--
CREATE TABLE icons (
    id integer NOT NULL,
    name character varying(20) NOT NULL,
    url character varying(50) NOT NULL
);

--
-- Name: icons_id_seq; Type: SEQUENCE; Schema: public;
--
CREATE SEQUENCE icons_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: icons_id_seq; Type: SEQUENCE SET; Schema: public;
--
SELECT pg_catalog.setval('icons_id_seq', 1, false);

--
-- Name: layouts; Type: TABLE; Schema: public; Tablespace: 
--
CREATE TABLE layouts (
    id integer NOT NULL,
    created_at timestamp without time zone NOT NULL,
    updated_at timestamp without time zone NOT NULL,
    name character varying(50),
    identifier character varying(50) NOT NULL,
    content text,
    description character varying(255)
);

--
-- Name: layouts_id_seq; Type: SEQUENCE; Schema: public;
--
CREATE SEQUENCE layouts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: layouts_id_seq; Type: SEQUENCE SET; Schema: public;
--
SELECT pg_catalog.setval('layouts_id_seq', 1, false);

--
-- Name: models; Type: TABLE; Schema: public; Tablespace: 
--
CREATE TABLE models (
    id integer NOT NULL,
    name character varying(50) NOT NULL,
    identifier character varying(50) NOT NULL,
    description character varying(255)
);

--
-- Name: models_id_seq; Type: SEQUENCE; Schema: public;
--
CREATE SEQUENCE models_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: models_id_seq; Type: SEQUENCE SET; Schema: public;
--
SELECT pg_catalog.setval('models_id_seq', 1, false);

--
-- Name: properties; Type: TABLE; Schema: public; Tablespace: 
--
CREATE TABLE properties (
    id integer NOT NULL,
    name character varying(50),
    identifier character varying(50) NOT NULL,
    description character varying(255),
    required boolean,
    "order" integer,
    tab_id integer,
    datatype_id integer
);
--
-- Name: properties_id_seq; Type: SEQUENCE; Schema: public;
--
CREATE SEQUENCE properties_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: properties_id_seq; Type: SEQUENCE SET; Schema: public;
--
SELECT pg_catalog.setval('properties_id_seq', 1, false);

--
-- Name: properties_value_id_seq; Type: SEQUENCE; Schema: public;
--
CREATE SEQUENCE properties_value_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: properties_value_id_seq; Type: SEQUENCE SET; Schema: public;
--
SELECT pg_catalog.setval('properties_value_id_seq', 1, false);

--
-- Name: properties_values; Type: TABLE; Schema: public; Tablespace: 
--
CREATE TABLE properties_values (
    id integer NOT NULL,
    document_id integer,
    property_id integer,
    value text
);

--
-- Name: tabs; Type: TABLE; Schema: public; Tablespace: 
--
CREATE TABLE tabs (
    id integer NOT NULL,
    name character varying(50) NOT NULL,
    description character varying(150),
    "order" integer DEFAULT 0,
    document_type_id integer NOT NULL
);

--
-- Name: tabs_id_seq; Type: SEQUENCE; Schema: public;
--
CREATE SEQUENCE tabs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: tabs_id_seq; Type: SEQUENCE SET; Schema: public;
--
SELECT pg_catalog.setval('tabs_id_seq', 1, false);

--
-- Name: translate; Type: TABLE; Schema: public; Tablespace: 
--
CREATE TABLE translate (
    id integer NOT NULL,
    source character varying(255) NOT NULL,
    hash character varying(40) NOT NULL
);

--
-- Name: translate_id_seq; Type: SEQUENCE; Schema: public;
--
CREATE SEQUENCE translate_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
    CYCLE;

--
-- Name: translate_id_seq; Type: SEQUENCE SET; Schema: public;
--
SELECT pg_catalog.setval('translate_id_seq', 1, false);

--
-- Name: translate_language; Type: TABLE; Schema: public; Tablespace: 
--
CREATE TABLE translate_language (
    id integer NOT NULL,
    destination character varying(255) NOT NULL,
    language character varying(2) NOT NULL,
    translate_id integer,
    hash character varying(40) NOT NULL
);

--
-- Name: translate_language_id_seq; Type: SEQUENCE; Schema: public;
--
CREATE SEQUENCE translate_language_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
    CYCLE;
--
-- Name: translate_language_id_seq; Type: SEQUENCE SET; Schema: public;
--
SELECT pg_catalog.setval('translate_language_id_seq', 1, false);

SET default_with_oids = false;
--
-- Name: user_roles; Type: TABLE; Schema: public; Tablespace: 
--
CREATE TABLE user_roles (
    id integer NOT NULL,
    name character varying(50),
    description character varying(255)
);
--
-- Name: user_roles_id_seq; Type: SEQUENCE; Schema: public;
--
CREATE SEQUENCE user_roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

--
-- Name: user_roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public;
--
ALTER SEQUENCE user_roles_id_seq OWNED BY user_roles.id;

--
-- Name: user_roles_id_seq; Type: SEQUENCE SET; Schema: public;
--
SELECT pg_catalog.setval('user_roles_id_seq', 1, false);

SET default_with_oids = true;
--
-- Name: users; Type: TABLE; Schema: public; Tablespace: 
--
CREATE TABLE users (
    id integer NOT NULL,
    created_at timestamp without time zone NOT NULL,
    updated_at timestamp without time zone NOT NULL,
    lastname character varying(30) NOT NULL,
    firstname character varying(30) NOT NULL,
    email character varying(50) NOT NULL,
    login character varying(50)  NOT NULL,
    password character varying(50) NOT NULL,
    user_role_id integer DEFAULT 0 NOT NULL
);

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public;
--
CREATE SEQUENCE users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public;
--
SELECT pg_catalog.setval('users_id_seq', 1, false);

--
-- Name: views; Type: TABLE; Schema: public; Tablespace: 
--
CREATE TABLE views (
    id integer NOT NULL,
    created_at timestamp without time zone NOT NULL,
    updated_at timestamp without time zone NOT NULL,
    name character varying(50) NOT NULL,
    identifier character varying(50),
    content text,
    description character varying(255)
);

--
-- Name: views_id_seq; Type: SEQUENCE; Schema: public;
--
CREATE SEQUENCE views_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
    CYCLE;

--
-- Name: views_id_seq; Type: SEQUENCE SET; Schema: public;
--
SELECT pg_catalog.setval('views_id_seq', 1, false);

--
-- Name: id; Type: DEFAULT; Schema: public;
--
ALTER TABLE ONLY user_roles ALTER COLUMN id SET DEFAULT nextval('user_roles_id_seq'::regclass);

--
-- Name: datatypes_pk; Type: CONSTRAINT; Schema: public; Tablespace: 
--
ALTER TABLE ONLY datatypes
    ADD CONSTRAINT datatypes_pk PRIMARY KEY (id);

--
-- Name: document_type_views_pk; Type: CONSTRAINT; Schema: public; Tablespace: 
--
ALTER TABLE ONLY document_type_views
    ADD CONSTRAINT document_type_views_pk PRIMARY KEY (id, view_id);

--
-- Name: document_types_pk; Type: CONSTRAINT; Schema: public; Tablespace: 
--
ALTER TABLE ONLY document_types
    ADD CONSTRAINT document_types_pk PRIMARY KEY (id);

--
-- Name: documents_pk; Type: CONSTRAINT; Schema: public; Tablespace: 
--
ALTER TABLE ONLY documents
    ADD CONSTRAINT documents_pk PRIMARY KEY (id);

--
-- Name: icons_pk; Type: CONSTRAINT; Schema: public; Tablespace: 
--
ALTER TABLE ONLY icons
    ADD CONSTRAINT icons_pk PRIMARY KEY (id);

--
-- Name: layouts_pk; Type: CONSTRAINT; Schema: public; Tablespace: 
--
ALTER TABLE ONLY layouts
    ADD CONSTRAINT layouts_pk PRIMARY KEY (id);

--
-- Name: models_pk; Type: CONSTRAINT; Schema: public; Tablespace: 
--
ALTER TABLE ONLY models
    ADD CONSTRAINT models_pk PRIMARY KEY (id);

--
-- Name: properties_pk; Type: CONSTRAINT; Schema: public; Tablespace: 
--
ALTER TABLE ONLY properties
    ADD CONSTRAINT properties_pk PRIMARY KEY (id);

--
-- Name: properties_values_pk; Type: CONSTRAINT; Schema: public; Tablespace: 
--
ALTER TABLE ONLY properties_values
    ADD CONSTRAINT properties_values_pk PRIMARY KEY (id);

--
-- Name: tabs_pk; Type: CONSTRAINT; Schema: public; Tablespace: 
--
ALTER TABLE ONLY tabs
    ADD CONSTRAINT tabs_pk PRIMARY KEY (id);

--
-- Name: translate_language_pk; Type: CONSTRAINT; Schema: public; Tablespace: 
--
ALTER TABLE ONLY translate_language
    ADD CONSTRAINT translate_language_pk PRIMARY KEY (id);

--
-- Name: translate_pk; Type: CONSTRAINT; Schema: public; Tablespace: 
--
ALTER TABLE ONLY translate
    ADD CONSTRAINT translate_pk PRIMARY KEY (id);

--
-- Name: user_roles_name_key; Type: CONSTRAINT; Schema: public; Tablespace: 
--
ALTER TABLE ONLY user_roles
    ADD CONSTRAINT user_roles_name_key UNIQUE (name);

--
-- Name: user_roles_pkey; Type: CONSTRAINT; Schema: public; Tablespace: 
--
ALTER TABLE ONLY user_roles
    ADD CONSTRAINT user_roles_pkey PRIMARY KEY (id);

--
-- Name: users_pk; Type: CONSTRAINT; Schema: public; Tablespace: 
--
ALTER TABLE ONLY users
    ADD CONSTRAINT users_pk PRIMARY KEY (id);

--
-- Name: views_pk; Type: CONSTRAINT; Schema: public; Tablespace: 
--
ALTER TABLE ONLY views
    ADD CONSTRAINT views_pk PRIMARY KEY (id);

--
-- Name: datatypes_name_idx; Type: INDEX; Schema: public; Tablespace: 
--
CREATE UNIQUE INDEX datatypes_name_idx ON datatypes USING btree (name);

--
-- Name: document_type_name_key; Type: INDEX; Schema: public; Tablespace: 
--
CREATE UNIQUE INDEX document_type_name_key ON document_types USING btree (name);

--
-- Name: document_url_key_parent_idx; Type: INDEX; Schema: public; Tablespace: 
--
CREATE UNIQUE INDEX document_url_key_parent_idx ON documents USING btree (url_key, parent_id);

--
-- Name: icon_name_idx; Type: INDEX; Schema: public; Tablespace: 
--
CREATE UNIQUE INDEX icon_name_idx ON icons USING btree (name);

--
-- Name: icon_url_idx; Type: INDEX; Schema: public; Tablespace: 
--
CREATE UNIQUE INDEX icon_url_idx ON icons USING btree (url);

--
-- Name: layout_identifier_idx; Type: INDEX; Schema: public; Tablespace: 
--
CREATE UNIQUE INDEX layout_identifier_idx ON layouts USING btree (identifier);

--
-- Name: layout_name_idx; Type: INDEX; Schema: public; Tablespace: 
--
CREATE UNIQUE INDEX layout_name_idx ON layouts USING btree (name);

--
-- Name: model_identifier_idx; Type: INDEX; Schema: public; Tablespace: 
--
CREATE UNIQUE INDEX model_identifier_idx ON models USING btree (identifier);

--
-- Name: model_name_idx; Type: INDEX; Schema: public; Tablespace: 
--
CREATE UNIQUE INDEX model_name_idx ON models USING btree (name);

--
-- Name: properties_identifier_idx; Type: INDEX; Schema: public; Tablespace: 
--
CREATE UNIQUE INDEX properties_identifier_idx ON properties USING btree (identifier);

--
-- Name: properties_values_idx; Type: INDEX; Schema: public; Tablespace: 
--
CREATE UNIQUE INDEX properties_values_idx ON properties_values USING btree (document_id, property_id);

--
-- Name: translate_language_idx; Type: INDEX; Schema: public; Tablespace: 
--
CREATE UNIQUE INDEX translate_language_idx ON translate_language USING btree (destination);

--
-- Name: translate_source_idx; Type: INDEX; Schema: public; Tablespace: 
--
CREATE UNIQUE INDEX translate_source_idx ON translate USING btree (source);

--
-- Name: user_email_idx; Type: INDEX; Schema: public; Tablespace: 
--
CREATE UNIQUE INDEX user_email_idx ON users USING btree (email);

--
-- Name: views_identifier_idx; Type: INDEX; Schema: public; Tablespace: 
--
CREATE UNIQUE INDEX views_identifier_idx ON views USING btree (identifier);

--
-- Name: views_name_idx; Type: INDEX; Schema: public; Tablespace: 
--
CREATE UNIQUE INDEX views_name_idx ON views USING btree (name);

--
-- Name: datatypes_model_id; Type: FK CONSTRAINT; Schema: public;
--
ALTER TABLE ONLY datatypes
    ADD CONSTRAINT fk_datatypes_model FOREIGN KEY (model_id) REFERENCES models(id) ON UPDATE CASCADE ON DELETE CASCADE;

--
-- Name: document_type_views_document_types_id; Type: FK CONSTRAINT; Schema: public;
--
ALTER TABLE ONLY document_type_views
    ADD CONSTRAINT fk_document_type_views_document_types FOREIGN KEY (id) REFERENCES document_types(id) ON UPDATE CASCADE ON DELETE CASCADE;

--
-- Name: document_type_views_view_id; Type: FK CONSTRAINT; Schema: public;
--
ALTER TABLE ONLY document_type_views
    ADD CONSTRAINT fk_document_type_views_view_id FOREIGN KEY (view_id) REFERENCES views(id) ON UPDATE CASCADE ON DELETE CASCADE;

--
-- Name: document_types_icon_id; Type: FK CONSTRAINT; Schema: public;
--
ALTER TABLE ONLY document_types
    ADD CONSTRAINT fk_document_types_icon FOREIGN KEY (icon_id) REFERENCES icons(id) ON UPDATE CASCADE ON DELETE CASCADE;

--
-- Name: document_types_id; Type: FK CONSTRAINT; Schema: public;
--
ALTER TABLE ONLY documents
    ADD CONSTRAINT fk_documents_document_type FOREIGN KEY (document_type_id) REFERENCES document_types(id) ON UPDATE CASCADE ON DELETE CASCADE;

--
-- Name: document_types_user_id; Type: FK CONSTRAINT; Schema: public;
--
ALTER TABLE ONLY document_types
    ADD CONSTRAINT fk_document_types_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE;

--
-- Name: document_types_view_id; Type: FK CONSTRAINT; Schema: public;
--
ALTER TABLE ONLY document_types
    ADD CONSTRAINT fk_document_types_view FOREIGN KEY (default_view_id) REFERENCES views(id) ON UPDATE CASCADE ON DELETE CASCADE;

--
-- Name: documents_layout_id; Type: FK CONSTRAINT; Schema: public;
--
ALTER TABLE ONLY documents
    ADD CONSTRAINT fk_documents_layout FOREIGN KEY (layout_id) REFERENCES layouts(id) ON UPDATE CASCADE ON DELETE CASCADE;

--
-- Name: documents_user_id; Type: FK CONSTRAINT; Schema: public;
--
ALTER TABLE ONLY documents
    ADD CONSTRAINT fk_documents_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE;

--
-- Name: documents_view_id; Type: FK CONSTRAINT; Schema: public;
--
ALTER TABLE ONLY documents
    ADD CONSTRAINT fk_documents_view FOREIGN KEY (view_id) REFERENCES views(id) ON UPDATE CASCADE ON DELETE CASCADE;

--
-- Name: properties_datatype_id; Type: FK CONSTRAINT; Schema: public;
--
ALTER TABLE ONLY properties
    ADD CONSTRAINT fk_properties_datatype FOREIGN KEY (datatype_id) REFERENCES datatypes(id) ON UPDATE CASCADE ON DELETE CASCADE;

--
-- Name: properties_tab_id; Type: FK CONSTRAINT; Schema: public;
--
ALTER TABLE ONLY properties
    ADD CONSTRAINT fk_properties_tab FOREIGN KEY (tab_id) REFERENCES tabs(id) ON UPDATE CASCADE ON DELETE CASCADE;

--
-- Name: properties_values_document_id; Type: FK CONSTRAINT; Schema: public;
--
ALTER TABLE ONLY properties_values
    ADD CONSTRAINT fk_properties_values_document FOREIGN KEY (document_id) REFERENCES documents(id) ON UPDATE CASCADE ON DELETE CASCADE;

--
-- Name: properties_values_property_id; Type: FK CONSTRAINT; Schema: public;
--
ALTER TABLE ONLY properties_values
    ADD CONSTRAINT fk_properties_values_property FOREIGN KEY (property_id) REFERENCES properties(id) ON UPDATE CASCADE ON DELETE CASCADE;

--
-- Name: tabs_document_types_id; Type: FK CONSTRAINT; Schema: public;
--
ALTER TABLE ONLY tabs
    ADD CONSTRAINT fk_tabs_document_types FOREIGN KEY (document_type_id) REFERENCES document_types(id) ON UPDATE CASCADE ON DELETE CASCADE;

--
-- Name: translate_locale_id; Type: FK CONSTRAINT; Schema: public;
--
ALTER TABLE ONLY translate_language
    ADD CONSTRAINT fk_translate_language_translate FOREIGN KEY (translate_id) REFERENCES translate(id) ON UPDATE CASCADE ON DELETE CASCADE;

--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--
REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;

--
-- PostgreSQL database dump complete
--
