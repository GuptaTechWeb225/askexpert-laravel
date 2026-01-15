  <footer class="pt-5">
    <div class="container">
      <div class="row">
        <!-- Left Section: Logo & Address -->
        <div class="col-md-5 mb-4">
          <div class="mb-3">
            <h4 class="fw-bold text-white">
              <span class="text-lowercase text-secondary"> <a class="navbar-brand" href="#">
                  <img src="{{ getStorageImages(path: $web_config['web_logo'], type: 'logo') }}" alt="Logo">
                </a></span>
            </h4>
          </div>
          <p class="text-secondary small">Ask Expert is a Q&A platform where users can ask questions and get answers
            from verified experts.
            Professionals from different fields share their knowledge and experience here.
            Our mission is to provide accurate and trusted solutions for everyone.</p>
        </div>

        <!-- Quick Links -->
        <div class="col-5 col-md-3 mb-4">
          <h6 class="fw-bold footer-heading">Quick links</h6>
          <ul class="list-unstyled text-secondary small">
            <li><a href="{{ route('about-us') }}" class="text-secondary text-decoration-none">About us</a>
            </li>
            <li><a href="{{ route('price') }}" class="text-secondary text-decoration-none">Pricing</a>
            </li>
            <li><a href="{{ route('expert') }}"
                class="text-secondary text-decoration-none">Become a Expert</a></li>
            <li><a href="{{ route('help') }}" class="text-secondary text-decoration-none">Help</a></li>
          </ul>
        </div>
        <div class="col-4 col-md-3 mb-4">
          <h6 class="fw-bold footer-heading">Customers</h6>
          <ul class="list-unstyled text-secondary small ">
            <li><a href="{{ route('help') }}" class="text-secondary text-decoration-none">How it work</a></li>
            <li><a href="{{ route('customer.auth.login') }}" class="text-secondary text-decoration-none">Log in</a></li>
            <li><a href="{{route('customer.auth.sign-up')}}" class="text-secondary text-decoration-none">Register</a></li>
            <li><a href="{{ route('price') }}" class="text-secondary text-decoration-none">Category</a></li>
          </ul>
        </div>

        @if (!empty($web_config['social_media']))
        <div class="col-1 col-md-1 mb-4">
          <h6 class="fw-bold footer-heading text-nowrap">Links</h6>
          <div class="list-unstyled text-secondary small d-flex flex-column">

            @php $twitter = $web_config['social_media']->firstWhere('name', 'twitter'); @endphp
            <li>
              <a href="{{ $twitter->link ?? '#' }}" target="_blank" class="text-primary fs-5">
                <i class="fab fa-twitter"></i>
              </a>
            </li>

            {{-- FACEBOOK --}}
            @php $facebook = $web_config['social_media']->firstWhere('name', 'facebook'); @endphp
            <li>
              <a href="{{ $facebook->link ?? '#' }}" target="_blank" class="text-primary fs-5">
                <i class="fab fa-facebook"></i>
              </a>
            </li>

            {{-- INSTAGRAM --}}
            @php $instagram = $web_config['social_media']->firstWhere('name', 'instagram'); @endphp
            <li>
              <a href="{{ $instagram->link ?? '#' }}" target="_blank" class="text-primary fs-5">
                <i class="fab fa-instagram"></i>
              </a>
            </li>

            {{-- LINKEDIN --}}
            @php $linkedin = $web_config['social_media']->firstWhere('name', 'linkedin'); @endphp
            <li>
              <a href="{{ $linkedin->link ?? '#' }}" target="_blank" class="text-primary fs-5">
                <i class="fab fa-linkedin-in"></i>
              </a>
            </li>

          </div>
        </div>
        @endif

      </div>
    </div>
    <div class="container-fluid footer-bottom text-center py-3 mt-4 ">
      {{ $web_config['copyright_text'] }}
    </div>
  </footer>