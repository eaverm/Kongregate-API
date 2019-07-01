 <!doctype html>
<html lang="en">
    <head>
        <title>Kongregate API Info</title>
        <link href="bootstrap/css/bootstrap.css" rel="stylesheet">
        <link href="register.css" rel="stylesheet" type="text/css">
    </head>

    <body class="text-center">
        <div class="tab-content col-12 fixed-top">
            <div class="container">
                <form class="form-signin mb-auto" action="" method="post">
                    <h1 class="h3 mb-3 font-weight-normal text-white">Convert Kong login</h1>
                    <input type="text" name="kong_user" class="form-control" placeholder="Kong Username -or- Email" required autofocus>
                    <input type="password" name="kong_password" class="form-control " placeholder="Password" required>
                    <button class="btn btn-lg btn-primary btn-block mt-2" name="Submit">Convert</button>
                </form>
            </div>

            <?php

	            if (!empty($_POST['kong_user']))
	            {
		              $getApi = new KongAPI($_POST['kong_user'], $_POST['kong_password']);
                  $user_data = $getApi->getInfo();

	                echo '<div class="container">';
	                  $alert = is_object($user_data) == true ? 'alert-success' : 'alert-danger';
	                echo '<div id="alertwi" class="alert ' . $alert . '">';

                  if(is_object($user_data))
	                {
                    echo ("Username: $user_data->username<br>ID: $user_data->id<br>Token: $user_data->token");
	                } else {
	                  echo ("Invalid User/Password Combination");
	                }

	               echo '</div></div>';
	            }

            ?>

        </div>
        
        <script src="bootstrap/js/bootstrap.min.js"></script>
    </body>
</html>

<?php

class KongAPI {
	public function __construct($user, $pass)
	{
		$this->postData = array(
    	'username' => $user,
      'password' => $pass,
      'game_id'  => '271381'
		);

		$mypath = preg_replace('/\\\\/', '/', dirname(__FILE__));
		$rand = rand(1, 15000);
		$this->cookie_file_path = "$mypath/cookies/cookie$rand.txt";
	}

	public function getInfo()
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://www.kongregate.com/session");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $this->postData);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE );
    curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file_path);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file_path);
    curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE);
    $output = curl_exec($ch);
    $info = curl_getinfo($ch);
    $output = json_decode($output);

	  if (is_numeric($user) && strtolower($output->username) != strtolower($user))
      return "Invalid User/Pass Combination";
	  if (is_numeric($user) == false && $output->username == 'Guest')
	    return "Invalid User/Pass Combination";

    $myID   = $output->user_id;
    $myPASS = $output->game_auth_token;

    curl_setopt($ch, CURLOPT_URL, "https://cb-live.synapse-games.com/api.php?message=getUserAccount&kong_id=" . $myID . "&kong_token=" . $myPASS);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE );
    $output = curl_exec($ch);
    $output = json_decode($output);
    $info = curl_getinfo($ch);
    curl_close($ch);

    $final_user = $output->new_user;
    $final_password = $output->new_password;

    return (object) array('username' => $output->user_data->name, 'id' => $output->user_data->user_id, 'token' => $output->new_password); //"$final_user $final_password";
  }
}

?>
