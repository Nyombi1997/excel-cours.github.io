<?php
    class home
    {
        public function showHome ()
        {
            /* ramener la vers home */
            $myView = new View('accueil');
            $myView->render('AbsoluHub | Apprenez à votre rythme !');
        }
        public function showCours ()
        {
            /* ramener la vers cours */
            $myView = new View('cours');
            $myView->render('Cours AbsoluHub | Apprenez à votre rythme !');
        }
        public function showLogin ()
        {
            /* ramener la vers connexion */
            $myView = new View('login');
            $myView->render('Connexion AbsoluHub | Apprenez à votre rythme !');
        }
        public function showAccount ()
        {
            /* ramener la vers connexion */
            $myView = new View('compte');
            $myView->render('Compte AbsoluHub | Apprenez à votre rythme !');
        }
        public function showLogout ()
        {
            /* ramener la vers connexion */
            $myView = new View('logout');
            $myView->render('deconnexion AbsoluHub | Apprenez à votre rythme !');
        }
        public function showAdmin ()
        {
            /* ramener la vers connexion */
            $myView = new View('admin');
            $myView->render('Admin AbsoluHub | Apprenez à votre rythme !');
        }
        public function showGestionCours ()
        {
            /* ramener la vers connexion */
            $myView = new View('add-contents');
            $myView->render('Gestion cours AbsoluHub | Apprenez à votre rythme !');
        }
        public function showGestionUsers ()
        {
            /* ramener la vers users-management */
            $myView = new View('users-management');
            $myView->render('Gestion cours AbsoluHub | Apprenez à votre rythme !');
        }
        public function showContact ()
        {
            /* ramener la vers users-management */
            $myView = new View('contact');
            $myView->render('Gestion cours AbsoluHub | Apprenez à votre rythme !');
        }
    }
?>