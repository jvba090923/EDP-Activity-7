<?php 
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

include_once 'Database.php'; 
include_once 'layout.php';
$db = Database::getConnection();

// --- 1. SAVE / UPDATE LOGIC ---
if(isset($_POST['save_user'])){
    try {
        // REPLACE INTO handles both New (empty ID) and Existing (ID present)
        $stmt = $db->prepare("REPLACE INTO users (UserID, Username, Password, FullName, Email, SecurityQuestion, SecurityAnswer, Status, Role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Staff')");
        $stmt->execute([
            $_POST['uid'], 
            $_POST['uname'], 
            $_POST['pass'], 
            $_POST['fname'], 
            $_POST['email'],
            $_POST['s_question'],
            $_POST['s_answer'],
            $_POST['status']
        ]);
        $msg = "Success: Identity updated in the retaildb.";
    } catch (Exception $e) {
        $msg = "Error: " . $e->getMessage();
    }
}

// --- 2. FETCH LIST ---
$search = $_GET['search'] ?? '';
$stmt = $db->prepare("SELECT * FROM users WHERE Username LIKE ? OR FullName LIKE ?");
$stmt->execute(["%$search%", "%$search%"]);
$list = $stmt->fetchAll(PDO::FETCH_ASSOC);

getHeader("User Manager"); 
renderSidebar('users');
?>

<main class="flex-grow p-10 overflow-y-auto content-animate">
    <h2 class="text-4xl font-extrabold mb-8 tracking-tight">Identity Access Management</h2>
    
    <?php if(isset($msg)) echo "<div class='bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 p-4 rounded-2xl mb-8'>$msg</div>"; ?>

    <!-- EDIT / ADD FORM -->
    <div class="glass p-10 rounded-[32px] mb-10 border border-white/5">
        <div class="flex justify-between items-center mb-6">
            <h4 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Credential Control Panel</h4>
            <button onclick="window.location.href='users.php'" class="text-[10px] text-cyan-400 font-bold uppercase hover:underline">Clear / New User</button>
        </div>
        
        <form method="POST" class="space-y-6">
            <input type="hidden" name="uid" id="uid"> <!-- FORM ID: uid -->
            
            <div class="grid grid-cols-4 gap-6">
                <div>
                    <label class="text-[10px] text-slate-500 uppercase font-bold">Username</label>
                    <input type="text" name="uname" id="uname" class="w-full bg-slate-900/50 border border-slate-800 p-4 rounded-2xl mt-1 outline-none focus:ring-1 focus:ring-cyan-500" required>
                </div>
                <div>
                    <label class="text-[10px] text-slate-500 uppercase font-bold">Password</label>
                    <input type="password" name="pass" id="pass" class="w-full bg-slate-900/50 border border-slate-800 p-4 rounded-2xl mt-1 outline-none focus:ring-1 focus:ring-cyan-500" required>
                </div>
                <div>
                    <label class="text-[10px] text-slate-500 uppercase font-bold">Full Name</label>
                    <input type="text" name="fname" id="fname" class="w-full bg-slate-900/50 border border-slate-800 p-4 rounded-2xl mt-1 outline-none focus:ring-1 focus:ring-cyan-500" required>
                </div>
                <div>
                    <label class="text-[10px] text-slate-500 uppercase font-bold">Email Address</label>
                    <input type="email" name="email" id="email" class="w-full bg-slate-900/50 border border-slate-800 p-4 rounded-2xl mt-1 outline-none focus:ring-1 focus:ring-cyan-500" required>
                </div>
            </div>

            <div class="grid grid-cols-4 gap-6 items-end">
                <div class="col-span-2">
                    <label class="text-[10px] text-slate-500 uppercase font-bold">Security Question</label>
                    <select name="s_question" id="s_question" class="w-full bg-slate-900/50 border border-slate-800 p-4 rounded-2xl mt-1 outline-none focus:ring-1 focus:ring-violet-500">
                        <option value="What was your first pet's name?">What was your first pet's name?</option>
                        <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                        <option value="What city were you born in?">What city were you born in?</option>
                    </select>
                </div>
                <div>
                    <label class="text-[10px] text-slate-500 uppercase font-bold">Security Answer</label>
                    <input type="text" name="s_answer" id="s_answer" class="w-full bg-slate-900/50 border border-slate-800 p-4 rounded-2xl mt-1 outline-none focus:ring-1 focus:ring-violet-500" required>
                </div>
                <div>
                    <label class="text-[10px] text-slate-500 uppercase font-bold">Account Status</label>
                    <select name="status" id="status" class="w-full bg-slate-900/50 border border-slate-800 p-4 rounded-2xl mt-1 outline-none">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" name="save_user" class="bg-gradient-to-r from-cyan-500 to-violet-500 px-10 py-4 rounded-2xl font-bold shadow-lg shadow-cyan-500/20 transition">Commit Changes</button>
            </div>
        </form>
    </div>

    <!-- TABLE -->
    <div class="glass p-10 rounded-[32px]">
        <table class="w-full text-left">
            <thead class="text-[10px] text-slate-500 uppercase border-b border-white/5">
                <tr><th class="pb-4">Full Name</th><th class="pb-4">Username</th><th class="pb-4">Status</th><th class="pb-4 text-right">Action</th></tr>
            </thead>
            <tbody class="text-sm">
                <?php foreach($list as $u): 
                    // CRUCIAL: Prepare the JSON string to safely handle quotes
                    $userData = htmlspecialchars(json_encode($u), ENT_QUOTES, 'UTF-8');
                ?>
                <tr class="border-b border-white/5 hover:bg-white/5 transition">
                    <td class="py-5 font-medium"><?php echo $u['FullName']; ?></td>
                    <td class="py-5 text-slate-400"><?php echo $u['Username']; ?></td>
                    <td class="py-5">
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest <?php echo $u['Status']=='Active'?'bg-emerald-500/10 text-emerald-400':'bg-red-500/10 text-red-400'; ?>">
                            <?php echo $u['Status']; ?>
                        </span>
                    </td>
                    <td class="py-5 text-right">
                        <!-- BUTTON CALLS THE SCRIPT BELOW -->
                        <button onclick='editUser(<?php echo $userData; ?>)' class="text-cyan-400 hover:text-cyan-300 font-bold">Update Profile</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
/**
 * Maps the JSON object keys to the HTML input IDs
 */
function editUser(u){
    // Basic Fields
    document.getElementById('uid').value = u.UserID;
    document.getElementById('uname').value = u.Username;
    document.getElementById('pass').value = u.Password;
    document.getElementById('fname').value = u.FullName;
    document.getElementById('email').value = u.Email;
    
    // Dropdowns (Must match value string exactly)
    document.getElementById('s_question').value = u.SecurityQuestion;
    document.getElementById('status').value = u.Status;
    
    // Answer
    document.getElementById('s_answer').value = u.SecurityAnswer;

    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>
</body></html>