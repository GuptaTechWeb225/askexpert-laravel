
// navbar toggle
window.addEventListener('scroll', function () {
  let navbar = document.querySelector('.custom-navbar');
  if (window.scrollY > 50) {
    navbar.classList.add('navbar-scrolled');
  } else {
    navbar.classList.remove('navbar-scrolled');
  }
});

// Carousel
const carouselElement = document.getElementById('howItWorksCarousel');
if (carouselElement) {
  const triggerCards = document.querySelectorAll('.step-trigger-card');
  carouselElement.addEventListener('slide.bs.carousel', event => {
    triggerCards.forEach(card => card.classList.remove('active'));
    const activeCard = document.querySelector(`.step-trigger-card[data-bs-slide-to="${event.to}"]`);
    if (activeCard) {
      activeCard.classList.add('active');
    }
  });
}

// Payment toggle
const creditRadio = document.getElementById("credit");
const paypalRadio = document.getElementById("paypal");
const debitRadio = document.getElementById("debit");

const creditBox = document.getElementById("credit-box");
const paypalBox = document.getElementById("paypal-box");
const debitBox = document.getElementById("debit-box");

if (creditRadio && paypalRadio && debitRadio && creditBox && paypalBox && debitBox) {
  function toggleBoxes() {
    creditBox.classList.add("d-none");
    paypalBox.classList.add("d-none");
    debitBox.classList.add("d-none");

    if (creditRadio.checked) creditBox.classList.remove("d-none");
    if (paypalRadio.checked) paypalBox.classList.remove("d-none");
    if (debitRadio.checked) debitBox.classList.remove("d-none");
  }

  creditRadio.addEventListener("change", toggleBoxes);
  paypalRadio.addEventListener("change", toggleBoxes);
  debitRadio.addEventListener("change", toggleBoxes);
  toggleBoxes();
}
 document.addEventListener("DOMContentLoaded", function () {
      const cards = document.querySelectorAll(".page-link");

      cards.forEach(card => {
        card.addEventListener("click", () => {
          const url = card.getAttribute("data-href");
          if (url) {
            // same tab navigation (relative path)
            window.location.href = url;
          }
        });
      });
    });