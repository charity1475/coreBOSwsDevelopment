<?php
/*************************************************************************************************
 * coreBOSwsDevelopment - web based coreBOS Webservice Development Tool
 * Copyright 2010-2014 JPL TSolucio, S.L.  --  This file is a part of coreBOS Family.
 * You can copy, adapt and distribute the work under the "Attribution-NonCommercial-ShareAlike"
 * Vizsage Public License (the "License"). You may not use this file except in compliance with the
 * License. Roughly speaking, non-commercial users may share and modify this code, but must give credit
 * and share improvements. However, for proper details please read the full License, available at
 * http://vizsage.com/license/Vizsage-License-BY-NC-SA.html and the handy reference for understanding
 * the full license at http://vizsage.com/license/Vizsage-Deed-BY-NC-SA.html. Unless required by
 * applicable law or agreed to in writing, any software distributed under the License is distributed
 * on an  "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the
 * License terms of Creative Commons Attribution-NonCommercial-ShareAlike 3.0 (the License).
 *************************************************************************************************
 *  Author       : JPL TSolucio, S. L.
 *************************************************************************************************/
class TestCode_Controller {

	public static function process($request) {
		$loginModel = Session_Controller::getLoginContext();

		$client = new Vtiger_WSClient($loginModel->getURL());
		$login  = $client->doLogin($loginModel->getUsername(), $loginModel->getAccessKey(), $loginModel->getWithPassword());

		if ($login) {
			TestCode_Controller::doLayout();
			TestCode_Controller::doLayoutCode();
		} else {
			echo "<div class='alert alert-danger'>ERROR: Login failure!</div>";
		}
	}

	public static function doLayout() {
		$testcodescripts='';
		foreach (glob('testcode/*.{php}', GLOB_BRACE) as $tcode) {
			$tc = basename($tcode);
			$testcodescripts.="<li><a href='index.php?action=TestCode&tcload=$tc'><h5>$tc</h5></a></li>";
		}
		$testcodescripts='<ul>'.$testcodescripts.'</ul>';
		$loadtc=$loadtcdoc='';
		if (!empty($_REQUEST['tcload'])) {
			$tcl = basename($_REQUEST['tcload']);
			$loadtc = file_get_contents('testcode/'.$tcl);
			if (file_exists('testcode/'.$tcl.'.html')) {
				$loadtcdoc = file_get_contents('testcode/'.$tcl.'.html');
			} else {
				$loadtcdoc = '<br>&nbsp;No documentation associated to this script.';
			}
		}
		echo <<<EOT
		<div class="row">
			<div class="col-lg-4 pull-left"><h3><a href="javascript:void(0);" onclick="$('#cbwscodediv').show();$('#cbwstestcodediv').hide();">Code</a></h3></div>
			<div class="col-lg-1 pull-left text-center" style="top:15px;"><a href="javascript:doExecCode();" class="btn btn-primary btn-large">Execute</a></div>
			<div class="col-lg-1 pull-left text-center" style="top:15px;">
				<a href="javascript:void(0);" onclick="$('#cbwstestcodediv').show();$('#cbwscodediv').hide();" class="btn btn-primary btn-large">Load</a>
			</div>
			<div class="col-lg-1 pull-left text-center" style="top:15px;"><a href="javascript:clearAllTextareas();" class="btn btn-primary btn-large">Clear</a></div>
			<div class="col-lg-3 pull-left"><h3><a href="javascript:void(0);" onclick="$('#cbwsoutput').show();$('#cbwsdocs').hide();">Output</a></h3></div>
			<div class="col-lg-2 pull-left"><h3><a href="javascript:void(0);" onclick="$('#cbwsoutput').hide();$('#cbwsdocs').show();">Documentation</a></h3></div>
		</div>
		<div class="row">
			<div class="col-lg-7 pull-left" style="border: 0px; padding: 0px;">
				<div class="col-lg-7 pull-left" id="cbwscodediv">
				<textarea id="cbwscode" rows=50 cols=78 style="height:100px">$loadtc</textarea>
				</div>
				<div class="col-lg-7 pull-left" id="cbwstestcodediv" style="height:415px; width:98%; border: solid 1px;overflow: scroll; display:none;padding:6px;">
				$testcodescripts
				</div>
			</div>
			<div class="col-lg-5 pull-left" style="height:415px;border: 0px; padding: 0px;">
				<div id="cbwsoutput" style="height:415px;border: solid 1px;overflow: scroll;"></div>
				<div id="cbwsdocs" style="height:415px;border: solid 1px;overflow: scroll; display:none;padding:4px;">$loadtcdoc</div>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-5 pull-left"><h3>Debug</h3></div>
		</div>
		<div class="row">
			<div class="col-lg-12 pull-left" id="cbwsdebug" style="height:600px;border: solid 1px;overflow: scroll;"></div>
		</div>
EOT;
	}

