<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequests\LoginRequest;
use App\Http\Requests\AuthRequests\PasswordResetRequest;
use App\Http\Requests\AuthRequests\RegisterRequest;
use App\Mail\EmailConfirmation;
use App\Mail\EmailVerification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
	private static function sendVerification(string $name, mixed $email, mixed $verification_code): void
	{
		Mail::to($email)->send(new EmailVerification([
			'name'              => $name,
			'verification_code' => $verification_code,
		]));
	}

	private static function sendConfirmation(string $name, mixed $email, mixed $verification_code): void
	{
		$data = [
			'name'              => $name,
			'verification_code' => $verification_code,
		];
		Mail::to($email)->send(new EmailConfirmation($data));
	}

	public function create(RegisterRequest $request): JsonResponse
	{
		$user = User::create($request->validated());
		$user->verification_code = sha1(time());
		$user->save();
		if ($user != null)
		{
			$this->sendVerification($user->name, $user->email, $user->verification_code);
			return response()->json(['message'=>'Verification Email Sent!'], 201);
		}
	}

	public function login(LoginRequest $request): JsonResponse
	{
		$credentials = $request->validated();
		$user = User::where('email', $request->name)->orWhere('name', $request->name)->first();
		if ($user && $user->email_verified_at === null)
		{
			return response()->json(['message'=>'Check your email to activate account.'], 403);
		}
		elseif (!$token = auth('api')->attempt($credentials))
		{
			return response()->json(['error' => 'incorrect credentials'], 401);
		}
		elseif (auth()->attempt($credentials))
		{
			if ($request->remember_me)
			{
				$token = auth('api')->setTTL(7200)->attempt($credentials);
			}
			return $this->respondWithToken($token, $request);
		}
	}

	protected function respondWithToken(string $token): JsonResponse
	{
		return response()->json([
			'access_token' => $token,
			'token_type'   => 'bearer',
			'expires_in'   => auth('api')->factory()->getTTL() * 60,
			'username'     => auth()->user()->name,
			'user_email'   => auth()->user()->email,
			'user_id'      => auth()->user()->id,
			'user_pfp'     => auth()->user()->profile_pic,
		], 200);
	}

	public function logout(): JsonResponse
	{
		auth()->logout(true);
		return response()->json(['message'=>'Logged out successfully.auth token invalidated.'], 200);
	}

	public function verifyEmail(Request $request): JsonResponse
	{
		$verification_code = $request->token;
		$user = User::firstWhere('verification_code', $verification_code);

		if ($user !== null)
		{
			$user->markEmailAsVerified();
			return response()->json(['message'=>'Email Activated Successfully'], 200);
		}
	}

	public function confirmEmail(Request $request): JsonResponse
	{
		$user = User::firstWhere(['email'=>$request->email, 'google_user'=>false]);

		if ($user)
		{
			$this->sendConfirmation($user->name, $user->email, $user->verification_code);
			return response()->json(['message'=>'Email confirmation sent.'], 200);
		}
		else
		{
			return response()->json(['error'=>'User does not exist'], 404);
		}
	}

	public function resetPassword(PasswordResetRequest $request): JsonResponse
	{
		$user = User::where('verification_code', $request->code)->first();
		$user->update($request->validated());
		return response()->json(['message'=>'Password updated successfully.'], 200);
	}
}
