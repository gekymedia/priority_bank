@extends('layouts.app')

@section('title', 'Terms of Service - Priority Bank')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
        <div class="bg-indigo-700 text-white px-6 py-4">
            <h1 class="text-2xl font-bold">Terms of Service</h1>
            <p class="text-indigo-200 text-sm mt-1">Effective Date: {{ date('F j, Y') }}</p>
        </div>
        
        <div class="p-6 space-y-6">
            <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 mb-6">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-600 dark:text-blue-400 mr-3 mt-1"></i>
                    <div>
                        <strong class="text-blue-900 dark:text-blue-100">Important:</strong>
                        <p class="text-blue-800 dark:text-blue-200 text-sm mt-1">
                            Please read these Terms of Service carefully before using Priority Bank. By accessing or using our services, you agree to be bound by these terms.
                        </p>
                    </div>
                </div>
            </div>

            <div class="prose dark:prose-invert max-w-none">
                <h2 class="text-xl font-semibold mt-6 mb-3 text-gray-900 dark:text-white">1. Acceptance of Terms</h2>
                <p class="text-gray-700 dark:text-gray-300">
                    Welcome to Priority Bank Central Finance Management System. These Terms of Service ("Terms") govern your access to and use of the Priority Bank platform, including all financial management features, API integrations, and related services (collectively, the "Services").
                </p>
                <p class="text-gray-700 dark:text-gray-300 mt-2">
                    By creating an account, accessing the system, or using our Services, you agree to be bound by these Terms and our Privacy Policy. If you do not agree to these Terms, you may not use our Services.
                </p>

                <h2 class="text-xl font-semibold mt-6 mb-3 text-gray-900 dark:text-white">2. Use of the Platform</h2>
                <p class="text-gray-700 dark:text-gray-300 mb-3">
                    This platform is intended for authorized users to manage financial transactions, income, expenses, and related financial data. You agree to:
                </p>
                <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                    <li>Use the Services only for lawful purposes and in accordance with these Terms</li>
                    <li>Provide accurate and truthful financial information</li>
                    <li>Maintain the confidentiality of your account credentials</li>
                    <li>Notify us immediately of any unauthorized access to your account</li>
                    <li>Not attempt to gain unauthorized access to any part of the system</li>
                </ul>

                <h2 class="text-xl font-semibold mt-6 mb-3 text-gray-900 dark:text-white">3. Account Responsibilities</h2>
                <p class="text-gray-700 dark:text-gray-300 mb-3">
                    You are responsible for:
                </p>
                <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                    <li>All activities that occur under your account</li>
                    <li>Maintaining the security of your password and authentication tokens</li>
                    <li>Ensuring the accuracy of all financial data you enter</li>
                    <li>Complying with all applicable laws and regulations regarding financial record-keeping</li>
                </ul>

                <h2 class="text-xl font-semibold mt-6 mb-3 text-gray-900 dark:text-white">4. System Integration</h2>
                <p class="text-gray-700 dark:text-gray-300 mb-3">
                    Priority Bank integrates with various management systems (Gekymedia, SchoolsGH, Priority Solutions Agency, etc.) to synchronize financial data. By using integrated systems, you acknowledge that:
                </p>
                <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                    <li>Data may be shared between Priority Bank and connected systems</li>
                    <li>You have authorized the integration and data synchronization</li>
                    <li>Changes in one system may be reflected in connected systems</li>
                    <li>We are not responsible for data accuracy in external systems</li>
                </ul>

                <h2 class="text-xl font-semibold mt-6 mb-3 text-gray-900 dark:text-white">5. Intellectual Property</h2>
                <p class="text-gray-700 dark:text-gray-300">
                    All materials, content, software, and functionality on this platform are the property of Priority Bank and its licensors. You may not:
                </p>
                <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300 mt-2">
                    <li>Reproduce, distribute, or create derivative works without permission</li>
                    <li>Reverse engineer, decompile, or disassemble the software</li>
                    <li>Remove or alter any copyright, trademark, or proprietary notices</li>
                    <li>Use the system to develop competing services</li>
                </ul>

                <h2 class="text-xl font-semibold mt-6 mb-3 text-gray-900 dark:text-white">6. Service Availability</h2>
                <p class="text-gray-700 dark:text-gray-300">
                    We strive to provide uninterrupted service, but we cannot guarantee 100% uptime. The Services may be unavailable due to:
                </p>
                <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300 mt-2">
                    <li>Scheduled maintenance and updates</li>
                    <li>Technical issues or system failures</li>
                    <li>Third-party service disruptions</li>
                    <li>Force majeure events</li>
                </ul>
                <p class="text-gray-700 dark:text-gray-300 mt-3">
                    We will make reasonable efforts to notify users of planned maintenance and minimize service interruptions.
                </p>

                <h2 class="text-xl font-semibold mt-6 mb-3 text-gray-900 dark:text-white">7. Data Accuracy and Liability</h2>
                <p class="text-gray-700 dark:text-gray-300 mb-3">
                    While we strive to maintain accurate financial records, you are responsible for:
                </p>
                <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                    <li>Verifying the accuracy of all entered data</li>
                    <li>Reviewing transactions and reports for errors</li>
                    <li>Reporting discrepancies promptly</li>
                </ul>
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-500 p-4 mt-4">
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Priority Bank is not responsible for financial decisions made based on system data. Always verify critical information independently.
                    </p>
                </div>

                <h2 class="text-xl font-semibold mt-6 mb-3 text-gray-900 dark:text-white">8. Prohibited Activities</h2>
                <p class="text-gray-700 dark:text-gray-300 mb-3">You may not:</p>
                <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                    <li>Enter false or fraudulent financial information</li>
                    <li>Attempt to manipulate or tamper with financial records</li>
                    <li>Use automated systems to access the API without authorization</li>
                    <li>Interfere with or disrupt the Services or servers</li>
                    <li>Share your account credentials with unauthorized parties</li>
                </ul>

                <h2 class="text-xl font-semibold mt-6 mb-3 text-gray-900 dark:text-white">9. Modifications to Terms</h2>
                <p class="text-gray-700 dark:text-gray-300">
                    We reserve the right to modify these Terms at any time. We will notify users of significant changes through:
                </p>
                <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300 mt-2">
                    <li>In-app notifications</li>
                    <li>Email communication</li>
                    <li>Updated version date on this page</li>
                </ul>
                <p class="text-gray-700 dark:text-gray-300 mt-3">
                    Continued use of the Services after changes constitutes acceptance of the updated Terms.
                </p>

                <h2 class="text-xl font-semibold mt-6 mb-3 text-gray-900 dark:text-white">10. Termination</h2>
                <p class="text-gray-700 dark:text-gray-300">
                    We may suspend or terminate your access to the Services if you:
                </p>
                <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300 mt-2">
                    <li>Violate these Terms</li>
                    <li>Engage in fraudulent or illegal activities</li>
                    <li>Fail to pay required fees (if applicable)</li>
                    <li>Request account deletion</li>
                </ul>

                <h2 class="text-xl font-semibold mt-6 mb-3 text-gray-900 dark:text-white">11. Contact Information</h2>
                <p class="text-gray-700 dark:text-gray-300 mb-3">
                    If you have any questions or concerns about these Terms, please contact us:
                </p>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-gray-700 dark:text-gray-300">
                        <i class="fas fa-envelope mr-2 text-indigo-600 dark:text-indigo-400"></i>
                        <a href="mailto:admin@prioritybank.com" class="text-indigo-600 dark:text-indigo-400 hover:underline">admin@prioritybank.com</a>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="bg-gray-100 dark:bg-gray-700 px-6 py-3 flex justify-between items-center text-sm text-gray-600 dark:text-gray-400">
            <span>Document Version: 1.0</span>
            <span>Effective Date: {{ date('F j, Y') }}</span>
        </div>
    </div>
</div>
@endsection

