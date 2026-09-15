<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

class Booking
{
    /**
     * Create a booking for a given slot, guaranteeing no double-booking
     * even under concurrent requests.
     *
     * Strategy:
     *  1. Start a DB transaction.
     *  2. SELECT ... FOR UPDATE locks the target slot row so that a
     *     concurrent request must wait until this transaction commits.
     *  3. Re-check the slot status AFTER acquiring the lock (not before) -
     *     this closes the classic "check-then-act" race condition.
     *  4. If still available -> mark as booked + insert booking + log,
     *     then COMMIT. Otherwise -> ROLLBACK and return an error.
     *
     * Returns: [success(bool), message(string), bookingRef(string|null)]
     */
    public static function createBooking($userId, $slotId, $notes = '')
    {
        $db = getDB();

        try {
            $db->beginTransaction();

            // Step 1: lock the row
            $stmt = $db->prepare("SELECT * FROM time_slots WHERE slot_id = ? FOR UPDATE");
            $stmt->execute([$slotId]);
            $slot = $stmt->fetch();

            if (!$slot) {
                $db->rollBack();
                return [false, 'The selected time slot no longer exists.', null];
            }

            // Step 2: re-check availability AFTER locking
            if ($slot['status'] !== 'available') {
                $db->rollBack();
                return [false, 'Sorry, this slot was just booked by another user. Please choose another time.', null];
            }

            // Step 3: mark slot as booked
            $upd = $db->prepare("UPDATE time_slots SET status = 'booked' WHERE slot_id = ?");
            $upd->execute([$slotId]);

            // Step 4: create the booking record
            $ref = generateBookingRef();
            $ins = $db->prepare(
                "INSERT INTO bookings (booking_ref, user_id, slot_id, status, notes) VALUES (?,?,?,'confirmed',?)"
            );
            $ins->execute([$ref, $userId, $slotId, $notes]);
            $bookingId = $db->lastInsertId();

            // Step 5: write audit log
            $log = $db->prepare(
                "INSERT INTO booking_logs (booking_id, action, performed_by) VALUES (?, 'created', ?)"
            );
            $log->execute([$bookingId, "user#$userId"]);

            $db->commit();
            return [true, 'Booking confirmed successfully.', $ref];

        } catch (Exception $e) {
            $db->rollBack();
            return [false, 'An error occurred while processing your booking: ' . $e->getMessage(), null];
        }
    }

    public static function cancelBooking($bookingId, $userId = null, $isAdmin = false)
    {
        $db = getDB();
        try {
            $db->beginTransaction();

            $sql = "SELECT * FROM bookings WHERE booking_id = ?";
            $params = [$bookingId];
            if (!$isAdmin) {
                $sql .= " AND user_id = ?";
                $params[] = $userId;
            }
            $stmt = $db->prepare($sql . " FOR UPDATE");
            $stmt->execute($params);
            $booking = $stmt->fetch();

            if (!$booking) {
                $db->rollBack();
                return [false, 'Booking not found or you do not have permission to cancel it.'];
            }
            if ($booking['status'] === 'cancelled') {
                $db->rollBack();
                return [false, 'This booking is already cancelled.'];
            }

            $db->prepare("UPDATE bookings SET status = 'cancelled' WHERE booking_id = ?")
               ->execute([$bookingId]);

            // free up the slot again
            $db->prepare("UPDATE time_slots SET status = 'available' WHERE slot_id = ?")
               ->execute([$booking['slot_id']]);

            $performer = $isAdmin ? 'admin' : "user#$userId";
            $db->prepare("INSERT INTO booking_logs (booking_id, action, performed_by) VALUES (?, 'cancelled', ?)")
               ->execute([$bookingId, $performer]);

            $db->commit();
            return [true, 'Booking cancelled successfully.'];

        } catch (Exception $e) {
            $db->rollBack();
            return [false, 'An error occurred: ' . $e->getMessage()];
        }
    }

    public static function markCompleted($bookingId)
    {
        $db = getDB();
        $stmt = $db->prepare("UPDATE bookings SET status = 'completed' WHERE booking_id = ?");
        $stmt->execute([$bookingId]);
        $db->prepare("INSERT INTO booking_logs (booking_id, action, performed_by) VALUES (?, 'completed', 'admin')")
           ->execute([$bookingId]);
    }

    public static function forUser($userId)
    {
        $db = getDB();
        $stmt = $db->prepare(
            "SELECT b.*, ts.slot_date, ts.start_time, ts.end_time,
                    s.service_name, s.provider_name, s.location, s.price, c.category_name
             FROM bookings b
             JOIN time_slots ts ON b.slot_id = ts.slot_id
             JOIN services s ON ts.service_id = s.service_id
             JOIN service_categories c ON s.category_id = c.category_id
             WHERE b.user_id = ?
             ORDER BY ts.slot_date DESC, ts.start_time DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function all($statusFilter = null)
    {
        $db = getDB();
        $sql = "SELECT b.*, ts.slot_date, ts.start_time, ts.end_time,
                       s.service_name, s.provider_name, c.category_name,
                       u.full_name, u.email
                FROM bookings b
                JOIN time_slots ts ON b.slot_id = ts.slot_id
                JOIN services s ON ts.service_id = s.service_id
                JOIN service_categories c ON s.category_id = c.category_id
                JOIN users u ON b.user_id = u.user_id";
        $params = [];
        if ($statusFilter) {
            $sql .= " WHERE b.status = ?";
            $params[] = $statusFilter;
        }
        $sql .= " ORDER BY ts.slot_date DESC, ts.start_time DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function find($bookingId)
    {
        $db = getDB();
        $stmt = $db->prepare(
            "SELECT b.*, ts.slot_date, ts.start_time, ts.end_time, ts.service_id,
                    s.service_name, s.provider_name, s.location, s.price, c.category_name,
                    u.full_name, u.email
             FROM bookings b
             JOIN time_slots ts ON b.slot_id = ts.slot_id
             JOIN services s ON ts.service_id = s.service_id
             JOIN service_categories c ON s.category_id = c.category_id
             JOIN users u ON b.user_id = u.user_id
             WHERE b.booking_id = ?"
        );
        $stmt->execute([$bookingId]);
        return $stmt->fetch();
    }

    public static function stats()
    {
        $db = getDB();
        $stats = [];
        $stats['total'] = $db->query("SELECT COUNT(*) c FROM bookings")->fetch()['c'];
        $stats['confirmed'] = $db->query("SELECT COUNT(*) c FROM bookings WHERE status='confirmed'")->fetch()['c'];
        $stats['cancelled'] = $db->query("SELECT COUNT(*) c FROM bookings WHERE status='cancelled'")->fetch()['c'];
        $stats['completed'] = $db->query("SELECT COUNT(*) c FROM bookings WHERE status='completed'")->fetch()['c'];
        $stats['customers'] = $db->query("SELECT COUNT(*) c FROM users WHERE role='customer'")->fetch()['c'];
        $stats['services'] = $db->query("SELECT COUNT(*) c FROM services WHERE is_active=1")->fetch()['c'];
        return $stats;
    }
}
