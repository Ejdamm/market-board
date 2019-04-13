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
    try {
        return $this->view->render($response, 'new_listing.html.twig');
    } catch (Exception $e) {
        //TODO: redirect?
        $this->logger->addError("/listings/new GET throw exception: " . $e);
    }
});

$app->post('/listings/new', function (Request $request, Response $response) {
    try {
        $this->logger->addInfo("Recieved post params:" . print_r($request->getParams(), true));

        $query = "INSERT INTO listings(email, category, subcategory, price, quantity) VALUES(?,?,?,?,?);";
        $params = $request->getParams();
        $this->db->execute($query, $params);

        $this->logger->addInfo("Parameters inserted");
        return $this->view->render($response, 'new_listing.html.twig', [
            'message' => "New listing inserted."
        ]);
    } catch (Exception $e) {
        $this->logger->addError("/listings/new POST throw exception: " . $e);
        //TODO: return something
    }
});

$app->get('/listings/', function (Request $request, Response $response) {
    try {
        $query = "SELECT * FROM listings;";
        $params = $request->getParams();
        $result = $this->db->executeFetchAll($query, $params);
        return $this->view->render($response, 'all_listings.html.twig', [
            'listings' => $result
        ]);
    } catch (Exception $e) {
        $this->logger->addError("/listings/ GET throw exception: " . $e);
        //TODO: return something
    }
});

$app->get('/listings/{id}', function (Request $request, Response $response, $args = []) {
    try {
        $query = "SELECT * FROM listings WHERE id = ?;";
        $params = [$args['id']];
        $this->db->execute($query, $params);
        $result = $this->db->fetchOne();
        return $this->view->render($response, 'single_listing.html.twig', [
            'listing' => $result
        ]);
    } catch (Exception $e) {
        //TODO: send requested id to logging. But what if it is $args['id'] that caused the exception?
        $this->logger->addError("/listings/{id} GET throw exception: " . $e);
        //TODO: return something
    }
});
