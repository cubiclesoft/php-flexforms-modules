<?php
	// Add one or more calendar months using table fields.
	// (C) 2022 CubicleSoft.  All Rights Reserved.

	function FF_AddCalendarMonthTableFields(&$contentopts, $startyear, $startmonth, $endyear, $endmonth, $cols, $data = array(), $monthcallback = "", $daycallback = "")
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
				if (is_callable($daycallback))  $data3 .= call_user_func_array($daycallback, array($curryear, $currmonth, $currday, $data2));
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

			$cardtemplate = "";
			foreach ($cols as $num => $col)  $cardtemplate .= "<div class=\"calendar_day_of_week\">" . htmlspecialchars($cols[$num]) . "</div>%" . ($num + 1);

			$field = array(
				"type" => "table",
				"cols" => $cols,
				"rows" => $rows,
				"card" => $cardtemplate,
				"bodyscroll" => true
			);

			if (is_callable($monthcallback))  call_user_func_array($monthcallback, array(&$field, $curryear, $currmonth));

			$contentopts["fields"][] = $field;

			$rows = array();
		}
	}

	// Add 'calendar' table support for the newer FlexForms class.
	class FlexForms_CalendarTable
	{
		public static function Init(&$state, &$options)
		{
			if (isset($options["fields"]))
			{
				do
				{
					$found = false;

					foreach ($options["fields"] as $num => $field)
					{
						if (!is_string($field) && isset($field["type"]) && $field["type"] == "calendar")
						{
							// Replace this field with one or more tables containing monthly calendar data.
							$contentopts = array("fields" => array());
							FF_AddCalendarMonthTableFields($contentopts, (isset($field["startyear"]) ? $field["startyear"] : date("Y")), (isset($field["startmonth"]) ? $field["startmonth"] : date("n")), (isset($field["endyear"]) ? $field["endyear"] : date("Y")), (isset($field["endmonth"]) ? $field["endmonth"] : date("n")), (isset($field["cols"]) ? $field["cols"] : array("S", "M", "T", "W", "T", "F", "S")), (isset($field["data"]) ? $field["data"] : array()), (isset($field["monthcallback"]) ? $field["monthcallback"] : ""), (isset($field["daycallback"]) ? $field["daycallback"] : ""));

							if (isset($field["title"]))  $contentopts["fields"][0]["title"] = $field["title"];
							if (isset($field["desc"]))  $contentopts["fields"][count($contentopts["fields"]) - 1]["desc"] = $field["desc"];
							else if (isset($field["htmldesc"]))  $contentopts["fields"][count($contentopts["fields"]) - 1]["htmldesc"] = $field["htmldesc"];

							array_splice($options["fields"], $num, 1, $contentopts["fields"]);

							$found = true;
							break;
						}
					}
				} while ($found);
			}
		}
	}

	if (is_callable("FlexForms::RegisterFormHandler"))
	{
		FlexForms::RegisterFormHandler("init", "FlexForms_CalendarTable::Init");
	}
?>