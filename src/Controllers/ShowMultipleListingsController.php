<?php


namespace Startplats\Controllers;

use Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Startplats\Listings;
use Startplats\Utils;

class ShowMultipleListingsController extends BaseController
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    public function get($request, $response, $args): ResponseInterface
    {
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
            $limit = $this->container->get('settings')['listings_limit'];
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
                'language' => $this->language,
            ]);
        } catch (Exception $e) {
            $this->logger->addError("/listings/ GET threw exception: " . $e);
            return $this->view->render($response->withStatus(500), 'errors/error500.html.twig', [
                'language' => $this->language,
            ]);
        }
    }

    public function post($request, $response, $args): ResponseInterface
    {
        $this->logger->addError("/listings/ POST not implemented");
        return $this->view->render($response->withStatus(501), 'errors/error501.html.twig', [
            'language' => $this->language,
        ]);
    }
}
