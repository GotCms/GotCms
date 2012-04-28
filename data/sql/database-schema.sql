------------------------------
-- pgDesigner 1.2.17
--
-- Project    : GotCms
-- Date       : 04/28/2012 23:15:49.323
-- Description: 
------------------------------


-- Start Séquence's declaration
DROP SEQUENCE IF EXISTS "datatypes_id_seq" CASCADE;
CREATE SEQUENCE "datatypes_id_seq" INCREMENT 1 NO MINVALUE NO MAXVALUE START 1 NO CYCLE;

DROP SEQUENCE IF EXISTS "document_types_id_seq" CASCADE;
CREATE SEQUENCE "document_types_id_seq" INCREMENT 1 NO MINVALUE NO MAXVALUE START 1 NO CYCLE;

DROP SEQUENCE IF EXISTS "documents_id_seq" CASCADE;
CREATE SEQUENCE "documents_id_seq" INCREMENT 1 NO MINVALUE NO MAXVALUE START 1 NO CYCLE;

DROP SEQUENCE IF EXISTS "icons_id_seq" CASCADE;
CREATE SEQUENCE "icons_id_seq" INCREMENT 1 NO MINVALUE NO MAXVALUE START 1 NO CYCLE;

DROP SEQUENCE IF EXISTS "layouts_id_seq" CASCADE;
CREATE SEQUENCE "layouts_id_seq" INCREMENT 1 NO MINVALUE NO MAXVALUE START 1 NO CYCLE;

DROP SEQUENCE IF EXISTS "properties_id_seq" CASCADE;
CREATE SEQUENCE "properties_id_seq" INCREMENT 1 NO MINVALUE NO MAXVALUE START 1 NO CYCLE;

DROP SEQUENCE IF EXISTS "properties_value_id_seq" CASCADE;
CREATE SEQUENCE "properties_value_id_seq" INCREMENT 1 NO MINVALUE NO MAXVALUE START 1 NO CYCLE;

DROP SEQUENCE IF EXISTS "tabs_id_seq" CASCADE;
CREATE SEQUENCE "tabs_id_seq" INCREMENT 1 NO MINVALUE NO MAXVALUE START 1 NO CYCLE;

DROP SEQUENCE IF EXISTS "translate_id_seq" CASCADE;
CREATE SEQUENCE "translate_id_seq" INCREMENT 1 NO MINVALUE NO MAXVALUE START 1 CYCLE;

DROP SEQUENCE IF EXISTS "translate_language_id_seq" CASCADE;
CREATE SEQUENCE "translate_language_id_seq" INCREMENT 1 NO MINVALUE NO MAXVALUE START 1 CYCLE;

DROP SEQUENCE IF EXISTS "user_acl_roles_id_seq" CASCADE;
CREATE SEQUENCE "user_acl_roles_id_seq" INCREMENT 1 NO MINVALUE NO MAXVALUE START 1 NO CYCLE;

DROP SEQUENCE IF EXISTS "user_acl_permissions_id_seq" CASCADE;
CREATE SEQUENCE "user_acl_permissions_id_seq" INCREMENT 1 NO MINVALUE NO MAXVALUE START 1 NO CYCLE;

DROP SEQUENCE IF EXISTS "user_acl_resources_id_seq" CASCADE;
CREATE SEQUENCE "user_acl_resources_id_seq" INCREMENT 1 NO MINVALUE NO MAXVALUE START 1 NO CYCLE;

DROP SEQUENCE IF EXISTS "users_id_seq" CASCADE;
CREATE SEQUENCE "users_id_seq" INCREMENT 1 NO MINVALUE NO MAXVALUE START 1 NO CYCLE;

DROP SEQUENCE IF EXISTS "views_id_seq" CASCADE;
CREATE SEQUENCE "views_id_seq" INCREMENT 1 NO MINVALUE NO MAXVALUE START 1 CYCLE;

-- End Séquence's declaration

-- Start Table's declaration
DROP TABLE IF EXISTS "datatypes" CASCADE;
CREATE TABLE "datatypes" (
"id" serial NOT NULL,
"name" character varying NOT NULL,
"prevalue_value" text
) WITH OIDS;
ALTER TABLE "datatypes" ADD CONSTRAINT "datatypes_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "document_type_views" CASCADE;
CREATE TABLE "document_type_views" (
"id" serial NOT NULL,
"view_id" integer NOT NULL,
"document_type_id" integer NOT NULL
) WITH OIDS;
ALTER TABLE "document_type_views" ADD CONSTRAINT "document_type_views_pk" PRIMARY KEY("id","view_id","document_type_id");

