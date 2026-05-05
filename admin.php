<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <script>(function (w, d, s, l, i) {
            w[l] = w[l] || []; w[l].push({
                'gtm.start':
                    new Date().getTime(), event: 'gtm.js'
            }); var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : ''; j.async = true; j.src =
                    'https://www.googletagmanager.com/gtm.js?id=' + i + dl; f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-MXKPWCPH');</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Hind Siliguri', sans-serif;
        }

        #admin-view,
        #product-manager-view,
        #visitor-manager-view {
            display: none;
        }

        .editable-input {
            width: 100%;
            padding: 4px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button:disabled {
            cursor: not-allowed;
            opacity: 0.5;
        }

        .toggle-checkbox:checked {
            right: 0;
            border-color: #48bb78;
            /* Tailwind green-500 */
        }

        .toggle-checkbox:checked+.toggle-label {
            background-color: #48bb78;
            /* Tailwind green-500 */
        }
    </style>
</head>

<body class="bg-gray-100">
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MXKPWCPH" height="0" width="0"
            style="display:none;visibility:hidden"></iframe></noscript>
    <div id="login-view" class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-sm">
            <h2 class="text-2xl font-bold text-center mb-6">Admin Login</h2>
            <form id="login-form">
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" name="email" required
                        class="mt-1 w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" id="password" name="password" required
                        class="mt-1 w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700">Login</button>
                <p id="login-error" class="text-red-500 text-sm mt-2 text-center"></p>
            </form>
        </div>
    </div>

    <div id="admin-view" class="container mx-auto p-4">
        <header class="flex justify-between items-center bg-white p-4 rounded-lg shadow-md mb-6">
            <h1 class="text-2xl font-bold">Admin Dashboard</h1>
            <div>
                <button id="show-orders-view"
                    class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded mr-2">Orders</button>
                <button id="show-visitor-manager"
                    class="bg-purple-500 hover:bg-purple-600 text-white font-bold py-2 px-4 rounded mr-2">Manage
                    Visitors</button>
                <button id="show-product-manager"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded mr-2">Manage Content &
                    Settings</button>
                <button id="show-password-manager"
                    class="bg-gray-700 hover:bg-gray-800 text-white font-bold py-2 px-4 rounded mr-2">Change
                    Password</button>
                <button id="logout-btn"
                    class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">Logout</button>
            </div>
        </header>

        <div id="orders-view" class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4">New Orders</h2>
            <div class="mb-4 flex justify-end">
                <input type="text" id="order-search-input" placeholder="Search by name, ID, phone, IP..."
                    class="p-2 border rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                <!-- NEW: Add Manual Order Button -->
                <button id="add-manual-order-btn"
                    class="ml-2 bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">Add +</button>
            </div>
            <div id="api-status-message" class="hidden p-3 mb-4 rounded-md text-sm font-semibold"></div>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="py-2 px-4 text-left">Date</th>
                            <th class="py-2 px-4 text-left">Order ID</th>
                            <th class="py-2 px-4 text-left">Name</th>
                            <th class="py-2 px-4 text-left">Product</th>
                            <th class="py-2 px-4 text-left">Total (Tk)</th>
                            <th class="py-2 px-4 text-left">Address</th>
                            <th class="py-2 px-4 text-left">Phone</th>
                            <th class="py-2 px-4 text-left">Note</th>
                            <th class="py-2 px-4 text-left">Reward</th>
                            <th class="py-2 px-4 text-left">IP Address</th>
                            <th class="py-2 px-4 text-left">Device</th>
                            <th class="py-2 px-4 text-left">Status</th>
                            <th class="py-2 px-4 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="orders-table-body">
                    </tbody>
                </table>
            </div>
            <div id="order-pagination-controls" class="mt-4 flex justify-between items-center">
                <span id="order-page-info" class="text-sm text-gray-600">Page 1 of 1 (0 records)</span>
                <div class="flex items-center">
                    <button id="prev-order-page"
                        class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-1 px-3 rounded-l disabled:opacity-50">Previous</button>
                    <div id="order-page-numbers" class="flex items-center -space-x-px bg-white">
                    </div>
                    <button id="next-order-page"
                        class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-1 px-3 rounded-r disabled:opacity-50">Next</button>
                </div>
            </div>
        </div>

        <div id="visitor-manager-view" class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4">Visitor Analytics</h2>

            <div class="mb-4 flex justify-end">
                <input type="text" id="visitor-search-input" placeholder="Search by IP or Location..."
                    class="p-2 border rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="py-2 px-4 text-left">Visit Date</th>
                            <th class="py-2 px-4 text-left">IP Address</th>
                            <th class="py-2 px-4 text-left">Location</th>
                            <th class="py-2 px-4 text-left">Visit Count</th>
                            <th class="py-2 px-4 text-left">Duration</th>
                            <th class="py-2 px-4 text-left">Device</th>
                            <th class="py-2 px-4 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="visitors-table-body">
                    </tbody>
                </table>
            </div>

            <div id="visitor-pagination-controls" class="mt-4 flex justify-between items-center">
                <span id="visitor-page-info" class="text-sm text-gray-600">Page 1 of 1 (0 records)</span>
                <div class="flex items-center">
                    <button id="prev-visitor-page"
                        class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-1 px-3 rounded-l disabled:opacity-50">Previous</button>
                    <div id="visitor-page-numbers" class="flex items-center -space-x-px bg-white">
                    </div>
                    <button id="next-visitor-page"
                        class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-1 px-3 rounded-r disabled:opacity-50">Next</button>
                </div>
            </div>
        </div>
    </div>

    <div id="product-manager-view" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl max-h-full overflow-y-auto p-6">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h2 class="text-2xl font-bold">Manage Content & Settings</h2>
                <button id="close-product-manager" class="text-gray-500 hover:text-gray-800 text-2xl">×</button>
            </div>
            <form id="product-form" class="space-y-6">
                <div class="p-4 border rounded-md">
                    <h3 class="text-xl font-semibold mb-3">Product Media</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="product-title" class="block font-medium">Product Title (Bangla)</label>
                            <input type="text" id="product-title" class="w-full p-2 border rounded">
                        </div>
                        <div>
                            <label for="product-images-urls-56" class="block font-medium">56 pis Image URLs (comma-separated)</label>
                            <input type="text" id="product-images-urls-56" name="product-images-urls-56" class="w-full p-2 border rounded" placeholder="https://link1, https://link2">
                        </div>
                        <div>
                            <label for="product-images-urls-32" class="block font-medium">32 pis Image URLs (comma-separated)</label>
                            <input type="text" id="product-images-urls-32" name="product-images-urls-32" class="w-full p-2 border rounded" placeholder="https://link1, https://link2">
                        </div>
                        <div>
                            <label for="product-images-urls-99" class="block font-medium">99 Names Image URLs (comma-separated)</label>
                            <input type="text" id="product-images-urls-99" name="product-images-urls-99" class="w-full p-2 border rounded" placeholder="https://link1, https://link2">
                        </div>
                        <div>
                            <label for="product-video-urls" class="block font-medium">Video URLs
                                (comma-separated)</label>
                            <input type="text" id="product-video-urls" class="w-full p-2 border rounded"
                                placeholder="https://link1, https://link2">
                        </div>
                    </div>
                </div>

                <div class="p-4 border rounded-md">
                    <h3 class="text-xl font-semibold mb-3">Site Content & Text</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="main-description-text" class="block font-medium">Description under "কেন আমাদের
                                দোয়া স্টিকার কিনবেন?"</label>
                            <textarea id="main-description-text" rows="4" class="w-full p-2 border rounded"></textarea>
                        </div>
                        <div>
                            <label for="package-56-full-list" class="block font-medium">Package 56 Full List (First line
                                is title, then one item per line)</label>
                            <textarea id="package-56-full-list" rows="10" class="w-full p-2 border rounded" placeholder="প্যাকেজ ৫৬ (৫৬ পিস)
সন্তান লাভের জন্য দোয়া
শক্তি ও সহনশীলতা লাভের দোয়া"></textarea>
                        </div>
                        <div>
                            <label for="package-32-full-list" class="block font-medium">Package 32 Full List (First line
                                is title, then one item per line)</label>
                            <textarea id="package-32-full-list" rows="10" class="w-full p-2 border rounded" placeholder="প্যাকেজ ৩২ (৩২ পিস)
Example item 1
Example item 2"></textarea>
                        </div>
                        <div>
                            <label for="package-99-full-list" class="block font-medium">Package 99 Full List (First line is title, then one item per line)</label>
                            <textarea id="package-99-full-list" rows="10" class="w-full p-2 border rounded" placeholder="প্যাকেজ ৯৯ (৯৯ টি আল্লাহর নাম)
Example item 1
Example item 2"></textarea>
                        </div>
                        <div>
                            <label for="small-size-image-url" class="block font-medium">Small Size Image URL</label>
                            <input type="text" id="small-size-image-url" class="w-full p-2 border rounded"
                                placeholder="https://link_to_small_image">
                        </div>
                        <div>
                            <label for="large-size-image-url" class="block font-medium">Large Size Image URL</label>
                            <input type="text" id="large-size-image-url" class="w-full p-2 border rounded"
                                placeholder="https://link_to_large_image">
                        </div>
                    </div>
                </div>

                <div class="p-4 border rounded-md">
                    <h3 class="text-xl font-semibold mb-3">Pricing (in Taka)</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label for="price-small-56" class="block font-medium">Small - 56 pcs</label>
                            <input type="number" id="price-small-56" class="w-full p-2 border rounded">
                        </div>
                        <div>
                            <label for="price-large-56" class="block font-medium">Large - 56 pcs</label>
                            <input type="number" id="price-large-56" class="w-full p-2 border rounded">
                        </div>
                        <div>
                            <label for="price-small-32" class="block font-medium">Small - 32 pcs</label>
                            <input type="number" id="price-small-32" class="w-full p-2 border rounded">
                        </div>
                        <div>
                            <label for="price-large-32" class="block font-medium">Large - 32 pcs</label>
                            <input type="number" id="price-large-32" class="w-full p-2 border rounded">
                        </div>
                        <div>
                            <label for="price-small-99" class="block font-medium">Small - 99 pcs</label>
                            <input type="number" id="price-small-99" class="w-full p-2 border rounded">
                        </div>
                        <div>
                            <label for="price-large-99" class="block font-medium">Large - 99 pcs</label>
                            <input type="number" id="price-large-99" class="w-full p-2 border rounded">
                        </div>
                        <div>
                            <label for="delivery-charge" class="block font-medium">Delivery Charge</label>
                            <input type="number" id="delivery-charge" class="w-full p-2 border rounded">
                        </div>
                    </div>
                </div>

                <div class="p-4 border rounded-md">
                    <h3 class="text-xl font-semibold mb-3">Feature Controls</h3>

                    <div class="flex items-center justify-between mb-4 p-2 bg-gray-50 rounded-md">
                        <label for="enable-spinning-wheel" class="font-medium">Enable Spinning Wheel</label>
                        <div
                            class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                            <input type="checkbox" name="enable-spinning-wheel" id="enable-spinning-wheel"
                                class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer" />
                            <label for="enable-spinning-wheel"
                                class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                        </div>
                    </div>

                    <div class="p-3 border rounded-md mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <label for="enable-small-size" class="font-medium">Enable 'Small' Size Option</label>
                            <div
                                class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                                <input type="checkbox" name="enable-small-size" id="enable-small-size"
                                    class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer" />
                                <label for="enable-small-size"
                                    class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                            </div>
                        </div>
                        <div>
                            <label for="label-small-size" class="block font-medium text-sm text-gray-600">Rename 'Small'
                                Size Label (e.g., ছোট)</label>
                            <input type="text" id="label-small-size" class="w-full p-2 border rounded mt-1">
                        </div>
                    </div>

                    <div class="p-3 border rounded-md">
                        <div class="flex items-center justify-between mb-2">
                            <label for="enable-big-size" class="font-medium">Enable 'Big' Size Option</label>
                            <div
                                class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                                <input type="checkbox" name="enable-big-size" id="enable-big-size"
                                    class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer" />
                                <label for="enable-big-size"
                                    class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                            </div>
                        </div>
                        <div>
                            <label for="label-big-size" class="block font-medium text-sm text-gray-600">Rename 'Big'
                                Size Label (e.g., বড়)</label>
                            <input type="text" id="label-big-size" class="w-full p-2 border rounded mt-1">
                        </div>
                    </div>
                </div>

                <div class="p-4 border rounded-md">
                    <h3 class="text-xl font-semibold mb-3">General Settings</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="whatsapp-number" class="block font-medium">WhatsApp Number (with country code,
                                e.g., 88017...)</label>
                            <input type="text" id="whatsapp-number" class="w-full p-2 border rounded"
                                placeholder="8801...">
                        </div>
                        <div>
                            <label for="messenger-page-id" class="block font-medium">Messenger Page ID /
                                Username</label>
                            <input type="text" id="messenger-page-id" class="w-full p-2 border rounded"
                                placeholder="your.page.username">
                        </div>
                    </div>
                </div>

                <div class="p-4 border rounded-md">
                    <h3 class="text-xl font-semibold mb-3">Customer Feedback Section</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="feedback-section-title" class="block font-medium">Section Title</label>
                            <input type="text" id="feedback-section-title" class="w-full p-2 border rounded"
                                placeholder="e.g., What Our Customers Say">
                        </div>
                        <div>
                            <label for="feedback-images-urls" class="block font-medium">Feedback Image URLs
                                (comma-separated screenshots)</label>
                            <input type="text" id="feedback-images-urls" class="w-full p-2 border rounded"
                                placeholder="https://link1, https://link2">
                        </div>
                        <div>
                            <label for="feedback-section-description" class="block font-medium">Section
                                Description</label>
                            <textarea id="feedback-section-description" rows="4" class="w-full p-2 border rounded"
                                placeholder="Describe the feedback..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="p-4 border rounded-md">
                    <h3 class="text-xl font-semibold mb-3">Spinning Wheel Rewards (9 Slots)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                        <input type="text" id="reward-1" class="w-full p-2 border rounded" placeholder="Reward 1">
                        <input type="text" id="reward-2" class="w-full p-2 border rounded" placeholder="Reward 2">
                        <input type="text" id="reward-3" class="w-full p-2 border rounded" placeholder="Reward 3">
                        <input type="text" id="reward-4" class="w-full p-2 border rounded" placeholder="Reward 4">
                        <input type="text" id="reward-5" class="w-full p-2 border rounded" placeholder="Reward 5">
                        <input type="text" id="reward-6" class="w-full p-2 border rounded" placeholder="Reward 6">
                        <input type="text" id="reward-7" class="w-full p-2 border rounded" placeholder="Reward 7">
                        <input type="text" id="reward-8" class="w-full p-2 border rounded" placeholder="Reward 8">
                        <input type="text" id="reward-9" class="w-full p-2 border rounded" placeholder="Reward 9">
                    </div>
                </div>


                <button type="submit"
                    class="w-full bg-green-600 text-white py-3 rounded hover:bg-green-700 font-bold text-lg">Save All
                    Changes</button>
                <p id="product-success" class="text-green-600 text-center mt-2"></p>
            </form>
        </div>
    </div>


    <div id="password-manager-view" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4"
        style="display: none;">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h2 class="text-2xl font-bold">Change Password</h2>
                <button id="close-password-manager" class="text-gray-500 hover:text-gray-800 text-2xl">×</button>
            </div>
            <form id="password-form" class="space-y-4">
                <div>
                    <label for="current-password" class="block font-medium">Current Password</label>
                    <input type="password" id="current-password" required
                        class="mt-1 w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="new-password" class="block font-medium">New Password</label>
                    <input type="password" id="new-password" required
                        class="mt-1 w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="confirm-password" class="block font-medium">Confirm New Password</label>
                    <input type="password" id="confirm-password" required
                        class="mt-1 w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700 font-bold">Update
                    Password</button>
                <p id="password-message" class="text-center mt-2 text-sm"></p>
            </form>
        </div>
    </div>

    <!-- NEW: Manual Order Modal -->
    <div id="manual-order-view" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4"
        style="display: none;">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6 overflow-y-auto max-h-full">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h2 class="text-2xl font-bold">Add Manual Order</h2>
                <button id="close-manual-order" class="text-gray-500 hover:text-gray-800 text-2xl">×</button>
            </div>

            <!-- Tabs -->
            <div class="flex border-b mb-4">
                <button id="tab-single-order" class="w-1/2 py-2 text-center font-semibold text-indigo-600 border-b-2 border-indigo-600 focus:outline-none">Single Entry</button>
                <button id="tab-bulk-order" class="w-1/2 py-2 text-center font-semibold text-gray-500 hover:text-indigo-600 focus:outline-none">Bulk Entry</button>
            </div>

            <!-- Single Entry Form -->
            <form id="manual-order-form" class="space-y-4">
                <div>
                    <label class="block font-medium">Customer Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="w-full p-2 border rounded">
                </div>
                <div>
                    <label class="block font-medium">Phone Number <span class="text-red-500">*</span></label>
                    <input type="text" name="phone" required class="w-full p-2 border rounded" placeholder="017...">
                </div>
                <div>
                    <label class="block font-medium">Address</label>
                    <textarea name="address" rows="2" class="w-full p-2 border rounded"></textarea>
                </div>
                 <div>
                    <label class="block font-medium">Product Name</label>
                    <input type="text" name="product" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label class="block font-medium">Total Price (Tk)</label>
                    <input type="number" name="price" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label class="block font-medium">Note</label>
                    <textarea name="note" rows="2" class="w-full p-2 border rounded"></textarea>
                </div>
                
                <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700 font-bold">Create Order & Track with Pixel</button>
                <p id="manual-order-msg" class="text-center mt-2 text-sm text-gray-700"></p>
            </form>

            <!-- Bulk Entry Form -->
            <form id="bulk-order-form" class="space-y-4 hidden">
                <div>
                    <label class="block font-medium mb-1">Paste Orders (Block Separated)</label>
                    <p class="text-xs text-gray-500 mb-2">Format per block (lines): OrderId, Name, Product, Price, Address, Phone, Note (Optional). Separate orders with empty lines.</p>
                    <textarea id="bulk-order-input" rows="12" class="w-full p-2 border rounded font-mono text-sm" placeholder="1001
John Doe
Premium Sticker Pack
500
123 Main St, Dhaka
01700000000
Customer requested quick delivery

1002
Jane Smith
..."></textarea>
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700 font-bold">Process Bulk Orders</button>
                <div id="bulk-order-preview" class="hidden mt-4 bg-gray-50 p-2 rounded border text-sm max-h-40 overflow-y-auto"></div>
                <p id="bulk-order-msg" class="text-center mt-2 text-sm text-gray-700"></p>
            </form>
        </div>
    </div>


    <script type="module">
        // --- NEW: Manual Order Logic ---
const showManualOrderBtn = document.getElementById('add-manual-order-btn');
const manualOrderView = document.getElementById('manual-order-view');
const closeManualOrderBtn = document.getElementById('close-manual-order');
const manualOrderForm = document.getElementById('manual-order-form');
const manualOrderMsg = document.getElementById('manual-order-msg');

// Bulk Elements
const tabSingle = document.getElementById('tab-single-order');
const tabBulk = document.getElementById('tab-bulk-order');
const bulkOrderForm = document.getElementById('bulk-order-form');
const bulkOrderInput = document.getElementById('bulk-order-input');
const bulkOrderMsg = document.getElementById('bulk-order-msg');

if (tabSingle && tabBulk) {
    tabSingle.addEventListener('click', () => {
        tabSingle.className = "w-1/2 py-2 text-center font-semibold text-indigo-600 border-b-2 border-indigo-600 focus:outline-none";
        tabBulk.className = "w-1/2 py-2 text-center font-semibold text-gray-500 hover:text-indigo-600 focus:outline-none";
        manualOrderForm.classList.remove('hidden');
        bulkOrderForm.classList.add('hidden');
        manualOrderMsg.textContent = ''; // Clear messages when switching tabs
        bulkOrderMsg.textContent = '';
    });

    tabBulk.addEventListener('click', () => {
        tabBulk.className = "w-1/2 py-2 text-center font-semibold text-indigo-600 border-b-2 border-indigo-600 focus:outline-none";
        tabSingle.className = "w-1/2 py-2 text-center font-semibold text-gray-500 hover:text-indigo-600 focus:outline-none";
        bulkOrderForm.classList.remove('hidden');
        manualOrderForm.classList.add('hidden');
        manualOrderMsg.textContent = ''; // Clear messages when switching tabs
        bulkOrderMsg.textContent = '';
    });
}

if (showManualOrderBtn) {
    showManualOrderBtn.addEventListener('click', () => {
        manualOrderView.style.display = 'flex';
        manualOrderForm.reset();
        manualOrderMsg.textContent = '';
        if(bulkOrderForm) {
             bulkOrderForm.reset();
             bulkOrderMsg.textContent = '';
        }
        // Ensure single entry tab is active by default when opening modal
        if (tabSingle) {
            tabSingle.click();
        }
    });
}

if (closeManualOrderBtn) {
    closeManualOrderBtn.addEventListener('click', () => {
        manualOrderView.style.display = 'none';
    });
}

if (manualOrderForm) {
    manualOrderForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        manualOrderMsg.textContent = 'Processing...';
        manualOrderMsg.className = 'text-center mt-2 text-sm text-blue-600';

        const formData = new FormData(manualOrderForm);
        formData.append('action', 'create_manual_order');

        try {
            const response = await fetch('api/admin.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                manualOrderMsg.textContent = 'Order created successfully!';
                manualOrderMsg.className = 'text-center mt-2 text-sm text-green-600';
                loadOrders();
                setTimeout(() => {
                    manualOrderView.style.display = 'none';
                }, 1500);
            } else {
                manualOrderMsg.textContent = result.error || 'Failed to create order.';
                manualOrderMsg.className = 'text-center mt-2 text-sm text-red-500';
            }
        } catch (error) {
            console.error(error);
            manualOrderMsg.textContent = 'An error occurred.';
            manualOrderMsg.className = 'text-center mt-2 text-sm text-red-500';
        }
    });
}

