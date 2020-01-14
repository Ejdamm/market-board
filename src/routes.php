<?php

use Slim\Http\Request;
use Slim\Http\Response;
use Startplats\Listings;
use Startplats\EmailNewListing;
use Startplats\Utils;

$app->get('/listings/new', function (Request $request, Response $response) {
    try {
        $categories = Utils::get_categories($this->db);
        $subcategories = Utils::get_subcategories($this->db);
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

        $removal_code = Utils::generate_removal_code();
        $params = $request->getParams();
        $params['removal_code'] = $removal_code;
        $listings = new Listings($this->db);
        $inserted_id = $listings->insertListing($params);
        $this->logger->addInfo("Parameters inserted:", $params);

        $categories = Utils::get_categories($this->db);
        $subcategories = Utils::get_subcategories($this->db);

        $email_variables = new stdClass;
        $email_variables->insertedId = $inserted_id;
        $email_variables->removal_code = $removal_code;
        // E-mail function is excluded if run in Travis since it's a closed environment and tests will fail
        if (getenv('TRAVIS') != 'true') {
            $this->mailer->setTo($params['email'])->sendMessage(new EmailNewListing($email_variables));
        }

        return $this->view->render($response, 'new_listing.html.twig', [
            'inserted_id' => $inserted_id,
            'categories' => $categories,
            'subcategories' => $subcategories,
            'removal_code' => $removal_code
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

        $all_listings = $listings->getMultipleListings($limit, $offset);

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

$app->post('/listings/{id}', function (Request $request, Response $response, $args = []) {
    try {
        $listings = new Listings($this->db);
        $affected_rows = $listings->removeListing($args['id'], $request->getParams()['removal_code']);
        $this->logger->addInfo("Listing removed: " . $args['id']);

        return $this->view->render($response, 'remove_listing.html.twig', [
            'affected_rows' => $affected_rows,
            'listing_id' => $args['id']
        ]);
    } catch (Exception $e) {
        //TODO: addWarning if id does not exist
        $this->logger->addError("/listings/" . $args['id'] . " POST throw exception: " . $e);
        //TODO: return something
    }
});
