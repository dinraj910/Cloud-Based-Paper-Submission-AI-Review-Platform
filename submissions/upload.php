

<?php
session_start();

// Check authentication before including header
if (empty($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit;
}

include '../config/header.php';
include '../config/aws.php';

$title = $description = $error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if (!$title || !isset($_FILES['paper']) || $_FILES['paper']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Title and file are required.';
    } else {
        $file = $_FILES['paper'];
        $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx', 'txt'];
        if (!in_array($fileType, $allowed)) {
            $error = 'Only PDF, DOC, DOCX, and TXT files are allowed.';
        } else {
            // Generate unique filename
            $fileName = uniqid('paper_', true) . '.' . $fileType;
            $s3Key = 'papers/' . date('Y/m/') . $fileName;
            
            // Check if AWS is configured
            if (isAwsConfigured()) {
                // Upload to S3
                $contentType = getContentType($fileType);
                $fileUrl = uploadToS3($file['tmp_name'], $s3Key, $contentType);
                
                if ($fileUrl) {
                    // Save to database
                    include '../config/db.php';
                    $stmt = mysqli_prepare($conn, "INSERT INTO submissions (user_id, title, description, s3_file_url, file_type, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                    mysqli_stmt_bind_param($stmt, 'issss', $_SESSION['user_id'], $title, $description, $fileUrl, $fileType);
                    if (mysqli_stmt_execute($stmt)) {
                        $success = 'Paper uploaded successfully to S3!';
                        $title = $description = '';
                    } else {
                        $error = 'Database error. Please try again.';
                        // Delete from S3 if database insert failed
                        deleteFromS3($s3Key);
                    }
                    mysqli_stmt_close($stmt);
                    mysqli_close($conn);
                } else {
                    $error = 'Failed to upload file to S3. Please check your AWS configuration.';
                }
            } else {
                // Fallback to local storage if AWS not configured
                $uploadDir = __DIR__ . '/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $filePath = $uploadDir . $fileName;
                
                if (move_uploaded_file($file['tmp_name'], $filePath)) {
                    $fileUrl = '/research-portal/submissions/uploads/' . $fileName;
                    include '../config/db.php';
                    $stmt = mysqli_prepare($conn, "INSERT INTO submissions (user_id, title, description, s3_file_url, file_type, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                    mysqli_stmt_bind_param($stmt, 'issss', $_SESSION['user_id'], $title, $description, $fileUrl, $fileType);
                    if (mysqli_stmt_execute($stmt)) {
                        $success = 'Paper uploaded successfully (stored locally - AWS not configured)!';
                        $title = $description = '';
                    } else {
                        $error = 'Database error. Please try again.';
                        unlink($filePath);
                    }
                    mysqli_stmt_close($stmt);
                    mysqli_close($conn);
                } else {
                    $error = 'File upload failed.';
                }
            }
        }
    }
}
?>


<div class="flex items-center justify-center min-h-[70vh] py-12">
    <div class="glass border border-white/20 rounded-2xl shadow-2xl p-8 w-full max-w-2xl">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg animate-float">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
            </div>
            <h2 class="text-3xl font-bold gradient-text mb-2">Upload Research Paper</h2>
            <p class="text-gray-600">Share your research with the academic community</p>
        </div>
        <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 p-4 rounded-xl mb-6 text-center">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-6 text-center">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data" class="space-y-6">
            <div>
                <label class="block mb-2 font-semibold text-gray-700 text-sm">Paper Title</label>
                <input type="text" name="title" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all bg-white/50" placeholder="Enter your paper title" required value="<?php echo htmlspecialchars($title); ?>">
            </div>
            <div>
                <label class="block mb-2 font-semibold text-gray-700 text-sm">Description</label>
                <textarea name="description" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all bg-white/50" rows="4" placeholder="Describe your research..."><?php echo htmlspecialchars($description); ?></textarea>
            </div>
            <div>
                <label class="block mb-2 font-semibold text-gray-700 text-sm">Upload File</label>
                <div class="relative">
                    <input type="file" name="paper" class="w-full border-2 border-dashed border-gray-300 rounded-xl px-4 py-8 bg-white/50 hover:border-indigo-400 transition-all cursor-pointer file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-gradient-to-r file:from-indigo-600 file:to-purple-600 file:text-white file:font-semibold file:cursor-pointer hover:file:scale-105 file:transition-all" required>
                </div>
                <p class="text-xs text-gray-500 mt-2">Supported formats: PDF, DOC, DOCX, TXT</p>
            </div>
            <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white px-6 py-3.5 rounded-xl font-semibold shadow-lg hover:shadow-xl hover:scale-105 transition-all flex items-center justify-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                <span>Upload Paper</span>
            </button>
        </form>
    </div>
</div>

<?php include '../config/footer.php'; ?>
