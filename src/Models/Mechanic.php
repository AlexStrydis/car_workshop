<?php
namespace Models;

use PDO;
use PDOException;

class Mechanic {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Δημιουργεί νέο μηχανικό. 
     * Πρέπει πρώτα να υπάρχει User με user_id.
     * Επιστρέφει true αν εισήχθη επιτυχώς.
     */
    public function create(array $data): bool {
        $sql = "
            INSERT INTO `mechanic`
              (user_id, specialty)
            VALUES
              (:user_id, :specialty)
        ";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':user_id'   => $data['user_id'],
            ':specialty' => $data['specialty'],
        ]);
    }

    /**
     * Φέρνει τα στοιχεία του μηχανικού για δεδομένο user_id.
     * Επιστρέφει associative array ή null.
     */
    public function findByUserId(int $userId): ?array {
        $sql = "
            SELECT m.user_id, m.specialty,
                   u.username, u.first_name, u.last_name, u.identity_number,
                   u.role, u.is_active, u.created_at
            FROM `mechanic` m
            JOIN `user` u ON m.user_id = u.id
            WHERE m.user_id = :user_id
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Ενημερώνει το specialty του μηχανικού.
     * Επιστρέφει true αν επηρεάστηκε γραμμή.
     */
    public function update(int $userId, string $specialty): bool {
        $sql = "
            UPDATE `mechanic`
            SET specialty = :specialty
            WHERE user_id = :user_id
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':specialty' => $specialty,
            ':user_id'   => $userId,
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Διαγράφει τον μηχανικό.
     * Cascade στο FK θα καθαρίσει ραντεβού.
     */
    public function delete(int $userId): bool {
        $stmt = $this->pdo->prepare("DELETE FROM `mechanic` WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Αναζητά μηχανικούς με κριτήρια:
     *  - specialty (partial)
     *  - last_name (partial)
     *  - username (partial)
     * Επιστρέφει array από associative arrays.
     */
    public function search(array $criteria = []): array {
        $sql = "
            SELECT m.user_id, m.specialty,
                   u.username, u.first_name, u.last_name
            FROM `mechanic` m
            JOIN `user` u ON m.user_id = u.id
            WHERE 1
        ";
        $params = [];
        if (!empty($criteria['specialty'])) {
            $sql .= " AND m.specialty LIKE :specialty";
            $params[':specialty'] = '%'.$criteria['specialty'].'%';
        }
        if (!empty($criteria['last_name'])) {
            $sql .= " AND u.last_name LIKE :last_name";
            $params[':last_name'] = '%'.$criteria['last_name'].'%';
        }
        if (!empty($criteria['username'])) {
            $sql .= " AND u.username LIKE :username";
            $params[':username'] = '%'.$criteria['username'].'%';
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
