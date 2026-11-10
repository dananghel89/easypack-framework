<?php

/**
 * Epack View Class
 */

namespace Epack;

class View
{
    // View constants.
    protected static $path = APP_PATH . '/Views';

    // View render method.
    public static function render($slug, $args = [], $use_tmplt = false)
    {
        if (!$use_tmplt) {
            extract($args, EXTR_SKIP);

            $file = self::$path . "/{$slug}.php";

            if (!file_exists($file)) {
                throw new \Exception("File not found: {$file}");
            }

            require $file;

        } else {
            $templates = new \League\Plates\Engine(self::$path);

            if (!$templates->exists($slug)) {
                throw new \Exception("Template not found: {$slug}");
            }

            echo $templates->render($slug, $args);
        }
    }

    // View get path method.
    public static function getPath()
    {
        return self::$path;
    }

}
