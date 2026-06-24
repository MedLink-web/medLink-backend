<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f7fafc; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff;
                     border-radius: 12px; padding: 40px;
                     box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #276749; }
        .info-box { background: #f0fff4; border: 1px solid #9ae6b4;
                    border-radius: 8px; padding: 20px; margin: 20px 0; }
        .credentials { background: #ebf8ff; border: 1px solid #90cdf4;
                       border-radius: 8px; padding: 20px; margin: 20px 0; }
        .credentials p { margin: 8px 0; font-size: 16px; }
        .footer { text-align: center; color: #718096;
                  font-size: 13px; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ تم قبول طلبك!</h1>
            <p>مرحباً بك في منصة MedLink</p>
        </div>

        <p>عزيزي / <strong>{{ $clinicName }}</strong></p>
        <p>يسعدنا إخبارك بأنه تم قبول طلب تسجيل عيادتك في منصة MedLink.</p>

        <div class="info-box">
            🎉 عيادتك الآن جزء من منصة MedLink الطبية!
        </div>

        <p>بيانات تسجيل الدخول الخاصة بك:</p>
        <div class="credentials">
            <p>📧 <strong>البريد الإلكتروني:</strong> {{ $email }}</p>
            <p>🔑 <strong>كلمة المرور المؤقتة:</strong> {{ $password }}</p>
        </div>

        <p style="color: #e53e3e;">
            ⚠️ يرجى تغيير كلمة المرور فور تسجيل دخولك الأول.
        </p>

        <div class="footer">
            <p>MedLink - منصتك الصحية الرقمية</p>
        </div>
    </div>
</body>
</html>
