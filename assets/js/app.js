/*
  OPTIMIZATION FIX:
  - This is your 1000+ lines of JavaScript, now in an external file.
  - It has been modified to "hydrate" the static HTML from index.php
    instead of fetching the content itself.
*/

// --- PHASE 1: HYDRATION ---
// Read the data that PHP embedded in the HTML
let ssrData = {};
try {
    const ssrDataElement = document.getElementById('ssr-data');
    if (ssrDataElement) {
        ssrData = JSON.parse(ssrDataElement.textContent);
    } else {
        console.error('SSR data script tag not found.');
    }
} catch (e) {
    console.error('Failed to parse SSR data', e);
}

// Get the data, or use empty objects as a fallback
const { product, settings, allOrders } = ssrData || { product: {}, settings: {}, allOrders: [] };


// --- PHASE 2: DEFINE ALL FUNCTIONS & VARIABLES ---

const modal = document.getElementById("imageModal");
const modalImg = document.getElementById("modalImage");
let currentModalIndex = 0;
let currentModalUrls = [];
let zoomLevel = 1.0;

function openModal(urls, index) {
    modal.classList.add('is-visible');
    currentModalUrls = urls.map(getGoogleDriveUrl);
    currentModalIndex = index;
    updateModalImage();
    resetZoom();
}

function updateModalImage() {
    if (currentModalIndex >= 0 && currentModalIndex < currentModalUrls.length) {
        modalImg.src = currentModalUrls[currentModalIndex];
    }
}

function changeModalSlide(n) {
    currentModalIndex += n;
    if (currentModalIndex >= currentModalUrls.length) {
        currentModalIndex = 0;
    } else if (currentModalIndex < 0) {
        currentModalIndex = currentModalUrls.length - 1;
    }
    updateModalImage();
    resetZoom();
}

function resetZoom() {
    zoomLevel = 1.0;
    modalImg.style.transform = 'scale(1.0)';
    modalImg.style.top = '0px';
    modalImg.style.left = '0px';
    modalImg.classList.remove('zoomed');
}

document.querySelector(".close-modal").onclick = () => {
    modal.classList.remove('is-visible');
}
document.querySelector(".modal-prev").onclick = () => changeModalSlide(-1);
document.querySelector(".modal-next").onclick = () => changeModalSlide(1);

document.getElementById('zoomInBtn').addEventListener('click', () => {
    if (zoomLevel < 3.0) {
        zoomLevel += 0.2;
        modalImg.style.transform = `scale(${zoomLevel})`;
        if (zoomLevel > 1.0) modalImg.classList.add('zoomed');
    }
});
document.getElementById('zoomOutBtn').addEventListener('click', () => {
    if (zoomLevel > 1.0) {
        zoomLevel -= 0.2;
        modalImg.style.transform = `scale(${zoomLevel})`;
    }
    if (zoomLevel <= 1.0) {
        resetZoom();
    }
});

let isDragging = false;
let startX, startY, startTop, startLeft;

modalImg.addEventListener('mousedown', (e) => {
    if (zoomLevel > 1.0) {
        isDragging = true;
        startX = e.pageX;
        startY = e.pageY;
        startTop = parseInt(modalImg.style.top) || 0;
        startLeft = parseInt(modalImg.style.left) || 0;
        modalImg.style.cursor = 'grabbing';
    }
});

document.addEventListener('mousemove', (e) => {
    if (isDragging) {
        const newLeft = startLeft + (e.pageX - startX);
        const newTop = startTop + (e.pageY - startY);
        modalImg.style.left = `${newLeft}px`;
        modalImg.style.top = `${newTop}px`;
    }
});

document.addEventListener('mouseup', () => {
    isDragging = false;
    if (zoomLevel > 1.0) {
        modalImg.style.cursor = 'move';
    } else {
        modalImg.style.cursor = 'default';
    }
});

function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return null;
}

let lastOrderId = null;
let rewards = [];
let lastOrderedSize = null;
let lastOrderedQuantity = null;

function getGoogleDriveUrl(originalUrl) {
    if (!originalUrl || typeof originalUrl !== 'string') return '';
    const match = originalUrl.match(/drive\.google\.com.*\/d\/([a-zA-Z0-9_-]+)/);
    if (match && match[1]) {
        const fileId = match[1];
        return `https://lh3.googleusercontent.com/d/${fileId}`;
    }
    return originalUrl;
}

function getDirectMediaUrl(originalUrl) {
    if (!originalUrl || typeof originalUrl !== 'string') return '';
    if (originalUrl.includes('dropbox.com')) {
        try {
            const url = new URL(originalUrl);
            url.searchParams.set('dl', '1');
            return url.toString();
        } catch (error) { return originalUrl; }
    }
    if (originalUrl.includes('drive.google.com')) {
        const match = originalUrl.match(/drive\.google\.com.*\/d\/([a-zA-Z0-9_-]+)/);
        if (match && match[1]) {
            const fileId = match[1];
            return `https://drive.google.com/uc?export=view&id=${fileId}`;
        }
    }
    return originalUrl;
}

