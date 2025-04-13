<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Ürün ID'si kontrolü ve veritabanından veri çekme
if (isset($_GET['id'])) {
    $productId = $_GET['id'];
    $stmt = $pdo->prepare("SELECT p.*, c.name AS category_name 
                          FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          WHERE p.id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        echo "Ürün bulunamadı!";
        exit;
    }
} else {
    echo "Geçersiz ürün ID!";
    exit;
}

// Kategorileri al
$stmt = $pdo->query("SELECT * FROM categories");
$categories = $stmt->fetchAll();

// Ürün düzenleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];

    // Yeni ürün resmi varsa, resmi yükle
    if ($_FILES['image']['error'] == 0) {
        $image = $_FILES['image'];
        $imageName = time() . "_" . $image['name'];
        $targetDir = "../uploads/";
        $targetFile = $targetDir . $imageName;

        if (move_uploaded_file($image['tmp_name'], $targetFile)) {
            // Eski resmi sil
            if (file_exists("../uploads/" . $product['image'])) {
                unlink("../uploads/" . $product['image']);
            }
        } else {
            $error = "Resim yüklenirken bir hata oluştu.";
            $imageName = $product['image']; // Eğer resim yüklenmediyse eski resmi koru
        }
    } else {
        $imageName = $product['image']; // Eğer yeni resim yüklenmediyse eski resmi koru
    }

    // Ürün bilgilerini güncelle
    $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, image = ?, category_id = ? WHERE id = ?");
    if ($stmt->execute([$name, $description, $price, $imageName, $category_id, $productId])) {
        $success = "Ürün başarıyla güncellendi!";
        
        // Güncel ürün bilgilerini tekrar çek
        $stmt = $pdo->prepare("SELECT p.*, c.name AS category_name 
                              FROM products p 
                              LEFT JOIN categories c ON p.category_id = c.id 
                              WHERE p.id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
    } else {
        $error = "Ürün güncellenirken bir hata oluştu.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürün Düzenle | Admin Panel</title>
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
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            cursor: pointer;
            border: none;
        }

        .btn i {
            margin-right: 6px;
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

        .btn-danger {
            background-color: var(--error);
            color: var(--white);
        }

        .btn-danger:hover {
            background-color: #dc2626;
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

        /* Form Card */
        .form-card {
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }

        .form-header {
            background-color: var(--primary-color);
            padding: 1.5rem;
            color: var(--white);
            position: relative;
        }

        .form-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .form-header .product-id {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
        }

        .form-body {
            padding: 2rem;
        }

        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            flex: 1;
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

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .image-preview {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-top: 15px;
        }

        .current-image {
            position: relative;
            width: 120px;
            height: 120px;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid var(--border-color);
        }

        .current-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .current-image-label {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: rgba(0, 0, 0, 0.6);
            color: white;
            text-align: center;
            padding: 5px;
            font-size: 0.7rem;
        }

        .file-upload {
            position: relative;
            flex: 1;
            padding: 20px;
            border: 2px dashed var(--border-color);
            border-radius: 8px;
            background-color: var(--input-bg);
            transition: all 0.3s;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .file-upload:hover {
            border-color: var(--primary-color);
            background-color: rgba(37, 99, 235, 0.05);
        }

        .file-upload i {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .file-upload input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 2rem;
            background-color: var(--light-bg);
            border-top: 1px solid var(--border-color);
        }

        .form-footer .btn {
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
        }

        /* Alert */
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

        /* Product Summary */
        .product-summary {
            background-color: var(--light-bg);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .product-image-small {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .product-image-small img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-info {
            flex: 1;
        }

        .product-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .product-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 10px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            font-size: 0.875rem;
            color: var(--secondary-color);
        }

        .meta-item i {
            margin-right: 6px;
            color: var(--primary-color);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .form-row {
                flex-direction: column;
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

            .product-summary {
                flex-direction: column;
                align-items: flex-start;
            }

            .form-footer {
                flex-direction: column;
                gap: 15px;
            }

            .form-footer .btn {
                width: 100%;
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
                <h1>Ürün Düzenle</h1>
                <div class="header-actions">
                    <a href="dashboard.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Ürünlere Dön
                    </a>
                </div>
            </header>

            <!-- Content Area -->
            <div class="content">
                <div class="product-summary">
                    <div class="product-image-small">
                        <img src="../uploads/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                    </div>
                    <div class="product-info">
                        <h3 class="product-name"><?php echo $product['name']; ?></h3>
                        <div class="product-meta">
                            <span class="meta-item">
                                <i class="fas fa-tag"></i> 
                                <?php echo isset($product['category_name']) && !empty($product['category_name']) ? $product['category_name'] : 'Kategori Yok'; ?>
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-money-bill-wave"></i> <?php echo $product['price']; ?> ₺
                            </span>
                            <span class="meta-item">
                                <i class="fas fa-hashtag"></i> ID: <?php echo $product['id']; ?>
                            </span>
                        </div>
                    </div>
                </div>

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

                <div class="form-card">
                    <div class="form-header">
                        <h2><i class="fas fa-edit"></i> Ürün Bilgilerini Düzenle</h2>
                        <div class="product-id">ID: <?php echo $product['id']; ?></div>
                    </div>
                    <div class="form-body">
                        <form action="edit_product.php?id=<?php echo $product['id']; ?>" method="POST" enctype="multipart/form-data">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name">Ürün Adı</label>
                                    <input type="text" id="name" name="name" class="form-control" value="<?php echo $product['name']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="price">Fiyat (₺)</label>
                                    <input type="number" id="price" name="price" class="form-control" value="<?php echo $product['price']; ?>" step="0.01" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="category_id">Kategori</label>
                                <select id="category_id" name="category_id" class="form-control" required>
                                    <option value="">Kategori Seçin</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo $product['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                            <?php echo $category['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="description">Ürün Açıklaması</label>
                                <textarea id="description" name="description" class="form-control"><?php echo $product['description']; ?></textarea>
                            </div>

                            <div class="form-group">
                                <label>Ürün Resmi</label>
                                <div class="image-preview">
                                    <div class="current-image">
                                        <img src="../uploads/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" id="currentImage">
                                        <div class="current-image-label">Mevcut Resim</div>
                                    </div>
                                    <div class="file-upload" id="fileUpload">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <p>Yeni resim yüklemek için tıklayın veya sürükleyin</p>
                                        <input type="file" name="image" id="newImage" accept="image/*">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-footer">
                                <a href="dashboard.php" class="btn btn-outline">
                                    <i class="fas fa-times"></i> İptal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Değişiklikleri Kaydet
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Mobile menu toggle functionality
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.querySelector('.sidebar');

        if (menuToggle) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('active');
            });
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768 && 
                sidebar.classList.contains('active') && 
                !sidebar.contains(e.target) && 
                e.target !== menuToggle) {
                sidebar.classList.remove('active');
            }
        });

        // Image preview functionality
        const newImage = document.getElementById('newImage');
        const currentImage = document.getElementById('currentImage');
        const fileUpload = document.getElementById('fileUpload');

        newImage.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    currentImage.src = e.target.result;
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Drag and drop functionality
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            fileUpload.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            fileUpload.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            fileUpload.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            fileUpload.style.borderColor = '#2563eb';
            fileUpload.style.backgroundColor = 'rgba(37, 99, 235, 0.1)';
        }

        function unhighlight() {
            fileUpload.style.borderColor = '';
            fileUpload.style.backgroundColor = '';
        }

        fileUpload.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files && files.length) {
                newImage.files = files;
                
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    currentImage.src = e.target.result;
                }
                
                reader.readAsDataURL(files[0]);
            }
        }
    </script>
</body>
</html>