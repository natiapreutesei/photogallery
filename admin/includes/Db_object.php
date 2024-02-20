<?php
class Db_object{

    public $id;
    /**
     * Executes a SQL query and returns an array of objects representing the fetched rows.
     *
     * This method serves as a general-purpose query executor within the application,
     * allowing for the execution of dynamically constructed SQL queries. It is particularly
     * useful for retrieving data from the database and instantiating objects of the class
     * from which it's called, based on the retrieved rows.
     *
     * How It Works:
     * 1. It uses the global `$database` object to execute the provided SQL query, optionally
     *    binding values to the query if provided.
     * 2. For each row fetched from the database result set, it instantiates an object of the
     *    class from which this method was called.
     * 3. Each instantiated object is initialized with the values from the corresponding row,
     *    allowing for object-oriented access to the row's data.
     * 4. The instantiated objects are accumulated into an array, which is then returned to the caller.
     *
     * Parameters:
     * - `$sql`: The SQL query string to be executed.
     * - `$values`: An optional array of values to be bound to the query, supporting prepared statements.
     *
     * Usage Scenario:
     * This method is used throughout the application to perform various types of queries,
     * including SELECT operations that retrieve data from the database. It abstracts the
     * details of query execution, object instantiation, and result set handling, providing
     * a convenient way to obtain fully populated objects from database queries.
     *
     * Example Usage:
     * ```php
     * $users = User::find_this_query("SELECT * FROM users WHERE active = ?", [1]);
     * foreach ($users as $user) {
     *     echo $user->name;
     * }
     * ```
     * In this example, the `find_this_query` method is used within the User class to execute
     * a SELECT query that retrieves all active users. The method returns an array of User
     * objects, which can then be iterated over to access individual user data.
     *
     * Note: The `instantie` method referred to in this code is assumed to be a factory method
     * within the calling class that creates an instance of the class based on a provided row
     * of data from the database. This pattern allows for the flexible instantiation of objects
     * without explicitly coupling this method to a specific class.
     */
    public static function find_this_query($sql, $values = []){

        global $database;
        $result = $database->query($sql,$values);
        $the_object_array = [];
        while($row = mysqli_fetch_assoc($result)){
            $the_object_array[] = static::instantie($row);
        }
        return $the_object_array;
    }

    /**
     * Instantiates an object of the class from which it is called and assigns values to its properties based on a provided associative array.
     *
     * This method uses late static binding (`static::`) to determine the class from which it is called. It creates a new instance of that class
     * and then iterates over the provided associative array. Each key-value pair in the array corresponds to a property-name and its value
     * for the newly created object. If the object has the property corresponding to the current key in the array, the method assigns the value
     * to that property.
     *
     * Parameters:
     * - $result: An associative array where keys correspond to property names of the class and values are the values to be assigned to those properties.
     *
     * How It Works:
     * 1. The method retrieves the name of the class from which it is called using `get_called_class()`. This allows the method to be used in a class hierarchy,
     *    correctly instantiating an object of the class from which it is called, even in inheritance situations.
     * 2. A new instance of the calling class is created.
     * 3. The method iterates over each key-value pair in the provided associative array.
     *    a. For each pair, it checks if the newly created object has a property with the same name as the key (`$the_attribute`) using the `has_the_attribute` method.
     *    b. If the property exists, the value is assigned to it.
     * 4. The fully populated object is returned.
     *
     * Usage Scenario:
     * This method is typically used in conjunction with methods that fetch data from a database. The fetched data is often returned as an associative array,
     * which can then be passed to this method to create a fully populated object that represents a row from the database.
     *
     * Example Usage:
     * Assuming a class `User` with properties `id`, `username`, and `email`, and an associative array `$userData` fetched from the database:
     * ```php
     * $userData = ['id' => 1, 'username' => 'johndoe', 'email' => 'john@example.com'];
     * $user = User::instantie($userData);
     * ```
     * In this example, `$user` is an instance of `User` with its properties set to the values from `$userData`.
     *
     * Note: The `has_the_attribute` method is assumed to check if the object has a given property, helping to safely populate the object without directly accessing properties that might not exist.
     */
    public static function instantie($result){
        // Utilizes late static binding to get the name of the class that called this method.
        $calling_class = get_called_class();

        // Instantiates an object of the calling class.
        $the_object = new $calling_class;

        // Iterates over the associative array.
        foreach($result as $the_attribute => $value){
            // Checks if the object has a property named as the current key in the array.
            if($the_object->has_the_attribute($the_attribute)){
                // If the property exists, assigns the value to it.
                $the_object->$the_attribute = $value;
            }
        }
        // Returns the fully populated object.
        return $the_object;
    }

