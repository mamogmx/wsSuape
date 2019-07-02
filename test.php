<?php
/*require_once "./lib/suape.class.php";
$ws = new suape();
$res = $ws->listaPratiche();
print_r($res);

die();*/
define('DSN','pgsql:dbname=gw_alghero;user=postgres;password=postgres;host=127.0.0.1;port=5434');
$id='endoprocedimenti';
//$url = "https://urlsand.esvalabs.com/?u=https://servizi.sardegnasuap.it/getListaEndoProcedimenti&e=3becf915&h=ee71eff5&f=n&p=y";
//$url = "https://servizi.sardegnasuap.it/suape-gestione-praticaFE/services/protocollazione/getListaEndoProcedimenti";
$baseUrl = "https://servizi.sardegnasuap.it/suape-gestione-praticaFE/services/protocollazione/%s";


$service["endoprocedimenti"] = "getListaEndoProcedimenti";
$service["settori"] = "getListaSettori";
$service["interventi"] = "getListaInterventi";
$service["listapratiche"] = "getListaPratiche";

$sql["endoprocedimenti"]="INSERT INTO elenchi.endoprocedimenti(codice,titolo,settore_padre,ente,notifica) VALUES(?,?,?,?,?)";
$sql["settori"] = "INSERT INTO elenchi.settori(codice,titolo,settore_padre) VALUES(?,?,?)";
$sql["interventi"] = "INSERT INTO elenchi.interventi(codice,titolo,settore_padre) VALUES(?,?,?)";

$jsonParams["endoprocedimenti"] = '{"verifica": true, "notifica": false}';
$jsonParams["settori"] = '{}';
$jsonParams["interventi"] = '{"settore":""}';
$jsonParams["listapratiche"] = '{"dataInoltroDa":"2019-05-01T00:00:00"}';
/*$jsonParams["listapratiche"] = Array(
    "DataInoltroDa"=>"2019-06-01"
);*/
$url = sprintf($baseUrl,$service[$id]);
$d = date("Ymd");
$key = "edrfikfgvbekgvb";
$AuAuth = hash("sha256",$key.$d);

$sportello = "dc74d8ee-7189-40ae-aac7-c428e329f08f";
$ente = "6b7a4aa6-3dd5-4505-8ac6-02ea23a04a49";
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL, $url);

curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "AU-auth: ".$AuAuth,
    "idente: ".$ente,
    "Content-Type: application/json",
    "cache-control: no-cache",
    //'Postman-Token: 15996e83-7db5-4214-8a60-49dcf96437cf {"verifica": true,"notifica": false}'
));
print_r($jsonParams[$id]);
curl_setopt($ch,CURLOPT_POST, 0);
curl_setopt($ch,CURLOPT_POSTFIELDS,$jsonParams[$id]);
curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
$data = json_decode($result,true);

if(!$data){
    print "Errore nella richiesta:\n$result\n";
}
else{
    print_r($data); die();
    $dbh = new PDO(DSN);
    $stmt = $dbh->prepare($sql[$id]);
    print_r($data);die();
    for($i=0;$i<count($data[$id."s"]);$i++){
        $d = $data[$id."s"][$i];
        switch($id){
            case "endoprocedimenti":
                $r = Array($d["intervento"]["idIntervento"],$d["intervento"]["titolo"],$d["intervento"]["idSettorePadre"],d["enteTezo"],$d["notifica"]);
                break;
            default :
                $r = array_values($d);
                break;
        }
        /*if (!$stmt->execute($r)){
            print_r($stmt->errorInfo());
        }*/
    }
}
?>
