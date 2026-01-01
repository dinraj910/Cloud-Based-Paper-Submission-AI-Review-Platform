<?php
include '../config/header.php';

$name = $email = $password = $error = '';
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$name = trim($_POST['name'] ?? '');
		$email = trim($_POST['email'] ?? '');
		$password = $_POST['password'] ?? '';
		if (!$name || !$email || !$password) {
				$error = 'All fields are required.';
		} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$error = 'Invalid email address.';
		} else {
				include '../config/db.php';
				$stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
				mysqli_stmt_bind_param($stmt, 's', $email);
				mysqli_stmt_execute($stmt);
				mysqli_stmt_store_result($stmt);
				if (mysqli_stmt_num_rows($stmt) > 0) {
						$error = 'Email already registered.';
				} else {
						$hash = password_hash($password, PASSWORD_DEFAULT);
						$stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())");
						mysqli_stmt_bind_param($stmt, 'sss', $name, $email, $hash);
						if (mysqli_stmt_execute($stmt)) {
								$success = true;
						} else {
								$error = 'Registration failed. Please try again.';
						}
				}
				mysqli_stmt_close($stmt);
				mysqli_close($conn);
		}
}
?>
<div class="flex items-center justify-center min-h-[70vh] py-12">
	<div class="glass border border-white/20 rounded-2xl shadow-2xl p-8 w-full max-w-md">
		<div class="text-center mb-8">
			<div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
				<svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
				</svg>
			</div>
			<h2 class="text-3xl font-bold gradient-text mb-2">Join Us</h2>
			<p class="text-gray-600">Create your account to get started</p>
		</div>
		<?php if ($success): ?>
			<div class="bg-green-50 border border-green-200 text-green-700 p-4 rounded-xl mb-6 text-center text-sm">
				<svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
				</svg>
				Registration successful! <a href="login.php" class="text-indigo-600 font-semibold hover:text-indigo-700">Login here</a>.
			</div>
		<?php endif; ?>
		<?php if ($error): ?>
			<div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-6 text-center text-sm">
				<svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
				</svg>
				<?php echo htmlspecialchars($error); ?>
			</div>
		<?php endif; ?>
		<form method="post" class="space-y-5">
			<div>
				<label class="block mb-2 font-semibold text-gray-700 text-sm">Full Name</label>
				<input type="text" name="name" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all bg-white/50" placeholder="John Doe" required value="<?php echo htmlspecialchars($name); ?>">
			</div>
			<div>
				<label class="block mb-2 font-semibold text-gray-700 text-sm">Email Address</label>
				<input type="email" name="email" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all bg-white/50" placeholder="you@example.com" required value="<?php echo htmlspecialchars($email); ?>">
			</div>
			<div>
				<label class="block mb-2 font-semibold text-gray-700 text-sm">Password</label>
				<input type="password" name="password" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all bg-white/50" placeholder="••••••••" required>
			</div>
			<button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white px-6 py-3.5 rounded-xl font-semibold shadow-lg hover:shadow-xl hover:scale-105 transition-all mt-6">
				Create Account
			</button>
		</form>
		<div class="mt-6 text-center text-sm text-gray-600">
			Already have an account? <a href="login.php" class="text-indigo-600 hover:text-indigo-700 font-semibold">Sign in</a>
		</div>
	</div>
</div>
<?php include '../config/footer.php'; ?>
