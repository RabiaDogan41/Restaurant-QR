<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Kategori ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_category'])) {
    $new_name = trim($_POST['new_category']);

    if (!empty($new_name)) {
        // Resim yükleme işlemi
        $image_name = '';
        if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] == 0) {
            $allowed = array('jpg', 'jpeg', 'png', 'gif');
            $filename = $_FILES['new_image']['name'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($ext), $allowed)) {
                $new_filename = uniqid() . '.' . $ext;
                $upload_path = '../uploads/categories/' . $new_filename;
                
                // uploads/categories klasörü yoksa oluştur
                if (!file_exists('../uploads/categories/')) {
                    mkdir('../uploads/categories/', 0777, true);
                }
                
                if (move_uploaded_file($_FILES['new_image']['tmp_name'], $upload_path)) {
                    $image_name = $new_filename;
                } else {
                    $error = "Dosya yüklenirken bir hata oluştu!";
                }
            } else {
                $error = "Sadece JPG, JPEG, PNG ve GIF dosyaları yüklenebilir!";
            }
        }
        
        if (!isset($error)) {
            // Yeni kategori ekle
            $stmt = $pdo->prepare("INSERT INTO categories (name, image) VALUES (?, ?)");
            $stmt->execute([$new_name, $image_name]);

            $success = "Kategori başarıyla eklendi!";
        }
    } else {
        $error = "Kategori adı boş olamaz!";
    }
}

// Kategori güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['category_id'])) {
    $category_id = $_POST['category_id'];
    $category_name = trim($_POST['name']);

    if (!empty($category_name)) {
        // Mevcut kategori bilgilerini al
        $stmt = $pdo->prepare("SELECT image FROM categories WHERE id = ?");
        $stmt->execute([$category_id]);
        $current_category = $stmt->fetch();
        
        $image_name = $current_category['image']; // Mevcut resim adını koru
        
        // Yeni resim yüklendi mi kontrol et
        if (isset($_FILES['edit_image']) && $_FILES['edit_image']['error'] == 0) {
            $allowed = array('jpg', 'jpeg', 'png', 'gif');
            $filename = $_FILES['edit_image']['name'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($ext), $allowed)) {
                $new_filename = uniqid() . '.' . $ext;
                $upload_path = '../uploads/categories/' . $new_filename;
                
                // uploads/categories klasörü yoksa oluştur
                if (!file_exists('../uploads/categories/')) {
                    mkdir('../uploads/categories/', 0777, true);
                }
                
                if (move_uploaded_file($_FILES['edit_image']['tmp_name'], $upload_path)) {
                    // Eski resmi sil
                    if (!empty($current_category['image']) && file_exists('../uploads/categories/' . $current_category['image'])) {
                        unlink('../uploads/categories/' . $current_category['image']);
                    }
                    
                    $image_name = $new_filename;
                } else {
                    $error = "Dosya yüklenirken bir hata oluştu!";
                }
            } else {
                $error = "Sadece JPG, JPEG, PNG ve GIF dosyaları yüklenebilir!";
            }
        }
        
        // "Resmi kaldır" seçeneği işaretlendiyse
        if (isset($_POST['remove_image']) && $_POST['remove_image'] == 1) {
            // Eski resmi sil
            if (!empty($current_category['image']) && file_exists('../uploads/categories/' . $current_category['image'])) {
                unlink('../uploads/categories/' . $current_category['image']);
            }
            $image_name = '';
        }
        
        if (!isset($error)) {
            // Kategoriyi güncelle
            $stmt = $pdo->prepare("UPDATE categories SET name = ?, image = ? WHERE id = ?");
            $stmt->execute([$category_name, $image_name, $category_id]);

            $success = "Kategori başarıyla güncellendi!";
        }
    } else {
        $error = "Kategori adı boş olamaz!";
    }
}

// Kategori silme işlemi
if (isset($_GET['delete'])) {
    $category_id = $_GET['delete'];

    // Kategoriyi silmeden önce, bu kategoriye bağlı ürün var mı kontrol et
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $product_count = $stmt->fetchColumn();

    if ($product_count > 0) {
        $error = "Bu kategoriye bağlı ürünler olduğu için silemezsiniz.";
    } else {
        // Kategori resmini al ve sil
        $stmt = $pdo->prepare("SELECT image FROM categories WHERE id = ?");
        $stmt->execute([$category_id]);
        $category = $stmt->fetch();
        
        // Kategoriyi sil
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$category_id]);
        
        // Kategori resmi varsa sil
        if (!empty($category['image']) && file_exists('../uploads/categories/' . $category['image'])) {
            unlink('../uploads/categories/' . $category['image']);
        }

        $success = "Kategori başarıyla silindi!";
    }
}

// Kategori sayısını al
$stmt = $pdo->query("SELECT COUNT(*) FROM categories");
$category_count = $stmt->fetchColumn();

