<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Password Reset - AARNEXT</title>
  <style>
    body {
      font-family: "Segoe UI", Arial, sans-serif;
      background-color: #f5f7fa;
      margin: 0;
      padding: 0;
      color: #333;
    }

    .container {
      max-width: 600px;
      margin: 30px auto;
      background: #ffffff;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
      border: 1px solid #e2e8f0;
    }

    .header {
      background: linear-gradient(135deg, #007bff, #0056d2);
      color: white;
      text-align: center;
      padding: 30px 20px;
    }

    .header h1 {
      margin: 0;
      font-size: 24px;
      font-weight: 600;
    }

    .body {
      padding: 30px 25px;
      line-height: 1.6;
    }

    .body h2 {
      font-size: 20px;
      margin-bottom: 10px;
      color: #2d3748;
    }

    .body p {
      font-size: 16px;
      margin: 10px 0;
      color: #4a5568;
    }

    .button-container {
      text-align: center;
      margin: 25px 0;
    }

    .reset-button {
      display: inline-block;
      background-color: #007bff;
      color: white !important;
      padding: 12px 25px;
      font-size: 16px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 500;
      transition: background 0.3s ease;
    }

    .reset-button:hover {
      background-color: #0056d2;
    }

    .footer {
      text-align: center;
      background-color: #f1f3f6;
      padding: 20px;
      font-size: 13px;
      color: #718096;
    }

    .footer a {
      color: #007bff;
      text-decoration: none;
    }

    .footer a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>AARNEXT Account Security</h1>
    </div>
    <div class="body">
      <h2>Reset Your Password</h2>
      <p>Hello {{ $customer->name ?? 'User' }},</p>
      <p>
        We received a request to reset the password for your AARNEXT account.
        You can set a new password by clicking the button below.
      </p>

      <div class="button-container">
        <a href="{{ url('customer/reset-password/' . $token . '?email=' . urlencode($customer->email ?? '')) }}" class="reset-button">
          Reset Password
        </a>
      </div>

      <p>
        This link will expire in 60 minutes. If you didn’t request a password reset,
        you can safely ignore this email—your password will remain unchanged.
      </p>

      <p>Stay secure,</p>
      <p><strong>The AARNEXT Support Team</strong></p>
    </div>

    <div class="footer">
      <p>Need help? <a href="mailto:support@aarnext.com">Contact Support</a></p>
      <p>© {{ date('Y') }} AARNEXT. All rights reserved.</p>
    </div>
  </div>
</body>
</html>
