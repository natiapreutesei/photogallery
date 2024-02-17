<?php
//include: error op de pagina,php genereert een waarschuwing,
//maar: de pagina zal wel verder uitgevoerd worden.
//require: hetzelfde als include: php genereert een fatale fout
//en stop de pagina van uitvoering
//include_once
//require_once
    require_once("includes/header.php");
    if(!$session->is_signed_in()){
        header("location:login.php");
    }
    include("includes/sidebar.php");

    $user = new User();
    if(isset($_POST['adduser'])){
        $user->username = $database->escape_string($_POST['username']);
        $user->first_name = $database->escape_string($_POST['first_name']);
        $user->last_name =$database->escape_string($_POST['last_name']);
        $user->password = password_hash($database->escape_string($_POST['password']), PASSWORD_DEFAULT);

        $user->deleted_at = '0000-00-00 00:00:00';
        $user->set_file($_FILES['user_image']);
        $user->save_user_and_image();
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
                        <h2 class="card-title">Add User</h2>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <form action="add_user.php" method="POST" enctype="multipart/form-data" class="form form-horizontal">
                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label for="username">Username</label>
                                        </div>
                                        <div class="col-md-8 form-group">
                                            <input type="text" id="username" class="form-control" name="username" placeholder="username">
                                            <div data-lastpass-icon-root="true" style="position: relative !important; height: 0px !important; width: 0px !important; float: left !important;"></div></div>
                                        <div class="col-md-4">
                                            <label for="first_name">First Name</label>
                                        </div>
                                        <div class="col-md-8 form-group">
                                            <input type="text" id="first_name" class="form-control" name="first_name" placeholder="first_name">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="last_name">Last Name</label>
                                        </div>
                                        <div class="col-md-8 form-group">
                                            <input type="text" id="last_name" class="form-control" name="last_name" placeholder="last_name">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="password">Password</label>
                                        </div>
                                        <div class="col-md-8 form-group">
                                            <input type="password" id="password" class="form-control" name="password" placeholder="password">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="user_image">User Image</label>
                                        </div>
                                        <div class="col-md-8 form-group">
                                            <input type="file" id="user_image" name="user_image" class="form-control">
                                        </div>

                                        <div class="col-sm-12 d-flex justify-content-end">
                                            <button type="submit" name="adduser" class="btn btn-primary me-1 mb-1">Add User</button>
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





