<?php

use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/{name}', function (Request $request, Response $response, $args = []) {
    $name = $args['name'];
    $this->logger->addInfo('Hello ' . $name);
    return $this->view->render($response, 'hello.html.twig', [
        'name' => $name
    ]);
});

$app->get('/listings/new', function (Request $request, Response $response) {
    return $this->view->render($response, 'new_listing.html.twig');
});

$app->post('/listings/new', function (Request $request, Response $response) {
    $query = "INSERT INTO listings(email, category, subcategory, price, quantity) VALUES(?,?,?,?,?);";
    $statement = $this->db->prepare($query);
    $statement->execute($request->getParams());
    $insertedId = $this->db->lastInsertId();
    return $this->view->render($response, 'new_listing.html.twig', [
        'insertedId' => $insertedId
    ]);
});

$app->get('/listings/', function (Request $request, Response $response) {
    $query = "SELECT * FROM listings;";
    $statement = $this->db->prepare($query);
    $statement->execute($request->getParams());
    $result = $statement->fetchAll();
    return $this->view->render($response, 'all_listings.html.twig', [
        'listings' => $result
    ]);
});

$app->get('/listings/{id}', function (Request $request, Response $response, $args = []) {
    $query = "SELECT * FROM listings WHERE id = ?;";
    $statement = $this->db->prepare($query);
    $statement->execute([$args['id']]);
    $result = $statement->fetch();
    return $this->view->render($response, 'single_listing.html.twig', [
        'listing' => $result
    ]);
});
