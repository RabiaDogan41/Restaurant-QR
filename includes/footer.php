<?php
// footer.php dosyası
?>
<footer>
    <p>&copy; <?php echo date("Y"); ?> Ego döner tüm hakları saklıdır.</p>
</footer>

<style>
/* Footer Stilleri */
footer {
    background-color:black; /* Arka plan rengi */
    color: white;            /* Yazı rengi */
    text-align: center;      /* Yazıyı ortala */
    padding: 20px 0;         /* Üst ve alt boşluk */
    position: relative;
    width: 100%;             /* Footer'ın tam genişlikte olmasını sağlar */
    bottom: 0;               /* Sayfanın alt kısmına yapıştırır */
    font-size: 14px;         /* Yazı boyutunu belirle */
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1); /* Hafif bir gölge efekti */
}

/* Footer içindeki tarih kısmı */
footer p {
    margin: 0;
    padding: 0;
    font-size: 16px;  /* Yazı boyutunu biraz daha büyüt */
    font-weight: normal;
}

/* Mobil uyumlu footer düzeni */
@media (max-width: 768px) {
    footer {
        padding: 15px 0; /* Mobilde daha küçük padding */
    }

    footer p {
        font-size: 14px; /* Daha küçük yazı boyutu */
    }
}
</style>
