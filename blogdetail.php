<?php
global $database;
require_once("includes/header.php");
require_once("includes/navbar.php");
require_once("admin/includes/init.php");
if(empty($_GET['id'])){
    header("Location:index.php");
}
$photo = Photo::find_by_id($_GET['id']);

if(isset($_POST['submit'])){
    $author = $database->escape_string($_POST['author']);
    $body = $database->escape_string($_POST['body']);
    $new_comment = Comment::create_comment($photo->id, $author, $body);
    if($new_comment && $new_comment->save()){
        header("Location:blogdetail.php?id={$photo->id}");
    }else{
       $message = "Problems while saving";
    }}else{
    $author= "";
    $body = "";
}
//hiermee halen we alle comments op van dit blogdetail,nl.photo->id
$comments = Comment::find_the_comments($photo->id);


?>
<!-- Page content-->
<div class="container mt-5">
    <div class="row">
        <div class="col-lg-8">
            <!-- Post content-->
            <article>
                <!-- Post header-->
                <header class="mb-4">
                    <!-- Post title-->
                    <h1 class="fw-bolder mb-1"><?php echo $photo->title; ?></h1>
                    <!-- Post meta content-->
	                <div class="text-muted fst-italic mb-2">Posted on <?php echo date('F j, Y', strtotime($photo->created_at)); ?></div>

	                <!-- Post tags-->
                    <?php
                    $tags = Tag::find_tags_by_photo_id($photo->id);
                    foreach($tags as $tag): ?>
		                <a class="badge bg-secondary text-decoration-none link-light" href="tagged_photos.php?tag=<?php echo urlencode($tag); ?>"><?php echo htmlspecialchars($tag); ?></a>
                    <?php endforeach; ?>
                </header>
                <!-- Preview image figure-->
                <figure class="mb-4"><img class="img-fluid rounded" src="<?php echo 'admin'.DS. $photo->picture_path(); ?>" alt="<?php echo $photo->alternate_text; ?>" /></figure>
                <!-- Post content-->
                <section class="mb-5">
                    <p class="fs-5 mb-4">Science is an enterprise that should be cherished as an activity of the free human mind. Because it transforms who we are, how we live, and it gives us an understanding of our place in the universe.</p>
                    <p class="fs-5 mb-4">The universe is large and old, and the ingredients for life as we know it are everywhere, so there's no reason to think that Earth would be unique in that regard. Whether of not the life became intelligent is a different question, and we'll see if we find that.</p>
                    <p class="fs-5 mb-4">If you get asteroids about a kilometer in size, those are large enough and carry enough energy into our system to disrupt transportation, communication, the food chains, and that can be a really bad day on Earth.</p>
                    <h2 class="fw-bolder mb-4 mt-5">I have odd cosmic thoughts every day</h2>
                    <p class="fs-5 mb-4">For me, the most fascinating interface is Twitter. I have odd cosmic thoughts every day and I realized I could hold them to myself or share them with people who might be interested.</p>
                    <p class="fs-5 mb-4">Venus has a runaway greenhouse effect. I kind of want to know what happened there because we're twirling knobs here on Earth without knowing the consequences of it. Mars once had running water. It's bone dry today. Something bad happened there as well.</p>
                </section>
            </article>
            <?php require_once("includes/comments.php"); ?>
        </div>
        <!-- Side widgets-->
        <div class="col-lg-4">
            <!-- Search widget-->
            <div class="card mb-4">
                <div class="card-header">Search</div>
                <div class="card-body">
                    <div class="input-group">
                        <input class="form-control" type="text" placeholder="Enter search term..." aria-label="Enter search term..." aria-describedby="button-search" />
                        <button class="btn btn-primary" id="button-search" type="button">Go!</button>
                    </div>
                </div>
            </div>
	        <!-- Category widget-->
	        <div class="card mb-4">
		        <div class="card-header">Categories</div>
		        <div class="card-body">
			        <div class="row">
                        <?php
                        // Fetch all categories from the database
                        $all_categories = Category::find_all_categories();
                        // Determine the midpoint of the categories array for splitting
                        $midpoint = ceil(count($all_categories) / 2);
                        $first_half_categories = array_slice($all_categories, 0, $midpoint);
                        $second_half_categories = array_slice($all_categories, $midpoint);
                        ?>
				        <div class="col-sm-6">
					        <ul class="list-unstyled mb-0">
                                <?php foreach($first_half_categories as $category): ?>
							        <li><a href="category_photos.php?category=<?php echo urlencode($category); ?>"><?php echo htmlspecialchars($category); ?></a></li>
                                <?php endforeach; ?>
					        </ul>
				        </div>
				        <div class="col-sm-6">
					        <ul class="list-unstyled mb-0">
                                <?php foreach($second_half_categories as $category): ?>
							        <li><a href="category_photos.php?category=<?php echo urlencode($category); ?>"><?php echo htmlspecialchars($category); ?></a></li>
                                <?php endforeach; ?>
					        </ul>
				        </div>
			        </div>
		        </div>
	        </div>
	        <!-- Side widget-->
            <div class="card mb-4">
                <div class="card-header">Side Widget</div>
                <div class="card-body">You can put anything you want inside of these side widgets. They are easy to use, and feature the Bootstrap 5 card component!</div>
            </div>
        </div>
    </div>
</div>

<?php
require_once("includes/footer.php");
?>
