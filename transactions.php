<?php 
session_start();
include_once 'Database.php'; include_once 'layout.php';
$db = Database::getConnection();

$products = $db->query("SELECT ProductID, ProductName, Price FROM products")->fetchAll(PDO::FETCH_ASSOC);

if(isset($_POST['trans_type'])){
    $type = $_POST['trans_type'];
    $pid = $_POST['pid'];
    $qty = $_POST['qty'];
    if($type == 'sale'){
        $db->query("INSERT INTO orders (OrderDate, TotalAmount) VALUES (CURDATE(), 0)");
        $oid = $db->lastInsertId();
        $db->prepare("INSERT INTO orderitems (OrderID, ProductID, Quantity) VALUES (?, ?, ?)")->execute([$oid, $pid, $qty]);
        $msg = "Sale Processed! Stock reduced via Trigger.";
    } else if($type == 'adjust'){
        $db->prepare("CALL UpdateProductStock(?, ?)")->execute([$pid, $qty]);
        $msg = "Stock Adjusted via Procedure.";
    } else if($type == 'restock'){
        $db->prepare("UPDATE products SET StockQuantity = StockQuantity + ?, Price = ? WHERE ProductID = ?")->execute([$qty, $_POST['price'], $pid]);
        $msg = "Inventory Updated successfully.";
    }
}

getHeader("Transactions"); renderSidebar('transactions');
?>
<main class="flex-grow p-10 overflow-y-auto content-animate">
    <h2 class="text-4xl font-extrabold mb-2 tracking-tight">Core Transactions</h2>
    <p class="text-slate-400 mb-8">Execute primary system logic and triggers.</p>
    
    <?php if(isset($msg)) echo "<div class='bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 p-4 rounded-2xl mb-8'>$msg</div>"; ?>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="glass p-8 rounded-[32px]">
            <h3 class="font-bold text-xl mb-6 text-cyan-400"><i class="fas fa-cart-shopping mr-2"></i> Sales Entry</h3>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="trans_type" value="sale">
                <label class="text-[10px] text-slate-500 uppercase font-bold tracking-widest">Target Item</label>
                <select name="pid" class="w-full bg-slate-900/50 border border-slate-800 p-4 rounded-2xl text-white outline-none focus:ring-2 focus:ring-cyan-500">
                    <?php foreach($products as $p): ?><option value="<?php echo $p['ProductID']; ?>"><?php echo $p['ProductName']; ?></option><?php endforeach; ?>
                </select>
                <label class="text-[10px] text-slate-500 uppercase font-bold tracking-widest">Sale Qty</label>
                <input type="number" name="qty" placeholder="0" class="w-full bg-slate-900/50 border border-slate-800 p-4 rounded-2xl text-white outline-none" required>
                <button class="w-full bg-cyan-500 py-4 rounded-2xl font-bold shadow-lg shadow-cyan-500/30 transition hover:brightness-110">Finalize Transaction</button>
            </form>
        </div>

        <div class="glass p-8 rounded-[32px]">
            <h3 class="font-bold text-xl mb-6 text-violet-400"><i class="fas fa-sliders mr-2"></i> Procedure Update</h3>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="trans_type" value="adjust">
                <label class="text-[10px] text-slate-500 uppercase font-bold tracking-widest">Product</label>
                <select name="pid" class="w-full bg-slate-900/50 border border-slate-800 p-4 rounded-2xl text-white outline-none">
                    <?php foreach($products as $p): ?><option value="<?php echo $p['ProductID']; ?>"><?php echo $p['ProductName']; ?></option><?php endforeach; ?>
                </select>
                <label class="text-[10px] text-slate-500 uppercase font-bold tracking-widest">Adjustment Value</label>
                <input type="number" name="qty" placeholder="+/- Qty" class="w-full bg-slate-900/50 border border-slate-800 p-4 rounded-2xl text-white outline-none" required>
                <button class="w-full bg-violet-500 py-4 rounded-2xl font-bold shadow-lg shadow-violet-500/30 transition hover:brightness-110">Apply Logic</button>
            </form>
        </div>

        <div class="glass p-8 rounded-[32px]">
            <h3 class="font-bold text-xl mb-6 text-emerald-400"><i class="fas fa-truck-ramp-box mr-2"></i> Supplier Restock</h3>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="trans_type" value="restock">
                <label class="text-[10px] text-slate-500 uppercase font-bold tracking-widest">Product</label>
                <select name="pid" class="w-full bg-slate-900/50 border border-slate-800 p-4 rounded-2xl text-white outline-none">
                    <?php foreach($products as $p): ?><option value="<?php echo $p['ProductID']; ?>"><?php echo $p['ProductName']; ?></option><?php endforeach; ?>
                </select>
                <input type="number" name="qty" placeholder="Units Received" class="w-full bg-slate-900/50 border border-slate-800 p-4 rounded-2xl text-white outline-none" required>
                <input type="number" step="0.01" name="price" placeholder="New Unit Price" class="w-full bg-slate-900/50 border border-slate-800 p-4 rounded-2xl text-white outline-none" required>
                <button class="w-full bg-emerald-500 py-4 rounded-2xl font-bold transition hover:brightness-110">Update Data</button>
            </form>
        </div>
    </div>
</main>
</body></html>