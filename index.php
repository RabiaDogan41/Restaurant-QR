<?php
// Veritabanı bağlantısını ve header dosyasını dahil edin
include 'includes/db.php';

// Kategori ID'sine göre ürünleri çekmek için kategori filtresi sorgusu
$categoryId = isset($_GET['category']) ? $_GET['category'] : null;

$query = "SELECT p.id, p.name, p.description, p.price, p.image, c.name AS category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id";

if ($categoryId) {
    $query .= " WHERE p.category_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$categoryId]);
} else {
    $stmt = $pdo->query($query);
}

$products = $stmt->fetchAll();

// Kategorileri çekmek için sorgu
$categoriesStmt = $pdo->query("SELECT * FROM categories");
$categories = $categoriesStmt->fetchAll();

// En son eklenen 3 ürünü çek
$latestProductsStmt = $pdo->query("SELECT p.id, p.name, p.description, p.price, p.image 
                                FROM products p 
                                ORDER BY p.id DESC LIMIT 3");
$latestProducts = $latestProductsStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ego Döner - Lezzetin adresi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Genel Sayfa Stilleri */
        :root {
            --primary-color:rgb(167, 156, 51);
            --primary-dark:rgb(231, 214, 60);
            --secondary-color:rgb(0, 0, 0);
            --accent-color: #f39c12;
            --dark-color:rgb(0, 0, 0);
            --light-color: #f9f9f9;
            --text-color: #333;
            --gray-color: #7f8c8d;
            --light-gray: #ecf0f1;
            --box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            --card-shadow: 0 5px 15px rgba(0,0,0,0.05);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            --border-radius: 8px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', Arial, sans-serif;
            line-height: 1.6;
            background-color: var(--light-color);
            color: var(--text-color);
            margin: 0;
            padding: 0;
        }
        
        .container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header ve Navbar */
        .header {
            background-color: #fff;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }
        
        .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        
        .logo img {
            height: 50px;
            margin-right: 12px;
            transition: transform 0.3s ease;
        }
        
        .logo:hover img {
            transform: scale(1.05);
        }
        
        .logo h1 {
            color: var(--primary-color);
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
        }
        
        .nav-links li {
            margin-left: 35px;
            position: relative;
        }
        
        .nav-links a {
            color: var(--dark-color);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            font-size: 1.05rem;
            position: relative;
            padding: 5px 0;
        }
        
        .nav-links a:after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            bottom: 0;
            left: 0;
            transition: width 0.3s ease;
        }
        
        .nav-links a:hover {
            color: var(--primary-color);
        }
        
        .nav-links a:hover:after {
            width: 100%;
        }
        
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--dark-color);
            transition: var(--transition);
        }
        
        .mobile-menu-btn:hover {
            color: var(--primary-color);
        }
        
        /* Hero Bölümü */
        .hero {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('uploads/dark.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            padding: 160px 0;
            text-align: center;
            color:white;
            position: relative;
        }
        
        .hero:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(153, 156, 66, 0.6) 0%, rgba(56, 66, 76, 0.6) 100%);
        }
        
        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }
        
        .hero h1 {
            font-size: 3.8em;
            margin: 0;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.4);
            font-family: 'Montserrat', sans-serif;
            letter-spacing: -1px;
        }
        
        .hero p {
            font-size: 1.4em;
            margin: 25px 0 40px;
            font-weight: 300;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .btn {
            display: inline-block;
            padding: 14px 34px;
            background-color: var(--primary-color);
            color: white;
            font-size: 1.1em;
            text-decoration: none;
            border-radius: 50px;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            font-weight: 500;
            box-shadow: 0 4px 15px rgba(5, 5, 5, 0.3);
            letter-spacing: 0.5px;
        }
        
        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 7px 20px rgba(231, 222, 60, 0.4);
        }
        
        .btn-outline {
            background-color: transparent;
            border: 2px solid white;
            margin-left: 15px;
            box-shadow: none;
        }
        
        .btn-outline:hover {
            background-color: white;
            color: var(--primary-color);
            box-shadow: 0 7px 20px rgba(255, 255, 255, 0.3);
        }
        
        /* Kategoriler Bölümü */
        .section-title {
            text-align: center;
            margin-bottom: 50px;
            position: relative;
            font-size: 2.4em;
            color:black;
            font-weight: 700;
            font-family: 'Montserrat', sans-serif;
        }
        
        .section-title::after {
            content: "";
            position: absolute;
            width: 60px;
            height: 4px;
            background-color: var(--primary-color);
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
        }
        
        /* Kategoriler Bölümü */
