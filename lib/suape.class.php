<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */



class suape{
    const dsn = "pgsql:dbname=gw_alghero;user=postgres;password=postgres;host=127.0.0.1;port=5434";
    const baseURL = "https://servizi.sardegnasuap.it/suape-gestione-praticaFE/services/protocollazione/%s";
    const backOfficeURL = "https://servizi.sardegnasuap.it/suape-gestione-praticaFE/services/integrazioneBackOffice/%s";
    const key = "edrfikfgvbekgvb";
    const sportello = "dc74d8ee-7189-40ae-aac7-c428e329f08f";
    const ente = "6b7a4aa6-3dd5-4505-8ac6-02ea23a04a49";
    const dir = "pratiche".DIRECTORY_SEPARATOR;
    const debugDir = "debug".DIRECTORY_SEPARATOR;
    const intestazione = Array(
        "ufficio-destinatario" => Array("codice-amministrazione","codice-aoo","identificativo-suap"),
        "richiedente" => Array(
            "cognome",
            "nome",
            "codice-fiscale",
            "nazionalita",
            "senza-codice-fiscale",
            "partita-iva",
            "sesso",
            "nascita"=>Array(
                "stato",
                "provincia",
                "comune",
                "citta-straniera",
                "data"
            )
        ),
        "impresa" => Array(
            "ragione-sociale",
            "forma-giuridica"=>Array(
                
            ),
            "codice-fiscale",
            "partita-iva",
            "codice-REA",
            "stato",
            "identificativo-legale",
            "vat",
            "indirizzo",
            "legale-rappresentante" => Array(
                "cognome",
                "nome",
                "codice-fiscale",
                "nazionalita",
                "senza-codice-fiscale",
                "partita-iva",
                "sesso",
                "nascita"=>Array(
                    "stato",
                    "provincia",
                    "comune",
                    "citta-straniera",
                    "data"
                ),
                "carica"
            )
        ),
        "oggetto-comunicazione" => Array(),
        "codice-pratica" => Array(),
        "procura-speciale" => Array(),
        "dichiarante" => Array(),
        "domicilio-elettronico" => Array(),
        "impianto-produttivo" => Array(),
        "protocollo" => Array(),
        "riferimento" => Array()
    );

/*Metodo che restituisce data e ora attuali*/    
    static function now(){
        return date('Y-m-d h:i:s', time());
    }   
    
/*Metodo di debug*/
    static function debug($file,$data,$mode='a+'){
        $now=self::now();
        $file = (defined('PROJECT'))?(sprintf("%s-%s",PROJECT,$file)):($file);
        $f=fopen($file,$mode);
        ob_start();
        echo "------- DEBUG DEL $now -------\n";
        print_r($data);
        $result=ob_get_contents();
        ob_end_clean();
        fwrite($f,$result."\n-------------------------\n");
        fclose($f);
    }  
    
/*metodo che restituisce la connessione al DB*/    
    static function getDB(){
        $dbh = new PDO(self::dsn);
        return $dbh;
    }
/*Metodo che restituisce l'autenticazione da passare negli header*/    
    static function getAuAuth() {
        $d = date("Ymd");
        $AuAuth = hash("sha256",self::key.$d);
        return $AuAuth;
    }
    
/*Metodo che verifica la correttezza sintattica del datetime inserito*/    
    static function checkDateTime($date){
        
    }
    
    static function callService($url,$headers,$postData,$key){
        
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch,CURLOPT_POST, 0);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $data = json_decode($result,true);
        if ($data && array_key_exists("esito",$data) && $data["esito"]["valore"]==1){
            return Array("success"=>1,"result"=>$data[$key]);
        }
        elseif($data && array_key_exists("esito",$data) && !$data["esito"]["valore"]){
            return Array("success"=>0,"result"=>$data["esito"]);
        }
        else{
            return Array("success"=>-1,"result"=>$result);
        }
    }
