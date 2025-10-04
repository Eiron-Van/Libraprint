<?php

function check_login($conn){
    if(isset($_SESSION['user_id'])){
        $id = $_SESSION['user_id'];
        
        // Use prepared statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? LIMIT 1");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result && mysqli_num_rows($result) > 0){
            $user_data = mysqli_fetch_assoc($result);
            return $user_data;
        }
    }

    //redirect to login
    header("Location: /Login");
    die;
}

function random_num($length, $conn = null){
    // Fixed length (no random length)
    if($length < 5) $length = 5;
    
    do {
        $text = "";
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'; // Include letters and numbers
        
        for($i = 0; $i < $length; $i++){
            $text .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        // If connection provided, check for uniqueness
        if($conn !== null) {
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE user_id = ?");
            $stmt->bind_param("s", $text);
            $stmt->execute();
            $result = $stmt->get_result();
            $count = $result->fetch_assoc()['count'];
            
            if($count == 0) {
                return $text; // Unique ID found
            }
        } else {
            return $text; // No uniqueness check requested
        }
        
    } while(true); // Keep generating until unique (if checking uniqueness)
}

// New function specifically for generating unique user IDs
function generate_unique_user_id($conn, $length = 8){
    do {
        $user_id = "";
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        
        for($i = 0; $i < $length; $i++){
            $user_id .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        // Check if this user_id already exists
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE user_id = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['count'];
        
    } while($count > 0); // Keep generating until we find a unique one
    
    return $user_id;
}