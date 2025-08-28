<?php
session_start();
include 'config.php';

// Pagination, search, category filter (same as before)
$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

$conditions = ["status='approved'"];
$params = [];
$types = "";

if ($search !== '') {
    $conditions[] = "title LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}
if ($category !== '') {
    $conditions[] = "category = ?";
    $params[] = $category;
    $types .= "s";
}

$where = implode(" AND ", $conditions);

// Count total videos
$countSql = "SELECT COUNT(*) as total FROM videos WHERE $where";
$stmt = $conn->prepare($countSql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$totalResult = $stmt->get_result()->fetch_assoc();
$totalPages = ceil($totalResult['total'] / $limit);

// Fetch videos with limit and offset
$sql = "SELECT * FROM videos WHERE $where ORDER BY uploaded_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Patronix Showcase</title>
<style>
  body {
    font-family: Arial, sans-serif;
    background: #f6f8fb;
    margin: 0; padding: 0;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }
  header {
    background: #1e2a78;
    color: white;
    padding: 20px;
    text-align: center;
  }
  nav {
    text-align: center;
    margin: 15px 0;
  }
  nav a {
    margin: 0 10px;
    color: #1e2a78;
    text-decoration: none;
    font-weight: bold;
  }
  .search-bar {
    max-width: 600px;
    margin: 20px auto;
    text-align: center;
  }
  .search-bar input, .search-bar select {
    padding: 8px;
    margin: 0 5px 10px;
    width: 40%;
    font-size: 1rem;
  }

  /* Container to center video grid with background */
  .main-content {
    background: white;
    max-width: 1100px;
    margin: 0 auto 40px;
    padding: 30px 20px 40px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    flex-grow: 1;
  }

  .video-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill,minmax(280px,1fr));
    gap: 20px;
  }
  .video-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgb(0 0 0 / 0.15);
    overflow: hidden;
    display: flex;
    flex-direction: column;
  }
  .video-card video {
    width: 100%;
    border-bottom: 1px solid #eee;
    border-radius: 10px 10px 0 0;
    cursor: pointer;
  }
  .video-info {
    padding: 15px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }
  .video-title {
    font-weight: bold;
    font-size: 1.1rem;
    margin-bottom: 8px;
    min-height: 44px;
  }
  .video-category {
    color: #555;
    font-size: 0.9rem;
    margin-bottom: 12px;
  }
  .video-buttons {
    display: flex;
    justify-content: space-between;
  }
  .btn {
    padding: 7px 12px;
    border-radius: 6px;
    font-size: 0.9rem;
    text-decoration: none;
    color: white;
    cursor: pointer;
    user-select: none;
  }
  .btn-view {
    background-color: #1e2a78;
  }
  .btn-view:hover {
    background-color: #12205c;
  }
  .btn-like {
    background-color: #e74c3c;
    display: flex;
    align-items: center;
    gap: 6px;
  }
  .btn-like:hover {
    background-color: #c0392b;
  }
  .btn-donate {
    background-color: #27ae60;
  }
  .btn-donate:hover {
    background-color: #1e8449;
  }
  .btn span.count {
    background: rgba(255 255 255 / 0.25);
    border-radius: 50%;
    padding: 2px 8px;
    font-weight: bold;
    font-size: 0.85rem;
  }

  /* Pagination */
  .pagination {
    text-align: center;
    margin-top: 30px;
  }
  .pagination a {
    margin: 0 8px;
    padding: 8px 15px;
    background: #1e2a78;
    color: white;
    border-radius: 6px;
    text-decoration: none;
    font-weight: bold;
  }
  .pagination a.active {
    background: #12205c;
  }
  .pagination a:hover:not(.active) {
    background: #3851a3;
  }

  /* Footer */
  footer {
    background: #1e2a78;
    color: white;
    text-align: center;
    padding: 15px 20px;
    font-size: 0.9rem;
  }
  .video-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    width: 900;
    gap: 20px;
    padding: 20px;
  }

  .video-card {
    width: 40%; /* 4 per row approximately */
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 10px;
    box-shadow: 4 2px 5px rgba(15, 1, 1, 0.1);
    transition: transform 0.3s;
  }

  .video-card:hover {
    transform: scale(1.02);
  }

  .video-card video {
    width: 100%;
    border-radius: 8px;
  }

  .video-card h3 {
    font-size: 16px;
    margin: 10px 0 5px;
  }

  .actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 5px;
  }

  .actions a, .actions span {
    font-size: 14px;
    text-decoration: none;
    padding: 5px 10px;
    border-radius: 5px;
  }

  .like-btn { background: #ff4d4d; color: white; }
  .donate-btn { background: #28a745; color: white; }
  .view-btn { background: #007bff; color: white; }

  @media (max-width: 768px) {
    .video-card {
      width: 45%;
    }
  }

  @media (max-width: 480px) {
    .video-card {
      width: 100%;
    }
  }
  .page-wrapper {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  gap: 20px;
  padding: 20px;
}

.ad-column {
  width: 160px; /* standard ad width */
  min-height: 500px;
  background-color: #f0f0f0;
  text-align: center;
  font-size: 0.9rem;
  padding: 10px;
  border-radius: 10px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  position: sticky;
  top: 20px;
}
</style>
</head>
<body>

<header>
  <h1>🎬 Patronix Talent Showcase</h1>
  <p>Watch & Support Talented Individuals from Club Rafiki</p>
</header>

<nav>
  <a href="user/user_register.php">Register</a>
  <a href="user/user_login.php">Login</a>
  <a href="user/upload_video.php">Upload</a>
  <a href="admin/admin_dashboard.php">Admin</a>
</nav>

<div class="search-bar">
  <form method="GET" action="">
    <input type="text" name="search" placeholder="Search by title..." value="<?= htmlspecialchars($search) ?>">
    <select name="category">
      <option value="">All Categories</option>
      <option value="Music" <?= $category == 'Music' ? 'selected' : '' ?>>Music</option>
      <option value="Dance" <?= $category == 'Dance' ? 'selected' : '' ?>>Dance</option>
      <option value="Drama" <?= $category == 'Drama' ? 'selected' : '' ?>>Drama</option>
    </select>
    <button type="submit">Search</button>
  </form>
</div>

<!-- Wrap video grid in main-content container -->
<div class="page-wrapper">
  <div class="ad-column">
    <!-- Left ad -->
    <!-- <p><strong>Ad Space</strong></p> -->
    <img src="ads/3.webp" alt="Left Ad" width="150">
      <img src="ads/2.jpg" alt="Left Ad" width="150">
  </div>

  <div class="main-content">
    <div class="video-container">
      <?php while ($video = $result->fetch_assoc()) : ?>
        <div class="video-card">
          <video controls autoplay muted loop
            onclick="window.location.href='user/view_video.php?id=<?= $video['id'] ?>'">
            <source src="uploads/<?= htmlspecialchars($video['filename']) ?>" type="video/mp4">
          </video>

          <div class="video-info">
            <div class="video-title"><?= htmlspecialchars($video['title']) ?></div>
            <div class="video-category"><?= htmlspecialchars($video['category']) ?></div>
            <div class="video-buttons">
              <a href="user/view_video.php?id=<?= $video['id'] ?>" class="btn btn-view">View
                <span class="count"><?= (int)$video['views'] ?></span>
              </a>

              <a href="#" class="btn btn-like">
                ❤️ Like <span class="count"><?= (int)$video['likes'] ?></span>
              </a>

              <a href="<?= isset($_SESSION['user_id']) ? "donate_now.php?id={$video['id']}" : "user/user_login.php" ?>" class="btn btn-donate">
                💰 Donate
              </a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>

    <div class="pagination">
      <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>">← Prev</a>
      <?php endif; ?>
      <span> Page <?= $page ?> of <?= $totalPages ?> </span>
      <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>">Next →</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="ad-column">
    <!-- Right ad -->
    <!-- <p><strong>Ad Space</strong></p> -->
    <img src="ads/5.jpg" alt="Right Ad" width="150">
    <img src="ads/4.webp" alt="Left Ad" width="150">
  </div>
</div>

<footer>
  &copy; <?= date('Y') ?> Patronix Showcase. All rights reserved. Developed by Brenda.
</footer>

</body>
</html>