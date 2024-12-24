<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> عملية تحديث للوحة التحكم</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}"> <!-- Link your app's CSS -->
    <style>
        /* Body styling */
        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
            background: linear-gradient(135deg, #d7e8f7, #eef2f3);
            font-family: 'Poppins', Arial, sans-serif;
            animation: bg-pulse 15s infinite alternate;
        }

        /* Animated background */
        @keyframes bg-pulse {
            0% {
                background: linear-gradient(135deg, #d7e8f7, #eef2f3);
            }

            100% {
                background: linear-gradient(135deg, #f7f9fb, #dce4ec);
            }
        }

        /* Main error page container */
        .error-page {
            position: relative;
            text-align: center;
            animation: float 4s ease-in-out infinite;
        }

        /* Floating effect for error content */
        @keyframes float {

            0%,
            100% {
                transform: translateY(-10px);
            }

            50% {
                transform: translateY(20px);
            }
        }

        /* Error image styling */
        .error-page img {
            max-width: 90%;
            /* Larger image size */
            max-height: 70vh;
            /* Limit height for large screens */
            border-radius: 20px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
            transition: transform 0.6s ease, box-shadow 0.4s ease;
        }

        /* Image hover effect */
        .error-page img:hover {
            transform: scale(1.1) rotate(-3deg);
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.4);
        }

        /* Progress bar styling */
        .progress-container {
            position: relative;
            width: 90%;
            /* Matches the max width of the image */
            height: 10px;
            margin: 20px auto 0 auto;
            /* Centered and spaced below the image */
            background: #e0e0e0;
            border-radius: 5px;
            overflow: hidden;
        }

        .progress-bar {
            position: absolute;
            height: 100%;
            width: 0%;
            background: #fbbc16;
            /* Updated color */
            border-radius: 5px;
            animation: load 600s linear forwards;
            /* Updated duration for 10 minutes (600 seconds) */
        }

        /* Progress bar animation */
        @keyframes load {
            0% {
                width: 0%;
            }

            100% {
                width: 100%;
            }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .error-page img {
                max-width: 80%;
            }

            .progress-container {
                width: 80%;
            }
        }
    </style>
</head>

<body>
    <div class="error-page">
        <img src="{{ asset('images/500-error.png') }}" alt="500 Internal Server Error">
        <!-- Progress bar container -->
        <div class="progress-container">
            <div class="progress-bar"></div>
        </div>
    </div>
</body>

</html>
