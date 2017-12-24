<?php

$campo = "cep";
$metodo = $_GET;
$protocolo = "http://";
$url = $protocolo . "www.buscacep.correios.com.br/sistemas/buscacep/resultadoBuscaCepEndereco.cfm";
$CEP = preg_replace("/[^0-9]/", "", $metodo[$campo]);

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
    "relaxation" => urlencode($CEP),
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

if (count($matchsTable) > 0 && !curl_error($ch)) {

    $siglas = array(
        "AC" => "Acre",
        "AL" => "Alagoas",
        "AP" => "Amapá",
        "AM" => "Amazonas",
        "BA" => "Bahia",
        "CE" => "Ceará",
        "DF" => "Distrito Federal",
        "ES" => "Espírito Santo",
        "GO" => "Goiás",
        "MA" => "Maranhão",
        "MT" => "Mato Grosso",
        "MS" => "Mato Grosso do Sul",
        "MG" => "Minas Gerais",
        "PA" => "Pará",
        "PB" => "Paraíba",
        "PR" => "Paraná",
        "PE" => "Pernambuco",
        "PI" => "Piauí",
        "RJ" => "Rio de Janeiro",
        "RN" => "Rio Grande do Norte",
        "RS" => "Rio Grande do Sul",
        "RO" => "Rondônia",
        "RR" => "Roraima",
        "SC" => "Santa Catarina",
        "SP" => "São Paulo",
        "SE" => "Sergipe",
        "TO" => "Tocantins",
    );

    $sedes = array(
        "0" => "São Paulo",
        "1" => "Santos",
        "2" => "Rio de Janeiro",
        "3" => "Belo Horizonte",
        "4" => "Salvador",
        "5" => "Recife",
        "6" => "Fortaleza",
        "7" => "Brasília",
        "8" => "Curitiba",
        "9" => "Porto Alegre"
    );

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
        "estado" => array(
            "nome" => $siglas[clean(explode("/", $Federal)[1])],
            "sigla" => clean(explode("/", $Federal)[1])
        ),
        "sede" => $sedes[strval(intval(substr($CEP, 0, 1)))],
        "estrutura" => array(
            "regiao" => intval(substr($CEP, 0, 1)),
            "subregiao" => intval(substr($CEP, 1, 1)),
            "setor" => intval(substr($CEP, 2, 1)),
            "subsetor" => intval(substr($CEP, 3, 1)),
            "divisao" => intval(substr($CEP, 4, 1)),
            "distribuicao" => intval(substr($CEP, 5, 3))
        )
    );

    foreach (explode(",", $preNumber) as $range) {
        array_push($jsonData["numeros"], array("inicio" => intval(explode("/", $range)[0]), "fim" => intval(explode("/", $range)[1])));
    }
} else {
    $jsonData = array("sucesso" => false, "erro" => "");
    if (!curl_error($ch)) {
        if (strlen($CEP > 8) || strlen($CEP < 8)) {
            $jsonData["erro"] = "O CEP é constituido por 8 numeros, verifique novamente";
        } else {
            $jsonData["erro"] = "Ocorreu um erro inesperado";
        }
    } else {
        $jsonData["erro"] = "Ocorreu uma falha ao tentar conectar aos Correios";
    }
}

$return = json_encode($jsonData);
header("Content-Type: application/json; charset=utf-8");
echo $return;
