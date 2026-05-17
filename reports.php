<?php
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}
include_once 'Database.php';
include_once 'layout.php';
$db = Database::getConnection();

require_once __DIR__ . '/vendor/autoload.php';

function reportDefinition(PDO $db, $type) {
    if ($type === 'inventory') {
        $records = $db->query("
            SELECT p.ProductID, p.ProductName, c.CategoryName, s.SupplierName, p.Price, p.StockQuantity
            FROM products p
            JOIN categories c ON p.CategoryID = c.CategoryID
            JOIN suppliers s ON p.SupplierID = s.SupplierID
            ORDER BY p.ProductName
        ")->fetchAll(PDO::FETCH_ASSOC);

        $chart = [];
        foreach ($records as $row) {
            $chart[] = [
                'label' => $row['ProductName'],
                'value' => (float)$row['StockQuantity'],
                'format' => 'Number'
            ];
        }

        return [
            'filename' => 'A-Retail-Inventory-Count-Report.xls',
            'title' => 'Inventory Count Report',
            'subtitle' => 'Current product stock levels by item',
            'columns' => ['Product ID', 'Product', 'Category', 'Supplier', 'Unit Price', 'Stock Qty'],
            'rows' => array_map(function($r) {
                return [
                    ['#'.$r['ProductID'], 'String'],
                    [$r['ProductName'], 'String'],
                    [$r['CategoryName'], 'String'],
                    [$r['SupplierName'], 'String'],
                    [(float)$r['Price'], 'Money'],
                    [(float)$r['StockQuantity'], 'Number']
                ];
            }, $records),
            'chartTitle' => 'Sheet 2: Stock Quantity Graph',
            'chartMetric' => 'Stock Qty',
            'chart' => $chart
        ];
    }

    if ($type === 'users') {
        $records = $db->query("
            SELECT UserID, FullName, Username, Email, Role, Status
            FROM users
            ORDER BY UserID DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $statusCounts = ['Active' => 0, 'Inactive' => 0];
        foreach ($records as $row) {
            $status = $row['Status'] ?: 'Inactive';
            if (!isset($statusCounts[$status])) {
                $statusCounts[$status] = 0;
            }
            $statusCounts[$status]++;
        }

        $chart = [];
        foreach ($statusCounts as $label => $value) {
            $chart[] = ['label' => $label, 'value' => (float)$value, 'format' => 'Number'];
        }

        return [
            'filename' => 'A-Retail-User-Access-Report.xls',
            'title' => 'User Access Management Report',
            'subtitle' => 'Registered system users and account status',
            'columns' => ['User ID', 'Full Name', 'Username', 'Email', 'Role', 'Status'],
            'rows' => array_map(function($r) {
                return [
                    ['#'.$r['UserID'], 'String'],
                    [$r['FullName'], 'String'],
                    [$r['Username'], 'String'],
                    [$r['Email'], 'String'],
                    [$r['Role'], 'String'],
                    [$r['Status'], 'String']
                ];
            }, $records),
            'chartTitle' => 'Sheet 2: User Status Graph',
            'chartMetric' => 'Accounts',
            'chart' => $chart
        ];
    }

    $records = $db->query("
        SELECT o.OrderID, o.OrderDate, COALESCE(v.TotalRevenue, o.TotalAmount, 0) AS TotalRevenue
        FROM orders o
        LEFT JOIN view_salesrevenue v ON o.OrderID = v.OrderID
        ORDER BY o.OrderDate DESC, o.OrderID DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $chartMap = [];
    foreach ($records as $row) {
        $month = date('M Y', strtotime($row['OrderDate']));
        if (!isset($chartMap[$month])) {
            $chartMap[$month] = 0;
        }
        $chartMap[$month] += (float)$row['TotalRevenue'];
    }

    $chart = [];
    foreach ($chartMap as $label => $value) {
        $chart[] = ['label' => $label, 'value' => (float)$value, 'format' => 'Money'];
    }

    return [
        'filename' => 'A-Retail-Sales-Transaction-Report.xls',
        'title' => 'Sales Transaction Report',
        'subtitle' => 'Order history generated from sales transactions',
        'columns' => ['Order ID', 'Order Date', 'Revenue'],
        'rows' => array_map(function($r) {
            return [
                ['#'.$r['OrderID'], 'String'],
                [$r['OrderDate'], 'String'],
                [(float)$r['TotalRevenue'], 'Money']
            ];
        }, $records),
        'chartTitle' => 'Sheet 2: Sales Revenue Graph',
        'chartMetric' => 'Revenue',
        'chart' => $chart
    ];
}

function outputWorkbook(array $report, $generatedBy) {
    $filename = str_replace('.xls', '.xlsx', $report['filename']);
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    
    // --- Sheet 1: Records ---
    $sheet1 = $spreadsheet->getActiveSheet();
    $sheet1->setTitle('Sheet 1 - Records');

    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F172A']],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
    ];
    
    $sheet1->setCellValue('B2', 'A-RETAIL SOLUTIONS');
    $sheet1->getStyle('B2')->getFont()->setBold(true)->setSize(18);
    $sheet1->setCellValue('B3', $report['title']);
    $sheet1->getStyle('B3')->getFont()->setBold(true)->setSize(14);
    $sheet1->setCellValue('B4', $report['subtitle']);
    $sheet1->setCellValue('B5', 'Date Generated: ' . date('Y-m-d h:i A'));
    
    $col = 'A';
    foreach ($report['columns'] as $column) {
        $sheet1->setCellValue($col . '7', $column);
        $sheet1->getStyle($col . '7')->applyFromArray($headerStyle);
        $sheet1->getColumnDimension($col)->setWidth(20);
        $col++;
    }

    $rowNum = 8;
    foreach ($report['rows'] as $row) {
        $col = 'A';
        foreach ($row as $cell) {
            $val = $cell[0];
            if ($cell[1] === 'Money' || $cell[1] === 'Number') {
                $sheet1->setCellValueExplicit($col . $rowNum, $val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                if ($cell[1] === 'Money') {
                    $sheet1->getStyle($col . $rowNum)->getNumberFormat()->setFormatCode('"P"#,##0.00');
                } else {
                    $sheet1->getStyle($col . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
                }
            } else {
                $sheet1->setCellValueExplicit($col . $rowNum, $val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }
            $col++;
        }
        $rowNum++;
    }
    
    $rowNum += 2;
    $sheet1->setCellValue('A' . $rowNum, 'Prepared by: ' . ($generatedBy ?: 'System Administrator'));
    $sheet1->setCellValue('A' . ($rowNum + 1), 'Signature: ________________________________');

    // --- Sheet 2: Graph ---
    $sheet2 = $spreadsheet->createSheet();
    $sheet2->setTitle('Sheet 2 - Graph');
    $sheet2->setCellValue('A1', $report['chartTitle']);
    $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    
    $sheet2->setCellValue('A3', 'Category');
    $sheet2->setCellValue('B3', $report['chartMetric']);
    $sheet2->getStyle('A3:B3')->applyFromArray($headerStyle);
    $sheet2->getColumnDimension('A')->setWidth(30);
    $sheet2->getColumnDimension('B')->setWidth(15);
    
    $rowNum = 4;
    foreach ($report['chart'] as $chartRow) {
        $sheet2->setCellValue('A' . $rowNum, $chartRow['label']);
        $sheet2->setCellValue('B' . $rowNum, $chartRow['value']);
        if ($chartRow['format'] === 'Money') {
            $sheet2->getStyle('B' . $rowNum)->getNumberFormat()->setFormatCode('"P"#,##0.00');
        } else {
            $sheet2->getStyle('B' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
        }
        $rowNum++;
    }
    
    // Create Chart
    $dataSeriesLabels = [
        new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('String', "'Sheet 2 - Graph'!\$B\$3", null, 1),
    ];
    $xAxisTickValues = [
        new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('String', "'Sheet 2 - Graph'!\$A\$4:\$A\$" . ($rowNum - 1), null, $rowNum - 4),
    ];
    $dataSeriesValues = [
        new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('Number', "'Sheet 2 - Graph'!\$B\$4:\$B\$" . ($rowNum - 1), null, $rowNum - 4),
    ];
    
    $series = new \PhpOffice\PhpSpreadsheet\Chart\DataSeries(
        \PhpOffice\PhpSpreadsheet\Chart\DataSeries::TYPE_BARCHART,
        \PhpOffice\PhpSpreadsheet\Chart\DataSeries::GROUPING_STANDARD,
        range(0, count($dataSeriesValues) - 1),
        $dataSeriesLabels,
        $xAxisTickValues,
        $dataSeriesValues
    );
    $series->setPlotDirection(\PhpOffice\PhpSpreadsheet\Chart\DataSeries::DIRECTION_COL);
    
    $plotArea = new \PhpOffice\PhpSpreadsheet\Chart\PlotArea(null, [$series]);
    $title = new \PhpOffice\PhpSpreadsheet\Chart\Title($report['chartTitle']);
    $chart = new \PhpOffice\PhpSpreadsheet\Chart\Chart(
        'chart1',
        $title,
        null,
        $plotArea,
        true,
        0,
        null,
        null
    );
    
    $chart->setTopLeftPosition('D3');
    $chart->setBottomRightPosition('O20');
    $sheet2->addChart($chart);

    // Export
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->setIncludeCharts(true);
    $writer->save('php://output');
    exit;
}

if (isset($_GET['export'])) {
    $reportType = in_array($_GET['export'], ['sales', 'inventory', 'users'], true) ? $_GET['export'] : 'sales';
    $report = reportDefinition($db, $reportType);
    outputWorkbook($report, $_SESSION['user']['FullName'] ?? 'Admin');
}

$orders = $db->query("
    SELECT o.OrderID, o.OrderDate, COALESCE(v.TotalRevenue, o.TotalAmount, 0) AS TotalAmount
    FROM orders o
    LEFT JOIN view_salesrevenue v ON o.OrderID = v.OrderID
    ORDER BY o.OrderDate DESC, o.OrderID DESC
")->fetchAll(PDO::FETCH_ASSOC);
$products = $db->query("
    SELECT p.ProductID, p.ProductName, c.CategoryName, p.Price, p.StockQuantity
    FROM products p
    JOIN categories c ON p.CategoryID = c.CategoryID
    ORDER BY p.ProductName
")->fetchAll(PDO::FETCH_ASSOC);
$users = $db->query("SELECT UserID, FullName, Username, Status FROM users ORDER BY UserID DESC")->fetchAll(PDO::FETCH_ASSOC);
getHeader("Report Engine");
renderSidebar('reports');
?>
<main class="flex-grow p-10 overflow-y-auto content-animate">
    <div class="flex justify-between items-end mb-10">
        <div>
            <h2 class="text-4xl font-extrabold tracking-tight">Report Engine</h2>
            <p class="text-slate-400 mt-2">Generate Excel templates with record data on Sheet 1 and graph data on Sheet 2.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <a href="?export=sales" class="glass p-6 rounded-[24px] border border-white/5 hover:border-emerald-400/40 transition group">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-5 group-hover:bg-emerald-500 group-hover:text-white transition"><i class="fas fa-file-excel"></i></div>
            <h3 class="font-bold text-xl">Sales Transaction</h3>
            <p class="text-sm text-slate-400 mt-2">Orders, generated revenue, and Sheet 2 revenue graph.</p>
        </a>
        <a href="?export=inventory" class="glass p-6 rounded-[24px] border border-white/5 hover:border-cyan-400/40 transition group">
            <div class="w-12 h-12 rounded-2xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center mb-5 group-hover:bg-cyan-500 group-hover:text-white transition"><i class="fas fa-boxes-stacked"></i></div>
            <h3 class="font-bold text-xl">Inventory Count</h3>
            <p class="text-sm text-slate-400 mt-2">Product stock list with Sheet 2 stock quantity graph.</p>
        </a>
        <a href="?export=users" class="glass p-6 rounded-[24px] border border-white/5 hover:border-violet-400/40 transition group">
            <div class="w-12 h-12 rounded-2xl bg-violet-500/10 text-violet-400 flex items-center justify-center mb-5 group-hover:bg-violet-500 group-hover:text-white transition"><i class="fas fa-users"></i></div>
            <h3 class="font-bold text-xl">User Access</h3>
            <p class="text-sm text-slate-400 mt-2">User management report with Sheet 2 status graph.</p>
        </a>
    </div>

    <div class="glass p-8 rounded-[32px] border border-white/5 mb-8">
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-bold text-xl">Sales Transaction Data Grid</h3>
            <span class="text-[10px] uppercase tracking-widest text-slate-500">orders</span>
        </div>
        <table class="w-full text-left">
            <thead class="text-slate-500 text-[10px] uppercase border-b border-white/10">
                <tr><th class="pb-4">Order ID</th><th class="pb-4">Timestamp</th><th class="pb-4">Total Amount</th><th class="pb-4">Status</th></tr>
            </thead>
            <tbody class="text-sm">
                <?php foreach($orders as $o): ?>
                <tr class="border-b border-white/5 hover:bg-white/5 transition">
                    <td class="py-5 font-bold text-cyan-400">#<?php echo str_pad($o['OrderID'], 4, '0', STR_PAD_LEFT); ?></td>
                    <td class="py-5 text-slate-300"><?php echo date("M d, Y", strtotime($o['OrderDate'])); ?></td>
                    <td class="py-5 font-bold">P<?php echo number_format($o['TotalAmount'], 2); ?></td>
                    <td class="py-5"><span class="bg-emerald-500/10 text-emerald-400 px-3 py-1 rounded-full text-[10px] font-bold">PROCESSED</span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        <div class="glass p-8 rounded-[32px] border border-white/5">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-bold text-xl">Inventory Count Data Grid</h3>
                <span class="text-[10px] uppercase tracking-widest text-slate-500">products</span>
            </div>
            <table class="w-full text-left">
                <thead class="text-slate-500 text-[10px] uppercase border-b border-white/10">
                    <tr><th class="pb-4">Product</th><th class="pb-4">Category</th><th class="pb-4">Price</th><th class="pb-4">Stock</th></tr>
                </thead>
                <tbody class="text-sm">
                    <?php foreach($products as $p): ?>
                    <tr class="border-b border-white/5 hover:bg-white/5 transition">
                        <td class="py-5 font-medium"><?php echo htmlspecialchars($p['ProductName']); ?></td>
                        <td class="py-5 text-slate-400"><?php echo htmlspecialchars($p['CategoryName']); ?></td>
                        <td class="py-5 font-bold text-cyan-400">P<?php echo number_format($p['Price'], 2); ?></td>
                        <td class="py-5"><?php echo $p['StockQuantity']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="glass p-8 rounded-[32px] border border-white/5">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-bold text-xl">User Access Data Grid</h3>
                <span class="text-[10px] uppercase tracking-widest text-slate-500">users</span>
            </div>
            <table class="w-full text-left">
                <thead class="text-slate-500 text-[10px] uppercase border-b border-white/10">
                    <tr><th class="pb-4">Full Name</th><th class="pb-4">Username</th><th class="pb-4">Status</th></tr>
                </thead>
                <tbody class="text-sm">
                    <?php foreach($users as $u): ?>
                    <tr class="border-b border-white/5 hover:bg-white/5 transition">
                        <td class="py-5 font-medium"><?php echo htmlspecialchars($u['FullName']); ?></td>
                        <td class="py-5 text-slate-400"><?php echo htmlspecialchars($u['Username']); ?></td>
                        <td class="py-5">
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest <?php echo $u['Status']=='Active'?'bg-emerald-500/10 text-emerald-400':'bg-red-500/10 text-red-400'; ?>">
                                <?php echo htmlspecialchars($u['Status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</body></html>
