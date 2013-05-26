INSERT INTO view VALUES (6, '2012-10-25 19:57:05', '2013-05-26 13:26:11.897632', 'Flash messages', 'flash-messages', '<?php if(!empty($this->layout()->flashMessages)): ?>
    <?php foreach($this->layout()->flashMessages as $type => $messages):?>
        <?php foreach($messages as $message): ?>
            <div class="notification <?php echo $type; ?>">
             <?php echo $this->escapeHtml($this->translate($message)); ?>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
<?php endif; ?>', 'Flash messages displayer');
INSERT INTO view VALUES (9, '2013-05-22 19:32:25.295636', '2013-05-26 13:26:11.910965', 'Footer', 'footer', '<?php use Gc\\Core\\Config; ?>
<div id="footer-wrapper">
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
                    &copy; <?php echo $this->escapeHtml(Config::getValue(''site_name'')); ?>. All rights reserved. | Design: <a href="http://html5up.net">HTML5 UP</a> | Images: <a href="http://fotogrph.com">fotogrph</a>
                </div>
            </div>
        </div>
    </footer>
</div>
', 'Footer navigation');
INSERT INTO view VALUES (8, '2013-05-22 19:29:56.943005', '2013-05-26 13:26:11.922496', 'Header', 'header', '<nav id="nav">
    <?php
        $component = new \\Gc\\Component\\Navigation();
        $container = new \\Zend\\Navigation\\Navigation($component->render());
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
                $comment_table = new \\Modules\\Blog\\Model\\Comment();

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

                $paginator = new \\Zend\\Paginator\\Paginator(new \\Zend\\Paginator\\Adapter\\ArrayAdapter($posts));
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

                $comment_table = new \\Modules\\Blog\\Model\\Comment();
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



INSERT INTO document_type VALUES (2, '2012-09-20 22:05:53', '2012-10-08 20:44:24', 'Contact', 'Contact form', 11, 3, 1);
INSERT INTO document_type VALUES (3, '2012-09-20 22:06:37', '2013-05-22 21:08:15.543148', 'About', 'About this website', 13, 4, 1);
INSERT INTO document_type VALUES (6, '2013-05-24 08:57:15.489668', '2013-05-24 19:01:16.968222', 'Blog', 'Blog', 24, 2, 1);
INSERT INTO document_type VALUES (4, '2013-05-24 08:49:53.537851', '2013-05-24 19:03:37.181713', 'Blog ticket', 'Ticket blog', 6, 11, 1);
INSERT INTO document_type VALUES (5, '2013-05-24 08:56:17.202506', '2013-05-25 14:13:02.199039', 'Blog category', 'Blog category', 8, 2, 1);
INSERT INTO document_type VALUES (1, '2012-09-20 22:01:55', '2013-05-26 13:29:33.312101', 'Home', 'Home page', 1, 1, 1);


INSERT INTO layout VALUES (1, '2012-09-19 19:28:34', '2013-05-24 08:24:49.350913', 'Main', 'main', '<?php use Gc\\Core\\Config; ?>
<!DOCTYPE html>
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
        <link href="http://fonts.googleapis.com/css?family=Open+Sans+Condensed:300,300italic,700" rel="stylesheet" />
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
                            <h1><?php echo $this->escapeHtml(Config::getValue(''site_name'')); ?></h1>
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


INSERT INTO document VALUES (2, '2012-09-20 22:09:06', '2013-05-25 13:58:10.956633', 'About', 'about', 1, 2, true, 1, 3, 4, 1, NULL);
INSERT INTO document VALUES (3, '2012-09-20 22:09:29', '2013-05-25 13:58:10.973436', 'Contact', 'contact', 1, 3, true, 1, 2, 3, 1, NULL);
INSERT INTO document VALUES (1, '2012-09-20 22:06:53', '2013-05-26 13:28:51.590892', 'Home', '', 1, 0, true, 1, 1, 1, 1, NULL);
INSERT INTO document VALUES (4, '2013-05-25 13:57:47.516331', '2013-05-26 13:31:30.638063', 'Blog', 'blog', 1, 1, true, 1, 6, 2, 1, NULL);
INSERT INTO document VALUES (5, '2013-05-25 13:58:49.09857', '2013-05-26 13:31:50.957512', 'First category', 'first-category', 1, 0, true, 1, 5, 2, 1, 4);
INSERT INTO document VALUES (6, '2013-05-25 13:59:05.99698', '2013-05-26 13:32:01.369958', 'Second category', 'second-category', 1, 0, true, 1, 5, 2, 1, 4);
INSERT INTO document VALUES (7, '2013-05-25 14:00:54.412118', '2013-05-26 13:32:18.374819', 'Article 1', 'article-about-something', 1, 0, false, 1, 4, 11, 1, 5);
INSERT INTO document VALUES (8, '2013-05-25 14:01:26.09229', '2013-05-26 13:32:27.671487', 'Article 2', 'article-about-something', 1, 0, false, 1, 4, 11, 1, 6);


INSERT INTO datatype VALUES (1, 'Text field', 'N;', 'Textstring');
INSERT INTO datatype VALUES (2, 'Rich text', 'a:1:{s:13:"toolbar-items";a:67:{s:6:"Source";s:1:"1";s:4:"Save";s:1:"1";s:7:"NewPage";s:1:"1";s:8:"DocProps";s:1:"1";s:7:"Preview";s:1:"1";s:5:"Print";s:1:"1";s:9:"Templates";s:1:"1";s:3:"Cut";s:1:"1";s:4:"Copy";s:1:"1";s:5:"Paste";s:1:"1";s:9:"PasteText";s:1:"1";s:13:"PasteFromWord";s:1:"1";s:4:"Undo";s:1:"1";s:4:"Redo";s:1:"1";s:4:"Find";s:1:"1";s:7:"Replace";s:1:"1";s:9:"SelectAll";s:1:"1";s:12:"SpellChecker";s:1:"1";s:5:"Scayt";s:1:"1";s:4:"Form";s:1:"1";s:8:"Checkbox";s:1:"1";s:5:"Radio";s:1:"1";s:9:"TextField";s:1:"1";s:8:"Textarea";s:1:"1";s:6:"Select";s:1:"1";s:6:"Button";s:1:"1";s:11:"ImageButton";s:1:"1";s:11:"HiddenField";s:1:"1";s:4:"Bold";s:1:"1";s:6:"Italic";s:1:"1";s:9:"Underline";s:1:"1";s:6:"Strike";s:1:"1";s:9:"Subscript";s:1:"1";s:11:"Superscript";s:1:"1";s:12:"RemoveFormat";s:1:"1";s:12:"NumberedList";s:1:"1";s:12:"BulletedList";s:1:"1";s:7:"Outdent";s:1:"1";s:6:"Indent";s:1:"1";s:10:"Blockquote";s:1:"1";s:9:"CreateDiv";s:1:"1";s:11:"JustifyLeft";s:1:"1";s:13:"JustifyCenter";s:1:"1";s:12:"JustifyRight";s:1:"1";s:12:"JustifyBlock";s:1:"1";s:7:"BidiLtr";s:1:"1";s:7:"BidiRtl";s:1:"1";s:4:"Link";s:1:"1";s:6:"Unlink";s:1:"1";s:6:"Anchor";s:1:"1";s:5:"Image";s:1:"1";s:5:"Flash";s:1:"1";s:5:"Table";s:1:"1";s:14:"HorizontalRule";s:1:"1";s:6:"Smiley";s:1:"1";s:11:"SpecialChar";s:1:"1";s:9:"PageBreak";s:1:"1";s:6:"Iframe";s:1:"1";s:6:"Styles";s:1:"1";s:6:"Format";s:1:"1";s:4:"Font";s:1:"1";s:8:"FontSize";s:1:"1";s:9:"TextColor";s:1:"1";s:7:"BGColor";s:1:"1";s:8:"Maximize";s:1:"1";s:10:"ShowBlocks";s:1:"1";s:5:"About";s:1:"1";}}', 'Textrich');
INSERT INTO datatype VALUES (3, 'Text area', 'a:3:{s:4:"cols";s:2:"50";s:4:"rows";s:2:"30";s:4:"wrap";s:4:"hard";}', 'Textarea');
INSERT INTO datatype VALUES (4, 'Simple Image', 'a:2:{s:9:"mime_list";a:3:{i:0;s:9:"image/gif";i:1;s:10:"image/jpeg";i:2;s:9:"image/png";}s:11:"is_multiple";b:0;}', 'Upload');
INSERT INTO datatype VALUES (6, 'Datepicker', 'N;', 'DatePicker');
INSERT INTO datatype VALUES (7, 'ImageCropperRelay', 'a:4:{s:10:"background";s:7:"#FFFFFF";s:13:"resize_option";s:4:"auto";s:9:"mime_list";a:3:{i:0;s:9:"image/gif";i:1;s:10:"image/jpeg";i:2;s:9:"image/png";}s:4:"size";a:1:{i:0;a:3:{s:4:"name";s:7:"570x150";s:5:"width";s:3:"570";s:6:"height";s:3:"150";}}}', 'ImageCropper');

INSERT INTO document_type_dependency VALUES (2, 6, 5);
INSERT INTO document_type_dependency VALUES (4, 5, 4);

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


INSERT INTO property_value VALUES (11, 3, 6, 'Contact');
INSERT INTO property_value VALUES (12, 3, 7, 'Contact');
INSERT INTO property_value VALUES (13, 3, 8, 'Contact');
INSERT INTO property_value VALUES (14, 3, 9, 'Contact');
INSERT INTO property_value VALUES (30, 5, 35, 'First category - Blog');
INSERT INTO property_value VALUES (31, 5, 36, '');
INSERT INTO property_value VALUES (32, 5, 37, 'First category - Blog');
INSERT INTO property_value VALUES (16, 1, 16, 'Responsive? Really?');
INSERT INTO property_value VALUES (2, 1, 1, 'My website');
INSERT INTO property_value VALUES (15, 2, 15, 'About');
INSERT INTO property_value VALUES (6, 2, 5, '<p>
	Suspendisse faucibus dictum tellus id posuere. Cras quis eros tellus, id posuere lacus. Praesent ac est eros. Aliquam eleifend nunc ut neque consequat quis suscipit elit tincidunt. Fusce interdum gravida tincidunt. Sed et ante nec ligula fringilla condimentum. Mauris vel sem ac ligula sed lorem vestibulum ornare vel sed nibh. Fusce rhoncus tincidunt ante, non hendrerit magna vestibulum quis. Integer vel enim sem phasellus tempus lorem.</p>
<p>
	Maecenas vel mauris sit amet augue accumsan tempor non in sapien. Nulla facilisi. Aliquam imperdiet sem et orci adipiscing vehicula sagittis metus consectetur. Mauris a dui dolor magna tristique condimentum nec ut ligula. Sed eu arcu in neque porta iaculis. Mauris fermentum velit sit amet leo convallis consectetur. Ut vel libero dui, eu vehicula velit. Maecenas et justo vitae lacus venenatis vehicula. Ut eget posuere arcu dolore blandit.</p>
<p>
	Aenean nec turpis ac ligula posuere eleifend sed a eros. Integer nec nibh interdum nulla fringilla ultricies. Integer semper felis eu ante congue dignissim. Praesent rhoncus nulla sed felis rhoncus et iaculis risus mattis. Nam sed purus dui, a egestas enim. Donec facilisis, enim id fermentum mattis, tellus libero ultricies massa, eu bibendum augue ipsum sit amet ligula. Proin porta sagittis erat ac pharetra. Aenean ultricies enim id mi lacinia rhoncus. Nunc volutpat, turpis vel porta tincidunt, lectus metus fermentum lectus, sed sodales erat nisi non dolor. Pellentesque vitae felis eget turpis rutrum malesuada in non quam. Mauris at lorem vel nunc tristique scelerisque sit amet id nisl. Donec at nisi magna, in pretium ligula. Sed lacus augue, sagittis et gravida viverra, imperdiet eget elit. Morbi malesuada ligula. Proin porta sagittis erat ac pharetra. Aenean ultricies enim id mi lacinia rhoncus. Nunc volutpat, turpis vel porta tincidunt, lectus metus fermentum lectus, sed sodales erat imperdiet sem et orci adipiscing vehicula sagittis metus consectetur. Mauris a dui dolor magna tristique condimentum nec ut ligula. Sed eu arcu in neque porta iaculis. Mauris fermentum velit sit amet leo convallis consectetur. Ut vel libero dui, eu vehicula velit. Maecenas et justo nisi non dolor. Pellentesque vitae felis eget turpis rutrum malesuada in non quam. Mauris at lorem vel nunc tristique scelerisque sit amet id nisl. Donec at nisi magna, in pretium ligula. Sed lacus augue, sagittis et gravida viverra, imperdiet eget elit. Morbi malesuada mauris eu eros rutrum blandit. Cras malesuada rutrum sem, ac vehicula diam volutpat ac. Vestibulum sed tortor purus. Phasellus in lectus fringilla tellus porttitor vehicula ut fermentum metus lorem ipsum dolor sit amet lorem ipsum dolor sit amet nullam consequat lorem ipsum dolor sit amet consequat veroeros etiam.</p>');
INSERT INTO property_value VALUES (7, 2, 10, 'About');
INSERT INTO property_value VALUES (8, 2, 11, '');
INSERT INTO property_value VALUES (9, 2, 12, 'About');
INSERT INTO property_value VALUES (10, 2, 13, 'About');
INSERT INTO property_value VALUES (33, 5, 38, 'First category - Blog');
INSERT INTO property_value VALUES (34, 6, 35, 'Second category - Blog');
INSERT INTO property_value VALUES (25, 1, 25, 'Arcana: A responsive HTML5 site template by HTML5 UP');
INSERT INTO property_value VALUES (24, 1, 24, 'a:5:{s:5:"value";s:35:"/media/files/1/24/519dbb3abfdd8.jpg";s:5:"width";i:1200;s:6:"height";i:265;s:4:"html";i:2;s:4:"mime";s:10:"image/jpeg";}');
INSERT INTO property_value VALUES (20, 1, 20, 'Welcome to Arcana');
INSERT INTO property_value VALUES (21, 1, 21, '<p>
	This is <strong>Arcana</strong>, a fully responsive HTML5 site template designed by <a href="http://n33.co/">n33</a> and released for free by <a href="http://html5up.net/">HTML5 UP</a> It features a simple yet elegant design, solid HTML5 and CSS3 code, and full responsive support for desktop, tablet, and mobile displays.</p>');
INSERT INTO property_value VALUES (3, 1, 2, '');
INSERT INTO property_value VALUES (4, 1, 3, 'My website');
INSERT INTO property_value VALUES (41, 7, 26, '<p>
	Suspendisse faucibus dictum tellus id posuere. Cras quis eros tellus, id posuere lacus. Praesent ac est eros. Aliquam eleifend nunc ut neque consequat quis suscipit elit tincidunt. Fusce interdum gravida tincidunt. Sed et ante nec ligula fringilla condimentum. Mauris vel sem ac ligula sed lorem vestibulum ornare vel sed nibh. Fusce rhoncus tincidunt ante, non hendrerit magna vestibulum quis. Integer vel enim sem phasellus tempus lorem.</p>');
INSERT INTO property_value VALUES (42, 7, 27, 'b:0;');
INSERT INTO property_value VALUES (43, 7, 28, 'Two Column #2');
INSERT INTO property_value VALUES (44, 7, 29, '');
INSERT INTO property_value VALUES (45, 7, 30, 'Two Column #2');
INSERT INTO property_value VALUES (46, 7, 31, 'Two Column #2');
INSERT INTO property_value VALUES (47, 8, 32, 'Two Column #2 (left-hand sidebar)');
INSERT INTO property_value VALUES (48, 8, 33, '2013/05/25 02:33:09');
INSERT INTO property_value VALUES (51, 8, 27, 'b:0;');
INSERT INTO property_value VALUES (52, 8, 28, 'Two Column #2');
INSERT INTO property_value VALUES (53, 8, 29, '');
INSERT INTO property_value VALUES (5, 1, 4, 'My website');
INSERT INTO property_value VALUES (26, 4, 39, 'Blog');
INSERT INTO property_value VALUES (35, 6, 36, '');
INSERT INTO property_value VALUES (36, 6, 37, 'Second category - Blog');
INSERT INTO property_value VALUES (27, 4, 40, '');
INSERT INTO property_value VALUES (28, 4, 41, 'Blog');
INSERT INTO property_value VALUES (29, 4, 42, 'Blog');
INSERT INTO property_value VALUES (37, 6, 38, 'Second category - Blog');
INSERT INTO property_value VALUES (38, 7, 32, 'Two Column #2 (left-hand sidebar)');
INSERT INTO property_value VALUES (39, 7, 33, '2013/05/25 02:32:17');
INSERT INTO property_value VALUES (54, 8, 30, 'Two Column #2');
INSERT INTO property_value VALUES (55, 8, 31, 'Two Column #2');
INSERT INTO property_value VALUES (22, 1, 22, '<p>
	Yup. This site template is fully responsive, meaning it&#39;ll look great on desktop (widescreen and standard), tablet and mobile device displays. Try it for yourself: simply resize your browser window to see how stuff shifts around and changes.</p>');
INSERT INTO property_value VALUES (17, 1, 17, 'The CCA 3.0 License');
INSERT INTO property_value VALUES (23, 1, 23, '<p>
	<strong>Arcana</strong> is released for free under the <a href="http://html5up.net/license">Creative Commons Attribution 3.0 license</a>, which means you can use it for pretty much any personal or commercial use. The catch? Just give us credit wherever you use it (oh, and tell your friends about us, though that&#39;s not in the license :)</p>');
INSERT INTO property_value VALUES (18, 1, 18, 'About HTML5 UP');
INSERT INTO property_value VALUES (19, 1, 19, '<p>
	HTML5 UP is a small side project by AJ (aka n33) to practice working with HTML5 and responsive design techniques. You can find more cool designs at <a href="http://html5up.net/">html5up.net</a> or follow us on Twitter (<a href="http://twitter.com/n33co">@n33co</a>) for new release announcements and other cool stuff.</p>');
INSERT INTO property_value VALUES (40, 7, 34, '<p>
	Suspendisse faucibus dictum tellus id posuere. Cras quis eros tellus, id posuere lacus. Praesent ac est eros. Aliquam eleifend nunc ut neque consequat quis suscipit elit tincidunt. Fusce interdum gravida tincidunt. Sed et ante nec ligula fringilla condimentum. Mauris vel sem ac ligula sed lorem vestibulum ornare vel sed nibh. Fusce rhoncus tincidunt ante, non hendrerit magna vestibulum quis. Integer vel enim sem phasellus tempus lorem.</p>
<p>
	Maecenas vel mauris sit amet augue accumsan tempor non in sapien. Nulla facilisi. Aliquam imperdiet sem et orci adipiscing vehicula sagittis metus consectetur. Mauris a dui dolor magna tristique condimentum nec ut ligula. Sed eu arcu in neque porta iaculis. Mauris fermentum velit sit amet leo convallis consectetur. Ut vel libero dui, eu vehicula velit. Maecenas et justo vitae lacus venenatis vehicula. Ut eget posuere arcu dolore blandit.</p>
<p>
	Aenean nec turpis ac ligula posuere eleifend sed a eros. Integer nec nibh interdum nulla fringilla ultricies. Integer semper felis eu ante congue dignissim. Praesent rhoncus nulla sed felis rhoncus et iaculis risus mattis. Nam sed purus dui, a egestas enim. Donec facilisis, enim id fermentum mattis, tellus libero ultricies massa, eu bibendum augue ipsum sit amet ligula. Proin porta sagittis erat ac pharetra. Aenean ultricies enim id mi lacinia rhoncus. Nunc volutpat, turpis vel porta tincidunt, lectus metus fermentum lectus, sed sodales erat nisi non dolor. Pellentesque vitae felis eget turpis rutrum malesuada in non quam. Mauris at lorem vel nunc tristique scelerisque sit amet id nisl. Donec at nisi magna, in pretium ligula. Sed lacus augue, sagittis et gravida viverra, imperdiet eget elit. Morbi malesuada ligula. Proin porta sagittis erat ac pharetra. Aenean ultricies enim id mi lacinia rhoncus. Nunc volutpat, turpis vel porta tincidunt, lectus metus fermentum lectus, sed sodales erat imperdiet sem et orci adipiscing vehicula sagittis metus consectetur. Mauris a dui dolor magna tristique condimentum nec ut ligula. Sed eu arcu in neque porta iaculis. Mauris fermentum velit sit amet leo convallis consectetur. Ut vel libero dui, eu vehicula velit. Maecenas et justo nisi non dolor. Pellentesque vitae felis eget turpis rutrum malesuada in non quam. Mauris at lorem vel nunc tristique scelerisque sit amet id nisl. Donec at nisi magna, in pretium ligula. Sed lacus augue, sagittis et gravida viverra, imperdiet eget elit. Morbi malesuada mauris eu eros rutrum blandit. Cras malesuada rutrum sem, ac vehicula diam volutpat ac. Vestibulum sed tortor purus. Phasellus in lectus fringilla tellus porttitor vehicula ut fermentum metus lorem ipsum dolor sit amet lorem ipsum dolor sit amet nullam consequat lorem ipsum dolor sit amet consequat veroeros etiam.</p>');
INSERT INTO property_value VALUES (49, 8, 34, '<p>
	Suspendisse faucibus dictum tellus id posuere. Cras quis eros tellus, id posuere lacus. Praesent ac est eros. Aliquam eleifend nunc ut neque consequat quis suscipit elit tincidunt. Fusce interdum gravida tincidunt. Sed et ante nec ligula fringilla condimentum. Mauris vel sem ac ligula sed lorem vestibulum ornare vel sed nibh. Fusce rhoncus tincidunt ante, non hendrerit magna vestibulum quis. Integer vel enim sem phasellus tempus lorem.</p>
<p>
	Maecenas vel mauris sit amet augue accumsan tempor non in sapien. Nulla facilisi. Aliquam imperdiet sem et orci adipiscing vehicula sagittis metus consectetur. Mauris a dui dolor magna tristique condimentum nec ut ligula. Sed eu arcu in neque porta iaculis. Mauris fermentum velit sit amet leo convallis consectetur. Ut vel libero dui, eu vehicula velit. Maecenas et justo vitae lacus venenatis vehicula. Ut eget posuere arcu dolore blandit.</p>
<p>
	Aenean nec turpis ac ligula posuere eleifend sed a eros. Integer nec nibh interdum nulla fringilla ultricies. Integer semper felis eu ante congue dignissim. Praesent rhoncus nulla sed felis rhoncus et iaculis risus mattis. Nam sed purus dui, a egestas enim. Donec facilisis, enim id fermentum mattis, tellus libero ultricies massa, eu bibendum augue ipsum sit amet ligula. Proin porta sagittis erat ac pharetra. Aenean ultricies enim id mi lacinia rhoncus. Nunc volutpat, turpis vel porta tincidunt, lectus metus fermentum lectus, sed sodales erat nisi non dolor. Pellentesque vitae felis eget turpis rutrum malesuada in non quam. Mauris at lorem vel nunc tristique scelerisque sit amet id nisl. Donec at nisi magna, in pretium ligula. Sed lacus augue, sagittis et gravida viverra, imperdiet eget elit. Morbi malesuada ligula. Proin porta sagittis erat ac pharetra. Aenean ultricies enim id mi lacinia rhoncus. Nunc volutpat, turpis vel porta tincidunt, lectus metus fermentum lectus, sed sodales erat imperdiet sem et orci adipiscing vehicula sagittis metus consectetur. Mauris a dui dolor magna tristique condimentum nec ut ligula. Sed eu arcu in neque porta iaculis. Mauris fermentum velit sit amet leo convallis consectetur. Ut vel libero dui, eu vehicula velit. Maecenas et justo nisi non dolor. Pellentesque vitae felis eget turpis rutrum malesuada in non quam. Mauris at lorem vel nunc tristique scelerisque sit amet id nisl. Donec at nisi magna, in pretium ligula. Sed lacus augue, sagittis et gravida viverra, imperdiet eget elit. Morbi malesuada mauris eu eros rutrum blandit. Cras malesuada rutrum sem, ac vehicula diam volutpat ac. Vestibulum sed tortor purus. Phasellus in lectus fringilla tellus porttitor vehicula ut fermentum metus lorem ipsum dolor sit amet lorem ipsum dolor sit amet nullam consequat lorem ipsum dolor sit amet consequat veroeros etiam.</p>');
INSERT INTO property_value VALUES (50, 8, 26, '<p>
	Suspendisse faucibus dictum tellus id posuere. Cras quis eros tellus, id posuere lacus. Praesent ac est eros. Aliquam eleifend nunc ut neque consequat quis suscipit elit tincidunt. Fusce interdum gravida tincidunt. Sed et ante nec ligula fringilla condimentum. Mauris vel sem ac ligula sed lorem vestibulum ornare vel sed nibh. Fusce rhoncus tincidunt ante, non hendrerit magna vestibulum quis. Integer vel enim sem phasellus tempus lorem.</p>');


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
        $mail = new \\Gc\\Mail(''utf-8'', $message);
        $mail->setFrom($email, $name);
        $mail->addTo(\\Gc\\Core\\Config::getValue(''mail_from''));
        $mail->send();
        $this->flashMessenger()->addSuccessMessage(''Message sent'');
        $this->redirect()->toUrl(''/contact'');
        return TRUE;
    }
}
', 'Contact ');