if (bulkOrderForm) {
    bulkOrderForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        bulkOrderMsg.textContent = 'Parsing and sending...';
        bulkOrderMsg.className = 'text-center mt-2 text-sm text-blue-600';

        const rawText = bulkOrderInput.value.trim();
        if (!rawText) {
            bulkOrderMsg.textContent = 'Please enter order data.';
            bulkOrderMsg.className = 'text-center mt-2 text-sm text-red-500';
            return;
        }

        // Parse logic
        // Split by empty lines (2 or more newlines)
        const blocks = rawText.split(/\n\s*\n/);
        const parsedOrders = [];

        blocks.forEach(block => {
            const lines = block.split('\n').map(l => l.trim()).filter(l => l !== '');
            if (lines.length >= 6) { // Minimum required common fields
                // Map based on user spec: OrderId, Name, Product, Price, Address, Phone, Note
                parsedOrders.push({
                    orderId: lines[0],
                    name: lines[1],
                    product: lines[2],
                    price: lines[3],
                    address: lines[4],
                    phone: lines[5],
                    note: lines[6] || '' // 7th line is note if exists
                });
            }
        });

        if (parsedOrders.length === 0) {
            bulkOrderMsg.textContent = 'No valid order blocks found. Check format.';
            bulkOrderMsg.className = 'text-center mt-2 text-sm text-red-500';
            return;
        }

        const formData = new FormData();
        formData.append('action', 'create_bulk_manual_orders');
        formData.append('orders', JSON.stringify(parsedOrders));

        try {
            const response = await fetch('api/admin.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                bulkOrderMsg.textContent = result.message || 'Orders processed successfully!';
                bulkOrderMsg.className = 'text-center mt-2 text-sm text-green-600';
                loadOrders();
                setTimeout(() => {
                    manualOrderView.style.display = 'none';
                }, 2000);
            } else {
                bulkOrderMsg.textContent = result.error || 'Failed to process valid orders.';
                bulkOrderMsg.className = 'text-center mt-2 text-sm text-red-500';
            }
        } catch (error) {
            console.error(error);
            bulkOrderMsg.textContent = 'Server error occurred.';
            bulkOrderMsg.className = 'text-center mt-2 text-sm text-red-500';
        }
    });
}

        const loginView = document.getElementById('login-view');
        const adminView = document.getElementById('admin-view');
        const loginForm = document.getElementById('login-form');
        const loginError = document.getElementById('login-error');
        const logoutBtn = document.getElementById('logout-btn');

        const ordersView = document.getElementById('orders-view');
        const visitorManagerView = document.getElementById('visitor-manager-view');
        const productManagerView = document.getElementById('product-manager-view');
        const showProductManagerBtn = document.getElementById('show-product-manager');
        const showVisitorManagerBtn = document.getElementById('show-visitor-manager');
        const showOrdersBtn = document.getElementById('show-orders-view');
        const closeProductManagerBtn = document.getElementById('close-product-manager');

        const ordersTableBody = document.getElementById('orders-table-body');
        const productForm = document.getElementById('product-form');
        const productSuccessMsg = document.getElementById('product-success');

        const passwordManagerView = document.getElementById('password-manager-view');
        const showPasswordManagerBtn = document.getElementById('show-password-manager');
        const closePasswordManagerBtn = document.getElementById('close-password-manager');
        const passwordForm = document.getElementById('password-form');
        const passwordMessage = document.getElementById('password-message');

        const visitorsTableBody = document.getElementById('visitors-table-body');

        // --- New Shared State ---
        const recordsPerPage = 10; // Show 15 records per page

        // Order Page State
        let orderCurrentPage = 1;
        let orderSearchTerm = '';
        let totalOrderRecords = 0;

        // Visitor Page State
        let visitorCurrentPage = 1;
        let visitorSearchTerm = '';
        let totalVisitorRecords = 0;

        // Helper function for search input
        function debounce(func, delay) {
            let timeout;
            return function (...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), delay);
            };
        }




        // --- ADD THESE NEW LISTENERS for Password Modal ---

        showPasswordManagerBtn.addEventListener('click', () => {
            passwordManagerView.style.display = 'flex';
            passwordMessage.textContent = '';
            passwordForm.reset();
        });

        closePasswordManagerBtn.addEventListener('click', () => {
            passwordManagerView.style.display = 'none';
        });

        passwordForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            passwordMessage.textContent = 'Updating...';
            passwordMessage.className = 'text-center mt-2 text-sm text-gray-700';

            const currentPassword = document.getElementById('current-password').value;
            const newPassword = document.getElementById('new-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;

            if (newPassword !== confirmPassword) {
                passwordMessage.textContent = 'New passwords do not match.';
                passwordMessage.className = 'text-center mt-2 text-sm text-red-500';
                return;
            }

            if (newPassword.length < 6) {
                passwordMessage.textContent = 'New password must be at least 6 characters.';
                passwordMessage.className = 'text-center mt-2 text-sm text-red-500';
                return;
            }

            const actionData = new URLSearchParams();
            actionData.append('current_password', currentPassword);
            actionData.append('new_password', newPassword);

            try {
                const response = await fetch('api/admin.php?action=update_password', {
                    method: 'POST',
                    body: actionData
                });
                const result = await response.json();

                if (result.success) {
                    passwordMessage.textContent = 'Password updated successfully!';
                    passwordMessage.className = 'text-center mt-2 text-sm text-green-600';
                    setTimeout(() => {
                        passwordManagerView.style.display = 'none';
                    }, 2000);
                } else {
                    passwordMessage.textContent = result.error || 'Failed to update password.';
                    passwordMessage.className = 'text-center mt-2 text-sm text-red-500';
                }
            } catch (error) {
                passwordMessage.textContent = 'An error occurred.';
                passwordMessage.className = 'text-center mt-2 text-sm text-red-500';
            }
        });








        // NEW: Checks session via PHP backend
        async function checkLoginStatus() {
            try {
                const response = await fetch('api/admin.php?action=check_session');
                const data = await response.json();
                if (data.loggedIn) {
                    loginView.style.display = 'none';
                    adminView.style.display = 'block';
                    ordersView.style.display = 'block';
                    visitorManagerView.style.display = 'none';

                    // --- We no longer poll ---
                    // The user will fetch data by searching or changing pages.

                    loadAllAdminData(); // This loads settings, which is fine
                    setupPaginationAndSearch(); // NEW: Wire up all our new listeners

                    // Load initial data for both tables
                    loadOrders();
                    loadVisitors();
                } else {
                    loginView.style.display = 'flex';
                    adminView.style.display = 'none';
                }
            } catch (error) {
                loginView.style.display = 'flex';
                adminView.style.display = 'none';
                console.error('Session check failed:', error);
            }
        }

        // NEW: Login form uses fetch to call PHP backend
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(loginForm);
            try {
                const response = await fetch('api/admin.php?action=login', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    checkLoginStatus();
                } else {
                    loginError.textContent = result.error || 'Login failed.';
                }
            } catch (error) {
                loginError.textContent = 'An error occurred during login.';
            }
        });

        // NEW: Logout calls PHP backend
        logoutBtn.addEventListener('click', async () => {
            await fetch('api/admin.php?action=logout');
            checkLoginStatus();
        });


        // NEW: Function to create the 1, 2, 3... page number buttons
        function createPaginationNumbers(prefix, currentPage, totalPages) {
            const container = document.getElementById(`${prefix}-page-numbers`);
            if (!container) return;

            container.innerHTML = ''; // Clear old numbers

            const createButton = (page) => {
                const btn = document.createElement('button');
                btn.dataset.page = page;
                btn.textContent = page;

                if (page === currentPage) {
                    // Current page style
                    btn.className = "relative inline-flex items-center px-4 py-2 border border-indigo-500 bg-indigo-500 text-sm font-medium text-white z-10";
                } else {
                    // Other page style
                    btn.className = "relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50";
                }
                return btn;
            };

            const createEllipsis = () => {
                const span = document.createElement('span');
                span.textContent = '...';
                span.className = "relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700";
                return span;
            };

            const maxVisibleButtons = 7; // e.g., 1 ... 4 5 6 ... 10
            if (totalPages <= maxVisibleButtons) {
                // Show all pages
                for (let i = 1; i <= totalPages; i++) {
                    container.appendChild(createButton(i));
                }
            } else {
                // Show ellipsis pagination
                // Always show first page
                container.appendChild(createButton(1));

                let start = Math.max(2, currentPage - 2);
                let end = Math.min(totalPages - 1, currentPage + 2);

                if (currentPage - 2 > 2) {
                    container.appendChild(createEllipsis());
                }

                if (currentPage < 5) {
                    end = 5;
                }
                if (currentPage > totalPages - 4) {
                    start = totalPages - 4;
                }

                for (let i = start; i <= end; i++) {
                    container.appendChild(createButton(i));
                }

                if (currentPage + 2 < totalPages - 1) {
                    container.appendChild(createEllipsis());
                }

                // Always show last page
                container.appendChild(createButton(totalPages));
            }
        }


        // REPLACED: This function now calls createPaginationNumbers
        function updatePagination(prefix, currentPage, totalRecords, limit) {
            const totalPages = Math.ceil(totalRecords / limit) || 1;
            const pageInfo = document.getElementById(`${prefix}-page-info`);
            const prevBtn = document.getElementById(`prev-${prefix}-page`);
            const nextBtn = document.getElementById(`next-${prefix}-page`);

            if (pageInfo) pageInfo.textContent = `Page ${currentPage} of ${totalPages} (${totalRecords} records)`;
            if (prevBtn) prevBtn.disabled = currentPage === 1;
            if (nextBtn) nextBtn.disabled = currentPage === totalPages;

            // --- NEW ---
            // Call the new function to build the 1, 2, 3... buttons
            createPaginationNumbers(prefix, currentPage, totalPages);
            // --- END NEW ---
        }

        // REPLACED: This function now adds event delegation for the new page number buttons
        function setupPaginationAndSearch() {
            // --- Order Listeners ---
            const orderSearchInput = document.getElementById('order-search-input');
            const debouncedOrderSearch = debounce(() => {
                orderSearchTerm = orderSearchInput.value;
                orderCurrentPage = 1; // Reset to page 1 on new search
                loadOrders();
            }, 350);
            orderSearchInput.addEventListener('input', debouncedOrderSearch);

            document.getElementById('prev-order-page').addEventListener('click', () => {
                if (orderCurrentPage > 1) {
                    orderCurrentPage--;
                    loadOrders();
                }
            });
            document.getElementById('next-order-page').addEventListener('click', () => {
                const totalPages = Math.ceil(totalOrderRecords / recordsPerPage);
                if (orderCurrentPage < totalPages) {
                    orderCurrentPage++;
                    loadOrders();
                }
            });

            // --- NEW: Event Delegation for Order Page Numbers ---
            document.getElementById('order-page-numbers').addEventListener('click', (e) => {
                const page = e.target.closest('button')?.dataset.page;
                if (page) {
                    const newPage = parseInt(page, 10);
                    if (newPage !== orderCurrentPage) {
                        orderCurrentPage = newPage;
                        loadOrders();
                    }
                }
            });


            // --- Visitor Listeners (re-wired) ---
            const visitorSearchInput = document.getElementById('visitor-search-input');
            const debouncedVisitorSearch = debounce(() => {
                visitorSearchTerm = visitorSearchInput.value;
                visitorCurrentPage = 1;
                loadVisitors();
            }, 350);
            visitorSearchInput.addEventListener('input', debouncedVisitorSearch);

            document.getElementById('prev-visitor-page').addEventListener('click', () => {
                if (visitorCurrentPage > 1) {
                    visitorCurrentPage--;
                    loadVisitors();
                }
            });
            document.getElementById('next-visitor-page').addEventListener('click', () => {
                const totalPages = Math.ceil(totalVisitorRecords / recordsPerPage);
                if (visitorCurrentPage < totalPages) {
                    visitorCurrentPage++;
                    loadVisitors();
                }
            });

            // --- NEW: Event Delegation for Visitor Page Numbers ---
            document.getElementById('visitor-page-numbers').addEventListener('click', (e) => {
                const page = e.target.closest('button')?.dataset.page;
                if (page) {
                    const newPage = parseInt(page, 10);
                    if (newPage !== visitorCurrentPage) {
                        visitorCurrentPage = newPage;
                        loadVisitors();
                    }
                }
            });
        }



        // NEW: Loads orders from PHP backend
        async function loadOrders() {
            try {
                const response = await fetch(`api/admin.php?action=get_orders&page=${orderCurrentPage}&limit=${recordsPerPage}&search=${encodeURIComponent(orderSearchTerm)}`);
                const result = await response.json();

                const orders = result.data || [];
                totalOrderRecords = parseInt(result.totalRecords || 0);

                ordersTableBody.innerHTML = ''; // Clear table

                if (orders.length === 0) {
                    ordersTableBody.innerHTML = '<tr><td colspan="13" class="text-center py-4 text-gray-600">No orders found.</td></tr>';
                }

                orders.forEach(order => {
                    const row = document.createElement('tr');
                    row.className = 'border-b';
                    row.dataset.orderId = order.id;
                    // Use replace() for cross-browser date parsing from MySQL format
                    // Convert 'YYYY-MM-DD HH:MM:SS' to 'YYYY-MM-DDTHH:MM:SSZ' to mark it as UTC
                    const formattedDate = new Date(order.timestamp.replace(' ', 'T') + 'Z').toLocaleString('en-GB', { timeZone: 'Asia/Dhaka' });
                    row.innerHTML = `
                <td class="py-2 px-4 whitespace-nowrap">${formattedDate}</td>
                <td class="py-2 px-4 font-mono text-sm text-blue-700">${order.orderId || 'N/A'}</td>
                <td class="py-2 px-4" data-field="name">${order.name}</td>
                <td class="py-2 px-4 font-semibold" data-field="product">${order.product || 'N/A'}</td>
                <td class="py-2 px-4 font-bold text-red-600" data-field="totalPrice">${order.totalPrice || 'N/A'}</td>
                <td class="py-2 px-4" data-field="address">${order.address}</td>
                <td class="py-2 px-4" data-field="phone">${order.phone}</td>
                <td class="py-2 px-4" data-field="note">${order.note || ''}</td>
                <td class="py-2 px-4 text-blue-600 font-semibold" data-field="reward">${order.reward || 'N/A'}</td>
                <td class="py-2 px-4 text-gray-600 text-sm">${order.ipAddress || 'N/A'}</td>
                <td class="py-2 px-4 text-gray-500 text-xs" title="${order.userAgent || ''}">${(order.userAgent || 'N/A').substring(0, 30)}...</td>
                <td class="py-2 px-4">
                    <select data-id="${order.id}" class="status-dropdown p-1 border rounded ${getStatusColor(order.status)}">
                        <option value="Pending" ${order.status === 'Pending' ? 'selected' : ''}>Pending</option>
                        <option value="Confirmed" ${order.status === 'Confirmed' ? 'selected' : ''}>Confirmed</option>
                        <option value="Preparing" ${order.status === 'Preparing' ? 'selected' : ''}>Preparing</option>
                        <option value="Shipped" ${order.status === 'Shipped' ? 'selected' : ''}>Shipped</option>
                        <option value="Delivered" ${order.status === 'Delivered' ? 'selected' : ''}>Delivered</option>
                        <option value="Canceled" ${order.status === 'Canceled' ? 'selected' : ''}>Canceled</option>
                    </select>
                </td>
                <td class="py-2 px-4 whitespace-nowrap">
                    <button data-id="${order.id}" class="edit-order-btn bg-blue-500 hover:bg-blue-600 text-white text-sm py-1 px-2 rounded">Edit</button>
                    <button data-id="${order.id}" class="delete-order-btn bg-red-500 hover:bg-red-600 text-white text-sm py-1 px-2 rounded ml-1">Delete</button>
                    <button data-tracking-id="${order.orderId}" class="copy-tracking-link-btn bg-green-500 hover:bg-green-600 text-white text-sm py-1 px-2 rounded ml-1">Copy Link</button>
                </td>
            `;
                    ordersTableBody.appendChild(row);
                });

                // Update pagination UI
                updatePagination('order', orderCurrentPage, totalOrderRecords, recordsPerPage);

            } catch (error) {
                console.error("Failed to load orders:", error);
                ordersTableBody.innerHTML = '<tr><td colspan="13" class="text-center py-4 text-red-500">Failed to load orders.</td></tr>';
            }
        }

        // NEW: Loads visitors from PHP backend
        async function loadVisitors() {
            try {
                const response = await fetch(`api/admin.php?action=get_visitors&page=${visitorCurrentPage}&limit=${recordsPerPage}&search=${encodeURIComponent(visitorSearchTerm)}`);
                const result = await response.json();

                const visitors = result.data || [];
                totalVisitorRecords = parseInt(result.totalRecords || 0);

                visitorsTableBody.innerHTML = ''; // Clear table

                if (visitors.length === 0) {
                    visitorsTableBody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-gray-600">No visitors found.</td></tr>';
                }

                visitors.forEach(visitor => {
                    const row = document.createElement('tr');
                    row.className = 'border-b';
                    // Convert 'YYYY-MM-DD HH:MM:SS' to 'YYYY-MM-DDTHH:MM:SSZ' to mark it as UTC
                    const formattedDate = new Date(visitor.startTime.replace(' ', 'T') + 'Z').toLocaleString('en-GB', { timeZone: 'Asia/Dhaka' });
                    const duration = visitor.durationMillis || 0;
                    const seconds = Math.floor((duration / 1000) % 60);
                    const minutes = Math.floor((duration / (1000 * 60)) % 60);
                    const hours = Math.floor((duration / (1000 * 60 * 60)) % 24);
                    const formattedDuration = `${hours}h ${minutes}m ${seconds}s`;
                    row.innerHTML = `
                <td class="py-2 px-4">${formattedDate}</td>
                <td class="py-2 px-4">${visitor.ipAddress}</td>
                <td class="py-2 px-4">${visitor.location}</td>
                <td class="py-2 px-4 font-semibold text-center">${visitor.visitCount || 1}</td>
                <td class="py-2 px-4 font-semibold">${formattedDuration}</td>
                <td class="py-2 px-4 text-xs text-gray-500" title="${visitor.userAgent || ''}">${(visitor.userAgent || 'N/A').substring(0, 40)}...</td>
                <td class="py-2 px-4">
                    <button data-id="${visitor.id}" class="delete-visitor-btn bg-red-500 hover:bg-red-600 text-white text-sm py-1 px-2 rounded">Delete</button>
                </td>
            `;
                    visitorsTableBody.appendChild(row);
                });

                // Update pagination UI
                updatePagination('visitor', visitorCurrentPage, totalVisitorRecords, recordsPerPage);

            } catch (error) {
                console.error("Failed to load visitors:", error);
                visitorsTableBody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-red-500">Failed to load visitors.</td></tr>';
            }
        }



        function getStatusColor(status) {
            switch (status) {
                case 'Pending': return 'bg-yellow-100 text-yellow-800';
                case 'Confirmed': return 'bg-indigo-100 text-indigo-800';
                case 'Preparing': return 'bg-purple-100 text-purple-800';
                case 'Shipped': return 'bg-blue-100 text-blue-800';
                case 'Delivered': return 'bg-green-100 text-green-800';
                case 'Canceled': return 'bg-red-100 text-red-800';
                default: return 'bg-gray-100';
            }
        }

        // NEW: All event listeners now use fetch to call the PHP backend
        ordersTableBody.addEventListener('click', async (e) => {
            const target = e.target;
            const orderId = target.dataset.id;

            // Use URLSearchParams for POST data
            const actionData = new URLSearchParams();

            if (target.classList.contains('delete-order-btn')) {
                if (confirm('Are you sure you want to delete this order?')) {
                    actionData.append('id', orderId);
                    await fetch('api/admin.php?action=delete_order', { method: 'POST', body: actionData });
                    loadOrders(); // Refresh the list
                }
            } else if (target.classList.contains('edit-order-btn')) {
                const row = target.closest('tr');
                toggleOrderEdit(row, true);
            } else if (target.classList.contains('save-order-btn')) {
                const row = target.closest('tr');
                actionData.append('id', orderId);
                actionData.append('name', row.querySelector('[data-field="name"] input').value);
                actionData.append('product', row.querySelector('[data-field="product"] input').value);
                actionData.append('totalPrice', row.querySelector('[data-field="totalPrice"] input').value);
                actionData.append('address', row.querySelector('[data-field="address"] input').value);
                actionData.append('phone', row.querySelector('[data-field="phone"] input').value);
                actionData.append('note', row.querySelector('[data-field="note"] input').value);
                actionData.append('reward', row.querySelector('[data-field="reward"] input').value);
                await fetch('api/admin.php?action=update_order_details', { method: 'POST', body: actionData });
                loadOrders();
            } else if (target.classList.contains('cancel-edit-btn')) {
                loadOrders(); // Just reload to cancel
            } else if (target.classList.contains('copy-tracking-link-btn')) {
                const trackingId = target.dataset.trackingId;
                if (trackingId) {
                    const trackingUrl = `${window.location.origin.replace('/admin.html', '').replace('/admin.php', '')}/?orderId=${trackingId}`;
                    navigator.clipboard.writeText(trackingUrl).then(() => {
                        target.textContent = 'Copied!';
                        setTimeout(() => { target.textContent = 'Copy Link'; }, 2000);
                    });
                }
            }
        });

        ordersTableBody.addEventListener('change', async (e) => {
            if (e.target.classList.contains('status-dropdown')) {
                const orderId = e.target.dataset.id;
                const newStatus = e.target.value;
                const apiMessageDiv = document.getElementById('api-status-message');

                const actionData = new URLSearchParams();
                actionData.append('id', orderId);
                actionData.append('status', newStatus);

                const response = await fetch('api/admin.php?action=update_order_status', { method: 'POST', body: actionData });
                const result = await response.json();

                // This handles the Vercel API response you had in your original code
                if (result.apiResponse && result.apiResponse.status) {
                    const apiResult = result.apiResponse;
                    apiMessageDiv.textContent = `${apiResult.status}: ${apiResult.message}`;
                    apiMessageDiv.className = `p-3 mb-4 rounded-md text-sm font-semibold ${apiResult.status === 'Success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`;
                    apiMessageDiv.classList.remove('hidden');
                    setTimeout(() => apiMessageDiv.classList.add('hidden'), 5000);
                }
                loadOrders();
            }
        });

        function toggleOrderEdit(row, isEditing) {
            const editableFields = row.querySelectorAll('[data-field]');

            if (isEditing) {
                editableFields.forEach(td => {
                    const currentValue = td.textContent;
                    td.dataset.originalValue = currentValue;
                    const fieldName = td.dataset.field;
                    const inputType = (fieldName === 'totalPrice') ? 'number' : 'text';
                    td.innerHTML = `<input type="${inputType}" class="editable-input" value="${currentValue}">`;
                });
                const actionsCell = row.querySelector('td:last-child');
                actionsCell.innerHTML = `
            <button data-id="${row.dataset.orderId}" class="save-order-btn bg-green-500 hover:bg-green-600 text-white text-sm py-1 px-2 rounded">Save</button>
            <button class="cancel-edit-btn bg-gray-500 hover:bg-gray-600 text-white text-sm py-1 px-2 rounded ml-1">Cancel</button>
        `;
            } else {
                loadOrders();
            }
        }

        visitorsTableBody.addEventListener('click', async (e) => {
            if (e.target.classList.contains('delete-visitor-btn')) {
                const visitorId = e.target.dataset.id;
                if (confirm('Are you sure you want to delete this visitor record?')) {
                    const actionData = new URLSearchParams();
                    actionData.append('id', visitorId);
                    await fetch('api/admin.php?action=delete_visitor', { method: 'POST', body: actionData });
                    loadVisitors();
                }
            }
        });

        showProductManagerBtn.addEventListener('click', () => productManagerView.style.display = 'flex');
        closeProductManagerBtn.addEventListener('click', () => productManagerView.style.display = 'none');
        showVisitorManagerBtn.addEventListener('click', () => {
            ordersView.style.display = 'none';
            visitorManagerView.style.display = 'block';
        });
        showOrdersBtn.addEventListener('click', () => {
            visitorManagerView.style.display = 'none';
            ordersView.style.display = 'block';
        });

        // NEW: Loads all settings from PHP backend
        async function loadAllAdminData() {
            try {
                const response = await fetch('api/admin.php?action=get_settings');
                const data = await response.json();
                const { product, settings } = data;

                if (product) {
                    document.getElementById('product-title').value = product.title || '';
                    const images = product.imageUrls || {};
                    if (Array.isArray(images)) {
                        document.getElementById('product-images-urls-56').value = images.join(', ');
                        document.getElementById('product-images-urls-32').value = images.join(', ');
                        document.getElementById('product-images-urls-99').value = images.join(', ');
                    } else {
                        document.getElementById('product-images-urls-56').value = (images['56'] || []).join(', ');
                        document.getElementById('product-images-urls-32').value = (images['32'] || []).join(', ');
                        document.getElementById('product-images-urls-99').value = (images['99'] || []).join(', ');
                    }
                    document.getElementById('product-video-urls').value = (product.videoUrls || []).join(', ');
                }

                if (settings) {
                    document.getElementById('whatsapp-number').value = settings.whatsappNumber || '';
                    document.getElementById('messenger-page-id').value = settings.messengerPageId || '';
                    document.getElementById('main-description-text').value = settings.mainDescription || '';
                    document.getElementById('package-56-full-list').value = settings.package56List || '';
                    document.getElementById('package-32-full-list').value = settings.package32List || '';
                    document.getElementById('package-99-full-list').value = settings.package99List || '';
                    document.getElementById('price-small-56').value = settings.priceSmall56 || '';
                    document.getElementById('price-large-56').value = settings.priceLarge56 || '';
                    document.getElementById('price-small-32').value = settings.priceSmall32 || '';
                    document.getElementById('price-large-32').value = settings.priceLarge32 || '';
                    document.getElementById('price-small-99').value = settings.priceSmall99 || '';
                    document.getElementById('price-large-99').value = settings.priceLarge99 || '';
                    document.getElementById('delivery-charge').value = settings.deliveryCharge || '';
                    document.getElementById('small-size-image-url').value = settings.smallSizeImageUrl || '';
                    document.getElementById('large-size-image-url').value = settings.largeSizeImageUrl || '';
                    document.getElementById('feedback-section-title').value = settings.feedbackTitle || '';
                    document.getElementById('feedback-images-urls').value = (settings.feedbackImageUrls || []).join(', ');
                    document.getElementById('feedback-section-description').value = settings.feedbackDescription || '';

                    for (let i = 1; i <= 9; i++) {
                        document.getElementById(`reward-${i}`).value = settings[`reward${i}`] || '';
                    }

                    document.getElementById('enable-spinning-wheel').checked = settings.isSpinningWheelEnabled !== '0';
                    document.getElementById('enable-small-size').checked = settings.isSmallSizeEnabled !== '0';
                    document.getElementById('label-small-size').value = settings.smallSizeLabel || 'ছোট';
                    document.getElementById('enable-big-size').checked = settings.isBigSizeEnabled !== '0';
                    document.getElementById('label-big-size').value = settings.bigSizeLabel || 'বড়';
                }
            } catch (error) {
                console.error("Failed to load admin settings:", error);
            }
        }

        // NEW: Form submission for settings uses fetch to POST to the PHP backend
        productForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitButton = productForm.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            productSuccessMsg.textContent = 'Saving...';

            // Create FormData directly from the form
            const formData = new FormData(productForm);

            // Manually add checkbox values because unchecked boxes are not sent by default
            formData.append('enable-spinning-wheel', document.getElementById('enable-spinning-wheel').checked ? '1' : '0');
            formData.append('enable-small-size', document.getElementById('enable-small-size').checked ? '1' : '0');
            formData.append('enable-big-size', document.getElementById('enable-big-size').checked ? '1' : '0');

            formData.append('product-title', document.getElementById('product-title').value);
            formData.append('product-images-urls-56', document.getElementById('product-images-urls-56').value);
            formData.append('product-images-urls-32', document.getElementById('product-images-urls-32').value);
            formData.append('product-images-urls-99', document.getElementById('product-images-urls-99').value);
            formData.append('product-video-urls', document.getElementById('product-video-urls').value);
            formData.append('whatsapp-number', document.getElementById('whatsapp-number').value);
            formData.append('messenger-page-id', document.getElementById('messenger-page-id').value);
            formData.append('main-description-text', document.getElementById('main-description-text').value);
            formData.append('package-56-full-list', document.getElementById('package-56-full-list').value);
            formData.append('package-32-full-list', document.getElementById('package-32-full-list').value);
            formData.append('package-99-full-list', document.getElementById('package-99-full-list').value);
            formData.append('price-small-56', document.getElementById('price-small-56').value);
            formData.append('price-large-56', document.getElementById('price-large-56').value);
            formData.append('price-small-32', document.getElementById('price-small-32').value);
            formData.append('price-large-32', document.getElementById('price-large-32').value);
            formData.append('price-small-99', document.getElementById('price-small-99').value);
            formData.append('price-large-99', document.getElementById('price-large-99').value);
            formData.append('delivery-charge', document.getElementById('delivery-charge').value);
            formData.append('small-size-image-url', document.getElementById('small-size-image-url').value);
            formData.append('large-size-image-url', document.getElementById('large-size-image-url').value);
            formData.append('feedback-section-title', document.getElementById('feedback-section-title').value);
            formData.append('feedback-images-urls', document.getElementById('feedback-images-urls').value);
            formData.append('feedback-section-description', document.getElementById('feedback-section-description').value);
            formData.append('label-small-size', document.getElementById('label-small-size').value);
            formData.append('label-big-size', document.getElementById('label-big-size').value);

            for (let i = 1; i <= 9; i++) {
                formData.append(`reward-${i}`, document.getElementById(`reward-${i}`).value);
            }

            try {
                const response = await fetch('api/admin.php?action=save_settings', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    productSuccessMsg.textContent = 'All data saved successfully!';
                    setTimeout(() => {
                        productSuccessMsg.textContent = '';
                        productManagerView.style.display = 'none';
                    }, 2000);
                } else {
                    throw new Error(result.error || 'Failed to save settings.');
                }
            } catch (error) {
                console.error("Error saving data:", error);
                productSuccessMsg.textContent = `An error occurred: ${error.message}`;
            } finally {
                submitButton.disabled = false;
            }
        });

        checkLoginStatus();
    </script>
</body>

</html>