DROP TABLE IF EXISTS "document_types" CASCADE;
CREATE TABLE "document_types" (
"id" serial NOT NULL,
"created_at" timestamp without time zone NOT NULL,
"updated_at" timestamp without time zone NOT NULL,
"name" character varying NOT NULL,
"description" text,
"icon_id" integer,
"default_view_id" integer,
"user_id" integer NOT NULL
) WITH OIDS;
ALTER TABLE "document_types" ADD CONSTRAINT "document_types_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "documents" CASCADE;
CREATE TABLE "documents" (
"id" serial NOT NULL,
"created_at" timestamp without time zone NOT NULL,
"updated_at" timestamp without time zone NOT NULL,
"name" character varying NOT NULL,
"url_key" character varying NOT NULL,
"status" boolean DEFAULT false,
"show_in_nav" boolean DEFAULT false,
"user_id" integer NOT NULL,
"document_type_id" integer NOT NULL,
"view_id" integer NOT NULL,
"layout_id" integer NOT NULL,
"parent_id" integer NOT NULL DEFAULT 0
) WITH OIDS;
ALTER TABLE "documents" ADD CONSTRAINT "documents_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "icons" CASCADE;
CREATE TABLE "icons" (
"id" serial NOT NULL,
"name" character varying NOT NULL,
"url" character varying NOT NULL
) WITH OIDS;
ALTER TABLE "icons" ADD CONSTRAINT "icons_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "layouts" CASCADE;
CREATE TABLE "layouts" (
"id" serial NOT NULL,
"created_at" timestamp without time zone NOT NULL,
"updated_at" timestamp without time zone NOT NULL,
"name" character varying NOT NULL,
"identifier" character varying NOT NULL,
"content" text,
"description" character varying
) WITH OIDS;
ALTER TABLE "layouts" ADD CONSTRAINT "layouts_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "properties" CASCADE;
CREATE TABLE "properties" (
"id" serial NOT NULL,
"name" character varying,
"identifier" character varying NOT NULL,
"description" character varying,
"required" boolean NOT NULL DEFAULT false,
"order" integer DEFAULT 0,
"tab_id" integer NOT NULL,
"datatype_id" integer NOT NULL
) WITH OIDS;
ALTER TABLE "properties" ADD CONSTRAINT "properties_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "properties_values" CASCADE;
CREATE TABLE "properties_values" (
"id" serial NOT NULL,
"document_id" integer NOT NULL,
"property_id" integer NOT NULL,
"value" text
) WITH OIDS;
ALTER TABLE "properties_values" ADD CONSTRAINT "properties_values_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "tabs" CASCADE;
CREATE TABLE "tabs" (
"id" serial NOT NULL,
"name" character varying NOT NULL,
"description" character varying,
"order" integer DEFAULT 0,
"document_type_id" integer NOT NULL
) WITH OIDS;
ALTER TABLE "tabs" ADD CONSTRAINT "tabs_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "translate" CASCADE;
CREATE TABLE "translate" (
"id" serial NOT NULL,
"source" character varying NOT NULL,
"hash" character varying NOT NULL
) WITH OIDS;
ALTER TABLE "translate" ADD CONSTRAINT "translate_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "translate_language" CASCADE;
CREATE TABLE "translate_language" (
"id" serial NOT NULL,
"destination" character varying NOT NULL,
"language" character varying NOT NULL,
"translate_id" integer NOT NULL,
"hash" character varying NOT NULL
) WITH OIDS;
ALTER TABLE "translate_language" ADD CONSTRAINT "translate_language_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "user_acl_roles" CASCADE;
CREATE TABLE "user_acl_roles" (
"id" serial NOT NULL,
"name" character varying,
"description" character varying
) WITH OIDS;
ALTER TABLE "user_acl_roles" ADD CONSTRAINT "user_acl_roles_pkey" PRIMARY KEY("id");
CREATE UNIQUE INDEX "user_acl_roles_name_key" ON "user_acl_roles" USING btree ("name");

