<?php

use Slim\Http\Request;
use Slim\Http\Response;
use Startplats\Listings;

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

        $listings = new Listings($this->db);
        $insertedId = $listings->insertListing($request->getParams());
        $this->logger->addInfo("Parameters inserted");

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
        $listings = new Listings($this->db);
        $count = $listings->getNrOfListings();

        $page = ($request->getParam('page', 0) > 0) ? $request->getParam('page') : 1;
        $limit = 20; //TODO should be configurable
        $count = isset($count['count']) != null ? $count['count'] : 0;
        $offset = ($page - 1) * $limit;
        $last_page = (ceil($count / $limit) == 0 ? 1 : ceil($count / $limit));
        $window_start = ($page - 2) > 2 ? $page - 2 : 1;
        $window_stop = ($window_start + 4) < $last_page ? ($window_start + 4) : $last_page;

        $all_listings = $listings->getAllListings($limit, $offset);

        return $this->view->render($response, 'all_listings.html.twig', [
            'listings' => $all_listings,
            'pagination' => [
                'needed' => $count > $limit,
                'count' => $count,
                'page' => $page,
                'last_page' => $last_page,
                'limit' => $limit,
                'window_start' => $window_start,
                'window_stop' => $window_stop
            ],
        ]);
    } catch (Exception $e) {
        $this->logger->addError("/listings/ GET throw exception: " . $e);
        //TODO: return something
    }
});

$app->get('/listings/{id}', function (Request $request, Response $response, $args = []) {
    try {
        $listings = new Listings($this->db);
        $single_listing = $listings->getSingleListing($args['id']);
        return $this->view->render($response, 'single_listing.html.twig', [
            'listing' => $single_listing
        ]);
    } catch (Exception $e) {
        //TODO: addWarning if id does not exist
        $this->logger->addError("/listings/" . $args['id'] . " GET throw exception: " . $e);
        //TODO: return something
    }
});