    /**
     * Checks if the current object has a property with the specified name.
     *
     * This method is crucial for dynamically checking if an object has a given property, especially when working with database results.
     * It uses `get_object_vars` to retrieve an associative array of all properties accessible from the current object context, including both public
     * and private/protected properties (when called within the class context).
     *
     * Parameters:
     * - $the_attribute: The name of the property to check for.
     *
     * How It Works:
     * 1. `get_object_vars($this)` returns an associative array where keys are the names of the object's properties and the values are the property values.
     *    When called within an object's method, `get_object_vars` includes private and protected properties of that object, along with public ones.
     * 2. `array_key_exists($the_attribute, $object_properties)` checks if the specified property name exists as a key in the array of object properties.
     * 3. Returns `true` if the property exists on the object, `false` otherwise.
     *
     * Usage Scenario:
     * This method is particularly useful in methods that dynamically set or get property values based on database results or other associative arrays.
     * It ensures that only properties existing on the object are accessed, preventing errors and maintaining encapsulation.
     *
     * Example Usage:
     * In a class `User` with properties `id`, `username`, `email`, and a method to dynamically assign values from an associative array:
     * ```php
     * $userData = ['id' => 1, 'username' => 'johndoe', 'email' => 'john@example.com', 'nonExistentProperty' => 'value'];
     * foreach ($userData as $property => $value) {
     *     if ($this->has_the_attribute($property)) {
     *         $this->$property = $value;
     *     }
     * }
     * ```
     * In this example, `has_the_attribute` is used to safely assign values to `User` properties from `$userData`, ignoring keys that do not match any property names.
     *
     * Note: This method enhances the robustness of dynamic property assignment by ensuring that only valid properties are manipulated, helping to prevent potential bugs or security issues.
     */
    public function has_the_attribute($the_attribute){
        // Retrieves an associative array of the object's properties accessible in this context.
        $object_properties = get_object_vars($this);

        // Checks if the specified attribute exists as a key in the properties array.
        return array_key_exists($the_attribute, $object_properties);
    }


    /**
     * Retrieves all records from the database table associated with the calling class that have not been soft-deleted.
     *
     * This static method is designed to fetch all entries from a specific database table, filtering out those marked as soft-deleted.
     * It utilizes a common pattern in web applications where records are not permanently deleted but marked with a 'deleted_at' timestamp.
     * Records with a 'NULL' or '0000-00-00 00:00:00' timestamp in the 'deleted_at' column are considered active.
     *
     * How It Works:
     * 1. It constructs an SQL query string using the static property `$table_name`, which should be defined in each subclass to specify the database table name.
     * 2. The query includes a WHERE clause to exclude records marked as deleted (i.e., those with a non-NULL 'deleted_at' timestamp).
     * 3. It calls `find_this_query`, a method expected to execute the SQL query and return an array of objects representing the fetched records.
     *    Each object in the array is an instance of the calling class, populated with data from a corresponding database record.
     *
     * Usage Scenario:
     * This method is typically used in ORM (Object-Relational Mapping) implementations within web applications to abstract and encapsulate database operations.
     * It allows for easily fetching all active records from a table without directly writing SQL queries in the business logic code.
     *
     * Example Usage:
     * Assuming a class `Article` extends a base class where `find_all` is defined, and `Article::$table_name` is set to "articles":
     * ```php
     * $activeArticles = Article::find_all();
     * foreach ($activeArticles as $article) {
     *     echo $article->title . "<br>";
     * }
     * ```
     * This example fetches all active articles from the "articles" database table and outputs their titles.
     * It demonstrates how `find_all` simplifies data retrieval by abstracting the underlying database query.
     *
     * Note: The method assumes that `static::find_this_query` is implemented to handle SQL execution and object instantiation. The approach follows the Active Record design pattern, aiming to keep data access simple and intuitive.
     */
    public static function find_all(){
        // Constructs the SQL query string, dynamically using the class's table name and filtering out soft-deleted records.
        $result = static::find_this_query("SELECT * FROM " . static::$table_name . " WHERE deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00'");

        // Returns the result of the query execution, which is an array of objects of the calling class, representing the active records.
        return $result;
    }

