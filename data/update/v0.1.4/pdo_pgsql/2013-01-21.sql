ALTER TABLE "user"
ADD COLUMN retrieve_password_key character varying(40) DEFAULT NULL,
ADD COLUMN retrieve_updated_at timestamp without time zone DEFAULT NULL;
