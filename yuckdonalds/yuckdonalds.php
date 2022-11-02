#!/usr/bin/env php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

class Location
{
	public int $distance;
	public int $profit;

	public function __construct(int $distance, int $profit)
	{
		$this->distance = $distance;
		$this->profit = $profit;
	}
}

function maxProfitFromLocations(array $locations, int $k, array &$memo = []): array
{
	$jsonLocations = json_encode($locations);
	if (isset($memo[$jsonLocations])) {
		return $memo[$jsonLocations];
	}
	echo "maxProfitFromLocations: " . $jsonLocations . "\n";
	if (count($locations) == 0) {
		$memo[$jsonLocations] = [0, []];
		return [0, []];
	}
	if (count($locations) == 1) {
		$memo[$jsonLocations] =  [$locations[0]->profit, $locations];
		return [$locations[0]->profit, $locations];
	}
	$currentLocation = $locations[count($locations) - 1];
	$nextLocation = $locations[count($locations) - 2];
	if ($currentLocation->distance - $nextLocation->distance >= $k) {
		// The solution definitely includes this location. We'll calculate the result without this location,
		// and then we'll add this location back in at the end:
		$result = maxProfitFromLocations(array_slice($locations, 0, count($locations) - 1), $k, $memo);
		$result[0] = $result[0] + $currentLocation->profit; // And our profit
		$result[1] [] = $currentLocation; // Add our location to the result
		$memo[$jsonLocations] = $result;
		return $result;
	} else {
		// There are two options: either this location is part of the solution, or it isn't:
		$resultWithoutCurrentLocation = maxProfitFromLocations(array_slice($locations, 0, count($locations) - 1), $k, $memo);
		// For the result that /does/ include the current location, we'll have to remove enough locations from the array
		// so we can hand it down to the next recursive call:
		$locationsForResultWithCurrentLocation = removeLocationsWithinDistance(
				array_slice($locations, 0, count($locations) - 1),
				$currentLocation->distance,
				$k
		);
		$locationsForResultWithCurrentLocation [] = $currentLocation;
		$resultWithCurrentLocation = maxProfitFromLocations($locationsForResultWithCurrentLocation, $k, $memo);
		if ($resultWithoutCurrentLocation[0] > $resultWithCurrentLocation[0]) {
			$memo[$jsonLocations] = $resultWithoutCurrentLocation;
			return $resultWithoutCurrentLocation;
		} else {
			$memo[$jsonLocations] = $resultWithCurrentLocation;
			return $resultWithCurrentLocation;
		}
	}
}


function removeLocationsWithinDistance(array $locations, int $distance, int $k)
{
	if (empty($locations)) {
		return [];
	}
	if ($distance - end($locations)->distance >= $k) {
		return $locations;
	} else {
		return removeLocationsWithinDistance(array_slice($locations, 0, count($locations) - 1), $distance, $k);
	}
}

$testCases = [
		'Single location: ' => [
				[ // Input args
						[
								new Location(1, 3),
						], // $locations array
						4 // $k
				], // End input args
				[ // Expected result
						3, // Expected profit
						[ // Expected Location array
								new Location(1, 3)
						]
				] // End expected result
		],
		'Two locations, both fit' => [
				[ // Input args
						[
								new Location(1, 3),
								new Location(3, 5)
						], // $locations array
						2 // $k
				], // End input args
				[ // Expected result
						8, // Expected profit
						[ // Expected Location array
								new Location(1, 3),
								new Location(3, 5)
						]
				] // End expected result
		],
		'Two locations, first location wins' => [
				[ // Input args
						[
								new Location(1, 6),
								new Location(3, 5)
						], // $locations array
						4 // $k
				], // End input args
				[ // Expected result
						6, // Expected profit
						[ // Expected Location array
								new Location(1, 6)
						]
				] // End expected result
		],
		'Two locations, last location wins' => [
				[ // Input args
						[
								new Location(1, 3),
								new Location(3, 5)
						], // $locations array
						4 // $k
				], // End input args
				[ // Expected result
						5, // Expected profit
						[ // Expected Location array
								new Location(3, 5)
						]
				] // End expected result
		],
		'Three locations, 1st and 3rd win' => [
				[ // Input args
						[ // $locations
								new Location(1, 3),
								new Location(3, 7),
								new Location(5, 5)
						],
						4 // $k
				], // End input args
				[ // Expected result
						8, // Expected profit
						[ // Expected Location array
								new Location(1, 3),
								new Location(5, 5)
						]
				] // End expected result
		],
		"Arris's test case" => [
			/**
			 * % Test Case
			% M =   1,  2,  3,   5,  8,   9, 10
			% P =  10,  5, 20,  50, 30,  35,  2
			% R = *10, 10, 20, *60, 90, *95, 95
			 */ // [W-11386320: Extend E2E org expiration dates beyond Jan 2023](https://gus.lightning.force.com/lightning/r/ADM_Work__c/a07EE000010jRF9YAM/view)
				[ // Input args
						[ // $locations
								new Location(1, 10),
								new Location(2, 5),
								new Location(3, 20),
								new Location(5, 50),
								new Location(8, 30),
								new Location(9, 35),
								new Location(10, 2)
						],
						3 // $k
				], // End input args
				[ // Expected result
						95, // Expected profit
						[ // Expected Location array
								new Location(1, 3),
								new Location(5, 5)
						]
				] // End expected result
		],

];

function testRunner($testCases)
{
	foreach ($testCases as $id => $testCase) {
		$result = call_user_func_array('maxProfitFromLocations', $testCase[0]);
		if ($result[0] != $testCase[1][0]) {
			echo "Test Case \"$id\": expected " . $testCase[1][0] . ", but got $result[0]\n";
			return;
		}
//		if (!areLocationArraysEqual($result[1], $testCase[1][1])) {
//			echo "Test Case \"$id\": location arrays are not equal. Expected:\n\t";
//			echo json_encode($testCase[1][1]);
//			echo "\nbut instead got\n\t";
//			echo json_encode($result[1]) . "\n";
//			return;
//		}
		echo "Test Case $id: PASSED\n";
	}
}

function areLocationArraysEqual($array1, $array2)
{
	if (count($array1) != count($array2)) {
		return false;
	}
	if (count($array1) == 0) {
		return true; // Both arrays are empty; no comparisons needed
	}
	for ($i = 0; $i < count($array1); $i++) {
		if ($array1[$i]->distance != $array2[$i]->distance) {
			return false;
		}
		if ($array1[$i]->profit != $array2[$i]->profit) {
			return false;
		}
	}
	return true;
}

testRunner($testCases);
