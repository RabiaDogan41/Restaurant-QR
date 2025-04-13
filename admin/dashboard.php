<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Ürünleri ve kategori bilgilerini çekmek için sorguyu çalıştır
$stmt = $pdo->query("SELECT p.id, p.name, p.description, p.price, p.image, c.name AS category_name
                     FROM products p
                     LEFT JOIN categories c ON p.category_id = c.id");

$products = $stmt->fetchAll();

// Ürün silme işlemi
if (isset($_GET['delete'])) {
    $productId = $_GET['delete'];

    // Resim yolunu al
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if ($product) {
        $imagePath = '../uploads/' . $product['image'];

        // Dosyanın var olup olmadığını kontrol edin
        if (file_exists($imagePath)) {
            if (!unlink($imagePath)) {
                die("Resim silinemedi: $imagePath");
            }
        } else {
            echo "Resim dosyası bulunamadı: $imagePath<br>";
        }

        // Ürünü veritabanından sil
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$productId]);
    }

    // Sayfayı yenileyerek yönlendir
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürün Yönetimi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #475569;
            --light-bg: #f8fafc;
            --white: #ffffff;
            --dark: #1e293b;
            --danger: #ef4444;
            --success: #10b981;
            --warning: #f59e0b;
            --info: #3b82f6;
            --border-color: #e2e8f0;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --header-height: 70px;
            --sidebar-width: 250px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--light-bg);
            color: var(--secondary-color);
            min-height: 100vh;
        }

        /* Dashboard Layout */
        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--white);
            border-right: 1px solid var(--border-color);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
            z-index: 100;
            transition: all 0.3s ease;
        }

        .sidebar-header {
            height: var(--header-height);
            display: flex;
            align-items: center;
            padding: 0 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .sidebar-header h2 {
            color: var(--primary-color);
            font-size: 1.5rem;
            font-weight: 600;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .sidebar-menu ul {
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 5px;
        }

        .sidebar-menu a {
            text-decoration: none;
            color: var(--secondary-color);
            display: flex;
            align-items: center;
            padding: 12px 20px;
            transition: all 0.3s;
            font-weight: 500;
        }

        .sidebar-menu a:hover {
            background-color: rgba(37, 99, 235, 0.1);
            color: var(--primary-color);
        }

        .sidebar-menu a.active {
            background-color: var(--primary-color);
            color: var(--white);
        }

        .sidebar-menu i {
            margin-right: 10px;
            font-size: 1.2rem;
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            overflow-x: hidden;
        }

        /* Header */
        .header {
            height: var(--header-height);
            background-color: var(--white);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 99;
        }

        .header h1 {
            color: var(--dark);
            font-size: 1.5rem;
            font-weight: 600;
        }

        .header-right {
            display: flex;
            align-items: center;
        }

        .header-right a {
            color: var(--secondary-color);
            text-decoration: none;
            margin-left: 20px;
            font-weight: 500;
            transition: color 0.3s;
        }

        .header-right a:hover {
            color: var(--primary-color);
        }

        .header-right .btn-logout {
            background-color: var(--primary-color);
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .header-right .btn-logout:hover {
            background-color: #1d4ed8;
        }

        /* Content Area */
        .content {
            padding: 30px;
        }

        .page-title {
            margin-bottom: 20px;
            font-weight: 600;
            color: var(--dark);
            font-size: 1.8rem;
        }

        /* Product Grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        /* Product Card */
        .product-card {
            background-color: var(--white);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .product-image {
            position: relative;
            height: 200px;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .product-card:hover .product-image img {
            transform: scale(1.05);
        }

        .product-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: var(--info);
            color: white;
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 4px;
        }

        .product-details {
            padding: 20px;
        }

        .product-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 10px;
            height: 2.5rem;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .product-description {
            color: var(--secondary-color);
            font-size: 0.9rem;
            margin-bottom: 15px;
            height: 4rem;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }

        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .product-price {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--primary-color);
        }

        .product-category {
            background-color: rgba(37, 99, 235, 0.1);
            color: var(--primary-color);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .product-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 500;
            font-size: 0.9rem;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 1;
        }

        .btn i {
            margin-right: 6px;
        }

        .btn-edit {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-edit:hover {
            background-color: #1d4ed8;
            transform: translateY(-2px);
        }

        .btn-delete {
            background-color: var(--danger);
            color: white;
        }

        .btn-delete:hover {
            background-color: #dc2626;
            transform: translateY(-2px);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 0;
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--border-color);
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .empty-state p {
            color: var(--secondary-color);
            margin-bottom: 20px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .menu-toggle {
                display: block;
            }

            .header {
                padding: 0 20px;
            }
        }

        @media (max-width: 576px) {
            .product-grid {
                grid-template-columns: 1fr;
            }

            .content {
                padding: 20px 15px;
            }

            .header h1 {
                font-size: 1.2rem;
            }
        }

        /* Mobile Menu Toggle */
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--secondary-color);
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }
        }


        /* Updated Responsive Styles */
@media (max-width: 1024px) {
    .product-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    }
}