function getYouTubeEmbedUrl(originalUrl) {
    if (!originalUrl || typeof originalUrl !== 'string') return null;
    if (originalUrl.includes('youtube.com') || originalUrl.includes('youtu.be')) {
        const youtubeRegex = /(?:youtube\.com\/(?:watch\?v=|embed\/|v\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/;
        const match = originalUrl.match(youtubeRegex);
        if (match && match[1]) {
            const videoId = match[1];
            return `https://www.youtube.com/embed/${videoId}?autoplay=0&mute=0`;
        }
    }
    return null;
}

let imageSlideIndex = 1;
let videoSlideIndex = 1;
let feedbackSlideIndex = 1;
let orderSlideIndex = 1;

function setupSlider(sliderId, slideClassName, dotsId, slideIndexVarName) {
    const slider = document.getElementById(sliderId);
    const dotsContainer = document.getElementById(dotsId);
    if (!slider || !dotsContainer) return () => { };
    slider.innerHTML = '';
    dotsContainer.innerHTML = '';
    const slideElements = [];
    let imageUrls = [];

    return (urls) => {
        if (typeof urls === 'string') {
            try { urls = JSON.parse(urls); } catch (e) { urls = []; }
        }
        if (!Array.isArray(urls)) urls = [];

        imageUrls = urls;
        if (!urls || urls.length === 0) {
            slider.style.display = 'none';
            dotsContainer.style.display = 'none';
            return;
        }
        slider.style.display = 'block';
        dotsContainer.style.display = 'block';
        urls.forEach((url, index) => {
            const slide = document.createElement('div');
            slide.className = slideClassName;

            if (slideClassName.includes('image')) {
                const directUrl = getGoogleDriveUrl(url);
                const img = document.createElement('img');
                img.src = directUrl;
                img.alt = `Product Image ${index + 1}`;
                img.onerror = function () { this.onerror = null; this.src = 'https://placehold.co/600x400/cccccc/ffffff?text=Image+Not+Found'; };
                img.onclick = () => openModal(imageUrls, index);
                slide.appendChild(img);
            } else {
                const youtubeEmbedUrl = getYouTubeEmbedUrl(url);
                if (youtubeEmbedUrl) {
                    slide.innerHTML = `<iframe class="w-full h-full" src="${youtubeEmbedUrl}" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;
                } else {
                    const directVideoUrl = getDirectMediaUrl(url);
                    slide.innerHTML = `<video controls playsinline webkit-playsinline loop muted autoplay class="w-full h-full"><source src="${directVideoUrl}" type="video/mp4"></video>`;
                }
            }

            slider.appendChild(slide);
            slideElements.push(slide);
            const dot = document.createElement('span');
            dot.className = 'dot';
            dot.onclick = () => window[`current${slideIndexVarName}Slide`](index + 1);
            dotsContainer.appendChild(dot);
        });
        const prevArrow = document.createElement('a');
        prevArrow.className = 'prev';
        prevArrow.onclick = () => window[`plus${slideIndexVarName}Slides`](-1);
        prevArrow.innerHTML = '❮';
        slider.appendChild(prevArrow);
        const nextArrow = document.createElement('a');
        nextArrow.className = 'next';
        nextArrow.onclick = () => window[`plus${slideIndexVarName}Slides`](1);
        nextArrow.innerHTML = '❯';
        slider.appendChild(nextArrow);
        const showSlidesFn = createShowSlidesFn(slideElements, dotsContainer, slideIndexVarName);
        window[`show${slideIndexVarName}Slides`] = showSlidesFn;
        showSlidesFn(1);
    };
}

function createShowSlidesFn(slides, dotsContainer, slideIndexVarName) {
    return function (n) {
        let slideIndex = n;
        if (slideIndex > slides.length) { slideIndex = 1; }
        if (slideIndex < 1) { slideIndex = slides.length; }

        slides.forEach(slide => {
            const video = slide.querySelector('video');
            if (video) video.pause();
            const iframe = slide.querySelector('iframe');
            if (iframe) iframe.src = iframe.src;
            slide.style.display = "none";
        });

        let dots = dotsContainer.getElementsByClassName("dot");
        for (let i = 0; i < dots.length; i++) {
            dots[i].className = dots[i].className.replace(" active", "");
        }

        slides[slideIndex - 1].style.display = "block";
        dots[slideIndex - 1].className += " active";
        window[slideIndexVarName] = slideIndex;
    };
}

window.plusImgSlides = (n) => window.showImgSlides(imageSlideIndex += n);
window.currentImgSlide = (n) => window.showImgSlides(imageSlideIndex = n);
window.plusVideoSlides = (n) => window.showVideoSlides(videoSlideIndex += n);
window.currentVideoSlide = (n) => window.showVideoSlides(videoSlideIndex = n);
window.plusFeedbackSlides = (n) => window.showFeedbackSlides(feedbackSlideIndex += n);
window.currentFeedbackSlide = (n) => window.showFeedbackSlides(feedbackSlideIndex = n);
window.plusOrderSlides = (n) => window.showOrderSlides(orderSlideIndex += n);
window.currentOrderSlide = (n) => window.showOrderSlides(orderSlideIndex = n);


let DELIVERY_FEE = 0;
let prices = {
    small_56: 300,
    big_56: 400,
    small_32: 200,
    big_32: 250
};
let sizeImageUrls = {
    small: '',
    big: ''
};

function generateList(listString, titleElId, ulElId, subtitleElId) {
    const titleElement = document.getElementById(titleElId);
    const ulElement = document.getElementById(ulElId);
    const subtitleElement = document.getElementById(subtitleElId);

    ulElement.innerHTML = '';

    if (listString && typeof listString === 'string') {
        const lines = listString.split('\n').filter(line => line.trim() !== '');
        if (lines.length > 0) {
            titleElement.innerText = lines.shift();
            if (subtitleElement && lines.length > 0) subtitleElement.classList.remove('hidden');
            lines.forEach(itemText => {
                const li = document.createElement('li');
                li.innerText = itemText;
                ulElement.appendChild(li);
            });
        }
    }
}

async function fetchAndApplyInitialData() {
    try {
        // Corrected API endpoint
        const response = await fetch('api/api.php?action=get_initial_data');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();

        const { product, settings, allOrders } = data;

        // Setup Product Sliders
        const renderImageSlider = setupSlider('imageSlider', 'slider-image', 'imageDots', 'Img');
        const renderVideoSlider = setupSlider('videoSlider', 'slider-video', 'videoDots', 'Video');
        if (product) {
            document.getElementById('productTitle').innerText = product.title || "Default Title";
            // The backend now sends arrays directly, no need for JSON.parse
            renderImageSlider(product.imageUrls);
            renderVideoSlider(product.videoUrls);
        } else {
            renderImageSlider([]);
            renderVideoSlider([]);
        }

        // Apply all settings (This part remains largely the same)
        if (settings) {
            if (settings.whatsappNumber) {
                document.getElementById('whatsapp-chat-link').href = `https://wa.me/${settings.whatsappNumber}`;
                document.getElementById('whatsapp-chat-link').style.display = 'flex';
            }
            if (settings.messengerPageId) {
                document.getElementById('messenger-chat-link').href = `https://m.me/${settings.messengerPageId}`;
                document.getElementById('messenger-chat-link').style.display = 'flex';
            }
            if (settings.mainDescription) document.getElementById('main-description').innerText = settings.mainDescription;

            generateList(settings.package56List, 'package-56-title', 'package-56-ul', 'package-56-subtitle');
            generateList(settings.package32List, 'package-32-title', 'package-32-ul', 'package-32-subtitle');

            prices.small_56 = Number(settings.priceSmall56) || prices.small_56;
            prices.big_56 = Number(settings.priceLarge56) || prices.big_56;
            prices.small_32 = Number(settings.priceSmall32) || prices.small_32;
            prices.big_32 = Number(settings.priceLarge32) || prices.big_32;
            DELIVERY_FEE = (settings.deliveryCharge !== undefined && settings.deliveryCharge !== null && settings.deliveryCharge !== '') ? Number(settings.deliveryCharge) : DELIVERY_FEE;

            sizeImageUrls.small = settings.smallSizeImageUrl || '';
            sizeImageUrls.big = settings.largeSizeImageUrl || '';

            rewards = [];
            for (let i = 1; i <= 9; i++) {
                rewards.push(settings[`reward${i}`] || `Prize ${i}`);
            }
            setupWheel();

            // Feature Controls (This part remains the same)
            if (settings.isSpinningWheelEnabled === '0') {
                document.getElementById('spin-container').style.display = 'none';
                const successMsg = document.getElementById('successMessage');
                const lastP = successMsg.querySelector('p:last-of-type');
                const spinBtn = successMsg.querySelector('#scrollToSpinBtn');
                if (lastP) lastP.style.display = 'none';
                if (spinBtn) spinBtn.style.display = 'none';
            }

            const isSmallEnabled = settings.isSmallSizeEnabled !== '0';
            const isBigEnabled = settings.isBigSizeEnabled !== '0';

            const smallSizeRadio = document.getElementById('size_small');
            const smallSizeLabel = document.querySelector('label[for="size_small"]');
            const bigSizeRadio = document.getElementById('size_big');
            const bigSizeLabel = document.querySelector('label[for="size_big"]');


            if (smallSizeLabel) smallSizeLabel.textContent = settings.smallSizeLabel || 'ছোট ৩/৪ ইঞ্ছি';
            if (bigSizeLabel) bigSizeLabel.textContent = settings.bigSizeLabel || 'বড় ৬/৪ ইঞ্ছি';

            if (!isSmallEnabled && !isBigEnabled) {
                const sizeContainer = smallSizeRadio?.parentElement?.parentElement;
                if (sizeContainer) sizeContainer.style.display = 'none';

            } else if (!isSmallEnabled) {
                if (smallSizeLabel) smallSizeLabel.style.display = 'none';
                if (bigSizeRadio) bigSizeRadio.checked = true;

            } else if (!isBigEnabled) {
                if (bigSizeLabel) bigSizeLabel.style.display = 'none';
            }

            // Customer Feedback
            const feedbackSection = document.getElementById('customer-feedback-section');
            if (settings.feedbackTitle && settings.feedbackDescription && settings.feedbackImageUrls) {
                document.getElementById('feedback-title').innerText = settings.feedbackTitle;
                document.getElementById('feedback-description').innerText = settings.feedbackDescription;
                const renderFeedbackSlider = setupSlider('feedback-slider-container', 'slider-image', 'feedback-dots-container', 'Feedback');
                renderFeedbackSlider(settings.feedbackImageUrls);
                feedbackSection.classList.remove('hidden');
            }
        }

        // Display Recent Orders
        if (allOrders && allOrders.length > 0) {
            setupOrderSlider(allOrders);
        }

        updatePrice();
    } catch (error) {
        console.error("Error fetching initial site data:", error);
    }
}


function maskPhoneNumber(phone) {
    if (!phone || phone.length < 11) return 'N/A';
    return `${phone.substring(0, 3)}*****${phone.substring(8)}`;
}

function timeAgo(dateString) {
    // const date = new Date(dateString.replace(/-/g, '/')); // <-- আপনার পুরোনো কোড

    // নতুন কোড: সার্ভারের 'YYYY-MM-DD HH:MM:SS' ফরম্যাটকে 'YYYY-MM-DDTHH:MM:SSZ' এ রূপান্তর করা
    // 'Z' যোগ করার ফলে JavaScript বুঝতে পারে যে এটি একটি UTC (GMT+0) টাইম।
    const date = new Date(dateString.replace(' ', 'T') + 'Z');

    // এখনকার সময় (ব্রাউজারের লোকাল টাইম)
    const now = new Date();

    // পার্থক্য সেকেন্ডে গণনা করা
    const seconds = Math.floor((now - date) / 1000);

    let interval = seconds / 31536000;
    if (interval > 1) return `${Math.floor(interval)} বছর আগে`;

    interval = seconds / 2592000;
    if (interval > 1) return `${Math.floor(interval)} মাস আগে`;

    interval = seconds / 86400;
    if (interval > 1) return `${Math.floor(interval)} দিন আগে`;

    interval = seconds / 3600;
    if (interval > 1) return `${Math.floor(interval)} ঘন্টা আগে`;

    interval = seconds / 60;
    if (interval > 1) return `${Math.floor(interval)} মিনিট আগে`;

    return `এই মাত্র`;
}

function setupOrderSlider(orders) {
    const slider = document.getElementById('recent-orders-slider');
    const dotsContainer = document.getElementById('recent-orders-dots');
    const section = document.getElementById('recent-orders-section');

    if (!orders || orders.length === 0) return;

    slider.innerHTML = '';
    dotsContainer.innerHTML = '';
    const slideElements = [];

    orders.forEach((orderData, index) => {
        const slide = document.createElement('div');
        slide.className = 'slider-image';
        slide.style.padding = '1.5rem';
        slide.style.backgroundColor = '#f0fdf4';
        slide.style.borderRadius = '0.5rem';
        slide.style.textAlign = 'center';

        slide.innerHTML = `
                    <div class="space-y-2">
                        <p class="text-sm text-gray-500">${timeAgo(orderData.timestamp)}</p>
                        <p class="font-bold text-lg text-green-800">${orderData.name}</p>
                        <p class="text-gray-600">${orderData.address}</p>
                        <p class="mt-2 text-md font-semibold text-gray-700">অর্ডার করেছেন: <span class="text-blue-600">${orderData.product}</span></p>
                        <p class="text-sm text-gray-500">ফোন: ${maskPhoneNumber(orderData.phone)}</p>
                    </div>
                `;
        slider.appendChild(slide);
        slideElements.push(slide);

        const dot = document.createElement('span');
        dot.className = 'dot';
        dot.onclick = () => currentOrderSlide(index + 1);
        dotsContainer.appendChild(dot);
    });

    const prevArrow = document.createElement('a');
    prevArrow.className = 'prev';
    prevArrow.onclick = () => plusOrderSlides(-1);
    prevArrow.innerHTML = '❮';
    slider.appendChild(prevArrow);

    const nextArrow = document.createElement('a');
    nextArrow.className = 'next';
    nextArrow.onclick = () => plusOrderSlides(1);
    nextArrow.innerHTML = '❯';
    slider.appendChild(nextArrow);

    window.showOrderSlides = createShowSlidesFn(slideElements, dotsContainer, 'orderSlideIndex');
    window.showOrderSlides(1);

    section.classList.remove('hidden');
}


const orderForm = document.getElementById('orderForm');
orderForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const phoneInput = orderForm.phone.value;
    const validPrefixes = ["013", "014", "015", "016", "017", "018", "019"];
    const phonePrefix = phoneInput.substring(0, 3);

    if (phoneInput.length !== 11 || !validPrefixes.includes(phonePrefix)) {
        alert("দয়া করে আপনার একটি সঠিক ১১ ডিজিটের মোবাইল নাম্বার দিন। ভুল নাম্বার দিয়ে অর্ডার করা যাবে না।");
        return;
    }

    const submitButton = orderForm.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.innerText = 'অর্ডার জমা হচ্ছে...';

    const formData = new FormData(orderForm);
    formData.append('fbp', getCookie('_fbp'));
    formData.append('fbc', getCookie('_fbc'));
    formData.append('deliveryFee', DELIVERY_FEE);

    try {
        // Corrected API endpoint
        const response = await fetch('api/api.php?action=place_order', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (!response.ok || result.error) {
            throw new Error(result.error || 'Failed to place order.');
        }

        lastOrderId = result.id; // The database row ID
        const generatedOrderId = result.orderId;
        document.getElementById('orderIdDisplay').textContent = generatedOrderId;

        lastOrderedSize = document.querySelector('input[name="size"]:checked').value;
        lastOrderedQuantity = document.querySelector('input[name="quantity"]:checked').value;

        orderForm.style.display = 'none';
        document.querySelector('.my-6.text-center.bg-gray-100').style.display = 'none';
        document.getElementById('productOptions').style.display = 'none';
        document.getElementById('successMessage').classList.remove('hidden');

        fbq('track', 'Purchase', {
            currency: "BDT",
            value: parseInt(orderForm.totalPriceInput.value)
        }, {
            eventID: generatedOrderId
        });

        dataLayer.push({
            'event': 'purchase',
            'ecommerce': {
                'transaction_id': generatedOrderId,
                'value': parseInt(orderForm.totalPriceInput.value),
                'currency': 'BDT',
                'items': [{
                    'item_id': `${lastOrderedSize}_${lastOrderedQuantity}`,
                    'item_name': selectedProductEl.value,
                    'price': parseInt(orderForm.productPriceInput.value)
                }]
            },
            // THIS IS THE KEY:
            // We use the real Order ID as the Event ID for Meta.
            'meta_event_id': generatedOrderId
        });

        document.getElementById('spin-btn').disabled = false;
        document.getElementById('spin-activation-msg').style.display = 'none';


    } catch (e) {
        console.error("Error submitting order: ", e);
        alert("An error occurred. Please try again.");
        submitButton.disabled = false;
        submitButton.innerText = 'অর্ডার কনফার্ম করুন';
    }
});

document.getElementById('copyOrderIdBtn').addEventListener('click', () => {
    const orderId = document.getElementById('orderIdDisplay').textContent;
    navigator.clipboard.writeText(orderId).then(() => {
        const btn = document.getElementById('copyOrderIdBtn');
        const originalText = btn.textContent;
        btn.textContent = 'কপি হয়েছে!';
        setTimeout(() => { btn.textContent = originalText; }, 2000);
    }).catch(err => { console.error('Failed to copy: ', err); });
});

const productOptions = document.getElementById('productOptions');
const dynamicPriceEl = document.getElementById('dynamicPrice');
const deliveryFeeEl = document.getElementById('deliveryFee');
const totalPriceEl = document.getElementById('totalPrice');
const selectedProductEl = document.getElementById('selectedProduct');
const productPriceInputEl = document.getElementById('productPriceInput');
const totalPriceInputEl = document.getElementById('totalPriceInput');
const sizeImagePreview = document.getElementById('size-image-preview');

function updatePrice() {
    const selectedSize = document.querySelector('input[name="size"]:checked').value;
    const selectedQuantity = document.querySelector('input[name="quantity"]:checked').value;
    const key = `${selectedSize}_${selectedQuantity}`;

    const productPrice = prices[key] || 0;
    const total = productPrice + DELIVERY_FEE;

    dynamicPriceEl.textContent = productPrice;

    // Handle free delivery display
    const deliveryFeeRow = document.getElementById('deliveryFeeRow');
    if (DELIVERY_FEE === 0) {
        if (deliveryFeeRow) {
            deliveryFeeRow.innerHTML = '<span class="font-semibold text-green-600">🚚 সারা বাংলাদেশে ফ্রি হোম ডেলিভারি</span><span class="font-bold text-red-600 bg-yellow-200 px-3 py-1 rounded-full text-sm inline-block shadow-sm">🎁 ফ্রি ওমরাহ কুপন</span><span id="deliveryFee" class="hidden">0</span>';
            deliveryFeeRow.classList.add("flex", "flex-col", "items-center", "gap-1");
        }
    } else {
        if (deliveryFeeRow) {
            deliveryFeeRow.innerHTML = `ডেলিভারি চার্জ: <span id="deliveryFee" class="font-semibold">${DELIVERY_FEE}</span> টাকা`;
            deliveryFeeRow.classList.remove("flex", "flex-col", "items-center", "gap-1");
        }
    }
    totalPriceEl.textContent = total;

    const sizeLabelEl = document.querySelector(`label[for="size_${selectedSize}"]`);
    const sizeText = sizeLabelEl ? sizeLabelEl.textContent : (selectedSize === 'small' ? 'ছোট' : 'বড়');
    const quantityText = `${selectedQuantity} পিস`;
    selectedProductEl.value = `${sizeText} - ${quantityText}`;
    productPriceInputEl.value = productPrice;
    totalPriceInputEl.value = total;

    const imageUrl = sizeImageUrls[selectedSize];
    if (imageUrl) {
        sizeImagePreview.src = getGoogleDriveUrl(imageUrl);
        sizeImagePreview.classList.remove('hidden');
    } else {
        sizeImagePreview.classList.add('hidden');
    }

    const package56Details = document.getElementById('package-56-details');
    const package32Details = document.getElementById('package-32-details');

    if (selectedQuantity === '56') {
        package56Details.classList.remove('hidden');
        package32Details.classList.add('hidden');
    } else if (selectedQuantity === '32') {
        package32Details.classList.remove('hidden');
        package56Details.classList.add('hidden');
    } else {
        package56Details.classList.add('hidden');
        package32Details.classList.add('hidden');
    }
}

const wheel = document.getElementById('wheel');
const spinBtn = document.getElementById('spin-btn');
const spinResultEl = document.getElementById('spin-result');
const segmentCount = 9;

function setupWheel() {
    wheel.innerHTML = '';
    const segmentAngle = 360 / segmentCount;
    const colors = ['#fecaca', '#fed7aa', '#fef08a', '#d9f99d', '#bfdbfe', '#e9d5ff', '#fed7e2', '#ccfbf1', '#d1d5db'];
    for (let i = 0; i < segmentCount; i++) {
        const segment = document.createElement('div');
        segment.className = 'segment';
        segment.style.transform = `rotate(${i * segmentAngle}deg)`;
        segment.style.backgroundColor = colors[i % colors.length];
        const rewardText = rewards[i] || `Prize ${i + 1}`;
        segment.innerHTML = `<span>${rewardText}</span>`;
        wheel.appendChild(segment);
    }
}

spinBtn.addEventListener('click', () => {
    if (!lastOrderId) {
        alert("Please place an order first to spin the wheel.");
        return;
    }
    spinBtn.disabled = true;
    spinResultEl.textContent = '';

    // ... (rest of the spinning logic is the same)
    let validSlots = [];
    if (lastOrderedSize === 'small' && lastOrderedQuantity === '56') {
        validSlots = [0, 1, 2, 3];
    } else if (lastOrderedSize === 'big' && lastOrderedQuantity === '56') {
        validSlots = [0, 1, 2, 3, 4];
    } else {
        validSlots = [0, 1, 2, 3, 4];
    }
    const winningSegmentIndex = validSlots[Math.floor(Math.random() * validSlots.length)];
    const segmentAngle = 360 / segmentCount;
    const randomOffsetInSegment = Math.random() * (segmentAngle - 10) + 5;
    const targetAngle = 360 - (winningSegmentIndex * segmentAngle) - randomOffsetInSegment;
    const randomSpins = Math.floor(Math.random() * 5) + 5;
    const totalRotation = (randomSpins * 360) + targetAngle;
    wheel.style.transform = `rotate(${totalRotation}deg)`;
    // ... (end of spinning logic)

    setTimeout(async () => {
        const winningReward = rewards[winningSegmentIndex];
        spinResultEl.textContent = `অভিনন্দন! আপনি জিতেছেন: ${winningReward}`;

        if (lastOrderId && winningReward) {
            try {
                // Corrected API endpoint
                await fetch('api/api.php?action=update_order_info', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id: lastOrderId,
                        reward: winningReward
                    })
                });
            } catch (error) {
                console.error("Error updating order with reward:", error);
            }
        }
    }, 5500);
});