    /**
     * Fetches all records from the associated database table that have been soft-deleted.
     *
     * This static method complements `find_all` by specifically targeting records that have been marked as soft-deleted.
     * Soft-deletion is a common practice where records are not physically removed from the database but marked with a 'deleted_at' timestamp.
     * This method retrieves those records, providing a way to access or manage deleted data without permanently losing it.
     *
     * How It Works:
     * 1. Constructs an SQL query string, using the class's `$table_name` static property to dynamically determine the database table.
     * 2. The SQL query includes a WHERE clause that filters for records with a non-null 'deleted_at' timestamp, indicating they have been soft-deleted.
     * 3. Executes the query by calling `find_this_query`, which is expected to perform the SQL operation and return an array of object instances representing the soft-deleted records.
     *
     * Usage Scenario:
     * This method is particularly useful in administrative interfaces or audit logs where there's a need to review or restore soft-deleted records.
     * It allows for easy retrieval of such records, facilitating operations like undoing deletions or analyzing historical data.
     *
     * Example Usage:
     * Assuming a `User` class extends a base class where `find_all_soft_deletes` is defined, and `User::$table_name` is set to "users":
     * ```php
     * $deletedUsers = User::find_all_soft_deletes();
     * foreach ($deletedUsers as $user) {
     *     echo $user->name . " was deleted on " . $user->deleted_at . "<br>";
     * }
     * ```
     * This example fetches all soft-deleted users from the "users" table and displays their name along with the deletion timestamp.
     * It demonstrates how `find_all_soft_deletes` enables specific access to soft-deleted records, which can be critical for data recovery or auditing purposes.
     *
     * Note: The functionality assumes that `static::find_this_query` is implemented to execute the SQL and return the appropriate objects.
     * The method follows the Active Record design pattern, facilitating direct interaction with database records through class methods and properties.
     */
    public static function find_all_soft_deletes(){
        // Dynamically constructs the SQL query to select all records marked as soft-deleted from the class's table.
        $result = static::find_this_query("SELECT * FROM " . static::$table_name . " WHERE deleted_at IS NOT NULL AND deleted_at != '0000-00-00 00:00:00'");

        // Returns the query result, which is an array of instances of the calling class, each representing a soft-deleted record.
        return $result;
    }

    /**
     * Retrieves a single record from the database table associated with the calling class by its unique identifier (ID).
     *
     * This method is a fundamental part of the Active Record implementation, allowing for the retrieval of a single database record based on its ID.
     * It utilizes prepared statements to securely query the database, preventing SQL injection attacks by parameterizing the ID value.
     *
     * How It Works:
     * 1. Constructs an SQL query string that selects all columns from the class's associated table where the 'id' column matches the provided ID.
     *    The class's `$table_name` static property dynamically determines the specific database table.
     * 2. Executes the query by calling `find_this_query`, a method expected to execute the SQL operation securely with prepared statements, passing the ID as a parameter.
     * 3. If the query returns results, `array_shift` is used to extract the first element from the resulting array, which should be the desired record.
     *    If no results are found, the method returns false, indicating no record with the provided ID exists in the table.
     *
     * Usage Scenario:
     * This method is particularly useful when needing to fetch a specific record to display its details, update its fields, or delete it.
     * It's a common operation in CRUD (Create, Read, Update, Delete) functionalities within web applications.
     *
     * Example Usage:
     * Assuming a `Product` class extends a base class where `find_by_id` is defined, and `Product::$table_name` is set to "products":
     * ```php
     * $product = Product::find_by_id(1);
     * if ($product) {
     *     echo "Product Name: " . $product->name;
     * } else {
     *     echo "Product not found.";
     * }
     * ```
     * This example attempts to fetch a product with ID 1 from the "products" table. If found, it displays the product's name; otherwise, it indicates that the product is not found.
     *
     * Note: The `find_this_query` method should be capable of handling prepared statements and returning an array of object instances.
     * The use of `array_shift` ensures that even if multiple records are mistakenly returned, only the first one is considered, adhering to the expectation of a unique ID.
     */
    public static function find_by_id($id){
        // Executes a secure, parameterized query to fetch a record by its ID from the class's associated database table.
        $result = static::find_this_query("SELECT * FROM " . static::$table_name . " where id=?",[$id]);

        // Returns the first result if available, or false if no matching record is found.
        return !empty($result) ? array_shift($result): false;
    }


