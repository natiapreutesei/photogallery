<?php

class Db_object{

    public $id;
    public static function find_this_query($sql, $values = []){

        global $database;
        $result = $database->query($sql,$values);
        $the_object_array = [];
        while($row = mysqli_fetch_assoc($result)){
            $the_object_array[] = static::instantie($row);
        }
        return $the_object_array;
    }
    public static function instantie($result){
        //static late binding
        $calling_class = get_called_class();

        $the_object = new $calling_class;
        foreach($result as $the_attribute => $value){
            if($the_object->has_the_attribute($the_attribute)){
                $the_object->$the_attribute = $value;
            }
        }
        return $the_object;
    }
    public function has_the_attribute($the_attribute){
        $object_properties = get_object_vars($this);
        return array_key_exists($the_attribute, $object_properties);
    }

    public static function find_all(){
        $result = static::find_this_query("SELECT * FROM " . static::$table_name . " WHERE deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00'");
        return $result;
    }
    public static function find_all_soft_deletes(){
        $result = static::find_this_query("SELECT * FROM " . static::$table_name . " WHERE deleted_at IS NOT NULL AND deleted_at != '0000-00-00 00:00:00'");
        return $result;
    }
    public static function find_by_id($id){
        $result = static::find_this_query("SELECT * FROM " . static::$table_name ." where id=?",[$id]);
        return !empty($result) ? array_shift($result): false;
    }

    /*CRUD*/
    public function create() {
        global $database;

        $table = static::$table_name;
        $properties = $this->get_properties();

        // Set default value for 'deleted_at' if not already set
        if (!isset($properties['deleted_at'])) {
            $properties['deleted_at'] = '0000-00-00 00:00:00';
        }

        unset($properties['id']); // Auto-generated, so we remove it

        $escaped_values = array_map([$database, 'escape_string'], array_values($properties));
        $placeholders = array_fill(0, count($properties), '?');
        $fields_string = implode(',', array_keys($properties));

        // Adjust the SQL statement to include 'deleted_at' handling
        $sql = sprintf("INSERT INTO %s (%s) VALUES (%s)", $table, $fields_string, implode(',', $placeholders));

        // Execute the query with prepared statement
        if ($database->query($sql, $escaped_values)) {
            $this->id = $database->the_insert_id();
            return true;
        } else {
            return false;
        }
    }

    public function update(){
        global $database;
        // Tabelnaam ophalen uit de statische eigenschap van de klasse
        $table = static::$table_name;

        // Eigenschappen van de klasse ophalen als een array
        $properties = $this->get_properties();
        unset($properties['id']);

        // De waarden van de eigenschappen beschermen tegen SQL-injectie
        // door elk element door de 'escape_string' functie van het databaseobject te halen
        $escaped_values = array_map([$database, 'escape_string'], $properties);
        $escaped_values[] = $this->id;

        // Placeholder voor elke waarde in de query genereren (bijv. '?')
        // Dit wordt gebruikt in een prepared statement
        $placeholders = array_fill(0, count($properties), '?');

        //maakt een string van de veldnamen en placeholders gescheiden
        // van elkaar.
        $fields_string="";
        $i=0;

        foreach($properties as $key => $value){
            if($i>0){
                $fields_string .=", ";
            }
            $fields_string .= "$key = $placeholders[$i]";
            $i++;
        }
        //prepared statement
        $sql = "UPDATE $table SET $fields_string WHERE id= ?";
        return $database->query($sql, $escaped_values);
    }

    public function delete(){
        global $database;
        $table = static::$table_name;
        //sql injection prevention
        $escaped_id = $database->escape_string($this->id);
        //create van prepared statement
        $sql = "DELETE FROM $table where id = ?";
        //$params
        $params = [$escaped_id];

        //execute
        $database->query($sql,$params);
    }

    public function soft_delete(){
        global $database;
        $table = static::$table_name;
        //update deleted_at field with current datetime
        $deleted_at = date('Y-m-d H:i:s');

        $escaped_id = $database->escape_string($this->id);
        $sql = "UPDATE $table SET deleted_at = '$deleted_at' WHERE id = ?";

        //bind the parameter (?) with the id
        $params = [$escaped_id];
        //execute the statement
        $database->query($sql,$params);
    }

    public function save(){
        return isset($this->id) ? $this->update() : $this->create();
    }
}
?>