<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/TimeSlot.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /customer/browse.php');
    exit;
}

$slotId = $_POST['slot_id'] ?? 0;
$slot = TimeSlot::find($slotId);

if (!$slot) {
    flash('error', 'Invalid time slot selected.');
    header('Location: /customer/browse.php');
    exit;
}

[$success, $message, $ref] = Booking::createBooking(currentUserId(), $slotId);

if ($success) {
    flash('success', "$message Your booking reference is $ref.");
    header('Location: /customer/bookings.php');
} else {
    flash('error', $message);
    header('Location: /customer/service.php?id=' . $slot['service_id']);
}
exit;
