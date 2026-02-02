<?php
    class home
    {
        public function showHome ()
        {
            /* ramener la vers home */
            $myView = new View('accueil');
            $myView->render('Accueil cours excel | Apprenez à votre rythme !');
        }
        public function showCours ()
        {
            /* ramener la vers cours */
            $myView = new View('cours');
            $myView->render('Cours excel | Apprenez à votre rythme !');
        }
        public function showLogin ()
        {
            /* ramener la vers connexion */
            $myView = new View('login');
            $myView->render('Connexion cours excel | Apprenez à votre rythme !');
        }
        public function showAccount ()
        {
            /* ramener la vers connexion */
            $myView = new View('compte');
            $myView->render('Compte cours excel | Apprenez à votre rythme !');
        }
        public function showLogout ()
        {
            /* ramener la vers connexion */
            $myView = new View('logout');
            $myView->render('deconnexion cours excel | Apprenez à votre rythme !');
        }
        public function showAdmin ()
        {
            /* ramener la vers connexion */
            $myView = new View('admin');
            $myView->render('Admin cours excel | Apprenez à votre rythme !');
        }
        public function showGestionCours ()
        {
            /* ramener la vers connexion */
            $myView = new View('add-contents');
            $myView->render('Gestion cours cours excel | Apprenez à votre rythme !');
        }
    }
?>