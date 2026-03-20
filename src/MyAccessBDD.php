<?php
include_once("AccessBDD.php");

/**
 * Classe de construction des requêtes SQL
 * hérite de AccessBDD qui contient les requêtes de base
 * Pour ajouter une requête :
 * - créer la fonction qui crée une requête (prendre modèle sur les fonctions 
 *   existantes qui ne commencent pas par 'traitement')
 * - ajouter un 'case' dans un des switch des fonctions redéfinies 
 * - appeler la nouvelle fonction dans ce 'case'
 */
class MyAccessBDD extends AccessBDD {
	    
    /**
     * constructeur qui appelle celui de la classe mère
     */
    public function __construct(){
        try{
            parent::__construct();
        }catch(\Exception $e){
            throw $e;
        }
    }

    /**
     * demande de recherche
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return array|null tuples du résultat de la requête ou null si erreur
     * @override
     */	
    protected function traitementSelect(string $table, ?array $champs) : ?array{
        switch($table){  
            case "livre" :
                return $this->selectAllLivres();
            case "dvd" :
                return $this->selectAllDvd();
            case "revue" :
                return $this->selectAllRevues();
            case "exemplaire" :
                return $this->selectExemplairesRevue($champs);
            case "commandesdocument" :
                return $this->selectCommandesDocument($champs);
            case "commandesrevue" :
                return $this->selectCommandesRevue($champs);
            case "abonnementsrevuesfinproche" :
                return $this->selectAbonnementsRevuesFinProche();
            case "genre" :
            case "public" :
            case "rayon" :
            case "etat" :
                // select portant sur une table contenant juste id et libelle
                return $this->selectTableSimple($table);
            case "" :
                // return $this->uneFonction(parametres);
            default:
                // cas général
                return $this->selectTuplesOneTable($table, $champs);
        }	
    }

    /**
     * demande d'ajout (insert)
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples ajoutés ou null si erreur
     * @override
     */	
    protected function traitementInsert(string $table, ?array $champs) : ?int{
        switch($table){
            case "livre" :
                 return $this->insertLivre($champs);
            case "dvd" :
                 return $this->insertDvd($champs);
            case "revue" : 
                 return $this->insertRevue($champs);
            case "commandedocument" :
                return $this->insertCommandeDocument($champs);
            case "commandesrevue" :
                return $this->insertCommandeRevue($champs);
            default:                    
                // cas général
                return $this->insertOneTupleOneTable($table, $champs);	
        }
    }
    
    /**
     * demande de modification (update)
     * @param string $table
     * @param string|null $id
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples modifiés ou null si erreur
     * @override
     */	
    protected function traitementUpdate(string $table, ?string $id, ?array $champs) : ?int{
        switch($table){
            case "livre" :
                 return $this->updateLivre($id, $champs);
            case "dvd" :
                 return $this->updateDvd($id, $champs);
            case "revue" : 
                 return $this->updateRevue($id, $champs);
            case "exemplaire" :
                return $this->updateExemplaire($id, $champs);
            default:                    
                // cas général
                return $this->updateOneTupleOneTable($table, $id, $champs);
        }	
    }  
    
    /**
     * demande de suppression (delete)
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples supprimés ou null si erreur
     * @override
     */	
    protected function traitementDelete(string $table, ?array $champs) : ?int{
        switch($table){
            case "livre":
                return $this->deleteLivre($champs);
            case "dvd":
                return $this->deleteDvd($champs);
            case "revue":
                return $this->deleteRevue($champs);
            case "commandedocument":
                return $this->deleteCommandeDocument($champs);
            case "commandesrevue":
                return $this->deleteCommandeRevue($champs);
            case "exemplaire":
                return $this->deleteExemplaire($champs);
            default:                    
                // cas général
                return $this->deleteTuplesOneTable($table, $champs);	
        }
    }	    
        
    /**
     * récupère les tuples d'une seule table
     * @param string $table
     * @param array|null $champs
     * @return array|null 
     */
    private function selectTuplesOneTable(string $table, ?array $champs) : ?array{
        if(empty($champs)){
            // tous les tuples d'une table
            $requete = "select * from $table;";
            return $this->conn->queryBDD($requete);  
        }else{
            // tuples spécifiques d'une table
            $requete = "select * from $table where ";
            foreach ($champs as $key => $value){
                $requete .= "$key=:$key and ";
            }
            // (enlève le dernier and)
            $requete = substr($requete, 0, strlen($requete)-5);	          
            return $this->conn->queryBDD($requete, $champs);
        }
    }	

