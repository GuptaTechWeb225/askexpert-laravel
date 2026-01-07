document.addEventListener('DOMContentLoaded', function () {
  // === Expert Categories Swiper ===
  const expertCategoriesSwiper = new Swiper('.expert-categories-slider', {
    slidesPerView: 1,
    slidesPerGroup: 1,
    spaceBetween: 30,
    loop: false,
    autoplay: {
      delay: 3000,
      disableOnInteraction: false,
    },
    pagination: {
      el: '.expert-categories-slider .swiper-pagination',
      clickable: true,
    },
    breakpoints: {
      576: {
        slidesPerView: 2,
        slidesPerGroup: 2,
        spaceBetween: 20,
      },
      768: {
        slidesPerView: 3,
        slidesPerGroup: 3,
        spaceBetween: 25,
      },
      992: {
        slidesPerView: 4,
        slidesPerGroup: 4,
        spaceBetween: 30,
      },
    },
  });

  // === Happy Members Swiper ===
  const happyMembersSwiper = new Swiper('#happyMembersSwiper', {
    breakpoints: {
      320: {
        slidesPerView: 1,
        slidesPerGroup: 1,
        spaceBetween: 20,
        grid: {
          rows: 1,
        },
      },
      992: {
        slidesPerView: 2,
        slidesPerGroup: 2,
        spaceBetween: 40,
        grid: {
          rows: 2,
          fill: 'row',
        },
      },
    },
    pagination: {
      el: '#happyMembersSwiper .swiper-pagination',
      clickable: true,
    },
  });

  // === We Help Section Swiper ===
  const weHelpSwiper = new Swiper('#we-help-section', {
    slidesPerView: 1,
    spaceBetween: 30,
    loop: true,
    pagination: {
      el: '#we-help-section .swiper-pagination',
      clickable: true,
    },
    breakpoints: {
      768: {
        slidesPerView: 2,
        spaceBetween: 30,
      },
      992: {
        slidesPerView: 3,
        spaceBetween: 30,
      },
    },
  });

  // === Our Experts Swiper ===
  const expertSwiper = new Swiper('#expertSwiper', {
    slidesPerView: 1,
    spaceBetween: 20,
    loop: true,
    navigation: {
      nextEl: '#expertSwiper .swiper-button-next',
      prevEl: '#expertSwiper .swiper-button-prev',
    },
    pagination: {
      el: '#expertSwiper .swiper-pagination',
      clickable: true,
    },
    breakpoints: {
      768: {
        slidesPerView: 2,
        spaceBetween: 30,
      },
      992: {
        slidesPerView: 3,
        spaceBetween: 30,
      },
    },
  });
});


const weHelpSwiper = new Swiper('#what-experts-say', {
    slidesPerView: 1,
    spaceBetween: 30,
    loop: true,
    pagination: {
      el: '#what-experts-say .swiper-pagination',
      clickable: true,
    },
    breakpoints: {
      768: {
        slidesPerView: 1,
        spaceBetween: 30,
      },
      992: {
        slidesPerView:1,
        spaceBetween: 30,
      },
      1010: {
        slidesPerView:2,
        spaceBetween: 30,
      },
    },
  });