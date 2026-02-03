<?php
    /* produit avec limitation */
    function getProducts($bdd, $base, $limit = null, $offset = 0) 
    {
        $request = "SELECT * FROM $base";
        if ($limit !== null) {
            $request .= " LIMIT :limit OFFSET :offset";
            $stmt = $bdd->prepare($request);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        } else {
            $stmt = $bdd->prepare($request);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    function select_bdd($bdd, $base, $where = null, $limit = null, $offset = 0, $order = null, $random = false)
    {
        $request = "SELECT * FROM $base";

        if ($where !== null) {
            $request .= " WHERE " . $where;
        }

        if ($random) {
            $request .= " ORDER BY RAND()"; // Pour MySQL
            // $request .= " ORDER BY RANDOM()"; // Pour PostgreSQL
        } elseif ($order !== null) {
            $request .= " ORDER BY " . $order;
        }

        if ($limit !== null) {
            $request .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $bdd->prepare($request);
        
        if ($limit !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /* while select sql */
    function while_select($table, $where = null, $order = null, $limit = null) {
        global $bdd;
        $query = "SELECT * FROM $table";
        if ($where) {
            $query .= " WHERE $where";
        }
        if ($order) {
            $query .= " ORDER BY $order";
        }
        if ($limit) {
            $query .= " LIMIT $limit";
        }
        $req = $bdd->query($query);
        return $req;
    }
    /* select */
    function select($table, $where = null, $order = null, $limit = null)
    {
        global $bdd;
        $query = "SELECT * FROM $table";
        if ($where) {
            $query .= " WHERE $where";
        }
        if ($order) {
            $query .= " ORDER BY $order";
        }
        if ($limit) {
            $query .= " LIMIT $limit";
        }
        $req = $bdd->query($query);
        if (!$req) {
            die("Erreur SQL : " . print_r($bdd->errorInfo(), true));
        }
        return $req;
    }
    /* select dernier */
    function select_dernier($table, $where = null, $order = null, $limit = null) {
        global $bdd;
        $query = "SELECT * FROM $table";
        if ($where) {
            $query .= " WHERE $where";
        }
        if ($order) {
            $query .= " ORDER BY $order";
        }
        if ($limit) {
            $query .= " LIMIT $limit";
        }
        $req = $bdd->query($query);
        return $req->fetch(PDO::FETCH_ASSOC);
    }
    /* select only */
    function only_select($table, $where = null, $order = null, $limit = null)
    {
        global $bdd;
        $query = "SELECT * FROM $table";
        if ($where) {
            $query .= " WHERE $where";
        }
        if ($order) {
            $query .= " ORDER BY $order";
        }
        if ($limit) {
            $query .= " LIMIT $limit";
        }
        $req = $bdd->query($query);
        if (!$req) {
            die("Erreur SQL : " . print_r($bdd->errorInfo(), true));
        }
        $req->execute();
    
        $req_ = $req->fetch(PDO::FETCH_ASSOC);
        return $req_;
    }
    /* select like */
    function select_like($get = null, $bdd = null) {
        /* si une recherche est faite */
        if($get || $get = '')
        {
            $search = isset($get) ? $get : '';
            $search = strtolower($search); // minuscule
            $search = preg_replace('/[^a-z0-9]/', '', $search); // enlever caractères spéciaux
        
            $query = $bdd->prepare("
                SELECT 'produit' AS source, id, nom
                FROM produit
                WHERE LOWER(REGEXP_REPLACE(nom, '[^a-zA-Z0-9]', '')) LIKE :search
                UNION
                SELECT 'etablissement' AS source, id, nom
                FROM etablissement
                WHERE LOWER(REGEXP_REPLACE(nom, '[^a-zA-Z0-9]', '')) LIKE :search
                UNION
                SELECT 'categorie' AS source, id, nom
                FROM categorie
                WHERE LOWER(REGEXP_REPLACE(nom, '[^a-zA-Z0-9]', '')) LIKE :search
                ORDER BY nom ASC
            ");
            $search_sql = '%' . $search . '%';
            $query->execute(['search' => $search_sql]);
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
        }
    }


    /* nombre de ligne de la table */
    function getRowCount($bdd, $base, $where = null, $limit = null, $offset = 0, $order = null) 
    {
        $request = "SELECT COUNT(*) as count FROM $base";

        if ($where !== null) {
            $request .= " WHERE ".$where;
        }
        $stmt = $bdd->prepare($request);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['count'];
    }

    function update_bdd($bdd, $base, $update_data, $where) 
    {
        /**
         * Tableau associatif contenant les données à mettre à jour.
         * Les clés doivent correspondre aux noms des colonnes de la table en base de données,
         * et les valeurs doivent être les nouvelles valeurs à attribuer à ces colonnes.
         * Exemple :
         * [
         *     'nom_colonne1' => 'nouvelle_valeur1',
         *     'nom_colonne2' => 'nouvelle_valeur2',
         *     // ...
         * ]
         */
        $set = [];
        foreach ($update_data as $key => $value) {
            $set[] = "$key = :$key";
        }
        $set_clause = implode(', ', $set);
        $request = "UPDATE $base SET $set_clause WHERE $where";
        $stmt = $bdd->prepare($request);
        foreach ($update_data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        return $stmt->execute();
    }

    function insert_bdd($bdd, $base, $insert_data) 
    {
        /**
         * Tableau associatif contenant les données à insérer dans la base de données.
         *
         * Exemple de structure attendue pour $insert_data :
         * [
         *     'nom_colonne1' => 'valeur1',
         *     'nom_colonne2' => 'valeur2',
         *     // ...
         * ]
         *
         * Chaque clé du tableau correspond au nom d'une colonne dans la table cible,
         * et chaque valeur correspond à la donnée à insérer dans cette colonne.
         */
        $columns = implode(', ', array_keys($insert_data));
        $placeholders = ':' . implode(', :', array_keys($insert_data));
        $request = "INSERT INTO $base ($columns) VALUES ($placeholders)";
        $stmt = $bdd->prepare($request);
        foreach ($insert_data as $key => $value) {
            $value = html_entity_decode(htmlspecialchars($value));
            $stmt->bindValue(":$key", $value);
        }
        return $stmt->execute();
    }
    /* créer un slug aux s'il y'en a pas */
    /*function createSlugIfNeeded($bdd, $base) {
        $request = "SELECT id, nom FROM $base WHERE slug IS NULL OR slug = ''";
        $stmt = $bdd->prepare($request);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($results as $row) {
            $slug = generateSlug($row['nom']);
            update_bdd($bdd, $base, ['slug' => $slug], "id = " . intval($row['id']));
        }
    }*/

    /* gestions des filtres */
    function select_articles_filtre($bdd, array $filters = [], $limit = null, $offset = 0, $order = null, $random = false)
    {
        $sql = "SELECT DISTINCT a.* FROM articles a";
        $joins = [];
        $params = [];

        // FILTRE CATÉGORIE
        if (!empty($filters['category'])) {
            if($filters['category']!=0)
            {
                $joins[] = "INNER JOIN categorie_article ac 
                            ON ac.article = a.id AND ac.categorie = :category";
                $params[':category'] = (int)$filters['category'];                
            }
        }

        // FILTRE TYPE
        if (!empty($filters['type'])) {
            if($filters['type']!=0)
            {
                $joins[] = "INNER JOIN types_article at 
                            ON at.article = a.id AND at.types = :type";
                $params[':type'] = (int)$filters['type'];
            }
        }

        // FILTRE TAILLE
        if (!empty($filters['taille'])) {
            if($filters['taille']!=0)
            {
                $joins[] = "INNER JOIN taille_articles al 
                            ON al.article = a.id AND al.taille = :taille";
                $params[':taille'] = (int)$filters['taille'];
            }
        }

        // FILTRE BOUTIQUE
        if (!empty($filters['boutique'])) {
            if($filters['boutique']!=0)
            {
                $joins[] = "INNER JOIN articles ab 
                            ON ab.id = a.id AND ab.boutique = :boutique";
                $params[':boutique'] = (int)$filters['boutique'];
            }
        }

        // Assemblage final
        if ($joins) {
            $sql .= " " . implode(" ", $joins);
        }

        if ($random) {
            $sql .= " ORDER BY RAND()";
        } elseif ($order !== null) {
            $sql .= " ORDER BY " . $order;
        } else {
            $sql .= " ORDER BY a.id DESC";
        }

        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $bdd->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        }

        if ($limit !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    /* moteur de recherche */
    function found($q, $limit = null, $offset = 0, $order = null, $random = false)
    {
        $q = trim($q);

        if ($q === '' || strlen($q) < 1) {
            return [];
        }

        if (preg_match('/(\d+)/', $q, $matches)) {
            $q_numeric = $matches[1];
            $q = $matches[1];
        }

        global $bdd;

        /* ORDER BY */
        $orderBy = "score DESC, label ASC";

        if ($random === true) {
            $orderBy = "RAND()";
        } elseif ($order !== null) {
            $allowedOrders = [
                'score'      => 'score DESC',
                'label'      => 'label ASC',
                'prix_asc'   => 'prix ASC',
                'prix_desc'  => 'prix DESC'
            ];
            if (isset($allowedOrders[$order])) {
                $orderBy = $allowedOrders[$order];
            }
        }

        /* LIMIT / OFFSET */
        $limitSql = "";
        if ($limit !== null) {
            $limit = (int) $limit;
            $offset = (int) $offset;
            $limitSql = "LIMIT $limit OFFSET $offset";
        }

        $sql = "
            (
                SELECT id, nom COLLATE utf8mb4_general_ci AS label, prix, description COLLATE utf8mb4_general_ci AS description, slug COLLATE utf8mb4_general_ci AS slug, 'articles' AS source,
                    (
                        (CASE WHEN nom LIKE :start THEN 5
                            WHEN nom LIKE :middle THEN 3
                            WHEN nom LIKE :any THEN 1 ELSE 0 END)
                        +
                        (CASE WHEN prix LIKE :any THEN 1 ELSE 0 END)
                    ) AS score
                FROM articles
                WHERE (nom LIKE :any OR prix LIKE :any OR (:q_numeric IS NOT NULL AND prix <= :q_numeric)) OR description LIKE :any
            )
            UNION
            (
                SELECT id, nom COLLATE utf8mb4_general_ci AS label, NULL AS prix, description COLLATE utf8mb4_general_ci AS description, slug COLLATE utf8mb4_general_ci AS slug, 'boutiques' AS source,
                    (
                        (CASE WHEN nom COLLATE utf8mb4_general_ci LIKE :start THEN 5
                            WHEN nom COLLATE utf8mb4_general_ci LIKE :middle THEN 3
                            WHEN nom COLLATE utf8mb4_general_ci LIKE :any THEN 1 ELSE 0 END)
                    ) AS score
                FROM boutiques
                WHERE nom COLLATE utf8mb4_general_ci LIKE :any OR description LIKE :any
            )
            UNION
            (
                SELECT id, nom COLLATE utf8mb4_general_ci AS label, NULL AS prix, NULL AS description, slug COLLATE utf8mb4_general_ci AS slug, 'categorie' AS source,
                    (
                        (CASE WHEN nom COLLATE utf8mb4_general_ci LIKE :start THEN 5
                            WHEN nom COLLATE utf8mb4_general_ci LIKE :middle THEN 3
                            WHEN nom COLLATE utf8mb4_general_ci LIKE :any THEN 1 ELSE 0 END)
                    ) AS score
                FROM categorie
                WHERE nom COLLATE utf8mb4_general_ci LIKE :any
            )
            UNION
            (
                SELECT id, nom COLLATE utf8mb4_general_ci AS label, NULL AS prix, NULL AS description, slug COLLATE utf8mb4_general_ci AS slug, 'types' AS source,
                    (
                        (CASE WHEN nom COLLATE utf8mb4_general_ci LIKE :start THEN 5
                            WHEN nom COLLATE utf8mb4_general_ci LIKE :middle THEN 3
                            WHEN nom COLLATE utf8mb4_general_ci LIKE :any THEN 1 ELSE 0 END)
                    ) AS score
                FROM types
                WHERE nom COLLATE utf8mb4_general_ci LIKE :any
            )
            UNION
            (
                SELECT id, nom COLLATE utf8mb4_general_ci AS label, NULL AS prix, commentaire COLLATE utf8mb4_general_ci AS description, slug COLLATE utf8mb4_general_ci AS slug, 'tailles' AS source,
                    (
                        (CASE WHEN nom COLLATE utf8mb4_general_ci LIKE :start THEN 5
                            WHEN nom COLLATE utf8mb4_general_ci LIKE :middle THEN 3
                            WHEN nom COLLATE utf8mb4_general_ci LIKE :any THEN 1 ELSE 0 END)
                    ) AS score
                FROM tailles
                WHERE nom COLLATE utf8mb4_general_ci LIKE :any
            )
            ORDER BY $orderBy
            $limitSql
            ";

        $stmt = $bdd->prepare($sql);

        $q_numeric = is_numeric($q) ? $q : null;

        $stmt->execute([
            ":start"     => "$q%",
            ":middle"    => "% $q%",
            ":any"       => "%$q%",
            ":q_numeric" => $q_numeric
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* trouveur d'article */
    function getArticlesFromSearch(array $results, $limit = null, $offset = 0, $order = null, $random = false)
    {
        global $bdd;

        $articleIds = [];

        $sourceMap = [
            'types'     => ['table' => 'types_article',     'col' => 'types'],
            'tailles'   => ['table' => 'taille_articles',   'col' => 'taille'],
            'categorie' => ['table' => 'categorie_article', 'col' => 'categorie'],
            'boutiques' => ['table' => 'articles',          'col' => 'boutique'],
            'articles'  => ['table' => 'articles',          'col' => 'id'],
        ];

        foreach ($results as $row) {

            /* articles directs */
            if ($row['source'] === 'articles') {
                $articleIds[] = (int)$row['id'];
                continue;
            }

            /* sources liées */
            if (!isset($sourceMap[$row['source']])) {
                continue;
            }

            $map = $sourceMap[$row['source']];

            if ($row['source'] === 'boutiques') {
                $sql = "SELECT id FROM articles WHERE boutique = ?";
            } else {
                $sql = "SELECT article FROM {$map['table']} WHERE {$map['col']} = ?";
            }

            $stmt = $bdd->prepare($sql);
            $stmt->execute([$row['id']]);

            $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $articleIds = array_merge($articleIds, $ids);
        }

        if (empty($articleIds)) {
            return [];
        }

        /* Unicité */
        $articleIds = array_values(array_unique(array_map('intval', $articleIds)));

        /* ORDER */
        $orderBy = "a.id DESC";

        if ($random === true) {
            $orderBy = "RAND()";
        } elseif ($order) {
            $allowed = [
                'prix_asc'  => 'a.prix ASC',
                'prix_desc' => 'a.prix DESC',
                'nom'       => 'a.nom ASC'
            ];
            if (isset($allowed[$order])) {
                $orderBy = $allowed[$order];
            }
        }

        /* LIMIT / OFFSET */
        $limitSql = "";
        if ($limit !== null) {
            $limitSql = "LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }

        /* Chargement final des articles */
        $placeholders = implode(',', array_fill(0, count($articleIds), '?'));

        $sql = "
            SELECT a.*
            FROM articles a
            WHERE a.id IN ($placeholders)
            ORDER BY $orderBy
            $limitSql
        ";

        $stmt = $bdd->prepare($sql);
        $stmt->execute($articleIds);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /* créer une table */
    function createTable(string $table, array $columns)
    {
        global $bdd;
        
        $sqlColumns = [];

        foreach ($columns as $column) {
            $sqlColumns[] = $column;
        }

        $sql = "
            CREATE TABLE IF NOT EXISTS `$table` (
                " . implode(",\n", $sqlColumns) . "
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";

        $bdd->exec($sql);
    }
    /* trouver les articles similaires */
    function getSimilarArticles(int $articleId,int $limit = 8,?string $order = null,bool $random = true) 
    {
        global $bdd;
        
        $sql = "SELECT 
                    a.*,
                    (CASE WHEN EXISTS (
                        SELECT 1 FROM categorie_article ca1 
                        WHERE ca1.article = a.id 
                        AND ca1.categorie IN (
                            SELECT categorie FROM categorie_article 
                            WHERE article = :id_article
                        )
                    ) THEN 1 ELSE 0 END) +
                    (CASE WHEN EXISTS (
                        SELECT 1 FROM taille_articles ta1 
                        WHERE ta1.article = a.id 
                        AND ta1.taille IN (
                            SELECT taille FROM taille_articles 
                            WHERE article = :id_article
                        )
                    ) THEN 1 ELSE 0 END) +
                    (CASE WHEN EXISTS (
                        SELECT 1 FROM types_article tp1 
                        WHERE tp1.article = a.id 
                        AND tp1.types IN (
                            SELECT types FROM types_article 
                            WHERE article = :id_article
                        )
                    ) THEN 1 ELSE 0 END) AS score_similarite
                FROM articles a
                WHERE a.id != :id_article
                HAVING score_similarite > 0
                ORDER BY score_similarite DESC
                LIMIT $limit";

        $stmt = $bdd->prepare($sql);
        $stmt->bindValue(':id_article', $articleId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* trouver les categories pour la boutique */
    function categorieBoutique ($boutique_id = 0)
    {
        global $bdd;
        
        $sql = "
            SELECT 
                ca.*,
                COUNT(DISTINCT ca.id) AS total
            FROM categorie ca
            INNER JOIN categorie_article caa ON caa.categorie = ca.id
            INNER JOIN articles a ON a.id = caa.article
            INNER JOIN boutiques bo ON bo.id = a.boutique
            WHERE bo.id = :boutique_id
            GROUP BY ca.id
            HAVING total > 0
            ORDER BY ca.nom ASC
        ";

        $stmt = $bdd->prepare($sql);
        $stmt->bindValue(':boutique_id', $boutique_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
?>