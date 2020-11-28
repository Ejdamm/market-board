<?php


namespace MarketBoard;

class Categories
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getMainCategories()
    {
        $query = "SELECT id, category_name FROM categories;";
        $statement = $this->db->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll();

        return $result;
    }

    /**
     * Returns all subcategories including the category name
     */
    public function getSubcategories()
    {
        $query = "SELECT subcategories.category_id, subcategories.id, subcategories.subcategory_name, categories.category_name
            FROM subcategories
            LEFT JOIN categories
            ON subcategories.category_id = categories.id;";

        $statement = $this->db->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll();

        return $result;
    }
}
