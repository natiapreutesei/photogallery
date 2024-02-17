<?php
class Tag extends Db_object {
    public $id;
    public $tag_name;

    public static function find_all_tags() {
        global $database;
        $tags = [];

        $sql = "SELECT * FROM tags";
        $result = $database->query($sql);

        while($row = $result->fetch_assoc()) {
            $tags[] = $row['tag_name']; // Assuming 'tag_name' is the column name
        }

        return $tags;
    }
    // Method to get tags by photo ID
    public static function find_tags_by_photo_id($photo_id) {
        global $database;
        $tag_list = [];

        $sql = "SELECT t.tag_name FROM tags t 
                INNER JOIN photo_tags pt ON t.id = pt.tag_id 
                WHERE pt.photo_id = ?";

        $result = $database->query($sql, [$photo_id]);

        while($row = $result->fetch_assoc()) {
            $tag_list[] = $row['tag_name'];
        }

        return $tag_list;
    }
    // Method to find a tag by name
    public static function find_by_tag_name($tag_name) {
        global $database;
        $sql = "SELECT * FROM tags WHERE tag_name = ? LIMIT 1";
        $result_array = self::find_this_query($sql, [$tag_name]);
        return !empty($result_array) ? array_shift($result_array) : false;
    }

    public function save() {
        global $database;
        if (!empty($this->tag_name)) {
            $existing_tag = static::find_by_tag_name($this->tag_name);
            if (!$existing_tag) {
                // If the tag does not exist, insert it into the database.
                $sql = "INSERT INTO tags (tag_name) VALUES (?)";
                $params = [$this->tag_name];
                if ($database->query($sql, $params)) {
                    // On successful insert, set the ID of the newly created tag.
                    $this->id = $database->the_insert_id();
                    return true;
                } else {
                    // Handle insert failure (optional).
                    return false;
                }
            } else {
                // If the tag already exists, set this object's ID to the existing tag's ID.
                $this->id = $existing_tag->id;
                return true; // Return true because the tag effectively "exists" in the database.
            }
        }
        // If the tag name is empty, return false to indicate failure.
        return false;
    }


}
?>