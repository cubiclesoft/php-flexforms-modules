<?php
	// Add visually appealing charts for dashboards/reports.
	// (C) 2017 CubicleSoft.  All Rights Reserved.

	class FlexForms_Chart
	{
		public static function Init(&$state, &$options)
		{
			if (!isset($state["modules_chart"]))  $state["modules_chart"] = false;
		}

		public static function FieldType(&$state, $num, &$field, $id)
		{
			if ($field["type"] === "chart")
			{
				$id .= "_chart";

?>
<div class="formitemdata">
	<div class="chartitemwrap"<?php if (isset($field["width"]))  echo " style=\"" . ($state["responsive"] ? "max-" : "") . "width: " . htmlspecialchars($field["width"]) . "\""; ?>>
		<div id="<?php echo htmlspecialchars($id); ?>"></div>
	</div>
</div>
<?php
				if ($state["modules_chart"] === false)
				{
					$state["css"]["modules-chart-c3"] = array("mode" => "link", "dependency" => false, "src" => $state["supporturl"] . "/c3.css");
					$state["js"]["modules-chart-d3"] = array("mode" => "src", "dependency" => false, "src" => $state["supporturl"] . "/d3-3.5.17.min.js", "detect" => "d3");
					$state["js"]["modules-chart-c3"] = array("mode" => "src", "dependency" => "modules-chart-d3", "src" => $state["supporturl"] . "/c3-0.4.11.min.js", "detect" => "c3");

					ob_start();
?>
FlexForms.modules.c3_charts = {};
<?php
					$state["js"]["modules-chart-c3-charts"] = array("mode" => "inline", "dependency" => "modules-chart-c3", "src" => ob_get_contents(), "detect" => "FlexForms.modules.c3_charts");
					ob_end_clean();

					$state["modules_chart"] = true;
				}

				$options = array(
					"bindto" => "#" . $id
				);

				$size = array();
				if (isset($field["width"]) && !$state["responsive"])  $size["width"] = (int)$field["width"];
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

						FlexForms::SetNestedPathValue($options, $parts, $val);
					}
				}

				// Queue up the necessary Javascript for later output.
				ob_start();
?>
(function() {
	var options = <?php echo json_encode($options, JSON_UNESCAPED_SLASHES); ?>;
<?php
				if (isset($field["callbacks"]))
				{
					foreach ($field["callbacks"] as $key => $val)
					{
						$parts = explode(".", $key);

?>
	options<?php foreach ($parts as $part)  echo "['" . $part . "']"; ?> = <?php echo $val; ?>;
<?php
					}
				}
?>

	FlexForms.modules.c3_charts['<?php echo FlexForms::JSSafe($id); ?>'] = c3.generate(options);
})();
<?php
				$state["js"]["modules-chart-c3-charts|" . $id] = array("mode" => "inline", "dependency" => "modules-chart-c3-charts", "src" => ob_get_contents());
				ob_end_clean();
			}
		}

		function GetLinearRegression($xvals, $yvals, $calcpoints = true)
		{
			// Calculate:  y = a + bx
			// Where:
			//   N = Total number of elements
			//   Sxy = SUM(x * y) - ((SUM(x) * SUM(y)) / N)
			//   Sxx = SUM(x * x) - ((SUM(x) * SUM(x)) / N)
			//   Syy = SUM(y * y) - ((SUM(y) * SUM(y)) / N)
			//   b = Sxy / Sxx
			//   a = (SUM(y) - (b * SUM(x))) / N
			//   r = Sxy / SQRT(Sxx * Syy)
			$n = count($xvals);
			if (!$n || $n != count($yvals))  return false;

			$Ex = 0;
			$Ey = 0;
			$Exy = 0;
			$Exx = 0;
			$Eyy = 0;
			foreach ($xvals as $num => $xval)
			{
				if (!isset($yvals[$num]))  return false;
				$yval = $yvals[$num];

				$Ex += $xval;
				$Ey += $yval;
				$Exy += $xval * $yval;
				$Exx += $xval * $xval;
				$Eyy += $yval * $yval;
			}

			$Sxy = $Exy - ($Ex * $Ey / $n);
			$Sxx = $Exx - ($Ex * $Ex / $n);
			$Syy = $Eyy - ($Ey * $Ey / $n);

			$b = $Sxy / $Sxx;
			$a = ($Ey - ($b * $Ex)) / $n;
			$r = $Sxy / sqrt($Sxx * $Syy);

			if (!$calcpoints)  $points = false;
			else
			{
				$points = array();
				foreach ($xvals as $xval)
				{
					$points[] = $a + ($b * $xval);
				}
			}

			return array("a" => $a, "b" => $b, "r" => $r, "points" => $points);
		}
	}

	// Register form handlers.
	if (is_callable("FlexForms::RegisterFormHandler"))
	{
		FlexForms::RegisterFormHandler("init", "FlexForms_Chart::Init");
		FlexForms::RegisterFormHandler("field_type", "FlexForms_Chart::FieldType");
	}
?>