	public static function doLayoutCode() {
		echo <<<EOT
<link rel="stylesheet" href="assets/codemirror/lib/codemirror.css">
<link rel="stylesheet" href="assets/codemirror/addon/dialog/dialog.css">
<script src="assets/codemirror/lib/codemirror.js"></script>
<script src="assets/codemirror/addon/edit/matchbrackets.js"></script>
<script src="assets/codemirror/addon/edit/matchtags.js"></script>
<script src="assets/codemirror/addon/edit/closebrackets.js"></script>
<script src="assets/codemirror/addon/edit/closetag.js"></script>
<script src="assets/codemirror/addon/search/search.js"></script>
<script src="assets/codemirror/addon/search/searchcursor.js"></script>
<script src="assets/codemirror/addon/dialog/dialog.js"></script>
<script src="assets/codemirror/mode/htmlmixed/htmlmixed.js"></script>
<script src="assets/codemirror/mode/xml/xml.js"></script>
<script src="assets/codemirror/mode/javascript/javascript.js"></script>
<script src="assets/codemirror/mode/css/css.js"></script>
<script src="assets/codemirror/mode/clike/clike.js"></script>
<script src="assets/codemirror/mode/php/php.js"></script>
<style type="text/css">.CodeMirror {border-top: 1px solid black; border-bottom: 1px solid black;}</style>
<script>
	function clearAllTextareas() {
		editor.setValue('');
		$('#cbwsdebug').html('');
		$('#cbwsoutput').html('');
		$('#cbwsoutput').removeClass('alert-danger');
	}
	function doExecCode() {
		var tccode = encodeURI(editor.getValue());
		$.ajax({
		  type: 'POST',
		  url: "index.php",
		  data: { action: "execCode", tcexec: tccode },
		  context: document.body
		}).done(function(oput) {
		  rdo = JSON.parse(oput);
		  if (rdo.result === false) {
			$('#cbwsoutput').addClass('alert-danger');
		  } else {
			$('#cbwsoutput').removeClass('alert-danger');
		  }
		  $('#cbwsoutput').html(rdo.output);
		  $('#cbwsdebug').html(rdo.debug);
		});
	}
	var editor = CodeMirror.fromTextArea(document.getElementById("cbwscode"), {
		lineNumbers: true,
		matchBrackets: true,
		mode: "application/x-httpd-php",
		indentUnit: 4,
		indentWithTabs: true
	});
	editor.setSize(650, 415);
</script>
EOT;
	}

	public static function doExecCode() {
		function debugmsg($name, $var = '') {
			$str = "<table border=0><tr><th align=left>$name</th></tr><tr><td>";
			$str.= print_r($var, true);
			if (is_array($var) && isset($var['body'])) {
				$str.= $var['body'];
			}
			if (is_array($var) && isset($var['xdebug_message'])) {
				$str.= $var['xdebug_message'];
			}
			$str.= "</td></tr></table>";
			return $str;
		}
		$loginModel = $_SESSION['vtbrowser_auth'];
		$cbURL = $loginModel->getURL().'/webservice.php';
		$cbUserName = $loginModel->getUsername();
		$cbUserID = $loginModel->getUserId();
		$cbAccessKey = $loginModel->getAccessKey();
		$cbWithPassword = $loginModel->getWithPassword();
		$cbSessionID = $loginModel->getSessionId();
		$cbconn = new Vtiger_WSClient($cbURL);
		$cbconn->doLogin($cbUserName, $cbAccessKey, $cbWithPassword);
		$httpc = $cbconn->_client;
		$dmsg = '';
		$toexec = urldecode($_REQUEST['tcexec']);
		$toexec = preg_replace('[<\?php|\?>]', '', $toexec);
		ob_start();
		$execrdo = eval($toexec);
		$out = ob_get_contents();
		ob_end_clean();
		$rdo = array(
			'result' => $execrdo,
			'output' => $out,
			'debug' => $dmsg
		);
		echo json_encode($rdo);
	}

	public static function doExecCodeDirect($script) {
		if (!empty($script)) {
			function debugmsg($name, $var) {
			};
			$loginModel = $_SESSION['vtbrowser_auth'];
			$cbURL = $loginModel->getURL().'/webservice.php';
			$cbUserName = $loginModel->getUsername();
			$cbUserID = $loginModel->getUserId();
			$cbAccessKey = $loginModel->getAccessKey();
			$cbSessionID = $loginModel->getSessionId();
			$cbconn = new Vtiger_WSClient($cbURL);
			$httpc = $cbconn->_client;
			$script = basename($script);
			include_once 'testcode/'.$script;
		}
	}
}
?>