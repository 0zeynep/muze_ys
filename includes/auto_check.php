<?php

function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);;
}


function has_role($required_role) {
    if (!is_logged_in()) {
        return false; 
    }
   
    return ($_SESSION['role'] ?? '') === $required_role;
}



?>
