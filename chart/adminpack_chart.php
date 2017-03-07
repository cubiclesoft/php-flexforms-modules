<?php
	// Add visually appealing charts for dashboards/reports.
	// (C) 2017 CubicleSoft.  All Rights Reserved.

	class BB_AdminPack_Chart
	{
		public static function Init(&$state, &$options)
		{
			$state["modules_chartused"] = false;
		}

		public static function FieldType(&$state, $num, &$field)
		{
			if ($field["type"] === "chart")
			{
				$id = "f" . $num . "_chart";

?>
<div id="<?php echo htmlspecialchars($id); ?>"></div>
<?php
				if (!$state["modules_chartused"])
				{
?>
<link rel="stylesheet" href="<?php echo htmlspecialchars($state["rooturl"] . "/" . $state["supportpath"] . "/c3.css"); ?>" type="text/css" media="all" />
<script type="text/javascript" src="<?php echo htmlspecialchars($state["rooturl"] . "/" . $state["supportpath"] . "/d3-3.5.17.min.js"); ?>"></script>
<script type="text/javascript" src="<?php echo htmlspecialchars($state["rooturl"] . "/" . $state["supportpath"] . "/c3-0.4.11.min.js"); ?>"></script>
<script type="text/javascript">
var adminpack_module_c3_charts = {};
</script>
<?php
					$state["modules_chartused"] = true;
				}

				$options = array(
					"bindto" => "#" . $id
				);

				$size = array();
				if (isset($field["width"]))  $size["width"] = (int)$field["width"];
				if (isset($field["height"]))  $size["height"] = (int)$field["height"];
				if (count($size))  $options["size"] = $size;

				if (isset($field["colors"]))  $options["color"] = array("pattern" => $field["colors"]);

				if (isset($field["thresholds"]))
				{
					if (!isset($options["color"]))  $options["color"] = array();
					$options["color"]["threshold"] = array("values" => $field["thresholds"]);
				}

				$options["data"] = array(
					"type" => $field["chart"]
				);

				if (isset($field["data"]))
				{
					$num = 0;
					$options["data"]["columns"] = array();

					if (isset($field["x"]))
					{
						$options["data"]["x"] = "-chart-x-axis";
						$vals = $field["x"];
						array_unshift($vals, "-chart-x-axis");
						$options["data"]["columns"][] = $vals;
					}

					foreach ($field["data"] as $key => $vals)
					{
						array_unshift($vals, $key);
						$options["data"]["columns"][] = $vals;
					}
				}

				if ($field["chart"] == "step" && isset($field["step"]))  $options["line"] = array("step" => array("type" => $field["step"]));
				if ($field["chart"] == "pie")  $options["pie"] = array("expand" => false);
				if ($field["chart"] == "donut")  $options["donut"] = array("expand" => false);
				if ($field["chart"] == "gauge")  $options["gauge"] = array("expand" => false);

				// Allow the chart to be fully customized beyond basic support.
				// Uses dot notation for array key references:  http://c3js.org/reference.html
				if (isset($field["options"]))
				{
					foreach ($field["options"] as $key => $val)
					{
						$parts = explode(".", $key);

						self::SetNestedPathValue($options, $parts, $val);
					}
				}

?>
<script type="text/javascript">
(function() {
	var options = <?php echo json_encode($options, JSON_UNESCAPED_SLASHES); ?>;
<?php
				if (isset($field["callbacks"]))
				{
					foreach ($field["callbacks"] as $key => $val)
					{
						$parts = explode(".", $key);

?>
	options<?php foreach ($parts as $part)  echo "[" . $part . "]"; ?> = <?php echo $val; ?>;
<?php
					}
				}
?>

	adminpack_module_c3_charts['<?php echo BB_JSSafe($id); ?>'] = c3.generate(options);
})();
</script>
<?php
			}
		}

		public static function SetNestedPathValue(&$data, $pathparts, $val)
		{
			$curr = &$data;
			foreach ($pathparts as $key)
			{
				if (!isset($curr[$key]))  $curr[$key] = array();

				$curr = &$curr[$key];
			}

			$curr = $val;
		}
	}


	// Register form handlers.
	if (function_exists("BB_RegisterPropertyFormHandler"))
	{
		BB_RegisterPropertyFormHandler("init", "BB_AdminPack_Chart::Init");
		BB_RegisterPropertyFormHandler("field_type", "BB_AdminPack_Chart::FieldType");
	}
?>