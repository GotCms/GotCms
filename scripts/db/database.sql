------------------------------
-- pgDesigner 1.2.17
--
-- Project    : Easy Setting Cms
-- Date       : 05/21/2011 21:10:34.232
-- Description: 
------------------------------


-- Start Séquence's declaration
DROP SEQUENCE IF EXISTS "datatypes_id_seq" CASCADE;
CREATE SEQUENCE "datatypes_id_seq" INCREMENT 1 NO MINVALUE NO MAXVALUE START 1 NO CYCLE;

DROP SEQUENCE IF EXISTS "document_types_document_type_id_seq" CASCADE;
CREATE SEQUENCE "document_types_document_type_id_seq" INCREMENT 1 NO MINVALUE NO MAXVALUE START 1 NO CYCLE;

DROP SEQUENCE IF EXISTS "documents_id_seq" CASCADE;
CREATE SEQUENCE "documents_id_seq" INCREMENT 1 NO MINVALUE NO MAXVALUE START 1 NO CYCLE;

DROP SEQUENCE IF EXISTS "icons_id_seq" CASCADE;
CREATE SEQUENCE "icons_id_seq" INCREMENT 1 NO MINVALUE NO MAXVALUE START 1 NO CYCLE;

DROP SEQUENCE IF EXISTS "layouts_id_seq" CASCADE;
CREATE SEQUENCE "layouts_id_seq" INCREMENT 1 NO MINVALUE NO MAXVALUE START 1 NO CYCLE;

DROP SEQUENCE IF EXISTS "models_id_seq" CASCADE;
CREATE SEQUENCE "models_id_seq" INCREMENT 1 NO MINVALUE NO MAXVALUE START 1 NO CYCLE;

DROP SEQUENCE IF EXISTS "properties_id_seq" CASCADE;
CREATE SEQUENCE "properties_id_seq" INCREMENT 1 NO MINVALUE NO MAXVALUE START 1 NO CYCLE;

DROP SEQUENCE IF EXISTS "properties_value_id_seq" CASCADE;
CREATE SEQUENCE "properties_value_id_seq" INCREMENT 1 NO MINVALUE NO MAXVALUE START 1 NO CYCLE;

DROP SEQUENCE IF EXISTS "tab_id_seq" CASCADE;
CREATE SEQUENCE "tab_id_seq" INCREMENT 1 NO MINVALUE NO MAXVALUE START 1 NO CYCLE;

DROP SEQUENCE IF EXISTS "users_id_seq" CASCADE;
CREATE SEQUENCE "users_id_seq" INCREMENT 1 NO MINVALUE NO MAXVALUE START 1 NO CYCLE;

DROP SEQUENCE IF EXISTS "views_id_seq" CASCADE;
CREATE SEQUENCE "views_id_seq" INCREMENT 1 NO MINVALUE NO MAXVALUE START 1 CYCLE;

DROP SEQUENCE IF EXISTS "translate_id_seq" CASCADE;
CREATE SEQUENCE "translate_id_seq" INCREMENT 1 NO MINVALUE NO MAXVALUE CYCLE;

DROP SEQUENCE IF EXISTS "translate_language_id_seq" CASCADE;
CREATE SEQUENCE "translate_language_id_seq" INCREMENT 1 NO MINVALUE NO MAXVALUE CYCLE;

-- End Séquence's declaration

-- Start Table's declaration
DROP TABLE IF EXISTS "datatypes" CASCADE;
CREATE TABLE "datatypes" (
"id" integer NOT NULL,
"name" character varying(50) NOT NULL,
"prevalue_value" text,
"model_id" integer
) WITH OIDS;
ALTER TABLE "datatypes" ADD CONSTRAINT "datatypes_pk" PRIMARY KEY("id");
CREATE UNIQUE INDEX "datatypes_name_idx" ON "datatypes" USING btree ("name");

DROP TABLE IF EXISTS "document_types" CASCADE;
CREATE TABLE "document_types" (
"id" integer NOT NULL,
"name" character varying(50) NOT NULL,
"created_at" timestamp without time zone,
"updated_at" timestamp without time zone,
"description" text,
"icon_id" integer,
"default_view_id" integer,
"user_id" integer NOT NULL
) WITH OIDS;
ALTER TABLE "document_types" ADD CONSTRAINT "document_types_pk" PRIMARY KEY("id");
CREATE UNIQUE INDEX "document_type_name_key" ON "document_types" USING btree ("name");

DROP TABLE IF EXISTS "document_type_views" CASCADE;
CREATE TABLE "document_type_views" (
"id" integer NOT NULL,
"view_id" integer NOT NULL
) WITH OIDS;
ALTER TABLE "document_type_views" ADD CONSTRAINT "document_type_views_pk" PRIMARY KEY("id","view_id");

