<?php
	// Add live Javascript searching/filtering to tables.
	// (C) 2017 CubicleSoft.  All Rights Reserved.

	class BB_AdminPack_TableFilter
	{
		public static function Init(&$state, &$options)
		{
			$state["modules_table_filter"] = false;
		}

		public static function TableRow(&$state, $num, &$field, $idbase, $type, $rownum, &$trattrs, &$colattrs, &$row)
		{
			global $bb_formtables;

			if ($bb_formtables && isset($field["filter"]) && $field["filter"])
			{
				if ($type == "head")
				{
					// Queue up the necessary Javascript for later output.
					ob_start();
					if ($state["modules_table_filter"] === false)
					{
?>
<style type="text/css">
td.filter_highlight { background-color: #FFFFCC; background-color: rgba(255, 255, 0, 0.1); }
</style>
<script type="text/javascript" src="<?php echo htmlspecialchars($state["rooturl"] . "/" . $state["supportpath"] . "/bindWithDelay.js"); ?>"></script>
<script type="text/javascript" src="<?php echo htmlspecialchars($state["rooturl"] . "/" . $state["supportpath"] . "/jquery.filtertable.min.js"); ?>"></script>
<script type="text/javascript">
function AdminPack_Module_TableFilter_Stripe(term, table) {
	table.find('tr').removeClass('altrow').filter(':visible:odd').addClass('altrow');
}
</script>
<?php

						$state["modules_table_filter"] = "";
					}

					$options = array(
						"highlightClass" => "filter_highlight",
						"minRows" => 0
					);

					// Allow each filterTable instance to be fully customized beyond basic support.
					// Valid options:  https://github.com/sunnywalker/jQuery.FilterTable
					if (isset($field["filter_options"]))
					{
						foreach ($field["filter_options"] as $key => $val)  $options[$key] = $val;
					}

?>
			<script type="text/javascript">
			if (jQuery.fn.filterTable)
			{
				var options = <?php echo json_encode($options, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); ?>;
<?php
				if (!isset($field["filter_callbacks"]))  $field["filter_callbacks"] = array();
				$field["filter_callbacks"]["callback"] = "AdminPack_Module_TableFilter_Stripe";

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

				$('#<?php echo BB_JSSafe($idbase); ?>').filterTable(options);

				AdminPack_Module_TableFilter_Stripe('', $('#<?php echo BB_JSSafe($idbase); ?>'));
			}
			else
			{
				alert('<?php echo BB_JSSafe(BB_Translate("Warning:  Missing jQuery filterTable plugin for table searcing/filtering.\n\nThis feature requires AdminPack Modules table-filter.")); ?>');
			}
			</script>
<?php
					$state["modules_table_filter"] .= ob_get_contents();
					ob_end_clean();
				}
			}
		}

		public static function Finalize(&$state)
		{
			if ($state["modules_table_filter"] !== false)  echo $state["modules_table_filter"];
		}
	}

	// Register form handlers.
	if (function_exists("BB_RegisterPropertyFormHandler"))
	{
		BB_RegisterPropertyFormHandler("init", "BB_AdminPack_TableFilter::Init");
		BB_RegisterPropertyFormHandler("table_row", "BB_AdminPack_TableFilter::TableRow");
		BB_RegisterPropertyFormHandler("finalize", "BB_AdminPack_TableFilter::Finalize");
	}
?>