@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
    }

    .sidebar.active {
        transform: translateX(0);
    }

    .main-content {
        margin-left: 0;
    }

    .menu-toggle {
        display: block;
    }

    .header {
        padding: 0 20px;
    }
    
    .product-grid {
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 20px;
    }
}

@media (max-width: 576px) {
    .product-grid {
        grid-template-columns: repeat(2, 1fr); /* Changed from 1fr to display 2 products side by side */
        gap: 15px; /* Reduced gap for better fit on small screens */
    }

    .content {
        padding: 20px 15px;
    }

    .header h1 {
        font-size: 1.2rem;
    }
    
    /* Adjust product card for smaller width */
    .product-image {
        height: 150px; /* Reduced image height for better mobile display */
    }
    
    .product-name {
        font-size: 1rem;
        height: 2.2rem;
    }
    
    .product-description {
        font-size: 0.8rem;
        height: 3.2rem;
        margin-bottom: 10px;
    }
    
    .product-price {
        font-size: 1rem;
    }
    
    .product-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .product-actions {
        flex-direction: column;
        gap: 8px;
    }
    
    .btn {
        padding: 6px 12px;
        font-size: 0.8rem;
    }
}

/* Even smaller screens - one column when really small */
@media (max-width: 400px) {
    .product-grid {
        grid-template-columns: repeat(2, 1fr); /* Keep 2 columns even on very small devices */
    }
    
    .product-details {
        padding: 15px 10px;
    }
}
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
            </div>
            <div class="sidebar-menu">
                <ul>
                    <li><a href="dashboard.php" class="active"><i class="fas fa-box"></i> Ürünler</a></li>
                    <li><a href="add_product.php"><i class="fas fa-plus-circle"></i> Ürün Ekle</a></li>
                    <li><a href="edit_category.php"><i class="fas fa-tags"></i> Kategoriler</a></li>
                    <li><a href="../index.php"><i class="fas fa-globe"></i> Siteyi Görüntüle</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a></li>
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <button class="menu-toggle" id="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Ürün Yönetimi</h1>
                <div class="header-right">
                    <a href="add_product.php" class="btn-logout"><i class="fas fa-plus"></i> Yeni Ürün</a>
                </div>
            </header>

            <!-- Content Area -->
            <div class="content">
                <h2 class="page-title">Ürünler</h2>

                <?php if (count($products) > 0): ?>
                    <!-- Product Grid -->
                    <div class="product-grid">
                        <?php foreach ($products as $product): ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <img src="../uploads/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                                    <div class="product-badge">ID: <?php echo $product['id']; ?></div>
                                </div>
                                <div class="product-details">
                                    <h3 class="product-name"><?php echo $product['name']; ?></h3>
                                    <p class="product-description"><?php echo $product['description']; ?></p>
                                    <div class="product-meta">
                                        <span class="product-price"><?php echo $product['price']; ?> ₺</span>
                                        <span class="product-category">
                                            <?php echo isset($product['category_name']) && !empty($product['category_name']) ? $product['category_name'] : 'Kategori Yok'; ?>
                                        </span>
                                    </div>
                                    <div class="product-actions">
                                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-edit">
                                            <i class="fas fa-edit"></i> Düzenle
                                        </a>
                                        <a href="?delete=<?php echo $product['id']; ?>" class="btn btn-delete" onclick="return confirm('Bu ürünü silmek istediğinizden emin misiniz?')">
                                            <i class="fas fa-trash"></i> Sil
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <!-- Empty State -->
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <h3>Hiç ürün bulunamadı</h3>
                        <p>Henüz ürün eklenmemiş. İlk ürününüzü ekleyin.</p>
                        <a href="add_product.php" class="btn btn-edit"><i class="fas fa-plus"></i> Ürün Ekle</a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent default button behavior
            e.stopPropagation(); // Stop event from bubbling up
            
            sidebar.classList.toggle('active');
            
            // Change menu icon
            if (sidebar.classList.contains('active')) {
                menuToggle.innerHTML = '<i class="fas fa-times"></i>';
            } else {
                menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
            }
        });
    }
    
    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        if (window.innerWidth <= 768 &&
            sidebar && 
            sidebar.classList.contains('active') && 
            !sidebar.contains(event.target) && 
            !menuToggle.contains(event.target)) {
            sidebar.classList.remove('active');
            menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
        }
    });
    
    // Ensure mobile styles are applied correctly
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('active');
            menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
        }
    });
});
</script>
</body>
</html>