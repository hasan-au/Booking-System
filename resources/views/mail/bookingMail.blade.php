<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .header .emoji {
            font-size: 48px;
            margin-bottom: 15px;
            display: block;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 25px;
            color: #2c3e50;
        }
        .greeting strong {
            color: #667eea;
        }
        .details-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
            border-left: 4px solid #667eea;
        }
        .detail-section {
            margin-bottom: 25px;
        }
        .detail-section:last-child {
            margin-bottom: 0;
        }
        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
        }
        .section-title .icon {
            font-size: 20px;
            margin-right: 8px;
        }
        .detail-item {
            margin: 8px 0;
            padding: 8px 0;
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-item:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 500;
            color: #6c757d;
        }
        .detail-value {
            font-weight: 600;
            color: #2c3e50;
        }
        .schedule-highlight {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }
        .schedule-time {
            font-size: 24px;
            font-weight: 600;
            margin: 5px 0;
        }
        .cta-button {
            text-align: center;
            margin: 40px 0;
        }
        .cta-button a {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 15px 40px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 16px;
            transition: transform 0.2s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        .cta-button a:hover {
            transform: translateY(-2px);
        }
        .footer {
            background: #2c3e50;
            color: white;
            text-align: center;
            padding: 30px;
        }
        .footer-message {
            font-size: 18px;
            margin-bottom: 10px;
        }
        .footer-brand {
            font-weight: 600;
            color: #667eea;
        }
        .divider {
            height: 2px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 20px 0;
            border-radius: 1px;
        }
        @media only screen and (max-width: 600px) {
            .content {
                padding: 30px 20px;
            }
            .header {
                padding: 30px 20px;
            }
            .details-card {
                padding: 20px;
            }
            .detail-item {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <span class="emoji">🎉</span>
            <h1>Booking Confirmed!</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Hello <strong>{{ $booking->customer_name ?? 'Valued Customer' }}</strong>,
            </div>

            <p>Great news! Your booking has been confirmed successfully. We're excited to serve you!</p>

            <!-- Booking Details Card -->
            <div class="details-card">
                <!-- Customer Details -->
                <div class="detail-section">
                    <div class="section-title">
                        <span class="icon">👤</span>
                        Customer Information
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Name:</span>
                        <span class="detail-value">{{ $booking->customer_name ?? '—' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Phone:</span>
                        <span class="detail-value">{{ $booking->customer_phone ?? '—' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value">{{ $booking->customer_email ?? '—' }}</span>
                    </div>
                </div>

                <div class="divider"></div>

                <!-- Service Details -->
                <div class="detail-section">
                    <div class="section-title">
                        <span class="icon">💇</span>
                        Service Details
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Service:</span>
                        <span class="detail-value">{{ $booking->service->name ?? '—' }}</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Price:</span>
                        <span class="detail-value">{{ $booking->service->price ? $booking->service->price.' $' : '—' }}</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Duration:</span>
                        <span class="detail-value">{{ $booking->service->duration_minutes.' minutes' ?? '—' }}</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Employee:</span>
                        <span class="detail-value">{{ $booking->employee->name ?? '—' }}</span>
                    </div>
                </div>
            </div>

            <!-- Schedule Highlight -->
            <div class="schedule-highlight">
                <div style="font-size: 18px; margin-bottom: 10px;">🗓️ Your Appointment</div>
                <div class="schedule-time">
                    {{ \Carbon\Carbon::parse($booking->start_at)->format('l, F j, Y') }}
                </div>
                <div style="font-size: 18px; margin-top: 15px;">
                    <strong>{{ \Carbon\Carbon::parse($booking->start_at)->format('g:i A') }}</strong>
                    to
                    <strong>{{ \Carbon\Carbon::parse($booking->end_at)->format('g:i A') }}</strong>
                </div>
            </div>

            <!-- Call to Action -->
            <div class="cta-button">
                <a href="{{ url('/bookings/'.$booking->id) }}">View Full Details</a>
            </div>

            <p style="color: #6c757d; font-size: 14px; line-height: 1.6;">
                <strong>Important:</strong> Please arrive 10 minutes before your appointment time.
                If you need to reschedule or cancel, please contact us at least 24 hours in advance.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-message">
                Thank you for choosing <span class="footer-brand">{{ config('app.name') }}</span>! ✨
            </div>
            <p style="margin: 10px 0 0 0; color: #95a5a6; font-size: 14px;">
                We look forward to providing you with exceptional service.
            </p>
        </div>
    </div>
</body>
</html>
