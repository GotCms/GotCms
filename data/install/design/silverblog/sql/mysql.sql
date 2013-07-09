set foreign_key_checks=0;

INSERT INTO view VALUES (6, '2012-10-25 19:57:05', '2013-06-02 12:22:18.393061', 'Flash messages', 'flash-messages', '<?php if(!empty($this->layout()->flashMessages)): ?>
    <?php foreach($this->layout()->flashMessages as $type => $messages):?>
        <?php foreach($messages as $message): ?>
            <div class="notification <?php echo $type; ?>">
             <?php echo $this->escapeHtml($this->translate($message)); ?>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
<?php endif; ?>', 'Flash messages displayer');
INSERT INTO view VALUES (4, '2012-09-19 19:33:51', '2013-06-02 12:22:18.425809', 'One column', 'one-column', '<div id="leftcontainer">
    <h2 class="mainheading"><?php echo $this->escapeHtml($this->title); ?></h2>
    <article class="post">
        <?php echo $this->content; ?>
    </article>
</div>
', 'One column page');
INSERT INTO view VALUES (3, '2012-09-19 19:32:56', '2013-06-02 12:22:18.37399', 'Contact', 'contact', '<div id="contents">
    <section id="main">
        <div id="leftcontainer">
            <h2>Contact Me</h2>
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
    </section>
</div>
', 'Contact form');
INSERT INTO view VALUES (7, '2013-05-20 15:52:38.647561', '2013-06-02 12:22:18.442743', 'Paginator', 'paginator', '<div class="wp-pagenavi">
    <?php if ($this->pageCount): ?>
        <p class="clearfix">
            <?php if (isset($this->previous)): ?>
                <a class="float right" href="<?php echo $this->escapeHtml($this->path); ?>?page=<?php echo $this->previous; ?>">
                    Newer
                </a>
            <?php endif; ?>

            <?php foreach ($this->pagesInRange as $page): ?>
                <?php if ($page != $this->current): ?>
                    <a href="<?php echo $this->escapeHtml($this->path); ?>?page=<?php echo $page; ?>">
                        <?php echo $page; ?>
                    </a>
                <?php else: ?>
                    <span class="current"><?php echo $page; ?></span>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if (isset($this->next)): ?>
                <a class="float" href="<?php echo $this->escapeHtml($this->path); ?>?page=<?php echo $this->next; ?>">
                    Previous
                </a>
            <?php endif; ?>
        </p>
    <?php endif; ?>
</div>

', 'Paginator control');
INSERT INTO view VALUES (12, '2013-05-27 08:55:24.70931', '2013-06-02 12:22:18.464786', 'Sidebar', 'sidebar', '<div id="sidebarwrap">
    <?php $sidebarElements = $this->tools(''unserialize'', $this->currentDocument->getProperty(''sidebarElements'')->getValue()); ?>
    <?php if (empty($sidebarElements)): //Take home value if page does not have one ?>
        <?php $sidebarElements = $this->tools(''unserialize'', $this->document()->getProperty(''sidebarElements'')->getValue()); ?>
    <?php endif; ?>

    <?php if (!empty($sidebarElements)): ?>
        <?php foreach($sidebarElements as $element): ?>
            <h2><?php echo $this->escapeHtml($element[0][''value'']); ?></h2>
            <?php echo $element[1][''value'']; ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php $blogChildren = $this->document('''')->getAvailableChildren(); ?>
    <h2>Categories</h2>
    <ul>
        <?php foreach($blogChildren as $child): ?>
            <?php $nbPosts = count($child->getAvailableChildren()); ?>
            <li><a href="<?php echo $this->escapeHtml($child->getUrl()); ?>"><?php echo $this->escapeHtml($child->getName()); ?></a>(<?php echo $nbPosts; ?>)</li>
        <?php endforeach; ?>
    </ul>

    <?php
    $posts = $blogChildren;
    foreach ($posts as $child) {
        $children = $child->getChildren();
        if (!empty($children)) {
            foreach ($children as $child) {
                $posts[] = $child;
            }
        }
    }

    foreach ($posts as $idx => $child) {
        if ($child->getView()->getIdentifier() != ''blog-ticket'') {
            unset($posts[$idx]);
        }
    }

    if (!function_exists(''sortObjects'')) {
        function sortObjects($a, $b) {
            $aPublishedAt = $a->getProperty(''published_at'')->getValue();
            $bPublishedAt = $b->getProperty(''published_at'')->getValue();
            if ($aPublishedAt == $bPublishedAt) {
                return 0;
            }

            return ($aPublishedAt > $bPublishedAt) ? -1 : 1;
        }
    }

    usort($posts, ''sortObjects'');
    ?>
    <h2>Latest Posts</h2>
    <ul>
        <?php foreach($posts as $idx => $post): ?>
            <li><a href="<?php echo $this->escapeHtml($post->getUrl()); ?>"><?php echo $this->escapeHtml($post->getProperty(''title'')->getValue()); ?></a></li>
            <?php if ($idx >= 8): ?>
                <?php break; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
</div>
', 'Sidebar');
INSERT INTO view VALUES (11, '2013-05-24 19:02:58.584746', '2013-06-02 12:22:18.347423', 'Blog ticket', 'blog-ticket', '<div class="subpage" id="main-wrapper">
    <div class="container">
        <div class="row">
            <?php echo $this->partial(''blog-categories''); ?>
            <div id="leftcontainer">
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
                    <h2 class="mainheading"><?php echo $this->escapeHtml($this->title); ?></h2>
                    <article class="post">
                        <div>
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
                        </div>
                        <footer>
                            <span class="author"><?php echo $this->escapeHtml($this->authorName); ?></span>
                            <span class="permalink"><a href="<?php echo $this->escapeHtml($this->document('''')->getUrl()); ?>" class="float">Back to Blog</a></span>
                            <span class="comments"><a href="#comment_form" class="float right">Discuss this post</a></span>
                        </footer>
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
INSERT INTO view VALUES (1, '2012-09-19 19:29:04', '2013-06-02 12:22:18.410393', 'Home page', 'home', '<?php if (!empty($this->image)): ?>
    <section id="featured">
        <h2 class="ftheading">Featured</h2>
        <div class="ftwrap">
            <div class="ftimg">
                <img src="<?php echo $this->image[''value'']; ?>" width="<?php echo $this->image[''width'']; ?>" height="<?php echo $this->image[''height'']; ?>" alt="">
            </div>
            <div class="fttxt"><?php echo $this->content; ?></div>
        </div>
    </section>
<?php endif; ?>
<div id="leftcontainer">
    <h2 class="mainheading">Latest from the blog</h2>
    <?php
    $comment_table = new \\Modules\\Blog\\Model\\Comment();
    $posts = $this->currentDocument->getAvailableChildren();
    foreach ($posts as $child) {
        $children = $child->getChildren();
        if(!empty($children)) {
            foreach($children as $child) {
                $posts[] = $child;
            }
        }
    }

    foreach($posts as $idx => $child) {
        if($child->getView()->getIdentifier() != ''blog-ticket'') {
            unset($posts[$idx]);
        }
    }

    function sortObjects($a, $b) {
        $aPublishedAt = $a->getProperty(''published_at'')->getValue();
        $bPublishedAt = $b->getProperty(''published_at'')->getValue();
        if ($aPublishedAt == $bPublishedAt) {
            return 0;
        }

        return ($aPublishedAt > $bPublishedAt) ? -1 : 1;
    }

    usort($posts, ''sortObjects'');

    $paginator = new \\Zend\\Paginator\\Paginator(new \\Zend\\Paginator\\Adapter\\ArrayAdapter($posts));
    $paginator->setItemCountPerPage(5);
    $paginator->setCurrentPageNumber(empty($_GET[''page'']) ? 1 : (int)$_GET[''page'']);
    ?>

    <?php if(!empty($posts)): ?>
        <?php foreach($paginator as $post): ?>
            <article class="post">
                <header>
                    <h3><a href="<?php echo $this->escapeHtml($post->getUrl()); ?>"><?php echo $this->escapeHtml($post->getProperty(''title'')->getValue()); ?></a></h3>
                    <p class="postinfo">
                        <?php $parent = $post->getParent(); ?>
                        Published on <time><?php echo $this->escapeHtml(date(''Y-m-d'', strtotime($post->getProperty(''published_at'')->getValue()))); ?></time> under <a href="<?php echo $this->escapeHtml($parent->getUrl()); ?>"><?php echo $this->escapeHtml($parent->getName()); ?></a>
                    </p>
                </header>

                <?php echo $post->getProperty(''shortContent'')->getValue(); ?>

                <footer>
                    <span class="author"><?php echo $this->escapeHtml($post->getProperty(''authorName'')->getValue()); ?></span>
                    <span class="permalink"><a href="<?php echo $this->escapeHtml($post->getUrl()); ?>">Read Full</a></span>
                    <?php $comments = $comment_table->getList($post->getId()); ?>
                    <?php $nb_comments = count($comments); ?>
                    <?php if(!empty($nb_comments)): ?>
                        <span class="comments">
                             &bull; <a href="<?php echo $this->escapeHtml($post->getUrl()); ?>#comments_list">
                                <?php if($nb_comments == 1): ?>
                                    1 Comment
                                <?php else: ?>
                                    <?php echo $nb_comments; ?> Comments
                                <?php endif; ?>
                            </a>
                        </span>
                    <?php endif; ?>
                </footer>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php echo $this->paginationControl($paginator, ''sliding'', ''paginator'', array(''path'' => $this->currentDocument->getUrl()));?>
    <div class="clear"></div>
</div>
', 'Home page content');


INSERT INTO document_type VALUES (5, '2013-05-24 08:56:17.202506', '2013-06-02 11:28:48.731862', 'Blog category', 'Blog category', 8, 1, 1);
INSERT INTO document_type VALUES (4, '2013-05-24 08:49:53.537851', '2013-06-02 11:29:27.171857', 'Blog ticket', 'Ticket blog', 6, 11, 1);
INSERT INTO document_type VALUES (2, '2012-09-20 22:05:53', '2013-06-02 11:29:45.535129', 'Contact', 'Contact form', 11, 3, 1);
INSERT INTO document_type VALUES (3, '2012-09-20 22:06:37', '2013-06-02 11:58:48.587983', 'About', 'About this website', 13, 4, 1);
INSERT INTO document_type VALUES (6, '2013-05-24 08:57:15.489668', '2013-06-02 12:06:43.23561', 'Blog', 'Blog', 24, 1, 1);


INSERT INTO layout VALUES (1, '2012-09-19 19:28:34', '2013-06-02 12:26:50.037182', 'Main', 'main', '<?php use Gc\\Core\\Config; ?>
<!doctype html>

<html lang="en-US">
<head>
<meta charset="UTF-8" />
<title><?php echo $this->escapeHtml($this->pageTitle); ?> - <?php echo $this->escapeHtml($this->config()->get(''site_name'')); ?></title>
<link href="<?php echo $this->cdn(''/frontend/css/style.css''); ?>" rel="stylesheet" type="text/css">
<!--[if IE]>
<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<!--[if IE 6]>
<script src="<?php echo $this->cdn(''/frontend/js/belatedPNG.js''); ?>"></script>
<script>
    DD_belatedPNG.fix(''*'');
</script>
<![endif]-->
<script src="<?php echo $this->cdn(''/frontend/js/jquery-1.4.min.js''); ?>" type="text/javascript" charset="utf-8"></script>
</head>
<body>
<div id="bodywrap">
    <section id="pagetop">
        <nav id="sitenav">
            <?php
                $component = new \\Gc\\Component\\Navigation();
                $container = new \\Zend\\Navigation\\Navigation($component->render());
                $this->navigation($container);
                $document = $this->layout()->currentDocument;

                echo $this->navigation()->menu()->setMaxDepth(0)->setUlClass(''sf-menu navigation'');
            ?>
        </nav>
    </section>

    <header id="pageheader">
        <h1><a href="<?php echo $this->document()->getUrl(); ?>"><?php echo $this->escapeHtml($this->config()->get(''site_name'')); ?></a></h1>
    </header>
    <div id="contents">
        <section id="main">
            <?php echo $this->content; ?>
        </section>
        <section id="sidebar">
            <?php echo $this->partial(''sidebar'', array(''currentDocument'' => $this->currentDocument)); ?>
        </section>
        <div class="clear"></div>
    </div>
</div>
<footer id="pagefooter">
    <div id="footerwrap">
        <div class="copyright">
            <?php echo date(''Y''); ?> &copy; <?php echo $this->escapeHtml($this->config()->get(''site_name'')); ?>
        </div>
        <div class="credit">
            <a href="http://cssheaven.org" title="Downlaod Free CSS Templates">Website Template</a> by CSSHeaven.org
        </div>
    </div>
</footer>
</body>
</html>
', 'Main layout');


INSERT INTO document VALUES (3, '2012-09-20 22:09:29', '2013-05-25 13:58:10.973436', 'Contact', 'contact', 1, 3, true, 1, 2, 3, 1, NULL);
INSERT INTO document VALUES (7, '2013-05-25 14:00:54.412118', '2013-05-26 13:32:18.374819', 'Article 1', 'article-about-something', 1, 0, false, 1, 4, 11, 1, 5);
INSERT INTO document VALUES (8, '2013-05-25 14:01:26.09229', '2013-05-26 13:32:27.671487', 'Article 2', 'article-about-something', 1, 0, false, 1, 4, 11, 1, 6);
INSERT INTO document VALUES (5, '2013-05-25 13:58:49.09857', '2013-05-27 08:53:50.482773', 'First category', 'first-category', 1, 0, true, 1, 5, 1, 1, 4);
INSERT INTO document VALUES (6, '2013-05-25 13:59:05.99698', '2013-05-27 08:53:53.785471', 'Second category', 'second-category', 1, 0, true, 1, 5, 1, 1, 4);
INSERT INTO document VALUES (2, '2012-09-20 22:09:06', '2013-06-02 12:00:33.863005', 'About', 'about', 1, 2, true, 1, 3, 4, 1, NULL);
INSERT INTO document VALUES (4, '2013-05-25 13:57:47.516331', '2013-06-02 12:07:47.590756', 'Home', '', 1, 1, true, 1, 6, 1, 1, NULL);


INSERT INTO datatype VALUES (1, 'Text field', 'N;', 'Textstring');
INSERT INTO datatype VALUES (2, 'Rich text', 'a:1:{s:13:"toolbar-items";a:67:{s:6:"Source";s:1:"1";s:4:"Save";s:1:"1";s:7:"NewPage";s:1:"1";s:8:"DocProps";s:1:"1";s:7:"Preview";s:1:"1";s:5:"Print";s:1:"1";s:9:"Templates";s:1:"1";s:3:"Cut";s:1:"1";s:4:"Copy";s:1:"1";s:5:"Paste";s:1:"1";s:9:"PasteText";s:1:"1";s:13:"PasteFromWord";s:1:"1";s:4:"Undo";s:1:"1";s:4:"Redo";s:1:"1";s:4:"Find";s:1:"1";s:7:"Replace";s:1:"1";s:9:"SelectAll";s:1:"1";s:12:"SpellChecker";s:1:"1";s:5:"Scayt";s:1:"1";s:4:"Form";s:1:"1";s:8:"Checkbox";s:1:"1";s:5:"Radio";s:1:"1";s:9:"TextField";s:1:"1";s:8:"Textarea";s:1:"1";s:6:"Select";s:1:"1";s:6:"Button";s:1:"1";s:11:"ImageButton";s:1:"1";s:11:"HiddenField";s:1:"1";s:4:"Bold";s:1:"1";s:6:"Italic";s:1:"1";s:9:"Underline";s:1:"1";s:6:"Strike";s:1:"1";s:9:"Subscript";s:1:"1";s:11:"Superscript";s:1:"1";s:12:"RemoveFormat";s:1:"1";s:12:"NumberedList";s:1:"1";s:12:"BulletedList";s:1:"1";s:7:"Outdent";s:1:"1";s:6:"Indent";s:1:"1";s:10:"Blockquote";s:1:"1";s:9:"CreateDiv";s:1:"1";s:11:"JustifyLeft";s:1:"1";s:13:"JustifyCenter";s:1:"1";s:12:"JustifyRight";s:1:"1";s:12:"JustifyBlock";s:1:"1";s:7:"BidiLtr";s:1:"1";s:7:"BidiRtl";s:1:"1";s:4:"Link";s:1:"1";s:6:"Unlink";s:1:"1";s:6:"Anchor";s:1:"1";s:5:"Image";s:1:"1";s:5:"Flash";s:1:"1";s:5:"Table";s:1:"1";s:14:"HorizontalRule";s:1:"1";s:6:"Smiley";s:1:"1";s:11:"SpecialChar";s:1:"1";s:9:"PageBreak";s:1:"1";s:6:"Iframe";s:1:"1";s:6:"Styles";s:1:"1";s:6:"Format";s:1:"1";s:4:"Font";s:1:"1";s:8:"FontSize";s:1:"1";s:9:"TextColor";s:1:"1";s:7:"BGColor";s:1:"1";s:8:"Maximize";s:1:"1";s:10:"ShowBlocks";s:1:"1";s:5:"About";s:1:"1";}}', 'Textrich');
INSERT INTO datatype VALUES (3, 'Text area', 'a:3:{s:4:"cols";s:2:"50";s:4:"rows";s:2:"30";s:4:"wrap";s:4:"hard";}', 'Textarea');
INSERT INTO datatype VALUES (4, 'Simple Image', 'a:2:{s:9:"mime_list";a:3:{i:0;s:9:"image/gif";i:1;s:10:"image/jpeg";i:2;s:9:"image/png";}s:11:"is_multiple";b:0;}', 'Upload');
INSERT INTO datatype VALUES (6, 'Datepicker', 'N;', 'DatePicker');
INSERT INTO datatype VALUES (7, 'ImageCropperRelay', 'a:4:{s:10:"background";s:7:"#FFFFFF";s:13:"resize_option";s:4:"auto";s:9:"mime_list";a:3:{i:0;s:9:"image/gif";i:1;s:10:"image/jpeg";i:2;s:9:"image/png";}s:4:"size";a:1:{i:0;a:3:{s:4:"name";s:7:"570x150";s:5:"width";s:3:"570";s:6:"height";s:3:"150";}}}', 'ImageCropper');
INSERT INTO datatype VALUES (8, 'Sidebar text', 'a:1:{s:9:"datatypes";a:2:{i:0;a:3:{s:4:"name";s:10:"Textstring";s:5:"label";s:5:"Title";s:6:"config";a:1:{s:6:"length";s:0:"";}}i:1;a:3:{s:4:"name";s:8:"Textrich";s:5:"label";s:7:"Content";s:6:"config";a:1:{s:13:"toolbar-items";a:67:{s:6:"Source";s:1:"1";s:4:"Save";s:1:"1";s:7:"NewPage";s:1:"1";s:8:"DocProps";s:1:"1";s:7:"Preview";s:1:"1";s:5:"Print";s:1:"1";s:9:"Templates";s:1:"1";s:3:"Cut";s:1:"1";s:4:"Copy";s:1:"1";s:5:"Paste";s:1:"1";s:9:"PasteText";s:1:"1";s:13:"PasteFromWord";s:1:"1";s:4:"Undo";s:1:"1";s:4:"Redo";s:1:"1";s:4:"Find";s:1:"1";s:7:"Replace";s:1:"1";s:9:"SelectAll";s:1:"1";s:12:"SpellChecker";s:1:"1";s:5:"Scayt";s:1:"1";s:4:"Form";s:1:"1";s:8:"Checkbox";s:1:"1";s:5:"Radio";s:1:"1";s:9:"TextField";s:1:"1";s:8:"Textarea";s:1:"1";s:6:"Select";s:1:"1";s:6:"Button";s:1:"1";s:11:"ImageButton";s:1:"1";s:11:"HiddenField";s:1:"1";s:4:"Bold";s:1:"1";s:6:"Italic";s:1:"1";s:9:"Underline";s:1:"1";s:6:"Strike";s:1:"1";s:9:"Subscript";s:1:"1";s:11:"Superscript";s:1:"1";s:12:"RemoveFormat";s:1:"1";s:12:"NumberedList";s:1:"1";s:12:"BulletedList";s:1:"1";s:7:"Outdent";s:1:"1";s:6:"Indent";s:1:"1";s:10:"Blockquote";s:1:"1";s:9:"CreateDiv";s:1:"1";s:11:"JustifyLeft";s:1:"1";s:13:"JustifyCenter";s:1:"1";s:12:"JustifyRight";s:1:"1";s:12:"JustifyBlock";s:1:"1";s:7:"BidiLtr";s:1:"1";s:7:"BidiRtl";s:1:"1";s:4:"Link";s:1:"1";s:6:"Unlink";s:1:"1";s:6:"Anchor";s:1:"1";s:5:"Image";s:1:"1";s:5:"Flash";s:1:"1";s:5:"Table";s:1:"1";s:14:"HorizontalRule";s:1:"1";s:6:"Smiley";s:1:"1";s:11:"SpecialChar";s:1:"1";s:9:"PageBreak";s:1:"1";s:6:"Iframe";s:1:"1";s:6:"Styles";s:1:"1";s:6:"Format";s:1:"1";s:4:"Font";s:1:"1";s:8:"FontSize";s:1:"1";s:9:"TextColor";s:1:"1";s:7:"BGColor";s:1:"1";s:8:"Maximize";s:1:"1";s:10:"ShowBlocks";s:1:"1";s:5:"About";s:1:"1";}}}}}', 'Mixed');
INSERT INTO datatype VALUES (9, 'Upload', 'a:2:{s:9:"mime_list";a:3:{i:0;s:9:"image/gif";i:1;s:10:"image/jpeg";i:2;s:9:"image/png";}s:11:"is_multiple";b:0;}', 'Upload');


INSERT INTO document_type_dependency VALUES (8, 5, 4);
INSERT INTO document_type_dependency VALUES (10, 6, 5);


INSERT INTO tab VALUES (17, 'Sidebar', 'Sidebar text', 1, 5);
INSERT INTO tab VALUES (14, 'Title and meta', 'Meta description', 2, 5);
INSERT INTO tab VALUES (18, 'Sidebar', 'Sidebar text', 1, 4);
INSERT INTO tab VALUES (11, 'Content', 'Content', 2, 4);
INSERT INTO tab VALUES (12, 'Relay', 'Relay', 3, 4);
INSERT INTO tab VALUES (13, 'Title and meta', 'Meta description', 4, 4);
INSERT INTO tab VALUES (7, 'Title and meta', 'Meta description', 1, 2);
INSERT INTO tab VALUES (19, 'Sidebar', 'Sidebar text', 2, 2);
INSERT INTO tab VALUES (4, 'Content', 'Content', 1, 3);
INSERT INTO tab VALUES (20, 'Sidebar', 'Sidebar text', 2, 3);
INSERT INTO tab VALUES (8, 'Title and meta', 'Meta description', 3, 3);
INSERT INTO tab VALUES (21, 'Featured', 'Featured content', 1, 6);
INSERT INTO tab VALUES (16, 'Sidebar', 'Sidebar text', 2, 6);
INSERT INTO tab VALUES (15, 'Title and meta', 'Meta description', 3, 6);

INSERT INTO property VALUES (10, 'Meta description', 'metaDescription', 'Description', false, 3, 8, 1);
INSERT INTO property VALUES (11, 'Keywords', 'metaKeywords', 'Keywords', false, 4, 8, 1);
INSERT INTO property VALUES (12, 'Title', 'pageTitle', 'Title', false, 5, 8, 1);
INSERT INTO property VALUES (13, 'Main Title', 'mainTitle', 'Title', false, 6, 8, 1);
INSERT INTO property VALUES (48, 'Elements', 'sidebarElements', '', false, 7, 20, 8);
INSERT INTO property VALUES (49, 'Image', 'image', '', false, 1, 21, 9);
INSERT INTO property VALUES (50, 'Content', 'content', '', false, 2, 21, 2);
INSERT INTO property VALUES (44, 'Elements', 'sidebarElements', '', false, 3, 16, 8);
INSERT INTO property VALUES (39, 'Meta description', 'metaDescription', 'Description', false, 4, 15, 1);
INSERT INTO property VALUES (40, 'Keywords', 'metaKeywords', 'Keywords', false, 5, 15, 1);
INSERT INTO property VALUES (35, 'Meta description', 'metaDescription', 'Description', false, 1, 14, 1);
INSERT INTO property VALUES (36, 'Keywords', 'metaKeywords', 'Keywords', false, 2, 14, 1);
INSERT INTO property VALUES (37, 'Title', 'pageTitle', 'Title', false, 3, 14, 1);
INSERT INTO property VALUES (38, 'Main Title', 'mainTitle', 'Title', false, 4, 14, 1);
INSERT INTO property VALUES (45, 'Elements', 'sidebarElements', '', false, 5, 17, 8);
INSERT INTO property VALUES (32, 'Title', 'title', '', false, 1, 11, 1);
INSERT INTO property VALUES (43, 'Author name', 'authorName', '', false, 2, 11, 1);
INSERT INTO property VALUES (33, 'Publication date', 'published_at', '', false, 3, 11, 6);
INSERT INTO property VALUES (34, 'Content', 'content', '', false, 4, 11, 2);
INSERT INTO property VALUES (26, 'Short content', 'shortContent', '', false, 5, 12, 2);
INSERT INTO property VALUES (27, 'Image', 'image', '', false, 6, 12, 7);
INSERT INTO property VALUES (28, 'Meta description', 'metaDescription', 'Description', false, 7, 13, 1);
INSERT INTO property VALUES (29, 'Keywords', 'metaKeywords', 'Keywords', false, 8, 13, 1);
INSERT INTO property VALUES (30, 'Title', 'pageTitle', 'Title', false, 9, 13, 1);
INSERT INTO property VALUES (31, 'Main Title', 'mainTitle', 'Title', false, 10, 13, 1);
INSERT INTO property VALUES (46, 'Elements', 'sidebarElements', '', false, 11, 18, 8);
INSERT INTO property VALUES (6, 'Meta description', 'metaDescription', 'Description', false, 1, 7, 1);
INSERT INTO property VALUES (7, 'Keywords', 'metaKeywords', 'Keywords', false, 2, 7, 1);
INSERT INTO property VALUES (8, 'Title', 'pageTitle', 'Title', false, 3, 7, 1);
INSERT INTO property VALUES (9, 'Main Title', 'mainTitle', 'Title', false, 4, 7, 1);
INSERT INTO property VALUES (47, 'Elements', 'sidebarElements', '', false, 5, 19, 8);
INSERT INTO property VALUES (15, 'Title', 'title', 'Title', false, 1, 4, 1);
INSERT INTO property VALUES (5, 'Content', 'content', 'content', false, 2, 4, 2);
INSERT INTO property VALUES (41, 'Title', 'pageTitle', 'Title', false, 6, 15, 1);
INSERT INTO property VALUES (42, 'Main Title', 'mainTitle', 'Title', false, 7, 15, 1);


INSERT INTO property_value VALUES (11, 3, 6, 'Contact');
INSERT INTO property_value VALUES (12, 3, 7, 'Contact');
INSERT INTO property_value VALUES (13, 3, 8, 'Contact');
INSERT INTO property_value VALUES (14, 3, 9, 'Contact');
INSERT INTO property_value VALUES (41, 7, 26, '<p>
    Suspendisse faucibus dictum tellus id posuere. Cras quis eros tellus, id posuere lacus. Praesent ac est eros. Aliquam eleifend nunc ut neque consequat quis suscipit elit tincidunt. Fusce interdum gravida tincidunt. Sed et ante nec ligula fringilla condimentum. Mauris vel sem ac ligula sed lorem vestibulum ornare vel sed nibh. Fusce rhoncus tincidunt ante, non hendrerit magna vestibulum quis. Integer vel enim sem phasellus tempus lorem.</p>
');
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
INSERT INTO property_value VALUES (38, 7, 32, 'Two Column #2 (left-hand sidebar)');
INSERT INTO property_value VALUES (39, 7, 33, '2013/05/25 02:32:17');
INSERT INTO property_value VALUES (54, 8, 30, 'Two Column #2');
INSERT INTO property_value VALUES (55, 8, 31, 'Two Column #2');
INSERT INTO property_value VALUES (40, 7, 34, '<p>
    Suspendisse faucibus dictum tellus id posuere. Cras quis eros tellus, id posuere lacus. Praesent ac est eros. Aliquam eleifend nunc ut neque consequat quis suscipit elit tincidunt. Fusce interdum gravida tincidunt. Sed et ante nec ligula fringilla condimentum. Mauris vel sem ac ligula sed lorem vestibulum ornare vel sed nibh. Fusce rhoncus tincidunt ante, non hendrerit magna vestibulum quis. Integer vel enim sem phasellus tempus lorem.</p>
<p>
    Maecenas vel mauris sit amet augue accumsan tempor non in sapien. Nulla facilisi. Aliquam imperdiet sem et orci adipiscing vehicula sagittis metus consectetur. Mauris a dui dolor magna tristique condimentum nec ut ligula. Sed eu arcu in neque porta iaculis. Mauris fermentum velit sit amet leo convallis consectetur. Ut vel libero dui, eu vehicula velit. Maecenas et justo vitae lacus venenatis vehicula. Ut eget posuere arcu dolore blandit.</p>
<p>
    Aenean nec turpis ac ligula posuere eleifend sed a eros. Integer nec nibh interdum nulla fringilla ultricies. Integer semper felis eu ante congue dignissim. Praesent rhoncus nulla sed felis rhoncus et iaculis risus mattis. Nam sed purus dui, a egestas enim. Donec facilisis, enim id fermentum mattis, tellus libero ultricies massa, eu bibendum augue ipsum sit amet ligula. Proin porta sagittis erat ac pharetra. Aenean ultricies enim id mi lacinia rhoncus. Nunc volutpat, turpis vel porta tincidunt, lectus metus fermentum lectus, sed sodales erat nisi non dolor. Pellentesque vitae felis eget turpis rutrum malesuada in non quam. Mauris at lorem vel nunc tristique scelerisque sit amet id nisl. Donec at nisi magna, in pretium ligula. Sed lacus augue, sagittis et gravida viverra, imperdiet eget elit. Morbi malesuada ligula. Proin porta sagittis erat ac pharetra. Aenean ultricies enim id mi lacinia rhoncus. Nunc volutpat, turpis vel porta tincidunt, lectus metus fermentum lectus, sed sodales erat imperdiet sem et orci adipiscing vehicula sagittis metus consectetur. Mauris a dui dolor magna tristique condimentum nec ut ligula. Sed eu arcu in neque porta iaculis. Mauris fermentum velit sit amet leo convallis consectetur. Ut vel libero dui, eu vehicula velit. Maecenas et justo nisi non dolor. Pellentesque vitae felis eget turpis rutrum malesuada in non quam. Mauris at lorem vel nunc tristique scelerisque sit amet id nisl. Donec at nisi magna, in pretium ligula. Sed lacus augue, sagittis et gravida viverra, imperdiet eget elit. Morbi malesuada mauris eu eros rutrum blandit. Cras malesuada rutrum sem, ac vehicula diam volutpat ac. Vestibulum sed tortor purus. Phasellus in lectus fringilla tellus porttitor vehicula ut fermentum metus lorem ipsum dolor sit amet lorem ipsum dolor sit amet nullam consequat lorem ipsum dolor sit amet consequat veroeros etiam.</p>
');
INSERT INTO property_value VALUES (15, 2, 15, 'About');
INSERT INTO property_value VALUES (6, 2, 5, '<p>
    Suspendisse faucibus dictum tellus id posuere. Cras quis eros tellus, id posuere lacus. Praesent ac est eros. Aliquam eleifend nunc ut neque consequat quis suscipit elit tincidunt. Fusce interdum gravida tincidunt. Sed et ante nec ligula fringilla condimentum. Mauris vel sem ac ligula sed lorem vestibulum ornare vel sed nibh. Fusce rhoncus tincidunt ante, non hendrerit magna vestibulum quis. Integer vel enim sem phasellus tempus lorem.</p>
<p>
    Maecenas vel mauris sit amet augue accumsan tempor non in sapien. Nulla facilisi. Aliquam imperdiet sem et orci adipiscing vehicula sagittis metus consectetur. Mauris a dui dolor magna tristique condimentum nec ut ligula. Sed eu arcu in neque porta iaculis. Mauris fermentum velit sit amet leo convallis consectetur. Ut vel libero dui, eu vehicula velit. Maecenas et justo vitae lacus venenatis vehicula. Ut eget posuere arcu dolore blandit.</p>
<p>
    Aenean nec turpis ac ligula posuere eleifend sed a eros. Integer nec nibh interdum nulla fringilla ultricies. Integer semper felis eu ante congue dignissim. Praesent rhoncus nulla sed felis rhoncus et iaculis risus mattis. Nam sed purus dui, a egestas enim. Donec facilisis, enim id fermentum mattis, tellus libero ultricies massa, eu bibendum augue ipsum sit amet ligula. Proin porta sagittis erat ac pharetra. Aenean ultricies enim id mi lacinia rhoncus. Nunc volutpat, turpis vel porta tincidunt, lectus metus fermentum lectus, sed sodales erat nisi non dolor. Pellentesque vitae felis eget turpis rutrum malesuada in non quam. Mauris at lorem vel nunc tristique scelerisque sit amet id nisl. Donec at nisi magna, in pretium ligula. Sed lacus augue, sagittis et gravida viverra, imperdiet eget elit. Morbi malesuada ligula. Proin porta sagittis erat ac pharetra. Aenean ultricies enim id mi lacinia rhoncus. Nunc volutpat, turpis vel porta tincidunt, lectus metus fermentum lectus, sed sodales erat imperdiet sem et orci adipiscing vehicula sagittis metus consectetur. Mauris a dui dolor magna tristique condimentum nec ut ligula. Sed eu arcu in neque porta iaculis. Mauris fermentum velit sit amet leo convallis consectetur. Ut vel libero dui, eu vehicula velit. Maecenas et justo nisi non dolor. Pellentesque vitae felis eget turpis rutrum malesuada in non quam. Mauris at lorem vel nunc tristique scelerisque sit amet id nisl. Donec at nisi magna, in pretium ligula. Sed lacus augue, sagittis et gravida viverra, imperdiet eget elit. Morbi malesuada mauris eu eros rutrum blandit. Cras malesuada rutrum sem, ac vehicula diam volutpat ac. Vestibulum sed tortor purus. Phasellus in lectus fringilla tellus porttitor vehicula ut fermentum metus lorem ipsum dolor sit amet lorem ipsum dolor sit amet nullam consequat lorem ipsum dolor sit amet consequat veroeros etiam.</p>
');
INSERT INTO property_value VALUES (7, 2, 10, 'About');
INSERT INTO property_value VALUES (8, 2, 11, '');
INSERT INTO property_value VALUES (9, 2, 12, 'About');
INSERT INTO property_value VALUES (10, 2, 13, 'About');
INSERT INTO property_value VALUES (26, 4, 39, 'Blog');
INSERT INTO property_value VALUES (27, 4, 40, '');
INSERT INTO property_value VALUES (28, 4, 41, 'Blog');
INSERT INTO property_value VALUES (29, 4, 42, 'Blog');
INSERT INTO property_value VALUES (30, 5, 35, 'First category - Blog');
INSERT INTO property_value VALUES (31, 5, 36, '');
INSERT INTO property_value VALUES (32, 5, 37, 'First category - Blog');
INSERT INTO property_value VALUES (33, 5, 38, 'First category - Blog');
INSERT INTO property_value VALUES (34, 6, 35, 'Second category - Blog');
INSERT INTO property_value VALUES (35, 6, 36, '');
INSERT INTO property_value VALUES (36, 6, 37, 'Second category - Blog');
INSERT INTO property_value VALUES (37, 6, 38, 'Second category - Blog');
INSERT INTO property_value VALUES (49, 8, 34, '<p>
    Suspendisse faucibus dictum tellus id posuere. Cras quis eros tellus, id posuere lacus. Praesent ac est eros. Aliquam eleifend nunc ut neque consequat quis suscipit elit tincidunt. Fusce interdum gravida tincidunt. Sed et ante nec ligula fringilla condimentum. Mauris vel sem ac ligula sed lorem vestibulum ornare vel sed nibh. Fusce rhoncus tincidunt ante, non hendrerit magna vestibulum quis. Integer vel enim sem phasellus tempus lorem.</p>
<p>
    Maecenas vel mauris sit amet augue accumsan tempor non in sapien. Nulla facilisi. Aliquam imperdiet sem et orci adipiscing vehicula sagittis metus consectetur. Mauris a dui dolor magna tristique condimentum nec ut ligula. Sed eu arcu in neque porta iaculis. Mauris fermentum velit sit amet leo convallis consectetur. Ut vel libero dui, eu vehicula velit. Maecenas et justo vitae lacus venenatis vehicula. Ut eget posuere arcu dolore blandit.</p>
<p>
    Aenean nec turpis ac ligula posuere eleifend sed a eros. Integer nec nibh interdum nulla fringilla ultricies. Integer semper felis eu ante congue dignissim. Praesent rhoncus nulla sed felis rhoncus et iaculis risus mattis. Nam sed purus dui, a egestas enim. Donec facilisis, enim id fermentum mattis, tellus libero ultricies massa, eu bibendum augue ipsum sit amet ligula. Proin porta sagittis erat ac pharetra. Aenean ultricies enim id mi lacinia rhoncus. Nunc volutpat, turpis vel porta tincidunt, lectus metus fermentum lectus, sed sodales erat nisi non dolor. Pellentesque vitae felis eget turpis rutrum malesuada in non quam. Mauris at lorem vel nunc tristique scelerisque sit amet id nisl. Donec at nisi magna, in pretium ligula. Sed lacus augue, sagittis et gravida viverra, imperdiet eget elit. Morbi malesuada ligula. Proin porta sagittis erat ac pharetra. Aenean ultricies enim id mi lacinia rhoncus. Nunc volutpat, turpis vel porta tincidunt, lectus metus fermentum lectus, sed sodales erat imperdiet sem et orci adipiscing vehicula sagittis metus consectetur. Mauris a dui dolor magna tristique condimentum nec ut ligula. Sed eu arcu in neque porta iaculis. Mauris fermentum velit sit amet leo convallis consectetur. Ut vel libero dui, eu vehicula velit. Maecenas et justo nisi non dolor. Pellentesque vitae felis eget turpis rutrum malesuada in non quam. Mauris at lorem vel nunc tristique scelerisque sit amet id nisl. Donec at nisi magna, in pretium ligula. Sed lacus augue, sagittis et gravida viverra, imperdiet eget elit. Morbi malesuada mauris eu eros rutrum blandit. Cras malesuada rutrum sem, ac vehicula diam volutpat ac. Vestibulum sed tortor purus. Phasellus in lectus fringilla tellus porttitor vehicula ut fermentum metus lorem ipsum dolor sit amet lorem ipsum dolor sit amet nullam consequat lorem ipsum dolor sit amet consequat veroeros etiam.</p>
');
INSERT INTO property_value VALUES (50, 8, 26, '<p>
    Suspendisse faucibus dictum tellus id posuere. Cras quis eros tellus, id posuere lacus. Praesent ac est eros. Aliquam eleifend nunc ut neque consequat quis suscipit elit tincidunt. Fusce interdum gravida tincidunt. Sed et ante nec ligula fringilla condimentum. Mauris vel sem ac ligula sed lorem vestibulum ornare vel sed nibh. Fusce rhoncus tincidunt ante, non hendrerit magna vestibulum quis. Integer vel enim sem phasellus tempus lorem.</p>
');
INSERT INTO property_value VALUES (57, 2, 48, 'N;');
INSERT INTO property_value VALUES (58, 4, 49, 'a:5:{s:5:"value";s:35:"/media/files/4/49/51ab19735dad2.jpg";s:5:"width";i:204;s:6:"height";i:128;s:4:"html";i:2;s:4:"mime";s:10:"image/jpeg";}');
INSERT INTO property_value VALUES (59, 4, 50, '<h3>
    Featured Content</h3>
<p>
    Lorema psum dolor sit amet, consectetur adipiscing elit. Integer egestas purus bibendum neque aliquam ut posuere elit semper. Fusce sagittis pharetra eros, sit amet consequat sem mollis vitae.</p>
');
INSERT INTO property_value VALUES (56, 4, 44, 'a:1:{i:0;a:2:{i:0;a:1:{s:5:"value";s:16:"About SilverBlog";}i:1;a:1:{s:5:"value";s:211:"<p>\n\r\tSilverBlog is a free CSS Template released under GNU GPL license. You are free to use / modify it in any way you want without any restrictions. A link back to this website will always be appreciated.</p>\n\r";}}}');



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
set foreign_key_checks=1;
