-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Máy chủ: localhost:3306
-- Thời gian đã tạo: Th6 25, 2026 lúc 03:19 AM
-- Phiên bản máy phục vụ: 8.0.30
-- Phiên bản PHP: 8.4.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `datn_laravel`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `banners`
--

CREATE TABLE `banners` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `brands`
--

CREATE TABLE `brands` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `brands`
--

INSERT INTO `brands` (`id`, `name`, `logo`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Apple', NULL, 'Thương hiệu Apple', '2026-06-17 02:23:15', '2026-06-17 02:23:15'),
(3, 'Samsung', 'brands/1Bci07g9d9s1KSQtKVXgFMvxvzA1d3QeBFndLjs0.jpg', NULL, '2026-06-19 08:45:26', '2026-06-19 08:45:26'),
(4, 'Anker', NULL, 'Thương hiệu phụ kiện hàng đầu thế giới', '2026-06-23 03:24:09', '2026-06-23 03:24:09'),
(5, 'Belkin', NULL, 'Nhà sản xuất phụ kiện công nghệ nổi tiếng', '2026-06-23 03:24:09', '2026-06-23 03:24:09'),
(6, 'Spigen', NULL, 'Chuyên về ốp lưng và bảo vệ điện thoại', '2026-06-23 03:24:09', '2026-06-23 03:24:09'),
(7, 'HOCO', NULL, 'Thương hiệu phụ kiện giá rẻ chất lượng tốt', '2026-06-23 03:24:09', '2026-06-23 03:24:09'),
(8, 'Baseus', NULL, 'Nhà sản xuất phụ kiện công nghệ cao cấp', '2026-06-23 03:24:09', '2026-06-23 03:24:09'),
(9, 'RhinoShield', NULL, 'Chuyên về ốp lưng bảo vệ chuyên nghiệp', '2026-06-23 03:24:09', '2026-06-23 03:24:09'),
(10, 'Ringke', NULL, 'Thương hiệu ốp lưng cao cấp', '2026-06-23 03:24:09', '2026-06-23 03:24:09'),
(11, 'Mophie', NULL, 'Chuyên về pin sạc dự phòng', '2026-06-23 03:24:09', '2026-06-23 03:24:09'),
(12, 'OtterBox', NULL, 'Ốp lưng bảo vệ cấp quân đội', '2026-06-23 03:24:09', '2026-06-23 03:24:09'),
(13, 'Nillkin', NULL, 'Thương hiệu phụ kiện Trung Quốc nổi tiếng', '2026-06-23 03:24:09', '2026-06-23 03:24:09'),
(14, 'Anker', NULL, 'Thương hiệu phụ kiện hàng đầu thế giới', '2026-06-23 03:24:40', '2026-06-23 03:24:40'),
(15, 'Belkin', NULL, 'Nhà sản xuất phụ kiện công nghệ nổi tiếng', '2026-06-23 03:24:40', '2026-06-23 03:24:40'),
(16, 'Spigen', NULL, 'Chuyên về ốp lưng và bảo vệ điện thoại', '2026-06-23 03:24:40', '2026-06-23 03:24:40'),
(17, 'HOCO', NULL, 'Thương hiệu phụ kiện giá rẻ chất lượng tốt', '2026-06-23 03:24:40', '2026-06-23 03:24:40'),
(18, 'Baseus', NULL, 'Nhà sản xuất phụ kiện công nghệ cao cấp', '2026-06-23 03:24:40', '2026-06-23 03:24:40'),
(19, 'RhinoShield', NULL, 'Chuyên về ốp lưng bảo vệ chuyên nghiệp', '2026-06-23 03:24:40', '2026-06-23 03:24:40'),
(20, 'Ringke', NULL, 'Thương hiệu ốp lưng cao cấp', '2026-06-23 03:24:40', '2026-06-23 03:24:40'),
(21, 'Mophie', NULL, 'Chuyên về pin sạc dự phòng', '2026-06-23 03:24:40', '2026-06-23 03:24:40'),
(22, 'OtterBox', NULL, 'Ốp lưng bảo vệ cấp quân đội', '2026-06-23 03:24:40', '2026-06-23 03:24:40'),
(23, 'Nillkin', NULL, 'Thương hiệu phụ kiện Trung Quốc nổi tiếng', '2026-06-23 03:24:40', '2026-06-23 03:24:40');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `carts`
--

CREATE TABLE `carts` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 9, '2026-06-19 09:04:43', '2026-06-19 09:04:43'),
(2, 7, '2026-06-25 02:08:00', '2026-06-25 02:08:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cart_items`
--

CREATE TABLE `cart_items` (
  `id` bigint UNSIGNED NOT NULL,
  `cart_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `product_variant_id` bigint UNSIGNED DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `cart_items`
--

INSERT INTO `cart_items` (`id`, `cart_id`, `product_id`, `product_variant_id`, `quantity`, `created_at`, `updated_at`) VALUES
(2, 2, 60, NULL, 1, '2026-06-25 02:08:00', '2026-06-25 02:08:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Điện thoại', 'Danh mục điện thoại', '2026-06-17 02:23:08', '2026-06-17 02:23:08'),
(4, 'Phụ Kiện Điện Thoại', 'ádfadsf', '2026-06-19 08:43:18', '2026-06-19 08:43:40');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `coupons`
--

CREATE TABLE `coupons` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_type` enum('percent','fixed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_value` decimal(15,2) NOT NULL,
  `min_order_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `usage_limit` int NOT NULL DEFAULT '0',
  `used_count` int NOT NULL DEFAULT '0',
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `imeis`
--

CREATE TABLE `imeis` (
  `id` bigint UNSIGNED NOT NULL,
  `product_variant_id` bigint UNSIGNED NOT NULL,
  `imei` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('available','sold','warranty','returned') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `imeis`
--

INSERT INTO `imeis` (`id`, `product_variant_id`, `imei`, `status`, `created_at`, `updated_at`) VALUES
(4, 2, '741852963789654', 'available', '2026-06-16 19:37:07', '2026-06-20 08:08:01'),
(9, 1, '13131654613156', 'available', '2026-06-18 04:27:48', '2026-06-19 08:59:23'),
(10, 1, '222222222222222', 'available', '2026-06-18 04:27:48', '2026-06-18 04:27:48'),
(22, 2, '123456789987654', 'available', '2026-06-21 20:16:55', '2026-06-21 20:16:55'),
(23, 1, '147852369845321', 'available', '2026-06-21 20:17:35', '2026-06-21 20:17:35'),
(24, 2, '852169874563217', 'available', '2026-06-21 20:19:12', '2026-06-21 20:19:12'),
(28, 2, '159753973148239', 'available', '2026-06-22 18:33:13', '2026-06-22 18:33:13'),
(37, 2, '124579865421365', 'available', '2026-06-23 15:28:37', '2026-06-23 15:28:37'),
(38, 24, '876531497500148', 'available', '2026-06-24 03:22:12', '2026-06-24 03:22:12'),
(39, 24, '876531497500145', 'available', '2026-06-24 03:22:12', '2026-06-24 03:22:12'),
(40, 24, '876531497500146', 'available', '2026-06-24 03:22:12', '2026-06-24 03:22:12'),
(41, 25, '753698741254789', 'available', '2026-06-24 03:22:48', '2026-06-24 03:22:48'),
(42, 25, '753698741254764', 'available', '2026-06-24 03:22:48', '2026-06-24 03:22:48'),
(43, 25, '753698741254710', 'available', '2026-06-24 03:22:48', '2026-06-24 03:22:48'),
(44, 1, '123698756234567', 'available', '2026-06-24 03:40:55', '2026-06-24 03:40:55'),
(45, 1, '123698756234563', 'available', '2026-06-24 03:40:55', '2026-06-24 03:40:55'),
(46, 1, '741852963147563', 'available', '2026-06-24 04:47:38', '2026-06-24 04:47:38'),
(47, 1, '741852963147562', 'available', '2026-06-24 04:47:38', '2026-06-24 04:47:38');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `inventory_transactions`
--

CREATE TABLE `inventory_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `product_variant_id` bigint UNSIGNED NOT NULL,
  `quantity` int NOT NULL,
  `type` enum('import','export','return','adjustment') COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `inventory_transactions`
--

INSERT INTO `inventory_transactions` (`id`, `product_variant_id`, `quantity`, `type`, `note`, `created_at`, `updated_at`) VALUES
(21, 5, 2, 'import', 'Nhập kho phụ kiện cho Sạc nhanh Anker PowerPort 65W (Màu: Đen / Dung lượng: 65W)', '2026-06-24 03:42:46', '2026-06-24 03:42:46'),
(22, 1, 2, 'import', 'Nhập IMEI cho iPhone 15 Pro (Màu: Black / Dung lượng: 256GB): 741852963147563, 741852963147562', '2026-06-24 04:47:38', '2026-06-24 04:47:38'),
(23, 16, 2, 'import', 'thêm 2', '2026-06-25 02:58:27', '2026-06-25 02:58:27');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_06_09_112331_add_customer_fields_to_users_table', 1),
(5, '2026_06_09_112626_create_categories_table', 1),
(6, '2026_06_09_112714_create_brands_table', 1),
(7, '2026_06_09_112814_create_products_table', 1),
(8, '2026_06_09_113027_create_product_images_table', 1),
(9, '2026_06_09_113100_create_reviews_table', 1),
(10, '2026_06_09_114410_create_carts_table', 1),
(11, '2026_06_09_114441_create_cart_items_table', 1),
(12, '2026_06_09_114752_create_orders_table', 1),
(13, '2026_06_09_114832_create_order_items_table', 1),
(14, '2026_06_09_115130_create_coupons_table', 1),
(15, '2026_06_09_115203_create_banners_table', 1),
(16, '2026_06_09_134907_create_product_variants_table', 1),
(17, '2026_06_09_135412_add_product_variant_id_to_cart_items_table', 1),
(18, '2026_06_09_135637_add_product_variant_id_to_order_items_table', 1),
(19, '2026_06_09_153303_create_imeis_table', 2),
(20, '2026_06_09_154155_create_inventory_transactions_table', 2),
(21, '2026_06_09_154221_create_payments_table', 2),
(22, '2026_06_09_154239_create_shipments_table', 2),
(23, '2026_06_09_154309_create_warranties_table', 2),
(24, '2026_06_09_154332_create_point_histories_table', 2);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `order_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `membership_discount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `coupon_discount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` enum('pending','processing','shipping','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_code`, `customer_name`, `customer_phone`, `shipping_address`, `subtotal`, `membership_discount`, `coupon_discount`, `total_amount`, `status`, `created_at`, `updated_at`) VALUES
(4, 8, 'ORD_TEST_SHIPMENT_001', 'Khách hàng test', '0987654321', 'Số 1 Cầu Giấy, Hà Nội', 30000000.00, 0.00, 0.00, 30000000.00, 'shipping', '2026-06-18 10:57:12', '2026-06-18 11:04:15'),
(5, 9, 'HSRN5XYWZQ', 'nguoidung', '0987543211', 'Phế Phẩm Hà Nội', 550.00, 0.00, 0.00, 550.00, 'completed', '2026-06-19 09:05:41', '2026-06-19 09:12:23'),
(8, 8, 'ORD_TEST_SHIPMENT_003', 'Khách hàng test', '0987654321', 'Số 1 Cầu Giấy, Hà Nội', 30000000.00, 0.00, 0.00, 30000000.00, 'pending', '2026-06-25 02:19:12', '2026-06-25 11:04:15');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `product_variant_id` bigint UNSIGNED DEFAULT NULL,
  `price` decimal(15,2) NOT NULL,
  `quantity` int NOT NULL,
  `total` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_variant_id`, `price`, `quantity`, `total`, `created_at`, `updated_at`) VALUES
(1, 4, 1, 1, 30000000.00, 1, 30000000.00, '2026-06-18 10:57:12', '2026-06-18 10:57:12'),
(2, 5, 4, NULL, 550.00, 1, 550.00, '2026-06-19 09:05:41', '2026-06-19 09:05:41'),
(4, 4, 60, 25, 30000000.00, 1, 30000000.00, '2026-06-25 02:57:12', '2026-06-25 10:57:12');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `payments`
--

CREATE TABLE `payments` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `payment_method` enum('cod','momo','vnpay','zalopay') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_status` enum('pending','paid','failed','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `transaction_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `amount`, `payment_status`, `transaction_code`, `paid_at`, `created_at`, `updated_at`) VALUES
(1, 4, 'cod', 30000000.00, 'pending', NULL, NULL, '2026-06-18 10:57:12', '2026-06-18 10:57:12'),
(2, 5, 'cod', 550.00, 'pending', NULL, NULL, '2026-06-19 09:05:41', '2026-06-19 09:05:41');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `point_histories`
--

CREATE TABLE `point_histories` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `points` int NOT NULL,
  `type` enum('earn','redeem') COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `brand_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `stock_quantity` int NOT NULL DEFAULT '0',
  `thumbnail` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_type` enum('imei/serial','quantity') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `category_id`, `brand_id`, `name`, `slug`, `description`, `price`, `stock_quantity`, `thumbnail`, `product_type`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'iPhone 15 Pro', 'iphone-15-pro', 'iPhone 15 Pro 256GB', 30000000.00, 0, NULL, 'imei/serial', 1, '2026-06-17 02:23:21', '2026-06-17 02:23:21'),
(4, 1, 1, 'iphone é', 'iphone-e', 'như cái con cá', 550.00, 10, 'products/thumbnails/sqW9TO9FWttwBfWWhmwvpZPLsLH9F9Lae8KiObNq.jpg', 'imei/serial', 1, '2026-06-18 09:40:28', '2026-06-19 09:29:14'),
(23, 4, 14, 'Sạc nhanh Anker PowerPort 65W', 'sac-nhanh-anker-powerport-65w', 'Sạc nhanh công suất 65W, hỗ trợ nhiều giao thức sạc thông minh.', 890000.00, 50, '/storage/accessories/anker-charger.jpg', 'quantity', 1, '2026-06-23 03:34:51', '2026-06-23 03:34:51'),
(24, 4, 18, 'Sạc nhanh Baseus 100W', 'sac-nhanh-baseus-100w', 'Sạc Baseus công suất 100W, thiết kế nhỏ gọn, tương thích đa thiết bị.', 950000.00, 40, '/storage/accessories/baseus-charger.jpg', 'quantity', 1, '2026-06-23 03:34:51', '2026-06-23 03:34:51'),
(29, 4, 16, 'Ốp lưng Spigen Tough Armor', 'op-lung-spigen-tough-armor', 'Ốp lưng chống sốc 2 lớp, bảo vệ tốt cho điện thoại.', 250000.00, 60, '/storage/accessories/spigen-case.jpg', 'quantity', 1, '2026-06-23 03:34:51', '2026-06-23 03:34:51'),
(30, 4, 20, 'Ốp lưng Ringke Fusion', 'op-lung-ringke-fusion', 'Ốp lưng trong suốt, chống va đập, giữ nguyên thiết kế máy.', 280000.00, 50, '/storage/accessories/ringke-case.jpg', 'quantity', 1, '2026-06-23 03:34:51', '2026-06-23 03:34:51'),
(34, 4, 21, 'Pin sạc Mophie 20000mAh', 'pin-sac-mophie-20000mah', 'Pin dự phòng 20000mAh, sạc nhanh, chống quá nhiệt.', 1200000.00, 25, '/storage/accessories/mophie-power.jpg', 'quantity', 1, '2026-06-23 03:34:51', '2026-06-23 03:34:51'),
(60, 1, 1, 'Iphone 18 Pro Max', 'iphone-18-pro-max', 'Điện thoại iphone 18 Pro Max', 39900000.00, 20, 'products/thumbnails/g7cKE5Ims83Zc8wcXdr47K1q2pekic4836BFDjLm.jpg', 'imei/serial', 1, '2026-06-24 03:21:06', '2026-06-24 03:21:06');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_images`
--

CREATE TABLE `product_images` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `created_at`, `updated_at`) VALUES
(3, 4, 'products/images/gYGOPKHdsmRJYM2AcHeTt5VpAWXKR0qU0HihorIw.jpg', '2026-06-19 09:29:14', '2026-06-19 09:29:14'),
(4, 4, 'products/images/guclxH3IMUpvRG28Io73UWB5mUOnS925OYoLM1Is.jpg', '2026-06-19 09:29:14', '2026-06-19 09:29:14'),
(6, 60, 'products/images/9U4v42RkeUP8mg1jkmQ2dhAMw2ZsT5vMV8cJWaDV.webp', '2026-06-24 03:21:06', '2026-06-24 03:21:06'),
(7, 60, 'products/images/HGlV44dmH3zbv6CRpXoGOgNuCfOWRICzcKDM39V2.jpg', '2026-06-24 03:21:06', '2026-06-24 03:21:06'),
(8, 60, 'products/images/vvmZjC8QCR5VGsvk33WpkCok7vYyFLviThkR3Qqo.jpg', '2026-06-24 03:21:06', '2026-06-24 03:21:06'),
(9, 60, 'products/images/vAFD4cQ0436B2o76TZE1IaZYO7kCcNhbN4VsQx3A.webp', '2026-06-24 03:21:06', '2026-06-24 03:21:06');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_variants`
--

CREATE TABLE `product_variants` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `color` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `storage` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stock_quantity` int NOT NULL DEFAULT '0',
  `additional_price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `color`, `storage`, `stock_quantity`, `additional_price`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Black', '256GB', 0, 0.00, 1, '2026-06-17 02:23:28', '2026-06-17 02:23:28'),
(2, 4, 'hồng cánh sen', '128gb', 12, 550.00, 1, '2026-06-18 09:40:28', '2026-06-18 09:40:28'),
(5, 23, 'Đen', '65W', 53, 0.00, 1, '2026-06-23 03:34:52', '2026-06-24 03:42:46'),
(6, 24, 'Đen', '100W', 40, 0.00, 1, '2026-06-23 03:34:52', '2026-06-23 03:34:52'),
(11, 29, 'Đen', 'Black', 60, 0.00, 1, '2026-06-23 03:34:52', '2026-06-23 03:34:52'),
(12, 30, 'Trong suốt', 'Black', 50, 0.00, 1, '2026-06-23 03:34:52', '2026-06-23 03:34:52'),
(16, 34, 'Đen', '20000mAh', 27, 0.00, 1, '2026-06-23 03:34:52', '2026-06-25 02:58:27'),
(24, 60, 'Đỏ', '1tb', 10, 39900000.00, 1, '2026-06-24 03:21:06', '2026-06-24 03:21:06'),
(25, 60, 'Vàng', '518gb', 10, 35000000.00, 1, '2026-06-24 03:21:06', '2026-06-24 03:21:06');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `rating` tinyint NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `product_id`, `rating`, `comment`, `created_at`, `updated_at`) VALUES
(3, 3, 1, 5, 'Sản phẩm rất tốt, giao hàng nhanh, đóng gói cẩn thận.', '2026-06-19 16:21:42', '2026-06-19 16:21:42'),
(4, 2, 1, 4, 'Máy chạy ổn định, pin tốt nhưng giao diện cần cải thiện hơn.', '2026-06-19 16:21:42', '2026-06-19 16:21:42');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `shipments`
--

CREATE TABLE `shipments` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `shipping_unit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tracking_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_status` enum('pending','shipping','delivered','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `shipped_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `shipments`
--

INSERT INTO `shipments` (`id`, `order_id`, `shipping_unit`, `tracking_code`, `shipping_status`, `shipped_at`, `delivered_at`, `created_at`, `updated_at`) VALUES
(1, 4, 'shopeeee lo7', '2665203', 'pending', '2026-06-18 11:04:07', '2026-06-18 11:04:12', '2026-06-18 10:58:21', '2026-06-18 11:04:15'),
(2, 5, 'ghtk', '2665203', 'delivered', '2026-06-19 09:12:01', '2026-06-19 09:12:23', '2026-06-19 09:11:54', '2026-06-19 09:12:23');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','customer') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `total_spent` decimal(15,2) NOT NULL DEFAULT '0.00',
  `membership_level` enum('bronze','silver','gold') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bronze'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `address`, `email_verified_at`, `password`, `role`, `remember_token`, `created_at`, `updated_at`, `total_spent`, `membership_level`) VALUES
(2, 'Nguyễn Văn A', 'a@gmail.com', '0900000002', 'Hải Phòng', '2026-06-18 16:49:16', '$2y$12$abcdefghijklmnopqrstuv', 'customer', NULL, '2026-06-18 16:49:16', '2026-06-18 16:49:16', 12000000.00, 'gold'),
(3, 'Trần Thị B', 'b@gmail.com', '0900000003', 'Đà Nẵng', '2026-06-18 16:49:16', '$2y$12$abcdefghijklmnopqrstuv', 'customer', NULL, '2026-06-18 16:49:16', '2026-06-18 16:49:16', 6000000.00, 'silver'),
(4, 'Lê Văn C', 'c@gmail.com', '0900000004', 'TP.HCM', '2026-06-18 16:49:16', '$2y$12$abcdefghijklmnopqrstuv', 'customer', NULL, '2026-06-18 16:49:16', '2026-06-18 16:49:16', 2500000.00, 'bronze'),
(7, 'Admin', 'admin@gmail.com', NULL, NULL, NULL, '$2y$12$WR/h4EMesTXZXXgAljoSmOObqiOGlGIKnTrWCtW1L0S5Nw44l7nSe', 'admin', NULL, '2026-06-18 09:53:19', '2026-06-18 09:55:00', 0.00, 'bronze'),
(8, 'Khách hàng test', 'customer.test@gmail.com', '0987654321', 'Số 1 Cầu Giấy, Hà Nội', NULL, '$2y$12$gT3ARuHqNaf1wi7MAk0syusQOBdoB5XoJMhwLub/5BOIynJvhfxJy', 'customer', NULL, '2026-06-18 10:57:12', '2026-06-18 10:57:12', 0.00, 'bronze'),
(9, 'nguoidung', 'abc@gmail.com', NULL, NULL, NULL, '$2y$12$EuDnl8HwRrby.WABYRmx9u/jjDeaiNfK/U9lFv/.qxLmT9q/jMzrW', 'customer', NULL, '2026-06-19 09:04:05', '2026-06-19 09:04:05', 0.00, 'bronze');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `warranties`
--

CREATE TABLE `warranties` (
  `id` bigint UNSIGNED NOT NULL,
  `imei_id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `warranty_start` date NOT NULL,
  `warranty_end` date NOT NULL,
  `status` enum('active','expired','claimed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `warranties`
--

INSERT INTO `warranties` (`id`, `imei_id`, `order_id`, `warranty_start`, `warranty_end`, `status`, `created_at`, `updated_at`) VALUES
(1, 4, 4, '2026-06-18', '2027-06-18', 'active', '2026-06-18 11:01:42', '2026-06-18 11:01:42');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Chỉ mục cho bảng `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Chỉ mục cho bảng `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `carts_user_id_foreign` (`user_id`);

--
-- Chỉ mục cho bảng `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_items_cart_id_foreign` (`cart_id`),
  ADD KEY `cart_items_product_id_foreign` (`product_id`),
  ADD KEY `cart_items_product_variant_id_foreign` (`product_variant_id`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupons_code_unique` (`code`);

--
-- Chỉ mục cho bảng `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Chỉ mục cho bảng `imeis`
--
ALTER TABLE `imeis`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `imeis_imei_unique` (`imei`),
  ADD KEY `imeis_product_variant_id_foreign` (`product_variant_id`);

--
-- Chỉ mục cho bảng `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventory_transactions_product_variant_id_foreign` (`product_variant_id`);

--
-- Chỉ mục cho bảng `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Chỉ mục cho bảng `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_order_code_unique` (`order_code`),
  ADD KEY `orders_user_id_foreign` (`user_id`);

--
-- Chỉ mục cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_foreign` (`order_id`),
  ADD KEY `order_items_product_id_foreign` (`product_id`),
  ADD KEY `order_items_product_variant_id_foreign` (`product_variant_id`);

--
-- Chỉ mục cho bảng `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Chỉ mục cho bảng `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payments_order_id_foreign` (`order_id`);

--
-- Chỉ mục cho bảng `point_histories`
--
ALTER TABLE `point_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `point_histories_user_id_foreign` (`user_id`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_slug_unique` (`slug`),
  ADD KEY `products_category_id_foreign` (`category_id`),
  ADD KEY `products_brand_id_foreign` (`brand_id`);

--
-- Chỉ mục cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_images_product_id_foreign` (`product_id`);

--
-- Chỉ mục cho bảng `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_variants_product_id_foreign` (`product_id`);

--
-- Chỉ mục cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reviews_user_id_foreign` (`user_id`),
  ADD KEY `reviews_product_id_foreign` (`product_id`);

--
-- Chỉ mục cho bảng `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Chỉ mục cho bảng `shipments`
--
ALTER TABLE `shipments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shipments_order_id_foreign` (`order_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Chỉ mục cho bảng `warranties`
--
ALTER TABLE `warranties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `warranties_imei_id_foreign` (`imei_id`),
  ADD KEY `warranties_order_id_foreign` (`order_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `banners`
--
ALTER TABLE `banners`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `brands`
--
ALTER TABLE `brands`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT cho bảng `carts`
--
ALTER TABLE `carts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `imeis`
--
ALTER TABLE `imeis`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT cho bảng `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT cho bảng `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `point_histories`
--
ALTER TABLE `point_histories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT cho bảng `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT cho bảng `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `shipments`
--
ALTER TABLE `shipments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `warranties`
--
ALTER TABLE `warranties`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Ràng buộc đối với các bảng kết xuất
--

--
-- Ràng buộc cho bảng `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ràng buộc cho bảng `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL;

--
-- Ràng buộc cho bảng `imeis`
--
ALTER TABLE `imeis`
  ADD CONSTRAINT `imeis_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Ràng buộc cho bảng `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD CONSTRAINT `inventory_transactions_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ràng buộc cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL;

--
-- Ràng buộc cho bảng `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Ràng buộc cho bảng `point_histories`
--
ALTER TABLE `point_histories`
  ADD CONSTRAINT `point_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Ràng buộc cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ràng buộc cho bảng `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ràng buộc cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ràng buộc cho bảng `shipments`
--
ALTER TABLE `shipments`
  ADD CONSTRAINT `shipments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Ràng buộc cho bảng `warranties`
--
ALTER TABLE `warranties`
  ADD CONSTRAINT `warranties_imei_id_foreign` FOREIGN KEY (`imei_id`) REFERENCES `imeis` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `warranties_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
