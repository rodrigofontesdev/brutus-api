<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\MagicLink;
use App\Rules\IsTokenExpired;
use App\Rules\IsTokenUsed;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class Authenticate extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => [
                'required',
                'uuid',
                'exists:magic_links,token',
                new IsTokenUsed,
                new IsTokenExpired
            ],
            'redirect' => 'url:https'
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

            $magicLink = MagicLink::where('token', $validated->token)->firstOrFail();
            $magicLink->used_at = now()->toDateTimeString();
            $magicLink->save();

            Auth::loginUsingId($magicLink->user);

            $request->session()->regenerate();

            return Response::json([
                'message' => 'User successfully authenticated.',
                'redirect' => $validated->redirect
            ]);
        } catch (QueryException | \Throwable $error) {
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
