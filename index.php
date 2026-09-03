<?php require_once 'config/init.php'; ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>SIKAWAS — Sistem Informasi Pelaporan Kejahatan Lingkungan di Kawasan Hutan</title>
    <meta name="description" content="Platform resmi pelaporan kejahatan lingkungan di kawasan hutan Indonesia">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Source Sans 3', 'Segoe UI', sans-serif;
            color: #1C2833;
            font-size: 14px;
            background: #f4f6f8;
        }

        a {
            text-decoration: none;
        }

        /* TOP RED STRIP */
        .top-strip {
            height: 5px;
            background: #C0392B;
        }

        /* MAIN NAV */
        .main-nav {
            background: #1A3A6B;
            padding: 0;
            border-bottom: 1px solid #122952;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
        }

        .nav-inner {
            max-width: 1100px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            height: 60px;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nav-logo .logo-icon {
            width: 42px;
            height: 42px;
            background: rgba(255, 255, 255, 0.12);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            border: 2px solid rgba(255, 255, 255, 0.25);
        }

        .nav-logo .logo-text h1 {
            font-size: 15px;
            font-weight: 700;
            color: #fff;
            letter-spacing: 0.5px;
        }

        .nav-logo .logo-text p {
            font-size: 9.5px;
            color: rgba(255, 255, 255, 0.5);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .nav-links a {
            color: rgba(255, 255, 255, 0.75);
            font-size: 13px;
            font-weight: 500;
            padding: 8px 14px;
            border-radius: 3px;
            transition: all 0.2s;
        }

        .nav-links a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .nav-links .btn-login {
            background: #C0392B;
            color: #fff;
            padding: 8px 18px;
            border-radius: 3px;
            font-weight: 700;
            font-size: 13px;
            transition: background 0.2s;
        }

        .nav-links .btn-login:hover {
            background: #A93226;
        }

        /* HERO */
        .hero {
            background: linear-gradient(135deg, #1A3A6B 0%, #122952 60%, #0D1E3C 100%);
            padding: 60px 24px;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23fff' fill-opacity='0.03'%3E%3Cpath d='M0 40L40 0H20L0 20M40 40V20L20 40'/%3E%3C/g%3E%3C/svg%3E");
        }

        .hero-inner {
            max-width: 1100px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 48px;
            align-items: center;
            position: relative;
        }

        .hero-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(192, 57, 43, 0.3);
            color: #f5a9a0;
            border: 1px solid rgba(192, 57, 43, 0.5);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 5px 12px;
            border-radius: 2px;
            margin-bottom: 16px;
        }

        .hero h2 {
            font-size: 30px;
            font-weight: 800;
            color: #fff;
            line-height: 1.3;
            margin-bottom: 14px;
        }

        .hero h2 .accent {
            color: #f5a9a0;
        }

        .hero p {
            font-size: 15px;
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.8;
            margin-bottom: 28px;
        }

        .hero-btns {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-hero-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #C0392B;
            color: #fff;
            padding: 12px 24px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 700;
            transition: background 0.2s;
        }

        .btn-hero-primary:hover {
            background: #A93226;
        }

        .btn-hero-secondary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            padding: 12px 22px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 3px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-hero-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Hero card */
        .hero-card {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 4px;
            padding: 24px;
            backdrop-filter: blur(6px);
        }

        .hero-card h3 {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.5);
            margin-bottom: 14px;
            font-weight: 700;
        }

        .alur-step {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .alur-step:last-child {
            border-bottom: none;
        }

        .step-num {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: #C0392B;
            color: #fff;
            font-size: 12px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .step-info strong {
            font-size: 13px;
            color: #fff;
            display: block;
        }

        .step-info span {
            font-size: 11.5px;
            color: rgba(255, 255, 255, 0.55);
        }

        /* BOTTOM HERO STRIP */
        .hero-strip {
            background: #C0392B;
            padding: 0;
            border-bottom: 2px solid #A93226;
        }

        .strip-inner {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            overflow-x: auto;
        }

        .strip-stat {
            flex: 1;
            padding: 14px 20px;
            color: #fff;
            text-align: center;
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            min-width: 140px;
        }

        .strip-stat:last-child {
            border-right: none;
        }

        .strip-stat .s-num {
            font-size: 24px;
            font-weight: 800;
            line-height: 1;
        }

        .strip-stat .s-label {
            font-size: 11px;
            opacity: 0.8;
            margin-top: 3px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* SECTION */
        .section {
            padding: 48px 24px;
        }

        .section-alt {
            background: #fff;
        }

        .section-inner {
            max-width: 1100px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            margin-bottom: 36px;
        }

        .section-title h3 {
            font-size: 22px;
            font-weight: 800;
            color: #1A3A6B;
            margin-bottom: 6px;
        }

        .section-title .underline {
            width: 50px;
            height: 3px;
            background: #C0392B;
            margin: 10px auto 0;
            border-radius: 2px;
        }

        .section-title p {
            font-size: 13.5px;
            color: #7F8C8D;
            margin-top: 10px;
        }

        /* FITUR CARDS */
        .fitur-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 16px;
        }

        .fitur-card {
            background: #fff;
            border: 1px solid #DDD;
            border-radius: 4px;
            padding: 22px;
            text-align: center;
            border-top: 3px solid #1A3A6B;
            transition: box-shadow 0.2s;
        }

        .fitur-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        .fitur-icon {
            width: 52px;
            height: 52px;
            border-radius: 3px;
            background: #EBF5FF;
            color: #1A3A6B;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin: 0 auto 14px;
            border: 1px solid #AED6F1;
        }

        .fitur-card.red .fitur-icon {
            background: #FDEDEC;
            color: #C0392B;
            border-color: #F5B7B1;
        }

        .fitur-card.green .fitur-icon {
            background: #EAFAF1;
            color: #1A6B3A;
            border-color: #A9DFBF;
        }

        .fitur-card h4 {
            font-size: 14px;
            font-weight: 700;
            color: #1C2833;
            margin-bottom: 6px;
        }

        .fitur-card p {
            font-size: 12.5px;
            color: #7F8C8D;
            line-height: 1.6;
        }

        /* PENGGUNA */
        .role-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }

        .role-card {
            background: #1A3A6B;
            border-radius: 4px;
            padding: 22px;
            color: #fff;
            border-bottom: 3px solid #C0392B;
            transition: transform 0.2s;
        }

        .role-card:hover {
            transform: translateY(-3px);
        }

        .role-icon {
            font-size: 32px;
            margin-bottom: 12px;
        }

        .role-card h4 {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .role-card p {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.65);
            line-height: 1.6;
        }

        .role-card ul {
            list-style: none;
            margin-top: 10px;
        }

        .role-card ul li {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.75);
            padding: 4px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            gap: 8px;
            align-items: flex-start;
        }

        .role-card ul li::before {
            content: '›';
            color: #f5a9a0;
            font-weight: 800;
            flex-shrink: 0;
        }

        /* CTA */
        .cta-section {
            background: linear-gradient(135deg, #1A3A6B, #122952);
            padding: 50px 24px;
            text-align: center;
            color: #fff;
            border-top: 4px solid #C0392B;
        }

        .cta-section h3 {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .cta-section p {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 24px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        /* FOOTER */
        footer {
            background: #0D1E3C;
            color: rgba(255, 255, 255, 0.4);
            text-align: center;
            padding: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        footer a {
            color: rgba(255, 255, 255, 0.5);
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: #fff;
            font-size: 22px;
            cursor: pointer;
        }

        .tentang-grid-main {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
            align-items: center;
        }

        .tentang-grid-cards {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        @media(max-width: 768px) {
            .mobile-menu-btn {
                display: block;
            }

            .nav-links {
                display: none;
                position: absolute;
                top: 60px;
                left: 0;
                right: 0;
                background: #1A3A6B;
                flex-direction: column;
                padding: 16px 24px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                border-top: 1px solid rgba(255, 255, 255, 0.1);
            }

            .nav-links.show {
                display: flex;
            }

            .nav-links a:not(.btn-login) {
                display: block;
                width: 100%;
                text-align: left;
                padding: 12px 0;
                border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            }

            .nav-links .btn-login {
                margin-top: 12px;
                text-align: center;
                display: block;
            }

            .hero-inner {
                grid-template-columns: 1fr;
            }

            .hero h2 {
                font-size: 22px;
            }

        }

        .strip-inner {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            padding: 0;
        }

        .strip-stat {
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .strip-stat:nth-child(odd) {
            border-right: 1px solid rgba(255, 255, 255, 0.2);
        }

        .strip-stat:nth-child(even) {
            border-right: none;
        }

        .tentang-grid-main {
            grid-template-columns: 1fr;
            gap: 24px;
        }

        .tentang-grid-cards {
            grid-template-columns: 1fr;
        }
        }
    </style>
</head>

<body>

    <div class="top-strip"></div>

    <!-- NAV -->
    <nav class="main-nav">
        <div class="nav-inner">
            <div class="nav-logo">
                <div class="logo-icon">🌿</div>
                <div class="logo-text">
                    <h1>SIKAWAS</h1>
                    <p>Sistem Informasi Pelaporan Kejahatan Lingkungan</p>
                </div>
            </div>
            <button class="mobile-menu-btn" onclick="document.getElementById('navLinks').classList.toggle('show')">
                <i class="fas fa-bars"></i>
            </button>
            <div class="nav-links" id="navLinks">
                <a href="#tentang" onclick="document.getElementById('navLinks').classList.remove('show')">Tentang</a>
                <a href="#fitur" onclick="document.getElementById('navLinks').classList.remove('show')">Fitur</a>
                <a href="#pengguna" onclick="document.getElementById('navLinks').classList.remove('show')">Pengguna</a>
                <a href="register.php"><i class="fas fa-user-plus"></i> Daftar</a>
                <a href="login.php" class="btn-login"><i class="fas fa-sign-in-alt"></i> Masuk</a>
            </div>
        </div>
    </nav>

    <!-- HERO -->
    <section class="hero">
        <div class="hero-inner">
            <div>
                <div class="hero-label"><i class="fas fa-leaf"></i> Platform Resmi Pelaporan</div>
                <h2>Sistem Informasi Pelaporan<br><span class="accent">Kejahatan Lingkungan</span><br>di Kawasan Hutan</h2>
                <p>
                    Portal resmi untuk masyarakat melaporkan tindak kejahatan lingkungan seperti
                    illegal logging, perburuan liar, dan kerusakan kawasan hutan kepada instansi berwenang.
                </p>
                <div class="hero-btns">
                    <a href="register.php" class="btn-hero-primary"><i class="fas fa-file-alt"></i> Buat Laporan Sekarang</a>
                    <a href="#tentang" class="btn-hero-secondary"><i class="fas fa-info-circle"></i> Pelajari Lebih Lanjut</a>
                </div>
            </div>
            <div class="hero-card">
                <h3>📋 Alur Pelaporan</h3>
                <div class="alur-step">
                    <div class="step-num">1</div>
                    <div class="step-info">
                        <strong>Daftar / Masuk</strong>
                        <span>Buat akun atau masuk sebagai masyarakat</span>
                    </div>
                </div>
                <div class="alur-step">
                    <div class="step-num">2</div>
                    <div class="step-info">
                        <strong>Isi Formulir Laporan</strong>
                        <span>Lengkapi detail kejadian dengan bukti foto</span>
                    </div>
                </div>
                <div class="alur-step">
                    <div class="step-num">3</div>
                    <div class="step-info">
                        <strong>Verifikasi Admin</strong>
                        <span>Admin memverifikasi dan menugaskan petugas</span>
                    </div>
                </div>
                <div class="alur-step">
                    <div class="step-num">4</div>
                    <div class="step-info">
                        <strong>Penanganan Lapangan</strong>
                        <span>Polisi Hutan menindaklanjuti di lapangan</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- STRIP STATS -->
    <div class="hero-strip">
        <div class="strip-inner">
            <div class="strip-stat">
                <div class="s-num">4</div>
                <div class="s-label">Jenis Pengguna</div>
            </div>
            <div class="strip-stat">
                <div class="s-num">24/7</div>
                <div class="s-label">Akses Laporan</div>
            </div>
            <div class="strip-stat">
                <div class="s-num">100%</div>
                <div class="s-label">Transparan</div>
            </div>
            <div class="strip-stat">
                <div class="s-num">≤48h</div>
                <div class="s-label">Respons Verifikasi</div>
            </div>
        </div>
    </div>

    <!-- TENTANG -->
    <section class="section section-alt" id="tentang">
        <div class="section-inner">
            <div class="section-title">
                <h3>Tentang SIKAWAS</h3>
                <div class="underline"></div>
                <p>Platform digital untuk mempercepat penanganan kejahatan lingkungan di kawasan hutan</p>
            </div>
            <div class="tentang-grid-main">
                <div>
                    <p style="color:#5D6D7E;line-height:1.9;font-size:14px">
                        SIKAWAS (Sistem Informasi Pelaporan Kejahatan Lingkungan di Kawasan Hutan) merupakan
                        platform digital yang memungkinkan masyarakat untuk melaporkan kejahatan lingkungan
                        secara mudah, cepat, dan terstruktur.
                    </p>
                    <p style="color:#5D6D7E;line-height:1.9;font-size:14px;margin-top:12px">
                        Laporan yang masuk akan langsung diteruskan kepada Administrator untuk diverifikasi,
                        kemudian ditugaskan kepada Polisi Hutan untuk penanganan lapangan. Pimpinan dapat
                        memantau seluruh progress penanganan secara real-time.
                    </p>
                    <div style="margin-top:20px;padding:14px;background:#EBF5FB;border-left:4px solid #1A3A6B;border-radius:0 3px 3px 0;font-size:13px;color:#1A5276">
                        <i class="fas fa-balance-scale"></i> &nbsp;
                        Dibuat sebagai bagian dari upaya perlindungan hutan dan penegakan hukum lingkungan hidup.
                    </div>
                </div>
                <div class="tentang-grid-cards">
                    <?php
                    $cards = [
                        ['icon' => 'fas fa-leaf', 'color' => '#1A6B3A', 'bg' => '#EAFAF1', 'b' => '#A9DFBF', 'title' => 'Illegal Logging', 'desc' => 'Pembalakan liar di kawasan hutan lindung'],
                        ['icon' => 'fas fa-paw', 'color' => '#1A3A6B', 'bg' => '#EBF5FB', 'b' => '#AED6F1', 'title' => 'Perburuan Liar', 'desc' => 'Perburuan satwa dilindungi'],
                        ['icon' => 'fas fa-fire', 'color' => '#C0392B', 'bg' => '#FDEDEC', 'b' => '#F5B7B1', 'title' => 'Pembakaran Hutan', 'desc' => 'Kebakaran hutan disengaja'],
                        ['icon' => 'fas fa-industry', 'color' => '#7D6608', 'bg' => '#FEF9E7', 'b' => '#F9E79F', 'title' => 'Pencemaran', 'desc' => 'Pembuangan limbah di kawasan hutan'],
                    ];
                    foreach ($cards as $c):
                    ?>
                        <div style="background:<?= $c['bg'] ?>;border:1px solid <?= $c['b'] ?>;border-radius:3px;padding:14px">
                            <i class="<?= $c['icon'] ?>" style="font-size:22px;color:<?= $c['color'] ?>;margin-bottom:8px;display:block"></i>
                            <strong style="font-size:12.5px;color:#1C2833"><?= $c['title'] ?></strong>
                            <p style="font-size:11.5px;color:#7F8C8D;margin-top:4px;line-height:1.5"><?= $c['desc'] ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- FITUR -->
    <section class="section" id="fitur">
        <div class="section-inner">
            <div class="section-title">
                <h3>Fitur Sistem</h3>
                <div class="underline"></div>
                <p>Kemampuan sistem yang mendukung proses pelaporan hingga penyelesaian kasus</p>
            </div>
            <div class="fitur-grid">
                <div class="fitur-card">
                    <div class="fitur-icon"><i class="fas fa-file-upload"></i></div>
                    <h4>Pelaporan Online</h4>
                    <p>Formulir digital dengan upload bukti foto secara langsung dari perangkat Anda</p>
                </div>
                <div class="fitur-card red">
                    <div class="fitur-icon"><i class="fas fa-bell"></i></div>
                    <h4>Notifikasi Real-time</h4>
                    <p>Informasi status laporan terkini dapat dipantau kapanpun oleh pelapor</p>
                </div>
                <div class="fitur-card green">
                    <div class="fitur-icon"><i class="fas fa-tasks"></i></div>
                    <h4>Manajemen Kasus</h4>
                    <p>Admin dan polisi hutan dapat mengelola kasus secara terstruktur dan efisien</p>
                </div>
                <div class="fitur-card">
                    <div class="fitur-icon"><i class="fas fa-chart-bar"></i></div>
                    <h4>Rekap & Laporan</h4>
                    <p>Pimpinan dapat mengakses rekapitulasi data dan mencetak laporan resmi</p>
                </div>
            </div>
        </div>
    </section>

    <!-- PENGGUNA -->
    <section class="section section-alt" id="pengguna">
        <div class="section-inner">
            <div class="section-title">
                <h3>Pengguna Sistem</h3>
                <div class="underline"></div>
                <p>Empat jenis pengguna dengan hak akses masing-masing</p>
            </div>
            <div class="role-grid">
                <div class="role-card">
                    <div class="role-icon"><i class="fas fa-users" style="color:#AED6F1"></i></div>
                    <h4>Masyarakat</h4>
                    <ul>
                        <li>Registrasi & login mandiri</li>
                        <li>Buat laporan dengan foto</li>
                        <li>Pantau status laporan</li>
                        <li>Kelola profil akun</li>
                    </ul>
                </div>
                <div class="role-card">
                    <div class="role-icon"><i class="fas fa-shield-alt" style="color:#AED6F1"></i></div>
                    <h4>Administrator</h4>
                    <ul>
                        <li>Verifikasi laporan masuk</li>
                        <li>Tugaskan polisi hutan</li>
                        <li>Kelola data master</li>
                        <li>Rekap & cetak data</li>
                    </ul>
                </div>
                <div class="role-card">
                    <div class="role-icon"><i class="fas fa-hard-hat" style="color:#AED6F1"></i></div>
                    <h4>Polisi Hutan</h4>
                    <ul>
                        <li>Terima penugasan kasus</li>
                        <li>Update status penanganan</li>
                        <li>Catat hasil lapangan</li>
                        <li>Selesaikan kasus</li>
                    </ul>
                </div>
                <div class="role-card">
                    <div class="role-icon"><i class="fas fa-user-tie" style="color:#AED6F1"></i></div>
                    <h4>Pimpinan</h4>
                    <ul>
                        <li>Dashboard ringkasan</li>
                        <li>Pemantauan seluruh kasus</li>
                        <li>Filter & analisis data</li>
                        <li>Cetak laporan resmi</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
        <h3>Mulai Laporan Sekarang</h3>
        <p>Bergabunglah dan bantu jaga kelestarian kawasan hutan Indonesia dengan melaporkan tindak kejahatan lingkungan yang Anda temukan</p>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
            <a href="register.php" class="btn-hero-primary"><i class="fas fa-user-plus"></i> Daftar Akun Baru</a>
            <a href="login.php" class="btn-hero-secondary"><i class="fas fa-sign-in-alt"></i> Sudah Punya Akun</a>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <p>&copy; <?= date('Y') ?> SIKAWAS — Sistem Informasi Pelaporan Kejahatan Lingkungan di Kawasan Hutan</p>
        <p style="margin-top:4px">Kementerian Lingkungan Hidup dan Kehutanan Republik Indonesia</p>
    </footer>

</body>

</html>