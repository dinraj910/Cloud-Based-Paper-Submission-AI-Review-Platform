
<?php
include 'config/db.php';
include 'config/header.php';

$result = mysqli_query($conn, "SELECT * FROM submissions ORDER BY created_at DESC");
?>


<!-- Hero Section -->
<section class="mb-10 text-center">
    <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4">Research Paper Social Feed</h1>
    <p class="text-lg text-gray-600 mb-6 max-w-2xl mx-auto">Share, discover, and discuss the latest research. Upload your paper or browse the feed below.</p>
    <a href="submissions/upload.php" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold shadow hover:bg-blue-700 transition">Upload New Paper</a>
</section>

<!-- Feed Section -->
<section id="submissions">
    <div class="flex flex-col md:flex-row gap-8">
        <!-- Feed -->
        <div class="flex-1">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Feed</h2>
            <div class="flex flex-col gap-6">
                <?php
                // Get user info for each post (simulate avatar and name)
                $userResult = mysqli_query($conn, "SELECT id, name FROM users");
                $userMap = [];
                while ($u = mysqli_fetch_assoc($userResult)) {
                    $userMap[$u['id']] = $u['name'];
                }
                mysqli_data_seek($result, 0);
                while ($row = mysqli_fetch_assoc($result)) {
                    $userName = isset($userMap[$row['user_id']]) ? $userMap[$row['user_id']] : 'User';
                    $avatarLetter = strtoupper(substr($userName, 0, 1));
                ?>
                <div class="bg-white rounded-xl shadow-md p-6 flex flex-col gap-3 hover:shadow-lg transition">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="inline-flex items-center justify-center w-10 h-10 bg-blue-500 text-white rounded-full font-bold text-lg"><?php echo $avatarLetter; ?></span>
                        <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($userName); ?></span>
                        <span class="ml-auto text-xs text-gray-400"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></span>
                    </div>
                    <h3 class="font-bold text-xl text-blue-700 mb-1"><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p class="text-gray-700 mb-2"><?php echo htmlspecialchars($row['description']); ?></p>
                    <div class="flex items-center gap-2 text-sm text-gray-500">
                        <span class="bg-gray-100 px-2 py-1 rounded">Type: <?php echo htmlspecialchars($row['file_type']); ?></span>
                        <a href="<?php echo htmlspecialchars($row['s3_file_url']); ?>" target="_blank" class="ml-2 text-blue-600 hover:underline">Download</a>
                    </div>
                    <div class="mt-3 border-t pt-3">
                        <span class="text-xs text-gray-400">No comments yet</span>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
        <!-- Sidebar (optional) -->
        <aside class="w-full md:w-72 flex-shrink-0">
            <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <h4 class="font-bold text-lg mb-2">Upload a Paper</h4>
                <p class="text-gray-600 mb-4 text-sm">Share your research with the community. Get feedback and reviews from peers.</p>
                <a href="submissions/upload.php" class="block w-full bg-blue-600 text-white text-center py-2 rounded-lg font-semibold shadow hover:bg-blue-700 transition">Upload Now</a>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6">
                <h4 class="font-bold text-lg mb-2">About</h4>
                <p class="text-gray-600 text-sm">This portal is a cloud-based platform for academic paper sharing, review, and AI-powered analysis.</p>
            </div>
        </aside>
    </div>
</section>

<?php include 'config/footer.php'; ?>
