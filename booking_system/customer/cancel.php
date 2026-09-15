<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/Booking.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = $_POST['booking_id'] ?? 0;
    [$success, $message] = Booking::cancelBooking($bookingId, currentUserId(), false);
    flash($success ? 'success' : 'error', $message);
}
header('Location: /customer/bookings.php');
exit;
