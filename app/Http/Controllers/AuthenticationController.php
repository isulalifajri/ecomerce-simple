<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;

class AuthenticationController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Authentication"},
     *     summary="Register user baru",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="Budi"),
     *             @OA\Property(property="email", type="string", example="budi@mail.com"),
     *             @OA\Property(property="password", type="string", example="secret123"),
     *             @OA\Property(property="password_confirmation", type="string", example="secret123"),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="Berhasil register"),
     *     @OA\Response(response=422, description="Validasi gagal"),
     * )
     */
    public function register(Request $request)
    {
        try{
            $validatedData = $request->validate([
                'name' => ['required', 'string', 'max:250'],
                'email' => ['required', 'email', 'max:250', 'unique:users'],
                'password' => ['required', 'min:5', 'confirmed'],
            ]);

            User::create($validatedData);

            return response()->json([
                'message' => 'Berhasil Melakukan Pendaftaran'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Authentication"},
     *     summary="Login user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", example="budi@mail.com"),
     *             @OA\Property(property="password", type="string", example="secret123"),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="Berhasil login"),
     *     @OA\Response(response=401, description="Login gagal (email/password salah)"),
     *     @OA\Response(response=422, description="Validasi gagal"),
     *     @OA\Response(response=500, description="Error server"),
     * )
     */
    public function login(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            if (!auth()->attempt($validatedData)) {
                return response()->json([
                    'message' => 'Login gagal',
                    'error'   => 'Email atau password salah',
                ], 401);
            }

            $user = User::where('email', $request->email)->first();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Berhasil Login!',
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Authentication"},
     *     summary="Logout user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Berhasil logout"),
     *     @OA\Response(response=401, description="Tidak terautentikasi / token tidak valid"),
     *     @OA\Response(response=500, description="Error server"),
     * )
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();

            return response()->json([
                'message' => 'Berhasil Logout!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan pada server',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

}
