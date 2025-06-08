<?php
namespace Models;

use PDO;
use PDOException;

class Appointment {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Δημιουργεί νέο ραντεβού. Επιστρέφει το ID του νέου ραντεβού.
     */
    public function create(array $data): int {
        $sql = "
            INSERT INTO `appointment`
              (appointment_date, appointment_time, reason, problem_description, car_serial, customer_id, mechanic_id, status, created_at)
            VALUES
              (:appointment_date, :appointment_time, :reason, :problem_description, :car_serial, :customer_id, :mechanic_id, 'CREATED', NOW())
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':appointment_date'    => $data['appointment_date'],
            ':appointment_time'    => $data['appointment_time'],
            ':reason'              => $data['reason'],
            ':problem_description' => $data['problem_description'],
            ':car_serial'          => $data['car_serial'],
            ':customer_id'         => $data['customer_id'],
            ':mechanic_id'         => $data['mechanic_id'],
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Επιστρέφει ένα ραντεβού βάσει του ID, ή null αν δεν βρεθεί.
     */
    public function findById(int $id): ?array {
        $sql = "
            SELECT 
              a.*,
              c.model AS car_model, c.brand AS car_brand,
              u_c.username AS customer_username, u_c.last_name AS customer_last_name,
              u_m.username AS mechanic_username, u_m.last_name AS mechanic_last_name
            FROM `appointment` a
            JOIN `car` c ON a.car_serial = c.serial_number
            JOIN `user` u_c ON a.customer_id = u_c.id
            JOIN `user` u_m ON a.mechanic_id = u_m.id
            WHERE a.id = :id
            LIMIT 1
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Επιστρέφει τους χρόνους (slots) που είναι ήδη κλεισμένα για έναν μηχανικό σε συγκεκριμένη ημερομηνία.
     * Χρησιμοποιείται για έλεγχο double-booking.
     */
    public function getByMechanicAndDate(int $mechanicId, string $date): array {
        $sql = "
            SELECT appointment_time
            FROM `appointment`
            WHERE mechanic_id = :mechId
              AND appointment_date = :date
              AND status != 'CANCELLED'
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':mechId' => $mechanicId,
            ':date'   => $date
        ]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'appointment_time');
    }