    /*CRUD*/
    /**
     * Creates a new record in the database table associated with the class, using the object's current property values.
     *
     * This method is part of an Active Record implementation, allowing objects to save themselves to the database.
     * It dynamically constructs an SQL INSERT statement based on the object's properties, safely handling data insertion using prepared statements.
     *
     * How It Works:
     * 1. Retrieves the object's properties using the `get_properties` method, ensuring only relevant data is included in the INSERT operation.
     * 2. Sets a default value for 'deleted_at' if it's not explicitly set, ensuring soft delete functionality is supported out of the box.
     * 3. Excludes the 'id' property from the INSERT operation since it's auto-generated by the database.
     * 4. Escapes all values to ensure data integrity and security, preventing SQL injection.
     * 5. Dynamically constructs the SQL INSERT statement using the table name, field names, and placeholders for values.
     * 6. Executes the constructed SQL statement as a prepared statement with the property values bound as parameters.
     * 7. On successful insertion, sets the object's 'id' property to the newly generated ID by the database.
     *
     * Usage Scenario:
     * This method is used to save a new instance of a class (representing a database record) to the database.
     * It's typically called after setting the object's properties to the desired values for the new record.
     *
     * Example Usage:
     * Assuming a `User` class with properties corresponding to database columns and `User::$table_name` set to "users":
     * ```php
     * $user = new User();
     * $user->name = "John Doe";
     * $user->email = "john.doe@example.com";
     * if ($user->create()) {
     *     echo "User created with ID: " . $user->id;
     * } else {
     *     echo "Failed to create user.";
     * }
     * ```
     * This example creates a new user record in the "users" table with the name "John Doe" and email "john.doe@example.com".
     * If the operation is successful, it outputs the new user's ID; otherwise, it indicates failure.
     *
     * Note: The method assumes the presence of a global `$database` object for database interaction, a `get_properties` method for retrieving object properties, and a `the_insert_id` method for fetching the last inserted ID.
     */
    public function create() {
        global $database; // Access the global database object.

        $table = static::$table_name; // Dynamically determine the database table.
        $properties = $this->get_properties(); // Retrieve object properties as key-value pairs.

        if (!isset($properties['deleted_at'])) {
            $properties['deleted_at'] = '0000-00-00 00:00:00'; // Default value for soft delete support.
        }

        unset($properties['id']); // Exclude the 'id' property, as it's auto-generated.

        // Escape all property values for security.
        $escaped_values = array_map([$database, 'escape_string'], array_values($properties));
        $placeholders = array_fill(0, count($properties), '?'); // Create placeholders for prepared statement.
        $fields_string = implode(',', array_keys($properties)); // Create a comma-separated string of property names.

        // Construct the SQL INSERT statement.
        $sql = sprintf("INSERT INTO %s (%s) VALUES (%s)", $table, $fields_string, implode(',', $placeholders));

        if ($database->query($sql, $escaped_values)) {
            $this->id = $database->the_insert_id(); // Set the object's 'id' to the new record's ID.
            return true; // Indicate success.
        } else {
            return false; // Indicate failure.
        }
    }


