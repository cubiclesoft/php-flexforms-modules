<?php
	// Add a text counter option to text elements.
	// (C) 2017 CubicleSoft.  All Rights Reserved.

	class BB_AdminPack_TextCounter
	{
		public static function Init(&$state, &$options)
		{
			$state["modules_textcounter"] = false;
		}

		public static function FieldType(&$state, $num, &$field)
		{
			if (($field["type"] === "text" || $field["type"] === "textarea") && isset($field["counter"]) && (is_int($field["counter"]) || $field["counter"]))
			{
				$id = "f" . $num . "_" . $field["name"];

				// Alter the field.
				if (isset($field["desc"]))
				{
					$field["htmldesc"] = htmlspecialchars($field["desc"]);
					unset($field["desc"]);
				}

				if (isset($field["htmldesc"]))  $field["htmldesc"] .= "<br>";
				else  $field["htmldesc"] = "";

				$field["htmldesc"] .= "<span id=\"" . $id . "_textcounter\"></span>";

				// Queue up the necessary Javascript for later output.
				ob_start();
				if ($state["modules_textcounter"] === false)
				{
?>
	<link rel="stylesheet" href="<?php echo htmlspecialchars($state["rooturl"] . "/" . $state["supportpath"] . "/jquery.textcounter.css"); ?>" type="text/css" media="all" />
	<script type="text/javascript" src="<?php echo htmlspecialchars($state["rooturl"] . "/" . $state["supportpath"] . "/jquery.textcounter.js"); ?>"></script>
<?php

					$state["modules_textcounter"] = "";
				}

				$options = array(
					"target" => "#" . $id . "_textcounter"
				);

				if (is_int($field["counter"]))  $options["limit"] = $field["counter"];

				// Allow each TextCounter instance to be fully customized beyond basic support.
				// Valid options:  See 'jquery.textcounter.js' file.
				if (isset($field["counter_options"]))
				{
					foreach ($field["counter_options"] as $key => $val)  $options[$key] = $val;
				}

?>
			<script type="text/javascript">
			jQuery(function() {
				var options = <?php echo json_encode($options, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); ?>;

<?php
				if (!isset($field["counter_callbacks"]))  $field["counter_callbacks"] = array();

				if (isset($field["counter_callbacks"]))
				{
					foreach ($field["counter_callbacks"] as $key => $val)
					{
?>
				options['<?php echo $key; ?>'] = <?php echo $val; ?>;
<?php
					}
				}
?>

				$('#<?php echo BB_JSSafe($id); ?>').TextCounter(options);
			});
			</script>
<?php
				$state["modules_textcounter"] .= ob_get_contents();
				ob_end_clean();
			}
		}

		public static function Finalize(&$state)
		{
			if ($state["modules_textcounter"] !== false)  echo $state["modules_textcounter"];
		}
	}

	// Register form handlers.
	if (function_exists("BB_RegisterPropertyFormHandler"))
	{
		BB_RegisterPropertyFormHandler("init", "BB_AdminPack_TextCounter::Init");
		BB_RegisterPropertyFormHandler("field_type", "BB_AdminPack_TextCounter::FieldType");
		BB_RegisterPropertyFormHandler("finalize", "BB_AdminPack_TextCounter::Finalize");
	}
?>