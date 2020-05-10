<?php

define("ASCENDING", "ASC");
define("DESCENDING", "DESC");

function get_categories($db)
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
function get_subcategories($db)
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

/**
 * Returns an array with sorting parameters for listing page
 * sorting_column == null means sorting isn't requested and it falls back to default sorting
 */
function get_sorting($sorting_column, $order)
{
    $orders = [
        DESCENDING => '-up',
        ASCENDING => '-down'
    ];
    $current_column = $sorting_column;
    $current_order = $order == null ? DESCENDING : $order;
    $toggle_order = $current_order == ASCENDING ? DESCENDING : ASCENDING;
    $price_class = '';
    $date_class = '';

    if ($sorting_column == 'created_at') {
        $date_class = $orders[$toggle_order];
    } elseif ($sorting_column == 'unit_price') {
        $price_class = $orders[$toggle_order];
    } else {
        $current_column = 'created_at';
        $current_order = DESCENDING;
        $toggle_order = ASCENDING;
    }

    return [
        'price_class' => $price_class,
        'date_class' => $date_class,
        'current_order' => $current_order,
        'toggle_order' => $toggle_order,
        'column' => $current_column
    ];
}

function get_paging($page, $count, $limit)
{
    $paging = [];
    $paging['last_page'] = (ceil($count / $limit) == 0 ? 1 : ceil($count / $limit));
    $paging['page'] = $page <= $paging['last_page'] ? $page : $paging['last_page'];
    $paging['offset'] = ($paging['page'] - 1) * $limit;
    $paging['window_start'] = ($paging['page'] - 2) > 2 ? $paging['page'] - 2 : 1;
    $paging['window_stop'] = ($paging['window_start'] + 4) < $paging['last_page'] ? ($paging['window_start'] + 4) : $paging['last_page'];
    return $paging;
}
