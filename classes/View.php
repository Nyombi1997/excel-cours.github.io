<?php
    class View {
        private $template;

        public function __construct($template) 
        {
            $this->template = $template;
        }

        public function render($title_page = 'OhNous')
        {            
            $template = $this->template;

            /* ramener la bdd */
            include MODEL.'bdd.php';
            /* ramener la recherche bdd */
            include_once MODEL.'select.php';
            global $bdd;

            ob_start();

            // Prépare le chemin de template en ajoutant .php si nécessaire
            $templatePath = VIEW . $template;
            if (substr($templatePath, -4) !== '.php') {
                $templatePath .= '.php';
            }

            include_once $templatePath;
            $contentPage = ob_get_clean();
            // Si on va vers l’accueille
            if (basename($templatePath) != 'accueil.php' && basename($templatePath) == 'articles.php') {
                $GLOBALS['others'] = 'ok';
            }
            else if(basename($templatePath) != 'articles.php' && basename($templatePath) != 'accueil.php')
            {
                $GLOBALS['others'] = 'ok';
                $GLOBALS['no_filtre'] = 'ok';
            }
            include_once VIEW . '_gabarit.php';
        }
    }
?>