<!-- Comments section-->
<?php
if($session->is_signed_in()) {
    ?>
    <section class="mb-5">
        <div class="card bg-light">
            <div class="card-body">
                <!-- Comment form-->
                <form method="POST" class="mb-4">
                    <input type="text" name="author" class="form-control mb-1" placeholder="Author...">
                    <textarea name="body" class="form-control mb-1" rows="3" placeholder="Join the discussion and leave a comment!"></textarea>
                    <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                </form>
                <!-- Comment with nested comments-->
                <?php foreach($comments as $comment): ?>

                    <div class="d-flex mb-4">
                        <!-- Parent comment-->
                        <div class="flex-shrink-0"><img width="62" height="62" class="rounded-circle" src="<?php echo $comment->comment_avatar(); ?>" alt="..." /></div>
                        <div class="ms-3">
                            <div class="fw-bold"><?= $comment->author; ?></div>
                            <p><?= $comment->body; ?></p>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}
?>
