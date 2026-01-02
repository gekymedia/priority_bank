@extends('layouts.app')
@section('title','Send Notifications')
@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-8 text-gray-900 dark:text-white">Send Notifications</h1>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <form method="POST" action="{{ route('notifications.send') }}" class="space-y-6">
                @csrf

                <!-- Channel Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Notification Channel
                    </label>
                    <select name="channel" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white" required>
                        <option value="sms">SMS</option>
                        <option value="email">Email</option>
                    </select>
                </div>

                <!-- User Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Select Member (leave blank to broadcast to all)
                    </label>
                    <select name="user_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                        <option value="">-- Broadcast to all members --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}"
                                    data-phone="{{ $user->phone }}"
                                    data-email="{{ $user->email }}">
                                {{ $user->name }}
                                @if($user->phone) ({{ $user->phone }}) @endif
                                @if($user->email) [{{ $user->email }}] @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Email Subject (only for email) -->
                <div id="emailSubjectField" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Email Subject
                    </label>
                    <input type="text" name="subject" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white" placeholder="Priority Savings Group Notification">
                </div>

                <!-- Message -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Message
                    </label>

                    <!-- Template Selection -->
                    <select id="templateSelect" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white mb-3">
                        <option value="">-- Choose a template (optional) --</option>
                        <option value="Dear @{{name}}, your loan application has been approved. Please check your dashboard for details.">Loan Approved</option>
                        <option value="Hi @{{name}}, your loan payment of GHS @{{amount}} has been received. Thank you for your commitment to our community!">Payment Received</option>
                        <option value="Hello @{{name}}, your loan payment is due soon. Please make your payment to avoid penalties.">Payment Reminder</option>
                        <option value="Dear @{{name}}, thank you for contributing to our savings pool. Your commitment helps our community grow!">Savings Thank You</option>
                        <option value="Hi @{{name}}, we hope you're doing well. We look forward to seeing you at our next community meeting!">General Update</option>
                    </select>

                    <textarea name="message" rows="6" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white" placeholder="Enter your message..." required></textarea>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-md font-medium transition duration-150">
                        Send Notification
                    </button>
                </div>
            </form>
        </div>

        <!-- Help Text -->
        <div class="mt-6 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <h3 class="text-lg font-medium text-blue-900 dark:text-blue-100 mb-2">How to use:</h3>
            <ul class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
                <li>• <strong>SMS:</strong> Requires users to have phone numbers in their profiles</li>
                <li>• <strong>Email:</strong> Requires users to have email addresses in their profiles</li>
                <li>• <strong>Broadcast:</strong> Leave member selection blank to send to all eligible users</li>
                <li>• <strong>Templates:</strong> Use pre-written templates and replace @{{name}} with actual names</li>
            </ul>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const channelSelect = document.querySelector('select[name="channel"]');
    const emailSubjectField = document.getElementById('emailSubjectField');
    const templateSelect = document.getElementById('templateSelect');

    // Show/hide email subject field based on channel
    function toggleEmailSubject() {
        if (channelSelect && emailSubjectField) {
            emailSubjectField.style.display = channelSelect.value === 'email' ? 'block' : 'none';
        }
    }

    if (channelSelect) {
        channelSelect.addEventListener('change', toggleEmailSubject);
        toggleEmailSubject(); // Initial check
    }

    // Template selection
    if (templateSelect) {
        templateSelect.addEventListener('change', function() {
            const textarea = document.querySelector('textarea[name="message"]');
            if (!textarea) return;

            const selectedValue = this.value;
            if (selectedValue) {
                // Get selected user name if available
                const userSelect = document.querySelector('select[name="user_id"]');
                let name = 'valued member';

                if (userSelect && userSelect.selectedOptions.length > 0) {
                    const selectedOption = userSelect.selectedOptions[0];
                    if (selectedOption.value) {
                        // Extract name from option text (remove phone/email parts)
                        const text = selectedOption.text;
                        name = text.split('(')[0].split('[')[0].trim();
                    }
                }

                textarea.value = selectedValue.replace(/\{\{name\}\}/g, name);
            }
        });
    }
});
</script>
@endsection
