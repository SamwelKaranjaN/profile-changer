<?php
// Database credentials
$host = 'localhost';
$username = 'root';
$password = '@X6js1488';
$dbname = 'semitrials';
$port = 3306; // Specify the MySQL port

// Create MySQLi connection
$mysqli = new mysqli($host, $username, $password, $dbname, $port);

// Check connection
if ($mysqli->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed: " . $mysqli->connect_error]);
    exit;
}

// Check if the form was submitted via POST (upload request)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['croppedImage'])) {
    // Get the base64 encoded image from the form
    $imageData = $_POST['croppedImage'];

    // Remove the prefix of the base64 string (e.g., "data:image/png;base64,")
    $imageData = str_replace('data:image/png;base64,', '', $imageData);
    $imageData = base64_decode($imageData);

    // Set the path to save the image (ensure unique name for each image)
    $imagePath = 'uploads/profile_' . uniqid() . '.png';

    // Create the uploads directory if it doesn't exist
    if (!file_exists('uploads')) {
        mkdir('uploads', 0777, true);
    }

    // Save the image to the uploads directory
    if (file_put_contents($imagePath, $imageData)) {
        // Check if there's already an entry for the user's profile image
        $sql = "SELECT id FROM user_profiles LIMIT 1";
        $result = $mysqli->query($sql);

        if ($result->num_rows > 0) {
            // User profile exists, update the existing record
            $profile = $result->fetch_assoc();
            $sql = "UPDATE user_profiles SET image_path = ? WHERE id = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("si", $imagePath, $profile['id']);
        } else {
            // No user profile, insert a new record
            $sql = "INSERT INTO user_profiles (image_path) VALUES (?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("s", $imagePath);
        }

        // Execute the query to insert or update the profile image
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "image_path" => $imagePath]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to save profile photo."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Failed to save image."]);
    }
} else {
    // Handle other cases, such as fetching profile image for display
    $sql = "SELECT image_path FROM user_profiles ORDER BY id DESC LIMIT 1";
    $result = $mysqli->query($sql);

    if ($result->num_rows > 0) {
        // Fetch the image path from the result
        $profile = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'image_path' => $profile['image_path'],
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No profile image found.']);
    }
}

// Close the database connection
$mysqli->close();
?>