
<?php
include 'config/db.php';
include 'config/header.php';

// Fetch submissions with user information and comment count using JOIN
$result = mysqli_query($conn, 
    "SELECT s.*, u.name as user_name, 
     (SELECT COUNT(*) FROM comments c WHERE c.submission_id = s.id) as comment_count
     FROM submissions s 
     LEFT JOIN users u ON s.user_id = u.id 
     ORDER BY s.created_at DESC"
);

// Get statistics
$statsQuery = mysqli_query($conn, 
    "SELECT 
        (SELECT COUNT(*) FROM submissions) as total_papers,
        (SELECT COUNT(*) FROM users) as total_users,
        (SELECT COUNT(*) FROM comments) as total_comments,
        (SELECT COUNT(*) FROM submissions WHERE DATE(created_at) = CURDATE()) as today_papers
    "
);
$stats = mysqli_fetch_assoc($statsQuery);
?>


<!-- Hero Section -->
<section class="mb-16 text-center animate-fade-in">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold mb-6">
            <span class="gradient-text">Research Paper</span><br>
            <span class="text-gray-800">Social Feed</span>
        </h1>
        <p class="text-lg md:text-xl text-gray-600 mb-8 max-w-2xl mx-auto leading-relaxed">
            Share, discover, and discuss the latest research.<br class="hidden md:block">
            Upload your paper or browse the feed below.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
            <a href="submissions/upload.php" class="group px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl hover:scale-105 transition-all flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                <span>Upload New Paper</span>
            </a>
            <a href="#submissions" class="px-8 py-3.5 bg-white/80 backdrop-blur text-gray-700 rounded-xl font-semibold shadow-md hover:shadow-lg hover:scale-105 transition-all">
                Browse Research
            </a>
        </div>
    </div>
</section>

<!-- Feed Section -->
<section id="submissions" class="animate-slide-up">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Feed -->
        <div class="flex-1">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-3xl font-bold text-gray-800">Latest Research</h2>
                <div class="text-sm text-gray-500">Showing recent submissions</div>
            </div>
            <div class="flex flex-col gap-6">
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $userName = !empty($row['user_name']) ? $row['user_name'] : 'Anonymous';
                        $avatarLetter = strtoupper(substr($userName, 0, 1));
                ?>
                <article class="glass border border-white/20 rounded-2xl p-6 card-hover">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center shadow-md">
                            <span class="text-white font-bold text-lg"><?php echo $avatarLetter; ?></span>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-gray-800"><?php echo htmlspecialchars($userName); ?></div>
                            <div class="text-xs text-gray-500"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></div>
                        </div>
                    </div>
                    <h3 class="font-bold text-xl text-gray-900 mb-3 hover:text-indigo-600 transition-colors cursor-pointer"><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p class="text-gray-600 mb-4 leading-relaxed"><?php echo htmlspecialchars($row['description']); ?></p>
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-indigo-50 to-purple-50 text-indigo-700 rounded-lg text-sm font-medium">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            <?php echo strtoupper(htmlspecialchars($row['file_type'])); ?>
                        </span>
                        <a href="<?php echo htmlspecialchars($row['s3_file_url']); ?>" target="_blank" class="inline-flex items-center px-4 py-1.5 bg-white/80 text-indigo-600 rounded-lg text-sm font-semibold hover:bg-indigo-600 hover:text-white transition-all shadow-sm hover:shadow-md">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Download
                        </a>
                    </div>
                    <!-- Comments Section -->
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <button onclick="toggleComments(<?php echo $row['id']; ?>)" class="flex items-center text-sm text-gray-600 hover:text-indigo-600 transition-colors font-medium mb-3">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <span id="comment-count-<?php echo $row['id']; ?>"><?php echo $row['comment_count']; ?> <?php echo $row['comment_count'] == 1 ? 'comment' : 'comments'; ?></span>
                        </button>
                        
                        <div id="comments-section-<?php echo $row['id']; ?>" class="hidden">
                            <!-- Comments List -->
                            <div id="comments-list-<?php echo $row['id']; ?>" class="space-y-3 mb-4">
                                <?php
                                // Fetch comments for this submission
                                $commentQuery = mysqli_query($conn, 
                                    "SELECT c.*, u.name as commenter_name 
                                     FROM comments c 
                                     LEFT JOIN users u ON c.user_id = u.id 
                                     WHERE c.submission_id = {$row['id']} 
                                     ORDER BY c.created_at ASC"
                                );
                                
                                if (mysqli_num_rows($commentQuery) > 0) {
                                    while ($comment = mysqli_fetch_assoc($commentQuery)) {
                                        $commenterName = !empty($comment['commenter_name']) ? $comment['commenter_name'] : 'Anonymous';
                                        $commenterLetter = strtoupper(substr($commenterName, 0, 1));
                                ?>
                                <div class="flex gap-3 p-3 bg-gray-50/50 rounded-lg">
                                    <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-cyan-500 rounded-full flex items-center justify-center flex-shrink-0">
                                        <span class="text-white font-semibold text-xs"><?php echo $commenterLetter; ?></span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-semibold text-sm text-gray-800"><?php echo htmlspecialchars($commenterName); ?></span>
                                            <span class="text-xs text-gray-400"><?php echo date('M d, Y g:i A', strtotime($comment['created_at'])); ?></span>
                                        </div>
                                        <p class="text-sm text-gray-700 break-words"><?php echo htmlspecialchars($comment['comment']); ?></p>
                                    </div>
                                </div>
                                <?php 
                                    }
                                } else {
                                    echo '<p class="text-sm text-gray-400 italic">No comments yet. Be the first to comment!</p>';
                                }
                                ?>
                            </div>
                            
                            <!-- Add Comment Form -->
                            <?php if (!empty($_SESSION['user_id'])): ?>
                            <form onsubmit="addComment(event, <?php echo $row['id']; ?>)" class="flex gap-2">
                                <input type="text" 
                                       id="comment-input-<?php echo $row['id']; ?>" 
                                       placeholder="Write a comment..." 
                                       class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white/50"
                                       required>
                                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg text-sm font-semibold hover:shadow-md transition-all">
                                    Post
                                </button>
                            </form>
                            <?php else: ?>
                            <p class="text-sm text-gray-500 italic">
                                <a href="/research-portal/auth/login.php" class="text-indigo-600 hover:text-indigo-700 font-semibold">Login</a> to comment
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
                <?php 
                    }
                } else {
                ?>
                <div class="glass border border-white/20 rounded-2xl p-12 text-center">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No papers yet</h3>
                    <p class="text-gray-500 mb-6">Be the first to share your research!</p>
                    <a href="submissions/upload.php" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl hover:scale-105 transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Upload First Paper
                    </a>
                </div>
                <?php } ?>
            </div>
        </div>
        <!-- Sidebar -->
        <aside class="w-full lg:w-80 flex-shrink-0 space-y-6">
            <div class="glass border border-white/20 rounded-2xl p-6 card-hover">
                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center mb-4 shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                </div>
                <h4 class="font-bold text-lg mb-2 text-gray-800">Upload a Paper</h4>
                <p class="text-gray-600 mb-4 text-sm leading-relaxed">Share your research with the community. Get feedback and reviews from peers.</p>
                <a href="submissions/upload.php" class="block w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-center py-2.5 rounded-lg font-semibold shadow-md hover:shadow-lg hover:scale-105 transition-all">Upload Now</a>
            </div>
            <div class="glass border border-white/20 rounded-2xl p-6 card-hover">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center mb-4 shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h4 class="font-bold text-lg mb-2 text-gray-800">About</h4>
                <p class="text-gray-600 text-sm leading-relaxed">This portal is a cloud-based platform for academic paper sharing, review, and AI-powered analysis.</p>
            </div>
            <div class="glass border border-white/20 rounded-2xl p-6 card-hover">
                <h4 class="font-bold text-lg mb-3 text-gray-800">Community Stats</h4>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 text-sm">Total Papers</span>
                        <span class="font-bold text-indigo-600"><?php echo $stats['total_papers']; ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 text-sm">Researchers</span>
                        <span class="font-bold text-purple-600"><?php echo $stats['total_users']; ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 text-sm">Comments</span>
                        <span class="font-bold text-blue-600"><?php echo $stats['total_comments']; ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 text-sm">Today</span>
                        <span class="inline-flex items-center">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                            <span class="font-bold text-gray-800"><?php echo $stats['today_papers']; ?> new</span>
                        </span>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</section>