.categories {
    padding: 80px 0;
    background-color: #fff;
    position: relative;
}

.categories:before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 200px;
    background: linear-gradient(to bottom, var(--light-color) 0%, #fff 100%);
    z-index: 0;
}

.categories-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 25px;
    margin-top: 40px;
    position: relative;
    z-index: 1;
}

.category-card {
    position: relative;
    background-color: #fff;
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    overflow: hidden;
    text-decoration: none;
    transition: var(--transition);
    display: flex;
    flex-direction: column;
    aspect-ratio: 1 / 1; /* Kare şekline zorlar */
}

.category-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
}

.category-image {
    position: relative;
    height: 100%;
    overflow: hidden;
}

.category-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.category-card:hover .category-image img {
    transform: scale(1.08);
}

.category-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, rgba(0, 0, 0, 0.1) 0%, rgba(0, 0, 0, 0.6) 100%);
    transition: var(--transition);
    z-index: 1;
}

.category-card:hover .category-overlay {
    background: linear-gradient(to bottom, rgba(231, 214, 60, 0.3) 0%, rgba(231, 214, 60, 0.7) 100%);
}

.category-name {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 15px;
    color: white;
    font-size: 1.2em;
    font-weight: 600;
    text-align: center;
    z-index: 2;
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
    background: linear-gradient(to top, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0) 100%);
    transition: var(--transition);
}

.category-card:hover .category-name {
    background: linear-gradient(to top, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0) 100%);
    padding-bottom: 20px;
}

.active-category {
    border: 3px solid var(--primary-color);
}

.active-category .category-overlay {
    background: linear-gradient(to bottom, rgba(231, 214, 60, 0.3) 0%, rgba(231, 214, 60, 0.7) 100%);
}

/* Responsive Tasarım */
@media (max-width: 991px) {
    .categories-container {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
    }
}

@media (max-width: 768px) {
    .categories-container {
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
    }
    
    .category-name {
        font-size: 1em;
        padding: 10px;
    }
}