    /**
     * Updates an existing record in the database table associated with the class, using the object's current property values.
     *
     * This method allows objects to persist changes back to the database. It dynamically constructs an SQL UPDATE statement
     * based on the object's properties, ensuring a secure operation through the use of prepared statements.
     *
     * How It Works:
     * 1. Retrieves the object's properties excluding the 'id', as the 'id' is used to identify the record to update and should not be modified.
     * 2. Escapes all property values to prevent SQL injection, ensuring data integrity and security.
     * 3. Dynamically constructs the SQL UPDATE statement, incorporating placeholders for values to facilitate the use of prepared statements.
     * 4. Executes the constructed SQL statement with the property values bound as parameters, targeting the record with the matching 'id'.
     *
     * Usage Scenario:
     * This method is used when an instance of a class (representing a database record) needs to update its corresponding database record.
     * It's typically called after modifying one or more of the object's properties to reflect changes in the database.
     *
     * Example Usage:
     * Assuming a `User` class with properties corresponding to database columns and `User::$table_name` set to "users":
     * ```php
     * $user = User::find_by_id(1); // Assume this returns a User object with ID 1
     * $user->name = "Jane Doe"; // Modify the name property
     * if ($user->update()) {
     *     echo "User updated successfully.";
     * } else {
     *     echo "Failed to update user.";
     * }
     * ```
     * This example updates the name of the user with ID 1 in the "users" table to "Jane Doe".
     * If the operation is successful, it outputs a confirmation message; otherwise, it indicates failure.
     *
     * Note: The method assumes the presence of a global `$database` object for database interaction and a `get_properties` method for retrieving object properties.
     */
    public function update(){
        global $database; // Access the global database object.

        $table = static::$table_name; // Dynamically determine the database table.
        $properties = $this->get_properties(); // Retrieve object properties as key-value pairs, excluding 'id'.
        unset($properties['id']); // Ensure 'id' is not included in the update.

        // Escape all property values for security.
        $escaped_values = array_map([$database, 'escape_string'], array_values($properties));
        $escaped_values[] = $this->id; // Append 'id' to the end for WHERE clause.

        // Generate placeholders for each property value.
        $placeholders = array_fill(0, count($properties), '?');

        // Construct the fields part of the SQL UPDATE statement.
        $fields_string = "";
        $i = 0;
        foreach ($properties as $key => $value) {
            if ($i > 0) {
                $fields_string .= ", ";
            }
            $fields_string .= "$key = ?"; // Use placeholder for each field value.
            $i++;
        }

        // Construct the full SQL UPDATE statement.
        $sql = "UPDATE $table SET $fields_string WHERE id = ?";

        // Execute the query with the prepared statement.
        return $database->query($sql, $escaped_values); // Perform the update operation.
    }


    /**
     * Deletes the database record associated with this object's 'id'.
     *
     * This method facilitates the deletion of a record in the database table corresponding to the class from which it's called.
     * It uses a prepared statement to securely execute the deletion, preventing SQL injection and ensuring that only the intended
     * record is removed based on the object's 'id' property.
     *
     * How It Works:
     * 1. Identifies the table associated with the class through the static property `$table_name`.
     * 2. Escapes the object's 'id' property to prevent SQL injection, although using prepared statements inherently mitigates this risk.
     * 3. Constructs a SQL DELETE statement targeting the record with the specified 'id'.
     * 4. Executes the statement with the 'id' as a parameter, ensuring targeted and secure deletion.
     *
     * Usage Scenario:
     * This method is typically invoked on an instance of a class representing a specific database record that needs to be removed.
     * For example, deleting a user or a blog post from their respective tables based on their unique identifier.
     *
     * Example Usage:
     * Assuming a `Post` class representing entries in a "posts" table and an instance `$post` with an 'id' of 5:
     * ```php
     * $post->delete();
     * ```
     * This would delete the post with ID 5 from the "posts" table in the database.
     *
     * Note: This method assumes the presence of a global `$database` object for database interaction. The deletion is irreversible,
     * so it should be used with caution and ideally be protected by user confirmation or similar safeguards.
     */
    public function delete(){
        global $database; // Access the global database object.

        $table = static::$table_name; // Determine the table name associated with this class.

        // Prepare the SQL DELETE statement.
        // Note: The use of prepared statements with parameter binding inherently protects against SQL injection,
        // making manual escaping redundant. This is retained for instructional purposes.
        $sql = "DELETE FROM $table WHERE id = ?";

        // Parameters for the prepared statement.
        // Directly using the object's 'id' property without manual escaping due to prepared statement usage.
        $params = [$this->id];

        // Execute the deletion query.
        // The method `query` is assumed to support prepared statements, securely executing the operation.
        $database->query($sql, $params);
    }