<script>
function toggleComments(submissionId) {
    const section = document.getElementById('comments-section-' + submissionId);
    section.classList.toggle('hidden');
}

function addComment(event, submissionId) {
    event.preventDefault();
    
    const input = document.getElementById('comment-input-' + submissionId);
    const comment = input.value.trim();
    
    if (!comment) return;
    
    // Show loading state
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Posting...';
    submitBtn.disabled = true;
    
    fetch('/research-portal/api/add_comment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'submission_id=' + submissionId + '&comment=' + encodeURIComponent(comment)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Add new comment to the list
            const commentsList = document.getElementById('comments-list-' + submissionId);
            const newComment = document.createElement('div');
            newComment.className = 'flex gap-3 p-3 bg-gray-50/50 rounded-lg animate-fade-in';
            newComment.innerHTML = `
                <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-cyan-500 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-white font-semibold text-xs">${data.commenter_letter}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-semibold text-sm text-gray-800">${data.commenter_name}</span>
                        <span class="text-xs text-gray-400">Just now</span>
                    </div>
                    <p class="text-sm text-gray-700 break-words">${escapeHtml(comment)}</p>
                </div>
            `;
            
            // Remove "no comments" message if exists
            const noComments = commentsList.querySelector('.italic');
            if (noComments) {
                noComments.remove();
            }
            
            commentsList.appendChild(newComment);
            
            // Update comment count
            const countElement = document.getElementById('comment-count-' + submissionId);
            const currentCount = parseInt(countElement.textContent);
            const newCount = currentCount + 1;
            countElement.textContent = newCount + ' ' + (newCount === 1 ? 'comment' : 'comments');
            
            // Clear input
            input.value = '';
        } else {
            alert(data.message || 'Failed to post comment');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    })
    .finally(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php include 'config/footer.php'; ?>
