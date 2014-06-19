<?php
	// Adds one or more calendar months to table fields.
	function BB_AddCalendarMonthTableFields(&$contentopts, $startyear, $startmonth, $endyear, $endmonth, $data = array(), $monthcallback = "", $daycallback = "")
	{
		$startyear = (int)$startyear;
		$startmonth = (int)$startmonth;
		$endyear = (int)$endyear;
		$endmonth = (int)$endmonth;

		$ts = mktime(0, 0, 0, $startmonth, 1, $startyear);
		$endts = mktime(0, 0, 0, $endmonth + 1, 1, $endyear);

		$rows = array();
		while ($ts < $endts)
		{
			$rows2 = array();
			$weekday = date("w", $ts);
			while ($weekday)
			{
				$rows2[] = "<div class=\"calendar_outofrange\">&nbsp;</div>";
				$weekday--;
			}

			$curryear = date("Y", $ts);
			$currmonth = date("m", $ts);
			do
			{
				if (count($rows2) == 7)
				{
					$rows[] = $rows2;
					$rows2 = array();
				}

				$currday = date("d", $ts);
				$date = $curryear . "-" . $currmonth . "-" . $currday;
				$data2 = (isset($data[$date]) ? $data[$date] : false);
				$data3 = "<div class=\"calendar_item\">";
				if (function_exists($daycallback))  $data3 .= $daycallback($curryear, $currmonth, $currday, $data2);
				else if ($data2 !== false)  $data3 .= $data2;
				else  $data3 .= "<div class=\"calendar_item_date\">" . (int)$currday . "</div>";
				$data3 .= "</div>";
				$rows2[] = $data3;

				$ts = mktime(0, 0, 0, $currmonth, $currday + 1, $curryear);
			} while (date("m", $ts) == $currmonth);

			while (count($rows2) != 7)
			{
				$rows2[] = "<div class=\"calendar_outofrange\">&nbsp;</div>";
			}

			$rows[] = $rows2;

			$field = array(
				"type" => "table",
				"rows" => $rows
			);

			if (function_exists($monthcallback))  $monthcallback($field, $curryear, $currmonth);

			$contentopts["fields"][] = $field;

			$rows = array();
		}
	}
?>