<?php
require_once __DIR__ . '/../config/db.php';

class TimeSlot
{
    /**
     * Auto-generate time slots for a service between two dates,
     * based on working hours 09:00 - 17:00 and the service duration.
     * Skips slots that already exist (UNIQUE constraint safe).
     */
    public static function generateSlots($serviceId, $durationMinutes, $startDate, $endDate, $dailyStart = '09:00:00', $dailyEnd = '17:00:00')
    {
        $db = getDB();
        $created = 0;
        $current = strtotime($startDate);
        $end = strtotime($endDate);

        $insert = $db->prepare(
            "INSERT IGNORE INTO time_slots (service_id, slot_date, start_time, end_time, status)
             VALUES (?,?,?,?,'available')"
        );

        while ($current <= $end) {
            $dayOfWeek = date('N', $current); // 6 = Sat, 7 = Sun
            if ($dayOfWeek < 6) { // Sunday-Thursday working week (Malaysia/MENA typical academic context)
                $slotStart = strtotime(date('Y-m-d', $current) . ' ' . $dailyStart);
                $slotEnd = strtotime(date('Y-m-d', $current) . ' ' . $dailyEnd);

                while ($slotStart + ($durationMinutes * 60) <= $slotEnd) {
                    $sStart = date('H:i:s', $slotStart);
                    $sEnd = date('H:i:s', $slotStart + $durationMinutes * 60);
                    $insert->execute([$serviceId, date('Y-m-d', $current), $sStart, $sEnd]);
                    $created += $insert->rowCount();
                    $slotStart += $durationMinutes * 60;
                }
            }
            $current = strtotime('+1 day', $current);
        }
        return $created;
    }

    public static function availableForService($serviceId, $fromDate = null)
    {
        $db = getDB();
        $fromDate = $fromDate ?: date('Y-m-d');
        $stmt = $db->prepare(
            "SELECT * FROM time_slots
             WHERE service_id = ? AND status = 'available' AND slot_date >= ?
             ORDER BY slot_date, start_time"
        );
        $stmt->execute([$serviceId, $fromDate]);
        return $stmt->fetchAll();
    }

    public static function find($slotId)
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM time_slots WHERE slot_id = ?");
        $stmt->execute([$slotId]);
        return $stmt->fetch();
    }

    public static function setStatus($slotId, $status)
    {
        $db = getDB();
        $stmt = $db->prepare("UPDATE time_slots SET status = ? WHERE slot_id = ?");
        return $stmt->execute([$status, $slotId]);
    }
}
