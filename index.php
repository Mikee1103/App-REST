<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$metodo = $_SERVER["REQUEST_METHOD"];
$ct = $_SERVER["CONTENT_TYPE"] ?? "application/json";
$type = explode("/", $ct);

$retct = $_SERVER["HTTP_ACCEPT"] ?? "application/json";
$ret = explode("/", $retct);

// Simulazione database
$database = [
    1 => ["id" => 1, "nome" => "Mario", "valore" => 100],
    2 => ["id" => 2, "nome" => "Luca", "valore" => 200]
];

// Funzione risposta
function risposta($data, $ret) {
    if ($ret[1] == "xml") {
        $xml = new SimpleXMLElement('<root/>');
        array_walk_recursive($data, function($value, $key) use ($xml) {
            $xml->addChild($key, $value);
        });
        echo $xml->asXML();
    } else {
        echo json_encode($data);
    }
}

// GET
if ($metodo == "GET") {

    if (isset($_GET["id"])) {
        $id = $_GET["id"];
        if (isset($database[$id])) {
            risposta($database[$id], $ret);
        } else {
            http_response_code(404);
        }
    } else {
        risposta($database, $ret);
    }
}

// POST 
if ($metodo == "POST") {

    $body = file_get_contents("php://input");

    if ($type[1] == "json") {
        $data = json_decode($body, true);
    } else if ($type[1] == "xml") {
        $xml = simplexml_load_string($body);
        $data = json_decode(json_encode($xml), true);
    }

    $id = count($database) + 1;
    $data["id"] = $id;

    risposta($data, $ret);
}

// PUT 
if ($metodo == "PUT") {

    parse_str($_SERVER['QUERY_STRING'], $query);
    $id = $query["id"] ?? null;

    if (!$id) {
        http_response_code(400);
        exit;
    }

    $body = file_get_contents("php://input");

    if ($type[1] == "json") {
        $data = json_decode($body, true);
    } else if ($type[1] == "xml") {
        $xml = simplexml_load_string($body);
        $data = json_decode(json_encode($xml), true);
    }

    $data["id"] = $id;

    risposta(["messaggio" => "Record aggiornato", "record" => $data], $ret);
}

// DELETE
if ($metodo == "DELETE") {

    parse_str($_SERVER['QUERY_STRING'], $query);
    $id = $query["id"] ?? null;

    if (!$id) {
        http_response_code(400);
        exit;
    }

    risposta(["messaggio" => "Record con id $id eliminato"], $ret);
}

?>