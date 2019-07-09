<?php

use Slim\Http\Request;
use Slim\Http\Response;

include_once __DIR__ . '/utils.php';

$app->get('/listings/new', function (Request $request, Response $response) {
    try {
        $categories = get_categories($this->db);
        $subcategories = get_subcategories($this->db);
        return $this->view->render($response, 'new_listing.html.twig', [
            'categories' => $categories,
            'subcategories' => $subcategories
        ]);
    } catch (Exception $e) {
        //TODO: redirect?
        $this->logger->addError("/listings/new GET throw exception: " . $e);
    }
});

$app->post('/listings/new', function (Request $request, Response $response) {
    try {
        $this->logger->addInfo("Received post params:" . print_r($request->getParams(), true));

        $query = "INSERT INTO listings(email, subcategory_id, price, quantity) VALUES(?,?,?,?);";
        $statement = $this->db->prepare($query);
        $params = $request->getParams();
        $statement->execute(array_values([
            $params['email'],
            $params['subcategory_id'],
            $params['price'],
            $params['quantity']
        ]));
        $this->logger->addInfo("Parameters inserted");

        $insertedId = $this->db->lastInsertId();
        $categories = get_categories($this->db);
        $subcategories = get_subcategories($this->db);
        return $this->view->render($response, 'new_listing.html.twig', [
            'insertedId' => $insertedId,
            'categories' => $categories,
            'subcategories' => $subcategories
        ]);
    } catch (Exception $e) {
        $this->logger->addError("/listings/new POST throw exception: " . $e);
        //TODO: return something
    }
});

$app->get('/[listings/]', function (Request $request, Response $response) {
    try {
        $query = "SELECT listings.id, price, quantity, created_at, subcategory_name
            FROM listings INNER JOIN subcategories ON listings.subcategory_id = subcategories.id
            ORDER BY created_at DESC;";
        $statement = $this->db->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll();
        $this->logger->addDebug(print_r($result, true));
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
        $query = "SELECT subcategory_name, category_name, email, price, quantity, created_at FROM listings
            INNER JOIN subcategories ON listings.subcategory_id = subcategories.id
            INNER JOIN categories ON subcategories.category_id = categories.id
            WHERE listings.id = ?;";
        $statement = $this->db->prepare($query);
        $statement->execute([$args['id']]);
        $result = $statement->fetch();
        return $this->view->render($response, 'single_listing.html.twig', [
            'listing' => $result
        ]);
    } catch (Exception $e) {
        //TODO: addWarning if id does not exist
        $this->logger->addError("/listings/" . $args['id'] . " GET throw exception: " . $e);
        //TODO: return something
    }
});
