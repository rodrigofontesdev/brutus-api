<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Mail\NewlyRegisteredSubscriber;
use App\Models\MagicLink;
use App\Models\Subscriber;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\Mailer\Exception\TransportException;

class SignUp extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cnpj' => ['required', 'string', 'unique:users,cnpj', 'size:14'],
            'full_name' => ['required', 'string', 'max:100'],
            'mobile_phone' => ['required', 'string', 'size:11'],
            'email' => ['required', 'email:filter', 'unique:users,email', 'max:100'],
        ]);

        if ($validator->fails()) {
            return Response::json([
                'type' => 'INVALID_REQUEST_ERROR',
                'code' => 400,
                'message' => 'The request was not accepted due to a missing required field or an error in the field format.',
                'path' => '/'.$request->path(),
                'timestamp' => now()->toDateTimeString(),
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $validated = $validator->safe();

            $subscriber = new Subscriber();
            $subscriber->email = $validated->email;
            $subscriber->full_name = $validated->full_name;
            $subscriber->cnpj = $validated->cnpj;
            $subscriber->mobile_phone = $validated->mobile_phone;
            $subscriber->save();

            $magicLink = new MagicLink();
            $magicLink->token = Str::uuid()->toString();
            $magicLink->user = $subscriber->id;
            $magicLink->expires_at = Carbon::now()->addMinutes(5)->toDateTimeString();
            $magicLink->save();

            Mail::to($subscriber->email)
                ->send(
                    new NewlyRegisteredSubscriber(
                        link: $subscriber->latestMagicLink->fullUrl(),
                    )
                );

            return Response::json($subscriber, 201);
        } catch (QueryException|TransportException $error) {
            return Response::json([
                'type' => 'API_ERROR',
                'code' => 500,
                'message' => 'Something went wrong with Brutus\'s servers. Please, contact the system admin at '.config('mail.from.address').'.',
                'path' => '/'.$request->path(),
                'timestamp' => now()->toDateTimeString(),
                'errors' => $error->getMessage(),
            ], 500);
        }
    }
}
