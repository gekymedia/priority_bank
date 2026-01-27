@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            @if (session('status') === 'profile-updated')
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                    Profile updated successfully!
                </div>
            @endif

            <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('patch')

                <div>
                    <h2 class="text-lg font-medium text-gray-900">Profile Information</h2>
                    <p class="mt-1 text-sm text-gray-600">Update your account's profile information and email address.</p>
                </div>

                <!-- Profile Photo Upload - Single Section -->
                <div>
                    <x-input-label for="photo" :value="__('Profile Photo')" />
                    <div class="flex items-center mt-2 space-x-4">
                        <div class="shrink-0">
                            @if(auth()->user()->profile_photo_path)
                                <img class="h-16 w-16 rounded-full object-cover" 
                                     src="{{ asset('storage/'.auth()->user()->profile_photo_path) }}" 
                                     alt="Current profile photo">
                            @else
                                <div class="h-16 w-16 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xl font-bold">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <label class="block flex-1">
                            <span class="sr-only">Choose profile photo</span>
                            <input type="file" 
                                   name="photo" 
                                   id="photo"
                                   class="block w-full text-sm text-gray-500
                                          file:mr-4 file:py-2 file:px-4
                                          file:rounded-md file:border-0
                                          file:text-sm file:font-semibold
                                          file:bg-indigo-50 file:text-indigo-700
                                          hover:file:bg-indigo-100">
                        </label>
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('photo')" />
                </div>

                <div>
                    <x-input-label for="name" :value="__('Name')" />
                    <x-text-input id="name" 
                                 name="name" 
                                 type="text" 
                                 class="mt-1 block w-full" 
                                 :value="old('name', $user->name)" 
                                 required 
                                 autofocus 
                                 autocomplete="name" />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>

                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" 
                                 name="email" 
                                 type="email" 
                                 class="mt-1 block w-full" 
                                 :value="old('email', $user->email)" 
                                 required 
                                 autocomplete="username" />
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                </div>

                <div>
                    <x-input-label for="phone" :value="__('Phone Number')" />
                    <x-text-input id="phone" 
                                 name="phone" 
                                 type="tel" 
                                 class="mt-1 block w-full" 
                                 :value="old('phone', $user->phone)" 
                                 autocomplete="tel" />
                    <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                </div>

                <!-- Notification Preferences -->
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Notification Preferences</h3>
                    <p class="text-sm text-gray-600 mb-4">Select which notification channels you want to receive updates on.</p>
                    
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input id="notification_email" 
                                   name="notification_email" 
                                   type="checkbox" 
                                   value="1"
                                   {{ old('notification_email', $user->notification_email ?? true) ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="notification_email" class="ml-3 block text-sm font-medium text-gray-700">
                                <span class="font-semibold">Email Notifications</span>
                                <span class="text-gray-500 block text-xs mt-1">Receive notifications via email</span>
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input id="notification_browser" 
                                   name="notification_browser" 
                                   type="checkbox" 
                                   value="1"
                                   {{ old('notification_browser', $user->notification_browser ?? true) ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="notification_browser" class="ml-3 block text-sm font-medium text-gray-700">
                                <span class="font-semibold">Browser Notifications</span>
                                <span class="text-gray-500 block text-xs mt-1">Receive in-app browser notifications</span>
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input id="notification_sms" 
                                   name="notification_sms" 
                                   type="checkbox" 
                                   value="1"
                                   {{ old('notification_sms', $user->notification_sms ?? true) ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="notification_sms" class="ml-3 block text-sm font-medium text-gray-700">
                                <span class="font-semibold">SMS Notifications</span>
                                <span class="text-gray-500 block text-xs mt-1">Receive notifications via SMS text messages</span>
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input id="notification_whatsapp" 
                                   name="notification_whatsapp" 
                                   type="checkbox" 
                                   value="1"
                                   {{ old('notification_whatsapp', $user->notification_whatsapp ?? true) ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="notification_whatsapp" class="ml-3 block text-sm font-medium text-gray-700">
                                <span class="font-semibold">WhatsApp Notifications</span>
                                <span class="text-gray-500 block text-xs mt-1">Receive notifications via WhatsApp</span>
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input id="notification_gekychat" 
                                   name="notification_gekychat" 
                                   type="checkbox" 
                                   value="1"
                                   {{ old('notification_gekychat', $user->notification_gekychat ?? true) ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="notification_gekychat" class="ml-3 block text-sm font-medium text-gray-700">
                                <span class="font-semibold">GekyChat Notifications</span>
                                <span class="text-gray-500 block text-xs mt-1">Receive notifications via GekyChat</span>
                            </label>
                        </div>
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('notification_*')" />
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button>{{ __('Save') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    <!-- Password Update Section -->
    <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            @include('profile.partials.update-password-form')
        </div>
    </div>

    <!-- Delete Account Section -->
    <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</div>
@endsection