<?php


namespace MarketBoard;

use PDO;

class Listings
{
    private $db;
    private $whereFilter;
    private $params;
    private $sortingColumn;
    private $sortingOrder;
    private $limit;
    private $offset;

    public function __construct($db)
    {
        $this->db = $db;
        $this->setWhereFilter();
        $this->params = [];
        $this->sortingColumn = "created_at";
        $this->sortingOrder = "DESC";
        $this->limit = 20;
        $this->offset = 0;
    }

    public function setWhereFilter(int $category_id = 0, int $subcategoryId = 0)
    {
        $this->whereFilter = "WHERE 1=1";
        if ($category_id > 0) {
            $this->whereFilter .= " AND categories.id = ?";
            $this->params[] = $category_id;
        }
        if ($subcategoryId > 0) {
            $this->whereFilter .= " AND subcategories.id = ?";
            $this->params[] = $subcategoryId;
        }
    }

    public function setSortingOrder($sortingOrder)
    {
        if ($sortingOrder == "ASC" || $sortingOrder == "DESC") {
            $this->sortingOrder = $sortingOrder;
        }
    }

    public function setSortingColumn($sortingColumn)
    {
        if ($sortingColumn == "created_at" || $sortingColumn == "unit_price") {
            $this->sortingColumn = $sortingColumn;
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
        $query = "SELECT subcategory_name, category_name, email, unit_price, quantity, created_at, description, title FROM listings
            INNER JOIN subcategories ON listings.subcategory_id = subcategories.id
            INNER JOIN categories ON subcategories.category_id = categories.id
            WHERE listings.id = ?;";
        $statement = $this->prepareAndExecute($query, [$id]);
        $result = $statement->fetch();
        if ($result) {
            return $result;
        } else {
            return [];
        }
    }

    public function getMultipleListings()
    {
        $params = array_merge($this->params, [$this->limit, $this->offset]);

        $query = "SELECT listings.id, subcategory_name, category_name, email, unit_price, quantity, created_at, title
            FROM listings 
            INNER JOIN subcategories ON listings.subcategory_id = subcategories.id
            INNER JOIN categories ON subcategories.category_id = categories.id
            $this->whereFilter
            ORDER BY $this->sortingColumn $this->sortingOrder
            LIMIT ? OFFSET ?;";
        $statement = $this->prepareAndExecute($query, $params);
        $result = $statement->fetchAll();
        if ($result) {
            return $result;
        } else {
            return [];
        }
    }

    public function insertListing($params, $removalCode)
    {
        $query = "INSERT INTO listings(email, subcategory_id, unit_price, quantity, removal_code, description, title) VALUES(?,?,?,?,?,?,?);";
        $params = array_values([
            $params['email'],
            $params['subcategory_id'],
            $params['unit_price'],
            $params['quantity'],
            $removalCode,
            $params['description'],
            $params['title'],
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
            $this->whereFilter;";
        $statement = $this->prepareAndExecute($query, $this->params);

        $count = $statement->fetch();
        if ($count) {
            return intval($count['count']);
        } else {
            return 0;
        }
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