const trackOrderForm = document.getElementById('trackOrderForm');
trackOrderForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const trackButton = trackOrderForm.querySelector('button[type="submit"]');
    trackButton.disabled = true;
    trackButton.textContent = 'অনুসন্ধান চলছে...';

    const trackingInput = document.getElementById('trackingInput').value.trim();
    const trackingResultDiv = document.getElementById('trackingResult');
    const trackingErrorP = document.getElementById('trackingError');

    trackingResultDiv.innerHTML = '';
    trackingResultDiv.classList.add('hidden');
    trackingErrorP.classList.add('hidden');

    try {
        // Corrected API endpoint
        const response = await fetch(`api/api.php?action=track_order&query=${encodeURIComponent(trackingInput)}`);
        const result = await response.json();

        if (result.error) throw new Error(result.error);

        if (result.order) {
            displayOrderDetails(result.order);
        } else {
            trackingErrorP.textContent = 'দুঃখিত, এই তথ্য দিয়ে কোনো অর্ডার খুঁজে পাওয়া যায়নি।';
            trackingErrorP.classList.remove('hidden');
        }

    } catch (error) {
        console.error("Error searching for order:", error);
        trackingErrorP.textContent = 'অনুসন্ধান করতে একটি সমস্যা হয়েছে। আবার চেষ্টা করুন।';
        trackingErrorP.classList.remove('hidden');
    } finally {
        trackButton.disabled = false;
        trackButton.textContent = 'ট্র্যাক করুন';
    }
});

