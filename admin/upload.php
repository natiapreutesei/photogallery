<?php
//include: error op de pagina,php genereert een waarschuwing,
//maar: de pagina zal wel verder uitgevoerd worden.
//require: hetzelfde als include: php genereert een fatale fout
//en stop de pagina van uitvoering
//include_once
//require_once
global $session;
include("includes/header.php");
if(!$session->is_signed_in()){
    header("location:login.php");
}

if(isset($_POST['submit'])) {
    $message = "";
    $upload_successes = 0;
    $upload_failures = 0;

    // Loop through each file
    foreach($_FILES['files']['name'] as $key => $value) {
        if(!empty($value)) { // Check if file name is not empty
            $photo = new Photo();
            // Set photo properties from the form
            $photo->title = $_POST['title'][$key] ?? "Untitled";
            $photo->description = $_POST['description'][$key] ?? "No description";

            // Construct the file array for this specific file
            $fileArray = [
                'name' => $_FILES['files']['name'][$key],
                'type' => $_FILES['files']['type'][$key],
                'tmp_name' => $_FILES['files']['tmp_name'][$key],
                'error' => $_FILES['files']['error'][$key],
                'size' => $_FILES['files']['size'][$key]
            ];
            $photo->set_file($fileArray);

            // Process and set tags and categories
            $photo->tags = isset($_POST['tags'][$key]) ? array_map('trim', explode(',', $_POST['tags'][$key])) : [];
            $photo->categories = isset($_POST['categories'][$key]) ? array_map('trim', explode(',', $_POST['categories'][$key])) : [];

            if($photo->save_all()) {
                $upload_successes++;
            } else {
                $upload_failures++;
                $message .= "Error uploading: " . $_FILES['files']['name'][$key] . "<br>" . join("<br>", $photo->errors) . "<br>";
            }
        }
    }

    if($upload_successes > 0) {
        $message .= "{$upload_successes} photo(s) uploaded successfully.<br>";
    }
    if($upload_failures > 0) {
        $message .= "{$upload_failures} upload(s) failed.<br>";
    }
}



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
    let fileIndex = 1; // Start with index 1 since 0 is used by the initial set of inputs

    function addMoreFiles() {
        const container = document.querySelector('.file-upload-row').cloneNode(true);

        // Update the input names with the new index for uniqueness
        container.querySelectorAll('input, textarea').forEach(input => {
            input.value = ''; // Clear input values

            if(input.name === 'files[]') {
                input.name = `files[${fileIndex}]`;
            } else if(input.name === 'title[]') {
                input.name = `title[${fileIndex}]`;
            } else if(input.name === 'description[]') {
                input.name = `description[${fileIndex}]`;
            } else if(input.name === 'tags[]') {
                input.name = `tags[${fileIndex}]`;
            } else if(input.name === 'categories[]') {
                input.name = `categories[${fileIndex}]`;
            }
        });

        // Find the form and buttons container
        const formBody = document.getElementById('uploadForm').querySelector('.form-body');
        const buttonsContainer = document.querySelector('.buttons-container');

        // Insert the new container before the buttons container
        formBody.insertBefore(container, buttonsContainer);

        fileIndex++; // Increment the index for the next set of inputs

        // Optional: Scroll to the new form for better user experience
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