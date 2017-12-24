<?php

$campo = "cep";
$metodo = $_POST;
$protocolo = "http://";
$url = $protocolo . "www.buscacep.correios.com.br/sistemas/buscacep/resultadoBuscaCepEndereco.cfm";

function clean($des) {
    $clear = strip_tags($des);
    $clear = html_entity_decode($clear);
    $clear = urldecode($clear);
    $clear = preg_replace('/ +/', ' ', $clear);
    $clear = trim($clear);
    /* importante manter o codificador utf8, 
      se não o json não será gerado */
    return utf8_encode(trim($clear));
}

$fields = array(
    "relaxation" => urlencode($metodo[$campo]),
    "tipoCEP" => urlencode("ALL"),
    "semelhante" => urlencode("N")
);

foreach ($fields as $key => $value) {
    $fields_string .= $key . "=" . $value . "&";
}

rtrim($fields_string, "&");
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, count($fields));
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$result = curl_exec($ch);
curl_close($ch);

$regex = '#\<table class="tmptabela"\>(.+?)\<\/table\>#s';
preg_match($regex, $result, $matchsTable);
$matchTable = $matchsTable[0];

if (count($matchsTable) > 0) {

    $regex = '#\<td width="150"\>(.+?)\<\/td\>#s';
    preg_match($regex, $matchTable, $matchsLogradouro);
    $Logradouro = $matchsLogradouro[0];

    $regex = '#\<td width="90"\>(.+?)\<\/td\>#s';
    preg_match($regex, $matchTable, $matchsBairro);
    $Bairro = $matchsBairro[0];

    $regex = '#\<td width="80"\>(.+?)\<\/td\>#s';
    preg_match($regex, $matchTable, $matchsFederal);
    $Federal = $matchsFederal[0];

    $preNumber = str_replace(" a ", ",", str_replace("&nbsp;", "", str_replace(" de ", "", explode("-", $Logradouro)[1])));

    $jsonData = array(
        "sucesso" => true,
        "numeros" => array(),
        "rua" => clean(explode("-", $Logradouro)[0]),
        "bairro" => clean(str_replace("&nbsp;", "", $Bairro)),
        "cidade" => clean(explode("/", $Federal)[0]),
        "estado" => clean(explode("/", $Federal)[1])
    );

    foreach (explode(",", $preNumber) as $range) {
        array_push($jsonData["numeros"], array("inicio" => intval(explode("/", $range)[0]), "fim" => intval(explode("/", $range)[1])));
    }
} else {
    $jsonData = array("sucesso" => false);
}

$return = json_encode($jsonData);
header("Content-Type: application/json; charset=utf-8");
echo $return;
