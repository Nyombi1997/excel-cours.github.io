<?php

    class MyAutoload
    {
        public static function start()
        {
            spl_autoload_register(array(__CLASS__, 'autoload'));

            $root = $_SERVER['DOCUMENT_ROOT'];
            $host = $_SERVER['HTTP_HOST'];

            define('ROOT', $root.'/');
            define('HOST', 'http://'.$host.'/');

            define('ASSET', HOST . 'asset/');


            define('CONTROLLER', ROOT . 'controller/');
            define('VIEW', ROOT . 'view/');
            define('CLASSES', ROOT . 'classes/');
            define('MODEL', ROOT . 'model/');
            define('FONCTION', ROOT . 'fonctions/');
            define('PAGE', ROOT . 'page/');
            
            // Qualités des images AVEC hauteurs définies
            $IMAGE_QUALITIES = [
                'lqip' => ['width' => 20, 'height' => 15, 'quality' => 10],
                'mobile' => ['width' => 400, 'height' => 300, 'quality' => 60],
                'tablet' => ['width' => 800, 'height' => 600, 'quality' => 75],
                'desktop' => ['width' => 1200, 'height' => 900, 'quality' => 85],
                'hd' => ['width' => 1920, 'height' => 1440, 'quality' => 92]
            ];

            // Configuration des chemins
            define('ROOT_PATH', dirname(__DIR__));
            define('UPLOAD_BASE', ROOT_PATH . '/uploads/');
            define('PRODUCT_UPLOAD_DIR', ASSET . 'images/articles');

            // Configuration des images
            define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
            define('MAX_IMAGES_PER_PRODUCT', 10);
            define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

            // Configuration sécurité
            define('UPLOAD_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);

            // Timezone
            date_default_timezone_set('Europe/Paris');

        }

        public static function autoload($class)
        {
            if(file_exists(MODEL.$class.'.php')) {
                include_once MODEL.$class.'.php';
            } elseif(file_exists(CONTROLLER.$class.'.php')) {
                include_once CONTROLLER.$class.'.php';
            } elseif(file_exists(CLASSES.$class.'.php')) {
                include_once CLASSES.$class.'.php';
            } elseif(file_exists(VIEW.$class.'.php')) {
                include_once VIEW.$class.'.php';
            } else {
                throw new Exception("Class $class not found.");
            }
        }
    }
?>