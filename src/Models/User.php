<?php
namespace Models;

use PDO;
use PDOException;

class User {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Δημιουργεί νέο χρήστη και επιστρέφει το ID.
     */
    public function create(array $data): int {
        $sql = "
            INSERT INTO `user`
              (username, password, first_name, last_name, identity_number, role, is_active)
            VALUES
              (:username, :password, :first_name, :last_name, :identity_number, :role, 0)
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':username'        => $data['username'],
            ':password'        => password_hash($data['password'], PASSWORD_DEFAULT),
            ':first_name'      => $data['first_name'],
            ':last_name'       => $data['last_name'],
            ':identity_number' => $data['identity_number'],
            ':role'            => $data['role'],
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Επιστρέφει ένα χρήστη από το ID του, ή null αν δεν βρέθηκε.
     */
    public function findById(int $id): ?array {
        $stmt = $this->pdo->prepare("
            SELECT id, username, first_name, last_name, identity_number, role, is_active, created_at
            FROM `user`
            WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Επιστρέφει όλα τα πεδία ενός χρήστη βάσει του username, ή false αν δεν βρεθεί.
     */
    public function findByUsername(string $username) {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM `user`
            WHERE username = :username
            LIMIT 1
        ");
        $stmt->execute([':username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Επιστρέφει όλα τα πεδία ενός χρήστη βάσει του identity_number, ή null αν δεν βρεθεί.
     * Χρησιμοποιείται για να ελέγξουμε ακριβή ταύτιση ταυτότητας.
     */
    public function findByIdentityNumber(string $identityNumber): ?array {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM `user`
            WHERE identity_number = :identity_number
            LIMIT 1
        ");
        $stmt->execute([':identity_number' => $identityNumber]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Ενεργοποίηση χρήστη
     */
    public function activate(int $id): bool {
        $stmt = $this->pdo->prepare("
            UPDATE `user` SET is_active = 1
            WHERE id = :id AND is_active = 0
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Επιστρέφει τους χρήστες που δεν έχουν ενεργοποιηθεί.
     */
    public function getPending(): array {
        $stmt = $this->pdo->query("
            SELECT id, username, first_name, last_name, role, is_active 
            FROM `user` 
            WHERE is_active = 0
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ενημερώνει πεδία χρήστη. Επιστρέφει true αν άλλαξε κάτι.
     */
    public function update(int $id, array $data): bool {
        $sql = "
            UPDATE `user` SET
              username        = :username,
              first_name      = :first_name,
              last_name       = :last_name,
              identity_number = :identity_number,
              role            = :role,
              is_active       = :is_active
            WHERE id = :id
        ";
        $stmt = $this->pdo->prepare($sql);
        $params = [
            ':username'        => $data['username'],
            ':first_name'      => $data['first_name'],
            ':last_name'       => $data['last_name'],
            ':identity_number' => $data['identity_number'],
            ':role'            => $data['role'],
            ':is_active'       => (int)$data['is_active'],
            ':id'              => $id,
        ];
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    /**
     * Διαγράφει χρήστη. Επιστρέφει true αν έγινε delete.
     */
    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("
            DELETE FROM `user`
            WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Αναζητά χρήστες με όρους: username (partial), last_name (partial), role (ακριβές),
     * και -προαιρετικά- identity_number (ακριβές) εάν περάσει στο criteria.
     * Επιστρέφει array από associative arrays.
     */
    public function search(array $criteria = []): array {
        $sql = "
            SELECT 
              id, username, first_name, last_name, identity_number, role, is_active, created_at 
            FROM `user` 
            WHERE 1
        ";
        $params = [];

        if (!empty($criteria['username'])) {
            $sql .= " AND username LIKE :username";
            $params[':username'] = '%'.$criteria['username'].'%';
        }
        if (!empty($criteria['last_name'])) {
            $sql .= " AND last_name LIKE :last_name";
            $params[':last_name'] = '%'.$criteria['last_name'].'%';
        }
        if (!empty($criteria['role'])) {
            $sql .= " AND role = :role";
            $params[':role'] = $criteria['role'];
        }
        // Πρόσθετο φίλτρο: ακριβής ταύτιση identity_number
        if (!empty($criteria['identity_number'])) {
            $sql .= " AND identity_number = :identity_number";
            $params[':identity_number'] = $criteria['identity_number'];
        }
        if (isset($criteria['is_active'])) {
            $sql .= " AND is_active = :is_active";
            $params[':is_active'] = $criteria['is_active'];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