function displayOrderDetails(data) {
    const container = document.getElementById('trackingResult');
    const statusColors = {
        'Pending': 'text-yellow-600',
        'Confirmed': 'text-indigo-600',
        'Preparing': 'text-purple-600',
        'Shipped': 'text-blue-600',
        'Delivered': 'text-green-600',
        'Canceled': 'text-red-600'
    };
    const statusColor = statusColors[data.status] || 'text-gray-700';

    let editButtonHtml = '';
    if (data.status === 'Pending' || data.status === 'Canceled') {
        editButtonHtml = `
                    <div class="text-right mt-4">
                        <button id="editOrderBtn" data-id="${data.id}" class="bg-yellow-500 text-white font-bold py-2 px-4 rounded-md hover:bg-yellow-600 transition">তথ্য এডিট করুন</button>
                    </div>
                `;
    }

    container.innerHTML = `
                <div class="border p-4 rounded-md bg-gray-50">
                    <div id="orderDetailsFields" class="space-y-3 text-gray-800">
                        <p><strong>অর্ডার নাম্বার:</strong> <span class="font-mono">${data.orderId || 'N/A'}</span></p>
                        <p><strong>স্ট্যাটাস:</strong> <span class="font-bold ${statusColor}">${data.status}</span></p>
                        <p><strong>পুরস্কার:</strong> ${data.reward}</p>
                        <hr class="my-3">
                        <p><strong>নাম:</strong> ${data.name}</p>
                        <p><strong>ঠিকানা:</strong> ${data.address}</p>
                        <p><strong>ফোন:</strong> ${data.phone}</p>
                        <p><strong>বিশেষ দ্রষ্টব্য:</strong> ${data.note || 'N/A'}</p>
                    </div>
                    ${editButtonHtml}
                </div>
            `;
    container.classList.remove('hidden');

    const editButton = document.getElementById('editOrderBtn');
    if (editButton) {
        editButton.addEventListener('click', (e) => showEditForm(data));
    }
}


