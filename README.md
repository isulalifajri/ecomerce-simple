### Membuat API Ecommerce Simple

Project ini di buat menggunakan laravel versi 10 dan postman untuk pengujian api nya.

untuk proses pembuatan product nya ini menggunakan data dummy factory, jadi harus menjalankan ini:

```
php artisan migrate:fresh --seed
```

jadi nanti di api tinggal memanggil product nya

## payment
proses pembayaran menggunakan midtrans, dan callback nya menggunakan ngrok

docs : `https://github.com/Midtrans/midtrans-php`

untuk menggunakan midtrans jalankan ini: `composer require midtrans/midtrans-php`

kemudian di env nanti tinggal menambahkan code ini:

```
MIDTRANS_MERCHANT_ID=******
MIDTRANS_CLIENT_KEY=******
MIDTRANS_SERVER_KEY=******
```

kemudian di folder config create folder dg nama `midtrans.php` dengan isi kode:

```
<?php

return [
    'merchant_id' => env('MIDTRANS_MERCHANT_ID'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'server_key' => env('MIDTRANS_SERVER_KEY'),
];
```

setelah itu nanti tinggal panggil di controller nya jika ingin melakukan pembayaran, dengan code contoh seperti ini:

```
    public function pay(Request $request)
    {
        $user = auth()->user();

        $validatedData = $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::with('product')->find($validatedData['order_id']);

        // Cek kepemilikan
        if ($user->id !== $order->user_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Midtrans Config
        \Midtrans\Config::$serverKey    = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = false;
        \Midtrans\Config::$isSanitized  = true;
        \Midtrans\Config::$is3ds        = true;

        $itemDetails = [
            [
                'id'       => $order->product->id,
                'price'    => (int) $order->product->price,
                'quantity' => $order->quantity,
                'name'     => $order->product->name,
            ]
        ];

        $params = [
            'transaction_details' => [
                'order_id'     => $order->id,
                'gross_amount' => (int) $order->total_amount,
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => $user->name,
                'email'      => $user->email,
                'phone'      => '08111222333',
            ],
        ];

        $snapToken = \Midtrans\Snap::getSnapToken($params);

        return response()->json([
            'snap_token'   => $snapToken,
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/' . $snapToken,
        ], 200);
    }
```


## Dokumentasi API

Dokumentasi:

```
https://documenter.getpostman.com/view/24453249/2sB3QFQC6p
```

### Dokumentasi API dengan Menggunakan Swagger

jalankan perintah ini:

```
composer require "darkaonline/l5-swagger"
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
php artisan l5-swagger:generate

```

lalu akses dokumentasi nya disini: `http://localhost:port(sesuaikan)/api/documentation`

di controller.php tambahkan code ini:

```

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Tes E-commerce Simple API",
 *     description="Dokumentasi API untuk tes e-commerce sederhana dengan Laravel dan L5-Swagger",
 * )
 *
 * @OA\Server(
 *     url="http://127.0.0.1:9090",
 *     description="Localhost API server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Masukkan token hasil login: Bearer {token}"
 * )
 */

 note: `sesuaikan url nya`

class Controller extends BaseController
```

kemudian contoh pada function register tambahkan code ini:

```
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
    public function register(Request $request){.....}
```

Setiap kali update anotase/kode swagger, jalankan ini: `php artisan l5-swagger:generate`

jika ingin hasil request nya mau lebih clean,, gunakan `try catch` di setiap function nya