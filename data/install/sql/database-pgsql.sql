-- ----------------------------
-- pgDesigner 1.2.17
--
-- Project    : GotCms
-- Description: 
-- ----------------------------


-- Start Table's declaration
DROP TABLE IF EXISTS "datatype" CASCADE;
CREATE TABLE "datatype" (
"id" serial NOT NULL,
"name" character varying NOT NULL,
"prevalue_value" text,
"model" character varying NOT NULL
) WITH OIDS;
ALTER TABLE "datatype" ADD CONSTRAINT "datatype_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "document_type_view" CASCADE;
CREATE TABLE "document_type_view" (
"id" serial NOT NULL,
"view_id" integer NOT NULL,
"document_type_id" integer NOT NULL
) WITH OIDS;
ALTER TABLE "document_type_view" ADD CONSTRAINT "document_type_view_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "document_type_dependency" CASCADE;
CREATE TABLE "document_type_dependency" (
"id" serial NOT NULL,
"parent_id" integer NOT NULL,
"children_id" integer NOT NULL
) WITH OIDS;
ALTER TABLE "document_type_dependency" ADD CONSTRAINT "document_type_dependency_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "document_type" CASCADE;
CREATE TABLE "document_type" (
"id" serial NOT NULL,
"created_at" timestamp without time zone NOT NULL,
"updated_at" timestamp without time zone NOT NULL,
"name" character varying NOT NULL,
"description" text,
"icon_id" integer,
"default_view_id" integer,
"user_id" integer NOT NULL
) WITH OIDS;
ALTER TABLE "document_type" ADD CONSTRAINT "document_type_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "document" CASCADE;
CREATE TABLE "document" (
"id" serial NOT NULL,
"created_at" timestamp without time zone NOT NULL,
"updated_at" timestamp without time zone NOT NULL,
"name" character varying NOT NULL,
"url_key" character varying NOT NULL,
"status" integer NOT NULL DEFAULT 0,
"show_in_nav" boolean DEFAULT false,
"user_id" integer NOT NULL,
"document_type_id" integer,
"view_id" integer,
"layout_id" integer,
"parent_id" integer DEFAULT 0
) WITH OIDS;
ALTER TABLE "document" ADD CONSTRAINT "document_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "icon" CASCADE;
CREATE TABLE "icon" (
"id" serial NOT NULL,
"name" character varying NOT NULL,
"url" character varying NOT NULL
) WITH OIDS;
ALTER TABLE "icon" ADD CONSTRAINT "icon_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "layout" CASCADE;
CREATE TABLE "layout" (
"id" serial NOT NULL,
"created_at" timestamp without time zone NOT NULL,
"updated_at" timestamp without time zone NOT NULL,
"name" character varying NOT NULL,
"identifier" character varying NOT NULL,
"content" text,
"description" character varying
) WITH OIDS;
ALTER TABLE "layout" ADD CONSTRAINT "layout_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "property" CASCADE;
CREATE TABLE "property" (
"id" serial NOT NULL,
"name" character varying,
"identifier" character varying NOT NULL,
"description" character varying,
"required" boolean NOT NULL DEFAULT false,
"order" integer DEFAULT 0,
"tab_id" integer NOT NULL,
"datatype_id" integer NOT NULL
) WITH OIDS;
ALTER TABLE "property" ADD CONSTRAINT "property_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "property_value" CASCADE;
CREATE TABLE "property_value" (
"id" serial NOT NULL,
"document_id" integer NOT NULL,
"property_id" integer NOT NULL,
"value" text
) WITH OIDS;
ALTER TABLE "property_value" ADD CONSTRAINT "property_value_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "tab" CASCADE;
CREATE TABLE "tab" (
"id" serial NOT NULL,
"name" character varying NOT NULL,
"description" character varying,
"order" integer DEFAULT 0,
"document_type_id" integer NOT NULL
) WITH OIDS;
ALTER TABLE "tab" ADD CONSTRAINT "tab_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "core_translate" CASCADE;
CREATE TABLE "core_translate" (
"id" serial NOT NULL,
"source" character varying NOT NULL
) WITH OIDS;
ALTER TABLE "core_translate" ADD CONSTRAINT "core_translate_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "core_translate_locale" CASCADE;
CREATE TABLE "core_translate_locale" (
"id" serial NOT NULL,
"destination" character varying NOT NULL,
"locale" character varying NOT NULL,
"core_translate_id" integer NOT NULL
) WITH OIDS;
ALTER TABLE "core_translate_locale" ADD CONSTRAINT "core_translate_locale_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "user_acl_role" CASCADE;
CREATE TABLE "user_acl_role" (
"id" serial NOT NULL,
"name" character varying,
"description" character varying
) WITH OIDS;
ALTER TABLE "user_acl_role" ADD CONSTRAINT "user_acl_role_pk" PRIMARY KEY("id");
CREATE UNIQUE INDEX "user_acl_roles_name_key" ON "user_acl_role" USING btree ("name");

