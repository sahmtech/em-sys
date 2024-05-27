<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تواصل معنا</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Almarai:wght@400;700&display=swap">
    <style>
        body {
            font-family: 'Almarai', sans-serif;
            background: rgb(255, 180, 0);
            /* //linear-gradient(to bottom, rgb(255, 180, 0), rgb(222, 193, 9)); */
            color: rgb(15, 66, 153);
            display: flex;
            flex-direction: column;
            justify-content: center;
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
            background: rgb(255, 180, 0);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            margin: 20px 0;
            color: rgb(15, 66, 153);
        }

        .contact-form-container h1 {
            text-align: center;
            margin-bottom: 20px;
            color: rgb(15, 66, 153);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: rgb(15, 66, 153);
            font-weight: bold;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
            background: #ffffff;
            color: rgb(15, 66, 153);
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: rgb(15, 66, 153);
            outline: none;
            box-shadow: 0 0 5px rgba(15, 66, 153, 0.2);
        }

        button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 5px;
            background: rgb(15, 66, 153);
            color: #ffffff;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background: rgb(255, 180, 0);
        }

        .alert {
            margin-top: 20px;
            padding: 10px 20px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            opacity: 1;
            transition: opacity 0.5s ease-in-out;
        }

        .alert-success {
            background-color: rgb(15, 66, 153);
        }

        .alert-error {
            background-color: red;
        }

        .alert.hide {
            opacity: 0;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const alert = document.querySelector('.alert');
            if (alert) {
                setTimeout(() => {
                    alert.classList.add('hide');
                }, 3000);
            }
        });
    </script>
</head>

<body>
    <div class="contact-form-container">

        @if (session('status'))
            <div class="alert {{ session('status')['success'] ? 'alert-success' : 'alert-error' }}">
                {{ session('status')['msg'] }}
            </div>
        @endif
        <form id="contactForm" method="POST" action="https://emdadatalatta.com/store_from_website">
            @csrf
            <div class="form-group">
                <label for="company-name">اسم الشركة :</label>
                <input type="text" id="company-name" name="company_name" required>
            </div>
            <div class="form-group">
                <label for="contact-name">اسم جهة الاتصال :</label>
                <input type="text" id="contact-name" name="contact_name" required>
            </div>
            <div class="form-group">
                <label for="email">البريد الالكتروني :</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="number">رقم الاتصال :</label>
                <input type="text" id="number" name="number" required>
            </div>
            {{-- <div class="form-group">
                <label for="commercial_register_no">السجل التجاري :</label>
                <input type="text" id="commercial_register_no" name="commercial_register_no" required>
            </div> --}}
            <div class="form-group">
                <label for="message">الرسالة :</label>
                <textarea id="message" name="message" rows="2"></textarea>
            </div>
            <button type="submit">إرسال</button>
        </form>
        <div id="responseMessage"></div>
    </div>
</body>

</html>
