<?php
// Query Kategori dengan Warna Custom untuk Chart
$data_kategori = mysqli_query($conn, "SELECT k.ket_kategori AS label, COUNT(ia.id_pelaporan) AS total 
    FROM kategori k 
    LEFT JOIN input_aspirasi ia ON k.id_kategori = ia.id_kategori 
    GROUP BY k.id_kategori, k.ket_kategori");
$label_kategori = []; $total_kategori = [];
while($g = mysqli_fetch_assoc($data_kategori)) { 
    $label_kategori[] = $g['label']; 
    $total_kategori[] = $g['total']; 
}

// Query Status
$data_status = mysqli_query($conn, "SELECT COALESCE(a.status, 'Menunggu') AS label, COUNT(ia.id_pelaporan) AS total 
    FROM input_aspirasi ia 
    LEFT JOIN aspirasi a ON ia.id_pelaporan = a.id_pelaporan 
    GROUP BY COALESCE(a.status, 'Menunggu')");
$label_status = []; $total_status = [];
while($s = mysqli_fetch_assoc($data_status)) { 
    $label_status[] = $s['label']; 
    $total_status[] = $s['total']; 
}

// Logika Filter Waktu Trend
$filter_waktu = isset($_GET['filter_waktu']) ? $_GET['filter_waktu'] : 'harian';
if ($filter_waktu == 'bulanan') {
    $sql_trend = "SELECT DATE_FORMAT(tanggal, '%Y-%m') AS label, COUNT(id_pelaporan) AS total FROM input_aspirasi WHERE tanggal IS NOT NULL GROUP BY 1 ORDER BY label ASC LIMIT 12";
} elseif ($filter_waktu == 'tahunan') {
    $sql_trend = "SELECT YEAR(tanggal) AS label, COUNT(id_pelaporan) AS total FROM input_aspirasi WHERE tanggal IS NOT NULL GROUP BY 1 ORDER BY label ASC LIMIT 5";
} else {
    $sql_trend = "SELECT DATE(tanggal) AS label, COUNT(id_pelaporan) AS total FROM input_aspirasi WHERE tanggal IS NOT NULL GROUP BY 1 ORDER BY label ASC LIMIT 10";
}

$data_trend = mysqli_query($conn, $sql_trend);
$label_trend = []; $total_trend = [];
while($t = mysqli_fetch_assoc($data_trend)) {
    $lbl = $t['label']; 
    if ($filter_waktu == 'bulanan') $label_trend[] = date('M y', strtotime($lbl . '-01')); 
    elseif ($filter_waktu == 'tahunan') $label_trend[] = $lbl; 
    else $label_trend[] = date('d/m', strtotime($lbl)); 
    $total_trend[] = $t['total'];
}
?>

<style>
    .grid-container { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
    .card-chart { background: #ffffff; border-radius: 24px; padding: 20px; border: 1px solid #f1f5f9; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.04); transition: transform 0.3s ease; }
    .card-chart:hover { transform: translateY(-5px); }
    .card-wide { grid-column: span 2; }
    .chart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
    .chart-header h4 { font-size: 0.85rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; margin: 0; }
    .filter-select { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 5px 12px; font-size: 0.75rem; font-weight: 600; color: #475569; outline: none; cursor: pointer; }
</style>

<div class="grid-container">
    <div class="card-chart">
        <div class="chart-header">
            <h4>Top Kategori</h4>
            <i class="fas fa-tags text-slate-300"></i>
        </div>
        <div style="height: 180px;"><canvas id="kategoriChartNew"></canvas></div>
    </div>

    <div class="card-chart">
        <div class="chart-header">
            <h4>Proporsi Status</h4>
            <i class="fas fa-chart-pie text-slate-300"></i>
        </div>
        <div style="height: 180px;"><canvas id="statusChartNew"></canvas></div>
    </div>

    <div class="card-chart card-wide">
        <div class="chart-header">
            <h4>Analisis Tren Laporan</h4>
            <form method="GET" action="" id="formFilter">
                <select name="filter_waktu" onchange="this.form.submit()" class="filter-select">
                    <option value="harian" <?= $filter_waktu == 'harian' ? 'selected' : '' ?>>Harian</option>
                    <option value="bulanan" <?= $filter_waktu == 'bulanan' ? 'selected' : '' ?>>Bulanan</option>
                    <option value="tahunan" <?= $filter_waktu == 'tahunan' ? 'selected' : '' ?>>Tahunan</option>
                </select>
            </form>
        </div>
        <div style="height: 220px;"><canvas id="trendChartNew"></canvas></div>
    </div>
</div>

<script>
// Konfigurasi Font Global
Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
Chart.defaults.color = '#94a3b8';

// 1. Kategori Chart (Doughnut)
new Chart(document.getElementById('kategoriChartNew'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($label_kategori); ?>,
        datasets: [{
            data: <?= json_encode($total_kategori); ?>,
            backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
            hoverOffset: 10,
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '75%',
        plugins: {
            legend: { position: 'bottom', labels: { usePointStyle: true, padding: 15, font: { size: 10, weight: '600' } } }
        }
    }
});

// 2. Status Chart (Polar Area) -> Biar beda dari pie biasa
new Chart(document.getElementById('statusChartNew'), {
    type: 'polarArea',
    data: {
        labels: <?= json_encode($label_status); ?>,
        datasets: [{
            data: <?= json_encode($total_status); ?>,
            backgroundColor: ['rgba(245, 158, 11, 0.7)', 'rgba(59, 130, 246, 0.7)', 'rgba(16, 185, 129, 0.7)'],
            borderWidth: 2,
            borderColor: '#ffffff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { r: { grid: { display: false }, ticks: { display: false } } },
        plugins: {
            legend: { position: 'bottom', labels: { usePointStyle: true, padding: 15, font: { size: 10, weight: '600' } } }
        }
    }
});

// 3. Trend Chart (Line with Gradient) -> Lebih elegan dibanding Bar
const ctxTrend = document.getElementById('trendChartNew').getContext('2d');
const gradient = ctxTrend.createLinearGradient(0, 0, 0, 400);
gradient.addColorStop(0, 'rgba(99, 102, 241, 0.2)');
gradient.addColorStop(1, 'rgba(99, 102, 241, 0)');

new Chart(ctxTrend, {
    type: 'line',
    data: {
        labels: <?= json_encode($label_trend); ?>,
        datasets: [{
            label: 'Total Laporan',
            data: <?= json_encode($total_trend); ?>,
            borderColor: '#6366f1',
            backgroundColor: gradient,
            fill: true,
            tension: 0.4,
            borderWidth: 3,
            pointBackgroundColor: '#ffffff',
            pointBorderColor: '#6366f1',
            pointBorderWidth: 2,
            pointRadius: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { borderDash: [5, 5], color: '#f1f5f9' }, ticks: { stepSize: 1 } },
            x: { grid: { display: false } }
        }
    }
});
</script>