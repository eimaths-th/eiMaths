<?php

// Include the Helper file to get the replaceMixedFractions function
require_once 'app/Helpers/Helper.php';

// Test cases
echo "Testing replaceMixedFractions function:\n\n";

echo "Test 1 - MixFrac(null,(1+g),3):\n";
$result1 = replaceMixedFractions('MixFrac(null,(1+g),3)');
echo "Input: MixFrac(null,(1+g),3)\n";
echo "Output: " . $result1 . "\n\n";

echo "Test 2 - Frac((1+g),3):\n";
$result2 = replaceMixedFractions('Frac((1+g),3)');
echo "Input: Frac((1+g),3)\n";
echo "Output: " . $result2 . "\n\n";

echo "Test 3 - MixFrac(1,2,3):\n";
$result3 = replaceMixedFractions('MixFrac(1,2,3)');
echo "Input: MixFrac(1,2,3)\n";
echo "Output: " . $result3 . "\n\n";

echo "Test 4 - Frac(1,2):\n";
$result4 = replaceMixedFractions('Frac(1,2)');
echo "Input: Frac(1,2)\n";
echo "Output: " . $result4 . "\n\n";

echo "Test 5 - MixFrac(a,(1+g),3):\n";
$result5 = replaceMixedFractions('MixFrac(a,(1+g),3)');
echo "Input: MixFrac(a,(1+g),3)\n";
echo "Output: " . $result5 . "\n\n";

echo "Test 6 - Thai characters - Frac(เศษ,ส่วน):\n";
$result6 = replaceMixedFractions('Frac(เศษ,ส่วน)');
echo "Input: Frac(เศษ,ส่วน)\n";
echo "Output: " . $result6 . "\n\n";
