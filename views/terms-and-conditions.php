<?php
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions | <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #f0f3ff 100%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: #2c3e50;
            line-height: 1.6;
        }

        .terms-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .terms-nav {
            background: white;
            padding: 24px 0;
            border-bottom: 1px solid #e8eef7;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .terms-nav-content {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #7c3aed;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            padding: 8px 12px;
            margin: -8px -12px;
            border-radius: 6px;
        }

        .back-btn:hover {
            color: #6d28d9;
            background: rgba(124, 58, 237, 0.08);
            transform: translateX(-3px);
        }

        .terms-container {
            flex: 1;
            max-width: 900px;
            margin: 0 auto;
            padding: 48px 24px;
            width: 100%;
        }

        .terms-header {
            text-align: center;
            margin-bottom: 56px;
        }

        .terms-header h1 {
            font-size: 3.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #4c1d95 0%, #7c3aed 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 16px;
            letter-spacing: -1px;
        }

        .terms-header p {
            font-size: 1.1rem;
            color: #6b7280;
            font-weight: 500;
        }

        .terms-content {
            background: white;
            border-radius: 16px;
            padding: 48px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            border: 1px solid #e8eef7;
        }

        .terms-content h2 {
            font-size: 1.55rem;
            font-weight: 700;
            color: #4c1d95;
            margin: 40px 0 20px 0;
            padding-bottom: 16px;
            border-bottom: 3px solid #7c3aed;
            position: relative;
        }

        .terms-content h2:first-child {
            margin-top: 0;
        }

        .terms-content h3 {
            font-size: 1.15rem;
            font-weight: 700;
            color: #6d28d9;
            margin: 24px 0 12px 0;
        }

        .terms-content p {
            font-size: 0.95rem;
            line-height: 1.8;
            margin-bottom: 18px;
            color: #374151;
            text-align: justify;
        }

        .terms-content ul,
        .terms-content ol {
            margin: 0 0 20px 24px;
            padding: 0;
        }

        .terms-content ul li,
        .terms-content ol li {
            margin-bottom: 12px;
            color: #374151;
            font-size: 0.95rem;
            line-height: 1.7;
        }

        .terms-content ul li::marker {
            color: #7c3aed;
            font-weight: 600;
        }

        .last-updated {
            background: linear-gradient(135deg, #f0f3ff 0%, #f8faff 100%);
            padding: 24px;
            border-left: 4px solid #7c3aed;
            border-radius: 12px;
            margin-top: 48px;
            border: 1px solid #e8eef7;
        }

        .last-updated strong {
            color: #4c1d95;
            font-weight: 700;
        }

        .last-updated p {
            font-size: 0.9rem;
            color: #6b7280;
            margin: 6px 0;
        }

        .footer-spacer {
            height: 32px;
        }

        @media (max-width: 768px) {
            .terms-header h1 {
                font-size: 2.2rem;
            }

            .terms-header p {
                font-size: 1rem;
            }

            .terms-container {
                padding: 32px 16px;
            }

            .terms-content {
                padding: 32px 24px;
            }

            .terms-content h2 {
                font-size: 1.3rem;
                margin: 32px 0 16px 0;
            }

            .terms-content h3 {
                font-size: 1.05rem;
            }

            .terms-content p {
                font-size: 0.9rem;
                text-align: left;
            }
        }

        @media (max-width: 480px) {
            .terms-nav-content {
                padding: 0 16px;
            }

            .terms-container {
                padding: 24px 12px;
            }

            .terms-content {
                padding: 24px 16px;
                border-radius: 12px;
            }

            .terms-header h1 {
                font-size: 1.8rem;
                margin-bottom: 12px;
            }

            .terms-header p {
                font-size: 0.95rem;
            }

            .terms-content h2 {
                font-size: 1.15rem;
                margin: 28px 0 14px 0;
            }

            .terms-content ul,
            .terms-content ol {
                margin-left: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="terms-wrapper">
        <nav class="terms-nav">
            <div class="terms-nav-content">
                <a href="<?php echo APP_ROUTE; ?>?page=register" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Registration</span>
                </a>
            </div>
        </nav>

        <div class="terms-container">
            <div class="terms-header">
                <h1><?php echo APP_NAME; ?></h1>
                <p>Terms and Conditions for Online Library</p>
            </div>

            <div class="terms-content">
                <h2>1. Agreement to Terms</h2>
                <p>
                    By accessing and using this Online Library (hereinafter referred to as "Service"), you agree to be bound by these Terms and Conditions. If you do not agree with any part of these terms, you may not use our Service. These terms apply to all visitors, users, and others who access or use the Service.
                </p>

                <h2>2. Description of Service</h2>
                <p>
                    The Online Library is a digital platform that provides access to a collection of books and literary materials. The Service allows registered users to browse, borrow, read, and manage their reading list. We reserve the right to modify, suspend, or discontinue any aspect of the Service at any time with or without notice.
                </p>

                <h2>3. User Accounts</h2>
                <h3>3.1 Registration Requirements</h3>
                <p>
                    To use certain features of the Service, you must create an account. You agree to provide accurate, current, and complete information during the registration process. You are responsible for maintaining the confidentiality of your account credentials and password.
                </p>

                <h3>3.2 Account Responsibilities</h3>
                <p>
                    You are responsible for all activities that occur under your account. You agree to notify us immediately of any unauthorized use of your account or any other breach of security. We cannot be held liable for losses incurred due to unauthorized use of your account.
                </p>

                <h2>4. User Conduct</h2>
                <p>
                    You agree not to use the Service for any unlawful purpose or in any way that could damage, disable, or impair the Service. You shall not:
                </p>
                <ul>
                    <li>Upload, post, or transmit any content that is obscene, defamatory, or infringes on any intellectual property rights</li>
                    <li>Attempt to gain unauthorized access to the Service or its systems</li>
                    <li>Engage in any form of harassment, abuse, or threats toward other users</li>
                    <li>Use automated tools or scripts to access or scrape content from the Service</li>
                    <li>Engage in any commercial activities or sales without explicit permission</li>
                    <li>Distribute viruses, malware, or any harmful code</li>
                </ul>

                <h2>5. Intellectual Property Rights</h2>
                <p>
                    All content available through the Service, including but not limited to books, articles, images, and software, is protected by copyright and other intellectual property laws. You may not reproduce, distribute, or transmit any content without prior written permission from the copyright holder or the Library.
                </p>

                <h2>6. Membership and Subscriptions</h2>
                <p>
                    The Service may offer various membership and subscription plans. By subscribing to any plan, you agree to pay the specified fees according to the terms of your chosen plan. Subscription fees are non-refundable unless otherwise specified. We reserve the right to change subscription fees with 30 days' prior notice.
                </p>

                <h2>7. Limited License</h2>
                <p>
                    We grant you a limited, non-exclusive, non-transferable license to access and read books and materials on the Service for personal, non-commercial use only. This license does not permit you to:
                </p>
                <ul>
                    <li>Sell, rent, lease, or lend the materials</li>
                    <li>Remove any copyright or other proprietary notices</li>
                    <li>Circumvent any technological measures or protection mechanisms</li>
                </ul>

                <h2>8. Disclaimers</h2>
                <p>
                    The Service is provided on an "AS IS" and "AS AVAILABLE" basis without any warranties, express or implied. We do not warrant that:
                </p>
                <ul>
                    <li>The Service will be uninterrupted or error-free</li>
                    <li>The content is accurate, complete, or current</li>
                    <li>The Service is free from viruses or harmful components</li>
                </ul>

                <h2>9. Limitation of Liability</h2>
                <p>
                    To the fullest extent permitted by law, the Online Library and its affiliates shall not be liable for any indirect, incidental, special, consequential, or punitive damages arising out of or related to your use of the Service, even if advised of the possibility of such damages.
                </p>

                <h2>10. Data Privacy and Security</h2>
                <p>
                    Your use of the Service is also governed by our Privacy Policy. We are committed to protecting your personal information and maintaining appropriate security measures. However, no system is completely secure, and we cannot guarantee absolute security of your data.
                </p>

                <h2>11. Suspension and Termination</h2>
                <p>
                    We reserve the right to suspend or terminate your account and access to the Service at any time if we believe you have violated these Terms and Conditions or have engaged in unlawful or harmful activities. Termination may be without notice if necessary to protect the integrity of the Service.
                </p>

                <h2>12. Changes to Terms</h2>
                <p>
                    We reserve the right to modify these Terms and Conditions at any time. Changes will be effective immediately upon posting to the Service. Your continued use of the Service following the posting of changes constitutes your acceptance of the modified terms.
                </p>

                <h2>13. Dispute Resolution</h2>
                <p>
                    Any disputes arising out of or relating to these Terms and Conditions or your use of the Service shall be resolved according to the applicable laws of the jurisdiction in which the Library operates. Both parties agree to attempt to resolve disputes amicably before pursuing legal action.
                </p>

                <h2>14. Third-Party Links and Content</h2>
                <p>
                    The Service may contain links to third-party websites or content. We are not responsible for the content, accuracy, or practices of these external sites. Your use of third-party content is at your own risk and is subject to their terms and conditions.
                </p>

                <h2>15. Contact Information</h2>
                <p>
                    If you have any questions about these Terms and Conditions, please contact us at:
                </p>
                <ul>
                    <li>Email: support@onlinelibrary.com</li>
                    <li>Address: Online Library Services, Digital Hub, Technology Park</li>
                    <li>Phone: 1-800-LIBRARY</li>
                </ul>

                <div class="last-updated">
                    <strong>Last Updated: <?php echo date('F j, Y'); ?></strong>
                    <p>These Terms and Conditions are effective as of the date stated above and will remain in effect until modified.</p>
                </div>
            </div>
        </div>

        <div class="footer-spacer"></div>
    </div>
</body>
</html>
