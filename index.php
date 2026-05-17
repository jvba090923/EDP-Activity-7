<?php 
session_start();
include_once 'Database.php';
include_once 'layout.php';

$db = Database::getConnection();
$error = "";
$success = "";
$view = "login"; 

// --- 1. LOGIN LOGIC ---
if (isset($_POST['login'])) {
    $stmt = $db->prepare("SELECT * FROM users WHERE Username = ? AND Password = ? AND Status = 'Active'");
    $stmt->execute([$_POST['user'], $_POST['pass']]);
    $user = $stmt->fetch();
    if ($user) {
        $_SESSION['user'] = $user;
        header("Location: dashboard.php");
        exit();
    } else { $error = "Invalid credentials."; }
}

// --- 2. REGISTER LOGIC ---
if (isset($_POST['register'])) {
    $view = "register";
    $stmt = $db->prepare("INSERT INTO users (Username, Password, FullName, Email, SecurityQuestion, SecurityAnswer, Role, Status) VALUES (?, ?, ?, ?, ?, ?, 'Staff', 'Active')");
    if($stmt->execute([$_POST['reg_user'], $_POST['reg_pass'], $_POST['reg_fullname'], $_POST['reg_email'], $_POST['reg_q'], $_POST['reg_a']])) {
        $success = "Registration complete! You can now log in.";
        $view = "login";
    } else { $error = "Registration failed."; }
}

// --- 3. PASSWORD RECOVERY LOGIC (WITH SECURITY QUESTION) ---
if (isset($_POST['recover'])) {
    $view = "recovery";
    // Check if Username, Email, and Security Answer match
    $stmt = $db->prepare("SELECT * FROM users WHERE Username = ? AND Email = ? AND SecurityAnswer = ?");
    $stmt->execute([$_POST['rec_user'], $_POST['rec_email'], $_POST['rec_a']]);
    
    if ($stmt->fetch()) {
        // Correct answer! Update to the NEW password provided by the user
        $update = $db->prepare("UPDATE users SET Password = ? WHERE Username = ?");
        $update->execute([$_POST['rec_new_pass'], $_POST['rec_user']]);
        
        $success = "Password successfully updated! Log in with your new credential.";
        $view = "login";
    } else {
        $error = "Verification failed. Security answer or details are incorrect.";
    }
}

getHeader("Access Portal");
?>

<div class="flex items-center justify-center w-full h-screen">
    <div class="glass p-10 rounded-[32px] w-full max-w-md shadow-2xl">
        
        <?php if($error) echo "<div class='bg-red-500/10 border border-red-500/20 text-red-400 p-3 rounded-xl mb-6 text-xs text-center'>$error</div>"; ?>
        <?php if($success) echo "<div class='bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-3 rounded-xl mb-6 text-xs text-center'>$success</div>"; ?>

        <!-- LOGIN FORM -->
        <div id="login-box" class="<?php echo ($view == 'login') ? '' : 'hidden'; ?>">
            <h2 class="text-3xl font-extrabold text-center mb-8">A-Retail <span class="text-cyan-400">Portal</span></h2>
            <form method="POST" class="space-y-4">
                <input type="text" name="user" class="w-full bg-slate-900 border border-slate-800 rounded-2xl p-4 outline-none focus:ring-2 focus:ring-cyan-500" placeholder="Username" required>
                <input type="password" name="pass" class="w-full bg-slate-900 border border-slate-800 rounded-2xl p-4 outline-none focus:ring-2 focus:ring-cyan-500" placeholder="Password" required>
                <button type="submit" name="login" class="w-full bg-gradient-to-r from-cyan-500 to-violet-500 py-4 rounded-2xl font-bold">Launch System</button>
            </form>
            <div class="mt-8 flex flex-col gap-3 text-center">
                <button onclick="toggle('register-box')" class="text-cyan-400 text-sm font-semibold">Register New Account</button>
                <button onclick="toggle('recovery-box')" class="text-slate-500 text-xs">Forgot Password?</button>
            </div>
        </div>

        <!-- REGISTER FORM -->
        <div id="register-box" class="<?php echo ($view == 'register') ? '' : 'hidden'; ?>">
            <button onclick="toggle('login-box')" class="text-slate-500 mb-4 text-sm"><i class="fas fa-arrow-left"></i> Back</button>
            <h2 class="text-2xl font-bold mb-6 text-cyan-400">Register Staff</h2>
            <form method="POST" class="space-y-3">
                <input type="text" name="reg_fullname" placeholder="Full Name" class="w-full bg-slate-900 border border-slate-800 rounded-xl p-3 outline-none" required>
                <input type="email" name="reg_email" placeholder="Email" class="w-full bg-slate-900 border border-slate-800 rounded-xl p-3 outline-none" required>
                <input type="text" name="reg_user" placeholder="Username" class="w-full bg-slate-900 border border-slate-800 rounded-xl p-3 outline-none" required>
                <input type="password" name="reg_pass" placeholder="Password" class="w-full bg-slate-900 border border-slate-800 rounded-xl p-3 outline-none" required>
                
                <div class="pt-2">
                    <label class="text-[10px] text-slate-500 uppercase font-bold">Security Question</label>
                    <select name="reg_q" class="w-full bg-slate-900 border border-slate-800 rounded-xl p-3 mt-1 outline-none text-sm">
                        <option>What was your first pet's name?</option>
                        <option>What is your mother's maiden name?</option>
                        <option>What city were you born in?</option>
                    </select>
                </div>
                <input type="text" name="reg_a" placeholder="Your Answer" class="w-full bg-slate-900 border border-slate-800 rounded-xl p-3 outline-none" required>
                
                <button type="submit" name="register" class="w-full bg-cyan-600 py-3 rounded-xl font-bold mt-2">Create Identity</button>
            </form>
        </div>

        <!-- RECOVERY FORM -->
        <div id="recovery-box" class="<?php echo ($view == 'recovery') ? '' : 'hidden'; ?>">
            <button onclick="toggle('login-box')" class="text-slate-500 mb-4 text-sm"><i class="fas fa-arrow-left"></i> Back</button>
            <h2 class="text-2xl font-bold mb-2 text-violet-400">Recovery</h2>
            <p class="text-slate-400 text-[10px] uppercase mb-6 tracking-widest">Verify identity to change password</p>
            <form method="POST" class="space-y-3">
                <input type="text" name="rec_user" placeholder="Confirm Username" class="w-full bg-slate-900 border border-slate-800 rounded-xl p-3" required>
                <input type="email" name="rec_email" placeholder="Registered Email" class="w-full bg-slate-900 border border-slate-800 rounded-xl p-3" required>
                <input type="text" name="rec_a" placeholder="Answer to Security Question" class="w-full bg-slate-900 border border-slate-800 rounded-xl p-3 border-violet-500/30" required>
                
                <div class="pt-2 border-t border-white/5 mt-2">
                    <label class="text-[10px] text-violet-300 uppercase font-bold">New Password</label>
                    <input type="password" name="rec_new_pass" placeholder="Enter New Password" class="w-full bg-slate-900 border border-slate-800 rounded-xl p-3 mt-1" required>
                </div>
                <button type="submit" name="recover" class="w-full bg-violet-600 py-3 rounded-xl font-bold shadow-lg shadow-violet-500/20">Update Password</button>
            </form>
        </div>

    </div>
</div>

<script>
function toggle(id) {
    document.getElementById('login-box').classList.add('hidden');
    document.getElementById('register-box').classList.add('hidden');
    document.getElementById('recovery-box').classList.add('hidden');
    document.getElementById(id).classList.remove('hidden');
}
</script>
</body></html>