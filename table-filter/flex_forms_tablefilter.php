<?php
	// Add live Javascript searching/filtering to tables.
	// (C) 2017 CubicleSoft.  All Rights Reserved.

	class FlexForms_TableFilter
	{
		public static function Init(&$state, &$options)
		{
			if (!isset($state["modules_table_filter"]))  $state["modules_table_filter"] = false;
		}

		public static function TableRow(&$state, $num, &$field, $idbase, $type, $rownum, &$trattrs, &$colattrs, &$row)
		{
			if ($state["formtables"] && isset($field["filter"]) && $field["filter"])
			{
				if ($type == "head")
				{
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
FlexForms.modules.TableFilter_Stripe = function(term, table) {
	table.find('tr').removeClass('altrow').filter(':visible:odd').addClass('altrow');
}
<?php
						$state["js"]["modules-table-filter-stripe"] = array("mode" => "inline", "dependency" => "modules-table-filter", "src" => ob_get_contents(), "detect" => "FlexForms.modules.TableFilter_Stripe");
						ob_end_clean();

						$state["modules_table_filter"] = true;
					}

					$options = array(
						"highlightClass" => "ff_filter_highlight",
						"minRows" => 0
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
				$field["filter_callbacks"]["callback"] = "FlexForms.modules.TableFilter_Stripe";

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

				jQuery('#<?php echo FlexForms::JSSafe($idbase); ?>').filterTable(options);

				FlexForms.modules.TableFilter_Stripe('', jQuery('#<?php echo FlexForms::JSSafe($idbase); ?>'));
			}
			else
			{
				alert('<?php echo FlexForms::JSSafe(FlexForms::FFTranslate("Warning:  Missing jQuery filterTable plugin for table searcing/filtering.\n\nThis feature requires the FlexForms table-filter module.")); ?>');
			}
<?php
					$state["js"][$id] = array("mode" => "inline", "dependency" => "modules-table-filter-stripe", "src" => ob_get_contents());
					ob_end_clean();
				}
			}
		}
	}

	// Register form handlers.
	if (is_callable("FlexForms::RegisterFormHandler"))
	{
		FlexForms::RegisterFormHandler("init", "FlexForms_TableFilter::Init");
		FlexForms::RegisterFormHandler("table_row", "FlexForms_TableFilter::TableRow");
	}
?>