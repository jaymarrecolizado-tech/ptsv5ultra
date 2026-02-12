<?php
/**
 * User Profile Page
 */

$pageTitle = 'My Profile';
$activeTab = '';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';

$user = getCurrentUser();
$message = '';
$error = '';

try {
    $db = getDB();
    
    // Get user profile
    $stmt = $db->prepare("SELECT p.*, u.username, u.email, u.role FROM user_profiles p JOIN users u ON p.user_id = u.id WHERE u.id = ?");
    $stmt->execute([$user['id']]);
    $profile = $stmt->fetch();
    
    if (!$profile) {
        // Create profile if doesn't exist
        $stmt = $db->prepare("INSERT INTO user_profiles (user_id) VALUES (?)");
        $stmt->execute([$user['id']]);
        $profile = ['user_id' => $user['id'], 'first_name' => '', 'last_name' => '', 'phone' => '', 'department' => '', 'username' => $user['username'], 'email' => $user['email'], 'role' => $user['role']];
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $firstName = sanitize($_POST['first_name'] ?? '');
        $lastName = sanitize($_POST['last_name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $department = sanitize($_POST['department'] ?? '');
        $timezone = $_POST['timezone'] ?? 'Asia/Manila';
        
        // Update profile
        $stmt = $db->prepare("UPDATE user_profiles SET first_name = ?, last_name = ?, phone = ?, department = ?, timezone = ? WHERE user_id = ?");
        $stmt->execute([$firstName, $lastName, $phone, $department, $timezone, $user['id']]);
        
        // Update password if provided
        if (!empty($_POST['current_password']) && !empty($_POST['new_password'])) {
            $currentPassword = $_POST['current_password'];
            $newPassword = $_POST['new_password'];
            
            // Verify current password
            $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $userData = $stmt->fetch();
            
            if (password_verify($currentPassword, $userData['password_hash'])) {
                if (strlen($newPassword) >= 6) {
                    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                    $stmt->execute([$newHash, $user['id']]);
                    $message = 'Profile and password updated successfully!';
                } else {
                    $error = 'New password must be at least 6 characters';
                }
            } else {
                $error = 'Current password is incorrect';
            }
        } else {
            $message = 'Profile updated successfully!';
        }
        
        // Refresh profile data
        $stmt = $db->prepare("SELECT p.*, u.username, u.email, u.role FROM user_profiles p JOIN users u ON p.user_id = u.id WHERE u.id = ?");
        $stmt->execute([$user['id']]);
        $profile = $stmt->fetch();
    }
} catch (Exception $e) {
    $error = 'An error occurred. Please try again.';
}
?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">My Profile</h2>
        </div>
        
        <?php if ($message): ?>
        <div class="mx-6 mt-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="mx-6 mt-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="" class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" value="<?php echo htmlspecialchars($profile['username']); ?>" disabled
                           class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-500">
                    <p class="text-xs text-gray-500 mt-1">Username cannot be changed</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" value="<?php echo htmlspecialchars($profile['email']); ?>" disabled
                           class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-500">
                    <p class="text-xs text-gray-500 mt-1">Contact admin to change email</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                    <input type="text" name="first_name" value="<?php echo htmlspecialchars($profile['first_name'] ?? ''); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                    <input type="text" name="last_name" value="<?php echo htmlspecialchars($profile['last_name'] ?? ''); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                    <input type="text" name="department" value="<?php echo htmlspecialchars($profile['department'] ?? ''); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                    <select name="timezone" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="Asia/Manila" <?php echo ($profile['timezone'] ?? 'Asia/Manila') === 'Asia/Manila' ? 'selected' : ''; ?>>Asia/Manila (Philippines)</option>
                        <option value="UTC" <?php echo ($profile['timezone'] ?? '') === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                        <option value="Asia/Singapore" <?php echo ($profile['timezone'] ?? '') === 'Asia/Singapore' ? 'selected' : ''; ?>>Asia/Singapore</option>
                        <option value="Asia/Tokyo" <?php echo ($profile['timezone'] ?? '') === 'Asia/Tokyo' ? 'selected' : ''; ?>>Asia/Tokyo</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <input type="text" value="<?php echo ucfirst($profile['role']); ?>" disabled
                           class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-500">
                </div>
            </div>
            
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-medium text-gray-800 mb-4">Change Password</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                        <input type="password" name="current_password"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                               placeholder="Leave blank to keep current">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                        <input type="password" name="new_password"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                               placeholder="Min 6 characters">
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end gap-3 pt-4">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