    /**
     * Επιστρέφει όλα τα ραντεβού για έναν μηχανικό, μαζί με:
     *   - appointment_time
     *   - brand, model του αυτοκινήτου
     *   - cust_last, cust_first (επίθετο και όνομα πελάτη)
     *   - status
     *
     * Αν δοθεί $filterDate, φιλτράρει μόνο από αυτή την ημερομηνία και μετά.
     * Δεν έχει πραγματικό pagination (ορίσαμε limit = 100000 απλώς για να πάρουμε όλα).
     */
    public function getForMechanic(int $mechanicId, ?string $filterDate = null): array {
        $sql = "
            SELECT 
              a.appointment_time,
              a.status,
              c.brand,
              c.model,
              u_c.last_name AS cust_last,
              u_c.first_name AS cust_first
            FROM `appointment` a
            JOIN `car` c ON a.car_serial = c.serial_number
            JOIN `user` u_c ON a.customer_id = u_c.id
            WHERE a.mechanic_id = :mechId
        ";
        $params = [':mechId' => $mechanicId];

        if ($filterDate) {
            $sql .= " AND a.appointment_date >= :filterDate";
            $params[':filterDate'] = $filterDate;
        }

        $sql .= " ORDER BY a.appointment_date ASC, a.appointment_time ASC";

        // Προσθέτουμε μεγάλο LIMIT ώστε να πάρουμε όλα (χωρίς πραγματικό pagination)
        $sql .= " LIMIT 100000 OFFSET 0";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Μεταθέτει (reschedule) ένα ραντεβού (μόνο αν status='CREATED').
     * Επιστρέφει true αν έγινε update.
     */
    public function reschedule(int $id, string $newDate, string $newTime): bool {
        $sql = "
            UPDATE `appointment`
            SET appointment_date = :newDate, appointment_time = :newTime
            WHERE id = :id AND status = 'CREATED'
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':newDate' => $newDate,
            ':newTime' => $newTime,
            ':id'      => $id
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Αλλάζει την κατάσταση (status) ενός ραντεβού.
     * Επιτρεπόμενες καταστάσεις: CREATED, IN_PROGRESS, COMPLETED, CANCELLED
     */
    public function changeStatus(int $id, string $status): bool {
        $allowed = ['CREATED','IN_PROGRESS','COMPLETED','CANCELLED'];
        if (!in_array($status, $allowed, true)) {
            throw new \InvalidArgumentException("Invalid status: $status");
        }
        $sql = "
            UPDATE `appointment`
            SET status = :status
            WHERE id = :id
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':status' => $status,
            ':id'     => $id
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Ακυρώνει (cancel) ένα ραντεβού, δηλαδή θέτει status='CANCELLED'.
     */
    public function cancel(int $id): bool {
        return $this->changeStatus($id, 'CANCELLED');
    }

    /**
     * Αναζήτηση ραντεβού με δυναμικά κριτήρια:
     *   - customer_id (ακριβές)
     *   - mechanic_id (ακριβές)
     *   - date_from (appointment_date >= date_from)
     *   - date_to   (appointment_date <= date_to)
     *   - status    (ακριβές)
     *
     * Υποστηρίζει pagination μέσω των $limit και $offset.
     * Επιστρέφει array από associative arrays με πεδία:
     *   id, appointment_date, appointment_time, reason, status, car_serial, customer_last_name.
     */
    public function search(array $criteria = [], int $limit = 10, int $offset = 0): array {
        $sql = "
            SELECT 
              a.id,
              a.appointment_date,
              a.appointment_time,
              a.reason,
              a.status,
              a.car_serial,
              u_c.last_name AS customer_last_name
            FROM `appointment` a
            JOIN `user` u_c ON a.customer_id = u_c.id
            WHERE 1
        ";
        $params = [];

        if (!empty($criteria['customer_id'])) {
            $sql .= " AND a.customer_id = :customer_id";
            $params[':customer_id'] = $criteria['customer_id'];
        }
        if (!empty($criteria['mechanic_id'])) {
            $sql .= " AND a.mechanic_id = :mechanic_id";
            $params[':mechanic_id'] = $criteria['mechanic_id'];
        }
        if (!empty($criteria['date_from'])) {
            $sql .= " AND a.appointment_date >= :date_from";
            $params[':date_from'] = $criteria['date_from'];
        }
        if (!empty($criteria['date_to'])) {
            $sql .= " AND a.appointment_date <= :date_to";
            $params[':date_to'] = $criteria['date_to'];
        }
        if (!empty($criteria['status'])) {
            $sql .= " AND a.status = :status";
            $params[':status'] = $criteria['status'];
        }

        $sql .= " ORDER BY a.appointment_date ASC, a.appointment_time ASC";
        $sql .= " LIMIT :limit OFFSET :offset";
        $params[':limit']  = $limit;
        $params[':offset'] = $offset;

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            if ($key === ':limit' || $key === ':offset') {
                $stmt->bindValue($key, $val, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $val);
            }
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Μετράει πόσα ραντεβού ικανοποιούν τα κριτήρια (χωρίς limit/offset).
     * Επιστρέφει integer με το πλήθος.
     */
    public function countAll(array $criteria = []): int {
        $sql = "
            SELECT COUNT(*) AS cnt
            FROM `appointment` a
            JOIN `user` u_c ON a.customer_id = u_c.id
            WHERE 1
        ";
        $params = [];

        if (!empty($criteria['customer_id'])) {
            $sql .= " AND a.customer_id = :customer_id";
            $params[':customer_id'] = $criteria['customer_id'];
        }
        if (!empty($criteria['mechanic_id'])) {
            $sql .= " AND a.mechanic_id = :mechanic_id";
            $params[':mechanic_id'] = $criteria['mechanic_id'];
        }
        if (!empty($criteria['date_from'])) {
            $sql .= " AND a.appointment_date >= :date_from";
            $params[':date_from'] = $criteria['date_from'];
        }
        if (!empty($criteria['date_to'])) {
            $sql .= " AND a.appointment_date <= :date_to";
            $params[':date_to'] = $criteria['date_to'];
        }
        if (!empty($criteria['status'])) {
            $sql .= " AND a.status = :status";
            $params[':status'] = $criteria['status'];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['cnt'];
    }
}
