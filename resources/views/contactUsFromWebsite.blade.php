<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تواصل معنا</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #f0f2f5;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            direction: rtl;
        }

        header,
        footer {
            background: rgb(255, 180, 0);
            color: #ffffff;
            padding: 10px 20px;
            width: 100%;
            text-align: center;
            font-weight: bold;
        }

        header {
            position: sticky;
            top: 0;
        }

        footer {
            position: sticky;
            bottom: 0;
        }

        .contact-form-container {
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            margin: 20px 0;
        }

        .contact-form-container h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: rgb(15, 66, 153);
            font-weight: bold;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #dddddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 4px;
            background: rgb(15, 66, 153);
            color: #ffffff;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: rgb(255, 180, 0);
        }

        #responseMessage {
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>

<body>
    @if (session('status'))
        <div style="color: {{ session('status')['success'] ? 'green' : 'red' }}; text-align: center; margin-top: 20px;">
            {{ session('status')['msg'] }}
        </div>
    @endif
    <div class="contact-form-container">
        <h1 style="color: rgb(15, 66, 153)">تواصل معنا</h1>
        <form id="contactForm" method="POST" action="https://dev.emdadatalatta.com/store_from_website">
            @csrf
            <div class="form-group">
                <label style="color: rgb(15, 66, 153)" for="company-name">اسم الشركة :</label>
                <input type="text" id="company-name" name="company_name" required>
            </div>
            <div class="form-group">
                <label style="color: rgb(15, 66, 153)" for="contact-name">اسم جهة الاتصال :</label>
                <input type="text" id="contact-name" name="contact_name" required>
            </div>
            <div class="form-group">
                <label style="color: rgb(15, 66, 153)" for="email">البريد الالكتروني :</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label style="color: rgb(15, 66, 153)" for="number">رقم الاتصال :</label>
                <input type="text" id="number" name="number" required>
            </div>
            <button type="submit">إرسال</button>
        </form>
        <div id="responseMessage"></div>
    </div>

</body>

</html>
