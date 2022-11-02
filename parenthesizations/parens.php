#!/usr/bin/env php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '4G');

class Evaluation
{
	public $answer;
	public $expression;
	public function __construct($answer, $expression) {
		$this->answer = $answer;
		$this->expression = $expression;
	}
}
// Returns an array of Evalution objects
function getAllPossibleAnswers(string $sequence, array &$memo = [], bool $isTopLevelCall = false): array
{
	$len = strlen($sequence);
	if ($len == 0) {
		throw new Exception("I don't think length == 0 should ever have happened");
	}

	if (isset($memo[$sequence])) {
		return $memo[$sequence];
	}

	if ($len == 1) {
		$memo[$sequence] = [new Evaluation($sequence, $sequence)];
		return $memo[$sequence];
	}
	if ($len == 2) {
		//echo "Base Case: " . $sequence . "\n";
		switch ($sequence) {
			case "ac":
				$memo[$sequence] = [new Evaluation("a", "ac")];
				return $memo[$sequence];
			case "bc":
				$memo[$sequence] = [new Evaluation("a", "bc")];
				return $memo[$sequence];
			case "ca":
				$memo[$sequence] = [new Evaluation("a", "ca")];
				return $memo[$sequence];
			case "aa":
				$memo[$sequence] = [new Evaluation("b", "aa")];
				return $memo[$sequence];
			case "ab":
				$memo[$sequence] = [new Evaluation("b", "ab")];
				return $memo[$sequence];
			case "bb":
				$memo[$sequence] = [new Evaluation("b", "bb")];
				return $memo[$sequence];
			case "ba":
				$memo[$sequence] = [new Evaluation("c", "ba")];
				return $memo[$sequence];
			case "cb":
				$memo[$sequence] = [new Evaluation("c", "cb")];
				return $memo[$sequence];
			case "cc":
				$memo[$sequence] = [new Evaluation("c", "cc")];
				return $memo[$sequence];
			default:
				throw new Exception("Missed a case, I guess -- didn't think this should have been possible. Good luck");
		}
	}
	// The length is three or more. We're gonna walk through all permutations of "stuff on the left side" and
	// "stuff on the right side"
	$ourPossibleAnswers = [];
	for ($i = 1; $i < strlen($sequence); $i++) {
		$leftSide = substr($sequence, 0, $i);
		$rightSide = substr($sequence, $i);
		if ($isTopLevelCall) {
			echo "trying leftSide: $leftSide, rightSide = $rightSide\n";
		}
		$leftSidePossibleAnswers = getAllPossibleAnswers($leftSide, $memo);
		$rightSidePossibleAnswers = getAllPossibleAnswers($rightSide, $memo);
		foreach ($leftSidePossibleAnswers as $leftSideAnswer) {
			foreach ($rightSidePossibleAnswers as $rightSideAnswer) {
				$ourPossibleAnswer = getAllPossibleAnswers($leftSideAnswer->answer . $rightSideAnswer->answer, $memo);
				// There's only one possible answer for a sequence of length two:
				$ourPossibleAnswer = clone $ourPossibleAnswer[0];
				if (strlen($leftSide) == 1) {
					// Don't put parens around single characters, for readability:
					$ourExpression = $leftSideAnswer->expression;
				} else {
					$ourExpression = "(" . $leftSideAnswer->expression . ")";
				}
				if (strlen($rightSide) == 1) {
					// Don't put parens around single characters, for readability:
					$ourExpression .= $rightSideAnswer->expression;
				} else {
					$ourExpression .= "(" . $rightSideAnswer->expression . ")";
				}
				$ourPossibleAnswer->expression = $ourExpression;
				$ourPossibleAnswers []= $ourPossibleAnswer;
			}
		}
	}
	$memo[$sequence] = $ourPossibleAnswers;
	return $memo[$sequence];
}

//$result = getAllPossibleAnswers("a");
//foreach ($result as $possibleAnswer) {
//	echo "We got: " . $possibleAnswer->answer . ", via " . $possibleAnswer->expression . "\n";
//}
//echo "===================\n\n";
//
//$result = getAllPossibleAnswers("aa");
//foreach ($result as $possibleAnswer) {
//	echo "We got: " . $possibleAnswer->answer . ", via " . $possibleAnswer->expression . "\n";
//}
//echo "===================\n\n";
//
//$result = getAllPossibleAnswers("aaa");
//foreach ($result as $possibleAnswer) {
//	echo "We got: " . $possibleAnswer->answer . ", via " . $possibleAnswer->expression . "\n";
//}
//echo "===================\n\n";
//
//$result = getAllPossibleAnswers("aaaa");
//foreach ($result as $possibleAnswer) {
//	echo "We got: " . $possibleAnswer->answer . ", via " . $possibleAnswer->expression . "\n";
//}
//echo "===================\n\n";
//$memo = [];
//$result = getAllPossibleAnswers("aaaaaaaa", $memo, true);
//foreach ($result as $possibleAnswer) {
//	echo "We got: " . $possibleAnswer->answer . ", via " . $possibleAnswer->expression . "\n";
//}
//echo "===================\n\n";

/**
 * @param string $sequence
 * @return bool
 * @throws Exception
 */
function canParenthesizeToA(string $sequence): bool
{
	$memo = [];
	$possibleAnswers = getAllPossibleAnswers($sequence, $memo, true);
	$answersWithA = array_filter($possibleAnswers, function($evaluation) { return $evaluation->answer == "a"; });
	if (count($answersWithA)) {
		foreach ($answersWithA as $answerWithA) {
			echo "Found: " . $answerWithA->answer . ", via " . $answerWithA->expression . "\n";
		}
		return true;
		unset($memo);
	}
	unset($memo);
	return false;
}

$testCases = [
		'a' => ["a", true],
		'b' => ["b", false],
		'c' => ["c", false],

		'aa' => ["aa", false],
		'ab' => ["ab", false],
		'ac' => ["ac", true],

		'ba' => ["ba", false],
		'bb' => ["bb", false],
		'bc' => ["bc", true],

		'ca' => ["ca", true],
		'cb' => ["cb", false],
		'cc' => ["cc", false],

		'aaa' => ["aaa", false],
		'aab' => ["aab", false],
		'aac' => ["aac", true],

		'bbbbac' => ["bbbbac", true],
		'bbac' => ["bbac", true],
		// The "exhaustive" list of test cases removed for brevity
];

function testRunner($testCases)
{
	foreach ($testCases as $id => $testCase) {
		$result = call_user_func_array('canParenthesizeToA', [$testCase[0]]);
		if ($result != $testCase[1]) {
			echo "Test Case \"$id\": expected " . ($testCase[1] ? "true" : "false") .
					", but got " . ($result ? "true": "false") . "\n";
			return;
		}
		echo "Test Case $id: PASSED\n";
	}
}

testRunner($testCases);

