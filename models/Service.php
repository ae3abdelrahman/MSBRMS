<?php
require_once __DIR__ . '/../config/db.php';

class Service
{
    public static function allCategories()
    {
        $db = getDB();
        return $db->query("SELECT * FROM service_categories ORDER BY category_name")->fetchAll();
    }

    public static function findCategory($id)
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM service_categories WHERE category_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function createCategory($name, $desc, $icon)
    {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO service_categories (category_name, description, icon) VALUES (?,?,?)");
        return $stmt->execute([$name, $desc, $icon]);
    }

    public static function allServices($activeOnly = true)
    {
        $db = getDB();
        $sql = "SELECT s.*, c.category_name FROM services s
                JOIN service_categories c ON s.category_id = c.category_id";
        if ($activeOnly) $sql .= " WHERE s.is_active = 1";
        $sql .= " ORDER BY c.category_name, s.service_name";
        return $db->query($sql)->fetchAll();
    }

    public static function byCategory($categoryId)
    {
        $db = getDB();
        $stmt = $db->prepare(
            "SELECT * FROM services WHERE category_id = ? AND is_active = 1 ORDER BY service_name"
        );
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll();
    }

    public static function find($id)
    {
        $db = getDB();
        $stmt = $db->prepare(
            "SELECT s.*, c.category_name FROM services s
             JOIN service_categories c ON s.category_id = c.category_id
             WHERE s.service_id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create($data)
    {
        $db = getDB();
        $stmt = $db->prepare(
            "INSERT INTO services (category_id, provider_name, service_name, description, duration_minutes, price, location)
             VALUES (?,?,?,?,?,?,?)"
        );
        return $stmt->execute([
            $data['category_id'], $data['provider_name'], $data['service_name'],
            $data['description'], $data['duration_minutes'], $data['price'], $data['location']
        ]);
    }

    public static function update($id, $data)
    {
        $db = getDB();
        $stmt = $db->prepare(
            "UPDATE services SET category_id=?, provider_name=?, service_name=?, description=?,
             duration_minutes=?, price=?, location=?, is_active=? WHERE service_id=?"
        );
        return $stmt->execute([
            $data['category_id'], $data['provider_name'], $data['service_name'],
            $data['description'], $data['duration_minutes'], $data['price'],
            $data['location'], $data['is_active'], $id
        ]);
    }

    public static function delete($id)
    {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM services WHERE service_id = ?");
        return $stmt->execute([$id]);
    }
}
