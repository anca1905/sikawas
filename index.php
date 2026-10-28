<?php
require_once 'config/init.php';
$db = getDB();

// Ambil data laporan kejadian yang sudah pernah dilaporkan (selain status Ditolak)
$stmtKejadian = $db->query("
    SELECT p.*, j.nama_jenis, h.nama_hutan, h.lokasi_hutan
    FROM pengaduan p
    LEFT JOIN jenis_kejahatan j ON p.id_jenis = j.id_jenis
    LEFT JOIN jenis_kawasan_hutan h ON p.id_hutan = h.id_hutan
    WHERE p.status != 'Ditolak'
    ORDER BY p.tanggal_kejadian DESC, p.id_laporan DESC
");
$laporanKejadian = $stmtKejadian->fetchAll();
$totalLaporan = count($laporanKejadian);
$totalSelesai = count(array_filter($laporanKejadian, fn($l) => $l['status'] === 'Selesai'));
?>
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

            .laporan-filter-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-search-wrap,
            .filter-select {
                width: 100%;
            }

            .kejadian-grid {
                grid-template-columns: 1fr;
            }
        }

        /* BADGES */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 3px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-new {
            background: #FEF9E7;
            color: #9A7D0A;
            border: 1px solid #D4AC0D;
        }

        .badge-verified {
            background: #EBF5FB;
            color: #1A5276;
            border: 1px solid #2980B9;
        }

        .badge-process {
            background: #FDF2E9;
            color: #A04000;
            border: 1px solid #E67E22;
        }

        .badge-done {
            background: #EAFAF1;
            color: #196F3D;
            border: 1px solid #27AE60;
        }

        .badge-rejected {
            background: #FDEDEC;
            color: #922B21;
            border: 1px solid #C0392B;
        }

        /* NOTICE BANNER */
        .notice-laporan-banner {
            background: #EBF5FB;
            border: 1px solid #AED6F1;
            border-left: 5px solid #1A3A6B;
            border-radius: 6px;
            padding: 16px 20px;
            margin-bottom: 24px;
            display: flex;
            gap: 14px;
            align-items: flex-start;
        }

        .notice-laporan-banner .notice-icon {
            font-size: 24px;
            color: #1A3A6B;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .notice-laporan-banner h4 {
            font-size: 14px;
            font-weight: 700;
            color: #1A3A6B;
            margin-bottom: 4px;
        }

        .notice-laporan-banner p {
            font-size: 13px;
            color: #2C3E50;
            line-height: 1.6;
            margin: 0;
        }

        /* FILTER BAR */
        .laporan-filter-bar {
            background: #fff;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 24px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .filter-search-wrap {
            flex: 1;
            min-width: 240px;
            position: relative;
        }

        .filter-search-wrap i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
        }

        .filter-search-input {
            width: 100%;
            padding: 9px 12px 9px 36px;
            border: 1px solid #CBD5E1;
            border-radius: 4px;
            font-size: 13px;
            outline: none;
            transition: border 0.2s;
        }

        .filter-search-input:focus {
            border-color: #1A3A6B;
        }

        .filter-select {
            padding: 9px 14px;
            border: 1px solid #CBD5E1;
            border-radius: 4px;
            font-size: 13px;
            background: #fff;
            color: #334155;
            outline: none;
        }

        .filter-stats-badge {
            font-size: 12px;
            font-weight: 600;
            color: #64748B;
            background: #F1F5F9;
            padding: 6px 12px;
            border-radius: 20px;
            white-space: nowrap;
        }

        /* GRID & CARD */
        .kejadian-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }

        .kejadian-card {
            background: #fff;
            border-radius: 8px;
            border: 1px solid #E2E8F0;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            display: flex;
            flex-direction: column;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .kejadian-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .kejadian-thumb-box {
            position: relative;
            height: 190px;
            background: #F1F5F9;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .kejadian-thumb-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
            cursor: pointer;
        }

        .kejadian-thumb-box:hover img {
            transform: scale(1.04);
        }

        .kejadian-thumb-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 6px 10px;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.7), transparent);
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            opacity: 0;
            transition: opacity 0.2s;
            pointer-events: none;
        }

        .kejadian-thumb-box:hover .kejadian-thumb-overlay {
            opacity: 1;
        }

        .kejadian-no-img {
            text-align: center;
            color: #94A3B8;
        }

        .kejadian-no-img i {
            font-size: 38px;
            margin-bottom: 6px;
            display: block;
            color: #CBD5E1;
        }

        .kejadian-no-img span {
            font-size: 11.5px;
        }

        .kejadian-badges-float {
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 6px;
            pointer-events: none;
        }

        .badge-jenis {
            background: rgba(26, 58, 107, 0.9);
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 4px;
            backdrop-filter: blur(4px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            max-width: 60%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .kejadian-card-body {
            padding: 16px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .kejadian-item {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            font-size: 13px;
            align-items: flex-start;
        }

        .kejadian-item.lokasi i {
            color: #C0392B;
            font-size: 15px;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .kejadian-item.tanggal i {
            color: #1A3A6B;
            font-size: 14px;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .lokasi-main {
            font-weight: 700;
            color: #1E293B;
            line-height: 1.4;
            word-break: break-word;
        }

        .lokasi-hutan-sub {
            font-size: 11.5px;
            color: #166534;
            font-weight: 600;
            margin-top: 2px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .tanggal-val {
            color: #334155;
            font-weight: 600;
        }

        .kejadian-desc {
            font-size: 12.5px;
            color: #64748B;
            line-height: 1.55;
            margin-top: 4px;
            margin-bottom: 14px;
            flex: 1;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .kejadian-card-footer {
            padding-top: 10px;
            border-top: 1px solid #F1F5F9;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11.5px;
            color: #94A3B8;
        }

        .btn-peta-link {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            color: #1A3A6B;
            font-weight: 700;
            font-size: 11.5px;
            padding: 4px 8px;
            border-radius: 4px;
            background: #EBF5FB;
            transition: background 0.2s;
        }

        .btn-peta-link:hover {
            background: #D4E6F1;
        }

        /* MODAL PREVIEW FOTO */
        .photo-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            inset: 0;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(4px);
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .photo-modal.active {
            display: flex;
        }

        .photo-modal-content {
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            max-width: 700px;
            width: 100%;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            animation: zoomModal 0.2s ease-out;
        }

        @keyframes zoomModal {
            from {
                transform: scale(0.9);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .photo-modal-img {
            width: 100%;
            max-height: 60vh;
            object-fit: contain;
            background: #0f172a;
        }

        .photo-modal-body {
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            background: #fff;
        }

        .photo-modal-info h5 {
            font-size: 14px;
            font-weight: 700;
            color: #1E293B;
            margin-bottom: 4px;
        }

        .photo-modal-info p {
            font-size: 12px;
            color: #64748B;
            margin: 0;
        }

        .photo-modal-close {
            background: #F1F5F9;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            color: #475569;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .photo-modal-close:hover {
            background: #E2E8F0;
            color: #0F172A;
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
                <a href="#laporan-kejadian" onclick="document.getElementById('navLinks').classList.remove('show')"><i class="fas fa-bullhorn" style="font-size:12px"></i> Kejadian Terlaporkan</a>
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
                    <a href="#laporan-kejadian" class="btn-hero-secondary" style="border-color:#AED6F1;background:rgba(255,255,255,0.15)"><i class="fas fa-map-marked-alt"></i> Cek Kejadian Terlaporkan</a>
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
                <div class="s-num"><?= $totalLaporan ?></div>
                <div class="s-label">Kejadian Terlaporkan</div>
            </div>
            <div class="strip-stat">
                <div class="s-num"><?= $totalSelesai ?></div>
                <div class="s-label">Kasus Selesai</div>
            </div>
            <div class="strip-stat">
                <div class="s-num">24/7</div>
                <div class="s-label">Akses Laporan</div>
            </div>
            <div class="strip-stat">
                <div class="s-num">100%</div>
                <div class="s-label">Transparan</div>
            </div>
        </div>
    </div>

    <!-- KEJADIAN YANG TELAH DILAPORKAN -->
    <section class="section" id="laporan-kejadian">
        <div class="section-inner">
            <div class="section-title">
                <div style="display:inline-flex;align-items:center;gap:6px;background:#FDEDEC;color:#C0392B;border:1px solid #F5B7B1;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;padding:4px 10px;border-radius:2px;margin-bottom:8px">
                    <i class="fas fa-bullhorn"></i> Transparansi Informasi Publik
                </div>
                <h3>Kejadian yang Telah Dilaporkan</h3>
                <div class="underline"></div>
                <p>Informasi foto bukti, lokasi, dan tanggal kejadian yang telah dilaporkan masyarakat untuk mencegah pelaporan berulang</p>
            </div>

            <!-- Notice Banner Pencegahan Laporan Berulang -->
            <div class="notice-laporan-banner">
                <i class="fas fa-info-circle notice-icon"></i>
                <div>
                    <h4>Pemberitahuan untuk Masyarakat:</h4>
                    <p>
                        Daftar di bawah memuat tindak kejahatan lingkungan di kawasan hutan yang <strong>sudah pernah dilaporkan</strong> oleh masyarakat.
                        Sebelum mengirim laporan baru, silakan periksa daftar atau gunakan pencarian untuk memastikan lokasi kejadian belum pernah dilaporkan,
                        sehingga tidak terjadi <strong>laporan berulang (duplikat)</strong> pada lokasi yang sama.
                    </p>
                </div>
            </div>

            <!-- Filter & Search Bar -->
            <div class="laporan-filter-bar">
                <div class="filter-search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" id="inputCariLaporan" class="filter-search-input" placeholder="Cari nama lokasi, kawasan hutan, atau jenis kejahatan..." oninput="filterLaporan()">
                </div>
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
                    <select id="selectStatusLaporan" class="filter-select" onchange="filterLaporan()">
                        <option value="">Semua Status</option>
                        <option value="Baru">Status: Baru</option>
                        <option value="Diverifikasi">Status: Diverifikasi</option>
                        <option value="Diproses">Status: Diproses</option>
                        <option value="Selesai">Status: Selesai</option>
                    </select>
                    <div class="filter-stats-badge" id="statsLaporanCounter">
                        Menampilkan <strong id="visibleCount"><?= count($laporanKejadian) ?></strong> dari <?= count($laporanKejadian) ?> kejadian
                    </div>
                </div>
            </div>

            <!-- Grid Daftar Laporan -->
            <?php if (empty($laporanKejadian)): ?>
                <div style="text-align:center;padding:50px 20px;background:#fff;border-radius:8px;border:1px solid #E2E8F0">
                    <i class="fas fa-check-circle" style="font-size:42px;color:#16A34A;margin-bottom:12px;display:block"></i>
                    <h4 style="font-size:16px;color:#1E293B;margin-bottom:6px">Belum Ada Kejadian yang Dilaporkan</h4>
                    <p style="font-size:13px;color:#64748B">Saat ini belum ada data laporan aktif yang tercatat di sistem.</p>
                </div>
            <?php else: ?>
                <div class="kejadian-grid" id="gridLaporan">
                    <?php foreach ($laporanKejadian as $row): ?>
                        <?php
                        $hasFoto = !empty($row['bukti_foto']) && file_exists(ROOT_PATH . $row['bukti_foto']);
                        $fotoUrl = $hasFoto ? (BASE_URL . sanitize($row['bukti_foto'])) : '';
                        $badgeStatus = getStatusBadge($row['status']);
                        $tglFormatted = formatTanggal($row['tanggal_kejadian']);
                        $lokasiFull = trim($row['lokasi'] . ' ' . ($row['nama_hutan'] ?? '') . ' ' . ($row['lokasi_hutan'] ?? ''));
                        ?>
                        <div class="kejadian-card item-laporan"
                            data-lokasi="<?= htmlspecialchars(strtolower($lokasiFull)) ?>"
                            data-jenis="<?= htmlspecialchars(strtolower($row['nama_jenis'] ?? '')) ?>"
                            data-deskripsi="<?= htmlspecialchars(strtolower($row['deskripsi'])) ?>"
                            data-status="<?= htmlspecialchars($row['status']) ?>">

                            <!-- Foto Bukti Kejadian -->
                            <div class="kejadian-thumb-box">
                                <?php if ($hasFoto): ?>
                                    <img src="<?= $fotoUrl ?>" alt="Foto Bukti Kejadian" loading="lazy" onclick="openPhotoModal('<?= $fotoUrl ?>', '<?= addslashes(sanitize($row['lokasi'])) ?>', '<?= addslashes($tglFormatted) ?>', '<?= addslashes(sanitize($row['nama_jenis'] ?? '')) ?>')">
                                    <div class="kejadian-thumb-overlay"><i class="fas fa-search-plus"></i> Klik untuk memperbesar foto</div>
                                <?php else: ?>
                                    <div class="kejadian-no-img">
                                        <i class="fas fa-image"></i>
                                        <span>Foto Tidak Dilampirkan</span>
                                    </div>
                                <?php endif; ?>

                                <div class="kejadian-badges-float">
                                    <span class="badge-jenis"><i class="fas fa-tag"></i> <?= sanitize($row['nama_jenis'] ?? 'Kejahatan Lingkungan') ?></span>
                                    <div><?= $badgeStatus ?></div>
                                </div>
                            </div>

                            <!-- Detail Laporan: Lokasi & Tanggal Kejadian -->
                            <div class="kejadian-card-body">
                                <!-- Lokasi -->
                                <div class="kejadian-item lokasi">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <div>
                                        <div class="lokasi-main"><?= sanitize($row['lokasi']) ?></div>
                                        <?php if (!empty($row['nama_hutan'])): ?>
                                            <div class="lokasi-hutan-sub">
                                                <i class="fas fa-tree"></i> <?= sanitize($row['nama_hutan']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Tanggal Kejadian -->
                                <div class="kejadian-item tanggal">
                                    <i class="fas fa-calendar-alt"></i>
                                    <div>
                                        <strong>Tanggal Kejadian:</strong>
                                        <span class="tanggal-val"><?= $tglFormatted ?></span>
                                    </div>
                                </div>

                                <!-- Deskripsi Kejadian Singkat -->
                                <div class="kejadian-desc">
                                    <?= nl2br(sanitize(mb_strimwidth($row['deskripsi'], 0, 160, '...'))) ?>
                                </div>

                                <!-- Card Footer: Tanggal Lapor & Link Peta -->
                                <div class="kejadian-card-footer">
                                    <span><i class="fas fa-clock"></i> Dilaporkan: <?= date('d M Y', strtotime($row['tanggal_lapor'])) ?></span>
                                    <?php if (!empty($row['latitude']) && !empty($row['longitude'])): ?>
                                        <a href="https://www.google.com/maps?q=<?= urlencode($row['latitude']) ?>,<?= urlencode($row['longitude']) ?>" target="_blank" class="btn-peta-link" title="Buka Titik Koordinat di Google Maps">
                                            <i class="fas fa-location-arrow"></i> Peta
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Empty Search Notice -->
                <div id="emptySearchNotice" style="display:none;padding:40px 20px;text-align:center;background:#fff;border-radius:8px;border:1px dashed #CBD5E1;margin-top:16px">
                    <i class="fas fa-search-location" style="font-size:38px;color:#94A3B8;margin-bottom:12px;display:block"></i>
                    <h4 style="font-size:15px;font-weight:700;color:#334155;margin-bottom:6px">Laporan Tidak Ditemukan</h4>
                    <p style="font-size:13px;color:#64748B;max-width:500px;margin:0 auto">Tidak ditemukan laporan kejadian yang cocok dengan pencarian Anda. Jika lokasi kejadian Anda belum pernah dilaporkan, silakan buat laporan baru.</p>
                    <div style="margin-top:16px">
                        <a href="register.php" class="btn-hero-primary" style="padding:9px 20px;font-size:13px"><i class="fas fa-plus-circle"></i> Buat Laporan Baru</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

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

    <!-- MODAL PREVIEW FOTO KEJADIAN -->
    <div id="photoModal" class="photo-modal" onclick="if(event.target===this) closePhotoModal()">
        <div class="photo-modal-content">
            <img id="photoModalImg" src="" alt="Bukti Foto Penuh" class="photo-modal-img">
            <div class="photo-modal-body">
                <div class="photo-modal-info">
                    <h5 id="photoModalLokasi">-</h5>
                    <p>
                        <i class="fas fa-calendar-alt"></i> Tanggal Kejadian: <span id="photoModalTanggal" style="font-weight:600">-</span>
                        &nbsp;•&nbsp; <span id="photoModalJenis" style="color:#1A3A6B;font-weight:700"></span>
                    </p>
                </div>
                <button type="button" class="photo-modal-close" onclick="closePhotoModal()" title="Tutup">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- SCRIPT FILTER & MODAL -->
    <script>
        function openPhotoModal(url, lokasi, tanggal, jenis) {
            document.getElementById('photoModalImg').src = url;
            document.getElementById('photoModalLokasi').textContent = lokasi;
            document.getElementById('photoModalTanggal').textContent = tanggal;
            document.getElementById('photoModalJenis').textContent = jenis;
            document.getElementById('photoModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closePhotoModal() {
            document.getElementById('photoModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closePhotoModal();
        });

        function filterLaporan() {
            const query = (document.getElementById('inputCariLaporan').value || '').toLowerCase().trim();
            const statusFilter = document.getElementById('selectStatusLaporan').value;
            const items = document.querySelectorAll('.item-laporan');
            let visibleCount = 0;

            items.forEach(function(item) {
                const lokasi = (item.dataset.lokasi || '').toLowerCase();
                const jenis = (item.dataset.jenis || '').toLowerCase();
                const deskripsi = (item.dataset.deskripsi || '').toLowerCase();
                const status = item.dataset.status || '';

                const matchesQuery = !query || lokasi.includes(query) || jenis.includes(query) || deskripsi.includes(query);
                const matchesStatus = !statusFilter || status === statusFilter;

                if (matchesQuery && matchesStatus) {
                    item.style.display = 'flex';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            const countEl = document.getElementById('visibleCount');
            if (countEl) countEl.textContent = visibleCount;

            const emptyNotice = document.getElementById('emptySearchNotice');
            if (emptyNotice) {
                emptyNotice.style.display = (visibleCount === 0) ? 'block' : 'none';
            }
        }
    </script>
</body>

</html>