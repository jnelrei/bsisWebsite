<?php
session_start();

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../functions/db/database.php';

$student_id = $_SESSION['student_id'];
$error = $_SESSION['project_error'] ?? '';
$success = $_SESSION['project_success'] ?? '';
unset($_SESSION['project_error'], $_SESSION['project_success']);

// Fetch projects for the student
$projects = [];
try {
    $pdo = getPDO();
    
    // Check if projects table exists
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'projects'");
    if ($tableCheck->rowCount() > 0) {
        // Check if projects table has student_id column
        $stmt = $pdo->query("SHOW COLUMNS FROM projects LIKE 'student_id'");
        $has_student_id = $stmt->rowCount() > 0;
        
        if ($has_student_id) {
            // Query with student_id column
            $stmt = $pdo->prepare('SELECT * FROM projects WHERE student_id = :student_id ORDER BY created_at DESC');
            $stmt->execute([':student_id' => $student_id]);
            $projects = $stmt->fetchAll();
        } else {
            // Check if there's a junction table
            $checkJunction = $pdo->query("SHOW TABLES LIKE 'student_projects'");
            if ($checkJunction->rowCount() > 0) {
                // Query using junction table
                $stmt = $pdo->prepare('SELECT p.* FROM projects p 
                    INNER JOIN student_projects sp ON p.project_id = sp.project_id 
                    WHERE sp.student_id = :student_id 
                    ORDER BY p.created_at DESC');
                $stmt->execute([':student_id' => $student_id]);
                $projects = $stmt->fetchAll();
            } else {
                // Fallback: query all projects
                $stmt = $pdo->prepare('SELECT * FROM projects ORDER BY created_at DESC');
                $stmt->execute();
                $projects = $stmt->fetchAll();
            }
        }
        
        // Fetch primary images for each project
        $checkImagesTable = $pdo->query("SHOW TABLES LIKE 'project_images'");
        if ($checkImagesTable->rowCount() > 0) {
            foreach ($projects as &$project) {
                $imageStmt = $pdo->prepare('SELECT image_path FROM project_images WHERE project_id = :project_id AND is_primary = 1 LIMIT 1');
                $imageStmt->execute([':project_id' => $project['project_id']]);
                $primaryImage = $imageStmt->fetch();
                
                if ($primaryImage) {
                    $project['primary_image'] = $primaryImage['image_path'];
                } else {
                    // Fallback to first image if no primary
                    $firstImageStmt = $pdo->prepare('SELECT image_path FROM project_images WHERE project_id = :project_id ORDER BY sort_order ASC, image_id ASC LIMIT 1');
                    $firstImageStmt->execute([':project_id' => $project['project_id']]);
                    $firstImage = $firstImageStmt->fetch();
                    $project['primary_image'] = $firstImage ? $firstImage['image_path'] : null;
                }
            }
            unset($project); // Break reference
        }
    }
} catch (Exception $e) {
    $error = 'Error loading projects: ' . $e->getMessage();
    $projects = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Projects - BSIS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg-main: #020617;
            --bg-elevated: #0f172a;
            --bg-card: #1e293b;
            --accent: #22c55e;
            --accent-soft: rgba(34, 197, 94, 0.16);
            --accent-strong: #4ade80;
            --text-main: #e5e7eb;
            --text-soft: #9ca3af;
            --border-subtle: rgba(148, 163, 184, 0.25);
            --radius-lg: 22px;
            --radius-md: 18px;
            --radius-pill: 999px;
        }

        body {
            font-family: 'Space Grotesk', system-ui, -apple-system, sans-serif;
            background: radial-gradient(circle at top left, #0f172a 0, #020617 45%, #000 100%);
            color: var(--text-main);
            min-height: 100vh;
            padding: 100px 20px 40px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .header h1 {
            font-size: 48px;
            font-weight: 700;
            background: linear-gradient(135deg, #22c55e, #0ea5e9);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .btn {
            padding: 12px 30px;
            border-radius: var(--radius-pill);
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #22c55e, #0ea5e9);
            color: var(--bg-main);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(34, 197, 94, 0.5);
        }

        .btn-secondary {
            background: rgba(15, 23, 42, 0.8);
            color: var(--text-main);
            border: 1px solid rgba(148, 163, 184, 0.3);
            padding: 8px 16px;
            font-size: 12px;
        }

        .btn-secondary:hover {
            border-color: rgba(148, 163, 184, 0.6);
            transform: translateY(-2px);
        }

        .btn-danger {
            background: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            border: 1px solid rgba(239, 68, 68, 0.3);
            padding: 8px 16px;
            font-size: 12px;
        }

        .btn-danger:hover {
            background: rgba(239, 68, 68, 0.3);
            border-color: rgba(239, 68, 68, 0.5);
        }

        .alert {
            padding: 15px 20px;
            border-radius: var(--radius-md);
            margin-bottom: 25px;
            font-size: 14px;
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

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: var(--text-soft);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: var(--accent-strong);
        }

        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        .project-item {
            background: radial-gradient(circle at top left, rgba(15, 23, 42, 0.96), #020617);
            border: 1px solid rgba(148, 163, 184, 0.55);
            border-radius: var(--radius-md);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.7);
        }

        .project-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 28px 80px rgba(0, 0, 0, 0.9);
        }

        .project-thumbnail {
            width: 100%;
            height: 200px;
            background: radial-gradient(circle at top left, rgba(34, 197, 94, 0.18), rgba(15, 23, 42, 0.98));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-soft);
            overflow: hidden;
        }

        .project-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .project-content {
            padding: 20px;
        }

        .project-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text-main);
        }

        .project-meta {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .project-badge {
            padding: 4px 12px;
            border-radius: var(--radius-pill);
            font-size: 11px;
            font-weight: 600;
        }

        .badge-category {
            background: rgba(34, 197, 94, 0.15);
            color: var(--accent-strong);
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .badge-featured {
            background: rgba(14, 165, 233, 0.15);
            color: #0ea5e9;
            border: 1px solid rgba(14, 165, 233, 0.3);
        }

        .badge-published {
            background: rgba(34, 197, 94, 0.15);
            color: var(--accent-strong);
        }

        .badge-unpublished {
            background: rgba(148, 163, 184, 0.15);
            color: var(--text-soft);
        }

        .project-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: radial-gradient(circle at top left, rgba(15, 23, 42, 0.96), #020617);
            border: 1px solid rgba(148, 163, 184, 0.55);
            border-radius: var(--radius-md);
        }

        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: var(--text-main);
        }

        .empty-state p {
            color: var(--text-soft);
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="main.php" class="back-link">← Back to Profile</a>
        
        <div class="header">
            <h1>My Projects</h1>
            <a href="add_project.php" class="btn btn-primary">+ Add New Project</a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (empty($projects)): ?>
            <div class="empty-state">
                <h3>No Projects Yet</h3>
                <p>Start showcasing your work by adding your first project!</p>
                <a href="add_project.php" class="btn btn-primary">Add Your First Project</a>
            </div>
        <?php else: ?>
            <div class="projects-grid">
                <?php foreach ($projects as $project): ?>
                    <div class="project-item">
                        <div class="project-thumbnail">
                            <?php 
                            $display_image = null;
                            // Try primary_image first, then thumbnail
                            if (isset($project['primary_image']) && $project['primary_image'] && file_exists(__DIR__ . '/uploads/' . $project['primary_image'])) {
                                $display_image = $project['primary_image'];
                            } elseif ($project['thumbnail'] && file_exists(__DIR__ . '/uploads/' . $project['thumbnail'])) {
                                $display_image = $project['thumbnail'];
                            }
                            
                            if ($display_image): ?>
                                <img src="uploads/<?php echo htmlspecialchars($display_image); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>">
                            <?php else: ?>
                                <div style="padding: 20px; text-align: center;">
                                    <?php echo htmlspecialchars(substr($project['title'], 0, 30)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="project-content">
                            <h3 class="project-title"><?php echo htmlspecialchars($project['title']); ?></h3>
                            <div class="project-meta">
                                <span class="project-badge badge-category"><?php echo htmlspecialchars($project['category']); ?></span>
                                <?php if ($project['is_featured']): ?>
                                    <span class="project-badge badge-featured">Featured</span>
                                <?php endif; ?>
                                <span class="project-badge <?php echo $project['is_published'] ? 'badge-published' : 'badge-unpublished'; ?>">
                                    <?php echo $project['is_published'] ? 'Published' : 'Draft'; ?>
                                </span>
                            </div>
                            <div class="project-actions">
                                <a href="edit_project.php?id=<?php echo $project['project_id']; ?>" class="btn btn-secondary">Edit</a>
                                <a href="delete_project.php?id=<?php echo $project['project_id']; ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('Are you sure you want to delete this project?');">Delete</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

