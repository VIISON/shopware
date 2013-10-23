<?php

/**
 * Returns a number in a pretty format by reoplacing ',' with '.' and truncating trailing zeros.
 *
 * @param string $value
 * @return int|float
 */
function smarty_modifier_prettynumber($value)
{
	$newValue = str_replace('.', ',', $value);
	// Remove all trailing zeros
	$newValue = rtrim($newValue, '0');
	// Remove decimal delimiter if it is last character
	$newValue = rtrim($newValue, ',');
	return $newValue;
}
