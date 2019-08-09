<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once "lib".DIRECTORY_SEPARATOR."suape.class.php";
$ids=Array(
    "14120315-1359-438b-bc65-441a672e7c3d",
    "4ebe84bd-6921-4986-925c-b833c0019db5",
    "559266ac-28de-40f5-9fb1-0d5636e16f39",
    "2017b66d-8efd-4fa4-af40-6f19ff7f05f5",
    "e0d7e3ed-6d1b-4137-b20a-1369c2da5a1f",
    "2bf41b99-c8b8-48a3-a0c4-63c62b2351c8",
    "03c15dc5-85f6-484e-bc5c-4e6ec13bcb55",
    "571e438e-bd41-4c3f-9ad7-6329dae29493",
    "386453a7-f55e-476d-9d89-c9d0526df599",
    "7e9971aa-de97-48db-9a58-e3e5a32283e6",
    "d49c50eb-00a6-4c60-854c-d204a7c8440e",
    "f2ec909e-d8de-44db-adb7-f79622090c66",
    "76c0dfaa-919f-449e-a42a-f928828a048b",
    "313e0143-fa3a-4ef2-9aff-b58933cc0479",
    "adfa29cc-59de-4971-abef-29cb05169b22",
    "c46ff654-d124-4de8-bc5a-376760111b42",
    "7e08abb9-2143-4b31-8fe7-cf0334d87b86",
    "8c03aa57-a260-4285-a0fb-384a94e078b2",
    "769dee6e-7fcf-4f60-8248-409239315e6e",
    "2158a0ff-2ebd-4d49-81bc-556e20ecea5a",
    "7ed3cfbd-8f77-4e1f-b10a-86ce65575d7d",
    "c57d39bc-e2cd-41fe-ad20-3cd789ff1b4b",
    "b8c040de-0026-4f0f-b649-71126b9f6af7",
    "d9a28043-aced-4005-9cd5-8fb58bed42eb",
    "69e9aa8b-4803-4904-82b7-a54fbaf8efa0",
    "de69a9d0-c47c-43f6-94c0-de860e597ddc",
    "9ce085fe-7baa-42ae-98cd-b05a0246bab8",
    "39065fa0-12ea-496a-89ef-3eda62365322"
);
$idPratica = $ids[3];
$idAllegato=14942322;
$idModulo = 14860196;
$idComunicazione = 13661986;
//$res = suape::listaPratiche("2019-03-01T00:00:00","2019-08-09T00:00:00");

//print_r($res);die();
$unusedKeys=Array("idIter","interventis","codiceFiscale","mudulis","pareris","allegatis","modelloRiepilogo","moduliXMLs","moduliJSONs","vecchiFormato","xmlpratica");
$usedKeys=Array("idPratica","codice","idStatoPratica","protocollo","dataInoltro","dataUltimaIntegrazione");
/*
for($i=0;$i<count($res["data"]);$i++){
    $r = Array();
    foreach($usedKeys as $kk){
        $r[$kk]=$res["data"][$i][$kk];
    }
    $result[]=$r;
    $idsArr[]=$res["data"][$i]["idPratica"];
}*/

//$res = suape::getListaStatoPratica();
$res = suape::getListaEsitoParere();
if($res["success"]==1){
    suape::insertEsitiPareri($res["result"]);
}
//$res = suape::getListaSportelli();
//if($res["success"]==1){
//    suape::insertTipiSportello($res["result"]);
//}
//$res = suape::getListaEndoProcedimenti();
//$res = suape::getListaTipoProcedimento();
//if($res["success"]==1){
//    suape::insertTipiProcedimento($res["result"]);
//}
//$res = suape::getListaInterventi();
//if($res["success"]==1){
 //   suape::insertTipiInterventi($res["result"]);
//}
//$res = suape::getListaSettori();
//if($res["success"]==1){
//    suape::insertTipiSettori($res["result"]);
//}
//$res = suape::getDatiPratica($idPratica);
//$res = suape::getModuloPratica($idPratica,$idModulo);
//$res = suape::getListaModuliPratica($idPratica);
//$res = suape::getListaComunicazioniPratica($idPratica);
//$res = suape::getListaAllegatiPratica($idPratica);
//$res = suape::getListaPareriPratica($idPratica);
//$res = suape::getAllegatoPratica(14837816, $idPratica);
//$res = suape::getComunicazionePratica($idComunicazione, $idPratica);
//print_r($res);
?>