/*Metodo che dati i parametri:
 *   $from di tipo DateTime
 *   $to di tipo DateTime
 * 
 *   restituisce un Array con success
 * 
 */    
    
    static function getListaEndoProcedimenti(){
        $key="endoprocedimentis";
        $url = sprintf(self::baseURL,"getListaEndoProcedimenti");
        $AuAuth = self::getAuAuth();
        $headers = array(
            "AU-auth: ".$AuAuth,
            "idente: ".self::ente,
            "Content-Type: application/json",
            "cache-control: no-cache",
            //'Postman-Token: 15996e83-7db5-4214-8a60-49dcf96437cf {"verifica": true,"notifica": false}'
        );
        $postData='{"verifica": true, "notifica": false}';
        $res = self::callService($url, $headers, $postData, $key);
        return $res;
    }
    
    static function getListaSettori(){
        $key="settoris";
        $url = sprintf(self::baseURL,"getListaSettori");
        $AuAuth = self::getAuAuth();
        $headers = array(
            "AU-auth: ".$AuAuth,
            "idente: ".self::ente,
            "Content-Type: application/json",
            "cache-control: no-cache",
            //'Postman-Token: 15996e83-7db5-4214-8a60-49dcf96437cf {"verifica": true,"notifica": false}'
        );
        $postData='{}';
        $res = self::callService($url, $headers, $postData, $key);
        return $res;
    }
    
    static function insertTipiSettori($data){
        $dbh = self::getDB();
        $sql = "INSERT INTO pe.e_settori(codice,nome,settore_padre) VALUES(?,?,?);";
        $stmt = $dbh->prepare($sql);
        
        for($i=0;$i<count($data);$i++){
            $values=Array($data[$i]["idSettore"],$data[$i]["nome"],$data[$i]["idSettorePadre"]);
            if(!$stmt->execute($values)){
                echo "Errore nell'inserimento del procedimento ".$data[$i]["nome"]."\n";
            }
        }
    }    
    
    static function getListaInterventi($settore=""){
        $key="interventis";
        $url = sprintf(self::baseURL,"getListaInterventi");
        $AuAuth = self::getAuAuth();
        $headers = array(
            "AU-auth: ".$AuAuth,
            "idente: ".self::ente,
            "Content-Type: application/json",
            "cache-control: no-cache",
            //'Postman-Token: 15996e83-7db5-4214-8a60-49dcf96437cf {"verifica": true,"notifica": false}'
        );
        $postData=sprintf('{"settore":"%s"}',$settore);
        $res = self::callService($url, $headers, $postData, $key);
        return $res;
    }
    
    static function insertTipiInterventi($data){
        $dbh = self::getDB();
        $sql = "INSERT INTO pe.e_intervento(codice,nome,descrizione,settore_padre) VALUES(?,?,?,?);";
        $stmt = $dbh->prepare($sql);
        
        for($i=0;$i<count($data);$i++){
            $values=Array($data[$i]["idIntervento"],$data[$i]["titolo"],$data[$i]["titolo"],$data[$i]["idSettorePadre"]);
            if(!$stmt->execute($values)){
                $err = $stmt->errorInfo();
                $message=sprintf("Errore : %s nell'inserimento del procedimento %s\n",$err[2],$data[$i]["titolo"]);
                echo $message;
            }
        }
    }   
    
    static function getListaEsitoParere(){
        $key="esitoPareres";
        $url = sprintf(self::baseURL,"getListaEsitoParere");
        $AuAuth = self::getAuAuth();
        $headers = array(
            "AU-auth: ".$AuAuth,
            "idente: ".self::ente,
            "Content-Type: application/json",
            "cache-control: no-cache",
            //'Postman-Token: 15996e83-7db5-4214-8a60-49dcf96437cf {"verifica": true,"notifica": false}'
        );
        $postData='{}';
        $res = self::callService($url, $headers, $postData, $key);
        return $res;
    }
    
    static function insertEsitiPareri($data){
        $dbh = self::getDB();
        $sql = "INSERT INTO pe.e_esiti(id,codice,nome) VALUES(?,?,?);";
        $stmt = $dbh->prepare($sql);
        
        for($i=0;$i<count($data);$i++){
            $values=Array($data[$i]["idEsito"],'---',$data[$i]["denominazione"]);
            if(!$stmt->execute($values)){
                $err = $stmt->errorInfo();
                $message=sprintf("Errore : %s nell'inserimento di %s\n",$err[2],$data[$i]["titolo"]);
                echo $message;
            }
        }
    }
    
    static function getListaStatoPratica($idProc=""){
        $key="statoPraticas";
        $url = sprintf(self::baseURL,"getListaStatoPratica");
        $AuAuth = self::getAuAuth();
        $headers = array(
            "AU-auth: ".$AuAuth,
            "idente: ".self::ente,
            "Content-Type: application/json",
            "cache-control: no-cache",
        );
        $res = Array("succes"=>1,"result"=>Array());
        if(!$idProc){
            $r = self::getListaTipoProcedimento();
            if($r["success"]===1){
                for($i=0;$i<count($r["result"]);$i++){
                    $postData=sprintf('{"idProcedimento": "%s"}',$r["result"][$i]["idProcedimento"]);
                    $rr = self::callService($url, $headers, $postData, $key);
                    $res["result"][$r["result"][$i]["idProcedimento"]] = $rr["result"];
                }
            }
        }
        else{
            $postData=sprintf('{"idProcedimento": "%s"}',$idProc);
            $rr = self::callService($url, $headers, $postData, $key);
            $res["result"][$idProc] = $rr["result"];
        }
        print "Stato Pratica\n";
        return $res;
    }

    static function getListaTipoProcedimento(){
        $key="tipologiaProcedimentos";
        $url = sprintf(self::baseURL,"getListaTipoProcedimento");
        $AuAuth = self::getAuAuth();
        $headers = array(
            "AU-auth: ".$AuAuth,
            "idente: ".self::ente,
            "Content-Type: application/json",
            "cache-control: no-cache",
            //'Postman-Token: 15996e83-7db5-4214-8a60-49dcf96437cf {"verifica": true,"notifica": false}'
        );
        $postData='{}';
        $res = self::callService($url, $headers, $postData, $key);
        return $res;
    }
    
    static function insertTipiProcedimento($data){
        $dbh = self::getDB();
        $sql = "INSERT INTO pe.e_tipopratica(id,nome,classe,menu_default,menu_file) VALUES(?,?,?,?,?);";
        $stmt = $dbh->prepare($sql);
        $menuFile="pratica";
        $menuDefault="10,20,40,50,70,80,92,110,100,90,91,135,210,250,260,280,295,300,293,305,160,170,285";
        for($i=0;$i<count($data);$i++){
            $values=Array($data[$i]["idProcedimento"],$data[$i]["denominazione"],$data[$i]["idClasseProcedimento"],$menuDefault,$menuFile);
            if(!$stmt->execute($values)){
                echo "Errore nell'inserimento del procedimento ".$data[$i]["idProcedimento"]."\n";
            }
        }
    }

    static function getListaSportelli(){
        $key="sportellis";
        $url = sprintf(self::baseURL,"getListaSportelli");
        $AuAuth = self::getAuAuth();
        $headers = array(
            "AU-auth: ".$AuAuth,
            "idente: ".self::ente,
            "Content-Type: application/json",
            "cache-control: no-cache",
            //'Postman-Token: 15996e83-7db5-4214-8a60-49dcf96437cf {"verifica": true,"notifica": false}'
        );
        $postData='{}';
        $res = self::callService($url, $headers, $postData, $key);
        return $res;
    }

    static function insertTipiSportello($data){
        $dbh = self::getDB();
        $sql = "INSERT INTO pe.e_sportelli(codice,nome,codice_aoo,codice_suape,identificativo_suape) VALUES(?,?,?,?,?);";
        $stmt = $dbh->prepare($sql);
        
        for($i=0;$i<count($data);$i++){
            $values=Array($data[$i]["idSportello"],$data[$i]["denominazione"],$data[$i]["codiceAOO"],$data[$i]["codiceSUAPE"],$data[$i]["identificativoSUAPE"]);
            if(!$stmt->execute($values)){
                echo "Errore nell'inserimento del procedimento ".$data[$i]["denominazione"]."\n";
            }
        }
    }
    
    static function getListaTipoParere(){
        $key="listaTipoPareres";
        $url = sprintf(self::baseURL,"getListaTipoParere");
        $AuAuth = self::getAuAuth();
        $headers = array(
            "AU-auth: ".$AuAuth,
            "idente: ".self::ente,
            "Content-Type: application/json",
            "cache-control: no-cache",
            //'Postman-Token: 15996e83-7db5-4214-8a60-49dcf96437cf {"verifica": true,"notifica": false}'
        );
        $postData='{}';
        $res = self::callService($url, $headers, $postData, $key);
        return $res;
    }

    static function getListaClassiComunicazione(){
        $key="classeComunicaziones";
        $url = sprintf(self::baseURL,"getListaClassiComunicazione");
        $AuAuth = self::getAuAuth();
        $headers = array(
            "AU-auth: ".$AuAuth,
            "idente: ".self::ente,
            "Content-Type: application/json",
            "cache-control: no-cache",
            //'Postman-Token: 15996e83-7db5-4214-8a60-49dcf96437cf {"verifica": true,"notifica": false}'
        );
        $postData='{}';
        $res = self::callService($url, $headers, $postData, $key);
        return $res;
    }
    
    static function listaPratiche($from, $to=""){
        $AuAuth = self::getAuAuth();
        $url = sprintf(self::baseURL,"getListaPratiche");
        
        if ($to){
            $postData = sprintf('{"dataInoltroDa":"%s", "dataInoltroA":"%s"}',$from,$to);
        }
        else{
            $postData = sprintf('{"dataInoltroDa":"%s"}',$from);
        }
        
        $headers = array(
            "AU-auth: ".$AuAuth,
            "idente: ".self::ente,
            "Content-Type: application/json",
            "cache-control: no-cache",
            //'Postman-Token: 15996e83-7db5-4214-8a60-49dcf96437cf {"verifica": true,"notifica": false}'
        );
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch,CURLOPT_POST, 0);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $data = json_decode($result,true);
        if ($data && array_key_exists("esito",$data) && $data["esito"]["valore"]==1){
            for($i=0;$i<count($data["listaPratiches"]);$i++){
                $res[]= $data["listaPratiches"][$i];
            }
            return Array("success"=>1, "data"=>$res);
        }
        elseif ($data && array_key_exists("esito",$data) && !$data["esito"]["valore"]) {
            return Array("success"=>0,"data"=>Array());
        }
        else{
            return Array("success"=>0,"data"=>Array());
        }
        
    }
    
    static function getDatiPratica($idPratica){
        $AuAuth = self::getAuAuth();
        $url = sprintf(self::baseURL,"getDatiPratica");
        $key = "pratica";
        $postData = sprintf('{"idPratica":"%s", "Moduli":true, "Pareri":true, "Allegati":true, "ModuloJSON":true}',$id);
        $postData = sprintf('{"idPratica":"%s"}',$idPratica);
        $headers = array(
            "AU-auth: ".$AuAuth,
            "idente: ".self::ente,
            "Content-Type: application/json",
            "cache-control: no-cache",
            //'Postman-Token: 15996e83-7db5-4214-8a60-49dcf96437cf {"verifica": true,"notifica": false}'
        );
        /*$ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch,CURLOPT_POST, 0);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $data = json_decode($result,true);*/
        $rr = self::callService($url, $headers, $postData, $key); 
        //if ($data && array_key_exists("esito",$data) && $data["esito"]["valore"]==1){
        if($rr["success"]===1){
            $data = $rr["result"];
            
            $praticaDir = self::dir.$data["idPratica"].DIRECTORY_SEPARATOR;
            
            /*Creazione della directori della pratica*/
            if (!is_dir($praticaDir)){
                mkdir($praticaDir);
                mkdir($praticaDir."moduli");
                mkdir($praticaDir."allegati");
                mkdir($praticaDir."pareri");
                mkdir($praticaDir."comunicazioni");
            }
            /*Inserimento dell'XML della pratica*/
            $text = base64_decode($data["xmlpratica"]);
            $xml = simplexml_load_string($text);
            $f = fopen($praticaDir."modulo.xml","w+");
            fwrite($f, $text);
            fclose($f);
            
            $fName = self::debugDir."moduloAgid.debug";
            $xmlData = json_decode(json_encode($xml),TRUE);
            /*print_r(xmlAgid::getIndirizzo($xmlData));
            print_r(xmlAgid::getCT($xmlData));
            print_r(xmlAgid::getCU($xmlData));
            print_r(xmlAgid::getRichiedente($xmlData));*/
            /*Inserimento del modello di riepilogo*/
            $text = base64_decode($data["modelloRiepilogo"]);
            $f = fopen($praticaDir."modello_riepilogo.pdf","w+");
            fwrite($f, $text);
            fclose($f);
            
            /*Importazione dei Moduli*/
            $moduli = self::getListaModuliPratica($idPratica);
            /*Importazione degli allegati*/
            $allegati = self::getListaAllegatiPratica($idPratica);
            /*Importazione delle Comunicazioni*/
            $comunicazioni = self::getListaComunicazioniPratica($idPratica);
            /*Importazione dei Pareri*/
            $pareri = self::getListaPareriPratica($idPratica);
            
            return Array("success"=>1, "data"=>$data);
        }
        else{
            return Array("success"=>0,"data"=>Array());
        }
    }
    
    static function getListaModuliPratica($idPratica){
        $AuAuth = self::getAuAuth();
        $url = sprintf(self::backOfficeURL,"getListaModuliPratica");
        $key = "modulis";
        //$postData = sprintf('{"idPratica":"%s", "Moduli":true, "Pareri":true, "Allegati":true, "ModuloJSON":true}',$id);
        $postData = sprintf('{"idPratica":"%s"}',$idPratica);
        $headers = array(
            "AU-auth: ".$AuAuth,
            "idente: ".self::ente,
            "Content-Type: application/json",
            "cache-control: no-cache",
            //'Postman-Token: 15996e83-7db5-4214-8a60-49dcf96437cf {"verifica": true,"notifica": false}'
        );
        $rr = self::callService($url, $headers, $postData, $key);
        $res = Array("success"=>1,"result" => Array());
        for($i=0;$i<count($rr["result"]);$i++){
            $data = $rr["result"][$i];
            $mess = sprintf("%d) Considero il modulo %s della pratica %s\n",$i,$data["idModulo"],$idPratica);
            print $mess;
            $rrr = self::getModuloPratica($data["idModulo"], $idPratica,$data);
            if ($rrr["success"]===1){
                $res["result"][] = $rrr["result"];
                self::inserisciRecord("pe.file_allegati", $data);
            }
        }
        return $res;
        
    }
    
    static function getModuloPratica($id,$idPratica,$info){
        $AuAuth = self::getAuAuth();
        $url = sprintf(self::backOfficeURL,"getModuloPratica");
        $key = "modulis";
        //$postData = sprintf('{"idPratica":"%s", "Moduli":true, "Pareri":true, "Allegati":true, "ModuloJSON":true}',$id);
        $postData = sprintf('{"idPratica":"%s", "idModulo":"%s"}',$idPratica,$id);
        $headers = array(
            "AU-auth: ".$AuAuth,
            "idente: ".self::ente,
            "Content-Type: application/json",
            "cache-control: no-cache",
            //'Postman-Token: 15996e83-7db5-4214-8a60-49dcf96437cf {"verifica": true,"notifica": false}'
        );
        $res = self::callService($url, $headers, $postData, $key);
        if($res["success"]===1){
            
            $praticaDir = self::dir.$idPratica.DIRECTORY_SEPARATOR."moduli".DIRECTORY_SEPARATOR;
            $data = $res["result"];
            
            for($i=0;$i<count($data);$i++){
                $ff = $data[$i];
                $mess = sprintf("\t%d) Considero il modulo %s della comunicazione %s\n",$i,$info["nomeFile"],$id);
                print $mess;

                self::scriviFile($praticaDir.$info["nomeFile"],$ff["fileModulo"]);
                self::inserisciRecord("pe.file_allegati", $ff);
               
            }
        }
        return $res;
    }
    
    static function getListaAllegatiPratica($idPratica){
        $AuAuth = self::getAuAuth();
        $key = "allegatis";
        $url = sprintf(self::backOfficeURL,"getListaAllegatiPratica");
        
        //$postData = sprintf('{"idPratica":"%s", "Moduli":true, "Pareri":true, "Allegati":true, "ModuloJSON":true}',$id);
        $postData = sprintf('{"idPratica":"%s"}',$idPratica);
        $headers = array(
            "AU-auth: ".$AuAuth,
            "idente: ".self::ente,
            "Content-Type: application/json",
            "cache-control: no-cache",
            //'Postman-Token: 15996e83-7db5-4214-8a60-49dcf96437cf {"verifica": true,"notifica": false}'
        );
        
        $rr = self::callService($url, $headers, $postData, $key);
        $res = Array("success"=>1,"result" => Array());
        for($i=0;$i<count($rr["result"]);$i++){
            $data = $rr["result"][$i];
            $mess = sprintf("%d) Considero l'allegato %s della pratica %s\n",$i,$data["idAllegato"],$idPratica);
            print $mess;
            $rrr = self::getAllegatoPratica($data["idAllegato"], $idPratica);
            if ($rrr["success"]===1){
                $res["result"][] = $rrr["result"];
                self::inserisciRecord("pe.allegati", $data);
            }
        }
        return $res;
    }
    
    static function getAllegatoPratica($id,$idPratica){
        $AuAuth = self::getAuAuth();
        $url = sprintf(self::backOfficeURL,"getAllegatoPratica");
        $key = "allegatis";
        //$postData = sprintf('{"idPratica":"%s", "Moduli":true, "Pareri":true, "Allegati":true, "ModuloJSON":true}',$id);
        $postData = sprintf('{"idPratica":"%s", "idAllegato":"%s"}',$idPratica,$id);
        $headers = array(
            "AU-auth: ".$AuAuth,
            "idente: ".self::ente,
            "Content-Type: application/json",
            "cache-control: no-cache",
            //'Postman-Token: 15996e83-7db5-4214-8a60-49dcf96437cf {"verifica": true,"notifica": false}'
        );
        $res = self::callService($url, $headers, $postData, $key);
        if($res["success"]===1){
            
            $praticaDir = self::dir.$idPratica.DIRECTORY_SEPARATOR."allegati".DIRECTORY_SEPARATOR;
            $data = $res["result"];
            
            for($i=0;$i<count($data);$i++){
                $ff = $data[$i];
                $mess = sprintf("\t%d) Considero il file %s della comunicazione %s\n",$i,$ff["nomeFile"],$id);
                print $mess;

                self::scriviFile($praticaDir.$ff["nomeFile"],$ff["fileAllegato"]);
                self::inserisciRecord("pe.file_allegati", $ff);
               
            }
        }
        return $res;
    }
    
    static function getListaComunicazioniPratica($idPratica){
        $AuAuth = self::getAuAuth();
        $key = "comunicazionis";
        $url = sprintf(self::backOfficeURL,"getListaComunicazioniPratica");
        $postData = sprintf('{"idPratica":"%s"}',$idPratica);
        $headers = array(
            "AU-auth: ".$AuAuth,
            "idente: ".self::ente,
            "Content-Type: application/json",
            "cache-control: no-cache",
            //'Postman-Token: 15996e83-7db5-4214-8a60-49dcf96437cf {"verifica": true,"notifica": false}'
        );
        $rr = self::callService($url, $headers, $postData, $key);
        $res = Array("success"=>1,"result" => Array());
        for($i=0;$i<count($rr["result"]);$i++){
            $data = $rr["result"][$i];
            $mess = sprintf("%d) Considero la Comunicazione %s della pratica %s\n",$i,$data["idComunicazione"],$idPratica);
            print $mess;
            $rrr = self::getComunicazionePratica($data["idComunicazione"], $idPratica);
            if ($rrr["success"]===1){
                $res["result"][] = $rrr["result"];
            }
        }
        return $res;
    }
    
    static function getComunicazionePratica($id,$idPratica){
        $AuAuth = self::getAuAuth();
        $key = "comunicazionis";
        $url = sprintf(self::backOfficeURL,"getComunicazionePratica");
        $postData = sprintf('{"idPratica":"%s", "idComunicazione":"%s"}',$idPratica,$id);
        $headers = array(
            "AU-auth: ".$AuAuth,
            "idente: ".self::ente,
            "Content-Type: application/json",
            "cache-control: no-cache",
            //'Postman-Token: 15996e83-7db5-4214-8a60-49dcf96437cf {"verifica": true,"notifica": false}'
        );
        $res = self::callService($url, $headers, $postData, $key);
        if($res["success"]===1){        
            $praticaDir = self::dir.$idPratica.DIRECTORY_SEPARATOR."comunicazioni".DIRECTORY_SEPARATOR;
            $data = $res["result"];
            
            self::inserisciRecord("pe.comunicazioni", $data);
            for($i=0;$i<count($data);$i++){
                for($j=0;$j<count($data[$i]["documentos"]);$j++){
                    $ff = $data[$i]["documentos"][$j];
                    $mess = sprintf("\t%d) Considero il file %s della comunicazione %s\n",$j,$ff["nome"],$id);
                    print $mess;
                    self::scriviFile($praticaDir.$ff["nome"],$ff["file"]);
                    
                }
            }
        }
        return $res;
    }
    
    static function getListaPareriPratica($idPratica){
        $AuAuth = self::getAuAuth();
        $key = "pareris";
        $url = sprintf(self::backOfficeURL,"getListaPareriPratica");
        
        //$postData = sprintf('{"idPratica":"%s", "Moduli":true, "Pareri":true, "Allegati":true, "ModuloJSON":true}',$id);
        $postData = sprintf('{"idPratica":"%s", "file":true}',$idPratica);
        $headers = array(
            "AU-auth: ".$AuAuth,
            "idente: ".self::ente,
            "Content-Type: application/json",
            "cache-control: no-cache",
            //'Postman-Token: 15996e83-7db5-4214-8a60-49dcf96437cf {"verifica": true,"notifica": false}'
        );
        
        $rr = self::callService($url, $headers, $postData, $key);
        $res = Array("success"=>1,"result" => Array());
        for($i=0;$i<count($rr["result"]);$i++){
            $data = $rr["result"][$i];
            $mess = sprintf("%d) Considero il parere %s del %s della pratica %s\n",$i,$data["idEnteTerzo"],$data["dataParere"],$idPratica);
            print $mess;
            $rrr = self::getParerePratica($data["idEndoprocedimento"], $idPratica,$data);
            if ($rrr["success"]===1){
                $res["result"][] = $rrr["result"];
                self::inserisciRecord("pe.pareri", $data);
            }
        }
        return $res;
    }
    
    static function getParerePratica($id,$idPratica,$info){
        $AuAuth = self::getAuAuth();
        $url = sprintf(self::backOfficeURL,"getParerePratica");
        $key = "pareris";
        //$postData = sprintf('{"idPratica":"%s", "Moduli":true, "Pareri":true, "Allegati":true, "ModuloJSON":true}',$id);
        $postData = sprintf('{"idPratica":"%s", "idEndoprocedimento":"%s", "idEnteTerzo":"%s"}',$idPratica,$id,$info["idEnteTerzo"]);
        $headers = array(
            "AU-auth: ".$AuAuth,
            "idente: ".self::ente,
            "Content-Type: application/json",
            "cache-control: no-cache",
            //'Postman-Token: 15996e83-7db5-4214-8a60-49dcf96437cf {"verifica": true,"notifica": false}'
        );
        $res = self::callService($url, $headers, $postData, $key);
        if($res["success"]===1){
            
            $praticaDir = self::dir.$idPratica.DIRECTORY_SEPARATOR."pareri".DIRECTORY_SEPARATOR;
            $data = $res["result"];
            //print_r($data);
            for($i=0;$i<count($data);$i++){
                $ff = $data[$i];
                $mess = sprintf("\t%d) Considero il Parere %s della comunicazione %s\n",$i,$ff["idFile"],$id);
                print $mess;

                self::scriviFile($praticaDir.$ff["idFile"].".xml",$ff["fileParere"]);
                self::inserisciRecord("pe.file_allegati", $ff);
               
            }
        }
        return $res;
    }
    
    static function scriviFile($fName,$file){
        $msg1=<<< EOT
***********************************************************
        Nessun dato passato    
***********************************************************                    

EOT;
        $msg2=<<< EOT
***********************************************************
        Nessun nome file    
***********************************************************                    

EOT;
        $msg3=<<< EOT
***********************************************************
        Dati non in base64    
***********************************************************   

EOT;
        $res = 0;
        if (!$file){
            print $msg1;
            return -1;
        }
        if(!$fName){
            print $msg2;
            return -2;
        }
        /*Scrittura del file*/
        $text = base64_decode($file);
        if (!$text){
            print $msg3;
            return -3;
        }
        
        $f = fopen($fName,"w");
        if(fwrite($f, $text)){
            $res = 1;
        }
        else{
            $message =<<< EOT
***********************************************************
        Impossibile scrivere il file $fName    
***********************************************************                    
EOT;
            print $message;
        }
        fclose($f);
        return $res;
    }
    
    static function inserisciRecord($tabella,$data){
        return 1;
    }
    
    static function getXmlData($data){
        
    }
    
    static function getIntestazione($data){
        $keys = Array("ufficio-destinatario","richiedente","impresa","oggetto-comunicazione","codice-pratica","procura-speciale","dichiarante","domicilio-elettronico","impianto-produttivo","protocollo","riferimento");
        foreach($keys as $key){
            if(array_key_exists($key, $data)){
                switch($key){
                    // Tipo di dato EstremiSUAP
                    case "ufficio-destinatario":
                        if (array_key_exists('@attributes', $data[$key])){
                            
                        }
                        break;
                    // Tipo di Dato AnagraficaPersona
                    case "richiedente":
                        if (array_key_exists($key, $data)){
                            
                        }
                        break;
                    // Tipo di Dato AnagraficaImpresa 
                    case "impresa":
                        break;
                    // Tipo di dato OggettoComunicazione
                    case "oggetto-comunicazione":
                        break;
                    // Tipo di dato Stringa
                    case "codice-pratica":
                        break;
                    // Tipo di dato ProcuraSpeciale
                    case "procura-speciale":
                        break;
                    // Tipo di dato EstremiDichiarante
                    case "dichiarante":
                        break;
                    // Tipo di dato EMailIndirizzo
                    case "domicilio-elettronico":
                        break;
                    // Tipo di dato ImpiantoProduttivo
                    case "impianto-produttivo":
                        break;
                    // Tipo di dato ProtocolloSUAP
                    case "protocollo":
                        break;
                    // Tipo di dato RiferimentoSUAP
                    case "riferimento":
                        break;
                }
            }
        }
    }
    
    static function getStruttura($data){
        
    }
    
    static function getDatiAggiuntivi($data){
        
    }
}

