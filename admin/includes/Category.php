<?php

class Category extends Db_object {
    public $id;
    public $category_name;
    protected static $table_name = "categories"; // The name of the database table used by this class

    /**
     * Retrieves all categories from the database.
     *
     * This static method is part of a class that interacts with the database to perform operations related to "categories".
     * It queries the database for all records in the `categories` table and returns an array of category names. This method
     * is typically used to populate dropdown lists in the UI or for filtering content based on categories.
     *
     * Usage Scenario:
     * - Displaying all available categories in a blog or eCommerce site for navigation or filtering purposes.
     * - Dynamically generating a list of categories for a form where users can select one or more categories.
     *
     * How it Works:
     * 1. The global `$database` object is used, ensuring this method has access to the database connection established elsewhere in the application.
     * 2. An SQL query is defined to select all records from the `categories` table. This query does not specify any ordering or filtering, so it retrieves all categories.
     * 3. The query is executed using `$database->query($sql)`, which sends the SQL query to the database and returns a result set.
     * 4. The method iterates over each row in the result set with a while loop. For each row, it extracts the category name using `$row['category_name']` and adds it to an array `$categories`.
     * 5. After collecting all category names, the method returns the array to the caller.
     *
     * Example:
     * ```php
     * $allCategories = ClassName::find_all_categories();
     * foreach ($allCategories as $category) {
     *     echo "<option value='{$category}'>{$category}</option>";
     * }
     * ```
     * This example shows how the returned category names might be used to populate a `<select>` element in an HTML form.
     *
     * Note: The method assumes a table structure where there is at least a column named 'category_name'. Adjustments may be needed based on the actual database schema.
     */
    public static function find_all_categories() {
        global $database; // Access the global `$database` object that represents the connection to the database.

        $categories = []; // Initialize an empty array to hold the names of the categories fetched from the database.

        // Define an SQL query string that selects all records from the `categories` table.
        $sql = "SELECT * FROM categories";
        // Execute the SQL query against the database. The `$database->query` method sends the SQL query to the database and returns the result set.
        $result = $database->query($sql);

        // Loop through each row in the result set returned by the query.
        while($row = $result->fetch_assoc()) {
            // For each row, access the 'category_name' column and add its value to the `$categories` array.
            // The `fetch_assoc` method fetches a row from the result set as an associative array where column names are keys.
            $categories[] = $row['category_name'];
        }

        // Return the array of category names.
        // After the loop completes, `$categories` contains all the category names fetched from the database, and this array is returned to the caller.
        return $categories;
    }


    /**
     * Retrieves categories associated with a specific photo by its ID.
     *
     * This static method queries the database to find all categories linked to a given photo. It's useful
     * for displaying which categories a photo belongs to or for filtering photos based on category criteria.
     *
     * @param int $photo_id The unique identifier of the photo whose categories are being queried.
     * @return array An array of category names associated with the photo.
     *
     * How It Works:
     * 1. Establishes a global database connection using the `$database` object.
     * 2. Initializes an empty array `$category_list` to hold the category names.
     * 3. Defines an SQL query that uses an INNER JOIN between `categories` and `photo_categories` tables.
     *    The join is on `c.id` and `pc.category_id` to fetch categories linked to the photo ID provided.
     * 4. Executes the SQL query with the photo ID as a parameter to ensure safe querying practices and prevent SQL injection.
     * 5. Loops through the result set, adding each category name to the `$category_list` array.
     * 6. Returns the list of category names.
     *
     * Usage Scenario:
     * - Displaying all categories a photo is tagged with on a photo detail page.
     * - Generating a list of filters based on the categories associated with a set of photos.
     *
     * Example Usage:
     * ```php
     * $photoCategories = ClassName::find_categories_by_photo_id($photo_id);
     * foreach ($photoCategories as $category) {
     *     echo "<li>{$category}</li>";
     * }
     * ```
     * This example would list all categories associated with a photo, which can be displayed to the user.
     *
     * Note: This method assumes the existence of a `photo_categories` table that links photos to categories,
     * and a `categories` table where category details are stored. Adjustments may be required based on the actual database schema.
     */
    public static function find_categories_by_photo_id($photo_id) {
        global $database; // Accesses the global `$database` object, representing the database connection.

        $category_list = []; // Initializes an empty array to store the names of categories associated with the photo.

        // Defines an SQL query to select category names. The query uses an INNER JOIN to combine rows from 'categories' and 'photo_categories' tables.
        // The join condition is on matching category IDs, filtering the results to only include categories linked to the specified photo ID.
        $sql = "SELECT c.category_name FROM categories c 
        INNER JOIN photo_categories pc ON c.id = pc.category_id 
        WHERE pc.photo_id = ?";

        // Executes the query against the database, passing the photo ID as a parameter to prevent SQL injection.
        // The method `query` is assumed to support prepared statements, where "?" is a placeholder for the parameter.
        $result = $database->query($sql, [$photo_id]);

        // Iterates over each row in the result set.
        while($row = $result->fetch_assoc()) {
            // For each row, the category name is extracted and added to the `$category_list` array.
            // The `fetch_assoc` method fetches a row as an associative array, with column names as keys.
            $category_list[] = $row['category_name'];
        }

        // Returns the array of category names associated with the photo.
        // This list can be used to display the categories of a photo in the UI or for filtering photos by category.
        return $category_list;
    }


