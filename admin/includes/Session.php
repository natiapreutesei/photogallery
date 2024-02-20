<?php
class Session{
    /* properties */
    private $signed_in=false;
    public $user_id;
    private $message;
    /* methods */

    /**
     * Verifies if a user is currently logged in by checking the session data.
     * This private method is crucial for session management within the application, ensuring a secure and consistent user experience.
     *
     * Explanation:
     * - User authentication and session management are core features of many web applications, allowing personalized and secure access to resources.
     * - This method checks the PHP session for a specific user ID, which, if present, indicates an active login state for the user.
     * - By examining the session data, the method dynamically adjusts the session object's state to reflect the user's current authentication status.
     *
     * Process:
     * 1. It checks if the `user_id` is set in the session, indicating an active user session.
     * 2. If `user_id` exists in the session, the method sets the session object's `user_id` property to this value and marks the user as signed in.
     * 3. If `user_id` is not found in the session, indicating no active login, it clears the object's `user_id` property and marks the user as not signed in.
     *
     * Importance:
     * - This method provides a foundational check that influences how the application responds to and interacts with the user, affecting access control and content personalization.
     * - It acts as a gateway, ensuring that user-specific actions and data are only accessible to authenticated users, thereby enhancing application security.
     *
     * Usage:
     * This method is typically called within the session management workflow, often during the instantiation of the session object or before processing user requests.
     *
     * Example:
     * ```php
     * // Assuming the session object is instantiated and configured correctly:
     * $session->check_the_login();
     * if ($session->is_signed_in()) {
     *     echo "User is logged in.";
     * } else {
     *     echo "User is not logged in.";
     * }
     * ```
     *
     * Note:
     * - This method relies on the PHP session mechanism, so it requires that sessions are properly started and managed throughout the application.
     * - The actual login and logout logic, which manipulates the session data, is handled elsewhere. This method simply reads the current state.
     */
    private function check_the_login(){
        if(isset($_SESSION['user_id'])){
            $this->user_id = $_SESSION['user_id'];
            $this->signed_in = true;
        }else{
            unset($this->user_id);
            $this->signed_in = false;
        }
    }

    /**
     * Checks if a user is currently signed in by returning the state of the `signed_in` property.
     * This public method is a straightforward way to assess the user's authentication status across the application.
     *
     * Explanation:
     * - In web applications, managing user sessions and authentication states is crucial for security and personalized user experiences.
     * - This method encapsulates the authentication state in a simple, readable manner, allowing other parts of the application to easily check if a user is logged in.
     * - By returning the value of the `signed_in` property, it provides a unified point of reference for authentication checks, contributing to cleaner, more maintainable code.
     *
     * Process:
     * 1. The method directly returns the value of the `signed_in` property, which is a boolean indicating the user's login state.
     * 2. A value of `true` means the user is currently authenticated and signed in; `false` means the opposite.
     *
     * Importance:
     * - It simplifies the process of checking the user's login state, avoiding direct access to session data or repeated logic throughout the application.
     * - This method enhances security by centralizing the login state check, making it easier to implement consistent access controls and redirects based on authentication.
     *
     * Usage:
     * This method can be called anytime there's a need to verify a user's authentication status, such as displaying user-specific content or protecting restricted routes.
     *
     * Example:
     * ```php
     * // Assuming $session is an instance of the session management class
     * if ($session->is_signed_in()) {
     *     // Proceed with actions or content for authenticated users
     * } else {
     *     // Redirect or restrict access for unauthenticated users
     * }
     * ```
     *
     * Note:
     * - This method's effectiveness relies on accurate session management, particularly the correct setting and clearing of the `signed_in` property during login and logout procedures.
     */
    public function is_signed_in(){
        return $this->signed_in;
    }


    public function login($user){
        if($user){
            $this->user_id = $_SESSION['user_id'] = $user->id;
            $this->signed_in = true;
        }
    }


    public function logout(){
        unset($_SESSION['user_id']);
        unset($this->user_id);
        $this->signed_in = false;
    }
    public function message($msg=""){
        if(!empty($msg)){
            $_SESSION['message']= $msg;
        }else{
            return $this->message;
        }
    }
    private function check_message(){
        if(isset($_SESSION['message'])){
            $this->message = $_SESSION['message'];
            unset($_SESSION['message']);
        }else{
            $this->message = "";
        }
    }
    /* constructor */
    function __construct(){
        session_start();
        $this->check_the_login();
        $this->check_message();
    }
}
$session = new Session();
?>