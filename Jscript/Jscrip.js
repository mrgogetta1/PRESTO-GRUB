
const searchQuery = new URLSearchParams(window.location.search).get('search');

const allProducts = document.getElementById("allProducts");
const allStores = document.getElementById("allStores");
const searchProducts = document.getElementById("searchProducts");
const searchStores = document.getElementById("searchStores");

if (searchQuery) {
    allProducts.style.display = 'none';
    allStores.style.display = 'none';
}

// Redirect back to all products and stores after 15 seconds of inactivity
setTimeout(function() {
    if (searchQuery) {
        window.location.href = window.location.pathname;
    }
}, 15000);

document.getElementById("toggle-btn").addEventListener("click", function () {
    var sidebar = document.getElementById("sidebar");
    var mainContent = document.querySelector(".main-content");

    sidebar.classList.toggle("active");
    mainContent.classList.toggle("shifted");
});



document.addEventListener("DOMContentLoaded", () => {
const orderItems = [
{
name: "Grilled Salmon",
price: "$25.00",
rating: 4,
description: "Delicious grilled salmon fillet served with lemon butter sauce.",
image: "uploads/07b7e99bb01cca8732387d18919b2b4e.jpg",
},
{
name: "Margherita Pizza",
price: "$18.00",
rating: 5,
description: "Classic margherita pizza with fresh mozzarella and basil.",
image: "uploads/07b7e99bb01cca8732387d18919b2b4e.jpg",
},
// Add more items here
{
name: "Grilled Salmon",
price: "$25.00",
rating: 4,
description: "Delicious grilled salmon fillet served with lemon butter sauce.",
image: "uploads/07b7e99bb01cca8732387d18919b2b4e.jpg",
},
{
name: "Margherita Pizza",
price: "$18.00",
rating: 5,
description: "Classic margherita pizza with fresh mozzarella and basil.",
image: "uploads/07b7e99bb01cca8732387d18919b2b4e.jpg",
},
{
name: "Grilled Salmon",
price: "$25.00",
rating: 4,
description: "Delicious grilled salmon fillet served with lemon butter sauce.",
image: "uploads/07b7e99bb01cca8732387d18919b2b4e.jpg",
},
{
name: "Margherita Pizza",
price: "$18.00",
rating: 5,
description: "Classic margherita pizza with fresh mozzarella and basil.",
image: "uploads/07b7e99bb01cca8732387d18919b2b4e.jpg",
},
{
name: "Grilled Salmon",
price: "$25.00",
rating: 4,
description: "Delicious grilled salmon fillet served with lemon butter sauce.",
image: "uploads/07b7e99bb01cca8732387d18919b2b4e.jpg",
},
{
name: "Margherita Pizza",
price: "$18.00",
rating: 5,
description: "Classic margherita pizza with fresh mozzarella and basil.",
image: "uploads/07b7e99bb01cca8732387d18919b2b4e.jpg",
},
{
name: "Grilled Salmon",
price: "$25.00",
rating: 4,
description: "Delicious grilled salmon fillet served with lemon butter sauce.",
image: "uploads/07b7e99bb01cca8732387d18919b2b4e.jpg",
},
{
name: "Margherita Pizza",
price: "$18.00",
rating: 5,
description: "Classic margherita pizza with fresh mozzarella and basil.",
image: "uploads/07b7e99bb01cca8732387d18919b2b4e.jpg",
},
{
name: "Grilled Salmon",
price: "$25.00",
rating: 4,
description: "Delicious grilled salmon fillet served with lemon butter sauce.",
image: "uploads/07b7e99bb01cca8732387d18919b2b4e.jpg",
},
{
name: "Margherita Pizza",
price: "$18.00",
rating: 5,
description: "Classic margherita pizza with fresh mozzarella and basil.",
image: "uploads/07b7e99bb01cca8732387d18919b2b4e.jpg",
}
];

const itemsPerPage = 8;
let currentPage = 1;

const orderItemsContainer = document.querySelector(".order-items");
const prevBtn = document.querySelector(".prev-btn");
const nextBtn = document.querySelector(".next-btn");
const pageInfo = document.querySelector(".page-info");

function renderItems() {
orderItemsContainer.innerHTML = "";

const start = (currentPage - 1) * itemsPerPage;
const end = start + itemsPerPage;
const currentItems = orderItems.slice(start, end);

currentItems.forEach((item) => {
const orderCard = document.createElement("div");
orderCard.classList.add("order-card");
orderCard.innerHTML = `
<img src="${item.image}" alt="${item.name}" class="product-image">
<div class="product-info">
<h4 class="product-name">${item.name}</h4>
<p class="product-description">${item.description}</p>
<p class="product-price">${item.price}</p>
<a href="" class="order-now-btn">Add to Cart</a>
<a href="#" class="order-now-btn">Order Now</a>
</div>
`;
orderItemsContainer.appendChild(orderCard);
});

prevBtn.disabled = currentPage === 1;
nextBtn.disabled = currentPage >= Math.ceil(orderItems.length / itemsPerPage);
pageInfo.textContent = `Page ${currentPage}`;
}


prevBtn.addEventListener("click", () => {
if (currentPage > 1) {
currentPage--;
renderItems();
}
});

nextBtn.addEventListener("click", () => {
if (currentPage < Math.ceil(orderItems.length / itemsPerPage)) {
currentPage++;
renderItems();
}
});

renderItems();
});




