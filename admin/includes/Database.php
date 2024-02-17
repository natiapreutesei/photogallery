<?php
require_once("config.php");

class Database
{
    /* properties of variabelen*/
    public $connection;
    private static $instance; // The single instance

    /* methods of functions*/
    public function open_db_connection()
    {
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if (mysqli_connect_errno()) {
            printf("Connectie mislukt: %s\n", mysqli_connect_errno());
            exit();
        }
    }

    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            die("Prepare statement failed: " . $this->connection->error);
        }

        if (!empty($params)) {
            // Calculate types
            $types = $this->calculateParamTypes($params);

            // Prepare the arguments for bind_param, which requires references
            $bindParams = [];
            $bindParams[] = &$types; // The first element is the string of types
            foreach ($params as $key => $value) {
                $bindParams[] = &$params[$key]; // Bind the reference of each parameter
            }

            // Use call_user_func_array to call bind_param with a dynamic number of params
            call_user_func_array([$stmt, 'bind_param'], $bindParams);
        }

        $executed = $stmt->execute();

        // Handle execution results based on the type of SQL operation
        if ($executed) {
            if (strpos(strtoupper($sql), "INSERT") !== false) {
                $lastId = $stmt->insert_id;
                $stmt->close();
                return $lastId; // For INSERT operations, return the last inserted ID
            } elseif (strpos(strtoupper($sql), "SELECT") !== false) {
                $result = $stmt->get_result();
                $stmt->close();
                return $result; // For SELECT operations, return the result set
            } else {
                $affected = $stmt->affected_rows;
                $stmt->close();
                return $affected; // For UPDATE/DELETE operations, return the number of affected rows
            }
        } else {
            $error = $stmt->error;
            $stmt->close();
            die("Query execution failed: " . $error);
        }
    }

    private function calculateParamTypes($params) {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } elseif (is_string($param)) {
                $types .= 's';
            } else {
                $types .= 'b'; // Default to blob for others
            }
        }
        return $types;
    }


    // Get an instance of the database
    public static function getInstance() {
        if (!self::$instance) { // If no instance then make one
            self::$instance = new self();
        }
        return self::$instance;
    }


    public function the_insert_id() {
        // Returns the auto generated id used in the latest query
        return $this->connection->insert_id;
    }

    public function escape_string($string) {
        if(is_string($string)) {
            return $this->connection->real_escape_string($string);
        }
        return $string;
    }


    /*constructors*/
    function __construct()
    {
        $this->open_db_connection();
    }

    // Prevent cloning of the instance
    private function __clone() {}
}

$database = new Database();
?>