<?php
  require_once 'lib/user_session.php'; 
  $user = new UserSession(); 
  $err_msg = ""; 
  if ($user->is_login()){
    header('location:index.php'); 
    die(); 
  }  
  
  if (isset($_POST['submit'])){
    $ok = $user->login_normal_user($_POST['username'],$_POST['password']); 
    if ($ok){
      header("location:index.php"); 
      die(); 
    }
    $err_msg = "Username or Password not valid"; 
  }else{
  //detect facebook login 
  $user->login_from_facebook('index.php');    
  }
?>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
	<head>
		<title>Facebook Login</title>
	</head>
	<body>
	<form action="login.php" method="post">
	<label for="username">Username</label>
	<input type="text" name="username" />
	<label for="password">Username</label>	
	<input type="password" name="password"> 
	
	<button type="submit" name="submit">Login</button>
	</form>
		<p><?php echo $err_msg; ?></p>
		<a href="<?php echo $user->get_facebook_login_url(); ?>">Login with Facebook</a>
	</body>
</html>