function showEditForm(data) {
    const container = document.getElementById('orderDetailsFields');
    container.innerHTML = `
                <div class="space-y-4">
                    <div>
                        <label for="edit-name" class="block mb-1 font-medium text-gray-700">আপনার নাম <span class="text-red-500">*</span></label>
                        <input type="text" id="edit-name" class="w-full p-2 border border-gray-300 rounded-md" value="${data.name}" required>
                    </div>
                    <div>
                        <label for="edit-address" class="block mb-1 font-medium text-gray-700">আপনার সম্পূর্ণ ঠিকানা <span class="text-red-500">*</span></label>
                        <textarea id="edit-address" rows="3" class="w-full p-2 border border-gray-300 rounded-md">${data.address}</textarea>
                    </div>
                    <div>
                        <label for="edit-phone" class="block mb-1 font-medium text-gray-700">আপনার ফোন নম্বর <span class="text-red-500">*</span></label>
                        <input type="tel" id="edit-phone" class="w-full p-2 border border-gray-300 rounded-md" value="${data.phone}" required>
                    </div>
                     <div>
                        <label for="edit-note" class="block mb-1 font-medium text-gray-700">বিশেষ দ্রষ্টব্য</label>
                        <textarea id="edit-note" rows="2" class="w-full p-2 border border-gray-300 rounded-md">${data.note || ''}</textarea>
                    </div>
                </div>
            `;

    const actionsDiv = document.querySelector('#trackingResult .text-right');
    actionsDiv.innerHTML = `
                <button id="saveChangesBtn" data-id="${data.id}" class="bg-green-600 text-white font-bold py-2 px-4 rounded-md hover:bg-green-700 transition">সেভ করুন</button>
                <button id="cancelEditBtn" class="bg-gray-500 text-white font-bold py-2 px-4 rounded-md hover:bg-gray-600 transition ml-2">বাতিল</button>
            `;

    document.getElementById('cancelEditBtn').addEventListener('click', () => displayOrderDetails(data));

    document.getElementById('saveChangesBtn').addEventListener('click', async (e) => {
        const saveButton = e.target;
        saveButton.disabled = true;
        saveButton.textContent = 'সেভ হচ্ছে...';

        const updatedData = {
            id: e.target.dataset.id,
            name: document.getElementById('edit-name').value,
            address: document.getElementById('edit-address').value,
            phone: document.getElementById('edit-phone').value,
            note: document.getElementById('edit-note').value,
        };

        if (!updatedData.name || !updatedData.address || !updatedData.phone) {
            alert("দয়া করে নাম, ঠিকানা এবং ফোন নাম্বার পূরণ করুন।");
            saveButton.disabled = false;
            saveButton.textContent = 'সেভ করুন';
            return;
        }

        try {
            // Corrected API endpoint
            const response = await fetch('api/api.php?action=update_order_info', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(updatedData)
            });
            const result = await response.json();
            if (result.error) throw new Error(result.error);

            alert("আপনার তথ্য সফলভাবে আপডেট করা হয়েছে।");
            // We need the original data to merge with the updated data
            // This requires a small change in how we call showEditForm
            const originalData = data;
            const refreshedData = { ...originalData, ...updatedData };
            displayOrderDetails(refreshedData);
        } catch (error) {
            console.error("Error updating document: ", error);
            alert("তথ্য আপডেট করতে একটি সমস্যা হয়েছে।");
            saveButton.disabled = false;
            saveButton.textContent = 'সেভ করুন';
        }
    });
}