DROP TABLE IF EXISTS "user_acl" CASCADE;
CREATE TABLE "user_acl" (
"id" serial NOT NULL,
"user_acl_permission_id" integer NOT NULL,
"user_acl_role_id" integer NOT NULL,
"user_acl_resource_id" integer NOT NULL
) WITH OIDS;
ALTER TABLE "user_acl" ADD CONSTRAINT "user_acl_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "user_acl_resources" CASCADE;
CREATE TABLE "user_acl_resources" (
"id" serial NOT NULL,
"resource" character varying
) WITH OIDS;
ALTER TABLE "user_acl_resources" ADD CONSTRAINT "user_acl_resources_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "users" CASCADE;
CREATE TABLE "users" (
"id" serial NOT NULL,
"created_at" timestamp without time zone NOT NULL,
"updated_at" timestamp without time zone NOT NULL,
"lastname" character varying NOT NULL,
"firstname" character varying NOT NULL,
"email" character varying NOT NULL,
"login" character varying NOT NULL,
"password" character varying NOT NULL,
"user_acl_role_id" integer NOT NULL DEFAULT 0
) WITH OIDS;
ALTER TABLE "users" ADD CONSTRAINT "users_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "views" CASCADE;
CREATE TABLE "views" (
"id" serial NOT NULL,
"created_at" timestamp without time zone NOT NULL,
"updated_at" timestamp without time zone NOT NULL,
"name" character varying NOT NULL,
"identifier" character varying,
"content" text,
"description" character varying
) WITH OIDS;
ALTER TABLE "views" ADD CONSTRAINT "views_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "user_acl_permissions" CASCADE;
CREATE TABLE "user_acl_permissions" (
"id" serial NOT NULL,
"permission" character varying(50)[] NOT NULL
) WITH OIDS;
ALTER TABLE "user_acl_permissions" ADD CONSTRAINT "user_acl_permissions_pk" PRIMARY KEY("id");

-- End Table's declaration

-- Start Relation's declaration
ALTER TABLE "document_type_views" ADD CONSTRAINT "fk_document_type_views_views" FOREIGN KEY ("view_id") REFERENCES "views"("id") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE "properties" ADD CONSTRAINT "fk_properties_datatypes" FOREIGN KEY ("datatype_id") REFERENCES "datatypes"("id") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE "properties" ADD CONSTRAINT "fk_properties_tabs" FOREIGN KEY ("tab_id") REFERENCES "tabs"("id") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE "documents" ADD CONSTRAINT "fk_documents_layouts" FOREIGN KEY ("layout_id") REFERENCES "layouts"("id") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE "documents" ADD CONSTRAINT "fk_documents_documents" FOREIGN KEY ("parent_id") REFERENCES "documents"("id") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE "documents" ADD CONSTRAINT "fk_documents_views" FOREIGN KEY ("view_id") REFERENCES "views"("id") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE "documents" ADD CONSTRAINT "fk_documents_document_types" FOREIGN KEY ("document_type_id") REFERENCES "document_types"("id") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE "document_types" ADD CONSTRAINT "fk_document_types_views" FOREIGN KEY ("default_view_id") REFERENCES "views"("id") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE "document_types" ADD CONSTRAINT "fk_document_types_icons" FOREIGN KEY ("icon_id") REFERENCES "icons"("id") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE "documents" ADD CONSTRAINT "fk_documents_users" FOREIGN KEY ("user_id") REFERENCES "users"("id") ON UPDATE SET NULL ON DELETE SET NULL;

ALTER TABLE "user_acl" ADD CONSTRAINT "fk_user_acl_permissions_user_acl_roles" FOREIGN KEY ("user_acl_role_id") REFERENCES "user_acl_roles"("id") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE "user_acl" ADD CONSTRAINT "fk_user_acl_permissions_user_acl_resources" FOREIGN KEY ("user_acl_resource_id") REFERENCES "user_acl_resources"("id") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE "users" ADD CONSTRAINT "fk_users_user_acl_roles" FOREIGN KEY ("user_acl_role_id") REFERENCES "user_acl_roles"("id") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE "translate_language" ADD CONSTRAINT "fk_translate_language_translate" FOREIGN KEY ("translate_id") REFERENCES "translate"("id") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE "user_acl" ADD CONSTRAINT "fk_user_acl_user_acl_permission" FOREIGN KEY ("user_acl_permission_id") REFERENCES "user_acl_permissions"("id") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE "document_type_views" ADD CONSTRAINT "fk_document_type_views_document_type" FOREIGN KEY ("document_type_id") REFERENCES "document_types"("id") ON UPDATE CASCADE ON DELETE CASCADE;

-- End Relation's declaration

