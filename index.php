<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BSIS</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="images/isss.png" rel="icon">

    <style>
        :root {
            --bg-main: #020617;
            --bg-elevated: #020617;
            --bg-card: #020617;
            --bg-soft: #020617;
            --accent: #22c55e;
            --accent-soft: rgba(34, 197, 94, 0.16);
            --accent-strong: #4ade80;
            --accent-alt: #0ea5e9;
            --text-main: #e5e7eb;
            --text-soft: #9ca3af;
            --border-subtle: rgba(148, 163, 184, 0.25);
            --glass: rgba(15, 23, 42, 0.82);
            --radius-lg: 22px;
            --radius-md: 18px;
            --radius-pill: 999px;
            --shadow-soft: 0 24px 60px rgba(0, 0, 0, 0.7);
            --shadow-glow: 0 0 80px rgba(34, 197, 94, 0.45);
        }

        * {
            box-sizing: border-box;
            padding: 0;
            margin: 0;
        }

        html {
            scroll-behavior: smooth;
            scroll-padding-top: 100px;
        }

        /* Adjust scroll padding for mobile to account for navbar */
        @media (max-width: 960px) {
            html {
                scroll-padding-top: 80px;
            }
        }

        @media (max-width: 720px) {
            html {
                scroll-padding-top: 70px;
            }
        }

        @media (max-width: 480px) {
            html {
                scroll-padding-top: 60px;
            }
        }

        body {
            height: 100%;
        }

        body {
            font-family: 'Space Grotesk', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: radial-gradient(circle at top left, #0f172a 0, #020617 45%, #000 100%);
            background-attachment: fixed;
            color: var(--text-main);
            -webkit-font-smoothing: antialiased;
            opacity: 0;
            transform: translateY(10px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }

        body.modal-open .page-shell {
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease-out;
        }

        body.page-ready {
            opacity: 1;
            transform: translateY(0);
        }

        /* Reveal-on-scroll helpers */
        .fade-section {
            opacity: 0;
            transform: translateY(26px);
            transition: opacity 0.7s ease-out, transform 0.7s ease-out;
        }

        .fade-section.in-view {
            opacity: 1;
            transform: translateY(0);
        }

        .page-shell {
            min-height: 100vh;
            background-image:
                radial-gradient(circle at 0% 0%, rgba(56, 189, 248, 0.22), transparent 55%),
                radial-gradient(circle at 80% 0%, rgba(34, 197, 94, 0.18), transparent 55%),
                radial-gradient(circle at 0% 80%, rgba(129, 140, 248, 0.16), transparent 55%);
            background-attachment: fixed;
            background-blend-mode: screen;
        }

        .shell-inner {
            max-width: 1320px;
            margin: 0 auto;
            padding: 26px 20px 20px;
            position: relative;
        }

        @media (max-width: 480px) {
            .shell-inner {
                padding: 16px 12px 10px;
            }
        }

        .blur-orb {
            position: fixed;
            inset: auto auto 10% -10%;
            width: 420px;
            height: 420px;
            background: radial-gradient(circle, rgba(34, 197, 94, 0.16), transparent 60%);
            filter: blur(40px);
            z-index: -1;
            opacity: 0.8;
        }

        @media (max-width: 720px) {
            .blur-orb {
                width: 300px;
                height: 300px;
                opacity: 0.6;
            }
        }

        @media (max-width: 480px) {
            .blur-orb {
                width: 200px;
                height: 200px;
                opacity: 0.4;
            }
        }

        /* Floating Background Animation - Code Snippets */
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
            pointer-events: none;
        }

        .floating-shape {
            position: absolute;
            user-select: none;
            opacity: 0.1;
            transform-origin: center;
        }

        .floating-shape img {
            width: 120px;
            height: 120px;
            object-fit: contain;
            filter: brightness(0.7) contrast(1.1);
            transition: opacity 0.3s ease;
        }

        .floating-shape:nth-child(1) {
            top: 10%;
            left: 5%;
            animation: float1 25s ease-in-out infinite;
        }

        .floating-shape:nth-child(2) {
            top: 60%;
            left: 80%;
            animation: float2 30s ease-in-out infinite;
        }

        .floating-shape:nth-child(3) {
            top: 30%;
            left: 70%;
            animation: float3 22s ease-in-out infinite;
        }

        .floating-shape:nth-child(4) {
            top: 80%;
            left: 20%;
            animation: float4 28s ease-in-out infinite;
        }

        .floating-shape:nth-child(5) {
            top: 50%;
            left: 10%;
            animation: float5 26s ease-in-out infinite;
        }

        .floating-shape:nth-child(6) {
            top: 20%;
            left: 50%;
            animation: float6 24s ease-in-out infinite;
        }

        .floating-shape:nth-child(7) {
            top: 70%;
            left: 60%;
            animation: float7 27s ease-in-out infinite;
        }

        .floating-shape:nth-child(8) {
            top: 40%;
            left: 15%;
            animation: float8 23s ease-in-out infinite;
        }

        .floating-shape:nth-child(9) {
            top: 15%;
            left: 85%;
            animation: float9 29s ease-in-out infinite;
        }

        .floating-shape:nth-child(10) {
            top: 65%;
            left: 5%;
            animation: float10 25s ease-in-out infinite;
        }

        .floating-shape:nth-child(11) {
            top: 45%;
            left: 90%;
            animation: float11 31s ease-in-out infinite;
        }

        .floating-shape:nth-child(12) {
            top: 25%;
            left: 25%;
            animation: float12 27s ease-in-out infinite;
        }

        .floating-shape:nth-child(13) {
            top: 75%;
            left: 75%;
            animation: float13 26s ease-in-out infinite;
        }

        .floating-shape:nth-child(14) {
            top: 35%;
            left: 45%;
            animation: float14 28s ease-in-out infinite;
        }

        .floating-shape:nth-child(15) {
            top: 55%;
            left: 30%;
            animation: float15 24s ease-in-out infinite;
        }

        .floating-shape:nth-child(16) {
            top: 85%;
            left: 55%;
            animation: float16 30s ease-in-out infinite;
        }

        .floating-shape:nth-child(17) {
            top: 5%;
            left: 40%;
            animation: float17 32s ease-in-out infinite;
        }

        .floating-shape:nth-child(18) {
            top: 90%;
            left: 90%;
            animation: float18 25s ease-in-out infinite;
        }

        @keyframes float1 {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            25% {
                transform: translate(50px, -80px) rotate(2deg);
            }
            50% {
                transform: translate(-30px, -120px) rotate(-1deg);
            }
            75% {
                transform: translate(80px, -40px) rotate(1deg);
            }
        }

        @keyframes float2 {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            33% {
                transform: translate(-60px, 70px) rotate(-2deg);
            }
            66% {
                transform: translate(40px, -50px) rotate(1deg);
            }
        }

        @keyframes float3 {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            25% {
                transform: translate(-70px, 50px) rotate(1.5deg);
            }
            50% {
                transform: translate(30px, 90px) rotate(-1.5deg);
            }
            75% {
                transform: translate(-40px, -30px) rotate(0.5deg);
            }
        }

        @keyframes float4 {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            30% {
                transform: translate(60px, -60px) rotate(-1deg);
            }
            60% {
                transform: translate(-50px, 40px) rotate(2deg);
            }
        }

        @keyframes float5 {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            20% {
                transform: translate(-40px, -50px) rotate(1deg);
            }
            40% {
                transform: translate(70px, 60px) rotate(-1deg);
            }
            60% {
                transform: translate(-30px, 80px) rotate(1.5deg);
            }
            80% {
                transform: translate(50px, -40px) rotate(-0.5deg);
            }
        }

        @keyframes float6 {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            25% {
                transform: translate(40px, 70px) rotate(-1deg);
            }
            50% {
                transform: translate(-60px, -40px) rotate(2deg);
            }
            75% {
                transform: translate(30px, -60px) rotate(-1deg);
            }
        }

        @keyframes float7 {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            30% {
                transform: translate(-50px, 60px) rotate(1deg);
            }
            60% {
                transform: translate(40px, -70px) rotate(-1.5deg);
            }
        }

        @keyframes float8 {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            25% {
                transform: translate(55px, -55px) rotate(-1deg);
            }
            50% {
                transform: translate(-45px, 65px) rotate(1deg);
            }
            75% {
                transform: translate(35px, -35px) rotate(-0.5deg);
            }
        }

        @keyframes float9 {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            33% {
                transform: translate(-45px, 55px) rotate(1.5deg);
            }
            66% {
                transform: translate(50px, -65px) rotate(-1deg);
            }
        }

        @keyframes float10 {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            20% {
                transform: translate(60px, 40px) rotate(-1.5deg);
            }
            40% {
                transform: translate(-35px, 75px) rotate(1deg);
            }
            60% {
                transform: translate(45px, -50px) rotate(-0.5deg);
            }
            80% {
                transform: translate(-25px, 30px) rotate(1deg);
            }
        }

        @keyframes float11 {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            25% {
                transform: translate(-70px, -50px) rotate(2deg);
            }
            50% {
                transform: translate(40px, 80px) rotate(-1.5deg);
            }
            75% {
                transform: translate(-30px, -40px) rotate(1deg);
            }
        }

        @keyframes float12 {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            30% {
                transform: translate(55px, 60px) rotate(-1deg);
            }
            60% {
                transform: translate(-50px, -45px) rotate(1.5deg);
            }
        }

        @keyframes float13 {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            25% {
                transform: translate(-40px, 70px) rotate(1deg);
            }
            50% {
                transform: translate(65px, -55px) rotate(-2deg);
            }
            75% {
                transform: translate(-35px, 45px) rotate(0.5deg);
            }
        }

        @keyframes float14 {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            20% {
                transform: translate(50px, -60px) rotate(-1deg);
            }
            40% {
                transform: translate(-60px, 50px) rotate(1.5deg);
            }
            60% {
                transform: translate(35px, 70px) rotate(-0.5deg);
            }
            80% {
                transform: translate(-45px, -35px) rotate(1deg);
            }
        }

        @keyframes float15 {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            33% {
                transform: translate(-55px, -70px) rotate(1deg);
            }
            66% {
                transform: translate(70px, 45px) rotate(-1.5deg);
            }
        }

        @keyframes float16 {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            25% {
                transform: translate(45px, 55px) rotate(-1deg);
            }
            50% {
                transform: translate(-70px, -40px) rotate(2deg);
            }
            75% {
                transform: translate(30px, -60px) rotate(-0.5deg);
            }
        }

        @keyframes float17 {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            30% {
                transform: translate(-50px, 65px) rotate(1.5deg);
            }
            60% {
                transform: translate(55px, -50px) rotate(-1deg);
            }
        }

        @keyframes float18 {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            20% {
                transform: translate(40px, -55px) rotate(-1deg);
            }
            40% {
                transform: translate(-65px, 40px) rotate(1.5deg);
            }
            60% {
                transform: translate(50px, 60px) rotate(-0.5deg);
            }
            80% {
                transform: translate(-35px, -45px) rotate(1deg);
            }
        }

        @media (max-width: 720px) {
            .floating-shape {
                opacity: 0.2;
            }
            .floating-shape img {
                width: 90px;
                height: 90px;
            }
        }

        @media (max-width: 480px) {
            .floating-shape {
                opacity: 0.15;
            }
            .floating-shape img {
                width: 70px;
                height: 70px;
            }
        }

        /* Top Nav */
        .nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 32px;
            padding: 10px 18px;
            border-radius: var(--radius-pill);
            border: 1px solid rgba(148, 163, 184, 0.3);
            background: radial-gradient(circle at top left, rgba(15, 23, 42, 0.94), rgba(15, 23, 42, 0.94));
            backdrop-filter: blur(22px);
            box-shadow: 0 18px 60px rgba(15, 23, 42, 0.9);
            position: sticky;
            top: 16px;
            z-index: 40;
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .logo-mark {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            overflow: hidden;
            display: grid;
            place-items: center;
            background: transparent;
            padding: 0;
        }

        .logo-mark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .logo-text-main {
            font-weight: 600;
            letter-spacing: 0.04em;
            font-size: 18px;
            display: inline-flex;
        }

        .logo-text-main .letter {
            display: inline-block;
            opacity: 0;
            transform: translateY(20px);
            animation: letterUp 0.6s ease-out forwards;
        }

        .logo-text-main .letter:nth-child(1) {
            animation-delay: 0.1s;
        }

        .logo-text-main .letter:nth-child(2) {
            animation-delay: 0.2s;
        }

        .logo-text-main .letter:nth-child(3) {
            animation-delay: 0.3s;
        }

        .logo-text-main .letter:nth-child(4) {
            animation-delay: 0.4s;
        }

        @keyframes letterUp {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .logo-dot {
            color: var(--accent);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 18px;
            font-size: 13px;
            color: var(--text-soft);
            flex-shrink: 0;
        }

        .nav-links a {
            text-decoration: none;
            color: inherit;
            padding: 8px 10px;
            border-radius: 999px;
            border: 1px solid transparent;
            transition: color 0.2s ease, border-color 0.2s ease, background 0.2s ease;
        }

        .nav-links a:hover {
            color: var(--text-main);
            border-color: rgba(148, 163, 184, 0.5);
            background: rgba(15, 23, 42, 0.8);
        }

        .nav-cta {
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
        }

        .badge-soft {
            padding: 6px 12px;
            border-radius: 999px;
            border: 1px solid rgba(34, 197, 94, 0.45);
            background: radial-gradient(circle at top left, rgba(34, 197, 94, 0.17), rgba(15, 23, 42, 0.9));
            font-size: 11px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--accent-strong);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .badge-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: radial-gradient(circle, #22c55e, #16a34a);
            box-shadow: 0 0 16px rgba(22, 163, 74, 0.9);
        }

        .btn {
            border-radius: var(--radius-pill);
            padding: 10px 18px;
            font-size: 13px;
            font-weight: 500;
            border: 1px solid rgba(148, 163, 184, 0.3);
            background: radial-gradient(circle at top left, rgba(15, 23, 42, 0.95), rgba(15, 23, 42, 0.98));
            color: var(--text-main);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease, background 0.15s ease;
            text-decoration: none;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 40px rgba(15, 23, 42, 0.9);
            border-color: rgba(148, 163, 184, 0.6);
        }

        .btn-primary {
            border-color: rgba(34, 197, 94, 0.7);
            background: linear-gradient(135deg, #22c55e, #0ea5e9);
            color: #020617;
            box-shadow: var(--shadow-glow);
        }

        .btn-primary.btn-large {
            padding: 14px 28px;
            font-size: 15px;
        }

        .btn-primary:hover {
            transform: translateY(-2px) scale(1.01);
            box-shadow: 0 0 40px rgba(34, 197, 94, 0.7);
        }

        .btn-ghost {
            background: radial-gradient(circle at top left, rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 1));
        }

        #searchBtn {
            border: none;
        }

        #searchBtn:hover {
            border: none;
        }

        .navbar-search-input {
            width: 0;
            opacity: 0;
            padding: 8px 0 !important;
            border: 1px solid rgba(148, 163, 184, 0.3) !important;
            border-radius: 6px !important;
            background: var(--bg-primary) !important;
            color: var(--text-main) !important;
            font-size: 0.9rem !important;
            outline: none !important;
            transition: width 0.3s ease, opacity 0.3s ease, padding 0.3s ease !important;
            overflow: hidden;
        }

        .navbar-search-input.active {
            width: 250px;
            opacity: 1;
            padding: 8px 16px !important;
        }

        /* Mobile search bar pop-out below navbar */
        @media (max-width: 960px) {
            .navbar-search-input {
                position: absolute !important;
                top: 100% !important;
                left: 16px !important;
                right: 16px !important;
                width: calc(100% - 32px) !important;
                max-width: none !important;
                margin-top: 12px !important;
                padding: 12px 16px !important;
                border-radius: 12px !important;
                background: radial-gradient(circle at top left, rgba(15, 23, 42, 0.94), rgba(15, 23, 42, 0.94)) !important;
                backdrop-filter: blur(22px) !important;
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4) !important;
                transform: translateY(-10px) !important;
                opacity: 0 !important;
                pointer-events: none !important;
                z-index: 50 !important;
            }

            .navbar-search-input.active {
                width: calc(100% - 32px) !important;
                opacity: 1 !important;
                transform: translateY(0) !important;
                pointer-events: auto !important;
                transition: opacity 0.3s ease, transform 0.3s ease !important;
            }

            .nav {
                position: relative !important;
            }
        }

        .btn-icon-circle {
            width: 22px;
            height: 22px;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.9);
            display: grid;
            place-items: center;
            font-size: 15px;
        }

        /* Hero Layout */
        .hero {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr);
            gap: 40px;
            margin-top: 80px;
            align-items: center;
            /* center text and right container vertically */
        }

        @media (max-width: 480px) {
            .hero {
                gap: 16px;
                margin-top: 24px;
            }
        }

        .hero-left {
            display: flex;
            flex-direction: column;
            gap: 24px;
            justify-content: center;
            /* center content within the left column */
            min-height: 60vh;
        }

        @media (max-width: 720px) {
            .hero-left {
                gap: 16px;
                min-height: auto;
            }
        }

        @media (max-width: 480px) {
            .hero-left {
                gap: 12px;
            }
        }

        .eyebrow-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        @media (max-width: 480px) {
            .eyebrow-row {
                gap: 6px;
            }
        }

        .pill-highlight {
            border-radius: 999px;
            padding: 6px 12px 6px 6px;
            border: 1px solid rgba(148, 163, 184, 0.4);
            background: radial-gradient(circle at top left, rgba(34, 197, 94, 0.16), rgba(15, 23, 42, 0.98));
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            color: var(--text-soft);
        }

        .pill-highlight strong {
            color: var(--accent);
        }

        .pill-chip {
            border-radius: 999px;
            padding: 3px 10px;
            background: rgba(15, 23, 42, 0.9);
            border: 1px solid rgba(55, 65, 81, 0.9);
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.13em;
        }

        .hero-title {
            font-size: clamp(2.4rem, 3.4vw, 3.2rem);
            line-height: 1.08;
            letter-spacing: -0.03em;
        }

        .hero-title span.accent {
            background: linear-gradient(135deg, #22c55e, #0ea5e9);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .hero-sub {
            max-width: 460px;
            font-size: 0.98rem;
            color: var(--text-soft);
        }

        .hero-metrics {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            margin-top: 10px;
        }

        @media (max-width: 720px) {
            .hero-metrics {
                gap: 12px;
                margin-top: 8px;
            }
        }

        @media (max-width: 480px) {
            .hero-metrics {
                gap: 8px;
                margin-top: 6px;
            }
        }

        .metric {
            min-width: 120px;
        }

        .metric-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--text-soft);
        }

        .metric-value {
            font-size: 1.4rem;
            font-weight: 600;
        }

        .metric-pill {
            padding: 4px 9px;
            border-radius: 999px;
            font-size: 11px;
            margin-top: 4px;
        }

        .metric-pill.positive {
            background: rgba(22, 163, 74, 0.14);
            color: var(--accent-strong);
        }

        .metric-pill.subtle {
            background: rgba(15, 23, 42, 0.9);
            color: var(--text-soft);
            border: 1px solid rgba(55, 65, 81, 0.9);
        }

        .hero-ctas {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 10px;
        }

        @media (max-width: 720px) {
            .hero-ctas {
                gap: 10px;
                margin-top: 8px;
            }
        }

        @media (max-width: 480px) {
            .hero-ctas {
                gap: 8px;
                margin-top: 6px;
            }
        }

        .hero-footnote {
            margin-top: 6px;
            font-size: 11px;
            color: var(--text-soft);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .hero-footnote span {
            color: var(--accent-alt);
        }

        .trust-row {
            display: flex;
            flex-wrap: wrap;
            gap: 24px;
            align-items: center;
            margin-top: 10px;
        }

        @media (max-width: 720px) {
            .trust-row {
                gap: 16px;
                margin-top: 8px;
            }
        }

        @media (max-width: 480px) {
            .trust-row {
                gap: 12px;
                margin-top: 6px;
            }
        }

        .avatars {
            display: flex;
        }

        .avatar {
            width: 28px;
            height: 28px;
            border-radius: 999px;
            background: linear-gradient(145deg, #0ea5e9, #22c55e);
            border: 2px solid #020617;
            display: grid;
            place-items: center;
            font-size: 12px;
            font-weight: 600;
            color: #020617;
            box-shadow: 0 0 0 1px rgba(15, 23, 42, 0.9);
        }

        .avatar:nth-child(2) {
            transform: translateX(-10px);
            background: linear-gradient(145deg, #22c55e, #a855f7);
        }

        .avatar:nth-child(3) {
            transform: translateX(-20px);
            background: linear-gradient(145deg, #a855f7, #f97316);
        }

        .trust-text {
            font-size: 11px;
            color: var(--text-soft);
        }

        .trust-text strong {
            color: var(--text-main);
        }

        /* Hero Right */
        .hero-right {
            position: relative;
            min-height: 420px;
            /* give the right container more height */
        }

        @media (max-width: 720px) {
            .hero-right {
                min-height: 250px;
            }
        }

        @media (max-width: 480px) {
            .hero-right {
                min-height: 180px;
            }
        }

        .glass-orbit {
            position: absolute;
            inset: 0;
            border-radius: 32px;
            border: 1px solid rgba(148, 163, 184, 0.55);
            background: radial-gradient(circle at 0% 0%, rgba(34, 197, 94, 0.18), transparent 60%),
                radial-gradient(circle at 100% 0%, rgba(56, 189, 248, 0.15), transparent 55%),
                radial-gradient(circle at 0% 100%, rgba(129, 140, 248, 0.14), transparent 50%),
                rgba(15, 23, 42, 0.96);
            box-shadow: var(--shadow-soft);
            overflow: hidden;
        }

        .glass-grid {
            position: absolute;
            inset: 0;
            background-image: linear-gradient(rgba(15, 23, 42, 0.0) 0, rgba(15, 23, 42, 0.7) 55%, rgba(15, 23, 42, 0.95) 100%),
                linear-gradient(90deg, rgba(148, 163, 184, 0.07) 1px, transparent 1px),
                linear-gradient(180deg, rgba(148, 163, 184, 0.07) 1px, transparent 1px);
            background-size: auto, 38px 38px, 38px 38px;
            opacity: 0.9;
        }

        .hero-logo-large {
            position: relative;
            z-index: 1;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero-logo-large img {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
            filter: none;
        }

        /* Mission Section */
        .mission-section {
            position: absolute;
            top: 150px;
            /* Align with hero section (80px margin-top + 26px padding + 80px hero margin-top) */
            right: 20px;
            width: calc(50% - 20px);
            max-width: 600px;
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 1s ease-out, transform 1s ease-out;
            pointer-events: none;
        }

        @media (max-width: 960px) {
            .mission-section {
                position: relative;
                top: auto;
                right: auto;
                width: 100%;
                margin-top: 100px;
            }

            .mission-content {
                text-align: center;
                margin-inline: 16px;
            }

            .mission-title {
                text-align: center;
            }

            .mission-text {
                text-align: center;
            }
        }

        .mission-section.visible {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        .mission-content {
            border-radius: var(--radius-md);
            padding: 24px 28px;
            background: radial-gradient(circle at top left, rgba(34, 197, 94, 0.15), rgba(15, 23, 42, 0.98));
            border: 1px solid rgba(148, 163, 184, 0.45);
            box-shadow: var(--shadow-soft);
        }

        @media (max-width: 720px) {
            .mission-content {
                padding: 18px 20px;
                margin-inline: 14px;
            }
        }

        @media (max-width: 480px) {
            .mission-content {
                padding: 14px 16px;
                margin-inline: 12px;
            }
        }

        .mission-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 16px;
            color: var(--accent);
            letter-spacing: 0.05em;
        }

        @media (max-width: 720px) {
            .mission-title {
                font-size: 1.2rem;
                margin-bottom: 12px;
            }
        }

        @media (max-width: 480px) {
            .mission-title {
                font-size: 1.1rem;
                margin-bottom: 10px;
            }
        }

        .mission-text {
            font-size: 0.95rem;
            line-height: 1.7;
            color: var(--text-main);
        }

        /* Vision Section */
        .vision-section {
            position: absolute;
            top: 410px;
            /* Position below mission section */
            right: 20px;
            width: calc(50% - 20px);
            max-width: 600px;
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 1s ease-out, transform 1s ease-out;
            pointer-events: none;
        }

        @media (max-width: 960px) {
            .vision-section {
                position: relative;
                top: auto;
                right: auto;
                width: 100%;
                margin-top: 40px;
            }

            .vision-content {
                text-align: center;
                margin-inline: 16px;
            }

            .vision-title {
                text-align: center;
            }

            .vision-text {
                text-align: center;
            }
        }

        .vision-section.visible {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        .vision-content {
            border-radius: var(--radius-md);
            padding: 24px 28px;
            background: radial-gradient(circle at top left, rgba(56, 189, 248, 0.18), rgba(15, 23, 42, 0.98));
            border: 1px solid rgba(148, 163, 184, 0.45);
            box-shadow: var(--shadow-soft);
        }

        @media (max-width: 720px) {
            .vision-content {
                padding: 18px 20px;
                margin-inline: 14px;
            }
        }

        @media (max-width: 480px) {
            .vision-content {
                padding: 14px 16px;
                margin-inline: 12px;
            }
        }

        .vision-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 16px;
            color: var(--accent-alt);
            letter-spacing: 0.05em;
        }

        @media (max-width: 720px) {
            .vision-title {
                font-size: 1.2rem;
                margin-bottom: 12px;
            }
        }

        @media (max-width: 480px) {
            .vision-title {
                font-size: 1.1rem;
                margin-bottom: 10px;
            }
        }

        .vision-text {
            font-size: 0.95rem;
            line-height: 1.7;
            color: var(--text-main);
        }

        /* Announcement Section */
        .announcement-section {
            margin-top: 140px;
            border-radius: var(--radius-lg);
            padding: 32px;
            background: radial-gradient(circle at top left, rgba(34, 197, 94, 0.12), rgba(15, 23, 42, 0.98));
            border: 1px solid rgba(148, 163, 184, 0.4);
            box-shadow: 0 14px 40px rgba(15, 23, 42, 0.9);
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        @media (max-width: 960px) {
            .announcement-section {
                margin-top: 240px;
                scroll-margin-top: 80px;
            }
        }

        @media (max-width: 720px) {
            .announcement-section {
                padding: 24px 20px;
                margin-top: 220px;
                scroll-margin-top: 70px;
            }
        }

        @media (max-width: 480px) {
            .announcement-section {
                padding: 20px 16px;
                margin-top: 200px;
                gap: 20px;
                scroll-margin-top: 60px;
            }
        }

        .announcement-header {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .announcement-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: rgba(34, 197, 94, 0.2);
            border: 1px solid rgba(34, 197, 94, 0.5);
            display: grid;
            place-items: center;
            font-size: 20px;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.15);
        }

        .announcement-content {
            flex: 1;
        }

        .announcement-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 6px;
            color: var(--text-main);
            letter-spacing: 0.02em;
        }

        .announcement-text {
            font-size: 0.9rem;
            color: var(--text-soft);
            line-height: 1.6;
        }

        @media (max-width: 960px) {
            .announcement-text {
                display: none;
            }
        }

        .announcement-display {
            min-height: 400px;
            border-radius: var(--radius-md);
            background: rgba(15, 23, 42, 0.6);
            border: 1px dashed rgba(148, 163, 184, 0.3);
            padding: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: var(--text-soft);
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .announcement-display:hover {
            border-color: rgba(34, 197, 94, 0.4);
            background: rgba(15, 23, 42, 0.7);
        }

        .announcement-display.empty {
            color: var(--text-soft);
            opacity: 0.6;
        }

        @media (max-width: 720px) {
            .announcement-display {
                min-height: 300px;
                padding: 24px 20px;
                font-size: 0.88rem;
            }
        }

        @media (max-width: 480px) {
            .announcement-display {
                min-height: 250px;
                padding: 20px 16px;
                font-size: 0.85rem;
            }
        }

        .announcement-scrollable {
            max-height: 380px;
            overflow-y: auto;
            padding-right: 10px;
            scrollbar-width: thin;
            scrollbar-color: rgba(148, 163, 184, 0.3) transparent;
        }

        .announcement-scrollable::-webkit-scrollbar {
            width: 8px;
        }

        .announcement-scrollable::-webkit-scrollbar-track {
            background: transparent;
            border-radius: 4px;
        }

        .announcement-scrollable::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.3);
            border-radius: 4px;
        }

        .announcement-scrollable::-webkit-scrollbar-thumb:hover {
            background: rgba(148, 163, 184, 0.5);
        }

        @media (max-width: 720px) {
            .announcement-scrollable {
                max-height: 320px;
            }
        }

        @media (max-width: 480px) {
            .announcement-scrollable {
                max-height: 280px;
            }
        }

        .announcement-content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 32px;
        }

        @media (max-width: 720px) {
            .announcement-content-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }

        /* Announcement items grid container */
        .announcement-items-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        @media (max-width: 720px) {
            .announcement-items-grid {
                grid-template-columns: 1fr;
                gap: 32px;
            }
        }

        @media (max-width: 480px) {
            .announcement-items-grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }
        }

        /* Announcement item styling */
        .announcement-item {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        @media (min-width: 721px) {
            .announcement-item:nth-child(odd) {
                padding-right: 20px;
                border-right: 1px solid rgba(148, 163, 184, 0.2);
            }

            .announcement-item:nth-child(even) {
                padding-left: 20px;
            }

            .announcement-item:nth-child(1),
            .announcement-item:nth-child(2) {
                padding-bottom: 32px;
                margin-bottom: 32px;
                border-bottom: 1px solid rgba(148, 163, 184, 0.2);
            }
        }

        @media (max-width: 720px) {
            .announcement-item {
                padding: 0 !important;
                border-right: none !important;
                border-left: none !important;
                padding-bottom: 24px !important;
                margin-bottom: 24px !important;
                border-bottom: 1px solid rgba(148, 163, 184, 0.2) !important;
            }

            .announcement-item:last-child {
                padding-bottom: 0 !important;
                margin-bottom: 0 !important;
                border-bottom: none !important;
            }
        }

        /* Empower Innovate Succeed Section */
        .empower-section {
            position: absolute;
            top: 200px;
            /* Align with hero section */
            left: 20px;
            width: calc(50% - 20px);
            max-width: 600px;
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 1s ease-out, transform 1s ease-out;
            pointer-events: none;
        }

        @media (max-width: 960px) {
            /* Position Core Values section as container */
            #coreValuesSection {
                position: relative;
                width: 100%;
                margin: 60px auto 10px;
                min-height: 500px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            /* Position CULTURE section to overlap Core Values */
            #cultureSection {
                position: absolute;
                top: 0;
                left: 50%;
                transform: translateX(-50%);
                width: 90%;
                max-width: 500px;
                min-height: 500px;
                display: flex;
                align-items: center;
                justify-content: center;
                opacity: 0;
                visibility: hidden;
                transition: opacity 2.5s ease-in-out, visibility 2.5s ease-in-out;
                pointer-events: none;
            }

            /* Position Core Values content to center */
            #coreValuesSection .empower-content {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 90%;
                max-width: 500px;
                text-align: center;
                opacity: 0;
                visibility: hidden;
                transition: opacity 2.5s ease-in-out, visibility 2.5s ease-in-out;
                pointer-events: none;
            }

            /* Position CULTURE content to center */
            #cultureSection .empower-content {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 90%;
                max-width: 500px;
                text-align: center;
            }

            #coreValuesSection.visible .empower-content,
            #cultureSection.visible {
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
            }

            .empower-content {
                align-items: center;
                text-align: center;
                width: 100%;
            }
        }

        .empower-section.visible {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        #cultureSection {
            opacity: 0;
            visibility: hidden;
            top: 260px;
        }

        #cultureSection.visible {
            opacity: 1;
            visibility: visible;
        }

        .empower-content {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        @media (max-width: 720px) {
            .empower-content {
                gap: 12px;
            }
        }

        @media (max-width: 480px) {
            .empower-content {
                gap: 10px;
            }
        }

        .core-values-title {
            font-size: clamp(2rem, 4vw, 3.5rem);
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: 0.05em;
            color: var(--text-main);
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        @media (max-width: 720px) {
            .core-values-title {
                font-size: clamp(2.5rem, 6vw, 3.5rem);
            }
        }

        @media (max-width: 480px) {
            .core-values-title {
                font-size: clamp(2.3rem, 5.5vw, 3.2rem);
            }
        }

        .empower-item {
            font-size: clamp(1.2rem, 2.5vw, 2rem);
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #22c55e, #0ea5e9);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-transform: uppercase;
        }

        .empower-description {
            margin-top: 16px;
            font-size: 0.95rem;
            line-height: 1.7;
            color: var(--text-main);
            max-width: 500px;
        }

        @media (max-width: 720px) {
            .empower-description {
                margin-top: 12px;
                font-size: 0.88rem;
            }
        }

        @media (max-width: 480px) {
            .empower-description {
                margin-top: 10px;
                font-size: 0.85rem;
            }
        }

        /* Hero Content Section */
        .hero-content-section {
            position: absolute;
            top: 186px;
            /* Align with hero section */
            left: 20px;
            width: calc(50% - 20px);
            max-width: 600px;
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 1s ease-out, transform 1s ease-out;
            pointer-events: none;
            display: flex;
            flex-direction: column;
            gap: 24px;
            padding: 20px 0;
        }

        @media (max-width: 720px) {
            .hero-content-section {
                gap: 16px;
                padding: 16px 0;
            }
        }

        @media (max-width: 480px) {
            .hero-content-section {
                gap: 12px;
                padding: 12px 0;
            }
        }

        @media (max-width: 960px) {
            .hero-content-section {
                display: none;
            }
        }

        .hero-content-section.visible {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        .hero-content-section .hero-title {
            margin-bottom: 0;
        }

        .hero-content-section .hero-sub {
            margin-top: 0;
            margin-bottom: 0;
        }

        .hero-content-section .hero-ctas {
            margin-top: 10px;
        }

        .bot-core {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 28px 26px 24px;
            gap: 18px;
        }

        .bot-orbit-ring {
            width: 190px;
            height: 190px;
            border-radius: 50%;
            border: 1px dashed rgba(148, 163, 184, 0.5);
            display: grid;
            place-items: center;
            position: relative;
        }

        .bot-orbit-ring::before {
            content: '';
            position: absolute;
            inset: 26px;
            border-radius: 50%;
            border: 1px solid rgba(34, 197, 94, 0.7);
            box-shadow: 0 0 40px rgba(34, 197, 94, 0.4);
        }

        .bot-orbit-ring::after {
            content: '';
            position: absolute;
            inset: 56px;
            border-radius: 50%;
            border: 1px solid rgba(56, 189, 248, 0.6);
        }

        .bot-sphere {
            width: 86px;
            height: 86px;
            border-radius: 50%;
            background:
                radial-gradient(circle at 30% 15%, #e5e7eb, #6b7280 50%, #020617 100%);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.9);
            position: relative;
            overflow: hidden;
        }

        .bot-sphere::before {
            content: '';
            position: absolute;
            inset: 10px;
            border-radius: 50%;
            border: 1px solid rgba(34, 197, 94, 0.8);
            box-shadow: inset 0 0 35px rgba(34, 197, 94, 0.5);
        }

        .bot-sphere::after {
            content: '';
            position: absolute;
            inset: 40% 26% 18% 26%;
            border-radius: 999px;
            background: radial-gradient(circle at 50% 0%, #22c55e, #16a34a);
        }

        .spark-row {
            display: flex;
            justify-content: space-between;
            width: 100%;
            gap: 10px;
        }

        .spark-card {
            flex: 1;
            border-radius: 16px;
            padding: 10px 11px;
            background: rgba(15, 23, 42, 0.95);
            border: 1px solid rgba(148, 163, 184, 0.45);
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .spark-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            color: var(--text-soft);
        }

        .spark-value {
            font-size: 0.92rem;
            font-weight: 500;
        }

        .spark-tag {
            margin-top: 3px;
            font-size: 10px;
            border-radius: 999px;
            padding: 2px 7px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .spark-tag.green {
            background: rgba(22, 163, 74, 0.14);
            color: var(--accent-strong);
        }

        .spark-tag.neutral {
            background: rgba(15, 23, 42, 0.9);
            color: var(--text-soft);
            border: 1px solid rgba(55, 65, 81, 0.9);
        }

        .spark-tag-dot {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: radial-gradient(circle, #22c55e, #16a34a);
        }

        .sparkline {
            margin-top: 5px;
            height: 32px;
            width: 100%;
            border-radius: 999px;
            background-image: linear-gradient(120deg, rgba(34, 197, 94, 0.12), transparent 30%, rgba(34, 197, 94, 0.4) 60%, transparent 75%, rgba(14, 165, 233, 0.4) 90%);
            position: relative;
            overflow: hidden;
        }

        .sparkline::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url('data:image/svg+xml,%3Csvg width="240" height="48" viewBox="0 0 240 48" xmlns="http://www.w3.org/2000/svg"%3E%3Cpolyline fill="none" stroke="%2322c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" points="0,34 22,30 40,32 58,18 76,22 92,10 110,16 130,6 148,14 166,8 184,16 204,12 224,18 240,10" /%3E%3C/svg%3E');
            background-size: cover;
            opacity: 0.88;
        }

        .status-strip {
            margin-top: 14px;
            border-radius: 999px;
            padding: 6px 10px;
            background: linear-gradient(90deg, rgba(34, 197, 94, 0.16), rgba(14, 165, 233, 0.08));
            border: 1px solid rgba(34, 197, 94, 0.4);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            font-size: 11px;
        }

        .status-strip span.label {
            text-transform: uppercase;
            letter-spacing: 0.14em;
            color: var(--accent-strong);
        }

        .status-strip span.badge {
            padding: 3px 8px;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.9);
            border: 1px solid rgba(55, 65, 81, 0.9);
            color: var(--text-soft);
        }

        /* Section: Why */
        .section {
            margin-top: 120px;
            /* push feature cards further down */
        }

        @media (max-width: 720px) {
            .section {
                margin-top: 50px;
            }
        }

        @media (max-width: 480px) {
            .section {
                margin-top: 40px;
            }
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 26px;
            margin-bottom: 22px;
        }

        @media (max-width: 720px) {
            .section-header {
                gap: 16px;
                margin-bottom: 16px;
            }
        }

        @media (max-width: 480px) {
            .section-header {
                gap: 12px;
                margin-bottom: 12px;
            }
        }

        .section-title {
            font-size: 1.18rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--text-soft);
        }

        .section-title span {
            color: var(--accent);
        }

        .section-subtitle {
            max-width: 440px;
            font-size: 0.9rem;
            color: var(--text-soft);
        }

        .why-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        @media (max-width: 720px) {
            .why-grid {
                gap: 12px;
            }
        }

        @media (max-width: 480px) {
            .why-grid {
                gap: 10px;
            }
        }

        .why-card {
            border-radius: var(--radius-md);
            padding: 16px 16px 15px;
            background: radial-gradient(circle at top left, rgba(34, 197, 94, 0.15), rgba(15, 23, 42, 1));
            border: 1px solid rgba(148, 163, 184, 0.45);
            box-shadow: 0 14px 40px rgba(15, 23, 42, 0.9);
        }

        @media (max-width: 720px) {
            .why-card {
                padding: 14px;
            }
        }

        @media (max-width: 480px) {
            .why-card {
                padding: 12px;
            }
        }

        .why-card:nth-child(2) {
            background: radial-gradient(circle at top left, rgba(56, 189, 248, 0.18), rgba(15, 23, 42, 1));
        }

        .why-card:nth-child(3) {
            background: radial-gradient(circle at top left, rgba(129, 140, 248, 0.22), rgba(15, 23, 42, 1));
        }

        .why-icon {
            width: 28px;
            height: 28px;
            border-radius: 10px;
            background: rgba(15, 23, 42, 0.9);
            border: 1px solid rgba(148, 163, 184, 0.5);
            display: grid;
            place-items: center;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .why-title {
            font-size: 0.98rem;
            margin-bottom: 5px;
        }

        .why-body {
            font-size: 0.86rem;
            color: var(--text-soft);
        }

        /* Market Table */
        .table-shell {
            margin-top: 26px;
            border-radius: 20px;
            border: 1px solid rgba(148, 163, 184, 0.55);
            background: radial-gradient(circle at top left, rgba(15, 23, 42, 0.96), #020617);
            box-shadow: var(--shadow-soft);
            overflow: hidden;
        }

        @media (max-width: 720px) {
            .table-shell {
                margin-top: 18px;
                border-radius: 16px;
            }
        }

        @media (max-width: 480px) {
            .table-shell {
                margin-top: 14px;
                border-radius: 14px;
            }
        }

        .table-tabs {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px 10px;
            border-bottom: 1px solid rgba(30, 64, 175, 0.6);
            background: radial-gradient(circle at 0% 0%, rgba(34, 197, 94, 0.1), rgba(15, 23, 42, 0.98));
        }

        .tab-group {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 2px;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.9);
        }

        .tab {
            padding: 5px 11px;
            font-size: 11px;
            border-radius: 999px;
            border: 1px solid transparent;
            color: var(--text-soft);
            cursor: pointer;
        }

        .tab.active {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.18), rgba(14, 165, 233, 0.18));
            color: var(--accent-strong);
            border-color: rgba(34, 197, 94, 0.7);
        }

        .table-latency {
            font-size: 11px;
            color: var(--text-soft);
        }

        .table-latency span {
            color: var(--accent-strong);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.86rem;
        }

        thead {
            background: rgba(15, 23, 42, 0.96);
        }

        thead th {
            text-align: left;
            padding: 10px 16px 8px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            color: var(--text-soft);
            border-bottom: 1px solid rgba(31, 41, 55, 0.9);
        }

        tbody tr {
            border-bottom: 1px solid rgba(31, 41, 55, 0.9);
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        tbody td {
            padding: 8px 16px 9px;
        }

        tbody tr:hover {
            background: rgba(15, 23, 42, 0.96);
        }

        .pair {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pair-circle {
            width: 22px;
            height: 22px;
            border-radius: 999px;
            background: linear-gradient(145deg, #0ea5e9, #22c55e);
            display: grid;
            place-items: center;
            font-size: 11px;
            font-weight: 700;
            color: #020617;
        }

        .pair-symbol {
            font-weight: 500;
        }

        .pair-name {
            font-size: 11px;
            color: var(--text-soft);
        }

        .price {
            font-feature-settings: 'tnum' 1, 'lnum' 1;
        }

        .change-pos {
            color: var(--accent-strong);
        }

        .change-neg {
            color: #fb7185;
        }

        .vol {
            color: var(--text-soft);
        }

        .btn-mini {
            padding: 4px 10px;
            font-size: 11px;
            border-radius: 999px;
            border: 1px solid rgba(148, 163, 184, 0.6);
            background: rgba(15, 23, 42, 0.95);
            color: var(--text-soft);
        }

        .table-foot {
            padding: 7px 16px 9px;
            border-top: 1px solid rgba(31, 41, 55, 0.9);
            font-size: 11px;
            color: var(--text-soft);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Projects Section */
        .projects-section {
            margin-top: 50px;
        }

        @media (max-width: 960px) {
            .projects-section {
                margin-inline: 16px;
            }
        }

        @media (max-width: 720px) {
            .projects-section {
                margin-top: 10px;
                margin-inline: 14px;
            }
        }

        @media (max-width: 480px) {
            .projects-section {
                margin-top: 8px;
                margin-inline: 12px;
            }
        }

        .projects-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 26px;
            margin-bottom: 32px;
        }

        @media (max-width: 720px) {
            .projects-header {
                gap: 16px;
                margin-bottom: 24px;
            }
        }

        @media (max-width: 480px) {
            .projects-header {
                gap: 12px;
                margin-bottom: 20px;
            }
        }

        .projects-title {
            font-size: 1.18rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--text-soft);
        }

        .projects-title span {
            color: var(--accent);
        }

        .projects-carousel-wrapper {
            position: relative;
            overflow: hidden;
            border-radius: var(--radius-md);
        }

        .projects-carousel {
            position: relative;
            width: 100%;
        }

        .projects-carousel-track {
            display: flex;
            gap: 24px;
            transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            will-change: transform;
        }

        @media (max-width: 720px) {
            .projects-carousel-track {
                gap: 18px;
            }
        }

        @media (max-width: 480px) {
            .projects-carousel-track {
                gap: 16px;
            }
        }

        .project-card {
            flex: 0 0 calc(33.333% - 16px);
            max-width: calc(33.333% - 16px);
        }

        @media (max-width: 720px) {
            .project-card {
                flex: 0 0 calc(50% - 9px);
                max-width: calc(50% - 9px);
            }
        }

        @media (max-width: 480px) {
            .project-card {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }

        .projects-carousel-controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-top: 24px;
        }

        .carousel-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 1px solid rgba(148, 163, 184, 0.3);
            background: radial-gradient(circle at top left, rgba(34, 197, 94, 0.12), rgba(15, 23, 42, 0.98));
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 18px;
        }

        .carousel-btn:hover {
            border-color: rgba(34, 197, 94, 0.5);
            background: radial-gradient(circle at top left, rgba(34, 197, 94, 0.2), rgba(15, 23, 42, 0.98));
            transform: scale(1.1);
        }

        .carousel-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .carousel-dots {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .carousel-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(148, 163, 184, 0.3);
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 0;
        }

        .carousel-dot.active {
            background: var(--accent);
            width: 24px;
            border-radius: 4px;
        }

        .project-card {
            border-radius: var(--radius-md);
            background: radial-gradient(circle at top left, rgba(34, 197, 94, 0.12), rgba(15, 23, 42, 0.98));
            border: 1px solid rgba(148, 163, 184, 0.3);
            overflow: hidden;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .project-card:hover {
            border-color: rgba(34, 197, 94, 0.5);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
            transform: translateY(-2px);
        }

        .project-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }

        @media (max-width: 720px) {
            .project-image {
                height: 180px;
            }
        }

        @media (max-width: 480px) {
            .project-image {
                height: 160px;
            }
        }

        .project-content {
            padding: 20px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .project-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--text-main);
        }

        .project-description {
            font-size: 0.9rem;
            color: var(--text-soft);
            line-height: 1.6;
            flex: 1;
        }

        /* Faculty Section */
        .faculty-section {
            margin-top: 80px;
        }

        @media (max-width: 720px) {
            .faculty-section {
                margin-top: 90px;
            }
        }

        @media (max-width: 480px) {
            .faculty-section {
                margin-top: 80px;
            }
        }

        .faculty-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 26px;
            margin-bottom: 32px;
        }

        @media (max-width: 720px) {
            .faculty-header {
                gap: 16px;
                margin-bottom: 24px;
            }
        }

        @media (max-width: 480px) {
            .faculty-header {
                gap: 12px;
                margin-bottom: 20px;
            }
        }

        .faculty-title {
            font-size: 1.18rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--text-soft);
        }

        .faculty-title span {
            color: var(--accent);
        }

        .faculty-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 24px;
        }

        @media (max-width: 720px) {
            .faculty-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 18px;
            }
        }

        @media (max-width: 480px) {
            .faculty-grid {
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
                gap: 16px;
            }
        }

        .faculty-card {
            border-radius: var(--radius-md);
            padding: 16px;
            background: radial-gradient(circle at top left, rgba(34, 197, 94, 0.12), rgba(15, 23, 42, 0.98));
            border: 1px solid rgba(148, 163, 184, 0.4);
            box-shadow: 0 14px 40px rgba(15, 23, 42, 0.9);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .faculty-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.95);
            border-color: rgba(34, 197, 94, 0.6);
        }

        .faculty-image {
            width: 100%;
            aspect-ratio: 1;
            border-radius: 16px;
            object-fit: cover;
            border: 2px solid rgba(148, 163, 184, 0.3);
            background: rgba(15, 23, 42, 0.9);
        }

        .faculty-name {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-main);
            text-align: center;
            margin-top: 4px;
        }

        .faculty-position {
            font-size: 0.82rem;
            color: var(--text-soft);
            text-align: center;
        }

        /* Partners Section */
        .partners-section {
            margin-top: 120px;
        }

        @media (max-width: 720px) {
            .partners-section {
                margin-top: 90px;
            }
        }

        @media (max-width: 480px) {
            .partners-section {
                margin-top: 80px;
            }
        }

        .partners-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 26px;
            margin-bottom: 32px;
        }

        @media (max-width: 720px) {
            .partners-header {
                gap: 16px;
                margin-bottom: 24px;
            }
        }

        @media (max-width: 480px) {
            .partners-header {
                gap: 12px;
                margin-bottom: 20px;
            }
        }

        .partners-title {
            font-size: 1.18rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--text-soft);
        }

        .partners-title span {
            color: var(--accent);
        }

        .partners-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .partners-logos-wrapper {
            position: relative;
            width: 100%;
            overflow: visible;
            margin-bottom: 8px;
            display: flex;
            justify-content: center;
        }

        .partners-logos {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0;
            flex-wrap: nowrap;
            position: relative;
            width: 420px;
            height: 140px;
            margin: 0 auto;
        }

        .partners-logo {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            padding: 0;
            background: rgba(15, 23, 42, 0.9);
            border: none;
            box-shadow: none;
            position: absolute;
            left: 50%;
            margin-left: -70px;
            transition: transform 0.8s cubic-bezier(0.4, 0.0, 0.2, 1), 
                        opacity 0.8s ease;
            z-index: 1;
            will-change: transform, opacity;
            cursor: pointer;
        }

        .partners-logo:hover {
            border: none;
            box-shadow: none;
        }

        .partners-logo-far-left {
            z-index: 0;
            transform: translateX(-210px) scale(0.85);
            opacity: 0;
            pointer-events: none;
        }

        .partners-logo-left {
            z-index: 2;
            transform: translateX(-140px) scale(0.75);
            opacity: 0.8;
        }

        .partners-logo-middle {
            z-index: 3;
            transform: translateX(0) scale(1);
            opacity: 1;
        }

        .partners-logo-right {
            z-index: 2;
            transform: translateX(140px) scale(0.75);
            opacity: 0.8;
        }

        .partners-logo.rotating {
            pointer-events: none;
        }

        @media (max-width: 720px) {
            .partners-logos {
                width: 350px;
                height: 120px;
            }
            
            .partners-logo {
                width: 120px;
                height: 120px;
                margin-left: -60px;
            }
            
            .partners-logo-far-left {
                transform: translateX(-180px) scale(0.85);
            }
            
            .partners-logo-left {
                transform: translateX(-120px) scale(0.9);
            }
            
            .partners-logo-right {
                transform: translateX(120px) scale(0.9);
            }
        }

        @media (max-width: 480px) {
            .partners-logos {
                width: 280px;
                height: 100px;
            }
            
            .partners-logo {
                width: 100px;
                height: 100px;
                margin-left: -50px;
            }
            
            .partners-logo-far-left {
                transform: translateX(-150px) scale(0.85);
            }
            
            .partners-logo-left {
                transform: translateX(-100px) scale(0.9);
            }
            
            .partners-logo-right {
                transform: translateX(100px) scale(0.9);
            }
        }

        .partners-carousel-controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-top: 24px;
        }

        .partners-carousel-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 1px solid rgba(148, 163, 184, 0.3);
            background: radial-gradient(circle at top left, rgba(34, 197, 94, 0.12), rgba(15, 23, 42, 0.98));
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 18px;
        }

        .partners-carousel-btn:hover {
            border-color: rgba(34, 197, 94, 0.5);
            background: radial-gradient(circle at top left, rgba(34, 197, 94, 0.2), rgba(15, 23, 42, 0.98));
            transform: scale(1.1);
        }

        .partners-carousel-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .partners-carousel-dots {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .partners-carousel-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(148, 163, 184, 0.3);
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 0;
        }

        .partners-carousel-dot.active {
            background: var(--accent);
            width: 24px;
            border-radius: 4px;
        }

        .partners-name {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-main);
            text-align: center;
            margin: 0;
            transition: opacity 0.6s cubic-bezier(0.4, 0.0, 0.2, 1);
            opacity: 1;
        }

        .partners-name.fade-out {
            opacity: 0;
        }

        .partners-name.fade-in {
            opacity: 1;
        }

        @media (max-width: 720px) {
            .partners-name {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .partners-name {
                font-size: 1.3rem;
            }
        }

        .partners-description {
            font-size: 1rem;
            color: var(--text-main);
            text-align: center;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            transition: opacity 0.6s cubic-bezier(0.4, 0.0, 0.2, 1);
            opacity: 1;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .partners-description.fade-out {
            opacity: 0;
        }

        .partners-description.fade-in {
            opacity: 1;
        }

        @media (max-width: 720px) {
            .partners-description {
                font-size: 0.95rem;
                padding: 0 16px;
            }
        }

        @media (max-width: 480px) {
            .partners-description {
                font-size: 0.9rem;
                padding: 0 12px;
            }
        }

        /* Contact Section */
        .contact-section {
            margin-top: 80px;
            margin-bottom: 20px;
        }

        @media (max-width: 720px) {
            .contact-section {
                margin-top: 90px;
                margin-bottom: 0;
            }
        }

        @media (max-width: 480px) {
            .contact-section {
                margin-top: 80px;
                margin-bottom: 0;
            }
        }

        .contact-header {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        @media (max-width: 960px) {
            .contact-header {
                grid-template-columns: 1fr;
                gap: 16px;
                margin-bottom: 8px;
            }

            .contact-title1 {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .contact-header {
                gap: 12px;
                margin-bottom: 6px;
            }
        }

        .contact-title {
            font-size: 1.18rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--text-soft);
        }

        .contact-title1 {
            font-size: 1.18rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--text-soft);
            margin-right: 755px;
        }

        .contact-title span {
            color: var(--accent);
        }

        .contact-title-center {
            margin-bottom: 16px;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 24px;
            margin-top: 20px;
        }

        @media (max-width: 960px) {
            .contact-grid {
                grid-template-columns: 1fr;
                gap: 18px;
                margin-top: 8px;
            }
        }

        @media (max-width: 720px) {
            .contact-grid {
                grid-template-columns: 1fr;
                gap: 18px;
            }
        }

        .contact-column {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .contact-map-column {
            min-height: 200px;
            align-self: start;
            margin-top: -48px;
        }

        @media (max-width: 960px) {
            .contact-map-column {
                margin-top: 0;
            }
        }

        .contact-map {
            width: 100%;
            min-width: 400px;
            height: 200px;
            border-radius: var(--radius-md);
            overflow: hidden;
            background: rgba(15, 23, 42, 0.5);
        }

        @media (max-width: 960px) {
            .contact-map {
                min-width: 100%;
            }
        }

        @media (max-width: 960px) {
            .contact-map {
                height: 180px;
            }
        }

        @media (max-width: 480px) {
            .contact-map {
                height: 150px;
            }
        }

        .contact-column-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 6px;
            letter-spacing: 0.02em;
        }

        .contact-info-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 0.95rem;
            line-height: 1.4;
            color: var(--text-main);
        }

        .contact-icon {
            font-size: 1.2rem;
            min-width: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent);
        }

        .contact-icon.facebook-icon {
            font-weight: 700;
            font-size: 1.1rem;
            width: 28px;
            height: 28px;
            border-radius: 4px;
            background-color: #1877f2;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
            min-width: 28px;
        }

        .contact-info-item div {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        @media (max-width: 720px) {
            .contact-column-title {
                font-size: 1rem;
            }

            .contact-info-item {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .contact-info-item {
                font-size: 0.85rem;
            }
        }

        /* Footer */
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            padding-bottom: 0;
            border-top: 1px solid rgba(31, 41, 55, 0.9);
            font-size: 0.82rem;
            color: var(--text-soft);
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            justify-content: flex-end;
            align-items: flex-start;
        }

        @media (max-width: 720px) {
            .footer {
                margin-top: 0;
                padding-top: 8px;
                padding-bottom: 0;
                gap: 14px;
                justify-content: center;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .footer {
                margin-top: 0;
                padding-top: 6px;
                padding-bottom: 0;
                gap: 12px;
                justify-content: center;
                text-align: center;
                align-items: center;
            }
        }

        .footer-links {
            display: flex;
            flex-wrap: wrap;
            gap: 12px 18px;
        }

        .footer-links a {
            text-decoration: none;
            color: inherit;
            font-size: 0.82rem;
        }

        .footer-tag {
            padding: 4px 9px;
            border-radius: 999px;
            border: 1px solid rgba(55, 65, 81, 0.9);
            background: rgba(15, 23, 42, 0.95);
            font-size: 0.76rem;
        }

        .footer-tag span {
            color: var(--accent-strong);
        }

        /* Scroll Transition Animations */
        .scroll-reveal {
            opacity: 0;
            transform: translateY(50px);
            transition: opacity 0.8s cubic-bezier(0.22, 0.61, 0.36, 1),
                transform 0.8s cubic-bezier(0.22, 0.61, 0.36, 1);
        }

        .scroll-reveal.revealed {
            opacity: 1;
            transform: translateY(0);
        }

        .scroll-reveal-left {
            opacity: 0;
            transform: translateX(-50px);
            transition: opacity 0.8s cubic-bezier(0.22, 0.61, 0.36, 1),
                transform 0.8s cubic-bezier(0.22, 0.61, 0.36, 1);
        }

        .scroll-reveal-left.revealed {
            opacity: 1;
            transform: translateX(0);
        }

        .scroll-reveal-right {
            opacity: 0;
            transform: translateX(50px);
            transition: opacity 0.8s cubic-bezier(0.22, 0.61, 0.36, 1),
                transform 0.8s cubic-bezier(0.22, 0.61, 0.36, 1);
        }

        .scroll-reveal-right.revealed {
            opacity: 1;
            transform: translateX(0);
        }

        .scroll-reveal-scale {
            opacity: 0;
            transform: scale(0.9);
            transition: opacity 0.8s cubic-bezier(0.22, 0.61, 0.36, 1),
                transform 0.8s cubic-bezier(0.22, 0.61, 0.36, 1);
        }

        .scroll-reveal-scale.revealed {
            opacity: 1;
            transform: scale(1);
        }

        .scroll-reveal-fade {
            opacity: 0;
            transition: opacity 1s cubic-bezier(0.22, 0.61, 0.36, 1);
        }

        .scroll-reveal-fade.revealed {
            opacity: 1;
        }

        /* Stagger delay for grid items */
        .scroll-reveal-delay-1 {
            transition-delay: 0.1s;
        }

        .scroll-reveal-delay-2 {
            transition-delay: 0.2s;
        }

        .scroll-reveal-delay-3 {
            transition-delay: 0.3s;
        }

        .scroll-reveal-delay-4 {
            transition-delay: 0.4s;
        }

        .scroll-reveal-delay-5 {
            transition-delay: 0.5s;
        }

        .scroll-reveal-delay-6 {
            transition-delay: 0.6s;
        }

        /* Responsive */
        @media (max-width: 960px) {
            html, body {
                overflow-x: hidden;
                width: 100%;
                max-width: 100%;
            }

            .page-shell {
                overflow-x: hidden;
                width: 100%;
                max-width: 100%;
            }

            .shell-inner {
                padding-inline: 16px;
                padding-bottom: 50px;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }

            .nav {
                padding-inline: 14px;
                padding-block: 6px;
                gap: 14px;
                flex-wrap: wrap;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }

            .nav-links {
                display: none;
            }

            .nav-cta {
                gap: 6px;
            }

            .btn {
                padding: 6px 12px;
                font-size: 11px;
            }

            #loginBtn {
                border-radius: 999px;
                padding: 4px 18px;
            }

            .hero {
                grid-template-columns: minmax(0, 1fr);
                gap: 28px;
                margin-top: 40px;
                padding: 0;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }

            .hero-left {
                min-height: auto;
                gap: 20px;
                padding-left: 24px;
                padding-right: 24px;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }

            .hero-right {
                display: none;
            }

            .hero-title {
                font-size: clamp(3.2rem, 10vw, 4rem);
                line-height: 1.2;
                letter-spacing: -0.02em;
                margin-top: 60px;
                margin-bottom: 8px;
                width: 100%;
                max-width: 100%;
                word-wrap: break-word;
                overflow-wrap: break-word;
                box-sizing: border-box;
            }

            .hero-sub {
                display: none;
            }


            .why-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 12px;
            }

            .section {
                margin-top: 60px;
            }

            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 14px;
                margin-bottom: 18px;
            }

            /* Fix absolutely positioned sections for tablet */
            .mission-section {
                margin-top: 100px;
            }

            .vision-section,
            .empower-section {
                position: relative;
                top: auto;
                left: auto;
                right: auto;
                width: 100%;
                max-width: 100%;
                margin-top: 30px;
                opacity: 1;
                transform: none;
                pointer-events: auto;
            }

            .hero-content-section {
                display: none;
            }
        }

        @media (max-width: 720px) {
            html, body {
                overflow-x: hidden;
                width: 100%;
                max-width: 100%;
            }

            .page-shell {
                overflow-x: hidden;
                width: 100%;
                max-width: 100%;
            }

            .shell-inner {
                padding-inline: 14px;
                padding-bottom: 40px;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }

            .nav {
                padding-inline: 12px;
                padding-block: 5px;
                gap: 10px;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }

            .logo-mark {
                width: 32px;
                height: 32px;
            }

            .logo-text-main {
                font-size: 16px;
            }

            .nav-cta {
                gap: 5px;
            }

            .btn {
                padding: 5px 10px;
                font-size: 10px;
            }

            #loginBtn {
                border-radius: 999px;
                padding: 3px 16px;
            }

            .btn-primary.btn-large {
                padding: 12px 24px;
                font-size: 14px;
            }

            .hero {
                margin-top: 32px;
                gap: 20px;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }

            .hero-left {
                gap: 18px;
                padding-left: 20px;
                padding-right: 20px;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }

            .hero-title {
                font-size: clamp(3rem, 11vw, 3.8rem);
                line-height: 1.25;
                letter-spacing: -0.02em;
                margin-top: 50px;
                margin-bottom: 6px;
                width: 100%;
                max-width: 100%;
                word-wrap: break-word;
                overflow-wrap: break-word;
                box-sizing: border-box;
            }

            .hero-sub {
                display: none;
            }

            .hero-right {
                display: none;
            }

            .hero-metrics {
                gap: 12px;
                margin-top: 6px;
            }

            .metric {
                min-width: 100px;
            }

            .metric-value {
                font-size: 1.2rem;
            }

            .hero-ctas {
                gap: 10px;
                margin-top: 12px;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }

            .hero-ctas .btn {
                max-width: 100%;
                box-sizing: border-box;
            }

            .why-grid {
                grid-template-columns: minmax(0, 1fr);
                gap: 10px;
            }

            .why-card {
                padding: 12px;
            }

            .section {
                margin-top: 45px;
            }

            .section-title {
                font-size: 1rem;
            }

            .section-subtitle {
                font-size: 0.85rem;
            }

            .section-header {
                gap: 12px;
                margin-bottom: 14px;
            }

            .table-shell {
                margin-top: 20px;
                border-radius: 16px;
                overflow-x: auto;
            }

            .table-tabs {
                padding: 10px 12px;
                flex-wrap: wrap;
                gap: 8px;
            }

            .tab-group {
                flex-wrap: wrap;
            }

            .table-latency {
                font-size: 10px;
            }

            table {
                font-size: 0.8rem;
                min-width: 600px;
            }

            thead {
                display: none;
            }

            table,
            tbody,
            tr,
            td {
                display: block;
                width: 100%;
            }

            tbody tr {
                padding: 12px;
                margin-bottom: 8px;
                border-radius: 12px;
                background: rgba(15, 23, 42, 0.5);
                border: 1px solid rgba(148, 163, 184, 0.3);
            }

            tbody td {
                padding: 4px 0;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            tbody td::before {
                content: attr(data-label);
                font-size: 10px;
                text-transform: uppercase;
                letter-spacing: 0.1em;
                color: var(--text-soft);
                margin-right: 12px;
                font-weight: 500;
            }

            tbody td:last-child::before {
                display: none;
            }

            .table-foot {
                flex-direction: column;
                align-items: flex-start;
                gap: 6px;
                padding: 10px 12px;
                font-size: 10px;
            }


            .mission-content,
            .vision-content {
                padding: 20px;
            }

            .mission-title,
            .vision-title {
                font-size: 1.2rem;
            }

            .mission-text,
            .vision-text {
                font-size: 0.88rem;
            }

            .empower-item {
                font-size: clamp(1.6rem, 7vw, 2.2rem);
            }

            .empower-description {
                font-size: 0.88rem;
            }

            .footer {
                margin-top: 0;
                padding-top: 16px;
                font-size: 0.78rem;
                gap: 14px;
                justify-content: center;
                text-align: center;
            }

            .footer-links {
                gap: 10px 14px;
            }
        }

        @media (max-width: 480px) {
            html, body {
                overflow-x: hidden;
                width: 100%;
                max-width: 100%;
            }

            .page-shell {
                overflow-x: hidden;
                width: 100%;
                max-width: 100%;
            }

            .shell-inner {
                padding-inline: 12px;
                padding-bottom: 32px;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }

            .nav {
                padding-inline: 10px;
                padding-block: 4px;
                gap: 8px;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }

            .logo-mark {
                width: 28px;
                height: 28px;
            }

            .logo-text-main {
                font-size: 14px;
            }

            .nav-cta {
                gap: 4px;
            }

            .btn {
                padding: 4px 8px;
                font-size: 9px;
            }

            #loginBtn {
                border-radius: 999px;
                padding: 3px 14px;
            }

            .btn-primary.btn-large {
                padding: 10px 18px;
                font-size: 13px;
            }

            .hero {
                margin-top: 24px;
                gap: 16px;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }

            .hero-left {
                gap: 16px;
                padding-left: 16px;
                padding-right: 16px;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }

            .hero-title {
                font-size: clamp(2.8rem, 12vw, 3.5rem);
                line-height: 1.3;
                letter-spacing: -0.01em;
                margin-top: 55px;
                margin-bottom: 4px;
                width: 100%;
                max-width: 100%;
                word-wrap: break-word;
                overflow-wrap: break-word;
                box-sizing: border-box;
            }

            .hero-sub {
                display: none;
            }

            .hero-right {
                display: none;
            }

            .hero-metrics {
                flex-direction: column;
                gap: 8px;
                margin-top: 4px;
            }

            .metric {
                width: 100%;
            }

            .hero-ctas {
                flex-direction: column;
                width: 100%;
                max-width: 100%;
                gap: 8px;
                margin-top: 10px;
                box-sizing: border-box;
            }

            .hero-ctas .btn {
                width: 100%;
                max-width: 100%;
                justify-content: center;
                box-sizing: border-box;
            }

            .eyebrow-row {
                gap: 6px;
            }

            .pill-highlight {
                font-size: 10px;
                padding: 4px 8px 4px 4px;
            }

            .pill-chip {
                font-size: 9px;
                padding: 2px 8px;
            }

            .section {
                margin-top: 35px;
            }

            .section-header {
                gap: 10px;
                margin-bottom: 12px;
            }

            .section-title {
                font-size: 0.95rem;
            }

            .section-subtitle {
                font-size: 0.82rem;
            }

            .why-grid {
                gap: 8px;
            }

            .why-card {
                padding: 10px;
            }

            .why-icon {
                width: 24px;
                height: 24px;
                font-size: 12px;
            }

            .why-title {
                font-size: 0.92rem;
            }

            .why-body {
                font-size: 0.82rem;
            }

            .table-shell {
                border-radius: 14px;
            }

            .table-tabs {
                padding: 8px 10px;
            }

            .tab {
                padding: 4px 8px;
                font-size: 10px;
            }

            table {
                min-width: 500px;
            }

            tbody tr {
                padding: 10px;
            }

            tbody td {
                padding: 3px 0;
                font-size: 0.78rem;
            }

            .pair {
                flex-wrap: wrap;
            }

            .pair-circle {
                width: 20px;
                height: 20px;
                font-size: 10px;
            }

            .pair-symbol {
                font-size: 0.85rem;
            }

            .pair-name {
                font-size: 10px;
            }

            .btn-mini {
                padding: 3px 8px;
                font-size: 10px;
            }


            .faq-btn {
                padding: 7px 8px;
                font-size: 0.78rem;
            }

            .faq-body {
                font-size: 0.76rem;
                padding: 0 10px;
            }

            .mission-section {
                margin-top: 100px;
            }

            .vision-section,
            .empower-section {
                margin-top: 24px;
            }

            .hero-content-section {
                display: none;
            }

            .mission-content,
            .vision-content {
                padding: 12px 14px;
            }

            .mission-title,
            .vision-title {
                font-size: 1.05rem;
                margin-bottom: 8px;
            }

            .mission-text,
            .vision-text {
                font-size: 0.82rem;
                line-height: 1.6;
            }

            .empower-content {
                gap: 12px;
            }

            .empower-item {
                font-size: clamp(1.4rem, 8vw, 1.8rem);
            }

            .empower-description {
                font-size: 0.82rem;
                margin-top: 8px;
            }

            .status-strip {
                flex-direction: column;
                align-items: flex-start;
                gap: 6px;
                padding: 8px;
                font-size: 10px;
            }

            .footer {
                margin-top: 0;
                padding-top: 12px;
                font-size: 0.76rem;
                flex-direction: column;
                gap: 10px;
                justify-content: center;
                text-align: center;
                align-items: center;
            }

            .footer-links {
                flex-direction: column;
                gap: 6px;
            }

            .footer-tag {
                font-size: 0.72rem;
                padding: 3px 8px;
            }

            .trust-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
                margin-top: 6px;
            }


            .table-shell {
                margin-top: 12px;
            }

            .avatars {
                margin-left: 0;
            }

            .hero-footnote {
                font-size: 10px;
            }
        }

        /* Touch-friendly improvements */
        @media (hover: none) and (pointer: coarse) {

            .btn,
            .tab,
            .faq-btn {
                min-height: 44px;
                min-width: 44px;
            }

            .nav-links a {
                min-height: 36px;
                padding: 10px 14px;
            }
        }

        /* Landscape mobile optimization */
        @media (max-width: 960px) and (orientation: landscape) {
            .hero {
                margin-top: 30px;
            }

            .hero-left {
                min-height: auto;
            }

            .hero-right {
                display: none;
            }
        }

        /* Login Modal */
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.85);
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding-top: 22vh;
            z-index: 100;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease-out;
        }

        .modal-backdrop.active {
            opacity: 1;
            pointer-events: auto;
        }

        .modal {
            width: 100%;
            max-width: 420px;
            border-radius: 22px;
            border: 1px solid rgba(148, 163, 184, 0.55);
            background: radial-gradient(circle at top left, rgba(34, 197, 94, 0.18), rgba(15, 23, 42, 0.98));
            box-shadow: 0 28px 80px rgba(0, 0, 0, 0.9);
            padding: 22px 22px 20px;
            transform: translateY(16px) scale(0.98);
            opacity: 0;
            transition: opacity 0.25s ease-out, transform 0.25s ease-out;
        }

        .modal-backdrop.active .modal {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 12px;
        }

        .modal-title {
            font-size: 1.05rem;
            font-weight: 600;
        }

        .modal-close-btn {
            width: 26px;
            height: 26px;
            border-radius: 999px;
            border: 1px solid rgba(75, 85, 99, 1);
            background: rgba(15, 23, 42, 0.9);
            display: grid;
            place-items: center;
            cursor: pointer;
            color: var(--text-soft);
            font-size: 14px;
        }

        .modal-body {
            font-size: 0.9rem;
        }

        .modal-description {
            font-size: 0.85rem;
            color: var(--text-soft);
            margin-bottom: 14px;
        }

        .form-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 12px;
        }

        .form-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            color: var(--text-soft);
        }

        .form-input {
            border-radius: 999px;
            border: 1px solid rgba(55, 65, 81, 0.9);
            background: rgba(15, 23, 42, 0.96);
            padding: 9px 12px;
            font-size: 0.88rem;
            color: var(--text-main);
            outline: none;
        }

        .form-input:focus {
            border-color: rgba(34, 197, 94, 0.8);
            box-shadow: 0 0 0 1px rgba(34, 197, 94, 0.5);
        }

        .form-input.invalid {
            border-color: #fb7185;
            box-shadow: 0 0 0 1px rgba(248, 113, 113, 0.7);
        }

        .form-error {
            font-size: 0.78rem;
            color: #fb7185;
            margin-top: 2px;
        }

        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 12px 16px;
            border-radius: var(--radius-md);
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }

        .success-message {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #86efac;
            padding: 12px 16px;
            border-radius: var(--radius-md);
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }

        .loading {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(2, 6, 23, 0.3);
            border-radius: 50%;
            border-top-color: #020617;
            animation: spin 0.8s linear infinite;
            margin-right: 8px;
            vertical-align: middle;
        }

        .modal-footer {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 8px;
        }

        .modal-footer-note {
            font-size: 0.78rem;
            color: var(--text-soft);
        }

        @media (max-width: 480px) {
            .modal {
                margin-inline: 14px;
                padding: 18px 16px 16px;
            }

            .modal-title {
                font-size: 0.98rem;
            }

            .modal-description {
                font-size: 0.8rem;
            }

            .form-input {
                padding: 8px 10px;
                font-size: 0.84rem;
            }
        }

        /* Back to Top Button */
        .back-to-top {
            position: fixed !important;
            bottom: 30px !important;
            right: 30px !important;
            left: auto !important;
            top: auto !important;
            width: 50px;
            height: 50px;
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(12px);
            color: var(--accent);
            border: 2px solid rgba(34, 197, 94, 0.7);
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: 600;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5), 0 0 20px rgba(34, 197, 94, 0.3);
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
            transition: opacity 0.3s ease, visibility 0.3s ease, transform 0.3s ease, background 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
            z-index: 99999 !important;
            margin: 0 !important;
            padding: 0 !important;
            pointer-events: auto;
        }

        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .back-to-top:hover {
            background: rgba(15, 23, 42, 0.95);
            border-color: var(--accent);
            border-width: 2.5px;
            color: var(--accent-strong);
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.6), 0 0 30px rgba(34, 197, 94, 0.5);
        }

        .back-to-top:active {
            transform: translateY(0);
        }

        @media (max-width: 768px) {
            .back-to-top {
                bottom: 30px !important;
                right: 10px !important;
                width: 45px;
                height: 45px;
                font-size: 20px;
                border-width: 2px;
            }
        }

        @media (max-width: 480px) {
            .back-to-top {
                bottom: 35px !important;
                right: 40px !important;
                width: 45px;
                height: 45px;
                font-size: 20px;
                border-width: 2px;
            }
        }
    </style>
