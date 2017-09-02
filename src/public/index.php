<?php

// https://www.slimframework.com/docs/tutorial/first-app.html


use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;

require '../../vendor/autoload.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$config['db']['host'] = "localhost";
$config['db']['user'] = "user";
$config['db']['pass'] = "password";
$config['db']['dbname'] = "exampleapp";

$app = new \Slim\App(["settings" => $config]);

$container = $app->getContainer();

$container['logger'] = function ($c) {
    // $this->logger->addInfo("Something interesting happened");
    $logger = new \Monolog\Logger();
    $fileHandler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($fileHandler);

    return $logger;
};

$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO(
        "mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
        $db['user'], $db['pass']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    return $pdo;
};

$app->get(
    '/',
    function (Request $request, Response $response) {
        echo "hello World2";
    }
);

$app->get(
    '/hello/{name}',
    function (Request $request, Response $response) {
        $name = $request->getAttribute('name');
        $response->getBody()->write("Salut ma belle $name");

        return $response;
    }
);


$app->get(
    '/ticket/{id}',
    function (Request $request, Response $response, $args) {
        $ticketID = (int)$args['id'];
        $mapper = new TicketMapper($this->db);
        $ticket = $mapper->getTicketById($ticketID);

        $response->getBody()->write(var_export($ticket, true));

        return $response;
    }
);

$app->post(
    '/ticket/new',
    function (Request $request, Response $response) {
        $data = $request->getParsedBody();
        $ticketData = [];
        $ticketData['title'] = filter_var($data['title'], FILTER_SANITIZE_STRING);
        $ticketData['description'] = filter_var($data['description'], FILTER_SANITIZE_STRING);

        return $response;
    }
);


$app->run();