// Cart Count Increment
let cartCount = 0;
document.querySelector('.cart-icon').addEventListener('click', () => {
cartCount++;
document.querySelector('.cart-count').textContent = cartCount;
});

// Profile dropdown toggle
const profile = document.querySelector('.profile');
const dropdown = document.querySelector('.dropdown');
profile.addEventListener('click', () => {
dropdown.classList.toggle('active');
});

// Close dropdown when clicking outside
document.addEventListener('click', (e) => {
if (!profile.contains(e.target)) {
  dropdown.classList.remove('active');
}
});

// Carousel for images
const carouselImages = document.querySelector('.carousel-images');
const images = document.querySelectorAll('.carousel-images img');
const prevButton = document.querySelector('.carousel-button.left');
const nextButton = document.querySelector('.carousel-button.right');
let currentIndex = 0;

function updateCarousel() {
    const offset = -currentIndex * 100; // Move by 100% of container width
    carouselImages.style.transform = `translateX(${offset}%)`;
}

nextButton.addEventListener('click', () => {
    currentIndex = (currentIndex + 1) % images.length;
    console.log('Next: ', currentIndex); // Debugging
    updateCarousel();
});

prevButton.addEventListener('click', () => {
    currentIndex = (currentIndex - 1 + images.length) % images.length;
    console.log('Prev: ', currentIndex); // Debugging
    updateCarousel();
});

function autoSlide() {
    currentIndex = (currentIndex + 1) % images.length;
    console.log('AutoSlide: ', currentIndex);
    updateCarousel();
}

setInterval(autoSlide, 3000);
updateCarousel();




// Handle the "See More" button functionality
const seeMoreButton = document.getElementById('see-more');
const moreItems = document.querySelectorAll('.more-item');
seeMoreButton.addEventListener('click', () => {
moreItems.forEach(item => {
  item.style.display = 'block'; // Show hidden items
});
seeMoreButton.style.display = 'none'; // Hide the "See More" button
});

// Recommended items carousel
const recommendedCarouselImages = document.querySelector('#recommended-items');
const recommendedItems = document.querySelectorAll('#recommended-items .item');
const prevRecommendedButton = document.querySelector('.recommendations .carousel-button.left');
const nextRecommendedButton = document.querySelector('.recommendations .carousel-button.right');
let currentRecommendedIndex = 0;

function updateRecommendedCarousel() {
const offset = -currentRecommendedIndex * 200;
recommendedCarouselImages.style.transform = `translateX(${offset}px)`;
}

nextRecommendedButton.addEventListener('click', () => {
currentRecommendedIndex = (currentRecommendedIndex + 1) % recommendedItems.length;
updateRecommendedCarousel();
});

prevRecommendedButton.addEventListener('click', () => {
currentRecommendedIndex = (currentRecommendedIndex - 1 + recommendedItems.length) % recommendedItems.length;
updateRecommendedCarousel();
});