@extends('layouts.app')

@section('title', 'Privacy Policy - Priority Bank')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
        <div class="bg-indigo-700 text-white px-6 py-4">
            <h1 class="text-2xl font-bold">Privacy Policy</h1>
            <p class="text-indigo-200 text-sm mt-1">Last Updated: {{ date('F j, Y') }}</p>
        </div>
        
        <div class="p-6 space-y-6">
            <div class="prose dark:prose-invert max-w-none">
                <p class="text-gray-700 dark:text-gray-300">
                    At Priority Bank, we are committed to protecting the privacy and security of your financial information. 
                    This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use 
                    our Central Finance Management System.
                </p>

                <h2 class="text-xl font-semibold mt-6 mb-3 text-gray-900 dark:text-white">1. Information We Collect</h2>
                <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 mb-4">
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        We collect only the information necessary to provide financial management services and maintain accurate records.
                    </p>
                </div>
                <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                    <li><strong>Financial Data:</strong> Income, expenses, transactions, account balances, budgets</li>
                    <li><strong>User Information:</strong> Name, email, role, authentication credentials</li>
                    <li><strong>System Data:</strong> Transaction metadata, system logs, integration data from connected systems</li>
                    <li><strong>Operational Data:</strong> Categories, accounts, payment methods, channels</li>
                </ul>

                <h2 class="text-xl font-semibold mt-6 mb-3 text-gray-900 dark:text-white">2. How We Use Your Information</h2>
                <div class="grid md:grid-cols-2 gap-4 mb-4">
                    <div class="border rounded-lg p-4 bg-gray-50 dark:bg-gray-700">
                        <h3 class="font-semibold text-indigo-600 dark:text-indigo-400 mb-2">Financial Management</h3>
                        <p class="text-sm text-gray-700 dark:text-gray-300">Processing transactions, generating reports, and maintaining financial records</p>
                    </div>
                    <div class="border rounded-lg p-4 bg-gray-50 dark:bg-gray-700">
                        <h3 class="font-semibold text-indigo-600 dark:text-indigo-400 mb-2">System Integration</h3>
                        <p class="text-sm text-gray-700 dark:text-gray-300">Synchronizing data with connected management systems (Gekymedia, SchoolsGH, etc.)</p>
                    </div>
                </div>

                <h2 class="text-xl font-semibold mt-6 mb-3 text-gray-900 dark:text-white">3. Data Protection</h2>
                <div class="flex items-start space-x-4 mb-4">
                    <div class="text-indigo-600 dark:text-indigo-400">
                        <i class="fas fa-lock text-2xl"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-gray-700 dark:text-gray-300 mb-2">We implement industry-standard security measures including:</p>
                        <ul class="list-disc pl-6 space-y-1 text-gray-700 dark:text-gray-300">
                            <li>256-bit SSL encryption for all data transmissions</li>
                            <li>Regular security audits and vulnerability testing</li>
                            <li>Role-based access controls with authentication</li>
                            <li>Secure API endpoints with token-based authentication</li>
                            <li>Regular data backups and disaster recovery procedures</li>
                        </ul>
                    </div>
                </div>

                <h2 class="text-xl font-semibold mt-6 mb-3 text-gray-900 dark:text-white">4. Data Sharing</h2>
                <p class="text-gray-700 dark:text-gray-300 mb-4">
                    We share financial data only with:
                </p>
                <ul class="list-disc pl-6 space-y-2 text-gray-700 dark:text-gray-300">
                    <li><strong>Connected Systems:</strong> Authorized management systems (Gekymedia, SchoolsGH, Priority Solutions Agency, etc.) for synchronization purposes</li>
                    <li><strong>Service Providers:</strong> Trusted third-party services for hosting, payment processing, and technical support</li>
                    <li><strong>Legal Requirements:</strong> When required by law, court order, or regulatory authority</li>
                </ul>
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-500 p-4 mt-4">
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        We never sell or rent your financial information to third-party marketers.
                    </p>
                </div>

                <h2 class="text-xl font-semibold mt-6 mb-3 text-gray-900 dark:text-white">5. Your Rights</h2>
                <p class="text-gray-700 dark:text-gray-300 mb-3">You have the right to:</p>
                <div class="flex flex-wrap gap-2 mb-4">
                    <span class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 rounded-full text-sm">Access</span>
                    <span class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 rounded-full text-sm">Correction</span>
                    <span class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 rounded-full text-sm">Deletion</span>
                    <span class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 rounded-full text-sm">Export</span>
                </div>
                <p class="text-gray-700 dark:text-gray-300">To exercise these rights, please contact your system administrator or email us at:</p>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mt-3">
                    <p class="text-gray-700 dark:text-gray-300">
                        <i class="fas fa-envelope mr-2 text-indigo-600 dark:text-indigo-400"></i>
                        <a href="mailto:admin@prioritybank.com" class="text-indigo-600 dark:text-indigo-400 hover:underline">admin@prioritybank.com</a>
                    </p>
                </div>

                <h2 class="text-xl font-semibold mt-6 mb-3 text-gray-900 dark:text-white">6. Data Retention</h2>
                <div class="overflow-x-auto mb-4">
                    <table class="min-w-full border border-gray-300 dark:border-gray-600">
                        <thead class="bg-gray-100 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-900 dark:text-white">Category</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-900 dark:text-white">Retention Period</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm text-gray-700 dark:text-gray-300">
                            <tr class="border-t border-gray-300 dark:border-gray-600">
                                <td class="px-4 py-2">Financial Transactions</td>
                                <td class="px-4 py-2">7 years (legal requirement)</td>
                            </tr>
                            <tr class="border-t border-gray-300 dark:border-gray-600">
                                <td class="px-4 py-2">User Accounts</td>
                                <td class="px-4 py-2">Until account deletion</td>
                            </tr>
                            <tr class="border-t border-gray-300 dark:border-gray-600">
                                <td class="px-4 py-2">System Logs</td>
                                <td class="px-4 py-2">1 year</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h2 class="text-xl font-semibold mt-6 mb-3 text-gray-900 dark:text-white">7. Policy Updates</h2>
                <p class="text-gray-700 dark:text-gray-300">
                    We will notify users of significant changes through:
                </p>
                <ul class="list-disc pl-6 space-y-1 text-gray-700 dark:text-gray-300 mt-2">
                    <li>Notifications within the application</li>
                    <li>Email communication to registered users</li>
                    <li>Updated version date on this page</li>
                </ul>

                <h2 class="text-xl font-semibold mt-6 mb-3 text-gray-900 dark:text-white">8. Contact Information</h2>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-gray-700 dark:text-gray-300 mb-2">
                        <strong>Priority Bank Central Finance System</strong>
                    </p>
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

