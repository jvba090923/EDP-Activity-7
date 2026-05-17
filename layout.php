<?php
function getHeader($title) {
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>A-Retail | '.$title.'</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            :root { --bg: #07111f; --panel: rgba(13, 23, 41, 0.76); --line: rgba(148, 163, 184, 0.14); --text: #e5eefb; }
            body { font-family: "Plus Jakarta Sans", sans-serif; color: var(--text); background: radial-gradient(circle at top left, rgba(56, 189, 248, 0.16), transparent 24%), #040b16; min-height: 100vh; overflow: hidden; }
            .glass { background: var(--panel); backdrop-filter: blur(18px); border: 1px solid var(--line); }
            .active-link { background: linear-gradient(90deg, rgba(56, 189, 248, 0.18), rgba(139, 92, 246, 0.12)); border: 1px solid rgba(56, 189, 248, 0.28); color: white; }
            
            /* ANIMATION */
            .content-animate { animation: fadeUp 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) both; }
            @keyframes fadeUp { 
                from { opacity: 0; transform: translateY(20px); } 
                to { opacity: 1; transform: translateY(0); } 
            }
            
            .stat-card::after { content: ""; position: absolute; inset: auto -20% -55% auto; width: 160px; height: 160px; border-radius: 999px; background: radial-gradient(circle, rgba(56, 189, 248, 0.18), transparent 70%); }
        </style>
    </head>
    <body class="flex h-screen">';
}

function renderSidebar($activePage) {
    $menu = [
        'dashboard' => ['Statistics', 'fa-chart-pie'],
        'reports' => ['Report Engine', 'fa-file-invoice'],
        'users' => ['User Manager', 'fa-users'],
        'transactions' => ['Transactions', 'fa-exchange-alt'],
        'about' => ['About System', 'fa-circle-info']
    ];
    ?>
    <aside class="w-72 min-w-[18rem] flex-shrink-0 bg-slate-950/45 border-r border-white/5 flex flex-col backdrop-blur-xl">
        <div class="p-8">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 bg-gradient-to-br from-cyan-500 to-violet-500 rounded-2xl flex items-center justify-center shadow-lg shadow-cyan-500/20"><i class="fas fa-layer-group text-white"></i></div>
                <div><p class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.24em] whitespace-nowrap">Store System</p><h1 class="font-bold text-xl tracking-tight text-white whitespace-nowrap">A-Retail Solutions</h1></div>
            </div>
        </div>
        <nav class="flex-grow px-4 space-y-2">
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.24em] px-4 mb-2">Main Menu</p>
            <?php foreach($menu as $page => $info): if($page == 'about') echo '<p class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.24em] px-4 mt-8 mb-2">System</p>'; ?>
                <a href="<?php echo $page; ?>.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition whitespace-nowrap <?php echo ($activePage == $page) ? 'active-link' : 'text-slate-300 hover:bg-slate-800/50'; ?>">
                    <i class="fas <?php echo $info[1]; ?> w-5"></i> <?php echo $info[0]; ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="p-4 border-t border-white/5">
            <div class="glass p-4 rounded-2xl mb-4 text-xs text-slate-400">
                <p class="whitespace-nowrap"><i class="fas fa-database text-cyan-400 mr-2"></i> Database: <span class="text-white font-bold">retaildb</span></p>
                <p class="mt-2 whitespace-nowrap"><i class="fas fa-signal text-emerald-400 mr-2"></i> SQL Sync: <span class="text-emerald-400 font-bold">Online</span></p>
            </div>
            <a href="logout.php" class="w-full flex items-center justify-center gap-2 py-3 bg-red-500/10 text-red-400 rounded-2xl hover:bg-red-500 hover:text-white transition whitespace-nowrap">
                <i class="fas fa-power-off"></i> Terminate Session
            </a>
        </div>
    </aside>
    <?php
}
?>