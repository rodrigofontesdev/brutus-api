<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Mail\AuthenticateWithMagickLink;
use App\Models\MagicLink;
use App\Models\Subscriber;
use Aws\Exception\CredentialsException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Mailer\Exception\TransportException;
use Illuminate\Support\Str;

class SignIn extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cnpj' => ['required', 'string', 'size:14', 'exists:users,cnpj'],
        ]);

        if ($validator->fails()) {
            return Response::json([
                'type' => 'INVALID_REQUEST_ERROR',
                'code' => 400,
                'message' => 'The request was not accepted due to a missing required field or an error in the field format.',
                'path' => '/' . $request->path(),
                'timestamp' => now()->toDateTimeString(),
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $validated = $validator->safe();

            $subscriber = Subscriber::where('cnpj', $validated->cnpj)
                ->select(
                    'id',
                    'email',
                    'secret_word',
                    'email_verified_at'
                )->firstOrFail();

            $magicLink = new MagicLink([
                'token' => Str::uuid()->toString(),
                'expires_at' => now()->addMinutes(5)->toDateTimeString()
            ]);

            $subscriber->magicLinks()->save($magicLink);

            Mail::to($subscriber->email)
                ->send(
                    new AuthenticateWithMagickLink(
                        link: $subscriber->latestMagicLink->fullUrl(),
                        secretWord: $subscriber->secret_word
                    )
                );

            return Response::json([], 204);
        } catch (QueryException | CredentialsException | TransportException $error) {
            return Response::json([
                'type' => 'API_ERROR',
                'code' => 500,
                'message' => 'Something went wrong with Brutus\'s servers. Please, contact the system admin at ' . config('mail.from.address') . '.',
                'path' => '/' . $request->path(),
                'timestamp' => now()->toDateTimeString()
            ], 500);
        }
    }
}
