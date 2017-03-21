<?php
	// Add visual HTML editing via TinyMCE.  It's NOT my favorite visual HTML editor, but it works.
	// (C) 2017 CubicleSoft.  All Rights Reserved.

	class BB_AdminPack_HTMLEdit
	{
		public static function Init(&$state, &$options)
		{
			$state["modules_htmledit"] = false;
		}

		public static function FieldType(&$state, $num, &$field)
		{
			if ($field["type"] === "textarea" && isset($field["html"]) && $field["html"])
			{
				$id = "f" . $num . "_" . $field["name"];

				// Queue up the necessary Javascript for later output.
				ob_start();
				if ($state["modules_htmledit"] === false)
				{
?>
<script type="text/javascript" src="<?php echo htmlspecialchars($state["rooturl"] . "/" . $state["supportpath"] . "/tinymce/tinymce.min.js"); ?>"></script>
<?php

					$state["modules_htmledit"] = "";
				}

				$options = array(
					"selector" => "#" . $id,
					"menubar" => false,
					"plugins" => "lists link paste contextmenu textpattern autolink autoresize",
					"toolbar" => "bold italic superscript subscript undo redo paste link numlist bullist indent outdent | formatselect blockquote",
					"autoresize_max_height" => 600,
					"autoresize_bottom_margin" => 0,
					"content_css" => array(
						$state["rooturl"] . "/" . $state["supportpath"] . "/adminpack_htmledit.css"
					)
				);

				// Allow each TinyMCE instance to be fully customized beyond basic support.
				// Valid options:  https://www.tinymce.com/docs/configure/integration-and-setup/
				if (isset($field["html_options"]))
				{
					foreach ($field["html_options"] as $key => $val)  $options[$key] = $val;
				}

?>
			<script type="text/javascript">
			jQuery(function() {
				var options = <?php echo json_encode($options, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); ?>;

<?php
				if (!isset($field["html_callbacks"]))  $field["html_callbacks"] = array();

				if (isset($field["html_callbacks"]))
				{
					foreach ($field["html_callbacks"] as $key => $val)
					{
?>
				options['<?php echo $key; ?>'] = <?php echo $val; ?>;
<?php
					}
				}
?>

				tinymce.init(options);
			});
			</script>
<?php
				$state["modules_htmledit"] .= ob_get_contents();
				ob_end_clean();
			}
		}

		public static function Finalize(&$state)
		{
			if ($state["modules_htmledit"] !== false)  echo $state["modules_htmledit"];
		}
	}

	// Register form handlers.
	if (function_exists("BB_RegisterPropertyFormHandler"))
	{
		BB_RegisterPropertyFormHandler("init", "BB_AdminPack_HTMLEdit::Init");
		BB_RegisterPropertyFormHandler("field_type", "BB_AdminPack_HTMLEdit::FieldType");
		BB_RegisterPropertyFormHandler("finalize", "BB_AdminPack_HTMLEdit::Finalize");
	}
?>