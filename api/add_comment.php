<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to comment']);
    exit;
}

// Validate input
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$submission_id = isset($_POST['submission_id']) ? (int)$_POST['submission_id'] : 0;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

if ($submission_id <= 0 || empty($comment)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Database connection
include '../config/db.php';

// Verify submission exists
$checkSubmission = mysqli_query($conn, "SELECT id FROM submissions WHERE id = $submission_id");
if (mysqli_num_rows($checkSubmission) === 0) {
    echo json_encode(['success' => false, 'message' => 'Submission not found']);
    exit;
}

// Insert comment
$stmt = mysqli_prepare($conn, "INSERT INTO comments (submission_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())");
mysqli_stmt_bind_param($stmt, 'iis', $submission_id, $_SESSION['user_id'], $comment);

if (mysqli_stmt_execute($stmt)) {
    // Get commenter information
    $userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Anonymous';
    $avatarLetter = strtoupper(substr($userName, 0, 1));
    
    echo json_encode([
        'success' => true,
        'message' => 'Comment posted successfully',
        'commenter_name' => htmlspecialchars($userName),
        'commenter_letter' => $avatarLetter
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to post comment']);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
