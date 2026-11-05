@extends('layouts.app')

@section('title', 'Khám Phá Độ Sâu Đại Dương | OceanQuiz')

@section('content')
<section class="hero" style="--hero-image: url('{{ asset('images/ocean-hero.jpg') }}');">
    <div class="hero-content">
        <span class="hero-eyebrow">Đấu trường kiến thức đại dương</span>
        <h1 class="hero-title">Khám phá<br>đại dương</h1>
        <span class="hero-subtitle">Đọc sâu · Phản xạ nhanh</span>
        <p class="hero-text">
            Hành trình từ tầng ánh sáng xuống rãnh Mariana, kèm những câu hỏi tính điểm tốc độ để bạn đo lại hiểu biết của mình về biển.
        </p>
        <div class="hero-actions">
            @auth
                <a href="{{ route('quiz.index') }}" class="btn btn-primary">Vào làm quiz</a>
                <a href="{{ route('dashboard') }}" class="btn btn-outline">Bảng điều khiển</a>
            @else
                <a href="{{ route('register') }}" class="btn btn-primary">Tạo tài khoản</a>
                <a href="{{ route('login') }}" class="btn btn-outline">Đăng nhập</a>
            @endauth
        </div>
    </div>
    <div class="hero-stats">
        <div class="hero-stat">
            <strong>5</strong>
            <span>Tầng đại dương</span>
        </div>
        <div class="hero-stat">
            <strong>3 phút</strong>
            <span>Đọc hiểu tư liệu</span>
        </div>
        <div class="hero-stat">
            <strong>1.000</strong>
            <span>Điểm tối đa mỗi câu</span>
        </div>
    </div>
</section>

