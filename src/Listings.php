<?php


namespace Startplats;

use PDO;

include_once __DIR__ . '/utils.php';

class Listings
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
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

    public function getMultipleListings(int $limit, int $offset, string $sortingColumn = "created_at", string $sortingOrder = DESCENDING)
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

        switch ($sortingOrder) {
            case ASCENDING:
                $order = ASCENDING;
                break;
            case DESCENDING:
            default:
                $order = DESCENDING;
                break;
        }

        $query = "SELECT listings.id, subcategory_name, category_name, email, unit_price, quantity, created_at
            FROM listings 
            INNER JOIN subcategories ON listings.subcategory_id = subcategories.id
            INNER JOIN categories ON subcategories.category_id = categories.id
            ORDER BY $sort $order LIMIT ? OFFSET ?;";
        $statement = $this->prepareAndExecute($query, [$limit, $offset]);
        $result = $statement->fetchAll();
        return $result;
    }

    public function insertListing($params)
    {
        $query = "INSERT INTO listings(email, subcategory_id, unit_price, quantity) VALUES(?,?,?,?);";
        $params = array_values([
            $params['email'],
            $params['subcategory_id'],
            $params['unit_price'],
            $params['quantity']
        ]);
        $this->prepareAndExecute($query, $params);
        $insertedId = $this->db->lastInsertId();
        return intval($insertedId);
    }

    public function getNrOfListings()
    {
        $query = "SELECT COUNT(*) AS count FROM listings;";
        $statement = $this->prepareAndExecute($query);
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
