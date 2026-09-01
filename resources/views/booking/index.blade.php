@extends('layouts.app')

@section('content')
  <style>
    /* Galeri Grid untuk Desktop */
    .gallery-grid {
      display: grid;
      grid-template-columns: 2fr 1fr; /* Kolom kiri 2x lebih lebar dari kanan */
      grid-template-rows: repeat(2, 200px); /* Tinggi baris tetap 200px agar rapi */
      gap: 0.5rem;
      cursor: pointer;
    }

    /* Gambar di dalam Grid */
    .gallery-grid img {
      width: 100%;
      height: 100%;
      object-fit: cover; /* Memastikan gambar terisi penuh tanpa terdistorsi */
      border-radius: 0.5rem;
      transition: transform 0.2s ease-in-out;
    }

    .gallery-grid img:hover {
      transform: scale(1.02); /* Efek zoom-in halus saat hover */
    }

    /* Gambar kiri menempati dua baris */
    .gallery-left {
      grid-row: span 2;
    }

    /* Kontainer foto untuk Mobile */
    .main-photo-container {
      display: none; /* Sembunyikan default untuk desktop */
      position: relative;
      cursor: pointer;
      border-radius: 0.5rem;
      overflow: hidden;
    }

    .main-photo-container img {
      width: 100%;
      height: auto;
      max-height: 400px; /* Batasi tinggi agar tidak memenuhi layar mobile */
      object-fit: cover;
      display: block;
    }

    /* Overlay "Lihat Semua Foto" di mobile */
    .view-all-overlay {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      padding: 1rem;
      background: linear-gradient(to top, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0));
      color: white;
      text-align: right;
      font-size: 1rem;
    }

    /* Responsif untuk Mobile */
    @media (max-width: 767.98px) {
      .gallery-grid {
        display: none; /* Sembunyikan tampilan grid di mobile */
      }
      .main-photo-container {
        display: block; /* Tampilkan tampilan mobile */
      }
    }

    /* --- Perbaikan CSS untuk Modal Galeri --- */
    /* Mengubah modal agar dapat menyesuaikan layar penuh di perangkat mobile */
    .modal.fade.modal-fullscreen-md-down .modal-dialog {
      transform: none; /* Memastikan modal tidak terdistorsi di mobile */
    }

    #galleryModal .modal-dialog {
      max-width: 90vw;
      width: 100%;
      height: 100%; /* Pastikan dialog modal mengisi ruang yang tersedia */
    }

    @media (min-width: 992px) {
      #galleryModal .modal-dialog {
        max-width: 1200px;
        height: auto;
      }
    }

    #galleryModal .modal-content {
      background-color: #1a1a1a;
      border: none;
      height: 100%;
    }

    /* Perbaikan: Aturan CSS baru untuk memastikan gambar karosel berada di tengah dan tidak terdistorsi */
    #galleryCarousel,
    #galleryCarousel .carousel-inner,
    #galleryCarousel .carousel-item {
      height: 100%;
    }

    #galleryCarousel .carousel-item img {
      max-height: 80vh;
      max-width: 100%;
      width: auto;
      height: auto;
      object-fit: contain;
      margin: auto;
      display: block;
    }

    /* Override khusus untuk mobile */
    @media (max-width: 767.98px) {
      #galleryCarousel .carousel-item img {
        width: 100%;
        height: 100%;
        max-height: 100vh; /* isi penuh layar */
        object-fit: cover; /* isi layar tanpa sisa abu-abu */
      }
    }

    /* Tombol navigasi yang lebih menonjol */
    #galleryCarousel .carousel-control-prev,
    #galleryCarousel .carousel-control-next {
      width: 5%;
      opacity: 0.8;
      transition: opacity 0.2s ease-in-out;
    }

    #galleryCarousel .carousel-control-prev:hover,
    #galleryCarousel .carousel-control-next:hover {
      opacity: 1;
    }

    /* Perbaikan CSS untuk thumbnail agar responsif di mobile */
    .carousel-thumbnails {
      padding: 1rem 0;
      background-color: rgba(0, 0, 0, 0.5);
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      z-index: 1051;
      display: flex; /* Menggunakan flexbox untuk tata letak thumbnail */
      justify-content: center;
      flex-wrap: nowrap; /* Mencegah thumbnail melompat ke baris baru */
      overflow-x: auto; /* Memungkinkan thumbnail digulir jika terlalu banyak */
    }

    .carousel-thumbnails img {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 0.3rem;
      margin: 0 0.25rem;
      cursor: pointer;
      opacity: 0.6;
      border: 2px solid transparent;
      transition: all 0.2s ease-in-out;
      flex-shrink: 0; /* Mencegah gambar menyusut */
    }

    .carousel-thumbnails img.active,
    .carousel-thumbnails img:hover {
      opacity: 1;
      border-color: #fff;
    }

    /* --- Perbaikan CSS untuk Sticky Header --- */
    .table-scroll-container {
      max-height: 500px;
      overflow-y: auto;
    }

    .table-scroll-container.table-responsive {
      max-height: 500px;
      overflow-y: auto;
    }

    .table-scroll-container thead {
      position: sticky;
      top: 0;
      z-index: 10;
      background-color: #ffffff;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .table-scroll-container thead th {
      border-bottom: 2px solid #dee2e6;
    }
  </style>

  <div class="container my-3">
    {{-- Gallery Lapangan (Responsive) --}}
    <div class="container mb-4">
      {{-- Tampilan Desktop (Grid) --}}
      <div class="gallery-grid d-none d-md-grid">
        <img
          src="{{ asset('images/oneinfini_slide1.jpg') }}"
          alt="Lapangan 1"
          class="gallery-left"
          onclick="showCarousel(0)"
        />
        <img
          src="{{ asset('images/oneinfini_slide2.jpg') }}"
          alt="Lapangan 2"
          onclick="showCarousel(1)"
        />
        <img
          src="{{ asset('images/oneinfini_slide3.jpg') }}"
          alt="Lapangan 3"
          onclick="showCarousel(2)"
        />
      </div>

      {{-- Tampilan Mobile (Satu Foto) --}}
      <div class="main-photo-container d-md-none" onclick="showCarousel(0)">
        <img src="{{ asset('images/oneinfini_slide1.jpg') }}" alt="Galeri Lapangan" />
        <div class="view-all-overlay">
          <span>Lihat Semua Foto &rarr;</span>
        </div>
      </div>
    </div>

    {{-- Modal Gallery --}}
    <div
      class="modal fade modal-fullscreen-md-down"
      id="galleryModal"
      tabindex="-1"
      aria-hidden="true"
      aria-labelledby="galleryModalLabel"
    >
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark">
          <div class="modal-header border-0">
            <h5 class="modal-title text-white visually-hidden" id="galleryModalLabel">
              Galeri Lapangan
            </h5>
            <button
              type="button"
              class="btn-close btn-close-white"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <div class="modal-body p-0 d-flex flex-column">
            <div id="galleryCarousel" class="carousel slide" data-bs-interval="false">
              <div class="carousel-inner">
                <div class="carousel-item active">
                  <img
                    src="{{ asset('images/oneinfini_slide1.jpg') }}"
                    class="d-block w-100"
                    alt="Slide 1"
                  />
                </div>
                <div class="carousel-item">
                  <img
                    src="{{ asset('images/oneinfini_slide2.jpg') }}"
                    class="d-block w-100"
                    alt="Slide 2"
                  />
                </div>
                <div class="carousel-item">
                  <img
                    src="{{ asset('images/oneinfini_slide3.jpg') }}"
                    class="d-block w-100"
                    alt="Slide 3"
                  />
                </div>
                <div class="carousel-item">
                  <img
                    src="{{ asset('images/oneinfini_slide4.jpg') }}"
                    class="d-block w-100"
                    alt="Slide 4"
                  />
                </div>
              </div>
              <button
                class="carousel-control-prev"
                type="button"
                data-bs-target="#galleryCarousel"
                data-bs-slide="prev"
              >
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
              </button>
              <button
                class="carousel-control-next"
                type="button"
                data-bs-target="#galleryCarousel"
                data-bs-slide="next"
              >
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
              </button>
            </div>

            {{-- Thumbnail untuk Navigasi Cepat, tata letak menggunakan flexbox untuk responsif --}}
            <div class="carousel-thumbnails">
              <img
                src="{{ asset('images/oneinfini_slide1.jpg') }}"
                alt="Thumbnail 1"
                class="img-thumbnail active"
                data-bs-target="#galleryCarousel"
                data-bs-slide-to="0"
              />
              <img
                src="{{ asset('images/oneinfini_slide2.jpg') }}"
                alt="Thumbnail 2"
                class="img-thumbnail"
                data-bs-target="#galleryCarousel"
                data-bs-slide-to="1"
              />
              <img
                src="{{ asset('images/oneinfini_slide3.jpg') }}"
                alt="Thumbnail 3"
                class="img-thumbnail"
                data-bs-target="#galleryCarousel"
                data-bs-slide-to="2"
              />
              <img
                src="{{ asset('images/oneinfini_slide4.jpg') }}"
                alt="Thumbnail 4"
                class="img-thumbnail"
                data-bs-target="#galleryCarousel"
                data-bs-slide-to="3"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Informasi Lapangan --}}
    <div class="mb-4 text-left">
      <h2 class="fw-bold d-flex flex-wrap align-items-baseline gap-2">
        <span class="me-1">OneInfini MiniSoccer</span>
        <span class="text-warning fs-5 fw-semibold">
          <span class="me-1">5.0</span>
          ★★★★★
        </span>
      </h2>
      <p>
        "Ayo buktikan skill kamu di lapangan mini soccer Lembang dengan kualitas rumput standar
        FIFA! Udara segar dan disertai fasilitas lengkap yang siap nemenin kamu, Yuk, booking
        sekarang sebelum slot favorit kamu diambil orang!"
      </p>
      <h3>Alamat</h3>
      <p>Jl. Pasir Handap, Lembang, West Bandung Regency, West Java 40391</p>
      <a
        href="https://www.google.com/maps/place/One+Infini+Mini+Soccer/@-6.8242957,107.6204063,17.76z/data=!4m6!3m5!1s0x2e68e1000a38739f:0x1966e85f3991476a!8m2!3d-6.8247003!4d107.6209166!16s%2Fg%2F11vwpm326v?entry=ttu&g_ep=EgoyMDI1MDgxOS4wIKXMDSoASAFQAw%3D%3D"
        target="_blank"
        class="btn btn-primary btn-sm"
      >
        📍 Lihat di Google Maps
      </a>
    </div>

    {{-- Navigasi Minggu --}}
    <div class="d-flex justify-content-between mb-3">
      <a
        href="{{ route('booking.index', ['date' => $startOfWeek->copy()->subWeek()->toDateString(),]) }}"
        class="btn btn-outline-secondary btn-sm"
      >
        ← Minggu Sebelumnya
      </a>
      <a
        href="{{ route('booking.index', ['date' => $startOfWeek->copy()->addWeek()->toDateString(),]) }}"
        class="btn btn-outline-secondary btn-sm"
      >
        Minggu Berikutnya →
      </a>
    </div>

    @php
      \Carbon\Carbon::setLocale('id');
    @endphp

    @if (session('error'))
      <div class="alert alert-danger">
        {{ session('error') }}
      </div>
    @endif

    {{-- Tabel Jadwal --}}
    <div class="table-responsive table-scroll-container">
      <table class="table table-bordered text-center align-middle">
        <thead class="table-light">
          <tr>
            @foreach ($dates as $date)
              <th>
                <div class="fw-bold">{{ strtoupper($date->isoFormat('dddd')) }}</div>
                <div>{{ $date->format('d M Y') }}</div>
              </th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @foreach ($timeSlots as $slot)
            @php
              // Cache variabel supaya gak panggil accessor ratusan kali
              $slotId = $slot->id;
              $start = $slot->start_time->format('H.i');
              $end = $slot->end_time->format('H.i');
            @endphp

            <tr>
              @foreach ($dates as $date)
                @php
                  $dateStr = $date->toDateString();
                  $key = $dateStr . '-' . $slotId;

                  $booking = $bookings->get($key);
                  $isBooked = $booking && strtolower($booking->booking_status) === 'booked';

                  $isWeekend = in_array($date->dayOfWeekIso, [6, 7]);
                  $defaultPrice = $isWeekend ? $slot->weekend_price : $slot->weekday_price;

                  $slotPrice = $booking && $booking->total_price != $defaultPrice ? $booking->total_price : $defaultPrice;

                  $formattedPrice = 'Rp' . number_format($slotPrice, 0, ',', '.');
                @endphp

                <td class="p-0">
                  <div
                    id="slot-{{ $slotId }}-{{ $dateStr }}"
                    class="time-slot-button {{ $isBooked ? 'booked' : 'available' }}"
                    data-date="{{ $dateStr }}"
                    data-slot-id="{{ $slotId }}"
                    data-slot-price="{{ $slotPrice }}"
                  >
                    <div class="fw-bold text-nowrap">{{ $start }} - {{ $end }}</div>
                    <div class="fw-bold">{{ $formattedPrice }}</div>
                    <div class="mt-1 small {{ $isBooked ? 'text-danger' : 'text-success' }}">
                      {{ $isBooked ? 'Booked' : 'Available' }}
                    </div>
                  </div>
                </td>
              @endforeach
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- Form Booking --}}
    <form action="{{ route('booking.store') }}" method="POST" id="bookingForm">
      @csrf
      <div id="selectedSlots"></div>
      <button
        type="submit"
        class="btn btn-success w-50 mx-auto py-2 rounded-pill shadow-sm d-none"
        id="confirmBooking"
        style="position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); z-index: 999"
      >
        Booking Sekarang
      </button>
    </form>
  </div>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // --- Logika untuk Modal Galeri yang Disempurnakan ---
      const galleryModalEl = document.getElementById('galleryModal');
      const carouselEl = document.getElementById('galleryCarousel');
      const thumbnailsContainer = document.querySelector('.carousel-thumbnails');
      const thumbnails = thumbnailsContainer.querySelectorAll('img');

      // Fungsi untuk membuka modal dan menggeser ke slide tertentu
      function showCarousel(index) {
        // Ambil instance modal dan karosel, atau buat baru jika belum ada
        let galleryModal = bootstrap.Modal.getOrCreateInstance(galleryModalEl);
        let galleryCarousel = bootstrap.Carousel.getOrCreateInstance(carouselEl, {
          interval: false,
        });

        galleryModal.show();
        galleryCarousel.to(index); // Geser ke slide yang sesuai
      }

      // Memperbarui kelas 'active' pada thumbnail saat slide karosel berubah
      carouselEl.addEventListener('slid.bs.carousel', function (event) {
        thumbnails.forEach((thumb) => thumb.classList.remove('active'));
        thumbnails[event.to].classList.add('active');
      });

      // Tambahkan listener untuk semua gambar di desktop
      document.querySelectorAll('.gallery-grid img').forEach((img, index) => {
        img.addEventListener('click', () => showCarousel(index));
      });

      // Listener untuk foto utama di mobile
      const mainPhotoContainer = document.querySelector('.main-photo-container');
      if (mainPhotoContainer) {
        mainPhotoContainer.addEventListener('click', () => {
          showCarousel(0); // Mulai dari slide pertama
        });
      }

      // ---- Logika Booking yang Sudah Ada ----
      const confirmBtn = document.getElementById('confirmBooking');
      const selectedSlotsContainer = document.getElementById('selectedSlots');
      let selectedSlots = [];

      document.querySelectorAll('.time-slot-button.available').forEach((el) => {
        el.addEventListener('click', function () {
          const key = `${this.dataset.date}|${this.dataset.slotId}`;
          const price = this.dataset.slotPrice;

          if (this.classList.contains('selected')) {
            this.classList.remove('selected');
            selectedSlots = selectedSlots.filter((s) => s.key !== key);
          } else {
            this.classList.add('selected');
            selectedSlots.push({ key, price });
          }

          toggleConfirmButton();
        });
      });

      function toggleConfirmButton() {
        if (selectedSlots.length > 0) {
          confirmBtn.classList.remove('d-none');
          updateHiddenInputs();
        } else {
          confirmBtn.classList.add('d-none');
          selectedSlotsContainer.innerHTML = '';
        }
      }

      function updateHiddenInputs() {
        selectedSlotsContainer.innerHTML = '';
        selectedSlots.forEach((slot) => {
          const inputKey = document.createElement('input');
          inputKey.type = 'hidden';
          inputKey.name = 'slots[]';
          inputKey.value = slot.key;
          selectedSlotsContainer.appendChild(inputKey);

          const inputPrice = document.createElement('input');
          inputPrice.type = 'hidden';
          inputPrice.name = `prices[${slot.key}]`;
          inputPrice.value = slot.price;
          selectedSlotsContainer.appendChild(inputPrice);
        });
      }
    });
  </script>
@endpush
