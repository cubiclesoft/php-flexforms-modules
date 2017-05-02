<?php
	// Add visual HTML editing via TinyMCE.  It's NOT my favorite visual HTML editor, but it works.
	// (C) 2017 CubicleSoft.  All Rights Reserved.

	class FlexForms_HTMLEdit
	{
		public static function Init(&$state, &$options)
		{
			if (!isset($state["modules_htmledit_tinymce"]))  $state["modules_htmledit_tinymce"] = false;
		}

		public static function FieldType(&$state, $num, &$field, $id)
		{
			if ($field["type"] === "textarea" && isset($field["html"]) && $field["html"])
			{
				if ($state["modules_htmledit_tinymce"] === false)
				{
					// While TinyMCE itself doesn't require jQuery, the code later on does.
					$state["js"]["modules-htmledit-tinymce"] = array("mode" => "src", "dependency" => "jquery", "src" => $state["supporturl"] . "/tinymce/tinymce.min.js", "detect" => "tinymce");

					$state["modules_htmledit_tinymce"] = true;
				}

				$options = array(
					"selector" => "#" . $id,
					"menubar" => false,
					"plugins" => "lists link paste contextmenu textpattern autolink autoresize",
					"toolbar" => "bold italic superscript subscript undo redo paste link numlist bullist indent outdent | formatselect blockquote",
					"autoresize_max_height" => 600,
					"autoresize_bottom_margin" => 0,
					"content_css" => array(
						$state["supporturl"] . "/flex_forms_htmledit.css"
					)
				);

				// Allow each TinyMCE instance to be fully customized beyond basic support.
				// Valid options:  https://www.tinymce.com/docs/configure/integration-and-setup/
				if (isset($field["html_options"]))
				{
					foreach ($field["html_options"] as $key => $val)  $options[$key] = $val;
				}

				// Queue up the necessary Javascript for later output.
				ob_start();
?>
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
<?php
				$state["js"][$id] = array("mode" => "inline", "dependency" => "modules-htmledit-tinymce", "src" => ob_get_contents());
				ob_end_clean();
			}
		}
	}

	// Register form handlers.
	if (is_callable("FlexForms::RegisterFormHandler"))
	{
		FlexForms::RegisterFormHandler("init", "FlexForms_HTMLEdit::Init");
		FlexForms::RegisterFormHandler("field_type", "FlexForms_HTMLEdit::FieldType");
	}
?>