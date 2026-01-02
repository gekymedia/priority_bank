<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Jobs\SendNotificationMessage;

class NotificationController extends Controller
{
    public function create()
    {
        $users = User::orderBy('name')->get(['id','name','phone','email']);
        return view('notifications.create', compact('users'));
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'channel' => 'required|in:sms,email',
            'user_id' => 'required_without:broadcast|nullable|exists:users,id',
            'broadcast' => 'nullable|boolean',
            'message' => 'required|string|max:1000',
            'subject' => 'nullable|string|max:255',
        ]);

        $channel = $data['channel'];
        $message = $data['message'];
        $count = 0;

        // Determine what field to use based on channel
        $needsPhone = $channel === 'sms';
        $needsEmail = $channel === 'email';

        if ($request->boolean('broadcast')) {
            $query = User::query();

            if ($needsPhone) {
                $query->whereNotNull('phone');
            } elseif ($needsEmail) {
                $query->whereNotNull('email');
            }

            $query->orderBy('id')
                ->chunkById(100, function($chunk) use ($channel, $message, &$count, $needsPhone, $needsEmail, $request) {
                foreach ($chunk as $user) {
                    $to = null;
                    if ($needsPhone) {
                        $to = $user->phone;
                    } elseif ($needsEmail) {
                        $to = $user->email;
                    }

                    if (!$to) {
                        continue;
                    }
                    $subject = $request->input('subject');
                    SendNotificationMessage::dispatch($channel, $to, $message, $subject);
                    $count++;
                }
            });
        } else {
            $user = User::findOrFail($data['user_id']);

            if ($needsPhone) {
                $to = $user->phone;
                if (!$to) {
                    return back()->with('error','User has no phone number');
                }
            } elseif ($needsEmail) {
                $to = $user->email;
                if (!$to) {
                    return back()->with('error','User has no email address');
                }
            }

            $subject = $request->input('subject');
            SendNotificationMessage::dispatch($channel, $to, $message, $subject);
            $count = 1;
        }
        return back()->with('success', "Queued {$count} message(s).");
    }
}
