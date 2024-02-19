<?php
global $session;
require_once("includes/header.php");


$the_message= "";
//controle of iemand was ingelogd?
if ($session->is_signed_in()) { //wanneer dit true is
    //wil zeggen dat iemand is ingelogd
    header("Location:index.php");
}

if(isset($_POST['submit'])){
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    //check of the user bestaat in de database
    $user_found = User::verify_user($username, $password);

    if($user_found){
        $session->login($user_found);
        header("Location:index.php");
    }else{
       $the_message="You entered a wrong username or password!";
    }
}else{
    $username="";
    $password="";
}
?>
<div class="container-fluid">
    <div class="row ">
        <div class="col-lg-6 offset-lg-3 my-auto">
	        <div class="auth-header d-flex align-items-center mt-5">
		        <h1 class="auth-title" style="flex-grow: 1;">Log in.</h1>
		        <div class="auth-logo" style="width: 50px; margin-right: 15px;">
			        <a href="index.php"><img src="../admin/assets/compiled/svg/logo.svg" alt="Logo" style="width: 100%;"></a>
		        </div>
            </div>

            <p class="auth-subtitle mb-4 fs-5">Log in with your data that you entered during registration.</p>
            <?php if (!empty($the_message)): ?>
		        <div class="alert alert-danger" role="alert">
                    <?php echo $the_message; ?>
		        </div>
            <?php endif; ?>
            <form action="" method="POST">
                <div class="form-group position-relative has-icon-left mb-4">
                    <input type="text" class="form-control form-control-xl" placeholder="Username" name="username">
                    <div class="form-control-icon">
                        <i class="bi bi-person"></i>
                    </div>
                </div>
                <div class="form-group position-relative has-icon-left mb-4">
                    <input type="password" class="form-control form-control-xl" placeholder="Password" name="password">
                    <div class="form-control-icon">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                </div>
                <div class="form-check form-check-lg d-flex align-items-end">
                    <input class="form-check-input me-2" type="checkbox" value="" id="flexCheckDefault">
                    <label class="form-check-label text-gray-600" for="flexCheckDefault">
                        Keep me logged in
                    </label>
                </div>
                <input type="submit" name="submit" value="Login" class="btn btn-primary btn-block btn-lg shadow-lg mt-5">

	            <a class="btn btn-lg btn-block btn-primary mt-2" href="google_auth.php" role="button" style="text-transform:none; background-color: #dd4b39;">
		            <i class="bi bi-google me-2"></i>
		            Login with Google
	            </a>

	            <!--<a href="google_auth.php" class="btn btn-info">Login with Google</a>-->
            </form>
            <div class="text-center mt-5 text-lg fs-5">
                <p class="text-gray-600">Don't have an account? <a href="auth-register.html" class="font-bold">Sign
                        up</a>.</p>
            </div>
        </div>
    </div>
</div>