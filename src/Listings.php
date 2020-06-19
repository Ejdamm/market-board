<?php


namespace Startplats;

use PDO;

class Listings
{
    private $db;
    private $WHERE_filter;
    private $placeholders;
    private $params;

    public function __construct($db)
    {
        $this->db = $db;
        $this->WHERE_filter = $this->setWHEREFilter();
        $this->params = [];
    }

    public function setWHEREFilter(int $category_id = 0, int $subcategory_id = 0)
    {
        $this->WHERE_filter = "WHERE 1=1";
        if ($category_id > 0) {
            $this->WHERE_filter .= " AND categories.id = ?";
            $this->params[] = $category_id;
        }
        if ($subcategory_id > 0) {
            $this->WHERE_filter .= " AND subcategories.id = ?";
            $this->params[] = $subcategory_id;
        }
    }

    public function getSingleListing(int $id)
    {
        $query = "SELECT subcategory_name, category_name, email, unit_price, quantity, created_at FROM listings
            INNER JOIN subcategories ON listings.subcategory_id = subcategories.id
            INNER JOIN categories ON subcategories.category_id = categories.id
            WHERE listings.id = ?;";
        $statement = $this->prepareAndExecute($query, [$id]);
        $result = $statement->fetch();
        return $result;
    }

    public function getMultipleListings(int $limit, int $offset, string $sortingColumn = "created_at", string $sortingOrder = "DESC")
    {
        #sortingColumn and sortingOrder need manual sanitizing because you can't prepare column names and ASC/DESC
        switch ($sortingColumn) {
            case 'unit_price':
                $sort = 'unit_price';
                break;
            case 'created_at':
            default:
                $sort = 'created_at';
                break;
        }

        $order = $sortingOrder == "ASC" ? $sortingOrder : "DESC";

        $params = array_merge($this->params, [$limit, $offset]);

        $query = "SELECT listings.id, subcategory_name, category_name, email, unit_price, quantity, created_at
            FROM listings 
            INNER JOIN subcategories ON listings.subcategory_id = subcategories.id
            INNER JOIN categories ON subcategories.category_id = categories.id
            $this->WHERE_filter
            ORDER BY $sort $order LIMIT ? OFFSET ?;";
        $statement = $this->prepareAndExecute($query, $params);
        $result = $statement->fetchAll();
        return $result;
    }

    public function insertListing($params)
    {
        $query = "INSERT INTO listings(email, subcategory_id, unit_price, quantity, removal_code) VALUES(?,?,?,?,?);";
        $params = array_values([
            $params['email'],
            $params['subcategory_id'],
            $params['unit_price'],
            $params['quantity'],
            $params['removal_code']
        ]);
        $this->prepareAndExecute($query, $params);
        $inserted_id = $this->db->lastInsertId();
        return intval($inserted_id);
    }

    public function removeListing($listing_id, $removal_code)
    {
        $query = "DELETE FROM listings WHERE id = ? AND removal_code = ?;";
        $params = array_values([
            $listing_id,
            $removal_code
        ]);
        $statement = $this->prepareAndExecute($query, $params);
        $affected_rows = $statement->rowCount();
        return intval($affected_rows);
    }

    public function getNrOfListings($filter = null)
    {
        $category_id = isset($filter['category']) ? $filter['category'] : 0;
        $subcategory_id = isset($filter['category']) ? $filter['category'] : 0;
        $this->setWHEREFilter($category_id, $subcategory_id);

        $query = "SELECT COUNT(*) AS count
            FROM listings
            INNER JOIN subcategories ON listings.subcategory_id = subcategories.id
            INNER JOIN categories ON subcategories.category_id = categories.id
            $this->WHERE_filter;";
        $statement = $this->prepareAndExecute($query, $this->params);
        $count = $statement->fetch();
        return intval($count['count']);
    }

    private function prepareAndExecute($query, $params=[])
    {
        $statement = $this->db->prepare($query);
        foreach ($params as $key => $param) {
            if (is_int($param)) {
                $statement->bindValue($key+1, $param, PDO::PARAM_INT);
            } else {
                $statement->bindValue($key+1, $param);
            }
        }
        $statement->execute();
        return $statement;
    }
}
