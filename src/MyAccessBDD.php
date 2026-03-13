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
        $requete = "Select e.id, e.numero, e.dateAchat, e.photo, e.idEtat ";
        $requete .= "from exemplaire e join document d on e.id=d.id ";
        $requete .= "where e.id = :id ";
        $requete .= "order by e.dateAchat DESC";
        return $this->conn->queryBDD($requete, $champNecessaire);
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
}
