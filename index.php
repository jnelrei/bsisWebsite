<?php session_start(); ?>
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

        html, body {
            height: 100%;
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Space Grotesk', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: radial-gradient(circle at top left, #0f172a 0, #020617 45%, #000 100%);
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
            background-blend-mode: screen;
        }

        .shell-inner {
            max-width: 1320px;
            margin: 0 auto;
            padding: 26px 20px 80px;
            position: relative;
        }

        @media (max-width: 480px) {
            .shell-inner {
                padding: 16px 12px 40px;
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
            align-items: center; /* center text and right container vertically */
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
            justify-content: center; /* center content within the left column */
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

        .avatar:nth-child(2) { transform: translateX(-10px); background: linear-gradient(145deg, #22c55e, #a855f7); }
        .avatar:nth-child(3) { transform: translateX(-20px); background: linear-gradient(145deg, #a855f7, #f97316); }

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
            min-height: 420px; /* give the right container more height */
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
            top: 186px; /* Align with hero section (80px margin-top + 26px padding + 80px hero margin-top) */
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
                margin-top: 40px;
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
            }
        }

        @media (max-width: 480px) {
            .mission-content {
                padding: 14px 16px;
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
            top: 450px; /* Position below mission section */
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
            }
        }

        @media (max-width: 480px) {
            .vision-content {
                padding: 14px 16px;
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
            border-radius: var(--radius-md);
            padding: 20px 24px;
            background: radial-gradient(circle at top left, rgba(34, 197, 94, 0.12), rgba(15, 23, 42, 0.98));
            border: 1px solid rgba(148, 163, 184, 0.4);
            box-shadow: 0 14px 40px rgba(15, 23, 42, 0.9);
            display: flex;
            align-items: center;
            gap: 16px;
        }

        @media (max-width: 720px) {
            .announcement-section {
                padding: 16px 20px;
                margin-top: 50px;
            }
        }

        @media (max-width: 480px) {
            .announcement-section {
                padding: 14px 16px;
                margin-top: 40px;
                flex-direction: column;
                align-items: flex-start;
            }
        }

        .announcement-icon {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            background: rgba(34, 197, 94, 0.2);
            border: 1px solid rgba(34, 197, 94, 0.5);
            display: grid;
            place-items: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .announcement-content {
            flex: 1;
        }

        .announcement-title {
            font-size: 0.98rem;
            font-weight: 600;
            margin-bottom: 4px;
            color: var(--text-main);
        }

        .announcement-text {
            font-size: 0.88rem;
            color: var(--text-soft);
            line-height: 1.5;
        }

        /* Empower Innovate Succeed Section */
        .empower-section {
            position: absolute;
            top: 186px; /* Align with hero section */
            left: 20px;
            width: calc(50% - 20px);
            max-width: 600px;
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 1s ease-out, transform 1s ease-out;
            pointer-events: none;
        }

        @media (max-width: 960px) {
            .empower-section {
                position: relative;
                top: auto;
                left: auto;
                width: 100%;
                margin-top: 40px;
            }
        }

        .empower-section.visible {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        .empower-content {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        @media (max-width: 720px) {
            .empower-content {
                gap: 18px;
            }
        }

        @media (max-width: 480px) {
            .empower-content {
                gap: 14px;
            }
        }

        .empower-item {
            font-size: clamp(2rem, 4vw, 3.5rem);
            font-weight: 700;
            line-height: 1.1;
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
            top: 186px; /* Align with hero section */
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
            margin-top: 120px; /* push feature cards further down */
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

        /* Split section: Terminal + FAQ */
        .split-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(0, 0.95fr);
            gap: 32px;
            margin-top: 60px;
        }

        @media (max-width: 720px) {
            .split-layout {
                gap: 24px;
                margin-top: 40px;
            }
        }

        @media (max-width: 480px) {
            .split-layout {
                gap: 18px;
                margin-top: 30px;
            }
        }

        .terminal-card {
            border-radius: 26px;
            border: 1px solid rgba(148, 163, 184, 0.55);
            background: radial-gradient(circle at 0% 0%, rgba(34, 197, 94, 0.18), rgba(15, 23, 42, 0.98));
            box-shadow: var(--shadow-soft);
            overflow: hidden;
        }

        .terminal-head {
            padding: 12px 16px 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: radial-gradient(circle at 0% 0%, rgba(34, 197, 94, 0.16), rgba(15, 23, 42, 0.98));
            border-bottom: 1px solid rgba(31, 41, 55, 0.9);
        }

        .traffic-dots {
            display: flex;
            gap: 6px;
        }

        .traffic-dot {
            width: 9px;
            height: 9px;
            border-radius: 999px;
            background: #4b5563;
        }

        .traffic-dot:nth-child(1) { background: #f97316; }
        .traffic-dot:nth-child(2) { background: #22c55e; }
        .traffic-dot:nth-child(3) { background: #e5e7eb; }

        .terminal-title {
            font-size: 11px;
            color: var(--text-soft);
        }

        .terminal-body {
            padding: 14px 16px 16px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
            font-size: 11px;
            background: radial-gradient(circle at 0 0, rgba(34, 197, 94, 0.1), rgba(15, 23, 42, 1));
        }

        .terminal-line {
            display: flex;
            gap: 6px;
            margin-bottom: 4px;
        }

        .terminal-line span.prompt {
            color: #4ade80;
        }

        .terminal-line span.path {
            color: #60a5fa;
        }

        .terminal-line span.cmd {
            color: #e5e7eb;
        }

        .terminal-highlight {
            color: #a5b4fc;
        }

        .terminal-comment {
            color: #6b7280;
        }

        .terminal-grid {
            margin-top: 10px;
            border-radius: 18px;
            border: 1px solid rgba(55, 65, 81, 0.9);
            background: rgba(15, 23, 42, 0.95);
            padding: 10px 12px 9px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
        }

        .terminal-chip {
            font-size: 10px;
            border-radius: 999px;
            padding: 3px 8px;
            background: rgba(15, 23, 42, 0.9);
            border: 1px solid rgba(55, 65, 81, 0.9);
            color: var(--text-soft);
        }

        .terminal-chip span {
            color: var(--accent-strong);
        }

        .faq-card {
            border-radius: 26px;
            border: 1px solid rgba(148, 163, 184, 0.55);
            background: radial-gradient(circle at 100% 0%, rgba(56, 189, 248, 0.2), rgba(15, 23, 42, 0.98));
            box-shadow: var(--shadow-soft);
            padding: 18px 18px 14px;
        }

        .faq-heading {
            font-size: 0.96rem;
            margin-bottom: 4px;
        }

        .faq-subheading {
            font-size: 0.86rem;
            color: var(--text-soft);
            margin-bottom: 12px;
        }

        .faq-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .faq-item {
            border-radius: 14px;
            border: 1px solid rgba(55, 65, 81, 0.9);
            background: rgba(15, 23, 42, 0.95);
            overflow: hidden;
        }

        .faq-btn {
            width: 100%;
            padding: 9px 10px 9px 12px;
            border: none;
            outline: none;
            background: transparent;
            color: inherit;
            text-align: left;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            cursor: pointer;
            font-size: 0.86rem;
        }

        .faq-btn span.label {
            flex: 1;
        }

        .faq-btn span.icon {
            width: 18px;
            height: 18px;
            border-radius: 999px;
            border: 1px solid rgba(75, 85, 99, 1);
            display: grid;
            place-items: center;
            font-size: 11px;
            color: var(--text-soft);
        }

        .faq-item.active {
            border-color: rgba(34, 197, 94, 0.8);
            box-shadow: 0 0 20px rgba(34, 197, 94, 0.5);
        }

        .faq-item.active .faq-btn {
            background: radial-gradient(circle at 0 0, rgba(34, 197, 94, 0.16), rgba(15, 23, 42, 0.95));
        }

        .faq-item.active .faq-btn span.icon {
            border-color: rgba(34, 197, 94, 0.7);
            color: var(--accent-strong);
        }

        .faq-body {
            max-height: 0;
            overflow: hidden;
            border-top: 1px solid rgba(31, 41, 55, 0.9);
            padding: 0 12px;
            font-size: 0.82rem;
            color: var(--text-soft);
            transition: max-height 0.2s ease;
        }

        .faq-item.active .faq-body {
            padding-top: 7px;
            padding-bottom: 10px;
            max-height: 200px;
        }

        /* Faculty Section */
        .faculty-section {
            margin-top: 80px;
        }

        @media (max-width: 720px) {
            .faculty-section {
                margin-top: 50px;
            }
        }

        @media (max-width: 480px) {
            .faculty-section {
                margin-top: 40px;
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

        /* Footer */
        .footer {
            margin-top: 70px;
            padding-top: 20px;
            border-top: 1px solid rgba(31, 41, 55, 0.9);
            font-size: 0.82rem;
            color: var(--text-soft);
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            justify-content: space-between;
            align-items: flex-start;
        }

        @media (max-width: 720px) {
            .footer {
                margin-top: 50px;
                padding-top: 16px;
                gap: 14px;
            }
        }

        @media (max-width: 480px) {
            .footer {
                margin-top: 40px;
                padding-top: 14px;
                gap: 12px;
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
            .shell-inner {
                padding-inline: 16px;
                padding-bottom: 50px;
            }

            .nav {
                padding-inline: 14px;
                gap: 14px;
                flex-wrap: wrap;
            }

            .nav-links {
                display: none;
            }

            .nav-cta {
                gap: 6px;
            }

            .btn {
                padding: 8px 14px;
                font-size: 12px;
            }

            .hero {
                grid-template-columns: minmax(0, 1fr);
                gap: 28px;
                margin-top: 40px;
            }

            .hero-left {
                min-height: auto;
                gap: 18px;
            }

            .hero-right {
                order: -1;
                min-height: 280px;
            }

            .hero-title {
                font-size: clamp(1.8rem, 5vw, 2.4rem);
            }

            .hero-sub {
                font-size: 0.9rem;
            }

            .split-layout {
                grid-template-columns: minmax(0, 1fr);
                gap: 20px;
                margin-top: 40px;
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
            .mission-section,
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
            .shell-inner {
                padding-inline: 14px;
                padding-bottom: 40px;
            }

            .nav {
                padding-inline: 12px;
                padding-block: 8px;
                gap: 10px;
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
                padding: 7px 12px;
                font-size: 11px;
            }

            .btn-primary.btn-large {
                padding: 12px 24px;
                font-size: 14px;
            }

            .hero {
                margin-top: 32px;
                gap: 20px;
            }

            .hero-left {
                gap: 16px;
            }

            .hero-title {
                font-size: clamp(1.6rem, 6vw, 2.05rem);
                line-height: 1.15;
            }

            .hero-sub {
                font-size: 0.88rem;
                max-width: 100%;
            }

            .hero-right {
                min-height: 220px;
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
                gap: 8px;
                margin-top: 6px;
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

            table, tbody, tr, td {
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

            .terminal-card {
                border-radius: 20px;
            }

            .terminal-head {
                padding: 10px 12px;
                flex-wrap: wrap;
                gap: 8px;
            }

            .terminal-title {
                font-size: 10px;
            }

            .terminal-body {
                padding: 12px;
                font-size: 10px;
            }

            .terminal-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 6px;
                padding: 8px 10px;
            }

            .terminal-chip {
                font-size: 9px;
                padding: 4px 6px;
            }

            .faq-card {
                border-radius: 20px;
                padding: 16px;
            }

            .faq-heading {
                font-size: 0.9rem;
            }

            .faq-subheading {
                font-size: 0.8rem;
            }

            .faq-btn {
                padding: 8px 10px;
                font-size: 0.8rem;
            }

            .faq-body {
                font-size: 0.78rem;
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
                font-size: clamp(1.8rem, 8vw, 2.5rem);
            }

            .empower-description {
                font-size: 0.88rem;
            }

            .footer {
                margin-top: 50px;
                padding-top: 16px;
                font-size: 0.78rem;
                gap: 14px;
            }

            .footer-links {
                gap: 10px 14px;
            }
        }

        @media (max-width: 480px) {
            .shell-inner {
                padding-inline: 12px;
                padding-bottom: 32px;
            }

            .nav {
                padding-inline: 10px;
                padding-block: 6px;
                gap: 8px;
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
                padding: 6px 10px;
                font-size: 10px;
            }

            .btn-primary.btn-large {
                padding: 10px 18px;
                font-size: 13px;
            }

            .hero {
                margin-top: 24px;
                gap: 16px;
            }

            .hero-left {
                gap: 12px;
            }

            .hero-title {
                font-size: clamp(1.4rem, 7vw, 1.8rem);
                line-height: 1.2;
            }

            .hero-sub {
                font-size: 0.85rem;
                line-height: 1.6;
            }

            .hero-right {
                min-height: 180px;
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
                gap: 6px;
                margin-top: 4px;
            }

            .hero-ctas .btn {
                width: 100%;
                justify-content: center;
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

            .terminal-card {
                border-radius: 18px;
            }

            .terminal-head {
                padding: 8px 10px;
            }

            .terminal-body {
                padding: 10px;
                font-size: 9px;
            }

            .terminal-grid {
                grid-template-columns: 1fr;
                gap: 4px;
            }

            .terminal-chip {
                font-size: 8px;
            }

            .faq-card {
                padding: 14px;
            }

            .faq-heading {
                font-size: 0.85rem;
            }

            .faq-subheading {
                font-size: 0.78rem;
            }

            .faq-btn {
                padding: 7px 8px;
                font-size: 0.78rem;
            }

            .faq-body {
                font-size: 0.76rem;
                padding: 0 10px;
            }

            .mission-section,
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
                font-size: clamp(1.5rem, 9vw, 2rem);
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
                margin-top: 32px;
                padding-top: 12px;
                font-size: 0.76rem;
                flex-direction: column;
                gap: 10px;
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

            .split-layout {
                gap: 16px;
                margin-top: 24px;
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
                min-height: 200px;
            }
        }

        /* Login Modal */
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.85);
            display: flex;
            align-items: center;
            justify-content: center;
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
    </style>
</head>
<body>
    <div class="page-shell">
        <div class="blur-orb" aria-hidden="true"></div>

        <div class="shell-inner">
            <!-- NAVIGATION -->
            <header class="nav">
                <div class="nav-left">
                    <div class="logo-mark">
                        <img src="images/is_logo.png" alt="BSIS Logo">
                    </div>
                    <div class="logo-text-main">BSIS</div>
                </div>

                <nav class="nav-links" aria-label="Primary">
                    <a href="#why">Why NeoTrade</a>
                    <a href="#market">Market feed</a>
                    <a href="#terminal">Execution engine</a>
                    <a href="#faq">FAQ</a>
                </nav>

                <div class="nav-cta">
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
                        <button class="btn btn-primary btn-large" type="button">
                            More info
                        </button>
                    </div>

                    <div class="hero-footnote"></div>
                </section>

                <!-- Hero visual -->
                <aside class="hero-right" aria-label="Bot visual">
                    <div class="glass-orbit">
                        <div class="glass-grid" aria-hidden="true"></div>
                        <div class="hero-logo-large">
                            <img src="images/bsis_logo.png" alt="BSIS Logo">
                        </div>
                    </div>
                </aside>
            </main>

            <!-- ANNOUNCEMENT SECTION -->
            <div class="announcement-section fade-section" aria-label="Announcement">
                <div class="announcement-icon">📢</div>
                <div class="announcement-content">
                    <div class="announcement-title">Important Announcement</div>
                    <div class="announcement-text">Stay updated with the latest news and updates from the BSIS Department.</div>
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

            <!-- EMPOWER INNOVATE SUCCEED SECTION -->
            <section class="empower-section" aria-label="Empower Innovate Succeed">
                <div class="empower-content">
                    <h2 class="empower-item">EMPOWER</h2>
                    <h2 class="empower-item">INNOVATE</h2>
                    <h2 class="empower-item">SUCCEED</h2>
                    <p class="empower-description">
                        This platform is owned and operated by the BSIS Department of Bago City College, serving as a hub for projects by information systems students and other initiatives of the BSIS department.
                    </p>
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
                    <button class="btn btn-primary btn-large" type="button">
                        More info
                    </button>
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

                <div class="why-grid">
                    <article class="why-card scroll-reveal scroll-reveal-delay-1">
                        <div class="why-icon">◇</div>
                        <h3 class="why-title">Fully decentralized, never custodial</h3>
                        <p class="why-body">
                            Funds sit in your own vault contracts. Bots only read signals and post transactions – never withdrawable by anyone but you.
                        </p>
                    </article>

                    <article class="why-card scroll-reveal scroll-reveal-delay-2">
                        <div class="why-icon">∞</div>
                        <h3 class="why-title">Two profit modes, one interface</h3>
                        <p class="why-body">
                            Choose between ultra‑active scalping bots for intraday gains or slower, market‑neutral vaults tuned for compounding.
                        </p>
                    </article>

                    <article class="why-card scroll-reveal scroll-reveal-delay-3">
                        <div class="why-icon">◎</div>
                        <h3 class="why-title">Transparent, real‑time telemetry</h3>
                        <p class="why-body">
                            Every fill, every fee, and every rebalance is streamed to your dashboard with full on‑chain proofs.
                        </p>
                    </article>
                </div>

                <!-- Market table -->
                <div id="market" class="table-shell scroll-reveal-scale" aria-label="Market update table">
                    <div class="table-tabs">
                        <div class="tab-group" role="tablist" aria-label="Market filters">
                            <button class="tab active" type="button" role="tab">Popular</button>
                            <button class="tab" type="button" role="tab">Gainers</button>
                            <button class="tab" type="button" role="tab">Newly listed</button>
                        </div>
                        <div class="table-latency">Engine latency: <span>&lt; 42ms</span> across 7 venues</div>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>Pair</th>
                                <th>Last price</th>
                                <th>24h change</th>
                                <th>Volume (24h)</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td data-label="Pair">
                                    <div class="pair">
                                        <div class="pair-circle">B</div>
                                        <div>
                                            <div class="pair-symbol">BTC / USDT</div>
                                            <div class="pair-name">Grid‑arbitrage bot</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="price" data-label="Last price">$92,384.21</td>
                                <td class="change-pos" data-label="24h change">+3.87%</td>
                                <td class="vol" data-label="Volume (24h)">$1.84B</td>
                                <td data-label=""><button class="btn-mini" type="button">View strategy</button></td>
                            </tr>
                            <tr>
                                <td data-label="Pair">
                                    <div class="pair">
                                        <div class="pair-circle">E</div>
                                        <div>
                                            <div class="pair-symbol">ETH / USDC</div>
                                            <div class="pair-name">Volatility harvest</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="price" data-label="Last price">$4,812.09</td>
                                <td class="change-pos" data-label="24h change">+6.12%</td>
                                <td class="vol" data-label="Volume (24h)">$926.4M</td>
                                <td data-label=""><button class="btn-mini" type="button">View strategy</button></td>
                            </tr>
                            <tr>
                                <td data-label="Pair">
                                    <div class="pair">
                                        <div class="pair-circle">S</div>
                                        <div>
                                            <div class="pair-symbol">SOL / USDT</div>
                                            <div class="pair-name">Perp basis bot</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="price" data-label="Last price">$172.44</td>
                                <td class="change-pos" data-label="24h change">+10.02%</td>
                                <td class="vol" data-label="Volume (24h)">$418.7M</td>
                                <td data-label=""><button class="btn-mini" type="button">View strategy</button></td>
                            </tr>
                            <tr>
                                <td data-label="Pair">
                                    <div class="pair">
                                        <div class="pair-circle">A</div>
                                        <div>
                                            <div class="pair-symbol">ARB / ETH</div>
                                            <div class="pair-name">L2 mean‑reversion</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="price" data-label="Last price">0.00182</td>
                                <td class="change-neg" data-label="24h change">−2.13%</td>
                                <td class="vol" data-label="Volume (24h)">$68.3M</td>
                                <td data-label=""><button class="btn-mini" type="button">View strategy</button></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="table-foot">
                        <div>Streaming pseudo‑live data for demo purposes only.</div>
                        <div>Protocols: zkSync • Arbitrum • Solana • Base</div>
                    </div>
                </div>
            </section>

            <!-- TERMINAL + FAQ -->
            <section id="terminal" class="split-layout fade-section" aria-label="Execution and FAQ">
                <article class="terminal-card scroll-reveal-left" aria-label="Execution engine terminal">
                    <div class="terminal-head">
                        <div class="traffic-dots" aria-hidden="true">
                            <div class="traffic-dot"></div>
                            <div class="traffic-dot"></div>
                            <div class="traffic-dot"></div>
                        </div>
                        <div class="terminal-title">bots.neotrade.app — orchestrator</div>
                        <div class="terminal-title">Latency &lt; 40ms</div>
                    </div>
                    <div class="terminal-body">
                        <div class="terminal-line">
                            <span class="prompt">neo@mesh</span>
                            <span class="path">~/bots</span>
                            <span class="cmd">$ deploy --strategy <span class="terminal-highlight">quantum-grid-v3</span> --size 25,000 USDT</span>
                        </div>
                        <div class="terminal-line terminal-comment"># compiling risk profile · backtesting on 4.3 years of data…</div>
                        <div class="terminal-line">
                            <span class="prompt">✔</span>
                            <span class="cmd"> 12 nodes synced · 0 warnings · 0 failed checks</span>
                        </div>
                        <div class="terminal-line">
                            <span class="prompt">⇢</span>
                            <span class="cmd"> routing initial orders across <span class="terminal-highlight">7 liquidity venues</span></span>
                        </div>
                        <div class="terminal-line terminal-comment"># live PnL updating every block · withdraw anytime</div>

                        <div class="terminal-grid" aria-hidden="true">
                            <div class="terminal-chip">Sharpe <span>3.91</span></div>
                            <div class="terminal-chip">Win‑rate <span>62%</span></div>
                            <div class="terminal-chip">Max DD <span>−6.2%</span></div>
                            <div class="terminal-chip">Fees saved <span>34%</span></div>
                            <div class="terminal-chip">Slippage &lt; <span>0.12%</span></div>
                            <div class="terminal-chip">Bots online <span>48</span></div>
                            <div class="terminal-chip">Markets <span>320+</span></div>
                            <div class="terminal-chip">Chains <span>6</span></div>
                        </div>
                    </div>
                </article>

                <article id="faq" class="faq-card scroll-reveal-right" aria-label="Frequently asked questions">
                    <h2 class="faq-heading">Frequently asked questions</h2>
                    <p class="faq-subheading">The key things traders ask us when moving from manual clicking to always‑on automation.</p>

                    <div class="faq-list">
                        <div class="faq-item active">
                            <button class="faq-btn" type="button">
                                <span class="label">How do I connect my wallet and start?</span>
                                <span class="icon">−</span>
                            </button>
                            <div class="faq-body">
                                Connect any EVM wallet, choose a strategy, and sign a single transaction to deposit into your personal vault contract. From there, bots simply orchestrate trades on your behalf — you can pause or withdraw at any time.
                            </div>
                        </div>

                        <div class="faq-item">
                            <button class="faq-btn" type="button">
                                <span class="label">Are funds safe? Can NeoTrade move my assets?</span>
                                <span class="icon">+</span>
                            </button>
                            <div class="faq-body">
                                No. Strategies interact with your funds only through audited smart contracts with role‑based permissions. The contracts can rebalance and hedge, but never withdraw to unknown addresses. You remain the sole owner of your keys and assets.
                            </div>
                        </div>

                        <div class="faq-item">
                            <button class="faq-btn" type="button">
                                <span class="label">What returns should I realistically expect?</span>
                                <span class="icon">+</span>
                            </button>
                            <div class="faq-body">
                                Historical performance has ranged between 2–5% daily depending on market conditions, but future returns are never guaranteed. We show full drawdown curves, stress tests, and risk metrics so you can size positions based on your own tolerance.
                            </div>
                        </div>

                        <div class="faq-item">
                            <button class="faq-btn" type="button">
                                <span class="label">Can I withdraw or switch strategies anytime?</span>
                                <span class="icon">+</span>
                            </button>
                            <div class="faq-body">
                                Yes. You can exit instantly when liquidity allows, or schedule gradual unwinds to minimize impact. Switching between strategies is as simple as reallocating from one vault to another.
                            </div>
                        </div>
                    </div>
                </article>
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
                        <div class="faculty-image" style="width: 100%; aspect-ratio: 1; border-radius: 16px; background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(56, 189, 248, 0.2)); display: flex; align-items: center; justify-content: center; font-size: 3rem; color: rgba(148, 163, 184, 0.6);">👨‍🏫</div>
                        <div class="faculty-name">Faculty Name</div>
                        <div class="faculty-position">Position/Title</div>
                    </article>

                    <article class="faculty-card scroll-reveal scroll-reveal-delay-2">
                        <div class="faculty-image" style="width: 100%; aspect-ratio: 1; border-radius: 16px; background: linear-gradient(135deg, rgba(56, 189, 248, 0.2), rgba(129, 140, 248, 0.2)); display: flex; align-items: center; justify-content: center; font-size: 3rem; color: rgba(148, 163, 184, 0.6);">👩‍🏫</div>
                        <div class="faculty-name">Faculty Name</div>
                        <div class="faculty-position">Position/Title</div>
                    </article>

                    <article class="faculty-card scroll-reveal scroll-reveal-delay-3">
                        <div class="faculty-image" style="width: 100%; aspect-ratio: 1; border-radius: 16px; background: linear-gradient(135deg, rgba(129, 140, 248, 0.2), rgba(34, 197, 94, 0.2)); display: flex; align-items: center; justify-content: center; font-size: 3rem; color: rgba(148, 163, 184, 0.6);">👨‍🏫</div>
                        <div class="faculty-name">Faculty Name</div>
                        <div class="faculty-position">Position/Title</div>
                    </article>

                    <article class="faculty-card scroll-reveal scroll-reveal-delay-4">
                        <div class="faculty-image" style="width: 100%; aspect-ratio: 1; border-radius: 16px; background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(249, 115, 22, 0.2)); display: flex; align-items: center; justify-content: center; font-size: 3rem; color: rgba(148, 163, 184, 0.6);">👩‍🏫</div>
                        <div class="faculty-name">Faculty Name</div>
                        <div class="faculty-position">Position/Title</div>
                    </article>
                </div>
            </section>

            <!-- FOOTER -->
            <footer class="footer fade-section scroll-reveal-fade">
                <div>
                    © <?php echo date('Y'); ?> NeoTradeBots. All rights reserved.
                </div>
                <div class="footer-links">
                    <a href="#">Status</a>
                    <a href="#">Docs</a>
                    <a href="#">Security</a>
                    <a href="#">Terms</a>
                    <a href="#">Privacy</a>
                </div>
                <div class="footer-tag">Prototype UI • <span>demo data only</span></div>
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
                    Enter your credentials to continue. Sign in with your TechnoPal account.
                </p>
                <?php if (isset($_SESSION['login_error'])): ?>
                    <div class="form-error" id="modalErrorMsg" style="margin-bottom: 14px; padding: 8px 12px; border-radius: 8px; background: rgba(251, 113, 133, 0.1); border: 1px solid rgba(251, 113, 133, 0.3); color: #fca5a5; font-size: 14px;">
                        <?php 
                        echo htmlspecialchars($_SESSION['login_error']);
                        unset($_SESSION['login_error']);
                        ?>
                    </div>
                <?php else: ?>
                    <div class="form-error" id="modalErrorMsg" style="display: none; margin-bottom: 14px; padding: 8px 12px; border-radius: 8px; background: rgba(251, 113, 133, 0.1); border: 1px solid rgba(251, 113, 133, 0.3); color: #fca5a5; font-size: 14px;"></div>
                <?php endif; ?>
                <form id="loginForm" autocomplete="off">
                    <div class="form-field">
                        <label class="form-label" for="login-username">User Name</label>
                        <input class="form-input" type="text" id="login-username" name="txtUserName" placeholder="Enter your username" required>
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="login-password">Password</label>
                        <input class="form-input" type="password" id="login-password" name="txtPassword" placeholder="Enter your password" required>
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

    <script>
        // Simple FAQ accordion interaction + smooth section reveals
        document.addEventListener('DOMContentLoaded', function () {
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
                    },
                    {
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

                // Show empower section on the left side after hero-right returns to right (12.5s + 2s transition = 14.5s)
                const empowerSection = document.querySelector('.empower-section');
                if (empowerSection) {
                    setTimeout(() => {
                        empowerSection.classList.add('visible');
                    }, 14500);

                    // Hide empower section after 5 seconds (14.5s + 5s = 19.5s)
                    setTimeout(() => {
                        empowerSection.style.transition = 'opacity 1s ease-out, transform 1s ease-out, visibility 1s ease-out';
                        empowerSection.style.opacity = '0';
                        empowerSection.style.transform = 'translateY(30px)';
                        empowerSection.style.visibility = 'hidden';
                    }, 19500);
                }

                // Show hero content section (title, sub, ctas) on the left after empower section disappears (19.5s + 1s fade = 20.5s)
                const heroContentSection = document.querySelector('.hero-content-section');
                if (heroContentSection) {
                    setTimeout(() => {
                        heroContentSection.classList.add('visible');
                    }, 20500);
                }
            } else {
                // On mobile, show all sections immediately with scroll reveal
                const missionSection = document.querySelector('.mission-section');
                const visionSection = document.querySelector('.vision-section');
                const empowerSection = document.querySelector('.empower-section');
                const heroContentSection = document.querySelector('.hero-content-section');
                
                // These sections are already positioned relatively on mobile via CSS
                // Just ensure they're visible
                if (missionSection) missionSection.classList.add('visible');
                if (visionSection) visionSection.classList.add('visible');
                if (empowerSection) empowerSection.classList.add('visible');
                if (heroContentSection) heroContentSection.classList.add('visible');
            }

            // Login modal interactions
            const loginBtn = document.getElementById('loginBtn');
            const loginModal = document.getElementById('loginModal');
            const loginModalClose = document.getElementById('loginModalClose');
            const loginUsername = document.getElementById('login-username');

            function openLoginModal() {
                if (!loginModal) return;
                loginModal.classList.add('active');
                loginModal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
                document.body.classList.add('modal-open');
                setTimeout(() => {
                    if (loginUsername) loginUsername.focus();
                }, 150);
            }

            // Auto-open modal if there's a login error
            <?php if (isset($_SESSION['login_error'])): ?>
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(() => {
                    openLoginModal();
                }, 300);
            });
            <?php endif; ?>

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

            // Login form validation
            const loginForm = document.getElementById('loginForm');
            const loginPassword = document.getElementById('login-password');

            function validateLoginForm() {
                let valid = true;
                
                // Clear previous errors
                if (loginUsername) clearError(loginUsername);
                if (loginPassword) clearError(loginPassword);
                
                // Hide any previous error messages
                const errorDiv = document.getElementById('modalErrorMsg');
                if (errorDiv) {
                    errorDiv.style.display = 'none';
                    errorDiv.textContent = '';
                }
                
                // Validate username
                if (loginUsername) {
                    const value = loginUsername.value.trim();
                    if (!value || value.length === 0) {
                        setError(loginUsername, 'Username is required.');
                        valid = false;
                    } else if (value.length < 3) {
                        setError(loginUsername, 'Username must be at least 3 characters.');
                        valid = false;
                    }
                }
                
                // Validate password
                if (loginPassword) {
                    const value = loginPassword.value;
                    if (!value || value.length === 0) {
                        setError(loginPassword, 'Password is required.');
                        valid = false;
                    } else if (value.length < 3) {
                        setError(loginPassword, 'Password must be at least 3 characters.');
                        valid = false;
                    }
                }
                
                return valid;
            }

            if (loginUsername) {
                loginUsername.addEventListener('input', () => {
                    if (loginUsername.value.trim()) {
                        clearError(loginUsername);
                        // Hide error message when user starts typing
                        const errorDiv = document.getElementById('modalErrorMsg');
                        if (errorDiv) {
                            errorDiv.style.display = 'none';
                            errorDiv.textContent = '';
                        }
                    }
                });
            }

            if (loginPassword) {
                loginPassword.addEventListener('input', () => {
                    if (loginPassword.value) {
                        clearError(loginPassword);
                        // Hide error message when user starts typing
                        const errorDiv = document.getElementById('modalErrorMsg');
                        if (errorDiv) {
                            errorDiv.style.display = 'none';
                            errorDiv.textContent = '';
                        }
                    }
                });
            }

            if (loginForm) {
                loginForm.addEventListener('submit', async (e) => {
                        e.preventDefault();
                    
                    if (!validateLoginForm()) {
                        return;
                    }

                    const submitBtn = document.getElementById('loginSubmitBtn');
                    const originalBtnText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span style="display: inline-block; width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.3); border-radius: 50%; border-top-color: white; animation: spin 0.8s linear infinite; margin-right: 8px;"></span>Logging in...';

                    try {
                        // Validate inputs again before sending
                        if (!validateLoginForm()) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalBtnText;
                            return;
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

                        // Parse JSON response (even for error responses)
                        let data;
                        try {
                            const responseText = await response.text();
                            data = JSON.parse(responseText);
                        } catch (parseError) {
                            // If parsing fails
                            console.error('JSON parse error:', parseError);
                            const errorMessage = response.ok 
                                ? 'Invalid response from server. Please try again.'
                                : `Server error (${response.status}): ${response.statusText}`;
                            
                            if (loginPassword) setError(loginPassword, errorMessage);
                            
                            const errorDiv = document.getElementById('modalErrorMsg');
                            if (errorDiv) {
                                errorDiv.textContent = errorMessage;
                                errorDiv.style.display = 'block';
                            }
                            
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalBtnText;
                            return; // Prevent redirect
                        }

                        // Check HTTP status code (after parsing JSON)
                        if (!response.ok) {
                            // Handle HTTP errors (4xx, 5xx)
                            const errorMessage = data.message || data.error || `Server error (${response.status}): ${response.statusText}`;
                            
                            // Clear errors and show new error
                            if (loginUsername) clearError(loginUsername);
                            if (loginPassword) setError(loginPassword, errorMessage);
                            
                            // Show error in modal
                            const errorDiv = document.getElementById('modalErrorMsg');
                            if (errorDiv) {
                                errorDiv.textContent = errorMessage;
                                errorDiv.style.display = 'block';
                            }
                            
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalBtnText;
                            return; // Prevent redirect
                        }

                        // Validate response structure
                        if (!data || typeof data !== 'object') {
                            const errorMessage = 'Invalid response format. Please try again.';
                            
                            if (loginPassword) setError(loginPassword, errorMessage);
                            
                            const errorDiv = document.getElementById('modalErrorMsg');
                            if (errorDiv) {
                                errorDiv.textContent = errorMessage;
                                errorDiv.style.display = 'block';
                            }
                            
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalBtnText;
                            return; // Prevent redirect
                        }

                        // Check if login was successful
                        if (data.success === true && data.data) {
                            // Validate that we have required data
                            if (!data.data.user_id && !data.data.student_id) {
                                const errorMessage = 'Login response missing user information.';
                                
                                if (loginPassword) setError(loginPassword, errorMessage);
                                
                                const errorDiv = document.getElementById('modalErrorMsg');
                                if (errorDiv) {
                                    errorDiv.textContent = errorMessage;
                                    errorDiv.style.display = 'block';
                                }
                                
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = originalBtnText;
                                return; // Prevent redirect
                            }

                            // Set session via API
                            try {
                                const sessionResponse = await fetch('api/set_session.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify(data.data)
                                });

                                if (!sessionResponse.ok) {
                                    throw new Error('Failed to create session');
                                }

                                const sessionData = await sessionResponse.json();
                                if (!sessionData.success) {
                                    throw new Error('Session creation failed');
                                }
                            } catch (sessionError) {
                                console.error('Session error:', sessionError);
                                // Continue anyway, session might still be created
                            }

                            // Determine redirect URL
                            let redirectUrl = 'index.php';
                            if (data.data.user_type === 'student') {
                                redirectUrl = 'student/main.php';
                            } else if (data.data.user_type === 'user') {
                                if (data.data.role === 'admin' || data.data.role === 'superadmin') {
                                    redirectUrl = 'admin/dashboard/main.php';
                                } else {
                                    redirectUrl = 'student/main.php';
                                }
                            }

                            // Only redirect if everything is valid
                            // Close modal first
                            const loginModal = document.getElementById('loginModal');
                            if (loginModal) {
                                loginModal.classList.remove('active');
                                loginModal.setAttribute('aria-hidden', 'true');
                                document.body.style.overflow = '';
                                document.body.classList.remove('modal-open');
                            }
                            
                            // Small delay before redirect to ensure modal closes
                            setTimeout(() => {
                                window.location.href = redirectUrl;
                            }, 100);
                            
                        } else {
                            // Login failed - show error and DO NOT redirect
                            const errorMessage = data.message || data.error || 'Invalid username or password.';
                            
                            // Clear previous errors
                            if (loginUsername) clearError(loginUsername);
                            if (loginPassword) clearError(loginPassword);
                            
                            // Show error on password field
                            if (loginPassword) {
                                setError(loginPassword, errorMessage);
                            }
                            
                            // Show error message in modal
                            const errorDiv = document.getElementById('modalErrorMsg');
                            if (errorDiv) {
                                errorDiv.textContent = errorMessage;
                                errorDiv.style.display = 'block';
                            }
                            
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalBtnText;
                            // DO NOT redirect - stay on login page
                        }
                    } catch (error) {
                        console.error('Login error:', error);
                        
                        // Clear previous errors
                        if (loginUsername) clearError(loginUsername);
                        if (loginPassword) clearError(loginPassword);
                        
                        const errorMessage = error.message || 'An error occurred. Please try again.';
                        
                        // Show error
                        if (loginPassword) {
                            setError(loginPassword, errorMessage);
                        }
                        
                        // Show error in modal
                        const errorDiv = document.getElementById('modalErrorMsg');
                        if (errorDiv) {
                            errorDiv.textContent = errorMessage;
                            errorDiv.style.display = 'block';
                        }
                        
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                        // DO NOT redirect - stay on login page
                    }
                });
            }

            // Add spin animation
            const style = document.createElement('style');
            style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
            document.head.appendChild(style);

        });
    </script>
</body>
</html>


