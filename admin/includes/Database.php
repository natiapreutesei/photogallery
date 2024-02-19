<?php
require_once("config.php");

class Database
{
    /* properties of variabelen*/
    public $connection;
    private static $instance; // The single instance

    /* methods of functions*/
    /**
     * Establishes a connection to the MySQL database using the MySQLi extension.
     * This method leverages predefined constants for connection parameters to ensure
     * a secure and configurable connection setup.
     *
     * The method does the following:
     * - Utilizes the MySQLi object-oriented interface to attempt a database connection.
     * - Uses predefined constants (DB_HOST, DB_USER, DB_PASS, DB_NAME) to specify the database
     *   host, user, password, and name, facilitating easy configuration and maintenance.
     * - Checks for a connection error and, if found, outputs an error message and terminates the script.
     *
     * @return void This method does not return a value but initializes the $this->connection property with
     *              the established MySQLi connection object on success.
     */
    public function open_db_connection() {
        // Attempt to establish a database connection using the MySQLi class.
        // The constructor of the MySQLi class is called with the database connection parameters
        // defined by the constants DB_HOST, DB_USER, DB_PASS, and DB_NAME.
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        // Immediately after attempting to connect, check for a connection error.
        // The mysqli_connect_errno function is used to retrieve the error code associated
        // with the last connection attempt. A return value of zero means no error occurred.
        if (mysqli_connect_errno()) {
            // If an error code is present, it means the connection attempt failed.
            // Print a formatted error message including the error code to aid in troubleshooting.
            // The %s placeholder is replaced by the actual error code returned by mysqli_connect_errno.
            printf("Connection failed: %s\n", mysqli_connect_errno());

            // After printing the error message, terminate the script using exit.
            // This is critical because if the database connection cannot be established,
            // further execution of the script could lead to unexpected behavior or security risks.
            exit();
        }
    }


    /**
     * Executes an SQL query using prepared statements, providing enhanced security and flexibility.
     * This method supports SELECT, INSERT, UPDATE, and DELETE operations with dynamic parameter binding.
     *
     * @param string $sql The SQL query to be executed.
     * @param array $params An array of parameters to be bound to the SQL query.
     * @return mixed Depending on the SQL operation, it may return the last inserted ID,
     *               a result set for SELECT queries, the number of rows affected by an UPDATE or DELETE,
     *               or null on failure.
     *
     * Method functionality includes:
     * - Preparing the SQL statement to prevent SQL injection attacks.
     * - Dynamically binding parameters to the prepared statement based on their types.
     * - Executing the prepared statement and handling the results based on the type of SQL operation.
     */
    public function query($sql, $params = []) {
        // Attempt to prepare the SQL statement with the database connection.
        // This method separates the query structure from the data, mitigating SQL injection risks.
        $stmt = $this->connection->prepare($sql);

        // Check if statement preparation was successful; if not, terminate with an error.
        if (!$stmt) {
            die("Prepare statement failed: " . $this->connection->error);
        }

        // If parameters are provided, bind them to the prepared statement.
        if (!empty($params)) {
            // Determine the types of the provided parameters to ensure correct data handling.
            $types = $this->calculateParamTypes($params);

            // bind_param requires references, so create an array of references to the parameters.
            $bindParams = [];
            $bindParams[] = &$types; // The first element is the string of types.
            foreach ($params as $key => $value) {
                $bindParams[] = &$params[$key]; // Add references to each parameter.
            }

            // Dynamically call bind_param using call_user_func_array to accommodate varying numbers of parameters.
            call_user_func_array([$stmt, 'bind_param'], $bindParams);
        }

        // Execute the prepared statement and check for successful execution.
        $executed = $stmt->execute();

        if ($executed) {
            // Determine the type of SQL operation performed and handle accordingly.
            if (strpos(strtoupper($sql), "INSERT") !== false) {
                // For INSERT queries, return the ID of the newly inserted row.
                $lastId = $stmt->insert_id;
                $stmt->close();
                return $lastId;
            } elseif (strpos(strtoupper($sql), "SELECT") !== false) {
                // For SELECT queries, return the result set for further processing.
                $result = $stmt->get_result();
                $stmt->close();
                return $result;
            } else {
                // For UPDATE or DELETE queries, return the number of affected rows.
                $affected = $stmt->affected_rows;
                $stmt->close();
                return $affected;
            }
        } else {
            // If execution fails, report the error and terminate.
            $error = $stmt->error;
            $stmt->close();
            die("Query execution failed: " . $error);
        }
    }


