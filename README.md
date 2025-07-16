# Indian Products Sync

A Laravel 12 web application for Indian sellers to register, create products, and sync them to their WooCommerce store via API. Features INR currency, Indian product examples, and a modern UI.

---

## 🚀 Features
- User registration & login (Laravel Sanctum)
- Product management: name, description, price (₹), image URL
- View, create, update, delete your products
- Sync products to WooCommerce via REST API (INR currency)
- Save WooCommerce product IDs for future syncs
- Product status: created, synced, failed
- Error handling and retry sync
- Responsive UI with Bootstrap 5 & Blade

---

## 🛠️ Tech Stack
- **Backend:** Laravel 12, PHP 8+
- **Frontend:** Blade, Bootstrap 5, JavaScript
- **Auth:** Laravel Sanctum
- **Database:** MySQL or PostgreSQL
- **API:** WooCommerce REST API

---

## ⚡ Quick Start

### 1. Clone & Install
```bash
git clone <your-repo-url>
cd woocommerce-sync
composer install
cp .env.example .env
php artisan key:generate
```

### 2. Configure Environment
Edit `.env`:
```
APP_ENV=production
APP_DEBUG=false
DB_DATABASE=your_db
DB_USERNAME=your_user
DB_PASSWORD=your_pass
WOOCOMMERCE_BASE_URL=https://yourstore.com
WOOCOMMERCE_CONSUMER_KEY=ck_xxx
WOOCOMMERCE_CONSUMER_SECRET=cs_xxx
```

### 3. Migrate & Seed
```bash
php artisan migrate --seed
```

### 4. Serve
```bash
php artisan serve --host=0.0.0.0 --port=8000
```
Visit [http://localhost:8000](http://localhost:8000)

---

## 🔗 WooCommerce Integration
1. In WooCommerce Admin, go to **Advanced > REST API**
2. Create a key with **Read/Write** permissions
3. Copy the Consumer Key & Secret to your `.env`
4. Set `WOOCOMMERCE_BASE_URL` to your store root (no trailing slash)
5. Test connection from the dashboard

---

## 📝 Usage
- Register or login (test user: `test@example.com` / `password`)
- Add, edit, or delete products (prices in ₹)
- Click **Test WooCommerce Connection** to verify API
- Products sync automatically on create/update
- Retry failed syncs from the dashboard

---

## 🧩 Troubleshooting
- **422 Unprocessable Entity:** Check required fields and valid image URL
- **WooCommerce sync fails:** Check API keys, permissions, and store URL
- **CORS issues:** Configure CORS in `config/cors.php`
- **SSL errors:** Ensure your server has valid SSL certs (see [cURL CA bundle](https://curl.se/docs/caextract.html))

---

## 🤝 Contributing
Pull requests welcome! For major changes, open an issue first.

---

## 📄 License
[MIT](LICENSE)
