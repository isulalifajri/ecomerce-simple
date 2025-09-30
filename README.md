### Membuat API Ecommerce Simple

Project ini di buat menggunakan laravel versi 10 dan postman untuk pengujian api nya.

untuk proses pembuatan product nya ini menggunakan data dummy factory, jadi harus menjalankan ini:

```
php artisan migrate:fresh --seed
```

jadi nanti di api tinggal memanggil product nya

## payment
proses pembayaran menggunakan midtrans, dan callback nya menggunakan ngrok