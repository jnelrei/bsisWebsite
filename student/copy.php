<?php
session_start();

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../functions/db/database.php';

$student_id = $_SESSION['student_id'];

// Fetch student data
try {
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT * FROM students WHERE student_id = :student_id LIMIT 1');
    $stmt->execute([':student_id' => $student_id]);
    $student = $stmt->fetch();
    
    if (!$student) {
        $student = [];
    }
    
    // Set default values
    $fullname = $student['fullname'] ?? $student['name'] ?? 'Student';
    $first_name = explode(' ', $fullname)[0];
    $email = $student['email'] ?? '';
    $phone = $student['phone'] ?? '';
    $bio = $student['bio'] ?? $student['description'] ?? 'Yet bed any for travelling assistance indulgence unpleasing. Not thoughts all exercise blessing. Indulgence way everything joy alteration boisterous the attachment.';
    $profile_picture = $student['profile_picture'] ?? '';
    $skills = $student['skills'] ?? '';
    $interests = $student['interests'] ?? '';
    $created_at = $student['created_at'] ?? '';
    $updated_at = $student['updated_at'] ?? '';
    
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
    $fullname = 'Student';
    $first_name = 'Student';
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
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
            gap: 30px;
            justify-content: flex-end;
            flex-wrap: nowrap;
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

        .dropdown-divider {
            height: 1px;
            background: rgba(148, 163, 184, 0.3);
            margin: 5px 0;
        }

        /* Hero Section */
        .hero {
            margin-top: 100px;
            padding: 100px 80px;
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 80px;
            align-items: center;
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
            letter-spacing: 0.2em;
            text-transform: uppercase;
            padding: 12px 24px;
            border-radius: 50px;
            border: 1px solid rgba(34, 197, 94, 0.3);
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(14, 165, 233, 0.1));
            backdrop-filter: blur(10px);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: fit-content;
            max-width: 100%;
            box-shadow: 0 8px 32px rgba(34, 197, 94, 0.1);
            transition: all var(--transition-slow);
            position: relative;
            overflow: hidden;
            will-change: transform, border-color, box-shadow;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .hero-label::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.8s var(--ease-smooth);
        }

        .hero-label:hover::before {
            left: 100%;
        }

        .hero-label:hover {
            border-color: rgba(34, 197, 94, 0.6);
            box-shadow: 0 8px 32px rgba(34, 197, 94, 0.2);
            transform: translateY(-2px);
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
            width: 420px;
            height: 420px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(14, 165, 233, 0.2));
            filter: blur(40px);
            z-index: -1;
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
            width: 380px;
            height: 380px;
            max-width: 100%;
            max-height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid transparent;
            background: linear-gradient(var(--bg-main), var(--bg-main)) padding-box,
                        linear-gradient(135deg, #22c55e, #0ea5e9, #a855f7, #22c55e) border-box;
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
            opacity: 0.15;
            will-change: transform;
            transition: transform 0.1s ease-out;
        }

        .parallax-cloud img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: blur(0.5px);
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
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-bottom: 40px;
        }

        .project-card {
            background: radial-gradient(circle at top left, rgba(15, 23, 42, 0.96), #020617);
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

        .project-image {
            width: 100%;
            height: 180px;
            background: radial-gradient(circle at top left, rgba(34, 197, 94, 0.18), rgba(15, 23, 42, 0.98));
            border-bottom: 1px solid rgba(148, 163, 184, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-main);
            font-size: 14px;
            overflow: hidden;
            position: relative;
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
            padding: 60px 80px 30px;
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
            padding-top: 30px;
            border-top: 1px solid rgba(148, 163, 184, 0.2);
            display: flex;
            justify-content: space-between;
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
                font-size: 14px;
                padding: 10px 20px;
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
                opacity: 0.1;
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
                opacity: 0.08;
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
                opacity: 0.06;
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
                font-size: 13px;
                padding: 10px 18px;
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
                font-size: 12px;
                padding: 6px 12px;
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
    </style>
</head>
<body>
    <!-- Parallax Clouds Container -->
    <div class="parallax-clouds-container" id="parallaxClouds">
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
                <li><a href="#home" class="active">HOME</a></li>
                <li><a href="#projects">PROJECTS</a></li>
                <li><a href="#contact">CONTACT</a></li>
            </ul>
        </nav>
        <div class="header-right">
            <div class="mobile-menu-toggle" id="mobileMenuToggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <div class="dropdown">
                <button class="cta-button dropdown-toggle" id="userDropdown">
                    <?php echo strtoupper(htmlspecialchars(explode(' ', $fullname)[0])); ?>
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

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <div class="hero-label"><?php echo strtoupper(htmlspecialchars($fullname)); ?></div>
            <h1 class="hero-title">
                <span id="typed-text"></span>
            </h1>
            <p class="hero-description">
                <?php echo htmlspecialchars($bio); ?>
            </p>
            <div class="hero-actions">
                <a href="#contact" class="btn-primary">GET IN TOUCH →</a>
                <div class="social-icons">
                    <a href="#" class="social-icon">f</a>
                    <a href="#" class="social-icon">@</a>
                    <a href="#" class="social-icon">in</a>
                </div>
            </div>
        </div>
        <?php if (!empty($hero_profile_img_path)): ?>
        <div class="hero-image">
            <div class="hero-bg-shapes">
                <div class="shape shape-1"></div>
                <div class="shape shape-2"></div>
            </div>
            <img src="<?php echo htmlspecialchars($hero_profile_img_path); ?>" alt="<?php echo htmlspecialchars($fullname); ?>">
        </div>
        <?php endif; ?>
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
                <div class="section-label scroll-fade-in scroll-delay-1">FEATURED WORK</div>
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
            <div class="section-label scroll-fade-in scroll-delay-1">MY WORK.</div>
            <h2 class="section-title scroll-fade-in scroll-delay-2">RECENT PROJECT</h2>
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
                <div class="project-card scroll-scale-up scroll-delay-<?php echo $delay; ?>">
                    <div class="project-image">
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
            <div class="footer-section scroll-fade-in scroll-delay-2">
                <h3 class="footer-title">Quick Links</h3>
                <div class="footer-links">
                    <a href="#home" class="footer-link">Home</a>
                    <a href="#projects" class="footer-link">Projects</a>
                    <a href="edit_profile.php" class="footer-link">Edit Profile</a>
                    <a href="manage_projects.php" class="footer-link">Manage Projects</a>
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
                &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($fullname); ?>. All rights reserved.
            </p>
            <div class="footer-bottom-links">
                <a href="edit_profile.php" class="footer-bottom-link">Privacy Policy</a>
                <a href="edit_profile.php" class="footer-bottom-link">Terms of Service</a>
            </div>
        </div>
    </footer>

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
            const firstName = '<?php echo strtoupper(htmlspecialchars($first_name)); ?>';
            const typedText = document.getElementById('typed-text');
            
            if (typedText) {
                // Build strings array for Typed.js
                const typedStrings = [];
                
                // First string: "Hi! I'm [FIRSTNAME]"
                const fullText = 'HI! I\'M <span class="highlight">' + firstName + '</span>';
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
                    const opacityBase = 0.15;
                    const opacityVariation = Math.sin(scrollProgress * Math.PI * 2) * 0.05;
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
    </script>
</body>
</html>