    /**
     * The `calculateParamTypes` method dynamically generates a string that represents the data types
     * of the parameters that will be bound to a MySQL prepared statement. This string is crucial for
     * the `mysqli_stmt_bind_param` function, which requires type definitions for the parameters to
     * ensure the data is treated correctly by the database engine.
     *
     * In a typical application using a MySQL database, it's important to match the data types of the
     * parameters accurately to prevent type coercion issues or data integrity problems. This method
     * simplifies the process by automatically determining the types based on the actual values passed.
     *
     * Method Overview:
     * - Iterates over each parameter provided to it.
     * - Determines the type of each parameter (integer, float, string, or binary).
     * - Appends a corresponding character ('i' for integer, 'd' for double/float, 's' for string,
     *   and 'b' for binary/blob) to a string that collectively represents the types of all parameters.
     * - This string of characters is then used in prepared statements to correctly bind the parameters
     *   with their respective types.
     *
     * Benefits:
     * - Automates the process of specifying parameter types for prepared statements, reducing manual coding effort.
     * - Enhances security and efficiency by ensuring that each parameter is treated with its correct type,
     *   thus leveraging the full capabilities of prepared statements, such as preventing SQL injection.
     *
     * Returns:
     * - A string where each character represents the data type of the corresponding parameter in the
     *   order they are passed. This string is directly used in the binding process of a prepared statement.
     */
    private function calculateParamTypes($params) {
        // Initialize an empty string to hold the type specification characters.
        $types = '';

        // Iterate over each parameter passed to the function.
        foreach ($params as $param) {
            // Check the type of the parameter and concatenate the corresponding
            // character to the $types string.
            if (is_int($param)) {
                // If the parameter is an integer, append 'i' to the $types string.
                $types .= 'i';
            } elseif (is_float($param)) {
                // If the parameter is a floating-point number, append 'd' (for double).
                $types .= 'd';
            } elseif (is_string($param)) {
                // If the parameter is a string, append 's'.
                $types .= 's';
            } else {
                // If the parameter is of any other type, treat it as a blob ('b'),
                // which is a binary large object that can store any data except NULL.
                $types .= 'b';
            }
        }

        // After iterating through all parameters, return the composed string
        // that represents their data types.
        return $types;
    }


    /**
     * The getInstance method is a critical component of the Singleton design pattern applied to the database connection class.
     * This pattern ensures that only one instance of the database connection class is created and used throughout the application,
     * which is particularly important for efficiently managing resources and maintaining a consistent state across the application.
     *
     * Usage of the Singleton pattern in database connections is common because it prevents multiple connections to the database,
     * which can be resource-intensive and unnecessary. By having a single point of access to the database connection,
     * the application can run more efficiently and with fewer errors related to database access.
     *
     * Method Overview:
     * - If no instance of the database connection class exists, it creates one and stores it in a static property.
     * - If an instance already exists, it simply returns that instance without creating a new one.
     * - This ensures that throughout the application lifecycle, only one database connection is active,
     *   reducing overhead and facilitating easier management of the database connection.
     *
     * Returns:
     * - The single, shared instance of the database connection class.
     */
    public static function getInstance() {
        if (!self::$instance) { // If no instance then make one
            self::$instance = new self();
        }
        return self::$instance;
    }


    /**
     * Retrieves the auto-generated ID from the last INSERT operation performed on the database.
     *
     * This method is essential when working with database records that require
     * retrieval of the unique identifier automatically generated for a new record
     * upon insertion (e.g., an auto-increment primary key in a MySQL table).
     *
     * Usage Scenario:
     * After inserting a new row into a table with an auto-increment primary key,
     * you may need to use the new row's ID for further operations, such as establishing
     * relationships in other tables. This method provides a straightforward way to get that ID.
     *
     * @return int The ID generated for an AUTO_INCREMENT column by the previous INSERT query.
     *             Returns zero if the previous query does not generate an AUTO_INCREMENT value.
     *             If no query has been executed yet, or if the query does not involve an
     *             AUTO_INCREMENT column, this method returns zero.
     */
    public function the_insert_id() {
        // Access the 'insert_id' property of the MySQLi object stored in $this->connection.
        // The 'insert_id' property holds the ID generated by a query on a table with an AUTO_INCREMENT column.
        // This value is crucial for tracking newly created records without additional database queries.

        // Return the value of the 'insert_id' property.
        // This value is the ID of the last inserted record if the table has an AUTO_INCREMENT primary key.
        // If the last query did not result in an auto-generated ID, this method returns zero.
        return $this->connection->insert_id;
    }


