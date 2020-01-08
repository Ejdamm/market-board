<?php

namespace Startplats;

class Utils
{
    public static function get_categories($db)
    {
        $query = "SELECT id, category_name FROM categories;";
        $statement = $db->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll();

        return $result;
    }

    /**
     * Returns all subcategories including the category name
     */
    public static function get_subcategories($db)
    {
        $query = "SELECT subcategories.category_id, subcategories.id, subcategories.subcategory_name, categories.category_name
            FROM subcategories
            LEFT JOIN categories
            ON subcategories.category_id = categories.id;";

        $statement = $db->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll();

        return $result;
    }

    public static function generate_removal_code()
    {
        return strtoupper(substr(sha1(mt_rand()), 17, 6));
    }
}