DROP TABLE IF EXISTS "user_acl" CASCADE;
CREATE TABLE "user_acl" (
"id" serial NOT NULL,
"user_acl_permission_id" integer NOT NULL,
"user_acl_role_id" integer NOT NULL
) WITH OIDS;
ALTER TABLE "user_acl" ADD CONSTRAINT "user_acl_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "user_acl_resource" CASCADE;
CREATE TABLE "user_acl_resource" (
"id" serial NOT NULL,
"resource" character varying
) WITH OIDS;
ALTER TABLE "user_acl_resource" ADD CONSTRAINT "user_acl_resource_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "user" CASCADE;
CREATE TABLE "user" (
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
ALTER TABLE "user" ADD CONSTRAINT "user_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "view" CASCADE;
CREATE TABLE "view" (
"id" serial NOT NULL,
"created_at" timestamp without time zone NOT NULL,
"updated_at" timestamp without time zone NOT NULL,
"name" character varying NOT NULL,
"identifier" character varying,
"content" text,
"description" character varying
) WITH OIDS;
ALTER TABLE "view" ADD CONSTRAINT "view_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "script" CASCADE;
CREATE TABLE "script" (
"id" serial NOT NULL,
"created_at" timestamp without time zone NOT NULL,
"updated_at" timestamp without time zone NOT NULL,
"name" character varying NOT NULL,
"identifier" character varying,
"content" text,
"description" character varying
) WITH OIDS;
ALTER TABLE "script" ADD CONSTRAINT "script_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "user_acl_permission" CASCADE;
CREATE TABLE "user_acl_permission" (
"id" serial NOT NULL,
"permission" character varying NOT NULL,
"user_acl_resource_id" integer NOT NULL
) WITH OIDS;
ALTER TABLE "user_acl_permission" ADD CONSTRAINT "user_acl_permission_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "core_config_data" CASCADE;
CREATE TABLE "core_config_data" (
"id" serial NOT NULL,
"identifier" character varying NOT NULL,
"value" character varying
) WITH OIDS;
ALTER TABLE "core_config_data" ADD CONSTRAINT "core_config_data_pk" PRIMARY KEY("id");
CREATE UNIQUE INDEX "core_config_data_id" ON "core_config_data" USING btree ("identifier");

DROP TABLE IF EXISTS "core_session" CASCADE;
CREATE TABLE "core_session" (
"id" character varying NOT NULL,
"name" character varying(50) NOT NULL,
"lifetime" integer NOT NULL,
"updated_at" integer NOT NULL,
"data" text
) WITH OIDS;
ALTER TABLE "core_session" ADD CONSTRAINT "core_session_pk" PRIMARY KEY("id");

-- End Table's declaration

-- Start Relation's declaration
ALTER TABLE "document_type_view" ADD CONSTRAINT "fk_document_type_views_views" FOREIGN KEY ("view_id") REFERENCES "view"("id") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE "document_type_dependency" ADD CONSTRAINT "fk_document_type_dependency_parent_id" FOREIGN KEY ("parent_id") REFERENCES "document_type"("id") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE "document_type_dependency" ADD CONSTRAINT "fk_document_type_dependency_children_id" FOREIGN KEY ("children_id") REFERENCES "document_type"("id") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE "property" ADD CONSTRAINT "fk_property_datatype" FOREIGN KEY ("datatype_id") REFERENCES "datatype"("id") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE "property" ADD CONSTRAINT "fk_property_tab" FOREIGN KEY ("tab_id") REFERENCES "tab"("id") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE "document" ADD CONSTRAINT "fk_document_layout" FOREIGN KEY ("layout_id") REFERENCES "layout"("id") ON UPDATE SET NULL ON DELETE SET NULL;

ALTER TABLE "document" ADD CONSTRAINT "fk_document_document" FOREIGN KEY ("parent_id") REFERENCES "document"("id") ON UPDATE SET NULL ON DELETE SET NULL;

ALTER TABLE "document" ADD CONSTRAINT "fk_documents_view" FOREIGN KEY ("view_id") REFERENCES "view"("id") ON UPDATE SET NULL ON DELETE SET NULL;

ALTER TABLE "document" ADD CONSTRAINT "fk_document_document_type" FOREIGN KEY ("document_type_id") REFERENCES "document_type"("id") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE "document_type" ADD CONSTRAINT "fk_document_type_view" FOREIGN KEY ("default_view_id") REFERENCES "view"("id") ON UPDATE SET NULL ON DELETE SET NULL;

ALTER TABLE "document_type" ADD CONSTRAINT "fk_document_type_icon" FOREIGN KEY ("icon_id") REFERENCES "icon"("id") ON UPDATE SET NULL ON DELETE SET NULL;

ALTER TABLE "document" ADD CONSTRAINT "fk_document_user" FOREIGN KEY ("user_id") REFERENCES "user"("id") ON UPDATE SET NULL ON DELETE SET NULL;

ALTER TABLE "user_acl" ADD CONSTRAINT "fk_user_acl_permission_user_acl_role" FOREIGN KEY ("user_acl_role_id") REFERENCES "user_acl_role"("id") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE "user" ADD CONSTRAINT "fk_user_user_acl_role" FOREIGN KEY ("user_acl_role_id") REFERENCES "user_acl_role"("id") ON UPDATE SET NULL ON DELETE SET NULL;

ALTER TABLE "core_translate_locale" ADD CONSTRAINT "fk_core_translate_locale_core_translate" FOREIGN KEY ("core_translate_id") REFERENCES "core_translate"("id") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE "user_acl" ADD CONSTRAINT "fk_user_acl_user_acl_permission" FOREIGN KEY ("user_acl_permission_id") REFERENCES "user_acl_permission"("id") ON UPDATE SET NULL ON DELETE SET NULL;

ALTER TABLE "document_type_view" ADD CONSTRAINT "fk_document_type_view_document_type" FOREIGN KEY ("document_type_id") REFERENCES "document_type"("id") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE "user_acl_permission" ADD CONSTRAINT "fk_user_acl_permission_user_acl_resource" FOREIGN KEY ("user_acl_resource_id") REFERENCES "user_acl_resource"("id") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE "property_value" ADD CONSTRAINT "fk_property_value_document" FOREIGN KEY ("document_id") REFERENCES "document"("id") ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE "property_value" ADD CONSTRAINT "fk_property_value_property" FOREIGN KEY ("property_id") REFERENCES "property"("id") ON UPDATE CASCADE ON DELETE CASCADE;

-- End Relation's declaration

