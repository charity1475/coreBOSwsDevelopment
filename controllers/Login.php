<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * Portions created by TSolucio are Copyright (C) TSolucio.
 * All Rights Reserved.
 ************************************************************************************/
class Login_Controller {

	public static function process($request) {
		if (isset($request['url'])) {
			$url = $request['url'];
		} else {
			$url = 'http://localhost/';
		}
		if (isset($request['username'])) {
			$username = $request['username'];
		} else {
			$username = 'admin';
		}
		if (isset($request['accesskey'])) {
			$accesskey = $request['accesskey'];
		} else {
			$accesskey = '';
		}
		if (isset($request['password'])) {
			$password = $request['password'];
		} else {
			$password = '';
		}
		$checkLogin = false;
		$loginModel = false;
		if (!empty($url) && !empty($username)) {
			if (!empty($accesskey) && $request['accesskeycheck']=='on') {
				$withpassword = false;
				$loginModel = new Login_Model($url, $username, $accesskey, $withpassword);
			}
			if (!empty($password) && $request['passwordcheck']=='on') {
				$withpassword = true;
				$loginModel = new Login_Model($url, $username, $password, $withpassword);
			}
		}
		if ($loginModel) {
			$client = new Vtiger_WSClient($loginModel->getURL());
			if (!empty($request['roadrunner']) && $request['roadrunner']=='on') {
				$client->_servicebase = '';
				$client->_serviceurl = $client->getWebServiceURL($url);
				$client->reinitalize();
			}
			$checkLogin = $client->doLogin($loginModel->getUsername(), $loginModel->getAccessKey(), $withpassword);

			if ($checkLogin) {
				Session_Controller::setLoginContext($loginModel);
				$loginModel->setUserId($client->_userid);
				$loginModel->setSessionId($client->_sessionid);
				header('Location: index.php');
				exit;
				//return;
			}
		}
		Header_Controller::process($request);
		?>
		<form method='POST' action='index.php' onsubmit='$("#wserrmsg").hide();this.__submitButton.value="Verifying"; this.__submitButton.disabled=true;'>
		<div class='form-group'>
			<label for='url'>URL</label><label for='roadrunner' style="margin-left: 30px;">RoadRunner</label>
			<input type="checkbox" name="roadrunner">
			<input type='text' name='url' value='<?php echo $url; ?>' size=40 class='form-control'>
		</div>
		<div class='form-group'>
			<label for='username'>Username</label>
			<input type='text' name='username' value='<?php echo $username; ?>' size=40 class='form-control'>
		</div>
		<div class='form-group'>
			<label for='accesskey'>Access key</label>
			<input type="checkbox" name="accesskeycheck" checked>
			<input type='text' name='accesskey' value='<?php echo $accesskey; ?>' size=40 class='form-control'>
		</div>
		<div class='form-group'>
			<label for='accesskey'>Password</label>
			<input type="checkbox" name="passwordcheck">
			<input type='text' name='password' value='' size=40 class='form-control'>
		</div>
		<div class='form-group'>
			<input class='btn btn-primary btn-large' type='submit' value='Login &raquo;' name='__submitButton'>
		</div>
		</form>
		<?php
		if (!empty($url) && !empty($username) && (!empty($accesskey) || !empty($password)) && !$checkLogin) {
			echo '<div class="alert alert-danger" id="wserrmsg">There is an error with the validation data given, please review and try again.</div>';
		}
		Footer_Controller::process($request);
	}
}
?>