    /**
     * Marks a database record as deleted without physically removing it from the database.
     *
     * This method implements the soft delete functionality by updating a 'deleted_at' field in the database record with the current datetime.
     * It allows the record to be treated as deleted in application logic while retaining it in the database for potential recovery or auditing purposes.
     *
     * How It Works:
     * 1. Identifies the appropriate database table using the static property `$table_name` specific to the class instance.
     * 2. Generates the current datetime string, which will be used to mark the record as deleted.
     * 3. Safely escapes the object's 'id' property to prevent SQL injection, though this is technically redundant when using prepared statements.
     * 4. Constructs a SQL UPDATE statement to set the 'deleted_at' field to the current datetime for the record with the given 'id'.
     * 5. Executes the SQL statement with the 'id' as a parameter, ensuring the operation targets only the intended record.
     *
     * Usage Scenario:
     * This method is useful in scenarios where data retention is important and deleting records permanently is not desirable.
     * For example, in user management systems, soft deleting user records can help preserve data integrity and maintain a history of user actions.
     *
     * Example Usage:
     * Assuming a `User` class representing users in a "users" table and an instance `$user` with an 'id' of 3:
     * ```php
     * $user->soft_delete();
     * ```
     * This would mark the user with ID 3 as deleted in the "users" table by updating the 'deleted_at' field with the current datetime.
     *
     * Note: This method relies on the presence of a 'deleted_at' column in the database table. It assumes the existence of a global `$database` object for database operations. The method safely uses prepared statements to execute the update operation, ensuring security and accuracy.
     */
    public function soft_delete(){
        global $database; // Access the global database object.

        $table = static::$table_name; // Determine the table name associated with this class.

        // Generate the current datetime string to mark the record as deleted.
        $deleted_at = date('Y-m-d H:i:s');

        // Construct the SQL UPDATE statement.
        $sql = "UPDATE $table SET deleted_at = ? WHERE id = ?";

        // Parameters for the prepared statement.
        $params = [$deleted_at, $this->id];

        // Execute the soft delete operation.
        // The method `query` supports prepared statements, allowing secure and targeted updating of the record.
        $database->query($sql, $params);
    }


    /**
     * Saves the current object's state to the database.
     *
     * This method decides whether to create a new record or update an existing one based on the presence of the object's 'id' property.
     * It acts as a bridge between the `create` and `update` methods, streamlining the save operation for any object instance.
     *
     * How It Works:
     * 1. Checks if the object's 'id' property is set. The 'id' is typically assigned when a record already exists in the database.
     *    - If 'id' is set, it means the object represents an existing record, and the method calls `update` to modify the record in the database.
     *    - If 'id' is not set, the object is assumed to be new, and `create` is called to insert a new record into the database.
     *
     * Usage Scenario:
     * This method simplifies saving objects to the database, abstracting away the need to manually determine whether to insert or update.
     * It is particularly useful in forms or applications where an object could either be new or an edited version of an existing record.
     *
     * Example Usage:
     * ```php
     * $user = new User();
     * $user->name = "Jane Doe";
     * $user->email = "jane@example.com";
     * // For a new user, 'id' is not set, so `create` will be called:
     * $user->save();
     *
     * // For updating an existing user, 'id' would be set:
     * $existingUser->name = "John Doe";
     * $existingUser->save(); // `update` is called because 'id' is set.
     * ```
     *
     * Note: The actual implementation of `create` and `update` methods is responsible for the specifics of inserting or modifying the database records.
     * This method merely delegates to those methods based on the object's state, ensuring that objects are persisted correctly without additional logic.
     */
    public function save(){
        // Determine whether to create a new record or update an existing one based on the presence of an 'id'.
        return isset($this->id) ? $this->update() : $this->create();
    }

}
?>