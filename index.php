<?php
    require_once("includes/header.php");
    require_once("includes/navbar.php");
    require_once("admin/includes/init.php");
    $photos = Photo::find_all();
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <?php foreach($photos as $photo):?>
                <a href="blogdetail.php?id=<?php echo $photo->id; ?>">
                    <img src="<?php echo 'admin'.DS. $photo->picture_path(); ?>" class="img-thumbnail" alt="..." width="300" height="300">
                </a>
            <?php endforeach;?>
        </div>
    </div>
</div>

<?php
    require_once("includes/footer.php");
?>
