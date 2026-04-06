<?php
    class home
    {
        public function showHome ()
        {
            $myView = new View('accueil');
            $myView->render('AbsoluHub | Apprenez à votre rythme !');
        }

        public function showCours ()
        {
            $myView = new View('cours');
            $myView->render('Cours AbsoluHub | Apprenez à votre rythme !');
        }

        public function showLogin ()
        {
            $myView = new View('login');
            $myView->render('Connexion AbsoluHub | Apprenez à votre rythme !');
        }

        public function showAccount ()
        {
            $myView = new View('compte');
            $myView->render('Compte AbsoluHub | Apprenez à votre rythme !');
        }

        public function showLogout ()
        {
            $myView = new View('logout');
            $myView->render('Déconnexion AbsoluHub | Apprenez à votre rythme !');
        }

        public function showAdmin ()
        {
            $myView = new View('admin');
            $myView->render('Admin AbsoluHub | Apprenez à votre rythme !');
        }

        public function showGestionCours ()
        {
            $myView = new View('course-admin-list');
            $myView->render('Gestion cours AbsoluHub | Apprenez à votre rythme !');
        }

        public function showCreateCourse ()
        {
            $myView = new View('course-admin-create');
            $myView->render('Création cours AbsoluHub | Apprenez à votre rythme !');
        }

        public function showEditCourse ()
        {
            $myView = new View('course-admin-edit');
            $myView->render('Modifier cours AbsoluHub | Apprenez à votre rythme !');
        }

        public function showManageSection ()
        {
            $myView = new View('course-admin-section');
            $myView->render('Gestion chapitre AbsoluHub | Apprenez à votre rythme !');
        }

        public function showGestionUsers ()
        {
            $myView = new View('users-management');
            $myView->render('Gestion utilisateurs AbsoluHub | Apprenez à votre rythme !');
        }

        public function showContact ()
        {
            $myView = new View('contact');
            $myView->render('Contact AbsoluHub | Apprenez à votre rythme !');
        }
    }
?>