let visitorDocId = null;
let visitStartTime = null;
async function trackVisitor() {
    try {
        const response = await fetch('api/api.php?action=track_visitor', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ userAgent: navigator.userAgent })
        });
        const result = await response.json();
        if (result.id) {
            visitorDocId = result.id;
            visitStartTime = new Date();
        }
    } catch (error) {
        console.error("Error tracking visitor.", error);
    }
}

async function updateVisitDuration() {
    if (visitorDocId && visitStartTime) {
        const duration = new Date() - visitStartTime;
        await fetch('api/api.php?action=track_visitor', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: visitorDocId, durationMillis: duration })
        });
    }
}

function checkUrlForOrderId() {
    const params = new URLSearchParams(window.location.search);
    const orderId = params.get('orderId');
    if (orderId) {
        const trackingInput = document.getElementById('trackingInput');
        const trackButton = document.querySelector('#trackOrderForm button[type="submit"]');
        const trackingSection = document.getElementById('order-tracking-section');

        if (trackingInput && trackButton && trackingSection) {
            trackingInput.value = orderId;
            trackButton.click();
            setTimeout(() => {
                trackingSection.scrollIntoView({ behavior: 'smooth' });
            }, 2000);
        }
    }
}

productOptions.addEventListener('change', updatePrice);

