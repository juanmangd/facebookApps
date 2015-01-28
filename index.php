 <?php
session_start();
// added in v4.0.0
require_once 'autoload.php';

use Facebook\FacebookSession;
use Facebook\FacebookCanvasLoginHelper;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\FacebookSDKException;
use Facebook\FacebookRequestException;
use Facebook\FacebookAuthorizationException;
use Facebook\GraphObject;
use Facebook\Entities\AccessToken;
use Facebook\HttpClients\FacebookCurlHttpClient;
use Facebook\HttpClients\FacebookHttpable;


// start session

// init app with app id and secret
FacebookSession::setDefaultApplication( '414776311910769','4c520c7f28a7b2dc8c295d42be8ea8b5' );

// login helper with redirect_uri
//$helper = new FacebookCanvasLoginHelper();
$helper = new FacebookRedirectLoginHelper('http://localhost/fbapp/index.php');
  //$helper = new FacebookRedirectLoginHelper('http://localhost/fbapp/index.php' );

	// see if a existing session exists
if ( isset( $_SESSION ) && isset( $_SESSION['fb_token'] ) ) {
  // create new session from saved access_token
  $session = new FacebookSession( $_SESSION['fb_token'] );

  // validate the access_token to make sure it's still valid
  try {
    if ( !$session->validate() ) {
      $session = null;
    }
  } catch ( Exception $e ) {
    // catch any exceptions
    $session = null;
  }

} else {
  // no session exists

  try {
	$session = $helper->getSessionFromRedirect();
	//$session = $helper->getSession();
  } catch( FacebookRequestException $ex ) {
    // When Facebook returns an error
  } catch( Exception $ex ) {
    // When validation fails or other local issues
    echo $ex->message;
  }

}

// see if we have a session
if ( isset( $session ) ) {

	try {
	
  // save the session
  $_SESSION['fb_token'] = $session->getToken();
  // create a session using saved token or the new one we generated at login
  $session = new FacebookSession( $session->getToken() );
  /*
  $getFeed = (new FacebookRequest( $session, 'GET', '/me/feed' ))->execute()->getGraphObject()->asArray();
  echo '<pre>' . print_r( $getFeed, 1 ) . '</pre>';
  */
/*
  $postRequest = new FacebookRequest($session, 'POST', '/me/feed', array('link' => 'http://www.pinterest.com/pin/341710690452379641/', 'description' => 'Gym partner', 'message' => 'Easier!!!'));
  $postResponse = $postRequest->execute();
  $posting = $postResponse->getGraphObject();
  echo $posting->getProperty('id');
*/
  // uploading image to user timeline using facebook php sdk v4
	/*	$response = (new FacebookRequest(
			$session, 'POST', '/me/photos', array(
				'source' => new CURLFile('picture.jpg', 'image/jpg'), // photo must be uploaded on your web hosting
				'message' => 'User provided message'
				)
			)
		)->execute()->getGraphObject();
		if($response) {
			echo "Photo is uploaded...";
		}
		*/
  // posting on user timeline
		$postRequest = new FacebookRequest($session, 'POST', '/me/feed', array('message' => 'Wassupp guys!!!'));
		//$postResponse = $postRequest->execute();
		//$posting = $postResponse->getGraphObject();
		//echo $posting->getProperty('id');
		
  $getPages = (new FacebookRequest(
			$session,
			'GET',
			'/me/likes?limit=10000'
		))->execute()->getGraphObject()->asArray();
		foreach ($getPages['data'] as $key) 
		{
		  echo $key->name;
		  echo "<br>";
		}
		// count all liked pages
		echo count($getPages['data']);
		
  // graph api request for user data

  $friends = (new FacebookRequest( $session, 'GET', '/me/friendlists' ))->execute()->getGraphObject()->asArray();
  echo '<pre>' . print_r( $friends, 1 ) . '</pre>';

}
 catch(FacebookRequestException $e) 
 {
		echo $e->getMessage();
}

  // print logout url using session and redirect_uri (logout.php page should destroy the session)
  echo '<a href="' . $helper->getLogoutUrl( $session, 'http://localhost/fbapp/logout.php' ) . '">Logout</a>';

} else {
  // show login url
 
	$auth_url = $helper->getLoginUrl(array('read_stream','publish_actions','user_likes','email', 'user_friends','read_friendlists'));
	echo "<script>window.top.location.href='".$auth_url."'</script>";
}

?>