<div class="ocean-scroll-wrapper" id="oceanWrapper">
    <div class="depth-layout">
        <!-- Thanh đo độ sâu bên trái -->
        <aside class="depth-sidebar">
            <div class="depth-readout-box">
                <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em;">Độ sâu hiện tại</div>
                <div class="depth-meter-number" id="currentDepthDisplay">0m</div>
                <div class="depth-zone-badge" id="currentZoneName">Tầng Ánh Sáng</div>
            </div>

            <ul class="depth-scale-list">
                <li class="depth-scale-item active" data-target="zone-0" onclick="scrollToZone('zone-0')">
                    <strong>0m</strong> — Tầng Ánh Sáng
                </li>
                <li class="depth-scale-item" data-target="zone-200" onclick="scrollToZone('zone-200')">
                    <strong>200m</strong> — Tầng Chạng Vạng
                </li>
                <li class="depth-scale-item" data-target="zone-1000" onclick="scrollToZone('zone-1000')">
                    <strong>1,000m</strong> — Tầng Nửa Đêm
                </li>
                <li class="depth-scale-item" data-target="zone-4000" onclick="scrollToZone('zone-4000')">
                    <strong>4,000m</strong> — Tầng Đáy Thẳm
                </li>
                <li class="depth-scale-item" data-target="zone-6000" onclick="scrollToZone('zone-6000')">
                    <strong>6,000m+</strong> — Rãnh Mariana
                </li>
            </ul>
        </aside>

        <div class="depth-sections">
            <!-- TẦNG 1: 0m -->
            <section class="ocean-zone-section" id="zone-0" data-depth="0" data-zone="Tầng Ánh Sáng (0m - 200m)">
                <div class="zone-card">
                    <figure class="zone-visual">
                        <img src="{{ asset('images/zones/tang-anh-sang.jpg') }}" alt="Rạn san hô trong ánh sáng mặt trời ở tầng ánh sáng" width="1200" height="670" loading="lazy">
                        <figcaption>Độ sâu 0 – 200 m</figcaption>
                    </figure>
                    <div class="zone-header">
                        <span class="zone-depth-pill">ĐỘ SÂU: 0m – 200m</span>
                        <span class="zone-topic">Quang Hợp &amp; Rạn San Hô</span>
                    </div>
                    <h2 style="font-size: 2.1rem; margin-bottom: 1rem;">Tầng Ánh Sáng (Epipelagic Zone)</h2>
                    <p>
                        Chào mừng bạn đến với tầng mặt của đại dương — nơi ánh sáng mặt trời chiếu rọi rực rỡ, thúc đẩy quá trình quang hợp của thực vật phù du (nguồn tạo ra hơn 50% lượng oxy cho Trái Đất và nuôi sống 90% sinh vật biển).
                    </p>
                    <div class="creature-showcase">
                        <div class="creature-box">
                            <strong>Rùa Biển Xanh (Vích)</strong>
                            <span>Định hướng vượt hàng ngàn hải lý qua đại dương nhờ cảm nhận từ trường Trái Đất.</span>
                        </div>
                        <div class="creature-box">
                            <strong>Cá Heo Mũi Chai</strong>
                            <span>Sử dụng hệ thống định vị bằng tiếng vang sinh học (Echolocation) để săn mồi và giao tiếp.</span>
                        </div>
                        <div class="creature-box">
                            <strong>Cá Mập Trắng Lớn</strong>
                            <span>Phát hiện xung điện từ cực nhỏ từ nhịp tim con mồi nhờ cơ quan Ampullae of Lorenzini.</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- TẦNG 2: 200m -->
            <section class="ocean-zone-section" id="zone-200" data-depth="200" data-zone="Tầng Chạng Vạng (200m - 1000m)">
                <div class="zone-card">
                    <figure class="zone-visual">
                        <img src="{{ asset('images/zones/tang-chang-vang.jpg') }}" alt="Đàn cá đèn lồng và mực thủy tinh ở tầng chạng vạng" width="1200" height="670" loading="lazy">
                        <figcaption>Độ sâu 200 m – 1.000 m</figcaption>
                    </figure>
                    <div class="zone-header">
                        <span class="zone-depth-pill">ĐỘ SÂU: 200m – 1,000m</span>
                        <span class="zone-topic">Di Cư Thẳng Đứng Hàng Đêm</span>
                    </div>
                    <h2 style="font-size: 2.1rem; margin-bottom: 1rem;">Tầng Chạng Vạng (Mesopelagic Zone)</h2>
                    <p>
                        Ánh sáng mặt trời yếu dần thành một màu xanh lam mờ ảo, không còn đủ cho quá trình quang hợp. Mỗi đêm, hàng ngàn tỷ sinh vật từ tầng này bơi lên mặt nước kiếm ăn, tạo nên cuộc di cư sinh học lớn nhất hành tinh.
                    </p>
                    <div class="creature-showcase">
                        <div class="creature-box">
                            <strong>Mực Thủy Tinh (Cockatoo Squid)</strong>
                            <span>Cơ thể trong suốt như pha lê giúp ngụy trang hoàn hảo trước kẻ săn mồi.</span>
                        </div>
                        <div class="creature-box">
                            <strong>Cá Đèn Lồng (Lanternfish)</strong>
                            <span>Chiếm tới 65% tổng sinh khối cá biển sâu; phát sáng ở bụng để xóa bóng của chính mình.</span>
                        </div>
                        <div class="creature-box">
                            <strong>Cá Nhà Táng Lặn Sâu</strong>
                            <span>Có thể nín thở suốt 90 phút để lặn xuống tầng chạng vạng săn mực khổng lồ.</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- TẦNG 3: 1000m -->
            <section class="ocean-zone-section" id="zone-1000" data-depth="1000" data-zone="Tầng Nửa Đêm (1000m - 4000m)">
                <div class="zone-card">
                    <figure class="zone-visual">
                        <img src="{{ asset('images/zones/tang-nua-dem.jpg') }}" alt="Cá vây chân phát sáng trong bóng tối tầng nửa đêm" width="1200" height="670" loading="lazy">
                        <figcaption>Độ sâu 1.000 m – 4.000 m</figcaption>
                    </figure>
                    <div class="zone-header">
                        <span class="zone-depth-pill">ĐỘ SÂU: 1,000m – 4,000m</span>
                        <span class="zone-topic">Bóng Tối Vĩnh Cửu &amp; Phát Quang Sinh Học</span>
                    </div>
                    <h2 style="font-size: 2.1rem; margin-bottom: 1rem;">Tầng Nửa Đêm (Bathypelagic Zone)</h2>
                    <p>
                        Không một tia sáng mặt trời nào chạm tới độ sâu 1.000 mét. Ánh sáng duy nhất tại đây được tạo ra bởi phản ứng hóa học phát quang sinh học (Bioluminescence). Áp suất nước vượt ngưỡng 100 lần khí quyển và nhiệt độ chỉ khoảng 4 °C.
                    </p>
                    <div class="creature-showcase">
                        <div class="creature-box">
                            <strong>Cá Vây Chân (Anglerfish)</strong>
                            <span>Treo chiếc "cần câu" phát sáng chứa vi khuẩn cộng sinh ngay trước miệng để dụ con mồi.</span>
                        </div>
                        <div class="creature-box">
                            <strong>Mực Ma Cà Rồng (Vampire Squid)</strong>
                            <span>Sống trong vùng tối thiểu oxy và ăn "tuyết biển" (mùn hữu cơ rơi từ tầng trên xuống).</span>
                        </div>
                        <div class="creature-box">
                            <strong>Cá Rồng Đèn Đỏ (Stoplight Loosejaw)</strong>
                            <span>Phát ra tia sáng đỏ/hồng ngoại như ống nhòm hồng ngoại mà con mồi không thể nhìn thấy.</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- TẦNG 4: 4000m -->
            <section class="ocean-zone-section" id="zone-4000" data-depth="4000" data-zone="Tầng Đáy Thẳm (4000m - 6000m)">
                <div class="zone-card">
                    <figure class="zone-visual">
                        <img src="{{ asset('images/zones/tang-day-tham.jpg') }}" alt="Miệng phun thủy nhiệt và giun ống ở tầng đáy thẳm" width="1200" height="670" loading="lazy">
                        <figcaption>Độ sâu 4.000 m – 6.000 m</figcaption>
                    </figure>
                    <div class="zone-header">
                        <span class="zone-depth-pill">ĐỘ SÂU: 4,000m – 6,000m</span>
                        <span class="zone-topic">Miệng Phun Thủy Nhiệt &amp; Hóa Tổng Hợp</span>
                    </div>
                    <h2 style="font-size: 2.1rem; margin-bottom: 1rem;">Tầng Đáy Thẳm (Abyssopelagic Zone)</h2>
                    <p>
                        Bao phủ hơn 60% bề mặt Trái Đất, đồng bằng đáy thẳm là vùng bùn lạnh giá. Tuy nhiên, xung quanh các miệng phun thủy nhiệt núi lửa ("Ống khói đen"), sự sống lại bùng nổ nhờ quá trình hóa tổng hợp (Chemosynthesis) thay vì ánh sáng mặt trời.
                    </p>
                    <div class="creature-showcase">
                        <div class="creature-box">
                            <strong>Bạch Tuộc Dumbo</strong>
                            <span>Loài bạch tuộc sống sâu nhất hành tinh, bơi lượn bằng hai chiếc vây giống tai voi Dumbo.</span>
                        </div>
                        <div class="creature-box">
                            <strong>Giun Ống Khổng Lồ (Riftia)</strong>
                            <span>Không có miệng và ruột; sống nhờ vi khuẩn chuyển hóa khí độc Hydro Sunfua từ miệng núi lửa.</span>
                        </div>
                        <div class="creature-box">
                            <strong>Cá Ba Chân (Tripod Fish)</strong>
                            <span>Đứng bất động trên lớp bùn đáy biển bằng 3 tia vây dài cứng như kiềng ba chân.</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- TẦNG 5: 6000m+ -->
            <section class="ocean-zone-section" id="zone-6000" data-depth="10984" data-zone="Tầng Hố Thẳm (Rãnh Mariana)">
                <div class="zone-card">
                    <figure class="zone-visual">
                        <img src="{{ asset('images/zones/ranh-mariana.jpg') }}" alt="Cá sên Mariana bên vách đá rãnh Mariana" width="1200" height="670" loading="lazy">
                        <figcaption>Độ sâu 6.000 m – 10.984 m</figcaption>
                    </figure>
                    <div class="zone-header">
                        <span class="zone-depth-pill">ĐỘ SÂU: 6,000m – 10,984m</span>
                        <span class="zone-topic">Đã Chạm Đáy Vực Challenger Deep</span>
                    </div>
                    <h2 style="font-size: 2.1rem; margin-bottom: 1rem;">Tầng Hố Thẳm (Hadalpelagic - Rãnh Mariana)</h2>
                    <p>
                        Bạn đã chạm tới điểm sâu nhất Trái Đất: Vực Challenger Deep ở độ sâu 10.984 mét. Áp suất tại đây gấp hơn 1.071 lần mặt biển — tương đương sức nặng của 50 chiếc máy bay phản lực đè lên cơ thể. Bạn đã sẵn sàng thử thách kiến thức đại dương của mình?
                    </p>
                    <div class="creature-showcase">
                        <div class="creature-box">
                            <strong>Cá Sên Mariana (Snailfish)</strong>
                            <span>Kỷ lục loài cá sống sâu nhất (~8.000m) với hộp sọ mềm và nồng độ phân tử TMAO cực cao.</span>
                        </div>
                        <div class="creature-box">
                            <strong>Giáp Xác Amphipod Khổng Lồ</strong>
                            <span>Phủ lớp gel nhôm hydroxit quanh vỏ để không bị áp suất cực đại làm tan chảy.</span>
                        </div>
                        <div class="creature-box">
                            <strong>Trùng Chân Giả Xenophyophores</strong>
                            <span>Sinh vật đơn bào khổng lồ rộng tới 20 cm sinh sống ngay dưới đáy Challenger Deep.</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function scrollToZone(id) {
        const el = document.getElementById(id);
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    window.addEventListener('scroll', () => {
        const scrollTop = window.scrollY;
        const maxScroll = document.documentElement.scrollHeight - window.innerHeight;
        const scrollFraction = maxScroll > 0 ? Math.min(Math.max(scrollTop / maxScroll, 0), 1) : 0;

        const simulatedMeters = Math.round(scrollFraction * 10984);
        document.getElementById('currentDepthDisplay').textContent = simulatedMeters.toLocaleString() + 'm';

        const sections = document.querySelectorAll('.ocean-zone-section');
        const scaleItems = document.querySelectorAll('.depth-scale-item');
        let activeZoneId = 'zone-0';
        let activeZoneTitle = 'Tầng Ánh Sáng';

        sections.forEach(sec => {
            const rect = sec.getBoundingClientRect();
            if (rect.top <= window.innerHeight * 0.5) {
                activeZoneId = sec.id;
                activeZoneTitle = sec.getAttribute('data-zone');
            }
        });

        document.getElementById('currentZoneName').textContent = activeZoneTitle;

        scaleItems.forEach(item => {
            if (item.getAttribute('data-target') === activeZoneId) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
    });
</script>
@endpush