</head>

<body>
    <div class="page-shell">
        <div class="blur-orb" aria-hidden="true"></div>
        <div class="floating-shapes" aria-hidden="true">
            <div class="floating-shape"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/javascript/javascript-original.svg" alt="JavaScript"></div>
            <div class="floating-shape"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/python/python-original.svg" alt="Python"></div>
            <div class="floating-shape"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/java/java-original.svg" alt="Java"></div>
            <div class="floating-shape"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/cplusplus/cplusplus-original.svg" alt="C++"></div>
            <div class="floating-shape"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/typescript/typescript-original.svg" alt="TypeScript"></div>
            <div class="floating-shape"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg" alt="PHP"></div>
            <div class="floating-shape"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/ruby/ruby-original.svg" alt="Ruby"></div>
            <div class="floating-shape"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/go/go-original.svg" alt="Go"></div>
            <div class="floating-shape"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/swift/swift-original.svg" alt="Swift"></div>
            <div class="floating-shape"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/kotlin/kotlin-original.svg" alt="Kotlin"></div>
            <div class="floating-shape"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/csharp/csharp-original.svg" alt="C#"></div>
            <div class="floating-shape"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/html5/html5-original.svg" alt="HTML"></div>
            <div class="floating-shape"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/css3/css3-original.svg" alt="CSS"></div>
            <div class="floating-shape"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mysql/mysql-original.svg" alt="SQL"></div>
            <div class="floating-shape"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/react/react-original.svg" alt="React"></div>
            <div class="floating-shape"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/vuejs/vuejs-original.svg" alt="Vue"></div>
            <div class="floating-shape"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/nodejs/nodejs-original.svg" alt="Node.js"></div>
        </div>

        <div class="shell-inner">
            <!-- NAVIGATION -->
            <header class="nav">
                <div class="nav-left">
                    <div class="logo-mark">
                        <img src="images/isss.png" alt="BSIS Logo">
                    </div>
                    <div class="logo-text-main">
                        <span class="letter">B</span>
                        <span class="letter">S</span>
                        <span class="letter">I</span>
                        <span class="letter">S</span>
                    </div>
                </div>

                <nav class="nav-links" aria-label="Primary">
                    <a href="#announcement">Announcement</a>
                    <a href="#projects">Projects</a>
                    <a href="#faculty">Faculty</a>
                    <a href="#partners">Partners</a>
                    <a href="#contact">Contact</a>
                </nav>

                <div class="nav-cta">
                    <button class="btn btn-ghost" type="button" id="searchBtn" aria-label="Search">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                    </button>
                    <button class="btn btn-ghost" type="button" id="loginBtn">Log in</button>
                </div>
            </header>

            <!-- HERO -->
            <main class="hero fade-section">
                <section class="hero-left" aria-label="Hero">
                    <div class="eyebrow-row"></div>

                    <h1 class="hero-title">
                        Bachelor of Science in <span class="accent">Information System</span>
                    </h1>

                    <p class="hero-sub">
                        The Bachelor of Science in Information System bridges technology and business by equipping students with the skills to design, manage, and innovate information systems. Our department focuses on data-driven solutions, system integration, and emerging technologies, preparing graduates to thrive in today’s dynamic digital environment.
                    </p>

                    <div class="hero-ctas">
                        <a href="#announcement" class="btn btn-primary btn-large">
                            Announcements
                        </a>
                    </div>

                    <div class="hero-footnote"></div>
                </section>

                <!-- Hero visual -->
                <aside class="hero-right" aria-label="Bot visual">
                    <div class="glass-orbit">
                        <div class="glass-grid" aria-hidden="true"></div>
                        <div class="hero-logo-large">
                            <img src="images/isss.png" alt="BSIS Logo">
                        </div>
                    </div>
                </aside>
            </main>

            <!-- ANNOUNCEMENT SECTION -->
            <div id="announcement" class="announcement-section fade-section" aria-label="Announcement">
                <div class="announcement-header">
                    <div class="announcement-icon">📢</div>
                    <div class="announcement-content">
                        <div class="announcement-title">Important Announcement</div>
                        <div class="announcement-text">Stay updated with the latest news and updates from the BSIS Department.</div>
                    </div>
                </div>
                <div class="announcement-display" style="align-items: flex-start; padding: 40px;">
                    <div class="announcement-scrollable" style="width: 100%; text-align: left;">
                        <div class="announcement-items-grid">
                        <!-- First Announcement -->
                        <ul class="announcement-item">
                            <li style="margin-bottom: 16px;">
                                <div style="font-size: 0.85rem; color: var(--text-soft); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; font-weight: 600;">What</div>
                                <div style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); line-height: 1.5;">BSIS Department Orientation Week</div>
                            </li>
                            <li style="margin-bottom: 16px;">
                                <div style="font-size: 0.85rem; color: var(--text-soft); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; font-weight: 600;">When</div>
                                <div style="font-size: 1rem; color: var(--text-main); line-height: 1.5;">January 15-19, 2024 | 8:00 AM - 5:00 PM</div>
                            </li>
                            <li style="margin-bottom: 16px;">
                                <div style="font-size: 0.85rem; color: var(--text-soft); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; font-weight: 600;">Where</div>
                                <div style="font-size: 1rem; color: var(--text-main); line-height: 1.5;">BSIS Department Building, Room 201 & Main Auditorium</div>
                            </li>
                        </ul>
                        
                        <!-- Second Announcement -->
                        <ul class="announcement-item">
                            <li style="margin-bottom: 16px;">
                                <div style="font-size: 0.85rem; color: var(--text-soft); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; font-weight: 600;">What</div>
                                <div style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); line-height: 1.5;">Capstone Project Proposal Submission</div>
                            </li>
                            <li style="margin-bottom: 16px;">
                                <div style="font-size: 0.85rem; color: var(--text-soft); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; font-weight: 600;">When</div>
                                <div style="font-size: 1rem; color: var(--text-main); line-height: 1.5;">February 28, 2024 | Before 5:00 PM</div>
                            </li>
                            <li style="margin-bottom: 16px;">
                                <div style="font-size: 0.85rem; color: var(--text-soft); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; font-weight: 600;">Where</div>
                                <div style="font-size: 1rem; color: var(--text-main); line-height: 1.5;">Student Portal (Online) or Department Office, Room 105</div>
                            </li>
                        </ul>
                        
                        <!-- Third Announcement -->
                        <ul class="announcement-item">
                            <li style="margin-bottom: 16px;">
                                <div style="font-size: 0.85rem; color: var(--text-soft); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; font-weight: 600;">What</div>
                                <div style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); line-height: 1.5;">Midterm Examination Schedule</div>
                            </li>
                            <li style="margin-bottom: 16px;">
                                <div style="font-size: 0.85rem; color: var(--text-soft); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; font-weight: 600;">When</div>
                                <div style="font-size: 1rem; color: var(--text-main); line-height: 1.5;">March 18-22, 2024 | 8:00 AM - 12:00 PM</div>
                            </li>
                            <li style="margin-bottom: 16px;">
                                <div style="font-size: 0.85rem; color: var(--text-soft); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; font-weight: 600;">Where</div>
                                <div style="font-size: 1rem; color: var(--text-main); line-height: 1.5;">Assigned Classrooms - Check your schedule</div>
                            </li>
                        </ul>
                        
                        <!-- Fourth Announcement -->
                        <ul class="announcement-item">
                            <li style="margin-bottom: 16px;">
                                <div style="font-size: 0.85rem; color: var(--text-soft); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; font-weight: 600;">What</div>
                                <div style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); line-height: 1.5;">IT Career Fair 2024</div>
                            </li>
                            <li style="margin-bottom: 16px;">
                                <div style="font-size: 0.85rem; color: var(--text-soft); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; font-weight: 600;">When</div>
                                <div style="font-size: 1rem; color: var(--text-main); line-height: 1.5;">April 10, 2024 | 9:00 AM - 4:00 PM</div>
                            </li>
                            <li style="margin-bottom: 16px;">
                                <div style="font-size: 0.85rem; color: var(--text-soft); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; font-weight: 600;">Where</div>
                                <div style="font-size: 1rem; color: var(--text-main); line-height: 1.5;">College Gymnasium & Main Lobby</div>
                            </li>
                        </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MISSION SECTION -->
            <section class="mission-section" aria-label="Mission">
                <div class="mission-content">
                    <h2 class="mission-title">MISSION</h2>
                    <p class="mission-text">
                        Bachelor of Science in Information System Department of Bago City COLLEGE aims to Provide Relevant, Accessible, High Quality and Efficient Computer Education to Graduates thus aligned with Industry need of the Local Community and to Upgrade Filipinos higher level manpower in line with the National Development Goals and Priorities
                    </p>
                </div>
            </section>

            <!-- VISION SECTION -->
            <section class="vision-section" aria-label="Vision">
                <div class="vision-content">
                    <h2 class="vision-title">VISION</h2>
                    <p class="vision-text">
                        Bachelor of Science in Information System Department of Bago City COLLEGE aims to Provide Relevant, Accessible, High Quality and Efficient Computer Education to Graduates thus aligned with Industry need of the Local Community and to Upgrade Filipinos higher level manpower in line with the National Development Goals and Priorities
                    </p>
                </div>
            </section>

            <!-- CORE VALUES SECTION -->
            <section class="empower-section" id="coreValuesSection" aria-label="Core Values">
                <div class="empower-content">
                    <h2 class="core-values-title">Core Values</h2>
                    <h2 class="empower-item">Dependability</h2>
                    <h2 class="empower-item">Humility</h2>
                    <h2 class="empower-item">Commitment</h2>
                    <h2 class="empower-item">Optimism</h2>
                    <h2 class="empower-item">Respect</h2>
                </div>
            </section>

            <!-- CULTURE SECTION -->
            <section class="empower-section" id="cultureSection" aria-label="Culture">
                <div class="empower-content">
                    <h2 class="core-values-title">CULTURE</h2>
                    <h2 class="empower-item">Godliness</h2>
                    <h2 class="empower-item">Family Oriented</h2>
                    <h2 class="empower-item">Resilience</h2>
                </div>
            </section>

            <!-- HERO CONTENT SECTION -->
            <section class="hero-content-section" aria-label="Hero Content">
                <h1 class="hero-title">
                    Bachelor of Science in <span class="accent">Information System</span>
                </h1>

                <p class="hero-sub">
                    The Bachelor of Science in Information System bridges technology and business by equipping students with the skills to design, manage, and innovate information systems. Our department focuses on data-driven solutions, system integration, and emerging technologies, preparing graduates to thrive in today's dynamic digital environment.
                </p>

                <div class="hero-ctas">
                    <a href="#announcement" class="btn btn-primary btn-large">
                        Announcements
                    </a>
                </div>
            </section>

            <!-- WHY SECTION -->
            <section id="why" class="section fade-section" aria-labelledby="why-heading">
                <div class="section-header">
                    <div>
                        <div id="why-heading" class="section-title scroll-reveal-fade"></div>
                    </div>
                    <p class="section-subtitle scroll-reveal-fade"></p>
                </div>


            </section>

            <!-- PROJECTS SECTION -->
            <section id="projects" class="projects-section fade-section" aria-labelledby="projects-heading">
                <div class="projects-header">
                    <div>
                        <h2 id="projects-heading" class="projects-title scroll-reveal-fade">Projects</h2>
                    </div>
                </div>
                <div class="projects-carousel-wrapper">
                    <div class="projects-carousel" id="projectsCarousel">
                        <div class="projects-carousel-track" id="projectsCarouselTrack">