DROP TABLE IF EXISTS "documents" CASCADE;
CREATE TABLE "documents" (
"id" integer NOT NULL,
"name" character varying(100) NOT NULL,
"url_key" character varying(50) NOT NULL,
"status" boolean DEFAULT false,
"created_at" timestamp without time zone,
"show_in_nav" boolean DEFAULT false,
"user_id" integer,
"document_type_id" integer,
"view_id" integer,
"layout_id" integer,
"parent_id" integer DEFAULT 0
) WITH OIDS;
ALTER TABLE "documents" ADD CONSTRAINT "documents_pk" PRIMARY KEY("id");
CREATE UNIQUE INDEX "document_url_key_parent_idx" ON "documents" USING btree ("url_key","parent_id");

DROP TABLE IF EXISTS "icons" CASCADE;
CREATE TABLE "icons" (
"id" integer NOT NULL,
"name" character varying(20) NOT NULL,
"url" character varying(50) NOT NULL
) WITH OIDS;
ALTER TABLE "icons" ADD CONSTRAINT "icons_pk" PRIMARY KEY("id");
CREATE UNIQUE INDEX "icon_name_idx" ON "icons" USING btree ("name");
CREATE UNIQUE INDEX "icon_url_idx" ON "icons" USING btree ("url");

DROP TABLE IF EXISTS "layouts" CASCADE;
CREATE TABLE "layouts" (
"id" integer NOT NULL,
"name" character varying(50),
"identifier" character varying(50) NOT NULL,
"content" text,
"description" character varying(255),
"created_at" timestamp without time zone,
"updated_at" timestamp without time zone
) WITH OIDS;
ALTER TABLE "layouts" ADD CONSTRAINT "layouts_pk" PRIMARY KEY("id");
CREATE UNIQUE INDEX "layout_identifier_idx" ON "layouts" USING btree ("identifier");
CREATE UNIQUE INDEX "layout_name_idx" ON "layouts" USING btree ("name");

DROP TABLE IF EXISTS "models" CASCADE;
CREATE TABLE "models" (
"id" integer NOT NULL,
"name" character varying(50) NOT NULL,
"identifier" character varying(50) NOT NULL,
"description" character varying(255)
) WITH OIDS;
ALTER TABLE "models" ADD CONSTRAINT "models_pk" PRIMARY KEY("id");
CREATE UNIQUE INDEX "model_identifier_idx" ON "models" USING btree ("identifier");
CREATE UNIQUE INDEX "model_name_idx" ON "models" USING btree ("name");

DROP TABLE IF EXISTS "properties" CASCADE;
CREATE TABLE "properties" (
"id" integer NOT NULL,
"name" character varying(50),
"identifier" character varying(50) NOT NULL,
"description" character varying(255),
"required" boolean,
"order" integer,
"tab_id" integer,
"datatype_id" integer
) WITH OIDS;
ALTER TABLE "properties" ADD CONSTRAINT "properties_pk" PRIMARY KEY("id");
CREATE UNIQUE INDEX "properties_identifier_idx" ON "properties" USING btree ("identifier");

DROP TABLE IF EXISTS "properties_values" CASCADE;
CREATE TABLE "properties_values" (
"id" integer NOT NULL,
"document_id" integer,
"property_id" integer,
"value" text
) WITH OIDS;
ALTER TABLE "properties_values" ADD CONSTRAINT "properties_values_pk" PRIMARY KEY("id");
CREATE UNIQUE INDEX "properties_values_idx" ON "properties_values" USING btree ("document_id","property_id");

DROP TABLE IF EXISTS "tabs" CASCADE;
CREATE TABLE "tabs" (
"id" integer NOT NULL,
"name" character varying(50) NOT NULL,
"description" character varying(150),
"order" integer DEFAULT 0,
"document_type_id" integer NOT NULL
) WITH OIDS;
ALTER TABLE "tabs" ADD CONSTRAINT "tabs_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "users" CASCADE;
CREATE TABLE "users" (
"id" integer NOT NULL,
"lastname" character varying(30) NOT NULL,
"firstname" character varying(30) NOT NULL,
"email" character varying(50) NOT NULL,
"password" character varying(50) NOT NULL,
"created_at" timestamp without time zone NOT NULL,
"user_type_id" integer NOT NULL DEFAULT 0
) WITH OIDS;
ALTER TABLE "users" ADD CONSTRAINT "users_pk" PRIMARY KEY("id");
CREATE UNIQUE INDEX "user_email_idx" ON "users" USING btree ("email");

DROP TABLE IF EXISTS "views" CASCADE;
CREATE TABLE "views" (
"id" integer NOT NULL,
"name" character varying(50) NOT NULL,
"identifier" character varying(50),
"content" text,
"description" character varying(255),
"created_at" timestamp without time zone NOT NULL,
"updated_at" timestamp without time zone NOT NULL
) WITH OIDS;
ALTER TABLE "views" ADD CONSTRAINT "views_pk" PRIMARY KEY("id");
CREATE UNIQUE INDEX "views_identifier_idx" ON "views" USING btree ("identifier");
CREATE UNIQUE INDEX "views_name_idx" ON "views" USING btree ("name");

