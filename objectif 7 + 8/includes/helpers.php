<?php

//Checks if the current request was made via AJAX (XMLHttpRequest)
function isAjaxRequest() {
    // check for the HTTP_X_REQUESTED_WITH header and verify its value
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

//Sends either a JSON response (for AJAX) or redirects (for normal requests)
function sendResponse($success, $message = '', $data = []) {
    if (isAjaxRequest()) {
        // For AJAX requests: return JSON with merged data
        $response = array_merge(['success' => $success, 'message' => $message], $data);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } else {
        // For normal requests: redirect with success/error parameter
        $param = $success ? 'success' : 'error';
        $location = "../admin.php" . (!empty($message) ? "?{$param}=" . urlencode($message) : "");
        header("Location: " . $location);
        exit;
    }
}