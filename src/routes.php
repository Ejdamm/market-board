<?php

use Slim\Http\Request;
use Slim\Http\Response;
use Startplats\Listings;
use Startplats\EmailNewListing;

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
        $removal_code = 'AAAAAA'; //TODO generate random, insert in database

        $categories = get_categories($this->db);
        $subcategories = get_subcategories($this->db);

        $email_variables = new stdClass;
        $email_variables->insertedId = $insertedId;
        $email_variables->removal_code = $removal_code;
        // E-mail function is excluded if run in Travis since it's a closed environment and tests will fail
        if (getenv('TRAVIS') != 'true') {
            $this->mailer->setTo($request->getParams()['email'])->sendMessage(new EmailNewListing($email_variables));
        }

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
        $limit = 20; //TODO should be configurable
        $page = ($request->getParam('page', 0) > 0) ? $request->getParam('page') : 1;
        $paging = get_paging($page, $count, $limit);

        $GET_sorting_column = $request->getParam('sorting_column', null);
        $GET_order = $request->getParam('order', null);
        if ($GET_sorting_column != null || $GET_order != null) {
            $this->session->set('sorting_column', $GET_sorting_column);
            $this->session->set('order', $GET_order);
        }

        $SESSION_sorting_column = $this->session->get('sorting_column', null);
        $SESSION_order = $this->session->get('order', null);
        $sorting = get_sorting($SESSION_sorting_column, $SESSION_order);

        $all_listings = $listings->getMultipleListings($limit, $paging['offset'], $sorting['column'], $sorting['current_order']);


        return $this->view->render($response, 'all_listings.html.twig', [
            'listings' => $all_listings,
            'pagination' => [
                'needed' => $count > $limit,
                'count' => $count,
                'page' => $page,
                'last_page' => $paging['last_page'],
                'limit' => $limit,
                'window_start' => $paging['window_start'],
                'window_stop' => $paging['window_stop']
            ],
            'sorting' => $sorting,
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
