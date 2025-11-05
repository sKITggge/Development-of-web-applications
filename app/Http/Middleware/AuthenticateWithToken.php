<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\PersonalAccessToken;
use App\Models\User;

class AuthenticateWithToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->getTokenFromRequest($request);

        if (!$token) {
            return response()->json([
                'message' => 'Authentication token is missing'
            ], 401);
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json([
                'message' => 'Invalid authentication token'
            ], 401);
        }

        if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
            $accessToken->delete();
            return response()->json([
                'message' => 'Token has expired'
            ], 401);
        }

        $user = User::find($accessToken->tokenable_id);

        if (!$user) {
            $accessToken->delete();
            return response()->json([
                'message' => 'User not found'
            ], 401);
        }

        $user->withAccessToken($accessToken);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }

    protected function getTokenFromRequest(Request $request): ?string
    {
        $header = $request->header('Authorization', '');

        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        $token = $request->bearerToken();

        return $token ? $token : null;
    }
}