<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$input = json_encode(['userId' => 653, 'bedSpace' => 1]);
// We need to bypass the session check or set a session. 
// Easier to test via curl.
