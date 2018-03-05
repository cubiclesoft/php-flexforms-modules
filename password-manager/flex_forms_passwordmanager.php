<?php
	// Add the ability to stop browser password managers from functioning for password elements.
	// (C) 2017 CubicleSoft.  All Rights Reserved.

	class FlexForms_PasswordManager
	{
		public static function Init(&$state, &$options)
		{
			if (!isset($state["modules_passwordmanager"]))  $state["modules_passwordmanager"] = false;
		}

		public static function FieldType(&$state, $num, &$field, $id)
		{
			if (($field["type"] === "password" || $field["type"] === "text") && isset($field["passwordmanager"]) && !$field["passwordmanager"])
			{
				if ($state["modules_passwordmanager"] === false)
				{
					$state["js"]["modules-passwordmanager"] = array("mode" => "src", "dependency" => "jquery", "src" => $state["supporturl"] . "/jquery.stoppasswordmanager.js", "detect" => "jQuery.fn.StopPasswordManager");

					$state["modules_passwordmanager"] = true;
				}

				// Queue up the necessary Javascript for later output.
				ob_start();
?>
			jQuery(function() {
				jQuery('#<?php echo FlexForms::JSSafe($id); ?>').StopPasswordManager();
			});
<?php
				$state["js"]["modules-passwordmanager|" . $id] = array("mode" => "inline", "dependency" => "modules-passwordmanager", "src" => ob_get_contents());
				ob_end_clean();
			}
		}
	}

	// Register form handlers.
	if (is_callable("FlexForms::RegisterFormHandler"))
	{
		FlexForms::RegisterFormHandler("init", "FlexForms_PasswordManager::Init");
		FlexForms::RegisterFormHandler("field_type", "FlexForms_PasswordManager::FieldType");
	}
?>