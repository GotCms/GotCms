--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;


--
-- Data for Name: view; Type: TABLE DATA; Schema: public; Owner: got
--

INSERT INTO view VALUES (6, '2012-10-25 19:57:05', '2013-05-26 13:26:11.897632', 'Flash messages', 'flash-messages', '<?php if(!empty($this->layout()->flashMessages)): ?>
    <?php foreach($this->layout()->flashMessages as $type => $messages):?>
        <?php foreach($messages as $message): ?>
            <div class="notification <?php echo $type; ?>">
             <?php echo $this->escapeHtml($this->translate($message)); ?>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
<?php endif; ?>', 'Flash messages displayer');
INSERT INTO view VALUES (9, '2013-05-22 19:32:25.295636', '2013-05-26 13:26:11.910965', 'Footer', 'footer', '<div id="footer-wrapper">
    <footer class="container" id="site-footer">
        <div class="row">
            <div class="4u">
                <section class="first">
                    <h2>Ipsum et phasellus</h2>
                    <ul class="link-list">
                        <li><a href="#">Mattis et quis rutrum sed accumsan</a>
                        <li><a href="#">Suspendisse amet varius nibh</a>
                        <li><a href="#">Suspenddapibus amet mattis quis</a>
                        <li><a href="#">Rutrum accumsan eu varius</a>
                        <li><a href="#">Nibh lorem sed dolore et ipsum.</a>
                    </ul>
                </section>
            </div>
            <div class="4u">
                <section>
                    <h2>Lorem mattis dolor</h2>
                    <ul class="link-list">
                        <li><a href="#">Duis neque nisi dapibus sed</a>
                        <li><a href="#">Suspenddapibus amet mattis quis</a>
                        <li><a href="#">Rutrum accumsan eu varius</a>
                        <li><a href="#">Nibh lorem sed dolore et ipsum.</a>
                        <li><a href="#">Mattis et quis rutrum sed accumsan</a>
                    </ul>
                </section>
            </div>
            <div class="4u">
                <section>
                    <h2>Mattis quis tempus</h2>
                    <ul class="link-list">
                        <li><a href="#">Suspendisse amet varius nibh</a>
                        <li><a href="#">Suspenddapibus amet mattis quis</a>
                        <li><a href="#">Rutrum accumsan eu varius</a>
                        <li><a href="#">Nibh lorem sed dolore et ipsum.</a>
                        <li><a href="#">Duis neque nisi dapibus sed</a>
                    </ul>
                </section>
            </div>
        </div>
        <div class="row">
            <div class="12u">
                <div class="divider"></div>
            </div>
        </div>
        <div class="row">
            <div class="12u">
                <div id="copyright">
                    &copy; <?php echo $this->escapeHtml($this->config()->get(''site_name'')); ?>. All rights reserved. | Design: <a href="http://html5up.net">HTML5 UP</a> | Images: <a href="http://fotogrph.com">fotogrph</a>
                </div>
            </div>
        </div>
    </footer>
</div>
', 'Footer navigation');
INSERT INTO view VALUES (8, '2013-05-22 19:29:56.943005', '2013-05-26 13:26:11.922496', 'Header', 'header', '<nav id="nav">
    <?php
        $component = new \Gc\Component\Navigation();
        $container = new \Zend\Navigation\Navigation($component->render());
        $this->navigation($container);
        $document = $this->layout()->currentDocument;

        echo $this->navigation()->menu()->setMaxDepth(0)->setUlClass(''sf-menu navigation'');
    ?>
</nav>
', 'Header');
INSERT INTO view VALUES (4, '2012-09-19 19:33:51', '2013-05-26 13:26:11.955889', 'One column', 'one-column', '<div id="main-wrapper">
    <div class="container">
        <div class="row">
            <div class="12u skel-cell-mainContent">
                <article class="first last">
                    <h2><?php echo $this->title; ?></h2>
                    <?php echo $this->content; ?>
                </article>
            </div>
        </div>
    </div>
</div>
', 'One column page');
INSERT INTO view VALUES (7, '2013-05-20 15:52:38.647561', '2013-05-26 13:26:11.972645', 'Paginator', 'paginator', '<?php if ($this->pageCount): ?>
    <p class="clearfix">
        <?php if (isset($this->next)): ?>
            <a class="button float" href="<?php echo $this->escapeHtml($this->path); ?>?page=<?php echo $this->next; ?>">
                &lt;&lt; Previous Posts
            </a>
        <?php endif; ?>

        <?php if (isset($this->previous)): ?>
            <a class="button float right" href="<?php echo $this->escapeHtml($this->path); ?>?page=<?php echo $this->previous; ?>">
                Newer Posts &gt;&gt;
            </a>
        <?php endif; ?>
    </p>
<?php endif; ?>
', 'Paginator control');
INSERT INTO view VALUES (3, '2012-09-19 19:32:56', '2013-05-26 13:26:11.884399', 'Contact', 'contact', '<div id="main-wrapper">
    <div class="container">
        <div class="row">
            <div class="12u skel-cell-mainContent">
                <article class="first last">
                    <h2>Contact</h2>
                    <?php echo $this->partial(''flash-messages''); ?>
                    <?php
                        $return = $this->script(''contact'');
                        $number_1 = mt_rand(1, 9);
                        $number_2 = mt_rand(1, 9);
                        $answer = substr(sha1($number_1+$number_2),5,10);
                    ?>

                    <form id="contact" action="<?php echo $this->escapeHtml($this->document(''contact'')->getUrl()); ?>" method="post">
                        <?php if(!empty($return[''error_message''])): ?>
                            <div class="notification error"><span><?php echo $this->escapeHtml($return[''error_message'']); ?><span></div>
                        <?php endif; ?>

                        <div class="form_settings">
                            <div>
                                <label>
                                    <span>Name</span>
                                    <input class="input-text" type="text" name="name" value="<?php echo $this->escapeHtml(!empty($return[''name'']) ? $return[''name''] : ''''); ?>">
                                </label>
                            </div>
                            <div>
                                <label>
                                    <span>Email Address</span>
                                    <input class="input-text" type="text" name="email" value="<?php echo $this->escapeHtml(!empty($return[''email'']) ? $return[''email''] : ''''); ?>">
                                </label>
                            </div>
                            <div>
                                <label>
                                    <span>Message</span>
                                    <textarea class="input-text" rows="5" cols="50" name="message"><?php echo $this->escapeHtml(!empty($return[''message'']) ? $return[''message''] : ''''); ?></textarea>
                                </label>
                            </div>
                            <div>
                                <p style="line-height: 1.7em;">
                                    To help prevent spam, please enter the answer to this question:
                                    <span><?php echo $number_1; ?> + <?php echo $number_2; ?> = ?</span>
                                    <input type="text" name="answer" class="input-text"><input type="hidden" name="answer_hash" value="<?php echo $answer; ?>">
                                </p>
                            </div>
                            <div>
                                <p style="padding-top: 15px"><span>&nbsp;</span><input class="button" type="submit" name="contact_submitted" value="send"></label>
                            </div>
                        </div>
                    </form>
                </article>
            </div>
        </div>
    </div>
</div>
', 'Contact form');
INSERT INTO view VALUES (2, '2012-09-19 19:29:50', '2013-05-26 13:26:11.840543', 'Blog', 'blog', '<div class="subpage" id="main-wrapper">
    <div class="container">
        <div class="row">
            <?php echo $this->partial(''blog-categories''); ?>
            <div class="9u skel-cell-mainContent">
                <?php
                $comment_table = new \Blog\Model\Comment();

                $posts = $this->currentDocument->getAvailableChildren();
                foreach($posts as $child)
                {
                    $children = $child->getChildren();
                    if(!empty($children))
                    {
                        foreach($children as $child)
                        {
                            $posts[] = $child;
                        }
                    }
                }

                foreach($posts as $idx => $child)
                {
                    if($child->getView()->getIdentifier() != ''blog-ticket'')
                    {
                        unset($posts[$idx]);
                    }
                }

                function sortObjects($a, $b)
                {
                    if($a->getCreatedAt() == $b->getCreatedAt())
                    {
                        return 0;
                    }

                    return ($a->getCreatedAt() > $b->getCreatedAt()) ? -1 : 1;
                }

                uasort($posts, ''sortObjects'');

                $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($posts));
                $paginator->setItemCountPerPage(5);
                $paginator->setCurrentPageNumber(empty($_GET[''page'']) ? 1 : (int)$_GET[''page'']);
                ?>

                <?php if(!empty($posts)): ?>
                    <?php foreach($paginator as $post): ?>
                        <article class="first">
                            <div>
                                <h2><a href="<?php echo $this->escapeHtml($post->getUrl()); ?>"><?php echo $this->escapeHtml($post->getProperty(''title'')->getValue()); ?></a></h2>
                                <?php $parent = $post->getParent(); ?>
                                <p class="sub"><a href="<?php echo $this->escapeHtml($parent->getUrl()); ?>"><?php echo $this->escapeHtml($parent->getName()); ?></a> &bull; <?php echo $this->escapeHtml(date(''Y-m-d'', strtotime($post->getProperty(''published_at'')->getValue()))); ?>
                                    <?php $comments = $comment_table->getList($post->getId()); ?>
                                    <?php $nb_comments = count($comments); ?>
                                    <?php if(!empty($nb_comments)): ?>
                                         &bull; <a href="<?php echo $this->escapeHtml($post->getUrl()); ?>#comments_list">
                                            <?php if($nb_comments == 1): ?>
                                                1 Comment
                                            <?php else: ?>
                                                <?php echo $nb_comments; ?> Comments
                                            <?php endif; ?>
                                        </a>
                                    <?php endif; ?>
                                </p>

                                <?php $image = $this->tools(''unserialize'', $post->getProperty(''image'')->getValue()); ?>
                                <?php if(!empty($image)): ?>
                                    <img class="thumb" src="<?php echo $this->escapeHtml($image[''570x150''][''value'']); ?>" alt="" width="570" height="150">
                                <?php endif; ?>

                                 <?php echo $post->getProperty(''shortContent'')->getValue(); ?>

                                <p class="clearfix"><a href="<?php echo $this->escapeHtml($post->getUrl()); ?>" class="button right">Read More...</a></p>
                            </div>
                        </article>
                    <?php endforeach; ?>

                <?php endif; ?>
                <?php echo $this->paginationControl($paginator, ''sliding'', ''paginator'', array(''path'' => $this->currentDocument->getUrl()));?>
            </div>
        </div>
    </div>
</div>

', 'Blog');
INSERT INTO view VALUES (10, '2013-05-24 19:02:21.732603', '2013-05-26 13:26:11.862514', 'Blog categories', 'blog-categories', '<div class="3u">
    <section>
        <h3><?php echo $this->escapeHtml($this->translate(''Categories'')); ?></h3>
        <ul class="link-list">
            <?php foreach($this->document(''blog'')->getAvailableChildren() as $child): ?>
                <li><a href="<?php echo $this->escapeHtml($child->getUrl()); ?>"><?php echo $this->escapeHtml($child->getName()); ?></a></li>
            <?php endforeach; ?>
        </ul>
    </section>
</div>
', 'List all categories');
INSERT INTO view VALUES (11, '2013-05-24 19:02:58.584746', '2013-05-26 13:26:11.869185', 'Blog ticket', 'blog-ticket', '<div class="subpage" id="main-wrapper">
    <div class="container">
        <div class="row">
            <?php echo $this->partial(''blog-categories''); ?>
            <div class="9u skel-cell-mainContent">
                <?php
                $return = $this->script(''blog-comment'');
                if ($return === true) {
                    return;
                }

                $comment_table = new \Blog\Model\Comment();
                $comments = $comment_table->getList($this->currentDocument->getId());
                $nb_comments = count($comments);
                ?>

                <?php echo $this->partial(''flash-messages''); ?>
                    <article class="first">
                        <div>
                            <h2 class="title"><?php echo $this->escapeHtml($this->title); ?></h2>
                            <?php $parent = $this->currentDocument->getParent(); ?>
                            <p class="sub">
                                <a href="<?php echo $this->escapeHtml($parent->getUrl()); ?>"><?php echo $this->escapeHtml($parent->getName()); ?></a> &bull; <?php echo $this->escapeHtml(date(''Y-m-d'', strtotime($this->published_at))); ?>
                                <?php if(!empty($nb_comments)): ?>
                                    &bull; <a href="#comments_list">
                                        <?php if($nb_comments == 1): ?>
                                            1 Comment
                                        <?php else: ?>
                                            <?php echo $nb_comments; ?> Comments
                                        <?php endif; ?>
                                    </a>
                                <?php endif; ?>
                            </p>
                            <div class="hr dotted clearfix">&nbsp;</div>
                            <?php echo $this->content; ?>
                            <p class="clearfix">
                                <a href="<?php echo $this->escapeHtml($this->document(''blog'')->getUrl()); ?>" class="button float">&lt;&lt; Back to Blog</a>
                                <a href="#comment_form" class="button float right">Discuss this post</a>
                            </p>
                        </div>
                    </article>
                <div class="hr clearfix">&nbsp;</div>

                <?php echo $this->modulePlugin(''Blog'', ''comment-list''); ?>

                <div class="hr clearfix">&nbsp;</div>

                <?php echo $this->modulePlugin(''Blog'', ''comment-form''); ?>
            </div>
        </div>
    </div>
</div>
', 'Simple blog ticket');
INSERT INTO view VALUES (1, '2012-09-19 19:29:04', '2013-05-26 13:26:11.939612', 'Home page', 'home', '<div id="main-wrapper">
    <div class="container">
        <div class="row">
            <div class="12u">
                <div id="banner">
                    <a href="#"><img src="<?php echo $this->cdn($this->banner[''value'']); ?>" alt="" /></a>
                    <div class="caption">
                        <span><?php echo $this->escapeHtml($this->translate($this->bannerTitle)); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="3u">
                <section class="first">
                    <h2><?php echo $this->escapeHtml($this->translate($this->blockTitle1)); ?></h2>
                    <?php echo $this->translate($this->blockContent1); ?>
                </section>
            </div>
            <div class="3u">
                <section>
                    <h2><?php echo $this->escapeHtml($this->translate($this->blockTitle2)); ?></h2>
                    <?php echo $this->translate($this->blockContent2); ?>
                </section>
            </div>
            <div class="3u">
                <section>
                    <h2><?php echo $this->escapeHtml($this->translate($this->blockTitle3)); ?></h2>
                    <?php echo $this->translate($this->blockContent3); ?>
                </section>
            </div>
            <div class="3u">
                <section class="last">
                    <h2><?php echo $this->escapeHtml($this->translate($this->blockTitle4)); ?></h2>
                    <?php echo $this->translate($this->blockContent4); ?>
                </section>
            </div>
        </div>
    </div>
</div>
', 'Home page content');


--
-- Data for Name: document_type; Type: TABLE DATA; Schema: public; Owner: got
--

INSERT INTO document_type VALUES (2, '2012-09-20 22:05:53', '2012-10-08 20:44:24', 'Contact', 'Contact form', 11, 3, 1);
INSERT INTO document_type VALUES (3, '2012-09-20 22:06:37', '2013-05-22 21:08:15.543148', 'About', 'About this website', 13, 4, 1);
INSERT INTO document_type VALUES (6, '2013-05-24 08:57:15.489668', '2013-05-24 19:01:16.968222', 'Blog', 'Blog', 24, 2, 1);
INSERT INTO document_type VALUES (4, '2013-05-24 08:49:53.537851', '2013-05-24 19:03:37.181713', 'Blog ticket', 'Ticket blog', 6, 11, 1);
INSERT INTO document_type VALUES (5, '2013-05-24 08:56:17.202506', '2013-05-25 14:13:02.199039', 'Blog category', 'Blog category', 8, 2, 1);
INSERT INTO document_type VALUES (1, '2012-09-20 22:01:55', '2013-05-26 13:29:33.312101', 'Home', 'Home page', 1, 1, 1);


--
-- Data for Name: layout; Type: TABLE DATA; Schema: public; Owner: got
--

INSERT INTO layout VALUES (1, '2012-09-19 19:28:34', '2013-05-24 08:24:49.350913', 'Main', 'main', '<!DOCTYPE html>
<!--
    Arcana 2.0 by HTML5 UP
    html5up.net | @n33co
    Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
-->
<html>
    <head>
        <title><?php echo $this->escapeHtml($this->pageTitle); ?></title>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <meta name="description" content="<?php echo $this->escapeHtml($this->metaDescription); ?>" />
        <meta name="keywords" content="<?php echo $this->escapeHtml($this->metaKeywords); ?>" />
        <link href="https://fonts.googleapis.com/css?family=Open+Sans+Condensed:300,300italic,700" rel="stylesheet" />
        <script src="<?php echo $this->cdn(''/frontend/js/jquery-1.9.1.min.js''); ?>"></script>
        <script src="<?php echo $this->cdn(''/frontend/js/config.js''); ?>"></script>
        <script src="<?php echo $this->cdn(''/frontend/js/skel.min.js''); ?>"></script>
        <script src="<?php echo $this->cdn(''/frontend/js/skel-ui.min.js''); ?>"></script>
        <noscript>
            <link rel="stylesheet" src="<?php echo $this->cdn(''/frontend/css/skel-noscript.css''); ?>"/>
            <link rel="stylesheet" src="<?php echo $this->cdn(''/frontend/css/style.css''); ?>"/>
            <link rel="stylesheet" src="<?php echo $this->cdn(''/frontend/css/style-desktop.css''); ?>"/>
        </noscript>
        <!--[if lte IE 9]><link rel="stylesheet" src="<?php echo $this->cdn(''/frontend/css/style-ie9.css''); ?>"/><![endif]-->
        <!--[if lte IE 8]><script src="<?php echo $this->cdn(''/frontend/js/html5shiv.js''); ?>"></script><![endif]-->
    </head>
    <body>
        <div id="header-wrapper">
            <header class="container" id="site-header">
                <div class="row">
                    <div class="12u">
                        <div id="logo">
                            <h1><?php echo $this->escapeHtml($this->config()->get(''site_name'')); ?></h1>
                        </div>
                        <?php echo $this->partial(''header''); ?>
                    </div>
                </div>
            </header>
        </div>

        <?php echo $this->content; ?>

        <?php echo $this->partial(''footer''); ?>
    </body>
</html>
', 'Main layout');

--
-- Data for Name: document; Type: TABLE DATA; Schema: public; Owner: got
--

INSERT INTO document VALUES (2, '2012-09-20 22:09:06', '2013-05-25 13:58:10.956633', 'About', 'about', 1, 2, true, 1, 3, 4, 1, NULL);
INSERT INTO document VALUES (3, '2012-09-20 22:09:29', '2013-05-25 13:58:10.973436', 'Contact', 'contact', 1, 3, true, 1, 2, 3, 1, NULL);
INSERT INTO document VALUES (1, '2012-09-20 22:06:53', '2013-05-26 13:28:51.590892', 'Home', '', 1, 0, true, 1, 1, 1, 1, NULL);
INSERT INTO document VALUES (4, '2013-05-25 13:57:47.516331', '2013-05-26 13:31:30.638063', 'Blog', 'blog', 1, 1, true, 1, 6, 2, 1, NULL);
INSERT INTO document VALUES (5, '2013-05-25 13:58:49.09857', '2013-05-26 13:31:50.957512', 'First category', 'first-category', 1, 0, true, 1, 5, 2, 1, 4);
INSERT INTO document VALUES (6, '2013-05-25 13:59:05.99698', '2013-05-26 13:32:01.369958', 'Second category', 'second-category', 1, 0, true, 1, 5, 2, 1, 4);
INSERT INTO document VALUES (7, '2013-05-25 14:00:54.412118', '2013-05-26 13:32:18.374819', 'Article 1', 'article-about-something', 1, 0, false, 1, 4, 11, 1, 5);
INSERT INTO document VALUES (8, '2013-05-25 14:01:26.09229', '2013-05-26 13:32:27.671487', 'Article 2', 'article-about-something', 1, 0, false, 1, 4, 11, 1, 6);

--
-- Data for Name: datatype; Type: TABLE DATA; Schema: public; Owner: got
--

INSERT INTO datatype VALUES (1, 'Text field', 'N;', 'Textstring');
INSERT INTO datatype VALUES (2, 'Rich text', 'a:1:{s:13:"toolbar-items";a:67:{s:6:"Source";s:1:"1";s:4:"Save";s:1:"1";s:7:"NewPage";s:1:"1";s:8:"DocProps";s:1:"1";s:7:"Preview";s:1:"1";s:5:"Print";s:1:"1";s:9:"Templates";s:1:"1";s:3:"Cut";s:1:"1";s:4:"Copy";s:1:"1";s:5:"Paste";s:1:"1";s:9:"PasteText";s:1:"1";s:13:"PasteFromWord";s:1:"1";s:4:"Undo";s:1:"1";s:4:"Redo";s:1:"1";s:4:"Find";s:1:"1";s:7:"Replace";s:1:"1";s:9:"SelectAll";s:1:"1";s:12:"SpellChecker";s:1:"1";s:5:"Scayt";s:1:"1";s:4:"Form";s:1:"1";s:8:"Checkbox";s:1:"1";s:5:"Radio";s:1:"1";s:9:"TextField";s:1:"1";s:8:"Textarea";s:1:"1";s:6:"Select";s:1:"1";s:6:"Button";s:1:"1";s:11:"ImageButton";s:1:"1";s:11:"HiddenField";s:1:"1";s:4:"Bold";s:1:"1";s:6:"Italic";s:1:"1";s:9:"Underline";s:1:"1";s:6:"Strike";s:1:"1";s:9:"Subscript";s:1:"1";s:11:"Superscript";s:1:"1";s:12:"RemoveFormat";s:1:"1";s:12:"NumberedList";s:1:"1";s:12:"BulletedList";s:1:"1";s:7:"Outdent";s:1:"1";s:6:"Indent";s:1:"1";s:10:"Blockquote";s:1:"1";s:9:"CreateDiv";s:1:"1";s:11:"JustifyLeft";s:1:"1";s:13:"JustifyCenter";s:1:"1";s:12:"JustifyRight";s:1:"1";s:12:"JustifyBlock";s:1:"1";s:7:"BidiLtr";s:1:"1";s:7:"BidiRtl";s:1:"1";s:4:"Link";s:1:"1";s:6:"Unlink";s:1:"1";s:6:"Anchor";s:1:"1";s:5:"Image";s:1:"1";s:5:"Flash";s:1:"1";s:5:"Table";s:1:"1";s:14:"HorizontalRule";s:1:"1";s:6:"Smiley";s:1:"1";s:11:"SpecialChar";s:1:"1";s:9:"PageBreak";s:1:"1";s:6:"Iframe";s:1:"1";s:6:"Styles";s:1:"1";s:6:"Format";s:1:"1";s:4:"Font";s:1:"1";s:8:"FontSize";s:1:"1";s:9:"TextColor";s:1:"1";s:7:"BGColor";s:1:"1";s:8:"Maximize";s:1:"1";s:10:"ShowBlocks";s:1:"1";s:5:"About";s:1:"1";}}', 'Textrich');
INSERT INTO datatype VALUES (3, 'Text area', 'a:3:{s:4:"cols";s:2:"50";s:4:"rows";s:2:"30";s:4:"wrap";s:4:"hard";}', 'Textarea');
INSERT INTO datatype VALUES (4, 'Simple Image', 'a:2:{s:9:"mime_list";a:3:{i:0;s:9:"image/gif";i:1;s:10:"image/jpeg";i:2;s:9:"image/png";}s:11:"is_multiple";b:0;}', 'Upload');
INSERT INTO datatype VALUES (6, 'Datepicker', 'N;', 'DatePicker');
INSERT INTO datatype VALUES (7, 'ImageCropperRelay', 'a:4:{s:10:"background";s:7:"#FFFFFF";s:13:"resize_option";s:4:"auto";s:9:"mime_list";a:3:{i:0;s:9:"image/gif";i:1;s:10:"image/jpeg";i:2;s:9:"image/png";}s:4:"size";a:1:{i:0;a:3:{s:4:"name";s:7:"570x150";s:5:"width";s:3:"570";s:6:"height";s:3:"150";}}}', 'ImageCropper');


--
-- Name: datatype_id_seq; Type: SEQUENCE SET; Schema: public; Owner: got
--

SELECT pg_catalog.setval('datatype_id_seq', 7, true);


--
-- Name: datatypes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: got
--

SELECT pg_catalog.setval('datatypes_id_seq', 1, false);


--
-- Name: document_id_seq; Type: SEQUENCE SET; Schema: public; Owner: got
--

SELECT pg_catalog.setval('document_id_seq', 8, true);


--
-- Data for Name: document_type_dependency; Type: TABLE DATA; Schema: public; Owner: got
--

INSERT INTO document_type_dependency VALUES (2, 6, 5);
INSERT INTO document_type_dependency VALUES (4, 5, 4);


--
-- Name: document_type_dependency_id_seq; Type: SEQUENCE SET; Schema: public; Owner: got
--

SELECT pg_catalog.setval('document_type_dependency_id_seq', 4, true);


--
-- Name: document_type_id_seq; Type: SEQUENCE SET; Schema: public; Owner: got
--

SELECT pg_catalog.setval('document_type_id_seq', 6, true);


--
-- Data for Name: document_type_view; Type: TABLE DATA; Schema: public; Owner: got
--



--
-- Name: document_type_view_id_seq; Type: SEQUENCE SET; Schema: public; Owner: got
--

SELECT pg_catalog.setval('document_type_view_id_seq', 1, false);


--
-- Name: document_types_id_seq; Type: SEQUENCE SET; Schema: public; Owner: got
--

SELECT pg_catalog.setval('document_types_id_seq', 1, false);


--
-- Name: documents_id_seq; Type: SEQUENCE SET; Schema: public; Owner: got
--

SELECT pg_catalog.setval('documents_id_seq', 1, false);


--
-- Name: icon_id_seq; Type: SEQUENCE SET; Schema: public; Owner: got
--

SELECT pg_catalog.setval('icon_id_seq', 24, true);


--
-- Name: icons_id_seq; Type: SEQUENCE SET; Schema: public; Owner: got
--

SELECT pg_catalog.setval('icons_id_seq', 1, false);


--
-- Name: layout_id_seq; Type: SEQUENCE SET; Schema: public; Owner: got
--

SELECT pg_catalog.setval('layout_id_seq', 1, true);


--
-- Name: layouts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: got
--

SELECT pg_catalog.setval('layouts_id_seq', 1, false);


--
-- Name: properties_id_seq; Type: SEQUENCE SET; Schema: public; Owner: got
--

SELECT pg_catalog.setval('properties_id_seq', 1, false);


--
-- Name: properties_value_id_seq; Type: SEQUENCE SET; Schema: public; Owner: got
--

SELECT pg_catalog.setval('properties_value_id_seq', 1, false);


--
-- Data for Name: tab; Type: TABLE DATA; Schema: public; Owner: got
--

INSERT INTO tab VALUES (7, 'Title and meta', 'Meta description', NULL, 2);
INSERT INTO tab VALUES (4, 'Content', 'Content', 1, 3);
INSERT INTO tab VALUES (8, 'Title and meta', 'Meta description', 2, 3);
INSERT INTO tab VALUES (15, 'Title and meta', 'Meta description', 1, 6);
INSERT INTO tab VALUES (11, 'Content', 'Content', 1, 4);
INSERT INTO tab VALUES (12, 'Relay', 'Relay', 2, 4);
INSERT INTO tab VALUES (13, 'Title and meta', 'Meta description', 3, 4);
INSERT INTO tab VALUES (14, 'Title and meta', 'Meta description', 1, 5);
INSERT INTO tab VALUES (9, 'Banner', 'Banner', 1, 1);
INSERT INTO tab VALUES (2, 'Four blocks', 'four blocks title and content', 2, 1);
INSERT INTO tab VALUES (5, 'Title and meta', 'Meta description', 3, 1);


--
-- Data for Name: property; Type: TABLE DATA; Schema: public; Owner: got
--

INSERT INTO property VALUES (6, 'Meta description', 'metaDescription', 'Description', false, NULL, 7, 1);
INSERT INTO property VALUES (7, 'Keywords', 'metaKeywords', 'Keywords', false, NULL, 7, 1);
INSERT INTO property VALUES (8, 'Title', 'pageTitle', 'Title', false, NULL, 7, 1);
INSERT INTO property VALUES (9, 'Main Title', 'mainTitle', 'Title', false, NULL, 7, 1);
INSERT INTO property VALUES (1, 'Meta description', 'metaDescription', 'Description', false, 11, 5, 1);
INSERT INTO property VALUES (2, 'Keywords', 'metaKeywords', 'Keywords', false, 12, 5, 1);
INSERT INTO property VALUES (3, 'Title', 'pageTitle', 'Title', false, 13, 5, 1);
INSERT INTO property VALUES (4, 'Main Title', 'mainTitle', 'Title', false, 14, 5, 1);
INSERT INTO property VALUES (15, 'Title', 'title', 'Title', false, 1, 4, 1);
INSERT INTO property VALUES (5, 'Content', 'content', 'content', false, 2, 4, 2);
INSERT INTO property VALUES (10, 'Meta description', 'metaDescription', 'Description', false, 3, 8, 1);
INSERT INTO property VALUES (11, 'Keywords', 'metaKeywords', 'Keywords', false, 4, 8, 1);
INSERT INTO property VALUES (12, 'Title', 'pageTitle', 'Title', false, 5, 8, 1);
INSERT INTO property VALUES (13, 'Main Title', 'mainTitle', 'Title', false, 6, 8, 1);
INSERT INTO property VALUES (39, 'Meta description', 'metaDescription', 'Description', false, 1, 15, 1);
INSERT INTO property VALUES (40, 'Keywords', 'metaKeywords', 'Keywords', false, 2, 15, 1);
INSERT INTO property VALUES (41, 'Title', 'pageTitle', 'Title', false, 3, 15, 1);
INSERT INTO property VALUES (42, 'Main Title', 'mainTitle', 'Title', false, 4, 15, 1);
INSERT INTO property VALUES (32, 'Title', 'title', '', false, 1, 11, 1);
INSERT INTO property VALUES (33, 'Publication date', 'published_at', '', false, 2, 11, 6);
INSERT INTO property VALUES (34, 'Content', 'content', '', false, 3, 11, 2);
INSERT INTO property VALUES (26, 'Short content', 'shortContent', '', false, 4, 12, 2);
INSERT INTO property VALUES (27, 'Image', 'image', '', false, 5, 12, 7);
INSERT INTO property VALUES (28, 'Meta description', 'metaDescription', 'Description', false, 6, 13, 1);
INSERT INTO property VALUES (29, 'Keywords', 'metaKeywords', 'Keywords', false, 7, 13, 1);
INSERT INTO property VALUES (30, 'Title', 'pageTitle', 'Title', false, 8, 13, 1);
INSERT INTO property VALUES (31, 'Main Title', 'mainTitle', 'Title', false, 9, 13, 1);
INSERT INTO property VALUES (35, 'Meta description', 'metaDescription', 'Description', false, 1, 14, 1);
INSERT INTO property VALUES (36, 'Keywords', 'metaKeywords', 'Keywords', false, 2, 14, 1);
INSERT INTO property VALUES (37, 'Title', 'pageTitle', 'Title', false, 3, 14, 1);
INSERT INTO property VALUES (38, 'Main Title', 'mainTitle', 'Title', false, 4, 14, 1);
INSERT INTO property VALUES (25, 'Title', 'bannerTitle', 'Banner title', false, 1, 9, 1);
INSERT INTO property VALUES (24, 'Banner', 'banner', 'Banner', false, 2, 9, 4);
INSERT INTO property VALUES (20, 'First title', 'blockTitle1', 'First block content', false, 3, 2, 1);
INSERT INTO property VALUES (21, 'First content', 'blockContent1', 'First block content', false, 4, 2, 2);
INSERT INTO property VALUES (16, 'Second title', 'blockTitle2', 'Second block title', false, 5, 2, 1);
INSERT INTO property VALUES (22, 'Second content', 'blockContent2', 'Second block content', false, 6, 2, 2);
INSERT INTO property VALUES (17, 'Third title', 'blockTitle3', 'Third block title', false, 7, 2, 1);
INSERT INTO property VALUES (23, 'Third content', 'blockContent3', 'Third block content', false, 8, 2, 2);
INSERT INTO property VALUES (18, 'Fourth title', 'blockTitle4', 'Fourth block title', false, 9, 2, 1);
INSERT INTO property VALUES (19, 'Fourth content', 'blockContent4', 'Fourth block content', false, 10, 2, 2);


--
-- Name: property_id_seq; Type: SEQUENCE SET; Schema: public; Owner: got
--

SELECT pg_catalog.setval('property_id_seq', 42, true);


--
-- Data for Name: property_value; Type: TABLE DATA; Schema: public; Owner: got
--

INSERT INTO property_value VALUES (11, 3, 6, '\x436f6e74616374');
INSERT INTO property_value VALUES (12, 3, 7, '\x436f6e74616374');
INSERT INTO property_value VALUES (13, 3, 8, '\x436f6e74616374');
INSERT INTO property_value VALUES (14, 3, 9, '\x436f6e74616374');
INSERT INTO property_value VALUES (30, 5, 35, '\x46697273742063617465676f7279202d20426c6f67');
INSERT INTO property_value VALUES (31, 5, 36, '\x');
INSERT INTO property_value VALUES (32, 5, 37, '\x46697273742063617465676f7279202d20426c6f67');
INSERT INTO property_value VALUES (16, 1, 16, '\x526573706f6e736976653f205265616c6c793f');
INSERT INTO property_value VALUES (2, 1, 1, '\x4d792077656273697465');
INSERT INTO property_value VALUES (15, 2, 15, '\x41626f7574');
INSERT INTO property_value VALUES (6, 2, 5, '\x3c703e0d0a0953757370656e64697373652066617563696275732064696374756d2074656c6c757320696420706f73756572652e204372617320717569732065726f732074656c6c75732c20696420706f7375657265206c616375732e205072616573656e74206163206573742065726f732e20416c697175616d20656c656966656e64206e756e63207574206e6571756520636f6e736571756174207175697320737573636970697420656c69742074696e636964756e742e20467573636520696e74657264756d20677261766964612074696e636964756e742e2053656420657420616e7465206e6563206c6967756c61206672696e67696c6c6120636f6e64696d656e74756d2e204d61757269732076656c2073656d206163206c6967756c6120736564206c6f72656d20766573746962756c756d206f726e6172652076656c20736564206e6962682e2046757363652072686f6e6375732074696e636964756e7420616e74652c206e6f6e2068656e647265726974206d61676e6120766573746962756c756d20717569732e20496e74656765722076656c20656e696d2073656d2070686173656c6c75732074656d707573206c6f72656d2e3c2f703e0d0a3c703e0d0a094d616563656e61732076656c206d61757269732073697420616d657420617567756520616363756d73616e2074656d706f72206e6f6e20696e2073617069656e2e204e756c6c6120666163696c6973692e20416c697175616d20696d706572646965742073656d206574206f7263692061646970697363696e67207665686963756c61207361676974746973206d6574757320636f6e73656374657475722e204d617572697320612064756920646f6c6f72206d61676e612074726973746971756520636f6e64696d656e74756d206e6563207574206c6967756c612e20536564206575206172637520696e206e6571756520706f72746120696163756c69732e204d6175726973206665726d656e74756d2076656c69742073697420616d6574206c656f20636f6e76616c6c697320636f6e73656374657475722e2055742076656c206c696265726f206475692c206575207665686963756c612076656c69742e204d616563656e6173206574206a7573746f207669746165206c616375732076656e656e61746973207665686963756c612e205574206567657420706f7375657265206172637520646f6c6f726520626c616e6469742e3c2f703e0d0a3c703e0d0a0941656e65616e206e656320747572706973206163206c6967756c6120706f737565726520656c656966656e642073656420612065726f732e20496e7465676572206e6563206e69626820696e74657264756d206e756c6c61206672696e67696c6c6120756c747269636965732e20496e74656765722073656d7065722066656c697320657520616e746520636f6e677565206469676e697373696d2e205072616573656e742072686f6e637573206e756c6c61207365642066656c69732072686f6e63757320657420696163756c6973207269737573206d61747469732e204e616d20736564207075727573206475692c2061206567657374617320656e696d2e20446f6e656320666163696c697369732c20656e696d206964206665726d656e74756d206d61747469732c2074656c6c7573206c696265726f20756c74726963696573206d617373612c20657520626962656e64756d20617567756520697073756d2073697420616d6574206c6967756c612e2050726f696e20706f72746120736167697474697320657261742061632070686172657472612e2041656e65616e20756c7472696369657320656e696d206964206d69206c6163696e69612072686f6e6375732e204e756e6320766f6c75747061742c207475727069732076656c20706f7274612074696e636964756e742c206c6563747573206d65747573206665726d656e74756d206c65637475732c2073656420736f64616c65732065726174206e697369206e6f6e20646f6c6f722e2050656c6c656e7465737175652076697461652066656c69732065676574207475727069732072757472756d206d616c65737561646120696e206e6f6e207175616d2e204d6175726973206174206c6f72656d2076656c206e756e6320747269737469717565207363656c657269737175652073697420616d6574206964206e69736c2e20446f6e6563206174206e697369206d61676e612c20696e207072657469756d206c6967756c612e20536564206c616375732061756775652c207361676974746973206574206772617669646120766976657272612c20696d70657264696574206567657420656c69742e204d6f726269206d616c657375616461206c6967756c612e2050726f696e20706f72746120736167697474697320657261742061632070686172657472612e2041656e65616e20756c7472696369657320656e696d206964206d69206c6163696e69612072686f6e6375732e204e756e6320766f6c75747061742c207475727069732076656c20706f7274612074696e636964756e742c206c6563747573206d65747573206665726d656e74756d206c65637475732c2073656420736f64616c6573206572617420696d706572646965742073656d206574206f7263692061646970697363696e67207665686963756c61207361676974746973206d6574757320636f6e73656374657475722e204d617572697320612064756920646f6c6f72206d61676e612074726973746971756520636f6e64696d656e74756d206e6563207574206c6967756c612e20536564206575206172637520696e206e6571756520706f72746120696163756c69732e204d6175726973206665726d656e74756d2076656c69742073697420616d6574206c656f20636f6e76616c6c697320636f6e73656374657475722e2055742076656c206c696265726f206475692c206575207665686963756c612076656c69742e204d616563656e6173206574206a7573746f206e697369206e6f6e20646f6c6f722e2050656c6c656e7465737175652076697461652066656c69732065676574207475727069732072757472756d206d616c65737561646120696e206e6f6e207175616d2e204d6175726973206174206c6f72656d2076656c206e756e6320747269737469717565207363656c657269737175652073697420616d6574206964206e69736c2e20446f6e6563206174206e697369206d61676e612c20696e207072657469756d206c6967756c612e20536564206c616375732061756775652c207361676974746973206574206772617669646120766976657272612c20696d70657264696574206567657420656c69742e204d6f726269206d616c657375616461206d61757269732065752065726f732072757472756d20626c616e6469742e2043726173206d616c6573756164612072757472756d2073656d2c206163207665686963756c61206469616d20766f6c75747061742061632e20566573746962756c756d2073656420746f72746f722070757275732e2050686173656c6c757320696e206c6563747573206672696e67696c6c612074656c6c757320706f72747469746f72207665686963756c61207574206665726d656e74756d206d65747573206c6f72656d20697073756d20646f6c6f722073697420616d6574206c6f72656d20697073756d20646f6c6f722073697420616d6574206e756c6c616d20636f6e736571756174206c6f72656d20697073756d20646f6c6f722073697420616d657420636f6e736571756174207665726f65726f7320657469616d2e3c2f703e0d0a');
INSERT INTO property_value VALUES (7, 2, 10, '\x41626f7574');
INSERT INTO property_value VALUES (8, 2, 11, '\x');
INSERT INTO property_value VALUES (9, 2, 12, '\x41626f7574');
INSERT INTO property_value VALUES (10, 2, 13, '\x41626f7574');
INSERT INTO property_value VALUES (33, 5, 38, '\x46697273742063617465676f7279202d20426c6f67');
INSERT INTO property_value VALUES (34, 6, 35, '\x5365636f6e642063617465676f7279202d20426c6f67');
INSERT INTO property_value VALUES (25, 1, 25, '\x417263616e613a204120726573706f6e736976652048544d4c3520736974652074656d706c6174652062792048544d4c35205550');
INSERT INTO property_value VALUES (24, 1, 24, '\x613a353a7b733a353a2276616c7565223b733a33353a222f6d656469612f66696c65732f312f32342f353139646262336162666464382e6a7067223b733a353a227769647468223b693a313230303b733a363a22686569676874223b693a3236353b733a343a2268746d6c223b693a323b733a343a226d696d65223b733a31303a22696d6167652f6a706567223b7d');
INSERT INTO property_value VALUES (20, 1, 20, '\x57656c636f6d6520746f20417263616e61');
INSERT INTO property_value VALUES (21, 1, 21, '\x3c703e0d0a0954686973206973203c7374726f6e673e417263616e613c2f7374726f6e673e2c20612066756c6c7920726573706f6e736976652048544d4c3520736974652074656d706c6174652064657369676e6564206279203c6120687265663d22687474703a2f2f6e33332e636f2f223e6e33333c2f613e20616e642072656c656173656420666f722066726565206279203c6120687265663d22687474703a2f2f68746d6c3575702e6e65742f223e48544d4c352055503c2f613e20497420666561747572657320612073696d706c652079657420656c6567616e742064657369676e2c20736f6c69642048544d4c3520616e64204353533320636f64652c20616e642066756c6c20726573706f6e7369766520737570706f727420666f72206465736b746f702c207461626c65742c20616e64206d6f62696c6520646973706c6179732e3c2f703e0d0a');
INSERT INTO property_value VALUES (3, 1, 2, '\x');
INSERT INTO property_value VALUES (4, 1, 3, '\x4d792077656273697465');
INSERT INTO property_value VALUES (41, 7, 26, '\x3c703e0d0a0953757370656e64697373652066617563696275732064696374756d2074656c6c757320696420706f73756572652e204372617320717569732065726f732074656c6c75732c20696420706f7375657265206c616375732e205072616573656e74206163206573742065726f732e20416c697175616d20656c656966656e64206e756e63207574206e6571756520636f6e736571756174207175697320737573636970697420656c69742074696e636964756e742e20467573636520696e74657264756d20677261766964612074696e636964756e742e2053656420657420616e7465206e6563206c6967756c61206672696e67696c6c6120636f6e64696d656e74756d2e204d61757269732076656c2073656d206163206c6967756c6120736564206c6f72656d20766573746962756c756d206f726e6172652076656c20736564206e6962682e2046757363652072686f6e6375732074696e636964756e7420616e74652c206e6f6e2068656e647265726974206d61676e6120766573746962756c756d20717569732e20496e74656765722076656c20656e696d2073656d2070686173656c6c75732074656d707573206c6f72656d2e3c2f703e0d0a');
INSERT INTO property_value VALUES (42, 7, 27, '\x623a303b');
INSERT INTO property_value VALUES (43, 7, 28, '\x54776f20436f6c756d6e202332');
INSERT INTO property_value VALUES (44, 7, 29, '\x');
INSERT INTO property_value VALUES (45, 7, 30, '\x54776f20436f6c756d6e202332');
INSERT INTO property_value VALUES (46, 7, 31, '\x54776f20436f6c756d6e202332');
INSERT INTO property_value VALUES (47, 8, 32, '\x54776f20436f6c756d6e20233220286c6566742d68616e64207369646562617229');
INSERT INTO property_value VALUES (48, 8, 33, '\x323031332f30352f32352030323a33333a3039');
INSERT INTO property_value VALUES (51, 8, 27, '\x623a303b');
INSERT INTO property_value VALUES (52, 8, 28, '\x54776f20436f6c756d6e202332');
INSERT INTO property_value VALUES (53, 8, 29, '\x');
INSERT INTO property_value VALUES (5, 1, 4, '\x4d792077656273697465');
INSERT INTO property_value VALUES (26, 4, 39, '\x426c6f67');
INSERT INTO property_value VALUES (35, 6, 36, '\x');
INSERT INTO property_value VALUES (36, 6, 37, '\x5365636f6e642063617465676f7279202d20426c6f67');
INSERT INTO property_value VALUES (27, 4, 40, '\x');
INSERT INTO property_value VALUES (28, 4, 41, '\x426c6f67');
INSERT INTO property_value VALUES (29, 4, 42, '\x426c6f67');
INSERT INTO property_value VALUES (37, 6, 38, '\x5365636f6e642063617465676f7279202d20426c6f67');
INSERT INTO property_value VALUES (38, 7, 32, '\x54776f20436f6c756d6e20233220286c6566742d68616e64207369646562617229');
INSERT INTO property_value VALUES (39, 7, 33, '\x323031332f30352f32352030323a33323a3137');
INSERT INTO property_value VALUES (54, 8, 30, '\x54776f20436f6c756d6e202332');
INSERT INTO property_value VALUES (55, 8, 31, '\x54776f20436f6c756d6e202332');
INSERT INTO property_value VALUES (22, 1, 22, '\x3c703e0d0a095975702e205468697320736974652074656d706c6174652069732066756c6c7920726573706f6e736976652c206d65616e696e67206974262333393b6c6c206c6f6f6b206772656174206f6e206465736b746f7020287769646573637265656e20616e64207374616e64617264292c207461626c657420616e64206d6f62696c652064657669636520646973706c6179732e2054727920697420666f7220796f757273656c663a2073696d706c7920726573697a6520796f75722062726f777365722077696e646f7720746f2073656520686f77207374756666207368696674732061726f756e6420616e64206368616e6765732e3c2f703e0d0a');
INSERT INTO property_value VALUES (17, 1, 17, '\x5468652043434120332e30204c6963656e7365');
INSERT INTO property_value VALUES (23, 1, 23, '\x3c703e0d0a093c7374726f6e673e417263616e613c2f7374726f6e673e2069732072656c656173656420666f72206672656520756e64657220746865203c6120687265663d22687474703a2f2f68746d6c3575702e6e65742f6c6963656e7365223e437265617469766520436f6d6d6f6e73204174747269627574696f6e20332e30206c6963656e73653c2f613e2c207768696368206d65616e7320796f752063616e2075736520697420666f7220707265747479206d75636820616e7920706572736f6e616c206f7220636f6d6d65726369616c207573652e205468652063617463683f204a75737420676976652075732063726564697420776865726576657220796f752075736520697420286f682c20616e642074656c6c20796f757220667269656e64732061626f75742075732c2074686f7567682074686174262333393b73206e6f7420696e20746865206c6963656e7365203a293c2f703e0d0a');
INSERT INTO property_value VALUES (18, 1, 18, '\x41626f75742048544d4c35205550');
INSERT INTO property_value VALUES (19, 1, 19, '\x3c703e0d0a0948544d4c35205550206973206120736d616c6c20736964652070726f6a65637420627920414a2028616b61206e33332920746f20707261637469636520776f726b696e6720776974682048544d4c3520616e6420726573706f6e736976652064657369676e20746563686e69717565732e20596f752063616e2066696e64206d6f726520636f6f6c2064657369676e73206174203c6120687265663d22687474703a2f2f68746d6c3575702e6e65742f223e68746d6c3575702e6e65743c2f613e206f7220666f6c6c6f77207573206f6e205477697474657220283c6120687265663d22687474703a2f2f747769747465722e636f6d2f6e3333636f223e406e3333636f3c2f613e2920666f72206e65772072656c6561736520616e6e6f756e63656d656e747320616e64206f7468657220636f6f6c2073747566662e3c2f703e0d0a');
INSERT INTO property_value VALUES (40, 7, 34, '\x3c703e0d0a0953757370656e64697373652066617563696275732064696374756d2074656c6c757320696420706f73756572652e204372617320717569732065726f732074656c6c75732c20696420706f7375657265206c616375732e205072616573656e74206163206573742065726f732e20416c697175616d20656c656966656e64206e756e63207574206e6571756520636f6e736571756174207175697320737573636970697420656c69742074696e636964756e742e20467573636520696e74657264756d20677261766964612074696e636964756e742e2053656420657420616e7465206e6563206c6967756c61206672696e67696c6c6120636f6e64696d656e74756d2e204d61757269732076656c2073656d206163206c6967756c6120736564206c6f72656d20766573746962756c756d206f726e6172652076656c20736564206e6962682e2046757363652072686f6e6375732074696e636964756e7420616e74652c206e6f6e2068656e647265726974206d61676e6120766573746962756c756d20717569732e20496e74656765722076656c20656e696d2073656d2070686173656c6c75732074656d707573206c6f72656d2e3c2f703e0d0a3c703e0d0a094d616563656e61732076656c206d61757269732073697420616d657420617567756520616363756d73616e2074656d706f72206e6f6e20696e2073617069656e2e204e756c6c6120666163696c6973692e20416c697175616d20696d706572646965742073656d206574206f7263692061646970697363696e67207665686963756c61207361676974746973206d6574757320636f6e73656374657475722e204d617572697320612064756920646f6c6f72206d61676e612074726973746971756520636f6e64696d656e74756d206e6563207574206c6967756c612e20536564206575206172637520696e206e6571756520706f72746120696163756c69732e204d6175726973206665726d656e74756d2076656c69742073697420616d6574206c656f20636f6e76616c6c697320636f6e73656374657475722e2055742076656c206c696265726f206475692c206575207665686963756c612076656c69742e204d616563656e6173206574206a7573746f207669746165206c616375732076656e656e61746973207665686963756c612e205574206567657420706f7375657265206172637520646f6c6f726520626c616e6469742e3c2f703e0d0a3c703e0d0a0941656e65616e206e656320747572706973206163206c6967756c6120706f737565726520656c656966656e642073656420612065726f732e20496e7465676572206e6563206e69626820696e74657264756d206e756c6c61206672696e67696c6c6120756c747269636965732e20496e74656765722073656d7065722066656c697320657520616e746520636f6e677565206469676e697373696d2e205072616573656e742072686f6e637573206e756c6c61207365642066656c69732072686f6e63757320657420696163756c6973207269737573206d61747469732e204e616d20736564207075727573206475692c2061206567657374617320656e696d2e20446f6e656320666163696c697369732c20656e696d206964206665726d656e74756d206d61747469732c2074656c6c7573206c696265726f20756c74726963696573206d617373612c20657520626962656e64756d20617567756520697073756d2073697420616d6574206c6967756c612e2050726f696e20706f72746120736167697474697320657261742061632070686172657472612e2041656e65616e20756c7472696369657320656e696d206964206d69206c6163696e69612072686f6e6375732e204e756e6320766f6c75747061742c207475727069732076656c20706f7274612074696e636964756e742c206c6563747573206d65747573206665726d656e74756d206c65637475732c2073656420736f64616c65732065726174206e697369206e6f6e20646f6c6f722e2050656c6c656e7465737175652076697461652066656c69732065676574207475727069732072757472756d206d616c65737561646120696e206e6f6e207175616d2e204d6175726973206174206c6f72656d2076656c206e756e6320747269737469717565207363656c657269737175652073697420616d6574206964206e69736c2e20446f6e6563206174206e697369206d61676e612c20696e207072657469756d206c6967756c612e20536564206c616375732061756775652c207361676974746973206574206772617669646120766976657272612c20696d70657264696574206567657420656c69742e204d6f726269206d616c657375616461206c6967756c612e2050726f696e20706f72746120736167697474697320657261742061632070686172657472612e2041656e65616e20756c7472696369657320656e696d206964206d69206c6163696e69612072686f6e6375732e204e756e6320766f6c75747061742c207475727069732076656c20706f7274612074696e636964756e742c206c6563747573206d65747573206665726d656e74756d206c65637475732c2073656420736f64616c6573206572617420696d706572646965742073656d206574206f7263692061646970697363696e67207665686963756c61207361676974746973206d6574757320636f6e73656374657475722e204d617572697320612064756920646f6c6f72206d61676e612074726973746971756520636f6e64696d656e74756d206e6563207574206c6967756c612e20536564206575206172637520696e206e6571756520706f72746120696163756c69732e204d6175726973206665726d656e74756d2076656c69742073697420616d6574206c656f20636f6e76616c6c697320636f6e73656374657475722e2055742076656c206c696265726f206475692c206575207665686963756c612076656c69742e204d616563656e6173206574206a7573746f206e697369206e6f6e20646f6c6f722e2050656c6c656e7465737175652076697461652066656c69732065676574207475727069732072757472756d206d616c65737561646120696e206e6f6e207175616d2e204d6175726973206174206c6f72656d2076656c206e756e6320747269737469717565207363656c657269737175652073697420616d6574206964206e69736c2e20446f6e6563206174206e697369206d61676e612c20696e207072657469756d206c6967756c612e20536564206c616375732061756775652c207361676974746973206574206772617669646120766976657272612c20696d70657264696574206567657420656c69742e204d6f726269206d616c657375616461206d61757269732065752065726f732072757472756d20626c616e6469742e2043726173206d616c6573756164612072757472756d2073656d2c206163207665686963756c61206469616d20766f6c75747061742061632e20566573746962756c756d2073656420746f72746f722070757275732e2050686173656c6c757320696e206c6563747573206672696e67696c6c612074656c6c757320706f72747469746f72207665686963756c61207574206665726d656e74756d206d65747573206c6f72656d20697073756d20646f6c6f722073697420616d6574206c6f72656d20697073756d20646f6c6f722073697420616d6574206e756c6c616d20636f6e736571756174206c6f72656d20697073756d20646f6c6f722073697420616d657420636f6e736571756174207665726f65726f7320657469616d2e3c2f703e0d0a');
INSERT INTO property_value VALUES (49, 8, 34, '\x3c703e0d0a0953757370656e64697373652066617563696275732064696374756d2074656c6c757320696420706f73756572652e204372617320717569732065726f732074656c6c75732c20696420706f7375657265206c616375732e205072616573656e74206163206573742065726f732e20416c697175616d20656c656966656e64206e756e63207574206e6571756520636f6e736571756174207175697320737573636970697420656c69742074696e636964756e742e20467573636520696e74657264756d20677261766964612074696e636964756e742e2053656420657420616e7465206e6563206c6967756c61206672696e67696c6c6120636f6e64696d656e74756d2e204d61757269732076656c2073656d206163206c6967756c6120736564206c6f72656d20766573746962756c756d206f726e6172652076656c20736564206e6962682e2046757363652072686f6e6375732074696e636964756e7420616e74652c206e6f6e2068656e647265726974206d61676e6120766573746962756c756d20717569732e20496e74656765722076656c20656e696d2073656d2070686173656c6c75732074656d707573206c6f72656d2e3c2f703e0d0a3c703e0d0a094d616563656e61732076656c206d61757269732073697420616d657420617567756520616363756d73616e2074656d706f72206e6f6e20696e2073617069656e2e204e756c6c6120666163696c6973692e20416c697175616d20696d706572646965742073656d206574206f7263692061646970697363696e67207665686963756c61207361676974746973206d6574757320636f6e73656374657475722e204d617572697320612064756920646f6c6f72206d61676e612074726973746971756520636f6e64696d656e74756d206e6563207574206c6967756c612e20536564206575206172637520696e206e6571756520706f72746120696163756c69732e204d6175726973206665726d656e74756d2076656c69742073697420616d6574206c656f20636f6e76616c6c697320636f6e73656374657475722e2055742076656c206c696265726f206475692c206575207665686963756c612076656c69742e204d616563656e6173206574206a7573746f207669746165206c616375732076656e656e61746973207665686963756c612e205574206567657420706f7375657265206172637520646f6c6f726520626c616e6469742e3c2f703e0d0a3c703e0d0a0941656e65616e206e656320747572706973206163206c6967756c6120706f737565726520656c656966656e642073656420612065726f732e20496e7465676572206e6563206e69626820696e74657264756d206e756c6c61206672696e67696c6c6120756c747269636965732e20496e74656765722073656d7065722066656c697320657520616e746520636f6e677565206469676e697373696d2e205072616573656e742072686f6e637573206e756c6c61207365642066656c69732072686f6e63757320657420696163756c6973207269737573206d61747469732e204e616d20736564207075727573206475692c2061206567657374617320656e696d2e20446f6e656320666163696c697369732c20656e696d206964206665726d656e74756d206d61747469732c2074656c6c7573206c696265726f20756c74726963696573206d617373612c20657520626962656e64756d20617567756520697073756d2073697420616d6574206c6967756c612e2050726f696e20706f72746120736167697474697320657261742061632070686172657472612e2041656e65616e20756c7472696369657320656e696d206964206d69206c6163696e69612072686f6e6375732e204e756e6320766f6c75747061742c207475727069732076656c20706f7274612074696e636964756e742c206c6563747573206d65747573206665726d656e74756d206c65637475732c2073656420736f64616c65732065726174206e697369206e6f6e20646f6c6f722e2050656c6c656e7465737175652076697461652066656c69732065676574207475727069732072757472756d206d616c65737561646120696e206e6f6e207175616d2e204d6175726973206174206c6f72656d2076656c206e756e6320747269737469717565207363656c657269737175652073697420616d6574206964206e69736c2e20446f6e6563206174206e697369206d61676e612c20696e207072657469756d206c6967756c612e20536564206c616375732061756775652c207361676974746973206574206772617669646120766976657272612c20696d70657264696574206567657420656c69742e204d6f726269206d616c657375616461206c6967756c612e2050726f696e20706f72746120736167697474697320657261742061632070686172657472612e2041656e65616e20756c7472696369657320656e696d206964206d69206c6163696e69612072686f6e6375732e204e756e6320766f6c75747061742c207475727069732076656c20706f7274612074696e636964756e742c206c6563747573206d65747573206665726d656e74756d206c65637475732c2073656420736f64616c6573206572617420696d706572646965742073656d206574206f7263692061646970697363696e67207665686963756c61207361676974746973206d6574757320636f6e73656374657475722e204d617572697320612064756920646f6c6f72206d61676e612074726973746971756520636f6e64696d656e74756d206e6563207574206c6967756c612e20536564206575206172637520696e206e6571756520706f72746120696163756c69732e204d6175726973206665726d656e74756d2076656c69742073697420616d6574206c656f20636f6e76616c6c697320636f6e73656374657475722e2055742076656c206c696265726f206475692c206575207665686963756c612076656c69742e204d616563656e6173206574206a7573746f206e697369206e6f6e20646f6c6f722e2050656c6c656e7465737175652076697461652066656c69732065676574207475727069732072757472756d206d616c65737561646120696e206e6f6e207175616d2e204d6175726973206174206c6f72656d2076656c206e756e6320747269737469717565207363656c657269737175652073697420616d6574206964206e69736c2e20446f6e6563206174206e697369206d61676e612c20696e207072657469756d206c6967756c612e20536564206c616375732061756775652c207361676974746973206574206772617669646120766976657272612c20696d70657264696574206567657420656c69742e204d6f726269206d616c657375616461206d61757269732065752065726f732072757472756d20626c616e6469742e2043726173206d616c6573756164612072757472756d2073656d2c206163207665686963756c61206469616d20766f6c75747061742061632e20566573746962756c756d2073656420746f72746f722070757275732e2050686173656c6c757320696e206c6563747573206672696e67696c6c612074656c6c757320706f72747469746f72207665686963756c61207574206665726d656e74756d206d65747573206c6f72656d20697073756d20646f6c6f722073697420616d6574206c6f72656d20697073756d20646f6c6f722073697420616d6574206e756c6c616d20636f6e736571756174206c6f72656d20697073756d20646f6c6f722073697420616d657420636f6e736571756174207665726f65726f7320657469616d2e3c2f703e0d0a');
INSERT INTO property_value VALUES (50, 8, 26, '\x3c703e0d0a0953757370656e64697373652066617563696275732064696374756d2074656c6c757320696420706f73756572652e204372617320717569732065726f732074656c6c75732c20696420706f7375657265206c616375732e205072616573656e74206163206573742065726f732e20416c697175616d20656c656966656e64206e756e63207574206e6571756520636f6e736571756174207175697320737573636970697420656c69742074696e636964756e742e20467573636520696e74657264756d20677261766964612074696e636964756e742e2053656420657420616e7465206e6563206c6967756c61206672696e67696c6c6120636f6e64696d656e74756d2e204d61757269732076656c2073656d206163206c6967756c6120736564206c6f72656d20766573746962756c756d206f726e6172652076656c20736564206e6962682e2046757363652072686f6e6375732074696e636964756e7420616e74652c206e6f6e2068656e647265726974206d61676e6120766573746962756c756d20717569732e20496e74656765722076656c20656e696d2073656d2070686173656c6c75732074656d707573206c6f72656d2e3c2f703e0d0a');


--
-- Name: property_value_id_seq; Type: SEQUENCE SET; Schema: public; Owner: got
--

SELECT pg_catalog.setval('property_value_id_seq', 55, true);


--
-- Data for Name: script; Type: TABLE DATA; Schema: public; Owner: got
--

INSERT INTO script VALUES (1, '2012-09-20 22:26:23', '2012-09-20 22:26:23', 'Contact', 'contact', '<?php
$request = $this->getRequest();
if($request->isPost())
{
    $post = $request->getPost();
    $name = $post->get(''name'');
    $email = $post->get(''email'');
    $message = $post->get(''message'');
    $answer_hash = $post->get(''answer_hash'');
    $answer = substr(sha1($post->get(''answer'')), 5, 10);

    if($answer != $answer_hash or empty($name) or empty($email) or empty($message))
    {
        return array(''name'' => $name, ''email'' => $email, ''message'' => $message, ''error_message'' => ''Please fill all fields'');
    }
    else
    {
        $mail = new \Gc\Mail(''utf-8'', $message);
        $mail->setFrom($email, $name);
        $mail->addTo($this->getServiceLocator()->get(''CoreConfig'')->getValue(''mail_from''));
        $mail->send();
        $this->flashMessenger()->addSuccessMessage(''Message sent'');
        $this->redirect()->toUrl(''/contact'');
        return TRUE;
    }
}
', 'Contact ');


--
-- Name: script_id_seq; Type: SEQUENCE SET; Schema: public; Owner: got
--

SELECT pg_catalog.setval('script_id_seq', 1, true);


--
-- Name: tab_id_seq; Type: SEQUENCE SET; Schema: public; Owner: got
--

SELECT pg_catalog.setval('tab_id_seq', 15, true);


--
-- Name: tabs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: got
--

SELECT pg_catalog.setval('tabs_id_seq', 1, false);


--
-- Name: view_id_seq; Type: SEQUENCE SET; Schema: public; Owner: got
--

SELECT pg_catalog.setval('view_id_seq', 11, true);


--
-- Name: views_id_seq; Type: SEQUENCE SET; Schema: public; Owner: got
--

SELECT pg_catalog.setval('views_id_seq', 1, false);


--
-- PostgreSQL database dump complete
--


