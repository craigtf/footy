<?php
// load_profanity.php

// Load profanity list
$profanity = file('profanity.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Function to check for profanity
function containsProfanity($string, $profanityList) {
    foreach ($profanityList as $badWord) {
        if (stripos($string, $badWord) !== false) {
            return true;
        }
    }
    return false;
}
?>
