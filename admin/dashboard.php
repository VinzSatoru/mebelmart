<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/admin.php';

$db = new Database();

// Helper function
function decimal128ToFloat($decimal128) {
    if ($decimal128 instanceof MongoDB\BSON\Decimal128) {
        return (float)$decimal128->__toString();
    }
    return (float)$decimal128;
}

// Get total orders
$orderCount = $db->getCollection('orders')->countDocuments();

// Get recent orders
$recentOrders = $db->getCollection('orders')
    ->find([], ['sort' => ['created_at' => -1], 'limit' => 10])
    ->toArray();

// Calculate total revenue
$totalRevenue = 0;
$paidOrders = $db->getCollection('orders')->find([
    'payment_status' => 'paid'
]);

foreach ($paidOrders as $order) {
    $totalRevenue += decimal128ToFloat($order->total);
}

// Get monthly revenue data
$monthlyRevenue = $db->getCollection('orders')->aggregate([
    [
        '$match' => [
            'created_at' => [
                '$gte' => new MongoDB\BSON\UTCDateTime(strtotime('-12 months') * 1000)
            ]
        ]
    ],
    [
        '$group' => [
            '_id' => [
                'year' => ['$year' => '$created_at'],
                'month' => ['$month' => '$created_at']
            ],
            'total' => ['$sum' => ['$toDouble' => '$total']]
        ]
    ],
    [
        '$sort' => ['_id.year' => 1, '_id.month' => 1]
    ]
])->toArray();

// Get top products
$topProducts = $db->getCollection('orders')->aggregate([
    [
        '$unwind' => '$items'
    ],
    [
        '$group' => [
            '_id' => '$items.product_id',
            'name' => ['$first' => '$items.name'],
            'total_quantity' => ['$sum' => '$items.quantity'],
            'revenue' => ['$sum' => ['$multiply' => [
                ['$toDouble' => '$items.price'],
                '$items.quantity'
            ]]]
        ]
    ],
    [
        '$sort' => ['revenue' => -1]
    ],
    [
        '$limit' => 5
    ]
])->toArray();

// Get total products
$productCount = $db->getCollection('products')->countDocuments();

// Get total users
$userCount = $db->getCollection('users')->countDocuments();

// Prepare chart data
$labels = [];
$revenueData = [];
$monthNames = [
    1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 
    5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug',
    9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'
];

foreach ($monthlyRevenue as $data) {
    $labels[] = $monthNames[$data->_id->month] . ' ' . $data->_id->year;
    $revenueData[] = $data->total;
}

// Calculate statistics
$avgOrderValue = $orderCount > 0 ? $totalRevenue / $orderCount : 0;
?>

