<?php
global $session;
$photoCount = Photo::count_all();
$userCount = User::count_all();

$user = User::find_by_id($session->user_id);
?>

<div id="main">
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>
    </header>

    <div class="page-heading">
        <h3>
	        Content Management System - Photo Gallery
        </h3>
    </div>
    <div class="page-content">
        <section class="row">
            <div class="col-12 col-lg-9">
                <div class="row">
                    <div class="col-6 col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                        <div class="stats-icon blue mb-2">
                                            <i class="iconly-boldProfile"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Users</h6>
	                                    <h6 class="font-extrabold mb-0"><?php echo $userCount; ?></h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
	                <div class="col-6 col-lg-3 col-md-6">
		                <div class="card">
			                <div class="card-body px-4 py-4-5">
				                <div class="row">
					                <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
						                <div class="stats-icon purple mb-2">
							                <i class="iconly-boldShow"></i>
						                </div>
					                </div>
					                <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
						                <h6 class="text-muted font-semibold">Photos</h6>
						                <h6 class="font-extrabold mb-0"><?php echo $photoCount; ?></h6>
					                </div>
				                </div>
			                </div>
		                </div>
	                </div>
                    <div class="col-6 col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                        <div class="stats-icon green mb-2">
                                            <i class="iconly-boldAdd-User"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Comments</h6>
                                        <h6 class="font-extrabold mb-0">0</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                        <div class="stats-icon red mb-2">
                                            <i class="iconly-boldBookmark"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Posts</h6>
                                        <h6 class="font-extrabold mb-0">0</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-xl-4">

                    </div>

                </div>
            </div>
            <div class="col-12 col-lg-3">
                <div class="card">
                    <div class="card-body py-4 px-4">
                        <div class="d-flex flex-column align-items-center">
                            <div class="avatar avatar-xl">
                                <?php
                                // Check if the user's image path is a URL or a local file
                                $isUrl = preg_match('/^https?:\/\//', $user->user_image); // Checks if the image path starts with http:// or https://
                                // Default image if the user doesn't have one
                                $default_image = './assets/compiled/jpg/1.jpg';
                                // Determine the correct path or URL for the user image
                                $user_image = $isUrl ? $user->user_image : (!empty($user->user_image) ? 'assets/images/photos/users/' . $user->user_image : $default_image);
                                ?>
	                            <img src="<?php echo $user_image; ?>" alt="User Image">
                            </div>
                            <div class="ms-3 name mt-2">
	                            <!-- Display first name and last name -->
	                            <h5 class="font-bold"><?php echo htmlspecialchars($user->first_name) . ' ' . htmlspecialchars($user->last_name); ?></h5>
	                            <!-- Display username -->
	                            <h6 class="text-muted mb-0 d-flex justify-content-center">@<?php echo htmlspecialchars($user->username); ?></h6>
                            </div>

                            <a href="logout.php" class="text-danger display-3 text-center font-bold">
                                <i class="bi bi-power"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
