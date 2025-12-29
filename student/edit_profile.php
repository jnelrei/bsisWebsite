<?php
session_start();

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../functions/db/database.php';

$student_id = $_SESSION['student_id'];
$profile_error = $_SESSION['profile_error'] ?? '';
$profile_success = $_SESSION['profile_success'] ?? '';
unset($_SESSION['profile_error'], $_SESSION['profile_success']);

try {
    $pdo = getPDO();
    
    // Fetch student data - adjust columns based on your actual schema
    $stmt = $pdo->prepare('SELECT * FROM students WHERE student_id = :student_id LIMIT 1');
    $stmt->execute([':student_id' => $student_id]);
    $student = $stmt->fetch();
    
    if (!$student) {
        $_SESSION['profile_error'] = 'Student not found.';
        header('Location: main.php');
        exit;
    }
    
    // Set default values for fields that might not exist
    $fullname = $student['fullname'] ?? $student['name'] ?? '';
    $email = $student['email'] ?? '';
    $phone = $student['phone'] ?? '';
    $bio = $student['bio'] ?? $student['description'] ?? '';
    $profile_picture = $student['profile_picture'] ?? '';
    $skills = $student['skills'] ?? '';
    $interests = $student['interests'] ?? '';
    
} catch (Exception $e) {
    $profile_error = 'Error loading profile: ' . $e->getMessage();
    $student = [];
    $fullname = $email = $phone = $bio = $profile_picture = $skills = $interests = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - BSIS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            width: 100%;
            max-width: 100vw;
            overflow-x: hidden;
        }

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
            --ease-smooth: cubic-bezier(0.25, 0.46, 0.45, 0.94);
            --ease-out-smooth: cubic-bezier(0.23, 1, 0.32, 1);
            --ease-in-out-smooth: cubic-bezier(0.4, 0, 0.2, 1);
            --ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
            --transition-fast: 0.3s var(--ease-smooth);
            --transition-normal: 0.5s var(--ease-smooth);
            --transition-slow: 0.7s var(--ease-smooth);
        }

        /* Light Mode Variables */
        [data-theme="light"] {
            --bg-main: #bfdbfe;
            --bg-elevated: #93c5fd;
            --bg-card: #ffffff;
            --bg-soft: #dbeafe;
            --accent: #16a34a;
            --accent-soft: rgba(22, 163, 74, 0.12);
            --accent-strong: #22c55e;
            --accent-alt: #0284c7;
            --text-main: #000000;
            --text-soft: #1a1a1a;
            --border-subtle: rgba(148, 163, 184, 0.3);
            --glass: rgba(191, 219, 254, 0.9);
            --shadow-soft: 0 24px 60px rgba(0, 0, 0, 0.12);
            --shadow-glow: 0 0 80px rgba(34, 197, 94, 0.25);
        }

        [data-theme="light"] body {
            background: 
                radial-gradient(ellipse at top right, rgba(252, 211, 77, 0.15) 0%, transparent 40%),
                linear-gradient(180deg, 
                    #1e40af 0%, 
                    #2563eb 8%,
                    #3b82f6 18%, 
                    #60a5fa 35%, 
                    #93c5fd 55%, 
                    #bfdbfe 75%, 
                    #dbeafe 90%, 
                    #e0f2fe 100%);
            background-attachment: fixed;
            min-height: 100vh;
        }

        [data-theme="light"] header {
            background: radial-gradient(circle at top left, rgba(191, 219, 254, 0.9), rgba(147, 197, 253, 0.85));
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(148, 163, 184, 0.25);
            box-shadow: 0 18px 60px rgba(0, 0, 0, 0.1);
        }

        [data-theme="light"] .profile-card,
        [data-theme="light"] .form-card {
            background: #ffffff;
            border-color: rgba(148, 163, 184, 0.35);
        }

        [data-theme="light"] .page-title,
        [data-theme="light"] .profile-name,
        [data-theme="light"] .form-section-title,
        [data-theme="light"] .form-group label {
            color: #000000 !important;
        }

        [data-theme="light"] .profile-role,
        [data-theme="light"] .profile-detail-item,
        [data-theme="light"] .form-group input,
        [data-theme="light"] .form-group textarea {
            color: #000000 !important;
        }

        [data-theme="light"] .form-group input,
        [data-theme="light"] .form-group textarea {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(148, 163, 184, 0.3);
        }

        [data-theme="light"] .form-group input:focus,
        [data-theme="light"] .form-group textarea:focus {
            background: rgba(255, 255, 255, 1);
            border-color: rgba(22, 163, 74, 0.5);
        }

        [data-theme="light"] .back-link {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(148, 163, 184, 0.3);
            color: #000000;
        }

        [data-theme="light"] .back-link:hover {
            background: rgba(34, 197, 94, 0.15);
            color: #000000;
        }

        /* Sun Animation - Light Mode Only */
        .sun-container {
            display: none;
            position: fixed;
            top: 10%;
            right: -200px;
            width: 200px;
            height: 200px;
            z-index: 0;
            pointer-events: none;
            will-change: transform;
            animation: sunContainerMove 60s linear infinite;
        }

        @keyframes sunContainerMove {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(calc(-100vw - 100%));
            }
        }

        [data-theme="light"] .sun-container {
            display: block;
        }

        .sun-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 0 40px rgba(252, 211, 77, 0.7)) 
                    drop-shadow(0 0 80px rgba(252, 211, 77, 0.5))
                    drop-shadow(0 0 120px rgba(252, 211, 77, 0.3));
        }

        /* Sun Glow Pulse Animation */
        .sun-glow {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(252, 211, 77, 0.3) 0%, rgba(252, 211, 77, 0.1) 50%, transparent 70%);
            animation: sunGlow 3s ease-in-out infinite;
            pointer-events: none;
        }

        @keyframes sunGlow {
            0%, 100% {
                opacity: 0.6;
                transform: translate(-50%, -50%) scale(1);
            }
            50% {
                opacity: 0.9;
                transform: translate(-50%, -50%) scale(1.15);
            }
        }

        body {
            font-family: 'Space Grotesk', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: radial-gradient(circle at top left, #0f172a 0, #020617 45%, #000 100%);
            color: var(--text-main);
            line-height: 1.6;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            width: 100%;
            max-width: 100vw;
            box-sizing: border-box;
            transition: background 0.3s var(--ease-smooth), color 0.3s var(--ease-smooth);
            min-height: 100vh;
            padding-top: 100px;
        }

        /* Header */
        header {
            position: fixed;
            top: 0;
            width: 100%;
            background: radial-gradient(circle at top left, rgba(15, 23, 42, 0.94), rgba(15, 23, 42, 0.94));
            backdrop-filter: blur(22px);
            border-bottom: 1px solid rgba(148, 163, 184, 0.3);
            box-shadow: 0 18px 60px rgba(15, 23, 42, 0.9);
            z-index: 1000;
            padding: 20px 80px;
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            transition: all var(--transition-normal);
            gap: 20px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
            justify-content: flex-end;
            flex-wrap: nowrap;
        }

        /* Theme Toggle Switch */
        .theme-toggle {
            position: relative;
            width: 60px;
            height: 32px;
            background: rgba(148, 163, 184, 0.2);
            border-radius: var(--radius-pill);
            border: 1px solid rgba(148, 163, 184, 0.3);
            cursor: pointer;
            transition: all var(--transition-normal);
            display: flex;
            align-items: center;
            padding: 3px;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .theme-toggle:hover {
            border-color: rgba(34, 197, 94, 0.5);
            background: rgba(34, 197, 94, 0.1);
        }

        .theme-toggle-slider {
            width: 26px;
            height: 26px;
            background: linear-gradient(135deg, #22c55e, #0ea5e9);
            border-radius: 50%;
            transition: transform var(--transition-normal);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .theme-toggle-slider::before {
            content: '🌙';
            font-size: 14px;
            transition: opacity var(--transition-fast);
        }

        [data-theme="light"] .theme-toggle-slider {
            transform: translateX(28px);
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
        }

        [data-theme="light"] .theme-toggle-slider::before {
            content: '☀️';
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
            font-weight: 600;
            color: var(--text-main);
            letter-spacing: 0.04em;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            overflow: hidden;
            display: grid;
            place-items: center;
            background: transparent;
            padding: 0;
        }

        .logo-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        nav {
            display: flex;
            align-items: center;
            justify-content: center;
            grid-column: 2;
            width: 100%;
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 30px;
            align-items: center;
            margin: 0;
            padding: 0;
            flex-wrap: wrap;
            justify-content: center;
        }

        nav ul li a {
            color: var(--text-soft);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            padding: 8px 10px;
            border-radius: var(--radius-pill);
            border: 1px solid transparent;
            transition: all var(--transition-normal);
            will-change: color, border-color, background, transform;
        }

        nav ul li a:hover {
            color: var(--text-main);
            border-color: rgba(148, 163, 184, 0.5);
            background: rgba(15, 23, 42, 0.8);
        }

        nav ul li a.active {
            color: var(--accent-strong);
            border-color: rgba(34, 197, 94, 0.5);
            background: rgba(15, 23, 42, 0.8);
        }

        [data-theme="light"] nav ul li a {
            color: #000000 !important;
        }

        [data-theme="light"] nav ul li a:hover {
            color: #000000 !important;
            background: rgba(34, 197, 94, 0.15) !important;
        }

        [data-theme="light"] nav ul li a.active {
            color: #16a34a !important;
            background: rgba(34, 197, 94, 0.15) !important;
        }

        .cta-button {
            background: linear-gradient(135deg, #22c55e, #0ea5e9);
            color: var(--bg-main);
            padding: 10px 18px;
            border-radius: var(--radius-pill);
            text-decoration: none;
            font-weight: 500;
            font-size: 13px;
            border: 1px solid rgba(34, 197, 94, 0.7);
            box-shadow: var(--shadow-glow);
            transition: all var(--transition-normal);
            will-change: transform, box-shadow;
        }

        .cta-button:hover {
            transform: translateY(-2px) scale(1.01);
            box-shadow: 0 0 40px rgba(34, 197, 94, 0.7);
        }

        /* Dropdown Menu */
        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-toggle {
            cursor: pointer;
            border: none;
            outline: none;
            white-space: nowrap;
        }

        .dropdown-toggle::after {
            content: ' ▼';
            font-size: 10px;
            margin-left: 5px;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 10px;
            background: radial-gradient(circle at top left, rgba(15, 23, 42, 0.98), rgba(15, 23, 42, 1));
            border: 1px solid rgba(148, 163, 184, 0.3);
            border-radius: var(--radius-md);
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.7);
            min-width: 200px;
            z-index: 1000;
            overflow: hidden;
            opacity: 0;
            transform: translateY(-10px) scale(0.95);
            transition: all var(--transition-normal);
        }

        .dropdown-menu.show {
            display: block;
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        [data-theme="light"] .dropdown-menu {
            background: radial-gradient(circle at top left, rgba(219, 234, 254, 0.95), rgba(191, 219, 254, 0.98));
            backdrop-filter: blur(20px);
            border-color: rgba(148, 163, 184, 0.3);
        }

        .dropdown-item {
            display: block;
            padding: 12px 20px;
            color: var(--text-main);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all var(--transition-normal);
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
            will-change: background, color, transform;
        }

        .dropdown-item:last-child {
            border-bottom: none;
        }

        .dropdown-item:hover {
            background: rgba(34, 197, 94, 0.15);
            color: var(--accent-strong);
            transform: translateX(4px);
        }

        [data-theme="light"] .dropdown-item {
            color: #000000 !important;
        }

        [data-theme="light"] .dropdown-item:hover {
            color: #16a34a !important;
        }

        .dropdown-divider {
            height: 1px;
            background: rgba(148, 163, 184, 0.3);
            margin: 5px 0;
        }

        /* Parallax Cloud Animations */
        .parallax-clouds-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }

        .parallax-cloud {
            position: absolute;
            opacity: 0.5;
            will-change: transform;
            transition: transform 0.1s ease-out;
        }

        .parallax-cloud img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: blur(0px);
        }

        /* Cloud layers with different speeds */
        .cloud-layer-1 {
            width: 200px;
            height: 120px;
            animation: driftSlow 60s linear infinite;
        }

        .cloud-layer-2 {
            width: 250px;
            height: 150px;
            animation: driftMedium 45s linear infinite;
        }

        .cloud-layer-3 {
            width: 180px;
            height: 110px;
            animation: driftFast 35s linear infinite;
        }

        .cloud-layer-4 {
            width: 220px;
            height: 130px;
            animation: driftSlow 70s linear infinite reverse;
        }

        .cloud-layer-5 {
            width: 300px;
            height: 180px;
            animation: driftMedium 50s linear infinite reverse;
        }

        .cloud-layer-6 {
            width: 160px;
            height: 100px;
            animation: driftFast 40s linear infinite reverse;
        }

        @keyframes driftSlow {
            0% {
                transform: translateX(-100px) translateY(0);
            }
            50% {
                transform: translateX(calc(100vw + 100px)) translateY(-30px);
            }
            100% {
                transform: translateX(calc(200vw + 100px)) translateY(0);
            }
        }

        @keyframes driftMedium {
            0% {
                transform: translateX(-150px) translateY(0);
            }
            50% {
                transform: translateX(calc(100vw + 150px)) translateY(-20px);
            }
            100% {
                transform: translateX(calc(200vw + 150px)) translateY(0);
            }
        }

        @keyframes driftFast {
            0% {
                transform: translateX(-200px) translateY(0);
            }
            50% {
                transform: translateX(calc(100vw + 200px)) translateY(-40px);
            }
            100% {
                transform: translateX(calc(200vw + 200px)) translateY(0);
            }
        }

        /* Make clouds more visible in light mode */
        [data-theme="light"] .parallax-cloud {
            opacity: 0.6 !important;
        }

        [data-theme="light"] .parallax-cloud img {
            filter: blur(0px) brightness(1.1);
        }

        /* Ensure content is above clouds */
        .container {
            position: relative;
            z-index: 1;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 80px;
        }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 32px;
            font-weight: 700;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-title::before {
            content: "👋";
            font-size: 28px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-soft);
            text-decoration: none;
            font-size: 14px;
            padding: 8px 16px;
            border-radius: var(--radius-pill);
            border: 1px solid var(--border-subtle);
            background: radial-gradient(circle at top left, rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.95));
            backdrop-filter: blur(10px);
            transition: all var(--transition-normal);
        }

        .back-link:hover {
            color: var(--text-main);
            border-color: var(--accent);
            background: rgba(34, 197, 94, 0.15);
            transform: translateY(-2px);
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        .profile-card,
        .form-card {
            background: radial-gradient(circle at top left, rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.95));
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-md);
            padding: 32px;
            box-shadow: var(--shadow-soft);
            backdrop-filter: blur(20px);
        }

        .profile-picture-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        .profile-picture-wrapper {
            position: relative;
            display: inline-block;
        }

        .profile-picture {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--accent);
            box-shadow: 0 0 20px rgba(34, 197, 94, 0.4);
        }

        .profile-picture-placeholder {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #22c55e, #0ea5e9);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            font-weight: 700;
            color: var(--bg-main);
            border: 3px solid var(--accent);
        }

        .profile-info {
            text-align: center;
        }

        .profile-name {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .profile-role {
            font-size: 14px;
            color: var(--text-soft);
            margin-bottom: 16px;
        }

        .profile-details {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 20px;
        }

        .profile-detail-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: var(--text-soft);
        }

        .profile-detail-item strong {
            color: var(--text-main);
        }

        .upload-btn {
            background: linear-gradient(135deg, #22c55e, #0ea5e9);
            color: var(--bg-main);
            padding: 10px 20px;
            border-radius: var(--radius-pill);
            border: none;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all var(--transition-normal);
        }

        .upload-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(34, 197, 94, 0.5);
        }

        .upload-hint {
            margin-top: 8px;
            color: var(--text-soft);
            font-size: 12px;
        }

        .form-section-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border-subtle);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-main);
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            background: var(--bg-elevated);
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            color: var(--text-main);
            font-size: 14px;
            font-family: inherit;
            transition: all var(--transition-normal);
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
            background: rgba(15, 23, 42, 0.9);
        }

        .form-group input[readonly] {
            background: rgba(15, 23, 42, 0.5);
            color: var(--text-soft);
            cursor: not-allowed;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 30px;
            padding-top: 24px;
            border-top: 1px solid var(--border-subtle);
        }

        .btn {
            padding: 12px 24px;
            border-radius: var(--radius-pill);
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all var(--transition-normal);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #22c55e, #0ea5e9);
            color: var(--bg-main);
            flex: 1;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(34, 197, 94, 0.5);
        }

        .btn-secondary {
            background: var(--bg-elevated);
            color: var(--text-main);
            border: 1px solid var(--border-subtle);
        }

        .btn-secondary:hover {
            border-color: var(--accent);
            background: rgba(34, 197, 94, 0.1);
            transform: translateY(-2px);
        }

        .alert {
            padding: 15px 20px;
            border-radius: var(--radius-md);
            margin-bottom: 25px;
            font-size: 14px;
            background: radial-gradient(circle at top left, rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.95));
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-subtle);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #86efac;
        }

        #profilePictureInput {
            display: none;
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
            padding: 5px;
            z-index: 1001;
            background: transparent;
            border: none;
            outline: none;
        }

        .mobile-menu-toggle span {
            width: 25px;
            height: 3px;
            background: var(--text-main);
            border-radius: 3px;
            transition: all var(--transition-normal);
            display: block;
        }

        .mobile-menu-toggle.active span:nth-child(1) {
            transform: rotate(45deg) translate(8px, 8px);
        }

        .mobile-menu-toggle.active span:nth-child(2) {
            opacity: 0;
        }

        .mobile-menu-toggle.active span:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -7px);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            header {
                padding: 18px 40px;
                grid-template-columns: auto 1fr;
                gap: 15px;
            }

            nav {
                display: none;
            }

            .header-right {
                gap: 15px;
                justify-content: flex-end;
            }

            .container {
                padding: 40px 40px;
            }
        }

        @media (max-width: 968px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }

            .container {
                padding: 40px 30px;
            }
        }

        @media (max-width: 768px) {
            header {
                padding: 15px 20px;
                grid-template-columns: auto 1fr auto;
                gap: 12px;
            }

            .mobile-menu-toggle {
                display: flex;
                order: 3;
            }

            nav {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                margin-top: 0;
                background: radial-gradient(circle at top left, rgba(15, 23, 42, 0.98), rgba(15, 23, 42, 1));
                border-radius: 0;
                padding: 20px;
                border-top: 1px solid rgba(148, 163, 184, 0.2);
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            }

            nav.active {
                display: block;
            }

            nav ul {
                flex-direction: column;
                gap: 8px;
                width: 100%;
            }

            nav ul li {
                width: 100%;
            }

            nav ul li a {
                display: block;
                width: 100%;
                text-align: center;
                padding: 12px 16px;
                font-size: 14px;
            }

            .container {
                padding: 40px 20px;
            }

            .theme-toggle {
                width: 50px;
                height: 28px;
            }

            .theme-toggle-slider {
                width: 22px;
                height: 22px;
            }

            [data-theme="light"] .theme-toggle-slider {
                transform: translateX(22px);
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 30px 15px;
            }

            .page-title {
                font-size: 24px;
            }

            .profile-card,
            .form-card {
                padding: 24px;
            }
        }
    </style>
