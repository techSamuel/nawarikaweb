<?php
// --- PHASE 1: SERVER-SIDE DATA FETCHING ---

// Note: Ensure this path is correct. If 'api' is a folder, this is right.
// If db_connect.php is in the same folder as index.php, remove 'api/'.
require 'api/db_connect.php';

// Helper function to decode JSON fields
function decodeJsonFields(&$row, $fields)
{
    foreach ($fields as $field) {
        if (isset($row[$field]) && is_string($row[$field])) {
            $decoded = json_decode($row[$field], true);
            $row[$field] = ($decoded !== null) ? $decoded : [];
        } else {
            $row[$field] = [];
        }
    }
}

// Helper function to render product lists as static HTML
function renderList($listString, $titleId, $ulId, $subtitleId)
{
    if (empty($listString) || !is_string($listString)) {
        return; // Don't output anything if no data
    }

    $lines = explode("\n", trim($listString));
    $title = htmlspecialchars(array_shift($lines)); // Get first line as title
    $items = array_filter($lines, 'trim'); // Get remaining lines as items

    echo "<h4 id='$titleId' class='font-semibold text-lg mt-6 mb-2 text-green-700'>$title</h4>";

    if (!empty($items)) {
        echo "<p id='$subtitleId' class='mb-2 text-gray-600'>এই প্যাকেজে সবগুলো প্রয়োজনীয় দোয়া রয়েছে:</p>"; // Subtitle is visible by default
        echo "<ul id='$ulId' class='list-disc list-inside space-y-1 text-gray-600'>";
        foreach ($items as $item) {
            echo "<li>" . htmlspecialchars($item) . "</li>";
        }
        echo "</ul>";
    }
}

// Initialize default variables
$product = [
    'title' => 'ইসলামিক দোয়া স্টিকার কার্ড',
    'imageUrls' => [],
    'videoUrls' => []
];
$settings = [];
$allOrders = [];
$ssr_data = []; // This will hold all data for JavaScript

// --- Fetch Product ---
$product_result = $conn->query("SELECT title, imageUrls, videoUrls FROM products ORDER BY createdAt DESC LIMIT 1");
if ($product_result && $product_result->num_rows > 0) {
    $product = $product_result->fetch_assoc();
    decodeJsonFields($product, ['imageUrls', 'videoUrls']);
}

