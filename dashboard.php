<?php
session_start();
if(!isset($_SESSION['user'])) header("Location: index.php");
include_once 'Database.php'; include_once 'layout.php';
$db = Database::getConnection();

$pCount = $db->query("SELECT SUM(StockQuantity) FROM products")->fetchColumn();
$revenue = $db->query("SELECT SUM(TotalRevenue) FROM view_salesrevenue")->fetchColumn();
$lowCount = $db->query("SELECT COUNT(*) FROM view_lowstock")->fetchColumn();
$products = $db->query("SELECT p.*, c.CategoryName FROM products p JOIN categories c ON p.CategoryID = c.CategoryID LIMIT 4")->fetchAll();

getHeader("Statistics"); renderSidebar("dashboard");
?>
<main class="flex-grow p-10 overflow-y-auto content-animate">
    <header class="mb-10 flex justify-between items-end">
        <div>
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-cyan-500/10 border border-cyan-400/15 text-xs uppercase tracking-widest text-cyan-300">A-Retail Solutions</div>
            <h2 class="text-4xl font-extrabold mt-5 tracking-tight">Analytics Overview</h2>
            <p class="text-slate-400 mt-2">Based on 'retaildb' tables, views, procedures, and triggers from your SQL.</p>
        </div>
        <div class="glass rounded-2xl px-6 py-4 min-w-[240px]">
            <p class="text-sm font-medium text-slate-400">Current Server Time</p>
            <p class="text-2xl font-bold text-cyan-400 mt-1"><?php echo date("h:i:s A"); ?></p>
        </div>
    </header>

    <div class="grid grid-cols-4 gap-6 mb-10">
        <div class="glass stat-card p-6 rounded-[24px] relative overflow-hidden">
            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">Inventory Health</p>
            <h3 class="text-3xl font-bold"><?php echo $pCount; ?></h3>
            <p class="text-emerald-400 text-[10px] mt-2"><i class="fas fa-boxes-stacked"></i> Total units in stock</p>
        </div>
        <div class="glass stat-card p-6 rounded-[24px] relative overflow-hidden">
            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">Sales Revenue</p>
            <h3 class="text-3xl font-bold">P<?php echo number_format($revenue, 2); ?></h3>
            <p class="text-cyan-400 text-[10px] mt-2"><i class="fas fa-circle text-[6px] mr-1"></i> From view_salesrevenue</p>
        </div>
        <div class="glass stat-card p-6 rounded-[24px] border border-red-500/20 bg-red-500/5 relative overflow-hidden">
            <p class="text-red-400 text-[10px] font-bold uppercase tracking-widest mb-1">Low Stock Alerts</p>
            <h3 class="text-3xl font-bold text-red-400">0<?php echo $lowCount; ?></h3>
            <p class="text-slate-400 text-[10px] mt-2">Items below threshold of 15</p>
        </div>
        <div class="glass stat-card p-6 rounded-[24px] relative overflow-hidden">
            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">System Triggers</p>
            <h3 class="text-3xl font-bold">3</h3>
            <p class="text-slate-400 text-[10px] mt-2">Active stock listeners</p>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-8">
        <div class="col-span-2 glass rounded-[28px] p-8">
            <h4 class="font-bold text-xl mb-6">Product Stock Distribution</h4>
            <table class="w-full text-left">
                <thead class="text-[10px] text-slate-500 uppercase border-b border-white/5">
                    <tr><th class="pb-4">Product</th><th class="pb-4">Category</th><th class="pb-4">Price</th><th class="pb-4">Stock Level</th></tr>
                </thead>
                <tbody class="text-sm">
                    <?php foreach($products as $p): ?>
                    <tr class="border-b border-white/5 hover:bg-white/5 transition">
                        <td class="py-4 font-medium text-white"><?php echo $p['ProductName']; ?></td>
                        <td class="py-4 text-slate-400"><?php echo $p['CategoryName']; ?></td>
                        <td class="py-4 font-mono text-cyan-400">P<?php echo number_format($p['Price'], 2); ?></td>
                        <td class="py-4"><span class="px-3 py-1 rounded-full text-[10px] font-bold <?php echo $p['StockQuantity'] < 15 ? 'bg-amber-500/20 text-amber-400':'bg-emerald-500/20 text-emerald-400'; ?>">Level (<?php echo $p['StockQuantity']; ?>)</span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="glass rounded-[28px] p-8 flex flex-col items-center justify-center text-center">
             <div class="w-16 h-16 bg-slate-900 rounded-2xl flex items-center justify-center border border-white/10 mb-4"><i class="fas fa-microchip text-2xl text-cyan-400"></i></div>
             <h4 class="font-bold">A-Retail Solutions</h4>
             <p class="text-[10px] text-slate-500 uppercase tracking-widest">v2.1 Information System</p>
             <p class="text-[11px] text-slate-400 mt-4 leading-relaxed">Relational model built on retaildb schema with 3 recursive triggers.</p>
        </div>
    </div>
</main>
</body></html>