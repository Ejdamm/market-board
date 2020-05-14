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

    /**
     * Returns an array with sorting parameters for listing page
     * sorting_column == null means sorting isn't requested and it falls back to default sorting
     */
    public static function get_sorting($sorting_column, $order)
    {
        $orders = [
            "DESC" => '-up',
            "ASC" => '-down'
        ];
        $current_column = $sorting_column;
        $current_order = $order == null ? "DESC" : $order;
        $toggle_order = $current_order == "ASC" ? "DESC" : "ASC";
        $price_class = '';
        $date_class = '';

        if ($sorting_column == 'created_at') {
            $date_class = $orders[$toggle_order];
        } elseif ($sorting_column == 'unit_price') {
            $price_class = $orders[$toggle_order];
        } else {
            $current_column = 'created_at';
            $current_order = "DESC";
            $toggle_order = "ASC";
        }

        return [
            'price_class' => $price_class,
            'date_class' => $date_class,
            'current_order' => $current_order,
            'toggle_order' => $toggle_order,
            'column' => $current_column
        ];
    }
}
