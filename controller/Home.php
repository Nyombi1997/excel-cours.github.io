<?php
    class home
    {
        public function showHome ()
        {
            $myView = new View('accueil');
            $myView->render('AbsoluHub | Apprenez Ã  votre rythme !');
        }

        public function showCours ()
        {
            $myView = new View('cours');
            $myView->render('Cours AbsoluHub | Apprenez Ã  votre rythme !');
        }

        public function showCoursDetail ()
        {
            $myView = new View('course-detail');
            $myView->render('DÃ©tail cours AbsoluHub | Apprenez Ã  votre rythme !');
        }

        public function showLogin ()
        {
            $myView = new View('login');
            $myView->render('Connexion AbsoluHub | Apprenez Ã  votre rythme !');
        }

        public function showAccount ()
        {
            $myView = new View('compte');
            $myView->render('Compte AbsoluHub | Apprenez Ã  votre rythme !');
        }

        public function showAccountEdit ()
        {
            $myView = new View('compte-edit');
            $myView->render('Modifier mon compte | AbsoluHub');
        }

        public function showAccountMessages ()
        {
            $myView = new View('messages-compte');
            $myView->render('Mes messages | AbsoluHub');
        }

        public function showAccountConversation ()
        {
            $myView = new View('conversation-compte');
            $myView->render('Ma conversation | AbsoluHub');
        }

        public function showLogout ()
        {
            $myView = new View('logout');
            $myView->render('DÃ©connexion AbsoluHub | Apprenez Ã  votre rythme !');
        }

        public function showAdmin ()
        {
            $myView = new View('admin');
            $myView->render('Admin AbsoluHub | Apprenez Ã  votre rythme !');
        }

        public function showAdminMessages ()
        {
            $myView = new View('messages-admin');
            $myView->render('Messages admin | AbsoluHub');
        }

        public function showAdminConversation ()
        {
            $myView = new View('conversation-admin');
            $myView->render('Conversation admin | AbsoluHub');
        }

        public function showGestionCours ()
        {
            $myView = new View('course-admin-list');
            $myView->render('Gestion cours AbsoluHub | Apprenez Ã  votre rythme !');
        }

        public function showCreateCourse ()
        {
            $myView = new View('course-admin-create');
            $myView->render('CrÃ©ation cours AbsoluHub | Apprenez Ã  votre rythme !');
        }

        public function showEditCourse ()
        {
            $myView = new View('course-admin-edit');
            $myView->render('Modifier cours AbsoluHub | Apprenez Ã  votre rythme !');
        }

        public function showManageSection ()
        {
            $myView = new View('course-admin-section');
            $myView->render('Gestion chapitre AbsoluHub | Apprenez Ã  votre rythme !');
        }

        public function showGestionUsers ()
        {
            $myView = new View('users-management');
            $myView->render('Gestion utilisateurs AbsoluHub | Apprenez Ã  votre rythme !');
        }

        public function showContact ()
        {
            $myView = new View('contact');
            $myView->render('Contact AbsoluHub | Apprenez Ã  votre rythme !');
        }
    }
?>
