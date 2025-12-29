<?php
session_start();

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../functions/db/database.php';

$student_id = $_SESSION['student_id'];
$project_id = $_GET['id'] ?? 0;

if (!$project_id) {
    $_SESSION['project_error'] = 'Project ID is required.';
    header('Location: manage_projects.php');
    exit;
}

$error = $_SESSION['project_error'] ?? '';
$success = $_SESSION['project_success'] ?? '';
unset($_SESSION['project_error'], $_SESSION['project_success']);

// Fetch project data
$project = null;
try {
    $pdo = getPDO();
    
    // Check if projects table has student_id column
    $stmt = $pdo->query("SHOW COLUMNS FROM projects LIKE 'student_id'");
    $has_student_id = $stmt->rowCount() > 0;
    
    if ($has_student_id) {
        // Query with student_id check
        $stmt = $pdo->prepare('SELECT * FROM projects WHERE project_id = :project_id AND student_id = :student_id LIMIT 1');
        $stmt->execute([':project_id' => $project_id, ':student_id' => $student_id]);
    } else {
        // Check if there's a junction table
        $checkJunction = $pdo->query("SHOW TABLES LIKE 'student_projects'");
        if ($checkJunction->rowCount() > 0) {
            // Query using junction table
            $stmt = $pdo->prepare('SELECT p.* FROM projects p 
                INNER JOIN student_projects sp ON p.project_id = sp.project_id 
                WHERE p.project_id = :project_id AND sp.student_id = :student_id LIMIT 1');
            $stmt->execute([':project_id' => $project_id, ':student_id' => $student_id]);
        } else {
            // Fallback: query without student check (less secure but works)
            $stmt = $pdo->prepare('SELECT * FROM projects WHERE project_id = :project_id LIMIT 1');
            $stmt->execute([':project_id' => $project_id]);
        }
    }
    
    $project = $stmt->fetch();
    
    if (!$project) {
        $_SESSION['project_error'] = 'Project not found or you do not have permission to edit it.';
        header('Location: manage_projects.php');
        exit;
    }
    
} catch (Exception $e) {
    $error = 'Error loading project: ' . $e->getMessage();
    $project = [];
}

// Set default values
$title = $project['title'] ?? '';
$description = $project['description'] ?? '';
$short_description = $project['short_description'] ?? '';
$category = $project['category'] ?? '';
$technologies = $project['technologies'] ?? '';
$live_demo_url = $project['live_demo_url'] ?? '';
$github_url = $project['github_url'] ?? '';
$figma_url = $project['figma_url'] ?? '';
    $thumbnail = $project['thumbnail'] ?? '';
    $banner_image = $project['banner_image'] ?? '';
    $is_featured = $project['is_featured'] ?? 0;
    $is_published = $project['is_published'] ?? 1;
    
    // Fetch project images
    $project_images = [];
    try {
        $checkImagesTable = $pdo->query("SHOW TABLES LIKE 'project_images'");
        if ($checkImagesTable->rowCount() > 0) {
            $imagesStmt = $pdo->prepare('SELECT * FROM project_images WHERE project_id = :project_id ORDER BY sort_order ASC, image_id ASC');
            $imagesStmt->execute([':project_id' => $project_id]);
            $project_images = $imagesStmt->fetchAll();
        }
    } catch (Exception $e) {
        // Ignore errors for project_images table
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Project - BSIS</title>
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
            max-width: 900px;
            margin: 0 auto;
        }

        .header {
            margin-bottom: 40px;
            text-align: center;
        }

        .header h1 {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #22c55e, #0ea5e9);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .header p {
            color: var(--text-soft);
            font-size: 16px;
        }

        .project-card {
            background: radial-gradient(circle at top left, rgba(15, 23, 42, 0.96), #020617);
            border: 1px solid rgba(148, 163, 184, 0.55);
            border-radius: var(--radius-md);
            padding: 40px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.7);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-main);
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 18px;
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(148, 163, 184, 0.3);
            border-radius: var(--radius-md);
            color: var(--text-main);
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-group small {
            display: block;
            margin-top: 5px;
            color: var(--text-soft);
            font-size: 12px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox-group input[type="checkbox"] {
            width: auto;
            cursor: pointer;
        }

        .image-preview {
            margin-top: 15px;
        }

        .image-preview img {
            max-width: 100%;
            max-height: 300px;
            border-radius: var(--radius-md);
            border: 1px solid rgba(148, 163, 184, 0.3);
        }

        .current-image {
            margin-top: 10px;
            padding: 10px;
            background: rgba(15, 23, 42, 0.5);
            border-radius: var(--radius-md);
            font-size: 12px;
            color: var(--text-soft);
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
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
        }

        .btn-secondary:hover {
            border-color: rgba(148, 163, 184, 0.6);
            transform: translateY(-2px);
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

        .existing-images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .existing-image-item {
            position: relative;
            border: 1px solid rgba(148, 163, 184, 0.3);
            border-radius: var(--radius-md);
            overflow: hidden;
            background: rgba(15, 23, 42, 0.5);
        }

        .existing-image-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            display: block;
        }

        .primary-badge {
            position: absolute;
            top: 8px;
            left: 8px;
            background: rgba(34, 197, 94, 0.9);
            color: var(--bg-main);
            padding: 4px 8px;
            border-radius: var(--radius-pill);
            font-size: 10px;
            font-weight: 700;
        }

        .image-actions {
            position: absolute;
            top: 0;
            right: 0;
            display: flex;
            gap: 5px;
            padding: 5px;
            background: rgba(0, 0, 0, 0.7);
            opacity: 0;
            transition: opacity 0.2s;
        }

        .existing-image-item:hover .image-actions {
            opacity: 1;
        }

        .btn-set-primary,
        .btn-delete-image {
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            text-decoration: none;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn-set-primary {
            background: rgba(34, 197, 94, 0.8);
            color: var(--bg-main);
        }

        .btn-set-primary:hover {
            background: rgba(34, 197, 94, 1);
            transform: scale(1.1);
        }

        .btn-delete-image {
            background: rgba(239, 68, 68, 0.8);
            color: white;
        }

        .btn-delete-image:hover {
            background: rgba(239, 68, 68, 1);
            transform: scale(1.1);
        }

        .image-previews-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .image-preview-item {
            position: relative;
            border: 1px solid rgba(148, 163, 184, 0.3);
            border-radius: var(--radius-md);
            overflow: hidden;
            background: rgba(15, 23, 42, 0.5);
        }

        .image-preview-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            display: block;
        }

        .image-preview-item .preview-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .image-preview-item:hover .preview-overlay {
            opacity: 1;
        }

        .preview-overlay button {
            background: rgba(239, 68, 68, 0.8);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: var(--radius-pill);
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
        }

        .preview-overlay button:hover {
            background: rgba(239, 68, 68, 1);
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="manage_projects.php" class="back-link">← Back to Projects</a>
        
        <div class="header">
            <h1>Edit Project</h1>
            <p>Update your project information</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="project-card">
            <form action="update_project.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($project_id); ?>">
                
                <div class="form-group">
                    <label for="title">Project Title *</label>
                    <input type="text" id="title" name="title" required maxlength="150" value="<?php echo htmlspecialchars($title); ?>">
                </div>

                <div class="form-group">
                    <label for="short_description">Short Description</label>
                    <input type="text" id="short_description" name="short_description" maxlength="255" value="<?php echo htmlspecialchars($short_description); ?>">
                    <small>This will be displayed in project cards</small>
                </div>

                <div class="form-group">
                    <label for="description">Full Description *</label>
                    <textarea id="description" name="description" required><?php echo htmlspecialchars($description); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category" required>
                            <option value="">Select category</option>
                            <option value="Web Development" <?php echo $category === 'Web Development' ? 'selected' : ''; ?>>Web Development</option>
                            <option value="Mobile App" <?php echo $category === 'Mobile App' ? 'selected' : ''; ?>>Mobile App</option>
                            <option value="Figma Design" <?php echo $category === 'Figma Design' ? 'selected' : ''; ?>>Figma Design</option>
                            <option value="UI/UX" <?php echo $category === 'UI/UX' ? 'selected' : ''; ?>>UI/UX</option>
                            <option value="Desktop App" <?php echo $category === 'Desktop App' ? 'selected' : ''; ?>>Desktop App</option>
                            <option value="Other" <?php echo $category === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="technologies">Technologies</label>
                        <input type="text" id="technologies" name="technologies" maxlength="255" value="<?php echo htmlspecialchars($technologies); ?>">
                        <small>Separate multiple technologies with commas</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="live_demo_url">Live Demo URL</label>
                    <input type="url" id="live_demo_url" name="live_demo_url" maxlength="255" value="<?php echo htmlspecialchars($live_demo_url); ?>">
                </div>

                <div class="form-group">
                    <label for="github_url">GitHub URL</label>
                    <input type="url" id="github_url" name="github_url" maxlength="255" value="<?php echo htmlspecialchars($github_url); ?>">
                </div>

                <div class="form-group">
                    <label for="figma_url">Figma URL</label>
                    <input type="url" id="figma_url" name="figma_url" maxlength="255" value="<?php echo htmlspecialchars($figma_url); ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="thumbnail">Thumbnail Image</label>
                        <input type="file" id="thumbnail" name="thumbnail" accept="image/*">
                        <small>Leave empty to keep current image</small>
                        <?php if ($thumbnail && file_exists(__DIR__ . '/uploads/' . $thumbnail)): ?>
                            <div class="current-image">
                                Current: <a href="uploads/<?php echo htmlspecialchars($thumbnail); ?>" target="_blank"><?php echo htmlspecialchars($thumbnail); ?></a>
                            </div>
                            <div class="image-preview">
                                <img src="uploads/<?php echo htmlspecialchars($thumbnail); ?>" alt="Current thumbnail">
                            </div>
                        <?php endif; ?>
                        <div class="image-preview" id="thumbnailPreview" style="display: none;">
                            <img id="thumbnailImg" src="" alt="Thumbnail preview">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="banner_image">Banner Image</label>
                        <input type="file" id="banner_image" name="banner_image" accept="image/*">
                        <small>Leave empty to keep current image</small>
                        <?php if ($banner_image && file_exists(__DIR__ . '/uploads/' . $banner_image)): ?>
                            <div class="current-image">
                                Current: <a href="uploads/<?php echo htmlspecialchars($banner_image); ?>" target="_blank"><?php echo htmlspecialchars($banner_image); ?></a>
                            </div>
                            <div class="image-preview">
                                <img src="uploads/<?php echo htmlspecialchars($banner_image); ?>" alt="Current banner">
                            </div>
                        <?php endif; ?>
                        <div class="image-preview" id="bannerPreview" style="display: none;">
                            <img id="bannerImg" src="" alt="Banner preview">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Project Images</label>
                    <?php if (!empty($project_images)): ?>
                        <div class="existing-images-grid">
                            <?php foreach ($project_images as $img): ?>
                                <div class="existing-image-item" data-image-id="<?php echo $img['image_id']; ?>">
                                    <img src="uploads/<?php echo htmlspecialchars($img['image_path']); ?>" alt="<?php echo htmlspecialchars($img['alt_text'] ?? ''); ?>">
                                    <?php if ($img['is_primary']): ?>
                                        <span class="primary-badge">Primary</span>
                                    <?php endif; ?>
                                    <div class="image-actions">
                                        <a href="set_primary_image.php?project_id=<?php echo $project_id; ?>&image_id=<?php echo $img['image_id']; ?>" class="btn-set-primary" title="Set as primary">★</a>
                                        <a href="delete_image.php?project_id=<?php echo $project_id; ?>&image_id=<?php echo $img['image_id']; ?>" class="btn-delete-image" title="Delete image" onclick="return confirm('Are you sure you want to delete this image?');">×</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <input type="file" id="project_images" name="project_images[]" accept="image/*" multiple style="margin-top: 15px;">
                    <small>You can upload multiple images for your project (Max 5MB each)</small>
                    <div id="imagePreviews" class="image-previews-container"></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" id="is_featured" name="is_featured" value="1" <?php echo $is_featured ? 'checked' : ''; ?>>
                            <label for="is_featured">Featured Project</label>
                        </div>
                        <small>Featured projects will be highlighted</small>
                    </div>

                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" id="is_published" name="is_published" value="1" <?php echo $is_published ? 'checked' : ''; ?>>
                            <label for="is_published">Published</label>
                        </div>
                        <small>Unpublished projects won't be visible to others</small>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Project</button>
                    <a href="manage_projects.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Thumbnail preview
        document.getElementById('thumbnail').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('thumbnailImg').src = e.target.result;
                    document.getElementById('thumbnailPreview').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        // Banner preview
        document.getElementById('banner_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('bannerImg').src = e.target.result;
                    document.getElementById('bannerPreview').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        // Multiple images preview
        const projectImagesInput = document.getElementById('project_images');
        if (projectImagesInput) {
            const imagePreviewsContainer = document.getElementById('imagePreviews');
            let imagePreviewIndex = 0;

            projectImagesInput.addEventListener('change', function(e) {
                const files = Array.from(e.target.files);
                if (!imagePreviewsContainer) return;
                imagePreviewsContainer.innerHTML = '';

                files.forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const previewItem = document.createElement('div');
                        previewItem.className = 'image-preview-item';
                        previewItem.dataset.index = imagePreviewIndex++;
                        
                        previewItem.innerHTML = `
                            <img src="${e.target.result}" alt="Preview ${index + 1}">
                            <div class="preview-overlay">
                                <button type="button" onclick="removeImagePreview(this, ${index})">Remove</button>
                            </div>
                        `;
                        
                        imagePreviewsContainer.appendChild(previewItem);
                    };
                    reader.readAsDataURL(file);
                });
            });

            window.removeImagePreview = function(button, index) {
                const previewItem = button.closest('.image-preview-item');
                previewItem.remove();
                
                // Create a new FileList without the removed file
                const dt = new DataTransfer();
                const files = projectImagesInput.files;
                
                for (let i = 0; i < files.length; i++) {
                    if (i !== index) {
                        dt.items.add(files[i]);
                    }
                }
                
                projectImagesInput.files = dt.files;
            };
        }
    </script>
</body>
</html>










