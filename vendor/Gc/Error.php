<?php
class \Gc\Error
{
    static function set($title, $content)
    {
        var_dump(array('Title' => $title, 'Content' => $content));
        exit(0);
    }
}