<!-- HTML template -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Dashboard Admin - MebelMart</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Argon Dashboard CSS -->
    <link rel="stylesheet" href="https://demos.creative-tim.com/argon-dashboard/assets/css/argon-dashboard.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
    
    <!-- Particles.js -->
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    
    <style>
        body {
            background-color: #f8fafc !important;
        }
        
        #particles-js {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 0;
            opacity: 0.5;
            pointer-events: none;
        }
        
        .main-content {
            position: relative;
            z-index: 1;
        }
        
        /* Card styles */
        .card {
            backdrop-filter: blur(5px);
            background: rgba(255, 255, 255, 0.9) !important;
            border: none;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background: #ffffff !important;
            border-bottom: 2px solid #f1f5f9;
        }

        /* Text colors */
        .card-title {
            color: #1e293b !important;
            font-weight: 700;
        }

        /* Statistics cards */
        .numbers p {
            color: #64748b !important;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
        }

        .numbers h5 {
            color: #1e293b !important;
            font-weight: 700;
            font-size: 1.5rem;
        }

        /* Gradient backgrounds for stat cards */
        .bg-gradient-primary {
            background: linear-gradient(135deg, #6366f1 0%, #818cf8 100%);
        }
        
        .bg-gradient-success {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
        }
        
        .bg-gradient-info {
            background: linear-gradient(135deg, #0ea5e9 0%, #38bdf8 100%);
        }
        
        .bg-gradient-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
        }

        /* Navigation */
        .sidenav {
            background: #ffffff !important;
            box-shadow: 2px 0 12px rgba(0, 0, 0, 0.08);
        }

        .nav-link {
            color: #64748b !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: #6366f1 !important;
            background: rgba(99, 102, 241, 0.1);
        }

        .nav-link.active {
            background: #6366f1 !important;
            color: #ffffff !important;
            font-weight: 600;
        }

        /* Top Products List */
        .list-group-item {
            background: #ffffff !important;
            border-color: #f1f5f9 !important;
            padding: 1rem;
            transition: all 0.2s ease;
        }

        .list-group-item:hover {
            background: #f8fafc !important;
            transform: translateX(5px);
        }

        .list-group-item h6 {
            color: #1e293b !important;
            font-weight: 600;
        }

        .list-group-item .text-xs {
            color: #64748b !important;
        }

        .list-group-item .font-weight-bold {
            color: #6366f1 !important;
            font-weight: 700;
        }

        /* Table styles */
        .table th {
            color: #6366f1 !important;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            padding-top: 1rem;
            padding-bottom: 1rem;
        }

        .table td {
            color: #1e293b !important;
            font-weight: 500;
            padding-top: 1rem;
            padding-bottom: 1rem;
            vertical-align: middle;
        }

        /* Status badges */
        .badge {
            padding: 0.5rem 0.8rem;
            font-weight: 600;
        }

        .badge-success { background: #dcfce7 !important; color: #059669 !important; }
        .badge-warning { background: #fef3c7 !important; color: #d97706 !important; }
        .badge-info { background: #e0f2fe !important; color: #0284c7 !important; }
        .badge-danger { background: #fee2e2 !important; color: #dc2626 !important; }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #6366f1;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #4f46e5;
        }

        /* Icons */
        .icon-shape {
            width: 48px;
            height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.75rem;
            position: relative;
            z-index: 10;
            padding-bottom:20px;

        }
        
        .icon-shape span {
            line-height: ;
        }

        /* Memastikan konten card di atas particles */
        .card-body {
            position: relative;
            z-index: 2;
        }
    </style>
</head>

<body class="g-sidenav-show">
    <div id="particles-js"></div>
    
    <!-- Sidebar -->
    <aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4" id="sidenav-main">
        <div class="sidenav-header">
            <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
            <a class="navbar-brand m-0" href="dashboard.php">
                <span class="ms-1 font-weight-bold">MebelMart Admin</span>
            </a>
        </div>
        
        
        <hr class="horizontal dark mt-0">
        <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
            <ul class="navbar-nav">
                <?php foreach ($adminNav as $nav): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage == $nav['url'] ? 'active' : '' ?>" href="<?= BASE_URL . $nav['url'] ?>">
                            <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="<?= $nav['icon'] ?> text-primary text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1"><?= $nav['title'] ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </aside>

    <!-- Main content -->
    <main class="main-content position-relative border-radius-lg">
        <!-- Navigation -->
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur">
            <div class="container-fluid py-1 px-3">
                <nav aria-label="breadcrumb">
                    <h6 class="font-weight-bolder mb-0">Dashboard</h6>
                </nav>
                <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
                    <div class="ms-md-auto pe-md-3 d-flex align-items-center"></div>
                    <ul class="navbar-nav justify-content-end">
                        <li class="nav-item d-flex align-items-center">
                            <a href="<?= BASE_URL ?>/auth/logout.php" class="nav-link text-body font-weight-bold px-0">
                                <i class="fa fa-user me-sm-1"></i>
                                <span class="d-sm-inline d-none">Logout</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Dashboard content -->
        <div class="container-fluid py-2">
            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-xl-3 col-sm-6 mb-4">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <div class="numbers">
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Pendapatan</p>
                                        <h5 class="font-weight-bolder mb-0">
                                            <?= formatCurrency($totalRevenue) ?>
                                        </h5>
                                    </div>
                                </div>
                                <div class="col-4 text-end">
                                    <div class="icon icon-shape bg-gradient-primary shadow text-center">
                                        <span style="font-size: 24px; color: white;">
                                            <i class="fa-solid fa-money-bill-wave"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-4">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <div class="numbers">
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Produk</p>
                                        <h5 class="font-weight-bolder mb-0">
                                            <?= number_format($productCount) ?>
                                        </h5>
                                    </div>
                                </div>
                                <div class="col-4 text-end">
                                    <div class="icon icon-shape bg-gradient-warning shadow text-center">
                                        <span style="font-size: 24px; color: white;">
                                            <i class="fa-solid fa-box-open"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-4">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <div class="numbers">
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Pesanan</p>
                                        <h5 class="font-weight-bolder mb-0">
                                            <?= number_format($orderCount) ?>
                                        </h5>
                                    </div>
                                </div>
                                <div class="col-4 text-end">
                                    <div class="icon icon-shape bg-gradient-success shadow text-center">
                                        <span style="font-size: 24px; color: white;">
                                            <i class="fa-solid fa-shopping-bag"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-4">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <div class="numbers">
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Pengguna</p>
                                        <h5 class="font-weight-bolder mb-0">
                                            <?= number_format($userCount) ?>
                                        </h5>
                                    </div>
                                </div>
                                <div class="col-4 text-end">
                                    <div class="icon icon-shape bg-gradient-info shadow text-center">
                                        <span style="font-size: 24px; color: white;">
                                            <i class="fa-solid fa-users"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="row mt-4">
                <div class="col-lg-8 mb-4">
                    <div class="card">
                        <div class="card-header pb-0">
                            <h6>Pendapatan Bulanan</h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="chart">
                                <canvas id="revenueChart" class="chart-canvas" height="300" 
                                        style="background: linear-gradient(to bottom, rgba(94, 114, 228, 0.1), rgba(94, 114, 228, 0.0));">
                                </canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header pb-0">
                            <h6 class="mb-0">Produk Terlaris</h6>
                        </div>
                        <div class="card-body px-0 pt-0 pb-2">
                            <div class="table-responsive p-0">
                                <ul class="list-group mx-3">
                                    <?php foreach ($topProducts as $product): ?>
                                        <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                                            <div class="d-flex align-items-center">
                                                <div class="d-flex flex-column">
                                                    <h6 class="mb-1"><?= $product->name ?></h6>
                                                    <span class="text-xs"><?= number_format($product->total_quantity) ?> terjual</span>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="font-weight-bold">
                                                    <?= formatCurrency($product->revenue) ?>
                                                </span>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header pb-0">
                            <h6>Pesanan Terbaru</h6>
                        </div>
                        <div class="card-body px-0 pt-0 pb-2">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID Pesanan</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Pelanggan</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Total</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentOrders as $order): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex px-3 py-1">
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm">#<?= substr($order->_id, -8) ?></h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="text-sm font-weight-bold mb-0"><?= $order->shipping_info->full_name ?></p>
                                                </td>
                                                <td class="align-middle text-center text-sm">
                                                    <p class="text-sm font-weight-bold mb-0"><?= formatCurrency($order->total) ?></p>
                                                </td>
                                                <td class="align-middle text-center text-sm">
                                                    <span class="badge badge-sm bg-gradient-<?= getStatusBadgeClass($order->status) ?>">
                                                        <?= getStatusText($order->status) ?>
                                                    </span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span class="text-secondary text-sm font-weight-bold">
                                                        <?= formatDate($order->created_at) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Chart initialization -->
    <script>
        particlesJS('particles-js', {
            particles: {
                number: {
                    value: 40,
                    density: {
                        enable: true,
                        value_area: 800
                    }
                },
                color: {
                    value: ['#6366f1', '#10b981', '#0ea5e9', '#f59e0b']
                },
                shape: {
                    type: 'circle'
                },
                opacity: {
                    value: 0.3,
                    random: true
                },
                size: {
                    value: 4,
                    random: true
                },
                line_linked: {
                    enable: true,
                    distance: 150,
                    color: '#6366f1',
                    opacity: 0.2,
                    width: 1
                },
                move: {
                    enable: true,
                    speed: 2,
                    direction: 'none',
                    random: true,
                    straight: false,
                    out_mode: 'out',
                    bounce: false
                }
            },
            interactivity: {
                detect_on: 'canvas',
                events: {
                    onhover: {
                        enable: true,
                        mode: 'grab'
                    },
                    onclick: {
                        enable: true,
                        mode: 'push'
                    },
                    resize: true
                },
                modes: {
                    grab: {
                        distance: 140,
                        line_linked: {
                            opacity: 0.4
                        }
                    },
                    push: {
                        particles_nb: 3
                    }
                }
            }
        });

        // Update chart configuration
        var ctx = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Pendapatan',
                    data: <?= json_encode($revenueData) ?>,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#6366f1',
                    pointBorderWidth: 3,
                    pointHoverRadius: 8,
                    pointHoverBorderWidth: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#ffffff',
                        titleColor: '#1e293b',
                        bodyColor: '#64748b',
                        borderColor: '#e2e8f0',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                let value = context.parsed.y;
                                return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        grid: {
                            color: '#f1f5f9',
                            borderDash: [5,5]
                        },
                        ticks: {
                            color: '#64748b',
                            font: {
                                size: 11,
                                weight: '500'
                            },
                            callback: function(value) {
                                return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#64748b',
                            font: {
                                size: 11,
                                weight: '500'
                            }
                        }
                    }
                }
            }
        });
    </script>

    <!-- Core JS Files -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://demos.creative-tim.com/argon-dashboard/assets/js/core/popper.min.js"></script>
    <script src="https://demos.creative-tim.com/argon-dashboard/assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="https://demos.creative-tim.com/argon-dashboard/assets/js/plugins/smooth-scrollbar.min.js"></script>
    <script src="https://demos.creative-tim.com/argon-dashboard/assets/js/argon-dashboard.min.js"></script>
</body>
</html>