// --- Fetch Settings ---
$settings_result = $conn->query("SELECT setting_key, setting_value FROM settings");
if ($settings_result) {
    while ($row = $settings_result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    if (isset($settings['feedbackImageUrls'])) {
        $decoded_urls = json_decode($settings['feedbackImageUrls'], true);
        $settings['feedbackImageUrls'] = ($decoded_urls !== null) ? $decoded_urls : [];
    }
}

// --- Fetch Recent Orders ---
$orders_result = $conn->query("SELECT name, address, phone, product, timestamp FROM orders WHERE status != 'Canceled' ORDER BY timestamp DESC LIMIT 100");
if ($orders_result) {
    while ($row = $orders_result->fetch_assoc()) {
        $allOrders[] = $row;
    }
}

// --- Assign data for JS hydration ---
// Only pass what the JS actually needs
$ssr_data = [
    'product' => $product, // For sliders
    'settings' => $settings, // For prices, links, rewards, etc.
    'allOrders' => $allOrders // For recent orders slider
];

// --- Assign key variables for static HTML rendering ---
$productTitle = htmlspecialchars($product['title'] ?? 'ইসলামিক দোয়া স্টিকার কার্ড');
$mainDescription = htmlspecialchars($settings['mainDescription'] ?? 'আমাদের এই আকর্ষণীয় ডিজাইনের স্টিকার কার্ডগুলো...');
$pageTitle = $productTitle . ' কিনুন | Nawarika Shop';
$pageDescription = $settings['metaDescription'] ?? 'ঘরে বসেই কিনুন আকর্ষণীয় ইসলামিক দোয়া স্টিকার কার্ড। আপনার ঘরকে রহমত ও বরকতে ভরিয়ে তুলুন। ৫৬ পিস ও ৩২ পিস প্যাকেজে পাওয়া যাচ্ছে। ক্যাশ অন ডেলিভারি।'; // Use a dedicated meta field if possible
$ogImage = 'https://duya.nawarika.shop/images/featured-product-image.png';
$canonicalUrl = "https://duya.nawarika.shop/";

// --- Calculate Initial Price (Default: small, 32 pcs) ---
$priceSmall32 = (int) ($settings['priceSmall32'] ?? 200);
$deliveryFee = isset($settings['deliveryCharge']) ? (int) $settings['deliveryCharge'] : 0;
$totalPrice = $priceSmall32 + $deliveryFee;

$conn->close();
?>
<!DOCTYPE html>
<html lang="bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- SEO Meta Tags - Now dynamically populated by PHP -->
    <title><?php echo $pageTitle; ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta name="keywords"
        content="ইসলামিক দোয়া স্টিকার, দোয়া স্টিকার কার্ড, ইসলামিক স্টিকার, Nawarika, Buy dua sticker card, Islamic sticker Bangladesh, দোয়া কার্ড">

    <meta property="og:title" content="<?php echo $pageTitle; ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $canonicalUrl; ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($ogImage); ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $pageTitle; ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($ogImage); ?>">
    <meta name="facebook-domain-verification" content="v9bf0ho8r1b2f1cus3kdp8j0izs4lj" />

    <link rel="canonical" href="<?php echo $canonicalUrl; ?>">

    <!-- Schema - Now dynamically populated by PHP -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org/",
      "@type": "Product",
      "name": "<?php echo $productTitle; ?>",
      "description": "<?php echo htmlspecialchars($pageDescription); ?>",
      "brand": {
        "@type": "Brand",
        "name": "Nawarika"
      },
      "image": "<?php echo htmlspecialchars($ogImage); ?>",
      "offers": {
        "@type": "AggregateOffer",
        "priceCurrency": "BDT",
        "lowPrice": "<?php echo (int) ($settings['priceSmall32'] ?? 200); ?>",
        "highPrice": "<?php echo (int) ($settings['priceLarge56'] ?? 400); ?>",
        "offerCount": "4",
        "availability": "https://schema.org/InStock"
      },
      "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "<?php echo $settings['aggregateRatingValue'] ?? '4.8'; ?>",
        "reviewCount": "<?php echo $settings['aggregateRatingCount'] ?? '483'; ?>"
      }
    }
    </script>
    <link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png">
    <link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png">

    <!-- GTM -->
    <script>(function (w, d, s, l, i) {
            w[l] = w[l] || []; w[l].push({
                'gtm.start':
                    new Date().getTime(), event: 'gtm.js'
            }); var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : ''; j.async = true; j.src =
                    'https://www.googletagmanager.com/gtm.js?id=' + i + dl; f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-MXKPWCPH');</script>

    <!-- Font Preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- 
      OPTIMIZATION FIX:
      - Removed the slow Tailwind CDN <script> tag.
      - Added link to your built 'output.css' file. You MUST generate this file.
      - Added link to 'custom-styles.css' which now contains your inline styles.
    -->
    <link rel="stylesheet" href="assets/css/output.css">
    <link rel="stylesheet" href="assets/css/custom-styles.css">
</head>

<body class="bg-gray-50 text-gray-800">
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MXKPWCPH" height="0" width="0"
            style="display:none;visibility:hidden"></iframe></noscript>

    <div class="container mx-auto p-4 md:p-8 max-w-4xl">

        <header class="text-center mb-8">
            <!-- SEO FIX: H1 tag is now rendered by PHP with the real product title -->
            <h1 id="productTitle" class="text-3xl md:text-4xl font-bold text-green-800">
                <?php echo $productTitle; ?>
            </h1>
            <p class="text-lg md:text-xl mt-2 text-gray-600">ইসলামিক দোয়া স্টিকার কার্ডের সাথে প্রতিদিন আল্লাহকে স্মরণ
                করুন।</p>
        </header>

        <main class="grid grid-cols-1 md:grid-cols-2 gap-8">

            <div class="space-y-6">
                <!-- These containers are now empty, JS will "hydrate" them -->
                <div id="imageSlider" class="slider-container shadow-lg"></div>
                <div id="imageDots" class="dots-container"></div>
                <div id="slider-variation-buttons" class="flex justify-center gap-2 mt-4 mb-6">
                    <button type="button" id="btn-slider-32" class="slider-var-btn bg-green-600 text-white px-4 py-2 rounded-md transition font-semibold">৩২ পিস</button>
                    <button type="button" id="btn-slider-56" class="slider-var-btn bg-gray-200 text-gray-800 hover:bg-gray-300 px-4 py-2 rounded-md transition font-semibold">৫৬ পিস</button>
                    <button type="button" id="btn-slider-99" class="slider-var-btn bg-gray-200 text-gray-800 hover:bg-gray-300 px-4 py-2 rounded-md transition font-semibold">৯৯ টি নাম</button>
                </div>
                <div id="videoSlider" class="slider-container shadow-lg aspect-9-16"></div>
                <div id="videoDots" class="dots-container"></div>

                <!-- Spin container visibility is controlled by PHP -->
                <div id="spin-container" <?php if (($settings['isSpinningWheelEnabled'] ?? '1') === '0')
                    echo 'style="display:none;"'; ?>>
                    <h3 class="text-2xl font-bold text-gray-800">একটি ফ্রি স্পিন জিতে নিন!</h3>
                    <p class="text-gray-600 mb-2">সম্পূর্ণ ক্যাশ অন ডেলিভারী। নিচের ফর্ম এ শুধু নাম ঠিকানা ও ফোন নাম্বার
                        এড করে অর্ডার করুন এবং জিতে নিন আকর্ষণীয় পুরস্কার!</p>
                    <div class="wheel-wrapper">
                        <div class="pointer"></div>
                        <div id="wheel" class="wheel"></div>
                    </div>
                    <p id="spin-activation-msg" class="text-red-600 font-semibold my-2">অর্ডার করার পর স্পিন বাটনটি চালু
                        হবে।</p>
                    <button id="spin-btn" disabled>Spin for a Reward!</button>
                    <div id="spin-result"></div>
                    <div class="mt-4 text-sm text-gray-500">
                        <p>প্রতিটি অর্ডারের সাথে থাকছে একটি নিশ্চিত পুরস্কার জেতার সুযোগ।</p>
                        <p>আপনার জেতা পুরস্কারটি আপনার অর্ডারের সাথে আপনাকে পাঠানো হবে</p>
                    </div>
                </div>

                <!-- Feedback section -->
                <div id="customer-feedback-section" class="mt-8 space-y-6 hidden">
                    <h2 id="feedback-title" class="text-3xl font-bold text-center text-green-800"></h2>
                    <div id="feedback-slider-container" class="slider-container shadow-lg"></div>
                    <div id="feedback-dots-container" class="dots-container"></div>
                    <p id="feedback-description" class="text-gray-600 text-center px-4"></p>
                </div>

                <!-- Recent Orders section -->
                <section id="recent-orders-section"
                    class="mt-12 p-6 bg-white rounded-lg shadow-lg max-w-4xl mx-auto hidden">
                    <h2 class="text-3xl font-bold text-center text-green-800 mb-2">🌸 আমাদের সম্মানিত দোয়া স্টিকার
                        ক্রেতাদের তালিকা</h2>
                    <p class="text-center text-gray-600 mb-6">এরকম হাজারো পরিবার দেশের প্রতিটি বিভাগ ও জেলার মানুষ
                        আমাদের পণ্যের উপর আস্থা রাখছেন...</p>
                    <div id="recent-orders-slider" class="slider-container"></div>
                    <div id="recent-orders-dots" class="dots-container"></div>
                </section>
            </div>

            <div id="order-section" class="bg-white p-6 rounded-lg shadow-lg">
                <h2 class="text-2xl font-bold mb-4 text-green-700">কেন আমাদের দোয়া স্টিকার কিনবেন?</h2>

                <!-- SEO FIX: Product description and lists are now rendered by PHP -->
                <div id="productDescription" class="text-gray-700 space-y-3">
                    <p id="main-description" class="mb-4">
                        <?php echo $mainDescription; ?>
                    </p>

                    <div id="package-56-details">
                        <?php renderList($settings['package56List'] ?? '', 'package-56-title', 'package-56-ul', 'package-56-subtitle'); ?>
                    </div>

                    <div id="package-32-details" class="hidden">
                        <?php renderList($settings['package32List'] ?? '', 'package-32-title', 'package-32-ul', 'package-32-subtitle'); ?>
                    </div>

                    <div id="package-99-details" class="hidden">
                        <?php renderList($settings['package99List'] ?? '', 'package-99-title', 'package-99-ul', 'package-99-subtitle'); ?>
                    </div>
                </div>

                <!-- Product Options -->
                <div id="productOptions" class="my-6 space-y-4">
                    <div>
                        <h3 class="font-semibold mb-2">সাইজ সিলেক্ট করুন:</h3>
                        <div class="flex gap-4">
                            <!-- PHP can hide options based on settings -->
                            <input type="radio" name="size" id="size_small" value="small" class="hidden" <?php if (($settings['isSmallSizeEnabled'] ?? '1') === '0')
                                echo 'disabled';
                            else
                                echo 'checked'; ?>>
                            <label for="size_small" class="radio-label text-center w-full" <?php if (($settings['isSmallSizeEnabled'] ?? '1') === '0')
                                echo 'style="display:none;"'; ?>>
                                <?php echo htmlspecialchars($settings['smallSizeLabel'] ?? 'ছোট'); ?>
                            </label>
                            <input type="radio" name="size" id="size_big" value="big" class="hidden" <?php if (($settings['isBigSizeEnabled'] ?? '1') === '0')
                                echo 'disabled';
                            if (($settings['isSmallSizeEnabled'] ?? '1') === '0')
                                echo 'checked'; ?>>
                            <label for="size_big" class="radio-label text-center w-full" <?php if (($settings['isBigSizeEnabled'] ?? '1') === '0')
                                echo 'style="display:none;"'; ?>>
                                <?php echo htmlspecialchars($settings['bigSizeLabel'] ?? 'বড়'); ?>
                            </label>
                        </div>
                        <div class="mt-4">
                            <img id="size-image-preview" src="" alt="Selected size preview"
                                class="w-full rounded-lg shadow-md hidden">
                        </div>
                    </div>
                    <div>
                        <h3 class="font-semibold mb-2">পরিমাণ:</h3>
                        <div class="flex gap-4">
                            <input type="radio" name="quantity" id="quantity_32" value="32" class="hidden" checked>
                            <label for="quantity_32" class="radio-label text-center w-full">৩২ পিস</label>
                            <input type="radio" name="quantity" id="quantity_56" value="56" class="hidden">
                            <label for="quantity_56" class="radio-label text-center w-full">৫৬ পিস</label>
                            <input type="radio" name="quantity" id="quantity_99" value="99" class="hidden">
                            <label for="quantity_99" class="radio-label text-center w-full">৯৯ টি নাম</label>
                        </div>
                    </div>
                </div>

                <!-- Price section - Populated with default data by PHP -->
                <div class="my-6 text-center bg-gray-100 p-4 rounded-lg space-y-2">
                    <p class="text-lg text-gray-800">দাম: <span id="dynamicPrice"
                            class="font-semibold"><?php echo $priceSmall32; ?></span> টাকা</p>
                    <p id="deliveryFeeRow" class="text-lg text-gray-800 flex flex-col items-center gap-1">
                        <?php if ($deliveryFee === 0): ?>
                            <span class="font-semibold text-green-600">🚚 সারা বাংলাদেশে ফ্রি হোম ডেলিভারি</span>
                            <span
                                class="font-bold text-red-600 bg-yellow-200 px-3 py-1 rounded-full text-sm inline-block shadow-sm">🎁
                                ফ্রি ওমরাহ কুপন</span>
                            <span id="deliveryFee" class="hidden">0</span>
                        <?php else: ?>
                            ডেলিভারি চার্জ: <span id="deliveryFee" class="font-semibold"><?php echo $deliveryFee; ?></span>
                            টাকা
                        <?php endif; ?>
                    </p>
                    <hr class="border-gray-300">
                    <p class="text-2xl font-bold text-red-600">সর্বমোট: <span
                            id="totalPrice"><?php echo $totalPrice; ?></span> টাকা</p>
                </div>

                <!-- Order Form - Populated with default data by PHP -->
                <form id="orderForm" class="space-y-4">
                    <input type="hidden" id="selectedProduct" name="selectedProduct"
                        value="<?php echo htmlspecialchars($settings['smallSizeLabel'] ?? 'ছোট'); ?> - ৩২ পিস">
                    <input type="hidden" id="productPriceInput" name="productPriceInput"
                        value="<?php echo $priceSmall32; ?>">
                    <input type="hidden" id="totalPriceInput" name="totalPriceInput" value="<?php echo $totalPrice; ?>">

                    <h3 class="text-xl font-semibold text-center bg-gray-100 p-2 rounded">অর্ডার করতে নিচের ফর্মটি পূরণ
                        করুন</h3>
                    <div>
                        <label for="name" class="block mb-1 font-medium">আপনার নাম <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name"
                            class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500"
                            required>
                    </div>
                    <div class="relative">
                        <label for="address" class="block mb-1 font-medium">আপনার সম্পূর্ণ ঠিকানা <span
                                class="text-red-500">*</span></label>
                        <textarea id="address" name="address" rows="3"
                            class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500"
                            placeholder="বাসা/হোল্ডিং, রোড, এলাকা, থানা, জেলা" required></textarea>

                        <div id="addressSuggestions"
                            class="hidden absolute left-0 right-0 z-10 bg-white border border-gray-300 rounded-b-md max-h-60 overflow-y-auto shadow-lg">
                        </div>
                    </div>
                    <div>
                        <label for="phone" class="block mb-1 font-medium">আপনার ফোন নম্বর <span
                                class="text-red-500">*</span></label>
                        <input type="tel" id="phone" name="phone"
                            class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500"
                            required>
                    </div>
                    <div id="optional-field-container">
                        <label for="optional-note" class="block mb-1 font-medium">বিশেষ দ্রষ্টব্য (Optional)</label>
                        <textarea id="optional-note" name="note" rows="2"
                            class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500"></textarea>
                    </div>
                    <button type="submit"
                        class="w-full bg-green-700 text-white font-bold py-3 px-4 rounded-md hover:bg-green-800 transition duration-300">
                        অর্ডার কনফার্ম করুন
                    </button>
                </form>

                <!-- Alert Box -->
                <div role="alert" aria-live="polite"
                    class="max-w-xl mx-auto p-4 rounded-lg bg-gradient-to-r from-yellow-50 via-yellow-100 to-white border-l-4 border-yellow-400 shadow-sm">
                    <div class="items-start gap-4">
                        <div>
                            <p class="text-red-700 font-semibold text-lg">⚠️ সতর্কবার্তা:</p>
                            <p class="mt-1 text-gray-800 leading-relaxed">দয়া করে আল্লাহর দোয়া ও কলাম সংবলিত পণ্য
                                নিয়ে কেউ প্রতারণা বা অর্ডার দিয়ে ডেলিভারি ম্যানের ফোন রিসিভ না করে হয়রানি করবেন না।
                            </p>
                            <p class="text-red-700 font-semibold text-md">এরকম ফ্রড বা হয়রানি করলে আপনার নাম ঠিকানা,
                                ফোন নাম্বার উল্লেখ করে থানায় জিডি করা হবে</p>
                            <p class="mt-2 text-md text-green-600">🙏 যারা সত্যিই নিতে আগ্রহী, শুধুমাত্র তারাই অর্ডার
                                করুন। সম্পূর্ণ ক্যাশ অন ডেলিভারী, পার্সেল খুলে চেক করে নিবেন, আমরা যেরকম বলেছি সেরকম না
                                হলে রিটার্ন করে দিবেন</p>
                        </div>
                    </div>
                </div>

                <!-- Success Message -->
                <div id="successMessage"
                    class="hidden mt-4 p-4 bg-green-100 text-green-800 border border-green-300 rounded-md text-center">
                    <p>অভিনন্দন! আপনার অর্ডারটি আমরা পেয়েছি। খুব শীঘ্রই আমাদের প্রতিনিধি আপনাকে কল করবে।</p>
                    <p class="mt-4 font-semibold text-lg">আপনার অর্ডার নাম্বার - <strong id="orderIdDisplay"
                            class="text-red-600"></strong>
                        <button id="copyOrderIdBtn"
                            class="ml-2 px-3 py-1 text-sm bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none">কপি</button>
                    </p>
                    <p class="mt-4">আপনার এই অর্ডারের জন্য থাকছে একটি নিশ্চিত উপহার! উপরের স্পিন বাটনে ক্লিক করে চাকাটি
                        ঘুরিয়ে জিতে নিন আপনার আকর্ষণীয় পুরস্কারটি।</p>
                    <button id="scrollToSpinBtn"
                        class="w-full bg-yellow-500 text-white font-bold py-3 px-4 rounded-md hover:bg-yellow-600 transition duration-300 mt-4">
                        স্পিন করুন
                    </button>
                </div>
            </div>
        </main>


        <!-- Order Tracking -->
        <section id="order-tracking-section" class="mt-12 p-6 bg-white rounded-lg shadow-lg max-w-4xl mx-auto">
            <h3 class="text-xl font-semibold text-center mb-4 text-gray-800">আপনার অর্ডার ট্র্যাক করুন</h3>
            <p class="text-center text-gray-600 mb-6">আপনার অর্ডার সম্পর্কিত তথ্য দেখতে বা আপনার ঠিকানা পরিবর্তন করতে
                অর্ডার নাম্বার বা ফোন নাম্বার দিয়ে অনুসন্ধান করুন।</p>
            <form id="trackOrderForm" class="flex flex-col md:flex-row gap-4 items-stretch justify-center">
                <input type="text" id="trackingInput" name="trackingInput"
                    class="w-full md:w-1/2 p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                    placeholder="আপনার অর্ডার নাম্বার বা ফোন নাম্বার দিন" required>
                <button type="submit"
                    class="w-full md:w-auto bg-blue-600 text-white font-bold py-3 px-6 rounded-md hover:bg-blue-700 transition duration-300">
                    ট্র্যাক করুন
                </button>
            </form>
            <div id="trackingResult" class="mt-6 hidden">
            </div>
            <p id="trackingError" class="text-red-500 text-center mt-4 hidden"></p>
        </section>


        <footer class="text-center mt-10 pt-6 border-t">
            <h3 class="text-xl font-bold mb-2">ডেলিভারি সংক্রান্ত তথ্য</h3>
            <?php if ($deliveryFee === 0): ?>
                <p class="text-green-600 font-semibold text-lg">🚚 সারা বাংলাদেশে ফ্রি হোম ডেলিভারি</p>
            <?php else: ?>
                <p class="text-gray-600">সারা দেশে ফ্ল্যাট <strong><?php echo $deliveryFee; ?> টাকা</strong> হোম ডেলিভারি।
                </p>
            <?php endif; ?>
            <p class="text-gray-600 mt-2">যেকোনো প্রয়োজনে কল করুন: <strong
                    class="text-green-700"><?php echo htmlspecialchars($settings['supportPhone'] ?? '01884031111'); ?></strong>
            </p>
        </footer>
    </div>

    <!-- FAB Buttons - Now populated by PHP -->
    <div class="fab-container">
        <div class="fab-item">
            <span class="fab-text">Messenger-এ চ্যাট করুন</span>
            <a id="messenger-chat-link"
                href="https://m.me/<?php echo htmlspecialchars($settings['messengerPageId'] ?? ''); ?>" target="_blank"
                class="chat-fab messenger-fab"
                style="<?php echo empty($settings['messengerPageId']) ? 'display: none;' : 'display: flex;'; ?>"
                title="Chat with us on Messenger">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" fill="currentColor" width="28" height="28">
                    <path
                        d="M16 2.898c-7.55 0-13.623 5.432-13.623 12.155 0 4.23 2.213 7.95 5.625 10.142v4.846l4.9-2.32c1.073.228 2.196.35 3.352.35 7.55 0 13.623-5.432 13.623-12.155S23.55 2.898 16 2.898zm1.144 18.283l-3.282-3.486-6.438 3.486L13.862 15 17.14 18.48l6.438-3.48-6.438 6.183z">
                    </path>
                </svg>
            </a>
        </div>
        <div class="fab-item">
            <span class="fab-text">WhatsApp-এ চ্যাট করুন</span>
            <a id="whatsapp-chat-link"
                href="https://wa.me/<?php echo htmlspecialchars($settings['whatsappNumber'] ?? ''); ?>" target="_blank"
                class="chat-fab whatsapp-fab"
                style="<?php echo empty($settings['whatsappNumber']) ? 'display: none;' : 'display: flex;'; ?>"
                title="Chat with us on WhatsApp">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" fill="currentColor" width="28"
                    height="28">
                    <path
                        d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.8 0-67.6-9.5-97.2-27.2l-6.7-4-71.6 18.7 19.3-68.6-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5c.1 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.4 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z" />
                </svg>
            </a>
        </div>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="modal">
        <span class="close-modal">×</span>
        <div class="zoom-controls">
            <button id="zoomInBtn" class="zoom-btn">+</button>
            <button id="zoomOutBtn" class="zoom-btn">-</button>
        </div>
        <a class="modal-prev">❮</a>
        <img class="modal-content" id="modalImage">
        <a class="modal-next">❯</a>
        <div id="caption"></div>
    </div>


    <!-- --- PHASE 3: JS HYDRATION --- -->

    <!-- 1. Pass all server-side data to JavaScript -->
    <script type="application/json" id="ssr-data">
    <?php echo json_encode($ssr_data); ?>
</script>

    <!-- 
  OPTIMIZATION FIX:
  - Removed all 1000+ lines of inline JavaScript.
  - The 'defer' attribute ensures this script runs after the HTML is parsed
    and doesn't block the page from rendering.
-->
    <script src="assets/js/app.js?v=<?php echo time(); ?>" type="module" defer></script>

</body>

</html>