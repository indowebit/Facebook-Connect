<?php
  require_once 'src/facebook.php';
  
  class UserSession {
    private $_mysql_host = 'localhost'; 
    private $_mysql_user = 'your_user'; 
    private $_mysql_pwd = 'your_password'; 
    private $_mysql_db = 'your_database';
    private $_mysql_table = 'your_table';
    
    private $facebook_app_id = 'YOUR_FACEBOOK_APP_ID'; 
    private $facebook_secret = 'YOUR_FACEBOOK_APP_SECRET'; 
    private $facebook_extended_permission = 'email,user_birthday,status_update,publish_stream,user_photos,user_videos'; 
    

    private $facebook;   
    private $facebook_uid = null;
    private $facebook_user = null;   
    public $oauth_provider = 'normal'; 
    
    public function __construct(){
      if (!session_start())
        session_start(); 
      $this->facebook = new Facebook(array(
      'appId'  => $this->facebook_app_id,
      'secret' => $this->facebook_secret 
      )); 
    }
    
    private function connect_to_db(){
      mysql_connect($this->_mysql_host,$this->_mysql_user,$this->_mysql_pwd) or die("can't connect to database"); 
      mysql_select_db($this->_mysql_db) or die("can't connect to database");       
    }
    
    private function close_db(){
      mysql_close(); 
    }
     
    public function is_login(){
      if (isset($_SESSION['username'])){
        $this->detect_user();
        return true; 
      }
      return false; 
    }
    
    private function detect_user(){
      $this->oauth_provider =  $_SESSION['oauth_provider'];   
      if ($this->oauth_provider =='facebook'){
        $this->facebook_uid = $_SESSION['oauth_id'];      
      }
    }
    
    public function get_facebook_login_url(){
      $loginUrl = $this->facebook->getLoginUrl(
    	array(
    		'scope' => $this->facebook_extended_permission
    	)
      );   
      return $loginUrl;    
    }
    
    private function get_user_facebook(){
     $this->facebook_uid = $this->facebook->getUser();  
      if ($this->facebook_uid){
        try {          
          $this->facebook_user = $this->facebook->api('/me'); 
        }catch(Exception $e){}         
      }
    }
    
    public function get_facebook_profile(){
      if ($this->is_login())
        if ($this->oauth_provider =='facebook'){
          try{
            return $this->facebook->api('/'.$this->facebook_uid);      
          }catch(Exception $e){}
        }              
    }
    
    private function save_new_fb_user_or_session(){
      if (!empty($this->facebook_user)){
        $query = "SELECT id FROM %s WHERE oauth_provider ='facebook' AND oauth_id = '%s'"; 
        $sql = sprintf($query,$this->_mysql_table,$this->facebook_uid); 
        $this->connect_to_db();
        $query = mysql_query($sql) or die(mysql_error()); 
        $result = mysql_fetch_array($query); 
        if (empty($result)){
          $query = "INSERT INTO %s (oauth_provider,oauth_id,username) VALUES('facebook','%s','%s')"; 
          $sql = sprintf($query,$this->_mysql_table,$this->facebook_user['id'],$this->facebook_user['username']);
          mysql_query($sql)or die(mysql_error()); 
        }
        $_SESSION['username'] = $this->facebook_user['username']; 
        $_SESSION['oauth_provider'] = 'facebook'; 
        $_SESSION['oauth_id'] = $this->facebook_user['id']; 
        $this->close_db();
      }
    }
    
    public function login_from_facebook($redirect =''){
      $this->get_user_facebook();
      $this->save_new_fb_user_or_session();
      if ($this->facebook_user){
        if ($redirect)
          header('location:'.$redirect); 
          die();
      }
    }
    
    private function get_facebook_logout($oauth_provider){
      if ($oauth_provider =='facebook'){
        $this->get_user_facebook();
        if ($this->facebook_user){
          return $this->facebook->getLogoutUrl(); 
        }        
      }
    }
    
    public function logout(){
      if ($this->is_login()){
        $oauth_provider = $_SESSION['oauth_provider']; 
        session_destroy(); 
        $logout_fb = $this->get_facebook_logout($oauth_provider); 
        if ($logout_fb){
          header('location:'.$logout_fb); 
        }        
      }
    }
    
    public function login_normal_user($username,$password){
        $ok = false;
        $query = "SELECT username FROM %s WHERE username ='%s' AND password = PASSWORD('%s')"; 
        $this->connect_to_db();
		$sql = sprintf($query,$this->_mysql_table,
                       mysql_real_escape_string($username),
                       mysql_real_escape_string($password)
               ); 
        $query = mysql_query($sql) or die(mysql_error()); 
        $result = mysql_fetch_array($query);   
        if (!empty($result)){
          $ok = true; 
          $_SESSION['username'] = $username; 
          $_SESSION['oauth_provider'] = 'normal'; 
        }
        $this->close_db();
        return $ok;     
    }
    
  }