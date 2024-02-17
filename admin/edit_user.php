<?php
//include: error op de pagina,php genereert een waarschuwing,
//maar: de pagina zal wel verder uitgevoerd worden.
//require: hetzelfde als include: php genereert een fatale fout
//en stop de pagina van uitvoering
//include_once
//require_once
require_once("includes/header.php");
if (!$session->is_signed_in()) {
    header("location:login.php");
}
include("includes/sidebar.php");

if (empty($_GET['id'])) {
    header("Location:users.php");
}
$user = User::find_by_id($_GET['id']);
if (isset($_POST['updateuser'])) {
    if ($user) {
        $user->username = $_POST['username'];
        $user->first_name = $_POST['first_name'];
        $user->last_name = $_POST['last_name'];
        // Huidige waarde van deleted_at ophalen uit de database
        $current_user = User::find_by_id($_GET['id']);
        $user->deleted_at = $current_user->deleted_at;
        $user->set_file($_FILES['user_image']);
        $user->save_user_and_image();
    }
}
?>

<div id="main">
    <div class="row">
        <div class="col-12">
            <div id="content">
                <h1 class="page-header">Users</h1>
                <hr>
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Edit User</h2>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <form action="edit_user.php?id=<?php echo $user->id; ?>" method="POST"
                                  enctype="multipart/form-data" class="form form-horizontal">
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label for="username">Username</label>
                                        </div>
                                        <div class="col-md-8 form-group">
                                            <input type="text" id="username" class="form-control" name="username"
                                                   placeholder="username" value="<?php echo $user->username; ?>">
                                            <div data-lastpass-icon-root="true"
                                                 style="position: relative !important; height: 0px !important; width: 0px !important; float: left !important;"></div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="first_name">First Name</label>
                                        </div>
                                        <div class="col-md-8 form-group">
                                            <input type="text" id="first_name" class="form-control" name="first_name"
                                                   placeholder="first_name" value="<?php echo $user->first_name; ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="last_name">Last Name</label>
                                        </div>
                                        <div class="col-md-8 form-group">
                                            <input type="text" id="last_name" class="form-control" name="last_name"
                                                   placeholder="last_name" value="<?php echo $user->last_name; ?>">
                                        </div>

                                        <div class="row">
                                            <div class="col-6">
                                                <div class="col-md-4">
                                                    <label for="user_image">User Image</label>
                                                </div>

                                                <div class="col-md-8 form-group">
                                                    <input type="file" id="user_image" name="user_image"
                                                           class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <img class="img-fluid img-thumbnail"
                                                     src="<?php echo $user->picture_path_and_placeholder(); ?>" alt="">
                                            </div>
                                        </div>


                                        <div class="col-sm-12 d-flex justify-content-end">
                                            <button type="submit" name="updateuser" class="btn btn-primary me-1 mb-1">
                                                Update User
                                            </button>
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
</div>

<?php
include("includes/footer.php");
?>