document.addEventListener('DOMContentLoaded', () => {
    fetchAndApplyInitialData();
    trackVisitor();
    updatePrice();

    // --- START: Barikoi Autocomplete Logic (Updated with Key Rotation) ---
    const addressTextarea = document.getElementById('address');
    const suggestionsContainer = document.getElementById('addressSuggestions');

    // ১. আপনার সব API কী এখানে অ্যারে হিসেবে যোগ করুন
    const barikoiApiKeys = [
        'bkoi_89a34f36979feaa997621cf592a3f242888e7095d94a1a2e0888fd26c19b38af',
        'bkoi_b9ccd4cbf0a9076ff38c77ba372e70158083c25f23fbb2675bd44850c4c1c25f',
        'bkoi_c05651b9d86a3c784969c3042716b352e9f71aed004bf565ba1966cd39298504',
        'bkoi_1490586bb8575b4fb7f9838ccec41728c04cf6082c253d84ed16a20428e5d780'
    ];

    let debounceTimer;

    // ২. এই নতুন ফাংশনটি অ্যারে থেকে র‍্যান্ডমলি একটি কী সিলেক্ট করবে
    function getRandomApiKey() {
        const randomIndex = Math.floor(Math.random() * barikoiApiKeys.length);
        return barikoiApiKeys[randomIndex];
    }

    addressTextarea.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        const query = addressTextarea.value;

        if (query.length < 3) {
            suggestionsContainer.classList.add('hidden');
            suggestionsContainer.innerHTML = '';
            return;
        }

        debounceTimer = setTimeout(async () => {
            // ৩. প্রতিবার কল করার সময় র‍্যান্ডম কী সিলেক্ট করা হচ্ছে
            const selectedApiKey = getRandomApiKey();

            try {
                const response = await fetch(`https://barikoi.xyz/v2/api/search/autocomplete/place?api_key=${selectedApiKey}&q=${encodeURIComponent(query)}`);

                if (!response.ok) {
                    console.warn(`API request failed with key: ${selectedApiKey.substring(0, 10)}...`); // ডিবাগিং এর জন্য
                    suggestionsContainer.classList.add('hidden');
                    return;
                }

                const data = await response.json();
                suggestionsContainer.innerHTML = '';

                if (data.status === 200 && data.places && data.places.length > 0) {
                    data.places.forEach(place => {
                        const suggestionEl = document.createElement('div');
                        suggestionEl.className = 'p-2 cursor-pointer hover:bg-gray-100 border-b border-gray-100 last:border-b-0';
                        suggestionEl.textContent = place.address;

                        suggestionEl.addEventListener('click', () => {
                            addressTextarea.value = place.address;
                            suggestionsContainer.classList.add('hidden');
                            suggestionsContainer.innerHTML = '';
                        });

                        suggestionsContainer.appendChild(suggestionEl);
                    });
                    suggestionsContainer.classList.remove('hidden');
                } else {
                    suggestionsContainer.classList.add('hidden');
                }
            } catch (error) {
                console.error("Error fetching Barikoi suggestions:", error);
                suggestionsContainer.classList.add('hidden');
            }
        }, 300);
    });

    document.addEventListener('click', (e) => {
        if (!addressTextarea.contains(e.target) && !suggestionsContainer.contains(e.target)) {
            suggestionsContainer.classList.add('hidden');
        }
    });
    // --- END: Barikoi Autocomplete Logic ---


});

window.addEventListener('load', () => checkUrlForOrderId());
document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'hidden') updateVisitDuration();
});
window.addEventListener('beforeunload', updateVisitDuration);

document.getElementById('scrollToSpinBtn').addEventListener('click', () => {
    const spinContainer = document.getElementById('spin-container');
    if (spinContainer) {
        spinContainer.scrollIntoView({ behavior: 'smooth' });
        setTimeout(() => {
            const spinButton = document.getElementById('spin-btn');
            if (spinButton && !spinButton.disabled) {
                spinButton.click();
            }
        }, 1000);
    }
});