    /**
     * Finds a category by its name.
     *
     * This method queries the database for a category with a specific name. It is designed to be used
     * when there is a need to fetch detailed information about a category based on its name.
     *
     * @param string $category_name The name of the category to search for.
     * @return mixed An object representing the found category or false if no category is found.
     *
     * How It Works:
     * 1. Accesses the global database connection object.
     * 2. Prepares a SQL query to select all data from the `categories` table where the `category_name` matches the provided name.
     *    A placeholder (?) is used in the query to safely insert the category name and prevent SQL injection.
     * 3. Executes the prepared query with the category name as a parameter using a custom method `find_this_query`.
     *    This method is expected to handle the execution of prepared statements and return an array of results.
     * 4. Checks if the result array is not empty, indicating that a category with the given name exists.
     *    If a category is found, the first element of the array is returned as the method is designed to fetch one category only (LIMIT 1).
     *    If no category is found, returns false.
     *
     * Usage Scenario:
     * This method can be used in situations where category-specific operations need to be performed, such as displaying
     * category-related data on a webpage or validating category existence before associating it with other data.
     *
     * Example Usage:
     * ```php
     * $category = Category::find_by_category_name("Sports");
     * if ($category) {
     *     echo "Category ID: " . $category->id;
     * } else {
     *     echo "Category not found.";
     * }
     * ```
     * This example demonstrates how to search for a category named "Sports" and then proceed based on whether the category was found.
     *
     * Note: This method assumes that the `find_this_query` method properly handles SQL prepared statements and parameter binding.
     * Adjustments may be required based on the actual implementation of `find_this_query`.
     */
    public static function find_by_category_name($category_name) {
        global $database; // Accesses the global `$database` object, representing the connection to the database.

        // Prepares an SQL query that selects all columns from the 'categories' table where the 'category_name' matches the provided argument.
        // The query uses a placeholder (?) for the category name to prevent SQL injection through prepared statements.
        $sql = "SELECT * FROM categories WHERE category_name = ? LIMIT 1";

        // Executes the query by calling a custom method `find_this_query`, passing the SQL string and the category name as parameters.
        // The method is expected to execute the query and return an array of result objects.
        $result_array = self::find_this_query($sql, [$category_name]);

        // Checks if the result array is not empty, indicating that at least one category matches the given name.
        // The `array_shift` function is used to extract the first element from the array, as the query is limited to one result.
        // If no category is found, the method returns false.
        return !empty($result_array) ? array_shift($result_array) : false;
    }


