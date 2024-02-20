<?php
// The Photo class extends Db_object to inherit database interaction functionalities.
// It is responsible for handling photo-related operations including file uploads,
// CRUD operations, and managing relationships with tags and categories.
class Photo extends Db_object {
    // Properties specific to the Photo entity
    public $id;
    public $title;
    public $description;
    public $filename; // Name of the uploaded file
    public $type; // MIME type of the uploaded file
    public $size; // Size of the uploaded file
    public $created_at; // Timestamp for when the file was created
    public $deleted_at; // Timestamp for soft deletion
    public $alternate_text = 'Default alternate text'; // Alternate text for the photo, for accessibility
    public $tags = []; // Array to hold associated tags
    public $categories = []; // Array to hold associated categories

    public $tmp_path; // Temporary path of the uploaded file before moving it to its final location
    public $upload_directory = "assets/images/photos"; // Directory where uploaded photos will be stored
    public $errors = array(); // Array to hold any errors during the file upload process
    public $upload_errors_array = array( // Associative array mapping PHP file upload error codes to human-readable messages
        UPLOAD_ERR_OK => "There is no error",
        UPLOAD_ERR_INI_SIZE => "The uploaded file exceeds the upload max_filesize directive in php.ini",
        UPLOAD_ERR_FORM_SIZE => "The uploaded file exceeds the MAX_FILE_SIZE directive specified in the HTML form",
        UPLOAD_ERR_NO_FILE => "No file was uploaded",
        UPLOAD_ERR_PARTIAL => "The uploaded file was only partially uploaded",
        UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder",
        UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk",
        UPLOAD_ERR_EXTENSION => "A PHP extension stopped the file upload",
    );

    protected static $table_name = "photos"; // The name of the database table used by this class

