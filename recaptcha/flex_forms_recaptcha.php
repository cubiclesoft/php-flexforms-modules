<?php
	// Add a reCAPTCHA field type.
	// (C) 2017 CubicleSoft.  All Rights Reserved.

	class FlexForms_reCAPTCHA
	{
		public static function IsValid($secretkey, $remoteip = true, $allowedhosts = true)
		{
			if (!isset($_REQUEST["g-recaptcha-response"]))  return array("success" => false, "error" => FlexForms::FFTranslate("Missing reCAPTCHA response.  Try again."), "errorcode" => "missing_recaptcha_response");

			if (!class_exists("WebBrowser", false))  require_once str_replace("\\", "/", dirname(__FILE__)) . "/web_browser.php";

			$web = new WebBrowser();

			$options = array(
				"postvars" => array(
					"secret" => $secretkey,
					"response" => $_REQUEST["g-recaptcha-response"]
				)
			);

			if (is_array($remoteip))
			{
				if (isset($remoteip["ipv4"]) && $remoteip["ipv4"] != "")  $options["postvars"]["remoteip"] = $remoteip["ipv4"];
				else if (isset($remoteip["ipv6"]) && $remoteip["ipv6"] != "")  $options["postvars"]["remoteip"] = $remoteip["ipv6"];
				else  $remoteip = true;
			}

			if ($remoteip === true)
			{
				if (!class_exists("IPAddr", false))  require_once str_replace("\\", "/", dirname(__FILE__)) . "/ipaddr.php";

				$remoteip = IPAddr::GetRemoteIP();

				$options["postvars"]["remoteip"] = ($remoteip["ipv4"] != "" ? $remoteip["ipv4"] : $remoteip["ipv6"]);
			}
			else if (is_string($remoteip))
			{
				$options["postvars"]["remoteip"] = $remoteip;
			}

			$result = $web->Process("https://www.google.com/recaptcha/api/siteverify", $options);
			if (!$result["success"])  return array("success" => false, "error" => FlexForms::FFTranslate("An error occurred while validating the reCAPTCHA response.  Try again in a bit."), "errorcode" => "server_communication_error", "info" => $result);

			$result2 = @json_decode($result["body"], true);
			if (!is_array($result2) || !isset($result2["success"]))  return array("success" => false, "error" => FlexForms::FFTranslate("An error occurred while processing the reCAPTCHA server response.  Try again in a bit."), "errorcode" => "response_decode_failed", "info" => $result);

			if (!$result2["success"])  return array("success" => false, "error" => FlexForms::FFTranslate("The reCAPTCHA server responsed with '%s'.  Try again in a bit.", htmlspecialchars(implode("', '", $result2["error-codes"]))), "errorcode" => "recaptcha_error", "info" => $result2);

			if (is_string($allowedhosts))  $allowedhosts = array($allowedhosts);
			if (is_array($allowedhosts))
			{
				$found = false;
				foreach ($allowedhosts as $hostname)
				{
					if ($result2["hostname"] === $hostname)  $found = true;
				}

				if (!$found)  return array("success" => false, "error" => FlexForms::FFTranslate("The website where this reCAPTCHA was solved is not any of the allowed hosts."), "errorcode" => "recaptcha_location_error", "info" => $result2);
			}

			return $result2;
		}

		public static function Init(&$state, &$options)
		{
			if (!isset($state["modules_recaptcha"]))  $state["modules_recaptcha"] = false;
		}

		public static function FieldType(&$state, $num, &$field, $id)
		{
			if ($field["type"] === "recaptcha" && isset($field["sitekey"]) && isset($field["size"]))
			{
				$id .= "_recaptcha";

?>
<div class="staticwrap"><div id="<?php echo htmlspecialchars($id); ?>"></div></div>
<?php

				if ($state["modules_recaptcha"] === false)
				{
					ob_start();
?>
FlexForms.modules.reCAPTCHA_renderinfo = [];
FlexForms_modules_reCAPTCHA_Init = function() {
	for (var x = 0; x < FlexForms.modules.reCAPTCHA_renderinfo.length; x++)
	{
		(function(info) {
			if (info.form) {
				info.options.callback = function(token) {
					HTMLFormElement.prototype.submit.call(info.form);
				};
			}

			var widgetid = grecaptcha.render(info.id, info.options);

			if (info.form) {
				info.form.addEventListener('submit', function(e) {
					e.preventDefault();
					grecaptcha.execute(widgetid);
				});
			}
		})(FlexForms.modules.reCAPTCHA_renderinfo[x]);
	}
};
<?php
					$state["js"]["modules-recaptcha-init"] = array("mode" => "inline", "dependency" => false, "src" => ob_get_contents(), "detect" => "FlexForms_modules_reCAPTCHA_Init");
					ob_end_clean();

					$state["modules_recaptcha"] = true;
				}

				$options = array(
					"sitekey" => $field["sitekey"],
					"size" => $field["size"]
				);

				if ($field["size"] == "invisible")  $options["badge"] = "inline";

				// Allow reCAPTCHA to be fully customized beyond basic support.
				// Valid options:
				//   https://developers.google.com/recaptcha/docs/invisible
				//   https://developers.google.com/recaptcha/docs/display
				if (isset($field["options"]))
				{
					foreach ($field["options"] as $key => $val)  $options[$key] = $val;
				}

				// Queue up the necessary Javascript for later output.
				ob_start();
?>
FlexForms.modules.reCAPTCHA_renderinfo.push({
	id: '<?php echo FlexForms::JSSafe($id); ?>',
	options: <?php echo json_encode($options, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); ?><?php
				if ($field["size"] == "invisible")
				{
					// Invisible reCAPTCHA (v3).
?>,
	form: document.getElementById('<?php echo $state["formid"]; ?>')
<?php
				}
?>
});
<?php
				$state["js"]["modules-recaptcha|" . $id] = array("mode" => "inline", "dependency" => "modules-recaptcha-init", "src" => ob_get_contents());
				ob_end_clean();

				// Always update the dependency to the most recent ID.
				$state["js"]["modules-recaptcha"] = array("mode" => "src", "dependency" => "modules-recaptcha|" . $id, "src" => "https://www.google.com/recaptcha/api.js?onload=FlexForms_modules_reCAPTCHA_Init&render=explicit", "detect" => "grecaptcha");
			}
		}
	}

	// Register form handlers.
	if (is_callable("FlexForms::RegisterFormHandler"))
	{
		FlexForms::RegisterFormHandler("init", "FlexForms_reCAPTCHA::Init");
		FlexForms::RegisterFormHandler("field_type", "FlexForms_reCAPTCHA::FieldType");
	}
?>