DROP TABLE IF EXISTS "activity_log_template" CASCADE;
CREATE TABLE activity_log_template (
    "id" serial NOT null, -- The event id
    "event_identifier" character varying(255) NOT NULL, -- The event identifier
    "event_name" character varying(255) NOT NULL, -- The event name
    "template" text NOT NULL -- The template
) WITH OIDS;
ALTER TABLE "activity_log_template" ADD CONSTRAINT "activity_log_template_pk" PRIMARY KEY("id");

DROP TABLE IF EXISTS "activity_log_event" CASCADE;
CREATE TABLE "activity_log_event" (
    "id" serial NOT null, -- The activity id
    "created_at" timestamp without time zone NOT null, -- The created at
    "content" text, -- The content already generate
    "target_id" integer, -- The target id (document, user, model, etc)
    "template_id" integer NOT null, -- The template id
    "user_id" integer -- The user id who trigger the event
) WITH OIDS;
ALTER TABLE "activity_log_event" ADD CONSTRAINT "activity_log_event_pk" PRIMARY KEY("id");
ALTER TABLE "activity_log_event" ADD CONSTRAINT "fk_activity_log_event_user" FOREIGN KEY ("user_id") REFERENCES "user"("id") ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE "activity_log_event" ADD CONSTRAINT "fk_activity_log_event_template" FOREIGN KEY ("template_id") REFERENCES "activity_log_template"("id") ON UPDATE CASCADE ON DELETE CASCADE;


INSERT INTO activity_log_template VALUES (1, 'Gc\User\Model', 'after.save', '<?= $this->escapeHtml($this->event->getParam(''user'')->getName()) ?> has saved the user model <a href="<?= $this->url(''config/user/edit'', array(''id'' => $this->event->getParam(''object'')->getId())) ?>"><?= $this->event->getParam(''object'')->getName(); ?></a>');
INSERT INTO activity_log_template VALUES (2, 'Gc\Datatype\Model', 'after.save', '<?= $this->escapeHtml($this->event->getParam(''user'')->getName()) ?> has saved the datatype model <a href="<?= $this->url(''development/datatype/edit'', array(''id'' => $this->event->getParam(''object'')->getId())) ?>"><?= $this->event->getParam(''object'')->getName(); ?></a>');
INSERT INTO activity_log_template VALUES (3, 'Gc\Datatype\Model', 'after.delete', '<?= $this->escapeHtml($this->event->getParam(''user'')->getName()) ?> has deleted the datatype model <strong>"<?= $this->escapeHtml($this->event->getParam(''object'')->getName()); ?>"</strong>');
INSERT INTO activity_log_template VALUES (4, 'Gc\Document\Model', 'after.delete', '<?= $this->escapeHtml($this->event->getParam(''user'')->getName()) ?> has deleted the document model <strong>""<?= $this->escapeHtml($this->event->getParam(''object'')->getName()); ?>"</strong>');
INSERT INTO activity_log_template VALUES (5, 'Gc\Document\Model', 'after.save', '<?= $this->escapeHtml($this->event->getParam(''user'')->getName()) ?> has saved the document model <a href="<?= $this->url(''content/document/edit'', array(''id'' => $this->event->getParam(''object'')->getId())) ?>"><?= $this->event->getParam(''object'')->getName(); ?></a>');
INSERT INTO activity_log_template VALUES (6, 'Gc\DocumentType\Model', 'after.save', '<?= $this->escapeHtml($this->event->getParam(''user'')->getName()) ?> has saved the document type model <a href="<?= $this->url(''development/document-type/edit'', array(''id'' => $this->event->getParam(''object'')->getId())) ?>"><?= $this->event->getParam(''object'')->getName(); ?></a>');
INSERT INTO activity_log_template VALUES (8, 'Gc\Layout\Model', 'after.delete', '<?= $this->escapeHtml($this->event->getParam(''user'')->getName()) ?> has deleted the layout model <strong>"<?= $this->escapeHtml($this->event->getParam(''object'')->getName()); ?>"</strong>');
INSERT INTO activity_log_template VALUES (7, 'Gc\DocumentType\Model', 'after.delete', '<?= $this->escapeHtml($this->event->getParam(''user'')->getName()) ?> has deleted the document type model <strong>"<?= $this->escapeHtml($this->event->getParam(''object'')->getName()); ?>"</strong>');
INSERT INTO activity_log_template VALUES (9, 'Gc\View\Model', 'after.delete', '<?= $this->escapeHtml($this->event->getParam(''user'')->getName()) ?> has deleted the view model <strong>"<?= $this->escapeHtml($this->event->getParam(''object'')->getName()); ?>"</strong>');
INSERT INTO activity_log_template VALUES (10, 'Gc\Script\Model', 'after.delete', '<?= $this->escapeHtml($this->event->getParam(''user'')->getName()) ?> has deleted the script model <strong>"<?= $this->escapeHtml($this->event->getParam(''object'')->getName()); ?>"</strong>');
INSERT INTO activity_log_template VALUES (11, 'Gc\View\Model', 'after.save', '<?= $this->escapeHtml($this->event->getParam(''user'')->getName()) ?> has saved the view model <a href="<?= $this->url(''development/view/edit'', array(''id'' => $this->event->getParam(''object'')->getId())) ?>"><?= $this->event->getParam(''object'')->getName(); ?></a>');
INSERT INTO activity_log_template VALUES (12, 'Gc\Script\Model', 'after.save', '<?= $this->escapeHtml($this->event->getParam(''user'')->getName()) ?> has saved the script model <a href="<?= $this->url(''development/script/edit'', array(''id'' => $this->event->getParam(''object'')->getId())) ?>"><?= $this->event->getParam(''object'')->getName(); ?></a>');
INSERT INTO activity_log_template VALUES (13, 'Gc\Layout\Model', 'after.save', '<?= $this->escapeHtml($this->event->getParam(''user'')->getName()) ?> has saved the layout model <a href="<?= $this->url(''development/layout/edit'', array(''id'' => $this->event->getParam(''object'')->getId())) ?>"><?= $this->event->getParam(''object'')->getName(); ?></a>');
INSERT INTO activity_log_template VALUES (14, 'Gc\User\Model', 'after.delete', '<?= $this->escapeHtml($this->event->getParam(''user'')->getName()) ?> has deleted the user model <strong>"<?= $this->escapeHtml($this->event->getParam(''object'')->getName()); ?>"</strong>');
INSERT INTO activity_log_template VALUES (15, 'Gc\User\Model', 'after.auth', '<?= $this->escapeHtml($this->event->getParam(''object'')->getName()) ?> is now connected with the ip address: <strong><?php $remote = new \Zend\Http\PhpEnvironment\RemoteAddress; echo $this->escapeHtml($remote->getIpAddress()); ?></strong>');
INSERT INTO activity_log_template VALUES (16, 'Gc\User\Model', 'after.auth.failed', '<?php $remote = new \Zend\Http\PhpEnvironment\RemoteAddress; echo $this->escapeHtml($remote->getIpAddress()); ?> tried to connect with <strong>"<?= $this->escapeHtml($this->event->getParam(''login'')); ?>"</strong>');
