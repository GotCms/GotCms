DROP TABLE IF EXISTS "blog_comment" CASCADE;
CREATE TABLE "blog_comment" (
"id" serial NOT null,
"username" character varying(255) NOT null,
"email" character varying(255) NOT null,
"show_email" boolean DEFAULT false,
"message" text,
"document_id" integer NOT null
) WITH OIDS;
ALTER TABLE "blog_comment" ADD CONSTRAINT "blog_comment_pk" PRIMARY KEY("id");
ALTER TABLE "blog_comment" ADD CONSTRAINT "fk_blog_comment_document" FOREIGN KEY ("document_id") REFERENCES "document"("id") ON UPDATE CASCADE ON DELETE CASCADE;DROP TABLE IF EXISTS "blog_comment" CASCADE;