DROP TABLE IF EXISTS "translate" CASCADE;
CREATE TABLE "translate" (
"id" integer NOT NULL,
"source" character varying(255) NOT NULL
) WITH OIDS;
ALTER TABLE "translate" ADD CONSTRAINT "translate_pk" PRIMARY KEY("id");
CREATE UNIQUE INDEX "translate_source_idx" ON "translate" USING btree ("source");

DROP TABLE IF EXISTS "translate_language" CASCADE;
CREATE TABLE "translate_language" (
"id" integer NOT NULL,
"destination" character varying(255) NOT NULL,
"language" character varying(2) NOT NULL,
"translate_id" integer
) WITH OIDS;
ALTER TABLE "translate_language" ADD CONSTRAINT "translate_language_pk" PRIMARY KEY("id");
CREATE UNIQUE INDEX "translate_language_idx" ON "translate_language" USING btree ("destination");

-- End Table's declaration

-- Start Relation's declaration
ALTER TABLE "datatypes" DROP CONSTRAINT "datatypes_model_id" CASCADE;
ALTER TABLE "datatypes" ADD CONSTRAINT "datatypes_model_id" FOREIGN KEY ("model_id") REFERENCES "models"("id") ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE "document_types" DROP CONSTRAINT "document_types_icon_id" CASCADE;
ALTER TABLE "document_types" ADD CONSTRAINT "document_types_icon_id" FOREIGN KEY ("icon_id") REFERENCES "icons"("id") ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE "document_types" DROP CONSTRAINT "document_types_view_id" CASCADE;
ALTER TABLE "document_types" ADD CONSTRAINT "document_types_view_id" FOREIGN KEY ("default_view_id") REFERENCES "views"("id") ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE "document_type_views" DROP CONSTRAINT "document_type_views_document_types_id" CASCADE;
ALTER TABLE "document_type_views" ADD CONSTRAINT "document_type_views_document_types_id" FOREIGN KEY ("id") REFERENCES "document_types"("id") ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE "document_type_views" DROP CONSTRAINT "document_type_views_view_id" CASCADE;
ALTER TABLE "document_type_views" ADD CONSTRAINT "document_type_views_view_id" FOREIGN KEY ("view_id") REFERENCES "views"("id") ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE "documents" DROP CONSTRAINT "documents_layout_id" CASCADE;
ALTER TABLE "documents" ADD CONSTRAINT "documents_layout_id" FOREIGN KEY ("layout_id") REFERENCES "layouts"("id") ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE "documents" DROP CONSTRAINT "document_types_id" CASCADE;
ALTER TABLE "documents" ADD CONSTRAINT "document_types_id" FOREIGN KEY ("document_type_id") REFERENCES "document_types"("id") ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE "documents" DROP CONSTRAINT "documents_view_id" CASCADE;
ALTER TABLE "documents" ADD CONSTRAINT "documents_view_id" FOREIGN KEY ("view_id") REFERENCES "views"("id") ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE "properties" DROP CONSTRAINT "properties_datatype_id" CASCADE;
ALTER TABLE "properties" ADD CONSTRAINT "properties_datatype_id" FOREIGN KEY ("datatype_id") REFERENCES "datatypes"("id") ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE "properties" DROP CONSTRAINT "properties_tab_id" CASCADE;
ALTER TABLE "properties" ADD CONSTRAINT "properties_tab_id" FOREIGN KEY ("tab_id") REFERENCES "tabs"("id") ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE "properties_values" DROP CONSTRAINT "properties_values_document_id" CASCADE;
ALTER TABLE "properties_values" ADD CONSTRAINT "properties_values_document_id" FOREIGN KEY ("document_id") REFERENCES "documents"("id") ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE "properties_values" DROP CONSTRAINT "properties_values_property_id" CASCADE;
ALTER TABLE "properties_values" ADD CONSTRAINT "properties_values_property_id" FOREIGN KEY ("property_id") REFERENCES "properties"("id") ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE "tabs" DROP CONSTRAINT "tabs_document_types_id" CASCADE;
ALTER TABLE "tabs" ADD CONSTRAINT "tabs_document_types_id" FOREIGN KEY ("document_type_id") REFERENCES "document_types"("id") ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE "documents" DROP CONSTRAINT "documents_user_id" CASCADE;
ALTER TABLE "documents" ADD CONSTRAINT "documents_user_id" FOREIGN KEY ("user_id") REFERENCES "users"("id") ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE "document_types" DROP CONSTRAINT "document_types_user_id" CASCADE;
ALTER TABLE "document_types" ADD CONSTRAINT "document_types_user_id" FOREIGN KEY ("user_id") REFERENCES "users"("id") ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE "translate_language" DROP CONSTRAINT "translate_locale_id" CASCADE;
ALTER TABLE "translate_language" ADD CONSTRAINT "translate_locale_id" FOREIGN KEY ("translate_id") REFERENCES "translate"("id") ON UPDATE CASCADE ON DELETE CASCADE;

-- End Relation's declaration

