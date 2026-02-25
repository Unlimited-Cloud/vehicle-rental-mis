<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Passcode Verification</title>
</head>

<body style="margin:0;padding:0;background-color:#f4f6f8;font-family:Arial,Helvetica,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding:30px 15px;">
                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:6px;overflow:hidden;">

                    <!-- Logo -->
                    <tr>
                        <td align="center" style="padding:25px 20px;">
                            <img src="{{ $message->embed(public_path('adminlte/logo.png')) }}"
                                alt="Kathmandu Sightseeing Logo" height="50">
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding:0 40px 30px;color:#333333;">
                            <h2 style="margin:0 0 10px;font-size:22px;">Welcome to Vehicle Rental!</h2>

                            <p style="font-size:15px;margin:0 0 15px;">
                                Hello {{ $name ?? 'User' }},
                            </p>

                            <p style="font-size:15px;margin:0 0 20px;">
                                Your passcode has been successfully sent.
                            </p>

                            <div style="background:#f1f3f5;padding:15px;text-align:center;border-radius:4px;margin-bottom:20px;">
                                <p style="margin:0;font-size:14px;color:#555;">Your passcode is:</p>
                                <h1 style="margin:5px 0 0;font-size:28px;letter-spacing:3px;color:#000;">
                                    {{ $otp }}
                                </h1>
                            </div>

                            <p style="font-size:13px;color:#777;">
                                This passcode will expire shortly. Please do not share it with anyone.
                            </p>

                            <p style="font-size:14px;margin-top:25px;">
                                Best regards,<br>
                                <strong>Vehicle Rental Pvt Ltd</strong><br>
                                Nepal: +977-1-5970800 |
                                info@vehiclerental.com
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#f4f6f8;padding:15px;text-align:center;font-size:12px;color:#888;">
                            © {{ date('Y') }} Vehicle Rental Pvt Ltd. All rights reserved.
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>