    /**
     * Saves a category to the database.
     *
     * This method ensures that each category is unique by either inserting a new category
     * into the database or recognizing an already existing category. It prevents the duplication
     * of category names in the database by first checking if a category with the provided name
     * exists before attempting to insert a new one.
     *
     * How It Works:
     * 1. Checks if the `category_name` property is not empty. An empty category name is considered invalid for insertion.
     * 2. Searches for an existing category with the same name to prevent duplication.
     *    a. If an existing category is found, the method sets the object's ID to that of the existing category and returns true.
     *    b. If no existing category is found, it proceeds to insert a new category into the database.
     *       i. Prepares an SQL INSERT statement with placeholders to ensure security against SQL injection.
     *       ii. Executes the statement with the category name as a parameter.
     *       iii. If the insertion is successful, sets the object's ID to the new category's ID and returns true.
     *       iv. If the insertion fails (due to a database error, for example), the method returns false.
     * 3. If the `category_name` property is empty, the method returns false, indicating the save operation failed.
     *
     * Usage Scenario:
     * This method is used whenever a new category needs to be added to the database or when ensuring that a category
     * exists before associating it with other entities (like photos or posts). It's particularly useful in admin interfaces
     * where categories are managed or during the upload process where categories are assigned to content.
     *
     * Example Usage:
     * ```php
     * $category = new Category();
     * $category->category_name = "New Category";
     * $result = $category->save();
     * if ($result) {
     *     echo "Category saved successfully with ID: " . $category->id;
     * } else {
     *     echo "Failed to save category.";
     * }
     * ```
     * In this example, a new Category object is created, a name is assigned, and the `save` method is called to
     * attempt to save the category to the database. The result indicates whether the operation was successful.
     *
     * Note: This method relies on the global `$database` object for executing SQL queries and assumes the existence
     * of a `find_by_category_name` method for checking the existence of categories.
     */
    public function save() {
        global $database; // Utilizes the global database object for executing SQL queries.

        // First, check if the category name provided is not empty.
        // An empty category name is invalid for insertion and will cause the method to return false.
        if (!empty($this->category_name)) {
            // Attempt to find an existing category with the same name to avoid duplicates.
            $existing_category = static::find_by_category_name($this->category_name);

            // If no existing category is found, proceed with inserting the new category.
            if (!$existing_category) {
                // Prepare the SQL statement for insertion, using placeholders for security.
                $sql = "INSERT INTO categories (category_name) VALUES (?)";
                $params = [$this->category_name]; // Parameters to be bound to the SQL statement.

                // Execute the query with parameters. If successful, assign the new ID to this object.
                if ($database->query($sql, $params)) {
                    $this->id = $database->the_insert_id(); // Sets the ID property to the new category's ID.
                    return true; // Indicates a successful insertion.
                } else {
                    // If the query fails, possibly due to database constraints or errors, return false.
                    return false;
                }
            } else {
                // If a matching category already exists, simply set this object's ID to the existing one.
                // This considers the operation successful as the goal is to ensure the category's presence.
                $this->id = $existing_category->id;
                return true;
            }
        }
        // If the category name was empty, signify failure to save the category.
        return false;
    }


    /**
     * Ensures the existence of a category in the database by its name.
     *
     * This method checks if a category with a specified name already exists in the database.
     * If it does, the method returns the ID of the existing category. If the category does not exist,
     * a new one is created with the given name, and its ID is returned. This ensures that categories
     * are uniquely identified by their names and that no duplicates are created.
     *
     * How It Works:
     * 1. It attempts to find an existing category by the given name using the `find_by_category_name` method.
     *    a. If an existing category is found, the method immediately returns the ID of this category.
     * 2. If no category with the given name exists, the method proceeds to create a new category.
     *    a. A new Category object is instantiated, and its `category_name` property is set to the provided name.
     *    b. The `save` method of the Category object is called to attempt to insert the new category into the database.
     *       i. If the save operation is successful, the method returns the ID of the newly created category.
     *       ii. If the save operation fails, the method returns null, indicating that the new category could not be created.
     *
     * Usage Scenario:
     * This method is particularly useful when associating content (such as photos or posts) with categories.
     * It allows for the automatic creation of categories as needed, ensuring that content is always associated
     * with a valid category without requiring pre-existing knowledge of category IDs.
     *
     * Example Usage:
     * ```php
     * $categoryID = Category::ensure_category("Landscape");
     * if ($categoryID) {
     *     echo "Category ID: " . $categoryID;
     * } else {
     *     echo "Failed to ensure the category exists.";
     * }
     * ```
     * In this example, the method is used to ensure that a category named "Landscape" exists in the database.
     * It either returns the ID of an existing "Landscape" category or creates a new one and returns its ID.
     *
     * Note: This method assumes that the `find_by_category_name` and `save` methods are implemented in the Category class
     * and that the global `$database` object is available for executing SQL queries and obtaining the ID of newly inserted records.
     */
    public static function ensure_category($category_name) {
        global $database; // Access the global database instance to perform SQL operations.

        // Attempt to find an existing category by the given name.
        $existing_category = self::find_by_category_name($category_name);

        // If the category does not exist, proceed to create a new one.
        if (!$existing_category) {
            $new_category = new Category(); // Instantiate a new Category object.
            $new_category->category_name = $category_name; // Set the category name.

            // Attempt to save the new category to the database.
            if($new_category->save()) {
                // If the category is successfully saved, return the new category's ID.
                return $database->the_insert_id();
            } else {
                // If saving the category fails, handle the error, e.g., by returning null.
                return null;
            }
        } else {
            // If the category already exists, return its ID.
            return $existing_category->id;
        }
    }


}
