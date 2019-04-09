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
    $params = $request->getParams();
    $this->db->execute($query, $params);
    $insertedId = $this->db->lastInsertId();
    return $this->view->render($response, 'new_listing.html.twig', [
        'insertedId' => $insertedId
    ]);
});

$app->get('/listings/', function (Request $request, Response $response) {
    $query = "SELECT * FROM listings;";
    $params = $request->getParams();
    $result = $this->db->executeFetchAll($query, $params);
    return $this->view->render($response, 'all_listings.html.twig', [
        'listings' => $result
    ]);
});

$app->get('/listings/{id}', function (Request $request, Response $response, $args = []) {
    $query = "SELECT * FROM listings WHERE id = ?;";
    $params = [$args['id']];
    $this->db->execute($query, $params);
    $result = $this->db->fetchOne();
    return $this->view->render($response, 'single_listing.html.twig', [
        'listing' => $result
    ]);
});
