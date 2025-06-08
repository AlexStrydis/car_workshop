<?php
namespace Models;

use PDO;

class Car {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Δημιουργεί νέο αυτοκίνητο.
     * Επιστρέφει true αν εισήχθη επιτυχώς.
     */
    public function create(array $data): bool {
        $sql = "
            INSERT INTO `car`
              (serial_number, model, brand, type, drive_type, door_count, wheel_count, production_date, acquisition_year, owner_id)
            VALUES
              (:serial, :model, :brand, :type, :drive, :doors, :wheels, :prod_date, :acq_year, :owner)
        ";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':serial'    => $data['serial_number'],
            ':model'     => $data['model'],
            ':brand'     => $data['brand'],
            ':type'      => $data['type'],
            ':drive'     => $data['drive_type'],
            ':doors'     => $data['door_count'],
            ':wheels'    => $data['wheel_count'],
            ':prod_date' => $data['production_date'],
            ':acq_year'  => $data['acquisition_year'],
            ':owner'     => $data['owner_id'],
        ]);
    }

    /**
     * Επιστρέφει το αυτοκίνητο βάσει serial_number ή null.
     */
    public function findBySerial(string $serial): ?array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM `car`
            WHERE serial_number = :serial
        ");
        $stmt->execute([':serial' => $serial]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Ενημερώνει ένα αυτοκίνητο. Επιστρέφει true αν άλλαξε.
     */
    public function update(string $serial, array $data): bool {
        $sql = "
            UPDATE `car` SET
              model            = :model,
              brand            = :brand,
              type             = :type,
              drive_type       = :drive,
              door_count       = :doors,
              wheel_count      = :wheels,
              production_date  = :prod_date,
              acquisition_year = :acq_year
            WHERE serial_number = :serial
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':model'     => $data['model'],
            ':brand'     => $data['brand'],
            ':type'      => $data['type'],
            ':drive'     => $data['drive_type'],
            ':doors'     => $data['door_count'],
            ':wheels'    => $data['wheel_count'],
            ':prod_date' => $data['production_date'],
            ':acq_year'  => $data['acquisition_year'],
            ':serial'    => $serial,
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Διαγράφει το αυτοκίνητο. Επιστρέφει true αν έγινε delete.
     */
    public function delete(string $serial): bool {
        $stmt = $this->pdo->prepare("DELETE FROM `car` WHERE serial_number = :serial");
        $stmt->execute([':serial' => $serial]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Αναζήτηση αυτοκινήτων με κριτήρια:
     *   - owner_id (προαιρετικό)
     *   - serial (partial, LIKE)
     *   - model (partial, LIKE)
     *   - brand (exact match)
     *
     * Αν δοθούν $limit και $offset, εφαρμόζονται για pagination.
     */
    public function search(array $criteria = [], ?int $limit = null, ?int $offset = null): array {
        $sql = "SELECT * FROM `car` WHERE 1";
        $params = [];

        if (!empty($criteria['owner_id'])) {
            $sql .= " AND owner_id = :owner_id";
            $params[':owner_id'] = $criteria['owner_id'];
        }
        if (!empty($criteria['serial'])) {
            $sql .= " AND serial_number LIKE :serial";
            $params[':serial'] = '%' . $criteria['serial'] . '%';
        }
        if (!empty($criteria['model'])) {
            $sql .= " AND model LIKE :model";
            $params[':model'] = '%' . $criteria['model'] . '%';
        }
        if (!empty($criteria['brand'])) {
            $sql .= " AND brand = :brand";
            $params[':brand'] = $criteria['brand'];
        }

        // Προσθέτουμε ORDER BY (προαιρετικό, π.χ. κατά serial)
        $sql .= " ORDER BY serial_number ASC";

        // Εάν έχουμε limit/offset, ενσωματώνουμε απευθείας στο string
        if ($limit !== null && $offset !== null) {
            // ΣΗΜΕΙΩΣΗ: επειδή το LIMIT και το OFFSET απαιτούν ακέραιες τιμές,
            // τις ενσωματώνουμε απευθείας και δεν χρησιμοποιούμε placeholders:
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Μετράει πόσα αυτοκίνητα ικανοποιούν τα κριτήρια (χωρίς limit/offset).
     * Χρησιμοποιείται για τον υπολογισμό των σελίδων.
     */
    public function countAll(array $criteria = []): int {
        $sql = "SELECT COUNT(*) AS cnt FROM `car` WHERE 1";
        $params = [];

        if (!empty($criteria['owner_id'])) {
            $sql .= " AND owner_id = :owner_id";
            $params[':owner_id'] = $criteria['owner_id'];
        }
        if (!empty($criteria['serial'])) {
            $sql .= " AND serial_number LIKE :serial";
            $params[':serial'] = '%' . $criteria['serial'] . '%';
        }
        if (!empty($criteria['model'])) {
            $sql .= " AND model LIKE :model";
            $params[':model'] = '%' . $criteria['model'] . '%';
        }
        if (!empty($criteria['brand'])) {
            $sql .= " AND brand = :brand";
            $params[':brand'] = $criteria['brand'];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['cnt'] ?? 0);
    }
}