class xmlAgid{
    
    const catasto = Array(
        "tipo" => "",
        "comune-catastale"=>"",
        "sezione"=>"sezione",
        "foglio"=>"foglio",
        "mappale"=>"mappale",
        "subalterno"=>"sub"
    );
    
    const indirizzo = Array(
        "stato"=>"",
        "comune"=>"",
        "cap"=>"",
        "toponimo" => "",
        "denominazione-stradale"=>"",
        "numero-civico"=>"civico"
    );
    
    const anagrafica = Array(
        "cognome"=>"cognome",
        "nome"=>"nome",
        "codice-fiscale"=>"codfis",
        "nazionalita"=>"",
        "partita-iva"=>"piva",
        "sesso"=>"sesso",
        "pec"=>"pec",
        "telefono"=>"telefono",
        "qualifica"=>"titolo",
    );
    
    const nascita = Array(
        "data"=>"datanato",
        "stato"=>"",
        "provincia"=>"provnato",
        "comune"=>"comunato",
        "citta-straniera"=>"comunato"
    );
    const tree = Array(
        "indirizzo" => Array("intestazione","impianto-produttivo","indirizzo"),
        "catasto" => Array("intestazione","impianto-produttivo","dati-catastali"),
        "richiedente" => Array("intestazione","richiedente"),
        "impresa-richiedente" => Array("intestazione","impresa"),
    );
    static function plainData($data){
        if(!$data || !is_array($data)) return $data;
        if (array_key_exists('@attributes', $data)){
            foreach($data['@attributes'] as $k=>$v){
                $data[$k] = $v;
            }
            unset($data['@attributes']);
        }
        return $data;
    }
    