<?php
require_once __DIR__ . '/functions/db/database.php';

$projects = [];
try {
    $pdo = getPDO();
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'projects'");
    if ($tableCheck->rowCount() > 0) {
        $stmt = $pdo->prepare("SELECT project_id, student_id, title, slug, short_description, description, category, technologies, figma_url, live_demo_url, github_url, thumbnail, banner_image, is_featured, is_published, created_at, updated_at FROM projects WHERE is_published = 1 ORDER BY is_featured DESC, created_at DESC LIMIT 12");
        $stmt->execute();
        $projects = $stmt->fetchAll();
    }
} catch (Exception $e) {
    $projects = [];
}

if (!empty($projects)):
    $delay = 1;
    foreach ($projects as $p):
        $title = htmlspecialchars($p['title'] ?? 'Untitled Project');
        $desc = htmlspecialchars($p['short_description'] ?? ($p['description'] ?? ''));
        $thumb = $p['thumbnail'] ?? '';
        $thumbPath = '';
        if (!empty($thumb) && file_exists(__DIR__ . '/student/uploads/' . $thumb)) {
            $thumbPath = 'student/uploads/' . $thumb;
        }
        $encodedTitle = rawurlencode($title);
?>
                            <article class="project-card scroll-reveal scroll-reveal-delay-<?php echo $delay; ?>">
                                <?php if ($thumbPath): ?>
                                    <img src="<?php echo htmlspecialchars($thumbPath); ?>" alt="<?php echo $title; ?>" class="project-image">
                                <?php else: ?>
                                    <img src="images/bsis_logo.png" alt="<?php echo $title; ?>" class="project-image" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22200%22%3E%3Crect fill=%22%231f2937%22 width=%22400%22 height=%22200%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%229ca3af%22 font-family=%22sans-serif%22 font-size=%2216%22%3E<?php echo $encodedTitle; ?>%3C/text%3E%3C/svg%3E';">
                                <?php endif; ?>
                                <div class="project-content">
                                    <div class="project-title"><?php echo $title; ?></div>
                                    <div class="project-description"><?php echo $desc; ?></div>
                                </div>
                            </article>
