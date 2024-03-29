<?php


namespace MarketBoard\Controllers;

use MarketBoard\Categories;
use MarketBoard\Listings;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class ListingsController extends BaseController
{
    protected $listings;
    private $request;
    protected $type;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->listings = new Listings($this->db);
        $this->type = 'listings';
    }

    public function get($request, $response, $args): ResponseInterface
    {
        $this->request = $request;

        // These must be called before getMultipleListings()
        $filter = $this->getFilter();
        $sorting = $this->getSorting();
        $paging = $this->getPaging();

        $categories = new Categories($this->db);
        return $this->render($response, 'listings.html.twig', [
            'listings' => $this->listings->getMultipleListings(),
            'pagination' => $paging,
            'sorting' => $sorting,
            'categories' => $categories->getMainCategories(),
            'subcategories' => $categories->getSubcategories(),
            'filter' => $filter,
            'type' => $this->type,
        ]);
    }

    public function post($request, $response, $args): ResponseInterface
    {
        $this->logger->addWarning(get_class($this) . " POST not implemented");
        return $this->render501($response);
    }

    private function getFilter()
    {
        $categoryFilter = $this->setAndGetIntSession("category_filter", 0, 0);
        $subcategoryFilter = $this->setAndGetIntSession("subcategory_filter", 0, 0);

        if ($categoryFilter > 0) {
            $this->listings->addWhereFilter($this->listings->CATEGORIES_ID_FIELD, $categoryFilter);
        }
        if ($subcategoryFilter > 0) {
            $this->listings->addWhereFilter($this->listings->SUBCATEGORIES_ID_FIELD, $subcategoryFilter);
        }

        return [
            'category' => $categoryFilter,
            'subcategory' => $subcategoryFilter,
        ];
    }

    private function getPaging()
    {
        $nrOfListings = $this->listings->getNrOfListings();
        $listingLimit = $this->settings['listings_limit'];
        $currentPage = $this->setAndGetIntSession("paging", 1, 1);
        $lastPage = (ceil($nrOfListings / $listingLimit) == 0 ? 1 : ceil($nrOfListings / $listingLimit));
        $currentPage = $currentPage <= $lastPage ? $currentPage : $lastPage;
        $rangeStart = ($currentPage - 2) > 2 ? $currentPage - 2 : 1;
        $rangeStop = ($rangeStart + 4) < $lastPage ? ($rangeStart + 4) : $lastPage;

        $this->listings->setLimit($listingLimit);
        $this->listings->setOffset(($currentPage - 1) * $listingLimit);

        return [
            'is_needed' => $nrOfListings > $listingLimit,
            'current_page' => $currentPage,
            'last_page' => $lastPage,
            'range_start' => $rangeStart,
            'range_stop' => $rangeStop,
        ];
    }

    private function getSorting()
    {
        $sortingOrder = $this->setAndGetStringSession("sorting_order", null);
        $this->listings->setSortingOrder($sortingOrder);
        $sortingColumn = $this->setAndGetStringSession("sorting_column", null);
        $this->listings->setSortingColumn($sortingColumn);

        $dateCssClass = "";
        $priceCssClass = "";
        switch ($sortingColumn) {
            case "unit_price":
                $priceCssClass = $this->getSortingCssClass($sortingOrder);
                break;
            case "created_at":
                $dateCssClass = $this->getSortingCssClass($sortingOrder);
                break;
            default:
        }

        return [
            'price_class' => $priceCssClass,
            'date_class' => $dateCssClass,
            'toggle_order' => $sortingOrder == "ASC" ? "DESC" : "ASC",
        ];
    }

    private function setAndGetIntSession($paramName, $default, $minValue = null)
    {
        $paramValue = $this->request->getParam($paramName, null);
        $isBigEnough = true;
        if ($minValue != null) {
            $isBigEnough = intval($paramValue) >= $minValue;
        }
        $sessionName = $this->type . "_" . $paramName;
        if ($paramValue != null && is_numeric($paramValue) && $isBigEnough) {
            $this->session->set($sessionName, $paramValue);
        }
        return $this->session->get($sessionName, $default);
    }

    private function setAndGetStringSession($paramName, $default)
    {
        $paramValue = $this->request->getParam($paramName, null);
        $sessionName = $this->type . "_" . $paramName;
        if ($paramValue != null) {
            $this->session->set($sessionName, $paramValue);
        }
        return $this->session->get($sessionName, $default);
    }

    private function getSortingCssClass($sortingOrder)
    {
        switch ($sortingOrder) {
            case "DESC":
                $cssClass = "-down";
                break;
            case "ASC":
                $cssClass = "-up";
                break;
            default:
                $cssClass = "";
        }
        return $cssClass;
    }
}
