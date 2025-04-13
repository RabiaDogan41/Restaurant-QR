<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $image = $_FILES['image'];
    $category_id = $_POST['category_id'];  // Seçilen kategori

    if (!empty($name) && !empty($price) && !empty($image) && !empty($category_id)) {
        // Resmi yüklemek için klasör yolu
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($image["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Resim tipi kontrolü
        $allowed_types = ['jpg', 'png', 'jpeg', 'gif'];
        if (in_array($imageFileType, $allowed_types)) {
            // Resmi sunucuya yükle
            if (move_uploaded_file($image["tmp_name"], $target_file)) {
                // Ürünü veritabanına ekle
                $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image, category_id) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $description, $price, basename($image["name"]), $category_id]);

                $success = "Ürün başarıyla eklendi!";
            } else {
                $error = "Resim yüklenirken bir hata oluştu.";
            }
        } else {
            $error = "Sadece JPG, JPEG, PNG ve GIF dosyaları yüklenebilir.";
        }
    } else {
        $error = "Lütfen tüm gerekli alanları doldurun.";
    }
}

// Kategorileri al
$stmt = $pdo->query("SELECT * FROM categories");
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Ürün Ekle | Admin Panel</title>
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

        /* Header */
        .header {
            background-color: var(--white);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header h1 {
            color: var(--dark);
            font-size: 1.5rem;
            font-weight: 600;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
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

        .btn i {
            margin-right: 0.5rem;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 2rem;
            display: flex;
            justify-content: center;
        }

        /* Form Card */
        .form-card {
            background-color: var(--white);
            border-radius: 0.5rem;
            box-shadow: var(--card-shadow);
            width: 100%;
            max-width: 600px;
            overflow: hidden;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-header {
            background-color: var(--primary-color);
            color: var(--white);
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .form-header h2 {
            font-size: 1.5rem;
            position: relative;
            z-index: 2;
        }

        .form-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background-color: rgba(255, 255, 255, 0.1);
            transform: rotate(30deg);
            z-index: 1;
        }

        .form-body {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
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
            font-size: 1rem;
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

        .file-upload {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem;
            border: 2px dashed var(--border-color);
            border-radius: 0.375rem;
            background-color: var(--input-bg);
            transition: all 0.3s;
            cursor: pointer;
        }

        .file-upload:hover {
            border-color: var(--primary-color);
            background-color: rgba(37, 99, 235, 0.05);
        }

        .file-upload i {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .file-upload .upload-text {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .file-upload .upload-hint {
            font-size: 0.875rem;
            color: var(--secondary-color);
            text-align: center;
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

        .file-preview {
            margin-top: 1rem;
            width: 100%;
            display: none;
        }

        .file-preview img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 0.375rem;
            border: 1px solid var(--border-color);
        }

        .submit-btn {
            width: 100%;
            padding: 0.875rem;
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 0.375rem;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .submit-btn i {
            margin-right: 0.5rem;
        }

        .submit-btn:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        /* Alert Messages */
        .alert {
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }

        .alert i {
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }

        .alert-error {
            background-color: var(--error-light);
            color: var(--error);
            border-left: 4px solid var(--error);
        }

        .alert-success {
            background-color: var(--success-light);
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        /* Footer */
        .footer {
            padding: 1.5rem;
            text-align: center;
            background-color: var(--white);
            border-top: 1px solid var(--border-color);
            margin-top: auto;
        }

        .footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header {
                padding: 1rem;
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .main-content {
                padding: 1rem;
            }

            .form-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <h1>Ürün Yönetimi</h1>
        <div class="header-actions">
            <a href="dashboard.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Ürünlere Dön
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="form-card">
            <div class="form-header">
                <h2><i class="fas fa-plus-circle"></i> Yeni Ürün Ekle</h2>
            </div>
            <div class="form-body">
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

                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Ürün Adı</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Ürün Açıklaması</label>
                        <textarea id="description" name="description" class="form-control"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="price">Fiyat (₺)</label>
                        <input type="number" id="price" name="price" step="0.01" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="category_id">Kategori</label>
                        <select id="category_id" name="category_id" class="form-control" required>
                            <option value="">Kategori Seçin</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>">
                                    <?php echo $category['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Ürün Resmi</label>
                        <div class="file-upload" id="dropArea">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <div class="upload-text">Resmi Seç veya Sürükle & Bırak</div>
                            <div class="upload-hint">JPG, PNG, JPEG veya GIF - Maks 5MB</div>
                            <input type="file" name="image" id="fileInput" accept="image/*" required>
                        </div>
                        <div class="file-preview" id="filePreview">
                            <img id="imagePreview" src="#" alt="Resim Önizleme">
                        </div>
                    </div>

                    <button type="submit" class="submit-btn">
                        <i class="fas fa-save"></i> Ürünü Kaydet
                    </button>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Resim önizleme
        const fileInput = document.getElementById('fileInput');
        const imagePreview = document.getElementById('imagePreview');
        const filePreview = document.getElementById('filePreview');
        const dropArea = document.getElementById('dropArea');

        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                
                reader.addEventListener('load', function() {
                    imagePreview.src = reader.result;
                    filePreview.style.display = 'block';
                });
                
                reader.readAsDataURL(file);
            }
        });

        // Drag & Drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            dropArea.style.borderColor = var(--primary-color);
            dropArea.style.backgroundColor = 'rgba(37, 99, 235, 0.1)';
        }

        function unhighlight() {
            dropArea.style.borderColor = '';
            dropArea.style.backgroundColor = '';
        }

        dropArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
            
            const file = files[0];
            if (file) {
                const reader = new FileReader();
                
                reader.addEventListener('load', function() {
                    imagePreview.src = reader.result;
                    filePreview.style.display = 'block';
                });
                
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>