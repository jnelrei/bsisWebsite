<?php
// Include database connection
require_once('../../production/includes/db.php');

// Set timezone to Philippine Time
date_default_timezone_set('Asia/Manila');

header('Content-Type: application/json');

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'daily';

$labels = [];
$quantities = [];

try {
  // Check if created_at column exists in order_items table
  $has_date_column = false;
  try {
    $test_stmt = $pdo->query("SELECT created_at FROM order_items LIMIT 1");
    $has_date_column = true;
  } catch (PDOException $e) {
    $has_date_column = false;
  }

  if ($has_date_column) {
    // Use date-based filtering
    switch ($filter) {
      case 'daily':
        // All 24 hours - hourly data
        $stmt = $pdo->query("
          SELECT 
            HOUR(created_at) as hour_time,
            SUM(quantity) as total_quantity
          FROM order_items 
          WHERE DATE(created_at) = CURDATE()
          GROUP BY HOUR(created_at)
          ORDER BY hour_time ASC
        ");
        $order_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Create an array with all 24 hours initialized to 0
        $hourly_data = array_fill(0, 24, 0);
        
        // Fill in the actual data
        foreach ($order_data as $row) {
          $hour = (int)$row['hour_time'];
          $hourly_data[$hour] = (int)$row['total_quantity'];
        }
        
        // Format for display
        for ($i = 0; $i < 24; $i++) {
          $labels[] = date('g A', strtotime($i . ':00'));
          $quantities[] = $hourly_data[$i];
        }
        break;

      case 'weekly':
        // Current week (Sunday to Saturday)
        $current_day = date('w');
        $days_since_sunday = $current_day;
        $week_start = date('Y-m-d', strtotime("-{$days_since_sunday} days"));
        $week_end = date('Y-m-d', strtotime($week_start . " +6 days"));
        
        $stmt = $pdo->prepare("
          SELECT 
            DATE(created_at) as order_date,
            SUM(quantity) as total_quantity
          FROM order_items 
          WHERE DATE(created_at) >= ? AND DATE(created_at) <= ?
          GROUP BY DATE(created_at)
          ORDER BY order_date ASC
        ");
        $stmt->execute([$week_start, $week_end]);
        $order_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Create array indexed by date
        $weekly_data = [];
        foreach ($order_data as $row) {
          $weekly_data[$row['order_date']] = (int)$row['total_quantity'];
        }
        
        // Generate all 7 days from Sunday to Saturday
        $day_names = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        
        for ($i = 0; $i < 7; $i++) {
          $date = date('Y-m-d', strtotime($week_start . " +{$i} days"));
          $labels[] = $day_names[$i];
          $quantities[] = isset($weekly_data[$date]) ? $weekly_data[$date] : 0;
        }
        break;

      case 'monthly':
        // Current month - daily data
        $stmt = $pdo->query("
          SELECT 
            DATE(created_at) as order_date,
            SUM(quantity) as total_quantity
          FROM order_items 
          WHERE MONTH(created_at) = MONTH(CURDATE()) 
          AND YEAR(created_at) = YEAR(CURDATE())
          GROUP BY DATE(created_at)
          ORDER BY order_date ASC
        ");
        $order_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Create array indexed by date
        $monthly_data = [];
        foreach ($order_data as $row) {
          $monthly_data[$row['order_date']] = (int)$row['total_quantity'];
        }
        
        // Get the number of days in current month
        $days_in_month = date('t');
        $current_month = date('Y-m');
        
        // Generate all days from 1 to last day of month
        for ($day = 1; $day <= $days_in_month; $day++) {
          $date = $current_month . '-' . sprintf('%02d', $day);
          $labels[] = date('M d', strtotime($date));
          $quantities[] = isset($monthly_data[$date]) ? $monthly_data[$date] : 0;
        }
        break;

      case 'yearly':
        // Current year - monthly data
        $stmt = $pdo->query("
          SELECT 
            MONTH(created_at) as month_num,
            SUM(quantity) as total_quantity
          FROM order_items 
          WHERE YEAR(created_at) = YEAR(CURDATE())
          GROUP BY MONTH(created_at)
          ORDER BY month_num ASC
        ");
        $order_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Create array indexed by month number
        $yearly_data = array_fill(1, 12, 0);
        foreach ($order_data as $row) {
          $yearly_data[(int)$row['month_num']] = (int)$row['total_quantity'];
        }
        
        // Generate all 12 months
        $month_names = ['January', 'February', 'March', 'April', 'May', 'June', 
                        'July', 'August', 'September', 'October', 'November', 'December'];
        
        for ($month = 1; $month <= 12; $month++) {
          $labels[] = $month_names[$month - 1];
          $quantities[] = $yearly_data[$month];
        }
        break;

      default:
        // Default to daily
        $stmt = $pdo->query("
          SELECT 
            HOUR(created_at) as hour_time,
            SUM(quantity) as total_quantity
          FROM order_items 
          WHERE DATE(created_at) = CURDATE()
          GROUP BY HOUR(created_at)
          ORDER BY hour_time ASC
        ");
        $order_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $hourly_data = array_fill(0, 24, 0);
        foreach ($order_data as $row) {
          $hour = (int)$row['hour_time'];
          $hourly_data[$hour] = (int)$row['total_quantity'];
        }
        
        for ($i = 0; $i < 24; $i++) {
          $labels[] = date('g A', strtotime($i . ':00'));
          $quantities[] = $hourly_data[$i];
        }
        break;
    }
  } else {
    // If no date column, fall back to simple ID-based display
    $stmt = $pdo->query("SELECT id, quantity FROM order_items ORDER BY id ASC LIMIT 100");
    $order_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($order_data as $item) {
      $labels[] = 'Order #' . $item['id'];
      $quantities[] = (int)($item['quantity'] ?? 0);
    }
  }

  // Prepare date range info for display
  $date_range = '';
  if ($has_date_column && $filter == 'weekly' && !empty($labels)) {
    // Extract start and end from the week calculation
    $current_day = date('w');
    $days_since_sunday = $current_day;
    $week_start = date('Y-m-d', strtotime("-{$days_since_sunday} days"));
    $week_end = date('Y-m-d', strtotime($week_start . " +6 days"));
    $start_date = date('M d', strtotime($week_start));
    $end_date = date('M d', strtotime($week_end));
    $date_range = $start_date . ' to ' . $end_date;
  }

  echo json_encode([
    'success' => true,
    'labels' => $labels,
    'data' => $quantities,
    'date_range' => $date_range
  ]);

} catch (PDOException $e) {
  echo json_encode([
    'success' => false,
    'message' => 'Error fetching order items data: ' . $e->getMessage(),
    'labels' => [],
    'data' => []
  ]);
}
?>
