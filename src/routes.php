<?php
use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/{name}', function (Request $req, Response $res, $args = []) {
    $name = $args['name'];
    return $res->getBody()->write("Hello $name");
});
