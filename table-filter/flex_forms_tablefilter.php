<?php
	// Add live Javascript searching/filtering to tables.
	// (C) 2017 CubicleSoft.  All Rights Reserved.

	class FlexForms_TableFilter
	{
		public static function Init(&$state, &$options)
		{
			if (!isset($state["modules_table_filter"]))  $state["modules_table_filter"] = false;
		}

		public static function FieldType(&$state, $num, &$field, $id)
		{
			if ($field["type"] == "table" && isset($field["filter"]) && $field["filter"])
			{
				if (!isset($field["filterwidth"]))  $field["filterwidth"] = "20em";
				if (!isset($field["filterplaceholder"]))  $field["filterplaceholder"] = FlexForms::FFTranslate("Search this table");
				if (!isset($field["class"]))  $field["class"] = "ff_tablefilter";
				else  $field["class"] .= " ff_tablefilter";

?>
			<div class="formitemdata">
				<div class="textitemwrap tablefiltersearchwrap"<?php if (isset($field["filterwidth"]))  echo " style=\"" . ($state["responsive"] ? "max-" : "") . "width: " . htmlspecialchars($field["filterwidth"]) . ";\""; ?>><input class="text" type="text" id="<?php echo htmlspecialchars($id); ?>_tablefilter_search" placeholder="<?php echo htmlspecialchars($field["filterplaceholder"]); ?>"<?php if ($state["autofocused"] === $id)  echo " autofocus"; ?> /></div>
			</div>
<?php

				if ($state["modules_table_filter"] === false)
				{
					ob_start();
?>
<style type="text/css">
td.ff_filter_highlight { background-color: #FFFFCC; background-color: rgba(255, 255, 0, 0.1); }
</style>
<?php

					$state["css"]["modules-table-filter"] = array("mode" => "inline", "dependency" => false, "src" => ob_get_contents());
					ob_end_clean();

					$state["js"]["modules-table-filter-bindwithdelay"] = array("mode" => "src", "dependency" => "jquery", "src" => $state["supporturl"] . "/bindWithDelay.js", "detect" => "jQuery.fn.bindWithDelay");
					$state["js"]["modules-table-filter"] = array("mode" => "src", "dependency" => "modules-table-filter-bindwithdelay", "src" => $state["supporturl"] . "/jquery.filtertable.min.js", "detect" => "jQuery.fn.filterTable");

					ob_start();
?>
FlexForms.modules.TableFilter_Finalize = function(term, table) {
	table.find('tr').removeClass('altrow').removeClass('lastrow').filter(':visible:odd').addClass('altrow').filter(':last');
	table.find('tr:visible:last').addClass('lastrow');

	table.trigger('table:datachanged');
}
<?php
					$state["js"]["modules-table-filter-stripe"] = array("mode" => "inline", "dependency" => "modules-table-filter", "src" => ob_get_contents(), "detect" => "FlexForms.modules.TableFilter_Finalize");
					ob_end_clean();

					$state["modules_table_filter"] = true;
				}

				$options = array(
					"highlightClass" => "ff_filter_highlight",
					"minRows" => 0,
					"inputSelector" => "#" . $id . "_tablefilter_search"
				);

				// Allow each filterTable instance to be fully customized beyond basic support.
				// Valid options:  https://github.com/sunnywalker/jQuery.FilterTable
				if (isset($field["filter_options"]))
				{
					foreach ($field["filter_options"] as $key => $val)  $options[$key] = $val;
				}

				// Queue up the necessary Javascript for later output.
				ob_start();
?>
			if (jQuery.fn.filterTable)
			{
				var options = <?php echo json_encode($options, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); ?>;
<?php
				if (!isset($field["filter_callbacks"]))  $field["filter_callbacks"] = array();
				$field["filter_callbacks"]["callback"] = "FlexForms.modules.TableFilter_Finalize";

				if (isset($field["filter_callbacks"]))
				{
					foreach ($field["filter_callbacks"] as $key => $val)
					{
?>
				options['<?php echo $key; ?>'] = <?php echo $val; ?>;
<?php
					}
				}
?>

				jQuery('#<?php echo FlexForms::JSSafe($id); ?>_table').filterTable(options);

				jQuery('#<?php echo FlexForms::JSSafe($id); ?>_tablefilter_search').keyup();
			}
			else
			{
				alert('<?php echo FlexForms::JSSafe(FlexForms::FFTranslate("Warning:  Missing jQuery filterTable plugin for table searcing/filtering.\n\nThis feature requires the FlexForms table-filter module.")); ?>');
			}
<?php
				$state["js"]["modules-table-filter|" . $id] = array("mode" => "inline", "dependency" => "modules-table-filter-stripe", "src" => ob_get_contents());
				ob_end_clean();
			}
		}
	}

	// Register form handlers.
	if (is_callable("FlexForms::RegisterFormHandler"))
	{
		FlexForms::RegisterFormHandler("init", "FlexForms_TableFilter::Init");
		FlexForms::RegisterFormHandler("field_type", "FlexForms_TableFilter::FieldType");
	}
?>