<?php
        $delay = $delay % 4 + 1;
    endforeach;
else:
?>
                            <article class="project-card">
                                <div class="project-content">
                                    <div class="project-title">No projects yet</div>
                                    <div class="project-description">Projects will appear here when published.</div>
                                </div>
                            </article>
<?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="projects-carousel-controls">
                    <button class="carousel-btn" id="carouselPrev" aria-label="Previous project">‹</button>
                    <div class="carousel-dots" id="carouselDots"></div>
                    <button class="carousel-btn" id="carouselNext" aria-label="Next project">›</button>
                </div>
            </section>

            <!-- FACULTY SECTION -->
            <section id="faculty" class="faculty-section fade-section" aria-labelledby="faculty-heading">
                <div class="faculty-header">
                    <div>
                        <h2 id="faculty-heading" class="faculty-title scroll-reveal-fade">OUR <span>Faculty</span></h2>
                    </div>
                </div>

                <div class="faculty-grid">
                    <article class="faculty-card scroll-reveal scroll-reveal-delay-1">
                        <img src="images/sirM.png" alt="Mr. Anthony Malabanan" class="faculty-image" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect fill=%22%231f2937%22 width=%22200%22 height=%22200%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%229ca3af%22 font-family=%22sans-serif%22 font-size=%2214%22%3EFaculty%3C/text%3E%3C/svg%3E';">
                        <div class="faculty-name">Mr. Anthony Malabanan</div>
                        <div class="faculty-position">Program Head</div>
                    </article>

                    <article class="faculty-card scroll-reveal scroll-reveal-delay-2">
                        <img src="images/gerard.jpg" alt="Faculty Member" class="faculty-image" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect fill=%22%231f2937%22 width=%22200%22 height=%22200%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%229ca3af%22 font-family=%22sans-serif%22 font-size=%2214%22%3EFaculty%3C/text%3E%3C/svg%3E';">
                        <div class="faculty-name">Mr. Gerard H. Pacete</div>
                        <div class="faculty-position">Instructor</div>
                    </article>

                    <article class="faculty-card scroll-reveal scroll-reveal-delay-3">
                        <img src="images/sirPat.jpg" alt="Faculty Member" class="faculty-image" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect fill=%22%231f2937%22 width=%22200%22 height=%22200%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%229ca3af%22 font-family=%22sans-serif%22 font-size=%2214%22%3EFaculty%3C/text%3E%3C/svg%3E';">
                        <div class="faculty-name">Mr. Patrick Solis</div>
                        <div class="faculty-position">Instructor</div>
                    </article>

                    <article class="faculty-card scroll-reveal scroll-reveal-delay-4">
                        <img src="images/Khycy.jpg" alt="Faculty Member" class="faculty-image" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect fill=%22%231f2937%22 width=%22200%22 height=%22200%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%229ca3af%22 font-family=%22sans-serif%22 font-size=%2214%22%3EFaculty%3C/text%3E%3C/svg%3E';">
                        <div class="faculty-name">Ms. Khycy Alvarez</div>
                        <div class="faculty-position">Instructor</div>
                    </article>

                    <article class="faculty-card scroll-reveal scroll-reveal-delay-4">
                        <img src="images/lea.jpg" alt="Faculty Member" class="faculty-image" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect fill=%22%231f2937%22 width=%22200%22 height=%22200%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%229ca3af%22 font-family=%22sans-serif%22 font-size=%2214%22%3EFaculty%3C/text%3E%3C/svg%3E';">
                        <div class="faculty-name">Ms. Lea Pangcobela</div>
                        <div class="faculty-position">Instructor</div>
                    </article>

                    <article class="faculty-card scroll-reveal scroll-reveal-delay-4">
                        <img src="images/joshua.png" alt="Faculty Member" class="faculty-image" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect fill=%22%231f2937%22 width=%22200%22 height=%22200%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%229ca3af%22 font-family=%22sans-serif%22 font-size=%2214%22%3EFaculty%3C/text%3E%3C/svg%3E';">
                        <div class="faculty-name">Mr. Joshua Alexes Dignadice</div>
                        <div class="faculty-position">Instructor</div>
                    </article>

                    <article class="faculty-card scroll-reveal scroll-reveal-delay-4">
                        <img src="images/sirA.jpg" alt="Faculty Member" class="faculty-image" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect fill=%22%231f2937%22 width=%22200%22 height=%22200%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%229ca3af%22 font-family=%22sans-serif%22 font-size=%2214%22%3EFaculty%3C/text%3E%3C/svg%3E';">
                        <div class="faculty-name">Mr. Albert Alvarez</div>
                        <div class="faculty-position">Instructor</div>
                    </article>

                    <article class="faculty-card scroll-reveal scroll-reveal-delay-4">
                        <img src="images/togle.jpg" alt="Faculty Member" class="faculty-image" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect fill=%22%231f2937%22 width=%22200%22 height=%22200%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%229ca3af%22 font-family=%22sans-serif%22 font-size=%2214%22%3EFaculty%3C/text%3E%3C/svg%3E';">
                        <div class="faculty-name">Ms. Gerlie Togle</div>
                        <div class="faculty-position">Instructor</div>
                    </article>

                    <article class="faculty-card scroll-reveal scroll-reveal-delay-4">
                        <img src="images/meets.jpg" alt="Faculty Member" class="faculty-image" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect fill=%22%231f2937%22 width=%22200%22 height=%22200%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%229ca3af%22 font-family=%22sans-serif%22 font-size=%2214%22%3EFaculty%3C/text%3E%3C/svg%3E';">
                        <div class="faculty-name">Mr. Edzel Villar</div>
                        <div class="faculty-position">Instructor</div>
                    </article>
                </div>
            </section>

            <!-- PARTNERS SECTION -->
            <section id="partners" class="partners-section fade-section" aria-labelledby="partners-heading">
                <div class="partners-header">
                    <div>
                        <h2 id="partners-heading" class="partners-title scroll-reveal-fade">OUR <span>Partners</span></h2>
                    </div>
                </div>

                <div class="partners-content scroll-reveal">
                    <div class="partners-logos-wrapper">
                        <div class="partners-logos" id="partnersLogos">
                            <!-- Logos will be generated by JavaScript -->
                        </div>
                    </div>

                    <div class="partners-carousel-controls">
                    </div>

                    <h3 class="partners-name" id="partnersName">Negros Occidental Language and Information Technology Center</h3>

                    <p class="partners-description" id="partnersDescription">
                        NOLITC is a premier training institution in the Philippines that focuses on providing top-quality education and skills development in language and information technology.
                    </p>
                </div>
            </section>

            <!-- CONTACT SECTION -->
            <section id="contact" class="contact-section fade-section" aria-labelledby="contact-heading">
                <hr style="width: 100%; height: 1px; background: linear-gradient(90deg, transparent, var(--border-subtle), transparent); margin: 0 0 30px 0; border: none; display: block;">
                <div class="contact-header">
                    <div>
                        <h2 id="contact-heading" class="contact-title scroll-reveal-fade">Contact <span>Us</span></h2>
                    </div>
                    <div></div>
                    <div>
                        <h2 class="contact-title1 scroll-reveal-fade">Address</h2>
                    </div>
                </div>
                <div class="contact-grid">
                    <div class="contact-column">
                        <div class="contact-info-item">
                            <span class="contact-icon">📞</span>
                            <span>(034) 461 1038 / (034) 461 0963</span>
                        </div>
                        <div class="contact-info-item">
                            <span class="contact-icon">📱</span>
                            <span>0915 712 5272 / 0921 2753 029</span>
                        </div>
                        <div class="contact-info-item">
                            <span class="contact-icon">✉️</span>
                            <span>bagocitycollege@gmail.com</span>
                        </div>
                    </div>
                    <div class="contact-column contact-column-center">
                        <div class="contact-info-item">
                            <span class="contact-icon">📍</span>
                            <div>
                                <div>Rafael Salas Drive</div>
                                <div>Bago City 6101 Neg. Occ.</div>
                                <div>Philippines</div>
                            </div>
                        </div>
                    </div>
                    <div class="contact-column contact-map-column">
                        <div class="contact-map">
                            <iframe
                                src="https://www.openstreetmap.org/export/embed.html?bbox=122.8385%2C10.5259%2C122.8485%2C10.5359&layer=mapnik&marker=10.5309%2C122.8435"
                                width="100%"
                                height="100%"
                                style="border: 1px solid rgba(148, 163, 184, 0.3); border-radius: var(--radius-md);"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                    </div>
                </div>
            </section>

            <!-- FOOTER -->
            <footer class="footer fade-section scroll-reveal-fade">
                <div>
                    © <?php echo date('Y'); ?> Copyright BAGO CITY COLLEGE BSIS. All rights reserved.
                </div>
            </footer>
        </div>
    </div>

    <!-- LOGIN MODAL -->
    <div class="modal-backdrop" id="loginModal" role="dialog" aria-modal="true" aria-labelledby="login-modal-title" aria-hidden="true">
        <div class="modal" role="document">
            <div class="modal-header">
                <h2 class="modal-title" id="login-modal-title">Log in to BSIS Portal</h2>
                <button type="button" class="modal-close-btn" id="loginModalClose" aria-label="Close login modal">×</button>
            </div>
            <div class="modal-body">
                <p class="modal-description">
                    Enter your credentials to continue. This can later be connected to your real authentication system.
                </p>
                <div class="error-message" id="errorMessage" style="display: none; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #fca5a5; padding: 12px 16px; border-radius: var(--radius-md); margin-bottom: 20px; font-size: 14px;"></div>
                <div class="success-message" id="successMessage" style="display: none; background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.3); color: #86efac; padding: 12px 16px; border-radius: var(--radius-md); margin-bottom: 20px; font-size: 14px;"></div>
                <form id="loginForm" autocomplete="off">
                    <div class="form-field">
                        <label class="form-label" for="login-username">Username</label>
                        <input class="form-input" type="text" id="login-username" name="username" placeholder="e.g. student001" required autocomplete="username">
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="login-password">Password</label>
                        <input class="form-input" type="password" id="login-password" name="password" placeholder="Enter your password" required autocomplete="current-password">
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary btn-large" type="submit" id="loginSubmitBtn" style="width: 100%; justify-content: center;">
                            Log in
                        </button>
                        <div class="modal-footer-note">
                            By logging in you agree to the department's acceptable use policy.
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- SIGNUP MODAL -->
    <div class="modal-backdrop" id="signupModal" role="dialog" aria-modal="true" aria-labelledby="signup-modal-title" aria-hidden="true">
        <div class="modal" role="document">
            <div class="modal-header">
                <h2 class="modal-title" id="signup-modal-title">Create your BSIS account</h2>
                <button type="button" class="modal-close-btn" id="signupModalClose" aria-label="Close signup modal">×</button>
            </div>
            <div class="modal-body">
                <p class="modal-description">
                    Fill in the details below to request an account. This form can later be wired to your real registration backend.
                </p>
                <form id="signupForm" action="functions/registration/register.php" method="post" autocomplete="off">
                    <div class="form-field">
                        <label class="form-label" for="signup-fullname">Full name</label>
                        <input class="form-input" type="text" id="signup-fullname" name="fullname" placeholder="e.g. Juan Dela Cruz" required>
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="signup-email">Email</label>
                        <input class="form-input" type="email" id="signup-email" name="email" placeholder="e.g. juan@example.com" required>
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="signup-username">Username</label>
                        <input class="form-input" type="text" id="signup-username" name="username" placeholder="Choose a username" required>
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="signup-password">Password</label>
                        <input class="form-input" type="password" id="signup-password" name="password" placeholder="Create a password" required>
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="signup-confirm-password">Confirm password</label>
                        <input class="form-input" type="password" id="signup-confirm-password" name="confirm_password" placeholder="Re‑enter password" required>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary btn-large" type="submit" style="width: 100%; justify-content: center;">
                            Sign up
                        </button>
                        <div class="modal-footer-note">
                            Submitting this form does not yet create a real account until the registration backend is connected.
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Prevent scroll restoration and keep page at top on reload
        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }
        window.scrollTo(0, 0);

        // Ensure page stays at top on load/reload
        window.addEventListener('load', function() {
            window.scrollTo(0, 0);
        });

        window.addEventListener('beforeunload', function() {
            window.scrollTo(0, 0);
        });

        // Animate browser tab title letter by letter
        (function() {
            const titleText = 'BSIS';
            const titleElement = document.querySelector('title');
            let currentIndex = 0;

            function animateTitle() {
                if (currentIndex < titleText.length) {
                    titleElement.textContent = titleText.substring(0, currentIndex + 1);
                    currentIndex++;
                    setTimeout(animateTitle, 200); // 200ms delay between each letter
                }
            }

            // Start animation when page loads
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', animateTitle);
            } else {
                animateTitle();
            }
        })();

        // Simple FAQ accordion interaction + smooth section reveals
        document.addEventListener('DOMContentLoaded', function() {
            const faqItems = document.querySelectorAll('.faq-item');

            faqItems.forEach((item) => {
                const btn = item.querySelector('.faq-btn');
                const icon = item.querySelector('.icon');

                btn.addEventListener('click', () => {
                    const isActive = item.classList.contains('active');

                    faqItems.forEach((other) => {
                        other.classList.remove('active');
                        const otherIcon = other.querySelector('.icon');
                        if (otherIcon) otherIcon.textContent = '+';
                    });

                    if (!isActive) {
                        item.classList.add('active');
                        icon.textContent = '−';
                    } else {
                        item.classList.remove('active');
                        icon.textContent = '+';
                    }
                });
            });

            // Mark page as ready for initial fade
            document.body.classList.add('page-ready');

            // Ensure page stays at top
            window.scrollTo(0, 0);

            // IntersectionObserver for smooth scroll-in sections
            const sections = document.querySelectorAll('.fade-section');
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver(
                    (entries) => {
                        entries.forEach((entry) => {
                            if (entry.isIntersecting) {
                                entry.target.classList.add('in-view');
                                observer.unobserve(entry.target);
                            }
                        });
                    }, {
                        threshold: 0.18
                    }
                );

                sections.forEach((section) => observer.observe(section));
            } else {
                // Fallback: show all sections if IntersectionObserver is not supported
                sections.forEach((section) => section.classList.add('in-view'));
            }

            // Scroll reveal animations using Intersection Observer
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const scrollRevealObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('revealed');
                        // Unobserve after animation to improve performance
                        scrollRevealObserver.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            // Observe all elements with scroll-reveal classes
            const scrollRevealElements = document.querySelectorAll(
                '.scroll-reveal, .scroll-reveal-left, .scroll-reveal-right, .scroll-reveal-scale, .scroll-reveal-fade'
            );

            scrollRevealElements.forEach(el => {
                scrollRevealObserver.observe(el);
            });

            // Projects Carousel
            const carousel = document.getElementById('projectsCarousel');
            const track = document.getElementById('projectsCarouselTrack');
            const prevBtn = document.getElementById('carouselPrev');
            const nextBtn = document.getElementById('carouselNext');
            const dotsContainer = document.getElementById('carouselDots');

            if (carousel && track && prevBtn && nextBtn && dotsContainer) {
                const cards = track.querySelectorAll('.project-card');
                let currentIndex = 0;
                let cardsPerView = 3;
                let autoAdvanceInterval = null;
                const autoAdvanceDelay = 4000; // 4 seconds between slides

                // Calculate cards per view based on screen size
                function updateCardsPerView() {
                    const width = window.innerWidth;
                    if (width <= 480) {
                        cardsPerView = 1;
                    } else if (width <= 720) {
                        cardsPerView = 2;
                    } else {
                        cardsPerView = 3;
                    }
                }

                // Get card width including gap
                function getCardWidth() {
                    if (cards.length === 0) return 0;
                    const firstCard = cards[0];
                    if (!firstCard) return 0;
                    const cardStyle = window.getComputedStyle(firstCard);
                    const cardWidth = firstCard.offsetWidth;
                    const gap = parseInt(window.getComputedStyle(track).gap) || 24;
                    return cardWidth + gap;
                }

                // Initialize carousel
                function initCarousel() {
                    updateCardsPerView();
                    if (cards.length === 0) {
                        prevBtn.style.display = 'none';
                        nextBtn.style.display = 'none';
                        dotsContainer.style.display = 'none';
                        return;
                    }
                    updateCarousel();
                    createDots();
                    startAutoAdvance();
                }

                // Update carousel position
                function updateCarousel() {
                    if (cards.length === 0) return;
                    const maxIndex = Math.max(0, cards.length - cardsPerView);
                    currentIndex = Math.min(currentIndex, maxIndex);
                    currentIndex = Math.max(0, currentIndex);

                    const cardWidth = getCardWidth();
                    const translateX = -currentIndex * cardWidth;
                    // Use translate3d for better hardware acceleration
                    track.style.transform = `translate3d(${translateX}px, 0, 0)`;

                    // Update button states
                    prevBtn.disabled = currentIndex === 0;
                    nextBtn.disabled = currentIndex >= maxIndex;

                    // Update dots
                    updateDots();
                }

                // Create dots
                function createDots() {
                    dotsContainer.innerHTML = '';
                    if (cards.length === 0) return;
                    const totalSlides = Math.max(1, Math.ceil(cards.length / cardsPerView));
                    
                    for (let i = 0; i < totalSlides; i++) {
                        const dot = document.createElement('button');
                        dot.className = 'carousel-dot';
                        if (i === 0) dot.classList.add('active');
                        dot.setAttribute('aria-label', `Go to slide ${i + 1}`);
                        dot.addEventListener('click', () => {
                            stopAutoAdvance();
                            currentIndex = i * cardsPerView;
                            updateCarousel();
                            setTimeout(() => startAutoAdvance(), autoAdvanceDelay);
                        });
                        dotsContainer.appendChild(dot);
                    }
                }

                // Update dots active state
                function updateDots() {
                    const dots = dotsContainer.querySelectorAll('.carousel-dot');
                    const activeDotIndex = Math.floor(currentIndex / cardsPerView);
                    dots.forEach((dot, index) => {
                        dot.classList.toggle('active', index === activeDotIndex);
                    });
                }

                // Auto-advance functionality
                function startAutoAdvance() {
                    stopAutoAdvance(); // Clear any existing interval
                    autoAdvanceInterval = setInterval(() => {
                        const maxIndex = Math.max(0, cards.length - cardsPerView);
                        if (currentIndex < maxIndex) {
                            currentIndex = Math.min(maxIndex, currentIndex + cardsPerView);
                        } else {
                            // Loop back to the beginning
                            currentIndex = 0;
                        }
                        updateCarousel();
                    }, autoAdvanceDelay);
                }

                function stopAutoAdvance() {
                    if (autoAdvanceInterval) {
                        clearInterval(autoAdvanceInterval);
                        autoAdvanceInterval = null;
                    }
                }

                // Navigation handlers
                prevBtn.addEventListener('click', () => {
                    stopAutoAdvance();
                    if (currentIndex > 0) {
                        currentIndex = Math.max(0, currentIndex - cardsPerView);
                        updateCarousel();
                    }
                    setTimeout(() => startAutoAdvance(), autoAdvanceDelay);
                });

                nextBtn.addEventListener('click', () => {
                    stopAutoAdvance();
                    const maxIndex = Math.max(0, cards.length - cardsPerView);
                    if (currentIndex < maxIndex) {
                        currentIndex = Math.min(maxIndex, currentIndex + cardsPerView);
                        updateCarousel();
                    }
                    setTimeout(() => startAutoAdvance(), autoAdvanceDelay);
                });

                // Handle window resize
                let resizeTimeout;
                window.addEventListener('resize', () => {
                    clearTimeout(resizeTimeout);
                    resizeTimeout = setTimeout(() => {
                        updateCardsPerView();
                        updateCarousel();
                        createDots();
                    }, 250);
                });

                // Pause auto-advance on hover
                carousel.addEventListener('mouseenter', () => {
                    stopAutoAdvance();
                });

                carousel.addEventListener('mouseleave', () => {
                    startAutoAdvance();
                });

                // Initialize on load
                setTimeout(() => {
                    initCarousel();
                }, 100);
            }

            // Partners Carousel - Rotating Circles
            const partnersLogosContainer = document.getElementById('partnersLogos');
            const partnersNameEl = document.getElementById('partnersName');
            const partnersDescEl = document.getElementById('partnersDescription');

            if (partnersLogosContainer && partnersNameEl && partnersDescEl) {
                // Define all partners with their logos and info
                const partners = [
                    {
                        src: 'images/coders_guild.png',
                        alt: 'Coders Guild',
                        name: 'Coders Guild',
                        description: 'Coders Guild partners with Bago City College BSIS Department to provide students with advanced training in Information Systems, equipping them with essential skills for the tech industry.'
                    },
                    {
                        src: 'images/nolitc.png',
                        alt: 'Negros Occidental Language & Information Technology Center',
                        name: 'Negros Occidental Language and Information Technology Center',
                        description: 'NOLITC is a premier training institution in the Philippines that focuses on providing top-quality education and skills development in language and information technology.'
                    },
                    {
                        src: 'images/technopal_logo.jpeg',
                        alt: 'TechnoPal',
                        name: 'TechnoPal',
                        description: 'TechnoPal is a valued partner of Bago City College\'s BSIS Department, dedicated to enhancing the educational experience by providing innovative technology solutions and hands-on training opportunities.'
                    },
                    {
                        src: 'images/city_logo.png',
                        alt: 'Bago City',
                        name: 'Bago City',
                        description: 'Bago City is home to Bago City College, where the Bachelor of Science in Information System program prepares students for dynamic careers in the technology sector.'
                    }
                    // Add more partners here - they will automatically rotate
                ];

                let currentMiddleIndex = 1; // Start with middle logo (NOLITC)
                let autoAdvanceInterval = null;
                const autoAdvanceDelay = 2500; // 2.5 seconds

                // Get indices for far-left, left, middle, and right positions
                function getLogoIndices() {
                    const farLeftIndex = (currentMiddleIndex - 2 + partners.length) % partners.length;
                    const leftIndex = (currentMiddleIndex - 1 + partners.length) % partners.length;
                    const middleIndex = currentMiddleIndex;
                    const rightIndex = (currentMiddleIndex + 1) % partners.length;
                    return { farLeftIndex, leftIndex, middleIndex, rightIndex };
                }

                // Create logo images
                function createLogos() {
                    partnersLogosContainer.innerHTML = '';
                    const { farLeftIndex, leftIndex, middleIndex, rightIndex } = getLogoIndices();
                    const positions = [
                        { index: farLeftIndex, className: 'partners-logo partners-logo-far-left', position: 'far-left' },
                        { index: leftIndex, className: 'partners-logo partners-logo-left', position: 'left' },
                        { index: middleIndex, className: 'partners-logo partners-logo-middle', position: 'middle' },
                        { index: rightIndex, className: 'partners-logo partners-logo-right', position: 'right' }
                    ];

                    positions.forEach(({ index, className, position }) => {
                        const img = document.createElement('img');
                        img.src = partners[index].src;
                        img.alt = partners[index].alt;
                        img.className = className;
                        img.style.cursor = 'pointer';
                        img.setAttribute('data-partner-index', index);
                        img.setAttribute('data-position', position);
                        
                        // Add click handler to make circles clickable
                        img.addEventListener('click', function() {
                            const clickedIndex = parseInt(this.getAttribute('data-partner-index'));
                            if (clickedIndex !== currentMiddleIndex) {
                                stopAutoAdvance();
                                // Rotate step by step to reach target
                                const rotateToTarget = (targetIndex) => {
                                    if (currentMiddleIndex === targetIndex) {
                                        updatePartnerDisplay();
                                        setTimeout(() => startAutoAdvance(), autoAdvanceDelay);
                                        return;
                                    }
                                    const direction = (targetIndex - currentMiddleIndex + partners.length) % partners.length <= partners.length / 2 ? 'next' : 'prev';
                                    rotateLogos(direction);
                                    setTimeout(() => rotateToTarget(targetIndex), 900);
                                };
                                rotateToTarget(clickedIndex);
                            }
                        });
                        
                        img.onerror = function() {
                            this.src = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22120%22 height=%22120%22%3E%3Ccircle fill=%22%231f2937%22 cx=%2260%22 cy=%2260%22 r=%2260%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%229ca3af%22 font-family=%22sans-serif%22 font-size=%2212%22%3EPartner%3C/text%3E%3C/svg%3E';
                        };
                        partnersLogosContainer.appendChild(img);
                    });
                }

                // Update partner display (name and description) with smooth fade effect
                function updatePartnerDisplay() {
                    const { middleIndex } = getLogoIndices();
                    const currentPartner = partners[middleIndex];
                    
                    // Fade out current content
                    partnersNameEl.classList.remove('fade-in');
                    partnersNameEl.classList.add('fade-out');
                    partnersDescEl.classList.remove('fade-in');
                    partnersDescEl.classList.add('fade-out');
                    
                    // Update content after fade out
                    setTimeout(() => {
                        partnersNameEl.textContent = currentPartner.name;
                        partnersDescEl.textContent = currentPartner.description;
                        
                        // Fade in new content
                        partnersNameEl.classList.remove('fade-out');
                        partnersNameEl.classList.add('fade-in');
                        partnersDescEl.classList.remove('fade-out');
                        partnersDescEl.classList.add('fade-in');
                    }, 300);
                }

                // Rotate logos with smooth animation
                function rotateLogos(direction) {
                    const logos = partnersLogosContainer.querySelectorAll('.partners-logo');
                    if (logos.length !== 4) {
                        // If logos don't exist, create them
                        if (direction === 'next') {
                            currentMiddleIndex = (currentMiddleIndex + 1) % partners.length;
                        } else {
                            currentMiddleIndex = (currentMiddleIndex - 1 + partners.length) % partners.length;
                        }
                        createLogos();
                        updatePartnerDisplay();
                        return;
                    }

                    // Add rotating class to prevent interaction during animation
                    logos.forEach(logo => logo.classList.add('rotating'));

                    // Update the index
                    const isMobile = window.innerWidth <= 720;
                    const translateDistance = isMobile ? (window.innerWidth <= 480 ? 100 : 120) : 140;
                    const farDistance = isMobile ? (window.innerWidth <= 480 ? 150 : 180) : 210;
                    
                    if (direction === 'next') {
                        currentMiddleIndex = (currentMiddleIndex + 1) % partners.length;
                        
                        // Animate: right logo moves to middle, middle moves to left, left moves to far-left (hidden)
                        logos[3].style.transform = 'translateX(0) scale(1)';
                        logos[3].style.zIndex = '3';
                        logos[3].style.opacity = '1';
                        
                        logos[2].style.transform = `translateX(-${translateDistance}px) scale(0.75)`;
                        logos[2].style.zIndex = '2';
                        logos[2].style.opacity = '0.8';
                        
                        // Hide the circle that's moving to the left/far-left position
                        logos[1].style.transform = `translateX(-${farDistance}px) scale(0.85)`;
                        logos[1].style.zIndex = '0';
                        logos[1].style.opacity = '0';
                        logos[1].style.pointerEvents = 'none';
                        
                        // Hide the far-left circle
                        logos[0].style.opacity = '0';
                        logos[0].style.zIndex = '0';
                        logos[0].style.pointerEvents = 'none';
                    } else {
                        currentMiddleIndex = (currentMiddleIndex - 1 + partners.length) % partners.length;
                        
                        // Animate: left logo moves to middle, middle moves to right, right moves to far-right and fades out
                        logos[1].style.transform = 'translateX(0) scale(1)';
                        logos[1].style.zIndex = '3';
                        logos[1].style.opacity = '1';
                        
                        logos[2].style.transform = `translateX(${translateDistance}px) scale(0.75)`;
                        logos[2].style.zIndex = '2';
                        logos[2].style.opacity = '0.8';
                        
                        // Right logo slides further right first (wrapping around)
                        logos[3].style.transform = `translateX(${farDistance}px) scale(0.85)`;
                        logos[3].style.zIndex = '1';
                        logos[3].style.opacity = '0.7';
                        
                        // Then fade it out after reaching the position
                        setTimeout(() => {
                            if (logos[3]) {
                                logos[3].style.opacity = '0';
                                logos[3].style.zIndex = '0';
                                logos[3].style.pointerEvents = 'none';
                            }
                        }, 400);
                        
                        // Far-left logo stays hidden
                        logos[0].style.transform = `translateX(-${farDistance}px) scale(0.85)`;
                        logos[0].style.zIndex = '0';
                        logos[0].style.opacity = '0';
                        logos[0].style.pointerEvents = 'none';
                    }

                    // After animation completes, recreate logos in new positions
                    setTimeout(() => {
                        createLogos();
                        updatePartnerDisplay();
                    }, 800);
                }


                // Auto-advance functionality
                function startAutoAdvance() {
                    stopAutoAdvance();
                    if (partners.length <= 1) return;
                    autoAdvanceInterval = setInterval(() => {
                        rotateLogos('next');
                    }, autoAdvanceDelay + 800); // Add animation duration to delay
                }

                function stopAutoAdvance() {
                    if (autoAdvanceInterval) {
                        clearInterval(autoAdvanceInterval);
                        autoAdvanceInterval = null;
                    }
                }

                // Pause on hover
                const partnersWrapper = document.querySelector('.partners-logos-wrapper');
                if (partnersWrapper) {
                    partnersWrapper.addEventListener('mouseenter', () => {
                        stopAutoAdvance();
                    });
                    partnersWrapper.addEventListener('mouseleave', () => {
                        startAutoAdvance();
                    });
                }

                // Initialize
                function initPartnersCarousel() {
                    if (partners.length === 0) {
                        return;
                    }
                    createLogos();
                    // Set initial fade-in state
                    partnersNameEl.classList.add('fade-in');
                    partnersDescEl.classList.add('fade-in');
                    updatePartnerDisplay();
                    startAutoAdvance();
                }

                // Initialize on load
                setTimeout(() => {
                    initPartnersCarousel();
                }, 100);
            }

            // Check if device is mobile/tablet (disable complex animations on mobile)
            const isMobile = window.matchMedia('(max-width: 960px)').matches;

            // Only run complex animations on desktop
            if (!isMobile) {
                // Hide hero-left section after 3 seconds (keep hero-right visible)
                const heroRightSection = document.querySelector('.hero-right');
                const heroLeftSection = document.querySelector('.hero-left');
                if (heroRightSection && heroLeftSection) {
                    // Hide hero-left section after 3 seconds
                    setTimeout(() => {
                        heroLeftSection.style.transition = 'opacity 0.5s ease-out, visibility 0.5s ease-out';
                        heroLeftSection.style.opacity = '0';
                        heroLeftSection.style.visibility = 'hidden';
                    }, 3000);

                    // Push hero-right to the left side after hero-left fade completes (3s + 0.5s = 3.5s)
                    setTimeout(() => {
                        heroRightSection.style.transition = 'transform 2s cubic-bezier(0.4, 0, 0.2, 1)';
                        heroRightSection.style.transform = 'translateX(calc(-100% - 40px))';
                    }, 3500);
                }

                // Show mission section on the right side after hero-right transition completes (3.5s + 2s = 5.5s)
                const missionSection = document.querySelector('.mission-section');
                if (missionSection) {
                    setTimeout(() => {
                        missionSection.classList.add('visible');
                    }, 5500);

                    // Hide mission section after 5 seconds (5.5s + 5s = 10.5s)
                    setTimeout(() => {
                        missionSection.style.transition = 'opacity 1s ease-out, transform 1s ease-out, visibility 1s ease-out';
                        missionSection.style.opacity = '0';
                        missionSection.style.transform = 'translateY(30px)';
                        missionSection.style.visibility = 'hidden';
                    }, 10500);
                }

                // Show vision section after mission section appears (5.5s + 1s transition = 6.5s)
                const visionSection = document.querySelector('.vision-section');
                if (visionSection) {
                    setTimeout(() => {
                        visionSection.classList.add('visible');
                    }, 6500);

                    // Hide vision section after mission section disappears (10.5s + 1s fade = 11.5s)
                    setTimeout(() => {
                        visionSection.style.transition = 'opacity 1s ease-out, transform 1s ease-out, visibility 1s ease-out';
                        visionSection.style.opacity = '0';
                        visionSection.style.transform = 'translateY(30px)';
                        visionSection.style.visibility = 'hidden';
                    }, 11500);
                }

                // Push hero-right back to the right side after mission and vision disappear (11.5s + 1s fade = 12.5s)
                if (heroRightSection) {
                    setTimeout(() => {
                        heroRightSection.style.transition = 'transform 2s cubic-bezier(0.4, 0, 0.2, 1)';
                        heroRightSection.style.transform = 'translateX(0)';
                    }, 12500);
                }

                // Show Core Values section on the left side after hero-right returns to right (12.5s + 2s transition = 14.5s)
                const coreValuesSection = document.getElementById('coreValuesSection');
                const cultureSection = document.getElementById('cultureSection');
                
                if (coreValuesSection) {
                    setTimeout(() => {
                        coreValuesSection.classList.add('visible');
                    }, 14500);

                    // Hide Core Values section after 5 seconds (14.5s + 5s = 19.5s)
                    setTimeout(() => {
                        coreValuesSection.style.transition = 'opacity 1s ease-out, transform 1s ease-out, visibility 1s ease-out';
                        coreValuesSection.style.opacity = '0';
                        coreValuesSection.style.transform = 'translateY(30px)';
                        coreValuesSection.style.visibility = 'hidden';
                    }, 19500);
                }

                // Show CULTURE section after Core Values fades out (19.5s + 1s fade = 20.5s)
                if (cultureSection) {
                    setTimeout(() => {
                        cultureSection.style.transition = 'opacity 1s ease-out, transform 1s ease-out, visibility 1s ease-out';
                        cultureSection.style.visibility = 'visible';
                        cultureSection.classList.add('visible');
                        
                        // Hide CULTURE section after 5 seconds (20.5s + 5s = 25.5s)
                        setTimeout(() => {
                            cultureSection.style.transition = 'opacity 1s ease-out, transform 1s ease-out, visibility 1s ease-out';
                            cultureSection.style.opacity = '0';
                            cultureSection.style.transform = 'translateY(30px)';
                            cultureSection.style.visibility = 'hidden';
                        }, 5000);
                    }, 20500);
                }

                // Show hero content section (title, sub, ctas) on the left after CULTURE section disappears (25.5s + 1s fade = 26.5s)
                const heroContentSection = document.querySelector('.hero-content-section');
                if (heroContentSection) {
                    setTimeout(() => {
                        heroContentSection.classList.add('visible');
                    }, 26500);
                }
            } else {
                // On mobile, show all sections immediately with scroll reveal
                const missionSection = document.querySelector('.mission-section');
                const visionSection = document.querySelector('.vision-section');
                const coreValuesSectionMobile = document.getElementById('coreValuesSection');
                const cultureSectionMobile = document.getElementById('cultureSection');
                const heroContentSection = document.querySelector('.hero-content-section');

                // These sections are already positioned relatively on mobile via CSS
                // Just ensure they're visible
                if (missionSection) missionSection.classList.add('visible');
                if (visionSection) visionSection.classList.add('visible');
                if (heroContentSection) heroContentSection.classList.add('visible');

                // On mobile, create fade transition between Core Values and CULTURE
                if (coreValuesSectionMobile && cultureSectionMobile) {
                    // Position CULTURE section to overlap Core Values section
                    const positionCultureSection = () => {
                        const coreRect = coreValuesSectionMobile.getBoundingClientRect();
                        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                        cultureSectionMobile.style.position = 'absolute';
                        cultureSectionMobile.style.top = (coreRect.top + scrollTop) + 'px';
                        cultureSectionMobile.style.left = '50%';
                        cultureSectionMobile.style.transform = 'translateX(-50%)';
                        cultureSectionMobile.style.width = '90%';
                        cultureSectionMobile.style.maxWidth = '500px';
                    };

                    // Initial positioning
                    positionCultureSection();
                    
                    // Reposition on resize
                    window.addEventListener('resize', positionCultureSection);

                    let currentSection = 'coreValues';
                    const switchInterval = 6000; // Switch every 6 seconds

                    // Show Core Values first
                    coreValuesSectionMobile.classList.add('visible');
                    cultureSectionMobile.classList.remove('visible');

                    // Auto-switch between sections
                    setInterval(() => {
                        if (currentSection === 'coreValues') {
                            // Fade out Core Values, fade in CULTURE
                            coreValuesSectionMobile.classList.remove('visible');
                            setTimeout(() => {
                                cultureSectionMobile.classList.add('visible');
                                currentSection = 'culture';
                            }, 2500); // Wait for fade out to complete
                        } else {
                            // Fade out CULTURE, fade in Core Values
                            cultureSectionMobile.classList.remove('visible');
                            setTimeout(() => {
                                coreValuesSectionMobile.classList.add('visible');
                                currentSection = 'coreValues';
                            }, 2500); // Wait for fade out to complete
                        }
                    }, switchInterval);
                }
            }

            // Center announcement section when clicked on mobile
            const announcementLinks = document.querySelectorAll('a[href="#announcement"]');
            announcementLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    // Only center on mobile/responsive
                    if (window.matchMedia('(max-width: 960px)').matches) {
                        e.preventDefault();
                        const announcementSection = document.getElementById('announcement');
                        if (announcementSection) {
                            // Wait a moment for any layout changes
                            setTimeout(() => {
                                const sectionRect = announcementSection.getBoundingClientRect();
                                const sectionTop = window.pageYOffset + sectionRect.top;
                                const sectionHeight = sectionRect.height;
                                const viewportHeight = window.innerHeight;
                                const nav = document.querySelector('.nav');
                                const navHeight = nav ? nav.offsetHeight : 0;
                                
                                // Calculate position to center the section in viewport
                                // Account for navbar height
                                const centerPosition = sectionTop - (viewportHeight / 2) + (sectionHeight / 2) - (navHeight / 2);
                                
                                window.scrollTo({
                                    top: Math.max(0, centerPosition),
                                    behavior: 'smooth'
                                });
                            }, 10);
                        }
                    }
                });
            });

            // Login modal interactions
            const loginBtn = document.getElementById('loginBtn');
            const loginModal = document.getElementById('loginModal');
            const loginModalClose = document.getElementById('loginModalClose');
            const loginUsernameEl = document.getElementById('login-username');

            function openLoginModal() {
                if (!loginModal) return;
                loginModal.classList.add('active');
                loginModal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
                document.body.classList.add('modal-open');
                setTimeout(() => {
                    if (loginUsernameEl) loginUsernameEl.focus();
                }, 150);
            }

            function closeLoginModal() {
                if (!loginModal) return;
                loginModal.classList.remove('active');
                loginModal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
                document.body.classList.remove('modal-open');
            }

            if (loginBtn) {
                loginBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    openLoginModal();
                });
            }

            // Smooth scroll for anchor links with offset for sticky navbar
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    const href = this.getAttribute('href');
                    if (href === '#' || href === '#!') return;
                    
                    const targetId = href.substring(1);
                    const targetElement = document.getElementById(targetId);
                    
                    if (targetElement) {
                        e.preventDefault();
                        const navHeight = document.querySelector('.nav')?.offsetHeight || 0;
                        const navTop = parseInt(window.getComputedStyle(document.querySelector('.nav')).top) || 0;
                        const offset = navHeight + navTop + 80; // 80px extra padding to move down more
                        const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - offset;
                        
                        window.scrollTo({
                            top: targetPosition,
                            behavior: 'smooth'
                        });
                    }
                });
            });

            if (loginModalClose) {
                loginModalClose.addEventListener('click', () => {
                    closeLoginModal();
                });
            }

            if (loginModal) {
                loginModal.addEventListener('click', (e) => {
                    if (e.target === loginModal) {
                        closeLoginModal();
                    }
                });
            }

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && loginModal && loginModal.classList.contains('active')) {
                    closeLoginModal();
                }
            });

            // Search button functionality
            const searchBtn = document.getElementById('searchBtn');
            
            // Function to smoothly close search input
            function closeSearchInput() {
                const searchInput = document.getElementById('navbarSearchInput');
                const nav = searchBtn ? searchBtn.closest('.nav') : null;
                if (searchInput) {
                    searchInput.classList.remove('active');
                    // Remove search-active class when search closes (mobile only)
                    if (nav && window.matchMedia('(max-width: 960px)').matches) {
                        nav.classList.remove('search-active');
                    }
                    setTimeout(() => {
                        const checkInput = document.getElementById('navbarSearchInput');
                        if (checkInput && !checkInput.classList.contains('active')) {
                            checkInput.remove();
                        }
                    }, 300); // Wait for animation to complete
                }
            }
            
            if (searchBtn) {
                searchBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    // Create or toggle search input
                    let searchInput = document.getElementById('navbarSearchInput');
                    if (!searchInput) {
                        // Create search input if it doesn't exist
                        searchInput = document.createElement('input');
                        searchInput.type = 'text';
                        searchInput.id = 'navbarSearchInput';
                        searchInput.placeholder = 'Search...';
                        searchInput.className = 'navbar-search-input';
                        
                        // On mobile, append to nav container; on desktop, insert before search button
                        const nav = searchBtn.closest('.nav');
                        if (nav && window.matchMedia('(max-width: 960px)').matches) {
                            nav.appendChild(searchInput);
                        } else {
                            searchBtn.parentNode.insertBefore(searchInput, searchBtn);
                        }
                        
                        // Trigger smooth animation by adding active class after a brief delay
                        setTimeout(() => {
                            searchInput.classList.add('active');
                            searchInput.focus();
                            // Add search-active class when search opens (mobile only)
                            const nav = searchBtn.closest('.nav');
                            if (nav && window.matchMedia('(max-width: 960px)').matches) {
                                nav.classList.add('search-active');
                            }
                        }, 10);
                        
                        // Handle search on Enter key
                        searchInput.addEventListener('keydown', (event) => {
                            if (event.key === 'Enter') {
                                const searchTerm = searchInput.value.trim();
                                if (searchTerm) {
                                    // Perform search - you can customize this behavior
                                    console.log('Searching for:', searchTerm);
                                    // Example: scroll to matching content or filter results
                                    // You can implement your search logic here
                                }
                            }
                            if (event.key === 'Escape') {
                                closeSearchInput();
                            }
                        });
                        
                        // Remove search input when clicking outside
                        setTimeout(() => {
                            document.addEventListener('click', function removeSearchInput(e) {
                                const isMobile = window.matchMedia('(max-width: 960px)').matches;
                                const nav = searchBtn.closest('.nav');
                                const clickedInsideNav = nav && nav.contains(e.target);
                                
                                if (!searchInput.contains(e.target) && e.target !== searchBtn && (!isMobile || !clickedInsideNav)) {
                                    closeSearchInput();
                                    document.removeEventListener('click', removeSearchInput);
                                }
                            });
                        }, 100);
                    } else {
                        // Toggle search input visibility
                        if (searchInput.classList.contains('active')) {
                            closeSearchInput();
                        } else {
                            searchInput.classList.add('active');
                            setTimeout(() => {
                                searchInput.focus();
                                // Add search-active class when search opens (mobile only)
                                const nav = searchBtn.closest('.nav');
                                if (nav && window.matchMedia('(max-width: 960px)').matches) {
                                    nav.classList.add('search-active');
                                }
                            }, 10);
                        }
                    }
                });
            }

            // ---- Real-time form validation ----
            function getOrCreateErrorEl(fieldWrapper) {
                if (!fieldWrapper) return null;
                let el = fieldWrapper.querySelector('.form-error');
                if (!el) {
                    el = document.createElement('div');
                    el.className = 'form-error';
                    fieldWrapper.appendChild(el);
                }
                return el;
            }

            function clearError(input) {
                if (!input) return;
                input.classList.remove('invalid');
                const wrapper = input.closest('.form-field');
                if (!wrapper) return;
                const el = wrapper.querySelector('.form-error');
                if (el) el.textContent = '';
            }

            function setError(input, message) {
                if (!input) return;
                input.classList.add('invalid');
                const wrapper = input.closest('.form-field');
                const el = getOrCreateErrorEl(wrapper);
                if (el) el.textContent = message;
            }

            function validateEmailFormat(value) {
                if (!value) return false;
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(String(value).toLowerCase());
            }

            // Login form validation and submission
            const loginForm = document.getElementById('loginForm');
            const loginUsername = document.getElementById('login-username');
            const loginPassword = document.getElementById('login-password');
            const loginSubmitBtn = document.getElementById('loginSubmitBtn');
            const errorMessage = document.getElementById('errorMessage');
            const successMessage = document.getElementById('successMessage');

            function hideMessages() {
                if (errorMessage) errorMessage.style.display = 'none';
                if (successMessage) successMessage.style.display = 'none';
            }

            function showError(message) {
                if (errorMessage) {
                    errorMessage.textContent = message;
                    errorMessage.style.display = 'block';
                    if (successMessage) successMessage.style.display = 'none';
                }
            }

            function showSuccess(message) {
                if (successMessage) {
                    successMessage.textContent = message;
                    successMessage.style.display = 'block';
                    if (errorMessage) errorMessage.style.display = 'none';
                }
            }

            function setLoading(loading) {
                if (loginSubmitBtn) {
                    if (loading) {
                        loginSubmitBtn.disabled = true;
                        loginSubmitBtn.innerHTML = '<span class="loading" style="display: inline-block; width: 16px; height: 16px; border: 2px solid rgba(2, 6, 23, 0.3); border-radius: 50%; border-top-color: #020617; animation: spin 0.8s linear infinite; margin-right: 8px;"></span>Logging in...';
                    } else {
                        loginSubmitBtn.disabled = false;
                        loginSubmitBtn.innerHTML = 'Log in';
                    }
                }
            }

            if (loginUsername) {
                loginUsername.addEventListener('input', () => {
                    if (loginUsername.value.trim()) {
                        clearError(loginUsername);
                    }
                    hideMessages();
                });
            }

            if (loginPassword) {
                loginPassword.addEventListener('input', () => {
                    if (loginPassword.value) {
                        clearError(loginPassword);
                    }
                    hideMessages();
                });
            }

            // Function to set session via PHP
            async function setSession(userData) {
                try {
                    const response = await fetch('api/set_session.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(userData)
                    });
                    return await response.json();
                } catch (error) {
                    console.error('Session error:', error);
                }
            }

            if (loginForm) {
                loginForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    hideMessages();
                    clearError(loginUsername);
                    clearError(loginPassword);

                    let isValid = true;
                    if (!loginUsername.value.trim()) {
                        setError(loginUsername, 'Username is required.');
                        isValid = false;
                    }
                    if (!loginPassword.value) {
                        setError(loginPassword, 'Password is required.');
                        isValid = false;
                    }

                    if (!isValid) {
                        return;
                    }

                    setLoading(true);

                    try {
                        const saParams = new URLSearchParams();
                        saParams.append('username', loginUsername.value.trim());
                        saParams.append('password', loginPassword.value);

                        const saResp = await fetch('production/includes/superadmin_login.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: saParams
                        });

                        if (saResp.ok) {
                            const saData = await saResp.json();
                            if (saData && saData.success) {
                                showSuccess('Login successful! Redirecting...');
                                setTimeout(() => {
                                    window.location.href = saData.redirect || 'superadmin/dashboard/main.php';
                                }, 800);
                                return;
                            }
                        }

                        const formData = new URLSearchParams();
                        formData.append('txtUserName', loginUsername.value.trim());
                        formData.append('txtPassword', loginPassword.value);

                        const response = await fetch('BCCWeb/TPLoginAPI.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: formData
                        });

                        if (!response.ok) {
                            let errorData;
                            try {
                                errorData = await response.json();
                            } catch (e) {
                                errorData = { message: `HTTP Error ${response.status}: ${response.statusText}` };
                            }
                            throw new Error(errorData.message || errorData.error || 'An error occurred');
                        }

                        const data = await response.json();

                        if (data.success) {
                            showSuccess('Login successful! Redirecting...');
                            let redirectUrl = 'index.php';
                            if (data.data.user_type === 'student') {
                                redirectUrl = 'student/main.php';
                            } else if (data.data.user_type === 'user') {
                                if (data.data.role === 'admin') {
                                    redirectUrl = 'admin/dashboard/main.php';
                                } else if (data.data.role === 'superadmin') {
                                    redirectUrl = 'admin/dashboard/main.php';
                                } else {
                                    redirectUrl = 'student/main.php';
                                }
                            }
                            await setSession(data.data);
                            setTimeout(() => {
                                window.location.href = redirectUrl;
                            }, 1000);
                        } else {
                            showError(data.message || 'Invalid username or password.');
                            setLoading(false);
                        }
                    } catch (error) {
                        console.error('Login error:', error);
                        const errorMsg = error.message || 'An error occurred. Please try again.';
                        showError(errorMsg);
                        setLoading(false);
                    }
                });
            }

            // Signup form validation
            const signupForm = document.getElementById('signupForm');
            const signupEmail = document.getElementById('signup-email');
            const signupUsername = document.getElementById('signup-username');
            const signupPassword = document.getElementById('signup-password');
            const signupConfirmPassword = document.getElementById('signup-confirm-password');

            async function checkAvailability(field, value) {
                if (!value) return null;
                try {
                    const params = new URLSearchParams();
                    if (field === 'username') {
                        params.append('username', value);
                    } else if (field === 'email') {
                        params.append('email', value);
                    }

                    const response = await fetch('functions/registration/check_availability.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                        },
                        body: params.toString()
                    });

                    if (!response.ok) return null;
                    const data = await response.json();
                    return data;
                } catch (e) {
                    return null;
                }
            }

            async function validateSignupForm(e) {
                let valid = true;

                // Full name
                const fullnameInput = document.getElementById('signup-fullname');
                if (fullnameInput) {
                    const v = fullnameInput.value.trim();
                    clearError(fullnameInput);
                    if (!v) {
                        setError(fullnameInput, 'Full name is required.');
                        valid = false;
                    } else if (v.length < 3) {
                        setError(fullnameInput, 'Full name looks too short.');
                        valid = false;
                    }
                }

                // Email
                if (signupEmail) {
                    const v = signupEmail.value.trim();
                    clearError(signupEmail);
                    if (!v) {
                        setError(signupEmail, 'Email is required.');
                        valid = false;
                    } else if (!validateEmailFormat(v)) {
                        setError(signupEmail, 'Enter a valid email address.');
                        valid = false;
                    } else {
                        const availability = await checkAvailability('email', v);
                        if (availability && availability.emailTaken) {
                            setError(signupEmail, 'This email is already in use.');
                            valid = false;
                        }
                    }
                }

                // Username
                if (signupUsername) {
                    const v = signupUsername.value.trim();
                    clearError(signupUsername);
                    if (!v) {
                        setError(signupUsername, 'Username is required.');
                        valid = false;
                    } else if (v.length < 4) {
                        setError(signupUsername, 'Username must be at least 4 characters.');
                        valid = false;
                    } else {
                        const availability = await checkAvailability('username', v);
                        if (availability && availability.usernameTaken) {
                            setError(signupUsername, 'This username is already taken.');
                            valid = false;
                        }
                    }
                }

                // Password
                if (signupPassword) {
                    const v = signupPassword.value;
                    clearError(signupPassword);
                    if (!v) {
                        setError(signupPassword, 'Password is required.');
                        valid = false;
                    } else if (v.length < 8) {
                        setError(signupPassword, 'Password must be at least 8 characters.');
                        valid = false;
                    }
                }

                // Confirm password
                if (signupConfirmPassword && signupPassword) {
                    const v = signupConfirmPassword.value;
                    clearError(signupConfirmPassword);
                    if (!v) {
                        setError(signupConfirmPassword, 'Please confirm your password.');
                        valid = false;
                    } else if (v !== signupPassword.value) {
                        setError(signupConfirmPassword, 'Passwords do not match.');
                        valid = false;
                    }
                }

                if (!valid && e) {
                    e.preventDefault();
                }

                return valid;
            }

            if (signupForm) {
                signupForm.addEventListener('submit', (e) => {
                    e.preventDefault();
                    validateSignupForm(e).then((ok) => {
                        if (ok) {
                            signupForm.submit();
                        }
                    });
                });
            }

            // Real-time per-field validation on input/blur
            if (signupEmail) {
                signupEmail.addEventListener('input', () => {
                    const v = signupEmail.value.trim();
                    if (!v) {
                        setError(signupEmail, 'Email is required.');
                    } else if (!validateEmailFormat(v)) {
                        setError(signupEmail, 'Enter a valid email address.');
                    } else {
                        clearError(signupEmail);
                    }
                });

                signupEmail.addEventListener('blur', async () => {
                    const v = signupEmail.value.trim();
                    if (!v || !validateEmailFormat(v)) return;
                    const availability = await checkAvailability('email', v);
                    if (availability && availability.emailTaken) {
                        setError(signupEmail, 'This email is already in use.');
                    }
                });
            }

            if (signupUsername) {
                signupUsername.addEventListener('input', () => {
                    const v = signupUsername.value.trim();
                    if (!v) {
                        setError(signupUsername, 'Username is required.');
                    } else if (v.length < 4) {
                        setError(signupUsername, 'Username must be at least 4 characters.');
                    } else {
                        clearError(signupUsername);
                    }
                });

                signupUsername.addEventListener('blur', async () => {
                    const v = signupUsername.value.trim();
                    if (!v || v.length < 4) return;
                    const availability = await checkAvailability('username', v);
                    if (availability && availability.usernameTaken) {
                        setError(signupUsername, 'This username is already taken.');
                    }
                });
            }

            if (signupPassword) {
                signupPassword.addEventListener('input', () => {
                    const v = signupPassword.value;
                    if (!v) {
                        setError(signupPassword, 'Password is required.');
                    } else if (v.length < 8) {
                        setError(signupPassword, 'Password must be at least 8 characters.');
                    } else {
                        clearError(signupPassword);
                    }
                });
            }

            if (signupConfirmPassword && signupPassword) {
                signupConfirmPassword.addEventListener('input', () => {
                    const v = signupConfirmPassword.value;
                    if (!v) {
                        setError(signupConfirmPassword, 'Please confirm your password.');
                    } else if (v !== signupPassword.value) {
                        setError(signupConfirmPassword, 'Passwords do not match.');
                    } else {
                        clearError(signupConfirmPassword);
                    }
                });
            }
        });

        // Back to Top Button
        (function() {
            // Wait for DOM to be ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initBackToTop);
            } else {
                initBackToTop();
            }
            
            function initBackToTop() {
                const backToTopBtn = document.createElement('button');
                backToTopBtn.className = 'back-to-top';
                backToTopBtn.setAttribute('aria-label', 'Back to top');
                backToTopBtn.innerHTML = '↑';
                // Append to html element to avoid any transform containing block issues
                document.documentElement.appendChild(backToTopBtn);

                function toggleBackToTop() {
                    if (window.pageYOffset > 300 || document.documentElement.scrollTop > 300) {
                        backToTopBtn.classList.add('visible');
                    } else {
                        backToTopBtn.classList.remove('visible');
                    }
                }

                backToTopBtn.addEventListener('click', function() {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });

                window.addEventListener('scroll', toggleBackToTop, { passive: true });
                toggleBackToTop(); // Check initial state
            }
        })();
    </script>
</body>

</html>