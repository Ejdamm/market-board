<?php


namespace Startplats;

use PDO;

class Listings
{
    private $db;
    private $WHERE_filter;
    private $params;
    private $sorting_column;
    private $sorting_order;
    private $limit;
    private $offset;

    public function __construct($db)
    {
        $this->db = $db;
        $this->setWHEREFilter();
        $this->params = [];
        $this->sorting_column = "created_at";
        $this->sorting_order = "DESC";
        $this->limit = 20;
        $this->offset = 0;
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

    public function setSortingOrder(string $sorting_order)
    {
        if ($sorting_order == "ASC" || $sorting_order == "DESC") {
            $this->sorting_order = $sorting_order;
        }
    }

    public function setSortingColumn(string $sorting_column)
    {
        if ($sorting_column == "created_at" || $sorting_column == "unit_price") {
            $this->sorting_column = $sorting_column;
        }
    }

    public function setLimit(int $limit)
    {
        $this->limit = $limit;
    }

    public function setOffset(int $offset)
    {
        $this->offset = $offset;
    }

    public function getSingleListing(int $id)
    {
        $query = "SELECT subcategory_name, category_name, email, unit_price, quantity, created_at, description FROM listings
            INNER JOIN subcategories ON listings.subcategory_id = subcategories.id
            INNER JOIN categories ON subcategories.category_id = categories.id
            WHERE listings.id = ?;";
        $statement = $this->prepareAndExecute($query, [$id]);
        $result = $statement->fetch();
        return $result;
    }

    public function getMultipleListings()
    {
        $params = array_merge($this->params, [$this->limit, $this->offset]);

        $query = "SELECT listings.id, subcategory_name, category_name, email, unit_price, quantity, created_at
            FROM listings 
            INNER JOIN subcategories ON listings.subcategory_id = subcategories.id
            INNER JOIN categories ON subcategories.category_id = categories.id
            $this->WHERE_filter
            ORDER BY $this->sorting_column $this->sorting_order
            LIMIT ? OFFSET ?;";
        $statement = $this->prepareAndExecute($query, $params);
        $result = $statement->fetchAll();
        return $result;
    }

    public function insertListing($params)
    {
        $query = "INSERT INTO listings(email, subcategory_id, unit_price, quantity, removal_code, description) VALUES(?,?,?,?,?,?);";
        $params = array_values([
            $params['email'],
            $params['subcategory_id'],
            $params['unit_price'],
            $params['quantity'],
            $params['removal_code'],
            $params['description']
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

    public function getNrOfListings()
    {
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