// Kategorileri al
$stmt = $pdo->query("SELECT c.id, c.name, c.image, COUNT(p.id) as product_count 
                    FROM categories c 
                    LEFT JOIN products p ON c.id = p.category_id 
                    GROUP BY c.id 
                    ORDER BY c.name ASC");
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori Yönetimi | Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
            --secondary-color: #64748b;
            --light-bg: #f8fafc;
            --white: #ffffff;
            --dark: #1e293b;
            --border-color: #e2e8f0;
            --input-bg: #f1f5f9;
            --success: #10b981;
            --success-light: #d1fae5;
            --error: #ef4444;
            --error-light: #fee2e2;
            --warning: #f59e0b;
            --warning-light: #fef3c7;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
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
            display: flex;
            flex-direction: column;
        }

        /* Dashboard Layout */
        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
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
            height: 70px;
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
            margin-left: 250px;
            overflow-x: hidden;
        }

        /* Header */
        .header {
            height: 70px;
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

        .header-actions {
            display: flex;
            gap: 15px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.6rem 1.2rem;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            cursor: pointer;
            border: none;
            font-size: 0.95rem;
        }

        .btn i {
            margin-right: 6px;
            font-size: 1rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .btn-outline {
            background-color: transparent;
            color: var(--secondary-color);
            border: 1px solid var(--border-color);
        }

        .btn-outline:hover {
            background-color: var(--light-bg);
        }

        .btn-success {
            background-color: var(--success);
            color: var(--white);
        }

        .btn-success:hover {
            background-color: #0ca678;
        }

        .btn-danger {
            background-color: var(--error);
            color: var(--white);
        }

        .btn-danger:hover {
            background-color: #dc2626;
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
        }

        /* Content Area */
        .content {
            padding: 30px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 1.75rem;
            color: var(--dark);
            font-weight: 600;
        }

        /* Cards */
        .card {
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark);
        }

        .card-tools {
            display: flex;
            gap: 10px;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Stats Cards */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: var(--white);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .stat-icon.primary {
            background-color: rgba(37, 99, 235, 0.1);
            color: var(--primary-color);
        }

        .stat-icon i {
            font-size: 1.5rem;
        }

        .stat-data {
            flex: 1;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--secondary-color);
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group:last-child {
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            background-color: var(--input-bg);
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            background-color: var(--white);
        }

        /* Image Preview */
        .image-preview-wrapper {
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .image-preview {
            max-width: 150px;
            max-height: 150px;
            border-radius: 5px;
            border: 1px solid var(--border-color);
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .image-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }

        .image-preview.empty {
            padding: 30px;
            background-color: var(--input-bg);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .image-preview.empty i {
            font-size: 2rem;
            color: var(--gray-300);
            margin-bottom: 10px;
        }

        .remove-image-option {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }

        .custom-file-input {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .custom-file-input input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .custom-file-label {
            display: block;
            padding: 0.75rem 1rem;
            background-color: var(--input-bg);
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            color: var(--secondary-color);
            cursor: pointer;
            transition: all 0.3s;
        }

        .custom-file-label:hover {
            background-color: var(--gray-200);
        }

        .custom-file-label i {
            margin-right: 8px;
        }

        .file-selected {
            color: var(--dark);
            background-color: var(--light-bg);
        }

        /* Alert Messages */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }

        .alert i {
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }

        .alert-success {
            background-color: var(--success-light);
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        .alert-error {
            background-color: var(--error-light);
            color: var(--error);
            border-left: 4px solid var(--error);
        }

        .alert-warning {
            background-color: var(--warning-light);
            color: var(--warning);
            border-left: 4px solid var(--warning);
        }

        /* Category List */
        .category-list {
            border-collapse: collapse;
            width: 100%;
        }

        .category-list th {
            text-align: left;
            padding: 1rem;
            background-color: var(--gray-100);
            border-bottom: 2px solid var(--border-color);
            color: var(--dark);
            font-weight: 600;
        }

        .category-list td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        .category-list tr:hover {
            background-color: var(--gray-100);
        }

        .category-list .actions {
            display: flex;
            gap: 8px;
        }

        .category-badge {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            background-color: rgba(37, 99, 235, 0.1);
            color: var(--primary-color);
        }

        .category-image {
            width: 60px;
            height: 60px;
            border-radius: 5px;
            object-fit: cover;
            border: 1px solid var(--border-color);
        }

        .category-image-placeholder {
            width: 60px;
            height: 60px;
            border-radius: 5px;
            background-color: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-300);
            font-size: 1.5rem;
        }

        /* Edit Category Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background-color: var(--white);
            margin: 10% auto;
            padding: 2rem;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: var(--card-shadow);
            animation: slideDown 0.3s;
        }

        @keyframes slideDown {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark);
        }

        .modal-close {
            font-size: 1.5rem;
            color: var(--secondary-color);
            cursor: pointer;
            background: none;
            border: none;
            transition: color 0.3s;
        }

        .modal-close:hover {
            color: var(--error);
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 1.5rem;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--gray-300);
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: var(--secondary-color);
            margin-bottom: 1.5rem;
        }

        /* Checkbox Style */
        .checkbox-container {
            display: flex;
            align-items: center;
            margin-top: 0.5rem;
        }

        .checkbox-input {
            margin-right: 8px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .stats-cards {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
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

            .content {
                padding: 20px;
            }

            .stats-cards {
                grid-template-columns: 1fr;
            }

            .category-list th:nth-child(3), 
            .category-list td:nth-child(3) {
                display: none;
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
                    <li><a href="dashboard.php"><i class="fas fa-box"></i> Ürünler</a></li>
                    <li><a href="add_product.php"><i class="fas fa-plus-circle"></i> Ürün Ekle</a></li>
                    <li><a href="edit_category.php" class="active"><i class="fas fa-tags"></i> Kategoriler</a></li>
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
                <h1>Kategori Yönetimi</h1>
                <div class="header-actions">
                    <button class="btn btn-primary" id="showAddModal">
                        <i class="fas fa-plus"></i> Yeni Kategori
                    </button>
                </div>
            </header>

            <!-- Content Area -->
            <div class="content">
                <?php if (isset($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo $success; ?></span>
                    </div>
                <?php endif; ?>

                <!-- Stats Cards -->
                <div class="stats-cards">
                    <div class="stat-card">
                        <div class="stat-icon primary">
                            <i class="fas fa-tag"></i>
                        </div>
                        <div class="stat-data">
                            <div class="stat-value"><?php echo $category_count; ?></div>
                            <div class="stat-label">Toplam Kategori</div>
                        </div>
                    </div>
                </div>

                <!-- Category List -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Kategoriler</h2>
                    </div>
                    <div class="card-body">
                        <?php if (count($categories) > 0): ?>
                            <table class="category-list">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Görsel</th>
                                        <th>Kategori Adı</th>
                                        <th>Ürün Sayısı</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td><?php echo $category['id']; ?></td>
                                            <td>
                                                <?php if (!empty($category['image']) && file_exists('../uploads/categories/' . $category['image'])): ?>
                                                    <img src="../uploads/categories/<?php echo $category['image']; ?>" alt="<?php echo $category['name']; ?>" class="category-image">
                                                <?php else: ?>
                                                    <div class="category-image-placeholder">
                                                        <i class="fas fa-image"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $category['name']; ?></td>
                                            <td>
                                                <span class="category-badge">
                                                    <?php echo $category['product_count']; ?> ürün
                                                </span></td>
                                            <td class="actions">
                                                <button class="btn btn-primary btn-sm edit-category" 
                                                        data-id="<?php echo $category['id']; ?>" 
                                                        data-name="<?php echo $category['name']; ?>"
                                                        data-image="<?php echo $category['image']; ?>">
                                                    <i class="fas fa-edit"></i> Düzenle
                                                </button>
                                                <a href="?delete=<?php echo $category['id']; ?>" 
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Bu kategoriyi silmek istediğinize emin misiniz? <?php echo $category['product_count']; ?> ürün bu kategoriye bağlı.')">
                                                    <i class="fas fa-trash"></i> Sil
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-tags"></i>
                                <h3>Henüz kategori bulunmuyor</h3>
                                <p>Ürünlerinizi organize etmek için kategori ekleyin.</p>
                                <button class="btn btn-primary" id="emptyAddBtn">
                                    <i class="fas fa-plus"></i> Kategori Ekle
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Category Modal -->
    <div id="addCategoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fas fa-plus-circle"></i> Yeni Kategori Ekle</h3>
                <button class="modal-close">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="new_category">Kategori Adı</label>
                    <input type="text" class="form-control" id="new_category" name="new_category" required>
                </div>
                <div class="form-group">
                    <label for="new_image">Kategori Görseli</label>
                    <div class="custom-file-input">
                        <input type="file" id="new_image" name="new_image" accept="image/*" onchange="updateFileLabel(this, 'new_file_label', 'new_image_preview')">
                        <label for="new_image" id="new_file_label" class="custom-file-label">
                            <i class="fas fa-upload"></i> Resim Seçin
                        </label>
                    </div>
                    <div class="image-preview-wrapper">
                        <div id="new_image_preview" class="image-preview empty">
                            <i class="fas fa-image"></i>
                            <span>Önizleme</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline modal-close-btn">İptal</button>
                    <button type="submit" class="btn btn-primary">Kategori Ekle</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div id="editCategoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fas fa-edit"></i> Kategori Düzenle</h3>
                <button class="modal-close">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="edit_name">Kategori Adı</label>
                    <input type="text" class="form-control" id="edit_name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="edit_image">Kategori Görseli</label>
                    <div class="custom-file-input">
                        <input type="file" id="edit_image" name="edit_image" accept="image/*" onchange="updateFileLabel(this, 'edit_file_label', 'edit_image_preview')">
                        <label for="edit_image" id="edit_file_label" class="custom-file-label">
                            <i class="fas fa-upload"></i> Yeni Resim Seçin
                        </label>
                    </div>
                    <div class="image-preview-wrapper">
                        <div id="edit_image_preview" class="image-preview empty">
                            <i class="fas fa-image"></i>
                            <span>Önizleme</span>
                        </div>
                        <div class="checkbox-container">
                            <input type="checkbox" id="remove_image" name="remove_image" value="1" class="checkbox-input">
                            <label for="remove_image">Mevcut resmi kaldır</label>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="edit_id" name="category_id">
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline modal-close-btn">İptal</button>
                    <button type="submit" class="btn btn-primary">Güncelle</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.querySelector('.sidebar');

        if (menuToggle) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('active');
            });
        }

        // Modal functionality
        const addModal = document.getElementById('addCategoryModal');
        const editModal = document.getElementById('editCategoryModal');
        const showAddModalBtn = document.getElementById('showAddModal');
        const emptyAddBtn = document.getElementById('emptyAddBtn');
        const closeBtns = document.querySelectorAll('.modal-close, .modal-close-btn');
        const editBtns = document.querySelectorAll('.edit-category');
        const removeImageCheckbox = document.getElementById('remove_image');

        // File input preview functionality
        function updateFileLabel(input, labelId, previewId) {
            const label = document.getElementById(labelId);
            const preview = document.getElementById(previewId);
            
            if (input.files && input.files[0]) {
                const fileName = input.files[0].name;
                label.textContent = fileName;
                label.classList.add('file-selected');
                
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Image Preview">`;
                    preview.classList.remove('empty');
                }
                
                reader.readAsDataURL(input.files[0]);
            } else {
                label.innerHTML = '<i class="fas fa-upload"></i> Resim Seçin';
                label.classList.remove('file-selected');
                preview.innerHTML = '<i class="fas fa-image"></i><span>Önizleme</span>';
                preview.classList.add('empty');
            }
        }

        // Open add modal
        if (showAddModalBtn) {
            showAddModalBtn.addEventListener('click', () => {
                addModal.style.display = 'block';
            });
        }

        if (emptyAddBtn) {
            emptyAddBtn.addEventListener('click', () => {
                addModal.style.display = 'block';
            });
        }

        // Close modals
        closeBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                addModal.style.display = 'none';
                editModal.style.display = 'none';
            });
        });

        // Edit category modal
        editBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const categoryId = btn.getAttribute('data-id');
                const categoryName = btn.getAttribute('data-name');
                const categoryImage = btn.getAttribute('data-image');
                
                document.getElementById('edit_id').value = categoryId;
                document.getElementById('edit_name').value = categoryName;
                
                // Reset the remove image checkbox
                if (removeImageCheckbox) {
                    removeImageCheckbox.checked = false;
                }
                
                // Set up the image preview
                const imagePreview = document.getElementById('edit_image_preview');
                if (categoryImage && categoryImage !== '') {
                    imagePreview.innerHTML = `<img src="../uploads/categories/${categoryImage}" alt="${categoryName}">`;
                    imagePreview.classList.remove('empty');
                } else {
                    imagePreview.innerHTML = '<i class="fas fa-image"></i><span>Önizleme</span>';
                    imagePreview.classList.add('empty');
                }
                
                editModal.style.display = 'block';
            });
        });

        // Remove image checkbox functionality
        if (removeImageCheckbox) {
            removeImageCheckbox.addEventListener('change', function() {
                const imagePreview = document.getElementById('edit_image_preview');
                const fileInput = document.getElementById('edit_image');
                
                if (this.checked) {
                    // Reset the file input and preview
                    fileInput.value = '';
                    imagePreview.innerHTML = '<i class="fas fa-image"></i><span>Önizleme</span>';
                    imagePreview.classList.add('empty');
                    document.getElementById('edit_file_label').innerHTML = '<i class="fas fa-upload"></i> Resim Seçin';
                    document.getElementById('edit_file_label').classList.remove('file-selected');
                }
            });
        }

        // Close modal when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target == addModal) {
                addModal.style.display = 'none';
            }
            if (e.target == editModal) {
                editModal.style.display = 'none';
            }
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768 && 
                sidebar.classList.contains('active') && 
                !sidebar.contains(e.target) && 
                e.target !== menuToggle) {
                sidebar.classList.remove('active');
            }
        });
    </script>
</body>
</html>