    /**
     * demande d'ajout (insert) d'un tuple dans une table
     * @param string $table
     * @param array|null $champs
     * @return int|null nombre de tuples ajoutés (0 ou 1) ou null si erreur
     */	
    private function insertOneTupleOneTable(string $table, ?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        // construction de la requête
        $requete = "insert into $table (";
        foreach ($champs as $key => $value){
            $requete .= "$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);
        $requete .= ") values (";
        foreach ($champs as $key => $value){
            $requete .= ":$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);
        $requete .= ");";
        return $this->conn->updateBDD($requete, $champs);
    }

    /**
     * demande de modification (update) d'un tuple dans une table
     * @param string $table
     * @param string\null $id
     * @param array|null $champs 
     * @return int|null nombre de tuples modifiés (0 ou 1) ou null si erreur
     */	
    private function updateOneTupleOneTable(string $table, ?string $id, ?array $champs) : ?int {
        if(empty($champs)){
            return null;
        }
        if(is_null($id)){
            return null;
        }
        // construction de la requête
        $requete = "update $table set ";
        foreach ($champs as $key => $value){
            $requete .= "$key=:$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);				
        $champs["id"] = $id;
        $requete .= " where id=:id;";		
        return $this->conn->updateBDD($requete, $champs);	        
    }
    
    /**
     * demande de suppression (delete) d'un ou plusieurs tuples dans une table
     * @param string $table
     * @param array|null $champs
     * @return int|null nombre de tuples supprimés ou null si erreur
     */
    private function deleteTuplesOneTable(string $table, ?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        // construction de la requête
        $requete = "delete from $table where ";
        foreach ($champs as $key => $value){
            $requete .= "$key=:$key and ";
        }
        // (enlève le dernier and)
        $requete = substr($requete, 0, strlen($requete)-5);   
        return $this->conn->updateBDD($requete, $champs);	        
    }
 
    /**
     * récupère toutes les lignes d'une table simple (qui contient juste id et libelle)
     * @param string $table
     * @return array|null
     */
    private function selectTableSimple(string $table) : ?array{
        $requete = "select * from $table order by libelle;";		
        return $this->conn->queryBDD($requete);	    
    }
    
    /**
     * récupère toutes les lignes de la table Livre et les tables associées
     * @return array|null
     */
    private function selectAllLivres() : ?array{
        $requete = "Select l.id, l.ISBN, l.auteur, d.titre, d.image, l.collection, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from livre l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "order by titre ";		
        return $this->conn->queryBDD($requete);
    }	

    /**
     * récupère toutes les lignes de la table DVD et les tables associées
     * @return array|null
     */
    private function selectAllDvd() : ?array{
        $requete = "Select l.id, l.duree, l.realisateur, d.titre, d.image, l.synopsis, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from dvd l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "order by titre ";	
        return $this->conn->queryBDD($requete);
    }	

    /**
     * récupère toutes les lignes de la table Revue et les tables associées
     * @return array|null
     */
    private function selectAllRevues() : ?array{
        $requete = "Select l.id, l.periodicite, d.titre, d.image, l.delaiMiseADispo, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from revue l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "order by titre ";
        return $this->conn->queryBDD($requete);
    }	

    /**
     * récupère tous les exemplaires d'une revue
     * @param array|null $champs 
     * @return array|null
     */
    private function selectExemplairesRevue(?array $champs) : ?array{
        if(empty($champs)){
            return null;
        }
        if(!array_key_exists('id', $champs)){
            return null;
        }
        $champNecessaire['id'] = $champs['id'];
        $requete = "Select e.id, e.numero, e.dateAchat, e.photo, e.idEtat, et.libelle as libelleEtat ";
        $requete .= "from exemplaire e join document d on e.id=d.id ";
        $requete .= "join etat et on e.idEtat = et.id ";
        $requete .= "where e.id = :id ";
        $requete .= "order by e.dateAchat DESC";
        return $this->conn->queryBDD($requete, $champNecessaire);
    }

    /**
     * modifie l'état d'un exemplaire (clé composite : id document + numero)
     * @param string|null $id id du document
     * @param array|null $champs doit contenir 'numero' et 'idEtat'
     * @return int|null nombre de tuples modifiés ou null si erreur
     */
    private function updateExemplaire(?string $id, ?array $champs) : ?int{
        if(empty($id) || empty($champs)){
            return null;
        }
        if(!array_key_exists('numero', $champs) || !array_key_exists('idEtat', $champs)){
            return null;
        }
        $requete = "UPDATE exemplaire SET idEtat = :idEtat WHERE id = :id AND numero = :numero;";
        $params = [
            'idEtat' => $champs['idEtat'],
            'id' => $id,
            'numero' => $champs['numero']
        ];
        return $this->conn->updateBDD($requete, $params);
    }
    
        /**
     * récupère les commandes d'un document (livre ou DVD)
     * joint commande, commandedocument et suivi
     * filtre par idLivreDvd, tri par dateCommande DESC
     * @param array|null $champs doit contenir 'idLivreDvd'
     * @return array|null
     */
    private function selectCommandesDocument(?array $champs) : ?array{
        if(empty($champs)){
            return null;
        }
        if(!array_key_exists('idLivreDvd', $champs)){
            return null;
        }

        $champNecessaire['idLivreDvd'] = $champs['idLivreDvd'];

        $requete = "SELECT c.id AS id, c.dateCommande, c.montant, cd.nbExemplaire, cd.idSuivi AS idSuivi, s.libelle AS suivi ";
        $requete .= "FROM commande c ";
        $requete .= "INNER JOIN commandedocument cd ON c.id = cd.id ";
        $requete .= "INNER JOIN suivi s ON cd.idSuivi = s.id ";
        $requete .= "WHERE cd.idLivreDvd = :idLivreDvd ";
        $requete .= "ORDER BY c.dateCommande DESC";

        return $this->conn->queryBDD($requete, $champNecessaire);
    }

    /**
     * récupère les commandes / abonnements d'une revue
     * joint commande et abonnement, filtre par idRevue, tri par dateCommande DESC
     * @param array|null $champs doit contenir 'idRevue'
     * @return array|null
     */
    private function selectCommandesRevue(?array $champs) : ?array{
        if(empty($champs) || !array_key_exists('idRevue', $champs)){
            return null;
        }
        $champNecessaire['idRevue'] = $champs['idRevue'];

        $requete = "SELECT c.id AS id, c.dateCommande, c.montant, a.dateFinAbonnement, a.idRevue ";
        $requete .= "FROM commande c ";
        $requete .= "INNER JOIN abonnement a ON c.id = a.id ";
        $requete .= "WHERE a.idRevue = :idRevue ";
        $requete .= "ORDER BY c.dateCommande DESC";

        return $this->conn->queryBDD($requete, $champNecessaire);
    }

    /**
     * récupère les abonnements de revues dont la date de fin est dans les 30 prochains jours
     * joint abonnement, revue et document pour le titre, tri par dateFinAbonnement croissant
     * @return array|null
     */
    private function selectAbonnementsRevuesFinProche() : ?array{
        $requete = "SELECT d.titre, a.dateFinAbonnement ";
        $requete .= "FROM abonnement a ";
        $requete .= "INNER JOIN revue r ON a.idRevue = r.id ";
        $requete .= "INNER JOIN document d ON r.id = d.id ";
        $requete .= "WHERE a.dateFinAbonnement >= CURDATE() ";
        $requete .= "AND a.dateFinAbonnement <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) ";
        $requete .= "ORDER BY a.dateFinAbonnement ASC";
        return $this->conn->queryBDD($requete);
    }

        // ====== TRANSACTIONS + INSERT LIVRE ======

    private function beginTransactionSql() : bool {
        return $this->conn->updateBDD("START TRANSACTION;") !== null;
    }
    private function commitSql() : bool {
        return $this->conn->updateBDD("COMMIT;") !== null;
    }
    private function rollbackSql() : bool {
        return $this->conn->updateBDD("ROLLBACK;") !== null;
    }

    private function insertLivre(?array $champs) : ?int {
        if (empty($champs)) return null;

        $id = $champs["id"] ?? null;
        $titre = $champs["titre"] ?? null;
        $idRayon = $champs["idRayon"] ?? null;
        $idPublic = $champs["idPublic"] ?? null;
        $idGenre = $champs["idGenre"] ?? null;

        if (empty($id) || empty($titre) || empty($idRayon) || empty($idPublic) || empty($idGenre)) {
            return null;
        }

        $doc = [
            "id" => $id,
            "titre" => $titre,
            "image" => $champs["image"] ?? null,
            "idRayon" => $idRayon,
            "idPublic" => $idPublic,
            "idGenre" => $idGenre
        ];
        $livreDvd = ["id" => $id];
        $livre = [
            "id" => $id,
            "ISBN" => $champs["ISBN"] ?? null,
            "auteur" => $champs["auteur"] ?? null,
            "collection" => $champs["collection"] ?? null
        ];

        if (!$this->beginTransactionSql()) return null;

        $n1 = $this->insertOneTupleOneTable("document", $doc);
        if ($n1 !== 1) { $this->rollbackSql(); return null; }

        $n2 = $this->insertOneTupleOneTable("livres_dvd", $livreDvd);
        if ($n2 !== 1) { $this->rollbackSql(); return null; }

        $n3 = $this->insertOneTupleOneTable("livre", $livre);
        if ($n3 !== 1) { $this->rollbackSql(); return null; }

        if (!$this->commitSql()) { $this->rollbackSql(); return null; }
        return 1;
    }
    
    private function insertDvd(?array $champs): ?int {
        if (empty($champs)) return null;
        
        $id = $champs["id"] ?? null;
        $titre = $champs["titre"] ?? null;
        $idRayon = $champs["idRayon"] ?? null;
        $idPublic = $champs["idPublic"] ?? null;
        $idGenre = $champs["idGenre"] ?? null;

        if (empty($id) || empty($titre) || empty($idRayon) || empty($idPublic) || empty($idGenre)) {
            return null;
        }

        $doc = [
            "id" => $id,
            "titre" => $titre,
            "image" => $champs["image"] ?? null,
            "idRayon" => $idRayon,
            "idPublic" => $idPublic,
            "idGenre" => $idGenre
        ];
        $livreDvd = ["id" => $id];
        $dvd = [
            "id" => $id,
            "duree" => $champs["duree"] ?? null,
            "realisateur" => $champs["realisateur"] ?? null,
            "synopsis" => $champs["synopsis"] ?? null
        ];

        if (!$this->beginTransactionSql()) return null;

        $n1 = $this->insertOneTupleOneTable("document", $doc);
        if ($n1 !== 1) { $this->rollbackSql(); return null; }

        $n2 = $this->insertOneTupleOneTable("livres_dvd", $livreDvd);
        if ($n2 !== 1) { $this->rollbackSql(); return null; }

        $n3 = $this->insertOneTupleOneTable("dvd", $dvd);
        if ($n3 !== 1) { $this->rollbackSql(); return null; }

        if (!$this->commitSql()) { $this->rollbackSql(); return null; }
        return 1;
    }
    
    private function insertRevue(?array $champs): ?int {
            if (empty($champs)) return null;

            $id = $champs["id"] ?? null;
            $titre = $champs["titre"] ?? null;
            $idRayon = $champs["idRayon"] ?? null;
            $idPublic = $champs["idPublic"] ?? null;
            $idGenre = $champs["idGenre"] ?? null;

            if (empty($id) || empty($titre) || empty($idRayon) || empty($idPublic) || empty($idGenre)) {
                return null;
            }

            $doc = [
                "id" => $id,
                "titre" => $titre,
                "image" => $champs["image"] ?? null,
                "idRayon" => $idRayon,
                "idPublic" => $idPublic,
                "idGenre" => $idGenre
            ];
            $revue = [
                "id" => $id,
                "periodicite" => $champs["periodicite"] ?? null,
                "delaiMiseADispo" => $champs["delaiMiseADispo"] ?? null,
            ];

            if (!$this->beginTransactionSql()) return null;

            $n1 = $this->insertOneTupleOneTable("document", $doc);
            if ($n1 !== 1) { $this->rollbackSql(); return null; }

            $n2 = $this->insertOneTupleOneTable("revue", $revue);
            if ($n2 !== 1) { $this->rollbackSql(); return null; }

            if (!$this->commitSql()) { $this->rollbackSql(); return null; }
            return 1;
        }
    private function updateLivre(?string $id, ?array $champs) : ?int {
        if (empty($id) || empty($champs)) return null;

        // Interdit de modifier l'id
        unset($champs["id"]);

        // Champs possibles pour document
        $doc = [];
        foreach (["titre","image","idRayon","idPublic","idGenre"] as $k) {
            if (array_key_exists($k, $champs)) {
                $doc[$k] = $champs[$k];
            }
        }

        // Champs possibles pour livre
        $livre = [];
        foreach (["ISBN","auteur","collection"] as $k) {
            if (array_key_exists($k, $champs)) {
                $livre[$k] = $champs[$k];
            }
        }

        // Rien à mettre à jour
        if (empty($doc) && empty($livre)) return null;

        if (!$this->beginTransactionSql()) return null;

        $total = 0;

        // Update document si nécessaire
        if (!empty($doc)) {
            $n1 = $this->updateOneTupleOneTable("document", $id, $doc);
            if ($n1 === null) { $this->rollbackSql(); return null; }
            $total += $n1;
        }

        // Update livre si nécessaire
        if (!empty($livre)) {
            $n2 = $this->updateOneTupleOneTable("livre", $id, $livre);
            if ($n2 === null) { $this->rollbackSql(); return null; }
            $total += $n2;
        }

        if (!$this->commitSql()) { $this->rollbackSql(); return null; }
        return $total;
    }
    private function updateDvd(?string $id, ?array $champs) : ?int {
        if (empty($id) || empty($champs)) return null;

        // Interdit de modifier l'id
        unset($champs["id"]);

        // Champs possibles pour document
        $doc = [];
        foreach (["titre","image","idRayon","idPublic","idGenre"] as $k) {
            if (array_key_exists($k, $champs)) {
                $doc[$k] = $champs[$k];
            }
        }

        // Champs possibles pour dvd
        $dvd = [];
        foreach (["duree","realisateur","synopsis"] as $k) {
            if (array_key_exists($k, $champs)) {
                $dvd[$k] = $champs[$k];
            }
        }

        // Rien à mettre à jour
        if (empty($doc) && empty($dvd)) return null;

        if (!$this->beginTransactionSql()) return null;

        $total = 0;

        // Update document si nécessaire
        if (!empty($doc)) {
            $n1 = $this->updateOneTupleOneTable("document", $id, $doc);
            if ($n1 === null) { $this->rollbackSql(); return null; }
            $total += $n1;
        }

        // Update dvd si nécessaire
        if (!empty($dvd)) {
            $n2 = $this->updateOneTupleOneTable("dvd", $id, $dvd);
            if ($n2 === null) { $this->rollbackSql(); return null; }
            $total += $n2;
        }

        if (!$this->commitSql()) { $this->rollbackSql(); return null; }
        return $total;
    }
    private function updateRevue(?string $id, ?array $champs) : ?int {
        if (empty($id) || empty($champs)) return null;

        // Interdit de modifier l'id
        unset($champs["id"]);

        // Champs possibles pour document
        $doc = [];
        foreach (["titre", "image", "idRayon", "idPublic", "idGenre"] as $k) {
            if (array_key_exists($k, $champs)) {
                $doc[$k] = $champs[$k];
            }
        }

        // Champs possibles pour revue
        $revue = [];
        foreach (["periodicite", "delaiMiseADispo"] as $k) {
            if (array_key_exists($k, $champs)) {
                $revue[$k] = $champs[$k];
            }
        }

        // Rien à mettre à jour
        if (empty($doc) && empty($revue)) return null;

        if (!$this->beginTransactionSql()) return null;

        $total = 0;

        // Update document si nécessaire
        if (!empty($doc)) {
            $n1 = $this->updateOneTupleOneTable("document", $id, $doc);
            if ($n1 === null) { $this->rollbackSql(); return null; }
            $total += $n1;
        }

        // Update revue si nécessaire
        if (!empty($revue)) {
            $n2 = $this->updateOneTupleOneTable("revue", $id, $revue);
            if ($n2 === null) { $this->rollbackSql(); return null; }
            $total += $n2;
        }

        if (!$this->commitSql()) { $this->rollbackSql(); return null; }
        return $total;
    }
    
    private function countCommandesLivreDvd(string $id) : int {
        $res = $this->conn->queryBDD(
            "SELECT COUNT(*) AS nb FROM commandedocument WHERE idLivreDvd = :id;",
            ["id" => $id]
        );
        return (int)($res[0]["nb"] ?? 0);
    }

    private function countExemplairesRevue(string $id) : int {
        $res = $this->conn->queryBDD(
            "SELECT COUNT(*) AS nb FROM exemplaire WHERE id = :id;",
            ["id" => $id]
        );
        return (int)($res[0]["nb"] ?? 0);
    }
    private function deleteLivre(?array $champs) : ?int {
        if (empty($champs) || empty($champs["id"])) return null;
        $id = $champs["id"];

        // Interdit si commandes
        if ($this->countCommandesLivreDvd($id) > 0) {
            return 0; // suppression refusée
        }

        if (!$this->beginTransactionSql()) return null;

        $n1 = $this->deleteTuplesOneTable("livre", ["id" => $id]);
        if ($n1 === null) { $this->rollbackSql(); return null; }

        $n2 = $this->deleteTuplesOneTable("livres_dvd", ["id" => $id]);
        if ($n2 === null) { $this->rollbackSql(); return null; }

        $n3 = $this->deleteTuplesOneTable("document", ["id" => $id]);
        if ($n3 === null) { $this->rollbackSql(); return null; }

        if (!$this->commitSql()) { $this->rollbackSql(); return null; }
        return $n1 + $n2 + $n3;
    }
    private function deleteDvd(?array $champs) : ?int {
        if (empty($champs) || empty($champs["id"])) return null;
        $id = $champs["id"];

        // Interdit si commandes
        if ($this->countCommandesLivreDvd($id) > 0) {
            return 0;
        }

        if (!$this->beginTransactionSql()) return null;

        $n1 = $this->deleteTuplesOneTable("dvd", ["id" => $id]);
        if ($n1 === null) { $this->rollbackSql(); return null; }

        $n2 = $this->deleteTuplesOneTable("livres_dvd", ["id" => $id]);
        if ($n2 === null) { $this->rollbackSql(); return null; }

        $n3 = $this->deleteTuplesOneTable("document", ["id" => $id]);
        if ($n3 === null) { $this->rollbackSql(); return null; }

        if (!$this->commitSql()) { $this->rollbackSql(); return null; }
        return $n1 + $n2 + $n3;
    }
    private function deleteRevue(?array $champs) : ?int {
        if (empty($champs) || empty($champs["id"])) return null;
        $id = $champs["id"];

        // Interdit si exemplaires
        if ($this->countExemplairesRevue($id) > 0) {
            return 0;   // était: return 0;
        }

        if (!$this->beginTransactionSql()) return null;

        $n1 = $this->deleteTuplesOneTable("revue", ["id" => $id]);
        if ($n1 === null) { $this->rollbackSql(); return null; }

        $n2 = $this->deleteTuplesOneTable("document", ["id" => $id]);
        if ($n2 === null) { $this->rollbackSql(); return null; }

        if (!$this->commitSql()) { $this->rollbackSql(); return null; }
        return $n1 + $n2;
    }

    /**
     * supprime un exemplaire (clé composite : id + numero)
     * @param array|null $champs doit contenir 'id' et 'numero'
     * @return int|null nombre de lignes supprimées ou null si erreur
     */
    private function deleteExemplaire(?array $champs) : ?int {
        if (empty($champs)) {
            return null;
        }
        if (!array_key_exists('id', $champs) || !array_key_exists('numero', $champs)) {
            return null;
        }
        $id = $champs['id'];
        $numero = $champs['numero'];
        if ($id === '' || $id === null) {
            return null;
        }
        $requete = "DELETE FROM exemplaire WHERE id = :id AND numero = :numero;";
        return $this->conn->updateBDD($requete, ['id' => $id, 'numero' => (int)$numero]);
    }
    
    /**
    * récupère le prochain id de commande
    * format : C0001, C0002, ...
    * @return string|null
    */
    private function getNextIdCommande() : ?string{
        $requete = "SELECT MAX(id) AS maxId FROM commande;";
        $result = $this->conn->queryBDD($requete);

        if($result === null){
            return null;
        }

        $maxId = $result[0]['maxId'] ?? null;

        if(empty($maxId)){
            return "C0001";
        }

        $num = intval(substr($maxId, 1)) + 1;
        return "C" . str_pad((string)$num, 4, "0", STR_PAD_LEFT);
    }
    
    /**
    * ajoute une commande de document (livre ou dvd)
    * insère dans commande puis dans commandedocument
    * @param array|null $champs
    * @return int|null
    */
    private function insertCommandeDocument(?array $champs) : ?int{
        if(empty($champs)) return null;

        $dateCommande = $champs["dateCommande"] ?? null;
        $montant = $champs["montant"] ?? null;
        $nbExemplaire = $champs["nbExemplaire"] ?? null;
        $idLivreDvd = $champs["idLivreDvd"] ?? null;
        $idSuivi = $champs["idSuivi"] ?? "00001";

        if(empty($dateCommande) || empty($montant) || empty($nbExemplaire) || empty($idLivreDvd)){
            return null;
        }

        $idCommande = $this->getNextIdCommande();
        if($idCommande === null){
            return null;
        }

        $commande = [
            "id" => $idCommande,
            "dateCommande" => $dateCommande,
            "montant" => $montant
        ];

        $commandeDocument = [
            "id" => $idCommande,
            "nbExemplaire" => $nbExemplaire,
            "idLivreDvd" => $idLivreDvd,
            "idSuivi" => $idSuivi
        ];

        if (!$this->beginTransactionSql()) return null;

        $n1 = $this->insertOneTupleOneTable("commande", $commande);
        if ($n1 !== 1) { $this->rollbackSql(); return null; }

        $n2 = $this->insertOneTupleOneTable("commandedocument", $commandeDocument);
        if ($n2 !== 1) { $this->rollbackSql(); return null; }

        if (!$this->commitSql()) { $this->rollbackSql(); return null; }

        return 1;
    }

    /**
    * ajoute une commande / abonnement de revue
    * insère dans commande puis dans abonnement
    * @param array|null $champs dateCommande, montant, dateFinAbonnement, idRevue
    * @return int|null
    */
    private function insertCommandeRevue(?array $champs) : ?int{
        if(empty($champs)) return null;

        $dateCommande = $champs["dateCommande"] ?? null;
        $montant = $champs["montant"] ?? null;
        $dateFinAbonnement = $champs["dateFinAbonnement"] ?? null;
        $idRevue = $champs["idRevue"] ?? null;

        if(empty($dateCommande) || $montant === null || $montant === '' || empty($dateFinAbonnement) || empty($idRevue)){
            return null;
        }

        $idCommande = $this->getNextIdCommande();
        if($idCommande === null){
            return null;
        }

        $commande = [
            "id" => $idCommande,
            "dateCommande" => $dateCommande,
            "montant" => $montant
        ];

        $abonnement = [
            "id" => $idCommande,
            "dateFinAbonnement" => $dateFinAbonnement,
            "idRevue" => $idRevue
        ];

        if (!$this->beginTransactionSql()) return null;

        $n1 = $this->insertOneTupleOneTable("commande", $commande);
        if ($n1 !== 1) { $this->rollbackSql(); return null; }

        $n2 = $this->insertOneTupleOneTable("abonnement", $abonnement);
        if ($n2 !== 1) { $this->rollbackSql(); return null; }

        if (!$this->commitSql()) { $this->rollbackSql(); return null; }

        return 1;
    }

    /**
    * supprime une commande de document
    * suppression autorisée uniquement si la commande n'est pas livrée ni réglée
    * @param array|null $champs doit contenir id
    * @return int|null
    */
    private function deleteCommandeDocument(?array $champs) : ?int{
    error_log("[MyAccessBDD] deleteCommandeDocument appelé avec: " . json_encode($champs));

    if (empty($champs) || empty($champs["id"])) {
        error_log("[MyAccessBDD] deleteCommandeDocument: champs/id manquant");
        return null;
    }

    $id = $champs["id"];

    $requete = "SELECT idSuivi FROM commandedocument WHERE id = :id;";
    $res = $this->conn->queryBDD($requete, ["id" => $id]);

    if ($res === null || count($res) == 0) {
        error_log("[MyAccessBDD] deleteCommandeDocument: commande $id introuvable");
        return null;
    }

    $idSuivi = $res[0]["idSuivi"] ?? null;
    error_log("[MyAccessBDD] deleteCommandeDocument: id=$id, idSuivi=$idSuivi");

    if ($idSuivi === "00003" || $idSuivi === "00004") {
        error_log("[MyAccessBDD] deleteCommandeDocument: suppression refusée (suivi livrée/réglée)");
        return 0;
    }

    if (!$this->beginTransactionSql()) {
        error_log("[MyAccessBDD] deleteCommandeDocument: beginTransactionSql KO");
        return null;
    }

    $n1 = $this->deleteTuplesOneTable("commandedocument", ["id" => $id]);
    if ($n1 === null) { $this->rollbackSql(); error_log("[MyAccessBDD] deleteCommandeDocument: delete commandedocument KO"); return null; }

    $n2 = $this->deleteTuplesOneTable("commande", ["id" => $id]);
    if ($n2 === null) { $this->rollbackSql(); error_log("[MyAccessBDD] deleteCommandeDocument: delete commande KO"); return null; }

    if (!$this->commitSql()) { $this->rollbackSql(); error_log("[MyAccessBDD] deleteCommandeDocument: commit KO"); return null; }

    $resFinal = ($n1 > 0 && $n2 > 0) ? 1 : 0;
    error_log("[MyAccessBDD] deleteCommandeDocument: retour=$resFinal (n1=$n1, n2=$n2)");

    return $resFinal;
    }

    /**
     * Règle métier : indique si une date de parution est comprise entre la date de commande et la date de fin d'abonnement.
     * @param string $dateCommande Date de commande (format BDD)
     * @param string $dateFinAbonnement Date de fin d'abonnement (format BDD)
     * @param string $dateParution Date de parution de l'exemplaire (format BDD)
     * @return bool true si dateParution est dans l'intervalle [dateCommande, dateFinAbonnement]
     */
    protected function ParutionDansAbonnement(string $dateCommande, string $dateFinAbonnement, string $dateParution) : bool {
        $dCommande = strtotime($dateCommande);
        $dFin = strtotime($dateFinAbonnement);
        $dParution = strtotime($dateParution);
        if ($dCommande === false || $dFin === false || $dParution === false) {
            return false;
        }
        return $dParution >= $dCommande && $dParution <= $dFin;
    }

    /**
    * supprime une commande / abonnement de revue
    * Règle métier : refus si au moins un exemplaire a une date de parution comprise entre dateCommande et dateFinAbonnement.
    * @param array|null $champs doit contenir id
    * @return int|null 1=supprimé, 0=refusé (exemplaire dans l'abonnement), null=erreur
    */
    private function deleteCommandeRevue(?array $champs) : ?int{
        if (empty($champs) || empty($champs["id"])) {
            return null;
        }

        $id = $champs["id"];

        // Récupérer l'abonnement (dateCommande, dateFinAbonnement, idRevue)
        $requeteAbo = "SELECT c.dateCommande, a.dateFinAbonnement, a.idRevue FROM commande c INNER JOIN abonnement a ON c.id = a.id WHERE a.id = :id";
        $resAbo = $this->conn->queryBDD($requeteAbo, ["id" => $id]);
        if ($resAbo === null || count($resAbo) === 0) {
            return null;
        }
        $dateCommande = $resAbo[0]["dateCommande"] ?? null;
        $dateFinAbonnement = $resAbo[0]["dateFinAbonnement"] ?? null;
        $idRevue = $resAbo[0]["idRevue"] ?? null;
        if (empty($dateCommande) || empty($dateFinAbonnement) || empty($idRevue)) {
            return null;
        }

        // Récupérer les exemplaires de la revue
        $exemplaires = $this->selectExemplairesRevue(["id" => $idRevue]);
        if ($exemplaires !== null) {
            foreach ($exemplaires as $ex) {
                $dateParution = $ex["dateAchat"] ?? null;
                if (!empty($dateParution) && $this->parutionDansAbonnement($dateCommande, $dateFinAbonnement, $dateParution)) {
                    return 0; // Refus : au moins un exemplaire rattaché à cet abonnement
                }
            }
        }

        if (!$this->beginTransactionSql()) {
            return null;
        }

        $n1 = $this->deleteTuplesOneTable("abonnement", ["id" => $id]);
        if ($n1 === null) { $this->rollbackSql(); return null; }

        $n2 = $this->deleteTuplesOneTable("commande", ["id" => $id]);
        if ($n2 === null) { $this->rollbackSql(); return null; }

        if (!$this->commitSql()) { $this->rollbackSql(); return null; }

        return ($n1 > 0 && $n2 > 0) ? 1 : 0;
    }
}
