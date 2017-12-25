<?php

$campo = "cep";
$metodo = $_GET;
$protocolo = "http://";
$url = $protocolo . "www.buscacep.correios.com.br/sistemas/buscacep/resultadoBuscaCepEndereco.cfm";
$CEP = preg_replace("/[^0-9]/", "", $metodo[$campo]);

function clean($des) {
    $clear = strip_tags($des);
    $clear = str_replace("&nbsp;", "", $clear);
    $clear = preg_replace("/ +/", " ", $clear);
    $clear = html_entity_decode($clear);
    $clear = urldecode($clear);
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
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$result = curl_exec($ch);
curl_close($ch);

$regex = '#\<table class="tmptabela"\>(.+?)\<\/table\>#s';
preg_match($regex, $result, $matchsTable);
$matchTable = $matchsTable[0];

if (count($matchsTable) > 0 && !curl_error($ch) && strlen($CEP) === 8) {

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
        "MT" => "Mato Grosso do Norte",
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

    $preNumber = explode("-", $Logradouro)[1];
    if (clean(substr($preNumber, 1, 3)) === "até") {
        $divisor = explode("/", clean(substr($preNumber, 4)));
        $numeros = array(array("inicio" => intval($divisor[0]), "fim" => intval($divisor[1])));
    } else if (clean(substr($preNumber, 1, 3)) === "de") {
        if (!strpos($preNumber, "ao fim")) {
            $part = str_replace(" a ", ",", substr(clean($preNumber), 4));
            $arr = array();
            foreach (explode(",", $part) as $range) {
                $divisor = explode("/", $range);
                array_push($arr, array(
                    "inicio" => intval($divisor[0]),
                    "fim" => intval($divisor[1])
                ));
            }
            $numeros = $arr;
        } else {
            $preAoFim = clean(str_replace(" de ", "", str_replace(" ao fim", "", $preNumber)));
            $numeros = array(array("inicio" => intval(explode("/", $preAoFim)[0]), "fim" => intval(explode("/", $preAoFim)[1])));
        }
    } else {
        $numeros = false;
    }

    $preSigla = clean(explode("/", $Federal)[1]);
    $preCidade = clean(explode("/", $Federal)[0]);
    if (strpos(strtolower($preSigla), "distrito") > 0) {
        $quaseSigla = str_replace(" - distrito", "", strtolower($preSigla));
        $sigla = strtoupper($quaseSigla);
        preg_match('#\((.*?)\)#', $preCidade, $matchCidade);
        $cidade = $matchCidade[1];
        $distrito = str_replace(" (" . $cidade . ")", "", $preCidade);
        $Bairro = clean($Bairro);
    } else {
        $sigla = $preSigla;
        $distrito = false;
        $cidade = $preCidade;
        $Bairro = clean($Bairro);
    }

    $preUnidade = explode(",", $Logradouro);
    if (count($preUnidade) > 1) {
        $logradouro = clean($preUnidade[0]);
        $preUnidade = $preUnidade[1];
        $quaseUnidade = ((strpos($preUnidade, "AC")) ? "Agência Credênciada" : ((strpos($preUnidade, "AGC")) ? "Agência Comunitária" : "Desconhecido"));
        $quaseUnidade_ = ((strpos($preUnidade, "AC")) ? "AC" : ((strpos($preUnidade, "AGC")) ? "AGC" : "N"));
        $quaseUnidade__ = ((strpos($preUnidade, "AC")) ? 1 : ((strpos($preUnidade, "AGC")) ? 2 : 0));
        $unidade = array("nome" => $quaseUnidade, "sigla" => $quaseUnidade_, "codigo" => $quaseUnidade__);
    } else {
        $unidade = false;
        $logradouro = clean(explode("-", $Logradouro)[0]);
    }

    if (strpos($logradouro, "Rural") && strpos($CEP, "899")) {
        $logradouro = false;
        $Bairro = false;
        $rural = true;
    } else {
        $rural = false;
    }

    $CEP = intval($CEP);

    $Bairro = ($Bairro === "") ? false : $Bairro;
    $logradouro = ($logradouro === "") ? false : $logradouro;

    $jsonData = array(
        "sucesso" => true,
        "cep" => $CEP,
        "numeros" => $numeros,
        "unidade" => $unidade,
        "logradouro" => $logradouro,
        "bairro" => $Bairro,
        "rural" => $rural,
        "cidade" => $cidade,
        "distrito" => $distrito,
        "estado" => array(
            "nome" => $siglas[$sigla],
            "sigla" => $sigla
        ),
        "sede" => $sedes[strval(intval(substr($CEP, 0, 1)))],
        "estrutura" => array(
            "regiao" => substr($CEP, 0, 1),
            "subregiao" => substr($CEP, 1, 1),
            "setor" => substr($CEP, 2, 1),
            "subsetor" => substr($CEP, 3, 1),
            "divisao" => substr($CEP, 4, 1),
            "distribuicao" => substr($CEP, 5, 3)
        )
    );
} else {
    $jsonData = array("sucesso" => false, "resposta" => "");
    if (!curl_error($ch)) {
        if (count($matchsTable) <= 0) {
            $jsonData["resposta"] = "Esse CEP não existe no sistema";
        } else if (strlen($CEP) < 8 || strlen($CEP) > 8) {
            $jsonData["resposta"] = "O CEP é constituido por 8 numeros";
        } else {
            $jsonData["resposta"] = "Ocorreu um erro inesperado";
        }
    } else {
        if (curl_error($ch) === CURLE_OPERATION_TIMEDOUT) {
            $jsonData["resposta"] = "Tempo limite de resposta foi atingido";
        } else if (curl_error($ch)) {
            $jsonData["resposta"] = "O curl retornou código " . curl_error($ch) . " de erro";
        } else {
            $jsonData["resposta"] = "Falha ao tentar conectar aos Correios";
        }
    }
}

$return = json_encode($jsonData);
header("Content-Type: application/json; charset=utf-8");
echo $return;
