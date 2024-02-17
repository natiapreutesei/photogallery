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
    public $created_at;
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

    // Method to return object properties as an associative array.
    public function get_properties() {
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

    // This method is responsible for handling the uploaded file.
    // It validates the file and sets the photo object's properties accordingly.
    public function set_file($file){
        // Check if the uploaded file is valid.
        // This involves several checks:
        // 1. If the file variable is empty.
        // 2. If the file variable is not an array (as expected from a file upload).
        // 3. If the file name is not set or is empty, indicating no file was chosen for upload.
        if(empty($file) || !$file || !is_array($file) || !$file['name']){
            $this->errors[] = "No file uploaded"; // Add an error message to the errors array.
            return false; // Return false to indicate the file handling failed.
        } elseif($file['error'] != 0){
            // Each file upload comes with an error code. A code of 0 means no error.
            // If there's any error code other than 0, it means an error occurred during file upload.
            // The specific error message is fetched from a predefined array using the error code as the key.
            $this->errors[] = $this->upload_errors_array[$file['error']];
            return false; // Return false to indicate the file handling failed due to an upload error.
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

    // Method to save or update a photo record in the database.
    // It handles file uploading, record creation, and record updates.
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


    // The save_all() method orchestrates the saving process of a photo along with its associated tags and categories.
    // This method ensures that all related data is consistently saved, making it a central part of managing photo uploads.
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
    // The save_tags() method is responsible for handling the association of tags with a specific photo.
    // It iterates through each tag assigned to the photo, checks if the tag already exists in the database,
    // creates it if it doesn't, and then links the photo to the tag.
    protected function save_tags() {
        global $database; // Access the global database object to perform database operations.

        $photo_id = $this->id; // Retrieve the ID of the current photo object, which was set after saving the photo.

        // Clear existing tags for the photo to handle removed tags and avoid duplicates.
        $this->clear_tags();

        // Iterate through each tag name assigned to this photo.
        foreach ($this->tags as $tag_name) {
            // Check if the current tag already exists in the database by searching for its name.
            $tag = new Tag();
            $tag->tag_name = $tag_name;

            if($tag->save()) { // Attempt to save the tag, which handles both new and existing tags.
                // After saving, the tag's id property is correctly set, so we can link it to the photo.
                $this->link_tag($tag->id);
            }
            // If saving the tag failed (due to empty name or other issues), handle accordingly (optional).
        }
    }

    // Method to clear existing tags for a photo before re-adding them.
    // This prevents duplicates and handles tag removal.
    protected function clear_tags() {
        global $database;
        $sql = "DELETE FROM photo_tags WHERE photo_id = ?";
        $database->query($sql, [$this->id]);
    }

    // This method is responsible for creating a link between a photo and a tag in the database.
    // It ensures that each photo is correctly associated with its tags, which is essential for organizing and retrieving photos based on tags.
    protected function link_tag($tag_id) {
        global $database; // Access the global database object to perform SQL operations.

        // Ensure that the $tag_id is valid to prevent attempting to insert null values into the database.
        if ($tag_id === null) {
            error_log("Attempted to link photo ID {$this->id} with a null tag ID.");
            return; // Exit the function if $tag_id is null.
        }

        // Prepare an SQL query to check if a link between the current photo and the given tag already exists.
        $sql = "SELECT * FROM photo_tags WHERE photo_id = ? AND tag_id = ?";
        $params = [$this->id, $tag_id]; // Parameters for the SQL query, preventing SQL injection.

        // Execute the query with the provided parameters.
        $result = $database->query($sql, $params);

        // If there is no existing link between the photo and the tag, create it.
        if ($result->num_rows == 0) {
            $sql = "INSERT INTO photo_tags (photo_id, tag_id) VALUES (?, ?)";
            $database->query($sql, $params);
        }
        // No action required if the relationship already exists.
    }


    // This static method retrieves all photos associated with a given tag name.
    public static function find_by_tag($tag_name) {
        global $database; // Access the global database object for executing SQL queries.

        $photo_list = []; // Initialize an empty array to hold the photos that match the given tag.

        // Prepare an SQL query that joins three tables: photos, photo_tags, and tags.
        // The goal is to select all photo records that are linked to the specified tag name.
        // This SQL statement uses INNER JOINs to ensure that only photos with a valid link to the specified tag are selected.
        // The query also filters out photos marked as deleted by checking the 'deleted_at' column.
        $sql = "SELECT p.* FROM photos p
            INNER JOIN photo_tags pt ON p.id = pt.photo_id
            INNER JOIN tags t ON pt.tag_id = t.id
            WHERE t.tag_name = ? AND (p.deleted_at IS NULL OR p.deleted_at = '0000-00-00 00:00:00')";

        // Execute the query with the tag name as a parameter. This prevents SQL injection by using parameterized queries.
        $result = $database->query($sql, [$tag_name]);

        // Check if the query was successful and returned rows.
        if($result) {
            // Iterate over each row in the result set.
            while($row = $result->fetch_assoc()) {
                // For each row, instantiate a new Photo object with the row's data.
                // The 'instantie' method is a custom method that maps the associative array from the fetch_assoc() call
                // to a new instance of the Photo class, setting its properties based on the column values.
                $photo_list[] = static::instantie($row);
            }
        }

        // Return the list of Photo objects that have been associated with the given tag name.
        return $photo_list;
    }

    // Method to unlink all tags from this photo

    // Public wrapper method for linking a tag
    public function addTag($tag_id) {
        $this->link_tag($tag_id);
    }

    // Ensure tag existence and get its ID
    // Ensure a tag exists and return its ID
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


    // Synchronize tags based on input from the edit form
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

    // Remove a tag from this photo
    private function removeTag($tagId) {
        global $database;
        $sql = "DELETE FROM photo_tags WHERE photo_id = ? AND tag_id = ?";
        $database->query($sql, [$this->id, $tagId]);
    }


    /*METHODS DEALING WITH CATEGORIES*/
    // This method handles the association of categories to a specific photo.
    // It ensures that the photo is linked to each of its categories in the database,
    // creating new category records as necessary.
    protected function save_categories() {
        global $database;

        // Clear existing category links for the photo
        $this->clear_categories();

        // Loop through each category name assigned to this photo
        foreach ($this->categories as $category_name) {
            // Ensure the category exists in the database and get its ID
            $category_id = Category::ensure_category($category_name);

            if($category_id) {
                // Link the current photo with the category by its ID
                $this->link_category($category_id);
            }
            // If the category ID is not returned, handle accordingly (optional)
        }
    }

    protected function clear_categories() {
        global $database;
        $sql = "DELETE FROM photo_categories WHERE photo_id = ?";
        $database->query($sql, [$this->id]);
    }

    // This method establishes a link between a photo and a category in the database.
    // It's a critical part of managing the many-to-many relationship between photos and categories,
    // allowing a single photo to be associated with multiple categories and vice versa.
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


    // This static method retrieves all photos associated with a given category.
    public static function find_by_category($category_name) {
        global $database; // Access the global database object to perform queries.

        // Construct an SQL query that selects all fields from the 'photos' table.
        // This SQL statement joins two additional tables: 'photo_categories' and 'categories',
        // to filter photos that belong to a specific category.
        $sql = "SELECT photos.* FROM photos ";
        $sql .= "INNER JOIN photo_categories ON photos.id = photo_categories.photo_id "; // Link 'photos' to 'photo_categories' by photo ID.
        $sql .= "INNER JOIN categories ON photo_categories.category_id = categories.id "; // Further join 'photo_categories' with 'categories' by category ID.
        $sql .= "WHERE categories.category_name = ? "; // Filter the result to only include photos that belong to the specified category name.

        // Execute the query with the provided category name as a parameter to prevent SQL injection.
        $result_set = $database->query($sql, [$category_name]);

        // Initialize an empty array to hold the Photo objects.
        $object_array = [];
        // Iterate through each row returned by the query.
        while($row = $result_set->fetch_assoc()) {
            // For each row, create a new Photo object and initialize it with the row data.
            // The 'instantie' method is assumed to properly instantiate a Photo object based on the row data.
            $object_array[] = static::instantie($row);
        }

        // Return the array of Photo objects.
        // Each object in this array represents a photo that belongs to the specified category,
        // allowing the application to process or display these photos accordingly.
        return $object_array;
    }


    // Public wrapper method for linking a category
    public function addCategory($category_id) {
        $this->link_category($category_id);
    }

    // Ensure category existence and get its ID
    // Ensure a category exists and return its ID
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

    // Method to remove a category from this photo
    private function removeCategory($categoryId) {
        global $database;
        $sql = "DELETE FROM photo_categories WHERE photo_id = ? AND category_id = ?";
        $database->query($sql, [$this->id, $categoryId]);
    }



    /*OTHER METHODS*/
    // This method returns the path to a photo file.
    // It's essential for retrieving and displaying photos stored in the application's filesystem or displaying a placeholder if the photo file does not exist.
    public function picture_path() {
        // Check if a filename has been set for the photo and that it is not an empty string.
        // The check ensures that there is a valid filename to construct the path.
        if ($this->filename && $this->upload_directory.DS.$this->filename != "") {
            // Construct the path to the photo using the upload directory and the filename.
            // DS is a directory separator constant (e.g., '/' on Linux/Unix or '\' on Windows),
            // ensuring the path is correctly formed regardless of the operating system.
            return $this->upload_directory.DS.$this->filename;
        } else {
            // If the photo does not have a valid filename, return a URL to a placeholder image.
            // This is useful for maintaining user interface consistency by providing a default image
            // when the actual photo is missing or not specified.
            return 'https://via.placeholder.com/300';
        }
    }

    public static function undo_soft_delete($photo_id) {
        global $database;
        // Ensure the photo ID is properly escaped to prevent SQL injection
        $escaped_id = $database->escape_string($photo_id);
        // SQL to set deleted_at to NULL for the specified photo ID
        $sql = "UPDATE " . static::$table_name . " SET deleted_at = NULL WHERE id = ?";
        // Execute the query
        $result = $database->query($sql, [$escaped_id]);
        return $result;
    }

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
                imagepng($destImg, $target_path, 6); // Compression level: from 0 (no compression) to 9
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

    /*public function update_photo(){
        if(!empty($this->filename)){
            $target_path = SITE_ROOT.DS.'admin'.DS.$this->picture_path();
            return unlink($target_path) ? true : false;
        }
    }*/
}

?>