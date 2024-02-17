<?php
global $database;
require_once("includes/header.php");
require_once("includes/navbar.php");
require_once("admin/includes/init.php");

// Retrieve the category from the URL
$category = isset($_GET['category']) ? urldecode($_GET['category']) : '';

// Fetch images associated with the category
$images = [];
if(!empty($category)) {
    $images = Photo::find_by_category($category); // Assumes you have this method implemented
}
?>

<div class="container mt-4">
	<h1 class="mb-4">Images in Category "<?php echo htmlspecialchars($category); ?>"</h1>
	<div class="row">
        <?php foreach($images as $image): ?>
			<div class="col-md-4 mb-4">
				<div class="card image-card">
					<!-- Wrapping image in an anchor tag -->
					<a href="blogdetail.php?id=<?php echo $image->id; ?>">
						<img src="<?php echo 'admin/' . $image->picture_path(); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($image->title); ?>">
					</a>
					<div class="card-body">
						<p class="card-text"><?php echo htmlspecialchars($image->title); ?></p>
					</div>
				</div>
			</div>
        <?php endforeach; ?>
	</div>
</div>

<?php require_once("includes/footer.php"); ?>

<style>
    .image-card {
        width: 20rem;
    }

    .card-img-top {
        height: 200px;
        object-fit: contain;
        width: 100%;
        background-color: #f8f9fa;
    }
</style>
