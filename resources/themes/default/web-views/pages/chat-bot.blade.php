  @php
  use Illuminate\Support\Facades\Vite;
  @endphp
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with {{ $expert?->f_name }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ theme_asset('public/assets/front-end/css/theme.css') }}">
    <link rel="stylesheet" href="{{ theme_asset('public/assets/front-end/css/cat-chatboat.css') }}">
    <link rel="stylesheet" href="{{dynamicAsset(path:'public/assets/back-end/vendor/fontawesome-free/css/all.min.css')}}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/bootstrap.min.css')}}">
    <meta name="user-id" content="{{ auth('customer')->id() }}">
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/style.css') }}">
    <script src="https://download.agora.io/sdk/release/AgoraRTC_N.js"></script>
    <audio id="ringtone" loop>
      <source src="{{ dynamicAsset(path: 'public/assets/back-end/sound/notification.mp3') }}" type="audio/mpeg">
    </audio>

    @vite(['resources/js/app.js'])


    <style>
      #video-container {
        background: #000;
        border-radius: 12px;
        overflow: hidden;
        margin: 15px 0;
        position: relative;
        height: 400px;
      }

      #remote-media {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }

      #local-media {
        width: 150px;
        height: 150px;
        border: 3px solid white;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
      }

      #remote-media video,
      #local-media video {
        position: relative !important;
        object-fit: cover !important;
      }

      #remote-media {
        background-color: #000;
        display: flex;
        align-items: center;
        justify-content: center;
      }
    </style>
  </head>

  <body>
    <div class="whatsapp-container" x-data="chatComponent({{ $chat->id }})" x-init="init()">
      <!-- Header -->
      <div class="chat-header">
        <div class="header-info d-flex position-relative expert-hover-wrapper">

          <img src="{{ getStorageImages(path: $expert?->image_full_url, type: 'avatar') }}"
            alt="{{ $expert?->f_name }}"
            class="expert-avatar">

          <div class="ms-2">
            <h6 class="expert-name text-white">{{ $expert?->f_name ?? 'System User' }} {{ $expert?->l_name }}</h6>
            <p class="online-status">
              <span class="online-dot" :class="{ 'online': expertOnline }"></span>
              <span x-text="expertOnline ? 'Active' : 'Offline'"></span>
            </p>
            <small class="text-white ms-2" x-show="typing" style="display:none;">typing...</small>

          </div>

          <div class="expert-hover-card">
            <div class="card-header">
              <img src="{{ getStorageImages(path: $expert?->image_full_url, type: 'avatar') }}">
              <div>
                <h6>{{ $expert?->f_name }} {{ $expert?->l_name }}</h6>
                <small class="text-muted">{{ $expert?->primary_specialty }}</small>
              </div>
            </div>

            <div class="card-body">
              <p><strong>Experience:</strong> {{ $expert?->experience }} yrs</p>
              <p><strong>Category:</strong> {{ $expert?->category->name ?? '—' }}</p>
            </div>
          </div>

        </div>
        <div class="">
          @if($chat->status !== 'ended')
          <button class="btn btn-primary btn-sm mr-2" @click="initiateCall(false)">
            <i class="fa-solid fa-phone"></i>
          </button>
          <button class="btn btn-success btn-sm mr-2" @click="initiateCall(true)">
            <i class="fa-solid fa-video"></i>
          </button>
          <button class="btn btn-danger btn-sm" @click="endChat()">
            <i class="fa-solid fa-phone-slash"></i>
          </button>
          @endif
        </div>
      </div>
      <div class="chat-body" id="messages">
        <!-- Full screen call modal -->
        <!-- Full screen call modal -->

        @foreach($messages as $msg)
        <div class="message-container {{ $msg->sender_type == 'user' ? 'user-side' : '' }}" data-message-id="{{ $msg->id }}">
          @if($msg->sender_type != 'user')
          <img src="{{ asset('assets/front-end/img/placeholder/user.png') }}" class="message-avatar" alt="Expert">
          @endif

          <div class="message-bubble {{ $msg->sender_type == 'user' ? 'user' : 'bot' }}">
            @if(Str::startsWith($msg->message, 'chat-images/'))
            <img src="{{ asset('storage/' . $msg->message) }}" style="max-width:200px; border-radius:10px;">
            @else
            {!! nl2br(e($msg->message)) !!}
            @endif

            <div class="message-meta">
              <span>{{ \Carbon\Carbon::parse($msg->sent_at)->format('h:i A') }}</span>
              @if($msg->sender_type == 'user')
              <span class="read-ticks" style="{{ $msg->is_read ? 'color: #34b7f1;' : '' }}">
                {{ $msg->is_read ? '✓✓' : '✓' }}
              </span>
              @endif
            </div>
          </div>

          @if($msg->sender_type == 'user')
          <img src="{{ getStorageImages(path: auth('customer')->user()->image_full_url, type: 'avatar') }}" class="message-avatar" alt="You">
          @endif
        </div>
        @endforeach
        @if($chat->status === 'ended')
        <div class="text-center py-5 my-4 bg-light rounded">
          <h5 class="text-muted mb-3">Chat has ended</h5>
          <p class="text-muted mb-0">Thank you for using our service!</p>
        </div>
        @endif
      </div>
      <div class="chat-footer" x-show="$store.chatStatus.status !== 'ended'">
        <input type="file" id="imageInput" style="display:none" accept="image/*" @change="handleFileUpload">

        <button class="btn btn-outline-danger btn-pill font-size-lg upload-btn" @click="document.getElementById('imageInput').click()">
          <i class="fa-solid fa-paperclip"></i>
        </button>

        <input type="text" x-model="newMessage" @keyup.enter="sendMessage" @keyup="typingEvent" placeholder="Type a message">

        <button class="send-btn bg-primary" @click="sendMessage">
          <i class="fa-solid fa-paper-plane"></i>
        </button>
      </div>

      <div class="modal fade" id="callModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen">
          <div class="modal-content bg-dark text-white border-0">

            <!-- Header -->
            <div class="modal-header border-0 text-center">
              <div class="d-flex align-items-center gap-10px">
                <div>
                  <img :src="callerInfo?.avatar" class="rounded-circle border border-success mb-3"
                    style="width:50px;height:50px;object-fit:cover">
                </div>
                <div>
                  <h4 class="modal-title" x-text="callerInfo?.name"></h4>
                  <p id="call-status" class="text-success mt-1" x-text="callStatusText"></p>
                </div>
              </div>
              <div x-show="callState === 'connected'">
                <span class="badge badge-pill badge-soft-light py-2 px-3"
                  x-text="formattedDuration"
                  style="font-size: 1.1rem; letter-spacing: 1px;">
                </span>
              </div>
            </div>

            <div class="modal-body position-relative p-0">
              <div id="video-wrapper" class="w-100 h-100" :class="isVideo ? 'd-block' : 'd-none'">
                <div id="remote-media" class="w-100 h-100"></div>
                <div id="local-media"
                  class="position-absolute bottom-0 end-0 m-3 rounded overflow-hidden border border-white"
                  style="width:160px;height:200px">
                </div>
              </div>
            </div>

            <div class="modal-footer justify-content-center border-0 bg-dark">

              <div x-show="callState === 'incoming'" class="row gap-4 align-items-center" x-cloak>

                <button @click="rejectCall()" class="btn btn-danger rounded-circle p-4 shadow-lg">
                  <i class="fa-solid fa-phone-slash fa-2x"></i>
                </button>
                <button @click="acceptCall()" class="btn btn-success rounded-circle p-4 shadow-lg">
                  <i class="fa-solid fa-phone fa-2x"></i>
                </button>
              </div>

              <div x-show="callState === 'ringing' && callInitiator === 'user'" class="text-center" x-cloak>
                <button @click="cancelCall()" class="btn btn-danger rounded-circle p-4 shadow-lg">
                  <i class="fa-solid fa-phone-slash fa-2x"></i>
                </button>
              </div>

              <div x-show="callState === 'connected'" class="row gap-10px align-items-center" x-cloak>
                <button @click="toggleMute()" :class="isMuted ? 'btn-danger' : 'btn-secondary px-4'"
                  class="btn rounded-circle p-3">
                  <i class="fa-solid" :class="isMuted ? 'fa-microphone-slash' : 'fa-microphone'"></i>
                </button>
                <button @click="hangUp()" class="btn btn-danger rounded-circle p-4">
                  <i class="fa-solid fa-phone-slash fa-2x"></i>
                </button>
              </div>

              <div x-show="callState === 'connecting'" class="text-center" x-cloak>
                <div class="spinner-border text-light" role="status"></div>
                <p class="mt-2">Connecting...</p>
              </div>


            </div>
          </div>

        </div>
      </div>

    </div>


    <div class="modal fade" id="reviewModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Rate Your Experience</h5>
            <button type="button" class="btn-close btn btn-wishlist" data-bs-dismiss="modal">X</button>
          </div>
          <form id="reviewForm">
            <div class="modal-body text-center">
              <p>How was your experience with <strong>{{ $expert?->f_name }}</strong>?</p>

              <div class="star-rating mb-3">
                <i class="fa fa-star fa-2x text-warning" data-rating="1"></i>
                <i class="fa fa-star fa-2x text-warning" data-rating="2"></i>
                <i class="fa fa-star fa-2x text-warning" data-rating="3"></i>
                <i class="fa fa-star fa-2x text-warning" data-rating="4"></i>
                <i class="fa fa-star fa-2x text-warning" data-rating="5"></i>
              </div>
              <input type="hidden" name="rating" id="selectedRating" required>

              <textarea name="review" class="form-control" rows="4" placeholder="Write your review (optional)"></textarea>
            </div>
            <div class="modal-footer justify-content-center">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Skip</button>
              <button type="submit" class="btn btn--primary" style="background-color: #800;">Submit Review</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </body>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
  <script>
    window.AUTH_USER_AVATAR = "{{ getStorageImages(path: auth('customer')->user()->image_full_url, type: 'avatar') }}";
    window.AGORA_APP_ID = "{{ config('services.agora.app_id') }}";
  </script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('alpine:init', () => {

      Alpine.store('chatStatus', {
        status: '{{ $chat->status }}'
      });
      Alpine.data('chatComponent', (chatId) => ({
        // Purana component extend karo
        ...window.chatComponent(chatId),

        // End Chat override
        endChat() {
          Swal.fire({
            title: 'End Chat?',
            text: "Are you sure you want to end this chat?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, End Chat',
            confirmButtonColor: '#dc3545'
          }).then((result) => {
            if (result.isConfirmed) {
              fetch(`{{ route('chat.end', $chat->id) }}`, {
                  method: 'POST',
                  headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                  }
                })
                .then(response => response.json())
                .then(data => {
                  if (data.success) {
                    toastr.success(data.message);
                    Alpine.store('chatStatus').status = 'ended';
                    // UI Updates - direct DOM manipulation
                    const footer = document.querySelector('.chat-footer');
                    if (footer) footer.style.display = 'none';

                    const endBtn = document.querySelector('.chat-header .btn-danger');
                    if (endBtn) endBtn.style.display = 'none';

                    const messagesDiv = document.getElementById('messages');
                    messagesDiv.insertAdjacentHTML('beforeend', `
                                <div class="text-center py-5 my-4 bg-light rounded">
                                    <h5 class="text-muted mb-3">Chat has ended</h5>
                                    <p class="text-muted mb-0">Thank you for using our service!</p>
                                </div>
                            `);

                    // Scroll to bottom - direct DOM se
                    messagesDiv.scrollTop = messagesDiv.scrollHeight;

                    // REVIEW MODAL OPEN - Guaranteed working
                    const modalElement = document.getElementById('reviewModal');
                    if (modalElement) {
                      const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                      modal.show();
                    } else {
                      console.error('Review modal not found!');
                    }
                  } else {
                    toastr.error(data.message || 'Failed to end chat');
                  }
                })
                .catch(error => {
                  console.error('End chat error:', error);
                  toastr.error('Network error. Please try again.');
                });
            }
          });
        }
      }));
    });

    // Star Rating
    document.querySelectorAll('.star-rating i').forEach(star => {
      star.addEventListener('click', function() {
        const rating = parseInt(this.dataset.rating);
        document.getElementById('selectedRating').value = rating;

        document.querySelectorAll('.star-rating i').forEach((s, i) => {
          if (i < rating) {
            s.classList.remove('text-muted');
            s.classList.add('text-warning');
          } else {
            s.classList.remove('text-warning');
            s.classList.add('text-muted');
          }
        });
      });
    });

    // Review Submit
    document.getElementById('reviewForm')?.addEventListener('submit', function(e) {
      e.preventDefault();

      const formData = new FormData(this);

      fetch(`{{ route('chat.review', $chat->id) }}`, {
          method: 'POST',
          body: formData,
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          }
        })
        .then(response => {
          if (!response.ok) {
            // Validation ya server error
            return response.json().then(errData => {
              throw errData;
            });
          }
          return response.json();
        })
        .then(data => {
          if (data.success) {
            toastr.success(data.message || 'Thank you for your review!');
            const modal = bootstrap.Modal.getInstance(document.getElementById('reviewModal'));
            if (modal) modal.hide();
            setTimeout(() => {
              window.location.href = '{{ route("home") }}';
            }, 1500);
          }
        })
        .catch(error => {
          console.log('Review submit error:', error); // Debug ke liye

          let errorMessage = 'Something went wrong. Please try again.';

          if (error.errors && Array.isArray(error.errors)) {
            // Laravel validation errors array
            errorMessage = error.errors.join('<br>');
          } else if (error.errors) {
            // Object form mein errors
            let msgs = [];
            for (let field in error.errors) {
              msgs = msgs.concat(error.errors[field]);
            }
            errorMessage = msgs.join('<br>');
          } else if (error.message) {
            errorMessage = error.message;
          }

          toastr.error(errorMessage);
        });
    });
  </script>
  <style>
    .star-rating i {
      cursor: pointer;
      transition: all 0.2s;
    }

    .star-rating i:hover {
      transform: scale(1.2);
    }
  </style>

  </html>