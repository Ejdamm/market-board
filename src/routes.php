<?php
use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/{name}', function (Request $req, Response $res, $args = []) {
    $name = $args['name'];
    $this->logger->addInfo('Hello ' . $name);
    return $this->view->render($res, 'hello.html.twig', [
        'name' => $name
    ]);
});
