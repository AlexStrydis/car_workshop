<?php
namespace Models;

use PDO;
use PDOException;

class Customer {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Δημιουργεί νέο εγγεγραμμένο customer.
     * Περιμένει ήδη να υπάρχει User με το user_id.
     * Επιστρέφει true αν εισήχθη επιτυχώς.
     */
    public function create(array $data): bool {
        $sql = "
            INSERT INTO `customer`
              (user_id, tax_id, address)
            VALUES
              (:user_id, :tax_id, :address)
        ";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':user_id' => $data['user_id'],
            ':tax_id'  => $data['tax_id'],
            ':address' => $data['address'],
        ]);
    }

    /**
     * Φέρνει τα customer details για given user_id.
     * Επιστρέφει array ή null.
     */
    public function findByUserId(int $userId): ?array {
        $sql = "
            SELECT c.user_id, c.tax_id, c.address,
                   u.username, u.first_name, u.last_name, u.identity_number,
                   u.role, u.is_active, u.created_at
            FROM `customer` c
            JOIN `user` u ON c.user_id = u.id
            WHERE c.user_id = :user_id
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Ενημερώνει τα πεδία ενός customer.
     * Επιστρέφει true αν επηρεάστηκαν γραμμές.
     */
    public function update(int $userId, array $data): bool {
        $sql = "
            UPDATE `customer` SET
              tax_id  = :tax_id,
              address = :address
            WHERE user_id = :user_id
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tax_id'    => $data['tax_id'],
            ':address'   => $data['address'],
            ':user_id'   => $userId,
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Διαγράφει τον customer (διαγραφή μόνο στην customer table).
     * Το cascade στο FK θα καθαρίσει τυχόν appointments/cars.
     */
    public function delete(int $userId): bool {
        $stmt = $this->pdo->prepare("DELETE FROM `customer` WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Αναζητά customers με κριτήρια:
     *  - tax_id (exact)
     *  - last_name (partial)
     *  - username (partial)
     * Επιστρέφει πίνακα εγγραφών.
     */
    public function search(array $criteria = []): array {
        $sql = "
            SELECT c.user_id, c.tax_id, c.address,
                   u.username, u.first_name, u.last_name
            FROM `customer` c
            JOIN `user` u ON c.user_id = u.id
            WHERE 1
        ";
        $params = [];
        if (!empty($criteria['tax_id'])) {
            $sql .= " AND c.tax_id = :tax_id";
            $params[':tax_id'] = $criteria['tax_id'];
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
