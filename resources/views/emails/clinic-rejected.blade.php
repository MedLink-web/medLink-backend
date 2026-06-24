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
        .header h1 { color: #c53030; }
        .reason-box { background: #fff5f5; border: 1px solid #fed7d7;
                      border-radius: 8px; padding: 20px; margin: 20px 0;
                      color: #c53030; }
        .footer { text-align: center; color: #718096;
                  font-size: 13px; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>❌ بخصوص طلب التسجيل</h1>
        </div>

        <p>عزيزي / <strong>{{ $clinicName }}</strong></p>
        <p>نأسف لإبلاغك بأنه تم رفض طلب تسجيل عيادتك في منصة MedLink.</p>

        <p>سبب الرفض:</p>
        <div class="reason-box">
            {{ $reason }}
        </div>

        <p>يمكنك التواصل معنا لمزيد من التوضيح أو إعادة تقديم الطلب بعد استيفاء المتطلبات.</p>

        <div class="footer">
            <p>MedLink - منصتك الصحية الرقمية</p>
        </div>
    </div>
</body>
</html>