    /**
     * Retrieves all relevant properties of the object and returns them as an associative array.
     *
     * This method is crucial for operations that require interaction with the object's properties in a dynamic or abstract manner,
     * such as database operations (inserting or updating records) or when displaying object data in a view.
     *
     * How It Works:
     * - It directly accesses the object's properties, packaging them into an associative array where keys represent property names,
     *   and values are the corresponding property values.
     * - This enables easy iteration over object properties or direct access to specific properties in a standardized format.
     *
     * Usage Scenario:
     * - When saving an object to a database, `get_properties` can be used to dynamically construct SQL queries based on object properties.
     * - It can also be used to dynamically populate form fields in a web application, reflecting the current state of the object.
     *
     * Example Usage:
     * Assuming an instance `$photo` of a class that contains various properties like 'title', 'description', etc.,
     * calling `$photo->get_properties()` will return an array such as:
     * ```php
     * [
     *   'id' => 1,
     *   'title' => 'Sunset',
     *   'description' => 'A beautiful sunset...',
     *   'filename' => 'sunset.jpg',
     *   'type' => 'image/jpeg',
     *   'size' => 102400,
     *   'deleted_at' => 0000-0000-00.....,
     *   'alternate_text' => 'Sunset over the mountains'
     * ]
     * ```
     * This array can then be used to facilitate CRUD operations or to map object properties to UI components.
     *
     * Note: The method assumes all properties listed are relevant and should be included in the returned array.
     * If certain properties should be excluded (e.g., for security reasons), adjustments to the method may be necessary.
     */
    public function get_properties() {
        // Collects and returns the object's properties as an associative array.
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'filename' => $this->filename,
            'type' => $this->type,
            'size' => $this->size,
            'deleted_at' => $this->deleted_at,
            'alternate_text' => $this->alternate_text,
        ];
    }

    /**
     * Handles the file upload process for a photo object.
     * Validates the file's presence and properties, setting the object's attributes accordingly.
     *
     * Process Overview:
     * 1. Validates the uploaded file to ensure it meets the expected criteria (not empty, is an array, and has a name).
     * 2. Checks for any upload errors indicated by the file's error code.
     * 3. Generates a unique filename to prevent file overwrites and sets the object's file-related attributes.
     *
     * Steps Explained:
     * - Initial checks ensure the `$file` array is correctly structured and not empty, indicating a valid file upload attempt.
     * - The method assesses the file's error status, adding any error messages to the object's `errors` array if problems are detected.
     * - Assuming the file is valid and error-free, the method proceeds to generate a unique filename using the current date and time, ensuring file uniqueness.
     * - Finally, it sets the object's properties based on the file's characteristics: type, size, and temporary path.
     *
     * Usage:
     * This method is intended to be called when a photo is uploaded through a form, providing a streamlined way to handle file uploads and attribute assignment.
     *
     * Example:
     * ```php
     * $photo = new Photo();
     * $uploadError = $photo->set_file($_FILES['photo_upload']);
     * if (!$uploadError) {
     *     // Proceed with file processing, saving, etc.
     * }
     * ```
     *
     * Note:
     * - This method assumes a well-structured `$file` array typical of PHP's `$_FILES` superglobal.
     * - The generation of a unique filename is crucial for avoiding file collisions in the storage directory.
     * - Proper error handling is implemented to ensure robustness and ease of debugging.
     */
    public function set_file($file){
        // Check if the uploaded file is valid.
        // This involves several checks:
        // 1. If the file variable is empty.
        // 2. If the file variable is not an array (as expected from a file upload).
        // 3. If the file name is not set or is empty, indicating no file was chosen for upload.
        if(empty($file) || !$file || !is_array($file) || !$file['name']){
            $this->errors[] = "No file uploaded";
            return false;
        } elseif($file['error'] != 0){
            // Each file upload comes with an error code. A code of 0 means no error.
            // If there's any error code other than 0, it means an error occurred during file upload.
            // The specific error message is fetched from a predefined array using the error code as the key.
            $this->errors[] = $this->upload_errors_array[$file['error']];
            return false;
        } else {
            // If the file passes all checks, proceed to process and set the file properties.
            // Generate a unique file name to prevent overwriting existing files.
            // This is done by appending the current date and time to the original file name, ensuring uniqueness.
            $date = date('Y_m_d-H-i-s');
            $without_extension = pathinfo(basename($file['name']), PATHINFO_FILENAME);
            $extension = pathinfo(basename($file['name']), PATHINFO_EXTENSION);
            $this->filename = $without_extension . $date . '.' . $extension;

            // Set other properties of the photo object based on the uploaded file.
            // These include the file's MIME type, its size, and the temporary path where the file is stored.
            $this->type = $file['type']; // MIME type of the file, e.g., image/jpeg.
            $this->size = $file['size']; // Size of the file in bytes.
            $this->tmp_path = $file['tmp_name']; // The temporary location of the file on the server.
        }
    }

    /**
     * Manages the saving or updating of a photo record in the database.
     * This function encapsulates the logic for handling both new uploads and updates to existing photos.
     * It checks for the presence of a file upload and either creates a new record or updates an existing one.
     *
     * Process Overview:
     * 1. Validates if there's an attempt to upload a file by checking the temporary path and filename.
     * 2. Determines the target path for the uploaded file and checks for potential duplicates.
     * 3. Handles the physical file move from the temporary location to the target directory.
     * 4. Invokes the appropriate database operation (create or update) based on the photo's current state.
     *
     * Steps Explained:
     * - Initially, the method checks if there's an actual file to process by looking at the `tmp_path` and `filename`.
     * - If a file is being uploaded, the method constructs the target path and checks if a file with the same name already exists to avoid overwrites.
     * - The file is then physically moved to the target directory. Successful move triggers a database operation:
     *   - For new files, a `create` operation is invoked to insert a new record.
     *   - For existing files (determined by the presence of an `id`), an `update` operation is performed.
     * - In cases where there's no file upload but other attributes have changed, the `update` operation is directly invoked.
     * - The method returns `true` upon successful operation, indicating that the photo was successfully saved or updated.
     * - In case of failure at any step (e.g., file move failure, database operation failure), the method returns `false`.
     *
     * Usage:
     * This method is designed to be called when a photo is uploaded through a web form or when an existing photo record needs to be updated.
     *
     * Example:
     * ```php
     * $photo = new Photo();
     * $photo->set_file($_FILES['photo_upload']);
     * if ($photo->save()) {
     *     echo "Photo successfully saved or updated.";
     * } else {
     *     echo "An error occurred.";
     * }
     * ```
     *
     * Note:
     * - The `optimize_image` call before creating a new record suggests additional processing (e.g., resizing or compression) to optimize the photo for web use.
     * - Proper error handling and feedback are crucial, as indicated by the use of the `errors` array to collect and report issues encountered during the process.
     */
    public function save(){
        // Check if a file upload is attempted by looking at tmp_path and ensuring a file name is provided.
        if (!empty($this->tmp_path) && !empty($this->filename) && file_exists($this->tmp_path)) {
            // Construct the target path where the uploaded file should be saved.
            $target_path = SITE_ROOT . DS . "admin" . DS . $this->upload_directory . DS . $this->filename;

            // Check if the photo object already has an ID, indicating it's an existing record that needs updating.
            if ($this->id) {
                // Update the existing photo record in the database.
                $this->update();
            } else {
                // Ensure that no file with the same name already exists in the target path.
                if (file_exists($target_path)) {
                    $this->errors[] = "The file {$this->filename} already exists.";
                    return false;
                }

                // Move the uploaded file to the target path.
                if (move_uploaded_file($this->tmp_path, $target_path)) {
                    $this->optimize_image($target_path);
                    // Create a new photo record in the database.
                    if ($this->create()) {
                        unset($this->tmp_path); // Clear the temporary path property.
                        return true; // Indicate success.
                    }
                } else {
                    // Record an error if the file could not be moved.
                    $this->errors[] = "The folder likely does not have write permissions.";
                    return false;
                }
            }
        } else {
            // If no new file is being uploaded, but other attributes may need updating.
            if ($this->id) {
                // Attempt to update the record without composer require league/oauth2-facebook handling file upload.
                return $this->update();
            }
        }

        // If execution reaches this point without returning, it indicates failure.
        return false;
    }

    /**
     * Orchestrates the saving process of a photo along with its associated tags and categories.
     * This comprehensive method ensures that the photo, its tags, and categories are consistently saved,
     * which is crucial for maintaining data integrity and relations within the application.
     *
     * Steps Explained:
     * 1. Attempts to save the photo first using the `save` method, which handles both new uploads and updates.
     *    - This is critical as tags and categories should only be linked to successfully saved photos.
     * 2. If the photo save operation is successful, it proceeds to save associated tags and categories.
     *    - Tags and categories are meant to add additional metadata and organizational structure to photos.
     * 3. It calls separate methods (`save_tags` and `save_categories`) to handle the saving of tags and categories.
     *    - These methods manage the creation of new tags/categories and the linking of existing ones to the photo.
     * 4. Returns `true` if all operations (photo, tags, categories) succeed, indicating a successful save operation.
     *
     * Process Overview:
     * - The method starts by ensuring the photo itself is saved or updated correctly.
     * - It then checks for any assigned tags or categories to the photo object and proceeds to save these, linking them appropriately.
     * - The operation is considered successful only if the photo and all its related data (tags, categories) are saved correctly.
     *
     * Importance:
     * - This method is central to managing photo uploads as it ensures all related data is saved in a single, atomic operation.
     * - It enhances data consistency by ensuring that photos are not saved without their related tags and categories if applicable.
     * - By encapsulating the saving of photos, tags, and categories into one method, it simplifies the process and reduces the risk of partial data saves.
     *
     * Usage Example:
     * ```php
     * $photo = new Photo();
     * $photo->set_file($_FILES['photo_upload']);
     * $photo->tags = ['Nature', 'Landscape'];
     * $photo->categories = ['Outdoor'];
     * if ($photo->save_all()) {
     *     echo "Photo, tags, and categories successfully saved.";
     * } else {
     *     echo "An error occurred while saving.";
     * }
     * ```
     *
     * Note:
     * - The `save_all` method is a higher-level operation that leverages other methods (`save`, `save_tags`, `save_categories`) to perform its task.
     * - Proper error handling within `save_tags` and `save_categories` is assumed to ensure that any issues in these processes are appropriately managed.
     */
    public function save_all() {
        // Attempt to save the photo information first.
        // The save() method is responsible for handling both new photo uploads and updates to existing photos.
        // It deals with moving the uploaded file to a permanent location and saving photo metadata to the database.
        if(!$this->save()) { // If saving the photo fails, return false to indicate the overall operation failed.
            return false; // This early return stops further execution if the photo cannot be saved, ensuring data integrity. Meaning that if the photo is not uploaded the tags and categories will not be added so we make sure we don't have partial data in the database that is not linked with nothing.
        }

        // After the photo is successfully saved, proceed to save associated tags.
        // Tags are meant to categorize or describe the photo and are stored separately but linked to the photo.
        if(!empty($this->tags)) { // Check if the photo object has any tags assigned to it.
            $this->save_tags(); // Call the save_tags() method to process and save these tags.
            // The save_tags() method handles both the creation of new tags and linking them to the photo in a many-to-many relationship.
        }

        // Similar to tags, proceed to save associated categories.
        // Categories serve as a broader classification of photos.
        if(!empty($this->categories)) { // Check if there are categories assigned to the photo.
            $this->save_categories(); // Call the save_categories() method to process and save these categories.
            // The save_categories() method ensures that each category is stored and properly linked to the photo.
        }

        return true; // After successfully saving the photo, tags, and categories, return true to indicate success.
        // This method ensures that the photo and all its relational data are saved as a complete unit,
        // enhancing data consistency and integrity within the application.
    }



    /*METHODS DEALING WITH TAGS*/
    /*SPECIFIC FOR WHEN ADDING A NEW IMAGE WITH TAGS*/
    /**
     * Handles the association and management of tags linked to a specific photo. It ensures
     * that all tags assigned to a photo are saved in the database and properly linked to the
     * photo, thereby facilitating accurate categorization and searchability of photos based on tags.
     *
     * Steps Explained:
     * 1. Starts by clearing any existing tags associated with the photo to prevent duplicates and manage tag removals.
     * 2. Iterates through each tag assigned to the photo, performing operations for each tag:
     *    a. Checks if the tag already exists in the database.
     *    b. Saves new tags to the database.
     *    c. Links the photo to the tag in a many-to-many relationship table.
     * 3. For each tag, the process involves creating or fetching the tag and then linking it to the photo, ensuring
     *    that the photo's tags are always current and reflect the tags assigned in the application.
     *
     * Process Overview:
     * - Begins with a cleanup step by clearing existing tags to ensure that only current tags are linked.
     * - Each tag is then processed individually. If a tag does not exist, it is created; otherwise, the existing tag is used.
     * - Finally, the photo is linked to each tag, ensuring a proper association between the photo and its tags in the database.
     *
     * Importance:
     * - Essential for maintaining an accurate and up-to-date association of tags with photos, which is crucial for organizing
     *   and retrieving photos based on thematic categories or keywords.
     * - Facilitates dynamic tag management by allowing tags to be added, removed, or updated as needed without leaving orphaned
     *   tags or incorrect associations.
     *
     * Usage Example:
     * ```php
     * $photo = new Photo();
     * $photo->id = $photo_id; // Assume $photo_id is the ID of a photo that was previously saved.
     * $photo->tags = ['Sunset', 'Beach']; // Tags to be associated with the photo.
     * $photo->save_tags(); // Saves the tags and links them to the photo.
     * ```
     *
     * Note:
     * - This method assumes the existence of a `Tag` class with a `save` method that either creates a new tag or
     *   returns an existing one, and a `link_tag` method that establishes the many-to-many relationship between
     *   photos and tags.
     * - Proper error handling and validation should be implemented within the `Tag` class's `save` method and
     *   the `link_tag` method to ensure robustness and data integrity.
     */
    protected function save_tags() {
        global $database; // Access global database object for operations.

        $photo_id = $this->id; // Use the photo's ID for linking.

        $this->clear_tags(); // Clear current tags to manage updates or removals.

        foreach ($this->tags as $tag_name) {
            $tag = new Tag();
            $tag->tag_name = $tag_name;

            if($tag->save()) { // Save new or fetch existing tag.
                $this->link_tag($tag->id); // Link photo to tag.
            }
            // Handle failure to save a tag if necessary.
        }
    }


    /**
     * Clears all existing tag associations for a specific photo. This method is essential in the process of updating
     * a photo's tags to ensure that only the current set of tags is associated with the photo, effectively managing
     * tag updates and removals.
     *
     * Steps Explained:
     * 1. Constructs an SQL statement to delete all entries in the 'photo_tags' relationship table for the given photo ID.
     * 2. Executes the SQL statement against the database, using the photo's ID as a parameter to specify which photo's
     *    tags are to be cleared.
     *
     * Process Overview:
     * - By executing a DELETE SQL operation on the 'photo_tags' table, this method removes all existing associations
     *   between the photo and its tags.
     * - This operation is typically performed before re-associating the photo with a new set of tags, allowing for
     *   the removal of old tags and the addition of new ones without causing duplicates or retaining unwanted tags.
     *
     * Importance:
     * - Essential for maintaining accurate and up-to-date tag associations for photos, especially when tags are added,
     *   removed, or updated.
     * - Helps prevent duplication of tag associations and ensures that removed tags are no longer linked to the photo,
     *   keeping the database clean and reflective of the current state of photo-tag associations.
     *
     * Usage Example:
     * ```php
     * $photo = new Photo();
     * $photo->id = $photo_id; // Assume $photo_id is an existing photo's ID.
     * $photo->clear_tags(); // Clears all existing tags associated with the photo.
     * // After clearing, tags can be re-associated with the photo as needed.
     * ```
     *
     * Note:
     * - This method directly modifies the database by removing records from the 'photo_tags' table.
     *   It should be used with caution to avoid unintentional data loss.
     * - Ensure that the photo ID passed to this method is valid and that the corresponding photo exists
     *   in the database to prevent SQL errors or unintended effects.
     */
    protected function clear_tags() {
        global $database; // Access global database object for operations.

        $sql = "DELETE FROM photo_tags WHERE photo_id = ?"; // SQL to clear tags for a photo.
        $database->query($sql, [$this->id]); // Execute with the photo's ID.
    }

    /**
     * Establishes a relationship between a specific photo and a tag in the database by creating an entry in the
     * 'photo_tags' relationship table. This method is crucial for maintaining the many-to-many relationship between
     * photos and tags, allowing photos to be categorized and searched by tags.
     *
     * Steps Explained:
     * 1. Verifies that the provided tag ID is not null to prevent database errors.
     * 2. Prepares an SQL query to check if the relationship between the photo and the tag already exists in the
     *    'photo_tags' table.
     * 3. Executes the SQL query with the photo's ID and the tag's ID as parameters, ensuring the operation is secure
     *    and prevents SQL injection.
     * 4. If no existing relationship is found, inserts a new record into the 'photo_tags' table to link the photo
     *    with the tag.
     * 5. If the relationship already exists, takes no action, avoiding duplicate entries.
     *
     * Process Overview:
     * - The method first ensures the integrity of the input by checking for a null tag ID.
     * - It then checks the database to see if the specified photo-tag relationship already exists to prevent duplication.
     * - If the relationship does not exist, it creates a new entry in the 'photo_tags' table, effectively linking the
     *   photo with the tag.
     * - This process allows photos to be associated with multiple tags and tags to be associated with multiple photos,
     *   facilitating flexible categorization and retrieval based on tags.
     *
     * Importance:
     * - Essential for dynamically managing the categorization of photos through tags.
     * - Enables efficient organization and retrieval of photos based on associated tags, enhancing the user's ability
     *   to find relevant content.
     *
     * Usage Example:
     * ```php
     * $photo = new Photo();
     * $photo->id = $photo_id; // Assume $photo_id is an existing photo's ID.
     * $tag_id = 1; // Assume this is a valid tag ID.
     * $photo->link_tag($tag_id); // Links the photo with the tag in the database.
     * ```
     *
     * Note:
     * - This method directly modifies the database by inserting into the 'photo_tags' table. Ensure that the photo and
     *   tag IDs are valid and that both the photo and tag exist in the database before calling this method to avoid
     *   referential integrity issues.
     * - It's designed to be used within the context of managing the photo-tag relationship, often called after new tags
     *   are added to a photo or when a photo is first created or updated.
     */
    protected function link_tag($tag_id) {
        global $database; // Access global database object for operations.

        if ($tag_id === null) {
            error_log("Attempted to link photo ID {$this->id} with a null tag ID.");
            return; // Prevents linking a photo to a non-existing tag.
        }

        // Checks for an existing link between the photo and tag.
        $sql = "SELECT * FROM photo_tags WHERE photo_id = ? AND tag_id = ?";
        $params = [$this->id, $tag_id];
        $result = $database->query($sql, $params);

        // Inserts a new link if it doesn't exist.
        if ($result->num_rows == 0) {
            $sql = "INSERT INTO photo_tags (photo_id, tag_id) VALUES (?, ?)";
            $database->query($sql, $params);
        }
        // Existing relationships are left unchanged to avoid duplication.
    }

    /**
     * Retrieves all photos associated with a given tag name from the database.
     * This method is a critical component for implementing tag-based photo filtering within the application,
     * allowing users to find photos by tags.
     *
     * Steps Explained:
     * 1. Initializes an empty array to store the Photo objects matching the given tag.
     * 2. Constructs an SQL query that joins the 'photos', 'photo_tags', and 'tags' tables.
     *    This query selects all photos that are linked to the specified tag name and are not marked as deleted.
     * 3. Executes the SQL query with the tag name as a parameter to ensure secure database interaction.
     * 4. Iterates through the query result set, instantiating a new Photo object for each row fetched and adding it to the photo list.
     * 5. Returns the compiled list of Photo objects.
     *
     * Process Overview:
     * - Verifies the tag name against the database to find all associated photos, ensuring data is fetched securely through parameterized queries.
     * - Dynamically creates Photo objects based on database records, enabling object-oriented manipulation of photo data.
     * - Filters out deleted photos to ensure only accessible photos are included in the result.
     *
     * Importance:
     * - Facilitates tag-based photo discovery, enhancing user experience by allowing for easy navigation and filtering of photos based on interests or categories.
     * - Supports the application's content organization and retrieval capabilities by leveraging relational database structures.
     *
     * Usage Example:
     * ```php
     * $tag_name = "Nature"; // Tag name to filter photos by
     * $photos = Photo::find_by_tag($tag_name); // Retrieves all photos associated with the "Nature" tag
     * foreach ($photos as $photo) {
     *     // Display or process each photo as needed
     * }
     * ```
     *
     * Note:
     * - This method assumes the presence of a relational structure between photos and tags in the database,
     *   requiring a many-to-many relationship facilitated by a 'photo_tags' linking table.
     * - It's designed to return an array of Photo objects, allowing further actions such as display in a user interface
     *   or additional filtering and processing.
     */
    public static function find_by_tag($tag_name) {
        global $database; // Access global database object.

        $photo_list = []; // Array to store matching photos.

        // SQL query joining photos, photo_tags, and tags tables to find matching photos.
        $sql = "SELECT p.* FROM photos p
        INNER JOIN photo_tags pt ON p.id = pt.photo_id
        INNER JOIN tags t ON pt.tag_id = t.id
        WHERE t.tag_name = ? AND (p.deleted_at IS NULL OR p.deleted_at = '0000-00-00 00:00:00')";

        // Execute the query securely with the tag name as parameter.
        $result = $database->query($sql, [$tag_name]);

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $photo_list[] = static::instantie($row); // Instantiate Photo object for each row.
            }
        }

        return $photo_list; // Return list of Photo objects associated with the tag.
    }

    /*TAGS - SPECIFIC FOR THE UPDATE OF A TAG WHEN EDITING EXISTING PHOTO*/

    /**
     * A public method designed to facilitate the association of a tag with a photo. This method acts as a wrapper
     * around the `link_tag` method, streamlining the process of linking a tag to a photo from external code.
     *
     * Explanation:
     * - This method simplifies the interface for adding a tag to a photo, making the code more readable and maintainable.
     * - It directly utilizes the `link_tag` method, which contains the logic for creating a relationship in the database
     *   between a photo and a tag.
     * - By abstracting the direct call to `link_tag`, this method allows for future enhancements or changes in the
     *   linking process without affecting the external code that adds tags to photos.
     *
     * Process:
     * 1. Receives a tag ID as its parameter. This ID should correspond to an existing tag in the database.
     * 2. Calls the `link_tag` method with the provided tag ID, which performs the actual operation of linking the tag
     *    with the photo in the database.
     *
     * Importance:
     * - Enhances code readability and usability by providing a clear and straightforward way to add tags to photos.
     * - Facilitates code maintenance and scalability by encapsulating the tagging logic within the `link_tag` method.
     *
     * Usage:
     * Assuming you have an instance of a Photo object and a valid tag ID:
     * ```php
     * $photo = new Photo(); // Assume this is an initialized and saved Photo object.
     * $tagId = 1; // Example tag ID that you want to link to the photo.
     * $photo->addTag($tagId); // This will link the tag with ID 1 to the photo.
     * ```
     *
     * Note:
     * - The tag ID provided must correspond to an existing tag in the database. The method assumes that the tag ID is valid
     *   and does not perform any validation itself.
     * - This method is particularly useful in contexts where photos are being tagged with predefined tags, such as in
     *   a photo management or categorization feature.
     */
    public function addTag($tag_id) {
        $this->link_tag($tag_id); // Delegate the linking operation to the `link_tag` method.
    }


    /**
     * This static method ensures the existence of a tag in the database, either by finding an existing tag with the given name or creating a new one. It then returns the ID of the tag, whether newly created or pre-existing. This functionality is crucial for maintaining a consistent and non-redundant set of tags within the application, facilitating organized and efficient tag management.
     *
     * Explanation:
     * - The method simplifies the process of associating tags with photos by ensuring that any given tag name is represented by a single, unique record in the database.
     * - It employs a check-and-act approach: first, it searches for an existing tag by name; if not found, it proceeds to create a new tag.
     * - This approach prevents duplicate tags, ensuring that each tag name is unique within the database.
     *
     * Process:
     * 1. Attempts to find an existing tag by the given name using `Tag::find_by_tag_name`, which searches the database for a tag matching the specified name.
     * 2. If the tag does not exist (indicated by `find_by_tag_name` returning null), a new `Tag` object is instantiated and assigned the provided tag name.
     * 3. The new tag's name is sanitized using `escape_string` to prevent SQL injection, and the tag is saved to the database.
     * 4. If the tag is successfully saved, its database-generated ID is returned. If the save operation fails, an error is logged, and null is returned to indicate failure.
     * 5. If an existing tag is found, its ID is returned, bypassing the need to create a duplicate tag.
     *
     * Importance:
     * - By ensuring that each tag name corresponds to a single database record, this method plays a vital role in maintaining data integrity and preventing tag duplication.
     * - It facilitates the tagging process, allowing tags to be easily and reliably associated with photos without manual verification of tag existence or uniqueness.
     *
     * Usage:
     * This method is called whenever a tag needs to be associated with a photo, especially in scenarios where the tag's prior existence in the database is uncertain.
     *
     * Example:
     * ```php
     * $tagName = "Landscape"; // The name of the tag to ensure exists
     * $tagId = Tag::ensureTag($tagName); // This will either find the existing "Landscape" tag or create a new one and return its ID
     * if ($tagId) {
     *     // Proceed to link the tag with a photo or handle the tag as needed
     * } else {
     *     // Handle the error scenario where the tag could not be ensured
     * }
     * ```
     *
     * Note:
     * - This method abstracts away the complexities of tag management, providing a straightforward interface for ensuring tag existence and obtaining tag IDs.
     */
    public static function ensureTag($tagName) {
        global $database;
        $tag = Tag::find_by_tag_name($tagName);
        if (!$tag) {
            // Tag does not exist, create it
            $tag = new Tag();
            $tag->tag_name = $database->escape_string($tagName);
            if($tag->save()) { // Ensure save returns true, indicating the tag was successfully inserted
                return $tag->id; // After a successful save, $tag->id should be the new tag ID
            } else {
                // Handle failure: perhaps log an error or throw an exception
                error_log("Failed to create new tag: $tagName");
                return null; // Or handle this scenario appropriately
            }
        }
        return $tag->id; // Return the existing tag's ID
    }


    /**
     * This method synchronizes the tags associated with a photo based on user input, typically from an edit form. It ensures that the photo is linked to all and only the tags specified in the input, adding new links as necessary and removing outdated ones. This process involves comparing the current set of tags linked to the photo with the set of tags provided by the user, then updating the photo's tags to reflect the user's input accurately.
     *
     * Explanation:
     * - It begins by converting the input string of tags into an array, separating the tags based on commas and trimming any whitespace from each tag name.
     * - The method then retrieves the current set of tags associated with the photo from the database.
     * - By comparing the current tags with the input tags, it identifies which tags need to be added to or removed from the photo.
     * - New tags are added to the photo by ensuring their existence in the database (creating them if necessary) and then linking them to the photo.
     * - Tags no longer included in the input are unlinked from the photo, effectively removing the association.
     *
     * Process:
     * 1. Parse the input string of tags into an array of individual, trimmed tag names.
     * 2. Fetch the list of tags currently associated with the photo.
     * 3. Identify the differences between the current tags and the input tags to determine which tags need to be added or removed.
     * 4. For each tag to be added, ensure its existence in the database and link it to the photo.
     * 5. For each tag to be removed, unlink it from the photo.
     *
     * Importance:
     * - This method plays a crucial role in maintaining accurate and up-to-date associations between photos and tags, reflecting changes made by users through the UI.
     * - It ensures the flexibility of tag management, allowing tags to be dynamically added or removed from photos based on user interaction.
     *
     * Usage:
     * This method is typically called when processing user input from a photo edit form where tags can be added or removed.
     *
     * Example:
     * ```php
     * $photo = Photo::find_by_id($photoId); // Assume $photoId is the ID of the photo being edited
     * $inputTags = "Nature, Landscape, Sunset"; // Example user input from a form field
     * $photo->syncTags($inputTags); // Synchronize the photo's tags based on the input
     * ```
     *
     * Note:
     * - This method assumes that the inputTags string is a comma-separated list of tag names.
     * - It handles both the creation of new tags (if they don't already exist) and the cleaning up of associations that are no longer relevant.
     */
    public function syncTags($inputTags) {
        global $database;

        // Convert input tags to an array of trimmed strings
        $inputTags = array_map('trim', explode(',', $inputTags));

        // Get current tags associated with this photo
        $currentTags = Tag::find_tags_by_photo_id($this->id);

        // Determine tags to add and remove
        $tagsToAdd = array_diff($inputTags, $currentTags);
        $tagsToRemove = array_diff($currentTags, $inputTags);

        // Add new tags
        foreach ($tagsToAdd as $tagName) {
            $tagId = $this->ensureTag($tagName);
            $this->addTag($tagId);
        }

        // Remove unselected tags
        foreach ($tagsToRemove as $tagName) {
            $tag = Tag::find_by_tag_name($tagName);
            if ($tag) {
                $this->removeTag($tag->id);
            }
        }
    }

    /**
     * This method is responsible for removing the association between a photo and a specific tag in the database. It effectively unlinks a tag from a photo by deleting the corresponding record from the `photo_tags` table, which maintains the many-to-many relationship between photos and tags.
     *
     * Explanation:
     * - The `photo_tags` table contains records that link photos to tags using their respective IDs. Each record represents a tagging relationship.
     * - By deleting a record from this table, the method removes the association, effectively "untagging" the photo with the specified tag.
     * - This operation is crucial for maintaining accurate tag associations, especially when tags need to be removed from photos, either as part of user-driven edits or during automated processes.
     *
     * Process:
     * 1. Prepare an SQL DELETE statement targeting the `photo_tags` table, specifying conditions to match both the photo ID and the tag ID.
     * 2. Execute the query with the current photo's ID and the tag ID to be removed as parameters, ensuring that only the relationship between this specific photo and tag is affected.
     * 3. The deletion operation does not return a result set but affects the database by removing the specified record.
     *
     * Importance:
     * - Allows for dynamic management of photo-tag relationships, ensuring that photos can be accurately tagged or untagged based on user actions or other criteria.
     * - Supports the integrity of tagging data by ensuring that only relevant tags are associated with each photo.
     *
     * Usage:
     * This method is typically called in the context of editing a photo's tags, where a user or process determines that a tag no longer applies to the photo.
     *
     * Example:
     * ```php
     * $photo = Photo::find_by_id($photoId); // Assume $photoId is the ID of the photo being edited
     * $tagIdToRemove = 5; // Example tag ID that needs to be removed from the photo
     * $photo->removeTag($tagIdToRemove); // Removes the association between the photo and the tag with ID 5
     * ```
     *
     * Note:
     * - This method is private and intended to be used internally by the Photo class, typically as part of methods that manage a photo's overall tag associations.
     * - Care should be taken to ensure the tag ID provided actually corresponds to a tag associated with the photo to avoid unnecessary database operations.
     */
    private function removeTag($tagId) {
        global $database;
        $sql = "DELETE FROM photo_tags WHERE photo_id = ? AND tag_id = ?";
        $database->query($sql, [$this->id, $tagId]);
    }


    /*METHODS DEALING WITH CATEGORIES*/
    /*SPECIFIC FOR WHEN ADDING A NEW IMAGE WITH TAGS*/
    /**
     * This protected method is tasked with associating a photo with its respective categories within the database.
     * It ensures a seamless linkage between a photo and one or multiple categories, facilitating categorization
     * and subsequent retrieval based on category criteria. If necessary, new categories are created to maintain
     * the integrity and comprehensiveness of the categorization system.
     *
     * Explanation:
     * - Initiates by clearing any pre-existing associations between the photo and categories to prevent duplicates
     *   and accurately reflect current categorizations.
     * - Iterates over each category assigned to the photo, verifying the existence of each category within the database,
     *   thereby ensuring that only valid categories are linked to the photo.
     * - Utilizes the `ensure_category` method, which checks for the existence of a category or creates it if absent,
     *   and then retrieves its ID for linkage.
     * - Calls the `link_category` method to establish a relationship in the database between the photo and each category,
     *   identified by their respective IDs.
     * - Handles cases where category IDs are not retrievable, indicating an issue with category creation or retrieval.
     *   This scenario allows for error handling or logging to ensure robust application behavior.
     *
     * Process:
     * 1. Clears existing links between the photo and categories to ensure a fresh start for categorization.
     * 2. Iterates through the assigned categories, ensuring each exists in the database, and obtains their IDs.
     * 3. For each category, establishes a link between the photo and the category using the retrieved ID.
     *
     * Importance:
     * - Enhances the organizational structure of photos by enabling categorization, which aids in efficient photo management and retrieval.
     * - Ensures data integrity by linking photos only to existing categories or by creating new categories as needed.
     * - Facilitates the dynamic association of photos with categories, supporting the flexible categorization of photos as required.
     *
     * Usage:
     * This method is typically called within the context of saving or updating a photo's information, where categorization
     * is a component of the photo's metadata. It abstracts the complexity of managing photo-category relationships,
     * allowing developers to focus on higher-level functionality.
     *
     * Note:
     * - The method assumes that the `categories` property of the photo object is populated with the desired categories
     *   before invocation.
     * - It's crucial that the `ensure_category` and `link_category` methods are correctly implemented and handle all
     *   edge cases, including error logging and handling, to ensure the reliable execution of this method.
     */
    protected function save_categories() {
        global $database;

        // Clear any existing associations to ensure a clean state for new category links.
        $this->clear_categories();

        // Iterate through each category assigned to the photo.
        foreach ($this->categories as $category_name) {
            // Verify or create the category in the database, obtaining its ID.
            $category_id = Category::ensure_category($category_name);

            // If the category ID is successfully retrieved, establish a link with the photo.
            if($category_id) {
                $this->link_category($category_id);
            }
            // Optionally, handle cases where the category ID could not be obtained.
        }
    }


    /**
     * This protected method is responsible for removing all existing associations between a specific photo and its categories in the database.
     * It ensures that a photo's categorization can be updated accurately by clearing out old category links before establishing new ones.
     * This step is crucial for maintaining the integrity of the photo's categorization data, particularly in scenarios where photo
     * categories are being updated or changed.
     *
     * Explanation:
     * - The method operates by executing a DELETE SQL statement on the `photo_categories` table, targeting rows where the `photo_id` matches
     *   the ID of the current photo object. This effectively removes all records linking the photo to any categories, ensuring a clean slate
     *   for re-categorization.
     * - It leverages the global `$database` object for database interaction, utilizing a prepared statement to securely execute the deletion
     *   based on the photo's ID.
     * - This approach prevents potential data inconsistency by ensuring that a photo's categorization is always a current and accurate reflection
     *   of the desired state, free from remnants of previous categorizations.
     *
     * Process:
     * 1. Constructs a DELETE SQL statement targeting the `photo_categories` table where `photo_id` matches the current photo's ID.
     * 2. Executes the SQL statement using the `$database->query` method, passing the photo's ID as a parameter to ensure precise targeting.
     *
     * Importance:
     * - Facilitates accurate updates to a photo's categories by preventing the accumulation of outdated or incorrect category associations.
     * - Enhances data integrity within the database by ensuring that changes to photo categorizations are reflected cleanly and accurately.
     * - Supports dynamic categorization workflows, allowing for the flexible management of photo categorizations as part of content management or
     *   gallery organization features.
     *
     * Usage:
     * This method is internally called as part of the process of updating a photo's category associations, typically before establishing new links
     * between the photo and its updated set of categories.
     *
     * Note:
     * - This method assumes that it is called in the context of a photo object with a valid ID, and that the global `$database` object is properly
     *   configured for database interaction.
     * - Care should be taken to ensure that this method is called appropriately within workflows that modify photo categorizations to prevent
     *   unintended data loss.
     */
    protected function clear_categories() {
        global $database;
        // Prepare the SQL statement to delete all category links for this photo.
        $sql = "DELETE FROM photo_categories WHERE photo_id = ?";
        // Execute the query, passing the photo's ID to target the correct links for deletion.
        $database->query($sql, [$this->id]);
    }


    /**
     * Establishes a linkage between a photo and a category in the database, ensuring the photo is correctly categorized. This method
     * is designed to create a new association in the `photo_categories` table unless an identical association already exists.
     *
     * Explanation:
     * - This method aims to maintain the integrity and accuracy of the categorization data by preventing duplicate entries. It ensures that each
     *   category association for a photo is unique.
     * - It checks for the existence of a category link for the photo before attempting to create a new one. This pre-check avoids unnecessary
     *   insertions and maintains database cleanliness.
     * - Utilizes the global `$database` object to execute SQL queries, ensuring secure and efficient database interactions through prepared
     *   statements and parameter binding.
     *
     * Process:
     * 1. Validates the provided `category_id` to ensure it is not null, avoiding potential database errors.
     * 2. Queries the `photo_categories` table to check for an existing link between the photo and the category using the provided `category_id`.
     * 3. If no existing link is found, inserts a new record into `photo_categories` to establish the link.
     * 4. Ignores the insertion if an identical link already exists, maintaining data integrity without redundancy.
     *
     * Importance:
     * - Critical for accurately reflecting a photo's categorization within the application, facilitating features like category-based photo filtering
     *   and organization.
     * - Enhances data integrity by ensuring that categorization data remains precise and free from duplicate associations.
     * - Supports dynamic categorization functionality, allowing photos to be associated with categories as part of content management workflows.
     *
     * Usage:
     * Typically invoked as part of the process of categorizing a photo, either when a new photo is added to the system or when existing photos are
     * recategorized.
     *
     * Note:
     * - Assumes the existence of a valid `category_id` and a properly configured global `$database` object for SQL execution.
     * - This method should be called with care, particularly in automated scripts or batch operations, to ensure that category associations remain
     *   meaningful and accurate.
     */
    protected function link_category($category_id) {
        global $database; // Access the global database object for executing SQL queries.

        // First, ensure that the $category_id is valid to prevent the "Column 'category_id' cannot be null" error.
        if ($category_id === null) {
            error_log("Attempted to link photo ID {$this->id} with a null category ID.");
            return; // Exit the function if $category_id is null.
        }

        // Prepare an SQL query to check if a link already exists between the current photo and the specified category.
        $sql = "SELECT * FROM photo_categories WHERE photo_id = ? AND category_id = ?";
        $params = [$this->id, $category_id]; // Define parameters for the query.

        // Execute the query with the specified parameters to check for existing links.
        $result = $database->query($sql, $params);

        // If no existing link is found (indicated by num_rows being 0), insert a new record.
        if ($result->num_rows == 0) {
            $sql = "INSERT INTO photo_categories (photo_id, category_id) VALUES (?, ?)";
            $database->query($sql, $params);
        }
        // No action required if the relationship already exists.
    }



    /**
     * Retrieves all photos associated with a specific category by category name.
     * This method enables the categorization feature within the application, allowing users to view photos
     * filtered by categories.
     *
     * Explanation:
     * - Utilizes the global `$database` object to execute a complex SQL query that involves joining tables.
     * - The method filters photos based on the specified category name, allowing for a dynamic and user-friendly
     *   way to organize and access photos.
     * - Promotes modular and reusable code by abstracting the database query logic into a single method.
     *
     * Process:
     * 1. Constructs an SQL query that selects all columns from the `photos` table and joins it with `photo_categories`
     *    and `categories` tables to filter photos belonging to the specified category.
     * 2. Executes the query using the provided category name as a parameter, ensuring the operation is safe from SQL injection.
     * 3. Iterates through the result set, creating a new Photo object for each row and collecting these objects in an array.
     * 4. Returns the array of Photo objects to the caller, providing a structured way to access photos by category.
     *
     * Importance:
     * - Essential for implementing categorization functionality within the application, enhancing user experience by
     *   allowing photos to be organized and displayed according to their categories.
     * - Supports the dynamic nature of web applications by enabling content filtering based on user input or other criteria.
     *
     * Usage:
     * Can be called to retrieve photos for a specific category within UI components, such as gallery views or category pages.
     *
     * Example:
     * ```php
     * $categoryName = "Nature";
     * $photosInCategory = Photo::find_by_category($categoryName);
     * foreach ($photosInCategory as $photo) {
     *     // Display each photo or process further
     * }
     * ```
     *
     * Note:
     * - Assumes the existence of a well-structured database schema with appropriate relationships between photos and categories.
     * - Relies on the `instantie` method to correctly instantiate Photo objects from the raw database rows, linking object-oriented
     *   programming concepts with relational database management.
     */
    public static function find_by_category($category_name) {
        global $database; // Access the global database object for executing SQL queries.

        // SQL query to join photos with categories through the photo_categories table.
        $sql = "SELECT photos.* FROM photos ";
        $sql .= "INNER JOIN photo_categories ON photos.id = photo_categories.photo_id ";
        $sql .= "INNER JOIN categories ON photo_categories.category_id = categories.id ";
        $sql .= "WHERE categories.category_name = ? ";

        // Execute the query with parameter binding to prevent SQL injection.
        $result_set = $database->query($sql, [$category_name]);

        $object_array = []; // Initialize an empty array to hold the resulting Photo objects.
        while ($row = $result_set->fetch_assoc()) {
            // Instantiate a Photo object for each row and add it to the array.
            $object_array[] = static::instantie($row);
        }

        return $object_array; // Return the array of Photo objects associated with the specified category.
    }

    /*CATEGORY - SPECIFIC FOR THE UPDATE OF A CATEGORY WHEN EDITING EXISTING PHOTO*/

    /**
     * Facilitates the association of a specific category with the current photo by invoking the `link_category` method.
     * This method acts as a convenient interface for adding a category to a photo, abstracting the direct database operation.
     *
     * Explanation:
     * - In a photo management system, categorizing photos is essential for organization and retrieval. This method provides a straightforward way to associate a photo with a category.
     * - It uses the `link_category` method, which handles the database interaction required to create a link between the photo and the category.
     * - By offering this functionality through a public method, it allows other parts of the application to easily categorize photos without dealing with the underlying database logic.
     *
     * Process:
     * 1. Accepts a category ID as its single parameter. This ID should correspond to a category already existing in the database.
     * 2. Delegates the task of linking the photo to the category to the `link_category` method by passing along the category ID.
     *
     * Importance:
     * - Enhances code usability and readability by providing a clear method for adding categories to photos.
     * - Encapsulates the logic of category association within the `link_category` method, simplifying maintenance and potential modifications to the association logic.
     *
     * Usage:
     * Use this method when there is a need to programmatically assign a category to a photo, such as during photo upload or editing processes.
     *
     * Example:
     * ```php
     * $photo = new Photo(); // Assume this is an initialized and saved Photo object.
     * $categoryId = 5; // Example category ID to link to the photo.
     * $photo->addCategory($categoryId); // Associates the photo with the category ID 5.
     * ```
     *
     * Note:
     * - The method assumes the category ID is valid and corresponds to an existing category in the database. It does not perform any validation on the category ID itself.
     * - This abstraction allows for ease of use and flexibility in photo categorization, which can be particularly beneficial in interfaces allowing users to select categories for their photos.
     */
    public function addCategory($category_id) {
        $this->link_category($category_id); // Delegate the linking operation to the `link_category` method.
    }


    /**
     * This method ensures the existence of a category within the database by its name. If the category does not exist, it is created, and its ID is returned. If it already exists, its existing ID is returned. This functionality is crucial for maintaining the integrity and uniqueness of categories in a photo management or content management system.
     *
     * Explanation:
     * - Categories are a fundamental part of organizing content, allowing for efficient retrieval and categorization of photos or other entities. This method supports the dynamic handling of categories by checking for their existence before insertion, preventing duplicate entries.
     * - If a category name provided does not match any existing category, a new category is created with that name, ensuring that all categories used within the application are recorded in the database.
     * - The method uses parameterized queries for database interactions, enhancing security by preventing SQL injection attacks.
     *
     * Process:
     * 1. Check if a category with the provided name exists in the database.
     * 2. If the category does not exist, a new Category object is created, and its name is set to the provided name. The category name is sanitized to prevent SQL injection.
     * 3. The new category is saved to the database. If the save operation is successful, the method returns the new category's ID, indicating the category's successful insertion.
     * 4. If the category already exists, the method simply returns the ID of the existing category, avoiding duplicate entries.
     *
     * Importance:
     * - Prevents duplication of category names in the database, ensuring data integrity.
     * - Facilitates the dynamic creation and utilization of categories within the application, enhancing user experience and administrative capabilities.
     *
     * Usage:
     * This method is particularly useful in scenarios where categories are created or assigned dynamically, such as during the upload process of new content or when categorizing existing content.
     *
     * Example:
     * ```php
     * $categoryName = "Nature"; // The name of the category to ensure exists in the database.
     * $categoryId = Category::ensureCategory($categoryName);
     * if ($categoryId) {
     *     // The category either existed or has been created successfully.
     *     echo "Category ID: " . $categoryId;
     * } else {
     *     // Failed to ensure the category exists.
     *     echo "Failed to create or find the category.";
     * }
     * ```
     *
     * Note:
     * - This method abstracts away the complexity of handling category creation and retrieval, allowing developers to ensure the use of valid categories without direct database manipulation.
     * - It's essential to handle the possibility of null returns, indicating a failure in creating a new category, which could be due to database errors or constraints.
     */
    public static function ensureCategory($categoryName) {
        global $database;
        $category = Category::find_by_category_name($categoryName);
        if (!$category) {
            // Category does not exist, create it
            $category = new Category();
            $category->category_name = $database->escape_string($categoryName);
            if($category->save()) { // Ensure save returns true, indicating the category was successfully inserted
                return $category->id; // After a successful save, $category->id should be the new category ID
            } else {
                // Handle failure: perhaps log an error or throw an exception
                error_log("Failed to create new category: $categoryName");
                return null; // Or handle this scenario appropriately
            }
        }
        return $category->id; // Return the existing category's ID
    }


    /**
     * Synchronizes the categories associated with a photo based on a given list of category names. This method ensures that only the specified categories are linked to the photo, adding new links where necessary and removing any associations that are no longer applicable.
     *
     * Explanation:
     * - In content management systems, it's common to categorize content (like photos) for better organization and retrieval. This method facilitates dynamic management of such categorizations, reflecting changes in the photo's categories directly in the database.
     * - It first identifies which categories need to be added or removed based on the current state in the database versus the input provided by the user or application.
     * - The method ensures that all specified categories exist in the database, creating any that are missing, and then updates the photo's category associations accordingly.
     *
     * Process:
     * 1. The input category names are split into an array and sanitized to ensure accurate processing.
     * 2. The method retrieves the photo's current categories from the database for comparison.
     * 3. It then calculates which categories need to be added or removed to synchronize the photo's categories with the provided list.
     * 4. New categories are added by ensuring their existence in the database and then linking them to the photo. Unselected categories are removed from the photo's associations.
     *
     * Importance:
     * - Allows for flexible and dynamic categorization of photos, adapting as the categorization needs change over time.
     * - Enhances the integrity of the database by ensuring that category associations accurately reflect the current state intended by the user or application.
     *
     * Usage:
     * This method is particularly useful in administrative interfaces or scripts where photos are being re-categorized, either in bulk or individually.
     *
     * Example:
     * ```php
     * $photo = Photo::find_by_id(1); // Assuming the photo exists
     * $newCategories = "Nature,Landscape,Wildlife"; // A comma-separated list of new categories for the photo
     * $photo->syncCategories($newCategories); // Synchronize the photo's categories with the new list
     * ```
     *
     * Note:
     * - The method assumes that the input is a comma-separated string of category names, which is a common format for category input in web forms.
     * - Care should be taken to ensure that category names do not contain commas unless intended as separators.
     * - This method provides a comprehensive approach to managing photo categorization, combining the creation of new categories, the linking of existing ones, and the cleanup of outdated associations.
     */
    public function syncCategories($inputCategories) {
        global $database;

        // Convert input categories to an array of trimmed strings
        $inputCategories = array_map('trim', explode(',', $inputCategories));

        // Get current categories associated with this photo
        $currentCategories = Category::find_categories_by_photo_id($this->id);

        // Determine categories to add and remove
        $categoriesToAdd = array_diff($inputCategories, $currentCategories);
        $categoriesToRemove = array_diff($currentCategories, $inputCategories);

        // Add new categories
        foreach ($categoriesToAdd as $categoryName) {
            $categoryId = $this->ensureCategory($categoryName);
            $this->addCategory($categoryId);
        }

        // Remove unselected categories
        foreach ($categoriesToRemove as $categoryName) {
            $category = Category::find_by_category_name($categoryName);
            if ($category) {
                $this->removeCategory($category->id);
            }
        }
    }


    /**
     * A private method designed to dissociate a specific category from a photo by removing the linkage record in the database. This method is integral to maintaining the accuracy of photo categorizations, ensuring that a photo's associated categories reflect current categorization intents.
     *
     * Explanation:
     * - In content management systems, photos can be associated with multiple categories. This method facilitates the dynamic management of such associations by allowing for the removal of specific category linkages.
     * - It directly interacts with the database to remove the association between the photo and a given category, effectively "uncategorizing" the photo from that category.
     * - This method is particularly useful in scenarios where categories need to be updated or corrected for a given photo.
     *
     * Process:
     * 1. The method takes a category ID as its parameter, identifying the category to be removed from the photo's associations.
     * 2. An SQL DELETE query is constructed to remove the specific record linking the photo to the category in the `photo_categories` table.
     * 3. The query is executed against the database, and the linkage is removed, dissociating the photo from the specified category.
     *
     * Importance:
     * - Allows for the precise management of photo categorizations, supporting operations like category updates and corrections.
     * - Enhances data integrity by ensuring that photo-category associations in the database accurately reflect the desired state.
     *
     * Usage:
     * This method is typically called as part of larger operations that manage a photo's categories, such as during category synchronization or when manually editing a photo's categories.
     *
     * Example:
     * Assuming a scenario where a photo is mistakenly categorized and needs correction:
     * ```php
     * $photo = Photo::find_by_id(1); // Assume the photo exists
     * $wrongCategoryId = 5; // The ID of the category to be removed from the photo
     * $photo->removeCategory($wrongCategoryId); // Remove the wrong category association
     * ```
     *
     * Note:
     * - This method operates based on the assumption that category IDs are valid and correspond to actual categories in the database.
     * - Being a private method, it's intended for internal use within the class, typically called by public methods handling higher-level category synchronization or management tasks.
     */
    private function removeCategory($categoryId) {
        global $database;
        $sql = "DELETE FROM photo_categories WHERE photo_id = ? AND category_id = ?";
        $database->query($sql, [$this->id, $categoryId]);
    }



    /*OTHER METHODS*/
    /**
     * Generates the accessible path for a photo's file, considering both uploaded files and external URLs.
     * This method ensures the application can handle photo files stored locally as well as references to external images.
     *
     * Explanation:
     * - The method first checks if the filename starts with 'http://' or 'https://', identifying it as an external URL.
     * - For external URLs, the method returns the URL directly, allowing for external images to be used without alteration.
     * - For locally stored photo files, it constructs a path by appending the filename to the predefined upload directory.
     * - If the photo does not have a filename, it returns a placeholder image URL, ensuring the UI remains consistent.
     *
     * Process:
     * 1. Checks if the filename indicates an external URL. If so, returns the URL directly.
     * 2. If the filename is not an external URL and is not empty, constructs the file path using the upload directory.
     * 3. Returns a placeholder image URL if no filename is provided, maintaining user interface consistency.
     *
     * Importance:
     * - Enhances flexibility by supporting both externally hosted images and those uploaded to the local server.
     * - Provides a unified method to obtain the correct path for displaying images, simplifying front-end development.
     *
     * Usage:
     * Can be called to dynamically generate the source URL for image tags in HTML, ensuring photos are correctly displayed regardless of their storage location.
     *
     * Example:
     * ```php
     * $photo = new Photo();
     * $photo->filename = "example.jpg";
     * echo '<img src="' . $photo->picture_path() . '" alt="Photo">';
     * ```
     * This example would construct the path to an image stored in the local upload directory and use it as the source in an image tag.
     *
     * Note:
     * - The method assumes that the `upload_directory` property is correctly set to the path where uploaded photos are stored.
     * - For external images, it's crucial to ensure the URL is valid and accessible to prevent broken images in the application.
     */
    public function picture_path() {
        if (strpos($this->filename, 'http://') === 0 || strpos($this->filename, 'https://') === 0) {
            // Directly return the filename if it's an external URL.
            return $this->filename;
        } else if ($this->filename && !empty($this->filename)) {
            // Construct and return the path for a locally stored file.
            return $this->upload_directory . DS . $this->filename;
        } else {
            // Return a placeholder image URL if no filename is provided.
            return 'https://via.placeholder.com/300';
        }
    }



    /**
     * Reverses the soft deletion of a photo, effectively restoring it in the application.
     * This method updates the 'deleted_at' field for a specific photo record, setting it to NULL,
     * which indicates the photo is active and should be visible within the application.
     *
     * Explanation:
     * - Soft deletion is a technique used to mark records as deleted without actually removing them from the database.
     * - This method targets photos that have been soft-deleted by updating their 'deleted_at' timestamp back to NULL.
     * - By restoring the 'deleted_at' field to its default state, the photo is made available again in the application.
     *
     * Process:
     * 1. The photo ID passed to the method is first sanitized to prevent SQL injection vulnerabilities.
     * 2. An SQL UPDATE query is constructed to set the 'deleted_at' field of the specified photo record to NULL.
     * 3. The query is executed against the database. If successful, the photo is considered restored.
     *
     * Importance:
     * - Allows for reversible deletions, providing a safety net against accidental deletion of photos.
     * - Facilitates features like trash or archive, where users can recover deleted items.
     *
     * Usage:
     * Typically used in administrative interfaces or as part of a feature allowing users to restore previously deleted photos.
     *
     * Example:
     * ```php
     * // Assuming $photo_id is the ID of a photo that was previously soft-deleted
     * Photo::undo_soft_delete($photo_id);
     * // After calling this method, the photo with $photo_id is no longer marked as deleted
     * ```
     *
     * Note:
     * - It's essential to ensure that the photo ID provided exists and refers to a soft-deleted photo.
     * - This method does not perform any checks to confirm the photo's current deletion status before attempting to restore it.
     */
    public static function undo_soft_delete($photo_id) {
        global $database;
        // Sanitize the photo ID to prevent SQL injection.
        $escaped_id = $database->escape_string($photo_id);
        // Construct and execute the SQL query to set 'deleted_at' to NULL.
        $sql = "UPDATE " . static::$table_name . " SET deleted_at = NULL WHERE id = ?";
        $result = $database->query($sql, [$escaped_id]);
        return $result;
    }

    /**
     * Optimizes the dimensions and file size of an image while maintaining its aspect ratio.
     * This method takes an image file located at `target_path`, resizes it to fit within
     * predefined maximum width and height limits, and saves the optimized image back to the same location.
     * It supports JPEG, PNG, and GIF image formats.
     *
     * Explanation:
     * - Image optimization is crucial for web applications to reduce load times and improve performance.
     * - This method uses the PHP GD library to create a new, resized image based on the original's dimensions.
     * - It maintains the aspect ratio of the original image to prevent distortion.
     *
     * Process:
     * 1. Retrieve the original image's dimensions and type using `getimagesize`.
     * 2. Calculate the new dimensions to fit within the maximum width and height while maintaining the aspect ratio.
     * 3. Based on the image type, create a new image resource from the original file.
     * 4. Create a new true color image resource with the calculated dimensions.
     * 5. Copy and resize the original image onto the new image resource with resampling.
     * 6. Save the new image over the original file at `target_path`, adjusting quality/compression settings based on format.
     * 7. Free up memory by destroying the temporary image resources.
     *
     * Importance:
     * - Reduces the file size of images, which is beneficial for storage and bandwidth usage.
     * - Ensures images load faster on web pages, enhancing the user experience.
     *
     * Usage:
     * This method is typically called after an image is uploaded to the server and before it is stored permanently.
     * It can also be part of a batch process for existing images that need optimization.
     *
     * Example:
     * Assuming `$target_path` is the file path to an uploaded image:
     * ```php
     * $photo = new Photo();
     * $photo->optimize_image($target_path);
     * ```
     * After execution, the image at `$target_path` will be resized and optimized.
     *
     * Note:
     * - The method assumes the GD library is installed and enabled in PHP.
     * - Unsupported file types will cause the method to return `false`, indicating failure.
     */
    protected function optimize_image($target_path) {
        // Set the maximum width and height
        $maxWidth = 1920;
        $maxHeight = 1080;

        // Get the original image dimensions and type
        list($origWidth, $origHeight, $imageType) = getimagesize($target_path);
        $ratio = $origWidth / $origHeight;

        // Calculate new dimensions while maintaining aspect ratio
        if ($maxWidth / $maxHeight > $ratio) {
            $maxWidth = $maxHeight * $ratio;
        } else {
            $maxHeight = $maxWidth / $ratio;
        }

        // Create a new image from file
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $srcImg = imagecreatefromjpeg($target_path);
                break;
            case IMAGETYPE_PNG:
                $srcImg = imagecreatefrompng($target_path);
                break;
            case IMAGETYPE_GIF:
                $srcImg = imagecreatefromgif($target_path);
                break;
            default:
                return false; // Unsupported file type
        }

        // Create a new true color image
        $destImg = imagecreatetruecolor($maxWidth, $maxHeight);

        // Copy and resize part of an image with resampling
        imagecopyresampled($destImg, $srcImg, 0, 0, 0, 0, $maxWidth, $maxHeight, $origWidth, $origHeight);

        // Save the optimized image
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                imagejpeg($destImg, $target_path, 85); // 85 is a good quality-value for JPEG
                break;
            case IMAGETYPE_PNG:
                imagepng($destImg, $target_path, 9); // Compression level: from 0 (no compression) to 9
                break;
            case IMAGETYPE_GIF:
                imagegif($destImg, $target_path);
                break;
        }

        // Free up memory
        imagedestroy($srcImg);
        imagedestroy($destImg);

        return true;
    }

    /**
     * Calculates the total number of active records in the database table associated with the class.
     * This method considers only those records that have not been soft-deleted, ensuring an accurate count of active items.
     *
     * Explanation:
     * - Soft deletion is a technique where records are marked as deleted (typically with a timestamp) rather than being physically removed from the database.
     * - This method filters out records marked as deleted by checking the 'deleted_at' column, counting only those without a deletion timestamp or where the timestamp is set to a default 'zero' value.
     *
     * Process:
     * 1. Construct an SQL query to count all records in the class-associated table where 'deleted_at' is NULL or set to '0000-00-00 00:00:00'.
     * 2. Execute the query against the database.
     * 3. Fetch the result, which is a single row with the count of active records.
     * 4. Extract and return the count from the result set.
     *
     * Importance:
     * - Provides a quick way to determine the number of active items in the database, useful for dashboards, reports, or pagination controls.
     * - Helps maintain data integrity by excluding soft-deleted records from the count, aligning with the application's data lifecycle policies.
     *
     * Usage:
     * This method is typically called when an overview of active records is needed, such as displaying the total number of users, posts, or other entities in an admin panel.
     *
     * Example:
     * ```php
     * $totalActivePhotos = Photo::count_all();
     * echo "Total active photos: " . $totalActivePhotos;
     * ```
     * This would display the total number of photos that haven't been soft-deleted.
     *
     * Note:
     * - The method assumes that the `deleted_at` column is used to mark soft deletions, and records with `NULL` or a 'zero' timestamp in this column are considered active.
     * - It's crucial that the class using this method has the `static::$table_name` property correctly set to the name of the database table it represents.
     */
    public static function count_all() {
        global $database;
        $sql = "SELECT COUNT(*) FROM " . self::$table_name . " WHERE deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00'";
        $result_set = $database->query($sql);
        $row = $result_set->fetch_array();
        return array_shift($row);
    }

}

?>