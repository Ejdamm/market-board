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

        // Filter
        $filter = [];
        $GET_category_filter = $request->getParam('category_filter', null);
        if ($GET_category_filter != null && is_numeric($GET_category_filter) && intval($GET_category_filter) >= 0) {
            $this->session->set('category_filter', $GET_category_filter);
        }
        $filter['category'] = $this->session->get('category_filter', 0);
        $GET_subcategory_filter = $request->getParam('subcategory_filter', null);
        if ($GET_subcategory_filter != null && is_numeric($GET_subcategory_filter) && intval($GET_subcategory_filter) >= 0) {
            $this->session->set('subcategory_filter', $GET_subcategory_filter);
        }
        $filter['subcategory'] = $this->session->get('subcategory_filter', 0);
        $listings->setWHEREFilter($filter['category'], $filter['subcategory']);

        // Paging
        $count = $listings->getNrOfListings();
        $limit = 20; //TODO should be configurable
        $GET_page = $request->getParam('page', null);
        if ($GET_page && is_numeric($GET_page) && intval($GET_page) > 0) {
            $this->session->set('paging', $GET_page);
        }
        $paging = Utils::get_paging($this->session->get('paging', 1), $count, $limit);
        $listings->setLimit($limit);
        $listings->setOffset($paging['offset']);

        //Sorting
        $GET_sorting_column = $request->getParam('sorting_column', null);
        $GET_order = $request->getParam('order', null);
        if ($GET_sorting_column != null || $GET_order != null) {
            $this->session->set('sorting_column', $GET_sorting_column);
            $this->session->set('order', $GET_order);
        }
        $sorting = Utils::get_sorting($this->session->get('sorting_column', null), $this->session->get('order', null));
        $listings->setSortingColumn($sorting['column']);
        $listings->setSortingOrder($sorting['current_order']);

        $all_listings = $listings->getMultipleListings();
        $categories = Utils::get_categories($this->db);
        $subcategories = Utils::get_subcategories($this->db);

        return $this->view->render($response, 'all_listings.html.twig', [
            'listings' => $all_listings,
            'pagination' => [
                'needed' => $count > $limit,
                'count' => $count,
                'page' => $paging['page'],
                'last_page' => $paging['last_page'],
                'limit' => $limit,
                'window_start' => $paging['window_start'],
                'window_stop' => $paging['window_stop']
            ],
            'sorting' => $sorting,
            'categories' => $categories,
            'subcategories' => $subcategories,
            'filter' => $filter,
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
