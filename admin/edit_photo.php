<?php
global $session;
include("includes/header.php");
include("includes/sidebar.php");
?>

<?php
/*include("includes/content-top.php");*/

//$photos = Photo::find_all();
//$photosoftdeletes = Photo::find_all_soft_deletes();
if(!$session->is_signed_in()){
    header("Location:login.php");
}
if(empty($_GET['id'])){
    header("Location:photos.php");
}else{
    $photo = Photo::find_by_id($_GET['id']);

    if(isset($_POST['update'])) {
        if($photo) {
            // Synchronize tags
            if(isset($_POST['tags'])) {
                $photo->syncTags($_POST['tags']);
            }

            // Synchronize categories
            if(isset($_POST['categories'])) {
                $photo->syncCategories($_POST['categories']);
            }

            // Continue with the rest of the update process, such as handling the file upload
            // Ensure to call $photo->save() after processing tags and categories
            $photo->title = $_POST['title'];
            $photo->description = $_POST['description'];
            $photo->alternate_text = $_POST['alternate_text'];
            // Add more fields as necessary

            // Handle file upload if needed
            if (!empty($_FILES['file']['name'])) {
                $photo->set_file($_FILES['file']);
            }

            // Final save method that saves all information including file, tags, and categories
            if ($photo->save_all()) {
                // Redirect or show success message
                header("Location: edit_photo.php?id={$photo->id}&saved=true");
            } else {
                // Handle errors
                echo "Photo could not be saved.";
            }
        }
    }
}

?>
    <div id="main">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <h1 class="page-header">Edit Photo</h1>
                </div>
                <hr>
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Photo upload</h2>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <form action="edit_photo.php?id=<?php echo $photo->id; ?>" method="POST" enctype="multipart/form-data" class="form form-horizontal">
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label for="title">Title</label>
                                        </div>
                                        <div class="col-md-8 form-group">
                                            <input type="text" id="title" class="form-control" name="title" placeholder="Title" value="<?php echo $photo->title; ?>">
                                            <div data-lastpass-icon-root="true" style="position: relative !important; height: 0px !important; width: 0px !important; float: left !important;"></div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="description">Description</label>
                                        </div>
                                        <div class="col-md-8 form-group">
                                            <textarea placeholder="description" name="description" id="editor" class="form-control" cols="100%">
                                                <?php echo $photo->description; ?>
                                            </textarea>
                                        </div>
                                        <div class="col-md-4 mt-5">
                                            <label for="alternate_text">Alt</label>
                                        </div>
                                        <div class="col-md-8 form-group mt-5">
                                            <input type="text" id="alternate_text" class="form-control"
                                                   placeholder="alternate text" name="alternate_text"
                                                   value="<?php echo $photo->alternate_text; ?>">

                                            <div data-lastpass-icon-root="true" style="position: relative !important; height: 0px !important; width: 0px !important; float: left !important;">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <label for="file">File</label>
                                        </div>
                                        <div class="col-md-8 form-group">
                                            <img src="<?php echo $photo->picture_path();?>" alt="<?php echo $photo->title;?>" class="img-fluid img-thumbnail">
                                            <p class="mt-3">Uploaded on: <?php echo $photo->created_at; ?></p>
                                            <p>Filename: <?php echo $photo->filename; ?></p>
                                            <p>Filetype: <?php echo $photo->type; ?></p>
                                            <p>Filesize: <?php echo ($photo->size)/1000; ?> Kb</p>
                                            <input type="file" name="file" class="form-control">
                                        </div>

	                                    <!-- Tags Input -->
	                                    <div class="col-md-4">
		                                    <label for="tags">Tags</label>
	                                    </div>
	                                    <div class="col-md-8 form-group">
		                                    <input type="text" id="tags" class="form-control" name="tags" placeholder="Tags" value="<?php echo implode(', ', Tag::find_tags_by_photo_id($photo->id)); ?>">
	                                    </div>

	                                    <!-- Categories Input -->
	                                    <div class="col-md-4">
		                                    <label for="categories">Categories</label>
	                                    </div>
	                                    <div class="col-md-8 form-group">
		                                    <input type="text" id="categories" class="form-control" name="categories" placeholder="Categories" value="<?php echo implode(', ', Category::find_categories_by_photo_id($photo->id)); ?>">
	                                    </div>

                                        <div class="col-sm-12 d-flex justify-content-end">
                                            <button type="submit" name="update" class="btn btn-primary me-1 mb-1">Update</button>
                                            <a href="photos.php" class="btn btn-light-secondary me-1 mb-1">Back</a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
include("includes/footer.php");
?>