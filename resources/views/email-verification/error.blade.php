<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email Gagal</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: #fef2f2;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .card {
            background: #ffffff;
            width: 100%;
            max-width: 460px;
            border-radius: 20px;
            padding: 40px 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            text-align: center;
        }

        .logo {
            max-width: 180px;
            height: auto;
            margin-bottom: 24px;
        }

        .icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 20px;
            border-radius: 999px;
            background: #fee2e2;
            color: #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            font-weight: bold;
        }

        h1 {
            margin: 0 0 12px;
            font-size: 28px;
            color: #1f2937;
        }

        p {
            margin: 0 0 28px;
            font-size: 16px;
            line-height: 1.6;
            color: #6b7280;
        }

        .btn {
            display: inline-block;
            background: #374151;
            color: #ffffff;
            text-decoration: none;
            padding: 14px 24px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
        }
    </style>
</head>
<body>
    <div class="card">
        <img src="https://book.thepride.id/images/mybook_logo.png" alt="MyBook Logo" class="logo">

        <div class="icon">!</div>

        <h1>Verifikasi Gagal</h1>
        <p>{{ $message }}</p>

        <!-- <a href="https://book.thepride.id" class="btn">Kembali</a> -->
    </div>
</body>
</html>