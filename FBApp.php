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


class FBDataClass
{
    // property declaration
    private $varAppID = '414776311910769';
	private $varAppSecret = '4c520c7f28a7b2dc8c295d42be8ea8b5';
	private $varHelper;
	private $varSession;
	
	public function GetSession()
	{
		return $this->varSession;
	}
	
    // method declaration
    public function Initialize() 
	{
		//echo "Initialize start";
		// init app with app id and secret
		 FacebookSession::setDefaultApplication( $this->varAppID,$this->varAppSecret);

		// login helper with redirect_uri
        $this->varHelper = new FacebookRedirectLoginHelper('http://localhost/fbapp/FBApp.php');
		///echo "Initialize end";
    }
	
	function InitializeSession()
	{
		// see if a existing session exists
		if ( isset( $_SESSION ) && isset( $_SESSION['fb_token'] ) ) 
		{
			// create new session from saved access_token
			$this->varSession = new FacebookSession( $_SESSION['fb_token'] );
			
			// validate the access_token to make sure it's still valid
			try 
			{
				if ( !$this->varSession->validate() ) 
				{
						$this->varSession= null;
				}
			} 
			catch ( Exception $ex ) 
			{
				// catch any exceptions
				$this->varSession = null;
				echo $ex->message;
			}
		}
		else
		{
			try 
			  {
				$this->varSession = $this->varHelper->getSessionFromRedirect();
			  } 
			  catch( FacebookRequestException $ex ) 
			  {
				//echo $ex->message;
			  } 
			  catch( Exception $ex ) 
			  {
				//echo $ex->message;
			  }
		}
		
		// see if we have a session
		if ( isset( $session ) ) 
		{
			try 
			{
				// save the session
			  $_SESSION['fb_token'] = $this->varSession->getToken();
			  // create a session using saved token or the new one we generated at login
			  $this->varSession = new FacebookSession( $this->varSession->getToken() );
			}
			catch(FacebookRequestException $e) 
			 {
					echo $e->getMessage();
			 }
		}
	}
	
	function ShowLoginURL()
	{
		$auth_url = $this->varHelper->getLoginUrl(array('read_stream','publish_actions','user_likes','email', 'user_friends','read_friendlists'));
		echo "<script>window.top.location.href='".$auth_url."'</script>";
	}
	
	function ShowLogoutURL()
	{
		// print logout url using session and redirect_uri (logout.php page should destroy the session)
		echo '<a href="' . $this->varHelper->getLogoutUrl( $this->varSession, 'http://localhost/fbapp/logout.php' ) . '">Logout</a>';
	}
	
	function GetFeeds()
	{
		$getFeed = (new FacebookRequest( $this->varSession, 'GET', '/me/feed' ))->execute()->getGraphObject()->asArray();
		echo '<pre>' . print_r( $getFeed, 1 ) . '</pre>';
	}
	
	function PostToStatus()
	{
		$postRequest = new FacebookRequest($this->varSession, 'POST', '/me/feed', array('link' => 'http://www.pinterest.com/pin/341710690452379641/', 'description' => 'Gym partner', 'message' => 'Easier!!!'));
		$postResponse = $postRequest->execute();
		$posting = $postResponse->getGraphObject();
		echo $posting->getProperty('id');
	}
	
	function UploadImageToStatus()
	{
		$response = (new FacebookRequest(
			$this->varSession, 'POST', '/me/photos', array(
				'source' => new CURLFile('picture.jpg', 'image/jpg'), // photo must be uploaded on your web hosting
				'message' => 'User provided message'
				)
			)
		)->execute()->getGraphObject();
		if($response) 
		{
			echo "Photo is uploaded...";
		}
	}
	
	function PostMessageToTimeLine()
	{
		$postRequest = new FacebookRequest($this->varSession, 'POST', '/me/feed', array('message' => 'Wassupp guys!!!'));
		$postResponse = $postRequest->execute();
		$posting = $postResponse->getGraphObject();
		echo $posting->getProperty('id');
	}
	
	function GetLikedPages()
	{
		try {
				$getPages = (new FacebookRequest(
					$this->varSession,
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
			}
			 catch(FacebookRequestException $e) 
			{
				echo $e->getMessage();
			}
	}
}

		$fbObject = new FBDataClass();
		$fbObject->Initialize();
		$fbObject->InitializeSession();
		if(null != $fbObject->GetSession())
		{
			//$fbObject->GetLikedPages();
			$fbObject->GetFeeds();
			$fbObject->ShowLogoutURL();
		}
		else
		{
			$fbObject->ShowLoginURL();
		}
?>