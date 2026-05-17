<?php
session_start();
if(!isset($_SESSION['user'])) header("Location: index.php");
include_once 'layout.php';
getHeader("About System"); renderSidebar("about");
?>
<main class="flex-grow p-10 flex items-center justify-center content-animate">
    <div class="max-w-4xl w-full glass p-12 rounded-[40px] text-center shadow-2xl relative">
        <div class="inline-flex items-center justify-center w-24 h-24 bg-slate-900 rounded-full mb-8 border border-white/10 shadow-inner shadow-cyan-500/10">
            <i class="fas fa-microchip text-4xl text-cyan-400"></i>
        </div>
        <h2 class="text-5xl font-extrabold mb-2 tracking-tight">A-Retail <span class="text-cyan-400">Solutions</span></h2>
        <p class="text-slate-500 font-bold tracking-[0.3em] text-[10px] mb-12 uppercase">Information System v2.1</p>

        <div class="grid grid-cols-2 gap-8 text-left mb-12">
            <div class="bg-white/5 p-8 rounded-[24px] border border-white/5">
                <p class="text-cyan-400 text-[10px] font-black uppercase mb-4 tracking-widest">Architecture</p>
                <p class="text-sm text-slate-300 leading-relaxed">A-Retail Solutions V2.1 is engineered on the retaildb schema, a high-normalization relational model. It features advanced automation through three recursive triggers for inventory precision and optimized business intelligence views.</p>
            </div>
            <div class="bg-white/5 p-8 rounded-[24px] border border-white/5 flex flex-col justify-center">
                <p class="text-violet-300 text-[10px] font-black uppercase mb-2 tracking-widest">Database Name</p>
                <p class="text-3xl font-extrabold text-white">retaildb</p>
            </div>
        </div>

        <div class="text-slate-500 text-[10px] space-y-2 font-mono uppercase tracking-widest">
            <p>Store Brand: A-Retail Solutions</p>
            <p>Schema Alias: retaildb</p>
            <p>&copy; 2026 A-RETAIL SOLUTIONS</p>
        </div>
    </div>
</main>
</body></html>