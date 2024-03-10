<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\LogoutRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->except(['password_confirmation']);
        $date['password'] = Hash::make($data['password']);

        DB::beginTransaction();
        try {
            $user = User::create($data);
            $user->assignRole('Utilisateur');
            DB::commit();
            Log::info('User registered successfully.', ['email' => $data['email']]);
            return $this->sendSuccessResponse(new UserResource($user), 'User created successfully!', 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('User registration failed.', ['error' => $e->getMessage()]);
            return $this->sendErrorResponse($e->getMessage());
        }
    }
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (isset($user) && (Hash::check($request->password, $user->password))) {
                $token = $user->createToken('personal-token');
                Log::info('User logged in successfully.', ['email' => $user->email]);

                return $this->sendSuccessResponse(['token' => $token->plainTextToken], 'Successfully logged in');
            }
            return $this->sendSuccessResponse(Hash::check($request->password, $user->password));
            Log::warning('Login attempt failed.', ['email' => $request->email]);
            return $this->sendErrorResponse('Oops! Something went wrong!', 422);

        } catch (Exception $e) {
            Log::error('Login error.', ['error' => $e->getMessage()]);
            return $this->sendErrorResponse($e->getMessage());
        }
    }

    public function logout()
    {
        DB::beginTransaction();
        try {
            Auth::user()->tokens()->delete();
            DB::commit();
            Log::info('User logged out successfully.', ['email' => Auth::user()->email]);
            return $this->sendSuccessResponse([], 'Successfully logged out');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendErrorResponse($e->getMessage());
        }
    }

    public function user()
    {
        try {
            $user = Auth::user();
            Log::info('User information retrieved successfully.', ['user_id' => $user->id]);
            return $this->sendSuccessResponse(new UserResource($user));

        } catch (Exception $e) {
            Log::error('User information retrieval error.', ['error' => $e->getMessage()]);
            return $this->sendErrorResponse($e->getMessage());
        }
    }
}
