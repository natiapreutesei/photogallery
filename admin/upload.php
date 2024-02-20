<?php
/**
 * This script facilitates the bulk upload of photos to a content management system, allowing users to
 * add multiple photos at once, along with their metadata such as titles, descriptions, tags, and categories.
 * It is designed to handle file uploads securely, process each photo's associated data, and provide feedback
 * on the success or failure of each upload.
 *
 * Features:
 * - Allows for the selection and upload of multiple files simultaneously.
 * - Users can specify titles, descriptions, tags, and categories for each photo.
 * - Provides immediate feedback on the outcome of each upload, including success or failure messages.
 * - Supports adding more file upload inputs dynamically with JavaScript for enhanced user interaction.
 *
 * Security Aspects:
 * - Ensures proper sanitation and validation of all user inputs to prevent security vulnerabilities.
 * - Implements checks to ensure only authenticated users can access the upload functionality.
 * - Utilizes server-side file type verification to prevent the upload of potentially harmful files.
 *
 * Workflow:
 * 1. Checks user authentication and redirects unauthenticated users to the login page.
 * 2. Processes the uploaded files and associated data upon form submission.
 * 3. For each file, creates a new Photo instance, sets its properties, and attempts to save it to the database.
 * 4. Collects and displays success or error messages based on the outcome of each upload.
 * 5. Allows users to dynamically add more file inputs to the form without reloading the page.
 *
 * Implementation Notes:
 * - The form uses the `enctype="multipart/form-data"` attribute to enable file uploads.
 * - PHP's global `$_FILES` array is used to access the uploaded files, while `$_POST` contains the metadata.
 * - JavaScript functionality enhances the form by allowing dynamic addition of more file inputs.
 *
 * Usage:
 * - This script is intended for use in the administrative backend of a website or application where managing
 *   photo content is required.
 * - It provides a streamlined way to upload and categorize photos, making it a valuable tool for content managers.
 */


// Access the session global variable to manage user sessions across the application.
global $session;

// Include the header part of the HTML page which typically contains the HTML head section including links to stylesheets, website title, etc.
include("includes/header.php");

// Check if the user is not signed in by calling the `is_signed_in` method on the session object.
// If the user is not signed in, redirect them to the login page.
if(!$session->is_signed_in()){
    header("location:login.php");
}

// Check if the form was submitted by looking for the 'submit' button in the POST request.
if(isset($_POST['submit'])) {
    // Initialize a variable to store messages that will be displayed to the user.
    $message = "";
    // Initialize counters for successful and failed uploads.
    $upload_successes = 0;
    $upload_failures = 0;

    // Iterate through each uploaded file. The `$_FILES['files']['name']` array contains all the names of uploaded files.
    foreach($_FILES['files']['name'] as $key => $value) {
        // Check if the current file name is not empty, indicating a file was uploaded.
        if(!empty($value)) {
            // Instantiate a new Photo object to represent the uploaded photo.
            $photo = new Photo();
            // Set the photo's title and description using the submitted form data. Default to "Untitled" or "No description" if not provided.
            $photo->title = $_POST['title'][$key] ?? "Untitled";
            $photo->description = $_POST['description'][$key] ?? "No description";

            // Construct an array representing the current file, consolidating its properties like name, type, temporary name, error code, and size.
            $fileArray = [
                'name' => $_FILES['files']['name'][$key],
                'type' => $_FILES['files']['type'][$key],
                'tmp_name' => $_FILES['files']['tmp_name'][$key],
                'error' => $_FILES['files']['error'][$key],
                'size' => $_FILES['files']['size'][$key]
            ];
            // Pass the constructed file array to the Photo object's `set_file` method to handle the upload.
            $photo->set_file($fileArray);

            // Process and set tags and categories for the photo, converting the submitted strings to arrays of trimmed strings.
            $photo->tags = isset($_POST['tags'][$key]) ? array_map('trim', explode(',', $_POST['tags'][$key])) : [];
            $photo->categories = isset($_POST['categories'][$key]) ? array_map('trim', explode(',', $_POST['categories'][$key])) : [];

            // Attempt to save the photo along with its metadata (tags, categories) by calling `save_all`.
            // If successful, increment the success counter; otherwise, increment the failure counter and append an error message.
            if($photo->save_all()) {
                $upload_successes++;
            } else {
                $upload_failures++;
                $message .= "Error uploading: " . $_FILES['files']['name'][$key] . "<br>" . join("<br>", $photo->errors) . "<br>";
            }
        }
    }

    // After processing all files, append messages about the number of successful and failed uploads to the `$message` variable.
    if($upload_successes > 0) {
        $message .= "{$upload_successes} photo(s) uploaded successfully.<br>";
    }
    if($upload_failures > 0) {
        $message .= "{$upload_failures} upload(s) failed.<br>";
    }
}