    static function transform($data,$keys){
        $res = Array();
        foreach($keys as $k=>$v){
            if($v) $res[$v]=$data[$k];
        }
        return $res;
    }
    
    static function getImpiantoProduttivo($data){
        $d = self::plainData($data);
        
    }
    
    static function getIndirizzo($data){
        $d = $data;
        foreach(self::tree["indirizzo"] as $k) $d = self::plainData($d[$k]);
         
        $result = Array();
        $tData = self::transform($d, self::indirizzo);
        $tData["via"] = trim(sprintf("%s %s",$d["toponimo"],$d["denominazione-stradale"]));
        $result[]=$tData;
        return $result;
    }
    
    static function getCT($data){
        $result = Array();
        $d = $data;
        foreach(self::tree["catasto"] as $k) $d = self::plainData($d[$k]);
        for($i=0;$i<count($d);$i++){
            $dd = self::plainData($d[$i]);
            if ($dd["tipo"] == "terreni") {
                $tData = self::transform($dd, self::catasto);
                $result[] = $tData;
            }
        }
        return $result;
    }
    static function getCU($data){
        $result = Array();
        $d = $data;
        foreach(self::tree["catasto"] as $k) {
            $d = self::plainData($d[$k]);
        }    
        for($i=0;$i<count($d);$i++){
            $dd = self::plainData($d[$i]);
            if ($dd["tipo"] == "fabbricati") {
                $tData = self::transform($dd, self::catasto);
                $result[] = $tData;
            }
        }
        return $result;
    }
    
    static function getRichiedente($data){
        $d = $data;
        foreach(self::tree["richiedente"] as $k) $d = self::plainData($d[$k]);
         
        $result = Array();
        $tData = self::transform($d, self::anagrafica);
        if (array_key_exists("nascita", $d)) {
            $d = self::plainData($d["nascita"]);
            $tData2 = self::transform($d, self::nascita);
        }
        $result[]=array_merge($tData,$tData2);
        return $result;
    }
}
?>