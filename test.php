<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selenium Test Page</title>
    <style>
        /* Genel ayarlar */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
        }

        h1 {
            text-align: center;
            background-color: #007bff;
            color: white;
            padding: 20px;
            margin: 0;
        }

        h2 {
            color: #007bff;
            margin-bottom: 10px;
        }

        div {
            margin: 20px auto;
            padding: 10px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            max-width: 600px;
        }

        label {
            display: block;
            margin: 10px 0;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        p {
            margin-top: 10px;
            color: #666;
        }

        select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        button {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
            font-size: 16px;
        }

        button:hover {
            background-color: #0056b3;
        }

        iframe {
            width: 100%;
            height: 100px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-top: 10px;
        }

        #dynamicContent {
            text-align: center;
            margin-top: 20px;
            font-size: 16px;
            color: #007bff;
        }
    </style>
</head>
<body>
    <h1>Selenium Test Sayfası</h1>

    <!-- Metin girme ve alma -->
    <div>
        <label for="textInput">Metin Girişi:</label>
        <input type="text" id="textInput" placeholder="Metin girin...">
        <p id="textOutput">Burada sonuç gözükecek</p>
    </div>

    <!-- Checkbox ve Radio Button -->
    <div>
        <h2>Checkbox ve Radio Button</h2>
        <label><input type="checkbox" id="checkbox1"> Checkbox 1</label>
        <label><input type="checkbox" id="checkbox2"> Checkbox 2</label>
        <label><input type="radio" name="options" value="option1"> Option 1</label>
        <label><input type="radio" name="options" value="option2"> Option 2</label>
    </div>

    <!-- Dropdown Menüsü -->
    <div>
        <h2>Dropdown Menüsü</h2>
        <select id="dropdown">
            <option value="value1">Seçenek 1</option>
            <option value="value2">Seçenek 2</option>
            <option value="value3">Seçenek 3</option>
        </select>
    </div>

    <!-- Iframe -->
    <div>
        <h2>Iframe</h2>
        <iframe id="testIframe" srcdoc="<p>Bu iframe içeriği.</p>"></iframe>
    </div>

    <!-- Yeni Pencere -->
    <div>
        <h2>Yeni Pencere Aç</h2>
        <button onclick="window.open('https://example.com', '_blank')">Yeni Pencere</button>
    </div>

    <!-- Dinamik İçerik -->
    <div>
        <h2>Dinamik İçerik</h2>
        <button id="loadContent">Dinamik İçerik Yükle</button>
        <div id="dynamicContent" style="display:none;">Bu dinamik olarak yüklendi!</div>
    </div>

    <script>
        document.getElementById('loadContent').onclick = function() {
            setTimeout(() => {
                document.getElementById('dynamicContent').style.display = 'block';
            }, 2000);
        };
    </script>
</body>
</html>
