<?php
session_start();
include '../config/header.php';

$email = $password = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$email = trim($_POST['email'] ?? '');
		$password = $_POST['password'] ?? '';
		if (!$email || !$password) {
				$error = 'Both fields are required.';
		} else {
				include '../config/db.php';
				$stmt = mysqli_prepare($conn, "SELECT id, name, password FROM users WHERE email = ?");
				mysqli_stmt_bind_param($stmt, 's', $email);
				mysqli_stmt_execute($stmt);
				mysqli_stmt_store_result($stmt);
				if (mysqli_stmt_num_rows($stmt) === 1) {
						mysqli_stmt_bind_result($stmt, $id, $name, $hash);
						mysqli_stmt_fetch($stmt);
						if (password_verify($password, $hash)) {
								$_SESSION['user_id'] = $id;
								$_SESSION['user_name'] = $name;
								header('Location: /research-portal/index.php');
								exit;
						} else {
								$error = 'Incorrect password.';
						}
				} else {
						$error = 'No account found with that email.';
				}
				mysqli_stmt_close($stmt);
				mysqli_close($conn);
		}
}
?>
<div class="flex items-center justify-center min-h-[60vh]">
	<div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-md">
		<h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Sign In</h2>
		<?php if ($error): ?>
			<div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-center"><?php echo htmlspecialchars($error); ?></div>
		<?php endif; ?>
		<form method="post" class="space-y-5">
			<div>
				<label class="block mb-1 font-medium text-gray-700">Email</label>
				<input type="email" name="email" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required value="<?php echo htmlspecialchars($email); ?>">
			</div>
			<div>
				<label class="block mb-1 font-medium text-gray-700">Password</label>
				<input type="password" name="password" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
			</div>
			<div class="text-center">
				<button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold shadow transition">Login</button>
			</div>
		</form>
		<div class="mt-4 text-center text-sm text-gray-600">
			Don't have an account? <a href="register.php" class="text-blue-600 underline">Register</a>
		</div>
	</div>
</div>
<?php include '../config/footer.php'; ?>
