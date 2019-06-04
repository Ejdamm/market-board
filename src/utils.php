<?php

function get_categories($db)
{
    $query = "SELECT id, category_name FROM categories;";
    $statement = $db->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll();

    return $result;
}

function get_subcategories($db)
{
    $query = "SELECT id, subcategory_name FROM subcategories;";
    $statement = $db->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll();

    return $result;
}