@media (max-width: 576px) {
    .categories-container {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
}
        
        /* Ürünler Bölümü */
        .featured {
            padding: 80px 0;
            background-color:white;
            position: relative;
        }
        
        .featured:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 200px;
            background: linear-gradient(to bottom, #fff 0%, var(--light-color) 100%);
            z-index: 0;
        }
        
        .product-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
            position: relative;
            z-index: 1;
        }
        
        .product-card {
            background-color: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: var(--transition);
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        
        .product-image {
            position: relative;
            height: 220px;
            overflow: hidden;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .product-card:hover .product-image img {
            transform: scale(1.08);
        }
        
        .category-tag {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: var(--primary-color);
            color: white;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 500;
            box-shadow: 0 2px 10px rgba(231, 76, 60, 0.3);
            z-index: 1;
        }
        
        .product-info {
            padding: 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .product-info h3 {
            font-size: 1.4em;
            margin-bottom: 12px;
            color: var(--dark-color);
            font-weight: 600;
            line-height: 1.3;
        }
        
        .product-info p {
            color: var(--gray-color);
            margin-bottom: 20px;
            font-size: 0.95em;
            line-height: 1.6;
            flex-grow: 1;
        }
        
        .price {
            font-size: 1.5em;
            font-weight: 700;
            color: var(--primary-color);
            display: flex;
            align-items: center;
        }
        
        .price:before {
            content: '₺';
            margin-right: 2px;
            font-weight: 400;
            font-size: 0.8em;
        }
        
        /* Yeni Ürünler Bölümü */
        .new-products {
            padding: 80px 0;
            background-color: #fff;
            position: relative;
        }
        
        .new-products:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 200px;
            background: linear-gradient(to bottom, var(--light-color) 0%, #fff 100%);
            z-index: 0;
        }
        
        .new-products-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            position: relative;
            z-index: 1;
        }
        
        /* Hakkımızda Bölümü */
        .about-content {
            text-align: center;
            max-width: 900px;
            margin: 0 auto;
            background-color: #fff;
            padding: 40px;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
        }
        
        .about-content p {
            font-size: 1.15em;
            line-height: 1.8;
            color: var(--gray-color);
        }
        
        /* Adres Bölümü Stilleri */
        .address {
            padding: 80px 0;
            background-color: var(--light-color);
        }
        
        .address-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: center;
        }
        
        .contact-info {
            padding: 40px;
            background-color: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
        }
        
        .contact-info h2 {
            font-size: 2em;
            margin-bottom: 30px;
            color: var(--dark-color);
            font-weight: 600;
        }
        
        .contact-detail {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .contact-icon {
            background-color: var(--primary-color);
            color: white;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            font-size: 1.3em;
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.2);
            transition: var(--transition);
        }
        
        .contact-detail:hover .contact-icon {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 8px 20px rgba(231, 76, 60, 0.3);
        }
        
        .contact-text h3 {
            font-size: 1.2em;
            margin-bottom: 5px;
            color: var(--dark-color);
            font-weight: 600;
        }
        
        .contact-text p, .contact-text a {
            color: var(--gray-color);
            text-decoration: none;
            transition: var(--transition);
            font-size: 1.05em;
        }
        
        .contact-text a:hover {
            color: var(--primary-color);
        }
        
        .map-container {
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--card-shadow);
            height: 100%;
            min-height: 450px;
            position: relative;
        }
        
        .map-container:after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            box-shadow: inset 0 0 20px rgba(0,0,0,0.1);
            pointer-events: none;
            border-radius: var(--border-radius);
        }
        
        .map-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        
        /* Footer */
        footer {
            background-color: var(--secondary-color);
            color: white;
            padding: 70px 0 20px;
            position: relative;
        }
        
        footer:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: var(--primary-color);
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 50px;
        }
        
        .footer-column h3 {
            font-size: 1.4em;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 15px;
            font-weight: 600;
        }
        
        .footer-column h3::after {
            content: "";
            position: absolute;
            width: 50px;
            height: 3px;
            background-color: var(--primary-color);
            bottom: 0;
            left: 0;
            border-radius: 2px;
        }
        
        .footer-column p {
            margin-bottom: 20px;
            font-size: 0.95em;
            color: #bdc3c7;
            line-height: 1.7;
        }
        
        .footer-column ul {
            list-style: none;
        }
        
        .footer-column li {
            margin-bottom: 15px;
        }
        
        .footer-column a {
            color: #bdc3c7;
            text-decoration: none;
            transition: var(--transition);
            display: flex;
            align-items: center;
            font-size: 0.95em;
        }
        
        .footer-column a i {
            margin-right: 12px;
            color: var(--primary-color);
            transition: var(--transition);
        }
        
        .footer-column a:hover {
            color: white;
            transform: translateX(5px);
        }
        
        .footer-column a:hover i {
            transform: translateX(3px);
        }
        
        .social-icons {
            display: flex;
            margin-top: 20px;
        }
        
        .social-icons a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-radius: 50%;
            margin-right: 12px;
            transition: var(--transition);
            font-size: 1.1em;
        }
        
        .social-icons a:hover {
            background-color: var(--primary-color);
            transform: translateY(-5px) scale(1.1);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
        }
        
        .copyright {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.9em;
            color: #bdc3c7;
        }
        
        /* Admin Panel Link */
        .admin-link {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: var(--secondary-color);
            color: white;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transition: var(--transition);
            z-index: 99;
            font-size: 1.2em;
        }
        
        .admin-link:hover {
            background-color: var(--primary-color);
            transform: rotate(360deg) scale(1.1);
            box-shadow: 0 8px 25px rgba(231, 76, 60, 0.3);
        }
        
        /* No Products Message */
        .no-products {
            grid-column: 1 / -1;
            text-align: center;
            padding: 40px;
            background-color: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
        }
        
        .no-products p {
            font-size: 1.1em;
            color: var(--gray-color);
        }
        
        /* Responsive Styles */
        @media (max-width: 991px) {
            .hero h1 {
                font-size: 3.2em;
            }
            
            .hero p {
                font-size: 1.2em;
            }
            
            .address-container {
                grid-template-columns: 1fr;
            }
            
            .nav-links {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 80px;
                left: 0;
                right: 0;
                background-color: white;
                padding: 20px;
                box-shadow: 0 10px 15px rgba(0,0,0,0.1);
                border-top: 3px solid var(--primary-color);
            }
            
            .nav-links.active {
                display: flex;
            }
            
            .nav-links li {
                margin: 15px 0;
            }
            
            .nav-links a:after {
                display: none;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .product-list, .new-products-container {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 25px;
            }
        }
        
        @media (max-width: 768px) {
            .hero {
                padding: 120px 0;
            }
            
            .hero h1 {
                font-size: 2.5em;
            }
            
            .hero p {
                font-size: 1.1em;
                margin: 20px 0 30px;
            }
            
            .btn {
                padding: 12px 25px;
                font-size: 1em;
            }
            
            .section-title {
                font-size: 2em;
            }
            
            .product-list, .new-products-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
            
            .product-image {
                height: 180px;
            }
            
            .product-info {
                padding: 20px;
            }
            
            .product-info h3 {
                font-size: 1.2em;
            }
            
            .footer-content {
                grid-template-columns: 1fr;
                gap: 40px;
            }
            
            .contact-info {
                padding: 30px;
            }
        }

        @media (max-width: 480px) {
            .hero h1 {
                font-size: 2.2em;
            }
            
            .product-list, .new-products-container {
                grid-template-columns: 1fr;
            }
            
            .product-image {
                height: 200px;
            }
            
            .product-info h3 {
                font-size: 1.15em;
                height: auto;
            }
            
            .product-info p {
                height: auto;
            }
            
            .btn-outline {
                margin: 15px 0 0 0;
                display: inline-block;
            }
            
            .contact-detail {
                flex-direction: column;
                text-align: center;
            }
            
            .contact-icon {
                margin: 0 0 15px 0;
            }
            
            .categories-container {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .category-btn {
                margin: 5px;
                padding: 8px 15px;
                font-size: 0.9em;
            }
        }
    </style>
</head>
<body>
    <!-- Header ve Navbar -->
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <a href="index.php" class="logo">
                    <img src="uploads/3.png  " alt="Ego Döner Logo">
                    <h1>Damak Dostları</h1>
                </a>
                <button class="mobile-menu-btn" id="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </button>
                <ul class="nav-links" id="nav-links">
                    <li><a href="index.php">Ana Sayfa</a></li>
                    <li><a href="#menu">Menü</a></li>
                    <li><a href="#about">Hakkımızda</a></li>
                    <li><a href="#contact">İletişim</a></li>
                    <?php if(isset($_SESSION['admin']) && $_SESSION['admin']): ?>
                        <li><a href="admin/index.php">Admin Panel</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Bölümü -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Damağınıza layık lezzetler</h1>
                <p>Lezzetin kaliteyle birleştiği yer damak zevkinize hitap eden eşsiz tatlarla buluşuyor.</p>
                <div class="hero-buttons">
                    <a href="#menu" class="btn">Menüyü İncele</a>
                    <a href="#contact" class="btn btn-outline">İletişime Geç</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Kategoriler Bölümü -->
<section class="categories" id="menu">
    <div class="container">
        <h2 class="section-title">Kategoriler</h2>
        <div class="categories-container">
            <a href="index.php" class="category-card <?php echo !$categoryId ? 'active-category' : ''; ?>">
                <div class="category-image">
                    <div class="category-overlay"></div>
                    <img src="uploads/category.jpg" alt="Tüm Kategoriler">
                </div>
                <div class="category-name">Tümünü Gör</div>
            </a>
            <?php foreach ($categories as $category): ?>
                <a href="?category=<?php echo $category['id']; ?>" class="category-card <?php echo $categoryId == $category['id'] ? 'active-category' : ''; ?>">
                    <div class="category-image">
                        <div class="category-overlay"></div>
                        <?php if (!empty($category['image']) && file_exists('uploads/categories/' . $category['image'])): ?>
                            <img src="uploads/categories/<?php echo $category['image']; ?>" alt="<?php echo $category['name']; ?>">
                        <?php else: ?>
                            <img src="uploads/category-placeholder.jpg" alt="<?php echo $category['name']; ?>">
                        <?php endif; ?>
                    </div>
                    <div class="category-name"><?php echo $category['name']; ?></div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

    <!-- Ürünler Bölümü -->
    <section class="featured">
        <div class="container">
            <h2 class="section-title">
                <?php echo $categoryId ? $categories[array_search($categoryId, array_column($categories, 'id'))]['name'] : 'Tüm Ürünlerimiz'; ?>
            </h2>
            <div class="product-list">
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="uploads/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                                <span class="category-tag"><?php echo $product['category_name']; ?></span>
                            </div>
                            <div class="product-info">
                                <h3><?php echo $product['name']; ?></h3>
                                <p><?php echo $product['description']; ?></p>
                                <div class="price"><?php echo $product['price']; ?> ₺</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-products">
                        <p>Bu kategoride henüz ürün bulunmamaktadır.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Yeni Ürünler Bölümü -->
    <section class="new-products">
        <div class="container">
            <h2 class="section-title">Yeni Eklenen Ürünler</h2>
            <div class="new-products-container">
                <?php foreach ($latestProducts as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="uploads/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                            <span class="category-tag">Yeni</span>
                        </div>
                        <div class="product-info">
                            <h3><?php echo $product['name']; ?></h3>
                            <p><?php echo $product['description']; ?></p>
                            <div class="price"><?php echo $product['price']; ?> ₺</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Hakkımızda Bölümü -->
    <section class="address" id="about">
        <div class="container">
            <h2 class="section-title">Hakkımızda</h2>
            <div class="about-content">
                <p>
                    Damak Dostları olarak sizlere en kaliteli et ve malzemelerle hazırladığımız lezzetli menülerimizi sunuyoruz. 
                    Amacımız, müşterilerimize her ziyaretlerinde unutulmaz bir deneyim yaşatmak ve damak tatlarında iz bırakmaktır.
                    Hijyenik ortamımız, güler yüzlü personelimiz ve eşsiz lezzetlerimizle hizmetinizdeyiz.
                </p>
            </div>
        </div>
    </section>

    <!-- İletişim Bölümü -->
    <section class="address" id="contact">
        <div class="container">
            <h2 class="section-title">İletişim</h2>
            <div class="address-container">
                <div class="contact-info">
                    <h2>Bize Ulaşın</h2>
                    <div class="contact-detail">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-text">
                            <h3>Adres</h3>
                            <p>Pazar, 55420 19 Mayıs/Samsun</p>
                        </div>
                    </div>
                    <div class="contact-detail">
                        <div class="contact-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div class="contact-text">
                            <h3>Telefon</h3>
                            <a href="tel:03625112449">0362 511 2449</a>
                        </div>
                    </div>
                    <div class="contact-detail">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-text">
                            <h3>E-posta</h3>
                            <a href="mailto:info@damakdostlari.com">info@damakdostlari.com</a>
                        </div>
                    </div>
                    <div class="contact-detail">
                        <div class="contact-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="contact-text">
                            <h3>Çalışma Saatleri</h3>
                            <p>Her gün: 10:00 - 22:00</p>
                        </div>
                    </div>
                </div>
                <div class="map-container">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2988.465192278014!2d36.07382087592388!3d41.49419287128644!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x408885dde0c73719%3A0x4c13ce63566b3d80!2sEgo%20D%C3%B6ner!5e0!3m2!1str!2str!4v1735751111096!5m2!1str!2str" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>Damak Dostları</h3>
                    <p>Kaliteli malzemeler ve özenle hazırlanan menülerimizle hizmetinizdeyiz. Lezzetimizi denediğiniz için teşekkür ederiz.</p>
                    <div class="social-icons">
                        <a href="https://www.facebook.com/people/damakdostlari/100091915872161/"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://www.instagram.com/damakdostlari/"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="footer-column">
                    <h3>Hızlı Erişim</h3>
                    <ul>
                        <li><a href="index.php"><i class="fas fa-chevron-right"></i> Ana Sayfa</a></li>
                        <li><a href="#menu"><i class="fas fa-chevron-right"></i> Menü</a></li>
                        <li><a href="#about"><i class="fas fa-chevron-right"></i> Hakkımızda</a></li>
                        <li><a href="#contact"><i class="fas fa-chevron-right"></i> İletişim</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Kategoriler</h3>
                    <ul>
                        <?php foreach (array_slice($categories, 0, 5) as $category): ?>
                            <li>
                                <a href="?category=<?php echo $category['id']; ?>">
                                    <i class="fas fa-chevron-right"></i> <?php echo $category['name']; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>İletişim</h3>
                    <ul>
                        <li><a href="#"><i class="fas fa-map-marker-alt"></i> 19 Mayıs/Samsun</a></li>
                        <li><a href="tel:03625112449"><i class="fas fa-phone-alt"></i> 0362 511 2449</a></li>
                        <li><a href="mailto:info@damakdostlari.com"><i class="fas fa-envelope"></i> info@damakdostlari.com</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> Damak Dostları. Tüm hakları saklıdır.</p>
            </div>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const navLinks = document.getElementById('nav-links');
        
        if (mobileMenuBtn && navLinks) {
            mobileMenuBtn.addEventListener('click', function(e) {
                e.stopPropagation(); // Stop event from bubbling up to document
                navLinks.classList.toggle('active');
                
                if (navLinks.classList.contains('active')) {
                    mobileMenuBtn.innerHTML = '<i class="fas fa-times"></i>';
                } else {
                    mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
                }
            });
        }
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (navLinks && navLinks.classList.contains('active') && 
                !navLinks.contains(event.target) && 
                !mobileMenuBtn.contains(event.target)) {
                navLinks.classList.remove('active');
                mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
            }
        });
        
        // Prevent menu links from closing immediately when clicked
        navLinks.addEventListener('click', function(e) {
            // If it's a link inside the menu, allow some time to navigate
            if (e.target.tagName === 'A') {
                e.stopPropagation();
            }
        });
        
        // Smooth scroll for internal links
        const internalLinks = document.querySelectorAll('a[href^="#"]');
        internalLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 100,
                        behavior: 'smooth'
                    });
                    
                    // Close mobile menu if open
                    if (navLinks.classList.contains('active')) {
                        navLinks.classList.remove('active');
                        mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
                    }
                }
            });
        });
        
        // Add subtle parallax effect to hero section
        window.addEventListener('scroll', function() {
            const scrollPosition = window.pageYOffset;
            const hero = document.querySelector('.hero');
            if (hero) {
                hero.style.backgroundPositionY = scrollPosition * 0.5 + 'px';
            }
        });
    });
    </script>
</body>
</html>