</head>
<body>
    <!-- Parallax Clouds Container -->
    <div class="parallax-clouds-container" id="parallaxClouds">
        <!-- Sun Animation - Light Mode Only -->
        <div class="sun-container" id="sunContainer">
            <div class="sun-glow"></div>
            <img src="images/sun.png" alt="Sun" class="sun-image" id="sunImage">
        </div>
        
        <!-- Layer 1 - Slow drift -->
        <div class="parallax-cloud cloud-layer-1" style="top: 10%; left: -200px;">
            <img src="images/clouds.png" alt="Cloud">
        </div>
        <div class="parallax-cloud cloud-layer-1" style="top: 25%; left: 30%; animation-delay: -20s;">
            <img src="images/clouds.png" alt="Cloud">
        </div>
        <div class="parallax-cloud cloud-layer-1" style="top: 40%; left: 60%; animation-delay: -40s;">
            <img src="images/clouds.png" alt="Cloud">
        </div>
        
        <!-- Layer 2 - Medium drift -->
        <div class="parallax-cloud cloud-layer-2" style="top: 15%; left: 20%; animation-delay: -10s;">
            <img src="images/clouds.png" alt="Cloud">
        </div>
        <div class="parallax-cloud cloud-layer-2" style="top: 50%; left: 50%; animation-delay: -30s;">
            <img src="images/clouds.png" alt="Cloud">
        </div>
        <div class="parallax-cloud cloud-layer-2" style="top: 70%; left: 80%; animation-delay: -50s;">
            <img src="images/clouds.png" alt="Cloud">
        </div>
        
        <!-- Layer 3 - Fast drift -->
        <div class="parallax-cloud cloud-layer-3" style="top: 5%; left: 40%; animation-delay: -15s;">
            <img src="images/clouds.png" alt="Cloud">
        </div>
        <div class="parallax-cloud cloud-layer-3" style="top: 60%; left: 10%; animation-delay: -35s;">
            <img src="images/clouds.png" alt="Cloud">
        </div>
        
        <!-- Layer 4 - Reverse slow drift -->
        <div class="parallax-cloud cloud-layer-4" style="top: 20%; left: 70%; animation-delay: -25s;">
            <img src="images/clouds.png" alt="Cloud">
        </div>
        <div class="parallax-cloud cloud-layer-4" style="top: 45%; left: 90%; animation-delay: -45s;">
            <img src="images/clouds.png" alt="Cloud">
        </div>
        
        <!-- Layer 5 - Reverse medium drift -->
        <div class="parallax-cloud cloud-layer-5" style="top: 30%; left: 5%; animation-delay: -20s;">
            <img src="images/clouds.png" alt="Cloud">
        </div>
        <div class="parallax-cloud cloud-layer-5" style="top: 65%; left: 35%; animation-delay: -40s;">
            <img src="images/clouds.png" alt="Cloud">
        </div>
        
        <!-- Layer 6 - Reverse fast drift -->
        <div class="parallax-cloud cloud-layer-6" style="top: 35%; left: 55%; animation-delay: -10s;">
            <img src="images/clouds.png" alt="Cloud">
        </div>
        <div class="parallax-cloud cloud-layer-6" style="top: 75%; left: 25%; animation-delay: -30s;">
            <img src="images/clouds.png" alt="Cloud">
        </div>
    </div>

    <!-- Header -->
    <header>
        <div class="logo">
            <div class="logo-icon">
                <img src="../images/is_logo.png" alt="BSIS Logo">
            </div>
            <span>BSIS</span>
        </div>
        <nav id="mainNav">
            <ul>
                <li><a href="main.php#home">HOME</a></li>
                <li><a href="main.php#projects">PROJECTS</a></li>
                <li><a href="main.php#contact">CONTACT</a></li>
            </ul>
        </nav>
        <div class="header-right">
            <button class="theme-toggle" id="themeToggle" aria-label="Toggle theme">
                <span class="theme-toggle-slider"></span>
            </button>
            <div class="mobile-menu-toggle" id="mobileMenuToggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <div class="dropdown">
                <button class="cta-button dropdown-toggle" id="userDropdown">
                    <?php echo strtoupper(htmlspecialchars(explode(' ', $fullname ?: $student_id)[0])); ?>
                </button>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="edit_profile.php" class="dropdown-item">EDIT PROFILE</a>
                    <a href="manage_projects.php" class="dropdown-item">PROJECTS</a>
                    <div class="dropdown-divider"></div>
                    <a href="logout.php" class="dropdown-item">LOGOUT</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Edit Profile</h1>
            <a href="main.php" class="back-link">← Back to Profile</a>
        </div>

        <?php if ($profile_error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($profile_error); ?></div>
        <?php endif; ?>

        <?php if ($profile_success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($profile_success); ?></div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <!-- Profile Card -->
            <div class="profile-card">
                <div class="profile-picture-section">
                    <div class="profile-picture-wrapper">
                        <?php if ($profile_picture && file_exists(__DIR__ . '/uploads/' . $profile_picture)): ?>
                            <img src="uploads/<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="profile-picture">
                        <?php else: ?>
                            <div class="profile-picture-placeholder">
                                <?php echo strtoupper(substr($fullname ?: $student_id, 0, 2)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="profile-info">
                        <div class="profile-name"><?php echo htmlspecialchars($fullname ?: 'Student'); ?></div>
                        <div class="profile-role">Student</div>
                        <input type="file" id="profilePictureInput" name="profile_picture" accept="image/*">
                        <button type="button" class="upload-btn" onclick="document.getElementById('profilePictureInput').click()">
                            Upload Profile Picture
                        </button>
                        <p class="upload-hint">JPG, PNG, GIF - Max 5MB</p>
                    </div>
                </div>
                <div class="profile-details">
                    <div class="profile-detail-item">
                        <strong>Student ID:</strong> <?php echo htmlspecialchars($student_id); ?>
                    </div>
                    <?php if ($email): ?>
                    <div class="profile-detail-item">
                        <strong>Email:</strong> <?php echo htmlspecialchars($email); ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($phone): ?>
                    <div class="profile-detail-item">
                        <strong>Phone:</strong> <?php echo htmlspecialchars($phone); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Form Card -->
            <div class="form-card">
                <form action="update_profile.php" method="POST" enctype="multipart/form-data">
                    <h2 class="form-section-title">Personal Information</h2>
                    
                    <div class="form-group">
                        <label for="student_id">Student ID</label>
                        <input type="text" id="student_id" name="student_id" value="<?php echo htmlspecialchars($student_id); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label for="fullname">Full Name</label>
                        <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($fullname); ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="bio">Bio / Description</label>
                        <textarea id="bio" name="bio" placeholder="Tell us about yourself..."><?php echo htmlspecialchars($bio); ?></textarea>
                    </div>

                    <h2 class="form-section-title" style="margin-top: 32px;">Skills & Interests</h2>

                    <div class="form-group">
                        <label for="skills">Skills</label>
                        <input type="text" id="skills" name="skills" value="<?php echo htmlspecialchars($skills); ?>" placeholder="e.g., PHP, JavaScript, Python, HTML/CSS">
                        <small style="color: var(--text-soft); font-size: 12px; display: block; margin-top: 5px;">Separate multiple skills with commas</small>
                    </div>

                    <div class="form-group">
                        <label for="interests">Interests</label>
                        <input type="text" id="interests" name="interests" value="<?php echo htmlspecialchars($interests); ?>" placeholder="e.g., Web Development, Mobile Apps, UI/UX Design">
                        <small style="color: var(--text-soft); font-size: 12px; display: block; margin-top: 5px;">Separate multiple interests with commas</small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="main.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Theme Toggle Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('themeToggle');
            const html = document.documentElement;
            
            // Get saved theme or default to dark
            const savedTheme = localStorage.getItem('theme') || 'dark';
            html.setAttribute('data-theme', savedTheme);
            
            // Theme toggle handler
            if (themeToggle) {
                themeToggle.addEventListener('click', function() {
                    const currentTheme = html.getAttribute('data-theme');
                    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                    html.setAttribute('data-theme', newTheme);
                    localStorage.setItem('theme', newTheme);
                });
            }
        });

        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const mainNav = document.getElementById('mainNav');
            const navLinks = document.querySelectorAll('nav a');

            if (mobileMenuToggle && mainNav) {
                mobileMenuToggle.addEventListener('click', function() {
                    mobileMenuToggle.classList.toggle('active');
                    mainNav.classList.toggle('active');
                });

                // Close menu when clicking on a link
                navLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        mobileMenuToggle.classList.remove('active');
                        mainNav.classList.remove('active');
                    });
                });

                // Close menu when clicking outside
                document.addEventListener('click', function(event) {
                    const isClickInsideNav = mainNav.contains(event.target);
                    const isClickOnToggle = mobileMenuToggle.contains(event.target);
                    
                    if (!isClickInsideNav && !isClickOnToggle && mainNav.classList.contains('active')) {
                        mobileMenuToggle.classList.remove('active');
                        mainNav.classList.remove('active');
                    }
                });
            }

            // Dropdown menu toggle
            const dropdownToggle = document.getElementById('userDropdown');
            const dropdownMenu = document.getElementById('dropdownMenu');

            if (dropdownToggle && dropdownMenu) {
                dropdownToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdownMenu.classList.toggle('show');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(event) {
                    const isClickInsideDropdown = dropdownMenu.contains(event.target);
                    const isClickOnToggle = dropdownToggle.contains(event.target);
                    
                    if (!isClickInsideDropdown && !isClickOnToggle && dropdownMenu.classList.contains('show')) {
                        dropdownMenu.classList.remove('show');
                    }
                });

                // Close dropdown when clicking on a dropdown item
                const dropdownItems = dropdownMenu.querySelectorAll('.dropdown-item');
                dropdownItems.forEach(item => {
                    item.addEventListener('click', function() {
                        dropdownMenu.classList.remove('show');
                    });
                });
            }
        });

        // Parallax Cloud Scrolling Effect
        document.addEventListener('DOMContentLoaded', function() {
            const clouds = document.querySelectorAll('.parallax-cloud');
            let ticking = false;

            function updateParallaxClouds() {
                const scrollY = window.pageYOffset;
                const windowHeight = window.innerHeight;
                const documentHeight = document.documentElement.scrollHeight;
                const scrollProgress = scrollY / (documentHeight - windowHeight);

                clouds.forEach((cloud, index) => {
                    // Different parallax speeds for different layers
                    let parallaxSpeed = 0;
                    const layer = cloud.className.match(/cloud-layer-(\d)/);
                    
                    if (layer) {
                        const layerNum = parseInt(layer[1]);
                        // Layer 1 & 4: slowest (0.3)
                        // Layer 2 & 5: medium (0.5)
                        // Layer 3 & 6: fastest (0.7)
                        if (layerNum === 1 || layerNum === 4) {
                            parallaxSpeed = 0.3;
                        } else if (layerNum === 2 || layerNum === 5) {
                            parallaxSpeed = 0.5;
                        } else {
                            parallaxSpeed = 0.7;
                        }
                    }

                    // Calculate vertical offset based on scroll
                    const verticalOffset = scrollY * parallaxSpeed;
                    
                    // Calculate horizontal drift based on scroll progress
                    const horizontalDrift = scrollProgress * 100;
                    
                    // Apply transform with parallax effect
                    cloud.style.transform = `translateY(${-verticalOffset}px) translateX(${horizontalDrift}px)`;
                    
                    // Adjust opacity based on scroll position for depth effect
                    const opacityBase = 0.5;
                    const opacityVariation = Math.sin(scrollProgress * Math.PI * 2) * 0.1;
                    cloud.style.opacity = opacityBase + opacityVariation;
                });

                ticking = false;
            }

            function requestParallaxUpdate() {
                if (!ticking) {
                    window.requestAnimationFrame(updateParallaxClouds);
                    ticking = true;
                }
            }

            // Update on scroll
            window.addEventListener('scroll', requestParallaxUpdate, { passive: true });
            
            // Initial update
            updateParallaxClouds();

            // Update on resize
            window.addEventListener('resize', updateParallaxClouds);
        });

        // Preview profile image when selected
        document.getElementById('profilePictureInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const wrapper = document.querySelector('.profile-card .profile-picture-wrapper');
                    wrapper.innerHTML = '<img src="' + e.target.result + '" alt="Profile Picture" class="profile-picture">';
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
