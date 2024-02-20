<?php
class Tag extends Db_object {
    public $id;
    public $tag_name;

    protected static $table_name = "tags"; // The name of the database table used by this class
    public static function find_all_tags() {
        global $database;
        $tags = [];

        $sql = "SELECT * FROM tags";
        $result = $database->query($sql);

        while($row = $result->fetch_assoc()) {
            $tags[] = $row['tag_name'];
        }

        return $tags;
    }

    /**
     * Retrieves a list of tag names associated with a specific photo, identified by its ID.
     * This method performs a database query to fetch all tags linked to the given photo through a many-to-many relationship.
     *
     * Explanation:
     * - Tags are used to categorize or label photos with keywords, facilitating easier searching and organization.
     * - This method utilizes an INNER JOIN to query the relational table `photo_tags` and the `tags` table to fetch relevant tag names.
     * - It ensures that only tags associated with the specified photo ID are returned, enhancing data retrieval efficiency.
     *
     * Process:
     * 1. Prepare an SQL query that selects tag names (`tag_name`) from the `tags` table.
     * 2. The query includes an INNER JOIN with `photo_tags` to link tags with photos based on their IDs.
     * 3. The WHERE clause filters the results to include only tags associated with the specified `photo_id`.
     * 4. Execute the prepared query against the database, passing the `photo_id` to prevent SQL injection.
     * 5. Iterate over the query result set, collecting each tag name into the `tag_list` array.
     *
     * Importance:
     * - Facilitates dynamic categorization of photos within the application, allowing users to filter or search photos by tags.
     * - Enhances user experience by providing meaningful context and information about photos through tags.
     *
     * Usage:
     * This method is particularly useful in gallery or portfolio sections of a web application, where photos might be displayed alongside their tags.
     *
     * Example:
     * Assuming `$photo_id` represents the ID of a photo whose tags you want to retrieve:
     * ```php
     * $tags = Photo::find_tags_by_photo_id($photo_id);
     * foreach ($tags as $tag) {
     *     echo $tag; // Prints each tag name associated with the photo
     * }
     * ```
     *
     * Note:
     * - The `$photo_id` parameter must correspond to a valid photo ID in the database.
     * - The method returns an array of strings, each representing a tag name. If no tags are found, an empty array is returned.
     */
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

    /**
     * Searches for a tag in the database by its name and returns the tag object if found.
     * This method is designed to facilitate the retrieval of tag information based on a unique tag name,
     * ensuring that operations involving tags can be performed with accurate and specific data.
     *
     * Explanation:
     * - Tags are crucial for categorizing content such as photos, articles, or other entities within an application,
     *   allowing for efficient organization and retrieval.
     * - By performing a search based on tag name, this method ensures that actions such as tagging content or filtering
     *   by tags are based on existing, valid tag entities.
     * - Utilizes a parameterized query to prevent SQL injection, enhancing security when querying the database.
     *
     * Process:
     * 1. An SQL query is prepared, selecting all columns from the `tags` table where the `tag_name` matches the specified name.
     *    The query is limited to return only one result to ensure uniqueness of tag names.
     * 2. The method `find_this_query` is called with the prepared SQL string and the tag name as parameters. This custom method
     *    is assumed to execute the SQL query and return an array of objects representing the result set.
     * 3. The method checks if the result array is not empty, indicating a tag was found, and returns the first element of the array.
     *    If the array is empty (no tag found), the method returns false.
     *
     * Importance:
     * - Ensures that tags can be uniquely identified and retrieved based on their name, supporting features like tag management,
     *   content tagging, and tag-based filtering.
     * - Promotes data integrity by verifying the existence of tags before associating them with other content.
     *
     * Usage:
     * Ideal for scenarios where tag data needs to be fetched before performing operations that involve specific tags, such as
     * displaying tag-related information or validating tag existence during content creation or editing.
     *
     * Example:
     * ```php
     * $tagName = "Nature";
     * $tag = Photo::find_by_tag_name($tagName);
     * if ($tag) {
     *     echo "Tag ID: " . $tag->id; // Access properties of the tag object
     * } else {
     *     echo "Tag not found.";
     * }
     * ```
     *
     * Note:
     * - The tag name provided must be a string and is case-sensitive based on the database's collation settings.
     * - This method assumes the uniqueness of tag names within the database, as it only fetches and returns the first matching tag.
     */
    public static function find_by_tag_name($tag_name) {
        global $database;
        $sql = "SELECT * FROM tags WHERE tag_name = ? LIMIT 1";
        $result_array = self::find_this_query($sql, [$tag_name]);
        return !empty($result_array) ? array_shift($result_array) : false;
    }

    /**
     * Attempts to save a new tag to the database or recognizes an existing one.
     * This method is pivotal for ensuring that each tag in the database is unique and managing the tags effectively.
     *
     * Explanation:
     * - Tags are fundamental for categorizing items, such as photos or articles, within the application, facilitating organized retrieval and display.
     * - This method checks if a tag with the given name already exists in the database to prevent duplicate entries.
     * - If the tag does not exist, it is added to the database, thereby maintaining a streamlined and duplicate-free list of tags.
     * - The operation's success is reflected by updating the tag object's ID, either with the new tag's ID or the existing tag's ID, enhancing data integrity.
     *
     * Process:
     * 1. First, it checks if the tag's name property is not empty, ensuring that no blank tags are attempted to be saved.
     * 2. It looks for an existing tag with the same name using the `find_by_tag_name` method.
     * 3. If no existing tag is found, it proceeds to insert the new tag into the database and sets the object's ID to the newly inserted tag's ID.
     * 4. If a tag with the same name already exists, it sets the object's ID to that of the existing tag, treating the operation as successful.
     * 5. The method returns true if the tag was successfully inserted or recognized as existing; otherwise, it returns false.
     *
     * Importance:
     * - By ensuring the uniqueness of tags, this method supports efficient data management and operation within the application.
     * - It provides a straightforward way to add new tags or acknowledge existing ones, facilitating features like tagging content or filtering by tags.
     *
     * Usage:
     * This method is particularly useful when adding new tags to the system or associating tags with other items, ensuring that only valid, unique tags are used.
     *
     * Example:
     * ```php
     * $tag = new Tag();
     * $tag->tag_name = "New Tag";
     * if ($tag->save()) {
     *     echo "Tag saved successfully. Tag ID: " . $tag->id;
     * } else {
     *     echo "Failed to save tag.";
     * }
     * ```
     *
     * Note:
     * - The tag name must be a non-empty string to proceed with the save operation.
     * - This method promotes the integrity of tag data by avoiding duplicate tag entries and ensuring that tags are properly indexed in the database.
     */
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