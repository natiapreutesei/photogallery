<?php
class User extends Db_Object{
    /*properties*/
    public $id;
    public $username;
    public $password;
    public $first_name;
    public $last_name;
    public $deleted_at;
    public $user_image;
    public $upload_directory='assets/images/photos/users';
    public $image_placeholder='https://via.placeholder.com/62';

    public $type;
    public $size;
    public $alternate_text='';

    public $tmp_path;

    public $errors = array();
    public $upload_errors_array = array(
        UPLOAD_ERR_OK => "There is no error",
        UPLOAD_ERR_INI_SIZE => "The uploaded file exceeds the upload max_filesize from php.ini",
        UPLOAD_ERR_FORM_SIZE=> "The uplod file exeeds MAX_FILE_SIZE in php.ini voor een html form",
        UPLOAD_ERR_NO_FILE=>"No file uploaded",
        UPLOAD_ERR_PARTIAL=>"The file was partially uploaded",
        UPLOAD_ERR_NO_TMP_DIR=> "Missing a temporary folder",
        UPLOAD_ERR_CANT_WRITE=>"Failed to write to disk",
        UPLOAD_ERR_EXTENSION=>"A php extension stopped your upload",
    );

    protected static $table_name = 'users';

    /*get properties method*/
    //object variabelen retourneren als een array.
    public function get_properties(){
        return [
            'id'=> $this->id,
            'username' => $this->username,
            'password'=> $this->password,
            'first_name'=> $this->first_name,
            'last_name'=> $this->last_name,
            'user_image' =>$this->user_image,
            'deleted_at'=> $this->deleted_at,
        ];
    }

    public static function find_user_by_name($last_name){
        global $database;
        // Opkuisen/sanitize van vreemde karakters
        $last_name = $database->escape_string($last_name);
        // Gebruik van de LIKE operator voor gedeeltelijke overeenkomsten
        $result = $database->query("SELECT * FROM users WHERE last_name LIKE '%" . $last_name . "%'");
        return $result;
    }

    /*verify user*/
//    public static function verify_user($username, $password){
//        global $database;
//        $username = $database->escape_string($username);
//        $password= $database->escape_string($password);
//
//        //SELECT * FROM users WHERE username = ? and password = ? LIMIT 1
//
//        $sql = "SELECT * FROM " . self::$table_name . " WHERE ";
//        $sql .= "username = ? ";
//        $sql .= "AND password = ? ";
//        $sql .= "LIMIT 1";
//
//
//        $the_result_array = self::find_this_query($sql,[$username,$password]);
//        return !empty($the_result_array) ? array_shift($the_result_array) : false;
//    }
    public static function verify_user($username, $password){
        global $database;
        $username = $database->escape_string($username);

        // Selecteer de gebruiker op basis van de gebruikersnaam
        $sql = "SELECT * FROM " . self::$table_name . " WHERE ";
        $sql .= "username = ? LIMIT 1";

        $the_result_array = self::find_this_query($sql, [$username]);

        if(!empty($the_result_array)){
            $user = array_shift($the_result_array);

            // Nu gebruik je password_verify om het wachtwoord te controleren
            if(password_verify($password, $user->password)){
                return $user; // Gebruiker geverifieerd
            }
        }

        return false; // Gebruiker niet gevonden of wachtwoord komt niet overeen
    }


    public function picture_path_and_placeholder(){
        return empty($this->user_image) ? $this->image_placeholder : $this->upload_directory.DS.$this->user_image;
    }
    public function delete_user_image(){
        if(!empty($this->user_image)){
            $target_path = SITE_ROOT.DS.'admin'.DS.$this->picture_path_and_placeholder();
            return unlink($target_path) ? true : false;
        }
    }
    public static function find_all_users(){
        $result = static::find_this_query("SELECT * FROM " . static::$table_name ." ");
        return $result;
    }

    public function set_file($file){

        if(empty($file) || !$file || !is_array($file) || !$file['name']){
            $this->errors[]=  $this->upload_errors_array[$file['error']];
            return false;
        }else{
            //image.jpg
            $date = date('Y_m_d-H-i-s');
            $without_extension = pathinfo(basename($file['name']),PATHINFO_FILENAME);
            $extension = pathinfo(basename($file['name']),PATHINFO_EXTENSION);
            $this->user_image = $without_extension.$date.'.'.$extension;
            $this->type = $file['type'];
            $this->size = $file['size'];
            $this->tmp_path = $file['tmp_name'];
        }
    }
    public function save_user_and_image(){
        $target_path = SITE_ROOT.DS."admin".DS.$this->upload_directory.DS.$this->user_image;
        if($this->id){
            //schrijft weg naar de tabel photos
            $this->update();
            if($this->tmp_path){
                //fysisch de foto naar het target_path wegschrijven
                if(move_uploaded_file($this->tmp_path,$target_path)){
//                    if($this->create()){
                    unset($this->tmp_path);
                    return true;
//                    }
                }
            }

        }else{
            if(!empty($this->errors)){
                return false;
            }
            if(empty($this->user_image) || empty($this->tmp_path)){
                $this->errors[]= "File not available";
                return false;
            }
            if(file_exists($target_path)){
                $this->errors[]= "File {$this->user_image} EXISTS!";
            }
            if(move_uploaded_file($this->tmp_path, $target_path)){//upload in de images map(photos)
                if($this->create()){//aanmaken in de database
                    unset($this->tmp_path);
                    return true;
                }
            }else{
                $this->errors[]= "This folder has no write rights";
                return false;
            }
        }
    }
}
?>