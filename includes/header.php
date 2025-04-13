<header>
    <div class="logo">
        <img src="egodoner.png" alt="Logo" style="width: 110px; height: 70px;">
    </div>
</header>

<style>
    /* Genel Sayfa Stilleri */
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: rgb(244, 244, 244);
    }

    /* Header Stilleri */
    header {
        background-color: #0033AB;
        color: white;
        display: flex;
        justify-content: center; /* Yatay merkezleme */
        align-items: center; /* Dikey merkezleme */
        height: 100px; /* Header yüksekliği */
    }

    .logo {
        display: flex;
        justify-content: center; /* Yatay merkezleme */
        align-items: center; /* Dikey merkezleme */
    }

    .logo img {
        display: block;
    }

    /* Responsive Tasarım */
    @media (max-width: 768px) {
        header {
            flex-direction: column;
            text-align: center;
            height: auto; /* İçeriğe göre yükseklik ayarı */
        }

        .logo {
            margin-bottom: 10px;
        }
    }
</style>
