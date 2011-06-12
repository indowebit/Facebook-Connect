<?php
  require_once 'lib/user_session.php'; 
  $user = new UserSession(); 
  $is_login = false;
  if (isset($_GET['action'])){
    if ($_GET['action'] =='logout')
      $user->logout();
  }else{
     $is_login = $user->is_login();  
  }
?>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
	<head>
		<title>Facebook Index</title>
	</head>
	<body>
		<?php if ($is_login){ ?>
		<a href="index.php?action=logout">Logout</a>
		
		<?php if ($user->oauth_provider =='facebook') { ?>
			<p>You are using facebook account to login on this site</p>
			<img src="https://graph.facebook.com/<?php echo $_SESSION['username']; ?>/picture">
			<br />
			<?php print_r($user->get_facebook_profile()); ?>
		<?php }else { ?>
			<p>You are using normal login user</p>
		<?php } ?>
		
		
		<?php } else {?>
		<a href="login.php">Login</a>
		<?php } ?>
		
	</body>
</html>