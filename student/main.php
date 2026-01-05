<?php
session_start();

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../functions/db/database.php';

// Get session data from login
$student_id = $_SESSION['student_id'] ?? '';
$user_role = $_SESSION['user_role'] ?? 'student';
$session_fullname = $_SESSION['fullname'] ?? '';
$logged_in = $_SESSION['logged_in'] ?? false;

// Fetch student data
try {
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT * FROM students WHERE student_id = :student_id LIMIT 1');
    $stmt->execute([':student_id' => $student_id]);
    $student = $stmt->fetch();
    
    if (!$student) {
        $student = [];
    }
    
    // Set default values - use database first, then session fallback where available
    // Session data from login: student_id, user_role, fullname, logged_in
    $fullname = $student['fullname'] ?? $session_fullname ?? 'Student';
    $first_name = explode(' ', $fullname)[0];
    $email = $student['email'] ?? '';
    $phone = $student['phone'] ?? '';
    $bio = $student['bio'] ?? $student['description'] ?? 'Yet bed any for travelling assistance indulgence unpleasing. Not thoughts all exercise blessing. Indulgence way everything joy alteration boisterous the attachment.';
    $profile_picture = $student['profile_picture'] ?? '';
    $skills = $student['skills'] ?? '';
    $interests = $student['interests'] ?? '';
    $created_at = $student['created_at'] ?? '';
    $updated_at = $student['updated_at'] ?? '';
    
    // Ensure student_id from session is used (already set at line 12)
    // $student_id is already set from $_SESSION['student_id'] at the top
    
    // Determine profile picture path for hero section - only use uploaded image
    $hero_profile_img_path = '';
    if ($profile_picture && file_exists(__DIR__ . '/uploads/' . $profile_picture)) {
        $hero_profile_img_path = 'uploads/' . $profile_picture;
    }
    
    // Pagination setup
    $projects_per_page = 9;
    $current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($current_page - 1) * $projects_per_page;
    
    // Fetch projects for the student
    $projects = [];
    $all_projects = []; // For project highlight carousel - all projects
    $total_projects = 0; // Total count for pagination
    try {
        // Check if projects table exists
        $tableCheck = $pdo->query("SHOW TABLES LIKE 'projects'");
        if ($tableCheck->rowCount() > 0) {
            // Try to query projects with student_id column first
            // If that doesn't work, try junction table student_projects
            // Otherwise, query all published projects
            $stmt = null;
            $countStmt = null;
            $allProjectsStmt = null;
            
            try {
                // Check if projects table has student_id column
                $checkStmt = $pdo->query("SHOW COLUMNS FROM projects LIKE 'student_id'");
                if ($checkStmt->rowCount() > 0) {
                    // Count total projects for pagination
                    $countStmt = $pdo->prepare('SELECT COUNT(*) as total FROM projects WHERE student_id = :student_id AND is_published = 1');
                    $countStmt->execute([':student_id' => $student_id]);
                    $total_result = $countStmt->fetch();
                    $total_projects = $total_result['total'] ?? 0;
                    
                    // Query with student_id column - paginated for recent projects section
                    $stmt = $pdo->prepare('SELECT * FROM projects WHERE student_id = :student_id AND is_published = 1 ORDER BY created_at DESC LIMIT :limit OFFSET :offset');
                    $stmt->bindValue(':student_id', $student_id, PDO::PARAM_INT);
                    $stmt->bindValue(':limit', $projects_per_page, PDO::PARAM_INT);
                    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                    $stmt->execute();
                    
                    // Query featured projects for highlight carousel
                    $allProjectsStmt = $pdo->prepare('SELECT * FROM projects WHERE student_id = :student_id AND is_published = 1 AND is_featured = 1 ORDER BY created_at DESC LIMIT 10');
                    $allProjectsStmt->execute([':student_id' => $student_id]);
                } else {
                    // Check if there's a junction table
                    $checkJunction = $pdo->query("SHOW TABLES LIKE 'student_projects'");
                    if ($checkJunction->rowCount() > 0) {
                        // Count total projects for pagination
                        $countStmt = $pdo->prepare('SELECT COUNT(*) as total FROM projects p 
                            INNER JOIN student_projects sp ON p.project_id = sp.project_id 
                            WHERE sp.student_id = :student_id AND p.is_published = 1');
                        $countStmt->execute([':student_id' => $student_id]);
                        $total_result = $countStmt->fetch();
                        $total_projects = $total_result['total'] ?? 0;
                        
                        // Query using junction table - paginated for recent projects section
                        $stmt = $pdo->prepare('SELECT p.* FROM projects p 
                            INNER JOIN student_projects sp ON p.project_id = sp.project_id 
                            WHERE sp.student_id = :student_id AND p.is_published = 1 
                            ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset');
                        $stmt->bindValue(':student_id', $student_id, PDO::PARAM_INT);
                        $stmt->bindValue(':limit', $projects_per_page, PDO::PARAM_INT);
                        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                        $stmt->execute();
                        
                        // Query featured projects for highlight carousel
                        $allProjectsStmt = $pdo->prepare('SELECT p.* FROM projects p 
                            INNER JOIN student_projects sp ON p.project_id = sp.project_id 
                            WHERE sp.student_id = :student_id AND p.is_published = 1 AND p.is_featured = 1 
                            ORDER BY p.created_at DESC LIMIT 10');
                        $allProjectsStmt->execute([':student_id' => $student_id]);
                    } else {
                        // Count total projects for pagination
                        $countStmt = $pdo->query('SELECT COUNT(*) as total FROM projects WHERE is_published = 1');
                        $total_result = $countStmt->fetch();
                        $total_projects = $total_result['total'] ?? 0;
                        
                        // Fallback: query all published projects (if no relationship exists) - paginated
                        $stmt = $pdo->prepare('SELECT * FROM projects WHERE is_published = 1 ORDER BY created_at DESC LIMIT :limit OFFSET :offset');
                        $stmt->bindValue(':limit', $projects_per_page, PDO::PARAM_INT);
                        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                        $stmt->execute();
                        
                        // Query featured projects for highlight carousel
                        $allProjectsStmt = $pdo->prepare('SELECT * FROM projects WHERE is_published = 1 AND is_featured = 1 ORDER BY created_at DESC LIMIT 10');
                        $allProjectsStmt->execute();
                    }
                }
                
                if ($stmt) {
                    $projects = $stmt->fetchAll();
                }
                if ($allProjectsStmt) {
                    $all_projects = $allProjectsStmt->fetchAll();
                }
            } catch (Exception $queryError) {
                // If query fails, use empty array
                $projects = [];
                $all_projects = [];
                $total_projects = 0;
            }
        }
    } catch (Exception $e) {
        // If projects table doesn't exist or query fails, use empty array
        $projects = [];
        $all_projects = [];
        $total_projects = 0;
    }
    
    // Calculate total pages
    $total_pages = ceil($total_projects / $projects_per_page);
    
} catch (Exception $e) {
    // On error, fallback to session data if available
    $fullname = $session_fullname ?? 'Student';
    $first_name = explode(' ', $fullname)[0];
    $email = '';
    $phone = '';
    $bio = 'Yet bed any for travelling assistance indulgence unpleasing. Not thoughts all exercise blessing. Indulgence way everything joy alteration boisterous the attachment.';
    $hero_profile_img_path = '';
    $skills = '';
    $interests = '';
    $created_at = '';
    $updated_at = '';
    $projects = [];
    $all_projects = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($fullname); ?> - Portfolio</title>
    <link href="../images/isss.png" rel="icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/typed.js@2.0.12"></script>
    <script type="module">
        import { removeBackground } from 'https://cdn.jsdelivr.net/npm/@imgly/background-removal@1.0.0/dist/index.js';
        
        document.addEventListener('DOMContentLoaded', function() {
            const heroImg = document.querySelector('.hero-image img');
            if (heroImg && heroImg.src) {
                // Process image to remove background
                removeBackground(heroImg.src)
                    .then((blob) => {
                        const url = URL.createObjectURL(blob);
                        heroImg.src = url;
                        heroImg.style.backgroundColor = 'transparent';
                    })
                    .catch((error) => {
                        console.log('Background removal failed, using original image:', error);
                    });
            }
        });
    </script>
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

        [data-theme="light"] .nav {
            border-color: rgba(148, 163, 184, 0.4);
            background: radial-gradient(circle at top left, rgba(255, 255, 255, 0.95), rgba(248, 250, 252, 0.9));
            backdrop-filter: blur(20px);
        }

        [data-theme="light"] .btn-ghost {
            border-color: rgba(148, 163, 184, 0.4);
            background: radial-gradient(circle at top left, rgba(255, 255, 255, 0.95), rgba(248, 250, 252, 0.9));
            color: #1a1a1a;
        }

        [data-theme="light"] .btn-ghost:hover {
            border-color: rgba(148, 163, 184, 0.6);
            background: rgba(148, 163, 184, 0.1);
            color: #1a1a1a;
        }

        [data-theme="light"] .dropdown-toggle {
            border-color: rgba(148, 163, 184, 0.4);
            background: radial-gradient(circle at top left, rgba(255, 255, 255, 0.95), rgba(248, 250, 252, 0.9));
            color: #1a1a1a;
        }

        [data-theme="light"] .dropdown-menu {
            background: radial-gradient(circle at top left, rgba(219, 234, 254, 0.95), rgba(191, 219, 254, 0.98));
            backdrop-filter: blur(20px);
            border-color: rgba(148, 163, 184, 0.3);
        }

        [data-theme="light"] .dropdown-item {
            color: #1a1a1a;
        }

        [data-theme="light"] .dropdown-item:hover {
            background: rgba(148, 163, 184, 0.1);
            color: #1a1a1a;
        }

        [data-theme="light"] .nav-links a {
            color: #000000;
        }

        [data-theme="light"] .nav-links a:hover {
            color: #1a1a1a;
            border-color: rgba(148, 163, 184, 0.6);
            background: rgba(148, 163, 184, 0.1);
        }

        [data-theme="light"] .logo-text-main .letter {
            color: #1a1a1a;
        }

        [data-theme="light"] .logo-dot {
            color: #16a34a;
        }

        [data-theme="light"] .project-card {
            background: #ffffff;
            border-color: rgba(148, 163, 184, 0.35);
        }

        [data-theme="light"] .project-card:hover {
            box-shadow: 0 28px 80px rgba(0, 0, 0, 0.15);
        }

        [data-theme="light"] .dropdown-menu {
            background: radial-gradient(circle at top left, rgba(219, 234, 254, 0.95), rgba(191, 219, 254, 0.98));
            backdrop-filter: blur(20px);
            border-color: rgba(148, 163, 184, 0.3);
        }

        [data-theme="light"] .stat-box {
            background: radial-gradient(circle at top left, rgba(34, 197, 94, 0.1), rgba(255, 255, 255, 0.95));
            border-color: rgba(148, 163, 184, 0.35);
        }

        [data-theme="light"] .project-highlight-content-wrapper {
            background: linear-gradient(135deg, rgba(219, 234, 254, 0.92), rgba(191, 219, 254, 0.95));
            backdrop-filter: blur(20px);
            border-color: rgba(148, 163, 184, 0.3);
        }

        [data-theme="light"] .footer {
            border-top-color: rgba(148, 163, 184, 0.2);
        }

        [data-theme="light"] .hero-label {
            background: radial-gradient(circle at top left, rgba(34, 197, 94, 0.12), rgba(224, 242, 254, 0.85));
            border-color: rgba(34, 197, 94, 0.35);
        }

        [data-theme="light"] .section-label {
            background: radial-gradient(circle at top left, rgba(34, 197, 94, 0.12), rgba(224, 242, 254, 0.85));
            border-color: rgba(34, 197, 94, 0.35);
        }

        [data-theme="light"] .pagination-number {
            background: radial-gradient(circle at top left, rgba(255, 255, 255, 0.85), rgba(224, 242, 254, 0.9));
            border-color: rgba(148, 163, 184, 0.3);
        }

        [data-theme="light"] .pagination-btn {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(14, 165, 233, 0.1));
            border-color: rgba(148, 163, 184, 0.3);
        }

        [data-theme="light"] .hero::before,
        [data-theme="light"] .hero::after {
            opacity: 0.3;
        }

        /* Make clouds more visible in light mode */
        [data-theme="light"] .parallax-cloud {
            opacity: 0.6 !important;
        }

        [data-theme="light"] .parallax-cloud img {
            filter: blur(0px) brightness(1.1);
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

        /* Light Mode Text Colors - Ensure All Text is Visible */
        [data-theme="light"] .hero-title {
            background: linear-gradient(135deg, #000000 0%, #1a1a1a 100%) !important;
            -webkit-background-clip: text !important;
            background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
        }

        [data-theme="light"] .hero-title .highlight {
            background: linear-gradient(135deg, #16a34a 0%, #0284c7 50%, #7c3aed 100%) !important;
            -webkit-background-clip: text !important;
            background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
        }

        [data-theme="light"] .section-title {
            color: #000000 !important;
        }

        [data-theme="light"] .section-title .highlight {
            background: linear-gradient(135deg, #16a34a, #0284c7) !important;
            -webkit-background-clip: text !important;
            background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
        }

        [data-theme="light"] .hero-description,
        [data-theme="light"] .section-description,
        [data-theme="light"] .project-title,
        [data-theme="light"] .project-subtitle,
        [data-theme="light"] .project-highlight-title,
        [data-theme="light"] .project-highlight-description,
        [data-theme="light"] .footer-title,
        [data-theme="light"] .footer-description,
        [data-theme="light"] .footer-link,
        [data-theme="light"] .footer-copyright,
        [data-theme="light"] .info-value,
        [data-theme="light"] .stat-label,
        [data-theme="light"] .pagination-info {
            color: #000000 !important;
        }

        [data-theme="light"] .hero-label,
        [data-theme="light"] .section-label,
        [data-theme="light"] .info-label,
        [data-theme="light"] .project-highlight-badge {
            color: #000000 !important;
        }

        [data-theme="light"] nav ul li a,
        [data-theme="light"] .dropdown-item,
        [data-theme="light"] .footer-bottom-link {
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

        [data-theme="light"] .dropdown-item:hover,
        [data-theme="light"] .footer-link:hover,
        [data-theme="light"] .footer-bottom-link:hover {
            color: #16a34a !important;
        }

        [data-theme="light"] .project-tag,
        [data-theme="light"] .project-highlight-tag,
        [data-theme="light"] .info-tag {
            color: #000000 !important;
            background: rgba(255, 255, 255, 0.9) !important;
            border-color: rgba(0, 0, 0, 0.2) !important;
        }

        [data-theme="light"] .pagination-number {
            color: #000000 !important;
        }

        [data-theme="light"] .pagination-number:hover {
            color: #000000 !important;
        }

        [data-theme="light"] .pagination-number.active {
            color: #ffffff !important;
        }

        [data-theme="light"] .pagination-btn {
            color: #000000 !important;
        }

        [data-theme="light"] .pagination-btn:hover:not(.disabled) {
            color: #000000 !important;
        }

        [data-theme="light"] .pagination-ellipsis {
            color: #000000 !important;
        }

        [data-theme="light"] .logo,
        [data-theme="light"] .footer-logo {
            color: #000000 !important;
        }

        [data-theme="light"] .cta-button {
            color: #ffffff !important;
        }

        [data-theme="light"] .social-icon,
        [data-theme="light"] .footer-social-icon {
            color: #ffffff !important;
            background: linear-gradient(135deg, #16a34a, #0284c7) !important;
            border: 2px solid rgba(22, 163, 74, 0.8) !important;
            box-shadow: 0 4px 15px rgba(22, 163, 74, 0.3) !important;
        }

        [data-theme="light"] .social-icon:hover,
        [data-theme="light"] .footer-social-icon:hover {
            color: #ffffff !important;
            background: linear-gradient(135deg, #22c55e, #0ea5e9) !important;
            border-color: rgba(22, 163, 74, 1) !important;
            box-shadow: 0 8px 25px rgba(22, 163, 74, 0.5) !important;
            transform: translateY(-4px) scale(1.1) !important;
        }

        [data-theme="light"] .btn-primary {
            color: #ffffff !important;
            background: linear-gradient(135deg, #16a34a, #0284c7) !important;
            border: 2px solid rgba(22, 163, 74, 0.8) !important;
            box-shadow: 0 4px 20px rgba(22, 163, 74, 0.4), 0 0 30px rgba(2, 132, 199, 0.3) !important;
        }

        [data-theme="light"] .btn-primary:hover {
            background: linear-gradient(135deg, #22c55e, #0ea5e9) !important;
            border-color: rgba(22, 163, 74, 1) !important;
            box-shadow: 0 8px 30px rgba(22, 163, 74, 0.5), 0 0 40px rgba(2, 132, 199, 0.4) !important;
        }

        [data-theme="light"] .project-link {
            color: #ffffff !important;
        }

        [data-theme="light"] .stat-number {
            color: #000000 !important;
            -webkit-background-clip: unset !important;
            background-clip: unset !important;
            background: none !important;
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
            to {
                transform: rotate(360deg);
            }
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
            justify-content: center;
            flex: 1;
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

        .nav-links a.active {
            color: var(--accent-strong);
            border-color: rgba(34, 197, 94, 0.5);
            background: rgba(15, 23, 42, 0.8);
        }

        .nav-cta {
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
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

        .btn-ghost {
            background: radial-gradient(circle at top left, rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 1));
        }

        .user-name {
            color: var(--text-main);
            font-size: 14px;
            font-weight: 500;
            padding: 10px 18px;
            border-radius: var(--radius-pill);
            border: 1px solid rgba(148, 163, 184, 0.3);
            background: radial-gradient(circle at top left, rgba(15, 23, 42, 0.95), rgba(15, 23, 42, 0.98));
            display: inline-flex;
            align-items: center;
            transition: color 0.2s ease, border-color 0.2s ease, background 0.2s ease;
        }

        .user-name:hover {
            color: var(--accent-strong);
            border-color: rgba(34, 197, 94, 0.5);
            background: rgba(15, 23, 42, 0.8);
        }

        [data-theme="light"] .user-name {
            color: #1a1a1a;
            background: radial-gradient(circle at top left, rgba(255, 255, 255, 0.95), rgba(248, 250, 252, 0.9));
            border-color: rgba(148, 163, 184, 0.4);
        }

        [data-theme="light"] .user-name:hover {
            color: #16a34a;
            border-color: rgba(148, 163, 184, 0.6);
            background: rgba(148, 163, 184, 0.1);
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


        .cta-button {
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


        /* Hero Section */
        .hero {
            margin-top: 100px;
            padding: 100px 80px;
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 80px;
            align-items: start;
            min-height: calc(100vh - 100px);
            position: relative;
            max-width: 1400px;
            margin-left: auto;
            margin-right: auto;
            width: 100%;
            box-sizing: border-box;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -100px;
            left: -100px;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(34, 197, 94, 0.1), transparent 70%);
            border-radius: 50%;
            filter: blur(80px);
            z-index: -1;
            animation: float 6s ease-in-out infinite;
        }

        .hero::after {
            content: '';
            position: absolute;
            bottom: -100px;
            right: -100px;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.1), transparent 70%);
            border-radius: 50%;
            filter: blur(80px);
            z-index: -1;
            animation: float 8s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px) scale(1);
            }
            50% {
                transform: translateY(-30px) scale(1.1);
            }
        }

        .hero-content {
            display: flex;
            flex-direction: column;
            gap: 35px;
            margin-top: 0;
            animation: fadeInUp 0.8s ease-out;
            width: 100%;
            max-width: 100%;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-label {
            color: var(--accent-strong);
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            padding: 8px 16px;
            border-radius: var(--radius-pill);
            border: 1px solid rgba(34, 197, 94, 0.45);
            background: radial-gradient(circle at top left, rgba(34, 197, 94, 0.17), rgba(15, 23, 42, 0.9));
            display: inline-flex;
            align-items: center;
            gap: 6px;
            width: fit-content;
            max-width: 100%;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .hero-title {
            font-size: 60px;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -0.04em;
            min-height: 1.2em;
            background: linear-gradient(135deg, #e5e7eb 0%, #9ca3af 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            position: relative;
            width: 100%;
            overflow-wrap: break-word;
            word-wrap: break-word;
            hyphens: auto;
            margin-top: 20px;
        }

        .hero-title .highlight {
            background: linear-gradient(135deg, #22c55e 0%, #0ea5e9 50%, #a855f7 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            background-size: 200% 200%;
            animation: gradientShift 3s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
        }

        .typed-cursor {
            opacity: 1;
            animation: typedjsBlink 0.7s infinite;
            color: var(--accent-strong);
            font-weight: 400;
        }

        @keyframes typedjsBlink {
            0%, 50% {
                opacity: 1;
            }
            51%, 100% {
                opacity: 0;
            }
        }

        .hero-description {
            color: var(--text-soft);
            font-size: 18px;
            line-height: 1.9;
            max-width: 550px;
            font-weight: 400;
            letter-spacing: 0.01em;
            width: 100%;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }

        .hero-info-section {
            margin-top: 25px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .info-label {
            color: var(--accent-strong);
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .info-value {
            color: var(--text-main);
            font-size: 14px;
            line-height: 1.6;
        }

        .info-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .info-tag {
            padding: 4px 12px;
            border-radius: var(--radius-pill);
            background: radial-gradient(circle at top left, rgba(34, 197, 94, 0.15), rgba(15, 23, 42, 0.9));
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: var(--text-main);
            font-size: 12px;
            font-weight: 500;
        }

        .hero-actions {
            display: flex;
            gap: 24px;
            align-items: center;
            margin-top: 10px;
            flex-wrap: wrap;
            width: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(14, 165, 233, 0.1));
            border: 2px solid rgba(34, 197, 94, 0.4);
            color: var(--text-main);
            padding: 14px 28px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            letter-spacing: 0.5px;
            transition: all var(--transition-slow);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(34, 197, 94, 0.1);
            will-change: transform, box-shadow, border-color, background;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(14, 165, 233, 0.2));
            transition: left 0.8s var(--ease-smooth);
            z-index: -1;
        }

        .btn-primary:hover::before {
            left: 0;
        }

        .btn-primary:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 30px rgba(34, 197, 94, 0.3);
            border-color: rgba(34, 197, 94, 0.7);
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.15), rgba(14, 165, 233, 0.15));
        }

        .btn-primary:active {
            transform: translateY(-1px) scale(1.01);
        }

        .social-icons {
            display: flex;
            gap: 16px;
        }

        .social-icon {
            width: 52px;
            height: 52px;
            border: 2px solid rgba(148, 163, 184, 0.2);
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.95));
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-main);
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all var(--transition-slow);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            will-change: transform, border-color, box-shadow;
        }

        .social-icon::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.3), rgba(14, 165, 233, 0.3));
            transform: translate(-50%, -50%);
            transition: width 0.8s var(--ease-smooth), height 0.8s var(--ease-smooth);
            z-index: -1;
        }

        .social-icon:hover::before {
            width: 100%;
            height: 100%;
        }

        .social-icon:hover {
            border-color: rgba(34, 197, 94, 0.6);
            color: var(--accent-strong);
            transform: translateY(-4px) scale(1.1);
            box-shadow: 0 8px 25px rgba(34, 197, 94, 0.3);
        }

        .hero-image {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            animation: fadeInRight 0.8s ease-out 0.2s both;
            width: 100%;
            max-width: 100%;
            margin-top: -120px;
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .hero-image::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 500px;
            border-radius: 25px;
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(14, 165, 233, 0.1));
            z-index: -1;
            opacity: 0.5;
            transform: scale(1);
            animation: pulseGlow 3s ease-in-out infinite;
        }

        @keyframes pulseGlow {
            0%, 100% {
                opacity: 0.5;
                transform: scale(1);
            }
            50% {
                opacity: 0.8;
                transform: scale(1.1);
            }
        }

        .hero-image img {
            width: 360px;
            height: 460px;
            max-width: 100%;
            max-height: 100%;
            border-radius: 20px;
            object-fit: cover;
            border: 5px solid transparent;
            background: linear-gradient(var(--bg-main), var(--bg-main)) padding-box,
                        linear-gradient(135deg, var(--accent), var(--accent-alt), var(--accent)) border-box;
            background-clip: padding-box, border-box;
            background-size: 200% 200%;
            position: relative;
            transition: all 0.8s var(--ease-smooth);
            background-color: transparent;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5),
                        0 0 80px rgba(34, 197, 94, 0.2);
            animation: borderRotate 4s linear infinite;
            will-change: transform, box-shadow;
        }

        @keyframes borderRotate {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        .hero-image img:hover {
            transform: scale(1.08) rotate(2deg);
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.6),
                        0 0 100px rgba(34, 197, 94, 0.4);
        }

        .hero-image::before {
            display: none;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 0.6;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
        }

        .hero-bg-shapes {
            display: none;
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

        /* Ensure content is above clouds */
        .hero,
        .project-highlight,
        .projects,
        .footer {
            position: relative;
            z-index: 1;
        }

        .section-label {
            color: var(--accent-strong);
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            padding: 8px 16px;
            border-radius: var(--radius-pill);
            border: 1px solid rgba(34, 197, 94, 0.45);
            background: radial-gradient(circle at top left, rgba(34, 197, 94, 0.17), rgba(15, 23, 42, 0.9));
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .section-title {
            font-size: 64px;
            font-weight: 700;
            line-height: 1.08;
            letter-spacing: -0.03em;
        }

        .section-title .highlight {
            background: linear-gradient(135deg, #22c55e, #0ea5e9);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .section-description {
            color: var(--text-soft);
            font-size: 18px;
            line-height: 1.8;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin: 30px 0;
        }

        .stat-box {
            background: radial-gradient(circle at top left, rgba(34, 197, 94, 0.15), rgba(15, 23, 42, 1));
            border: 1px solid rgba(148, 163, 184, 0.45);
            padding: 25px;
            border-radius: var(--radius-md);
            text-align: center;
            box-shadow: 0 14px 40px rgba(15, 23, 42, 0.9);
        }

        .stat-number {
            font-size: 36px;
            font-weight: 700;
            background: linear-gradient(135deg, #22c55e, #0ea5e9);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .stat-label {
            font-size: 14px;
            color: var(--text-soft);
            margin-top: 5px;
        }

        /* Project Highlight Section - 3D Carousel Design */
        .project-highlight {
            margin-top: 80px;
            padding: 80px 80px;
            position: relative;
            overflow: visible;
            background: transparent;
            color: white;
        }

        .project-highlight-container {
            max-width: 1000px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .project-highlight-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .project-highlight-banner {
            width: 100%;
            height: 80vh;
            min-height: 600px;
            text-align: center;
            overflow: visible;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        /* 3D Slider container */
        .project-highlight-banner .project-highlight-slider {
            position: absolute;
            width: 120px;
            height: 160px;
            top: 20%;
            left: calc(50% - 60px);
            transform-style: preserve-3d;
            transform: perspective(1000px);
            animation: projectHighlightAutoRun 50s linear infinite;
            z-index: 2;
        }

        @keyframes projectHighlightAutoRun {
            from {
                transform: perspective(1000px) rotateX(-16deg) rotateY(0deg);
            }
            to {
                transform: perspective(1000px) rotateX(-16deg) rotateY(360deg);
            }
        }

        /* Pause rotation on hover */
        .project-highlight-banner .project-highlight-slider:hover {
            animation-play-state: paused;
        }

        /* Individual slider items */
        .project-highlight-banner .project-highlight-slider .project-highlight-item {
            position: absolute;
            inset: 0 0 0 0;
            transform: rotateY(calc((var(--position) - 1) * (360 / var(--quantity)) * 1deg))
                translateZ(400px);
            transition: transform 0.8s var(--ease-smooth), box-shadow 0.8s var(--ease-smooth), filter 0.8s var(--ease-smooth);
            filter: brightness(0.80);
            cursor: pointer;
        }

        /* Hover effect on each item */
        .project-highlight-banner .project-highlight-slider .project-highlight-item:hover {
            transform: rotateY(calc((var(--position) - 1) * (360 / var(--quantity)) * 1deg))
                translateZ(300px) scale(1.1);
            box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.5);
            filter: brightness(1.20);
        }

        /* Styling for images inside the slider items */
        .project-highlight-banner .project-highlight-slider .project-highlight-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.8s var(--ease-smooth);
            border-radius: 8px;
        }

        /* Hover effect for image scale */
        .project-highlight-banner .project-highlight-slider .project-highlight-item:hover img {
            transform: scale(1.05);
        }

        /* Project content overlay/info section */
        .project-highlight-content-wrapper {
            position: absolute;
            bottom: 50px;
            left: 50%;
            transform: translateX(-50%);
            width: min(90%, 700px);
            max-width: 700px;
            padding: 25px;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(15, 23, 42, 0.98));
            backdrop-filter: blur(20px);
            border: 2px solid rgba(148, 163, 184, 0.3);
            border-radius: 24px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.6);
            z-index: 3;
            text-align: center;
            display: none;
            opacity: 0;
            transition: opacity 0.5s var(--ease-smooth);
        }

        .project-highlight-content-wrapper.show {
            display: block !important;
            opacity: 1;
        }

        .project-highlight-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.15), rgba(14, 165, 233, 0.15));
            border: 1px solid rgba(34, 197, 94, 0.3);
            border-radius: 50px;
            color: var(--accent-strong);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            width: fit-content;
            margin: 0 auto 12px;
        }

        .project-highlight-badge::before {
            content: '★';
            font-size: 14px;
            color: #fbbf24;
        }

        .project-highlight-title {
            font-size: 32px;
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -0.02em;
            color: #e5e7eb;
            margin-bottom: 15px;
        }

        .project-highlight-description {
            color: var(--text-soft);
            font-size: 14px;
            line-height: 1.6;
            font-weight: 400;
            margin-bottom: 15px;
        }

        .project-highlight-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
            margin-bottom: 15px;
        }

        .project-highlight-tag {
            padding: 8px 16px;
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(14, 165, 233, 0.1));
            border: 1px solid rgba(148, 163, 184, 0.3);
            border-radius: 50px;
            color: var(--text-main);
            font-size: 13px;
            font-weight: 500;
            transition: all var(--transition-normal);
        }

        .project-highlight-tag:hover {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(14, 165, 233, 0.2));
            border-color: rgba(34, 197, 94, 0.5);
            transform: translateY(-2px);
        }

        .project-highlight-actions {
            display: flex;
            gap: 16px;
            justify-content: center;
        }

        .project-highlight-btn {
            padding: 10px 24px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 13px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all var(--transition-slow);
            letter-spacing: 0.5px;
        }

        .project-highlight-btn-primary {
            background: linear-gradient(135deg, #22c55e, #0ea5e9);
            color: var(--bg-main);
            box-shadow: 0 8px 30px rgba(34, 197, 94, 0.3);
        }

        .project-highlight-btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 40px rgba(34, 197, 94, 0.4);
        }

        .project-highlight-btn-secondary {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.95));
            border: 2px solid rgba(148, 163, 184, 0.3);
            color: var(--text-main);
            backdrop-filter: blur(10px);
        }

        .project-highlight-btn-secondary:hover {
            border-color: rgba(34, 197, 94, 0.5);
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(14, 165, 233, 0.1));
            transform: translateY(-3px);
        }

        /* Projects Section */
        .projects {
            padding: 100px 80px;
        }

        .projects-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .projects-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            margin-bottom: 40px;
        }

        .project-card {
            background: var(--bg-card);
            border: 1px solid rgba(148, 163, 184, 0.55);
            border-radius: var(--radius-md);
            overflow: hidden;
            transition: all var(--transition-slow);
            box-shadow: var(--shadow-soft);
            will-change: transform, box-shadow;
        }

        .project-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 28px 80px rgba(0, 0, 0, 0.9);
            border-color: rgba(34, 197, 94, 0.7);
        }

        .project-image-clickable {
            cursor: pointer;
            transition: opacity var(--transition-normal);
        }

        .project-image-clickable:hover {
            opacity: 0.9;
        }

        /* Project Modal Styles */
        .project-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10000;
            overflow-y: auto;
        }

        .project-modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .project-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            z-index: 10001;
        }

        .project-modal-content {
            position: relative;
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            max-width: 900px;
            width: 90%;
            max-height: 90vh;
            margin: 40px auto;
            z-index: 10002;
            box-shadow: 0 30px 100px rgba(0, 0, 0, 0.9);
            border: 1px solid var(--border-subtle);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .project-modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.5);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: #ffffff;
            font-size: 24px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10003;
            transition: all var(--transition-normal);
        }

        .project-modal-close:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.5);
            transform: rotate(90deg);
        }

        .project-modal-body {
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .project-modal-image-container {
            position: relative;
            width: 100%;
            height: 300px;
            overflow: hidden;
            background: var(--bg-card);
        }

        .project-modal-banner {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
        }

        .project-modal-banner.active {
            display: block;
        }

        .project-modal-thumbnail {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
        }

        .project-modal-thumbnail.active {
            display: block;
        }

        .project-modal-info {
            padding: 40px;
            color: var(--text-main);
        }

        .project-modal-title {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 15px;
            color: var(--text-main);
        }

        .project-modal-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .project-modal-category {
            padding: 6px 14px;
            border-radius: var(--radius-pill);
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.15), rgba(14, 165, 233, 0.15));
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: var(--accent-strong);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .project-modal-date {
            color: var(--text-soft);
            font-size: 14px;
        }

        .project-modal-technologies {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .project-modal-technologies .project-tag {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-main);
            padding: 4px 12px;
            border-radius: var(--radius-pill);
            background: rgba(15, 23, 42, 0.9);
            border: 1px solid rgba(55, 65, 81, 0.9);
        }

        [data-theme="light"] .project-modal-technologies .project-tag {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(0, 0, 0, 0.2);
            color: #000000;
        }

        .project-modal-short-description {
            font-size: 18px;
            color: var(--text-soft);
            margin-bottom: 25px;
            font-weight: 500;
            line-height: 1.6;
        }

        .project-modal-description {
            font-size: 16px;
            color: var(--text-main);
            line-height: 1.8;
            margin-bottom: 30px;
            white-space: pre-wrap;
        }

        .project-modal-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .project-modal-btn {
            padding: 12px 28px;
            border-radius: var(--radius-pill);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all var(--transition-normal);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .project-modal-btn-primary {
            background: linear-gradient(135deg, #22c55e, #0ea5e9);
            color: var(--bg-main);
            box-shadow: 0 8px 30px rgba(34, 197, 94, 0.3);
        }

        .project-modal-btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 40px rgba(34, 197, 94, 0.4);
        }

        .project-modal-btn-secondary {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.95));
            border: 2px solid rgba(148, 163, 184, 0.3);
            color: var(--text-main);
            backdrop-filter: blur(10px);
        }

        .project-modal-btn-secondary:hover {
            border-color: rgba(34, 197, 94, 0.5);
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(14, 165, 233, 0.1));
            transform: translateY(-3px);
        }

        [data-theme="light"] .project-modal-content {
            background: #ffffff;
            border-color: rgba(148, 163, 184, 0.3);
        }

        [data-theme="light"] .project-modal-title {
            color: #000000;
        }

        [data-theme="light"] .project-modal-description {
            color: #000000;
        }

        [data-theme="light"] .project-modal-short-description {
            color: #1a1a1a;
        }

        [data-theme="light"] .project-modal-date {
            color: #475569;
        }

        @media (max-width: 768px) {
            .project-modal-content {
                width: 95%;
                margin: 20px auto;
                max-height: 95vh;
            }

            .project-modal-info {
                padding: 25px;
            }

            .project-modal-title {
                font-size: 24px;
            }

            .project-modal-image-container {
                height: 200px;
            }

            .project-modal-actions {
                flex-direction: column;
            }

            .project-modal-btn {
                width: 100%;
                justify-content: center;
            }
        }

        .project-image {
            width: 100%;
            height: 180px;
            background: var(--bg-card);
            border-bottom: 1px solid rgba(148, 163, 184, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-main);
            font-size: 14px;
            overflow: hidden;
            position: relative;
            cursor: pointer;
        }

        [data-theme="light"] .project-image {
            background: #ffffff;
        }

        .project-image img {
            width: 70%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.8s var(--ease-smooth);
            margin: 0 auto;
            will-change: transform;
        }

        .project-card:hover .project-image img {
            transform: scale(1.05);
        }

        .project-content {
            padding: 25px;
            color: var(--text-main);
        }

        /* Dark mode project card text - ensure visibility on white background */
        .project-card .project-title {
            color: #1e293b;
        }

        .project-card .project-subtitle {
            color: #475569;
        }

        [data-theme="light"] .project-card .project-title {
            color: #000000;
        }

        [data-theme="light"] .project-card .project-subtitle {
            color: #1a1a1a;
        }

        .project-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .project-subtitle {
            font-size: 14px;
            color: var(--text-soft);
            margin-bottom: 15px;
        }

        .project-tags {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .project-tag {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-main);
            padding: 3px 10px;
            border-radius: var(--radius-pill);
            background: rgba(15, 23, 42, 0.9);
            border: 1px solid rgba(55, 65, 81, 0.9);
        }

        .project-link {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #22c55e, #0ea5e9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--bg-main);
            text-decoration: none;
            margin-left: auto;
            transition: all var(--transition-slow);
            box-shadow: var(--shadow-glow);
            will-change: transform;
        }

        .project-link:hover {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 0 30px rgba(34, 197, 94, 0.6);
        }

        .project-link::after {
            content: '→';
            font-size: 20px;
            font-weight: 700;
        }

        .carousel-dots {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: rgba(148, 163, 184, 0.3);
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .dot.active {
            background: radial-gradient(circle, #22c55e, #16a34a);
            box-shadow: 0 0 16px rgba(22, 163, 74, 0.9);
        }

        /* Pagination Styles */
        .pagination-wrapper {
            margin-top: 60px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        .pagination {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .pagination-btn {
            padding: 12px 24px;
            border-radius: var(--radius-pill);
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(14, 165, 233, 0.1));
            border: 2px solid rgba(148, 163, 184, 0.3);
            color: var(--text-main);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all var(--transition-slow);
            backdrop-filter: blur(10px);
            cursor: pointer;
            will-change: transform, box-shadow, border-color, background;
        }

        .pagination-btn:hover:not(.disabled) {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(14, 165, 233, 0.2));
            border-color: rgba(34, 197, 94, 0.6);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(34, 197, 94, 0.2);
        }

        .pagination-btn.disabled {
            opacity: 0.4;
            cursor: not-allowed;
            pointer-events: none;
        }

        .pagination-btn span {
            font-size: 16px;
            line-height: 1;
        }

        .pagination-numbers {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .pagination-number {
            min-width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-md);
            background: radial-gradient(circle at top left, rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.95));
            border: 1px solid rgba(148, 163, 184, 0.3);
            color: var(--text-main);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all var(--transition-slow);
            backdrop-filter: blur(10px);
        }

        .pagination-number:hover {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.15), rgba(14, 165, 233, 0.15));
            border-color: rgba(34, 197, 94, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(34, 197, 94, 0.2);
        }

        .pagination-number.active {
            background: linear-gradient(135deg, #22c55e, #0ea5e9);
            border-color: rgba(34, 197, 94, 0.7);
            color: var(--bg-main);
            box-shadow: 0 4px 20px rgba(34, 197, 94, 0.4);
            transform: scale(1.05);
        }

        .pagination-number.active:hover {
            transform: scale(1.05) translateY(-2px);
        }

        .pagination-ellipsis {
            color: var(--text-soft);
            font-size: 14px;
            padding: 0 8px;
            display: flex;
            align-items: center;
        }

        .pagination-info {
            color: var(--text-soft);
            font-size: 13px;
            text-align: center;
            margin-top: 10px;
        }

        /* Footer Section */
        .footer {
            padding: 0px 80px 0px;
            background: transparent;
            border-top: 1px solid rgba(148, 163, 184, 0.3);
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-section {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
            font-weight: 600;
            color: var(--text-main);
            letter-spacing: 0.04em;
            margin-bottom: 10px;
        }

        .footer-logo-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            overflow: hidden;
            display: grid;
            place-items: center;
        }

        .footer-logo-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .footer-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 10px;
        }

        .footer-description {
            color: var(--text-soft);
            font-size: 14px;
            line-height: 1.6;
        }

        .footer-links {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .footer-link {
            color: var(--text-soft);
            text-decoration: none;
            font-size: 14px;
            transition: all var(--transition-normal);
            display: inline-block;
        }

        .footer-link:hover {
            color: var(--accent-strong);
            transform: translateX(5px);
        }

        .footer-social {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }

        .footer-social-icon {
            width: 40px;
            height: 40px;
            border: 1px solid rgba(148, 163, 184, 0.3);
            border-radius: 50%;
            background: rgba(15, 23, 42, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-main);
            text-decoration: none;
            transition: all var(--transition-slow);
        }

        .footer-social-icon:hover {
            border-color: var(--accent-strong);
            background: rgba(34, 197, 94, 0.1);
            color: var(--accent-strong);
            transform: translateY(-3px);
        }

        .footer-bottom {
            padding-top: 10px;
            border-top: 1px solid rgba(148, 163, 184, 0.2);
            display: flex;
            justify-content: flex-end;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .footer-copyright {
            color: var(--text-soft);
            font-size: 14px;
        }

        .footer-bottom-links {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
        }

        .footer-bottom-link {
            color: var(--text-soft);
            text-decoration: none;
            font-size: 14px;
            transition: all var(--transition-normal);
        }

        .footer-bottom-link:hover {
            color: var(--accent-strong);
        }

        /* Scroll Animation Styles - Smooth Transitions */
        .scroll-fade-in {
            opacity: 0;
            transform: translate3d(0, 30px, 0);
            transition: opacity 1.2s var(--ease-smooth), 
                        transform 1.2s var(--ease-smooth);
            will-change: opacity, transform;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
        }

        .scroll-fade-in.visible {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }

        .scroll-fade-in-left {
            opacity: 0;
            transform: translate3d(-40px, 0, 0);
            transition: opacity 1.2s var(--ease-smooth), 
                        transform 1.2s var(--ease-smooth);
            will-change: opacity, transform;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
        }

        .scroll-fade-in-left.visible {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }

        .scroll-fade-in-right {
            opacity: 0;
            transform: translate3d(40px, 0, 0);
            transition: opacity 1.2s var(--ease-smooth), 
                        transform 1.2s var(--ease-smooth);
            will-change: opacity, transform;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
        }

        .scroll-fade-in-right.visible {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }

        /* Additional animation variants */
        .scroll-scale-up {
            opacity: 0;
            transform: translate3d(0, 0, 0) scale(0.9);
            transition: opacity 1.2s var(--ease-smooth), 
                        transform 1.2s var(--ease-spring);
            will-change: opacity, transform;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
        }

        .scroll-scale-up.visible {
            opacity: 1;
            transform: translate3d(0, 0, 0) scale(1);
        }

        .scroll-rotate-in {
            opacity: 0;
            transform: translate3d(0, 20px, 0) rotate(-3deg);
            transition: opacity 1.2s var(--ease-smooth), 
                        transform 1.2s var(--ease-smooth);
            will-change: opacity, transform;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
        }

        .scroll-rotate-in.visible {
            opacity: 1;
            transform: translate3d(0, 0, 0) rotate(0deg);
        }

        /* Stagger delays for smooth sequential animations */
        .scroll-delay-1 {
            transition-delay: 0.15s;
        }

        .scroll-delay-2 {
            transition-delay: 0.3s;
        }

        .scroll-delay-3 {
            transition-delay: 0.45s;
        }

        .scroll-delay-4 {
            transition-delay: 0.6s;
        }

        .scroll-delay-5 {
            transition-delay: 0.75s;
        }

        /* Smooth scroll behavior */
        html {
            scroll-behavior: smooth;
        }

        /* Performance optimizations for smooth scrolling */
        * {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Responsive text overflow handling */
        .hero-title,
        .section-title,
        .project-title,
        .project-highlight-title {
            word-wrap: break-word;
            overflow-wrap: break-word;
            hyphens: auto;
        }

        /* Ensure images scale properly */
        img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        /* Responsive container improvements */
        .project-highlight-container,
        .projects-header,
        .footer-content {
            width: 100%;
            max-width: 100%;
        }

        /* Optimize rendering for animated elements */
        .scroll-fade-in,
        .scroll-fade-in-left,
        .scroll-fade-in-right,
        .scroll-scale-up,
        .scroll-rotate-in {
            transform-style: preserve-3d;
            -webkit-transform-style: preserve-3d;
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
        /* Extra Large Screens */
        @media (min-width: 1920px) {
            .hero,
            .projects,
            .footer {
                max-width: 1800px;
                margin-left: auto;
                margin-right: auto;
            }

            .hero {
                padding: 120px 100px;
            }
        }

        @media (max-width: 1600px) {
            header {
                padding: 20px 70px;
            }

            .hero,
            .projects,
            .footer {
                padding: 90px 70px;
            }

            .hero {
                gap: 75px;
            }
        }

        @media (max-width: 1400px) {
            header {
                padding: 20px 60px;
            }

            .hero,
            .projects,
            .footer {
                padding: 80px 60px;
            }

            .hero {
                gap: 70px;
            }

            .hero-title {
                font-size: 58px;
            }

            .section-title {
                font-size: 58px;
            }
        }

        @media (max-width: 1200px) {
            header {
                padding: 20px 50px;
                grid-template-columns: auto 1fr auto;
                gap: 15px;
            }

            nav {
                order: 3;
                grid-column: 1 / -1;
                margin-top: 15px;
            }

            .header-right {
                order: 2;
            }

            .hero {
                padding: 80px 50px;
                gap: 60px;
            }

            .hero-title {
                font-size: 56px;
            }

            .section-title {
                font-size: 56px;
            }

            .hero-image::before {
                width: 400px;
                height: 400px;
            }

            .hero-image img {
                width: 380px;
                height: 380px;
            }

            .project-highlight-content-wrapper {
                width: min(90%, 650px);
            }
        }

        @media (max-width: 900px) {
            header {
                padding: 18px 30px;
            }

            .hero {
                padding: 70px 30px;
                grid-template-columns: 1fr;
                gap: 50px;
            }

            .hero-title {
                font-size: 52px;
            }

            .section-title {
                font-size: 52px;
            }

            .hero-image img {
                width: 360px;
                height: 360px;
                max-width: 90%;
                max-height: 90%;
            }

            .hero-image::before {
                width: 380px;
                height: 380px;
                max-width: 95%;
                max-height: 95%;
            }

            .projects-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 24px;
            }
        }

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

            .theme-toggle {
                width: 55px;
                height: 30px;
            }

            .theme-toggle-slider {
                width: 24px;
                height: 24px;
            }

            [data-theme="light"] .theme-toggle-slider {
                transform: translateX(25px);
            }

            nav ul {
                gap: 15px;
            }

            nav ul li a {
                font-size: 12px;
                padding: 8px 12px;
            }

            .cta-button {
                font-size: 12px;
                padding: 10px 16px;
            }

            .hero {
                margin-top: 80px;
                padding: 60px 40px;
                grid-template-columns: 1fr;
                gap: 50px;
                text-align: center;
            }

            .hero-content {
                align-items: center;
                text-align: center;
            }

            .hero-description {
                max-width: 100%;
            }

            .project-highlight,
            .projects,
            .footer {
                padding: 60px 40px;
            }

            .project-highlight {
                margin-top: 60px;
                margin-bottom: 20px;
                padding: 60px 40px;
            }

            .project-highlight-banner {
                min-height: 650px;
                height: 85vh;
            }

            .project-highlight-banner .project-highlight-slider {
                width: 100px;
                height: 140px;
                left: calc(50% - 50px);
                top: 15%;
            }

            .project-highlight-banner .project-highlight-slider .project-highlight-item {
                transform: rotateY(calc((var(--position) - 1) * (360 / var(--quantity)) * 1deg))
                    translateZ(250px);
            }

            .project-highlight-header {
                margin-bottom: 15px;
            }

            .project-highlight-content-wrapper:not(.show) {
                display: none;
            }

            .project-highlight-description {
                margin-bottom: 10px;
                font-size: 13px;
            }

            .project-highlight-tags {
                margin-bottom: 10px;
            }

            .project-highlight-tag {
                font-size: 12px;
                padding: 6px 14px;
            }

            .project-highlight-title {
                font-size: 28px;
            }

            .project-highlight-btn {
                font-size: 12px;
                padding: 9px 20px;
            }

            .hero-image::before {
                width: 340px;
                height: 340px;
                max-width: 100%;
            }

            .hero-image img {
                width: 320px;
                height: 320px;
                max-width: 90%;
                max-height: 90%;
            }

            .section-title {
                font-size: 48px;
            }

            .section-description {
                font-size: 17px;
            }

            .section-label {
                font-size: 13px;
                padding: 7px 14px;
            }

            .projects-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 25px;
            }

            .project-card {
                border-radius: 16px;
            }

            .project-title {
                font-size: 19px;
            }

            .project-subtitle {
                font-size: 13px;
            }

            .project-tag {
                font-size: 11px;
                padding: 3px 9px;
            }

            .footer-content {
                grid-template-columns: repeat(2, 1fr);
            }

            .footer-bottom {
                flex-direction: column;
                text-align: center;
            }

            .hero-label {
                font-size: 13px;
                padding: 7px 14px;
            }

            .btn-primary {
                font-size: 13px;
                padding: 12px 24px;
            }

            .stats {
                gap: 18px;
            }

            .stat-box {
                padding: 22px;
            }

            .stat-number {
                font-size: 32px;
            }

            .stat-label {
                font-size: 13px;
            }

            .pagination-btn {
                padding: 11px 22px;
                font-size: 13px;
            }

            .pagination-number {
                min-width: 42px;
                height: 42px;
                font-size: 13px;
            }
        }

        /* Responsive adjustments for parallax clouds */
        @media (max-width: 1024px) {
            .parallax-cloud {
                opacity: 0.4;
            }
            
            .cloud-layer-1,
            .cloud-layer-2,
            .cloud-layer-3,
            .cloud-layer-4,
            .cloud-layer-5,
            .cloud-layer-6 {
                width: 150px;
                height: 90px;
            }
        }

        @media (max-width: 768px) {
            .parallax-cloud {
                opacity: 0.35;
            }
            
            .cloud-layer-1,
            .cloud-layer-2,
            .cloud-layer-3,
            .cloud-layer-4,
            .cloud-layer-5,
            .cloud-layer-6 {
                width: 120px;
                height: 72px;
            }
        }

        @media (max-width: 480px) {
            .parallax-cloud {
                opacity: 0.3;
            }
            
            .cloud-layer-1,
            .cloud-layer-2,
            .cloud-layer-3,
            .cloud-layer-4,
            .cloud-layer-5,
            .cloud-layer-6 {
                width: 100px;
                height: 60px;
            }

            /* Sun responsive adjustments */
            .sun-container {
                width: 120px;
                height: 120px;
                top: 8%;
                right: -120px;
            }

            .sun-glow {
                width: 180px;
                height: 180px;
            }
        }

        /* Sun responsive adjustments */
        @media (max-width: 1024px) {
            .sun-container {
                width: 160px;
                height: 160px;
                top: 10%;
                right: -160px;
            }

            .sun-glow {
                width: 240px;
                height: 240px;
            }
        }

        @media (max-width: 768px) {
            .sun-container {
                width: 140px;
                height: 140px;
                top: 8%;
                right: -140px;
            }

            .sun-glow {
                width: 210px;
                height: 210px;
            }
        }

        @media (max-width: 768px) {
            header {
                padding: 15px 20px;
                grid-template-columns: auto 1fr auto;
                gap: 12px;
                align-items: center;
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

            .header-right {
                gap: 10px;
                flex-wrap: nowrap;
                order: 2;
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

            .header-right .dropdown {
                display: block;
            }

            .header-right .dropdown-toggle {
                white-space: nowrap;
            }

            .header-right .dropdown-menu {
                right: 0;
                left: auto;
                min-width: 180px;
            }

            .btn-primary {
                font-size: 12px;
                padding: 8px 14px;
            }

            .cta-button {
                padding: 8px 14px;
                font-size: 12px;
                white-space: nowrap;
            }

            .logo {
                font-size: 20px;
                order: 1;
            }

            .logo-icon {
                width: 35px;
                height: 35px;
            }

            .hero,
            .project-highlight,
            .projects,
            .footer {
                padding: 40px 20px;
            }

            .hero {
                margin-top: 70px;
                margin-left: 0;
                min-height: auto;
                padding-top: 60px;
                padding-bottom: 60px;
                gap: 40px;
                grid-template-columns: 1fr;
            }

            .hero-content {
                width: 100%;
                max-width: 100%;
            }

            .hero-title {
                font-size: 36px;
                line-height: 1.15;
                width: 100%;
            }

            .hero-label {
                font-size: 12px;
                padding: 8px 14px;
            }

            .hero-description {
                font-size: 15px;
                line-height: 1.7;
            }

            .hero-info-section {
                margin-top: 20px;
                gap: 18px;
            }

            .info-label {
                font-size: 11px;
            }

            .info-value {
                font-size: 13px;
            }

            .info-tag {
                font-size: 11px;
                padding: 4px 10px;
            }

            .hero-actions {
                flex-direction: column;
                width: 100%;
                gap: 20px;
            }

            .hero-actions .btn-primary {
                width: 100%;
                justify-content: center;
            }

            .social-icons {
                justify-content: center;
                width: 100%;
                gap: 12px;
            }

            .social-icon {
                width: 48px;
                height: 48px;
                font-size: 15px;
            }

            .hero-image::before {
                width: 280px;
                height: 280px;
                max-width: 90vw;
                max-height: 90vw;
            }

            .hero-image img {
                width: 260px;
                height: 260px;
                max-width: 85vw;
                max-height: 85vw;
            }

            .section-title {
                font-size: 36px;
                line-height: 1.1;
            }

            .section-label {
                font-size: 12px;
                padding: 8px 14px;
            }

            .section-description {
                font-size: 16px;
                line-height: 1.7;
            }

            .project-highlight {
                margin-top: 40px;
                margin-bottom: 15px;
                padding: 50px 20px;
            }

            .project-highlight-container {
                max-width: 100%;
            }

            .project-highlight-banner {
                min-height: 600px;
                height: 90vh;
            }

            .project-highlight-banner .project-highlight-slider {
                width: 90px;
                height: 130px;
                left: calc(50% - 45px);
                top: 12%;
            }

            .project-highlight-banner .project-highlight-slider .project-highlight-item {
                transform: rotateY(calc((var(--position) - 1) * (360 / var(--quantity)) * 1deg))
                    translateZ(200px);
            }

            .project-highlight-header {
                margin-bottom: 10px;
            }

            .project-highlight-content-wrapper:not(.show) {
                display: none;
            }

            .project-highlight-badge {
                font-size: 10px;
                padding: 5px 10px;
            }

            .project-highlight-title {
                font-size: 26px;
                margin-bottom: 12px;
            }

            .project-highlight-description {
                font-size: 13px;
                margin-bottom: 12px;
                line-height: 1.6;
            }

            .project-highlight-tags {
                margin-bottom: 12px;
                gap: 6px;
            }

            .project-highlight-tag {
                font-size: 11px;
                padding: 6px 12px;
            }

            .project-highlight-actions {
                flex-direction: column;
                width: 100%;
                gap: 12px;
            }

            .project-highlight-btn {
                width: 100%;
                justify-content: center;
                font-size: 12px;
                padding: 10px 20px;
            }

            .projects-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .project-card {
                width: 100%;
            }

            .project-image {
                height: 160px;
            }

            .project-title {
                font-size: 18px;
            }

            .project-subtitle {
                font-size: 13px;
            }

            .pagination-wrapper {
                margin-top: 40px;
            }

            .pagination {
                gap: 8px;
                flex-wrap: wrap;
            }

            .pagination-btn {
                padding: 10px 18px;
                font-size: 12px;
            }

            .pagination-number {
                min-width: 38px;
                height: 38px;
                font-size: 12px;
            }

            .pagination-info {
                font-size: 12px;
                padding: 0 10px;
            }

            .footer-content {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .footer-section {
                gap: 18px;
            }

            .footer-title {
                font-size: 17px;
            }

            .footer-description {
                font-size: 13px;
            }

            .footer-link {
                font-size: 13px;
            }

            .footer-social-icon {
                width: 38px;
                height: 38px;
            }

            .stats {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .stat-box {
                padding: 20px;
            }

            .stat-number {
                font-size: 28px;
            }

            .stat-label {
                font-size: 12px;
            }
        }

        /* Small tablets and large phones */
        @media (max-width: 600px) and (min-width: 481px) {
            .hero-title {
                font-size: 30px;
            }

            .section-title {
                font-size: 30px;
            }

            .hero-image img {
                width: 220px;
                height: 220px;
            }
        }

        @media (max-width: 600px) {
            .hero-title {
                font-size: 32px;
            }

            .section-title {
                font-size: 32px;
            }

            .hero-image img {
                width: 240px;
                height: 240px;
            }

            .hero-image::before {
                width: 260px;
                height: 260px;
            }

            .project-highlight-content-wrapper {
                padding: 20px 15px;
            }

            .project-highlight-title {
                font-size: 24px;
            }

            .pagination {
                flex-direction: column;
                align-items: stretch;
            }

            .pagination-numbers {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            header {
                padding: 12px 15px;
                grid-template-columns: auto 1fr auto;
                gap: 10px;
            }

            .logo {
                font-size: 18px;
            }

            .logo-icon {
                width: 30px;
                height: 30px;
            }

            .cta-button {
                padding: 8px 12px;
                font-size: 11px;
            }

            .hero {
                margin-top: 60px;
                padding: 40px 15px;
                gap: 30px;
            }

            .projects,
            .footer {
                padding: 30px 15px;
            }

            .project-highlight {
                margin-top: 30px;
                margin-bottom: 10px;
                padding: 40px 15px;
            }

            .project-highlight-banner {
                min-height: 550px;
                height: 85vh;
            }

            .project-highlight-banner .project-highlight-slider {
                width: 80px;
                height: 120px;
                left: calc(50% - 40px);
                top: 10%;
            }

            .project-highlight-banner .project-highlight-slider .project-highlight-item {
                transform: rotateY(calc((var(--position) - 1) * (360 / var(--quantity)) * 1deg))
                    translateZ(180px);
            }

            .project-highlight-header {
                margin-bottom: 8px;
            }

            .project-highlight-content-wrapper:not(.show) {
                display: none;
            }

            .project-highlight-title {
                font-size: 22px;
                margin-bottom: 10px;
            }

            .project-highlight-description {
                font-size: 12px;
                margin-bottom: 8px;
                line-height: 1.5;
            }

            .project-highlight-tags {
                margin-bottom: 8px;
                gap: 5px;
            }

            .project-highlight-tag {
                font-size: 10px;
                padding: 5px 10px;
            }

            .project-highlight-btn {
                font-size: 11px;
                padding: 9px 18px;
            }

            .hero-title {
                font-size: 28px;
            }

            .section-title {
                font-size: 28px;
            }

            .hero-label {
                font-size: 11px;
                padding: 5px 10px;
            }

            .hero-description {
                font-size: 14px;
            }

            .section-description {
                font-size: 15px;
            }

            .hero-image img {
                width: 200px;
                height: 200px;
            }

            .hero-image::before {
                width: 220px;
                height: 220px;
            }

            .btn-primary,
            .cta-button {
                font-size: 11px;
                padding: 8px 12px;
            }

            .social-icon {
                width: 40px;
                height: 40px;
            }

            .project-image {
                height: 150px;
            }

            .footer {
                padding: 40px 20px 20px;
            }

            .footer-content {
                gap: 25px;
            }

            .pagination-wrapper {
                margin-top: 30px;
            }

            .pagination-btn {
                font-size: 11px;
                padding: 9px 16px;
            }

            .pagination-number {
                min-width: 36px;
                height: 36px;
                font-size: 11px;
            }

            .section-label {
                font-size: 11px;
                padding: 5px 10px;
            }
        }

        /* Landscape orientation for mobile */
        @media (max-width: 768px) and (orientation: landscape) {
            .hero {
                min-height: auto;
                padding: 40px 20px;
            }

            .project-highlight-banner {
                height: 70vh;
                min-height: 500px;
            }

            .hero-image img {
                width: 220px;
                height: 220px;
            }
        }

        @media (max-width: 360px) {
            header {
                padding: 10px 12px;
            }

            .logo {
                font-size: 16px;
            }

            .logo-icon {
                width: 28px;
                height: 28px;
            }

            .cta-button {
                padding: 6px 10px;
                font-size: 10px;
            }

            .hero {
                padding: 30px 12px;
                gap: 25px;
            }

            .hero-title {
                font-size: 24px;
            }

            .section-title {
                font-size: 24px;
            }

            .hero-image img {
                width: 180px;
                height: 180px;
                max-width: 75vw;
                max-height: 75vw;
            }

            .hero-image::before {
                width: 200px;
                height: 200px;
                max-width: 80vw;
                max-height: 80vw;
            }
        }

        /* Responsive Navbar Styles */
        @media (max-width: 960px) {
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
        }

        @media (max-width: 480px) {
            .nav {
                padding-inline: 12px;
                padding-block: 5px;
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
        }

        @media (max-width: 360px) {
            .nav {
                padding-inline: 10px;
                padding-block: 4px;
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
        }

        .hero-label,
        .section-label {
            font-size: 11px;
            padding: 5px 10px;
        }

        .project-highlight-title {
            font-size: 20px;
        }

        .project-highlight-banner {
            min-height: 500px;
        }

        .project-highlight-content-wrapper:not(.show) {
            display: none;
        }

        .btn-primary,
        .cta-button,
        .project-highlight-btn {
            font-size: 10px;
            padding: 8px 14px;
        }

        .pagination-btn {
            font-size: 10px;
            padding: 8px 14px;
        }

        .pagination-number {
            min-width: 32px;
            height: 32px;
            font-size: 10px;
        }

        /* Improve touch targets for mobile */
        @media (max-width: 768px) {
            .btn-primary,
            .cta-button,
            .project-highlight-btn,
            .pagination-btn,
            .pagination-number,
            .social-icon,
            .project-link {
                min-height: 44px;
                min-width: 44px;
            }

            nav ul li a {
                min-height: 44px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
        }

        /* Floating Background Animation - Code Snippets */
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
            transform-origin: center;
            transition: opacity 0.5s ease;
        }

        /* Programming Language Icons - Both Modes */
        .code-icon {
            opacity: 0.08;
            transition: opacity 0.5s ease;
        }

        .code-icon img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            filter: brightness(0.7) contrast(1.1);
            transition: opacity 0.3s ease;
        }

        /* Light Mode - Slightly brighter code icons */
        [data-theme="light"] .code-icon {
            opacity: 0.06;
        }

        [data-theme="light"] .code-icon img {
            filter: brightness(0.9) contrast(1.05);
        }

        /* Realistic Stars - Dark Mode */
        .star {
            width: 0;
            height: 0;
            position: relative;
            opacity: 0.8;
        }

        .star::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 4px;
            height: 4px;
            background: white;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            box-shadow: 
                0 0 6px rgba(255, 255, 255, 0.8),
                0 0 12px rgba(255, 255, 255, 0.6),
                0 0 18px rgba(255, 255, 255, 0.4),
                0 0 24px rgba(255, 255, 255, 0.2);
        }

        .star::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 2px;
            height: 20px;
            background: linear-gradient(to bottom, 
                transparent 0%, 
                rgba(255, 255, 255, 0.8) 45%, 
                white 50%, 
                rgba(255, 255, 255, 0.8) 55%, 
                transparent 100%);
            transform: translate(-50%, -50%) rotate(45deg);
        }

        /* Different star sizes and variations */
        .star-1::before { width: 5px; height: 5px; }
        .star-1::after { width: 2.5px; height: 20px; }
        
        .star-2::before { width: 7px; height: 7px; }
        .star-2::after { width: 3px; height: 30px; }
        
        .star-3::before { width: 4px; height: 4px; }
        .star-3::after { width: 2px; height: 18px; }
        
        .star-4::before { width: 6px; height: 6px; }
        .star-4::after { width: 2.5px; height: 24px; }
        
        .star-5::before { width: 8px; height: 8px; }
        .star-5::after { width: 3.5px; height: 35px; }
        
        .star-6::before { width: 5px; height: 5px; }
        .star-6::after { width: 2.5px; height: 22px; }
        
        .star-7::before { width: 6px; height: 6px; }
        .star-7::after { width: 3px; height: 26px; }
        
        .star-8::before { width: 4.5px; height: 4.5px; }
        .star-8::after { width: 2px; height: 19px; }

        /* Realistic Clouds - Light Mode */
        .cloud {
            width: 100px;
            height: 40px;
            background: linear-gradient(to right, 
                rgba(255, 255, 255, 0.8) 0%, 
                rgba(255, 255, 255, 0.9) 50%, 
                rgba(255, 255, 255, 0.8) 100%);
            border-radius: 100px;
            position: relative;
            opacity: 0;
            filter: blur(1px);
        }

        .cloud::before,
        .cloud::after {
            content: '';
            position: absolute;
            background: linear-gradient(to right, 
                rgba(255, 255, 255, 0.8) 0%, 
                rgba(255, 255, 255, 0.9) 50%, 
                rgba(255, 255, 255, 0.8) 100%);
            border-radius: 100px;
        }

        .cloud::before {
            width: 50px;
            height: 50px;
            top: -25px;
            left: 10px;
        }

        .cloud::after {
            width: 60px;
            height: 40px;
            top: -15px;
            right: 10px;
        }

        /* Different cloud sizes */
        .cloud-1 { width: 120px; height: 45px; }
        .cloud-1::before { width: 60px; height: 60px; top: -30px; }
        .cloud-1::after { width: 70px; height: 45px; top: -18px; }

        .cloud-2 { width: 90px; height: 35px; }
        .cloud-2::before { width: 45px; height: 45px; top: -22px; }
        .cloud-2::after { width: 55px; height: 35px; top: -12px; }

        .cloud-3 { width: 110px; height: 42px; }
        .cloud-3::before { width: 55px; height: 55px; top: -27px; }
        .cloud-3::after { width: 65px; height: 42px; top: -16px; }

        .cloud-4 { width: 95px; height: 38px; }
        .cloud-4::before { width: 48px; height: 48px; top: -24px; }
        .cloud-4::after { width: 58px; height: 38px; top: -14px; }

        /* Realistic Sun - Light Mode */
        .sun {
            width: 80px;
            height: 80px;
            background: radial-gradient(circle, 
                #fff8dc 0%, 
                #ffd700 30%, 
                #ffb347 60%, 
                #ff8c00 100%);
            border-radius: 50%;
            position: relative;
            opacity: 0;
            box-shadow: 
                0 0 60px rgba(255, 215, 0, 0.8),
                0 0 100px rgba(255, 215, 0, 0.6),
                0 0 150px rgba(255, 215, 0, 0.4),
                inset 0 0 20px rgba(255, 255, 255, 0.5);
        }

        .sun::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100px;
            height: 100px;
            transform: translate(-50%, -50%);
            background: radial-gradient(circle, 
                transparent 30%, 
                rgba(255, 215, 0, 0.3) 50%, 
                transparent 70%);
            border-radius: 50%;
            animation: sunRays 20s linear infinite;
        }

        /* Realistic Moon - Dark Mode */
        .moon {
            width: 100px;
            height: 100px;
            background: radial-gradient(circle at 30% 30%, 
                #f4f4f4 0%, 
                #e8e8e8 20%, 
                #d0d0d0 50%, 
                #b8b8b8 80%, 
                #a0a0a0 100%);
            border-radius: 50%;
            position: relative;
            opacity: 0;
            box-shadow: 
                0 0 40px rgba(255, 255, 255, 0.5),
                0 0 80px rgba(255, 255, 255, 0.3),
                0 0 120px rgba(255, 255, 255, 0.1),
                inset -10px -10px 20px rgba(0, 0, 0, 0.2),
                inset 5px 5px 15px rgba(255, 255, 255, 0.3);
        }

        .moon::before {
            content: '';
            position: absolute;
            top: 15%;
            left: 20%;
            width: 15px;
            height: 15px;
            background: rgba(160, 160, 160, 0.7);
            border-radius: 50%;
            box-shadow: 
                20px 10px 0 5px rgba(160, 160, 160, 0.6),
                35px 25px 0 3px rgba(160, 160, 160, 0.5),
                15px 30px 0 4px rgba(160, 160, 160, 0.6),
                40px 5px 0 2px rgba(160, 160, 160, 0.4);
        }

        .moon::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 120px;
            height: 120px;
            transform: translate(-50%, -50%);
            background: radial-gradient(circle, 
                transparent 40%, 
                rgba(255, 255, 255, 0.1) 60%, 
                transparent 80%);
            border-radius: 50%;
            animation: moonGlow 8s ease-in-out infinite;
        }

        /* Dark Mode - Show Stars and Moon, Hide Clouds/Sun */
        .star {
            opacity: 0.8;
            animation: twinkle 3s ease-in-out infinite;
        }

        .moon {
            opacity: 0.9;
            animation: moonFloat 40s ease-in-out infinite;
        }

        .cloud,
        .sun {
            opacity: 0;
        }

        /* Light Mode - Show Clouds/Sun, Hide Stars and Moon */
        [data-theme="light"] .star {
            opacity: 0;
        }

        [data-theme="light"] .moon {
            opacity: 0;
        }

        [data-theme="light"] .cloud {
            opacity: 0.8;
            background: linear-gradient(to right, 
                rgba(255, 255, 255, 0.9) 0%, 
                rgba(255, 255, 255, 1) 50%, 
                rgba(255, 255, 255, 0.9) 100%);
        }

        [data-theme="light"] .cloud::before,
        [data-theme="light"] .cloud::after {
            background: linear-gradient(to right, 
                rgba(255, 255, 255, 0.9) 0%, 
                rgba(255, 255, 255, 1) 50%, 
                rgba(255, 255, 255, 0.9) 100%);
        }

        [data-theme="light"] .sun {
            opacity: 0.9;
            filter: brightness(1.2);
            animation: sunGlow 4s ease-in-out infinite;
        }

        /* Star Twinkle Animation */
        @keyframes twinkle {
            0%, 100% {
                opacity: 0.8;
                transform: scale(1);
            }
            50% {
                opacity: 1;
                transform: scale(1.2);
            }
        }

        /* Sun Rays Animation */
        @keyframes sunRays {
            0% {
                transform: translate(-50%, -50%) rotate(0deg);
            }
            100% {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }

        /* Sun Glow Animation */
        @keyframes sunGlow {
            0%, 100% {
                filter: brightness(1.2);
                transform: scale(1);
            }
            50% {
                filter: brightness(1.4);
                transform: scale(1.05);
            }
        }

        .floating-shape img {
            width: 120px;
            height: 120px;
            object-fit: contain;
            filter: brightness(0.7) contrast(1.1);
            transition: opacity 0.3s ease;
        }

        /* Positioning for Code Icons (Both Modes) */
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

        /* Positioning for Stars (Dark Mode) */
        .floating-shape:nth-child(9) {
            top: 15%;
            left: 25%;
            animation: float9 29s ease-in-out infinite;
        }

        .floating-shape:nth-child(10) {
            top: 75%;
            left: 85%;
            animation: float10 25s ease-in-out infinite;
        }

        .floating-shape:nth-child(11) {
            top: 35%;
            left: 45%;
            animation: float11 31s ease-in-out infinite;
        }

        .floating-shape:nth-child(12) {
            top: 55%;
            left: 35%;
            animation: float12 27s ease-in-out infinite;
        }

        .floating-shape:nth-child(13) {
            top: 25%;
            left: 75%;
            animation: float13 29s ease-in-out infinite;
        }

        .floating-shape:nth-child(14) {
            top: 65%;
            left: 55%;
            animation: float14 23s ease-in-out infinite;
        }

        .floating-shape:nth-child(15) {
            top: 45%;
            left: 65%;
            animation: float15 25s ease-in-out infinite;
        }

        .floating-shape:nth-child(16) {
            top: 85%;
            left: 45%;
            animation: float16 30s ease-in-out infinite;
        }

        /* Positioning for Moon (Dark Mode) */
        .floating-shape:nth-child(17) {
            top: 15%;
            left: 85%;
            animation: moonFloat 40s ease-in-out infinite;
        }

        /* Positioning for Clouds (Light Mode) */
        .floating-shape:nth-child(18) {
            top: 25%;
            left: 35%;
            animation: float18 25s ease-in-out infinite;
        }

        .floating-shape:nth-child(19) {
            top: 45%;
            left: 75%;
            animation: float19 31s ease-in-out infinite;
        }

        .floating-shape:nth-child(20) {
            top: 65%;
            left: 25%;
            animation: float20 27s ease-in-out infinite;
        }

        .floating-shape:nth-child(21) {
            top: 35%;
            left: 15%;
            animation: float21 29s ease-in-out infinite;
        }

        /* Positioning for Sun (Light Mode) */
        .floating-shape:nth-child(22) {
            top: 10%;
            left: 50%;
            animation: sunFloat 35s ease-in-out infinite;
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

        @keyframes sunFloat {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg) scale(1);
            }
            25% {
                transform: translate(100px, -30px) rotate(5deg) scale(1.05);
            }
            50% {
                transform: translate(-80px, 60px) rotate(-3deg) scale(1.1);
            }
            75% {
                transform: translate(60px, 40px) rotate(2deg) scale(1.05);
            }
        }

        @keyframes moonFloat {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg) scale(1);
            }
            25% {
                transform: translate(-80px, 40px) rotate(-2deg) scale(1.02);
            }
            50% {
                transform: translate(60px, -60px) rotate(3deg) scale(1.05);
            }
            75% {
                transform: translate(-40px, 30px) rotate(-1deg) scale(1.02);
            }
        }

        @keyframes moonGlow {
            0%, 100% {
                opacity: 0.3;
                transform: translate(-50%, -50%) scale(1);
            }
            50% {
                opacity: 0.6;
                transform: translate(-50%, -50%) scale(1.1);
            }
        }

        @keyframes float13 {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            25% {
                transform: translate(-45px, 35px) rotate(1deg);
            }
            50% {
                transform: translate(30px, -45px) rotate(-1deg);
            }
            75% {
                transform: translate(-25px, 25px) rotate(0.5deg);
            }
        }

        @keyframes float14 {
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

        @keyframes float15 {
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

        @keyframes float16 {
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

        @keyframes float18 {
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

        @keyframes float19 {
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

        @keyframes float20 {
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

        @keyframes float21 {
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

        /* Floating Button - Bottom Right */
        .floating-button {
            position: fixed;
            bottom: 50px;
            right: 50px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
        }

        .floating-button:active {
            transform: translateY(-2px) scale(1.05);
        }

        .button-icon {
            font-size: 24px;
            font-weight: bold;
            color: white;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        /* Light Mode Floating Button */
        [data-theme="light"] .floating-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }

        @media (max-width: 720px) {
            .floating-shape {
                opacity: 0.6;
            }

            .floating-shape img {
                width: 90px;
                height: 90px;
            }

            .floating-button {
                width: 50px;
                height: 50px;
                bottom: 20px;
                right: 20px;
            }

            .button-icon {
                font-size: 20px;
            }
        }

        /* Sliding Circles Menu */
        .circles-menu {
            position: fixed;
            bottom: 120px;
            right: 50px;
            z-index: 1500;
            pointer-events: none;
        }

        .circle-item {
            position: absolute;
            width: 60px;
            height: 60px;
            background: var(--bg-elevated);
            border: 2px solid #10b981;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            pointer-events: all;
            opacity: 0;
            transform: translateY(20px) scale(0.8);
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }

        .circle-item i {
            font-size: 20px;
            color: white;
        }

        /* SweetAlert Theme Integration */
        .theme-swal-popup {
            border: 2px solid var(--border-color) !important;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2) !important;
        }

        .theme-swal-title {
            color: #10b981 !important;
        }

        .theme-swal-content {
            color: #10b981 !important;
        }

        [data-theme="light"] .theme-swal-title {
            color: #000000 !important;
        }

        [data-theme="light"] .theme-swal-content {
            color: #000000 !important;
        }

        [data-theme="light"] .swal2-confirm {
            color: #000000 !important;
        }

        [data-theme="light"] .swal2-cancel {
            color: #000000 !important;
        }

        [data-theme="light"] .circle-item {
            border: 2px solid #000000;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }

        .circles-menu.active .circle-item {
            opacity: 1;
            transform: translateY(0) scale(1);
            transition: all 0.3s ease;
        }

        .circle-item:hover {
            transform: translateY(-5px) scale(1.1);
            box-shadow: 0 6px 18px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
        }

        .circle-item:active {
            transform: translateY(-2px) scale(1.05);
        }

        /* Individual Circle Positions */
        .circle-1 {
            bottom: -25px;
            right: 0;
            transition-delay: 0.1s;
        }

        .circle-2 {
            bottom: 20px;
            right: 0;
            transition-delay: 0.2s;
        }

        .circle-3 {
            bottom: 70px;
            right: 0;
            transition-delay: 0.3s;
        }

        .circle-4 {
            bottom: 120px;
            right: 0;
            transition-delay: 0.4s;
        }

        .circles-menu.active .circle-1 {
            transform: translateY(-35px) scale(1);
        }

        .circles-menu.active .circle-2 {
            transform: translateY(-70px) scale(1);
        }

        .circles-menu.active .circle-3 {
            transform: translateY(-105px) scale(1);
        }

        .circles-menu.active .circle-4 {
            transform: translateY(-140px) scale(1);
        }

        .circle-icon {
            font-size: 20px;
            margin-bottom: 2px;
        }

        .circle-label {
            font-size: 10px;
            color: white;
            font-weight: 500;
            opacity: 0;
            transform: scale(0.8);
            transition: all 0.3s ease;
            display: none;
        }

        /* Burger Button Animation */
        .floating-button.active {
            transform: rotate(45deg);
            background: linear-gradient(135deg, #f56565 0%, #ed8936 100%);
        }

        .floating-button.active .button-icon {
            transform: rotate(45deg);
        }

        @media (max-width: 720px) {
            .floating-shape {
                opacity: 0.5;
            }

            .floating-shape img {
                width: 70px;
                height: 70px;
            }
        }
    </style>
</head>
<body>
    <!-- Page Shell -->
    <div class="page-shell">
        <div class="blur-orb" aria-hidden="true"></div>
        <div class="floating-shapes" aria-hidden="true">
            <!-- Programming Language Icons - Both Modes -->
            <div class="floating-shape code-icon"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/javascript/javascript-original.svg" alt="JavaScript"></div>
            <div class="floating-shape code-icon"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/python/python-original.svg" alt="Python"></div>
            <div class="floating-shape code-icon"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/java/java-original.svg" alt="Java"></div>
            <div class="floating-shape code-icon"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/cplusplus/cplusplus-original.svg" alt="C++"></div>
            <div class="floating-shape code-icon"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/typescript/typescript-original.svg" alt="TypeScript"></div>
            <div class="floating-shape code-icon"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg" alt="PHP"></div>
            <div class="floating-shape code-icon"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/react/react-original.svg" alt="React"></div>
            <div class="floating-shape code-icon"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/nodejs/nodejs-original.svg" alt="Node.js"></div>
            
            <!-- Dark Mode Stars -->
            <div class="floating-shape star star-1"></div>
            <div class="floating-shape star star-2"></div>
            <div class="floating-shape star star-3"></div>
            <div class="floating-shape star star-4"></div>
            <div class="floating-shape star star-5"></div>
            <div class="floating-shape star star-6"></div>
            <div class="floating-shape star star-7"></div>
            <div class="floating-shape star star-8"></div>
            
            <!-- Dark Mode Moon -->
            <div class="floating-shape moon"></div>
            
            <!-- Light Mode Clouds and Sun -->
            <div class="floating-shape cloud cloud-1"></div>
            <div class="floating-shape cloud cloud-2"></div>
            <div class="floating-shape cloud cloud-3"></div>
            <div class="floating-shape cloud cloud-4"></div>
            <div class="floating-shape sun"></div>
        </div>
        <!-- Floating Button - Bottom Right -->
        <div class="floating-button" id="floatingButton">
            <span class="button-icon">☰</span>
        </div>
        
        <!-- Sliding Circles Menu -->
        <div class="circles-menu" id="circlesMenu">
            <div class="circle-item circle-1" data-action="logout">
                <i class="fa fa-sign-out"></i>
                <span class="circle-label">Logout</span>
            </div>
            <div class="circle-item circle-2" data-action="projects">
                <i class="fa fa-folder"></i>
                <span class="circle-label">Projects</span>
            </div>
            <div class="circle-item circle-3" data-action="theme">
                <i class="fa fa-moon" id="themeIcon"></i>
                <span class="circle-label">Theme</span>
            </div>
            <div class="circle-item circle-4" data-action="profile">
                <i class="fa fa-user"></i>
                <span class="circle-label">Profile</span>
            </div>
        </div>
        
        <!-- Shell Container -->
        <div class="shell-inner">
        <!-- Header -->
        <header class="nav">
        <div class="nav-left">
            <div class="logo-mark">
                <img src="../images/isss.png" alt="BSIS Logo">
            </div>
            <div class="logo-text-main">
                <span class="letter">B</span>
                <span class="letter">S</span>
                <span class="letter">I</span>
                <span class="letter">S</span>
            </div>
        </div>

        <nav class="nav-links" aria-label="Primary">
            <a href="#home" class="active">Home</a>
            <a href="#projects">Projects</a>
            <a href="#contact">Contact</a>
        </nav>

        <div class="nav-cta">
            <span class="user-name"><?php echo htmlspecialchars($fullname); ?></span>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <?php if (!empty($hero_profile_img_path)): ?>
        <div class="hero-image">
            <div class="hero-bg-shapes">
                <div class="shape shape-1"></div>
                <div class="shape shape-2"></div>
            </div>
            <img src="<?php echo htmlspecialchars($hero_profile_img_path); ?>" alt="<?php echo htmlspecialchars($fullname); ?>">
        </div>
        <?php endif; ?>
        <div class="hero-content">
            <h1 class="hero-title">
                <span id="typed-text"></span>
            </h1>
            <p class="hero-description">
                <?php echo htmlspecialchars($bio); ?>
            </p>
        </div>
    </section>

    <!-- Project Highlight Section -->
    <?php 
    // Use all_projects for carousel (only featured projects with is_featured = 1)
    $highlight_projects = $all_projects;
    $project_count = count($highlight_projects);
    // Ensure we have at least 3 projects for the carousel (minimum for good 3D effect)
    if ($project_count < 3 && $project_count > 0) {
        // Duplicate projects to reach minimum of 3
        while (count($highlight_projects) < 3) {
            $highlight_projects = array_merge($highlight_projects, $highlight_projects);
        }
        $highlight_projects = array_slice($highlight_projects, 0, 3);
        $project_count = 3;
    }
    
    if (!empty($highlight_projects) && $project_count > 0): 
        $featured_project = $highlight_projects[0]; // Get the first/most recent project as featured
        $featured_title = htmlspecialchars($featured_project['title'] ?? 'Featured Project');
        $featured_description = htmlspecialchars($featured_project['short_description'] ?? $featured_project['description'] ?? 'This is a featured project that showcases my skills and expertise.');
        $featured_category = htmlspecialchars($featured_project['category'] ?? 'Featured');
        $featured_technologies = $featured_project['technologies'] ?? '';
        $featured_thumbnail = $featured_project['thumbnail'] ?? '';
        $featured_live_url = $featured_project['live_demo_url'] ?? '';
        $featured_github_url = $featured_project['github_url'] ?? '';
        $featured_figma_url = $featured_project['figma_url'] ?? '';
        
        // Determine featured project image
        $featured_image_path = '';
        if ($featured_thumbnail && file_exists(__DIR__ . '/uploads/' . $featured_thumbnail)) {
            $featured_image_path = 'uploads/' . $featured_thumbnail;
        }
        
        // Parse technologies for tags
        $featured_tags = [];
        if (!empty($featured_technologies)) {
            $featured_tags = array_map('trim', explode(',', $featured_technologies));
        }
        if (!empty($featured_category)) {
            $featured_tags[] = $featured_category;
        }
        $featured_tags = array_unique(array_filter($featured_tags));
        
        // Determine project links
        $featured_primary_link = '#';
        $featured_primary_text = 'View Project';
        if (!empty($featured_live_url)) {
            $featured_primary_link = $featured_live_url;
            $featured_primary_text = 'Live Demo →';
        } elseif (!empty($featured_github_url)) {
            $featured_primary_link = $featured_github_url;
            $featured_primary_text = 'View Code →';
        } elseif (!empty($featured_figma_url)) {
            $featured_primary_link = $featured_figma_url;
            $featured_primary_text = 'View Design →';
        }
    ?>
    <section class="project-highlight">
        <div class="project-highlight-container">
            <div class="project-highlight-header scroll-fade-in">
                <h2 class="section-title scroll-fade-in scroll-delay-2">PROJECT HIGHLIGHT</h2>
            </div>
            <div class="project-highlight-banner">
                <div class="project-highlight-slider" style="--quantity: <?php echo $project_count; ?>">
                    <?php 
                    // Display all projects in the 3D carousel
                    foreach ($highlight_projects as $index => $project): 
                        $project_title = htmlspecialchars($project['title'] ?? 'Project');
                        $project_description = htmlspecialchars($project['short_description'] ?? $project['description'] ?? 'This is a featured project that showcases my skills and expertise.');
                        $project_category = htmlspecialchars($project['category'] ?? 'Featured');
                        $project_technologies = $project['technologies'] ?? '';
                        $project_thumbnail = $project['thumbnail'] ?? '';
                        $project_live_url = htmlspecialchars($project['live_demo_url'] ?? '');
                        $project_github_url = htmlspecialchars($project['github_url'] ?? '');
                        $project_figma_url = htmlspecialchars($project['figma_url'] ?? '');
                        
                        // Parse technologies for tags
                        $project_tags = [];
                        if (!empty($project_technologies)) {
                            $project_tags = array_map('trim', explode(',', $project_technologies));
                        }
                        if (!empty($project_category)) {
                            $project_tags[] = $project_category;
                        }
                        $project_tags = array_unique(array_filter($project_tags));
                        $project_tags_json = json_encode($project_tags);
                        
                        // Determine project links
                        $project_primary_link = '#';
                        $project_primary_text = 'View Project';
                        if (!empty($project_live_url)) {
                            $project_primary_link = $project_live_url;
                            $project_primary_text = 'Live Demo →';
                        } elseif (!empty($project_github_url)) {
                            $project_primary_link = $project_github_url;
                            $project_primary_text = 'View Code →';
                        } elseif (!empty($project_figma_url)) {
                            $project_primary_link = $project_figma_url;
                            $project_primary_text = 'View Design →';
                        }
                        
                        $project_image_path = '';
                        if ($project_thumbnail && file_exists(__DIR__ . '/uploads/' . $project_thumbnail)) {
                            $project_image_path = 'uploads/' . $project_thumbnail;
                        }
                    ?>
                        <div class="project-highlight-item" 
                             style="--position: <?php echo $index + 1; ?>"
                             data-title="<?php echo $project_title; ?>"
                             data-description="<?php echo $project_description; ?>"
                             data-tags='<?php echo $project_tags_json; ?>'
                             data-primary-link="<?php echo $project_primary_link; ?>"
                             data-primary-text="<?php echo $project_primary_text; ?>"
                             data-github-url="<?php echo $project_github_url; ?>">
                            <?php if (!empty($project_image_path)): ?>
                                <img src="<?php echo htmlspecialchars($project_image_path); ?>" alt="<?php echo $project_title; ?>">
                            <?php else: ?>
                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 12px; color: var(--text-soft); background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(14, 165, 233, 0.2)); border-radius: 8px; padding: 10px; text-align: center;">
                                    <?php echo substr($project_title, 0, 15); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="project-highlight-content-wrapper" id="projectHighlightContent">
                    <div class="project-highlight-badge">FEATURED PROJECT</div>
                    <h3 class="project-highlight-title" id="projectHighlightTitle"><?php echo $featured_title; ?></h3>
                    <p class="project-highlight-description" id="projectHighlightDescription"><?php echo $featured_description; ?></p>
                    <div class="project-highlight-tags" id="projectHighlightTags">
                        <?php if (!empty($featured_tags)): ?>
                            <?php foreach (array_slice($featured_tags, 0, 5) as $tag): ?>
                                <span class="project-highlight-tag"><?php echo htmlspecialchars($tag); ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="project-highlight-actions" id="projectHighlightActions">
                        <?php if ($featured_primary_link !== '#'): ?>
                            <a href="<?php echo htmlspecialchars($featured_primary_link); ?>" target="_blank" rel="noopener noreferrer" class="project-highlight-btn project-highlight-btn-primary" id="projectHighlightPrimaryBtn">
                                <?php echo $featured_primary_text; ?>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($featured_github_url) && $featured_github_url !== $featured_primary_link): ?>
                            <a href="<?php echo htmlspecialchars($featured_github_url); ?>" target="_blank" rel="noopener noreferrer" class="project-highlight-btn project-highlight-btn-secondary" id="projectHighlightGithubBtn">
                                GitHub →
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Projects Section -->
    <section class="projects" id="projects">
        <div class="projects-header scroll-fade-in">
            <h2 class="section-title scroll-fade-in scroll-delay-2">PROJECTS</h2>
        </div>
        <div class="projects-grid">
            <?php if (!empty($projects)): ?>
                <?php 
                $delay = 1;
                foreach ($projects as $project): 
                    $project_title = htmlspecialchars($project['title'] ?? 'Untitled Project');
                    $project_description = htmlspecialchars($project['short_description'] ?? $project['description'] ?? '');
                    $project_category = htmlspecialchars($project['category'] ?? 'Other');
                    $project_technologies = $project['technologies'] ?? '';
                    $project_thumbnail = $project['thumbnail'] ?? '';
                    $project_live_url = $project['live_demo_url'] ?? '';
                    $project_github_url = $project['github_url'] ?? '';
                    $project_figma_url = $project['figma_url'] ?? '';
                    
                    // Determine project image
                    $project_image_path = '';
                    $project_image_display = '';
                    if ($project_thumbnail && file_exists(__DIR__ . '/uploads/' . $project_thumbnail)) {
                        $project_image_path = 'uploads/' . $project_thumbnail;
                        $project_image_display = '<img src="' . htmlspecialchars($project_image_path) . '" alt="' . htmlspecialchars($project_title) . '">';
                    } else {
                        $project_image_display = htmlspecialchars($project_title);
                    }
                    
                    // Determine project link (prioritize live demo, then github, then figma)
                    $project_link = '#';
                    if (!empty($project_live_url)) {
                        $project_link = $project_live_url;
                    } elseif (!empty($project_github_url)) {
                        $project_link = $project_github_url;
                    } elseif (!empty($project_figma_url)) {
                        $project_link = $project_figma_url;
                    }
                    
                    // Parse technologies for tags
                    $tech_tags = [];
                    if (!empty($project_technologies)) {
                        $tech_tags = array_map('trim', explode(',', $project_technologies));
                    }
                    // Add category as a tag
                    if (!empty($project_category)) {
                        $tech_tags[] = $project_category;
                    }
                    $tech_tags = array_unique(array_filter($tech_tags));
                ?>
                <div class="project-card scroll-scale-up scroll-delay-<?php echo $delay; ?>" 
                     data-project-id="<?php echo htmlspecialchars($project['project_id'] ?? ''); ?>"
                     data-project-title="<?php echo htmlspecialchars($project['title'] ?? 'Untitled Project'); ?>"
                     data-project-description="<?php echo htmlspecialchars($project['description'] ?? ''); ?>"
                     data-project-short-description="<?php echo htmlspecialchars($project['short_description'] ?? ''); ?>"
                     data-project-category="<?php echo htmlspecialchars($project_category); ?>"
                     data-project-technologies="<?php echo htmlspecialchars($project_technologies); ?>"
                     data-project-live-url="<?php echo htmlspecialchars($project_live_url); ?>"
                     data-project-github-url="<?php echo htmlspecialchars($project_github_url); ?>"
                     data-project-figma-url="<?php echo htmlspecialchars($project_figma_url); ?>"
                     data-project-thumbnail="<?php echo htmlspecialchars($project_image_path); ?>"
                     data-project-banner="<?php echo htmlspecialchars(($project['banner_image'] && file_exists(__DIR__ . '/uploads/' . $project['banner_image'])) ? 'uploads/' . $project['banner_image'] : ''); ?>">
                    <div class="project-image project-image-clickable" style="cursor: pointer;">
                        <?php echo $project_image_display; ?>
                    </div>
                    <div class="project-content">
                        <div class="project-title"><?php echo $project_title; ?></div>
                        <div class="project-subtitle"><?php echo $project_category; ?></div>
                        <?php if (!empty($tech_tags)): ?>
                        <div class="project-tags">
                            <?php foreach (array_slice($tech_tags, 0, 3) as $tag): ?>
                                <span class="project-tag"><?php echo htmlspecialchars($tag); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        <?php if ($project_link !== '#'): ?>
                            <a href="<?php echo htmlspecialchars($project_link); ?>" target="_blank" rel="noopener noreferrer" class="project-link"></a>
                        <?php else: ?>
                            <a href="#" class="project-link"></a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php 
                    $delay++;
                    if ($delay > 3) $delay = 1; // Reset delay for animation classes
                endforeach; 
                ?>
            <?php else: ?>
                <div class="project-card scroll-scale-up scroll-delay-1" style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                    <div class="project-content">
                        <div class="project-title" style="font-size: 24px; margin-bottom: 15px;">No Projects Yet</div>
                        <div class="project-subtitle" style="color: var(--text-soft);">Start showcasing your work by adding your first project!</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($total_pages > 1): ?>
        <div class="pagination-wrapper scroll-fade-in scroll-delay-4">
            <div class="pagination">
                <?php if ($current_page > 1): ?>
                    <a href="?page=<?php echo $current_page - 1; ?>" class="pagination-btn pagination-prev">
                        <span>←</span> Previous
                    </a>
                <?php else: ?>
                    <span class="pagination-btn pagination-prev disabled">
                        <span>←</span> Previous
                    </span>
                <?php endif; ?>
                
                <div class="pagination-numbers">
                    <?php
                    // Calculate page range to display
                    $max_pages_to_show = 5;
                    $start_page = max(1, $current_page - floor($max_pages_to_show / 2));
                    $end_page = min($total_pages, $start_page + $max_pages_to_show - 1);
                    
                    // Adjust start if we're near the end
                    if ($end_page - $start_page < $max_pages_to_show - 1) {
                        $start_page = max(1, $end_page - $max_pages_to_show + 1);
                    }
                    
                    // Show first page if not in range
                    if ($start_page > 1): ?>
                        <a href="?page=1" class="pagination-number">1</a>
                        <?php if ($start_page > 2): ?>
                            <span class="pagination-ellipsis">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <?php if ($i == $current_page): ?>
                            <span class="pagination-number active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>" class="pagination-number"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php // Show last page if not in range
                    if ($end_page < $total_pages): ?>
                        <?php if ($end_page < $total_pages - 1): ?>
                            <span class="pagination-ellipsis">...</span>
                        <?php endif; ?>
                        <a href="?page=<?php echo $total_pages; ?>" class="pagination-number"><?php echo $total_pages; ?></a>
                    <?php endif; ?>
                </div>
                
                <?php if ($current_page < $total_pages): ?>
                    <a href="?page=<?php echo $current_page + 1; ?>" class="pagination-btn pagination-next">
                        Next <span>→</span>
                    </a>
                <?php else: ?>
                    <span class="pagination-btn pagination-next disabled">
                        Next <span>→</span>
                    </span>
                <?php endif; ?>
            </div>
            <div class="pagination-info">
                Showing <?php echo count($projects); ?> of <?php echo $total_projects; ?> projects (Page <?php echo $current_page; ?> of <?php echo $total_pages; ?>)
            </div>
        </div>
        <?php endif; ?>
    </section>

    <!-- Footer Section -->
    <footer class="footer" id="contact">
        <div class="footer-content">
            <div class="footer-section scroll-fade-in scroll-delay-1">
                <div class="footer-logo">
                    <div class="footer-logo-icon">
                        <img src="../images/is_logo.png" alt="BSIS Logo">
                    </div>
                    <span>BSIS</span>
                </div>
                <p class="footer-description">
                    <?php echo htmlspecialchars($bio); ?>
                </p>
                <div class="footer-social">
                    <a href="#" class="footer-social-icon">f</a>
                    <a href="#" class="footer-social-icon">@</a>
                    <a href="#" class="footer-social-icon">in</a>
                </div>
            </div>
            <div class="footer-section scroll-fade-in scroll-delay-3">
                <h3 class="footer-title">Contact</h3>
                <div class="footer-links">
                    <?php if (!empty($email)): ?>
                        <a href="mailto:<?php echo htmlspecialchars($email); ?>" class="footer-link"><?php echo htmlspecialchars($email); ?></a>
                    <?php endif; ?>
                    <?php if (!empty($phone)): ?>
                        <a href="tel:<?php echo htmlspecialchars($phone); ?>" class="footer-link"><?php echo htmlspecialchars($phone); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="footer-bottom scroll-fade-in scroll-delay-4">
            <p class="footer-copyright">
                © <?php echo date('Y'); ?> Copyright BAGO CITY COLLEGE BSIS. All rights reserved.
            </p>
        </div>
    </footer>

    <!-- Project Modal -->
    <div class="project-modal" id="projectModal">
        <div class="project-modal-overlay"></div>
        <div class="project-modal-content">
            <button class="project-modal-close" id="projectModalClose">&times;</button>
            <div class="project-modal-body">
                <div class="project-modal-image-container">
                    <img id="projectModalBanner" src="" alt="Project Banner" class="project-modal-banner">
                    <img id="projectModalThumbnail" src="" alt="Project Thumbnail" class="project-modal-thumbnail">
                </div>
                <div class="project-modal-info">
                    <h2 id="projectModalTitle" class="project-modal-title"></h2>
                    <div class="project-modal-meta">
                        <span id="projectModalCategory" class="project-modal-category"></span>
                        <span id="projectModalDate" class="project-modal-date"></span>
                    </div>
                    <div id="projectModalTechnologies" class="project-modal-technologies"></div>
                    <p id="projectModalShortDescription" class="project-modal-short-description"></p>
                    <div id="projectModalDescription" class="project-modal-description"></div>
                    <div class="project-modal-actions">
                        <a id="projectModalLiveBtn" href="#" target="_blank" rel="noopener noreferrer" class="project-modal-btn project-modal-btn-primary" style="display: none;">
                            Live Demo →
                        </a>
                        <a id="projectModalGithubBtn" href="#" target="_blank" rel="noopener noreferrer" class="project-modal-btn project-modal-btn-secondary" style="display: none;">
                            GitHub →
                        </a>
                        <a id="projectModalFigmaBtn" href="#" target="_blank" rel="noopener noreferrer" class="project-modal-btn project-modal-btn-secondary" style="display: none;">
                            Figma →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>

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

        });

        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Smooth scroll to projects section when clicking pagination links
        document.querySelectorAll('.pagination a').forEach(link => {
            link.addEventListener('click', function(e) {
                // Small delay to allow page to load, then scroll
                setTimeout(() => {
                    const projectsSection = document.getElementById('projects');
                    if (projectsSection) {
                        projectsSection.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }, 100);
            });
        });

        // Active navigation link
        window.addEventListener('scroll', () => {
            const sections = document.querySelectorAll('section[id]');
            const navLinks = document.querySelectorAll('nav a');
            
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (window.pageYOffset >= sectionTop - 200) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${current}`) {
                    link.classList.add('active');
                }
            });
        });

        // Typed.js initialization for hero title
        document.addEventListener('DOMContentLoaded', function() {
            const fullName = '<?php echo strtoupper(htmlspecialchars($fullname)); ?>';
            const typedText = document.getElementById('typed-text');
            
            if (typedText) {
                // Build strings array for Typed.js
                const typedStrings = [];
                
                // First string: "Hi! I'm [FIRSTNAME]"
                const fullText = 'HI! I\'M <span class="highlight">' + fullName + '</span>';
                typedStrings.push(fullText);
                
                <?php
                // Format skills with proper grammar
                $skills_text = '';
                if (!empty($skills)) {
                    $skills_array = array_map('trim', explode(',', $skills));
                    $skills_array = array_filter($skills_array);
                    if (!empty($skills_array)) {
                        if (count($skills_array) == 1) {
                            $skills_text = htmlspecialchars($skills_array[0], ENT_QUOTES, 'UTF-8');
                        } elseif (count($skills_array) == 2) {
                            $skills_text = htmlspecialchars($skills_array[0] . ' and ' . $skills_array[1], ENT_QUOTES, 'UTF-8');
                        } else {
                            $skills_copy = $skills_array;
                            $last = array_pop($skills_copy);
                            $skills_text = htmlspecialchars(implode(', ', $skills_copy) . ', and ' . $last, ENT_QUOTES, 'UTF-8');
                        }
                    }
                }
                
                // Format interests with proper grammar
                $interests_text = '';
                if (!empty($interests)) {
                    $interests_array = array_map('trim', explode(',', $interests));
                    $interests_array = array_filter($interests_array);
                    if (!empty($interests_array)) {
                        if (count($interests_array) == 1) {
                            $interests_text = htmlspecialchars($interests_array[0], ENT_QUOTES, 'UTF-8');
                        } elseif (count($interests_array) == 2) {
                            $interests_text = htmlspecialchars($interests_array[0] . ' and ' . $interests_array[1], ENT_QUOTES, 'UTF-8');
                        } else {
                            $interests_copy = $interests_array;
                            $last = array_pop($interests_copy);
                            $interests_text = htmlspecialchars(implode(', ', $interests_copy) . ', and ' . $last, ENT_QUOTES, 'UTF-8');
                        }
                    }
                }
                
                // Build JavaScript strings
                if (!empty($skills_text)) {
                    echo "typedStrings.push('I LIKE " . strtoupper($skills_text) . "');\n";
                }
                
                if (!empty($interests_text)) {
                    echo "typedStrings.push('I AM INTERESTED IN " . strtoupper($interests_text) . "');\n";
                }
                ?>
                
                // If no skills or interests, just use the name and empty string for looping effect
                if (typedStrings.length === 1) {
                    typedStrings.push('');
                }
                
                let typedInstance = new Typed('#typed-text', {
                    strings: typedStrings,
                    typeSpeed: 100,
                    backSpeed: 60,
                    backDelay: 3500,
                    startDelay: 500,
                    showCursor: true,
                    cursorChar: '_',
                    fadeOut: false,
                    loop: typedStrings.length > 1,
                    html: true,
                    smartBackspace: true
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

                // Sun horizontal movement is handled by CSS animation only
                // No parallax effect needed for smooth horizontal movement

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

        // Smooth Scroll-triggered animations using Intersection Observer
        document.addEventListener('DOMContentLoaded', function() {
            // Improved observer options for smoother triggering
            const observerOptions = {
                threshold: [0, 0.05, 0.1, 0.15, 0.2],
                rootMargin: '0px 0px -80px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    // Use requestAnimationFrame for smoother animation triggering
                    requestAnimationFrame(() => {
                        if (entry.isIntersecting) {
                            // Add a small delay for smoother entrance
                            setTimeout(() => {
                                entry.target.classList.add('visible');
                            }, 30);
                        } else {
                            // Only remove visible class if scrolling up significantly
                            if (entry.boundingClientRect.top > window.innerHeight) {
                                entry.target.classList.remove('visible');
                            }
                        }
                    });
                });
            }, observerOptions);

            // Observe all elements with scroll animation classes
            const animatedElements = document.querySelectorAll(
                '.scroll-fade-in, .scroll-fade-in-left, .scroll-fade-in-right, ' +
                '.scroll-scale-up, .scroll-rotate-in'
            );
            
            animatedElements.forEach(el => {
                // Pre-optimize elements for animation
                el.style.perspective = '1000px';
                observer.observe(el);
            });

            // Handle carousel item clicks to show project details
            const carouselItems = document.querySelectorAll('.project-highlight-item');
            const contentWrapper = document.getElementById('projectHighlightContent');
            const titleElement = document.getElementById('projectHighlightTitle');
            const descriptionElement = document.getElementById('projectHighlightDescription');
            const tagsElement = document.getElementById('projectHighlightTags');
            const actionsElement = document.getElementById('projectHighlightActions');

            if (carouselItems.length > 0 && contentWrapper) {
                carouselItems.forEach(item => {
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();

                        // Get project data from data attributes
                        const projectTitle = this.getAttribute('data-title') || 'Featured Project';
                        const projectDescription = this.getAttribute('data-description') || 'This is a featured project that showcases my skills and expertise.';
                        const projectTagsJson = this.getAttribute('data-tags') || '[]';
                        const projectPrimaryLink = this.getAttribute('data-primary-link') || '#';
                        const projectPrimaryText = this.getAttribute('data-primary-text') || 'View Project';
                        const projectGithubUrl = this.getAttribute('data-github-url') || '';

                        // Parse tags
                        let projectTags = [];
                        try {
                            projectTags = JSON.parse(projectTagsJson);
                        } catch (e) {
                            projectTags = [];
                        }

                        // Update title
                        if (titleElement) {
                            titleElement.textContent = projectTitle;
                        }

                        // Update description
                        if (descriptionElement) {
                            descriptionElement.textContent = projectDescription;
                        }

                        // Update tags
                        if (tagsElement) {
                            tagsElement.innerHTML = '';
                            projectTags.slice(0, 5).forEach(tag => {
                                const tagElement = document.createElement('span');
                                tagElement.className = 'project-highlight-tag';
                                tagElement.textContent = tag;
                                tagsElement.appendChild(tagElement);
                            });
                        }

                        // Update action buttons
                        if (actionsElement) {
                            actionsElement.innerHTML = '';
                            
                            // Primary button
                            if (projectPrimaryLink && projectPrimaryLink !== '#') {
                                const primaryBtn = document.createElement('a');
                                primaryBtn.href = projectPrimaryLink;
                                primaryBtn.target = '_blank';
                                primaryBtn.rel = 'noopener noreferrer';
                                primaryBtn.className = 'project-highlight-btn project-highlight-btn-primary';
                                primaryBtn.textContent = projectPrimaryText;
                                actionsElement.appendChild(primaryBtn);
                            }

                            // GitHub button (if different from primary)
                            if (projectGithubUrl && projectGithubUrl !== projectPrimaryLink && projectGithubUrl !== '') {
                                const githubBtn = document.createElement('a');
                                githubBtn.href = projectGithubUrl;
                                githubBtn.target = '_blank';
                                githubBtn.rel = 'noopener noreferrer';
                                githubBtn.className = 'project-highlight-btn project-highlight-btn-secondary';
                                githubBtn.textContent = 'GitHub →';
                                actionsElement.appendChild(githubBtn);
                            }
                        }

                        // Show the content wrapper with animation
                        contentWrapper.classList.add('show');
                    });
                });

                // Close content wrapper when clicking outside
                document.addEventListener('click', function(e) {
                    if (contentWrapper && contentWrapper.classList.contains('show')) {
                        const isClickInsideContent = contentWrapper.contains(e.target);
                        const isClickOnCarouselItem = Array.from(carouselItems).some(item => item.contains(e.target));
                        
                        if (!isClickInsideContent && !isClickOnCarouselItem) {
                            contentWrapper.classList.remove('show');
                        }
                    }
                });
            }
        });

        // Project Modal Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('projectModal');
            const closeBtn = document.getElementById('projectModalClose');
            const overlay = modal.querySelector('.project-modal-overlay');
            const projectCards = document.querySelectorAll('.project-card .project-image-clickable');

            // Open modal function
            function openProjectModal(card) {
                const projectCard = card.closest('.project-card');
                if (!projectCard) return;

                // Get project data from data attributes
                const projectId = projectCard.getAttribute('data-project-id');
                const title = projectCard.getAttribute('data-project-title') || 'Untitled Project';
                const description = projectCard.getAttribute('data-project-description') || '';
                const shortDescription = projectCard.getAttribute('data-project-short-description') || '';
                const category = projectCard.getAttribute('data-project-category') || 'Other';
                const technologies = projectCard.getAttribute('data-project-technologies') || '';
                const liveUrl = projectCard.getAttribute('data-project-live-url') || '';
                const githubUrl = projectCard.getAttribute('data-project-github-url') || '';
                const figmaUrl = projectCard.getAttribute('data-project-figma-url') || '';
                const thumbnail = projectCard.getAttribute('data-project-thumbnail') || '';
                const banner = projectCard.getAttribute('data-project-banner') || '';

                // Populate modal
                document.getElementById('projectModalTitle').textContent = title;
                document.getElementById('projectModalCategory').textContent = category;
                document.getElementById('projectModalShortDescription').textContent = shortDescription || description.substring(0, 150) + '...';
                document.getElementById('projectModalDescription').textContent = description;

                // Set technologies
                const techContainer = document.getElementById('projectModalTechnologies');
                techContainer.innerHTML = '';
                if (technologies) {
                    const techArray = technologies.split(',').map(t => t.trim()).filter(t => t);
                    techArray.forEach(tech => {
                        const tag = document.createElement('span');
                        tag.className = 'project-tag';
                        tag.textContent = tech;
                        techContainer.appendChild(tag);
                    });
                }

                // Set images
                const bannerImg = document.getElementById('projectModalBanner');
                const thumbnailImg = document.getElementById('projectModalThumbnail');
                
                if (banner) {
                    bannerImg.src = banner;
                    bannerImg.classList.add('active');
                    thumbnailImg.classList.remove('active');
                } else if (thumbnail) {
                    thumbnailImg.src = thumbnail;
                    thumbnailImg.classList.add('active');
                    bannerImg.classList.remove('active');
                } else {
                    bannerImg.classList.remove('active');
                    thumbnailImg.classList.remove('active');
                }

                // Set action buttons
                const liveBtn = document.getElementById('projectModalLiveBtn');
                const githubBtn = document.getElementById('projectModalGithubBtn');
                const figmaBtn = document.getElementById('projectModalFigmaBtn');

                if (liveUrl) {
                    liveBtn.href = liveUrl;
                    liveBtn.style.display = 'inline-flex';
                } else {
                    liveBtn.style.display = 'none';
                }

                if (githubUrl) {
                    githubBtn.href = githubUrl;
                    githubBtn.style.display = 'inline-flex';
                } else {
                    githubBtn.style.display = 'none';
                }

                if (figmaUrl) {
                    figmaBtn.href = figmaUrl;
                    figmaBtn.style.display = 'inline-flex';
                } else {
                    figmaBtn.style.display = 'none';
                }

                // Show modal
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }

            // Close modal function
            function closeProjectModal() {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }

            // Add click event to project images
            projectCards.forEach(card => {
                card.addEventListener('click', function(e) {
                    e.preventDefault();
                    openProjectModal(this);
                });
            });

            // Close modal events
            if (closeBtn) {
                closeBtn.addEventListener('click', closeProjectModal);
            }

            if (overlay) {
                overlay.addEventListener('click', closeProjectModal);
            }

            // Close on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.classList.contains('active')) {
                    closeProjectModal();
                }
            });
        });

        // Floating Button - Sliding Circles Menu
        const floatingButton = document.getElementById('floatingButton');
        const circlesMenu = document.getElementById('circlesMenu');
        const circleItems = document.querySelectorAll('.circle-item');
        let isMenuOpen = false;

        // Toggle circles menu
        floatingButton.addEventListener('click', () => {
            isMenuOpen = !isMenuOpen;
            
            if (isMenuOpen) {
                circlesMenu.classList.add('active');
                floatingButton.classList.add('active');
                floatingButton.querySelector('.button-icon').textContent = '✕';
            } else {
                circlesMenu.classList.remove('active');
                floatingButton.classList.remove('active');
                floatingButton.querySelector('.button-icon').textContent = '☰';
            }
        });

        // Handle circle item clicks
        circleItems.forEach(item => {
            item.addEventListener('click', () => {
                const action = item.getAttribute('data-action');
                
                switch(action) {
                    case 'logout':
                        const isLightMode = document.documentElement.getAttribute('data-theme') === 'light';
                        Swal.fire({
                            title: 'Logout Confirmation',
                            text: 'Are you sure you want to logout?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#667eea',
                            cancelButtonColor: '#ef4444',
                            confirmButtonText: 'Yes, logout',
                            cancelButtonText: 'Cancel',
                            background: getComputedStyle(document.documentElement).getPropertyValue('--bg-elevated'),
                            color: isLightMode ? '#000000' : '#10b981',
                            iconColor: isLightMode ? '#000000' : '#10b981',
                            customClass: {
                                popup: 'theme-swal-popup',
                                title: 'theme-swal-title',
                                content: 'theme-swal-content'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'logout.php';
                            }
                        });
                        break;
                    case 'projects':
                        window.location.href = 'manage_projects.php';
                        break;
                    case 'theme':
                        const currentTheme = document.documentElement.getAttribute('data-theme');
                        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
                        document.documentElement.setAttribute('data-theme', newTheme);
                        localStorage.setItem('theme', newTheme);
                        
                        // Update theme icon
                        const themeIcon = document.getElementById('themeIcon');
                        if (newTheme === 'light') {
                            themeIcon.className = 'fa fa-sun';
                        } else {
                            themeIcon.className = 'fa fa-moon';
                        }
                        break;
                    case 'profile':
                        window.location.href = 'edit_profile.php';
                        break;
                }
                
                // Keep menu open after action (don't auto-close)
            });
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (isMenuOpen && !floatingButton.contains(e.target) && !circlesMenu.contains(e.target)) {
                circlesMenu.classList.remove('active');
                floatingButton.classList.remove('active');
                floatingButton.querySelector('.button-icon').textContent = '☰';
                isMenuOpen = false;
            }
        });

        // Update theme icon on load
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const themeIcon = document.getElementById('themeIcon');
        if (currentTheme === 'light' && themeIcon) {
            themeIcon.className = 'fa fa-sun';
        }
    </script>
        </div>
        <!-- End Shell Container -->
    </div>
    <!-- End Page Shell -->
</body>
</html>


