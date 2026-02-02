<?php

    class Routeur {
        private $request;

        private $routes = [
                            "" => ["controller" => 'Home', "method" => 'showHome'], 
                            "accueil" => ["controller" => 'Home', "method" => 'showHome'],

                            "cours" => ["controller" => 'Home', "method" => 'showCours'],

                            "connexion" => ["controller" => 'Home', "method" => 'showLogin'],
                            "inscription" => ["controller" => 'Home', "method" => 'showLogin'],
                            "deconnexion" => ["controller" => 'Home', "method" => 'showLogout'],
                            
                            "compte" => ["controller" => 'Home', "method" => 'showAccount'],
                            "admin" => ["controller" => 'Home', "method" => 'showAdmin'],
                            "gestion-cours" => ["controller" => 'Home', "method" => 'showGestionCours'],
                        ];

        public function __construct($request) {
            $this->request = $request;
        }

        public function renderController() {

            $request = $this->request;

            if (key_exists($request, $this->routes)) {
                $controller = $this->routes[$request]['controller'];
                $method = $this->routes[$request]['method'];

                $currentController = new $controller();
                $currentController->$method();
            } else {
                    echo '
                    <!DOCTYPE html>
                    <html lang="fr">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>404</title>
                        <style>
                            *{
                                margin: 0;
                                padding: 0;
                                box-sizing: border-box;
                            }
                            body {
                                font-family: Arial, sans-serif;
                                text-align: center;
                                padding: 50px;
                                display: flex;
                                justify-content: center;
                                align-items: center;
                            }
                            div.container {
                                position: absolute;
                                top: 0;
                                left: 0;
                                bottom: 0;
                                right: 0;
                                width: 100%;
                                height: 100vh;
                                background: linear-gradient(135deg, #f6f9f8, #4caf50);
                                padding: 20px 0px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                color: #444444;
                            }
                            h1 {
                                font-size: 50px;
                                margin-bottom: 20px;
                            }
                            p {
                                font-size: 20px;
                                margin-bottom: 20px;
                            }
                            a {
                                color: #444444;
                                text-decoration: none;
                                font-weight: bold;
                                display: inline-block;
                                padding: 10px 20px;
                                background-color: #f6f9f8;
                                border-radius: 20px;
                            }
                        </style>
                    </head>
                    <body>
                        <div class="container">
                            <div class="">
                                <h1>404 - Page non trouvée</h1>
                                <p>La page que vous recherchez n\'existe pas.</p>
                                <p><a href="/accueil">Retourner à la page d\'accueil</a></p>  
                            </div>
                        </div>                  
                    </body>
                    </html>';
            }
        }
    }
?>