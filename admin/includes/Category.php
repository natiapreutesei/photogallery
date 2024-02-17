<?php

class Category extends Db_object {
    public $id;
    public $category_name;

    // Find all categories
    public static function find_all_categories() {
        global $database;
        $categories = [];

        $sql = "SELECT * FROM categories";
        $result = $database->query($sql);

        while($row = $result->fetch_assoc()) {
            $categories[] = $row['category_name'];
        }

        return $categories;
    }

    // Find categories by photo ID
    public static function find_categories_by_photo_id($photo_id) {
        global $database;
        $category_list = [];

        $sql = "SELECT c.category_name FROM categories c 
                INNER JOIN photo_categories pc ON c.id = pc.category_id 
                WHERE pc.photo_id = ?";

        $result = $database->query($sql, [$photo_id]);

        while($row = $result->fetch_assoc()) {
            $category_list[] = $row['category_name'];
        }

        return $category_list;
    }

    // Find a category by name
    public static function find_by_category_name($category_name) {
        global $database;
        $sql = "SELECT * FROM categories WHERE category_name = ? LIMIT 1";
        $result_array = self::find_this_query($sql, [$category_name]);
        return !empty($result_array) ? array_shift($result_array) : false;
    }

    // Save a category (insert if not exists)
    public function save() {
        global $database;
        if (!empty($this->category_name)) {
            $existing_category = static::find_by_category_name($this->category_name);
            if (!$existing_category) {
                // If the category does not exist, insert it into the database.
                $sql = "INSERT INTO categories (category_name) VALUES (?)";
                $params = [$this->category_name];
                if ($database->query($sql, $params)) {
                    // On successful insert, set the ID of the newly created category.
                    $this->id = $database->the_insert_id();
                    return true;
                } else {
                    // Handle insert failure (optional).
                    return false;
                }
            } else {
                // If the category already exists, set this object's ID to the existing category's ID.
                $this->id = $existing_category->id;
                return true; // Return true because the category effectively "exists" in the database.
            }
        }
        // If the category name is empty, return false to indicate failure.
        return false;
    }


    // Ensure a category exists and return its ID
    public static function ensure_category($category_name) {
        global $database;
        $existing_category = self::find_by_category_name($category_name);

        if (!$existing_category) {
            $new_category = new Category();
            $new_category->category_name = $category_name;
            if($new_category->save()) {
                return $database->the_insert_id();
            } else {
                // Handling error if category could not be saved, return null or false
                return null;
            }
        } else {
            return $existing_category->id;
        }
    }
}