    /**
     * Safely escapes a string for use in a SQL query to prevent SQL injection attacks.
     *
     * When incorporating user input directly into SQL queries, there's a risk of SQL injection,
     * where malicious users can manipulate the query to access or modify data they shouldn't have access to.
     * This method mitigates that risk by escaping special characters in the input string according
     * to the current character set of the database connection. This makes the input safe for use in SQL queries.
     *
     * Usage Scenario:
     * Use this method to sanitize user input or any external data before including it in a SQL query.
     * This is a critical security measure for any database interactions involving potentially unsafe input.
     *
     * @param string $string The input string to be sanitized for safe inclusion in a SQL query.
     * @return string The sanitized string, with special characters escaped, making it safe for SQL queries.
     *                If the input is not a string, it's returned unmodified, as non-string types don't require escaping.
     */
    public function escape_string($string) {
        // First, check if the input is a string. Non-string types don't require escaping.
        if(is_string($string)) {
            // Use the MySQLi object's real_escape_string method to escape special characters
            // in the string according to the current character set of the database connection.
            // This prepares the string for safe inclusion in SQL queries, effectively preventing SQL injection.
            return $this->connection->real_escape_string($string);
        }
        // If the input is not a string, return it unmodified.
        // This handles cases where non-string data is inadvertently passed to the method.
        return $string;
    }



    /*constructors*/
    /**
     * Constructor method for the database connection class.
     *
     * This method is automatically called when an instance of the class is created.
     * Its primary responsibility is to establish a connection to the database by invoking
     * the open_db_connection method. This ensures that a database connection is ready and
     * available for use immediately upon instantiation of the class.
     *
     * The open_db_connection method is defined within the same class and encapsulates
     * the logic required to connect to the database, including handling errors and setting
     * the appropriate connection parameters. By calling this method in the constructor,
     * it abstracts the complexity of database connectivity from the user, providing a simplified
     * and streamlined interface for interacting with the database.
     *
     * Usage Scenario:
     * This constructor is utilized whenever a new object of the database connection class is needed.
     * It can be used in various parts of the application where database interaction is required,
     * ensuring that each object has its own connection to the database ready to execute queries.
     *
     * Example:
     * $databaseConnection = new DatabaseConnectionClass();
     * // At this point, $databaseConnection is ready to use with an open database connection.
     */
    function __construct() {
        // Automatically open a database connection upon instantiation of the class.
        // This ensures that every object of this class has immediate access to a functional
        // database connection, simplifying database operations throughout the application.
        $this->open_db_connection();
    }


    /**
     * The __clone method is defined as private to prevent cloning of an instance of the class.
     *
     * Cloning an object in PHP creates a shallow copy of it. However, for certain classes, especially
     * those that handle resources like database connections, cloning could lead to unintended consequences
     * such as duplicate connections or shared state between two instances of the class. This can lead to
     * hard-to-debug issues in your application.
     *
     * By marking the __clone method as private, any attempt to clone instances of this class from outside
     * the class will result in a PHP Fatal error. This design pattern is used to implement a Singleton pattern
     * or similar patterns where it is critical to ensure that only one instance of the class exists throughout
     * the application lifecycle.
     *
     * Usage Scenario:
     * This method is implicitly called when an attempt is made to clone an object of this class. The private
     * visibility ensures that the method cannot be called from outside the class, effectively preventing
     * external code from cloning instances of the class.
     *
     * Example:
     * $instance = new SingletonClass();
     * $cloneInstance = clone $instance; // This will cause a fatal error.
     *
     * Benefits:
     * - Ensures that the class adheres to a Singleton pattern by preventing multiple instances.
     * - Protects resources and state managed by the class from being duplicated unintentionally.
     * - Enforces better control over how the class is used within the application, promoting consistency
     *   and reliability in the class's behavior.
     */
    private function __clone() {
        // Intentionally left empty to prevent cloning of the instance.
    }

}

/**
 * Instantiation of the Database class.
 *
 * This line of code creates a new instance of the Database class, which is designed to manage
 * all interactions with the database. This includes opening connections to the database,
 * executing queries, and handling results. The Database class is likely to include methods
 * for performing these operations in a secure and efficient manner.
 *
 * Usage Scenario:
 * The $database object created here serves as a central point for database operations throughout
 * the application. It encapsulates the complexity of direct database interactions, providing
 * a cleaner and more intuitive interface for executing database queries and handling results.
 * This object can be used anywhere within the project where database access is required.
 *
 * Example:
 * // Use the $database object to execute a SELECT query
 * $result = $database->query("SELECT * FROM users WHERE id = 1");
 * // Process the query results...
 *
 * Background:
 * Instantiating the Database class at this point in the code ensures that a database connection
 * is readily available for use throughout the application. This is a common pattern in web
 * applications, where managing database connections efficiently is crucial for performance
 * and reliability. The Database class might include mechanisms for connection pooling, error
 * handling, and other best practices related to database management.
 */
$database = new Database();

?>