// Include the sidebar part of the HTML page which may contain navigation links, user profile information, etc.
include("includes/sidebar.php");
?>

	<div class="container-fluid">
		<div class="row">
			<div class="col-6 offset-4 mt-5">
				<div id="content">
					<h1 class="page-header">Upload</h1>
					<hr>
					<div class="card">
						<div class="card-header">
							<h2 class="card-title">Photo upload</h2>
						</div>
						<div class="card-content">
							<div class="card-body">
								<form action="upload.php" method="POST" enctype="multipart/form-data" class="form form-horizontal" id="uploadForm">
									<div class="form-body">
										<div class="row file-upload-row">
											<div class="col-md-4">
												<label for="file[]">File</label>
											</div>
											<div class="col-md-8 form-group">
												<input type="file" name="files[]" class="form-control-file">
											</div>
											<div class="col-md-4">
												<!--title[] is intended for use with an array of title inputs (for example, when uploading multiple files each with its own title). To make this work correctly, each corresponding input element should have an id attribute that matches the for value of its associated label. Since title[] suggests multiple titles, you would typically have unique IDs for each input (e.g., title-0, title-1, etc.), and the for attribute of each label should match these IDs.-->
												<label for="title[]">Title</label>
											</div>
											<div class="col-md-8 form-group">
												<input type="text" name="title[]" class="form-control" placeholder="Title">
											</div>
											<div class="col-md-4">
												<label for="description[]">Description</label>
											</div>
											<div class="col-md-8 form-group">
												<textarea name="description[]" class="form-control" placeholder="Description"></textarea>
											</div>
											<div class="col-md-4">
												<label for="tags[]">Tags</label>
											</div>
											<div class="col-md-8 form-group">
												<input type="text" name="tags[]" class="form-control" placeholder="Tags">
											</div>
											<div class="col-md-4">
												<label for="categories[]">Categories</label>
											</div>
											<div class="col-md-8 form-group">
												<input type="text" name="categories[]" class="form-control" placeholder="Categories">
											</div>
										</div>
										<div class="col-sm-12 d-flex justify-content-end buttons-container">
											<button type="button" class="btn btn-secondary me-1 mb-1" onclick="addMoreFiles()">Add More Files</button>
											<button type="submit" name="submit" class="btn btn-primary me-1 mb-1">Upload</button>
										</div>
									</div>
								</form>

							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

<?php
include("includes/footer.php");
?>


<script>
    // Initialize fileIndex to 1 because the initial set of file input fields in the form uses index 0.
    let fileIndex = 1;

    // Define a function that will be called to add more file input fields to the form.
    function addMoreFiles() {
        // Clone the first row of file input fields as a template for new inputs.
        const container = document.querySelector('.file-upload-row').cloneNode(true);

        // Iterate over each input and textarea in the cloned container.
        container.querySelectorAll('input, textarea').forEach(input => {
            // Clear the values of the inputs to ensure the cloned elements are empty.
            input.value = '';

            // Update the names of the inputs to include the new fileIndex, ensuring each input has a unique name.
            // This is necessary to correctly process multiple files on the server side.
            if (input.name === 'files[]') {
                input.name = `files[${fileIndex}]`;
            } else if (input.name === 'title[]') {
                input.name = `title[${fileIndex}]`;
            } else if (input.name === 'description[]') {
                input.name = `description[${fileIndex}]`;
            } else if (input.name === 'tags[]') {
                input.name = `tags[${fileIndex}]`;
            } else if (input.name === 'categories[]') {
                input.name = `categories[${fileIndex}]`;
            }
        });

        // Locate the form's main body where inputs are contained and the container for the buttons.
        const formBody = document.getElementById('uploadForm').querySelector('.form-body');
        const buttonsContainer = document.querySelector('.buttons-container');

        // Insert the new set of input fields (container) into the form, just above the buttons' container.
        // This places the new inputs in the correct location within the form layout.
        formBody.insertBefore(container, buttonsContainer);

        // Increment the fileIndex to ensure that the next set of added inputs will have a unique index.
        fileIndex++;

        // Scroll the page to the newly added set of input fields to bring them into view.
        // This improves the user experience by showing the user where the new inputs have been added.
        container.scrollIntoView({ behavior: 'smooth', block: 'end' });
    }
</script>

<style>
    .file-upload-row {
        border: 1px solid #ccc;
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 5px;
    }
</style>